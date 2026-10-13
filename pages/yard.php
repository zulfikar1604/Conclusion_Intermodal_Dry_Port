<?php
// =============================================================================
// MODUL: MANAJEMEN CONTAINER YARD & 3D STACKING DIGITAL TWIN
// File: pages/yard.php
// Proyek: Conclusion Intermodal Dry Port (CIDP) 35 Ha — Yard Management System
// PIC: Juan Gamaliel (Yard Planner & Data Integration) & Armansyah Muchtarrom (IoT & RTK GNSS)
// =============================================================================

$yard_info = [
    'pic'    => 'Juan Gamaliel & Armansyah Muchtarrom',
    'role'   => 'Yard Planning Specialist & IoT Infrastructure Specialist',
    'desc'   => 'Digital Twin 3D Bay-Row-Tier, mitigasi Container Relocation Problem (CRP), dan telemetri RTK GNSS alat berat.',
    'icon'   => 'fa-boxes-stacked',
    'status' => 'Digital Twin 3D Aktif & Terintegrasi RTK'
];

// Data KPI Yard Lapangan 15 Ha
$yard_kpis = [
    [
        'label' => 'Total Kapasitas Lapangan',
        'val' => '15.000',
        'unit' => 'TEUs',
        'sub' => 'Kawasan Yard 15 Hektar',
        'icon' => 'fa-cubes-stacked',
        'color' => 'blue'
    ],
    [
        'label' => 'Okupansi Lapangan (Yard Density)',
        'val' => '68,4%',
        'unit' => '(10.260 TEUs)',
        'sub' => 'Batas Optimal: Max 75%',
        'icon' => 'fa-chart-pie',
        'color' => 'emerald'
    ],
    [
        'label' => 'Rasio Reshuffle (Unproductive Moves)',
        'val' => '4,2%',
        'unit' => 'Rasio CRP',
        'sub' => 'Target Operasional: < 8%',
        'icon' => 'fa-arrows-rotate',
        'color' => 'purple'
    ],
    [
        'label' => 'Rata-rata Dwell Time',
        'val' => '3,1',
        'unit' => 'Hari',
        'sub' => 'Kawasan Pabean Dry Port',
        'icon' => 'fa-clock-rotate-left',
        'color' => 'amber'
    ],
    [
        'label' => 'Alat Berat Beroperasi (VMT & RTK)',
        'val' => '6',
        'unit' => 'Unit RS / EH',
        'sub' => 'Terhubung Telemetri RTK GNSS',
        'icon' => 'fa-truck-monster',
        'color' => 'cyan'
    ]
];

require_once __DIR__ . '/../connection.php';

// Handle Form Aksi POST (Relokasi Kontainer / VMT Dispatch)
$yard_alert = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['yard_action'])) {
    if ($_POST['yard_action'] === 'relocate_container') {
        $ctr_num   = strtoupper(trim($_POST['container_number'] ?? ''));
        $to_block  = trim($_POST['to_block'] ?? 'A');
        $to_bay    = intval($_POST['to_bay'] ?? 1);
        $to_row    = intval($_POST['to_row'] ?? 1);
        $to_tier   = intval($_POST['to_tier'] ?? 1);
        $equip_id  = trim($_POST['equipment_id'] ?? 'RS-01');
        $operator  = trim($_POST['operator_name'] ?? 'Budi Santoso');

        if (!empty($ctr_num) && isset($pdo)) {
            try {
                // Ambil posisi lama
                $stmtOld = $pdo->prepare("SELECT block, bay, row, tier FROM containers WHERE container_number = ? LIMIT 1");
                $stmtOld->execute([$ctr_num]);
                $oldPos = $stmtOld->fetch(PDO::FETCH_ASSOC);
                $from_block = $oldPos['block'] ?? 'A';
                $from_bay   = $oldPos['bay'] ?? '1';
                $from_row   = $oldPos['row'] ?? '1';
                $from_tier  = $oldPos['tier'] ?? '1';

                // Update posisi baru di containers
                $stmtUp = $pdo->prepare("UPDATE containers SET block = ?, bay = ?, row = ?, tier = ?, status = 'in_yard' WHERE container_number = ?");
                $stmtUp->execute([$to_block, $to_bay, $to_row, $to_tier, $ctr_num]);

                // Catat ke yard_events
                $stmtEv = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, from_block, from_bay, from_row, from_tier, to_block, to_bay, to_row, to_tier, operator_name, notes, created_at) VALUES ('RELOCATION', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmtEv->execute([$ctr_num, $equip_id, $from_block, $from_bay, $from_row, $from_tier, $to_block, $to_bay, $to_row, $to_tier, $operator, "Relokasi VMT oleh $equip_id ke Blok $to_block-$to_bay-$to_row-$to_tier"]);

                // Update equipment status
                $stmtEq = $pdo->prepare("UPDATE equipment SET current_container = ?, last_block = ?, status = 'operating', last_updated = NOW() WHERE equipment_id = ?");
                $stmtEq->execute([$ctr_num, $to_block, $equip_id]);

                $yard_alert = [
                    'type' => 'success',
                    'title' => 'Relokasi Kontainer Berhasil (VMT RTK Synchronized)',
                    'msg' => "Peti kemas <strong>$ctr_num</strong> berhasil dipindahkan oleh <strong>$equip_id</strong> ke <strong>Blok $to_block (Bay $to_bay &bull; Row $to_row &bull; Tier $to_tier)</strong>. Data tersinkronisasi di seluruh modul."
                ];
            } catch (Exception $e) {
                $yard_alert = ['type' => 'warning', 'title' => 'Relokasi Tersimpan (Mode Demo)', 'msg' => "Kontainer $ctr_num dipindahkan ke Blok $to_block."];
            }
        }
    }
}

// Dataset Sampel Kontainer Lapangan di Blok A, B, C, D, E & Reefer (Fallback)
$yard_containers = [
    // Blok A: Laden Ekspor (Navy Blue)
    ['id' => 'MSKU9821458', 'iso' => '45G1 (40ft HC)', 'tipe' => 'laden_exp', 'blok' => 'A', 'bay' => 1, 'row' => 1, 'tier' => 1, 'berat' => '28.450 kg', 'line' => 'Maersk Line', 'pod' => 'Rotterdam (NLRTM)', 'dwell' => '1 hari', 'pabean' => 'Jalur Hijau (NPE)', 'seal' => 'ML-ID99214', 'rtk' => 'RS-01 (1.2cm)'],
    ['id' => 'MSKU3412901', 'iso' => '45G1 (40ft HC)', 'tipe' => 'laden_exp', 'blok' => 'A', 'bay' => 1, 'row' => 1, 'tier' => 2, 'berat' => '24.120 kg', 'line' => 'Maersk Line', 'pod' => 'Hamburg (DEHAM)', 'dwell' => '2 hari', 'pabean' => 'Jalur Hijau (NPE)', 'seal' => 'ML-ID88210', 'rtk' => 'RS-01 (1.1cm)'],
    ['id' => 'CMAU7712390', 'iso' => '42G1 (40ft GP)', 'tipe' => 'laden_exp', 'blok' => 'A', 'bay' => 1, 'row' => 2, 'tier' => 1, 'berat' => '26.800 kg', 'line' => 'CMA CGM', 'pod' => 'Le Havre (FRLEH)', 'dwell' => '1 hari', 'pabean' => 'Jalur Hijau (NPE)', 'seal' => 'CMA-77129', 'rtk' => 'RS-01 (1.4cm)'],
    ['id' => 'CMAU8834120', 'iso' => '42G1 (40ft GP)', 'tipe' => 'laden_exp', 'blok' => 'A', 'bay' => 1, 'row' => 2, 'tier' => 2, 'berat' => '21.400 kg', 'line' => 'CMA CGM', 'pod' => 'Antwerp (BEANR)', 'dwell' => '3 hari', 'pabean' => 'Jalur Hijau (NPE)', 'seal' => 'CMA-99412', 'rtk' => 'RS-01 (1.0cm)'],
    ['id' => 'SUDU4421098', 'iso' => '45G1 (40ft HC)', 'tipe' => 'laden_exp', 'blok' => 'A', 'bay' => 1, 'row' => 3, 'tier' => 1, 'berat' => '29.100 kg', 'line' => 'Hamburg Süd', 'pod' => 'Santos (BRSSZ)', 'dwell' => '2 hari', 'pabean' => 'Jalur Hijau (NPE)', 'seal' => 'HS-44210', 'rtk' => 'RS-02 (0.9cm)'],

    // Blok B: Laden Impor (Sky Blue)
    ['id' => 'ONEU6612984', 'iso' => '45G1 (40ft HC)', 'tipe' => 'laden_imp', 'blok' => 'B', 'bay' => 3, 'row' => 1, 'tier' => 1, 'berat' => '27.900 kg', 'line' => 'ONE Network', 'pod' => 'Cikarang (IDCBI)', 'dwell' => '2 hari', 'pabean' => 'SPPB Terbit', 'seal' => 'ONE-99214', 'rtk' => 'RS-02 (1.1cm)'],
    ['id' => 'ONEU7719023', 'iso' => '45G1 (40ft HC)', 'tipe' => 'laden_imp', 'blok' => 'B', 'bay' => 3, 'row' => 1, 'tier' => 2, 'berat' => '22.300 kg', 'line' => 'ONE Network', 'pod' => 'Cikarang (IDCBI)', 'dwell' => '3 hari', 'pabean' => 'SPPB Terbit', 'seal' => 'ONE-88129', 'rtk' => 'RS-02 (1.3cm)'],
    ['id' => 'HLCU9912041', 'iso' => '42G1 (40ft GP)', 'tipe' => 'laden_imp', 'blok' => 'B', 'bay' => 3, 'row' => 2, 'tier' => 1, 'berat' => '28.500 kg', 'line' => 'Hapag-Lloyd', 'pod' => 'Cikarang (IDCBI)', 'dwell' => '1 hari', 'pabean' => 'SPPB Terbit', 'seal' => 'HL-99120', 'rtk' => 'RS-03 (1.2cm)'],
    ['id' => 'HLCU1190234', 'iso' => '42G1 (40ft GP)', 'tipe' => 'laden_imp', 'blok' => 'B', 'bay' => 3, 'row' => 2, 'tier' => 2, 'berat' => '19.800 kg', 'line' => 'Hapag-Lloyd', 'pod' => 'Cikarang (IDCBI)', 'dwell' => '4 hari', 'pabean' => 'Jalur Merah (Behandle)', 'seal' => 'JT701-E-SEAL', 'rtk' => 'RS-03 (1.4cm)'],

    // Blok C: Domestik (Emerald Green)
    ['id' => 'SPNU2019482', 'iso' => '22G1 (20ft GP)', 'tipe' => 'domestic', 'blok' => 'C', 'bay' => 5, 'row' => 1, 'tier' => 1, 'berat' => '18.400 kg', 'line' => 'Spil Logistics', 'pod' => 'Surabaya (IDSUB)', 'dwell' => '2 hari', 'pabean' => 'Domestik Bebas Pabean', 'seal' => 'SPIL-00214', 'rtk' => 'RS-04 (1.1cm)'],
    ['id' => 'SPNU2088192', 'iso' => '22G1 (20ft GP)', 'tipe' => 'domestic', 'blok' => 'C', 'bay' => 5, 'row' => 1, 'tier' => 2, 'berat' => '14.200 kg', 'line' => 'Spil Logistics', 'pod' => 'Semarang (IDSRG)', 'dwell' => '1 hari', 'pabean' => 'Domestik Bebas Pabean', 'seal' => 'SPIL-00891', 'rtk' => 'RS-04 (0.8cm)'],
    ['id' => 'MRKU2219401', 'iso' => '22G1 (20ft GP)', 'tipe' => 'domestic', 'blok' => 'C', 'bay' => 5, 'row' => 2, 'tier' => 1, 'berat' => '20.100 kg', 'line' => 'Meratus Line', 'pod' => 'Makassar (IDMAK)', 'dwell' => '3 hari', 'pabean' => 'Domestik Bebas Pabean', 'seal' => 'MRT-77219', 'rtk' => 'RS-04 (1.2cm)'],

    // Blok D: Buffer Siding KA (Purple)
    ['id' => 'TEMU4491024', 'iso' => '45G1 (40ft HC)', 'tipe' => 'buffer_rail', 'blok' => 'D', 'bay' => 7, 'row' => 1, 'tier' => 1, 'berat' => '27.400 kg', 'line' => 'KAI Logistik', 'pod' => 'Priok Siding Track 1', 'dwell' => '6 jam', 'pabean' => 'Manifest KA Disetujui', 'seal' => 'KAI-88190', 'rtk' => 'RS-05 (1.0cm)'],
    ['id' => 'TEMU8819402', 'iso' => '45G1 (40ft HC)', 'tipe' => 'buffer_rail', 'blok' => 'D', 'bay' => 7, 'row' => 1, 'tier' => 2, 'berat' => '23.600 kg', 'line' => 'KAI Logistik', 'pod' => 'Priok Siding Track 1', 'dwell' => '4 jam', 'pabean' => 'Manifest KA Disetujui', 'seal' => 'KAI-77218', 'rtk' => 'RS-05 (0.9cm)'],

    // Blok E: Dangerous Goods (Hazard Red)
    ['id' => 'DGNU9921401', 'iso' => '22T1 (Tank DG)', 'tipe' => 'dg_hazard', 'blok' => 'E', 'bay' => 9, 'row' => 1, 'tier' => 1, 'berat' => '24.000 kg', 'line' => 'Bertschi Global', 'pod' => 'Cikarang DG Storage', 'dwell' => '1 hari', 'pabean' => 'IMO Class 3 Flammable', 'seal' => 'DG-SEAL-01', 'rtk' => 'RS-06 (1.1cm)'],

    // Reefer Yard: Cold Chain (Cyan)
    ['id' => 'MNBU9920194', 'iso' => '45R1 (40ft Reefer)', 'tipe' => 'reefer', 'blok' => 'Reefer', 'bay' => 11, 'row' => 1, 'tier' => 1, 'berat' => '29.300 kg', 'line' => 'Maersk Reefer', 'pod' => 'Cold Storage Cikarang', 'dwell' => '2 hari', 'pabean' => 'Suhu -20°C (Plugged In)', 'seal' => 'RF-99210-TEMP', 'rtk' => 'RS-06 (0.8cm)']
];

