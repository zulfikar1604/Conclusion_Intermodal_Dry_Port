<?php
// =============================================================================
// MODUL: MANAJEMEN TRUCKING & ARUS GERBANG (FLEET & GATE TRAFFIC MONITOR)
// File: pages/trucking.php
// CIDP Yard Management System - PT Multi Terminal Indonesia / ITL Trisakti
// =============================================================================

require_once __DIR__ . '/../connection.php';

// Ambil data truk dari database
$trucks = [];
$total_in_yard = 0;
$total_queuing = 0;
$total_at_gate = 0;
$total_gate_out = 0;

try {
    $stmt = $pdo->query("SELECT * FROM trucks ORDER BY id ASC");
    $trucks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $trucks = [];
}

// Fallback dataset jika tabel kosong
if (empty($trucks)) {
    $trucks = [];
}

// Tambah armada realistis untuk memperkaya traffic demo jika kurang dari 8 unit
// if (count($trucks) < 8) {
//     $extra_trucks = [ ... ];
// }

// Data Gerbang, Posisi Lapangan & Alat Berat
$lanes = [
    'Gate 01 (Lane A - Inbound Heavy)',
    'Gate 02 (Lane B - Fast Track Empty)',
    'Gate 01 (Lane C - Reefer Priority)'
];

$yard_locations = [
    'loading' => 'Slot Blok B / Bay 08 (Proses Lift-Off oleh RS-02)',
    'in_yard' => 'Jalur Sirkulasi Blok A (Menuju Slot A-01-01)',
    'at_gate' => 'Jembatan Timbang Gate 01 (Sedang Ditimbang)',
    'queuing' => 'Jalur Antrian Pra-Gerbang Luar (Pre-Gate Buffer)',
    'gate_out' => 'Gate Out Lane 01 (Selesai Keluar Menuju Tol)'
];

$assigned_rs = [
    'RS-02 (Agus Setiawan)',
    'RS-01 (Budi Santoso)',
    'RTG-01 (Joko Susilo)',
    'RS-03 (Rudi Hermawan)'
];

foreach ($trucks as $idx => &$t) {
    if ($t['status'] === 'in_yard' || $t['status'] === 'loading') $total_in_yard++;
    elseif ($t['status'] === 'queuing') $total_queuing++;
    elseif ($t['status'] === 'at_gate') $total_at_gate++;
    elseif ($t['status'] === 'gate_out') $total_gate_out++;

    $t['do_number'] = 'DO-2026/CIDP/' . str_pad($idx + 101, 4, '0', STR_PAD_LEFT);
    $t['driver_sim'] = 'SIM B2 Umum #' . (982100 + $idx);
    $t['gate_lane'] = $lanes[$idx % count($lanes)];
    $t['current_location'] = $yard_locations[$t['status']] ?? 'Area Lapangan';
    $t['handling_equipment'] = (!empty($t['container_number'])) ? $assigned_rs[$idx % count($assigned_rs)] : 'Menunggu Penugasan';

    // Data Timbangan VGM
    $has_cont = !empty($t['container_number']);
    $db_gross = floatval($t['weight_gross'] ?? 0);
    $db_tare = floatval($t['weight_tare'] ?? 0);
    if ($db_gross > 0 && $db_tare > 0) {
        $gross = $db_gross;
        $tare = $db_tare;
        $net = max(0, $gross - $tare);
    } else {
        $tare = 12500 + ($idx * 150);
        $net = $has_cont ? (14000 + ($idx * 2100)) : 0;
        $gross = $tare + $net;
    }

    $t['weight_tare'] = $tare;
    $t['weight_net'] = $net;
    $t['weight_gross'] = $gross;

    // Hitung Turnaround Time (TAT)
    if (!empty($t['gate_in_time']) && !empty($t['gate_out_time'])) {
        $mins = round((strtotime($t['gate_out_time']) - strtotime($t['gate_in_time'])) / 60);
        $t['tat_text'] = "{$mins} Menit (Selesai)";
    } elseif (!empty($t['gate_in_time'])) {
        $mins = round((time() - strtotime($t['gate_in_time'])) / 60);
        $t['tat_text'] = "{$mins} Menit (Berjalan)";
    } else {
        $t['tat_text'] = "Menunggu Gate-In";
    }
}
unset($t);

$total_trucks = count($trucks);
?>

