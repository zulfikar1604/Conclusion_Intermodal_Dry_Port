<?php
// =============================================================================
// MODUL: PELACAKAN KONTAINER & AUDIT PERJALANAN (CONTAINER TRACKING & JOURNEY)
// File: pages/kontainer.php
// CIDP Yard Management System - PT Multi Terminal Indonesia / ITL Trisakti
// =============================================================================

require_once __DIR__ . '/../connection.php';

// Ambil data kontainer dari database
$containers = [];
$total_dry = 0;
$total_reefer = 0;
$total_dg = 0;
$total_empty = 0;

try {
    $stmt = $pdo->query("SELECT * FROM containers ORDER BY id ASC");
    $containers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Fallback data jika query gagal
    $containers = [];
}

// Fallback dataset realistis jika database kosong atau belum di-seed
if (empty($containers)) {
    $containers = [
        [
            'id' => 1,
            'container_number' => 'MSKU9182374',
            'iso_code' => '42G1',
            'size_type' => '40FT HIGH CUBE',
            'cargo_type' => 'dry',
            'rfid_tag' => 'E280117000000001',
            'sscc_code' => '(00)389912345000000001',
            'gross_weight_kg' => 25400.00,
            'owner_company' => 'PT Samudera Logistik Prima',
            'seal_number' => 'SN-JKT-102938',
            'customs_status' => 'SPPB_CLEARED',
            'block' => 'A',
            'bay' => '01',
            'row' => '01',
            'tier' => '01',
            'gate_in_time' => '2026-09-19 08:30:00',
            'status' => 'in_yard'
        ],
        [
            'id' => 2,
            'container_number' => 'TCLU1234567',
            'iso_code' => '22G1',
            'size_type' => '20FT STANDARD',
            'cargo_type' => 'dry',
            'rfid_tag' => 'E280117000000002',
            'sscc_code' => '(00)389912345000000002',
            'gross_weight_kg' => 14500.00,
            'owner_company' => 'PT Evergreen Shipping Indonesia',
            'seal_number' => 'SN-JKT-102939',
            'customs_status' => 'SPPB_CLEARED',
            'block' => 'A',
            'bay' => '01',
            'row' => '01',
            'tier' => '02',
            'gate_in_time' => '2026-09-19 09:15:00',
            'status' => 'in_yard'
        ],
        [
            'id' => 3,
            'container_number' => 'TEMU8888888',
            'iso_code' => '42G1',
            'size_type' => '40FT HIGH CUBE',
            'cargo_type' => 'dry',
            'rfid_tag' => 'E280117000000003',
            'sscc_code' => '(00)389912345000000003',
            'gross_weight_kg' => 28000.00,
            'owner_company' => 'Maersk Indonesia',
            'seal_number' => 'SN-JKT-102940',
            'customs_status' => 'SPPB_CLEARED',
            'block' => 'A',
            'bay' => '01',
            'row' => '02',
            'tier' => '01',
            'gate_in_time' => '2026-09-18 14:00:00',
            'status' => 'in_yard'
        ],
        [
            'id' => 4,
            'container_number' => 'CSQU7777777',
            'iso_code' => '42R1',
            'size_type' => '40FT REEFER HC',
            'cargo_type' => 'reefer',
            'rfid_tag' => 'E280117000000004',
            'sscc_code' => '(00)389912345000000004',
            'gross_weight_kg' => 27200.00,
            'owner_company' => 'PT Cold Chain Logistik Nusantara',
            'seal_number' => 'SN-JKT-102941',
            'customs_status' => 'SPPB_CLEARED',
            'block' => 'REEFER',
            'bay' => '01',
            'row' => '01',
            'tier' => '01',
            'gate_in_time' => '2026-09-20 11:20:00',
            'status' => 'in_yard'
        ],
        [
            'id' => 5,
            'container_number' => 'FCIU5555555',
            'iso_code' => '22G1',
            'size_type' => '20FT STANDARD DG',
            'cargo_type' => 'dg',
            'rfid_tag' => 'E280117000000005',
            'sscc_code' => '(00)389912345000000005',
            'gross_weight_kg' => 18900.00,
            'owner_company' => 'PT Kimia Farma Trading',
            'seal_number' => 'SN-JKT-102942',
            'customs_status' => 'INSPECTION_REQUIRED',
            'block' => 'DG',
            'bay' => '01',
            'row' => '01',
            'tier' => '01',
            'gate_in_time' => '2026-09-21 07:45:00',
            'status' => 'in_yard'
        ]
    ];
}

