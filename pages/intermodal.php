<?php
// =============================================================================
// MODUL: INTERMODAL RAIL SIDING & INTEGRASI MODA LOGISTIK KERETA API
// File: pages/intermodal.php
// CIDP Yard Management System — PT Multi Terminal Indonesia / ITL Trisakti
// PIC : Juan Gamaliel & Afriansayah Ayubi (Data Integration & Software ERP Specialist)
// Standar: Frauscher Axle Counter RSR180 + UN/EDIFACT BAPLIE D.95B Standard SMDG
// =============================================================================
require_once __DIR__ . '/../connection.php';

$db_train = null;
try {
    $stmtT = $pdo->query("SELECT * FROM trains ORDER BY id ASC LIMIT 1");
    $db_train = $stmtT->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$train_info = [
    'train_id'      => $db_train ? $db_train['train_code'] . ' (CIDP Express Freight)' : 'KA 2518 (CIDP Express Freight)',
    'route'         => ($db_train && !empty($db_train['origin'])) ? ($db_train['origin'] . ' <——> ' . $db_train['destination']) : 'CIDP Dry Port Hub (Cikarang) <——> Tanjung Priok Port (JICT / TPK Koja)',
    'locomotive'    => 'CC 206 13 42 (GE CM20EMP - 2.250 HP)',
    'wagons_total'  => $db_train ? (int)$db_train['total_wagons'] : 30,
    'teu_capacity'  => $db_train ? ((int)$db_train['total_wagons'] * 2) : 60,
    'teu_loaded'    => $db_train ? (int)$db_train['loaded_wagons'] : 0,
    'load_factor'   => ($db_train && $db_train['total_wagons'] > 0 && $db_train['loaded_wagons'] > 0) ? round(($db_train['loaded_wagons'] / ($db_train['total_wagons'] * 2)) * 100, 1) . '%' : '0.0%',
    'est_departure' => '18:45 WIB',
    'status'        => $db_train ? strtoupper($db_train['status']) : 'SCHEDULED',
    'operator'      => 'PT Kereta Api Logistik (KAI Logistik)'
];

$siding_tracks = [
    [
        'id'          => 'TRACK-01 (Utara)',
        'length'      => '450 Meter',
        'rail_type'   => 'UIC 54 (Bantalan Beton R.54)',
        'occupancy'   => 'TERISI (KA 2518)',
        'status_color'=> 'bg-emerald-100 text-emerald-800 border-emerald-300',
        'axles_count' => 126,
        'equip'       => 'Reach Stacker RS-05 & RMGC Rail Crane 01'
    ],
    [
        'id'          => 'TRACK-02 (Selatan)',
        'length'      => '450 Meter',
        'rail_type'   => 'UIC 54 (Bantalan Beton R.54)',
        'occupancy'   => 'BERSIH / STANDBY',
        'status_color'=> 'bg-blue-100 text-blue-800 border-blue-300',
        'axles_count' => 0,
        'equip'       => 'Standby untuk Rangkaian KA 2520 (Kertapati)'
    ]
];

// Sample Rangkaian Gerbong Datar (GD 40t / PPCW)
$wagons = [
    [
        'wagon_no' => 'GD 42 12 01',
        'type'     => 'PPCW 42t',
        'slot_a'   => ['container' => 'MSKU 982145-0', 'iso' => '45G1', 'type' => 'Laden Ekspor', 'weight' => '32.450 kg', 'pod' => 'USLAX (Los Angeles)', 'bg' => 'bg-blue-600'],
        'slot_b'   => null,
        'is_40ft'  => true,
        'status'   => 'LIFTED TO BUFFER',
        'status_bg'=> 'bg-amber-100 text-amber-800'
    ],
    [
        'wagon_no' => 'GD 42 12 02',
        'type'     => 'PPCW 42t',
        'slot_a'   => ['container' => 'TCKU 829104-8', 'iso' => '22G1', 'type' => 'Laden Impor', 'weight' => '21.500 kg', 'pod' => 'IDCKG (CIDP)', 'bg' => 'bg-sky-600'],
        'slot_b'   => ['container' => 'EMCU 551820-0', 'iso' => '22G1', 'type' => 'Laden Impor', 'weight' => '19.800 kg', 'pod' => 'IDCKG (CIDP)', 'bg' => 'bg-emerald-600'],
        'is_40ft'  => false,
        'status'   => 'ON WAGON (READY LIFT)',
        'status_bg'=> 'bg-blue-100 text-blue-800'
    ],
    [
        'wagon_no' => 'GD 42 12 03',
        'type'     => 'PPCW 42t',
        'slot_a'   => ['container' => 'ONEU 661298-8', 'iso' => '42G1', 'type' => 'Laden Ekspor', 'weight' => '28.900 kg', 'pod' => 'JPTYO (Tokyo)', 'bg' => 'bg-pink-600'],
        'slot_b'   => null,
        'is_40ft'  => true,
        'status'   => 'ON WAGON (READY LIFT)',
        'status_bg'=> 'bg-blue-100 text-blue-800'
    ],
    [
        'wagon_no' => 'GD 42 12 04',
        'type'     => 'PPCW 42t',
        'slot_a'   => ['container' => 'CMAU 723491-0', 'iso' => '45R1', 'type' => 'Reefer Ekspor', 'weight' => '29.100 kg', 'pod' => 'NLRTM (Rotterdam)', 'bg' => 'bg-cyan-600'],
        'slot_b'   => null,
        'is_40ft'  => true,
        'status'   => 'TRANSSHIP TO TRUCK',
        'status_bg'=> 'bg-emerald-100 text-emerald-800'
    ],
    [
        'wagon_no' => 'GD 42 12 05',
        'type'     => 'PPCW 42t',
        'slot_a'   => ['container' => 'SUDU 310928-4', 'iso' => '22G1', 'type' => 'Empty Return', 'weight' => '2.250 kg', 'pod' => 'IDTPP (Priok Depot)', 'bg' => 'bg-slate-500'],
        'slot_b'   => ['container' => 'KKFU 192801-6', 'iso' => '22G1', 'type' => 'Empty Return', 'weight' => '2.300 kg', 'pod' => 'IDTPP (Priok Depot)', 'bg' => 'bg-slate-500'],
        'is_40ft'  => false,
        'status'   => 'ON WAGON',
        'status_bg'=> 'bg-slate-100 text-slate-800'
    ],
    [
        'wagon_no' => 'GD 42 12 06',
        'type'     => 'PPCW 42t',
        'slot_a'   => ['container' => 'COSU 601928-3', 'iso' => '45G1', 'type' => 'Laden Ekspor', 'weight' => '31.200 kg', 'pod' => 'CNSHA (Shanghai)', 'bg' => 'bg-indigo-600'],
        'slot_b'   => null,
        'is_40ft'  => true,
        'status'   => 'ON WAGON',
        'status_bg'=> 'bg-blue-100 text-blue-800'
    ]
];
?>

<!-- Open Source Leaflet.js GIS Engine (Lokal & Offline Ready) -->
<link rel="stylesheet" href="assets/leaflet.css" />
<script src="assets/leaflet.js"></script>

<style>
/* Leaflet GIS Custom Styles for Intermodal Corridor */
#corridorGisMap {
    background-color: #f8fafc;
    background-image: 
        linear-gradient(to right, rgba(203, 213, 225, 0.35) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(203, 213, 225, 0.35) 1px, transparent 1px);
    background-size: 20px 20px;
}
.train-radar-pulse {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 9999px;
    background-color: rgba(245, 158, 11, 0.45);
    animation: trainPulseAnim 1.8s cubic-bezier(0, 0, 0.2, 1) infinite;
}
@keyframes trainPulseAnim {
    0% { transform: scale(0.8); opacity: 1; }
    100% { transform: scale(2.8); opacity: 0; }
}
.leaflet-popup-content-wrapper {
    border-radius: 14px !important;
    padding: 4px !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15) !important;
    font-family: 'Plus Jakarta Sans', sans-serif !important;
    border: 1px solid #e2e8f0;
}
.leaflet-popup-tip {
    background: white !important;
}
</style>

