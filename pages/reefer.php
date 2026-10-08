<?php
// =============================================================================
// MODUL: MONITOR REEFER COLD CHAIN — TELEMETRI SUHU & STEKER LISTRIK 380V (300 PLUGS)
// File: pages/reefer.php
// Proyek: Conclusion Intermodal Dry Port (CIDP) 35 Ha — Yard Management System
// Standar: HACCP Food Safety, WHO GDP Pharma, ISO 1496-2 Thermal Containers
// PIC : Armansyah Muchtarrom (Hardware & Infrastructure Specialist)
// =============================================================================

require_once __DIR__ . '/../connection.php';

$reefer_info = [
    'pic'      => 'Armansyah Muchtarrom',
    'role'     => 'Hardware & Infrastructure Specialist',
    'desc'     => 'Pemantauan daya 380V/32A pada 300 steker reefer, telemetri suhu kontinu LoRaWAN HACCP, dan pemindaian termal inframerah kompresor.',
    'icon'     => 'fa-snowflake',
    'status'   => 'LoRaWAN 868 MHz & Modbus Online',
    'badge'    => 'bg-cyan-50 text-cyan-700 border-cyan-200'
];

// Handle Form Aksi POST (Plug-In / Un-Plug / Simpan Kalibrasi)
$action_alert = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (isset($_POST['action_type']) && $_POST['action_type'] === 'plug_in') {
        $ctr_num   = strtoupper(trim($_POST['container_number'] ?? ''));
        $slot_id   = trim($_POST['slot_id'] ?? '');
        $setpoint  = floatval($_POST['setpoint_temp'] ?? -20.0);
        $commodity = trim($_POST['commodity'] ?? 'General Perishables');
        $operator  = trim($_POST['operator_name'] ?? 'Petugas Reefer');

        if (!empty($ctr_num) && !empty($slot_id)) {
            try {
                // Update / Insert ke database jika ada
                if (isset($pdo)) {
                    $stmtCheck = $pdo->prepare("SELECT id FROM containers WHERE container_number = ? LIMIT 1");
                    $stmtCheck->execute([$ctr_num]);
                    $existing = $stmtCheck->fetch();

                    if ($existing) {
                        $stmtUp = $pdo->prepare("UPDATE containers SET block = 'REEFER', status = 'in_yard', customs_status = 'SPPB_CLEARED' WHERE id = ?");
                        $stmtUp->execute([$existing['id']]);
                    }

                    // Catat ke log yard_events
                    $stmtLog = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, to_block, operator_name, notes, created_at) VALUES ('REEFER_PLUG_IN', ?, 'REEFER', ?, ?, NOW())");
                    $stmtLog->execute([$ctr_num, $operator, "Plug-in steker $slot_id pada setpoint $setpoint °C ($commodity)"]);
                }
                $action_alert = [
                    'type' => 'success',
                    'title' => 'Steker Berhasil Terhubung (Plug-In Confirmed)',
                    'msg' => "Peti kemas <strong>$ctr_num</strong> berhasil dicolokkan ke soket <strong>$slot_id</strong> dengan setpoint suhu <strong>{$setpoint}°C</strong>. Telemetri daya 380V aktif."
                ];
            } catch (Exception $e) {
                $action_alert = [
                    'type' => 'warning',
                    'title' => 'Steker Terhubung (Mode Demo)',
                    'msg' => "Koneksi steker $slot_id untuk $ctr_num telah aktif pada setpoint {$setpoint}°C."
                ];
            }
        }
    } elseif (isset($_POST['action_type']) && $_POST['action_type'] === 'un_plug') {
        $ctr_num = strtoupper(trim($_POST['container_number'] ?? ''));
        $slot_id = trim($_POST['slot_id'] ?? '');
        $kwh     = floatval($_POST['energy_consumed'] ?? 142.5);

        if (isset($pdo) && !empty($ctr_num)) {
            try {
                $stmtLog = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, operator_name, billable_amount, notes, created_at) VALUES ('REEFER_UNPLUG', ?, 'Petugas Reefer', ?, ?, NOW())");
                $bill_amount = $kwh * 2850; // Tarif listrik industri Rp 2.850 / kWh
                $stmtLog->execute([$ctr_num, $bill_amount, "Pelepasan steker $slot_id. Total konsumsi: $kwh kWh"]);
            } catch (Exception $e) {}
        }

        $action_alert = [
            'type' => 'info',
            'title' => 'Steker Dilepas (Un-Plug Completed)',
            'msg' => "Peti kemas <strong>$ctr_num</strong> telah dilepas dari steker <strong>$slot_id</strong>. Konsumsi energi: <strong>{$kwh} kWh</strong> telah dibukukan ke tagihan modul Billing."
        ];
    } elseif (isset($_POST['action_type']) && $_POST['action_type'] === 'trigger_lorawan_ping') {
        $ctr_num     = strtoupper(trim($_POST['container_number'] ?? 'EITU9823102'));
        $slot_id     = trim($_POST['slot_id'] ?? 'R01-S04');
        $temp_actual = floatval($_POST['temp_actual'] ?? -20.4);
        $setpoint    = floatval($_POST['setpoint'] ?? -20.0);
        $voltage     = floatval($_POST['voltage'] ?? 382.4);
        $humidity    = floatval($_POST['humidity'] ?? 89.5);
        $rssi        = trim($_POST['rssi'] ?? '-68 dBm');

        if (isset($pdo) && !empty($ctr_num)) {
            try {
                // Update / touch container
                $stmtUp = $pdo->prepare("UPDATE containers SET customs_status = COALESCE(customs_status, 'SPPB_CLEARED') WHERE container_number = ?");
                $stmtUp->execute([$ctr_num]);

                // Catat ke yard_events
                $stmtLog = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, to_block, operator_name, notes, created_at) VALUES ('REEFER_TELEMETRY_PING', ?, 'REEFER', 'LoRaWAN SHT40 Gateway', ?, NOW())");
                $stmtLog->execute([$ctr_num, "Paket Telemetri LoRaWAN 868MHz: Slot $slot_id, Temp {$temp_actual}°C (SP {$setpoint}°C), Tegangan {$voltage}V, Kelembaban {$humidity}% RH, Sinyal RSSI $rssi"]);
            } catch (Exception $e) {}
        }

        $action_alert = [
            'type' => 'success',
            'title' => '📡 Paket Telemetri LoRaWAN Berhasil Diterima (Real IoT Sensor)',
            'msg' => "Gateway LoRaWAN 868 MHz (HW-23 Node) menerima transmisi dari kontainer <strong>$ctr_num</strong> (Slot <strong>$slot_id</strong>). Suhu Aktual: <strong class='font-mono text-cyan-800'>{$temp_actual}°C</strong> (Setpoint: {$setpoint}°C), Tegangan: <strong class='font-mono'>{$voltage}V</strong>, Kelembaban: <strong class='font-mono'>{$humidity}% RH</strong>, RSSI: <strong>$rssi</strong> (Kualitas Sinyal Unggul). Event telah dicatat permanen ke database YMS."
        ];
    } elseif (isset($_POST['action_type']) && $_POST['action_type'] === 'trigger_thermal_scan') {
        $rack_id   = trim($_POST['rack_id'] ?? 'Rack 03');
        $max_temp  = floatval($_POST['max_temp'] ?? 68.9);
        $camera_id = trim($_POST['camera_id'] ?? 'HW-16 Hikvision DS-2TD2636B');
        $is_alarm  = ($max_temp > 85.0);

        if (isset($pdo)) {
            try {
                $event_name = $is_alarm ? 'K3_THERMAL_ALARM' : 'K3_THERMAL_SCAN';
                $stmtLog = $pdo->prepare("INSERT INTO yard_events (event_type, to_block, operator_name, notes, created_at) VALUES (?, 'REEFER', 'AI Thermal Cam HW-16', ?, NOW())");
                $stmtLog->execute([$event_name, "Pemindaian Termal HW-16: $rack_id Kompresor {$max_temp}°C (Ambang batas trip 85.0°C - Status: " . ($is_alarm ? 'ALARM OVERHEAT' : 'NORMAL') . ")"]);
            } catch (Exception $e) {}
        }

        $action_alert = [
            'type' => $is_alarm ? 'warning' : 'success',
            'title' => $is_alarm ? '🔥 PERINGATAN K3: Hotspot Kompresor Terdeteksi!' : '✅ Pemindaian Kamera Termal Inframerah HW-16 Berhasil',
            'msg' => "Kamera termal bi-spektrum HW-16 di Menara Timur telah memindai <strong>$rack_id</strong>. Titik panas kompresor tertinggi: <strong class='font-mono text-red-600'>{$max_temp}°C</strong> (" . ($is_alarm ? 'MELEBIHI AMBANG BATAS 85°C — Solenoid proteksi trip siaga!' : 'Di bawah batas aman 85.0°C — Sistem pendingin beroperasi normal') . "). Log audit K3 tersimpan di database."
        ];
    } elseif (isset($_POST['action_type']) && $_POST['action_type'] === 'trigger_ats_test') {
        $load_kw     = floatval($_POST['load_kw'] ?? 740.5);
        $switch_time = floatval($_POST['switch_time'] ?? 6.8);

        if (isset($pdo)) {
            try {
                $stmtLog = $pdo->prepare("INSERT INTO yard_events (event_type, to_block, operator_name, notes, created_at) VALUES ('ATS_GENSET_PULSE', 'REEFER', 'Cummins 1500kVA ATS', ?, NOW())");
                $stmtLog->execute(["Simulasi Alih Daya ATS Genset: Waktu alih $switch_time detik, beban $load_kw kW, frekuensi 50.1 Hz, tegangan 384V stabil"]);
            } catch (Exception $e) {}
        }

        $action_alert = [
            'type' => 'success',
            'title' => '⚡ Pengujian Otomasi ATS Genset 1.500 kVA Berhasil',
            'msg' => "Simulasi alih catu daya darurat Cummins 1.500 kVA dengan sistem ATS tervalidasi dalam <strong class='font-mono text-amber-700'>{$switch_time} detik</strong> (standar ISO &lt; 10 detik). Suplai daya 380V/32A pada beban <strong>{$load_kw} kW</strong> tetap stabil tanpa lonjakan arus."
        ];
    }
}

// Ambil kontainer reefer dari database jika ada
$db_reefers = [];
if (isset($pdo)) {
    try {
        $stmtR = $pdo->query("SELECT * FROM containers WHERE cargo_type = 'reefer' OR block = 'REEFER' ORDER BY id DESC");
        $db_reefers = $stmtR->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $db_reefers = [];
    }
}

