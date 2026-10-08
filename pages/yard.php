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
                $blk = !empty($c['block']) ? strtoupper($c['block']) : 'A';
                $tipe = 'laden_exp';
                if ($c['cargo_type'] === 'reefer' || $blk === 'REEFER') $tipe = 'reefer';
                elseif ($blk === 'B') $tipe = 'laden_imp';
                elseif ($blk === 'C') $tipe = 'domestic';
                elseif ($blk === 'D') $tipe = 'buffer_rail';
                elseif ($blk === 'E') $tipe = 'dg_hazard';

                $live_mapped[] = [
                    'id'     => $c['container_number'],
                    'iso'    => $c['iso_code'] ? ($c['iso_code'] . ' (' . ($c['size_type'] ?? '40ft') . ')') : '42G1 (40ft GP)',
                    'tipe'   => $tipe,
                    'blok'   => ($blk === 'REEFER' ? 'Reefer' : $blk),
                    'bay'    => max(1, (int)($c['bay'] ?? 1)),
                    'row'    => max(1, (int)($c['row'] ?? 1)),
                    'tier'   => max(1, (int)($c['tier'] ?? 1)),
                    'berat'  => number_format((float)($c['gross_weight_kg'] ?? 24000), 0, ',', '.') . ' kg',
                    'line'   => !empty($c['owner_company']) ? $c['owner_company'] : 'Shipping Carrier',
                    'pod'    => ($blk === 'B') ? 'Cikarang (IDCBI)' : 'Rotterdam (NLRTM)',
                    'dwell'  => '2 hari',
                    'pabean' => ($c['customs_status'] === 'SPPB_CLEARED' ? 'SPPB Terbit (Hijau)' : ($c['customs_status'] === 'RED_LANE' ? 'Jalur Merah (Hold)' : 'Pemeriksaan')),
                    'seal'   => !empty($c['seal_number']) ? $c['seal_number'] : 'SEAL-OK',
                    'rtk'    => 'RS-01 (1.2cm)'
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
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-2 mb-2.5">
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
        <div class="xl:col-span-3 bg-slate-900 rounded-2xl p-4 sm:p-5 shadow-lg border border-slate-800 space-y-4">
            
            <!-- Toolbar Pengendali Tampilan 3D & Filter Kategori -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center">
                        <i class="fa-solid fa-cube text-blue-400 mr-2"></i>
                        Digital Twin Container Stacking (3D WebGL)
                    </span>
                    <span class="text-[10px] text-emerald-400 bg-emerald-950/80 px-2 py-0.5 rounded border border-emerald-800/80 font-mono">
                        Three.js Engine r128
                    </span>
                </div>

                <!-- Kontrol Sudut Kamera 3D Presets -->
                <div class="flex items-center space-x-1.5 text-xs">
                    <span class="text-[11px] text-slate-400 mr-1"><i class="fa-solid fa-camera mr-1"></i>Preset:</span>
                    <button onclick="setCameraView('iso')" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-medium border border-slate-700 transition" title="Tampak Isometrik 3D">
                        <i class="fa-solid fa-cube mr-1"></i>Isometrik
                    </button>
                    <button onclick="setCameraView('top')" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-medium border border-slate-700 transition" title="Tampak Atas (Bird's Eye / Plan View)">
                        <i class="fa-solid fa-arrows-up-to-line mr-1"></i>Tampak Atas
                    </button>
                    <button onclick="setCameraView('side')" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-medium border border-slate-700 transition" title="Tampak Samping (Profil Bay)">
                        <i class="fa-solid fa-arrows-left-right mr-1"></i>Profil Bay
                    </button>
                    <button onclick="resetYardCamera()" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] font-mono border border-slate-700 transition" title="Reset Kamera">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                </div>
            </div>

            <!-- Filter Kategori Kontainer & Simulasi Reshuffle -->
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                <!-- Filter Kategori Warna -->
                <div class="flex flex-wrap items-center gap-1.5" id="containerFilterButtons">
                    <span class="text-[11px] text-slate-400 mr-1 font-semibold">Tampilkan:</span>
                    <button onclick="filter3DContainers('all')" class="cat-filter-btn px-2.5 py-1 rounded-lg text-[11px] font-bold bg-[#0170b9] text-white transition-all shadow-xs" data-cat="all">Semua Blok</button>
                    <button onclick="filter3DContainers('laden_exp')" class="cat-filter-btn px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-800 text-blue-300 hover:bg-slate-700 border border-blue-900 transition-all" data-cat="laden_exp">🔵 Blok A (Laden Ekspor)</button>
                    <button onclick="filter3DContainers('laden_imp')" class="cat-filter-btn px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-800 text-sky-300 hover:bg-slate-700 border border-sky-900 transition-all" data-cat="laden_imp">🔷 Blok B (Laden Impor)</button>
                    <button onclick="filter3DContainers('domestic')" class="cat-filter-btn px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-800 text-emerald-300 hover:bg-slate-700 border border-emerald-900 transition-all" data-cat="domestic">🟢 Blok C (Domestik)</button>
                    <button onclick="filter3DContainers('buffer_rail')" class="cat-filter-btn px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-800 text-purple-300 hover:bg-slate-700 border border-purple-900 transition-all" data-cat="buffer_rail">🟣 Blok D (Siding KA)</button>
                    <button onclick="filter3DContainers('dg_hazard')" class="cat-filter-btn px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-800 text-rose-300 hover:bg-slate-700 border border-rose-900 transition-all" data-cat="dg_hazard">🔴 Blok E (DG Hazard)</button>
                    <button onclick="filter3DContainers('reefer')" class="cat-filter-btn px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-800 text-cyan-300 hover:bg-slate-700 border border-cyan-900 transition-all" data-cat="reefer">❄️ Reefer Cold Chain</button>
                </div>

                <!-- Tombol Simulasi Reshuffle (CRP) -->
                <button onclick="simulateContainerRelocation()" id="btnSimulateCRP" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-500 hover:bg-amber-600 text-slate-950 transition-all shadow-md flex items-center space-x-1.5">
                    <i class="fa-solid fa-arrows-split-up-and-left animate-spin" id="crpSpinIcon" style="display:none;"></i>
                    <i class="fa-solid fa-dolly" id="crpStaticIcon"></i>
                    <span>Simulasi Manuver Reshuffle (CRP)</span>
                </button>
            </div>

            <!-- Viewport 3D Canvas Three.js -->
            <div class="relative w-full h-[460px] rounded-xl overflow-hidden bg-slate-950 border border-slate-800" id="threeCanvasContainer">
                <canvas id="yard3DCanvas" class="w-full h-full block cursor-grab active:cursor-grabbing"></canvas>

                <!-- Floating 3D Navigation Guide -->
                <div class="absolute bottom-3 left-3 bg-slate-900/85 backdrop-blur-md px-3 py-1.5 rounded-lg border border-slate-700/80 text-[10px] text-slate-300 flex items-center space-x-3 pointer-events-none">
                    <span><i class="fa-solid fa-computer-mouse text-blue-400 mr-1"></i>Klik Kiri + Geser: Putar 360°</span>
                    <span><i class="fa-solid fa-arrows-up-down text-blue-400 mr-1"></i>Scroll: Zoom</span>
                    <span><i class="fa-solid fa-hand-pointer text-amber-400 mr-1"></i>Klik Kontainer: Detail Manifest</span>
                </div>

                <!-- Loading Overlay -->
                <div id="threeLoadingOverlay" class="absolute inset-0 bg-slate-950/90 flex flex-col items-center justify-center space-y-3 z-10 transition-opacity duration-300">
                    <div class="w-10 h-10 border-3 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                    <span class="text-xs text-slate-300 font-mono">Memuat Mesh Digital Twin 3D...</span>
                </div>
            </div>

            <!-- Petunjuk Arsitektur Bay-Row-Tier -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs bg-slate-950/60 p-3 rounded-xl border border-slate-800/80">
                <div class="flex items-center space-x-2">
                    <span class="w-7 h-7 rounded-lg bg-blue-950/90 border border-blue-800 text-blue-400 flex items-center justify-center font-bold font-mono text-xs">BAY</span>
                    <div>
                        <span class="font-bold text-slate-200 text-xs block">Sumbu Memanjang (Bay 01 - 16)</span>
                        <span class="text-[10px] text-slate-400">Ganjil = 20ft | Genap = 40ft High Cube</span>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <span class="w-7 h-7 rounded-lg bg-emerald-950/90 border border-emerald-800 text-emerald-400 flex items-center justify-center font-bold font-mono text-xs">ROW</span>
                    <div>
                        <span class="font-bold text-slate-200 text-xs block">Sumbu Melebar (Row 01 - 06)</span>
                        <span class="text-[10px] text-slate-400">Jalur lajur sisi laut / rel hingga sisi darat</span>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <span class="w-7 h-7 rounded-lg bg-purple-950/90 border border-purple-800 text-purple-400 flex items-center justify-center font-bold font-mono text-xs">TIER</span>
                    <div>
                        <span class="font-bold text-slate-200 text-xs block">Ketinggian Tumpuk (Tier 1 - 4)</span>
                        <span class="text-[10px] text-slate-400">Tier 1 Terbawah (Heavy) s/d Tier 4 Teratas</span>
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
            <div class="bg-slate-900 rounded-xl p-3.5 text-white space-y-1 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest">ISO 6346 CONTAINER ID</span>
                    <span id="yardBoxLine" class="text-[10px] font-bold text-blue-400">Maersk Line</span>
                </div>
                <h3 id="yardBoxNumber" class="text-base font-mono font-bold tracking-wider text-amber-400">MSKU 982145-8</h3>
                <div class="flex items-center justify-between text-[11px] text-slate-300 pt-1 border-t border-slate-800 mt-2">
                    <span id="yardBoxType">45G1 (40ft High Cube)</span>
                    <span id="yardBoxWeight" class="font-mono text-emerald-400 font-bold">VGM: 28.450 kg</span>
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
                        <td class="py-3 px-3.5 font-mono font-bold text-[#002f5e]"><?= $jo['id'] ?></td>
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
    <div id="bayMatrixModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 animate-fadeIn">
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
            <div class="space-y-3">
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
                    <div class="h-16 rounded-lg bg-[#002f5e] text-white p-2 flex flex-col justify-between shadow-xs border-b-2 border-amber-400">
                        <span class="font-mono font-bold text-[11px] block truncate text-amber-300">MSKU 982145-8</span>
                        <span class="text-[9px] text-blue-200 font-mono">28.5t • Heavy Tier 1</span>
                    </div>
                    <div class="h-16 rounded-lg bg-[#002f5e] text-white p-2 flex flex-col justify-between shadow-xs border-b-2 border-amber-400">
                        <span class="font-mono font-bold text-[11px] block truncate text-amber-300">CMAU 771239-0</span>
                        <span class="text-[9px] text-blue-200 font-mono">26.8t • Heavy Tier 1</span>
                    </div>
                    <div class="h-16 rounded-lg bg-[#002f5e] text-white p-2 flex flex-col justify-between shadow-xs border-b-2 border-amber-400">
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

<!-- DATASET JAVASCRIPT & THREE.JS 3D ENGINE -->
<!-- CDN Three.js r128 & OrbitControls -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>

<script>
// Data Kontainer Javascript
const containersData = <?= json_encode($yard_containers, JSON_PRETTY_PRINT) ?>;

// Skema Warna Standar ISO Kontainer
const COLOR_MAP = {
    'laden_exp': 0x1e3a8a,   // Deep Navy Blue
    'laden_imp': 0x0284c7,   // Sky Blue
    'domestic': 0x059669,    // Emerald Green
    'buffer_rail': 0x7c3aed, // Purple
    'dg_hazard': 0xdc2626,   // Hazard Red
    'reefer': 0x06b6d4       // Cyan Cold Chain
};

let scene, camera, renderer, controls;
let containerMeshes = [];
let raycaster, mouse;
let activeFilter = 'all';
let isAnimatingCRP = false;
let targetRelocationBox = null;

// Inisialisasi Tampilan 3D Three.js
function initThreeYard() {
    const container = document.getElementById('threeCanvasContainer');
    const canvas = document.getElementById('yard3DCanvas');

    const width = container.clientWidth;
    const height = container.clientHeight;

    // 1. Scene
    scene = new THREE.Scene();
    scene.background = new THREE.Color(0x0a0f1d);
    scene.fog = new THREE.FogExp2(0x0a0f1d, 0.008);

    // 2. Camera
    camera = new THREE.PerspectiveCamera(45, width / height, 1, 1000);
    camera.position.set(45, 35, 55);

    // 3. Renderer
    renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true });
    renderer.setSize(width, height);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;

    // 4. OrbitControls
    controls = new THREE.OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.05;
    controls.maxPolarAngle = Math.PI / 2 - 0.05; // Mencegah kamera masuk ke bawah tanah
    controls.minDistance = 15;
    controls.maxDistance = 140;
    controls.target.set(10, 5, 0);

    // 5. Pencahayaan (Lighting Realistis)
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
    scene.add(ambientLight);

    const sunLight = new THREE.DirectionalLight(0xffffff, 0.9);
    sunLight.position.set(50, 70, 40);
    sunLight.castShadow = true;
    sunLight.shadow.mapSize.width = 2048;
    sunLight.shadow.mapSize.height = 2048;
    sunLight.shadow.camera.near = 0.5;
    sunLight.shadow.camera.far = 200;
    const d = 50;
    sunLight.shadow.camera.left = -d;
    sunLight.shadow.camera.right = d;
    sunLight.shadow.camera.top = d;
    sunLight.shadow.camera.bottom = -d;
    scene.add(sunLight);

    // Lampu Sorot Fill Light
    const fillLight = new THREE.DirectionalLight(0x38bdf8, 0.3);
    fillLight.position.set(-40, 30, -30);
    scene.add(fillLight);

    // 6. Grid Lantai Aspal/Beton Yard
    const gridHelper = new THREE.GridHelper(120, 30, 0x0284c7, 0x1e293b);
    gridHelper.position.y = 0;
    scene.add(gridHelper);

    // Lantai Solid
    const groundGeo = new THREE.PlaneGeometry(160, 160);
    const groundMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.9, metalness: 0.1 });
    const ground = new THREE.Mesh(groundGeo, groundMat);
    ground.rotation.x = -Math.PI / 2;
    ground.receiveShadow = true;
    scene.add(ground);

    // 7. Raycaster untuk Interaksi Klik Kontainer
    raycaster = new THREE.Raycaster();
    mouse = new THREE.Vector2();

    // 8. Buat Kubus Kontainer Lapangan
    build3DContainers();

    // Sembunyikan Loading Overlay
    document.getElementById('threeLoadingOverlay').style.opacity = '0';
    setTimeout(() => {
        document.getElementById('threeLoadingOverlay').style.display = 'none';
    }, 300);

    // Event Listeners
    window.addEventListener('resize', onWindowResize);
    canvas.addEventListener('click', onCanvasClick);

    // Animasi Loop
    animate();
}

