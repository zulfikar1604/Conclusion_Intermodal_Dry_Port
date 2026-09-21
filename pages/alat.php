<?php
// =============================================================================
// MODUL: LOKASI & STATUS ALAT BERAT (EQUIPMENT FLEET & LIVE POSITIONING)
// File: pages/alat.php
// CIDP Yard Management System - PT Multi Terminal Indonesia / ITL Trisakti
// =============================================================================

require_once __DIR__ . '/../connection.php';

// Ambil data armada alat berat dari database
$equipment_list = [];
try {
    $stmt = $pdo->query("SELECT * FROM equipment ORDER BY id ASC");
    $equipment_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $equipment_list = [];
}

// Fallback jika belum di-seed
if (empty($equipment_list)) {
    $equipment_list = [
        [
            'equipment_id' => 'RS-01',
            'equipment_type' => 'REACH_STACKER',
            'brand_model' => 'Kalmar DRG450-65S5',
            'operator_name' => 'Budi Santoso',
            'operator_id' => 'OPR-001',
            'status' => 'idle',
            'gps_x' => 150.00,
            'gps_y' => 200.00,
            'current_container' => null,
            'last_block' => 'A',
            'fuel_percent' => 85,
            'hours_today' => 3.5,
            'last_updated' => date('Y-m-d H:i:s')
        ],
        [
            'equipment_id' => 'RS-02',
            'equipment_type' => 'REACH_STACKER',
            'brand_model' => 'Kalmar DRG450-65S5',
            'operator_name' => 'Agus Setiawan',
            'operator_id' => 'OPR-002',
            'status' => 'operating',
            'gps_x' => 350.00,
            'gps_y' => 300.00,
            'current_container' => 'MSKU9182374',
            'last_block' => 'B',
            'fuel_percent' => 70,
            'hours_today' => 4.2,
            'last_updated' => date('Y-m-d H:i:s')
        ],
        [
            'equipment_id' => 'RS-03',
            'equipment_type' => 'REACH_STACKER',
            'brand_model' => 'Kalmar DRG450-65S5',
            'operator_name' => 'Rudi Hermawan',
            'operator_id' => 'OPR-003',
            'status' => 'idle',
            'gps_x' => 500.00,
            'gps_y' => 150.00,
            'current_container' => null,
            'last_block' => 'REEFER',
            'fuel_percent' => 90,
            'hours_today' => 2.1,
            'last_updated' => date('Y-m-d H:i:s')
        ],
        [
            'equipment_id' => 'RTG-01',
            'equipment_type' => 'RTG_CRANE',
            'brand_model' => 'Konecranes RTG Electric',
            'operator_name' => 'Joko Susilo',
            'operator_id' => 'OPR-004',
            'status' => 'operating',
            'gps_x' => 100.00,
            'gps_y' => 450.00,
            'current_container' => 'TCLU8827415',
            'last_block' => 'RAIL',
            'fuel_percent' => 100, // Grid Electric
            'hours_today' => 5.0,
            'last_updated' => date('Y-m-d H:i:s')
        ]
    ];
}

// Ambil riwayat kejadian pemindahan yard (yard_events)
$recent_events = [];
try {
    $stmt2 = $pdo->query("SELECT * FROM yard_events ORDER BY id DESC LIMIT 10");
    $recent_events = $stmt2->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $recent_events = [];
}