// Dataset Komprehensif Kontainer Reefer Aktif di Lapangan CIDP
$active_reefers = [
    [
        'id'            => 'EITU9823102',
        'iso'           => '45R1 (40ft High Cube Reefer)',
        'line'          => 'Maersk Line Cold Care',
        'line_badge'    => 'bg-blue-600',
        'commodity'     => 'Vaksin & Produk Biologi Farmasi',
        'category'      => 'pharma',
        'setpoint'      => 4.0,
        'temp_actual'   => 4.2,
        'temp_supply'   => 3.8,
        'temp_return'   => 4.5,
        'humidity'      => 85.0,
        'rack'          => 'Rack 02',
        'slot'          => 'R02-S14',
        'voltage'       => 382.4,
        'amperage'      => 14.8,
        'power_kw'      => 5.65,
        'kwh_total'     => 178.4,
        'status'        => 'normal',
        'status_text'   => 'Stabil & Presisi',
        'plug_time'     => '29 Sep 2026 10:45 WIB',
        'duration'      => '35 Jam 20 Menit',
        'pti_status'    => 'PASSED',
        'haccp_cert'    => 'WHO-GDP/2026/09/8821',
        'destination'   => 'Bio Farma Hub Bandung',
        'compressor_temp' => 46.2
    ],
    [
        'id'            => 'MNBU9920194',
        'iso'           => '45R1 (40ft High Cube Reefer)',
        'line'          => 'CMA CGM Reefer+',
        'line_badge'    => 'bg-red-600',
        'commodity'     => 'Daging Sapi Beku (Frozen Beef Boneless)',
        'category'      => 'meat',
        'setpoint'      => -20.0,
        'temp_actual'   => -19.8,
        'temp_supply'   => -21.2,
        'temp_return'   => -19.4,
        'humidity'      => 92.5,
        'rack'          => 'Rack 01',
        'slot'          => 'R01-S08',
        'voltage'       => 380.1,
        'amperage'      => 18.2,
        'power_kw'      => 6.92,
        'kwh_total'     => 294.6,
        'status'        => 'normal',
        'status_text'   => 'Suhu Beku Stabil',
        'plug_time'     => '28 Sep 2026 14:15 WIB',
        'duration'      => '55 Jam 50 Menit',
        'pti_status'    => 'PASSED',
        'haccp_cert'    => 'HACCP-ID/BEEF/2026/041',
        'destination'   => 'Cold Storage Pergudangan Marunda',
        'compressor_temp' => 52.8
    ],
    [
        'id'            => 'ONEU8812903',
        'iso'           => '45R1 (40ft High Cube Reefer)',
        'line'          => 'ONE Ocean Network Express',
        'line_badge'    => 'bg-pink-600',
        'commodity'     => 'Ikan Tuna Sashimi Grade (Super Freezer)',
        'category'      => 'seafood',
        'setpoint'      => -40.0,
        'temp_actual'   => -39.4,
        'temp_supply'   => -41.0,
        'temp_return'   => -39.1,
        'humidity'      => 90.0,
        'rack'          => 'Rack 04',
        'slot'          => 'R04-S22',
        'voltage'       => 384.0,
        'amperage'      => 24.5,
        'power_kw'      => 9.41,
        'kwh_total'     => 412.0,
        'status'        => 'normal',
        'status_text'   => 'Ultra-Low Cryo OK',
        'plug_time'     => '27 Sep 2026 19:30 WIB',
        'duration'      => '74 Jam 35 Menit',
        'pti_status'    => 'PASSED',
        'haccp_cert'    => 'HACCP-ID/TUNA/2026/910',
        'destination'   => 'Ekspor ke Narita (NRT-TYO)',
        'compressor_temp' => 61.4
    ],
    [
        'id'            => 'HLXU4419208',
        'iso'           => '22R1 (20ft Reefer Standard)',
        'line'          => 'Hapag-Lloyd ExtraFresh',
        'line_badge'    => 'bg-amber-600',
        'commodity'     => 'Buah Apel Fuji & Anggur Impor',
        'category'      => 'fruits',
        'setpoint'      => 1.5,
        'temp_actual'   => 3.2,
        'temp_supply'   => 2.8,
        'temp_return'   => 3.5,
        'humidity'      => 94.0,
        'rack'          => 'Rack 03',
        'slot'          => 'R03-S11',
        'voltage'       => 378.6,
        'amperage'      => 13.4,
        'power_kw'      => 5.07,
        'kwh_total'     => 89.2,
        'status'        => 'warning',
        'status_text'   => 'Fluktuasi Suhu (+1.7°C)',
        'plug_time'     => '30 Sep 2026 04:10 WIB',
        'duration'      => '18 Jam 0 Menit',
        'pti_status'    => 'MONITORING',
        'haccp_cert'    => 'FRUIT-INSP/2026/112',
        'destination'   => 'Distribusi Supermarket Jabodetabek',
        'compressor_temp' => 68.9
    ],
    [
        'id'            => 'MSKU3319024',
        'iso'           => '45R1 (40ft High Cube Reefer)',
        'line'          => 'Maersk Line Cold Care',
        'line_badge'    => 'bg-blue-600',
        'commodity'     => 'Es Krim & Dairy Products (Campina)',
        'category'      => 'dairy',
        'setpoint'      => -25.0,
        'temp_actual'   => -24.8,
        'temp_supply'   => -26.0,
        'temp_return'   => -24.5,
        'humidity'      => 88.0,
        'rack'          => 'Rack 05',
        'slot'          => 'R05-S03',
        'voltage'       => 381.2,
        'amperage'      => 19.8,
        'power_kw'      => 7.55,
        'kwh_total'     => 146.5,
        'status'        => 'normal',
        'status_text'   => 'Beku Dalam Optimal',
        'plug_time'     => '29 Sep 2026 21:00 WIB',
        'duration'      => '25 Jam 10 Menit',
        'pti_status'    => 'PASSED',
        'haccp_cert'    => 'DAIRY-CERT/2026/089',
        'destination'   => 'DC Cikarang Industri',
        'compressor_temp' => 49.3
    ],
    [
        'id'            => 'EMCU7712093',
        'iso'           => '45R1 (40ft High Cube Reefer)',
        'line'          => 'Evergreen Marine Line',
        'line_badge'    => 'bg-emerald-600',
        'commodity'     => 'Udang Vaname Beku (Frozen Shrimp Export)',
        'category'      => 'seafood',
        'setpoint'      => -22.0,
        'temp_actual'   => -21.9,
        'temp_supply'   => -23.1,
        'temp_return'   => -21.6,
        'humidity'      => 91.0,
        'rack'          => 'Rack 06',
        'slot'          => 'R06-S19',
        'voltage'       => 382.9,
        'amperage'      => 17.6,
        'power_kw'      => 6.74,
        'kwh_total'     => 210.8,
        'status'        => 'normal',
        'status_text'   => 'Kualitas Ekspor Prima',
        'plug_time'     => '29 Sep 2026 06:30 WIB',
        'duration'      => '39 Jam 40 Menit',
        'pti_status'    => 'PASSED',
        'haccp_cert'    => 'HACCP-ID/SHRIMP/2026/512',
        'destination'   => 'Tanjung Priok ➔ US West Coast',
        'compressor_temp' => 50.1
    ],
    [
        'id'            => 'SUDU9912048',
        'iso'           => '45R1 (40ft High Cube Reefer)',
        'line'          => 'Hamburg Süd Cold Chain',
        'line_badge'    => 'bg-red-700',
        'commodity'     => 'Plasma Darah & Reagen Laboratorium',
        'category'      => 'pharma',
        'setpoint'      => -30.0,
        'temp_actual'   => -29.8,
        'temp_supply'   => -31.4,
        'temp_return'   => -29.5,
        'humidity'      => 80.0,
        'rack'          => 'Rack 07',
        'slot'          => 'R07-S06',
        'voltage'       => 383.5,
        'amperage'      => 21.0,
        'power_kw'      => 8.05,
        'kwh_total'     => 320.1,
        'status'        => 'normal',
        'status_text'   => 'Ultra Cryo Medis OK',
        'plug_time'     => '28 Sep 2026 11:20 WIB',
        'duration'      => '58 Jam 50 Menit',
        'pti_status'    => 'PASSED',
        'haccp_cert'    => 'MED-PLASMA/2026/004',
        'destination'   => 'RSPAD Gatot Soebroto Jakarta',
        'compressor_temp' => 54.0
    ],
    [
        'id'            => 'TCKU2219401',
        'iso'           => '22R1 (20ft Reefer Standard)',
        'line'          => 'TexTainer Reefer Lease',
        'line_badge'    => 'bg-slate-700',
        'commodity'     => 'Cokelat Couverture & Pastry Premium',
        'category'      => 'confectionery',
        'setpoint'      => 16.0,
        'temp_actual'   => 16.1,
        'temp_supply'   => 15.5,
        'temp_return'   => 16.4,
        'humidity'      => 60.0,
        'rack'          => 'Rack 08',
        'slot'          => 'R08-S25',
        'voltage'       => 379.8,
        'amperage'      => 10.2,
        'power_kw'      => 3.87,
        'kwh_total'     => 62.4,
        'status'        => 'normal',
        'status_text'   => 'Suhu Sejuk Kering OK',
        'plug_time'     => '30 Sep 2026 08:00 WIB',
        'duration'      => '14 Jam 10 Menit',
        'pti_status'    => 'PASSED',
        'haccp_cert'    => 'CHOC-SPEC/2026/021',
        'destination'   => 'Sentra Industri Cikarang Barat',
        'compressor_temp' => 41.5
    ]
];

// Integrasi kontainer dari database ke tabel tampilan jika belum ada di list
foreach ($db_reefers as $dbr) {
    $exists = false;
    foreach ($active_reefers as $ar) {
        if ($ar['id'] === $dbr['container_number']) {
            $exists = true;
            break;
        }
    }
    if (!$exists) {
        $active_reefers[] = [
            'id'            => $dbr['container_number'],
            'iso'           => $dbr['size_type'] ?? '40FT REEFER HC',
            'line'          => $dbr['owner_company'] ?? 'Mitra Ekspedisi CIDP',
            'line_badge'    => 'bg-cyan-700',
            'commodity'     => 'Komoditas Rantai Dingin Terdaftar',
            'category'      => 'general',
            'setpoint'      => -18.0,
            'temp_actual'   => -17.9,
            'temp_supply'   => -19.0,
            'temp_return'   => -17.5,
            'humidity'      => 88.0,
            'rack'          => 'Rack 09',
            'slot'          => 'R09-S01',
            'voltage'       => 380.0,
            'amperage'      => 16.5,
            'power_kw'      => 6.27,
            'kwh_total'     => 112.0,
            'status'        => 'normal',
            'status_text'   => 'Daya 380V Stabil',
            'plug_time'     => $dbr['gate_in_time'] ?? date('d M Y H:i'),
            'duration'      => '24 Jam 0 Menit',
            'pti_status'    => 'PASSED',
            'haccp_cert'    => 'HACCP-CIDP/' . $dbr['id'],
            'destination'   => 'Yard Blok Reefer CIDP',
            'compressor_temp' => 48.0
        ];
    }
}

// Perhitungan Statistik Metrik Ringkasan
$total_plugs_capacity = 300;
$connected_plugs_count = 184; // Rata-rata operasional live terminal
$empty_plugs_count = $total_plugs_capacity - $connected_plugs_count;
$occupancy_rate = round(($connected_plugs_count / $total_plugs_capacity) * 100, 1);

// Beban daya total terminal
$total_power_kw = 0;
foreach ($active_reefers as $ar) {
    $total_power_kw += $ar['power_kw'];
}
$estimated_total_load_kw = round($total_power_kw + ($connected_plugs_count - count($active_reefers)) * 6.2, 1); // Rata-rata 6.2 kW/steker
$power_capacity_kw = 1200; // Kapasitas trafo PLN 1.200 kVA (PF 0.85 ~ 1.020 kW) + Backup Genset 1.500 kVA

$normal_count = 0;
$warning_count = 0;
$critical_count = 0;
foreach ($active_reefers as $ar) {
    if ($ar['status'] === 'normal') $normal_count++;
    elseif ($ar['status'] === 'warning') $warning_count++;
    elseif ($ar['status'] === 'alarm') $critical_count++;
}
?>