// Render Kubus Kontainer Berdasarkan Posisi Lapangan
function build3DContainers() {
    // Dimensi Standar Proporsional Kontainer 40ft (P: 12m, L: 2.4m, T: 2.6m) -> Skala (10, 2.4, 2.4)
    const boxGeo40 = new THREE.BoxGeometry(10, 2.4, 2.5);
    const boxGeo20 = new THREE.BoxGeometry(5, 2.4, 2.5);

    containersData.forEach((cData, index) => {
        const is20ft = cData.iso.includes('20ft');
        const geo = is20ft ? boxGeo20 : boxGeo40;
        const color = COLOR_MAP[cData.tipe] || 0x1e3a8a;

        const mat = new THREE.MeshStandardMaterial({
            color: color,
            roughness: 0.5,
            metalness: 0.25
        });

        const mesh = new THREE.Mesh(geo, mat);
        mesh.castShadow = true;
        mesh.receiveShadow = true;

        // Hitung Posisi 3D (X, Y, Z) dari Bay, Row, Tier
        // Bay: Geser pada sumbu X
        // Row: Geser pada sumbu Z
        // Tier: Naik pada sumbu Y
        const posX = (cData.bay - 6) * 12;
        const posY = (cData.tier - 1) * 2.5 + 1.25;
        const posZ = (cData.row - 2) * 3.2;

        mesh.position.set(posX, posY, posZ);
        mesh.userData = cData;
        mesh.userData.defaultColor = color;
        mesh.userData.defaultPosY = posY;

        // Beri garis tepi / wireframe container edge untuk efek corrugated kontainer
        const edges = new THREE.EdgesGeometry(geo);
        const lineMat = new THREE.LineBasicMaterial({ color: 0xffffff, opacity: 0.25, transparent: true });
        const wireframe = new THREE.LineSegments(edges, lineMat);
        mesh.add(wireframe);

        scene.add(mesh);
        containerMeshes.push(mesh);
    });
}

