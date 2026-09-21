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
    $trucks = [
        [
            'id' => 1,
            'license_plate' => 'B 9182 TE',
            'rfid_tag' => 'RFID-TRK-001',
            'driver_name' => 'Soleh Marzuki',
            'company' => 'PT Trans Logistik Prima',
            'container_number' => 'MSKU9182374',
            'status' => 'loading',
            'gate_in_time' => '2026-09-21 13:45:00',
            'gate_out_time' => null
        ],
        [
            'id' => 2,
            'license_plate' => 'B 1234 XY',
            'rfid_tag' => 'RFID-TRK-002',
            'driver_name' => 'Hendra Gunawan',
            'company' => 'PT Cepat Aman Sejahtera',
            'container_number' => 'TCLU1234567',
            'status' => 'in_yard',
            'gate_in_time' => '2026-09-21 14:05:00',
            'gate_out_time' => null
        ],
        [
            'id' => 3,
            'license_plate' => 'D 5555 ZZ',
            'rfid_tag' => 'RFID-TRK-003',
            'driver_name' => 'Maman Sulaeman',
            'company' => 'CV Jaya Abadi Transport',
            'container_number' => null,
            'status' => 'at_gate',
            'gate_in_time' => '2026-09-21 14:22:00',
            'gate_out_time' => null
        ],
        [
            'id' => 4,
            'license_plate' => 'B 7777 AB',
            'rfid_tag' => 'RFID-TRK-004',
            'driver_name' => 'Tono Hartono',
            'company' => 'PT Puninar Logistics',
            'container_number' => 'CSQU7777777',
            'status' => 'queuing',
            'gate_in_time' => null,
            'gate_out_time' => null
        ],
        [
            'id' => 5,
            'license_plate' => 'L 8888 KL',
            'rfid_tag' => 'RFID-TRK-005',
            'driver_name' => 'Yanto Prasetyo',
            'company' => 'PT Siba Surya',
            'container_number' => 'TEMU8888888',
            'status' => 'gate_out',
            'gate_in_time' => '2026-09-21 12:15:00',
            'gate_out_time' => '2026-09-21 12:38:00'
        ]
    ];
}

// Tambah armada realistis untuk memperkaya traffic demo jika kurang dari 7 unit
if (count($trucks) < 8) {
    $extra_trucks = [
        [
            'id' => 6,
            'license_plate' => 'B 9481 FZ',
            'rfid_tag' => 'RFID-TRK-006',
            'driver_name' => 'Bambang Irawan',
            'company' => 'PT Samudera Freight Nusantara',
            'container_number' => 'FCIU5555555',
            'status' => 'loading',
            'gate_in_time' => '2026-09-21 13:58:00',
            'gate_out_time' => null
        ],
        [
            'id' => 7,
            'license_plate' => 'B 9021 UJ',
            'rfid_tag' => 'RFID-TRK-007',
            'driver_name' => 'Dedi Iskandar',
            'company' => 'PT Dunex Express Indonesia',
            'container_number' => null,
            'status' => 'in_yard',
            'gate_in_time' => '2026-09-21 14:10:00',
            'gate_out_time' => null
        ],
        [
            'id' => 8,
            'license_plate' => 'B 8820 KXA',
            'rfid_tag' => 'RFID-TRK-008',
            'driver_name' => 'Wahyudi Santoso',
            'company' => 'PT Pos Logistik Indonesia',
            'container_number' => 'TEMU4819203',
            'status' => 'gate_out',
            'gate_in_time' => '2026-09-21 11:30:00',
            'gate_out_time' => '2026-09-21 11:51:00'
        ]
    ];
    $trucks = array_merge($trucks, $extra_trucks);
}

