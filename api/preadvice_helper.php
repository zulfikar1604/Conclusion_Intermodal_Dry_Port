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
class IngestRejection extends Exception {
    public array $anomalyMeta = [];
    public function __construct(string $message = '', array $anomalyMeta = [], int $code = 0, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->anomalyMeta = $anomalyMeta;
    }
}

/**
 * Katalog 6 Anomali Operasional Nyata di Lapangan (Gate, Timbangan VGM, Segel, Bea Cukai, & OCR)
 * Digunakan untuk menyuntikkan variasi data tidak sempurna (anomali) pada antrean database pre-advice
 * beserta SOP Penolakan (Turn-Back) dan SOP Penyelesaian (Override / Koreksi / Amandemen).
 */
function cidp_preadvice_anomalies_catalog(): array {
    $validAltBox = 'HLCU994012' . iso6346_check_digit('HLCU994012');
    return [
        2 => [
            'anomaly_code'        => 'DOC_DO_EXPIRED',
            'anomaly_title'       => 'Dokumen e-DO Kadaluarsa & Billing PIB Belum Lunas',
            'anomaly_stage'       => 'GATE_IN',
            'anomaly_notes'       => 'Masa berlaku Electronic Delivery Order (DO) telah habis H-2 & billing pajak PIB belum terkonfirmasi di portal Inaportnet/CEISA. Palang Gate Inbound otomatis terkunci (Hard Interlock).',
            'declared_weight_kg'  => 24800.00,
            'actual_weight_kg'    => 24950.00,
            'seal_number'         => 'SL-MAEU-77102',
            'actual_seal_ocr'     => 'SL-MAEU-77102',
            'actual_container_ocr'=> null,
            'sop_reject'          => 'Tolak Masuk Terminal & Terbitkan Berita Acara Putar Balik (Gate Turn-Back Slip) via Jalur Darurat Gate.',
            'sop_resolve'         => 'Verifikasi Bukti Perpanjangan e-DO (DO-EXT) & Bukti Bayar Billing PIB di Klinik Dokumen Gate, lalu Buka Interlock Palang.'
        ],
        5 => [
            'anomaly_code'        => 'CONTAINER_MISMATCH',
            'anomaly_title'       => 'Nomor Kontainer Fisik OCR ≠ Dokumen Pre-Advice (Salah Angkut)',
            'anomaly_stage'       => 'GATE_IN',
            'anomaly_notes'       => "Kamera OCR Gate membaca fisik kontainer {$validAltBox}, berbeda dengan nomor kontainer yang terdaftar pada dokumen EDI COPARN / Surat Jalan (tertukar saat pemuatan di pabrik shipper Cikarang).",
            'declared_weight_kg'  => 27400.00,
            'actual_weight_kg'    => 27520.00,
            'seal_number'         => 'SL-HLCU-55205',
            'actual_seal_ocr'     => 'SL-HLCU-55205',
            'actual_container_ocr'=> $validAltBox,
            'sop_reject'          => 'Tolak Truk Masuk & Perintahkan Pengemudi Kembali ke Gudang Shipper untuk Menukar Kontainer Sesuai Booking.',
            'sop_resolve'         => 'Proses Amandemen Manifest B/L & Pre-Advice (Sinkronisasi Nomor Kontainer Fisik OCR) atas Persetujuan Pelayaran, lalu Buka Gate.'
        ],
        8 => [
            'anomaly_code'        => 'VGM_OVERWEIGHT',
            'anomaly_title'       => 'Selisih Berat Timbangan 80T SOLAS VGM > 5% & Overload (39.250 kg)',
            'anomaly_stage'       => 'WEIGHBRIDGE',
            'anomaly_notes'       => 'Sensor Load-Cell Jembatan Timbang 80T mencatat berat aktual 39.250 kg vs deklarasi B/L 26.000 kg (Deviasi +51,0%, melampaui batas toleransi SOLAS Bab VI Reg 2 ±5% dan batas beban gandar 35 Ton). Work Order Reach Stacker RS-02 diblokir otomatis.',
            'declared_weight_kg'  => 26000.00,
            'actual_weight_kg'    => 39250.00,
            'seal_number'         => 'SL-CMAU-88108',
            'actual_seal_ocr'     => 'SL-CMAU-88108',
            'actual_container_ocr'=> null,
            'sop_reject'          => 'Tolak Penumpukan Yard (Safety Hold) & Putar Balik Truk untuk Pengurangan Muatan (Stripping Sebagian).',
            'sop_resolve'         => 'Terbitkan Sertifikat SOLAS VGM Amendment (Revisi Deklarasi Berat + Biaya Timbang Ulang Rp 150.000) & Buka Blokir Lift-Off RS-02.'
        ],
        11 => [
            'anomaly_code'        => 'SEAL_TAMPERED',
            'anomaly_title'       => 'Segel Pelayaran Putus / Nomor E-Seal Tidak Cocok & Dinding Penyok',
            'anomaly_stage'       => 'GATE_IN',
            'anomaly_notes'       => 'Pemeriksaan Gate Inspection mendeteksi segel pelayaran putus (terbaca TAMPERED-BROKEN vs dokumen SL-HLCU-99111) serta penyok pada panel samping kanan (Damage Grade C).',
            'declared_weight_kg'  => 25200.00,
            'actual_weight_kg'    => 25180.00,
            'seal_number'         => 'SL-HLCU-99111',
            'actual_seal_ocr'     => 'TAMPERED-BROKEN',
            'actual_container_ocr'=> null,
            'sop_reject'          => 'Tolak Serah Terima Kontainer Cacat / Tanpa Segel Sah agar Tanggung Jawab Klaim Tidak Beralih ke Dry Port.',
            'sop_resolve'         => 'Terbitkan EIR Damage Report (Ditandatangani Sopir), Pasang Segel Pengganti Resmi (RESEAL-CIDP) & Arahkan ke Blok E (Karantina/Behandle).'
        ],
        15 => [
            'anomaly_code'        => 'CUSTOMS_DOC_MISMATCH',
            'anomaly_title'       => 'Dokumen BC 2.3 / HS Code Tidak Sinkron CEISA 4.0 (Red Line Hold)',
            'anomaly_stage'       => 'CUSTOMS',
            'anomaly_notes'       => 'Validasi Host-to-Host DJBC CEISA 4.0 menolak rilis SPPB otomatis karena kode HS komoditas & jumlah kemasan pada Packing List tidak cocok dengan pemberitahuan pabean BC 2.3.',
            'declared_weight_kg'  => 29100.00,
            'actual_weight_kg'    => 29150.00,
            'seal_number'         => 'SL-CMAU-44015',
            'actual_seal_ocr'     => 'SL-CMAU-44015',
            'actual_container_ocr'=> null,
            'sop_reject'          => 'Bekukan Pengeluaran Barang (Customs Red-Line Lock) & Tolak Permohonan Rilis Gate-Out.',
            'sop_resolve'         => 'Laksanakan Pemeriksaan Fisik Jalur Merah di Behandle Blok E (FL-02 & X-Ray), Terbitkan Nota Pembetulan BC 2.3 & Rilis SPPB.'
        ],
        18 => [
            'anomaly_code'        => 'OCR_CHECKDIGIT_ERROR',
            'anomaly_title'       => 'Stiker Nomor Kontainer Rusak / Check Digit ISO 6346 Gagal Validasi',
            'anomaly_stage'       => 'GATE_IN',
            'anomaly_notes'       => 'Kamera OCR Gate membaca nomor kontainer TGHU9186669 (Check Digit Modulo-11 tidak valid karena stiker angka terakhir terkelupas akibat cuaca).',
            'declared_weight_kg'  => 22400.00,
            'actual_weight_kg'    => 22450.00,
            'seal_number'         => 'SL-TGHU-31018',
            'actual_seal_ocr'     => 'SL-TGHU-31018',
            'actual_container_ocr'=> 'TGHU9186669',
            'sop_reject'          => 'Tolak Masuk Otomatis & Instruksikan Ekspedisi Memperbaiki Marka Identitas Standar ISO 6346.',
            'sop_resolve'         => 'Verifikasi Fisik Plat CSC Baja Kontainer oleh Petugas Gate, Koreksi Check Digit ISO 6346 di Sistem & Buka Palang.'
        ],
    ];
}