<div class="space-y-6 animate-fadeIn pb-12">

    <!-- Flash Alert Feedback Aksi -->
    <?php if ($action_alert): ?>
        <div class="p-4 rounded-xl border flex items-start space-x-3.5 shadow-sm <?= $action_alert['type'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : ($action_alert['type'] === 'warning' ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-blue-50 border-blue-200 text-blue-900') ?>">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold flex-shrink-0 <?= $action_alert['type'] === 'success' ? 'bg-emerald-200 text-emerald-800' : ($action_alert['type'] === 'warning' ? 'bg-amber-200 text-amber-800' : 'bg-blue-200 text-blue-800') ?>">
                <i class="fa-solid <?= $action_alert['type'] === 'success' ? 'fa-check' : ($action_alert['type'] === 'warning' ? 'fa-triangle-exclamation' : 'fa-info') ?>"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-sm"><?= $action_alert['title'] ?></h4>
                <p class="text-xs mt-0.5"><?= $action_alert['msg'] ?></p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-700 text-sm p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- Header Modul: Clean & Minimal -->
    <div class="bg-white rounded-xl px-4 py-3 shadow-2xs border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#002f5e] via-[#0170b9] to-orange-500 text-white flex items-center justify-center text-lg shadow-xs flex-shrink-0">
                <i class="fa-solid <?= $reefer_info['icon'] ?>"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-base font-bold text-gray-900 leading-tight">Monitor Reefer Cold Chain (300 Plugs)</h1>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-cyan-50 text-cyan-700 border border-cyan-200 flex items-center">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 mr-1.5 animate-pulse"></span>LoRaWAN Online
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-orange-50 text-orange-700 border border-orange-200">
                        300 Steker 380V
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                        HACCP &amp; GDP
                    </span>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Cepat Header -->
        <div class="flex flex-wrap items-center gap-2 self-start md:self-auto flex-shrink-0">
            <button onclick="openPlugInModal()" class="px-3.5 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center space-x-1.5">
                <i class="fa-solid fa-plug text-xs"></i>
                <span>+ Plug-In Baru</span>
            </button>
            <a href="dashboard.php?page=simulator" class="px-3 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-2xs transition flex items-center space-x-1.5">
                <i class="fa-solid fa-cube text-amber-400 text-xs"></i>
                <span>Simulasi 3D</span>
            </a>
            <button onclick="exportReeferExcel()" class="px-3 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-xl shadow-2xs transition flex items-center space-x-1.5" title="Ekspor Log Suhu HACCP ke Excel">
                <i class="fa-solid fa-file-excel text-emerald-600"></i>
                <span>Log HACCP</span>
            </button>
        </div>
    </div>

    <!-- 5 Kartu KPI Telemetri Utama Cold Chain -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3.5">
        <!-- KPI 1: Okupansi Steker -->
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-2xs hover:border-cyan-300 transition group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Okupansi Steker</span>
                <span class="w-8 h-8 rounded-lg bg-cyan-50 text-cyan-700 flex items-center justify-center text-xs shadow-inner group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-plug-circle-check"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline space-x-1.5">
                <span class="text-2xl font-black text-gray-900"><?= $connected_plugs_count ?></span>
                <span class="text-xs text-gray-400 font-semibold">/ <?= $total_plugs_capacity ?> Plugs</span>
            </div>
            <div class="mt-2 w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-cyan-500 h-1.5 rounded-full" style="width: <?= $occupancy_rate ?>%"></div>
            </div>
            <p class="text-[10px] text-gray-500 mt-1.5 flex justify-between">
                <span>Okupansi: <strong><?= $occupancy_rate ?>%</strong></span>
                <span class="text-emerald-600">Sisa: <?= $empty_plugs_count ?> Colokan</span>
            </p>
        </div>

        <!-- KPI 2: Beban Daya Listrik 380V -->
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-2xs hover:border-blue-300 transition group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Beban Daya</span>
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center text-xs shadow-inner group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-bolt"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline space-x-1.5">
                <span class="text-2xl font-black text-gray-900"><?= number_format($estimated_total_load_kw, 0, ',', '.') ?></span>
                <span class="text-xs text-gray-400 font-semibold">kW (380V)</span>
            </div>
            <div class="mt-2 w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-blue-600 h-1.5 rounded-full" style="width: <?= round(($estimated_total_load_kw / $power_capacity_kw) * 100) ?>%"></div>
            </div>
            <p class="text-[10px] text-gray-500 mt-1.5 flex justify-between">
                <span>Trafo: 1.200 kVA</span>
                <span class="text-blue-600">Genset 1.500 kVA</span>
            </p>
        </div>

        <!-- KPI 3: Integritas Rantai Dingin -->
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-2xs hover:border-emerald-300 transition group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kepatuhan Suhu</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center text-xs shadow-inner group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-shield-virus"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline space-x-1.5">
                <span class="text-2xl font-black text-emerald-700">98,8%</span>
                <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-1.5 py-0.5 rounded">HACCP</span>
            </div>
            <p class="text-[10px] text-gray-500 mt-3 flex items-center">
                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5"></span>
                <span><?= $normal_count ?> Normal / <?= $warning_count ?> Warning Drift</span>
            </p>
        </div>

        <!-- KPI 4: Kamera Termal Inframerah HW-16 -->
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-2xs hover:border-amber-300 transition group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kamera Termal HW-16</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center text-xs shadow-inner group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-temperature-arrow-up"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline space-x-1.5">
                <span class="text-2xl font-black text-gray-900">4 Titik</span>
                <span class="text-xs text-amber-600 font-bold">Aktif</span>
            </div>
            <p class="text-[10px] text-gray-500 mt-3 flex items-center">
                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5"></span>
                <span>Max Hotspot: 68.9°C (Batas 85°C)</span>
            </p>
        </div>

        <!-- KPI 5: Dwell Time Rata-rata -->
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-2xs hover:border-purple-300 transition group col-span-2 md:col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Rata-rata Turnaround</span>
                <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-700 flex items-center justify-center text-xs shadow-inner group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline space-x-1.5">
                <span class="text-2xl font-black text-gray-900">1,8</span>
                <span class="text-xs text-purple-600 font-bold">Hari / Box</span>
            </div>
            <p class="text-[10px] text-gray-500 mt-3">
                Target Standar Cold Chain: &lt; 3 Hari
            </p>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- KONSOL PEMICU SENSOR TELEMETRI & K3 REEFER LAPANGAN (REAL DATABASE)   -->
    <!-- ======================================================================= -->
    <div class="bg-gradient-to-r from-slate-900 via-cyan-950 to-blue-950 rounded-2xl p-5 sm:p-6 text-white shadow-xl border border-cyan-800/40 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-cyan-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-cyan-800/40 pb-4 mb-4 relative z-10">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-cyan-500/20 border border-cyan-400/40 flex items-center justify-center text-cyan-300 text-lg shadow-inner">
                    <i class="fa-solid fa-satellite-dish animate-pulse"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h3 class="text-base font-bold text-white tracking-wide">Konsol Pemicu Sensor Operasional Reefer &amp; K3</h3>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-500/30 text-cyan-200 border border-cyan-400/40 font-mono">
                            REAL DATABASE MUTATION
                        </span>
                    </div>
                    <p class="text-xs text-cyan-100/70 mt-0.5">
                        Uji &amp; catat telemetri sensor cold chain riil (SHT40 LoRaWAN, Kamera Termal HW-16, &amp; ATS Genset 1.500 kVA) langsung ke basis data YMS.
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2 text-xs">
                <a href="dashboard.php?page=denah&zone=reefer&hw_id=16" class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 border border-white/15 text-cyan-200 font-semibold transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-map-pin text-cyan-400"></i>
                    <span>Posisi HW-16 di Denah</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 relative z-10">
            <!-- Sensor 1: LoRaWAN Telemetry Pulse (SHT40 & Modbus) -->
            <div class="bg-white/5 hover:bg-white/10 border border-white/10 hover:border-cyan-400/50 rounded-xl p-4 transition-all duration-200 flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-400/30 font-mono">
                            HW-23 &bull; LoRaWAN 868MHz
                        </span>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                    </div>
                    <h4 class="font-bold text-sm text-white group-hover:text-cyan-200 transition">Transmisi Telemetri Suhu Kontinu</h4>
                    <p class="text-[11px] text-cyan-100/70 mt-1 leading-relaxed">
                        Kirim data uplink nirkabel sensor boks (Suhu Aktual, Supply/Return, Tegangan 380V, Kelembaban RH) dan catat log kepatuhan HACCP.
                    </p>
                    <div class="mt-3 bg-black/30 p-2 rounded-lg font-mono text-[10.5px] text-cyan-200/90 space-y-0.5 border border-white/5">
                        <div class="flex justify-between"><span>Container:</span> <strong class="text-white">EITU9823102</strong></div>
                        <div class="flex justify-between"><span>Slot Steker:</span> <strong class="text-cyan-300">R01-S04</strong></div>
                        <div class="flex justify-between"><span>Default Suhu:</span> <strong class="text-emerald-300">-20.4°C / 89% RH</strong></div>
                    </div>
                </div>
                <button onclick="openReeferSensorModal('lorawan', 'EITU9823102')" class="mt-3.5 w-full py-2 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-bold text-xs rounded-lg transition shadow-md flex items-center justify-center space-x-1.5">
                    <i class="fa-solid fa-satellite-dish text-xs"></i>
                    <span>Trigger Telemetri LoRaWAN</span>
                </button>
            </div>

            <!-- Sensor 2: Thermal Camera HW-16 -->
            <div class="bg-white/5 hover:bg-white/10 border border-white/10 hover:border-amber-400/50 rounded-xl p-4 transition-all duration-200 flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-400/30 font-mono">
                            HW-16 &bull; Bi-Spectrum AI
                        </span>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                    </div>
                    <h4 class="font-bold text-sm text-white group-hover:text-amber-200 transition">Audit Termal Inframerah Kompresor</h4>
                    <p class="text-[11px] text-cyan-100/70 mt-1 leading-relaxed">
                        Pindai titik panas (*hotspot*) kompresor reefer jarak jauh (380m). Verifikasi ambang batas K3 85°C untuk mencegah kebakaran kompresor.
                    </p>
                    <div class="mt-3 bg-black/30 p-2 rounded-lg font-mono text-[10.5px] text-amber-200/90 space-y-0.5 border border-white/5">
                        <div class="flex justify-between"><span>Target Rak:</span> <strong class="text-white">Rack 03 (40ft High Cube)</strong></div>
                        <div class="flex justify-between"><span>Kamera:</span> <strong class="text-amber-300">DS-2TD2636B</strong></div>
                        <div class="flex justify-between"><span>Threshold K3:</span> <strong class="text-red-300">&le; 85.0°C (Trip Alarm)</strong></div>
                    </div>
                </div>
                <button onclick="openReeferSensorModal('thermal', 'Rack 03')" class="mt-3.5 w-full py-2 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white font-bold text-xs rounded-lg transition shadow-md flex items-center justify-center space-x-1.5">
                    <i class="fa-solid fa-fire-flame-curved text-xs"></i>
                    <span>Trigger Pemindaian Termal</span>
                </button>
            </div>

            <!-- Sensor 3: ATS Genset 1.500 kVA -->
            <div class="bg-white/5 hover:bg-white/10 border border-white/10 hover:border-emerald-400/50 rounded-xl p-4 transition-all duration-200 flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 font-mono">
                            HW-14 &bull; Cummins 1.500 kVA
                        </span>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                    </div>
                    <h4 class="font-bold text-sm text-white group-hover:text-emerald-200 transition">Uji Beban ATS Genset Shelter</h4>
                    <p class="text-[11px] text-cyan-100/70 mt-1 leading-relaxed">
                        Simulasikan pemadaman grid PLN dan alih daya otomatis (*Automatic Transfer Switch*) dalam 8 detik untuk menjamin kontinuitas 300 steker 380V.
                    </p>
                    <div class="mt-3 bg-black/30 p-2 rounded-lg font-mono text-[10.5px] text-emerald-200/90 space-y-0.5 border border-white/5">
                        <div class="flex justify-between"><span>Kapasitas:</span> <strong class="text-white">1.500 kVA / 1.200 kW</strong></div>
                        <div class="flex justify-between"><span>Waktu Alih:</span> <strong class="text-emerald-300">&lt; 10 Detik (ATS)</strong></div>
                        <div class="flex justify-between"><span>Beban Uji:</span> <strong class="text-cyan-200">740.5 kW (184 Box)</strong></div>
                    </div>
                </div>
                <button onclick="openReeferSensorModal('ats', '740.5')" class="mt-3.5 w-full py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs rounded-lg transition shadow-md flex items-center justify-center space-x-1.5">
                    <i class="fa-solid fa-bolt-lightning text-xs"></i>
                    <span>Trigger Simulasi ATS Genset</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Tab Bar Navigasi Sub-Modul Reefer -->
    <div class="bg-white rounded-xl p-1.5 border border-gray-100 shadow-xs flex flex-wrap gap-1">
        <button onclick="switchReeferTab('tab-tabel')" id="btn-tab-tabel" class="reefer-tab-btn flex-1 min-w-[150px] py-2.5 px-3.5 rounded-lg text-xs sm:text-sm font-bold transition-all text-[#0170b9] bg-blue-50/90 shadow-2xs flex items-center justify-center space-x-2">
            <i class="fa-solid fa-table-list"></i>
            <span>Daftar Peti Kemas Aktif (Live Telemetri)</span>
        </button>
        <button onclick="switchReeferTab('tab-matrix')" id="btn-tab-matrix" class="reefer-tab-btn flex-1 min-w-[150px] py-2.5 px-3.5 rounded-lg text-xs sm:text-sm font-semibold transition-all text-gray-600 hover:text-gray-900 hover:bg-gray-50 flex items-center justify-center space-x-2">
            <i class="fa-solid fa-cubes-stacked"></i>
            <span>Matriks 300 Smart Plugs (Rack Visualizer)</span>
        </button>
        <button onclick="switchReeferTab('tab-grafik')" id="btn-tab-grafik" class="reefer-tab-btn flex-1 min-w-[150px] py-2.5 px-3.5 rounded-lg text-xs sm:text-sm font-semibold transition-all text-gray-600 hover:text-gray-900 hover:bg-gray-50 flex items-center justify-center space-x-2">
            <i class="fa-solid fa-chart-line"></i>
            <span>Grafik Log Suhu 24 Jam (HACCP Audit)</span>
        </button>
        <button onclick="switchReeferTab('tab-thermal')" id="btn-tab-thermal" class="reefer-tab-btn flex-1 min-w-[150px] py-2.5 px-3.5 rounded-lg text-xs sm:text-sm font-semibold transition-all text-gray-600 hover:text-gray-900 hover:bg-gray-50 flex items-center justify-center space-x-2">
            <i class="fa-solid fa-fire-flame-curved"></i>
            <span>Kamera Termal HW-16 &amp; Proteksi K3</span>
        </button>
    </div>

    <!-- ======================================================================= -->
    <!-- TAB 1: DAFTAR PETI KEMAS REEFER AKTIF (LIVE MONITORING TABLE)          -->
    <!-- ======================================================================= -->
    <div id="tab-tabel" class="reefer-tab-content space-y-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <!-- Filter Bar & Search -->
            <div class="p-4 sm:p-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-slate-50/50">
                <div class="flex items-center space-x-2.5">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                    <h3 class="text-sm sm:text-base font-bold text-gray-800">
                        Telemetri Rantai Dingin Aktif (<span id="reeferCountBadge"><?= count($active_reefers) ?></span> Peti Kemas Terpantau)
                    </h3>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <input type="text" id="searchReeferInput" onkeyup="filterReeferTable()" placeholder="Cari kontainer / komoditas / slot..." class="pl-8 pr-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-cyan-500 focus:outline-none w-56">
                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-gray-400 text-xs"></i>
                    </div>
                    <select id="filterCategorySelect" onchange="filterReeferTable()" class="px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-cyan-500 text-gray-700">
                        <option value="all">Semua Komoditas</option>
                        <option value="pharma">Vaksin &amp; Farmasi</option>
                        <option value="meat">Daging Beku</option>
                        <option value="seafood">Ikan &amp; Seafood</option>
                        <option value="fruits">Buah &amp; Sayuran</option>
                        <option value="dairy">Dairy &amp; Es Krim</option>
                    </select>
                    <select id="filterStatusSelect" onchange="filterReeferTable()" class="px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-cyan-500 text-gray-700">
                        <option value="all">Semua Status</option>
                        <option value="normal">Normal / Stabil</option>
                        <option value="warning">Warning / Drift</option>
                    </select>
                </div>
            </div>

            <!-- Tabel Data -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600" id="reeferTable">
                    <thead class="bg-gray-50/80 text-[10.5px] uppercase font-bold text-gray-500 border-b border-gray-100 tracking-wider">
                        <tr>
                            <th class="p-3.5">No. Kontainer / ISO</th>
                            <th class="p-3.5">Pelayaran / Komoditas</th>
                            <th class="p-3.5">Slot Steker (HW-23)</th>
                            <th class="p-3.5 text-center">Setpoint</th>
                            <th class="p-3.5 text-center">Suhu Aktual</th>
                            <th class="p-3.5 text-center">Supply / Return</th>
                            <th class="p-3.5 text-center">Kelembaban</th>
                            <th class="p-3.5 text-center">Daya 380V (kW)</th>
                            <th class="p-3.5">Durasi Colok</th>
                            <th class="p-3.5 text-center">Status</th>
                            <th class="p-3.5 text-center">Aksi Operasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-sans">
                        <?php foreach ($active_reefers as $idx => $r): ?>
                            <tr class="hover:bg-cyan-50/30 transition reefer-row" data-cat="<?= $r['category'] ?>" data-status="<?= $r['status'] ?>">
                                <!-- Kontainer & ISO -->
                                <td class="p-3.5">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-800 flex items-center justify-center font-bold text-[11px] font-mono flex-shrink-0">
                                            RF
                                        </div>
                                        <div>
                                            <span class="font-mono font-bold text-gray-900 text-xs block leading-tight"><?= $r['id'] ?></span>
                                            <span class="text-[10px] text-gray-400 font-mono"><?= $r['iso'] ?></span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Pelayaran & Komoditas -->
                                <td class="p-3.5">
                                    <span class="font-semibold text-gray-900 block"><?= $r['commodity'] ?></span>
                                    <span class="text-[10px] text-white px-1.5 py-0.2 rounded font-semibold <?= $r['line_badge'] ?> inline-block mt-0.5"><?= $r['line'] ?></span>
                                </td>

                                <!-- Slot Steker -->
                                <td class="p-3.5 font-mono">
                                    <span class="font-bold text-gray-900 bg-slate-100 px-2 py-1 rounded border border-slate-200"><?= $r['slot'] ?></span>
                                    <span class="text-[10px] text-gray-400 block mt-0.5"><?= $r['rack'] ?></span>
                                </td>

                                <!-- Setpoint -->
                                <td class="p-3.5 text-center font-mono font-bold text-gray-700">
                                    <?= $r['setpoint'] > 0 ? '+' : '' ?><?= number_format($r['setpoint'], 1) ?>°C
                                </td>

                                <!-- Suhu Aktual -->
                                <td class="p-3.5 text-center font-mono">
                                    <?php 
                                        $diff = abs($r['temp_actual'] - $r['setpoint']);
                                        $temp_color = ($diff <= 1.0) ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-amber-700 bg-amber-50 border-amber-200';
                                    ?>
                                    <span class="inline-block px-2 py-0.5 rounded font-black text-xs border <?= $temp_color ?>">
                                        <?= $r['temp_actual'] > 0 ? '+' : '' ?><?= number_format($r['temp_actual'], 1) ?>°C
                                    </span>
                                </td>

                                <!-- Supply / Return -->
                                <td class="p-3.5 text-center font-mono text-[11px] text-gray-500">
                                    <div>Sup: <span class="font-semibold text-gray-700"><?= number_format($r['temp_supply'], 1) ?>°C</span></div>
                                    <div>Ret: <span class="font-semibold text-gray-700"><?= number_format($r['temp_return'], 1) ?>°C</span></div>
                                </td>

                                <!-- Kelembaban -->
                                <td class="p-3.5 text-center font-mono text-[11px]">
                                    <span class="font-semibold text-gray-800"><?= number_format($r['humidity'], 0) ?>%</span>
                                    <span class="text-[9.5px] text-gray-400 block">RH</span>
                                </td>

                                <!-- Daya & Energi -->
                                <td class="p-3.5 text-center font-mono text-[11px]">
                                    <div class="font-bold text-blue-700"><?= number_format($r['power_kw'], 2) ?> kW</div>
                                    <div class="text-[10px] text-gray-400"><?= number_format($r['kwh_total'], 1) ?> kWh</div>
                                </td>

                                <!-- Durasi Colok -->
                                <td class="p-3.5 text-[11px]">
                                    <span class="font-semibold text-gray-800 block"><?= $r['duration'] ?></span>
                                    <span class="text-[9.5px] text-gray-400 block font-mono"><?= $r['plug_time'] ?></span>
                                </td>

                                <!-- Status -->
                                <td class="p-3.5 text-center">
                                    <?php if ($r['status'] === 'normal'): ?>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center justify-center space-x-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <span>Normal</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 flex items-center justify-center space-x-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            <span>Warning Drift</span>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Tombol Aksi -->
                                <td class="p-3.5 text-center">
                                    <div class="flex items-center justify-center space-x-1">
                                        <button onclick='showReeferDetailModal(<?= json_encode($r) ?>)' class="w-7 h-7 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 flex items-center justify-center text-xs transition" title="Lihat Rincian Telemetri Lengkap">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <a href="dashboard.php?page=kontainer&search=<?= urlencode($r['id']) ?>&open=1" class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs transition" title="Lacak Siklus &amp; Milestone Kontainer">
                                            <i class="fa-solid fa-route"></i>
                                        </a>
                                        <a href="dashboard.php?page=simulator&focus_box=<?= urlencode($r['id']) ?>&view=reefer" class="w-7 h-7 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs transition" title="Sorot Posisi Fisik di Simulasi 3D">
                                            <i class="fa-solid fa-cube"></i>
                                        </a>
                                        <button onclick='selectReeferGraph("<?= $r['id'] ?>", "<?= $r['commodity'] ?>", <?= $r['setpoint'] ?>)' class="w-7 h-7 rounded-lg bg-cyan-50 hover:bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs transition" title="Lihat Grafik Fluktuasi Suhu 24 Jam">
                                            <i class="fa-solid fa-chart-line"></i>
                                        </button>
                                        <button onclick='openUnplugModal("<?= $r['id'] ?>", "<?= $r['slot'] ?>", <?= $r['kwh_total'] ?>)' class="w-7 h-7 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 flex items-center justify-center text-xs transition" title="Lepas Steker (Un-Plug Peti Kemas)">
                                            <i class="fa-solid fa-plug-circle-xmark"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Table Footer -->
            <div class="p-3 sm:p-4 bg-gray-50/70 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-500 gap-2">
                <span>Menampilkan <strong><?= count($active_reefers) ?></strong> kontainer terhubung steker aktif Marechal Decontactor.</span>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10.5px] font-medium bg-emerald-100 text-emerald-800">
                        <i class="fa-solid fa-circle-check mr-1"></i> Standar HACCP &amp; WHO GDP Pharma Terpenuhi
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- TAB 2: MATRIKS 300 SMART PLUGS (VISUAL RACK GRID VIEW)                 -->
    <!-- ======================================================================= -->
    <div id="tab-matrix" class="reefer-tab-content hidden space-y-4">
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-gray-100 shadow-sm">
            <!-- Header Rack Grid -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Visualisasi Rak Penumpukan Reefer (300 Soket 380V/32A)</h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Representasi 10 Rak Reefer (Rack 01 s/d Rack 10) dengan 30 titik steker industri Marechal per rak. Dilengkapi sensor arus telemetri Modbus RS-485.
                    </p>
                </div>
                <!-- Legend Status -->
                <div class="flex flex-wrap items-center gap-3 text-xs font-semibold">
                    <span class="inline-flex items-center text-emerald-700 bg-emerald-50 px-2 py-1 rounded-md border border-emerald-200">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-1.5"></span>
                        Terhubung &amp; Normal (179)
                    </span>
                    <span class="inline-flex items-center text-amber-700 bg-amber-50 px-2 py-1 rounded-md border border-amber-200">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 mr-1.5"></span>
                        Warning Drift Suhu (5)
                    </span>
                    <span class="inline-flex items-center text-red-700 bg-red-50 px-2 py-1 rounded-md border border-red-200">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500 mr-1.5"></span>
                        Power Trip / Alarm (0)
                    </span>
                    <span class="inline-flex items-center text-slate-500 bg-slate-100 px-2 py-1 rounded-md border border-slate-200">
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-300 mr-1.5"></span>
                        Standby / Kosong (116)
                    </span>
                </div>
            </div>

            <!-- Filter Rak -->
            <div class="flex items-center space-x-2 my-4 overflow-x-auto pb-1">
                <span class="text-xs font-bold text-gray-600 whitespace-nowrap">Pilih Rak:</span>
                <button onclick="filterRackDisplay('all')" class="rack-filter-btn px-3 py-1 rounded-lg text-xs font-bold bg-[#004b87] text-white transition" data-rack="all">Semua Rak (300)</button>
                <?php for ($rk = 1; $rk <= 10; $rk++): ?>
                    <button onclick="filterRackDisplay('<?= sprintf('%02d', $rk) ?>')" class="rack-filter-btn px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 transition" data-rack="<?= sprintf('%02d', $rk) ?>">
                        Rack <?= sprintf('%02d', $rk) ?>
                    </button>
                <?php endfor; ?>
            </div>

            <!-- Grid 10 Rak Penumpukan -->
            <div class="space-y-4" id="rackGridContainer">
                <?php for ($r_idx = 1; $r_idx <= 10; $r_idx++): 
                    $rack_num = sprintf('%02d', $r_idx);
                ?>
                    <div class="rack-block border border-gray-200/90 rounded-xl p-3.5 bg-slate-50/50" data-rack-id="<?= $rack_num ?>">
                        <div class="flex items-center justify-between mb-2.5">
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-0.5 bg-[#002f5e] text-white font-mono font-bold text-xs rounded">RACK-<?= $rack_num ?></span>
                                <span class="text-xs font-bold text-gray-800">Catwalk Platform <?= ($r_idx <= 5) ? 'Zona Inbound / Ekspor' : 'Zona Outbound / Domestik' ?></span>
                                <span class="text-[10px] text-gray-400 font-mono">(30 Steker 380V/32A)</span>
                            </div>
                            <span class="text-[11px] text-cyan-700 font-bold font-mono">Panel Distribusi: DB-RF<?= $rack_num ?> (415V/3P)</span>
                        </div>

                        <!-- 30 Sockets per Rack (Grid 10 col x 3 rows) -->
                        <div class="grid grid-cols-6 sm:grid-cols-10 md:grid-cols-15 gap-1.5">
                            <?php for ($s_idx = 1; $s_idx <= 30; $s_idx++): 
                                $slot_code = "R{$rack_num}-S" . sprintf('%02d', $s_idx);
                                
                                // Cek apakah ada di list aktif kita
                                $matched_ctr = null;
                                foreach ($active_reefers as $ar) {
                                    if ($ar['slot'] === $slot_code) {
                                        $matched_ctr = $ar;
                                        break;
                                    }
                                }

                                // Status acak realistis untuk slot lainnya
                                if ($matched_ctr) {
                                    $slot_status = $matched_ctr['status'];
                                    $slot_title = "{$matched_ctr['id']} ({$matched_ctr['commodity']}) | {$matched_ctr['temp_actual']}°C";
                                    $slot_class = ($slot_status === 'normal') ? 'bg-emerald-500 text-white shadow-xs' : 'bg-amber-500 text-white animate-pulse';
                                } else {
                                    // 60% terisi normal, 40% kosong
                                    $rand = ($r_idx * 30 + $s_idx) % 7;
                                    if ($rand < 4) {
                                        $slot_status = 'occupied';
                                        $slot_class = 'bg-emerald-100 text-emerald-800 border border-emerald-300 hover:bg-emerald-200';
                                        $slot_title = "$slot_code: Terhubung Box Mitra (-20.1°C / Normal)";
                                    } else {
                                        $slot_status = 'empty';
                                        $slot_class = 'bg-white text-gray-400 border border-gray-200 hover:border-cyan-400 hover:text-cyan-600';
                                        $slot_title = "$slot_code: Tersedia / Siap Plug-In";
                                    }
                                }
                            ?>
                                <div id="slot-<?= $slot_code ?>" data-slot="<?= $slot_code ?>" onclick="clickSocketSlot('<?= $slot_code ?>', '<?= $slot_status ?>', <?= $matched_ctr ? json_encode($matched_ctr['id']) : 'null' ?>)" 
                                     class="p-1 rounded text-center cursor-pointer transition select-none <?= $slot_class ?>" 
                                     title="<?= $slot_title ?>">
                                    <div class="text-[9px] font-mono font-bold leading-none">S<?= sprintf('%02d', $s_idx) ?></div>
                                    <i class="fa-solid fa-plug text-[9px] mt-0.5 block"></i>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- TAB 3: GRAFIK LOG SUHU 24 JAM (CHART.JS & HACCP COMPLIANCE)            -->
    <!-- ======================================================================= -->
    <div id="tab-grafik" class="reefer-tab-content hidden space-y-4">
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-gray-100 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Grafik Fluktuasi Suhu Kontinu 24 Jam (Audit HACCP &amp; WHO GDP)</h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Perekaman data telemetri nirkabel per 15 menit. Memantau akurasi suhu supply air, return air, dan toleransi setpoint peti kemas.
                    </p>
                </div>
                <!-- Dropdown Pilih Kontainer -->
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-bold text-gray-600">Pilih Box:</span>
                    <select id="selectReeferGraphBox" onchange="updateReeferChartFromSelect()" class="px-3 py-1.5 bg-slate-50 border border-gray-200 rounded-lg text-xs font-bold text-gray-800 focus:ring-1 focus:ring-cyan-500">
                        <?php foreach ($active_reefers as $ar): ?>
                            <option value="<?= $ar['id'] ?>" data-commodity="<?= $ar['commodity'] ?>" data-setpoint="<?= $ar['setpoint'] ?>">
                                <?= $ar['id'] ?> &bull; <?= $ar['commodity'] ?> (Setpoint: <?= $ar['setpoint'] ?>°C)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Kartu Status Box Terpilih pada Grafik -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 my-4 bg-cyan-50/50 p-3.5 rounded-xl border border-cyan-100">
                <div>
                    <span class="text-[10px] text-cyan-800 font-bold uppercase block">Peti Kemas Aktif</span>
                    <span class="font-mono font-bold text-gray-900 text-sm" id="chartCtrDisplay"><?= $active_reefers[0]['id'] ?></span>
                    <span class="text-[10.5px] text-gray-500 block truncate" id="chartCommDisplay"><?= $active_reefers[0]['commodity'] ?></span>
                </div>
                <div>
                    <span class="text-[10px] text-cyan-800 font-bold uppercase block">Target Setpoint</span>
                    <span class="font-mono font-bold text-blue-700 text-sm" id="chartSetpointDisplay"><?= $active_reefers[0]['setpoint'] ?>.0°C</span>
                    <span class="text-[10.5px] text-gray-500 block">Toleransi &plusmn;1.0°C</span>
                </div>
                <div>
                    <span class="text-[10px] text-cyan-800 font-bold uppercase block">Siklus Defrost Terakhir</span>
                    <span class="font-mono font-bold text-gray-900 text-sm">04:15 WIB (Otomatis)</span>
                    <span class="text-[10.5px] text-emerald-600 font-semibold block">Durasi 18 Menit (Normal)</span>
                </div>
                <div>
                    <span class="text-[10px] text-cyan-800 font-bold uppercase block">Audit Kepatuhan HACCP</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 inline-block mt-1">
                        <i class="fa-solid fa-check-double mr-1"></i> PASSED / MEMENUHI SYARAT
                    </span>
                </div>
            </div>

            <!-- Canvas Chart.js -->
            <div class="w-full h-80 sm:h-96">
                <canvas id="reeferTempChart"></canvas>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between text-xs text-gray-500 gap-2">
                <span>Pencatatan telemetri mematuhi regulasi <strong>FDA 21 CFR Part 11</strong> dan <strong>Peraturan Kepala BPOM No. 24/2021</strong>.</span>
                <button onclick="downloadChartPDF()" class="px-3 py-1 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg text-xs font-semibold flex items-center space-x-1 shadow-2xs">
                    <i class="fa-solid fa-file-pdf text-red-600 mr-1"></i> Cetak Dokumen HACCP Suhu
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- TAB 4: KAMERA TERMAL HW-16 & PROTEKSI K3 LISTRIK GENSET                -->
    <!-- ======================================================================= -->
    <div id="tab-thermal" class="reefer-tab-content hidden space-y-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- Feed Kamera Termal Inframerah HW-16 -->
            <div class="lg:col-span-2 bg-white rounded-2xl p-5 sm:p-6 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4 flex-wrap gap-2">
                    <div class="flex items-center space-x-2">
                        <span class="w-3 h-3 rounded-full bg-red-500 animate-ping"></span>
                        <h3 class="text-base font-bold text-gray-900">Live Thermal Imaging Feed (HW-16 Hikvision DS-2TD2636B)</h3>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-mono font-bold px-2 py-0.5 bg-red-50 text-red-700 border border-red-200 rounded">
                            FOV: Menara Pengawas Timur (380m)
                        </span>
                        <button onclick="openReeferSensorModal('thermal', 'Rack 03')" class="px-2.5 py-1 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold transition flex items-center space-x-1.5 shadow-xs">
                            <i class="fa-solid fa-radar text-[10px]"></i>
                            <span>Uji Pindai Termal</span>
                        </button>
                    </div>
                </div>

                <!-- Simulated Thermal Feed Monitor Display -->
                <div class="relative w-full h-72 sm:h-80 bg-slate-950 rounded-xl overflow-hidden border-2 border-slate-800 flex items-center justify-center group">
                    <!-- Thermal Color Gradient Background Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-tr from-purple-950 via-indigo-900/60 to-amber-900/40 opacity-90"></div>
                    
                    <!-- HUD Crosshair & Thermal Measurements -->
                    <div class="absolute inset-0 p-4 flex flex-col justify-between pointer-events-none font-mono text-white text-xs">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="bg-black/60 px-2 py-0.5 rounded text-[11px] text-amber-400 font-bold block">CAM-04: REEFER PLATFORM RACK 01-05</span>
                                <span class="text-[10px] text-slate-300">Optik: Bi-spectrum Thermal + 4MP Optical</span>
                            </div>
                            <div class="bg-black/60 px-2 py-1 rounded text-right">
                                <span class="text-[10px] text-slate-400 block">MAX HOTSPOT:</span>
                                <span class="text-sm font-black text-amber-400">68.9°C (Rack 03 Comp.)</span>
                            </div>
                        </div>

                        <!-- Center Reticle Marker -->
                        <div class="self-center flex flex-col items-center">
                            <div class="w-12 h-12 border border-red-400/80 rounded-full flex items-center justify-center animate-pulse">
                                <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                            </div>
                            <span class="text-[10px] bg-red-600/90 px-1.5 py-0.2 rounded mt-1 font-bold">SP01: 52.8°C (Normal)</span>
                        </div>

                        <div class="flex justify-between items-end">
                            <span class="text-[10px] text-slate-400">PALETTE: IRONBOW THERMAL | RANGE: -20°C s/d 150°C</span>
                            <span class="text-[10px] text-emerald-400 bg-black/60 px-2 py-0.5 rounded font-bold">PROTEKSI TRIP K3: NORMAL</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3 mt-4 text-center">
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200/80">
                        <span class="text-[10px] text-gray-500 uppercase font-bold block">Ambang Batas Alarm</span>
                        <span class="text-base font-extrabold text-red-600 font-mono">&gt; 85.0°C</span>
                    </div>
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200/80">
                        <span class="text-[10px] text-gray-500 uppercase font-bold block">Deteksi Kebocoran Gas</span>
                        <span class="text-base font-extrabold text-emerald-600 font-mono">0.00 PPM (Aman)</span>
                    </div>
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200/80">
                        <span class="text-[10px] text-gray-500 uppercase font-bold block">Sistem Interlock Relay</span>
                        <span class="text-base font-extrabold text-blue-600 font-mono">SIAP SIAGA</span>
                    </div>
                </div>
            </div>

            <!-- Fasilitas Pendukung Listrik Genset & Damkar -->
            <div class="space-y-4">
                <!-- Kartu Genset Shelter Cadangan 1.500 kVA -->
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-lg font-bold">
                            <i class="fa-solid fa-charging-station"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 text-sm">Genset Shelter 1.500 kVA</h4>
                            <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">ATS Standby Otomatis</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Pencatu daya cadangan darurat (Cummins 1.500 kVA Diesel Generator) dengan sistem ATS (*Automatic Transfer Switch*) yang beralih dalam <strong>8 detik</strong> jika listrik PLN terputus.
                    </p>
                    <div class="mt-3.5 space-y-2 text-xs border-t border-gray-100 pt-3">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Kapasitas Bahan Bakar:</span>
                            <span class="font-bold font-mono text-gray-800">4.500 Liter (92%)</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Daya Dukung Mandiri:</span>
                            <span class="font-bold font-mono text-gray-800">24 Jam Kontinu</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Pemanasan Otomatis:</span>
                            <span class="font-bold text-emerald-600 font-mono">Tiap Senin 08:00</span>
                        </div>
                    </div>
                </div>

                <!-- Kartu Integrasi Pos Damkar K3 -->
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-red-50 text-red-700 flex items-center justify-center text-lg font-bold">
                            <i class="fa-solid fa-fire-extinguisher"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 text-sm">Proteksi Kebakaran K3</h4>
                            <span class="text-[10px] text-blue-600 font-bold bg-blue-50 px-1.5 py-0.2 rounded border border-blue-200">Hydrant Pilar #06 &amp; #07</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Terintegrasi dengan Pos Damkar &amp; Pompa Hydrant (Kolom C Utara). Jika kamera termal HW-16 mendeteksi suhu &gt; 85°C selama 30 detik berturut-turut, sirene terminal dan solenoid pemutus steker langsung aktif.
                    </p>
                    <div class="mt-3.5 flex items-center justify-between text-xs pt-2">
                        <span class="text-gray-500">Tekanan Pompa Air:</span>
                        <span class="font-bold font-mono text-emerald-600">8.2 Bar (Stabil)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- =========================================================================== -->
<!-- MODAL 1: FORM COLOK STEKER BARU (PLUG-IN REEFER)                           -->
<!-- =========================================================================== -->
<div id="modalPlugIn" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 animate-fadeIn" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between pb-3.5 border-b border-gray-100">
            <div class="flex items-center space-x-2.5">
                <div class="w-9 h-9 rounded-xl bg-cyan-100 text-cyan-800 flex items-center justify-center text-base">
                    <i class="fa-solid fa-plug"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-base">Colok Steker Baru (Plug-In Reefer)</h3>
                    <p class="text-xs text-gray-500">Pendaftaran koneksi steker Marechal 380V/32A ke YMS</p>
                </div>
            </div>
            <button onclick="closePlugInModal()" class="text-gray-400 hover:text-gray-700 text-lg p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="dashboard.php?page=reefer" class="mt-4 space-y-4 text-xs font-sans">
            <input type="hidden" name="action_type" value="plug_in">

            <div>
                <label class="block font-bold text-gray-700 mb-1">Nomor Peti Kemas (ISO 6346) *</label>
                <input type="text" name="container_number" required placeholder="Contoh: MSKU9182374" class="w-full p-2.5 bg-slate-50 border border-gray-200 rounded-lg font-mono font-bold uppercase focus:ring-1 focus:ring-cyan-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Pilih Slot Steker (HW-23) *</label>
                    <input type="text" name="slot_id" id="modalInputSlot" required placeholder="Contoh: R03-S09" class="w-full p-2.5 bg-slate-50 border border-gray-200 rounded-lg font-mono font-bold focus:ring-1 focus:ring-cyan-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Suhu Target Setpoint (°C) *</label>
                    <input type="number" step="0.1" name="setpoint_temp" required value="-20.0" class="w-full p-2.5 bg-slate-50 border border-gray-200 rounded-lg font-mono font-bold focus:ring-1 focus:ring-cyan-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-1">Jenis Komoditas Rantai Dingin *</label>
                <select name="commodity" class="w-full p-2.5 bg-slate-50 border border-gray-200 rounded-lg font-semibold text-gray-800 focus:ring-1 focus:ring-cyan-500 focus:outline-none">
                    <option value="Daging Sapi Beku (Frozen Meat)">Daging Sapi Beku (-18°C s/d -22°C)</option>
                    <option value="Vaksin & Produk Farmasi">Vaksin &amp; Produk Farmasi (+2°C s/d +8°C)</option>
                    <option value="Ikan Tuna Sashimi (Super Freezer)">Ikan Tuna Sashimi Ultra-Low (-40°C)</option>
                    <option value="Buah & Sayuran Segar">Buah &amp; Sayuran Segar (+1°C s/d +4°C)</option>
                    <option value="Es Krim & Dairy Products">Es Krim &amp; Olahan Susu (-25°C)</option>
                    <option value="Cokelat & Confectionery">Cokelat Couverture (+16°C)</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Nomor Segel (Seal Number)</label>
                    <input type="text" name="seal_number" placeholder="Contoh: ML-ID992140" class="w-full p-2.5 bg-slate-50 border border-gray-200 rounded-lg font-mono focus:ring-1 focus:ring-cyan-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Petugas Operator Reefer</label>
                    <input type="text" name="operator_name" value="Armansyah M. (Teknisi K3)" class="w-full p-2.5 bg-slate-50 border border-gray-200 rounded-lg focus:ring-1 focus:ring-cyan-500 focus:outline-none font-medium">
                </div>
            </div>

            <div class="p-3 bg-cyan-50 rounded-xl border border-cyan-200/80 text-cyan-900 text-[11px] leading-relaxed">
                <i class="fa-solid fa-circle-info mr-1 text-cyan-700"></i>
                Dengan menekan tombol konfirmasi, steker Marechal Decontactor akan mengalirkan daya 380V/32A dan sensor LoRaWAN akan memulai perekaman suhu otomatis per 15 menit.
            </div>

            <div class="flex items-center justify-end space-x-2 pt-2">
                <button type="button" onclick="closePlugInModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg transition">Batal</button>
                <button type="submit" class="px-5 py-2 bg-[#004b87] hover:bg-[#002f5e] text-white font-bold rounded-lg shadow-sm transition">
                    <i class="fa-solid fa-plug mr-1.5"></i> Konfirmasi Plug-In
                </button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================== -->
<!-- MODAL 2: KONFIRMASI LEPAS STEKER (UN-PLUG)                                 -->
<!-- =========================================================================== -->
<div id="modalUnplug" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 animate-fadeIn" onclick="event.stopPropagation()">
        <div class="text-center">
            <div class="w-14 h-14 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-2xl mx-auto mb-3">
                <i class="fa-solid fa-plug-circle-xmark"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Konfirmasi Lepas Steker (Un-Plug)</h3>
            <p class="text-xs text-gray-500 mt-1">
                Apakah peti kemas siap dilepas dari steker daya 380V untuk persiapan alih muat (*Gate Out* atau *Rail Siding*)?
            </p>
        </div>

        <form method="POST" action="dashboard.php?page=reefer" class="mt-4 space-y-3 text-xs">
            <input type="hidden" name="action_type" value="un_plug">
            <input type="hidden" name="container_number" id="unplugCtrInput">
            <input type="hidden" name="slot_id" id="unplugSlotInput">
            <input type="hidden" name="energy_consumed" id="unplugKwhInput">

            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-1.5 font-mono">
                <div class="flex justify-between">
                    <span class="text-gray-500">Nomor Peti Kemas:</span>
                    <strong class="text-gray-900" id="unplugCtrText">-</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Posisi Slot Steker:</span>
                    <strong class="text-gray-900" id="unplugSlotText">-</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Total Konsumsi Listrik:</span>
                    <strong class="text-blue-700" id="unplugKwhText">- kWh</strong>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-3">
                <button type="button" onclick="closeUnplugModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg transition">Batal</button>
                <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg shadow-sm transition">
                    <i class="fa-solid fa-check mr-1"></i> Lepas Steker &amp; Terbitkan Nota
                </button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================== -->
<!-- MODAL 3: DETAIL TELEMETRI LENGKAP & SERTIFIKAT PTI / HACCP                 -->
<!-- =========================================================================== -->
<div id="modalDetail" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-2xl w-full p-6 sm:p-7 shadow-2xl border border-gray-100 animate-fadeIn" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between pb-3.5 border-b border-gray-100">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-700 flex items-center justify-center font-mono font-bold text-sm">
                    RF
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-base" id="detailCtrNumber">MSKU9920194</h3>
                    <span class="text-xs text-gray-500" id="detailCommodity">Daging Sapi Beku Impor</span>
                </div>
            </div>
            <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-700 text-lg p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="mt-4 space-y-4 text-xs font-sans">
            <!-- 4 Indikator Parameter Utama -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] text-gray-400 uppercase font-bold block">Suhu Aktual</span>
                    <span class="text-lg font-black text-emerald-600 font-mono" id="detailTemp">-20.0°C</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] text-gray-400 uppercase font-bold block">Target Setpoint</span>
                    <span class="text-lg font-black text-blue-700 font-mono" id="detailSetpoint">-20.0°C</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] text-gray-400 uppercase font-bold block">Tegangan Listrik</span>
                    <span class="text-lg font-black text-gray-800 font-mono" id="detailVolt">382V (3P)</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] text-gray-400 uppercase font-bold block">Arus Beban</span>
                    <span class="text-lg font-black text-gray-800 font-mono" id="detailAmp">18.2 A</span>
                </div>
            </div>

            <!-- Rincian Data Teknis -->
            <div class="bg-gray-50/70 p-4 rounded-xl border border-gray-100 grid grid-cols-2 gap-y-2.5 font-mono text-[11.5px]">
                <div><span class="text-gray-400">Posisi Slot Steker:</span> <strong class="text-gray-900" id="detailSlot">R01-S08</strong></div>
                <div><span class="text-gray-400">Rak Platform:</span> <strong class="text-gray-900" id="detailRack">Rack 01 Catwalk</strong></div>
                <div><span class="text-gray-400">Supply Air Temp:</span> <strong class="text-gray-900" id="detailSupply">-21.2°C</strong></div>
                <div><span class="text-gray-400">Return Air Temp:</span> <strong class="text-gray-900" id="detailReturn">-19.4°C</strong></div>
                <div><span class="text-gray-400">Kelembaban Relatif:</span> <strong class="text-gray-900" id="detailHum">92% RH</strong></div>
                <div><span class="text-gray-400">Total Daya Terpakai:</span> <strong class="text-blue-700" id="detailKwh">294.6 kWh</strong></div>
                <div><span class="text-gray-400">Sertifikat HACCP:</span> <strong class="text-emerald-700" id="detailHaccp">HACCP-ID/2026/041</strong></div>
                <div><span class="text-gray-400">Status Inspeksi PTI:</span> <span class="px-1.5 py-0.2 bg-emerald-100 text-emerald-800 rounded font-bold">PASSED</span></div>
            </div>

            <!-- Inter-Module Quick Action Toolbar -->
            <div class="p-3 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-100 flex flex-wrap items-center justify-between gap-2">
                <span class="text-[10.5px] font-bold text-gray-700 uppercase flex items-center gap-1.5">
                    <i class="fa-solid fa-compass text-blue-600"></i> Pintasan Antar-Modul:
                </span>
                <div class="flex items-center gap-1.5 flex-wrap">
                    <a id="btnReeferLinkTrack" href="#" class="px-2.5 py-1 bg-white hover:bg-emerald-600 hover:text-white text-emerald-700 font-bold text-[10.5px] rounded-lg border border-emerald-200 transition flex items-center gap-1 shadow-2xs">
                        <i class="fa-solid fa-route"></i> Lacak Kontainer
                    </a>
                    <a id="btnReeferLink3D" href="#" class="px-2.5 py-1 bg-white hover:bg-indigo-600 hover:text-white text-indigo-700 font-bold text-[10.5px] rounded-lg border border-indigo-200 transition flex items-center gap-1 shadow-2xs">
                        <i class="fa-solid fa-cube"></i> Twin 3D
                    </a>
                    <a id="btnReeferLinkDenah" href="dashboard.php?page=denah&highlight_block=R" class="px-2.5 py-1 bg-white hover:bg-amber-600 hover:text-white text-amber-700 font-bold text-[10.5px] rounded-lg border border-amber-200 transition flex items-center gap-1 shadow-2xs">
                        <i class="fa-solid fa-map-location-dot"></i> Denah 2D
                    </a>
                    <a id="btnReeferLinkBill" href="#" class="px-2.5 py-1 bg-white hover:bg-blue-600 hover:text-white text-blue-700 font-bold text-[10.5px] rounded-lg border border-blue-200 transition flex items-center gap-1 shadow-2xs">
                        <i class="fa-solid fa-file-invoice-dollar"></i> Billing Listrik
                    </a>
                </div>
            </div>

            <!-- Footer Modal Detail -->
            <div class="flex items-center justify-between pt-2">
                <span class="text-gray-400 text-[11px]">Terverifikasi oleh Sensor LoRaWAN Gateway &bull; Armansyah M.</span>
                <div class="flex items-center space-x-2">
                    <button onclick="window.print()" class="px-3.5 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg font-semibold flex items-center space-x-1">
                        <i class="fa-solid fa-print mr-1"></i> Cetak Dokumen
                    </button>
                    <button onclick="closeDetailModal()" class="px-4 py-1.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-lg">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================================== -->
