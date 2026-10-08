<?php
// =============================================================================
// MODUL: LOKASI & STATUS ALAT BERAT (EQUIPMENT FLEET & LIVE POSITIONING)
// File: pages/alat.php
// CIDP Yard Management System - PT Multi Terminal Indonesia / ITL Trisakti
// =============================================================================

require_once __DIR__ . '/../connection.php';

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
                $stmtC = $pdo->prepare("SELECT block, bay, row, tier FROM containers WHERE container_number = ? LIMIT 1");
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

                // 1. Update status armada alat
                $stmtUpEq = $pdo->prepare("UPDATE equipment SET status = 'operating', current_container = ?, last_block = ?, last_updated = NOW() WHERE equipment_id = ?");
                $stmtUpEq->execute([$ctr_num, $t_block, $eq_id]);

                // 2. Update koordinat fisik kontainer di database
                $stmtUpCtr = $pdo->prepare("UPDATE containers SET block = ?, bay = ?, row = ?, tier = ?, status = 'stacked', last_moved = NOW() WHERE container_number = ?");
                $stmtUpCtr->execute([$t_block, $t_bay, $t_row, $t_tier, $ctr_num]);

                // 3. Catat audit pemindahan ke yard_events
                $stmtLog = $pdo->prepare("INSERT INTO yard_events (event_type, equipment_id, operator_name, container_number, from_block, from_bay, to_block, to_bay, notes, billable_amount, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmtLog->execute([$job_type, $eq_id, $op_name, $ctr_num, $from_b, $from_by, $t_block, $t_bay, $notes, $fee]);

                $alat_alert = [
                    'type' => 'success',
                    'title' => "Job Order VMT Terkirim ke $eq_id",
                    'msg' => "Unit <strong>$eq_id</strong> (Operator: $op_name) ditugaskan untuk $job_type kontainer <strong>$ctr_num</strong> ke Blok $t_block (Bay $t_bay / Row $t_row / Tier $t_tier). Posisi fisik kontainer di database telah diperbarui."
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
    $stmtC = $pdo->query("SELECT container_number, iso_code, size, status, block, bay, row, tier, commodity FROM containers WHERE status IN ('stacked', 'gate_in') ORDER BY block ASC, bay ASC, container_number ASC");
    $active_containers = $stmtC->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $active_containers = [];
}

$total_units = count($equipment_list);
$operating_units = 0;
$idle_units = 0;
foreach ($equipment_list as $eq) {
    if ($eq['status'] === 'operating' || $eq['status'] === 'carrying') $operating_units++;
    else $idle_units++;
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

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white px-4 py-3 rounded-xl shadow-2xs border border-gray-100">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#002f5e] via-[#0170b9] to-orange-500 text-white flex items-center justify-center text-lg shadow-xs flex-shrink-0">
                <i class="fa-solid fa-satellite-dish"></i>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h2 class="text-base font-bold text-gray-900 leading-tight">Lokasi &amp; Status Alat Berat (Reach Stacker &amp; RTG)</h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 mr-1 rounded-full bg-emerald-500 animate-pulse"></span> GPS Aktif
                    </span>
                </div>
            </div>
        </div>
        <div class="flex items-center space-x-2 flex-shrink-0 flex-wrap gap-y-2">
            <button onclick="openDispatchModal()" class="px-3 py-1.5 bg-[#0170b9] hover:bg-[#002f5e] text-white text-xs font-bold rounded-lg shadow-2xs transition flex items-center">
                <i class="fa-solid fa-paper-plane mr-1.5"></i> + Dispatch Job Order
            </button>
            <a href="dashboard.php?page=simulator" class="px-3 py-1.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-gray-950 text-xs font-bold rounded-lg shadow-2xs transition flex items-center">
                <i class="fa-solid fa-cube mr-1.5"></i> Simulasi 3D
            </a>
            <button onclick="refreshEquipment()" class="px-3 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg shadow-2xs transition flex items-center">
                <i class="fa-solid fa-rotate mr-1.5 text-gray-500" id="refreshIcon"></i> GPS
            </button>
            <a href="dashboard.php?page=kontainer" class="px-3 py-1.5 bg-[#004b87] hover:bg-[#002f5e] text-white text-xs font-bold rounded-lg shadow-2xs transition flex items-center">
                <i class="fa-solid fa-boxes-stacked mr-1.5"></i> Kontainer <i class="fa-solid fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <!-- 4 Telemetry Metric Cards (Compact Grid) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3">
        <div class="bg-white p-3 sm:p-3.5 rounded-xl shadow-2xs border border-gray-200/80 hover:border-blue-300 transition-all flex items-center justify-between">
            <div>
                <p class="text-[10px] font-semibold uppercase text-gray-400 tracking-wider">Armada Aktif</p>
                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mt-0.5"><?= $total_units ?> <span class="text-[10px] font-normal text-gray-400">Unit</span></h3>
                <div class="mt-0.5 text-[10px] text-blue-600 font-medium">3 RS + 1 RTG</div>
            </div>
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#0170b9] flex items-center justify-center text-xs shadow-inner">
                <i class="fa-solid fa-dolly"></i>
            </div>
        </div>

        <div class="bg-white p-3 sm:p-3.5 rounded-xl shadow-2xs border border-gray-200/80 hover:border-purple-300 transition-all flex items-center justify-between">
            <div>
                <p class="text-[10px] font-semibold uppercase text-gray-400 tracking-wider">Status Operasional</p>
                <div class="flex items-center space-x-1.5 mt-0.5">
                    <span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.2 rounded"><?= $operating_units ?> Bekerja</span>
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded"><?= $idle_units ?> Standby</span>
                </div>
                <p class="mt-0.5 text-[10px] text-gray-400">Semua Unit Sehat</p>
            </div>
            <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs shadow-inner">
                <i class="fa-solid fa-gears"></i>
            </div>
        </div>

        <div class="bg-white p-3 sm:p-3.5 rounded-xl shadow-2xs border border-gray-200/80 hover:border-indigo-300 transition-all flex items-center justify-between">
            <div>
                <p class="text-[10px] font-semibold uppercase text-gray-400 tracking-wider">Rata-Rata Utilisasi</p>
                <h3 class="text-xl sm:text-2xl font-bold text-indigo-600 mt-0.5">82.5%</h3>
                <div class="mt-0.5 text-[10px] text-emerald-600 font-medium"><i class="fa-solid fa-arrow-trend-up mr-1"></i>Sangat Efisien</div>
            </div>
            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs shadow-inner">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>

        <div class="bg-white p-3 sm:p-3.5 rounded-xl shadow-2xs border border-gray-200/80 hover:border-emerald-300 transition-all flex items-center justify-between">
            <div>
                <p class="text-[10px] font-semibold uppercase text-gray-400 tracking-wider">Produktivitas Gerakan</p>
                <h3 class="text-xl sm:text-2xl font-bold text-emerald-600 mt-0.5">24.2 <span class="text-[10px] font-normal text-gray-400">M/Jam</span></h3>
                <div class="mt-0.5 text-[10px] text-blue-600 font-medium">Siklus Cepat Lift-Off/On</div>
            </div>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs shadow-inner">
                <i class="fa-solid fa-gauge-high"></i>
            </div>
        </div>
    </div>

    <!-- PETA SKEMATIK POSISI ALAT BERAT DI TERMINAL (RADAR GRID) -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
            <div>
                <h3 class="font-bold text-gray-900 text-base flex items-center">
                    <i class="fa-solid fa-map-location-dot mr-2 text-[#0170b9]"></i>Peta Skematik Posisi Live Alat Berat Terminal
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Penempatan posisi fisik alat berat pada masing-masing zona yard.</p>
            </div>
            <div class="flex items-center space-x-3 text-xs">
                <span class="flex items-center text-gray-600 font-medium"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-1.5"></span>Standby</span>
                <span class="flex items-center text-gray-600 font-medium"><span class="w-2.5 h-2.5 rounded-full bg-purple-600 mr-1.5 animate-ping"></span>Sedang Memindahkan</span>
            </div>
        </div>

        <div class="bg-slate-900 rounded-2xl p-5 text-white relative overflow-hidden border border-slate-800">
            <?php
            $rs01 = $eq_map['RS-01'] ?? ['operator_name' => 'Budi Santoso', 'status' => 'idle', 'current_container' => '', 'last_block' => 'A', 'fuel_percent' => 85];
            $rs02 = $eq_map['RS-02'] ?? ['operator_name' => 'Agus Setiawan', 'status' => 'idle', 'current_container' => '', 'last_block' => 'B', 'fuel_percent' => 87];
            $rs03 = $eq_map['RS-03'] ?? ['operator_name' => 'Rudi Hermawan', 'status' => 'idle', 'current_container' => '', 'last_block' => 'R', 'fuel_percent' => 78];
            $rtg01 = $eq_map['RTG-01'] ?? ['operator_name' => 'Joko Susilo', 'status' => 'idle', 'current_container' => '', 'last_block' => 'S', 'fuel_percent' => 95];

            $rs01_op = ($rs01['status'] === 'operating' || $rs01['status'] === 'carrying');
            $rs02_op = ($rs02['status'] === 'operating' || $rs02['status'] === 'carrying');
            $rs03_op = ($rs03['status'] === 'operating' || $rs03['status'] === 'carrying');
            $rtg01_op = ($rtg01['status'] === 'operating' || $rtg01['status'] === 'carrying');
            ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                
                <!-- Blok A (RS-01) -->
                <div class="bg-slate-800/60 border <?= $rs01_op ? 'border-purple-500/60' : 'border-slate-700/80' ?> rounded-xl p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-blue-400">BLOK A (Dry 40ft & Shifting)</span>
                        <span class="w-2.5 h-2.5 rounded-full <?= $rs01_op ? 'bg-purple-500 animate-ping' : 'bg-emerald-500' ?>"></span>
                    </div>
                    <div class="bg-slate-900/90 p-3 rounded-xl border <?= $rs01_op ? 'border-purple-500/40' : 'border-slate-700' ?> flex items-center space-x-3">
                        <span class="font-mono font-bold <?= $rs01_op ? 'text-purple-300 bg-purple-950' : 'text-emerald-400 bg-emerald-950' ?> text-sm px-2 py-1 rounded">RS-01</span>
                        <div class="text-xs">
                            <strong class="text-slate-200 block"><?= htmlspecialchars($rs01['operator_name']) ?></strong>
                            <?php if ($rs01_op && !empty($rs01['current_container'])): ?>
                                <span class="text-purple-300 font-mono text-[10px] font-bold block">Angkut: <?= htmlspecialchars($rs01['current_container']) ?></span>
                            <?php else: ?>
                                <span class="text-slate-400 text-[10px]">Standby di Blok <?= htmlspecialchars($rs01['last_block'] ?? 'A') ?> (BBM <?= $rs01['fuel_percent'] ?>%)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Blok B (RS-02) -->
                <div class="bg-slate-800/60 border <?= $rs02_op ? 'border-purple-500/60' : 'border-slate-700/80' ?> rounded-xl p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-purple-400">BLOK B (Dry 20ft & Shifting)</span>
                        <span class="w-2.5 h-2.5 rounded-full <?= $rs02_op ? 'bg-purple-500 animate-ping' : 'bg-emerald-500' ?>"></span>
                    </div>
                    <div class="bg-slate-900/90 p-3 rounded-xl border <?= $rs02_op ? 'border-purple-500/40' : 'border-slate-700' ?> flex items-center space-x-3">
                        <span class="font-mono font-bold <?= $rs02_op ? 'text-purple-300 bg-purple-950' : 'text-emerald-400 bg-emerald-950' ?> text-sm px-2 py-1 rounded">RS-02</span>
                        <div class="text-xs">
                            <strong class="text-slate-100 block"><?= htmlspecialchars($rs02['operator_name']) ?></strong>
                            <?php if ($rs02_op && !empty($rs02['current_container'])): ?>
                                <span class="text-purple-300 font-mono text-[10px] font-bold block">Angkut: <?= htmlspecialchars($rs02['current_container']) ?></span>
                            <?php else: ?>
                                <span class="text-slate-400 text-[10px]">Standby di Blok <?= htmlspecialchars($rs02['last_block'] ?? 'B') ?> (BBM <?= $rs02['fuel_percent'] ?>%)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Reefer (RS-03) -->
                <div class="bg-slate-800/60 border <?= $rs03_op ? 'border-purple-500/60' : 'border-slate-700/80' ?> rounded-xl p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-cyan-400">ZONA DERMAGA REEFER &amp; DG</span>
                        <span class="w-2.5 h-2.5 rounded-full <?= $rs03_op ? 'bg-purple-500 animate-ping' : 'bg-cyan-400' ?>"></span>
                    </div>
                    <div class="bg-slate-900/90 p-3 rounded-xl border <?= $rs03_op ? 'border-purple-500/40' : 'border-slate-700' ?> flex items-center space-x-3">
                        <span class="font-mono font-bold <?= $rs03_op ? 'text-purple-300 bg-purple-950' : 'text-cyan-400 bg-cyan-950' ?> text-sm px-2 py-1 rounded">RS-03</span>
                        <div class="text-xs">
                            <strong class="text-slate-200 block"><?= htmlspecialchars($rs03['operator_name']) ?></strong>
                            <?php if ($rs03_op && !empty($rs03['current_container'])): ?>
                                <span class="text-purple-300 font-mono text-[10px] font-bold block">Angkut: <?= htmlspecialchars($rs03['current_container']) ?></span>
                            <?php else: ?>
                                <span class="text-slate-400 text-[10px]">Siaga Steker Reefer (BBM <?= $rs03['fuel_percent'] ?>%)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Rail Siding (RTG-01) -->
            <div class="bg-slate-800/80 border <?= $rtg01_op ? 'border-amber-500/50' : 'border-blue-500/30' ?> rounded-xl p-3 flex items-center justify-between">
                <div class="flex items-center space-x-3 text-xs">
                    <span class="font-mono font-bold text-amber-400 bg-amber-950 px-2 py-1 rounded">RTG-01</span>
                    <div>
                        <strong class="text-slate-200"><?= htmlspecialchars($rtg01['operator_name']) ?> (Konecranes RTG Electric)</strong>
                        <?php if ($rtg01_op && !empty($rtg01['current_container'])): ?>
                            <span class="text-amber-300 block text-[10px] font-mono">Memuat KA Logistik: <strong><?= htmlspecialchars($rtg01['current_container']) ?></strong> • Daya Grid 100%</span>
                        <?php else: ?>
                            <span class="text-slate-400 block text-[10px]">Jalur Rel Siding KA Logistik (Siaga di Rel Siding • Daya Grid 100%)</span>
                        <?php endif; ?>
                    </div>
                </div>
                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold <?= $rtg01_op ? 'bg-amber-900 text-amber-200' : 'bg-emerald-900 text-emerald-200' ?>">
                    <?= $rtg01_op ? 'AKTIF MEMUAT KA' : 'STANDBY SIDING' ?>
                </span>
            </div>
        </div>
    </div>

    <!-- KARTU RINGKAS TIAP UNIT ALAT BERAT (4 CARDS) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php foreach ($equipment_list as $eq): ?>
            <?php
            $is_operating = ($eq['status'] === 'operating' || $eq['status'] === 'carrying');
            ?>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100">
                        <span class="font-mono font-bold text-gray-900 text-sm bg-slate-100 px-2.5 py-0.5 rounded-lg border border-slate-200">
                            <?= htmlspecialchars($eq['equipment_id']) ?>
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $is_operating ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' ?>">
                            <?= $is_operating ? 'Operating' : 'Standby' ?>
                        </span>
                    </div>
                    <div class="space-y-1.5 text-xs text-gray-600 mb-3">
                        <div class="font-semibold text-gray-800 flex items-center justify-between">
                            <span><?= htmlspecialchars($eq['operator_name']) ?></span>
                            <span class="text-[10px] text-gray-400 font-mono"><?= htmlspecialchars($eq['operator_id'] ?? '') ?></span>
                        </div>
                        <div class="text-[11px] text-gray-500">Blok Terakhir: <strong><?= htmlspecialchars($eq['last_block'] ?? '-') ?></strong></div>
                        <?php if (!empty($eq['current_container'])): ?>
                            <div class="text-purple-700 font-mono font-bold text-[11px] bg-purple-50 px-2 py-1 rounded border border-purple-100">
                                <i class="fa-solid fa-lock mr-1"></i><?= htmlspecialchars($eq['current_container']) ?>
                            </div>
                        <?php else: ?>
                            <div class="text-gray-400 italic text-[11px] bg-slate-50 px-2 py-1 rounded border border-slate-100">
                                <i class="fa-solid fa-check mr-1 text-emerald-500"></i>Spreader Siap
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="pt-2 border-t border-gray-100 space-y-2">
                    <div class="flex items-center justify-between text-[11px] text-gray-500">
                        <span>Power: <strong><?= $eq['fuel_percent'] ?>%</strong></span>
                        <span>Jam: <strong><?= $eq['hours_today'] ?>j</strong></span>
                    </div>
                    <div class="flex items-center gap-1.5 pt-1">
                        <button onclick="openDispatchModal('<?= htmlspecialchars($eq['equipment_id']) ?>')" class="flex-1 py-1.5 bg-slate-100 hover:bg-[#0170b9] text-slate-700 hover:text-white rounded-lg text-xs font-bold transition flex items-center justify-center space-x-1">
                            <i class="fa-solid fa-paper-plane text-[10px]"></i>
                            <span>Tugaskan</span>
                        </button>
                        <?php if ($is_operating): ?>
                        <form method="POST" class="inline" onsubmit="return confirm('Lepas twistlock spreader unit <?= $eq['equipment_id'] ?> dan ubah status ke Standby?')">
                            <input type="hidden" name="alat_action" value="release_spreader">
                            <input type="hidden" name="equipment_id" value="<?= htmlspecialchars($eq['equipment_id']) ?>">
                            <button type="submit" class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-xs font-bold transition" title="Lepas Spreader & Standby">
                                <i class="fa-solid fa-lock-open"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- CLEAN & SPACIOUS AUDIT LOG TABEL PEMINDAHAN YARD -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-bold text-gray-900 text-base flex items-center">
                    <i class="fa-solid fa-clock-rotate-left mr-2 text-[#0170b9]"></i>Riwayat Pekerjaan Pemindahan Kontainer
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Tampilan ringkas satu baris. Klik baris untuk membuka rincian tiket pemindahan.</p>
            </div>
            <span class="text-xs font-semibold text-gray-500 bg-white px-3 py-1 rounded-lg border border-gray-200">
                <?= count($recent_events) ?> Catatan Terakhir
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[720px]" id="eventTable">
                <thead>
                    <tr class="bg-gray-50/80 text-gray-600 text-xs uppercase tracking-wider border-b border-gray-200">
                        <th class="py-3.5 px-4 font-semibold">Alat & Operator</th>
                        <th class="py-3.5 px-4 font-semibold">Tipe Tugas</th>
                        <th class="py-3.5 px-4 font-semibold">Nomor Kontainer</th>
                        <th class="py-3.5 px-4 font-semibold">Perpindahan Koordinat</th>
                        <th class="py-3.5 px-4 font-semibold">Waktu Eksekusi</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-gray-100">
                    <?php foreach ($recent_events as $ev): ?>
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
                        <tr class="hover:bg-blue-50/40 transition cursor-pointer group"
                            onclick='showJobModal(<?= $json_event ?>)'>
                            
                            <!-- Alat & Operator -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <span class="font-mono font-bold text-gray-900 bg-slate-100 px-2 py-0.5 rounded text-xs">
                                        <?= htmlspecialchars($ev['equipment_id']) ?>
                                    </span>
                                    <span class="text-gray-700 font-medium text-xs">
                                        <?= htmlspecialchars($ev['operator_name']) ?>
                                    </span>
                                </div>
                            </td>

                            <!-- Tipe Tugas -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border uppercase <?= $badge_ev ?>">
                                    <?= htmlspecialchars($ev['event_type']) ?>
                                </span>
                            </td>

                            <!-- Nomor Kontainer -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="font-mono font-bold text-[#0170b9] text-xs">
                                    <?= htmlspecialchars($ev['container_number']) ?>
                                </span>
                            </td>

                            <!-- Perpindahan Koordinat -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <div class="flex items-center space-x-1.5 font-medium text-xs">
                                    <span class="text-gray-600"><?= $from ?></span>
                                    <i class="fa-solid fa-arrow-right text-[10px] text-gray-400"></i>
                                    <span class="text-emerald-700 font-bold"><?= $to ?></span>
                                </div>
                            </td>

                            <!-- Waktu Eksekusi -->
                            <td class="py-4 px-4 whitespace-nowrap text-gray-500 font-mono text-[11px]">
                                <?= date('d M Y, H:i', strtotime($ev['created_at'])) ?>
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-4 text-right whitespace-nowrap">
                                <button class="px-3 py-1.5 bg-slate-100 hover:bg-[#0170b9] text-slate-700 hover:text-white text-xs font-semibold rounded-lg transition shadow-2xs inline-flex items-center space-x-1.5">
                                    <span>Detail Job</span>
                                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
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
        <div class="bg-gradient-to-r from-[#002f5e] to-[#0170b9] text-white p-5 flex items-center justify-between">
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
            <button onclick="closeJobModal()" class="px-5 py-2 bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold rounded-xl transition">
                Tutup
            </button>
        </div>

    </div>
</div>

<!-- MODAL INTERAKTIF: PENUGASAN JOB ORDER VMT (DISPATCH MODAL) -->
<div id="dispatchModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden animate-fadeIn border border-gray-100">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-[#002f5e] to-[#0170b9] text-white p-5 flex items-center justify-between">
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
                            <?= htmlspecialchars($eq_opt['equipment_id']) ?> — <?= htmlspecialchars($eq_opt['operator_name']) ?> (<?= ucfirst($eq_opt['status']) ?>)
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
                                <?= htmlspecialchars($act_c['container_number']) ?> (<?= $act_c['size'] ?>' - Blok <?= $act_c['block'] ?? '-' ?> Bay <?= $act_c['bay'] ?? '-' ?> - <?= htmlspecialchars($act_c['commodity'] ?? 'General Cargo') ?>)
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
                            <option value="A">A (40ft)</option>
                            <option value="B">B (20ft)</option>
                            <option value="C">C (Empty)</option>
                            <option value="D">D (Trans)</option>
                            <option value="E">E (Buffer)</option>
                            <option value="R">R (Reefer)</option>
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
                <button type="submit" class="px-5 py-2 bg-[#0170b9] hover:bg-[#002f5e] text-white text-xs font-bold rounded-xl transition shadow-sm flex items-center space-x-1.5">
                    <i class="fa-solid fa-paper-plane text-[10px]"></i>
                    <span>Kirim ke VMT Terminal</span>
                </button>
            </div>
        </form>

    </div>
</div>

<script>
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
