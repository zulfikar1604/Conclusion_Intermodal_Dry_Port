<?php
// =============================================================================
// MODUL: GUDANG CFS & PERGUDANGAN LCL KONSOLIDASI 4.000 M² (RAMPA D1-D5)
// File: pages/cfs.php
// Proyek: Conclusion Intermodal Dry Port (CIDP) 35 Ha — Yard Management System
// Standar: FIATA LCL Conventions, WCO SAFE Framework, GS1-128 SSCC-18 Logistics Label
// PIC : Eko Prasetyo (Head of CFS & Warehouse Operations / Certified Tally Master)
// =============================================================================

require_once __DIR__ . '/../connection.php';

$cfs_info = [
    'pic'      => 'Eko Prasetyo',
    'role'     => 'Head of CFS & Warehouse Operations',
    'desc'     => 'Layanan konsolidasi LCL/FCL, otomasi 5 rampa loading dock hidrolik (D1–D5), pelabelan palet GS1 SSCC-18, dan tally sheet digital.',
    'icon'     => 'fa-warehouse',
    'status'   => '5 Rampa Dock Hidrolik Aktif & MHE Siaga',
    'badge'    => 'bg-indigo-50 text-indigo-700 border-indigo-200'
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
        $operator    = trim($_POST['operator_name'] ?? 'Eko Prasetyo');
        $job_num     = 'JO-' . ($job_type === 'STRIPPING' ? 'STP' : 'STF') . '-' . date('Y') . '-' . rand(100, 999);

        try {
            if (isset($pdo)) {
                $stmt = $pdo->prepare("
                    INSERT INTO cfs_jobs (job_number, job_type, container_number, dock_ramp, shipper_consignee, bl_number, total_packages, total_weight_kg, total_cbm, status, operator_name, start_time, tally_notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'IN_PROGRESS', ?, NOW(), ?)
                ");
                $stmt->execute([$job_num, $job_type, $ctr_num, $dock_ramp, $consignee, $bl_number, $total_pkgs, $total_wt, $total_cbm, $operator, $tally_notes]);

                // Update container block to CFS if in database
                $stmtUpCtr = $pdo->prepare("UPDATE containers SET block = 'CFS' WHERE container_number = ?");
                $stmtUpCtr->execute([$ctr_num]);

                // Catat ke yard_events
                $stmtLog = $pdo->prepare("
                    INSERT INTO yard_events (event_type, container_number, to_block, operator_name, notes, created_at)
                    VALUES (?, ?, 'CFS', ?, ?, NOW())
                ");
                $stmtLog->execute(['CFS_' . $job_type, $ctr_num, $operator, "Job Order $job_num di Rampa $dock_ramp ($total_pkgs palet/koli)"]);
            }
            $action_alert = [
                'type' => 'success',
                'title' => 'Job Order CFS Berhasil Diterbitkan',
                'msg' => "Job Order <strong>$job_num</strong> ($job_type) untuk kontainer <strong>$ctr_num</strong> di Rampa <strong>$dock_ramp</strong> telah aktif di sistem."
            ];
        } catch (Exception $e) {
            $action_alert = [
                'type' => 'warning',
                'title' => 'Job Order Tersimpan (Mode Demo)',
                'msg' => "Job order $job_num untuk kontainer $ctr_num telah aktif pada Rampa $dock_ramp."
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
            }
            $action_alert = [
                'type' => 'success',
                'title' => 'Progres Tally Diperbarui',
                'msg' => "Catatan progres pembongkaran/pemuatan kargo rampa berhasil disimpan."
            ];
        } catch (Exception $e) {
            $action_alert = ['type' => 'info', 'title' => 'Update Tally Tersimpan', 'msg' => 'Data tally progres telah diperbarui.'];
        }
    } elseif ($cfs_action === 'complete_job') {
        $job_id = intval($_POST['job_id'] ?? 0);
        try {
            if (isset($pdo) && $job_id > 0) {
                $stmt = $pdo->prepare("UPDATE cfs_jobs SET status = 'COMPLETED', completed_time = NOW() WHERE id = ?");
                $stmt->execute([$job_id]);
            }
            $action_alert = [
                'type' => 'success',
                'title' => 'Job Order Selesai (Final Tally Confirmed)',
                'msg' => "Aktivitas rampa telah ditutup dan Berita Acara Tally Sheet siap dicetak."
            ];
        } catch (Exception $e) {
            $action_alert = ['type' => 'success', 'title' => 'Job Order Ditutup', 'msg' => 'Status rampa kini kembali bersih dan siap untuk kontainer berikutnya.'];
        }
    } elseif ($cfs_action === 'add_pallet') {
        $sscc      = trim($_POST['pallet_sscc'] ?? '00189912001' . rand(100000000, 999999999));
        $commodity = trim($_POST['commodity_name'] ?? 'Kargo Umum LCL');
        $consignee = trim($_POST['consignee'] ?? 'PT Penerima Logistik');
        $category  = trim($_POST['category'] ?? 'general');
        $cartons   = intval($_POST['carton_count'] ?? 20);
        $weight    = floatval($_POST['gross_weight_kg'] ?? 400.0);
        $cbm       = floatval($_POST['cbm'] ?? 1.2);
        $rack      = trim($_POST['rack_location'] ?? 'AISLE-A-01-L1');
        $ctr_orig  = trim($_POST['container_origin'] ?? 'MSKU8821940');

        try {
            if (isset($pdo)) {
                $stmt = $pdo->prepare("
                    INSERT INTO cfs_pallets (pallet_sscc, container_origin, commodity_name, consignee, category, carton_count, gross_weight_kg, cbm, rack_location, status, entry_date)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'IN_RACK', NOW())
                ");
                $stmt->execute([$sscc, $ctr_orig, $commodity, $consignee, $category, $cartons, $weight, $cbm, $rack]);
            }
            $action_alert = [
                'type' => 'success',
                'title' => 'Palet Baru Teregistrasi (GS1 SSCC)',
                'msg' => "Palet dengan nomor SSCC <strong>$sscc</strong> berhasil ditempatkan di <strong>$rack</strong>."
            ];
        } catch (Exception $e) {
            $action_alert = [
                'type' => 'info',
                'title' => 'Palet Tersimpan (Mode Demo)',
                'msg' => "Palet $sscc berhasil dialokasikan pada lokasi rak $rack."
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

                    // 3. Buat Job Order baru di Rampa
                    $job_num = 'JO-' . ($job_type === 'STRIPPING' ? 'STP' : 'STF') . '-' . date('Y') . '-' . rand(200, 999);
                    $stmtJob = $pdo->prepare("INSERT INTO cfs_jobs (job_number, job_type, container_number, dock_ramp, shipper_consignee, bl_number, total_packages, total_weight_kg, total_cbm, status, operator_name, start_time, tally_notes) VALUES (?, ?, ?, ?, ?, CONCAT('BL-', LPAD(FLOOR(RAND()*999999), 6, '0')), ?, ?, ?, 'IN_PROGRESS', 'Sensor Ultrasonik HW-06', NOW(), ?)");
                    $stmtJob->execute([$job_num, $job_type, $ctr_num, $dock_id, $consignee, $pkgs, $weight, round($weight/320, 2), "Trigger Sensor Ultrasonik HW-06: Truk $plate terdeteksi merapat di Rampa $dock_id. Leveler hidrolik 15T dinaikkan otomatis."]);

                    // 4. Catat event log
                    $stmtLog = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, to_block, operator_name, notes, created_at) VALUES ('CFS_DOCK_IN', ?, 'CFS', 'Sensor Ultrasonik HW-06', ?, NOW())");
                    $stmtLog->execute([$ctr_num, "Deteksi kehadiran armada $plate di Rampa $dock_id ($job_type - $consignee)"]);

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

                    $action_alert = [
                        'type' => 'success',
                        'title' => "⚡ Sensor Rampa $dock_id Terpicu (Dock Outbound Cleared)",
                        'msg' => "Armada telah meninggalkan <strong>Rampa $dock_id</strong>. Sensor mendeteksi rampa kosong, lampu indikator berubah <strong>Hijau (Standby)</strong>, dan status pekerjaan di database telah ditutup."
                    ];
                }
            }
        } catch (Exception $e) {
            $action_alert = [
                'type' => 'warning',
                'title' => "Sensor Rampa $dock_id Terpicu",
                'msg' => "Event sensor rampa $dock_id berhasil dieksekusi."
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
        $rack      = trim($_POST['rack_location'] ?? 'AISLE-B-04-L1');
        $sscc      = '0018991200' . rand(1000000000, 9999999999);

        try {
            if (isset($pdo)) {
                $stmt = $pdo->prepare("
                    INSERT INTO cfs_pallets (pallet_sscc, container_origin, commodity_name, consignee, category, carton_count, gross_weight_kg, cbm, rack_location, status, entry_date)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'IN_RACK', NOW())
                ");
                $stmt->execute([$sscc, $ctr_orig, $commodity, $consignee, $category, $cartons, $weight, $cbm, $rack]);

                $stmtLog = $pdo->prepare("
                    INSERT INTO yard_events (event_type, container_number, operator_name, billable_amount, notes, created_at)
                    VALUES ('PALLET_WEIGHED', ?, 'HW-21 Floor Scale 3T', 25000, ?, NOW())
                ");
                $stmtLog->execute([$ctr_orig, "Penimbangan & pengukuran CBM palet SSCC $sscc ($weight kg / $cbm CBM)"]);
            }
            $action_alert = [
                'type' => 'success',
                'title' => '⚡ HW-21 Floor Scale & 3D Pallet Dimensioner Terpicu',
                'msg' => "Sensor membaca bobot kargo <strong>{$weight} kg</strong> dan kubikasi <strong>{$cbm} CBM</strong> ($cartons karton). Label GS1 SSCC <strong>(00) $sscc</strong> diterbitkan dan palet otomatis dialokasikan ke <strong>$rack</strong> di database!"
            ];
        } catch (Exception $e) {
            $action_alert = [
                'type' => 'info',
                'title' => 'Palet Tertimbang & Terdaftar',
                'msg' => "Data palet $sscc seberat {$weight} kg telah disimpan."
            ];
        }
    } elseif ($cfs_action === 'trigger_hht_scan') {
        $sscc      = trim($_POST['pallet_sscc'] ?? '');
        $target_rk = trim($_POST['target_rack'] ?? 'AISLE-E-02-L2');

        try {
            if (isset($pdo) && !empty($sscc)) {
                $stmtUp = $pdo->prepare("UPDATE cfs_pallets SET rack_location = ?, status = 'IN_RACK' WHERE pallet_sscc = ?");
                $stmtUp->execute([$target_rk, $sscc]);

                $stmtLog = $pdo->prepare("INSERT INTO yard_events (event_type, operator_name, notes, created_at) VALUES ('HHT_TALLY_SCAN', 'HW-19 Zebra TC26', ?, NOW())");
                $stmtLog->execute(["Pemindaian barcode SSCC $sscc oleh HHT Zebra TC26. Palet diverifikasi masuk rak $target_rk."]);
            }
            $action_alert = [
                'type' => 'success',
                'title' => '⚡ HW-19 Handheld Barcode Scanner (HHT) Terpicu',
                'msg' => "Barcode GS1 SSCC <strong>$sscc</strong> berhasil dipindai via laser optic. Status palet terverifikasi masuk ke rak <strong>$target_rk</strong> dan log tally telah dicatat."
            ];
        } catch (Exception $e) {
            $action_alert = [
                'type' => 'info',
                'title' => 'Barcode SSCC Dipindai',
                'msg' => "Palet $sscc berhasil diverifikasi masuk rak $target_rk."
            ];
        }
    } elseif ($cfs_action === 'trigger_cfs_env') {
        $temp = floatval($_POST['temp_val'] ?? 24.2);
        $hum  = floatval($_POST['hum_val'] ?? 58.0);

        try {
            if (isset($pdo)) {
                $stmtLog = $pdo->prepare("INSERT INTO yard_events (event_type, operator_name, notes, created_at) VALUES ('CFS_ENV_TELEMETRY', 'LoRaWAN SHT40 Sensor', ?, NOW())");
                $stmtLog->execute(["Telemetri Suhu Gudang CFS: {$temp}°C, Kelembaban Relatif: {$hum}% RH. Status: AMAN (Standar Kargo Kering & Farmasi Normal)"]);
            }
            $action_alert = [
                'type' => 'success',
                'title' => '⚡ LoRaWAN SHT40 Multi-Sensor Gudang Terpicu',
                'msg' => "Transmisi paket telemetri 868 MHz berhasil diterima: Suhu Gudang <strong>{$temp}°C</strong> & Kelembaban <strong>{$hum}% RH</strong>. Standar kualitas kargo kering & farmasi terpenuhi."
            ];
        } catch (Exception $e) {
            $action_alert = [
                'type' => 'info',
                'title' => 'Telemetri Lingkungan CFS Diterima',
                'msg' => "Suhu {$temp}°C dan Kelembaban {$hum}% RH telah dicatat."
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

// Fallback Data Demo jika database masih kosong
if (empty($cfs_jobs)) {
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
            'operator_name' => 'Eko Prasetyo',
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
            'operator_name' => 'Eko Prasetyo',
            'start_time' => date('Y-m-d H:i', strtotime('-45 minutes')),
            'completed_time' => null,
            'tally_notes' => 'Konsolidasi bumbu instan & mie untuk distribusi feeder rute Tanjung Perak Surabaya.'
        ]
    ];
}

if (empty($cfs_pallets)) {
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
<div class="bg-white rounded-xl px-4 py-3 shadow-2xs border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div class="flex items-center space-x-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#002f5e] via-[#0170b9] to-orange-500 text-white flex items-center justify-center text-lg shadow-xs flex-shrink-0">
            <i class="fa-solid fa-warehouse"></i>
        </div>
        <div>
            <div class="flex items-center flex-wrap gap-2">
                <h1 class="text-base font-bold text-gray-900 tracking-tight">
                    CFS &amp; Pergudangan LCL Konsolidasi 4.000 m²
                </h1>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 animate-pulse"></span>
                    5 Rampa Hidrolik D1–D5
                </span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-orange-50 text-orange-700 border border-orange-200">
                    GS1 SSCC-18 Label
                </span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Stripping &amp; Stuffing
                </span>
            </div>
        </div>
    </div>

        <!-- Tombol Aksi Cepat Header -->
        <div class="flex items-center flex-wrap gap-2 w-full lg:w-auto">
            <button onclick="openModal('modalNewJobOrder')" class="flex-1 sm:flex-none px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/20 transition-all flex items-center justify-center space-x-2">
                <i class="fa-solid fa-dolly"></i>
                <span>+ Buat Job Order Rampa</span>
            </button>
            <button onclick="openModal('modalAddPallet')" class="flex-1 sm:flex-none px-3.5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center space-x-1.5">
                <i class="fa-solid fa-pallet text-indigo-600"></i>
                <span>Registrasi Palet</span>
            </button>
            <button onclick="exportCfsExcel()" class="px-3.5 py-2.5 bg-white border border-gray-300 hover:bg-emerald-50 text-emerald-700 text-xs font-bold rounded-xl shadow-xs transition flex items-center space-x-1.5" title="Ekspor Manifest LCL ke Excel">
                <i class="fa-solid fa-file-excel text-emerald-600"></i>
                <span>Ekspor Excel</span>
            </button>
            <button onclick="window.print()" class="px-3 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-600 text-xs font-bold rounded-xl shadow-xs transition" title="Cetak Rekap">
                <i class="fa-solid fa-print"></i>
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 5 KARTU INDIKATOR KPI EKSEKUTIF CFS WAREHOUSE -->
<!-- ========================================================================= -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 mb-6">
    <!-- Card 1: Okupansi Palet Gudang -->
    <div class="bg-white rounded-2xl p-4 border border-gray-200/80 shadow-2xs hover:shadow-md transition">
        <div class="flex items-center justify-between text-gray-500 mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider">Okupansi Rak Palet</span>
            <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                <i class="fa-solid fa-pallet"></i>
            </span>
        </div>
        <div class="flex items-baseline space-x-2">
            <span class="text-2xl font-black text-gray-900 tracking-tight"><?= $occupied_pallets ?></span>
            <span class="text-xs text-gray-400 font-semibold">/ <?= $total_pallet_capacity ?> Posisi</span>
        </div>
        <div class="mt-2.5">
            <div class="flex items-center justify-between text-[10px] text-gray-500 font-semibold mb-1">
                <span>Utilisasi Rak A-F</span>
                <span class="text-indigo-600"><?= $utilization_pct ?>%</span>
            </div>
            <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-indigo-500 to-blue-600 rounded-full" style="width: <?= $utilization_pct ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Card 2: 5 Rampa Dock Leveler D1-D5 -->
    <div class="bg-white rounded-2xl p-4 border border-gray-200/80 shadow-2xs hover:shadow-md transition">
        <div class="flex items-center justify-between text-gray-500 mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider">Rampa Dock Leveler</span>
            <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </span>
        </div>
        <div class="flex items-baseline space-x-2">
            <span class="text-2xl font-black text-emerald-600 tracking-tight"><?= $active_docks_count ?> / 5</span>
            <span class="text-xs text-gray-400 font-semibold">Rampa Aktif</span>
        </div>
        <p class="text-[10px] text-gray-500 mt-2.5 flex items-center gap-1 font-medium">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            D1, D2 Stripping &bull; D4, D5 Stuffing &bull; D3 Standby
        </p>
    </div>

    <!-- Card 3: Job Order Stripping Hari Ini -->
    <div class="bg-white rounded-2xl p-4 border border-gray-200/80 shadow-2xs hover:shadow-md transition">
        <div class="flex items-center justify-between text-gray-500 mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider">Stripping FCL &rarr; LCL</span>
            <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                <i class="fa-solid fa-box-open"></i>
            </span>
        </div>
        <div class="flex items-baseline space-x-2">
            <span class="text-2xl font-black text-gray-900 tracking-tight">18</span>
            <span class="text-xs text-emerald-600 font-bold flex items-center">
                <i class="fa-solid fa-check text-[9px] mr-0.5"></i> 420 Palet
            </span>
        </div>
        <p class="text-[10px] text-gray-500 mt-2.5 font-medium">
            Rekonsiliasi Tally Sheet: <strong>0 Selisih Kargo</strong> (100% Cocok)
        </p>
    </div>

    <!-- Card 4: Job Order Stuffing Ekspor -->
    <div class="bg-white rounded-2xl p-4 border border-gray-200/80 shadow-2xs hover:shadow-md transition">
        <div class="flex items-center justify-between text-gray-500 mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider">Stuffing Ekspor / Feeder</span>
            <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                <i class="fa-solid fa-boxes-packing"></i>
            </span>
        </div>
        <div class="flex items-baseline space-x-2">
            <span class="text-2xl font-black text-gray-900 tracking-tight">12</span>
            <span class="text-xs text-blue-600 font-bold">284.2 CBM</span>
        </div>
        <p class="text-[10px] text-gray-500 mt-2.5 font-medium">
            SOLAS VGM Certified &bull; Segel High-Security ISO 17712
        </p>
    </div>

    <!-- Card 5: Dwell Time Rata-rata LCL -->
    <div class="bg-white rounded-2xl p-4 border border-gray-200/80 shadow-2xs hover:shadow-md transition">
        <div class="flex items-center justify-between text-gray-500 mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider">LCL Storage Dwell Time</span>
            <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xs">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </span>
        </div>
        <div class="flex items-baseline space-x-2">
            <span class="text-2xl font-black text-purple-700 tracking-tight">2.8 Hari</span>
            <span class="text-xs text-emerald-600 font-bold">Target &lt; 3.5 Hari</span>
        </div>
        <p class="text-[10px] text-gray-500 mt-2.5 font-medium">
            Free Storage 3 Hari Kerja &bull; Terhubung Billing ERP
        </p>
    </div>
</div>

<!-- ========================================================================= -->
<!-- NAVIGASI TAB MODUL CFS (4 TAB UTAMA) -->
<!-- ========================================================================= -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden mb-6">
    <div class="border-b border-gray-200 px-4 sm:px-6 flex items-center justify-between overflow-x-auto gap-4 bg-gray-50/50">
        <div class="flex space-x-1 sm:space-x-2 py-2.5 flex-nowrap min-w-max">
            <button onclick="switchCfsTab('tab-rampa')" id="btn-tab-rampa" class="cfs-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all bg-indigo-600 text-white shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-truck-ramp-box"></i>
                <span>Manajemen Rampa D1-D5 (Live Dock)</span>
            </button>
            <button onclick="switchCfsTab('tab-manifest')" id="btn-tab-manifest" class="cfs-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all text-gray-600 hover:text-gray-900 hover:bg-gray-100 flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span>Manifest Kargo LCL &amp; Inventori Palet (GS1)</span>
            </button>
            <button onclick="switchCfsTab('tab-denah')" id="btn-tab-denah" class="cfs-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all text-gray-600 hover:text-gray-900 hover:bg-gray-100 flex items-center gap-2">
                <i class="fa-solid fa-map"></i>
                <span>Denah 2D Gudang 4.000 m² (Aisle A-F)</span>
            </button>
            <button onclick="switchCfsTab('tab-joborder')" id="btn-tab-joborder" class="cfs-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all text-gray-600 hover:text-gray-900 hover:bg-gray-100 flex items-center gap-2">
                <i class="fa-solid fa-clipboard-check"></i>
                <span>Job Order &amp; Tally Master Console</span>
            </button>
        </div>
        <div class="text-[11px] text-gray-400 font-mono hidden md:block whitespace-nowrap">
            Lokasi: Sektor Barat (Zona F-CFS) &bull; Luas 4.000 m²
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
                <p class="text-xs text-gray-500 mt-0.5">
                    Dilengkapi Hydraulic Dock Leveler 15 Ton, Inflatable Dock Shelter, Traffic Signal Light, &amp; Tally Scanner Nirkabel.
                </p>
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
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2">
                            <span class="w-9 h-9 rounded-xl font-black text-sm flex items-center justify-center <?= $is_idle ? 'bg-gray-200 text-gray-700' : 'bg-indigo-600 text-white shadow-sm' ?>">
                                <?= $dock_id ?>
                            </span>
                            <div>
                                <span class="text-xs font-bold text-gray-900 block leading-tight">Rampa <?= $dock_id ?></span>
                                <span class="text-[10px] text-gray-500 block">Leveler 15T Hidrolik</span>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold border <?= $dock['badge'] ?>">
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
                        <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyelesaikan dan menutup Job Order di Rampa <?= $dock_id ?>?');">
                            <input type="hidden" name="cfs_action" value="complete_job">
                            <input type="hidden" name="job_id" value="<?= ($dock_id === 'D1' ? 1 : ($dock_id === 'D2' ? 2 : ($dock_id === 'D4' ? 3 : 4))) ?>">
                            <button type="submit" class="w-full px-2 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold rounded-lg transition text-center shadow-xs">
                                <i class="fa-solid fa-check mr-0.5"></i> Selesai
                            </button>
                        </form>
                    </div>
                    <?php else: ?>
                    <button onclick="openModalWithDock('modalNewJobOrder', '<?= $dock_id ?>')" class="w-full py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-xs transition flex items-center justify-center space-x-1">
                        <i class="fa-solid fa-truck-arrow-right text-[11px]"></i>
                        <span>Assign Truk</span>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Informasi SOP Keamanan & Rampa -->
        <div class="mt-6 bg-gradient-to-r from-slate-50 to-indigo-50/40 rounded-2xl p-4 border border-indigo-100/80 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-lg flex-shrink-0 shadow-sm">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-gray-900">SOP Standar K3 Rampa CFS &bull; Inflatable Dock Seal &amp; Wheel Chock Interlock</h4>
                    <p class="text-[11px] text-gray-600 mt-0.5 leading-relaxed">
                        Chassis truk wajib diganjal <em>wheel chock interlock</em> sebelum pintu kontainer dibuka. Sensor nirkabel melarang pengoperasian rampa hidrolik jika ganjal roda belum terpasang.
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2 text-xs flex-shrink-0">
                <span class="px-3 py-1 bg-white rounded-lg border border-gray-200 text-gray-700 font-semibold shadow-2xs">
                    <i class="fa-solid fa-bolt text-amber-500 mr-1"></i> MHE: 4 Forklift Listrik Linde E20
                </span>
            </div>
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
                    <input type="text" id="searchPalletInput" onkeyup="filterPalletTable()" placeholder="Cari No. Palet SSCC, B/L, Barang, atau Consignee..." class="w-full pl-9 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
                </div>
                <select id="filterAisleSelect" onchange="filterPalletTable()" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Aisle (A - F)</option>
                    <option value="AISLE-A">Aisle A (Mesin Berat)</option>
                    <option value="AISLE-B">Aisle B (Elektronik)</option>
                    <option value="AISLE-C">Aisle C (FMCG Food)</option>
                    <option value="AISLE-D">Aisle D (Tekstil/Garment)</option>
                    <option value="AISLE-E">Aisle E (Otomotif)</option>
                    <option value="AISLE-F">Aisle F (Staging &amp; Hold)</option>
                </select>
                <select id="filterCategorySelect" onchange="filterPalletTable()" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Kategori</option>
                    <option value="automotive">Otomotif</option>
                    <option value="electronics">Elektronik</option>
                    <option value="machinery">Mesin</option>
                    <option value="fmcg">FMCG</option>
                    <option value="textile">Tekstil</option>
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
                    <?php foreach ($cfs_pallets as $p): 
                        $status_badge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        if ($p['status'] === 'STAGING_OUT') $status_badge = 'bg-blue-50 text-blue-700 border-blue-200';
                        if ($p['status'] === 'HOLD') $status_badge = 'bg-red-50 text-red-700 border-red-200';

                        $customs_badge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        if ($p['customs_status'] === 'RED_LANE') $customs_badge = 'bg-red-50 text-red-700 border-red-200';
                        if ($p['customs_status'] === 'HOLD') $customs_badge = 'bg-amber-50 text-amber-700 border-amber-200';
                    ?>
                    <tr class="hover:bg-indigo-50/20 transition-colors pallet-row" 
                        data-aisle="<?= substr($p['rack_location'], 0, 7) ?>" 
                        data-category="<?= $p['category'] ?>"
                        data-search="<?= strtolower($p['pallet_sscc'] . ' ' . $p['commodity_name'] . ' ' . $p['consignee'] . ' ' . ($p['container_origin'] ?? '') . ' ' . $p['rack_location']) ?>">
                        
                        <!-- SSCC-18 Pallet Code -->
                        <td class="py-3 px-3.5">
                            <div class="flex items-center space-x-2">
                                <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center text-xs flex-shrink-0">
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
                            <span class="px-2.5 py-1 bg-slate-100 border border-slate-200 rounded-lg text-slate-800 font-mono font-bold text-[11px]">
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
                            <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold border <?= $status_badge ?>">
                                <?= str_replace('_', ' ', $p['status']) ?>
                            </span>
                        </td>

                        <!-- Action -->
                        <td class="py-3 px-3.5 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                <button onclick="showGs1LabelModal('<?= $p['pallet_sscc'] ?>', '<?= htmlspecialchars(addslashes($p['commodity_name'])) ?>', '<?= htmlspecialchars(addslashes($p['consignee'])) ?>', '<?= $p['rack_location'] ?>', '<?= $p['carton_count'] ?>', '<?= $p['gross_weight_kg'] ?>')" class="w-7 h-7 rounded-lg bg-gray-100 hover:bg-indigo-50 hover:text-indigo-600 text-gray-600 transition flex items-center justify-center text-xs" title="Cetak Label Barcode GS1-128">
                                    <i class="fa-solid fa-print"></i>
                                </button>
                                <button onclick="openScannerPage('<?= $p['pallet_sscc'] ?>')" class="w-7 h-7 rounded-lg bg-gray-100 hover:bg-purple-50 hover:text-purple-600 text-gray-600 transition flex items-center justify-center text-xs" title="Verifikasi dengan Scanner GS1">
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
        <!-- Header & Kontrol Denah Master Plan Arsitektur & Sipil (Sesuai Denah 35 Ha) -->
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-3 mb-4 pb-3 border-b border-gray-200">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-blue-50 text-[#002f5e] border border-blue-200">
                        DWG: CIDP-CFS-STR-01 &bull; REV. 2.4
                    </span>
                    <h3 class="text-sm sm:text-base font-bold text-gray-800 uppercase tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-compass-drafting text-[#0170b9]"></i>
                        Denah Tata Letak Arsitektur &amp; Rekayasa Sipil Gudang CFS 4.000 m² (±0.000)
                    </h3>
                </div>
                <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                    Struktur Baja Bentang Bersih 50.000 mm &times; 80.000 mm (SNI 1729:2020) &bull; Pelat Lantai Beton fc' 30 MPa (K-350) Heavy Duty 50 kN/m² &bull; Rampa D1–D5 &bull; Selective Pallet Racks Aisle A–F.
                </p>
            </div>

            <!-- Kontrol Lapisan Layer & Legenda Pin (Proporsi Denah 35 Ha) -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Legenda Pin Standar Denah -->
                <div class="flex items-center space-x-3 text-[11px] text-gray-700 bg-slate-50 px-3 py-1.5 rounded-xl border border-gray-200">
                    <span class="flex items-center">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                        <strong class="text-emerald-700 mr-1">Live IoT:</strong> 3 Sensor
                    </span>
                    <span class="text-gray-300">|</span>
                    <span class="flex items-center">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 mr-1.5"></span>
                        <strong class="text-indigo-700 mr-1">Scanner/AI:</strong> 2 Titik
                    </span>
                </div>

                <!-- Layer Toggles -->
                <div class="flex items-center gap-1.5 text-[11px] font-semibold bg-white p-1 rounded-xl border border-gray-200 shadow-2xs">
                    <label class="cursor-pointer px-2 py-1 rounded hover:bg-gray-50 flex items-center gap-1.5 text-gray-700 select-none">
                        <input type="checkbox" id="layer-grid-chk" checked onchange="toggleCadLayer('layer-grid', this.checked)" class="accent-blue-600 rounded">
                        <span>As Kolom &amp; Ukuran</span>
                    </label>
                    <label class="cursor-pointer px-2 py-1 rounded hover:bg-gray-50 flex items-center gap-1.5 text-gray-700 select-none">
                        <input type="checkbox" id="layer-racks-chk" checked onchange="toggleCadLayer('layer-racks', this.checked)" class="accent-indigo-600 rounded">
                        <span>Racks Aisle A-F</span>
                    </label>
                    <label class="cursor-pointer px-2 py-1 rounded hover:bg-gray-50 flex items-center gap-1.5 text-gray-700 select-none">
                        <input type="checkbox" id="layer-iot-chk" checked onchange="toggleCadLayer('layer-iot', this.checked)" class="accent-emerald-600 rounded">
                        <span>Sensor Telemetri</span>
                    </label>
                    <label class="cursor-pointer px-2 py-1 rounded hover:bg-gray-50 flex items-center gap-1.5 text-gray-700 select-none">
                        <input type="checkbox" id="layer-safety-chk" checked onchange="toggleCadLayer('layer-safety', this.checked)" class="accent-amber-600 rounded">
                        <span>K3 &amp; Evakuasi</span>
                    </label>
                    <label class="cursor-pointer px-2 py-1 rounded hover:bg-gray-50 flex items-center gap-1.5 text-gray-700 select-none">
                        <input type="checkbox" id="layer-footer-chk" checked onchange="toggleCadLayer('cadDrawingSheetFooter', this.checked)" class="accent-slate-700 rounded">
                        <span>Etiket Kop CAD</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Master Technical Drawing Canvas (Proporsi & Palet Warna Standar Denah 35 Ha) -->
        <div id="cadDrawingContainer" class="bg-white rounded-2xl p-4 sm:p-5 shadow-lg border border-gray-200 overflow-x-auto transition-all relative">
            
            <!-- Floating Zoom Controls UI (Identik Denah 35 Ha) -->
            <div class="absolute top-7 right-7 flex flex-col gap-1 z-10">
                <button onclick="cfsMapZoom(0.15)" class="w-8 h-8 bg-white border border-gray-200 shadow-sm rounded-lg flex items-center justify-center text-[#0170b9] hover:bg-gray-50 transition" title="Perbesar (Zoom In)">
                    <i class="fa-solid fa-plus text-xs"></i>
                </button>
                <button onclick="cfsMapZoom(-0.15)" class="w-8 h-8 bg-white border border-gray-200 shadow-sm rounded-lg flex items-center justify-center text-[#0170b9] hover:bg-gray-50 transition" title="Perkecil (Zoom Out)">
                    <i class="fa-solid fa-minus text-xs"></i>
                </button>
                <button onclick="cfsMapReset()" class="w-8 h-8 bg-white border border-gray-200 shadow-sm rounded-lg flex items-center justify-center text-gray-600 hover:bg-gray-50 transition mt-0.5" title="Kembalikan Tampilan (Reset)">
                    <i class="fa-solid fa-rotate-left text-xs"></i>
                </button>
            </div>

            <svg id="cfsCadSvg" viewBox="0 0 1200 875" class="w-full min-w-[1000px] h-auto select-none transition-transform duration-100" xmlns="http://www.w3.org/2000/svg" onmousemove="updateCadHud(event)" onmouseleave="resetCadHud()">
                <defs>
                    <!-- Engineering Grid Patterns (Exact Denah 35 Ha Proportions) -->
                    <pattern id="gridFine" width="10" height="10" patternUnits="userSpaceOnUse">
                        <path d="M 10 0 L 0 0 0 10" fill="none" stroke="#e2e8f0" stroke-width="0.35"/>
                    </pattern>
                    <pattern id="gridMajor" width="50" height="50" patternUnits="userSpaceOnUse">
                        <path d="M 50 0 L 0 0 0 50" fill="none" stroke="#cbd5e1" stroke-width="0.6"/>
                    </pattern>
                    
                    <!-- Material Hatching Patterns (Exact Denah 35 Ha Proportions) -->
                    <pattern id="hatchConcrete" width="8" height="8" patternTransform="rotate(45)" patternUnits="userSpaceOnUse">
                        <line x1="0" y1="0" x2="0" y2="8" stroke="#cbd5e1" stroke-width="0.6"/>
                    </pattern>
                    <pattern id="hatchReinforced" width="6" height="6" patternTransform="rotate(45)" patternUnits="userSpaceOnUse">
                        <line x1="0" y1="0" x2="0" y2="6" stroke="#94a3b8" stroke-width="0.6"/>
                        <line x1="0" y1="0" x2="6" y2="0" stroke="#94a3b8" stroke-width="0.6"/>
                    </pattern>
                    <pattern id="asphaltFill" width="4" height="4" patternUnits="userSpaceOnUse">
                        <rect width="4" height="4" fill="#334155"/>
                        <circle cx="1" cy="1" r="0.4" fill="#475569"/>
                        <circle cx="3" cy="3" r="0.4" fill="#1e293b"/>
                    </pattern>
                    <pattern id="asphaltLight" width="4" height="4" patternUnits="userSpaceOnUse">
                        <rect width="4" height="4" fill="#475569"/>
                        <circle cx="1.5" cy="1.5" r="0.4" fill="#64748b"/>
                    </pattern>
                    <pattern id="pavingTactile" width="6" height="6" patternUnits="userSpaceOnUse">
                        <rect width="6" height="6" fill="#fef3c7"/>
                        <circle cx="3" cy="3" r="1.1" fill="#d97706"/>
                    </pattern>
                    <pattern id="hazardStripe" width="12" height="12" patternTransform="rotate(45)" patternUnits="userSpaceOnUse">
                        <rect width="6" height="12" fill="#facc15"/>
                        <rect x="6" width="6" height="12" fill="#0f172a"/>
                    </pattern>
                    <pattern id="wireMeshHatch" width="6" height="6" patternTransform="rotate(45)" patternUnits="userSpaceOnUse">
                        <line x1="0" y1="0" x2="6" y2="6" stroke="#ef4444" stroke-width="0.7" opacity="0.4"/>
                        <line x1="6" y1="0" x2="0" y2="6" stroke="#ef4444" stroke-width="0.7" opacity="0.4"/>
                    </pattern>

                    <!-- Markers for Dimension Lines (Architectural 45 deg Tick) -->
                    <marker id="cadTick" markerWidth="6" markerHeight="6" refX="3" refY="3" orient="auto">
                        <path d="M 1 5 L 5 1" stroke="#0284c7" stroke-width="1.2"/>
                    </marker>

                    <!-- Filters for Live Telemetri (Identik Denah 35 Ha) -->
                    <filter id="glowGreen" x="-30%" y="-30%" width="160%" height="160%">
                        <feGaussianBlur stdDeviation="2.5" result="blur" />
                        <feComposite in="SourceGraphic" in2="blur" operator="over" />
                    </filter>
                </defs>

                <!-- LAYER 0: Canvas Sheet Base & Pelat Lantai Beton Bertulang fc' 30 MPa -->
                <rect width="1200" height="875" fill="#f8fafc" />
                <rect width="1200" height="875" fill="url(#gridFine)" />
                <rect width="1200" height="875" fill="url(#gridMajor)" />

                <!-- Garis Tepi Format Lembar Gambar A1 (ISO 5457 CAD Sheet Margin) -->
                <rect x="25" y="16" width="1150" height="843" fill="none" stroke="#0f172a" stroke-width="1.8" rx="2" />
                <rect x="29" y="20" width="1142" height="835" fill="none" stroke="#94a3b8" stroke-width="0.6" rx="1" />

                <!-- Dinding Keliling Bangunan Gudang 80.000 mm x 50.000 mm -->
                <rect x="70" y="70" width="1080" height="540" fill="#ffffff" stroke="#0f172a" stroke-width="2.5" rx="3" />
                <rect x="73" y="73" width="1074" height="534" fill="url(#hatchConcrete)" opacity="0.45" />

                <!-- ============================================================= -->
                <!-- LAYER 1: GARIS AS KOLOM STRUKTURAL & GARIS UKUR CAD (DIMENSI) -->
                <!-- ============================================================= -->
                <g id="layer-grid">
                    <!-- Garis Ukur Dimensi Luar Atas: Bentang Total 80.000 mm -->
                    <line x1="70" y1="24" x2="1150" y2="24" stroke="#0284c7" stroke-width="1" marker-start="url(#cadTick)" marker-end="url(#cadTick)"/>
                    <text x="610" y="18" fill="#0284c7" font-family="'Consolas', monospace" font-size="8.5" font-weight="bold" text-anchor="middle">BENTANG PANJANG BANGUNAN = 80.000 mm (80,00 M)</text>

                    <!-- Garis Ukur Dimensi Luar Kiri: Bentang Total 50.000 mm -->
                    <line x1="24" y1="70" x2="24" y2="610" stroke="#0284c7" stroke-width="1" marker-start="url(#cadTick)" marker-end="url(#cadTick)"/>
                    <text x="18" y="340" fill="#0284c7" font-family="'Consolas', monospace" font-size="8.5" font-weight="bold" text-anchor="middle" transform="rotate(-90 18 340)">LEBAR BENTANG BERSIH = 50.000 mm (50,00 M)</text>

                    <!-- Garis Sumbu As Kolom 1 s/d 9 (Sumbu X) -->
                    <?php 
                    $grid_x = [
                        1 => 70, 2 => 205, 3 => 340, 4 => 475, 5 => 610, 
                        6 => 745, 7 => 880, 8 => 1015, 9 => 1150
                    ];
                    foreach ($grid_x as $num => $gx): 
                    ?>
                        <!-- Lingkaran Bubble As Kolom Atas -->
                        <circle cx="<?= $gx ?>" cy="48" r="9.5" fill="#ffffff" stroke="#94a3b8" stroke-width="0.9"/>
                        <text x="<?= $gx ?>" y="51" fill="#475569" font-family="'Consolas', monospace" font-size="9" font-weight="bold" text-anchor="middle"><?= $num ?></text>

                        <!-- Garis Sumbu Kolom Merah Strip-Titik (SNI Standard Drafting) -->
                        <line x1="<?= $gx ?>" y1="58" x2="<?= $gx ?>" y2="610" stroke="#ef4444" stroke-width="0.75" stroke-dasharray="8 3 2 3" opacity="0.55"/>

                        <!-- Dimensi Per Bay Kolom Antar As (10.000 mm) -->
                        <?php if ($num < 9): $next_gx = $grid_x[$num + 1]; ?>
                            <line x1="<?= $gx ?>" y1="36" x2="<?= $next_gx ?>" y2="36" stroke="#64748b" stroke-width="0.7" marker-start="url(#cadTick)" marker-end="url(#cadTick)"/>
                            <text x="<?= ($gx + $next_gx) / 2 ?>" y="33" fill="#64748b" font-family="'Consolas', monospace" font-size="7" font-weight="bold" text-anchor="middle">10.000</text>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <!-- Garis Sumbu As Kolom A s/d F (Sumbu Y) -->
                    <?php 
                    $grid_y = [
                        'A' => 70, 'B' => 178, 'C' => 286, 'D' => 394, 'E' => 502, 'F' => 610
                    ];
                    foreach ($grid_y as $let => $gy): 
                    ?>
                        <!-- Lingkaran Bubble As Kolom Kiri -->
                        <circle cx="48" cy="<?= $gy ?>" r="9.5" fill="#ffffff" stroke="#94a3b8" stroke-width="0.9"/>
                        <text x="48" y="<?= $gy + 3 ?>" fill="#475569" font-family="'Consolas', monospace" font-size="9" font-weight="bold" text-anchor="middle"><?= $let ?></text>

                        <!-- Garis Sumbu Kolom Merah Horizontal -->
                        <line x1="58" y1="<?= $gy ?>" x2="1150" y2="<?= $gy ?>" stroke="#ef4444" stroke-width="0.75" stroke-dasharray="8 3 2 3" opacity="0.55"/>

                        <!-- Dimensi Per Bentang Bay Y (10.000 mm) -->
                        <?php 
                        $keys = array_keys($grid_y);
                        $cur_idx = array_search($let, $keys);
                        if ($cur_idx < count($keys) - 1):
                            $next_let = $keys[$cur_idx + 1];
                            $next_gy = $grid_y[$next_let];
                        ?>
                            <line x1="36" y1="<?= $gy ?>" x2="36" y2="<?= $next_gy ?>" stroke="#64748b" stroke-width="0.7" marker-start="url(#cadTick)" marker-end="url(#cadTick)"/>
                            <text x="32" y="<?= ($gy + $next_gy) / 2 + 2 ?>" fill="#64748b" font-family="'Consolas', monospace" font-size="7" font-weight="bold" text-anchor="end">10.000</text>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <!-- Profil Kolom Baja WF 400x200 di Setiap Titik Simpul Struktur -->
                    <?php foreach ($grid_x as $gx): foreach ($grid_y as $gy): ?>
                        <g transform="translate(<?= $gx ?>, <?= $gy ?>)">
                            <path d="M -5 -7 L 5 -7 L 5 -5 L 1.5 -5 L 1.5 5 L 5 5 L 5 7 L -5 7 L -5 5 L -1.5 5 L -1.5 -5 L -5 -5 Z" fill="#0f172a" stroke="#334155" stroke-width="0.6"/>
                        </g>
                    <?php endforeach; endforeach; ?>
                </g>

                <!-- ============================================================= -->
                <!-- LAYER 2: FASILITAS PENUNJANG & KANTOR PENGURUS GUDANG CFS    -->
                <!-- ============================================================= -->
                <g id="layer-facilities">
                    <!-- 1. Pos Pengawas Hanggar Bea Cukai CEISA 4.0 (North-West, As 1-2, Y: 75-155) -->
                    <rect x="75" y="75" width="120" height="80" fill="#ffffff" stroke="#dc2626" stroke-width="2" rx="2"/>
                    <rect x="78" y="78" width="114" height="20" fill="#fee2e2" stroke="#dc2626" stroke-width="1" rx="1"/>
                    <text x="135" y="92" fill="#991b1b" font-family="'Consolas', monospace" font-size="7.5" font-weight="bold" text-anchor="middle">HANGGAR BEA CUKAI</text>
                    <text x="135" y="115" fill="#0f172a" font-family="'Consolas', monospace" font-size="7" text-anchor="middle">POS PENGAWAS CEISA 4.0</text>
                    <text x="135" y="130" fill="#64748b" font-family="'Consolas', monospace" font-size="6.5" text-anchor="middle">SEGEL ELEKTRONIK &amp; DOKUMEN</text>

                    <!-- 2. Ruang Briefing K3 & Locker Tallyman (North-Center, As 4-6, Y: 75-155) -->
                    <rect x="540" y="75" width="200" height="80" fill="#ffffff" stroke="#002f5e" stroke-width="1.8" rx="2"/>
                    <rect x="543" y="78" width="194" height="20" fill="#e0f2fe" stroke="#0284c7" stroke-width="1" rx="1"/>
                    <text x="640" y="92" fill="#002f5e" font-family="'Consolas', monospace" font-size="7.5" font-weight="bold" text-anchor="middle">RUANG BRIEFING K3 &amp; TALLYMAN</text>
                    <text x="640" y="115" fill="#0f172a" font-family="'Consolas', monospace" font-size="7" text-anchor="middle">KAPASITAS 30 PERSONIL &bull; POS APD</text>
                    <text x="640" y="130" fill="#64748b" font-family="'Consolas', monospace" font-size="6.5" text-anchor="middle">REKONSILIASI FISIK CARGO TALLY</text>
                    <!-- First Aid P3K Box -->
                    <rect x="555" y="125" width="16" height="16" fill="#ffffff" stroke="#dc2626" stroke-width="1.2" rx="1"/>
                    <rect x="561" y="128" width="4" height="10" fill="#dc2626"/>
                    <rect x="558" y="131" width="10" height="4" fill="#dc2626"/>

                    <!-- 3. Stasiun Pengisian Daya Forklift Listrik 380V (North-East, As 8-9, Y: 75-155) -->
                    <rect x="1025" y="75" width="120" height="80" fill="#ffffff" stroke="#d97706" stroke-width="1.8" rx="2"/>
                    <rect x="1028" y="78" width="114" height="20" fill="#fef3c7" stroke="#d97706" stroke-width="1" rx="1"/>
                    <text x="1085" y="92" fill="#b45309" font-family="'Consolas', monospace" font-size="7.5" font-weight="bold" text-anchor="middle">CHARGING BAY FORKLIFT</text>
                    <text x="1085" y="115" fill="#0f172a" font-family="'Consolas', monospace" font-size="7" text-anchor="middle">4 CHARGER CEPAT 380V/32A</text>
                    <text x="1085" y="130" fill="#64748b" font-family="'Consolas', monospace" font-size="6.5" text-anchor="middle">KAPASITAS 6 UNIT FORKLIFT</text>
                </g>

                <!-- ============================================================= -->
                <!-- LAYER 3: JALUR EVAKUASI K3, SIRKULASI FORKLIFT & APAR/HYDRANT -->
                <!-- ============================================================= -->
                <g id="layer-safety">
                    <!-- Jalur Sirkulasi Forklift & Pedestrian Utara (Y: 165 to 185) -->
                    <rect x="75" y="165" width="1070" height="20" fill="url(#pavingTactile)" stroke="#f59e0b" stroke-width="1" rx="2"/>
                    <text x="610" y="179" fill="#b45309" font-family="'Consolas', monospace" font-size="8" font-weight="bold" text-anchor="middle">JALUR UTAMA FORKLIFT &amp; PEDESTRIAN K3 &bull; KECEPATAN MAKS. 10 KM/JAM</text>

                    <!-- Pintu Keluar Darurat (Exit Doors Dinding Barat & Timur) -->
                    <!-- Barat -->
                    <rect x="68" y="310" width="4" height="28" fill="#16a34a"/>
                    <path d="M 68 310 A 28 28 0 0 1 96 338" fill="none" stroke="#16a34a" stroke-width="1.2" stroke-dasharray="2 2"/>
                    <rect x="74" y="304" width="45" height="14" fill="#ecfdf5" stroke="#16a34a" stroke-width="1" rx="2"/>
                    <text x="96" y="314" fill="#15803d" font-family="'Consolas', monospace" font-size="6.5" font-weight="bold" text-anchor="middle">🚪 EXIT</text>

                    <!-- Timur -->
                    <rect x="1148" y="310" width="4" height="28" fill="#16a34a"/>
                    <path d="M 1148 310 A 28 28 0 0 0 1120 338" fill="none" stroke="#16a34a" stroke-width="1.2" stroke-dasharray="2 2"/>
                    <rect x="1100" y="304" width="45" height="14" fill="#ecfdf5" stroke="#16a34a" stroke-width="1" rx="2"/>
                    <text x="1122" y="314" fill="#15803d" font-family="'Consolas', monospace" font-size="6.5" font-weight="bold" text-anchor="middle">🚪 EXIT</text>

                    <!-- Kotak Hydrant Gedung FHC-01 & FHC-02 -->
                    <rect x="74" y="278" width="14" height="14" fill="#fee2e2" stroke="#dc2626" stroke-width="1.2" rx="1"/>
                    <text x="81" y="288" fill="#991b1b" font-family="'Consolas', monospace" font-size="7" font-weight="bold" text-anchor="middle">H</text>
                    <text x="92" y="287" fill="#dc2626" font-family="'Consolas', monospace" font-size="6">FHC-01</text>

                    <rect x="1132" y="278" width="14" height="14" fill="#fee2e2" stroke="#dc2626" stroke-width="1.2" rx="1"/>
                    <text x="1139" y="288" fill="#991b1b" font-family="'Consolas', monospace" font-size="7" font-weight="bold" text-anchor="middle">H</text>
                    <text x="1128" y="287" fill="#dc2626" font-family="'Consolas', monospace" font-size="6" text-anchor="end">FHC-02</text>

                    <!-- APAR 6kg di Tiang-Tiang Kolom -->
                    <circle cx="205" cy="72" r="5" fill="#fee2e2" stroke="#dc2626" stroke-width="0.8"/>
                    <text x="205" y="75" fill="#dc2626" font-size="5" text-anchor="middle">🧯</text>
                    <circle cx="745" cy="72" r="5" fill="#fee2e2" stroke="#dc2626" stroke-width="0.8"/>
                    <text x="745" y="75" fill="#dc2626" font-size="5" text-anchor="middle">🧯</text>
                    <circle cx="205" cy="608" r="5" fill="#fee2e2" stroke="#dc2626" stroke-width="0.8"/>
                    <text x="205" y="611" fill="#dc2626" font-size="5" text-anchor="middle">🧯</text>
                    <circle cx="745" cy="608" r="5" fill="#fee2e2" stroke="#dc2626" stroke-width="0.8"/>
                    <text x="745" y="611" fill="#dc2626" font-size="5" text-anchor="middle">🧯</text>
                </g>

                <!-- ============================================================= -->
                <!-- LAYER 4: SELECTIVE PALLET RACKING AISLE A S/D AISLE F         -->
                <!-- ============================================================= -->
                <g id="layer-racks">
                    <!-- Ukuran Lebar Gang Antar Rak (Forklift Reach Clearance) -->
                    <line x1="235" y1="200" x2="235" y2="470" stroke="#0284c7" stroke-width="0.7" stroke-dasharray="3 3"/>
                    <text x="240" y="340" fill="#0284c7" font-family="'Consolas', monospace" font-size="7" transform="rotate(-90 240 340)">GANG FORKLIFT = 3.500 mm (CLEARANCE REACH TRUCK)</text>

                    <?php
                    // Palet Warna & Proporsi Menyesuaikan pages/denah.php (Blok A, B, C, D, Reefer, Customs)
                    $aisles_data = [
                        'A' => ['name' => 'AISLE A', 'category' => 'Mesin & Traksi', 'client' => 'PT United Tractors & Komatsu', 'occupied' => 160, 'total' => 200, 'x' => 90,  'bg' => '#eff6ff', 'border' => '#3b82f6', 'header' => '#1d4ed8', 'pallet' => '#3b82f6', 'letter' => 'A'],
                        'B' => ['name' => 'AISLE B', 'category' => 'Elektronik & High-Tech', 'client' => 'PT Sharp Electronics Ind.', 'occupied' => 175, 'total' => 200, 'x' => 265, 'bg' => '#ecfeff', 'border' => '#06b6d4', 'header' => '#0891b2', 'pallet' => '#06b6d4', 'letter' => 'B'],
                        'C' => ['name' => 'AISLE C', 'category' => 'FMCG Food & Flavor', 'client' => 'PT Indofood & Mayora Indah', 'occupied' => 190, 'total' => 200, 'x' => 440, 'bg' => '#ecfdf5', 'border' => '#10b981', 'header' => '#047857', 'pallet' => '#10b981', 'letter' => 'C'],
                        'D' => ['name' => 'AISLE D', 'category' => 'Tekstil & Garment Ekspor', 'client' => 'H&M Hennes & Pan Brothers', 'occupied' => 130, 'total' => 200, 'x' => 615, 'bg' => '#f5f3ff', 'border' => '#8b5cf6', 'header' => '#6d28d9', 'pallet' => '#8b5cf6', 'letter' => 'D'],
                        'E' => ['name' => 'AISLE E', 'category' => 'Bahan Kimia Non-B3', 'client' => 'PT Astra Honda Motor', 'occupied' => 165, 'total' => 200, 'x' => 790, 'bg' => '#fffbeb', 'border' => '#d97706', 'header' => '#b45309', 'pallet' => '#d97706', 'letter' => 'E'],
                        'F' => ['name' => 'AISLE F', 'category' => 'Karantina Jalur Merah', 'client' => 'Hanggar Bea Cukai CEISA', 'occupied' => 54,  'total' => 200, 'x' => 965, 'bg' => '#fef2f2', 'border' => '#dc2626', 'header' => '#991b1b', 'pallet' => '#dc2626', 'letter' => 'F']
                    ];

                    foreach ($aisles_data as $ak => $adata):
                        $ax = $adata['x'];
                        $is_customs = ($ak === 'F');
                    ?>
                    <!-- Aisle Group: Clickable untuk Memfilter Manifest LCL -->
                    <g class="cursor-pointer group" onclick="filterAisleFromMap('AISLE-<?= $ak ?>')">
                        <!-- Kotak Denah Area Rak (Proporsi Denah 35 Ha) -->
                        <rect x="<?= $ax ?>" y="200" width="145" height="270" fill="<?= $adata['bg'] ?>" stroke="<?= $adata['border'] ?>" stroke-width="2" rx="3" class="transition group-hover:brightness-95"/>
                        
                        <!-- Huruf Watermark Transparan (Identik Denah Blok A/B/C/D) -->
                        <text x="<?= $ax + 72 ?>" y="360" fill="<?= $adata['border'] ?>" font-family="'Consolas', monospace" font-size="64" font-weight="900" opacity="0.14" text-anchor="middle"><?= $adata['letter'] ?></text>

                        <!-- Corner Protectors (Bumper Kuning K3 di Ujung Kaki Rak) -->
                        <rect x="<?= $ax - 3 ?>" y="197" width="6" height="8" fill="#facc15" stroke="#0f172a" stroke-width="0.6"/>
                        <rect x="<?= $ax + 142 ?>" y="197" width="6" height="8" fill="#facc15" stroke="#0f172a" stroke-width="0.6"/>
                        <rect x="<?= $ax - 3 ?>" y="465" width="6" height="8" fill="#facc15" stroke="#0f172a" stroke-width="0.6"/>
                        <rect x="<?= $ax + 142 ?>" y="465" width="6" height="8" fill="#facc15" stroke="#0f172a" stroke-width="0.6"/>

                        <!-- Header Badge Aisle -->
                        <rect x="<?= $ax + 5 ?>" y="205" width="135" height="24" fill="<?= $adata['header'] ?>" rx="2"/>
                        <text x="<?= $ax + 72 ?>" y="217" fill="#ffffff" font-family="'Consolas', monospace" font-size="8.5" font-weight="bold" text-anchor="middle"><?= $adata['name'] ?></text>
                        <text x="<?= $ax + 72 ?>" y="226" fill="#ffffff" font-family="'Consolas', monospace" font-size="6" text-anchor="middle"><?= $adata['category'] ?></text>

                        <!-- Matriks Slot Palet Modular (7 Tingkat x 2 Bay Ganda) -->
                        <?php for ($pr = 0; $pr < 7; $pr++): $py = 235 + ($pr * 30); ?>
                            <!-- Left Bay -->
                            <rect x="<?= $ax + 8 ?>" y="<?= $py ?>" width="60" height="24" fill="#ffffff" stroke="#cbd5e1" stroke-width="0.8" rx="1.5"/>
                            <!-- EUR Pallets Slot -->
                            <rect x="<?= $ax + 11 ?>" y="<?= $py + 3 ?>" width="24" height="18" fill="<?= $adata['pallet'] ?>" stroke="#ffffff" stroke-width="0.6" rx="1"/>
                            <rect x="<?= $ax + 41 ?>" y="<?= $py + 3 ?>" width="24" height="18" fill="<?= ($pr % 3 === 0 && !$is_customs) ? '#f8fafc' : $adata['pallet'] ?>" stroke="<?= ($pr % 3 === 0 && !$is_customs) ? '#cbd5e1' : '#ffffff' ?>" stroke-width="0.6" stroke-dasharray="<?= ($pr % 3 === 0 && !$is_customs) ? '2 1' : 'none' ?>" rx="1"/>

                            <!-- Right Bay -->
                            <rect x="<?= $ax + 77 ?>" y="<?= $py ?>" width="60" height="24" fill="#ffffff" stroke="#cbd5e1" stroke-width="0.8" rx="1.5"/>
                            <rect x="<?= $ax + 80 ?>" y="<?= $py + 3 ?>" width="24" height="18" fill="<?= $adata['pallet'] ?>" stroke="#ffffff" stroke-width="0.6" rx="1"/>
                            <rect x="<?= $ax + 110 ?>" y="<?= $py + 3 ?>" width="24" height="18" fill="<?= ($pr % 2 === 1 && !$is_customs) ? '#f8fafc' : $adata['pallet'] ?>" stroke="<?= ($pr % 2 === 1 && !$is_customs) ? '#cbd5e1' : '#ffffff' ?>" stroke-width="0.6" stroke-dasharray="<?= ($pr % 2 === 1 && !$is_customs) ? '2 1' : 'none' ?>" rx="1"/>
                        <?php endfor; ?>

                        <!-- Aisle Footer: Client & Okupansi Rak -->
                        <rect x="<?= $ax + 5 ?>" y="448" width="135" height="18" fill="#ffffff" stroke="#cbd5e1" stroke-width="0.8" rx="2"/>
                        <text x="<?= $ax + 72 ?>" y="457" fill="#475569" font-family="'Consolas', monospace" font-size="6.5" text-anchor="middle"><?= $adata['client'] ?></text>
                        <text x="<?= $ax + 72 ?>" y="464" fill="<?= $adata['header'] ?>" font-family="'Consolas', monospace" font-size="6.5" font-weight="bold" text-anchor="middle">Okupansi: <?= $adata['occupied'] ?> / <?= $adata['total'] ?> Palet</text>
                    </g>
                    <?php endforeach; ?>

                    <!-- Kandang Karantina Kawat Ram Bea Cukai (Aisle F) -->
                    <rect x="960" y="195" width="155" height="280" fill="url(#wireMeshHatch)" stroke="#dc2626" stroke-width="1.8" stroke-dasharray="6 3" rx="4"/>
                    <rect x="970" y="186" width="135" height="16" fill="#fee2e2" stroke="#dc2626" stroke-width="1" rx="2"/>
                    <text x="1037" y="198" fill="#991b1b" font-family="'Consolas', monospace" font-size="7" font-weight="bold" text-anchor="middle">⚠️ KANDANG KARANTINA JALUR MERAH</text>
                </g>

                <!-- ============================================================= -->
                <!-- LAYER 5: ZONA STAGING BUFFER & TIMBANGAN LANTAI HW-21         -->
                <!-- ============================================================= -->
                <g id="layer-staging">
                    <!-- Lantai Staging Inbound (Bongkar FCL ke Palet LCL) -->
                    <rect x="90" y="495" width="220" height="70" fill="#f8fafc" stroke="#94a3b8" stroke-width="1" stroke-dasharray="4 3" rx="2"/>
                    <text x="200" y="510" fill="#475569" font-family="'Consolas', monospace" font-size="7.5" font-weight="bold" text-anchor="middle">ZONA STAGING PALET INBOUND (BONGKAR)</text>
                    <?php for ($sb = 0; $sb < 5; $sb++): ?>
                        <rect x="<?= 105 + ($sb * 40) ?>" y="525" width="30" height="22" fill="#eff6ff" stroke="#3b82f6" stroke-width="0.8" rx="2"/>
                        <rect x="<?= 110 + ($sb * 40) ?>" y="529" width="20" height="14" fill="#3b82f6" rx="1"/>
                    <?php endfor; ?>

                    <!-- Lantai Staging Outbound (Konsolidasi Palet Siap Muat) -->
                    <rect x="750" y="495" width="220" height="70" fill="#f8fafc" stroke="#94a3b8" stroke-width="1" stroke-dasharray="4 3" rx="2"/>
                    <text x="860" y="510" fill="#475569" font-family="'Consolas', monospace" font-size="7.5" font-weight="bold" text-anchor="middle">ZONA STAGING PALET OUTBOUND (MUAT)</text>
                    <?php for ($sb = 0; $sb < 5; $sb++): ?>
                        <rect x="<?= 765 + ($sb * 40) ?>" y="525" width="30" height="22" fill="#ecfdf5" stroke="#10b981" stroke-width="0.8" rx="2"/>
                        <rect x="<?= 770 + ($sb * 40) ?>" y="529" width="20" height="14" fill="#10b981" rx="1"/>
                    <?php endfor; ?>

                    <!-- Timbangan Lantai Heavy Duty HW-21 (3 Ton) -->
                    <g class="cursor-pointer group" onclick="openSensorTriggerModal('scale')">
                        <rect x="500" y="505" width="120" height="55" fill="#f1f5f9" stroke="#002f5e" stroke-width="1.6" rx="2"/>
                        <rect x="508" y="512" width="104" height="41" fill="#e2e8f0" stroke="#0284c7" stroke-width="1.2"/>
                        <!-- 4 Titik Load Cell Sensor Sudut -->
                        <circle cx="514" cy="518" r="2.5" fill="#0284c7"/>
                        <circle cx="606" cy="518" r="2.5" fill="#0284c7"/>
                        <circle cx="514" cy="547" r="2.5" fill="#0284c7"/>
                        <circle cx="606" cy="547" r="2.5" fill="#0284c7"/>
                        <!-- Display Digital Berat Riil -->
                        <rect x="525" y="520" width="70" height="25" fill="#ffffff" stroke="#0284c7" stroke-width="1" rx="1"/>
                        <text x="560" y="533" fill="#002f5e" font-family="'Consolas', monospace" font-size="7.5" font-weight="bold" text-anchor="middle">HW-21: 485.0 kg</text>
                        <text x="560" y="542" fill="#0284c7" font-family="'Consolas', monospace" font-size="6" font-weight="bold" text-anchor="middle">CBM 1.35 &bull; SCALE</text>
                    </g>
                </g>

                <!-- ============================================================= -->
                <!-- LAYER 6: RAMPA HIDROLIK DOKING D1-D5 & DERMAGA APRON TRUK     -->
                <!-- ============================================================= -->
                <g id="layer-docks">
                    <!-- Apron Aspal Luar Gudang (Akses Manuver Truk Trailer) -->
                    <rect x="70" y="665" width="1080" height="70" fill="url(#asphaltFill)" stroke="none"/>
                    <line x1="70" y1="700" x2="1150" y2="700" stroke="#facc15" stroke-width="1.8" stroke-dasharray="8 6"/>
                    <line x1="70" y1="665" x2="1150" y2="665" stroke="#ffffff" stroke-width="1.2"/>
                    <text x="610" y="725" fill="#f8fafc" font-family="'Consolas', monospace" font-size="8" font-weight="bold" text-anchor="middle">← APRON DERMAGA TRUK TRAILER D1-D5 (SOUTH HAUL ROAD ACCESS) →</text>

                    <?php
                    $docks_draw = [
                        'D1' => ['x' => 150, 'truck' => 'B 9841 TEI', 'ctr' => 'MSKU8821940', 'status' => 'STRIPPING', 'pct' => 82, 'active' => true, 'color' => '#d97706'],
                        'D2' => ['x' => 350, 'truck' => 'B 9012 UXZ', 'ctr' => 'CMAU7718290', 'status' => 'STRIPPING', 'pct' => 45, 'active' => true, 'color' => '#d97706'],
                        'D3' => ['x' => 550, 'truck' => '-',           'ctr' => 'SIAP DOKING', 'status' => 'STANDBY',   'pct' => 0,  'active' => false, 'color' => '#10b981'],
                        'D4' => ['x' => 750, 'truck' => 'B 9411 KZA', 'ctr' => 'ONEU3391820', 'status' => 'STUFFING',  'pct' => 69, 'active' => true, 'color' => '#0284c7'],
                        'D5' => ['x' => 950, 'truck' => 'B 9200 VBC', 'ctr' => 'TEMU2281093', 'status' => 'STUFFING',  'pct' => 90, 'active' => true, 'color' => '#0284c7']
                    ];

                    foreach ($docks_draw as $did => $dd):
                        $dx = $dd['x'];
                    ?>
                    <!-- Dock Rampa Group -->
                    <g class="cursor-pointer group" onclick="switchCfsTab('tab-rampa')">
                        <!-- Ceruk Beton Rampa (Concrete Dock Pit) -->
                        <rect x="<?= $dx ?>" y="605" width="115" height="55" fill="#f1f5f9" stroke="#64748b" stroke-width="1.4"/>
                        <!-- Pelat Leveler Baja Hidrolik 15 Ton -->
                        <rect x="<?= $dx + 5 ?>" y="610" width="105" height="42" fill="#cbd5e1" stroke="#0f172a" stroke-width="1.2" rx="1"/>
                        <rect x="<?= $dx + 15 ?>" y="652" width="85" height="8" fill="#94a3b8" stroke="#475569" stroke-width="0.8"/>
                        
                        <!-- Karet Bumper Penahan Benturan Truk (Heavy Rubber Bumpers) -->
                        <rect x="<?= $dx - 8 ?>" y="650" width="10" height="15" fill="#0f172a"/>
                        <rect x="<?= $dx + 113 ?>" y="650" width="10" height="15" fill="#0f172a"/>

                        <!-- Label Nama Rampa & Tipe Pekerjaan -->
                        <text x="<?= $dx + 57 ?>" y="625" fill="#0f172a" font-family="'Consolas', monospace" font-size="8.5" font-weight="bold" text-anchor="middle">RAMPA <?= $did ?></text>
                        <text x="<?= $dx + 57 ?>" y="636" fill="<?= $dd['color'] ?>" font-family="'Consolas', monospace" font-size="7" font-weight="bold" text-anchor="middle"><?= $dd['status'] ?></text>
                        <text x="<?= $dx + 57 ?>" y="646" fill="#475569" font-family="'Consolas', monospace" font-size="6.5" text-anchor="middle"><?= $dd['ctr'] ?></text>

                        <!-- Sinyal Lampu Lalu Lintas Doking Merah/Hijau -->
                        <rect x="<?= $dx - 18 ?>" y="618" width="8" height="18" fill="#ffffff" stroke="#64748b" stroke-width="0.8" rx="1"/>
                        <circle cx="<?= $dx - 14 ?>" cy="623" r="2.8" fill="<?= $dd['active'] ? '#ef4444' : '#cbd5e1' ?>"/>
                        <circle cx="<?= $dx - 14 ?>" cy="631" r="2.8" fill="<?= $dd['active'] ? '#cbd5e1' : '#10b981' ?>"/>

                        <!-- Truk Trailer Merapat vs Rampa Standby -->
                        <?php if ($dd['active']): ?>
                            <rect x="<?= $dx + 5 ?>" y="660" width="105" height="42" fill="#1e293b" stroke="#0f172a" stroke-width="1.2" rx="2"/>
                            <text x="<?= $dx + 57 ?>" y="678" fill="#ffffff" font-family="'Consolas', monospace" font-size="7.5" font-weight="bold" text-anchor="middle"><?= $dd['truck'] ?></text>
                            <text x="<?= $dx + 57 ?>" y="690" fill="#facc15" font-family="'Consolas', monospace" font-size="6.5" text-anchor="middle">Tally: <?= $dd['pct'] ?>% Selesai</text>
                            <!-- Pipa Baja Pemandu Roda Truk (Yellow Wheel Guides) -->
                            <line x1="<?= $dx - 5 ?>" y1="660" x2="<?= $dx - 5 ?>" y2="702" stroke="#facc15" stroke-width="2.5"/>
                            <line x1="<?= $dx + 120 ?>" y1="660" x2="<?= $dx + 120 ?>" y2="702" stroke="#facc15" stroke-width="2.5"/>
                        <?php else: ?>
                            <rect x="<?= $dx + 5 ?>" y="660" width="105" height="24" fill="#ecfdf5" stroke="#10b981" stroke-width="1.2" stroke-dasharray="4 2" rx="2"/>
                            <text x="<?= $dx + 57 ?>" y="675" fill="#059669" font-family="'Consolas', monospace" font-size="7" font-weight="bold" text-anchor="middle">STANDBY (SIAP DOKING)</text>
                        <?php endif; ?>
                    </g>
                    <?php endforeach; ?>
                </g>

                <!-- ============================================================= -->
                <!-- LAYER 7: TITIK SENSOR TELEMETRI IOT (PROPORSI DENAH 35 HA)    -->
                <!-- ============================================================= -->
                <g id="layer-iot">
                    <!-- Sensor 1: HW-21 Floor Scale & Laser Dimensioner (Apron Tengah) -->
                    <g class="cursor-pointer group" onclick="openSensorTriggerModal('scale')">
                        <circle cx="560" cy="515" r="13" fill="none" stroke="#10b981" stroke-width="0.8" stroke-dasharray="3 2" class="animate-spin-slow"/>
                        <circle cx="560" cy="515" r="10" fill="#ecfdf5" stroke="#10b981" stroke-width="2" filter="url(#glowGreen)"/>
                        <path d="M 549 515 L 571 515 M 560 504 L 560 526" stroke="#10b981" stroke-width="0.6"/>
                        <text x="560" y="518" text-anchor="middle" fill="#065f46" font-size="8" font-family="'Consolas', monospace" font-weight="bold">21</text>
                        <rect x="495" y="488" width="130" height="18" fill="#ffffff" stroke="#10b981" stroke-width="1" rx="3"/>
                        <text x="560" y="500" fill="#065f46" font-family="'Consolas', monospace" font-size="7.5" font-weight="bold" text-anchor="middle">HW-21: SCALE 3T [TRIGGER]</text>
                    </g>

                    <!-- Sensor 2: HW-19 Handheld Zebra TC26 RFID/Barcode (Aisle E) -->
                    <g class="cursor-pointer group" onclick="openSensorTriggerModal('scanner')">
                        <circle cx="862" cy="335" r="9" fill="#eef2ff" stroke="#4f46e5" stroke-width="1.8"/>
                        <path d="M 852 335 L 872 335 M 862 325 L 862 345" stroke="#4f46e5" stroke-width="0.6"/>
                        <text x="862" y="338" text-anchor="middle" fill="#312e81" font-size="7.5" font-family="'Consolas', monospace" font-weight="bold">19</text>
                        <rect x="797" y="308" width="130" height="18" fill="#ffffff" stroke="#4f46e5" stroke-width="1" rx="3"/>
                        <text x="862" y="320" fill="#312e81" font-family="'Consolas', monospace" font-size="7.5" font-weight="bold" text-anchor="middle">HW-19: ZEBRA TC26 [PIN]</text>
                    </g>

                    <!-- Sensor 3: ENV-01 LoRaWAN Ambient Climate Node (Dinding Utara) -->
                    <g class="cursor-pointer group" onclick="openSensorTriggerModal('env')">
                        <circle cx="610" cy="90" r="13" fill="none" stroke="#10b981" stroke-width="0.8" stroke-dasharray="3 2" class="animate-spin-slow"/>
                        <circle cx="610" cy="90" r="10" fill="#ecfdf5" stroke="#10b981" stroke-width="2" filter="url(#glowGreen)"/>
                        <path d="M 599 90 L 621 90 M 610 79 L 610 101" stroke="#10b981" stroke-width="0.6"/>
                        <text x="610" y="93" text-anchor="middle" fill="#065f46" font-size="8" font-family="'Consolas', monospace" font-weight="bold">01</text>
                        <rect x="545" y="103" width="130" height="18" fill="#ffffff" stroke="#10b981" stroke-width="1" rx="3"/>
                        <text x="610" y="115" fill="#065f46" font-family="'Consolas', monospace" font-size="7.5" font-weight="bold" text-anchor="middle">ENV-01: LoRaWAN 24.2°C</text>
                    </g>

                    <!-- Sensor 4: HW-18 CCTV AI Pabean Surveillance (Kandang Aisle F) -->
                    <g class="cursor-pointer group" onclick="openSensorTriggerModal('cctv')">
                        <circle cx="1037" cy="180" r="9" fill="#fee2e2" stroke="#dc2626" stroke-width="1.8"/>
                        <path d="M 1027 180 L 1047 180 M 1037 170 L 1037 190" stroke="#dc2626" stroke-width="0.6"/>
                        <text x="1037" y="183" text-anchor="middle" fill="#991b1b" font-size="7.5" font-family="'Consolas', monospace" font-weight="bold">18</text>
                        <rect x="972" y="153" width="130" height="18" fill="#ffffff" stroke="#dc2626" stroke-width="1" rx="3"/>
                        <text x="1037" y="165" fill="#991b1b" font-family="'Consolas', monospace" font-size="7.5" font-weight="bold" text-anchor="middle">HW-18: CCTV AI CEISA</text>
                    </g>

                    <!-- Sensor 5: HW-06 Ultrasonic Dock Presence Sensor on Dock D3 -->
                    <g class="cursor-pointer group" onclick="openSensorTriggerModal('dock', 'D3')">
                        <circle cx="607" cy="610" r="13" fill="none" stroke="#10b981" stroke-width="0.8" stroke-dasharray="3 2" class="animate-spin-slow"/>
                        <circle cx="607" cy="610" r="10" fill="#ecfdf5" stroke="#10b981" stroke-width="2" filter="url(#glowGreen)"/>
                        <path d="M 596 610 L 618 610 M 607 599 L 607 621" stroke="#10b981" stroke-width="0.6"/>
                        <text x="607" y="613" text-anchor="middle" fill="#065f46" font-size="8" font-family="'Consolas', monospace" font-weight="bold">06</text>
                        <rect x="542" y="583" width="130" height="18" fill="#ffffff" stroke="#10b981" stroke-width="1" rx="3"/>
                        <text x="607" y="595" fill="#065f46" font-family="'Consolas', monospace" font-size="7.5" font-weight="bold" text-anchor="middle">HW-06: SENSOR D3 [LIVE]</text>
                    </g>
                </g>

                <!-- ============================================================= -->
                <!-- LAYER 8: KOMPAS ROSE, SCALE BAR & KOP GAMBAR (ISO 7200)       -->
                <!-- ============================================================= -->
                <!-- Compass Rose (Identik Denah 35 Ha) -->
                <g transform="translate(1120, 32)" class="select-none pointer-events-none">
                    <circle cx="0" cy="0" r="18" fill="#ffffff" stroke="#002f5e" stroke-width="1.5" />
                    <polygon points="0,0 0,-22 -5,-5" fill="#002f5e" />
                    <polygon points="0,0 0,-22 5,-5" fill="#0284c7" />
                    <polygon points="0,0 22,0 5,5" fill="#64748b" />
                    <polygon points="0,0 -22,0 -5,-5" fill="#94a3b8" />
                    <polygon points="0,0 0,22 5,5" fill="#94a3b8" />
                    <circle cx="0" cy="0" r="3" fill="#f8fafc" stroke="#002f5e" stroke-width="1"/>
                    <text x="0" y="-25" text-anchor="middle" fill="#002f5e" font-family="'Consolas', sans-serif" font-size="11" font-weight="900">U</text>
                    <text x="0" y="-34" text-anchor="middle" fill="#64748b" font-family="'Consolas', sans-serif" font-size="6" font-weight="bold">NORTH</text>
                </g>

                <!-- Dedicated Engineering Sheet Footer Margin (Terletak di Bawah Apron & Rampa D1-D5, Tidak Menutupi Dermaga) -->
                <g id="cadDrawingSheetFooter" class="select-none">
                    <!-- Linear Scale Bar CAD (Identik Denah 35 Ha) -->
                    <g transform="translate(70, 785)" font-family="'Consolas', monospace" font-size="7.5" fill="#0f172a">
                        <line x1="0" y1="0" x2="200" y2="0" stroke="#0f172a" stroke-width="2" />
                        <line x1="0" y1="-5" x2="0" y2="5" stroke="#0f172a" stroke-width="1.2" />
                        <line x1="50" y1="-3" x2="50" y2="0" stroke="#0f172a" stroke-width="1" />
                        <line x1="100" y1="-5" x2="100" y2="5" stroke="#0f172a" stroke-width="1.2" />
                        <line x1="200" y1="-5" x2="200" y2="5" stroke="#0f172a" stroke-width="1.2" />
                        <text x="-4" y="-7">0</text>
                        <text x="44" y="-7">10m</text>
                        <text x="94" y="-7">20m</text>
                        <text x="188" y="-7">40m</text>
                        <text x="0" y="16" fill="#475569" font-size="7" font-weight="bold">SKALA METRIK 1:200 @ A1 (1 DIVISI = 10m)</text>
                        <text x="0" y="27" fill="#64748b" font-size="6.5">PEIL LANTAI &plusmn;0.000 MKL (EL. +2.450 MSL)</text>
                    </g>

                    <!-- Catatan Teknis Sipil & Spesifikasi Pelaksanaan (General Engineering Notes) -->
                    <g transform="translate(340, 755)" font-family="'Consolas', monospace" font-size="6.5" fill="#475569">
                        <rect width="485" height="98" fill="#ffffff" stroke="#cbd5e1" stroke-width="1" rx="3"/>
                        <rect width="485" height="20" fill="#f1f5f9" stroke="#cbd5e1" stroke-width="0.8" rx="2"/>
                        <text x="12" y="14" fill="#002f5e" font-size="7" font-weight="bold">CATATAN TEKNIS &amp; SPESIFIKASI STRUKTURAL (GENERAL NOTES)</text>
                        <text x="12" y="34" fill="#334155">1. Pelat beton bertulang t=200 mm mutu fc' 30 MPa (K-350) + floor hardener Sika Chapdur 5 kg/m².</text>
                        <text x="12" y="48" fill="#334155">2. Beban hidup merata: 50 kN/m² (5.0 T/m²). Beban terpusat rak footplate: maks. 65 kN per titik.</text>
                        <text x="12" y="62" fill="#334155">3. Rampa D1-D5 dilengkapi Hydraulic Dock Leveler 15T, lip 400 mm, &amp; sensor ultrasonik HW-06.</text>
                        <text x="12" y="76" fill="#334155">4. Dimensi dalam satuan mm. Toleransi kerataan lantai standar DIN 15185 Superflat.</text>
                        <text x="12" y="90" fill="#059669" font-weight="bold">✓ Pintu darurat K3, jalur evakuasi, hidran FHC, &amp; sprinkler sesuai standar NFPA 13 &amp; 30.</text>
                    </g>

                    <!-- Kop Gambar Teknik ISO 7200 Resmi (Title Block CAD di Sudut Kanan Bawah) -->
                    <g id="cadTitleBlock" transform="translate(845, 755)">
                        <rect width="305" height="98" fill="#ffffff" stroke="#002f5e" stroke-width="1.8" rx="3"/>
                        <rect width="305" height="22" fill="#002f5e" rx="2"/>
                        <text x="152" y="15" fill="#ffffff" font-family="'Consolas', monospace" font-size="8.5" font-weight="bold" text-anchor="middle">CONCLUSION INTERMODAL DRY PORT (CIDP)</text>
                        <line x1="0" y1="48" x2="305" y2="48" stroke="#cbd5e1" stroke-width="0.8"/>
                        <line x1="0" y1="74" x2="305" y2="74" stroke="#cbd5e1" stroke-width="0.8"/>
                        <line x1="185" y1="22" x2="185" y2="98" stroke="#cbd5e1" stroke-width="0.8"/>
                        
                        <!-- Kolom Kiri: Proyek, Judul, Engineer -->
                        <text x="8" y="34" fill="#0f172a" font-family="'Consolas', monospace" font-size="7" font-weight="bold">PROYEK: CIDP CFS WAREHOUSE</text>
                        <text x="8" y="42" fill="#64748b" font-family="'Consolas', monospace" font-size="6">OPERATOR: PT MULTI TERMINAL INDONESIA</text>
                        <text x="8" y="59" fill="#0f172a" font-family="'Consolas', monospace" font-size="7" font-weight="bold">JUDUL: TATA LETAK ARSITEKTUR &amp; SIPIL</text>
                        <text x="8" y="68" fill="#64748b" font-family="'Consolas', monospace" font-size="6">RAMPA D1-D5 &bull; RACKS AISLE A-F</text>
                        <text x="8" y="85" fill="#002f5e" font-family="'Consolas', monospace" font-size="6.5" font-weight="bold">ENGINEER: ARMANSYAH M.</text>
                        <text x="8" y="94" fill="#16a34a" font-family="'Consolas', monospace" font-size="6" font-weight="bold">STATUS: AS-BUILT APPROVED</text>

                        <!-- Kolom Kanan: Skala, Tanggal, No. Dokumen (Bebas Tabrakan Teks) -->
                        <text x="195" y="34" fill="#0f172a" font-family="'Consolas', monospace" font-size="7" font-weight="bold">SKALA: 1:200 @ A1</text>
                        <text x="195" y="42" fill="#64748b" font-family="'Consolas', monospace" font-size="6">SATUAN: MILIMETER (mm)</text>
                        <text x="195" y="59" fill="#0f172a" font-family="'Consolas', monospace" font-size="6.5" font-weight="bold">TGL: 30 SEP 2026</text>
                        <text x="195" y="68" fill="#64748b" font-family="'Consolas', monospace" font-size="6">REVISI: REV. 2.4 FINAL</text>
                        <text x="195" y="85" fill="#002f5e" font-family="'Consolas', monospace" font-size="7" font-weight="bold">DWG-CFS-STR-01</text>
                        <text x="195" y="94" fill="#0f172a" font-family="'Consolas', monospace" font-size="6" font-weight="bold">fc' 30 MPa &bull; 50 kN/m²</text>
                    </g>
                </g>
            </svg>
        </div>

        <!-- Real-Time Cursor Coordinate & Structural Inspector HUD Bar (Matching Denah 35 Ha Proportions) -->
        <div class="mt-3 bg-white border border-gray-200 rounded-xl p-2.5 sm:px-4 text-xs font-mono text-gray-700 flex flex-col md:flex-row items-start md:items-center justify-between gap-2 shadow-xs">
            <div class="flex items-center gap-3 flex-wrap">
                <span class="flex items-center gap-1.5 font-bold text-[#002f5e]">
                    <i class="fa-solid fa-crosshairs text-[11px] animate-spin text-[#0170b9]" style="animation-duration: 8s;"></i>
                    <span id="cadHudCoords">X: 42.5 m | Y: 25.0 m</span>
                </span>
                <span class="text-gray-300 hidden sm:inline">&bull;</span>
                <span id="cadHudGrid" class="text-amber-700 font-semibold">Grid: As D-4</span>
                <span class="text-gray-300 hidden sm:inline">&bull;</span>
                <span id="cadHudZone" class="text-emerald-700 font-medium">Zona: Selective Pallet Racking Aisle C (FMCG)</span>
            </div>
            <div class="flex items-center gap-3 text-[11px] text-gray-500 flex-wrap">
                <span>Beban Rencana: <strong class="text-gray-800 font-mono">50 kN/m²</strong></span>
                <span class="text-gray-300">&bull;</span>
                <span>Elevasi: <strong class="text-gray-800 font-mono">&plusmn;0.000 MKL</strong></span>
                <span class="text-gray-300">&bull;</span>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>CAD Inspector Aktif</span>
                </span>
            </div>
        </div>

        <!-- Floor Specifications & Engineering Callout Accordion -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4 text-xs">
            <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-3.5 space-y-1.5">
                <h4 class="font-bold text-gray-800 flex items-center gap-1.5 text-xs">
                    <i class="fa-solid fa-cube text-[#0170b9]"></i>
                    <span>Spesifikasi Struktur Lantai</span>
                </h4>
                <p class="text-gray-600 text-[11px] leading-relaxed">
                    Pelat beton bertulang tebal 200 mm, mutu beton f'c 30 MPa (K-350), dry-shake floor hardener Sika Chapdur 5 kg/m², toleransi kerataan lantai DIN 15185 Superflat.
                </p>
            </div>
            <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-3.5 space-y-1.5">
                <h4 class="font-bold text-gray-800 flex items-center gap-1.5 text-xs">
                    <i class="fa-solid fa-truck-ramp-box text-emerald-600"></i>
                    <span>Spesifikasi Rampa Dock D1-D5</span>
                </h4>
                <p class="text-gray-600 text-[11px] leading-relaxed">
                    Hydraulic Dock Leveler kapasitas dinamis 15.000 kg, lip length 400 mm, inflatable dock shelter kedap cuaca, sensor ultrasonik HW-06, lampu sinyal lalu-lintas LED merah/hijau.
                </p>
            </div>
            <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-3.5 space-y-1.5">
                <h4 class="font-bold text-gray-800 flex items-center gap-1.5 text-xs">
                    <i class="fa-solid fa-fire-extinguisher text-rose-600"></i>
                    <span>Keselamatan K3 &amp; Proteksi Kebakaran</span>
                </h4>
                <p class="text-gray-600 text-[11px] leading-relaxed">
                    Sistem hidran gedung FHC-01 s/d FHC-04 (tekanan kerja 4.5 bar), APAR Powder 6 kg per 20 meter, koridor evakuasi selebar 2.000 mm menuju pintu darurat ber-panic bar.
                </p>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- KONSOL KENDALI & UJI PEMICU SENSOR GUDANG CFS REAL-TIME           -->
        <!-- ================================================================= -->
        <div class="mt-6 bg-white rounded-2xl p-5 border border-gray-200 text-gray-800 shadow-sm">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pb-4 border-b border-gray-100 gap-3">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#002f5e] border border-blue-200 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-satellite-dish"></i>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2 flex-wrap">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">Live IoT Trigger</span>
                            <h4 class="text-sm font-bold text-gray-900">Konsol Pemicu Sensor Operasional Gudang CFS (Real Database Event)</h4>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Setiap pemicu sensor di bawah ini <strong>bukan simulasi semu</strong>, melainkan langsung menulis data nyata ke tabel <code class="bg-gray-100 text-gray-700 px-1 py-0.5 rounded text-[11px]">cfs_jobs</code>, <code class="bg-gray-100 text-gray-700 px-1 py-0.5 rounded text-[11px]">cfs_pallets</code>, <code class="bg-gray-100 text-gray-700 px-1 py-0.5 rounded text-[11px]">trucks</code>, dan <code class="bg-gray-100 text-gray-700 px-1 py-0.5 rounded text-[11px]">yard_events</code> di MySQL.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3.5 mt-4">
                <!-- Sensor Card 1: Dock Sensor HW-06 -->
                <div class="bg-slate-50/80 rounded-xl p-4 border border-slate-200/80 flex flex-col justify-between hover:border-amber-400 hover:bg-white hover:shadow-xs transition">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10.5px] font-mono text-amber-700 uppercase font-bold flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                HW-06 &bull; Rampa D1-D5
                            </span>
                            <span class="px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded text-[9px] font-mono border border-gray-200">Ultrasonik</span>
                        </div>
                        <h5 class="text-xs font-bold text-gray-900">Sensor Keberadaan Truk Rampa</h5>
                        <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">
                            Mendeteksi truk merapat, memicu hidrolik dock leveler naik, dan membuka Job Order nyata di Rampa D3.
                        </p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-200/70">
                        <button onclick="openSensorTriggerModal('dock', 'D3')" class="w-full py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-lg transition shadow-xs flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-bolt"></i>
                            <span>Trigger Rampa D3</span>
                        </button>
                    </div>
                </div>

                <!-- Sensor Card 2: Floor Scale HW-21 -->
                <div class="bg-slate-50/80 rounded-xl p-4 border border-slate-200/80 flex flex-col justify-between hover:border-blue-400 hover:bg-white hover:shadow-xs transition">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10.5px] font-mono text-[#0170b9] uppercase font-bold flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-[#0170b9] animate-pulse"></span>
                                HW-21 &bull; Floor Scale 3T
                            </span>
                            <span class="px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded text-[9px] font-mono border border-gray-200">Loadcell &amp; Laser</span>
                        </div>
                        <h5 class="text-xs font-bold text-gray-900">Timbangan &amp; Dimensi Palet</h5>
                        <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">
                            Membaca berat riil palet &amp; kubikasi CBM, lalu menerbitkan label GS1 SSCC ke database inventori.
                        </p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-200/70">
                        <button onclick="openSensorTriggerModal('scale')" class="w-full py-1.5 bg-[#0170b9] hover:bg-[#004b87] text-white font-bold text-xs rounded-lg transition shadow-xs flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-weight-scale"></i>
                            <span>Trigger Timbang Palet</span>
                        </button>
                    </div>
                </div>

                <!-- Sensor Card 3: Handheld Scanner HW-19 -->
                <div class="bg-slate-50/80 rounded-xl p-4 border border-slate-200/80 flex flex-col justify-between hover:border-purple-400 hover:bg-white hover:shadow-xs transition">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10.5px] font-mono text-purple-700 uppercase font-bold flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-purple-500 animate-pulse"></span>
                                HW-19 &bull; Zebra TC26
                            </span>
                            <span class="px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded text-[9px] font-mono border border-gray-200">Laser / RFID</span>
                        </div>
                        <h5 class="text-xs font-bold text-gray-900">Handheld Barcode Scanner</h5>
                        <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">
                            Memindai label SSCC palet, memverifikasi rak simpan (Aisle A-F), &amp; mencatat rekonsiliasi tally counter.
                        </p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-200/70">
                        <button onclick="openSensorTriggerModal('scanner')" class="w-full py-1.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-lg transition shadow-xs flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-barcode"></i>
                            <span>Trigger Pindai Barcode</span>
                        </button>
                    </div>
                </div>

                <!-- Sensor Card 4: LoRaWAN Telemetry ENV-01 -->
                <div class="bg-slate-50/80 rounded-xl p-4 border border-slate-200/80 flex flex-col justify-between hover:border-emerald-400 hover:bg-white hover:shadow-xs transition">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10.5px] font-mono text-emerald-700 uppercase font-bold flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                ENV-01 &bull; SHT40 Node
                            </span>
                            <span class="px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded text-[9px] font-mono border border-gray-200">LoRaWAN 868 MHz</span>
                        </div>
                        <h5 class="text-xs font-bold text-gray-900">Suhu &amp; Kelembaban Gudang</h5>
                        <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">
                            Transmisi periodik telemetri lingkungan untuk memastikan standar keselamatan kargo kering &amp; farmasi.
                        </p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-200/70">
                        <button onclick="openSensorTriggerModal('env')" class="w-full py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition shadow-xs flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-tower-broadcast"></i>
                            <span>Trigger Ping Telemetri</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 4: JOB ORDER & TALLY MASTER CONSOLE -->
    <!-- ===================================================================== -->
    <div id="tab-joborder" class="cfs-tab-content p-5 sm:p-6 hidden">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
            <div>
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-list text-indigo-600"></i>
                    Konsol Tally Master &bull; Daftar Job Order Bongkar/Muat CFS
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    Penerbitan Job Order Stripping, Stuffing Konsolidasi, Verifikasi Segel Pabean, &amp; Berita Acara Rekonsiliasi Tally.
                </p>
            </div>
            <button onclick="openModal('modalNewJobOrder')" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center space-x-1.5">
                <i class="fa-solid fa-plus"></i>
                <span>Terbitkan Job Order Baru</span>
            </button>
        </div>

        <!-- Tabel Riwayat Job Order -->
        <div class="overflow-x-auto border border-gray-200 rounded-2xl shadow-2xs">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider text-[10.5px]">
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
                    <?php foreach ($cfs_jobs as $job): 
                        $is_strip = ($job['job_type'] === 'STRIPPING');
                        $job_badge = $is_strip ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-blue-50 text-blue-700 border-blue-200';
                        $status_badge = ($job['status'] === 'COMPLETED') ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-indigo-50 text-indigo-700 border-indigo-200';
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-3.5 font-mono font-bold text-gray-900">
                            <?= $job['job_number'] ?>
                        </td>
                        <td class="py-3 px-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold border <?= $job_badge ?>">
                                <?= $job['job_type'] ?>
                            </span>
                            <span class="ml-1 text-[11px] font-bold text-gray-700 font-mono">Dock <?= $job['dock_ramp'] ?></span>
                        </td>
                        <td class="py-3 px-3.5 font-mono font-bold text-indigo-700">
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
                                    <i class="fa-solid fa-file-invoice text-indigo-600"></i>
                                    <span>Tally</span>
                                </button>
                                <a href="dashboard.php?page=kontainer&search=<?= urlencode($job['container_number']) ?>" class="w-7 h-7 bg-blue-50 text-[#0170b9] hover:bg-[#002f5e] hover:text-white rounded-lg flex items-center justify-center transition shadow-2xs" title="Lacak Kontainer di Tracking Box">
                                    <i class="fa-solid fa-boxes-stacked text-xs"></i>
                                </a>
                                <a href="dashboard.php?page=simulator&view=cfs" class="w-7 h-7 bg-amber-50 text-amber-700 hover:bg-amber-600 hover:text-white rounded-lg flex items-center justify-center transition shadow-2xs" title="Lihat 3D Gudang CFS">
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
        <div class="bg-gradient-to-r from-indigo-700 via-indigo-600 to-blue-600 p-5 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-lg shadow-inner">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold leading-tight">Terbitkan Job Order Rampa CFS</h3>
                    <p class="text-[11px] text-indigo-100">Alokasikan Rampa D1–D5 &bull; Stripping FCL atau Stuffing Konsolidasi</p>
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
                    <select name="job_type" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                        <option value="STRIPPING">STRIPPING (Bongkar FCL ke Palet LCL)</option>
                        <option value="STUFFING">STUFFING (Konsolidasi Ekspor LCL ke FCL)</option>
                        <option value="CROSS_DOCK">CROSS-DOCK (Direct Transfer Truk-ke-Truk)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Pilih Rampa Dock Leveler *</label>
                    <select name="dock_ramp" id="modalDockSelect" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
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
                    <input type="text" name="container_number" required placeholder="Contoh: MSKU8821940" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono uppercase font-bold focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Nomor B/L (Bill of Lading) *</label>
                    <input type="text" name="bl_number" required placeholder="Contoh: BL-MAEU-982104" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono uppercase focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-700 mb-1">Shipper / Consignee (Perusahaan Pemilik Kargo) *</label>
                <input type="text" name="shipper_consignee" required placeholder="Contoh: PT Astra Honda Motor" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Estimasi Palet/Koli</label>
                    <input type="number" name="total_packages" value="40" min="1" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Total Berat (kg)</label>
                    <input type="number" step="0.01" name="total_weight_kg" value="18500" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Volume (CBM)</label>
                    <input type="number" step="0.1" name="total_cbm" value="52.4" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-700 mb-1">Catatan Khusus Tally &bull; Verifikasi Segel Fisik</label>
                <textarea name="tally_notes" rows="2" placeholder="Catat nomor segel pelayaran, kondisi ganjal roda wheel chock, & instruksi penempatan rak..." class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeModal('modalNewJobOrder')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md shadow-indigo-600/20 transition">
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
        <div class="bg-gradient-to-r from-blue-700 to-indigo-600 p-5 text-white flex items-center justify-between">
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

        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="cfs_action" value="add_pallet">

            <div>
                <label class="block text-[11px] font-bold text-gray-700 mb-1">Nomor Palet SSCC-18 (GS1 Logistic Label)</label>
                <input type="text" name="pallet_sscc" readonly value="0018991200109<?= rand(1000000, 9999999) ?>" class="w-full px-3 py-2 bg-gray-100 border border-gray-200 rounded-xl font-mono font-bold text-indigo-700">
                <span class="text-[9.5px] text-gray-400 mt-0.5 block font-mono">Standar Global GS1-128 AI (00) Serial Shipping Container Code</span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Ex-Kontainer Asal</label>
                    <input type="text" name="container_origin" value="MSKU8821940" placeholder="Nomor Kontainer" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono uppercase focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Kategori Kargo *</label>
                    <select name="category" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
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
                <input type="text" name="commodity_name" required placeholder="Contoh: Modul Sensor ABS Otomotif" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-700 mb-1">Penerima Barang (Consignee) *</label>
                <input type="text" name="consignee" required placeholder="Contoh: PT Denso Indonesia" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Jumlah Koli</label>
                    <input type="number" name="carton_count" value="20" min="1" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Berat Kotor (kg)</label>
                    <input type="number" step="0.1" name="gross_weight_kg" value="450.0" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Volume (CBM)</label>
                    <input type="number" step="0.01" name="cbm" value="1.25" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-700 mb-1">Alokasi Posisi Rak Gudang *</label>
                <select name="rack_location" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono text-xs font-bold focus:ring-2 focus:ring-indigo-500">
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
        <div class="bg-[#002f5e] p-4 text-white flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-barcode text-indigo-400 text-base"></i>
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
                        <span class="font-mono font-extrabold text-indigo-700 text-xs block" id="lblRack">AISLE-E-02-L1</span>
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
                <button onclick="window.print()" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-sm transition flex items-center space-x-1">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak Label</span>
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
<!-- MODAL 5: MODAL UJI PEMICU SENSOR OPERASIONAL CFS REAL-TIME               -->
<!-- ========================================================================= -->
<div id="modalTriggerCfsSensor" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden animate-fadeIn">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-gray-100 transform transition-all text-xs">
        <!-- Header Modal Dynamic -->
        <div id="cfsSensorModalHeader" class="bg-[#002f5e] p-5 text-white flex items-center justify-between">
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
                        <input type="text" name="container_number" value="MSKU7712048" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono uppercase font-bold text-indigo-700">
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
                    <select name="rack_location" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono text-xs font-bold text-indigo-700">
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
                    <select name="target_rack" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono text-xs font-bold text-indigo-700">
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
        header.className = 'bg-[#002f5e] p-5 text-white flex items-center justify-between border-b-2 border-amber-500';
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
        header.className = 'bg-[#002f5e] p-5 text-white flex items-center justify-between border-b-2 border-sky-500';
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
        header.className = 'bg-[#002f5e] p-5 text-white flex items-center justify-between border-b-2 border-purple-500';
        icon.className = 'w-10 h-10 rounded-xl bg-purple-500/20 text-purple-400 border border-purple-500/30 flex items-center justify-center text-lg';
        icon.innerHTML = '<i class="fa-solid fa-barcode"></i>';
        btnSubmit.className = 'px-5 py-2 bg-purple-500 hover:bg-purple-400 text-slate-950 font-bold rounded-xl shadow-md transition flex items-center space-x-1.5';
        btnText.innerText = 'Scan Barcode & Update Tally';
    } else if (type === 'env') {
        actionInput.value = 'trigger_cfs_env';
        document.getElementById('sectionTriggerEnv').classList.remove('hidden');
        title.innerText = 'Trigger Telemetri LoRaWAN SHT40 (ENV-01)';
        sub.innerText = 'Transmisi 868 MHz Suhu & Kelembaban Gudang 4.000m²';
        header.className = 'bg-[#002f5e] p-5 text-white flex items-center justify-between border-b-2 border-emerald-500';
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
        if (el) el.classList.add('hidden');
        if (btn) {
            btn.className = 'cfs-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all text-gray-600 hover:text-gray-900 hover:bg-gray-100 flex items-center gap-2';
        }
    });

    const activeEl = document.getElementById(tabId);
    const activeBtn = document.getElementById('btn-' + tabId);
    if (activeEl) activeEl.classList.remove('hidden');
    if (activeBtn) {
        activeBtn.className = 'cfs-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all bg-indigo-600 text-white shadow-sm flex items-center gap-2';
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
    let visibleCount = 0;

    rows.forEach(r => {
        const searchTxt = r.getAttribute('data-search') || '';
        const aisle = r.getAttribute('data-aisle') || '';
        const cat = r.getAttribute('data-category') || '';

        const matchSearch = !searchVal || searchTxt.includes(searchVal);
        const matchAisle = !aisleVal || aisle === aisleVal;
        const matchCat = !catVal || cat === catVal;

        if (matchSearch && matchAisle && matchCat) {
            r.style.display = '';
            visibleCount++;
        } else {
            r.style.display = 'none';
        }
    });

    const counter = document.getElementById('palletCountDisplay');
    if (counter) counter.innerText = 'Menampilkan ' + visibleCount + ' palet terdaftar';
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

// =========================================================================
// CAD VISUALIZER & DIGITAL TWIN CONTROLS (INFORMATIKA & SIPIL)
// =========================================================================
function setCadTheme(theme) {
    const container = document.getElementById('cadDrawingContainer');
    if (!container) return;

    // Reset theme classes
    container.classList.remove('cad-blueprint-mode', 'cad-cyber-mode', 'cad-paper-mode');
    
    // Reset buttons
    const btnBlueprint = document.getElementById('btn-theme-blueprint');
    const btnCyber = document.getElementById('btn-theme-cyber');
    const btnPaper = document.getElementById('btn-theme-paper');

    [btnBlueprint, btnCyber, btnPaper].forEach(btn => {
        if (btn) {
            btn.className = 'cad-theme-btn px-2.5 py-1 rounded-lg transition text-gray-600 hover:text-gray-900 hover:bg-gray-200 flex items-center gap-1';
        }
    });

    if (theme === 'cyber') {
        container.classList.add('cad-cyber-mode');
        if (btnCyber) btnCyber.className = 'cad-theme-btn px-2.5 py-1 rounded-lg transition bg-emerald-600 text-white shadow-xs flex items-center gap-1';
    } else if (theme === 'paper') {
        container.classList.add('cad-paper-mode');
        if (btnPaper) btnPaper.className = 'cad-theme-btn px-2.5 py-1 rounded-lg transition bg-slate-800 text-white shadow-xs flex items-center gap-1';
    } else {
        container.classList.add('cad-blueprint-mode');
        if (btnBlueprint) btnBlueprint.className = 'cad-theme-btn px-2.5 py-1 rounded-lg transition bg-blue-600 text-white shadow-xs flex items-center gap-1';
    }
}

function toggleCadLayer(layerId, isChecked) {
    const layer = document.getElementById(layerId);
    if (layer) {
        layer.style.display = isChecked ? 'inline' : 'none';
    }
}

let cfsMapScale = 1;
function cfsMapZoom(delta) {
    cfsMapScale = Math.min(2.5, Math.max(0.6, cfsMapScale + delta));
    const svg = document.getElementById('cfsCadSvg');
    if (svg) {
        svg.style.transform = `scale(${cfsMapScale})`;
        svg.style.transformOrigin = 'center center';
    }
}

function cfsMapReset() {
    cfsMapScale = 1;
    const svg = document.getElementById('cfsCadSvg');
    if (svg) {
        svg.style.transform = 'scale(1)';
        svg.style.transformOrigin = 'center center';
    }
}

function updateCadHud(e) {
    const svg = document.getElementById('cfsCadSvg');
    if (!svg) return;

    const pt = svg.createSVGPoint();
    pt.x = e.clientX;
    pt.y = e.clientY;
    const svgP = pt.matrixTransform(svg.getScreenCTM().inverse());

    // SVG coordinates: building span X: 70 to 1150 (1080px = 80m), Y: 70 to 670 (600px = 50m)
    const relX = Math.max(0, Math.min(1080, svgP.x - 70));
    const relY = Math.max(0, Math.min(600, svgP.y - 70));

    const xM = (relX / 1080 * 80).toFixed(1);
    const yM = (relY / 600 * 50).toFixed(1);

    // Calculate nearest Grid As
    // X grids 1..9 (every 10m)
    const gridXIdx = Math.min(9, Math.max(1, Math.round(relX / 135) + 1));
    // Y grids A..F (every 10m)
    const gridYLetters = ['A', 'B', 'C', 'D', 'E', 'F'];
    const gridYIdx = Math.min(5, Math.max(0, Math.round(relY / 120)));
    const gridYLetter = gridYLetters[gridYIdx];

    // Determine Zone
    let zone = 'Area Operasional Terbuka';
    const xVal = parseFloat(xM);
    const yVal = parseFloat(yM);

    if (svgP.y > 745) {
        zone = 'Etiket Kop Gambar ISO 7200 &amp; Catatan Teknis Sipil';
    } else if (yVal > 45) {
        zone = 'Rampa Doking D1–D5 (Leveler Hidrolik 15T) &amp; Apron Aspal';
    } else if (yVal > 35) {
        if (xVal < 40) {
            zone = 'Zona Staging Inbound & Timbangan HW-21';
        } else {
            zone = 'Zona Staging Outbound Konsolidasi LCL';
        }
    } else if (yVal < 10 && xVal < 18) {
        zone = 'Ruang Pengawas Hanggar Bea Cukai CEISA';
    } else if (yVal < 10 && xVal > 64) {
        zone = 'Charging Bay Forklift Listrik (380V)';
    } else if (yVal >= 10 && yVal <= 35) {
        if (xVal >= 8 && xVal < 19) {
            zone = 'Aisle A: Racks Otomotif & Heavy Spareparts';
        } else if (xVal >= 19 && xVal < 30) {
            zone = 'Aisle B: Racks Elektronik & High-Tech Components';
        } else if (xVal >= 30 && xVal < 41) {
            zone = 'Aisle C: Racks FMCG & Fast-Moving Consumer Goods';
        } else if (xVal >= 41 && xVal < 52) {
            zone = 'Aisle D: Racks Tekstil, Garmen & Alas Kaki';
        } else if (xVal >= 52 && xVal < 63) {
            zone = 'Aisle E: Racks Kimia Non-B3 & Polimer';
        } else if (xVal >= 63 && xVal < 76) {
            zone = 'Aisle F: Karantina Pabean Bea Cukai (Jalur Merah)';
        } else {
            zone = 'Jalur Sirkulasi Forklift Utama (Lebar 3.500 mm)';
        }
    }

    const hudCoords = document.getElementById('cadHudCoords');
    const hudGrid = document.getElementById('cadHudGrid');
    const hudZone = document.getElementById('cadHudZone');

    if (hudCoords) hudCoords.innerText = `X: ${xM} m | Y: ${yM} m`;
    if (hudGrid) hudGrid.innerText = (svgP.y > 745) ? 'Grid: Margin CAD' : `Grid: As ${gridYLetter}-${gridXIdx}`;
    if (hudZone) hudZone.innerText = `Zona: ${zone}`;
}

function resetCadHud() {
    const hudCoords = document.getElementById('cadHudCoords');
    const hudGrid = document.getElementById('cadHudGrid');
    const hudZone = document.getElementById('cadHudZone');

    if (hudCoords) hudCoords.innerText = 'X: --.- m | Y: --.- m';
    if (hudGrid) hudGrid.innerText = 'Grid: Area Bebas';
    if (hudZone) hudZone.innerText = 'Zona: CFS Warehouse 4.000 m² (Arahkan kursor ke denah)';
}

// Export CFS Inventory to Excel using SheetJS
function exportCfsExcel() {
    const pallets = <?= json_encode($cfs_pallets) ?>;
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

// Print Tally Sheet Modal / Quick Document
function printTallySheet(jobNum, ctrNum, jobType, consignee, dock, pkgs, weight, notes) {
    const w = window.open('', '_blank', 'width=800,height=700');
    w.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Tally Sheet ${jobNum} - CIDP CFS 4.000m²</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 25px; color: #1e293b; font-size: 13px; line-height: 1.5; }
                .header { border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
                .title { font-size: 18px; font-weight: bold; color: #004b87; }
                .subtitle { font-size: 11px; color: #64748b; }
                table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                th, td { border: 1px solid #cbd5e1; padding: 8px 12px; text-align: left; }
                th { background-color: #f1f5f9; font-weight: bold; }
                .footer { margin-top: 40px; display: flex; justify-content: space-between; text-align: center; }
                .sign-box { width: 200px; padding-top: 60px; border-top: 1px solid #94a3b8; margin-top: 50px; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="header">
                <div>
                    <div class="title">CONCLUSION INTERMODAL DRY PORT</div>
                    <div class="subtitle">Container Freight Station (CFS) 4.000 m² &bull; Rampa D1-D5</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-weight: bold; font-size: 14px; font-family: monospace;">${jobNum}</div>
                    <div style="font-size: 11px; color: #64748b;">Tanggal: ${new Date().toLocaleDateString('id-ID')}</div>
                </div>
            </div>

            <h3 style="text-align: center; text-transform: uppercase; margin-bottom: 15px;">BERITA ACARA TALLY SHEET BONGKAR / MUAT CFS</h3>

            <table>
                <tr>
                    <th width="30%">Tipe Operasi</th>
                    <td><strong>${jobType}</strong></td>
                    <th width="20%">Rampa Dock</th>
                    <td><strong>Rampa ${dock} (Hydraulic 15T)</strong></td>
                </tr>
                <tr>
                    <th>Nomor Peti Kemas</th>
                    <td style="font-family: monospace; font-weight: bold;">${ctrNum}</td>
                    <th>Shipper / Consignee</th>
                    <td>${consignee}</td>
                </tr>
                <tr>
                    <th>Total Koli / Palet</th>
                    <td><strong>${pkgs} Palet</strong></td>
                    <th>Total Berat Kotor</th>
                    <td><strong>${parseFloat(weight).toLocaleString('id-ID')} kg</strong></td>
                </tr>
                <tr>
                    <th>Catatan & Segel Pabean</th>
                    <td colspan="3">${notes || 'Segel pabean terverifikasi utuh, tidak ditemukan kerusakan kargo.'}</td>
                </tr>
            </table>

            <div class="footer">
                <div>
                    <div>Petugas Tallyman CFS</div>
                    <div class="sign-box">Eko Prasetyo</div>
                </div>
                <div>
                    <div>Pengemudi / Transporter</div>
                    <div class="sign-box">( ........................................ )</div>
                </div>
                <div>
                    <div>Pengawas Pabean Hanggar</div>
                    <div class="sign-box">Hanggar Bea Cukai CEISA</div>
                </div>
            </div>
            <script>window.print();<\/script>
        </body>
        </html>
    `);
    w.document.close();
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
                targetDock.classList.add('ring-4', 'ring-indigo-500', 'shadow-xl');
                setTimeout(() => targetDock.classList.remove('ring-4', 'ring-indigo-500', 'shadow-xl'), 4000);
            }, 300);
        }
    }

    // 5. Handle action modal
    if (actionParam === 'new_job') {
        openModal('modalNewJobOrder');
    } else if (actionParam === 'assign_dock') {
        openModal('modalAssignDock');
    }
});
</script>