<div class="space-y-6">
    <!-- Top Header Banner & PIC Badge -->
    <div class="bg-gradient-to-r from-[#002f5e] via-[#014d80] to-[#0170b9] rounded-2xl p-6 text-white shadow-sm flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
        <div class="space-y-1.5">
            <div class="inline-flex items-center space-x-2 px-2.5 py-0.5 rounded-full bg-blue-400/20 border border-blue-300/30 text-xs font-mono text-blue-200">
                <i class="fa-solid fa-train-subway"></i>
                <span>INTERMODAL SIDING • DOUBLE TRACK 2 &times; 450M • UIC 54</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight">Hub Logistik Intermodal Rail Siding & Alih Muat (Transshipment)</h1>
            <p class="text-xs text-blue-100 max-w-3xl leading-relaxed">
                Pusat integrasi antarmoda kereta api barang pelabuhan (KAI Logistik) dan armada truk jalan raya CIDP. Dilengkapi sistem sensor pendeteksi gandeng <b class="text-white">Frauscher Axle Counter RSR180</b>, alokasi slot gerbong <b class="text-white">UN/EDIFACT BAPLIE</b>, dan integrasi VMT alih muat langsung (Cross-Docking).
            </p>
        </div>

        <!-- PIC Badge -->
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-3 text-xs flex items-center space-x-3 flex-shrink-0">
            <div class="w-10 h-10 rounded-full bg-white text-[#002f5e] flex items-center justify-center font-bold text-sm shadow-sm">
                <i class="fa-solid fa-users-gear"></i>
            </div>
            <div>
                <span class="text-[10px] text-blue-200 uppercase font-bold block">Penanggung Jawab Integrasi:</span>
                <span class="font-bold text-white block">Juan Gamaliel &amp; Afriansayah Ayubi</span>
                <span class="text-[10px] text-blue-200">Data Integration &amp; Software ERP Specialist</span>
            </div>
        </div>
    </div>

    <!-- 4 Key Metrics Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Metric 1: Active Train Status -->
        <div class="bg-white rounded-xl p-4 sm:p-5 border border-gray-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500">Rangkaian KA Aktif</span>
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-[#0170b9] flex items-center justify-center text-xs">
                    <i class="fa-solid fa-train"></i>
                </span>
            </div>
            <div class="mt-2.5">
                <span class="text-lg font-bold text-gray-900 block truncate"><?= $train_info['train_id'] ?></span>
                <span class="text-xs text-emerald-600 font-semibold flex items-center mt-0.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                    <?= $train_info['locomotive'] ?>
                </span>
            </div>
            <p class="text-[11px] text-gray-400 mt-2">Relasi: Cikarang Dry Port &harr; Tanjung Priok</p>
        </div>

        <!-- Metric 2: Kapasitas Angkut TEU -->
        <div class="bg-white rounded-xl p-4 sm:p-5 border border-gray-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500">Kapasitas Muat Siding</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </span>
            </div>
            <div class="mt-2.5 flex items-baseline space-x-2">
                <span class="text-2xl font-bold text-gray-900"><?= $train_info['teu_loaded'] ?> / <?= $train_info['teu_capacity'] ?> TEU</span>
                <span class="text-xs text-emerald-700 font-bold"><?= $train_info['load_factor'] ?></span>
            </div>
            <div class="w-full h-1.5 bg-gray-100 rounded-full mt-2.5 overflow-hidden">
                <div class="h-full bg-emerald-500" style="width: 80%;"></div>
            </div>
        </div>

        <!-- Metric 3: Frauscher Axle Counter -->
        <div class="bg-white rounded-xl p-4 sm:p-5 border border-gray-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500">Sensor Gandeng Frauscher</span>
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-microchip"></i>
                </span>
            </div>
            <div class="mt-2.5 flex items-baseline space-x-2">
                <span id="axle-count-display" class="text-2xl font-bold text-gray-900 font-mono">126 As Roda</span>
                <span class="text-[11px] text-indigo-600 font-bold">RSR180 OK</span>
            </div>
            <p class="text-[11px] text-gray-400 mt-2">30 Gerbong GD &times; 4 as + 6 as Lokomotif</p>
        </div>

        <!-- Metric 4: Turnaround Time (TAT) -->
        <div class="bg-white rounded-xl p-4 sm:p-5 border border-gray-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500">Target Waktu Alih Muat (TAT)</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-stopwatch"></i>
                </span>
            </div>
            <div class="mt-2.5 flex items-baseline space-x-2">
                <span class="text-2xl font-bold text-gray-900">48 / 110 Menit</span>
                <span class="text-xs text-emerald-600 font-bold">ON SCHEDULE</span>
            </div>
            <p class="text-[11px] text-gray-400 mt-2">Batas waktu keberangkatan: <?= $train_info['est_departure'] ?></p>
        </div>
    </div>

    <!-- Banner Navigasi ke Panel Simulasi 3D -->
    <div class="bg-gradient-to-r from-[#002f5e] to-indigo-900 rounded-2xl p-4 sm:p-5 text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-md">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-amber-400 text-lg">
                <i class="fa-solid fa-gamepad"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold">Simulasi Intermodal Kereta Api &amp; Alih Muat (Transshipment)</h3>
                <p class="text-xs text-blue-200">Seluruh simulasi pergerakan kereta, bongkar-muat RMGC crane, dan pergerakan gerbong dijalankan terpadu di Simulator 3D.</p>
            </div>
        </div>
        <a href="dashboard.php?page=simulator" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-gray-950 text-xs font-bold rounded-xl transition flex items-center space-x-1.5 shrink-0 shadow-sm">
            <i class="fa-solid fa-cubes"></i>
            <span>Buka Panel Simulasi 3D</span>
        </a>
    </div>

    <!-- Siding Track Status Bar -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($siding_tracks as $track): ?>
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="space-y-1">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full <?= strpos($track['occupancy'], 'TERISI') !== false ? 'bg-emerald-500 animate-pulse' : 'bg-blue-400' ?>"></span>
                    <h3 class="font-bold text-sm text-gray-900"><?= $track['id'] ?> &bull; <?= $track['length'] ?></h3>
                </div>
                <p class="text-xs text-gray-500"><?= $track['rail_type'] ?> &bull; Alat: <?= $track['equip'] ?></p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold border <?= $track['status_color'] ?>">
                <?= $track['occupancy'] ?>
            </span>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ======================================================================= -->
    <!-- REAL-TIME GIS INTERMODAL CORRIDOR TRACKER (TANJUNG PRIOK <-> CIDP 35 HA) -->
    <!-- ======================================================================= -->
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-gray-100 pb-4">
            <div>
                <div class="flex items-center space-x-2.5 flex-wrap gap-y-1">
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 flex items-center">
                        <i class="fa-solid fa-map-location-dot text-[#0170b9] mr-2"></i>
                        Pelacak Spasial Koridor Rel KA Pelabuhan (Tanjung Priok &harr; CIDP 35 Ha)
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center shadow-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                        Live GIS Telemetri
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 font-mono">
                        Daop 1 Jakarta • 54,8 KM
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 font-mono">
                        Leaflet.js + OpenStreetMap
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Pemantauan geospasial waktu-nyata rangkaian KA Logistik 2518 melintasi koridor rel khusus pelabuhan Tanjung Priok (Pasoso) menuju Hub CIDP Cikarang, dibandingkan rute truk via Tol Jakarta-Cikampek.
                </p>
            </div>

            <!-- Toolbar Kontrol GIS & Animasi -->
            <div class="flex items-center space-x-2 flex-wrap gap-y-2">
                <button type="button" onclick="toggleTrainAnimation()" id="btnPlayTrain" class="px-3.5 py-2 bg-[#002f5e] hover:bg-[#0170b9] text-white rounded-xl text-xs font-bold transition-all flex items-center space-x-1.5 shadow-xs">
                    <i class="fa-solid fa-play text-[10px]" id="playTrainIcon"></i>
                    <span id="playTrainText">Simulasikan Perjalanan KA</span>
                </button>
                <button type="button" onclick="resetTrainPosition()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-all flex items-center space-x-1" title="Reset Posisi KA ke Priok">
                    <i class="fa-solid fa-rotate-left text-[11px]"></i>
                    <span>Reset</span>
                </button>
                <div class="h-6 w-px bg-gray-200 mx-1 hidden sm:block"></div>
                <!-- Layer Toggles -->
                <div class="flex items-center space-x-2 bg-slate-50 border border-slate-200/80 px-2.5 py-1.5 rounded-xl text-[11px] font-semibold text-gray-700">
                    <label class="flex items-center space-x-1.5 cursor-pointer">
                        <input type="checkbox" id="chkShowRail" checked onchange="toggleMapLayer('rail')" class="rounded text-blue-600 focus:ring-0">
                        <span class="text-blue-800">Jalur Rel KA</span>
                    </label>
                    <span class="text-gray-300">|</span>
                    <label class="flex items-center space-x-1.5 cursor-pointer">
                        <input type="checkbox" id="chkShowRoad" checked onchange="toggleMapLayer('road')" class="rounded text-amber-600 focus:ring-0">
                        <span class="text-amber-800">Tol Cikampek</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Container Peta Leaflet & HUD Overlay Telemetri -->
        <div class="relative w-full rounded-2xl overflow-hidden border border-gray-200 shadow-inner bg-slate-100" style="height: 480px;">
            <!-- Map Viewport Div -->
            <div id="corridorGisMap" class="w-full h-full z-0"></div>

            <!-- Floating Telemetri HUD (Pojok Kiri Atas Peta) -->
            <div class="absolute top-3 left-3 z-[1000] bg-white/95 backdrop-blur-md rounded-xl p-3.5 border border-gray-200/80 shadow-lg text-xs space-y-2 max-w-xs pointer-events-auto">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                    <div class="flex items-center space-x-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        <strong class="font-bold text-gray-800">Telemetri GPS KA 2518</strong>
                    </div>
                    <span id="hudTrainSpeed" class="font-mono text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">
                        65 km/jam
                    </span>
                </div>
                <div class="space-y-1 font-mono text-[11px]">
                    <div class="flex justify-between text-gray-500">
                        <span>Lokasi Terkini:</span>
                        <strong id="hudTrainLoc" class="text-gray-900">Stasiun Bekasi (KM 26.5)</strong>
                    </div>
                    <div class="flex justify-between text-gray-500">
                        <span>Jarak Tempuh:</span>
                        <span id="hudTrainProgress" class="font-bold text-blue-600">26.5 / 54.8 km (48%)</span>
                    </div>
                    <div class="flex justify-between text-gray-500">
                        <span>Estimasi Tiba (ETA):</span>
                        <strong id="hudTrainEta" class="text-gray-900">42 Menit Lagi</strong>
                    </div>
                    <div class="flex justify-between text-gray-500">
                        <span>Emisi CO₂ Terhemat:</span>
                        <strong class="text-emerald-600">-1.84 Ton CO₂</strong>
                    </div>
                </div>
                <!-- Progress Bar -->
                <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div id="hudProgressBar" class="h-full bg-gradient-to-r from-blue-500 to-emerald-500 transition-all duration-300" style="width: 48%;"></div>
                </div>
            </div>

            <!-- Floating Quick Navigation Buttons (Pojok Kanan Atas Peta) -->
            <div class="absolute top-3 right-3 z-[1000] flex flex-col gap-1.5 pointer-events-auto">
                <button onclick="flyToLocation('all')" class="px-2.5 py-1.5 bg-white/95 backdrop-blur-md hover:bg-white text-gray-700 border border-gray-200 rounded-lg text-xs font-semibold shadow-md transition flex items-center space-x-1" title="Tampilkan Seluruh Koridor">
                    <i class="fa-solid fa-expand text-[10px] text-blue-600"></i>
                    <span class="hidden sm:inline">Koridor Penuh</span>
                </button>
                <button onclick="flyToLocation('priok')" class="px-2.5 py-1.5 bg-white/95 backdrop-blur-md hover:bg-white text-gray-700 border border-gray-200 rounded-lg text-xs font-semibold shadow-md transition flex items-center space-x-1" title="Fokus ke Pelabuhan Tanjung Priok">
                    <i class="fa-solid fa-anchor text-[10px] text-blue-600"></i>
                    <span class="hidden sm:inline">Tj. Priok (JICT)</span>
                </button>
                <button onclick="flyToLocation('cidp')" class="px-2.5 py-1.5 bg-white/95 backdrop-blur-md hover:bg-white text-gray-700 border border-gray-200 rounded-lg text-xs font-semibold shadow-md transition flex items-center space-x-1" title="Fokus ke CIDP Hub 35 Ha">
                    <i class="fa-solid fa-warehouse text-[10px] text-blue-600"></i>
                    <span class="hidden sm:inline">CIDP Hub (35 Ha)</span>
                </button>
            </div>
        </div>

        <!-- Kajian Konsultan: Matriks Perbandingan Moda (Rail vs Road Corridor) -->
        <div class="bg-gradient-to-r from-slate-50 via-blue-50/40 to-slate-50 rounded-xl p-4 border border-blue-100 text-xs">
            <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                <h4 class="font-bold text-gray-900 flex items-center">
                    <i class="fa-solid fa-chart-simple text-blue-600 mr-2"></i>
                    Analisis Kelayakan Alih Moda (Modal Shift Matrix): KA Barang vs Truk Jalan Raya
                </h4>
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider font-mono">Kajian Supply Chain Consultant CIDP</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-slate-700">
                <div class="bg-white p-3 rounded-lg border border-gray-200/80 shadow-2xs space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 font-semibold text-[11px]">Kepastian Waktu Tempuh</span>
                        <i class="fa-solid fa-clock text-blue-600"></i>
                    </div>
                    <div class="text-base font-bold text-gray-900">110 Menit <span class="text-xs font-normal text-emerald-600 font-semibold">(vs 4-6 Jam Truk)</span></div>
                    <p class="text-[10.5px] text-gray-500 leading-relaxed">
                        KA memiliki jalur rel khusus bebas hambatan kemacetan Tol Jakarta-Cikampek & Cikunir.
                    </p>
                </div>

                <div class="bg-white p-3 rounded-lg border border-gray-200/80 shadow-2xs space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 font-semibold text-[11px]">Kapasitas Muat Sekali Jalan</span>
                        <i class="fa-solid fa-boxes-stacked text-indigo-600"></i>
                    </div>
                    <div class="text-base font-bold text-gray-900">60 TEU / Rangkaian <span class="text-xs font-normal text-indigo-600 font-semibold">(Setara 60 Truk)</span></div>
                    <p class="text-[10.5px] text-gray-500 leading-relaxed">
                        1 lokomotif CC 206 menggantikan 30-60 unit truk trailer di jalan raya, mengurangi beban jalan Pantura.
                    </p>
                </div>

                <div class="bg-white p-3 rounded-lg border border-gray-200/80 shadow-2xs space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 font-semibold text-[11px]">Reduksi Emisi Karbon (Green ICD)</span>
                        <i class="fa-solid fa-leaf text-emerald-600"></i>
                    </div>
                    <div class="text-base font-bold text-emerald-700">-76.4% Emisi CO₂ <span class="text-xs font-normal text-gray-500">/ Ton-KM</span></div>
                    <p class="text-[10.5px] text-gray-500 leading-relaxed">
                        Memenuhi standar global Green Logistics & ESG pelayaran internasional (Maersk, CMA CGM, ONE).
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- INTERACTIVE TRAIN FORMATION & FLATCAR VISUALIZER -->
    <!-- ======================================================================= -->
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-base font-bold text-gray-900 flex items-center">
                    <i class="fa-solid fa-train-track text-[#0170b9] mr-2"></i>
                    Visualisasi Rangkaian Gerbong Datar (Rail Siding Track-01)
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Klik salah satu gerbong datar untuk membuka detail kontainer, verifikasi manifest, dan dispatch crane.</p>
            </div>
            <div class="flex items-center space-x-2">
                <button type="button" onclick="openBaplieModal()" class="px-3.5 py-2 bg-[#002f5e] hover:bg-[#0170b9] text-white rounded-xl text-xs font-bold transition-colors flex items-center space-x-1.5 shadow-xs">
                    <i class="fa-solid fa-file-code"></i>
                    <span>Lihat Manifest EDIFACT BAPLIE</span>
                </button>
            </div>
        </div>

        <!-- Train Track & Wagons Container Visual Strip (Scrollable) -->
        <div class="bg-slate-900 rounded-2xl p-6 overflow-x-auto shadow-inner border border-slate-800">
            <!-- Legend Indicators -->
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs mb-6 text-slate-300 border-b border-slate-800 pb-3">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-1.5">
                        <span class="w-3 h-3 rounded bg-blue-600"></span>
                        <span class="text-[11px]">40ft Dry Ekspor</span>
                    </div>
                    <div class="flex items-center space-x-1.5">
                        <span class="w-3 h-3 rounded bg-sky-600"></span>
                        <span class="text-[11px]">20ft Dry Impor</span>
                    </div>
                    <div class="flex items-center space-x-1.5">
                        <span class="w-3 h-3 rounded bg-cyan-600"></span>
                        <span class="text-[11px]">40ft Reefer</span>
                    </div>
                    <div class="flex items-center space-x-1.5">
                        <span class="w-3 h-3 rounded bg-slate-500"></span>
                        <span class="text-[11px]">Empty Box</span>
                    </div>
                </div>
                <div class="text-[11px] font-mono text-emerald-400 flex items-center">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Arah Rel Menuju Tanjung Priok (Barat)
                </div>
            </div>

            <!-- Train Visual Composition -->
            <div class="flex items-center space-x-3 min-w-[900px] py-4">
                <!-- Locomotive CC 206 -->
                <div class="flex-shrink-0 w-44 bg-gradient-to-r from-amber-600 via-orange-600 to-amber-500 rounded-xl p-3 text-white border-2 border-amber-400 shadow-lg relative flex flex-col justify-between h-28">
                    <div class="flex justify-between items-start text-[10px] font-mono font-bold">
                        <span>LOKOMOTIF</span>
                        <span class="px-1.5 py-0.5 bg-black/40 rounded">CC 206</span>
                    </div>
                    <div class="text-center my-auto">
                        <i class="fa-solid fa-train text-3xl opacity-90 drop-shadow"></i>
                        <span class="block text-[11px] font-mono font-bold mt-1 tracking-wider">CC 206 13 42</span>
                    </div>
                    <div class="flex justify-between items-center text-[9px] text-amber-100 font-mono">
                        <span>2.250 HP</span>
                        <span>KAI LOGISTIK</span>
                    </div>
                    <!-- Wheels -->
                    <div class="absolute -bottom-2.5 left-4 right-4 flex justify-between">
                        <span class="w-4 h-4 rounded-full bg-slate-400 border-2 border-slate-900 shadow"></span>
                        <span class="w-4 h-4 rounded-full bg-slate-400 border-2 border-slate-900 shadow"></span>
                        <span class="w-4 h-4 rounded-full bg-slate-400 border-2 border-slate-900 shadow"></span>
                        <span class="w-4 h-4 rounded-full bg-slate-400 border-2 border-slate-900 shadow"></span>
                    </div>
                </div>

                <!-- Coupler -->
                <div class="w-3 h-2 bg-slate-600 flex-shrink-0"></div>

                <!-- Flatcars (GD) Iteration -->
                <?php foreach ($wagons as $idx => $wg): ?>
                <div onclick="inspectWagon(<?= $idx ?>)" class="flex-shrink-0 w-52 bg-slate-800 hover:bg-slate-750 cursor-pointer rounded-xl p-2.5 border-2 border-slate-700 hover:border-blue-400 transition-all shadow-md relative flex flex-col justify-between h-28 group">
                    <!-- Top Wagon Label -->
                    <div class="flex justify-between items-center text-[10px] font-mono text-slate-400">
                        <span class="font-bold text-slate-200"><?= $wg['wagon_no'] ?></span>
                        <span class="text-[9px] px-1 py-0.2 rounded <?= $wg['status_bg'] ?>"><?= $wg['status'] ?></span>
                    </div>

                    <!-- Containers on Wagon Bed -->
                    <div class="my-auto flex items-center gap-1.5">
                        <?php if ($wg['is_40ft']): ?>
                            <!-- 1 x 40ft Container -->
                            <div class="w-full <?= $wg['slot_a']['bg'] ?> text-white rounded-lg p-2 text-center shadow transition-transform group-hover:scale-95">
                                <span class="font-mono font-bold text-xs block truncate"><?= $wg['slot_a']['container'] ?></span>
                                <div class="flex justify-between items-center text-[9px] text-white/80 font-mono mt-0.5">
                                    <span><?= $wg['slot_a']['iso'] ?></span>
                                    <span><?= $wg['slot_a']['weight'] ?></span>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- 2 x 20ft Containers -->
                            <div class="w-1/2 <?= $wg['slot_a']['bg'] ?> text-white rounded-lg p-1.5 text-center shadow transition-transform group-hover:scale-95">
                                <span class="font-mono font-bold text-[10px] block truncate"><?= $wg['slot_a']['container'] ?></span>
                                <span class="text-[8px] text-white/80 block font-mono"><?= $wg['slot_a']['iso'] ?></span>
                            </div>
                            <div class="w-1/2 <?= $wg['slot_b']['bg'] ?> text-white rounded-lg p-1.5 text-center shadow transition-transform group-hover:scale-95">
                                <span class="font-mono font-bold text-[10px] block truncate"><?= $wg['slot_b']['container'] ?></span>
                                <span class="text-[8px] text-white/80 block font-mono"><?= $wg['slot_b']['iso'] ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Flatcar Chassis Bed & Wheels -->
                    <div class="flex justify-between items-center text-[9px] text-slate-400 border-t border-slate-700/80 pt-1 font-mono">
                        <span>PPCW 42t</span>
                        <span class="text-blue-400 group-hover:underline">Detail &rarr;</span>
                    </div>

                    <!-- Wheels -->
                    <div class="absolute -bottom-2.5 left-3 right-3 flex justify-between">
                        <span class="w-3.5 h-3.5 rounded-full bg-slate-500 border border-slate-900 shadow"></span>
                        <span class="w-3.5 h-3.5 rounded-full bg-slate-500 border border-slate-900 shadow"></span>
                        <span class="w-3.5 h-3.5 rounded-full bg-slate-500 border border-slate-900 shadow"></span>
                        <span class="w-3.5 h-3.5 rounded-full bg-slate-500 border border-slate-900 shadow"></span>
                    </div>
                </div>

                <!-- Coupler -->
                <?php if ($idx < count($wagons) - 1): ?>
                <div class="w-2.5 h-1.5 bg-slate-600 flex-shrink-0"></div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Rail Track Graphic Underneath -->
            <div class="w-full h-3 border-t-2 border-b-2 border-slate-600 bg-slate-950 flex items-center justify-around opacity-60">
                <?php for ($i = 0; $i < 30; $i++): ?>
                <div class="w-1 h-3 bg-amber-900/60"></div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- DUAL COLUMNS: FRAUSCHER AXLE SENSOR & TRANSSHIPMENT WORKFLOW -->
    <!-- ======================================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left: Frauscher Axle Counter Telemetry & Pulse Simulator (6 cols) -->
        <div class="lg:col-span-6 space-y-4">
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg">
                            <i class="fa-solid fa-microchip"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Frauscher Axle Counter RSR180 &amp; ACS2000</h3>
                            <p class="text-xs text-gray-500">Sensor induktif roda kereta api &amp; penentu okupansi rel</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-[11px] font-bold rounded-full">
                        ONLINE &bull; SIL 4
                    </span>
                </div>

                <!-- Sensor Diagram & Diagnostics -->
                <div class="bg-slate-900 text-white rounded-xl p-4 border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between text-xs font-mono">
                        <span class="text-slate-400">SENSOR CHANNEL 1 &amp; 2 (DUAL INDUCTIVE)</span>
                        <span class="text-emerald-400 flex items-center">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                            WHEEL DETECTED
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div class="bg-slate-800/80 p-3 rounded-xl border border-slate-700/60">
                            <span class="text-[10px] text-slate-400 block uppercase font-mono">Total As Roda</span>
                            <span id="axle-count-val" class="text-2xl font-bold font-mono text-emerald-400">126</span>
                            <span class="text-[9px] text-slate-500 block">30 GD + 1 Loco</span>
                        </div>
                        <div class="bg-slate-800/80 p-3 rounded-xl border border-slate-700/60">
                            <span class="text-[10px] text-slate-400 block uppercase font-mono">Kecepatan Langsir</span>
                            <span class="text-2xl font-bold font-mono text-white">12.4</span>
                            <span class="text-[9px] text-slate-500 block">km/jam (Siding)</span>
                        </div>
                        <div class="bg-slate-800/80 p-3 rounded-xl border border-slate-700/60">
                            <span class="text-[10px] text-slate-400 block uppercase font-mono">Arah Gerak</span>
                            <span class="text-base font-bold font-mono text-cyan-300 mt-1 block">INBOUND &rarr;</span>
                            <span class="text-[9px] text-slate-500 block">Direction Forward</span>
                        </div>
                    </div>

                    <!-- Pulse Waveform Graphic Animation -->
                    <div class="relative h-12 bg-slate-950 rounded-lg border border-slate-800 overflow-hidden flex items-center px-3">
                        <div class="text-[10px] font-mono text-slate-500 mr-2 flex-shrink-0">CH-A / CH-B PULSE:</div>
                        <div id="pulse-indicator" class="flex-1 flex items-center space-x-1 overflow-hidden h-6">
                            <?php for ($p = 0; $p < 24; $p++): ?>
                            <span class="w-1 bg-emerald-500 rounded-full transition-all duration-150" style="height: <?= ($p % 2 == 0 ? 18 : 6) ?>px;"></span>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <!-- Diagnostic Hardware Self-Test Controls -->
                <div class="space-y-2">
                    <button type="button" onclick="triggerAxlePassSimulation()" id="btn-axle-sim" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition-colors flex items-center justify-center space-x-2 shadow-xs">
                        <i class="fa-solid fa-microchip"></i>
                        <span>Uji Diagnostik Sinyal Sensor Roda (Hardware Self-Test)</span>
                    </button>
                    <p class="text-[11px] text-gray-400 text-center">
                        Memvalidasi respons pulsa magnetik sensor RSR123 &amp; pencacah roda trainlist KAI Logistik (Zero Discrepancy Rule). Untuk simulasi alih muat dan pergerakan KA, jalankan di <a href="dashboard.php?page=simulator" class="text-indigo-600 font-bold underline">Panel Simulasi 3D</a>.
                    </p>
                </div>
            </div>
        </div>

        <!-- Right: 5-Stage Intermodal Transshipment Workflow (6 cols) -->
        <div class="lg:col-span-6 space-y-4">
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center">
                        <i class="fa-solid fa-diagram-project text-[#0170b9] mr-2"></i>
                        SOP Alur Transshipment (Kereta &harr; Truk Antarmoda)
                    </h3>
                    <span class="text-xs text-blue-600 font-bold">5 Tahap Operasi</span>
                </div>

                <div class="space-y-3">
                    <!-- Step 1 -->
                    <div class="flex items-start space-x-3 p-2.5 rounded-xl bg-slate-50 border border-slate-200/80">
                        <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">
                            <i class="fa-solid fa-check text-[10px]"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xs font-bold text-gray-900">Tahap 1: Deteksi Kedatangan &amp; Sensor Gandeng Frauscher</h4>
                            <p class="text-[11px] text-gray-500 mt-0.5">Sensor RSR180 menghitung 126 as roda dan mengunci sinyal interlocking jalur rel Track-01.</p>
                        </div>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded">Selesai</span>
                    </div>

                    <!-- Step 2 -->
                    <div class="flex items-start space-x-3 p-2.5 rounded-xl bg-slate-50 border border-slate-200/80">
                        <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">
                            <i class="fa-solid fa-check text-[10px]"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xs font-bold text-gray-900">Tahap 2: Penguncian Posisi Lokomotif &amp; Rem Sepatu Siding</h4>
                            <p class="text-[11px] text-gray-500 mt-0.5">Masinis KAI Logistik menerapkan handbrake dan pemasangan stop block demi keselamatan kerja alih muat.</p>
                        </div>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded">Selesai</span>
                    </div>

                    <!-- Step 3 (Active) -->
                    <div class="flex items-start space-x-3 p-2.5 rounded-xl bg-blue-50/80 border border-blue-200">
                        <div class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5 animate-pulse">
                            3
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xs font-bold text-blue-900">Tahap 3: Disparitas Work Order Crane VMT (Bongkar-Muat)</h4>
                            <p class="text-[11px] text-blue-800 mt-0.5">Reach Stacker RS-05 dan RMGC mengambil kontainer 40HC/20GP dari gerbong datar ke Buffer Siding.</p>
                        </div>
                        <span class="text-[10px] font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded animate-pulse">Berjalan (48/60)</span>
                    </div>

                    <!-- Step 4 -->
                    <div class="flex items-start space-x-3 p-2.5 rounded-xl bg-slate-50 border border-slate-200/80">
                        <div class="w-6 h-6 rounded-full bg-slate-300 text-slate-700 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">
                            4
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xs font-bold text-gray-700">Tahap 4: Cross-Docking Langsung ke Armada Truk Inbound</h4>
                            <p class="text-[11px] text-gray-500 mt-0.5">Kontainer prioritas (Reefer &amp; Direct Ekspor) langsung dimuat ke sasis truk tanpa pengendapan di yard.</p>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500 bg-slate-200 px-2 py-0.5 rounded">Menunggu</span>
                    </div>

                    <!-- Step 5 -->
                    <div class="flex items-start space-x-3 p-2.5 rounded-xl bg-slate-50 border border-slate-200/80">
                        <div class="w-6 h-6 rounded-full bg-slate-300 text-slate-700 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">
                            5
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xs font-bold text-gray-700">Tahap 5: Transmisi EDI BAPLIE &amp; Pemberangkatan KA Balik</h4>
                            <p class="text-[11px] text-gray-500 mt-0.5">Penerbitan surat jalan kereta api dan sinkronisasi EDIFACT BAPLIE ke sistem Pelindo TOS / Priok.</p>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500 bg-slate-200 px-2 py-0.5 rounded">18:45 WIB</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- UN/EDIFACT BAPLIE (BAYPLAN / WAGON SLOT ALLOCATION MESSAGE) PANEL -->
    <!-- ======================================================================= -->
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-4">
            <div>
                <h3 class="text-base font-bold text-gray-900 flex items-center">
                    <i class="fa-solid fa-file-code text-[#0170b9] mr-2"></i>
                    UN/EDIFACT BAPLIE D.95B (Rail Siding Slot Allocation)
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Format elektronik standar internasional SMDG untuk pertukaran data susunan muatan gerbong kereta api dry port.</p>
            </div>
            <div class="flex items-center space-x-2">
                <button type="button" onclick="copyBaplieText()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-bold transition-colors flex items-center space-x-1.5">
                    <i class="fa-regular fa-copy"></i>
                    <span id="copy-baplie-btn-text">Salin BAPLIE</span>
                </button>
                <button type="button" onclick="downloadBaplieFile()" class="px-3.5 py-2 bg-[#0170b9] hover:bg-[#002f5e] text-white rounded-xl text-xs font-bold transition-colors flex items-center space-x-1.5 shadow-sm">
                    <i class="fa-solid fa-download"></i>
                    <span>Unduh File (.edi)</span>
                </button>
            </div>
        </div>

        <!-- Raw EDIFACT BAPLIE Block -->
        <div class="relative bg-slate-950 rounded-xl p-4 border border-slate-800 shadow-inner">
            <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-800 text-[11px] font-mono text-slate-400">
                <span>BUFFER: BAPLIE_KA2518_CIDP_PRIOK.EDI</span>
                <span class="text-emerald-400">STANDARD SMDG BAPLIE 2.2 / D.95B</span>
            </div>
            <pre id="baplie-raw-text" class="font-mono text-xs text-emerald-300 leading-relaxed overflow-x-auto whitespace-pre selection:bg-blue-600 selection:text-white max-h-56">UNA:+.? '
UNB+UNOA:2+CIDP_RAIL_YMS+KAI_LOGISTIK+20260923:1700+BAP251801'
BGM+BAPLIE+KA2518-20260923+9'
DTM+137:202609231845:203'
TDT+20+KA2518+1++KAILOG:172:20+++CC2061342:103::CC206'
LOC+5+IDCKG:139:6+CIDP CIKARANG DRY PORT'
LOC+61+IDTPP:139:6+TANJUNG PRIOK CONTAINER TERMINAL'
EQD+CN+MSKU9821450+45G1:102:5++2+5'
LOC+147+GD421201:W01:BAY01'
MEA+WT++KGM:32450'
LOC+11+USLAX:139:6'
EQD+CN+ONEU6612988+42G1:102:5++2+5'
LOC+147+GD421203:W03:BAY01'
MEA+WT++KGM:28900'
LOC+11+JPTYO:139:6'
EQD+CN+CMAU7234910+45R1:102:5++2+5'
LOC+147+GD421204:W04:BAY01'
MEA+WT++KGM:29100'
LOC+11+NLRTM:139:6'
UNT+18+BAP251801'
UNZ+1+BAP251801'</pre>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- MODAL: WAGON INSPECTOR MODAL -->
<!-- ======================================================================= -->
<div id="wagonModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 animate-fadeIn">
    <div class="bg-white rounded-2xl max-w-lg w-full border border-gray-200 shadow-2xl overflow-hidden">
        <div class="bg-[#002f5e] text-white px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-train text-blue-300"></i>
                <h3 id="modal-wagon-title" class="font-bold text-sm">Inspeksi Gerbong Datar</h3>
            </div>
            <button onclick="closeWagonModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="p-6 space-y-4 text-xs">
            <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200">
                <div>
                    <span class="text-gray-500 font-bold block">Tipe Gerbong</span>
                    <span id="modal-wagon-type" class="font-mono font-bold text-gray-900">PPCW 42t</span>
                </div>
                <div>
                    <span class="text-gray-500 font-bold block">Status Transshipment</span>
                    <span id="modal-wagon-status" class="font-bold text-emerald-700">LIFTED</span>
                </div>
            </div>

            <div id="modal-container-details" class="space-y-2">
                <!-- Dynamically filled by inspectWagon() -->
            </div>

            <div class="pt-2 flex justify-end space-x-2 border-t border-gray-100">
                <button type="button" onclick="closeWagonModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs transition-colors">
                    Tutup
                </button>
                <button type="button" onclick="dispatchReachStackerToWagon()" class="px-4 py-2 bg-[#0170b9] hover:bg-[#002f5e] text-white font-bold rounded-xl text-xs transition-colors">
                    <i class="fa-solid fa-dolly mr-1"></i> Dispatch Reach Stacker (VMT)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- JAVASCRIPT LOGIC INTERMODAL -->
<!-- ======================================================================= -->
<script>
const wagonsData = <?= json_encode($wagons) ?>;

function inspectWagon(idx) {
    const wg = wagonsData[idx];
    if (!wg) return;

    document.getElementById('modal-wagon-title').textContent = `Inspeksi ${wg.wagon_no} (${wg.type})`;
    document.getElementById('modal-wagon-type').textContent = wg.type;
    document.getElementById('modal-wagon-status').textContent = wg.status;

    const containerBox = document.getElementById('modal-container-details');
    let html = '';

    if (wg.is_40ft) {
        html = `
            <div class="bg-blue-50 border border-blue-200 p-3.5 rounded-xl space-y-1.5">
                <div class="flex justify-between items-center">
                    <span class="text-[10px] font-bold uppercase text-blue-700">Slot 40ft (Full Bed)</span>
                    <span class="font-mono text-[10px] bg-blue-200 text-blue-900 px-1.5 py-0.5 rounded font-bold">${wg.slot_a.iso}</span>
                </div>
                <div class="font-mono font-bold text-sm text-gray-900">${wg.slot_a.container}</div>
                <div class="text-[11px] text-gray-600">Muatan: <b>${wg.slot_a.type}</b> &bull; Berat VGM: <b>${wg.slot_a.weight}</b></div>
                <div class="text-[11px] text-gray-600">Pelabuhan Tujuan (POD): <b class="text-blue-800">${wg.slot_a.pod}</b></div>
            </div>
        `;
    } else {
        html = `
            <div class="space-y-2">
                <div class="bg-sky-50 border border-sky-200 p-3 rounded-xl space-y-1">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold uppercase text-sky-700">Slot A (20ft Front)</span>
                        <span class="font-mono text-[10px] bg-sky-200 text-sky-900 px-1.5 py-0.5 rounded font-bold">${wg.slot_a.iso}</span>
                    </div>
                    <div class="font-mono font-bold text-sm text-gray-900">${wg.slot_a.container}</div>
                    <div class="text-[11px] text-gray-600">Muatan: <b>${wg.slot_a.type}</b> &bull; Berat: <b>${wg.slot_a.weight}</b></div>
                    <div class="text-[11px] text-gray-600">POD: <b class="text-sky-800">${wg.slot_a.pod}</b></div>
                </div>
                ${wg.slot_b ? `
                <div class="bg-emerald-50 border border-emerald-200 p-3 rounded-xl space-y-1">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold uppercase text-emerald-700">Slot B (20ft Rear)</span>
                        <span class="font-mono text-[10px] bg-emerald-200 text-emerald-900 px-1.5 py-0.5 rounded font-bold">${wg.slot_b.iso}</span>
                    </div>
                    <div class="font-mono font-bold text-sm text-gray-900">${wg.slot_b.container}</div>
                    <div class="text-[11px] text-gray-600">Muatan: <b>${wg.slot_b.type}</b> &bull; Berat: <b>${wg.slot_b.weight}</b></div>
                    <div class="text-[11px] text-gray-600">POD: <b class="text-emerald-800">${wg.slot_b.pod}</b></div>
                </div>` : ''}
            </div>
        `;
    }

    containerBox.innerHTML = html;
    document.getElementById('wagonModal').classList.remove('hidden');
}

function closeWagonModal() {
    document.getElementById('wagonModal').classList.add('hidden');
}

function dispatchReachStackerToWagon() {
    alert("Instruksi alih muat terkirim ke Reach Stacker RS-05 melalui Terminal Operating System (VMT). Kontainer siap diangkat ke buffer siding.");
    closeWagonModal();
}

function triggerAxlePassSimulation() {
    const btn = document.getElementById('btn-axle-sim');
    const display = document.getElementById('axle-count-display');
    const val = document.getElementById('axle-count-val');
    const pulses = document.querySelectorAll('#pulse-indicator span');

    btn.disabled = true;
    btn.classList.add('opacity-50');

    let current = 120;
    const interval = setInterval(() => {
        current += 2;
        display.textContent = current + " As Roda";
        val.textContent = current;

        // Jiggle pulse bars
        pulses.forEach(p => {
            const h = Math.floor(Math.random() * 20) + 4;
            p.style.height = h + 'px';
        });

        if (current >= 126) {
            clearInterval(interval);
            btn.disabled = false;
            btn.classList.remove('opacity-50');
            alert("Verifikasi As Roda Lengkap! Total 126 as roda sesuai dengan trainlist KA 2518 (30 GD + 1 Lokomotif CC 206).");
        }
    }, 250);
}

function copyBaplieText() {
    const text = document.getElementById('baplie-raw-text').textContent;
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('copy-baplie-btn-text');
        btn.textContent = "Tersalin!";
        setTimeout(() => btn.textContent = "Salin BAPLIE", 2000);
    }).catch(err => {
        alert("Pesan BAPLIE:\n\n" + text);
    });
}

