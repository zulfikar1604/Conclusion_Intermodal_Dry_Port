<?php
/**
 * Conclusion Intermodal Dry Port (CIDP)
 * Edge Hardware Simulation & Testing Runner
 * File: api/edge_simulator.php
 *
 * Simulator meniru Edge AI PC / sensor. Identitas truk & kontainer TIDAK ditulis di kode:
 * semuanya diambil dari tabel pre-advice (data yang sudah terdaftar di database).
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/twin_state_helper.php';
require_once __DIR__ . '/preadvice_helper.php';
require_once __DIR__ . '/oauth_helper.php';
global $pdo;
ensure_integration_schema($pdo);

$action = $_GET['action'] ?? ($_POST['action'] ?? 'status');
$step_id = (int)($_GET['step_id'] ?? ($_POST['step_id'] ?? 1));
$sim_code = trim($_GET['code'] ?? ($_POST['code'] ?? ''));

if (!function_exists('post_local_event')) {
function post_local_event($event_type, $payload, $source = 'EDGE-SIMULATOR', ?string $override_client = null, ?string $override_hmac = null) {
    $env = oauth_build_signed_envelope($event_type, is_array($payload) ? $payload : [], $override_client);
    $_POST = [
        'event_id'         => 'SIM-' . date('YmdHis') . '-' . substr(uniqid(), -4),
        'source_device'    => $source,
        'event_type'       => $event_type,
        'bearer_token'     => $env['bearer_token'],
        'signature_hmac'   => $override_hmac !== null ? $override_hmac : $env['signature_hmac'],
        'oauth_context'    => $env['oauth_context'],
        'enforce_security' => true,
        'payload'          => $payload
    ];
    ob_start();
    include __DIR__ . '/ingest_event.php';
    $out = ob_get_clean();
    http_response_code(200);
    return @json_decode($out, true);
}
}

/** Ambil pre-advice target: step 1 = yang masih SCHEDULED, step lain = yang sudah ARRIVED */
if (!function_exists('sim_pick_preadvice')) {
function sim_pick_preadvice(PDO $pdo, int $step, string $code) {
    $status = $step === 1 ? 'SCHEDULED' : 'ARRIVED';
    if ($code !== '') {
        $q = $pdo->prepare("SELECT * FROM preadvice WHERE pre_gate_code = ? AND status = ?");
        $q->execute([$code, $status]);
    } else {
        $order = $step === 1 ? 'eta ASC, id ASC' : 'arrived_at DESC, id DESC';
        $q = $pdo->prepare("SELECT * FROM preadvice WHERE status = ? ORDER BY $order LIMIT 1");
        $q->execute([$status]);
    }
    return $q->fetch(PDO::FETCH_ASSOC);
}
}

/** Cari slot kosong berikutnya pada blok target (Bay-Row-Tier) dari data kontainer di database */
if (!function_exists('sim_next_free_slot')) {
function sim_next_free_slot(PDO $pdo, string $block): array {
    $used = [];
    $q = $pdo->prepare("SELECT bay, row, tier FROM containers WHERE status = 'in_yard' AND block = ?");
    $q->execute([$block]);
    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $used[(int)$r['bay'] . '-' . (int)$r['row'] . '-' . (int)$r['tier']] = true;
    }
    for ($tier = 1; $tier <= 3; $tier++) {
        for ($bay = 1; $bay <= 20; $bay++) {
            for ($row = 1; $row <= 6; $row++) {
                if (!isset($used["$bay-$row-$tier"])) {
                    return [sprintf('%02d', $bay), sprintf('%02d', $row), sprintf('%02d', $tier)];
                }
            }
        }
    }
    return ['01', '01', '01'];
}
}

