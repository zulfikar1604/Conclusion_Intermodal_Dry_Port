<?php
// =============================================================================
// MODUL: INTERMODAL RAIL SIDING & INTEGRASI MODA LOGISTIK KERETA API
// File: pages/intermodal.php
// CIDP Yard Management System — PT Multi Terminal Indonesia / ITL Trisakti
// PIC : Juan Gamaliel & Afriansayah Ayubi (Data Integration & Software ERP Specialist)
// Standar: Frauscher Axle Counter RSR180 + UN/EDIFACT BAPLIE D.95B Standard SMDG
// =============================================================================

$train_info = [
    'train_id'      => 'KA 2518 (CIDP Express Freight)',
    'route'         => 'CIDP Dry Port Hub (Cikarang) <——> Tanjung Priok Port (JICT / TPK Koja)',
    'locomotive'    => 'CC 206 13 42 (GE CM20EMP - 2.250 HP)',
    'wagons_total'  => 30,
    'teu_capacity'  => 60,
    'teu_loaded'    => 48,
    'load_factor'   => '80.0%',
    'est_departure' => '18:45 WIB',
    'status'        => 'TRANSSHIPMENT IN PROGRESS (BONGKAR-MUAT)',
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

                <!-- Simulation Trigger Controls -->
                <div class="space-y-2">
                    <button type="button" onclick="triggerAxlePassSimulation()" id="btn-axle-sim" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition-colors flex items-center justify-center space-x-2 shadow-xs">
                        <i class="fa-solid fa-play"></i>
                        <span>Simulasikan Gerbong Melintas (Frauscher Pulse Count)</span>
                    </button>
                    <p class="text-[11px] text-gray-400 text-center">
                        Memvalidasi bahwa jumlah roda masuk persis sama dengan manifest trainlist KAI Logistik (Zero Discrepancy Rule).
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
</script>
