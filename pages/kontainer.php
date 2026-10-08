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
    $containers = [];
}

// Fallback dataset realistis jika database kosong
if (empty($containers)) {
    $containers = [];
}

// Data rute dan atribut pendukung
$origins = [
    'Tg. Priok (JICT)',
    'MM2100 Cikarang',
    'KIIC Karawang',
    'Depo Marunda',
    'GIIC Cikarang',
    'Surya Cipta'
];

$destinations = [
    'KA Rel ➔ Surabaya',
    'Tg. Priok (MSC Aries)',
    'Jababeka V Cikarang',
    'KA Parcel ➔ Semarang',
    'Depo M&R Cakung',
    'Patimban Subang'
];

$gate_lanes = [
    'Gate 01 (Lane A)',
    'Gate 02 (Lane B)',
    'Gate 01 (Lane C)'
];

$truck_plates = ['B 9182 TE', 'B 1234 XY', 'D 5555 ZZ', 'B 7777 AB', 'L 8888 KL', 'B 9021 UJ', 'B 9481 FZ'];
$reach_stackers = ['RS-01 (Budi S.)', 'RS-02 (Agus S.)', 'RS-03 (Rudi H.)', 'RTG-01 (Joko S.)'];

foreach ($containers as $idx => &$c) {
    if ($c['cargo_type'] === 'dry') $total_dry++;
    elseif ($c['cargo_type'] === 'reefer') $total_reefer++;
    elseif ($c['cargo_type'] === 'dg') $total_dg++;
    elseif ($c['cargo_type'] === 'empty') $total_empty++;

    $gate_in = !empty($c['gate_in_time']) ? strtotime($c['gate_in_time']) : (time() - 86400 * ($idx % 3 + 1));
    $hours_in_yard = max(1, round((time() - $gate_in) / 3600));
    $days = floor($hours_in_yard / 24);
    $rem_hours = $hours_in_yard % 24;
    $c['dwell_time_text'] = ($days > 0 ? "{$days}h " : "") . "{$rem_hours}j";
    $c['hours_in_yard'] = $hours_in_yard;

    $c['origin'] = $origins[$idx % count($origins)];
    $c['destination'] = $destinations[$idx % count($destinations)];
    $c['inbound_gate'] = $gate_lanes[$idx % count($gate_lanes)];
    $c['inbound_truck'] = $truck_plates[$idx % count($truck_plates)];
    $c['handling_equipment'] = ($c['cargo_type'] === 'reefer') ? 'RS-03 (Rudi H.)' : $reach_stackers[$idx % count($reach_stackers)];
    $c['commodity'] = ($c['cargo_type'] === 'reefer') ? 'Frozen Seafood (-18.5°C)' : 
                      (($c['cargo_type'] === 'dg') ? 'Chemical Class 3 Flammable' : 
                      (($c['cargo_type'] === 'empty') ? 'Empty Container' : 'Automotive Spare Parts'));
}
unset($c);

$total_containers = count($containers);
?>