// Handler Klik Mouse pada Kontainer 3D (Raycasting)
function onCanvasClick(event) {
    const canvas = document.getElementById('yard3DCanvas');
    const rect = canvas.getBoundingClientRect();
    mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
    mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

    raycaster.setFromCamera(mouse, camera);
    const intersects = raycaster.intersectObjects(containerMeshes);

    if (intersects.length > 0) {
        const clickedMesh = intersects[0].object;
        selectContainerMesh(clickedMesh);
    }
}

// Highlight Kontainer yang Dipilih & Tampilkan di Inspector
function selectContainerMesh(mesh) {
    // Reset warna kontainer lainnya
    containerMeshes.forEach(m => {
        m.material.color.setHex(m.userData.defaultColor);
        m.material.emissive.setHex(0x000000);
    });

    // Highlight kontainer terpilih dengan warna emas berkilau
    mesh.material.emissive.setHex(0x332200);

    const d = mesh.userData;
    updateInspectorWithContainerData(d);
}

// Perbarui Panel Inspector Samping
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

    // Badge Tipe
    const badge = document.getElementById('yardBoxStatusBadge');
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

// Preset Kamera
function setCameraView(preset) {
    if (!controls) return;
    if (preset === 'iso') {
        camera.position.set(45, 35, 55);
        controls.target.set(10, 5, 0);
    } else if (preset === 'top') {
        camera.position.set(10, 85, 0.1);
        controls.target.set(10, 0, 0);
    } else if (preset === 'side') {
        camera.position.set(-65, 15, 0);
        controls.target.set(0, 5, 0);
    }
    controls.update();
}

