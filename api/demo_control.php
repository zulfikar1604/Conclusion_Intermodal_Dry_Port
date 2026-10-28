<?php
/**
 * Conclusion Intermodal Dry Port (CIDP)
 * Demo & Pre-Advice Control API
 * File: api/demo_control.php
 *
 * action=status          -> ringkasan kondisi (di luar / di dalam / DLQ)
 * action=list            -> daftar pre-advice (?status=SCHEDULED|ARRIVED|ALL)
 * action=register (POST) -> daftarkan pre-gate baru (booking truk + kontainer)
 * action=reset           -> kosongkan transaksi, kembalikan semua ke "di luar" + seed pre-advice
 * action=dlq             -> daftar Dead Letter Queue
 * action=dlq_resolve|dlq_discard (id)
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/twin_state_helper.php';
require_once __DIR__ . '/preadvice_helper.php';
global $pdo;
ensure_integration_schema($pdo);

$action = $_GET['action'] ?? ($_POST['action'] ?? 'status');

function out($d, int $code = 200) {
    http_response_code($code);
    echo json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function make_container_no(string $prefix, int $serial): string {
    $first10 = $prefix . str_pad((string)$serial, 6, '0', STR_PAD_LEFT);
    return $first10 . iso6346_check_digit($first10);
}

/** Data master pre-advice (EDI 940 dari Principal): deterministik, 30 baris */
function build_preadvice_seed(): array {
    $lines = [
        ['MSCU', 'MSC Indonesia', '42G1', '40FT HIGH CUBE'],
        ['MAEU', 'Maersk Line', '22G1', '20FT STANDARD'],
        ['CMAU', 'CMA CGM', '42G1', '40FT HIGH CUBE'],
        ['ONEU', 'Ocean Network Express', '22G1', '20FT STANDARD'],
        ['HLCU', 'Hapag-Lloyd', '45G1', '40FT HIGH CUBE'],
        ['TGHU', 'PT Samudera Logistik Prima', '22G1', '20FT STANDARD'],
    ];
    $drivers = ['Rahmat Hidayat','Slamet Riyadi','Joko Susilo','Anton Wijaya','Bambang Hermawan','Dedi Kurniawan','Wahyu Pratama','Fajar Nugroho','Gunawan Saputra','Hendra Setiawan','Irwan Maulana','Yusuf Hakim','Taufik Hidayat','Rudi Hartono','Agus Salim'];
    $trucking = ['PT Samudera Logistik Prima','PT Cikarang Trans Logistik','PT Bekasi Karya Angkut','PT Mitra Kontainer Nusantara','PT Jababeka Lintas Raya'];
    $blocks = ['A','B','C','D','E'];
    $rows = [];
    for ($i = 1; $i <= 30; $i++) {
        $ln = $lines[($i - 1) % count($lines)];
        $cargo = 'dry';
        $iso = $ln[2]; $size = $ln[3];
        if (in_array($i, [4, 9, 14, 19, 24], true)) { $cargo = 'reefer'; $iso = '45R1'; $size = '40FT HIGH CUBE REEFER'; }
        elseif (in_array($i, [7, 21], true)) { $cargo = 'dg'; }
        elseif (in_array($i, [12, 26, 30], true)) { $cargo = 'empty'; }
        $rows[] = [
            'pre_gate_code' => sprintf('PG-%s-%03d', date('ymd'), $i),
            'truck_plate' => sprintf('B %d %s', 1000 + $i * 137 % 8999, ['UIX','KFA','TRB','DPL','CKR'][$i % 5] ),
            'rfid_tag' => sprintf('E2801170000002048911%04d', $i),
            'driver_name' => $drivers[($i - 1) % count($drivers)],
            'company' => $trucking[($i - 1) % count($trucking)],
            'job_type' => 'drop_off',
            'do_number' => sprintf('DO-%s-%04d', date('Y'), 7000 + $i),
            'container_number' => make_container_no($ln[0], 918000 + $i * 37),
            'iso_code' => $iso,
            'size_type' => $size,
            'cargo_type' => $cargo,
            'owner_company' => $ln[1],
            'target_block' => $cargo === 'reefer' ? 'REEFER' : $blocks[($i - 1) % count($blocks)],
            'eta' => date('Y-m-d H:i:s', strtotime('+' . (10 + $i * 7) . ' minutes')),
        ];
    }
    return $rows;
}

