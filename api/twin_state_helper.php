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

    // 1. ALIRAN FISIK (Physical Flow: Coordinates, Equipment, Trucks, Yard Slots & CFS Interior)
    $defaultCoords = [
        'RS-01'     => [270.0, 175.0, 'A'],
        'RS-02'     => [490.0, 175.0, 'B'],
        'RS-03'     => [770.0, 175.0, 'REEFER'],
        'RS-04'     => [270.0, 545.0, 'C'],
        'RTG-01'    => [600.0, 625.0, 'RAIL'],
        'RTG-02'    => [270.0, 250.0, 'A'],
        'RTG-03'    => [490.0, 250.0, 'B'],
        'RTG-04'    => [270.0, 470.0, 'C'],
        'RTG-05'    => [490.0, 470.0, 'D'],
        'FL-01'     => [140.0, 240.0, 'CFS'],
        'FL-02'     => [835.0, 470.0, 'E'],
        'FL-03'     => [148.0, 310.0, 'CFS'],
        'FL-04'     => [90.0,  350.0, 'CFS'],
        'CDP-TT-01' => [360.0, 585.0, 'RAIL'],
        'CDP-TT-02' => [635.0, 470.0, 'E']
    ];
    $equipment = [];
    try {
        $eq_stmt = $pdo->query("SELECT * FROM equipment ORDER BY id ASC");
        while ($eq = $eq_stmt->fetch(PDO::FETCH_ASSOC)) {
            $eqId = $eq['equipment_id'];
            $def = $defaultCoords[$eqId] ?? [350.0, 300.0, 'B'];
            $gx = (float)$eq['gps_x'];
            $gy = (float)$eq['gps_y'];
            if ($gx <= 0 && $gy <= 0) {
                $gx = $def[0];
                $gy = $def[1];
            }
            $equipment[$eqId] = [
                'id' => $eqId,
                'type' => $eq['equipment_type'],
                'brand' => $eq['brand_model'] ?? 'Kalmar DRG450',
                'operator' => $eq['operator_name'] ?? 'Operator Siaga',
                'status' => $eq['status'] ?? 'idle',
                'gps_x' => $gx,
                'gps_y' => $gy,
                'carried_container' => $eq['current_container'],
                'last_block' => $eq['last_block'] ?? $def[2],
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
            'RS-01' => ['id' => 'RS-01', 'type' => 'REACH_STACKER', 'brand' => 'Kalmar DRG450', 'operator' => 'Budi Santoso', 'status' => 'idle', 'gps_x' => 270.0, 'gps_y' => 175.0, 'carried_container' => null, 'last_block' => 'A', 'fuel_percent' => 88, 'hours_today' => 3.5, 'spreader_locked' => false, 'last_updated' => $time_iso],
            'RS-02' => ['id' => 'RS-02', 'type' => 'REACH_STACKER', 'brand' => 'Kalmar DRG450', 'operator' => 'Agus Prayitno', 'status' => 'idle', 'gps_x' => 490.0, 'gps_y' => 175.0, 'carried_container' => null, 'last_block' => 'B', 'fuel_percent' => 74, 'hours_today' => 5.2, 'spreader_locked' => false, 'last_updated' => $time_iso],
            'RS-03' => ['id' => 'RS-03', 'type' => 'REACH_STACKER', 'brand' => 'Kalmar DRG450', 'operator' => 'Dedi Suryadi', 'status' => 'idle', 'gps_x' => 770.0, 'gps_y' => 175.0, 'carried_container' => null, 'last_block' => 'REEFER', 'fuel_percent' => 92, 'hours_today' => 2.1, 'spreader_locked' => false, 'last_updated' => $time_iso],
            'RTG-01' => ['id' => 'RTG-01', 'type' => 'RTG_CRANE', 'brand' => 'Kalmar Zero-Emission RTG', 'operator' => 'Hendro Wijaya', 'status' => 'idle', 'gps_x' => 600.0, 'gps_y' => 625.0, 'carried_container' => null, 'last_block' => 'RAIL', 'fuel_percent' => 95, 'hours_today' => 6.0, 'spreader_locked' => false, 'last_updated' => $time_iso],
        ];
    }

    // Active Trucks
    $trucks = [];
    try {
        $tr_stmt = $pdo->query("SELECT * FROM trucks WHERE status NOT IN ('gate_out', 'scheduled') ORDER BY id DESC LIMIT 10");
        $trucks = $tr_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    // Map per-container billing status & latest reefer telemetry
    $ctr_billing_map = [];
    try {
        $b_stmt = $pdo->query("SELECT container_number, CASE WHEN SUM(CASE WHEN payment_status <> 'PAID' THEN 1 ELSE 0 END) > 0 THEN 'PENDING' ELSE 'PAID' END AS pay_stat FROM billing_invoices GROUP BY container_number");
        while ($br = $b_stmt->fetch(PDO::FETCH_ASSOC)) {
            $ctr_billing_map[$br['container_number']] = $br['pay_stat'];
        }
    } catch (Exception $e) {}

    $reefer_latest_map = [];
    $reefer_units = [];
    $reefer_alarms = 0;
    $reefer_temp_sum = 0.0;
    $reefer_temp_cnt = 0;
    try {
        $rf_stmt = $pdo->query("SELECT r1.* FROM reefer_telemetry r1 INNER JOIN (SELECT container_number, MAX(id) AS max_id FROM reefer_telemetry GROUP BY container_number) r2 ON r1.id = r2.max_id ORDER BY r1.id DESC");
        while ($rr = $rf_stmt->fetch(PDO::FETCH_ASSOC)) {
            $reefer_latest_map[$rr['container_number']] = $rr;
            $reefer_units[] = $rr;
            if ($rr['status'] === 'ALARM') $reefer_alarms++;
            if ($rr['supply_temp_c'] !== null) {
                $reefer_temp_sum += (float)$rr['supply_temp_c'];
                $reefer_temp_cnt++;
            }
        }
    } catch (Exception $e) {}

    // Containers in Yard, In Transit, On Rail & Block Distribution
    $containers_count = 0;
    $expected_count = 0;
    $blocks = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'REEFER' => 0, 'DG' => 0, 'EMPTY' => 0];
    $active_slots = [];
    $in_transit_containers = [];
    $on_rail_containers = [];
    try {
        $containers_count = (int)$pdo->query("SELECT COUNT(*) FROM containers WHERE status = 'in_yard'")->fetchColumn();
        $expected_count = (int)$pdo->query("SELECT COUNT(*) FROM containers WHERE status = 'expected'")->fetchColumn();

        $slot_stmt = $pdo->query("SELECT id, container_number, size_type, cargo_type, owner_company, block, bay, `row`, tier, customs_status, gross_weight_kg, status, gate_in_time FROM containers WHERE status IN ('in_yard','in_transit','on_rail') ORDER BY id ASC LIMIT 200");
        while ($c = $slot_stmt->fetch(PDO::FETCH_ASSOC)) {
            $cn = $c['container_number'];
            $bStat = $ctr_billing_map[$cn] ?? 'NO_INVOICE';
            $rfInfo = $reefer_latest_map[$cn] ?? null;
            $cStat = $c['customs_status'] ?? 'PENDING';
            $isHold = in_array($cStat, ['RED_LANE_CHECK', 'INSPECTION', 'RED_LINE'], true) || ($rfInfo && $rfInfo['status'] === 'ALARM');
            $isReadyOut = ($cStat === 'SPPB_CLEARED' && in_array($bStat, ['PAID', 'NO_INVOICE'], true));
            $roleFlow = $isHold ? 'hold' : ($c['status'] === 'in_transit' ? 'unload_in' : ($isReadyOut ? 'load_out' : 'stored'));

            $item = [
                'id' => $c['id'],
                'container_number' => $cn,
                'size_type' => $c['size_type'] ?? '40FT HIGH CUBE',
                'type' => $c['cargo_type'] ?? 'dry',
                'owner' => $c['owner_company'] ?? 'Principal',
                'block' => $c['block'],
                'bay' => $c['bay'],
                'row' => $c['row'],
                'tier' => $c['tier'],
                'customs' => $cStat,
                'billing' => $bStat,
                'weight' => (float)$c['gross_weight_kg'],
                'status' => $c['status'],
                'role_flow' => $roleFlow,
                'reefer' => $rfInfo ? [
                    'socket' => $rfInfo['socket_id'],
                    'set_c' => (float)$rfInfo['set_temp_c'],
                    'supply_c' => (float)$rfInfo['supply_temp_c'],
                    'power_kw' => (float)$rfInfo['power_kw'],
                    'alarm' => ($rfInfo['status'] === 'ALARM')
                ] : null
            ];

            if ($c['status'] === 'in_yard') {
                $blk = strtoupper($c['block'] ?? 'A');
                $blocks[$blk] = ($blocks[$blk] ?? 0) + 1;
                $active_slots[] = $item;
            } elseif ($c['status'] === 'in_transit') {
                $in_transit_containers[] = $item;
            } elseif ($c['status'] === 'on_rail') {
                $on_rail_containers[] = $item;
            }
        }
    } catch (Exception $e) {}

    // Trains at Rail Siding
    $trains = [];
    try {
        $trn_stmt = $pdo->query("SELECT * FROM trains ORDER BY id ASC LIMIT 4");
        $trains = $trn_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    // CFS active jobs, CFS pallets (SSCC-18) inside warehouse & scheduled preadvice queue
    $cfs_jobs = [];
    $cfs_pallets = [];
    $cfs_containers = [];
    $cfs_summary = [
        'active_jobs' => 0,
        'completed_jobs' => 0,
        'pallets_in_rack' => 0,
        'pallets_dispatched' => 0,
        'total_weight_kg' => 0.0,
        'total_cbm' => 0.0,
        'ramps' => [
            'D1' => ['ramp' => 'D1', 'status' => 'STANDBY', 'container' => null, 'job_number' => null],
            'D2' => ['ramp' => 'D2', 'status' => 'STANDBY', 'container' => null, 'job_number' => null],
            'D3' => ['ramp' => 'D3', 'status' => 'STANDBY', 'container' => null, 'job_number' => null],
            'D4' => ['ramp' => 'D4', 'status' => 'STANDBY', 'container' => null, 'job_number' => null],
            'D5' => ['ramp' => 'D5', 'status' => 'STANDBY', 'container' => null, 'job_number' => null],
        ]
    ];
    try {
        $cfs_q = $pdo->query("SELECT id, job_number, job_type, container_number, dock_ramp, shipper_consignee, customs_status, status, total_packages, total_weight_kg, total_cbm FROM cfs_jobs ORDER BY id DESC LIMIT 12");
        $cfs_jobs = $cfs_q->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cfs_jobs as $cj) {
            $rmp = strtoupper(trim($cj['dock_ramp'] ?? 'D1'));
            if ($cj['status'] === 'IN_PROGRESS' || $cj['status'] === 'HOLD') {
                $cfs_summary['active_jobs']++;
                if (isset($cfs_summary['ramps'][$rmp])) {
                    $cfs_summary['ramps'][$rmp] = [
                        'ramp' => $rmp,
                        'status' => $cj['status'],
                        'container' => $cj['container_number'],
                        'job_number' => $cj['job_number'],
                        'job_type' => $cj['job_type'],
                        'packages' => (int)($cj['total_packages'] ?? 0)
                    ];
                }
            } elseif ($cj['status'] === 'COMPLETED') {
                $cfs_summary['completed_jobs']++;
            }
        }

        $pal_q = $pdo->query("SELECT id, job_id, pallet_sscc, container_origin, commodity_name, consignee, category, carton_count, gross_weight_kg, cbm, rack_location, customs_doc, customs_status, status, entry_date FROM cfs_pallets ORDER BY id DESC LIMIT 36");
        $raw_pallets = $pal_q->fetchAll(PDO::FETCH_ASSOC);
        foreach ($raw_pallets as $cp) {
            if ($cp['status'] === 'DISPATCHED') {
                $cfs_summary['pallets_dispatched']++;
            } else {
                $cfs_summary['pallets_in_rack']++;
                $cfs_summary['total_weight_kg'] += (float)($cp['gross_weight_kg'] ?? 0);
                $cfs_summary['total_cbm'] += (float)($cp['cbm'] ?? 0);
            }
            $sscc = (string)($cp['pallet_sscc'] ?? '');
            $cp['pallet_code'] = 'PAL-' . substr($sscc, -4);
            $cp['sscc_barcode'] = $sscc;
            $cp['container_number'] = $cp['container_origin'] ?? '-';
            $cp['cargo_description'] = $cp['commodity_name'] ?? 'Kargo LCL';
            $cp['weight_kg'] = (float)($cp['gross_weight_kg'] ?? 0);
            $cp['volume_cbm'] = (float)($cp['cbm'] ?? 0);
            $cfs_pallets[] = $cp;
        }
        $cfs_summary['total_weight_kg'] = round($cfs_summary['total_weight_kg'], 1);
        $cfs_summary['total_cbm'] = round($cfs_summary['total_cbm'], 2);

        foreach ($cfs_jobs as $cj) {
            if ($cj['status'] !== 'COMPLETED') {
                $cfs_containers[] = [
                    'container_number' => $cj['container_number'],
                    'ramp_no' => $cj['dock_ramp'] ?: 'D1',
                    'operation_type' => $cj['job_type'] ?: 'STRIPPING',
                    'status' => $cj['status'],
                    'processed_pallets' => max(1, (int)round(((float)($cj['total_packages'] ?? 30)) / 30)),
                    'total_pallets' => 8,
                    'bl_number' => 'HBL-CIDP-' . substr(preg_replace('/\D/', '', $cj['job_number']), -4),
                    'consignee' => $cj['shipper_consignee'] ?: 'PT Konsolidasi LCL'
                ];
            }
        }
    } catch (Exception $e) {}

    $preadvice_queue = [];
    try {
        $pq = $pdo->query("SELECT pre_gate_code, truck_plate, container_number, cargo_type, target_block, status FROM preadvice WHERE status = 'SCHEDULED' ORDER BY id ASC LIMIT 6");
        $preadvice_queue = $pq->fetchAll(PDO::FETCH_ASSOC);
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
        if (!empty($recent_events[0]) && ($recent_events[0]['event_type'] ?? '') === 'GATE_OUT') {
            $evTs = strtotime($recent_events[0]['created_at'] ?? 'now');
            if (($timestamp - $evTs) <= 15) {
                $gate_status['outbound_barrier'] = 'OPEN';
            }
        }
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
        'active_plugs' => max(count($reefer_units), (int)($blocks['REEFER'] ?? 0)),
        'total_capacity' => 120,
        'avg_temp_celsius' => $reefer_temp_cnt > 0 ? round($reefer_temp_sum / $reefer_temp_cnt, 1) : -18.4,
        'status' => $reefer_alarms > 0 ? 'ALARM' : 'OPTIMAL',
        'alerts' => $reefer_alarms,
        'units' => $reefer_units
    ];

    // Susun Antrean Kerja Nyata per Alat Berat (RS-01, RS-02, RS-03, RTG-01) dari DB
    $all_moves_cnt = 0;
    try {
        $all_moves_cnt = (int)$pdo->query("SELECT COUNT(*) FROM yard_events")->fetchColumn();
    } catch (Exception $e) {}

    $equipment_queues = [
        'RS-01'  => ['id' => 'RS-01',  'zone' => 'Blok A • Ekspor',     'color' => 'sky',     'jobs' => []],
        'RS-02'  => ['id' => 'RS-02',  'zone' => 'Blok B • Gate Hub',   'color' => 'orange',  'jobs' => []],
        'RS-03'  => ['id' => 'RS-03',  'zone' => 'Reefer 380V & DG',    'color' => 'cyan',    'jobs' => []],
        'RTG-01' => ['id' => 'RTG-01', 'zone' => 'Rail Siding Pasoso',  'color' => 'emerald', 'jobs' => []]
    ];

    // Map equipment carried containers & active transit/yard/rail items into work queues
    foreach ($equipment_queues as $eqId => &$qMeta) {
        $eqItem = $equipment[$eqId] ?? null;
        if ($eqItem && !empty($eqItem['carried_container'])) {
            $qMeta['jobs'][] = [
                'container' => $eqItem['carried_container'],
                'slot' => $eqItem['last_block'] ?: 'YARD',
                'mode' => 'ACTIVE',
                'label' => 'Carrying ' . $eqItem['carried_container']
            ];
        }
    }
    unset($qMeta);

    foreach ($in_transit_containers as $it) {
        $eqTarget = ($it['type'] === 'reefer' || $it['type'] === 'dg') ? 'RS-03' : (($it['block'] === 'A') ? 'RS-01' : 'RS-02');
        $equipment_queues[$eqTarget]['jobs'][] = [
            'container' => $it['container_number'],
            'slot' => ($it['block'] ?: 'GATE') . ' IN',
            'mode' => 'ACTIVE',
            'label' => 'Unload ' . substr($it['container_number'], 0, 7)
        ];
    }
    foreach ($active_slots as $sl) {
        $blk = strtoupper($sl['block'] ?? 'A');
        $eqTarget = ($blk === 'REEFER' || $blk === 'E' || $blk === 'DG') ? 'RS-03' : (($blk === 'A' || $blk === 'C') ? 'RS-01' : 'RS-02');
        $mode = ($sl['role_flow'] === 'load_out') ? 'READY_LOAD' : (($sl['role_flow'] === 'hold') ? 'HOLD' : 'DONE');
        $slotAddr = $blk . '-' . str_pad((string)($sl['bay'] ?? 1), 2, '0', STR_PAD_LEFT);
        if (count($equipment_queues[$eqTarget]['jobs']) < 6) {
            $equipment_queues[$eqTarget]['jobs'][] = [
                'container' => $sl['container_number'],
                'slot' => $slotAddr,
                'mode' => $mode,
                'label' => $slotAddr . ' ' . ($sl['role_flow'] === 'load_out' ? 'LOAD' : ($sl['role_flow'] === 'hold' ? 'HOLD' : 'IN'))
            ];
        }
    }
    foreach ($trains as $tr) {
        $isLoad = ($tr['status'] === 'loading');
        $equipment_queues['RTG-01']['jobs'][] = [
            'container' => $tr['train_code'],
            'slot' => ($tr['loaded_wagons'] ?? 0) . '/' . ($tr['total_wagons'] ?? 30) . ' Gerbong',
            'mode' => $isLoad ? 'ACTIVE' : 'QUEUED',
            'label' => $tr['train_code'] . ($isLoad ? ' LOAD' : ' DISCH')
        ];
    }
    foreach ($on_rail_containers as $rc) {
        if (count($equipment_queues['RTG-01']['jobs']) < 6) {
            $equipment_queues['RTG-01']['jobs'][] = [
                'container' => $rc['container_number'],
                'slot' => $rc['bay'] ?: 'PPCW',
                'mode' => 'READY_LOAD',
                'label' => ($rc['bay'] ?: 'PPCW') . ' ' . substr($rc['container_number'], 0, 6)
            ];
        }
    }
    foreach ($preadvice_queue as $pq) {
        $blk = strtoupper($pq['target_block'] ?: 'B');
        $eqTarget = ($blk === 'REEFER' || $blk === 'E') ? 'RS-03' : (($blk === 'A' || $blk === 'C') ? 'RS-01' : 'RS-02');
        if (count($equipment_queues[$eqTarget]['jobs']) < 6) {
            $equipment_queues[$eqTarget]['jobs'][] = [
                'container' => $pq['container_number'],
                'slot' => 'PRE-' . $blk,
                'mode' => 'QUEUED',
                'label' => $blk . ' ETA (' . substr($pq['container_number'], 0, 4) . ')'
            ];
        }
    }

    // 3. ALIRAN FINANSIAL (Financial Flow: Real-Time Accruals, Tariffs, Invoices)
    $financial_summary = [
        'total_revenue_accrued_idr' => 0.0,
        'weighbridge_fee_idr' => 0.0,
        'liftoff_fee_idr' => 0.0,
        'storage_dwell_fee_idr' => 0.0,
        'reefer_power_fee_idr' => 0.0,
        'cfs_fee_idr' => 0.0,
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
            COALESCE(SUM(cfs_amount), 0) as cfs,
            COALESCE(SUM(CASE WHEN payment_status = 'PAID' THEN total_amount ELSE 0 END), 0) as paid,
            COALESCE(SUM(CASE WHEN payment_status = 'PENDING' THEN total_amount ELSE 0 END), 0) as pending
            FROM billing_invoices");
        $rev = $rev_stmt->fetch(PDO::FETCH_ASSOC);
        if ($rev) {
            $financial_summary['total_revenue_accrued_idr'] = (float)$rev['total'];
            $financial_summary['liftoff_fee_idr'] = (float)$rev['lolo'];
            $financial_summary['storage_dwell_fee_idr'] = (float)$rev['storage'];
            $financial_summary['reefer_power_fee_idr'] = (float)$rev['reefer'];
            $financial_summary['cfs_fee_idr'] = (float)$rev['cfs'];
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
            'equipment_queues' => $equipment_queues,
            'total_moves_logged' => $all_moves_cnt,
            'trucks' => $trucks,
            'containers_in_yard' => $containers_count,
            'expected_count' => $expected_count,
            'blocks_distribution' => $blocks,
            'active_slots' => $active_slots,
            'cfs_pallets' => $cfs_pallets,
            'cfs_containers' => $cfs_containers,
            'cfs_summary' => $cfs_summary,
            'in_transit_containers' => $in_transit_containers,
            'on_rail_containers' => $on_rail_containers,
            'trains' => $trains,
            'preadvice_queue' => $preadvice_queue,
            'gate' => $gate_status
        ],
        'information' => [
            'recent_events' => $recent_events,
            'cfs_jobs' => $cfs_jobs,
            'cfs_pallets' => $cfs_pallets,
            'cfs_summary' => $cfs_summary,
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
    $json = json_encode($state, JSON_UNESCAPED_SLASHES);
    $tmp_file = TWIN_CACHE_FILE . '.tmp.' . uniqid();
    if (@file_put_contents($tmp_file, $json) !== false) {
        return @rename($tmp_file, TWIN_CACHE_FILE);
    }
    return false;
}