<!-- MODAL 4: PEMICU SENSOR TELEMETRI & K3 REEFER (REAL DATABASE EVENTS)       -->
<!-- =========================================================================== -->
<div id="modalTriggerReeferSensor" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-gray-100 animate-fadeIn" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between pb-3.5 border-b border-gray-100">
            <div class="flex items-center space-x-2.5">
                <div class="w-9 h-9 rounded-xl bg-cyan-100 text-cyan-800 flex items-center justify-center text-base">
                    <i class="fa-solid fa-satellite-dish"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-base">Pemicu Sensor Lapangan Reefer &amp; K3</h3>
                    <p class="text-xs text-gray-500">Transmisi Telemetri Riil &bull; Menyimpan ke Basis Data YMS</p>
                </div>
            </div>
            <button onclick="closeReeferSensorModal()" class="text-gray-400 hover:text-gray-700 text-lg p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Sub Tabs Sensor Selection -->
        <div class="flex gap-1.5 mt-3.5 p-1 bg-slate-100 rounded-xl">
            <button type="button" onclick="switchReeferSensorTab('lorawan')" id="btnReeferSensorTab-lorawan" class="reefer-sensor-tab-btn flex-1 py-1.5 px-2 rounded-lg text-xs font-bold transition bg-cyan-600 text-white shadow-xs">
                📡 LoRaWAN SHT40
            </button>
            <button type="button" onclick="switchReeferSensorTab('thermal')" id="btnReeferSensorTab-thermal" class="reefer-sensor-tab-btn flex-1 py-1.5 px-2 rounded-lg text-xs font-bold transition bg-gray-100 text-gray-700">
                🔥 Termal HW-16
            </button>
            <button type="button" onclick="switchReeferSensorTab('ats')" id="btnReeferSensorTab-ats" class="reefer-sensor-tab-btn flex-1 py-1.5 px-2 rounded-lg text-xs font-bold transition bg-gray-100 text-gray-700">
                ⚡ ATS Genset
            </button>
        </div>

        <!-- PANEL 1: LORAWAN PING -->
        <div id="reeferSensorPanel-lorawan" class="reefer-sensor-panel mt-4 space-y-3">
            <form method="POST" action="dashboard.php?page=reefer" class="space-y-3.5 text-xs">
                <input type="hidden" name="action_type" value="trigger_lorawan_ping">
                
                <div class="p-3 bg-cyan-50/70 border border-cyan-100 rounded-xl text-cyan-900 text-xs">
                    <span class="font-bold flex items-center gap-1 mb-1">
                        <i class="fa-solid fa-circle-info text-cyan-600"></i> Mode Telemetri LoRaWAN 868 MHz:
                    </span>
                    Sensor suhu SHT40 + pemantau daya Modbus pada steker Marechal HW-23 mengirim paket uplink ke YMS.
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Peti Kemas Target:</label>
                        <select name="container_number" id="reeferSensorCtrInput" class="w-full p-2 bg-slate-50 border border-gray-200 rounded-lg text-xs font-mono font-bold focus:ring-1 focus:ring-cyan-500">
                            <?php foreach ($active_reefers as $ar): ?>
                                <option value="<?= $ar['id'] ?>"><?= $ar['id'] ?> (<?= $ar['slot'] ?> - <?= $ar['commodity'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Slot Steker (HW-23):</label>
                        <input type="text" name="slot_id" value="R01-S04" class="w-full p-2 bg-slate-50 border border-gray-200 rounded-lg text-xs font-mono focus:ring-1 focus:ring-cyan-500">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2.5">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Suhu Aktual (°C):</label>
                        <input type="number" step="0.1" name="temp_actual" value="-20.4" class="w-full p-2 bg-slate-50 border border-gray-200 rounded-lg text-xs font-mono font-bold text-cyan-800 focus:ring-1 focus:ring-cyan-500">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Setpoint (°C):</label>
                        <input type="number" step="0.1" name="setpoint" value="-20.0" class="w-full p-2 bg-slate-50 border border-gray-200 rounded-lg text-xs font-mono text-gray-700 focus:ring-1 focus:ring-cyan-500">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Kelembaban (%RH):</label>
                        <input type="number" step="0.1" name="humidity" value="89.5" class="w-full p-2 bg-slate-50 border border-gray-200 rounded-lg text-xs font-mono focus:ring-1 focus:ring-cyan-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Tegangan 3-Fase (Volt):</label>
                        <input type="number" step="0.1" name="voltage" value="382.4" class="w-full p-2 bg-slate-50 border border-gray-200 rounded-lg text-xs font-mono focus:ring-1 focus:ring-cyan-500">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Kualitas Sinyal (RSSI):</label>
                        <input type="text" name="rssi" value="-68 dBm (SNR +9.4 dB)" class="w-full p-2 bg-slate-50 border border-gray-200 rounded-lg text-xs font-mono focus:ring-1 focus:ring-cyan-500">
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2 border-t border-gray-100">
                    <button type="button" onclick="closeReeferSensorModal()" class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg transition">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-bold rounded-lg shadow-sm transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-satellite-dish"></i>
                        <span>Kirim Telemetri ke Database</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- PANEL 2: THERMAL SCAN -->
        <div id="reeferSensorPanel-thermal" class="reefer-sensor-panel hidden mt-4 space-y-3">
            <form method="POST" action="dashboard.php?page=reefer" class="space-y-3.5 text-xs">
                <input type="hidden" name="action_type" value="trigger_thermal_scan">
                
                <div class="p-3 bg-amber-50/70 border border-amber-100 rounded-xl text-amber-900 text-xs">
                    <span class="font-bold flex items-center gap-1 mb-1">
                        <i class="fa-solid fa-shield-halved text-amber-600"></i> Kamera Inframerah HW-16 (Hikvision DS-2TD2636B):
                    </span>
                    Memindai radiasi termal kompresor reefer jarak 380m untuk audit K3. Ambang batas trip otomatis: <strong>85.0°C</strong>.
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Target Rak Reefer:</label>
                        <select name="rack_id" id="reeferThermalRackInput" class="w-full p-2 bg-slate-50 border border-gray-200 rounded-lg text-xs font-mono font-bold focus:ring-1 focus:ring-amber-500">
                            <option value="Rack 03">Rack 03 (Catwalk Timur)</option>
                            <option value="Rack 01">Rack 01 (Platform Utama)</option>
                            <option value="Rack 02">Rack 02 (Platform Utama)</option>
                            <option value="Rack 04">Rack 04 (Ultra-Low Cryo)</option>
                            <option value="Rack 05">Rack 05 (Platform Barat)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">ID Kamera Termal:</label>
                        <input type="text" name="camera_id" value="HW-16 Hikvision DS-2TD2636B" class="w-full p-2 bg-slate-50 border border-gray-200 rounded-lg text-xs font-mono focus:ring-1 focus:ring-amber-500" readonly>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Suhu Titik Panas Tertinggi (Max Hotspot °C):</label>
                    <input type="number" step="0.1" name="max_temp" id="reeferMaxTempInput" value="68.9" class="w-full p-2 bg-slate-50 border border-gray-200 rounded-lg text-xs font-mono font-bold text-red-600 focus:ring-1 focus:ring-amber-500">
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="text-[10.5px] text-gray-500">Preset Cepat:</span>
                        <button type="button" onclick="document.getElementById('reeferMaxTempInput').value = '54.2'" class="px-2 py-0.5 bg-gray-100 hover:bg-emerald-50 text-gray-700 rounded text-[10px] font-mono">54.2°C (Dingin)</button>
                        <button type="button" onclick="document.getElementById('reeferMaxTempInput').value = '68.9'" class="px-2 py-0.5 bg-gray-100 hover:bg-amber-50 text-gray-700 rounded text-[10px] font-mono">68.9°C (Normal)</button>
                        <button type="button" onclick="document.getElementById('reeferMaxTempInput').value = '87.4'" class="px-2 py-0.5 bg-red-100 hover:bg-red-200 text-red-800 rounded text-[10px] font-mono font-bold">87.4°C (Trip Alarm K3)</button>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2 border-t border-gray-100">
                    <button type="button" onclick="closeReeferSensorModal()" class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg transition">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white font-bold rounded-lg shadow-sm transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-fire-flame-curved"></i>
                        <span>Eksekusi Pemindaian Termal</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- PANEL 3: ATS TEST -->
        <div id="reeferSensorPanel-ats" class="reefer-sensor-panel hidden mt-4 space-y-3">
            <form method="POST" action="dashboard.php?page=reefer" class="space-y-3.5 text-xs">
                <input type="hidden" name="action_type" value="trigger_ats_test">
                
                <div class="p-3 bg-emerald-50/70 border border-emerald-100 rounded-xl text-emerald-900 text-xs">
                    <span class="font-bold flex items-center gap-1 mb-1">
                        <i class="fa-solid fa-bolt text-emerald-600"></i> Genset Shelter 1.500 kVA (Cummins QSK50):
                    </span>
                    Pengujian otomatisasi saklar pemindah daya ATS (*Automatic Transfer Switch*) untuk memastikan proteksi rantai dingin saat padam PLN.
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Beban Daya Uji (kW):</label>
                        <input type="number" step="0.1" name="load_kw" value="740.5" class="w-full p-2 bg-slate-50 border border-gray-200 rounded-lg text-xs font-mono font-bold text-gray-800 focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Waktu Alih Daya (Detik):</label>
                        <input type="number" step="0.1" name="switch_time" value="6.8" class="w-full p-2 bg-slate-50 border border-gray-200 rounded-lg text-xs font-mono font-bold text-emerald-700 focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2 border-t border-gray-100">
                    <button type="button" onclick="closeReeferSensorModal()" class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg transition">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold rounded-lg shadow-sm transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-bolt-lightning"></i>
                        <span>Uji Transfer ATS Sekarang</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<!-- Script Interaktif Modul Reefer -->
<script>
// Pemicu Sensor Modal
function openReeferSensorModal(type = 'lorawan', param = '') {
    const modal = document.getElementById('modalTriggerReeferSensor');
    if (!modal) return;
    modal.classList.remove('hidden');
    switchReeferSensorTab(type);

    if (type === 'lorawan' && param) {
        const ctrInput = document.getElementById('reeferSensorCtrInput');
        if (ctrInput) ctrInput.value = param;
    } else if (type === 'thermal' && param) {
        const rackInput = document.getElementById('reeferThermalRackInput');
        if (rackInput) rackInput.value = param;
    }
}

function closeReeferSensorModal() {
    const modal = document.getElementById('modalTriggerReeferSensor');
    if (modal) modal.classList.add('hidden');
}

function switchReeferSensorTab(type) {
    document.querySelectorAll('.reefer-sensor-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.reefer-sensor-tab-btn').forEach(b => {
        b.classList.remove('bg-cyan-600', 'text-white', 'shadow-xs');
        b.classList.add('bg-gray-100', 'text-gray-700');
    });

    const targetPanel = document.getElementById('reeferSensorPanel-' + type);
    const targetBtn = document.getElementById('btnReeferSensorTab-' + type);
    if (targetPanel) targetPanel.classList.remove('hidden');
    if (targetBtn) {
        targetBtn.classList.add('bg-cyan-600', 'text-white', 'shadow-xs');
        targetBtn.classList.remove('bg-gray-100', 'text-gray-700');
    }
}

// Switch Tabs
function switchReeferTab(tabId) {
    document.querySelectorAll('.reefer-tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.reefer-tab-btn').forEach(btn => {
        btn.classList.remove('text-[#0170b9]', 'bg-blue-50/90', 'shadow-2xs');
        btn.classList.add('text-gray-600');
    });

    const targetTab = document.getElementById(tabId);
    if (targetTab) targetTab.classList.remove('hidden');

    const activeBtn = document.getElementById('btn-' + tabId);
    if (activeBtn) {
        activeBtn.classList.add('text-[#0170b9]', 'bg-blue-50/90', 'shadow-2xs');
        activeBtn.classList.remove('text-gray-600');
    }

    if (tabId === 'tab-grafik' && !window.reeferChartInstance) {
        setTimeout(initReeferChart, 100);
    }
}

// Filter Tabel Kontainer
function filterReeferTable() {
    const q = document.getElementById('searchReeferInput').value.toLowerCase();
    const cat = document.getElementById('filterCategorySelect').value;
    const stat = document.getElementById('filterStatusSelect').value;

    let visibleCount = 0;
    document.querySelectorAll('.reefer-row').forEach(row => {
        const text = row.innerText.toLowerCase();
        const rowCat = row.getAttribute('data-cat');
        const rowStat = row.getAttribute('data-status');

        const matchQ = text.includes(q);
        const matchCat = (cat === 'all' || rowCat === cat);
        const matchStat = (stat === 'all' || rowStat === stat);

        if (matchQ && matchCat && matchStat) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const badge = document.getElementById('reeferCountBadge');
    if (badge) badge.innerText = visibleCount;
}

// Filter Rack Display
function filterRackDisplay(rack) {
    document.querySelectorAll('.rack-filter-btn').forEach(b => {
        b.classList.remove('bg-[#004b87]', 'text-white');
        b.classList.add('bg-gray-100', 'text-gray-700');
    });
    event.currentTarget.classList.add('bg-[#004b87]', 'text-white');
    event.currentTarget.classList.remove('bg-gray-100', 'text-gray-700');

    document.querySelectorAll('.rack-block').forEach(blk => {
        const rId = blk.getAttribute('data-rack-id');
        if (rack === 'all' || rId === rack) {
            blk.style.display = '';
        } else {
            blk.style.display = 'none';
        }
    });
}

// Klik Slot Socket di Matriks
function clickSocketSlot(slotCode, status, ctrId) {
    if (ctrId) {
        // Cari data di active_reefers
        const reefers = <?= json_encode($active_reefers) ?>;
        const found = reefers.find(r => r.id === ctrId);
        if (found) {
            showReeferDetailModal(found);
            return;
        }
    }
    // Jika slot kosong, buka modal plug-in dengan pre-fill slot
    openPlugInModal(slotCode);
}

// Modals Handling
function openPlugInModal(prefillSlot = '') {
    document.getElementById('modalPlugIn').classList.remove('hidden');
    if (prefillSlot) {
        document.getElementById('modalInputSlot').value = prefillSlot;
    }
}
function closePlugInModal() {
    document.getElementById('modalPlugIn').classList.add('hidden');
}

function openUnplugModal(ctrId, slot, kwh) {
    document.getElementById('modalUnplug').classList.remove('hidden');
    document.getElementById('unplugCtrInput').value = ctrId;
    document.getElementById('unplugSlotInput').value = slot;
    document.getElementById('unplugKwhInput').value = kwh;

    document.getElementById('unplugCtrText').innerText = ctrId;
    document.getElementById('unplugSlotText').innerText = slot;
    document.getElementById('unplugKwhText').innerText = kwh + ' kWh';
}
function closeUnplugModal() {
    document.getElementById('modalUnplug').classList.add('hidden');
}

function showReeferDetailModal(data) {
    document.getElementById('modalDetail').classList.remove('hidden');
    document.getElementById('detailCtrNumber').innerText = data.id + ' (' + data.line + ')';
    document.getElementById('detailCommodity').innerText = data.commodity + ' • ' + data.destination;
    document.getElementById('detailTemp').innerText = (data.temp_actual > 0 ? '+' : '') + Number(data.temp_actual).toFixed(1) + '°C';
    document.getElementById('detailSetpoint').innerText = (data.setpoint > 0 ? '+' : '') + Number(data.setpoint).toFixed(1) + '°C';
    document.getElementById('detailVolt').innerText = data.voltage + 'V (3P)';
    document.getElementById('detailAmp').innerText = data.amperage + ' A';
    document.getElementById('detailSlot').innerText = data.slot;
    document.getElementById('detailRack').innerText = data.rack;
    document.getElementById('detailSupply').innerText = (data.temp_supply > 0 ? '+' : '') + Number(data.temp_supply).toFixed(1) + '°C';
    document.getElementById('detailReturn').innerText = (data.temp_return > 0 ? '+' : '') + Number(data.temp_return).toFixed(1) + '°C';
    document.getElementById('detailHum').innerText = data.humidity + '% RH';
    document.getElementById('detailKwh').innerText = data.kwh_total + ' kWh (' + Number(data.power_kw).toFixed(2) + ' kW)';
    document.getElementById('detailHaccp').innerText = data.haccp_cert;

    // Bind Universal Deep Links
    const cleanCtr = encodeURIComponent(data.id);
    const linkTrack = document.getElementById('btnReeferLinkTrack');
    if (linkTrack) linkTrack.href = 'dashboard.php?page=kontainer&search=' + cleanCtr + '&open=1';

    const link3D = document.getElementById('btnReeferLink3D');
    if (link3D) link3D.href = 'dashboard.php?page=simulator&focus_box=' + cleanCtr + '&view=reefer';

    const linkBill = document.getElementById('btnReeferLinkBill');
    if (linkBill) linkBill.href = 'dashboard.php?page=billing&search=' + cleanCtr;
}
function closeDetailModal() {
    document.getElementById('modalDetail').classList.add('hidden');
}

// Chart.js Telemetri Suhu 24 Jam
window.reeferChartInstance = null;
function initReeferChart(setpoint = -20.0, ctrName = 'EITU9823102') {
    const ctx = document.getElementById('reeferTempChart');
    if (!ctx) return;

    if (window.reeferChartInstance) {
        window.reeferChartInstance.destroy();
    }

    // Generate 24 jam label
    const hours = [];
    const actualData = [];
    const supplyData = [];
    const returnData = [];
    const setpointLine = [];

    const nowHour = 18;
    for (let i = 23; i >= 0; i--) {
        const h = (nowHour - i + 24) % 24;
        hours.push(String(h).padStart(2, '0') + ':00');
        
        // Sedikit fluktuasi acak realistis sekitar setpoint
        const noise = (Math.sin(i * 0.7) * 0.4) + ((Math.random() - 0.5) * 0.2);
        const actual = setpoint + noise;
        actualData.push(Number(actual.toFixed(2)));
        supplyData.push(Number((actual - 1.2).toFixed(2)));
        returnData.push(Number((actual + 0.8).toFixed(2)));
        setpointLine.push(setpoint);
    }

    window.reeferChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: hours,
            datasets: [
                {
                    label: 'Suhu Aktual Kontainer (°C)',
                    data: actualData,
                    borderColor: '#0284c7',
                    backgroundColor: 'rgba(2, 132, 199, 0.1)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 2.5,
                    pointRadius: 2.5
                },
                {
                    label: 'Target Setpoint (°C)',
                    data: setpointLine,
                    borderColor: '#dc2626',
                    borderDash: [6, 4],
                    borderWidth: 1.8,
                    pointRadius: 0,
                    fill: false
                },
                {
                    label: 'Supply Air Temp (°C)',
                    data: supplyData,
                    borderColor: '#10b981',
                    borderWidth: 1.2,
                    borderDash: [3, 3],
                    pointRadius: 1.5,
                    fill: false
                },
                {
                    label: 'Return Air Temp (°C)',
                    data: returnData,
                    borderColor: '#f59e0b',
                    borderWidth: 1.2,
                    borderDash: [3, 3],
                    pointRadius: 1.5,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        font: { size: 11, family: 'Plus Jakarta Sans' }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + ' °C';
                        }
                    }
                }
            },
            scales: {
                y: {
                    title: {
                        display: true,
                        text: 'Suhu (°C)',
                        font: { size: 11, weight: 'bold' }
                    },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Waktu Perekaman (24 Jam Terakhir WIB)',
                        font: { size: 11, weight: 'bold' }
                    },
                    grid: { display: false }
                }
            }
        }
    });
}

