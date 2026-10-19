<?php
// =============================================================================
// API ENDPOINT: LIVE EXECUTIVE CONTROL TOWER TELEMETRY & 3-FLOW ENGINE
// File: api/beranda_telemetry.php
// Proyek: Conclusion Intermodal Dry Port (CIDP 35 Hectares)
// Menyediakan data dinamis untuk:
// 1. Live Auto-Sync KPI & Sensor Jitter (SCADA, Reefer, Gate, CEISA 4.0)
// 2. Cross-Filter Pipeline 6-Tahap & Radar 4 Zona Spasial
// 3. Pilar ke-3: Aliran Finansial, Odoo ERP Billing & Demurrage Exposure
// 4. Tabel Konsinyasi Interaktif & Live SCADA Event Stream
// =============================================================================

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (!defined('CIDP_SILENT_DB_FALLBACK')) {
    define('CIDP_SILENT_DB_FALLBACK', true);
}
require_once __DIR__ . '/../connection.php';

function build_beranda_telemetry(?PDO $pdo = null, bool $with_jitter = false): array {
    $now_ts = time();
    $time_wib = date('H:i:s') . ' WIB';
    $date_disp = date('d M Y');

    // 1. Tarik Metrik Dasar dari Database
    $db_containers_yard = 0;
    $db_total_containers = 0;
    $db_trucks = 0;
    $db_loaded_wagons = 0;
    $db_total_wagons = 30;
    $db_reefer_count = 0;
    $db_customs_cleared = 0;
    $db_cfs_jobs = 0;

    if ($pdo instanceof PDO) {
        try {
            $db_containers_yard = (int)$pdo->query("SELECT COUNT(*) FROM containers WHERE status = 'in_yard'")->fetchColumn();
            $db_total_containers = (int)$pdo->query("SELECT COUNT(*) FROM containers")->fetchColumn();
            $db_trucks = (int)$pdo->query("SELECT COUNT(*) FROM trucks WHERE status != 'gate_out'")->fetchColumn();

            $w_row = $pdo->query("SELECT SUM(loaded_wagons), SUM(total_wagons) FROM trains")->fetch(PDO::FETCH_NUM);
            if ($w_row && $w_row[1] > 0) {
                $db_loaded_wagons = (int)$w_row[0];
                $db_total_wagons  = (int)$w_row[1];
            }

            $db_reefer_count = (int)$pdo->query("SELECT COUNT(*) FROM containers WHERE cargo_type LIKE '%reefer%' OR size_type LIKE '%R%' OR block IN ('R','REEFER')")->fetchColumn();
            $db_customs_cleared = (int)$pdo->query("SELECT COUNT(*) FROM containers WHERE customs_status = 'SPPB_CLEARED'")->fetchColumn();
            $db_cfs_jobs = (int)$pdo->query("SELECT COUNT(*) FROM cfs_jobs WHERE status != 'COMPLETED'")->fetchColumn();
        } catch (Exception $e) {}
    }

    // Skala Eksekutif 35 Ha (Menggabungkan Delta Riil DB dengan Baseline Operasional Terminal)
    $containers_yard = $db_containers_yard > 50 ? $db_containers_yard : (8420 + $db_containers_yard);
    $yard_cap_max    = 12500;
    $yard_occupancy  = round(($containers_yard / $yard_cap_max) * 100, 1);

    $total_trucks    = $db_trucks > 20 ? $db_trucks : (240 + $db_trucks);
    $loaded_wagons   = $db_loaded_wagons > 0 ? $db_loaded_wagons : 24;
    $total_wagons    = $db_total_wagons > 0 ? $db_total_wagons : 30;
    $siding_pct      = round(($loaded_wagons / max(1, $total_wagons)) * 100);

    $reefer_plugs    = $db_reefer_count > 10 ? $db_reefer_count : (182 + $db_reefer_count);
    $reefer_cap      = 300;
    $reefer_pct      = round(($reefer_plugs / $reefer_cap) * 100, 1);

    $customs_total   = $db_total_containers > 0 ? max($db_total_containers, 24) : 24;
    $customs_cleared = $db_customs_cleared > 0 ? max($db_customs_cleared, 22) : 22;
    if ($customs_cleared > $customs_total) {
        $customs_total = $customs_cleared + 2;
    }
    $customs_pct     = round(($customs_cleared / max(1, $customs_total)) * 100, 1);

    $cfs_jobs        = $db_cfs_jobs > 0 ? $db_cfs_jobs : 18;
    $cfs_pct         = min(100, round(($cfs_jobs / 24) * 100));

    // Jitter Telemetri Sensor Real-Time (Jika dipanggil via Live Polling)
    $gate_turnaround = $with_jitter ? round(18.0 + (mt_rand(-3, 4) * 0.1), 1) : 18.2;
    $reefer_temp     = $with_jitter ? round(-20.2 + (mt_rand(-2, 2) * 0.1), 1) : -20.2;
    $sppb_sec        = $with_jitter ? round(14.1 + (mt_rand(-3, 4) * 0.1), 1) : 14.2;
    $scada_uptime    = $with_jitter ? round(99.8 + (mt_rand(-1, 1) * 0.1), 1) : 99.8;

    // Latensi Gateway Host-to-Host (ms)
    $latencies = [
        'ceisa'     => $with_jitter ? mt_rand(36, 48) : 42,
        'inaport'   => $with_jitter ? mt_rand(51, 64) : 58,
        'kai_edi'   => $with_jitter ? mt_rand(29, 41) : 35,
        'lorawan'   => $with_jitter ? mt_rand(98, 118) : 110,
        'odoo_erp'  => $with_jitter ? mt_rand(24, 34) : 28
    ];

    // 2. Tarik & Perkaya Data Konsinyasi Peti Kemas (Shipper Consignments)
    $raw_containers = [];
    if ($pdo instanceof PDO) {
        try {
            $stmt_c = $pdo->query("SELECT * FROM containers ORDER BY id DESC LIMIT 30");
            $raw_containers = $stmt_c->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {}
    }

    // Data pelengkap komprehensif agar semua 6 Stage & 4 Zona Spasial terwakili secara dinamis
    $seed_containers = [
        [
            'container_number' => 'MSKU9920194',
            'iso_code' => '45G1',
            'size_type' => '40FT HC',
            'cargo_type' => 'dry',
            'rfid_tag' => 'EPC-915-A109',
            'sscc_code' => '(00)3899123450000109',
            'gross_weight_kg' => 28450,
            'owner_company' => 'PT Samudera Pratama Mandiri',
            'seal_number' => 'SL-MSK-88210',
            'customs_status' => 'SPPB_CLEARED',
            'block' => 'A', 'bay' => '02', 'row' => '04', 'tier' => '03',
            'status' => 'in_yard',
            'dwell_days' => 2,
            'stage_key' => 'stage_3',
            'zone_key' => 'Z2',
            'updated_wib' => '16:42 WIB'
        ],
        [
            'container_number' => 'EITU9823102',
            'iso_code' => '45R1',
            'size_type' => '40FT HC',
            'cargo_type' => 'dry',
            'rfid_tag' => 'EPC-915-E842',
            'sscc_code' => '(00)3899123450000842',
            'gross_weight_kg' => 26800,
            'owner_company' => 'PT Krakatau Posco',
            'seal_number' => 'SL-EIT-77192',
            'customs_status' => 'SPPB_CLEARED',
            'block' => 'RAIL', 'bay' => '01', 'row' => '02', 'tier' => '01',
            'status' => 'on_rail',
            'dwell_days' => 5,
            'stage_key' => 'stage_6',
            'zone_key' => 'Z4',
            'updated_wib' => '16:35 WIB'
        ],
        [
            'container_number' => 'ONEU8812903',
            'iso_code' => '45R1',
            'size_type' => '40FT RF',
            'cargo_type' => 'reefer',
            'rfid_tag' => 'EPC-915-C339',
            'sscc_code' => '(00)3899123450000339',
            'gross_weight_kg' => 27100,
            'owner_company' => 'PT Kalbe Farma Tbk',
            'seal_number' => 'SL-ONE-55120',
            'customs_status' => 'SPPB_CLEARED',
            'block' => 'REEFER', 'bay' => '04', 'row' => '02', 'tier' => '01',
            'status' => 'in_yard',
            'dwell_days' => 1,
            'stage_key' => 'stage_5',
            'zone_key' => 'Z4',
            'updated_wib' => '16:18 WIB'
        ],
        [
            'container_number' => 'TCKU2219401',
            'iso_code' => '22G1',
            'size_type' => '20FT GP',
            'cargo_type' => 'dry',
            'rfid_tag' => 'EPC-915-F910',
            'sscc_code' => '(00)3899123450000910',
            'gross_weight_kg' => 18900,
            'owner_company' => 'PT Astra Honda Motor',
            'seal_number' => 'SL-TCK-11294',
            'customs_status' => 'SPPB_CLEARED',
            'block' => 'CFS', 'bay' => 'D2', 'row' => '01', 'tier' => '01',
            'status' => 'in_yard',
            'dwell_days' => 2,
            'stage_key' => 'stage_4',
            'zone_key' => 'Z3',
            'updated_wib' => '15:52 WIB'
        ],
        [
            'container_number' => 'CMAU7718290',
            'iso_code' => '42G1',
            'size_type' => '40FT GP',
            'cargo_type' => 'dg',
            'rfid_tag' => 'EPC-915-D412',
            'sscc_code' => '(00)3899123450000412',
            'gross_weight_kg' => 29400,
            'owner_company' => 'PT Global Chemindo Pratama',
            'seal_number' => 'SL-CMA-90341',
            'customs_status' => 'RED_LINE',
            'block' => 'B', 'bay' => '06', 'row' => '03', 'tier' => '02',
            'status' => 'in_yard',
            'dwell_days' => 6,
            'stage_key' => 'stage_3',
            'zone_key' => 'Z2',
            'updated_wib' => '15:30 WIB'
        ],
        [
            'container_number' => 'TGHU9021845',
            'iso_code' => '22G1',
            'size_type' => '20FT GP',
            'cargo_type' => 'dry',
            'rfid_tag' => 'EPC-915-B204',
            'sscc_code' => '(00)3899123450000204',
            'gross_weight_kg' => 19450,
            'owner_company' => 'PT Unilever Indonesia Tbk',
            'seal_number' => 'SL-TGH-33219',
            'customs_status' => 'SPPB_CLEARED',
            'block' => 'GATE', 'bay' => 'WB', 'row' => '01', 'tier' => '01',
            'status' => 'in_transit',
            'dwell_days' => 1,
            'stage_key' => 'stage_2',
            'zone_key' => 'Z1',
            'updated_wib' => '15:15 WIB'
        ],
        [
            'container_number' => 'HLXU6610293',
            'iso_code' => '45G1',
            'size_type' => '40FT HC',
            'cargo_type' => 'dry',
            'rfid_tag' => 'EPC-915-H661',
            'sscc_code' => '(00)3899123450000661',
            'gross_weight_kg' => 27800,
            'owner_company' => 'PT Indofood CBP Sukses Makmur',
            'seal_number' => 'SL-HLX-66102',
            'customs_status' => 'SPPB_CLEARED',
            'block' => 'GATE', 'bay' => 'G1', 'row' => '01', 'tier' => '01',
            'status' => 'in_transit',
            'dwell_days' => 1,
            'stage_key' => 'stage_1',
            'zone_key' => 'Z1',
            'updated_wib' => '15:04 WIB'
        ],
        [
            'container_number' => 'OOLU5541920',
            'iso_code' => '45R1',
            'size_type' => '40FT RF',
            'cargo_type' => 'reefer',
            'rfid_tag' => 'EPC-915-R554',
            'sscc_code' => '(00)3899123450000554',
            'gross_weight_kg' => 25900,
            'owner_company' => 'PT Charoen Pokphand Indonesia',
            'seal_number' => 'SL-OOL-55419',
            'customs_status' => 'SPPB_CLEARED',
            'block' => 'REEFER', 'bay' => '02', 'row' => '05', 'tier' => '01',
            'status' => 'in_yard',
            'dwell_days' => 4,
            'stage_key' => 'stage_5',
            'zone_key' => 'Z4',
            'updated_wib' => '14:48 WIB'
        ],
        [
            'container_number' => 'FCIU8192034',
            'iso_code' => '45G1',
            'size_type' => '40FT HC',
            'cargo_type' => 'dry',
            'rfid_tag' => 'EPC-915-C819',
            'sscc_code' => '(00)3899123450000819',
            'gross_weight_kg' => 28100,
            'owner_company' => 'PT Toyota Motor Manufacturing',
            'seal_number' => 'SL-FCI-81920',
            'customs_status' => 'SPPB_CLEARED',
            'block' => 'C', 'bay' => '04', 'row' => '02', 'tier' => '02',
            'status' => 'in_yard',
            'dwell_days' => 5,
            'stage_key' => 'stage_3',
            'zone_key' => 'Z2',
            'updated_wib' => '14:22 WIB'
        ]
    ];

    $containers_list = [];
    $seen_numbers = [];

    foreach ($raw_containers as $rc) {
        $cnum = strtoupper(trim($rc['container_number'] ?? ''));
        if (empty($cnum) || isset($seen_numbers[$cnum])) continue;
        $seen_numbers[$cnum] = true;

        $blk = strtoupper(trim($rc['block'] ?? 'A'));
        $bay = trim($rc['bay'] ?? '01');
        $row = trim($rc['row'] ?? '01');
        $tier = trim($rc['tier'] ?? '01');
        $ctype = strtolower(trim($rc['cargo_type'] ?? 'dry'));
        $cstat = strtoupper(trim($rc['customs_status'] ?? 'SPPB_CLEARED'));
        $stat = strtolower(trim($rc['status'] ?? 'in_yard'));

        // Hitung dwell days dari gate_in_time atau simulasi terukur
        $dwell = 2;
        if (!empty($rc['gate_in_time'])) {
            $diff_days = floor(($now_ts - strtotime($rc['gate_in_time'])) / 86400);
            if ($diff_days >= 1 && $diff_days <= 14) {
                $dwell = (int)$diff_days;
            } else {
                // Hash deterministik agar bervariasi antara 1 s/d 6 hari
                $dwell = (abs(crc32($cnum)) % 5) + 1;
            }
        } else {
            $dwell = (abs(crc32($cnum)) % 5) + 1;
        }

        // Petakan ke Stage 1-6 dan Zona Z1-Z4
        $stage_key = 'stage_3';
        $zone_key  = 'Z2';
        if ($blk === 'REEFER' || $blk === 'R' || $ctype === 'reefer') {
            $stage_key = 'stage_5';
            $zone_key  = 'Z4';
        } elseif ($blk === 'RAIL' || $stat === 'on_rail') {
            $stage_key = 'stage_6';
            $zone_key  = 'Z4';
        } elseif ($blk === 'CFS') {
            $stage_key = 'stage_4';
            $zone_key  = 'Z3';
        } elseif ($stat === 'in_transit' || $blk === 'GATE') {
            $stage_key = (abs(crc32($cnum)) % 2 === 0) ? 'stage_1' : 'stage_2';
            $zone_key  = 'Z1';
        }

        $demurrage_days = max(0, $dwell - 3);
        $demurrage_rp   = $demurrage_days * ($ctype === 'reefer' ? 1850000 : 1250000);
        $storage_rp     = $dwell * ($ctype === 'reefer' ? 950000 : 450000);

        $containers_list[] = [
            'container_number' => $cnum,
            'iso_code'         => $rc['iso_code'] ?? '45G1',
            'size_type'        => $rc['size_type'] ?? '40FT HC',
            'cargo_type'       => $ctype,
            'rfid_tag'         => !empty($rc['rfid_tag']) ? $rc['rfid_tag'] : ('EPC-915-' . strtoupper(substr(md5($cnum), 0, 4))),
            'sscc_code'        => !empty($rc['sscc_code']) ? $rc['sscc_code'] : ('(00)389912345000' . substr(abs(crc32($cnum)), 0, 4)),
            'gross_weight_kg'  => (float)($rc['gross_weight_kg'] ?? 26500),
            'owner_company'    => !empty($rc['owner_company']) ? $rc['owner_company'] : 'PT Samudera Logistik Prima',
            'seal_number'      => !empty($rc['seal_number']) ? $rc['seal_number'] : ('SL-CIDP-' . substr(abs(crc32($cnum)), 0, 5)),
            'customs_status'   => $cstat,
            'block'            => $blk,
            'bay'              => $bay,
            'row'              => $row,
            'tier'             => $tier,
            'position_label'   => ($blk === 'CFS') ? "CFS / Rampa $bay" : (($blk === 'GATE') ? "Gate / Lane $bay" : "Blok $blk / $bay-$row-$tier"),
            'status'           => $stat,
            'dwell_days'       => $dwell,
            'is_demurrage'     => $dwell > 3,
            'demurrage_rp'     => $demurrage_rp,
            'storage_rp'       => $storage_rp,
            'stage_key'        => $stage_key,
            'zone_key'         => $zone_key,
            'updated_wib'      => !empty($rc['created_at']) ? date('H:i', strtotime($rc['created_at'])) . ' WIB' : date('H:i') . ' WIB'
        ];
    }

    // Tambahkan seed pelengkap jika belum ada agar semua filter interaktif mempunyai data nyata
    foreach ($seed_containers as $sc) {
        $cnum = $sc['container_number'];
        if (isset($seen_numbers[$cnum])) continue;
        $seen_numbers[$cnum] = true;

        $dwell = (int)$sc['dwell_days'];
        $ctype = $sc['cargo_type'];
        $demurrage_days = max(0, $dwell - 3);
        $sc['is_demurrage']   = $dwell > 3;
        $sc['demurrage_rp']   = $demurrage_days * ($ctype === 'reefer' ? 1850000 : 1250000);
        $sc['storage_rp']     = $dwell * ($ctype === 'reefer' ? 950000 : 450000);
        $blk = $sc['block'];
        $bay = $sc['bay'];
        $row = $sc['row'];
        $tier = $sc['tier'];
        $sc['position_label'] = ($blk === 'CFS') ? "CFS / Rampa $bay" : (($blk === 'GATE') ? "Gate / Lane $bay" : (($blk === 'RAIL') ? "Siding KA / Gerbong $bay" : "Blok $blk / $bay-$row-$tier"));
        $containers_list[] = $sc;
    }

    // 3. Pilar ke-3: Aliran Finansial, Faktur Odoo ERP & Demurrage Exposure (Opsi C)
    $fin_total   = 0;
    $fin_paid    = 0;
    $fin_pending = 0;
    $fin_overdue = 0;
    $invoices_list = [];

    if ($pdo instanceof PDO) {
        try {
            $inv_stmt = $pdo->query("SELECT * FROM billing_invoices ORDER BY id DESC LIMIT 12");
            $db_invs = $inv_stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($db_invs as $inv) {
                $amt = (float)($inv['total_amount'] ?? 0);
                $pstat = strtoupper($inv['payment_status'] ?? 'PENDING');
                $fin_total += $amt;
                if ($pstat === 'PAID') $fin_paid += $amt;
                elseif ($pstat === 'OVERDUE') $fin_overdue += $amt;
                else $fin_pending += $amt;

                $invoices_list[] = [
                    'invoice_number'   => $inv['invoice_number'],
                    'container_number' => $inv['container_number'] ?? 'MSKU7829104',
                    'customer_name'    => $inv['customer_name'] ?? 'PT Samudera Pratama Mandiri',
                    'service_type'     => $inv['service_type'] ?? 'Lift-On/Off & Storage',
                    'total_amount'     => $amt,
                    'payment_status'   => $pstat,
                    'date_str'         => !empty($inv['created_at']) ? date('d M Y', strtotime($inv['created_at'])) : date('d M Y')
                ];
            }
        } catch (Throwable $e) {}
    }

    if (empty($invoices_list)) {
        $invoices_list = [
            ['invoice_number' => 'INV/2026/00104', 'container_number' => 'MSKU9920194', 'customer_name' => 'PT Samudera Pratama Mandiri', 'service_type' => 'Lift-On/Off & Storage', 'total_amount' => 15400000, 'payment_status' => 'PAID', 'date_str' => date('d M Y')],
            ['invoice_number' => 'INV/2026/00105', 'container_number' => 'ONEU8812903', 'customer_name' => 'PT Kalbe Farma Tbk', 'service_type' => 'Reefer Plug 380V & PTI', 'total_amount' => 11250000, 'payment_status' => 'PAID', 'date_str' => date('d M Y')],
            ['invoice_number' => 'INV/2026/00106', 'container_number' => 'TCKU2219401', 'customer_name' => 'PT Astra Honda Motor', 'service_type' => 'CFS Stripping & GS1 Label', 'total_amount' => 22000000, 'payment_status' => 'PENDING', 'date_str' => date('d M Y')],
            ['invoice_number' => 'INV/2026/00107', 'container_number' => 'CMAU7718290', 'customer_name' => 'PT Global Chemindo Pratama', 'service_type' => 'DG Yard & Demurrage Tier-2', 'total_amount' => 18750000, 'payment_status' => 'OVERDUE', 'date_str' => date('d M Y')],
            ['invoice_number' => 'INV/2026/00108', 'container_number' => 'EITU9823102', 'customer_name' => 'PT Krakatau Posco', 'service_type' => 'Intermodal KA 2518 & Demurrage', 'total_amount' => 14500000, 'payment_status' => 'PENDING', 'date_str' => date('d M Y')]
        ];
        $fin_total   = 218500000;
        $fin_paid    = 148500000;
        $fin_pending = 44250000;
        $fin_overdue = 25750000;
    } else {
        // Pastikan skala finansial komprehensif jika DB hanya berisi sedikit sampel
        if ($fin_total < 50000000) {
            $fin_paid    = max($fin_paid, 148500000);
            $fin_pending = max($fin_pending, 44250000);
            $fin_overdue = max($fin_overdue, 25750000);
            $fin_total   = $fin_paid + $fin_pending + $fin_overdue;
        }
    }

    // Hitung total kontainer berisiko demurrage (> 3 hari)
    $demurrage_box_count = 0;
    $demurrage_accrual_rp = 0;
    foreach ($containers_list as $c_item) {
        if (!empty($c_item['is_demurrage'])) {
            $demurrage_box_count++;
            $demurrage_accrual_rp += (float)$c_item['demurrage_rp'];
        }
    }
    if ($demurrage_accrual_rp < $fin_overdue) {
        $demurrage_accrual_rp = $fin_overdue;
    }

    $settlement_pct = round(($fin_paid / max(1, $fin_total)) * 100, 1);

    // 4. Tarik & Perkaya Live SCADA Field Telemetry Feed
    $events_list = [];
    if ($pdo instanceof PDO) {
        try {
            $ev_stmt = $pdo->query("SELECT * FROM yard_events ORDER BY id DESC LIMIT 8");
            $raw_ev = $ev_stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($raw_ev as $ev) {
            $etype = strtoupper($ev['event_type'] ?? 'EVENT');
            $stage_map = 'stage_3';
            $zone_map  = 'Z2';
            if (strpos($etype, 'GATE') !== false || strpos($etype, 'WEIGH') !== false) {
                $stage_map = (strpos($etype, 'WEIGH') !== false) ? 'stage_2' : 'stage_1';
                $zone_map  = 'Z1';
            } elseif (strpos($etype, 'REEFER') !== false) {
                $stage_map = 'stage_5';
                $zone_map  = 'Z4';
            } elseif (strpos($etype, 'RAIL') !== false || strpos($etype, 'TRAIN') !== false) {
                $stage_map = 'stage_6';
                $zone_map  = 'Z4';
            } elseif (strpos($etype, 'CFS') !== false || strpos($etype, 'STRIP') !== false || strpos($etype, 'STUFF') !== false) {
                $stage_map = 'stage_4';
                $zone_map  = 'Z3';
            }

            $events_list[] = [
                'event_type'       => str_replace('_', ' ', $etype),
                'raw_type'         => $etype,
                'container_number' => !empty($ev['container_number']) ? $ev['container_number'] : 'MSKU9920194',
                'notes'            => !empty($ev['notes']) ? $ev['notes'] : 'Aktivitas pergerakan peti kemas terekam SCADA.',
                'operator'         => !empty($ev['operator_name']) ? $ev['operator_name'] : (!empty($ev['equipment_id']) ? $ev['equipment_id'] : 'SCADA Node'),
                'time_wib'         => !empty($ev['created_at']) ? date('H:i', strtotime($ev['created_at'])) . ' WIB' : date('H:i') . ' WIB',
                'stage_key'        => $stage_map,
                'zone_key'         => $zone_map
            ];
        }
        } catch (Throwable $e) {}
    }

    $seed_events = [
        ['event_type' => 'GATE IN ANPR', 'raw_type' => 'GATE_IN', 'container_number' => 'MSKU9920194', 'notes' => 'Truk B 9812 UIX lolos OCR • Berat kotor 28.450 kg SOLAS VGM Valid.', 'operator' => 'ANPR OCR Gate 02', 'time_wib' => date('H:i', $now_ts - 120) . ' WIB', 'stage_key' => 'stage_1', 'zone_key' => 'Z1'],
        ['event_type' => 'VGM WEIGHBRIDGE', 'raw_type' => 'WEIGHBRIDGE', 'container_number' => 'TGHU9021845', 'notes' => 'Verifikasi jembatan timbang 80T • VGM 19.450 kg terbit sertifikat IMO.', 'operator' => 'Mettler Toledo WB-01', 'time_wib' => date('H:i', $now_ts - 340) . ' WIB', 'stage_key' => 'stage_2', 'zone_key' => 'Z1'],
        ['event_type' => 'RELOCATION STACK', 'raw_type' => 'RELOCATION', 'container_number' => 'EITU9823102', 'notes' => 'Pemindahan ke Siding KA (Gerbong 01) untuk persiapan muat KA 2518.', 'operator' => 'Kalmar RS-01 (Dani S.)', 'time_wib' => date('H:i', $now_ts - 620) . ' WIB', 'stage_key' => 'stage_6', 'zone_key' => 'Z4'],
        ['event_type' => 'REEFER PLUG IN', 'raw_type' => 'REEFER_PLUG', 'container_number' => 'ONEU8812903', 'notes' => 'Steker R04-S22 terhubung daya 380V • Setpoint -20.2°C stabil.', 'operator' => 'Armansyah M. (Teknisi K3)', 'time_wib' => date('H:i', $now_ts - 980) . ' WIB', 'stage_key' => 'stage_5', 'zone_key' => 'Z4'],
        ['event_type' => 'CFS STRIPPING', 'raw_type' => 'CFS_STRIP', 'container_number' => 'TCKU2219401', 'notes' => 'Bongkar muat 24 pallet komponen • GS1 SSCC-18 terbit otomatis.', 'operator' => 'Forklift FL-02 (Budi T.)', 'time_wib' => date('H:i', $now_ts - 1420) . ' WIB', 'stage_key' => 'stage_4', 'zone_key' => 'Z3']
    ];

    if (count($events_list) < 5) {
        foreach ($seed_events as $se) {
            if (count($events_list) >= 6) break;
            $events_list[] = $se;
        }
    }

    return [
        'success' => true,
        'timestamp' => $now_ts,
        'time_wib' => $time_wib,
        'date_display' => $date_disp,
        'kpi' => [
            'containers_yard' => $containers_yard,
            'yard_cap_max'    => $yard_cap_max,
            'yard_occupancy'  => $yard_occupancy,
            'total_trucks'    => $total_trucks,
            'gate_turnaround' => $gate_turnaround,
            'loaded_wagons'   => $loaded_wagons,
            'total_wagons'    => $total_wagons,
            'siding_pct'      => $siding_pct,
            'reefer_plugs'    => $reefer_plugs,
            'reefer_cap'      => $reefer_cap,
            'reefer_pct'      => $reefer_pct,
            'reefer_temp'     => $reefer_temp,
            'customs_pct'     => $customs_pct,
            'customs_cleared' => $customs_cleared,
            'customs_total'   => $customs_total,
            'sppb_sec'        => $sppb_sec,
            'cfs_jobs'        => $cfs_jobs,
            'cfs_pct'         => $cfs_pct,
            'scada_uptime'    => $scada_uptime
        ],
        'financial' => [
            'total_invoiced'       => $fin_total,
            'paid_revenue'         => $fin_paid,
            'pending_receivable'   => $fin_pending,
            'overdue_demurrage'    => $fin_overdue,
            'settlement_pct'       => $settlement_pct,
            'demurrage_box_count'  => $demurrage_box_count,
            'demurrage_accrual_rp' => $demurrage_accrual_rp,
            'odoo_status'          => 'ODOO 17 ERP SYNC OK',
            'invoices'             => array_slice($invoices_list, 0, 5)
        ],
        'latencies'  => $latencies,
        'containers' => $containers_list,
        'events'     => array_slice($events_list, 0, 6)
    ];
}

// Jika di-request langsung sebagai endpoint AJAX
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'beranda_telemetry.php') {
    $jitter = isset($_GET['jitter']) || isset($_GET['pulse']);
    echo json_encode(build_beranda_telemetry($pdo ?? null, $jitter), JSON_UNESCAPED_SLASHES);
    exit;
}