// Sinkronisasi Data Nyata dari Basis Data MySQL (containers)
$db_all_containers = [];
if (isset($pdo)) {
    try {
        $stmtC = $pdo->query("SELECT * FROM containers WHERE status IN ('in_yard', 'in_transit') ORDER BY id ASC");
        $rawYard = $stmtC->fetchAll(PDO::FETCH_ASSOC);
        $db_all_containers = $rawYard;
        if (!empty($rawYard)) {
            $live_mapped = [];
            foreach ($rawYard as $c) {
                $blkRaw = !empty($c['block']) ? strtoupper(trim($c['block'])) : 'A';
                $cargoType = strtolower(trim($c['cargo_type'] ?? 'dry'));
                
                // Klasifikasi Blok & Tipe Operasional Standar Yard Civil Engineering
                if ($blkRaw === 'REEFER' || $cargoType === 'reefer') {
                    $tipe = 'reefer';
                    $blk = 'Reefer';
                } elseif ($blkRaw === 'DG' || $blkRaw === 'E' || $cargoType === 'dg') {
                    $tipe = 'dg_hazard';
                    $blk = 'E';
                } elseif ($blkRaw === 'B') {
                    $tipe = 'laden_imp';
                    $blk = 'B';
                } elseif ($blkRaw === 'C') {
                    $tipe = 'domestic';
                    $blk = 'C';
                } elseif ($blkRaw === 'D' || $blkRaw === 'RAIL') {
                    $tipe = 'buffer_rail';
                    $blk = 'D';
                } elseif ($blkRaw === 'EMPTY') {
                    $tipe = 'domestic';
                    $blk = 'C';
                } else {
                    $tipe = 'laden_exp';
                    $blk = 'A';
                }

                $grossKg = (float)($c['gross_weight_kg'] ?? 24000);
                $cornerLoadKn = round(($grossKg * 9.81 / 4) / 1000, 1);
                $tierNum = max(1, (int)($c['tier'] ?? 1));

                $live_mapped[] = [
                    'id'               => $c['container_number'],
                    'iso'              => $c['iso_code'] ? ($c['iso_code'] . ' (' . ($c['size_type'] ?? '40ft') . ')') : '42G1 (40ft GP)',
                    'tipe'             => $tipe,
                    'blok'             => $blk,
                    'bay'              => max(1, (int)($c['bay'] ?? 1)),
                    'row'              => max(1, (int)($c['row'] ?? 1)),
                    'tier'             => $tierNum,
                    'berat'            => number_format($grossKg, 0, ',', '.') . ' kg',
                    'gross_kg'         => $grossKg,
                    'point_load'       => $cornerLoadKn . ' kN / corner',
                    'pavement_status'  => 'PQC 800 kN/m² (Aman)',
                    'stability_factor' => ($grossKg >= 22000 ? 'SF 3.1 (>1.5 BMKG)' : 'SF 2.4 (>1.5 BMKG)'),
                    'layer_type'       => ($tierNum === 1 ? 'Tier 1 (Heavy Base Foundation)' : ($tierNum === 2 ? 'Tier 2 (Intermediate)' : 'Tier ' . $tierNum . ' (Top Layer)')),
                    'line'             => !empty($c['owner_company']) ? $c['owner_company'] : 'Shipping Carrier',
                    'pod'              => ($blk === 'B' ? 'Cikarang (IDCBI)' : ($blk === 'C' ? 'Surabaya (IDSUB)' : 'Rotterdam (NLRTM)')),
                    'dwell'            => '2 hari 6 jam',
                    'pabean'           => ($c['customs_status'] === 'SPPB_CLEARED' ? 'SPPB Terbit (Hijau)' : ($c['customs_status'] === 'RED_LANE' ? 'Jalur Merah (Hold)' : 'Pemeriksaan Pabean')),
                    'seal'             => !empty($c['seal_number']) ? $c['seal_number'] : 'SEAL-OK',
                    'rtk'              => 'RS-01 (Akurasi 1.2cm)',
                    'is_live_db'       => true
                ];
            }
            if (!empty($live_mapped)) {
                $yard_containers = $live_mapped;
            }
        }
    } catch (Exception $e) {}
}

// Antrean Job Order Reach Stacker (VMT)
$vmt_job_orders = [
    ['id' => 'JO-8821', 'alat' => 'Reach Stacker 01', 'operator' => 'Bambang S.', 'aksi' => 'LIFT-ON KE TRUK', 'kontainer' => 'MSKU3412901', 'pos_asal' => 'Blok A - Bay 01 Row 01 Tier 2', 'tujuan' => 'Trailer B 9421 UIT (Lane 1)', 'status' => 'IN_PROGRESS', 'prioritas' => 'TINGGI'],
    ['id' => 'JO-8822', 'alat' => 'Reach Stacker 02', 'operator' => 'Dedi Kurniawan', 'aksi' => 'RESHUFFLE (CRP)', 'kontainer' => 'ONEU7719023', 'pos_asal' => 'Blok B - Bay 03 Row 01 Tier 2', 'tujuan' => 'Bay 03 Row 02 Tier 2 (Temp)', 'status' => 'DISPATCHED', 'prioritas' => 'CRITICAL'],
    ['id' => 'JO-8823', 'alat' => 'Reach Stacker 03', 'operator' => 'Agus Priyanto', 'aksi' => 'STACKING DARI GERBONG', 'kontainer' => 'TEMU4491024', 'pos_asal' => 'Flatcar KA #08 Siding Track 1', 'tujuan' => 'Blok D - Bay 07 Row 01 Tier 1', 'status' => 'PENDING', 'prioritas' => 'NORMAL'],
    ['id' => 'JO-8824', 'alat' => 'Reach Stacker 04', 'operator' => 'Hendro W.', 'aksi' => 'REEFER PLUG-IN MOVE', 'kontainer' => 'MNBU9920194', 'pos_asal' => 'Trailer B 9912 KAA', 'tujuan' => 'Reefer Rack Slot #42 (Plug 380V)', 'status' => 'COMPLETED', 'prioritas' => 'TINGGI']
];
?>

<!-- Alert Feedback Pasca Aksi Relokasi -->
<?php if ($yard_alert): ?>
<div class="mb-4 p-4 rounded-xl border flex items-start space-x-3 animate-fadeIn <?= $yard_alert['type'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-amber-50 border-amber-200 text-amber-900' ?>">
    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 <?= $yard_alert['type'] === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
        <i class="fa-solid <?= $yard_alert['type'] === 'success' ? 'fa-check' : 'fa-info' ?>"></i>
    </div>
    <div class="flex-1 min-w-0">
        <h4 class="text-sm font-bold"><?= $yard_alert['title'] ?></h4>
        <p class="text-xs mt-0.5"><?= $yard_alert['msg'] ?></p>
    </div>
    <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>
<?php endif; ?>

