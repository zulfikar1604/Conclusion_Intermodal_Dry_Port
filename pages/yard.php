<?php
// =============================================================================
// MODUL: MANAJEMEN STACKING CONTAINER YARD REAL-TIME (YMS)
// File: pages/yard.php
// Proyek: Conclusion Intermodal Dry Port (CIDP) 35 Ha — Yard Management System
// PIC: Juan Gamaliel (Yard Planner & Data Integration) & Armansyah Muchtarrom (IoT & RTK GNSS)
// =============================================================================

$yard_info = [
    'pic'    => 'Juan Gamaliel & Armansyah Muchtarrom',
    'role'   => 'Yard Planning Specialist & IoT Infrastructure Specialist',
    'desc'   => 'Matriks Okupansi Yard Real-Time (Blok–Bay–Row–Tier), mitigasi Container Relocation Problem (CRP), dan telemetri RTK GNSS alat berat.',
    'icon'   => 'fa-boxes-stacked',
    'status' => 'Matriks YMS Real-Time & RTK Terintegrasi'
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
        'label' => 'Armada Alat & MHE Aktif (RTK)',
        'val' => '15',
        'unit' => 'Unit MHE',
        'sub' => 'RS, E-RTG, Forklift & Shuttle',
        'icon' => 'fa-truck-monster',
        'color' => 'cyan'
    ]
];