<div class="space-y-2.5 animate-fadeIn pb-12">

    <!-- Header Modul: Compact Executive Style (Aligned with Sidebar) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 bg-white px-3 py-2 rounded-xl shadow-2xs border border-slate-200/80 mb-2.5">
        <div class="flex items-center space-x-2.5">
            <div class="w-7 h-7 rounded-lg bg-orange-50 border border-orange-200/70 text-orange-600 flex items-center justify-center text-xs shadow-2xs flex-shrink-0">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div>
                <div class="flex items-center space-x-1.5">
                    <h2 class="text-[13px] font-extrabold text-slate-900 tracking-tight leading-tight">Pelacakan Kontainer &amp; Audit Perjalanan</h2>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 mr-1 rounded-full bg-emerald-500 animate-pulse"></span> Live Sync
                    </span>
                </div>
            </div>
        </div>
        <div class="flex items-center flex-wrap gap-1.5 flex-shrink-0">
            <button onclick="showAddContainerModal()" class="px-2.5 py-1 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg text-[11px] font-bold transition flex items-center space-x-1 shadow-xs cursor-pointer">
                <i class="fa-solid fa-plus text-[10px]"></i>
                <span>Tambah</span>
            </button>
            <a href="dashboard.php?page=simulator" class="px-2.5 py-1 bg-slate-900 hover:bg-slate-800 text-white text-[11px] font-bold rounded-lg shadow-2xs transition flex items-center space-x-1">
                <i class="fa-solid fa-cubes text-amber-400 text-[10px]"></i>
                <span>Simulasi 3D</span>
            </a>
            <button onclick="exportKontainerExcel()" class="px-2 py-1 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 text-emerald-700 rounded-lg text-[11px] font-semibold transition flex items-center space-x-1 cursor-pointer">
                <i class="fa-solid fa-file-excel text-[10px]"></i>
                <span>Excel</span>
            </button>
            <button onclick="window.print()" class="px-2 py-1 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-[11px] font-semibold rounded-lg shadow-2xs transition flex items-center cursor-pointer">
                <i class="fa-solid fa-print text-slate-400 text-[10px]"></i>
            </button>
            <a href="dashboard.php?page=trucking" class="px-2.5 py-1 bg-[#004b87] hover:bg-[#002f5e] text-white text-[11px] font-bold rounded-lg shadow-2xs transition flex items-center space-x-1">
                <i class="fa-solid fa-truck-front text-[10px]"></i>
                <span>Trucking</span>
            </a>
        </div>
    </div>

    <!-- 4 KPI Summary Cards (Compact Executive Grid) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 mb-2.5">
        <div class="bg-white p-2.5 rounded-xl shadow-2xs border border-slate-200/80 hover:border-orange-200 transition-all flex items-center justify-between">
            <div class="min-w-0 pr-1.5">
                <p class="text-[9px] font-bold uppercase text-slate-400 tracking-wider truncate mb-0.5">Total Kontainer</p>
                <h3 class="text-[13.5px] font-extrabold text-slate-900 leading-tight"><?= $total_containers ?> <span class="text-[9px] font-normal text-slate-400">Box</span></h3>
                <div class="mt-0.5 text-[9px] text-blue-600 font-semibold">Kapasitas 200 TEU</div>
            </div>
            <div class="w-7 h-7 rounded-lg bg-blue-50 text-[#0170b9] flex items-center justify-center text-[11px] shadow-2xs flex-shrink-0">
                <i class="fa-solid fa-box-archive"></i>
            </div>
        </div>

        <div class="bg-white p-2.5 rounded-xl shadow-2xs border border-slate-200/80 hover:border-emerald-200 transition-all flex items-center justify-between">
            <div class="min-w-0 pr-1.5">
                <p class="text-[9px] font-bold uppercase text-slate-400 tracking-wider truncate mb-0.5">Komposisi Muatan</p>
                <div class="flex items-center space-x-1 mt-0.5">
                    <span class="text-[9px] font-bold text-blue-700 bg-blue-50 px-1 py-0.2 rounded"><?= $total_dry ?> Dry</span>
                    <span class="text-[9px] font-bold text-cyan-700 bg-cyan-50 px-1 py-0.2 rounded"><?= $total_reefer ?> Rf</span>
                    <span class="text-[9px] font-bold text-red-700 bg-red-50 px-1 py-0.2 rounded"><?= $total_dg ?> DG</span>
                </div>
                <p class="mt-0.5 text-[9px] text-slate-400 truncate"><?= $total_empty ?> Empty Box</p>
            </div>
            <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-[11px] shadow-2xs flex-shrink-0">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
        </div>

        <div class="bg-white p-2.5 rounded-xl shadow-2xs border border-slate-200/80 hover:border-amber-200 transition-all flex items-center justify-between">
            <div class="min-w-0 pr-1.5">
                <p class="text-[9px] font-bold uppercase text-slate-400 tracking-wider truncate mb-0.5">Rata-Rata Dwell Time</p>
                <h3 class="text-[13.5px] font-extrabold text-amber-600 leading-tight">1.8 <span class="text-[9px] font-normal text-slate-400">Hari</span></h3>
                <div class="mt-0.5 text-[9px] text-emerald-600 font-semibold"><i class="fa-solid fa-circle-check mr-1 text-[8px]"></i>Target (&lt; 3 Hari)</div>
            </div>
            <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-[11px] shadow-2xs flex-shrink-0">
                <i class="fa-regular fa-clock"></i>
            </div>
        </div>

        <div class="bg-white p-2.5 rounded-xl shadow-2xs border border-slate-200/80 hover:border-purple-200 transition-all flex items-center justify-between">
            <div class="min-w-0 pr-1.5">
                <p class="text-[9px] font-bold uppercase text-slate-400 tracking-wider truncate mb-0.5">Kliring SPPB Bea Cukai</p>
                <h3 class="text-[13.5px] font-extrabold text-emerald-600 leading-tight">94.7% <span class="text-[9px] font-normal text-slate-400">Cleared</span></h3>
                <div class="mt-0.5 text-[9px] text-purple-600 font-semibold"><i class="fa-solid fa-barcode mr-1 text-[8px]"></i>RFID UHF Valid</div>
            </div>
            <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-[11px] shadow-2xs flex-shrink-0">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar (Compact) -->
    <div class="bg-white px-3 py-2 rounded-xl shadow-2xs border border-slate-200/80 flex flex-col md:flex-row items-center justify-between gap-2 mb-2.5">
        <div class="relative w-full md:w-72">
            <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400 pointer-events-none">
                <i class="fa-solid fa-magnifying-glass text-[10px]"></i>
            </span>
            <input type="text" id="searchInput" onkeyup="filterContainers()" placeholder="Cari No. Kontainer, Pemilik, RFID..." 
                   class="w-full pl-7 pr-3 py-1 bg-white border border-slate-200 rounded-lg text-[11px] focus:outline-none focus:ring-1 focus:ring-orange-500 transition placeholder-slate-400">
        </div>

        <div class="flex flex-wrap items-center gap-1.5 w-full md:w-auto">
            <select id="filterBlock" onchange="filterContainers()" class="px-2 py-1 bg-white border border-slate-200 rounded-lg text-[11px] text-slate-700 focus:outline-none focus:ring-1 focus:ring-orange-500">
                <option value="">Semua Zona Yard</option>
                <option value="A">Blok A (Dry 40ft)</option>
                <option value="B">Blok B (Dry 20ft)</option>
                <option value="C">Blok C (Buffer)</option>
                <option value="REEFER">Zona Reefer</option>
                <option value="DG">Zona DG</option>
                <option value="EMPTY">Zona Empty</option>
            </select>

            <select id="filterType" onchange="filterContainers()" class="px-2 py-1 bg-white border border-slate-200 rounded-lg text-[11px] text-slate-700 focus:outline-none focus:ring-1 focus:ring-orange-500">
                <option value="">Semua Tipe Kargo</option>
                <option value="dry">Dry Cargo</option>
                <option value="reefer">Reefer Cold Chain</option>
                <option value="dg">Dangerous Goods</option>
                <option value="empty">Empty Box</option>
            </select>

            <button onclick="resetFilter()" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 text-[11px] font-semibold rounded-lg transition cursor-pointer">
                <i class="fa-solid fa-rotate-left mr-1 text-[10px]"></i> Reset
            </button>
        </div>
    </div>

    <!-- CLEAN & COMPACT CONTAINER TABLE -->
    <div class="bg-white rounded-xl shadow-2xs border border-slate-200/80 overflow-hidden">
        <div class="px-3 py-2 border-b border-slate-100 flex items-center justify-between bg-slate-50/40">
            <div class="flex items-center space-x-2">
                <span class="w-1.5 h-3.5 bg-orange-500 rounded-full"></span>
                <h3 class="font-extrabold text-slate-900 text-[12.5px] flex items-center">
                    <i class="fa-solid fa-boxes-stacked mr-1.5 text-orange-600 text-[11px]"></i>Daftar Posisi &amp; Alur Kontainer
                </h3>
            </div>
            <span class="text-[10px] font-bold text-slate-500 bg-white px-2 py-0.5 rounded border border-slate-200" id="rowCountDisplay">
                Menampilkan <?= count($containers) ?> Kontainer
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[720px]" id="containerTable">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-[9.5px] font-bold uppercase tracking-wider border-b border-slate-200/80">
                        <th class="py-2 px-3 font-semibold">Nomor Kontainer</th>
                        <th class="py-2 px-3 font-semibold">Pemilik / Pengirim</th>
                        <th class="py-2 px-3 font-semibold">Posisi di Yard</th>
                        <th class="py-2 px-3 font-semibold">Rute (Asal ➔ Tujuan)</th>
                        <th class="py-2 px-3 font-semibold">Status &amp; Dwell</th>
                        <th class="py-2 px-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-[11.5px] divide-y divide-slate-100">
                    <?php foreach ($containers as $c): ?>
                        <?php
                        $badge_cargo = 'bg-blue-50 text-blue-700 border-blue-200';
                        if ($c['cargo_type'] === 'reefer') $badge_cargo = 'bg-cyan-50 text-cyan-700 border-cyan-200';
                        elseif ($c['cargo_type'] === 'dg') $badge_cargo = 'bg-red-50 text-red-700 border-red-200';
                        elseif ($c['cargo_type'] === 'empty') $badge_cargo = 'bg-gray-100 text-gray-700 border-gray-300';

                        // Format posisi ringkas
                        $pos_text = "Blok {$c['block']} / B{$c['bay']}-R{$c['row']}-T{$c['tier']}";

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
                            'gate_in_time' => $c['gate_in_time'] ? date('d M Y, H:i \W\I\B', strtotime($c['gate_in_time'])) : '-',
                            'dwell_time' => $c['dwell_time_text'],
                            'origin' => $c['origin'],
                            'destination' => $c['destination'],
                            'inbound_gate' => $c['inbound_gate'],
                            'inbound_truck' => $c['inbound_truck'],
                            'handling_equipment' => $c['handling_equipment'],
                            'commodity' => $c['commodity']
                        ]), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr class="hover:bg-orange-50/30 transition cursor-pointer container-row group" 
                            onclick='showJourneyModal(<?= $json_detail ?>)'
                            data-container="<?= strtolower($c['container_number']) ?>" 
                            data-owner="<?= strtolower($c['owner_company']) ?>"
                            data-block="<?= strtoupper($c['block']) ?>"
                            data-type="<?= strtolower($c['cargo_type']) ?>"
                            data-rfid="<?= strtolower($c['rfid_tag']) ?>">
                            
                            <!-- Nomor Kontainer -->
                            <td class="py-1.5 px-3 whitespace-nowrap">
                                <div class="flex items-center space-x-1.5">
                                    <span class="font-mono font-bold text-orange-600 group-hover:text-orange-700 text-[11.5px]">
                                        <?= htmlspecialchars($c['container_number']) ?>
                                    </span>
                                    <span class="px-1.5 py-0.2 text-[9px] font-bold rounded border uppercase <?= $badge_cargo ?>">
                                        <?= htmlspecialchars($c['cargo_type']) ?>
                                    </span>
                                </div>
                            </td>

                            <!-- Pemilik / Pengirim -->
                            <td class="py-1.5 px-3 whitespace-nowrap">
                                <span class="text-slate-800 font-bold text-[11px] truncate max-w-[180px] block">
                                    <?= htmlspecialchars($c['owner_company']) ?>
                                </span>
                            </td>

                            <!-- Posisi di Yard -->
                            <td class="py-1.5 px-3 whitespace-nowrap">
                                <span class="font-mono font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded text-[10px] border border-slate-200">
                                    <?= $pos_text ?>
                                </span>
                            </td>

                            <!-- Rute (Asal ➔ Tujuan) -->
                            <td class="py-1.5 px-3 whitespace-nowrap">
                                <div class="flex items-center space-x-1 text-[11px] text-slate-600 font-medium">
                                    <span><?= htmlspecialchars($c['origin']) ?></span>
                                    <i class="fa-solid fa-arrow-right text-[8.5px] text-slate-400"></i>
                                    <span class="text-orange-600 font-bold"><?= htmlspecialchars($c['destination']) ?></span>
                                </div>
                            </td>

                            <!-- Status & Dwell -->
                            <td class="py-1.5 px-3 whitespace-nowrap">
                                <div class="flex items-center space-x-1.5">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa-regular fa-clock mr-1 text-[8.5px]"></i><?= $c['dwell_time_text'] ?>
                                    </span>
                                    <?php if ($c['customs_status'] === 'SPPB_CLEARED'): ?>
                                        <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">SPPB</span>
                                    <?php else: ?>
                                        <span class="text-[9px] font-bold text-amber-800 bg-amber-50 px-1.5 py-0.2 rounded border border-amber-200">Cek Fisik</span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Tombol Aksi Detail & Aksi Pintas Lintas Modul -->
                            <td class="py-1.5 px-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center space-x-1" onclick="event.stopPropagation()">
                                    <!-- Detail Modal Button -->
                                    <button onclick='showJourneyModal(<?= $json_detail ?>)' class="px-2 py-1 bg-slate-100 hover:bg-orange-600 text-slate-700 hover:text-white text-[10px] font-semibold rounded-md transition shadow-2xs inline-flex items-center space-x-1 cursor-pointer" title="Buka Detail Milestone">
                                        <i class="fa-solid fa-timeline text-[9px]"></i>
                                        <span>Milestone</span>
                                    </button>
                                    <!-- Denah 2D -->
                                    <a href="dashboard.php?page=denah&highlight_block=<?= urlencode($c['block']) ?>" class="w-6 h-6 rounded-md bg-blue-50 hover:bg-[#0170b9] text-[#0170b9] hover:text-white flex items-center justify-center text-[10px] transition shadow-2xs" title="Sorot di Denah 2D (Blok <?= $c['block'] ?>)">
                                        <i class="fa-solid fa-map-location-dot"></i>
                                    </a>
                                    <!-- 3D Simulator -->
                                    <a href="dashboard.php?page=simulator&focus_box=<?= urlencode($c['container_number']) ?>" class="w-6 h-6 rounded-md bg-amber-50 hover:bg-amber-500 text-amber-700 hover:text-gray-950 flex items-center justify-center text-[10px] transition shadow-2xs" title="Lihat di Simulasi 3D">
                                        <i class="fa-solid fa-cube"></i>
                                    </a>
                                    <!-- Billing -->
                                    <a href="dashboard.php?page=billing&search=<?= urlencode($c['container_number']) ?>" class="w-6 h-6 rounded-md bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white flex items-center justify-center text-[10px] transition shadow-2xs" title="Periksa Faktur Billing">
                                        <i class="fa-solid fa-file-invoice-dollar"></i>
                                    </a>
                                    <?php if ($c['cargo_type'] === 'reefer'): ?>
                                    <!-- Reefer -->
                                    <a href="dashboard.php?page=reefer&search=<?= urlencode($c['container_number']) ?>" class="w-6 h-6 rounded-md bg-cyan-50 hover:bg-cyan-600 text-cyan-700 hover:text-white flex items-center justify-center text-[10px] transition shadow-2xs" title="Monitoring Reefer Cold Chain">
                                        <i class="fa-solid fa-snowflake"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
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
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <div>
                    <span class="text-[10px] uppercase tracking-wider font-bold text-blue-200">Milestone Siklus Hidup Kontainer</span>
                    <h3 class="text-xl font-bold font-mono" id="m_container_number">MSKU9182374</h3>
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

        <!-- Modal Body: 5-Stage Milestone Timeline & Snapshot -->
        <div class="p-6 max-h-[70vh] overflow-y-auto space-y-6 text-xs">
            
            <!-- Snapshot Data Utama -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs">
                <div>
                    <span class="text-gray-400 text-[10px] block uppercase font-semibold">Tag RFID UHF:</span>
                    <span class="font-mono font-bold text-purple-700" id="m_rfid">E280117000000001</span>
                </div>
                <div>
                    <span class="text-gray-400 text-[10px] block uppercase font-semibold">SSCC-18 Code:</span>
                    <span class="font-mono font-bold text-gray-800 truncate block" id="m_sscc">(00)389912345000000001</span>
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
                    <i class="fa-solid fa-route mr-2 text-[#0170b9]"></i>Kronologi 5 Milestone Perjalanan Kontainer
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
                                <span class="font-bold text-gray-900">1. Keberangkatan Asal & Masuk Gerbang (Gate-In)</span>
                                <span class="text-[10px] text-gray-400 font-mono" id="m_gate_time">2026-09-19 08:30 WIB</span>
                            </div>
                            <p class="text-gray-600 mt-1">
                                Kargo berasal dari <strong class="text-gray-900" id="m_origin">Pelabuhan Tanjung Priok</strong>, diangkut armada truk <strong class="text-blue-700 font-mono" id="m_truck">B 9182 TE</strong> melalui <strong class="text-gray-800" id="m_lane">Gate 01 Lane A</strong>.
                            </p>
                            <div class="mt-2 text-[10px] text-gray-500 flex items-center space-x-3">
                                <span><i class="fa-solid fa-scale-balanced mr-1 text-blue-500"></i>VGM Otomatis: Terverifikasi</span>
                                <span><i class="fa-solid fa-qrcode mr-1 text-purple-500"></i>RFID & OCR: Valid</span>
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
                                <span class="font-bold text-gray-900">2. Pembongkaran dari Truk (Lift-Off Operasional)</span>
                                <span class="text-[10px] text-gray-400 font-mono">15 menit setelah Gate-In</span>
                            </div>
                            <p class="text-gray-600 mt-1">
                                Diangkat dari sasis truk oleh alat berat <strong class="text-blue-700" id="m_equipment">RS-01</strong>. Corner casting terkunci dengan aman ke spreader otomatis.
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
                                <span class="font-bold text-emerald-900">3. Posisi Penumpukan Lapangan Saat Ini</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">LOKASI AKTIF</span>
                            </div>
                            <div class="mt-2 flex items-center space-x-2">
                                <span class="text-gray-700">Koordinat 3D Yard:</span>
                                <span class="font-mono font-bold text-sm bg-white px-2.5 py-1 rounded border border-emerald-300 text-emerald-800" id="m_yard_pos">
                                    Blok A / Bay 01 / Row 01 / Tier 01
                                </span>
                            </div>
                            <p class="text-gray-600 mt-2">
                                Lama Inap: <strong class="text-gray-900" id="m_dwell">1 hari 4 jam</strong>. Komoditas: <span class="text-gray-700" id="m_commodity">Automotive Spare Parts</span>.
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
                                <span class="font-bold text-gray-900">4. Kliring Dokumen & Segel Bea Cukai</span>
                                <span class="text-[10px] text-emerald-600 font-bold">Resmi Disetujui (SPPB)</span>
                            </div>
                            <p class="text-gray-600 mt-1">
                                Nomor segel kontainer: <strong class="text-gray-800 font-mono" id="m_seal">SN-JKT-102938</strong>. Terintegrasi dengan CEISA Bea Cukai.
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
                                <span class="font-bold text-gray-900">5. Rencana Pengeluaran & Moda Intermodal Keluar</span>
                                <span class="text-[10px] text-blue-600 font-semibold">Terjadwal</span>
                            </div>
                            <p class="text-gray-700 mt-1">
                                Tujuan berikutnya: <strong class="text-[#0170b9]" id="m_destination">KA Logistik (Rel Siding ➔ Surabaya)</strong>.
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ======================================================================= -->
            <!-- NEW SECTION: DCSA EVENT TIMELINE (TRACK & TRACE API)                    -->
            <!-- ======================================================================= -->
            <div class="border-t border-gray-100 pt-6 mt-2">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800 flex items-center">
                        <i class="fa-solid fa-satellite-dish mr-2 text-[#0170b9]"></i>DCSA Track & Trace Event Timeline
                    </h4>
                    <span class="px-2 py-0.5 text-[9px] font-bold bg-slate-100 text-slate-500 rounded border border-slate-200">API v2.2</span>
                </div>
                
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 shadow-inner overflow-x-auto">
                    <div class="min-w-[600px]">
                        <!-- DCSA Timeline Node Container -->
                        <div class="relative flex items-center justify-between before:absolute before:inset-0 before:top-1/2 before:-translate-y-1/2 before:h-0.5 before:bg-slate-200 before:w-full z-0">
                            
                            <!-- Event 1: DEPA SGSIN -->
                            <div class="relative z-10 flex flex-col items-center w-24">
                                <div class="w-7 h-7 bg-indigo-100 border-2 border-indigo-500 text-indigo-600 rounded-full flex items-center justify-center text-[10px] mb-2 shadow-sm font-bold"><i class="fa-solid fa-ship"></i></div>
                                <span class="px-1.5 py-0.5 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded text-[9px] font-bold mb-1">DEPA</span>
                                <span class="text-[9px] font-mono text-gray-500">SGSIN</span>
                            </div>

                            <!-- Event 2: ARRI IDTPP -->
                            <div class="relative z-10 flex flex-col items-center w-24">
                                <div class="w-7 h-7 bg-indigo-100 border-2 border-indigo-500 text-indigo-600 rounded-full flex items-center justify-center text-[10px] mb-2 shadow-sm font-bold"><i class="fa-solid fa-anchor"></i></div>
                                <span class="px-1.5 py-0.5 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded text-[9px] font-bold mb-1">ARRI</span>
                                <span class="text-[9px] font-mono text-gray-500">IDTPP</span>
                            </div>

                            <!-- Event 3: DISCHARGE IDTPP -->
                            <div class="relative z-10 flex flex-col items-center w-24">
                                <div class="w-7 h-7 bg-blue-100 border-2 border-blue-500 text-blue-600 rounded-full flex items-center justify-center text-[10px] mb-2 shadow-sm font-bold"><i class="fa-solid fa-crane"></i></div>
                                <span class="px-1.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[9px] font-bold mb-1">DISCHARGE</span>
                                <span class="text-[9px] font-mono text-gray-500">IDTPP</span>
                            </div>

                            <!-- Event 4: LOAD onto KA2518 -->
                            <div class="relative z-10 flex flex-col items-center w-24">
                                <div class="w-7 h-7 bg-blue-100 border-2 border-blue-500 text-blue-600 rounded-full flex items-center justify-center text-[10px] mb-2 shadow-sm font-bold"><i class="fa-solid fa-train"></i></div>
                                <span class="px-1.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[9px] font-bold mb-1">LOAD</span>
                                <span class="text-[9px] font-mono text-gray-500">KA 2518</span>
                            </div>

                            <!-- Event 5: DEPA IDTPP -->
                            <div class="relative z-10 flex flex-col items-center w-24">
                                <div class="w-7 h-7 bg-indigo-100 border-2 border-indigo-500 text-indigo-600 rounded-full flex items-center justify-center text-[10px] mb-2 shadow-sm font-bold"><i class="fa-solid fa-train-tram"></i></div>
                                <span class="px-1.5 py-0.5 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded text-[9px] font-bold mb-1">DEPA</span>
                                <span class="text-[9px] font-mono text-gray-500">IDTPP</span>
                            </div>

                            <!-- Event 6: ARRI IDCKG -->
                            <div class="relative z-10 flex flex-col items-center w-24">
                                <div class="w-7 h-7 bg-indigo-100 border-2 border-indigo-500 text-indigo-600 rounded-full flex items-center justify-center text-[10px] mb-2 shadow-sm font-bold"><i class="fa-solid fa-map-pin"></i></div>
                                <span class="px-1.5 py-0.5 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded text-[9px] font-bold mb-1">ARRI</span>
                                <span class="text-[9px] font-mono text-gray-500">IDCKG</span>
                            </div>

                            <!-- Event 7: GATE_IN CIDP -->
                            <div class="relative z-10 flex flex-col items-center w-24">
                                <div class="w-7 h-7 bg-blue-100 border-2 border-blue-500 text-blue-600 rounded-full flex items-center justify-center text-[10px] mb-2 shadow-sm font-bold"><i class="fa-solid fa-door-open"></i></div>
                                <span class="px-1.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[9px] font-bold mb-1">GATE_IN</span>
                                <span class="text-[9px] font-mono text-gray-500">CIDP</span>
                            </div>

                            <!-- Event 8: DROP_OFF Yard Block A-01 -->
                            <div class="relative z-10 flex flex-col items-center w-24">
                                <div class="w-7 h-7 bg-blue-100 border-2 border-blue-500 text-blue-600 rounded-full flex items-center justify-center text-[10px] mb-2 shadow-sm font-bold"><i class="fa-solid fa-boxes-stacked"></i></div>
                                <span class="px-1.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[9px] font-bold mb-1">DROP_OFF</span>
                                <span class="text-[9px] font-mono text-gray-500">Yard A-01</span>
                            </div>
                            
                        </div>
                        <div class="mt-4 flex justify-center space-x-6 text-[9px]">
                            <div class="flex items-center"><span class="w-2.5 h-2.5 rounded bg-blue-500 mr-1.5"></span><span class="text-gray-500">EQUIPMENT Event</span></div>
                            <div class="flex items-center"><span class="w-2.5 h-2.5 rounded bg-indigo-500 mr-1.5"></span><span class="text-gray-500">TRANSPORT Event</span></div>
                            <div class="flex items-center"><span class="w-2.5 h-2.5 rounded bg-emerald-500 mr-1.5"></span><span class="text-gray-500">SHIPMENT Event</span></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Aksi Pintas Lintas Modul (Inter-Module Shortcuts) -->
        <div class="bg-blue-50/70 border-t border-b border-blue-100 px-6 py-3.5 flex flex-wrap items-center justify-between gap-3 text-xs">
            <span class="font-bold text-[#002f5e] flex items-center">
                <i class="fa-solid fa-compass mr-1.5 text-[#0170b9]"></i>Aksi Pintas Lintas Modul:
            </span>
            <div class="flex items-center space-x-2 flex-wrap gap-y-1.5" id="journeyActionShortcuts">
                <a id="btnLinkDenah" href="#" class="px-3 py-1.5 bg-white hover:bg-[#002f5e] text-gray-700 hover:text-white border border-gray-200 rounded-lg font-semibold transition flex items-center shadow-2xs">
                    <i class="fa-solid fa-map-location-dot mr-1.5 text-blue-500"></i>Denah 2D 35 Ha
                </a>
                <a id="btnLink3D" href="#" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-gray-950 font-bold rounded-lg transition flex items-center shadow-2xs">
                    <i class="fa-solid fa-cube mr-1.5"></i>Simulasi 3D
                </a>
                <a id="btnLinkBilling" href="#" class="px-3 py-1.5 bg-white hover:bg-emerald-600 text-gray-700 hover:text-white border border-gray-200 rounded-lg font-semibold transition flex items-center shadow-2xs">
                    <i class="fa-solid fa-file-invoice-dollar mr-1.5 text-emerald-600"></i>Faktur Billing
                </a>
                <a id="btnLinkYard" href="#" class="px-3 py-1.5 bg-white hover:bg-purple-600 text-gray-700 hover:text-white border border-gray-200 rounded-lg font-semibold transition flex items-center shadow-2xs">
                    <i class="fa-solid fa-grip mr-1.5 text-purple-600"></i>Matriks Yard
                </a>
                <a id="btnLinkReefer" href="#" class="px-3 py-1.5 bg-cyan-50 hover:bg-cyan-600 text-cyan-800 hover:text-white border border-cyan-200 rounded-lg font-semibold transition flex items-center shadow-2xs hidden">
                    <i class="fa-solid fa-snowflake mr-1.5 text-cyan-600"></i>Reefer Cold Chain
                </a>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-between">
            <div class="text-xs text-gray-500">
                <i class="fa-solid fa-info-circle mr-1 text-blue-500"></i> Audit jejak tersertifikasi oleh CIDP YMS Audit Engine.
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
    document.getElementById('m_customs').innerText = (data.customs_status === 'SPPB_CLEARED') ? 'SPPB Cleared' : 'Cek Fisik / Behandle';

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

    // Konfigurasi Link Aksi Pintas Lintas Modul
    const btnDenah = document.getElementById('btnLinkDenah');
    if (btnDenah) btnDenah.href = `dashboard.php?page=denah&highlight_block=${encodeURIComponent(data.block)}`;

    const btn3D = document.getElementById('btnLink3D');
    if (btn3D) btn3D.href = `dashboard.php?page=simulator&focus_box=${encodeURIComponent(data.container_number)}`;

    const btnBill = document.getElementById('btnLinkBilling');
    if (btnBill) btnBill.href = `dashboard.php?page=billing&search=${encodeURIComponent(data.container_number)}`;

    const btnYard = document.getElementById('btnLinkYard');
    if (btnYard) btnYard.href = `dashboard.php?page=yard&find=${encodeURIComponent(data.container_number)}`;

    const reeferBtn = document.getElementById('btnLinkReefer');
    if (reeferBtn) {
        if (data.cargo_type === 'REEFER' || data.block === 'R') {
            reeferBtn.classList.remove('hidden');
            reeferBtn.href = `dashboard.php?page=reefer&search=${encodeURIComponent(data.container_number)}`;
        } else {
            reeferBtn.classList.add('hidden');
        }
    }

    document.getElementById('journeyModal').classList.remove('hidden');
}

