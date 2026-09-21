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

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../connection.php';

// Ambil input JSON atau GET/POST parameter
$input_raw = file_get_contents('php://input');
$input = json_decode($input_raw, true) ?? [];
$action = $_GET['action'] ?? $input['action'] ?? $_POST['action'] ?? '';

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
            $container_id = (int)($input['container_id'] ?? $_POST['container_id'] ?? 0);
            $container_number = trim($input['container_number'] ?? $_POST['container_number'] ?? '');
            $equipment_id = trim($input['equipment_id'] ?? $_POST['equipment_id'] ?? 'RS-02');
            
            $to_block = strtoupper(trim($input['to_block'] ?? $_POST['to_block'] ?? 'B'));
            $to_bay   = str_pad(trim($input['to_bay'] ?? $_POST['to_bay'] ?? '01'), 2, '0', STR_PAD_LEFT);
            $to_row   = str_pad(trim($input['to_row'] ?? $_POST['to_row'] ?? '01'), 2, '0', STR_PAD_LEFT);
            $to_tier  = str_pad(trim($input['to_tier'] ?? $_POST['to_tier'] ?? '01'), 2, '0', STR_PAD_LEFT);

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
        case 'gate_in':
            $plates = ['B 9481 UEK', 'B 7712 SCK', 'D 9921 XY', 'L 8182 PO', 'B 3341 JKT'];
            $drivers = ['Sholehudin', 'Hendra Kusuma', 'Bambang Irawan', 'Rahmat Hidayat', 'Yanto Subagyo'];
            $companies = ['PT Samudera Logistik Prima', 'PT Lintas Benua Cepat', 'PT Trans Harapan Logistik'];
            
            $randPlate = $plates[array_rand($plates)];
            $randDriver = $drivers[array_rand($drivers)];
            $randCompany = $companies[array_rand($companies)];
            
            // Hitung bobot jembatan timbang acak realistis
            $gross = rand(28500, 32500);
            $tare  = rand(11000, 12500);
            $net_vgm = $gross - $tare;

            $pdo->beginTransaction();

            // Cek apakah truk sudah ada di DB
            $stmtTrk = $pdo->prepare("SELECT id FROM trucks WHERE license_plate = ? LIMIT 1");
            $stmtTrk->execute([$randPlate]);
            $existTrk = $stmtTrk->fetch();

            if ($existTrk) {
                $upd = $pdo->prepare("UPDATE trucks SET status = 'in_yard', gate_in_time = NOW() WHERE id = ?");
                $upd->execute([$existTrk['id']]);
                $truck_id = $existTrk['id'];
            } else {
                $ins = $pdo->prepare("INSERT INTO trucks (license_plate, rfid_tag, driver_name, company, status, gate_in_time) 
                                      VALUES (?, ?, ?, ?, 'in_yard', NOW())");
                $rfid = 'RFID-TRK-' . rand(100, 999);
                $ins->execute([$randPlate, $rfid, $randDriver, $randCompany]);
                $truck_id = $pdo->lastInsertId();
            }

            // Catat event Gate-In & Biaya Jasa Timbang VGM
            $stmtLog = $pdo->prepare("INSERT INTO yard_events 
                (event_type, container_number, equipment_id, operator_name, billable_amount, notes) 
                VALUES ('GATE_IN', 'INBOUND-TRUCK', 'GATE-01', 'Petugas Gerbang Timbangan', 50000, ?)");
            $stmtLog->execute(["Truk {$randPlate} ({$randDriver}) Gate-In. Timbang VGM SOLAS: Gross {$gross}kg, Tare {$tare}kg, Net {$net_vgm}kg (PASS)."]);
            $event_id = $pdo->lastInsertId();

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => "Truk {$randPlate} berhasil Gate-In melalui Gate 1!",
                'truck' => [
                    'id' => $truck_id,
                    'license_plate' => $randPlate,
                    'driver_name' => $randDriver,
                    'company' => $randCompany,
                    'gross_weight' => $gross,
                    'tare_weight' => $tare,
                    'vgm_net' => $net_vgm,
                    'vgm_status' => 'SOLAS VERIFIED (PASS)'
                ],
                'billing' => [
                    'charge_name' => 'Jasa Jembatan Timbang VGM SOLAS',
                    'amount' => 50000
                ]
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
