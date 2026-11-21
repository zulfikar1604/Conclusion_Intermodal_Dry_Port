<?php
// =============================================================================
// MODUL: GUDANG CFS & PERGUDANGAN LCL KONSOLIDASI 4.000 M² (RAMPA D1-D5)
// File: pages/cfs.php
// Proyek: Conclusion Intermodal Dry Port (CIDP) 35 Ha — Yard Management System
// Standar: FIATA LCL Conventions, WCO SAFE Framework, GS1-128 SSCC-18 Logistics Label
// PIC : Juan Gamaliel (Yard Planning, CFS & Multimodal Specialist)
// =============================================================================

require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../api/preadvice_helper.php';
require_once __DIR__ . '/../api/twin_state_helper.php';
if (isset($pdo) && $pdo instanceof PDO) { ensure_integration_schema($pdo); }

$cfs_info = [
    'pic'      => 'Juan Gamaliel',
    'role'     => 'Yard Planning, CFS & Multimodal Specialist',
    'desc'     => 'Layanan konsolidasi LCL/FCL, otomasi 5 rampa loading dock hidrolik (D1–D5), pelabelan palet GS1 SSCC-18, dan tally sheet digital.',
    'icon'     => 'fa-warehouse',
    'status'   => '5 Rampa Dock Hidrolik Aktif & MHE Siaga',
    'badge'    => 'bg-blue-50 text-[#0170b9] border-blue-200'
];

