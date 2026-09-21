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
            'fuel_percent' => 100,
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
            'notes' => 'Shifting kontainer 40ft untuk persiapan loading truk B 1234 XY',
            'billable_amount' => 150000.00,
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
            'billable_amount' => 250000.00,
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
            'billable_amount' => 200000.00,
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
            'billable_amount' => 180000.00,
            'created_at' => date('Y-m-d H:i:s', time() - 14400)
        ]
    ];
}

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
            <h2 class="text-2xl font-bold text-gray-900">Lokasi & Status Alat Berat (Reach Stacker & RTG)</h2>
            <p class="text-gray-500 text-sm mt-1">
                Pantau posisi radar terminal dan kartu unit. Klik pada baris riwayat pekerjaan untuk melihat rincian pemindahan kontainer secara lengkap.
            </p>
        </div>
        <div class="flex items-center space-x-3 flex-shrink-0">
            <a href="dashboard.php?page=simulator" class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-gray-950 text-xs font-bold rounded-xl shadow-xs transition flex items-center">
                <i class="fa-solid fa-gamepad mr-2"></i> Buka Simulasi 3D
            </a>
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
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-400 tracking-wider">Armada Aktif</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1"><?= $total_units ?> <span class="text-xs font-normal text-gray-400">Unit</span></h3>
                <div class="mt-1 text-xs text-blue-600 font-medium">3 Reach Stacker + 1 RTG</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#0170b9] flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-dolly"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-400 tracking-wider">Status Operasional</p>
                <div class="flex items-center space-x-2 mt-1">
                    <span class="text-sm font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded"><?= $operating_units ?> Bekerja</span>
                    <span class="text-sm font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded"><?= $idle_units ?> Standby</span>
                </div>
                <p class="mt-1 text-xs text-gray-400">Semua Unit Sehat</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-gears"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-400 tracking-wider">Rata-Rata Utilisasi</p>
                <h3 class="text-2xl font-bold text-indigo-600 mt-1">82.5%</h3>
                <div class="mt-1 text-xs text-emerald-600 font-medium"><i class="fa-solid fa-arrow-trend-up mr-1"></i>Sangat Efisien</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-400 tracking-wider">Produktivitas Gerakan</p>
                <h3 class="text-2xl font-bold text-emerald-600 mt-1">24.2 <span class="text-xs font-normal text-gray-400">Moves / Jam</span></h3>
                <div class="mt-1 text-xs text-blue-600 font-medium">Siklus Cepat Lift-Off/On</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-gauge-high"></i>
            </div>
        </div>
    </div>

    <!-- PETA SKEMATIK POSISI ALAT BERAT DI TERMINAL (RADAR GRID) -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
            <div>
                <h3 class="font-bold text-gray-900 text-base flex items-center">
                    <i class="fa-solid fa-map-location-dot mr-2 text-[#0170b9]"></i>Peta Skematik Posisi Live Alat Berat Terminal
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Penempatan posisi fisik alat berat pada masing-masing zona yard.</p>
            </div>
            <div class="flex items-center space-x-3 text-xs">
                <span class="flex items-center text-gray-600 font-medium"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-1.5"></span>Standby</span>
                <span class="flex items-center text-gray-600 font-medium"><span class="w-2.5 h-2.5 rounded-full bg-purple-600 mr-1.5 animate-ping"></span>Sedang Memindahkan</span>
            </div>
        </div>

        <div class="bg-slate-900 rounded-2xl p-5 text-white relative overflow-hidden border border-slate-800">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                
                <!-- Blok A (RS-01) -->
                <div class="bg-slate-800/60 border border-slate-700/80 rounded-xl p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-blue-400">BLOK A (Dry 40ft)</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </div>
                    <div class="bg-slate-900/90 p-3 rounded-xl border border-slate-700 flex items-center space-x-3">
                        <span class="font-mono font-bold text-emerald-400 text-sm bg-emerald-950 px-2 py-1 rounded">RS-01</span>
                        <div class="text-xs">
                            <strong class="text-slate-200 block">Budi Santoso</strong>
                            <span class="text-slate-400 text-[10px]">Standby di Bay 01 (BBM 85%)</span>
                        </div>
                    </div>
                </div>

                <!-- Blok B (RS-02) -->
                <div class="bg-slate-800/60 border border-purple-500/50 rounded-xl p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-purple-400">BLOK B (Dry 20ft & Shifting)</span>
                        <span class="w-2 h-2 rounded-full bg-purple-500 animate-ping"></span>
                    </div>
                    <div class="bg-slate-900/90 p-3 rounded-xl border border-purple-500 flex items-center space-x-3">
                        <span class="font-mono font-bold text-white text-sm bg-purple-600 px-2 py-1 rounded">RS-02</span>
                        <div class="text-xs">
                            <strong class="text-slate-100 block">Agus Setiawan</strong>
                            <span class="text-purple-300 font-mono text-[10px] font-bold block">Angkut: MSKU9182374</span>
                        </div>
                    </div>
                </div>

                <!-- Reefer (RS-03) -->
                <div class="bg-slate-800/60 border border-slate-700/80 rounded-xl p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-cyan-400">ZONA DERMAGA REEFER & DG</span>
                        <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                    </div>
                    <div class="bg-slate-900/90 p-3 rounded-xl border border-slate-700 flex items-center space-x-3">
                        <span class="font-mono font-bold text-cyan-400 text-sm bg-cyan-950 px-2 py-1 rounded">RS-03</span>
                        <div class="text-xs">
                            <strong class="text-slate-200 block">Rudi Hermawan</strong>
                            <span class="text-slate-400 text-[10px]">Siaga Steker Reefer (BBM 90%)</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Rail Siding (RTG-01) -->
            <div class="bg-slate-800/80 border border-blue-500/30 rounded-xl p-3 flex items-center justify-between">
                <div class="flex items-center space-x-3 text-xs">
                    <span class="font-mono font-bold text-amber-400 bg-amber-950 px-2 py-1 rounded">RTG-01</span>
                    <div>
                        <strong class="text-slate-200">Joko Susilo (Konecranes RTG Electric)</strong>
                        <span class="text-slate-400 block text-[10px]">Jalur Rel Siding KA Logistik (Memuat Gerbong W08 • Daya Grid 100%)</span>
                    </div>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-900 text-blue-200">AKTIF MEMUAT KA</span>
            </div>
        </div>
    </div>

    <!-- KARTU RINGKAS TIAP UNIT ALAT BERAT (4 CARDS) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php foreach ($equipment_list as $eq): ?>
            <?php
            $is_operating = ($eq['status'] === 'operating' || $eq['status'] === 'carrying');
            ?>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100">
                    <span class="font-mono font-bold text-gray-900 text-sm bg-slate-100 px-2.5 py-0.5 rounded-lg border border-slate-200">
                        <?= htmlspecialchars($eq['equipment_id']) ?>
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $is_operating ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' ?>">
                        <?= $is_operating ? 'Operating' : 'Standby' ?>
                    </span>
                </div>
                <div class="space-y-1.5 text-xs text-gray-600 mb-3">
                    <div class="font-semibold text-gray-800"><?= htmlspecialchars($eq['operator_name']) ?></div>
                    <div class="text-[11px] text-gray-500">Blok: <strong><?= htmlspecialchars($eq['last_block']) ?></strong></div>
                    <?php if (!empty($eq['current_container'])): ?>
                        <div class="text-purple-700 font-mono font-bold text-[11px]">
                            <i class="fa-solid fa-lock mr-1"></i><?= htmlspecialchars($eq['current_container']) ?>
                        </div>
                    <?php else: ?>
                        <div class="text-gray-400 italic text-[11px]">Spreader Siap</div>
                    <?php endif; ?>
                </div>
                <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-500">
                    <span>Power: <strong><?= $eq['fuel_percent'] ?>%</strong></span>
                    <span>Jam: <strong><?= $eq['hours_today'] ?>j</strong></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- CLEAN & SPACIOUS AUDIT LOG TABEL PEMINDAHAN YARD -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-bold text-gray-900 text-base flex items-center">
                    <i class="fa-solid fa-clock-rotate-left mr-2 text-[#0170b9]"></i>Riwayat Pekerjaan Pemindahan Kontainer
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Tampilan ringkas satu baris. Klik baris untuk membuka rincian tiket pemindahan.</p>
            </div>
            <span class="text-xs font-semibold text-gray-500 bg-white px-3 py-1 rounded-lg border border-gray-200">
                <?= count($recent_events) ?> Catatan Terakhir
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="eventTable">
                <thead>
                    <tr class="bg-gray-50/80 text-gray-600 text-xs uppercase tracking-wider border-b border-gray-200">
                        <th class="py-3.5 px-4 font-semibold">Alat & Operator</th>
                        <th class="py-3.5 px-4 font-semibold">Tipe Tugas</th>
                        <th class="py-3.5 px-4 font-semibold">Nomor Kontainer</th>
                        <th class="py-3.5 px-4 font-semibold">Perpindahan Koordinat</th>
                        <th class="py-3.5 px-4 font-semibold">Waktu Eksekusi</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-gray-100">
                    <?php foreach ($recent_events as $ev): ?>
                        <?php
                        $badge_ev = 'bg-blue-50 text-blue-700 border-blue-200';
                        if ($ev['event_type'] === 'RELOCATION') $badge_ev = 'bg-purple-50 text-purple-700 border-purple-200';
                        elseif ($ev['event_type'] === 'RAIL_LOAD') $badge_ev = 'bg-indigo-50 text-indigo-700 border-indigo-200';
                        elseif ($ev['event_type'] === 'LIFT_OFF') $badge_ev = 'bg-emerald-50 text-emerald-700 border-emerald-200';

                        $from = $ev['from_block'] ? "Blok {$ev['from_block']}" . ($ev['from_bay'] ? "-{$ev['from_bay']}" : "") : 'Gate / Truk';
                        $to = $ev['to_block'] ? "Blok {$ev['to_block']}" . ($ev['to_bay'] ? "-{$ev['to_bay']}" : "") : 'Lokasi Baru';

                        $json_event = htmlspecialchars(json_encode([
                            'id' => $ev['id'],
                            'event_type' => $ev['event_type'],
                            'equipment_id' => $ev['equipment_id'],
                            'operator_name' => $ev['operator_name'],
                            'container_number' => $ev['container_number'],
                            'from_pos' => $from,
                            'to_pos' => $to,
                            'notes' => $ev['notes'] ?? 'Pemindahan rutin sesuai instruksi YMS',
                            'billable_amount' => 'Rp ' . number_format($ev['billable_amount'] ?? 150000, 0, ',', '.'),
                            'created_at' => date('d M Y, H:i \W\I\B', strtotime($ev['created_at']))
                        ]), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr class="hover:bg-blue-50/40 transition cursor-pointer group"
                            onclick='showJobModal(<?= $json_event ?>)'>
                            
                            <!-- Alat & Operator -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <span class="font-mono font-bold text-gray-900 bg-slate-100 px-2 py-0.5 rounded text-xs">
                                        <?= htmlspecialchars($ev['equipment_id']) ?>
                                    </span>
                                    <span class="text-gray-700 font-medium text-xs">
                                        <?= htmlspecialchars($ev['operator_name']) ?>
                                    </span>
                                </div>
                            </td>

                            <!-- Tipe Tugas -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border uppercase <?= $badge_ev ?>">
                                    <?= htmlspecialchars($ev['event_type']) ?>
                                </span>
                            </td>

                            <!-- Nomor Kontainer -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="font-mono font-bold text-[#0170b9] text-xs">
                                    <?= htmlspecialchars($ev['container_number']) ?>
                                </span>
                            </td>

                            <!-- Perpindahan Koordinat -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <div class="flex items-center space-x-1.5 font-medium text-xs">
                                    <span class="text-gray-600"><?= $from ?></span>
                                    <i class="fa-solid fa-arrow-right text-[10px] text-gray-400"></i>
                                    <span class="text-emerald-700 font-bold"><?= $to ?></span>
                                </div>
                            </td>

                            <!-- Waktu Eksekusi -->
                            <td class="py-4 px-4 whitespace-nowrap text-gray-500 font-mono text-[11px]">
                                <?= date('d M Y, H:i', strtotime($ev['created_at'])) ?>
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-4 text-right whitespace-nowrap">
                                <button class="px-3 py-1.5 bg-slate-100 hover:bg-[#0170b9] text-slate-700 hover:text-white text-xs font-semibold rounded-lg transition shadow-2xs inline-flex items-center space-x-1.5">
                                    <span>Detail Job</span>
                                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                                </button>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL INTERAKTIF: DETAIL TIKET PEKERJAAN ALAT BERAT (EQUIPMENT JOB MODAL) -->
<div id="jobModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden animate-fadeIn border border-gray-100">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-[#002f5e] to-[#0170b9] text-white p-5 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-xl border border-white/20">
                    <i class="fa-solid fa-dolly"></i>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-blue-200">Tiket Pekerjaan Lapangan YMS</span>
                    <h3 class="text-lg font-bold font-mono" id="j_title">JOB-RELOCATION-01</h3>
                </div>
            </div>
            <button onclick="closeJobModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4 text-xs">
            <div class="grid grid-cols-2 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
                <div>
                    <span class="text-gray-400 text-[10px] block uppercase font-semibold">Unit Alat Berat:</span>
                    <strong class="text-gray-900 text-sm font-mono" id="j_equipment">RS-02</strong>
                </div>
                <div>
                    <span class="text-gray-400 text-[10px] block uppercase font-semibold">Operator Bertugas:</span>
                    <strong class="text-gray-900 text-sm" id="j_operator">Agus Setiawan</strong>
                </div>
                <div>
                    <span class="text-gray-400 text-[10px] block uppercase font-semibold">Kontainer Dipindah:</span>
                    <strong class="text-[#0170b9] text-sm font-mono" id="j_container">MSKU9182374</strong>
                </div>
                <div>
                    <span class="text-gray-400 text-[10px] block uppercase font-semibold">Biaya Operasional:</span>
                    <strong class="text-emerald-700 text-sm font-mono" id="j_fee">Rp 150.000</strong>
                </div>
            </div>

            <!-- Perpindahan Koordinat -->
            <div class="bg-blue-50/60 p-4 rounded-xl border border-blue-100">
                <span class="text-[10px] uppercase font-bold text-blue-800 block mb-2">Perpindahan Koordinat 3D Lapangan:</span>
                <div class="flex items-center justify-between text-xs">
                    <div class="p-2.5 bg-white rounded-lg border border-blue-200 text-center flex-1 mr-2">
                        <span class="text-gray-400 text-[10px] block">Posisi Asal:</span>
                        <strong class="text-gray-800 font-mono" id="j_from">Blok A-01</strong>
                    </div>
                    <i class="fa-solid fa-arrow-right text-blue-600 text-base"></i>
                    <div class="p-2.5 bg-white rounded-lg border border-blue-200 text-center flex-1 ml-2">
                        <span class="text-gray-400 text-[10px] block">Posisi Tujuan:</span>
                        <strong class="text-emerald-700 font-mono" id="j_to">Blok B-08</strong>
                    </div>
                </div>
            </div>

            <!-- Catatan & Waktu -->
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 space-y-1">
                <span class="text-gray-400 text-[10px] block uppercase font-semibold">Catatan Lapangan Operator:</span>
                <p class="text-gray-700 font-medium" id="j_notes">Shifting kontainer untuk persiapan loading truk.</p>
                <div class="text-[10px] text-gray-400 pt-1 font-mono" id="j_time">2026-09-21 14:15 WIB</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-end">
            <button onclick="closeJobModal()" class="px-5 py-2 bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold rounded-xl transition">
                Tutup
            </button>
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

function showJobModal(data) {
    document.getElementById('j_title').innerText = 'JOB-' + data.event_type + '-#' + data.id;
    document.getElementById('j_equipment').innerText = data.equipment_id;
    document.getElementById('j_operator').innerText = data.operator_name;
    document.getElementById('j_container').innerText = data.container_number;
    document.getElementById('j_fee').innerText = data.billable_amount;
    document.getElementById('j_from').innerText = data.from_pos;
    document.getElementById('j_to').innerText = data.to_pos;
    document.getElementById('j_notes').innerText = data.notes;
    document.getElementById('j_time').innerText = data.created_at;

    document.getElementById('jobModal').classList.remove('hidden');
}

function closeJobModal() {
    document.getElementById('jobModal').classList.add('hidden');
}

window.addEventListener('click', function(e) {
    const modal = document.getElementById('jobModal');
    if (e.target === modal) {
        closeJobModal();
    }
});
</script>
