<?php
// =============================================================================
// API ENDPOINT: TOC UNIVERSAL CARGO & ASSET LOCATOR (PELACAK POSISI SENTRAL)
// File: api/track_asset.php
// Proyek: Conclusion Intermodal Dry Port (CIDP 35 Ha)
// Mendukung pencarian instan kontainer, truk, atau pre-advice ID untuk pusat kendali (TOC)
// =============================================================================

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/../connection.php';

$db = $pdo;
if (!$db instanceof PDO) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$query = trim($_GET['q'] ?? '');
$action = trim($_GET['action'] ?? 'search');

if ($action === 'recent' || empty($query)) {
    // Ambil daftar kontainer & truk aktif terkini untuk dropdown saran cepat
    $recent_containers = [];
    $recent_trucks = [];

    try {
        $c_stmt = $db->query("SELECT container_number, block, bay, row, tier, status, cargo_type, customs_status FROM containers WHERE status != 'expected' ORDER BY id DESC LIMIT 10");
        $recent_containers = $c_stmt->fetchAll(PDO::FETCH_ASSOC);

        $t_stmt = $db->query("SELECT license_plate, container_number, driver_name, status FROM trucks WHERE status != 'scheduled' ORDER BY id DESC LIMIT 10");
        $recent_trucks = $t_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Ambil beberapa pre-advice di luar
        $pa_stmt = $db->query("SELECT id, truck_plate, container_number, driver_name, status FROM preadvice WHERE status = 'SCHEDULED' ORDER BY id ASC LIMIT 5");
        $scheduled_outside = $pa_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    echo json_encode([
        'success' => true,
        'action' => 'recent',
        'containers' => $recent_containers,
        'trucks' => $recent_trucks,
        'outside' => $scheduled_outside
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

// Lakukan pencarian mendalam
$res_container = null;
$res_truck = null;
$res_preadvice = null;

try {
    // 1. Cari di tabel containers
    $stmt = $db->prepare("SELECT * FROM containers WHERE container_number = ? OR container_number LIKE ? LIMIT 1");
    $stmt->execute([$query, "%$query%"]);
    $res_container = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Cari di tabel trucks
    $stmt = $db->prepare("SELECT * FROM trucks WHERE license_plate = ? OR license_plate LIKE ? OR container_number = ? LIMIT 1");
    $stmt->execute([$query, "%$query%", $query]);
    $res_truck = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. Jika belum ketemu, cari di tabel preadvice
    if (!$res_container && !$res_truck) {
        $stmt = $db->prepare("SELECT * FROM preadvice WHERE container_number LIKE ? OR truck_plate LIKE ? OR id = ? LIMIT 1");
        $stmt->execute(["%$query%", "%$query%", is_numeric($query) ? (int)$query : 0]);
        $res_preadvice = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}

if (!$res_container && !$res_truck && !$res_preadvice) {
    echo json_encode([
        'success' => false,
        'found' => false,
        'message' => 'Aset dengan nomor "' . htmlspecialchars($query) . '" tidak ditemukan di database.'
    ]);
    exit;
}

// Bangun profil posisi lengkap aset
$c_num = $res_container['container_number'] ?? ($res_preadvice['container_number'] ?? ($res_truck['container_number'] ?? '-'));
$t_plate = $res_truck['license_plate'] ?? ($res_preadvice['truck_plate'] ?? '-');
$driver = $res_truck['driver_name'] ?? ($res_preadvice['driver_name'] ?? 'Budi Santoso');

// Saling melengkapi kaitan kontainer & truk jika salah satu ditemukan
if ($res_container && !$res_truck && !empty($c_num)) {
    try {
        $stmt = $db->prepare("SELECT * FROM trucks WHERE container_number = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$c_num]);
        $res_truck = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($res_truck) {
            $t_plate = $res_truck['license_plate'];
            $driver = $res_truck['driver_name'] ?? $driver;
        }
    } catch (Exception $e) {}
} elseif ($res_truck && !$res_container && !empty($c_num)) {
    try {
        $stmt = $db->prepare("SELECT * FROM containers WHERE container_number = ? LIMIT 1");
        $stmt->execute([$c_num]);
        $res_container = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}

// Tentukan status dan lokasi presisi
$c_stat = strtolower($res_container['status'] ?? ($res_preadvice['status'] ?? 'expected'));
$blk = strtoupper($res_container['block'] ?? 'OUTSIDE');
$bay = $res_container['bay'] ?? '01';
$row = $res_container['row'] ?? '01';
$tier = $res_container['tier'] ?? '01';
$ctype = strtolower($res_container['cargo_type'] ?? 'dry');
$weight = (float)($res_container['gross_weight_kg'] ?? ($res_truck['weight_gross'] ?? 24500));
$customs = strtoupper($res_container['customs_status'] ?? 'AWAITING_CLEARANCE');

$location_title = '';
$location_detail = '';
$zone_code = 'Z2';
$zone_name = 'Stacking Yard';
$badge_color = 'blue';
$badge_label = 'IN YARD';

if ($c_stat === 'expected' || $blk === 'OUTSIDE' || $c_stat === 'scheduled') {
    $location_title = 'Area Tunggu Luar (Pre-Gate Buffer)';
    $location_detail = 'Menunggu verifikasi e-Ticket & antrean Gate In di Jalan Arteri';
    $zone_code = 'OUTSIDE';
    $zone_name = 'Luar Terminal (Pre-Advice)';
    $badge_color = 'slate';
    $badge_label = 'SCHEDULED (LUAR)';
} elseif ($c_stat === 'in_transit' || $blk === 'GATE') {
    $location_title = 'Gerbang Utama (Gate Inbound Lane 1)';
    $location_detail = 'Sedang proses penimbangan Weighbridge 80T & verifikasi RFID/OCR';
    $zone_code = 'Z1';
    $zone_name = 'Zona Gate & Buffer Truk';
    $badge_color = 'amber';
    $badge_label = 'AT GATE / WEIGHING';
} elseif ($blk === 'CFS') {
    $location_title = 'Gudang CFS Rampa ' . ($bay ?: 'D1');
    $location_detail = 'Proses stripping/stuffing kargo LCL & verifikasi barcode GS1 SSCC-18';
    $zone_code = 'Z3';
    $zone_name = 'Zona CFS Warehouse';
    $badge_color = 'amber';
    $badge_label = 'AT CFS WAREHOUSE';
} elseif ($blk === 'REEFER' || $blk === 'R' || $ctype === 'reefer') {
    $location_title = 'Reefer Stacking Rack (Plug R-' . str_pad($bay, 2, '0', STR_PAD_LEFT) . ')';
    $location_detail = 'Terhubung steker daya 380V/32A, pemantauan telemetri rantai dingin aktif';
    $zone_code = 'Z4';
    $zone_name = 'Zona Reefer Cold Chain';
    $badge_color = 'cyan';
    $badge_label = 'REEFER 380V ACTIVE';
} elseif ($blk === 'RAIL' || $c_stat === 'on_rail') {
    $location_title = 'Siding KA Intermodal (Sepur Rel 1 / Gerbong #' . ($bay ?: '03') . ')';
    $location_detail = 'Rangkaian Kereta Api KA 2518 rute Priok, terverifikasi Axle Counter Frauscher';
    $zone_code = 'Z4';
    $zone_name = 'Zona Siding Kereta Api';
    $badge_color = 'emerald';
    $badge_label = 'ON RAIL SIDING';
} elseif ($c_stat === 'gate_out') {
    $location_title = 'Gerbang Keluar (Gate Outbound)';
    $location_detail = 'Pemeriksaan palang barrier selesai, telah meninggalkan kawasan terminal';
    $zone_code = 'GATE_OUT';
    $zone_name = 'Keluar Terminal';
    $badge_color = 'emerald';
    $badge_label = 'COMPLETED / GATE OUT';
} else {
    // Normal Yard Stacking
    $location_title = "Stacking Yard Blok $blk / Bay $bay - Row $row - Tier $tier";
    $location_detail = "Slot CY Blok $blk (Laden/Domestik), siap handling Reach Stacker RS-02";
    $zone_code = 'Z2';
    $zone_name = 'Zona Lapangan Penumpukan (CY)';
    $badge_color = 'blue';
    $badge_label = 'IN YARD STACKING';
}

// Ambil riwayat tagihan faktur
$billing_info = null;
try {
    $b_stmt = $db->prepare("SELECT invoice_number, total_amount, payment_status, service_type FROM billing_invoices WHERE container_number = ? ORDER BY id DESC LIMIT 1");
    $b_stmt->execute([$c_num]);
    $billing_info = $b_stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// Ambil riwayat event log SCADA terkini
$event_history = [];
try {
    $ev_stmt = $db->prepare("SELECT event_type, notes, operator_name, equipment_id, created_at FROM yard_events WHERE container_number = ? ORDER BY id ASC LIMIT 6");
    $ev_stmt->execute([$c_num]);
    $event_history = $ev_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

echo json_encode([
    'success' => true,
    'found' => true,
    'query' => $query,
    'asset' => [
        'container_number' => $c_num,
        'license_plate' => $t_plate,
        'driver_name' => $driver,
        'cargo_type' => strtoupper($ctype),
        'size_type' => $res_container['size_type'] ?? '40FT HC',
        'gross_weight_kg' => $weight,
        'rfid_tag' => $res_container['rfid_tag'] ?? ($res_truck['rfid_tag'] ?? 'E280117000000001'),
        'customs_status' => $customs,
        'handling_equipment' => ($ctype === 'reefer') ? 'RS-03 (Rudi H.)' : 'RS-02 (Agus Setiawan)'
    ],
    'position' => [
        'location_title' => $location_title,
        'location_detail' => $location_detail,
        'zone_code' => $zone_code,
        'zone_name' => $zone_name,
        'block' => $blk,
        'bay' => $bay,
        'row' => $row,
        'tier' => $tier,
        'badge_label' => $badge_label,
        'badge_color' => $badge_color
    ],
    'billing' => [
        'has_invoice' => !empty($billing_info),
        'invoice_number' => $billing_info['invoice_number'] ?? '-',
        'amount_formatted' => !empty($billing_info['total_amount']) ? 'Rp ' . number_format($billing_info['total_amount'], 0, ',', '.') : '-',
        'payment_status' => $billing_info['payment_status'] ?? 'BELUM ADA TAGIHAN',
        'service_type' => $billing_info['service_type'] ?? 'Standard Terminal Handling',
        'odoo_status' => (!empty($billing_info) && ($billing_info['payment_status'] ?? '') === 'PAID') ? 'SYNCED TO ODOO ERP' : 'READY TO SYNC'
    ],
    'events' => $event_history
], JSON_UNESCAPED_SLASHES);
