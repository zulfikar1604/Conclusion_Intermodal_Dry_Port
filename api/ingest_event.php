<?php
/**
 * Conclusion Intermodal Dry Port (CIDP)
 * Edge Telemetry & Hardware Ingestion Endpoint (Tri-Flow Correlation)
 * File: api/ingest_event.php
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/twin_state_helper.php';
require_once __DIR__ . '/preadvice_helper.php';
require_once __DIR__ . '/oauth_helper.php';
global $pdo;
ensure_integration_schema($pdo);
cidp_ensure_gate_schema($pdo);

// Accept internal $_POST (when included by edge_simulator / demo_control) or JSON payload
$raw_input = '';
$data = [];
if (!empty($_POST['event_type'])) {
    $data = $_POST;
} else {
    $raw_input = file_get_contents('php://input');
    if (!empty($raw_input)) {
        $data = @json_decode($raw_input, true) ?: [];
    }
    if (empty($data)) {
        $data = $_POST;
    }
}

if (empty($data) || empty($data['event_type'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request. "event_type" is required.',
        'available_events' => [
            'TRUCK_GATE_ARRIVAL',
            'TRUCK_WEIGHBRIDGE',
            'EQUIPMENT_MOVE',
            'CONTAINER_LIFT',
            'CONTAINER_STACK',
            'CUSTOMS_CEISA',
            'XRAY_SCAN_RESULT',
            'ESEAL_TELEMETRY',
            'REEFER_TELEMETRY',
            'PAYMENT_SETTLED',
            'YARD_RELOCATION',
            'CFS_STRIPPING_START',
            'CFS_PALLET_SCAN',
            'CFS_STRIPPING_COMPLETE',
            'CFS_PALLET_DISPATCH',
            'HANDHELD_AIDC_SCAN',
            'CUSTOMS_INSPECTION',
            'REEFER_PLUGIN',
            'RAIL_AXLE_COUNTER',
            'RAIL_LOADING',
            'RAIL_UNLOADING',
            'TRUCK_GATE_OUT'
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

$event_type = strtoupper($data['event_type']);
$source_device = $data['source_device'] ?? 'EDGE-SIMULATOR';
$event_id = $data['event_id'] ?? ($_SERVER['HTTP_X_IDEMPOTENCY_KEY'] ?? ('EVT-' . date('YmdHis') . '-' . substr(uniqid(), -4)));
$p = $data['payload'] ?? $data;

// IDEMPOTENCY: event_id yang sudah pernah diproses tidak dijalankan ulang (Zero Double-Billing)
$dup = $pdo->prepare("SELECT response_json FROM processed_events WHERE event_id = ?");
$dup->execute([$event_id]);
$dup_resp = $dup->fetchColumn();
if ($dup_resp !== false) {
    $prev = json_decode($dup_resp, true) ?: [];
    $prev['duplicate'] = true;
    $prev['message'] = 'Event sudah diproses sebelumnya (idempotent) - tidak diproses ulang.';
    echo json_encode($prev, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'ingest_event.php') exit;
    return;
}

// Event selain EQUIPMENT_MOVE / RAIL_AXLE_COUNTER / HANDHELD_AIDC_SCAN wajib membawa identitas dari sensor
$needs_container = ['TRUCK_WEIGHBRIDGE', 'CONTAINER_LIFT', 'CONTAINER_STACK', 'CUSTOMS_CEISA', 'PAYMENT_SETTLED',
    'YARD_RELOCATION', 'CFS_STRIPPING_START', 'CFS_PALLET_SCAN', 'CFS_STRIPPING_COMPLETE', 'CUSTOMS_INSPECTION',
    'XRAY_SCAN_RESULT', 'ESEAL_TELEMETRY', 'REEFER_PLUGIN', 'REEFER_TELEMETRY', 'RAIL_LOADING', 'RAIL_UNLOADING', 'TRUCK_GATE_OUT'];

$tri_flow_log = [
    'physical' => '',
    'information' => '',
    'financial' => ''
];
$thresholds = cidp_get_thresholds($pdo);
$decision_summary = null;

try {
    // =========================================================================
    // 0. OAUTH 2.0 BEARER JWT & HMAC-SHA256 CRYPTOGRAPHIC TRUST VERIFICATION
    // =========================================================================
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
    if ($auth_header === '' && function_exists('apache_request_headers')) {
        $hdrs = apache_request_headers();
        $auth_header = $hdrs['Authorization'] ?? ($hdrs['authorization'] ?? '');
    }
    $bearer_token = trim(preg_replace('/^Bearer\s+/i', '', $auth_header));
    if ($bearer_token === '') {
        $bearer_token = trim((string)($data['bearer_token'] ?? ($data['access_token'] ?? ($data['oauth_context']['access_token'] ?? ''))));
    }
    $provided_hmac = trim((string)($_SERVER['HTTP_X_PAYLOAD_SIGNATURE'] ?? ($data['signature_hmac'] ?? '')));
    $enforce_security = !empty($_SERVER['HTTP_X_ENFORCE_SECURITY']) || !empty($data['enforce_security']);

    if ($bearer_token === '' && !$enforce_security) {
        // Fallback internal portal UI: terbitkan envelope OAuth 2.0 sesuai event_type
        $auto_env = oauth_build_signed_envelope($event_type, is_array($p) ? $p : []);
        $bearer_token = $auto_env['bearer_token'];
        if ($provided_hmac === '') {
            $provided_hmac = $auto_env['signature_hmac'];
        }
    } elseif ($bearer_token === '' && $enforce_security) {
        throw new OAuthSecurityException('Header Authorization: Bearer <OAuth2_JWT_Token> tidak ditemukan pada request.', 401, 'missing_token');
    }

    $token_info = oauth_verify_jwt($bearer_token);
    $scope_map = oauth_event_scope_map();
    $required_scope = $scope_map[$event_type]['scope'] ?? 'ingest:telemetry';

    if (!in_array($required_scope, $token_info['granted_scopes'], true)) {
        throw new OAuthSecurityException(
            "Akses Ditolak (Least Privilege Scope Violation): Token milik client '{$token_info['client_id']}' hanya memiliki scope [" . implode(', ', $token_info['granted_scopes']) . "], sedangkan event {$event_type} mewajibkan scope '{$required_scope}'.",
            403,
            'insufficient_scope'
        );
    }

    if ($provided_hmac !== '' || $enforce_security) {
        if (!oauth_verify_payload_hmac($provided_hmac, $token_info['client_id'], $event_type, is_array($p) ? $p : [], $raw_input)) {
            throw new OAuthSecurityException(
                "Manipulasi Payload Terdeteksi (HMAC-SHA256 Signature Mismatch)! Isi payload tidak cocok dengan tanda tangan kriptografi dari '{$token_info['client_id']}'.",
                401,
                'hmac_signature_mismatch'
            );
        }
    }

    $oauth_verified = [
        'authenticated'   => true,
        'client_id'       => $token_info['client_id'],
        'scope_matched'   => $required_scope,
        'integrity_check' => 'HMAC_SHA256_VALID',
        'token_jti'       => $token_info['jti']
    ];

    // =========================================================================
    // 0B. SURGE RATE-LIMITER & BCP STORE-AND-FORWARD FAILOVER GUARD
    // =========================================================================
    $is_replay = !empty($data['bcp_replay']);
    $bcp_state = cidp_get_bcp_state($pdo);
    $rate_limit_max = isset($data['rate_limit_max']) ? max(1, (int)$data['rate_limit_max']) : 20;
    $rate_check = cidp_check_rate_limit($token_info['client_id'], $rate_limit_max, 5, $is_replay);

    if (!$is_replay && (!empty($data['simulate_surge']) || !$rate_check['allowed'] || !empty($bcp_state['failover_active']))) {
        $reason_code = !empty($bcp_state['failover_active']) ? 'BCP_EDGE_BUFFER' : 'SURGE_BACKPRESSURE';
        $buf_reason = !empty($bcp_state['failover_active'])
            ? "[BCP_EDGE_BUFFER] Mode Kontingensi aktif ({$bcp_state['mode']}): event {$event_type} disimpan aman ke Store-and-Forward lokal (RPO=0)."
            : "[SURGE_BACKPRESSURE] Lonjakan trafik ({$rate_check['count']}/{$rate_check['limit']} req/5dtk) untuk {$token_info['client_id']}: dialihkan aman ke Edge Buffer (Zero Data Loss).";
        dlq_push($pdo, $event_id, $event_type, $source_device, $buf_reason, $data, 'BUFFERED');
        http_response_code(202);
        echo json_encode([
            'success' => true,
            'buffered' => true,
            'reason_code' => $reason_code,
            'event_id' => $event_id,
            'event_type' => $event_type,
            'source_device' => $source_device,
            'oauth_verified' => $oauth_verified,
            'rate_limit' => $rate_check,
            'bcp_mode' => $bcp_state['mode'],
            'routed_to' => 'EDGE_BACKPRESSURE_BUFFER',
            'message' => $buf_reason,
            'tri_flow' => [
                'physical' => 'Operasi lapangan tetap berjalan tanpa henti (Non-Blocking Gate/Yard)',
                'information' => "Payload diamankan ke Edge Store-and-Forward Buffer ({$reason_code})",
                'financial' => 'Tagihan tidak hilang; akan tersinkron otomatis saat buffer di-flush (RPO=0)'
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'ingest_event.php') exit;
        return;
    }

    $pdo->beginTransaction();

    // CONTRACT-FIRST: validasi identitas sebelum menyentuh data operasional
    if (in_array($event_type, $needs_container, true)) {
        $cn = strtoupper(trim($p['container_number'] ?? ''));
        if ($cn === '') {
            throw new IngestRejection("Payload {$event_type} wajib membawa container_number.");
        }
        if (!iso6346_valid($cn)) {
            throw new IngestRejection("Check digit ISO 6346 tidak valid untuk kontainer {$cn}.");
        }
        $ck = $pdo->prepare("SELECT status FROM containers WHERE container_number = ?");
        $ck->execute([$cn]);
        $cst = $ck->fetchColumn();
        if ($cst === false) {
            throw new IngestRejection("Kontainer {$cn} tidak terdaftar (tidak ada pre-advice).");
        }
        if ($cst === 'expected') {
            throw new IngestRejection("Kontainer {$cn} belum melewati Gate Inbound (status masih di luar dry port).");
        }
        $p['container_number'] = $cn;
        if (empty($p['truck_plate'])) {
            $pl = $pdo->prepare("SELECT license_plate FROM trucks WHERE container_number = ? ORDER BY id DESC LIMIT 1");
            $pl->execute([$cn]);
            $p['truck_plate'] = $pl->fetchColumn() ?: '';
        }
    }

    switch ($event_type) {

        // =====================================================================
        // 1. TRUCK_GATE_ARRIVAL (OCR Kamera & Antena RFID Gate Inbound)
        // =====================================================================
        case 'TRUCK_GATE_ARRIVAL':
            // Identitas HANYA dari sensor ANPR/OCR/RFID, lalu dicocokkan dengan pre-advice (booking/DO)
            $plate = strtoupper(trim($p['truck_plate'] ?? ''));
            $container_no = strtoupper(trim($p['container_number'] ?? ''));
            $rfid = trim($p['rfid_tag'] ?? '');
            $code = trim($p['pre_gate_code'] ?? '');

            if ($plate === '' && $rfid === '' && $code === '') {
                throw new IngestRejection('Sensor gate tidak mengirim identitas (plat / RFID / kode pre-gate).');
            }

            $q = $pdo->prepare("SELECT * FROM preadvice WHERE status IN ('SCHEDULED','HOLD_ANOMALY','ARRIVED','REJECTED')
                AND ((? <> '' AND pre_gate_code = ?) OR (? <> '' AND truck_plate = ?) OR (? <> '' AND rfid_tag = ?))
                ORDER BY (status IN ('SCHEDULED','HOLD_ANOMALY')) DESC, id ASC LIMIT 1");
            $q->execute([$code, $code, $plate, $plate, $rfid, $rfid]);
            $pre = $q->fetch(PDO::FETCH_ASSOC);
            if (!$pre) {
                $catMeta = cidp_get_anomaly_meta('UNREGISTERED_TRUCK_REJECT') ?: [];
                throw new IngestRejection(
                    "Tidak ada pre-advice/booking untuk truk " . ($plate ?: $rfid ?: $code) . " - akses gate ditolak (unregistered).",
                    array_merge($catMeta, [
                        'anomaly_code'     => 'UNREGISTERED_TRUCK_REJECT',
                        'truck_plate'      => $plate ?: 'UNKNOWN',
                        'container_number' => $container_no ?: 'UNKNOWN',
                        'interlock_state'  => 'BARRIER_GATE_LOCKED_RED'
                    ])
                );
            }
            if ($pre['status'] === 'REJECTED') {
                throw new IngestRejection(
                    "Truk {$pre['truck_plate']} ({$pre['pre_gate_code']}) telah ditolak & diperintahkan putar balik (BA: " . ($pre['ba_number'] ?: '-') . ").",
                    ['pre_gate_code' => $pre['pre_gate_code'], 'anomaly_code' => $pre['anomaly_code'] ?: 'REJECTED', 'truck_plate' => $pre['truck_plate'], 'container_number' => $pre['container_number']]
                );
            }
            if ($pre['status'] === 'ARRIVED') {
                throw new IngestRejection("Truk {$pre['truck_plate']} sudah tercatat masuk (pre-gate {$pre['pre_gate_code']}).");
            }

            // Deteksi Anomali Gate In dari data Pre-Advice / Sensor Fisik (sebelum palang dibuka)
            $isResolved = (($pre['resolution_status'] ?? 'NONE') === 'OVERRIDDEN_APPROVED');
            if (!$isResolved && !empty($pre['anomaly_code']) && in_array($pre['anomaly_code'], ['DOC_DO_EXPIRED', 'CONTAINER_MISMATCH', 'SEAL_TAMPERED', 'OCR_CHECKDIGIT_ERROR'], true)) {
                $catMeta = cidp_get_anomaly_meta($pre['anomaly_code']) ?: [];
                $anomMeta = array_merge($catMeta, [
                    'pre_gate_code'        => $pre['pre_gate_code'],
                    'anomaly_code'         => $pre['anomaly_code'],
                    'anomaly_title'        => $pre['anomaly_title'] ?: ($catMeta['anomaly_title'] ?? $pre['anomaly_code']),
                    'anomaly_stage'        => 'GATE_IN',
                    'anomaly_notes'        => $pre['anomaly_notes'] ?: ($catMeta['anomaly_notes'] ?? ''),
                    'truck_plate'          => $pre['truck_plate'],
                    'container_number'     => $pre['container_number'],
                    'actual_container_ocr' => $pre['actual_container_ocr'] ?: $container_no,
                    'seal_number'          => $pre['seal_number'],
                    'actual_seal_ocr'      => $pre['actual_seal_ocr'],
                    'driver_name'          => $pre['driver_name'],
                    'company'              => $pre['company'],
                    'owner_company'        => $pre['owner_company'],
                    'do_number'            => $pre['do_number'],
                    'interlock_state'      => 'BARRIER_GATE_LOCKED_RED'
                ]);
                throw new IngestRejection(
                    "[INTERLOCK GATE IN — {$pre['anomaly_code']}] {$anomMeta['anomaly_title']}: {$anomMeta['anomaly_notes']}",
                    $anomMeta
                );
            }

            if ($container_no !== '' && !iso6346_valid($container_no)) {
                $catMeta = cidp_get_anomaly_meta('OCR_CHECKDIGIT_ERROR') ?: [];
                throw new IngestRejection(
                    "Check digit ISO 6346 tidak valid untuk kontainer {$container_no}.",
                    array_merge($catMeta, [
                        'pre_gate_code'        => $pre['pre_gate_code'],
                        'anomaly_code'         => 'OCR_CHECKDIGIT_ERROR',
                        'truck_plate'          => $pre['truck_plate'],
                        'container_number'     => $pre['container_number'],
                        'actual_container_ocr' => $container_no,
                        'interlock_state'      => 'BARRIER_GATE_LOCKED_RED'
                    ])
                );
            }
            if ($container_no !== '' && $container_no !== $pre['container_number']) {
                $catMeta = cidp_get_anomaly_meta('CONTAINER_MISMATCH') ?: [];
                throw new IngestRejection(
                    "Kontainer fisik OCR ({$container_no}) tidak sesuai dengan dokumen booking Pre-Advice ({$pre['container_number']}).",
                    array_merge($catMeta, [
                        'pre_gate_code'        => $pre['pre_gate_code'],
                        'anomaly_code'         => 'CONTAINER_MISMATCH',
                        'truck_plate'          => $pre['truck_plate'],
                        'container_number'     => $pre['container_number'],
                        'actual_container_ocr' => $container_no,
                        'interlock_state'      => 'BARRIER_GATE_LOCKED_RED'
                    ])
                );
            }
            $plate = $pre['truck_plate'];
            $container_no = $pre['container_number'];

            // Pre-advice -> ARRIVED
            $pdo->prepare("UPDATE preadvice SET status = 'ARRIVED', arrived_at = NOW() WHERE id = ?")->execute([$pre['id']]);

            $gate_lane = cidp_pick_gate_lane($pdo, 'IN', [
                'lane'       => $p['gate_lane'] ?? $p['lane_id'] ?? $p['lane'] ?? null,
                'cargo_type' => $pre['cargo_type'] ?? 'dry',
                'block'      => $pre['target_block'] ?? 'A',
                'job_type'   => $pre['job_type'] ?? 'drop_off',
            ]);

            // Truk: dari "scheduled" (di luar) menjadi "at_gate" (di dalam proses dry port)
            $t_check = $pdo->prepare("SELECT id FROM trucks WHERE license_plate = ?");
            $t_check->execute([$plate]);
            $tid = $t_check->fetchColumn();
            if ($tid) {
                $pdo->prepare("UPDATE trucks SET container_number = ?, driver_name = ?, company = ?, status = 'at_gate', gate_lane = ?, gate_in_time = NOW() WHERE id = ?")
                    ->execute([$container_no, $pre['driver_name'], $pre['company'], $gate_lane, $tid]);
            } else {
                $pdo->prepare("INSERT INTO trucks (license_plate, rfid_tag, driver_name, company, job_type, do_number, container_number, target_block, status, gate_lane, gate_in_time) VALUES (?,?,?,?,?,?,?,?, 'at_gate', ?, NOW())")
                    ->execute([$plate, $pre['rfid_tag'], $pre['driver_name'], $pre['company'], $pre['job_type'], $pre['do_number'], $container_no, $pre['target_block'], $gate_lane]);
            }

            // Kontainer: dari "expected" (di luar) menjadi "in_transit" di GATE
            $c_check = $pdo->prepare("SELECT id FROM containers WHERE container_number = ?");
            $c_check->execute([$container_no]);
            if ($c_check->fetchColumn()) {
                $pdo->prepare("UPDATE containers SET status = 'in_transit', block = 'GATE', bay = NULL, row = NULL, tier = NULL, gate_in_time = NOW() WHERE container_number = ?")
                    ->execute([$container_no]);
            } else {
                $pdo->prepare("INSERT INTO containers (container_number, iso_code, size_type, cargo_type, rfid_tag, owner_company, customs_status, block, status, gate_in_time) VALUES (?,?,?,?,?,?, 'AWAITING_CLEARANCE', 'GATE', 'in_transit', NOW())")
                    ->execute([$container_no, $pre['iso_code'], $pre['size_type'], $pre['cargo_type'], $pre['rfid_tag'], $pre['owner_company']]);
            }

            // Log event dengan kode lajur spesifik (IN-01..IN-06)
            $ev_ins = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, from_block, to_block, notes, billable_amount) VALUES ('GATE_IN', ?, ?, 'OUTSIDE', 'GATE', ?, 0)");
            $ev_ins->execute([$container_no, $gate_lane, "[{$gate_lane}] Truk {$plate} terdeteksi OCR & RFID di Gate Inbound (pre-gate {$pre['pre_gate_code']})"]);

            $tri_flow_log['physical'] = "Truk {$plate} tiba di {$gate_lane}; Palang Barrier {$gate_lane} Terbuka";
            $tri_flow_log['information'] = "OCR {$gate_lane} membaca pelat {$plate} & kontainer {$container_no}; cocok dengan pre-advice {$pre['pre_gate_code']} ({$pre['source']})";
            $tri_flow_log['financial'] = "Verifikasi izin masuk terminal tervalidasi (Tanpa penalti)";
            break;

        // =====================================================================
        // 2. TRUCK_WEIGHBRIDGE (Penimbangan 80 Ton SOLAS VGM)
        // =====================================================================
        case 'TRUCK_WEIGHBRIDGE':
            $plate = $p['truck_plate'] ?? '';
            $container_no = $p['container_number'] ?? '';
            $raw_gross = (float)($p['weight_gross'] ?? 34850.0);
            $weight_gross = max(0.0, $raw_gross + (float)$thresholds['vgm_zero_offset_kg']);
            $weigh_fee = 75000.0;
            $tolPct = max(0.5, (float)$thresholds['vgm_tolerance_pct']);
            $maxGross = max(10000.0, (float)$thresholds['vgm_max_gross_kg']);

            // Cek apakah ada anomali selisih berat SOLAS VGM (> threshold%) atau Overload (> maxGross) yang belum di-override
            $paWb = $pdo->prepare("SELECT * FROM preadvice WHERE container_number = ? OR (truck_plate = ? AND ? <> '') ORDER BY id DESC LIMIT 1");
            $paWb->execute([$container_no, $plate, $plate]);
            $preWb = $paWb->fetch(PDO::FETCH_ASSOC);
            $declCheck = (float)($p['declared_weight_kg'] ?? ($preWb['declared_weight_kg'] ?? 0));
            $isDynamicBreach = ($weight_gross > $maxGross) || ($declCheck > 0 && abs(($weight_gross - $declCheck) / $declCheck) * 100.0 > $tolPct);
            if ($preWb || $isDynamicBreach) {
                $isWbResolved = ($preWb && (($preWb['resolution_status'] ?? 'NONE') === 'OVERRIDDEN_APPROVED'));
                if (!$isWbResolved && (($preWb['anomaly_code'] ?? '') === 'VGM_OVERWEIGHT' || $isDynamicBreach)) {
                    if ($preWb && ($preWb['anomaly_code'] ?? '') === 'VGM_OVERWEIGHT' && empty($p['weight_gross'])) {
                        $weight_gross = (float)($preWb['actual_weight_kg'] ?: 39250.0);
                    }
                    $declW = (float)($declCheck > 0 ? $declCheck : (($preWb['declared_weight_kg'] ?? 0) ?: 26000.0));
                    $diffPct = round((($weight_gross - $declW) / max(1.0, $declW)) * 100, 1);
                    $catMeta = cidp_get_anomaly_meta('VGM_OVERWEIGHT') ?: [];
                    $anomMeta = array_merge($catMeta, [
                        'pre_gate_code'      => $preWb['pre_gate_code'] ?? null,
                        'anomaly_code'       => 'VGM_OVERWEIGHT',
                        'anomaly_title'      => ($preWb['anomaly_title'] ?? '') ?: "Deviasi / Beban Timbangan SOLAS VGM > {$tolPct}%",
                        'anomaly_stage'      => 'WEIGHBRIDGE',
                        'anomaly_notes'      => ($preWb['anomaly_notes'] ?? '') ?: "Sensor Load-Cell 80T mencatat berat aktual " . number_format($weight_gross, 0, ',', '.') . " kg vs deklarasi manifest " . number_format($declW, 0, ',', '.') . " kg (Deviasi {$diffPct}%, batas toleransi ±{$tolPct}% / maks {$maxGross} kg).",
                        'truck_plate'        => ($preWb['truck_plate'] ?? '') ?: $plate,
                        'container_number'   => $container_no,
                        'declared_weight_kg' => $declW,
                        'actual_weight_kg'   => $weight_gross,
                        'discrepancy_pct'    => $diffPct,
                        'driver_name'        => $preWb['driver_name'] ?? '',
                        'company'            => $preWb['company'] ?? '',
                        'owner_company'      => $preWb['owner_company'] ?? '',
                        'interlock_state'    => 'WEIGHBRIDGE_HOLD_RS02_LOCKED'
                    ]);
                    throw new IngestRejection(
                        "[INTERLOCK TIMBANGAN 80T — VGM_OVERWEIGHT] Berat aktual " . number_format($weight_gross, 0, ',', '.') . " kg melampaui batas toleransi ±{$tolPct}% / maks {$maxGross} kg ({$diffPct}%). Work Order Reach Stacker diblokir!",
                        $anomMeta
                    );
                }
            }

            $wb_lane = cidp_pick_gate_lane($pdo, 'IN', [
                'lane'       => $p['gate_lane'] ?? $p['lane_id'] ?? null,
                'cargo_type' => $preWb['cargo_type'] ?? 'dry',
                'block'      => $preWb['target_block'] ?? 'A',
            ]);
            if (!empty($wb_lane) && !in_array($wb_lane, ['IN-01', 'IN-02', 'IN-03', 'IN-06'], true)) {
                $wb_lane = 'IN-01';
            }

            $stmt = $pdo->prepare("UPDATE trucks SET weight_gross = ?, status = 'in_yard', gate_lane = COALESCE(gate_lane, ?) WHERE license_plate = ?");
            $stmt->execute([$weight_gross, $wb_lane, $plate]);

            // Update container gross weight
            $c_upd = $pdo->prepare("UPDATE containers SET gross_weight_kg = ? WHERE container_number = ?");
            $c_upd->execute([$weight_gross, $container_no]);

            // Accrue Weighbridge Fee into billing_invoices
            $inv_no = 'INV-W-' . date('Ymd') . '-' . substr(uniqid(), -4);
            $inv_stmt = $pdo->prepare("INSERT INTO billing_invoices (invoice_number, container_number, customer_name, service_type, total_amount, payment_status, payment_method, created_at) VALUES (?, ?, 'PT Samudera Logistik Prima', 'Penimbangan Gantry 80T SOLAS VGM', ?, 'PENDING', 'Bank Transfer (Mandiri H2H)', NOW())");
            $inv_stmt->execute([$inv_no, $container_no, $weigh_fee]);

            // Log event
            $ev_ins = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, notes, billable_amount) VALUES ('WEIGHBRIDGE_VGM', ?, ?, ?, ?)");
            $ev_ins->execute([$container_no, $wb_lane, "[{$wb_lane}] Penimbangan SOLAS VGM 80T: " . number_format($weight_gross, 0) . " kg (Offset: {$thresholds['vgm_zero_offset_kg']} kg)", $weigh_fee]);

            $tri_flow_log['physical'] = "Truk {$plate} ditimbang di Jembatan Timbang 80T {$wb_lane} (Berat: " . number_format($weight_gross, 0) . " kg)";
            $tri_flow_log['information'] = "Sertifikat SOLAS VGM diterbitkan secara digital untuk {$container_no}";
            $tri_flow_log['financial'] = "Biaya Timbang Rp " . number_format($weigh_fee, 0, ',', '.') . " terakrual pada invoice {$inv_no}";
            $decision_summary = [
                'verdict' => 'PASS_VGM_CERTIFIED',
                'rule'    => "SOLAS VGM Load-Cell Check (Toleransi ±{$tolPct}%, Maks {$maxGross} kg)",
                'reason'  => "Berat terukur " . number_format($weight_gross, 0) . " kg lolos toleransi kalibrasi.",
                'next_action' => "Lanjutkan angkat Reach Stacker ke koordinat Yard."
            ];
            break;

        // =====================================================================
        // 3. EQUIPMENT_MOVE (Telemetri GPS & CAN-Bus Kalmar Reach Stacker)
        // =====================================================================
        case 'EQUIPMENT_MOVE':
            $eq_id = $p['equipment_id'] ?? 'RS-02';
            $gps_x = (float)($p['gps_x'] ?? 320.0);
            $gps_y = (float)($p['gps_y'] ?? 240.0);
            $fuel = (int)($p['fuel_percent'] ?? 78);
            $status = $p['status'] ?? 'operating';

            $stmt = $pdo->prepare("UPDATE equipment SET gps_x = ?, gps_y = ?, fuel_percent = ?, status = ?, last_updated = NOW() WHERE equipment_id = ?");
            $stmt->execute([$gps_x, $gps_y, $fuel, $status, $eq_id]);

            $tri_flow_log['physical'] = "Alat berat {$eq_id} bergerak ke koordinat ({$gps_x}, {$gps_y})";
            $tri_flow_log['information'] = "Telemetri CAN-Bus: BBM {$fuel}%, status {$status}";
            $tri_flow_log['financial'] = "Konsumsi solar & jam operasional alat dicatat ke OPEX";
            break;

        // =====================================================================
        // 4. CONTAINER_LIFT (Reach Stacker Mengangkat Kontainer dari Truk)
        // =====================================================================
        case 'CONTAINER_LIFT':
            $eq_id = $p['equipment_id'] ?? 'RS-02';
            $container_no = $p['container_number'] ?? '';
            $plate = $p['truck_plate'] ?? '';
            $lolo_fee = 325000.0;

            // Equipment state: carrying container
            $stmt = $pdo->prepare("UPDATE equipment SET status = 'carrying', current_container = ?, last_updated = NOW() WHERE equipment_id = ?");
            $stmt->execute([$container_no, $eq_id]);

            // Container state: in transit
            $c_stmt = $pdo->prepare("UPDATE containers SET status = 'in_transit' WHERE container_number = ?");
            $c_stmt->execute([$container_no]);

            // Truck is now empty/loading complete
            $t_stmt = $pdo->prepare("UPDATE trucks SET status = 'loading' WHERE license_plate = ?");
            $t_stmt->execute([$plate]);

            // Accrue Lift-Off Fee into billing
            $inv_no = 'INV-L-' . date('Ymd') . '-' . substr(uniqid(), -4);
            $inv_stmt = $pdo->prepare("INSERT INTO billing_invoices (invoice_number, container_number, customer_name, service_type, lolo_amount, total_amount, payment_status, payment_method, created_at) VALUES (?, ?, 'PT Samudera Logistik Prima', 'Lift-Off Heavy Equipment Stacking', ?, ?, 'PENDING', 'Bank Transfer (Mandiri H2H)', NOW())");
            $inv_stmt->execute([$inv_no, $container_no, $lolo_fee, $lolo_fee]);

            // Log event
            $ev_ins = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, notes, billable_amount) VALUES ('LIFT_OFF', ?, ?, ?, ?)");
            $ev_ins->execute([$container_no, $eq_id, "Pengangkatan kontainer dari truk {$plate} oleh {$eq_id}", $lolo_fee]);

            $tri_flow_log['physical'] = "Spreader {$eq_id} mengunci twistlock kontainer {$container_no} di atas sasis truk";
            $tri_flow_log['information'] = "Status kontainer berganti menjadi IN_TRANSIT (Alat: {$eq_id})";
            $tri_flow_log['financial'] = "Tarif Lift-Off Rp " . number_format($lolo_fee, 0, ',', '.') . " terakrual pada {$inv_no}";
            break;

        // =====================================================================
        // 5. CONTAINER_STACK (Penempatan di Slot Blok A-E, Bay-Row-Tier)
        // =====================================================================
        case 'CONTAINER_STACK':
            $eq_id = $p['equipment_id'] ?? 'RS-02';
            $container_no = $p['container_number'] ?? '';
            $useAutoSlot = !empty($p['auto_slot']) || empty($p['block']);
            $slotReason = 'Koordinat DGPS RTK ditentukan langsung oleh operator spreader.';
            if ($useAutoSlot) {
                $cq = $pdo->prepare("SELECT container_number, cargo_type, size_type, customs_status, gross_weight_kg FROM containers WHERE container_number = ? LIMIT 1");
                $cq->execute([$container_no]);
                $cRowInfo = $cq->fetch(PDO::FETCH_ASSOC) ?: ['container_number' => $container_no];
                $rec = cidp_recommend_yard_slot($pdo, $cRowInfo, !empty($p['block']) ? (string)$p['block'] : null);
                $block = $rec['block'];
                $bay   = $rec['bay'];
                $row   = $rec['row'];
                $tier  = $rec['tier'];
                $slotReason = $rec['reason'];
            } else {
                $block = strtoupper(trim((string)$p['block']));
                $bay = yard_format_coord($p['bay'] ?? '08');
                $row = yard_format_coord($p['row'] ?? '03');
                $tier = yard_format_coord($p['tier'] ?? '02');
            }

            // Equipment becomes idle/operating without container
            $stmt = $pdo->prepare("UPDATE equipment SET status = 'idle', current_container = NULL, last_block = ?, last_updated = NOW() WHERE equipment_id = ?");
            $stmt->execute([$block, $eq_id]);

            // Container placed in yard slot
            $c_stmt = $pdo->prepare("UPDATE containers SET block = ?, bay = ?, row = ?, tier = ?, status = 'in_yard' WHERE container_number = ?");
            $c_stmt->execute([$block, $bay, $row, $tier, $container_no]);

            // Log event
            $slot_str = "{$block}-{$bay}-{$row}-{$tier}";
            $ev_ins = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, to_block, to_bay, to_row, to_tier, notes, billable_amount) VALUES ('STACKING', ?, ?, ?, ?, ?, ?, ?, 0)");
            $ev_ins->execute([$container_no, $eq_id, $block, $bay, $row, $tier, "Kontainer berhasil ditumpuk di {$slot_str} ({$slotReason})"]);

            $tri_flow_log['physical'] = "Kontainer {$container_no} terpasang di Blok {$block} (Slot: {$slot_str})";
            $tri_flow_log['information'] = "Akurasi slot spasial 100%; Penghitungan Dwell Time (t0) aktif ({$thresholds['free_dwell_days']} hari gratis)";
            $tri_flow_log['financial'] = "Masa bebas penumpukan hari ke-1 dimulai (Argo progresif siap jalan)";
            $decision_summary = [
                'verdict' => $useAutoSlot ? 'AUTO_SLOTTED_OPTIMAL' : 'STACKED_BY_DGPS',
                'rule'    => 'Yard Auto-Slotting Engine (Customs/Cargo/Gravity Weight Tier)',
                'reason'  => $slotReason,
                'next_action' => "Kontainer siap dipantau pada Matriks Yard {$slot_str} & Digital Twin 3D."
            ];
            break;

        // =====================================================================
        // 6. CUSTOMS_CEISA (Webhook CEISA 4.0 Bea Cukai SPPB Clearance)
        // =====================================================================
        case 'CUSTOMS_CEISA':
            $container_no = $p['container_number'] ?? '';
            $status = $p['customs_status'] ?? 'SPPB_CLEARED';
            $sppb_no = $p['sppb_number'] ?? ('SPPB-' . date('Ymd') . '/KPU.01/2026');

            // Cek apakah ada anomali ketidakcocokan dokumen pabean BC 2.3 / HS Code yang belum diselesaikan
            $paCust = $pdo->prepare("SELECT * FROM preadvice WHERE container_number = ? ORDER BY id DESC LIMIT 1");
            $paCust->execute([$container_no]);
            $preCust = $paCust->fetch(PDO::FETCH_ASSOC);
            if ($preCust && ($preCust['anomaly_code'] ?? '') === 'CUSTOMS_DOC_MISMATCH' && (($preCust['resolution_status'] ?? 'NONE') !== 'OVERRIDDEN_APPROVED') && $status === 'SPPB_CLEARED' && empty($p['force_sppb_override'])) {
                $catMeta = cidp_get_anomaly_meta('CUSTOMS_DOC_MISMATCH') ?: [];
                $anomMeta = array_merge($catMeta, [
                    'pre_gate_code'    => $preCust['pre_gate_code'],
                    'anomaly_code'     => 'CUSTOMS_DOC_MISMATCH',
                    'anomaly_title'    => $preCust['anomaly_title'] ?: ($catMeta['anomaly_title'] ?? 'Dokumen BC 2.3 Tidak Sinkron CEISA 4.0'),
                    'anomaly_stage'    => 'CUSTOMS',
                    'anomaly_notes'    => $preCust['anomaly_notes'] ?: ($catMeta['anomaly_notes'] ?? ''),
                    'truck_plate'      => $preCust['truck_plate'],
                    'container_number' => $container_no,
                    'driver_name'      => $preCust['driver_name'],
                    'company'          => $preCust['company'],
                    'owner_company'    => $preCust['owner_company'],
                    'interlock_state'  => 'CUSTOMS_RED_LINE_LOCKED'
                ]);
                throw new IngestRejection(
                    "[INTERLOCK CEISA 4.0 — CUSTOMS_DOC_MISMATCH] {$anomMeta['anomaly_title']}: {$anomMeta['anomaly_notes']}",
                    $anomMeta
                );
            }

            $stmt = $pdo->prepare("UPDATE containers SET customs_status = ? WHERE container_number = ?");
            $stmt->execute([$status, $container_no]);

            // Propagasi ke CFS: SPPB cleared membuka palet & job; status lain menahan
            if ($status === 'SPPB_CLEARED') {
                $pdo->prepare("UPDATE cfs_pallets SET customs_status = 'CLEARED', status = 'IN_RACK' WHERE container_origin = ? AND status IN ('IN_RACK','HOLD')")->execute([$container_no]);
                $pdo->prepare("UPDATE cfs_jobs SET customs_status = 'SPPB_CLEARED', status = IF(status = 'HOLD', 'IN_PROGRESS', status) WHERE container_number = ?")->execute([$container_no]);
                cfs_sync_master_lokasi($pdo);
            }

            // Log event
            $ev_ins = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, notes, billable_amount) VALUES ('CUSTOMS_CEISA', ?, ?, 0)");
            $ev_ins->execute([$container_no, "Status CEISA 4.0 DJBC: {$status} ({$sppb_no})"]);

            $tri_flow_log['physical'] = "Kontainer {$container_no} di Blok Yard ditandai aksen HIJAU pabean";
            $tri_flow_log['information'] = "API DJBC: SPPB {$sppb_no} Cleared Host-to-Host otomatis";
            $tri_flow_log['financial'] = "Kewajiban kepabeanan lunas; Jaminan pabean didepositkan";
            break;

        // =====================================================================
        // 7. PAYMENT_SETTLED (Host-to-Host Pelunasan Bank Mandiri)
        // =====================================================================
        case 'PAYMENT_SETTLED':
            $container_no = $p['container_number'] ?? '';

            // Mark invoices for this container as PAID
            $stmt = $pdo->prepare("UPDATE billing_invoices SET payment_status = 'PAID', paid_at = NOW() WHERE container_number = ? AND payment_status = 'PENDING'");
            $stmt->execute([$container_no]);

            // Log event
            $ev_ins = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, notes, billable_amount) VALUES ('BILLING_PAID', ?, 'Pelunasan tagihan terminal via Bank Mandiri Host-to-Host', 0)");
            $ev_ins->execute([$container_no]);

            $tri_flow_log['physical'] = "Ikon verifikasi lunas muncul pada kontainer {$container_no}";
            $tri_flow_log['information'] = "e-Receipt & SPK Pengambilan Kargo resmi dirilis ke transporter";
            $tri_flow_log['financial'] = "Total piutang lunas 100%; Dana masuk rekening penampung CIDP";
            break;

        // =====================================================================
        // 8. YARD_RELOCATION (Reshuffle / Relokasi slot oleh Reach Stacker/RTG + DGPS RTK)
        // =====================================================================
        case 'YARD_RELOCATION':
            $eq_id = trim($p['equipment_id'] ?? '');
            $tb = strtoupper(trim($p['to_block'] ?? ''));
            $tbayRaw = $p['to_bay'] ?? '';
            $trowRaw = $p['to_row'] ?? '';
            $ttierRaw = $p['to_tier'] ?? '';
            if ($eq_id === '' || $tb === '' || $tbayRaw === '' || $trowRaw === '' || $ttierRaw === '') {
                throw new IngestRejection('Relokasi wajib membawa equipment_id dan slot tujuan (block, bay, row, tier).');
            }
            $tbay = yard_format_coord($tbayRaw);
            $trow = yard_format_coord($trowRaw);
            $ttier = yard_format_coord($ttierRaw);
            $cur = $pdo->prepare("SELECT block, bay, row, tier, status FROM containers WHERE container_number = ?");
            $cur->execute([$container_no = $p['container_number']]);
            $cr = $cur->fetch(PDO::FETCH_ASSOC);
            if ($cr['status'] !== 'in_yard') {
                throw new IngestRejection("Kontainer {$container_no} tidak berada di yard (status {$cr['status']}); relokasi ditolak.");
            }
            $occ = $pdo->prepare("SELECT container_number FROM containers WHERE status='in_yard' AND UPPER(TRIM(COALESCE(block,''))) = ? AND CAST(COALESCE(NULLIF(bay,''),'0') AS UNSIGNED) = ? AND CAST(COALESCE(NULLIF(`row`,''),'0') AS UNSIGNED) = ? AND CAST(COALESCE(NULLIF(tier,''),'0') AS UNSIGNED) = ? AND container_number <> ?");
            $occ->execute([$tb, (int)$tbay, (int)$trow, (int)$ttier, $container_no]);
            if ($o = $occ->fetchColumn()) {
                throw new IngestRejection("Slot tujuan {$tb}-{$tbay}-{$trow}-{$ttier} sudah terisi oleh {$o}.");
            }
            $pdo->prepare("UPDATE containers SET block = ?, bay = ?, row = ?, tier = ? WHERE container_number = ?")->execute([$tb, $tbay, $trow, $ttier, $container_no]);
            $blkCenterMap = [
                'A' => [270.0, 250.0, 'RTG-02'], 'B' => [490.0, 250.0, 'RTG-03'],
                'C' => [270.0, 470.0, 'RTG-04'], 'D' => [490.0, 470.0, 'RTG-05'],
                'E' => [770.0, 470.0, null],     'REEFER' => [770.0, 250.0, null]
            ];
            $bcInfo = $blkCenterMap[$tb] ?? [350.0, 300.0, null];
            $relX = $bcInfo[0] + ((((int)$tbay - 1) % 3) - 1) * 26.0;
            $relY = $bcInfo[1] + ((((int)$trow - 1) % 4) - 1.5) * 16.0;
            $pdo->prepare("UPDATE equipment SET status = 'idle', current_container = NULL, last_block = ?, gps_x = ?, gps_y = ?, last_updated = NOW() WHERE equipment_id = ?")
                ->execute([$tb, $relX, $relY, $eq_id]);
            if (!empty($bcInfo[2]) && $bcInfo[2] !== $eq_id) {
                $pdo->prepare("UPDATE equipment SET status = 'operating', last_block = ?, gps_x = ?, gps_y = ?, last_updated = NOW() WHERE equipment_id = ?")
                    ->execute([$tb, $relX, $bcInfo[1], $bcInfo[2]]);
            }
            $reloc_fee = 200000.0;
            $inv_no = accrue_billing($pdo, $container_no, 'Relokasi / Shifting Yard', 'lolo_amount', $reloc_fee);
            $from = "{$cr['block']}-{$cr['bay']}-{$cr['row']}-{$cr['tier']}";
            $to = "{$tb}-{$tbay}-{$trow}-{$ttier}";
            $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, from_block, from_bay, from_row, from_tier, to_block, to_bay, to_row, to_tier, notes, billable_amount) VALUES ('RELOCATION', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
                ->execute([$container_no, $eq_id, $cr['block'], $cr['bay'], $cr['row'], $cr['tier'], $tb, $tbay, $trow, $ttier, "Relokasi {$from} -> {$to} (" . ($p['reason'] ?? 'reshuffle') . ")", $reloc_fee]);
            $tri_flow_log['physical'] = "{$eq_id} memindahkan {$container_no} dari {$from} ke {$to}";
            $tri_flow_log['information'] = "Slot lama dilepas, slot baru terkunci via DGPS RTK; posisi digital twin diperbarui";
            $tri_flow_log['financial'] = "Biaya relokasi Rp " . number_format($reloc_fee, 0, ',', '.') . " terakrual pada {$inv_no}";
            break;

        // =====================================================================
        // 9. CFS_STRIPPING_START (Kontainer LCL ke Rampa CFS, mulai bongkar)
        // =====================================================================
        case 'CFS_STRIPPING_START':
            $container_no = $p['container_number'];
            $ramp = strtoupper(trim($p['dock_ramp'] ?? ''));
            if ($ramp === '') throw new IngestRejection('Stripping wajib membawa dock_ramp (rampa CFS).');
            $open = $pdo->prepare("SELECT job_number FROM cfs_jobs WHERE container_number = ? AND status IN ('PENDING','IN_PROGRESS','HOLD')");
            $open->execute([$container_no]);
            if ($oj = $open->fetchColumn()) throw new IngestRejection("Kontainer {$container_no} sudah memiliki job CFS aktif ({$oj}).");
            $cc = $pdo->prepare("SELECT customs_status, owner_company FROM containers WHERE container_number = ?");
            $cc->execute([$container_no]);
            $crow = $cc->fetch(PDO::FETCH_ASSOC);
            $job_no = 'CFS-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));
            $pdo->prepare("INSERT INTO cfs_jobs (job_number, job_type, container_number, dock_ramp, shipper_consignee, bl_number, customs_status, status, operator_name, start_time) VALUES (?, 'STRIPPING', ?, ?, ?, ?, ?, 'IN_PROGRESS', ?, NOW())")
                ->execute([$job_no, $container_no, $ramp, $p['consignee'] ?? $crow['owner_company'], $p['bl_number'] ?? null, $crow['customs_status'], $p['operator_name'] ?? null]);
            $pdo->prepare("UPDATE containers SET block = 'CFS', bay = ?, row = NULL, tier = NULL WHERE container_number = ?")->execute([$ramp, $container_no]);
            $rampYMap = ['D1' => 218.0, 'D2' => 262.0, 'D3' => 308.0, 'D4' => 352.0, 'D5' => 396.0];
            $ry = $rampYMap[$ramp] ?? 308.0;
            $pdo->prepare("UPDATE equipment SET status = 'operating', current_container = ?, last_block = 'CFS', gps_x = 148.0, gps_y = ?, last_updated = NOW() WHERE equipment_id = 'FL-03'")
                ->execute([$container_no, $ry]);
            $pdo->prepare("UPDATE equipment SET status = 'operating', current_container = ?, last_block = 'CFS', gps_x = 172.0, gps_y = ?, last_updated = NOW() WHERE equipment_id = 'CDP-TT-01'")
                ->execute([$container_no, $ry]);
            $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, from_block, to_block, notes, billable_amount) VALUES ('CFS_STRIP_START', ?, 'FL-03', 'YARD', 'CFS', ?, 0)")
                ->execute([$container_no, "Mulai stripping di rampa {$ramp} (job {$job_no}, Forklift FL-03 & Shuttle CDP-TT-01)"]);
            $tri_flow_log['physical'] = "Kontainer {$container_no} didorong ke rampa {$ramp}; pintu dibuka, bongkar dimulai";
            $tri_flow_log['information'] = "Job CFS {$job_no} dibuat otomatis dari data kontainer (tanpa input ulang)";
            $tri_flow_log['financial'] = "Argo & tarif CFS akan diakrual saat stripping selesai";
            break;

        // =====================================================================
        // 10. CFS_PALLET_SCAN (Scanner GS1-128 SSCC-18 + timbangan palet 3T)
        // =====================================================================
        case 'CFS_PALLET_SCAN':
            $container_no = $p['container_number'];
            $sscc = preg_replace('/\D/', '', $p['pallet_sscc'] ?? '');
            if (!sscc18_valid($sscc)) throw new IngestRejection("SSCC-18 '{$sscc}' tidak valid (panjang/check digit Modulo 10 salah).");
            $jb = $pdo->prepare("SELECT id, job_number, customs_status, status FROM cfs_jobs WHERE container_number = ? AND status IN ('IN_PROGRESS','HOLD') ORDER BY id DESC LIMIT 1");
            $jb->execute([$container_no]);
            $job = $jb->fetch(PDO::FETCH_ASSOC);
            if (!$job) throw new IngestRejection("Tidak ada job stripping aktif untuk {$container_no}; scan palet ditolak.");
            if ($job['status'] === 'HOLD') throw new IngestRejection("Job {$job['job_number']} sedang HOLD (inspeksi bea cukai); scan palet ditolak.");
            $dp = $pdo->prepare("SELECT COUNT(*) FROM cfs_pallets WHERE pallet_sscc = ?");
            $dp->execute([$sscc]);
            if ($dp->fetchColumn() > 0) throw new IngestRejection("Palet SSCC {$sscc} sudah tercatat (duplikat scan).");
            $kg = (float)($p['gross_weight_kg'] ?? 0);
            if ($kg <= 0) throw new IngestRejection('Berat palet dari timbangan wajib > 0.');
            $cat = in_array(($p['category'] ?? ''), ['machinery','electronics','fmcg','textile','automotive','general'], true) ? $p['category'] : 'general';
            $cust = ($job['customs_status'] === 'SPPB_CLEARED') ? 'CLEARED' : (in_array($job['customs_status'], ['RED_LANE_CHECK','RED_LANE'], true) ? 'RED_LANE' : 'HOLD');
            $allocRack = cfs_allocate_or_validate_rack($pdo, $p['rack_location'] ?? 'AISLE-B-02-L1', true);
            if (!$allocRack['ok']) throw new IngestRejection($allocRack['message'] ?? 'Seluruh slot rak Gudang CFS penuh.');
            $rackLoc = $allocRack['code'];
            $pdo->prepare("INSERT INTO cfs_pallets (job_id, pallet_sscc, container_origin, commodity_name, consignee, category, carton_count, gross_weight_kg, cbm, rack_location, customs_status, status, entry_date) VALUES (?,?,?,?,?,?,?,?,?,?,?, 'IN_RACK', NOW())")
                ->execute([$job['id'], $sscc, $container_no, $p['commodity_name'] ?? null, $p['consignee'] ?? null, $cat, (int)($p['carton_count'] ?? 0), $kg, (float)($p['cbm'] ?? 0), $rackLoc, $cust]);
            $pdo->prepare("UPDATE cfs_jobs SET total_packages = COALESCE(total_packages,0) + ?, total_weight_kg = COALESCE(total_weight_kg,0) + ?, total_cbm = COALESCE(total_cbm,0) + ? WHERE id = ?")
                ->execute([(int)($p['carton_count'] ?? 0), $kg, (float)($p['cbm'] ?? 0), $job['id']]);
            cfs_sync_master_lokasi($pdo);
            $aisleCoords = [
                'AISLE-A' => [75.0, 232.0], 'AISLE-B' => [115.0, 232.0],
                'AISLE-C' => [75.0, 308.0], 'AISLE-D' => [115.0, 308.0],
                'AISLE-E' => [75.0, 384.0], 'AISLE-F' => [115.0, 384.0]
            ];
            $rkPrefix = substr($rackLoc, 0, 7);
            $rkXY = $aisleCoords[$rkPrefix] ?? [92.0, 308.0];
            $shortPal = 'SSCC-' . substr($sscc, -4);
            $pdo->prepare("UPDATE equipment SET status = 'operating', current_container = ?, last_block = 'CFS', gps_x = 136.0, gps_y = 236.0, last_updated = NOW() WHERE equipment_id = 'FL-01'")
                ->execute([$shortPal]);
            $pdo->prepare("UPDATE equipment SET status = 'carrying', current_container = ?, last_block = 'CFS', gps_x = ?, gps_y = ?, last_updated = NOW() WHERE equipment_id = 'FL-04'")
                ->execute([$shortPal, $rkXY[0], $rkXY[1]]);
            $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, notes, billable_amount) VALUES ('CFS_PALLET_IN', ?, 'FL-04', ?, 0)")
                ->execute([$container_no, "Palet SSCC {$sscc} (" . number_format($kg, 0) . " kg) ditimbang FL-01 & disusun FL-04 ke rak {$rackLoc}"]);
            $tri_flow_log['physical'] = "Palet {$sscc} ditimbang (" . number_format($kg, 0) . " kg) dan ditempatkan di rak {$rackLoc}";
            $tri_flow_log['information'] = "Scanner GS1-128 membaca SSCC-18 valid; stok cfs_pallets & job {$job['job_number']} bertambah otomatis";
            $tri_flow_log['financial'] = "Palet tercatat untuk penagihan CFS saat job selesai (status pabean: {$cust})";
            break;

        // =====================================================================
        // 11. CFS_STRIPPING_COMPLETE
        // =====================================================================
        case 'CFS_STRIPPING_COMPLETE':
            $container_no = $p['container_number'];
            $jb = $pdo->prepare("SELECT id, job_number, total_packages FROM cfs_jobs WHERE container_number = ? AND status = 'IN_PROGRESS' ORDER BY id DESC LIMIT 1");
            $jb->execute([$container_no]);
            $job = $jb->fetch(PDO::FETCH_ASSOC);
            if (!$job) throw new IngestRejection("Tidak ada job stripping IN_PROGRESS untuk {$container_no}.");
            $pc = $pdo->prepare("SELECT COUNT(*) FROM cfs_pallets WHERE job_id = ?");
            $pc->execute([$job['id']]);
            $pallets = (int)$pc->fetchColumn();
            if ($pallets === 0) throw new IngestRejection("Job {$job['job_number']} belum memiliki palet ter-scan; tidak dapat diselesaikan.");
            $pdo->prepare("UPDATE cfs_jobs SET status = 'COMPLETED', completed_time = NOW() WHERE id = ?")->execute([$job['id']]);
            $pdo->prepare("UPDATE containers SET cargo_type = 'empty' WHERE container_number = ?")->execute([$container_no]);
            $pdo->exec("UPDATE equipment SET status = 'idle', current_container = NULL, last_updated = NOW() WHERE equipment_id IN ('FL-01','FL-03','FL-04','CDP-TT-01')");
            $cfs_fee = 450000.0 + 25000.0 * $pallets;
            $inv_no = accrue_billing($pdo, $container_no, 'Jasa CFS Stripping & Penumpukan Palet', 'cfs_amount', $cfs_fee);
            $pdo->prepare("INSERT INTO yard_events (event_type, container_number, notes, billable_amount) VALUES ('CFS_STRIP_DONE', ?, ?, ?)")
                ->execute([$container_no, "Stripping selesai ({$pallets} palet, job {$job['job_number']})", $cfs_fee]);
            $tri_flow_log['physical'] = "Kontainer {$container_no} kosong; {$pallets} palet tersusun di rak CFS";
            $tri_flow_log['information'] = "Job {$job['job_number']} COMPLETED; kontainer kosong siap relokasi/return";
            $tri_flow_log['financial'] = "Jasa CFS Rp " . number_format($cfs_fee, 0, ',', '.') . " terakrual pada {$inv_no}";
            break;

        // =====================================================================
        // 12. CFS_PALLET_DISPATCH (Palet keluar rak ke truk pengambil)
        // =====================================================================
        case 'CFS_PALLET_DISPATCH':
            $sscc = preg_replace('/\D/', '', $p['pallet_sscc'] ?? '');
            if (!sscc18_valid($sscc)) throw new IngestRejection("SSCC-18 '{$sscc}' tidak valid.");
            $pq = $pdo->prepare("SELECT id, container_origin, customs_status, status FROM cfs_pallets WHERE pallet_sscc = ?");
            $pq->execute([$sscc]);
            $pal = $pq->fetch(PDO::FETCH_ASSOC);
            if (!$pal) throw new IngestRejection("Palet {$sscc} tidak ditemukan di CFS.");
            if ($pal['status'] !== 'IN_RACK') throw new IngestRejection("Palet {$sscc} berstatus {$pal['status']}; dispatch ditolak.");
            if ($pal['customs_status'] !== 'CLEARED') throw new IngestRejection("Palet {$sscc} belum CLEARED bea cukai ({$pal['customs_status']}); keluar gudang ditolak (interlock).");
            $pdo->prepare("UPDATE cfs_pallets SET status = 'DISPATCHED' WHERE id = ?")->execute([$pal['id']]);
            cfs_sync_master_lokasi($pdo);
            $container_no = $pal['container_origin'];
            $pdo->prepare("UPDATE equipment SET status = 'operating', current_container = ?, last_block = 'CFS', gps_x = 148.0, gps_y = 392.0, last_updated = NOW() WHERE equipment_id = 'FL-04'")
                ->execute(['OUT-' . substr($sscc, -4)]);
            $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, notes, billable_amount) VALUES ('CFS_PALLET_OUT', ?, 'FL-04', ?, 0)")
                ->execute([$container_no, "Palet {$sscc} dikeluarkan FL-04 dari rak CFS ke rampa distribusi"]);
            $tri_flow_log['physical'] = "Palet {$sscc} diangkat forklift keluar rak menuju truk";
            $tri_flow_log['information'] = "Stok CFS berkurang; surat jalan terbit otomatis";
            $tri_flow_log['financial'] = "Tidak ada biaya tambahan (dasar tagihan sudah diakrual saat stripping)";
            break;

        // =====================================================================
        // 13. CUSTOMS_INSPECTION (Gantry X-Ray / pemeriksaan fisik jalur merah)
        // =====================================================================
        case 'CUSTOMS_INSPECTION':
            $container_no = $p['container_number'];
            $itype = strtoupper($p['inspection_type'] ?? 'XRAY_SCAN');
            $result = strtoupper($p['result'] ?? '');
            if (!in_array($result, ['ANOMALY', 'CLEAR'], true)) throw new IngestRejection("Hasil inspeksi harus 'ANOMALY' atau 'CLEAR'.");
            if ($result === 'ANOMALY') {
                $pdo->prepare("UPDATE containers SET customs_status = 'RED_LANE_CHECK', block = 'E' WHERE container_number = ?")->execute([$container_no]);
                $pdo->prepare("UPDATE cfs_jobs SET status = 'HOLD', customs_status = 'RED_LANE_CHECK' WHERE container_number = ? AND status IN ('IN_PROGRESS','PENDING')")->execute([$container_no]);
                $pdo->prepare("UPDATE cfs_pallets SET customs_status = 'RED_LANE', status = 'HOLD' WHERE container_origin = ? AND status = 'IN_RACK'")->execute([$container_no]);
                $pdo->prepare("UPDATE equipment SET status = 'operating', current_container = ?, last_block = 'E', gps_x = 805.0, gps_y = 468.0, last_updated = NOW() WHERE equipment_id = 'FL-02'")->execute([$container_no]);
                $pdo->prepare("UPDATE equipment SET status = 'operating', current_container = ?, last_block = 'E', gps_x = 695.0, gps_y = 470.0, last_updated = NOW() WHERE equipment_id = 'CDP-TT-02'")->execute([$container_no]);
                $note = "Inspeksi {$itype}: ANOMALI - kontainer ditahan (jalur merah Behandle Blok E)";
                $tri_flow_log['physical'] = "Kontainer {$container_no} dialihkan CDP-TT-02 & FL-02 ke area pemeriksaan fisik jalur merah Blok E";
                $tri_flow_log['information'] = "Status pabean RED_LANE_CHECK; job & palet CFS terkait otomatis HOLD";
                $tri_flow_log['financial'] = "Gate-out terkunci; biaya penumpukan tetap berjalan selama HOLD";
            } else {
                $pdo->prepare("UPDATE containers SET customs_status = 'SPPB_PENDING' WHERE container_number = ? AND customs_status IN ('RED_LANE_CHECK','AWAITING_CLEARANCE')")->execute([$container_no]);
                $pdo->prepare("UPDATE cfs_jobs SET status = 'IN_PROGRESS', customs_status = 'SPPB_PENDING' WHERE container_number = ? AND status = 'HOLD'")->execute([$container_no]);
                $pdo->prepare("UPDATE cfs_pallets SET customs_status = 'HOLD', status = 'IN_RACK' WHERE container_origin = ? AND status = 'HOLD'")->execute([$container_no]);
                $pdo->exec("UPDATE equipment SET status = 'idle', current_container = NULL, last_updated = NOW() WHERE equipment_id IN ('FL-02','CDP-TT-02')");
                $note = "Inspeksi {$itype}: BERSIH - menunggu SPPB";
                $tri_flow_log['physical'] = "Pemeriksaan selesai, kontainer {$container_no} dikembalikan ke posisi";
                $tri_flow_log['information'] = "Hasil inspeksi bersih; status SPPB_PENDING (menunggu webhook CEISA)";
                $tri_flow_log['financial'] = "Tidak ada biaya tambahan; rilis menunggu SPPB";
            }
            $pdo->prepare("INSERT INTO yard_events (event_type, container_number, notes, billable_amount) VALUES ('CUSTOMS_INSPECTION', ?, ?, 0)")
                ->execute([$container_no, $note . (empty($p['notes']) ? '' : ' - ' . $p['notes'])]);
            cfs_sync_master_lokasi($pdo);
            break;

        // =====================================================================
        // 14. REEFER_PLUGIN (Colok soket reefer 380V - mulai pemantauan suhu)
        // =====================================================================
        case 'REEFER_PLUGIN':
        case 'REEFER_TELEMETRY':
            $container_no = $p['container_number'];
            $rc = $pdo->prepare("SELECT cargo_type, status FROM containers WHERE container_number = ?");
            $rc->execute([$container_no]);
            $rrow = $rc->fetch(PDO::FETCH_ASSOC);
            if ($rrow['cargo_type'] !== 'reefer') throw new IngestRejection("Kontainer {$container_no} bukan reefer; telemetri ditolak.");
            if ($rrow['status'] !== 'in_yard') throw new IngestRejection("Reefer {$container_no} belum ditempatkan di yard.");
            $socket = trim($p['socket_id'] ?? '');
            $set_t = isset($p['set_temp_c']) ? (float)$p['set_temp_c'] : null;
            if ($event_type === 'REEFER_PLUGIN') {
                if ($socket === '' || $set_t === null) throw new IngestRejection('Plug-in reefer wajib membawa socket_id dan set_temp_c.');
                $pdo->prepare("INSERT INTO reefer_telemetry (container_number, socket_id, set_temp_c, supply_temp_c, power_kw, status) VALUES (?,?,?,?,?, 'NORMAL')")
                    ->execute([$container_no, $socket, $set_t, $set_t, 0]);
                $pdo->prepare("UPDATE equipment SET status = 'operating', current_container = NULL, last_block = 'REEFER', gps_x = 755.0, gps_y = 225.0, last_updated = NOW() WHERE equipment_id = 'RS-03'")->execute();
                $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, notes, billable_amount) VALUES ('REEFER_PLUGIN', ?, 'RS-03', ?, 0)")
                    ->execute([$container_no, "Reefer terhubung ke soket {$socket}, set point {$set_t} C"]);
                $tri_flow_log['physical'] = "Kabel daya 380V reefer dicolok ke soket {$socket}";
                $tri_flow_log['information'] = "Pemantauan suhu aktif; set point {$set_t} C tercatat";
                $tri_flow_log['financial'] = "Biaya listrik reefer mulai dihitung per kWh";
                break;
            }
            // REEFER_TELEMETRY
            $last = $pdo->prepare("SELECT socket_id, set_temp_c FROM reefer_telemetry WHERE container_number = ? ORDER BY id ASC LIMIT 1");
            $last->execute([$container_no]);
            $first = $last->fetch(PDO::FETCH_ASSOC);
            if (!$first) throw new IngestRejection("Reefer {$container_no} belum melakukan plug-in; telemetri ditolak.");
            if (!isset($p['supply_temp_c']) || !isset($p['power_kw'])) throw new IngestRejection('Telemetri wajib membawa supply_temp_c dan power_kw.');
            $set_t = $set_t ?? (float)$first['set_temp_c'];
            $supply = (float)$p['supply_temp_c'];
            $kw = (float)$p['power_kw'];
            $interval_h = ((float)($p['interval_minutes'] ?? 60)) / 60.0;
            $maxDevC = max(0.5, (float)$thresholds['reefer_max_deviation_c']);
            $alarm = abs($supply - $set_t) > $maxDevC;
            $pdo->prepare("INSERT INTO reefer_telemetry (container_number, socket_id, set_temp_c, supply_temp_c, power_kw, status) VALUES (?,?,?,?,?,?)")
                ->execute([$container_no, $first['socket_id'], $set_t, $supply, $kw, $alarm ? 'ALARM' : 'NORMAL']);
            $kwh_fee = round($kw * $interval_h * 2500.0, 2);
            $inv_no = accrue_billing($pdo, $container_no, 'Listrik & Monitoring Reefer', 'reefer_power_amount', $kwh_fee);
            if ($alarm) {
                $pdo->prepare("INSERT INTO yard_events (event_type, container_number, notes, billable_amount) VALUES ('REEFER_ALARM', ?, ?, 0)")
                    ->execute([$container_no, "ALARM suhu: suplai {$supply} C vs set point {$set_t} C (soket {$first['socket_id']}, deviasi > {$maxDevC} C)"]);
            }
            $tri_flow_log['physical'] = "Soket {$first['socket_id']}: suplai {$supply} C, daya {$kw} kW" . ($alarm ? ' - ALARM penyimpangan suhu' : '');
            $tri_flow_log['information'] = "Sampel telemetri tersimpan (set point {$set_t} C); status " . ($alarm ? 'ALARM' : 'NORMAL');
            $tri_flow_log['financial'] = "Listrik reefer Rp " . number_format($kwh_fee, 0, ',', '.') . " terakrual pada {$inv_no}";
            $decision_summary = [
                'verdict' => $alarm ? 'ALARM_INSPECT_COMPRESSOR_ATS' : 'TEMP_WITHIN_SPEC',
                'rule'    => "LoRaWAN SHT40 Threshold (Maks Deviasi ±{$maxDevC}°C)",
                'reason'  => "Selisih aktual " . round(abs($supply - $set_t), 1) . "°C terhadap setpoint {$set_t}°C.",
                'next_action' => $alarm ? "Kirim teknisi pendingin ke soket {$first['socket_id']} & siapkan backup ATS Genset!" : "Lanjutkan logging HACCP periodik."
            ];
            break;

        // =====================================================================
        // 15. RAIL_LOADING (Muat ke gerbong KA Pasoso - Frauscher axle counter)
        // =====================================================================
        case 'RAIL_LOADING':
            $container_no = $p['container_number'];
            $train_code = trim($p['train_code'] ?? '');
            if ($train_code === '') throw new IngestRejection('Rail loading wajib membawa train_code.');
            $cs = $pdo->prepare("SELECT customs_status, status FROM containers WHERE container_number = ?");
            $cs->execute([$container_no]);
            $crow = $cs->fetch(PDO::FETCH_ASSOC);
            if ($crow['status'] !== 'in_yard') throw new IngestRejection("Kontainer {$container_no} tidak berada di yard; tidak dapat dimuat ke kereta.");
            if ($crow['customs_status'] !== 'SPPB_CLEARED') throw new IngestRejection("Kontainer {$container_no} belum SPPB_CLEARED ({$crow['customs_status']}); muat ke kereta ditolak.");
            $tq = $pdo->prepare("SELECT id, total_wagons FROM trains WHERE train_code = ?");
            $tq->execute([$train_code]);
            $tr = $tq->fetch(PDO::FETCH_ASSOC);
            if (!$tr) {
                $pdo->prepare("INSERT INTO trains (train_code, origin, destination, total_wagons, loaded_wagons, status, arrival_time) VALUES (?, 'Cikarang Dry Port', ?, ?, 0, 'loading', NOW())")
                    ->execute([$train_code, $p['destination'] ?? 'Pelabuhan', (int)($p['total_wagons'] ?? 20)]);
                $tr = ['id' => $pdo->lastInsertId()];
            }
            $pdo->prepare("UPDATE trains SET loaded_wagons = loaded_wagons + 1, status = 'loading' WHERE id = ?")->execute([$tr['id']]);
            $pdo->prepare("UPDATE containers SET status = 'on_rail', block = 'RAIL', bay = ?, row = NULL, tier = NULL WHERE container_number = ?")->execute([$p['wagon_no'] ?? null, $container_no]);
            $rtgX = 480.0 + ((int)crc32($container_no) % 5) * 45.0;
            $pdo->prepare("UPDATE equipment SET status = 'operating', current_container = NULL, last_block = 'RAIL', gps_x = ?, gps_y = 625.0, last_updated = NOW() WHERE equipment_id = 'RTG-01'")->execute([$rtgX]);
            $pdo->prepare("UPDATE equipment SET status = 'operating', current_container = NULL, last_block = 'RAIL', gps_x = ?, gps_y = 585.0, last_updated = NOW() WHERE equipment_id = 'CDP-TT-01'")->execute([$rtgX - 25.0]);
            $inv_no = accrue_billing($pdo, $container_no, 'Lift-On Gerbong KA Intermodal', 'lolo_amount', 325000.0);
            $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, from_block, to_block, notes, billable_amount) VALUES ('RAIL_LOADING', ?, 'RTG-01', 'YARD', 'RAIL', ?, 325000)")
                ->execute([$container_no, "Dimuat oleh RTG-01 & CDP-TT-01 ke kereta {$train_code}, gerbong " . ($p['wagon_no'] ?? '-')]);
            $tri_flow_log['physical'] = "Kontainer {$container_no} dimuat ke gerbong " . ($p['wagon_no'] ?? '-') . " kereta {$train_code}";
            $tri_flow_log['information'] = "Manifest kereta {$train_code} diperbarui otomatis; slot yard dilepas";
            $tri_flow_log['financial'] = "Lift-on KA Rp 325.000 terakrual pada {$inv_no}";
            break;

        // =====================================================================
        // 16. TRUCK_GATE_OUT (Interlock: SPPB cleared + tagihan lunas)
        // =====================================================================
        case 'TRUCK_GATE_OUT':
            $container_no = $p['container_number'];
            $gs = $pdo->prepare("SELECT customs_status, status, owner_company, cargo_type, block FROM containers WHERE container_number = ?");
            $gs->execute([$container_no]);
            $crow = $gs->fetch(PDO::FETCH_ASSOC);
            $pend = $pdo->prepare("SELECT COUNT(*), COALESCE(SUM(total_amount),0) FROM billing_invoices WHERE container_number = ? AND payment_status <> 'PAID'");
            $pend->execute([$container_no]);
            [$unpaidCnt, $unpaidSum] = $pend->fetch(PDO::FETCH_NUM);

            if ($crow['customs_status'] !== 'SPPB_CLEARED' || (int)$unpaidCnt > 0) {
                $catMeta = cidp_get_anomaly_meta('GATE_OUT_INTERLOCK_BLOCK') ?: [];
                $paGo = $pdo->prepare("SELECT pre_gate_code, truck_plate, driver_name, company FROM preadvice WHERE container_number = ? ORDER BY id DESC LIMIT 1");
                $paGo->execute([$container_no]);
                $preGo = $paGo->fetch(PDO::FETCH_ASSOC) ?: [];
                $reasons = [];
                if ($crow['customs_status'] !== 'SPPB_CLEARED') $reasons[] = "status pabean masih {$crow['customs_status']} (belum SPPB_CLEARED)";
                if ((int)$unpaidCnt > 0) $reasons[] = "terdapat {$unpaidCnt} faktur belum lunas senilai Rp " . number_format((float)$unpaidSum, 0, ',', '.');
                $reasonStr = implode(' & ', $reasons);

                $anomMeta = array_merge($catMeta, [
                    'pre_gate_code'    => $preGo['pre_gate_code'] ?? null,
                    'anomaly_code'     => 'GATE_OUT_INTERLOCK_BLOCK',
                    'anomaly_title'    => 'Interlock Gate-Out Terkunci: ' . ($crow['customs_status'] !== 'SPPB_CLEARED' ? 'SPPB Belum Rilis' : 'Faktur Belum Lunas'),
                    'anomaly_stage'    => 'GATE_OUT',
                    'anomaly_notes'    => "Palang Gate-Out menolak terbuka untuk {$container_no} karena {$reasonStr}.",
                    'truck_plate'      => $preGo['truck_plate'] ?? ($p['truck_plate'] ?? ''),
                    'container_number' => $container_no,
                    'driver_name'      => $preGo['driver_name'] ?? '',
                    'company'          => $preGo['company'] ?? '',
                    'owner_company'    => $crow['owner_company'] ?? '',
                    'unpaid_invoices'  => (int)$unpaidCnt,
                    'unpaid_amount_idr'=> (float)$unpaidSum,
                    'customs_status'   => $crow['customs_status'],
                    'interlock_state'  => 'GATE_OUT_BARRIER_LOCKED_RED'
                ]);
                throw new IngestRejection("[INTERLOCK GATE-OUT] Gate-out ditolak untuk {$container_no}: {$reasonStr}.", $anomMeta);
            }

            $gate_out_lane = cidp_pick_gate_lane($pdo, 'OUT', [
                'lane'       => $p['gate_lane'] ?? $p['lane_id'] ?? $p['lane'] ?? null,
                'cargo_type' => $crow['cargo_type'] ?? 'dry',
                'block'      => $crow['block'] ?? '',
                'job_type'   => 'pick_up',
            ]);

            $pdo->prepare("UPDATE containers SET status = 'gate_out', block = 'OUTSIDE', bay = NULL, row = NULL, tier = NULL, gate_out_time = NOW() WHERE container_number = ?")->execute([$container_no]);
            $pdo->prepare("UPDATE trucks SET status = 'gate_out', gate_out_lane = ?, gate_out_time = NOW() WHERE container_number = ? AND status <> 'gate_out'")->execute([$gate_out_lane, $container_no]);
            $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, from_block, to_block, notes, billable_amount) VALUES ('GATE_OUT', ?, ?, 'YARD', 'OUTSIDE', ?, 0)")
                ->execute([$container_no, $gate_out_lane, "[{$gate_out_lane}] Gate-out terotorisasi: SPPB cleared & tagihan lunas"]);
            $tri_flow_log['physical'] = "Palang {$gate_out_lane} terbuka; truk dan kontainer {$container_no} meninggalkan dry port";
            $tri_flow_log['information'] = "Interlock lolos: SPPB_CLEARED dan semua tagihan PAID; e-Gate Pass tercatat";
            $tri_flow_log['financial'] = "Siklus tagihan kontainer ditutup";
            break;
        // =====================================================================
        // 17. RAIL_AXLE_COUNTER (Sensor Gandar Induktif Frauscher RSR180 SIL 4)
        // =====================================================================
        case 'RAIL_AXLE_COUNTER':
            $train_code = trim($p['train_code'] ?? 'KA 2518');
            $track_id = trim($p['track_id'] ?? 'TRACK-01');
            $expectedAxles = max(4, (int)($p['expected_axles'] ?? $thresholds['rail_expected_axles']));
            $detectedAxles = (int)($p['detected_axles'] ?? $expectedAxles);
            $speedKmh = (float)($p['speed_kmh'] ?? 12.4);

            if ($detectedAxles !== $expectedAxles) {
                throw new IngestRejection(
                    "[INTERLOCK SIL 4 — RAIL_AXLE_MISMATCH] Sensor Frauscher di {$track_id} menghitung {$detectedAxles} gandar vs manifes {$expectedAxles} gandar ({$train_code}). Penguncian sinyal rel diaktifkan!",
                    [
                        'anomaly_code'    => 'RAIL_AXLE_MISMATCH',
                        'anomaly_title'   => "Selisih Jumlah Gandar Rangkaian KA ({$detectedAxles}/{$expectedAxles})",
                        'anomaly_stage'   => 'RAIL_SIDING',
                        'interlock_state' => 'RAIL_SIGNAL_LOCKED_RED'
                    ]
                );
            }

            $pdo->prepare("INSERT INTO yard_events (event_type, equipment_id, to_block, notes, billable_amount) VALUES ('RAIL_AXLE_COUNTER', 'FRAUSCHER-RSR180', 'RAIL', ?, 0)")
                ->execute(["[{$track_id}] Sensor Frauscher SIL-4 memverifikasi {$detectedAxles}/{$expectedAxles} gandar {$train_code} pada {$speedKmh} km/jam (0 Discrepancy)."]);
            $tri_flow_log['physical'] = "Rangkaian {$train_code} melintasi {$track_id} dengan {$detectedAxles} as roda utuh";
            $tri_flow_log['information'] = "Verifikasi Frauscher RSR180 SIL-4 PASS (Zero Discrepancy Rule)";
            $tri_flow_log['financial'] = "Otorisasi bongkar/muat RMGC & RS dibuka";
            $decision_summary = [
                'verdict' => 'SIL4_INTERLOCK_RELEASED',
                'rule'    => "Frauscher Axle Integrity Check (Target {$expectedAxles} as roda)",
                'reason'  => "Terdeteksi tepat {$detectedAxles} gandar pada kecepatan langsir {$speedKmh} km/jam.",
                'next_action' => "Izinkan RMGC / Reach Stacker memulai bongkar/muat di {$track_id}."
            ];
            break;

        // =====================================================================
        // 18. RAIL_UNLOADING (Bongkar Kontainer dari Gerbong KA ke Buffer Yard)
        // =====================================================================
        case 'RAIL_UNLOADING':
            $container_no = $p['container_number'];
            $train_code = trim($p['train_code'] ?? 'KA 2518');
            $eq_id = trim($p['equipment_id'] ?? 'RTG-01');
            $cInf = $pdo->prepare("SELECT * FROM containers WHERE container_number = ? LIMIT 1");
            $cInf->execute([$container_no]);
            $cData = $cInf->fetch(PDO::FETCH_ASSOC) ?: ['container_number' => $container_no];
            $recSlot = cidp_recommend_yard_slot($pdo, $cData, !empty($p['to_block']) ? strtoupper((string)$p['to_block']) : 'D');

            $pdo->prepare("UPDATE containers SET status = 'in_yard', block = ?, bay = ?, row = ?, tier = ?, gate_in_time = COALESCE(gate_in_time, NOW()) WHERE container_number = ?")
                ->execute([$recSlot['block'], $recSlot['bay'], $recSlot['row'], $recSlot['tier'], $container_no]);
            $pdo->prepare("UPDATE trains SET loaded_wagons = GREATEST(0, loaded_wagons - 1) WHERE train_code = ? OR train_code LIKE CONCAT(?, ' %')")->execute([$train_code, $train_code]);
            $inv_no = accrue_billing($pdo, $container_no, "Lift-Off Bongkar KA ({$train_code})", 'lolo_amount', 325000.0);
            $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, from_block, to_block, to_bay, to_row, to_tier, notes, billable_amount) VALUES ('RAIL_UNLOADING', ?, ?, 'RAIL', ?, ?, ?, ?, ?, 325000)")
                ->execute([$container_no, $eq_id, $recSlot['block'], $recSlot['bay'], $recSlot['row'], $recSlot['tier'], "Bongkar KA {$train_code} ke {$recSlot['code']} ({$recSlot['reason']})"]);

            $tri_flow_log['physical'] = "Kontainer {$container_no} dibongkar {$eq_id} dari {$train_code} ke {$recSlot['code']}";
            $tri_flow_log['information'] = "Slot gerbong KA terbebas; posisi buffer yard {$recSlot['code']} aktif";
            $tri_flow_log['financial'] = "Lift-off intermodal Rp 325.000 terakrual pada {$inv_no}";
            $decision_summary = [
                'verdict' => 'DISCHARGED_TO_BUFFER_YARD',
                'rule'    => 'Rail-to-Yard Auto-Slotting + Intermodal Lo-Lo Trigger',
                'reason'  => $recSlot['reason'],
                'next_action' => "Siapkan armada trailer/kliring untuk distribusi keluar."
            ];
            break;

        // =====================================================================
        // 19. XRAY_SCAN_RESULT (Hi-Co Gantry X-Ray 6 MeV Zona 5 Bea Cukai)
        // =====================================================================
        case 'XRAY_SCAN_RESULT':
            $container_no = $p['container_number'];
            $scanMode = strtoupper(trim((string)($p['scan_result'] ?? ($p['result'] ?? 'CLEAR'))));
            $isAnomaly = in_array($scanMode, ['ANOMALY', 'SUSPICIOUS', 'CONTRABAND'], true);

            if ($isAnomaly) {
                $pdo->prepare("UPDATE containers SET customs_status = 'RED_LANE_CHECK', block = 'E' WHERE container_number = ?")->execute([$container_no]);
                $note = "Gantry X-Ray 6 MeV mendeteksi anomali densitas kargo -> Dialihkan ke Jalur Merah / Behandle Blok E.";
                $tri_flow_log['physical'] = "Kontainer {$container_no} ditahan di kanopi Behandle Zona 5 / Blok E";
                $tri_flow_log['information'] = "Status kepabeanan berubah otomatis menjadi RED_LANE_CHECK";
                $tri_flow_log['financial'] = "Interlock gate-out aktif selama pemeriksaan fisik SPJM";
                $decision_summary = [
                    'verdict' => 'HOLD_PHYSICAL_BEHANDLE',
                    'rule'    => 'Hi-Co X-Ray 6 MeV Density Anomaly Interlock',
                    'reason'  => "Citra radiografi menunjukkan muatan tidak sesuai manifest ({$scanMode}).",
                    'next_action' => "Laksanakan stripping pemeriksaan fisik & input LHP PFPB."
                ];
            } else {
                $pdo->prepare("UPDATE containers SET customs_status = 'SPPB_CLEARED' WHERE container_number = ?")->execute([$container_no]);
                $note = "Gantry X-Ray 6 MeV bersih (Homogenous & Match) -> Rilis SPPB_CLEARED.";
                $tri_flow_log['physical'] = "Kontainer {$container_no} lolos pemindaian X-Ray Zona 5";
                $tri_flow_log['information'] = "Verifikasi citra bersih; status menjadi SPPB_CLEARED";
                $tri_flow_log['financial'] = "Siap pelunasan & pelepasan Gate-Out";
                $decision_summary = [
                    'verdict' => 'XRAY_CLEAR_SPPB_RELEASE',
                    'rule'    => 'Hi-Co X-Ray Non-Intrusive Inspection Clear',
                    'reason'  => "Densitas citra radiografi sesuai manifest pemberitahuan.",
                    'next_action' => "Kontainer dapat dirilis keluar atau dimuat ke KA Outbound."
                ];
            }
            $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, notes, billable_amount) VALUES ('XRAY_SCAN_6MEV', ?, 'XRAY-6MEV-Z5', ?, 0)")
                ->execute([$container_no, $note]);
            break;

        // =====================================================================
        // 20. ESEAL_TELEMETRY (Smart GPS E-Seal Jointech JT701)
        // =====================================================================
        case 'ESEAL_TELEMETRY':
            $container_no = $p['container_number'];
            $sealId = trim((string)($p['seal_id'] ?? 'JT701-ID-8821'));
            $wireStatus = strtoupper(trim((string)($p['wire_status'] ?? 'INTACT')));
            $geofenceOk = isset($p['geofence_ok']) ? (bool)$p['geofence_ok'] : true;
            $isBreach = ($wireStatus === 'BROKEN_TAMPERED' || !$geofenceOk);

            if ($isBreach) {
                $pdo->prepare("UPDATE containers SET customs_status = 'RED_LANE_CHECK', seal_number = ? WHERE container_number = ?")
                    ->execute(["TAMPER-{$sealId}", $container_no]);
                $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, notes, billable_amount) VALUES ('ESEAL_TAMPER_ALARM', ?, ?, ?, 0)")
                    ->execute([$container_no, $sealId, "ALARM E-SEAL {$sealId}: wire={$wireStatus}, geofence=" . ($geofenceOk ? 'OK' : 'DEVIATION') . " -> Auto Lock RED_LANE_CHECK!"]);
                $tri_flow_log['physical'] = "Segel elektronik {$sealId} pada {$container_no} memicu alarm tampering koridor";
                $tri_flow_log['information'] = "Status pabean dikunci ke RED_LANE_CHECK & notifikasi Bea Cukai terbit";
                $tri_flow_log['financial'] = "Rilis Gate-Out diblokir sampai inspeksi LHP selesai";
                $decision_summary = [
                    'verdict' => 'SECURITY_LOCK_HOLD',
                    'rule'    => 'PER-09/BC/2020 PLP E-Seal Wire & Geofence Integrity',
                    'reason'  => "Terdeteksi anomali segel ({$wireStatus}) / deviasi rute GPS.",
                    'next_action' => "Arahkan ke Bay Behandle untuk pemeriksaan fisik Bea Cukai."
                ];
            } else {
                $actionNote = ($wireStatus === 'UNLOCKED')
                    ? "Remote Unlock sah dalam geofence CIDP (SPPB verified)."
                    : "Ping GPS koridor aman; kawat segel {$sealId} utuh (INTACT).";
                $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, notes, billable_amount) VALUES ('ESEAL_OK', ?, ?, ?, 0)")
                    ->execute([$container_no, $sealId, $actionNote]);
                $tri_flow_log['physical'] = "Kontainer {$container_no} ({$sealId}) terverifikasi di geofence CIDP";
                $tri_flow_log['information'] = $actionNote;
                $tri_flow_log['financial'] = "Kepatuhan transit PLP terjaga (tanpa penalti pelanggaran segel)";
                $decision_summary = [
                    'verdict' => 'ESEAL_GEOFENCE_VERIFIED',
                    'rule'    => 'Jointech JT701 Tamper-Proof Telemetry Check',
                    'reason'  => $actionNote,
                    'next_action' => "Izinkan proses bongkar / penumpukan reguler."
                ];
            }
            break;

        // =====================================================================
        // 21. HANDHELD_AIDC_SCAN (Scanner Optik SSCC-18, GS1-128 & ISO 6346)
        // =====================================================================
        case 'HANDHELD_AIDC_SCAN':
            $code = strtoupper(trim((string)($p['code'] ?? '')));
            if ($code === '') throw new IngestRejection('Payload HANDHELD_AIDC_SCAN wajib membawa field code.');
            $digitsOnly = preg_replace('/\D/', '', $code);
            $matchedType = 'UNKNOWN';
            $matchedRef = $code;
            $verdict = 'UNREGISTERED_CODE';
            $reason = "Kode {$code} terbaca scanner optik namun belum cocok dengan record YMS/WMS.";
            $nextAction = 'Periksa atau daftarkan Pre-Advice terlebih dahulu.';

            $palStmt = $pdo->prepare("SELECT id, pallet_sscc, container_origin, rack_location, customs_status, status FROM cfs_pallets WHERE pallet_sscc = ? LIMIT 1");
            $palStmt->execute([$digitsOnly !== '' ? $digitsOnly : $code]);
            $pal = $palStmt->fetch(PDO::FETCH_ASSOC);

            if ($pal) {
                $matchedType = 'GS1_SSCC18_PALLET';
                $matchedRef = $pal['container_origin'];
                $verdict = 'PALLET_VERIFIED_IN_WMS';
                $reason = "SSCC-18 cocok di Gudang CFS ({$pal['rack_location']} • Status: {$pal['status']} • Pabean: {$pal['customs_status']}).";
                $nextAction = $pal['customs_status'] === 'CLEARED' ? 'Siap dispatch ke truk.' : 'Tunggu kliring Bea Cukai sebelum dispatch.';
            } else {
                $boxStmt = $pdo->prepare("SELECT id, container_number, status, block, bay, row, tier, customs_status FROM containers WHERE container_number = ? OR seal_number = ? LIMIT 1");
                $boxStmt->execute([$code, $code]);
                $box = $boxStmt->fetch(PDO::FETCH_ASSOC);
                if ($box) {
                    $matchedType = 'ISO6346_CONTAINER';
                    $matchedRef = $box['container_number'];
                    $verdict = ($box['status'] === 'expected') ? 'EXPECTED_PRE_ADVICE_MATCH' : 'CONTAINER_VERIFIED_IN_DB';
                    $pos = trim("{$box['block']}-{$box['bay']}-{$box['row']}-{$box['tier']}", '-');
                    $reason = "Kontainer terverifikasi (Status: {$box['status']}, Lokasi: {$pos}, Pabean: {$box['customs_status']}).";
                    $nextAction = ($box['status'] === 'expected') ? 'Eksekusi Gate-In ANPR/VGM.' : 'Lanjutkan tracking ke Digital Twin / Yard.';
                }
            }

            $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, notes, billable_amount) VALUES ('AIDC_SCAN_AUDIT', ?, 'HANDHELD-AIDC', ?, 0)")
                ->execute([$matchedType === 'UNKNOWN' ? null : $matchedRef, "Scanner AIDC [{$matchedType}]: {$code} -> {$verdict} ({$reason})"]);
            $tri_flow_log['physical'] = "Perangkat scanner optik memindai {$code}";
            $tri_flow_log['information'] = "{$verdict}: {$reason}";
            $tri_flow_log['financial'] = "Audit trail kualitas identifikasi AIDC tercatat";
            $decision_summary = [
                'verdict' => $verdict,
                'rule'    => "AIDC GS1/ISO 6346 Optical Cross-Check ({$matchedType})",
                'reason'  => $reason,
                'next_action' => $nextAction
            ];
            break;

        default:
            throw new Exception("Unhandled event type: {$event_type}");
    }

    $pdo->commit();

    // 4. Update Atomic Cache & Read New State
    $new_state = get_live_twin_state($pdo, true);

    $response = [
        'success' => true,
        'event_id' => $event_id,
        'event_type' => $event_type,
        'source_device' => $source_device,
        'oauth_verified' => $oauth_verified,
        'timestamp' => date('c'),
        'tri_flow' => $tri_flow_log,
        'decision_summary' => $decision_summary ?: [
            'verdict' => 'EVENT_PROCESSED',
            'rule' => "Standard Event Handler ({$event_type})",
            'reason' => $tri_flow_log['information'] ?: 'Event valid dan tersinkron ke basis data YMS.',
            'next_action' => 'Pantau status terbaru di Control Tower & Digital Twin.'
        ],
        'thresholds_applied' => $thresholds,
        'raw_payload_echo' => $p,
        'state_summary' => [
            'containers_in_yard' => $new_state['physical']['containers_in_yard'],
            'total_revenue_accrued_idr' => $new_state['financial']['total_revenue_accrued_idr'],
            'sppb_cleared' => $new_state['information']['customs']['sppb_cleared'],
            'active_inbound_lanes' => $new_state['physical']['gate']['active_inbound_lanes'] ?? [],
            'active_outbound_lanes' => $new_state['physical']['gate']['active_outbound_lanes'] ?? []
        ]
    ];

    // Catat event_id sebagai sudah diproses (idempotency)
    $pdo->prepare("INSERT IGNORE INTO processed_events (event_id, event_type, response_json) VALUES (?,?,?)")
        ->execute([$event_id, $event_type, json_encode($response, JSON_UNESCAPED_SLASHES)]);

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Error dipisahkan ke Dead Letter Queue; alur event sehat lain tidak terganggu
    $is_oauth_err = ($e instanceof OAuthSecurityException);
    $rejected = ($e instanceof IngestRejection) || $is_oauth_err;
    $anomMeta = ($e instanceof IngestRejection && !empty($e->anomalyMeta)) ? $e->anomalyMeta : [];
    $dlq_reason = $is_oauth_err ? ("[OAUTH_SECURITY:{$e->errorCode}] " . $e->getMessage()) : $e->getMessage();

    // Catat status HOLD_ANOMALY di preadvice & log intersepsi di yard_events agar terlihat real-time di Digital Twin & Dashboard
    try {
        if (!empty($anomMeta['pre_gate_code'])) {
            $pdo->prepare("UPDATE preadvice SET status = IF(status = 'SCHEDULED', 'HOLD_ANOMALY', status), resolution_status = 'HOLD_AT_GATE' WHERE pre_gate_code = ?")
                ->execute([$anomMeta['pre_gate_code']]);
        }
        if (($anomMeta['anomaly_code'] ?? '') === 'CUSTOMS_DOC_MISMATCH' && !empty($anomMeta['container_number'])) {
            $pdo->prepare("UPDATE containers SET customs_status = 'RED_LANE_CHECK', block = 'E' WHERE container_number = ?")
                ->execute([$anomMeta['container_number']]);
        }
        $evCno = $anomMeta['container_number'] ?? ($p['container_number'] ?? null);
        if ($evCno) {
            $pdo->prepare("INSERT INTO yard_events (event_type, container_number, from_block, to_block, notes, billable_amount) VALUES ('ANOMALY_HOLD', ?, 'GATE', 'HOLD', ?, 0)")
                ->execute([$evCno, mb_substr($dlq_reason, 0, 240)]);
        }
        get_live_twin_state($pdo, true);
    } catch (Throwable $ignore) {}

    $dlq_id = dlq_push($pdo, $event_id, $event_type, $source_device, $dlq_reason, $data, 'QUARANTINED', $anomMeta);
    http_response_code($is_oauth_err ? $e->httpCode : ($rejected ? 422 : 500));
    echo json_encode([
        'success'     => false,
        'event_id'    => $event_id,
        'event_type'  => $event_type,
        'dlq_id'      => $dlq_id,
        'oauth_error' => $is_oauth_err ? $e->errorCode : null,
        'error'       => $e->getMessage(),
        'routed_to'   => 'DEAD_LETTER_QUEUE',
        'anomaly'     => !empty($anomMeta) ? $anomMeta : null,
        'tri_flow'    => [
            'physical'    => !empty($anomMeta['interlock_state'])
                ? "INTERLOCK AKTIF ({$anomMeta['interlock_state']}): Palang / Alat Berat dikunci otomatis; truk diarahkan ke Holding Bay."
                : 'Akses fisik diblokir oleh Interlock Gate / Yard.',
            'information' => $e->getMessage(),
            'financial'   => 'Transaksi ditahan di Antrean Resolusi Masalah (DLQ); pilih SOP Tolak (Turn-Back) atau Override/Koreksi.'
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}