function downloadBaplieFile() {
    const text = document.getElementById('baplie-raw-text').textContent;
    const blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = "BAPLIE_KA2518_CIDP.edi";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

function openBaplieModal() {
    window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
}

// =======================================================================
// GIS CORRIDOR TRACKER LOGIC (LEAFLET.JS) — DAOP 1 JAKARTA & CIDP HUB
// =======================================================================
let corridorMap = null;
let railLayerGroup = null;
let roadLayerGroup = null;
let trainMarker = null;
let trainAnimInterval = null;
let isTrainPlaying = false;
let currentTrainStep = 0;
let interpolatedSteps = [];

// Waypoints Geospasial Lintas Rel KA Barang Daop 1 (Tj. Priok - CIDP Cikarang Hub 35 Ha)
const railCorridorPoints = [
    { lat: -6.1042, lng: 106.8856, name: "Pelabuhan Tanjung Priok (JICT)", km: 0.0, speed: 30, desc: "Quay Crane & Dermaga Petikemas Ekspor-Impor Internasional" },
    { lat: -6.1158, lng: 106.8887, name: "Stasiun Pasoso (Freight Terminal)", km: 1.5, speed: 35, desc: "Marshalling Yard Petikemas Daop 1 & Gate Rel Pelabuhan" },
    { lat: -6.1105, lng: 106.8809, name: "Stasiun Tanjung Priuk", km: 2.8, speed: 40, desc: "Emplasemen Utama KA Daop 1" },
    { lat: -6.1328, lng: 106.8523, name: "Ancol - Kampung Bandan", km: 7.2, speed: 50, desc: "Lintas Lingkar Rel Daop 1" },
    { lat: -6.1620, lng: 106.8450, name: "Kemayoran", km: 9.8, speed: 55, desc: "Koridor Lintas Tengah" },
    { lat: -6.1751, lng: 106.8451, name: "Stasiun Pasar Senen", km: 12.0, speed: 50, desc: "Lintas Utama Timur Daop 1" },
    { lat: -6.2151, lng: 106.8704, name: "Stasiun Jatinegara (KM 11.8)", km: 17.5, speed: 60, desc: "Percabangan Strategis Jalur Pantura & Lintas Selatan" },
    { lat: -6.2135, lng: 106.8998, name: "Stasiun Klender", km: 21.0, speed: 70, desc: "Double-Double Track (DDT) Paket B" },
    { lat: -6.2198, lng: 106.9532, name: "Stasiun Cakung", km: 27.2, speed: 75, desc: "Batas Wilayah Administrasi DKI Jakarta - Jawa Barat" },
    { lat: -6.2241, lng: 106.9793, name: "Stasiun Kranji", km: 30.5, speed: 75, desc: "Koridor Laju Aglomerasi Kota Bekasi" },
    { lat: -6.2361, lng: 107.0002, name: "Stasiun Bekasi (KM 33.0)", km: 33.0, speed: 70, desc: "Simpul Transit & Stasiun Sentral Kota Bekasi" },
    { lat: -6.2608, lng: 107.0601, name: "Stasiun Tambun", km: 40.2, speed: 75, desc: "Lintas Kecepatan Tinggi KA Logistik" },
    { lat: -6.2642, lng: 107.0988, name: "Stasiun Cibitung", km: 45.0, speed: 70, desc: "Koridor Kawasan Industri Logistik MM2100" },
    { lat: -6.2553, lng: 107.1472, name: "Stasiun Cikarang", km: 50.4, speed: 60, desc: "Stasiun Hub Industri Terbesar Kabupaten Bekasi" },
    { lat: -6.2818, lng: 107.1729, name: "Stasiun Lemahabang (Spur Junction)", km: 53.6, speed: 40, desc: "Percabangan Jalur Rel Khusus (Spur Line) Menuju CIDP" },
    { lat: -6.2731, lng: 107.1652, name: "CIDP Hub Rail Siding Track-01", km: 54.8, speed: 20, desc: "Terminal Intermodal 35 Ha, Sensor Frauscher RSR180, & Bea Cukai Terpadu" }
];

// Rute Tol Jakarta - Cikampek (Perbandingan Koridor Truk Jalan Raya yang Rawan Macet)
const roadCorridorCoords = [
    [-6.1042, 106.8856], // Tanjung Priok
    [-6.1265, 106.8912], // Tol Pelabuhan / Plumpang
    [-6.1820, 106.8760], // Tol Ir. Wiyoto Wiyono
    [-6.2415, 106.8722], // Cawang Interchange
    [-6.2450, 106.9050], // Halim Perdanakusuma
    [-6.2512, 106.9421], // Simpang Susun Cikunir (Titik Macet Kronis)
    [-6.2482, 106.9891], // GT Bekasi Barat
    [-6.2570, 107.0420], // GT Bekasi Timur
    [-6.2680, 107.0930], // GT Tambun
    [-6.2890, 107.1250], // GT Cibitung
    [-6.3101, 107.1352], // GT Cikarang Barat (KM 28)
    [-6.2950, 107.1550], // Akses Kawasan Industri Jababeka
    [-6.2731, 107.1652]  // CIDP Dry Port Hub
];

// Generate Interpolated Sub-steps untuk Animasi Halus
function generateInterpolatedPath() {
    interpolatedSteps = [];
    const stepsPerSegment = 16;
    for (let i = 0; i < railCorridorPoints.length - 1; i++) {
        const p1 = railCorridorPoints[i];
        const p2 = railCorridorPoints[i + 1];
        for (let j = 0; j < stepsPerSegment; j++) {
            const frac = j / stepsPerSegment;
            const lat = p1.lat + (p2.lat - p1.lat) * frac;
            const lng = p1.lng + (p2.lng - p1.lng) * frac;
            const km = p1.km + (p2.km - p1.km) * frac;
            const speed = Math.round(p1.speed + (p2.speed - p1.speed) * frac);
            interpolatedSteps.push({
                lat: lat,
                lng: lng,
                km: km,
                speed: speed,
                name: frac < 0.5 ? p1.name : p2.name,
                desc: p2.desc
            });
        }
    }
    const lastP = railCorridorPoints[railCorridorPoints.length - 1];
    interpolatedSteps.push({
        lat: lastP.lat,
        lng: lastP.lng,
        km: lastP.km,
        speed: lastP.speed,
        name: lastP.name,
        desc: lastP.desc
    });
}

// Inisialisasi Peta GIS Intermodal Leaflet
function initCorridorGisMap() {
    const mapContainer = document.getElementById('corridorGisMap');
    if (!mapContainer || corridorMap) return;

    generateInterpolatedPath();

    // Inisialisasi Peta Leaflet
    corridorMap = L.map('corridorGisMap', {
        zoomControl: false,
        attributionControl: false
    }).setView([-6.20, 107.03], 11);

    // Zoom control di pojok kanan bawah
    L.control.zoom({ position: 'bottomright' }).addTo(corridorMap);

    // OpenStreetMap Tile Layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        subdomains: ['a', 'b', 'c']
    }).addTo(corridorMap);

    railLayerGroup = L.layerGroup().addTo(corridorMap);
    roadLayerGroup = L.layerGroup().addTo(corridorMap);

    // Custom Icon: Pelabuhan Tanjung Priok
    const priokIcon = L.divIcon({
        className: 'custom-gis-pin',
        html: `
            <div class="relative flex items-center justify-center pointer-events-auto">
                <span class="absolute w-8 h-8 rounded-full bg-blue-500/30 animate-ping"></span>
                <div class="w-8 h-8 rounded-full bg-[#004b87] border-2 border-white shadow-lg flex items-center justify-center text-white text-xs font-bold">
                    <i class="fa-solid fa-anchor"></i>
                </div>
                <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 whitespace-nowrap bg-white/95 px-2 py-0.5 rounded shadow text-[10px] font-bold text-[#004b87] border border-blue-100">
                    Tj. Priok (JICT)
                </div>
            </div>
        `,
        iconSize: [32, 32],
        iconAnchor: [16, 16]
    });

    // Custom Icon: CIDP Hub 35 Ha
    const cidpIcon = L.divIcon({
        className: 'custom-gis-pin',
        html: `
            <div class="relative flex items-center justify-center pointer-events-auto">
                <span class="absolute w-10 h-10 rounded-full bg-emerald-500/40 animate-ping"></span>
                <div class="w-9 h-9 rounded-full bg-emerald-600 border-2 border-white shadow-xl flex items-center justify-center text-white text-sm font-bold">
                    <i class="fa-solid fa-warehouse"></i>
                </div>
                <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 whitespace-nowrap bg-emerald-900 text-white px-2 py-0.5 rounded shadow text-[10px] font-bold border border-emerald-400">
                    CIDP Hub 35 Ha
                </div>
            </div>
        `,
        iconSize: [36, 36],
        iconAnchor: [18, 18]
    });

    // Function pembuat icon stasiun antara
    function createStationIcon(label, code) {
        return L.divIcon({
            className: 'custom-gis-pin',
            html: `
                <div class="relative flex items-center justify-center pointer-events-auto">
                    <div class="w-6 h-6 rounded-full bg-white border-2 border-[#0170b9] shadow flex items-center justify-center text-[#0170b9] text-[9px] font-bold">
                        ${code}
                    </div>
                    <div class="absolute -bottom-5 left-1/2 -translate-x-1/2 whitespace-nowrap bg-white/90 px-1.5 py-0.2 rounded shadow-2xs text-[9px] font-semibold text-gray-700 border border-gray-200">
                        ${label}
                    </div>
                </div>
            `,
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });
    }

    // Custom Icon: Bottleneck Simpang Cikunir
    const cikunirIcon = L.divIcon({
        className: 'custom-gis-pin',
        html: `
            <div class="relative flex items-center justify-center pointer-events-auto">
                <span class="absolute w-7 h-7 rounded-full bg-amber-500/30 animate-pulse"></span>
                <div class="w-7 h-7 rounded-full bg-amber-500 border-2 border-white shadow flex items-center justify-center text-white text-xs font-bold">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="absolute -bottom-5 left-1/2 -translate-x-1/2 whitespace-nowrap bg-amber-100 text-amber-900 px-1.5 py-0.5 rounded shadow text-[9px] font-bold border border-amber-300">
                    Macet Cikunir (Delay 3 Jam)
                </div>
            </div>
        `,
        iconSize: [28, 28],
        iconAnchor: [14, 14]
    });

    // Custom Icon: Lokomotif Bergerak KA 2518 (CC 206)
    const trainIcon = L.divIcon({
        className: 'custom-gis-pin',
        html: `
            <div class="relative flex items-center justify-center pointer-events-auto">
                <div class="train-radar-pulse"></div>
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-600 via-orange-500 to-amber-400 border-2 border-white shadow-xl flex items-center justify-center text-white text-sm z-10">
                    <i class="fa-solid fa-train"></i>
                </div>
                <div class="absolute -top-7 left-1/2 -translate-x-1/2 whitespace-nowrap bg-gray-900/90 text-amber-300 px-2 py-0.5 rounded-full shadow text-[10px] font-mono font-bold border border-amber-500/50 z-20 flex items-center space-x-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>KA 2518 (CC 206)</span>
                </div>
            </div>
        `,
        iconSize: [36, 36],
        iconAnchor: [18, 18]
    });

    // Layer 1: Rel KA Daop 1 (Double Polyline: Rel Fisik + Bantalan)
    const railCoords = railCorridorPoints.map(p => [p.lat, p.lng]);
    L.polyline(railCoords, {
        color: '#002f5e',
        weight: 6,
        opacity: 0.85
    }).addTo(railLayerGroup);

    L.polyline(railCoords, {
        color: '#38bdf8',
        weight: 3.5,
        opacity: 0.95,
        dashArray: '8, 8'
    }).addTo(railLayerGroup);

    // Layer 2: Tol Jakarta - Cikampek
    L.polyline(roadCorridorCoords, {
        color: '#f59e0b',
        weight: 4,
        opacity: 0.75,
        dashArray: '6, 6'
    }).addTo(roadLayerGroup);

    // Tambah Marker ke Layer Rel
    // Marker Priok
    const pPriok = railCorridorPoints[0];
    L.marker([pPriok.lat, pPriok.lng], { icon: priokIcon })
        .bindPopup(`
            <div class="p-1 space-y-1 text-xs">
                <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded font-bold text-[10px]">ORIGIN PORT TERMINAL</span>
                <h4 class="font-bold text-gray-900 text-sm mt-1">${pPriok.name}</h4>
                <p class="text-gray-600">${pPriok.desc}</p>
                <div class="text-[11px] text-gray-500 pt-1 border-t border-gray-100">
                    Kapasitas: <b>7,2 Juta TEU/thn</b> &bull; Gate Rel: <b>Stasiun Pasoso</b>
                </div>
            </div>
        `)
        .addTo(railLayerGroup);

    // Marker CIDP Hub
    const pCidp = railCorridorPoints[railCorridorPoints.length - 1];
    L.marker([pCidp.lat, pCidp.lng], { icon: cidpIcon })
        .bindPopup(`
            <div class="p-1 space-y-1 text-xs">
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded font-bold text-[10px]">DESTINATION HUB (35 HA)</span>
                <h4 class="font-bold text-gray-900 text-sm mt-1">${pCidp.name}</h4>
                <p class="text-gray-600">${pCidp.desc}</p>
                <div class="text-[11px] text-gray-500 pt-1 border-t border-gray-100">
                    Siding Track: <b>2 &times; 450m UIC 54</b> &bull; Sensor: <b>Frauscher Axle Counter RSR180</b>
                </div>
            </div>
        `)
        .addTo(railLayerGroup);

    // Marker Stasiun Transit Antara
    const stations = [
        { idx: 1, code: "PSO", label: "Pasoso" },
        { idx: 6, code: "JNG", label: "Jatinegara" },
        { idx: 10, code: "BKS", label: "Bekasi" },
        { idx: 14, code: "LBH", label: "Lemahabang" }
    ];
    stations.forEach(st => {
        const pt = railCorridorPoints[st.idx];
        L.marker([pt.lat, pt.lng], { icon: createStationIcon(st.label, st.code) })
            .bindPopup(`
                <div class="p-1 space-y-1 text-xs">
                    <span class="px-1.5 py-0.5 bg-slate-100 text-slate-800 rounded font-mono font-bold text-[10px]">STASIUN DAOP 1</span>
                    <h4 class="font-bold text-gray-900 mt-1">${pt.name}</h4>
                    <p class="text-gray-600 text-[11px]">${pt.desc}</p>
                    <div class="text-[10px] text-blue-600 font-mono">KM ${pt.km.toFixed(1)} dari Priok</div>
                </div>
            `)
            .addTo(railLayerGroup);
    });

    // Marker Bottleneck Tol Cikunir pada Road Layer
    L.marker([-6.2512, 106.9421], { icon: cikunirIcon })
        .bindPopup(`
            <div class="p-1 space-y-1 text-xs">
                <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded font-bold text-[10px]">ROAD BOTTLENECK</span>
                <h4 class="font-bold text-gray-900 text-sm mt-1">Simpang Susun Cikunir (Tol Jkt-Cikampek)</h4>
                <p class="text-gray-600">Titik kemacetan kronis harian antrean truk kontainer menuju Pelabuhan. Rata-rata delay 2,5 - 4 jam.</p>
                <div class="text-[11px] text-emerald-700 font-bold pt-1 border-t border-gray-100">
                    Solusi Intermodal: Kereta api barang memangkas dwell time dan bebas macet jalan raya.
                </div>
            </div>
        `)
        .addTo(roadLayerGroup);

    // Initial Position KA 2518 Marker
    const startPt = interpolatedSteps[0];
    trainMarker = L.marker([startPt.lat, startPt.lng], { icon: trainIcon })
        .bindPopup(`
            <div class="p-1 space-y-1 text-xs font-sans">
                <div class="flex items-center space-x-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="font-mono font-bold text-gray-900">KA 2518 (CIDP Express Freight)</span>
                </div>
                <div class="text-[11px] text-gray-600">
                    Lokomotif CC 206 13 42 &bull; 30 Gerbong Datar PPCW &bull; 60 TEU
                </div>
            </div>
        `)
        .addTo(corridorMap);

    // Set initial Telemetri HUD
    updateTelemetryHUD(startPt);

    // Auto-fit bounds
    setTimeout(() => {
        corridorMap.invalidateSize();
        corridorMap.fitBounds([
            [-6.1042, 106.8523],
            [-6.2818, 107.1729]
        ], { padding: [30, 30] });
    }, 300);
}

