<?php
// =============================================================================
// FILE: api/crud.php
// FUNGSI: Unified CRUD API untuk CIDP YMS
// =============================================================================

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/connection.php';

api_init_headers();

$input = get_json_input();
$action = $input['action'] ?? '';

// Backward-compatible response bridge
if (!function_exists('response')) {
    function response($success, $data, $message, $count = null) {
        $extra = ($count !== null) ? ['count' => $count] : [];
        json_response($success, $data, $message, $success ? 200 : 400, $extra);
    }
}

try {
    switch ($action) {
        // ---------------------------------------------------------------------
        // CONTAINER OPERATIONS
        // ---------------------------------------------------------------------
        case 'get_containers':
            $status = $_GET['status'] ?? null;
            if ($status) {
                $stmt = $pdo->prepare("SELECT * FROM containers WHERE status = ? ORDER BY id DESC");
                $stmt->execute([$status]);
            } else {
                $stmt = $pdo->query("SELECT * FROM containers ORDER BY id DESC");
            }
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            response(true, $data, "Containers fetched successfully", count($data));
            break;

        case 'add_container':
            $container_number = $_POST['container_number'] ?? $input['container_number'] ?? null;
            $size_type = $_POST['size_type'] ?? $input['size_type'] ?? null;
            if (!$container_number || !$size_type) {
                response(false, null, "container_number and size_type are required");
            }
            
            $iso_code = $_POST['iso_code'] ?? $input['iso_code'] ?? null;
            $cargo_type = $_POST['cargo_type'] ?? $input['cargo_type'] ?? null;
            $rfid_tag = $_POST['rfid_tag'] ?? $input['rfid_tag'] ?? null;
            $sscc_code = $_POST['sscc_code'] ?? $input['sscc_code'] ?? null;
            $gross_weight_kg = $_POST['gross_weight_kg'] ?? $input['gross_weight_kg'] ?? null;
            $owner_company = $_POST['owner_company'] ?? $input['owner_company'] ?? null;
            $seal_number = $_POST['seal_number'] ?? $input['seal_number'] ?? null;
            $customs_status = $_POST['customs_status'] ?? $input['customs_status'] ?? null;
            $block = $_POST['block'] ?? $input['block'] ?? null;
            $bay = $_POST['bay'] ?? $input['bay'] ?? null;
            $row = $_POST['row'] ?? $input['row'] ?? null;
            $tier = $_POST['tier'] ?? $input['tier'] ?? null;
            
            $stmt = $pdo->prepare("INSERT INTO containers (container_number, size_type, iso_code, cargo_type, rfid_tag, sscc_code, gross_weight_kg, owner_company, seal_number, customs_status, block, bay, row, tier, status, gate_in_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'in_yard', NOW())");
            $stmt->execute([$container_number, $size_type, $iso_code, $cargo_type, $rfid_tag, $sscc_code, $gross_weight_kg, $owner_company, $seal_number, $customs_status, $block, $bay, $row, $tier]);
            
            response(true, ["id" => $pdo->lastInsertId()], "Container added successfully");
            break;

        case 'update_container':
            $id = $_POST['id'] ?? $input['id'] ?? null;
            $container_number = $_POST['container_number'] ?? $input['container_number'] ?? null;
            
            if (!$id && !$container_number) {
                response(false, null, "id or container_number required");
            }
            
            $fields = ['block', 'bay', 'row', 'tier', 'status', 'customs_status'];
            $updates = [];
            $params = [];
            
            foreach ($fields as $field) {
                $val = $_POST[$field] ?? $input[$field] ?? null;
                if ($val !== null) {
                    $updates[] = "$field = ?";
                    $params[] = $val;
                }
            }
            
            if (empty($updates)) {
                response(false, null, "No fields to update");
            }
            
            if ($id) {
                $where = "id = ?";
                $params[] = $id;
            } else {
                $where = "container_number = ?";
                $params[] = $container_number;
            }
            
            $sql = "UPDATE containers SET " . implode(", ", $updates) . " WHERE $where";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            response(true, null, "Container updated successfully");
            break;

        case 'delete_container':
            $id = $_POST['id'] ?? $input['id'] ?? null;
            if (!$id) response(false, null, "id required");
            
            $stmt = $pdo->prepare("DELETE FROM containers WHERE id = ?");
            $stmt->execute([$id]);
            
            response(true, null, "Container deleted successfully");
            break;

        case 'gate_out_container':
            $id = $_POST['id'] ?? $input['id'] ?? null;
            $container_number = $_POST['container_number'] ?? $input['container_number'] ?? null;
            if (!$id && !$container_number) response(false, null, "id or container_number required");
            
            if ($id) {
                $stmt = $pdo->prepare("UPDATE containers SET status = 'gate_out', gate_out_time = NOW() WHERE id = ?");
                $stmt->execute([$id]);
            } else {
                $stmt = $pdo->prepare("UPDATE containers SET status = 'gate_out', gate_out_time = NOW() WHERE container_number = ?");
                $stmt->execute([$container_number]);
            }
            
            response(true, null, "Container gated out successfully");
            break;
            
        // ---------------------------------------------------------------------
        // TRUCK OPERATIONS
        // ---------------------------------------------------------------------
        case 'get_trucks':
            $status = $_GET['status'] ?? null;
            if ($status) {
                $stmt = $pdo->prepare("SELECT * FROM trucks WHERE status = ? ORDER BY id DESC");
                $stmt->execute([$status]);
            } else {
                $stmt = $pdo->query("SELECT * FROM trucks ORDER BY id DESC");
            }
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            response(true, $data, "Trucks fetched successfully", count($data));
            break;
            
        case 'add_truck':
            $license_plate = $_POST['license_plate'] ?? $input['license_plate'] ?? null;
            $driver_name = $_POST['driver_name'] ?? $input['driver_name'] ?? null;
            $company = $_POST['company'] ?? $input['company'] ?? null;
            $job_type = $_POST['job_type'] ?? $input['job_type'] ?? 'drop_off';
            $container_number = $_POST['container_number'] ?? $input['container_number'] ?? null;
            $do_number = $_POST['do_number'] ?? $input['do_number'] ?? null;
            $rfid_tag = $_POST['rfid_tag'] ?? $input['rfid_tag'] ?? null;
            
            if (!$license_plate || !$driver_name || !$company) {
                response(false, null, "license_plate, driver_name, and company are required");
            }
            
            $stmt = $pdo->prepare("INSERT INTO trucks (license_plate, driver_name, company, job_type, container_number, do_number, rfid_tag, status, gate_in_time) VALUES (?, ?, ?, ?, ?, ?, ?, 'queuing', NOW())");
            $stmt->execute([$license_plate, $driver_name, $company, $job_type, $container_number, $do_number, $rfid_tag]);
            
            // Also log to yard_events
            try {
                $jobDesc = ($job_type === 'pick_up') ? 'Ambil Kontainer (Pick-Up / Impor)' : 'Antar Kontainer (Drop-Off / Ekspor)';
                $evtStmt = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, notes) VALUES ('TRUCK_ARRIVAL', ?, ?)");
                $evtStmt->execute([$container_number, "Truk {$license_plate} ({$driver_name} - {$company}) tiba di Pre-Gate. Misi: {$jobDesc}."]);
            } catch (Exception $e) {}

            response(true, ["id" => $pdo->lastInsertId()], "Truck added successfully");
            break;
            
        case 'update_truck':
            $id = $_POST['id'] ?? $input['id'] ?? null;
            if (!$id) response(false, null, "id required");
            
            $status = $_POST['status'] ?? $input['status'] ?? null;
            $container_number = $_POST['container_number'] ?? $input['container_number'] ?? null;
            
            $updates = [];
            $params = [];
            if ($status !== null) {
                $updates[] = "status = ?";
                $params[] = $status;
            }
            if ($container_number !== null) {
                $updates[] = "container_number = ?";
                $params[] = $container_number;
            }
            
            if (empty($updates)) response(false, null, "No fields to update");
            
            $params[] = $id;
            $sql = "UPDATE trucks SET " . implode(", ", $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            response(true, null, "Truck updated successfully");
            break;
            
        case 'gate_out_truck':
            $id = $_POST['id'] ?? $input['id'] ?? null;
            if (!$id) response(false, null, "id required");
            
            $stmt = $pdo->prepare("UPDATE trucks SET status = 'gate_out', gate_out_time = NOW() WHERE id = ?");
            $stmt->execute([$id]);
            
            response(true, null, "Truck gated out successfully");
            break;
            
        // ---------------------------------------------------------------------
        // EQUIPMENT OPERATIONS
        // ---------------------------------------------------------------------
        case 'get_equipment':
            $stmt = $pdo->query("SELECT * FROM equipment ORDER BY id ASC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            response(true, $data, "Equipment fetched successfully", count($data));
            break;
            
        case 'update_equipment':
            $id = $_POST['id'] ?? $input['id'] ?? null;
            if (!$id) response(false, null, "id required");
            
            $fields = ['status', 'current_container', 'operator_name'];
            $updates = [];
            $params = [];
            
            foreach ($fields as $field) {
                $val = $_POST[$field] ?? $input[$field] ?? null;
                if ($val !== null) {
                    $updates[] = "$field = ?";
                    $params[] = $val;
                }
            }
            
            if (empty($updates)) response(false, null, "No fields to update");
            
            $params[] = $id;
            $sql = "UPDATE equipment SET " . implode(", ", $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            response(true, null, "Equipment updated successfully");
            break;
            
        // ---------------------------------------------------------------------
        // TRAIN OPERATIONS
        // ---------------------------------------------------------------------
        case 'get_trains':
            $stmt = $pdo->query("SELECT * FROM trains ORDER BY id DESC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            response(true, $data, "Trains fetched successfully", count($data));
            break;
            
        case 'add_train':
            $train_number = $_POST['train_number'] ?? $input['train_number'] ?? null;
            $origin = $_POST['origin'] ?? $input['origin'] ?? null;
            $destination = $_POST['destination'] ?? $input['destination'] ?? null;
            $status = $_POST['status'] ?? $input['status'] ?? 'scheduled';
            
            if (!$train_number || !$origin || !$destination) {
                response(false, null, "train_number, origin, and destination are required");
            }
            
            $stmt = $pdo->prepare("INSERT INTO trains (train_number, origin, destination, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$train_number, $origin, $destination, $status]);
            
            response(true, ["id" => $pdo->lastInsertId()], "Train added successfully");
            break;
            
        case 'update_train':
            $id = $_POST['id'] ?? $input['id'] ?? null;
            $status = $_POST['status'] ?? $input['status'] ?? null;
            if (!$id || !$status) response(false, null, "id and status are required");
            
            $stmt = $pdo->prepare("UPDATE trains SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            
            response(true, null, "Train updated successfully");
            break;
            
        // ---------------------------------------------------------------------
        // YARD EVENTS
        // ---------------------------------------------------------------------
        case 'get_events':
            $container_number = $_GET['container_number'] ?? null;
            if ($container_number) {
                $stmt = $pdo->prepare("SELECT * FROM yard_events WHERE container_number = ? ORDER BY id DESC LIMIT 50");
                $stmt->execute([$container_number]);
            } else {
                $stmt = $pdo->query("SELECT * FROM yard_events ORDER BY id DESC LIMIT 50");
            }
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            response(true, $data, "Events fetched successfully", count($data));
            break;
            
        case 'add_event':
            $event_type = $_POST['event_type'] ?? $input['event_type'] ?? null;
            $container_number = $_POST['container_number'] ?? $input['container_number'] ?? null;
            $description = $_POST['description'] ?? $input['description'] ?? null;
            $equipment_id = $_POST['equipment_id'] ?? $input['equipment_id'] ?? null;
            $truck_id = $_POST['truck_id'] ?? $input['truck_id'] ?? null;
            $train_id = $_POST['train_id'] ?? $input['train_id'] ?? null;
            
            if (!$event_type) response(false, null, "event_type is required");
            
            $stmt = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, notes, equipment_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$event_type, $container_number, $description, $equipment_id]);
            
            response(true, ["id" => $pdo->lastInsertId()], "Event added successfully");
            break;

        // ---------------------------------------------------------------------
        // SENSOR-INTEGRATED SIMULATION & LIFECYCLE CONTROLS
        // ---------------------------------------------------------------------
        case 'simulate_sensor_gate_in':
            $plate = $_POST['plate'] ?? $input['plate'] ?? 'B 9812 UIK';
            $container_no = $_POST['container'] ?? $input['container'] ?? 'TCKU8291042';
            $container_no = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $container_no));
            $weight_str = $_POST['weight'] ?? $input['weight'] ?? '32500';
            $gross_weight = (float)preg_replace('/[^0-9.]/', '', $weight_str);
            if ($gross_weight < 1000) $gross_weight = 32500.00;
            $company = $_POST['company'] ?? $input['company'] ?? 'PT Logistik Prima Internasional';
            $driver = $_POST['driver'] ?? $input['driver'] ?? 'Budi Hartono';
            $block = $_POST['block'] ?? $input['block'] ?? 'B';
            $is_reefer = (stripos($container_no, 'R') !== false || stripos($plate, '9144') !== false);
            if ($is_reefer) $block = 'REEFER';

            $pdo->beginTransaction();

            // 1. Cek atau Buat Truk
            $stmtTrk = $pdo->prepare("SELECT id FROM trucks WHERE license_plate = ? LIMIT 1");
            $stmtTrk->execute([$plate]);
            $trk = $stmtTrk->fetch(PDO::FETCH_ASSOC);

            if ($trk) {
                $updTrk = $pdo->prepare("UPDATE trucks SET status = 'in_yard', container_number = ?, gate_in_time = NOW(), gate_out_time = NULL WHERE id = ?");
                $updTrk->execute([$container_no, $trk['id']]);
                $truck_id = $trk['id'];
            } else {
                $rfid = 'RFID-TRK-' . rand(100, 999);
                $insTrk = $pdo->prepare("INSERT INTO trucks (license_plate, rfid_tag, driver_name, company, container_number, status, gate_in_time) VALUES (?, ?, ?, ?, ?, 'in_yard', NOW())");
                $insTrk->execute([$plate, $rfid, $driver, $company, $container_no]);
                $truck_id = $pdo->lastInsertId();
            }

            // 2. Cek atau Buat Kontainer
            $stmtCtr = $pdo->prepare("SELECT id FROM containers WHERE container_number = ? LIMIT 1");
            $stmtCtr->execute([$container_no]);
            $ctr = $stmtCtr->fetch(PDO::FETCH_ASSOC);

            if ($ctr) {
                $updCtr = $pdo->prepare("UPDATE containers SET status = 'in_yard', gross_weight_kg = ?, block = ?, gate_in_time = NOW(), gate_out_time = NULL WHERE id = ?");
                $updCtr->execute([$gross_weight, $block, $ctr['id']]);
                $container_id = $ctr['id'];
            } else {
                $rfid_ctr = 'RFID-CTR-' . rand(100, 999);
                $sscc = '0038991234' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
                $seal = 'SEAL-ID-DJBC-' . date('Ymd') . '-' . rand(100, 999);
                $cargo = $is_reefer ? 'reefer' : 'dry';
                $size = $is_reefer ? '20FT REEFER' : '40FT HIGH CUBE';
                $iso = $is_reefer ? '22R1' : '45G1';

                $insCtr = $pdo->prepare("INSERT INTO containers (container_number, iso_code, size_type, cargo_type, rfid_tag, sscc_code, gross_weight_kg, owner_company, seal_number, customs_status, block, bay, row, tier, gate_in_time, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'SPPB_CLEARED', ?, '01', '01', '01', NOW(), 'in_yard')");
                $insCtr->execute([$container_no, $iso, $size, $cargo, $rfid_ctr, $sscc, $gross_weight, $company, $seal, $block]);
                $container_id = $pdo->lastInsertId();
            }

            // 3. Catat ke yard_events
            $evtMsg = "GATE-IN LANE 1: ANPR capture {$plate} MATCHED. OCR ISO 6346 {$container_no} VERIFIED. Timbangan 80T: {$gross_weight} kg (VGM SOLAS PASS). Barrier Gate OPEN. Alokasi Yard: Blok {$block}.";
            $stmtEvt = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, to_block, to_bay, to_row, to_tier, operator_name, billable_amount, notes) VALUES ('GATE_IN', ?, 'GATE-IN-L1', ?, '01', '01', '01', 'Petugas Gerbang & Timbangan', 50000.00, ?)");
            $stmtEvt->execute([$container_no, $block, $evtMsg]);

            $pdo->commit();

            response(true, [
                'truck_id' => $truck_id,
                'container_id' => $container_id,
                'plate' => $plate,
                'container' => $container_no,
                'gross_weight' => $gross_weight,
                'block' => $block,
                'status' => 'in_yard',
                'gate_in_time' => date('Y-m-d H:i:s')
            ], "Gate-In terintegrasi sensor berhasil dicatat ke database!");
            break;

        case 'advance_truck_status':
            $id = $_POST['id'] ?? $input['id'] ?? null;
            $next_status = $_POST['next_status'] ?? $input['next_status'] ?? null;
            if (!$id || !$next_status) response(false, null, "id and next_status are required");

            $stmtTrk = $pdo->prepare("SELECT * FROM trucks WHERE id = ?");
            $stmtTrk->execute([$id]);
            $trk = $stmtTrk->fetch(PDO::FETCH_ASSOC);
            if (!$trk) response(false, null, "Truk tidak ditemukan");

            $is_pickup = ($trk['job_type'] === 'pick_up');

            if ($next_status === 'gate_out') {
                $upd = $pdo->prepare("UPDATE trucks SET status = 'gate_out', gate_out_time = NOW() WHERE id = ?");
                $upd->execute([$id]);

                // Jika truk bertugas Pick-Up, maka kontainer ikut keluar dari dry port!
                if ($is_pickup && !empty($trk['container_number'])) {
                    $updCtr = $pdo->prepare("UPDATE containers SET status = 'gate_out', gate_out_time = NOW() WHERE container_number = ?");
                    $updCtr->execute([$trk['container_number']]);
                }

                // Log event
                $stmtEvt = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, notes) VALUES ('GATE_OUT', ?, ?)");
                $actNotes = $is_pickup 
                    ? "Truk {$trk['license_plate']} GATE-OUT membawa kontainer {$trk['container_number']} keluar dari dry port (Pick-Up Selesai)."
                    : "Truk {$trk['license_plate']} GATE-OUT sasis kosong setelah menurunkan kontainer di yard (Drop-Off Selesai).";
                $stmtEvt->execute([$trk['container_number'], $actNotes]);
                $msg = "Truk {$trk['license_plate']} berhasil Gate-Out (" . ($is_pickup ? "Membawa Kontainer {$trk['container_number']} Keluar" : "Sasis Kosong") . ").";
            } elseif ($next_status === 'queuing') {
                // Truk masuk antrian lagi untuk ritase baru
                $upd = $pdo->prepare("UPDATE trucks SET status = 'queuing', gate_in_time = NULL, gate_out_time = NULL WHERE id = ?");
                $upd->execute([$id]);
                $stmtEvt = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, notes) VALUES ('TRUCK_REQUEUED', ?, ?)");
                $stmtEvt->execute([$trk['container_number'], "Truk {$trk['license_plate']} kembali ke antrian pra-gerbang untuk ritase penugasan berikutnya."]);
                $msg = "Truk {$trk['license_plate']} kembali ke antrian pra-gerbang (Siap Masuk Lagi).";
            } elseif ($next_status === 'loading') {
                $upd = $pdo->prepare("UPDATE trucks SET status = 'loading' WHERE id = ?");
                $upd->execute([$id]);

                $liftType = $is_pickup ? 'LIFT_ON' : 'LIFT_OFF';
                $liftNotes = $is_pickup 
                    ? "Reach Stacker melakukan LIFT-ON: menaikkan kontainer {$trk['container_number']} dari yard ke sasis truk {$trk['license_plate']} (Proses Pick-Up)."
                    : "Reach Stacker melakukan LIFT-OFF: menurunkan kontainer {$trk['container_number']} dari sasis truk {$trk['license_plate']} ke blok yard (Proses Drop-Off).";
                
                $stmtEvt = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, notes) VALUES (?, ?, ?)");
                $stmtEvt->execute([$liftType, $trk['container_number'], $liftNotes]);
                $msg = "Truk {$trk['license_plate']} proses " . ($is_pickup ? "Lift-On (Ambil Kontainer)" : "Lift-Off (Bongkar Kontainer)") . ".";
            } elseif ($next_status === 'in_yard') {
                $upd = $pdo->prepare("UPDATE trucks SET status = 'in_yard', gate_in_time = IFNULL(gate_in_time, NOW()) WHERE id = ?");
                $upd->execute([$id]);

                // Jika drop-off, pastikan kontainer status in_yard
                if (!$is_pickup && !empty($trk['container_number'])) {
                    $updCtr = $pdo->prepare("UPDATE containers SET status = 'in_yard', gate_in_time = IFNULL(gate_in_time, NOW()) WHERE container_number = ?");
                    $updCtr->execute([$trk['container_number']]);
                }

                $stmtEvt = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, notes) VALUES ('GATE_IN', ?, ?)");
                $stmtEvt->execute([$trk['container_number'], "Truk {$trk['license_plate']} masuk ke terminal yard (" . ($is_pickup ? "Ambil Barang / Pick-Up" : "Antar Barang / Drop-Off") . ")."]);
                $msg = "Truk {$trk['license_plate']} status diubah ke In-Yard (" . ($is_pickup ? "Pick-Up" : "Drop-Off") . ").";
            } else {
                response(false, null, "Status tidak valid");
            }

            response(true, ['id' => $id, 'status' => $next_status], $msg);
            break;

        case 'advance_train_status':
            $status = $_POST['status'] ?? $input['status'] ?? 'arrived';
            $train_id = $_POST['id'] ?? $input['id'] ?? null;

            if ($train_id) {
                $stmtT = $pdo->prepare("SELECT * FROM trains WHERE id = ?");
                $stmtT->execute([$train_id]);
            } else {
                $stmtT = $pdo->query("SELECT * FROM trains ORDER BY id ASC LIMIT 1");
            }
            $train = $stmtT->fetch(PDO::FETCH_ASSOC);
            if (!$train) response(false, null, "Kereta tidak ditemukan di database");

            $tid = $train['id'];
            $code = $train['train_code'];

            if ($status === 'arrived') {
                $upd = $pdo->prepare("UPDATE trains SET status = 'arrived', arrival_time = NOW() WHERE id = ?");
                $upd->execute([$tid]);
                $stmtEvt = $pdo->prepare("INSERT INTO yard_events (event_type, notes) VALUES ('RAIL_ARRIVAL', ?)");
                $stmtEvt->execute(["Kereta {$code} TIBA di Track-01 Rail Siding CIDP. Frauscher Axle Counter mendeteksi 126 gandar."]);
                $msg = "Kereta {$code} tiba di Rail Siding Track-01 (Sensor Axle Counter Aktif).";
            } elseif ($status === 'loading') {
                $upd = $pdo->prepare("UPDATE trains SET status = 'loading', loaded_wagons = 30 WHERE id = ?");
                $upd->execute([$tid]);
                $stmtEvt = $pdo->prepare("INSERT INTO yard_events (event_type, notes) VALUES ('RAIL_LOADING', ?)");
                $stmtEvt->execute(["Bongkar-muat kontainer ke rangkaian {$code} oleh RMGC Rail Crane & Reach Stacker."]);
                $msg = "Kereta {$code} dalam proses bongkar-muat kargo (Loading).";
            } elseif ($status === 'departed') {
                $upd = $pdo->prepare("UPDATE trains SET status = 'departed', departure_time = NOW() WHERE id = ?");
                $upd->execute([$tid]);
                $stmtEvt = $pdo->prepare("INSERT INTO yard_events (event_type, notes) VALUES ('RAIL_DEPARTURE', ?)");
                $stmtEvt->execute(["Kereta {$code} BERANGKAT menuju Pelabuhan Tanjung Priok (JICT). Siding Track-01 Clear."]);
                $msg = "Kereta {$code} telah diberangkatkan menuju Tanjung Priok.";
            } elseif ($status === 'scheduled') {
                $upd = $pdo->prepare("UPDATE trains SET status = 'scheduled', arrival_time = NULL, departure_time = NULL, loaded_wagons = 0 WHERE id = ?");
                $upd->execute([$tid]);
                $stmtEvt = $pdo->prepare("INSERT INTO yard_events (event_type, notes) VALUES ('RAIL_SCHEDULED', ?)");
                $stmtEvt->execute(["Jadwal dinas baru kereta {$code} disiapkan untuk ritase berikutnya."]);
                $msg = "Kereta {$code} di-reset ke jadwal dinas baru (Scheduled).";
            } else {
                response(false, null, "Status kereta tidak valid");
            }

            response(true, ['id' => $tid, 'status' => $status, 'train_code' => $code], $msg);
            break;

        // ---------------------------------------------------------------------
        // DASHBOARD STATS
        // ---------------------------------------------------------------------
        case 'get_dashboard_stats':
            $stats = [];
            
            // Containers
            $stats['total_containers'] = (int)$pdo->query("SELECT COUNT(*) FROM containers")->fetchColumn();
            $stats['containers_in_yard'] = (int)$pdo->query("SELECT COUNT(*) FROM containers WHERE status = 'in_yard'")->fetchColumn();
            $stats['containers_in_transit'] = (int)$pdo->query("SELECT COUNT(*) FROM containers WHERE status = 'in_transit'")->fetchColumn();
            $stats['containers_gate_out'] = (int)$pdo->query("SELECT COUNT(*) FROM containers WHERE status = 'gate_out'")->fetchColumn();
            
            // Trucks
            $stats['total_trucks'] = (int)$pdo->query("SELECT COUNT(*) FROM trucks")->fetchColumn();
            $stats['trucks_in_yard'] = (int)$pdo->query("SELECT COUNT(*) FROM trucks WHERE status = 'in_yard'")->fetchColumn();
            $stats['trucks_queuing'] = (int)$pdo->query("SELECT COUNT(*) FROM trucks WHERE status = 'queuing'")->fetchColumn();
            $stats['trucks_gate_out'] = (int)$pdo->query("SELECT COUNT(*) FROM trucks WHERE status = 'gate_out'")->fetchColumn();
            
            // Equipment
            $stats['total_equipment'] = (int)$pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
            $stats['equipment_operating'] = (int)$pdo->query("SELECT COUNT(*) FROM equipment WHERE status IN ('operating', 'active', 'busy')")->fetchColumn();
            
            // Events Today
            $stats['total_events_today'] = (int)$pdo->query("SELECT COUNT(*) FROM yard_events WHERE DATE(created_at) = CURDATE()")->fetchColumn();
            
            // Trains
            $stats['total_trains'] = (int)$pdo->query("SELECT COUNT(*) FROM trains")->fetchColumn();
            
            response(true, $stats, "Dashboard stats fetched successfully");
            break;
            
        default:
            response(false, null, "Invalid or missing action parameter");
            break;
    }
} catch (Exception $e) {
    response(false, null, "Server error: " . $e->getMessage());
}