require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../api/preadvice_helper.php';
require_once __DIR__ . '/../api/twin_state_helper.php';
if (isset($pdo) && $pdo instanceof PDO) { ensure_integration_schema($pdo); }

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
        $operator  = trim($_POST['operator_name'] ?? 'Juan Gamaliel');

        if (!empty($ctr_num) && isset($pdo)) {
            try {
                // Validasi slot tujuan & anti-bentrok SSoT
                $val = yard_validate_slot($pdo, strtoupper($to_block), $to_bay, $to_row, $to_tier, $ctr_num);
                if (!$val['ok']) {
                    throw new Exception($val['message']);
                }

                // Ambil posisi lama
                $stmtOld = $pdo->prepare("SELECT block, bay, `row`, tier FROM containers WHERE container_number = ? LIMIT 1");
                $stmtOld->execute([$ctr_num]);
                $oldPos = $stmtOld->fetch(PDO::FETCH_ASSOC);
                $from_block = $oldPos['block'] ?? 'A';
                $from_bay   = yard_format_coord($oldPos['bay'] ?? 1);
                $from_row   = yard_format_coord($oldPos['row'] ?? 1);
                $from_tier  = yard_format_coord($oldPos['tier'] ?? 1);

                // Update posisi baru di containers (canonical zero-padded coordinates)
                $stmtUp = $pdo->prepare("UPDATE containers SET block = ?, bay = ?, `row` = ?, tier = ?, status = 'in_yard' WHERE container_number = ?");
                $stmtUp->execute([$val['block'], $val['bay'], $val['row'], $val['tier'], $ctr_num]);

                // Catat ke yard_events
                $stmtEv = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, from_block, from_bay, from_row, from_tier, to_block, to_bay, to_row, to_tier, operator_name, notes, created_at) VALUES ('RELOCATION', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmtEv->execute([$ctr_num, $equip_id, $from_block, $from_bay, $from_row, $from_tier, $val['block'], $val['bay'], $val['row'], $val['tier'], $operator, "Relokasi VMT oleh $equip_id ke {$val['code']}"]);

                // Update equipment status & billing
                $stmtEq = $pdo->prepare("UPDATE equipment SET current_container = ?, last_block = ?, status = 'operating', last_updated = NOW() WHERE equipment_id = ?");
                $stmtEq->execute([$ctr_num, $val['block'], $equip_id]);
                accrue_billing($pdo, $ctr_num, "Relokasi Yard ($from_block -> {$val['block']})", 'lolo_amount', 200000);

                get_live_twin_state($pdo, true);

                $warnNote = !empty($val['gravity_warning']) ? (' <br><span class="text-amber-700 font-semibold">' . htmlspecialchars($val['gravity_warning']) . '</span>') : '';
                $yard_alert = [
                    'type' => 'success',
                    'title' => 'Relokasi Kontainer Berhasil (VMT RTK Synchronized)',
                    'msg' => "Peti kemas <strong>$ctr_num</strong> berhasil dipindahkan oleh <strong>$equip_id</strong> ke <strong>Blok {$val['block']} (Bay {$val['bay']} &bull; Row {$val['row']} &bull; Tier {$val['tier']})</strong>. Data tersinkronisasi di seluruh modul.{$warnNote}"
                ];
            } catch (Exception $e) {
                $yard_alert = ['type' => 'error', 'title' => 'Gagal Relokasi Yard', 'msg' => $e->getMessage()];
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
$yard_initial_twin = [];
if (isset($pdo)) {
    try {
        $stmtC = $pdo->query("SELECT * FROM containers WHERE status = 'in_yard' ORDER BY id ASC");
        $rawYard = $stmtC->fetchAll(PDO::FETCH_ASSOC);
        $db_all_containers = $rawYard;
        $validCyBlocks = array_keys(yard_block_catalog());

        if (true || !empty($rawYard)) {
            $yard_containers = [];
            $live_mapped = [];
            foreach ($rawYard as $c) {
                $blkRaw = !empty($c['block']) ? strtoupper(trim($c['block'])) : '';
                if ($blkRaw === 'RAIL') $blkRaw = 'D';
                // Kecualikan kontainer yang sedang di GATE/OUTSIDE/CFS agar tidak menumpuk palsu ke Blok A
                if ($blkRaw === '' || !in_array($blkRaw, $validCyBlocks, true)) {
                    continue;
                }

                $cargoType = strtolower(trim($c['cargo_type'] ?? 'dry'));
                $blk = $blkRaw;

                if ($blk === 'REEFER' || $cargoType === 'reefer') {
                    $tipe = 'reefer';
                } elseif ($blk === 'DG' || $blk === 'E' || $cargoType === 'dg') {
                    $tipe = 'dg_hazard';
                } elseif ($blk === 'B') {
                    $tipe = 'laden_imp';
                } elseif ($blk === 'C' || $blk === 'EMPTY') {
                    $tipe = 'domestic';
                } elseif ($blk === 'D') {
                    $tipe = 'buffer_rail';
                } else {
                    $tipe = 'laden_exp';
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
                    'pabean'           => ($c['customs_status'] === 'SPPB_CLEARED' ? 'SPPB Terbit (Hijau)' : (in_array($c['customs_status'], ['RED_LANE', 'RED_LANE_CHECK', 'DOC_HOLD', 'VGM_HOLD'], true) ? 'Hold / Jalur Merah' : 'Pemeriksaan Pabean')),
                    'customs_status'   => $c['customs_status'] ?? 'PENDING',
                    'seal'             => !empty($c['seal_number']) ? $c['seal_number'] : 'SEAL-OK',
                    'rtk'              => 'RS-01 (Akurasi 1.2cm)',
                    'is_live_db'       => true
                ];
            }
            if (true || !empty($live_mapped)) {
                $yard_containers = $live_mapped;
                $__teu = 0; foreach ($yard_containers as $__yc) { $__teu += (strpos($__yc['iso'] ?? '', '22') === 0 ? 1 : 2); }
                $yard_kpis[1]['val'] = round(($__teu / 15000) * 100, 2) . '%';
                $yard_kpis[1]['unit'] = '(' . $__teu . ' TEUs)';

                // SSoT KPI dinamis: CRP Reshuffle Ratio, Rata-rata Dwell Time & Armada MHE dari MySQL
                try {
                    $evTot = max(1, (int)$pdo->query("SELECT COUNT(*) FROM yard_events")->fetchColumn());
                    $evReloc = (int)$pdo->query("SELECT COUNT(*) FROM yard_events WHERE event_type IN ('RELOCATION','CRP_RESHUFFLE')")->fetchColumn();
                    $crpPct = round(($evReloc / $evTot) * 100, 1);
                    $yard_kpis[2]['val'] = number_format($crpPct, 1, ',', '.') . '%';

                    $avgH = $pdo->query("SELECT AVG(TIMESTAMPDIFF(HOUR, gate_in_time, NOW())) FROM containers WHERE status IN ('in_yard','in_transit') AND gate_in_time IS NOT NULL")->fetchColumn();
                    $avgD = ($avgH !== false && $avgH !== null) ? max(0.2, round(((float)$avgH) / 24.0, 1)) : 0.0;
                    $yard_kpis[3]['val'] = count($yard_containers) > 0 ? number_format($avgD, 1, ',', '.') : '0,0';

                    $eqAct = (int)$pdo->query("SELECT COUNT(*) FROM equipment WHERE status <> 'maintenance'")->fetchColumn();
                    $yard_kpis[4]['val'] = (string)$eqAct;
                } catch (Throwable $e) {}
            }
        }
        $yard_initial_twin = get_live_twin_state($pdo, true);
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

<div class="space-y-2.5 pb-12">
    <!-- Header Modul: Compact Executive Style (Aligned with Sidebar) -->
    <div class="bg-white rounded-xl px-3 py-2 shadow-2xs border border-slate-200/80 mb-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div class="flex items-center space-x-2.5">
            <div class="w-7 h-7 rounded-lg bg-orange-50 border border-orange-200/70 text-orange-600 flex items-center justify-center text-xs shadow-2xs flex-shrink-0">
                <i class="fa-solid <?= $yard_info['icon'] ?>"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-1.5">
                    <h1 class="text-[13px] font-extrabold tracking-tight text-slate-900">Manajemen Stacking Yard &amp; Matriks Okupansi (Blok–Bay–Row–Tier)</h1>
                    <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-[9px] font-bold flex items-center">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span><?= $yard_info['status'] ?>
                    </span>
                    <span id="yardHeaderBoxCount" class="px-1.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[9px] font-bold">
                        MySQL Sync (<?= count($yard_containers) ?> Box)
                    </span>
                    <span class="px-1.5 py-0.5 bg-orange-50 text-orange-700 border border-orange-200 rounded text-[9px] font-bold">
                        RTK GNSS &lt;1.4cm
                    </span>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-1.5 self-start sm:self-auto flex-shrink-0">
            <a href="dashboard.php?page=twin&view=yard" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 text-[11px] font-bold rounded-lg shadow-2xs transition flex items-center space-x-1.5">
                <i class="fa-solid fa-cubes text-[#0170b9] text-[10px]"></i>
                <span>Digital Twin 3D</span>
            </a>
            <button type="button" onclick="openYardPutawayModal()" class="px-2.5 py-1 bg-[#0170b9] hover:bg-blue-700 text-white text-[11px] font-bold rounded-lg shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-solid fa-layer-group text-[10px]"></i>
                <span>+ Stack Antrean</span>
            </button>
            <button onclick="openModalYardRelocate()" class="px-2.5 py-1 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white text-[11px] font-bold rounded-lg shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-solid fa-arrows-up-down-left-right text-[10px]"></i>
                <span>+ Relokasi (VMT)</span>
            </button>
        </div>
    </div>

    <!-- KPI Micro-Cards (High-Density Executive Grid) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-2 mb-2.5">
        <?php foreach ($yard_kpis as $kIdx => $kpi): ?>
            <div class="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-2xs flex items-center justify-between transition-all hover:border-orange-200">
                <div class="min-w-0 pr-2">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider truncate mb-0.5"><?= $kpi['label'] ?></p>
                    <div class="flex items-baseline space-x-1">
                        <h3 id="yardKpiVal-<?= (int)$kIdx ?>" class="text-[13.5px] font-extrabold text-slate-900 tracking-tight leading-tight truncate"><?= $kpi['val'] ?></h3>
                        <span id="yardKpiUnit-<?= (int)$kIdx ?>" class="text-[9px] text-<?= $kpi['color'] ?>-600 font-bold"><?= $kpi['unit'] ?></span>
                    </div>
                    <p class="text-[9px] text-slate-500 font-semibold mt-0.5 truncate"><?= $kpi['sub'] ?></p>
                </div>
                <div class="w-7 h-7 rounded-lg bg-slate-50 text-<?= $kpi['color'] ?>-600 flex items-center justify-center text-[11px] flex-shrink-0 shadow-2xs">
                    <i class="fa-solid <?= $kpi['icon'] ?>"></i>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ======================================================================= -->
    <!-- MATRIKS OKUPANSI & PENAMPANG LAPANGAN REAL-TIME (BLOK - BAY - ROW - TIER) -->
    <!-- ======================================================================= -->
    <div id="yardMatrixSection" class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs space-y-4">
        <!-- Header & Live SSoT Status -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <div class="flex items-center flex-wrap gap-2">
                    <span class="w-7 h-7 rounded-lg bg-blue-50 text-[#0170b9] border border-blue-200 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-table-cells"></i>
                    </span>
                    <h2 class="text-sm sm:text-base font-extrabold text-slate-900 tracking-tight">
                        Matriks Okupansi &amp; Penampang Yard Real-Time (Blok &ndash; Bay &ndash; Row &ndash; Tier)
                    </h2>
                    <span id="yardLiveStreamBadge" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                        <span id="yardLiveStreamText">Live SSE Sync (SSoT)</span>
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 mt-1">
                    Klik <strong class="text-slate-700">Bay (B01–B10)</strong> atau <strong class="text-slate-700">Slot Matrix</strong> untuk inspeksi detail, relokasi VMT, atau stacking dari antrean Gate/Rail.
                </p>
            </div>

            <!-- KPI & Staging Pills -->
            <div class="flex flex-wrap items-center gap-1.5 text-[10.5px]">
                <span class="px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 font-bold text-slate-700">
                    Gate Staging: <span id="yardGateStageCnt" class="font-mono text-[#0170b9]">0</span>
                </span>
                <span class="px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 font-bold text-slate-700">
                    Rampa CFS: <span id="yardCfsStageCnt" class="font-mono text-orange-600">0</span>
                </span>
                <span class="px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 font-bold text-slate-700">
                    On Rail KA: <span id="yardRailStageCnt" class="font-mono text-purple-700">0</span>
                </span>
                <span class="px-2.5 py-1 rounded-lg bg-red-50 border border-red-200 font-bold text-red-700">
                    Hold Pabean: <span id="yardHoldCnt" class="font-mono">0</span>
                </span>
            </div>
        </div>

        <!-- Summary Strip & Dynamic Progress Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-2.5 items-center bg-slate-50/80 p-3 rounded-xl border border-slate-200/80">
            <div class="bg-white rounded-lg p-2 border border-slate-200/70 text-center">
                <div class="text-[9.5px] font-bold uppercase text-slate-400">Blok CY</div>
                <div class="text-sm font-black font-mono text-slate-900" id="yardSumBlocks">8</div>
            </div>
            <div class="bg-white rounded-lg p-2 border border-slate-200/70 text-center">
                <div class="text-[9.5px] font-bold uppercase text-slate-400">Slot Terisi</div>
                <div class="text-sm font-black font-mono text-[#0170b9]" id="yardSumOccupied">0</div>
            </div>
            <div class="bg-white rounded-lg p-2 border border-slate-200/70 text-center">
                <div class="text-[9.5px] font-bold uppercase text-slate-400">Slot Kosong</div>
                <div class="text-sm font-black font-mono text-emerald-700" id="yardSumEmpty">928</div>
            </div>
            <div class="bg-white rounded-lg p-2 border border-slate-200/70 text-center">
                <div class="text-[9.5px] font-bold uppercase text-slate-400">Total TEUs CY</div>
                <div class="text-sm font-black font-mono text-indigo-700" id="yardSumTeus">0</div>
            </div>
            <div class="col-span-2 bg-white rounded-lg p-2.5 border border-slate-200/70">
                <div class="flex items-center justify-between text-[10.5px] font-bold text-slate-700 mb-1">
                    <span>Okupansi Matriks CY (Blok A–REEFER)</span>
                    <span id="yardOccPctLabel" class="font-mono text-[#0170b9]">0.0%</span>
                </div>
                <div class="w-full h-2.5 rounded-full bg-slate-200 overflow-hidden">
                    <div id="yardOccBarFill" class="h-full rounded-full bg-gradient-to-r from-[#0170b9] via-sky-500 to-emerald-500 transition-all duration-300" style="width: 0%;"></div>
                </div>
                <div id="yardOccText" class="text-[10px] text-slate-500 font-mono mt-1">Memuat sinkronisasi slot lapangan...</div>
            </div>
        </div>

        <!-- Live Update Toast -->
        <div id="yardMatrixToast" class="hidden p-3 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 text-xs flex items-center justify-between gap-2 animate-fadeIn">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-bolt text-orange-500"></i>
                <span id="yardMatrixToastMsg" class="font-semibold">Mutasi posisi kontainer CY terdeteksi real-time.</span>
            </div>
            <button type="button" onclick="document.getElementById('yardMatrixToast').classList.add('hidden')" class="text-emerald-700 hover:text-emerald-950 font-bold text-sm cursor-pointer">&times;</button>
        </div>

        <!-- LAYER 1: HEATMAP 8 BLOK YARD & MINI-BAY SELECTOR -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-600">
                    <i class="fa-solid fa-layer-group text-orange-500 mr-1"></i> 1. Peta Okupansi Blok &amp; Bay (Klik Bay untuk Membuka Penampang Row &times; Tier)
                </span>
                <span class="text-[10px] text-slate-400 font-mono">Biru/Berwarna = Terisi &bull; Merah = Hold Pabean &bull; Abu = Kosong</span>
            </div>
            <div id="yardBlocksOverviewGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5"></div>
        </div>

        <!-- LAYER 2: CROSS-SECTION PENAMPANG BAY (ROW x TIER INTERAKTIF) -->
        <div id="yardCrossSectionPanel" class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 space-y-3">
            <!-- Toolbar Block & Bay Switcher -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2.5 border-b border-slate-200/80 pb-3">
                <div class="flex items-center flex-wrap gap-1.5" id="yardMatrixBlockTabs"></div>
                <div class="flex items-center flex-wrap gap-1.5" id="yardMatrixBayPills"></div>
            </div>

            <!-- Cross-Section Title & Active Equipment -->
            <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                <div class="flex items-center gap-2">
                    <span id="yardActiveBayTitle" class="font-extrabold text-slate-900 text-sm">BLOK A &bull; BAY 01</span>
                    <span id="yardActiveBayZone" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-[#0170b9] border border-blue-200">Laden Ekspor</span>
                    <span id="yardActiveBayOcc" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white text-slate-700 border border-slate-200 font-mono">0/16 Slot</span>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="text-[11px] text-slate-500 font-mono" id="yardActiveBayEquip">
                        <i class="fa-solid fa-truck-monster text-orange-500 mr-1"></i>Alat Penumpuk: RS-01 / RTG-02
                    </div>
                    <button type="button" onclick="showBayMatrixView()" class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 rounded-lg text-[10.5px] font-bold transition flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-expand text-[#0170b9]"></i>
                        <span>Pop-out Bay 2D</span>
                    </button>
                </div>
            </div>

            <!-- Cross Section Grid -->
            <div class="overflow-x-auto pb-1">
                <div class="min-w-0 sm:min-w-[500px] space-y-2">
                    <div class="flex items-center justify-between text-[10px] font-semibold text-slate-400 uppercase px-1">
                        <span><i class="fa-solid fa-ship mr-1"></i>Sisi Laut / Rel Siding (Kiri)</span>
                        <span>Penampang Vertikal (Tier Atas &rarr; Tanah &times; Row)</span>
                        <span>Sisi Jalur Truk VMT (Kanan)<i class="fa-solid fa-truck ml-1"></i></span>
                    </div>

                    <div id="yardCrossSectionGrid" class="space-y-1.5"></div>

                    <div class="h-2.5 bg-slate-700 rounded-full w-full shadow-inner flex items-center justify-center"></div>
                    <div class="text-center text-[10px] text-slate-500 font-mono font-semibold">
                        LANTAI BETON RIGID PAVEMENT (PQC K-450 fc&apos; 40 MPa) &bull; KAPASITAS BEBAN 800 kN/m&sup2;
                    </div>
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
                    <h3 class="font-bold text-sm text-gray-900">Antrean Work Order Alat Berat (VMT)</h3>
                    <p class="text-xs text-gray-500">Zebra VC8300 &amp; sensor twistlock Bromma.</p>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block mr-1.5 animate-pulse"></span>6 VMT Online
                </span>
                <select id="yardVmtLimitSelect" data-limit-target="#yardVmtTable" class="px-2.5 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-700">
                    <option value="10" selected>Tampil: 10</option>
                    <option value="25">Tampil: 25</option>
                    <option value="50">Tampil: 50</option>
                    <option value="all">Semua</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs" id="yardVmtTable">
                <thead>
                    <tr class="bg-slate-50 text-gray-500 uppercase tracking-wider font-semibold border-b border-gray-200">
                        <th class="py-3 px-2.5 text-center w-10">No</th>
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
                    <?php foreach ($vmt_job_orders as $v_idx => $jo): ?>
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3 px-2.5 text-center font-mono text-gray-400" data-row-num><?= $v_idx + 1 ?></td>
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

    <!-- MODAL PENAMPANG 2D BAY MATRIX (REAL-TIME SSoT DARI MYSQL) -->
    <div id="bayMatrixModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 animate-fadeIn">
        <div class="bg-white rounded-2xl max-w-4xl w-full p-6 shadow-2xl border border-gray-100 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center space-x-2.5">
                    <span class="w-8 h-8 rounded-lg bg-blue-50 text-[#0170b9] flex items-center justify-center font-bold">
                        <i class="fa-solid fa-table-cells"></i>
                    </span>
                    <div>
                        <h3 id="bayMatrixModalTitle" class="font-bold text-base text-gray-900">Penampang 2D Matrix &mdash; Blok A (Bay 01)</h3>
                        <p id="bayMatrixModalSub" class="text-xs text-gray-500">Data Real-Time SSoT &bull; Klik slot untuk inspeksi atau putaway</p>
                    </div>
                </div>
                <button onclick="closeBayMatrixModal()" class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="overflow-x-auto pb-1">
                <div id="bayMatrixModalBody" class="min-w-[540px] space-y-2"></div>
            </div>

            <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                <span class="text-[11px] text-slate-500 font-mono">Tersinkronisasi langsung dengan tabel <code class="font-bold text-slate-700">containers</code> &amp; SSE SSoT.</span>
                <button type="button" onclick="closeBayMatrixModal()" class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition cursor-pointer">Tutup</button>
            </div>
        </div>
    </div>

    <!-- MODAL INSPEKTOR SLOT YARD REAL-TIME -->
    <div id="modalYardSlotInspector" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden animate-fadeIn">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-gray-100 transform transition-all text-xs">
            <div class="bg-gradient-to-r from-[#0170b9] to-slate-900 p-5 text-white flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center text-lg shadow-inner">
                        <i class="fa-solid fa-box"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 id="ysInspCtr" class="text-base font-extrabold font-mono leading-tight">-</h3>
                            <span id="ysInspBadge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white">-</span>
                        </div>
                        <p id="ysInspCoord" class="text-[11px] text-blue-100 font-mono mt-0.5">-</p>
                    </div>
                </div>
                <button type="button" onclick="closeModalById('modalYardSlotInspector')" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-5 space-y-3">
                <div class="grid grid-cols-2 gap-2.5">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="text-[10px] font-bold uppercase text-slate-400">Operator / Principal</div>
                        <div id="ysInspOwner" class="font-bold text-slate-900 text-xs mt-0.5 truncate">-</div>
                        <div id="ysInspSize" class="text-[10.5px] font-mono text-slate-500 mt-0.5">-</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="text-[10px] font-bold uppercase text-slate-400">Berat &amp; Status Pabean</div>
                        <div id="ysInspWeight" class="font-bold font-mono text-slate-900 text-xs mt-0.5">-</div>
                        <div id="ysInspCustoms" class="text-[10.5px] font-bold text-emerald-700 mt-0.5">-</div>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-blue-50/60 border border-blue-100 flex items-center justify-between text-[11px]">
                    <span class="text-slate-600 font-semibold"><i class="fa-solid fa-satellite-dish text-emerald-600 mr-1"></i>Billing Status:</span>
                    <span id="ysInspBilling" class="font-mono font-bold text-[#0170b9]">-</span>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <a id="ysInspTrackLink" href="dashboard.php?page=kontainer" class="px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold rounded-xl transition flex items-center gap-1.5">
                        <i class="fa-solid fa-route"></i>
                        <span>Lacak Siklus</span>
                    </a>
                    <a id="ysInspTwinLink" href="dashboard.php?page=twin&view=yard" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl transition flex items-center gap-1.5">
                        <i class="fa-solid fa-cubes text-[#0170b9]"></i>
                        <span>Lihat di Twin 3D</span>
                    </a>
                    <button type="button" id="ysInspRelocBtn" class="px-3.5 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-arrows-up-down-left-right"></i>
                        <span>Relokasi VMT</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL STACKING / PUTAWAY SLOT KOSONG YARD REAL-TIME -->
    <div id="modalYardSlotPutaway" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden animate-fadeIn">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-gray-100 transform transition-all text-xs">
            <div class="bg-gradient-to-r from-[#0170b9] to-indigo-700 p-5 text-white flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center text-lg shadow-inner">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold leading-tight">Stack / Tempatkan Peti Kemas ke Slot</h3>
                        <p class="text-[11px] text-blue-100">Putaway dari Antrean Gate/Rail atau Pindahkan Kontainer Yard</p>
                    </div>
                </div>
                <button type="button" onclick="closeModalById('modalYardSlotPutaway')" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form onsubmit="submitYardPutaway(event)" class="p-5 space-y-4">
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Pilih Kontainer (Antrean Gate / On-Rail / Yard) *</label>
                    <select id="putawayContainerSelect" required class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono text-xs font-bold text-gray-900"></select>
                    <span class="text-[10px] text-slate-500 mt-1 block">Kontainer <code>GATE / RAIL</code> memakai aksi <strong>Stacking Masuk CY</strong>; kontainer <code>YARD</code> memakai aksi <strong>Relokasi VMT</strong>.</span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Alat Eksekutor VMT</label>
                        <select id="putawayEquipSelect" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold">
                            <option value="RS-01">RS-01 (Kalmar Reach Stacker)</option>
                            <option value="RS-02">RS-02 (Kalmar Reach Stacker)</option>
                            <option value="RS-03">RS-03 (Sany Reefer / Buffer)</option>
                            <option value="RS-04">RS-04 (Kalmar Domestik)</option>
                            <option value="RTG-02">RTG-02 (E-RTG Blok A)</option>
                            <option value="RTG-03">RTG-03 (E-RTG Blok B)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Operator VMT</label>
                        <input type="text" id="putawayOperatorInput" value="Juan Gamaliel" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs">
                    </div>
                </div>

                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200 space-y-2.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="text-[11px] font-bold text-[#0170b9] uppercase">Target Slot Penumpukan</span>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="applyAutoSlotRecommendation()" class="px-2 py-0.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-[10px] font-bold transition cursor-pointer flex items-center gap-1" title="Pilih slot otomatis berdasarkan pabean, jenis kargo &amp; bobot untuk menekan reshuffle (CRP)">
                                <i class="fa-solid fa-wand-magic-sparkles text-[9px]"></i>
                                <span>Auto-Slot Optimal</span>
                            </button>
                            <span id="putawaySlotCodePreview" class="font-mono font-black text-slate-900 text-xs">A-B01-R01-T01</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-4 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Blok</label>
                            <select id="putawayBlockSelect" onchange="updatePutawaySlotPreview()" class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold">
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                                <option value="E">E</option>
                                <option value="DG">DG</option>
                                <option value="EMPTY">EMPTY</option>
                                <option value="REEFER">REEFER</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Bay</label>
                            <input type="number" id="putawayBayInput" onchange="updatePutawaySlotPreview()" oninput="updatePutawaySlotPreview()" min="1" max="12" value="1" class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-mono font-bold text-center">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Row</label>
                            <input type="number" id="putawayRowInput" onchange="updatePutawaySlotPreview()" oninput="updatePutawaySlotPreview()" min="1" max="6" value="1" class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-mono font-bold text-center">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Tier</label>
                            <input type="number" id="putawayTierInput" onchange="updatePutawaySlotPreview()" oninput="updatePutawaySlotPreview()" min="1" max="5" value="1" class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-mono font-bold text-center">
                        </div>
                    </div>
                    <div id="putawaySlotStatusPill" class="px-2.5 py-1.5 rounded-xl text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">SLOT KOSONG — SIAP</div>
                    <div id="putawayAutoSlotReason" class="hidden px-2.5 py-1.5 rounded-xl text-[11px] font-semibold bg-indigo-50 text-indigo-800 border border-indigo-200"></div>
                    <div id="putawayGravityHint" class="hidden px-2.5 py-1.5 rounded-xl text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200"></div>
                </div>

                <div id="putawayErrorBox" class="hidden p-2.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-[11px] font-semibold"></div>

                <div class="pt-2 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeModalById('modalYardSlotPutaway')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition cursor-pointer">Batal</button>
                    <button type="submit" id="btnSubmitYardPutaway" class="px-4 py-2 bg-[#0170b9] hover:bg-blue-700 text-white font-bold rounded-xl shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-check"></i>
                        <span>Eksekusi Penumpukan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DATASET JAVASCRIPT & REAL-TIME SSoT ENGINE -->
<script>
let currentYardTwinState = <?= json_encode($yard_initial_twin, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

function number_format_js(num) {
    return Number(num).toLocaleString('id-ID');
}

// =========================================================================
// REAL-TIME YARD OCCUPANCY MATRIX (BLOK - BAY - ROW - TIER SSoT ENGINE)
// =========================================================================
let selectedMatrixBlock = 'A';
let selectedMatrixBay = '01';
let prevYardSlotMap = null;
let yardToastTimer = null;
let yardEventSource = null;
let yardFallbackTimer = null;

function closeModalById(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('hidden');
}

function escapeYardHtml(val) {
    return String(val ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function pad2(v) {
    return String(Math.max(1, parseInt(v, 10) || 1)).padStart(2, '0');
}

function normObj(v) {
    return (v && typeof v === 'object' && !Array.isArray(v)) ? v : {};
}

function getYardBlockTheme(color) {
    const map = {
        blue:    { chip: 'bg-blue-600 text-white',    soft: 'bg-blue-50 text-blue-700 border-blue-200',       cell: 'bg-[#0170b9] text-white border-b-2 border-orange-400', mini: 'bg-blue-600 text-white' },
        sky:     { chip: 'bg-sky-600 text-white',     soft: 'bg-sky-50 text-sky-700 border-sky-200',         cell: 'bg-sky-700 text-white border-b-2 border-sky-300',      mini: 'bg-sky-600 text-white' },
        emerald: { chip: 'bg-emerald-600 text-white', soft: 'bg-emerald-50 text-emerald-700 border-emerald-200', cell: 'bg-emerald-700 text-white border-b-2 border-emerald-300', mini: 'bg-emerald-600 text-white' },
        purple:  { chip: 'bg-purple-600 text-white',  soft: 'bg-purple-50 text-purple-700 border-purple-200', cell: 'bg-purple-700 text-white border-b-2 border-purple-300', mini: 'bg-purple-600 text-white' },
        rose:    { chip: 'bg-rose-600 text-white',    soft: 'bg-rose-50 text-rose-700 border-rose-200',       cell: 'bg-rose-700 text-white border-b-2 border-rose-300',     mini: 'bg-rose-600 text-white' },
        red:     { chip: 'bg-red-600 text-white',     soft: 'bg-red-50 text-red-700 border-red-200',         cell: 'bg-red-700 text-white border-b-2 border-amber-300',     mini: 'bg-red-600 text-white' },
        slate:   { chip: 'bg-slate-600 text-white',   soft: 'bg-slate-100 text-slate-700 border-slate-300',   cell: 'bg-slate-700 text-white border-b-2 border-slate-400',   mini: 'bg-slate-600 text-white' },
        cyan:    { chip: 'bg-cyan-600 text-white',    soft: 'bg-cyan-50 text-cyan-700 border-cyan-200',       cell: 'bg-cyan-700 text-white border-b-2 border-cyan-300',     mini: 'bg-cyan-600 text-white' }
    };
    return map[color] || map.blue;
}

function isCustomsHoldStatus(customs) {
    const c = String(customs || '').toUpperCase();
    return ['RED_LANE_CHECK', 'RED_LANE', 'RED_LINE', 'INSPECTION', 'DOC_HOLD', 'VGM_HOLD'].includes(c);
}

function showYardMatrixToast(msg, tone = 'emerald') {
    const el = document.getElementById('yardMatrixToast');
    const txt = document.getElementById('yardMatrixToastMsg');
    if (!el || !txt) return;
    el.className = tone === 'red'
        ? 'p-3 rounded-xl bg-red-50 border border-red-300 text-red-900 text-xs flex items-center justify-between gap-2 animate-fadeIn'
        : 'p-3 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 text-xs flex items-center justify-between gap-2 animate-fadeIn';
    txt.textContent = msg;
    if (yardToastTimer) clearTimeout(yardToastTimer);
    yardToastTimer = setTimeout(() => el.classList.add('hidden'), 6500);
}

function selectYardMatrixSlot(blk, bayStr) {
    selectedMatrixBlock = String(blk || 'A').toUpperCase();
    selectedMatrixBay = pad2(bayStr || 1);
    const phys = currentYardTwinState?.physical;
    if (phys) {
        renderYardBlockTabsAndMatrix(phys, new Set(), new Set());
    }
}

function selectYardMatrixBlock(blk) {
    let bCode = String(blk || 'A').toUpperCase();
    if (bCode === 'R') bCode = 'REEFER';
    if (bCode === 'RAIL') bCode = 'D';
    const grid = normObj(currentYardTwinState?.physical?.yard_matrix_grid?.[bCode]);
    const occupiedBays = Object.keys(grid).sort();
    const firstBay = occupiedBays.length > 0 ? occupiedBays[0] : '01';
    selectYardMatrixSlot(bCode, firstBay);
}

function openYardSlotInspector(slot) {
    if (!slot) return;
    const ctr = slot.container_number || '-';
    const blk = String(slot.block || 'A').toUpperCase();
    const bay = pad2(slot.bay || 1);
    const row = pad2(slot.row || 1);
    const tier = pad2(slot.tier || 1);
    const isHold = isCustomsHoldStatus(slot.customs);

    document.getElementById('ysInspCtr').textContent = ctr;
    document.getElementById('ysInspCoord').textContent = `BLOK ${blk} • BAY ${bay} • ROW ${row} • TIER ${tier}`;
    document.getElementById('ysInspOwner').textContent = slot.owner || '-';
    document.getElementById('ysInspSize').textContent = slot.size_type || '40FT HIGH CUBE';
    document.getElementById('ysInspWeight').textContent = (slot.weight ? number_format_js(Math.round(slot.weight)) : '0') + ' kg';
    document.getElementById('ysInspBilling').textContent = slot.billing || 'NO_INVOICE';

    const custEl = document.getElementById('ysInspCustoms');
    custEl.textContent = slot.customs || 'PENDING';
    custEl.className = isHold
        ? 'text-[10.5px] font-bold text-red-700 mt-0.5'
        : 'text-[10.5px] font-bold text-emerald-700 mt-0.5';

    const badgeEl = document.getElementById('ysInspBadge');
    badgeEl.textContent = isHold ? 'CUSTOMS HOLD' : (slot.customs || 'IN YARD');
    badgeEl.className = isHold
        ? 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-500 text-white'
        : 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-400 text-slate-900';

    const clean = ctr.replace(/[^A-Z0-9]/gi, '');
    document.getElementById('ysInspTrackLink').href = 'dashboard.php?page=kontainer&search=' + encodeURIComponent(clean) + '&open=1';
    const twinLink = document.getElementById('ysInspTwinLink');
    if (twinLink) twinLink.href = 'dashboard.php?page=twin&focus_box=' + encodeURIComponent(clean);

    document.getElementById('ysInspRelocBtn').onclick = () => {
        closeModalById('modalYardSlotInspector');
        closeBayMatrixModal();
        openModalYardRelocate(ctr);
    };

    document.getElementById('modalYardSlotInspector')?.classList.remove('hidden');
}

function openYardPutawayModal(blk = null, bay = 1, row = 1, tier = 1) {
    const sel = document.getElementById('putawayContainerSelect');
    const phys = currentYardTwinState?.physical || {};
    if (sel) {
        sel.innerHTML = '';
        const gateArr = Array.isArray(phys.in_transit_containers) ? phys.in_transit_containers : [];
        const railArr = Array.isArray(phys.on_rail_containers) ? phys.on_rail_containers : [];
        const yardArr = Array.isArray(phys.active_slots)
            ? phys.active_slots.filter(s => !['CFS', 'GATE', 'OUTSIDE'].includes(String(s.block || '').toUpperCase()))
            : [];

        const addGroup = (label, arr, tag) => {
            if (!arr.length) return;
            const grp = document.createElement('optgroup');
            grp.label = label;
            arr.forEach(item => {
                const o = document.createElement('option');
                o.value = item.container_number;
                o.dataset.mode = (item.status === 'in_yard') ? 'relocate_container' : 'stack_container';
                const posInfo = item.status === 'in_yard'
                    ? `(${item.block}-B${pad2(item.bay)}-R${pad2(item.row)}-T${pad2(item.tier)})`
                    : `(${tag} • ${item.customs || 'IN_TRANSIT'})`;
                o.textContent = `${item.container_number} — ${item.size_type || '40ft'} ${posInfo}`;
                grp.appendChild(o);
            });
            sel.appendChild(grp);
        };

        addGroup('Antrean Gate / In-Transit (Stack Masuk CY)', gateArr, 'GATE');
        addGroup('Antrean Rail Siding KA (Unloading ke CY)', railArr, 'RAIL');
        addGroup('Kontainer Aktif di Yard (Pindah / Relokasi)', yardArr, 'YARD');

        if (!sel.options.length) {
            const o = document.createElement('option');
            o.value = '';
            o.textContent = 'Tidak ada kontainer tersedia';
            sel.appendChild(o);
        }
    }

    const bSel = document.getElementById('putawayBlockSelect');
    const bayIn = document.getElementById('putawayBayInput');
    const rowIn = document.getElementById('putawayRowInput');
    const tierIn = document.getElementById('putawayTierInput');
    if (bSel) bSel.value = String(blk || selectedMatrixBlock || 'A').toUpperCase();
    if (bayIn) bayIn.value = parseInt(bay || selectedMatrixBay || 1, 10) || 1;
    if (rowIn) rowIn.value = parseInt(row || 1, 10) || 1;
    if (tierIn) tierIn.value = parseInt(tier || 1, 10) || 1;

    document.getElementById('putawayErrorBox')?.classList.add('hidden');
    updatePutawaySlotPreview();
    document.getElementById('modalYardSlotPutaway')?.classList.remove('hidden');
}

function inspectSlotOccupancy(blk, bay, row, tier, excludeCtr = '') {
    const bKey = String(blk || 'A').toUpperCase();
    const bayS = pad2(bay);
    const rowS = pad2(row);
    const tierN = Math.max(1, parseInt(tier || 1, 10));
    const tierS = pad2(tierN);
    const code = `${bKey}-B${bayS}-R${rowS}-T${tierS}`;

    const rowMap = normObj(currentYardTwinState?.physical?.yard_matrix_grid?.[bKey]?.[bayS]?.[rowS]);
    const occupant = rowMap[tierS] || null;
    const isConflict = occupant && String(occupant.container_number || '').toUpperCase() !== String(excludeCtr || '').toUpperCase();

    const missingBelow = [];
    for (let t = 1; t < tierN; t++) {
        const tS = pad2(t);
        if (!rowMap[tS]) missingBelow.push('T' + tS);
    }

    return { code, isConflict, occupant, missingBelow };
}

function updatePutawaySlotPreview() {
    const blk = document.getElementById('putawayBlockSelect')?.value || 'A';
    const bay = document.getElementById('putawayBayInput')?.value || 1;
    const row = document.getElementById('putawayRowInput')?.value || 1;
    const tier = document.getElementById('putawayTierInput')?.value || 1;
    const sel = document.getElementById('putawayContainerSelect');
    const ctr = sel ? sel.value : '';

    const check = inspectSlotOccupancy(blk, bay, row, tier, ctr);
    const codeEl = document.getElementById('putawaySlotCodePreview');
    const pillEl = document.getElementById('putawaySlotStatusPill');
    const gravEl = document.getElementById('putawayGravityHint');

    if (codeEl) codeEl.textContent = check.code;
    if (pillEl) {
        if (check.isConflict) {
            pillEl.className = 'px-2.5 py-1.5 rounded-xl text-[11px] font-bold bg-red-50 text-red-700 border border-red-200';
            pillEl.textContent = `SLOT TERISI OLEH ${check.occupant.container_number} — ANTI-BENTROK`;
        } else {
            pillEl.className = 'px-2.5 py-1.5 rounded-xl text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200';
            pillEl.textContent = `SLOT KOSONG (${check.code}) — SIAP PENUMPUKAN`;
        }
    }
    if (gravEl) {
        if (check.missingBelow.length > 0 && !check.isConflict) {
            gravEl.className = 'px-2.5 py-1.5 rounded-xl text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200';
            gravEl.textContent = `Peringatan Stabilitas: Level di bawahnya (${check.missingBelow.join(', ')}) masih kosong — tumpukan disarankan mulai T01.`;
            gravEl.classList.remove('hidden');
        } else {
            gravEl.classList.add('hidden');
        }
    }
}

async function applyAutoSlotRecommendation() {
    const sel = document.getElementById('putawayContainerSelect');
    const ctr = sel ? sel.value : '';
    if (!ctr) return;
    try {
        const res = await fetch('api/yard_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'recommend_slot', container_number: ctr })
        });
        const data = await res.json();
        const rec = data && data.recommendation;
        if (rec) {
            const bSel = document.getElementById('putawayBlockSelect');
            const bayI = document.getElementById('putawayBayInput');
            const rowI = document.getElementById('putawayRowInput');
            const tierI = document.getElementById('putawayTierInput');
            if (bSel) bSel.value = rec.block;
            if (bayI) bayI.value = parseInt(rec.bay, 10) || 1;
            if (rowI) rowI.value = parseInt(rec.row, 10) || 1;
            if (tierI) tierI.value = parseInt(rec.tier, 10) || 1;
            updatePutawaySlotPreview();
            const recEl = document.getElementById('putawayAutoSlotReason');
            if (recEl) {
                recEl.textContent = 'Rekomendasi Keputusan Auto-Slotting: ' + rec.reason;
                recEl.classList.remove('hidden');
            }
        }
    } catch (err) {
        if (window.cidpToast) cidpToast('Auto-Slot Gagal', err.message, 'error');
    }
}

async function submitYardPutaway(event) {
    if (event) event.preventDefault();
    const sel = document.getElementById('putawayContainerSelect');
    const ctr = sel ? sel.value : '';
    const opt = sel && sel.selectedIndex >= 0 ? sel.options[sel.selectedIndex] : null;
    const apiAction = opt?.dataset?.mode || 'stack_container';

    const blk = document.getElementById('putawayBlockSelect')?.value || 'A';
    const bay = parseInt(document.getElementById('putawayBayInput')?.value || '1', 10);
    const row = parseInt(document.getElementById('putawayRowInput')?.value || '1', 10);
    const tier = parseInt(document.getElementById('putawayTierInput')?.value || '1', 10);
    const eqId = document.getElementById('putawayEquipSelect')?.value || 'RS-01';
    const opName = document.getElementById('putawayOperatorInput')?.value || 'Juan Gamaliel';

    if (window.cidpConfirm) {
        const slotStr = `${blk}-B${String(bay).padStart(2, '0')}-R${String(row).padStart(2, '0')}-T${String(tier).padStart(2, '0')}`;
        const ok = await window.cidpConfirm({
            title: 'Konfirmasi Putaway / Relokasi Yard',
            target: `${ctr} → ${slotStr}`,
            message: `Tugaskan unit ${eqId} (${opName}) menempatkan peti kemas ${ctr} ke slot ${slotStr}?`,
            confirmText: 'Ya, Eksekusi',
            cancelText: 'Tidak, Batal',
            tone: 'primary'
        });
        if (!ok) return;
    }

    const errEl = document.getElementById('putawayErrorBox');
    const btn = document.getElementById('btnSubmitYardPutaway');
    errEl?.classList.add('hidden');
    if (btn) { btn.disabled = true; btn.classList.add('opacity-70'); }

    try {
        const res = await fetch('api/yard_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: apiAction,
                container_number: ctr,
                to_block: blk,
                to_bay: bay,
                to_row: row,
                to_tier: tier,
                equipment_id: eqId,
                operator_name: opName
            })
        });
        const data = await res.json();
        if (!res.ok || !data.success) {
            if (errEl) {
                errEl.textContent = data.message || 'Gagal menumpuk kontainer ke slot.';
                errEl.classList.remove('hidden');
            }
            return;
        }

        closeModalById('modalYardSlotPutaway');
        selectedMatrixBlock = blk;
        selectedMatrixBay = pad2(bay);
        const msg = data.gravity_warning ? `${data.message} (${data.gravity_warning})` : data.message;
        if (data.state && data.state.physical) {
            renderLiveYardMatrix(data.state.physical, msg, true);
        }
    } catch (err) {
        if (errEl) {
            errEl.textContent = 'Koneksi ke api/yard_action.php gagal: ' + err.message;
            errEl.classList.remove('hidden');
        }
    } finally {
        if (btn) { btn.disabled = false; btn.classList.remove('opacity-70'); }
    }
}

function updateYardRelocateSlotPreview() {
    const ctr = document.getElementById('relocateContainerSelect')?.value || '';
    const blk = document.getElementById('relocateToBlock')?.value || 'A';
    const bay = document.getElementById('relocateToBay')?.value || 1;
    const row = document.getElementById('relocateToRow')?.value || 1;
    const tier = document.getElementById('relocateToTier')?.value || 1;

    const pill = document.getElementById('relocYardSlotPreview');
    if (!pill) return;
    const check = inspectSlotOccupancy(blk, bay, row, tier, ctr);
    if (check.isConflict) {
        pill.className = 'mt-2 px-2.5 py-1.5 rounded-xl text-[10.5px] font-bold bg-red-50 text-red-700 border border-red-200';
        pill.textContent = `TERISI (${check.code} ditempati ${check.occupant.container_number}) — Pilih slot lain!`;
    } else {
        const gravTxt = check.missingBelow.length > 0 ? ` • Peringatan: ${check.missingBelow.join(', ')} kosong` : '';
        pill.className = 'mt-2 px-2.5 py-1.5 rounded-xl text-[10.5px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200';
        pill.textContent = `SLOT KOSONG (${check.code}) — SIAP VMT${gravTxt}`;
    }
}

function renderBayCrossSectionHtml(blk, bayStr, changedSlotKeys = new Set(), compactModal = false) {
    const cat = currentYardTwinState?.physical?.yard_matrix_catalog || {};
    const cfg = cat[blk] || { rows: 4, tiers: 4, color: 'blue', name: 'Blok ' + blk, equipment: 'RS-01' };
    const bayMap = normObj(currentYardTwinState?.physical?.yard_matrix_grid?.[blk]?.[bayStr]);

    let maxRow = Math.max(4, parseInt(cfg.rows || 4, 10));
    let maxTier = Math.max(4, parseInt(cfg.tiers || 4, 10));
    Object.keys(bayMap).forEach(rK => {
        maxRow = Math.max(maxRow, parseInt(rK, 10) || 4);
        const rObj = normObj(bayMap[rK]);
        Object.keys(rObj).forEach(tK => { maxTier = Math.max(maxTier, parseInt(tK, 10) || 4); });
    });

    const theme = getYardBlockTheme(cfg.color);
    const colsStyle = `grid-template-columns: 52px repeat(${maxRow}, minmax(0, 1fr));`;
    let html = `<div class="grid gap-1.5 items-center text-center" style="${colsStyle}">`;
    html += `<div class="text-[10px] font-mono font-bold text-slate-400 uppercase">Tier \\ Row</div>`;
    for (let r = 1; r <= maxRow; r++) {
        html += `<div class="text-[11px] font-mono font-bold text-slate-600 bg-white border border-slate-200 rounded-lg py-1">Row ${pad2(r)}</div>`;
    }
    html += `</div>`;

    for (let t = maxTier; t >= 1; t--) {
        const tierStr = pad2(t);
        html += `<div class="grid gap-1.5 items-stretch" style="${colsStyle}">`;
        html += `<div class="flex items-center justify-center rounded-lg bg-slate-200/70 text-slate-700 font-mono font-black text-[9.5px] leading-tight text-center px-0.5">T${tierStr}${t === 1 ? '<br>(GND)' : ''}</div>`;

        for (let r = 1; r <= maxRow; r++) {
            const rowStr = pad2(r);
            const slot = bayMap[rowStr] ? bayMap[rowStr][tierStr] : null;
            const slotKey = `${blk}-${bayStr}-${rowStr}-${tierStr}`;
            const isChanged = changedSlotKeys.has(slotKey);
            const ringCls = isChanged ? ' ring-4 ring-orange-400 shadow-lg' : '';

            if (slot) {
                const hold = isCustomsHoldStatus(slot.customs);
                const cellCls = hold
                    ? 'bg-red-600 text-white border-b-2 border-amber-300'
                    : theme.cell;
                const wtTon = (parseFloat(slot.weight || 0) / 1000).toFixed(1);
                const custShort = hold ? 'HOLD' : String(slot.customs || 'SPPB').replace(/_/g, ' ');
                const hCls = compactModal ? 'min-h-[68px]' : 'min-h-[82px]';

                html += `
                    <button type="button" data-slot-filled="${escapeYardHtml(slot.container_number)}" data-slot-blk="${blk}" data-slot-bay="${bayStr}" data-slot-row="${rowStr}" data-slot-tier="${tierStr}"
                        class="${hCls} rounded-xl ${cellCls}${ringCls} p-2 flex flex-col justify-between gap-0.5 text-left shadow-xs hover:brightness-110 transition cursor-pointer relative overflow-hidden">
                        <div class="flex items-center justify-between gap-1 w-full">
                            <span class="font-mono font-black text-[11px] truncate">${escapeYardHtml(slot.container_number)}</span>
                            ${hold ? '<i class="fa-solid fa-triangle-exclamation text-amber-300 text-[10px] shrink-0"></i>' : ''}
                        </div>
                        <div class="text-[9.5px] opacity-90 truncate w-full font-medium">${escapeYardHtml(slot.owner || '-')}</div>
                        <div class="flex items-center justify-between gap-1 w-full text-[9px] font-mono opacity-95">
                            <span class="shrink-0">${wtTon}t &bull; ${String(slot.size_type || '').includes('20') ? '20ft' : '40ft'}</span>
                            <span class="px-1 rounded bg-black/25 truncate max-w-[66px]">${escapeYardHtml(custShort)}</span>
                        </div>
                    </button>
                `;
            } else {
                const hCls = compactModal ? 'min-h-[68px]' : 'min-h-[82px]';
                html += `
                    <button type="button" data-slot-empty="${slotKey}" data-slot-blk="${blk}" data-slot-bay="${parseInt(bayStr, 10)}" data-slot-row="${r}" data-slot-tier="${t}"
                        class="${hCls} rounded-xl border border-dashed border-slate-300 bg-white/80 hover:bg-blue-50/70 hover:border-[#0170b9] text-slate-400 hover:text-[#0170b9] flex flex-col items-center justify-center p-1.5 transition cursor-pointer group${ringCls}">
                        <span class="text-[9.5px] font-mono font-bold group-hover:hidden">KOSONG (R${rowStr}-T${tierStr})</span>
                        <span class="hidden group-hover:inline-flex items-center gap-1 text-[10px] font-bold"><i class="fa-solid fa-plus"></i> Stack T${tierStr}</span>
                    </button>
                `;
            }
        }
        html += `</div>`;
    }
    return html;
}

function bindCrossSectionSlotEvents(containerEl) {
    if (!containerEl) return;
    containerEl.querySelectorAll('[data-slot-filled]').forEach(btn => {
        btn.addEventListener('click', () => {
            const b = btn.getAttribute('data-slot-blk');
            const bay = btn.getAttribute('data-slot-bay');
            const r = btn.getAttribute('data-slot-row');
            const t = btn.getAttribute('data-slot-tier');
            const slot = currentYardTwinState?.physical?.yard_matrix_grid?.[b]?.[bay]?.[r]?.[t];
            if (slot) openYardSlotInspector(slot);
        });
    });
    containerEl.querySelectorAll('[data-slot-empty]').forEach(btn => {
        btn.addEventListener('click', () => {
            const b = btn.getAttribute('data-slot-blk');
            const bay = parseInt(btn.getAttribute('data-slot-bay') || '1', 10);
            const r = parseInt(btn.getAttribute('data-slot-row') || '1', 10);
            const t = parseInt(btn.getAttribute('data-slot-tier') || '1', 10);
            openYardPutawayModal(b, bay, r, t);
        });
    });
}

function renderYardBlockTabsAndMatrix(physical, changedBayKeys = new Set(), changedSlotKeys = new Set()) {
    const stats = physical.yard_matrix_stats || {};
    const blocks = stats.blocks || {};
    const bayCountsAll = stats.bay_counts || {};
    const grid = physical.yard_matrix_grid || {};

    if (!blocks[selectedMatrixBlock]) selectedMatrixBlock = 'A';
    const curBlkSum = blocks[selectedMatrixBlock] || { name: 'Blok A', zone: 'LADEN_EXP', bays: 10, rows: 4, tiers: 4, equipment: 'RS-01', color: 'blue' };
    const maxBayForBlk = Math.max(4, parseInt(curBlkSum.bays || 10, 10));
    if (parseInt(selectedMatrixBay, 10) > maxBayForBlk) selectedMatrixBay = '01';

    // Render Block Overview Cards (Layer 1)
    const overviewEl = document.getElementById('yardBlocksOverviewGrid');
    if (overviewEl) {
        let cardsHtml = '';
        Object.keys(blocks).forEach(bCode => {
            const bs = blocks[bCode];
            const theme = getYardBlockTheme(bs.color);
            const isSelectedBlk = (bCode === selectedMatrixBlock);
            const bCounts = normObj(bayCountsAll[bCode]);
            const blkGrid = normObj(grid[bCode]);
            const maxBays = Math.max(4, parseInt(bs.bays || 4, 10));

            let bayTiles = '';
            for (let b = 1; b <= maxBays; b++) {
                const bayStr = pad2(b);
                const cnt = parseInt(bCounts[bayStr] || 0, 10);
                const isSelBay = isSelectedBlk && (bayStr === selectedMatrixBay);
                const isChangedBay = changedBayKeys.has(`${bCode}-${bayStr}`);

                // Detect any customs hold in this bay
                let bayHold = false;
                const bayData = normObj(blkGrid[bayStr]);
                Object.values(bayData).forEach(rObj => {
                    Object.values(normObj(rObj)).forEach(sl => {
                        if (isCustomsHoldStatus(sl.customs)) bayHold = true;
                    });
                });

                const tileCls = bayHold
                    ? 'bg-red-600 text-white border-red-700'
                    : (cnt > 0 ? `${theme.mini} border-transparent shadow-2xs` : 'bg-slate-100 text-slate-500 border-slate-200 hover:bg-slate-200');
                const ring = isSelBay ? ' ring-2 ring-orange-500 ring-offset-1 scale-105' : (isChangedBay ? ' ring-2 ring-amber-400 animate-pulse' : '');

                bayTiles += `
                    <button type="button" onclick="selectYardMatrixSlot('${bCode}', '${bayStr}')"
                        title="Blok ${bCode} Bay ${bayStr}: ${cnt} kontainer terisi"
                        class="px-1.5 py-1 rounded-md border text-[9.5px] font-mono font-bold transition cursor-pointer flex items-center justify-between gap-1 ${tileCls}${ring}">
                        <span>B${bayStr}</span>
                        ${cnt > 0 ? `<span class="px-1 rounded bg-black/20 text-[8.5px]">${cnt}</span>` : ''}
                    </button>
                `;
            }

            cardsHtml += `
                <div class="rounded-xl p-3 border transition-all bg-white ${isSelectedBlk ? 'border-[#0170b9] ring-2 ring-blue-100 shadow-xs' : 'border-slate-200 hover:border-slate-300'}">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <button type="button" onclick="selectYardMatrixBlock('${bCode}')" class="text-left cursor-pointer min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span class="px-1.5 py-0.5 rounded font-mono font-black text-[10px] ${theme.chip}">${escapeYardHtml(bCode)}</span>
                                <span class="font-bold text-slate-800 text-[11px] truncate">${escapeYardHtml(bs.name.replace(/^Blok [^—]+—\s*/i, ''))}</span>
                            </div>
                        </button>
                        <div class="flex items-center gap-1 shrink-0">
                            ${bs.hold_count > 0 ? `<span class="px-1.5 py-0.5 rounded-full bg-red-50 text-red-700 border border-red-200 font-bold text-[9px]">${bs.hold_count} HOLD</span>` : ''}
                            <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 font-mono font-bold text-[10px]">${bs.occupied}/${bs.total_slots}</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-5 gap-1">${bayTiles}</div>
                </div>
            `;
        });
        overviewEl.innerHTML = cardsHtml;
    }

    // Render Block Tabs in Cross Section
    const tabsEl = document.getElementById('yardMatrixBlockTabs');
    if (tabsEl) {
        let tHtml = '';
        Object.keys(blocks).forEach(bCode => {
            const bs = blocks[bCode];
            const active = (bCode === selectedMatrixBlock);
            tHtml += `
                <button type="button" onclick="selectYardMatrixBlock('${bCode}')"
                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition cursor-pointer ${active ? 'bg-[#0170b9] text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100'}">
                    ${escapeYardHtml(bCode)} <span class="font-mono opacity-80">(${bs.occupied})</span>
                </button>
            `;
        });
        tabsEl.innerHTML = tHtml;
    }

    // Render Bay Pills for Selected Block
    const bayPillsEl = document.getElementById('yardMatrixBayPills');
    const curBayCounts = normObj(bayCountsAll[selectedMatrixBlock]);
    if (bayPillsEl) {
        let pHtml = '';
        for (let b = 1; b <= maxBayForBlk; b++) {
            const bayStr = pad2(b);
            const cnt = parseInt(curBayCounts[bayStr] || 0, 10);
            const active = (bayStr === selectedMatrixBay);
            pHtml += `
                <button type="button" onclick="selectYardMatrixSlot('${selectedMatrixBlock}', '${bayStr}')"
                    class="px-2 py-1 rounded-lg text-[10.5px] font-mono font-bold transition cursor-pointer ${active ? 'bg-orange-500 text-white shadow-xs' : (cnt > 0 ? 'bg-blue-50 text-[#0170b9] border border-blue-200 hover:bg-blue-100' : 'bg-white text-slate-500 border border-slate-200 hover:bg-slate-100')}">
                    B${bayStr}${cnt > 0 ? ` (${cnt})` : ''}
                </button>
            `;
        }
        bayPillsEl.innerHTML = pHtml;
    }

    // Update Cross-Section Titles
    const occCurBay = parseInt(curBayCounts[selectedMatrixBay] || 0, 10);
    const maxBayCap = (parseInt(curBlkSum.rows || 4, 10) * parseInt(curBlkSum.tiers || 4, 10));
    const titleEl = document.getElementById('yardActiveBayTitle');
    const zoneEl = document.getElementById('yardActiveBayZone');
    const occEl = document.getElementById('yardActiveBayOcc');
    const eqEl = document.getElementById('yardActiveBayEquip');

    if (titleEl) titleEl.textContent = `BLOK ${selectedMatrixBlock} • BAY ${selectedMatrixBay}`;
    if (zoneEl) zoneEl.textContent = curBlkSum.name || 'Container Yard';
    if (occEl) occEl.textContent = `${occCurBay}/${maxBayCap} Slot Terisi`;
    if (eqEl) eqEl.innerHTML = `<i class="fa-solid fa-truck-monster text-orange-500 mr-1"></i>Alat Penumpuk: ${escapeYardHtml(curBlkSum.equipment || 'RS-01')}`;

    const gridEl = document.getElementById('yardCrossSectionGrid');
    if (gridEl) {
        gridEl.innerHTML = renderBayCrossSectionHtml(selectedMatrixBlock, selectedMatrixBay, changedSlotKeys, false);
        bindCrossSectionSlotEvents(gridEl);
    }

    // Also update Dynamic Modal Bay Matrix if currently open
    const bayModal = document.getElementById('bayMatrixModal');
    if (bayModal && !bayModal.classList.contains('hidden')) {
        renderDynamicBayMatrixModal(selectedMatrixBlock, selectedMatrixBay, changedSlotKeys);
    }
}

function renderDynamicBayMatrixModal(blk, bayStr, changedSlotKeys = new Set()) {
    const titleEl = document.getElementById('bayMatrixModalTitle');
    const subEl = document.getElementById('bayMatrixModalSub');
    const bodyEl = document.getElementById('bayMatrixModalBody');
    const cfg = currentYardTwinState?.physical?.yard_matrix_catalog?.[blk] || { name: 'Blok ' + blk, rows: 4, tiers: 4 };
    if (titleEl) titleEl.textContent = `Penampang 2D Matrix — Blok ${blk} (Bay ${bayStr})`;
    if (subEl) subEl.textContent = `${cfg.name} • ${cfg.rows || 4} Row × ${cfg.tiers || 4} Tier Real-Time SSoT`;
    if (bodyEl) {
        bodyEl.innerHTML = renderBayCrossSectionHtml(blk, bayStr, changedSlotKeys, true);
        bindCrossSectionSlotEvents(bodyEl);
    }
}

function showBayMatrixView() {
    const blk = selectedMatrixBlock || 'A';
    const bayStr = selectedMatrixBay || '01';
    renderDynamicBayMatrixModal(blk === 'RAIL' ? 'D' : blk, bayStr);
    const m = document.getElementById('bayMatrixModal');
    if (m) m.classList.remove('hidden');
}

function closeBayMatrixModal() {
    const m = document.getElementById('bayMatrixModal');
    if (m) m.classList.add('hidden');
}

function renderLiveYardMatrix(physical, toastHint = null, force3DRebuild = false) {
    if (!physical || !physical.yard_matrix_catalog) return;
    if (!currentYardTwinState || typeof currentYardTwinState !== 'object') currentYardTwinState = {};
    currentYardTwinState.physical = physical;

    const grid = physical.yard_matrix_grid || {};
    const stats = physical.yard_matrix_stats || {};
    const nextSlotMap = {};
    const changedBayKeys = new Set();
    const changedSlotKeys = new Set();
    const changeNotes = [];

    Object.keys(grid).forEach(blk => {
        const blkObj = normObj(grid[blk]);
        Object.keys(blkObj).forEach(bayS => {
            const bayObj = normObj(blkObj[bayS]);
            Object.keys(bayObj).forEach(rowS => {
                const rowObj = normObj(bayObj[rowS]);
                Object.keys(rowObj).forEach(tierS => {
                    const sl = rowObj[tierS];
                    if (!sl) return;
                    const key = `${blk}-${bayS}-${rowS}-${tierS}`;
                    const ctr = String(sl.container_number || '');
                    nextSlotMap[key] = ctr;
                    if (prevYardSlotMap !== null && prevYardSlotMap[key] !== ctr) {
                        changedBayKeys.add(`${blk}-${bayS}`);
                        changedSlotKeys.add(key);
                        changeNotes.push(`+ ${ctr} -> ${key}`);
                    }
                });
            });
        });
    });

    if (prevYardSlotMap !== null) {
        Object.keys(prevYardSlotMap).forEach(oldKey => {
            if (!nextSlotMap[oldKey]) {
                const parts = oldKey.split('-');
                if (parts.length >= 2) changedBayKeys.add(`${parts[0]}-${parts[1]}`);
                changedSlotKeys.add(oldKey);
            }
        });
    }

    const hadChanges = (prevYardSlotMap !== null && changedSlotKeys.size > 0);
    prevYardSlotMap = nextSlotMap;

    // Update Summary Counters & KPI Micro-Cards
    const occCnt = parseInt(stats.occupied_yard_slots ?? 0, 10);
    const totalSlots = parseInt(stats.total_slots ?? 928, 10);
    const emptyCnt = parseInt(stats.empty_yard_slots ?? (totalSlots - occCnt), 10);
    const teus = parseInt(stats.teus_in_yard ?? 0, 10);
    const occPct = Number(stats.occupancy_pct || 0);

    const setTxt = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = String(v); };
    setTxt('yardHeaderBoxCount', `MySQL Sync (${occCnt} Box)`);
    setTxt('yardKpiVal-1', `${((teus / 15000) * 100).toFixed(2)}%`);
    setTxt('yardKpiUnit-1', `(${teus} TEUs)`);
    setTxt('yardGateStageCnt', stats.gate_staging_count ?? 0);
    setTxt('yardCfsStageCnt', stats.cfs_dock_count ?? 0);
    setTxt('yardRailStageCnt', stats.on_rail_count ?? 0);
    setTxt('yardHoldCnt', stats.hold_count ?? 0);
    setTxt('yardSumBlocks', stats.total_blocks ?? 8);
    setTxt('yardSumOccupied', occCnt);
    setTxt('yardSumEmpty', emptyCnt);
    setTxt('yardSumTeus', teus);
    setTxt('yardOccPctLabel', `${occPct.toFixed(1)}%`);

    const fillEl = document.getElementById('yardOccBarFill');
    if (fillEl) fillEl.style.width = `${Math.max(0, Math.min(100, occPct))}%`;
    const occTextEl = document.getElementById('yardOccText');
    if (occTextEl) occTextEl.textContent = `${occCnt} dari ${totalSlots} slot CY terisi aktif (${teus} TEUs across 8 Blok Yard)`;

    // Refresh #relocateContainerSelect options dynamically
    const relocSel = document.getElementById('relocateContainerSelect');
    if (relocSel && Array.isArray(physical.active_slots)) {
        const prevVal = relocSel.value;
        const validCY = physical.active_slots.filter(s => !['CFS', 'GATE', 'OUTSIDE'].includes(String(s.block || '').toUpperCase()));
        relocSel.innerHTML = '';
        validCY.forEach(s => {
            const o = document.createElement('option');
            o.value = s.container_number;
            o.textContent = `${s.container_number} (${s.size_type || '40ft'}) — Blok ${s.block} Bay ${pad2(s.bay)} Row ${pad2(s.row)} Tier ${pad2(s.tier)}`;
            relocSel.appendChild(o);
        });
        if (prevVal) relocSel.value = prevVal;
    }

    renderYardBlockTabsAndMatrix(physical, changedBayKeys, changedSlotKeys);

    // Sync open Putaway / Relocate preview badges if open
    const putModal = document.getElementById('modalYardSlotPutaway');
    if (putModal && !putModal.classList.contains('hidden')) updatePutawaySlotPreview();
    const relModal = document.getElementById('modalRelocateYard');
    if (relModal && !relModal.classList.contains('hidden')) updateYardRelocateSlotPreview();

    if (toastHint) {
        showYardMatrixToast(toastHint, 'emerald');
    } else if (changeNotes.length > 0) {
        showYardMatrixToast('Sync Real-Time CY SSoT: ' + changeNotes.slice(0, 3).join(' | '), 'emerald');
    }

    // State matriks & modal telah tersinkron langsung (3D terpusat di Digital Twin)
}

function dispatchWorkOrderFromInspector() {
    openModalYardRelocate('');
}

// Universal Deep-Linking Handler
function initYardDeepLinking() {
    const urlParams = new URLSearchParams(window.location.search);
    const focusBox = urlParams.get('focus_box') || urlParams.get('ctr') || urlParams.get('find') || urlParams.get('search');
    const blockParam = urlParams.get('block');
    const actionParam = urlParams.get('action');

    if (blockParam) {
        selectYardMatrixBlock(blockParam.toUpperCase());
    }
    if (focusBox) {
        const cleanBox = focusBox.replace(/[^A-Z0-9]/gi, '').toUpperCase();
        const foundSlot = (currentYardTwinState?.physical?.active_slots || []).find(s =>
            String(s.container_number || '').replace(/[^A-Z0-9]/gi, '').toUpperCase() === cleanBox
        );
        if (foundSlot && foundSlot.block && foundSlot.bay) {
            const normBlk = foundSlot.block === 'RAIL' ? 'D' : String(foundSlot.block).toUpperCase();
            selectYardMatrixSlot(normBlk, foundSlot.bay);
            setTimeout(() => {
                const cell = document.querySelector(`[data-slot-filled="${foundSlot.container_number}"]`);
                if (cell) {
                    cell.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    cell.classList.add('ring-4', 'ring-orange-400', 'shadow-lg');
                    setTimeout(() => cell.classList.remove('ring-4', 'ring-orange-400', 'shadow-lg'), 4000);
                }
            }, 150);
        }
    }
    if (actionParam === 'relocate') {
        openModalYardRelocate(focusBox || '');
    }
}

// Modal Relokasi Kontainer VMT
function openModalYardRelocate(prefillId = '') {
    const m = document.getElementById('modalRelocateYard');
    document.getElementById('relocYardErrorBox')?.classList.add('hidden');
    if (m) {
        m.classList.remove('hidden');
        if (prefillId) {
            const sel = document.getElementById('relocateContainerSelect');
            if (sel) sel.value = prefillId;
        }
        updateYardRelocateSlotPreview();
    }
}

function closeModalYardRelocate() {
    const m = document.getElementById('modalRelocateYard');
    if (m) m.classList.add('hidden');
}

function initYardLiveSync() {
    // Wire AJAX VMT Relocation Form
    const relocForm = document.getElementById('modalRelocateYardForm');
    if (relocForm && !relocForm.dataset.boundAjax) {
        relocForm.dataset.boundAjax = '1';
        relocForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const fd = new FormData(relocForm);
            const errBox = document.getElementById('relocYardErrorBox');
            const btn = document.getElementById('btnSubmitYardReloc');
            errBox?.classList.add('hidden');
            if (btn) { btn.disabled = true; btn.classList.add('opacity-70'); }

            try {
                const res = await fetch('api/yard_action.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'relocate_container',
                        container_number: fd.get('container_number') || '',
                        equipment_id: fd.get('equipment_id') || 'RS-01',
                        operator_name: fd.get('operator_name') || 'Juan Gamaliel',
                        to_block: fd.get('to_block') || 'A',
                        to_bay: parseInt(fd.get('to_bay') || '1', 10),
                        to_row: parseInt(fd.get('to_row') || '1', 10),
                        to_tier: parseInt(fd.get('to_tier') || '1', 10)
                    })
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    if (errBox) {
                        errBox.textContent = data.message || 'Relokasi kontainer ditolak.';
                        errBox.classList.remove('hidden');
                    }
                    return;
                }
                closeModalYardRelocate();
                selectedMatrixBlock = String(fd.get('to_block') || 'A').toUpperCase();
                selectedMatrixBay = pad2(fd.get('to_bay') || 1);
                const msg = data.gravity_warning ? `${data.message} (${data.gravity_warning})` : data.message;
                if (data.state && data.state.physical) {
                    renderLiveYardMatrix(data.state.physical, msg, true);
                }
            } catch (err) {
                if (errBox) {
                    errBox.textContent = 'Koneksi ke api/yard_action.php gagal: ' + err.message;
                    errBox.classList.remove('hidden');
                }
            } finally {
                if (btn) { btn.disabled = false; btn.classList.remove('opacity-70'); }
            }
        });
    }

    if (currentYardTwinState && currentYardTwinState.physical) {
        renderLiveYardMatrix(currentYardTwinState.physical);
    }

    const startFallback = () => {
        if (yardFallbackTimer) return;
        yardFallbackTimer = setInterval(async () => {
            try {
                const res = await fetch('api/stream_twin.php?poll=1');
                if (!res.ok) return;
                const payload = await res.json();
                if (payload && payload.physical) renderLiveYardMatrix(payload.physical);
            } catch (e) {}
        }, 5000);
    };

    const connectSse = () => {
        if (typeof EventSource === 'undefined') {
            startFallback();
            return;
        }
        if (yardEventSource) {
            try { yardEventSource.close(); } catch (e) {}
        }

        const badge = document.getElementById('yardLiveStreamBadge');
        const badgeTxt = document.getElementById('yardLiveStreamText');
        const es = new EventSource('api/stream_twin.php');
        yardEventSource = es;

        const handleSse = (ev) => {
            if (yardFallbackTimer) {
                clearInterval(yardFallbackTimer);
                yardFallbackTimer = null;
            }
            if (badge && badgeTxt) {
                badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200';
                badgeTxt.textContent = 'Live SSE Sync (SSoT)';
            }
            try {
                const payload = JSON.parse(ev.data);
                if (payload && payload.physical) renderLiveYardMatrix(payload.physical);
            } catch (err) {}
        };

        es.addEventListener('initial_state', handleSse);
        es.addEventListener('state_update', handleSse);
        es.addEventListener('reconnect_advisory', () => {
            try { es.close(); } catch (e) {}
            setTimeout(connectSse, 400);
        });
        es.onerror = () => {
            if (badge && badgeTxt) {
                badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200';
                badgeTxt.textContent = 'Sync Polling (Fallback)';
            }
            startFallback();
        };
    };

    connectSse();
}