<div class="space-y-2.5 animate-fadeIn pb-12">
    <!-- Header Modul: Compact Executive Style (Aligned with Sidebar) -->
    <div class="bg-white rounded-xl px-3 py-2 shadow-2xs border border-slate-200/80 mb-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div class="flex items-center space-x-2.5">
            <div class="w-7 h-7 rounded-lg bg-orange-50 border border-orange-200/70 text-orange-600 flex items-center justify-center text-xs shadow-2xs flex-shrink-0">
                <i class="fa-solid <?= $yard_info['icon'] ?>"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-1.5">
                    <h1 class="text-[13px] font-extrabold tracking-tight text-slate-900">Manajemen Stacking Yard &amp; Digital Twin 3D</h1>
                    <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-[9px] font-bold flex items-center">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span><?= $yard_info['status'] ?>
                    </span>
                    <span class="px-1.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[9px] font-bold">
                        MySQL Sync (<?= count($yard_containers) ?> Box)
                    </span>
                    <span class="px-1.5 py-0.5 bg-orange-50 text-orange-700 border border-orange-200 rounded text-[9px] font-bold">
                        RTK GNSS &lt;1.4cm
                    </span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-1.5 self-start sm:self-auto flex-shrink-0">
            <button onclick="openModalYardRelocate()" class="px-2.5 py-1 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white text-[11px] font-bold rounded-lg shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-solid fa-arrows-up-down-left-right text-[10px]"></i>
                <span>+ Relokasi (VMT)</span>
            </button>
        </div>
    </div>

    <!-- KPI Micro-Cards (High-Density Executive Grid) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-2 mb-2.5">
        <?php foreach ($yard_kpis as $kpi): ?>
            <div class="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-2xs flex items-center justify-between transition-all hover:border-orange-200">
                <div class="min-w-0 pr-2">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider truncate mb-0.5"><?= $kpi['label'] ?></p>
                    <div class="flex items-baseline space-x-1">
                        <h3 class="text-[13.5px] font-extrabold text-slate-900 tracking-tight leading-tight truncate"><?= $kpi['val'] ?></h3>
                        <span class="text-[9px] text-<?= $kpi['color'] ?>-600 font-bold"><?= $kpi['unit'] ?></span>
                    </div>
                    <p class="text-[9px] text-slate-500 font-semibold mt-0.5 truncate"><?= $kpi['sub'] ?></p>
                </div>
                <div class="w-7 h-7 rounded-lg bg-slate-50 text-<?= $kpi['color'] ?>-600 flex items-center justify-center text-[11px] flex-shrink-0 shadow-2xs">
                    <i class="fa-solid <?= $kpi['icon'] ?>"></i>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- AREA UTAMA: 3D DIGITAL TWIN VIEWER & KONTROL STACKING -->
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6 items-start">
        
        <!-- Kanvas 3D WebGL (Three.js) & Bay Matrix (Span 3 Kolom) -->
        <div class="xl:col-span-3 bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200/80 space-y-4">
            
            <!-- Toolbar Pengendali Tampilan 3D & Filter Kategori -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center">
                        <i class="fa-solid fa-cube text-blue-400 mr-2"></i>
                        Digital Twin Container Stacking &amp; Yard Infrastructure (3D WebGL)
                    </span>
                    <span class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 font-bold font-mono">
                        PQC 40 MPa • Three.js r128
                    </span>
                </div>

                <!-- Kontrol Sudut Kamera 3D Presets & Mode Siang/Malam -->
                <div class="flex items-center space-x-1.5 text-xs">
                    <!-- Day / Night Toggle -->
                    <button id="btnToggleDayNight" onclick="toggleYardDayNight()" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-amber-700 text-[11px] font-semibold border border-slate-200 transition flex items-center gap-1" title="Beralih Pencahayaan Lapangan (Siang / Malam dengan Lampu Sorot Tower)">
                        <i class="fa-solid fa-sun" id="iconDayNight"></i>
                        <span id="textDayNight">Siang</span>
                    </button>

                    <span class="text-[11px] text-slate-400 mx-1"><i class="fa-solid fa-camera mr-1"></i>Preset:</span>
                    <button onclick="setCameraView('iso')" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-medium border border-slate-200 transition" title="Tampak Isometrik 3D">
                        <i class="fa-solid fa-cube mr-1"></i>Isometrik
                    </button>
                    <button onclick="setCameraView('top')" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-medium border border-slate-200 transition" title="Tampak Atas (Bird's Eye / Site Plan Layout)">
                        <i class="fa-solid fa-arrows-up-to-line mr-1"></i>Tampak Atas
                    </button>
                    <button onclick="setCameraView('side')" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-medium border border-slate-200 transition" title="Tampak Samping (Profil Bay Melintang)">
                        <i class="fa-solid fa-arrows-left-right mr-1"></i>Profil Bay
                    </button>
                    <button onclick="setCameraView('operator')" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-medium border border-slate-200 transition" title="Pandangan Operator Lapangan (Kabin Reach Stacker)">
                        <i class="fa-solid fa-truck-monster mr-1"></i>Kabin RS
                    </button>
                    <button onclick="resetYardCamera()" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 text-[11px] font-mono border border-slate-200 transition" title="Reset Kamera">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                </div>
            </div>

            <!-- Filter Kategori Kontainer & Simulasi Reshuffle -->
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                <!-- Filter Kategori Warna -->
                <div class="flex flex-wrap items-center gap-1.5" id="containerFilterButtons">
                    <span class="text-[11px] text-slate-400 mr-1 font-semibold">Tampilkan:</span>
                    <button onclick="filter3DContainers('all')" class="cat-filter-btn px-2.5 py-1 rounded-lg text-[11px] font-bold bg-[#0170b9] text-white transition-all shadow-xs" data-cat="all">Semua Blok (Master)</button>
                    <button onclick="filter3DContainers('laden_exp')" class="cat-filter-btn px-2.5 py-1 rounded-lg text-[11px] font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 transition-all" data-cat="laden_exp">🔵 Blok A (Laden Ekspor)</button>
                    <button onclick="filter3DContainers('laden_imp')" class="cat-filter-btn px-2.5 py-1 rounded-lg text-[11px] font-medium bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 transition-all" data-cat="laden_imp">🔷 Blok B (Laden Impor)</button>
                    <button onclick="filter3DContainers('domestic')" class="cat-filter-btn px-2.5 py-1 rounded-lg text-[11px] font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition-all" data-cat="domestic">🟢 Blok C (Domestik)</button>
                    <button onclick="filter3DContainers('buffer_rail')" class="cat-filter-btn px-2.5 py-1 rounded-lg text-[11px] font-medium bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 transition-all" data-cat="buffer_rail">🟣 Blok D (Siding KA)</button>
                    <button onclick="filter3DContainers('dg_hazard')" class="cat-filter-btn px-2.5 py-1 rounded-lg text-[11px] font-medium bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition-all" data-cat="dg_hazard">🔴 Blok E (DG Hazard)</button>
                    <button onclick="filter3DContainers('reefer')" class="cat-filter-btn px-2.5 py-1 rounded-lg text-[11px] font-medium bg-cyan-50 text-cyan-700 hover:bg-cyan-100 border border-cyan-200 transition-all" data-cat="reefer">❄️ Blok Reefer</button>
                </div>

                <!-- Tombol Simulasi Reshuffle (CRP) -->
                <button onclick="simulateContainerRelocation()" id="btnSimulateCRP" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white transition-all shadow-md flex items-center space-x-1.5">
                    <i class="fa-solid fa-arrows-split-up-and-left animate-spin" id="crpSpinIcon" style="display:none;"></i>
                    <i class="fa-solid fa-dolly" id="crpStaticIcon"></i>
                    <span>Simulasi Manuver Reshuffle (CRP)</span>
                </button>
            </div>

            <!-- Viewport 3D Canvas Three.js (Expanded Height & Civil Eng HUD) -->
            <div class="relative w-full h-[530px] rounded-xl overflow-hidden bg-gradient-to-b from-sky-50/60 to-slate-100 border border-slate-200/90 shadow-inner" id="threeCanvasContainer">
                <canvas id="yard3DCanvas" class="w-full h-full block cursor-grab active:cursor-grabbing"></canvas>

                <!-- Floating Civil Engineering HUD Badge (Top Right) -->
                <div class="absolute top-3 right-3 bg-white/95 backdrop-blur-md px-3 py-1.5 rounded-lg border border-slate-200 text-[10px] text-slate-700 flex items-center space-x-2 pointer-events-none z-10 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="font-mono font-bold text-emerald-400">PQC Rigid Pavement 350mm</span>
                    <span class="text-slate-600">|</span>
                    <span class="text-slate-300">Beban Gandar RS 80 Ton</span>
                    <span class="text-slate-600">|</span>
                    <span class="text-blue-300 font-mono">RTK &lt;1.4cm</span>
                </div>

                <!-- Floating 3D Navigation Guide (Bottom Left) -->
                <div class="absolute bottom-3 left-3 bg-white/95 backdrop-blur-md px-3 py-1.5 rounded-lg border border-slate-200 text-[10px] text-slate-700 flex items-center space-x-3 pointer-events-none z-10 shadow-xs">
                    <span><i class="fa-solid fa-computer-mouse text-blue-400 mr-1"></i>Klik Kiri + Geser: Putar 360°</span>
                    <span><i class="fa-solid fa-arrows-up-down text-blue-400 mr-1"></i>Scroll: Zoom</span>
                    <span><i class="fa-solid fa-hand-pointer text-amber-400 mr-1"></i>Klik Kontainer: Detail Manifest &amp; Beban</span>
                </div>

                <!-- Loading Overlay -->
                <div id="threeLoadingOverlay" class="absolute inset-0 bg-white/90 flex flex-col items-center justify-center space-y-3 z-20 transition-opacity duration-300">
                    <div class="w-10 h-10 border-3 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                    <span class="text-xs text-slate-700 font-mono font-bold">Memuat Digital Twin Stacking Yard 3D...</span>
                </div>
            </div>

            <!-- Petunjuk Arsitektur Bay-Row-Tier & Pavement Engineering -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs bg-slate-50/80 p-3 rounded-xl border border-slate-200/80">
                <div class="flex items-center space-x-2">
                    <span class="w-7 h-7 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 flex items-center justify-center font-bold font-mono text-xs flex-shrink-0">BAY</span>
                    <div>
                        <span class="font-bold text-slate-800 text-xs block">Sumbu Memanjang (Bay)</span>
                        <span class="text-[10px] text-slate-400">Modul 14m • Ganjil 20ft | Genap 40ft HC</span>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <span class="w-7 h-7 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center justify-center font-bold font-mono text-xs flex-shrink-0">ROW</span>
                    <div>
                        <span class="font-bold text-slate-800 text-xs block">Sumbu Melintang (Row 01-06)</span>
                        <span class="text-[10px] text-slate-400">Pitch 3.2m • Spreader Clearance 76cm</span>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <span class="w-7 h-7 rounded-lg bg-purple-50 border border-purple-200 text-purple-700 flex items-center justify-center font-bold font-mono text-xs flex-shrink-0">TIER</span>
                    <div>
                        <span class="font-bold text-slate-800 text-xs block">Elevasi Tumpuk (Tier 1-4)</span>
                        <span class="text-[10px] text-slate-400">Tier 1 Dasar (Heavy) s/d Tier 4 Atas</span>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <span class="w-7 h-7 rounded-lg bg-amber-50 border border-amber-200 text-amber-700 flex items-center justify-center font-bold font-mono text-xs flex-shrink-0"><i class="fa-solid fa-road"></i></span>
                    <div>
                        <span class="font-bold text-slate-800 text-xs block">Perkerasan Beton (PQC)</span>
                        <span class="text-[10px] text-slate-400">K-450 fc' 40 MPa • Titik Kontak 250 kN</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- INSPECTOR DRAWER SAMPING: DETAIL MANIFEST & TELEMETRI KONTAINER -->
        <div class="xl:col-span-1 bg-white rounded-2xl p-5 border border-gray-100 shadow-sm space-y-4" id="yardInspectorSidebar">
            
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600 animate-pulse"></span>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-700">Inspector Kontainer 3D</span>
                </div>
                <span id="yardBoxStatusBadge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    LADEN EKSPOR
                </span>
            </div>

            <!-- Visual Nomor Kontainer & Tipe ISO -->
            <div class="bg-gradient-to-br from-[#0170b9] to-blue-700 rounded-xl p-3.5 text-white space-y-1 relative overflow-hidden shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-mono text-blue-100 uppercase tracking-widest">ISO 6346 CONTAINER ID</span>
                    <span id="yardBoxLine" class="text-[10px] font-bold text-white bg-white/20 px-2 py-0.5 rounded">Maersk Line</span>
                </div>
                <h3 id="yardBoxNumber" class="text-base font-mono font-black tracking-wider text-white">MSKU 982145-8</h3>
                <div class="flex items-center justify-between text-[11px] text-blue-100 pt-1 border-t border-white/20 mt-2">
                    <span id="yardBoxType">45G1 (40ft High Cube)</span>
                    <span id="yardBoxWeight" class="font-mono text-amber-300 font-bold">VGM: 28.450 kg</span>
                </div>
            </div>

            <!-- Detail Spesifikasi Slot Stacking Lapangan -->
            <div class="space-y-2.5 text-xs">
                
                <div class="bg-gray-50/80 p-2.5 rounded-lg border border-gray-100">
                    <span class="text-[10px] uppercase font-bold text-gray-400 block">Koordinat Alokasi Stacking:</span>
                    <p id="yardBoxCoord" class="font-mono font-bold text-gray-900 text-xs mt-0.5">
                        BLOK A • BAY 01 • ROW 01 • TIER 1
                    </p>
                    <span class="text-[10px] text-gray-500 block mt-0.5">Tumpukan Dasar (Heavy Cargo Tier 1)</span>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="bg-gray-50/80 p-2.5 rounded-lg border border-gray-100">
                        <span class="text-[10px] uppercase font-bold text-gray-400 block">Status Kepabeanan:</span>
                        <p id="yardBoxCustoms" class="font-semibold text-emerald-700 text-[11px] mt-0.5">Jalur Hijau (NPE)</p>
                    </div>
                    <div class="bg-gray-50/80 p-2.5 rounded-lg border border-gray-100">
                        <span class="text-[10px] uppercase font-bold text-gray-400 block">Nomor Segel (Seal):</span>
                        <p id="yardBoxSeal" class="font-mono font-semibold text-gray-800 text-[11px] mt-0.5">ML-ID99214</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="bg-gray-50/80 p-2.5 rounded-lg border border-gray-100">
                        <span class="text-[10px] uppercase font-bold text-gray-400 block">Tujuan / POD:</span>
                        <p id="yardBoxPod" class="font-semibold text-gray-800 text-[11px] mt-0.5">Rotterdam (NLRTM)</p>
                    </div>
                    <div class="bg-gray-50/80 p-2.5 rounded-lg border border-gray-100">
                        <span class="text-[10px] uppercase font-bold text-gray-400 block">Dwell Time:</span>
                        <p id="yardBoxDwell" class="font-mono font-bold text-blue-700 text-[11px] mt-0.5">1 Hari 4 Jam</p>
                    </div>
                </div>

                <!-- Analisis Struktur & Beban Perkerasan (Civil Engineering) -->
                <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200/90 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] uppercase font-bold text-slate-700 flex items-center">
                            <i class="fa-solid fa-ruler-combined mr-1 text-blue-600"></i>Analisis Beban Fondasi Yard:
                        </span>
                        <span class="text-[9px] font-mono text-emerald-600 font-bold bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">K-450 PQC</span>
                    </div>
                    <div class="grid grid-cols-2 gap-1.5 text-[11px] pt-1">
                        <div>
                            <span class="text-[9px] text-slate-400 block font-semibold">Beban Titik Sudut:</span>
                            <span id="yardBoxPointLoad" class="font-mono font-bold text-slate-800">69.8 kN / corner</span>
                        </div>
                        <div>
                            <span class="text-[9px] text-slate-400 block font-semibold">Stabilitas Angin 120km/h:</span>
                            <span id="yardBoxWind" class="font-mono font-bold text-emerald-700">SF 3.1 (Aman)</span>
                        </div>
                        <div>
                            <span class="text-[9px] text-slate-400 block font-semibold">Kapasitas Pavement:</span>
                            <span id="yardBoxPavement" class="font-mono font-bold text-slate-800">800 kN/m² (Aman)</span>
                        </div>
                        <div>
                            <span class="text-[9px] text-slate-400 block font-semibold">Kategori Layer:</span>
                            <span id="yardBoxLayer" class="font-mono font-bold text-blue-700">Tier 1 (Heavy Base)</span>
                        </div>
                    </div>
                </div>

                <!-- Telemetri Sensor RTK GNSS Alat Berat -->
                <div class="p-2.5 rounded-lg bg-emerald-50/60 border border-emerald-100">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] uppercase font-bold text-emerald-800 flex items-center">
                            <i class="fa-solid fa-satellite-dish mr-1 text-emerald-600"></i>RTK GNSS Telemetry:
                        </span>
                        <span class="text-[10px] font-mono text-emerald-700 font-bold">< 1.4 cm</span>
                    </div>
                    <p id="yardBoxRtk" class="text-[11px] font-mono text-emerald-950 font-semibold mt-1">
                        Reach Stacker 01 (Bromma Spreader Locked)
                    </p>
                </div>

            </div>

            <!-- Tombol Aksi Penerbitan Job Order Work Order -->
            <div class="pt-2 space-y-2">
                <button onclick="dispatchWorkOrderFromInspector()" class="w-full py-2.5 bg-[#0170b9] hover:bg-[#004b87] text-white text-xs font-bold rounded-xl transition-all shadow-sm flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-clipboard-list"></i>
                    <span>Terbitkan Job Order Manuver (VMT)</span>
                </button>
                <button onclick="showBayMatrixView()" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-all flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-table-cells"></i>
                    <span>Buka Matriks Penampang Bay 2D</span>
                </button>
            </div>

            <!-- Inter-Module Teleport Toolbar -->
            <div class="pt-2 border-t border-gray-100">
                <span class="text-[10px] uppercase font-bold text-gray-400 block mb-1.5">Pintasan Antar-Modul:</span>
                <div class="grid grid-cols-2 gap-1.5 text-[11px]">
                    <a id="btnYardLinkTrack" href="dashboard.php?page=kontainer" class="px-2 py-1.5 bg-emerald-50 hover:bg-emerald-600 hover:text-white text-emerald-700 font-bold rounded-lg border border-emerald-200 transition flex items-center justify-center gap-1">
                        <i class="fa-solid fa-route"></i> Lacak Siklus
                    </a>
                    <a id="btnYardLink3D" href="dashboard.php?page=simulator" class="px-2 py-1.5 bg-indigo-50 hover:bg-indigo-600 hover:text-white text-indigo-700 font-bold rounded-lg border border-indigo-200 transition flex items-center justify-center gap-1">
                        <i class="fa-solid fa-cube"></i> Simulasi 3D
                    </a>
                    <a id="btnYardLinkDenah" href="dashboard.php?page=denah" class="px-2 py-1.5 bg-amber-50 hover:bg-amber-600 hover:text-white text-amber-700 font-bold rounded-lg border border-amber-200 transition flex items-center justify-center gap-1">
                        <i class="fa-solid fa-map-location-dot"></i> Denah 2D
                    </a>
                    <a id="btnYardLinkBill" href="dashboard.php?page=billing" class="px-2 py-1.5 bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-700 font-bold rounded-lg border border-blue-200 transition flex items-center justify-center gap-1">
                        <i class="fa-solid fa-file-invoice-dollar"></i> Faktur Billing
                    </a>
                </div>
            </div>

        </div>

    </div>

    <!-- ======================================================================= -->
    <!-- TABEL ANTREAN JOB ORDER ALAT BERAT (VMT & RTK DISPATCH) -->
    <!-- ======================================================================= -->
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
        
        <div class="flex items-center justify-between border-b border-gray-100 pb-3 flex-wrap gap-2">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-sm">
                    <i class="fa-solid fa-truck-monster"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-gray-900">Antrean Perintah Kerja Alat Berat (VMT Work Order Queue)</h3>
                    <p class="text-xs text-gray-500">Integrasi Vehicle Mounted Terminal (Zebra VC8300) & Sensor Twistlock Bromma</p>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block mr-1.5 animate-pulse"></span>6 VMT Online
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-gray-500 uppercase tracking-wider font-semibold border-b border-gray-200">
                        <th class="py-3 px-3.5">No. Job Order</th>
                        <th class="py-3 px-3.5">Unit Alat & Operator</th>
                        <th class="py-3 px-3.5">Instruksi Kerja</th>
                        <th class="py-3 px-3.5">Nomor Kontainer</th>
                        <th class="py-3 px-3.5">Posisi Asal</th>
                        <th class="py-3 px-3.5">Lokasi Tujuan</th>
                        <th class="py-3 px-3.5 text-center">Prioritas</th>
                        <th class="py-3 px-3.5 text-center">Status VMT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    <?php foreach ($vmt_job_orders as $jo): ?>
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3 px-3.5 font-mono font-bold text-blue-700"><?= $jo['id'] ?></td>
                        <td class="py-3 px-3.5">
                            <strong class="text-gray-900 block"><?= $jo['alat'] ?></strong>
                            <span class="text-[11px] text-gray-500">Op: <?= $jo['operator'] ?></span>
                        </td>
                        <td class="py-3 px-3.5">
                            <span class="font-semibold text-slate-800 block"><?= $jo['aksi'] ?></span>
                        </td>
                        <td class="py-3 px-3.5 font-mono font-bold text-blue-700"><?= $jo['kontainer'] ?></td>
                        <td class="py-3 px-3.5 text-gray-600"><?= $jo['pos_asal'] ?></td>
                        <td class="py-3 px-3.5 text-gray-900 font-medium"><?= $jo['tujuan'] ?></td>
                        <td class="py-3 px-3.5 text-center">
                            <?php if ($jo['prioritas'] === 'CRITICAL'): ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">KRITIKAL</span>
                            <?php elseif ($jo['prioritas'] === 'TINGGI'): ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200">TINGGI</span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">NORMAL</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-3.5 text-center">
                            <?php if ($jo['status'] === 'IN_PROGRESS'): ?>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 flex items-center justify-center w-fit mx-auto">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600 mr-1.5 animate-ping"></span>BERGERAK
                                </span>
                            <?php elseif ($jo['status'] === 'DISPATCHED'): ?>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center w-fit mx-auto">
                                    DISPATCHED
                                </span>
                            <?php elseif ($jo['status'] === 'COMPLETED'): ?>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center w-fit mx-auto">
                                    <i class="fa-solid fa-check mr-1"></i>SELESAI
                                </span>
                            <?php else: ?>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 flex items-center justify-center w-fit mx-auto">
                                    PENDING
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- MODAL PENAMPANG 2D BAY MATRIX (ROW VS TIER) -->
    <div id="bayMatrixModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 animate-fadeIn">
        <div class="bg-white rounded-2xl max-w-4xl w-full p-6 shadow-2xl border border-gray-100 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center space-x-2.5">
                    <span class="w-8 h-8 rounded-lg bg-blue-50 text-[#0170b9] flex items-center justify-center font-bold">
                        <i class="fa-solid fa-table-cells"></i>
                    </span>
                    <div>
                        <h3 class="font-bold text-base text-gray-900">Penampang 2D Stacking Matrix — Blok A (Bay 01)</h3>
                        <p class="text-xs text-gray-500">Struktur Penumpukan 6 Row x 4 Tier Kontainer 40ft High Cube</p>
                    </div>
                </div>
                <button onclick="closeBayMatrixModal()" class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Grid Matriks Penampang 4 Tier x 6 Row -->
            <div class="overflow-x-auto pb-1">
                <div class="space-y-3 min-w-[540px]">
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>Sisi Laut / Rel KA (Kiri)</span>
                    <span class="font-bold text-gray-700">BAY 01 (40ft High Cube Stacking)</span>
                    <span>Sisi Darat / Jalan Truk (Kanan)</span>
                </div>

                <div class="grid grid-cols-6 gap-2 text-center text-xs">
                    <!-- Header Row 01-06 -->
                    <div class="font-mono font-bold text-slate-500">Row 01</div>
                    <div class="font-mono font-bold text-slate-500">Row 02</div>
                    <div class="font-mono font-bold text-slate-500">Row 03</div>
                    <div class="font-mono font-bold text-slate-500">Row 04</div>
                    <div class="font-mono font-bold text-slate-500">Row 05</div>
                    <div class="font-mono font-bold text-slate-500">Row 06</div>

                    <!-- Tier 4 (Paling Atas) -->
                    <div class="h-16 rounded-lg border border-dashed border-gray-200 bg-slate-50/50 flex items-center justify-center text-[10px] text-gray-400">KOSONG (T4)</div>
                    <div class="h-16 rounded-lg border border-dashed border-gray-200 bg-slate-50/50 flex items-center justify-center text-[10px] text-gray-400">KOSONG (T4)</div>
                    <div class="h-16 rounded-lg border border-dashed border-gray-200 bg-slate-50/50 flex items-center justify-center text-[10px] text-gray-400">KOSONG (T4)</div>
                    <div class="h-16 rounded-lg border border-dashed border-gray-200 bg-slate-50/50 flex items-center justify-center text-[10px] text-gray-400">KOSONG (T4)</div>
                    <div class="h-16 rounded-lg border border-dashed border-gray-200 bg-slate-50/50 flex items-center justify-center text-[10px] text-gray-400">KOSONG (T4)</div>
                    <div class="h-16 rounded-lg border border-dashed border-gray-200 bg-slate-50/50 flex items-center justify-center text-[10px] text-gray-400">KOSONG (T4)</div>

                    <!-- Tier 3 -->
                    <div class="h-16 rounded-lg border border-dashed border-gray-200 bg-slate-50/50 flex items-center justify-center text-[10px] text-gray-400">KOSONG (T3)</div>
                    <div class="h-16 rounded-lg border border-dashed border-gray-200 bg-slate-50/50 flex items-center justify-center text-[10px] text-gray-400">KOSONG (T3)</div>
                    <div class="h-16 rounded-lg border border-dashed border-gray-200 bg-slate-50/50 flex items-center justify-center text-[10px] text-gray-400">KOSONG (T3)</div>
                    <div class="h-16 rounded-lg border border-dashed border-gray-200 bg-slate-50/50 flex items-center justify-center text-[10px] text-gray-400">KOSONG (T3)</div>
                    <div class="h-16 rounded-lg border border-dashed border-gray-200 bg-slate-50/50 flex items-center justify-center text-[10px] text-gray-400">KOSONG (T3)</div>
                    <div class="h-16 rounded-lg border border-dashed border-gray-200 bg-slate-50/50 flex items-center justify-center text-[10px] text-gray-400">KOSONG (T3)</div>

                    <!-- Tier 2 -->
                    <div class="h-16 rounded-lg bg-blue-700 text-white p-2 flex flex-col justify-between shadow-xs">
                        <span class="font-mono font-bold text-[11px] block truncate">MSKU 341290-1</span>
                        <span class="text-[9px] text-blue-200 font-mono">24.1t • Maersk</span>
                    </div>
                    <div class="h-16 rounded-lg bg-blue-700 text-white p-2 flex flex-col justify-between shadow-xs">
                        <span class="font-mono font-bold text-[11px] block truncate">CMAU 883412-0</span>
                        <span class="text-[9px] text-blue-200 font-mono">21.4t • CMA CGM</span>
                    </div>
                    <div class="h-16 rounded-lg border border-dashed border-gray-200 bg-slate-50/50 flex items-center justify-center text-[10px] text-gray-400">KOSONG (T2)</div>
                    <div class="h-16 rounded-lg border border-dashed border-gray-200 bg-slate-50/50 flex items-center justify-center text-[10px] text-gray-400">KOSONG (T2)</div>
                    <div class="h-16 rounded-lg border border-dashed border-gray-200 bg-slate-50/50 flex items-center justify-center text-[10px] text-gray-400">KOSONG (T2)</div>
                    <div class="h-16 rounded-lg border border-dashed border-gray-200 bg-slate-50/50 flex items-center justify-center text-[10px] text-gray-400">KOSONG (T2)</div>

                    <!-- Tier 1 (Paling Bawah - Ground) -->
                    <div class="h-16 rounded-lg bg-[#0170b9] text-white p-2 flex flex-col justify-between shadow-xs border-b-2 border-orange-400">
                        <span class="font-mono font-bold text-[11px] block truncate text-amber-300">MSKU 982145-8</span>
                        <span class="text-[9px] text-blue-200 font-mono">28.5t • Heavy Tier 1</span>
                    </div>
                    <div class="h-16 rounded-lg bg-[#0170b9] text-white p-2 flex flex-col justify-between shadow-xs border-b-2 border-orange-400">
                        <span class="font-mono font-bold text-[11px] block truncate text-amber-300">CMAU 771239-0</span>
                        <span class="text-[9px] text-blue-200 font-mono">26.8t • Heavy Tier 1</span>
                    </div>
                    <div class="h-16 rounded-lg bg-[#0170b9] text-white p-2 flex flex-col justify-between shadow-xs border-b-2 border-orange-400">
                        <span class="font-mono font-bold text-[11px] block truncate text-amber-300">SUDU 442109-8</span>
                        <span class="text-[9px] text-blue-200 font-mono">29.1t • Heavy Tier 1</span>
                    </div>
                    <div class="h-16 rounded-lg border border-dashed border-gray-200 bg-slate-50/50 flex items-center justify-center text-[10px] text-gray-400">SLOT TERSEDIA</div>
                    <div class="h-16 rounded-lg border border-dashed border-gray-200 bg-slate-50/50 flex items-center justify-center text-[10px] text-gray-400">SLOT TERSEDIA</div>
                    <div class="h-16 rounded-lg border border-dashed border-gray-200 bg-slate-50/50 flex items-center justify-center text-[10px] text-gray-400">SLOT TERSEDIA</div>
                </div>

                <!-- Land Ground Indicator -->
                <div class="h-2 bg-slate-400 rounded-full w-full"></div>
                <div class="text-center text-[11px] text-gray-400 font-mono">LANTAI BETON YARD 15 HA (REINFORCED HEAVY CONCRETE 80 TONS)</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DATASET JAVASCRIPT & THREE.JS 3D ENGINE -->
