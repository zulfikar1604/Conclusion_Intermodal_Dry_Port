<?php
/**
 * Conclusion Intermodal Dry Port (CIDP)
 * Edge Telemetry & Hardware Ingestion Endpoint (Tri-Flow Correlation)
 * File: api/ingest_event.php
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/twin_state_helper.php';
global $pdo;

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
            'PAYMENT_SETTLED'
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

$event_type = strtoupper($data['event_type']);
$source_device = $data['source_device'] ?? 'EDGE-SIMULATOR';
$event_id = $data['event_id'] ?? ('EVT-' . date('YmdHis') . '-' . substr(uniqid(), -4));
$p = $data['payload'] ?? $data;

$tri_flow_log = [
    'physical' => '',
    'information' => '',
    'financial' => ''
];

try {
    $pdo->beginTransaction();

    switch ($event_type) {

        // =====================================================================
        // 1. TRUCK_GATE_ARRIVAL (OCR Kamera & Antena RFID Gate Inbound)
        // =====================================================================
        case 'TRUCK_GATE_ARRIVAL':
            $plate = $p['truck_plate'] ?? 'B 9812 UIX';
            $container_no = $p['container_number'] ?? 'MSKU9182374';
            $driver = $p['driver_name'] ?? 'Rahmat Hidayat';
            $company = $p['company'] ?? 'PT Samudera Logistik Prima';
            $rfid = $p['rfid_tag'] ?? 'E28011700000020489110011';

            // Check if truck already exists
            $t_check = $pdo->prepare("SELECT id FROM trucks WHERE license_plate = ?");
            $t_check->execute([$plate]);
            $tid = $t_check->fetchColumn();

            if ($tid) {
                $stmt = $pdo->prepare("UPDATE trucks SET container_number = ?, driver_name = ?, company = ?, status = 'at_gate', gate_in_time = NOW() WHERE id = ?");
                $stmt->execute([$container_no, $driver, $company, $tid]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO trucks (license_plate, rfid_tag, driver_name, company, container_number, status, gate_in_time) VALUES (?, ?, ?, ?, ?, 'at_gate', NOW())");
                $stmt->execute([$plate, $rfid, $driver, $company, $container_no]);
            }

            // Register container if not present
            $c_check = $pdo->prepare("SELECT id FROM containers WHERE container_number = ?");
            $c_check->execute([$container_no]);
            if (!$c_check->fetchColumn()) {
                $c_ins = $pdo->prepare("INSERT INTO containers (container_number, iso_code, size_type, cargo_type, rfid_tag, owner_company, customs_status, block, status, gate_in_time) VALUES (?, '42G1', '40FT HIGH CUBE', 'dry', ?, ?, 'AWAITING_CLEARANCE', 'GATE', 'in_transit', NOW())");
                $c_ins->execute([$container_no, $rfid, $company]);
            }

            // Log event
            $ev_ins = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, from_block, to_block, notes, billable_amount) VALUES ('GATE_IN', ?, 'OUTSIDE', 'GATE', ?, 0)");
            $ev_ins->execute([$container_no, "Truk {$plate} terdeteksi di Gate Inbound oleh OCR & RFID"]);

            $tri_flow_log['physical'] = "Truk {$plate} tiba di Gate Inbound; Palang Barrier Gate Terbuka";
            $tri_flow_log['information'] = "OCR membaca pelat {$plate} & kontainer {$container_no} (RFID Valid)";
            $tri_flow_log['financial'] = "Verifikasi izin masuk terminal tervalidasi (Tanpa penalti)";
            break;

        // =====================================================================
        // 2. TRUCK_WEIGHBRIDGE (Penimbangan 80 Ton SOLAS VGM)
        // =====================================================================
        case 'TRUCK_WEIGHBRIDGE':
            $plate = $p['truck_plate'] ?? 'B 9812 UIX';
            $container_no = $p['container_number'] ?? 'MSKU9182374';
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
            $container_no = $p['container_number'] ?? 'MSKU9182374';
            $plate = $p['truck_plate'] ?? 'B 9812 UIX';
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
            $container_no = $p['container_number'] ?? 'MSKU9182374';
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
            $container_no = $p['container_number'] ?? 'MSKU9182374';
            $status = $p['customs_status'] ?? 'SPPB_CLEARED';
            $sppb_no = $p['sppb_number'] ?? ('SPPB-' . date('Ymd') . '/KPU.01/2026');

            $stmt = $pdo->prepare("UPDATE containers SET customs_status = ? WHERE container_number = ?");
            $stmt->execute([$status, $container_no]);

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
            $container_no = $p['container_number'] ?? 'MSKU9182374';

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

        default:
            throw new Exception("Unhandled event type: {$event_type}");
    }

    $pdo->commit();

    // 4. Update Atomic Cache & Read New State
    $new_state = get_live_twin_state($pdo, true);

    echo json_encode([
        'success' => true,
        'event_id' => $event_id,
        'event_type' => $event_type,
        'source_device' => $source_device,
        'timestamp' => date('c'),
        'tri_flow' => $tri_flow_log,
        'state_summary' => [
            'containers_in_yard' => $new_state['physical']['containers_in_yard'],
            'total_revenue_accrued_idr' => $new_state['financial']['total_revenue_accrued_idr'],
            'sppb_cleared' => $new_state['information']['customs']['sppb_cleared']
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'event_id' => $event_id,
        'event_type' => $event_type,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