function cidp_get_anomaly_meta(?string $code): ?array {
    if (!$code) return null;
    foreach (cidp_preadvice_anomalies_catalog() as $item) {
        if ($item['anomaly_code'] === $code) return $item;
    }
    if ($code === 'GATE_OUT_INTERLOCK_BLOCK') {
        return [
            'anomaly_code'  => 'GATE_OUT_INTERLOCK_BLOCK',
            'anomaly_title' => 'Penahanan Gate-Out: Faktur Belum Lunas / SPPB Belum Terbit',
            'anomaly_stage' => 'GATE_OUT',
            'anomaly_notes' => 'Interlock Gate-Out mengunci palang keluar karena kontainer masih memiliki tagihan PENDING di Odoo 17 ERP atau status pabean belum SPPB_CLEARED.',
            'sop_reject'    => 'Tolak Gate-Out & Tahan Truk di Area Buffer Outbound hingga Kewajiban Diselesaikan.',
            'sop_resolve'   => 'Lunasi Tagihan H2H / Otorisasi Jaminan Korporat & Rilis SPPB, lalu Buka Palang Gate-Out.'
        ];
    }
    if ($code === 'UNREGISTERED_TRUCK_REJECT') {
        return [
            'anomaly_code'  => 'UNREGISTERED_TRUCK_REJECT',
            'anomaly_title' => 'Truk & Kontainer Ilegal / Tidak Terdaftar di Pre-Advice EDI',
            'anomaly_stage' => 'GATE_IN',
            'anomaly_notes' => 'Sensor ANPR & RFID Gate mendeteksi truk tanpa booking Pre-Advice (EDI COPARN / Surat Jalan tidak ditemukan di database).',
            'sop_reject'    => 'Tolak Akses Gate (Lockout), Cetak Berita Acara Penolakan & Putar Balik via Jalur Darurat.',
            'sop_resolve'   => 'Daftarkan Pre-Advice Darurat (Manual Gate Registration) setelah Verifikasi Dokumen Fisik B/L & DO.'
        ];
    }
    return null;
}

