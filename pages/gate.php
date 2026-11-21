<?php
// =============================================================================
// MODUL: MANAJEMEN GATE & INFRASTRUKTUR HARDWARE DRY PORT
// File: pages/gate.php
// Proyek: Conclusion Intermodal Dry Port (CIDP) 35 Ha — Yard Management System
// PIC: Armansyah Muchtarrom (Hardware & Infrastructure Specialist)
// =============================================================================

require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../api/preadvice_helper.php';
require_once __DIR__ . '/../api/twin_state_helper.php';
if (isset($pdo) && $pdo instanceof PDO) {
    ensure_integration_schema($pdo);
    cidp_ensure_gate_schema($pdo);
}
$gate_lanes_catalog = cidp_gate_lane_catalog();
$inbound_lanes_list  = array_values(array_filter($gate_lanes_catalog, fn($l) => $l['direction'] === 'IN'));
$outbound_lanes_list = array_values(array_filter($gate_lanes_catalog, fn($l) => $l['direction'] === 'OUT'));
$vgm_lanes_list      = array_values(array_filter($inbound_lanes_list, fn($l) => !empty($l['has_weighbridge'])));

// Handle Form Aksi POST (Dual-Clearance Gate Out Release)
$gate_alert = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['gate_action'])) {
    if ($_POST['gate_action'] === 'release_gate_out') {
        $truck_id = intval($_POST['truck_id'] ?? 0);
        $plate = trim($_POST['license_plate'] ?? '');
        $ctr_num = strtoupper(trim($_POST['container_number'] ?? ''));
        $req_out_lane = trim($_POST['lane_id'] ?? $_POST['gate_out_lane'] ?? '');

        if (isset($pdo)) {
            try {
                // 1. Validasi Pabean (SPPB_CLEARED)
                $customsCleared = true;
                $customsStatus = 'SPPB_CLEARED';
                $ctrCargo = 'dry';
                $ctrBlock = '';
                if (!empty($ctr_num)) {
                    $stmtCheckC = $pdo->prepare("SELECT customs_status, cargo_type, block FROM containers WHERE container_number = ? LIMIT 1");
                    $stmtCheckC->execute([$ctr_num]);
                    $cData = $stmtCheckC->fetch(PDO::FETCH_ASSOC);
                    $customsStatus = $cData['customs_status'] ?? 'PENDING';
                    $ctrCargo = $cData['cargo_type'] ?? 'dry';
                    $ctrBlock = $cData['block'] ?? '';
                    $customsCleared = ($cData && $customsStatus === 'SPPB_CLEARED');
                }

                // 2. Validasi Billing (Tidak boleh ada satupun faktur PENDING / UNPAID untuk kontainer ini)
                $billingPaid = true;
                $billingStatus = 'NO_INVOICE';
                if (!empty($ctr_num)) {
                    $stmtCheckB = $pdo->prepare("SELECT COUNT(*) FROM billing_invoices WHERE container_number = ? AND payment_status <> 'PAID'");
                    $stmtCheckB->execute([$ctr_num]);
                    $unpaidCnt = (int)$stmtCheckB->fetchColumn();
                    if ($unpaidCnt > 0) {
                        $billingPaid = false;
                        $billingStatus = "PENDING ({$unpaidCnt} faktur belum lunas)";
                    } else {
                        $billingPaid = true;
                        $billingStatus = 'PAID';
                    }
                }

                if (!empty($ctr_num) && (!$customsCleared || !$billingPaid)) {
                    $reasons = [];
                    if (!$customsCleared) $reasons[] = "Bea Cukai belum SPPB_CLEARED (Status: $customsStatus)";
                    if (!$billingPaid) $reasons[] = "Faktur Billing ERP belum lunas (Status: $billingStatus)";
                    $gate_alert = [
                        'type' => 'error',
                        'title' => 'Gate-Out Terkunci (Dual-Clearance Interlock)',
                        'msg' => "Armada <strong>$plate</strong> ($ctr_num) ditahan di gerbang keluar: " . implode(' &amp; ', $reasons) . ". Selesaikan validasi di modul Bea Cukai dan/atau Billing terlebih dahulu."
                    ];
                } else {
                    $out_lane = cidp_pick_gate_lane($pdo, 'OUT', [
                        'lane'       => $req_out_lane,
                        'cargo_type' => $ctrCargo,
                        'block'      => $ctrBlock,
                        'job_type'   => !empty($ctr_num) ? 'pick_up' : 'drop_off',
                    ]);

                    // Update trucks status to gate_out
                    $stmtUpT = $pdo->prepare("UPDATE trucks SET status = 'gate_out', gate_out_lane = ?, gate_out_time = NOW() WHERE id = ? OR license_plate = ?");
                    $stmtUpT->execute([$out_lane, $truck_id, $plate]);

                    // Update container status if present
                    if (!empty($ctr_num)) {
                        $stmtUpC = $pdo->prepare("UPDATE containers SET status = 'gate_out', gate_out_time = NOW() WHERE container_number = ?");
                        $stmtUpC->execute([$ctr_num]);
                        $pdo->prepare("UPDATE preadvice SET status = 'ARRIVED' WHERE container_number = ? OR truck_plate = ?")->execute([$ctr_num, $plate]);
                    }

                    // Log audit event dengan kode lajur spesifik (OUT-01..OUT-04)
                    $stmtLog = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, operator_name, notes, created_at) VALUES ('GATE_OUT', ?, ?, 'Petugas Gate Outbound', ?, NOW())");
                    $stmtLog->execute([$ctr_num ?: '-', $out_lane, "[{$out_lane}] Rilis Gate Out armada $plate membawa kontainer $ctr_num. Validasi Pabean: SPPB Lolos, Billing: Lunas."]);

                    get_live_twin_state($pdo, true);

                    $gate_alert = [
                        'type' => 'success',
                        'title' => "Rilis Gate Out Sukses — Palang {$out_lane} Terbuka",
                        'msg' => "Armada truk <strong>$plate</strong> dengan kontainer <strong>$ctr_num</strong> berhasil diproses keluar melalui <strong>{$out_lane}</strong>. Palang keluar {$out_lane} terbuka otomatis."
                    ];
                }
            } catch (Exception $e) {
                $gate_alert = ['type' => 'error', 'title' => 'Gagal Rilis Gate Out', 'msg' => $e->getMessage()];
            }
        }
    } elseif ($_POST['gate_action'] === 'trigger_anpr_gate_in') {
        $plate = strtoupper(trim($_POST['license_plate'] ?? 'B 9812 UIK'));
        $ctr_num = strtoupper(trim($_POST['container_number'] ?? 'MSKU9821450'));
        $lane_raw = trim($_POST['lane_id'] ?? 'IN-01');
        $driver = trim($_POST['driver_name'] ?? 'Bambang Supriyadi');
        $company = trim($_POST['company'] ?? 'PT Samudera Logistik');

        if (isset($pdo)) {
            try {
                $lane = cidp_pick_gate_lane($pdo, 'IN', ['lane' => $lane_raw]);

                // 1. Cek atau tambahkan truk
                $stmtCheck = $pdo->prepare("SELECT id FROM trucks WHERE license_plate = ? LIMIT 1");
                $stmtCheck->execute([$plate]);
                $exTruck = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                if ($exTruck) {
                    $stmtUp = $pdo->prepare("UPDATE trucks SET status = 'in_yard', gate_lane = ?, gate_in_time = NOW(), destination = 'Yard Blok A' WHERE id = ?");
                    $stmtUp->execute([$lane, $exTruck['id']]);
                } else {
                    $stmtIns = $pdo->prepare("INSERT INTO trucks (license_plate, driver_name, company, container_number, target_block, status, gate_lane, job_type, destination, gate_in_time) VALUES (?, ?, ?, ?, 'A', 'in_yard', ?, 'drop_off', 'Yard Blok A', NOW())");
                    $stmtIns->execute([$plate, $driver, $company, $ctr_num ?: null, $lane]);
                }

                // 2. Update atau masukkan status kontainer & akrual biaya
                if (!empty($ctr_num)) {
                    $stmtExC = $pdo->prepare("SELECT id FROM containers WHERE container_number = ? LIMIT 1");
                    $stmtExC->execute([$ctr_num]);
                    if ($stmtExC->fetchColumn()) {
                        $stmtUpC = $pdo->prepare("UPDATE containers SET status = 'in_yard', block = COALESCE(block, 'A'), bay = COALESCE(bay, 1), `row` = COALESCE(`row`, 1), tier = COALESCE(tier, 1), gate_in_time = COALESCE(gate_in_time, NOW()) WHERE container_number = ?");
                        $stmtUpC->execute([$ctr_num]);
                    } else {
                        $stmtInC = $pdo->prepare("INSERT INTO containers (container_number, iso_code, size_type, cargo_type, owner_company, status, block, bay, `row`, tier, customs_status, gate_in_time) VALUES (?, '45G1', '40HC', 'general', ?, 'in_yard', 'A', 1, 1, 1, 'PENDING', NOW())");
                        $stmtInC->execute([$ctr_num, $company]);
                    }
                    $pdo->prepare("UPDATE preadvice SET status = 'ARRIVED' WHERE container_number = ? OR truck_plate = ?")->execute([$ctr_num, $plate]);
                    accrue_billing($pdo, $ctr_num, 'Lift-Off & Gate-In Handling', 'lolo_amount', 525000);
                    accrue_billing($pdo, $ctr_num, 'Penumpukan Yard (Storage)', 'storage_amount', 275000);
                }

                // 3. Catat ke yard_events dengan kode lajur spesifik (IN-01..IN-06)
                $stmtLog = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, operator_name, notes, created_at) VALUES ('GATE_IN_ANPR', ?, ?, 'HW-01 ANPR & OCR', ?, NOW())");
                $stmtLog->execute([$ctr_num ?: '-', $lane, "[{$lane}] Deteksi loop induksi & ANPR {$lane}. Plat: $plate, Box: $ctr_num. Barrier gate terbuka otomatis."]);

                get_live_twin_state($pdo, true);

                $gate_alert = [
                    'type' => 'success',
                    'title' => "⚡ Sensor ANPR & Loop Detector {$lane} Terpicu",
                    'msg' => "Sensor pada <strong>{$lane}</strong> mendeteksi armada <strong>$plate</strong> melintasi induction loop. Kamera ANPR & OCR membaca nomor peti kemas <strong>$ctr_num</strong>. Palang gerbang <strong>{$lane}</strong> otomatis terangkat dan data armada tercatat <strong>in_yard</strong>!"
                ];
            } catch (Exception $e) {
                $gate_alert = ['type' => 'error', 'title' => 'Gagal Proses ANPR', 'msg' => $e->getMessage()];
            }
        }
    } elseif ($_POST['gate_action'] === 'trigger_weighbridge') {
        $plate = strtoupper(trim($_POST['license_plate'] ?? 'B 9812 UIK'));
        $ctr_num = strtoupper(trim($_POST['container_number'] ?? 'MSKU9821450'));
        $wb_lane = cidp_pick_gate_lane($pdo, 'IN', ['lane' => trim($_POST['lane_id'] ?? 'IN-01')]);
        if (!in_array($wb_lane, ['IN-01', 'IN-02', 'IN-03', 'IN-06'], true)) {
            $wb_lane = 'IN-01';
        }
        $gross = floatval($_POST['gross_weight_kg'] ?? 32450.0);
        $tare = floatval($_POST['tare_weight_kg'] ?? 4200.0);
        $net = max(0, $gross - $tare);

        if (isset($pdo)) {
            try {
                if (!empty($ctr_num)) {
                    $pdo->prepare("UPDATE containers SET gross_weight_kg = ? WHERE container_number = ?")->execute([$net > 0 ? $net : $gross, $ctr_num]);
                    accrue_billing($pdo, $ctr_num, 'Penimbangan Jembatan Timbang 80T SOLAS VGM', 'lolo_amount', 75000);
                }
                if (!empty($plate)) {
                    $pdo->prepare("UPDATE trucks SET weight_gross = ?, weight_tare = ?, gate_lane = COALESCE(gate_lane, ?) WHERE license_plate = ?")->execute([$gross, $tare, $wb_lane, $plate]);
                }

                $stmtLog = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, operator_name, billable_amount, notes, created_at) VALUES ('VGM_WEIGHED', ?, ?, 'HW-04 Weighbridge 80T', 75000, ?, NOW())");
                $stmtLog->execute([$ctr_num ?: '-', $wb_lane, "[{$wb_lane}] Penimbangan jembatan timbang Fangda 80T untuk $plate: Bruto {$gross} kg, Tara {$tare} kg, Netto {$net} kg. Status: SOLAS VGM Compliant."]);

                get_live_twin_state($pdo, true);

                $gate_alert = [
                    'type' => 'success',
                    'title' => "⚡ Sensor Jembatan Timbang 80T {$wb_lane} Terpicu",
                    'msg' => "Sensor loadcell Fangda 80 Ton pada <strong>{$wb_lane}</strong> membaca bobot bruto: <strong>" . number_format($gross, 0) . " kg</strong> (Netto: <strong>" . number_format($net, 0) . " kg</strong>). Sertifikat digital <strong>SOLAS VGM Chapter VI</strong> berhasil diterbitkan!"
                ];
            } catch (Exception $e) {
                $gate_alert = ['type' => 'error', 'title' => 'Gagal Penimbangan', 'msg' => $e->getMessage()];
            }
        }
    } elseif ($_POST['gate_action'] === 'quick_gate_in') {
        $truck_id = intval($_POST['truck_id'] ?? 0);
        $plate = strtoupper(trim($_POST['license_plate'] ?? ''));
        $ctr_num = strtoupper(trim($_POST['container_number'] ?? ''));
        $mission = trim($_POST['mission_type'] ?? 'drop_export');
        $target_block = strtoupper(trim($_POST['target_block'] ?? 'B'));
        $lane = cidp_pick_gate_lane($pdo, 'IN', [
            'lane'     => trim($_POST['lane_id'] ?? ''),
            'block'    => $target_block,
            'job_type' => (strpos($mission, 'pick') !== false || strpos($mission, 'release') !== false) ? 'pick_up' : 'drop_off',
        ]);
        $gross = floatval($_POST['weight_gross'] ?? 32000.0);
        $tare = floatval($_POST['weight_tare'] ?? 14000.0);
        $net = max(0, $gross - $tare);

        if (isset($pdo)) {
            try {
                // 1. Update status armada menjadi in_yard & simpan gate_lane
                $dest = "Yard Blok " . $target_block;
                $stmtUpT = $pdo->prepare("UPDATE trucks SET status = 'in_yard', gate_lane = ?, gate_in_time = NOW(), destination = ? WHERE id = ? OR license_plate = ?");
                $stmtUpT->execute([$lane, $dest, $truck_id, $plate]);

                // 2. Jika membawa kontainer (Laden In / Empty Return / Dual Cycle), perbarui atau masukkan kontainer
                if (!empty($ctr_num)) {
                    $stmtCheckC = $pdo->prepare("SELECT id FROM containers WHERE container_number = ? LIMIT 1");
                    $stmtCheckC->execute([$ctr_num]);
                    $exC = $stmtCheckC->fetch(PDO::FETCH_ASSOC);

                    if ($exC) {
                        $stmtUpC = $pdo->prepare("UPDATE containers SET status = 'in_yard', block = ?, bay = COALESCE(bay, 1), `row` = COALESCE(`row`, 1), tier = COALESCE(tier, 1), gross_weight_kg = ?, gate_in_time = COALESCE(gate_in_time, NOW()) WHERE container_number = ?");
                        $stmtUpC->execute([$target_block, $net > 0 ? $net : $gross, $ctr_num]);
                    } else {
                        $type = ($gross > 20000) ? '40HC' : '20GP';
                        $customs = ($mission === 'drop_export') ? 'EXPORT_READY' : 'SPPB_CLEARED';
                        $stmtInsC = $pdo->prepare("INSERT INTO containers (container_number, size_type, status, block, bay, `row`, tier, iso_code, gross_weight_kg, customs_status, gate_in_time) VALUES (?, ?, 'in_yard', ?, 4, 2, 1, '45G1', ?, ?, NOW())");
                        $stmtInsC->execute([$ctr_num, $type, $target_block, $net > 0 ? $net : $gross, $customs]);
                    }
                    $pdo->prepare("UPDATE preadvice SET status = 'ARRIVED' WHERE container_number = ? OR truck_plate = ?")->execute([$ctr_num, $plate]);
                    accrue_billing($pdo, $ctr_num, 'Weighbridge VGM & Lift-Off Handling', 'lolo_amount', 525000);
                    accrue_billing($pdo, $ctr_num, 'Penumpukan Yard (Storage Blok ' . $target_block . ')', 'storage_amount', 275000);
                }

                // 3. Catat audit event ke yard_events (ANPR Gate-In & SOLAS VGM) dengan equipment_id = $lane
                $stmtLog1 = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, operator_name, notes, created_at) VALUES ('GATE_IN_ANPR', ?, ?, 'HW-01 ANPR & OCR', ?, NOW())");
                $stmtLog1->execute([$ctr_num ?: '-', $lane, "[{$lane}] Deteksi ANPR {$lane} untuk $plate (Misi: $mission). Palang masuk terbuka otomatis."]);

                if ($gross > 0 && !empty($ctr_num)) {
                    $stmtLog2 = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, operator_name, billable_amount, notes, created_at) VALUES ('VGM_WEIGHED', ?, ?, 'HW-04 Weighbridge 80T', 75000, ?, NOW())");
                    $stmtLog2->execute([$ctr_num, $lane, "[{$lane}] SOLAS VGM Verified: Bruto " . number_format($gross, 0, ',', '.') . " kg, Tara " . number_format($tare, 0, ',', '.') . " kg, Netto " . number_format($net, 0, ',', '.') . " kg untuk $plate ke Blok $target_block."]);
                }

                get_live_twin_state($pdo, true);

                $mission_labels = [
                    'drop_export' => 'Skenario 1: Drop-Off Ekspor (Laden In)',
                    'pick_import' => 'Skenario 2: Pick-Up Impor (Chassis In)',
                    'empty_return' => 'Skenario 3: Drop Kosong (Empty Return)',
                    'empty_release' => 'Skenario 4: Pick-Up Kosong (Empty Release)',
                    'dual_cycle' => 'Skenario 5: Dual Cycle (Drop Ekspor & Ambil Impor)'
                ];
                $mission_label = $mission_labels[$mission] ?? $mission;

                $gate_alert = [
                    'type' => 'success',
                    'title' => "⚡ Sukses Gate-In 1-Click: $plate ($mission_label)",
                    'msg' => "Armada <strong>$plate</strong> berhasil diproses masuk gerbang melalui <strong>$lane</strong>.<br>Kamera ANPR membaca plat nomor, jembatan timbang merekam bobot <strong>" . number_format($gross, 0, ',', '.') . " kg</strong> (SOLAS VGM Sah), dan tiket yard diterbitkan menuju <strong>Blok $target_block</strong>. Palang {$lane} otomatis terangkat!"
                ];
            } catch (Exception $e) {
                $gate_alert = ['type' => 'error', 'title' => 'Gagal Gate-In', 'msg' => $e->getMessage()];
            }
        }
    } elseif ($_POST['gate_action'] === 'batch_gate_in') {
        if (isset($pdo)) {
            try {
                $stmtGetQ = $pdo->query("SELECT * FROM trucks WHERE status IN ('scheduled', 'queuing')");
                $batch_list = $stmtGetQ->fetchAll(PDO::FETCH_ASSOC);
                $inPool = ['IN-01', 'IN-02', 'IN-03', 'IN-04', 'IN-05', 'IN-06'];
                $count = 0;
                foreach ($batch_list as $bIdx => $bt) {
                    $b_dest = "Yard Blok " . ($bt['target_block'] ?: 'B');
                    $bLane = !empty($bt['gate_lane'])
                        ? cidp_normalize_gate_lane($bt['gate_lane'], 'IN')
                        : $inPool[$bIdx % count($inPool)];
                    $pdo->prepare("UPDATE trucks SET status = 'in_yard', gate_lane = ?, gate_in_time = NOW(), destination = ? WHERE id = ?")->execute([$bLane, $b_dest, $bt['id']]);
                    if (!empty($bt['container_number'])) {
                        $t_blk = $bt['target_block'] ?: 'B';
                        $stmtExC = $pdo->prepare("SELECT id FROM containers WHERE container_number = ? LIMIT 1");
                        $stmtExC->execute([$bt['container_number']]);
                        if ($stmtExC->fetchColumn()) {
                            $pdo->prepare("UPDATE containers SET status = 'in_yard', block = ?, bay = COALESCE(bay, 1), `row` = COALESCE(`row`, 1), tier = COALESCE(tier, 1), gate_in_time = COALESCE(gate_in_time, NOW()) WHERE container_number = ?")->execute([$t_blk, $bt['container_number']]);
                        } else {
                            $pdo->prepare("INSERT INTO containers (container_number, size_type, status, block, bay, `row`, tier, iso_code, gross_weight_kg, customs_status, gate_in_time) VALUES (?, '40HC', 'in_yard', ?, 1, 1, 1, '45G1', 26000, 'SPPB_CLEARED', NOW())")->execute([$bt['container_number'], $t_blk]);
                        }
                        $pdo->prepare("UPDATE preadvice SET status = 'ARRIVED' WHERE container_number = ? OR truck_plate = ?")->execute([$bt['container_number'], $bt['license_plate']]);
                        accrue_billing($pdo, $bt['container_number'], 'Batch Gate-In & Lift-Off Handling', 'lolo_amount', 525000);
                        accrue_billing($pdo, $bt['container_number'], 'Penumpukan Yard (Storage)', 'storage_amount', 275000);
                    }
                    $count++;
                }
                get_live_twin_state($pdo, true);
                $gate_alert = [
                    'type' => 'success',
                    'title' => "⚡ Batch Gate-In Selesai ($count Armada)",
                    'msg' => "Seluruh $count armada antrean terjadwal (Pre-Advice Queue) telah didistribusikan ke 6 lajur Gate-In (`IN-01..IN-06`) dan diproses masuk terminal yard!"
                ];
            } catch (Exception $e) {
                $gate_alert = ['type' => 'error', 'title' => 'Gagal Batch Gate-In', 'msg' => $e->getMessage()];
            }
        }
    } elseif ($_POST['gate_action'] === 'reset_demo_queue') {
        if (isset($pdo)) {
            try {
                $pdo->exec("DELETE FROM trucks WHERE license_plate IN ('B 9481 UIX', 'B 9120 KLP', 'B 9833 TRK', 'B 9502 PQR', 'B 9044 XZW')");
                $demo_queuing = [
                    ['B 9481 UIX', 'PT Samudera Pratama Logistik', 'Budi Santoso', 'drop_export', 'drop_off', 'MSKU9821450', 'B', 'Pabrik Cikarang Jababeka', 32450.00, 14200.00, 'IN-01'],
                    ['B 9120 KLP', 'PT Dunex Express Indonesia', 'Bambang Supriyadi', 'pick_import', 'pick_up', NULL, 'A', 'Pabrik MM2100 Cibitung', 14200.00, 14200.00, 'IN-04'],
                    ['B 9833 TRK', 'PT Cipta Krida Logistik', 'Dedi Suryanto', 'empty_return', 'drop_off', 'TEMU6543210', 'C', 'Pabrik KIIC Karawang', 16500.00, 14100.00, 'IN-05'],
                    ['B 9502 PQR', 'PT Puninar Jaya', 'Hendra Setiawan', 'empty_release', 'pick_up', NULL, 'C', 'Pabrik EJIP Cikarang', 14100.00, 14100.00, 'IN-04'],
                    ['B 9044 XZW', 'PT Kamadjaja Logistics', 'Wahyu Pratama', 'dual_cycle', 'drop_off', 'SUDU5544332', 'B', 'Pabrik GIIC Deltamas', 33100.00, 14300.00, 'IN-02']
                ];
                $stmtIns = $pdo->prepare("INSERT INTO trucks (license_plate, company, driver_name, mission_type, job_type, container_number, target_block, destination, weight_gross, weight_tare, gate_lane, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'queuing', NOW())");
                foreach ($demo_queuing as $row) {
                    $stmtIns->execute($row);
                }
                get_live_twin_state($pdo, true);
                $gate_alert = [
                    'type' => 'success',
                    'title' => 'Antrean Pra-Gate Berhasil Direset',
                    'msg' => '5 Skenario Truk Terjadwal (Pre-Advice Queue) siap diuji kembali!'
                ];
            } catch (Exception $e) {
                $gate_alert = ['type' => 'error', 'title' => 'Gagal Reset Antrean', 'msg' => $e->getMessage()];
            }
        }
    }
}