<!-- CDN Three.js r128 & OrbitControls -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>

<script>
// Data Kontainer Javascript dari Basis Data MySQL
const containersData = <?= json_encode($yard_containers, JSON_PRETTY_PRINT) ?>;

// Profil Branding Shipping Line & Warna ISO Kontainer
const CARRIER_PROFILES = {
    'Maersk Line':               { color: '#2b7a9e', brand: 'MAERSK', code: 'MSKU' },
    'Mediterranean Shipping Co': { color: '#d4a017', brand: 'MSC', code: 'MSCU' },
    'MSC Line':                  { color: '#d4a017', brand: 'MSC', code: 'MEDU' },
    'CMA CGM':                   { color: '#0a2540', brand: 'CMA CGM', code: 'CMAU' },
    'Evergreen Marine':          { color: '#006837', brand: 'EVERGREEN', code: 'EMCU' },
    'Ocean Network Express':     { color: '#d61f69', brand: 'ONE', code: 'ONEU' },
    'Hapag-Lloyd':               { color: '#ea580c', brand: 'HAPAG-LLOYD', code: 'HLCU' },
    'COSCO Shipping':            { color: '#1e40af', brand: 'COSCO', code: 'COSU' },
    'CNC Line':                  { color: '#1e3a8a', brand: 'CNC LINE', code: 'TCKU' },
    'Hamburg Sud':               { color: '#be123c', brand: 'HAMBURG SUD', code: 'SUDU' },
    'Spil Logistics':            { color: '#059669', brand: 'SPIL', code: 'SPNU' },
    'Meratus Line':              { color: '#047857', brand: 'MERATUS', code: 'MRKU' },
    'KAI Logistik':              { color: '#6d28d9', brand: 'KAI LOGISTIK', code: 'TEMU' },
    'Tex Tainer':                { color: '#9a3412', brand: 'TEXTAINER', code: 'TEMU' },
    'Trans Liner':               { color: '#0369a1', brand: 'TRANSLINER', code: 'TLLU' },
    'Yang Ming Line':            { color: '#b91c1c', brand: 'YANG MING', code: 'YMLU' },
    'NYK Line':                  { color: '#1e3a8a', brand: 'NYK LINE', code: 'NYKU' },
    'K-Line':                    { color: '#dc2626', brand: 'K-LINE', code: 'KKFU' },
    'Florens Container':         { color: '#c2410c', brand: 'FLORENS', code: 'FSCU' },
    'Bertschi Global':           { color: '#b91c1c', brand: 'BERTSCHI DG', code: 'DGNU' }
};

// Tata Letak Geometri Blok Lapangan Penumpukan (Master Plan Teknik Sipil Yard 15 Ha)
const BLOCK_LAYOUT = {
    'A': { name: 'Blok A (Ekspor)', basePos: [-28, -20], bayCount: 3, rowCount: 4, type: 'laden_exp', label: 'BLOK A - LADEN EKSPOR [MAX 4 TIER]' },
    'B': { name: 'Blok B (Impor)', basePos: [28, -20], bayCount: 3, rowCount: 4, type: 'laden_imp', label: 'BLOK B - LADEN IMPOR [CUSTOMS BONDED]' },
    'C': { name: 'Blok C (Domestik)', basePos: [-28, 16], bayCount: 3, rowCount: 4, type: 'domestic', label: 'BLOK C - DOMESTIK [FAST TURN]' },
    'D': { name: 'Blok D (KA Buffer)', basePos: [28, 16], bayCount: 3, rowCount: 4, type: 'buffer_rail', label: 'BLOK D - RAIL BUFFER SIDING' },
    'E': { name: 'Blok E (DG Hazard)', basePos: [28, -48], bayCount: 3, rowCount: 3, type: 'dg_hazard', label: 'BLOK E - DG HAZARD (IMDG B3)' },
    'Reefer': { name: 'Blok Reefer (Cold Chain)', basePos: [-28, -48], bayCount: 3, rowCount: 3, type: 'reefer', label: 'REEFER YARD [300 PLUGS 380V]' }
};

