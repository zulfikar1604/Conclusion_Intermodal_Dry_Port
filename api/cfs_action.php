<?php
/**
 * Conclusion Intermodal Dry Port (CIDP)
 * Real-Time CFS Warehouse Rack SSoT Operations API
 * File: api/cfs_action.php
 *
 * Actions (POST JSON / Form):
 *  - dispatch_pallet : Keluarkan palet dari rak gudang & kosongkan slot master_lokasi
 *  - relocate_pallet : Pindahkan palet ke slot rak kosong lain (anti-bentrok)
 *  - quick_putaway   : Registrasi + timbang palet baru langsung ke slot rak terpilih
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/preadvice_helper.php';
require_once __DIR__ . '/twin_state_helper.php';

global $pdo;
ensure_integration_schema($pdo);

$raw = file_get_contents('php://input');
$body = $raw ? (@json_decode($raw, true) ?: []) : [];
$in = array_merge($_GET, $_POST, is_array($body) ? $body : []);
$action = trim((string)($in['action'] ?? ''));

function cfs_json_out(array $payload, int $code = 200): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    cfs_json_out(['success' => false, 'message' => 'Gunakan metode POST untuk mutasi lokasi rak CFS.'], 405);
}

$aisleCoords = [
    'AISLE-A' => [75.0, 232.0], 'AISLE-B' => [115.0, 232.0],
    'AISLE-C' => [75.0, 308.0], 'AISLE-D' => [115.0, 308.0],
    'AISLE-E' => [75.0, 384.0], 'AISLE-F' => [115.0, 384.0]
];

try {
    if ($action === 'dispatch_pallet') {
        $sscc = preg_replace('/\D/', '', (string)($in['pallet_sscc'] ?? ''));
        if ($sscc === '') {
            cfs_json_out(['success' => false, 'message' => 'Nomor barcode SSCC-18 wajib disertakan.'], 400);
        }

        $q = $pdo->prepare("SELECT id, container_origin, commodity_name, rack_location, customs_status, status FROM cfs_pallets WHERE pallet_sscc = ? LIMIT 1");
        $q->execute([$sscc]);
        $pal = $q->fetch(PDO::FETCH_ASSOC);
        if (!$pal) {
            cfs_json_out(['success' => false, 'message' => "Palet SSCC {$sscc} tidak ditemukan."], 404);
        }
        if ($pal['status'] === 'DISPATCHED') {
            cfs_json_out(['success' => false, 'message' => "Palet SSCC {$sscc} sudah pernah dikeluarkan sebelumnya."], 409);
        }
        if (in_array(strtoupper((string)$pal['customs_status']), ['HOLD', 'RED_LANE', 'RED_LANE_CHECK'], true)) {
            cfs_json_out([
                'success' => false,
                'message' => "INTERLOCK BEA CUKAI: Palet SSCC {$sscc} berstatus {$pal['customs_status']}; dispatch keluar gudang ditahan hingga SPPB CLEARED!"
            ], 403);
        }

        $pdo->prepare("UPDATE cfs_pallets SET status = 'DISPATCHED' WHERE id = ?")->execute([$pal['id']]);
        cfs_sync_master_lokasi($pdo);

        $shortPal = 'OUT-' . substr($sscc, -4);
        $pdo->prepare("UPDATE equipment SET status = 'operating', current_container = ?, last_block = 'CFS', gps_x = 148.0, gps_y = 392.0, last_updated = NOW() WHERE equipment_id = 'FL-04'")
            ->execute([$shortPal]);
        $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, notes, billable_amount, created_at) VALUES ('CFS_PALLET_OUT', ?, 'FL-04', ?, 0, NOW())")
            ->execute([$pal['container_origin'] ?: '-', "Dispatch palet SSCC {$sscc} keluar dari rak {$pal['rack_location']} via Forklift FL-04"]);

        $state = get_live_twin_state($pdo, true);
        cfs_json_out([
            'success' => true,
            'message' => "Palet SSCC {$sscc} dikeluarkan dari {$pal['rack_location']}; slot otomatis kosong (Real-Time SSoT).",
            'freed_rack' => $pal['rack_location'],
            'state' => $state
        ]);
    } elseif ($action === 'relocate_pallet') {
        $sscc = preg_replace('/\D/', '', (string)($in['pallet_sscc'] ?? ''));
        $targetRaw = trim((string)($in['target_rack'] ?? ''));
        if ($sscc === '' || $targetRaw === '') {
            cfs_json_out(['success' => false, 'message' => 'SSCC palet dan lokasi rak tujuan wajib diisi.'], 400);
        }

        $q = $pdo->prepare("SELECT id, container_origin, rack_location, status FROM cfs_pallets WHERE pallet_sscc = ? LIMIT 1");
        $q->execute([$sscc]);
        $pal = $q->fetch(PDO::FETCH_ASSOC);
        if (!$pal) {
            cfs_json_out(['success' => false, 'message' => "Palet SSCC {$sscc} tidak ditemukan."], 404);
        }
        if ($pal['status'] === 'DISPATCHED') {
            cfs_json_out(['success' => false, 'message' => "Palet {$sscc} sudah DISPATCHED dan tidak berada di rak."], 409);
        }

        $alloc = cfs_allocate_or_validate_rack($pdo, $targetRaw, false);
        if (!$alloc['ok']) {
            cfs_json_out(['success' => false, 'message' => $alloc['message']], 409);
        }

        $newRack = $alloc['code'];
        $oldRack = $pal['rack_location'] ?: '-';
        $pdo->prepare("UPDATE cfs_pallets SET rack_location = ? WHERE id = ?")->execute([$newRack, $pal['id']]);
        cfs_sync_master_lokasi($pdo);

        $rkXY = $aisleCoords[substr($newRack, 0, 7)] ?? [92.0, 308.0];
        $pdo->prepare("UPDATE equipment SET status = 'carrying', current_container = ?, last_block = 'CFS', gps_x = ?, gps_y = ?, last_updated = NOW() WHERE equipment_id = 'FL-04'")
            ->execute(['SSCC-' . substr($sscc, -4), $rkXY[0], $rkXY[1]]);
        $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, notes, billable_amount, created_at) VALUES ('CFS_PALLET_RELOC', ?, 'FL-04', ?, 0, NOW())")
            ->execute([$pal['container_origin'] ?: '-', "Relokasi rak palet SSCC {$sscc} oleh FL-04: {$oldRack} -> {$newRack}"]);

        $state = get_live_twin_state($pdo, true);
        cfs_json_out([
            'success' => true,
            'message' => "Palet SSCC {$sscc} dipindahkan dari {$oldRack} ke {$newRack}.",
            'old_rack' => $oldRack,
            'new_rack' => $newRack,
            'state' => $state
        ]);
    } elseif ($action === 'quick_putaway') {
        $ctr_orig  = strtoupper(trim((string)($in['container_origin'] ?? 'ONEU6612988')));
        $commodity = trim((string)($in['commodity_name'] ?? 'Kargo LCL Konsolidasi'));
        $consignee = trim((string)($in['consignee'] ?? 'PT Penerima Logistik'));
        $category  = trim((string)($in['category'] ?? 'general'));
        $cartons   = max(1, (int)($in['carton_count'] ?? 24));
        $weight    = max(1.0, (float)($in['gross_weight_kg'] ?? 450.0));
        $cbm       = max(0.05, (float)($in['cbm'] ?? 1.25));
        $reqRack   = trim((string)($in['rack_location'] ?? 'AISLE-A-01-L1'));
        $autoFb    = isset($in['auto_fallback']) ? (bool)$in['auto_fallback'] : false;

        $alloc = cfs_allocate_or_validate_rack($pdo, $reqRack, $autoFb);
        if (!$alloc['ok']) {
            cfs_json_out(['success' => false, 'message' => $alloc['message']], 409);
        }
        $rackCode = $alloc['code'];

        $base17 = '0018991200' . str_pad((string)mt_rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
        $sscc = $base17 . gs1_check_digit($base17);

        $jobId = null;
        $jbStmt = $pdo->prepare("SELECT id FROM cfs_jobs WHERE container_number = ? AND status IN ('IN_PROGRESS','PENDING') ORDER BY id DESC LIMIT 1");
        $jbStmt->execute([$ctr_orig]);
        if ($jid = $jbStmt->fetchColumn()) {
            $jobId = (int)$jid;
        }

        $pdo->prepare("INSERT INTO cfs_pallets (job_id, pallet_sscc, container_origin, commodity_name, consignee, category, carton_count, gross_weight_kg, cbm, rack_location, customs_doc, customs_status, status, entry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'SPPB-CEISA', 'CLEARED', 'IN_RACK', NOW())")
            ->execute([$jobId, $sscc, $ctr_orig, $commodity, $consignee, $category, $cartons, $weight, $cbm, $rackCode]);

        if ($jobId) {
            $pdo->prepare("UPDATE cfs_jobs SET total_packages = COALESCE(total_packages,0) + ?, total_weight_kg = COALESCE(total_weight_kg,0) + ?, total_cbm = COALESCE(total_cbm,0) + ? WHERE id = ?")
                ->execute([$cartons, $weight, $cbm, $jobId]);
        }

        accrue_billing($pdo, $ctr_orig, "CFS LCL Putaway ({$sscc})", 'cfs_amount', 35000);
        cfs_sync_master_lokasi($pdo);

        $rkXY = $aisleCoords[substr($rackCode, 0, 7)] ?? [92.0, 308.0];
        $shortPal = 'SSCC-' . substr($sscc, -4);
        $pdo->prepare("UPDATE equipment SET status = 'operating', current_container = ?, last_block = 'CFS', gps_x = 136.0, gps_y = 236.0, last_updated = NOW() WHERE equipment_id = 'FL-01'")
            ->execute([$shortPal]);
        $pdo->prepare("UPDATE equipment SET status = 'carrying', current_container = ?, last_block = 'CFS', gps_x = ?, gps_y = ?, last_updated = NOW() WHERE equipment_id = 'FL-04'")
            ->execute([$shortPal, $rkXY[0], $rkXY[1]]);
        $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, notes, billable_amount, created_at) VALUES ('CFS_PALLET_IN', ?, 'FL-04', ?, 35000, NOW())")
            ->execute([$ctr_orig, "Putaway palet SSCC {$sscc} ({$weight} kg) ke {$rackCode}"]);

        $state = get_live_twin_state($pdo, true);
        cfs_json_out([
            'success' => true,
            'pallet_sscc' => $sscc,
            'rack_location' => $rackCode,
            'message' => "Palet {$sscc} berhasil ditempatkan di {$rackCode} (master_lokasi & 3D Twin tersinkron).",
            'state' => $state
        ]);
    } else {
        cfs_json_out(['success' => false, 'message' => 'Aksi tidak dikenali.'], 400);
    }
} catch (Throwable $e) {
    cfs_json_out(['success' => false, 'message' => $e->getMessage()], 500);
}
