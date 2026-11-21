<?php
/**
 * =============================================================================
 * FILE: pages/integration.php
 * FUNGSI: Integrasi Data & EDI Terminal Operating System (TOS)
 *         Alur Gate-to-Yard, Antrean Error (DLQ), Token API & Odoo ERP Sync
 * =============================================================================
 */

require_once __DIR__ . '/../api/preadvice_helper.php';
require_once __DIR__ . '/../api/oauth_helper.php';
ensure_integration_schema($pdo);

$oauth_clients = oauth_registered_clients();

$bcp_state = cidp_get_bcp_state($pdo);
$init_stats = [
    'outside_scheduled'  => (int)$pdo->query("SELECT COUNT(*) FROM preadvice WHERE status='SCHEDULED'")->fetchColumn(),
    'arrived'            => (int)$pdo->query("SELECT COUNT(*) FROM preadvice WHERE status='ARRIVED'")->fetchColumn(),
    'containers_in_yard' => (int)$pdo->query("SELECT COUNT(*) FROM containers WHERE status='in_yard'")->fetchColumn(),
    'events_logged'      => (int)$pdo->query("SELECT COUNT(*) FROM yard_events")->fetchColumn(),
    'dlq_open'           => (int)$pdo->query("SELECT COUNT(*) FROM integration_dlq WHERE status='QUARANTINED'")->fetchColumn(),
    'dlq_buffered'       => (int)($bcp_state['buffered_count'] ?? 0),
];
?>

