<?php
// =============================================================================
// MODUL: LOKASI & STATUS ALAT BERAT (EQUIPMENT FLEET & LIVE POSITIONING)
// File: pages/alat.php
// CIDP Yard Management System - PT Multi Terminal Indonesia / ITL Trisakti
// =============================================================================

require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../api/preadvice_helper.php';
require_once __DIR__ . '/../api/twin_state_helper.php';
if (isset($pdo) && $pdo instanceof PDO) { ensure_integration_schema($pdo); }

// Handle POST Aksi Dispatcher Alat Berat (VMT Live Binding)
$alat_alert = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['alat_action'])) {
    if (isset($pdo)) {
        try {
            if ($_POST['alat_action'] === 'dispatch_job') {
                $eq_id = trim($_POST['equipment_id'] ?? '');
                $ctr_num = strtoupper(trim($_POST['container_number'] ?? ''));
                $job_type = trim($_POST['job_type'] ?? 'RELOCATION');
                $t_block = strtoupper(trim($_POST['target_block'] ?? 'A'));
                $t_bay = intval($_POST['target_bay'] ?? 1);
                $t_row = intval($_POST['target_row'] ?? 1);
                $t_tier = intval($_POST['target_tier'] ?? 1);
                $notes = trim($_POST['operator_notes'] ?? '');

                // Ambil info koordinat kontainer saat ini
                $stmtC = $pdo->prepare("SELECT block, bay, `row`, tier FROM containers WHERE container_number = ? LIMIT 1");
                $stmtC->execute([$ctr_num]);
                $cData = $stmtC->fetch(PDO::FETCH_ASSOC);
                $from_b = $cData['block'] ?? 'A';
                $from_by = $cData['bay'] ?? 1;

                // Ambil nama operator alat
                $stmtEq = $pdo->prepare("SELECT operator_name FROM equipment WHERE equipment_id = ? LIMIT 1");
                $stmtEq->execute([$eq_id]);
                $eqData = $stmtEq->fetch(PDO::FETCH_ASSOC);
                $op_name = $eqData['operator_name'] ?? 'Dispatcher Yard';

                if (empty($notes)) {
                    $notes = "Instruksi VMT $job_type kontainer $ctr_num dari Blok $from_b-Bay $from_by ke Blok $t_block-Bay $t_bay.";
                }

                $fee = ($job_type === 'RAIL_LOAD') ? 250000 : 150000;

                // 1. Update status armada alat + koordinat GPS/RTK blok tujuan (WCS -> MHE Spatial Sync)
                $blockCoords = [
                    'A' => [270.0, 175.0], 'B' => [490.0, 175.0], 'C' => [270.0, 385.0],
                    'D' => [490.0, 385.0], 'E' => [770.0, 385.0], 'REEFER' => [770.0, 175.0],
                    'RAIL' => [600.0, 625.0], 'CFS' => [210.0, 520.0]
                ];
                $bc = $blockCoords[$t_block] ?? [360.0, 240.0];
                $gx = round($bc[0] + (($t_bay % 4) * 12.0), 2);
                $gy = round($bc[1] + (($t_row % 4) * 10.0), 2);
                $stmtUpEq = $pdo->prepare("UPDATE equipment SET status = 'operating', current_container = ?, last_block = ?, gps_x = ?, gps_y = ?, last_updated = NOW() WHERE equipment_id = ?");
                $stmtUpEq->execute([$ctr_num, $t_block, $gx, $gy, $eq_id]);

                // 2. Update koordinat fisik kontainer di database (status ENUM valid: in_yard / on_rail)
                $newStatus = ($job_type === 'RAIL_LOAD' || $t_block === 'RAIL') ? 'on_rail' : 'in_yard';
                $stmtUpCtr = $pdo->prepare("UPDATE containers SET block = ?, bay = ?, `row` = ?, tier = ?, status = ? WHERE container_number = ?");
                $stmtUpCtr->execute([$t_block, $t_bay, $t_row, $t_tier, $newStatus, $ctr_num]);

                // 3. Catat audit pemindahan ke yard_events & akrual biaya LoLo ke Billing
                $stmtLog = $pdo->prepare("INSERT INTO yard_events (event_type, equipment_id, operator_name, container_number, from_block, from_bay, to_block, to_bay, notes, billable_amount, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmtLog->execute([$job_type, $eq_id, $op_name, $ctr_num, $from_b, $from_by, $t_block, $t_bay, $notes, $fee]);
                accrue_billing($pdo, $ctr_num, "Alat Berat $job_type ($eq_id)", 'lolo_amount', $fee);

                get_live_twin_state($pdo, true);

                $alat_alert = [
                    'type' => 'success',
                    'title' => "Job Order VMT Terkirim ke $eq_id",
                    'msg' => "Unit <strong>$eq_id</strong> (Operator: $op_name) ditugaskan untuk $job_type kontainer <strong>$ctr_num</strong> ke Blok $t_block (Bay $t_bay / Row $t_row / Tier $t_tier). Posisi fisik & tagihan telah diperbarui."
                ];
            } elseif ($_POST['alat_action'] === 'release_spreader') {
                $eq_id = trim($_POST['equipment_id'] ?? '');

                $stmtEq = $pdo->prepare("SELECT operator_name, current_container FROM equipment WHERE equipment_id = ? LIMIT 1");
                $stmtEq->execute([$eq_id]);
                $eqData = $stmtEq->fetch(PDO::FETCH_ASSOC);
                $op_name = $eqData['operator_name'] ?? 'Operator Unit';
                $cur_c = $eqData['current_container'] ?? '-';

                $stmtUpEq = $pdo->prepare("UPDATE equipment SET status = 'idle', current_container = NULL, last_updated = NOW() WHERE equipment_id = ?");
                $stmtUpEq->execute([$eq_id]);

                $stmtLog = $pdo->prepare("INSERT INTO yard_events (event_type, equipment_id, operator_name, container_number, notes, billable_amount, created_at) VALUES ('RELEASE', ?, ?, ?, 'Twistlock spreader dilepas, pekerjaan selesai. Unit Standby.', 0, NOW())");
                $stmtLog->execute([$eq_id, $op_name, $cur_c]);

                get_live_twin_state($pdo, true);

                $alat_alert = [
                    'type' => 'success',
                    'title' => "Spreader Unit $eq_id Telah Dilepas",
                    'msg' => "Unit <strong>$eq_id</strong> kini berstatus <strong>Standby</strong> dan siap menerima penugasan job order berikutnya."
                ];
            }
        } catch (Exception $e) {
            $alat_alert = [
                'type' => 'error',
                'title' => 'Gagal Memproses Aksi Alat',
                'msg' => $e->getMessage()
            ];
        }
    }
}

// Ambil data armada alat berat dari database
$equipment_list = [];
try {
    $stmt = $pdo->query("SELECT * FROM equipment ORDER BY id ASC");
    $equipment_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $equipment_list = [];
}

// Map armada untuk akses cepat
$eq_map = [];
foreach ($equipment_list as $eq_item) {
    $eq_map[$eq_item['equipment_id']] = $eq_item;
}

// Ambil riwayat kejadian pemindahan yard (yard_events)
$recent_events = [];
try {
    $stmt2 = $pdo->query("SELECT * FROM yard_events ORDER BY id DESC LIMIT 15");
    $recent_events = $stmt2->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $recent_events = [];
}

// Ambil daftar kontainer aktif di lapangan untuk modal penugasan
$active_containers = [];
try {
    $stmtC = $pdo->query("SELECT container_number, iso_code, size_type, status, block, bay, `row`, tier, cargo_type FROM containers WHERE status IN ('in_yard', 'in_transit') ORDER BY block ASC, bay ASC, container_number ASC");
    $active_containers = $stmtC->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $active_containers = [];
}

$total_units = count($equipment_list);
$operating_units = 0;
$idle_units = 0;
$cnt_rs = 0; $cnt_rtg = 0; $cnt_fl = 0; $cnt_tt = 0;
foreach ($equipment_list as $eq) {
    if ($eq['status'] === 'operating' || $eq['status'] === 'carrying') $operating_units++;
    else $idle_units++;
    $et = strtoupper($eq['equipment_type'] ?? '');
    if (strpos($et, 'STACKER') !== false) $cnt_rs++;
    elseif (strpos($et, 'RTG') !== false) $cnt_rtg++;
    elseif (strpos($et, 'FORKLIFT') !== false) $cnt_fl++;
    elseif (strpos($et, 'TRACTOR') !== false) $cnt_tt++;
}
?>

<!-- Alert Feedback Pasca Aksi Dispatcher Alat Berat -->
<?php if ($alat_alert): ?>
<div class="mb-4 p-4 rounded-xl border flex items-start space-x-3 animate-fadeIn <?= $alat_alert['type'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-rose-50 border-rose-200 text-rose-900' ?>">
    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 <?= $alat_alert['type'] === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>">
        <i class="fa-solid <?= $alat_alert['type'] === 'success' ? 'fa-check' : 'fa-circle-exclamation' ?>"></i>
    </div>
    <div class="flex-1 min-w-0">
        <h4 class="text-sm font-bold"><?= $alat_alert['title'] ?></h4>
        <p class="text-xs mt-0.5"><?= $alat_alert['msg'] ?></p>
    </div>
    <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>
<?php endif; ?>

<div class="space-y-4">

    <!-- Header & Subtitle (Executive Compact) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 bg-white px-3.5 py-2.5 rounded-xl border border-slate-200/80 shadow-2xs">
        <div class="flex items-center space-x-2.5">
            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-orange-500 to-orange-600 text-white flex items-center justify-center text-xs shadow-xs flex-shrink-0">
                <i class="fa-solid fa-satellite-dish"></i>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h2 class="text-[13px] font-extrabold text-slate-900 leading-tight">Lokasi &amp; Status Alat Berat (Reach Stacker &amp; RTG)</h2>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 mr-1 rounded-full bg-emerald-500 animate-pulse"></span> GPS Aktif
                    </span>
                </div>
                <div class="text-[9px] font-bold text-orange-600 tracking-wider uppercase mt-0.5">Telemetri Lapangan • Dispatch Spreader • Utilisasi Alat</div>
            </div>
        </div>
        <div class="flex items-center space-x-1.5 flex-shrink-0 flex-wrap">
            <button onclick="openDispatchModal()" class="px-2.5 py-1 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white text-[11px] font-bold rounded-lg shadow-xs transition flex items-center">
                <i class="fa-solid fa-paper-plane mr-1 text-[10px]"></i> + Dispatch Job
            </button>
            <a href="dashboard.php?page=simulator" class="px-2.5 py-1 bg-[#0170b9] hover:bg-blue-700 text-white text-[11px] font-bold rounded-lg shadow-2xs transition flex items-center">
                <i class="fa-solid fa-cube mr-1 text-[10px] text-amber-400"></i> Simulasi 3D
            </a>
            <button onclick="refreshEquipment()" class="px-2 py-1 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-[11px] font-semibold rounded-lg shadow-2xs transition flex items-center">
                <i class="fa-solid fa-rotate mr-1 text-slate-500 text-[10px]" id="refreshIcon"></i> GPS
            </button>
            <a href="dashboard.php?page=kontainer" class="px-2.5 py-1 bg-white border border-slate-200 hover:bg-orange-50 text-orange-700 text-[11px] font-bold rounded-lg shadow-2xs transition flex items-center">
                <i class="fa-solid fa-boxes-stacked mr-1 text-[10px]"></i> Kontainer
            </a>
        </div>
    </div>

    <!-- 4 Telemetry Metric Cards (Micro Compact Grid) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
        <div class="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-2xs flex items-center justify-between hover:border-orange-300 transition">
            <div>
                <p class="text-[9px] font-bold uppercase text-slate-400 tracking-wider">Armada &amp; MHE Aktif</p>
                <h3 class="text-[13.5px] font-extrabold text-slate-900 tracking-tight leading-tight mt-0.5"><?= $total_units ?> <span class="text-[10px] font-medium text-slate-400">Unit Terpetakan</span></h3>
                <div class="text-[9px] text-blue-600 font-semibold font-mono"><?= $cnt_rs ?> RS &bull; <?= $cnt_rtg ?> RTG &bull; <?= $cnt_fl ?> FL &bull; <?= $cnt_tt ?> Shuttle</div>
            </div>
            <div class="w-7 h-7 rounded-lg bg-blue-50 text-[#0170b9] flex items-center justify-center text-xs">
                <i class="fa-solid fa-dolly"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-2xs flex items-center justify-between hover:border-purple-300 transition">
            <div>
                <p class="text-[9px] font-bold uppercase text-slate-400 tracking-wider">Status Operasional</p>
                <div class="flex items-center space-x-1 mt-0.5">
                    <span class="text-[9px] font-bold text-purple-700 bg-purple-50 px-1 py-0.2 rounded"><?= $operating_units ?> Bekerja</span>
                    <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-1 py-0.2 rounded"><?= $idle_units ?> Standby</span>
                </div>
                <p class="text-[9px] text-slate-400 font-medium mt-0.5">Semua Unit Sehat</p>
            </div>
            <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs">
                <i class="fa-solid fa-gears"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-2xs flex items-center justify-between hover:border-indigo-300 transition">
            <div>
                <p class="text-[9px] font-bold uppercase text-slate-400 tracking-wider">Rata-Rata Utilisasi</p>
                <h3 class="text-[13.5px] font-extrabold text-indigo-600 tracking-tight leading-tight mt-0.5">82.5%</h3>
                <div class="text-[9px] text-emerald-600 font-medium"><i class="fa-solid fa-arrow-trend-up mr-1"></i>Sangat Efisien</div>
            </div>
            <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-2xs flex items-center justify-between hover:border-emerald-300 transition">
            <div>
                <p class="text-[9px] font-bold uppercase text-slate-400 tracking-wider">Produktivitas Gerakan</p>
                <h3 class="text-[13.5px] font-extrabold text-emerald-600 tracking-tight leading-tight mt-0.5">24.2 <span class="text-[10px] font-medium text-slate-400">M/Jam</span></h3>
                <div class="text-[9px] text-blue-600 font-medium">Siklus Lift-Off/On</div>
            </div>
            <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                <i class="fa-solid fa-gauge-high"></i>
            </div>
        </div>
    </div>

    <!-- PETA SKEMATIK PEMETAAN ZONA SELURUH ARMADA & MHE (15 UNIT) -->
    <div class="bg-white rounded-xl p-3 shadow-2xs border border-slate-200/80">
        <div class="flex flex-wrap items-center justify-between gap-2 pb-2 mb-2.5 border-b border-slate-200/80">
            <div>
                <h3 class="font-extrabold text-slate-900 text-xs flex items-center">
                    <i class="fa-solid fa-map-location-dot mr-1.5 text-orange-600"></i>Pemetaan Zona Live Armada &amp; MHE Terminal (<?= $total_units ?> Unit)
                </h3>
                <p class="text-[9px] text-slate-400 mt-0.5">Sinkron dengan aset 3D Simulator: Reach Stacker, E-RTG Yard/Rail, Forklift Gudang CFS, &amp; Shuttle Tractor.</p>
            </div>
            <div class="flex items-center space-x-2.5 text-[10px]">
                <span class="flex items-center text-slate-600 font-medium"><span class="w-2 h-2 rounded-full bg-emerald-500 mr-1"></span>Standby</span>
                <span class="flex items-center text-slate-600 font-medium"><span class="w-2 h-2 rounded-full bg-orange-500 mr-1 animate-ping"></span>Beroperasi</span>
            </div>
        </div>

        <?php
        $zoneGroups = [
            ['title' => 'Yard Utara • Blok A &amp; B', 'badge' => 'text-blue-700 bg-blue-50 border-blue-200', 'ids' => ['RTG-02', 'RS-01', 'RTG-03', 'RS-02']],
            ['title' => 'Yard Selatan • Blok C &amp; D', 'badge' => 'text-emerald-700 bg-emerald-50 border-emerald-200', 'ids' => ['RTG-04', 'RS-04', 'RTG-05']],
            ['title' => 'Reefer, Blok E &amp; Behandle', 'badge' => 'text-cyan-700 bg-cyan-50 border-cyan-200', 'ids' => ['RS-03', 'FL-02', 'CDP-TT-02']],
            ['title' => 'Gudang CFS (Rampa D1–D5)', 'badge' => 'text-amber-700 bg-amber-50 border-amber-200', 'ids' => ['FL-01', 'FL-03', 'FL-04']],
            ['title' => 'Rail Siding KA Pasoso', 'badge' => 'text-indigo-700 bg-indigo-50 border-indigo-200', 'ids' => ['RTG-01', 'CDP-TT-01']],
        ];
        ?>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-2.5">
            <?php foreach ($zoneGroups as $zg): ?>
                <div class="bg-slate-50/70 border border-slate-200 rounded-xl p-2.5 space-y-1.5">
                    <div class="flex items-center justify-between pb-1 border-b border-slate-200/80">
                        <span class="text-[10px] font-extrabold text-slate-800"><?= $zg['title'] ?></span>
                        <span class="text-[9px] font-bold px-1.5 py-0.2 rounded border <?= $zg['badge'] ?>"><?= count($zg['ids']) ?> Unit</span>
                    </div>
                    <?php foreach ($zg['ids'] as $zid):
                        $u = $eq_map[$zid] ?? null;
                        if (!$u) continue;
                        $uOp = ($u['status'] === 'operating' || $u['status'] === 'carrying');
                    ?>
                        <button type="button" onclick="openDispatchModal('<?= htmlspecialchars($zid) ?>')" class="w-full text-left bg-white hover:border-orange-300 border <?= $uOp ? 'border-orange-300' : 'border-slate-200' ?> rounded-lg p-2 transition flex items-center justify-between gap-2 cursor-pointer">
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono font-extrabold text-[10.5px] text-slate-900"><?= htmlspecialchars($zid) ?></span>
                                    <span class="w-1.5 h-1.5 rounded-full <?= $uOp ? 'bg-orange-500 animate-ping' : 'bg-emerald-500' ?>"></span>
                                </div>
                                <div class="text-[9.5px] text-slate-500 truncate"><?= htmlspecialchars($u['operator_name']) ?> &bull; Blok <?= htmlspecialchars($u['last_block'] ?? '-') ?></div>
                            </div>
                            <span class="text-[9px] font-mono font-bold px-1.5 py-0.5 rounded <?= $uOp ? 'bg-orange-50 text-orange-700' : 'bg-slate-100 text-slate-600' ?> shrink-0">
                                <?= $uOp && !empty($u['current_container']) ? htmlspecialchars($u['current_container']) : ((int)$u['fuel_percent'] . '%') ?>
                            </span>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- MASTER INVENTARIS & TELEMETRI SELURUH ASET ALAT BERAT / MHE PERUSAHAAN -->
    <div class="bg-white rounded-xl shadow-2xs border border-slate-200/80 overflow-hidden">
        <div class="px-3.5 py-2.5 border-b border-slate-200/80 flex flex-wrap items-center justify-between gap-2 bg-slate-50/70">
            <div class="flex flex-wrap items-center gap-1.5">
                <h3 class="font-extrabold text-slate-900 text-xs flex items-center mr-2">
                    <i class="fa-solid fa-warehouse mr-1.5 text-[#0170b9]"></i>Inventaris Master Alat &amp; MHE
                </h3>
                <button type="button" onclick="filterAlatFleet('ALL')" id="fltAlat_ALL" class="alat-flt-btn px-2 py-1 rounded-md text-[10px] font-bold bg-slate-900 text-white cursor-pointer">Semua (<?= $total_units ?>)</button>
                <button type="button" onclick="filterAlatFleet('REACH_STACKER')" id="fltAlat_REACH_STACKER" class="alat-flt-btn px-2 py-1 rounded-md text-[10px] font-bold bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 cursor-pointer">Reach Stacker (<?= $cnt_rs ?>)</button>
                <button type="button" onclick="filterAlatFleet('RTG_CRANE')" id="fltAlat_RTG_CRANE" class="alat-flt-btn px-2 py-1 rounded-md text-[10px] font-bold bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 cursor-pointer">E-RTG Crane (<?= $cnt_rtg ?>)</button>
                <button type="button" onclick="filterAlatFleet('ELECTRIC_FORKLIFT')" id="fltAlat_ELECTRIC_FORKLIFT" class="alat-flt-btn px-2 py-1 rounded-md text-[10px] font-bold bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 cursor-pointer">Forklift CFS (<?= $cnt_fl ?>)</button>
                <button type="button" onclick="filterAlatFleet('TERMINAL_TRACTOR')" id="fltAlat_TERMINAL_TRACTOR" class="alat-flt-btn px-2 py-1 rounded-md text-[10px] font-bold bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 cursor-pointer">Shuttle Tractor (<?= $cnt_tt ?>)</button>
            </div>
            <div class="flex items-center gap-2">
                <span id="alatInventoryCount" class="text-[10px] font-bold text-slate-600 bg-white px-2 py-0.5 rounded border border-slate-200">
                    <?= $total_units ?> Unit
                </span>
                <select id="alatInventoryLimit" data-limit-target="#equipmentInventoryTable" data-limit-callback="filterAlatFleetCurrent" data-count-target="#alatInventoryCount" data-count-unit="Unit" class="px-2 py-1 bg-white border border-slate-200 rounded-lg text-[10.5px] font-bold text-slate-700 cursor-pointer">
                    <option value="10">Tampil: 10</option>
                    <option value="25" selected>Tampil: 25</option>
                    <option value="50">Tampil: 50</option>
                    <option value="all">Semua</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[780px]" id="equipmentInventoryTable">
                <thead>
                    <tr class="bg-slate-50/90 text-slate-500 text-[10px] uppercase font-bold tracking-wider border-b border-slate-200/80">
                        <th class="py-2 px-2.5 text-center w-10">No</th>
                        <th class="py-2 px-3">Kode Unit &amp; Kategori</th>
                        <th class="py-2 px-3">Merk / Tipe Aset</th>
                        <th class="py-2 px-3">Operator / PIC</th>
                        <th class="py-2 px-3">Zona &amp; Koordinat (RTK)</th>
                        <th class="py-2 px-3">Status &amp; Muatan</th>
                        <th class="py-2 px-3 text-center">Daya / Jam</th>
                        <th class="py-2 px-3 text-right">Aksi VMT</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-slate-100">
                    <?php foreach ($equipment_list as $eq_idx => $eq):
                        $is_operating = ($eq['status'] === 'operating' || $eq['status'] === 'carrying');
                        $eqType = strtoupper($eq['equipment_type'] ?? 'REACH_STACKER');
                        $typeLabel = $eqType === 'RTG_CRANE' ? 'E-RTG Crane' : ($eqType === 'ELECTRIC_FORKLIFT' ? 'Forklift Elektrik' : ($eqType === 'TERMINAL_TRACTOR' ? 'Shuttle Tractor' : 'Reach Stacker'));
                        $typeBadge = $eqType === 'RTG_CRANE' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : ($eqType === 'ELECTRIC_FORKLIFT' ? 'bg-amber-50 text-amber-800 border-amber-200' : ($eqType === 'TERMINAL_TRACTOR' ? 'bg-cyan-50 text-cyan-700 border-cyan-200' : 'bg-blue-50 text-[#0170b9] border-blue-200'));
                    ?>
                    <tr class="alat-inv-row hover:bg-slate-50 transition" data-eq-type="<?= htmlspecialchars($eqType) ?>">
                        <td class="py-2 px-2.5 text-center font-mono text-slate-400" data-row-num><?= $eq_idx + 1 ?></td>
                        <td class="py-2 px-3 whitespace-nowrap">
                            <span class="font-mono font-extrabold text-slate-900 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 mr-1.5"><?= htmlspecialchars($eq['equipment_id']) ?></span>
                            <span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold border <?= $typeBadge ?>"><?= $typeLabel ?></span>
                        </td>
                        <td class="py-2 px-3 font-medium text-slate-700"><?= htmlspecialchars($eq['brand_model'] ?? '-') ?></td>
                        <td class="py-2 px-3 whitespace-nowrap">
                            <span class="font-semibold text-slate-900"><?= htmlspecialchars($eq['operator_name']) ?></span>
                            <span class="text-[10px] text-slate-400 font-mono ml-1"><?= htmlspecialchars($eq['operator_id'] ?? '') ?></span>
                        </td>
                        <td class="py-2 px-3 whitespace-nowrap">
                            <span class="font-bold text-slate-800">Blok <?= htmlspecialchars($eq['last_block'] ?? '-') ?></span>
                            <span class="text-[10px] font-mono text-slate-400 ml-1">(<?= round((float)$eq['gps_x']) ?>, <?= round((float)$eq['gps_y']) ?>)</span>
                        </td>
                        <td class="py-2 px-3 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold <?= $is_operating ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' ?>">
                                <?= $is_operating ? 'Operating' : 'Standby' ?>
                            </span>
                            <?php if (!empty($eq['current_container'])): ?>
                                <span class="ml-1 font-mono font-bold text-[10px] text-purple-700"><?= htmlspecialchars($eq['current_container']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="py-2 px-3 text-center whitespace-nowrap font-mono text-[11px] text-slate-700">
                            <strong><?= (int)$eq['fuel_percent'] ?>%</strong> <span class="text-slate-400">/ <?= $eq['hours_today'] ?>j</span>
                        </td>
                        <td class="py-2 px-3 text-right whitespace-nowrap space-x-1">
                            <button type="button" onclick="openDispatchModal('<?= htmlspecialchars($eq['equipment_id']) ?>')" class="px-2.5 py-1 bg-slate-100 hover:bg-orange-500 text-slate-700 hover:text-white rounded text-[10.5px] font-bold transition cursor-pointer">
                                <i class="fa-solid fa-paper-plane mr-1 text-[9px]"></i>Tugaskan
                            </button>
                            <?php if ($is_operating): ?>
                            <form method="POST" class="inline" onsubmit="return confirm('Lepas twistlock/beban unit <?= $eq['equipment_id'] ?> dan ubah status ke Standby?')">
                                <input type="hidden" name="alat_action" value="release_spreader">
                                <input type="hidden" name="equipment_id" value="<?= htmlspecialchars($eq['equipment_id']) ?>">
                                <button type="submit" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded text-[10.5px] font-bold transition cursor-pointer" title="Lepas Muatan & Standby">
                                    <i class="fa-solid fa-lock-open"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- CLEAN & COMPACT AUDIT LOG TABEL PEMINDAHAN YARD -->
    <div class="bg-white rounded-xl shadow-2xs border border-slate-200/80 overflow-hidden">
        <div class="px-3.5 py-2 border-b border-slate-200/80 flex items-center justify-between bg-slate-50/70">
            <div class="flex items-center space-x-2">
                <h3 class="font-extrabold text-slate-900 text-xs flex items-center">
                    <i class="fa-solid fa-clock-rotate-left mr-1.5 text-orange-600"></i>Riwayat Pekerjaan Pemindahan Kontainer
                </h3>
                <span class="text-[9px] font-bold text-slate-400">• Satu baris ringkas per tiket pemindahan</span>
            </div>
            <div class="flex items-center gap-2">
                <span id="alatEventCount" class="text-[10px] font-bold text-slate-600 bg-white px-2 py-0.5 rounded border border-slate-200">
                    <?= count($recent_events) ?> Catatan Terakhir
                </span>
                <select id="alatLimitSelect" data-limit-target="#eventTable" data-count-target="#alatEventCount" data-count-unit="Catatan" class="px-2 py-0.5 bg-slate-50 border border-slate-200 rounded-lg text-[10.5px] font-bold text-slate-700">
                    <option value="10" selected>Tampil: 10</option>
                    <option value="25">Tampil: 25</option>
                    <option value="50">Tampil: 50</option>
                    <option value="all">Semua</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[720px]" id="eventTable">
                <thead>
                    <tr class="bg-slate-50/90 text-slate-500 text-[10px] uppercase font-bold tracking-wider border-b border-slate-200/80">
                        <th class="py-2 px-2.5 text-center w-10">No</th>
                        <th class="py-2 px-3">Alat & Operator</th>
                        <th class="py-2 px-3">Tipe Tugas</th>
                        <th class="py-2 px-3">Nomor Kontainer</th>
                        <th class="py-2 px-3">Perpindahan Koordinat</th>
                        <th class="py-2 px-3">Waktu Eksekusi</th>
                        <th class="py-2 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-slate-100">
                    <?php foreach ($recent_events as $ev_idx => $ev): ?>
                        <?php
                        $badge_ev = 'bg-blue-50 text-blue-700 border-blue-200';
                        if ($ev['event_type'] === 'RELOCATION') $badge_ev = 'bg-purple-50 text-purple-700 border-purple-200';
                        elseif ($ev['event_type'] === 'RAIL_LOAD') $badge_ev = 'bg-indigo-50 text-indigo-700 border-indigo-200';
                        elseif ($ev['event_type'] === 'LIFT_OFF') $badge_ev = 'bg-emerald-50 text-emerald-700 border-emerald-200';

                        $from = $ev['from_block'] ? "Blok {$ev['from_block']}" . ($ev['from_bay'] ? "-{$ev['from_bay']}" : "") : 'Gate / Truk';
                        $to = $ev['to_block'] ? "Blok {$ev['to_block']}" . ($ev['to_bay'] ? "-{$ev['to_bay']}" : "") : 'Lokasi Baru';

                        $json_event = htmlspecialchars(json_encode([
                            'id' => $ev['id'],
                            'event_type' => $ev['event_type'],
                            'equipment_id' => $ev['equipment_id'],
                            'operator_name' => $ev['operator_name'],
                            'container_number' => $ev['container_number'],
                            'from_pos' => $from,
                            'to_pos' => $to,
                            'notes' => $ev['notes'] ?? 'Pemindahan rutin sesuai instruksi YMS',
                            'billable_amount' => 'Rp ' . number_format($ev['billable_amount'] ?? 150000, 0, ',', '.'),
                            'created_at' => date('d M Y, H:i \W\I\B', strtotime($ev['created_at']))
                        ]), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr class="hover:bg-orange-50/30 transition cursor-pointer group"
                            onclick='showJobModal(<?= $json_event ?>)'>
                            
                            <td class="py-1.5 px-2.5 text-center font-mono text-slate-400" data-row-num><?= $ev_idx + 1 ?></td>

                            <!-- Alat & Operator -->
                            <td class="py-1.5 px-3 whitespace-nowrap">
                                <div class="flex items-center space-x-1.5">
                                    <span class="font-mono font-bold text-slate-900 bg-slate-100 px-1.5 py-0.5 rounded text-[11px]">
                                        <?= htmlspecialchars($ev['equipment_id']) ?>
                                    </span>
                                    <span class="text-slate-700 font-medium text-xs">
                                        <?= htmlspecialchars($ev['operator_name']) ?>
                                    </span>
                                </div>
                            </td>

                            <!-- Tipe Tugas -->
                            <td class="py-1.5 px-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase <?= $badge_ev ?>">
                                    <?= htmlspecialchars($ev['event_type']) ?>
                                </span>
                            </td>

                            <!-- Nomor Kontainer -->
                            <td class="py-1.5 px-3 whitespace-nowrap">
                                <span class="font-mono font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded border border-orange-200 text-[11px]">
                                    <?= htmlspecialchars($ev['container_number']) ?>
                                </span>
                            </td>

                            <!-- Perpindahan Koordinat -->
                            <td class="py-1.5 px-3 whitespace-nowrap">
                                <div class="flex items-center space-x-1.5 font-medium text-xs">
                                    <span class="text-slate-600"><?= $from ?></span>
                                    <i class="fa-solid fa-arrow-right text-[9px] text-slate-400"></i>
                                    <span class="text-emerald-700 font-bold"><?= $to ?></span>
                                </div>
                            </td>

                            <!-- Waktu Eksekusi -->
                            <td class="py-1.5 px-3 whitespace-nowrap text-slate-500 font-mono text-[11px]">
                                <?= date('d M Y, H:i', strtotime($ev['created_at'])) ?>
                            </td>

                            <!-- Aksi -->
                            <td class="py-1.5 px-3 text-right whitespace-nowrap">
                                <?php
                                $jobData = [
                                    'id' => $ev['id'],
                                    'event_type' => $ev['event_type'],
                                    'equipment_id' => $ev['equipment_id'],
                                    'operator_name' => $ev['operator_name'],
                                    'container_number' => $ev['container_number'],
                                    'billable_amount' => 'Rp ' . number_format($ev['billable_amount'] ?? 0, 0, ',', '.'),
                                    'from_pos' => $from,
                                    'to_pos' => $to,
                                    'notes' => $ev['notes'] ?? 'Relokasi kontainer terminal YMS',
                                    'created_at' => date('d M Y, H:i', strtotime($ev['created_at'])) . ' WIB'
                                ];
                                ?>
                                <button onclick='showJobModal(<?= json_encode($jobData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' class="px-2 py-1 bg-slate-100 hover:bg-orange-500 text-slate-700 hover:text-white text-[11px] font-semibold rounded transition shadow-2xs inline-flex items-center space-x-1 cursor-pointer" title="Buka Detail Tiket Pekerjaan">
                                    <span>Detail Job</span>
                                    <i class="fa-solid fa-chevron-right text-[9px]"></i>
                                </button>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL INTERAKTIF: DETAIL TIKET PEKERJAAN ALAT BERAT (EQUIPMENT JOB MODAL) -->
<div id="jobModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden animate-fadeIn border border-gray-100">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-[#0170b9] to-blue-600 text-white p-5 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-xl border border-white/20">
                    <i class="fa-solid fa-dolly"></i>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-blue-200">Tiket Pekerjaan Lapangan YMS</span>
                    <h3 class="text-lg font-bold font-mono" id="j_title">JOB-RELOCATION-01</h3>
                </div>
            </div>
            <button onclick="closeJobModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4 text-xs">
            <div class="grid grid-cols-2 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
                <div>
                    <span class="text-gray-400 text-[10px] block uppercase font-semibold">Unit Alat Berat:</span>
                    <strong class="text-gray-900 text-sm font-mono" id="j_equipment">RS-02</strong>
                </div>
                <div>
                    <span class="text-gray-400 text-[10px] block uppercase font-semibold">Operator Bertugas:</span>
                    <strong class="text-gray-900 text-sm" id="j_operator">Agus Setiawan</strong>
                </div>
                <div>
                    <span class="text-gray-400 text-[10px] block uppercase font-semibold">Kontainer Dipindah:</span>
                    <strong class="text-[#0170b9] text-sm font-mono" id="j_container">MSKU9182374</strong>
                </div>
                <div>
                    <span class="text-gray-400 text-[10px] block uppercase font-semibold">Biaya Operasional:</span>
                    <strong class="text-emerald-700 text-sm font-mono" id="j_fee">Rp 150.000</strong>
                </div>
            </div>

            <!-- Perpindahan Koordinat -->
            <div class="bg-blue-50/60 p-4 rounded-xl border border-blue-100">
                <span class="text-[10px] uppercase font-bold text-blue-800 block mb-2">Perpindahan Koordinat 3D Lapangan:</span>
                <div class="flex items-center justify-between text-xs">
                    <div class="p-2.5 bg-white rounded-lg border border-blue-200 text-center flex-1 mr-2">
                        <span class="text-gray-400 text-[10px] block">Posisi Asal:</span>
                        <strong class="text-gray-800 font-mono" id="j_from">Blok A-01</strong>
                    </div>
                    <i class="fa-solid fa-arrow-right text-blue-600 text-base"></i>
                    <div class="p-2.5 bg-white rounded-lg border border-blue-200 text-center flex-1 ml-2">
                        <span class="text-gray-400 text-[10px] block">Posisi Tujuan:</span>
                        <strong class="text-emerald-700 font-mono" id="j_to">Blok B-08</strong>
                    </div>
                </div>
            </div>

            <!-- Catatan & Waktu -->
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 space-y-1">
                <span class="text-gray-400 text-[10px] block uppercase font-semibold">Catatan Lapangan Operator:</span>
                <p class="text-gray-700 font-medium" id="j_notes">Shifting kontainer untuk persiapan loading truk.</p>
                <div class="text-[10px] text-gray-400 pt-1 font-mono" id="j_time">2026-09-21 14:15 WIB</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-end">
            <button onclick="closeJobModal()" class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 text-xs font-bold rounded-xl transition">
                Tutup
            </button>
        </div>

    </div>
</div>

<!-- MODAL INTERAKTIF: PENUGASAN JOB ORDER VMT (DISPATCH MODAL) -->
<div id="dispatchModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden animate-fadeIn border border-gray-100">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-[#0170b9] to-blue-600 text-white p-5 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-xl border border-white/20">
                    <i class="fa-solid fa-satellite-dish"></i>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-blue-200">Terminal Dispatcher Command</span>
                    <h3 class="text-base font-bold">Terbitkan Job Order Baru (VMT)</h3>
                </div>
            </div>
            <button onclick="closeDispatchModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <!-- Form Dispatch -->
        <form method="POST" class="p-6 space-y-4 text-xs">
            <input type="hidden" name="alat_action" value="dispatch_job">

            <!-- Pilih Unit Alat Berat -->
            <div>
                <label class="block text-gray-700 font-bold mb-1 uppercase text-[10px] tracking-wider">Unit Alat Berat & Operator Bertugas</label>
                <select name="equipment_id" id="disp_equipment_id" required class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:outline-hidden focus:ring-2 focus:ring-[#0170b9]">
                    <?php foreach ($equipment_list as $eq_opt): ?>
                        <option value="<?= htmlspecialchars($eq_opt['equipment_id']) ?>">
                            <?= htmlspecialchars($eq_opt['equipment_id']) ?> [<?= htmlspecialchars($eq_opt['equipment_type'] ?? 'MHE') ?>] — <?= htmlspecialchars($eq_opt['operator_name']) ?> (<?= ucfirst($eq_opt['status']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Pilih Kontainer Aktif -->
            <div>
                <label class="block text-gray-700 font-bold mb-1 uppercase text-[10px] tracking-wider">Kontainer Lapangan yang Ditugaskan</label>
                <select name="container_number" required class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-mono font-semibold focus:outline-hidden focus:ring-2 focus:ring-[#0170b9]">
                    <?php if (empty($active_containers)): ?>
                        <option value="">Tidak ada kontainer aktif di yard</option>
                    <?php else: ?>
                        <?php foreach ($active_containers as $act_c): ?>
                            <option value="<?= htmlspecialchars($act_c['container_number']) ?>">
                                <?= htmlspecialchars($act_c['container_number']) ?> (<?= htmlspecialchars($act_c['size_type'] ?? '40HC') ?> - Blok <?= htmlspecialchars($act_c['block'] ?? '-') ?> Bay <?= htmlspecialchars($act_c['bay'] ?? '-') ?> - <?= htmlspecialchars(strtoupper($act_c['cargo_type'] ?? 'General')) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Jenis Tugas Operasional -->
            <div>
                <label class="block text-gray-700 font-bold mb-1 uppercase text-[10px] tracking-wider">Jenis Tugas Lapangan</label>
                <select name="job_type" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:outline-hidden focus:ring-2 focus:ring-[#0170b9]">
                    <option value="RELOCATION">RELOCATION — Relokasi & Penataan Stacking Yard</option>
                    <option value="RAIL_LOAD">RAIL_LOAD — Pemuatan Kontainer ke Kereta Api Logistik</option>
                    <option value="LIFT_OFF">LIFT_OFF — Penurunan Box dari Truk Masuk</option>
                    <option value="SHIFTING">SHIFTING — Pergeseran Sementara Antar Tier</option>
                    <option value="INSPECTION">INSPECTION — Pindah ke Jalur Behandle / X-Ray</option>
                </select>
            </div>

            <!-- Koordinat Sasaran 3D -->
            <div>
                <label class="block text-gray-700 font-bold mb-1 uppercase text-[10px] tracking-wider">Koordinat Sasaran 3D (Blok / Bay / Row / Tier)</label>
                <div class="grid grid-cols-4 gap-2">
                    <div>
                        <span class="text-[10px] text-gray-400 block mb-0.5">Blok</span>
                        <select name="target_block" class="w-full px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold text-center">
                            <option value="A">A (Ekspor)</option>
                            <option value="B">B (Impor)</option>
                            <option value="C">C (Domestik)</option>
                            <option value="D">D (Buffer)</option>
                            <option value="E">E (Behandle/DG)</option>
                            <option value="REEFER">REEFER</option>
                            <option value="CFS">CFS (Gudang)</option>
                            <option value="RAIL">RAIL (Siding KA)</option>
                        </select>
                    </div>
                    <div>
                        <span class="text-[10px] text-gray-400 block mb-0.5">Bay</span>
                        <input type="number" name="target_bay" min="1" max="12" value="2" class="w-full px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold text-center">
                    </div>
                    <div>
                        <span class="text-[10px] text-gray-400 block mb-0.5">Row</span>
                        <input type="number" name="target_row" min="1" max="6" value="1" class="w-full px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold text-center">
                    </div>
                    <div>
                        <span class="text-[10px] text-gray-400 block mb-0.5">Tier</span>
                        <input type="number" name="target_tier" min="1" max="4" value="1" class="w-full px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold text-center">
                    </div>
                </div>
            </div>

            <!-- Catatan Lapangan -->
            <div>
                <label class="block text-gray-700 font-bold mb-1 uppercase text-[10px] tracking-wider">Catatan Khusus Operator VMT</label>
                <input type="text" name="operator_notes" placeholder="Misal: Prioritas muat KA rute Cikarang-Surabaya atau relokasi stack..." class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-hidden focus:ring-2 focus:ring-[#0170b9]">
            </div>

            <!-- Footer Action -->
            <div class="pt-3 border-t border-gray-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeDispatchModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-[#0170b9] hover:bg-[#0170b9] text-white text-xs font-bold rounded-xl transition shadow-sm flex items-center space-x-1.5">
                    <i class="fa-solid fa-paper-plane text-[10px]"></i>
                    <span>Kirim ke VMT Terminal</span>
                </button>
            </div>
        </form>

    </div>
</div>

<script>
let currentAlatCategory = 'ALL';

function filterAlatFleet(cat) {
    currentAlatCategory = cat || 'ALL';
    document.querySelectorAll('.alat-flt-btn').forEach(btn => {
        const active = btn.id === ('fltAlat_' + currentAlatCategory);
        btn.className = active
            ? 'alat-flt-btn px-2 py-1 rounded-md text-[10px] font-bold bg-slate-900 text-white cursor-pointer'
            : 'alat-flt-btn px-2 py-1 rounded-md text-[10px] font-bold bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 cursor-pointer';
    });
    filterAlatFleetCurrent();
}

function filterAlatFleetCurrent() {
    document.querySelectorAll('#equipmentInventoryTable tbody tr.alat-inv-row').forEach(row => {
        const eqType = row.dataset.eqType || '';
        const match = (currentAlatCategory === 'ALL') || (eqType === currentAlatCategory);
        row.dataset.filteredOut = match ? '0' : '1';
        row.style.display = match ? '' : 'none';
    });
    if (window.CIDPTableTools) {
        window.CIDPTableTools.apply('#equipmentInventoryTable');
    }
}

function refreshEquipment() {
    const icon = document.getElementById('refreshIcon');
    icon.classList.add('fa-spin');
    setTimeout(() => {
        icon.classList.remove('fa-spin');
    }, 800);
}

function showJobModal(data) {
    document.getElementById('j_title').innerText = 'JOB-' + data.event_type + '-#' + data.id;
    document.getElementById('j_equipment').innerText = data.equipment_id;
    document.getElementById('j_operator').innerText = data.operator_name;
    document.getElementById('j_container').innerText = data.container_number;
    document.getElementById('j_fee').innerText = data.billable_amount;
    document.getElementById('j_from').innerText = data.from_pos;
    document.getElementById('j_to').innerText = data.to_pos;
    document.getElementById('j_notes').innerText = data.notes;
    document.getElementById('j_time').innerText = data.created_at;

    document.getElementById('jobModal').classList.remove('hidden');
}

function closeJobModal() {
    document.getElementById('jobModal').classList.add('hidden');
}

function openDispatchModal(eqId = '') {
    if (eqId) {
        const select = document.getElementById('disp_equipment_id');
        if (select) select.value = eqId;
    }
    document.getElementById('dispatchModal').classList.remove('hidden');
}

function closeDispatchModal() {
    document.getElementById('dispatchModal').classList.add('hidden');
}

window.addEventListener('click', function(e) {
    const jModal = document.getElementById('jobModal');
    if (e.target === jModal) {
        closeJobModal();
    }
    const dModal = document.getElementById('dispatchModal');
    if (e.target === dModal) {
        closeDispatchModal();
    }
});
</script>