// Ambil antrian truk terjadwal (Pre-Advised / Queuing) untuk Gate In
$queuing_trucks = [];
try {
    $stmtQ = $pdo->query("SELECT * FROM trucks WHERE status IN ('scheduled', 'queuing') ORDER BY id ASC");
    $queuing_trucks = $stmtQ->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $queuing_trucks = [];
}

// Ambil data truk dari database untuk telemetri gerbang
$gate_trucks = [];
$total_inbound_today = 0;
$total_outbound_today = 0;
$total_at_gate = 0;
$latest_truck = null;

// Map status kepabeanan & billing per kontainer
$ctr_customs_map = [];
$ctr_billing_map = [];

try {
    $stmt = $pdo->query("SELECT * FROM trucks WHERE status != 'scheduled' ORDER BY COALESCE(gate_out_time, gate_in_time, created_at) DESC LIMIT 20");
    $gate_trucks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($gate_trucks as $gt) {
        if ($gt['status'] === 'at_gate' || $gt['status'] === 'queuing') $total_at_gate++;
        if (!empty($gt['gate_in_time'])) $total_inbound_today++;
        if (!empty($gt['gate_out_time'])) $total_outbound_today++;
    }
    if (!empty($gate_trucks)) {
        $latest_truck = $gate_trucks[0];
    }

    // Ambil data status pabean
    $stmtC = $pdo->query("SELECT container_number, customs_status FROM containers");
    while ($rc = $stmtC->fetch(PDO::FETCH_ASSOC)) {
        $ctr_customs_map[$rc['container_number']] = $rc['customs_status'];
    }

    // Ambil data status billing (PENDING jika ada minimal 1 faktur belum PAID)
    $stmtB = $pdo->query("SELECT container_number, CASE WHEN SUM(CASE WHEN payment_status <> 'PAID' THEN 1 ELSE 0 END) > 0 THEN 'PENDING' ELSE 'PAID' END AS payment_status FROM billing_invoices GROUP BY container_number");
    while ($rb = $stmtB->fetch(PDO::FETCH_ASSOC)) {
        $ctr_billing_map[$rb['container_number']] = $rb['payment_status'];
    }
} catch (Exception $e) {
    $gate_trucks = [];
}

$gate_info = [
    'pic'    => 'Armansyah Muchtarrom',
    'role'   => 'Hardware & Infrastructure Specialist',
    'desc'   => 'Otomasi gerbang masuk/keluar, verifikasi kamera OCR ISO 6346 & ANPR, jembatan timbang 80T SOLAS VGM, dan kontrol palang otomatis terintegrasi.',
    'icon'   => 'fa-door-open',
    'status' => 'Modul Aktif & Terintegrasi Sensor'
];

// Data Hardware 26 Item
$hardware_list = [
    [
        'no' => 1,
        'kategori' => 'Otomasi Gate',
        'tipe' => 'live',
        'nama' => 'Kamera ANPR Gate',
        'model' => 'Hikvision DS-TCG406-E / Seri ANPR',
        'spesifikasi' => 'Pembacaan plat nomor otomatis, capture kendaraan, outdoor IP67, integrasi barrier gate & YMS',
        'harga' => 16000000,
        'harga_label' => 'Rp 16.000.000',
        'unit' => 10,
        'unit_label' => '10 unit (6 In / 4 Out)',
        'subtotal' => 160000000,
        'subtotal_label' => 'Rp 160.000.000',
        'link' => 'https://www.hikvision.com/id/products/parking-management/vehicle-access-control-management/anpr-cameras/ds-tcg406-e/'
    ],
    [
        'no' => 2,
        'kategori' => 'Otomasi Gate',
        'tipe' => 'live',
        'nama' => 'Kamera OCR Kontainer',
        'model' => 'Hikvision iDS-TCV300-A6I / OCR Portal',
        'spesifikasi' => 'Membaca nomor kontainer ISO 6346 (4 huruf + 7 angka), multi-angle recognition, integrasi gate system',
        'harga' => 55000000,
        'harga_label' => 'Rp 55.000.000',
        'unit' => 6,
        'unit_label' => '6 unit (6 Portal In)',
        'subtotal' => 330000000,
        'subtotal_label' => 'Rp 330.000.000',
        'link' => 'https://www.hikvision.com/id/products/ITS-Products/Checkpoint-Systems/Checkpoint-Capture-Units/ids-tcv300-a6i/'
    ],
    [
        'no' => 3,
        'kategori' => 'Keamanan & Bea Cukai',
        'tipe' => 'blueprint',
        'nama' => 'CCTV Yard 4 MP',
        'model' => 'Hikvision DS-2CD2046G2H-IU',
        'spesifikasi' => '4 MP, IP67 weatherproof, IR night vision, PoE, monitoring area penumpukan umum',
        'harga' => 3500000,
        'harga_label' => 'Rp 3.500.000',
        'unit' => 32,
        'unit_label' => '32 unit',
        'subtotal' => 112000000,
        'subtotal_label' => 'Rp 112.000.000',
        'link' => 'https://www.hikvision.com/en/products/IP-Products/Network-Cameras/Pro-Series-EasyIP-/ds-2cd2046g2h-i-u-/'
    ],
    [
        'no' => 4,
        'kategori' => 'Keamanan & Bea Cukai',
        'tipe' => 'blueprint',
        'nama' => 'CCTV Yard 8 MP',
        'model' => 'Hikvision DS-2CD2T87G2H-LI',
        'spesifikasi' => '8 MP / 4K UHD, ColorVu night vision, outdoor IP67, perimeter boundary monitoring',
        'harga' => 6500000,
        'harga_label' => 'Rp 6.500.000',
        'unit' => 16,
        'unit_label' => '16 unit',
        'subtotal' => 104000000,
        'subtotal_label' => 'Rp 104.000.000',
        'link' => 'https://www.hikvision.com/id/products/IP-Products/Network-Cameras/Pro-Series-EasyIP-/ds-2cd2t87g2h-li/?subName=DS-2CD2T87G2H-LI'
    ],
    [
        'no' => 5,
        'kategori' => 'Keamanan & Bea Cukai',
        'tipe' => 'blueprint',
        'nama' => 'CCTV PTZ 360°',
        'model' => 'Hikvision DS-2SE4C425MWG-E/14 TandemVu',
        'spesifikasi' => 'Pan-tilt-zoom 25x optical zoom, dual-channel panoramic + PTZ, pemantauan area luas 360°',
        'harga' => 10300000,
        'harga_label' => 'Rp 10.300.000',
        'unit' => 8,
        'unit_label' => '8 unit',
        'subtotal' => 82400000,
        'subtotal_label' => 'Rp 82.400.000',
        'link' => 'https://www.hikvision.com/id/products/IP-Products/PTZ-Cameras/Pro-Series/ds-2se4c425mwg-e-14-f0-/'
    ],
    [
        'no' => 6,
        'kategori' => 'Otomasi Gate',
        'tipe' => 'live',
        'nama' => 'Weighbridge / VGM Scale',
        'model' => 'Fangda Electronic Truck Scale 80 Ton',
        'spesifikasi' => 'Jembatan timbang kendaraan kapasitas 80 ton, digital load cell indicator, sertifikasi SOLAS VGM, integrasi YMS',
        'harga' => 267000000,
        'harga_label' => 'Rp 267.000.000',
        'unit' => 4,
        'unit_label' => '4 unit (IN-01..03 & IN-06)',
        'subtotal' => 1068000000,
        'subtotal_label' => 'Rp 1.068.000.000',
        'link' => 'https://cnfjkeda.en.made-in-china.com/product/aQRUXTBbvLYe/China-60-Ton-80-Ton-Weighbridge-Price-Electronic-Truck-Scale.html'
    ],
    [
        'no' => 7,
        'kategori' => 'Otomasi Gate',
        'tipe' => 'live',
        'nama' => 'RFID Reader Long-Range',
        'model' => 'Long-Range UHF RFID Reader (20m)',
        'spesifikasi' => 'Frekuensi 860-960 MHz, jarak baca hingga 20 meter, identifikasi e-Pass driver & armada truk otomatis',
        'harga' => 3000000,
        'harga_label' => 'Rp 3.000.000',
        'unit' => 10,
        'unit_label' => '10 unit (6 In / 4 Out)',
        'subtotal' => 30000000,
        'subtotal_label' => 'Rp 30.000.000',
        'link' => 'https://www.orbitadigital.com/en/access-controls/accessories/19689-uhf-reader-20m-access-reader-uhf-tag-access-range-up-to-20-m.html'
    ],
    [
        'no' => 8,
        'kategori' => 'Otomasi Gate',
        'tipe' => 'blueprint',
        'nama' => 'RFID Tag UHF ISO',
        'model' => 'RFID UHF ID Tag ISO (TRANSIT & ATEX)',
        'spesifikasi' => 'Tag pasif UHF anti-metal standar ISO 18000-6C, identitas truk mitra / kartu akses pengemudi',
        'harga' => 690000,
        'harga_label' => 'Rp 690.000 / pack',
        'unit' => 50,
        'unit_label' => '50 pack (500 tags)',
        'subtotal' => 34500000,
        'subtotal_label' => 'Rp 34.500.000',
        'link' => 'https://www.ebay.com/itm/316860017458'
    ],
    [
        'no' => 9,
        'kategori' => 'Otomasi Gate',
        'tipe' => 'blueprint',
        'nama' => 'Automatic Barrier Gate',
        'model' => 'High-Speed Automatic Barrier Gate',
        'spesifikasi' => 'Palang otomatis 3 meter, motor DC brushless (kecepatan buka 1-1.5 detik), sensor loop detector, integrasi ANPR/RFID',
        'harga' => 60000000,
        'harga_label' => 'Rp 60.000.000',
        'unit' => 10,
        'unit_label' => '10 unit (6 In / 4 Out)',
        'subtotal' => 600000000,
        'subtotal_label' => 'Rp 600.000.000',
        'link' => 'https://jutaigateopener.en.made-in-china.com/product/MObfpFLKLorl/China-Automatic-Barrier-Gate-with-Single-Bar-Gate-Arm-Barrier-Automatic-Parking-Gate-Barrier.html'
    ],
    [
        'no' => 10,
        'kategori' => 'Otomasi Gate',
        'tipe' => 'live',
        'nama' => 'LED Information Display',
        'model' => 'Outdoor P6/P8 Industrial LED Display',
        'spesifikasi' => 'Menampilkan nomor gate, instruksi sopir ("SILAHKAN MASUK"), nomor kontainer, dan blok tujuan yard',
        'harga' => 47000000,
        'harga_label' => 'Rp 47.000.000',
        'unit' => 10,
        'unit_label' => '10 unit (6 In / 4 Out)',
        'subtotal' => 470000000,
        'subtotal_label' => 'Rp 470.000.000',
        'link' => 'https://chipshow.en.made-in-china.com/product/KYarZvkHyFhd/China-Sc-Smart-P6-67-Outdoor-LED-Display-for-Wall-Mounted-Advertising.html'
    ],
    [
        'no' => 11,
        'kategori' => 'Core Datacenter & Jaringan',
        'tipe' => 'blueprint',
        'nama' => 'Industrial Network Switch',
        'model' => 'Managed Industrial PoE+ Gigabit Switch',
        'spesifikasi' => 'Switch industri 16/24-Port PoE+ Gigabit, casing logam IP40, tahan suhu ekstrem lapangan, redundant power input',
        'harga' => 10000000,
        'harga_label' => 'Rp 10.000.000',
        'unit' => 12,
        'unit_label' => '12 unit',
        'subtotal' => 120000000,
        'subtotal_label' => 'Rp 120.000.000',
        'link' => 'https://global.microless.com/product/tp-link-jetstream-48-port-gigabit-l2-managed-switch-with-4-gigabit-sfp-slots-t2600g-52ts-tl-sg3452/'
    ],
    [
        'no' => 12,
        'kategori' => 'Intermodal Rail Siding',
        'tipe' => 'blueprint',
        'nama' => 'Access Point Outdoor',
        'model' => 'Ubiquiti U7 Pro Outdoor / UniFi WiFi 7 AP',
        'spesifikasi' => 'Tri-Band WiFi 7 outdoor, casing IP67 weatherproof, jangkauan luas untuk konektivitas VMT & PDA di yard',
        'harga' => 7200000,
        'harga_label' => 'Rp 7.200.000',
        'unit' => 18,
        'unit_label' => '18 unit (Yard & Rail)',
        'subtotal' => 129600000,
        'subtotal_label' => 'Rp 129.600.000',
        'link' => 'https://www.aloinfousa.com/products/ubiquiti-networks-unifi-u7-pro-outdoor-tri-band-wi-fi-7-access-point'
    ],
    [
        'no' => 13,
        'kategori' => 'Core Datacenter & Jaringan',
        'tipe' => 'blueprint',
        'nama' => 'Server NVR & Database',
        'model' => 'Hikvision DS-9664NI-M16/R 64-Ch NVR & Server',
        'spesifikasi' => 'NVR 64-Channel 4K, 16 SATA bay, redundant PSU, rekaman CCTV yard & gate, integrasi database YMS',
        'harga' => 102000000,
        'harga_label' => 'Rp 102.000.000',
        'unit' => 2,
        'unit_label' => '2 unit (Primary + Backup)',
        'subtotal' => 204000000,
        'subtotal_label' => 'Rp 204.000.000',
        'link' => 'https://www.orbitadigital.com/en/cctv-ip/ip-nvr-recorders/56602-hikvision-solutions-ds-9664ni-m16-r-hikvision-nvr-recorder-64-ch-ip-solutions-maximum.html'
    ],
    [
        'no' => 14,
        'kategori' => 'Core Datacenter & Jaringan',
        'tipe' => 'blueprint',
        'nama' => 'UPS Online Rackmount',
        'model' => 'APC Smart-UPS SRT 5000VA RM 230V (SRT5KRMXLI)',
        'spesifikasi' => 'UPS On-Line double conversion 5kVA/4.5kW, rackmount 3U, backup daya darurat server & gate automation',
        'harga' => 81000000,
        'harga_label' => 'Rp 81.000.000',
        'unit' => 4,
        'unit_label' => '4 unit (Server & Gate)',
        'subtotal' => 324000000,
        'subtotal_label' => 'Rp 324.000.000',
        'link' => 'https://www.monotaro.id/items/s013485894.html'
    ],
    [
        'no' => 15,
        'kategori' => 'Core Datacenter & Jaringan',
        'tipe' => 'blueprint',
        'nama' => 'Barcode / QR Scanner',
        'model' => 'Zebra Symbol LS2208 Handheld Scanner',
        'spesifikasi' => 'Laser barcode/QR scanner 1D/2D, USB, scan dokumen surat jalan, gate pass, e-Ticket booking pos sekuriti',
        'harga' => 1600000,
        'harga_label' => 'Rp 1.600.000',
        'unit' => 8,
        'unit_label' => '8 unit (Loket & Pos)',
        'subtotal' => 12800000,
        'subtotal_label' => 'Rp 12.800.000',
        'link' => 'https://www.monotaro.id/items/s003425886.html'
    ],
    [
        'no' => 16,
        'kategori' => 'Cold Chain Reefer',
        'tipe' => 'blueprint',
        'nama' => 'Thermal Camera Reefer',
        'model' => 'Hikvision DS-2TD2636B-13/P Thermal & Optical',
        'spesifikasi' => 'Pemantauan suhu permukaan reefer container, deteksi dini overheat kompresor & alarm kebakaran cold chain',
        'harga' => 30000000,
        'harga_label' => 'Rp 30.000.000',
        'unit' => 4,
        'unit_label' => '4 unit (Reefer Yard)',
        'subtotal' => 120000000,
        'subtotal_label' => 'Rp 120.000.000',
        'link' => 'https://www.hikvision.com/en/products/Thermal-Products/Thermography-thermal-cameras/temperature-screening-series/ds-2td2636b-13-p/'
    ],
    [
        'no' => 17,
        'kategori' => 'Intermodal Rail Siding',
        'tipe' => 'blueprint',
        'nama' => 'GPS Tracker Truck & Asset',
        'model' => 'Teltonika TAT240 4G LTE Asset Tracker',
        'spesifikasi' => 'Pelacak posisi armada truk real-time, 4G Cat M1/NB-IoT, baterai internal otonom, tracking perjalanan Priok - Cikarang',
        'harga' => 2500000,
        'harga_label' => 'Rp 2.500.000 / unit',
        'unit' => 40,
        'unit_label' => '40 unit',
        'subtotal' => 100000000,
        'subtotal_label' => 'Rp 100.000.000',
        'link' => 'https://www.slapukas.lt/ee/teltonika-tat240'
    ],
    [
        'no' => 18,
        'kategori' => 'Otomasi Gate',
        'tipe' => 'live',
        'nama' => 'Industrial Edge AI PC Gate',
        'model' => 'Advantech ARK-3532 / Neousys Nuvo-9000 Fanless',
        'spesifikasi' => 'Intel Core i7, Fanless Rugged, suhu -20°C s/d 60°C, catu daya 9-48V DC, IP40, Dual GbE PoE, 4x COM RS-232/422/485 untuk Timbangan & PLC Gate',
        'harga' => 42000000,
        'harga_label' => 'Rp 42.000.000',
        'unit' => 4,
        'unit_label' => '4 unit (Gate Lanes)',
        'subtotal' => 168000000,
        'subtotal_label' => 'Rp 168.000.000',
        'link' => 'https://www.advantech.com/en/products/fanless-embedded-box-pcs/ark-3532/'
    ],
    [
        'no' => 19,
        'kategori' => 'Keamanan & Bea Cukai',
        'tipe' => 'blueprint',
        'nama' => 'Gantry Container X-Ray',
        'model' => 'Nuctech MB1215DE / Smiths Detection HCVG',
        'spesifikasi' => 'High-Energy X-Ray (6 MeV), penetrasi baja >300 mm, throughput 20-25 truk/jam, pemindaian peti kemas jalur merah & integrasi CEISA 4.0 Bea Cukai',
        'harga' => 12500000000,
        'harga_label' => 'Rp 12.500.000.000',
        'unit' => 1,
        'unit_label' => '1 sistem gantry',
        'subtotal' => 12500000000,
        'subtotal_label' => 'Rp 12.500.000.000',
        'link' => 'https://www.nuctech.com/en/product/high-energy-container-and-vehicle-inspection-system/'
    ],
    [
        'no' => 20,
        'kategori' => 'Yard & Alat Angkat',
        'tipe' => 'blueprint',
        'nama' => 'Vehicle Mounted Terminal (VMT)',
        'model' => 'Zebra VC8300 Rugged Vehicle Mounted Terminal',
        'spesifikasi' => 'Android Enterprise, layar sentuh 8 inci ultra-bright, keyboard fisik, tahan getaran MIL-STD-810G & IP66 untuk kabin Reach Stacker / RTG',
        'harga' => 74000000,
        'harga_label' => 'Rp 74.000.000',
        'unit' => 6,
        'unit_label' => '6 unit (Alat Berat)',
        'subtotal' => 444000000,
        'subtotal_label' => 'Rp 444.000.000',
        'link' => 'https://www.zebra.com/ap/en/products/mobile-computers/vehicle-mounted/vc8300/vc8300.html'
    ],
    [
        'no' => 21,
        'kategori' => 'Yard & Alat Angkat',
        'tipe' => 'live',
        'nama' => 'DGPS / RTK GNSS Receiver',
        'model' => 'CHCNAV CGI-610 / Hemisphere GNSS',
        'spesifikasi' => 'Dual-antenna RTK GNSS + IMU sensor, akurasi centimeter (<2 cm), update rate 50Hz, RS-232/CAN Bus, IP67 rugged untuk Reach Stacker & RTG auto Bay-Row-Tier',
        'harga' => 45000000,
        'harga_label' => 'Rp 45.000.000',
        'unit' => 6,
        'unit_label' => '6 unit (Alat Berat)',
        'subtotal' => 270000000,
        'subtotal_label' => 'Rp 270.000.000',
        'link' => 'https://chcnav.com/product-detail/cgi-610-gnss-ins-sensor'
    ],
    [
        'no' => 22,
        'kategori' => 'Yard & Alat Angkat',
        'tipe' => 'live',
        'nama' => 'Spreader Twistlock & Load Cell',
        'model' => 'Bromma / Kalmar SmartSpreader Sensor Kit',
        'spesifikasi' => 'Inductive proximity sensors (twistlock engaged/unlocked), 4x load cell telemetry (gross weight), RS-485/CANopen, tahan getaran maritim untuk Reach Stacker',
        'harga' => 35000000,
        'harga_label' => 'Rp 35.000.000',
        'unit' => 6,
        'unit_label' => '6 unit (Spreader)',
        'subtotal' => 210000000,
        'subtotal_label' => 'Rp 210.000.000',
        'link' => 'https://www.bromma.com/products/sensors-telematics/'
    ],
    [
        'no' => 23,
        'kategori' => 'Cold Chain Reefer',
        'tipe' => 'live',
        'nama' => 'Smart Reefer Power Socket',
        'model' => 'Marechal / Cavotec 380V/32A Smart Receptacle',
        'spesifikasi' => '3P+N+E 32A 380V IP67, built-in Modbus RTU / LoRaWAN energy meter & continuous temperature logging, trip detection untuk 300 titik reefer yard',
        'harga' => 8500000,
        'harga_label' => 'Rp 8.500.000 / titik',
        'unit' => 300,
        'unit_label' => '300 titik colokan',
        'subtotal' => 2550000000,
        'subtotal_label' => 'Rp 2.550.000.000',
        'link' => 'https://marechal.com/en/products/decontractor/smart-reefer-sockets'
    ],
    [
        'no' => 24,
        'kategori' => 'Yard & Alat Angkat',
        'tipe' => 'blueprint',
        'nama' => 'Rugged Mobile PDA',
        'model' => 'Zebra TC57x / Honeywell CT60',
        'spesifikasi' => 'Android Enterprise, 2D Long-Range Barcode Imager, IP68 tahan banting/air, 4G LTE & WiFi 6, hot-swappable battery untuk tallyman & inspeksi kontainer (EIR)',
        'harga' => 16000000,
        'harga_label' => 'Rp 16.000.000',
        'unit' => 10,
        'unit_label' => '10 unit (Tallyman)',
        'subtotal' => 160000000,
        'subtotal_label' => 'Rp 160.000.000',
        'link' => 'https://www.zebra.com/id/id/products/mobile-computers/handheld/tc57.html'
    ],
    [
        'no' => 25,
        'kategori' => 'Intermodal Rail Siding',
        'tipe' => 'live',
        'nama' => 'Rail Trackside Axle Counter',
        'model' => 'Frauscher RSR123 Wheel Sensor',
        'spesifikasi' => 'Inductive rail head sensor, deteksi arah gerak KA, hitung as roda (axle count), estimasi kecepatan, IP68, SIL 4 untuk Intermodal Rail Siding KA Logistik',
        'harga' => 85000000,
        'harga_label' => 'Rp 85.000.000',
        'unit' => 4,
        'unit_label' => '4 sensor (2 Jalur KA)',
        'subtotal' => 340000000,
        'subtotal_label' => 'Rp 340.000.000',
        'link' => 'https://www.frauscher.com/en/products/wheel-sensors/frauscher-wheel-sensor-rsr123'
    ],
    [
        'no' => 26,
        'kategori' => 'Keamanan & Bea Cukai',
        'tipe' => 'live',
        'nama' => 'Electronic Cargo Smart Seal',
        'model' => 'Jointech JT701 GPS Smart E-Seal',
        'spesifikasi' => 'GPS/GSM/RFID Smart Electronic Lock, sensor kawat anti-tamper, masa pakai baterai 30 hari, IP67 untuk kontainer transit Bea Cukai Priok - Cikarang (CEISA 4.0)',
        'harga' => 3200000,
        'harga_label' => 'Rp 3.200.000 / unit',
        'unit' => 100,
        'unit_label' => '100 unit e-Seal',
        'subtotal' => 320000000,
        'subtotal_label' => 'Rp 320.000.000',
        'link' => 'https://www.jointech.com/product/jt701-smart-gps-electronic-seal-tracker.html'
    ]
];