function resetYardCamera() {
    setCameraView('iso');
}

// Filter Kontainer Berdasarkan Kategori
function filter3DContainers(category) {
    activeFilter = category;

    // Perbarui Tombol Aktif
    document.querySelectorAll('.cat-filter-btn').forEach(btn => {
        if (btn.getAttribute('data-cat') === category) {
            btn.classList.add('bg-[#0170b9]', 'text-white', 'shadow-xs');
            btn.classList.remove('bg-slate-800');
        } else {
            btn.classList.remove('bg-[#0170b9]', 'text-white', 'shadow-xs');
            btn.classList.add('bg-slate-800');
        }
    });

    containerMeshes.forEach(mesh => {
        if (category === 'all' || mesh.userData.tipe === category) {
            mesh.visible = true;
        } else {
            mesh.visible = false;
        }
    });
}

// Simulasi Manuver Reshuffle Kontainer (Container Relocation Problem - CRP)
function simulateContainerRelocation() {
    if (isAnimatingCRP) return;
    isAnimatingCRP = true;

    const btn = document.getElementById('btnSimulateCRP');
    const spinIcon = document.getElementById('crpSpinIcon');
    const staticIcon = document.getElementById('crpStaticIcon');
    spinIcon.style.display = 'inline-block';
    staticIcon.style.display = 'none';
    btn.disabled = true;

    // Target: Cari kontainer Tier 2 yang menutupi Tier 1 di Blok B (ONEU7719023)
    const topMesh = containerMeshes.find(m => m.userData.id === 'ONEU7719023');
    if (!topMesh) {
        isAnimatingCRP = false;
        return;
    }

    selectContainerMesh(topMesh);

    // Animasi Angkat (Lift-Off) -> Geser (Traverse) -> Taruh ke Row 2 (Set-Down)
    const initialY = topMesh.userData.defaultPosY;
    const liftedY = initialY + 6.0;
    const initialZ = topMesh.position.z;
    const targetZ = initialZ + 3.2;

    let step = 0;
    const animInterval = setInterval(() => {
        step++;
        if (step <= 25) {
            // Fase 1: Spreader RTK mengangkat kontainer
            topMesh.position.y += (liftedY - initialY) / 25;
        } else if (step <= 50) {
            // Fase 2: Reach Stacker bermanuver memindahkan ke slot kosong terdekat
            topMesh.position.z += (targetZ - initialZ) / 25;
        } else if (step <= 75) {
            // Fase 3: Menurunkan kontainer ke posisi sementara (Reshuffle minim)
            topMesh.position.y -= (liftedY - initialY) / 25;
        } else {
            clearInterval(animInterval);
            isAnimatingCRP = false;
            spinIcon.style.display = 'none';
            staticIcon.style.display = 'inline-block';
            btn.disabled = false;

            // Beri notifikasi hasil optimasi CRP
            alert("✅ Simulasi Reshuffle Sukses!\nKontainer ONEU7719023 berhasil dipindahkan ke Row 02 (Temp Slot) dengan akurasi RTK GNSS 0.9 cm.\nKontainer target ONEU6612984 di Tier 1 kini bebas diambil tanpa hambatan (0 reshuffle penalty).");
        }
    }, 40);
}