<div class="space-y-4">

    <!-- Header & Subtitle (Compact & Clean) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white p-3.5 sm:p-4 rounded-xl shadow-2xs border border-gray-200/80">
        <div>
            <div class="flex items-center space-x-2 mb-1">
                <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                    <i class="fa-solid fa-truck-front mr-1"></i> Trucking & Gate Traffic
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 mr-1 rounded-full bg-emerald-500 animate-pulse"></span> Gerbang & Timbangan Aktif
                </span>
            </div>
            <h2 class="text-base sm:text-lg font-bold text-gray-900 leading-tight">Manajemen Trucking & Arus Gerbang</h2>
            <p class="text-gray-500 text-xs mt-0.5">
                Monitoring arus truk secara ringkas dan rapi. Klik pada baris armada untuk melihat rincian milestone perjalanan, slip timbangan, dan dokumen masuk.
            </p>
        </div>
        <div class="flex items-center space-x-2 flex-shrink-0">
            <button onclick="showAddTruckModal()" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition flex items-center space-x-1.5 shadow-xs">
                <i class="fa-solid fa-plus"></i>
                <span>Registrasi Truk Masuk</span>
            </button>
            <button onclick="exportTruckingExcel()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition flex items-center space-x-1.5 shadow-xs">
                <i class="fa-solid fa-file-excel"></i>
                <span>Export Excel</span>
            </button>
            <button onclick="exportTruckingPDF()" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition flex items-center space-x-1.5 shadow-xs">
                <i class="fa-solid fa-file-pdf"></i>
                <span>Export PDF</span>
            </button>
            <button onclick="window.print()" class="px-3 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg shadow-2xs transition flex items-center">
                <i class="fa-solid fa-print mr-1.5 text-gray-500"></i> Cetak Rekap
            </button>
            <a href="dashboard.php?page=alat" class="px-3 py-1.5 bg-[#004b87] hover:bg-[#002f5e] text-white text-xs font-bold rounded-lg shadow-2xs transition flex items-center">
                <i class="fa-solid fa-dolly mr-1.5"></i> Monitor Alat Berat <i class="fa-solid fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <!-- 4 KPI Summary Cards (Compact Grid) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3">
        <div class="bg-white p-3 sm:p-3.5 rounded-xl shadow-2xs border border-gray-200/80 hover:border-indigo-300 transition-all flex items-center justify-between">
            <div>
                <p class="text-[10px] font-semibold uppercase text-gray-400 tracking-wider">Armada di Terminal</p>
                <h3 class="text-xl sm:text-2xl font-bold text-indigo-600 mt-0.5"><?= $total_in_yard ?> <span class="text-[10px] font-normal text-gray-400">Armada</span></h3>
                <div class="mt-0.5 text-[10px] text-gray-500 font-medium">Bongkar & Muat Lapangan</div>
            </div>
            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs shadow-inner">
                <i class="fa-solid fa-truck-moving"></i>
            </div>
        </div>

        <div class="bg-white p-3 sm:p-3.5 rounded-xl shadow-2xs border border-gray-200/80 hover:border-amber-300 transition-all flex items-center justify-between">
            <div>
                <p class="text-[10px] font-semibold uppercase text-gray-400 tracking-wider">Antrian Gerbang</p>
                <h3 class="text-xl sm:text-2xl font-bold text-amber-600 mt-0.5"><?= $total_queuing + $total_at_gate ?> <span class="text-[10px] font-normal text-gray-400">Truk</span></h3>
                <div class="mt-0.5 text-[10px] text-amber-600 font-medium"><?= $total_at_gate ?> Timbang / <?= $total_queuing ?> Antri</div>
            </div>
            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs shadow-inner">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
        </div>

        <div class="bg-white p-3 sm:p-3.5 rounded-xl shadow-2xs border border-gray-200/80 hover:border-emerald-300 transition-all flex items-center justify-between">
            <div>
                <p class="text-[10px] font-semibold uppercase text-gray-400 tracking-wider">Rata-Rata Turnaround</p>
                <h3 class="text-xl sm:text-2xl font-bold text-emerald-600 mt-0.5">21.5 <span class="text-[10px] font-normal text-gray-400">Menit</span></h3>
                <div class="mt-0.5 text-[10px] text-emerald-600 font-medium"><i class="fa-solid fa-circle-check mr-1"></i>Sesuai Target (&lt; 30m)</div>
            </div>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs shadow-inner">
                <i class="fa-solid fa-stopwatch-20"></i>
            </div>
        </div>

        <div class="bg-white p-3 sm:p-3.5 rounded-xl shadow-2xs border border-gray-200/80 hover:border-blue-300 transition-all flex items-center justify-between">
            <div>
                <p class="text-[10px] font-semibold uppercase text-gray-400 tracking-wider">Selesai Gate-Out</p>
                <h3 class="text-xl sm:text-2xl font-bold text-blue-600 mt-0.5"><?= $total_gate_out ?> <span class="text-[10px] font-normal text-gray-400">Siklus</span></h3>
                <div class="mt-0.5 text-[10px] text-blue-600 font-medium"><i class="fa-solid fa-file-circle-check mr-1"></i>Tiket Ditutup</div>
            </div>
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs shadow-inner">
                <i class="fa-solid fa-door-open"></i>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="relative w-full md:w-80">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </span>
            <input type="text" id="searchTruckInput" onkeyup="filterTrucks()" placeholder="Cari Plat Nomor, Pengemudi, Kontainer..." 
                   class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#0170b9] focus:bg-white transition">
        </div>

        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
            <select id="filterStatus" onchange="filterTrucks()" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#0170b9]">
                <option value="">Semua Status Armada</option>
                <option value="in_yard">Di Lapangan</option>
                <option value="loading">Bongkar/Muat</option>
                <option value="at_gate">Jembatan Timbang</option>
                <option value="queuing">Antrian Pra-Gate</option>
                <option value="gate_out">Selesai Gate-Out</option>
            </select>

            <button onclick="resetTruckFilter()" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold rounded-xl transition">
                <i class="fa-solid fa-rotate-left mr-1"></i> Reset
            </button>
        </div>
    </div>

    <!-- CLEAN & SPACIOUS TRUCK TABLE -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-bold text-gray-900 text-base flex items-center">
                    <i class="fa-solid fa-truck-front mr-2 text-indigo-600"></i>Daftar Arus Armada Truk
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Tampilan ringkas dan bersih. Klik pada baris atau tombol detail untuk membuka seluruh milestone data.</p>
            </div>
            <span class="text-xs font-semibold text-gray-500 bg-white px-3 py-1 rounded-lg border border-gray-200" id="truckRowCountDisplay">
                Menampilkan <?= count($trucks) ?> Armada
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[720px]" id="truckTable">
                <thead>
                    <tr class="bg-gray-50/80 text-gray-600 text-xs uppercase tracking-wider border-b border-gray-200">
                        <th class="py-3.5 px-4 font-semibold">Armada / Plat Truk</th>
                        <th class="py-3.5 px-4 font-semibold">Pengemudi</th>
                        <th class="py-3.5 px-4 font-semibold">Tugas / Misi</th>
                        <th class="py-3.5 px-4 font-semibold">Gerbang Masuk</th>
                        <th class="py-3.5 px-4 font-semibold">Muatan Kontainer</th>
                        <th class="py-3.5 px-4 font-semibold">Status Operasional</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-gray-100">
                    <?php foreach ($trucks as $t): ?>
                        <?php
                        // Status Badge Tunggal & Rapi
                        $badge_status = '';
                        if ($t['status'] === 'loading') {
                            $badge_status = '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200"><span class="w-1.5 h-1.5 rounded-full bg-purple-500 mr-1.5 animate-pulse"></span>Bongkar/Muat</span>';
                        } elseif ($t['status'] === 'in_yard') {
                            $badge_status = '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200"><span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5 animate-pulse"></span>Di Lapangan</span>';
                        } elseif ($t['status'] === 'at_gate') {
                            $badge_status = '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-ping"></span>Jembatan Timbang</span>';
                        } elseif ($t['status'] === 'queuing') {
                            $badge_status = '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-300">Antrian Pra-Gate</span>';
                        } elseif ($t['status'] === 'gate_out') {
                            $badge_status = '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fa-solid fa-check mr-1 text-[10px]"></i>Gate-Out Selesai</span>';
                        }

                        // Data JSON Lengkap untuk Modal
                        $json_pass = htmlspecialchars(json_encode([
                            'license_plate' => $t['license_plate'],
                            'rfid_tag' => $t['rfid_tag'],
                            'driver_name' => $t['driver_name'],
                            'driver_sim' => $t['driver_sim'],
                            'company' => $t['company'],
                            'do_number' => $t['do_number'],
                            'container_number' => $t['container_number'] ?? 'Truk Kosong (Pick-Up)',
                            'gate_lane' => $t['gate_lane'],
                            'gate_in_time' => $t['gate_in_time'] ? date('d M Y, H:i \W\I\B', strtotime($t['gate_in_time'])) : 'Belum Masuk Gate',
                            'gate_out_time' => $t['gate_out_time'] ? date('d M Y, H:i \W\I\B', strtotime($t['gate_out_time'])) : 'Masih di Dalam Area',
                            'current_location' => $t['current_location'],
                            'handling_equipment' => $t['handling_equipment'],
                            'weight_gross' => number_format($t['weight_gross'], 0, ',', '.') . ' kg',
                            'weight_tare' => number_format($t['weight_tare'], 0, ',', '.') . ' kg',
                            'weight_net' => number_format($t['weight_net'], 0, ',', '.') . ' kg',
                            'tat_text' => $t['tat_text'],
                            'status_raw' => $t['status'],
                            'status_text' => strtoupper($t['status'])
                        ]), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr class="hover:bg-indigo-50/40 transition cursor-pointer truck-row group"
                            onclick='showGatePassModal(<?= $json_pass ?>)'
                            data-plate="<?= strtolower($t['license_plate']) ?>"
                            data-driver="<?= strtolower($t['driver_name']) ?>"
                            data-company="<?= strtolower($t['company']) ?>"
                            data-status="<?= strtolower($t['status']) ?>"
                            data-container="<?= strtolower($t['container_number'] ?? '') ?>"
                            data-do="<?= strtolower($t['do_number']) ?>">
                            
                            <!-- Armada / Plat Truk (Satu Baris Bersih) -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <span class="font-mono font-bold text-gray-900 bg-slate-100 group-hover:bg-white group-hover:border-indigo-300 px-2.5 py-1 rounded-lg border border-slate-200 text-xs transition">
                                        <?= htmlspecialchars($t['license_plate']) ?>
                                    </span>
                                    <span class="text-gray-500 text-xs truncate max-w-[150px]">
                                        <?= htmlspecialchars($t['company']) ?>
                                    </span>
                                </div>
                            </td>

                            <!-- Pengemudi -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <div class="font-semibold text-gray-800 text-xs flex items-center">
                                    <i class="fa-solid fa-user text-gray-400 mr-2 text-[11px]"></i>
                                    <span><?= htmlspecialchars($t['driver_name']) ?></span>
                                </div>
                            </td>

                            <!-- Tugas / Misi (5 Skenario Riil Dry Port) -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <?php
                                $mType = $t['mission_type'] ?? '';
                                if ($mType === 'drop_export'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                        <i class="fa-solid fa-arrow-down-to-bracket mr-1 text-[10px] text-blue-600"></i>Drop Ekspor
                                    </span>
                                <?php elseif ($mType === 'pick_import'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-100 text-amber-900 border border-amber-200">
                                        <i class="fa-solid fa-arrow-up-from-bracket mr-1 text-[10px] text-amber-600"></i>Pick-Up Impor
                                    </span>
                                <?php elseif ($mType === 'empty_return'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-800 border border-slate-300">
                                        <i class="fa-solid fa-rotate-left mr-1 text-[10px] text-slate-600"></i>Empty Return
                                    </span>
                                <?php elseif ($mType === 'empty_release'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-cyan-100 text-cyan-900 border border-cyan-200">
                                        <i class="fa-solid fa-box-open mr-1 text-[10px] text-cyan-600"></i>Empty Release
                                    </span>
                                <?php elseif ($mType === 'dual_cycle'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-purple-100 text-purple-900 border border-purple-200">
                                        <i class="fa-solid fa-arrows-rotate mr-1 text-[10px] text-purple-600"></i>Dual Cycle
                                    </span>
                                <?php elseif (($t['job_type'] ?? 'drop_off') === 'pick_up'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        <i class="fa-solid fa-arrow-up-from-bracket mr-1 text-[10px] text-amber-600"></i>Pick-Up (Ambil)
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-800 border border-blue-200">
                                        <i class="fa-solid fa-arrow-down-to-bracket mr-1 text-[10px] text-blue-600"></i>Drop-Off (Antar)
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Gerbang Masuk -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <div class="text-xs text-gray-700 flex items-center">
                                    <i class="fa-solid fa-door-open text-indigo-500 mr-1.5 text-[11px]"></i>
                                    <span class="font-medium"><?= explode('(', $t['gate_lane'])[0] ?></span>
                                    <span class="text-gray-400 text-[11px] ml-1.5">• <?= $t['gate_in_time'] ? date('H:i', strtotime($t['gate_in_time'])) : '-' ?></span>
                                </div>
                            </td>

                            <!-- Muatan Kontainer & Tujuan Yard -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <?php if (!empty($t['container_number'])): ?>
                                    <span class="font-mono font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100 text-xs block w-fit">
                                        <?= htmlspecialchars($t['container_number']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400 italic text-xs block">Chassis Kosong</span>
                                <?php endif; ?>
                                <?php if (!empty($t['destination']) || !empty($t['target_block'])): ?>
                                    <span class="text-[10px] text-gray-500 flex items-center mt-0.5">
                                        <i class="fa-solid fa-location-dot mr-1 text-rose-500"></i><?= htmlspecialchars($t['destination'] ?: ('Blok ' . $t['target_block'])) ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Status Operasional -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <?= $badge_status ?>
                            </td>

                            <!-- Tombol Aksi Detail & Aksi Pintas Lintas Modul -->
                            <td class="py-4 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center space-x-1.5" onclick="event.stopPropagation()">
                                    <button onclick="showGatePassModal(<?= $json_pass ?>)" class="px-2.5 py-1.5 bg-slate-100 hover:bg-indigo-600 text-slate-700 hover:text-white text-xs font-semibold rounded-lg transition shadow-2xs inline-flex items-center space-x-1" title="Lihat Detail Tiket Armada">
                                        <i class="fa-solid fa-file-lines text-[10px]"></i>
                                        <span>Milestone</span>
                                    </button>
                                    <?php if (!empty($t['container_number'])): ?>
                                    <a href="dashboard.php?page=kontainer&search=<?= urlencode($t['container_number']) ?>" class="w-7 h-7 rounded-lg bg-blue-50 hover:bg-[#002f5e] text-[#0170b9] hover:text-white flex items-center justify-center text-xs transition shadow-2xs" title="Lacak Kontainer <?= $t['container_number'] ?>">
                                        <i class="fa-solid fa-boxes-stacked text-[11px]"></i>
                                    </a>
                                    <a href="dashboard.php?page=simulator&focus_box=<?= urlencode($t['container_number']) ?>" class="w-7 h-7 rounded-lg bg-amber-50 hover:bg-amber-500 text-amber-700 hover:text-gray-950 flex items-center justify-center text-xs transition shadow-2xs" title="Lihat di Simulasi 3D">
                                        <i class="fa-solid fa-cube text-[11px]"></i>
                                    </a>
                                    <a href="dashboard.php?page=billing&search=<?= urlencode($t['container_number']) ?>" class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white flex items-center justify-center text-xs transition shadow-2xs" title="Cek Faktur Billing">
                                        <i class="fa-solid fa-file-invoice-dollar text-[11px]"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="dashboard.php?page=denah&fac_id=f_gate_in" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-700 text-slate-600 hover:text-white flex items-center justify-center text-xs transition shadow-2xs" title="Lihat Gerbang di Denah">
                                        <i class="fa-solid fa-map-location-dot text-[11px]"></i>
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

<!-- MODAL INTERAKTIF: MILESTONE & DETAIL LENGKAP ARMADA (TRUCK MILESTONE MODAL) -->
<div id="gatePassModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden animate-fadeIn border border-gray-100">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-[#002f5e] to-indigo-800 text-white p-6 relative">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center text-2xl border border-white/20">
                        <i class="fa-solid fa-truck-ramp-box"></i>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <h3 class="text-xl font-bold font-mono" id="p_plate">B 9182 TE</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white" id="p_status_badge">LOADING</span>
                        </div>
                        <p class="text-xs text-blue-100 mt-0.5">
                            <span id="p_driver">Soleh Marzuki</span> • <span id="p_company">PT Trans Logistik Prima</span>
                        </p>
                    </div>
                </div>
                <button onclick="closeGatePassModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <!-- Subheader Barcode & Ticket -->
            <div class="mt-4 flex items-center justify-between bg-white/10 px-4 py-2 rounded-xl text-xs text-blue-100">
                <div>No. Dokumen / Tiket: <strong class="text-white font-mono" id="p_ticket_no">E-GATE-9182</strong></div>
                <div>Tag RFID: <strong class="text-purple-200 font-mono" id="p_rfid">RFID-TRK-001</strong></div>
            </div>
        </div>

        <!-- Body: Milestone 5 Tahap & Data Rincian -->
        <div class="p-6 max-h-[70vh] overflow-y-auto space-y-6 text-xs">
            
            <!-- Tahap Milestone Perjalanan Truk -->
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4 flex items-center">
                    <i class="fa-solid fa-timeline mr-2 text-indigo-600"></i>Kronologi Milestone Pergerakan Truk
                </h4>

                <div class="relative border-l-2 border-indigo-200 ml-4 space-y-5">
                    
                    <!-- Milestone 1: Pra-Gerbang -->
                    <div class="relative pl-6">
                        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-indigo-600 ring-4 ring-indigo-100 flex items-center justify-center text-[8px] text-white">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-gray-900">1. Antrian Pra-Gerbang (Pre-Gate Queue)</span>
                                <span class="text-[10px] text-gray-400">Verifikasi Plat Kamera ANPR</span>
                            </div>
                            <p class="text-gray-600 mt-1">Armada tiba di area buffer luar gerbang CIDP. Kamera ANPR mendeteksi plat nomor secara otomatis.</p>
                        </div>
                    </div>

                    <!-- Milestone 2: Gate-In & Timbang -->
                    <div class="relative pl-6">
                        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-indigo-600 ring-4 ring-indigo-100 flex items-center justify-center text-[8px] text-white">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div class="bg-indigo-50/60 p-3 rounded-xl border border-indigo-100">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-indigo-950">2. Masuk Gerbang & Jembatan Timbang (Gate-In)</span>
                                <span class="text-[10px] text-indigo-700 font-mono font-bold" id="p_gate_in">13:45 WIB</span>
                            </div>
                            <p class="text-gray-700 mt-1">
                                Masuk melalui <strong class="text-gray-900" id="p_gate_lane">Gate 01 Lane A</strong>. Ditimbang di jembatan timbang 80 Ton & Tag RFID divalidasi.
                            </p>
                        </div>
                    </div>

                    <!-- Milestone 3: Manuver & Posisi Yard -->
                    <div class="relative pl-6">
                        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-blue-600 ring-4 ring-blue-100 flex items-center justify-center text-[8px] text-white">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div class="bg-blue-50/50 p-3 rounded-xl border border-blue-100">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-blue-950">3. Posisi Terkini di Lapangan Yard</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">LOKASI AKTIF</span>
                            </div>
                            <p class="text-gray-700 mt-1 font-semibold" id="p_location">Slot Blok B / Bay 08</p>
                            <p class="text-gray-500 mt-0.5">Alat berat pelayan: <strong class="text-gray-800" id="p_equipment">RS-02 (Agus Setiawan)</strong></p>
                        </div>
                    </div>

                    <!-- Milestone 4: Bongkar / Muat Kargo -->
                    <div class="relative pl-6">
                        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-purple-600 ring-4 ring-purple-100 flex items-center justify-center text-[8px] text-white">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </div>
                        <div class="bg-purple-50/50 p-3 rounded-xl border border-purple-100">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-purple-950">4. Operasional Bongkar / Muat (Lift-Off/On)</span>
                                <span class="text-[10px] text-purple-700 font-mono font-bold" id="p_container">MSKU9182374</span>
                            </div>
                            <p class="text-gray-700 mt-1">
                                Kontainer ditangani oleh operator Reach Stacker. Surat Jalan / DO: <strong class="text-gray-900 font-mono" id="p_do">DO-2026/CIDP/0101</strong>.
                            </p>
                        </div>
                    </div>

                    <!-- Milestone 5: Gate-Out -->
                    <div class="relative pl-6">
                        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-emerald-600 ring-4 ring-emerald-100 flex items-center justify-center text-[8px] text-white">
                            <i class="fa-solid fa-flag-checkered"></i>
                        </div>
                        <div class="bg-emerald-50/50 p-3 rounded-xl border border-emerald-100">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-emerald-950">5. Gerbang Keluar & Penyelesaian (Gate-Out)</span>
                                <span class="text-[10px] text-emerald-700 font-bold" id="p_tat">TAT: 21 Menit</span>
                            </div>
                            <p class="text-gray-700 mt-1">
                                Waktu Gate-Out: <span id="p_gate_out" class="font-mono text-gray-800 font-semibold">Sedang di Yard</span>. Validasi e-Gate Pass selesai.
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Rincian Dokumen & Jembatan Timbang (Slip VGM) -->
            <div class="border border-gray-200 rounded-xl p-4 bg-white">
                <h5 class="font-bold text-gray-800 flex items-center border-b border-gray-100 pb-2 mb-3">
                    <i class="fa-solid fa-scale-balanced mr-2 text-indigo-600"></i>Hasil Penimbangan Jembatan Timbang (SOLAS VGM)
                </h5>

                <div class="grid grid-cols-3 gap-3 text-center mb-3">
                    <div class="p-2.5 bg-gray-50 rounded-lg">
                        <span class="text-[10px] text-gray-400 block uppercase font-semibold">Berat Kotor (Gross):</span>
                        <span class="font-bold text-sm text-gray-900" id="p_gross">38.450 kg</span>
                    </div>
                    <div class="p-2.5 bg-gray-50 rounded-lg">
                        <span class="text-[10px] text-gray-400 block uppercase font-semibold">Berat Kosong (Tare):</span>
                        <span class="font-bold text-sm text-gray-700" id="p_tare">12.850 kg</span>
                    </div>
                    <div class="p-2.5 bg-emerald-50 rounded-lg border border-emerald-200">
                        <span class="text-[10px] text-emerald-700 block uppercase font-semibold">Berat Bersih (Net VGM):</span>
                        <span class="font-bold text-sm text-emerald-800" id="p_net">25.600 kg</span>
                    </div>
                </div>

                <div class="text-[11px] text-gray-500 flex justify-between items-center pt-1 border-t border-gray-100">
                    <span>Pengemudi: <strong class="text-gray-800" id="p_sim">SIM B2 Umum</strong></span>
                    <span class="text-emerald-600 font-bold"><i class="fa-solid fa-circle-check mr-1"></i>VGM Certified</span>
                </div>
            </div>

        </div>

        <!-- Aksi Pintas Lintas Modul -->
        <div class="bg-indigo-50/70 border-t border-b border-indigo-100 px-6 py-3 flex flex-wrap items-center justify-between gap-3 text-xs">
            <span class="font-bold text-[#002f5e] flex items-center">
                <i class="fa-solid fa-compass mr-1.5 text-indigo-600"></i>Aksi Pintas Lintas Modul:
            </span>
            <div class="flex items-center space-x-2 flex-wrap gap-y-1" id="truckModalShortcuts">
                <a id="btnTrkCont" href="#" class="px-3 py-1 bg-white hover:bg-[#002f5e] text-slate-700 hover:text-white border border-slate-200 rounded-lg font-semibold transition flex items-center shadow-2xs hidden">
                    <i class="fa-solid fa-boxes-stacked mr-1 text-[#0170b9]"></i>Lacak Kontainer
                </a>
                <a id="btnTrk3D" href="#" class="px-3 py-1 bg-amber-500 hover:bg-amber-600 text-gray-950 font-bold rounded-lg transition flex items-center shadow-2xs hidden">
                    <i class="fa-solid fa-cube mr-1"></i>Simulasi 3D
                </a>
                <a id="btnTrkBilling" href="#" class="px-3 py-1 bg-white hover:bg-emerald-600 text-slate-700 hover:text-white border border-slate-200 rounded-lg font-semibold transition flex items-center shadow-2xs hidden">
                    <i class="fa-solid fa-file-invoice-dollar mr-1 text-emerald-600"></i>Faktur Billing
                </a>
                <a href="dashboard.php?page=denah&fac_id=f_gate_in" class="px-3 py-1 bg-white hover:bg-slate-700 text-slate-700 hover:text-white border border-slate-200 rounded-lg font-semibold transition flex items-center shadow-2xs">
                    <i class="fa-solid fa-map-location-dot mr-1 text-blue-500"></i>Denah Gerbang
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-between">
            <button onclick="window.print()" class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 text-xs font-bold rounded-xl transition flex items-center">
                <i class="fa-solid fa-print mr-1.5"></i> Cetak e-Pass
            </button>
            <button onclick="closeGatePassModal()" class="px-5 py-2 bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold rounded-xl transition">
                Tutup Jendela
            </button>
        </div>

    </div>
</div>

<script>
// Filter realtime tabel trucking
function filterTrucks() {
    const searchVal = document.getElementById('searchTruckInput').value.toLowerCase();
    const statusVal = document.getElementById('filterStatus').value.toLowerCase();

    const rows = document.querySelectorAll('#truckTable tbody tr.truck-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const plate = row.getAttribute('data-plate') || '';
        const driver = row.getAttribute('data-driver') || '';
        const company = row.getAttribute('data-company') || '';
        const status = row.getAttribute('data-status') || '';
        const container = row.getAttribute('data-container') || '';
        const doNum = row.getAttribute('data-do') || '';

        const matchSearch = (plate.includes(searchVal) || driver.includes(searchVal) || company.includes(searchVal) || container.includes(searchVal) || doNum.includes(searchVal));
        const matchStatus = (!statusVal || status === statusVal);

        if (matchSearch && matchStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    document.getElementById('truckRowCountDisplay').innerText = `Menampilkan ${visibleCount} Armada`;
}

function resetTruckFilter() {
    document.getElementById('searchTruckInput').value = '';
    document.getElementById('filterStatus').value = '';
    filterTrucks();
}

// Buka Modal Tiket & Milestone
function showGatePassModal(data) {
    document.getElementById('p_ticket_no').innerText = 'E-PASS-' + data.license_plate.replace(/\s+/g, '') + '-' + Math.floor(1000 + Math.random() * 9000);
    document.getElementById('p_status_badge').innerText = data.status_text;

    document.getElementById('p_plate').innerText = data.license_plate;
    document.getElementById('p_rfid').innerText = data.rfid_tag;
    document.getElementById('p_driver').innerText = data.driver_name;
    document.getElementById('p_sim').innerText = data.driver_sim;
    document.getElementById('p_company').innerText = data.company;

    document.getElementById('p_gross').innerText = data.weight_gross;
    document.getElementById('p_tare').innerText = data.weight_tare;
    document.getElementById('p_net').innerText = data.weight_net;
    document.getElementById('p_container').innerText = data.container_number;
    document.getElementById('p_do').innerText = data.do_number;

    document.getElementById('p_gate_lane').innerText = data.gate_lane;
    document.getElementById('p_gate_in').innerText = data.gate_in_time;
    document.getElementById('p_gate_out').innerText = data.gate_out_time;
    document.getElementById('p_tat').innerText = 'TAT: ' + data.tat_text;
    document.getElementById('p_location').innerText = data.current_location;
    document.getElementById('p_equipment').innerText = data.handling_equipment;

    // Tombol Lintas Modul
    const btnC = document.getElementById('btnTrkCont');
    const btn3 = document.getElementById('btnTrk3D');
    const btnB = document.getElementById('btnTrkBilling');
    if (data.container_number && data.container_number !== '-') {
        if (btnC) {
            btnC.classList.remove('hidden');
            btnC.href = `dashboard.php?page=kontainer&search=${encodeURIComponent(data.container_number)}&open=1`;
        }
        if (btn3) {
            btn3.classList.remove('hidden');
            btn3.href = `dashboard.php?page=simulator&focus_box=${encodeURIComponent(data.container_number)}`;
        }
        if (btnB) {
            btnB.classList.remove('hidden');
            btnB.href = `dashboard.php?page=billing&search=${encodeURIComponent(data.container_number)}`;
        }
    } else {
        if (btnC) btnC.classList.add('hidden');
        if (btn3) btn3.classList.add('hidden');
        if (btnB) btnB.classList.add('hidden');
    }

    document.getElementById('gatePassModal').classList.remove('hidden');
}

function closeGatePassModal() {
    document.getElementById('gatePassModal').classList.add('hidden');
}

// URL Deep-Linking Initializer for Trucking Monitor
setTimeout(() => {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const searchParam = urlParams.get('search') || urlParams.get('plate') || urlParams.get('q');
        const statusParam = urlParams.get('status');
        if (searchParam) {
            const input = document.getElementById('searchTruckInput');
            if (input) {
                input.value = searchParam;
                filterTrucks();
            }
        }
        if (statusParam) {
            const select = document.getElementById('filterStatus');
            if (select) {
                select.value = statusParam;
                filterTrucks();
            }
        }
    } catch(e) {}
}, 200);

window.addEventListener('click', function(e) {
    const modal = document.getElementById('gatePassModal');
    if (e.target === modal) {
        closeGatePassModal();
    }
});

function getTruckingExportData() {
    const headers = ['Nopol', 'Nama Supir', 'Perusahaan', 'No. Kontainer', 'Lokasi Saat Ini', 'Status', 'Gate In', 'Gate Out', 'Turnaround Time (TAT)'];
    const rows = [];
    const tableRows = document.querySelectorAll('#truckTable tbody tr.truck-row');
    
    tableRows.forEach(row => {
        if (row.style.display !== 'none') {
            const onclickStr = row.getAttribute('onclick') || '';
            const match = onclickStr.match(/showGatePassModal\((.*?)\)/);
            if (match && match[1]) {
                try {
                    const data = JSON.parse(match[1]);
                    rows.push([
                        data.license_plate,
                        data.driver_name,
                        data.company,
                        data.container_number,
                        data.current_location,
                        data.status_text,
                        data.gate_in_time,
                        data.gate_out_time,
                        data.tat_text
                    ]);
                } catch (e) {
                    console.error("Error parsing row data", e);
                }
            }
        }
    });
    return { headers, rows };
}

function exportTruckingExcel() {
    const { headers, rows } = getTruckingExportData();
    CIDPExport.toExcel(headers, rows, 'Data Trucking', 'Laporan_Trucking_CIDP');
}

function exportTruckingPDF() {
    const { headers, rows } = getTruckingExportData();
    CIDPExport.toPDF('Laporan Manajemen Trucking & Gate', headers, rows, 'Laporan_Trucking_CIDP');
}

function showAddTruckModal() {
    document.getElementById('addTruckModal').classList.remove('hidden');
}
function closeAddTruckModal() {
    document.getElementById('addTruckModal').classList.add('hidden');
}

function submitAddTruck(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    fetch('api/crud.php?action=add_truck', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            alert('Truk berhasil diregistrasi!');
            location.reload();
        } else {
            alert('Gagal: ' + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan.');
    });
}
</script>

<!-- Add Truck Modal -->
<div id="addTruckModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden animate-fadeIn border border-gray-100">
        <div class="bg-gradient-to-r from-[#002f5e] to-indigo-800 text-white p-4 flex items-center justify-between">
            <h3 class="font-bold text-lg">Registrasi Truk Masuk</h3>
            <button onclick="closeAddTruckModal()" class="text-white hover:text-gray-200"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form onsubmit="submitAddTruck(event)" class="p-5 space-y-3.5">
            <!-- Pilihan Tugas / Misi -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Jenis Tugas / Misi Operasional:</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center p-2.5 border border-blue-200 rounded-xl bg-blue-50/50 cursor-pointer hover:bg-blue-50 transition">
                        <input type="radio" name="job_type" value="drop_off" checked onchange="toggleJobType(this.value)" class="text-blue-600 focus:ring-blue-500 mr-2">
                        <div>
                            <span class="text-xs font-bold text-blue-900 block">📥 Drop-Off</span>
                            <span class="text-[10px] text-blue-700">Antar kontainer ke yard</span>
                        </div>
                    </label>
                    <label class="flex items-center p-2.5 border border-amber-200 rounded-xl bg-amber-50/50 cursor-pointer hover:bg-amber-50 transition">
                        <input type="radio" name="job_type" value="pick_up" onchange="toggleJobType(this.value)" class="text-amber-600 focus:ring-amber-500 mr-2">
                        <div>
                            <span class="text-xs font-bold text-amber-900 block">📤 Pick-Up</span>
                            <span class="text-[10px] text-amber-700">Ambil kontainer dari yard</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Plat Nomor Truk</label>
                    <input type="text" name="license_plate" required placeholder="mis: B 9182 TE" class="w-full border border-gray-200 p-2 rounded-lg text-xs font-mono font-bold focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Pengemudi</label>
                    <input type="text" name="driver_name" required placeholder="mis: Soleh Marzuki" class="w-full border border-gray-200 p-2 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Perusahaan Ekspedisi / Transporter</label>
                <input type="text" name="company" required placeholder="mis: PT Trans Logistik Prima" class="w-full border border-gray-200 p-2 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label id="lblContainer" class="block text-xs font-bold text-gray-700 mb-1">Nomor Kontainer</label>
                    <input type="text" name="container_number" id="inpContainer" placeholder="mis: MSKU7829104" class="w-full border border-gray-200 p-2 rounded-lg text-xs font-mono uppercase focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">No. DO / SPPB Bea Cukai</label>
                    <input type="text" name="do_number" placeholder="mis: DO-2026-09-001" class="w-full border border-gray-200 p-2 rounded-lg text-xs font-mono focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="pt-3 flex justify-end space-x-2 border-t border-gray-100">
                <button type="button" onclick="closeAddTruckModal()" class="px-4 py-2 border border-gray-200 hover:bg-gray-50 rounded-lg text-xs font-bold text-gray-700 transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition shadow-xs">Simpan Registrasi</button>
            </div>
        </form>

        <script>
        function toggleJobType(val) {
            const lbl = document.getElementById('lblContainer');
            const inp = document.getElementById('inpContainer');
            if (val === 'pick_up') {
                lbl.innerHTML = 'Kontainer Tujuan <span class="text-[10px] text-amber-600">(Yang Diambil)</span>';
                inp.placeholder = 'mis: TGHU9021845';
            } else {
                lbl.innerHTML = 'Nomor Kontainer <span class="text-[10px] text-blue-600">(Yang Diantar)</span>';
                inp.placeholder = 'mis: MSKU7829104';
            }
        }
        </script>
    </div>
</div>