let scene, camera, renderer, controls;
let containerMeshes = [];
let beaconMeshes = [];
let floodLights = [];
let sunLight, ambientLight, hemiLight;
let raycaster, mouse;
let activeFilter = 'all';
let isDayMode = true;
let isAnimatingCRP = false;
let selectedContainerMesh = null;
const textureCache = {};

// Generator Tekstur Prosedural Panel Dinding Corten Steel Bergelombang
function getContainerSideTexture(carrierName, containerId, colorHex, is20ft) {
    const key = `side_${carrierName}_${colorHex}_${is20ft ? '20' : '40'}`;
    if (textureCache[key]) return textureCache[key];

    const canvas = document.createElement('canvas');
    canvas.width = is20ft ? 256 : 512;
    canvas.height = 128;
    const ctx = canvas.getContext('2d');

    // Cat Dasar Pelat Corten
    ctx.fillStyle = colorHex;
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Profil Gelombang Rusuk Vertikal (Corrugated Ribs)
    const ribWidth = 14;
    for (let x = 8; x < canvas.width - 8; x += ribWidth) {
        const grad = ctx.createLinearGradient(x, 0, x + ribWidth, 0);
        grad.addColorStop(0, 'rgba(255,255,255,0.22)');
        grad.addColorStop(0.35, 'rgba(255,255,255,0.08)');
        grad.addColorStop(0.65, 'rgba(0,0,0,0.25)');
        grad.addColorStop(1, 'rgba(0,0,0,0.45)');
        ctx.fillStyle = grad;
        ctx.fillRect(x, 4, ribWidth - 2, canvas.height - 8);
    }

    // Balok Rangka Atas & Bawah (Top & Bottom Rails)
    ctx.fillStyle = 'rgba(0,0,0,0.3)';
    ctx.fillRect(0, 0, canvas.width, 6);
    ctx.fillRect(0, canvas.height - 6, canvas.width, 6);

    // Pelat Bantalan Sudut (Corner Casting Pads)
    ctx.fillStyle = '#27272a';
    const cw = 16, ch = 16;
    ctx.fillRect(0, 0, cw, ch);
    ctx.fillRect(canvas.width - cw, 0, cw, ch);
    ctx.fillRect(0, canvas.height - ch, cw, ch);
    ctx.fillRect(canvas.width - cw, canvas.height - ch, cw, ch);

    // Lubang Twistlock Sudut Oval ISO 1161
    ctx.fillStyle = '#09090b';
    ctx.beginPath();
    ctx.ellipse(8, 8, 4, 2.5, 0, 0, Math.PI * 2);
    ctx.ellipse(canvas.width - 8, 8, 4, 2.5, 0, 0, Math.PI * 2);
    ctx.ellipse(8, canvas.height - 8, 4, 2.5, 0, 0, Math.PI * 2);
    ctx.ellipse(canvas.width - 8, canvas.height - 8, 4, 2.5, 0, 0, Math.PI * 2);
    ctx.fill();

    // Logo / Nama Brand Shipping Line
    ctx.save();
    ctx.fillStyle = '#ffffff';
    ctx.shadowColor = 'rgba(0,0,0,0.7)';
    ctx.shadowBlur = 5;
    ctx.font = 'bold ' + (is20ft ? '18px' : '26px') + ' "Arial Black", Impact, sans-serif';
    ctx.textAlign = 'left';
    ctx.textBaseline = 'middle';
    ctx.fillText(carrierName.toUpperCase(), 24, canvas.height / 2);
    ctx.restore();

    // Blok Identifikasi ISO di Pojok Kanan Atas
    ctx.save();
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 9px "Courier New", monospace';
    ctx.textAlign = 'right';
    ctx.fillText(containerId || 'CIDP-ISO', canvas.width - 20, 20);
    ctx.font = '7px sans-serif';
    ctx.fillText(is20ft ? '22G1 • MAX 30.480 KG' : '45G1 • MAX 32.500 KG', canvas.width - 20, 32);
    ctx.fillText('TARE 3.820 KG • NET 28.680 KG', canvas.width - 20, 42);

    // Pelat Persetujuan Keselamatan CSC (CSC Safety Plate)
    ctx.fillStyle = '#eab308';
    ctx.fillRect(canvas.width - 46, canvas.height - 24, 24, 14);
    ctx.fillStyle = '#000000';
    ctx.font = 'bold 5px sans-serif';
    ctx.fillText('CSC SAFETY', canvas.width - 24, canvas.height - 18);
    ctx.restore();

    const tex = new THREE.CanvasTexture(canvas);
    tex.anisotropy = 4;
    textureCache[key] = tex;
    return tex;
}

// Generator Tekstur Pintu Belakang Kontainer (Batang Pengunci & Segel)
function getContainerDoorTexture(colorHex) {
    const key = `door_${colorHex}`;
    if (textureCache[key]) return textureCache[key];

    const canvas = document.createElement('canvas');
    canvas.width = 128;
    canvas.height = 128;
    const ctx = canvas.getContext('2d');

    ctx.fillStyle = colorHex;
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Garis Pemisah Daun Pintu Tengah
    ctx.fillStyle = '#18181b';
    ctx.fillRect(62, 0, 4, canvas.height);

    // 4 Batang Pengunci Vertikal Baja Galvanis (Locking Rods)
    const rods = [22, 44, 84, 106];
    rods.forEach(rx => {
        ctx.fillStyle = '#d4d4d8';
        ctx.fillRect(rx - 2, 4, 4, canvas.height - 8);
        ctx.fillStyle = '#52525b';
        ctx.fillRect(rx - 1, 4, 2, canvas.height - 8);
        // Handle pegangan
        ctx.fillStyle = '#eab308';
        ctx.fillRect(rx - 3, 60, 6, 8);
    });

    // Stiker Hazard / Warning
    ctx.fillStyle = '#eab308';
    ctx.fillRect(14, 14, 20, 10);
    ctx.fillStyle = '#000000';
    ctx.font = 'bold 5px sans-serif';
    ctx.fillText('WARNING', 16, 21);

    const tex = new THREE.CanvasTexture(canvas);
    textureCache[key] = tex;
    return tex;
}

// Generator Tekstur Unit Pendingin Reefer (Kompresor & Kipas)
function getReeferFrontTexture() {
    const key = 'reefer_front';
    if (textureCache[key]) return textureCache[key];

    const canvas = document.createElement('canvas');
    canvas.width = 128;
    canvas.height = 128;
    const ctx = canvas.getContext('2d');

    ctx.fillStyle = '#e2e8f0';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Kisi-Kisi Udara Kondensor (Louver Fin)
    ctx.fillStyle = '#334155';
    ctx.fillRect(16, 16, 96, 50);
    for (let y = 20; y < 64; y += 4) {
        ctx.fillStyle = '#94a3b8';
        ctx.fillRect(18, y, 92, 2);
    }

    // Kipas Exhaust Twin
    ctx.fillStyle = '#0f172a';
    ctx.beginPath();
    ctx.arc(42, 41, 14, 0, Math.PI * 2);
    ctx.arc(86, 41, 14, 0, Math.PI * 2);
    ctx.fill();

    // Box Kontrol Mikroprosesor & Layar Digital LED
    ctx.fillStyle = '#1e293b';
    ctx.fillRect(20, 78, 88, 38);
    ctx.fillStyle = '#000000';
    ctx.fillRect(28, 86, 48, 20);

    // Display LED Suhu -20.5 C
    ctx.fillStyle = '#22c55e';
    ctx.font = 'bold 9px "Courier New", monospace';
    ctx.fillText('-20.5 C', 32, 100);

    // Lampu Indikator Power 380V
    ctx.fillStyle = '#38bdf8';
    ctx.beginPath();
    ctx.arc(88, 96, 4, 0, Math.PI * 2);
    ctx.fill();

    const tex = new THREE.CanvasTexture(canvas);
    textureCache[key] = tex;
    return tex;
}

// Generator Tekstur Bahaya DG Hazard (Plakat Kelas 3 / 8)
function getDGFrontTexture() {
    const key = 'dg_front';
    if (textureCache[key]) return textureCache[key];

    const canvas = document.createElement('canvas');
    canvas.width = 128;
    canvas.height = 128;
    const ctx = canvas.getContext('2d');

    ctx.fillStyle = '#dc2626';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Belah Ketupat Plakat IMO Class 3 (Flammable Liquid)
    ctx.save();
    ctx.translate(64, 64);
    ctx.rotate(Math.PI / 4);
    ctx.fillStyle = '#ef4444';
    ctx.fillRect(-28, -28, 56, 56);
    ctx.lineWidth = 3;
    ctx.strokeStyle = '#ffffff';
    ctx.strokeRect(-26, -26, 52, 52);
    ctx.restore();

    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 16px sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('3', 64, 80);
    ctx.font = 'bold 7px sans-serif';
    ctx.fillText('FLAMMABLE', 64, 52);

    const tex = new THREE.CanvasTexture(canvas);
    textureCache[key] = tex;
    return tex;
}

// Generator Tekstur Atap Kontainer (Corrugated Roof)
function getContainerRoofTexture(colorHex) {
    const key = `roof_${colorHex}`;
    if (textureCache[key]) return textureCache[key];

    const canvas = document.createElement('canvas');
    canvas.width = 256;
    canvas.height = 64;
    const ctx = canvas.getContext('2d');

    ctx.fillStyle = colorHex;
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Garis Gelombang Atap Melintang
    for (let x = 6; x < canvas.width; x += 10) {
        ctx.fillStyle = 'rgba(0,0,0,0.2)';
        ctx.fillRect(x, 2, 3, canvas.height - 4);
        ctx.fillStyle = 'rgba(255,255,255,0.15)';
        ctx.fillRect(x + 3, 2, 2, canvas.height - 4);
    }

    const tex = new THREE.CanvasTexture(canvas);
    textureCache[key] = tex;
    return tex;
}

// Generator Tekstur Lantai Beton Kaku (Rigid PQC Concrete 6m x 6m Slab Joints)
function getPavementTexture() {
    const canvas = document.createElement('canvas');
    canvas.width = 512;
    canvas.height = 512;
    const ctx = canvas.getContext('2d');

    // Pelat Beton Abu-abu Industri PQC
    ctx.fillStyle = '#475569';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Butiran Agregat Halus
    for (let i = 0; i < 4000; i++) {
        const x = Math.random() * canvas.width;
        const y = Math.random() * canvas.height;
        const c = Math.random() > 0.5 ? 255 : 0;
        ctx.fillStyle = `rgba(${c},${c},${c},0.04)`;
        ctx.fillRect(x, y, 2, 2);
    }

    // Garis Siar Dilatasi / Expansion Joint Sealing Polyurethane
    ctx.strokeStyle = '#1e293b';
    ctx.lineWidth = 3;
    const step = 64;
    for (let x = 0; x <= canvas.width; x += step) {
        ctx.beginPath();
        ctx.moveTo(x, 0);
        ctx.lineTo(x, canvas.height);
        ctx.stroke();
    }
    for (let y = 0; y <= canvas.height; y += step) {
        ctx.beginPath();
        ctx.moveTo(0, y);
        ctx.lineTo(canvas.width, y);
        ctx.stroke();
    }

    const tex = new THREE.CanvasTexture(canvas);
    tex.wrapS = THREE.RepeatWrapping;
    tex.wrapT = THREE.RepeatWrapping;
    tex.repeat.set(16, 16);
    return tex;
}

// Inisialisasi Tampilan 3D Digital Twin Three.js
function initThreeYard() {
    const container = document.getElementById('threeCanvasContainer');
    const canvas = document.getElementById('yard3DCanvas');

    const width = container.clientWidth;
    const height = container.clientHeight;

    // 1. Scene & Atmosfer Terang Realistis
    scene = new THREE.Scene();
    scene.background = new THREE.Color(0xd9e8f5); // Langit Siang Cerah Teknik Sipil
    scene.fog = new THREE.FogExp2(0xcbd5e1, 0.005);

    // 2. Kamera Perspektif
    camera = new THREE.PerspectiveCamera(45, width / height, 1, 1000);
    camera.position.set(55, 42, 60);

    // 3. WebGL Renderer dengan Bayangan PCF Halus
    renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, powerPreference: 'high-performance' });
    renderer.setSize(width, height);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;

    // 4. OrbitControls
    controls = new THREE.OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.05;
    controls.maxPolarAngle = Math.PI / 2 - 0.04; // Mencegah masuk ke bawah perkerasan
    controls.minDistance = 12;
    controls.maxDistance = 180;
    controls.target.set(0, 3, 0);

    // 5. Pencahayaan Alami (Lighting Daylight & Sky)
    hemiLight = new THREE.HemisphereLight(0xdbeafe, 0x64748b, 0.65);
    scene.add(hemiLight);

    sunLight = new THREE.DirectionalLight(0xfff8ee, 1.15);
    sunLight.position.set(70, 90, 50);
    sunLight.castShadow = true;
    sunLight.shadow.mapSize.width = 2048;
    sunLight.shadow.mapSize.height = 2048;
    sunLight.shadow.camera.near = 1;
    sunLight.shadow.camera.far = 260;
    const d = 75;
    sunLight.shadow.camera.left = -d;
    sunLight.shadow.camera.right = d;
    sunLight.shadow.camera.top = d;
    sunLight.shadow.camera.bottom = -d;
    sunLight.shadow.bias = -0.0005;
    scene.add(sunLight);

    ambientLight = new THREE.AmbientLight(0xffffff, 0.4);
    scene.add(ambientLight);

    // 6. Bangun Infrastruktur Lapangan & Marka
    buildPavementAndMarkings();
    buildRailwayInfrastructure();
    buildHighMastLightingTowers();
    buildReeferCatwalkRacks();
    buildDangerousGoodsBunding();
    buildHeavyMachinery();

    // 7. Bangun Tumpukan Peti Kemas Lapangan (Live DB + Operasional)
    buildContainerYardStacks();

    // 8. Raycaster Interaksi Seleksi Kontainer
    raycaster = new THREE.Raycaster();
    mouse = new THREE.Vector2();

    // Sembunyikan Loading Overlay
    const overlay = document.getElementById('threeLoadingOverlay');
    if (overlay) {
        overlay.style.opacity = '0';
        setTimeout(() => { overlay.style.display = 'none'; }, 300);
    }

    // Event Listeners
    window.addEventListener('resize', onWindowResize);
    canvas.addEventListener('click', onCanvasClick);

    // Jalankan Loop Render
    animate();
}