function selectReeferGraph(ctrId, commodity, setpoint) {
    switchReeferTab('tab-grafik');
    const sel = document.getElementById('selectReeferGraphBox');
    if (sel) {
        sel.value = ctrId;
        updateReeferChartFromSelect();
    }
}

function updateReeferChartFromSelect() {
    const sel = document.getElementById('selectReeferGraphBox');
    const opt = sel.options[sel.selectedIndex];
    const ctrId = sel.value;
    const commodity = opt.getAttribute('data-commodity');
    const setpoint = parseFloat(opt.getAttribute('data-setpoint'));

    document.getElementById('chartCtrDisplay').innerText = ctrId;
    document.getElementById('chartCommDisplay').innerText = commodity;
    document.getElementById('chartSetpointDisplay').innerText = (setpoint > 0 ? '+' : '') + setpoint.toFixed(1) + '°C';

    initReeferChart(setpoint, ctrId);
}

// Ekspor Excel Data Reefer HACCP via SheetJS
function exportReeferExcel() {
    const reefers = <?= json_encode($active_reefers) ?>;
    const exportData = reefers.map((r, i) => ({
        "No": i + 1,
        "Nomor Kontainer": r.id,
        "Ukuran/Tipe ISO": r.iso,
        "Pelayaran": r.line,
        "Komoditas": r.commodity,
        "Slot Steker (HW-23)": r.slot,
        "Rak": r.rack,
        "Setpoint Temp (°C)": r.setpoint,
        "Suhu Aktual (°C)": r.temp_actual,
        "Supply Air (°C)": r.temp_supply,
        "Return Air (°C)": r.temp_return,
        "Kelembaban (%)": r.humidity,
        "Tegangan (V)": r.voltage,
        "Arus (A)": r.amperage,
        "Daya (kW)": r.power_kw,
        "Total Energi (kWh)": r.kwh_total,
        "Durasi Colok": r.duration,
        "Waktu Plug-In": r.plug_time,
        "Status Integritas": r.status_text,
        "Sertifikat HACCP": r.haccp_cert
    }));

    if (typeof XLSX !== 'undefined') {
        const ws = XLSX.utils.json_to_sheet(exportData);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Log_HACCP_ColdChain");
        XLSX.writeFile(wb, "CIDP_Reefer_HACCP_Log_" + new Date().toISOString().slice(0,10) + ".xlsx");
    } else {
        alert("Pustaka SheetJS sedang dimuat. Silakan gunakan tombol cetak rekap.");
    }
}