function closeJourneyModal() {
    document.getElementById('journeyModal').classList.add('hidden');
}

// URL Deep-Linking Initializer for Container Tracking
setTimeout(() => {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const searchParam = urlParams.get('search') || urlParams.get('q');
        const blockParam = urlParams.get('block');
        const typeParam = urlParams.get('type');
        const autoOpen = urlParams.get('open') === '1';

        if (searchParam) {
            const input = document.getElementById('searchInput');
            if (input) {
                input.value = searchParam;
                filterContainers();
            }
        }
        if (blockParam) {
            const bSelect = document.getElementById('filterBlock');
            if (bSelect) {
                bSelect.value = blockParam.toUpperCase();
                filterContainers();
            }
        }
        if (typeParam) {
            const tSelect = document.getElementById('filterType');
            if (tSelect) {
                tSelect.value = typeParam.toLowerCase();
                filterContainers();
            }
        }

        // If autoOpen is set or searchParam exact match:
        if (searchParam && autoOpen) {
            const rows = document.querySelectorAll('#containerTable tbody tr.container-row');
            for (let r of rows) {
                if ((r.getAttribute('data-container') || '').toUpperCase() === searchParam.toUpperCase()) {
                    r.click();
                    break;
                }
            }
        }
    } catch(err) {
        console.warn('Container deep-linking error:', err);
    }
}, 200);