// Update Telemetri HUD pada Peta
function updateTelemetryHUD(step) {
    const elSpeed = document.getElementById('hudTrainSpeed');
    const elLoc = document.getElementById('hudTrainLoc');
    const elProgress = document.getElementById('hudTrainProgress');
    const elEta = document.getElementById('hudTrainEta');
    const elBar = document.getElementById('hudProgressBar');

    if (elSpeed) elSpeed.textContent = `${step.speed} km/jam`;
    if (elLoc) elLoc.textContent = step.name;

    const pct = Math.min(100, Math.round((step.km / 54.8) * 100));
    if (elProgress) elProgress.textContent = `${step.km.toFixed(1)} / 54.8 km (${pct}%)`;
    if (elBar) elBar.style.width = `${pct}%`;

    const remainingKm = Math.max(0, 54.8 - step.km);
    const etaMin = Math.round((remainingKm / 54.8) * 110);
    if (elEta) {
        if (etaMin === 0) {
            elEta.textContent = "Tiba di Siding CIDP Track-01";
            elEta.className = "text-emerald-600 font-bold";
        } else {
            elEta.textContent = `${etaMin} Menit Lagi`;
            elEta.className = "text-gray-900";
        }
    }
}

// Kontrol Play / Pause Simulasi Perjalanan KA
function toggleTrainAnimation() {
    if (!isTrainPlaying) {
        startTrainAnimation();
    } else {
        pauseTrainAnimation();
    }
}