// Perkaya data dengan Gate Lane, DO Number, Timbangan Jembatan Timbang (Gross, Tare, Net), Posisi Terkini di Yard, dan Alat Berat
$lanes = [
    'Gate 01 (Lane A - Inbound Heavy)',
    'Gate 02 (Lane B - Inbound Fast Track)',
    'Gate 01 (Lane C - Inbound Reefer Priority)'
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

    // Data Surat Jalan & Tiket
    $t['do_number'] = 'DO-2026/CIDP/' . str_pad($idx + 101, 4, '0', STR_PAD_LEFT);
    $t['driver_sim'] = 'SIM B2 Umum #' . (982100 + $idx);
    $t['gate_lane'] = $lanes[$idx % count($lanes)];
    $t['current_location'] = $yard_locations[$t['status']] ?? 'Area Lapangan';
    $t['handling_equipment'] = ($t['container_number']) ? $assigned_rs[$idx % count($assigned_rs)] : 'Menunggu Penugasan Alat';

    // Data Timbangan (VGM)
    $has_cont = !empty($t['container_number']);
    $tare = 12500 + ($idx * 150); // Berat kosong truk rata-rata 12.5 ton
    $net = $has_cont ? (14000 + ($idx * 2100)) : 0; // Berat kargo
    $gross = $tare + $net;

    $t['weight_tare'] = $tare;
    $t['weight_net'] = $net;
    $t['weight_gross'] = $gross;

    // Hitung Turnaround Time (TAT)
    if (!empty($t['gate_in_time']) && !empty($t['gate_out_time'])) {
        $mins = round((strtotime($t['gate_out_time']) - strtotime($t['gate_in_time'])) / 60);
        $t['tat_text'] = "{$mins} Menit (Selesai)";
        $t['is_active'] = false;
    } elseif (!empty($t['gate_in_time'])) {
        $mins = round((time() - strtotime($t['gate_in_time'])) / 60);
        $t['tat_text'] = "{$mins} Menit (Berjalan)";
        $t['is_active'] = true;
    } else {
        $t['tat_text'] = "Menunggu Gate-In";
        $t['is_active'] = false;
    }
}
unset($t);

$total_trucks = count($trucks);
?>

