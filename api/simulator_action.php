<?php
// =============================================================================
// FILE: api/simulator_action.php
// FUNGSI: REST API Gateway untuk Simulasi 3D Virtual Terminal CIDP
// Mendukung aksi operasional Reach Stacker, Gate-In VGM, dan Alih Muat KA
// =============================================================================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../connection.php';

// Ambil input JSON atau GET/POST parameter
$input_raw = file_get_contents('php://input');
$input = json_decode($input_raw, true) ?? [];
$action = $_GET['action'] ?? $input['action'] ?? $_POST['action'] ?? $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        // ---------------------------------------------------------------------
        // 1. GET CURRENT STATE: Ambil seluruh data aktif untuk viewport 3D
        // ---------------------------------------------------------------------
        case 'get_state':
            // Kontainer di Yard dan di Moda Angkutan
            $stmtC = $pdo->query("SELECT id, container_number, iso_code, size_type, cargo_type, rfid_tag, sscc_code, 
                                         gross_weight_kg, owner_company, seal_number, customs_status, 
                                         block, bay, row, tier, status, gate_in_time, created_at 
                                  FROM containers 
                                  ORDER BY block ASC, bay ASC, row ASC, tier ASC");
            $containers = $stmtC->fetchAll();

            // Armada Alat Berat (RS & RTG)
            $stmtE = $pdo->query("SELECT id, equipment_id, equipment_type, brand_model, operator_name, operator_id, 
                                         status, gps_x, gps_y, current_container, last_block, fuel_percent, hours_today 
                                  FROM equipment");
            $equipment = $stmtE->fetchAll();

            // Armada Truk
            $stmtT = $pdo->query("SELECT id, license_plate, rfid_tag, driver_name, company, container_number, 
                                         status, gate_in_time, gate_out_time 
                                  FROM trucks 
                                  ORDER BY id ASC");
            $trucks = $stmtT->fetchAll();

            // Jadwal Rangkaian Kereta Api Logistik
            $stmtR = $pdo->query("SELECT id, train_code, origin, destination, total_wagons, loaded_wagons, 
                                         status, arrival_time 
                                  FROM trains");
            $trains = $stmtR->fetchAll();

            // 10 Riwayat Kejadian Terkini
            $stmtEv = $pdo->query("SELECT id, event_type, container_number, equipment_id, 
                                          from_block, from_bay, from_row, from_tier, 
                                          to_block, to_bay, to_row, to_tier, 
                                          operator_name, billable_amount, notes, created_at 
                                   FROM yard_events 
                                   ORDER BY id DESC LIMIT 10");
            $events = $stmtEv->fetchAll();

            // Hitung statistik ringkas
            $total_containers = count($containers);
            $in_yard = 0;
            $dry = 0;
            $reefer = 0;
            $dg = 0;
            $empty = 0;

            foreach ($containers as $c) {
                if ($c['status'] === 'in_yard') $in_yard++;
                if ($c['cargo_type'] === 'dry') $dry++;
                elseif ($c['cargo_type'] === 'reefer') $reefer++;
                elseif ($c['cargo_type'] === 'dg') $dg++;
                elseif ($c['cargo_type'] === 'empty') $empty++;
            }

            echo json_encode([
                'success' => true,
                'timestamp' => date('Y-m-d H:i:s'),
                'containers' => $containers,
                'equipment' => $equipment,
                'trucks' => $trucks,
                'trains' => $trains,
                'recent_events' => $events,
                'stats' => [
                    'total' => $total_containers,
                    'in_yard' => $in_yard,
                    'dry' => $dry,
                    'reefer' => $reefer,
                    'dg' => $dg,
                    'empty' => $empty,
                    'occupancy_pct' => round(($in_yard / 80) * 100, 1) // Asumsi kapasitas prototype 80 slot
                ]
            ]);
            break;

        // ---------------------------------------------------------------------
        // 2. MOVE CONTAINER: Relokasi kontainer oleh Reach Stacker
        // ---------------------------------------------------------------------
        case 'move_container':
            $container_id = (int)($input['container_id'] ?? $_REQUEST['container_id'] ?? 0);
            $container_number = trim($input['container_number'] ?? $_REQUEST['container_number'] ?? '');
            $equipment_id = trim($input['equipment_id'] ?? $_REQUEST['equipment_id'] ?? 'RS-02');
            
            $to_block = strtoupper(trim($input['to_block'] ?? $_REQUEST['to_block'] ?? 'B'));
            $to_bay   = str_pad(trim($input['to_bay'] ?? $_REQUEST['to_bay'] ?? '01'), 2, '0', STR_PAD_LEFT);
            $to_row   = str_pad(trim($input['to_row'] ?? $_REQUEST['to_row'] ?? '01'), 2, '0', STR_PAD_LEFT);
            $to_tier  = str_pad(trim($input['to_tier'] ?? $_REQUEST['to_tier'] ?? '01'), 2, '0', STR_PAD_LEFT);

            // Validasi keberadaan kontainer
            if ($container_id > 0) {
                $stmtFind = $pdo->prepare("SELECT * FROM containers WHERE id = ?");
                $stmtFind->execute([$container_id]);
            } else {
                $stmtFind = $pdo->prepare("SELECT * FROM containers WHERE container_number = ?");
                $stmtFind->execute([$container_number]);
            }
            $targetContainer = $stmtFind->fetch();

            if (!$targetContainer) {
                if (!empty($container_number)) {
                    $from_block = strtoupper(trim($input['from_block'] ?? $_REQUEST['from_block'] ?? 'A'));
                    $from_bay   = str_pad(trim($input['from_bay'] ?? $_REQUEST['from_bay'] ?? '01'), 2, '0', STR_PAD_LEFT);
                    $from_row   = str_pad(trim($input['from_row'] ?? $_REQUEST['from_row'] ?? '01'), 2, '0', STR_PAD_LEFT);
                    $from_tier  = str_pad(trim($input['from_tier'] ?? $_REQUEST['from_tier'] ?? '01'), 2, '0', STR_PAD_LEFT);
                    $owner_comp = trim($input['owner_company'] ?? $_REQUEST['owner_company'] ?? 'Ocean Carrier');
                    $cargo_t    = trim($input['cargo_type'] ?? $_REQUEST['cargo_type'] ?? 'dry');
                    $stmtIns = $pdo->prepare("INSERT INTO containers 
                        (container_number, iso_code, size_type, cargo_type, owner_company, gross_weight_kg, block, bay, row, tier, status)
                        VALUES (?, '45G1', '40FT HIGH CUBE', ?, ?, 28500, ?, ?, ?, ?, 'in_yard')");
                    $stmtIns->execute([$container_number, $cargo_t, $owner_comp, $from_block, $from_bay, $from_row, $from_tier]);
                    $stmtFind = $pdo->prepare("SELECT * FROM containers WHERE container_number = ?");
                    $stmtFind->execute([$container_number]);
                    $targetContainer = $stmtFind->fetch();
                }
            }

            if (!$targetContainer) {
                echo json_encode(['success' => false, 'message' => 'Kontainer tidak ditemukan dalam sistem.']);
                exit;
            }

            $cid = $targetContainer['id'];
            $cnum = $targetContainer['container_number'];
            $from_block = $targetContainer['block'];
            $from_bay   = $targetContainer['bay'];
            $from_row   = $targetContainer['row'];
            $from_tier  = $targetContainer['tier'];

            // Cek apakah slot tujuan sudah terisi oleh kontainer lain yang berstatus in_yard
            $stmtCheck = $pdo->prepare("SELECT id, container_number FROM containers 
                                        WHERE block = ? AND bay = ? AND row = ? AND tier = ? 
                                          AND status = 'in_yard' AND id != ?");
            $stmtCheck->execute([$to_block, $to_bay, $to_row, $to_tier, $cid]);
            $occupied = $stmtCheck->fetch();

            if ($occupied) {
                echo json_encode([
                    'success' => false, 
                    'message' => "Slot {$to_block}-{$to_bay}-{$to_row}-{$to_tier} sudah terisi oleh kontainer {$occupied['container_number']}! Pilih slot lain."
                ]);
                exit;
            }

            // Ambil nama operator alat
            $stmtEq = $pdo->prepare("SELECT operator_name FROM equipment WHERE equipment_id = ?");
            $stmtEq->execute([$equipment_id]);
            $eqRow = $stmtEq->fetch();
            $operator_name = $eqRow ? $eqRow['operator_name'] : 'Agus Setiawan (RS-02)';

            // Mulai transaksi database
            $pdo->beginTransaction();

            // 1. Update koordinat kontainer
            $stmtUpdC = $pdo->prepare("UPDATE containers 
                                       SET block = ?, bay = ?, row = ?, tier = ?, status = 'in_yard' 
                                       WHERE id = ?");
            $stmtUpdC->execute([$to_block, $to_bay, $to_row, $to_tier, $cid]);

            // 2. Update status alat berat (mengangkut dan memindahkan)
            $stmtUpdE = $pdo->prepare("UPDATE equipment 
                                       SET last_block = ?, status = 'operating', current_container = ? 
                                       WHERE equipment_id = ?");
            $stmtUpdE->execute([$to_block, $cnum, $equipment_id]);

            // 3. Catat audit trail di yard_events
            $billable_amount = 250000; // Biaya Lift-Off / Relokasi resmi konsultan
            $stmtLog = $pdo->prepare("INSERT INTO yard_events 
                (event_type, container_number, equipment_id, from_block, from_bay, from_row, from_tier, 
                 to_block, to_bay, to_row, to_tier, operator_name, billable_amount, notes) 
                VALUES ('RELOCATION', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtLog->execute([
                $cnum, $equipment_id,
                $from_block, $from_bay, $from_row, $from_tier,
                $to_block, $to_bay, $to_row, $to_tier,
                $operator_name, $billable_amount,
                "Relokasi box oleh {$equipment_id} ({$operator_name}) via 3D Virtual Simulator"
            ]);
            $event_id = $pdo->lastInsertId();

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => "Kontainer {$cnum} berhasil dipindahkan oleh {$equipment_id} ke {$to_block}-{$to_bay}-{$to_row}-{$to_tier}",
                'event_id' => $event_id,
                'container' => [
                    'id' => $cid,
                    'container_number' => $cnum,
                    'from' => "{$from_block}-{$from_bay}-{$from_row}-{$from_tier}",
                    'to' => "{$to_block}-{$to_bay}-{$to_row}-{$to_tier}",
                    'block' => $to_block,
                    'bay' => $to_bay,
                    'row' => $to_row,
                    'tier' => $to_tier
                ],
                'equipment' => [
                    'equipment_id' => $equipment_id,
                    'operator_name' => $operator_name,
                    'status' => 'operating'
                ],
                'billing' => [
                    'charge_name' => 'Jasa Lo-Lo / Relokasi Yard',
                    'amount' => $billable_amount
                ]
            ]);
            break;

        // ---------------------------------------------------------------------
        // 3. GATE-IN TRUCK & TIMBANGAN VGM SOLAS
        // ---------------------------------------------------------------------
        // ---------------------------------------------------------------------
        // 3. GATE-IN TRUCK (DROP-OFF & PICK-UP) & TIMBANGAN VGM SOLAS
        // ---------------------------------------------------------------------
        case 'gate_in':
            $req_type = $_GET['type'] ?? 'drop_off'; // drop_off atau pick_up

            // Prioritaskan mengambil truk antrian dari database jika ada yang sesuai
            $stmtQ = $pdo->prepare("SELECT * FROM trucks WHERE status = 'queuing' AND job_type = ? ORDER BY id ASC LIMIT 1");
            $stmtQ->execute([$req_type]);
            $queuingTrk = $stmtQ->fetch(PDO::FETCH_ASSOC);

            if (!$queuingTrk) {
                // Ambil sembarang antrian jika tipe spesifik tidak ada
                $stmtQ2 = $pdo->query("SELECT * FROM trucks WHERE status = 'queuing' ORDER BY id ASC LIMIT 1");
                $queuingTrk = $stmtQ2->fetch(PDO::FETCH_ASSOC);
            }

            if ($queuingTrk) {
                $randPlate = $queuingTrk['license_plate'];
                $randDriver = $queuingTrk['driver_name'];
                $randCompany = $queuingTrk['company'];
                $jobType = $queuingTrk['job_type'] ?? $req_type;
                $containerNo = $queuingTrk['container_number'];
                $truck_id = $queuingTrk['id'];
            } else {
                $plates = ['B 9481 UEK', 'B 7712 SCK', 'D 9921 XY', 'L 8182 PO', 'B 3341 JKT'];
                $drivers = ['Sholehudin', 'Hendra Kusuma', 'Bambang Irawan', 'Rahmat Hidayat', 'Yanto Subagyo'];
                $companies = ['PT Samudera Logistik Prima', 'PT Lintas Benua Cepat', 'PT Trans Harapan Logistik'];
                
                $randPlate = $plates[array_rand($plates)];
                $randDriver = $drivers[array_rand($drivers)];
                $randCompany = $companies[array_rand($companies)];
                $jobType = $req_type;
                $containerNo = ($jobType === 'drop_off') ? 'MSKU' . rand(1000000, 9999999) : null;
                $truck_id = null;
            }

            $is_pickup = ($jobType === 'pick_up');

            if ($is_pickup) {
                // Truk Pick-Up: Masuk dengan sasis kosong untuk mengambil kargo di dry port
                $tare = rand(10500, 11800);
                $gross = $tare; // Truk kosong
                $net_vgm = 0;
                $vgm_status = 'EMPTY CHASSIS (TARE RECORDED) - PICK-UP PERMITTED';
            } else {
                // Truk Drop-Off: Masuk membawa kontainer penuh (Laden)
                $gross = rand(28500, 32500);
                $tare  = rand(11000, 12500);
                $net_vgm = $gross - $tare;
                $vgm_status = 'SOLAS VERIFIED (PASS)';
            }

            $pdo->beginTransaction();

            if ($truck_id) {
                $upd = $pdo->prepare("UPDATE trucks SET status = 'in_yard', gate_in_time = NOW() WHERE id = ?");
                $upd->execute([$truck_id]);
            } else {
                $ins = $pdo->prepare("INSERT INTO trucks (license_plate, rfid_tag, driver_name, company, job_type, container_number, status, gate_in_time) 
                                      VALUES (?, ?, ?, ?, ?, ?, 'in_yard', NOW())");
                $rfid = 'RFID-TRK-' . rand(100, 999);
                $ins->execute([$randPlate, $rfid, $randDriver, $randCompany, $jobType, $containerNo]);
                $truck_id = $pdo->lastInsertId();
            }

            $allocated_slot = null;
            $target_pickup_container = null;

            if ($is_pickup) {
                // Pick-Up: Ambil kontainer impor dari Blok B yang statusnya in_yard
                $stmtPick = $pdo->prepare("SELECT * FROM containers WHERE block = 'B' AND status = 'in_yard' ORDER BY tier DESC, bay ASC, row ASC LIMIT 1");
                $stmtPick->execute();
                $target_pickup_container = $stmtPick->fetch(PDO::FETCH_ASSOC);

                if ($target_pickup_container) {
                    $containerNo = $target_pickup_container['container_number'];
                    $updTrk = $pdo->prepare("UPDATE trucks SET container_number = ? WHERE id = ?");
                    $updTrk->execute([$containerNo, $truck_id]);
                } else {
                    // Fallback jika blok B kosong
                    $containerNo = 'MSKU' . rand(2000000, 8999999);
                }
            } else {
                // Drop-Off: Alokasikan slot kosong di Blok A (Laden Export)
                $stmtOcc = $pdo->query("SELECT bay, row, tier FROM containers WHERE block = 'A' AND status = 'in_yard'")->fetchAll(PDO::FETCH_ASSOC);
                $occupied = [];
                foreach ($stmtOcc as $o) {
                    $occupied[$o['bay'].'-'.$o['row'].'-'.$o['tier']] = true;
                }

                $target_bay = '02';
                $target_row = '02';
                $target_tier = '01';

                for ($b = 1; $b <= 4; $b++) {
                    $sb = str_pad($b, 2, '0', STR_PAD_LEFT);
                    for ($r = 1; $r <= 8; $r++) {
                        $sr = str_pad($r, 2, '0', STR_PAD_LEFT);
                        for ($t = 1; $t <= 4; $t++) {
                            $st = str_pad($t, 2, '0', STR_PAD_LEFT);
                            if (!isset($occupied["{$sb}-{$sr}-{$st}"])) {
                                $target_bay = $sb;
                                $target_row = $sr;
                                $target_tier = $st;
                                break 3;
                            }
                        }
                    }
                }

                $allocated_slot = [
                    'block' => 'A',
                    'bay' => $target_bay,
                    'row' => $target_row,
                    'tier' => $target_tier
                ];

                // Jika Drop-Off dan ada kontainer, daftarkan kontainer di database pada slot Blok A
                if ($containerNo) {
                    $stmtC = $pdo->prepare("SELECT id FROM containers WHERE container_number = ? LIMIT 1");
                    $stmtC->execute([$containerNo]);
                    $existingC = $stmtC->fetch(PDO::FETCH_ASSOC);

                    if (!$existingC) {
                        $rfidC = 'RFID-CTR-' . rand(100, 999);
                        $sscc = '0038991234' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
                        $seal = 'SEAL-ID-DJBC-' . date('Ymd') . '-' . rand(100, 999);
                        $insCtr = $pdo->prepare("INSERT INTO containers 
                            (container_number, iso_code, size_type, cargo_type, rfid_tag, sscc_code, gross_weight_kg, owner_company, seal_number, customs_status, block, bay, row, tier, gate_in_time, status) 
                            VALUES (?, '45G1', '40FT HIGH CUBE', 'dry', ?, ?, ?, ?, ?, 'SPPB_CLEARED', 'A', ?, ?, ?, NOW(), 'in_yard')");
                        $insCtr->execute([$containerNo, $rfidC, $sscc, $gross, $randCompany, $seal, $target_bay, $target_row, $target_tier]);
                    } else {
                        $updCtr = $pdo->prepare("UPDATE containers SET block = 'A', bay = ?, row = ?, tier = ?, status = 'in_yard', gate_in_time = NOW() WHERE id = ?");
                        $updCtr->execute([$target_bay, $target_row, $target_tier, $existingC['id']]);
                    }
                }
            }

            // Catat event Gate-In & Biaya Jasa Timbang VGM
            $misiText = $is_pickup ? 'PICK-UP (Ambil Barang)' : 'DROP-OFF (Antar Barang)';
            $stmtLog = $pdo->prepare("INSERT INTO yard_events 
                (event_type, container_number, equipment_id, operator_name, billable_amount, notes) 
                VALUES ('GATE_IN', ?, 'GATE-01', 'Petugas Gerbang Timbangan', 50000, ?)");
            $stmtLog->execute([$containerNo ?? 'TRUK-KOSONG', "Truk {$randPlate} ({$randDriver}) Gate-In [{$misiText}]. Timbang: Gross {$gross}kg, Tare {$tare}kg. Status: {$vgm_status}."]);
            $event_id = $pdo->lastInsertId();

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => "Truk {$randPlate} berhasil Gate-In melalui Gate 1 ({$misiText})!",
                'truck' => [
                    'id' => $truck_id,
                    'license_plate' => $randPlate,
                    'driver_name' => $randDriver,
                    'company' => $randCompany,
                    'job_type' => $jobType,
                    'container_number' => $containerNo,
                    'gross_weight' => $gross,
                    'tare_weight' => $tare,
                    'vgm_net' => $net_vgm,
                    'vgm_status' => $vgm_status
                ],
                'allocated_slot' => $allocated_slot,
                'target_container' => $target_pickup_container,
                'billing' => [
                    'charge_name' => 'Jasa Jembatan Timbang VGM SOLAS',
                    'amount' => 50000
                ]
            ]);
            break;

        case 'gate_out':
            $stmtY = $pdo->query("SELECT * FROM trucks WHERE status IN ('in_yard', 'loading') ORDER BY id ASC LIMIT 1");
            $trk = $stmtY->fetch(PDO::FETCH_ASSOC);
            if (!$trk) {
                echo json_encode(['success' => false, 'message' => 'Tidak ada armada truk di dalam yard yang siap Gate-Out.']);
                break;
            }

            $is_pickup = ($trk['job_type'] === 'pick_up');
            $pdo->beginTransaction();

            $upd = $pdo->prepare("UPDATE trucks SET status = 'gate_out', gate_out_time = NOW() WHERE id = ?");
            $upd->execute([$trk['id']]);

            if ($is_pickup && !empty($trk['container_number'])) {
                $updC = $pdo->prepare("UPDATE containers SET status = 'gate_out', gate_out_time = NOW() WHERE container_number = ?");
                $updC->execute([$trk['container_number']]);
            }

            $stmtLog = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, notes) VALUES ('GATE_OUT', ?, ?)");
            $actNotes = $is_pickup 
                ? "Truk {$trk['license_plate']} GATE-OUT membawa kontainer {$trk['container_number']} keluar dari dry port (Pick-Up Selesai)."
                : "Truk {$trk['license_plate']} GATE-OUT sasis kosong setelah menurunkan kontainer di yard (Drop-Off Selesai).";
            $stmtLog->execute([$trk['container_number'], $actNotes]);

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => "Truk {$trk['license_plate']} berhasil Gate-Out (" . ($is_pickup ? "Membawa Kontainer Keluar" : "Sasis Kosong") . ")!",
                'truck' => $trk
            ]);
            break;

        // ---------------------------------------------------------------------
        // 4. RAIL DISCHARGE: Alih Muat Box dari Rangkaian Kereta Api oleh RTG
        // ---------------------------------------------------------------------
        case 'rail_discharge':
            $container_codes = ['TEMU9921820', 'KLINE7721839', 'MSKU4419283', 'ONEU8829104'];
            $randBox = $container_codes[array_rand($container_codes)];
            
            // Cari slot kosong di Blok A untuk ditumpuk
            $stmtOccupied = $pdo->query("SELECT bay, row, tier FROM containers WHERE block = 'A' AND status = 'in_yard'")->fetchAll();
            $occupiedSlots = [];
            foreach ($stmtOccupied as $o) {
                $occupiedSlots[$o['bay'].'-'.$o['row'].'-'.$o['tier']] = true;
            }

            $target_bay = '02';
            $target_row = '01';
            $target_tier = '01';

            // Cari slot bebas
            for ($b = 1; $b <= 5; $b++) {
                $sb = str_pad($b, 2, '0', STR_PAD_LEFT);
                for ($r = 1; $r <= 3; $r++) {
                    $sr = str_pad($r, 2, '0', STR_PAD_LEFT);
                    for ($t = 1; $t <= 3; $t++) {
                        $st = str_pad($t, 2, '0', STR_PAD_LEFT);
                        if (!isset($occupiedSlots["{$sb}-{$sr}-{$st}"])) {
                            $target_bay = $sb;
                            $target_row = $sr;
                            $target_tier = $st;
                            break 3;
                        }
                    }
                }
            }

            $pdo->beginTransaction();

            // Masukkan kontainer baru dari kereta
            $insC = $pdo->prepare("INSERT INTO containers 
                (container_number, iso_code, size_type, cargo_type, rfid_tag, sscc_code, gross_weight_kg, owner_company, block, bay, row, tier, status) 
                VALUES (?, '42G1', '40FT HIGH CUBE', 'dry', ?, ?, 26400, 'PT Kereta Api Logistik', 'A', ?, ?, ?, 'in_yard')");
            $insC->execute([
                $randBox, 
                'E280' . rand(100000000000, 999999999999), 
                '(00)38991234' . rand(10000000, 99999999),
                $target_bay, $target_row, $target_tier
            ]);
            $cid = $pdo->lastInsertId();

            // Update kereta api status
            $pdo->query("UPDATE trains SET loaded_wagons = GREATEST(0, loaded_wagons - 1) WHERE train_code = 'KA-LOG-JKT-SMG'");

            // Catat event alih muat KA
            $stmtLog = $pdo->prepare("INSERT INTO yard_events 
                (event_type, container_number, equipment_id, to_block, to_bay, to_row, to_tier, operator_name, billable_amount, notes) 
                VALUES ('RAIL_UNLOAD', ?, 'RTG-01', 'A', ?, ?, ?, 'Joko Susilo (RTG-01)', 350000, ?)");
            $stmtLog->execute([
                $randBox, $target_bay, $target_row, $target_tier, 
                "Bongkar kontainer dari Rangkaian KA-LOG-JKT-SMG oleh RTG-01 ke Blok A Bay {$target_bay}"
            ]);

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => "Kontainer {$randBox} dari KA Logistik berhasil dibongkar oleh RTG-01 ke Blok A-{$target_bay}-{$target_row}-{$target_tier}!",
                'container' => [
                    'id' => $cid,
                    'container_number' => $randBox,
                    'block' => 'A',
                    'bay' => $target_bay,
                    'row' => $target_row,
                    'tier' => $target_tier
                ],
                'equipment' => [
                    'equipment_id' => 'RTG-01',
                    'operator_name' => 'Joko Susilo'
                ],
                'billing' => [
                    'charge_name' => 'Jasa Alih Muat KA Logistik (Rail Stevedoring)',
                    'amount' => 350000
                ]
            ]);
            break;

        // ---------------------------------------------------------------------
        // 5. RESET SIMULATION: Kembalikan kondisi data demo
        // ---------------------------------------------------------------------
        case 'reset_simulation':
            $pdo->beginTransaction();

            // Reset posisi kontainer utama ke sebaran demo awal
            $defaults = [
                ['id' => 1, 'block' => 'A', 'bay' => '01', 'row' => '01', 'tier' => '01'],
                ['id' => 2, 'block' => 'A', 'bay' => '01', 'row' => '01', 'tier' => '02'],
                ['id' => 3, 'block' => 'A', 'bay' => '01', 'row' => '02', 'tier' => '01'],
                ['id' => 4, 'block' => 'B', 'bay' => '05', 'row' => '03', 'tier' => '01'],
                ['id' => 5, 'block' => 'B', 'bay' => '05', 'row' => '03', 'tier' => '02'],
                ['id' => 6, 'block' => 'B', 'bay' => '06', 'row' => '01', 'tier' => '01'],
                ['id' => 7, 'block' => 'C', 'bay' => '10', 'row' => '01', 'tier' => '01'],
                ['id' => 8, 'block' => 'C', 'bay' => '10', 'row' => '01', 'tier' => '02'],
                ['id' => 9, 'block' => 'C', 'bay' => '10', 'row' => '02', 'tier' => '01'],
                ['id' => 10, 'block' => 'C', 'bay' => '11', 'row' => '01', 'tier' => '01'],
                ['id' => 11, 'block' => 'REEFER', 'bay' => '01', 'row' => '01', 'tier' => '01'],
                ['id' => 12, 'block' => 'REEFER', 'bay' => '01', 'row' => '01', 'tier' => '02'],
                ['id' => 13, 'block' => 'REEFER', 'bay' => '02', 'row' => '01', 'tier' => '01'],
                ['id' => 14, 'block' => 'REEFER', 'bay' => '02', 'row' => '02', 'tier' => '01'],
                ['id' => 15, 'block' => 'DG', 'bay' => '01', 'row' => '01', 'tier' => '01'],
                ['id' => 16, 'block' => 'DG', 'bay' => '01', 'row' => '02', 'tier' => '01'],
                ['id' => 17, 'block' => 'EMPTY', 'bay' => '01', 'row' => '01', 'tier' => '01'],
                ['id' => 18, 'block' => 'EMPTY', 'bay' => '01', 'row' => '01', 'tier' => '02'],
                ['id' => 19, 'block' => 'EMPTY', 'bay' => '01', 'row' => '01', 'tier' => '03'],
            ];

            $stmtU = $pdo->prepare("UPDATE containers SET block = ?, bay = ?, row = ?, tier = ?, status = 'in_yard' WHERE id = ?");
            foreach ($defaults as $d) {
                $stmtU->execute([$d['block'], $d['bay'], $d['row'], $d['tier'], $d['id']]);
            }

            // Hapus kontainer tambahan di luar ID 19
            $pdo->query("DELETE FROM containers WHERE id > 19");

            // Reset posisi equipment
            $pdo->query("UPDATE equipment SET status = 'idle', current_container = NULL, last_block = 'A' WHERE equipment_id = 'RS-01'");
            $pdo->query("UPDATE equipment SET status = 'idle', current_container = NULL, last_block = 'B' WHERE equipment_id = 'RS-02'");
            $pdo->query("UPDATE equipment SET status = 'idle', current_container = NULL, last_block = 'REEFER' WHERE equipment_id = 'RS-03'");
            $pdo->query("UPDATE equipment SET status = 'operating', current_container = NULL, last_block = 'RAIL' WHERE equipment_id = 'RTG-01'");

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Simulasi berhasil direset ke konfigurasi awal demo.'
            ]);
            break;

        // ---------------------------------------------------------------------
        // 6. HW-01: KAMERA ANPR GERBANG (Hikvision DS-TCG406-E)
        // ---------------------------------------------------------------------
        case 'test_hw01_anpr':
        case 'test_anpr':
            $plates = ['B 9481 UEK', 'B 7712 SCK', 'D 9921 XY', 'L 8182 PO', 'B 3341 JKT', 'B 9204 TKL'];
            $randPlate = $plates[array_rand($plates)];
            $accuracy = rand(991, 999) / 10;

            $stmt = $pdo->prepare("INSERT INTO yard_events (event_type, equipment_id, operator_name, billable_amount, notes) 
                                   VALUES ('ANPR_CAPTURE', 'HW-01-ANPR', 'ANPR Auto Trigger', 50000, ?)");
            $stmt->execute(["[HW-01 ANPR] Kamera mendeteksi truk {$randPlate} (Akurasi {$accuracy}%). Pas Masuk Truk diterbitkan."]);

            echo json_encode([
                'success' => true,
                'hw_id' => 'HW-01',
                'name' => 'Kamera ANPR Gerbang',
                'message' => "HW-01 ANPR membaca plat {$randPlate} (Tingkat Keyakinan {$accuracy}%)",
                'plate' => $randPlate,
                'confidence' => $accuracy . '%',
                'lane' => 'Inbound Lane 1',
                'billing' => [
                    'charge_name' => 'Pas Masuk Truk (Gate Pass)',
                    'amount' => 50000
                ]
            ]);
            break;

        // ---------------------------------------------------------------------
        // 6B. HW-02: PORTAL OCR KONTAINER ISO 6346 (Hikvision iDS-TCV300-A6I)
        // ---------------------------------------------------------------------
        case 'test_hw02_ocr':
        case 'test_ocr':
            $boxPrefixes = ['MSKU', 'TCLU', 'TEMU', 'CSQU', 'FCIU', 'HLXU', 'CMAU'];
            $randBox = $boxPrefixes[array_rand($boxPrefixes)] . rand(1000000, 9999999);
            $accuracy = rand(989, 998) / 10;

            $stmt = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, operator_name, billable_amount, notes) 
                                   VALUES ('OCR_CAPTURE', ?, 'HW-02-OCR', 'Portal OCR System', 0, ?)");
            $stmt->execute([$randBox, "[HW-02 OCR] Portal OCR memindai kontainer {$randBox} (ISO 42G1). Check Digit Valid."]);

            echo json_encode([
                'success' => true,
                'hw_id' => 'HW-02',
                'name' => 'Portal OCR Peti Kemas',
                'message' => "HW-02 Portal OCR memindai {$randBox} (Valid ISO 6346)",
                'container_number' => $randBox,
                'iso_code' => '42G1 (40ft High Cube Dry)',
                'check_digit' => 'VALID (0-Error)',
                'confidence' => $accuracy . '%',
                'demurrage' => 'Free Time 5 Hari Dimulai'
            ]);
            break;

        // ---------------------------------------------------------------------
        // 6C. HW-04: LONG-RANGE UHF RFID READER (Hopeland 20m ISO 18000-6C)
        // ---------------------------------------------------------------------
        case 'test_hw04_rfid':
        case 'test_rfid':
            $rfidTags = ['E280117000000001', 'E280117000000002', 'E280117000000003', 'E280117000000004'];
            $drivers = ['Sholehudin (PT Samudera Logistik Prima)', 'Hendra Kusuma (PT Lintas Benua Cepat)', 'Bambang Irawan (PT Trans Harapan Logistik)'];
            $randRfid = $rfidTags[array_rand($rfidTags)];
            $randDriver = $drivers[array_rand($drivers)];

            $stmt = $pdo->prepare("INSERT INTO yard_events (event_type, equipment_id, operator_name, billable_amount, notes) 
                                   VALUES ('RFID_AUTH', 'HW-04-RFID', 'Hopeland Reader', 0, ?)");
            $stmt->execute(["[HW-04 RFID] UHF RFID 20m mendeteksi tag armada {$randRfid} ({$randDriver}). Saldo e-Wallet diverifikasi."]);

            echo json_encode([
                'success' => true,
                'hw_id' => 'HW-04',
                'name' => 'Long-Range UHF RFID Reader',
                'message' => "HW-04 RFID membaca tag armada {$randRfid}",
                'rfid_tag' => $randRfid,
                'driver' => $randDriver,
                'e_wallet_status' => 'AUTO-DEBIT APPROVED (Saldo Cukup)',
                'signal_rssi' => '-54 dBm (Sangat Kuat)'
            ]);
            break;

        // ---------------------------------------------------------------------
        // 6D. HW-05: VARIABLE MESSAGE SIGN (VMS) GATE DISPLAY (Chipshow Outdoor LED)
        // ---------------------------------------------------------------------
        case 'test_hw05_vms':
        case 'test_vms':
            $destBlocks = ['BLOK A - BAY 04', 'BLOK B - BAY 08', 'BLOK C - BAY 11', 'REEFER R-02 #14'];
            $dest = $destBlocks[array_rand($destBlocks)];
            $ledMsg = "LANE 1: TRUK MENUJU {$dest}";

            $stmt = $pdo->prepare("INSERT INTO yard_events (event_type, equipment_id, operator_name, billable_amount, notes) 
                                   VALUES ('VMS_DIRECTIVE', 'HW-05-VMS', 'Traffic Control AI', 0, ?)");
            $stmt->execute(["[HW-05 VMS] LED Display mengarahkan supir: '{$ledMsg}'. Estimasi TAT terpangkas 60%."]);

            echo json_encode([
                'success' => true,
                'hw_id' => 'HW-05',
                'name' => 'Variable Message Sign (VMS) Gate',
                'message' => "HW-05 VMS menampilkan rute: {$ledMsg}",
                'display_text' => $ledMsg,
                'target_block' => $dest,
                'tat_impact' => 'Pangkas Turnaround Time (TAT) 45 mnt -> 18 mnt (-60%)'
            ]);
            break;

        // ---------------------------------------------------------------------
        // 6E. HW-06: INDUSTRIAL FANLESS EDGE AI PC (Advantech ARK-3532)
        // ---------------------------------------------------------------------
        case 'test_hw06_edge':
        case 'test_edge':
            $latency = rand(112, 148) / 10;

            $stmt = $pdo->prepare("INSERT INTO yard_events (event_type, equipment_id, operator_name, billable_amount, notes) 
                                   VALUES ('EDGE_INTERLOCK', 'HW-06-EDGE', 'Advantech ARK-3532', 0, ?)");
            $stmt->execute(["[HW-06 Edge PC] Edge AI memproses verifikasi dalam {$latency}ms. Relay GPIO mengirim pulsa 1.2 detik membuka palang."]);

            echo json_encode([
                'success' => true,
                'hw_id' => 'HW-06',
                'name' => 'Industrial Fanless Edge AI PC',
                'message' => "HW-06 Edge PC ARK-3532 memproses interlock dalam {$latency} ms. Palang Terbuka!",
                'device_model' => 'Advantech ARK-3532 Fanless (IP40)',
                'latency' => $latency . ' ms',
                'relay_status' => 'PULSE ACTIVE (1.2s OPEN)',
                'barrier_state' => 'BARRIER OPENED'
            ]);
            break;

        // ---------------------------------------------------------------------
        // 6F. HW-07: DGPS / RTK GNSS RECEIVER (CHCNAV CGI-610 <2cm)
        // ---------------------------------------------------------------------
        case 'test_hw07_rtk':
        case 'test_rtk':
            $satCount = rand(16, 20);
            $accuracyCm = rand(12, 18) / 10;
            $slots = ['B-08-03-02', 'B-06-02-01', 'A-04-01-03', 'C-10-02-02', 'R-02-01-01'];
            $randSlot = $slots[array_rand($slots)];

            $stmt = $pdo->prepare("INSERT INTO yard_events (event_type, equipment_id, operator_name, billable_amount, notes) 
                                   VALUES ('RTK_FIX', 'HW-07-RTK', 'CHCNAV CGI-610', 0, ?)");
            $stmt->execute(["[HW-07 RTK] DGPS mengunci koordinat akurasi {$accuracyCm} cm ({$satCount} Satelit). Posisi Slot: {$randSlot}."]);

            echo json_encode([
                'success' => true,
                'hw_id' => 'HW-07',
                'name' => 'DGPS / RTK GNSS Receiver',
                'message' => "HW-07 RTK GNSS mengunci slot {$randSlot} (Akurasi {$accuracyCm} cm, {$satCount} Sat)",
                'status' => 'FIX',
                'accuracy' => "< {$accuracyCm} cm",
                'satellites' => "{$satCount} Satellites (GPS + BeiDou)",
                'slot_3d' => $randSlot,
                'risk_elimination' => 'Eliminasi 100% Salah Letak Box (Misplacement Zero)'
            ]);
            break;

        // ---------------------------------------------------------------------
        // 6G. HW-08: SPREADER TWISTLOCK & LOAD CELL SENSOR KIT (Bromma)
        // ---------------------------------------------------------------------
        case 'test_hw08_twistlock':
        case 'test_twistlock':
            $tonnage = rand(245, 305) / 10;
            $billable = 250000;

            $stmt = $pdo->prepare("INSERT INTO yard_events (event_type, equipment_id, operator_name, billable_amount, notes) 
                                   VALUES ('SPREADER_LOCK', 'HW-08-BROMMA', 'Bromma Twistlock Kit', 250000, ?)");
            $stmt->execute(["[HW-08 Twistlock] 4 Pin mengunci sempurna di corner casting. Load cell mencatat {$tonnage} Ton. Jasa Lo-Lo diterbitkan."]);

            echo json_encode([
                'success' => true,
                'hw_id' => 'HW-08',
                'name' => 'Spreader Twistlock & Load Cell Sensor Kit',
                'message' => "HW-08 Spreader Twistlock LOCKED & Load Cell: {$tonnage} Ton (Siap Angkat)",
                'twistlock_state' => 'LOCKED (4/4 Pin Terkunci)',
                'load_tonnage' => "{$tonnage} Ton",
                'hoist_interlock' => 'PERMITTED (Aman Diangkat)',
                'billing' => [
                    'charge_name' => 'Jasa Lo-Lo Lift-Off Stevedoring',
                    'amount' => $billable
                ]
            ]);
            break;

        // ---------------------------------------------------------------------
        // 6H. TEST ANPR & OCR SENSOR KOMBINASI (Backward Compatibility)
        // ---------------------------------------------------------------------
        case 'test_anpr_ocr':
            $plates = ['B 9481 UEK', 'B 7712 SCK', 'D 9921 XY', 'L 8182 PO', 'B 3341 JKT', 'B 9204 TKL'];
            $boxPrefixes = ['MSKU', 'TCLU', 'TEMU', 'CSQU', 'FCIU', 'HLXU', 'CMAU'];
            
            $randPlate = $plates[array_rand($plates)];
            $randBox = $boxPrefixes[array_rand($boxPrefixes)] . rand(1000000, 9999999);
            $accuracy = rand(988, 999) / 10;

            // Catat log
            $stmt = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, operator_name, billable_amount, notes) 
                                   VALUES ('GATE_SCAN', ?, 'GATE-OCR-01', 'Sistem Otomasi Gerbang', 50000, ?)");
            $stmt->execute([$randBox, "ANPR: {$randPlate} (Akurasi {$accuracy}%) & Portal OCR: {$randBox} (Valid ISO 6346)"]);
            
            echo json_encode([
                'success' => true,
                'message' => "ANPR mendeteksi {$randPlate} ({$accuracy}%) dan OCR membaca {$randBox}",
                'anpr' => [
                    'hardware' => 'Hikvision DS-TCG406-E',
                    'plate' => $randPlate,
                    'confidence' => $accuracy . '%',
                    'status' => 'REGISTERED'
                ],
                'ocr' => [
                    'hardware' => 'Hikvision iDS-TCV300-A6I',
                    'container_number' => $randBox,
                    'iso_code' => '42G1 (40ft High Cube)',
                    'check_digit' => 'VALID'
                ],
                'billing' => [
                    'charge_name' => 'Pas Masuk Truk (Gate Pass)',
                    'amount' => 50000
                ]
            ]);
            break;

        // ---------------------------------------------------------------------
        // 7. TEST VGM WEIGHBRIDGE: Simulasi penimbangan truk 80 Ton
        // ---------------------------------------------------------------------
        case 'test_hw03_vgm':
        case 'test_vgm':
            $gross = rand(29500, 34500);
            $tare = rand(10500, 11500);
            $net_vgm = $gross - $tare;
            $cert_num = 'VGM-CIDP-' . date('Ymd') . '-' . rand(1000, 9999);

            $stmt = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, operator_name, billable_amount, notes) 
                                   VALUES ('VGM_WEIGH', 'VGM-CERTIFIED', 'SCALE-80T-01', 'Operator Timbangan VGM', 120000, ?)");
            $stmt->execute(["Penimbangan Jembatan Timbang 80T: Gross {$gross}kg, Tare {$tare}kg, Net VGM {$net_vgm}kg. Sertifikat #{$cert_num} diterbitkan."]);

            echo json_encode([
                'success' => true,
                'message' => "Penimbangan Jembatan Timbang 80T selesai! Net VGM: " . number_format($net_vgm, 0, ',', '.') . " kg (SOLAS PASSED)",
                'vgm' => [
                    'hardware' => 'Fangda Electronic Truck Scale 80 Ton',
                    'certificate_no' => $cert_num,
                    'gross_weight' => $gross,
                    'tare_weight' => $tare,
                    'net_vgm' => $net_vgm,
                    'solas_status' => 'COMPLIANT'
                ],
                'billing' => [
                    'charge_name' => 'Biaya Jasa Sertifikasi VGM SOLAS',
                    'amount' => 120000
                ]
            ]);
            break;

        // ---------------------------------------------------------------------
        // 8. TEST REEFER SOCKET: Simulasi Modbus RTU Telemetri Daya & Suhu
        // ---------------------------------------------------------------------
        case 'test_hw09_reefer':
        case 'test_reefer':
            $temps = [-20.4, -20.2, -19.8, -20.1, -19.5];
            $current_temp = $temps[array_rand($temps)];
            $volts = rand(385, 392) + (rand(1, 9) / 10);
            $amps = rand(26, 31) + (rand(1, 9) / 10);
            $power_kw = round(($volts * $amps * 1.732 * 0.85) / 1000, 2);

            $stmt = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, operator_name, billable_amount, notes) 
                                   VALUES ('REEFER_CHECK', 'TEMU4819203', 'RACK-R02-P14', 'SCADA Reefer Monitor', 35000, ?)");
            $stmt->execute(["Smart Socket R-02 #14: Tegangan {$volts}V, Arus {$amps}A, Daya {$power_kw}kW, Suhu {$current_temp}°C (OPTIMAL)."]);

            echo json_encode([
                'success' => true,
                'hw_id' => 'HW-09',
                'name' => 'Smart Reefer Socket Modbus RTU',
                'message' => "Sensor Smart Socket Marechal: Suhu {$current_temp}°C, Daya {$power_kw} kW (Optimal)",
                'reefer' => [
                    'hardware' => 'Marechal Smart Receptacle 380V/32A',
                    'rack_plug' => 'Reefer Rack R-02 Plug #14',
                    'temperature' => $current_temp . '°C',
                    'voltage' => $volts . ' V',
                    'current' => $amps . ' A',
                    'power' => $power_kw . ' kW',
                    'status' => 'COLD_CHAIN_NORMAL'
                ],
                'billing' => [
                    'charge_name' => 'Reefer Electricity & Monitoring Fee (1 Jam)',
                    'amount' => 35000
                ]
            ]);
            break;

        // ---------------------------------------------------------------------
        // 9. TEST AXLE COUNTER: Simulasi sensor gandar KA logistik
        // ---------------------------------------------------------------------
        case 'test_hw10_axle':
        case 'test_axle':
            $total_axles = 126; // 1 Lokomotif 6 as + 30 Gerbong 120 as
            $train_id = 'KA 2518 (Tj. Priok - Cikarang)';

            $stmt = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, operator_name, billable_amount, notes) 
                                   VALUES ('RAIL_AXLE_CHECK', 'KA-2518-WAGONS', 'AXLE-RSR123', 'Petugas Siding Intermodal', 0, ?)");
            $stmt->execute(["Sensor Frauscher RSR123 mendeteksi 126 gandar utuh pada Siding Track 1. KA 2518 siap alih muat."]);

            echo json_encode([
                'success' => true,
                'hw_id' => 'HW-10',
                'name' => 'Frauscher Wheel Sensor / Axle Counter',
                'message' => "Frauscher Axle Counter memverifikasi 126 as roda! Rangkaian KA 2518 utuh & aman.",
                'axle' => [
                    'hardware' => 'Frauscher RSR123 Wheel Sensor',
                    'train' => $train_id,
                    'axle_count' => $total_axles,
                    'locomotive' => 'CC 206 13 42 (6 As Roda)',
                    'wagons' => '30 Gerbong PPCW (120 As Roda)',
                    'integrity' => 'VERIFIED (100% COMPLETE)',
                    'safety_level' => 'SIL 4'
                ],
                'billing' => [
                    'charge_name' => 'Intermodal Rail Manifest Verified',
                    'amount' => 0
                ]
            ]);
            break;

        // ---------------------------------------------------------------------
        // 10. TEST E-SEAL: Simulasi verifikasi Smart E-Seal pabean CEISA 4.0
        // ---------------------------------------------------------------------
        case 'test_hw11_eseal':
        case 'test_eseal':
            $seal_num = 'JT701-' . rand(100000, 999999);
            $sppb_num = 'SPPB-' . rand(10000, 99999) . '/KPU.01/2026';

            $stmt = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, operator_name, billable_amount, notes) 
                                   VALUES ('CUSTOMS_CLEARANCE', 'E-SEAL-VERIFIED', 'SEAL-JT701', 'Bea Cukai KPU Priok/Cikarang', 0, ?)");
            $stmt->execute(["Jointech JT701 GPS Smart E-Seal #{$seal_num} diverifikasi via RFID nirkabel. Kawat utuh (INTACT). SPPB #{$sppb_num} terbit otomatis."]);

            echo json_encode([
                'success' => true,
                'message' => "Smart E-Seal terverifikasi INTACT! SPPB Bea Cukai Jalur Hijau terbit otomatis.",
                'eseal' => [
                    'hardware' => 'Jointech JT701 GPS/GSM Smart Lock',
                    'seal_id' => $seal_num,
                    'wire_status' => 'INTACT (NO TAMPER)',
                    'battery' => '96%',
                    'sppb_number' => $sppb_num,
                    'channel' => 'JALUR HIJAU (AUTOMATIC CLEARANCE)'
                ],
                'billing' => [
                    'charge_name' => 'Customs Bond Released (Jaminan Pabean Bebas)',
                    'amount' => 0
                ]
            ]);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Aksi tidak dikenali. Gunakan action=get_state, move_container, gate_in, rail_discharge, atau reset_simulation.'
            ]);
            break;
    }
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