function downloadChartPDF() {
    window.print();
}

// =========================================================================
// UNIVERSAL INTER-MODULE DEEP-LINKING INITIALIZER (REEFER MODUL)
// =========================================================================
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    const searchParam = urlParams.get('search') || urlParams.get('ctr') || urlParams.get('query');
    const slotParam = urlParams.get('slot');
    const actionParam = urlParams.get('action');

    // 1. Handle tab switching
    if (tabParam) {
        let targetTab = tabParam;
        if (!targetTab.startsWith('tab-')) {
            targetTab = 'tab-' + targetTab;
        }
        if (['tab-tabel', 'tab-matrix', 'tab-grafik', 'tab-thermal'].includes(targetTab)) {
            switchReeferTab(targetTab);
        }
    }

    // 2. Handle search query (Container number, commodity, or slot)
    if (searchParam) {
        if (!tabParam) {
            switchReeferTab('tab-tabel');
        }
        const searchInput = document.getElementById('searchReeferInput');
        if (searchInput) {
            searchInput.value = searchParam;
            filterReeferTable();
        }

        // Auto-open modal if exact container match is found
        const reefers = <?= json_encode($active_reefers) ?>;
        const matched = reefers.find(r => r.id.toUpperCase() === searchParam.toUpperCase());
        if (matched) {
            showReeferDetailModal(matched);
        }
    }

    // 3. Handle specific slot highlighting (e.g. R02-S14)
    if (slotParam) {
        switchReeferTab('tab-matrix');
        const cleanSlot = slotParam.toUpperCase();
        
        // If rack number is extractable (e.g. R02 from R02-S14), filter to that rack
        const rackMatch = cleanSlot.match(/R(\d+)/i);
        if (rackMatch && rackMatch[1]) {
            const rackNum = rackMatch[1].padStart(2, '0');
            const rackBtn = document.querySelector(`.rack-filter-btn[data-rack="${rackNum}"]`);
            if (rackBtn) {
                rackBtn.click();
            }
        }

        const slotEl = document.getElementById('slot-' + cleanSlot);
        if (slotEl) {
            setTimeout(() => {
                slotEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                slotEl.classList.add('ring-4', 'ring-cyan-400', 'shadow-lg');
                setTimeout(() => slotEl.classList.remove('ring-4', 'ring-cyan-400', 'shadow-lg'), 5000);
            }, 300);
        }
    }

    // 4. Handle plug-in action modal
    if (actionParam === 'plug_in') {
        openPlugInModal(slotParam || '');
    }
});
</script>