function insert_preadvice(PDO $pdo, array $r): void {
    $pdo->prepare("INSERT INTO preadvice (pre_gate_code, truck_plate, rfid_tag, driver_name, company, job_type, do_number, container_number, iso_code, size_type, cargo_type, owner_company, target_block, eta, status, source)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'SCHEDULED', ?)")
        ->execute([$r['pre_gate_code'], $r['truck_plate'], $r['rfid_tag'], $r['driver_name'], $r['company'], $r['job_type'], $r['do_number'],
            $r['container_number'], $r['iso_code'], $r['size_type'], $r['cargo_type'], $r['owner_company'], $r['target_block'], $r['eta'], $r['source'] ?? 'EDI-940']);
}

function register_outside(PDO $pdo, array $r): void {
    // Truk & kontainer dibuat sebagai master data berstatus DI LUAR dry port
    $pdo->prepare("INSERT INTO trucks (license_plate, rfid_tag, driver_name, company, job_type, do_number, container_number, target_block, status) VALUES (?,?,?,?,?,?,?,?, 'scheduled')")
        ->execute([$r['truck_plate'], $r['rfid_tag'], $r['driver_name'], $r['company'], $r['job_type'], $r['do_number'], $r['container_number'], $r['target_block']]);
    $pdo->prepare("INSERT INTO containers (container_number, iso_code, size_type, cargo_type, rfid_tag, owner_company, customs_status, block, status) VALUES (?,?,?,?,?,?, 'AWAITING_CLEARANCE', 'OUTSIDE', 'expected')")
        ->execute([$r['container_number'], $r['iso_code'], $r['size_type'], $r['cargo_type'], $r['rfid_tag'], $r['owner_company']]);
}