// Handle Form Aksi POST (Job Order Stripping/Stuffing, Tally Update, Pallet Register)
$action_alert = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['cfs_action'])) {
    $cfs_action = $_POST['cfs_action'];

    if ($cfs_action === 'create_job') {
        $job_type    = $_POST['job_type'] ?? 'STRIPPING';
        $ctr_num     = strtoupper(trim($_POST['container_number'] ?? ''));
        $dock_ramp   = trim($_POST['dock_ramp'] ?? 'D1');
        $consignee   = trim($_POST['shipper_consignee'] ?? 'PT Konsumen Logistik');
        $bl_number   = trim($_POST['bl_number'] ?? 'BL-' . strtoupper(substr(md5(uniqid()), 0, 8)));
        $total_pkgs  = intval($_POST['total_packages'] ?? 20);
        $total_wt    = floatval($_POST['total_weight_kg'] ?? 10000.0);
        $total_cbm   = floatval($_POST['total_cbm'] ?? 30.0);
        $tally_notes = trim($_POST['tally_notes'] ?? 'Job order terdaftar.');
        $operator    = trim($_POST['operator_name'] ?? 'Juan Gamaliel');
        $job_num     = 'JO-' . ($job_type === 'STRIPPING' ? 'STP' : 'STF') . '-' . date('Y') . '-' . rand(100, 999);

        try {
            if (isset($pdo)) {
                $stmt = $pdo->prepare("
                    INSERT INTO cfs_jobs (job_number, job_type, container_number, dock_ramp, shipper_consignee, bl_number, total_packages, total_weight_kg, total_cbm, status, operator_name, start_time, tally_notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'IN_PROGRESS', ?, NOW(), ?)
                ");
                $stmt->execute([$job_num, $job_type, $ctr_num, $dock_ramp, $consignee, $bl_number, $total_pkgs, $total_wt, $total_cbm, $operator, $tally_notes]);

                // Update container block to CFS if in database
                $stmtUpCtr = $pdo->prepare("UPDATE containers SET block = 'CFS', status = 'in_yard', gate_in_time = COALESCE(gate_in_time, NOW()) WHERE container_number = ?");
                $stmtUpCtr->execute([$ctr_num]);
                $pdo->prepare("UPDATE preadvice SET status = 'ARRIVED' WHERE container_number = ?")->execute([$ctr_num]);
                accrue_billing($pdo, $ctr_num, "CFS $job_type ($job_num)", 'cfs_amount', 650000);

                // Catat ke yard_events
                $stmtLog = $pdo->prepare("
                    INSERT INTO yard_events (event_type, container_number, to_block, operator_name, billable_amount, notes, created_at)
                    VALUES (?, ?, 'CFS', ?, 650000, ?, NOW())
                ");
                $stmtLog->execute(['CFS_' . $job_type, $ctr_num, $operator, "Job Order $job_num di Rampa $dock_ramp ($total_pkgs palet/koli)"]);
                get_live_twin_state($pdo, true);
            }
            $action_alert = [
                'type' => 'success',
                'title' => 'Job Order CFS Berhasil Diterbitkan',
                'msg' => "Job Order <strong>$job_num</strong> ($job_type) untuk kontainer <strong>$ctr_num</strong> di Rampa <strong>$dock_ramp</strong> telah aktif di sistem."
            ];
        } catch (Exception $e) {
            $action_alert = [
                'type' => 'error',
                'title' => 'Gagal Menerbitkan Job Order CFS',
                'msg' => $e->getMessage()
            ];
        }
    } elseif ($cfs_action === 'update_tally') {
        $job_id      = intval($_POST['job_id'] ?? 0);
        $add_pallets = intval($_POST['add_pallets'] ?? 1);
        $notes       = trim($_POST['tally_notes'] ?? '');

        try {
            if (isset($pdo) && $job_id > 0) {
                $stmt = $pdo->prepare("UPDATE cfs_jobs SET tally_notes = CONCAT(IFNULL(tally_notes,''), '\n[', NOW(), '] ', ?) WHERE id = ?");
                $stmt->execute(["Update tally: Ditambahkan $add_pallets palet. Catatan: $notes", $job_id]);
                get_live_twin_state($pdo, true);
            }
            $action_alert = [
                'type' => 'success',
                'title' => 'Progres Tally Diperbarui',
                'msg' => "Catatan progres pembongkaran/pemuatan kargo rampa berhasil disimpan."
            ];
        } catch (Exception $e) {
            $action_alert = ['type' => 'error', 'title' => 'Gagal Update Tally', 'msg' => $e->getMessage()];
        }
    } elseif ($cfs_action === 'complete_job') {
        $job_id = intval($_POST['job_id'] ?? 0);
        try {
            if (isset($pdo) && $job_id > 0) {
                $stmt = $pdo->prepare("UPDATE cfs_jobs SET status = 'COMPLETED', completed_time = NOW() WHERE id = ?");
                $stmt->execute([$job_id]);
                get_live_twin_state($pdo, true);
            }
            $action_alert = [
                'type' => 'success',
                'title' => 'Job Order Selesai (Final Tally Confirmed)',
                'msg' => "Aktivitas rampa telah ditutup dan Berita Acara Tally Sheet siap dicetak."
            ];
        } catch (Exception $e) {
            $action_alert = ['type' => 'error', 'title' => 'Gagal Menutup Job Order', 'msg' => $e->getMessage()];
        }
    } elseif ($cfs_action === 'add_pallet') {
        $sscc      = trim($_POST['pallet_sscc'] ?? '');
        if ($sscc === '') {
            $b17 = '0018991200' . str_pad((string)mt_rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
            $sscc = $b17 . gs1_check_digit($b17);
        }
        $commodity = trim($_POST['commodity_name'] ?? 'Kargo Umum LCL');
        $consignee = trim($_POST['consignee'] ?? 'PT Penerima Logistik');
        $category  = trim($_POST['category'] ?? 'general');
        $cartons   = intval($_POST['carton_count'] ?? 20);
        $weight    = floatval($_POST['gross_weight_kg'] ?? 400.0);
        $cbm       = floatval($_POST['cbm'] ?? 1.2);
        $rackReq   = trim($_POST['rack_location'] ?? 'AISLE-A-01-L1');
        $ctr_orig  = strtoupper(trim($_POST['container_origin'] ?? 'MSKU8821940'));

        try {
            if (isset($pdo)) {
                $alloc = cfs_allocate_or_validate_rack($pdo, $rackReq, true);
                if (!$alloc['ok']) {
                    throw new Exception($alloc['message'] ?? 'Slot rak Gudang CFS penuh.');
                }
                $rack = $alloc['code'];

                $stmt = $pdo->prepare("
                    INSERT INTO cfs_pallets (pallet_sscc, container_origin, commodity_name, consignee, category, carton_count, gross_weight_kg, cbm, rack_location, customs_doc, customs_status, status, entry_date)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'SPPB-CEISA', 'CLEARED', 'IN_RACK', NOW())
                ");
                $stmt->execute([$sscc, $ctr_orig, $commodity, $consignee, $category, $cartons, $weight, $cbm, $rack]);
                $pdo->prepare("UPDATE cfs_jobs SET total_packages = total_packages + 1, total_weight_kg = total_weight_kg + ?, total_cbm = total_cbm + ? WHERE container_number = ? AND status = 'IN_PROGRESS'")->execute([$weight, $cbm, $ctr_orig]);
                accrue_billing($pdo, $ctr_orig, "CFS LCL Pallet Handling ($sscc)", 'cfs_amount', 35000);
                cfs_sync_master_lokasi($pdo);
                get_live_twin_state($pdo, true);
            } else {
                $rack = $rackReq;
            }
            $noteFb = !empty($alloc['fallback_used']) ? " (Slot {$rackReq} penuh; otomatis dialihkan ke slot kosong {$rack})" : "";
            $action_alert = [
                'type' => 'success',
                'title' => 'Palet Baru Teregistrasi (GS1 SSCC & Master Lokasi SSoT)',
                'msg' => "Palet dengan nomor SSCC <strong>$sscc</strong> berhasil ditempatkan di <strong>$rack</strong>{$noteFb}. Layout Gudang 6 Blok langsung ter-update."
            ];
        } catch (Exception $e) {
            $action_alert = [
                'type' => 'error',
                'title' => 'Gagal Registrasi Palet',
                'msg' => $e->getMessage()
            ];
        }
    } elseif ($cfs_action === 'trigger_dock_sensor') {
        $dock_id   = trim($_POST['dock_id'] ?? 'D3');
        $mode      = trim($_POST['sensor_mode'] ?? 'arrival'); // arrival | departure
        $plate     = strtoupper(trim($_POST['truck_plate'] ?? 'B 9771 KZA'));
        $ctr_num   = strtoupper(trim($_POST['container_number'] ?? 'MSKU7712048'));
        $job_type  = trim($_POST['job_type'] ?? 'STRIPPING');
        $consignee = trim($_POST['consignee'] ?? 'PT Astra Honda Motor');
        $pkgs      = intval($_POST['total_packages'] ?? 30);
        $weight    = floatval($_POST['total_weight_kg'] ?? 12500.0);

        try {
            if (isset($pdo)) {
                if ($mode === 'arrival') {
                    // 1. Cek atau daftarkan truk jika belum ada
                    $stmtTrk = $pdo->prepare("SELECT id FROM trucks WHERE license_plate = ? LIMIT 1");
                    $stmtTrk->execute([$plate]);
                    $exTrk = $stmtTrk->fetch(PDO::FETCH_ASSOC);
                    if ($exTrk) {
                        $stmtUpTrk = $pdo->prepare("UPDATE trucks SET status = 'in_yard', destination = 'CFS' WHERE id = ?");
                        $stmtUpTrk->execute([$exTrk['id']]);
                    } else {
                        $stmtInTrk = $pdo->prepare("INSERT INTO trucks (license_plate, driver_name, company, status, job_type, destination, gate_in_time) VALUES (?, 'Driver Mitra CFS', ?, 'in_yard', 'drop_off', 'CFS', NOW())");
                        $stmtInTrk->execute([$plate, $consignee]);
                    }

                    // 2. Update status kontainer
                    $stmtUpCtr = $pdo->prepare("UPDATE containers SET block = 'CFS', status = 'in_yard' WHERE container_number = ?");
                    $stmtUpCtr->execute([$ctr_num]);
                    $pdo->prepare("UPDATE preadvice SET status = 'ARRIVED' WHERE container_number = ?")->execute([$ctr_num]);

                    // 3. Buat Job Order baru di Rampa & akrual billing
                    $job_num = 'JO-' . ($job_type === 'STRIPPING' ? 'STP' : 'STF') . '-' . date('Y') . '-' . rand(200, 999);
                    $stmtJob = $pdo->prepare("INSERT INTO cfs_jobs (job_number, job_type, container_number, dock_ramp, shipper_consignee, bl_number, total_packages, total_weight_kg, total_cbm, status, operator_name, start_time, tally_notes) VALUES (?, ?, ?, ?, ?, CONCAT('BL-', LPAD(FLOOR(RAND()*999999), 6, '0')), ?, ?, ?, 'IN_PROGRESS', 'Sensor Ultrasonik HW-06', NOW(), ?)");
                    $stmtJob->execute([$job_num, $job_type, $ctr_num, $dock_id, $consignee, $pkgs, $weight, round($weight/320, 2), "Trigger Sensor Ultrasonik HW-06: Truk $plate terdeteksi merapat di Rampa $dock_id. Leveler hidrolik 15T dinaikkan otomatis."]);
                    accrue_billing($pdo, $ctr_num, "CFS $job_type Rampa $dock_id ($job_num)", 'cfs_amount', 650000);

                    // 4. Catat event log
                    $stmtLog = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, to_block, operator_name, billable_amount, notes, created_at) VALUES ('CFS_DOCK_IN', ?, 'CFS', 'Sensor Ultrasonik HW-06', 650000, ?, NOW())");
                    $stmtLog->execute([$ctr_num, "Deteksi kehadiran armada $plate di Rampa $dock_id ($job_type - $consignee)"]);
                    get_live_twin_state($pdo, true);

                    $action_alert = [
                        'type' => 'success',
                        'title' => "⚡ Sensor Ultrasonik Rampa $dock_id Terpicu (Dock Inbound Detected)",
                        'msg' => "Sensor HW-06 mendeteksi armada <strong>$plate</strong> merapat di <strong>Rampa $dock_id</strong>. Leveler hidrolik 15T dinaikkan otomatis. Job Order <strong>$job_num</strong> ($job_type) untuk kontainer <strong>$ctr_num</strong> berhasil dibuat secara real di database!"
                    ];
                } else {
                    // Mode departure / lepas rampa
                    $stmtJob = $pdo->prepare("UPDATE cfs_jobs SET status = 'COMPLETED', completed_time = NOW() WHERE dock_ramp = ? AND status = 'IN_PROGRESS' ORDER BY id DESC LIMIT 1");
                    $stmtJob->execute([$dock_id]);

                    $stmtLog = $pdo->prepare("INSERT INTO yard_events (event_type, operator_name, notes, created_at) VALUES ('CFS_DOCK_OUT', 'Sensor Ultrasonik HW-06', ?, NOW())");
                    $stmtLog->execute(["Armada meninggalkan Rampa $dock_id. Sinyal lampu hijau (Ready). Job order ditutup."]);
                    get_live_twin_state($pdo, true);

                    $action_alert = [
                        'type' => 'success',
                        'title' => "⚡ Sensor Rampa $dock_id Terpicu (Dock Outbound Cleared)",
                        'msg' => "Armada telah meninggalkan <strong>Rampa $dock_id</strong>. Sensor mendeteksi rampa kosong, lampu indikator berubah <strong>Hijau (Standby)</strong>, dan status pekerjaan di database telah ditutup."
                    ];
                }
            }
        } catch (Exception $e) {
            $action_alert = [
                'type' => 'error',
                'title' => "Gagal Trigger Sensor Rampa $dock_id",
                'msg' => $e->getMessage()
            ];
        }
    } elseif ($cfs_action === 'trigger_floor_scale') {
        $ctr_orig  = strtoupper(trim($_POST['container_origin'] ?? 'MSKU8821940'));
        $commodity = trim($_POST['commodity_name'] ?? 'Komponen Elektronik & Suku Cadang');
        $consignee = trim($_POST['consignee'] ?? 'PT Sharp Electronics Indonesia');
        $category  = trim($_POST['category'] ?? 'electronics');
        $cartons   = intval($_POST['carton_count'] ?? 36);
        $weight    = floatval($_POST['gross_weight_kg'] ?? 485.5);
        $cbm       = floatval($_POST['cbm'] ?? 1.35);
        $rackReq   = trim($_POST['rack_location'] ?? 'AISLE-B-04-L1');
        $b17       = '0018991200' . str_pad((string)mt_rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
        $sscc      = $b17 . gs1_check_digit($b17);

        try {
            if (isset($pdo)) {
                $alloc = cfs_allocate_or_validate_rack($pdo, $rackReq, true);
                if (!$alloc['ok']) {
                    throw new Exception($alloc['message'] ?? 'Seluruh slot rak Gudang CFS penuh.');
                }
                $rack = $alloc['code'];

                $stmt = $pdo->prepare("
                    INSERT INTO cfs_pallets (pallet_sscc, container_origin, commodity_name, consignee, category, carton_count, gross_weight_kg, cbm, rack_location, customs_doc, customs_status, status, entry_date)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'SPPB-CEISA', 'CLEARED', 'IN_RACK', NOW())
                ");
                $stmt->execute([$sscc, $ctr_orig, $commodity, $consignee, $category, $cartons, $weight, $cbm, $rack]);
                $pdo->prepare("UPDATE cfs_jobs SET total_packages = total_packages + 1, total_weight_kg = total_weight_kg + ?, total_cbm = total_cbm + ? WHERE container_number = ? AND status = 'IN_PROGRESS'")->execute([$weight, $cbm, $ctr_orig]);
                accrue_billing($pdo, $ctr_orig, "CFS LCL Pallet Handling ($sscc)", 'cfs_amount', 35000);
                cfs_sync_master_lokasi($pdo);

                $shortPal = 'SSCC-' . substr($sscc, -4);
                $pdo->prepare("UPDATE equipment SET status = 'operating', current_container = ?, last_block = 'CFS', gps_x = 136.0, gps_y = 236.0, last_updated = NOW() WHERE equipment_id = 'FL-01'")->execute([$shortPal]);
                $pdo->prepare("UPDATE equipment SET status = 'carrying', current_container = ?, last_block = 'CFS', gps_x = 92.0, gps_y = 308.0, last_updated = NOW() WHERE equipment_id = 'FL-04'")->execute([$shortPal]);

                $stmtLog = $pdo->prepare("
                    INSERT INTO yard_events (event_type, container_number, equipment_id, operator_name, billable_amount, notes, created_at)
                    VALUES ('PALLET_WEIGHED', ?, 'FL-01', 'HW-21 Floor Scale 3T', 35000, ?, NOW())
                ");
                $stmtLog->execute([$ctr_orig, "Penimbangan & pengukuran CBM palet SSCC $sscc ($weight kg / $cbm CBM) -> masuk rak $rack"]);
                get_live_twin_state($pdo, true);
            } else {
                $rack = $rackReq;
            }
            $noteFb = !empty($alloc['fallback_used']) ? " (dialihkan ke slot kosong)" : "";
            $action_alert = [
                'type' => 'success',
                'title' => '⚡ HW-21 Floor Scale & 3D Pallet Dimensioner Terpicu',
                'msg' => "Sensor membaca bobot kargo <strong>{$weight} kg</strong> dan kubikasi <strong>{$cbm} CBM</strong> ($cartons karton). Label GS1 SSCC <strong>(00) $sscc</strong> diterbitkan ke rak <strong>$rack</strong>{$noteFb} dan menyala di Layout Gudang 6 Blok!"
            ];
        } catch (Exception $e) {
            $action_alert = [
                'type' => 'error',
                'title' => 'Gagal Timbang Floor Scale',
                'msg' => $e->getMessage()
            ];
        }
    } elseif ($cfs_action === 'trigger_hht_scan') {
        $sscc      = trim($_POST['pallet_sscc'] ?? '');
        $targetReq = trim($_POST['target_rack'] ?? 'AISLE-E-02-L2');

        try {
            if (isset($pdo) && !empty($sscc)) {
                $alloc = cfs_allocate_or_validate_rack($pdo, $targetReq, true);
                if (!$alloc['ok']) {
                    throw new Exception($alloc['message'] ?? 'Seluruh slot rak Gudang CFS penuh.');
                }
                $target_rk = $alloc['code'];

                $chkExist = $pdo->prepare("SELECT id, container_origin FROM cfs_pallets WHERE pallet_sscc = ? LIMIT 1");
                $chkExist->execute([$sscc]);
                $exRow = $chkExist->fetch(PDO::FETCH_ASSOC);
                if ($exRow) {
                    $pdo->prepare("UPDATE cfs_pallets SET rack_location = ?, status = 'IN_RACK' WHERE id = ?")->execute([$target_rk, $exRow['id']]);
                    $relCtr = $exRow['container_origin'] ?: 'ONEU6612988';
                } else {
                    $relCtr = 'ONEU6612988';
                    $pdo->prepare("INSERT INTO cfs_pallets (pallet_sscc, container_origin, commodity_name, consignee, category, carton_count, gross_weight_kg, cbm, rack_location, customs_doc, customs_status, status, entry_date) VALUES (?, ?, 'Kargo Scan HHT Zebra', 'Customer LCL CIDP', 'general', 24, 420.0, 1.25, ?, 'SPPB-CEISA', 'CLEARED', 'IN_RACK', NOW())")
                        ->execute([$sscc, $relCtr, $target_rk]);
                }

                cfs_sync_master_lokasi($pdo);
                $pdo->prepare("UPDATE equipment SET status = 'carrying', current_container = ?, last_block = 'CFS', gps_x = 92.0, gps_y = 308.0, last_updated = NOW() WHERE equipment_id = 'FL-04'")->execute(['SSCC-' . substr($sscc, -4)]);

                $stmtLog = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, operator_name, notes, created_at) VALUES ('HHT_TALLY_SCAN', ?, 'FL-04', 'HW-19 Zebra TC26', ?, NOW())");
                $stmtLog->execute([$relCtr, "Pemindaian barcode SSCC $sscc oleh HHT Zebra TC26. Palet diverifikasi masuk rak $target_rk."]);
                get_live_twin_state($pdo, true);
            } else {
                $target_rk = $targetReq;
            }
            $action_alert = [
                'type' => 'success',
                'title' => '⚡ HW-19 Handheld Barcode Scanner (HHT) Terpicu',
                'msg' => "Barcode GS1 SSCC <strong>$sscc</strong> berhasil dipindai via laser optic. Status palet terverifikasi masuk ke rak <strong>$target_rk</strong> di Master Lokasi."
            ];
        } catch (Exception $e) {
            $action_alert = [
                'type' => 'error',
                'title' => 'Gagal Scan Barcode SSCC',
                'msg' => $e->getMessage()
            ];
        }
    } elseif ($cfs_action === 'trigger_cfs_env') {
        $temp = floatval($_POST['temp_val'] ?? 24.2);
        $hum  = floatval($_POST['hum_val'] ?? 58.0);

        try {
            if (isset($pdo)) {
                $stmtLog = $pdo->prepare("INSERT INTO yard_events (event_type, operator_name, notes, created_at) VALUES ('CFS_ENV_TELEMETRY', 'LoRaWAN SHT40 Sensor', ?, NOW())");
                $stmtLog->execute(["Telemetri Suhu Gudang CFS: {$temp}°C, Kelembaban Relatif: {$hum}% RH. Status: AMAN (Standar Kargo Kering & Farmasi Normal)"]);
                get_live_twin_state($pdo, true);
            }
            $action_alert = [
                'type' => 'success',
                'title' => '⚡ LoRaWAN SHT40 Multi-Sensor Gudang Terpicu',
                'msg' => "Transmisi paket telemetri 868 MHz berhasil diterima: Suhu Gudang <strong>{$temp}°C</strong> & Kelembaban <strong>{$hum}% RH</strong>. Standar kualitas kargo kering & farmasi terpenuhi."
            ];
        } catch (Exception $e) {
            $action_alert = [
                'type' => 'error',
                'title' => 'Gagal Telemetri Lingkungan CFS',
                'msg' => $e->getMessage()
            ];
        }
    }
}

// Ambil Data dari MySQL Database
$cfs_jobs = [];
$cfs_pallets = [];
$available_containers = [];

if (isset($pdo)) {
    try {
        $stmtJobs = $pdo->query("SELECT * FROM cfs_jobs ORDER BY id DESC LIMIT 20");
        $cfs_jobs = $stmtJobs->fetchAll(PDO::FETCH_ASSOC);

        $stmtPallets = $pdo->query("SELECT * FROM cfs_pallets ORDER BY id DESC LIMIT 50");
        $cfs_pallets = $stmtPallets->fetchAll(PDO::FETCH_ASSOC);

        $stmtCtr = $pdo->query("SELECT container_number, size_type, cargo_type, block FROM containers WHERE status IN ('in_yard', 'in_transit') ORDER BY id DESC LIMIT 25");
        $available_containers = $stmtCtr->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Fallback jika query error
    }
}

// =============================================================================
// QUERY STATUS RAK WAREHOUSE MASTER LAYOUT 6 BLOK (master_lokasi OPTIMIZED 1x)
// =============================================================================
$map_status = [];
$blok_list_dynamic = [];
$sektor_list_per_blok = [];

if (isset($conn) && $conn) {
    $get_all = mysqli_query($conn, "SELECT blok, bagian, rak, status FROM master_lokasi ORDER BY blok ASC, bagian ASC, rak ASC");
    if ($get_all) {
        while ($row = mysqli_fetch_assoc($get_all)) {
            $b = (string)$row['blok'];
            $s = strtoupper(trim($row['bagian']));
            $r = (int)$row['rak'];
            $isFilled = (strtolower(trim($row['status'])) === 'terisi');

            if (!isset($map_status[$b])) {
                $map_status[$b] = [];
                if (!in_array($b, $blok_list_dynamic)) {
                    $blok_list_dynamic[] = $b;
                }
            }

            if (!isset($map_status[$b][$s])) {
                $map_status[$b][$s] = [];
                if (!isset($sektor_list_per_blok[$b])) {
                    $sektor_list_per_blok[$b] = [];
                }
                if (!in_array($s, $sektor_list_per_blok[$b])) {
                    $sektor_list_per_blok[$b][] = $s;
                }
            }

            $map_status[$b][$s][$r] = $isFilled;
        }
    }
}

// Gunakan blok list dinamis jika ada, default ke 1-6
$blok_list = !empty($blok_list_dynamic) ? $blok_list_dynamic : ['1', '2', '3', '4', '5', '6'];
if (!in_array('6', $blok_list)) {
    $blok_list[] = '6';
}

// Hitung ringkasan kapasitas real-time
$totalBlok = count($blok_list);
$totalSektor = 0;
$totalSlot = 0;
$filledSlot = 0;

foreach ($blok_list as $b) {
    $sektors = isset($sektor_list_per_blok[$b]) ? $sektor_list_per_blok[$b] : [];
    $totalSektor += count($sektors);
    
    foreach ($sektors as $s) {
        $levels = $map_status[$b][$s] ?? [];
        $totalSlot += count($levels);
        foreach ($levels as $r => $isFilled) {
            if ($isFilled) $filledSlot++;
        }
    }
}

$emptySlot = $totalSlot - $filledSlot;
$occupancy = $totalSlot > 0 ? round(($filledSlot / $totalSlot) * 100, 1) : 0;
$cfs_initial_twin = (isset($pdo) && $pdo instanceof PDO) ? get_live_twin_state($pdo, true) : [];

// Fallback Data Demo jika database masih kosong
if (false && empty($cfs_jobs)) {
    $cfs_jobs = [
        [
            'id' => 1,
            'job_number' => 'JO-STP-2026-001',
            'job_type' => 'STRIPPING',
            'container_number' => 'MSKU8821940',
            'dock_ramp' => 'D1',
            'shipper_consignee' => 'PT Astra Honda Motor',
            'bl_number' => 'BL-MAEU-982104',
            'total_packages' => 40,
            'total_weight_kg' => 18500.00,
            'total_cbm' => 52.40,
            'customs_status' => 'SPPB_CLEARED',
            'status' => 'IN_PROGRESS',
            'operator_name' => 'Juan Gamaliel',
            'start_time' => date('Y-m-d H:i', strtotime('-2 hours')),
            'completed_time' => null,
            'tally_notes' => 'Stripping 40 palet CKD komponen transmisi motor. 33 palet sudah masuk Aisle E. Kondisi kemasan utuh, segel ML-ID882109 sesuai.'
        ],
        [
            'id' => 2,
            'job_number' => 'JO-STP-2026-002',
            'job_type' => 'STRIPPING',
            'container_number' => 'CMAU7718290',
            'dock_ramp' => 'D2',
            'shipper_consignee' => 'PT Sharp Electronics Indonesia',
            'bl_number' => 'BL-CMAC-112093',
            'total_packages' => 40,
            'total_weight_kg' => 14200.00,
            'total_cbm' => 48.00,
            'customs_status' => 'SPPB_CLEARED',
            'status' => 'IN_PROGRESS',
            'operator_name' => 'Ahmad Dani',
            'start_time' => date('Y-m-d H:i', strtotime('-1 hour')),
            'completed_time' => null,
            'tally_notes' => 'Stripping panel solar cell & inverter hybrid. 18 palet dialokasikan ke Aisle B. Segel CMA-78192 utuh.'
        ],
        [
            'id' => 3,
            'job_number' => 'JO-STF-2026-003',
            'job_type' => 'STUFFING',
            'container_number' => 'ONEU3391820',
            'dock_ramp' => 'D4',
            'shipper_consignee' => 'PT Pan Brothers Tbk / H&M Global',
            'bl_number' => 'BL-ONEY-440192',
            'total_packages' => 75,
            'total_weight_kg' => 18420.00,
            'total_cbm' => 58.20,
            'customs_status' => 'SPPB_CLEARED',
            'status' => 'IN_PROGRESS',
            'operator_name' => 'Budi Santoso',
            'start_time' => date('Y-m-d H:i', strtotime('-3 hours')),
            'completed_time' => null,
            'tally_notes' => 'Stuffing konsolidasi pakaian jadi (Garment) tujuan Hamburg Port. 52/75 palet termuat. SOLAS VGM on track.'
        ],
        [
            'id' => 4,
            'job_number' => 'JO-STF-2026-004',
            'job_type' => 'STUFFING',
            'container_number' => 'TEMU2281093',
            'dock_ramp' => 'D5',
            'shipper_consignee' => 'PT Indofood Sukses Makmur',
            'bl_number' => 'BL-TEMU-550183',
            'total_packages' => 30,
            'total_weight_kg' => 9800.00,
            'total_cbm' => 28.50,
            'customs_status' => 'SPPB_CLEARED',
            'status' => 'IN_PROGRESS',
            'operator_name' => 'Juan Gamaliel',
            'start_time' => date('Y-m-d H:i', strtotime('-45 minutes')),
            'completed_time' => null,
            'tally_notes' => 'Konsolidasi bumbu instan & mie untuk distribusi feeder rute Tanjung Perak Surabaya.'
        ]
    ];
}

if (false && empty($cfs_pallets)) {
    $cfs_pallets = [
        [
            'id' => 1,
            'job_id' => 1,
            'pallet_sscc' => '00189912001092837101',
            'container_origin' => 'MSKU8821940',
            'commodity_name' => 'Komponen Transmisi Motor Matic',
            'consignee' => 'PT Astra Honda Motor',
            'category' => 'automotive',
            'carton_count' => 24,
            'gross_weight_kg' => 480.00,
            'cbm' => 1.25,
            'rack_location' => 'AISLE-E-02-L1',
            'customs_doc' => 'SPPB/2026/09/1892',
            'customs_status' => 'CLEARED',
            'status' => 'IN_RACK',
            'entry_date' => date('Y-m-d H:i', strtotime('-2 days'))
        ],
        [
            'id' => 2,
            'job_id' => 1,
            'pallet_sscc' => '00189912001092837102',
            'container_origin' => 'MSKU8821940',
            'commodity_name' => 'Crankcase Aluminium Engine Block',
            'consignee' => 'PT Astra Honda Motor',
            'category' => 'automotive',
            'carton_count' => 20,
            'gross_weight_kg' => 520.00,
            'cbm' => 1.30,
            'rack_location' => 'AISLE-E-02-L2',
            'customs_doc' => 'SPPB/2026/09/1892',
            'customs_status' => 'CLEARED',
            'status' => 'IN_RACK',
            'entry_date' => date('Y-m-d H:i', strtotime('-2 days'))
        ],
        [
            'id' => 3,
            'job_id' => 2,
            'pallet_sscc' => '00189912001092837103',
            'container_origin' => 'CMAU7718290',
            'commodity_name' => 'Inverter Solar Cell 5kW Hybrid',
            'consignee' => 'PT Sharp Electronics',
            'category' => 'electronics',
            'carton_count' => 15,
            'gross_weight_kg' => 390.00,
            'cbm' => 1.15,
            'rack_location' => 'AISLE-B-04-L1',
            'customs_doc' => 'SPPB/2026/09/2041',
            'customs_status' => 'CLEARED',
            'status' => 'IN_RACK',
            'entry_date' => date('Y-m-d H:i', strtotime('-1 day'))
        ],
        [
            'id' => 4,
            'job_id' => 2,
            'pallet_sscc' => '00189912001092837104',
            'container_origin' => 'CMAU7718290',
            'commodity_name' => 'Photovoltaic Monocrystalline Module',
            'consignee' => 'PT Sharp Electronics',
            'category' => 'electronics',
            'carton_count' => 30,
            'gross_weight_kg' => 680.00,
            'cbm' => 1.85,
            'rack_location' => 'AISLE-B-04-L2',
            'customs_doc' => 'SPPB/2026/09/2041',
            'customs_status' => 'CLEARED',
            'status' => 'IN_RACK',
            'entry_date' => date('Y-m-d H:i', strtotime('-1 day'))
        ],
        [
            'id' => 5,
            'job_id' => null,
            'pallet_sscc' => '00189912001092837105',
            'container_origin' => 'TGHU1190241',
            'commodity_name' => 'Heavy Duty Hydraulic Pump & Valve',
            'consignee' => 'PT United Tractors Tbk',
            'category' => 'machinery',
            'carton_count' => 4,
            'gross_weight_kg' => 850.00,
            'cbm' => 1.60,
            'rack_location' => 'AISLE-A-01-L1',
            'customs_doc' => 'SPPB/2026/09/0981',
            'customs_status' => 'CLEARED',
            'status' => 'IN_RACK',
            'entry_date' => date('Y-m-d H:i', strtotime('-4 days'))
        ],
        [
            'id' => 6,
            'job_id' => null,
            'pallet_sscc' => '00189912001092837106',
            'container_origin' => 'TGHU1190241',
            'commodity_name' => 'Excavator Track Shoe Segment',
            'consignee' => 'PT United Tractors Tbk',
            'category' => 'machinery',
            'carton_count' => 8,
            'gross_weight_kg' => 1200.00,
            'cbm' => 1.40,
            'rack_location' => 'AISLE-A-01-L2',
            'customs_doc' => 'SPPB/2026/09/0981',
            'customs_status' => 'CLEARED',
            'status' => 'IN_RACK',
            'entry_date' => date('Y-m-d H:i', strtotime('-4 days'))
        ],
        [
            'id' => 7,
            'job_id' => null,
            'pallet_sscc' => '00189912001092837107',
            'container_origin' => 'SUDU5510291',
            'commodity_name' => 'Bahan Pewarna Makanan Ekstrak Alami',
            'consignee' => 'PT Mayora Indah Tbk',
            'category' => 'fmcg',
            'carton_count' => 40,
            'gross_weight_kg' => 420.00,
            'cbm' => 1.10,
            'rack_location' => 'AISLE-C-03-L1',
            'customs_doc' => 'SPPB/2026/09/3104',
            'customs_status' => 'CLEARED',
            'status' => 'IN_RACK',
            'entry_date' => date('Y-m-d H:i', strtotime('-3 days'))
        ],
        [
            'id' => 8,
            'job_id' => null,
            'pallet_sscc' => '00189912001092837108',
            'container_origin' => 'SUDU5510291',
            'commodity_name' => 'Perisa Vanila Grade Industri Makanan',
            'consignee' => 'PT Mayora Indah Tbk',
            'category' => 'fmcg',
            'carton_count' => 35,
            'gross_weight_kg' => 380.00,
            'cbm' => 0.95,
            'rack_location' => 'AISLE-C-03-L2',
            'customs_doc' => 'SPPB/2026/09/3104',
            'customs_status' => 'CLEARED',
            'status' => 'IN_RACK',
            'entry_date' => date('Y-m-d H:i', strtotime('-3 days'))
        ],
        [
            'id' => 9,
            'job_id' => 3,
            'pallet_sscc' => '00189912001092837109',
            'container_origin' => 'ONEU3391820',
            'commodity_name' => 'Kaos Katun Organik Ekspor (Cartons)',
            'consignee' => 'H&M Hennes & Mauritz AB',
            'category' => 'textile',
            'carton_count' => 50,
            'gross_weight_kg' => 310.00,
            'cbm' => 1.45,
            'rack_location' => 'AISLE-D-05-L1',
            'customs_doc' => 'PEB/2026/09/8812',
            'customs_status' => 'CLEARED',
            'status' => 'STAGING_OUT',
            'entry_date' => date('Y-m-d H:i', strtotime('-12 hours'))
        ],
        [
            'id' => 10,
            'job_id' => 3,
            'pallet_sscc' => '00189912001092837110',
            'container_origin' => 'ONEU3391820',
            'commodity_name' => 'Jaket Denim Pria Ekspor Premium',
            'consignee' => 'H&M Hennes & Mauritz AB',
            'category' => 'textile',
            'carton_count' => 45,
            'gross_weight_kg' => 340.00,
            'cbm' => 1.50,
            'rack_location' => 'AISLE-D-05-L2',
            'customs_doc' => 'PEB/2026/09/8812',
            'customs_status' => 'CLEARED',
            'status' => 'STAGING_OUT',
            'entry_date' => date('Y-m-d H:i', strtotime('-12 hours'))
        ],
        [
            'id' => 11,
            'job_id' => null,
            'pallet_sscc' => '00189912001092837111',
            'container_origin' => 'HLBU9920141',
            'commodity_name' => 'Sensor Otomotif Mikroprosesor ECU',
            'consignee' => 'PT Denso Indonesia',
            'category' => 'automotive',
            'carton_count' => 12,
            'gross_weight_kg' => 180.00,
            'cbm' => 0.65,
            'rack_location' => 'AISLE-F-01-L1',
            'customs_doc' => 'PIB-HOLD-RED',
            'customs_status' => 'RED_LANE',
            'status' => 'HOLD',
            'entry_date' => date('Y-m-d H:i', strtotime('-1 day'))
        ],
        [
            'id' => 12,
            'job_id' => 4,
            'pallet_sscc' => '00189912001092837112',
            'container_origin' => 'TEMU2281093',
            'commodity_name' => 'Bumbu Racik Spesial Kuliner Nusantara',
            'consignee' => 'PT Indofood Sukses Makmur',
            'category' => 'fmcg',
            'carton_count' => 60,
            'gross_weight_kg' => 450.00,
            'cbm' => 1.20,
            'rack_location' => 'AISLE-C-06-L1',
            'customs_doc' => 'DO-DOM/2026/102',
            'customs_status' => 'CLEARED',
            'status' => 'STAGING_OUT',
            'entry_date' => date('Y-m-d H:i', strtotime('-6 hours'))
        ]
    ];
}

// Hitung Statistik KPI CFS
$total_pallet_capacity = 1200; // Kapasitas 6 Aisle A-F
$occupied_pallets      = 874;  // Aktif dalam gudang
$available_pallets     = $total_pallet_capacity - $occupied_pallets;
$utilization_pct       = round(($occupied_pallets / $total_pallet_capacity) * 100, 1);

// Status Rampa D1 - D5
$dock_status = [
    'D1' => ['status' => 'STRIPPING', 'truck' => 'B 9841 TEI', 'ctr' => 'MSKU8821940', 'pkg' => '33/40 Palet', 'pct' => 82, 'badge' => 'bg-amber-100 text-amber-800 border-amber-300'],
    'D2' => ['status' => 'STRIPPING', 'truck' => 'B 9012 UXZ', 'ctr' => 'CMAU7718290', 'pkg' => '18/40 Palet', 'pct' => 45, 'badge' => 'bg-amber-100 text-amber-800 border-amber-300'],
    'D3' => ['status' => 'STANDBY',   'truck' => '-',           'ctr' => '-',           'pkg' => 'Siap Terima Truk', 'pct' => 0,  'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
    'D4' => ['status' => 'STUFFING',  'truck' => 'B 9411 KZA', 'ctr' => 'ONEU3391820', 'pkg' => '52/75 Palet', 'pct' => 69, 'badge' => 'bg-blue-100 text-blue-800 border-blue-300'],
    'D5' => ['status' => 'STUFFING',  'truck' => 'B 9200 VBC', 'ctr' => 'TEMU2281093', 'pkg' => '27/30 Palet', 'pct' => 90, 'badge' => 'bg-blue-100 text-blue-800 border-blue-300']
];

$active_docks_count = 4;
?>

<!-- Alert Feedback Pasca Aksi Form -->
<?php if ($action_alert): ?>
<div class="mb-4 p-4 rounded-xl border flex items-start space-x-3 animate-fadeIn <?= $action_alert['type'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : ($action_alert['type'] === 'warning' ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-blue-50 border-blue-200 text-blue-900') ?>">
    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 <?= $action_alert['type'] === 'success' ? 'bg-emerald-100 text-emerald-700' : ($action_alert['type'] === 'warning' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') ?>">
        <i class="fa-solid <?= $action_alert['type'] === 'success' ? 'fa-check' : 'fa-info' ?>"></i>
    </div>
    <div class="flex-1 min-w-0">
        <h4 class="text-sm font-bold"><?= $action_alert['title'] ?></h4>
        <p class="text-xs mt-0.5"><?= $action_alert['msg'] ?></p>
    </div>
    <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- HEADER & CONTEXT MODUL CFS WAREHOUSE 4.000 M² -->
<!-- ========================================================================= -->
<div class="bg-white rounded-xl px-3 py-2 shadow-2xs border border-slate-200/80 mb-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
    <div class="flex items-center space-x-2.5">
        <div class="w-7 h-7 rounded-lg bg-orange-50 border border-orange-200/70 text-orange-600 flex items-center justify-center text-xs shadow-2xs flex-shrink-0">
            <i class="fa-solid fa-warehouse"></i>
        </div>
        <div>
            <div class="flex items-center flex-wrap gap-1.5">
                <h1 class="text-[13px] font-extrabold text-slate-900 tracking-tight">
                    CFS &amp; Pergudangan LCL Konsolidasi 4.000 m²
                </h1>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-50 text-[#0170b9] border border-blue-200 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0170b9] animate-pulse"></span>
                    5 Rampa Hidrolik
                </span>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-orange-50 text-orange-700 border border-orange-200">
                    GS1 SSCC-18
                </span>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Stripping &amp; Stuffing
                </span>
            </div>
        </div>
    </div>

    <!-- Tombol Aksi Cepat Header (Compact) -->
    <div class="flex items-center flex-wrap gap-1.5 w-full sm:w-auto">
        <button onclick="openModal('modalNewJobOrder')" class="px-2.5 py-1 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white text-[11px] font-bold rounded-lg shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
            <i class="fa-solid fa-dolly text-[10px]"></i>
            <span>+ Job Order</span>
        </button>
        <button onclick="openModal('modalAddPallet')" class="px-2 py-1 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-[11px] font-semibold rounded-lg shadow-2xs transition flex items-center space-x-1 cursor-pointer">
            <i class="fa-solid fa-pallet text-orange-600 text-[10px]"></i>
            <span>Palet</span>
        </button>
        <button onclick="exportCfsExcel()" class="px-2 py-1 bg-white border border-slate-200 hover:bg-emerald-50 text-emerald-700 text-[11px] font-semibold rounded-lg shadow-2xs transition flex items-center space-x-1 cursor-pointer" title="Ekspor Manifest LCL ke Excel">
            <i class="fa-solid fa-file-excel text-emerald-600 text-[10px]"></i>
            <span>Excel</span>
        </button>
        <button onclick="printCfsRekap()" class="px-2 py-1 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-[11px] font-semibold rounded-lg shadow-2xs transition cursor-pointer" title="Cetak Rekap Operasional CFS (Format A4 Resmi)">
            <i class="fa-solid fa-print text-[#0170b9] text-[10px]"></i>
        </button>
    </div>
</div>

<!-- 5 Micro-Cards Indikator KPI Eksekutif CFS Warehouse -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2 mb-2.5">
    <!-- Card 1: Okupansi Palet Gudang -->
    <div class="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-2xs flex items-center justify-between transition hover:border-orange-200">
        <div class="min-w-0 pr-1.5 flex-1">
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider truncate mb-0.5">Okupansi Rak Palet</p>
            <div class="flex items-baseline space-x-1">
                <h3 class="text-[13.5px] font-extrabold text-slate-900 tracking-tight leading-tight"><?= $occupied_pallets ?></h3>
                <span class="text-[9px] text-slate-400 font-semibold">/ <?= $total_pallet_capacity ?></span>
            </div>
            <div class="mt-1 w-full h-1 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-orange-500 to-orange-600 rounded-full" style="width: <?= $utilization_pct ?>%"></div>
            </div>
            <p class="text-[8.5px] text-slate-500 mt-1 flex justify-between">
                <span>Rak A-F</span>
                <span class="text-orange-600 font-bold"><?= $utilization_pct ?>%</span>
            </p>
        </div>
        <div class="w-7 h-7 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center text-[11px] shadow-2xs flex-shrink-0">
            <i class="fa-solid fa-pallet"></i>
        </div>
    </div>

    <!-- Card 2: 5 Rampa Dock Leveler D1-D5 -->
    <div class="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-2xs flex items-center justify-between transition hover:border-emerald-200">
        <div class="min-w-0 pr-1.5 flex-1">
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider truncate mb-0.5">Rampa Dock Leveler</p>
            <div class="flex items-baseline space-x-1">
                <h3 class="text-[13.5px] font-extrabold text-emerald-600 tracking-tight leading-tight"><?= $active_docks_count ?> / 5</h3>
                <span class="text-[9px] text-slate-400 font-semibold">Aktif</span>
            </div>
            <p class="text-[8.5px] text-slate-500 mt-1.5 truncate">
                D1-D2 Strip &bull; D4-D5 Stuff
            </p>
        </div>
        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-[11px] shadow-2xs flex-shrink-0">
            <i class="fa-solid fa-truck-ramp-box"></i>
        </div>
    </div>

    <!-- Card 3: Job Order Stripping Hari Ini -->
    <div class="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-2xs flex items-center justify-between transition hover:border-amber-200">
        <div class="min-w-0 pr-1.5 flex-1">
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider truncate mb-0.5">Stripping FCL &rarr; LCL</p>
            <div class="flex items-baseline space-x-1">
                <h3 class="text-[13.5px] font-extrabold text-slate-900 tracking-tight leading-tight">18</h3>
                <span class="text-[9px] text-emerald-600 font-bold">420 Palet</span>
            </div>
            <p class="text-[8.5px] text-slate-500 mt-1.5 truncate">
                Tally Sheet: 0 Selisih Kargo
            </p>
        </div>
        <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-[11px] shadow-2xs flex-shrink-0">
            <i class="fa-solid fa-box-open"></i>
        </div>
    </div>

    <!-- Card 4: Job Order Stuffing Ekspor -->
    <div class="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-2xs flex items-center justify-between transition hover:border-blue-200">
        <div class="min-w-0 pr-1.5 flex-1">
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider truncate mb-0.5">Stuffing Ekspor</p>
            <div class="flex items-baseline space-x-1">
                <h3 class="text-[13.5px] font-extrabold text-slate-900 tracking-tight leading-tight">12</h3>
                <span class="text-[9px] text-blue-600 font-bold">284 CBM</span>
            </div>
            <p class="text-[8.5px] text-slate-500 mt-1.5 truncate">
                SOLAS VGM &bull; Segel ISO 17712
            </p>
        </div>
        <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-[11px] shadow-2xs flex-shrink-0">
            <i class="fa-solid fa-boxes-packing"></i>
        </div>
    </div>

    <!-- Card 5: Dwell Time Rata-rata LCL -->
    <div class="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-2xs flex items-center justify-between transition hover:border-purple-200 col-span-2 md:col-span-1">
        <div class="min-w-0 pr-1.5 flex-1">
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider truncate mb-0.5">LCL Dwell Time</p>
            <div class="flex items-baseline space-x-1">
                <h3 class="text-[13.5px] font-extrabold text-purple-700 tracking-tight leading-tight">2.8 Hari</h3>
                <span class="text-[9px] text-emerald-600 font-bold">&lt; 3.5 Hari</span>
            </div>
            <p class="text-[8.5px] text-slate-500 mt-1.5 truncate">
                Free Storage 3 Hari Kerja
            </p>
        </div>
        <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-700 flex items-center justify-center text-[11px] shadow-2xs flex-shrink-0">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- NAVIGASI TAB MODUL CFS (4 TAB UTAMA) -->
<!-- ========================================================================= -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden mb-6">
    <div class="border-b border-gray-200 px-4 sm:px-5 flex flex-wrap items-center justify-between gap-2 bg-gray-50/50">
        <div class="flex flex-wrap gap-1.5 py-2.5">
            <button onclick="switchCfsTab('tab-rampa')" id="btn-tab-rampa" class="cfs-tab-btn active px-3.5 py-2 rounded-xl text-xs font-bold transition-all bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-xs shadow-orange-500/20 flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-truck-ramp-box"></i>
                <span>Rampa D1–D5 (Live)</span>
            </button>
            <button onclick="switchCfsTab('tab-manifest')" id="btn-tab-manifest" class="cfs-tab-btn px-3.5 py-2 rounded-xl text-xs font-semibold transition-all text-slate-600 hover:text-orange-600 hover:bg-orange-50/70 flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span>Kargo &amp; Palet GS1</span>
            </button>
            <button onclick="switchCfsTab('tab-denah')" id="btn-tab-denah" class="cfs-tab-btn px-3.5 py-2 rounded-xl text-xs font-semibold transition-all text-slate-600 hover:text-orange-600 hover:bg-orange-50/70 flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-map-location-dot"></i>
                <span>Layout Gudang (6 Blok)</span>
            </button>
            <button onclick="switchCfsTab('tab-joborder')" id="btn-tab-joborder" class="cfs-tab-btn px-3.5 py-2 rounded-xl text-xs font-semibold transition-all text-slate-600 hover:text-orange-600 hover:bg-orange-50/70 flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-clipboard-check"></i>
                <span>Job Order &amp; Tally</span>
            </button>
        </div>
        <div class="text-[11px] text-gray-400 font-mono hidden xl:block whitespace-nowrap">
            Sektor Barat (Zona F-CFS) &bull; 4.000 m²
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 1: MANAJEMEN RAMPA DOCK LEVELER D1 - D5 (LIVE DOCK) -->
    <!-- ===================================================================== -->
    <div id="tab-rampa" class="cfs-tab-content p-5 sm:p-6 block">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
            <div>
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Status Operasional 5 Rampa Loading Dock Hidrolik (D1 - D5)
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Dock Leveler 15T &bull; Shelter &bull; Traffic Signal</p>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 font-bold border border-amber-200">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> Stripping Inbound (2)
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 font-bold border border-blue-200">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span> Stuffing Outbound (2)
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Standby (1)
                </span>
            </div>
        </div>

        <!-- Grid 5 Kartu Rampa -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <?php foreach ($dock_status as $dock_id => $dock): 
                $is_idle = ($dock['status'] === 'STANDBY');
            ?>
            <div id="dock-card-<?= $dock_id ?>" data-dock="<?= $dock_id ?>" class="rounded-2xl border <?= $is_idle ? 'border-dashed border-gray-300 bg-gray-50/50' : 'border-gray-200 bg-white' ?> p-4 shadow-xs flex flex-col justify-between relative overflow-hidden transition-all hover:shadow-md">
                <!-- Top Dock Identifier -->
                <div>
                    <div class="flex flex-wrap items-center justify-between gap-1.5 mb-3">
                        <div class="flex items-center space-x-2 min-w-0">
                            <span class="w-8 h-8 shrink-0 rounded-xl font-black text-xs flex items-center justify-center <?= $is_idle ? 'bg-gray-200 text-gray-700' : 'bg-[#0170b9] text-white shadow-sm' ?>">
                                <?= $dock_id ?>
                            </span>
                            <div class="min-w-0">
                                <span class="text-xs font-bold text-gray-900 block leading-tight">Rampa <?= $dock_id ?></span>
                                <span class="text-[10px] text-gray-500 block">Leveler 15T</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center whitespace-nowrap shrink-0 px-2 py-0.5 rounded-full text-[9px] font-bold border <?= $dock['badge'] ?>">
                            <?= $dock['status'] ?>
                        </span>
                    </div>

                    <?php if (!$is_idle): ?>
                    <!-- Active Cargo Details -->
                    <div class="space-y-2 text-xs">
                        <div class="bg-gray-50 rounded-xl p-2.5 border border-gray-100">
                            <div class="text-[10px] text-gray-400 font-mono uppercase">Kontainer &amp; Truk</div>
                            <div class="font-extrabold text-gray-900 text-xs font-mono tracking-tight mt-0.5"><?= $dock['ctr'] ?></div>
                            <div class="text-[10px] text-gray-600 font-semibold mt-0.5">Head: <?= $dock['truck'] ?></div>
                        </div>

                        <!-- Progress Bar Tally -->
                        <div>
                            <div class="flex items-center justify-between text-[11px] mb-1">
                                <span class="text-gray-500 font-medium">Tally Palet:</span>
                                <span class="font-bold text-gray-800"><?= $dock['pkg'] ?></span>
                            </div>
                            <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full <?= $dock['status'] === 'STRIPPING' ? 'bg-amber-500' : 'bg-blue-600' ?> rounded-full transition-all duration-500" style="width: <?= $dock['pct'] ?>%"></div>
                            </div>
                            <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1 font-mono">
                                <span>Progress</span>
                                <span class="font-bold text-gray-700"><?= $dock['pct'] ?>%</span>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Standby State -->
                    <div class="py-8 text-center text-gray-400">
                        <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2 text-lg">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <span class="text-xs font-bold text-gray-700 block">Rampa Kosong</span>
                        <span class="text-[11px] text-gray-500 block mt-0.5">Lampu Sinyal: Hijau (Ready)</span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Bottom Action Buttons -->
                <div class="mt-4 pt-3 border-t border-gray-100">
                    <?php if (!$is_idle): ?>
                    <div class="grid grid-cols-2 gap-1.5">
                        <button onclick="openTallyModal('<?= $dock_id ?>', '<?= $dock['ctr'] ?>', '<?= $dock['status'] ?>')" class="px-2 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[10px] font-bold rounded-lg transition text-center" title="Update Progres Tally">
                            <i class="fa-solid fa-plus-minus mr-1"></i> Tally
                        </button>
                        <form method="POST" data-confirm="Apakah Anda yakin ingin menyelesaikan dan menutup Job Order di Rampa <?= htmlspecialchars($dock_id) ?>?" data-confirm-title="Konfirmasi Tutup Job Order CFS" data-confirm-target="Rampa <?= htmlspecialchars($dock_id) ?>" data-confirm-tone="warning">
                            <input type="hidden" name="cfs_action" value="complete_job">
                            <input type="hidden" name="job_id" value="<?= ($dock_id === 'D1' ? 1 : ($dock_id === 'D2' ? 2 : ($dock_id === 'D4' ? 3 : 4))) ?>">
                            <button type="submit" class="w-full px-2 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold rounded-lg transition text-center shadow-xs">
                                <i class="fa-solid fa-check mr-0.5"></i> Selesai
                            </button>
                        </form>
                    </div>
                    <?php else: ?>
                    <button onclick="openModalWithDock('modalNewJobOrder', '<?= $dock_id ?>')" class="w-full py-1.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white text-xs font-bold rounded-lg shadow-xs transition flex items-center justify-center space-x-1 cursor-pointer">
                        <i class="fa-solid fa-truck-arrow-right text-[11px]"></i>
                        <span>Assign Truk</span>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Status Keamanan Rampa Ringkas -->
        <div class="mt-4 bg-slate-50 rounded-xl px-4 py-2.5 border border-slate-200 flex flex-wrap items-center justify-between gap-2 text-xs">
            <div class="flex items-center gap-2 font-bold text-slate-800">
                <i class="fa-solid fa-shield-halved text-[#0170b9]"></i>
                <span>Wheel Chock &amp; Dock Seal Interlock K3 Aktif</span>
            </div>
            <span class="px-2.5 py-0.5 bg-white rounded-lg border border-slate-200 text-slate-700 font-semibold text-[11px]">
                <i class="fa-solid fa-bolt text-amber-500 mr-1"></i> MHE Terpetakan: FL-01..FL-04 (4 Forklift Linde E-Series)
            </span>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 2: MANIFEST KARGO LCL & INVENTORI PALET (GS1 SSCC-18) -->
    <!-- ===================================================================== -->
    <div id="tab-manifest" class="cfs-tab-content p-5 sm:p-6 hidden">
        <!-- Filter & Search Toolbar -->
        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 mb-5">
            <div class="flex items-center flex-1 max-w-lg space-x-2">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 text-xs">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" id="searchPalletInput" onkeyup="filterPalletTable()" placeholder="Cari No. Palet SSCC, B/L, Barang, atau Consignee..." class="w-full pl-9 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white transition-all">
                </div>
                <select id="filterAisleSelect" onchange="filterPalletTable()" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 font-medium focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="">Semua Aisle (A - F)</option>
                    <option value="AISLE-A">Aisle A (Mesin Berat)</option>
                    <option value="AISLE-B">Aisle B (Elektronik)</option>
                    <option value="AISLE-C">Aisle C (FMCG Food)</option>
                    <option value="AISLE-D">Aisle D (Tekstil/Garment)</option>
                    <option value="AISLE-E">Aisle E (Otomotif)</option>
                    <option value="AISLE-F">Aisle F (Staging &amp; Hold)</option>
                </select>
                <select id="filterCategorySelect" onchange="filterPalletTable()" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 font-medium focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="">Semua Kategori</option>
                    <option value="automotive">Otomotif</option>
                    <option value="electronics">Elektronik</option>
                    <option value="machinery">Mesin</option>
                    <option value="fmcg">FMCG</option>
                    <option value="textile">Tekstil</option>
                </select>
                <select id="palletLimitSelect" data-limit-target="#palletTable" data-limit-callback="filterPalletTable" data-count-target="#palletCountDisplay" data-count-unit="palet" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="10" selected>Tampil: 10</option>
                    <option value="25">Tampil: 25</option>
                    <option value="50">Tampil: 50</option>
                    <option value="all">Semua</option>
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-xs text-gray-500 font-medium" id="palletCountDisplay">Menampilkan <?= count($cfs_pallets) ?> palet terdaftar</span>
            </div>
        </div>

        <!-- Tabel Inventori Palet LCL -->
        <div class="overflow-x-auto border border-gray-200 rounded-2xl shadow-2xs">
            <table class="w-full text-left border-collapse text-xs" id="palletTable">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider text-[10.5px]">
                        <th class="py-3 px-2.5 text-center w-10">No</th>
                        <th class="py-3 px-3.5">No. Palet SSCC-18 (GS1)</th>
                        <th class="py-3 px-3.5">Ex-Kontainer &amp; Dokumen</th>
                        <th class="py-3 px-3.5">Deskripsi Komoditas &amp; Consignee</th>
                        <th class="py-3 px-3.5 text-center">Lokasi Rak</th>
                        <th class="py-3 px-3.5 text-right">Koli / Berat</th>
                        <th class="py-3 px-3.5 text-right">Volume</th>
                        <th class="py-3 px-3.5 text-center">Status Pabean</th>
                        <th class="py-3 px-3.5 text-center">Status Rak</th>
                        <th class="py-3 px-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium text-gray-800">
                    <?php foreach ($cfs_pallets as $p_idx => $p): 
                        $status_badge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        if ($p['status'] === 'STAGING_OUT') $status_badge = 'bg-blue-50 text-blue-700 border-blue-200';
                        if ($p['status'] === 'HOLD') $status_badge = 'bg-red-50 text-red-700 border-red-200';
                        if ($p['status'] === 'DISPATCHED') $status_badge = 'bg-slate-100 text-slate-500 border-slate-200';

                        $customs_badge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        if ($p['customs_status'] === 'RED_LANE') $customs_badge = 'bg-red-50 text-red-700 border-red-200';
                        if ($p['customs_status'] === 'HOLD') $customs_badge = 'bg-amber-50 text-amber-700 border-amber-200';
                    ?>
                    <tr class="hover:bg-orange-50/40 transition-colors pallet-row"
                        data-sscc="<?= htmlspecialchars($p['pallet_sscc']) ?>"
                        data-aisle="<?= substr($p['rack_location'], 0, 7) ?>" 
                        data-category="<?= $p['category'] ?>"
                        data-search="<?= strtolower($p['pallet_sscc'] . ' ' . $p['commodity_name'] . ' ' . $p['consignee'] . ' ' . ($p['container_origin'] ?? '') . ' ' . $p['rack_location']) ?>">
                        
                        <td class="py-3 px-2.5 text-center font-mono text-gray-400" data-row-num><?= $p_idx + 1 ?></td>
                        <!-- SSCC-18 Pallet Code -->
                        <td class="py-3 px-3.5">
                            <div class="flex items-center space-x-2">
                                <div class="w-7 h-7 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center text-xs flex-shrink-0">
                                    <i class="fa-solid fa-barcode"></i>
                                </div>
                                <div>
                                    <span class="font-mono font-bold text-gray-900 block leading-tight text-[11.5px]"><?= $p['pallet_sscc'] ?></span>
                                    <span class="text-[9.5px] text-gray-400 font-mono">(00) AI GS1-128</span>
                                </div>
                            </div>
                        </td>

                        <!-- Container & Doc -->
                        <td class="py-3 px-3.5 font-mono">
                            <span class="font-bold text-gray-800 text-[11px] block"><?= $p['container_origin'] ?? '-' ?></span>
                            <span class="text-[10px] text-gray-500 block"><?= $p['customs_doc'] ?></span>
                        </td>

                        <!-- Commodity & Consignee -->
                        <td class="py-3 px-3.5 max-w-xs">
                            <span class="font-bold text-gray-900 block truncate" title="<?= htmlspecialchars($p['commodity_name']) ?>"><?= $p['commodity_name'] ?></span>
                            <span class="text-[10.5px] text-gray-500 block truncate"><?= $p['consignee'] ?></span>
                        </td>

                        <!-- Rack Location -->
                        <td class="py-3 px-3.5 text-center">
                            <span class="px-2.5 py-1 bg-slate-100 border border-slate-200 rounded-lg text-slate-800 font-mono font-bold text-[11px]" data-rack-badge>
                                <?= $p['rack_location'] ?>
                            </span>
                        </td>

                        <!-- Packages & Weight -->
                        <td class="py-3 px-3.5 text-right font-mono">
                            <span class="font-bold text-gray-900 block"><?= $p['carton_count'] ?> Koli</span>
                            <span class="text-[10.5px] text-gray-500 block"><?= number_format($p['gross_weight_kg'], 1) ?> kg</span>
                        </td>

                        <!-- CBM -->
                        <td class="py-3 px-3.5 text-right font-mono">
                            <span class="font-bold text-gray-800"><?= number_format($p['cbm'], 2) ?></span>
                            <span class="text-[10px] text-gray-400 block">m³ (CBM)</span>
                        </td>

                        <!-- Customs Status -->
                        <td class="py-3 px-3.5 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold border <?= $customs_badge ?>">
                                <?= $p['customs_status'] ?>
                            </span>
                        </td>

                        <!-- Rack Status -->
                        <td class="py-3 px-3.5 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold border <?= $status_badge ?>" data-status-badge>
                                <?= str_replace('_', ' ', $p['status']) ?>
                            </span>
                        </td>

                        <!-- Action -->
                        <td class="py-3 px-3.5 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                <?php if ($p['status'] === 'IN_RACK' || $p['status'] === 'STAGING_OUT'): ?>
                                    <button type="button" onclick="openRelocatePalletModal('<?= htmlspecialchars($p['pallet_sscc']) ?>', '<?= htmlspecialchars($p['rack_location']) ?>', '<?= htmlspecialchars(addslashes($p['commodity_name'])) ?>')" class="w-7 h-7 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#0170b9] transition flex items-center justify-center text-xs cursor-pointer" title="Pindahkan Slot Rak (Relokasi Real-Time)">
                                        <i class="fa-solid fa-arrows-up-down-left-right"></i>
                                    </button>
                                    <button type="button" onclick="cfsQuickDispatch('<?= htmlspecialchars($p['pallet_sscc']) ?>')" class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition flex items-center justify-center text-xs cursor-pointer" title="Keluarkan / Dispatch Palet Keluar Gudang">
                                        <i class="fa-solid fa-truck-ramp-box"></i>
                                    </button>
                                <?php endif; ?>
                                <button type="button" onclick="showGs1LabelModal('<?= $p['pallet_sscc'] ?>', '<?= htmlspecialchars(addslashes($p['commodity_name'])) ?>', '<?= htmlspecialchars(addslashes($p['consignee'])) ?>', '<?= $p['rack_location'] ?>', '<?= $p['carton_count'] ?>', '<?= $p['gross_weight_kg'] ?>')" class="w-7 h-7 rounded-lg bg-gray-100 hover:bg-orange-50 hover:text-orange-600 text-gray-600 transition flex items-center justify-center text-xs cursor-pointer" title="Cetak Label Barcode GS1-128">
                                    <i class="fa-solid fa-print"></i>
                                </button>
                                <button type="button" onclick="openScannerPage('<?= $p['pallet_sscc'] ?>')" class="w-7 h-7 rounded-lg bg-gray-100 hover:bg-purple-50 hover:text-purple-600 text-gray-600 transition flex items-center justify-center text-xs cursor-pointer" title="Verifikasi dengan Scanner GS1">
                                    <i class="fa-solid fa-qrcode"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 3: DENAH TATA LETAK VISUAL 2D GUDANG CFS 4.000 M² (AISLE A-F) -->
    <!-- ===================================================================== -->
    <div id="tab-denah" class="cfs-tab-content p-5 sm:p-6 hidden">
        <style>
            :root {
                --wh-floor: #cbd5e1;
                --wh-floor-dark: #1e293b;
                --wh-green: #22c55e;
                --wh-amber: #f59e0b;
                --wh-amber-dark: #d97706;
                --wh-red: #ef4444;
                --wh-red-dark: #dc2626;
                --wh-blue: #2563eb;
            }
            .wh-shell {
                max-width: 1450px;
                margin: 0 auto 20px auto;
            }
            .wh-card {
                background: rgba(255,255,255,0.78);
                backdrop-filter: blur(8px);
                border: 1px solid rgba(226,232,240,0.9);
                border-radius: 24px;
                padding: 16px;
                box-shadow: 0 18px 40px rgba(15,23,42,0.08);
            }
            .warehouse-boundary {
                background: var(--wh-floor);
                border: 12px solid var(--wh-floor-dark);
                border-radius: 26px;
                padding: 12px;
                position: relative;
                box-shadow: 0 30px 60px rgba(15, 23, 42, 0.18);
                outline: 22px solid var(--wh-green);
                outline-offset: -22px;
                overflow: hidden;
            }
            .pedestrian-label {
                position: absolute;
                left: 0;
                right: 0;
                bottom: 7px;
                text-align: center;
                color: rgba(255,255,255,0.78);
                font-size: 10px;
                font-weight: 800;
                letter-spacing: 8px;
                text-transform: uppercase;
                pointer-events: none;
                z-index: 2;
            }
            .office-strip {
                margin: 26px 26px 18px 26px;
                background: linear-gradient(135deg, #0170b9, #0284c7, #0ea5e9);
                border-radius: 20px;
                min-height: 100px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 18px 22px;
                color: #fff;
                box-shadow: 0 10px 25px rgba(15, 23, 42, 0.10);
                border: 2px solid rgba(255,255,255,0.18);
                position: relative;
                overflow: hidden;
                z-index: 5;
            }
            .office-strip::before {
                content: "";
                position: absolute;
                inset: 0;
                background:
                    linear-gradient(135deg, rgba(255,255,255,0.12), rgba(255,255,255,0)),
                    repeating-linear-gradient(
                        90deg,
                        rgba(255,255,255,0.03) 0px,
                        rgba(255,255,255,0.03) 1px,
                        transparent 1px,
                        transparent 60px
                    );
                pointer-events: none;
            }
            .office-main {
                display: flex;
                align-items: center;
                gap: 14px;
                position: relative;
                z-index: 1;
            }
            .office-icon {
                width: 52px;
                height: 52px;
                border-radius: 16px;
                background: rgba(255,255,255,0.12);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.35rem;
                box-shadow: inset 0 0 0 1px rgba(255,255,255,0.08);
            }
            .office-title {
                font-size: 1.05rem;
                font-weight: 900;
                letter-spacing: 0.4px;
                text-transform: uppercase;
            }
            .office-subtitle {
                font-size: 0.82rem;
                color: rgba(255,255,255,0.82);
                font-weight: 600;
                margin-top: 2px;
            }
            .office-zones {
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
                justify-content: flex-end;
                position: relative;
                z-index: 1;
            }
            .office-chip {
                background: rgba(255,255,255,0.12);
                border: 1px solid rgba(255,255,255,0.14);
                color: #fff;
                padding: 7px 12px;
                border-radius: 999px;
                font-size: 11px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .flow-strip {
                margin: 0 26px 18px 26px;
                background: rgba(255,255,255,0.55);
                border: 2px dashed rgba(30,41,59,0.22);
                border-radius: 14px;
                padding: 10px 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 12px;
                flex-wrap: wrap;
                position: relative;
                z-index: 3;
            }
            .flow-step {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: rgba(255,255,255,0.85);
                border: 1px solid rgba(148,163,184,0.4);
                color: #0f172a;
                border-radius: 999px;
                padding: 6px 12px;
                font-size: 11.5px;
                font-weight: 800;
                box-shadow: 0 2px 6px rgba(15, 23, 42, 0.06);
            }
            .flow-arrow {
                color: #64748b;
                font-weight: 900;
                font-size: 14px;
            }
            .forklift-corridor {
                margin: 0 26px 18px 26px;
                background:
                    repeating-linear-gradient(
                        90deg,
                        rgba(245,158,11,0.95) 0px,
                        rgba(245,158,11,0.95) 42px,
                        rgba(255,255,255,0.85) 42px,
                        rgba(255,255,255,0.85) 54px
                    );
                border: 4px solid #d97706;
                border-radius: 16px;
                min-height: 56px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: inset 0 2px 10px rgba(0,0,0,0.08), 0 4px 10px rgba(15, 23, 42, 0.08);
                position: relative;
                z-index: 2;
            }
            .forklift-corridor .lane-label {
                background: rgba(17,24,39,0.88);
                color: #fff;
                font-size: 11px;
                font-weight: 900;
                letter-spacing: 1px;
                text-transform: uppercase;
                padding: 6px 14px;
                border-radius: 999px;
                box-shadow: 0 6px 16px rgba(0,0,0,0.18);
            }
            .storage-zone {
                margin: 0 26px 18px 26px;
                position: relative;
                z-index: 3;
            }
            .storage-zone-title {
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 14px;
            }
            .storage-zone-title span {
                background: rgba(255,255,255,0.88);
                color: #0f172a;
                border: 1px solid rgba(148,163,184,0.4);
                border-radius: 999px;
                padding: 7px 16px;
                font-size: 12px;
                font-weight: 900;
                letter-spacing: 1px;
                text-transform: uppercase;
                box-shadow: 0 4px 10px rgba(15, 23, 42, 0.06);
            }
            .storage-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 18px;
            }
            .blok-wrapper {
                background: rgba(255,255,255,0.45);
                padding: 24px 12px 16px 12px;
                border-radius: 18px;
                border: 4px solid #f59e0b;
                position: relative;
                min-height: 220px;
                box-shadow: 0 10px 25px rgba(15, 23, 42, 0.10);
                overflow: hidden;
            }
            .blok-wrapper::before {
                content: "";
                position: absolute;
                inset: 0;
                background:
                    linear-gradient(180deg, rgba(255,255,255,0.18), rgba(255,255,255,0)),
                    repeating-linear-gradient(
                        0deg,
                        rgba(255,255,255,0.08) 0px,
                        rgba(255,255,255,0.08) 1px,
                        transparent 1px,
                        transparent 28px
                    );
                pointer-events: none;
            }
            .blok-title {
                position: absolute;
                top: -10px;
                left: 50%;
                transform: translateX(-50%);
                background: linear-gradient(135deg, #f59e0b, #fbbf24);
                color: #111827;
                padding: 5px 22px;
                border-radius: 999px;
                font-size: 12px;
                font-weight: 900;
                letter-spacing: 1px;
                box-shadow: 0 8px 18px rgba(245,158,11,0.35);
                border: 2px solid rgba(255,255,255,0.55);
                z-index: 2;
            }
            .blok-meta {
                position: absolute;
                right: 10px;
                top: 10px;
                display: flex;
                gap: 6px;
                flex-wrap: wrap;
                justify-content: flex-end;
                z-index: 2;
            }
            .meta-chip {
                background: rgba(15,23,42,0.76);
                color: #fff;
                border-radius: 999px;
                font-size: 9px;
                font-weight: 800;
                letter-spacing: 0.6px;
                text-transform: uppercase;
                padding: 3px 8px;
            }
            .meta-chip.reserve {
                background: rgba(220, 38, 38, 0.9);
            }
            .racks-container {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                justify-content: center;
                position: relative;
                z-index: 1;
            }
            .rack-unit {
                width: 68px;
                min-width: 68px;
                height: 84px;
                background: rgba(255,255,255,0.97);
                border: 2px solid #94a3b8;
                border-radius: 14px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                transition: 0.22s ease;
                cursor: pointer;
                box-shadow: 0 4px 8px rgba(0,0,0,0.05);
                text-decoration: none !important;
                position: relative;
            }
            .rack-unit:hover {
                border-color: #2563eb;
                transform: translateY(-5px);
                z-index: 10;
                box-shadow: 0 14px 26px rgba(37,99,235,0.16);
            }
            .rack-unit.state-empty {
                border-color: #94a3b8;
            }
            .rack-unit.state-partial {
                border-color: #3b82f6;
                box-shadow: 0 0 0 1px rgba(59,130,246,0.10), 0 8px 16px rgba(59,130,246,0.08);
            }
            .rack-unit.state-full {
                border-color: #dc2626;
                background: linear-gradient(180deg, #fff7f7, #ffffff);
                box-shadow: 0 0 0 1px rgba(239,68,68,0.12), 0 10px 18px rgba(239,68,68,0.10);
            }
            .unit-name {
                font-weight: 800;
                font-size: 20px;
                color: #1e293b;
                margin-bottom: 2px;
                line-height: 1;
            }
            .unit-code {
                font-size: 9px;
                font-weight: 800;
                color: #64748b;
                margin-top: 2px;
                letter-spacing: 0.4px;
            }
            .unit-count {
                font-size: 9px;
                font-weight: 800;
                margin-top: 2px;
                letter-spacing: 0.2px;
                color: #475569;
            }
            .rack-unit.state-full .unit-count {
                color: #dc2626;
            }
            .stack-dots {
                display: flex;
                gap: 3px;
                margin-top: 6px;
            }
            .dot {
                width: 7px;
                height: 7px;
                border-radius: 50%;
                background: #e2e8f0;
                border: 1px solid rgba(0,0,0,0.05);
            }
            .dot.fill {
                background: #ef4444;
                box-shadow: 0 0 6px rgba(239, 68, 68, 0.6);
            }
            .forklift-corridor.bottom {
                margin-top: 0;
                margin-bottom: 18px;
            }
            .bottom-ops {
                margin: 0 26px 24px 26px;
                display: grid;
                grid-template-columns: 1fr 1.3fr 1fr;
                gap: 14px;
                position: relative;
                z-index: 4;
            }
            .ops-box-bottom {
                border-radius: 18px;
                min-height: 92px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-direction: column;
                color: #fff;
                font-weight: 800;
                text-transform: uppercase;
                font-size: 12px;
                box-shadow: 0 10px 25px rgba(15, 23, 42, 0.10);
                border: 2px solid rgba(255,255,255,0.2);
                position: relative;
                overflow: hidden;
            }
            .ops-box-bottom::before {
                content: "";
                position: absolute;
                inset: 0;
                background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(255,255,255,0));
                pointer-events: none;
            }
            .ops-box-bottom i {
                margin-bottom: 7px;
                font-size: 1rem;
            }
            .box-inbound-bottom { background: linear-gradient(135deg, #059669, #10b981); }
            .box-pickpack       { background: linear-gradient(135deg, #7c3aed, #db2777); }
            .box-outbound-bottom{ background: linear-gradient(135deg, #0f766e, #14b8a6); }
            .ops-bottom-sub {
                font-size: 10px;
                font-weight: 700;
                opacity: 0.92;
                margin-top: 3px;
                letter-spacing: 0.3px;
            }
            .legend-panel {
                margin-top: 18px;
                display: grid;
                grid-template-columns: 1.5fr 1fr;
                gap: 16px;
            }
            .legend-card, .summary-card {
                background: rgba(255,255,255,0.85);
                border: 1px solid rgba(226,232,240,0.9);
                border-radius: 18px;
                padding: 16px 18px;
                box-shadow: 0 12px 28px rgba(15,23,42,0.07);
            }
            .legend-title {
                font-size: 12px;
                font-weight: 900;
                color: #0f172a;
                letter-spacing: 1px;
                text-transform: uppercase;
                margin-bottom: 12px;
            }
            .legend-list {
                display: flex;
                flex-wrap: wrap;
                gap: 12px 18px;
            }
            .legend-item {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                font-size: 13px;
                color: #334155;
                font-weight: 700;
            }
            .legend-badge {
                width: 14px;
                height: 14px;
                border-radius: 50%;
                flex-shrink: 0;
            }
            .badge-red { background: #ef4444; box-shadow: 0 0 8px rgba(239,68,68,0.45); }
            .badge-gray { background: #cbd5e1; }
            .badge-green { background: #22c55e; }
            .badge-yellow { background: #f59e0b; }
            .badge-blue { background: #2563eb; }
            .badge-dark { background: #0170b9; }
            .summary-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 10px;
            }
            .summary-box {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 12px 10px;
                text-align: center;
            }
            .summary-value {
                font-size: 1.05rem;
                font-weight: 900;
                color: #0f172a;
                line-height: 1;
            }
            .summary-label {
                margin-top: 6px;
                font-size: 10px;
                font-weight: 800;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 0.6px;
            }
            .occupancy-bar-wrap {
                margin-top: 14px;
            }
            .occupancy-bar {
                width: 100%;
                height: 12px;
                border-radius: 999px;
                background: #e2e8f0;
                overflow: hidden;
            }
            .occupancy-fill {
                height: 100%;
                border-radius: 999px;
                background: linear-gradient(90deg, #10b981, #3b82f6, #8b5cf6);
                width: <?= max(0, min(100, $occupancy)); ?>%;
                transition: width 0.35s ease;
            }
            .occupancy-text {
                margin-top: 8px;
                font-size: 12px;
                font-weight: 800;
                color: #334155;
            }
            @media (max-width: 1199.98px) {
                .storage-grid { grid-template-columns: repeat(2, 1fr); }
                .bottom-ops { grid-template-columns: 1fr; }
                .office-strip { flex-direction: column; align-items: flex-start; gap: 14px; }
                .office-zones { justify-content: flex-start; }
            }
            @media (max-width: 991.98px) {
                .legend-panel { grid-template-columns: 1fr; }
                .summary-grid { grid-template-columns: repeat(2, 1fr); }
            }
            @media (max-width: 767.98px) {
                .warehouse-boundary { border-width: 8px; outline-width: 14px; outline-offset: -14px; border-radius: 18px; }
                .office-strip, .flow-strip, .forklift-corridor, .storage-zone, .bottom-ops { margin-left: 16px; margin-right: 16px; }
                .storage-grid { grid-template-columns: 1fr; }
                .racks-container { gap: 10px; }
                .rack-unit { width: 62px; min-width: 62px; height: 82px; }
                .unit-name { font-size: 18px; }
                .summary-grid { grid-template-columns: 1fr; }
                .pedestrian-label { letter-spacing: 4px; font-size: 9px; }
                .office-title { font-size: 0.95rem; }
                .office-subtitle { font-size: 0.8rem; }
            }
        </style>

        <!-- Header & Kontrol Denah Master Plan Arsitektur & Sipil (Real-Time SSoT) -->
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-3 mb-4 pb-3 border-b border-gray-200">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-blue-50 text-[#0170b9] border border-blue-200">
                        DWG: CIDP-CFS-STR-01 &bull; REV. 2.4
                    </span>
                    <span id="cfsLiveStreamBadge" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                        <span id="cfsLiveStreamText">Live SSE Sync (SSoT)</span>
                    </span>
                    <h3 class="text-sm sm:text-base font-bold text-gray-800 uppercase tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-warehouse text-[#0170b9]"></i>
                        Master Layout Gudang CFS (4.000 m²)
                    </h3>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">
                    50 &times; 80 m &bull; 6 Blok &times; 6 Sektor (A–F) &times; 4 Level Rak &bull; Klik sektor untuk inspeksi R1–R4, putaway cepat, atau dispatch
                </p>
            </div>

            <!-- Pintasan Operasional Gudang & Digital Twin -->
            <div class="flex items-center gap-2 flex-wrap">
                <a href="master_rak.php" target="_blank" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200">
                    <i class="fa-solid fa-table-list text-[#0170b9]"></i>
                    <span>Master Rak (144 Slot)</span>
                </a>
                <a href="dashboard.php?page=twin&view=cfs" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-[#0170b9] hover:bg-blue-700 text-white shadow-xs">
                    <i class="fa-solid fa-cubes"></i>
                    <span>Pantau 3D Gudang di Digital Twin</span>
                </a>
            </div>
        </div>

        <!-- Live Update Toast Banner inside Warehouse Layout -->
        <div id="cfsLayoutToast" class="hidden mb-3 p-3 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 text-xs flex items-center justify-between gap-2 animate-fadeIn">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-bolt text-orange-500"></i>
                <span id="cfsLayoutToastMsg" class="font-semibold">Mutasi slot rak gudang terdeteksi real-time.</span>
            </div>
            <button type="button" onclick="document.getElementById('cfsLayoutToast').classList.add('hidden')" class="text-emerald-700 hover:text-emerald-950 font-bold text-xs cursor-pointer">&times;</button>
        </div>

        <!-- ===================================================================== -->
        <!-- VIEW 1: WAREHOUSE MASTER LAYOUT FINAL PRO PLUS (6 BLOK INTERAKTIF)     -->
        <!-- ===================================================================== -->
        <div id="warehouseGridView" class="block space-y-4">
            <div class="wh-shell">
                <div class="wh-card">
                    <div class="warehouse-boundary">
                        <div class="pedestrian-label">Pedestrian Safety Zone - Jalur Hijau K3</div>

                        <!-- FULL TOP OFFICE -->
                        <div class="office-strip">
                            <div class="office-main">
                                <div class="office-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div>
                                    <div class="office-title">Office / Admin / Control Room</div>
                                    <div class="office-subtitle">Kontrol operasional &amp; administrasi gudang</div>
                                </div>
                            </div>

                            <div class="office-zones">
                                <div class="office-chip">Admin Desk</div>
                                <div class="office-chip">Supervisor</div>
                                <div class="office-chip">Control Panel</div>
                                <div class="office-chip">Document Check</div>
                                <div class="office-chip">CCTV Monitor</div>
                            </div>
                        </div>

                        <!-- FLOW STRIP -->
                        <div class="flow-strip">
                            <div class="flow-step"><i class="fas fa-arrow-circle-down text-emerald-600"></i> INBOUND</div>
                            <div class="flow-arrow">&rarr;</div>
                            <div class="flow-step"><i class="fas fa-search text-cyan-600"></i> CHECK / RECEIVE</div>
                            <div class="flow-arrow">&rarr;</div>
                            <div class="flow-step"><i class="fas fa-warehouse text-amber-500"></i> STORAGE</div>
                            <div class="flow-arrow">&rarr;</div>
                            <div class="flow-step"><i class="fas fa-hand-paper text-purple-600"></i> PICK + PACK</div>
                            <div class="flow-arrow">&rarr;</div>
                            <div class="flow-step"><i class="fas fa-truck text-emerald-600"></i> OUTBOUND</div>
                        </div>

                        <!-- MAIN FORKLIFT CORRIDOR -->
                        <div class="forklift-corridor">
                            <div class="lane-label">Forklift Corridor / Jalur Forklift (FL-01, FL-03, FL-04)</div>
                        </div>

                        <!-- STORAGE 6 BLOK -->
                        <div class="storage-zone">
                            <div class="storage-zone-title">
                                <span>Storage Zone - 6 Main Blocks (Klik Sektor A–F untuk Inspeksi &amp; Operasi Rak Live)</span>
                            </div>

                            <div class="storage-grid" id="cfsMasterStorageGrid">
                                <?php foreach ($blok_list as $b): ?>
                                    <?php $isReserveBlock = ($b === '6'); ?>
                                    <div class="blok-wrapper" data-blok-wrap="<?= htmlspecialchars($b); ?>">
                                        <div class="blok-title">BLOK <?= htmlspecialchars($b); ?></div>

                                        <div class="blok-meta">
                                            <?php if ($isReserveBlock): ?>
                                                <div class="meta-chip reserve">Expansion / Staging</div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="racks-container">
                                            <?php 
                                            $sektors = isset($sektor_list_per_blok[$b]) ? $sektor_list_per_blok[$b] : [];
                                            foreach ($sektors as $s):
                                                $levels = isset($map_status[$b][$s]) ? $map_status[$b][$s] : [];
                                                $filledCount = 0;
                                                $maxRak = count($levels);
                                                
                                                foreach ($levels as $r => $isFilled) {
                                                    if ($isFilled) $filledCount++;
                                                }

                                                $stateClass = 'state-empty';
                                                if ($maxRak > 0) {
                                                    if ($filledCount > 0 && $filledCount < $maxRak) {
                                                        $stateClass = 'state-partial';
                                                    } elseif ($filledCount >= $maxRak) {
                                                        $stateClass = 'state-full';
                                                    }
                                                }

                                                $searchCode = $s . $b;
                                                $titleText = "Blok {$b} - Sektor {$s} ({$filledCount}/{$maxRak} terisi) • Klik untuk inspeksi R1–R{$maxRak}";
                                            ?>
                                                <a
                                                    id="cfsRackUnit-<?= htmlspecialchars($b); ?>-<?= htmlspecialchars($s); ?>"
                                                    data-blok="<?= htmlspecialchars($b); ?>"
                                                    data-sektor="<?= htmlspecialchars($s); ?>"
                                                    href="master_rak.php?search=<?= urlencode($searchCode); ?>"
                                                    onclick="openCfsSectorModal('<?= htmlspecialchars($b); ?>', '<?= htmlspecialchars($s); ?>'); return false;"
                                                    class="rack-unit <?= $stateClass; ?>"
                                                    title="<?= htmlspecialchars($titleText); ?>"
                                                >
                                                    <span class="unit-name"><?= htmlspecialchars($s); ?></span>

                                                    <div class="stack-dots" id="cfsRackDots-<?= htmlspecialchars($b); ?>-<?= htmlspecialchars($s); ?>">
                                                        <?php foreach ($levels as $r => $isFilled): ?>
                                                            <div class="dot <?= ($isFilled) ? 'fill' : ''; ?>" data-rak="<?= (int)$r ?>"></div>
                                                        <?php endforeach; ?>
                                                    </div>

                                                    <div class="unit-code">R1 - R<?= $maxRak; ?></div>
                                                    <div class="unit-count" id="cfsRackCount-<?= htmlspecialchars($b); ?>-<?= htmlspecialchars($s); ?>"><?= $filledCount; ?>/<?= $maxRak; ?></div>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- LOWER FORKLIFT STRIP -->
                        <div class="forklift-corridor bottom">
                            <div class="lane-label">Forklift Movement / Jalur Distribusi ke Area Proses</div>
                        </div>

                        <!-- BOTTOM OPS -->
                        <div class="bottom-ops">
                            <div class="ops-box-bottom box-inbound-bottom">
                                <i class="fas fa-truck-loading"></i>
                                <div>Inbound Area</div>
                                <div class="ops-bottom-sub">Receiving &amp; Unloading</div>
                            </div>

                            <div class="ops-box-bottom box-pickpack">
                                <i class="fas fa-box-open"></i>
                                <div>Picking + Packing Zone</div>
                                <div class="ops-bottom-sub">Ambil Barang, Sortir, Packing</div>
                            </div>

                            <div class="ops-box-bottom box-outbound-bottom cursor-pointer hover:opacity-95 transition" onclick="switchCfsTab('tab-rampa')" title="Klik untuk menuju Live Rampa D1-D5 (Loading Dock)">
                                <i class="fas fa-shipping-fast"></i>
                                <div>Outbound / Loading Bay</div>
                                <div class="ops-bottom-sub flex items-center gap-1.5 justify-center">
                                    <span>Dispatch &amp; Loading</span>
                                    <span class="bg-white/20 px-1.5 py-0.5 rounded text-[9px] font-mono">&rarr; Rampa D1-D5</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- LEGEND & REAL-TIME SUMMARY -->
                    <div class="legend-panel">
                        <div class="legend-card">
                            <div class="legend-title">Keterangan Layout &amp; Status Rak (Real-Time SSoT)</div>
                            <div class="legend-list">
                                <div class="legend-item">
                                    <span class="legend-badge badge-red"></span>
                                    <span>Titik Merah = Slot Terisi</span>
                                </div>

                                <div class="legend-item">
                                    <span class="legend-badge badge-gray"></span>
                                    <span>Titik Abu = Slot Kosong</span>
                                </div>

                                <div class="legend-item">
                                    <span class="legend-badge badge-blue"></span>
                                    <span>Border Biru = Sektor Terisi Sebagian</span>
                                </div>

                                <div class="legend-item">
                                    <span class="legend-badge badge-red"></span>
                                    <span>Border Merah = Sektor Full</span>
                                </div>

                                <div class="legend-item">
                                    <span class="legend-badge badge-green"></span>
                                    <span>Jalur Pedestrian K3</span>
                                </div>

                                <div class="legend-item">
                                    <span class="legend-badge badge-yellow"></span>
                                    <span>Jalur Forklift</span>
                                </div>

                                <div class="legend-item">
                                    <span class="legend-badge badge-dark"></span>
                                    <span>Office / Control Room</span>
                                </div>
                            </div>
                        </div>

                        <div class="summary-card">
                            <div class="legend-title">Ringkasan Kapasitas Real-Time</div>

                            <div class="summary-grid">
                                <div class="summary-box">
                                    <div class="summary-value" id="cfsSumBlok"><?= $totalBlok; ?></div>
                                    <div class="summary-label">Blok</div>
                                </div>

                                <div class="summary-box">
                                    <div class="summary-value" id="cfsSumSektor"><?= $totalSektor; ?></div>
                                    <div class="summary-label">Sektor</div>
                                </div>

                                <div class="summary-box">
                                    <div class="summary-value text-rose-600" id="cfsSumTerisi"><?= $filledSlot; ?></div>
                                    <div class="summary-label">Terisi</div>
                                </div>

                                <div class="summary-box">
                                    <div class="summary-value text-emerald-700" id="cfsSumKosong"><?= $emptySlot; ?></div>
                                    <div class="summary-label">Kosong</div>
                                </div>
                            </div>

                            <div class="occupancy-bar-wrap">
                                <div class="occupancy-bar">
                                    <div class="occupancy-fill" id="cfsOccBarFill"></div>
                                </div>
                                <div class="occupancy-text" id="cfsOccText">
                                    Occupancy Rate: <strong><?= number_format($occupancy, 1); ?>%</strong> dari total <?= $totalSlot; ?> slot level
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /warehouseGridView -->

        <!-- KONSOL UJI SENSOR GUDANG CFS -->
        <div class="mt-5 bg-white rounded-2xl p-4 border border-gray-200 text-gray-800 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 gap-2">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#0170b9] border border-blue-200 flex items-center justify-center text-sm shrink-0">
                        <i class="fa-solid fa-satellite-dish"></i>
                    </div>
                    <h4 class="text-sm font-bold text-gray-900">Pemicu Sensor Gudang CFS</h4>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Live MySQL</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mt-3">
                <div class="bg-slate-50/80 rounded-xl p-3 border border-slate-200/80 flex flex-col justify-between gap-2.5 hover:border-amber-400 hover:bg-white transition">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[10px] font-mono text-amber-700 font-bold">HW-06 • Rampa</span>
                            <span class="text-[9px] text-slate-500">Ultrasonik</span>
                        </div>
                        <h5 class="text-xs font-bold text-gray-900">Truk Merapat Rampa D3</h5>
                    </div>
                    <button onclick="openSensorTriggerModal('dock', 'D3')" class="w-full py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-lg transition cursor-pointer">
                        <i class="fa-solid fa-bolt mr-1"></i> Trigger Rampa D3
                    </button>
                </div>

                <div class="bg-slate-50/80 rounded-xl p-3 border border-slate-200/80 flex flex-col justify-between gap-2.5 hover:border-blue-400 hover:bg-white transition">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[10px] font-mono text-[#0170b9] font-bold">HW-21 • Scale 3T</span>
                            <span class="text-[9px] text-slate-500">Loadcell</span>
                        </div>
                        <h5 class="text-xs font-bold text-gray-900">Timbang &amp; Dimensi Palet</h5>
                    </div>
                    <button onclick="openSensorTriggerModal('scale')" class="w-full py-1.5 bg-[#0170b9] hover:bg-[#004b87] text-white font-bold text-xs rounded-lg transition cursor-pointer">
                        <i class="fa-solid fa-weight-scale mr-1"></i> Trigger Timbang
                    </button>
                </div>

                <div class="bg-slate-50/80 rounded-xl p-3 border border-slate-200/80 flex flex-col justify-between gap-2.5 hover:border-purple-400 hover:bg-white transition">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[10px] font-mono text-purple-700 font-bold">HW-19 • Zebra</span>
                            <span class="text-[9px] text-slate-500">Barcode</span>
                        </div>
                        <h5 class="text-xs font-bold text-gray-900">Scan Label Palet SSCC</h5>
                    </div>
                    <button onclick="openSensorTriggerModal('scanner')" class="w-full py-1.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-lg transition cursor-pointer">
                        <i class="fa-solid fa-barcode mr-1"></i> Pindai Barcode
                    </button>
                </div>

                <div class="bg-slate-50/80 rounded-xl p-3 border border-slate-200/80 flex flex-col justify-between gap-2.5 hover:border-emerald-400 hover:bg-white transition">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[10px] font-mono text-emerald-700 font-bold">ENV-01 • SHT40</span>
                            <span class="text-[9px] text-slate-500">LoRaWAN</span>
                        </div>
                        <h5 class="text-xs font-bold text-gray-900">Suhu &amp; Kelembaban</h5>
                    </div>
                    <button onclick="openSensorTriggerModal('env')" class="w-full py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition cursor-pointer">
                        <i class="fa-solid fa-tower-broadcast mr-1"></i> Ping Telemetri
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 4: JOB ORDER & TALLY MASTER CONSOLE -->
    <!-- ===================================================================== -->
    <div id="tab-joborder" class="cfs-tab-content p-5 sm:p-6 hidden">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-list text-orange-600"></i>
                    Daftar Job Order Bongkar/Muat CFS
                </h3>
            </div>
            <div class="flex items-center gap-2">
                <select id="cfsJobsLimitSelect" data-limit-target="#cfsJobsTable" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700">
                    <option value="10" selected>Tampil: 10</option>
                    <option value="25">Tampil: 25</option>
                    <option value="50">Tampil: 50</option>
                    <option value="all">Semua</option>
                </select>
                <button onclick="openModal('modalNewJobOrder')" class="px-3.5 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>Terbitkan Job Order Baru</span>
                </button>
            </div>
        </div>

        <!-- Tabel Riwayat Job Order -->
        <div class="overflow-x-auto border border-gray-200 rounded-2xl shadow-2xs">
            <table class="w-full text-left border-collapse text-xs" id="cfsJobsTable">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider text-[10.5px]">
                        <th class="py-3 px-2.5 text-center w-10">No</th>
                        <th class="py-3 px-3.5">Nomor Job Order</th>
                        <th class="py-3 px-3.5">Tipe &amp; Rampa</th>
                        <th class="py-3 px-3.5">No. Kontainer</th>
                        <th class="py-3 px-3.5">Shipper / Consignee</th>
                        <th class="py-3 px-3.5">No. B/L</th>
                        <th class="py-3 px-3.5 text-right">Koli / Berat / CBM</th>
                        <th class="py-3 px-3.5 text-center">Status</th>
                        <th class="py-3 px-3.5">Tallyman / Waktu</th>
                        <th class="py-3 px-3.5 text-center">Aksi Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium text-gray-800">
                    <?php foreach ($cfs_jobs as $j_idx => $job): 
                        $is_strip = ($job['job_type'] === 'STRIPPING');
                        $job_badge = $is_strip ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-blue-50 text-blue-700 border-blue-200';
                        $status_badge = ($job['status'] === 'COMPLETED') ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-blue-50 text-[#0170b9] border-blue-200';
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-2.5 text-center font-mono text-gray-400" data-row-num><?= $j_idx + 1 ?></td>
                        <td class="py-3 px-3.5 font-mono font-bold text-gray-900">
                            <?= $job['job_number'] ?>
                        </td>
                        <td class="py-3 px-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold border <?= $job_badge ?>">
                                <?= $job['job_type'] ?>
                            </span>
                            <span class="ml-1 text-[11px] font-bold text-gray-700 font-mono">Dock <?= $job['dock_ramp'] ?></span>
                        </td>
                        <td class="py-3 px-3.5 font-mono font-bold text-[#0170b9]">
                            <?= $job['container_number'] ?>
                        </td>
                        <td class="py-3 px-3.5 max-w-xs truncate" title="<?= htmlspecialchars($job['shipper_consignee']) ?>">
                            <?= $job['shipper_consignee'] ?>
                        </td>
                        <td class="py-3 px-3.5 font-mono text-gray-600">
                            <?= $job['bl_number'] ?>
                        </td>
                        <td class="py-3 px-3.5 text-right font-mono">
                            <span class="font-bold text-gray-900 block"><?= $job['total_packages'] ?> Palet</span>
                            <span class="text-[10px] text-gray-500 block"><?= number_format($job['total_weight_kg'], 0) ?> kg &bull; <?= number_format($job['total_cbm'], 1) ?> CBM</span>
                        </td>
                        <td class="py-3 px-3.5 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold border <?= $status_badge ?>">
                                <?= $job['status'] ?>
                            </span>
                        </td>
                        <td class="py-3 px-3.5 text-[11px] text-gray-600">
                            <span class="font-semibold block text-gray-800"><?= $job['operator_name'] ?></span>
                            <span class="text-[10px] text-gray-400 block font-mono"><?= date('d M H:i', strtotime($job['start_time'])) ?></span>
                        </td>
                        <td class="py-3 px-3.5 text-center">
                            <div class="inline-flex items-center space-x-1">
                                <button onclick="printTallySheet('<?= $job['job_number'] ?>', '<?= $job['container_number'] ?>', '<?= $job['job_type'] ?>', '<?= htmlspecialchars(addslashes($job['shipper_consignee'])) ?>', '<?= $job['dock_ramp'] ?>', '<?= $job['total_packages'] ?>', '<?= $job['total_weight_kg'] ?>', '<?= htmlspecialchars(addslashes($job['tally_notes'] ?? '')) ?>')" class="px-2.5 py-1 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-[10.5px] font-bold rounded-lg shadow-2xs transition inline-flex items-center space-x-1" title="Cetak Berita Acara & Tally Sheet">
                                    <i class="fa-solid fa-file-invoice text-[#0170b9]"></i>
                                    <span>Tally</span>
                                </button>
                                <a href="dashboard.php?page=kontainer&search=<?= urlencode($job['container_number']) ?>" class="w-7 h-7 bg-blue-50 text-[#0170b9] hover:bg-[#0170b9] hover:text-white rounded-lg flex items-center justify-center transition shadow-2xs" title="Lacak Kontainer di Tracking Box">
                                    <i class="fa-solid fa-boxes-stacked text-xs"></i>
                                </a>
                                <a href="dashboard.php?page=twin&view=cfs" class="w-7 h-7 bg-amber-50 text-amber-700 hover:bg-amber-600 hover:text-white rounded-lg flex items-center justify-center transition shadow-2xs" title="Lihat 3D Gudang CFS">
                                    <i class="fa-solid fa-cube text-xs"></i>
                                </a>
                                <a href="dashboard.php?page=billing&search=<?= urlencode($job['container_number']) ?>" class="w-7 h-7 bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white rounded-lg flex items-center justify-center transition shadow-2xs" title="Periksa Faktur Billing CFS">
                                    <i class="fa-solid fa-file-invoice-dollar text-xs"></i>
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

<!-- ========================================================================= -->
<!-- MODAL 1: FORM BUAT JOB ORDER CFS BARU (STRIPPING / STUFFING) -->
<!-- ========================================================================= -->
<div id="modalNewJobOrder" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden animate-fadeIn">
    <div class="bg-white rounded-3xl shadow-2xl max-w-xl w-full overflow-hidden border border-gray-100 transform transition-all text-xs">
        <div class="bg-gradient-to-r from-[#0170b9] to-[#0284c7] p-5 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-lg shadow-inner">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold leading-tight">Terbitkan Job Order Rampa CFS</h3>
                    <p class="text-[11px] text-blue-100">Alokasikan Rampa D1–D5 &bull; Stripping FCL atau Stuffing Konsolidasi</p>
                </div>
            </div>
            <button onclick="closeModal('modalNewJobOrder')" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="cfs_action" value="create_job">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Tipe Operasi CFS *</label>
                    <select name="job_type" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-orange-500">
                        <option value="STRIPPING">STRIPPING (Bongkar FCL ke Palet LCL)</option>
                        <option value="STUFFING">STUFFING (Konsolidasi Ekspor LCL ke FCL)</option>
                        <option value="CROSS_DOCK">CROSS-DOCK (Direct Transfer Truk-ke-Truk)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Pilih Rampa Dock Leveler *</label>
                    <select name="dock_ramp" id="modalDockSelect" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-orange-500">
                        <option value="D1">Rampa D1 (Hydraulic 15T)</option>
                        <option value="D2">Rampa D2 (Hydraulic 15T)</option>
                        <option value="D3" selected>Rampa D3 (Hydraulic 15T - Siap)</option>
                        <option value="D4">Rampa D4 (Hydraulic 15T)</option>
                        <option value="D5">Rampa D5 (Hydraulic 15T)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Nomor Peti Kemas / Kontainer *</label>
                    <input type="text" name="container_number" required placeholder="Contoh: MSKU8821940" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono uppercase font-bold focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Nomor B/L (Bill of Lading) *</label>
                    <input type="text" name="bl_number" required placeholder="Contoh: BL-MAEU-982104" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono uppercase focus:ring-2 focus:ring-orange-500">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-700 mb-1">Shipper / Consignee (Perusahaan Pemilik Kargo) *</label>
                <input type="text" name="shipper_consignee" required placeholder="Contoh: PT Astra Honda Motor" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500">
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Estimasi Palet/Koli</label>
                    <input type="number" name="total_packages" value="40" min="1" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Total Berat (kg)</label>
                    <input type="number" step="0.01" name="total_weight_kg" value="18500" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Volume (CBM)</label>
                    <input type="number" step="0.1" name="total_cbm" value="52.4" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono focus:ring-2 focus:ring-orange-500">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-700 mb-1">Catatan Khusus Tally &bull; Verifikasi Segel Fisik</label>
                <textarea name="tally_notes" rows="2" placeholder="Catat nomor segel pelayaran, kondisi ganjal roda wheel chock, & instruksi penempatan rak..." class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-orange-500"></textarea>
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeModal('modalNewJobOrder')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold rounded-xl shadow-md shadow-orange-500/20 transition cursor-pointer">
                    Konfirmasi &amp; Buka Rampa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: FORM REGISTRASI PALET BARU (GS1 SSCC) -->
<!-- ========================================================================= -->
<div id="modalAddPallet" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden animate-fadeIn">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-gray-100 transform transition-all text-xs">
        <div class="bg-gradient-to-r from-[#0170b9] to-[#0284c7] p-5 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-lg shadow-inner">
                    <i class="fa-solid fa-pallet"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold leading-tight">Registrasi Palet Kargo LCL</h3>
                    <p class="text-[11px] text-blue-100">Penomoran Otomatis GS1 SSCC-18 &amp; Alokasi Rak Aisle A–F</p>
                </div>
            </div>
            <button onclick="closeModal('modalAddPallet')" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" id="modalAddPalletForm" class="p-6 space-y-4">
            <input type="hidden" name="cfs_action" value="add_pallet">
            <div id="addPalletLiveHint" class="hidden p-2.5 rounded-xl bg-blue-50 border border-blue-200 text-[#0170b9] text-[11px] font-semibold flex items-center gap-2">
                <i class="fa-solid fa-bolt text-orange-500"></i>
                <span id="addPalletLiveHintText">Mode Real-Time Layout: Palet langsung mengisi slot tanpa reload halaman.</span>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-700 mb-1">Nomor Palet SSCC-18 (GS1 Logistic Label)</label>
                <input type="text" name="pallet_sscc" readonly value="0018991200109<?= rand(1000000, 9999999) ?>" class="w-full px-3 py-2 bg-gray-100 border border-gray-200 rounded-xl font-mono font-bold text-[#0170b9]">
                <span class="text-[9.5px] text-gray-400 mt-0.5 block font-mono">Standar Global GS1-128 AI (00) Serial Shipping Container Code</span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Ex-Kontainer Asal</label>
                    <input type="text" name="container_origin" value="MSKU8821940" placeholder="Nomor Kontainer" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono uppercase focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Kategori Kargo *</label>
                    <select name="category" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-orange-500">
                        <option value="automotive">Otomotif &amp; Spareparts</option>
                        <option value="electronics">Elektronik &amp; Komponen</option>
                        <option value="machinery">Mesin &amp; Alat Berat</option>
                        <option value="fmcg">FMCG &amp; Makanan Kering</option>
                        <option value="textile">Tekstil &amp; Garment</option>
                        <option value="general">Kargo Umum Lainnya</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-700 mb-1">Nama / Deskripsi Komoditas *</label>
                <input type="text" name="commodity_name" required placeholder="Contoh: Modul Sensor ABS Otomotif" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-700 mb-1">Penerima Barang (Consignee) *</label>
                <input type="text" name="consignee" required placeholder="Contoh: PT Denso Indonesia" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500">
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Jumlah Koli</label>
                    <input type="number" name="carton_count" value="20" min="1" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Berat Kotor (kg)</label>
                    <input type="number" step="0.1" name="gross_weight_kg" value="450.0" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Volume (CBM)</label>
                    <input type="number" step="0.01" name="cbm" value="1.25" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono focus:ring-2 focus:ring-orange-500">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-700 mb-1">Alokasi Posisi Rak Gudang *</label>
                <select name="rack_location" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono text-xs font-bold focus:ring-2 focus:ring-orange-500">
                    <option value="AISLE-A-02-L1">AISLE-A-02-L1 (Mesin Berat)</option>
                    <option value="AISLE-B-05-L2">AISLE-B-05-L2 (Elektronik)</option>
                    <option value="AISLE-C-04-L1">AISLE-C-04-L1 (FMCG Food)</option>
                    <option value="AISLE-D-06-L2">AISLE-D-06-L2 (Tekstil Garment)</option>
                    <option value="AISLE-E-03-L1" selected>AISLE-E-03-L1 (Otomotif CKD)</option>
                    <option value="AISLE-F-02-L1">AISLE-F-02-L1 (Staging Pabean)</option>
                </select>
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeModal('modalAddPallet')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md shadow-blue-600/20 transition">
                    Simpan &amp; Cetak Barcode GS1
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: MODAL CETAK LABEL PALET LOGISTIK GS1-128 / SSCC-18 -->
<!-- ========================================================================= -->
<div id="modalGs1Label" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden animate-fadeIn">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden border border-gray-100 transform transition-all text-xs">
        <div class="bg-[#0170b9] p-4 text-white flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-barcode text-orange-400 text-base"></i>
                <span class="font-bold text-sm">GS1 Standard Logistic Pallet Label</span>
            </div>
            <button onclick="closeModal('modalGs1Label')" class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Render GS1 Label Standard -->
        <div class="p-6 bg-gray-50" id="printableGs1Label">
            <div class="bg-white border-2 border-black p-4 rounded-xl text-black font-sans shadow-sm">
                <!-- Header Label -->
                <div class="border-b-2 border-black pb-2 mb-2 flex items-start justify-between">
                    <div>
                        <h4 class="font-extrabold text-sm tracking-tight">CONCLUSION INTERMODAL DRY PORT</h4>
                        <p class="text-[9px] uppercase font-bold text-gray-600">Container Freight Station (CFS) Hub 35 Ha</p>
                    </div>
                    <div class="text-right">
                        <span class="text-[9px] font-bold block">SSCC-18</span>
                        <span class="text-[8px] text-gray-500">ISO 15459</span>
                    </div>
                </div>

                <!-- Consignee Box -->
                <div class="border-b-2 border-black pb-2 mb-2">
                    <span class="text-[8.5px] uppercase font-bold text-gray-500 block">Ship To / Consignee:</span>
                    <span class="font-extrabold text-xs block text-gray-900" id="lblConsignee">PT Astra Honda Motor</span>
                    <span class="text-[9.5px] text-gray-700 block">Kawasan Industri MM2100 Cikarang Barat</span>
                </div>

                <!-- Commodity & Details -->
                <div class="grid grid-cols-2 gap-2 border-b-2 border-black pb-2 mb-3 text-[10px]">
                    <div>
                        <span class="text-[8px] uppercase font-bold text-gray-500 block">Deskripsi Barang:</span>
                        <span class="font-bold text-gray-900 block" id="lblCommodity">Komponen Transmisi Matic</span>
                    </div>
                    <div>
                        <span class="text-[8px] uppercase font-bold text-gray-500 block">Lokasi Rak Gudang:</span>
                        <span class="font-mono font-extrabold text-[#0170b9] text-xs block" id="lblRack">AISLE-E-02-L1</span>
                    </div>
                    <div>
                        <span class="text-[8px] uppercase font-bold text-gray-500 block">Jumlah Koli:</span>
                        <span class="font-bold text-gray-900 block" id="lblCartons">24 Cartons</span>
                    </div>
                    <div>
                        <span class="text-[8px] uppercase font-bold text-gray-500 block">Berat Kotor:</span>
                        <span class="font-bold text-gray-900 block" id="lblWeight">480.0 kg</span>
                    </div>
                </div>

                <!-- Barcode SVG Render -->
                <div class="text-center py-2">
                    <svg viewBox="0 0 260 70" class="w-full h-16 mx-auto">
                        <!-- Barcode Simulation Pattern -->
                        <rect x="10" y="5" width="2" height="50" fill="#000"/>
                        <rect x="14" y="5" width="4" height="50" fill="#000"/>
                        <rect x="21" y="5" width="2" height="50" fill="#000"/>
                        <rect x="25" y="5" width="6" height="50" fill="#000"/>
                        <rect x="34" y="5" width="2" height="50" fill="#000"/>
                        <rect x="38" y="5" width="4" height="50" fill="#000"/>
                        <rect x="45" y="5" width="6" height="50" fill="#000"/>
                        <rect x="54" y="5" width="2" height="50" fill="#000"/>
                        <rect x="60" y="5" width="8" height="50" fill="#000"/>
                        <rect x="71" y="5" width="3" height="50" fill="#000"/>
                        <rect x="77" y="5" width="5" height="50" fill="#000"/>
                        <rect x="85" y="5" width="2" height="50" fill="#000"/>
                        <rect x="90" y="5" width="7" height="50" fill="#000"/>
                        <rect x="100" y="5" width="4" height="50" fill="#000"/>
                        <rect x="107" y="5" width="2" height="50" fill="#000"/>
                        <rect x="112" y="5" width="6" height="50" fill="#000"/>
                        <rect x="121" y="5" width="3" height="50" fill="#000"/>
                        <rect x="127" y="5" width="5" height="50" fill="#000"/>
                        <rect x="135" y="5" width="2" height="50" fill="#000"/>
                        <rect x="140" y="5" width="6" height="50" fill="#000"/>
                        <rect x="149" y="5" width="4" height="50" fill="#000"/>
                        <rect x="156" y="5" width="2" height="50" fill="#000"/>
                        <rect x="161" y="5" width="7" height="50" fill="#000"/>
                        <rect x="171" y="5" width="3" height="50" fill="#000"/>
                        <rect x="177" y="5" width="5" height="50" fill="#000"/>
                        <rect x="185" y="5" width="2" height="50" fill="#000"/>
                        <rect x="190" y="5" width="8" height="50" fill="#000"/>
                        <rect x="201" y="5" width="3" height="50" fill="#000"/>
                        <rect x="207" y="5" width="5" height="50" fill="#000"/>
                        <rect x="215" y="5" width="2" height="50" fill="#000"/>
                        <rect x="220" y="5" width="6" height="50" fill="#000"/>
                        <rect x="229" y="5" width="4" height="50" fill="#000"/>
                        <rect x="236" y="5" width="2" height="50" fill="#000"/>
                        <rect x="241" y="5" width="5" height="50" fill="#000"/>
                    </svg>
                    <!-- AI (00) SSCC Human Readable Number -->
                    <div class="font-mono font-bold tracking-widest text-xs mt-1" id="lblSsccHuman">(00) 00189912001092837101</div>
                </div>
            </div>
        </div>

        <div class="p-4 bg-white border-t border-gray-100 flex items-center justify-between">
            <span class="text-[10px] text-gray-500">Cetak ke Thermal Printer 100x150mm</span>
            <div class="flex items-center space-x-2">
                <button onclick="closeModal('modalGs1Label')" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg transition">
                    Tutup
                </button>
                <button onclick="printCurrentGs1Label()" class="px-4 py-1.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold rounded-lg shadow-sm shadow-orange-500/20 transition flex items-center space-x-1 cursor-pointer">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak Label GS1 (A4/Thermal)</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 4: MODAL UPDATE TALLY HARIAN -->
<!-- ========================================================================= -->
<div id="modalUpdateTally" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden animate-fadeIn">
    <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full overflow-hidden border border-gray-100 transform transition-all text-xs">
        <div class="bg-amber-600 p-4 text-white flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-clipboard-check text-lg"></i>
                <span class="font-bold text-sm">Update Tally Progres Rampa</span>
            </div>
            <button onclick="closeModal('modalUpdateTally')" class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" class="p-5 space-y-3">
            <input type="hidden" name="cfs_action" value="update_tally">
            <input type="hidden" name="job_id" id="tallyJobId" value="1">

            <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                <div class="text-[10px] text-gray-400 uppercase font-mono">Target Rampa &amp; Peti Kemas</div>
                <div class="font-extrabold text-gray-900 text-sm mt-0.5" id="tallyTargetDisplay">Rampa D1 &bull; MSKU8821940</div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-700 mb-1">Tambah Jumlah Palet yang Selesai di-Tally</label>
                <div class="flex items-center space-x-2">
                    <input type="number" name="add_pallets" value="5" min="1" max="100" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono font-bold text-sm focus:ring-2 focus:ring-amber-500">
                    <span class="text-xs font-bold text-gray-500">Palet</span>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-700 mb-1">Catatan Kondisi Fisik Kargo</label>
                <textarea name="tally_notes" rows="2" placeholder="Kemasan baik, tidak ada kardus basah/rusak, segel utuh..." class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-amber-500"></textarea>
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeModal('modalUpdateTally')" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg shadow-sm transition">
                    Simpan Tally
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 4B: INSPEKTOR SEKTOR RAK GUDANG CFS REAL-TIME (R1–R4 SSoT)         -->
<!-- ========================================================================= -->
<div id="modalCfsSectorInspector" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden animate-fadeIn">
    <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full overflow-hidden border border-gray-100 transform transition-all text-xs">
        <div class="bg-gradient-to-r from-[#0170b9] to-slate-900 p-5 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-sm border border-white/20 flex items-center justify-center text-lg shadow-inner">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-base font-extrabold leading-tight" id="inspSectorTitle">BLOK 1 • SEKTOR A</h3>
                        <span id="inspSectorBadge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white font-mono">0/4 TERISI</span>
                    </div>
                    <p class="text-[11px] text-blue-100 mt-0.5" id="inspSectorSubtitle">Aisle A • Bay 01 • Inspeksi Level Rak R1–R4 Real-Time (SSoT)</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('modalCfsSectorInspector')" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-5 space-y-3 max-h-[70vh] overflow-y-auto">
            <div id="inspActionAlert" class="hidden p-3 rounded-xl border text-xs font-semibold flex items-start gap-2"></div>

            <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 flex items-center justify-between text-[11px] text-slate-600">
                <span><i class="fa-solid fa-database text-[#0170b9] mr-1"></i> Tersinkron langsung dengan <code class="font-mono font-bold text-slate-800">cfs_pallets</code> &amp; <code class="font-mono font-bold text-slate-800">master_lokasi</code></span>
                <span class="font-mono font-bold text-slate-500">4 Level Vertikal (R1–R4)</span>
            </div>

            <div id="inspLevelsContainer" class="space-y-2.5"></div>
        </div>

        <div class="px-5 py-3.5 bg-slate-50 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2">
            <a id="inspMasterRakLink" href="master_rak.php" target="_blank" class="inline-flex items-center gap-1.5 text-[11px] font-bold text-[#0170b9] hover:underline">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>Buka Log Lengkap di Master Rak</span>
            </a>
            <button type="button" onclick="closeModal('modalCfsSectorInspector')" class="px-4 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold rounded-xl transition cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 4C: RELOKASI SLOT RAK PALET (ANTI-BENTROK REAL-TIME)               -->
<!-- ========================================================================= -->
<div id="modalRelocatePallet" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden animate-fadeIn">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden border border-gray-100 transform transition-all text-xs">
        <div class="bg-gradient-to-r from-indigo-700 to-[#0170b9] p-5 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center text-lg shadow-inner">
                    <i class="fa-solid fa-arrows-up-down-left-right"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold leading-tight">Relokasi Slot Rak Gudang</h3>
                    <p class="text-[11px] text-blue-100">Mutasi Real-Time Forklift FL-04 &amp; Cek Anti-Bentrok</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('modalRelocatePallet')" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form onsubmit="submitRelocatePallet(event)" class="p-5 space-y-4">
            <input type="hidden" id="relocInputSscc" value="">

            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3 space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase text-slate-400">Palet Dipindahkan</span>
                    <span id="relocSourceRack" class="px-2 py-0.5 rounded bg-slate-200 text-slate-800 font-mono font-bold text-[10px]">-</span>
                </div>
                <div id="relocSourceCommodity" class="font-bold text-slate-900 text-xs truncate">-</div>
                <div id="relocSourceSscc" class="font-mono text-[11px] text-[#0170b9] font-bold">-</div>
            </div>

            <div class="grid grid-cols-3 gap-2.5">
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Blok (01–06)</label>
                    <select id="relocBlokSelect" onchange="syncRelocateTargetOptions()" class="w-full px-2.5 py-2 bg-gray-50 border border-gray-200 rounded-xl font-bold text-gray-900">
                        <option value="1">Blok 1 (Bay 01)</option>
                        <option value="2">Blok 2 (Bay 02)</option>
                        <option value="3">Blok 3 (Bay 03)</option>
                        <option value="4">Blok 4 (Bay 04)</option>
                        <option value="5">Blok 5 (Bay 05)</option>
                        <option value="6">Blok 6 (Bay 06)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Sektor (A–F)</label>
                    <select id="relocSektorSelect" onchange="syncRelocateTargetOptions()" class="w-full px-2.5 py-2 bg-gray-50 border border-gray-200 rounded-xl font-bold text-gray-900">
                        <option value="A">Sektor A</option>
                        <option value="B">Sektor B</option>
                        <option value="C">Sektor C</option>
                        <option value="D">Sektor D</option>
                        <option value="E">Sektor E</option>
                        <option value="F">Sektor F</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Level (R1–R4)</label>
                    <select id="relocRakSelect" onchange="updateRelocateSlotPreview()" class="w-full px-2 py-2 bg-gray-50 border border-gray-200 rounded-xl font-bold text-gray-900">
                        <option value="1">R1 (L1)</option>
                        <option value="2">R2 (L2)</option>
                        <option value="3">R3 (L3)</option>
                        <option value="4">R4 (L4)</option>
                    </select>
                </div>
            </div>

            <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between gap-2">
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase">Kode Slot Tujuan</div>
                    <div id="relocPreviewCode" class="font-mono font-black text-slate-900 text-xs">AISLE-A-01-L1</div>
                </div>
                <span id="relocSlotStatusPill" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">SLOT KOSONG</span>
            </div>

            <div id="relocErrorBox" class="hidden p-2.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-[11px] font-semibold"></div>

            <div class="pt-2 border-t border-gray-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalRelocatePallet')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="btnSubmitRelocate" class="px-4 py-2 bg-[#0170b9] hover:bg-blue-700 text-white font-bold rounded-xl shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-dolly"></i>
                    <span>Eksekusi Pindah Rak</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 5: MODAL UJI PEMICU SENSOR OPERASIONAL CFS REAL-TIME               -->
<!-- ========================================================================= -->
<div id="modalTriggerCfsSensor" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden animate-fadeIn">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-gray-100 transform transition-all text-xs">
        <!-- Header Modal Dynamic -->
        <div id="cfsSensorModalHeader" class="bg-[#0170b9] p-5 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div id="cfsSensorModalIcon" class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center text-lg shadow-inner">
                    <i class="fa-solid fa-satellite-dish"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold leading-tight" id="cfsSensorModalTitle">Uji Pemicu Sensor IoT Real-Time</h3>
                    <p class="text-[11px] text-slate-300" id="cfsSensorModalSub">Transmisi Data Telemetri &amp; Eksekusi Basis Data Nyata</p>
                </div>
            </div>
            <button onclick="closeModal('modalTriggerCfsSensor')" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="cfs_action" id="sensorFormAction" value="trigger_dock_sensor">

            <!-- Dynamic Form Section: DOCK SENSOR (HW-06) -->
            <div id="sectionTriggerDock" class="space-y-3 hidden">
                <div class="bg-amber-50/80 p-3 rounded-xl border border-amber-200 text-amber-900 text-[11px]">
                    <i class="fa-solid fa-bolt mr-1 text-amber-600"></i>
                    Sensor ultrasonik akan mendeteksi armada merapat ke rampa, menaikkan dock leveler 15T hidrolik, dan menerbitkan Job Order aktif ke MySQL.
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Target Rampa D1-D5 *</label>
                        <select name="dock_id" id="dockSelectInput" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono font-bold text-gray-900">
                            <option value="D1">Rampa D1 (Stripping)</option>
                            <option value="D2">Rampa D2 (Stripping)</option>
                            <option value="D3" selected>Rampa D3 (Standby - Siap)</option>
                            <option value="D4">Rampa D4 (Stuffing)</option>
                            <option value="D5">Rampa D5 (Stuffing)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Mode Peristiwa Sensor *</label>
                        <select name="sensor_mode" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-bold text-gray-900">
                            <option value="arrival" selected>Truk Merapat (Dock In)</option>
                            <option value="departure">Truk Selesai (Dock Out)</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Nomor Polisi Truk</label>
                        <input type="text" name="truck_plate" value="B 9771 KZA" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono uppercase font-bold text-gray-900">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Nomor Peti Kemas</label>
                        <input type="text" name="container_number" value="MSKU7712048" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono uppercase font-bold text-[#0170b9]">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Tipe Pekerjaan</label>
                        <select name="job_type" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-bold">
                            <option value="STRIPPING">STRIPPING (Bongkar FCL &rarr; LCL)</option>
                            <option value="STUFFING">STUFFING (Muat LCL &rarr; FCL)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Consignee / Shipper</label>
                        <input type="text" name="consignee" value="PT Astra Honda Motor" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-bold">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Estimasi Koli / Palet</label>
                        <input type="number" name="total_packages" value="35" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Total Berat Kotor (kg)</label>
                        <input type="number" name="total_weight_kg" value="14200" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono">
                    </div>
                </div>
            </div>

            <!-- Dynamic Form Section: FLOOR SCALE & 3D DIMENSIONER (HW-21) -->
            <div id="sectionTriggerScale" class="space-y-3 hidden">
                <div class="bg-sky-50/80 p-3 rounded-xl border border-sky-200 text-sky-900 text-[11px]">
                    <i class="fa-solid fa-weight-scale mr-1 text-sky-600"></i>
                    Sensor loadcell timbangan lantai membaca berat riil palet, laser 3D mengukur kubikasi CBM, dan menerbitkan label GS1 SSCC ke inventori gudang.
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Ex-Kontainer Asal</label>
                        <input type="text" name="container_origin" value="MSKU8821940" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono uppercase font-bold text-gray-900">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Kategori Barang</label>
                        <select name="category" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-bold text-gray-900">
                            <option value="automotive">Otomotif &amp; Spareparts</option>
                            <option value="electronics" selected>Elektronik &amp; Panel</option>
                            <option value="fmcg">FMCG &amp; Makanan Kering</option>
                            <option value="textile">Tekstil &amp; Garment</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Deskripsi Komoditas</label>
                        <input type="text" name="commodity_name" value="Inverter Solar Cell Hybrid" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-bold">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Consignee</label>
                        <input type="text" name="consignee" value="PT Sharp Electronics Indonesia" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-bold">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[10.5px] font-bold text-gray-700 mb-1">Berat Sensor (kg)</label>
                        <input type="number" step="0.5" name="gross_weight_kg" id="scaleWeightInput" value="520.0" class="w-full px-2.5 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono font-bold text-sky-700">
                    </div>
                    <div>
                        <label class="block text-[10.5px] font-bold text-gray-700 mb-1">Laser CBM (m³)</label>
                        <input type="number" step="0.05" name="cbm" id="scaleCbmInput" value="1.45" class="w-full px-2.5 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono font-bold text-sky-700">
                    </div>
                    <div>
                        <label class="block text-[10.5px] font-bold text-gray-700 mb-1">Jumlah Koli</label>
                        <input type="number" name="carton_count" value="28" class="w-full px-2.5 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Rekomendasi Alokasi Rak Gudang *</label>
                    <select name="rack_location" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono text-xs font-bold text-[#0170b9]">
                        <option value="AISLE-B-04-L1">AISLE-B-04-L1 (Elektronik Level 1)</option>
                        <option value="AISLE-A-03-L2">AISLE-A-03-L2 (Mesin Berat Level 2)</option>
                        <option value="AISLE-C-05-L1">AISLE-C-05-L1 (FMCG Level 1)</option>
                        <option value="AISLE-D-02-L1">AISLE-D-02-L1 (Tekstil Level 1)</option>
                        <option value="AISLE-E-04-L1">AISLE-E-04-L1 (Otomotif Level 1)</option>
                    </select>
                </div>
            </div>

            <!-- Dynamic Form Section: HANDHELD SCANNER HHT (HW-19) -->
            <div id="sectionTriggerScanner" class="space-y-3 hidden">
                <div class="bg-purple-50/80 p-3 rounded-xl border border-purple-200 text-purple-900 text-[11px]">
                    <i class="fa-solid fa-barcode mr-1 text-purple-600"></i>
                    Memindai barcode GS1 SSCC-18 palet kargo LCL dengan scanner laser optik Zebra TC26 untuk memverifikasi penempatan rak dan memperbarui hitungan tally.
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Pilih / Masukkan Nomor Barcode SSCC-18 *</label>
                    <input type="text" name="pallet_sscc" id="hhtBarcodeField" value="00189912001092837101" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono font-bold text-purple-700 text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Verifikasi Masuk Rak Penyimpanan (Putaway)</label>
                    <select name="target_rack" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono text-xs font-bold text-[#0170b9]">
                        <option value="AISLE-E-02-L1">AISLE-E-02-L1 (Sesuai Manifest Tally)</option>
                        <option value="AISLE-B-03-L2">AISLE-B-03-L2 (Relokasi Rak Elektronik)</option>
                        <option value="AISLE-C-02-L1">AISLE-C-02-L1 (Relokasi Rak FMCG)</option>
                        <option value="AISLE-D-05-L1">AISLE-D-05-L1 (Staging Outbound Garment)</option>
                    </select>
                </div>
            </div>

            <!-- Dynamic Form Section: LoRaWAN ENV SENSOR -->
            <div id="sectionTriggerEnv" class="space-y-3 hidden">
                <div class="bg-emerald-50/80 p-3 rounded-xl border border-emerald-200 text-emerald-900 text-[11px]">
                    <i class="fa-solid fa-tower-broadcast mr-1 text-emerald-600"></i>
                    Memicu transmisi radio paket data LoRaWAN 868 MHz dari node sensor SHT40 untuk mencatat suhu dan kelembaban udara gudang ke basis data.
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Pembacaan Suhu (°C)</label>
                        <input type="number" step="0.1" name="temp_val" value="24.5" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono font-bold text-emerald-700">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Kelembaban Relatif (% RH)</label>
                        <input type="number" step="0.5" name="hum_val" value="58.5" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono font-bold text-emerald-700">
                    </div>
                </div>
                <div class="text-[10.5px] text-gray-500 font-mono">
                    Node ID: LoRa-CFS-SHT40 &bull; Frekuensi: 868.10 MHz &bull; SF: 7 &bull; Gateway ID: GW-CIDP-01
                </div>
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeModal('modalTriggerCfsSensor')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition">
                    Batal
                </button>
                <button type="submit" id="btnSubmitCfsSensor" class="px-5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl shadow-md transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-bolt"></i>
                    <span id="btnSubmitCfsSensorText">Eksekusi Trigger Sensor</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT CONTROLLERS MODUL CFS -->
<!-- ========================================================================= -->
<script>
// Open CFS Live Sensor Trigger Workbench Modal
function openSensorTriggerModal(type, param = '') {
    const modal = document.getElementById('modalTriggerCfsSensor');
    if (!modal) return;

    // Reset sections
    document.getElementById('sectionTriggerDock').classList.add('hidden');
    document.getElementById('sectionTriggerScale').classList.add('hidden');
    document.getElementById('sectionTriggerScanner').classList.add('hidden');
    document.getElementById('sectionTriggerEnv').classList.add('hidden');

    const header = document.getElementById('cfsSensorModalHeader');
    const icon = document.getElementById('cfsSensorModalIcon');
    const title = document.getElementById('cfsSensorModalTitle');
    const sub = document.getElementById('cfsSensorModalSub');
    const actionInput = document.getElementById('sensorFormAction');
    const btnSubmit = document.getElementById('btnSubmitCfsSensor');
    const btnText = document.getElementById('btnSubmitCfsSensorText');

    if (type === 'dock') {
        actionInput.value = 'trigger_dock_sensor';
        document.getElementById('sectionTriggerDock').classList.remove('hidden');
        title.innerText = 'Trigger Sensor Keberadaan Truk Rampa (HW-06)';
        sub.innerText = 'Ultrasonik & Loop Detector Rampa Hidrolik D1-D5';
        header.className = 'bg-[#0170b9] p-5 text-white flex items-center justify-between border-b-2 border-amber-500';
        icon.className = 'w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center text-lg';
        icon.innerHTML = '<i class="fa-solid fa-truck-ramp-box"></i>';
        btnSubmit.className = 'px-5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl shadow-md transition flex items-center space-x-1.5';
        btnText.innerText = 'Trigger Sensor Rampa';
        if (param) {
            document.getElementById('dockSelectInput').value = param;
        }
    } else if (type === 'scale') {
        actionInput.value = 'trigger_floor_scale';
        document.getElementById('sectionTriggerScale').classList.remove('hidden');
        title.innerText = 'Trigger Timbangan Lantai & Dimensi CBM (HW-21)';
        sub.innerText = 'Floor Scale 3 Ton & 3D Laser Pallet Dimensioner';
        header.className = 'bg-[#0170b9] p-5 text-white flex items-center justify-between border-b-2 border-sky-500';
        icon.className = 'w-10 h-10 rounded-xl bg-sky-500/20 text-sky-400 border border-sky-500/30 flex items-center justify-center text-lg';
        icon.innerHTML = '<i class="fa-solid fa-weight-scale"></i>';
        btnSubmit.className = 'px-5 py-2 bg-sky-500 hover:bg-sky-400 text-slate-950 font-bold rounded-xl shadow-md transition flex items-center space-x-1.5';
        btnText.innerText = 'Timbang & Daftarkan Palet GS1';
        document.getElementById('scaleWeightInput').value = (450 + Math.floor(Math.random() * 80) + (Math.random() > 0.5 ? 0.5 : 0)).toFixed(1);
        document.getElementById('scaleCbmInput').value = (1.2 + (Math.floor(Math.random() * 5) * 0.05)).toFixed(2);
    } else if (type === 'scanner') {
        actionInput.value = 'trigger_hht_scan';
        document.getElementById('sectionTriggerScanner').classList.remove('hidden');
        title.innerText = 'Trigger Handheld Barcode Scanner Zebra TC26 (HW-19)';
        sub.innerText = 'Pemindaian Optik GS1 SSCC-18 & Verifikasi Rak Aisle A-F';
        header.className = 'bg-[#0170b9] p-5 text-white flex items-center justify-between border-b-2 border-purple-500';
        icon.className = 'w-10 h-10 rounded-xl bg-purple-500/20 text-purple-400 border border-purple-500/30 flex items-center justify-center text-lg';
        icon.innerHTML = '<i class="fa-solid fa-barcode"></i>';
        btnSubmit.className = 'px-5 py-2 bg-purple-500 hover:bg-purple-400 text-slate-950 font-bold rounded-xl shadow-md transition flex items-center space-x-1.5';
        btnText.innerText = 'Scan Barcode & Update Tally';
    } else if (type === 'env') {
        actionInput.value = 'trigger_cfs_env';
        document.getElementById('sectionTriggerEnv').classList.remove('hidden');
        title.innerText = 'Trigger Telemetri LoRaWAN SHT40 (ENV-01)';
        sub.innerText = 'Transmisi 868 MHz Suhu & Kelembaban Gudang 4.000m²';
        header.className = 'bg-[#0170b9] p-5 text-white flex items-center justify-between border-b-2 border-emerald-500';
        icon.className = 'w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-lg';
        icon.innerHTML = '<i class="fa-solid fa-tower-broadcast"></i>';
        btnSubmit.className = 'px-5 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold rounded-xl shadow-md transition flex items-center space-x-1.5';
        btnText.innerText = 'Kirim Paket Telemetri 868MHz';
    } else if (type === 'cctv') {
        alert("📹 [HW-18 CCTV AI HANGGAR CEISA]\nStatus: Kamera AI Aktif mengawasi Aisle F (Jalur Merah Bea Cukai).\nResolusi: 4K 60fps dengan analitik deteksi orang tak berwenang & integritas segel pabean.\nIntegritas Pengawasan: 100% SECURE.");
        return;
    }

    modal.classList.remove('hidden');
}

// Tab Switching Controller
function switchCfsTab(tabId) {
    const tabs = ['tab-rampa', 'tab-manifest', 'tab-denah', 'tab-joborder'];
    tabs.forEach(t => {
        const el = document.getElementById(t);
        const btn = document.getElementById('btn-' + t);
        if (el) {
            el.classList.add('hidden');
            el.classList.remove('block');
        }
        if (btn) {
            btn.className = 'cfs-tab-btn px-4 py-2 rounded-xl text-xs font-semibold transition-all text-slate-600 hover:text-orange-600 hover:bg-orange-50/70 flex items-center gap-2 cursor-pointer';
        }
    });

    const activeEl = document.getElementById(tabId);
    const activeBtn = document.getElementById('btn-' + tabId);
    if (activeEl) {
        activeEl.classList.remove('hidden');
        activeEl.classList.add('block');
    }
    if (activeBtn) {
        activeBtn.className = 'cfs-tab-btn active px-4 py-2 rounded-xl text-xs font-bold transition-all bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-xs shadow-orange-500/20 flex items-center gap-2 cursor-pointer';
    }
}

// Modal Handlers
function openModal(id) {
    const m = document.getElementById(id);
    if (m) m.classList.remove('hidden');
}

function closeModal(id) {
    const m = document.getElementById(id);
    if (m) m.classList.add('hidden');
}

function openModalWithDock(modalId, dockId) {
    openModal(modalId);
    const sel = document.getElementById('modalDockSelect');
    if (sel) sel.value = dockId;
}

function openTallyModal(dockId, ctrNum, status) {
    document.getElementById('tallyTargetDisplay').innerText = 'Rampa ' + dockId + ' • ' + ctrNum + ' (' + status + ')';
    const jobMap = { 'D1': 1, 'D2': 2, 'D4': 3, 'D5': 4 };
    document.getElementById('tallyJobId').value = jobMap[dockId] || 1;
    openModal('modalUpdateTally');
}

// Show GS1 Label Modal
function showGs1LabelModal(sscc, commodity, consignee, rack, cartons, weight) {
    document.getElementById('lblConsignee').innerText = consignee;
    document.getElementById('lblCommodity').innerText = commodity;
    document.getElementById('lblRack').innerText = rack;
    document.getElementById('lblCartons').innerText = cartons + ' Koli / Cartons';
    document.getElementById('lblWeight').innerText = parseFloat(weight).toFixed(1) + ' kg';
    document.getElementById('lblSsccHuman').innerText = '(00) ' + sscc;
    openModal('modalGs1Label');
}

// Open Scanner Page with prefilled query
function openScannerPage(code) {
    window.location.href = 'dashboard.php?page=scanner&query=' + encodeURIComponent(code);
}

// Filter Pallet Table by Search and Dropdown
function filterPalletTable() {
    const searchVal = document.getElementById('searchPalletInput').value.toLowerCase();
    const aisleVal = document.getElementById('filterAisleSelect').value;
    const catVal = document.getElementById('filterCategorySelect').value;

    const rows = document.querySelectorAll('.pallet-row');

    rows.forEach(r => {
        const searchTxt = r.getAttribute('data-search') || '';
        const aisle = r.getAttribute('data-aisle') || '';
        const cat = r.getAttribute('data-category') || '';

        const matchSearch = !searchVal || searchTxt.includes(searchVal);
        const matchAisle = !aisleVal || aisle === aisleVal;
        const matchCat = !catVal || cat === catVal;

        r.dataset.filteredOut = (matchSearch && matchAisle && matchCat) ? '0' : '1';
    });

    if (window.CIDPTableTools) {
        window.CIDPTableTools.apply('#palletTable');
    }
}

// Filter from 2D SVG Denah Click
function filterAisleFromMap(aisleKey) {
    switchCfsTab('tab-manifest');
    const sel = document.getElementById('filterAisleSelect');
    if (sel) {
        sel.value = aisleKey;
        filterPalletTable();
    }
}

window.cfsPalletsData = <?= json_encode($cfs_pallets) ?>;

// Export CFS Inventory to Excel using SheetJS
function exportCfsExcel() {
    const pallets = window.cfsPalletsData || [];
    const exportData = pallets.map((p, idx) => ({
        "No": idx + 1,
        "No. Palet SSCC-18": p.pallet_sscc,
        "Ex-Kontainer": p.container_origin || '-',
        "Komoditas": p.commodity_name,
        "Consignee / Penerima": p.consignee,
        "Kategori": p.category,
        "Lokasi Rak": p.rack_location,
        "Jumlah Koli": p.carton_count,
        "Berat Kotor (kg)": p.gross_weight_kg,
        "Kubikasi (CBM)": p.cbm,
        "Dokumen Pabean": p.customs_doc,
        "Status Bea Cukai": p.customs_status,
        "Status Rak": p.status,
        "Waktu Masuk": p.entry_date
    }));

    if (typeof XLSX !== 'undefined') {
        const ws = XLSX.utils.json_to_sheet(exportData);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Manifest_LCL_CFS");
        XLSX.writeFile(wb, "CIDP_CFS_Manifest_LCL_" + new Date().toISOString().slice(0,10) + ".xlsx");
    } else {
        alert("Pustaka SheetJS sedang dimuat. Silakan gunakan tombol cetak rekap.");
    }
}

// Print Tally Sheet Format Kertas A4 Resmi Berstempel Digital
function printTallySheet(jobNum, ctrNum, jobType, consignee, dock, pkgs, weight, notes) {
    const j = encodeURIComponent(jobNum || '');
    const c = encodeURIComponent(ctrNum || '');
    window.open(`document.php?type=cfs_tally&job=${j}&container=${c}&autoprint=1`, '_blank');
}

// Cetak Label Logistik Palet GS1-128 Standar Internasional
function printCurrentGs1Label() {
    const sscc = document.getElementById('lblSsccHuman')?.innerText.trim() || '(00) 00189912001092837101';
    const consignee = document.getElementById('lblConsignee')?.innerText.trim() || 'PT Astra Honda Motor';
    const commodity = document.getElementById('lblCommodity')?.innerText.trim() || 'Komponen Transmisi Matic';
    const rack = document.getElementById('lblRack')?.innerText.trim() || 'AISLE-E-02-L1';
    const cartons = document.getElementById('lblCartons')?.innerText.trim() || '24 Cartons';
    const weight = document.getElementById('lblWeight')?.innerText.trim() || '480.0 kg';

    if (typeof CIDPPrint !== 'undefined' && typeof CIDPPrint.printGS1Label === 'function') {
        CIDPPrint.printGS1Label({
            sscc: sscc,
            consignee: consignee,
            commodity: commodity,
            rack: rack,
            cartons: cartons,
            weight: weight
        });
    } else {
        window.print();
    }
}

// Cetak Rekapitulasi Operasional Gudang CFS Format Resmi A4 Berstempel Digital
function printCfsRekap() {
    if (typeof CIDPPrint === 'undefined') {
        window.print();
        return;
    }
    const headers = ['No. Job Order', 'Peti Kemas', 'Operasi', 'Consignee / Shipper', 'Rampa Dock', 'Palet (GS1)', 'Berat (kg)', 'Status'];
    const rows = [
        ['JO-STR-0901', 'MSKU8821940 (40HC)', 'STRIPPING LCL', 'PT Astra Honda Motor', 'Dock D1', '24 Palet', '12.400 kg', 'PROSES BONGKAR'],
        ['JO-STF-0902', 'TCLU9102842 (20GP)', 'STUFFING LCL', 'PT Unilever Indonesia Tbk', 'Dock D2', '16 Palet', '8.200 kg', 'SELESAI MUAT'],
        ['JO-STR-0903', 'EITU7120931 (40HC)', 'STRIPPING LCL', 'PT Samsung Electronics ID', 'Dock D3', '30 Palet', '14.800 kg', 'WAITING DOCK'],
        ['JO-STF-0904', 'CMAU3192004 (20GP)', 'STUFFING LCL', 'PT Toyota Motor Mfg ID', 'Dock D4', '18 Palet', '9.500 kg', 'INSPEKSI PABEAN'],
        ['JO-STR-0905', 'ONEU6128491 (40HC)', 'STRIPPING LCL', 'PT Denso Indonesia', 'Dock D5', '22 Palet', '11.100 kg', 'PROSES BONGKAR']
    ];
    CIDPPrint.printTableReport(
        'LAPORAN REKAPITULASI OPERASIONAL CFS WAREHOUSE (RAMPA D1-D5 & LCL INVENTORY)',
        headers,
        rows,
        {
            category: 'CFS-WMS',
            stats: [
                { label: 'Okupansi Rak Gudang', value: '<?= $occupied_pallets ?> / <?= $total_pallet_capacity ?> Palet' },
                { label: 'Rampa Aktif', value: '4 / 5 Rampa Hidrolik' },
                { label: 'Dwell Time LCL', value: '2.8 Hari' },
                { label: 'Throughput Hari Ini', value: '110 Palet (GS1)' }
            ]
        }
    );
}

// =========================================================================
// UNIVERSAL INTER-MODULE DEEP-LINKING INITIALIZER (CFS MODUL)
// =========================================================================
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    const searchParam = urlParams.get('search') || urlParams.get('ctr') || urlParams.get('query');
    const aisleParam = urlParams.get('aisle');
    const dockParam = urlParams.get('dock');
    const actionParam = urlParams.get('action');

    // 1. Handle explicit tab switching
    if (tabParam) {
        let targetTab = tabParam;
        if (!targetTab.startsWith('tab-')) {
            targetTab = 'tab-' + targetTab;
        }
        if (['tab-rampa', 'tab-manifest', 'tab-denah', 'tab-joborder'].includes(targetTab)) {
            switchCfsTab(targetTab);
        }
    }

    // 2. Handle search query (Container or Job Order or Pallet SSCC)
    if (searchParam) {
        if (!tabParam) {
            if (searchParam.toUpperCase().startsWith('JO-')) {
                switchCfsTab('tab-joborder');
            } else {
                switchCfsTab('tab-manifest');
            }
        }
        const palletInput = document.getElementById('searchPalletInput');
        if (palletInput) {
            palletInput.value = searchParam;
            filterPalletTable();
        }
    }

    // 3. Handle aisle filtering (Aisle A-F)
    if (aisleParam) {
        switchCfsTab('tab-manifest');
        const sel = document.getElementById('filterAisleSelect');
        if (sel) {
            sel.value = aisleParam.toUpperCase();
            filterPalletTable();
        }
    }

    // 4. Handle dock focusing (D1 - D5)
    if (dockParam) {
        switchCfsTab('tab-rampa');
        const targetDock = document.getElementById('dock-card-' + dockParam.toUpperCase());
        if (targetDock) {
            setTimeout(() => {
                targetDock.scrollIntoView({ behavior: 'smooth', block: 'center' });
                targetDock.classList.add('ring-4', 'ring-orange-500', 'shadow-xl');
                setTimeout(() => targetDock.classList.remove('ring-4', 'ring-orange-500', 'shadow-xl'), 4000);
            }, 300);
        }
    }

    // 5. Handle action modal
    if (actionParam === 'new_job') {
        openModal('modalNewJobOrder');
    } else if (actionParam === 'assign_dock') {
        openModal('modalAssignDock');
    }

    // 6. Real-Time 6-Block CFS Rack SSoT Layout & SSE Sync Engine
    initCfsLiveSync();
});

// =========================================================================
// REAL-TIME CFS WAREHOUSE 6-BLOK RACK SSoT ENGINE
// =========================================================================
let currentCfsTwinState = <?= json_encode($cfs_initial_twin, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
let activeInspectorCoords = null;
let prevCfsRackOccupancy = null;
let cfsToastTimer = null;
let cfsEventSource = null;
let cfsFallbackTimer = null;

function escapeCfsHtml(val) {
    return String(val ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function parseCfsRackCode(code) {
    const m = String(code || '').trim().toUpperCase().match(/^AISLE-([A-F])-0?([1-6])-L([1-4])$/);
    if (!m) return null;
    return {
        sektor: m[1],
        blok: String(parseInt(m[2], 10)),
        rak: String(parseInt(m[3], 10))
    };
}

function formatCfsRackCode(blok, sektor, rak) {
    const b = Math.max(1, Math.min(6, parseInt(blok, 10) || 1));
    const s = String(sektor || 'A').toUpperCase().replace(/[^A-F]/g, '') || 'A';
    const r = Math.max(1, Math.min(4, parseInt(rak, 10) || 1));
    return `AISLE-${s}-0${b}-L${r}`;
}

function getCfsStatusBadgeClass(status) {
    const st = String(status || '').toUpperCase();
    if (st === 'STAGING_OUT') return 'bg-blue-50 text-blue-700 border-blue-200';
    if (st === 'HOLD') return 'bg-red-50 text-red-700 border-red-200';
    if (st === 'DISPATCHED') return 'bg-slate-100 text-slate-500 border-slate-200';
    return 'bg-emerald-50 text-emerald-700 border-emerald-200';
}

function getCfsCustomsBadgeClass(status) {
    const st = String(status || '').toUpperCase();
    if (st === 'RED_LANE' || st === 'RED_LANE_CHECK') return 'bg-red-50 text-red-700 border-red-200';
    if (st === 'HOLD') return 'bg-amber-50 text-amber-700 border-amber-200';
    return 'bg-emerald-50 text-emerald-700 border-emerald-200';
}

function showCfsLayoutToast(message, tone = 'emerald') {
    const toast = document.getElementById('cfsLayoutToast');
    const msgEl = document.getElementById('cfsLayoutToastMsg');
    if (!toast || !msgEl) return;

    toast.className = tone === 'red'
        ? 'mb-3 p-3 rounded-xl bg-red-50 border border-red-300 text-red-900 text-xs flex items-center justify-between gap-2 animate-fadeIn'
        : 'mb-3 p-3 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 text-xs flex items-center justify-between gap-2 animate-fadeIn';
    msgEl.textContent = message;

    if (cfsToastTimer) clearTimeout(cfsToastTimer);
    cfsToastTimer = setTimeout(() => {
        toast.classList.add('hidden');
    }, 6500);
}

function showInspectorAlert(message, tone = 'red') {
    const el = document.getElementById('inspActionAlert');
    if (!el) return;
    el.className = tone === 'emerald'
        ? 'p-3 rounded-xl border bg-emerald-50 border-emerald-200 text-emerald-800 text-xs font-semibold flex items-start gap-2'
        : 'p-3 rounded-xl border bg-red-50 border-red-200 text-red-800 text-xs font-semibold flex items-start gap-2';
    el.innerHTML = `<i class="fa-solid ${tone === 'emerald' ? 'fa-circle-check text-emerald-600' : 'fa-shield-halved text-red-600'} mt-0.5"></i><span>${escapeCfsHtml(message)}</span>`;
}

function hideInspectorAlert() {
    const el = document.getElementById('inspActionAlert');
    if (el) el.classList.add('hidden');
}

function buildManifestRowActionButtons(p) {
    const sscc = escapeCfsHtml(p.pallet_sscc || '');
    const rack = escapeCfsHtml(p.rack_location || '-');
    const comm = escapeCfsHtml(p.commodity_name || '-');
    const cons = escapeCfsHtml(p.consignee || '-');
    const cartons = parseInt(p.carton_count || 0, 10) || 0;
    const weight = parseFloat(p.gross_weight_kg || p.weight_kg || 0) || 0;
    const isActiveRack = (p.status === 'IN_RACK' || p.status === 'STAGING_OUT');

    let btns = '<div class="flex items-center justify-center space-x-1">';
    if (isActiveRack) {
        btns += `<button type="button" data-row-act="relocate" class="w-7 h-7 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#0170b9] transition flex items-center justify-center text-xs cursor-pointer" title="Pindahkan Slot Rak (Relokasi Real-Time)"><i class="fa-solid fa-arrows-up-down-left-right"></i></button>`;
        btns += `<button type="button" data-row-act="dispatch" class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition flex items-center justify-center text-xs cursor-pointer" title="Keluarkan / Dispatch Palet Keluar Gudang"><i class="fa-solid fa-truck-ramp-box"></i></button>`;
    }
    btns += `<button type="button" data-row-act="gs1" class="w-7 h-7 rounded-lg bg-gray-100 hover:bg-orange-50 hover:text-orange-600 text-gray-600 transition flex items-center justify-center text-xs cursor-pointer" title="Cetak Label Barcode GS1-128"><i class="fa-solid fa-print"></i></button>`;
    btns += `<button type="button" data-row-act="scanner" class="w-7 h-7 rounded-lg bg-gray-100 hover:bg-purple-50 hover:text-purple-600 text-gray-600 transition flex items-center justify-center text-xs cursor-pointer" title="Verifikasi dengan Scanner GS1"><i class="fa-solid fa-qrcode"></i></button>`;
    btns += '</div>';
    return { btns, sscc, rack, comm, cons, cartons, weight };
}

function attachManifestRowButtonHandlers(tr, p) {
    const relBtn = tr.querySelector('[data-row-act="relocate"]');
    const dspBtn = tr.querySelector('[data-row-act="dispatch"]');
    const gs1Btn = tr.querySelector('[data-row-act="gs1"]');
    const scnBtn = tr.querySelector('[data-row-act="scanner"]');

    if (relBtn) relBtn.addEventListener('click', () => openRelocatePalletModal(p.pallet_sscc, p.rack_location, p.commodity_name));
    if (dspBtn) dspBtn.addEventListener('click', () => cfsQuickDispatch(p.pallet_sscc));
    if (gs1Btn) gs1Btn.addEventListener('click', () => showGs1LabelModal(p.pallet_sscc, p.commodity_name, p.consignee, p.rack_location, p.carton_count, p.gross_weight_kg || p.weight_kg || 0));
    if (scnBtn) scnBtn.addEventListener('click', () => openScannerPage(p.pallet_sscc));
}

function syncManifestTableFromState(pallets) {
    const tbody = document.querySelector('#palletTable tbody');
    if (!tbody || !Array.isArray(pallets)) return;

    const existingRows = new Map();
    tbody.querySelectorAll('tr.pallet-row[data-sscc]').forEach(tr => {
        existingRows.set(tr.getAttribute('data-sscc'), tr);
    });

    pallets.forEach(p => {
        const sscc = String(p.pallet_sscc || '');
        if (!sscc) return;
        const rackLoc = String(p.rack_location || '-');
        const stBadgeClass = getCfsStatusBadgeClass(p.status);
        const custBadgeClass = getCfsCustomsBadgeClass(p.customs_status);
        const searchBlob = `${sscc} ${p.commodity_name || ''} ${p.consignee || ''} ${p.container_origin || ''} ${rackLoc}`.toLowerCase();

        let tr = existingRows.get(sscc);
        if (tr) {
            tr.dataset.aisle = rackLoc.slice(0, 7);
            tr.dataset.search = searchBlob;

            const rkEl = tr.querySelector('[data-rack-badge]');
            if (rkEl) rkEl.textContent = rackLoc;

            const stEl = tr.querySelector('[data-status-badge]');
            if (stEl) {
                stEl.className = `px-2 py-0.5 rounded-full text-[9.5px] font-bold border ${stBadgeClass}`;
                stEl.setAttribute('data-status-badge', '');
                stEl.textContent = String(p.status || 'IN_RACK').replace(/_/g, ' ');
            }

            const actCell = tr.querySelector('td:last-child');
            if (actCell) {
                const { btns } = buildManifestRowActionButtons(p);
                actCell.innerHTML = btns;
                attachManifestRowButtonHandlers(tr, p);
            }
        } else {
            tr = document.createElement('tr');
            tr.className = 'hover:bg-orange-50/40 transition-colors pallet-row bg-emerald-50/30';
            tr.setAttribute('data-sscc', sscc);
            tr.setAttribute('data-aisle', rackLoc.slice(0, 7));
            tr.setAttribute('data-category', p.category || 'general');
            tr.setAttribute('data-search', searchBlob);

            const weight = parseFloat(p.gross_weight_kg || p.weight_kg || 0) || 0;
            const cbm = parseFloat(p.cbm || p.volume_cbm || 0) || 0;
            const cartons = parseInt(p.carton_count || 0, 10) || 0;
            const { btns } = buildManifestRowActionButtons(p);

            tr.innerHTML = `
                <td class="py-3 px-2.5 text-center font-mono text-gray-400" data-row-num>1</td>
                <td class="py-3 px-3.5">
                    <div class="flex items-center space-x-2">
                        <div class="w-7 h-7 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center text-xs flex-shrink-0">
                            <i class="fa-solid fa-barcode"></i>
                        </div>
                        <div>
                            <span class="font-mono font-bold text-gray-900 block leading-tight text-[11.5px]">${escapeCfsHtml(sscc)}</span>
                            <span class="text-[9.5px] text-gray-400 font-mono">(00) AI GS1-128</span>
                        </div>
                    </div>
                </td>
                <td class="py-3 px-3.5 font-mono">
                    <span class="font-bold text-gray-800 text-[11px] block">${escapeCfsHtml(p.container_origin || '-')}</span>
                    <span class="text-[10px] text-gray-500 block">${escapeCfsHtml(p.customs_doc || 'SPPB-CEISA')}</span>
                </td>
                <td class="py-3 px-3.5 max-w-xs">
                    <span class="font-bold text-gray-900 block truncate" title="${escapeCfsHtml(p.commodity_name || '')}">${escapeCfsHtml(p.commodity_name || '-')}</span>
                    <span class="text-[10.5px] text-gray-500 block truncate">${escapeCfsHtml(p.consignee || '-')}</span>
                </td>
                <td class="py-3 px-3.5 text-center">
                    <span class="px-2.5 py-1 bg-slate-100 border border-slate-200 rounded-lg text-slate-800 font-mono font-bold text-[11px]" data-rack-badge>${escapeCfsHtml(rackLoc)}</span>
                </td>
                <td class="py-3 px-3.5 text-right font-mono">
                    <span class="font-bold text-gray-900 block">${cartons} Koli</span>
                    <span class="text-[10.5px] text-gray-500 block">${weight.toFixed(1)} kg</span>
                </td>
                <td class="py-3 px-3.5 text-right font-mono">
                    <span class="font-bold text-gray-800">${cbm.toFixed(2)}</span>
                    <span class="text-[10px] text-gray-400 block">m³ (CBM)</span>
                </td>
                <td class="py-3 px-3.5 text-center">
                    <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold border ${custBadgeClass}">${escapeCfsHtml(p.customs_status || 'CLEARED')}</span>
                </td>
                <td class="py-3 px-3.5 text-center">
                    <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold border ${stBadgeClass}" data-status-badge>${escapeCfsHtml(String(p.status || 'IN_RACK').replace(/_/g, ' '))}</span>
                </td>
                <td class="py-3 px-3.5 text-center">${btns}</td>
            `;
            tbody.insertBefore(tr, tbody.firstChild);
            attachManifestRowButtonHandlers(tr, p);
        }
    });

    let rowIdx = 1;
    tbody.querySelectorAll('tr.pallet-row [data-row-num]').forEach(cell => {
        cell.textContent = String(rowIdx++);
    });
}

function renderLiveCfsLayout(physical, toastHint = null) {
    if (!physical || !physical.cfs_rack_grid) return;

    if (!currentCfsTwinState || typeof currentCfsTwinState !== 'object') {
        currentCfsTwinState = {};
    }
    currentCfsTwinState.physical = physical;

    const grid = physical.cfs_rack_grid;
    const stats = physical.cfs_rack_stats || {};
    const nextOcc = {};
    const changedSectors = new Set();
    const changedDetails = [];

    ['1', '2', '3', '4', '5', '6'].forEach(b => {
        ['A', 'B', 'C', 'D', 'E', 'F'].forEach(s => {
            const levels = (grid[b] && grid[b][s]) ? grid[b][s] : {};
            const maxRak = Math.max(4, Object.keys(levels).length || 4);
            let filledCount = 0;

            for (let rIdx = 1; rIdx <= maxRak; rIdx++) {
                const r = String(rIdx);
                const slot = levels[r] || null;
                const isFilled = Boolean(slot && slot.filled);
                if (isFilled) filledCount++;

                const occKey = `${b}-${s}-${r}`;
                const occVal = isFilled ? String(slot.pallet_sscc || 'OCCUPIED') : '';
                if (prevCfsRackOccupancy !== null && prevCfsRackOccupancy[occKey] !== occVal) {
                    changedSectors.add(`${b}-${s}`);
                    const slotCode = (slot && slot.code) ? slot.code : formatCfsRackCode(b, s, r);
                    changedDetails.push((isFilled ? '+ Masuk ' : '- Keluar ') + slotCode);
                }
                nextOcc[occKey] = occVal;
            }

            const unitEl = document.getElementById(`cfsRackUnit-${b}-${s}`);
            if (unitEl) {
                unitEl.classList.remove('state-empty', 'state-partial', 'state-full');
                const nextClass = filledCount === 0 ? 'state-empty' : (filledCount >= maxRak ? 'state-full' : 'state-partial');
                unitEl.classList.add(nextClass);
                unitEl.title = `Blok ${b} - Sektor ${s} (${filledCount}/${maxRak} terisi) • Klik untuk inspeksi R1–R${maxRak}`;
            }

            const countEl = document.getElementById(`cfsRackCount-${b}-${s}`);
            if (countEl) {
                countEl.textContent = `${filledCount}/${maxRak}`;
            }

            const dotsWrap = document.getElementById(`cfsRackDots-${b}-${s}`);
            if (dotsWrap) {
                dotsWrap.querySelectorAll('.dot').forEach(dot => {
                    const rKey = String(dot.getAttribute('data-rak') || '');
                    const slot = levels[rKey] || null;
                    const isFilled = Boolean(slot && slot.filled);
                    dot.classList.toggle('fill', isFilled);
                    if (slot && slot.code) {
                        dot.title = isFilled
                            ? `R${rKey} (${slot.code}): ${slot.pallet_sscc || ''} - ${slot.commodity_name || ''}`
                            : `R${rKey} (${slot.code}): Kosong`;
                    }
                });
            }
        });
    });

    prevCfsRackOccupancy = nextOcc;

    // Visual glow ring for modified sectors
    changedSectors.forEach(bs => {
        const card = document.getElementById(`cfsRackUnit-${bs}`);
        if (card) {
            card.classList.add('ring-4', 'ring-orange-400', 'shadow-lg');
            setTimeout(() => card.classList.remove('ring-4', 'ring-orange-400', 'shadow-lg'), 3500);
        }
    });

    if (toastHint) {
        showCfsLayoutToast(toastHint, 'emerald');
    } else if (changedDetails.length > 0) {
        showCfsLayoutToast('Sync Real-Time SSoT: ' + changedDetails.slice(0, 3).join(' | '), 'emerald');
    }

    // Update KPI Summary Counters
    const elBlok = document.getElementById('cfsSumBlok');
    const elSektor = document.getElementById('cfsSumSektor');
    const elTerisi = document.getElementById('cfsSumTerisi');
    const elKosong = document.getElementById('cfsSumKosong');
    const elFill = document.getElementById('cfsOccBarFill');
    const elText = document.getElementById('cfsOccText');

    if (elBlok && stats.total_blocks !== undefined) elBlok.textContent = stats.total_blocks;
    if (elSektor && stats.total_sectors !== undefined) elSektor.textContent = stats.total_sectors;
    if (elTerisi && stats.filled !== undefined) elTerisi.textContent = stats.filled;
    if (elKosong && stats.empty !== undefined) elKosong.textContent = stats.empty;

    const occPct = Number(stats.occupancy_pct || 0);
    if (elFill) elFill.style.width = `${Math.max(0, Math.min(100, occPct))}%`;
    if (elText) {
        const totalSlots = stats.total_slots ?? 144;
        elText.innerHTML = `Occupancy Rate: <strong>${occPct.toFixed(1)}%</strong> dari total ${totalSlots} slot level`;
    }

    if (Array.isArray(physical.cfs_pallets)) {
        syncManifestTableFromState(physical.cfs_pallets);
    }

    // Keep open inspector or relocate modal synchronized
    const inspModal = document.getElementById('modalCfsSectorInspector');
    if (inspModal && !inspModal.classList.contains('hidden') && activeInspectorCoords) {
        renderInspectorLevels(activeInspectorCoords.blok, activeInspectorCoords.sektor);
    }

    const relModal = document.getElementById('modalRelocatePallet');
    if (relModal && !relModal.classList.contains('hidden')) {
        syncRelocateTargetOptions();
    }
}

function renderInspectorLevels(blok, sektor) {
    const b = String(blok);
    const s = String(sektor).toUpperCase();
    const grid = currentCfsTwinState?.physical?.cfs_rack_grid || {};
    const levels = (grid[b] && grid[b][s]) ? grid[b][s] : {};

    let filledCount = 0;
    for (let i = 1; i <= 4; i++) {
        if (levels[String(i)]?.filled) filledCount++;
    }

    const titleEl = document.getElementById('inspSectorTitle');
    const subEl = document.getElementById('inspSectorSubtitle');
    const badgeEl = document.getElementById('inspSectorBadge');
    const linkEl = document.getElementById('inspMasterRakLink');
    const container = document.getElementById('inspLevelsContainer');

    if (titleEl) titleEl.textContent = `BLOK ${b} • SEKTOR ${s}`;
    if (subEl) subEl.textContent = `Aisle ${s} • Bay 0${b} • Inspeksi Level Rak R1–R4 Real-Time (SSoT)`;
    if (badgeEl) {
        badgeEl.textContent = `${filledCount}/4 TERISI`;
        badgeEl.className = filledCount >= 4
            ? 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500 text-white font-mono'
            : (filledCount > 0
                ? 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-400 text-slate-900 font-mono'
                : 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-400 text-slate-900 font-mono');
    }
    if (linkEl) linkEl.href = `master_rak.php?search=${encodeURIComponent(s + b)}`;
    if (!container) return;

    container.innerHTML = '';

    for (let i = 1; i <= 4; i++) {
        const r = String(i);
        const slot = levels[r] || {
            rak: i,
            code: formatCfsRackCode(b, s, r),
            filled: false,
            pallet_sscc: null,
            commodity_name: null,
            container_origin: null,
            consignee: null,
            weight_kg: 0,
            cbm: 0,
            cartons: 0,
            customs_status: 'CLEARED',
            pallet_status: 'KOSONG'
        };

        const card = document.createElement('div');
        if (slot.filled) {
            const cust = String(slot.customs_status || 'CLEARED').toUpperCase();
            const isHold = (cust === 'HOLD' || cust === 'RED_LANE' || cust === 'RED_LANE_CHECK');
            card.className = isHold
                ? 'p-3.5 rounded-2xl border-2 border-red-200 bg-red-50/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3'
                : 'p-3.5 rounded-2xl border border-emerald-200 bg-emerald-50/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3';

            const custBadgeClass = getCfsCustomsBadgeClass(cust);
            const stBadgeClass = getCfsStatusBadgeClass(slot.pallet_status);

            card.innerHTML = `
                <div class="space-y-1 min-w-0">
                    <div class="flex items-center flex-wrap gap-1.5">
                        <span class="px-2 py-0.5 rounded-lg bg-slate-900 text-white font-mono font-black text-[10px]">R${i}</span>
                        <span class="px-2 py-0.5 rounded-lg bg-white border border-slate-200 font-mono font-bold text-[#0170b9] text-[10.5px]">${escapeCfsHtml(slot.code)}</span>
                        <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold border ${custBadgeClass}">Pabean: ${escapeCfsHtml(cust)}</span>
                        <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold border ${stBadgeClass}">${escapeCfsHtml(String(slot.pallet_status || 'IN_RACK').replace(/_/g, ' '))}</span>
                    </div>
                    <div class="font-extrabold text-slate-900 text-xs truncate">${escapeCfsHtml(slot.commodity_name || 'Kargo LCL Terisi')}</div>
                    <div class="text-[10.5px] text-slate-600 font-mono flex flex-wrap items-center gap-x-2 gap-y-0.5">
                        <span>SSCC: <strong class="text-slate-900">${escapeCfsHtml(slot.pallet_sscc || '-')}</strong></span>
                        <span>&bull; Ex: <strong>${escapeCfsHtml(slot.container_origin || '-')}</strong></span>
                        <span>&bull; ${escapeCfsHtml(slot.consignee || '-')}</span>
                    </div>
                    <div class="text-[10px] text-slate-500 font-mono">
                        ${parseInt(slot.cartons || 0, 10)} Koli &bull; ${Number(slot.weight_kg || 0).toFixed(1)} kg &bull; ${Number(slot.cbm || 0).toFixed(2)} CBM
                    </div>
                </div>
                <div class="flex flex-wrap sm:flex-nowrap items-center gap-1.5 shrink-0">
                    <button type="button" data-insp-act="relocate" class="px-2.5 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-[#0170b9] border border-blue-200 font-bold text-[11px] flex items-center gap-1 transition cursor-pointer">
                        <i class="fa-solid fa-arrows-up-down-left-right"></i>
                        <span>Pindah Rak</span>
                    </button>
                    <button type="button" data-insp-act="dispatch" class="px-2.5 py-1.5 rounded-xl ${isHold ? 'bg-amber-50 hover:bg-amber-100 text-amber-800 border-amber-200' : 'bg-emerald-600 hover:bg-emerald-700 text-white border-emerald-600'} border font-bold text-[11px] flex items-center gap-1 transition cursor-pointer">
                        <i class="fa-solid ${isHold ? 'fa-lock' : 'fa-truck-ramp-box'}"></i>
                        <span>Dispatch Keluar</span>
                    </button>
                    <button type="button" data-insp-act="gs1" class="px-2.5 py-1.5 rounded-xl bg-white hover:bg-orange-50 text-slate-700 hover:text-orange-600 border border-slate-200 font-bold text-[11px] flex items-center gap-1 transition cursor-pointer" title="Cetak Label Barcode GS1-128">
                        <i class="fa-solid fa-print"></i>
                    </button>
                </div>
            `;

            card.querySelector('[data-insp-act="relocate"]')?.addEventListener('click', () => {
                openRelocatePalletModal(slot.pallet_sscc, slot.code, slot.commodity_name);
            });
            card.querySelector('[data-insp-act="dispatch"]')?.addEventListener('click', () => {
                cfsQuickDispatch(slot.pallet_sscc);
            });
            card.querySelector('[data-insp-act="gs1"]')?.addEventListener('click', () => {
                showGs1LabelModal(slot.pallet_sscc, slot.commodity_name || '-', slot.consignee || '-', slot.code, slot.cartons || 0, slot.weight_kg || 0);
            });
        } else {
            card.className = 'p-3 rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 flex items-center justify-between gap-3';
            card.innerHTML = `
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="px-2 py-0.5 rounded-lg bg-slate-200 text-slate-700 font-mono font-black text-[10px]">R${i}</span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-slate-800 text-[11px]">${escapeCfsHtml(slot.code)}</span>
                            <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-slate-200/80 text-slate-600">RAK KOSONG</span>
                        </div>
                        <div class="text-[10.5px] text-slate-500">Slot tersedia — siap menerima putaway palet LCL / hasil stripping</div>
                    </div>
                </div>
                <button type="button" data-insp-act="putaway" class="px-3 py-1.5 rounded-xl bg-[#0170b9] hover:bg-blue-700 text-white font-bold text-[11px] shadow-xs flex items-center gap-1.5 shrink-0 transition cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>Isi Slot Ini</span>
                </button>
            `;
            card.querySelector('[data-insp-act="putaway"]')?.addEventListener('click', () => {
                cfsQuickPutawayFromSlot(slot.code);
            });
        }

        container.appendChild(card);
    }
}

function openCfsSectorModal(blok, sektor) {
    activeInspectorCoords = { blok: String(blok), sektor: String(sektor).toUpperCase() };
    hideInspectorAlert();
    renderInspectorLevels(activeInspectorCoords.blok, activeInspectorCoords.sektor);
    openModal('modalCfsSectorInspector');
}

function cfsQuickPutawayFromSlot(code) {
    closeModal('modalCfsSectorInspector');

    const modal = document.getElementById('modalAddPallet');
    const form = document.getElementById('modalAddPalletForm');
    if (!modal || !form) return;

    const rackSel = modal.querySelector('select[name="rack_location"]');
    if (rackSel) {
        let opt = Array.from(rackSel.options).find(o => o.value === code);
        if (!opt) {
            opt = document.createElement('option');
            opt.value = code;
            opt.textContent = `${code} (Slot Terpilih dari Layout)`;
            rackSel.insertBefore(opt, rackSel.firstChild);
        }
        rackSel.value = code;
    }

    const hintEl = document.getElementById('addPalletLiveHint');
    const hintTextEl = document.getElementById('addPalletLiveHintText');
    if (hintEl && hintTextEl) {
        hintEl.className = 'p-2.5 rounded-xl bg-blue-50 border border-blue-200 text-[#0170b9] text-[11px] font-semibold flex items-center gap-2';
        hintTextEl.textContent = `Slot Tujuan Terpilih: ${code} — langsung mengisi gudang & menyalakan indikator titik merah real-time.`;
    }

    form.dataset.ajaxMode = '1';
    openModal('modalAddPallet');

    const commInput = form.querySelector('input[name="commodity_name"]');
    if (commInput) {
        setTimeout(() => commInput.focus(), 80);
    }
}

function openRelocatePalletModal(sscc, currentRack, commodity) {
    const cleanSscc = String(sscc || '').trim();
    const pallets = currentCfsTwinState?.physical?.cfs_pallets || [];
    const livePal = pallets.find(p => String(p.pallet_sscc) === cleanSscc);
    const actualRack = livePal?.rack_location || currentRack || 'AISLE-A-01-L1';
    const actualComm = livePal?.commodity_name || commodity || '-';

    document.getElementById('relocInputSscc').value = cleanSscc;
    document.getElementById('relocSourceSscc').textContent = '(00) ' + cleanSscc;
    document.getElementById('relocSourceCommodity').textContent = actualComm;
    document.getElementById('relocSourceRack').textContent = 'Asal: ' + actualRack;

    const errBox = document.getElementById('relocErrorBox');
    if (errBox) errBox.classList.add('hidden');

    const parsed = parseCfsRackCode(actualRack) || { blok: '1', sektor: 'A', rak: '1' };
    const bSel = document.getElementById('relocBlokSelect');
    const sSel = document.getElementById('relocSektorSelect');
    if (bSel) bSel.value = parsed.blok;
    if (sSel) sSel.value = parsed.sektor;

    syncRelocateTargetOptions(true);
    openModal('modalRelocatePallet');
}

function syncRelocateTargetOptions(autoPickEmpty = false) {
    const bSel = document.getElementById('relocBlokSelect');
    const sSel = document.getElementById('relocSektorSelect');
    const rSel = document.getElementById('relocRakSelect');
    if (!bSel || !sSel || !rSel) return;

    const b = bSel.value;
    const s = sSel.value;
    const levels = currentCfsTwinState?.physical?.cfs_rack_grid?.[b]?.[s] || {};
    let firstEmpty = null;

    Array.from(rSel.options).forEach(opt => {
        const r = opt.value;
        const slot = levels[r];
        const isFilled = Boolean(slot && slot.filled);
        if (!isFilled && firstEmpty === null) firstEmpty = r;
        const suffix = isFilled ? `[TERISI - ${String(slot.pallet_sscc || '').slice(-6)}]` : '[KOSONG]';
        opt.textContent = `R${r} (L${r}) • ${suffix}`;
    });

    if (autoPickEmpty && firstEmpty !== null) {
        rSel.value = firstEmpty;
    }
    updateRelocateSlotPreview();
}

function updateRelocateSlotPreview() {
    const b = document.getElementById('relocBlokSelect')?.value || '1';
    const s = document.getElementById('relocSektorSelect')?.value || 'A';
    const r = document.getElementById('relocRakSelect')?.value || '1';
    const code = formatCfsRackCode(b, s, r);

    const codeEl = document.getElementById('relocPreviewCode');
    const pillEl = document.getElementById('relocSlotStatusPill');
    if (codeEl) codeEl.textContent = code;

    const slot = currentCfsTwinState?.physical?.cfs_rack_grid?.[b]?.[s]?.[r];
    if (pillEl) {
        if (slot && slot.filled) {
            pillEl.className = 'px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200';
            pillEl.textContent = `TERISI (${String(slot.pallet_sscc || '').slice(-6)})`;
        } else {
            pillEl.className = 'px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200';
            pillEl.textContent = 'SLOT KOSONG — SIAP';
        }
    }
}

async function submitRelocatePallet(event) {
    if (event) event.preventDefault();
    const sscc = document.getElementById('relocInputSscc')?.value || '';
    const b = document.getElementById('relocBlokSelect')?.value || '1';
    const s = document.getElementById('relocSektorSelect')?.value || 'A';
    const r = document.getElementById('relocRakSelect')?.value || '1';
    const targetRack = formatCfsRackCode(b, s, r);

    if (window.cidpConfirm) {
        const ok = await window.cidpConfirm({
            title: 'Konfirmasi Relokasi Palet CFS',
            target: `${sscc} → ${targetRack}`,
            message: `Pindahkan palet ${sscc} ke lokasi rak ${targetRack} melalui Forklift FL-04?`,
            confirmText: 'Ya, Pindahkan',
            cancelText: 'Tidak, Batal',
            tone: 'warning'
        });
        if (!ok) return;
    }

    const errBox = document.getElementById('relocErrorBox');
    const btn = document.getElementById('btnSubmitRelocate');
    if (errBox) errBox.classList.add('hidden');
    if (btn) {
        btn.disabled = true;
        btn.classList.add('opacity-70');
    }

    try {
        const res = await fetch('api/cfs_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'relocate_pallet',
                pallet_sscc: sscc,
                target_rack: targetRack
            })
        });
        const data = await res.json();
        if (!res.ok || !data.success) {
            if (errBox) {
                errBox.textContent = data.message || 'Gagal memindahkan slot rak.';
                errBox.classList.remove('hidden');
            }
            return;
        }

        closeModal('modalRelocatePallet');
        if (data.state && data.state.physical) {
            renderLiveCfsLayout(data.state.physical, data.message);
        }
    } catch (err) {
        if (errBox) {
            errBox.textContent = 'Koneksi ke api/cfs_action.php gagal: ' + err.message;
            errBox.classList.remove('hidden');
        }
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.classList.remove('opacity-70');
        }
    }
}

async function cfsQuickDispatch(sscc) {
    hideInspectorAlert();
    try {
        const res = await fetch('api/cfs_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'dispatch_pallet',
                pallet_sscc: sscc
            })
        });
        const data = await res.json();
        if (!res.ok || !data.success) {
            const msg = data.message || 'Dispatch palet ditolak oleh sistem.';
            const inspModal = document.getElementById('modalCfsSectorInspector');
            if (inspModal && !inspModal.classList.contains('hidden')) {
                showInspectorAlert(msg, 'red');
            } else {
                showCfsLayoutToast(msg, 'red');
            }
            return;
        }

        const inspModal = document.getElementById('modalCfsSectorInspector');
        if (inspModal && !inspModal.classList.contains('hidden')) {
            showInspectorAlert(data.message, 'emerald');
        }
        if (data.state && data.state.physical) {
            renderLiveCfsLayout(data.state.physical, data.message);
        }
    } catch (err) {
        showCfsLayoutToast('Koneksi gagal saat dispatch palet: ' + err.message, 'red');
    }
}

function initCfsLiveSync() {
    // Wrap closeModal once so canceling Quick Putaway clears ajaxMode flag cleanly
    const origCloseModal = closeModal;
    closeModal = function(id) {
        origCloseModal(id);
        if (id === 'modalAddPallet') {
            const f = document.getElementById('modalAddPalletForm');
            const h = document.getElementById('addPalletLiveHint');
            if (f) delete f.dataset.ajaxMode;
            if (h) h.classList.add('hidden');
        }
    };

    // Intercept #modalAddPalletForm submit when launched via Layout '+ Isi Slot Ini'
    const addForm = document.getElementById('modalAddPalletForm');
    if (addForm && !addForm.dataset.boundAjax) {
        addForm.dataset.boundAjax = '1';
        addForm.addEventListener('submit', async function(e) {
            if (addForm.dataset.ajaxMode !== '1') return;
            e.preventDefault();

            const submitBtn = addForm.querySelector('button[type="submit"]');
            const hintEl = document.getElementById('addPalletLiveHint');
            const hintText = document.getElementById('addPalletLiveHintText');
            const fd = new FormData(addForm);

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-70');
            }

            try {
                const res = await fetch('api/cfs_action.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'quick_putaway',
                        container_origin: fd.get('container_origin') || 'MSKU8821940',
                        category: fd.get('category') || 'general',
                        commodity_name: fd.get('commodity_name') || 'Kargo LCL Konsolidasi',
                        consignee: fd.get('consignee') || 'PT Penerima Logistik',
                        carton_count: parseInt(fd.get('carton_count') || '20', 10),
                        gross_weight_kg: parseFloat(fd.get('gross_weight_kg') || '450'),
                        cbm: parseFloat(fd.get('cbm') || '1.25'),
                        rack_location: fd.get('rack_location') || 'AISLE-A-01-L1',
                        auto_fallback: false
                    })
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    if (hintEl && hintText) {
                        hintEl.className = 'p-2.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-[11px] font-semibold flex items-center gap-2';
                        hintText.textContent = data.message || 'Gagal menempatkan palet ke slot rak.';
                    }
                    return;
                }

                // Clear commodity/consignee & generate new preview SSCC before closing
                const commIn = addForm.querySelector('input[name="commodity_name"]');
                const consIn = addForm.querySelector('input[name="consignee"]');
                const ssccIn = addForm.querySelector('input[name="pallet_sscc"]');
                if (commIn) commIn.value = '';
                if (consIn) consIn.value = '';
                if (ssccIn) ssccIn.value = '0018991200109' + String(Math.floor(1000000 + Math.random() * 8999999));

                closeModal('modalAddPallet');
                if (data.state && data.state.physical) {
                    renderLiveCfsLayout(data.state.physical, data.message);
                }
            } catch (err) {
                if (hintEl && hintText) {
                    hintEl.className = 'p-2.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-[11px] font-semibold flex items-center gap-2';
                    hintText.textContent = 'Koneksi gagal: ' + err.message;
                }
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-70');
                }
            }
        });
    }

    // Seed initial occupancy map from PHP initial snapshot
    if (currentCfsTwinState && currentCfsTwinState.physical) {
        renderLiveCfsLayout(currentCfsTwinState.physical);
    }

    const startPollingFallback = () => {
        if (cfsFallbackTimer) return;
        cfsFallbackTimer = setInterval(async () => {
            try {
                const res = await fetch('api/stream_twin.php?poll=1');
                if (!res.ok) return;
                const payload = await res.json();
                if (payload && payload.physical) {
                    renderLiveCfsLayout(payload.physical);
                }
            } catch (e) { /* keep silent during background poll */ }
        }, 5000);
    };

    const connectSse = () => {
        if (typeof EventSource === 'undefined') {
            startPollingFallback();
            return;
        }
        if (cfsEventSource) {
            try { cfsEventSource.close(); } catch (e) {}
        }

        const badge = document.getElementById('cfsLiveStreamBadge');
        const badgeTxt = document.getElementById('cfsLiveStreamText');
        const es = new EventSource('api/stream_twin.php');
        cfsEventSource = es;

        const handleSsePayload = (ev) => {
            if (cfsFallbackTimer) {
                clearInterval(cfsFallbackTimer);
                cfsFallbackTimer = null;
            }
            if (badge && badgeTxt) {
                badge.className = 'inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200';
                badgeTxt.textContent = 'Live SSE Sync (SSoT)';
            }
            try {
                const data = JSON.parse(ev.data);
                if (data && data.physical) {
                    renderLiveCfsLayout(data.physical);
                }
            } catch (err) { /* ignore malformed chunk */ }
        };

        es.addEventListener('initial_state', handleSsePayload);
        es.addEventListener('state_update', handleSsePayload);
        es.addEventListener('reconnect_advisory', () => {
            try { es.close(); } catch (e) {}
            setTimeout(connectSse, 400);
        });
        es.onerror = () => {
            if (badge && badgeTxt) {
                badge.className = 'inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200';
                badgeTxt.textContent = 'Sync Polling (Fallback)';
            }
            startPollingFallback();
        };
    };

    connectSse();
}
</script>