<div class="space-y-6">

    <!-- Header & Subtitle -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <div class="flex items-center space-x-3 mb-1">
                <span class="px-3 py-1 text-xs font-bold uppercase rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                    <i class="fa-solid fa-truck-front mr-1.5"></i> Trucking & Gate Traffic Module
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Gerbang & Timbangan Aktif
                </span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Manajemen Trucking & Arus Gerbang (Gate Traffic)</h2>
            <p class="text-gray-500 text-sm mt-1">
                Pelacakan menyeluruh pergerakan armada pengangkut: gerbang masuk yang dilalui, data muatan (DO/Surat Jalan), verifikasi jembatan timbang VGM, posisi terkini di lapangan, hingga alur gate-out.
            </p>
        </div>
        <div class="flex items-center space-x-3 flex-shrink-0">
            <button onclick="window.print()" class="px-4 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-xl shadow-xs transition flex items-center">
                <i class="fa-solid fa-print mr-2 text-gray-500"></i> Cetak Rekap
            </button>
            <a href="dashboard.php?page=alat" class="px-4 py-2.5 bg-[#004b87] hover:bg-[#002f5e] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center">
                <i class="fa-solid fa-dolly mr-2"></i> Monitor Alat Berat <i class="fa-solid fa-arrow-right ml-1.5"></i>
            </a>
        </div>
    </div>

    <!-- 4 KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Truk Aktif di Yard -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-400 tracking-wider">Armada di Dalam Terminal</p>
                <h3 class="text-2xl font-bold text-indigo-600 mt-1"><?= $total_in_yard ?> <span class="text-xs font-normal text-gray-400">Armada</span></h3>
                <div class="mt-2 text-xs text-gray-500 flex items-center font-medium">
                    <i class="fa-solid fa-arrows-split-up-and-left mr-1 text-indigo-500"></i> Manuver & Bongkar Muat
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-truck-moving"></i>
            </div>
        </div>

        <!-- Antrian Gerbang -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-400 tracking-wider">Antrian & Jembatan Timbang</p>
                <h3 class="text-2xl font-bold text-amber-600 mt-1"><?= $total_queuing + $total_at_gate ?> <span class="text-xs font-normal text-gray-400">Truk</span></h3>
                <div class="mt-2 text-xs text-amber-600 flex items-center font-medium">
                    <span><?= $total_at_gate ?> Timbang / <?= $total_queuing ?> Antri Luar</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
        </div>

        <!-- Rata-rata Turnaround Time (TAT) -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-400 tracking-wider">Rata-Rata Turnaround (TAT)</p>
                <h3 class="text-2xl font-bold text-emerald-600 mt-1">21.5 <span class="text-xs font-normal text-gray-400">Menit</span></h3>
                <div class="mt-2 text-xs text-emerald-600 flex items-center font-medium">
                    <i class="fa-solid fa-circle-check mr-1"></i> Sangat Cepat (SOP < 30m)
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-stopwatch-20"></i>
            </div>
        </div>

        <!-- Selesai Gate Out -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-400 tracking-wider">Selesai Keluar (Gate-Out)</p>
                <h3 class="text-2xl font-bold text-blue-600 mt-1"><?= $total_gate_out ?> <span class="text-xs font-normal text-gray-400">Siklus Sukses</span></h3>
                <div class="mt-2 text-xs text-blue-600 flex items-center font-medium">
                    <i class="fa-solid fa-file-circle-check mr-1"></i> e-Gate Pass Ditutup
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-door-open"></i>
            </div>
        </div>

    </div>

    <!-- Live Gate Lane Status Bar -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-3 border-b border-gray-100 pb-2">
            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 flex items-center">
                <i class="fa-solid fa-traffic-light mr-2 text-[#0170b9]"></i>Status Kanal Gerbang (Gate Lane Monitoring)
            </h4>
            <span class="text-[11px] text-gray-400">Sistem Kamera ANPR + OCR Otomatis Aktif</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            
            <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-200 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase text-emerald-800 block">Gate In - Lane 01 (Heavy)</span>
                    <span class="text-xs font-bold text-gray-800">Lancar • 1 Truk Timbang</span>
                </div>
                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-ping"></span>
            </div>

            <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-200 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase text-emerald-800 block">Gate In - Lane 02 (Fast Track)</span>
                    <span class="text-xs font-bold text-gray-800">Lancar • Siaga</span>
                </div>
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
            </div>

            <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-200 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase text-blue-800 block">Gate Out - Lane 01</span>
                    <span class="text-xs font-bold text-gray-800">Kliring e-Gate Pass Cepat</span>
                </div>
                <span class="w-3 h-3 rounded-full bg-blue-500"></span>
            </div>

            <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-200 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase text-blue-800 block">Gate Out - Lane 02</span>
                    <span class="text-xs font-bold text-gray-800">Kamera Inspeksi Siaga</span>
                </div>
                <span class="w-3 h-3 rounded-full bg-blue-500"></span>
            </div>

        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="relative w-full md:w-80">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </span>
            <input type="text" id="searchTruckInput" onkeyup="filterTrucks()" placeholder="Cari Plat Nomor, Pengemudi, DO..." 
                   class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#0170b9] focus:bg-white transition">
        </div>

        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
            <!-- Filter Status -->
            <select id="filterStatus" onchange="filterTrucks()" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#0170b9]">
                <option value="">Semua Status Armada</option>
                <option value="in_yard">Manuver di Lapangan</option>
                <option value="loading">Proses Bongkar/Muat</option>
                <option value="at_gate">Di Jembatan Timbang</option>
                <option value="queuing">Antrian Gerbang Luar</option>
                <option value="gate_out">Selesai Gate-Out</option>
            </select>

            <button onclick="resetTruckFilter()" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold rounded-xl transition">
                <i class="fa-solid fa-rotate-left mr-1"></i> Reset
            </button>
        </div>
    </div>

    <!-- Main Truck Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-bold text-gray-900 text-base flex items-center">
                    <i class="fa-solid fa-list-check mr-2 text-indigo-600"></i>Arus Keluar-Masuk Armada & Rincian Muatan
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Memantau nomor gerbang masuk, data timbangan kotor/bersih, posisi terkini di lapangan, dan alat berat pelayan.</p>
            </div>
            <span class="text-xs font-semibold text-gray-500 bg-white px-3 py-1 rounded-lg border border-gray-200" id="truckRowCountDisplay">
                Menampilkan <?= count($trucks) ?> Armada
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="truckTable">
                <thead>
                    <tr class="bg-gray-50/80 text-gray-600 text-xs uppercase tracking-wider border-b border-gray-200">
                        <th class="py-3.5 px-4 font-semibold">Plat Nomor & RFID</th>
                        <th class="py-3.5 px-4 font-semibold">Pengemudi & Transporter</th>
                        <th class="py-3.5 px-4 font-semibold">Gerbang Masuk (Gate-In)</th>
                        <th class="py-3.5 px-4 font-semibold">Data Masuk / Kargo</th>
                        <th class="py-3.5 px-4 font-semibold">Data Timbangan (VGM)</th>
                        <th class="py-3.5 px-4 font-semibold">Posisi & Status Terkini</th>
                        <th class="py-3.5 px-4 font-semibold">Alat Berat</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Tiket e-Gate</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-gray-100">
                    <?php foreach ($trucks as $t): ?>
                        <?php
                        // Status Badge
                        $badge_status = '';
                        if ($t['status'] === 'loading') {
                            $badge_status = '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200"><span class="w-1.5 h-1.5 rounded-full bg-purple-500 mr-1 animate-pulse"></span>Bongkar/Muat</span>';
                        } elseif ($t['status'] === 'in_yard') {
                            $badge_status = '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200"><span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1 animate-pulse"></span>Di Lapangan</span>';
                        } elseif ($t['status'] === 'at_gate') {
                            $badge_status = '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1 animate-ping"></span>Di Jembatan Timbang</span>';
                        } elseif ($t['status'] === 'queuing') {
                            $badge_status = '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700 border border-gray-300">Antrian Pra-Gate</span>';
                        } elseif ($t['status'] === 'gate_out') {
                            $badge_status = '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fa-solid fa-check mr-1"></i>Gate-Out Selesai</span>';
                        }

                        // Data JSON untuk Modal Tiket Digital
                        $json_pass = htmlspecialchars(json_encode([
                            'license_plate' => $t['license_plate'],
                            'rfid_tag' => $t['rfid_tag'],
                            'driver_name' => $t['driver_name'],
                            'driver_sim' => $t['driver_sim'],
                            'company' => $t['company'],
                            'do_number' => $t['do_number'],
                            'container_number' => $t['container_number'] ?? 'TRUK KOSONG (PICK-UP MUATAN)',
                            'gate_lane' => $t['gate_lane'],
                            'gate_in_time' => $t['gate_in_time'] ?? 'Belum Gate-In',
                            'gate_out_time' => $t['gate_out_time'] ?? 'Sedang Beroperasi di Yard',
                            'current_location' => $t['current_location'],
                            'handling_equipment' => $t['handling_equipment'],
                            'weight_gross' => number_format($t['weight_gross'], 0, ',', '.') . ' kg',
                            'weight_tare' => number_format($t['weight_tare'], 0, ',', '.') . ' kg',
                            'weight_net' => number_format($t['weight_net'], 0, ',', '.') . ' kg',
                            'tat_text' => $t['tat_text'],
                            'status_text' => strtoupper($t['status'])
                        ]), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr class="hover:bg-indigo-50/20 transition truck-row"
                            data-plate="<?= strtolower($t['license_plate']) ?>"
                            data-driver="<?= strtolower($t['driver_name']) ?>"
                            data-company="<?= strtolower($t['company']) ?>"
                            data-status="<?= strtolower($t['status']) ?>"
                            data-do="<?= strtolower($t['do_number']) ?>">
                            
                            <!-- Plat Nomor & RFID -->
                            <td class="py-3.5 px-4">
                                <div class="font-mono font-bold text-gray-900 text-sm flex items-center">
                                    <span class="bg-gray-800 text-white px-2 py-0.5 rounded mr-1.5"><?= htmlspecialchars($t['license_plate']) ?></span>
                                </div>
                                <div class="text-[10px] font-mono text-purple-700 font-bold mt-1 flex items-center">
                                    <i class="fa-solid fa-satellite-dish mr-1 text-[9px] text-purple-500"></i>
                                    <span><?= htmlspecialchars($t['rfid_tag']) ?></span>
                                </div>
                            </td>

                            <!-- Pengemudi & Transporter -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-gray-800 text-xs flex items-center">
                                    <i class="fa-solid fa-user text-gray-400 mr-1.5"></i>
                                    <span><?= htmlspecialchars($t['driver_name']) ?></span>
                                </div>
                                <div class="text-[11px] text-gray-500 mt-0.5">
                                    <?= htmlspecialchars($t['company']) ?>
                                </div>
                                <div class="text-[10px] text-gray-400">
                                    <?= htmlspecialchars($t['driver_sim']) ?>
                                </div>
                            </td>

                            <!-- Gerbang Masuk -->
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-gray-800 text-xs flex items-center">
                                    <i class="fa-solid fa-door-open text-indigo-500 mr-1.5"></i>
                                    <span><?= htmlspecialchars($t['gate_lane']) ?></span>
                                </div>
                                <div class="text-[11px] text-gray-500 mt-1 flex items-center">
                                    <i class="fa-regular fa-clock text-gray-400 mr-1"></i>
                                    <span><?= $t['gate_in_time'] ? date('H:i \W\I\B', strtotime($t['gate_in_time'])) : '-' ?></span>
                                </div>
                                <div class="text-[10px] text-indigo-600 font-medium mt-0.5">
                                    TAT: <?= $t['tat_text'] ?>
                                </div>
                            </td>

                            <!-- Data Masuk / Kargo -->
                            <td class="py-3.5 px-4">
                                <div class="font-mono text-blue-700 font-bold text-xs">
                                    <?= $t['container_number'] ? htmlspecialchars($t['container_number']) : '<span class="text-gray-400 font-normal italic">Truk Kosong (Pick-up)</span>' ?>
                                </div>
                                <div class="text-[11px] text-gray-600 mt-0.5">
                                    No. DO: <span class="font-mono font-semibold"><?= htmlspecialchars($t['do_number']) ?></span>
                                </div>
                            </td>

                            <!-- Timbangan Jembatan -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-gray-900 text-xs">
                                    Kotor: <?= number_format($t['weight_gross'], 0, ',', '.') ?> kg
                                </div>
                                <div class="text-[10px] text-gray-500 mt-0.5">
                                    Tara: <?= number_format($t['weight_tare'], 0, ',', '.') ?> kg | Netto: <strong class="text-emerald-700"><?= number_format($t['weight_net'], 0, ',', '.') ?> kg</strong>
                                </div>
                                <div class="text-[10px] text-emerald-600 font-medium mt-0.5 flex items-center">
                                    <i class="fa-solid fa-circle-check mr-1 text-[9px]"></i>VGM Disetujui
                                </div>
                            </td>

                            <!-- Posisi Terkini -->
                            <td class="py-3.5 px-4">
                                <div class="mb-1">
                                    <?= $badge_status ?>
                                </div>
                                <div class="text-[11px] text-gray-700 font-medium">
                                    <?= htmlspecialchars($t['current_location']) ?>
                                </div>
                            </td>

                            <!-- Alat Berat -->
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-gray-800 text-xs flex items-center">
                                    <i class="fa-solid fa-dolly text-blue-600 mr-1.5"></i>
                                    <span><?= htmlspecialchars($t['handling_equipment']) ?></span>
                                </div>
                            </td>

                            <!-- Aksi Tiket Digital -->
                            <td class="py-3.5 px-4 text-center">
                                <button onclick='showGatePassModal(<?= $json_pass ?>)' 
                                        class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition shadow-xs flex items-center justify-center space-x-1 mx-auto">
                                    <i class="fa-solid fa-ticket"></i>
                                    <span>e-Pass</span>
                                </button>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL INTERAKTIF: TIKET DIGITAL GERBANG & SLIP TIMBANGAN (e-GATE PASS) -->
<div id="gatePassModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-xl w-full overflow-hidden animate-fadeIn border border-gray-100">
        
        <!-- Pass Header -->
        <div class="bg-[#002f5e] text-white p-6 relative">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-xl border border-white/20">
                        <i class="fa-solid fa-ticket"></i>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase tracking-wider font-bold text-blue-200 block">Conclusion Intermodal Dry Port</span>
                        <h3 class="text-lg font-bold">Tiket Digital Gerbang (e-Gate Pass)</h3>
                    </div>
                </div>
                <button onclick="closeGatePassModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>
            
            <div class="mt-4 flex items-center justify-between bg-white/10 p-3 rounded-xl border border-white/10 text-xs">
                <div>
                    <span class="text-blue-200 text-[10px] block">NOMOR DOKUMEN / TIKET:</span>
                    <span class="font-mono font-bold text-sm tracking-wider" id="p_ticket_no">E-GATE-2026-918237</span>
                </div>
                <div class="text-right">
                    <span class="text-blue-200 text-[10px] block">STATUS OPERASIONAL:</span>
                    <span class="font-bold text-emerald-300" id="p_status_badge">LOADING</span>
                </div>
            </div>
        </div>

        <!-- Pass Body: Slip Timbangan & Rincian Armada -->
        <div class="p-6 space-y-5 text-xs">
            
            <!-- Grid Rincian Armada -->
            <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                <div>
                    <span class="text-gray-400 text-[10px] block uppercase font-semibold">Plat Nomor Truk:</span>
                    <span class="font-mono font-bold text-base text-gray-900" id="p_plate">B 9182 TE</span>
                </div>
                <div>
                    <span class="text-gray-400 text-[10px] block uppercase font-semibold">Tag RFID Armada:</span>
                    <span class="font-mono font-bold text-purple-700" id="p_rfid">RFID-TRK-001</span>
                </div>
                <div>
                    <span class="text-gray-400 text-[10px] block uppercase font-semibold">Pengemudi (Driver):</span>
                    <span class="font-bold text-gray-800" id="p_driver">Soleh Marzuki</span>
                    <span class="text-[10px] text-gray-400 block" id="p_sim">SIM B2 Umum #982100</span>
                </div>
                <div>
                    <span class="text-gray-400 text-[10px] block uppercase font-semibold">Transporter:</span>
                    <span class="font-bold text-gray-800" id="p_company">PT Trans Logistik Prima</span>
                </div>
            </div>

            <!-- Rincian Muatan & Timbangan Resmi (Weighbridge Slip) -->
            <div class="border border-gray-200 rounded-xl p-4 bg-white">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2 mb-3">
                    <h5 class="font-bold text-gray-800 flex items-center">
                        <i class="fa-solid fa-scale-balanced mr-2 text-indigo-600"></i>Hasil Penimbangan Jembatan Timbang (VGM)
                    </h5>
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">SOLAS Compliant</span>
                </div>

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

                <div class="text-[11px] text-gray-600 space-y-1">
                    <div>Kontainer: <strong class="text-[#0170b9] font-mono" id="p_container">MSKU9182374</strong></div>
                    <div>Surat Jalan (DO): <span class="font-mono text-gray-800" id="p_do">DO-2026/CIDP/0101</span></div>
                </div>
            </div>

            <!-- Stempel Waktu & Lokasi Yard -->
            <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 space-y-2">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-600">Pintu Gerbang Masuk:</span>
                    <strong class="text-gray-900" id="p_gate_lane">Gate 01 (Lane A - Inbound Heavy)</strong>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-600">Stempel Waktu Gate-In:</span>
                    <strong class="text-gray-900 font-mono" id="p_gate_in">2026-09-21 13:45:00</strong>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-600">Posisi / Blok Penumpukan:</span>
                    <strong class="text-indigo-700" id="p_location">Slot Blok B / Bay 08</strong>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-600">Alat Berat Penanggung Jawab:</span>
                    <strong class="text-gray-900" id="p_equipment">RS-02 (Agus Setiawan)</strong>
                </div>
            </div>

            <!-- Barcode & QR Code Simulasi -->
            <div class="text-center pt-2">
                <div class="inline-block bg-slate-100 px-6 py-2 rounded-lg font-mono text-lg tracking-widest font-bold text-gray-800 border border-slate-300">
                    ||||| | |||| |||||| || | |||| |||
                </div>
                <p class="text-[10px] text-gray-400 mt-1">Scan barcode saat tiba di Gate Out untuk validasi izin keluar otomatis.</p>
            </div>

        </div>

        <!-- Pass Footer -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-between">
            <button onclick="window.print()" class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 text-xs font-bold rounded-xl transition flex items-center">
                <i class="fa-solid fa-print mr-1.5"></i> Cetak Tiket
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
        const doNum = row.getAttribute('data-do') || '';

        const matchSearch = (plate.includes(searchVal) || driver.includes(searchVal) || company.includes(searchVal) || doNum.includes(searchVal));
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

// Buka Modal Tiket Digital Gerbang
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
    document.getElementById('p_location').innerText = data.current_location;
    document.getElementById('p_equipment').innerText = data.handling_equipment;

    document.getElementById('gatePassModal').classList.remove('hidden');
}

function closeGatePassModal() {
    document.getElementById('gatePassModal').classList.add('hidden');
}

window.addEventListener('click', function(e) {
    const modal = document.getElementById('gatePassModal');
    if (e.target === modal) {
        closeGatePassModal();
    }
});
</script>
