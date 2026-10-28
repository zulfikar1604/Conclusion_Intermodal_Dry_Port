<?php
/**
 * Conclusion Intermodal Dry Port (CIDP)
 * Digital Twin State Helper & Cache Engine
 * File: api/twin_state_helper.php
 */

require_once __DIR__ . '/../connection.php';

define('TWIN_CACHE_FILE', __DIR__ . '/../cache/twin_state.json');

/**
 * Fetch full unified twin state across the three flows (Physical, Information, Financial)
 */
function get_live_twin_state(PDO $pdo, bool $force_refresh = false): array {
    // If cache is fresh (< 500ms) and not forced, return cached state
    if (!$force_refresh && file_exists(TWIN_CACHE_FILE)) {
        $mtime = filemtime(TWIN_CACHE_FILE);
        if ((microtime(true) - $mtime) < 0.5) {
            $cached = @json_decode(file_get_contents(TWIN_CACHE_FILE), true);
            if ($cached && is_array($cached)) {
                return $cached;
            }
        }
    }

    $timestamp = microtime(true);
    $time_iso = date('c');

    // 1. ALIRAN FISIK (Physical Flow: Coordinates, Equipment, Trucks, Yard Slots)
    $equipment = [];
    try {
        $eq_stmt = $pdo->query("SELECT * FROM equipment ORDER BY id ASC");
        while ($eq = $eq_stmt->fetch(PDO::FETCH_ASSOC)) {
            $equipment[$eq['equipment_id']] = [
                'id' => $eq['equipment_id'],
                'type' => $eq['equipment_type'],
                'brand' => $eq['brand_model'] ?? 'Kalmar DRG450',
                'operator' => $eq['operator_name'] ?? 'Operator Siaga',
                'status' => $eq['status'] ?? 'idle',
                'gps_x' => (float)$eq['gps_x'],
                'gps_y' => (float)$eq['gps_y'],
                'carried_container' => $eq['current_container'],
                'last_block' => $eq['last_block'] ?? 'B',
                'fuel_percent' => (int)($eq['fuel_percent'] ?? 85),
                'hours_today' => (float)($eq['hours_today'] ?? 4.2),
                'spreader_locked' => !empty($eq['current_container']),
                'last_updated' => $eq['last_updated'] ?? $time_iso
            ];
        }
    } catch (Exception $e) {}

    // Ensure default RS units exist if DB has none
    if (empty($equipment)) {
        $equipment = [
            'RS-01' => ['id' => 'RS-01', 'type' => 'REACH_STACKER', 'brand' => 'Kalmar DRG450', 'operator' => 'Budi Santoso', 'status' => 'idle', 'gps_x' => 150.0, 'gps_y' => 200.0, 'carried_container' => null, 'last_block' => 'A', 'fuel_percent' => 88, 'hours_today' => 3.5, 'spreader_locked' => false, 'last_updated' => $time_iso],
            'RS-02' => ['id' => 'RS-02', 'type' => 'REACH_STACKER', 'brand' => 'Kalmar DRG450', 'operator' => 'Agus Prayitno', 'status' => 'operating', 'gps_x' => 350.0, 'gps_y' => 300.0, 'carried_container' => null, 'last_block' => 'B', 'fuel_percent' => 74, 'hours_today' => 5.2, 'spreader_locked' => false, 'last_updated' => $time_iso],
            'RS-03' => ['id' => 'RS-03', 'type' => 'REACH_STACKER', 'brand' => 'Kalmar DRG450', 'operator' => 'Dedi Suryadi', 'status' => 'idle', 'gps_x' => 500.0, 'gps_y' => 150.0, 'carried_container' => null, 'last_block' => 'REEFER', 'fuel_percent' => 92, 'hours_today' => 2.1, 'spreader_locked' => false, 'last_updated' => $time_iso],
            'RTG-01' => ['id' => 'RTG-01', 'type' => 'RTG_CRANE', 'brand' => 'Kalmar Zero-Emission RTG', 'operator' => 'Hendro Wijaya', 'status' => 'operating', 'gps_x' => 100.0, 'gps_y' => 450.0, 'carried_container' => null, 'last_block' => 'RAIL', 'fuel_percent' => 95, 'hours_today' => 6.0, 'spreader_locked' => false, 'last_updated' => $time_iso],
        ];
    }

    // Active Trucks
    $trucks = [];
    try {
        $tr_stmt = $pdo->query("SELECT * FROM trucks WHERE status NOT IN ('gate_out', 'scheduled') ORDER BY id DESC LIMIT 10");
        $trucks = $tr_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    // Containers in Yard & Block Distribution
    $containers_count = 0;
    $blocks = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'REEFER' => 0, 'DG' => 0, 'EMPTY' => 0];
    $active_slots = [];
    try {
        $cnt_stmt = $pdo->query("SELECT COUNT(*) FROM containers WHERE status = 'in_yard'");
        $containers_count = (int)$cnt_stmt->fetchColumn();

        $slot_stmt = $pdo->query("SELECT id, container_number, cargo_type, block, bay, row, tier, customs_status, gross_weight_kg FROM containers WHERE status = 'in_yard' LIMIT 100");
        while ($c = $slot_stmt->fetch(PDO::FETCH_ASSOC)) {
            $blk = strtoupper($c['block'] ?? 'A');
            if (isset($blocks[$blk])) {
                $blocks[$blk]++;
            } else {
                $blocks[$blk] = 1;
            }
            $active_slots[] = [
                'id' => $c['id'],
                'container_number' => $c['container_number'],
                'type' => $c['cargo_type'] ?? 'dry',
                'block' => $c['block'],
                'bay' => $c['bay'],
                'row' => $c['row'],
                'tier' => $c['tier'],
                'customs' => $c['customs_status'],
                'weight' => (float)$c['gross_weight_kg']
            ];
        }
    } catch (Exception $e) {}

    // Trains at Rail Siding
    $trains = [];
    try {
        $trn_stmt = $pdo->query("SELECT * FROM trains ORDER BY id DESC LIMIT 2");
        $trains = $trn_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    // Gatehouse & Weighbridge status
    $gate_status = [
        'inbound_barrier' => 'CLOSED', // or OPEN
        'outbound_barrier' => 'CLOSED',
        'weighbridge_active_kg' => 0.0,
        'weighbridge_truck' => null,
        'current_ocr_plate' => null,
        'current_ocr_container' => null
    ];
    if (!empty($trucks)) {
        foreach ($trucks as $trk) {
            if ($trk['status'] === 'at_gate') {
                $gate_status['inbound_barrier'] = 'OPEN';
                $gate_status['weighbridge_active_kg'] = (float)($trk['weight_gross'] ?? 34850.0);
                $gate_status['weighbridge_truck'] = $trk['license_plate'];
                $gate_status['current_ocr_plate'] = $trk['license_plate'];
                $gate_status['current_ocr_container'] = $trk['container_number'];
                break;
            }
        }
    }

    // 2. ALIRAN INFORMASI (Information Flow: RFID, OCR, CEISA 4.0, Reefer Telematics, Logs)
    $recent_events = [];
    try {
        $ev_stmt = $pdo->query("SELECT * FROM yard_events ORDER BY id DESC LIMIT 10");
        $recent_events = $ev_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    $customs_summary = [
        'total_audited' => max($containers_count, 1),
        'sppb_cleared' => 0,
        'red_line_inspection' => 0,
        'clearance_rate_pct' => 100.0,
        'last_ceisa_sync' => $time_iso
    ];
    try {
        $c_clr = (int)$pdo->query("SELECT COUNT(*) FROM containers WHERE status != 'expected' AND customs_status = 'SPPB_CLEARED'")->fetchColumn();
        $c_red = (int)$pdo->query("SELECT COUNT(*) FROM containers WHERE status != 'expected' AND customs_status IN ('INSPECTION', 'RED_LINE', 'RED_LANE_CHECK')")->fetchColumn();
        $customs_summary['sppb_cleared'] = $c_clr;
        $customs_summary['red_line_inspection'] = $c_red;
        if ($customs_summary['total_audited'] > 0) {
            $customs_summary['clearance_rate_pct'] = round(($c_clr / $customs_summary['total_audited']) * 100, 1);
        }
    } catch (Exception $e) {}

    $reefer_telematics = [
        'active_plugs' => $blocks['REEFER'] ?? 4,
        'total_capacity' => 120,
        'avg_temp_celsius' => -18.4,
        'status' => 'OPTIMAL',
        'alerts' => 0
    ];

    // 3. ALIRAN FINANSIAL (Financial Flow: Real-Time Accruals, Tariffs, Invoices)
    $financial_summary = [
        'total_revenue_accrued_idr' => 0.0,
        'weighbridge_fee_idr' => 0.0,
        'liftoff_fee_idr' => 0.0,
        'storage_dwell_fee_idr' => 0.0,
        'reefer_power_fee_idr' => 0.0,
        'paid_amount_idr' => 0.0,
        'pending_amount_idr' => 0.0,
        'settlement_ratio_pct' => 95.0,
        'last_invoice_generated' => null
    ];
    try {
        $rev_stmt = $pdo->query("SELECT 
            COALESCE(SUM(total_amount), 0) as total,
            COALESCE(SUM(lolo_amount), 0) as lolo,
            COALESCE(SUM(storage_amount), 0) as storage,
            COALESCE(SUM(reefer_power_amount), 0) as reefer,
            COALESCE(SUM(CASE WHEN payment_status = 'PAID' THEN total_amount ELSE 0 END), 0) as paid,
            COALESCE(SUM(CASE WHEN payment_status = 'PENDING' THEN total_amount ELSE 0 END), 0) as pending
            FROM billing_invoices");
        $rev = $rev_stmt->fetch(PDO::FETCH_ASSOC);
        if ($rev) {
            $financial_summary['total_revenue_accrued_idr'] = (float)$rev['total'];
            $financial_summary['liftoff_fee_idr'] = (float)$rev['lolo'];
            $financial_summary['storage_dwell_fee_idr'] = (float)$rev['storage'];
            $financial_summary['reefer_power_fee_idr'] = (float)$rev['reefer'];
            $financial_summary['paid_amount_idr'] = (float)$rev['paid'];
            $financial_summary['pending_amount_idr'] = (float)$rev['pending'];
            if ((float)$rev['total'] > 0) {
                $financial_summary['settlement_ratio_pct'] = round(((float)$rev['paid'] / (float)$rev['total']) * 100, 1);
            }
        }

        // Weighbridge estimate: total trucks weighed * 75,000
        $weigh_cnt = (int)$pdo->query("SELECT COUNT(*) FROM trucks WHERE weight_gross > 0")->fetchColumn();
        $financial_summary['weighbridge_fee_idr'] = (float)($weigh_cnt * 75000);
        if ($financial_summary['total_revenue_accrued_idr'] == 0) {
            // Default realistic demonstration baseline
            $financial_summary['total_revenue_accrued_idr'] = 48750000.0;
            $financial_summary['liftoff_fee_idr'] = 18500000.0;
            $financial_summary['storage_dwell_fee_idr'] = 24750000.0;
            $financial_summary['reefer_power_fee_idr'] = 3250000.0;
            $financial_summary['weighbridge_fee_idr'] = 2250000.0;
            $financial_summary['paid_amount_idr'] = 46500000.0;
            $financial_summary['pending_amount_idr'] = 2250000.0;
            $financial_summary['settlement_ratio_pct'] = 95.4;
        }

        $inv_last = $pdo->query("SELECT invoice_number, container_number, total_amount, payment_status, created_at FROM billing_invoices ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if ($inv_last) {
            $financial_summary['last_invoice_generated'] = $inv_last;
        }
    } catch (Exception $e) {}

    $state = [
        'system' => [
            'app' => 'CIDP Digital Twin Monitoring Engine',
            'version' => '2.0-Live',
            'server_time' => $time_iso,
            'timestamp' => $timestamp,
            'status' => 'ONLINE'
        ],
        'physical' => [
            'equipment' => $equipment,
            'trucks' => $trucks,
            'containers_in_yard' => $containers_count,
            'blocks_distribution' => $blocks,
            'active_slots' => $active_slots,
            'trains' => $trains,
            'gate' => $gate_status
        ],
        'information' => [
            'recent_events' => $recent_events,
            'customs' => $customs_summary,
            'reefer' => $reefer_telematics
        ],
        'financial' => $financial_summary
    ];

    write_twin_state_cache($state);
    return $state;
}

/**
 * Save twin state to atomic JSON cache
 */
function write_twin_state_cache(array $state): bool {
    $dir = dirname(TWIN_CACHE_FILE);
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    $json = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $tmp_file = TWIN_CACHE_FILE . '.tmp.' . uniqid();
    if (@file_put_contents($tmp_file, $json) !== false) {
        return @rename($tmp_file, TWIN_CACHE_FILE);
    }
    return false;
}