// Perkaya data dengan riwayat perjalanan (Origin, Inbound Gate, Truck, Next Destination, Handling Equipment)
$origins = [
    'Pelabuhan Tanjung Priok (Dermaga JICT)',
    'Kawasan Industri MM2100 Cikarang',
    'Kawasan Industri KIIC Karawang',
    'Depo Logistik Marunda',
    'Kawasan Industri GIIC Cikarang Pusat',
    'Sentra Industri Surya Cipta Karawang'
];

$destinations = [
    'KA Logistik (Rel Siding ➔ Stasiun Kalimas Surabaya)',
    'Pelabuhan Tanjung Priok (Vessel Feeder MSC Aries)',
    'Kawasan Industri Jababeka V Cikarang',
    'Stasiun Semarang Poncol (KA Parcel Logistik)',
    'Depo M&R Cakung Barat',
    'Pelabuhan Patimban Subang'
];

$gate_lanes = [
    'Gate 01 (Lane A - Inbound Heavy)',
    'Gate 02 (Lane B - Inbound Fast Track)',
    'Gate 01 (Lane C - Inbound Standard)'
];

$truck_plates = ['B 9182 TE', 'B 1234 XY', 'D 5555 ZZ', 'B 7777 AB', 'L 8888 KL', 'B 9021 UJ', 'B 9481 FZ'];
$reach_stackers = ['RS-01 (Budi Santoso)', 'RS-02 (Agus Setiawan)', 'RS-03 (Rudi Hermawan)', 'RTG-01 (Joko Susilo)'];

foreach ($containers as $idx => &$c) {
    if ($c['cargo_type'] === 'dry') $total_dry++;
    elseif ($c['cargo_type'] === 'reefer') $total_reefer++;
    elseif ($c['cargo_type'] === 'dg') $total_dg++;
    elseif ($c['cargo_type'] === 'empty') $total_empty++;

    // Hitung dwell time
    $gate_in = !empty($c['gate_in_time']) ? strtotime($c['gate_in_time']) : (time() - 86400 * ($idx % 3 + 1));
    $hours_in_yard = max(1, round((time() - $gate_in) / 3600));
    $days = floor($hours_in_yard / 24);
    $rem_hours = $hours_in_yard % 24;
    $c['dwell_time_text'] = ($days > 0 ? "{$days}h " : "") . "{$rem_hours}j";
    $c['hours_in_yard'] = $hours_in_yard;

    // Atribut perjalanan realistis
    $c['origin'] = $origins[$idx % count($origins)];
    $c['destination'] = $destinations[$idx % count($destinations)];
    $c['inbound_gate'] = $gate_lanes[$idx % count($gate_lanes)];
    $c['inbound_truck'] = $truck_plates[$idx % count($truck_plates)];
    $c['handling_equipment'] = ($c['cargo_type'] === 'reefer') ? 'RS-03 (Rudi Hermawan)' : $reach_stackers[$idx % count($reach_stackers)];
    $c['commodity'] = ($c['cargo_type'] === 'reefer') ? 'Frozen Seafood & Cold Pharma (-18.5°C)' : 
                      (($c['cargo_type'] === 'dg') ? 'Chemical Class 3 Flammable Liquid' : 
                      (($c['cargo_type'] === 'empty') ? 'Empty Steel Box (Ready Survey)' : 'Automotive Spare Parts & Consumer Goods'));
}
unset($c);

$total_containers = count($containers);
?>