/** Daftar Master Armada & MHE Terminal CIDP 35 Ha (SSoT sinkron dengan Digital Twin 2D/3D, Simulator 3D, CFS & Yard) */
function cidp_master_equipment_fleet(): array {
    return [
        // 4 Unit Reach Stacker 45T (Koridor Yard, Empty Depot, Reefer, South Haul)
        ['equipment_id' => 'RS-01',     'equipment_type' => 'REACH_STACKER',     'brand_model' => 'Kalmar DRG450-65S5',             'operator_name' => 'Budi Santoso',    'operator_id' => 'OP-001', 'gps_x' => 270.00, 'gps_y' => 175.00, 'last_block' => 'A',      'fuel_percent' => 85,  'hours_today' => 4.2],
        ['equipment_id' => 'RS-02',     'equipment_type' => 'REACH_STACKER',     'brand_model' => 'Kalmar DRG450-65S5',             'operator_name' => 'Agus Setiawan',   'operator_id' => 'OP-002', 'gps_x' => 490.00, 'gps_y' => 175.00, 'last_block' => 'B',      'fuel_percent' => 84,  'hours_today' => 5.1],
        ['equipment_id' => 'RS-03',     'equipment_type' => 'REACH_STACKER',     'brand_model' => 'Kalmar DRG450-65S5',             'operator_name' => 'Rudi Hermawan',   'operator_id' => 'OP-003', 'gps_x' => 770.00, 'gps_y' => 175.00, 'last_block' => 'REEFER', 'fuel_percent' => 78,  'hours_today' => 3.4],
        ['equipment_id' => 'RS-04',     'equipment_type' => 'REACH_STACKER',     'brand_model' => 'Kalmar DRG450-65S5',             'operator_name' => 'Slamet Riyadi',   'operator_id' => 'OP-005', 'gps_x' => 270.00, 'gps_y' => 545.00, 'last_block' => 'C',      'fuel_percent' => 90,  'hours_today' => 2.8],

        // 5 Unit RTG / E-RTG Zero-Emission (Rail Siding KA Pasoso + Blok Yard A, B, C, D)
        ['equipment_id' => 'RTG-01',    'equipment_type' => 'RTG_CRANE',         'brand_model' => 'Konecranes RMGC Rail Gantry',    'operator_name' => 'Joko Susilo',     'operator_id' => 'OP-004', 'gps_x' => 600.00, 'gps_y' => 625.00, 'last_block' => 'RAIL',   'fuel_percent' => 100, 'hours_today' => 6.0],
        ['equipment_id' => 'RTG-02',    'equipment_type' => 'RTG_CRANE',         'brand_model' => 'Konecranes Zero-Emission E-RTG', 'operator_name' => 'Hendra Wijaya',   'operator_id' => 'OP-006', 'gps_x' => 270.00, 'gps_y' => 250.00, 'last_block' => 'A',      'fuel_percent' => 100, 'hours_today' => 5.4],
        ['equipment_id' => 'RTG-03',    'equipment_type' => 'RTG_CRANE',         'brand_model' => 'Konecranes Zero-Emission E-RTG', 'operator_name' => 'Dedi Kurniawan',  'operator_id' => 'OP-007', 'gps_x' => 490.00, 'gps_y' => 250.00, 'last_block' => 'B',      'fuel_percent' => 100, 'hours_today' => 4.9],
        ['equipment_id' => 'RTG-04',    'equipment_type' => 'RTG_CRANE',         'brand_model' => 'Konecranes Zero-Emission E-RTG', 'operator_name' => 'Wahyu Pratama',   'operator_id' => 'OP-008', 'gps_x' => 270.00, 'gps_y' => 470.00, 'last_block' => 'C',      'fuel_percent' => 100, 'hours_today' => 4.1],
        ['equipment_id' => 'RTG-05',    'equipment_type' => 'RTG_CRANE',         'brand_model' => 'Konecranes Zero-Emission E-RTG', 'operator_name' => 'Fajar Nugroho',   'operator_id' => 'OP-009', 'gps_x' => 490.00, 'gps_y' => 470.00, 'last_block' => 'D',      'fuel_percent' => 100, 'hours_today' => 3.7],

        // 4 Unit Forklift Elektrik Gudang CFS & Karantina Behandle (Linde E-Series 2.5T - 5.0T)
        ['equipment_id' => 'FL-01',     'equipment_type' => 'ELECTRIC_FORKLIFT', 'brand_model' => 'Linde E35 Electric 3.5T',        'operator_name' => 'Ahmad Fauzi',     'operator_id' => 'OP-010', 'gps_x' => 140.00, 'gps_y' => 240.00, 'last_block' => 'CFS',    'fuel_percent' => 84,  'hours_today' => 4.6],
        ['equipment_id' => 'FL-02',     'equipment_type' => 'ELECTRIC_FORKLIFT', 'brand_model' => 'Linde E50 Heavy Electric 5.0T',  'operator_name' => 'Taufik Hidayat',  'operator_id' => 'OP-011', 'gps_x' => 835.00, 'gps_y' => 470.00, 'last_block' => 'E',      'fuel_percent' => 88,  'hours_today' => 3.9],
        ['equipment_id' => 'FL-03',     'equipment_type' => 'ELECTRIC_FORKLIFT', 'brand_model' => 'Linde E35 Electric 3.5T',        'operator_name' => 'Irwan Maulana',   'operator_id' => 'OP-012', 'gps_x' => 148.00, 'gps_y' => 310.00, 'last_block' => 'CFS',    'fuel_percent' => 91,  'hours_today' => 5.0],
        ['equipment_id' => 'FL-04',     'equipment_type' => 'ELECTRIC_FORKLIFT', 'brand_model' => 'Linde E25 Reach Truck 2.5T',     'operator_name' => 'Eko Prasetyo',    'operator_id' => 'OP-013', 'gps_x' => 90.00,  'gps_y' => 350.00, 'last_block' => 'CFS',    'fuel_percent' => 79,  'hours_today' => 4.3],

        // 2 Unit Head Truck Langsir Internal (Terminal Shuttle Tractor CFS <-> Rail <-> Bea Cukai)
        ['equipment_id' => 'CDP-TT-01', 'equipment_type' => 'TERMINAL_TRACTOR',  'brand_model' => 'Kalmar Ottawa T2 Shuttle',       'operator_name' => 'Yusuf Hakim',     'operator_id' => 'OP-014', 'gps_x' => 360.00, 'gps_y' => 585.00, 'last_block' => 'RAIL',   'fuel_percent' => 86,  'hours_today' => 5.5],
        ['equipment_id' => 'CDP-TT-02', 'equipment_type' => 'TERMINAL_TRACTOR',  'brand_model' => 'Terberg YT220 Terminal Tractor', 'operator_name' => 'Bambang Hermawan','operator_id' => 'OP-015', 'gps_x' => 635.00, 'gps_y' => 470.00, 'last_block' => 'E',      'fuel_percent' => 82,  'hours_today' => 4.8],
    ];
}

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

    $pdo->exec("CREATE TABLE IF NOT EXISTS system_thresholds (
        setting_key VARCHAR(60) PRIMARY KEY,
        setting_value VARCHAR(160) NOT NULL,
        description VARCHAR(255) NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $defThresholds = [
        'vgm_tolerance_pct'      => ['5.0', 'Batas toleransi selisih berat SOLAS VGM (%)'],
        'vgm_max_gross_kg'       => ['35000', 'Batas berat kotor maksimal kontainer (kg)'],
        'anpr_min_confidence'    => ['85', 'Ambang batas keyakinan minimal OCR/ANPR (%)'],
        'reefer_max_deviation_c' => ['2.0', 'Deviasi maksimal suhu Reefer vs Setpoint (C)'],
        'reefer_thermal_trip_c'  => ['85.0', 'Ambang batas alarm overheat kamera termal HW-16 (C)'],
        'free_dwell_days'        => ['3', 'Masa bebas penumpukan kontainer di yard (hari)'],
        'rail_expected_axles'    => ['126', 'Jumlah gandar standar 1 rangkaian KA 30 gerbong + loko'],
        'ats_switch_max_sec'     => ['10.0', 'Batas waktu alih daya ATS genset darurat (detik)'],
        'vgm_zero_offset_kg'     => ['0.0', 'Kalibrasi zero tare offset timbangan 80T (kg)'],
    ];
    $insTh = $pdo->prepare("INSERT IGNORE INTO system_thresholds (setting_key, setting_value, description) VALUES (?, ?, ?)");
    foreach ($defThresholds as $tk => $tv) {
        $insTh->execute([$tk, $tv[0], $tv[1]]);
    }
    // Tambah status "belum masuk" pada truk & kontainer (tidak mengubah nilai lama)
    $t = $pdo->query("SHOW COLUMNS FROM trucks LIKE 'status'")->fetch();
    if ($t && strpos($t['Type'], "'scheduled'") === false) {
        $pdo->exec("ALTER TABLE trucks MODIFY status ENUM('scheduled','queuing','at_gate','in_yard','loading','gate_out') NOT NULL DEFAULT 'queuing'");
    }
    $c = $pdo->query("SHOW COLUMNS FROM containers LIKE 'status'")->fetch();
    if ($c && strpos($c['Type'], "'expected'") === false) {
        $pdo->exec("ALTER TABLE containers MODIFY status ENUM('expected','in_yard','in_transit','gate_out','on_rail') NOT NULL DEFAULT 'expected'");
    }
    $dlq = $pdo->query("SHOW COLUMNS FROM integration_dlq LIKE 'status'")->fetch();
    if ($dlq && strpos($dlq['Type'], "'BUFFERED'") === false) {
        $pdo->exec("ALTER TABLE integration_dlq MODIFY status ENUM('QUARANTINED','BUFFERED','RESOLVED','DISCARDED') NOT NULL DEFAULT 'QUARANTINED'");
    }

    // Perlebar status preadvice & tambahkan kolom anomali operasional nyata (idempoten)
    $paSt = $pdo->query("SHOW COLUMNS FROM preadvice LIKE 'status'")->fetch();
    if ($paSt && strpos($paSt['Type'], "'HOLD_ANOMALY'") === false) {
        $pdo->exec("ALTER TABLE preadvice MODIFY status ENUM('SCHEDULED','ARRIVED','HOLD_ANOMALY','REJECTED','CANCELLED') NOT NULL DEFAULT 'SCHEDULED'");
    }
    $paCols = [
        'anomaly_code'         => "VARCHAR(40) NULL DEFAULT NULL",
        'anomaly_title'        => "VARCHAR(160) NULL DEFAULT NULL",
        'anomaly_stage'        => "VARCHAR(30) NULL DEFAULT NULL",
        'anomaly_notes'        => "TEXT NULL",
        'declared_weight_kg'   => "DECIMAL(10,2) NULL DEFAULT 26500.00",
        'actual_weight_kg'     => "DECIMAL(10,2) NULL DEFAULT 26800.00",
        'seal_number'          => "VARCHAR(35) NULL DEFAULT NULL",
        'actual_seal_ocr'      => "VARCHAR(35) NULL DEFAULT NULL",
        'actual_container_ocr' => "VARCHAR(25) NULL DEFAULT NULL",
        'resolution_status'    => "ENUM('NONE','HOLD_AT_GATE','REJECTED_TURNBACK','OVERRIDDEN_APPROVED') NOT NULL DEFAULT 'NONE'",
        'resolution_notes'     => "VARCHAR(255) NULL DEFAULT NULL",
        'ba_number'            => "VARCHAR(50) NULL DEFAULT NULL"
    ];
    foreach ($paCols as $colName => $colDef) {
        if (!$pdo->query("SHOW COLUMNS FROM preadvice LIKE '$colName'")->fetch()) {
            $pdo->exec("ALTER TABLE preadvice ADD COLUMN `$colName` $colDef");
        }
    }

    $dlqCols = [
        'anomaly_code'      => "VARCHAR(40) NULL DEFAULT NULL",
        'pre_gate_code'     => "VARCHAR(30) NULL DEFAULT NULL",
        'container_number'  => "VARCHAR(25) NULL DEFAULT NULL",
        'truck_plate'       => "VARCHAR(25) NULL DEFAULT NULL",
        'resolution_action' => "VARCHAR(40) NULL DEFAULT NULL",
        'resolution_notes'  => "VARCHAR(255) NULL DEFAULT NULL",
        'ba_number'         => "VARCHAR(50) NULL DEFAULT NULL"
    ];
    foreach ($dlqCols as $colName => $colDef) {
        if (!$pdo->query("SHOW COLUMNS FROM integration_dlq LIKE '$colName'")->fetch()) {
            $pdo->exec("ALTER TABLE integration_dlq ADD COLUMN `$colName` $colDef");
        }
    }

    foreach (['reefer_telemetry', 'trains', 'yard_events', 'cfs_jobs', 'cfs_pallets', 'billing_invoices', 'containers', 'trucks', 'equipment', 'preadvice', 'integration_dlq'] as $tbl) {
        try {
            $idCol = $pdo->query("SHOW COLUMNS FROM `$tbl` LIKE 'id'")->fetch(PDO::FETCH_ASSOC);
            if ($idCol && stripos($idCol['Extra'] ?? '', 'auto_increment') === false) {
                $maxId = (int)$pdo->query("SELECT COALESCE(MAX(id), 0) FROM `$tbl` WHERE id > 0")->fetchColumn();
                $pdo->exec("UPDATE `$tbl` SET id = " . ($maxId + 1) . " WHERE id = 0 LIMIT 1");
                $pdo->exec("DELETE FROM `$tbl` WHERE id = 0");
                $pdo->exec("ALTER TABLE `$tbl` MODIFY id INT NOT NULL AUTO_INCREMENT");
            }
        } catch (Exception $e) {}
    }

    // Normalisasi SSoT: kontainer OUTSIDE berstatus kosong dipastikan 'expected', koordinat & armada 15 unit MHE tersinkron ke MySQL
    try {
        $pdo->exec("UPDATE containers SET status = 'expected' WHERE (status = '' OR status IS NULL) AND UPPER(COALESCE(block,'')) = 'OUTSIDE'");

        // Hidrasi idempoten 6 skenario anomali nyata pada antrean preadvice jika belum ada baris ber-anomali
        $anomCnt = (int)$pdo->query("SELECT COUNT(*) FROM preadvice WHERE anomaly_code IS NOT NULL AND anomaly_code <> ''")->fetchColumn();
        if ($anomCnt === 0) {
            $allPa = $pdo->query("SELECT id, pre_gate_code, container_number, truck_plate FROM preadvice ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
            $catalog = cidp_preadvice_anomalies_catalog();
            $updAnom = $pdo->prepare("UPDATE preadvice SET anomaly_code=?, anomaly_title=?, anomaly_stage=?, anomaly_notes=?, declared_weight_kg=?, actual_weight_kg=?, seal_number=?, actual_seal_ocr=?, actual_container_ocr=? WHERE id=?");
            foreach ($allPa as $idx0 => $paRow) {
                $seq = $idx0 + 1;
                if (isset($catalog[$seq])) {
                    $ca = $catalog[$seq];
                    $updAnom->execute([
                        $ca['anomaly_code'], $ca['anomaly_title'], $ca['anomaly_stage'], $ca['anomaly_notes'],
                        $ca['declared_weight_kg'], $ca['actual_weight_kg'],
                        $ca['seal_number'], $ca['actual_seal_ocr'], $ca['actual_container_ocr'],
                        $paRow['id']
                    ]);
                } else {
                    $defSeal = 'SL-CIDP-' . str_pad((string)(80000 + $seq), 5, '0', STR_PAD_LEFT);
                    $pdo->prepare("UPDATE preadvice SET seal_number = COALESCE(seal_number, ?), actual_seal_ocr = COALESCE(actual_seal_ocr, ?), declared_weight_kg = COALESCE(declared_weight_kg, 26500.00), actual_weight_kg = COALESCE(actual_weight_kg, 26750.00) WHERE id = ?")
                        ->execute([$defSeal, $defSeal, $paRow['id']]);
                }
            }
        }

        $fleet = cidp_master_equipment_fleet();
        $chkEq = $pdo->prepare("SELECT COUNT(*) FROM equipment WHERE equipment_id = ?");
        $insEq = $pdo->prepare("INSERT INTO equipment (equipment_id, equipment_type, brand_model, operator_name, operator_id, status, gps_x, gps_y, last_block, fuel_percent, hours_today, last_updated) VALUES (?, ?, ?, ?, ?, 'idle', ?, ?, ?, ?, ?, NOW())");
        $stEq  = $pdo->prepare("UPDATE equipment SET gps_x = ?, gps_y = ?, last_block = COALESCE(NULLIF(last_block,''), ?) WHERE equipment_id = ? AND (gps_x <= 0 OR gps_y <= 0 OR (status = 'idle' AND equipment_id NOT IN ('RS-01','RS-02','RS-03','RTG-01')))");
        foreach ($fleet as $fm) {
            $chkEq->execute([$fm['equipment_id']]);
            if ((int)$chkEq->fetchColumn() === 0) {
                $insEq->execute([
                    $fm['equipment_id'], $fm['equipment_type'], $fm['brand_model'],
                    $fm['operator_name'], $fm['operator_id'],
                    $fm['gps_x'], $fm['gps_y'], $fm['last_block'],
                    $fm['fuel_percent'], $fm['hours_today']
                ]);
            } else {
                $stEq->execute([$fm['gps_x'], $fm['gps_y'], $fm['last_block'], $fm['equipment_id']]);
            }
        }

        // Hidrasi idempoten reefer_telemetry jika kosong tetapi sudah ada reefer aktif di dalam yard
        $rtCnt = (int)$pdo->query("SELECT COUNT(*) FROM reefer_telemetry")->fetchColumn();
        if ($rtCnt === 0) {
            $rfRows = $pdo->query("SELECT id, container_number, owner_company FROM containers WHERE status IN ('in_yard','in_transit') AND (LOWER(cargo_type) = 'reefer' OR UPPER(block) = 'REEFER') ORDER BY id ASC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rfRows)) {
                $insRt = $pdo->prepare("INSERT INTO reefer_telemetry (container_number, socket_id, set_temp_c, supply_temp_c, power_kw, status, created_at) VALUES (?,?,?,?,?, 'NORMAL', NOW())");
                foreach ($rfRows as $idx => $rr) {
                    $sock = sprintf('R0%d-S%02d', ($idx % 4) + 1, ($idx * 3) + 2);
                    $setT = ($idx % 2 === 0) ? -20.0 : 4.0;
                    $supT = ($idx % 2 === 0) ? -19.8 : 4.1;
                    $kw   = ($idx % 2 === 0) ? 6.45 : 4.85;
                    $insRt->execute([$rr['container_number'], $sock, $setT, $supT, $kw]);
                }
            }
        }

        // Hidrasi idempoten cfs_pallets jika kosong tetapi sudah ada job order di cfs_jobs
        $palCnt = (int)$pdo->query("SELECT COUNT(*) FROM cfs_pallets")->fetchColumn();
        if ($palCnt === 0) {
            $jobRows = $pdo->query("SELECT id, container_number, shipper_consignee FROM cfs_jobs ORDER BY id ASC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($jobRows)) {
                $insPal = $pdo->prepare("INSERT INTO cfs_pallets (job_id, pallet_sscc, container_origin, commodity_name, consignee, category, carton_count, gross_weight_kg, cbm, rack_location, customs_doc, customs_status, status, entry_date) VALUES (?,?,?,?,?, 'general', ?,?,?,?, 'SPPB-CEISA', 'CLEARED', 'IN_RACK', NOW())");
                foreach ($jobRows as $jIdx => $jr) {
                    for ($pNum = 1; $pNum <= 2; $pNum++) {
                        $base17 = '0018991200' . str_pad((string)(1000000 + ($jr['id'] * 10) + $pNum), 7, '0', STR_PAD_LEFT);
                        $sscc = $base17 . gs1_check_digit($base17);
                        $rack = sprintf('AISLE-%s-0%d-L%d', chr(65 + ($jIdx % 4)), ($pNum + 1), (($jIdx + $pNum) % 2) + 1);
                        $insPal->execute([
                            $jr['id'],
                            $sscc,
                            $jr['container_number'],
                            'Kargo Konsolidasi LCL (' . $jr['container_number'] . ' #' . $pNum . ')',
                            $jr['shipper_consignee'] ?: 'Customer LCL CIDP',
                            20 + ($pNum * 8),
                            380.0 + ($pNum * 75.5),
                            1.20 + ($pNum * 0.25),
                            $rack
                        ]);
                    }
                }
            }
        }

        // Rekonsiliasi awal master_lokasi agar selalu selaras SSoT dengan cfs_pallets (tanpa ghost row lama)
        cfs_ensure_master_lokasi_grid($pdo);
        $staleCnt = (int)$pdo->query("SELECT COUNT(*) FROM master_lokasi WHERE status = 'terisi' AND (pallet_sscc IS NULL OR pallet_sscc = '')")->fetchColumn();
        if ($staleCnt > 0) {
            cfs_sync_master_lokasi($pdo);
        }
    } catch (Throwable $e) {}
}

/**
 * Parse format lokasi rak gudang CFS (contoh: 'AISLE-E-02-L1' atau 'BLOK2-E-R1')
 * menjadi koordinat kanonik ['blok' => '1..6', 'bagian' => 'A..F', 'rak' => 1..4, 'code' => 'AISLE-E-02-L1'].
 */
function cfs_parse_rack_location(?string $rack_loc): ?array {
    $raw = strtoupper(trim((string)$rack_loc));
    if ($raw === '') return null;

    if (preg_match('/AISLE-([A-F])-0?([1-6])-L([1-4])/i', $raw, $m)) {
        $bagian = strtoupper($m[1]);
        $blok   = (string)intval($m[2]);
        $rak    = intval($m[3]);
    } elseif (preg_match('/BLOK-?([1-6])-([A-F])-R?([1-4])/i', $raw, $m)) {
        $blok   = (string)intval($m[1]);
        $bagian = strtoupper($m[2]);
        $rak    = intval($m[3]);
    } elseif (preg_match('/^([A-F])([1-6])[-_ ]?R?([1-4])$/i', $raw, $m)) {
        $bagian = strtoupper($m[1]);
        $blok   = (string)intval($m[2]);
        $rak    = intval($m[3]);
    } else {
        return null;
    }

    return [
        'blok'   => $blok,
        'bagian' => $bagian,
        'rak'    => $rak,
        'code'   => sprintf('AISLE-%s-%02d-L%d', $bagian, (int)$blok, $rak),
    ];
}

/**
 * Pastikan seluruh 144 slot kanonik Gudang CFS (Blok 1..6 x Sektor A..F x Level R1..R4) tersedia di master_lokasi.
 */
function cfs_ensure_master_lokasi_grid(PDO $pdo): void {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS master_lokasi (
            id INT AUTO_INCREMENT PRIMARY KEY,
            blok VARCHAR(10) NOT NULL,
            bagian VARCHAR(20) NOT NULL,
            rak INT NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'kosong',
            pallet_sscc VARCHAR(35) NULL,
            commodity_name VARCHAR(150) NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_blok_bagian_rak (blok, bagian, rak)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $existingCnt = (int)$pdo->query("SELECT COUNT(*) FROM master_lokasi")->fetchColumn();
        if ($existingCnt >= 144) return;

        $existingMap = [];
        $stAll = $pdo->query("SELECT blok, bagian, rak FROM master_lokasi");
        while ($r = $stAll->fetch(PDO::FETCH_ASSOC)) {
            $existingMap[$r['blok'] . '|' . strtoupper($r['bagian']) . '|' . (int)$r['rak']] = true;
        }

        $ins = $pdo->prepare("INSERT INTO master_lokasi (blok, bagian, rak, status, pallet_sscc, commodity_name) VALUES (?, ?, ?, 'kosong', NULL, NULL)");
        foreach (['1', '2', '3', '4', '5', '6'] as $b) {
            foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $sec) {
                for ($rk = 1; $rk <= 4; $rk++) {
                    $k = $b . '|' . $sec . '|' . $rk;
                    if (!isset($existingMap[$k])) {
                        $ins->execute([$b, $sec, $rk]);
                    }
                }
            }
        }
    } catch (Throwable $e) {}
}

