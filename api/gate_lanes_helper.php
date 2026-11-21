<?php
/**
 * Conclusion Intermodal Dry Port (CIDP)
 * Single Source of Truth (SSoT): Multi-Lane Gate Plaza Configuration & Helpers
 * Architecture: 6 Gate-In (IN-01..IN-06) + 4 Gate-Out (OUT-01..OUT-04) = 10 Automated Lanes
 * File: api/gate_lanes_helper.php
 */

/**
 * Authoritative 10-lane catalog for the CIDP Main Gate Complex.
 * Coordinates map directly into the Digital Twin 3D space (via svgTo3D).
 *
 * @return array<string, array<string, mixed>>
 */
function cidp_gate_lane_catalog(): array {
    static $catalog = null;
    if ($catalog !== null) {
        return $catalog;
    }

    $specs = [
        ['IN-01', 'IN', 1, 'Gate-In 01 (Heavy VGM #1)',         'IN-01 Heavy',     'heavy_vgm',         true,  ['ANPR', 'OCR_ISO6346', 'WEIGHBRIDGE_80T', 'RFID_UHF'], 658, -58],
        ['IN-02', 'IN', 2, 'Gate-In 02 (Heavy VGM #2)',         'IN-02 Heavy',     'heavy_vgm',         true,  ['ANPR', 'OCR_ISO6346', 'WEIGHBRIDGE_80T', 'RFID_UHF'], 692, -58],
        ['IN-03', 'IN', 3, 'Gate-In 03 (Heavy VGM #3)',         'IN-03 Heavy',     'heavy_vgm',         true,  ['ANPR', 'OCR_ISO6346', 'WEIGHBRIDGE_80T', 'RFID_UHF'], 725, -58],
        ['IN-04', 'IN', 4, 'Gate-In 04 (Express Auto-Gate #1)', 'IN-04 Express',   'express',           false, ['ANPR', 'OCR_ISO6346', 'RFID_UHF'],                    759, -58],
        ['IN-05', 'IN', 5, 'Gate-In 05 (Express / Empty #2)',   'IN-05 Empty',     'empty_express',     false, ['ANPR', 'OCR_ISO6346', 'RFID_UHF'],                    792, -58],
        ['IN-06', 'IN', 6, 'Gate-In 06 (Reefer / DG / Red-Lane)', 'IN-06 Reefer/DG', 'reefer_dg',       true,  ['ANPR', 'OCR_ISO6346', 'WEIGHBRIDGE_80T', 'THERMAL'],  826, -58],
        ['OUT-01', 'OUT', 1, 'Gate-Out 01 (SPPB & Billing #1)', 'OUT-01 SPPB',     'outbound_sppb',     false, ['ANPR', 'OCR_ISO6346', 'E_SEAL_JT701'],                893, -58],
        ['OUT-02', 'OUT', 2, 'Gate-Out 02 (SPPB & Billing #2)', 'OUT-02 SPPB',     'outbound_sppb',     false, ['ANPR', 'OCR_ISO6346', 'E_SEAL_JT701'],                926, -58],
        ['OUT-03', 'OUT', 3, 'Gate-Out 03 (Empty / Chassis)',   'OUT-03 Chassis',  'outbound_empty',    false, ['ANPR', 'RFID_UHF'],                                   960, -58],
        ['OUT-04', 'OUT', 4, 'Gate-Out 04 (Reefer / Priority)', 'OUT-04 Reefer',   'outbound_priority', false, ['ANPR', 'OCR_ISO6346', 'E_SEAL_JT701', 'THERMAL'],     994, -58],
    ];

    $catalog = [];
    foreach ($specs as [$code, $dir, $num, $name, $short, $role, $wb, $sensors, $svgX, $svgY]) {
        $catalog[$code] = [
            'code'            => $code,
            'direction'       => $dir,
            'lane_num'        => $num,
            'name'            => $name,
            'short_name'      => $short,
            'role'            => $role,
            'has_weighbridge' => $wb,
            'sensors'         => $sensors,
            'svg_x'           => $svgX,
            'svg_y'           => $svgY,
        ];
    }
    return $catalog;
}