<div class="space-y-4">

    <!-- HEADER BAR RINGKAS ALA TERMINAL OPERATING SYSTEM -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center text-base shrink-0">
                <i class="fa-solid fa-diagram-project text-sky-400"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">Integrasi Data &amp; EDI</h1>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> ONLINE
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 font-medium">Gate OCR • CEISA Bea Cukai • Siding KA • Odoo ERP</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="openPreGateModal()" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-800 border border-slate-300 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-plus text-indigo-600"></i> Pre-Gate
            </button>
            <button type="button" onclick="runAutoCycleNext()" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-2xs transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-play"></i> Jalankan 1 Truk
            </button>
        </div>
    </div>

    <!-- VISUAL PIPELINE STATUS (ALUR DATA REAL-TIME TANPA BANYAK TULISAN) -->
    <div class="bg-white rounded-2xl p-3.5 border border-slate-200 shadow-2xs">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Node 1: Pre-Advice -->
            <div onclick="switchIntegrationTab('tab-simulator')" class="group p-3 rounded-xl bg-amber-50/60 border border-amber-200/80 hover:border-amber-400 transition cursor-pointer flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-amber-700">1. Pre-Advice</div>
                    <div class="flex items-baseline gap-1.5 mt-0.5">
                        <span id="kpiOutsideScheduled" class="text-2xl font-black text-slate-900"><?= $init_stats['outside_scheduled'] ?></span>
                        <span class="text-[11px] font-semibold text-slate-500">Truk di luar</span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-white text-amber-600 border border-amber-200 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>

            <!-- Node 2: Gate-In -->
            <div onclick="switchIntegrationTab('tab-simulator')" class="group p-3 rounded-xl bg-sky-50/60 border border-sky-200/80 hover:border-sky-400 transition cursor-pointer flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-sky-700">2. Masuk Gate</div>
                    <div class="flex items-baseline gap-1.5 mt-0.5">
                        <span id="kpiArrived" class="text-2xl font-black text-sky-700"><?= $init_stats['arrived'] ?></span>
                        <span class="text-[11px] font-semibold text-slate-500">Truk OCR</span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-white text-sky-600 border border-sky-200 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-archway"></i>
                </div>
            </div>

            <!-- Node 3: Yard & Log -->
            <div onclick="switchIntegrationTab('tab-simulator')" class="group p-3 rounded-xl bg-teal-50/60 border border-teal-200/80 hover:border-teal-400 transition cursor-pointer flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-teal-700">3. Yard Aktif</div>
                    <div class="flex items-baseline gap-2 mt-0.5">
                        <span id="kpiInYard" class="text-2xl font-black text-teal-700"><?= $init_stats['containers_in_yard'] ?></span>
                        <span class="text-[11px] font-semibold text-slate-500">Box</span>
                        <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded bg-white border border-teal-200 text-indigo-700">
                            <span id="kpiEventsLogged"><?= $init_stats['events_logged'] ?></span> Log
                        </span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-white text-teal-600 border border-teal-200 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>

            <!-- Node 4: Error Queue (DLQ) -->
            <div onclick="switchIntegrationTab('tab-dlq')" class="group p-3 rounded-xl <?= $init_stats['dlq_open'] > 0 ? 'bg-rose-50/70 border-rose-200 hover:border-rose-400' : 'bg-emerald-50/60 border-emerald-200 hover:border-emerald-400' ?> border transition cursor-pointer flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider <?= $init_stats['dlq_open'] > 0 ? 'text-rose-700' : 'text-emerald-700' ?>">4. Antrean Error</div>
                    <div class="flex items-baseline gap-1.5 mt-0.5">
                        <span id="kpiDlqOpen" class="text-2xl font-black <?= $init_stats['dlq_open'] > 0 ? 'text-rose-600' : 'text-emerald-600' ?>"><?= $init_stats['dlq_open'] ?></span>
                        <span class="text-[11px] font-semibold text-slate-500">Karantina DLQ</span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-white <?= $init_stats['dlq_open'] > 0 ? 'text-rose-600 border-rose-200' : 'text-emerald-600 border-emerald-200' ?> border flex items-center justify-center text-sm">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- NAVIGASI 3 TAB UTAMA + AKSI UTILITAS (SIMPEL ALA TOS PELABUHAN) -->
    <div class="bg-white rounded-xl p-1.5 border border-slate-200 shadow-2xs flex flex-wrap items-center justify-between gap-2">
        <div class="flex flex-wrap gap-1.5">
            <button type="button" onclick="switchIntegrationTab('tab-simulator')" id="btn-tab-simulator" class="int-tab-btn px-3.5 py-2 rounded-lg text-xs font-extrabold transition flex items-center gap-2 bg-slate-900 text-white shadow-2xs cursor-pointer">
                <i class="fa-solid fa-satellite-dish text-sky-400"></i>
                <span>Antrean &amp; Alur Gate</span>
            </button>
            <button type="button" onclick="switchIntegrationTab('tab-dlq')" id="btn-tab-dlq" class="int-tab-btn px-3.5 py-2 rounded-lg text-xs font-extrabold transition flex items-center gap-2 text-slate-600 hover:bg-slate-100 cursor-pointer">
                <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                <span>Antrean Error (DLQ)</span>
                <span id="tabDlqBadge" class="px-1.5 py-0.5 rounded-full text-[10px] bg-rose-100 text-rose-700 font-black"><?= $init_stats['dlq_open'] + $init_stats['dlq_buffered'] ?></span>
            </button>
            <button type="button" onclick="switchIntegrationTab('tab-connectors')" id="btn-tab-connectors" class="int-tab-btn px-3.5 py-2 rounded-lg text-xs font-extrabold transition flex items-center gap-2 text-slate-600 hover:bg-slate-100 cursor-pointer">
                <i class="fa-solid fa-plug-circle-check text-emerald-600"></i>
                <span>Koneksi Odoo &amp; API</span>
            </button>
        </div>
        <div class="flex items-center gap-1.5 pr-0.5">
            <button type="button" onclick="openHwModal()" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-microchip text-slate-500"></i> Perangkat (11)
            </button>
            <button type="button" onclick="resetDemoDatabase()" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer" title="Reset antrean ke awal">
                <i class="fa-solid fa-rotate-left"></i> Reset
            </button>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- TAB 1: ANTREAN & ALUR GATE (SIMPEL & VISUAL)                        -->
    <!-- =================================================================== -->
    <div id="tab-simulator" class="int-tab-pane space-y-4">

        <!-- BARIS ALUR 6 TAHAP + AKSI CEPAT ZONA -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-4 space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-extrabold text-slate-900">Alur Otomatis Gate → Yard</span>
                    <span class="text-[11px] text-slate-400">Klik berurutan atau jalankan otomatis</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <a href="dashboard.php?page=scanner" target="_blank" class="px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-[11px] font-bold transition flex items-center gap-1">
                        <i class="fa-solid fa-camera"></i> Kamera OCR
                    </a>
                    <button type="button" onclick="runAutoCycleNext()" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-bold transition flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-bolt"></i> Auto 6-Tahap
                    </button>
                </div>
            </div>

            <!-- 6-Stage Horizontal Stepper -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
                <button type="button" onclick="triggerStep(1)" class="p-2.5 rounded-xl border border-slate-200 hover:border-sky-400 hover:bg-sky-50/60 text-left transition cursor-pointer group">
                    <div class="flex items-center justify-between text-[10px] font-bold text-sky-600">
                        <span>TAHAP 1</span>
                        <i class="fa-solid fa-archway"></i>
                    </div>
                    <div class="text-xs font-extrabold text-slate-800 mt-1 group-hover:text-sky-700">Masuk Gate</div>
                </button>
                <button type="button" onclick="triggerStep(2)" class="p-2.5 rounded-xl border border-slate-200 hover:border-sky-400 hover:bg-sky-50/60 text-left transition cursor-pointer group">
                    <div class="flex items-center justify-between text-[10px] font-bold text-sky-600">
                        <span>TAHAP 2</span>
                        <i class="fa-solid fa-weight-scale"></i>
                    </div>
                    <div class="text-xs font-extrabold text-slate-800 mt-1 group-hover:text-sky-700">Timbang VGM</div>
                </button>
                <button type="button" onclick="triggerStep(3)" class="p-2.5 rounded-xl border border-slate-200 hover:border-teal-400 hover:bg-teal-50/60 text-left transition cursor-pointer group">
                    <div class="flex items-center justify-between text-[10px] font-bold text-teal-600">
                        <span>TAHAP 3</span>
                        <i class="fa-solid fa-dolly"></i>
                    </div>
                    <div class="text-xs font-extrabold text-slate-800 mt-1 group-hover:text-teal-700">Bongkar RS</div>
                </button>
                <button type="button" onclick="triggerStep(4)" class="p-2.5 rounded-xl border border-slate-200 hover:border-teal-400 hover:bg-teal-50/60 text-left transition cursor-pointer group">
                    <div class="flex items-center justify-between text-[10px] font-bold text-teal-600">
                        <span>TAHAP 4</span>
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div class="text-xs font-extrabold text-slate-800 mt-1 group-hover:text-teal-700">Susun Yard</div>
                </button>
                <button type="button" onclick="triggerStep(5)" class="p-2.5 rounded-xl border border-slate-200 hover:border-purple-400 hover:bg-purple-50/60 text-left transition cursor-pointer group">
                    <div class="flex items-center justify-between text-[10px] font-bold text-purple-600">
                        <span>TAHAP 5</span>
                        <i class="fa-solid fa-file-shield"></i>
                    </div>
                    <div class="text-xs font-extrabold text-slate-800 mt-1 group-hover:text-purple-700">Bea Cukai</div>
                </button>
                <button type="button" onclick="triggerStep(6)" class="p-2.5 rounded-xl border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50/60 text-left transition cursor-pointer group">
                    <div class="flex items-center justify-between text-[10px] font-bold text-emerald-600">
                        <span>TAHAP 6</span>
                        <i class="fa-solid fa-truck-fast"></i>
                    </div>
                    <div class="text-xs font-extrabold text-slate-800 mt-1 group-hover:text-emerald-700">Rilis Gate-Out</div>
                </button>
            </div>

            <!-- Baris Aktivitas Lapangan Cepat (Ringkas 1 Klik) -->
            <div class="pt-2.5 border-t border-slate-100 flex flex-wrap items-center gap-1.5">
                <input type="text" id="activityTargetContainer" placeholder="No. Box (opsional)" class="w-36 px-2.5 py-1.5 rounded-lg border border-slate-200 text-[11px] font-mono bg-slate-50 focus:bg-white focus:outline-none focus:border-sky-400">
                <button type="button" onclick="triggerActivity('relocate')" class="px-2.5 py-1.5 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-800 border border-teal-200 text-[11px] font-bold transition cursor-pointer flex items-center gap-1">
                    <i class="fa-solid fa-arrows-up-down-left-right"></i> Relokasi Slot
                </button>
                <button type="button" onclick="triggerActivity('cfs_cycle')" class="px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-[11px] font-bold transition cursor-pointer flex items-center gap-1">
                    <i class="fa-solid fa-barcode"></i> Stripping CFS
                </button>
                <button type="button" onclick="triggerActivity('cfs_dispatch')" class="px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-[11px] font-bold transition cursor-pointer flex items-center gap-1">
                    <i class="fa-solid fa-box-open"></i> Keluar CFS
                </button>
                <button type="button" onclick="triggerActivity('customs_inspect', '&result=ANOMALY')" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-200 text-[11px] font-bold transition cursor-pointer flex items-center gap-1">
                    <i class="fa-solid fa-x-ray"></i> Jalur Merah
                </button>
                <button type="button" onclick="triggerActivity('customs_release')" class="px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-[11px] font-bold transition cursor-pointer flex items-center gap-1">
                    <i class="fa-solid fa-check-double"></i> Rilis SPPB
                </button>
                <button type="button" onclick="triggerActivity('reefer_plug')" class="px-2.5 py-1.5 rounded-lg bg-cyan-50 hover:bg-cyan-100 text-cyan-800 border border-cyan-200 text-[11px] font-bold transition cursor-pointer flex items-center gap-1">
                    <i class="fa-solid fa-plug-circle-bolt"></i> Plug Reefer
                </button>
                <button type="button" onclick="triggerActivity('reefer_telemetry')" class="px-2.5 py-1.5 rounded-lg bg-cyan-50 hover:bg-cyan-100 text-cyan-800 border border-cyan-200 text-[11px] font-bold transition cursor-pointer flex items-center gap-1">
                    <i class="fa-solid fa-temperature-low"></i> Suhu Normal
                </button>
                <button type="button" onclick="triggerActivity('reefer_telemetry', '&fault=1')" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-[11px] font-bold transition cursor-pointer flex items-center gap-1">
                    <i class="fa-solid fa-bell"></i> Alarm Suhu
                </button>
                <button type="button" onclick="triggerActivity('rail_load')" class="px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-800 border border-indigo-200 text-[11px] font-bold transition cursor-pointer flex items-center gap-1">
                    <i class="fa-solid fa-train"></i> Muat Kereta
                </button>
                <button type="button" onclick="triggerActivity('gate_out')" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-bold transition cursor-pointer flex items-center gap-1">
                    <i class="fa-solid fa-right-from-bracket"></i> Gate-Out
                </button>
            </div>

            <!-- Baris Simulasi Trigger Anomali & Masalah Operasional Nyata (SOP Penolakan vs Penyelesaian) -->
            <div class="pt-2.5 border-t border-rose-100 bg-rose-50/40 -mx-4 -mb-4 px-4 py-3 rounded-b-2xl flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-1.5">
                    <span class="w-5 h-5 rounded-md bg-rose-600 text-white flex items-center justify-center text-[10px] shrink-0"><i class="fa-solid fa-triangle-exclamation"></i></span>
                    <span class="text-[11px] font-extrabold text-rose-900">Trigger Kasus Anomali Lapangan (SOP Tolak / Atasi):</span>
                </div>
                <div class="flex flex-wrap items-center gap-1.5">
                    <button type="button" onclick="triggerAnomalyScenario('doc_expired')" class="px-2.5 py-1 rounded-lg bg-white hover:bg-rose-100 text-rose-700 border border-rose-200 text-[10.5px] font-extrabold transition cursor-pointer flex items-center gap-1 shadow-2xs" title="Truk tiba dengan e-DO kadaluarsa & pajak PIB belum lunas">
                        <i class="fa-solid fa-file-circle-xmark"></i> 1. e-DO Expired
                    </button>
                    <button type="button" onclick="triggerAnomalyScenario('container_mismatch')" class="px-2.5 py-1 rounded-lg bg-white hover:bg-rose-100 text-rose-700 border border-rose-200 text-[10.5px] font-extrabold transition cursor-pointer flex items-center gap-1 shadow-2xs" title="Kamera OCR membaca nomor kontainer fisik berbeda dari manifest booking">
                        <i class="fa-solid fa-boxes-packing"></i> 2. Salah Kontainer (OCR ≠ B/L)
                    </button>
                    <button type="button" onclick="triggerAnomalyScenario('vgm_overweight')" class="px-2.5 py-1 rounded-lg bg-white hover:bg-amber-100 text-amber-800 border border-amber-300 text-[10.5px] font-extrabold transition cursor-pointer flex items-center gap-1 shadow-2xs" title="Timbangan 80T mendeteksi selisih berat SOLAS VGM > 5% (Overload 39,2 Ton)">
                        <i class="fa-solid fa-weight-hanging"></i> 3. Overload Timbangan VGM (+51%)
                    </button>
                    <button type="button" onclick="triggerAnomalyScenario('seal_tampered')" class="px-2.5 py-1 rounded-lg bg-white hover:bg-rose-100 text-rose-700 border border-rose-200 text-[10.5px] font-extrabold transition cursor-pointer flex items-center gap-1 shadow-2xs" title="Segel pelayaran putus / tidak cocok & dinding kontainer penyok">
                        <i class="fa-solid fa-link-slash"></i> 4. Segel Putus &amp; Cacat Fisik
                    </button>
                    <button type="button" onclick="triggerAnomalyScenario('customs_mismatch')" class="px-2.5 py-1 rounded-lg bg-white hover:bg-purple-100 text-purple-800 border border-purple-200 text-[10.5px] font-extrabold transition cursor-pointer flex items-center gap-1 shadow-2xs" title="Dokumen BC 2.3 / HS Code tidak cocok dengan Packing List CEISA 4.0">
                        <i class="fa-solid fa-building-shield"></i> 5. BC 2.3 Tidak Cocok (Jalur Merah)
                    </button>
                    <button type="button" onclick="triggerAnomalyScenario('gate_out_unpaid')" class="px-2.5 py-1 rounded-lg bg-white hover:bg-slate-200 text-slate-800 border border-slate-300 text-[10.5px] font-extrabold transition cursor-pointer flex items-center gap-1 shadow-2xs" title="Truk mencoba Gate-Out saat invoice belum lunas / belum SPPB">
                        <i class="fa-solid fa-ban"></i> 6. Gate-Out Ditolak (Belum Lunas)
                    </button>
                </div>
            </div>
        </div>

        <!-- PANEL RESOLUSI ANOMALI & KEPUTUSAN SOP (MUNCUL SAAT ADA INTERLOCK / MASALAH) -->
        <div id="anomalyActionCard" class="hidden bg-gradient-to-r from-rose-50 via-amber-50/70 to-white rounded-2xl border-2 border-rose-300 shadow-md p-4 space-y-3">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 border-b border-rose-200/80 pb-3">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center text-base shrink-0 shadow-xs animate-pulse">
                        <i class="fa-solid fa-hand"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-rose-600 text-white tracking-wider">INTERLOCK AKTIF — TERTAHAN</span>
                            <span id="anomBadgeCode" class="px-2 py-0.5 rounded-md text-[10px] font-mono font-extrabold bg-rose-100 text-rose-800 border border-rose-300">DOC_DO_EXPIRED</span>
                            <span id="anomBadgeAsset" class="text-xs font-mono font-extrabold text-slate-900 bg-white px-2 py-0.5 rounded border border-slate-300">B 1274 TRB • MAEU9180740</span>
                        </div>
                        <h3 id="anomTitleText" class="text-sm sm:text-base font-black text-rose-950 mt-1">Dokumen e-DO Kadaluarsa &amp; Billing PIB Belum Lunas</h3>
                        <p id="anomDescText" class="text-xs text-slate-700 mt-0.5 leading-relaxed">Masa berlaku Electronic Delivery Order telah habis &amp; palang Gate Inbound dikunci otomatis.</p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('anomalyActionCard').classList.add('hidden')" class="self-end lg:self-start text-slate-400 hover:text-slate-700 text-xs font-bold px-2 py-1 cursor-pointer">
                    <i class="fa-solid fa-xmark mr-1"></i>Tutup
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                <!-- Opsi 1: SOP Penolakan & Putar Balik (Turn-Back) -->
                <div class="bg-white rounded-xl p-3.5 border border-rose-200 flex flex-col justify-between gap-2.5 shadow-2xs">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase tracking-wider text-rose-700"><i class="fa-solid fa-ban mr-1"></i>OPSI A — SOP PENOLAKAN GATE (TURN-BACK)</span>
                            <span class="text-[10px] font-mono font-bold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded">Terbitkan BA Tolak</span>
                        </div>
                        <p id="anomSopRejectText" class="text-[11.5px] text-slate-700 mt-1.5 leading-snug">Tolak akses masuk terminal, kunci palang gate, dan arahkan truk putar balik keluar melalui Jalur Darurat Gate.</p>
                    </div>
                    <button type="button" onclick="executeAnomalyDecision('reject')" class="w-full py-2 px-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                        <i class="fa-solid fa-truck-arrow-right"></i>
                        <span>Tolak &amp; Putar Balik (Cetak Berita Acara Penolakan)</span>
                    </button>
                </div>

                <!-- Opsi 2: SOP Penyelesaian / Override / Koreksi Dokumen -->
                <div class="bg-white rounded-xl p-3.5 border border-emerald-200 flex flex-col justify-between gap-2.5 shadow-2xs">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700"><i class="fa-solid fa-screwdriver-wrench mr-1"></i>OPSI B — SOP PENYELESAIAN / KOREKSI &amp; OVERRIDE</span>
                            <span class="text-[10px] font-mono font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">Klinik Dokumen / VGM</span>
                        </div>
                        <p id="anomSopResolveText" class="text-[11.5px] text-slate-700 mt-1.5 leading-snug">Verifikasi dokumen pengganti / amandemen VGM / pasang segel baru (Reseal), buka interlock, dan lanjutkan proses truk.</p>
                    </div>
                    <button type="button" onclick="executeAnomalyDecision('resolve')" class="w-full py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Atasi Masalah Sesuai SOP &amp; Lanjutkan Operasi</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- KARTU BUKTI BERITA ACARA PENOLAKAN / PENYELESAIAN SOP (MUNCUL SETELAH KEPUTUSAN) -->
        <div id="baSlipCard" class="hidden bg-white rounded-2xl border-2 border-slate-800 shadow-md p-4 space-y-3 text-xs">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 pb-2.5">
                <div class="flex items-center gap-2.5">
                    <span id="baSlipIcon" class="w-8 h-8 rounded-xl bg-slate-900 text-white flex items-center justify-center text-sm"><i class="fa-solid fa-file-signature"></i></span>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-500">BERITA ACARA OPERASIONAL GATE &amp; TOS</span>
                            <span id="baSlipNumber" class="font-mono font-black text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">BA-TOLAK/CIDP/20261118/001</span>
                        </div>
                        <h4 id="baSlipDecision" class="text-sm font-black text-slate-900 mt-0.5">DITOLAK &amp; PUTAR BALIK</h4>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.print()" class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-print"></i> Cetak Slip BA
                    </button>
                    <button type="button" onclick="document.getElementById('baSlipCard').classList.add('hidden')" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 bg-slate-50 p-3 rounded-xl border border-slate-200">
                <div><span class="text-[10px] text-slate-400 block">Plat Truk &amp; Pre-Gate</span><strong id="baSlipTruck" class="font-mono text-slate-900">-</strong></div>
                <div><span class="text-[10px] text-slate-400 block">Nomor Kontainer</span><strong id="baSlipContainer" class="font-mono text-indigo-700">-</strong></div>
                <div><span class="text-[10px] text-slate-400 block">Jenis Masalah / Anomali</span><strong id="baSlipAnomaly" class="text-rose-700">-</strong></div>
                <div><span class="text-[10px] text-slate-400 block">Waktu &amp; Otorisasi</span><strong id="baSlipTime" class="text-slate-800">-</strong></div>
            </div>
            <div class="p-2.5 rounded-xl bg-amber-50/70 border border-amber-200 text-slate-800">
                <span class="font-extrabold text-amber-900">Tindakan SOP yang Dieksekusi: </span>
                <span id="baSlipAction">-</span>
            </div>
        </div>

        <!-- RINGKASAN AKTIVITAS TERKINI (STRIP HORIZONTAL 3-KOLOM PENUH) -->
        <div id="intSplitRight" class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-3.5 space-y-2.5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-extrabold text-slate-900">Aktivitas Sistem Terbaru</span>
                    <span id="consoleStatusBadge" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600">STANDBY</span>
                </div>
                <div class="flex items-center gap-2 text-[11px]">
                    <span class="text-slate-500 font-semibold"><i class="fa-solid fa-shield-halved text-orange-500 mr-1"></i>Client: <code id="ovClient" class="text-slate-800 font-bold">-</code></span>
                    <span id="ovIntegrity" class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">READY</span>
                    <button type="button" onclick="document.getElementById('liveJsonDrawer').toggleAttribute('open')" class="text-indigo-600 hover:underline font-bold cursor-pointer ml-1">
                        <i class="fa-solid fa-code mr-0.5"></i>JSON
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-2.5 text-xs">
                <div class="p-2.5 rounded-xl bg-sky-50/70 border border-sky-200/80 flex items-start gap-2.5">
                    <span class="w-6 h-6 rounded-lg bg-sky-600 text-white flex items-center justify-center shrink-0 text-[11px]"><i class="fa-solid fa-truck"></i></span>
                    <div class="min-w-0">
                        <div class="text-[10px] font-extrabold uppercase text-sky-800">Pergerakan Fisik</div>
                        <div id="tfPhysical" class="text-slate-700 text-[11px] mt-0.5 leading-snug">Siap memproses event gate atau yard.</div>
                    </div>
                </div>

                <div class="p-2.5 rounded-xl bg-indigo-50/70 border border-indigo-200/80 flex items-start gap-2.5">
                    <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center shrink-0 text-[11px]"><i class="fa-solid fa-database"></i></span>
                    <div class="min-w-0">
                        <div class="text-[10px] font-extrabold uppercase text-indigo-800">Sinkron Data &amp; Dokumen</div>
                        <div id="tfInformation" class="text-slate-700 text-[11px] mt-0.5 leading-snug">Menunggu scan atau trigger operasi.</div>
                    </div>
                </div>

                <div class="p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-200/80 flex items-start gap-2.5">
                    <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0 text-[11px]"><i class="fa-solid fa-receipt"></i></span>
                    <div class="min-w-0">
                        <div class="text-[10px] font-extrabold uppercase text-emerald-800">Tagihan / Billing</div>
                        <div id="tfFinancial" class="text-slate-700 text-[11px] mt-0.5 leading-snug">Invoice otomatis dicatat sesuai layanan.</div>
                    </div>
                </div>
            </div>

            <details id="liveJsonDrawer" class="pt-1">
                <summary class="sr-only">Payload JSON</summary>
                <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1">
                    <span class="font-mono font-bold">Payload Respons</span>
                    <button type="button" onclick="copyConsoleJson()" class="text-indigo-600 hover:underline font-semibold cursor-pointer">Salin JSON</button>
                </div>
                <pre id="liveJsonConsole" class="bg-slate-900 text-emerald-300 font-mono text-[10px] p-2.5 rounded-xl overflow-x-auto max-h-40">{ "status": "READY" }</pre>
            </details>
        </div>

        <!-- TABEL ANTREAN PRE-ADVICE (LEBAR PENUH 100%, BEBAS TERPOTONG) -->
        <div id="intSplitLeft" class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-4 space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2.5 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">Antrean Truk &amp; Kontainer (Pre-Advice &amp; Skenario Anomali)</h2>
                    <p class="text-[11px] text-slate-500">Data antrean mencakup truk normal &amp; 6 skenario anomali nyata (e-DO expired, salah kontainer, overload VGM, segel rusak, BC 2.3 mismatch, &amp; OCR error).</p>
                </div>
                <div class="flex flex-wrap items-center gap-1.5">
                    <select id="preadviceFilterStatus" onchange="loadPreadviceTable()" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-bold bg-slate-50 cursor-pointer">
                        <option value="ALL">Semua Status (30)</option>
                        <option value="SCHEDULED" selected>Di Luar &amp; Hold Gate</option>
                        <option value="ANOMALY">⚠️ Hanya Data Anomali (6 Kasus)</option>
                        <option value="HOLD_ANOMALY">🚨 Sedang Ditahan (Hold Gate)</option>
                        <option value="REJECTED">🚫 Ditolak (Putar Balik)</option>
                        <option value="ARRIVED">✅ Sudah Masuk Gate</option>
                    </select>
                    <input type="text" id="preadviceSearch" oninput="renderPreadviceFiltered()" placeholder="Cari plat / box / anomali..." class="w-36 sm:w-44 px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:outline-none">
                    <select id="preadviceLimitSelect" onchange="renderPreadviceFiltered()" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-bold bg-slate-50 text-slate-700 cursor-pointer">
                        <option value="10" selected>Tampil: 10</option>
                        <option value="25">Tampil: 25</option>
                        <option value="50">Tampil: 50</option>
                        <option value="all">Semua</option>
                    </select>
                    <button type="button" onclick="exportPreadviceJson()" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer" title="Export JSON">
                        <i class="fa-solid fa-download"></i>
                    </button>
                    <button type="button" onclick="openImportJsonModal()" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer" title="Import JSON">
                        <i class="fa-solid fa-upload"></i>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs" id="preadviceTable">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 uppercase text-[10px] font-extrabold border-b border-slate-200">
                            <th class="py-2 px-2.5 text-center w-10">No</th>
                            <th class="py-2 px-3">Truk &amp; Kode</th>
                            <th class="py-2 px-3">Kontainer &amp; Pelayaran</th>
                            <th class="py-2 px-3">Kondisi Dokumen / Fisik</th>
                            <th class="py-2 px-3">Blok Tujuan</th>
                            <th class="py-2 px-3">Status</th>
                            <th class="py-2 px-3 text-right">Aksi &amp; SOP</th>
                        </tr>
                    </thead>
                    <tbody id="preadviceTableBody" class="divide-y divide-slate-100">
                        <tr data-empty-row><td colspan="7" class="py-6 text-center text-slate-400">Memuat antrean...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- TAB 2: ANTREAN ERROR (DLQ), SURGE BUFFER & BCP FAILOVER             -->
    <!-- =================================================================== -->
    <div id="tab-dlq" class="int-tab-pane hidden space-y-4">
        <!-- Ketahanan & BCP Failover Strip (Ringkas Port-TOS) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-slate-200 p-3 shadow-2xs flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-bold uppercase text-slate-400">Status Kontingensi (BCP)</div>
                    <div id="bcpModeBadge" class="text-xs font-black mt-0.5 <?= !empty($bcp_state['failover_active']) ? 'text-amber-600' : 'text-emerald-700' ?>">
                        <?= !empty($bcp_state['failover_active']) ? 'BCP EDGE BUFFER' : 'PRIMARY ONLINE' ?>
                    </div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 flex items-center justify-center text-xs"><i class="fa-solid fa-server"></i></div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-3 shadow-2xs flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-bold uppercase text-slate-400">Buffer Lonjakan (Surge)</div>
                    <div class="text-xs font-black mt-0.5 text-[#0170b9]"><span id="bcpBufferedCount"><?= $init_stats['dlq_buffered'] ?></span> Antrean Aman</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-200 text-[#0170b9] flex items-center justify-center text-xs"><i class="fa-solid fa-layer-group"></i></div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-3 shadow-2xs flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-bold uppercase text-slate-400">Kehilangan Data (RPO)</div>
                    <div class="text-xs font-black mt-0.5 text-emerald-700">0 Data (Zero Loss)</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-xs"><i class="fa-solid fa-database"></i></div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-3 shadow-2xs flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-bold uppercase text-slate-400">Waktu Pulih (RTO &amp; ATS)</div>
                    <div class="text-xs font-black mt-0.5 text-indigo-700">&lt; 10 Detik</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-600 flex items-center justify-center text-xs"><i class="fa-solid fa-bolt"></i></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-4 space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">Isolasi Eror (DLQ), Penanganan Lonjakan &amp; Pemulihan BCP</h2>
                    <p class="text-[11px] text-slate-500">Eror dikarantina &amp; lonjakan/gangguan ditampung ke Edge Buffer tanpa menghentikan gate.</p>
                </div>
                <div class="flex items-center gap-2">
                    <select id="dlqLimitSelect" data-limit-target="#dlqTable" data-observe-body="1" class="px-2.5 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs font-bold text-slate-700 cursor-pointer">
                        <option value="10" selected>Tampil: 10</option>
                        <option value="25">Tampil: 25</option>
                        <option value="50">Tampil: 50</option>
                        <option value="all">Semua</option>
                    </select>
                    <button type="button" onclick="loadDlqTable()" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer">
                        <i class="fa-solid fa-rotate mr-1"></i> Muat Ulang
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs" id="dlqTable">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 uppercase text-[10px] font-extrabold border-b border-slate-200">
                            <th class="py-2.5 px-2.5 text-center w-10">No</th>
                            <th class="py-2.5 px-3">Waktu</th>
                            <th class="py-2.5 px-3">Event</th>
                            <th class="py-2.5 px-3">Sumber</th>
                            <th class="py-2.5 px-3">Penyebab</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="dlqTableBody" class="divide-y divide-slate-100">
                        <tr data-empty-row><td colspan="7" class="py-6 text-center text-slate-400">Memuat data error...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Tombol Uji Keamanan, Surge & BCP Ringkas -->
            <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-[11px] font-bold text-slate-500 mr-1">Uji Proteksi &amp; BCP:</span>
                    <button type="button" onclick="runSecurityScenario('tamper')" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-[11px] font-bold cursor-pointer">Payload Palsu (401)</button>
                    <button type="button" onclick="runSecurityScenario('scope')" class="px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-[11px] font-bold cursor-pointer">Izin Ditolak (403)</button>
                    <button type="button" onclick="runSecurityScenario('unregistered')" class="px-2.5 py-1.5 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 text-[11px] font-bold cursor-pointer">Truk Tak Terdaftar (422)</button>
                    <button type="button" onclick="runSecurityScenario('idempotency')" class="px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-[11px] font-bold cursor-pointer">Cegah Kirim Ganda</button>
                    <button type="button" onclick="runSecurityScenario('surge')" class="px-2.5 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#0170b9] border border-blue-200 text-[11px] font-bold cursor-pointer"><i class="fa-solid fa-burst mr-1"></i>Uji Lonjakan (12 Req)</button>
                    <button type="button" onclick="toggleBcpMode()" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 text-[11px] font-bold cursor-pointer"><i class="fa-solid fa-shield-halved mr-1"></i>Toggle BCP Failover</button>
                    <button type="button" onclick="flushBcpBuffer()" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-bold cursor-pointer"><i class="fa-solid fa-play mr-1"></i>Pulihkan &amp; Flush Buffer</button>
                </div>
                <span id="secTestStatus" class="text-[11px] font-mono text-slate-500">Siap diuji</span>
            </div>

            <details class="pt-1">
                <summary class="text-[11px] font-bold text-slate-400 hover:text-slate-700 cursor-pointer select-none">Lihat Log Respons Pengujian (Teknis)</summary>
                <pre id="secTestOutput" class="mt-2 bg-slate-900 text-emerald-300 font-mono text-[10px] p-3 rounded-xl overflow-x-auto max-h-48">{ "ready": true }</pre>
            </details>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- TAB 3: KONEKSI EKSTERNAL (ODOO CLOUD & API ACCESS)                  -->
    <!-- =================================================================== -->
    <div id="tab-connectors" class="int-tab-pane hidden space-y-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            <!-- Kartu 1: Cloud ERP Odoo -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-extrabold text-slate-900">Cloud ERP Odoo</h2>
                            <p class="text-[11px] font-mono text-slate-400">conclusion-intermodal-dry-port.odoo.com</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">JSON-RPC 2.0</span>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="callOdooApi('test_connection')" class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold cursor-pointer">
                        <i class="fa-solid fa-plug mr-1"></i> Cek Koneksi
                    </button>
                    <button type="button" onclick="callOdooApi('get_invoices')" class="px-3 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold cursor-pointer">
                        <i class="fa-solid fa-file-invoice-dollar mr-1"></i> Tarik Invoice
                    </button>
                    <button type="button" onclick="callOdooApi('sync_all_invoices')" class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold cursor-pointer">
                        <i class="fa-solid fa-rotate mr-1"></i> Sync Semua Invoice
                    </button>
                </div>

                <pre id="odooJsonOutput" class="bg-slate-900 text-emerald-300 font-mono text-[11px] p-3 rounded-xl overflow-x-auto max-h-64">{ "status": "Klik tombol di atas untuk sinkronisasi Odoo." }</pre>
            </div>

            <!-- Kartu 2: Token OAuth 2.0 Perangkat -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-600 flex items-center justify-center">
                            <i class="fa-solid fa-key"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-extrabold text-slate-900">Akses Perangkat (OAuth 2.0)</h2>
                            <p class="text-[11px] text-slate-500">Manajemen izin akses sensor gerbang &amp; yard</p>
                        </div>
                    </div>
                    <span id="jwtExpiryBadge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Siap</span>
                </div>

                <div class="flex flex-wrap gap-2">
                    <select id="oauthClientSelect" class="flex-1 min-w-[200px] px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold bg-slate-50">
                        <?php foreach ($oauth_clients as $cid => $cl): ?>
                            <option value="<?= htmlspecialchars($cid) ?>"><?= htmlspecialchars($cl['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" onclick="requestOAuthToken()" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold cursor-pointer">
                        Buat Token Baru
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-[11px]" id="oauthClientsTable">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 uppercase text-[10px] font-extrabold border-b border-slate-200">
                                <th class="py-2 px-2 text-center w-8">No</th>
                                <th class="py-2 px-2.5">Perangkat</th>
                                <th class="py-2 px-2.5">Izin Akses</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php $o_idx = 0; foreach ($oauth_clients as $cid => $cl): $o_idx++; ?>
                                <tr>
                                    <td class="py-2 px-2 text-center font-mono text-slate-400"><?= $o_idx ?></td>
                                    <td class="py-2 px-2.5 font-bold text-slate-800"><?= htmlspecialchars($cl['name']) ?><div class="text-[10px] font-mono text-slate-400"><?= htmlspecialchars($cid) ?></div></td>
                                    <td class="py-2 px-2.5">
                                        <div class="flex flex-wrap gap-1">
                                            <?php foreach ($cl['scopes'] as $sc): ?>
                                                <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 font-mono text-[10px]"><?= htmlspecialchars($sc) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <details class="pt-1 border-t border-slate-100">
                    <summary class="text-[11px] font-bold text-slate-400 hover:text-slate-700 cursor-pointer select-none">Lihat Bearer Token &amp; Klaim JWT</summary>
                    <div class="mt-2 space-y-2">
                        <textarea id="jwtTokenOutput" rows="2" readonly class="w-full bg-slate-900 text-amber-300 font-mono text-[10px] p-2 rounded-lg"></textarea>
                        <pre id="jwtClaimsOutput" class="bg-slate-900 text-sky-200 font-mono text-[10px] p-2.5 rounded-lg overflow-x-auto max-h-36">{}</pre>
                    </div>
                </details>
            </div>
        </div>
    </div>

</div>

<!-- MODAL 1: DAFTAR PRE-GATE BARU -->
<div id="preGateModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-5 shadow-xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-extrabold text-slate-900">Daftar Truk &amp; Kontainer Baru</h3>
            <button type="button" onclick="closePreGateModal()" class="text-slate-400 hover:text-slate-700 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>

        <form id="preGateForm" onsubmit="submitPreGateForm(event)" class="space-y-3 text-xs">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Plat Truk *</label>
                    <input type="text" name="truck_plate" id="pgPlate" required placeholder="B 9421 KFA" class="w-full px-3 py-2 rounded-lg border border-slate-300 font-mono uppercase">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="font-bold text-slate-700">No. Kontainer *</label>
                        <button type="button" onclick="generateValidIsoContainer()" class="text-[10px] font-bold text-indigo-600 hover:underline cursor-pointer">Acak ISO</button>
                    </div>
                    <input type="text" name="container_number" id="pgContainer" required placeholder="MSCU9182379" class="w-full px-3 py-2 rounded-lg border border-slate-300 font-mono uppercase">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Supir</label>
                    <input type="text" name="driver_name" value="Hendra Wijaya" class="w-full px-3 py-2 rounded-lg border border-slate-300">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ekspedisi</label>
                    <input type="text" name="company" value="PT Cikarang Trans Logistik" class="w-full px-3 py-2 rounded-lg border border-slate-300">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jenis Muatan</label>
                    <select name="cargo_type" id="pgCargoType" onchange="if(this.value==='reefer') document.getElementById('pgBlock').value='REEFER';" class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white">
                        <option value="dry">Dry General</option>
                        <option value="reefer">Reefer (-18°C)</option>
                        <option value="dg">Hazmat (DG)</option>
                        <option value="empty">Empty</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Blok Tujuan</label>
                    <select name="target_block" id="pgBlock" class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white">
                        <option value="A">Blok A</option>
                        <option value="B" selected>Blok B</option>
                        <option value="C">Blok C</option>
                        <option value="REEFER">Blok REEFER</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Pelayaran</label>
                    <input type="text" name="owner_company" value="Maersk Line" class="w-full px-3 py-2 rounded-lg border border-slate-300">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">No. DO</label>
                    <input type="text" name="do_number" value="DO-<?= date('Y') ?>-<?= rand(8000,9999) ?>" class="w-full px-3 py-2 rounded-lg border border-slate-300 font-mono">
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closePreGateModal()" class="px-3.5 py-2 rounded-lg bg-slate-100 text-slate-700 font-bold cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-bold cursor-pointer">Simpan Pre-Gate</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: TIKET BARCODE GATE PASS -->
<div id="barcodeTicketModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-5 shadow-xl border border-slate-200 space-y-4 text-center">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5 text-left">
            <div>
                <span class="text-[10px] font-extrabold uppercase text-sky-600">Gate Pass</span>
                <h3 id="btCode" class="text-base font-black text-slate-900 font-mono">PG-261025-001</h3>
            </div>
            <button type="button" onclick="document.getElementById('barcodeTicketModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>

        <div class="bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl p-4 space-y-2">
            <div class="h-14 w-full flex items-center justify-center gap-[2px] overflow-hidden px-4" id="btBars"></div>
            <div id="btBarcodeText" class="font-mono text-sm font-black tracking-[0.25em] text-slate-900"></div>
            <div id="btRfidText" class="text-[10px] font-mono text-slate-500"></div>
        </div>

        <div class="grid grid-cols-2 gap-2 text-left text-xs bg-slate-50 p-3 rounded-xl border border-slate-200">
            <div><span class="text-slate-400 block text-[10px]">Plat Truk</span><strong id="btPlate" class="font-mono"></strong></div>
            <div><span class="text-slate-400 block text-[10px]">Kontainer</span><strong id="btContainer" class="font-mono text-indigo-700"></strong></div>
            <div><span class="text-slate-400 block text-[10px]">Supir</span><strong id="btDriver"></strong></div>
            <div><span class="text-slate-400 block text-[10px]">Tujuan</span><strong id="btBlock"></strong></div>
        </div>

        <div class="flex items-center gap-2 pt-1">
            <button type="button" id="btTriggerGateBtn" class="flex-1 py-2.5 px-3 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-xs font-extrabold cursor-pointer">Scan Gate-In</button>
            <button type="button" id="btTriggerAutoBtn" class="flex-1 py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-extrabold cursor-pointer">Jalankan 6-Tahap</button>
        </div>
    </div>
</div>

<!-- MODAL 3: RINGKASAN 11 PERANGKAT LAPANGAN -->
<div id="hwModal" class="hidden fixed inset-0 z-50 bg-slate-900/70 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-3xl w-full p-5 shadow-2xl border border-slate-200 space-y-3 max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
            <h3 class="text-base font-extrabold text-slate-900">11 Perangkat Terintegrasi</h3>
            <div class="flex items-center gap-2">
                <select id="hwModalLimitSelect" data-limit-target="#hwModalTable" class="px-2 py-1 rounded-lg bg-slate-50 border border-slate-200 text-xs font-bold text-slate-700">
                    <option value="10" selected>Tampil: 10</option>
                    <option value="25">Tampil: 25</option>
                    <option value="all">Semua</option>
                </select>
                <button type="button" onclick="closeHwModal()" class="text-slate-400 hover:text-slate-700 p-1 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
        </div>

        <div class="overflow-y-auto flex-1 border border-slate-200 rounded-xl">
            <table class="w-full text-left text-xs" id="hwModalTable">
                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] font-extrabold uppercase text-slate-500">
                    <tr>
                        <th class="p-2.5 text-center w-10">No</th>
                        <th class="p-2.5">ID</th>
                        <th class="p-2.5">Perangkat</th>
                        <th class="p-2.5">Fungsi</th>
                        <th class="p-2.5 text-right">Uji</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr><td class="p-2.5 text-center font-mono text-slate-400" data-row-num>1</td><td class="p-2.5 font-mono font-bold text-orange-600">HW-01</td><td class="p-2.5 font-bold">Kamera ANPR Gate</td><td class="p-2.5">Baca plat nomor truk</td><td class="p-2.5 text-right"><button type="button" onclick="closeHwModal(); triggerStep(1);" class="px-2 py-1 rounded bg-sky-600 text-white text-[10px] font-bold cursor-pointer">Test</button></td></tr>
                    <tr><td class="p-2.5 text-center font-mono text-slate-400" data-row-num>2</td><td class="p-2.5 font-mono font-bold text-indigo-600">HW-02</td><td class="p-2.5 font-bold">Scanner Container ISO</td><td class="p-2.5">Scan nomor box</td><td class="p-2.5 text-right"><a href="dashboard.php?page=scanner" target="_blank" class="px-2 py-1 rounded bg-indigo-600 text-white text-[10px] font-bold">Kamera</a></td></tr>
                    <tr><td class="p-2.5 text-center font-mono text-slate-400" data-row-num>3</td><td class="p-2.5 font-mono font-bold text-orange-600">HW-03</td><td class="p-2.5 font-bold">Jembatan Timbang 80T</td><td class="p-2.5">Timbang berat VGM</td><td class="p-2.5 text-right"><button type="button" onclick="closeHwModal(); triggerStep(2);" class="px-2 py-1 rounded bg-sky-600 text-white text-[10px] font-bold cursor-pointer">Test</button></td></tr>
                    <tr><td class="p-2.5 text-center font-mono text-slate-400" data-row-num>4</td><td class="p-2.5 font-mono font-bold text-orange-600">HW-04</td><td class="p-2.5 font-bold">UHF RFID Reader</td><td class="p-2.5">Baca kartu e-Pass</td><td class="p-2.5 text-right"><button type="button" onclick="closeHwModal(); triggerActivity('cfs_cycle');" class="px-2 py-1 rounded bg-amber-600 text-white text-[10px] font-bold cursor-pointer">Test</button></td></tr>
                    <tr><td class="p-2.5 text-center font-mono text-slate-400" data-row-num>5</td><td class="p-2.5 font-mono font-bold text-orange-600">HW-05</td><td class="p-2.5 font-bold">LED Display Gate</td><td class="p-2.5">Papan instruksi supir</td><td class="p-2.5 text-right"><button type="button" onclick="closeHwModal(); triggerStep(6);" class="px-2 py-1 rounded bg-slate-700 text-white text-[10px] font-bold cursor-pointer">Test</button></td></tr>
                    <tr><td class="p-2.5 text-center font-mono text-slate-400" data-row-num>6</td><td class="p-2.5 font-mono font-bold text-orange-600">HW-06</td><td class="p-2.5 font-bold">Edge PC &amp; Palang Gate</td><td class="p-2.5">Interlock buka/tutup</td><td class="p-2.5 text-right"><button type="button" onclick="closeHwModal(); triggerStep(6);" class="px-2 py-1 rounded bg-emerald-600 text-white text-[10px] font-bold cursor-pointer">Test</button></td></tr>
                    <tr><td class="p-2.5 text-center font-mono text-slate-400" data-row-num>7</td><td class="p-2.5 font-mono font-bold text-orange-600">HW-07</td><td class="p-2.5 font-bold">GPS / RTK Kabin Alat</td><td class="p-2.5">Posisi presisi slot yard</td><td class="p-2.5 text-right"><button type="button" onclick="closeHwModal(); triggerStep(4);" class="px-2 py-1 rounded bg-teal-600 text-white text-[10px] font-bold cursor-pointer">Test</button></td></tr>
                    <tr><td class="p-2.5 text-center font-mono text-slate-400" data-row-num>8</td><td class="p-2.5 font-mono font-bold text-orange-600">HW-08</td><td class="p-2.5 font-bold">Sensor Spreader Twistlock</td><td class="p-2.5">Catat angkat container</td><td class="p-2.5 text-right"><button type="button" onclick="closeHwModal(); triggerStep(3);" class="px-2 py-1 rounded bg-teal-600 text-white text-[10px] font-bold cursor-pointer">Test</button></td></tr>
                    <tr><td class="p-2.5 text-center font-mono text-slate-400" data-row-num>9</td><td class="p-2.5 font-mono font-bold text-orange-600">HW-09</td><td class="p-2.5 font-bold">Soket Reefer &amp; Suhu IoT</td><td class="p-2.5">Monitor suhu -18°C &amp; kWh</td><td class="p-2.5 text-right"><button type="button" onclick="closeHwModal(); triggerActivity('reefer_plug');" class="px-2 py-1 rounded bg-cyan-600 text-white text-[10px] font-bold cursor-pointer">Test</button></td></tr>
                    <tr><td class="p-2.5 text-center font-mono text-slate-400" data-row-num>10</td><td class="p-2.5 font-mono font-bold text-orange-600">HW-10</td><td class="p-2.5 font-bold">Sensor Rel Kereta (KA)</td><td class="p-2.5">Deteksi gerbong siding</td><td class="p-2.5 text-right"><button type="button" onclick="closeHwModal(); triggerActivity('rail_load');" class="px-2 py-1 rounded bg-indigo-600 text-white text-[10px] font-bold cursor-pointer">Test</button></td></tr>
                    <tr><td class="p-2.5 text-center font-mono text-slate-400" data-row-num>11</td><td class="p-2.5 font-mono font-bold text-orange-600">HW-11</td><td class="p-2.5 font-bold">E-Seal Pabean (CEISA)</td><td class="p-2.5">Segel GPS &amp; rilis SPPB</td><td class="p-2.5 text-right"><button type="button" onclick="closeHwModal(); triggerStep(5);" class="px-2 py-1 rounded bg-purple-600 text-white text-[10px] font-bold cursor-pointer">Test</button></td></tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-end">
            <button type="button" onclick="closeHwModal()" class="px-4 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold cursor-pointer">Tutup</button>
        </div>
    </div>
</div>

<!-- MODAL 4: IMPORT JSON -->
<div id="importJsonModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-5 shadow-xl border border-slate-200 space-y-3">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
            <h3 class="text-sm font-extrabold text-slate-900">Import Data Pre-Advice (JSON)</h3>
            <button type="button" onclick="document.getElementById('importJsonModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <textarea id="importJsonTextarea" rows="7" class="w-full font-mono text-xs p-3 rounded-xl border border-slate-300 bg-slate-50"></textarea>
        <div class="flex justify-end gap-2">
            <button type="button" onclick="document.getElementById('importJsonModal').classList.add('hidden')" class="px-3 py-1.5 rounded-lg bg-slate-100 text-xs font-bold cursor-pointer">Batal</button>
            <button type="button" onclick="submitImportJson()" class="px-4 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-bold cursor-pointer">Import</button>
        </div>
    </div>
</div>

<script>
let allPreadviceRows = [];
let activeAnomalyCtx = null;

function openHwModal()  { document.getElementById('hwModal')?.classList.remove('hidden'); }
function closeHwModal() { document.getElementById('hwModal')?.classList.add('hidden'); }

function switchIntegrationTab(tabId) {
    if (tabId === 'tab-oauth' || tabId === 'tab-odoo') tabId = 'tab-connectors';
    document.querySelectorAll('.int-tab-pane').forEach(el => el.classList.add('hidden'));
    document.getElementById(tabId)?.classList.remove('hidden');
    document.querySelectorAll('.int-tab-btn').forEach(btn => {
        btn.classList.remove('bg-slate-900', 'text-white', 'shadow-2xs');
        btn.classList.add('text-slate-600', 'hover:bg-slate-100');
    });
    const activeBtn = document.getElementById('btn-' + tabId);
    if (activeBtn) {
        activeBtn.classList.remove('text-slate-600', 'hover:bg-slate-100');
        activeBtn.classList.add('bg-slate-900', 'text-white', 'shadow-2xs');
    }
    if (tabId === 'tab-dlq') loadDlqTable();
}

function showAnomalyActionCard(anom, dlqId = 0) {
    if (!anom) return;
    activeAnomalyCtx = {
        dlq_id: dlqId || anom.dlq_id || 0,
        pre_gate_code: anom.pre_gate_code || '',
        anomaly_code: anom.anomaly_code || 'OPERATIONAL_EXCEPTION',
        container_number: anom.container_number || '',
        truck_plate: anom.truck_plate || ''
    };
    const card = document.getElementById('anomalyActionCard');
    const slipCard = document.getElementById('baSlipCard');
    if (slipCard) slipCard.classList.add('hidden');
    if (!card) return;

    document.getElementById('anomBadgeCode').textContent = anom.anomaly_code || 'ANOMALY';
    document.getElementById('anomBadgeAsset').textContent = `${anom.truck_plate || '-'} • ${anom.container_number || '-'} (${anom.pre_gate_code || 'GATE'})`;
    document.getElementById('anomTitleText').textContent = anom.anomaly_title || 'Ketidakcocokan Dokumen / Fisik Terdeteksi';
    document.getElementById('anomDescText').textContent = anom.anomaly_notes || 'Interlock mengunci proses untuk mencegah data tidak valid masuk ke yard.';
    document.getElementById('anomSopRejectText').textContent = anom.sop_reject || 'Tolak akses masuk terminal, kunci palang gate, dan arahkan truk putar balik keluar melalui Jalur Darurat Gate.';
    document.getElementById('anomSopResolveText').textContent = anom.sop_resolve || 'Verifikasi dokumen pengganti / amandemen VGM / pasang segel baru (Reseal), buka interlock, dan lanjutkan proses truk.';

    card.classList.remove('hidden');
    card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function openAnomalyFromRow(encodedJson) {
    try {
        const r = JSON.parse(decodeURIComponent(encodedJson));
        showAnomalyActionCard(r, 0);
    } catch (e) {}
}

function showBaSlipCard(slip, isResolve = false) {
    if (!slip) return;
    const card = document.getElementById('baSlipCard');
    if (!card) return;
    document.getElementById('baSlipNumber').textContent = slip.ba_number || '-';
    const decEl = document.getElementById('baSlipDecision');
    decEl.textContent = slip.decision_label || '-';
    decEl.className = 'text-sm font-black mt-0.5 ' + (isResolve ? 'text-emerald-700' : 'text-rose-700');
    const iconEl = document.getElementById('baSlipIcon');
    if (iconEl) {
        iconEl.className = 'w-8 h-8 rounded-xl text-white flex items-center justify-center text-sm ' + (isResolve ? 'bg-emerald-600' : 'bg-rose-600');
        iconEl.innerHTML = isResolve ? '<i class="fa-solid fa-file-circle-check"></i>' : '<i class="fa-solid fa-file-circle-xmark"></i>';
    }
    document.getElementById('baSlipTruck').textContent = `${slip.truck_plate || '-'} (${slip.pre_gate_code || '-'})`;
    document.getElementById('baSlipContainer').textContent = slip.container_number || '-';
    document.getElementById('baSlipAnomaly').textContent = `${slip.anomaly_code || '-'} — ${slip.anomaly_title || ''}`;
    document.getElementById('baSlipTime').textContent = `${slip.timestamp || '-'} • ${slip.officer || 'Supervisor TOC'}`;
    document.getElementById('baSlipAction').textContent = slip.action_taken || '-';
    card.classList.remove('hidden');
    card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function updateLiveConsole(data, label = 'DONE') {
    const badge = document.getElementById('consoleStatusBadge');
    const pre   = document.getElementById('liveJsonConsole');
    if (pre) pre.textContent = JSON.stringify(data, null, 2);

    let resObj = data?.result || data;
    if (Array.isArray(data?.steps) && data.steps.length > 0) {
        const lastStep = data.steps[data.steps.length - 1];
        resObj = lastStep?.result || lastStep;
    }

    const ok = Boolean(data?.success && (resObj?.success !== false));
    if (badge) {
        badge.textContent = ok ? (label + ' • SUKSES') : (label + ' • INTERLOCK / DITOLAK');
        badge.className = ok
            ? 'px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200'
            : 'px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200';
    }

    if (resObj?.oauth_verified) {
        document.getElementById('ovClient').textContent = resObj.oauth_verified.client_id || '-';
        document.getElementById('ovIntegrity').textContent = resObj.oauth_verified.integrity_check || 'VALID';
        document.getElementById('ovIntegrity').className = 'px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200';
    } else if (resObj?.oauth_error) {
        document.getElementById('ovIntegrity').textContent = resObj.oauth_error.toUpperCase();
        document.getElementById('ovIntegrity').className = 'px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-rose-50 text-rose-700 border border-rose-200';
    }

    if (resObj?.tri_flow) {
        document.getElementById('tfPhysical').textContent = resObj.tri_flow.physical || '-';
        document.getElementById('tfInformation').textContent = resObj.tri_flow.information || '-';
        document.getElementById('tfFinancial').textContent = resObj.tri_flow.financial || '-';
    } else if (data?.tri_flow) {
        document.getElementById('tfPhysical').textContent = data.tri_flow.physical || '-';
        document.getElementById('tfInformation').textContent = data.tri_flow.information || '-';
        document.getElementById('tfFinancial').textContent = data.tri_flow.financial || '-';
    } else if (resObj?.error || data?.error) {
        document.getElementById('tfPhysical').textContent = 'Interlock aktif: Palang / Alat Berat dikunci otomatis.';
        document.getElementById('tfInformation').textContent = resObj?.error || data?.error;
        document.getElementById('tfFinancial').textContent = 'Ditahan di Antrean Resolusi Masalah (DLQ); pilih SOP Tolak atau Override.';
    }

    if (resObj?.anomaly) {
        showAnomalyActionCard(resObj.anomaly, resObj.dlq_id || 0);
    }
}

async function triggerAnomalyScenario(type, code = '') {
    let url = `api/edge_simulator.php?action=trigger_anomaly&type=${encodeURIComponent(type)}`;
    if (code) url += `&code=${encodeURIComponent(code)}`;
    const r = await fetch(url);
    const d = await r.json();
    updateLiveConsole(d, 'ANOMALI SENSOR');
    await refreshIntegrationStats();
    await loadPreadviceTable();
    await loadDlqTable();
}

async function executeAnomalyDecision(decision) {
    if (!activeAnomalyCtx) return;
    const payload = {
        decision: decision,
        dlq_id: activeAnomalyCtx.dlq_id || 0,
        pre_gate_code: activeAnomalyCtx.pre_gate_code || '',
        anomaly_code: activeAnomalyCtx.anomaly_code || '',
        container_number: activeAnomalyCtx.container_number || '',
        truck_plate: activeAnomalyCtx.truck_plate || ''
    };
    const r = await fetch('api/demo_control.php?action=handle_anomaly', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    const d = await r.json();
    document.getElementById('anomalyActionCard')?.classList.add('hidden');
    updateLiveConsole(d, decision === 'reject' ? 'SOP PENOLAKAN' : 'SOP PENYELESAIAN');
    if (d.slip) {
        showBaSlipCard(d.slip, decision !== 'reject');
    }
    await refreshIntegrationStats();
    await loadPreadviceTable();
    await loadDlqTable();
}

async function refreshIntegrationStats() {
    try {
        const r = await fetch('api/demo_control.php?action=status');
        const d = await r.json();
        if (d.success) {
            document.getElementById('kpiOutsideScheduled').textContent = d.outside_scheduled;
            document.getElementById('kpiArrived').textContent = d.arrived;
            document.getElementById('kpiInYard').textContent = d.containers_in_yard;
            document.getElementById('kpiEventsLogged').textContent = d.events_logged;
            document.getElementById('kpiDlqOpen').textContent = d.dlq_open;
            document.getElementById('tabDlqBadge').textContent = (d.dlq_open || 0) + (d.dlq_buffered || 0);
            const bufEl = document.getElementById('bcpBufferedCount');
            if (bufEl) bufEl.textContent = d.dlq_buffered ?? 0;
            const bcpEl = document.getElementById('bcpModeBadge');
            if (bcpEl && d.bcp) {
                bcpEl.textContent = d.bcp.failover_active ? 'BCP EDGE BUFFER' : 'PRIMARY ONLINE';
                bcpEl.className = 'text-xs font-black mt-0.5 ' + (d.bcp.failover_active ? 'text-amber-600' : 'text-emerald-700');
            }
        }
    } catch (e) {}
}

async function loadPreadviceTable() {
    const st = document.getElementById('preadviceFilterStatus').value;
    try {
        const r = await fetch('api/demo_control.php?action=list&status=' + encodeURIComponent(st));
        const d = await r.json();
        allPreadviceRows = d.data || [];
        renderPreadviceFiltered();
    } catch (e) {}
}

function renderPreadviceFiltered() {
    const q = (document.getElementById('preadviceSearch').value || '').toLowerCase().trim();
    const tbody = document.getElementById('preadviceTableBody');
    const rows = allPreadviceRows.filter(r => {
        if (!q) return true;
        return (r.pre_gate_code || '').toLowerCase().includes(q)
            || (r.truck_plate || '').toLowerCase().includes(q)
            || (r.container_number || '').toLowerCase().includes(q)
            || (r.driver_name || '').toLowerCase().includes(q)
            || (r.owner_company || '').toLowerCase().includes(q)
            || (r.anomaly_code || '').toLowerCase().includes(q)
            || (r.anomaly_title || '').toLowerCase().includes(q);
    });

    if (rows.length === 0) {
        tbody.innerHTML = '<tr data-empty-row><td colspan="7" class="py-6 text-center text-slate-400">Tidak ada data antrean sesuai filter.</td></tr>';
        return;
    }

    const limVal = document.getElementById('preadviceLimitSelect')?.value || '10';
    const visible = (limVal === 'all') ? rows : rows.slice(0, parseInt(limVal, 10) || 10);

    tbody.innerHTML = visible.map((r, idx) => {
        const isSched = r.status === 'SCHEDULED';
        const isHold  = r.status === 'HOLD_ANOMALY';
        const isRej   = r.status === 'REJECTED';
        const hasAnom = Boolean(r.anomaly_code && r.anomaly_code !== '');
        const isOverridden = r.resolution_status === 'OVERRIDDEN_APPROVED';

        const cargoColors = {
            dry: 'bg-slate-100 text-slate-700',
            reefer: 'bg-cyan-50 text-cyan-700 border border-cyan-200',
            dg: 'bg-rose-50 text-rose-700 border border-rose-200',
            empty: 'bg-amber-50 text-amber-700'
        };
        const cBadge = cargoColors[r.cargo_type] || cargoColors.dry;
        const rowJson = encodeURIComponent(JSON.stringify(r));

        let condHtml = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fa-solid fa-check"></i> Dokumen &amp; VGM Sesuai</span>`;
        if (hasAnom) {
            if (isOverridden) {
                condHtml = `<div class="space-y-0.5">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300"><i class="fa-solid fa-screwdriver-wrench"></i> Diatasi SOP (${r.anomaly_code})</span>
                    <div class="text-[10px] font-mono text-slate-500">${r.ba_number || ''}</div>
                </div>`;
            } else if (isRej) {
                condHtml = `<div class="space-y-0.5">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-300"><i class="fa-solid fa-ban"></i> Ditolak (${r.anomaly_code})</span>
                    <div class="text-[10px] font-mono text-rose-600">${r.ba_number || ''}</div>
                </div>`;
            } else {
                condHtml = `<div class="space-y-0.5 max-w-[250px]">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200"><i class="fa-solid fa-triangle-exclamation"></i> ${r.anomaly_code}</span>
                    <div class="text-[10.5px] text-slate-600 font-semibold leading-tight truncate" title="${(r.anomaly_title || '').replace(/"/g, '&quot;')}">${r.anomaly_title || r.anomaly_notes || ''}</div>
                </div>`;
            }
        }

        let statusBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Masuk Gate</span>';
        if (isHold) {
            statusBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-600 text-white animate-pulse">HOLD GATE</span>';
        } else if (isRej) {
            statusBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-200 text-rose-700 border border-rose-300">Putar Balik</span>';
        } else if (isSched) {
            statusBadge = hasAnom && !isOverridden
                ? '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">Siap Uji Anomali</span>'
                : '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Di Luar</span>';
        }

        let actionsHtml = `<button type="button" onclick="openBarcodeTicket('${rowJson}')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold cursor-pointer" title="Tiket Barcode"><i class="fa-solid fa-barcode"></i></button>`;
        if (isHold || (hasAnom && !isOverridden && !isRej)) {
            actionsHtml += `
                <button type="button" onclick="triggerAnomalyScenario('${(r.anomaly_code || '').toLowerCase()}', '${r.pre_gate_code}')" class="px-2 py-1 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-[11px] font-bold cursor-pointer" title="Trigger Sensor & Lihat Interlock"><i class="fa-solid fa-bolt mr-0.5"></i>Uji</button>
                <button type="button" onclick="openAnomalyFromRow('${rowJson}')" class="px-2 py-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-[11px] font-bold cursor-pointer" title="Buka Panel Keputusan SOP (Tolak / Atasi)">SOP</button>
            `;
        } else if (isRej) {
            actionsHtml += `
                <button type="button" onclick="openAnomalyFromRow('${rowJson}')" class="px-2 py-1 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 text-[11px] font-bold cursor-pointer">Tinjau Ulang</button>
            `;
        } else if (isSched) {
            actionsHtml += `
                <button type="button" onclick="triggerStep(1, '${r.pre_gate_code}')" class="px-2 py-1 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-[11px] font-bold cursor-pointer">Masuk</button>
                <button type="button" onclick="runAutoCycleForCode('${r.pre_gate_code}')" class="px-2 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-bold cursor-pointer">Auto</button>
            `;
        } else {
            actionsHtml += `
                <button type="button" onclick="selectContainerForActivity('${r.container_number}')" class="px-2 py-1 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 text-[11px] font-bold cursor-pointer">Pilih</button>
            `;
        }

        return `<tr class="${isHold ? 'bg-rose-50/60' : 'hover:bg-slate-50/80'} transition">
            <td class="py-2 px-2.5 text-center font-mono text-slate-400">${idx + 1}</td>
            <td class="py-2 px-2 whitespace-nowrap">
                <div class="font-mono font-extrabold text-slate-900">${r.truck_plate}</div>
                <div class="text-[10px] font-mono text-slate-400">${r.pre_gate_code}</div>
            </td>
            <td class="py-2 px-2">
                <span class="font-mono font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200/70">${r.container_number}</span>
                <div class="text-[10.5px] text-slate-500 mt-0.5 truncate max-w-[150px]">${r.owner_company || '-'}</div>
            </td>
            <td class="py-2 px-2">${condHtml}</td>
            <td class="py-2 px-2 whitespace-nowrap">
                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase ${cBadge}">${r.cargo_type}</span>
                <span class="ml-1 font-bold text-slate-700">${r.target_block || 'B'}</span>
            </td>
            <td class="py-2 px-2 whitespace-nowrap">${statusBadge}</td>
            <td class="py-2 px-2 text-right whitespace-nowrap space-x-1">${actionsHtml}</td>
        </tr>`;
    }).join('');
}

function selectContainerForActivity(cn) {
    const inp = document.getElementById('activityTargetContainer');
    inp.value = cn;
    inp.scrollIntoView({ behavior: 'smooth', block: 'center' });
    inp.classList.add('ring-2', 'ring-teal-500');
    setTimeout(() => inp.classList.remove('ring-2', 'ring-teal-500'), 1800);
}

async function triggerStep(stepId, code = '') {
    let url = `api/edge_simulator.php?action=step&step_id=${stepId}`;
    if (code) url += `&code=${encodeURIComponent(code)}`;
    const r = await fetch(url);
    const d = await r.json();
    updateLiveConsole(d, `TAHAP ${stepId}`);
    await refreshIntegrationStats();
    await loadPreadviceTable();
}

async function runAutoCycleNext() {
    const r = await fetch('api/edge_simulator.php?action=auto_cycle');
    const d = await r.json();
    updateLiveConsole(d, 'AUTO 6-TAHAP');
    await refreshIntegrationStats();
    await loadPreadviceTable();
}

async function runAutoCycleForCode(code) {
    const r = await fetch('api/edge_simulator.php?action=auto_cycle&code=' + encodeURIComponent(code));
    const d = await r.json();
    updateLiveConsole(d, `AUTO (${code})`);
    await refreshIntegrationStats();
    await loadPreadviceTable();
}

async function triggerActivity(type, extraQuery = '') {
    const cn = (document.getElementById('activityTargetContainer').value || '').trim();
    let url = `api/edge_simulator.php?action=activity&type=${encodeURIComponent(type)}${extraQuery}`;
    if (cn) url += `&container=${encodeURIComponent(cn)}`;
    const r = await fetch(url);
    const d = await r.json();
    updateLiveConsole(d, type.toUpperCase());
    await refreshIntegrationStats();
    await loadPreadviceTable();
}

async function resetDemoDatabase() {
    if (window.cidpConfirm) {
        const ok = await window.cidpConfirm({
            title: 'Konfirmasi Reset Antrean Sandbox',
            message: 'Reset seluruh antrean simulasi integrasi ke status awal?',
            confirmText: 'Ya, Reset',
            cancelText: 'Tidak, Batal',
            tone: 'danger'
        });
        if (!ok) return;
    }
    const r = await fetch('api/demo_control.php?action=reset');
    const d = await r.json();
    document.getElementById('activityTargetContainer').value = '';
    document.getElementById('anomalyActionCard')?.classList.add('hidden');
    document.getElementById('baSlipCard')?.classList.add('hidden');
    updateLiveConsole(d, 'RESET');
    await refreshIntegrationStats();
    await loadPreadviceTable();
    await loadDlqTable();
}

async function requestOAuthToken() {
    const cid = document.getElementById('oauthClientSelect').value;
    const r = await fetch('api/oauth_server.php?action=token', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ grant_type: 'client_credentials', client_id: cid })
    });
    const d = await r.json();
    if (d.success) {
        document.getElementById('jwtTokenOutput').value = 'Bearer ' + d.access_token;
        document.getElementById('jwtExpiryBadge').textContent = 'AKTIF • ' + d.expires_in + ' dtk';
        document.getElementById('jwtClaimsOutput').textContent = JSON.stringify({
            client_id: d.client_id,
            granted_scopes: d.granted_scopes,
            expires_at: d.expires_at,
            jwt_claims: d.claims
        }, null, 2);
    }
}

async function runSecurityScenario(kind) {
    const out = document.getElementById('secTestOutput');
    const st  = document.getElementById('secTestStatus');
    let d = {};
    if (kind === 'tamper') {
        d = await (await fetch('api/edge_simulator.php?action=tamper_test')).json();
        st.textContent = 'DITOLAK (401 Payload Palsu) → Masuk DLQ';
        st.className = 'text-[11px] font-mono text-rose-600 font-bold';
    } else if (kind === 'scope') {
        d = await (await fetch('api/edge_simulator.php?action=scope_violation_test')).json();
        st.textContent = 'DITOLAK (403 Izin Ditolak) → Masuk DLQ';
        st.className = 'text-[11px] font-mono text-amber-600 font-bold';
    } else if (kind === 'unregistered') {
        d = await (await fetch('api/edge_simulator.php?action=send_unregistered')).json();
        st.textContent = 'DITOLAK (422 Truk Ilegal) → Masuk DLQ';
        st.className = 'text-[11px] font-mono text-purple-600 font-bold';
    } else if (kind === 'idempotency') {
        const evId = 'IDEMP-TEST-' + Date.now();
        const body = { event_id: evId, event_type: 'EQUIPMENT_MOVE', payload: { equipment_id: 'RS-01', gps_x: 210, gps_y: 190, fuel_percent: 85, status: 'operating' } };
        const r1 = await (await fetch('api/ingest_event.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(body) })).json();
        const r2 = await (await fetch('api/ingest_event.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(body) })).json();
        d = { event_id_tested: evId, first_send: r1, second_send_replay: r2 };
        st.textContent = 'AMAN • Kiriman ke-2 dicegah (duplicate=' + String(r2.duplicate) + ')';
        st.className = 'text-[11px] font-mono text-emerald-600 font-bold';
    } else if (kind === 'surge') {
        st.textContent = 'Menguji lonjakan 12 transaksi/detik...';
        d = await (await fetch('api/edge_simulator.php?action=surge_test')).json();
        st.textContent = `SURGE OK • ${d.processed_live} live, ${d.buffered_zero_loss} di Edge Buffer (0 hilang)`;
        st.className = 'text-[11px] font-mono text-[#0170b9] font-bold';
    }
    if (out) out.textContent = JSON.stringify(d, null, 2);
    updateLiveConsole(d, kind === 'surge' ? 'SURGE BUFFER' : 'PROTEKSI');
    await refreshIntegrationStats();
    await loadDlqTable();
}

async function toggleBcpMode() {
    const out = document.getElementById('secTestOutput');
    const st  = document.getElementById('secTestStatus');
    const d = await (await fetch('api/demo_control.php?action=bcp_toggle')).json();
    if (out) out.textContent = JSON.stringify(d, null, 2);
    if (st && d.bcp) {
        st.textContent = d.bcp.failover_active ? 'BCP AKTIF • Event masuk diamankan ke Edge Buffer' : 'PRIMARY ONLINE • Sinkronisasi normal aktif';
        st.className = 'text-[11px] font-mono font-bold ' + (d.bcp.failover_active ? 'text-amber-600' : 'text-emerald-600');
    }
    await refreshIntegrationStats();
}

async function flushBcpBuffer() {
    const out = document.getElementById('secTestOutput');
    const st  = document.getElementById('secTestStatus');
    if (st) st.textContent = 'Memulihkan & me-replay Edge Buffer...';
    const d = await (await fetch('api/demo_control.php?action=bcp_flush')).json();
    if (out) out.textContent = JSON.stringify(d, null, 2);
    if (st) {
        st.textContent = `PULIH (RPO=0) • ${d.resolved || 0} antrean dipulihkan dalam ${d.elapsed_ms || 0} ms`;
        st.className = 'text-[11px] font-mono text-emerald-600 font-bold';
    }
    updateLiveConsole(d, 'BCP RECOVERY');
    await refreshIntegrationStats();
    await loadDlqTable();
}

async function loadDlqTable() {
    const tbody = document.getElementById('dlqTableBody');
    try {
        const r = await fetch('api/demo_control.php?action=dlq');
        const d = await r.json();
        const rows = d.data || [];
        if (rows.length === 0) {
            tbody.innerHTML = '<tr data-empty-row><td colspan="7" class="py-6 text-center text-emerald-600 font-bold"><i class="fa-solid fa-circle-check mr-1"></i> Bersih — tidak ada error atau antrean buffer DLQ.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map((x, idx) => {
            const stBadge = x.status === 'QUARANTINED'
                ? '<span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700">Tertahan (Quarantined)</span>'
                : (x.status === 'BUFFERED'
                    ? '<span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-100 text-[#0170b9]">Buffer BCP/Surge</span>'
                    : (x.status === 'RESOLVED'
                        ? `<span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700">Selesai SOP</span><div class="text-[9.5px] font-mono text-slate-400 mt-0.5">${x.ba_number || ''}</div>`
                        : `<span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-200 text-rose-700">Ditolak (Turn-Back)</span><div class="text-[9.5px] font-mono text-rose-500 mt-0.5">${x.ba_number || ''}</div>`));
            const acts = x.status === 'QUARANTINED'
                ? `<button type="button" onclick="resolveDlq(${x.id}, 'dlq_resolve')" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-[10px] font-bold cursor-pointer" title="${(x.sop_resolve || '').replace(/"/g, '&quot;')}"><i class="fa-solid fa-screwdriver-wrench mr-0.5"></i>SOP Atasi</button>
                   <button type="button" onclick="resolveDlq(${x.id}, 'dlq_discard')" class="px-2.5 py-1 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-[10px] font-bold cursor-pointer" title="${(x.sop_reject || '').replace(/"/g, '&quot;')}"><i class="fa-solid fa-ban mr-0.5"></i>Tolak &amp; BA</button>`
                : (x.status === 'BUFFERED'
                    ? `<button type="button" onclick="flushBcpBuffer()" class="px-2 py-1 rounded bg-[#0170b9] text-white text-[10px] font-bold cursor-pointer">Flush Buffer</button>`
                    : `<span class="text-[10px] text-slate-500 font-mono">${x.resolution_action || 'Selesai'}</span>`);
            return `
            <tr class="hover:bg-slate-50">
                <td class="py-2.5 px-2.5 text-center font-mono text-slate-400" data-row-num>${idx + 1}</td>
                <td class="py-2.5 px-3 font-mono text-[11px] text-slate-500">#${x.id}<div>${x.created_at}</div></td>
                <td class="py-2.5 px-3 font-mono font-bold text-slate-900">
                    <div>${x.event_type || '-'}</div>
                    ${x.anomaly_code ? `<span class="px-1.5 py-0.2 rounded text-[9.5px] bg-rose-50 text-rose-700 border border-rose-200 font-extrabold">${x.anomaly_code}</span>` : ''}
                </td>
                <td class="py-2.5 px-3 font-mono text-xs text-slate-700">
                    <div>${x.source_device || '-'}</div>
                    ${x.truck_plate || x.container_number ? `<div class="text-[10px] text-indigo-700 font-bold">${x.truck_plate || ''} ${x.container_number || ''}</div>` : ''}
                </td>
                <td class="py-2.5 px-3 text-slate-700 font-semibold max-w-md">
                    <div>${x.reason}</div>
                    ${x.resolution_notes ? `<div class="text-[10.5px] text-emerald-700 font-bold mt-0.5"><i class="fa-solid fa-check-double mr-1"></i>SOP: ${x.resolution_notes}</div>` : ''}
                </td>
                <td class="py-2.5 px-3 whitespace-nowrap">${stBadge}</td>
                <td class="py-2.5 px-3 text-right space-x-1 whitespace-nowrap">${acts}</td>
            </tr>`;
        }).join('');
    } catch (e) {}
}

async function resolveDlq(id, act) {
    const r = await fetch(`api/demo_control.php?action=${act}&id=${id}`);
    const d = await r.json();
    updateLiveConsole(d, act === 'dlq_resolve' ? 'SOP PENYELESAIAN' : 'SOP PENOLAKAN');
    if (d.slip) {
        showBaSlipCard(d.slip, act === 'dlq_resolve');
    }
    await refreshIntegrationStats();
    await loadPreadviceTable();
    await loadDlqTable();
}

function openPreGateModal() {
    generateValidIsoContainer();
    document.getElementById('preGateModal').classList.remove('hidden');
}
function closePreGateModal() {
    document.getElementById('preGateModal').classList.add('hidden');
}
async function generateValidIsoContainer() {
    const r = await fetch('api/demo_control.php?action=generate_iso&prefix=MSCU');
    const d = await r.json();
    if (d.container_number) document.getElementById('pgContainer').value = d.container_number;
}
async function submitPreGateForm(e) {
    e.preventDefault();
    const obj = Object.fromEntries(new FormData(e.target).entries());
    const r = await fetch('api/demo_control.php?action=register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(obj)
    });
    const d = await r.json();
    if (!d.success) { alert(d.error || 'Gagal mendaftarkan pre-gate.'); return; }
    closePreGateModal();
    updateLiveConsole(d, 'PRE-GATE BARU');
    await refreshIntegrationStats();
    await loadPreadviceTable();
}

function openBarcodeTicket(encodedJson) {
    const r = JSON.parse(decodeURIComponent(encodedJson));
    document.getElementById('btCode').textContent = r.pre_gate_code;
    document.getElementById('btBarcodeText').textContent = r.container_number;
    document.getElementById('btRfidText').textContent = 'RFID: ' + (r.rfid_tag || '-');
    document.getElementById('btPlate').textContent = r.truck_plate;
    document.getElementById('btContainer').textContent = r.container_number;
    document.getElementById('btDriver').textContent = r.driver_name || '-';
    document.getElementById('btBlock').textContent = (r.cargo_type || 'dry').toUpperCase() + ' → Blok ' + (r.target_block || 'B');

    const barsEl = document.getElementById('btBars');
    let barsHtml = '';
    const seedStr = r.pre_gate_code + r.container_number;
    for (let i = 0; i < 46; i++) {
        const w = ((seedStr.charCodeAt(i % seedStr.length) + i) % 3) + 1;
        barsHtml += `<span class="bg-slate-900 h-full inline-block" style="width:${w * 2}px"></span>`;
    }
    barsEl.innerHTML = barsHtml;

    document.getElementById('btTriggerGateBtn').onclick = () => {
        document.getElementById('barcodeTicketModal').classList.add('hidden');
        triggerStep(1, r.pre_gate_code);
    };
    document.getElementById('btTriggerAutoBtn').onclick = () => {
        document.getElementById('barcodeTicketModal').classList.add('hidden');
        runAutoCycleForCode(r.pre_gate_code);
    };
    document.getElementById('barcodeTicketModal').classList.remove('hidden');
}

function exportPreadviceJson() { window.open('api/demo_control.php?action=export_json', '_blank'); }
async function openImportJsonModal() {
    const r = await fetch('api/demo_control.php?action=generate_iso&prefix=CMAU');
    const d = await r.json();
    document.getElementById('importJsonTextarea').value = JSON.stringify([{
        truck_plate: "B 9771 UTX",
        container_number: d.container_number || "CMAU9180379",
        driver_name: "Wahyu Setiawan",
        company: "PT Nusantara Intermodal",
        cargo_type: "dry",
        owner_company: "CMA CGM",
        target_block: "C"
    }], null, 2);
    document.getElementById('importJsonModal').classList.remove('hidden');
}
async function submitImportJson() {
    const r = await fetch('api/demo_control.php?action=import_json', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: document.getElementById('importJsonTextarea').value
    });
    const d = await r.json();
    if (!d.success) { alert(d.error || 'Format JSON tidak valid'); return; }
    document.getElementById('importJsonModal').classList.add('hidden');
    updateLiveConsole(d, 'IMPORT JSON');
    await refreshIntegrationStats();
    await loadPreadviceTable();
}

async function callOdooApi(act) {
    const out = document.getElementById('odooJsonOutput');
    out.textContent = 'Menghubungi Odoo Cloud...';
    const r = await fetch('api/odoo_client.php?action=' + encodeURIComponent(act));
    const d = await r.json();
    out.textContent = JSON.stringify(d, null, 2);
}

function copyConsoleJson() {
    navigator.clipboard.writeText(document.getElementById('liveJsonConsole').textContent || '');
}

document.addEventListener('DOMContentLoaded', () => {
    refreshIntegrationStats();
    loadPreadviceTable();
    requestOAuthToken();
});
</script>
