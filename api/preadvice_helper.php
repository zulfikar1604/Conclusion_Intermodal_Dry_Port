<?php
/**
 * Conclusion Intermodal Dry Port (CIDP)
 * Pre-Advice, Idempotency & Dead Letter Queue (DLQ) Helper
 * File: api/preadvice_helper.php
 *
 * Konsep: truk & kontainer sudah terdaftar (pre-advice / booking / DO) SEBELUM tiba,
 * berstatus "di luar dry port". Proses di dalam dry port baru dimulai ketika
 * sensor (ANPR/OCR/RFID) memicu event TRUCK_GATE_ARRIVAL.
 */

/** Exception untuk payload yang ditolak (kontrak/validasi) -> masuk DLQ, HTTP 422 */
class IngestRejection extends Exception {}

/** Membuat tabel pendukung & melebarkan enum status (idempotent, aman dipanggil berulang) */
function ensure_integration_schema(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS preadvice (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pre_gate_code VARCHAR(20) NOT NULL UNIQUE,
        truck_plate VARCHAR(20) NOT NULL,
        rfid_tag VARCHAR(50) NULL,
        driver_name VARCHAR(100) NULL,
        company VARCHAR(100) NULL,
        job_type ENUM('drop_off','pick_up') NOT NULL DEFAULT 'drop_off',
        do_number VARCHAR(50) NULL,
        container_number VARCHAR(20) NOT NULL,
        iso_code VARCHAR(10) NULL,
        size_type VARCHAR(30) NULL,
        cargo_type ENUM('dry','reefer','dg','empty') NOT NULL DEFAULT 'dry',
        owner_company VARCHAR(100) NULL,
        target_block VARCHAR(15) NULL,
        eta DATETIME NULL,
        status ENUM('SCHEDULED','ARRIVED','CANCELLED') NOT NULL DEFAULT 'SCHEDULED',
        source VARCHAR(30) NOT NULL DEFAULT 'EDI-940',
        arrived_at DATETIME NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        KEY idx_plate (truck_plate), KEY idx_container (container_number), KEY idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS integration_dlq (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_id VARCHAR(60) NULL,
        event_type VARCHAR(40) NULL,
        source_device VARCHAR(60) NULL,
        reason VARCHAR(255) NOT NULL,
        payload_json MEDIUMTEXT NULL,
        retry_count INT NOT NULL DEFAULT 0,
        status ENUM('QUARANTINED','RESOLVED','DISCARDED') NOT NULL DEFAULT 'QUARANTINED',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        resolved_at DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS processed_events (
        event_id VARCHAR(60) PRIMARY KEY,
        event_type VARCHAR(40) NULL,
        response_json MEDIUMTEXT NULL,
        processed_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS reefer_telemetry (
        id INT AUTO_INCREMENT PRIMARY KEY,
        container_number VARCHAR(20) NOT NULL,
        socket_id VARCHAR(20) NULL,
        set_temp_c DECIMAL(5,1) NULL,
        supply_temp_c DECIMAL(5,1) NULL,
        power_kw DECIMAL(6,2) NULL,
        status ENUM('NORMAL','ALARM') NOT NULL DEFAULT 'NORMAL',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        KEY idx_cn (container_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    // Tambah status "belum masuk" pada truk & kontainer (tidak mengubah nilai lama)
    $t = $pdo->query("SHOW COLUMNS FROM trucks LIKE 'status'")->fetch();
    if ($t && strpos($t['Type'], "'scheduled'") === false) {
        $pdo->exec("ALTER TABLE trucks MODIFY status ENUM('scheduled','queuing','at_gate','in_yard','loading','gate_out') NOT NULL DEFAULT 'queuing'");
    }
    $c = $pdo->query("SHOW COLUMNS FROM containers LIKE 'status'")->fetch();
    if ($c && strpos($c['Type'], "'expected'") === false) {
        $pdo->exec("ALTER TABLE containers MODIFY status ENUM('expected','in_yard','in_transit','gate_out','on_rail') NOT NULL DEFAULT 'expected'");
    }
}

/** Check digit ISO 6346 (Modulo 11). Input 10 karakter pertama (4 huruf + 6 angka). */
function iso6346_check_digit(string $first10): int {
    $map = []; $n = 10;
    foreach (range('A', 'Z') as $ch) { if ($n % 11 === 0) $n++; $map[$ch] = $n++; }
    $sum = 0;
    for ($i = 0; $i < 10; $i++) {
        $ch = $first10[$i];
        $v = ctype_alpha($ch) ? $map[$ch] : (int)$ch;
        $sum += $v * (2 ** $i);
    }
    return ($sum % 11) % 10;
}

function iso6346_valid(string $no): bool {
    $no = strtoupper(trim($no));
    if (!preg_match('/^[A-Z]{4}[0-9]{7}$/', $no)) return false;
    return iso6346_check_digit(substr($no, 0, 10)) === (int)$no[10];
}

/** Masukkan payload gagal ke Dead Letter Queue tanpa menghentikan alur utama */
function dlq_push(PDO $pdo, ?string $event_id, ?string $event_type, ?string $source, string $reason, $payload): void {
    try {
        $st = $pdo->prepare("INSERT INTO integration_dlq (event_id, event_type, source_device, reason, payload_json) VALUES (?,?,?,?,?)");
        $st->execute([$event_id, $event_type, $source, mb_substr($reason, 0, 250), json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    } catch (Throwable $e) { /* DLQ tidak boleh menggagalkan respons */ }
}

/** GS1 Modulo 10: check digit untuk 17 digit pertama SSCC-18 */
function gs1_check_digit(string $first17): int {
    $sum = 0; $len = strlen($first17);
    for ($i = 0; $i < $len; $i++) {
        $w = (($len - $i) % 2 === 1) ? 3 : 1;
        $sum += (int)$first17[$i] * $w;
    }
    return (10 - ($sum % 10)) % 10;
}
function sscc18_valid(string $s): bool {
    return (bool)preg_match('/^[0-9]{18}$/', $s) && gs1_check_digit(substr($s, 0, 17)) === (int)$s[17];
}

/** Akrual tagihan: tambahkan ke invoice PENDING yang sama (container + layanan), atau buat baru */
function accrue_billing(PDO $pdo, string $container_no, string $service, string $field, float $amount): string {
    $allowed = ['lolo_amount', 'storage_amount', 'reefer_power_amount', 'cfs_amount'];
    if (!in_array($field, $allowed, true)) throw new Exception("Kolom billing tidak valid: {$field}");
    $q = $pdo->prepare("SELECT id, invoice_number FROM billing_invoices WHERE container_number = ? AND service_type = ? AND payment_status = 'PENDING' LIMIT 1");
    $q->execute([$container_no, $service]);
    $inv = $q->fetch(PDO::FETCH_ASSOC);
    if ($inv) {
        $pdo->prepare("UPDATE billing_invoices SET $field = COALESCE($field,0) + ?, total_amount = COALESCE(total_amount,0) + ? WHERE id = ?")
            ->execute([$amount, $amount, $inv['id']]);
        return $inv['invoice_number'];
    }
    $own = $pdo->prepare("SELECT owner_company FROM containers WHERE container_number = ?");
    $own->execute([$container_no]);
    $customer = $own->fetchColumn() ?: 'Customer';
    $pfx = ['lolo_amount' => 'L', 'storage_amount' => 'S', 'reefer_power_amount' => 'R', 'cfs_amount' => 'C'][$field];
    $no = 'INV-' . $pfx . '-' . date('Ymd') . '-' . substr(uniqid(), -4);
    $pdo->prepare("INSERT INTO billing_invoices (invoice_number, container_number, customer_name, service_type, $field, total_amount, payment_status, payment_method, created_at) VALUES (?,?,?,?,?,?, 'PENDING', 'Bank Transfer (Mandiri H2H)', NOW())")
        ->execute([$no, $container_no, $customer, $service, $amount, $amount]);
    return $no;
}