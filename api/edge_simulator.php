<?php
/**
 * Conclusion Intermodal Dry Port (CIDP)
 * Edge Hardware Simulation & Testing Runner
 * File: api/edge_simulator.php
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/twin_state_helper.php';

$action = $_GET['action'] ?? ($_POST['action'] ?? 'status');
$step_id = (int)($_GET['step_id'] ?? ($_POST['step_id'] ?? 1));

function post_local_event($event_type, $payload, $source = 'EDGE-SIMULATOR') {
    // Internal direct dispatch
    $_POST = [
        'event_id' => 'SIM-' . date('YmdHis') . '-' . substr(uniqid(), -4),
        'source_device' => $source,
        'event_type' => $event_type,
        'payload' => $payload
    ];
    ob_start();
    include __DIR__ . '/ingest_event.php';
    $out = ob_get_clean();
    return @json_decode($out, true);
}

switch ($action) {
    case 'status':
        echo json_encode([
            'status' => 'READY',
            'available_steps' => [
                1 => 'TRUCK_GATE_ARRIVAL (Kamera ANPR & Antena RFID Gate Inbound)',
                2 => 'TRUCK_WEIGHBRIDGE (Penimbangan 80 Ton SOLAS VGM & Tiket e-Pass)',
                3 => 'CONTAINER_LIFT (Reach Stacker RS-02 Bergerak & Lift-Off dari Truk)',
                4 => 'CONTAINER_STACK (Penempatan di Slot Yard Blok B-08-03-02)',
                5 => 'CUSTOMS_CEISA (Webhook CEISA 4.0 DJBC SPPB Cleared Otomatis)',
                6 => 'PAYMENT_SETTLED (Host-to-Host Pelunasan Bank Mandiri Rp 400.000)'
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        break;

    case 'step':
        $res = null;
        switch ($step_id) {
            case 1:
                $res = post_local_event('TRUCK_GATE_ARRIVAL', [
                    'truck_plate' => 'B 9812 UIX',
                    'container_number' => 'MSKU9182374',
                    'driver_name' => 'Rahmat Hidayat',
                    'company' => 'PT Samudera Logistik Prima',
                    'rfid_tag' => 'E28011700000020489110011'
                ], 'GATE-IN-LANE-01');
                break;
            case 2:
                $res = post_local_event('TRUCK_WEIGHBRIDGE', [
                    'truck_plate' => 'B 9812 UIX',
                    'container_number' => 'MSKU9182374',
                    'weight_gross' => 34850.0
                ], 'WEIGHBRIDGE-80T');
                break;
            case 3:
                // Move RS-02 to Truck Transfer Lane at North of Block B
                post_local_event('EQUIPMENT_MOVE', [
                    'equipment_id' => 'RS-02',
                    'gps_x' => 480.0,
                    'gps_y' => 172.0,
                    'fuel_percent' => 74,
                    'status' => 'operating'
                ], 'KALMAR-CANBUS-RS02');

                $res = post_local_event('CONTAINER_LIFT', [
                    'equipment_id' => 'RS-02',
                    'truck_plate' => 'B 9812 UIX',
                    'container_number' => 'MSKU9182374'
                ], 'KALMAR-DRG450-RS02');
                break;
            case 4:
                // Move RS-02 into Block B (B-08-03-02) to stack the container
                post_local_event('EQUIPMENT_MOVE', [
                    'equipment_id' => 'RS-02',
                    'gps_x' => 490.0,
                    'gps_y' => 250.0,
                    'fuel_percent' => 73,
                    'status' => 'operating'
                ], 'KALMAR-CANBUS-RS02');

                $res = post_local_event('CONTAINER_STACK', [
                    'equipment_id' => 'RS-02',
                    'container_number' => 'MSKU9182374',
                    'block' => 'B',
                    'bay' => '08',
                    'row' => '03',
                    'tier' => '02'
                ], 'KALMAR-DRG450-RS02');
                break;
            case 5:
                $res = post_local_event('CUSTOMS_CEISA', [
                    'container_number' => 'MSKU9182374',
                    'customs_status' => 'SPPB_CLEARED',
                    'sppb_number' => 'SPPB-092811/KPU.01/2026'
                ], 'DJBC-CEISA-H2H');
                break;
            case 6:
                $res = post_local_event('PAYMENT_SETTLED', [
                    'container_number' => 'MSKU9182374'
                ], 'BANK-MANDIRI-H2H');
                break;
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid step_id (1-6)']);
                exit;
        }

        echo json_encode([
            'success' => true,
            'step_executed' => $step_id,
            'result' => $res
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        break;

    case 'move_equipment':
        $eq_id = $_GET['equipment_id'] ?? ($_POST['equipment_id'] ?? 'RS-02');
        $gx = (float)($_GET['gps_x'] ?? ($_POST['gps_x'] ?? 350.0));
        $gy = (float)($_GET['gps_y'] ?? ($_POST['gps_y'] ?? 300.0));
        $status = $_GET['status'] ?? ($_POST['status'] ?? 'operating');
        $res = post_local_event('EQUIPMENT_MOVE', [
            'equipment_id' => $eq_id,
            'gps_x' => $gx,
            'gps_y' => $gy,
            'fuel_percent' => 78,
            'status' => $status
        ], 'KALMAR-CANBUS-' . $eq_id);
        echo json_encode([
            'success' => true,
            'action' => 'move_equipment',
            'equipment_id' => $eq_id,
            'gps_x' => $gx,
            'gps_y' => $gy,
            'result' => $res
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        break;

    case 'auto_cycle':
        $results = [];
        for ($i = 1; $i <= 6; $i++) {
            $_GET['step_id'] = $i;
            ob_start();
            $action = 'step';
            include __FILE__;
            $out = ob_get_clean();
            $results[] = @json_decode($out, true);
        }
        echo json_encode([
            'success' => true,
            'message' => 'Full 6-step operational cycle executed successfully.',
            'steps' => $results
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        break;

    case 'reset':
        try {
            $pdo->exec("UPDATE equipment SET gps_x = 350.0, gps_y = 300.0, status = 'idle', current_container = NULL WHERE equipment_id = 'RS-02'");
            $pdo->exec("UPDATE equipment SET gps_x = 150.0, gps_y = 200.0, status = 'idle', current_container = NULL WHERE equipment_id = 'RS-01'");
            $pdo->exec("UPDATE equipment SET gps_x = 720.0, gps_y = 240.0, status = 'idle', current_container = NULL WHERE equipment_id = 'RS-03'");
            $pdo->exec("UPDATE equipment SET gps_x = 600.0, gps_y = 600.0, status = 'idle', current_container = NULL WHERE equipment_id = 'RTG-01'");
            $pdo->exec("UPDATE trucks SET status = 'gate_out', weight_gross = 0 WHERE license_plate = 'B 9812 UIX'");
            get_live_twin_state($pdo, true);
            echo json_encode(['success' => true, 'message' => 'Simulation state reset to default benchmark.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action. Use "status", "step", "move_equipment", "auto_cycle", or "reset".']);
        break;
}
