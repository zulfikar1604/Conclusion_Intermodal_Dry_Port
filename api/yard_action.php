<?php
/**
 * Conclusion Intermodal Dry Port (CIDP)
 * Real-Time Container Yard (CY) Matrix SSoT Operations API
 * File: api/yard_action.php
 *
 * Actions (POST JSON / Form):
 *  - relocate_container : Relokasi VMT kontainer aktif di Yard (Blok-Bay-Row-Tier) + anti-bentrok
 *  - stack_container    : Stacking / putaway kontainer Gate/Rail (in_transit/on_rail) ke slot kosong CY
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

function yard_json_out(array $payload, int $code = 200): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    yard_json_out(['success' => false, 'message' => 'Gunakan metode POST untuk mutasi slot Container Yard.'], 405);
}

$blkCenterMap = [
    'A'      => [270.0, 250.0, 'RTG-02'],
    'B'      => [490.0, 250.0, 'RTG-03'],
    'C'      => [270.0, 470.0, 'RTG-04'],
    'D'      => [490.0, 470.0, 'RTG-05'],
    'E'      => [770.0, 470.0, null],
    'DG'     => [770.0, 470.0, null],
    'REEFER' => [770.0, 250.0, null],
    'EMPTY'  => [270.0, 545.0, 'RS-04'],
];

$updateEquipGps = static function (PDO $pdo, string $eqId, string $blk, int $bay, int $row) use ($blkCenterMap): void {
    $bcInfo = $blkCenterMap[$blk] ?? [350.0, 300.0, null];
    $relX = $bcInfo[0] + ((($bay - 1) % 5) - 2) * 20.0;
    $relY = $bcInfo[1] + ((($row - 1) % 4) - 1.5) * 16.0;
    $pdo->prepare("UPDATE equipment SET status = 'operating', current_container = NULL, last_block = ?, gps_x = ?, gps_y = ?, last_updated = NOW() WHERE equipment_id = ?")
        ->execute([$blk, $relX, $relY, $eqId]);
    if (!empty($bcInfo[2]) && $bcInfo[2] !== $eqId) {
        $pdo->prepare("UPDATE equipment SET status = 'operating', last_block = ?, gps_x = ?, gps_y = ?, last_updated = NOW() WHERE equipment_id = ?")
            ->execute([$blk, $relX, $bcInfo[1], $bcInfo[2]]);
    }
};

try {
    $ctr = strtoupper(trim((string)($in['container_number'] ?? '')));
    $toBlock = strtoupper(trim((string)($in['to_block'] ?? 'A')));
    $toBay   = max(1, (int)($in['to_bay'] ?? 1));
    $toRow   = max(1, (int)($in['to_row'] ?? 1));
    $toTier  = max(1, (int)($in['to_tier'] ?? 1));
    $eqId    = strtoupper(trim((string)($in['equipment_id'] ?? 'RS-01')));
    $opName  = trim((string)($in['operator_name'] ?? 'Juan Gamaliel'));

    if ($ctr === '') {
        yard_json_out(['success' => false, 'message' => 'Nomor kontainer (ISO 6346) wajib dipilih.'], 400);
    }

    $q = $pdo->prepare("SELECT id, container_number, status, block, bay, `row`, tier, customs_status, cargo_type, size_type, gross_weight_kg FROM containers WHERE container_number = ? LIMIT 1");
    $q->execute([$ctr]);
    $cur = $q->fetch(PDO::FETCH_ASSOC);
    if (!$cur) {
        yard_json_out(['success' => false, 'message' => "Kontainer {$ctr} tidak ditemukan di basis data."], 404);
    }

    if ($action === 'recommend_slot') {
        $prefBlk = trim((string)($in['preferred_block'] ?? ''));
        $rec = cidp_recommend_yard_slot($pdo, $cur, $prefBlk !== '' ? $prefBlk : null);
        yard_json_out([
            'success' => true,
            'recommendation' => $rec
        ]);
    } elseif ($action === 'relocate_container') {
        if ($cur['status'] !== 'in_yard') {
            yard_json_out([
                'success' => false,
                'message' => "Kontainer {$ctr} berstatus '{$cur['status']}' (bukan di dalam Yard); gunakan aksi Stacking untuk penempatan awal."
            ], 409);
        }

        $val = yard_validate_slot($pdo, $toBlock, $toBay, $toRow, $toTier, $ctr);
        if (!$val['ok']) {
            yard_json_out(['success' => false, 'message' => $val['message']], 409);
        }

        $fromBlock = (string)($cur['block'] ?: '-');
        $fromBay   = (string)($cur['bay'] ?: '-');
        $fromRow   = (string)($cur['row'] ?: '-');
        $fromTier  = (string)($cur['tier'] ?: '-');
        $fromStr   = "{$fromBlock}-B" . yard_format_coord($fromBay) . "-R" . yard_format_coord($fromRow) . "-T" . yard_format_coord($fromTier);

        $pdo->prepare("UPDATE containers SET block = ?, bay = ?, `row` = ?, tier = ?, status = 'in_yard' WHERE id = ?")
            ->execute([$val['block'], $val['bay'], $val['row'], $val['tier'], $cur['id']]);

        $updateEquipGps($pdo, $eqId, $val['block'], $toBay, $toRow);
        $relocFee = 200000.0;
        $invNo = accrue_billing($pdo, $ctr, "Relokasi Yard ({$fromBlock} -> {$val['block']})", 'lolo_amount', $relocFee);

        $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, from_block, from_bay, from_row, from_tier, to_block, to_bay, to_row, to_tier, operator_name, billable_amount, notes, created_at) VALUES ('RELOCATION', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())")
            ->execute([$ctr, $eqId, $fromBlock, $fromBay, $fromRow, $fromTier, $val['block'], $val['bay'], $val['row'], $val['tier'], $opName, $relocFee, "Relokasi VMT oleh {$eqId} ({$opName}): {$fromStr} -> {$val['code']} (Faktur {$invNo})"]);

        $state = get_live_twin_state($pdo, true);
        yard_json_out([
            'success' => true,
            'message' => "Kontainer {$ctr} berhasil direlokasi oleh {$eqId} dari {$fromStr} ke {$val['code']}.",
            'from' => $fromStr,
            'to' => $val['code'],
            'gravity_warning' => $val['gravity_warning'],
            'state' => $state
        ]);
    } elseif ($action === 'stack_container') {
        $val = yard_validate_slot($pdo, $toBlock, $toBay, $toRow, $toTier, $ctr);
        if (!$val['ok']) {
            yard_json_out(['success' => false, 'message' => $val['message']], 409);
        }

        $fromBlock = (string)($cur['block'] ?: ($cur['status'] === 'on_rail' ? 'RAIL' : 'GATE'));
        $fromBay   = $cur['bay'] ?: null;
        $fromRow   = $cur['row'] ?: null;
        $fromTier  = $cur['tier'] ?: null;

        $pdo->prepare("UPDATE containers SET block = ?, bay = ?, `row` = ?, tier = ?, status = 'in_yard', gate_in_time = COALESCE(gate_in_time, NOW()) WHERE id = ?")
            ->execute([$val['block'], $val['bay'], $val['row'], $val['tier'], $cur['id']]);

        $updateEquipGps($pdo, $eqId, $val['block'], $toBay, $toRow);
        $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, from_block, from_bay, from_row, from_tier, to_block, to_bay, to_row, to_tier, operator_name, billable_amount, notes, created_at) VALUES ('STACKING', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, NOW())")
            ->execute([$ctr, $eqId, $fromBlock, $fromBay, $fromRow, $fromTier, $val['block'], $val['bay'], $val['row'], $val['tier'], $opName, "Stacking real-time oleh {$eqId} ({$opName}) dari {$fromBlock} ke {$val['code']}"]);

        $state = get_live_twin_state($pdo, true);
        yard_json_out([
            'success' => true,
            'message' => "Kontainer {$ctr} berhasil ditumpuk oleh {$eqId} di slot {$val['code']} (SSoT Matriks & 3D Twin tersinkron).",
            'slot' => $val['code'],
            'gravity_warning' => $val['gravity_warning'],
            'state' => $state
        ]);
    } else {
        yard_json_out(['success' => false, 'message' => 'Aksi Yard tidak dikenali.'], 400);
    }
} catch (Throwable $e) {
    yard_json_out(['success' => false, 'message' => $e->getMessage()], 500);
}