/**
 * Idempotently ensures multi-lane tracking columns exist on trucks table.
 */
function cidp_ensure_gate_schema(PDO $pdo): void {
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
        $cols = $pdo->query("SHOW COLUMNS FROM trucks")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('gate_lane', $cols, true)) {
            $pdo->exec("ALTER TABLE trucks ADD COLUMN gate_lane VARCHAR(20) NULL DEFAULT NULL AFTER status");
        }
        if (!in_array('gate_out_lane', $cols, true)) {
            $pdo->exec("ALTER TABLE trucks ADD COLUMN gate_out_lane VARCHAR(20) NULL DEFAULT NULL AFTER gate_lane");
        }
    } catch (Exception $e) {
        // Silently degrade if DB lacks ALTER privileges; runtime still uses catalog mapping
    }
}

/**
 * Normalizes any lane identifier or legacy alias into a valid IN-01..06 or OUT-01..04 code.
 */
function cidp_normalize_gate_lane(?string $raw, string $direction = 'IN'): string {
    $dir = strtoupper($direction) === 'OUT' ? 'OUT' : 'IN';
    $catalog = cidp_gate_lane_catalog();
    $clean = strtoupper(trim((string)$raw));

    if ($clean !== '' && isset($catalog[$clean]) && $catalog[$clean]['direction'] === $dir) {
        return $clean;
    }

    // Legacy / descriptive alias matching
    if ($clean !== '') {
        if (preg_match('/\b(IN|OUT)[-\s_]?0?([1-6])\b/', $clean, $m)) {
            $cand = $m[1] . '-0' . $m[2];
            if (isset($catalog[$cand]) && $catalog[$cand]['direction'] === $dir) {
                return $cand;
            }
        }
        if ($dir === 'IN') {
            if (strpos($clean, 'REEFER') !== false || strpos($clean, 'DG') !== false || strpos($clean, 'QUARANT') !== false) {
                return 'IN-06';
            }
            if (strpos($clean, 'FAST') !== false || strpos($clean, 'EXPRESS') !== false || strpos($clean, 'EMPTY') !== false) {
                return (strpos($clean, '#2') !== false || preg_match('/\b5\b/', $clean)) ? 'IN-05' : 'IN-04';
            }
            if (preg_match('/(?:LANE|GATE)[-\s_]*0?([1-6])\b/', $clean, $m)) {
                $cand = 'IN-0' . $m[1];
                if (isset($catalog[$cand])) {
                    return $cand;
                }
            }
        } else {
            if (strpos($clean, 'REEFER') !== false || strpos($clean, 'PRIOR') !== false) {
                return 'OUT-04';
            }
            if (strpos($clean, 'EMPTY') !== false || strpos($clean, 'CHASSIS') !== false) {
                return 'OUT-03';
            }
            if (preg_match('/(?:LANE|GATE)[-\s_]*0?([1-4])\b/', $clean, $m)) {
                $cand = 'OUT-0' . $m[1];
                if (isset($catalog[$cand])) {
                    return $cand;
                }
            }
        }
    }

    return $dir === 'OUT' ? 'OUT-01' : 'IN-01';
}

/**
 * Selects an explicit lane or assigns the best operational lane based on cargo, mission, and live lane load.
 */