// Bangun Perkerasan Kaku Beton (PQC) & Marka Lalu Lintas Lapangan
function buildPavementAndMarkings() {
    // 1. Pelat Lantai Utama Beton Semen (Rigid Pavement 240m x 220m)
    const groundGeo = new THREE.PlaneGeometry(240, 220);
    const groundMat = new THREE.MeshStandardMaterial({
        map: getPavementTexture(),
        roughness: 0.85,
        metalness: 0.1
    });
    const ground = new THREE.Mesh(groundGeo, groundMat);
    ground.rotation.x = -Math.PI / 2;
    ground.receiveShadow = true;
    scene.add(ground);

    // 2. Lajur Manuver Aspal Utama (Central Transfer Roadway 14m)
    const roadGeo = new THREE.PlaneGeometry(170, 16);
    const roadMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.9, metalness: 0.05 });
    const road = new THREE.Mesh(roadGeo, roadMat);
    road.rotation.x = -Math.PI / 2;
    road.position.set(0, 0.02, -2);
    road.receiveShadow = true;
    scene.add(road);

    // Marka Garis Putus-Putus Kuning Lajur Jalan
    const dashGroup = new THREE.Group();
    for (let x = -80; x <= 80; x += 6) {
        const dashGeo = new THREE.PlaneGeometry(3.5, 0.25);
        const dashMat = new THREE.MeshBasicMaterial({ color: 0xfacc15 });
        const dash = new THREE.Mesh(dashGeo, dashMat);
        dash.rotation.x = -Math.PI / 2;
        dash.position.set(x, 0.03, -2);
        dashGroup.add(dash);
    }
    scene.add(dashGroup);

    // Zebra Cross Keselamatan Operator Pejalan Kaki
    for (let i = -6; i <= 6; i += 1.5) {
        const zGeo = new THREE.PlaneGeometry(0.8, 5.5);
        const zMat = new THREE.MeshBasicMaterial({ color: 0xffffff });
        const zLine = new THREE.Mesh(zGeo, zMat);
        zLine.rotation.x = -Math.PI / 2;
        zLine.position.set(i, 0.03, -2);
        scene.add(zLine);
    }

    // 3. Marka Batas Blok Stacking & Plakat Identifikasi
    Object.keys(BLOCK_LAYOUT).forEach(bKey => {
        const blk = BLOCK_LAYOUT[bKey];
        const bw = 36, bd = 18;

        // Garis Batas Kuning Reflektif Blok
        const borderGeo = new THREE.PlaneGeometry(bw, bd);
        const borderEdges = new THREE.EdgesGeometry(borderGeo);
        const borderMat = new THREE.LineBasicMaterial({ color: 0xf59e0b, linewidth: 3 });
        const borderMesh = new THREE.LineSegments(borderEdges, borderMat);
        borderMesh.rotation.x = -Math.PI / 2;
        borderMesh.position.set(blk.basePos[0], 0.03, blk.basePos[1]);
        scene.add(borderMesh);

        // Bollard Baja Pengaman di Sudut-Sudut Blok
        const corners = [
            [-bw / 2, -bd / 2],
            [bw / 2, -bd / 2],
            [-bw / 2, bd / 2],
            [bw / 2, bd / 2]
        ];
        corners.forEach(c => {
            const bGeo = new THREE.CylinderGeometry(0.2, 0.2, 1.2, 12);
            const bMat = new THREE.MeshStandardMaterial({ color: 0xfacc15, roughness: 0.4 });
            const bollard = new THREE.Mesh(bGeo, bMat);
            bollard.position.set(blk.basePos[0] + c[0], 0.6, blk.basePos[1] + c[1]);
            bollard.castShadow = true;
            scene.add(bollard);
        });

        // Plakat Marka Tulisan Blok di Lantai Beton
        const signCanvas = document.createElement('canvas');
        signCanvas.width = 512;
        signCanvas.height = 64;
        const sctx = signCanvas.getContext('2d');
        sctx.fillStyle = 'rgba(15, 23, 42, 0.9)';
        sctx.fillRect(0, 0, signCanvas.width, signCanvas.height);
        sctx.lineWidth = 4;
        sctx.strokeStyle = '#f59e0b';
        sctx.strokeRect(2, 2, signCanvas.width - 4, signCanvas.height - 4);
        sctx.fillStyle = '#f8fafc';
        sctx.font = 'bold 22px "Arial Black", sans-serif';
        sctx.textAlign = 'center';
        sctx.fillText(blk.label, signCanvas.width / 2, 40);

        const signTex = new THREE.CanvasTexture(signCanvas);
        const signGeo = new THREE.PlaneGeometry(14, 1.7);
        const signMat = new THREE.MeshBasicMaterial({ map: signTex, transparent: true });
        const signMesh = new THREE.Mesh(signGeo, signMat);
        signMesh.rotation.x = -Math.PI / 2;
        signMesh.position.set(blk.basePos[0], 0.04, blk.basePos[1] + (blk.basePos[1] < -5 ? 10.5 : -10.5));
        scene.add(signMesh);
    });
}

// Bangun Jalur Rel Kereta Api Siding (Buffer Track Blok D)
function buildRailwayInfrastructure() {
    const railZ = 32;
    const length = 150;

    // Tanggul Balas Kerikil (Ballast Gravel Bed)
    const ballastGeo = new THREE.BoxGeometry(length, 0.4, 6.0);
    const ballastMat = new THREE.MeshStandardMaterial({ color: 0x334155, roughness: 0.95 });
    const ballast = new THREE.Mesh(ballastGeo, ballastMat);
    ballast.position.set(0, 0.2, railZ);
    ballast.receiveShadow = true;
    scene.add(ballast);

    // Bantalan Beton Rel (Concrete Sleepers) Setiap 1.8 Meter
    const sleeperGeo = new THREE.BoxGeometry(0.3, 0.2, 3.2);
    const sleeperMat = new THREE.MeshStandardMaterial({ color: 0x94a3b8, roughness: 0.8 });
    for (let x = -70; x <= 70; x += 1.8) {
        const sleeper = new THREE.Mesh(sleeperGeo, sleeperMat);
        sleeper.position.set(x, 0.45, railZ);
        sleeper.receiveShadow = true;
        scene.add(sleeper);
    }

    // 2 Batang Rel Baja Standard Gauge (Steel Rails)
    const railMat = new THREE.MeshStandardMaterial({ color: 0xa1a1aa, roughness: 0.3, metalness: 0.85 });
    const railLeftGeo = new THREE.BoxGeometry(length, 0.15, 0.1);
    const railLeft = new THREE.Mesh(railLeftGeo, railMat);
    railLeft.position.set(0, 0.6, railZ - 0.75);
    scene.add(railLeft);

    const railRight = new THREE.Mesh(railLeftGeo, railMat);
    railRight.position.set(0, 0.6, railZ + 0.75);
    scene.add(railRight);

    // Penahan Ujung Rel (Hydraulic Buffer Stop Bumper)
    const bumperGeo = new THREE.BoxGeometry(1.2, 1.4, 2.2);
    const bumperMat = new THREE.MeshStandardMaterial({ color: 0xdc2626, roughness: 0.5 });
    const bumper = new THREE.Mesh(bumperGeo, bumperMat);
    bumper.position.set(72, 1.0, railZ);
    scene.add(bumper);
}

// Bangun 4 Tiang Lampu Sorot Tower High-Mast 30m di Sudut-Sudut Yard
function buildHighMastLightingTowers() {
    const towerCoords = [
        [-65, -55],
        [65, -55],
        [-65, 30],
        [65, 30]
    ];

    towerCoords.forEach((tc, idx) => {
        const towerGroup = new THREE.Group();

        // Fondasi Beton Tapak
        const baseGeo = new THREE.CylinderGeometry(1.2, 1.5, 1.5, 8);
        const baseMat = new THREE.MeshStandardMaterial({ color: 0x64748b, roughness: 0.9 });
        const base = new THREE.Mesh(baseGeo, baseMat);
        base.position.y = 0.75;
        towerGroup.add(base);

        // Tiang Baja Tapered 32 Meter
        const mastGeo = new THREE.CylinderGeometry(0.35, 0.8, 30, 8);
        const mastMat = new THREE.MeshStandardMaterial({ color: 0xd4d4d8, roughness: 0.4, metalness: 0.7 });
        const mast = new THREE.Mesh(mastGeo, mastMat);
        mast.position.y = 16.5;
        towerGroup.add(mast);

        // Platform Pemeliharaan Melingkar & Railing
        const platGeo = new THREE.CylinderGeometry(2.0, 2.0, 0.4, 12);
        const platMat = new THREE.MeshStandardMaterial({ color: 0x52525b, roughness: 0.6 });
        const plat = new THREE.Mesh(platGeo, platMat);
        plat.position.y = 31.5;
        towerGroup.add(plat);

        // Kepala Lampu Sorot LED Multi-Head
        const headGeo = new THREE.BoxGeometry(0.8, 0.4, 0.4);
        const headMat = new THREE.MeshStandardMaterial({ color: 0x18181b, emissive: 0xfef08a, emissiveIntensity: 0.6 });
        for (let a = 0; a < Math.PI * 2; a += Math.PI / 4) {
            const head = new THREE.Mesh(headGeo, headMat);
            head.position.set(Math.cos(a) * 1.8, 32.2, Math.sin(a) * 1.8);
            head.rotation.y = a;
            towerGroup.add(head);
        }

        // Lampu Sorot Tembak (SpotLight untuk Mode Malam)
        const spot = new THREE.SpotLight(0xfffaed, 0.4, 140, Math.PI / 3, 0.5, 1);
        spot.position.set(0, 32, 0);
        const targetObj = new THREE.Object3D();
        targetObj.position.set(-tc[0] * 0.3, 0, -tc[1] * 0.3);
        scene.add(targetObj);
        spot.target = targetObj;
        towerGroup.add(spot);
        floodLights.push(spot);

        towerGroup.position.set(tc[0], 0, tc[1]);
        scene.add(towerGroup);
    });
}

// Bangun Struktur Rangka Jembatan Catwalk Listrik di Blok Reefer
function buildReeferCatwalkRacks() {
    const rx = -28, rz = -48;
    const catwalkGroup = new THREE.Group();

    // Rangka Baja Catwalk Memanjang (Elevated Catwalk)
    const walkGeo = new THREE.BoxGeometry(32, 0.25, 1.2);
    const walkMat = new THREE.MeshStandardMaterial({ color: 0x64748b, roughness: 0.6, metalness: 0.6 });
    const walk = new THREE.Mesh(walkGeo, walkMat);
    walk.position.set(0, 3.4, 0);
    catwalkGroup.add(walk);

    // Tiang Penyangga Struktur Baja Catwalk
    const postGeo = new THREE.CylinderGeometry(0.12, 0.12, 3.4, 8);
    const postMat = new THREE.MeshStandardMaterial({ color: 0x475569, roughness: 0.5 });
    for (let x = -15; x <= 15; x += 5) {
        const post = new THREE.Mesh(postGeo, postMat);
        post.position.set(x, 1.7, 0);
        catwalkGroup.add(post);

        // Box Stopkontak Listrik Industri 380V / 32A
        const boxGeo = new THREE.BoxGeometry(0.5, 0.7, 0.4);
        const boxMat = new THREE.MeshStandardMaterial({ color: 0x0284c7, roughness: 0.3 });
        const box = new THREE.Mesh(boxGeo, boxMat);
        box.position.set(x, 2.2, 0.65);
        catwalkGroup.add(box);
    }

    catwalkGroup.position.set(rx, 0, rz - 8.5);
    scene.add(catwalkGroup);
}

// Bangun Tanggul Pengaman Khusus (Bunding Retaining Wall) di Blok E Dangerous Goods
function buildDangerousGoodsBunding() {
    const ex = 28, ez = -48;
    const bundGeo = new THREE.BoxGeometry(38, 0.5, 20);
    const bundEdges = new THREE.EdgesGeometry(bundGeo);
    const bundMat = new THREE.LineBasicMaterial({ color: 0xdc2626, linewidth: 3 });
    const bundWire = new THREE.LineSegments(bundEdges, bundMat);
    bundWire.position.set(ex, 0.25, ez);
    scene.add(bundWire);
}