// Buka Modal Penampang 2D
function showBayMatrixView() {
    document.getElementById('bayMatrixModal').classList.remove('hidden');
}

function closeBayMatrixModal() {
    document.getElementById('bayMatrixModal').classList.add('hidden');
}

function dispatchWorkOrderFromInspector() {
    alert("✅ Perintah Kerja Berhasil Diterbitkan!\nInstruksi kerja dikirimkan secara nirkabel (WiFi 7) ke Vehicle Mounted Terminal (VMT) Reach Stacker terdekat dengan koordinat DGPS RTK.");
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

function animate() {
    requestAnimationFrame(animate);
    if (controls) controls.update();
    if (renderer && scene && camera) renderer.render(scene, camera);
}

// Jalankan Three.js & Inisialisasi Deep-Linking setelah DOM siap
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

// Universal Deep-Linking Handler for Yard Modul
function initYardDeepLinking() {
    const urlParams = new URLSearchParams(window.location.search);
    const focusBox = urlParams.get('focus_box') || urlParams.get('ctr') || urlParams.get('search');
    const blockParam = urlParams.get('block');
    const actionParam = urlParams.get('action');

    // 1. Focus on specific container
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

    // 2. Action modal opener
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
</script>

<!-- MODAL RELOKASI KONTAINER LAPANGAN (VMT JOB ORDER) -->
<div id="modalRelocateYard" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden animate-fadeIn">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-gray-100 transform transition-all text-xs">
        <div class="bg-gradient-to-r from-[#002f5e] via-[#004b87] to-[#0170b9] p-5 text-white flex items-center justify-between">
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
                <div class="text-[11px] font-bold text-[#002f5e] uppercase tracking-wider mb-2 flex items-center gap-1.5">
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
                <button type="submit" class="px-5 py-2 bg-[#004b87] hover:bg-[#002f5e] text-white font-bold rounded-xl shadow-md transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-check"></i>
                    <span>Eksekusi Relokasi</span>
                </button>
            </div>
        </form>
    </div>
</div>