function cidp_pick_gate_lane(PDO $pdo, string $direction = 'IN', array $ctx = []): string {
    $dir = strtoupper($direction) === 'OUT' ? 'OUT' : 'IN';
    $explicit = trim((string)($ctx['lane'] ?? $ctx['gate_lane'] ?? $ctx['lane_id'] ?? ''));
    if ($explicit !== '') {
        $norm = cidp_normalize_gate_lane($explicit, $dir);
        $catalog = cidp_gate_lane_catalog();
        if (isset($catalog[$norm]) && $catalog[$norm]['direction'] === $dir) {
            return $norm;
        }
    }

    $cargo = strtolower(trim((string)($ctx['cargo_type'] ?? '')));
    $block = strtoupper(trim((string)($ctx['block'] ?? $ctx['target_block'] ?? '')));
    $job   = strtolower(trim((string)($ctx['job_type'] ?? 'drop_off')));

    // Gather currently occupied lanes at_gate so concurrent trucks spread naturally across lanes
    $busy = [];
    try {
        $stmt = $pdo->query("SELECT gate_lane FROM trucks WHERE status = 'at_gate' AND gate_lane IS NOT NULL");
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $bLane) {
            $busy[$bLane] = true;
        }
    } catch (Exception $e) {}

    if ($dir === 'IN') {
        if ($cargo === 'reefer' || $cargo === 'dg' || in_array($block, ['REEFER', 'R', 'E', 'DG'], true)) {
            return 'IN-06';
        }
        $candidates = ($job === 'pick_up' || $cargo === 'empty' || $block === 'EMPTY')
            ? ['IN-04', 'IN-05', 'IN-03']
            : ['IN-01', 'IN-02', 'IN-03', 'IN-04'];
        foreach ($candidates as $cand) {
            if (empty($busy[$cand])) {
                return $cand;
            }
        }
        return $candidates[0];
    }

    // Outbound lane selection
    if ($cargo === 'reefer' || $cargo === 'dg' || in_array($block, ['REEFER', 'R', 'E', 'DG'], true)) {
        return 'OUT-04';
    }
    if ($job === 'drop_off' && !empty($ctx['empty_chassis_out'])) {
        return 'OUT-03';
    }
    $outCandidates = ($job === 'pick_up') ? ['OUT-01', 'OUT-02', 'OUT-04'] : ['OUT-03', 'OUT-02', 'OUT-01'];
    return $outCandidates[0];
}

/**
 * Builds the full 10-lane Gate Complex telemetry structure (6 IN + 4 OUT) for twin state & gate UI.
 *
 * @param array<int, array<string, mixed>> $trucks
 * @param array<int, array<string, mixed>> $recent_events
 * @return array<string, mixed>
 */