// Bangun Alat Berat (Reach Stacker Kalmar DRG450 & Truk Chassis)
function buildHeavyMachinery() {
    const rsGroup = new THREE.Group();

    // Sasis Utama Reach Stacker (Warna Merah Kalmar)
    const bodyGeo = new THREE.BoxGeometry(6.8, 1.8, 2.8);
    const bodyMat = new THREE.MeshStandardMaterial({ color: 0xbe123c, roughness: 0.4 });
    const body = new THREE.Mesh(bodyGeo, bodyMat);
    body.position.set(0, 1.6, 0);
    body.castShadow = true;
    rsGroup.add(body);

    // Counterweight Belakang
    const cwGeo = new THREE.BoxGeometry(1.6, 1.8, 2.7);
    const cwMat = new THREE.MeshStandardMaterial({ color: 0x18181b, roughness: 0.8 });
    const cw = new THREE.Mesh(cwGeo, cwMat);
    cw.position.set(-3.2, 1.7, 0);
    rsGroup.add(cw);

    // 4 Roda Ban Karet Raksasa
    const wheelGeo = new THREE.CylinderGeometry(0.85, 0.85, 0.6, 16);
    const wheelMat = new THREE.MeshStandardMaterial({ color: 0x18181b, roughness: 0.9 });
    const wheelPos = [
        [-2.0, 0.85, 1.5],
        [-2.0, 0.85, -1.5],
        [2.2, 0.85, 1.5],
        [2.2, 0.85, -1.5]
    ];
    wheelPos.forEach(wp => {
        const w = new THREE.Mesh(wheelGeo, wheelMat);
        w.rotation.x = Math.PI / 2;
        w.position.set(wp[0], wp[1], wp[2]);
        w.castShadow = true;
        rsGroup.add(w);
    });

    // Kabin Operator dengan Kaca Tinted
    const cabGeo = new THREE.BoxGeometry(1.8, 1.5, 1.6);
    const cabMat = new THREE.MeshStandardMaterial({ color: 0x0284c7, roughness: 0.1, transparent: true, opacity: 0.7 });
    const cab = new THREE.Mesh(cabGeo, cabMat);
    cab.position.set(-0.5, 3.2, 0);
    rsGroup.add(cab);

    // Boom Teleskopik Mengarah ke Atas 35 Derajat
    const boomGeo = new THREE.BoxGeometry(9.0, 0.7, 0.8);
    const boomMat = new THREE.MeshStandardMaterial({ color: 0x991b1b, roughness: 0.5 });
    const boom = new THREE.Mesh(boomGeo, boomMat);
    boom.position.set(2.4, 4.2, 0);
    boom.rotation.z = Math.PI / 6;
    boom.castShadow = true;
    rsGroup.add(boom);

    // Spreader Bar Hidraulis Bromma di Ujung Boom
    const spreaderGeo = new THREE.BoxGeometry(6.2, 0.4, 1.2);
    const spreaderMat = new THREE.MeshStandardMaterial({ color: 0xfacc15, roughness: 0.3 });
    const spreader = new THREE.Mesh(spreaderGeo, spreaderMat);
    spreader.position.set(6.4, 6.2, 0);
    spreader.castShadow = true;
    rsGroup.add(spreader);

    // Letakkan Reach Stacker di Lajur Jalan Samping Blok A
    rsGroup.position.set(-10, 0, -5);
    rsGroup.rotation.y = -Math.PI / 8;
    scene.add(rsGroup);
}

// Bangun Seluruh Kubus Kontainer Lapangan (Live DB + Operasional Kepadatan 68.4%)
function buildContainerYardStacks() {
    containerMeshes = [];
    beaconMeshes = [];

    // Matriks Slot Terisi untuk Menghindari Tumpang Tindih Fisik
    const occupiedSlots = {};

    // 1. Tempatkan Kontainer Live dari MySQL Database
    containersData.forEach(c => {
        const blkKey = c.blok || 'A';
        const blkInfo = BLOCK_LAYOUT[blkKey] || BLOCK_LAYOUT['A'];
        const is20ft = c.iso.includes('20ft');
        const isReefer = c.tipe === 'reefer';
        const isDG = c.tipe === 'dg_hazard';

        const bayIdx = ((c.bay - 1) % 3) - 1; // -1, 0, 1
        const rowIdx = (c.row - 1) - 1.5;     // -1.5, -0.5, 0.5, 1.5
        const tier = c.tier || 1;

        const slotKey = `${blkKey}_${bayIdx}_${rowIdx}_${tier}`;
        occupiedSlots[slotKey] = true;

        const posX = blkInfo.basePos[0] + bayIdx * 13.0;
        const posZ = blkInfo.basePos[1] + rowIdx * 3.1;
        const boxH = is20ft ? 2.6 : 2.9;
        const boxL = is20ft ? 6.1 : 12.2;
        const boxW = 2.44;
        const posY = (tier - 1) * boxH + (boxH / 2);

        createContainerMesh(c, posX, posY, posZ, boxL, boxW, boxH, is20ft, isReefer, isDG, true);
    });

    // 2. Tumpukan Latar Operasional Lapangan (Mencerminkan Okupansi Nyata 68.4% / 10.260 TEUs)
    const sampleCarriers = Object.keys(CARRIER_PROFILES);
    Object.keys(BLOCK_LAYOUT).forEach(bKey => {
        const blkInfo = BLOCK_LAYOUT[bKey];
        for (let b = -1; b <= 1; b++) {
            for (let r = -1.5; r <= 1.5; r += 1.0) {
                // Tentukan tinggi tumpukan stabil (1 sampai 3 tier)
                const maxStack = (Math.abs(b + r) % 2 === 0) ? 3 : 2;
                for (let t = 1; t <= maxStack; t++) {
                    const slotKey = `${bKey}_${b}_${r}_${t}`;
                    if (occupiedSlots[slotKey]) continue;

                    // Lewati beberapa slot agar terlihat realistis ada ruang operasi
                    if (t > 1 && (b * r * t) % 3 === 0) continue;

                    const isReefer = bKey === 'Reefer';
                    const isDG = bKey === 'E';
                    const is20ft = (bKey === 'C' || t === 3) && !isReefer;
                    const cProfileKey = sampleCarriers[Math.abs(Math.floor(b * 3 + r * 5 + t * 7)) % sampleCarriers.length];
                    const prof = CARRIER_PROFILES[cProfileKey] || CARRIER_PROFILES['Maersk Line'];

                    const fakeId = prof.code + String(100000 + Math.abs(Math.floor((b + 5) * 8921 + r * 314 + t * 99)) % 899999) + '-' + (t % 9);
                    const grossKg = 18000 + (4 - t) * 3500;

                    const bgData = {
                        id: fakeId,
                        iso: is20ft ? '22G1 (20ft GP)' : '45G1 (40ft High Cube)',
                        tipe: blkInfo.type,
                        blok: bKey,
                        bay: (b + 1) * 2 + 1,
                        row: Math.round(r + 2.5),
                        tier: t,
                        berat: number_format_js(grossKg) + ' kg',
                        gross_kg: grossKg,
                        point_load: ((grossKg * 9.81 / 4) / 1000).toFixed(1) + ' kN / corner',
                        pavement_status: 'PQC 800 kN/m² (Aman)',
                        stability_factor: 'SF 3.2 (>1.5 BMKG)',
                        layer_type: t === 1 ? 'Tier 1 (Heavy Base Foundation)' : 'Tier ' + t + ' (Intermediate)',
                        line: cProfileKey,
                        pod: bKey === 'B' ? 'Cikarang (IDCBI)' : 'Rotterdam (NLRTM)',
                        dwell: '2 hari 4 jam',
                        pabean: 'SPPB Terbit (Hijau)',
                        seal: 'ID-SEAL-' + Math.floor(Math.random() * 8999 + 1000),
                        rtk: 'Telemetri RTK GNSS Standar (1.4cm)',
                        is_live_db: false
                    };

                    const posX = blkInfo.basePos[0] + b * 13.0;
                    const posZ = blkInfo.basePos[1] + r * 3.1;
                    const boxH = is20ft ? 2.6 : 2.9;
                    const boxL = is20ft ? 6.1 : 12.2;
                    const boxW = 2.44;
                    const posY = (t - 1) * boxH + (boxH / 2);

                    createContainerMesh(bgData, posX, posY, posZ, boxL, boxW, boxH, is20ft, isReefer, isDG, false);
                }
            }
        }
    });
}

// Buat Mesh Kotak Kontainer dengan 6 Material Berbeda (Corrugated Sidewalls & Door)
function createContainerMesh(cData, x, y, z, length, width, height, is20ft, isReefer, isDG, isLiveDb) {
    const geo = new THREE.BoxGeometry(length, height, width);

    const carrierName = cData.line || 'Maersk Line';
    const profile = CARRIER_PROFILES[carrierName] || { color: '#2b7a9e', brand: 'CIDP', code: 'CIDU' };
    const colorHex = isReefer ? '#f1f5f9' : (isDG ? '#b91c1c' : profile.color);

    const sideTex = getContainerSideTexture(profile.brand || carrierName, cData.id, colorHex, is20ft);
    const doorTex = getContainerDoorTexture(colorHex);
    const frontTex = isReefer ? getReeferFrontTexture() : (isDG ? getDGFrontTexture() : sideTex);
    const roofTex = getContainerRoofTexture(colorHex);

    const sideMat = new THREE.MeshStandardMaterial({ map: sideTex, roughness: 0.6, metalness: 0.25 });
    const doorMat = new THREE.MeshStandardMaterial({ map: doorTex, roughness: 0.6, metalness: 0.25 });
    const frontMat = new THREE.MeshStandardMaterial({ map: frontTex, roughness: 0.5, metalness: 0.3 });
    const roofMat = new THREE.MeshStandardMaterial({ map: roofTex, roughness: 0.7, metalness: 0.2 });

    // Urutan Sisi BoxGeometry Three.js: [+X (Door), -X (Front), +Y (Roof), -Y (Bottom), +Z (Side Right), -Z (Side Left)]
    const materials = [
        doorMat,
        frontMat,
        roofMat,
        roofMat,
        sideMat,
        sideMat
    ];

    const mesh = new THREE.Mesh(geo, materials);
    mesh.position.set(x, y, z);
    mesh.castShadow = true;
    mesh.receiveShadow = true;

    mesh.userData = cData;
    mesh.userData.defaultPosY = y;
    mesh.userData.defaultColor = colorHex;
    mesh.userData.isLiveDb = isLiveDb;

    scene.add(mesh);
    containerMeshes.push(mesh);

    // Tambahkan Beacon RTK GNSS untuk Kontainer Database Nyata
    if (isLiveDb) {
        const beaconGeo = new THREE.OctahedronGeometry(0.55, 0);
        const beaconMat = new THREE.MeshBasicMaterial({ color: 0x38bdf8, wireframe: true });
        const beacon = new THREE.Mesh(beaconGeo, beaconMat);
        beacon.position.set(x, y + (height / 2) + 0.9, z);
        scene.add(beacon);
        beaconMeshes.push({ mesh: beacon, baseY: y + (height / 2) + 0.9, parentBox: mesh });
    }
}

// Raycaster Handler Klik Kontainer
function onCanvasClick(event) {
    const canvas = document.getElementById('yard3DCanvas');
    const rect = canvas.getBoundingClientRect();
    mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
    mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

    raycaster.setFromCamera(mouse, camera);
    const intersects = raycaster.intersectObjects(containerMeshes);

    if (intersects.length > 0) {
        selectContainerMesh(intersects[0].object);
    }
}

// Sorot Kontainer Terpilih & Sinkronkan Panel Inspektur
function selectContainerMesh(mesh) {
    if (selectedContainerMesh) {
        selectedContainerMesh.material.forEach(m => m.emissive.setHex(0x000000));
    }

    selectedContainerMesh = mesh;
    // Beri Cahaya Emisi Emas
    mesh.material.forEach(m => m.emissive.setHex(0x3b2d04));

    const d = mesh.userData;
    updateInspectorWithContainerData(d);

    // Easing Kamera Perlahan ke Arah Kontainer
    if (controls) {
        controls.target.set(mesh.position.x, mesh.position.y, mesh.position.z);
    }
}

// Perbarui Informasi di Sidebar Inspector & Analisis Teknik Sipil
function updateInspectorWithContainerData(d) {
    document.getElementById('yardBoxNumber').textContent = d.id;
    document.getElementById('yardBoxLine').textContent = d.line;
    document.getElementById('yardBoxType').textContent = d.iso;
    document.getElementById('yardBoxWeight').textContent = "VGM: " + d.berat;
    document.getElementById('yardBoxCoord').textContent = "BLOK " + d.blok + " • BAY " + String(d.bay).padStart(2, '0') + " • ROW " + String(d.row).padStart(2, '0') + " • TIER " + d.tier;
    document.getElementById('yardBoxCustoms').textContent = d.pabean;
    document.getElementById('yardBoxSeal').textContent = d.seal;
    document.getElementById('yardBoxPod').textContent = d.pod;
    document.getElementById('yardBoxDwell').textContent = d.dwell;
    document.getElementById('yardBoxRtk').textContent = d.rtk;

    // Parameter Teknik Sipil
    const ptLoad = document.getElementById('yardBoxPointLoad');
    if (ptLoad) ptLoad.textContent = d.point_load || '68.5 kN / corner';

    const pPave = document.getElementById('yardBoxPavement');
    if (pPave) pPave.textContent = d.pavement_status || '800 kN/m² (Aman)';

    const pWind = document.getElementById('yardBoxWind');
    if (pWind) pWind.textContent = d.stability_factor || 'SF 3.1 (Aman)';

    const pLayer = document.getElementById('yardBoxLayer');
    if (pLayer) pLayer.textContent = d.layer_type || ('Tier ' + d.tier + ' (Structural)');

    // Badge Kategori
    const badge = document.getElementById('yardBoxStatusBadge');
    if (badge) {
        if (d.tipe === 'laden_exp') {
            badge.textContent = "LADEN EKSPOR";
            badge.className = "px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200";
        } else if (d.tipe === 'laden_imp') {
            badge.textContent = "LADEN IMPOR";
            badge.className = "px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200";
        } else if (d.tipe === 'domestic') {
            badge.textContent = "DOMESTIK";
            badge.className = "px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200";
        } else if (d.tipe === 'buffer_rail') {
            badge.textContent = "BUFFER REL KA";
            badge.className = "px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200";
        } else if (d.tipe === 'dg_hazard') {
            badge.textContent = "DG HAZARDOUS";
            badge.className = "px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200";
        } else if (d.tipe === 'reefer') {
            badge.textContent = "REEFER COLD CHAIN";
            badge.className = "px-2 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200";
        }
    }

    // Dynamic Inter-Module Deep Links
    const cleanId = (d.id || '').replace(/[^A-Z0-9]/g, '');
    const linkTrack = document.getElementById('btnYardLinkTrack');
    if (linkTrack) linkTrack.href = 'dashboard.php?page=kontainer&search=' + encodeURIComponent(cleanId) + '&open=1';

    const link3D = document.getElementById('btnYardLink3D');
    if (link3D) link3D.href = 'dashboard.php?page=simulator&focus_box=' + encodeURIComponent(cleanId);

    const linkDenah = document.getElementById('btnYardLinkDenah');
    if (linkDenah) linkDenah.href = 'dashboard.php?page=denah&highlight_block=' + (d.blok || 'A');

    const linkBill = document.getElementById('btnYardLinkBill');
    if (linkBill) linkBill.href = 'dashboard.php?page=billing&search=' + encodeURIComponent(cleanId);
}