// Jalankan saat DOM siap
document.addEventListener('DOMContentLoaded', () => {
    initYardLiveSync();
    setTimeout(initYardDeepLinking, 150);
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
            <button onclick="closeModalYardRelocate()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" id="modalRelocateYardForm" class="p-6 space-y-4">
            <input type="hidden" name="yard_action" value="relocate_container">

            <div>
                <label class="block text-[11px] font-bold text-gray-700 mb-1">Pilih Peti Kemas / Kontainer *</label>
                <select name="container_number" id="relocateContainerSelect" onchange="updateYardRelocateSlotPreview()" required class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono text-xs font-bold text-gray-900 focus:ring-2 focus:ring-blue-500">
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
                        <option value="RS-04">RS-04 (Kalmar DRG450 - Slamet Riyadi)</option>
                        <option value="RTG-01">RTG-01 (ZPMC 16-Wheel - Joko Susilo)</option>
                        <option value="RTG-02">RTG-02 (Konecranes E-RTG - Hendra Wijaya)</option>
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
                        <select name="to_block" id="relocateToBlock" onchange="updateYardRelocateSlotPreview()" class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-800">
                            <option value="A">Blok A (Ekspor)</option>
                            <option value="B">Blok B (Impor)</option>
                            <option value="C">Blok C (Domestik)</option>
                            <option value="D">Blok D (KA Buffer)</option>
                            <option value="E">Blok E (Karantina)</option>
                            <option value="DG">Blok DG (Hazard)</option>
                            <option value="EMPTY">Blok Empty</option>
                            <option value="REEFER">Blok Reefer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Bay (1-12)</label>
                        <input type="number" name="to_bay" id="relocateToBay" onchange="updateYardRelocateSlotPreview()" oninput="updateYardRelocateSlotPreview()" value="2" min="1" max="12" class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-mono font-bold text-center">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Row (1-6)</label>
                        <input type="number" name="to_row" id="relocateToRow" onchange="updateYardRelocateSlotPreview()" oninput="updateYardRelocateSlotPreview()" value="1" min="1" max="6" class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-mono font-bold text-center">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Tier (1-5)</label>
                        <input type="number" name="to_tier" id="relocateToTier" onchange="updateYardRelocateSlotPreview()" oninput="updateYardRelocateSlotPreview()" value="1" min="1" max="5" class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-mono font-bold text-center">
                    </div>
                </div>
                <div id="relocYardSlotPreview" class="mt-2 px-2.5 py-1.5 rounded-xl text-[10.5px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">SLOT KOSONG — SIAP VMT</div>
            </div>

            <div id="relocYardErrorBox" class="hidden p-2.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-[11px] font-semibold"></div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeModalYardRelocate()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="btnSubmitYardReloc" class="px-5 py-2 bg-[#004b87] hover:bg-[#0170b9] text-white font-bold rounded-xl shadow-md transition flex items-center space-x-1.5 cursor-pointer">
                    <i class="fa-solid fa-check"></i>
                    <span>Eksekusi Relokasi</span>
                </button>
            </div>
        </form>
    </div>
</div>