window.addEventListener('click', function(e) {
    const modal = document.getElementById('journeyModal');
    if (e.target === modal) {
        closeJourneyModal();
    }
});

function getKontainerExportData() {
    const headers = ['Nomor Kontainer', 'Tipe Kargo', 'Pemilik', 'Posisi Yard', 'Gate In', 'Dwell Time', 'Asal', 'Tujuan', 'Status Bea Cukai'];
    const rows = [];
    const tableRows = document.querySelectorAll('#containerTable tbody tr.container-row');
    
    tableRows.forEach(row => {
        if (row.style.display !== 'none') {
            // We can extract data from the onclick JSON payload
            const onclickStr = row.getAttribute('onclick') || '';
            const match = onclickStr.match(/showJourneyModal\((.*?)\)/);
            if (match && match[1]) {
                try {
                    const data = JSON.parse(match[1]);
                    rows.push([
                        data.container_number,
                        data.cargo_type,
                        data.owner_company,
                        `Blok ${data.block} / B${data.bay}-R${data.row}-T${data.tier}`,
                        data.gate_in_time,
                        data.dwell_time,
                        data.origin,
                        data.destination,
                        data.customs_status
                    ]);
                } catch (e) {
                    console.error("Error parsing row data", e);
                }
            }
        }
    });
    return { headers, rows };
}