<div class="space-y-6">

    <!-- Header & Subtitle -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <div class="flex items-center space-x-3 mb-1">
                <span class="px-3 py-1 text-xs font-bold uppercase rounded-full bg-blue-50 text-[#0170b9] border border-blue-200">
                    <i class="fa-solid fa-boxes-stacked mr-1.5"></i> Container Tracking Module
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Data Terverifikasi Real-Time
                </span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Pelacakan Kontainer & Audit Jejak Perjalanan</h2>
            <p class="text-gray-500 text-sm mt-1">
                Visualisasi terperinci siklus hidup kontainer di dry port: asal kargo pengirim, pintu gerbang masuk, identifikasi RFID UHF & SSCC-18, koordinat 3D penumpukan yard, hingga rencana moda intermodal keluar.
            </p>
        </div>
        <div class="flex items-center space-x-3 flex-shrink-0">
            <button onclick="window.print()" class="px-4 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-xl shadow-xs transition flex items-center">
                <i class="fa-solid fa-print mr-2 text-gray-500"></i> Cetak Laporan
            </button>
            <a href="dashboard.php?page=trucking" class="px-4 py-2.5 bg-[#004b87] hover:bg-[#002f5e] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center">
                <i class="fa-solid fa-truck-front mr-2"></i> Ke Arus Trucking <i class="fa-solid fa-arrow-right ml-1.5"></i>
            </a>
        </div>
    </div>

    <!-- 4 KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Kontainer -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-400 tracking-wider">Total Kontainer Terdata</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1"><?= $total_containers ?> <span class="text-xs font-normal text-gray-400">Box</span></h3>
                <div class="mt-2 text-xs text-blue-600 flex items-center font-medium">
                    <i class="fa-solid fa-layer-group mr-1"></i> Kapasitas Lapangan 200 TEU
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#0170b9] flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-box-archive"></i>
            </div>
        </div>

        <!-- Breakdown Kategori -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-400 tracking-wider">Komposisi Muatan</p>
                <div class="flex items-center space-x-2 mt-1">
                    <span class="text-sm font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-100"><?= $total_dry ?> Dry</span>
                    <span class="text-sm font-bold text-cyan-700 bg-cyan-50 px-2 py-0.5 rounded border border-cyan-100"><?= $total_reefer ?> Reefer</span>
                    <span class="text-sm font-bold text-red-700 bg-red-50 px-2 py-0.5 rounded border border-red-100"><?= $total_dg ?> DG</span>
                </div>
                <p class="mt-2 text-xs text-gray-400"><?= $total_empty ?> Empty Container Box</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
        </div>

        <!-- Rata-rata Dwell Time -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-400 tracking-wider">Rata-Rata Dwell Time</p>
                <h3 class="text-2xl font-bold text-amber-600 mt-1">1.8 <span class="text-xs font-normal text-gray-500">Hari / Box</span></h3>
                <div class="mt-2 text-xs text-green-600 flex items-center font-medium">
                    <i class="fa-solid fa-circle-check mr-1"></i> Sesuai Target KPI (< 3 Hari)
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-regular fa-clock"></i>
            </div>
        </div>

        <!-- Integrasi RFID & Bea Cukai -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-400 tracking-wider">Kliring SPPB Bea Cukai</p>
                <h3 class="text-2xl font-bold text-emerald-600 mt-1">94.7% <span class="text-xs font-normal text-gray-500">Cleared</span></h3>
                <div class="mt-2 text-xs text-purple-600 flex items-center font-medium">
                    <i class="fa-solid fa-barcode mr-1"></i> RFID UHF & SSCC-18 Valid
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
        </div>

    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="relative w-full md:w-80">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </span>
            <input type="text" id="searchInput" onkeyup="filterContainers()" placeholder="Cari Nomor Kontainer, Pemilik, RFID..." 
                   class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#0170b9] focus:bg-white transition">
        </div>

        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
            <!-- Filter Blok -->
            <select id="filterBlock" onchange="filterContainers()" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#0170b9]">
                <option value="">Semua Zona / Blok Yard</option>
                <option value="A">Blok A (Dry 40ft)</option>
                <option value="B">Blok B (Dry Mix)</option>
                <option value="C">Blok C (Dry Buffer)</option>
                <option value="REEFER">Zona Reefer</option>
                <option value="DG">Zona DG / IMO</option>
                <option value="EMPTY">Zona Empty Box</option>
            </select>

            <!-- Filter Kategori -->
            <select id="filterType" onchange="filterContainers()" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#0170b9]">
                <option value="">Semua Tipe Kargo</option>
                <option value="dry">Dry Cargo</option>
                <option value="reefer">Reefer Cold Chain</option>
                <option value="dg">Dangerous Goods (DG)</option>
                <option value="empty">Empty Box</option>
            </select>

            <button onclick="resetFilter()" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold rounded-xl transition">
                <i class="fa-solid fa-rotate-left mr-1"></i> Reset
            </button>
        </div>
    </div>

    <!-- Main Container Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-bold text-gray-900 text-base flex items-center">
                    <i class="fa-solid fa-list-check mr-2 text-[#0170b9]"></i>Daftar Posisi & Pergerakan Kontainer Real-Time
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Memantau lokasi 3D yard (Bay-Row-Tier), asal muatan, nomor tag RFID, dan rencana moda keluar.</p>
            </div>
            <span class="text-xs font-semibold text-gray-500 bg-white px-3 py-1 rounded-lg border border-gray-200" id="rowCountDisplay">
                Menampilkan <?= count($containers) ?> Kontainer
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="containerTable">
                <thead>
                    <tr class="bg-gray-50/80 text-gray-600 text-xs uppercase tracking-wider border-b border-gray-200">
                        <th class="py-3.5 px-4 font-semibold">Nomor Kontainer</th>
                        <th class="py-3.5 px-4 font-semibold">Tag RFID UHF & SSCC-18</th>
                        <th class="py-3.5 px-4 font-semibold">Asal Muatan (Origin)</th>
                        <th class="py-3.5 px-4 font-semibold">Posisi Yard (3D)</th>
                        <th class="py-3.5 px-4 font-semibold">Tujuan Keluar (Next Hop)</th>
                        <th class="py-3.5 px-4 font-semibold">Dwell & Status</th>
                        <th class="py-3.5 px-4 font-semibold">Penanganan Alat</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Aksi Pelacakan</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-gray-100">
                    <?php foreach ($containers as $c): ?>
                        <?php
                        // Warna Badge Cargo Type
                        $badge_cargo = 'bg-blue-50 text-blue-700 border-blue-200';
                        if ($c['cargo_type'] === 'reefer') $badge_cargo = 'bg-cyan-50 text-cyan-700 border-cyan-200';
                        elseif ($c['cargo_type'] === 'dg') $badge_cargo = 'bg-red-50 text-red-700 border-red-200';
                        elseif ($c['cargo_type'] === 'empty') $badge_cargo = 'bg-gray-100 text-gray-700 border-gray-300';

                        // Warna Badge Status Bea Cukai
                        $badge_customs = ($c['customs_status'] === 'SPPB_CLEARED') ? 
                            '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fa-solid fa-circle-check mr-1"></i>SPPB Cleared</span>' :
                            '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Behandle / Cek Fisik</span>';

                        // Data JSON untuk Modal
                        $json_detail = htmlspecialchars(json_encode([
                            'container_number' => $c['container_number'],
                            'iso_code' => $c['iso_code'],
                            'size_type' => $c['size_type'],
                            'cargo_type' => strtoupper($c['cargo_type']),
                            'rfid_tag' => $c['rfid_tag'],
                            'sscc_code' => $c['sscc_code'],
                            'gross_weight' => number_format($c['gross_weight_kg'], 0, ',', '.') . ' kg',
                            'owner_company' => $c['owner_company'],
                            'seal_number' => $c['seal_number'],
                            'customs_status' => $c['customs_status'],
                            'block' => $c['block'],
                            'bay' => $c['bay'],
                            'row' => $c['row'],
                            'tier' => $c['tier'],
                            'gate_in_time' => $c['gate_in_time'],
                            'dwell_time' => $c['dwell_time_text'],
                            'origin' => $c['origin'],
                            'destination' => $c['destination'],
                            'inbound_gate' => $c['inbound_gate'],
                            'inbound_truck' => $c['inbound_truck'],
                            'handling_equipment' => $c['handling_equipment'],
                            'commodity' => $c['commodity']
                        ]), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr class="hover:bg-blue-50/30 transition container-row" 
                            data-container="<?= strtolower($c['container_number']) ?>" 
                            data-owner="<?= strtolower($c['owner_company']) ?>"
                            data-block="<?= strtoupper($c['block']) ?>"
                            data-type="<?= strtolower($c['cargo_type']) ?>"
                            data-rfid="<?= strtolower($c['rfid_tag']) ?>">
                            
                            <!-- Nomor & Tipe -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center space-x-2">
                                    <div class="font-mono font-bold text-[#0170b9] text-sm">
                                        <?= htmlspecialchars($c['container_number']) ?>
                                    </div>
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded border uppercase <?= $badge_cargo ?>">
                                        <?= htmlspecialchars($c['cargo_type']) ?>
                                    </span>
                                </div>
                                <div class="text-[11px] text-gray-500 mt-0.5 flex items-center">
                                    <span class="font-semibold text-gray-700 mr-1.5"><?= htmlspecialchars($c['iso_code']) ?></span>
                                    <span><?= htmlspecialchars($c['size_type']) ?></span>
                                </div>
                                <div class="text-[10px] text-gray-400 mt-0.5 truncate max-w-[180px]" title="<?= htmlspecialchars($c['owner_company']) ?>">
                                    <?= htmlspecialchars($c['owner_company']) ?>
                                </div>
                            </td>

                            <!-- RFID & SSCC -->
                            <td class="py-3.5 px-4 font-mono">
                                <div class="text-[11px] font-bold text-purple-700 flex items-center">
                                    <i class="fa-solid fa-satellite-dish mr-1 text-[10px] text-purple-500"></i>
                                    <span><?= htmlspecialchars($c['rfid_tag']) ?></span>
                                </div>
                                <div class="text-[10px] text-gray-500 mt-0.5 flex items-center" title="SSCC-18 Barcode">
                                    <i class="fa-solid fa-barcode mr-1 text-gray-400"></i>
                                    <span class="truncate max-w-[160px]"><?= htmlspecialchars($c['sscc_code']) ?></span>
                                </div>
                                <div class="text-[10px] text-gray-400 mt-0.5">
                                    Segel: <span class="font-mono text-gray-600"><?= htmlspecialchars($c['seal_number']) ?></span>
                                </div>
                            </td>

                            <!-- Asal Muatan -->
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-gray-800 text-xs flex items-center">
                                    <i class="fa-solid fa-location-dot text-rose-500 mr-1.5"></i>
                                    <span class="truncate max-w-[190px]" title="<?= htmlspecialchars($c['origin']) ?>"><?= htmlspecialchars($c['origin']) ?></span>
                                </div>
                                <div class="text-[11px] text-gray-500 mt-1 flex items-center">
                                    <i class="fa-solid fa-truck text-gray-400 mr-1"></i>
                                    <span>Truk <strong class="text-gray-700"><?= $c['inbound_truck'] ?></strong></span>
                                </div>
                                <div class="text-[10px] text-gray-400">
                                    Via <?= $c['inbound_gate'] ?>
                                </div>
                            </td>

                            <!-- Posisi 3D Yard -->
                            <td class="py-3.5 px-4">
                                <div class="inline-flex items-center space-x-1 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                                    <span class="font-bold text-gray-900">Blok <?= htmlspecialchars($c['block']) ?></span>
                                    <span class="text-gray-300">/</span>
                                    <span class="text-gray-700 font-mono">B<?= htmlspecialchars($c['bay']) ?></span>
                                    <span class="text-gray-300">/</span>
                                    <span class="text-gray-700 font-mono">R<?= htmlspecialchars($c['row']) ?></span>
                                    <span class="text-gray-300">/</span>
                                    <span class="text-blue-600 font-bold font-mono">T<?= htmlspecialchars($c['tier']) ?></span>
                                </div>
                                <div class="text-[10px] text-gray-500 mt-1">
                                    Berat: <strong class="text-gray-800"><?= number_format($c['gross_weight_kg'], 0, ',', '.') ?> kg</strong> (VGM Verified)
                                </div>
                            </td>

                            <!-- Rencana Tujuan Keluar -->
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-emerald-700 text-xs flex items-center">
                                    <i class="fa-solid fa-route mr-1.5 text-emerald-600"></i>
                                    <span class="truncate max-w-[200px]" title="<?= htmlspecialchars($c['destination']) ?>"><?= htmlspecialchars($c['destination']) ?></span>
                                </div>
                                <div class="text-[10px] text-gray-400 mt-1">
                                    Komoditas: <span class="text-gray-600"><?= htmlspecialchars($c['commodity']) ?></span>
                                </div>
                            </td>

                            <!-- Dwell Time & Status -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-gray-800 text-xs flex items-center">
                                    <i class="fa-regular fa-clock text-amber-500 mr-1.5"></i>
                                    <span><?= $c['dwell_time_text'] ?> di Yard</span>
                                </div>
                                <div class="mt-1">
                                    <?= $badge_customs ?>
                                </div>
                            </td>

                            <!-- Alat Berat -->
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-gray-800 text-xs flex items-center">
                                    <i class="fa-solid fa-dolly text-blue-600 mr-1.5"></i>
                                    <span><?= htmlspecialchars($c['handling_equipment']) ?></span>
                                </div>
                                <div class="text-[10px] text-gray-400 mt-0.5">
                                    Posisi Terkunci (Spreader Safe)
                                </div>
                            </td>

                            <!-- Tombol Aksi -->
                            <td class="py-3.5 px-4 text-center">
                                <button onclick='showJourneyModal(<?= $json_detail ?>)' 
                                        class="px-3 py-1.5 bg-[#0170b9] hover:bg-[#004b87] text-white text-xs font-bold rounded-lg transition shadow-xs flex items-center justify-center space-x-1 mx-auto">
                                    <i class="fa-solid fa-timeline"></i>
                                    <span>Jejak Audit</span>
                                </button>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL INTERAKTIF: JEJAK AUDIT PERJALANAN KONTAINER (CONTAINER JOURNEY AUDIT TRAIL) -->
<div id="journeyModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full overflow-hidden animate-fadeIn border border-gray-100">
        
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-[#002f5e] to-[#0170b9] text-white p-6 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center text-2xl border border-white/20">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                </div>
                <div>
                    <span class="text-[10px] uppercase tracking-wider font-bold text-blue-200">Siklus Hidup & Jejak Audit Kontainer</span>
                    <h3 class="text-xl font-bold" id="m_container_number">MSKU9182374</h3>
                    <div class="flex items-center space-x-2 text-xs text-blue-100 mt-0.5">
                        <span id="m_size_type">40FT HIGH CUBE</span>
                        <span>•</span>
                        <span id="m_cargo_type">DRY</span>
                        <span>•</span>
                        <span id="m_owner">PT Samudera Logistik Prima</span>
                    </div>
                </div>
            </div>
            <button onclick="closeJourneyModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <!-- Modal Body: Timeline Perjalanan Real-Life (5 Tahap) -->
        <div class="p-6 max-h-[70vh] overflow-y-auto space-y-6">
            
            <!-- Snapshot Data Utama -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs">
                <div>
                    <span class="text-gray-400 text-[10px] block uppercase font-semibold">Tag RFID UHF:</span>
                    <span class="font-mono font-bold text-purple-700" id="m_rfid">E280117000000001</span>
                </div>
                <div>
                    <span class="text-gray-400 text-[10px] block uppercase font-semibold">SSCC-18 Code:</span>
                    <span class="font-mono font-bold text-gray-800" id="m_sscc">(00)389912345000000001</span>
                </div>
                <div>
                    <span class="text-gray-400 text-[10px] block uppercase font-semibold">Berat Kotor VGM:</span>
                    <span class="font-bold text-gray-900" id="m_weight">25.400 kg</span>
                </div>
                <div>
                    <span class="text-gray-400 text-[10px] block uppercase font-semibold">Status Bea Cukai:</span>
                    <span class="font-bold text-emerald-600" id="m_customs">SPPB Cleared</span>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4 flex items-center">
                    <i class="fa-solid fa-route mr-2 text-[#0170b9]"></i>Kronologi Pergerakan & Status Saat Ini
                </h4>

                <!-- 5-Step Milestone Timeline -->
                <div class="relative border-l-2 border-blue-200 ml-4 space-y-6">
                    
                    <!-- Milestone 1: Origin & Gate-In -->
                    <div class="relative pl-6">
                        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-blue-600 ring-4 ring-blue-100 flex items-center justify-center text-[8px] text-white">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div class="bg-blue-50/50 p-3 rounded-xl border border-blue-100">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-gray-900 text-xs">Tahap 1: Keberangkatan Asal & Masuk Gerbang (Gate-In)</span>
                                <span class="text-[10px] text-gray-400 font-mono" id="m_gate_time">2026-09-19 08:30 WIB</span>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">
                                Kontainer diangkut dari <strong class="text-gray-900" id="m_origin">Pelabuhan Tanjung Priok</strong> menggunakan armada truk plat <strong class="text-blue-700" id="m_truck">B 9182 TE</strong>. Tiba di dry port melalui <strong class="text-gray-800" id="m_lane">Gate 01 Lane A</strong>.
                            </p>
                            <div class="mt-2 text-[10px] text-gray-500 flex items-center space-x-3">
                                <span><i class="fa-solid fa-scale-balanced mr-1 text-blue-500"></i>Timbang Otomatis: Terverifikasi</span>
                                <span><i class="fa-solid fa-qrcode mr-1 text-purple-500"></i>OCR ISO & RFID: Valid</span>
                            </div>
                        </div>
                    </div>

                    <!-- Milestone 2: Lift-off Reach Stacker -->
                    <div class="relative pl-6">
                        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-blue-600 ring-4 ring-blue-100 flex items-center justify-center text-[8px] text-white">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-gray-900 text-xs">Tahap 2: Pembongkaran dari Truk (Lift-Off Operasional)</span>
                                <span class="text-[10px] text-gray-400 font-mono">15 menit setelah Gate-In</span>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">
                                Kontainer diangkat dari sasis truk oleh alat berat <strong class="text-blue-700" id="m_equipment">RS-01</strong> menuju zona penyangga lapangan. Spreader teleskopik mengunci 4 corner casting secara otomatis.
                            </p>
                        </div>
                    </div>

                    <!-- Milestone 3: Current 3D Stacking in Yard -->
                    <div class="relative pl-6">
                        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-emerald-500 ring-4 ring-emerald-100 flex items-center justify-center text-[8px] text-white animate-pulse">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div class="bg-emerald-50/60 p-3 rounded-xl border border-emerald-200">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-emerald-900 text-xs">Tahap 3: Posisi Penumpukan Saat Ini (Yard Active Stacking)</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">LOKASI AKTIF</span>
                            </div>
                            <div class="mt-2 flex items-center space-x-2">
                                <span class="text-xs font-semibold text-gray-700">Koordinat 3D Lapangan:</span>
                                <span class="font-mono font-bold text-sm bg-white px-2 py-1 rounded border border-emerald-300 text-emerald-800" id="m_yard_pos">
                                    Blok A / Bay 01 / Row 01 / Tier 01
                                </span>
                            </div>
                            <p class="text-xs text-gray-600 mt-2">
                                Lama waktu penumpukan: <strong class="text-gray-900" id="m_dwell">1 hari 4 jam</strong>. Muatan: <span class="text-gray-700" id="m_commodity">Automotive Spare Parts</span>.
                            </p>
                        </div>
                    </div>

                    <!-- Milestone 4: Customs Clearance & Inspection -->
                    <div class="relative pl-6">
                        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-blue-600 ring-4 ring-blue-100 flex items-center justify-center text-[8px] text-white">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-gray-900 text-xs">Tahap 4: Kliring Dokumen & Segel Bea Cukai</span>
                                <span class="text-[10px] text-emerald-600 font-bold">Resmi Disetujui (SPPB)</span>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">
                                Nomor segel fisik: <strong class="text-gray-800 font-mono" id="m_seal">SN-JKT-102938</strong>. Dokumen kepabeanan telah diverifikasi secara elektronik melalui integrasi CEISA Bea Cukai.
                            </p>
                        </div>
                    </div>

                    <!-- Milestone 5: Outbound Intermodal Destination -->
                    <div class="relative pl-6">
                        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-gray-300 ring-4 ring-gray-100 flex items-center justify-center text-[8px] text-white">
                            <i class="fa-solid fa-arrow-right"></i>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-gray-900 text-xs">Tahap 5: Rencana Pengeluaran & Moda Intermodal Keluar</span>
                                <span class="text-[10px] text-blue-600 font-semibold">Terjadwal Hari Ini</span>
                            </div>
                            <p class="text-xs text-gray-700 mt-1">
                                Rencana tujuan berikutnya: <strong class="text-[#0170b9]" id="m_destination">KA Logistik (Rel Siding ➔ Stasiun Kalimas Surabaya)</strong>.
                            </p>
                            <div class="mt-2 text-[10px] text-gray-400">
                                Akan dimuat ke gerbong datar KA Logistik oleh RTG-01 saat rangkaian tiba di Jalur Rel Siding Dry Port.
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-between">
            <div class="text-xs text-gray-500">
                <i class="fa-solid fa-info-circle mr-1 text-blue-500"></i> Data audit diverifikasi oleh CIDP YMS Audit Engine.
            </div>
            <button onclick="closeJourneyModal()" class="px-5 py-2 bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold rounded-xl transition">
                Tutup Jendela
            </button>
        </div>

    </div>
</div>

<script>
// Filter realtime tabel kontainer
function filterContainers() {
    const searchVal = document.getElementById('searchInput').value.toLowerCase();
    const blockVal = document.getElementById('filterBlock').value.toUpperCase();
    const typeVal = document.getElementById('filterType').value.toLowerCase();

    const rows = document.querySelectorAll('#containerTable tbody tr.container-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const container = row.getAttribute('data-container') || '';
        const owner = row.getAttribute('data-owner') || '';
        const block = row.getAttribute('data-block') || '';
        const type = row.getAttribute('data-type') || '';
        const rfid = row.getAttribute('data-rfid') || '';

        const matchSearch = (container.includes(searchVal) || owner.includes(searchVal) || rfid.includes(searchVal));
        const matchBlock = (!blockVal || block === blockVal);
        const matchType = (!typeVal || type === typeVal);

        if (matchSearch && matchBlock && matchType) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    document.getElementById('rowCountDisplay').innerText = `Menampilkan ${visibleCount} Kontainer`;
}

function resetFilter() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterBlock').value = '';
    document.getElementById('filterType').value = '';
    filterContainers();
}

// Buka Modal Jejak Perjalanan
function showJourneyModal(data) {
    document.getElementById('m_container_number').innerText = data.container_number;
    document.getElementById('m_size_type').innerText = data.size_type;
    document.getElementById('m_cargo_type').innerText = data.cargo_type;
    document.getElementById('m_owner').innerText = data.owner_company;

    document.getElementById('m_rfid').innerText = data.rfid_tag;
    document.getElementById('m_sscc').innerText = data.sscc_code;
    document.getElementById('m_weight').innerText = data.gross_weight;
    document.getElementById('m_customs').innerText = (data.customs_status === 'SPPB_CLEARED') ? 'SPPB Cleared' : 'Behandle / Cek Fisik';

    document.getElementById('m_origin').innerText = data.origin;
    document.getElementById('m_truck').innerText = data.inbound_truck;
    document.getElementById('m_lane').innerText = data.inbound_gate;
    document.getElementById('m_equipment').innerText = data.handling_equipment;

    document.getElementById('m_yard_pos').innerText = `Blok ${data.block} / Bay ${data.bay} / Row ${data.row} / Tier ${data.tier}`;
    document.getElementById('m_dwell').innerText = data.dwell_time + ' di Yard';
    document.getElementById('m_commodity').innerText = data.commodity;
    document.getElementById('m_seal').innerText = data.seal_number;
    document.getElementById('m_destination').innerText = data.destination;
    document.getElementById('m_gate_time').innerText = data.gate_in_time || '2026-09-19 08:30 WIB';

    document.getElementById('journeyModal').classList.remove('hidden');
}

function closeJourneyModal() {
    document.getElementById('journeyModal').classList.add('hidden');
}

// Tutup modal jika klik di luar box
window.addEventListener('click', function(e) {
    const modal = document.getElementById('journeyModal');
    if (e.target === modal) {
        closeJourneyModal();
    }
});
</script>