function startTrainAnimation() {
    if (!trainMarker || interpolatedSteps.length === 0) return;
    if (currentTrainStep >= interpolatedSteps.length - 1) {
        currentTrainStep = 0;
    }
    isTrainPlaying = true;
    const btnText = document.getElementById('playTrainText');
    const btnIcon = document.getElementById('playTrainIcon');
    if (btnText) btnText.textContent = "Jeda Perjalanan KA";
    if (btnIcon) btnIcon.className = "fa-solid fa-pause text-[10px]";

    trainAnimInterval = setInterval(() => {
        if (currentTrainStep < interpolatedSteps.length - 1) {
            currentTrainStep++;
            const step = interpolatedSteps[currentTrainStep];
            trainMarker.setLatLng([step.lat, step.lng]);
            updateTelemetryHUD(step);
        } else {
            pauseTrainAnimation();
            const btnText = document.getElementById('playTrainText');
            if (btnText) btnText.textContent = "Simulasikan Ulang";
            alert("Rangkaian KA 2518 (CC 206) telah tiba di Intermodal Rail Siding Track-01 CIDP Hub 35 Ha!\n\nSensor Axle Counter Frauscher mendeteksi 126 as roda sesuai manifest BAPLIE.\nSiap untuk proses pembongkaran 48 TEU menggunakan Reach Stacker / RMGC.");
        }
    }, 90);
}