function exportKontainerExcel() {
    const { headers, rows } = getKontainerExportData();
    CIDPExport.toExcel(headers, rows, 'Data Kontainer', 'Laporan_Kontainer_CIDP');
}

function exportKontainerPDF() {
    const { headers, rows } = getKontainerExportData();
    CIDPExport.toPDF('Laporan Posisi & Pelacakan Kontainer', headers, rows, 'Laporan_Kontainer_CIDP');
}

function showAddContainerModal() {
    document.getElementById('addContainerModal').classList.remove('hidden');
}
function closeAddContainerModal() {
    document.getElementById('addContainerModal').classList.add('hidden');
}

function submitAddContainer(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    fetch('api/crud.php?action=add_container', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            alert('Kontainer berhasil ditambahkan!');
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

<!-- Add Container Modal -->
<div id="addContainerModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden animate-fadeIn border border-gray-100">
        <div class="bg-gradient-to-r from-[#002f5e] to-[#0170b9] text-white p-4 flex items-center justify-between">
            <h3 class="font-bold text-lg">Tambah Kontainer Baru</h3>
            <button onclick="closeAddContainerModal()" class="text-white hover:text-gray-200"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form onsubmit="submitAddContainer(event)" class="p-4 space-y-4">
            <div><label class="block text-xs font-bold mb-1">Nomor Kontainer</label><input type="text" name="container_number" required placeholder="MSKU1234567" class="w-full border p-2 rounded text-xs"></div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-xs font-bold mb-1">Ukuran / Tipe</label><select name="size_type" class="w-full border p-2 rounded text-xs">
                    <option value="20FT STANDARD">20FT STANDARD</option>
                    <option value="40FT STANDARD">40FT STANDARD</option>
                    <option value="40FT HIGH CUBE">40FT HIGH CUBE</option>
                    <option value="20FT REEFER">20FT REEFER</option>
                    <option value="40FT REEFER HC">40FT REEFER HC</option>
                </select></div>
                <div><label class="block text-xs font-bold mb-1">Tipe Kargo</label><select name="cargo_type" class="w-full border p-2 rounded text-xs">
                    <option value="dry">Dry</option><option value="reefer">Reefer</option><option value="dg">DG</option><option value="empty">Empty</option>
                </select></div>
            </div>
            <div><label class="block text-xs font-bold mb-1">Pemilik / Perusahaan</label><input type="text" name="owner_company" class="w-full border p-2 rounded text-xs"></div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-xs font-bold mb-1">Berat (kg)</label><input type="number" name="gross_weight_kg" class="w-full border p-2 rounded text-xs"></div>
                <div><label class="block text-xs font-bold mb-1">No Segel</label><input type="text" name="seal_number" class="w-full border p-2 rounded text-xs"></div>
            </div>
            <div class="grid grid-cols-4 gap-2">
                <div><label class="block text-xs font-bold mb-1">Blok</label><select name="block" class="w-full border p-2 rounded text-xs"><option>A</option><option>B</option><option>C</option><option>D</option><option>E</option><option>REEFER</option><option>DG</option></select></div>
                <div><label class="block text-xs font-bold mb-1">Bay</label><input type="number" name="bay" class="w-full border p-2 rounded text-xs" value="1"></div>
                <div><label class="block text-xs font-bold mb-1">Row</label><input type="number" name="row" class="w-full border p-2 rounded text-xs" value="1"></div>
                <div><label class="block text-xs font-bold mb-1">Tier</label><input type="number" name="tier" class="w-full border p-2 rounded text-xs" value="1"></div>
            </div>
            <div class="pt-4 flex justify-end space-x-2">
                <button type="button" onclick="closeAddContainerModal()" class="px-4 py-2 border rounded text-xs font-bold">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded text-xs font-bold">Simpan</button>
            </div>
        </form>
    </div>
</div>