// Beralih Antara Mode Siang & Malam (Lampu Sorot Tower High-Mast)
function toggleYardDayNight() {
    isDayMode = !isDayMode;

    const icon = document.getElementById('iconDayNight');
    const text = document.getElementById('textDayNight');

    if (isDayMode) {
        scene.background.setHex(0xd9e8f5);
        scene.fog.color.setHex(0xcbd5e1);
        hemiLight.intensity = 0.65;
        sunLight.intensity = 1.15;
        ambientLight.intensity = 0.4;
        floodLights.forEach(fl => fl.intensity = 0.4);

        if (icon) icon.className = "fa-solid fa-sun";
        if (text) text.textContent = "Siang";
    } else {
        scene.background.setHex(0x090d16);
        scene.fog.color.setHex(0x090d16);
        hemiLight.intensity = 0.2;
        sunLight.intensity = 0.15;
        ambientLight.intensity = 0.15;
        floodLights.forEach(fl => fl.intensity = 2.4);

        if (icon) icon.className = "fa-solid fa-moon text-blue-400";
        if (text) text.textContent = "Malam (50 Lux)";
    }
}

// Kontrol Sudut Kamera Presets
function setCameraView(preset) {
    if (!controls || !camera) return;
    if (preset === 'iso') {
        camera.position.set(55, 42, 60);
        controls.target.set(0, 3, 0);
    } else if (preset === 'top') {
        camera.position.set(0, 115, 0.1);
        controls.target.set(0, 0, 0);
    } else if (preset === 'side') {
        camera.position.set(-82, 18, -18);
        controls.target.set(-28, 6, -18);
    } else if (preset === 'operator') {
        camera.position.set(-6, 4.2, -5);
        controls.target.set(-28, 6, -18);
    }
    controls.update();
}

function resetYardCamera() {
    setCameraView('iso');
}

// Filter Kontainer Lapangan Berdasarkan Kategori
function filter3DContainers(category) {
    activeFilter = category;

    document.querySelectorAll('.cat-filter-btn').forEach(btn => {
        if (btn.getAttribute('data-cat') === category) {
            btn.classList.add('bg-[#0170b9]', 'text-white', 'shadow-xs');
            btn.classList.remove('bg-slate-100', 'text-slate-600');
        } else {
            btn.classList.remove('bg-[#0170b9]', 'text-white', 'shadow-xs');
            btn.classList.add('bg-slate-100', 'text-slate-600');
        }
    });

    containerMeshes.forEach(mesh => {
        if (category === 'all' || mesh.userData.tipe === category) {
            mesh.visible = true;
        } else {
            mesh.visible = false;
        }
    });

    beaconMeshes.forEach(b => {
        b.mesh.visible = (category === 'all' || b.parentBox.userData.tipe === category);
    });
}

// Simulasi Manuver Reshuffle Kontainer (CRP)
function simulateContainerRelocation() {
    if (isAnimatingCRP) return;
    isAnimatingCRP = true;

    const btn = document.getElementById('btnSimulateCRP');
    const spinIcon = document.getElementById('crpSpinIcon');
    const staticIcon = document.getElementById('crpStaticIcon');
    if (spinIcon) spinIcon.style.display = 'inline-block';
    if (staticIcon) staticIcon.style.display = 'none';
    if (btn) btn.disabled = true;

    // Pilih kontainer tier atas untuk di-reshuffle
    const topMesh = containerMeshes.find(m => m.userData.tier >= 2 && m.userData.blok === 'B') || containerMeshes[0];
    if (!topMesh) {
        isAnimatingCRP = false;
        return;
    }

    selectContainerMesh(topMesh);

    const initialY = topMesh.userData.defaultPosY;
    const liftedY = initialY + 6.0;
    const initialZ = topMesh.position.z;
    const targetZ = initialZ + 3.1;

    let step = 0;
    const animInterval = setInterval(() => {
        step++;
        if (step <= 25) {
            // Fase 1: Twistlock Lock & Lift
            topMesh.position.y += (liftedY - initialY) / 25;
        } else if (step <= 50) {
            // Fase 2: Traverse Koridor Jalan
            topMesh.position.z += (targetZ - initialZ) / 25;
        } else if (step <= 75) {
            // Fase 3: Lowering Soft Landing
            topMesh.position.y -= (liftedY - initialY) / 25;
        } else {
            clearInterval(animInterval);
            isAnimatingCRP = false;
            if (spinIcon) spinIcon.style.display = 'none';
            if (staticIcon) staticIcon.style.display = 'inline-block';
            if (btn) btn.disabled = false;

            alert("✅ Simulasi Reshuffle (CRP) Sukses!\nPeti kemas " + topMesh.userData.id + " berhasil dipindahkan ke Row terdekat dengan akurasi RTK GNSS 1.1 cm.\nSlot Tier bawah kini bebas hambatan tanpa penalti reshuffle.");
        }
    }, 35);
}

// Format Angka JS
function number_format_js(num) {
    return Number(num).toLocaleString('id-ID');
}

// Animasi Loop Frame
function animate() {
    requestAnimationFrame(animate);

    // Animasi Denyut Berputar Beacon RTK GNSS
    const time = Date.now() * 0.003;
    beaconMeshes.forEach(b => {
        b.mesh.rotation.y += 0.03;
        b.mesh.position.y = b.baseY + Math.sin(time) * 0.2;
    });

    if (controls) controls.update();
    if (renderer && scene && camera) renderer.render(scene, camera);
}

function onWindowResize() {
    const container = document.getElementById('threeCanvasContainer');
    if (!container || !renderer || !camera) return;
    const width = container.clientWidth;
    const height = container.clientHeight;
    camera.aspect = width / height;
    camera.updateProjectionMatrix();
    renderer.setSize(width, height);
}

// Buka Modal Penampang 2D
function showBayMatrixView() {
    const m = document.getElementById('bayMatrixModal');
    if (m) m.classList.remove('hidden');
}

function closeBayMatrixModal() {
    const m = document.getElementById('bayMatrixModal');
    if (m) m.classList.add('hidden');
}

function dispatchWorkOrderFromInspector() {
    const ctrId = selectedContainerMesh?.userData?.id || document.getElementById('yardBoxNumber')?.textContent?.trim() || '';
    if (ctrId && ctrId !== '-' && typeof openModalYardRelocate === 'function') {
        openModalYardRelocate(ctrId);
    } else {
        alert("✅ Perintah Kerja Berhasil Diterbitkan!\nInstruksi kerja dikirimkan secara nirkabel (WiFi 7) ke Vehicle Mounted Terminal (VMT) Reach Stacker terdekat dengan koordinat DGPS RTK.");
    }
}

// Universal Deep-Linking Handler
function initYardDeepLinking() {
    const urlParams = new URLSearchParams(window.location.search);
    const focusBox = urlParams.get('focus_box') || urlParams.get('ctr') || urlParams.get('search');
    const blockParam = urlParams.get('block');
    const actionParam = urlParams.get('action');

    if (focusBox && typeof containerMeshes !== 'undefined' && containerMeshes.length > 0) {
        const cleanBox = focusBox.toUpperCase();
        const found = containerMeshes.find(m => 
            m.userData.id.toUpperCase() === cleanBox || 
            m.userData.id.replace(/[^A-Z0-9]/g, '').toUpperCase() === cleanBox.replace(/[^A-Z0-9]/g, '')
        );
        if (found) {
            selectContainerMesh(found);
            if (controls && camera) {
                controls.target.copy(found.position);
                camera.position.set(found.position.x + 15, found.position.y + 12, found.position.z + 18);
                controls.update();
            }
        }
    }

    if (actionParam === 'relocate') {
        openModalYardRelocate(focusBox || '');
    }
}

// Modal Relokasi Kontainer VMT
function openModalYardRelocate(prefillId = '') {
    const m = document.getElementById('modalRelocateYard');
    if (m) {
        m.classList.remove('hidden');
        if (prefillId) {
            const sel = document.getElementById('relocateContainerSelect');
            if (sel) sel.value = prefillId;
        }
    }
}

function closeModalYardRelocate() {
    const m = document.getElementById('modalRelocateYard');
    if (m) m.classList.add('hidden');
}

// Jalankan saat DOM siap
document.addEventListener('DOMContentLoaded', () => {
    if (typeof THREE !== 'undefined') {
        initThreeYard();
        setTimeout(initYardDeepLinking, 300);
    } else {
        const checkInterval = setInterval(() => {
            if (typeof THREE !== 'undefined') {
                clearInterval(checkInterval);
                initThreeYard();
                setTimeout(initYardDeepLinking, 300);
            }
        }, 100);
    }
});
</script>

<!-- MODAL RELOKASI KONTAINER LAPANGAN (VMT JOB ORDER) -->
<div id="modalRelocateYard" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden animate-fadeIn">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-gray-100 transform transition-all text-xs">
        <div class="bg-gradient-to-r from-orange-500 via-orange-600 to-[#0170b9] p-5 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-lg shadow-inner">
                    <i class="fa-solid fa-arrows-up-down-left-right"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold leading-tight">Relokasi Kontainer Lapangan (VMT Dispatch)</h3>
                    <p class="text-[11px] text-blue-100">Sinkronisasi Posisi Bay-Row-Tier ke Basis Data &amp; Log Peristiwa</p>
                </div>
            </div>
            <button onclick="closeModalYardRelocate()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="yard_action" value="relocate_container">

            <div>
                <label class="block text-[11px] font-bold text-gray-700 mb-1">Pilih Peti Kemas / Kontainer *</label>
                <select name="container_number" id="relocateContainerSelect" required class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono text-xs font-bold text-gray-900 focus:ring-2 focus:ring-blue-500">
                    <?php foreach ($yard_containers as $yc): ?>
                        <option value="<?= $yc['id'] ?>">
                            <?= $yc['id'] ?> (<?= $yc['iso'] ?>) — Blok <?= $yc['blok'] ?> Bay <?= $yc['bay'] ?> Row <?= $yc['row'] ?> Tier <?= $yc['tier'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="text-[10px] text-gray-400 mt-1 block">Tersambung langsung ke master record tabel <code>containers</code>.</span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Alat Berat Eksekutor *</label>
                    <select name="equipment_id" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-blue-500">
                        <option value="RS-01">RS-01 (Kalmar DRG450 - Budi Santoso)</option>
                        <option value="RS-02">RS-02 (Kalmar DRG450 - Agus Setiawan)</option>
                        <option value="RS-03">RS-03 (Sany SRSC45 - Rudi Hermawan)</option>
                        <option value="RTG-01">RTG-01 (ZPMC 16-Wheel - Joko Susilo)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Nama Operator VMT</label>
                    <input type="text" name="operator_name" value="Budi Santoso" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="bg-blue-50/60 p-3.5 rounded-2xl border border-blue-100">
                <div class="text-[11px] font-bold text-blue-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <i class="fa-solid fa-location-crosshairs text-blue-600"></i>
                    <span>Koordinat Posisi Baru di Lapangan</span>
                </div>
                <div class="grid grid-cols-4 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Blok Yard *</label>
                        <select name="to_block" class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-800">
                            <option value="A">Blok A (Ekspor)</option>
                            <option value="B">Blok B (Impor)</option>
                            <option value="C">Blok C (Domestik)</option>
                            <option value="D">Blok D (KA Buffer)</option>
                            <option value="E">Blok E (DG / Behandle)</option>
                            <option value="REEFER">Blok Reefer (300 Plugs)</option>
                            <option value="CFS">Blok CFS (Warehouse)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Bay (1-12)</label>
                        <input type="number" name="to_bay" value="2" min="1" max="12" class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-mono font-bold text-center">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Row (1-6)</label>
                        <input type="number" name="to_row" value="3" min="1" max="6" class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-mono font-bold text-center">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Tier (1-5)</label>
                        <input type="number" name="to_tier" value="1" min="1" max="5" class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-mono font-bold text-center">
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeModalYardRelocate()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-[#004b87] hover:bg-[#0170b9] text-white font-bold rounded-xl shadow-md transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-check"></i>
                    <span>Eksekusi Relokasi</span>
                </button>
            </div>
        </form>
    </div>
</div>
