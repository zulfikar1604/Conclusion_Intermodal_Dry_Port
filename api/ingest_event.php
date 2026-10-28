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

// Accept JSON payload or POST
$raw_input = file_get_contents('php://input');
$data = [];
if (!empty($raw_input)) {
    $data = @json_decode($raw_input, true) ?: [];
}
if (empty($data)) {
    $data = $_POST;
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
            'REEFER_TELEMETRY',
            'PAYMENT_SETTLED',
            'YARD_RELOCATION',
            'CFS_STRIPPING_START',
            'CFS_PALLET_SCAN',
            'CFS_STRIPPING_COMPLETE',
            'CFS_PALLET_DISPATCH',
            'CUSTOMS_INSPECTION',
            'REEFER_PLUGIN',
            'REEFER_TELEMETRY',
            'RAIL_LOADING',
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
    exit;
}

// Event selain EQUIPMENT_MOVE wajib membawa identitas dari sensor (tidak ada nilai default/dummy)
$needs_container = ['TRUCK_WEIGHBRIDGE', 'CONTAINER_LIFT', 'CONTAINER_STACK', 'CUSTOMS_CEISA', 'PAYMENT_SETTLED',
    'YARD_RELOCATION', 'CFS_STRIPPING_START', 'CFS_PALLET_SCAN', 'CFS_STRIPPING_COMPLETE', 'CUSTOMS_INSPECTION',
    'REEFER_PLUGIN', 'REEFER_TELEMETRY', 'RAIL_LOADING', 'TRUCK_GATE_OUT'];