if (empty($recent_events)) {
    $recent_events = [
        [
            'id' => 5,
            'event_type' => 'RELOCATION',
            'container_number' => 'MSKU9182374',
            'equipment_id' => 'RS-02',
            'from_block' => 'A',
            'from_bay' => '01',
            'from_row' => '01',
            'from_tier' => '01',
            'to_block' => 'B',
            'to_bay' => '08',
            'to_row' => '03',
            'to_tier' => '02',
            'operator_name' => 'Agus Setiawan',
            'notes' => 'Shifting kontainer untuk persiapan loading truk B 1234 XY',
            'created_at' => date('Y-m-d H:i:s', time() - 1200)
        ],
        [
            'id' => 4,
            'event_type' => 'RAIL_LOAD',
            'container_number' => 'TCLU8827415',
            'equipment_id' => 'RTG-01',
            'from_block' => 'B',
            'from_bay' => '04',
            'from_row' => '02',
            'from_tier' => '01',
            'to_block' => 'RAIL',
            'to_bay' => 'W08',
            'to_row' => 'R1',
            'to_tier' => 'T1',
            'operator_name' => 'Joko Susilo',
            'notes' => 'Pemuatan ke Gerbong Datar KA Logistik KA-LOG-SMG',
            'created_at' => date('Y-m-d H:i:s', time() - 3600)
        ],
        [
            'id' => 3,
            'event_type' => 'LIFT_OFF',
            'container_number' => 'CSQU7777777',
            'equipment_id' => 'RS-03',
            'from_block' => 'GATE',
            'from_bay' => null,
            'from_row' => null,
            'from_tier' => null,
            'to_block' => 'REEFER',
            'to_bay' => '02',
            'to_row' => '01',
            'to_tier' => '01',
            'operator_name' => 'Rudi Hermawan',
            'notes' => 'Bongkar kontainer reefer dari truk B 7777 AB langsung ke colokan reefer plug',
            'created_at' => date('Y-m-d H:i:s', time() - 7200)
        ],
        [
            'id' => 2,
            'event_type' => 'LIFT_OFF',
            'container_number' => 'TEMU8888888',
            'equipment_id' => 'RS-01',
            'from_block' => 'GATE',
            'from_bay' => null,
            'from_row' => null,
            'from_tier' => null,
            'to_block' => 'A',
            'to_bay' => '01',
            'to_row' => '02',
            'to_tier' => '01',
            'operator_name' => 'Budi Santoso',
            'notes' => 'Bongkar kontainer 40ft dari armada L 8888 KL',
            'created_at' => date('Y-m-d H:i:s', time() - 14400)
        ]
    ];
}

// Hitung metrik
$total_units = count($equipment_list);
$operating_units = 0;
$idle_units = 0;
foreach ($equipment_list as $eq) {
    if ($eq['status'] === 'operating' || $eq['status'] === 'carrying') $operating_units++;
    else $idle_units++;
}
?>

