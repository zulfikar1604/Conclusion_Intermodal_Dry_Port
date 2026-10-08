<?php
// =============================================================================
// MODUL: INTERMODAL RAIL SIDING & INTEGRASI MODA LOGISTIK KERETA API
// File: pages/intermodal.php
// CIDP Yard Management System — PT Multi Terminal Indonesia / ITL Trisakti
// PIC : Juan Gamaliel & Afriansayah Ayubi (Data Integration & Software ERP Specialist)
// Standar: Frauscher Axle Counter RSR180 + UN/EDIFACT BAPLIE D.95B Standard SMDG
// =============================================================================
require_once __DIR__ . '/../connection.php';

$trains_list = [];
try {
    $stmtT = $pdo->query("SELECT * FROM trains ORDER BY id ASC");
    $trains_list = $stmtT->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// Cari Rangkaian Aktif: Inbound (Priok -> CIDP) & Outbound (CIDP -> Priok)
$train_inbound = null;
$train_outbound = null;
foreach ($trains_list as $t) {
    if (strtoupper($t['direction'] ?? '') === 'INBOUND' && !$train_inbound) {
        $train_inbound = $t;
    } elseif (strtoupper($t['direction'] ?? '') === 'OUTBOUND' && !$train_outbound) {
        $train_outbound = $t;
    }
}

// Fallback jika belum tersinkronisasi database
if (!$train_inbound) {
    $train_inbound = [
        'train_code' => 'KA 2518',
        'origin' => 'Pelabuhan Tanjung Priok (Pasoso)',
        'destination' => 'CIDP Cikarang Hub',
        'direction' => 'INBOUND',
        'total_wagons' => 30,
        'loaded_wagons' => 24,
        'status' => 'unloading',
        'estimated_arrival' => '10:05 WIB',
        'estimated_departure' => '08:15 WIB'
    ];
}
if (!$train_outbound) {
    $train_outbound = [
        'train_code' => 'KA 2519',
        'origin' => 'CIDP Cikarang Hub',
        'destination' => 'Pelabuhan Tanjung Priok (Pasoso/JICT)',
        'direction' => 'OUTBOUND',
        'total_wagons' => 30,
        'loaded_wagons' => 26,
        'status' => 'loading',
        'estimated_arrival' => '15:20 WIB',
        'estimated_departure' => '13:30 WIB'
    ];
}

// Perhitungan Agregat 2-Arah Siding
$total_active_wagons = (int)($train_inbound['total_wagons'] ?? 30) + (int)($train_outbound['total_wagons'] ?? 30);
$total_active_teu_cap = $total_active_wagons * 2; // 120 TEU
$total_loaded_wagons = (int)($train_inbound['loaded_wagons'] ?? 24) + (int)($train_outbound['loaded_wagons'] ?? 26);
$total_loaded_teu = $total_loaded_wagons * 2; // 100 TEU
$combined_load_factor = $total_active_teu_cap > 0 ? round(($total_loaded_teu / $total_active_teu_cap) * 100, 1) : 83.3;

$siding_tracks = [
    [
        'id'          => 'TRACK-01 (Sepur Utara — Bongkar Impor)',
        'track_code'  => 'TRK-01',
        'length'      => '450 Meter',
        'rail_type'   => 'UIC 54 (Bantalan Beton R.54)',
        'train_code'  => $train_inbound['train_code'],
        'direction'   => 'INBOUND (Priok &rarr; CIDP)',
        'occupancy'   => 'SEDANG BONGKAR (' . $train_inbound['train_code'] . ')',
        'status_color'=> 'bg-emerald-100 text-emerald-800 border-emerald-300',
        'dot_color'   => 'bg-emerald-500 animate-pulse',
        'axles_count' => 126,
        'loaded_teu'  => ((int)$train_inbound['loaded_wagons'] * 2) . ' / ' . ((int)$train_inbound['total_wagons'] * 2) . ' TEU',
        'cargo_type'  => 'Laden Impor & Relokasi Pabean PLP ex-Vessel',
        'equip'       => 'Reach Stacker RS-05 & RMGC Rail Crane 01'
    ],
    [
        'id'          => 'TRACK-02 (Sepur Selatan — Muat Ekspor)',
        'track_code'  => 'TRK-02',
        'length'      => '450 Meter',
        'rail_type'   => 'UIC 54 (Bantalan Beton R.54)',
        'train_code'  => $train_outbound['train_code'],
        'direction'   => 'OUTBOUND (CIDP &rarr; Priok)',
        'occupancy'   => 'SEDANG MUAT (' . $train_outbound['train_code'] . ')',
        'status_color'=> 'bg-blue-100 text-blue-800 border-blue-300',
        'dot_color'   => 'bg-blue-500 animate-pulse',
        'axles_count' => 126,
        'loaded_teu'  => ((int)$train_outbound['loaded_wagons'] * 2) . ' / ' . ((int)$train_outbound['total_wagons'] * 2) . ' TEU',
        'cargo_type'  => 'Laden Ekspor (VGM SOLAS + NPE) Siap Muat Kapal',
        'equip'       => 'Reach Stacker RS-02 & RMGC Rail Crane 02'
    ]
];

// Master Timetable: Siklus 6 Perjalanan KA Shuttle Per Hari (Tanjung Priok <-> CIDP)
$shuttle_timetable = [
    [
        'trip_no'     => 'TRIP #1',
        'train_code'  => 'KA 2517',
        'direction'   => 'INBOUND',
        'origin'      => 'Stasiun Pasoso (Priok)',
        'dest'        => 'CIDP Hub Cikarang',
        'dep'         => '03:30 WIB',
        'arr'         => '05:20 WIB',
        'teu'         => '60 TEU',
        'loco'        => 'CC 206 13 18',
        'cargo'       => 'Impor Raw Material Jababeka',
        'status'      => 'SELESAI (DISCHARGED)',
        'badge'       => 'bg-slate-100 text-slate-700 border-slate-300'
    ],
    [
        'trip_no'     => 'TRIP #2',
        'train_code'  => 'KA 2518',
        'direction'   => 'INBOUND',
        'origin'      => 'Stasiun Pasoso (Priok)',
        'dest'        => 'CIDP Hub (Track-01)',
        'dep'         => '08:15 WIB',
        'arr'         => '10:05 WIB',
        'teu'         => '48 TEU',
        'loco'        => 'CC 206 13 42',
        'cargo'       => 'Impor CKD, Resin & Farmasi PLP',
        'status'      => 'BONGKAR (TRK-01)',
        'badge'       => 'bg-emerald-100 text-emerald-800 border-emerald-300 font-bold animate-pulse'
    ],
    [
        'trip_no'     => 'TRIP #3',
        'train_code'  => 'KA 2519',
        'direction'   => 'OUTBOUND',
        'origin'      => 'CIDP Hub (Track-02)',
        'dest'        => 'Stasiun Pasoso (Priok)',
        'dep'         => '13:30 WIB',
        'arr'         => '15:20 WIB',
        'teu'         => '52 TEU',
        'loco'        => 'CC 206 13 55',
        'cargo'       => 'Ekspor Manufaktur & Ban Kendaraan (VGM)',
        'status'      => 'MUAT (TRK-02)',
        'badge'       => 'bg-blue-100 text-blue-800 border-blue-300 font-bold animate-pulse'
    ],
    [
        'trip_no'     => 'TRIP #4',
        'train_code'  => 'KA 2520',
        'direction'   => 'INBOUND',
        'origin'      => 'Stasiun Pasoso (Priok)',
        'dest'        => 'CIDP Hub Cikarang',
        'dep'         => '16:00 WIB',
        'arr'         => '17:50 WIB',
        'teu'         => '60 TEU',
        'loco'        => 'CC 206 13 29',
        'cargo'       => 'Impor Mesin & Empty Depo Reposition',
        'status'      => 'PERJALANAN (EN ROUTE)',
        'badge'       => 'bg-amber-100 text-amber-800 border-amber-300 font-bold'
    ],
    [
        'trip_no'     => 'TRIP #5',
        'train_code'  => 'KA 2521',
        'direction'   => 'OUTBOUND',
        'origin'      => 'CIDP Hub Cikarang',
        'dest'        => 'Stasiun Pasoso (Priok)',
        'dep'         => '20:15 WIB',
        'arr'         => '22:05 WIB',
        'teu'         => '56 TEU',
        'loco'        => 'CC 206 13 42',
        'cargo'       => 'Ekspor Tekstil, Sepatu & Komponen Elektronik',
        'status'      => 'TERJADWAL',
        'badge'       => 'bg-indigo-50 text-indigo-700 border-indigo-200'
    ],
    [
        'trip_no'     => 'TRIP #6',
        'train_code'  => 'KA 2522',
        'direction'   => 'OUTBOUND',
        'origin'      => 'CIDP Hub Cikarang',
        'dest'        => 'Stasiun Pasoso (Priok)',
        'dep'         => '00:30 WIB',
        'arr'         => '02:20 WIB',
        'teu'         => '50 TEU',
        'loco'        => 'CC 206 13 55',
        'cargo'       => 'Ekspor Fast Freight Direct Vessel Koja',
        'status'      => 'TERJADWAL',
        'badge'       => 'bg-slate-100 text-slate-600 border-slate-200'
    ]
];

// Gerbong Datar Track-01: INBOUND IMPOR (Tanjung Priok -> CIDP Hub)
$wagons_inbound = [
    [
        'wagon_no' => 'GD 42 12 01',
        'type'     => 'PPCW 42t',
        'direction'=> 'INBOUND',
        'slot_a'   => ['container' => 'MSKU 982145-0', 'iso' => '45G1', 'type' => 'Laden Impor (Raw Material)', 'weight' => '31.450 kg', 'pod' => 'IDCKG (CIDP Cikarang)', 'bg' => 'bg-sky-600', 'shipper' => 'Maersk Line / PT Samsung Electronics'],
        'slot_b'   => null,
        'is_40ft'  => true,
        'status'   => 'LIFTED TO BUFFER',
        'status_bg'=> 'bg-amber-100 text-amber-800'
    ],
    [
        'wagon_no' => 'GD 42 12 02',
        'type'     => 'PPCW 42t',
        'direction'=> 'INBOUND',
        'slot_a'   => ['container' => 'TCKU 829104-8', 'iso' => '22G1', 'type' => 'Laden Impor (Chemical Resin)', 'weight' => '21.500 kg', 'pod' => 'IDCKG (CIDP Cikarang)', 'bg' => 'bg-sky-600', 'shipper' => 'CNC Line / PT Chandra Asri'],
        'slot_b'   => ['container' => 'EMCU 551820-0', 'iso' => '22G1', 'type' => 'Laden Impor (CKD Automotive)', 'weight' => '19.800 kg', 'pod' => 'IDCKG (CIDP Cikarang)', 'bg' => 'bg-blue-600', 'shipper' => 'Evergreen / PT Astra Daihatsu'],
        'is_40ft'  => false,
        'status'   => 'ON WAGON (READY LIFT)',
        'status_bg'=> 'bg-blue-100 text-blue-800'
    ],
    [
        'wagon_no' => 'GD 42 12 03',
        'type'     => 'PPCW 42t',
        'direction'=> 'INBOUND',
        'slot_a'   => ['container' => 'ONEU 661298-8', 'iso' => '42G1', 'type' => 'Laden Impor (Machinery)', 'weight' => '28.900 kg', 'pod' => 'IDCKG (CIDP Cikarang)', 'bg' => 'bg-pink-600', 'shipper' => 'Ocean Network Express / PT Epson'],
        'slot_b'   => null,
        'is_40ft'  => true,
        'status'   => 'ON WAGON (READY LIFT)',
        'status_bg'=> 'bg-blue-100 text-blue-800'
    ],
    [
        'wagon_no' => 'GD 42 12 04',
        'type'     => 'PPCW 42t',
        'direction'=> 'INBOUND',
        'slot_a'   => ['container' => 'CMAU 723491-0', 'iso' => '45R1', 'type' => 'Reefer Impor (Pharma/Cold)', 'weight' => '29.100 kg', 'pod' => 'IDCKG (CIDP Cikarang)', 'bg' => 'bg-cyan-600', 'shipper' => 'CMA CGM / PT Kalbe Farma'],
        'slot_b'   => null,
        'is_40ft'  => true,
        'status'   => 'CROSS-DOCK TO TRUCK',
        'status_bg'=> 'bg-emerald-100 text-emerald-800'
    ],
    [
        'wagon_no' => 'GD 42 12 05',
        'type'     => 'PPCW 42t',
        'direction'=> 'INBOUND',
        'slot_a'   => ['container' => 'SUDU 310928-4', 'iso' => '22G1', 'type' => 'Empty Box Reposition', 'weight' => '2.250 kg', 'pod' => 'IDCKG (CIDP Inland Depot)', 'bg' => 'bg-slate-500', 'shipper' => 'Hamburg Süd / Inland Stock'],
        'slot_b'   => ['container' => 'KKFU 192801-6', 'iso' => '22G1', 'type' => 'Empty Box Reposition', 'weight' => '2.300 kg', 'pod' => 'IDCKG (CIDP Inland Depot)', 'bg' => 'bg-slate-500', 'shipper' => 'K-Line / Inland Stock'],
        'is_40ft'  => false,
        'status'   => 'ON WAGON',
        'status_bg'=> 'bg-slate-100 text-slate-800'
    ],
    [
        'wagon_no' => 'GD 42 12 06',
        'type'     => 'PPCW 42t',
        'direction'=> 'INBOUND',
        'slot_a'   => ['container' => 'COSU 601928-3', 'iso' => '45G1', 'type' => 'Laden Impor (Solar Panel Parts)', 'weight' => '31.200 kg', 'pod' => 'IDCKG (CIDP Cikarang)', 'bg' => 'bg-indigo-600', 'shipper' => 'COSCO Shipping / PT Trina Solar'],
        'slot_b'   => null,
        'is_40ft'  => true,
        'status'   => 'ON WAGON',
        'status_bg'=> 'bg-blue-100 text-blue-800'
    ]
];

// Gerbong Datar Track-02: OUTBOUND EKSPOR (CIDP Hub -> Tanjung Priok)
$wagons_outbound = [
    [
        'wagon_no' => 'GD 42 14 11',
        'type'     => 'PPCW 42t',
        'direction'=> 'OUTBOUND',
        'slot_a'   => ['container' => 'SEGU 198273-1', 'iso' => '45G1', 'type' => 'Laden Ekspor (Footwear Nike)', 'weight' => '26.850 kg', 'pod' => 'USLAX (Los Angeles, USA)', 'bg' => 'bg-emerald-600', 'shipper' => 'PT Pou Yuen Indonesia (VGM Verified)'],
        'slot_b'   => null,
        'is_40ft'  => true,
        'status'   => 'LOADED & LASHED',
        'status_bg'=> 'bg-emerald-100 text-emerald-800'
    ],
    [
        'wagon_no' => 'GD 42 14 12',
        'type'     => 'PPCW 42t',
        'direction'=> 'OUTBOUND',
        'slot_a'   => ['container' => 'TEMU 491028-5', 'iso' => '22G1', 'type' => 'Laden Ekspor (Auto Wire Harness)', 'weight' => '18.400 kg', 'pod' => 'JPTYO (Tokyo, Japan)', 'bg' => 'bg-teal-600', 'shipper' => 'PT Denso Indonesia (NPE Cleared)'],
        'slot_b'   => ['container' => 'MEDU 773190-2', 'iso' => '22G1', 'type' => 'Laden Ekspor (Ceramic Tiles)', 'weight' => '22.100 kg', 'pod' => 'AUSYD (Sydney, Australia)', 'bg' => 'bg-indigo-600', 'shipper' => 'PT Mulia Ceramics (VGM Verified)'],
        'is_40ft'  => false,
        'status'   => 'LOADED & LASHED',
        'status_bg'=> 'bg-emerald-100 text-emerald-800'
    ],
    [
        'wagon_no' => 'GD 42 14 13',
        'type'     => 'PPCW 42t',
        'direction'=> 'OUTBOUND',
        'slot_a'   => ['container' => 'HASU 882019-4', 'iso' => '45R1', 'type' => 'Reefer Ekspor (Processed Tuna)', 'weight' => '27.900 kg', 'pod' => 'NLRTM (Rotterdam, NL)', 'bg' => 'bg-cyan-600', 'shipper' => 'PT Aneka Tuna (Plugged Active)'],
        'slot_b'   => null,
        'is_40ft'  => true,
        'status'   => 'BEING LOADED (CRANE)',
        'status_bg'=> 'bg-blue-100 text-blue-800 animate-pulse'
    ],
    [
        'wagon_no' => 'GD 42 14 14',
        'type'     => 'PPCW 42t',
        'direction'=> 'OUTBOUND',
        'slot_a'   => ['container' => 'TLLU 552910-3', 'iso' => '45G1', 'type' => 'Laden Ekspor (Tires Gajah Tunggal)', 'weight' => '29.300 kg', 'pod' => 'SGSIN (Singapore Hub)', 'bg' => 'bg-blue-700', 'shipper' => 'PT Gajah Tunggal Tbk'],
        'slot_b'   => null,
        'is_40ft'  => true,
        'status'   => 'BEING LOADED (CRANE)',
        'status_bg'=> 'bg-blue-100 text-blue-800 animate-pulse'
    ],
    [
        'wagon_no' => 'GD 42 14 15',
        'type'     => 'PPCW 42t',
        'direction'=> 'OUTBOUND',
        'slot_a'   => ['container' => 'FSCU 662810-7', 'iso' => '22G1', 'type' => 'Empty Surplus Reposition', 'weight' => '2.200 kg', 'pod' => 'IDTPP (Priok Ocean Depot)', 'bg' => 'bg-slate-500', 'shipper' => 'Hapag-Lloyd (Evacuation)'],
        'slot_b'   => ['container' => 'NYKU 391820-1', 'iso' => '22G1', 'type' => 'Empty Surplus Reposition', 'weight' => '2.220 kg', 'pod' => 'IDTPP (Priok Ocean Depot)', 'bg' => 'bg-slate-500', 'shipper' => 'ONE Network (Evacuation)'],
        'is_40ft'  => false,
        'status'   => 'WAITING CRANE',
        'status_bg'=> 'bg-slate-100 text-slate-700'
    ],
    [
        'wagon_no' => 'GD 42 14 16',
        'type'     => 'PPCW 42t',
        'direction'=> 'OUTBOUND',
        'slot_a'   => ['container' => 'MSKU 110928-8', 'iso' => '45G1', 'type' => 'Laden Ekspor (Automotive CKD Export)', 'weight' => '30.150 kg', 'pod' => 'THBKK (Bangkok, Thailand)', 'bg' => 'bg-emerald-600', 'shipper' => 'PT Toyota Motor Mfg (VGM)'],
        'slot_b'   => null,
        'is_40ft'  => true,
        'status'   => 'WAITING CRANE',
        'status_bg'=> 'bg-slate-100 text-slate-700'
    ]
];

// Active wagons default to Track-01 (Inbound)
$wagons = $wagons_inbound;
?>

<!-- Open Source Leaflet.js GIS Engine (Lokal & Offline Ready) -->
<link rel="stylesheet" href="assets/vendor/leaflet.css" />
<script src="assets/vendor/leaflet.js"></script>

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
    <!-- Header Modul: Clean & Minimal -->
    <div class="bg-white rounded-xl px-4 py-3 shadow-2xs border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#002f5e] via-[#0170b9] to-orange-500 text-white flex items-center justify-center text-lg shadow-xs flex-shrink-0">
                <i class="fa-solid fa-train-subway"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-base font-bold text-gray-900">Intermodal Rail Siding &amp; KA Logistik 2-Arah</h1>
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-[10px] font-semibold flex items-center">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>Track Siding Aktif
                    </span>
                    <span class="px-2 py-0.5 bg-orange-50 text-orange-700 border border-orange-200 rounded-full text-[10px] font-semibold">
                        Dual Track 2 &times; 450M
                    </span>
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-full text-[10px] font-semibold">
                        Shuttle Pasoso &harr; CIDP
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Key Metrics Bar (Bidirectional Dry Port Model) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Metric 1: Dual Active Trains -->
        <div class="bg-white rounded-xl p-4 sm:p-5 border border-gray-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500">Rangkaian KA 2-Arah Aktif</span>
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-[#0170b9] flex items-center justify-center text-xs">
                    <i class="fa-solid fa-arrows-left-right"></i>
                </span>
            </div>
            <div class="mt-2.5">
                <div class="text-sm font-bold text-gray-900 truncate flex items-center space-x-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span><?= $train_inbound['train_code'] ?> (Inbound)</span>
                </div>
                <div class="text-sm font-bold text-blue-700 truncate flex items-center space-x-1 mt-0.5">
                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                    <span><?= $train_outbound['train_code'] ?> (Outbound)</span>
                </div>
            </div>
            <p class="text-[11px] text-gray-400 mt-2">Sepur Utara: Bongkar &bull; Sepur Selatan: Muat</p>
        </div>

        <!-- Metric 2: Kapasitas Angkut TEU 2-Arah -->
        <div class="bg-white rounded-xl p-4 sm:p-5 border border-gray-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500">Total Muatan Siding 2-Arah</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </span>
            </div>
            <div class="mt-2.5 flex items-baseline space-x-2">
                <span class="text-2xl font-bold text-gray-900"><?= $total_loaded_teu ?> / <?= $total_active_teu_cap ?> TEU</span>
                <span class="text-xs text-emerald-700 font-bold"><?= $combined_load_factor ?>%</span>
            </div>
            <div class="w-full h-1.5 bg-gray-100 rounded-full mt-2.5 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-emerald-500 to-blue-500" style="width: <?= min(100, $combined_load_factor) ?>%;"></div>
            </div>
            <p class="text-[11px] text-gray-400 mt-2"><?= $total_active_wagons ?> Gerbong Datar (PPCW 42t) di Siding</p>
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
                <span id="axle-count-display" class="text-2xl font-bold text-gray-900 font-mono">252 As Roda</span>
                <span class="text-[11px] text-indigo-600 font-bold">SIL 4 OK</span>
            </div>
            <p class="text-[11px] text-gray-400 mt-2">Track-01: 126 As &bull; Track-02: 126 As Roda</p>
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
                <span class="text-2xl font-bold text-gray-900">54 / 110 Menit</span>
                <span class="text-xs text-emerald-600 font-bold">ON SCHEDULE</span>
            </div>
            <p class="text-[11px] text-gray-400 mt-2">6 Shuttle Loop Round-Trip / 24 Jam</p>
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
                <p class="text-xs text-blue-200">Seluruh simulasi pergerakan kereta 2-arah, bongkar-muat RMGC crane, dan alur pabean dijalankan terpadu di Simulator 3D.</p>
            </div>
        </div>
        <a href="dashboard.php?page=simulator" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-gray-950 text-xs font-bold rounded-xl transition flex items-center space-x-1.5 shrink-0 shadow-sm">
            <i class="fa-solid fa-cubes"></i>
            <span>Buka Panel Simulasi 3D</span>
        </a>
    </div>

    <!-- Siding Track Status Bar (Dual Track: Sepur Utara vs Sepur Selatan) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($siding_tracks as $track): ?>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col justify-between gap-3">
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full <?= $track['dot_color'] ?>"></span>
                        <h3 class="font-bold text-sm text-gray-900"><?= $track['id'] ?></h3>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border <?= $track['status_color'] ?>">
                        <?= $track['occupancy'] ?>
                    </span>
                </div>
                <p class="text-xs text-gray-600">
                    <span class="font-semibold text-gray-800">Muatan:</span> <?= $track['cargo_type'] ?>
                </p>
                <div class="flex items-center space-x-4 text-xs text-gray-500 pt-1 font-mono">
                    <span>Panjang: <b><?= $track['length'] ?></b></span>
                    <span>Alokasi: <b class="text-gray-900"><?= $track['loaded_teu'] ?></b></span>
                    <span>Rel: <b>UIC 54</b></span>
                </div>
            </div>
            <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <span>Alat Crane: <strong class="text-gray-700"><?= $track['equip'] ?></strong></span>
                <span class="font-mono text-indigo-600 font-bold"><?= $track['axles_count'] ?> As Roda</span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ======================================================================= -->
    <!-- MASTER TIMETABLE: JADWAL SHUTTLE REL 2-ARAH (TANJUNG PRIOK <-> CIDP)   -->
    <!-- ======================================================================= -->
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-3">
            <div>
                <h3 class="text-base font-bold text-gray-900 flex items-center">
                    <i class="fa-solid fa-clock text-[#0170b9] mr-2"></i>
                    Jadwal Harian Kereta Api Kontainer 2-Arah (24-Hour Shuttle Timetable)
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Siklus kontinu 6 perjalanan KA bolak-balik antara Stasiun Pasoso (Pelabuhan Priok) &amp; CIDP Hub Cikarang.</p>
            </div>
            <div class="flex items-center space-x-2 text-xs">
                <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
                    <i class="fa-solid fa-arrow-down mr-1"></i> 3 Inbound Impor
                </span>
                <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 font-bold border border-blue-200">
                    <i class="fa-solid fa-arrow-up mr-1"></i> 3 Outbound Ekspor
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-gray-600 uppercase font-semibold text-[10px] tracking-wider border-b border-gray-200">
                    <tr>
                        <th class="py-2.5 px-3">Trip #</th>
                        <th class="py-2.5 px-3">Nomor KA</th>
                        <th class="py-2.5 px-3">Arah Relasi</th>
                        <th class="py-2.5 px-3">Asal &rarr; Tujuan</th>
                        <th class="py-2.5 px-3">Jadwal (WIB)</th>
                        <th class="py-2.5 px-3">Kapasitas</th>
                        <th class="py-2.5 px-3">Lokomotif</th>
                        <th class="py-2.5 px-3">Manifest Kargo</th>
                        <th class="py-2.5 px-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-sans">
                    <?php foreach ($shuttle_timetable as $tt): ?>
                    <tr class="hover:bg-slate-50/70 transition <?= strpos($tt['badge'], 'pulse') !== false ? 'bg-blue-50/30' : '' ?>">
                        <td class="py-2.5 px-3 font-mono font-bold text-gray-500"><?= $tt['trip_no'] ?></td>
                        <td class="py-2.5 px-3 font-mono font-bold text-gray-900"><?= $tt['train_code'] ?></td>
                        <td class="py-2.5 px-3">
                            <?php if ($tt['direction'] === 'INBOUND'): ?>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    <i class="fa-solid fa-arrow-down mr-0.5"></i> INBOUND
                                </span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-300">
                                    <i class="fa-solid fa-arrow-up mr-0.5"></i> OUTBOUND
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="py-2.5 px-3 font-semibold text-gray-800"><?= $tt['origin'] ?> &rarr; <?= $tt['dest'] ?></td>
                        <td class="py-2.5 px-3 font-mono font-semibold text-gray-700"><?= $tt['dep'] ?> - <?= $tt['arr'] ?></td>
                        <td class="py-2.5 px-3 font-bold text-gray-900"><?= $tt['teu'] ?></td>
                        <td class="py-2.5 px-3 font-mono text-gray-600"><?= $tt['loco'] ?></td>
                        <td class="py-2.5 px-3 text-gray-600 max-w-[200px] truncate" title="<?= $tt['cargo'] ?>"><?= $tt['cargo'] ?></td>
                        <td class="py-2.5 px-3">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] border <?= $tt['badge'] ?>">
                                <?= $tt['status'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
                        Pelacak Spasial Koridor Rel KA Pelabuhan 2-Arah (Tanjung Priok &harr; CIDP 35 Ha)
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center shadow-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                        Live GIS Telemetri 2-Arah
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 font-mono">
                        Daop 1 Jakarta • 54,8 KM
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 font-mono">
                        Leaflet.js + OpenStreetMap
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Pemantauan geospasial waktu-nyata rangkaian KA Logistik melintasi koridor rel pelabuhan Daop 1: <strong class="text-emerald-700">KA 2518 (Inbound Impor)</strong> dan <strong class="text-blue-700">KA 2519 (Outbound Ekspor)</strong> yang melaju dua arah dan berpapasan di jalur ganda.
                </p>
            </div>

            <!-- Toolbar Kontrol GIS & Animasi -->
            <div class="flex items-center space-x-2 flex-wrap gap-y-2">
                <!-- Direction Mode Toggles -->
                <div class="flex items-center bg-slate-100 p-1 rounded-xl text-xs space-x-1">
                    <button type="button" onclick="setCorridorDirection('both')" id="btnDirBoth" class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center space-x-1 bg-[#002f5e] text-white shadow-xs">
                        <i class="fa-solid fa-arrows-left-right text-[10px]"></i>
                        <span>2-Arah (Berpapasan)</span>
                    </button>
                    <button type="button" onclick="setCorridorDirection('inbound')" id="btnDirInbound" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center space-x-1 text-slate-700 hover:text-gray-900">
                        <i class="fa-solid fa-arrow-right text-[10px] text-emerald-600"></i>
                        <span>Inbound</span>
                    </button>
                    <button type="button" onclick="setCorridorDirection('outbound')" id="btnDirOutbound" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center space-x-1 text-slate-700 hover:text-gray-900">
                        <i class="fa-solid fa-arrow-left text-[10px] text-blue-600"></i>
                        <span>Outbound</span>
                    </button>
                </div>

                <button type="button" onclick="toggleTrainAnimation()" id="btnPlayTrain" class="px-3.5 py-2 bg-[#002f5e] hover:bg-[#0170b9] text-white rounded-xl text-xs font-bold transition-all flex items-center space-x-1.5 shadow-xs">
                    <i class="fa-solid fa-play text-[10px]" id="playTrainIcon"></i>
                    <span id="playTrainText">Simulasikan Perjalanan KA</span>
                </button>
                <button type="button" onclick="resetTrainPosition()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-all flex items-center space-x-1" title="Reset Posisi KA">
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
                        <strong id="hudTrainTitle" class="font-bold text-gray-800">Telemetri GPS KA 2-Arah</strong>
                    </div>
                    <span id="hudTrainSpeed" class="font-mono text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">
                        65 km/jam
                    </span>
                </div>

                <!-- HUD Train Switcher Tabs -->
                <div class="flex items-center bg-gray-100 p-0.5 rounded-lg text-[10.5px] font-semibold">
                    <button type="button" onclick="setHudActiveTrain('inbound')" id="hudTabInbound" class="flex-1 py-1 rounded-md text-center bg-white text-emerald-800 shadow-2xs font-bold">
                        KA 2518 (Inbound)
                    </button>
                    <button type="button" onclick="setHudActiveTrain('outbound')" id="hudTabOutbound" class="flex-1 py-1 rounded-md text-center text-gray-600 hover:text-gray-900">
                        KA 2519 (Outbound)
                    </button>
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
                        <span>Status Pabean:</span>
                        <strong id="hudTrainCustoms" class="text-emerald-600">Jalur Hijau PLP (CEISA 4.0)</strong>
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
    <!-- INTERACTIVE TRAIN FORMATION & FLATCAR VISUALIZER (DUAL TRACK SELECTION) -->
    <!-- ======================================================================= -->
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-base font-bold text-gray-900 flex items-center">
                    <i class="fa-solid fa-train-track text-[#0170b9] mr-2"></i>
                    Visualisasi Rangkaian Gerbong Datar (Sepur Rel Siding CIDP)
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Pilih sepur rel aktif untuk memeriksa susunan gerbong datar, muatan kontainer, dan status alih muat crane.</p>
            </div>
            <!-- Track Switcher & BAPLIE Button -->
            <div class="flex items-center space-x-2 flex-wrap gap-y-2">
                <div class="flex items-center bg-slate-100 p-1 rounded-xl text-xs space-x-1">
                    <button type="button" onclick="switchWagonTrack('track1')" id="btnWagonTrk1" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center space-x-1.5 bg-[#002f5e] text-white shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span>Track-01: Inbound Impor (KA 2518)</span>
                    </button>
                    <button type="button" onclick="switchWagonTrack('track2')" id="btnWagonTrk2" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center space-x-1.5 text-slate-700 hover:text-gray-900">
                        <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                        <span>Track-02: Outbound Ekspor (KA 2519)</span>
                    </button>
                </div>

                <button type="button" onclick="openBaplieModal()" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold transition-colors flex items-center space-x-1.5 shadow-xs">
                    <i class="fa-solid fa-file-code"></i>
                    <span>Lihat Manifest BAPLIE</span>
                </button>
            </div>
        </div>

        <!-- TRACK 1 VISUAL CONTAINER (INBOUND) -->
        <div id="wagonTrack1View" class="space-y-2">
            <div class="flex items-center justify-between text-xs px-1 text-gray-600">
                <span class="font-bold flex items-center text-emerald-700">
                    <i class="fa-solid fa-circle-down mr-1.5"></i> Sepur Utara Track-01: KA 2518 &bull; Bongkar Impor ex-Pasoso (Priok) &bull; 48 TEU Loaded
                </span>
                <span class="font-mono text-gray-400 text-[11px]">Alat: Reach Stacker RS-05 &amp; RMGC 01</span>
            </div>

            <div class="bg-slate-900 rounded-2xl p-6 overflow-x-auto shadow-inner border border-slate-800">
                <!-- Legend Indicators -->
                <div class="flex flex-wrap items-center justify-between gap-3 text-xs mb-6 text-slate-300 border-b border-slate-800 pb-3">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-1.5">
                            <span class="w-3 h-3 rounded bg-sky-600"></span>
                            <span class="text-[11px]">40ft/20ft Laden Impor (Raw Mat / CKD)</span>
                        </div>
                        <div class="flex items-center space-x-1.5">
                            <span class="w-3 h-3 rounded bg-cyan-600"></span>
                            <span class="text-[11px]">40ft Reefer Impor (Pharma/Cold)</span>
                        </div>
                        <div class="flex items-center space-x-1.5">
                            <span class="w-3 h-3 rounded bg-slate-500"></span>
                            <span class="text-[11px]">Empty Reposition Stock</span>
                        </div>
                    </div>
                    <div class="text-[11px] font-mono text-emerald-400 flex items-center">
                        <i class="fa-solid fa-arrow-right mr-1"></i> Arah Masuk dari Stasiun Lemahabang (Timur)
                    </div>
                </div>

                <!-- Train Visual Composition Track-01 -->
                <div class="flex items-center space-x-3 min-w-[900px] py-4">
                    <!-- Locomotive CC 206 13 42 -->
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
                        <div class="absolute -bottom-2.5 left-4 right-4 flex justify-between">
                            <span class="w-4 h-4 rounded-full bg-slate-400 border-2 border-slate-900 shadow"></span>
                            <span class="w-4 h-4 rounded-full bg-slate-400 border-2 border-slate-900 shadow"></span>
                            <span class="w-4 h-4 rounded-full bg-slate-400 border-2 border-slate-900 shadow"></span>
                            <span class="w-4 h-4 rounded-full bg-slate-400 border-2 border-slate-900 shadow"></span>
                        </div>
                    </div>

                    <div class="w-3 h-2 bg-slate-600 flex-shrink-0"></div>

                    <!-- Flatcars Iteration Track 1 -->
                    <?php foreach ($wagons_inbound as $idx => $wg): ?>
                    <div onclick="inspectWagon('track1', <?= $idx ?>)" class="flex-shrink-0 w-52 bg-slate-800 hover:bg-slate-750 cursor-pointer rounded-xl p-2.5 border-2 border-slate-700 hover:border-emerald-400 transition-all shadow-md relative flex flex-col justify-between h-28 group">
                        <div class="flex justify-between items-center text-[10px] font-mono text-slate-400">
                            <span class="font-bold text-slate-200"><?= $wg['wagon_no'] ?></span>
                            <span class="text-[9px] px-1 py-0.2 rounded <?= $wg['status_bg'] ?>"><?= $wg['status'] ?></span>
                        </div>
                        <div class="my-auto flex items-center gap-1.5">
                            <?php if ($wg['is_40ft']): ?>
                                <div class="w-full <?= $wg['slot_a']['bg'] ?> text-white rounded-lg p-2 text-center shadow transition-transform group-hover:scale-95">
                                    <span class="font-mono font-bold text-xs block truncate"><?= $wg['slot_a']['container'] ?></span>
                                    <div class="flex justify-between items-center text-[9px] text-white/80 font-mono mt-0.5">
                                        <span><?= $wg['slot_a']['iso'] ?></span>
                                        <span><?= $wg['slot_a']['weight'] ?></span>
                                    </div>
                                </div>
                            <?php else: ?>
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
                        <div class="flex justify-between items-center text-[9px] text-slate-400 border-t border-slate-700/80 pt-1 font-mono">
                            <span>PPCW 42t</span>
                            <span class="text-emerald-400 group-hover:underline">Bongkar &rarr;</span>
                        </div>
                        <div class="absolute -bottom-2.5 left-3 right-3 flex justify-between">
                            <span class="w-3.5 h-3.5 rounded-full bg-slate-500 border border-slate-900 shadow"></span>
                            <span class="w-3.5 h-3.5 rounded-full bg-slate-500 border border-slate-900 shadow"></span>
                            <span class="w-3.5 h-3.5 rounded-full bg-slate-500 border border-slate-900 shadow"></span>
                            <span class="w-3.5 h-3.5 rounded-full bg-slate-500 border border-slate-900 shadow"></span>
                        </div>
                    </div>
                    <?php if ($idx < count($wagons_inbound) - 1): ?>
                    <div class="w-2.5 h-1.5 bg-slate-600 flex-shrink-0"></div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <div class="w-full h-3 border-t-2 border-b-2 border-slate-600 bg-slate-950 flex items-center justify-around opacity-60">
                    <?php for ($i = 0; $i < 30; $i++): ?>
                    <div class="w-1 h-3 bg-amber-900/60"></div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- TRACK 2 VISUAL CONTAINER (OUTBOUND) -->
        <div id="wagonTrack2View" class="space-y-2 hidden">
            <div class="flex items-center justify-between text-xs px-1 text-gray-600">
                <span class="font-bold flex items-center text-blue-700">
                    <i class="fa-solid fa-circle-up mr-1.5"></i> Sepur Selatan Track-02: KA 2519 &bull; Muat Ekspor Menuju Pasoso (Priok) &bull; 52 TEU Loaded (SOLAS VGM)
                </span>
                <span class="font-mono text-gray-400 text-[11px]">Alat: Reach Stacker RS-02 &amp; RMGC 02</span>
            </div>

            <div class="bg-slate-900 rounded-2xl p-6 overflow-x-auto shadow-inner border border-slate-800">
                <!-- Legend Indicators Track 2 -->
                <div class="flex flex-wrap items-center justify-between gap-3 text-xs mb-6 text-slate-300 border-b border-slate-800 pb-3">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-1.5">
                            <span class="w-3 h-3 rounded bg-emerald-600"></span>
                            <span class="text-[11px]">40ft Laden Ekspor (VGM Verified)</span>
                        </div>
                        <div class="flex items-center space-x-1.5">
                            <span class="w-3 h-3 rounded bg-teal-600"></span>
                            <span class="text-[11px]">20ft Laden Ekspor (NPE Cleared)</span>
                        </div>
                        <div class="flex items-center space-x-1.5">
                            <span class="w-3 h-3 rounded bg-cyan-600"></span>
                            <span class="text-[11px]">40ft Reefer Ekspor (Active Plug)</span>
                        </div>
                    </div>
                    <div class="text-[11px] font-mono text-blue-400 flex items-center">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Arah Keluar Menuju Stasiun Pasoso / Dermaga Priok (Barat)
                    </div>
                </div>

                <!-- Train Visual Composition Track-02 -->
                <div class="flex items-center space-x-3 min-w-[900px] py-4">
                    <!-- Locomotive CC 206 13 55 -->
                    <div class="flex-shrink-0 w-44 bg-gradient-to-r from-blue-700 via-sky-600 to-blue-600 rounded-xl p-3 text-white border-2 border-blue-400 shadow-lg relative flex flex-col justify-between h-28">
                        <div class="flex justify-between items-start text-[10px] font-mono font-bold">
                            <span>LOKOMOTIF</span>
                            <span class="px-1.5 py-0.5 bg-black/40 rounded">CC 206</span>
                        </div>
                        <div class="text-center my-auto">
                            <i class="fa-solid fa-train text-3xl opacity-90 drop-shadow"></i>
                            <span class="block text-[11px] font-mono font-bold mt-1 tracking-wider">CC 206 13 55</span>
                        </div>
                        <div class="flex justify-between items-center text-[9px] text-blue-100 font-mono">
                            <span>2.250 HP</span>
                            <span>KAI LOGISTIK</span>
                        </div>
                        <div class="absolute -bottom-2.5 left-4 right-4 flex justify-between">
                            <span class="w-4 h-4 rounded-full bg-slate-400 border-2 border-slate-900 shadow"></span>
                            <span class="w-4 h-4 rounded-full bg-slate-400 border-2 border-slate-900 shadow"></span>
                            <span class="w-4 h-4 rounded-full bg-slate-400 border-2 border-slate-900 shadow"></span>
                            <span class="w-4 h-4 rounded-full bg-slate-400 border-2 border-slate-900 shadow"></span>
                        </div>
                    </div>

                    <div class="w-3 h-2 bg-slate-600 flex-shrink-0"></div>

                    <!-- Flatcars Iteration Track 2 -->
                    <?php foreach ($wagons_outbound as $idx => $wg): ?>
                    <div onclick="inspectWagon('track2', <?= $idx ?>)" class="flex-shrink-0 w-52 bg-slate-800 hover:bg-slate-750 cursor-pointer rounded-xl p-2.5 border-2 border-slate-700 hover:border-blue-400 transition-all shadow-md relative flex flex-col justify-between h-28 group">
                        <div class="flex justify-between items-center text-[10px] font-mono text-slate-400">
                            <span class="font-bold text-slate-200"><?= $wg['wagon_no'] ?></span>
                            <span class="text-[9px] px-1 py-0.2 rounded <?= $wg['status_bg'] ?>"><?= $wg['status'] ?></span>
                        </div>
                        <div class="my-auto flex items-center gap-1.5">
                            <?php if ($wg['is_40ft']): ?>
                                <div class="w-full <?= $wg['slot_a']['bg'] ?> text-white rounded-lg p-2 text-center shadow transition-transform group-hover:scale-95">
                                    <span class="font-mono font-bold text-xs block truncate"><?= $wg['slot_a']['container'] ?></span>
                                    <div class="flex justify-between items-center text-[9px] text-white/80 font-mono mt-0.5">
                                        <span><?= $wg['slot_a']['iso'] ?></span>
                                        <span><?= $wg['slot_a']['weight'] ?></span>
                                    </div>
                                </div>
                            <?php else: ?>
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
                        <div class="flex justify-between items-center text-[9px] text-slate-400 border-t border-slate-700/80 pt-1 font-mono">
                            <span>PPCW 42t</span>
                            <span class="text-blue-400 group-hover:underline">Muat &rarr;</span>
                        </div>
                        <div class="absolute -bottom-2.5 left-3 right-3 flex justify-between">
                            <span class="w-3.5 h-3.5 rounded-full bg-slate-500 border border-slate-900 shadow"></span>
                            <span class="w-3.5 h-3.5 rounded-full bg-slate-500 border border-slate-900 shadow"></span>
                            <span class="w-3.5 h-3.5 rounded-full bg-slate-500 border border-slate-900 shadow"></span>
                            <span class="w-3.5 h-3.5 rounded-full bg-slate-500 border border-slate-900 shadow"></span>
                        </div>
                    </div>
                    <?php if ($idx < count($wagons_outbound) - 1): ?>
                    <div class="w-2.5 h-1.5 bg-slate-600 flex-shrink-0"></div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <div class="w-full h-3 border-t-2 border-b-2 border-slate-600 bg-slate-950 flex items-center justify-around opacity-60">
                    <?php for ($i = 0; $i < 30; $i++): ?>
                    <div class="w-1 h-3 bg-amber-900/60"></div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- DUAL COLUMNS: FRAUSCHER AXLE SENSOR & TRANSSHIPMENT WORKFLOW 2-ARAH     -->
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
                            <p class="text-xs text-gray-500">Sensor gandar induktif dual track &bull; Sepur Utara &amp; Sepur Selatan</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-[11px] font-bold rounded-full">
                        ONLINE &bull; SIL 4 PASS
                    </span>
                </div>

                <!-- Sensor Diagram & Diagnostics -->
                <div class="bg-slate-900 text-white rounded-xl p-4 border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between text-xs font-mono">
                        <span class="text-slate-400">SENSOR DUAL CHANNEL (TRACK 1 &amp; TRACK 2)</span>
                        <span class="text-emerald-400 flex items-center">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                            ACTIVE INTERLOCKING
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div class="bg-slate-800/80 p-3 rounded-xl border border-slate-700/60">
                            <span class="text-[10px] text-slate-400 block uppercase font-mono">Total Gandar</span>
                            <span id="axle-count-val" class="text-2xl font-bold font-mono text-emerald-400">252</span>
                            <span class="text-[9px] text-slate-500 block">60 GD + 2 Loco</span>
                        </div>
                        <div class="bg-slate-800/80 p-3 rounded-xl border border-slate-700/60">
                            <span class="text-[10px] text-slate-400 block uppercase font-mono">Kecepatan Langsir</span>
                            <span class="text-2xl font-bold font-mono text-white">12.4</span>
                            <span class="text-[9px] text-slate-500 block">km/jam (Siding)</span>
                        </div>
                        <div class="bg-slate-800/80 p-3 rounded-xl border border-slate-700/60">
                            <span class="text-[10px] text-slate-400 block uppercase font-mono">Arah Gerak</span>
                            <span class="text-sm font-bold font-mono text-cyan-300 mt-1 block">DUAL LOOP &harr;</span>
                            <span class="text-[9px] text-slate-500 block">In &amp; Outbound</span>
                        </div>
                    </div>

                    <!-- Pulse Waveform Graphic Animation -->
                    <div class="relative h-12 bg-slate-950 rounded-lg border border-slate-800 overflow-hidden flex items-center px-3">
                        <div class="text-[10px] font-mono text-slate-500 mr-2 flex-shrink-0">PULSE INDUKTIF DUAL-TRACK:</div>
                        <div id="pulse-indicator" class="flex-1 flex items-center space-x-1 overflow-hidden h-6">
                            <?php for ($pulse_i = 0; $pulse_i < 24; $pulse_i++): ?>
                            <span class="w-1 bg-emerald-500 rounded-full transition-all duration-150" style="height: <?= ($pulse_i % 2 == 0 ? 18 : 6) ?>px;"></span>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <!-- Diagnostic Hardware Self-Test Controls -->
                <div class="space-y-2">
                    <button type="button" onclick="triggerAxlePassSimulation()" id="btn-axle-sim" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition-colors flex items-center justify-center space-x-2 shadow-xs">
                        <i class="fa-solid fa-microchip"></i>
                        <span>Uji Diagnostik Sinyal Sensor Roda Dual Track (Self-Test)</span>
                    </button>
                    <p class="text-[11px] text-gray-400 text-center">
                        Memvalidasi sensor Frauscher RSR180 Sepur Utara &amp; Sepur Selatan (Zero Discrepancy Rule). Simulasi alih muat dan pergerakan 3D dijalankan di <a href="dashboard.php?page=simulator" class="text-indigo-600 font-bold underline">Panel Simulasi 3D</a>.
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
                        SOP Alur Transshipment 2-Arah (Loop Rel &harr; Lapangan)
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
                            <h4 class="text-xs font-bold text-gray-900">Tahap 1: Deteksi Kedatangan Dual Siding &amp; Axle Counter</h4>
                            <p class="text-[11px] text-gray-500 mt-0.5">Sensor RSR180 menghitung 126 as roda KA 2518 (Track-01) dan 126 as roda KA 2519 (Track-02) secara independen.</p>
                        </div>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded">Selesai</span>
                    </div>

                    <!-- Step 2 -->
                    <div class="flex items-start space-x-3 p-2.5 rounded-xl bg-slate-50 border border-slate-200/80">
                        <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">
                            <i class="fa-solid fa-check text-[10px]"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xs font-bold text-gray-900">Tahap 2: Penguncian Lokomotif &amp; Interlocking Persinyalan SIL 4</h4>
                            <p class="text-[11px] text-gray-500 mt-0.5">Pemasangan stop block dan penguncian rem sepatu siding menjamin keamanan pekerja crane RMGC &amp; RS.</p>
                        </div>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded">Selesai</span>
                    </div>

                    <!-- Step 3 (Active) -->
                    <div class="flex items-start space-x-3 p-2.5 rounded-xl bg-blue-50/80 border border-blue-200">
                        <div class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5 animate-pulse">
                            3
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xs font-bold text-blue-900">Tahap 3: Operasi Alih Muat Simultan (Bongkar Impor &amp; Muat Ekspor)</h4>
                            <p class="text-[11px] text-blue-800 mt-0.5">Track-01 membongkar kontainer impor ke buffer, sementara Track-02 memuat box ekspor (VGM) ke gerbong datar.</p>
                        </div>
                        <span class="text-[10px] font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded animate-pulse">Berjalan Simultan</span>
                    </div>

                    <!-- Step 4 -->
                    <div class="flex items-start space-x-3 p-2.5 rounded-xl bg-slate-50 border border-slate-200/80">
                        <div class="w-6 h-6 rounded-full bg-slate-300 text-slate-700 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">
                            4
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xs font-bold text-gray-700">Tahap 4: Klirens Pabean CEISA 4.0 &amp; Cross-Docking Truk</h4>
                            <p class="text-[11px] text-gray-500 mt-0.5">Relokasi pabean PLP disahkan Bea Cukai, kargo prioritas langsung dipindahkan ke armada truk tanpa pengendapan.</p>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500 bg-slate-200 px-2 py-0.5 rounded">Sinkron</span>
                    </div>

                    <!-- Step 5 -->
                    <div class="flex items-start space-x-3 p-2.5 rounded-xl bg-slate-50 border border-slate-200/80">
                        <div class="w-6 h-6 rounded-full bg-slate-300 text-slate-700 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">
                            5
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xs font-bold text-gray-700">Tahap 5: Pertukaran Pesan EDI BAPLIE &amp; Pelepasan KA Shuttle</h4>
                            <p class="text-[11px] text-gray-500 mt-0.5">Penerbitan surat jalan KA dan transmisi BAPLIE ke TOS Dermaga Priok untuk persiapan crane muat kapal.</p>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500 bg-slate-200 px-2 py-0.5 rounded">13:30 / 18:45 WIB</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- UN/EDIFACT BAPLIE (BAYPLAN / WAGON SLOT ALLOCATION MESSAGE) PANEL       -->
    <!-- ======================================================================= -->
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-4">
            <div>
                <h3 class="text-base font-bold text-gray-900 flex items-center">
                    <i class="fa-solid fa-file-code text-[#0170b9] mr-2"></i>
                    UN/EDIFACT BAPLIE D.95B (Slot Allocation EDI Standard SMDG)
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Format elektronik standar internasional SMDG untuk pertukaran data manifest slot gerbong KA 2-arah.</p>
            </div>
            <!-- BAPLIE Switcher & Actions -->
            <div class="flex items-center space-x-2 flex-wrap gap-y-2">
                <div class="flex items-center bg-slate-100 p-1 rounded-xl text-xs space-x-1">
                    <button type="button" onclick="switchBaplieTab('inbound')" id="btnBaplieInbound" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center space-x-1 bg-[#002f5e] text-white shadow-xs">
                        <span>BAPLIE Inbound (KA 2518)</span>
                    </button>
                    <button type="button" onclick="switchBaplieTab('outbound')" id="btnBaplieOutbound" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center space-x-1 text-slate-700 hover:text-gray-900">
                        <span>BAPLIE Outbound (KA 2519)</span>
                    </button>
                </div>
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

        <!-- Raw EDIFACT BAPLIE Block (Inbound vs Outbound) -->
        <div class="relative bg-slate-950 rounded-xl p-4 border border-slate-800 shadow-inner">
            <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-800 text-[11px] font-mono text-slate-400">
                <span id="baplie-buffer-name">BUFFER: BAPLIE_KA2518_INBOUND_PRIOK_CIDP.EDI</span>
                <span class="text-emerald-400">STANDARD SMDG BAPLIE 2.2 / D.95B</span>
            </div>
            <pre id="baplie-raw-text" class="font-mono text-xs text-emerald-300 leading-relaxed overflow-x-auto whitespace-pre selection:bg-blue-600 selection:text-white max-h-56">UNA:+.? '
UNB+UNOA:2+TANJUNG_PRIOK_TOS+CIDP_RAIL_YMS+20261001:0815+BAP251801'
BGM+BAPLIE+KA2518-INBOUND-20261001+9'
DTM+137:202610011005:203'
TDT+20+KA2518+1++KAILOG:172:20+++CC2061342:103::CC206'
LOC+5+IDTPP:139:6+TANJUNG PRIOK CONTAINER TERMINAL'
LOC+61+IDCKG:139:6+CIDP CIKARANG DRY PORT HUB'
EQD+CN+MSKU9821450+45G1:102:5++2+5'
LOC+147+GD421201:W01:BAY01'
MEA+WT++KGM:31450'
LOC+11+IDCKG:139:6'
EQD+CN+TCKU8291048+22G1:102:5++2+5'
LOC+147+GD421202:W02:BAY01'
MEA+WT++KGM:21500'
LOC+11+IDCKG:139:6'
EQD+CN+EMCU5518200+22G1:102:5++2+5'
LOC+147+GD421202:W02:BAY02'
MEA+WT++KGM:19800'
LOC+11+IDCKG:139:6'
EQD+CN+ONEU6612988+42G1:102:5++2+5'
LOC+147+GD421203:W03:BAY01'
MEA+WT++KGM:28900'
LOC+11+IDCKG:139:6'
EQD+CN+CMAU7234910+45R1:102:5++2+5'
LOC+147+GD421204:W04:BAY01'
MEA+WT++KGM:29100'
LOC+11+IDCKG:139:6'
UNT+22+BAP251801'
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
const wagonsInbound = <?= json_encode($wagons_inbound) ?>;
const wagonsOutbound = <?= json_encode($wagons_outbound) ?>;

const baplieInboundData = `UNA:+.? '
UNB+UNOA:2+TANJUNG_PRIOK_TOS+CIDP_RAIL_YMS+20261001:0815+BAP251801'
BGM+BAPLIE+KA2518-INBOUND-20261001+9'
DTM+137:202610011005:203'
TDT+20+KA2518+1++KAILOG:172:20+++CC2061342:103::CC206'
LOC+5+IDTPP:139:6+TANJUNG PRIOK CONTAINER TERMINAL'
LOC+61+IDCKG:139:6+CIDP CIKARANG DRY PORT HUB'
EQD+CN+MSKU9821450+45G1:102:5++2+5'
LOC+147+GD421201:W01:BAY01'
MEA+WT++KGM:31450'
LOC+11+IDCKG:139:6'
EQD+CN+TCKU8291048+22G1:102:5++2+5'
LOC+147+GD421202:W02:BAY01'
MEA+WT++KGM:21500'
LOC+11+IDCKG:139:6'
EQD+CN+EMCU5518200+22G1:102:5++2+5'
LOC+147+GD421202:W02:BAY02'
MEA+WT++KGM:19800'
LOC+11+IDCKG:139:6'
EQD+CN+ONEU6612988+42G1:102:5++2+5'
LOC+147+GD421203:W03:BAY01'
MEA+WT++KGM:28900'
LOC+11+IDCKG:139:6'
EQD+CN+CMAU7234910+45R1:102:5++2+5'
LOC+147+GD421204:W04:BAY01'
MEA+WT++KGM:29100'
LOC+11+IDCKG:139:6'
UNT+22+BAP251801'
UNZ+1+BAP251801'`;

const baplieOutboundData = `UNA:+.? '
UNB+UNOA:2+CIDP_RAIL_YMS+TANJUNG_PRIOK_TOS+20261001:1330+BAP251901'
BGM+BAPLIE+KA2519-OUTBOUND-20261001+9'
DTM+137:202610011520:203'
TDT+20+KA2519+1++KAILOG:172:20+++CC2061355:103::CC206'
LOC+5+IDCKG:139:6+CIDP CIKARANG DRY PORT HUB'
LOC+61+IDTPP:139:6+TANJUNG PRIOK CONTAINER TERMINAL'
EQD+CN+SEGU1982731+45G1:102:5++2+5'
LOC+147+GD421411:W01:BAY01'
MEA+WT++KGM:26850'
LOC+11+USLAX:139:6'
EQD+CN+TEMU4910285+22G1:102:5++2+5'
LOC+147+GD421412:W02:BAY01'
MEA+WT++KGM:18400'
LOC+11+JPTYO:139:6'
EQD+CN+MEDU7731902+22G1:102:5++2+5'
LOC+147+GD421412:W02:BAY02'
MEA+WT++KGM:22100'
LOC+11+AUSYD:139:6'
EQD+CN+HASU8820194+45R1:102:5++2+5'
LOC+147+GD421413:W03:BAY01'
MEA+WT++KGM:27900'
LOC+11+NLRTM:139:6'
EQD+CN+TLLU5529103+45G1:102:5++2+5'
LOC+147+GD421414:W04:BAY01'
MEA+WT++KGM:29300'
LOC+11+SGSIN:139:6'
UNT+22+BAP251901'
UNZ+1+BAP251901'`;

let activeBaplieTab = 'inbound';

// Switch Wagon View between Track 1 (Inbound) and Track 2 (Outbound)
function switchWagonTrack(track) {
    const v1 = document.getElementById('wagonTrack1View');
    const v2 = document.getElementById('wagonTrack2View');
    const btn1 = document.getElementById('btnWagonTrk1');
    const btn2 = document.getElementById('btnWagonTrk2');

    if (track === 'track1') {
        if (v1) v1.classList.remove('hidden');
        if (v2) v2.classList.add('hidden');
        if (btn1) {
            btn1.className = "px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center space-x-1.5 bg-[#002f5e] text-white shadow-xs";
        }
        if (btn2) {
            btn2.className = "px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center space-x-1.5 text-slate-700 hover:text-gray-900";
        }
    } else {
        if (v1) v1.classList.add('hidden');
        if (v2) v2.classList.remove('hidden');
        if (btn1) {
            btn1.className = "px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center space-x-1.5 text-slate-700 hover:text-gray-900";
        }
        if (btn2) {
            btn2.className = "px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center space-x-1.5 bg-[#002f5e] text-white shadow-xs";
        }
    }
}

// Inspect Wagon Modal
function inspectWagon(track, idx) {
    const list = track === 'track1' ? wagonsInbound : wagonsOutbound;
    const wg = list[idx];
    if (!wg) return;

    document.getElementById('modal-wagon-title').textContent = `Inspeksi ${wg.wagon_no} (${wg.type}) — ${wg.direction}`;
    document.getElementById('modal-wagon-type').textContent = `${wg.type} • ${wg.direction}`;
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
                <div class="text-[11px] text-gray-600">Muatan: <b>${wg.slot_a.type}</b> &bull; Berat: <b>${wg.slot_a.weight}</b></div>
                <div class="text-[11px] text-gray-600">Pelabuhan: <b class="text-blue-800">${wg.slot_a.pod}</b></div>
                <div class="text-[10.5px] text-gray-500 pt-1 border-t border-blue-100">Shipper / Forwarder: <b>${wg.slot_a.shipper}</b></div>
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
                    <div class="text-[11px] text-gray-600">Pelabuhan: <b class="text-sky-800">${wg.slot_a.pod}</b></div>
                    <div class="text-[10.5px] text-gray-500 pt-1 border-t border-sky-100">Shipper: <b>${wg.slot_a.shipper}</b></div>
                </div>
                ${wg.slot_b ? `
                <div class="bg-indigo-50 border border-indigo-200 p-3 rounded-xl space-y-1">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold uppercase text-indigo-700">Slot B (20ft Rear)</span>
                        <span class="font-mono text-[10px] bg-indigo-200 text-indigo-900 px-1.5 py-0.5 rounded font-bold">${wg.slot_b.iso}</span>
                    </div>
                    <div class="font-mono font-bold text-sm text-gray-900">${wg.slot_b.container}</div>
                    <div class="text-[11px] text-gray-600">Muatan: <b>${wg.slot_b.type}</b> &bull; Berat: <b>${wg.slot_b.weight}</b></div>
                    <div class="text-[11px] text-gray-600">Pelabuhan: <b class="text-indigo-800">${wg.slot_b.pod}</b></div>
                    <div class="text-[10.5px] text-gray-500 pt-1 border-t border-indigo-100">Shipper: <b>${wg.slot_b.shipper}</b></div>
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
    alert("Instruksi alih muat terkirim ke Reach Stacker RS-05 / RS-02 melalui TOS VMT. Kontainer siap diangkat.");
    closeWagonModal();
}

function triggerAxlePassSimulation() {
    const btn = document.getElementById('btn-axle-sim');
    const display = document.getElementById('axle-count-display');
    const val = document.getElementById('axle-count-val');
    const pulses = document.querySelectorAll('#pulse-indicator span');

    btn.disabled = true;
    btn.classList.add('opacity-50');

    let current = 240;
    const interval = setInterval(() => {
        current += 2;
        display.textContent = current + " As Roda";
        val.textContent = current;

        // Jiggle pulse bars
        pulses.forEach(p => {
            const h = Math.floor(Math.random() * 20) + 4;
            p.style.height = h + 'px';
        });

        if (current >= 252) {
            clearInterval(interval);
            btn.disabled = false;
            btn.classList.remove('opacity-50');
            alert("Verifikasi As Roda Dual Track Selesai!\n\n• Track-01: 126 as (KA 2518 Inbound)\n• Track-02: 126 as (KA 2519 Outbound)\nTotal: 252 as roda SIL 4 PASS (Zero Discrepancy Rule).");
        }
    }, 250);
}

// BAPLIE Functions
function switchBaplieTab(mode) {
    activeBaplieTab = mode;
    const btnIn = document.getElementById('btnBaplieInbound');
    const btnOut = document.getElementById('btnBaplieOutbound');
    const preText = document.getElementById('baplie-raw-text');
    const bufName = document.getElementById('baplie-buffer-name');

    if (mode === 'inbound') {
        if (btnIn) btnIn.className = "px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center space-x-1 bg-[#002f5e] text-white shadow-xs";
        if (btnOut) btnOut.className = "px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center space-x-1 text-slate-700 hover:text-gray-900";
        if (preText) preText.textContent = baplieInboundData;
        if (bufName) bufName.textContent = "BUFFER: BAPLIE_KA2518_INBOUND_PRIOK_CIDP.EDI";
    } else {
        if (btnIn) btnIn.className = "px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center space-x-1 text-slate-700 hover:text-gray-900";
        if (btnOut) btnOut.className = "px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center space-x-1 bg-[#002f5e] text-white shadow-xs";
        if (preText) preText.textContent = baplieOutboundData;
        if (bufName) bufName.textContent = "BUFFER: BAPLIE_KA2519_OUTBOUND_CIDP_PRIOK.EDI";
    }
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
    const filename = activeBaplieTab === 'inbound' ? "BAPLIE_KA2518_INBOUND_CIDP.edi" : "BAPLIE_KA2519_OUTBOUND_PRIOK.edi";
    const blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

function openBaplieModal() {
    window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
}

// =======================================================================
// GIS CORRIDOR TRACKER LOGIC (LEAFLET.JS) — 2-ARAH SHUTTLE SIMULATOR
// =======================================================================
let corridorMap = null;
let railLayerGroup = null;
let roadLayerGroup = null;
let trainMarkerInbound = null;
let trainMarkerOutbound = null;
let trainAnimInterval = null;
let isTrainPlaying = false;
let corridorMode = 'both'; // 'both' | 'inbound' | 'outbound'
let hudActiveTrain = 'inbound'; // 'inbound' | 'outbound'

let currentStepInbound = 0;
let currentStepOutbound = 0;
let interpolatedStepsInbound = [];
let interpolatedStepsOutbound = [];

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
    { lat: -6.2731, lng: 107.1652, name: "CIDP Hub Rail Siding Track-01/02", km: 54.8, speed: 20, desc: "Terminal Intermodal 35 Ha, Dual Track 450m, Bea Cukai Terpadu" }
];

// Rute Tol Jakarta - Cikampek
const roadCorridorCoords = [
    [-6.1042, 106.8856],
    [-6.1265, 106.8912],
    [-6.1820, 106.8760],
    [-6.2415, 106.8722],
    [-6.2450, 106.9050],
    [-6.2512, 106.9421],
    [-6.2482, 106.9891],
    [-6.2570, 107.0420],
    [-6.2680, 107.0930],
    [-6.2890, 107.1250],
    [-6.3101, 107.1352],
    [-6.2950, 107.1550],
    [-6.2731, 107.1652]
];

// Generate Interpolated Sub-steps
function generateInterpolatedPaths() {
    interpolatedStepsInbound = [];
    const stepsPerSegment = 16;
    for (let i = 0; i < railCorridorPoints.length - 1; i++) {
        const p1 = railCorridorPoints[i];
        const p2 = railCorridorPoints[i + 1];
        for (let j = 0; j < stepsPerSegment; j++) {
            const frac = j / stepsPerSegment;
            interpolatedStepsInbound.push({
                lat: p1.lat + (p2.lat - p1.lat) * frac,
                lng: p1.lng + (p2.lng - p1.lng) * frac,
                km: p1.km + (p2.km - p1.km) * frac,
                speed: Math.round(p1.speed + (p2.speed - p1.speed) * frac),
                name: frac < 0.5 ? p1.name : p2.name,
                direction: 'INBOUND (Priok &rarr; CIDP)'
            });
        }
    }
    const lastP = railCorridorPoints[railCorridorPoints.length - 1];
    interpolatedStepsInbound.push({
        lat: lastP.lat,
        lng: lastP.lng,
        km: lastP.km,
        speed: lastP.speed,
        name: lastP.name,
        direction: 'INBOUND (Priok &rarr; CIDP)'
    });

    // Outbound path is reverse of inbound path with km relative to CIDP
    interpolatedStepsOutbound = [];
    for (let i = interpolatedStepsInbound.length - 1; i >= 0; i--) {
        const p = interpolatedStepsInbound[i];
        interpolatedStepsOutbound.push({
            lat: p.lat,
            lng: p.lng,
            km: 54.8 - p.km,
            speed: p.speed,
            name: p.name,
            direction: 'OUTBOUND (CIDP &rarr; Priok)'
        });
    }
}

// Inisialisasi Peta GIS Intermodal Leaflet
function initCorridorGisMap() {
    const mapContainer = document.getElementById('corridorGisMap');
    if (!mapContainer || corridorMap) return;

    generateInterpolatedPaths();

    corridorMap = L.map('corridorGisMap', {
        zoomControl: false,
        attributionControl: false
    }).setView([-6.20, 107.03], 11);

    L.control.zoom({ position: 'bottomright' }).addTo(corridorMap);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        subdomains: ['a', 'b', 'c']
    }).addTo(corridorMap);

    railLayerGroup = L.layerGroup().addTo(corridorMap);
    roadLayerGroup = L.layerGroup().addTo(corridorMap);

    // Icons
    const priokIcon = L.divIcon({
        className: 'custom-gis-pin',
        html: `
            <div class="relative flex items-center justify-center pointer-events-auto">
                <span class="absolute w-8 h-8 rounded-full bg-blue-500/30 animate-ping"></span>
                <div class="w-8 h-8 rounded-full bg-[#004b87] border-2 border-white shadow-lg flex items-center justify-center text-white text-xs font-bold">
                    <i class="fa-solid fa-anchor"></i>
                </div>
                <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 whitespace-nowrap bg-white/95 px-2 py-0.5 rounded shadow text-[10px] font-bold text-[#004b87] border border-blue-100">
                    Tj. Priok (Pasoso/JICT)
                </div>
            </div>
        `,
        iconSize: [32, 32],
        iconAnchor: [16, 16]
    });

    const cidpIcon = L.divIcon({
        className: 'custom-gis-pin',
        html: `
            <div class="relative flex items-center justify-center pointer-events-auto">
                <span class="absolute w-10 h-10 rounded-full bg-emerald-500/40 animate-ping"></span>
                <div class="w-9 h-9 rounded-full bg-emerald-600 border-2 border-white shadow-xl flex items-center justify-center text-white text-sm font-bold">
                    <i class="fa-solid fa-warehouse"></i>
                </div>
                <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 whitespace-nowrap bg-emerald-900 text-white px-2 py-0.5 rounded shadow text-[10px] font-bold border border-emerald-400">
                    CIDP Hub (35 Ha)
                </div>
            </div>
        `,
        iconSize: [36, 36],
        iconAnchor: [18, 18]
    });

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

    // Inbound Train Marker (Amber/Orange)
    const trainInboundIcon = L.divIcon({
        className: 'custom-gis-pin',
        html: `
            <div class="relative flex items-center justify-center pointer-events-auto">
                <div class="train-radar-pulse"></div>
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-600 via-orange-500 to-amber-400 border-2 border-white shadow-xl flex items-center justify-center text-white text-sm z-10">
                    <i class="fa-solid fa-train"></i>
                </div>
                <div class="absolute -top-7 left-1/2 -translate-x-1/2 whitespace-nowrap bg-gray-900/90 text-amber-300 px-2 py-0.5 rounded-full shadow text-[10px] font-mono font-bold border border-amber-500/50 z-20 flex items-center space-x-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>KA 2518 [Inbound]</span>
                </div>
            </div>
        `,
        iconSize: [36, 36],
        iconAnchor: [18, 18]
    });

    // Outbound Train Marker (Blue/Emerald)
    const trainOutboundIcon = L.divIcon({
        className: 'custom-gis-pin',
        html: `
            <div class="relative flex items-center justify-center pointer-events-auto">
                <div class="absolute w-full h-full rounded-full bg-blue-500/40 animate-ping"></div>
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-700 via-sky-600 to-blue-500 border-2 border-white shadow-xl flex items-center justify-center text-white text-sm z-10">
                    <i class="fa-solid fa-train-subway"></i>
                </div>
                <div class="absolute -top-7 left-1/2 -translate-x-1/2 whitespace-nowrap bg-gray-900/90 text-sky-300 px-2 py-0.5 rounded-full shadow text-[10px] font-mono font-bold border border-sky-500/50 z-20 flex items-center space-x-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-ping"></span>
                    <span>KA 2519 [Outbound]</span>
                </div>
            </div>
        `,
        iconSize: [36, 36],
        iconAnchor: [18, 18]
    });

    // Polyline tracks
    const railCoords = railCorridorPoints.map(p => [p.lat, p.lng]);
    L.polyline(railCoords, { color: '#002f5e', weight: 6, opacity: 0.85 }).addTo(railLayerGroup);
    L.polyline(railCoords, { color: '#38bdf8', weight: 3.5, opacity: 0.95, dashArray: '8, 8' }).addTo(railLayerGroup);

    L.polyline(roadCorridorCoords, { color: '#f59e0b', weight: 4, opacity: 0.75, dashArray: '6, 6' }).addTo(roadLayerGroup);

    // Priok Marker
    const pPriok = railCorridorPoints[0];
    L.marker([pPriok.lat, pPriok.lng], { icon: priokIcon })
        .bindPopup(`
            <div class="p-1 space-y-1 text-xs">
                <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded font-bold text-[10px]">ORIGIN / DESTINATION PORT</span>
                <h4 class="font-bold text-gray-900 text-sm mt-1">${pPriok.name}</h4>
                <p class="text-gray-600">${pPriok.desc}</p>
                <div class="text-[11px] text-gray-500 pt-1 border-t border-gray-100">
                    Gate Rel: <b>Stasiun Pasoso</b> &bull; Kapasitas: <b>7,2 Juta TEU/thn</b>
                </div>
            </div>
        `)
        .addTo(railLayerGroup);

    // CIDP Marker
    const pCidp = railCorridorPoints[railCorridorPoints.length - 1];
    L.marker([pCidp.lat, pCidp.lng], { icon: cidpIcon })
        .bindPopup(`
            <div class="p-1 space-y-1 text-xs">
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded font-bold text-[10px]">CIDP DRY PORT HUB (35 HA)</span>
                <h4 class="font-bold text-gray-900 text-sm mt-1">${pCidp.name}</h4>
                <p class="text-gray-600">${pCidp.desc}</p>
                <div class="text-[11px] text-gray-500 pt-1 border-t border-gray-100">
                    Dual Siding Track: <b>Track-01 (Impor) &amp; Track-02 (Ekspor)</b> &bull; Sensor: <b>Frauscher 252 As</b>
                </div>
            </div>
        `)
        .addTo(railLayerGroup);

    // Stasiun Antara
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

    // Cikunir
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

    // Initial Position KA 2518 Marker (Inbound - starts at Priok)
    const startIn = interpolatedStepsInbound[0];
    trainMarkerInbound = L.marker([startIn.lat, startIn.lng], { icon: trainInboundIcon })
        .bindPopup(`
            <div class="p-1 space-y-1 text-xs font-sans">
                <div class="flex items-center space-x-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="font-mono font-bold text-gray-900">KA 2518 (Inbound Impor ex-Priok)</span>
                </div>
                <div class="text-[11px] text-gray-600">
                    CC 206 13 42 &bull; 30 Gerbong PPCW &bull; Menuju Siding Track-01 CIDP Hub
                </div>
            </div>
        `)
        .addTo(corridorMap);

    // Initial Position KA 2519 Marker (Outbound - starts at CIDP)
    const startOut = interpolatedStepsOutbound[0];
    trainMarkerOutbound = L.marker([startOut.lat, startOut.lng], { icon: trainOutboundIcon })
        .bindPopup(`
            <div class="p-1 space-y-1 text-xs font-sans">
                <div class="flex items-center space-x-1.5">
                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                    <span class="font-mono font-bold text-gray-900">KA 2519 (Outbound Ekspor to-Priok)</span>
                </div>
                <div class="text-[11px] text-gray-600">
                    CC 206 13 55 &bull; 30 Gerbong PPCW &bull; Menuju Stasiun Pasoso / Dermaga JICT
                </div>
            </div>
        `)
        .addTo(corridorMap);

    // Set Initial HUD Telemetry
    updateTelemetryHUD();

    // Auto-fit bounds
    setTimeout(() => {
        corridorMap.invalidateSize();
        corridorMap.fitBounds([
            [-6.1042, 106.8523],
            [-6.2818, 107.1729]
        ], { padding: [30, 30] });
    }, 300);
}

// Set Corridor Direction Mode
function setCorridorDirection(dir) {
    corridorMode = dir;
    const btnBoth = document.getElementById('btnDirBoth');
    const btnIn = document.getElementById('btnDirInbound');
    const btnOut = document.getElementById('btnDirOutbound');

    const activeCls = "px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center space-x-1 bg-[#002f5e] text-white shadow-xs";
    const idleCls = "px-2.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center space-x-1 text-slate-700 hover:text-gray-900";

    if (btnBoth) btnBoth.className = dir === 'both' ? activeCls : idleCls;
    if (btnIn) btnIn.className = dir === 'inbound' ? activeCls : idleCls;
    if (btnOut) btnOut.className = dir === 'outbound' ? activeCls : idleCls;

    if (dir === 'inbound') {
        setHudActiveTrain('inbound');
    } else if (dir === 'outbound') {
        setHudActiveTrain('outbound');
    }
}

// Toggle HUD Active Train
function setHudActiveTrain(train) {
    hudActiveTrain = train;
    const tabIn = document.getElementById('hudTabInbound');
    const tabOut = document.getElementById('hudTabOutbound');

    if (train === 'inbound') {
        if (tabIn) tabIn.className = "flex-1 py-1 rounded-md text-center bg-white text-emerald-800 shadow-2xs font-bold";
        if (tabOut) tabOut.className = "flex-1 py-1 rounded-md text-center text-gray-600 hover:text-gray-900";
    } else {
        if (tabIn) tabIn.className = "flex-1 py-1 rounded-md text-center text-gray-600 hover:text-gray-900";
        if (tabOut) tabOut.className = "flex-1 py-1 rounded-md text-center bg-white text-blue-800 shadow-2xs font-bold";
    }
    updateTelemetryHUD();
}

// Update HUD Display
function updateTelemetryHUD() {
    const isIb = hudActiveTrain === 'inbound';
    const step = isIb ? interpolatedStepsInbound[currentStepInbound] : interpolatedStepsOutbound[currentStepOutbound];
    if (!step) return;

    const elTitle = document.getElementById('hudTrainTitle');
    const elSpeed = document.getElementById('hudTrainSpeed');
    const elLoc = document.getElementById('hudTrainLoc');
    const elProgress = document.getElementById('hudTrainProgress');
    const elEta = document.getElementById('hudTrainEta');
    const elCustoms = document.getElementById('hudTrainCustoms');
    const elBar = document.getElementById('hudProgressBar');

    if (elTitle) {
        elTitle.textContent = isIb ? "Telemetri GPS KA 2518 (Inbound)" : "Telemetri GPS KA 2519 (Outbound)";
    }
    if (elSpeed) {
        elSpeed.textContent = `${step.speed} km/jam`;
        elSpeed.className = isIb ? 
            "font-mono text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200" :
            "font-mono text-[10px] font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200";
    }
    if (elLoc) elLoc.textContent = step.name;

    const pct = Math.min(100, Math.round((step.km / 54.8) * 100));
    if (elProgress) elProgress.textContent = `${step.km.toFixed(1)} / 54.8 km (${pct}%)`;
    if (elBar) elBar.style.width = `${pct}%`;

    const remainingKm = Math.max(0, 54.8 - step.km);
    const etaMin = Math.round((remainingKm / 54.8) * 110);
    if (elEta) {
        if (etaMin === 0) {
            elEta.textContent = isIb ? "Tiba di Siding Track-01 CIDP" : "Tiba di Stasiun Pasoso (Priok)";
            elEta.className = "text-emerald-600 font-bold";
        } else {
            elEta.textContent = `${etaMin} Menit Lagi`;
            elEta.className = "text-gray-900";
        }
    }
    if (elCustoms) {
        elCustoms.textContent = isIb ? "Jalur Hijau PLP (CEISA 4.0)" : "NPE Ekspor & VGM Valid";
    }
}

// Animation Controls
function toggleTrainAnimation() {
    if (!isTrainPlaying) {
        startTrainAnimation();
    } else {
        pauseTrainAnimation();
    }
}

function startTrainAnimation() {
    if (!trainMarkerInbound || !trainMarkerOutbound) return;
    if (currentStepInbound >= interpolatedStepsInbound.length - 1 && currentStepOutbound >= interpolatedStepsOutbound.length - 1) {
        currentStepInbound = 0;
        currentStepOutbound = 0;
    }
    isTrainPlaying = true;
    const btnText = document.getElementById('playTrainText');
    const btnIcon = document.getElementById('playTrainIcon');
    if (btnText) btnText.textContent = "Jeda Perjalanan KA";
    if (btnIcon) btnIcon.className = "fa-solid fa-pause text-[10px]";

    trainAnimInterval = setInterval(() => {
        let reachedIn = currentStepInbound >= interpolatedStepsInbound.length - 1;
        let reachedOut = currentStepOutbound >= interpolatedStepsOutbound.length - 1;

        if (corridorMode === 'both' || corridorMode === 'inbound') {
            if (!reachedIn) {
                currentStepInbound++;
                const stepIn = interpolatedStepsInbound[currentStepInbound];
                trainMarkerInbound.setLatLng([stepIn.lat, stepIn.lng]);
            }
        }
        if (corridorMode === 'both' || corridorMode === 'outbound') {
            if (!reachedOut) {
                currentStepOutbound++;
                const stepOut = interpolatedStepsOutbound[currentStepOutbound];
                trainMarkerOutbound.setLatLng([stepOut.lat, stepOut.lng]);
            }
        }

        updateTelemetryHUD();

        // Check completion
        const finished = (corridorMode === 'both' && reachedIn && reachedOut) ||
                         (corridorMode === 'inbound' && reachedIn) ||
                         (corridorMode === 'outbound' && reachedOut);

        if (finished) {
            pauseTrainAnimation();
            const btnText = document.getElementById('playTrainText');
            if (btnText) btnText.textContent = "Simulasikan Ulang";
            alert("Simulasi Kereta Api 2-Arah Selesai!\n\n• KA 2518 tiba di Siding Track-01 CIDP (Bongkar 48 TEU Impor ex-Priok)\n• KA 2519 tiba di Pasoso Pelabuhan Tanjung Priok (Siap muat 52 TEU Ekspor ke kapal laut)\n\nKedua rangkaian sukses bersilangan di petak jalan jalur ganda (DDT) Daop 1!");
        }
    }, 90);
}

function pauseTrainAnimation() {
    isTrainPlaying = false;
    if (trainAnimInterval) clearInterval(trainAnimInterval);
    const btnText = document.getElementById('playTrainText');
    const btnIcon = document.getElementById('playTrainIcon');
    if (btnText) btnText.textContent = "Lanjutkan Perjalanan KA";
    if (btnIcon) btnIcon.className = "fa-solid fa-play text-[10px]";
}

function resetTrainPosition() {
    pauseTrainAnimation();
    currentStepInbound = 0;
    currentStepOutbound = 0;
    if (interpolatedStepsInbound.length > 0 && trainMarkerInbound) {
        const step0 = interpolatedStepsInbound[0];
        trainMarkerInbound.setLatLng([step0.lat, step0.lng]);
    }
    if (interpolatedStepsOutbound.length > 0 && trainMarkerOutbound) {
        const step0Out = interpolatedStepsOutbound[0];
        trainMarkerOutbound.setLatLng([step0Out.lat, step0Out.lng]);
    }
    updateTelemetryHUD();
    const btnText = document.getElementById('playTrainText');
    if (btnText) btnText.textContent = "Simulasikan Perjalanan KA";
    flyToLocation('all');
}

// Navigasi Kamera GIS
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

// Inisialisasi onload
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