function cidp_build_gate_twin_state(PDO $pdo, array $trucks, array $recent_events, float $now_ts): array {
    $catalog = cidp_gate_lane_catalog();
    $lanes = [];
    foreach ($catalog as $code => $meta) {
        $lanes[$code] = array_merge($meta, [
            'barrier'          => 'CLOSED',
            'status'           => 'STANDBY',
            'truck_plate'      => null,
            'container_number' => null,
            'weight_kg'        => 0.0,
            'ocr_confidence'   => null,
            'last_event_at'    => null,
        ]);
    }

    $activeInLanes = [];
    $activeOutLanes = [];
    $primaryPlate = null;
    $primaryContainer = null;
    $primaryWeight = 0.0;

    // 1. Bind active trucks at_gate into their designated IN lane
    $atGateFallbackIdx = 0;
    $inCodes = ['IN-01', 'IN-02', 'IN-03', 'IN-04', 'IN-05', 'IN-06'];
    foreach ($trucks as $trk) {
        if (($trk['status'] ?? '') !== 'at_gate') {
            continue;
        }
        $rawLane = $trk['gate_lane'] ?? '';
        $laneCode = $rawLane !== ''
            ? cidp_normalize_gate_lane($rawLane, 'IN')
            : $inCodes[$atGateFallbackIdx % count($inCodes)];
        $atGateFallbackIdx++;

        if ($lanes[$laneCode]['truck_plate'] !== null) {
            foreach ($inCodes as $alt) {
                if ($lanes[$alt]['truck_plate'] === null) {
                    $laneCode = $alt;
                    break;
                }
            }
        }

        $wKg = (float)($trk['weight_gross'] ?? 34850.0);
        $lanes[$laneCode]['barrier']          = 'OPEN';
        $lanes[$laneCode]['status']           = 'ACTIVE';
        $lanes[$laneCode]['truck_plate']      = $trk['license_plate'] ?? null;
        $lanes[$laneCode]['container_number'] = $trk['container_number'] ?? null;
        $lanes[$laneCode]['weight_kg']        = $wKg;
        $lanes[$laneCode]['ocr_confidence']   = '99.4%';
        $lanes[$laneCode]['last_event_at']    = $trk['gate_in_time'] ?? date('Y-m-d H:i:s');
        $activeInLanes[$laneCode] = true;

        if ($primaryPlate === null) {
            $primaryPlate     = $trk['license_plate'] ?? null;
            $primaryContainer = $trk['container_number'] ?? null;
            $primaryWeight    = $wKg;
        }
    }

    // 2. Bind recent gate events (<= 18s) to open individual IN/OUT lane barriers
    foreach ($recent_events as $ev) {
        $evType = strtoupper((string)($ev['event_type'] ?? ''));
        $evTs   = strtotime($ev['created_at'] ?? 'now');
        $isFresh = ($now_ts - $evTs) <= 18;
        if (!$isFresh) {
            continue;
        }

        $eqRaw = (string)($ev['equipment_id'] ?? '');
        if ($evType === 'GATE_OUT') {
            $outLane = cidp_normalize_gate_lane($eqRaw, 'OUT');
            $lanes[$outLane]['barrier']          = 'OPEN';
            $lanes[$outLane]['status']           = 'ACTIVE';
            $lanes[$outLane]['container_number'] = $ev['container_number'] ?? $lanes[$outLane]['container_number'];
            $lanes[$outLane]['ocr_confidence']   = '99.7%';
            $lanes[$outLane]['last_event_at']    = $ev['created_at'] ?? null;
            $activeOutLanes[$outLane] = true;
        } elseif (in_array($evType, ['GATE_IN', 'ANPR_CAPTURE', 'OCR_CAPTURE', 'WEIGHBRIDGE_VGM'], true)) {
            $inLane = cidp_normalize_gate_lane($eqRaw, 'IN');
            if ($lanes[$inLane]['barrier'] !== 'OPEN') {
                $lanes[$inLane]['barrier']          = 'OPEN';
                $lanes[$inLane]['status']           = 'PROCESSING';
                $lanes[$inLane]['container_number'] = $ev['container_number'] ?? $lanes[$inLane]['container_number'];
                $lanes[$inLane]['ocr_confidence']   = '99.5%';
                $lanes[$inLane]['last_event_at']    = $ev['created_at'] ?? null;
                $activeInLanes[$inLane] = true;
            }
        }
    }

    $inList  = array_keys($activeInLanes);
    $outList = array_keys($activeOutLanes);

    return [
        'config' => [
            'inbound_lanes'     => 6,
            'outbound_lanes'    => 4,
            'total_lanes'       => 10,
            'corridor_distance_m' => 87,
        ],
        // Legacy compatibility top-level summary fields
        'inbound_barrier'       => count($inList) > 0 ? 'OPEN' : 'CLOSED',
        'outbound_barrier'      => count($outList) > 0 ? 'OPEN' : 'CLOSED',
        'weighbridge_active_kg' => $primaryWeight,
        'weighbridge_truck'     => $primaryPlate,
        'current_ocr_plate'     => $primaryPlate,
        'current_ocr_container' => $primaryContainer,
        'last_anpr_scan'        => !empty($recent_events) ? ($recent_events[0]['created_at'] ?? null) : null,
        // Multi-lane 6-IN / 4-OUT live maps
        'active_inbound_lanes'  => $inList,
        'active_outbound_lanes' => $outList,
        'lanes'                 => $lanes,
    ];
}