if (!function_exists('sim_pick_container')) {
    /** Pilih kontainer dari database sesuai kondisi aktivitas (?container= untuk menentukan sendiri) */
    function sim_pick_container(PDO $pdo, string $where, string $explicit = '') {
        if ($explicit !== '') {
            $q = $pdo->prepare("SELECT * FROM containers WHERE container_number = ?");
            $q->execute([strtoupper($explicit)]);
            return $q->fetch(PDO::FETCH_ASSOC);
        }
        return $pdo->query("SELECT * FROM containers WHERE $where ORDER BY gate_in_time ASC, id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    }
}
switch ($action) {
    case 'status':
        $next = sim_pick_preadvice($pdo, 1, '');
        echo json_encode([
            'status' => 'READY',
            'next_scheduled' => $next ? ['pre_gate_code' => $next['pre_gate_code'], 'truck_plate' => $next['truck_plate'], 'container_number' => $next['container_number']] : null,
            'available_steps' => [
                1 => 'TRUCK_GATE_ARRIVAL (Kamera ANPR & Antena RFID Gate Inbound)',
                2 => 'TRUCK_WEIGHBRIDGE (Penimbangan 80 Ton SOLAS VGM & Tiket e-Pass)',
                3 => 'CONTAINER_LIFT (Reach Stacker RS-02 Bergerak & Lift-Off dari Truk)',
                4 => 'CONTAINER_STACK (Penempatan di slot kosong pada blok target)',
                5 => 'CUSTOMS_CEISA (Webhook CEISA 4.0 DJBC SPPB Cleared Otomatis)',
                6 => 'PAYMENT_SETTLED (Host-to-Host Pelunasan Bank Mandiri)'
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        break;

    case 'step':
        $pre = sim_pick_preadvice($pdo, $step_id, $sim_code);
        if (!$pre) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => $step_id === 1
                    ? 'Tidak ada truk berstatus SCHEDULED (semua sudah masuk). Daftarkan pre-gate baru atau reset demo.'
                    : 'Belum ada truk yang masuk gate. Jalankan Step 1 terlebih dahulu.'
            ], JSON_PRETTY_PRINT);
            exit;
        }
        $plate = $pre['truck_plate'];
        $cn = $pre['container_number'];
        $res = null;
        switch ($step_id) {
            case 1:
                // Sensor hanya "melihat" plat/RFID/kontainer di gerbang; data master dicocokkan oleh ingest
                $res = post_local_event('TRUCK_GATE_ARRIVAL', [
                    'truck_plate' => $plate,
                    'container_number' => $cn,
                    'rfid_tag' => $pre['rfid_tag']
                ], 'GATE-IN-LANE-01');
                break;
            case 2:
                $res = post_local_event('TRUCK_WEIGHBRIDGE', [
                    'truck_plate' => $plate,
                    'container_number' => $cn,
                    'weight_gross' => round(mt_rand(26500, 34500) + mt_rand(0, 99) / 100, 2)
                ], 'WEIGHBRIDGE-80T');
                break;
            case 3:
                post_local_event('EQUIPMENT_MOVE', [
                    'equipment_id' => 'RS-02',
                    'gps_x' => 480.0,
                    'gps_y' => 172.0,
                    'fuel_percent' => 74,
                    'status' => 'operating'
                ], 'KALMAR-CANBUS-RS02');

                $res = post_local_event('CONTAINER_LIFT', [
                    'equipment_id' => 'RS-02',
                    'truck_plate' => $plate,
                    'container_number' => $cn
                ], 'KALMAR-DRG450-RS02');
                break;
            case 4:
                $block = strtoupper($pre['target_block'] ?: 'B');
                [$bay, $row, $tier] = sim_next_free_slot($pdo, $block);
                post_local_event('EQUIPMENT_MOVE', [
                    'equipment_id' => 'RS-02',
                    'gps_x' => 490.0,
                    'gps_y' => 250.0,
                    'fuel_percent' => 73,
                    'status' => 'operating'
                ], 'KALMAR-CANBUS-RS02');

                $res = post_local_event('CONTAINER_STACK', [
                    'equipment_id' => 'RS-02',
                    'container_number' => $cn,
                    'block' => $block,
                    'bay' => $bay,
                    'row' => $row,
                    'tier' => $tier
                ], 'KALMAR-DRG450-RS02');
                break;
            case 5:
                $res = post_local_event('CUSTOMS_CEISA', [
                    'container_number' => $cn,
                    'customs_status' => 'SPPB_CLEARED',
                    'sppb_number' => 'SPPB-' . date('Ymd') . substr($cn, 4, 4) . '/KPU.01/' . date('Y')
                ], 'DJBC-CEISA-H2H');
                break;
            case 6:
                $res = post_local_event('PAYMENT_SETTLED', [
                    'container_number' => $cn
                ], 'BANK-MANDIRI-H2H');
                break;
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid step_id (1-6)']);
                exit;
        }

        echo json_encode([
            'success' => (bool)($res['success'] ?? false),
            'step_executed' => $step_id,
            'pre_gate_code' => $pre['pre_gate_code'],
            'truck_plate' => $plate,
            'container_number' => $cn,
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
        // Satu siklus penuh untuk SATU truk terjadwal (berikutnya, atau sesuai ?code=)
        $first = sim_pick_preadvice($pdo, 1, $sim_code);
        if (!$first) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Tidak ada truk berstatus SCHEDULED. Daftarkan pre-gate baru atau reset demo.'], JSON_PRETTY_PRINT);
            break;
        }
        $_GET['code'] = $first['pre_gate_code'];
        $results = [];
        for ($i = 1; $i <= 6; $i++) {
            $_GET['step_id'] = $i;
            $_GET['action'] = 'step';
            ob_start();
            $action = 'step';
            $step_id = $i;
            $sim_code = $first['pre_gate_code'];
            include __FILE__;
            $out = ob_get_clean();
            $decoded = @json_decode($out, true);
            $results[] = $decoded;
            if (!($decoded['success'] ?? false)) break; // error -> DLQ, hentikan siklus truk ini
        }
        echo json_encode([
            'success' => true,
            'message' => 'Siklus operasional untuk ' . $first['truck_plate'] . ' / ' . $first['container_number'] . ' dieksekusi.',
            'steps' => $results
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        break;

    case 'activity':
        // Aktivitas lapangan selain alur masuk-keluar utama; semua lewat ingest_event (sensor -> software -> DB)
        $type = strtolower($_GET['type'] ?? ($_POST['type'] ?? ''));
        $want = trim($_GET['container'] ?? ($_POST['container'] ?? ''));
        $steps = [];
        $run = function ($ev, $payload, $src) use (&$steps) { $r = post_local_event($ev, $payload, $src); $steps[] = ['event' => $ev, 'success' => (bool)($r['success'] ?? false), 'result' => $r]; return $r; };
        $fail = function ($msg) { http_response_code(404); echo json_encode(['success' => false, 'error' => $msg, 'message' => $msg], JSON_PRETTY_PRINT); exit; };

        // Helper: jika belum ada kontainer bertipe tertentu di yard saat tombol aktivitas diklik, masukkan 1 dari preadvice secara otomatis
        $ensureInYard = function ($cargoFilter = '') use ($pdo, $run) {
            $sql = "SELECT * FROM preadvice WHERE status = 'SCHEDULED'" . ($cargoFilter ? " AND cargo_type = '$cargoFilter'" : "") . " ORDER BY id ASC LIMIT 1";
            $pre = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
            if (!$pre) return null;
            $cn = $pre['container_number'];
            $plate = $pre['truck_plate'];
            $blk = $pre['target_block'] ?: ($pre['cargo_type'] === 'reefer' ? 'A' : 'B');
            [$bay, $row, $tier] = sim_next_free_slot($pdo, $blk);
            $run('TRUCK_GATE_ARRIVAL', ['truck_plate' => $plate, 'container_number' => $cn, 'driver_name' => $pre['driver_name'], 'company' => $pre['company']], 'GATE-IN-LANE-01');
            $run('TRUCK_WEIGHBRIDGE', ['truck_plate' => $plate, 'container_number' => $cn, 'weight_gross' => (float)$pre['weight_gross'], 'weight_tare' => (float)$pre['weight_tare']], 'METTLER-WB-01');
            $run('CONTAINER_LIFT', ['equipment_id' => 'RS-02', 'container_number' => $cn], 'KALMAR-DRG450-RS02');
            $run('CONTAINER_STACK', ['equipment_id' => 'RS-02', 'container_number' => $cn, 'block' => $blk, 'bay' => $bay, 'row' => $row, 'tier' => $tier], 'KALMAR-DRG450-RS02');
            return sim_pick_container($pdo, "status = 'in_yard'", $cn);
        };

        switch ($type) {
            case 'relocate':
            case 'yard_relocation':
                $c = sim_pick_container($pdo, "status = 'in_yard' AND block IN ('A','B','C','D','E') AND bay IS NOT NULL", $want);
                if (!$c) $c = $ensureInYard();
                if (!$c) $fail('Tidak ada kontainer di yard (blok A-E) untuk direlokasi.');
                $blocks = array_values(array_diff(['A','B','C','D','E'], [$c['block']]));
                $tb = $_GET['to_block'] ?? $blocks[array_rand($blocks)];
                [$bay, $row, $tier] = sim_next_free_slot($pdo, strtoupper($tb));
                $run('EQUIPMENT_MOVE', ['equipment_id' => 'RS-01', 'gps_x' => 160.0, 'gps_y' => 210.0, 'fuel_percent' => 86, 'status' => 'operating'], 'KALMAR-CANBUS-RS01');
                $run('YARD_RELOCATION', ['equipment_id' => 'RS-01', 'container_number' => $c['container_number'], 'to_block' => strtoupper($tb), 'to_bay' => $bay, 'to_row' => $row, 'to_tier' => $tier, 'reason' => 'optimasi blok / reshuffle'], 'RTK-GNSS-RS01');
                break;

            case 'cfs_stripping':
                $c = sim_pick_container($pdo, "status = 'in_yard' AND cargo_type = 'dry' AND container_number NOT IN (SELECT container_number FROM cfs_jobs)", $want);
                if (!$c) $c = $ensureInYard('dry');
                if (!$c) $fail('Tidak ada kontainer dry di yard untuk stripping CFS.');
                $ramp = 'D' . mt_rand(1, 5);
                $run('CFS_STRIPPING_START', ['container_number' => $c['container_number'], 'dock_ramp' => $ramp, 'operator_name' => 'Tally CFS'], 'CFS-RAMP-' . $ramp);
                break;

            case 'cfs_pallet':
                $c = sim_pick_container($pdo, "status = 'in_yard' AND container_number IN (SELECT container_number FROM cfs_jobs WHERE status = 'IN_PROGRESS')", $want);
                if (!$c) {
                    $c = sim_pick_container($pdo, "status = 'in_yard' AND cargo_type = 'dry'", $want) ?: $ensureInYard('dry');
                    if ($c) {
                        $ramp = 'D' . mt_rand(1, 5);
                        $run('CFS_STRIPPING_START', ['container_number' => $c['container_number'], 'dock_ramp' => $ramp, 'operator_name' => 'Tally CFS'], 'CFS-RAMP-' . $ramp);
                    }
                }
                if (!$c) $fail('Tidak ada kontainer di CFS untuk pemindaian palet.');
                $items = [['Spare part mesin industri', 'machinery'], ['Komponen elektronik', 'electronics'], ['Barang konsumsi FMCG', 'fmcg'], ['Suku cadang otomotif CKD', 'automotive']];
                $it = $items[array_rand($items)];
                $f17 = '0' . '8991234' . str_pad((string)mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT);
                $run('CFS_PALLET_SCAN', [
                    'container_number' => $c['container_number'], 'pallet_sscc' => $f17 . gs1_check_digit($f17),
                    'commodity_name' => $it[0], 'category' => $it[1], 'carton_count' => mt_rand(20, 80),
                    'gross_weight_kg' => mt_rand(450, 1800), 'cbm' => round(mt_rand(10, 25) / 10, 2),
                    'rack_location' => sprintf('AISLE-A-%02d-L%d', mt_rand(1, 8), mt_rand(1, 4)), 'consignee' => $c['owner_company']
                ], 'HONEYWELL-GS1-SCAN');
                break;

            case 'cfs_cycle':
                $c = sim_pick_container($pdo, "status = 'in_yard' AND cargo_type = 'dry' AND container_number NOT IN (SELECT container_number FROM cfs_jobs)", $want);
                if (!$c) $c = $ensureInYard('dry');
                if (!$c) $fail('Tidak ada kontainer dry di yard untuk stripping CFS.');
                $ramp = 'D' . mt_rand(1, 5);
                $run('CFS_STRIPPING_START', ['container_number' => $c['container_number'], 'dock_ramp' => $ramp, 'operator_name' => 'Tally CFS'], 'CFS-RAMP-' . $ramp);
                $items = [['Spare part mesin industri', 'machinery'], ['Komponen elektronik', 'electronics'], ['Barang konsumsi FMCG', 'fmcg']];
                foreach ($items as $k => $it) {
                    $f17 = '0' . '8991234' . str_pad((string)mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT);
                    $run('CFS_PALLET_SCAN', [
                        'container_number' => $c['container_number'], 'pallet_sscc' => $f17 . gs1_check_digit($f17),
                        'commodity_name' => $it[0], 'category' => $it[1], 'carton_count' => mt_rand(20, 80),
                        'gross_weight_kg' => mt_rand(450, 1800), 'cbm' => round(mt_rand(10, 25) / 10, 2),
                        'rack_location' => sprintf('R-%02d-%02d', mt_rand(1, 9), $k + 1), 'consignee' => $c['owner_company']
                    ], 'HONEYWELL-GS1-SCAN');
                }
                $run('CFS_STRIPPING_COMPLETE', ['container_number' => $c['container_number']], 'CFS-RAMP-' . $ramp);
                break;

            case 'cfs_dispatch':
                $pal = $pdo->query("SELECT pallet_sscc FROM cfs_pallets WHERE status = 'IN_RACK' ORDER BY (customs_status = 'CLEARED') DESC, id ASC LIMIT 1")->fetchColumn() ?: $fail('Tidak ada palet di rak CFS.');
                $run('CFS_PALLET_DISPATCH', ['pallet_sscc' => $pal], 'CFS-FORKLIFT-GATE');
                break;

            case 'customs_inspect':
            case 'customs_xray_hold':
                $c = sim_pick_container($pdo, "status = 'in_yard'", $want);
                if (!$c) $c = $ensureInYard();
                if (!$c) $fail('Tidak ada kontainer di yard untuk diperiksa.');
                $run('CUSTOMS_INSPECTION', ['container_number' => $c['container_number'], 'inspection_type' => 'XRAY_SCAN', 'result' => strtoupper($_GET['result'] ?? 'ANOMALY'), 'notes' => 'Gantry X-Ray 6 MeV'], 'XRAY-GANTRY-6MEV');
                break;

            case 'customs_release':
            case 'customs_sppb':
                $c = sim_pick_container($pdo, "status = 'in_yard' AND customs_status <> 'SPPB_CLEARED'", $want)
                    ?: sim_pick_container($pdo, "status = 'in_yard'", $want)
                    ?: $ensureInYard();
                if (!$c) $fail('Tidak ada kontainer yang menunggu SPPB.');
                $run('CUSTOMS_CEISA', ['container_number' => $c['container_number'], 'customs_status' => 'SPPB_CLEARED', 'sppb_number' => 'SPPB-' . date('Ymd') . substr($c['container_number'], 4, 4) . '/KPU.01/' . date('Y')], 'DJBC-CEISA-H2H');
                break;

            case 'reefer_plug':
            case 'reefer_plugin':
                $c = sim_pick_container($pdo, "status = 'in_yard' AND cargo_type = 'reefer' AND container_number NOT IN (SELECT container_number FROM reefer_telemetry)", $want);
                if (!$c) $c = $ensureInYard('reefer');
                if (!$c) $c = sim_pick_container($pdo, "status = 'in_yard' AND cargo_type = 'reefer'", $want);
                if (!$c) $fail('Tidak ada kontainer reefer tersedia untuk dicolok.');
                $n = (int)$pdo->query("SELECT COUNT(DISTINCT container_number) FROM reefer_telemetry")->fetchColumn() + 1;
                $run('REEFER_PLUGIN', ['container_number' => $c['container_number'], 'socket_id' => sprintf('RF-%03d', $n), 'set_temp_c' => -18.0], 'LORAWAN-REEFER-SOCKET');
                break;

            case 'reefer_telemetry':
            case 'reefer_telemetry_normal':
            case 'reefer_telemetry_alarm':
                $c = sim_pick_container($pdo, "cargo_type = 'reefer' AND status = 'in_yard' AND container_number IN (SELECT container_number FROM reefer_telemetry)", $want);
                if (!$c) {
                    $c = sim_pick_container($pdo, "status = 'in_yard' AND cargo_type = 'reefer'", $want) ?: $ensureInYard('reefer');
                    if ($c) {
                        $n = (int)$pdo->query("SELECT COUNT(DISTINCT container_number) FROM reefer_telemetry")->fetchColumn() + 1;
                        $run('REEFER_PLUGIN', ['container_number' => $c['container_number'], 'socket_id' => sprintf('RF-%03d', $n), 'set_temp_c' => -18.0], 'LORAWAN-REEFER-SOCKET');
                    }
                }
                if (!$c) $fail('Tidak ada reefer aktif di yard.');
                $fault = ($type === 'reefer_telemetry_alarm') || (($_GET['fault'] ?? '') === '1');
                $run('REEFER_TELEMETRY', ['container_number' => $c['container_number'], 'supply_temp_c' => $fault ? -13.2 : round(-18.0 + mt_rand(-6, 6) / 10, 1), 'power_kw' => round(mt_rand(48, 65) / 10, 2), 'interval_minutes' => 60], 'LORAWAN-REEFER-SOCKET');
                break;

            case 'rail_load':
            case 'rail_loading':
                $c = sim_pick_container($pdo, "status = 'in_yard' AND customs_status = 'SPPB_CLEARED'", $want);
                if (!$c) {
                    $c = sim_pick_container($pdo, "status = 'in_yard'", $want) ?: $ensureInYard();
                    if ($c) {
                        $run('CUSTOMS_CEISA', ['container_number' => $c['container_number'], 'customs_status' => 'SPPB_CLEARED', 'sppb_number' => 'SPPB-' . date('Ymd') . substr($c['container_number'], 4, 4) . '/KPU.01/' . date('Y')], 'DJBC-CEISA-H2H');
                    }
                }
                if (!$c) $fail('Tidak ada kontainer SPPB_CLEARED di yard untuk dimuat ke kereta.');
                $wagon = 'GD-' . str_pad((string)(1 + (int)$pdo->query("SELECT COALESCE(SUM(loaded_wagons),0) FROM trains")->fetchColumn()), 2, '0', STR_PAD_LEFT);
                $run('RAIL_LOADING', ['container_number' => $c['container_number'], 'train_code' => $_GET['train'] ?? ('KA-PASOSO-' . date('md')), 'wagon_no' => $wagon, 'destination' => 'Pasoso (Tanjung Priok)', 'total_wagons' => 20], 'FRAUSCHER-AXLE-COUNTER');
                break;

            case 'gate_out':
                $c = sim_pick_container($pdo, "status = 'in_yard' AND customs_status = 'SPPB_CLEARED'", $want) ?: $fail('Tidak ada kontainer SPPB_CLEARED di yard untuk gate-out.');
                $run('TRUCK_GATE_OUT', ['container_number' => $c['container_number']], 'GATE-OUT-LANE-01');
                break;

            default:
                http_response_code(400);
                echo json_encode(['error' => 'Unknown activity type: ' . $type], JSON_PRETTY_PRINT);
                exit;
        }
        $ok = !in_array(false, array_column($steps, 'success'), true);
        $lastStep = !empty($steps) ? $steps[count($steps) - 1]['result'] : null;
        echo json_encode([
            'success'  => $ok,
            'activity' => $type,
            'message'  => $lastStep['message'] ?? ('Aktivitas ' . $type . ' dieksekusi'),
            'result'   => $lastStep,
            'steps'    => $steps
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        break;
    case 'send_unregistered':
        // Uji DLQ: sensor membaca truk yang TIDAK ada di pre-advice
        $res = post_local_event('TRUCK_GATE_ARRIVAL', [
            'truck_plate' => 'B 0000 XXX',
            'container_number' => 'TEMU123456' . iso6346_check_digit('TEMU123456')
        ], 'GATE-IN-LANE-01');
        echo json_encode(['success' => false, 'expected' => 'REJECTED_TO_DLQ', 'result' => $res], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        break;

    case 'tamper_test':
        // Uji Cryptographic Trust (Slide 8): Tanda tangan HMAC dihitung untuk 34.850 kg, tetapi payload dimanipulasi menjadi 20.000 kg (Man-in-the-Middle)
        $orig_payload = ['truck_plate' => 'B 1137 KFA', 'container_number' => 'MSCU9180379', 'weight_gross' => 34850.0];
        $tampered_payload = ['truck_plate' => 'B 1137 KFA', 'container_number' => 'MSCU9180379', 'weight_gross' => 20000.0];
        $orig_sig = oauth_compute_payload_hmac('cidp-edge-gate-01', 'TRUCK_WEIGHBRIDGE', $orig_payload, '');
        $res = post_local_event('TRUCK_WEIGHBRIDGE', $tampered_payload, 'MITM-ATTACKER-PROXY', 'cidp-edge-gate-01', $orig_sig);
        echo json_encode(['success' => false, 'expected' => 'HMAC_SIGNATURE_MISMATCH_401', 'result' => $res], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        break;

    case 'scope_violation_test':
        // Uji Least Privilege Scope (Slide 7): Perangkat Timbangan (cidp-edge-gate-01) mencoba menembak pelunasan tagihan PAYMENT_SETTLED
        $res = post_local_event('PAYMENT_SETTLED', ['container_number' => 'MSCU9180379'], 'COMPROMISED-GATE-PC', 'cidp-edge-gate-01');
        echo json_encode(['success' => false, 'expected' => 'INSUFFICIENT_SCOPE_403', 'result' => $res], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        break;

    case 'reset':
        $_GET['action'] = 'reset';
        include __DIR__ . '/demo_control.php';
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action. Use "status", "step", "move_equipment", "auto_cycle", "activity", "send_unregistered", or "reset".']);
        break;
}