try {
    switch ($action) {

        case 'status':
            out([
                'success' => true,
                'outside_scheduled' => (int)$pdo->query("SELECT COUNT(*) FROM preadvice WHERE status='SCHEDULED'")->fetchColumn(),
                'arrived' => (int)$pdo->query("SELECT COUNT(*) FROM preadvice WHERE status='ARRIVED'")->fetchColumn(),
                'containers_in_yard' => (int)$pdo->query("SELECT COUNT(*) FROM containers WHERE status='in_yard'")->fetchColumn(),
                'dlq_open' => (int)$pdo->query("SELECT COUNT(*) FROM integration_dlq WHERE status='QUARANTINED'")->fetchColumn(),
                'events_logged' => (int)$pdo->query("SELECT COUNT(*) FROM yard_events")->fetchColumn(),
            ]);

        case 'list':
            $st = strtoupper($_GET['status'] ?? 'SCHEDULED');
            $sql = "SELECT * FROM preadvice" . ($st === 'ALL' ? '' : " WHERE status = " . $pdo->quote($st)) . " ORDER BY eta ASC, id ASC";
            $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            out(['success' => true, 'count' => count($rows), 'data' => $rows]);

        case 'register':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(['success' => false, 'error' => 'Gunakan POST.'], 405);
            $in = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $plate = strtoupper(trim($in['truck_plate'] ?? ''));
            $cn = strtoupper(trim($in['container_number'] ?? ''));
            if ($plate === '' || $cn === '') out(['success' => false, 'error' => 'Plat truk dan nomor kontainer wajib diisi.'], 422);
            if (!iso6346_valid($cn)) out(['success' => false, 'error' => "Nomor kontainer {$cn} tidak valid (check digit ISO 6346 salah)."], 422);
            $dup = $pdo->prepare("SELECT COUNT(*) FROM preadvice WHERE status IN ('SCHEDULED','ARRIVED') AND (container_number = ? OR truck_plate = ?)");
            $dup->execute([$cn, $plate]);
            if ($dup->fetchColumn() > 0) out(['success' => false, 'error' => 'Truk atau kontainer sudah terdaftar pada pre-advice aktif.'], 409);
            $dupc = $pdo->prepare("SELECT COUNT(*) FROM containers WHERE container_number = ?");
            $dupc->execute([$cn]);
            if ($dupc->fetchColumn() > 0) out(['success' => false, 'error' => 'Kontainer sudah ada di database.'], 409);

            $n = (int)$pdo->query("SELECT COALESCE(MAX(id),0)+1 FROM preadvice")->fetchColumn();
            $r = [
                'pre_gate_code' => sprintf('PG-%s-%03d', date('ymd'), $n),
                'truck_plate' => $plate,
                'rfid_tag' => trim($in['rfid_tag'] ?? '') ?: sprintf('E2801170000002049%05d', $n),
                'driver_name' => trim($in['driver_name'] ?? '') ?: null,
                'company' => trim($in['company'] ?? '') ?: null,
                'job_type' => in_array(($in['job_type'] ?? ''), ['drop_off', 'pick_up'], true) ? $in['job_type'] : 'drop_off',
                'do_number' => trim($in['do_number'] ?? '') ?: null,
                'container_number' => $cn,
                'iso_code' => trim($in['iso_code'] ?? '') ?: '42G1',
                'size_type' => trim($in['size_type'] ?? '') ?: '40FT HIGH CUBE',
                'cargo_type' => in_array(($in['cargo_type'] ?? ''), ['dry', 'reefer', 'dg', 'empty'], true) ? $in['cargo_type'] : 'dry',
                'owner_company' => trim($in['owner_company'] ?? '') ?: null,
                'target_block' => strtoupper(trim($in['target_block'] ?? '')) ?: null,
                'eta' => date('Y-m-d H:i:s', strtotime($in['eta'] ?? '+15 minutes')),
                'source' => 'PRE-GATE-WEB',
            ];
            $pdo->beginTransaction();
            insert_preadvice($pdo, $r);
            register_outside($pdo, $r);
            $pdo->commit();
            out(['success' => true, 'message' => 'Pre-gate terdaftar. Truk berstatus di luar dry port.', 'data' => $r]);

        case 'generate_iso':
            $pfx = strtoupper(preg_replace('/[^A-Z]/', '', $_GET['prefix'] ?? 'MSCU'));
            if (strlen($pfx) !== 4) $pfx = 'MSCU';
            $ser = random_int(100000, 999999);
            $cn = make_container_no($pfx, $ser);
            out(['success' => true, 'container_number' => $cn, 'check_digit' => (int)substr($cn, -1)]);

        case 'export_json':
            $rows = $pdo->query("SELECT pre_gate_code, truck_plate, rfid_tag, driver_name, company, job_type, do_number, container_number, iso_code, size_type, cargo_type, owner_company, target_block, eta, status, source FROM preadvice ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
            out(['success' => true, 'document_type' => 'EDI-940-PREADVICE-MANIFEST', 'exported_at' => date('c'), 'count' => count($rows), 'items' => $rows]);

        case 'import_json':
            $raw = file_get_contents('php://input');
            $parsed = @json_decode($raw, true);
            $items = $parsed['items'] ?? (is_array($parsed) ? $parsed : []);
            if (empty($items) || !is_array($items)) out(['success' => false, 'error' => 'Format JSON tidak valid atau kosong. Kirim array items pre-advice.'], 422);
            $added = 0; $skipped = [];
            $pdo->beginTransaction();
            foreach ($items as $idx => $in) {
                $plate = strtoupper(trim($in['truck_plate'] ?? ''));
                $cn = strtoupper(trim($in['container_number'] ?? ''));
                if ($plate === '' || $cn === '' || !iso6346_valid($cn)) {
                    $skipped[] = "Baris #" . ($idx + 1) . " ({$plate}/{$cn}): tidak lengkap atau ISO 6346 tidak valid.";
                    continue;
                }
                $dup = $pdo->prepare("SELECT COUNT(*) FROM preadvice WHERE container_number = ? OR truck_plate = ?");
                $dup->execute([$cn, $plate]);
                if ($dup->fetchColumn() > 0) {
                    $skipped[] = "Baris #" . ($idx + 1) . " ({$plate}/{$cn}): sudah ada.";
                    continue;
                }
                $n = (int)$pdo->query("SELECT COALESCE(MAX(id),0)+1 FROM preadvice")->fetchColumn();
                $r = [
                    'pre_gate_code' => sprintf('PG-%s-%03d', date('ymd'), $n),
                    'truck_plate' => $plate,
                    'rfid_tag' => trim($in['rfid_tag'] ?? '') ?: sprintf('E2801170000002049%05d', $n),
                    'driver_name' => trim($in['driver_name'] ?? '') ?: 'Driver Ekspedisi',
                    'company' => trim($in['company'] ?? '') ?: 'PT Mitra Logistik',
                    'job_type' => in_array(($in['job_type'] ?? ''), ['drop_off', 'pick_up'], true) ? $in['job_type'] : 'drop_off',
                    'do_number' => trim($in['do_number'] ?? '') ?: sprintf('DO-%s-%04d', date('Y'), 8000 + $n),
                    'container_number' => $cn,
                    'iso_code' => trim($in['iso_code'] ?? '') ?: '42G1',
                    'size_type' => trim($in['size_type'] ?? '') ?: '40FT HIGH CUBE',
                    'cargo_type' => in_array(($in['cargo_type'] ?? ''), ['dry', 'reefer', 'dg', 'empty'], true) ? $in['cargo_type'] : 'dry',
                    'owner_company' => trim($in['owner_company'] ?? '') ?: 'Principal Line',
                    'target_block' => strtoupper(trim($in['target_block'] ?? '')) ?: 'B',
                    'eta' => date('Y-m-d H:i:s', strtotime($in['eta'] ?? '+20 minutes')),
                    'source' => 'EDI-940-JSON',
                ];
                insert_preadvice($pdo, $r);
                register_outside($pdo, $r);
                $added++;
            }
            $pdo->commit();
            out(['success' => true, 'added' => $added, 'skipped' => $skipped, 'message' => "{$added} data pre-advice berhasil diimpor dari JSON."]);

        case 'reset':
            // 1) Kosongkan semua data TRANSAKSI & master yang akan dibangun ulang
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            foreach (['yard_events', 'billing_invoices', 'cfs_pallets', 'cfs_jobs', 'reefer_telemetry', 'trains', 'integration_dlq', 'processed_events', 'preadvice', 'trucks', 'containers'] as $t) {
                $pdo->exec("TRUNCATE TABLE `$t`");
            }
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

            // 2) Alat berat (aset terminal) kembali idle tanpa muatan
            $pdo->exec("UPDATE equipment SET status='idle', current_container=NULL, last_updated=NOW()");

            // 3) Pre-advice + truk & kontainer berstatus DI LUAR
            $pdo->beginTransaction();
            $seed = build_preadvice_seed();
            foreach ($seed as $r) { insert_preadvice($pdo, $r); register_outside($pdo, $r); }
            $pdo->commit();

            get_live_twin_state($pdo, true);
            out(['success' => true, 'message' => 'Database di-reset. 30 truk + kontainer terdaftar (pre-advice) dan berstatus di luar dry port.', 'preadvice_seeded' => count($seed)]);

        case 'dlq':
            $rows = $pdo->query("SELECT id, event_id, event_type, source_device, reason, retry_count, status, created_at FROM integration_dlq ORDER BY id DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
            out(['success' => true, 'count' => count($rows), 'data' => $rows]);

        case 'dlq_resolve':
        case 'dlq_discard':
            $id = (int)($_GET['id'] ?? ($_POST['id'] ?? 0));
            $new = $action === 'dlq_resolve' ? 'RESOLVED' : 'DISCARDED';
            $pdo->prepare("UPDATE integration_dlq SET status = ?, resolved_at = NOW() WHERE id = ?")->execute([$new, $id]);
            out(['success' => true, 'id' => $id, 'status' => $new]);

        default:
            out(['success' => false, 'error' => 'Aksi tidak dikenal.'], 400);
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    out(['success' => false, 'error' => $e->getMessage()], 500);
}