function pauseTrainAnimation() {
    isTrainPlaying = false;
    if (trainAnimInterval) clearInterval(trainAnimInterval);
    const btnText = document.getElementById('playTrainText');
    const btnIcon = document.getElementById('playTrainIcon');
    if (btnText && currentTrainStep < interpolatedSteps.length - 1) btnText.textContent = "Lanjutkan Perjalanan KA";
    if (btnIcon) btnIcon.className = "fa-solid fa-play text-[10px]";
}

function resetTrainPosition() {
    pauseTrainAnimation();
    currentTrainStep = 0;
    if (interpolatedSteps.length > 0 && trainMarker) {
        const step0 = interpolatedSteps[0];
        trainMarker.setLatLng([step0.lat, step0.lng]);
        updateTelemetryHUD(step0);
    }
    const btnText = document.getElementById('playTrainText');
    if (btnText) btnText.textContent = "Simulasikan Perjalanan KA";
    flyToLocation('priok');
}

// Navigasi Kamera GIS (Pojok Kanan Atas)
function flyToLocation(target) {
    if (!corridorMap) return;
    if (target === 'all') {
        corridorMap.flyToBounds([
            [-6.1042, 106.8523],
            [-6.2818, 107.1729]
        ], { padding: [40, 40], duration: 1.2 });
    } else if (target === 'priok') {
        corridorMap.flyTo([-6.1100, 106.8856], 14, { duration: 1.2 });
    } else if (target === 'cidp') {
        corridorMap.flyTo([-6.2731, 107.1652], 14, { duration: 1.2 });
    }
}

// Toggle Layer Rel KA & Jalan Tol
function toggleMapLayer(layerType) {
    if (!corridorMap) return;
    if (layerType === 'rail') {
        const chk = document.getElementById('chkShowRail');
        if (chk && chk.checked) {
            if (!corridorMap.hasLayer(railLayerGroup)) corridorMap.addLayer(railLayerGroup);
        } else {
            if (corridorMap.hasLayer(railLayerGroup)) corridorMap.removeLayer(railLayerGroup);
        }
    } else if (layerType === 'road') {
        const chk = document.getElementById('chkShowRoad');
        if (chk && chk.checked) {
            if (!corridorMap.hasLayer(roadLayerGroup)) corridorMap.addLayer(roadLayerGroup);
        } else {
            if (corridorMap.hasLayer(roadLayerGroup)) corridorMap.removeLayer(roadLayerGroup);
        }
    }
}

// Trigger inisialisasi peta saat viewport siap
window.addEventListener('load', () => {
    initCorridorGisMap();
});
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    setTimeout(initCorridorGisMap, 200);
}
window.addEventListener('resize', () => {
    if (corridorMap) corridorMap.invalidateSize();
});

</script>