<div class="space-y-6">

    <!-- Header & Subtitle -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <div class="flex items-center space-x-3 mb-1">
                <span class="px-3 py-1 text-xs font-bold uppercase rounded-full bg-blue-50 text-[#0170b9] border border-blue-200">
                    <i class="fa-solid fa-satellite-dish mr-1.5"></i> Equipment Positioning & Telemetry
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-emerald-500 animate-pulse"></span> GPS Telemetri Aktif
                </span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Lokasi & Status Armada Alat Berat (Reach Stacker & RTG)</h2>
            <p class="text-gray-500 text-sm mt-1">
                Pemantauan posisi fisik alat berat di area lapangan penumpukan yard: operator aktif, status hidrolik spreader, muatan kontainer yang sedang diangkat, level daya/BBM, dan audit pemindahan kontainer.
            </p>
        </div>
        <div class="flex items-center space-x-3 flex-shrink-0">
            <button onclick="refreshEquipment()" class="px-4 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-xl shadow-xs transition flex items-center">
                <i class="fa-solid fa-rotate mr-2 text-gray-500" id="refreshIcon"></i> Perbarui GPS
            </button>
            <a href="dashboard.php?page=kontainer" class="px-4 py-2.5 bg-[#004b87] hover:bg-[#002f5e] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center">
                <i class="fa-solid fa-boxes-stacked mr-2"></i> Ke Data Kontainer <i class="fa-solid fa-arrow-right ml-1.5"></i>
            </a>
        </div>
    </div>

    <!-- 4 Telemetry Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Unit Siap -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-400 tracking-wider">Total Armada Aktif</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1"><?= $total_units ?> <span class="text-xs font-normal text-gray-400">Unit</span></h3>
                <div class="mt-2 text-xs text-blue-600 flex items-center font-medium">
                    <i class="fa-solid fa-check-circle mr-1"></i> 3 Reach Stacker + 1 RTG
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#0170b9] flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-dolly"></i>
            </div>
        </div>

        <!-- Status Operasi -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-400 tracking-wider">Status Operasional</p>
                <div class="flex items-center space-x-2 mt-1">
                    <span class="text-sm font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-100"><?= $operating_units ?> Bekerja</span>
                    <span class="text-sm font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100"><?= $idle_units ?> Standby</span>
                </div>
                <p class="mt-2 text-xs text-gray-400">0 Unit Dalam Perbaikan</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-gears"></i>
            </div>
        </div>

        <!-- Utilisasi Armada -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-400 tracking-wider">Rata-Rata Utilisasi</p>
                <h3 class="text-2xl font-bold text-indigo-600 mt-1">82.5%</h3>
                <div class="mt-2 text-xs text-emerald-600 flex items-center font-medium">
                    <i class="fa-solid fa-arrow-trend-up mr-1"></i> Optimal (Target > 75%)
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>

        <!-- Produktivitas Moves/Jam -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-400 tracking-wider">Produktivitas Lapangan</p>
                <h3 class="text-2xl font-bold text-emerald-600 mt-1">24.2 <span class="text-xs font-normal text-gray-400">Moves / Jam</span></h3>
                <div class="mt-2 text-xs text-blue-600 flex items-center font-medium">
                    <i class="fa-solid fa-bolt mr-1"></i> Siklus Cepat Lift-Off/On
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-gauge-high"></i>
            </div>
        </div>

    </div>

    <!-- RADAR VISUAL LOKASI TERMINAL YARD (SCHEMATIC POSITIONING MAP) -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-4 mb-4">
            <div>
                <h3 class="font-bold text-gray-900 text-base flex items-center">
                    <i class="fa-solid fa-map-location-dot mr-2 text-[#0170b9]"></i>Peta Skematik Posisi Live Alat Berat di Zona Terminal
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Visualisasi penempatan alat berat pada zona operasional dry port secara real-time.</p>
            </div>
            <div class="flex items-center space-x-3 text-xs">
                <span class="flex items-center text-gray-600 font-medium"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-1.5"></span>Standby / Siaga</span>
                <span class="flex items-center text-gray-600 font-medium"><span class="w-2.5 h-2.5 rounded-full bg-purple-600 mr-1.5 animate-ping"></span>Sedang Memindahkan</span>
            </div>
        </div>

        <!-- Terminal Schematic Grid -->
        <div class="bg-slate-900 rounded-2xl p-5 text-white relative overflow-hidden shadow-inner border border-slate-800">
            <!-- Grid Watermark & Compass -->
            <div class="absolute right-4 top-4 text-slate-700 font-mono text-xs flex items-center space-x-1">
                <i class="fa-solid fa-compass text-sm text-slate-600"></i>
                <span>UTARA ➔</span>
            </div>

            <!-- Zona Gerbang Utara -->
            <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-3 mb-4 flex items-center justify-between text-xs">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                    <span class="font-bold text-slate-300">ZONA GERBANG UTARA & JEMBATAN TIMBANG (WEIGHBRIDGE)</span>
                </div>
                <span class="text-slate-400 text-[10px]">Akses Truk Kontainer Inbound & Outbound</span>
            </div>

            <!-- Zona Blok Penumpukan Yard (3 Kolom Utama) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                
                <!-- Blok A (Zona Barat) -->
                <div class="bg-slate-800/60 border border-slate-700/80 rounded-xl p-4 relative group hover:border-blue-500 transition">
                    <div class="flex justify-between items-start mb-3">
                        <span class="text-xs font-bold text-blue-400">BLOK A (Dry 40ft - Zona Barat)</span>
                        <span class="text-[10px] text-slate-400 bg-slate-900 px-2 py-0.5 rounded">Slot 01 - 20</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mb-4">Area penumpukan kargo kering ekspor-impor.</p>
                    
                    <!-- Alat di Blok A: RS-01 -->
                    <div class="bg-slate-900/90 border border-emerald-500/40 rounded-xl p-3 flex items-center space-x-3">
                        <div class="relative">
                            <div class="w-10 h-10 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-sm border border-emerald-500/50">
                                RS-01
                            </div>
                            <span class="absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        </div>
                        <div class="text-xs">
                            <strong class="text-slate-200 block">Budi Santoso</strong>
                            <span class="text-emerald-400 text-[10px] font-semibold">Standby di Bay 01 (BBM 85%)</span>
                        </div>
                    </div>
                </div>

                <!-- Blok B (Zona Tengah) -->
                <div class="bg-slate-800/60 border border-purple-500/50 rounded-xl p-4 relative group hover:border-purple-400 transition">
                    <div class="flex justify-between items-start mb-3">
                        <span class="text-xs font-bold text-purple-400">BLOK B (Dry 20ft & Shifting - Zona Tengah)</span>
                        <span class="text-[10px] text-purple-300 bg-purple-950 px-2 py-0.5 rounded border border-purple-700/50">AKTIF MEMINDAHKAN</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mb-4">Zona sirkulasi muat truk dan penataan ulang.</p>
                    
                    <!-- Alat di Blok B: RS-02 -->
                    <div class="bg-slate-900/90 border border-purple-500 rounded-xl p-3 flex items-center space-x-3 shadow-lg shadow-purple-950/50">
                        <div class="relative">
                            <div class="w-10 h-10 rounded-lg bg-purple-600 text-white flex items-center justify-center font-bold text-sm">
                                RS-02
                            </div>
                            <span class="absolute -top-1 -right-1 w-3 h-3 rounded-full bg-purple-500 animate-ping"></span>
                        </div>
                        <div class="text-xs">
                            <strong class="text-slate-100 block">Agus Setiawan</strong>
                            <span class="text-purple-300 text-[10px] font-mono font-bold block">Mengangkat: MSKU9182374</span>
                            <span class="text-slate-400 text-[10px]">Bay 08 Row 03 Tier 02 (BBM 70%)</span>
                        </div>
                    </div>
                </div>

                <!-- Zona Reefer & DG (Zona Timur) -->
                <div class="bg-slate-800/60 border border-slate-700/80 rounded-xl p-4 relative group hover:border-cyan-500 transition">
                    <div class="flex justify-between items-start mb-3">
                        <span class="text-xs font-bold text-cyan-400">ZONA DERMAGA REEFER & DG (Zona Timur)</span>
                        <span class="text-[10px] text-slate-400 bg-slate-900 px-2 py-0.5 rounded">Cold Plugs & IMO</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mb-4">Dermaga listrik pendingin (-25°C) & isolasi B3.</p>
                    
                    <!-- Alat di Reefer: RS-03 -->
                    <div class="bg-slate-900/90 border border-cyan-500/40 rounded-xl p-3 flex items-center space-x-3">
                        <div class="relative">
                            <div class="w-10 h-10 rounded-lg bg-cyan-500/20 text-cyan-400 flex items-center justify-center font-bold text-sm border border-cyan-500/50">
                                RS-03
                            </div>
                            <span class="absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
                        </div>
                        <div class="text-xs">
                            <strong class="text-slate-200 block">Rudi Hermawan</strong>
                            <span class="text-cyan-400 text-[10px] font-semibold">Siaga Steker Reefer (BBM 90%)</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Zona Rail Siding (Selatan) - RTG-01 -->
            <div class="bg-gradient-to-r from-slate-800 via-slate-800/90 to-blue-950/60 border border-blue-500/40 rounded-xl p-4 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-3">
                    <div class="w-11 h-11 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-base shadow-md">
                        <i class="fa-solid fa-train"></i>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <h4 class="font-bold text-sm text-slate-100">JALUR REL SIDING KA LOGISTIK (Intermodal Rail Yard)</h4>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">KA-LOG-JKT-SMG</span>
                        </div>
                        <p class="text-slate-400 text-xs mt-0.5">Operasional pemuatan 20 gerbong datar kontainer ke jaringan rel nasional.</p>
                    </div>
                </div>

                <div class="flex items-center space-x-3 bg-slate-900/80 px-4 py-2.5 rounded-xl border border-slate-700">
                    <div class="w-9 h-9 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-xs border border-amber-500/40">
                        RTG-01
                    </div>
                    <div class="text-xs">
                        <span class="text-slate-300 font-bold block">Joko Susilo (Konecranes RTG)</span>
                        <span class="text-amber-400 text-[10px] font-semibold">Sedang Muat Gerbong W08 • Daya Listrik Grid 100%</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- SPESIFIKASI DETAIL & KARTU TELEMETRI TIAP UNIT ALAT BERAT -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <?php foreach ($equipment_list as $eq): ?>
            <?php
            $is_rs = ($eq['equipment_type'] === 'REACH_STACKER');
            $status_color = ($eq['status'] === 'operating' || $eq['status'] === 'carrying') ? 
                'bg-purple-50 text-purple-700 border-purple-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200';
            $status_label = ($eq['status'] === 'operating' || $eq['status'] === 'carrying') ? 'Operating (Sedang Memindahkan)' : 'Standby / Siaga';
            
            // Lokasi deskripsi
            $loc_desc = 'Blok ' . $eq['last_block'];
            if ($eq['last_block'] === 'A') $loc_desc = 'Blok A (Barat) - Slot A-01-01';
            elseif ($eq['last_block'] === 'B') $loc_desc = 'Blok B (Tengah) - Slot B-08-03';
            elseif ($eq['last_block'] === 'REEFER') $loc_desc = 'Zona Dermaga Reefer Rack R-02';
            elseif ($eq['last_block'] === 'RAIL') $loc_desc = 'Jalur Rel Intermodal Rail Siding';
            ?>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
                <div class="flex items-start justify-between border-b border-gray-100 pb-3 mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-xl <?= $is_rs ? 'bg-blue-50 text-[#0170b9]' : 'bg-amber-50 text-amber-600' ?> flex items-center justify-center text-xl font-bold shadow-inner">
                            <i class="fa-solid <?= $is_rs ? 'fa-dolly' : 'fa-train-subway' ?>"></i>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <h3 class="font-bold text-gray-900 text-base"><?= htmlspecialchars($eq['equipment_id']) ?></h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border <?= $status_color ?>">
                                    <?= $status_label ?>
                                </span>
                            </div>
                            <p class="text-xs text-gray-500"><?= htmlspecialchars($eq['brand_model']) ?></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-gray-400 block uppercase font-semibold">Operator Aktif:</span>
                        <strong class="text-xs text-gray-800"><?= htmlspecialchars($eq['operator_name']) ?></strong>
                        <span class="text-[10px] text-gray-400 block"><?= htmlspecialchars($eq['operator_id']) ?></span>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-2 gap-3 text-xs mb-4">
                    <div class="p-3 bg-gray-50 rounded-xl">
                        <span class="text-[10px] text-gray-400 block uppercase font-semibold">Lokasi Terkini di Yard:</span>
                        <strong class="text-gray-900 mt-0.5 block"><?= $loc_desc ?></strong>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl">
                        <span class="text-[10px] text-gray-400 block uppercase font-semibold">Kontainer di Spreader:</span>
                        <?php if (!empty($eq['current_container'])): ?>
                            <strong class="text-purple-700 font-mono mt-0.5 block font-bold">
                                <i class="fa-solid fa-lock mr-1"></i><?= htmlspecialchars($eq['current_container']) ?>
                            </strong>
                        <?php else: ?>
                            <span class="text-gray-400 italic mt-0.5 block">Spreader Siap (Kosong)</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Fuel & Hour Bar -->
                <div class="space-y-2 text-xs">
                    <div>
                        <div class="flex justify-between text-[11px] mb-1">
                            <span class="text-gray-500 font-medium"><?= $is_rs ? 'Kapasitas Bahan Bakar (BBM Solar)' : 'Pasokan Listrik Grid 380V' ?></span>
                            <span class="font-bold text-gray-800"><?= $eq['fuel_percent'] ?>%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="h-2 rounded-full <?= $eq['fuel_percent'] > 75 ? 'bg-emerald-500' : 'bg-amber-500' ?>" style="width: <?= $eq['fuel_percent'] ?>%"></div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center text-[11px] text-gray-500 pt-1">
                        <span>Jam Operasi Hari Ini: <strong class="text-gray-800"><?= $eq['hours_today'] ?> Jam</strong></span>
                        <span class="text-[10px] text-gray-400">GPS Ping: Baru saja</span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- LOG PEMINDAHAN YARD TERBARU (HANDLING & RELOCATION EVENTS AUDIT) -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-bold text-gray-900 text-base flex items-center">
                    <i class="fa-solid fa-clock-rotate-left mr-2 text-[#0170b9]"></i>Riwayat Pekerjaan & Pemindahan Kontainer (Handling Audit Log)
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Catatan log pemindahan posisi stacking kontainer yang dilakukan operator alat berat sesuai scope simulasi YMS.</p>
            </div>
            <span class="text-xs font-semibold text-gray-500 bg-white px-3 py-1 rounded-lg border border-gray-200">
                <?= count($recent_events) ?> Catatan Terakhir
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 text-gray-600 text-xs uppercase tracking-wider border-b border-gray-200">
                        <th class="py-3 px-4 font-semibold">Tipe Pekerjaan</th>
                        <th class="py-3 px-4 font-semibold">Alat & Operator</th>
                        <th class="py-3 px-4 font-semibold">Nomor Kontainer</th>
                        <th class="py-3 px-4 font-semibold">Perpindahan Posisi (Asal ➔ Tujuan)</th>
                        <th class="py-3 px-4 font-semibold">Catatan Operasional</th>
                        <th class="py-3 px-4 font-semibold text-right">Waktu Eksekusi</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-gray-100">
                    <?php foreach ($recent_events as $ev): ?>
                        <?php
                        $badge_ev = 'bg-blue-50 text-blue-700 border-blue-200';
                        if ($ev['event_type'] === 'RELOCATION') $badge_ev = 'bg-purple-50 text-purple-700 border-purple-200';
                        elseif ($ev['event_type'] === 'RAIL_LOAD') $badge_ev = 'bg-indigo-50 text-indigo-700 border-indigo-200';
                        elseif ($ev['event_type'] === 'LIFT_OFF') $badge_ev = 'bg-emerald-50 text-emerald-700 border-emerald-200';

                        // Format Asal -> Tujuan
                        $from = $ev['from_block'] ? "Blok {$ev['from_block']}" . ($ev['from_bay'] ? "-{$ev['from_bay']}" : "") : 'Gate / Truk';
                        $to = $ev['to_block'] ? "Blok {$ev['to_block']}" . ($ev['to_bay'] ? "-{$ev['to_bay']}" : "") : 'Lokasi Baru';
                        ?>
                        <tr class="hover:bg-blue-50/20 transition">
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase <?= $badge_ev ?>">
                                    <?= htmlspecialchars($ev['event_type']) ?>
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <strong class="text-gray-900"><?= htmlspecialchars($ev['equipment_id']) ?></strong>
                                <span class="text-gray-500 block text-[11px]"><?= htmlspecialchars($ev['operator_name']) ?></span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-[#0170b9]">
                                <?= htmlspecialchars($ev['container_number']) ?>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center space-x-1.5 font-medium">
                                    <span class="text-gray-600"><?= $from ?></span>
                                    <i class="fa-solid fa-arrow-right text-[10px] text-gray-400"></i>
                                    <span class="text-emerald-700 font-bold"><?= $to ?></span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-gray-600 max-w-xs truncate" title="<?= htmlspecialchars($ev['notes']) ?>">
                                <?= htmlspecialchars($ev['notes']) ?>
                            </td>
                            <td class="py-3.5 px-4 text-right text-gray-400 font-mono text-[11px]">
                                <?= date('d M Y, H:i', strtotime($ev['created_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function refreshEquipment() {
    const icon = document.getElementById('refreshIcon');
    icon.classList.add('fa-spin');
    setTimeout(() => {
        icon.classList.remove('fa-spin');
    }, 800);
}
</script>