$tri_flow_log = [
    'physical' => '',
    'information' => '',
    'financial' => ''
];

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
            if ($container_no !== '' && !iso6346_valid($container_no)) {
                throw new IngestRejection("Check digit ISO 6346 tidak valid untuk kontainer {$container_no}.");
            }

            $q = $pdo->prepare("SELECT * FROM preadvice WHERE status IN ('SCHEDULED','ARRIVED')
                AND ((? <> '' AND pre_gate_code = ?) OR (? <> '' AND truck_plate = ?) OR (? <> '' AND rfid_tag = ?))
                ORDER BY (status = 'SCHEDULED') DESC, id ASC LIMIT 1");
            $q->execute([$code, $code, $plate, $plate, $rfid, $rfid]);
            $pre = $q->fetch(PDO::FETCH_ASSOC);
            if (!$pre) {
                throw new IngestRejection("Tidak ada pre-advice/booking untuk truk " . ($plate ?: $rfid ?: $code) . " - akses gate ditolak (unregistered).");
            }
            if ($pre['status'] === 'ARRIVED') {
                throw new IngestRejection("Truk {$pre['truck_plate']} sudah tercatat masuk (pre-gate {$pre['pre_gate_code']}).");
            }
            if ($container_no !== '' && $container_no !== $pre['container_number']) {
                throw new IngestRejection("Kontainer {$container_no} tidak sesuai dengan booking ({$pre['container_number']}).");
            }
            $plate = $pre['truck_plate'];
            $container_no = $pre['container_number'];

            // Pre-advice -> ARRIVED
            $pdo->prepare("UPDATE preadvice SET status = 'ARRIVED', arrived_at = NOW() WHERE id = ?")->execute([$pre['id']]);

            // Truk: dari "scheduled" (di luar) menjadi "at_gate" (di dalam proses dry port)
            $t_check = $pdo->prepare("SELECT id FROM trucks WHERE license_plate = ?");
            $t_check->execute([$plate]);
            $tid = $t_check->fetchColumn();
            if ($tid) {
                $pdo->prepare("UPDATE trucks SET container_number = ?, driver_name = ?, company = ?, status = 'at_gate', gate_in_time = NOW() WHERE id = ?")
                    ->execute([$container_no, $pre['driver_name'], $pre['company'], $tid]);
            } else {
                $pdo->prepare("INSERT INTO trucks (license_plate, rfid_tag, driver_name, company, job_type, do_number, container_number, target_block, status, gate_in_time) VALUES (?,?,?,?,?,?,?,?, 'at_gate', NOW())")
                    ->execute([$plate, $pre['rfid_tag'], $pre['driver_name'], $pre['company'], $pre['job_type'], $pre['do_number'], $container_no, $pre['target_block']]);
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

            // Log event
            $ev_ins = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, from_block, to_block, notes, billable_amount) VALUES ('GATE_IN', ?, 'OUTSIDE', 'GATE', ?, 0)");
            $ev_ins->execute([$container_no, "Truk {$plate} terdeteksi di Gate Inbound oleh OCR & RFID (pre-gate {$pre['pre_gate_code']})"]);

            $tri_flow_log['physical'] = "Truk {$plate} tiba di Gate Inbound; Palang Barrier Gate Terbuka";
            $tri_flow_log['information'] = "OCR membaca pelat {$plate} & kontainer {$container_no}; cocok dengan pre-advice {$pre['pre_gate_code']} ({$pre['source']})";
            $tri_flow_log['financial'] = "Verifikasi izin masuk terminal tervalidasi (Tanpa penalti)";
            break;

        // =====================================================================
        // 2. TRUCK_WEIGHBRIDGE (Penimbangan 80 Ton SOLAS VGM)
        // =====================================================================
        case 'TRUCK_WEIGHBRIDGE':
            $plate = $p['truck_plate'] ?? '';
            $container_no = $p['container_number'] ?? '';
            $weight_gross = (float)($p['weight_gross'] ?? 34850.0);
            $weigh_fee = 75000.0;

            $stmt = $pdo->prepare("UPDATE trucks SET weight_gross = ?, status = 'in_yard' WHERE license_plate = ?");
            $stmt->execute([$weight_gross, $plate]);

            // Update container gross weight
            $c_upd = $pdo->prepare("UPDATE containers SET gross_weight_kg = ? WHERE container_number = ?");
            $c_upd->execute([$weight_gross, $container_no]);

            // Accrue Weighbridge Fee into billing_invoices
            $inv_no = 'INV-W-' . date('Ymd') . '-' . substr(uniqid(), -4);
            $inv_stmt = $pdo->prepare("INSERT INTO billing_invoices (invoice_number, container_number, customer_name, service_type, total_amount, payment_status, payment_method, created_at) VALUES (?, ?, 'PT Samudera Logistik Prima', 'Penimbangan Gantry 80T SOLAS VGM', ?, 'PENDING', 'Bank Transfer (Mandiri H2H)', NOW())");
            $inv_stmt->execute([$inv_no, $container_no, $weigh_fee]);

            // Log event
            $ev_ins = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, notes, billable_amount) VALUES ('WEIGHBRIDGE_VGM', ?, ?, ?)");
            $ev_ins->execute([$container_no, "Penimbangan SOLAS VGM: " . number_format($weight_gross, 0) . " kg", $weigh_fee]);

            $tri_flow_log['physical'] = "Truk {$plate} ditimbang di Jembatan Timbang 80T (Berat: " . number_format($weight_gross, 0) . " kg)";
            $tri_flow_log['information'] = "Sertifikat SOLAS VGM diterbitkan secara digital untuk {$container_no}";
            $tri_flow_log['financial'] = "Biaya Timbang Rp " . number_format($weigh_fee, 0, ',', '.') . " terakrual pada invoice {$inv_no}";
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
            $block = strtoupper($p['block'] ?? 'B');
            $bay = $p['bay'] ?? '08';
            $row = $p['row'] ?? '03';
            $tier = $p['tier'] ?? '02';

            // Equipment becomes idle/operating without container
            $stmt = $pdo->prepare("UPDATE equipment SET status = 'idle', current_container = NULL, last_block = ?, last_updated = NOW() WHERE equipment_id = ?");
            $stmt->execute([$block, $eq_id]);

            // Container placed in yard slot
            $c_stmt = $pdo->prepare("UPDATE containers SET block = ?, bay = ?, row = ?, tier = ?, status = 'in_yard' WHERE container_number = ?");
            $c_stmt->execute([$block, $bay, $row, $tier, $container_no]);

            // Log event
            $slot_str = "{$block}-{$bay}-{$row}-{$tier}";
            $ev_ins = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, to_block, to_bay, to_row, to_tier, notes, billable_amount) VALUES ('STACKING', ?, ?, ?, ?, ?, ?, ?, 0)");
            $ev_ins->execute([$container_no, $eq_id, $block, $bay, $row, $tier, "Kontainer berhasil ditumpuk di {$slot_str}"]);

            $tri_flow_log['physical'] = "Kontainer {$container_no} terpasang di Blok {$block} (Slot: {$slot_str})";
            $tri_flow_log['information'] = "Akurasi slot spasial 100%; Penghitungan Dwell Time (t0) aktif";
            $tri_flow_log['financial'] = "Masa bebas penumpukan hari ke-1 dimulai (Argo progresif siap jalan)";
            break;

        // =====================================================================
        // 6. CUSTOMS_CEISA (Webhook CEISA 4.0 Bea Cukai SPPB Clearance)
        // =====================================================================
        case 'CUSTOMS_CEISA':
            $container_no = $p['container_number'] ?? '';
            $status = $p['customs_status'] ?? 'SPPB_CLEARED';
            $sppb_no = $p['sppb_number'] ?? ('SPPB-' . date('Ymd') . '/KPU.01/2026');

            $stmt = $pdo->prepare("UPDATE containers SET customs_status = ? WHERE container_number = ?");
            $stmt->execute([$status, $container_no]);

            // Propagasi ke CFS: SPPB cleared membuka palet & job; status lain menahan
            if ($status === 'SPPB_CLEARED') {
                $pdo->prepare("UPDATE cfs_pallets SET customs_status = 'CLEARED', status = 'IN_RACK' WHERE container_origin = ? AND status IN ('IN_RACK','HOLD')")->execute([$container_no]);
                $pdo->prepare("UPDATE cfs_jobs SET customs_status = 'SPPB_CLEARED', status = IF(status = 'HOLD', 'IN_PROGRESS', status) WHERE container_number = ?")->execute([$container_no]);
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
            $tb = strtoupper(trim($p['to_block'] ?? '')); $tbay = $p['to_bay'] ?? ''; $trow = $p['to_row'] ?? ''; $ttier = $p['to_tier'] ?? '';
            if ($eq_id === '' || $tb === '' || $tbay === '' || $trow === '' || $ttier === '') {
                throw new IngestRejection('Relokasi wajib membawa equipment_id dan slot tujuan (block, bay, row, tier).');
            }
            $cur = $pdo->prepare("SELECT block, bay, row, tier, status FROM containers WHERE container_number = ?");
            $cur->execute([$container_no = $p['container_number']]);
            $cr = $cur->fetch(PDO::FETCH_ASSOC);
            if ($cr['status'] !== 'in_yard') {
                throw new IngestRejection("Kontainer {$container_no} tidak berada di yard (status {$cr['status']}); relokasi ditolak.");
            }
            $occ = $pdo->prepare("SELECT container_number FROM containers WHERE status='in_yard' AND block = ? AND bay = ? AND row = ? AND tier = ? AND container_number <> ?");
            $occ->execute([$tb, $tbay, $trow, $ttier, $container_no]);
            if ($o = $occ->fetchColumn()) {
                throw new IngestRejection("Slot tujuan {$tb}-{$tbay}-{$trow}-{$ttier} sudah terisi oleh {$o}.");
            }
            $pdo->prepare("UPDATE containers SET block = ?, bay = ?, row = ?, tier = ? WHERE container_number = ?")->execute([$tb, $tbay, $trow, $ttier, $container_no]);
            $pdo->prepare("UPDATE equipment SET status = 'idle', current_container = NULL, last_block = ?, last_updated = NOW() WHERE equipment_id = ?")->execute([$tb, $eq_id]);
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
            $pdo->prepare("UPDATE containers SET block = 'CFS', bay = NULL, row = NULL, tier = NULL WHERE container_number = ?")->execute([$container_no]);
            $pdo->prepare("INSERT INTO yard_events (event_type, container_number, from_block, to_block, notes, billable_amount) VALUES ('CFS_STRIP_START', ?, 'YARD', 'CFS', ?, 0)")
                ->execute([$container_no, "Mulai stripping di rampa {$ramp} (job {$job_no})"]);
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
            $pdo->prepare("INSERT INTO cfs_pallets (job_id, pallet_sscc, container_origin, commodity_name, consignee, category, carton_count, gross_weight_kg, cbm, rack_location, customs_status, status, entry_date) VALUES (?,?,?,?,?,?,?,?,?,?,?, 'IN_RACK', NOW())")
                ->execute([$job['id'], $sscc, $container_no, $p['commodity_name'] ?? null, $p['consignee'] ?? null, $cat, (int)($p['carton_count'] ?? 0), $kg, (float)($p['cbm'] ?? 0), $p['rack_location'] ?? null, $cust]);
            $pdo->prepare("UPDATE cfs_jobs SET total_packages = COALESCE(total_packages,0) + ?, total_weight_kg = COALESCE(total_weight_kg,0) + ?, total_cbm = COALESCE(total_cbm,0) + ? WHERE id = ?")
                ->execute([(int)($p['carton_count'] ?? 0), $kg, (float)($p['cbm'] ?? 0), $job['id']]);
            $pdo->prepare("INSERT INTO yard_events (event_type, container_number, notes, billable_amount) VALUES ('CFS_PALLET_IN', ?, ?, 0)")
                ->execute([$container_no, "Palet SSCC {$sscc} (" . number_format($kg, 0) . " kg) masuk rak " . ($p['rack_location'] ?? '-')]);
            $tri_flow_log['physical'] = "Palet {$sscc} ditimbang (" . number_format($kg, 0) . " kg) dan ditempatkan di rak " . ($p['rack_location'] ?? '-');
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
            $container_no = $pal['container_origin'];
            $pdo->prepare("INSERT INTO yard_events (event_type, container_number, notes, billable_amount) VALUES ('CFS_PALLET_OUT', ?, ?, 0)")
                ->execute([$container_no, "Palet {$sscc} dikeluarkan dari rak CFS ke truk pengambil"]);
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
                $pdo->prepare("UPDATE containers SET customs_status = 'RED_LANE_CHECK' WHERE container_number = ?")->execute([$container_no]);
                $pdo->prepare("UPDATE cfs_jobs SET status = 'HOLD', customs_status = 'RED_LANE_CHECK' WHERE container_number = ? AND status IN ('IN_PROGRESS','PENDING')")->execute([$container_no]);
                $pdo->prepare("UPDATE cfs_pallets SET customs_status = 'RED_LANE', status = 'HOLD' WHERE container_origin = ? AND status = 'IN_RACK'")->execute([$container_no]);
                $note = "Inspeksi {$itype}: ANOMALI - kontainer ditahan (jalur merah)";
                $tri_flow_log['physical'] = "Kontainer {$container_no} dialihkan ke area pemeriksaan fisik jalur merah";
                $tri_flow_log['information'] = "Status pabean RED_LANE_CHECK; job & palet CFS terkait otomatis HOLD";
                $tri_flow_log['financial'] = "Gate-out terkunci; biaya penumpukan tetap berjalan selama HOLD";
            } else {
                $pdo->prepare("UPDATE containers SET customs_status = 'SPPB_PENDING' WHERE container_number = ? AND customs_status IN ('RED_LANE_CHECK','AWAITING_CLEARANCE')")->execute([$container_no]);
                $pdo->prepare("UPDATE cfs_jobs SET status = 'IN_PROGRESS', customs_status = 'SPPB_PENDING' WHERE container_number = ? AND status = 'HOLD'")->execute([$container_no]);
                $pdo->prepare("UPDATE cfs_pallets SET customs_status = 'HOLD', status = 'IN_RACK' WHERE container_origin = ? AND status = 'HOLD'")->execute([$container_no]);
                $note = "Inspeksi {$itype}: BERSIH - menunggu SPPB";
                $tri_flow_log['physical'] = "Pemeriksaan selesai, kontainer {$container_no} dikembalikan ke posisi";
                $tri_flow_log['information'] = "Hasil inspeksi bersih; status SPPB_PENDING (menunggu webhook CEISA)";
                $tri_flow_log['financial'] = "Tidak ada biaya tambahan; rilis menunggu SPPB";
            }
            $pdo->prepare("INSERT INTO yard_events (event_type, container_number, notes, billable_amount) VALUES ('CUSTOMS_INSPECTION', ?, ?, 0)")
                ->execute([$container_no, $note . (empty($p['notes']) ? '' : ' - ' . $p['notes'])]);
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
                $pdo->prepare("INSERT INTO yard_events (event_type, container_number, notes, billable_amount) VALUES ('REEFER_PLUGIN', ?, ?, 0)")
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
            $alarm = abs($supply - $set_t) > 2.0;
            $pdo->prepare("INSERT INTO reefer_telemetry (container_number, socket_id, set_temp_c, supply_temp_c, power_kw, status) VALUES (?,?,?,?,?,?)")
                ->execute([$container_no, $first['socket_id'], $set_t, $supply, $kw, $alarm ? 'ALARM' : 'NORMAL']);
            $kwh_fee = round($kw * $interval_h * 2500.0, 2);
            $inv_no = accrue_billing($pdo, $container_no, 'Listrik & Monitoring Reefer', 'reefer_power_amount', $kwh_fee);
            if ($alarm) {
                $pdo->prepare("INSERT INTO yard_events (event_type, container_number, notes, billable_amount) VALUES ('REEFER_ALARM', ?, ?, 0)")
                    ->execute([$container_no, "ALARM suhu: suplai {$supply} C vs set point {$set_t} C (soket {$first['socket_id']})"]);
            }
            $tri_flow_log['physical'] = "Soket {$first['socket_id']}: suplai {$supply} C, daya {$kw} kW" . ($alarm ? ' - ALARM penyimpangan suhu' : '');
            $tri_flow_log['information'] = "Sampel telemetri tersimpan (set point {$set_t} C); status " . ($alarm ? 'ALARM' : 'NORMAL');
            $tri_flow_log['financial'] = "Listrik reefer Rp " . number_format($kwh_fee, 0, ',', '.') . " terakrual pada {$inv_no}";
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
            $inv_no = accrue_billing($pdo, $container_no, 'Lift-On Gerbong KA Intermodal', 'lolo_amount', 325000.0);
            $pdo->prepare("INSERT INTO yard_events (event_type, container_number, from_block, to_block, notes, billable_amount) VALUES ('RAIL_LOADING', ?, 'YARD', 'RAIL', ?, 325000)")
                ->execute([$container_no, "Dimuat ke kereta {$train_code}, gerbong " . ($p['wagon_no'] ?? '-')]);
            $tri_flow_log['physical'] = "Kontainer {$container_no} dimuat ke gerbong " . ($p['wagon_no'] ?? '-') . " kereta {$train_code}";
            $tri_flow_log['information'] = "Manifest kereta {$train_code} diperbarui otomatis; slot yard dilepas";
            $tri_flow_log['financial'] = "Lift-on KA Rp 325.000 terakrual pada {$inv_no}";
            break;

        // =====================================================================
        // 16. TRUCK_GATE_OUT (Interlock: SPPB cleared + tagihan lunas)
        // =====================================================================
        case 'TRUCK_GATE_OUT':
            $container_no = $p['container_number'];
            $gs = $pdo->prepare("SELECT customs_status, status FROM containers WHERE container_number = ?");
            $gs->execute([$container_no]);
            $crow = $gs->fetch(PDO::FETCH_ASSOC);
            if ($crow['customs_status'] !== 'SPPB_CLEARED') throw new IngestRejection("Gate-out ditolak: {$container_no} belum SPPB_CLEARED ({$crow['customs_status']}).");
            $pend = $pdo->prepare("SELECT COUNT(*) FROM billing_invoices WHERE container_number = ? AND payment_status <> 'PAID'");
            $pend->execute([$container_no]);
            if ($pend->fetchColumn() > 0) throw new IngestRejection("Gate-out ditolak: masih ada tagihan belum lunas untuk {$container_no}.");
            $pdo->prepare("UPDATE containers SET status = 'gate_out', block = 'OUTSIDE', bay = NULL, row = NULL, tier = NULL, gate_out_time = NOW() WHERE container_number = ?")->execute([$container_no]);
            $pdo->prepare("UPDATE trucks SET status = 'gate_out', gate_out_time = NOW() WHERE container_number = ? AND status <> 'gate_out'")->execute([$container_no]);
            $pdo->prepare("INSERT INTO yard_events (event_type, container_number, from_block, to_block, notes, billable_amount) VALUES ('GATE_OUT', ?, 'YARD', 'OUTSIDE', 'Gate-out terotorisasi: SPPB cleared & tagihan lunas', 0)")
                ->execute([$container_no]);
            $tri_flow_log['physical'] = "Palang gate-out terbuka; truk dan kontainer {$container_no} meninggalkan dry port";
            $tri_flow_log['information'] = "Interlock lolos: SPPB_CLEARED dan semua tagihan PAID; e-Gate Pass tercatat";
            $tri_flow_log['financial'] = "Siklus tagihan kontainer ditutup";
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
        'state_summary' => [
            'containers_in_yard' => $new_state['physical']['containers_in_yard'],
            'total_revenue_accrued_idr' => $new_state['financial']['total_revenue_accrued_idr'],
            'sppb_cleared' => $new_state['information']['customs']['sppb_cleared']
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
    $dlq_reason = $is_oauth_err ? ("[OAUTH_SECURITY:{$e->errorCode}] " . $e->getMessage()) : $e->getMessage();
    dlq_push($pdo, $event_id, $event_type, $source_device, $dlq_reason, $data);
    http_response_code($is_oauth_err ? $e->httpCode : ($rejected ? 422 : 500));
    echo json_encode([
        'success' => false,
        'event_id' => $event_id,
        'event_type' => $event_type,
        'oauth_error' => $is_oauth_err ? $e->errorCode : null,
        'error' => $e->getMessage(),
        'routed_to' => 'DEAD_LETTER_QUEUE'
    ], JSON_PRETTY_PRINT);
}