/**
 * Sinkronisasi penuh Single Source of Truth (SSoT):
 * Memetakan setiap palet aktif di cfs_pallets (IN_RACK / STAGING_OUT / HOLD) langsung ke slot master_lokasi.
 */
function cfs_sync_master_lokasi(PDO $pdo): void {
    try {
        cfs_ensure_master_lokasi_grid($pdo);
        $pdo->exec("UPDATE master_lokasi SET status = 'kosong', pallet_sscc = NULL, commodity_name = NULL");

        $pallets = $pdo->query("SELECT id, pallet_sscc, commodity_name, rack_location FROM cfs_pallets WHERE status IN ('IN_RACK', 'STAGING_OUT', 'HOLD') ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $used = [];
        $updSlot = $pdo->prepare("UPDATE master_lokasi SET status = 'terisi', pallet_sscc = ?, commodity_name = ? WHERE blok = ? AND bagian = ? AND rak = ?");
        $updPalLoc = $pdo->prepare("UPDATE cfs_pallets SET rack_location = ? WHERE id = ?");

        foreach ($pallets as $p) {
            $parsed = cfs_parse_rack_location($p['rack_location'] ?? '');
            if (!$parsed) {
                $parsed = ['blok' => '1', 'bagian' => 'A', 'rak' => 1, 'code' => 'AISLE-A-01-L1'];
            }
            $slotKey = $parsed['blok'] . '|' . $parsed['bagian'] . '|' . $parsed['rak'];

            // Hindari bentrok 2 palet di 1 rak level yang sama saat rekonsiliasi
            if (isset($used[$slotKey])) {
                $foundFree = null;
                for ($tryRak = 1; $tryRak <= 4; $tryRak++) {
                    $tk = $parsed['blok'] . '|' . $parsed['bagian'] . '|' . $tryRak;
                    if (!isset($used[$tk])) {
                        $foundFree = ['blok' => $parsed['blok'], 'bagian' => $parsed['bagian'], 'rak' => $tryRak];
                        break;
                    }
                }
                if (!$foundFree) {
                    foreach (['1','2','3','4','5','6'] as $tb) {
                        for ($tr = 1; $tr <= 4; $tr++) {
                            $tk = $tb . '|' . $parsed['bagian'] . '|' . $tr;
                            if (!isset($used[$tk])) {
                                $foundFree = ['blok' => $tb, 'bagian' => $parsed['bagian'], 'rak' => $tr];
                                break 2;
                            }
                        }
                    }
                }
                if ($foundFree) {
                    $parsed = [
                        'blok'   => $foundFree['blok'],
                        'bagian' => $foundFree['bagian'],
                        'rak'    => $foundFree['rak'],
                        'code'   => sprintf('AISLE-%s-%02d-L%d', $foundFree['bagian'], (int)$foundFree['blok'], $foundFree['rak'])
                    ];
                    $slotKey = $parsed['blok'] . '|' . $parsed['bagian'] . '|' . $parsed['rak'];
                    $updPalLoc->execute([$parsed['code'], $p['id']]);
                }
            }

            $used[$slotKey] = true;
            $updSlot->execute([
                $p['pallet_sscc'],
                mb_substr((string)($p['commodity_name'] ?: 'Kargo LCL Terverifikasi'), 0, 145),
                $parsed['blok'],
                $parsed['bagian'],
                $parsed['rak']
            ]);
        }
    } catch (Throwable $e) {}
}

/**
 * Validasi ketersediaan slot rak di master_lokasi (anti-bentrok).
 * Bila $auto_fallback = true dan slot terisi, otomatis mencari level/blok kosong terdekat di sektor yang sama.
 */
function cfs_allocate_or_validate_rack(PDO $pdo, ?string $requested_rack, bool $auto_fallback = true): array {
    cfs_ensure_master_lokasi_grid($pdo);
    $parsed = cfs_parse_rack_location($requested_rack) ?: ['blok' => '1', 'bagian' => 'A', 'rak' => 1, 'code' => 'AISLE-A-01-L1'];

    $chk = $pdo->prepare("SELECT status, pallet_sscc FROM master_lokasi WHERE blok = ? AND bagian = ? AND rak = ? LIMIT 1");
    $chk->execute([$parsed['blok'], $parsed['bagian'], $parsed['rak']]);
    $cur = $chk->fetch(PDO::FETCH_ASSOC);

    if (!$cur || strtolower(trim((string)$cur['status'])) !== 'terisi') {
        return ['ok' => true, 'code' => $parsed['code'], 'parsed' => $parsed, 'fallback_used' => false];
    }

    if (!$auto_fallback) {
        return [
            'ok' => false,
            'code' => $parsed['code'],
            'occupied_by' => $cur['pallet_sscc'] ?: 'PALET-AKTIF',
            'message' => "Slot {$parsed['code']} sudah terisi oleh SSCC " . ($cur['pallet_sscc'] ?: '-') . "."
        ];
    }

    // 1) Cari level kosong pada blok & sektor yang sama
    $stSame = $pdo->prepare("SELECT blok, bagian, rak FROM master_lokasi WHERE blok = ? AND bagian = ? AND status <> 'terisi' ORDER BY rak ASC LIMIT 1");
    $stSame->execute([$parsed['blok'], $parsed['bagian']]);
    $free = $stSame->fetch(PDO::FETCH_ASSOC);

    // 2) Cari di blok lain dalam sektor yang sama
    if (!$free) {
        $stSec = $pdo->prepare("SELECT blok, bagian, rak FROM master_lokasi WHERE bagian = ? AND status <> 'terisi' ORDER BY blok ASC, rak ASC LIMIT 1");
        $stSec->execute([$parsed['bagian']]);
        $free = $stSec->fetch(PDO::FETCH_ASSOC);
    }

    // 3) Cari di seluruh gudang
    if (!$free) {
        $free = $pdo->query("SELECT blok, bagian, rak FROM master_lokasi WHERE status <> 'terisi' ORDER BY blok ASC, bagian ASC, rak ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    }

    if (!$free) {
        return ['ok' => false, 'code' => $parsed['code'], 'occupied_by' => $cur['pallet_sscc'], 'message' => 'Seluruh 144 slot rak Gudang CFS penuh (100% terisi).'];
    }

    $newCode = sprintf('AISLE-%s-%02d-L%d', strtoupper($free['bagian']), (int)$free['blok'], (int)$free['rak']);
    return [
        'ok' => true,
        'code' => $newCode,
        'parsed' => ['blok' => (string)$free['blok'], 'bagian' => strtoupper($free['bagian']), 'rak' => (int)$free['rak'], 'code' => $newCode],
        'fallback_used' => true,
        'requested_code' => $parsed['code']
    ];
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

/** Masukkan payload gagal atau ter-buffer ke DLQ / Backpressure Queue tanpa menghentikan alur utama */
function dlq_push(PDO $pdo, ?string $event_id, ?string $event_type, ?string $source, string $reason, $payload, string $status = 'QUARANTINED', array $anomalyMeta = []): int {
    try {
        $allowed = ['QUARANTINED', 'BUFFERED', 'RESOLVED', 'DISCARDED'];
        $safeStatus = in_array($status, $allowed, true) ? $status : 'QUARANTINED';
        $pInner = is_array($payload) ? ($payload['payload'] ?? $payload) : [];
        $anomCode = $anomalyMeta['anomaly_code'] ?? ($pInner['anomaly_code'] ?? null);
        $pgCode   = $anomalyMeta['pre_gate_code'] ?? ($pInner['pre_gate_code'] ?? null);
        $cNo      = $anomalyMeta['container_number'] ?? ($pInner['container_number'] ?? null);
        $plate    = $anomalyMeta['truck_plate'] ?? ($pInner['truck_plate'] ?? null);
        $st = $pdo->prepare("INSERT INTO integration_dlq (event_id, event_type, source_device, reason, payload_json, status, anomaly_code, pre_gate_code, container_number, truck_plate) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $st->execute([$event_id, $event_type, $source, mb_substr($reason, 0, 250), json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $safeStatus, $anomCode, $pgCode, $cNo, $plate]);
        return (int)$pdo->lastInsertId();
    } catch (Throwable $e) {
        return 0;
    }
}

function cidp_bcp_cache_file(): string {
    return dirname(__DIR__) . '/cache/bcp_mode.json';
}

function cidp_rate_cache_file(): string {
    return dirname(__DIR__) . '/cache/rate_limit_window.json';
}

function cidp_get_bcp_state(?PDO $pdo = null): array {
    $file = cidp_bcp_cache_file();
    $state = [
        'failover_active' => false,
        'mode'            => 'PRIMARY_ONLINE',
        'reason'          => 'Sistem cloud ERP Odoo & CEISA 4.0 aktif normal.',
        'updated_at'      => date('c'),
        'buffered_count'  => 0,
        'rpo_seconds'     => 0,
        'rto_target_sec'  => 10,
        'ats_genset'      => 'STANDBY (1500kVA / <7s)'
    ];
    if (file_exists($file)) {
        $raw = @json_decode((string)@file_get_contents($file), true);
        if (is_array($raw)) {
            $state['failover_active'] = !empty($raw['failover_active']);
            $state['mode']            = $state['failover_active'] ? 'BCP_EDGE_BUFFER' : 'PRIMARY_ONLINE';
            if (!empty($raw['reason'])) $state['reason'] = (string)$raw['reason'];
            if (!empty($raw['updated_at'])) $state['updated_at'] = (string)$raw['updated_at'];
        }
    }
    if ($pdo instanceof PDO) {
        try {
            $state['buffered_count'] = (int)$pdo->query("SELECT COUNT(*) FROM integration_dlq WHERE status = 'BUFFERED'")->fetchColumn();
        } catch (Throwable $e) {}
    }
    return $state;
}

function cidp_set_bcp_state(bool $active, string $reason = ''): array {
    $dir = dirname(cidp_bcp_cache_file());
    if (!is_dir($dir)) @mkdir($dir, 0777, true);
    $payload = [
        'failover_active' => $active,
        'mode'            => $active ? 'BCP_EDGE_BUFFER' : 'PRIMARY_ONLINE',
        'reason'          => $reason !== '' ? $reason : ($active
            ? 'Mode BCP Store-and-Forward aktif: event dialihkan aman ke Edge Buffer lokal (Zero Data Loss).'
            : 'Koneksi utama pulih: Primary Cloud & Database Sinkron aktif.'),
        'updated_at'      => date('c')
    ];
    @file_put_contents(cidp_bcp_cache_file(), json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    return cidp_get_bcp_state();
}

function cidp_check_rate_limit(string $client_key, int $max_per_window = 20, int $window_sec = 5, bool $bypass = false): array {
    if ($bypass) {
        return ['allowed' => true, 'count' => 0, 'limit' => $max_per_window, 'window_sec' => $window_sec, 'bypassed' => true];
    }
    $file = cidp_rate_cache_file();
    $dir = dirname($file);
    if (!is_dir($dir)) @mkdir($dir, 0777, true);

    $now = microtime(true);
    $fp = @fopen($file, 'c+');
    $buckets = [];
    if ($fp) {
        @flock($fp, LOCK_EX);
        $content = stream_get_contents($fp);
        $decoded = @json_decode((string)$content, true);
        if (is_array($decoded)) $buckets = $decoded;

        $arr = isset($buckets[$client_key]) && is_array($buckets[$client_key]) ? $buckets[$client_key] : [];
        $cutoff = $now - $window_sec;
        $arr = array_values(array_filter($arr, static function ($ts) use ($cutoff) {
            return (float)$ts >= $cutoff;
        }));
        $current_count = count($arr);
        $allowed = ($current_count < $max_per_window);
        if ($allowed) {
            $arr[] = $now;
            $current_count++;
        }
        $buckets[$client_key] = $arr;
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($buckets));
        fflush($fp);
        @flock($fp, LOCK_UN);
        fclose($fp);
        return ['allowed' => $allowed, 'count' => $current_count, 'limit' => $max_per_window, 'window_sec' => $window_sec];
    }
    return ['allowed' => true, 'count' => 1, 'limit' => $max_per_window, 'window_sec' => $window_sec];
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

// =============================================================================
// YARD OCCUPANCY MATRIX SSoT HELPERS (BLOK - BAY - ROW - TIER)
// =============================================================================
function yard_block_catalog(): array {
    return [
        'A'      => ['code' => 'A',      'name' => 'Blok A — Laden Ekspor',        'zone' => 'LADEN_EXP',   'bays' => 10, 'rows' => 4, 'tiers' => 4, 'color' => 'blue',    'equipment' => 'RS-01 / RTG-02'],
        'B'      => ['code' => 'B',      'name' => 'Blok B — Laden Impor',         'zone' => 'LADEN_IMP',   'bays' => 10, 'rows' => 4, 'tiers' => 4, 'color' => 'sky',     'equipment' => 'RS-02 / RTG-03'],
        'C'      => ['code' => 'C',      'name' => 'Blok C — Domestik Fast-Turn',  'zone' => 'DOMESTIC',    'bays' => 10, 'rows' => 4, 'tiers' => 4, 'color' => 'emerald', 'equipment' => 'RS-04 / RTG-04'],
        'D'      => ['code' => 'D',      'name' => 'Blok D — Buffer Siding KA',    'zone' => 'BUFFER_RAIL', 'bays' => 10, 'rows' => 4, 'tiers' => 4, 'color' => 'purple',  'equipment' => 'RS-03 / RTG-05'],
        'E'      => ['code' => 'E',      'name' => 'Blok E — Karantina / Behandle','zone' => 'QUARANTINE',  'bays' => 6,  'rows' => 4, 'tiers' => 4, 'color' => 'rose',    'equipment' => 'RS-02'],
        'DG'     => ['code' => 'DG',     'name' => 'Blok DG — Hazard IMDG B3',     'zone' => 'DG_HAZARD',   'bays' => 4,  'rows' => 4, 'tiers' => 3, 'color' => 'red',     'equipment' => 'RS-02'],
        'EMPTY'  => ['code' => 'EMPTY',  'name' => 'Blok Empty — Depo Kosong',     'zone' => 'EMPTY_DEPO',  'bays' => 6,  'rows' => 4, 'tiers' => 4, 'color' => 'slate',   'equipment' => 'RS-04'],
        'REEFER' => ['code' => 'REEFER', 'name' => 'Blok Reefer — Cold Chain',     'zone' => 'REEFER_380V', 'bays' => 4,  'rows' => 4, 'tiers' => 3, 'color' => 'cyan',    'equipment' => 'RS-03'],
    ];
}

function yard_format_coord($val): string {
    $n = max(1, (int)$val);
    return sprintf('%02d', $n);
}

function yard_normalize_block_key(?string $raw): string {
    $b = strtoupper(trim((string)$raw));
    if ($b === 'RAIL') return 'D';
    $cat = yard_block_catalog();
    return isset($cat[$b]) ? $b : 'A';
}

function yard_validate_slot(PDO $pdo, string $block, int $bay, int $row, int $tier, ?string $excludeContainer = null): array {
    $cat = yard_block_catalog();
    $blkKey = strtoupper(trim($block));
    if (!isset($cat[$blkKey])) {
        return [
            'ok' => false,
            'code' => '',
            'conflict_container' => null,
            'gravity_warning' => null,
            'message' => "Blok Yard '{$blkKey}' tidak terdaftar dalam tata letak CY standar."
        ];
    }

    $cfg = $cat[$blkKey];
    $maxBay  = max(12, (int)$cfg['bays']);
    $maxRow  = max(6, (int)$cfg['rows']);
    $maxTier = max(5, (int)$cfg['tiers']);
    if ($bay < 1 || $bay > $maxBay || $row < 1 || $row > $maxRow || $tier < 1 || $tier > $maxTier) {
        return [
            'ok' => false,
            'code' => '',
            'conflict_container' => null,
            'gravity_warning' => null,
            'message' => "Koordinat slot di luar batas Blok {$blkKey} (Bay 1–{$maxBay}, Row 1–{$maxRow}, Tier 1–{$maxTier})."
        ];
    }

    $bayStr  = yard_format_coord($bay);
    $rowStr  = yard_format_coord($row);
    $tierStr = yard_format_coord($tier);
    $code    = "{$blkKey}-B{$bayStr}-R{$rowStr}-T{$tierStr}";

    $sql = "SELECT container_number FROM containers
            WHERE status = 'in_yard'
              AND UPPER(TRIM(COALESCE(block,''))) = ?
              AND CAST(COALESCE(NULLIF(bay,''), '0') AS UNSIGNED) = ?
              AND CAST(COALESCE(NULLIF(`row`,''), '0') AS UNSIGNED) = ?
              AND CAST(COALESCE(NULLIF(tier,''), '0') AS UNSIGNED) = ?";
    $params = [$blkKey, $bay, $row, $tier];
    if ($excludeContainer !== null && trim($excludeContainer) !== '') {
        $sql .= " AND container_number <> ?";
        $params[] = strtoupper(trim($excludeContainer));
    }
    $sql .= " LIMIT 1";

    $st = $pdo->prepare($sql);
    $st->execute($params);
    $conflict = $st->fetchColumn();
    if ($conflict) {
        return [
            'ok' => false,
            'code' => $code,
            'conflict_container' => (string)$conflict,
            'gravity_warning' => null,
            'message' => "ANTI-BENTROK: Slot {$code} sudah ditempati oleh peti kemas {$conflict}!"
        ];
    }

    $gravityWarning = null;
    if ($tier > 1) {
        $stBelow = $pdo->prepare("SELECT CAST(COALESCE(NULLIF(tier,''), '0') AS UNSIGNED) AS t
            FROM containers
            WHERE status = 'in_yard'
              AND UPPER(TRIM(COALESCE(block,''))) = ?
              AND CAST(COALESCE(NULLIF(bay,''), '0') AS UNSIGNED) = ?
              AND CAST(COALESCE(NULLIF(`row`,''), '0') AS UNSIGNED) = ?
              AND CAST(COALESCE(NULLIF(tier,''), '0') AS UNSIGNED) < ?");
        $stBelow->execute([$blkKey, $bay, $row, $tier]);
        $occupiedBelow = array_map('intval', $stBelow->fetchAll(PDO::FETCH_COLUMN));
        $missing = [];
        for ($t = 1; $t < $tier; $t++) {
            if (!in_array($t, $occupiedBelow, true)) $missing[] = "T" . yard_format_coord($t);
        }
        if (!empty($missing)) {
            $gravityWarning = "Catatan Stabilitas Stacking: Level di bawahnya (" . implode(', ', $missing) . ") masih kosong pada {$blkKey}-B{$bayStr}-R{$rowStr}.";
        }
    }

    return [
        'ok' => true,
        'block' => $blkKey,
        'bay' => $bayStr,
        'row' => $rowStr,
        'tier' => $tierStr,
        'code' => $code,
        'conflict_container' => null,
        'gravity_warning' => $gravityWarning,
        'message' => "Slot {$code} tersedia."
    ];
}

/** Ambil seluruh parameter kalibrasi threshold hardware & rule keputusan dari DB */
function cidp_get_thresholds(PDO $pdo): array {
    $defaults = [
        'vgm_tolerance_pct'      => 5.0,
        'vgm_max_gross_kg'       => 35000.0,
        'anpr_min_confidence'    => 85.0,
        'reefer_max_deviation_c' => 2.0,
        'reefer_thermal_trip_c'  => 85.0,
        'free_dwell_days'        => 3,
        'rail_expected_axles'    => 126,
        'ats_switch_max_sec'     => 10.0,
        'vgm_zero_offset_kg'     => 0.0,
    ];
    try {
        $rows = $pdo->query("SELECT setting_key, setting_value FROM system_thresholds")->fetchAll(PDO::FETCH_KEY_PAIR);
        foreach ($defaults as $k => $v) {
            if (isset($rows[$k]) && is_numeric($rows[$k])) {
                $defaults[$k] = is_int($v) ? (int)$rows[$k] : (float)$rows[$k];
            }
        }
    } catch (Throwable $e) {}
    return $defaults;
}

/** Simpan pembaruan parameter kalibrasi threshold ke database */
function cidp_save_thresholds(PDO $pdo, array $updates): array {
    ensure_integration_schema($pdo);
    $stmt = $pdo->prepare("INSERT INTO system_thresholds (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
    foreach ($updates as $k => $v) {
        if ($v === null || $v === '') continue;
        $stmt->execute([(string)$k, (string)$v]);
    }
    return cidp_get_thresholds($pdo);
}

/**
 * Mesin Rekomendasi Slot Yard Otomatis (Anti-Bentrok & Minimalkan Shifting/CRP)
 * Menentukan blok & koordinat Bay-Row-Tier terbaik berdasarkan pabean, jenis kargo, dan berat kotor.
 */
function cidp_recommend_yard_slot(PDO $pdo, array $container, ?string $preferredBlock = null): array {
    $customs = strtoupper(trim((string)($container['customs_status'] ?? '')));
    $cargo   = strtolower(trim((string)($container['cargo_type'] ?? 'dry')));
    $size    = strtoupper(trim((string)($container['size_type'] ?? '40FT')));
    $weight  = (float)($container['gross_weight_kg'] ?? 20000);
    $ctrNo   = strtoupper(trim((string)($container['container_number'] ?? '')));

    $blk = strtoupper(trim((string)$preferredBlock));
    $reasonParts = [];

    if ($blk === '') {
        if (in_array($customs, ['RED_LANE_CHECK', 'RED_LANE', 'DOC_HOLD'], true)) {
            $blk = 'E';
            $reasonParts[] = "Status pabean {$customs} -> isolasi Blok E (Behandle/Karantina)";
        } elseif ($cargo === 'reefer' || strpos($size, 'R') !== false) {
            $blk = 'REEFER';
            $reasonParts[] = "Kargo Reefer -> zona daya listrik 380V (Blok REEFER)";
        } elseif ($cargo === 'dg') {
            $blk = 'DG';
            $reasonParts[] = "Kargo Dangerous Goods IMO -> zona bunding aman (Blok DG)";
        } elseif ($cargo === 'empty') {
            $blk = 'EMPTY';
            $reasonParts[] = "Empty Reposition -> zona depot kosong (Blok EMPTY)";
        } elseif (strpos($size, '20') !== false) {
            $blk = 'B';
            $reasonParts[] = "Kontainer 20ft dry/impor -> Blok B";
        } else {
            $blk = 'A';
            $reasonParts[] = "Kontainer 40ft/HC reguler -> Blok A";
        }
    } else {
        $reasonParts[] = "Penempatan ke Blok {$blk} ({$cargo})";
    }

    $cat = yard_block_catalog();
    $cfg = $cat[$blk] ?? $cat['A'];
    $maxBay  = min(10, max(4, (int)$cfg['bays']));
    $maxRow  = min(4, max(2, (int)$cfg['rows']));
    $maxTier = min(4, max(2, (int)$cfg['tiers']));

    $tierOrder = ($weight >= 24000) ? range(1, $maxTier) : array_merge(range(1, $maxTier));
    if ($weight >= 24000) {
        $reasonParts[] = "berat ≥ 24t (prioritas Tier rendah agar stabil & minim CRP)";
    }

    for ($tIdx = 0; $tIdx < count($tierOrder); $tIdx++) {
        $t = $tierOrder[$tIdx];
        for ($b = 1; $b <= $maxBay; $b++) {
            for ($r = 1; $r <= $maxRow; $r++) {
                $val = yard_validate_slot($pdo, $blk, $b, $r, $t, $ctrNo);
                if ($val['ok'] && empty($val['gravity_warning'])) {
                    return [
                        'ok'     => true,
                        'block'  => $val['block'],
                        'bay'    => $val['bay'],
                        'row'    => $val['row'],
                        'tier'   => $val['tier'],
                        'code'   => $val['code'],
                        'reason' => implode('; ', $reasonParts) . " — slot {$val['code']} kosong & terbebas double-handling."
                    ];
                }
            }
        }
    }

    return [
        'ok'     => false,
        'block'  => $blk,
        'bay'    => '01',
        'row'    => '01',
        'tier'   => '01',
        'code'   => "{$blk}-B01-R01-T01",
        'reason' => "Fallback slot {$blk}-B01-R01-T01 (kapasitas blok hampir penuh)."
    ];
}

/**
 * Mesin Rekomendasi Keputusan Operasional Terpadu (Control Tower Decision Engine)
 * Mengolah status aktual sensor & database menjadi tindakan prioritas berbasis aturan.
 */
function cidp_build_decision_report(PDO $pdo): array {
    $th = cidp_get_thresholds($pdo);
    $freeDays = max(1, (int)$th['free_dwell_days']);
    $decisions = [];

    try {
        // 1. Reefer ALARM keputusan darurat suhu/ATS
        $rfAlms = $pdo->query("SELECT container_number, socket_id, set_temp_c, supply_temp_c FROM reefer_telemetry WHERE status = 'ALARM' ORDER BY id DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rfAlms)) {
            $first = $rfAlms[0];
            $decisions[] = [
                'priority' => 'critical',
                'source'   => 'Sensor Suhu LoRaWAN + ATS',
                'title'    => count($rfAlms) . " Alarm Suhu Reefer (> ±{$th['reefer_max_deviation_c']}°C)",
                'reason'   => "{$first['container_number']} ({$first['socket_id']}) terbaca {$first['supply_temp_c']}°C vs setpoint {$first['set_temp_c']}°C.",
                'action'   => 'Inspeksi kompresor segera & verifikasi backup ATS Genset.',
                'href'     => 'dashboard.php?page=reefer',
                'cta'      => 'Tangani Reefer'
            ];
        }

        // 2. Kontainer HOLD / Jalur Merah / E-Seal Tamper
        $redCnt = (int)$pdo->query("SELECT COUNT(*) FROM containers WHERE status NOT IN ('gate_out','expected') AND customs_status IN ('RED_LANE_CHECK','DOC_HOLD')")->fetchColumn();
        if ($redCnt > 0) {
            $decisions[] = [
                'priority' => 'high',
                'source'   => 'CEISA 4.0 + X-Ray + JT701 E-Seal',
                'title'    => "{$redCnt} Box Terkunci Interlock Pabean",
                'reason'   => 'Status RED_LANE_CHECK / DOC_HOLD terdeteksi pada manifest pabean/segel.',
                'action'   => 'Prioritaskan pemindahan Blok E untuk X-Ray 6 MeV / input LHP Behandle.',
                'href'     => 'dashboard.php?page=customs',
                'cta'      => 'Buka Bea Cukai'
            ];
        }

        // 3. Antrean DLQ Anomali Gate/Sensor
        $dlqCnt = (int)$pdo->query("SELECT COUNT(*) FROM integration_dlq WHERE status = 'QUARANTINED'")->fetchColumn();
        if ($dlqCnt > 0) {
            $decisions[] = [
                'priority' => 'high',
                'source'   => 'ANPR + Weighbridge 80T + DLQ',
                'title'    => "{$dlqCnt} Payload Anomali di Karantina DLQ",
                'reason'   => "Payload gagal aturan (deviasi VGM > {$th['vgm_tolerance_pct']}%, e-DO expired, atau mismatch OCR).",
                'action'   => 'Putuskan SOP Putar Balik (Turn-Back) atau Verifikasi & Override.',
                'href'     => 'dashboard.php?page=integration',
                'cta'      => 'Resolusi DLQ'
            ];
        }

        // 4. Demurrage / Overstay vs Masa Bebas
        $overStay = (int)$pdo->query("SELECT COUNT(*) FROM containers WHERE status = 'in_yard' AND gate_in_time IS NOT NULL AND DATEDIFF(NOW(), gate_in_time) > {$freeDays}")->fetchColumn();
        if ($overStay > 0) {
            $decisions[] = [
                'priority' => 'medium',
                'source'   => 'Timer Dwell Time YMS vs Billing',
                'title'    => "{$overStay} Box Melewati Free Time (> {$freeDays} Hari)",
                'reason'   => "Dwell time melampaui batas free storage {$freeDays} hari dan mulai terkena argo progresif.",
                'action'   => 'Dorong percepatan Gate-Out / muat KA Outbound & validasi tagihan penumpukan.',
                'href'     => 'dashboard.php?page=yard',
                'cta'      => 'Kelola Yard'
            ];
        }

        // 5. Antrean Truk Gate vs Pilihan Moda Rel
        $gateQueue = (int)$pdo->query("SELECT COUNT(*) FROM trucks WHERE status IN ('queuing','at_gate')")->fetchColumn();
        if ($gateQueue >= 4) {
            $decisions[] = [
                'priority' => 'medium',
                'source'   => 'ANPR Gate + Frauscher Siding',
                'title'    => "Kepadatan Gate Terdeteksi ({$gateQueue} Truk Antre)",
                'reason'   => 'Volume truk di kompleks gerbang tinggi; potensi bottleneck buffer.',
                'action'   => 'Aktifkan seluruh 6 lajur Gate-In dan alihkan muatan ekspor siap ke Siding KA.',
                'href'     => 'dashboard.php?page=gate',
                'cta'      => 'Atur Gate'
            ];
        }
    } catch (Throwable $e) {}

    if (empty($decisions)) {
        $decisions[] = [
            'priority' => 'ok',
            'source'   => 'Seluruh Sensor 4-Layer',
            'title'    => 'Operasi Seluruh Zona Sehat & Terkendali',
            'reason'   => 'Tidak ada pelanggaran ambang batas VGM, suhu Reefer, segel JT701, ataupun DLQ aktif.',
            'action'   => 'Pertahankan dispatch standar dan pantau stream telemetri masuk.',
            'href'     => 'dashboard.php?page=twin',
            'cta'      => 'Pantau Live 3D'
        ];
    }

    return $decisions;
}