// Perhitungan Ringkasan CAPEX
$total_capex = 0;
$total_unit = 0;
$live_count = 0;
$blueprint_count = 0;

foreach ($hardware_list as $item) {
    $total_capex += $item['subtotal'];
    $total_unit += $item['unit'];
    if ($item['tipe'] === 'live') {
        $live_count++;
    } else {
        $blueprint_count++;
    }
}
?>

<!-- Alert Feedback Pasca Aksi Gate Outbound Interlock -->
<?php if ($gate_alert): ?>
<div class="mb-4 p-4 rounded-xl border flex items-start space-x-3 animate-fadeIn <?= $gate_alert['type'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-amber-50 border-amber-200 text-amber-900' ?>">
    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 <?= $gate_alert['type'] === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
        <i class="fa-solid <?= $gate_alert['type'] === 'success' ? 'fa-check' : 'fa-triangle-exclamation' ?>"></i>
    </div>
    <div class="flex-1 min-w-0">
        <h4 class="text-sm font-bold"><?= $gate_alert['title'] ?></h4>
        <p class="text-xs mt-0.5"><?= $gate_alert['msg'] ?></p>
    </div>
    <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>
<?php endif; ?>

<div class="space-y-2.5 animate-fadeIn pb-12">
    <!-- Header Modul: Compact Executive Style (Aligned with Sidebar) -->
    <div class="bg-white rounded-xl px-3 py-2 shadow-2xs border border-slate-200/80 mb-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div class="flex items-center space-x-2.5">
            <div class="w-7 h-7 rounded-lg bg-orange-50 border border-orange-200/70 text-orange-600 flex items-center justify-center text-xs shadow-2xs flex-shrink-0">
                <i class="fa-solid fa-door-open"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-1.5">
                    <h1 class="text-[13px] font-extrabold tracking-tight text-slate-900">Manajemen Gate &amp; Otomasi Infrastruktur</h1>
                    <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-[9px] font-bold flex items-center">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span>Sensor Live
                    </span>
                    <span class="px-1.5 py-0.5 bg-orange-50 text-orange-700 border border-orange-200 rounded text-[9px] font-bold">
                        OCR ISO 6346 &amp; ANPR
                    </span>
                    <span class="px-1.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[9px] font-bold">
                        80T SOLAS VGM
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Micro-Cards (High-Density Executive Grid) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 mb-2.5">
        <div class="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-2xs flex items-center justify-between transition-all hover:border-orange-200">
            <div class="min-w-0 pr-2">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider truncate mb-0.5">Plaza Gate Otomasi</p>
                <div class="flex items-baseline space-x-1.5">
                    <h3 class="text-[13.5px] font-extrabold text-slate-900 tracking-tight leading-tight">6 IN / 4 OUT</h3>
                    <span class="text-[9px] text-emerald-600 font-bold">10 Lajur</span>
                </div>
                <p class="text-[9px] text-slate-500 font-semibold mt-0.5 truncate">4 Timbang VGM 80T • 6 OCR In</p>
            </div>
            <div class="w-7 h-7 rounded-lg bg-blue-50 text-[#0170b9] flex items-center justify-center text-[11px] flex-shrink-0 shadow-2xs">
                <i class="fa-solid fa-arrow-right-to-bracket"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-2xs flex items-center justify-between transition-all hover:border-orange-200">
            <div class="min-w-0 pr-2">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider truncate mb-0.5">Avg Transaction Time</p>
                <div class="flex items-baseline space-x-1.5">
                    <h3 class="text-[13.5px] font-extrabold text-slate-900 tracking-tight leading-tight">28 Detik</h3>
                    <span class="text-[9px] text-emerald-600 font-bold">-37%</span>
                </div>
                <p class="text-[9px] text-slate-500 font-semibold mt-0.5 truncate">Target SOLAS: &lt; 45s / Truk</p>
            </div>
            <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-[11px] flex-shrink-0 shadow-2xs">
                <i class="fa-solid fa-stopwatch"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-2xs flex items-center justify-between transition-all hover:border-orange-200">
            <div class="min-w-0 pr-2">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider truncate mb-0.5">Katalog Hardware</p>
                <div class="flex items-baseline space-x-1.5">
                    <h3 class="text-[13.5px] font-extrabold text-slate-900 tracking-tight leading-tight">26 Perangkat</h3>
                    <span class="text-[9px] text-indigo-600 font-bold"><?= $live_count ?> Live</span>
                </div>
                <p class="text-[9px] text-slate-500 font-semibold mt-0.5 truncate">Total <?= number_format($total_unit, 0, ',', '.') ?> Unit di 35 Ha</p>
            </div>
            <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-[11px] flex-shrink-0 shadow-2xs">
                <i class="fa-solid fa-server"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-2xs flex items-center justify-between transition-all hover:border-orange-200">
            <div class="min-w-0 pr-2">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider truncate mb-0.5">Total CAPEX Hardware</p>
                <div class="flex items-baseline space-x-1.5">
                    <h3 class="text-[13.5px] font-extrabold text-slate-900 tracking-tight leading-tight">Rp <?= number_format($total_capex / 1000000000, 2, ',', '.') ?> M</h3>
                    <span class="text-[9px] text-slate-500 font-bold">6 Klaster</span>
                </div>
                <p class="text-[9px] text-slate-500 font-semibold mt-0.5 truncate">Investasi Fasilitas Dry Port</p>
            </div>
            <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-[11px] flex-shrink-0 shadow-2xs">
                <i class="fa-solid fa-coins"></i>
            </div>
        </div>
    </div>

    <!-- Toolbar Aksi Cepat & Registrasi Pra-Gate (Compact) -->
    <div class="bg-white rounded-xl px-3 py-2 border border-slate-200/80 shadow-2xs flex flex-wrap items-center justify-between gap-2 mb-2.5">
        <div class="flex items-center space-x-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-[11px] font-bold text-slate-700">Status Gerbang: <span class="text-emerald-600">Aktif &amp; Terhubung Telemetri</span></span>
        </div>
        <div class="flex flex-wrap items-center gap-1.5">
            <button onclick="showGatePassModal()" class="px-2.5 py-1 bg-[#0170b9] hover:bg-[#004b87] text-white rounded-lg text-[11px] font-bold transition flex items-center space-x-1.5 shadow-2xs cursor-pointer">
                <i class="fa-solid fa-plus text-[10px]"></i>
                <span>Registrasi Pra-Gate</span>
            </button>
            <a href="dashboard.php?page=twin&view=gate" class="px-2.5 py-1 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg text-[11px] font-bold transition flex items-center space-x-1.5 shadow-2xs" title="Pantau Gate Automasi di Digital Twin 3D">
                <i class="fa-solid fa-cube text-[10px]"></i>
                <span>Digital Twin 3D</span>
            </a>
            <button onclick="printGateRekap()" class="px-2.5 py-1 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-[11px] font-semibold rounded-lg shadow-2xs transition flex items-center cursor-pointer" title="Cetak Rekapitulasi Arus Gerbang Format Resmi A4">
                <i class="fa-solid fa-print mr-1 text-[#0170b9] text-[10px]"></i> Cetak Rekap Resmi
            </button>
        </div>
    </div>

    <!-- Tab Navigation (Compact Executive Pills) -->
    <div class="bg-white rounded-xl p-1 border border-slate-200/80 shadow-2xs flex flex-wrap gap-1 mb-2.5">
        <button onclick="switchGateTab('tab-simulasi')" id="btn-tab-simulasi" class="tab-btn flex-1 min-w-[120px] py-1.5 px-2.5 rounded-lg text-[11.5px] font-bold transition-all text-white bg-gradient-to-r from-orange-500 to-orange-600 shadow-xs shadow-orange-500/20 flex items-center justify-center space-x-1.5 cursor-pointer">
            <i class="fa-solid fa-tower-broadcast text-[11px]"></i>
            <span>Monitoring Arus Gerbang</span>
        </button>
        <button onclick="switchGateTab('tab-ocr-iso')" id="btn-tab-ocr-iso" class="tab-btn flex-1 min-w-[120px] py-1.5 px-2.5 rounded-lg text-[11.5px] font-medium transition-all text-slate-600 hover:text-orange-600 hover:bg-orange-50/70 flex items-center justify-center space-x-1.5 cursor-pointer">
            <i class="fa-solid fa-camera-retro text-[11px]"></i>
            <span>Interactive OCR &amp; ISO 6346</span>
        </button>
        <button onclick="switchGateTab('tab-katalog')" id="btn-tab-katalog" class="tab-btn flex-1 min-w-[120px] py-1.5 px-2.5 rounded-lg text-[11.5px] font-medium transition-all text-slate-600 hover:text-orange-600 hover:bg-orange-50/70 flex items-center justify-center space-x-1.5 cursor-pointer">
            <i class="fa-solid fa-list-check text-[11px]"></i>
            <span>Lampiran SRS: Katalog Hardware (26 Item)</span>
        </button>
        <button onclick="switchGateTab('tab-telemetri')" id="btn-tab-telemetri" class="tab-btn flex-1 min-w-[120px] py-1.5 px-2.5 rounded-lg text-[11.5px] font-medium transition-all text-slate-600 hover:text-orange-600 hover:bg-orange-50/70 flex items-center justify-center space-x-1.5 cursor-pointer">
            <i class="fa-solid fa-chart-line text-[11px]"></i>
            <span>Telemetri IoT Lapangan</span>
        </button>
        <button onclick="switchGateTab('tab-dcsa')" id="btn-tab-dcsa" class="tab-btn flex-1 min-w-[120px] py-1.5 px-2.5 rounded-lg text-[11.5px] font-medium transition-all text-slate-600 hover:text-orange-600 hover:bg-orange-50/70 flex items-center justify-center space-x-1.5 cursor-pointer">
            <i class="fa-solid fa-satellite-dish text-[11px]"></i>
            <span>DCSA Event Log</span>
        </button>
    </div>

    <!-- ======================================================================= -->
    <!-- TAB 1: SIMULASI ALUR GATE & SENSOR INTERAKTIF -->
    <!-- ======================================================================= -->
    <div id="tab-simulasi" class="tab-content space-y-6">
        <!-- ================================================================= -->
        <!-- ANTREAN TRUK TERJADWAL & PRA-GATE BOOKING (PRE-ADVICE QUEUE)     -->
        <!-- ================================================================= -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-[#0170b9] border border-blue-200">
                            <i class="fa-solid fa-list-ol mr-1"></i>Pre-Advice &amp; Booking Queue
                        </span>
                        <span class="text-xs font-bold text-gray-500 font-mono">5 Skenario Riil Dry Port</span>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mt-1 flex items-center">
                        <i class="fa-solid fa-truck-ramp-box text-[#0170b9] mr-2"></i>
                        Antrean Truk Terjadwal (Pra-Gate)
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">Booking terdaftar &bull; Eksekusi <strong>1-Click Gate-In</strong> langsung memicu ANPR, VGM &amp; alokasi yard.</p>
                </div>
                <div class="flex items-center space-x-2 shrink-0">
                    <?php if (!empty($queuing_trucks)): ?>
                    <form method="POST" data-confirm="Proses seluruh <?= count($queuing_trucks) ?> armada antrean ke terminal yard sekaligus?" data-confirm-title="Konfirmasi Batch Gate-In" data-confirm-tone="primary" class="inline">
                        <input type="hidden" name="gate_action" value="batch_gate_in">
                        <button type="submit" class="px-3 py-1.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center space-x-1.5">
                            <i class="fa-solid fa-bolt-lightning text-amber-300"></i>
                            <span>Batch Gate-In (Semua <?= count($queuing_trucks) ?>)</span>
                        </button>
                    </form>
                    <?php endif; ?>
                    <form method="POST" data-confirm="Muat ulang seluruh 5 skenario antrean armada pra-gate ke status awal?" data-confirm-title="Reset Antrean Demo" data-confirm-tone="danger" class="inline">
                        <input type="hidden" name="gate_action" value="reset_demo_queue">
                        <button type="submit" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition flex items-center space-x-1" title="Reset 5 skenario armada pra-gate">
                            <i class="fa-solid fa-rotate-left text-slate-500"></i>
                            <span>Reset Antrean Demo</span>
                        </button>
                    </form>
                </div>
            </div>

            <?php if (empty($queuing_trucks)): ?>
            <div class="py-8 text-center bg-slate-50/70 rounded-xl border border-dashed border-slate-200 p-6">
                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center text-xl mb-3 shadow-inner">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h4 class="text-sm font-bold text-gray-800">Semua Antrean Telah Diproses</h4>
                <p class="text-xs text-gray-500 mt-1">5 skenario armada sudah masuk atau selesai dilayani.</p>
                <div class="mt-4 flex items-center justify-center space-x-2">
                    <form method="POST" data-confirm="Muat ulang 5 skenario antrean armada pra-gate sekarang?" data-confirm-title="Muat Ulang Antrean" data-confirm-tone="warning" class="inline">
                        <input type="hidden" name="gate_action" value="reset_demo_queue">
                        <button type="submit" class="px-4 py-2 bg-[#0170b9] hover:bg-[#0170b9] text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center space-x-2">
                            <i class="fa-solid fa-arrows-rotate text-amber-400"></i>
                            <span>Muat Ulang 5 Skenario Antrean Pra-Gate</span>
                        </button>
                    </form>
                    <button onclick="showGatePassModal()" class="px-3.5 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-semibold transition">
                        <i class="fa-solid fa-plus mr-1"></i>Daftar Pre-Advice Truk
                    </button>
                </div>
            </div>
            <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <?php
                $meta_map = [
                    'drop_export' => [
                        'tag' => 'SKENARIO 1 • DROP EKSPOR',
                        'badge_bg' => 'bg-blue-100 text-blue-800 border-blue-200',
                        'card_border' => 'hover:border-blue-400',
                        'icon' => 'fa-arrow-down-to-bracket text-blue-600',
                        'lane' => 'IN-01',
                        'desc' => 'Truk bawa kontainer ekspor ex-pabrik. Ditimbang SOLAS VGM &amp; dialokasikan ke Blok B Ekspor.',
                        'btn_label' => '1-Click Gate-In Ekspor (VGM + Blok B)'
                    ],
                    'pick_import' => [
                        'tag' => 'SKENARIO 2 • PICK-UP IMPOR',
                        'badge_bg' => 'bg-amber-100 text-amber-900 border-amber-200',
                        'card_border' => 'hover:border-amber-400',
                        'icon' => 'fa-arrow-up-from-bracket text-amber-600',
                        'lane' => 'IN-04',
                        'desc' => 'Truk chassis kosong masuk ambil box impor di Blok A ex-kereta Priok. SPPB Bea Cukai lolos.',
                        'btn_label' => '1-Click Gate-In Ambil Impor (Blok A)'
                    ],
                    'empty_return' => [
                        'tag' => 'SKENARIO 3 • EMPTY RETURN',
                        'badge_bg' => 'bg-slate-100 text-slate-800 border-slate-300',
                        'card_border' => 'hover:border-slate-400',
                        'icon' => 'fa-rotate-left text-slate-600',
                        'lane' => 'IN-05',
                        'desc' => 'Kembalikan kontainer kosong setelah dibongkar di pabrik ke Depo Empty Blok C untuk stock.',
                        'btn_label' => '1-Click Gate-In Return Kosong (Blok C)'
                    ],
                    'empty_release' => [
                        'tag' => 'SKENARIO 4 • EMPTY RELEASE',
                        'badge_bg' => 'bg-cyan-100 text-cyan-900 border-cyan-200',
                        'card_border' => 'hover:border-cyan-400',
                        'icon' => 'fa-box-open text-cyan-600',
                        'lane' => 'IN-04',
                        'desc' => 'Truk chassis kosong ambil box kosong dari Depo Blok C untuk stuffing ekspor barang pabrik.',
                        'btn_label' => '1-Click Gate-In Ambil Kosong (Blok C)'
                    ],
                    'dual_cycle' => [
                        'tag' => 'SKENARIO 5 • DUAL CYCLE',
                        'badge_bg' => 'bg-purple-100 text-purple-900 border-purple-200',
                        'card_border' => 'hover:border-purple-400',
                        'icon' => 'fa-arrows-rotate text-purple-600',
                        'lane' => 'IN-02',
                        'desc' => 'Efisiensi 100%: Drop box ekspor di Blok B lalu langsung ambil box impor di Blok A.',
                        'btn_label' => '1-Click Gate-In Dual Cycle (Blok B &amp; A)'
                    ]
                ];

                foreach ($queuing_trucks as $qt):
                    $m_type = $qt['mission_type'] ?? 'drop_export';
                    $m_info = $meta_map[$m_type] ?? [
                        'tag' => strtoupper($m_type),
                        'badge_bg' => 'bg-gray-100 text-gray-800 border-gray-200',
                        'card_border' => 'hover:border-gray-400',
                        'icon' => 'fa-truck text-gray-600',
                        'lane' => 'IN-01',
                        'desc' => 'Misi operasional terjadwal dry port.',
                        'btn_label' => 'Proses Gate-In'
                    ];
                    $qt_lane = !empty($qt['gate_lane']) ? cidp_normalize_gate_lane($qt['gate_lane'], 'IN') : $m_info['lane'];
                    $m_info['lane'] = $qt_lane;
                    $has_box = !empty($qt['container_number']);
                ?>
                <div class="bg-white rounded-xl p-4 border border-gray-200/80 shadow-2xs <?= $m_info['card_border'] ?> transition-all flex flex-col justify-between space-y-3 relative group">
                    <div class="space-y-2">
                        <!-- Top Meta Header -->
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 text-[9.5px] font-mono font-extrabold rounded-md border <?= $m_info['badge_bg'] ?>">
                                <?= $m_info['tag'] ?>
                            </span>
                            <span class="text-[10px] font-mono text-gray-400 flex items-center">
                                <i class="fa-solid fa-road mr-1 text-gray-400"></i><?= $m_info['lane'] ?>
                            </span>
                        </div>

                        <!-- Truk Plat & Perusahaan -->
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="font-mono text-base font-extrabold text-gray-900 tracking-wide block">
                                    <?= htmlspecialchars($qt['license_plate']) ?>
                                </span>
                                <span class="text-[11px] text-gray-500 font-semibold block truncate max-w-[210px]">
                                    <?= htmlspecialchars($qt['company']) ?>
                                </span>
                            </div>
                            <span class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center text-sm <?= $m_info['icon'] ?>">
                                <i class="fa-solid <?= explode(' ', $m_info['icon'])[0] ?>"></i>
                            </span>
                        </div>

                        <!-- Info Muatan & Bobot -->
                        <div class="bg-slate-50 rounded-lg p-2.5 border border-slate-200/70 space-y-1.5 text-xs font-sans">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-gray-500">Muatan Box:</span>
                                <?php if ($has_box): ?>
                                    <span class="font-mono font-bold text-indigo-700"><?= htmlspecialchars($qt['container_number']) ?></span>
                                <?php else: ?>
                                    <span class="italic text-amber-700 font-semibold flex items-center"><i class="fa-solid fa-truck-pickup mr-1"></i>Chassis Kosong</span>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-gray-500">Tujuan / Slot:</span>
                                <span class="font-bold text-gray-800">
                                    <i class="fa-solid fa-location-dot text-rose-500 mr-1"></i>Yard Blok <?= htmlspecialchars($qt['target_block'] ?? 'B') ?>
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-gray-500">Bobot Bruto:</span>
                                <span class="font-mono font-bold text-slate-900"><?= number_format($qt['weight_gross'] ?? 30000, 0, ',', '.') ?> kg</span>
                            </div>
                        </div>

                        <p class="text-[10.5px] text-gray-500 leading-tight">
                            <?= $m_info['desc'] ?>
                        </p>
                    </div>

                    <!-- Tombol Aksi 1-Click Gate-In -->
                    <form method="POST" class="pt-2 border-t border-gray-100">
                        <input type="hidden" name="gate_action" value="quick_gate_in">
                        <input type="hidden" name="truck_id" value="<?= $qt['id'] ?>">
                        <input type="hidden" name="license_plate" value="<?= htmlspecialchars($qt['license_plate']) ?>">
                        <input type="hidden" name="container_number" value="<?= htmlspecialchars($qt['container_number'] ?? '') ?>">
                        <input type="hidden" name="mission_type" value="<?= htmlspecialchars($m_type) ?>">
                        <input type="hidden" name="target_block" value="<?= htmlspecialchars($qt['target_block'] ?? 'B') ?>">
                        <input type="hidden" name="lane_id" value="<?= htmlspecialchars($m_info['lane']) ?>">
                        <input type="hidden" name="weight_gross" value="<?= $qt['weight_gross'] ?? 32000 ?>">
                        <input type="hidden" name="weight_tare" value="<?= $qt['weight_tare'] ?? 14000 ?>">
                        <button type="submit" class="w-full py-2 bg-gradient-to-r from-[#0170b9] to-blue-600 hover:from-blue-700 hover:to-[#01558c] text-white rounded-lg text-xs font-bold transition shadow-xs flex items-center justify-center space-x-1.5 group-hover:shadow-sm">
                            <i class="fa-solid fa-bolt text-amber-400"></i>
                            <span><?= $m_info['btn_label'] ?></span>
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- ================================================================= -->
        <!-- KONSOL UJI PEMICU SENSOR GERBANG REAL-TIME (LIVE IOT TRIGGER)     -->
        <!-- ================================================================= -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/90 space-y-4">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-4 border-b border-slate-200">
                <div class="flex items-center space-x-3.5">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-600 text-white flex items-center justify-center text-xl shadow-md shrink-0">
                        <i class="fa-solid fa-satellite-dish"></i>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Live Sensor Trigger
                            </span>
                            <h3 class="text-base font-extrabold text-blue-700">Konsol Pemicu Sensor Gerbang Lapangan (Real Database Events)</h3>
                        </div>
                        <p class="text-xs text-slate-600 mt-1">Uji sensor gate &amp; catat mutasi live ke MySQL.</p>
                    </div>
                </div>
            </div>

            <!-- 3 Trigger Action Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-1">
                <!-- Trigger 1: ANPR & Induction Loop (6 Gate-In Lanes) -->
                <div class="bg-gradient-to-b from-amber-50/70 via-amber-50/20 to-white rounded-xl p-4 border-2 border-amber-300 hover:border-amber-500 hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10.5px] font-mono text-amber-800 uppercase font-extrabold flex items-center gap-1.5 bg-amber-100/90 px-2 py-0.5 rounded-md border border-amber-200">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                HW-01 &bull; ANPR &amp; LOOP
                            </span>
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-700 font-bold rounded text-[9.5px] font-mono border border-slate-200">6 Gate-In (IN-01..06)</span>
                        </div>
                        <h4 class="text-xs font-extrabold text-slate-900 mt-2">ANPR &amp; Loop Gate-In</h4>
                        <p class="text-[11px] text-slate-600 mt-1">Pilih lajur masuk (IN-01..06), baca plat + ISO kontainer &bull; buka palang masuk.</p>
                    </div>
                    <form method="POST" class="mt-4 pt-3 border-t border-amber-200/70 space-y-2">
                        <input type="hidden" name="gate_action" value="trigger_anpr_gate_in">
                        <select name="lane_id" class="w-full px-2.5 py-1.5 bg-white border border-amber-300 focus:border-amber-500 rounded-lg text-[11px] font-mono font-bold text-slate-800">
                            <?php foreach ($inbound_lanes_list as $il): ?>
                                <option value="<?= htmlspecialchars($il['code']) ?>"><?= htmlspecialchars($il['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="license_plate" value="B 9812 UIK" class="px-2.5 py-1.5 bg-white border border-amber-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 rounded-lg text-xs font-mono font-extrabold text-slate-900 uppercase shadow-2xs" placeholder="Plat Truk">
                            <input type="text" name="container_number" value="MSKU9821450" class="px-2.5 py-1.5 bg-white border border-amber-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 rounded-lg text-xs font-mono font-extrabold text-blue-700 uppercase shadow-2xs" placeholder="No. Kontainer">
                        </div>
                        <button type="submit" class="w-full py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-extrabold text-xs rounded-lg transition shadow-xs hover:shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-bolt"></i>
                            <span>Trigger ANPR Masuk &amp; Buka Palang</span>
                        </button>
                    </form>
                </div>

                <!-- Trigger 2: Weighbridge 80 Ton (4 Timbangan SOLAS VGM) -->
                <div class="bg-gradient-to-b from-emerald-50/70 via-emerald-50/20 to-white rounded-xl p-4 border-2 border-emerald-300 hover:border-emerald-500 hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10.5px] font-mono text-emerald-800 uppercase font-extrabold flex items-center gap-1.5 bg-emerald-100/90 px-2 py-0.5 rounded-md border border-emerald-200">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                HW-04 &bull; WEIGHBRIDGE 80T
                            </span>
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-700 font-bold rounded text-[9.5px] font-mono border border-slate-200">4 Deck VGM 80T</span>
                        </div>
                        <h4 class="text-xs font-extrabold text-slate-900 mt-2">Weighbridge 80T (SOLAS VGM)</h4>
                        <p class="text-[11px] text-slate-600 mt-1">Timbang beban statis di lajur VGM &bull; cetak sertifikat SOLAS.</p>
                    </div>
                    <form method="POST" class="mt-4 pt-3 border-t border-emerald-200/70 space-y-2">
                        <input type="hidden" name="gate_action" value="trigger_weighbridge">
                        <select name="lane_id" class="w-full px-2.5 py-1.5 bg-white border border-emerald-300 focus:border-emerald-500 rounded-lg text-[11px] font-mono font-bold text-slate-800">
                            <?php foreach ($vgm_lanes_list as $vl): ?>
                                <option value="<?= htmlspecialchars($vl['code']) ?>"><?= htmlspecialchars($vl['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="license_plate" value="B 9812 UIK" class="px-2.5 py-1.5 bg-white border border-emerald-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 rounded-lg text-xs font-mono font-extrabold text-slate-900 uppercase shadow-2xs" placeholder="Plat Truk">
                            <input type="number" name="gross_weight_kg" value="32450" class="px-2.5 py-1.5 bg-white border border-emerald-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 rounded-lg text-xs font-mono font-extrabold text-emerald-800 text-right shadow-2xs" placeholder="Berat Bruto (kg)">
                        </div>
                        <button type="submit" class="w-full py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold text-xs rounded-lg transition shadow-xs hover:shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-scale-balanced"></i>
                            <span>Trigger Timbang &amp; Terbitkan VGM</span>
                        </button>
                    </form>
                </div>

                <!-- Trigger 3: Dual-Clearance Gate Outbound (4 Gate-Out Lanes) -->
                <div class="bg-gradient-to-b from-blue-50/70 via-blue-50/20 to-white rounded-xl p-4 border-2 border-blue-300 hover:border-blue-500 hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10.5px] font-mono text-blue-800 uppercase font-extrabold flex items-center gap-1.5 bg-blue-100/90 px-2 py-0.5 rounded-md border border-blue-200">
                                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                                HW-05 &bull; BARRIER GATE OUT
                            </span>
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-700 font-bold rounded text-[9.5px] font-mono border border-slate-200">4 Gate-Out (OUT-01..04)</span>
                        </div>
                        <h4 class="text-xs font-extrabold text-slate-900 mt-2">Gate-Out Dual Clearance</h4>
                        <p class="text-[11px] text-slate-600 mt-1">Validasi SPPB CEISA + Billing Lunas &bull; buka palang keluar (OUT-01..04).</p>
                    </div>
                    <form method="POST" class="mt-4 pt-3 border-t border-blue-200/70 space-y-2">
                        <input type="hidden" name="gate_action" value="release_gate_out">
                        <select name="lane_id" class="w-full px-2.5 py-1.5 bg-white border border-blue-300 focus:border-blue-500 rounded-lg text-[11px] font-mono font-bold text-slate-800">
                            <?php foreach ($outbound_lanes_list as $ol): ?>
                                <option value="<?= htmlspecialchars($ol['code']) ?>"><?= htmlspecialchars($ol['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="license_plate" value="B 9812 UIK" class="px-2.5 py-1.5 bg-white border border-blue-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 rounded-lg text-xs font-mono font-extrabold text-slate-900 uppercase shadow-2xs" placeholder="Plat Truk">
                            <input type="text" name="container_number" value="MSKU9821450" class="px-2.5 py-1.5 bg-white border border-blue-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 rounded-lg text-xs font-mono font-extrabold text-blue-700 uppercase shadow-2xs" placeholder="No. Kontainer">
                        </div>
                        <button type="submit" class="w-full py-2 bg-gradient-to-r from-[#0170b9] to-blue-600 hover:from-blue-700 hover:to-[#015891] text-white font-extrabold text-xs rounded-lg transition shadow-xs hover:shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-door-open"></i>
                            <span>Trigger Rilis Gate-Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <?php
        $gate_snapshot = get_live_twin_state($pdo, false);
        $gate_live_lanes = $gate_snapshot['physical']['gate']['lanes'] ?? [];
        ?>
        <!-- Live Gate Plaza 10-Lane Telemetry Console (6 Gate-In + 4 Gate-Out) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-xl bg-blue-50 text-[#0170b9] border border-blue-200 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-road"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">Monitor Telemetri Plaza Gate 10 Lajur (6 Gate-In &amp; 4 Gate-Out)</h3>
                        <p class="text-[11px] text-slate-500">Status palang barrier, kamera ANPR/OCR ISO 6346, serta beban VGM SOLAS 80T secara real-time per lajur.</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                    <button type="button" onclick="filterGateLaneCards('all')" data-lane-filter="all" class="gate-lane-filter-btn px-2.5 py-1 rounded-lg bg-[#0170b9] text-white font-bold cursor-pointer">Semua (10)</button>
                    <button type="button" onclick="filterGateLaneCards('IN')" data-lane-filter="IN" class="gate-lane-filter-btn px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-sky-50 hover:text-sky-700 font-bold cursor-pointer">6 Gate-In (IN-01..06)</button>
                    <button type="button" onclick="filterGateLaneCards('OUT')" data-lane-filter="OUT" class="gate-lane-filter-btn px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-orange-50 hover:text-orange-700 font-bold cursor-pointer">4 Gate-Out (OUT-01..04)</button>
                    <button type="button" onclick="refreshGateLanesLive()" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold cursor-pointer" title="Segarkan State Live 10 Lajur">
                        <i class="fa-solid fa-rotate mr-1"></i>Sync
                    </button>
                </div>
            </div>

            <div id="gateTenLaneGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <?php foreach ($gate_lanes_catalog as $code => $meta):
                    $lst = $gate_live_lanes[$code] ?? [];
                    $isIn = ($meta['direction'] === 'IN');
                    $isOpen = (($lst['barrier'] ?? 'CLOSED') === 'OPEN');
                    $plateTxt = !empty($lst['truck_plate']) ? $lst['truck_plate'] : ($isIn ? ($code === 'IN-01' && !empty($latest_truck['license_plate']) ? $latest_truck['license_plate'] : 'Siaga ANPR') : 'Siaga SPPB');
                    $ctrTxt = !empty($lst['container_number']) ? $lst['container_number'] : 'Menunggu Truk';
                    $wtKg = (float)($lst['weight_kg'] ?? 0);
                    $wtTxt = !empty($meta['has_weighbridge']) ? ($wtKg > 0 ? number_format($wtKg, 0, ',', '.') . ' kg' : '0 kg (Zeroed)') : 'Auto Bypass';
                    $borderCls = $isOpen
                        ? 'border-emerald-400 bg-gradient-to-b from-emerald-50/70 to-white ring-1 ring-emerald-200'
                        : ($isIn ? 'border-sky-200/90 bg-gradient-to-b from-sky-50/40 to-white hover:border-sky-400' : 'border-orange-200/90 bg-gradient-to-b from-orange-50/40 to-white hover:border-orange-400');
                    $badgeCls = $isOpen ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : ($isIn ? 'bg-sky-100 text-sky-800 border-sky-200' : 'bg-orange-100 text-orange-800 border-orange-200');
                    $dotCls = $isOpen ? 'bg-emerald-500 animate-ping' : ($isIn ? 'bg-sky-500' : 'bg-orange-500');
                ?>
                <div data-gate-lane-card="<?= htmlspecialchars($code) ?>" data-lane-dir="<?= htmlspecialchars($meta['direction']) ?>" class="rounded-xl p-3.5 border-2 <?= $borderCls ?> shadow-2xs transition-all flex flex-col justify-between space-y-2.5">
                    <div>
                        <div class="flex items-center justify-between gap-1 pb-2 border-b border-slate-100">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span data-lane-dot class="w-2 h-2 rounded-full <?= $dotCls ?> shrink-0"></span>
                                <span class="font-mono font-black text-xs text-slate-900"><?= htmlspecialchars($code) ?></span>
                            </div>
                            <span data-lane-status class="px-2 py-0.5 rounded-full text-[9.5px] font-mono font-extrabold border <?= $badgeCls ?>">
                                <?= htmlspecialchars($lst['barrier'] ?? 'CLOSED') === 'OPEN' ? 'OPEN' : 'STANDBY' ?>
                            </span>
                        </div>
                        <div class="text-[10.5px] font-bold text-slate-700 mt-1.5 truncate" title="<?= htmlspecialchars($meta['name']) ?>"><?= htmlspecialchars($meta['short_name']) ?></div>
                        <div class="mt-2 space-y-1 text-[10.5px] font-mono bg-white rounded-lg p-2 border border-slate-100">
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-slate-400">Plat:</span>
                                <span data-lane-plate class="font-bold text-slate-900 truncate"><?= htmlspecialchars($plateTxt) ?></span>
                            </div>
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-slate-400">Box:</span>
                                <span data-lane-ctr class="font-bold text-blue-700 truncate"><?= htmlspecialchars($ctrTxt) ?></span>
                            </div>
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-slate-400"><?= !empty($meta['has_weighbridge']) ? 'VGM 80T:' : 'Mode:' ?></span>
                                <span data-lane-weight class="font-bold text-emerald-700 truncate"><?= htmlspecialchars($wtTxt) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-1.5 border-t border-slate-100 text-[10px]">
                        <div class="flex flex-wrap gap-1">
                            <?php if (!empty($meta['has_weighbridge'])): ?>
                                <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 font-bold">80T VGM</span>
                            <?php else: ?>
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-semibold"><?= $isIn ? 'Express' : 'Interlock' ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="dashboard.php?page=twin&view=gate" class="text-[#0170b9] hover:underline font-bold flex items-center gap-1" title="Lihat Lajur di Digital Twin 3D">
                            <i class="fa-solid fa-cube text-[9px]"></i> Twin 3D
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Log Aktivitas Transaksi Gerbang Real-Time (Live dari MySQL) -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-900 flex items-center">
                        <i class="fa-solid fa-clock-rotate-left text-[#0170b9] mr-2"></i>
                        Log Transaksi Gerbang Real-Time
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">Riwayat masuk, antre, dan keluar armada.</p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-bold border border-emerald-200" id="gateLogCountBadge">
                        <i class="fa-solid fa-database mr-1"></i> <?= count($gate_trucks) ?> Riwayat Terdata
                    </span>
                    <select id="gateLogLimitSelect" data-limit-target="#gateLogTable" data-count-target="#gateLogCountBadge" data-count-unit="Riwayat" class="px-2.5 py-1 bg-slate-50 border border-gray-200 rounded-lg text-xs font-bold text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="10" selected>Tampil: 10</option>
                        <option value="25">Tampil: 25</option>
                        <option value="50">Tampil: 50</option>
                        <option value="all">Semua</option>
                    </select>
                    <button type="button" onclick="if(window.cidpSoftRefreshContent) { cidpSoftRefreshContent({ title: 'Data Disegarkan', message: 'Riwayat & antrean gerbang diperbarui tanpa reload halaman.', type: 'info' }); }" class="p-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition cursor-pointer" title="Segarkan Data Tanpa Refresh">
                        <i class="fa-solid fa-rotate-right text-xs"></i>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs" id="gateLogTable">
                    <thead>
                        <tr class="bg-slate-50 text-gray-500 uppercase tracking-wider font-semibold border-b border-gray-200">
                            <th class="py-3 px-3 text-center w-10">No</th>
                            <th class="py-3 px-3.5">Waktu</th>
                            <th class="py-3 px-3.5">Lane</th>
                            <th class="py-3 px-3.5">Plat Nomor (ANPR)</th>
                            <th class="py-3 px-3.5">Pengemudi / Transporter</th>
                            <th class="py-3 px-3.5">Misi Operasional</th>
                            <th class="py-3 px-3.5">Kontainer / DO</th>
                            <th class="py-3 px-3.5">Status Gerbang</th>
                            <th class="py-3 px-3.5 text-center">Dokumen</th>
                        </tr>
                    </thead>
                    <tbody id="gate-log-tbody" class="divide-y divide-gray-100 text-gray-700">
                        <?php if (empty($gate_trucks)): ?>
                        <tr data-empty-row>
                            <td colspan="9" class="text-center py-8 text-gray-400">
                                <i class="fa-solid fa-door-open text-3xl mb-2 text-gray-300 block"></i>
                                Belum ada data gerbang. Silakan registrasi pra-gate atau jalankan simulasi di Panel Simulasi 3D.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($gate_trucks as $idx => $t): 
                            $time = !empty($t['gate_out_time']) ? $t['gate_out_time'] : (!empty($t['gate_in_time']) ? $t['gate_in_time'] : $t['created_at']);
                            $isOut = !empty($t['gate_out_time']) || $t['status'] === 'gate_out';
                            $isIn = $t['status'] === 'in_yard' || $t['status'] === 'loading';
                            $isQueue = $t['status'] === 'queuing';
                            $fallbackIn  = 'IN-0' . (($idx % 6) + 1);
                            $fallbackOut = 'OUT-0' . (($idx % 4) + 1);
                            $lane = $isOut
                                ? (!empty($t['gate_out_lane']) ? cidp_normalize_gate_lane($t['gate_out_lane'], 'OUT') : $fallbackOut)
                                : (!empty($t['gate_lane']) ? cidp_normalize_gate_lane($t['gate_lane'], 'IN') : $fallbackIn);
                            $laneClass = $isOut ? 'bg-orange-50 text-orange-700 border border-orange-200' : 'bg-blue-50 text-blue-700 border border-blue-200';
                            $mType = $t['mission_type'] ?? '';
                            if ($mType === 'drop_export') {
                                $jobBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200 flex items-center w-fit"><i class="fa-solid fa-arrow-down-to-bracket mr-1 text-blue-600"></i>Drop Ekspor</span>';
                            } elseif ($mType === 'pick_import') {
                                $jobBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-200 flex items-center w-fit"><i class="fa-solid fa-arrow-up-from-bracket mr-1 text-amber-600"></i>Pick-Up Impor</span>';
                            } elseif ($mType === 'empty_return') {
                                $jobBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-300 flex items-center w-fit"><i class="fa-solid fa-rotate-left mr-1 text-slate-600"></i>Empty Return</span>';
                            } elseif ($mType === 'empty_release') {
                                $jobBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-100 text-cyan-900 border border-cyan-200 flex items-center w-fit"><i class="fa-solid fa-box-open mr-1 text-cyan-600"></i>Empty Release</span>';
                            } elseif ($mType === 'dual_cycle') {
                                $jobBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-900 border border-purple-200 flex items-center w-fit"><i class="fa-solid fa-arrows-rotate mr-1 text-purple-600"></i>Dual Cycle</span>';
                            } else {
                                $jobBadge = ($t['job_type'] === 'pick_up') 
                                    ? '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200"><i class="fa-solid fa-arrow-up-from-bracket mr-1"></i>Pick-Up</span>' 
                                    : '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200"><i class="fa-solid fa-arrow-down-to-bracket mr-1"></i>Drop-Off</span>';
                            }
                        ?>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-3 text-center font-mono text-gray-400" data-row-num><?= $idx + 1 ?></td>
                            <td class="py-3 px-3.5 font-mono text-gray-500 whitespace-nowrap"><?= date('H:i:s d/m', strtotime($time)) ?></td>
                            <td class="py-3 px-3.5"><span class="px-2 py-0.5 rounded text-[11px] font-bold <?= $laneClass ?>"><?= $lane ?></span></td>
                            <td class="py-3 px-3.5 font-mono font-bold text-gray-900"><?= htmlspecialchars($t['license_plate']) ?></td>
                            <td class="py-3 px-3.5">
                                <div class="font-bold text-gray-800"><?= htmlspecialchars($t['driver_name'] ?? 'Pengemudi CIDP') ?></div>
                                <div class="text-[11px] text-gray-400"><?= htmlspecialchars($t['company'] ?? 'PT Logistik Nasional') ?></div>
                            </td>
                            <td class="py-3 px-3.5"><?= $jobBadge ?></td>
                            <td class="py-3 px-3.5 font-mono">
                                <?php if (!empty($t['container_number'])): ?>
                                    <span class="font-bold text-indigo-700 block"><?= htmlspecialchars($t['container_number']) ?></span>
                                <?php elseif (!empty($t['do_number'])): ?>
                                    <span class="text-amber-700 font-semibold block"><?= htmlspecialchars($t['do_number']) ?></span>
                                <?php else: ?>
                                    <span class="text-gray-400 italic block font-sans">Chassis Kosong</span>
                                <?php endif; ?>
                                <?php if (!empty($t['destination']) || !empty($t['target_block'])): ?>
                                    <span class="text-[10px] font-sans text-gray-500 flex items-center mt-0.5">
                                        <i class="fa-solid fa-location-dot mr-1 text-rose-500"></i><?= htmlspecialchars($t['destination'] ?: ('Blok ' . $t['target_block'])) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-3.5">
                                <?php if ($isOut): ?>
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-full text-[10px] font-bold flex items-center w-fit"><i class="fa-solid fa-check mr-1 text-slate-500"></i>SELESAI GATE-OUT</span>
                                <?php elseif ($isIn): ?>
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full text-[10px] font-bold flex items-center w-fit"><i class="fa-solid fa-circle-dot mr-1 text-emerald-500"></i>DI TERMINAL YARD</span>
                                <?php elseif ($isQueue): ?>
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full text-[10px] font-bold flex items-center w-fit"><i class="fa-solid fa-hourglass-half mr-1 text-amber-500"></i>ANTRIAN PRA-GATE</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-[10px] font-bold flex items-center w-fit"><?= strtoupper(htmlspecialchars($t['status'])) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-3.5 text-center">
                                <div class="flex items-center justify-center space-x-1">
                                    <button onclick="viewGatePassDetail('<?= htmlspecialchars($t['license_plate']) ?>', '<?= htmlspecialchars($t['container_number'] ?? $t['do_number'] ?? '-') ?>', '<?= htmlspecialchars($t['driver_name'] ?? '') ?>')" class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold rounded text-[11px] transition" title="Lihat E-Gate Pass">
                                        <i class="fa-solid fa-qrcode mr-1"></i> Pass
                                    </button>
                                    
                                    <?php if ($isIn && !empty($t['container_number'])): 
                                        $cRef = $t['container_number'];
                                        $cStat = $ctr_customs_map[$cRef] ?? 'PENDING';
                                        $bStat = $ctr_billing_map[$cRef] ?? 'PAID';
                                        $isCleared = ($cStat === 'SPPB_CLEARED' && $bStat === 'PAID');
                                    ?>
                                        <?php if ($isCleared): ?>
                                        <form method="POST" data-confirm="Pabean SPPB: Lolos. Billing ERP: Lunas. Buka palang rilis Gate-Out untuk armada <?= htmlspecialchars($t['license_plate']) ?>?" data-confirm-title="Konfirmasi Rilis Gate-Out" data-confirm-target="<?= htmlspecialchars($t['license_plate']) ?>" data-confirm-tone="warning" class="inline">
                                            <input type="hidden" name="gate_action" value="release_gate_out">
                                            <input type="hidden" name="truck_id" value="<?= $t['id'] ?? 0 ?>">
                                            <input type="hidden" name="license_plate" value="<?= htmlspecialchars($t['license_plate']) ?>">
                                            <input type="hidden" name="container_number" value="<?= htmlspecialchars($cRef) ?>">
                                            <button type="submit" class="px-2 py-1 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded text-[11px] transition shadow-2xs flex items-center" title="Dual-Clearance Approved (SPPB + Lunas) - Rilis Gate Out">
                                                <i class="fa-solid fa-door-open mr-1"></i> Rilis Out
                                            </button>
                                        </form>
                                        <?php else: ?>
                                        <button onclick="checkDualClearanceLock('<?= htmlspecialchars($cRef) ?>', '<?= htmlspecialchars($t['license_plate']) ?>', '<?= $cStat ?>', '<?= $bStat ?>')" class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold rounded text-[11px] transition border border-amber-200 cursor-pointer" title="Cek Dual Clearance">
                                            <i class="fa-solid fa-lock mr-0.5"></i> Lock
                                        </button>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <button onclick="openEdifactModal()" class="px-2 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded text-[11px] transition" title="Lihat EDI CODECO">
                                        <i class="fa-solid fa-file-code"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- TAB: INTERACTIVE OCR & ISO 6346 CHECK DIGIT ENGINE -->
    <!-- ======================================================================= -->
    <div id="tab-ocr-iso" class="tab-content hidden space-y-6">
        <!-- Banner Intro -->
        <div class="bg-white rounded-2xl p-5 text-slate-800 shadow-2xs border border-slate-200 border-l-4 border-l-orange-500 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="inline-flex items-center space-x-2 px-2.5 py-0.5 rounded-full bg-orange-50 border border-orange-200 text-xs font-mono font-bold text-orange-700">
                    <i class="fa-solid fa-microchip"></i>
                    <span>ISO 6346:1995 / BIC Standard Modulo 11</span>
                </div>
                <h2 class="text-lg sm:text-xl font-extrabold tracking-tight text-slate-900">Portal OCR &amp; Check Digit ISO 6346</h2>
                <p class="text-xs text-slate-600">Validasi 4-Camera OCR &amp; Modulo 11 otomatis.</p>
            </div>
            <div class="flex items-center space-x-2 flex-shrink-0">
                <button type="button" onclick="triggerOcrDoorScan()" class="px-4 py-2.5 bg-orange-600 hover:bg-orange-500 text-white font-bold rounded-xl text-xs transition-all shadow-2xs flex items-center space-x-2 cursor-pointer">
                    <i class="fa-solid fa-barcode"></i>
                    <span>Scan OCR Otomatis</span>
                </button>
            </div>
        </div>

        <!-- Presets Selection Bar -->
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-3">
                <i class="fa-solid fa-images mr-1.5 text-blue-600"></i>Pilih Sampel Kontainer Realistis (Dry Port Test Cases):
            </span>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <button type="button" onclick="selectOcrPreset('MSKU')" id="preset-btn-MSKU" class="ocr-preset-btn p-3 rounded-xl border-2 border-blue-500 bg-blue-50/50 text-left transition-all hover:shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-blue-600 block uppercase">Maersk Line • 40HC</span>
                        <span class="font-mono font-bold text-sm text-gray-900">MSKU 982145-8</span>
                        <span class="text-[11px] text-gray-500 block">45G1 • 32.500 kg</span>
                    </div>
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded">Valid</span>
                </button>
                <button type="button" onclick="selectOcrPreset('ONEU')" id="preset-btn-ONEU" class="ocr-preset-btn p-3 rounded-xl border border-gray-200 bg-white text-left transition-all hover:shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-pink-600 block uppercase">Ocean Network Express • 40GP</span>
                        <span class="font-mono font-bold text-sm text-gray-900">ONEU 661298-4</span>
                        <span class="text-[11px] text-gray-500 block">42G1 • 30.480 kg</span>
                    </div>
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded">Valid</span>
                </button>
                <button type="button" onclick="selectOcrPreset('EMCU')" id="preset-btn-EMCU" class="ocr-preset-btn p-3 rounded-xl border border-gray-200 bg-white text-left transition-all hover:shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-emerald-600 block uppercase">Evergreen Marine • 20GP</span>
                        <span class="font-mono font-bold text-sm text-gray-900">EMCU 551820-2</span>
                        <span class="text-[11px] text-gray-500 block">22G1 • 24.000 kg</span>
                    </div>
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded">Valid</span>
                </button>
                <button type="button" onclick="selectOcrPreset('INVALID')" id="preset-btn-INVALID" class="ocr-preset-btn p-3 rounded-xl border border-gray-200 bg-white text-left transition-all hover:shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-rose-600 block uppercase">Test Checksum Gagal</span>
                        <span class="font-mono font-bold text-sm text-rose-700">MSKU 982145-3</span>
                        <span class="text-[11px] text-gray-500 block">Digit Salah (Harusnya 8)</span>
                    </div>
                    <span class="px-2 py-0.5 bg-rose-100 text-rose-700 text-[10px] font-bold rounded">Invalid</span>
                </button>
            </div>
        </div>

        <!-- Main OCR Visualizer & Calculation Engine Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            <!-- Left: Simulated Container Door & Camera Detection (5 cols) -->
            <div class="xl:col-span-5 space-y-4">
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 text-slate-800 shadow-xs shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3 border-b border-slate-800 pb-3">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-xs font-mono font-bold text-slate-700">CAM-02: REAR DOOR OCR GANTRY</span>
                        </div>
                        <span id="ocr-fps-tag" class="text-[10px] font-mono text-slate-400">1080P @ 30 FPS • HDR ON</span>
                    </div>

                    <!-- Visual Container Door Box Graphic -->
                    <div class="relative w-full aspect-[4/3] bg-gradient-to-b from-slate-800 to-slate-850 rounded-xl border-2 border-slate-700 p-4 flex flex-col justify-between overflow-hidden shadow-inner">
                        <!-- Scanning Line Animation -->
                        <div id="ocr-scanline" class="absolute left-0 right-0 h-1 bg-gradient-to-r from-transparent via-cyan-400 to-transparent shadow-[0_0_12px_#38bdf8] opacity-0 transition-opacity duration-300 pointer-events-none z-20 top-0"></div>

                        <!-- Top Door Hinges & Corrugation Effect -->
                        <div class="flex justify-between items-center text-slate-500 text-[11px] px-2 border-b border-slate-700/60 pb-1">
                            <span><i class="fa-solid fa-lock"></i> LOCK BAR L</span>
                            <span class="text-[10px] font-mono text-slate-400" id="door-seal-tag">SEAL: ML-ID992810</span>
                            <span>LOCK BAR R <i class="fa-solid fa-lock"></i></span>
                        </div>

                        <!-- Container Identification Decal Graphic -->
                        <div class="my-auto text-right space-y-1 pr-3">
                            <!-- Bounding Box Container Number -->
                            <div id="bbox-container" class="inline-block p-1.5 border-2 border-dashed border-emerald-400 bg-emerald-950/40 rounded transition-all">
                                <div class="text-[9px] font-mono text-emerald-300 text-left">OCR CONF: 99.6% [BOX-ID]</div>
                                <div id="door-container-no" class="font-mono text-2xl sm:text-3xl font-black tracking-widest text-white drop-shadow">
                                    MSKU 982145<span class="ml-1 px-1 border border-white/40 bg-white/10 rounded">8</span>
                                </div>
                            </div>

                            <!-- Bounding Box Size Type -->
                            <div class="block">
                                <div id="bbox-sizetype" class="inline-block p-1 border border-dashed border-cyan-400 bg-cyan-950/40 rounded mt-1">
                                    <span class="text-[9px] font-mono text-cyan-300 mr-2">SIZE/TYPE:</span>
                                    <span id="door-sizetype" class="font-mono text-sm font-bold text-cyan-200">45G1</span>
                                </div>
                            </div>

                            <!-- Weight Specifications Stencil -->
                            <div class="pt-2 text-[10px] sm:text-[11px] font-mono text-slate-300 space-y-0.5">
                                <div>MAX. GROSS: <span id="door-max-gross" class="font-bold text-white">32.500 KG / 71.650 LBS</span></div>
                                <div>TARE WT: <span id="door-tare" class="font-bold text-white">3.900 KG / 8.598 LBS</span></div>
                                <div>NET / PAYLOAD: <span id="door-payload" class="font-bold text-white">28.600 KG / 63.052 LBS</span></div>
                                <div>MAX CUBE: <span id="door-cube" class="font-bold text-white">76.4 CU.M / 2.700 CU.FT</span></div>
                            </div>
                        </div>

                        <!-- CSC Safety Approval Plate & Barcode at bottom -->
                        <div class="flex items-center justify-between border-t border-slate-700/60 pt-2 text-slate-400 text-[10px]">
                            <div class="flex items-center space-x-2">
                                <span class="px-1.5 py-0.5 bg-slate-800 border border-slate-600 rounded font-mono text-slate-300">CSC APPROVED</span>
                                <span class="font-mono text-slate-400">BV-IND-2024</span>
                            </div>
                            <div class="font-mono text-slate-400 flex items-center space-x-1">
                                <i class="fa-solid fa-barcode text-sm"></i>
                                <span id="door-bottom-code">MSKU9821458</span>
                            </div>
                        </div>
                    </div>

                    <!-- OCR Engine Status Footer -->
                    <div class="mt-4 grid grid-cols-2 gap-3 text-xs font-mono">
                        <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                            <span class="text-slate-500 block text-[10px] font-bold">RECOGNITION LATENCY</span>
                            <span id="ocr-latency-val" class="font-bold text-emerald-700 text-sm">184 ms</span>
                            <span class="text-[10px] text-slate-500 block">TensorRT Inference</span>
                        </div>
                        <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                            <span class="text-slate-500 block text-[10px] font-bold">OVERALL CONFIDENCE</span>
                            <span id="ocr-overall-conf" class="font-bold text-emerald-700 text-sm">99.4%</span>
                            <span class="text-[10px] text-slate-500 block">4/4 Cameras Voted</span>
                        </div>
                    </div>
                </div>

                <!-- Fast Actions -->
                <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm space-y-2">
                    <button type="button" onclick="transferOcrToGateLane()" class="w-full py-2.5 px-4 bg-[#0170b9] hover:bg-[#0170b9] text-white font-bold rounded-xl text-xs transition-colors flex items-center justify-center space-x-2 shadow-xs">
                        <i class="fa-solid fa-id-card"></i>
                        <span>Gunakan Kontainer untuk Registrasi Pra-Gate</span>
                    </button>
                    <button type="button" onclick="openEdifactModalCurrent()" class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl text-xs transition-colors flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-file-invoice"></i>
                        <span>Generate UN/EDIFACT CODECO</span>
                    </button>
                </div>
            </div>

            <!-- Right: ISO 6346 Mathematical Check Digit Engine (7 cols) -->
            <div class="xl:col-span-7 space-y-4">
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-4 mb-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 flex items-center">
                                <i class="fa-solid fa-calculator text-[#0170b9] mr-2"></i>
                                Kalkulator Standar ISO 6346 & Check Digit
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">Validasi matematis algoritma modulus 11 dengan pembobotan eksponensial 2^i</p>
                        </div>
                        <span id="iso-badge-status" class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full font-bold text-xs self-start sm:self-auto flex items-center space-x-1">
                            <i class="fa-solid fa-circle-check"></i>
                            <span id="iso-badge-text">VALID ISO 6346</span>
                        </span>
                    </div>

                    <!-- Interactive Input Field -->
                    <div class="space-y-2 mb-5">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Nomor Kontainer (10 atau 11 Karakter):
                        </label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input type="text" id="iso-input-container" value="MSKU9821458" maxlength="11" oninput="handleContainerInput(this.value)" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 font-mono text-base font-bold uppercase tracking-wider text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none uppercase" placeholder="Contoh: MSKU9821458">
                                <span class="absolute right-3 top-2.5 text-xs font-mono text-gray-400" id="iso-char-count">11/11</span>
                            </div>
                            <button type="button" onclick="selectOcrPreset('MSKU')" class="px-3 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-xl transition-colors">
                                Reset
                            </button>
                        </div>
                    </div>

                    <!-- Breakdown Badges -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 mb-5 text-center">
                        <div class="bg-blue-50/70 border border-blue-100 rounded-xl p-2.5">
                            <span class="text-[10px] font-bold text-blue-600 uppercase block">Owner Prefix</span>
                            <span id="iso-owner-code" class="font-mono font-bold text-sm text-gray-900">MSK</span>
                            <span id="iso-owner-name" class="text-[10px] text-gray-500 block truncate">Maersk Line</span>
                        </div>
                        <div class="bg-indigo-50/70 border border-indigo-100 rounded-xl p-2.5">
                            <span class="text-[10px] font-bold text-indigo-600 uppercase block">Category Identifier</span>
                            <span id="iso-cat-code" class="font-mono font-bold text-sm text-gray-900">U</span>
                            <span class="text-[10px] text-gray-500 block">Freight Container</span>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5">
                            <span class="text-[10px] font-bold text-slate-600 uppercase block">Serial Number</span>
                            <span id="iso-serial-code" class="font-mono font-bold text-sm text-gray-900">982145</span>
                            <span class="text-[10px] text-gray-500 block">6 Digit Unik</span>
                        </div>
                        <div id="iso-check-box" class="bg-emerald-50 border border-emerald-200 rounded-xl p-2.5">
                            <span class="text-[10px] font-bold text-emerald-700 uppercase block">Check Digit</span>
                            <span id="iso-check-digit" class="font-mono font-extrabold text-sm text-emerald-700">8</span>
                            <span id="iso-check-sub" class="text-[10px] text-emerald-600 font-semibold block">Cocok (Rem 8)</span>
                        </div>
                    </div>

                    <!-- Mathematical Step-by-Step Table -->
                    <div class="border border-gray-100 rounded-xl overflow-hidden mb-4">
                        <div class="bg-slate-50 px-3.5 py-2 border-b border-gray-200 flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-700">Tabel Kalkulasi Modulo 11 Langkah Demi Langkah</span>
                            <span class="text-[11px] text-gray-500 font-mono">Rumus: &sum; (Nilai &times; 2<sup>i</sup>) mod 11</span>
                        </div>
                        <div class="overflow-x-auto max-h-64 overflow-y-auto">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-100 text-gray-600 font-semibold uppercase tracking-wider text-[10px] sticky top-0">
                                    <tr>
                                        <th class="py-2 px-3 text-center">Pos (i)</th>
                                        <th class="py-2 px-3 text-center">Karakter</th>
                                        <th class="py-2 px-3 text-center">Nilai Karakter</th>
                                        <th class="py-2 px-3 text-center">Bobot (2<sup>i</sup>)</th>
                                        <th class="py-2 px-3 text-right">Hasil Kali</th>
                                    </tr>
                                </thead>
                                <tbody id="iso-calc-tbody" class="divide-y divide-gray-100 font-mono">
                                    <!-- Populated via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Modulo 11 Summary Result Panel -->
                    <div id="iso-calc-summary" class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="space-y-1 text-center sm:text-left">
                            <div class="text-xs text-gray-600">
                                Total Penjumlahan Bobot (&sum; Produk): <span id="iso-sum-total" class="font-mono font-bold text-gray-900 text-sm">4958</span>
                            </div>
                            <div class="text-xs text-gray-600">
                                Sisa Bagi Modulo 11: <span id="iso-mod-formula" class="font-mono font-semibold text-blue-700">4958 mod 11 = 8</span>
                            </div>
                            <div class="text-[11px] text-gray-500 italic">
                                *Catatan ISO 6346: Jika sisa bagi adalah 10, digit pemeriksa ditetapkan menjadi 0.
                            </div>
                        </div>
                        <div id="iso-verdict-card" class="bg-emerald-500 text-white px-4 py-2.5 rounded-xl text-center shadow-xs flex-shrink-0 min-w-[140px]">
                            <span class="text-[10px] uppercase font-bold block opacity-90">Hasil Verifikasi</span>
                            <span id="iso-verdict-text" class="text-base font-black tracking-wide">VALID PASS</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- TAB 2: KATALOG HARDWARE & BILL OF MATERIALS (BOM 26 ITEM) -->
    <!-- ======================================================================= -->
    <div id="tab-katalog" class="tab-content hidden space-y-6">
        <!-- Ringkasan CAPEX & Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 border-l-4 border-l-orange-500 shadow-2xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Nilai Investasi (CAPEX)</span>
                <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">Rp <?= number_format($total_capex, 0, ',', '.') ?></p>
                <p class="text-xs text-slate-500 mt-2">Mencakup 26 jenis hardware untuk lahan operasional 35 Ha</p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Klasifikasi Perangkat</span>
                <div class="mt-2 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-emerald-700 font-bold flex items-center">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2"></span> Live Software / Telemetri
                        </span>
                        <span class="font-bold text-gray-900"><?= $live_count ?> Item (Disimulasikan)</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-indigo-700 font-bold flex items-center">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 mr-2"></span> Blueprint & Fasilitas Fisik
                        </span>
                        <span class="font-bold text-gray-900"><?= $blueprint_count ?> Item (Infrastruktur)</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Dokumen Acuan Resmi</span>
                    <p class="text-xs text-gray-700 font-semibold mt-1">File Word & Markdown Tersedia di Direktori:</p>
                    <p class="text-[11px] font-mono text-gray-500 bg-gray-50 p-1.5 rounded mt-1 border border-gray-200">docs/hardware/KATALOG_HARDWARE_CIDP.md</p>
                </div>
                <div class="mt-3 flex items-center space-x-2">
                    <a href="docs/hardware/KATALOG_HARDWARE_CIDP.md" target="_blank" class="text-xs text-[#0170b9] hover:underline font-bold flex items-center">
                        <i class="fa-solid fa-file-lines mr-1"></i> Buka Katalog MD
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex flex-wrap gap-1.5" id="category-filter-buttons">
                    <button onclick="filterCategory('all')" class="filter-cat-btn px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-[#0170b9] text-white" data-cat="all">Semua (26)</button>
                    <button onclick="filterCategory('Otomasi Gate')" class="filter-cat-btn px-3 py-1.5 rounded-lg text-xs font-semibold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200" data-cat="Otomasi Gate">Gate (8)</button>
                    <button onclick="filterCategory('Yard & Alat Angkat')" class="filter-cat-btn px-3 py-1.5 rounded-lg text-xs font-semibold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200" data-cat="Yard & Alat Angkat">Yard & Crane (4)</button>
                    <button onclick="filterCategory('Cold Chain Reefer')" class="filter-cat-btn px-3 py-1.5 rounded-lg text-xs font-semibold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200" data-cat="Cold Chain Reefer">Cold Chain (2)</button>
                    <button onclick="filterCategory('Intermodal Rail Siding')" class="filter-cat-btn px-3 py-1.5 rounded-lg text-xs font-semibold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200" data-cat="Intermodal Rail Siding">Rail Siding (3)</button>
                    <button onclick="filterCategory('Keamanan & Bea Cukai')" class="filter-cat-btn px-3 py-1.5 rounded-lg text-xs font-semibold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200" data-cat="Keamanan & Bea Cukai">Keamanan & Bea Cukai (5)</button>
                    <button onclick="filterCategory('Core Datacenter & Jaringan')" class="filter-cat-btn px-3 py-1.5 rounded-lg text-xs font-semibold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200" data-cat="Core Datacenter & Jaringan">Core Datacenter (4)</button>
                </div>

                <div class="flex items-center gap-2 min-w-[260px]">
                    <div class="relative flex-1">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" id="hardware-search" onkeyup="searchHardwareTable()" placeholder="Cari hardware, model, spesifikasi..." class="w-full pl-8 pr-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-blue-500 focus:bg-white transition-all">
                    </div>
                    <select id="hardwareLimitSelect" data-limit-target="#hardware-table" data-limit-callback="searchHardwareTable" class="px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="10" selected>Tampil: 10</option>
                        <option value="25">Tampil: 25</option>
                        <option value="50">Tampil: 50</option>
                        <option value="all">Semua</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tabel 26 Hardware -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs" id="hardware-table">
                    <thead>
                        <tr class="bg-slate-50 text-gray-500 uppercase tracking-wider font-semibold border-b border-gray-200">
                            <th class="py-3.5 px-3 text-center w-12">No.</th>
                            <th class="py-3.5 px-2 text-center w-16">Foto</th>
                            <th class="py-3.5 px-3.5">Hardware</th>
                            <th class="py-3.5 px-3.5">Klaster / Kategori</th>
                            <th class="py-3.5 px-3.5">Tipe Integrasi</th>
                            <th class="py-3.5 px-3.5">Contoh / Model</th>
                            <th class="py-3.5 px-3.5">Spesifikasi Utama</th>
                            <th class="py-3.5 px-3.5 text-right">Harga / Unit</th>
                            <th class="py-3.5 px-3.5 text-center">Unit</th>
                            <th class="py-3.5 px-3.5 text-right">Subtotal CAPEX</th>
                            <th class="py-3.5 px-3.5 text-center">Tautan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <?php foreach ($hardware_list as $hw): ?>
                        <tr id="hw-row-<?= $hw['no'] ?>" class="hardware-row hover:bg-slate-50/70 transition-colors" data-category="<?= htmlspecialchars($hw['kategori']) ?>">
                            <td class="py-3 px-3 text-center font-bold text-gray-400" data-row-num><?= $hw['no'] ?></td>
                            <td class="py-2 px-2 text-center">
                                <div class="w-12 h-12 rounded-lg bg-slate-50 border border-gray-200 p-1 flex items-center justify-center overflow-hidden mx-auto shadow-2xs hover:shadow-md transition-shadow group/img relative">
                                    <img src="assets/img/hardware/converted/item_<?= $hw['no'] ?>.png" alt="<?= htmlspecialchars($hw['nama']) ?>" class="max-w-full max-h-full object-contain group-hover/img:scale-125 transition-transform" loading="lazy">
                                </div>
                            </td>
                            <td class="py-3 px-3.5 font-bold text-gray-900"><?= htmlspecialchars($hw['nama']) ?></td>
                            <td class="py-3 px-3.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700">
                                    <?= htmlspecialchars($hw['kategori']) ?>
                                </span>
                            </td>
                            <td class="py-3 px-3.5">
                                <?php if ($hw['tipe'] === 'live'): ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center w-fit">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Live Software
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center w-fit">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mr-1.5"></span> Blueprint Fisik
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-3.5 font-medium text-slate-800"><?= htmlspecialchars($hw['model']) ?></td>
                            <td class="py-3 px-3.5 text-gray-500 max-w-xs text-[11px] leading-relaxed"><?= htmlspecialchars($hw['spesifikasi']) ?></td>
                            <td class="py-3 px-3.5 text-right font-mono font-semibold text-gray-800"><?= $hw['harga_label'] ?></td>
                            <td class="py-3 px-3.5 text-center font-mono text-gray-600"><?= $hw['unit_label'] ?></td>
                            <td class="py-3 px-3.5 text-right font-mono font-bold text-blue-700"><?= $hw['subtotal_label'] ?></td>
                            <td class="py-3 px-3.5 text-center">
                                <div class="flex items-center justify-center space-x-1">
                                    <a href="dashboard.php?page=twin&view=gate&hw_id=<?= $hw['no'] ?>" class="p-1.5 text-amber-600 hover:text-amber-800 rounded hover:bg-amber-50 transition-colors inline-block" title="Pantau Lokasi Sensor Gerbang di Digital Twin 3D">
                                        <i class="fa-solid fa-cube"></i>
                                    </a>
                                    <a href="<?= htmlspecialchars($hw['link']) ?>" target="_blank" rel="noopener noreferrer" class="p-1.5 text-[#0170b9] hover:text-[#004b87] rounded hover:bg-blue-50 transition-colors inline-block" title="Buka tautan spesifikasi resmi">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- TAB 3: TELEMETRI SENSOR IOT LAPANGAN -->
    <!-- ======================================================================= -->
    <div id="tab-telemetri" class="tab-content hidden space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Telemetri Alat Angkat (Reach Stacker) -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center">
                        <i class="fa-solid fa-truck-ramp-box text-blue-600 mr-2"></i>
                        Reach Stacker 01 (Kalmar DRG450)
                    </h3>
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-bold border border-emerald-200">
                        ONLINE - OPERATIONAL
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">DGPS / RTK Position</span>
                        <p class="text-xs font-mono font-bold text-gray-900 mt-0.5">-6.284210, 107.142890</p>
                        <span class="text-[10px] text-emerald-600 font-semibold block mt-1"><i class="fa-solid fa-satellite mr-1"></i> RTK FIXED (&lt; 1.4 cm)</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Current Yard Slot</span>
                        <p class="text-xs font-mono font-bold text-[#0170b9] mt-0.5">Block B - Bay 04 - Row 02</p>
                        <span class="text-[10px] text-gray-500 block mt-1">Tier 3 (Stacking Height)</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Spreader Twistlock</span>
                        <p class="text-xs font-bold text-emerald-700 mt-0.5 flex items-center">
                            <i class="fa-solid fa-lock text-emerald-600 mr-1.5"></i> LOCKED (Loaded)
                        </p>
                        <span class="text-[10px] text-gray-500 block mt-1">Bromma SmartSpreader Kit</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Load Cell Telemetry</span>
                        <p class="text-xs font-mono font-bold text-gray-900 mt-0.5">28.520 kg (28.5 t)</p>
                        <span class="text-[10px] text-emerald-600 font-semibold block mt-1"><i class="fa-solid fa-check mr-1"></i> Beban Normal Aman</span>
                    </div>
                </div>
            </div>

            <!-- Telemetri Cold Chain (Smart Reefer Monitoring) -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center">
                        <i class="fa-solid fa-snowflake text-cyan-600 mr-2"></i>
                        Reefer Yard Monitoring (300 Titik Colokan)
                    </h3>
                    <span class="px-2 py-0.5 bg-blue-50 text-[#0170b9] rounded-full text-[10px] font-bold border border-blue-200">
                        142 / 300 PLUGGED
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Total Konsumsi Daya</span>
                        <p class="text-xs font-mono font-bold text-gray-900 mt-0.5">852 kW</p>
                        <span class="text-[10px] text-gray-500 block mt-1">Rata-rata 6.0 kW / Reefer</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Suhu Rata-rata Cold Chain</span>
                        <p class="text-xs font-mono font-bold text-cyan-700 mt-0.5">-18.4 °C</p>
                        <span class="text-[10px] text-emerald-600 font-semibold block mt-1"><i class="fa-solid fa-check mr-1"></i> Rentang Suhu Beku Stabil</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Status Trip Alarm Listrik</span>
                        <p class="text-xs font-bold text-emerald-700 mt-0.5 flex items-center">
                            <i class="fa-solid fa-shield-check mr-1.5"></i> 0 Peringatan
                        </p>
                        <span class="text-[10px] text-gray-500 block mt-1">Semua socket beroperasi normal</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Kamera Thermal Inframerah</span>
                        <p class="text-xs font-bold text-gray-900 mt-0.5">4 Unit Aktif</p>
                        <span class="text-[10px] text-emerald-600 font-semibold block mt-1"><i class="fa-solid fa-temperature-arrow-down mr-1"></i> Tidak Ada Overheat</span>
                    </div>
                </div>
            </div>

            <!-- Telemetri Intermodal Rail Siding (Axle Counter) -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center">
                        <i class="fa-solid fa-train-subway text-indigo-600 mr-2"></i>
                        Intermodal Rail Siding (Frauscher RSR123)
                    </h3>
                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded-full text-[10px] font-bold border border-indigo-200">
                        TRAIN DETECTED
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Identitas Kereta Api</span>
                        <p class="text-xs font-bold text-gray-900 mt-0.5">KA 2501 (KAI Logistik)</p>
                        <span class="text-[10px] text-gray-500 block mt-1">Rute: Tj. Priok &rarr; CIDP Siding</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Hitungan Gandar (Axle Count)</span>
                        <p class="text-xs font-mono font-bold text-indigo-700 mt-0.5">120 Axles</p>
                        <span class="text-[10px] text-gray-500 block mt-1">30 Gerbong Datar (60 TEUs)</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Kecepatan Pendekatan</span>
                        <p class="text-xs font-mono font-bold text-emerald-700 mt-0.5">12 km/h (Safe)</p>
                        <span class="text-[10px] text-emerald-600 font-semibold block mt-1">Dalam batas aman siding</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Status Jalur Siding</span>
                        <p class="text-xs font-bold text-emerald-700 mt-0.5 flex items-center">
                            <i class="fa-solid fa-circle-check mr-1.5"></i> Jalur 1 Siap Bongkar
                        </p>
                        <span class="text-[10px] text-gray-500 block mt-1">Reach Stacker siap melayani</span>
                    </div>
                </div>
            </div>

            <!-- Telemetri E-Seal Bea Cukai (Jointech JT701) -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center">
                        <i class="fa-solid fa-lock text-amber-600 mr-2"></i>
                        Bea Cukai CEISA 4.0 (Smart E-Seal Tracker)
                    </h3>
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-bold border border-emerald-200">
                        ALL SEALS SECURED
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Kontainer Transit Aktif</span>
                        <p class="text-xs font-mono font-bold text-gray-900 mt-0.5">18 Unit</p>
                        <span class="text-[10px] text-gray-500 block mt-1">Koridor KA Priok - Cikarang</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Integritas Kawat Segel</span>
                        <p class="text-xs font-bold text-emerald-700 mt-0.5 flex items-center">
                            <i class="fa-solid fa-shield-check mr-1.5"></i> 0 Tamper Alert
                        </p>
                        <span class="text-[10px] text-emerald-600 font-semibold block mt-1">Semua segel utuh terkunci</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Kondisi Baterai Rata-rata</span>
                        <p class="text-xs font-mono font-bold text-gray-900 mt-0.5">94%</p>
                        <span class="text-[10px] text-gray-500 block mt-1">Masa pakai hingga 30 hari</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Sinkronisasi CEISA 4.0</span>
                        <p class="text-xs font-bold text-blue-700 mt-0.5 flex items-center">
                            <i class="fa-solid fa-cloud-arrow-up mr-1.5"></i> Terhubung Real-Time
                        </p>
                        <span class="text-[10px] text-gray-500 block mt-1">PIC: Naufal Andika Heditya</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- TAB 5: DCSA EVENT LOG -->
    <!-- ======================================================================= -->
    <div id="tab-dcsa" class="tab-content hidden space-y-6">
        <!-- Header Info DCSA -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
            <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-1 flex items-center">
                <i class="fa-solid fa-satellite-dish text-[#0170b9] mr-2"></i>
                DCSA Track &amp; Trace API v2.2
            </h2>
            <p class="text-xs text-gray-500">Pelaporan standar <span class="font-mono font-semibold text-gray-700">EQUIPMENT</span> &bull; <span class="font-mono font-semibold text-gray-700">TRANSPORT</span> &bull; <span class="font-mono font-semibold text-gray-700">SHIPMENT</span>.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Event Log Table -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col h-[600px] overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <h3 class="text-sm font-bold text-gray-800">Event Log Stream</h3>
                    <div class="flex space-x-2">
                        <button onclick="exportGateExcel()" class="px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-bold transition flex items-center shadow-xs">
                            <i class="fa-solid fa-file-excel mr-1"></i> Excel
                        </button>
                        <button onclick="exportGatePDF()" class="px-2 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded text-xs font-bold transition flex items-center shadow-xs">
                            <i class="fa-solid fa-file-pdf mr-1"></i> PDF
                        </button>
                        <select class="text-xs border border-gray-200 rounded-lg px-2 py-1 bg-white focus:outline-none focus:border-blue-400">
                            <option value="">Semua Event</option>
                            <option value="EQUIPMENT">EQUIPMENT</option>
                            <option value="TRANSPORT">TRANSPORT</option>
                            <option value="SHIPMENT">SHIPMENT</option>
                        </select>
                        <select class="text-xs border border-gray-200 rounded-lg px-2 py-1 bg-white focus:outline-none focus:border-blue-400">
                            <option value="">Semua Status</option>
                            <option value="ACT">ACT (Actual)</option>
                            <option value="PLN">PLN (Planned)</option>
                            <option value="EST">EST (Estimated)</option>
                        </select>
                        <select id="dcsaLimitSelect" data-limit-target="#dcsaEventTable" class="text-xs font-bold border border-gray-200 rounded-lg px-2 py-1 bg-white focus:outline-none focus:border-blue-400 text-gray-700">
                            <option value="10" selected>Tampil: 10</option>
                            <option value="25">Tampil: 25</option>
                            <option value="all">Semua</option>
                        </select>
                    </div>
                </div>
                <div class="flex-1 overflow-auto p-4">
                    <table class="w-full text-left border-collapse" id="dcsaEventTable">
                        <thead>
                            <tr class="text-[10px] text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                <th class="pb-2 pr-2 font-semibold text-center w-8">No</th>
                                <th class="pb-2 font-semibold">Timestamp</th>
                                <th class="pb-2 font-semibold">Event Type / ID</th>
                                <th class="pb-2 font-semibold">Classifier</th>
                                <th class="pb-2 font-semibold">Equipment / Call</th>
                                <th class="pb-2 font-semibold">Location</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs divide-y divide-gray-50">
                            <!-- Sample Data 1: GATE_IN -->
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="py-2.5 pr-2 text-center font-mono text-gray-400" data-row-num>1</td>
                                <td class="py-2.5 font-mono text-gray-500">2026-09-29T08:15:00+07:00</td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold">EQUIPMENT: GATE_IN</span>
                                    <div class="text-[10px] text-gray-400 font-mono mt-1">EVT-CIDP-001</div>
                                </td>
                                <td class="py-2.5"><span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded text-[10px] font-bold">ACT</span></td>
                                <td class="py-2.5 font-mono font-bold text-gray-800">MSKU7829104</td>
                                <td class="py-2.5 text-gray-600">IDCKG (CIDP)</td>
                            </tr>
                            <!-- Sample Data 2: DROP_OFF -->
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="py-2.5 pr-2 text-center font-mono text-gray-400" data-row-num>2</td>
                                <td class="py-2.5 font-mono text-gray-500">2026-09-29T08:25:00+07:00</td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold">EQUIPMENT: DROP_OFF</span>
                                    <div class="text-[10px] text-gray-400 font-mono mt-1">EVT-CIDP-002</div>
                                </td>
                                <td class="py-2.5"><span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded text-[10px] font-bold">ACT</span></td>
                                <td class="py-2.5 font-mono font-bold text-gray-800">MSKU7829104</td>
                                <td class="py-2.5 text-gray-600">IDCKG (Yard A-01)</td>
                            </tr>
                            <!-- Sample Data 3: LOAD -->
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="py-2.5 pr-2 text-center font-mono text-gray-400" data-row-num>3</td>
                                <td class="py-2.5 font-mono text-gray-500">2026-09-29T11:30:00+07:00</td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold">EQUIPMENT: LOAD</span>
                                    <div class="text-[10px] text-gray-400 font-mono mt-1">EVT-CIDP-003</div>
                                </td>
                                <td class="py-2.5"><span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded text-[10px] font-bold">ACT</span></td>
                                <td class="py-2.5 font-mono font-bold text-gray-800">TGHU9021845</td>
                                <td class="py-2.5 text-gray-600">IDCKG (Rail Siding)</td>
                            </tr>
                            <!-- Sample Data 4: DEPA -->
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="py-2.5 pr-2 text-center font-mono text-gray-400" data-row-num>4</td>
                                <td class="py-2.5 font-mono text-gray-500">2026-09-29T12:00:00+07:00</td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded text-[10px] font-bold">TRANSPORT: DEPA</span>
                                    <div class="text-[10px] text-gray-400 font-mono mt-1">EVT-CIDP-004</div>
                                </td>
                                <td class="py-2.5"><span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded text-[10px] font-bold">ACT</span></td>
                                <td class="py-2.5 font-mono font-bold text-gray-800">KA2518 (Train)</td>
                                <td class="py-2.5 text-gray-600">IDCKG (CIDP)</td>
                            </tr>
                            <!-- Sample Data 5: ARRI Planned -->
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="py-2.5 pr-2 text-center font-mono text-gray-400" data-row-num>5</td>
                                <td class="py-2.5 font-mono text-gray-500">2026-09-29T18:00:00+07:00</td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded text-[10px] font-bold">TRANSPORT: ARRI</span>
                                    <div class="text-[10px] text-gray-400 font-mono mt-1">EVT-CIDP-005</div>
                                </td>
                                <td class="py-2.5"><span class="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-100 rounded text-[10px] font-bold">PLN</span></td>
                                <td class="py-2.5 font-mono font-bold text-gray-800">KA2518 (Train)</td>
                                <td class="py-2.5 text-gray-600">IDTPP (Priok)</td>
                            </tr>
                            <!-- Sample Data 6: RECE -->
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="py-2.5 pr-2 text-center font-mono text-gray-400" data-row-num>6</td>
                                <td class="py-2.5 font-mono text-gray-500">2026-09-28T09:00:00+07:00</td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded text-[10px] font-bold">SHIPMENT: RECE</span>
                                    <div class="text-[10px] text-gray-400 font-mono mt-1">EVT-CIDP-006</div>
                                </td>
                                <td class="py-2.5"><span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded text-[10px] font-bold">ACT</span></td>
                                <td class="py-2.5 font-mono font-bold text-gray-800">BK-20260901</td>
                                <td class="py-2.5 text-gray-600">IDCKG</td>
                            </tr>
                            <!-- Sample Data 7: CONF -->
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="py-2.5 pr-2 text-center font-mono text-gray-400" data-row-num>7</td>
                                <td class="py-2.5 font-mono text-gray-500">2026-09-28T10:15:00+07:00</td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded text-[10px] font-bold">SHIPMENT: CONF</span>
                                    <div class="text-[10px] text-gray-400 font-mono mt-1">EVT-CIDP-007</div>
                                </td>
                                <td class="py-2.5"><span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded text-[10px] font-bold">ACT</span></td>
                                <td class="py-2.5 font-mono font-bold text-gray-800">BK-20260901</td>
                                <td class="py-2.5 text-gray-600">IDCKG</td>
                            </tr>
                            <!-- Sample Data 8: STUFF -->
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="py-2.5 pr-2 text-center font-mono text-gray-400" data-row-num>8</td>
                                <td class="py-2.5 font-mono text-gray-500">2026-09-28T14:30:00+07:00</td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold">EQUIPMENT: STUFF</span>
                                    <div class="text-[10px] text-gray-400 font-mono mt-1">EVT-CIDP-008</div>
                                </td>
                                <td class="py-2.5"><span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded text-[10px] font-bold">ACT</span></td>
                                <td class="py-2.5 font-mono font-bold text-gray-800">EITU9823102</td>
                                <td class="py-2.5 text-gray-600">IDCKG (CFS)</td>
                            </tr>
                            <!-- Sample Data 9: GATE_OUT -->
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="py-2.5 pr-2 text-center font-mono text-gray-400" data-row-num>9</td>
                                <td class="py-2.5 font-mono text-gray-500">2026-09-30T09:00:00+07:00</td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold">EQUIPMENT: GATE_OUT</span>
                                    <div class="text-[10px] text-gray-400 font-mono mt-1">EVT-CIDP-009</div>
                                </td>
                                <td class="py-2.5"><span class="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-100 rounded text-[10px] font-bold">PLN</span></td>
                                <td class="py-2.5 font-mono font-bold text-gray-800">EITU9823102</td>
                                <td class="py-2.5 text-gray-600">IDCKG (Gate Out)</td>
                            </tr>
                            <!-- Sample Data 10: DISCHARGE Estimated -->
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="py-2.5 pr-2 text-center font-mono text-gray-400" data-row-num>10</td>
                                <td class="py-2.5 font-mono text-gray-500">2026-10-01T15:00:00+07:00</td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold">EQUIPMENT: DISCHARGE</span>
                                    <div class="text-[10px] text-gray-400 font-mono mt-1">EVT-CIDP-010</div>
                                </td>
                                <td class="py-2.5"><span class="px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-100 rounded text-[10px] font-bold">EST</span></td>
                                <td class="py-2.5 font-mono font-bold text-gray-800">EITU9823102</td>
                                <td class="py-2.5 text-gray-600">IDTPP (Priok)</td>
                            </tr>
                            <!-- Sample Data 11: ISSU -->
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="py-2.5 pr-2 text-center font-mono text-gray-400" data-row-num>11</td>
                                <td class="py-2.5 font-mono text-gray-500">2026-09-30T10:00:00+07:00</td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded text-[10px] font-bold">SHIPMENT: ISSU</span>
                                    <div class="text-[10px] text-gray-400 font-mono mt-1">EVT-CIDP-011</div>
                                </td>
                                <td class="py-2.5"><span class="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-100 rounded text-[10px] font-bold">PLN</span></td>
                                <td class="py-2.5 font-mono font-bold text-gray-800">BL-9912034</td>
                                <td class="py-2.5 text-gray-600">IDCKG</td>
                            </tr>
                            <!-- Sample Data 12: STRIP -->
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="py-2.5 pr-2 text-center font-mono text-gray-400" data-row-num>12</td>
                                <td class="py-2.5 font-mono text-gray-500">2026-09-27T11:00:00+07:00</td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold">EQUIPMENT: STRIP</span>
                                    <div class="text-[10px] text-gray-400 font-mono mt-1">EVT-CIDP-012</div>
                                </td>
                                <td class="py-2.5"><span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded text-[10px] font-bold">ACT</span></td>
                                <td class="py-2.5 font-mono font-bold text-gray-800">MSKU7829104</td>
                                <td class="py-2.5 text-gray-600">IDCKG (CFS)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- API Payload Preview & Controls -->
            <div class="space-y-6">
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
                        <h3 class="text-xs font-bold text-slate-900 flex items-center">
                            <i class="fa-solid fa-code text-[#0170b9] mr-2"></i>
                            DCSA API Payload Preview
                        </h3>
                        <span class="text-[9px] bg-blue-50 text-blue-700 font-bold border border-blue-200 px-2 py-0.5 rounded font-mono">JSON</span>
                    </div>
                    <pre id="dcsaJsonPayloadBox" class="text-[10.5px] font-mono text-emerald-700 bg-slate-50 p-3.5 rounded-xl overflow-x-auto border border-slate-200 shadow-inner">{
  "eventID": "EVT-CIDP-001",
  "eventType": "EQUIPMENT",
  "eventDateTime": "2026-09-29T08:15:00+07:00",
  "eventClassifierCode": "ACT",
  "equipmentEventTypeCode": "GATE_IN",
  "equipmentReference": "MSKU7829104",
  "transportCallID": "TC-CIDP-KA2518",
  "facilityCode": "IDCKG",
  "facilityCodeListProvider": "SMDG"
}</pre>
                    <button id="btnCopyDcsaJson" onclick="copyDcsaJsonPayload()" class="w-full mt-4 py-2 bg-[#0170b9] hover:bg-[#004b87] text-white font-bold text-xs rounded-lg transition shadow-xs flex items-center justify-center cursor-pointer">
                        <i class="fa-regular fa-copy mr-1.5" id="btnCopyDcsaIcon"></i> <span id="btnCopyDcsaText">Salin JSON Payload</span>
                    </button>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                    <h3 class="text-xs font-bold text-gray-800 mb-3 border-b border-gray-100 pb-2">Simulator DCSA Event</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[10px] text-gray-500 font-semibold mb-1">Event Type</label>
                            <select id="dcsaSimEventType" class="w-full text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 bg-gray-50 focus:outline-none focus:border-blue-400">
                                <option value="EQUIPMENT">EQUIPMENT_EVENT</option>
                                <option value="TRANSPORT">TRANSPORT_EVENT</option>
                                <option value="SHIPMENT">SHIPMENT_EVENT</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] text-gray-500 font-semibold mb-1">Equipment Reference</label>
                            <input id="dcsaSimEquipRef" type="text" value="MSKU7829104" class="w-full text-xs font-mono border border-gray-200 rounded-lg px-2.5 py-1.5 bg-gray-50 focus:outline-none focus:border-blue-400">
                        </div>
                        <button onclick="generateDcsaEventSim()" class="w-full py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold text-xs rounded-xl transition-all shadow-sm flex items-center justify-center space-x-1.5 mt-2 cursor-pointer">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>Generate DCSA Event</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- MODAL: UN/EDIFACT CODECO (Container Gate-In/Out Report) -->
<!-- ======================================================================= -->
<div id="edifactModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 animate-fadeIn">
    <div class="bg-white rounded-2xl max-w-4xl w-full border border-gray-200 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="bg-[#0170b9] text-white px-6 py-4 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-blue-500/20 border border-blue-400/30 flex items-center justify-center text-blue-300 text-lg">
                    <i class="fa-solid fa-file-code"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold tracking-tight">UN/EDIFACT CODECO (D.95B Standard SMDG)</h3>
                    <p class="text-xs text-blue-200">Container Gate-In / Gate-Out Electronic Data Interchange Transmission</p>
                </div>
            </div>
            <button type="button" onclick="closeEdifactModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 overflow-y-auto space-y-4">
            <!-- Metadata Strip -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                    <span class="text-[10px] text-gray-500 uppercase font-bold block">Interchange Sender</span>
                    <span class="text-xs font-mono font-bold text-gray-900">CIDP_YMS</span>
                </div>
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                    <span class="text-[10px] text-gray-500 uppercase font-bold block">Interchange Receiver</span>
                    <span id="edi-receiver-tag" class="text-xs font-mono font-bold text-gray-900">MAERSK_LINE</span>
                </div>
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                    <span class="text-[10px] text-gray-500 uppercase font-bold block">Document Type</span>
                    <span class="text-xs font-mono font-bold text-blue-700">CODECO (Gate-In: BGM+34)</span>
                </div>
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                    <span class="text-[10px] text-gray-500 uppercase font-bold block">Format Standard</span>
                    <span class="text-xs font-mono font-bold text-emerald-700">UN/EDIFACT D.95B</span>
                </div>
            </div>

            <!-- Monospace EDIFACT Message Preview -->
            <div class="relative bg-slate-50 rounded-xl p-4 border border-slate-200 shadow-inner text-slate-800">
                <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-800 text-[11px] font-mono text-slate-400">
                    <span>TRANSMISSION BUFFER: CODECO_PAYLOAD.EDI</span>
                    <span class="text-emerald-400">SYNTAX 2 • SEGMENT TERM (')</span>
                </div>
                <pre id="edifact-raw-text" class="font-mono text-xs text-emerald-300 leading-relaxed overflow-x-auto whitespace-pre selection:bg-blue-600 selection:text-white max-h-64"></pre>
            </div>

            <!-- Segment Legend & Technical Explanation -->
            <div class="bg-blue-50/60 border border-blue-100 rounded-xl p-4 text-xs space-y-2">
                <h4 class="font-bold text-gray-900 flex items-center">
                    <i class="fa-solid fa-circle-info text-blue-600 mr-1.5"></i>
                    Struktur Segmen Standar SMDG CODECO D.95B:
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-[11px] text-gray-600 font-mono">
                    <div><b class="text-blue-800 font-bold">UNA:+.? '</b> — Service String Advice (delimiter format)</div>
                    <div><b class="text-blue-800 font-bold">UNB+UNOA:2</b> — Interchange Header (Sender, Receiver, Jam)</div>
                    <div><b class="text-blue-800 font-bold">BGM+34</b> — 34 = Gate In Confirmation Report</div>
                    <div><b class="text-blue-800 font-bold">TDT+20</b> — Inland Truck Details & Nomor Polisi</div>
                    <div><b class="text-blue-800 font-bold">EQD+CN</b> — Equipment Details (No Kontainer & Tipe ISO)</div>
                    <div><b class="text-blue-800 font-bold">MEA+WT</b> — Measurement (Verified Gross Mass / VGM SOLAS)</div>
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3 flex-shrink-0">
            <span class="text-xs text-gray-500">Kompatibel dengan Navis N4, Pelindo TOS, dan Ocean Carrier Shipping EDI Gateway.</span>
            <div class="flex items-center space-x-2">
                <button type="button" onclick="copyEdifactText()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold rounded-xl text-xs transition-colors flex items-center space-x-1.5">
                    <i class="fa-regular fa-copy"></i>
                    <span id="copy-edi-btn-text">Salin Pesan EDI</span>
                </button>
                <button type="button" onclick="downloadEdifactFile()" class="px-4 py-2 bg-[#0170b9] hover:bg-[#0170b9] text-white font-bold rounded-xl text-xs transition-colors flex items-center space-x-1.5 shadow-sm">
                    <i class="fa-solid fa-download"></i>
                    <span>Unduh File (.edi)</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- MODAL: REGISTRASI PRA-GATE (GATE PASS BOOKING) -->
<!-- ======================================================================= -->
<div id="gatePassModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 animate-fadeIn">
    <div class="bg-white rounded-2xl max-w-xl w-full border border-gray-200 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="bg-[#0170b9] text-white px-6 py-4 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-blue-500/20 border border-blue-400/30 flex items-center justify-center text-blue-300 text-lg">
                    <i class="fa-solid fa-id-card"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold tracking-tight">Registrasi Pra-Gate (Gate Pass Booking)</h3>
                    <p class="text-xs text-blue-200">Pendaftaran armada truk &amp; dokumen sebelum melewati gerbang</p>
                </div>
            </div>
            <button type="button" onclick="closeGatePassModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Form Body -->
        <form id="formGatePass" onsubmit="submitGatePass(event)" class="p-6 overflow-y-auto space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Tugas / Misi Truk di Dry Port</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center p-3 border border-blue-200 bg-blue-50/50 rounded-xl cursor-pointer hover:bg-blue-50 transition">
                        <input type="radio" name="gp_job_type" value="drop_off" checked onchange="toggleGatePassFields()" class="text-[#0170b9] focus:ring-blue-500 mr-2.5">
                        <div>
                            <span class="block text-xs font-bold text-gray-900">Drop-Off (Antar Barang)</span>
                            <span class="block text-[11px] text-gray-500">Bawa kontainer masuk terminal</span>
                        </div>
                    </label>
                    <label class="flex items-center p-3 border border-amber-200 bg-amber-50/50 rounded-xl cursor-pointer hover:bg-amber-50 transition">
                        <input type="radio" name="gp_job_type" value="pick_up" onchange="toggleGatePassFields()" class="text-amber-600 focus:ring-amber-500 mr-2.5">
                        <div>
                            <span class="block text-xs font-bold text-gray-900">Pick-Up (Ambil Barang)</span>
                            <span class="block text-[11px] text-gray-500">Truk kosong ambil kontainer</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nomor Polisi (Plat Truk) *</label>
                    <input type="text" id="gp_license_plate" required placeholder="misal: B 9481 UIX" class="w-full text-xs font-mono font-bold uppercase border border-gray-200 rounded-xl p-2.5 focus:outline-none focus:ring-2 focus:ring-[#0170b9]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Pengemudi *</label>
                    <input type="text" id="gp_driver_name" required placeholder="misal: Budi Santoso" class="w-full text-xs border border-gray-200 rounded-xl p-2.5 focus:outline-none focus:ring-2 focus:ring-[#0170b9]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Perusahaan Ekspedisi / Transporter *</label>
                    <input type="text" id="gp_company" required placeholder="misal: PT Samudera Raya Logistik" class="w-full text-xs border border-gray-200 rounded-xl p-2.5 focus:outline-none focus:ring-2 focus:ring-[#0170b9]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Alokasi Lajur Gate-In (6 Lajur)</label>
                    <select id="gp_gate_lane" class="w-full text-xs font-mono font-bold border border-gray-200 rounded-xl p-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-[#0170b9]">
                        <option value="">Otomatis (Load-Balanced 6-In)</option>
                        <?php foreach ($inbound_lanes_list as $il): ?>
                            <option value="<?= htmlspecialchars($il['code']) ?>"><?= htmlspecialchars($il['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">RFID / e-Tag Truk (Opsional)</label>
                <input type="text" id="gp_rfid_tag" placeholder="misal: RFID-99214" class="w-full text-xs font-mono border border-gray-200 rounded-xl p-2.5 focus:outline-none focus:ring-2 focus:ring-[#0170b9]">
            </div>

            <div id="gp_dropoff_section" class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-3">
                <span class="text-[11px] font-bold text-blue-700 uppercase tracking-wider block"><i class="fa-solid fa-box mr-1"></i> Data Muatan Bawaan (Drop-Off)</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Nomor Kontainer ISO 6346</label>
                        <input type="text" id="gp_container_number" placeholder="misal: MSKU9182374" class="w-full text-xs font-mono uppercase border border-gray-200 rounded-lg p-2 focus:outline-none focus:border-blue-500 bg-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Estimasi Gross SOLAS VGM (kg)</label>
                        <input type="number" id="gp_weight" placeholder="misal: 28450" class="w-full text-xs font-mono border border-gray-200 rounded-lg p-2 focus:outline-none focus:border-blue-500 bg-white">
                    </div>
                </div>
            </div>

            <div id="gp_pickup_section" class="hidden bg-amber-50/60 p-3.5 rounded-xl border border-amber-200 space-y-3">
                <span class="text-[11px] font-bold text-amber-800 uppercase tracking-wider block"><i class="fa-solid fa-file-invoice mr-1"></i> Dokumen Pengambilan (Pick-Up)</span>
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 mb-1">Nomor DO (Delivery Order) / SPPB Bea Cukai</label>
                    <input type="text" id="gp_do_number" placeholder="misal: DO-2026/CIDP/0889" class="w-full text-xs font-mono uppercase border border-amber-200 rounded-lg p-2 focus:outline-none focus:border-amber-500 bg-white">
                </div>
            </div>


            <div class="pt-2 flex items-center justify-end space-x-2 border-t border-gray-100">
                <button type="button" onclick="closeGatePassModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs transition">Batal</button>
                <button type="submit" id="btnSubmitGP" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs shadow-xs transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-check"></i>
                    <span>Simpan &amp; Terbitkan Gate Pass</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript Interaktif Modul Gate -->
<script>
// Cetak Rekapitulasi Arus Gerbang Format Resmi A4 Berstempel Digital
function printGateRekap() {
    if (typeof CIDPPrint === 'undefined') {
        window.print();
        return;
    }
    const headers = ['No. Polisi', 'Pengemudi', 'Peti Kemas', 'Tipe Gerakan', 'Timbangan VGM', 'Status'];
    const rows = [
        ['B 9201 UEK', 'Agus Suprianto (SIM B2)', 'TCLU8192031 (40HC)', 'GATE IN', '38.450 kg (VGM OK)', 'IN YARD'],
        ['B 9481 FTR', 'Bambang Irawan (SIM B2)', 'MSKU7829104 (40HC)', 'GATE OUT', '32.100 kg (VGM OK)', 'RELEASED'],
        ['B 9012 KHA', 'Dedi Kurniawan (SIM B2)', 'SEGU4421908 (20GP)', 'GATE IN', '21.500 kg (VGM OK)', 'WEIGHING'],
        ['B 9554 WRT', 'Hendra Wijaya (SIM B2)', 'CMAU9021844 (40HC)', 'GATE IN', '34.800 kg (VGM OK)', 'IN YARD'],
        ['B 9112 PZX', 'Surya Saputra (SIM B2)', 'TEMU2910482 (20GP)', 'GATE OUT', '23.400 kg (VGM OK)', 'RELEASED']
    ];
    CIDPPrint.printTableReport(
        'LAPORAN REKAPITULASI ARUS GERBANG & TIMBANGAN SOLAS VGM',
        headers,
        rows,
        {
            category: 'GATE-TMS',
            stats: [
                { label: 'Total Inbound', value: '14 Unit' },
                { label: 'Total Outbound', value: '9 Unit' },
                { label: 'Rerata Waktu Timbang', value: '42 Detik' },
                { label: 'Status Kalibrasi', value: '100% METROLOGI' }
            ]
        }
    );
}

// Tab Switching Logic (Executive Pill Buttons Aligned with Sidebar)
function switchGateTab(tabId) {
    const activeClasses = ['text-white', 'bg-gradient-to-r', 'from-orange-500', 'to-orange-600', 'shadow-xs', 'shadow-orange-500/20', 'font-bold'];
    const inactiveClasses = ['text-slate-600', 'font-medium', 'hover:text-orange-600', 'hover:bg-orange-50/70'];

    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove(...activeClasses);
        btn.classList.add(...inactiveClasses);
    });

    const activeTab = document.getElementById(tabId);
    if (activeTab) activeTab.classList.remove('hidden');

    const activeBtn = document.getElementById('btn-' + tabId);
    if (activeBtn) {
        activeBtn.classList.add(...activeClasses);
        activeBtn.classList.remove(...inactiveClasses);
    }
}

// Modal Registrasi Pra-Gate
function showGatePassModal() {
    const modal = document.getElementById('gatePassModal');
    if (modal) modal.classList.remove('hidden');
}

function closeGatePassModal() {
    const modal = document.getElementById('gatePassModal');
    if (modal) modal.classList.add('hidden');
}

function toggleGatePassFields() {
    const isPickup = document.querySelector('input[name="gp_job_type"]:checked')?.value === 'pick_up';
    const dropoffSec = document.getElementById('gp_dropoff_section');
    const pickupSec = document.getElementById('gp_pickup_section');
    if (dropoffSec && pickupSec) {
        if (isPickup) {
            dropoffSec.classList.add('hidden');
            pickupSec.classList.remove('hidden');
        } else {
            dropoffSec.classList.remove('hidden');
            pickupSec.classList.add('hidden');
        }
    }
}

async function submitGatePass(e) {
    e.preventDefault();
    const form = e.target;
    const jobType = document.querySelector('input[name="gp_job_type"]:checked')?.value || 'drop_off';
    const plate = document.getElementById('gp_license_plate').value.trim();
    const driver = document.getElementById('gp_driver_name').value.trim();
    const company = document.getElementById('gp_company').value.trim();
    const rfid = document.getElementById('gp_rfid_tag').value.trim();
    const container = document.getElementById('gp_container_number').value.trim();
    const doNumber = document.getElementById('gp_do_number').value.trim();

    if (window.cidpConfirm) {
        const ok = await window.cidpConfirm({
            title: 'Konfirmasi Terbitkan Gate Pass',
            target: plate,
            message: `Terbitkan Gate Pass untuk armada ${plate} (${company}) dan masukkan ke antrean Pra-Gate?`,
            confirmText: 'Ya, Terbitkan',
            cancelText: 'Tidak, Batal',
            tone: 'primary'
        });
        if (!ok) return;
    }

    const btn = document.getElementById('btnSubmitGP');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Menyimpan...';
    }

    const payload = {
        license_plate: plate,
        driver_name: driver,
        company: company,
        job_type: jobType,
        gate_lane: document.getElementById('gp_gate_lane')?.value || '',
        rfid_tag: rfid,
        container_number: (jobType === 'drop_off') ? container : '',
        do_number: (jobType === 'pick_up') ? doNumber : ''
    };

    try {
        const r = await fetch('api/crud.php?action=add_truck', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await r.json();
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Simpan & Terbitkan Gate Pass';
        }
        if (data.success || data.status) {
            closeGatePassModal();
            if (form) form.reset();
            if (window.cidpSoftRefreshContent) {
                await window.cidpSoftRefreshContent({
                    title: 'Gate Pass Terbit',
                    message: `Armada ${plate} terdaftar di antrean Pra-Gate (${data.data?.gate_lane || 'Gate-In'}).`,
                    type: 'success'
                });
            }
        } else {
            if (window.cidpToast) cidpToast('Gagal Mendaftarkan Armada', data.message || 'Terjadi kesalahan sistem', 'error');
        }
    } catch (err) {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Simpan & Terbitkan Gate Pass';
        }
        if (window.cidpToast) cidpToast('Gagal Menghubungi Server', err.message, 'error');
    }
}

function viewGatePassDetail(plate, contOrDo, driver) {
    const p = encodeURIComponent(plate || '');
    const c = encodeURIComponent(contOrDo || '');
    window.open(`document.php?type=gate_pass&plate=${p}&container=${c}&autoprint=1`, '_blank');
}

function copyDcsaJsonPayload() {
    const pre = document.getElementById('dcsaJsonPayloadBox');
    const btnText = document.getElementById('btnCopyDcsaText');
    const btnIcon = document.getElementById('btnCopyDcsaIcon');
    if (!pre) return;

    navigator.clipboard.writeText(pre.innerText).then(() => {
        if (btnText) btnText.textContent = 'Berhasil Disalin!';
        if (btnIcon) btnIcon.className = 'fa-solid fa-check mr-1.5 text-emerald-300';
        setTimeout(() => {
            if (btnText) btnText.textContent = 'Salin JSON Payload';
            if (btnIcon) btnIcon.className = 'fa-regular fa-copy mr-1.5';
        }, 2000);
    }).catch(() => {
        alert('Gagal menyalin. Silakan seleksi teks secara manual.');
    });
}

function generateDcsaEventSim() {
    const eventType = document.getElementById('dcsaSimEventType')?.value || 'EQUIPMENT';
    const equipRef = (document.getElementById('dcsaSimEquipRef')?.value || 'MSKU7829104').trim().toUpperCase();
    const pre = document.getElementById('dcsaJsonPayloadBox');
    if (!pre) return;

    const nowIso = new Date().toISOString();
    const randomEventId = 'EVT-CIDP-' + Math.floor(100 + Math.random() * 900);

    let eventTypeCode = 'GATE_IN';
    if (eventType === 'TRANSPORT') eventTypeCode = 'DEPARTURE';
    if (eventType === 'SHIPMENT') eventTypeCode = 'LOADED';

    const payload = {
        eventID: randomEventId,
        eventType: eventType + "_EVENT",
        eventDateTime: nowIso,
        eventClassifierCode: "ACT",
        equipmentEventTypeCode: eventTypeCode,
        equipmentReference: equipRef,
        transportCallID: "TC-CIDP-KA" + Math.floor(2000 + Math.random() * 900),
        facilityCode: "IDCKG",
        facilityCodeListProvider: "SMDG",
        terminalOperator: "Conclusion Intermodal Dry Port",
        vgmStatus: "VERIFIED_WEIGHED"
    };

    pre.textContent = JSON.stringify(payload, null, 2);
    
    // Feedback visual kilas
    pre.classList.add('ring-2', 'ring-orange-500');
    setTimeout(() => pre.classList.remove('ring-2', 'ring-orange-500'), 1000);
}

function checkDualClearanceLock(cRef, plate, cStat, bStat) {
    const isCustomsCleared = (cStat === 'SPPB_CLEARED');
    const isBillingPaid = (bStat === 'PAID');
    
    let msg = "=== DUAL-CLEARANCE CHECK (GERBANG KELUAR) ===\n\n";
    msg += "Nomor Peti Kemas : " + cRef + "\n";
    msg += "Plat Armada Truk  : " + plate + "\n\n";
    msg += "1. Bea Cukai (CEISA) : " + cStat + " " + (isCustomsCleared ? "[✓ LOLOS]" : "[✗ TERTAHAN - Butuh SPPB]") + "\n";
    msg += "2. Billing ERP (Odoo): " + bStat + " " + (isBillingPaid ? "[✓ LUNAS]" : "[✗ BELUM LUNAS - Butuh Pembayaran]") + "\n\n";
    
    if (!isCustomsCleared && !isBillingPaid) {
        msg += "Palang gerbang keluar TERKUNCI OTOMATIS karena kedua syarat belum terpenuhi.";
    } else if (!isCustomsCleared) {
        msg += "Palang keluar TERKUNCI: Selesaikan persetujuan SPPB di modul Bea Cukai.";
    } else if (!isBillingPaid) {
        msg += "Palang keluar TERKUNCI: Selesaikan pelunasan invoice di modul Billing & ERP.";
    }
    
    alert(msg);
}

// Filter Kategori Hardware BOM
function filterCategory(category) {
    const buttons = document.querySelectorAll('.filter-cat-btn');
    buttons.forEach(btn => {
        if (btn.getAttribute('data-cat') === category) {
            btn.classList.add('bg-[#0170b9]', 'text-white');
            btn.classList.remove('bg-gray-100', 'text-gray-600');
        } else {
            btn.classList.remove('bg-[#0170b9]', 'text-white');
            btn.classList.add('bg-gray-100', 'text-gray-600');
        }
    });

    const rows = document.querySelectorAll('.hardware-row');
    window._activeHwCategory = category;
    searchHardwareTable();
}

// Quick Search Hardware Table
function searchHardwareTable() {
    const input = (document.getElementById('hardware-search')?.value || '').toLowerCase();
    const cat = window._activeHwCategory || 'all';
    const rows = document.querySelectorAll('.hardware-row');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const rowCat = row.getAttribute('data-category');
        const matchCat = (cat === 'all' || rowCat === cat);
        const matchQ = (!input || text.includes(input));
        row.dataset.filteredOut = (matchCat && matchQ) ? '0' : '1';
    });

    if (window.CIDPTableTools) {
        window.CIDPTableTools.apply('#hardware-table');
    }
}

// ===========================================================================
// OCR ENGINE & ISO 6346 CHECK DIGIT JAVASCRIPT
// ===========================================================================
const ISO_LETTER_VALUES = {
    'A': 10, 'B': 12, 'C': 13, 'D': 14, 'E': 15, 'F': 16, 'G': 17, 'H': 18, 'I': 19, 'J': 20,
    'K': 21, 'L': 23, 'M': 24, 'N': 25, 'O': 26, 'P': 27, 'Q': 28, 'R': 29, 'S': 30, 'T': 31,
    'U': 32, 'V': 34, 'W': 35, 'X': 36, 'Y': 37, 'Z': 38
};

const ocrPresetsData = {
    'MSKU': {
        code: 'MSKU9821450',
        displayMain: 'MSKU 982145',
        checkDigit: '0',
        sizeType: '45G1',
        owner: 'MSK',
        ownerName: 'Maersk Line A/S',
        category: 'U',
        serial: '982145',
        maxGross: '32.500 KG / 71.650 LBS',
        tare: '3.900 KG / 8.598 LBS',
        payload: '28.600 KG / 63.052 LBS',
        cube: '76.4 CU.M / 2.700 CU.FT',
        seal: 'SEAL: ML-ID992810',
        plate: 'B 9812 UIK',
        weight: '32.450 kg',
        receiver: 'MAERSK_LINE',
        yardLoc: 'Block B - Bay 04 - Row 02'
    },
    'ONEU': {
        code: 'ONEU6612988',
        displayMain: 'ONEU 661298',
        checkDigit: '8',
        sizeType: '42G1',
        owner: 'ONE',
        ownerName: 'Ocean Network Express',
        category: 'U',
        serial: '661298',
        maxGross: '30.480 KG / 67.200 LBS',
        tare: '3.820 KG / 8.420 LBS',
        payload: '26.660 KG / 58.780 LBS',
        cube: '67.7 CU.M / 2.390 CU.FT',
        seal: 'SEAL: ONE-JKT88120',
        plate: 'B 9144 PXT',
        weight: '24.120 kg',
        receiver: 'OCEAN_NETWORK_EXPRESS',
        yardLoc: 'Reefer Yard - Block R02 - Plug 14'
    },
    'EMCU': {
        code: 'EMCU5518200',
        displayMain: 'EMCU 551820',
        checkDigit: '0',
        sizeType: '22G1',
        owner: 'EMC',
        ownerName: 'Evergreen Marine Corp',
        category: 'U',
        serial: '551820',
        maxGross: '24.000 KG / 52.910 LBS',
        tare: '2.280 KG / 5.026 LBS',
        payload: '21.720 KG / 47.884 LBS',
        cube: '33.2 CU.M / 1.172 CU.FT',
        seal: 'SEAL: EMC-TW77192',
        plate: 'B 9033 BAA',
        weight: '21.800 kg',
        receiver: 'EVERGREEN_LINE',
        yardLoc: 'Block A - Bay 02 - Row 01'
    },
    'INVALID': {
        code: 'MSKU9821453',
        displayMain: 'MSKU 982145',
        checkDigit: '3',
        sizeType: '45G1',
        owner: 'MSK',
        ownerName: 'Maersk Line A/S',
        category: 'U',
        serial: '982145',
        maxGross: '32.500 KG / 71.650 LBS',
        tare: '3.900 KG / 8.598 LBS',
        payload: '28.600 KG / 63.052 LBS',
        cube: '76.4 CU.M / 2.700 CU.FT',
        seal: 'SEAL: ML-ID992810',
        plate: 'B 9812 UIK',
        weight: '32.450 kg',
        receiver: 'MAERSK_LINE',
        yardLoc: 'Area Behandle / Bea Cukai'
    }
};

let activePresetKey = 'MSKU';

function getIsoCharValue(ch) {
    ch = ch.toUpperCase();
    if (ch >= '0' && ch <= '9') return parseInt(ch, 10);
    return ISO_LETTER_VALUES[ch] !== undefined ? ISO_LETTER_VALUES[ch] : 0;
}

function handleContainerInput(val) {
    const clean = val.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
    document.getElementById('iso-input-container').value = clean;
    document.getElementById('iso-char-count').textContent = clean.length + '/11';
    recalculateISO(clean);
}

function recalculateISO(code) {
    const tbody = document.getElementById('iso-calc-tbody');
    tbody.innerHTML = '';

    const ownerCodeEl = document.getElementById('iso-owner-code');
    const ownerNameEl = document.getElementById('iso-owner-name');
    const catCodeEl = document.getElementById('iso-cat-code');
    const serialCodeEl = document.getElementById('iso-serial-code');
    const checkDigitEl = document.getElementById('iso-check-digit');
    const checkSubEl = document.getElementById('iso-check-sub');
    const checkBoxEl = document.getElementById('iso-check-box');
    const badgeStatus = document.getElementById('iso-badge-status');
    const badgeText = document.getElementById('iso-badge-text');
    const sumTotalEl = document.getElementById('iso-sum-total');
    const modFormulaEl = document.getElementById('iso-mod-formula');
    const verdictCard = document.getElementById('iso-verdict-card');
    const verdictText = document.getElementById('iso-verdict-text');

    if (!code || code.length < 4) {
        ownerCodeEl.textContent = '---';
        ownerNameEl.textContent = 'Menunggu Input';
        catCodeEl.textContent = '-';
        serialCodeEl.textContent = '------';
        checkDigitEl.textContent = '-';
        checkSubEl.textContent = 'Belum Lengkap';
        sumTotalEl.textContent = '0';
        modFormulaEl.textContent = '-';
        return;
    }

    const owner = code.substring(0, 3);
    ownerCodeEl.textContent = owner;
    
    // Lookup owner name
    const ownerDirectory = {
        'MSK': 'Maersk Line A/S',
        'ONE': 'Ocean Network Express',
        'EMC': 'Evergreen Marine',
        'TCK': 'Trans Capital Korea',
        'CMA': 'CMA CGM Group',
        'HLC': 'Hapag-Lloyd AG',
        'COS': 'COSCO Shipping',
        'MSC': 'Mediterranean Shipping Co'
    };
    ownerNameEl.textContent = ownerDirectory[owner] || 'International BIC Owner';

    const cat = code.length >= 4 ? code[3] : '-';
    catCodeEl.textContent = cat;

    const serial = code.length >= 10 ? code.substring(4, 10) : (code.length > 4 ? code.substring(4) : '------');
    serialCodeEl.textContent = serial;

    // Check calculation
    let totalSum = 0;
    const calcLen = Math.min(code.length, 10);

    for (let i = 0; i < calcLen; i++) {
        const char = code[i];
        const val = getIsoCharValue(char);
        const weight = Math.pow(2, i);
        const product = val * weight;
        totalSum += product;

        const tr = document.createElement('tr');
        tr.className = "hover:bg-slate-50 transition-colors";
        tr.innerHTML = `
            <td class="py-1.5 px-3 text-center text-gray-400 font-bold">${i}</td>
            <td class="py-1.5 px-3 text-center font-bold text-gray-900 bg-slate-50/50">${char}</td>
            <td class="py-1.5 px-3 text-center text-blue-600">${val}</td>
            <td class="py-1.5 px-3 text-center text-gray-500">2<sup>${i}</sup> = ${weight}</td>
            <td class="py-1.5 px-3 text-right font-bold text-gray-800">${product.toLocaleString()}</td>
        `;
        tbody.appendChild(tr);
    }

    sumTotalEl.textContent = totalSum.toLocaleString();

    if (code.length >= 10) {
        const rem = totalSum % 11;
        const expectedCheck = (rem === 10) ? 0 : rem;
        modFormulaEl.textContent = `${totalSum.toLocaleString()} mod 11 = ${rem} (Harusnya: ${expectedCheck})`;

        if (code.length >= 11) {
            const actualCheck = parseInt(code[10], 10);
            checkDigitEl.textContent = actualCheck;

            if (actualCheck === expectedCheck) {
                // Match
                badgeStatus.className = "px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full font-bold text-xs self-start sm:self-auto flex items-center space-x-1";
                badgeStatus.innerHTML = '<i class="fa-solid fa-circle-check"></i><span id="iso-badge-text">VALID ISO 6346</span>';
                checkBoxEl.className = "bg-emerald-50 border border-emerald-200 rounded-xl p-2.5";
                checkDigitEl.className = "font-mono font-extrabold text-sm text-emerald-700";
                checkSubEl.className = "text-[10px] text-emerald-600 font-semibold block";
                checkSubEl.textContent = `Cocok (Rem: ${expectedCheck})`;

                verdictCard.className = "bg-emerald-500 text-white px-4 py-2.5 rounded-xl text-center shadow-xs flex-shrink-0 min-w-[140px]";
                verdictText.textContent = "VALID PASS";
            } else {
                // Mismatch
                badgeStatus.className = "px-3 py-1 bg-rose-100 text-rose-800 rounded-full font-bold text-xs self-start sm:self-auto flex items-center space-x-1";
                badgeStatus.innerHTML = '<i class="fa-solid fa-circle-xmark"></i><span id="iso-badge-text">CHECKSUM GAGAL</span>';
                checkBoxEl.className = "bg-rose-50 border border-rose-200 rounded-xl p-2.5";
                checkDigitEl.className = "font-mono font-extrabold text-sm text-rose-700";
                checkSubEl.className = "text-[10px] text-rose-600 font-semibold block";
                checkSubEl.textContent = `Salah! Sisa bagi: ${expectedCheck}`;

                verdictCard.className = "bg-rose-600 text-white px-4 py-2.5 rounded-xl text-center shadow-xs flex-shrink-0 min-w-[140px]";
                verdictText.textContent = "MISMATCH (GAGAL)";
            }
        } else {
            checkDigitEl.textContent = expectedCheck + ' (Estimasi)';
            checkSubEl.textContent = 'Digit ke-11 Belum Diisi';
        }
    } else {
        modFormulaEl.textContent = 'Minimal 10 karakter untuk hitung modulo 11';
    }
}

function selectOcrPreset(key) {
    activePresetKey = key;
    const preset = ocrPresetsData[key];
    if (!preset) return;

    // Update preset buttons styling
    document.querySelectorAll('.ocr-preset-btn').forEach(btn => {
        btn.classList.remove('border-2', 'border-blue-500', 'bg-blue-50/50');
        btn.classList.add('border', 'border-gray-200', 'bg-white');
    });
    const currentBtn = document.getElementById('preset-btn-' + key);
    if (currentBtn) {
        currentBtn.classList.remove('border', 'border-gray-200', 'bg-white');
        currentBtn.classList.add('border-2', 'border-blue-500', 'bg-blue-50/50');
    }

    // Update Door Graphics
    document.getElementById('door-container-no').innerHTML = `${preset.displayMain}<span class="ml-1 px-1 border border-white/40 bg-white/10 rounded">${preset.checkDigit}</span>`;
    document.getElementById('door-sizetype').textContent = preset.sizeType;
    document.getElementById('door-max-gross').textContent = preset.maxGross;
    document.getElementById('door-tare').textContent = preset.tare;
    document.getElementById('door-payload').textContent = preset.payload;
    document.getElementById('door-cube').textContent = preset.cube;
    document.getElementById('door-seal-tag').textContent = preset.seal;
    document.getElementById('door-bottom-code').textContent = preset.code;

    // Trigger OCR Input
    document.getElementById('iso-input-container').value = preset.code;
    document.getElementById('iso-char-count').textContent = preset.code.length + '/11';
    recalculateISO(preset.code);

    triggerOcrDoorScan();
}

function triggerOcrDoorScan() {
    const scanline = document.getElementById('ocr-scanline');
    const confTag = document.getElementById('ocr-overall-conf');
    const latencyTag = document.getElementById('ocr-latency-val');
    const bbox = document.getElementById('bbox-container');

    // Animasi scanline
    scanline.style.opacity = '1';
    scanline.style.top = '0%';
    scanline.style.transition = 'top 0.6s ease-in-out, opacity 0.3s ease';

    setTimeout(() => {
        scanline.style.top = '100%';
    }, 50);

    setTimeout(() => {
        scanline.style.opacity = '0';
        scanline.style.top = '0%';
    }, 700);

    // Randomize telemetry
    const latency = Math.floor(Math.random() * 40) + 165;
    const conf = (99.1 + Math.random() * 0.7).toFixed(1);
    if (latencyTag) latencyTag.textContent = latency + ' ms';
    if (confTag) confTag.textContent = conf + '%';

    // Bbox highlight
    if (bbox) {
        bbox.classList.add('ring-4', 'ring-emerald-400');
        setTimeout(() => bbox.classList.remove('ring-4', 'ring-emerald-400'), 500);
    }
}

function transferOcrToGateLane() {
    const preset = ocrPresetsData[activePresetKey] || ocrPresetsData['MSKU'];
    const currentCode = document.getElementById('iso-input-container').value;

    showGatePassModal();
    const contInput = document.getElementById('gp_container_number');
    if (contInput) contInput.value = currentCode;
    const plateInput = document.getElementById('gp_license_plate');
    if (plateInput && preset.plate) plateInput.value = preset.plate;
    const weightInput = document.getElementById('gp_weight');
    if (weightInput && preset.weight) weightInput.value = parseInt(preset.weight.replace(/[^0-9]/g, '')) || 24000;
}

// ===========================================================================
// UN/EDIFACT CODECO ENGINE JAVASCRIPT
// ===========================================================================
let currentEdifactPayload = "";

function generateEdifactCodecoString(preset) {
    const now = new Date();
    const ymd = now.getFullYear().toString() + 
                String(now.getMonth() + 1).padStart(2, '0') + 
                String(now.getDate()).padStart(2, '0');
    const hm = String(now.getHours()).padStart(2, '0') + String(now.getMinutes()).padStart(2, '0');
    const refNum = 'CIDP' + ymd + hm;
    const cleanPlate = (preset.plate || 'B9812UIK').replace(/\s+/g, '');
    const cleanWeight = (preset.weight || '32.450 kg').replace(/[^0-9]/g, '');

    return `UNA:+.? '
UNB+UNOA:2+CIDP_DRY_PORT+${preset.receiver || 'MAERSK_LINE'}+${ymd}:${hm}+${refNum}'
BGM+34+GP-${ymd}-0042+9'
TDT+20++1++TRUCK:CIDP_GATE+${cleanPlate}'
RFF+SI:CIDP-SI-${ymd}'
NAD+CF+${preset.owner || 'MSK'}:172:20'
EQD+CN+${preset.code}+${preset.sizeType}:102:5++2+5'
MEA+WT++KGM:${cleanWeight}'
SEL+${preset.seal.replace('SEAL: ', '')}+CA'
UNT+9+${refNum}'
UNZ+1+${refNum}'`;
}

function openEdifactModal(customPreset) {
    const preset = customPreset || ocrPresetsData[activePresetKey] || ocrPresetsData['MSKU'];
    currentEdifactPayload = generateEdifactCodecoString(preset);

    const rawPre = document.getElementById('edifact-raw-text');
    const recvTag = document.getElementById('edi-receiver-tag');
    if (rawPre) rawPre.textContent = currentEdifactPayload;
    if (recvTag) recvTag.textContent = preset.receiver || 'MAERSK_LINE';

    const modal = document.getElementById('edifactModal');
    if (modal) modal.classList.remove('hidden');
}

function openEdifactModalCurrent() {
    openEdifactModal(ocrPresetsData[activePresetKey]);
}

function closeEdifactModal() {
    const modal = document.getElementById('edifactModal');
    if (modal) modal.classList.add('hidden');
}

function copyEdifactText() {
    if (!currentEdifactPayload) return;
    navigator.clipboard.writeText(currentEdifactPayload).then(() => {
        const btnText = document.getElementById('copy-edi-btn-text');
        if (btnText) {
            btnText.textContent = "Tersalin ke Clipboard!";
            setTimeout(() => {
                btnText.textContent = "Salin Pesan EDI";
            }, 2000);
        }
    }).catch(err => {
        alert("Pesan EDIFACT:\n\n" + currentEdifactPayload);
    });
}

function downloadEdifactFile() {
    if (!currentEdifactPayload) return;
    const preset = ocrPresetsData[activePresetKey] || ocrPresetsData['MSKU'];
    const filename = `CODECO_${preset.code}.edi`;
    const blob = new Blob([currentEdifactPayload], { type: 'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

// Universal Inter-Module Deep-Linking Initializer (Gate Modul)
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    const hwId = urlParams.get('hw_id');
    const actionParam = urlParams.get('action');
    const searchParam = urlParams.get('search') || urlParams.get('ctr');

    // 1. Tab switching
    if (tabParam) {
        let targetTab = tabParam;
        if (!targetTab.startsWith('tab-')) {
            targetTab = 'tab-' + targetTab;
        }
        if (['tab-simulasi', 'tab-ocr-iso', 'tab-katalog', 'tab-telemetri', 'tab-dcsa'].includes(targetTab)) {
            switchGateTab(targetTab);
        }
    }

    // 2. Hardware ID highlight in catalog
    if (hwId) {
        switchGateTab('tab-katalog');
        filterCategory('all');
        const row = document.getElementById('hw-row-' + hwId);
        if (row) {
            setTimeout(() => {
                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                row.classList.add('bg-blue-100', 'font-bold');
                setTimeout(() => row.classList.remove('bg-blue-100', 'font-bold'), 4000);
            }, 300);
        }
    }

    // 3. Action modal opener
    if (actionParam === 'gate_pass') {
        showGatePassModal();
    }

    // 4. Container ISO check calculation
    if (searchParam) {
        switchGateTab('tab-ocr-iso');
        handleContainerInput(searchParam);
    } else {
        recalculateISO('MSKU9821450');
    }
});

function getGateExportData() {
    const headers = ['Timestamp', 'Event Type / ID', 'Classifier', 'Equipment / Call', 'Location'];
    const rows = [];
    const tableRows = document.querySelectorAll('#tab-dcsa table tbody tr');
    
    tableRows.forEach(row => {
        const cols = row.querySelectorAll('td');
        if (cols.length === 6 && row.style.display !== 'none') {
            rows.push([
                cols[1].innerText.trim(),
                cols[2].innerText.replace(/\n/g, ' - ').trim(),
                cols[3].innerText.trim(),
                cols[4].innerText.trim(),
                cols[5].innerText.trim()
            ]);
        }
    });
    return { headers, rows };
}

function exportGateExcel() {
    const { headers, rows } = getGateExportData();
    CIDPExport.toExcel(headers, rows, 'DCSA Event Log', 'Laporan_Gate_DCSA');
}

function filterGateLaneCards(dir) {
    document.querySelectorAll('.gate-lane-filter-btn').forEach(btn => {
        const active = btn.dataset.laneFilter === dir;
        btn.className = active
            ? 'gate-lane-filter-btn px-2.5 py-1 rounded-lg bg-[#0170b9] text-white font-bold cursor-pointer'
            : 'gate-lane-filter-btn px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-sky-50 hover:text-sky-700 font-bold cursor-pointer';
    });
    document.querySelectorAll('[data-gate-lane-card]').forEach(card => {
        const match = (dir === 'all' || card.dataset.laneDir === dir);
        card.classList.toggle('hidden', !match);
    });
}

async function refreshGateLanesLive() {
    try {
        const res = await fetch('api/stream_twin.php?poll=1&force=1');
        if (!res.ok) return;
        const data = await res.json();
        const lanes = data?.physical?.gate?.lanes || {};
        Object.entries(lanes).forEach(([code, st]) => {
            const card = document.querySelector(`[data-gate-lane-card="${code}"]`);
            if (!card) return;
            const isOpen = (st.barrier === 'OPEN');
            const isIn = (st.direction === 'IN');
            const statusEl = card.querySelector('[data-lane-status]');
            const plateEl = card.querySelector('[data-lane-plate]');
            const ctrEl = card.querySelector('[data-lane-ctr]');
            const wtEl = card.querySelector('[data-lane-weight]');
            const dotEl = card.querySelector('[data-lane-dot]');
            if (statusEl) {
                statusEl.textContent = isOpen ? 'OPEN' : 'STANDBY';
                statusEl.className = `px-2 py-0.5 rounded-full text-[9.5px] font-mono font-extrabold border ${isOpen ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : (isIn ? 'bg-sky-100 text-sky-800 border-sky-200' : 'bg-orange-100 text-orange-800 border-orange-200')}`;
            }
            if (dotEl) dotEl.className = `w-2 h-2 rounded-full shrink-0 ${isOpen ? 'bg-emerald-500 animate-ping' : (isIn ? 'bg-sky-500' : 'bg-orange-500')}`;
            if (plateEl && st.truck_plate) plateEl.textContent = st.truck_plate;
            if (ctrEl && st.container_number) ctrEl.textContent = st.container_number;
            if (wtEl && st.has_weighbridge) {
                const kg = parseFloat(st.weight_kg || 0);
                wtEl.textContent = kg > 0 ? `${Math.round(kg).toLocaleString('id-ID')} kg` : '0 kg (Zeroed)';
            }
        });
    } catch (e) {}
}

function exportGatePDF() {
    const { headers, rows } = getGateExportData();
    CIDPExport.toPDF('Laporan DCSA Event Log', headers, rows, 'Laporan_Gate_DCSA');
}
</script>
