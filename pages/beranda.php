<?php
// =============================================================================
// MODUL: ENTERPRISE EXECUTIVE CONTROL TOWER DASHBOARD (DPMS 35 HA)
// File: pages/beranda.php
// Proyek: Conclusion Intermodal Dry Port (CIDP 35 Hectares)
// Desain: Clean High-Contrast Light Enterprise Control Tower (#ffffff, #0170b9, #ea580c)
// Standar: UN/CEFACT, ISO 18000-6C RFID, Bea Cukai CEISA 4.0, SOLAS VGM 80T, HACCP Cold Chain
// Fitur Dinamis:
//   - Opsi A: Live Auto-Sync Telemetry Engine (AJAX Polling + Jitter Sensor Real-Time)
//   - Opsi B: Cross-Filtering Interaktif (6-Stage Ribbon & 4 Zona Spasial) + Instant Search
//   - Opsi C: Pilar ke-3 Aliran Finansial, Demurrage Exposure & Odoo 17 ERP Ledger
//   - Opsi D: Integrasi Halaman Dedikasi Laporan Eksekutif A4 (report_executive.php)
// =============================================================================

require_once __DIR__ . '/../api/beranda_telemetry.php';
require_once __DIR__ . '/../api/preadvice_helper.php';

$beranda_data = build_beranda_telemetry($pdo ?? null, false);
$beranda_decisions = (isset($pdo) && $pdo instanceof PDO) ? cidp_build_decision_report($pdo) : [];
$kpi          = $beranda_data['kpi'];
$perf         = $beranda_data['performance'];
$fin          = $beranda_data['financial'];
$latencies    = $beranda_data['latencies'];
$mhe          = $beranda_data['mhe'] ?? ['total_units' => 15, 'active_units' => 3, 'carrying_units' => 1, 'idle_units' => 12, 'rs_total' => 4, 'rs_active' => 1, 'rtg_total' => 5, 'rtg_active' => 1, 'fl_total' => 4, 'fl_active' => 1, 'tt_total' => 2, 'tt_active' => 0];
$zones        = $beranda_data['zones'] ?? ['z1_pct' => 28, 'z2_pct' => 35, 'z3_pct' => 42, 'z4_pct' => 55];
$dwell_b      = $beranda_data['dwell_buckets'] ?? ['pct_1_2' => 68, 'pct_3' => 22, 'pct_4_5' => 7, 'pct_6p' => 3, 'pct_4p' => 10];
$quick_samples   = $beranda_data['quick_samples'] ?? [];
$containers_list = $beranda_data['containers'];
$events_list     = $beranda_data['events'];

$containers_yard = $kpi['containers_yard'];
$yard_cap_max    = $kpi['yard_cap_max'];
$yard_occupancy  = $kpi['yard_occupancy'];
$total_trucks    = $kpi['total_trucks'];
$gate_turnaround = $kpi['gate_turnaround'];
$loaded_w        = $kpi['loaded_wagons'];
$total_w         = $kpi['total_wagons'];
$siding_pct      = $kpi['siding_pct'];
$reefer_plugs    = $kpi['reefer_plugs'];
$reefer_cap      = $kpi['reefer_cap'];
$reefer_pct      = $kpi['reefer_pct'];
$reefer_temp     = $kpi['reefer_temp'];
$customs_percent = $kpi['customs_pct'];
$customs_cleared = $kpi['customs_cleared'];
$customs_total   = $kpi['customs_total'];
$sppb_sec        = $kpi['sppb_sec'];
$cfs_jobs        = $kpi['cfs_jobs'];
$cfs_pct         = $kpi['cfs_pct'];
$cfs_pallets_rack = $kpi['cfs_pallets_in_rack'] ?? 6;
$cfs_pallets_out  = $kpi['cfs_pallets_dispatched'] ?? 2;
$cfs_weight_kg    = $kpi['cfs_weight_kg'] ?? 4250.0;
$cfs_volume_cbm   = $kpi['cfs_volume_cbm'] ?? 10.8;
$paid_rev        = $fin['paid_revenue'];
$date_display    = !empty($tanggal_sekarang) ? $tanggal_sekarang : $beranda_data['date_display'];
?>

<div class="relative animate-fadeIn pb-12">

    <!-- ======================================================================= -->
    <!-- AMBIENT SPATIAL BACKGROUND LAYER (BATIK NUSANTARA & DRY PORT BLUEPRINT) -->
    <!-- ======================================================================= -->
    <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden select-none" aria-hidden="true">
        <div class="absolute top-0 right-0 w-full lg:w-3/4 max-w-[1000px] h-[550px] opacity-[0.04] bg-no-repeat bg-right-top"
             style="background-image: url('assets/img/facilities/stacking_yard.jpg'); background-size: cover; filter: grayscale(100%); mask-image: radial-gradient(circle at 85% 15%, black 30%, transparent 75%); -webkit-mask-image: radial-gradient(circle at 85% 15%, black 30%, transparent 75%);">
        </div>
    </div>

    <!-- MAIN DASHBOARD CONTENT (ELEVATED Z-10) -->
    <div class="relative z-10 space-y-4">

        <!-- ======================================================================= -->
        <!-- 1. TOP EXECUTIVE COCKPIT BAR (DECLUTTERED & FOCUSED BOARDROOM HEADER)  -->
        <!-- ======================================================================= -->
        <div class="bg-white rounded-xl px-4 py-3 shadow-2xs border border-slate-200 relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-orange-50 border border-orange-200 text-orange-600 flex items-center justify-center text-base flex-shrink-0">
                    <i class="fa-solid fa-tower-observation"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-base sm:text-[17px] font-extrabold text-slate-900 tracking-tight leading-tight">
                            <?= ($current_lang === 'en') ? 'Executive Control Tower — CIDP 35 Ha' : 'Control Tower Eksekutif — CIDP 35 Ha' ?>
                        </h1>
                        <span class="text-[11px] font-extrabold tracking-wide uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full inline-flex items-center gap-1.5">
                            <span id="livePulseIndicator" class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                            <span id="badgeScadaUptime">SCADA <?= $kpi['scada_uptime'] ?>%</span>
                        </span>
                    </div>
                    <p class="text-xs font-medium text-slate-600 mt-0.5 leading-snug">
                        <?= ($current_lang === 'en')
                            ? 'Real-time physical, customs (CEISA 4.0), and financial (Odoo ERP) terminal operations'
                            : 'Pemantauan terpadu operasional lapangan, kepabeanan CEISA 4.0, dan finansial terminal' ?>
                    </p>
                </div>
            </div>

            <!-- Right: Calm Time/Sync Pill + 1 Primary Button + Secondary Export Menu -->
            <div class="flex items-center gap-2.5 flex-wrap w-full lg:w-auto justify-between lg:justify-end">
                <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 text-xs text-slate-700">
                    <span class="font-semibold text-slate-600 hidden sm:inline"><?= $date_display ?> &bull;</span>
                    <span id="berandaLiveClock" class="font-extrabold text-slate-900 font-mono">--:--:-- WIB</span>
                    <span class="text-slate-300">|</span>
                    <button type="button" id="btnToggleSyncSpeed" onclick="cycleSyncSpeed()" class="text-[10px] font-black px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-300 hover:bg-emerald-200 transition cursor-pointer" title="Mode Pembaruan Demo Realtime">⚡ 5s (Live)</button>
                    <button type="button" id="btnToggleAutoSync" onclick="toggleBerandaAutoSync()" class="inline-flex items-center gap-1.5 font-bold text-emerald-700 cursor-pointer" title="Aktifkan/Matikan Sinkronisasi Otomatis">
                        <span id="autoSyncDot" class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span id="autoSyncCountdown" class="font-mono text-[11px]">5s</span>
                    </button>
                    <button type="button" onclick="fetchLiveBerandaTelemetry(true)" class="text-slate-500 hover:text-[#0170b9] transition cursor-pointer" title="Sinkronisasi Sekarang">
                        <i id="syncSpinnerIcon" class="fa-solid fa-arrows-rotate text-[11px]"></i>
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <a href="report_executive.php?autoprint=1" target="_blank" class="px-3.5 py-1.5 bg-orange-600 hover:bg-orange-500 text-white rounded-lg text-xs font-extrabold transition inline-flex items-center gap-1.5 shadow-2xs" title="Cetak / Unduh PDF Format Resmi Direksi">
                        <i class="fa-solid fa-print"></i>
                        <span><?= ($current_lang === 'en') ? 'Print Official PDF' : 'Cetak Laporan PDF' ?></span>
                    </a>
                    <details class="cidp-menu">
                        <summary class="px-3 py-1.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs">
                            <i class="fa-solid fa-download text-slate-500"></i>
                            <span><?= ($current_lang === 'en') ? 'Export' : 'Ekspor' ?></span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                        </summary>
                        <div class="cidp-menu__panel">
                            <a href="docs/KATALOG_50_KEGIATAN_OPERASIONAL_CIDP.md" target="_blank">
                                <i class="fa-solid fa-list-check text-cyan-600"></i>
                                <div>
                                    <div>Katalog 50 Kegiatan (ACT-01..50)</div>
                                    <span class="hint">Matriks skenario operasional sistem</span>
                                </div>
                            </a>
                            <a href="docs/PRD_CONCLUSION_INTERMODAL_DRY_PORT.md" target="_blank">
                                <i class="fa-solid fa-file-contract text-amber-500"></i>
                                <div>
                                    <div>PRD v2.0 (Dokumen Patokan Tim)</div>
                                    <span class="hint">Spesifikasi kebutuhan & arsitektur</span>
                                </div>
                            </a>
                            <a href="report_executive.php" target="_blank">
                                <i class="fa-solid fa-file-shield text-[#0170b9]"></i>
                                <div>
                                    <div>Executive Briefing (A4)</div>
                                    <span class="hint"><?= ($current_lang === 'en') ? 'Standalone boardroom dossier' : 'Dokumen laporan direksi A4' ?></span>
                                </div>
                            </a>
                            <button type="button" onclick="exportBerandaToExcel(); this.closest('details').removeAttribute('open');">
                                <i class="fa-solid fa-file-excel text-emerald-600"></i>
                                <div>
                                    <div><?= ($current_lang === 'en') ? 'Download Excel (.XLSX)' : 'Unduh Data Excel (.XLSX)' ?></div>
                                    <span class="hint"><?= ($current_lang === 'en') ? 'Consignment & billing table' : 'Tabel konsinyasi & tagihan' ?></span>
                                </div>
                            </button>
                        </div>
                    </details>
                </div>
            </div>
        </div>

        <!-- ======================================================================= -->
        <!-- 1.5 TOC COMMAND CENTER: UNIVERSAL ASSET & CARGO LIVE LOCATOR (COMPACT)  -->
        <!-- ======================================================================= -->
        <div class="bg-white text-slate-800 rounded-xl px-4 py-3 shadow-2xs border border-slate-200 border-l-4 border-l-orange-500 relative overflow-hidden">
            <div class="relative z-10 flex flex-col xl:flex-row xl:items-center justify-between gap-2.5">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 rounded-xl bg-orange-50 border border-orange-200 text-orange-600 flex items-center justify-center text-xs relative flex-shrink-0">
                        <i class="fa-solid fa-location-crosshairs"></i>
                        <span class="w-2 h-2 rounded-full bg-emerald-500 absolute -top-0.5 -right-0.5 ring-2 ring-white"></span>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-xs sm:text-sm font-extrabold tracking-tight text-slate-900">
                                Pusat Kendali TOC: Pelacak Kargo, Palet CFS &amp; 15 Armada Real-Time
                            </h2>
                            <span class="text-[9px] font-extrabold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> 15 MHE &amp; IoT Live
                            </span>
                        </div>
                        <!-- Dynamic Quick Suggestion Pills (100% Database-Driven) -->
                        <div class="flex items-center gap-1.5 mt-1 overflow-x-auto text-[10px] text-slate-600 no-scrollbar">
                            <span class="font-bold text-slate-400 uppercase tracking-wider text-[9px] flex items-center gap-1 flex-shrink-0">
                                <i class="fa-solid fa-bolt text-orange-500"></i> Lacak Cepat:
                            </span>
                            <div id="tocQuickPills" class="flex items-center gap-1.5 flex-nowrap">
                                <?php foreach ($quick_samples as $qs): ?>
                                    <button type="button" onclick="trackSampleAsset('<?= htmlspecialchars($qs['code'], ENT_QUOTES) ?>')" class="px-2 py-0.5 rounded-md bg-slate-50 hover:bg-orange-50 border border-slate-200 hover:border-orange-200 text-slate-700 hover:text-orange-700 transition font-mono font-semibold whitespace-nowrap cursor-pointer"><?= htmlspecialchars($qs['label']) ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search Input Bar + Direct Digital Twin 3D Launch -->
                <div class="flex items-center gap-2 w-full xl:w-auto flex-wrap sm:flex-nowrap">
                    <div class="relative w-full sm:w-64 sm:flex-1">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="tocAssetSearchInput" onkeydown="if(event.key==='Enter') executeTocSearch();" placeholder="Ketik No. Kontainer / Plat Truk..." class="w-full pl-8 pr-8 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition">
                        <button type="button" id="btnTocClearSearch" onclick="clearTocSearch()" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 text-xs">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <button type="button" onclick="executeTocSearch()" class="flex-1 sm:flex-initial justify-center px-3 py-1.5 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-2xs cursor-pointer flex-shrink-0">
                        <i class="fa-solid fa-crosshairs text-[11px]"></i>
                        <span>Lacak</span>
                    </button>
                    <a href="dashboard.php?page=twin" class="flex-1 sm:flex-initial justify-center px-3 py-1.5 bg-[#0170b9] hover:bg-blue-700 text-white rounded-xl text-xs font-extrabold transition flex items-center gap-1.5 shadow-2xs flex-shrink-0" title="Buka Visualisasi Event-Driven Digital Twin 3D (Yard & Interior Gudang CFS)">
                        <i class="fa-solid fa-cubes text-[11px]"></i>
                        <span>Digital Twin 3D</span>
                    </a>
                </div>
            </div>

            <!-- Live Track Result Card (Collapsible — Hidden until searched or sample clicked) -->
            <div id="tocLiveResultPanel" class="hidden relative z-10 mt-2.5 bg-slate-50/90 rounded-xl p-3 border border-slate-200 animate-fadeIn">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                    <!-- Left: Position & Location Title (5 Cols) -->
                    <div class="md:col-span-5 border-b md:border-b-0 md:border-r border-slate-200 pb-2.5 md:pb-0 md:pr-3">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <span id="tocResBadge" class="px-2 py-0.5 rounded-md text-[9.5px] font-extrabold uppercase tracking-wider bg-sky-50 text-sky-800 border border-sky-200">IN YARD STACKING</span>
                            <span id="tocResZone" class="text-[10px] text-slate-700 font-bold bg-white border border-slate-200 px-1.5 py-0.5 rounded">Zona Z2</span>
                            <span id="tocResCargo" class="text-[10px] text-orange-700 font-bold font-mono">DRY 40FT HC</span>
                        </div>
                        <h3 id="tocResLocationTitle" class="text-sm sm:text-base font-black text-slate-900 tracking-tight flex items-center gap-1.5">
                            <i class="fa-solid fa-location-dot text-orange-600"></i>
                            <span id="tocResLocationText">Memuat Posisi Terkini...</span>
                        </h3>
                        <p id="tocResLocationDetail" class="text-[11px] text-slate-600 mt-0.5 leading-snug">
                            Sinkronisasi telemetri sensor SCADA &amp; IoT Dry Port 35 Ha
                        </p>
                    </div>

                    <!-- Middle: Fleet, Equipment, Customs & Billing (4 Cols) -->
                    <div class="md:col-span-4 border-b md:border-b-0 md:border-r border-slate-200 pb-2.5 md:pb-0 md:pr-3 text-[11px] space-y-1">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Armada Truk:</span>
                            <strong id="tocResTruck" class="font-mono text-slate-900">-</strong>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Alat Berat (VMT):</span>
                            <strong id="tocResEquip" class="text-slate-800">-</strong>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Pabean CEISA:</span>
                            <strong id="tocResCustoms" class="text-emerald-700">-</strong>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Faktur Billing:</span>
                            <strong id="tocResBilling" class="text-slate-900">-</strong>
                        </div>
                    </div>

                    <!-- Right: Journey Flow Status & Action Buttons (3 Cols) -->
                    <div class="md:col-span-3 flex flex-col justify-between h-full gap-2 text-right">
                        <div class="flex items-center justify-between md:justify-end gap-2">
                            <div class="text-[9.5px] font-bold uppercase tracking-wider text-slate-500">Alur 6-Tahap</div>
                            <div class="flex items-center gap-1">
                                <span id="flowStep_OUTSIDE" class="w-2 h-2 rounded-full bg-slate-300 transition" title="Pre-Gate Outside"></span>
                                <span class="w-1.5 h-0.5 bg-slate-200"></span>
                                <span id="flowStep_Z1" class="w-2 h-2 rounded-full bg-slate-300 transition" title="Gate In / Scale"></span>
                                <span class="w-1.5 h-0.5 bg-slate-200"></span>
                                <span id="flowStep_Z2" class="w-2 h-2 rounded-full bg-slate-300 transition" title="Yard Stacking"></span>
                                <span class="w-1.5 h-0.5 bg-slate-200"></span>
                                <span id="flowStep_Z3" class="w-2 h-2 rounded-full bg-slate-300 transition" title="CFS Warehouse"></span>
                                <span class="w-1.5 h-0.5 bg-slate-200"></span>
                                <span id="flowStep_Z4" class="w-2 h-2 rounded-full bg-slate-300 transition" title="Reefer / Rail"></span>
                                <span class="w-1.5 h-0.5 bg-slate-200"></span>
                                <span id="flowStep_GATE_OUT" class="w-2 h-2 rounded-full bg-slate-300 transition" title="Gate Out"></span>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-1.5 flex-wrap">
                            <button type="button" id="btnTocInspectDetail" onclick="inspectCurrentTocAsset()" class="px-2.5 py-1 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-[10.5px] font-bold transition flex items-center gap-1 cursor-pointer shadow-2xs">
                                <i class="fa-solid fa-eye text-[9.5px]"></i>
                                <span>3-Aliran</span>
                            </button>
                            <a id="btnTocTwinLink" href="dashboard.php?page=twin" class="px-2.5 py-1 rounded-lg bg-[#0170b9] hover:bg-blue-700 text-white text-[10.5px] font-bold transition flex items-center gap-1 shadow-2xs">
                                <i class="fa-solid fa-cubes text-[9.5px]"></i>
                                <span>Twin 3D</span>
                            </a>
                            <a id="btnTocYardLink" href="dashboard.php?page=yard" class="px-2 py-1 rounded-lg bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 text-[10.5px] font-bold transition flex items-center gap-1">
                                <i class="fa-solid fa-boxes-stacked text-[9.5px]"></i>
                                <span>Yard</span>
                            </a>
                            <button type="button" onclick="closeTocResultPanel()" class="px-2 py-1 rounded-lg bg-slate-200/80 hover:bg-slate-300 text-slate-600 text-[10px] font-bold transition cursor-pointer" title="Sembunyikan Panel Pelacak">
                                <i class="fa-solid fa-chevron-up"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($beranda_decisions)): ?>
        <!-- ======================================================================= -->
        <!-- 1.8 PANEL KEPUTUSAN OPERASIONAL (OLAHAN DATA HARDWARE -> REKOMENDASI)    -->
        <!-- ======================================================================= -->
        <div class="bg-white rounded-xl p-3.5 shadow-2xs border border-slate-200 space-y-2.5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-slate-900 text-amber-400 flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-brain"></i>
                    </span>
                    <div>
                        <h3 class="text-xs sm:text-[13px] font-extrabold text-slate-900">Keputusan &amp; Rekomendasi Operasional (Sensor &amp; Rule Engine)</h3>
                        <p class="text-[10.5px] text-slate-500">Hasil olahan telemetri LoRaWAN, Timbangan 80T, CEISA/E-Seal JT701, Dwell Time &amp; DLQ secara real-time.</p>
                    </div>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    <?= count($beranda_decisions) ?> Keputusan Aktif
                </span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-2">
                <?php foreach (array_slice($beranda_decisions, 0, 3) as $dec):
                    $prioCls = $dec['priority'] === 'critical'
                        ? 'border-rose-200 bg-rose-50/70'
                        : ($dec['priority'] === 'high' ? 'border-amber-200 bg-amber-50/60' : ($dec['priority'] === 'medium' ? 'border-blue-200 bg-blue-50/50' : 'border-emerald-200 bg-emerald-50/60'));
                    $badgeCls = $dec['priority'] === 'critical'
                        ? 'bg-rose-600 text-white'
                        : ($dec['priority'] === 'high' ? 'bg-amber-600 text-white' : ($dec['priority'] === 'medium' ? 'bg-[#0170b9] text-white' : 'bg-emerald-600 text-white'));
                ?>
                <div class="rounded-xl p-3 border <?= $prioCls ?> flex flex-col justify-between gap-2 text-xs">
                    <div class="space-y-1">
                        <div class="flex items-center justify-between gap-1.5">
                            <span class="text-[9.5px] font-mono font-bold text-slate-600 truncate"><?= htmlspecialchars($dec['source']) ?></span>
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold uppercase <?= $badgeCls ?> shrink-0"><?= htmlspecialchars($dec['priority']) ?></span>
                        </div>
                        <div class="font-extrabold text-slate-900 text-xs leading-snug"><?= htmlspecialchars($dec['title']) ?></div>
                        <p class="text-[11px] text-slate-600 leading-snug"><?= htmlspecialchars($dec['reason']) ?></p>
                    </div>
                    <div class="pt-1.5 border-t border-slate-200/70 flex items-center justify-between gap-2">
                        <span class="text-[10.5px] font-semibold text-slate-800 truncate" title="<?= htmlspecialchars($dec['action']) ?>">
                            <i class="fa-solid fa-arrow-right text-[9px] text-orange-600 mr-1"></i><?= htmlspecialchars($dec['action']) ?>
                        </span>
                        <a href="<?= htmlspecialchars($dec['href']) ?>" class="px-2 py-0.5 rounded-md bg-white hover:bg-slate-900 text-slate-800 hover:text-white border border-slate-300 text-[10px] font-bold whitespace-nowrap transition shrink-0">
                            <?= htmlspecialchars($dec['cta']) ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- ======================================================================= -->
        <!-- 2. TOP METRIC ROW (6 UNIFIED KPI CARDS - BALANCED & HIGH-CONTRAST)     -->
        <!-- ======================================================================= -->
        <div class="cidp-kpi-6-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6 gap-3">
            
            <!-- KPI 1: Yard Stacking (Tahap 3 Alur Multimoda - Blok A-E) -->
            <div id="kpiCard_yard" onclick="setBerandaStageFilter('stage_3')" class="cursor-pointer bg-white rounded-xl p-3.5 shadow-2xs border border-slate-200 hover:border-blue-400 hover:shadow-sm transition flex flex-col justify-between group overflow-hidden min-w-0" title="Klik untuk memfilter Tahap 3 (Stacking Yard Blok A-E)">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-600 group-hover:text-[#0170b9] transition"><?= __('kpi_yard_stacking', 'Yard Stacking') ?></span>
                    <span class="w-7 h-7 rounded-lg bg-blue-50 text-[#0170b9] border border-blue-100 flex items-center justify-center text-xs group-hover:scale-105 transition-transform flex-shrink-0">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </span>
                </div>
                
                <div class="my-2 flex items-center justify-between gap-2">
                    <div>
                        <h3 id="kpiYardVal" class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-none"><?= number_format($containers_yard) ?></h3>
                        <span class="text-xs text-slate-600 font-semibold mt-1 block">/ <?= number_format($yard_cap_max) ?> TEU</span>
                    </div>
                    <div class="relative w-12 h-12 flex-shrink-0 flex items-center justify-center">
                        <svg class="w-12 h-12 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-slate-100" stroke-width="3.8" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path id="kpiYardRing" class="text-[#0170b9] transition-all duration-500" stroke-dasharray="<?= max(10, min(100, (int)$yard_occupancy)) ?>, 100" stroke-width="3.8" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <span id="kpiYardPct" class="absolute text-xs font-extrabold text-slate-800 font-mono"><?= $yard_occupancy ?>%</span>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center justify-between gap-x-2 gap-y-0.5 text-[11px]">
                    <span class="text-emerald-700 font-bold flex items-center gap-1">
                        <i class="fa-solid fa-arrow-trend-up text-[10px]"></i>
                        <span>4 E-RTG + 4 RS</span>
                    </span>
                    <span class="font-semibold text-slate-600">Blok A–E Siaga</span>
                </div>
            </div>

            <!-- KPI 2: Gate Turnaround Time -->
            <div id="kpiCard_gate" onclick="setBerandaStageFilter('stage_1')" class="cursor-pointer bg-white rounded-xl p-3.5 shadow-2xs border border-slate-200 hover:border-orange-400 hover:shadow-sm transition flex flex-col justify-between group overflow-hidden min-w-0" title="Klik untuk memfilter antrean & aktivitas Gerbang ANPR / Timbangan VGM">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-600 group-hover:text-[#ea580c] transition"><?= __('kpi_gate_turnaround', 'Gate Turnaround') ?></span>
                    <span class="w-7 h-7 rounded-lg bg-orange-50 text-[#ea580c] border border-orange-100 flex items-center justify-center text-xs group-hover:scale-105 transition-transform flex-shrink-0">
                        <i class="fa-solid fa-truck-fast"></i>
                    </span>
                </div>

                <div class="my-2 flex items-center justify-between gap-2">
                    <div>
                        <h3 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-none"><span id="kpiGateVal"><?= $gate_turnaround ?></span> <span class="text-xs font-bold text-slate-500"><?= ($current_lang === 'en') ? 'min' : 'mnt' ?></span></h3>
                        <span class="text-xs text-emerald-700 font-semibold mt-1 block"><?= __('kpi_gate_target', 'Target &le; 20 mnt') ?></span>
                    </div>
                    <div class="relative w-12 h-12 flex-shrink-0 flex items-center justify-center">
                        <svg class="w-12 h-12 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-slate-100" stroke-width="3.8" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="text-[#ea580c] transition-all duration-500" stroke-dasharray="91, 100" stroke-width="3.8" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <span id="kpiGateGaugeTxt" class="absolute text-xs font-extrabold text-orange-700 font-mono"><?= $gate_turnaround ?>m</span>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center justify-between gap-x-2 gap-y-0.5 text-[11px]">
                    <span class="font-bold text-slate-700"><span id="kpiTrucksCount"><?= $total_trucks ?></span> <?= __('kpi_gate_trucks', 'Truk Aktif') ?></span>
                    <span class="text-emerald-700 font-bold"><?= __('kpi_gate_vbs', 'VBS 98% Tepat') ?></span>
                </div>
            </div>

            <!-- KPI 3: Siding KA Intermodal -->
            <div id="kpiCard_rail" onclick="setBerandaStageFilter('stage_6')" class="cursor-pointer bg-white rounded-xl p-3.5 shadow-2xs border border-slate-200 hover:border-sky-400 hover:shadow-sm transition flex flex-col justify-between group overflow-hidden min-w-0" title="Klik untuk memfilter kontainer di Jalur Rel Siding KA 2518">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-600 group-hover:text-[#0170b9] transition"><?= __('kpi_rail_siding', 'Siding KA Intermodal') ?></span>
                    <span class="w-7 h-7 rounded-lg bg-blue-50 text-[#0170b9] border border-blue-100 flex items-center justify-center text-xs group-hover:scale-105 transition-transform flex-shrink-0">
                        <i class="fa-solid fa-train"></i>
                    </span>
                </div>

                <div class="my-2 flex items-center justify-between gap-2">
                    <div>
                        <h3 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-none"><span id="kpiRailVal"><?= $loaded_w ?>/<?= $total_w ?></span> <span class="text-xs font-bold text-slate-500">TEU</span></h3>
                        <span class="text-xs text-[#0170b9] font-semibold mt-1 block">RMGC RTG-01 &bull; KA Pasoso</span>
                    </div>
                    <div class="relative w-12 h-12 flex-shrink-0 flex items-center justify-center">
                        <svg class="w-12 h-12 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-slate-100" stroke-width="3.8" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path id="kpiRailRing" class="text-[#0170b9] transition-all duration-500" stroke-dasharray="<?= max(10, (int)$siding_pct) ?>, 100" stroke-width="3.8" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <span id="kpiRailPct" class="absolute text-xs font-extrabold text-slate-800 font-mono"><?= $siding_pct ?>%</span>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center justify-between gap-x-2 gap-y-0.5 text-[11px]">
                    <span class="font-semibold text-slate-600"><?= __('kpi_rail_track', 'Track S-01 (600m)') ?></span>
                    <span class="font-bold text-orange-600"><?= __('kpi_rail_eta', 'ETA 16:45 WIB') ?></span>
                </div>
            </div>

            <!-- KPI 4: Reefer Cold Chain -->
            <div id="kpiCard_reefer" onclick="setBerandaStageFilter('stage_5')" class="cursor-pointer bg-white rounded-xl p-3.5 shadow-2xs border border-slate-200 hover:border-cyan-400 hover:shadow-sm transition flex flex-col justify-between group overflow-hidden min-w-0" title="Klik untuk memfilter kontainer berpendingin Reefer 380V">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-600 group-hover:text-cyan-700 transition"><?= __('kpi_reefer_chain', 'Reefer Cold Chain') ?></span>
                    <span class="w-7 h-7 rounded-lg bg-cyan-50 text-cyan-600 border border-cyan-100 flex items-center justify-center text-xs group-hover:scale-105 transition-transform flex-shrink-0">
                        <i class="fa-solid fa-snowflake"></i>
                    </span>
                </div>

                <div class="my-2 flex items-center justify-between gap-2">
                    <div>
                        <h3 id="kpiReeferTemp" class="text-xl sm:text-2xl font-black text-cyan-700 font-mono tracking-tight leading-none"><?= $reefer_temp ?>&deg;C</h3>
                        <span class="text-xs text-slate-600 font-semibold mt-1 block"><span id="kpiReeferPlugs" class="font-bold text-cyan-800"><?= $reefer_plugs ?></span> / 300 <?= __('kpi_reefer_plugs', 'Steker') ?></span>
                    </div>
                    <div class="relative w-12 h-12 flex-shrink-0 flex items-center justify-center">
                        <svg class="w-12 h-12 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-slate-100" stroke-width="3.8" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path id="kpiReeferRing" class="text-cyan-600 transition-all duration-500" stroke-dasharray="<?= max(12, min(100, (int)$reefer_pct)) ?>, 100" stroke-width="3.8" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <span id="kpiReeferPct" class="absolute text-xs font-extrabold text-cyan-900 font-mono"><?= round($reefer_pct) ?>%</span>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center justify-between gap-x-2 gap-y-0.5 text-[11px]">
                    <span class="font-semibold text-slate-600">RS-03 &bull; 380V</span>
                    <span class="text-emerald-700 font-bold"><?= __('kpi_reefer_haccp', 'HACCP 98.8% OK') ?></span>
                </div>
            </div>

            <!-- KPI 5: Bea Cukai CEISA 4.0 -->
            <div id="kpiCard_customs" onclick="setBerandaCategoryFilter('customs_hold')" class="cursor-pointer bg-white rounded-xl p-3.5 shadow-2xs border border-slate-200 hover:border-emerald-400 hover:shadow-sm transition flex flex-col justify-between group overflow-hidden min-w-0" title="Klik untuk memfilter status kepabeanan CEISA 4.0">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-600 group-hover:text-emerald-700 transition"><?= __('kpi_customs_ceisa', 'Bea Cukai CEISA 4.0') ?></span>
                    <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-xs group-hover:scale-105 transition-transform flex-shrink-0">
                        <i class="fa-solid fa-shield-halved"></i>
                    </span>
                </div>

                <div class="my-2 flex items-center justify-between gap-2">
                    <div>
                        <h3 id="kpiCustomsVal" class="text-xl sm:text-2xl font-black text-emerald-700 tracking-tight leading-none"><?= number_format($customs_percent, 1) ?>%</h3>
                        <span class="text-xs text-slate-600 font-semibold mt-1 block"><span id="kpiCustomsBox" class="font-bold text-emerald-800"><?= $customs_cleared ?>/<?= $customs_total ?></span> <?= __('kpi_customs_cleared', 'Box Rilis') ?></span>
                    </div>
                    <div class="relative w-12 h-12 flex-shrink-0 flex items-center justify-center">
                        <svg class="w-12 h-12 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-slate-100" stroke-width="3.8" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="text-emerald-500 transition-all duration-500" stroke-dasharray="<?= max(12, min(100, (int)$customs_percent)) ?>, 100" stroke-width="3.8" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <span class="absolute text-xs font-extrabold text-emerald-900 font-mono"><?= round($customs_percent) ?>%</span>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center justify-between gap-x-2 gap-y-0.5 text-[11px]">
                    <span class="font-semibold text-slate-600"><?= __('kpi_customs_release', 'SPPB Otomatis') ?></span>
                    <span class="text-emerald-700 font-bold"><?= __('kpi_customs_xray', 'X-Ray 6MeV OK') ?></span>
                </div>
            </div>

            <!-- KPI 6: Gudang CFS (Palet SSCC-18) & Billing -->
            <div id="kpiCard_cfs" onclick="setBerandaStageFilter('stage_4')" class="cursor-pointer bg-white rounded-xl p-3.5 shadow-2xs border border-slate-200 hover:border-amber-400 hover:shadow-sm transition flex flex-col justify-between group overflow-hidden min-w-0" title="Klik untuk memfilter pekerjaan Gudang CFS Stripping/Stuffing & Palet SSCC-18">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-600 group-hover:text-amber-700 transition">Gudang CFS &amp; LCL</span>
                    <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center text-xs group-hover:scale-105 transition-transform flex-shrink-0">
                        <i class="fa-solid fa-warehouse"></i>
                    </span>
                </div>

                <div class="my-2 flex items-center justify-between gap-2">
                    <div>
                        <h3 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-none"><span id="kpiCfsPalletsRack"><?= $cfs_pallets_rack ?></span> <span class="text-xs font-bold text-emerald-700">Palet</span></h3>
                        <span class="text-[11px] text-amber-700 font-semibold mt-1 block"><span id="kpiCfsJobs"><?= $cfs_jobs ?></span> Job &bull; <span id="kpiCfsWeightTxt"><?= number_format($cfs_weight_kg, 0, ',', '.') ?></span> kg</span>
                    </div>
                    <div class="relative w-12 h-12 flex-shrink-0 flex items-center justify-center">
                        <svg class="w-12 h-12 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-slate-100" stroke-width="3.8" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path id="kpiCfsRing" class="text-amber-500 transition-all duration-500" stroke-dasharray="<?= max(12, min(100, (int)$cfs_pct)) ?>, 100" stroke-width="3.8" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <span id="kpiCfsPct" class="absolute text-xs font-extrabold text-amber-900 font-mono"><?= $cfs_pct ?>%</span>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center justify-between gap-x-2 gap-y-0.5 text-[11px]">
                    <span class="font-semibold text-slate-600">Rak A–F &bull; FL-01..04</span>
                    <span id="kpiBillingPaid" class="text-emerald-700 font-extrabold"><?= ($current_lang === 'en') ? 'Rp ' . number_format($paid_rev / 1000000, 1) . 'M Paid' : 'Rp ' . number_format($paid_rev / 1000000, 1) . 'Jt Lunas' ?></span>
                </div>
            </div>

        </div>

        <!-- ======================================================================= -->
        <!-- 3. COMPACT 6-STAGE PIPELINE & LIVE 15-UNIT MHE FLEET TELEMETRY BAR      -->
        <!-- ======================================================================= -->
        <div class="bg-white rounded-xl p-3.5 shadow-2xs border border-slate-200 relative space-y-2.5">
            <!-- Row 1: Compact 6-Stage Multimodal Filter Ribbon -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-2 pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-extrabold text-slate-900 flex items-center gap-1.5 mr-1">
                        <i class="fa-solid fa-route text-[#ea580c]"></i>
                        <span>Filter Alur 6-Tahap:</span>
                    </span>
                    <button type="button" id="ribbon_stage_1" onclick="setBerandaStageFilter('stage_1')" class="pipeline-stage-card px-2.5 py-1 rounded-lg border border-slate-200 bg-slate-50 hover:bg-orange-50 hover:border-orange-300 text-[11px] font-bold text-slate-700 transition flex items-center gap-1.5 cursor-pointer">
                        <span class="w-4 h-4 rounded bg-[#0170b9] text-white text-[10px] font-mono font-black inline-flex items-center justify-center">1</span>
                        <span>Gate ANPR</span>
                    </button>
                    <i class="fa-solid fa-chevron-right text-[9px] text-slate-300 hidden sm:inline"></i>
                    <button type="button" id="ribbon_stage_2" onclick="setBerandaStageFilter('stage_2')" class="pipeline-stage-card px-2.5 py-1 rounded-lg border border-slate-200 bg-slate-50 hover:bg-orange-50 hover:border-orange-300 text-[11px] font-bold text-slate-700 transition flex items-center gap-1.5 cursor-pointer">
                        <span class="w-4 h-4 rounded bg-orange-600 text-white text-[10px] font-mono font-black inline-flex items-center justify-center">2</span>
                        <span>Timbangan 80T</span>
                    </button>
                    <i class="fa-solid fa-chevron-right text-[9px] text-slate-300 hidden sm:inline"></i>
                    <button type="button" id="ribbon_stage_3" onclick="setBerandaStageFilter('stage_3')" class="pipeline-stage-card px-2.5 py-1 rounded-lg border border-slate-200 bg-slate-50 hover:bg-blue-50 hover:border-blue-300 text-[11px] font-bold text-slate-700 transition flex items-center gap-1.5 cursor-pointer">
                        <span class="w-4 h-4 rounded bg-[#0170b9] text-white text-[10px] font-mono font-black inline-flex items-center justify-center">3</span>
                        <span>Yard Blok A–E (<?= number_format($containers_yard) ?>)</span>
                    </button>
                    <i class="fa-solid fa-chevron-right text-[9px] text-slate-300 hidden sm:inline"></i>
                    <button type="button" id="ribbon_stage_4" onclick="setBerandaStageFilter('stage_4')" class="pipeline-stage-card px-2.5 py-1 rounded-lg border border-slate-200 bg-slate-50 hover:bg-amber-50 hover:border-amber-300 text-[11px] font-bold text-slate-700 transition flex items-center gap-1.5 cursor-pointer">
                        <span class="w-4 h-4 rounded bg-amber-600 text-white text-[10px] font-mono font-black inline-flex items-center justify-center">4</span>
                        <span>Gudang CFS (<span id="ribbonCfsPalletCount"><?= $cfs_pallets_rack ?></span> Palet)</span>
                    </button>
                    <i class="fa-solid fa-chevron-right text-[9px] text-slate-300 hidden sm:inline"></i>
                    <button type="button" id="ribbon_stage_5" onclick="setBerandaStageFilter('stage_5')" class="pipeline-stage-card px-2.5 py-1 rounded-lg border border-slate-200 bg-slate-50 hover:bg-cyan-50 hover:border-cyan-300 text-[11px] font-bold text-slate-700 transition flex items-center gap-1.5 cursor-pointer">
                        <span class="w-4 h-4 rounded bg-cyan-600 text-white text-[10px] font-mono font-black inline-flex items-center justify-center">5</span>
                        <span>Reefer 380V (<?= $reefer_plugs ?>)</span>
                    </button>
                    <i class="fa-solid fa-chevron-right text-[9px] text-slate-300 hidden sm:inline"></i>
                    <button type="button" id="ribbon_stage_6" onclick="setBerandaStageFilter('stage_6')" class="pipeline-stage-card px-2.5 py-1 rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-300 text-[11px] font-bold text-slate-700 transition flex items-center gap-1.5 cursor-pointer">
                        <span class="w-4 h-4 rounded bg-emerald-600 text-white text-[10px] font-mono font-black inline-flex items-center justify-center">6</span>
                        <span>Siding KA (<?= $loaded_w ?>/<?= $total_w ?>)</span>
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" id="btnResetPipelineFilter" onclick="resetBerandaFilters()" class="hidden text-[11px] font-extrabold text-orange-700 bg-orange-50 hover:bg-orange-100 px-2.5 py-1 rounded-lg border border-orange-300 transition cursor-pointer">
                        <i class="fa-solid fa-filter-circle-xmark mr-1"></i>Reset Filter
                    </button>
                    <a href="dashboard.php?page=alat" class="text-[11px] font-bold text-[#0170b9] hover:underline flex items-center gap-1 whitespace-nowrap">
                        <span>Inventaris 15 MHE &rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Row 2: Live 15-Unit MHE Fleet & CFS Interior Summary Strip -->
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-2 text-[11px]">
                <a href="dashboard.php?page=alat" class="p-2 rounded-lg bg-orange-50/50 border border-orange-200/80 hover:border-orange-400 transition flex items-center justify-between">
                    <div>
                        <div class="text-[9.5px] font-bold text-orange-800 uppercase">Reach Stacker 45T</div>
                        <div class="font-extrabold text-slate-900 font-mono mt-0.5"><span id="mheRsCount"><?= $mhe['rs_total'] ?></span> Unit (RS-01..04)</div>
                    </div>
                    <span class="px-1.5 py-0.5 rounded bg-white border border-orange-200 text-orange-700 font-mono font-bold text-[9.5px]">Yard A–E</span>
                </a>
                <a href="dashboard.php?page=alat" class="p-2 rounded-lg bg-blue-50/50 border border-blue-200/80 hover:border-blue-400 transition flex items-center justify-between">
                    <div>
                        <div class="text-[9.5px] font-bold text-[#0170b9] uppercase">E-RTG &amp; Rail Gantry</div>
                        <div class="font-extrabold text-slate-900 font-mono mt-0.5"><span id="mheRtgCount"><?= $mhe['rtg_total'] ?></span> Unit (RTG-01..05)</div>
                    </div>
                    <span class="px-1.5 py-0.5 rounded bg-white border border-blue-200 text-[#0170b9] font-mono font-bold text-[9.5px]">Zero-Emisi</span>
                </a>
                <a href="dashboard.php?page=cfs" class="p-2 rounded-lg bg-emerald-50/50 border border-emerald-200/80 hover:border-emerald-400 transition flex items-center justify-between">
                    <div>
                        <div class="text-[9.5px] font-bold text-emerald-800 uppercase">Forklift Gudang CFS</div>
                        <div class="font-extrabold text-slate-900 font-mono mt-0.5"><span id="mheFlCount"><?= $mhe['fl_total'] ?></span> Unit (FL-01..04)</div>
                    </div>
                    <span class="px-1.5 py-0.5 rounded bg-white border border-emerald-200 text-emerald-700 font-mono font-bold text-[9.5px]">CFS &amp; Blok E</span>
                </a>
                <a href="dashboard.php?page=alat" class="p-2 rounded-lg bg-purple-50/50 border border-purple-200/80 hover:border-purple-400 transition flex items-center justify-between">
                    <div>
                        <div class="text-[9.5px] font-bold text-purple-800 uppercase">Shuttle Tractor</div>
                        <div class="font-extrabold text-slate-900 font-mono mt-0.5"><span id="mheTtCount"><?= $mhe['tt_total'] ?></span> Unit (CDP-TT-01..02)</div>
                    </div>
                    <span class="px-1.5 py-0.5 rounded bg-white border border-purple-200 text-purple-700 font-mono font-bold text-[9.5px]">Langsir KA</span>
                </a>
                <a href="dashboard.php?page=cfs" class="p-2 rounded-lg bg-amber-50/50 border border-amber-200/80 hover:border-amber-400 transition flex items-center justify-between">
                    <div>
                        <div class="text-[9.5px] font-bold text-amber-800 uppercase">Interior Gudang CFS</div>
                        <div class="font-extrabold text-slate-900 font-mono mt-0.5"><span id="stripCfsPallets"><?= $cfs_pallets_rack ?></span> Palet &bull; <span id="stripCfsCbm"><?= number_format($cfs_volume_cbm, 1) ?></span> CBM</div>
                    </div>
                    <span class="px-1.5 py-0.5 rounded bg-white border border-amber-200 text-amber-800 font-mono font-bold text-[9.5px]">Rak A–F</span>
                </a>
                <a href="dashboard.php?page=twin" class="p-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white transition flex items-center justify-between shadow-2xs">
                    <div>
                        <div class="text-[9.5px] font-bold text-cyan-300 uppercase">Total 15 Unit MHE</div>
                        <div class="font-extrabold text-white font-mono mt-0.5"><span id="mheActiveSummary"><?= $mhe['active_units'] ?> Aktif • <?= $mhe['idle_units'] ?> Siaga</span></div>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-orange-600 text-white font-bold text-[9.5px] flex items-center gap-1">
                        <i class="fa-solid fa-cubes"></i> 3D
                    </span>
                </a>
            </div>
        </div>

        <!-- ======================================================================= -->
        <!-- 3B. QUANTITATIVE SLA PERFORMANCE STRIP (OTIF, MAE/MAPE, CYCLE TIME, ROI)-->
        <!-- ======================================================================= -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-slate-200 px-3.5 py-2.5 shadow-2xs flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <div class="text-[9.5px] font-bold uppercase tracking-wider text-slate-400">Target OTIF (On-Time In-Full)</div>
                    <div class="flex items-baseline gap-2 mt-0.5">
                        <span id="perfOtifVal" class="text-lg font-black text-emerald-700 font-mono"><?= $perf['otif_rate_pct'] ?>%</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">Target &ge;<?= $perf['otif_target_pct'] ?>%</span>
                    </div>
                    <div class="text-[10px] text-slate-500 mt-0.5">Tepat waktu jadwal &amp; SPPB tanpa hold</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-bullseye"></i>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 px-3.5 py-2.5 shadow-2xs flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <div class="text-[9.5px] font-bold uppercase tracking-wider text-slate-400">Akurasi Forecast (MAE &amp; MAPE)</div>
                    <div class="flex items-baseline gap-2 mt-0.5">
                        <span id="perfMapeVal" class="text-lg font-black text-[#0170b9] font-mono">MAPE <?= $perf['forecast_mape_pct'] ?>%</span>
                        <span id="perfMaeBadge" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-50 text-[#0170b9] border border-blue-200 font-mono">MAE <?= $perf['forecast_mae'] ?></span>
                    </div>
                    <div class="text-[10px] text-slate-500 mt-0.5">Akurasi VBS: <strong id="perfAccVal" class="text-slate-800"><?= $perf['forecast_accuracy_pct'] ?>%</strong> (&lt;10% Sangat Akurat)</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-200 text-[#0170b9] flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 px-3.5 py-2.5 shadow-2xs flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <div class="text-[9.5px] font-bold uppercase tracking-wider text-slate-400">Efisiensi Cycle Time</div>
                    <div class="flex items-baseline gap-2 mt-0.5">
                        <span id="perfCycleEffVal" class="text-lg font-black text-indigo-700 font-mono">-<?= $perf['cycle_efficiency_pct'] ?>%</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-200 font-mono">TAT <span id="perfGateTatVal"><?= $perf['gate_tat_min'] ?></span>m</span>
                    </div>
                    <div class="text-[10px] text-slate-500 mt-0.5">vs SLA Manual 35m &bull; CEISA SPPB 14.2 dtk</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-600 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-stopwatch"></i>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 px-3.5 py-2.5 shadow-2xs flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <div class="text-[9.5px] font-bold uppercase tracking-wider text-slate-400">Efisiensi Biaya &amp; ROI</div>
                    <div class="flex items-baseline gap-2 mt-0.5">
                        <span id="perfCostEffVal" class="text-lg font-black text-amber-700 font-mono"><?= $perf['cost_efficiency_pct'] ?>%</span>
                        <span id="perfSavingVal" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200 font-mono">Rp <?= number_format($perf['monthly_saving_idr'] / 1000000, 1, ',', '.') ?> Jt/bln</span>
                    </div>
                    <div class="text-[10px] text-slate-500 mt-0.5">Modal-Split KA &bull; <strong>0%</strong> Kebocoran Pendapatan</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>
        </div>

        <!-- ======================================================================= -->
        <!-- 4. MID ANALYTICAL VISUAL GRID (CHARTS & DIAGRAMS - 4 COLUMNS)          -->
        <!-- ======================================================================= -->
        <div class="cidp-chart-4-grid grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-4 gap-3.5">
            
            <!-- Col 1: Gate Throughput vs VBS Hourly Demand -->
            <div class="bg-white rounded-xl shadow-2xs border border-slate-200 p-3 sm:p-3.5 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-1 pb-1.5 border-b border-slate-100">
                        <h3 class="font-extrabold text-slate-900 text-xs flex items-center">
                            <i class="fa-solid fa-chart-area mr-1.5 text-[#0170b9] text-xs"></i>Gate vs VBS Demand
                        </h3>
                        <span class="text-[8.5px] font-bold text-orange-700 bg-orange-50 px-1.5 py-0.5 rounded border border-orange-200 font-mono">
                            Peak: 14:00 - 16:00
                        </span>
                    </div>
                    <p class="text-[10px] text-slate-400 mb-2">Throughput aktual vs kuota VBS / 2 jam.</p>
                </div>
                
                <div class="h-44 sm:h-48 relative w-full flex-1">
                    <canvas id="gateVbsAreaChart"></canvas>
                </div>

                <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9.5px] text-slate-600">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#0170b9]"></span>Aktual: <strong><?= $total_trucks ?> Truk</strong></span>
                    <span class="font-mono font-bold text-slate-700">MAE: <span id="chartVbsMae"><?= $perf['forecast_mae'] ?></span></span>
                    <span class="text-emerald-600 font-bold font-mono">MAPE: <span id="chartVbsMape"><?= $perf['forecast_mape_pct'] ?>%</span></span>
                </div>
            </div>

            <!-- Col 2: Heavy Equipment SCADA OEE (15 Unit Armada MHE) -->
            <div class="bg-white/95 backdrop-blur-xs rounded-xl shadow-2xs border border-slate-200/90 p-3 sm:p-3.5 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -right-8 -top-8 w-32 h-32 pointer-events-none opacity-[0.04] z-0" aria-hidden="true">
                    <svg width="100%" height="100%" viewBox="0 0 100 100" fill="none" stroke="#ea580c" stroke-width="1.5">
                        <circle cx="50" cy="50" r="44" stroke-dasharray="3 3" />
                        <circle cx="50" cy="50" r="30" />
                        <circle cx="50" cy="50" r="16" stroke="#0170b9" />
                    </svg>
                </div>
                <div class="relative z-10">
                    <div class="flex justify-between items-center mb-1 pb-1.5 border-b border-slate-100">
                        <h3 class="font-extrabold text-slate-900 text-xs flex items-center">
                            <i class="fa-solid fa-chart-radar mr-1.5 text-orange-600 text-xs"></i>15 Armada MHE SCADA OEE
                        </h3>
                        <a href="dashboard.php?page=alat" class="text-[8.5px] font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 px-1.5 py-0.5 rounded border border-blue-200 font-mono transition">
                            15 Unit MHE &rarr;
                        </a>
                    </div>
                    <p class="text-[10px] text-slate-400 mb-2">OEE 5 sumbu: 4 RS/2 TT vs 5 E-RTG vs 4 Forklift CFS.</p>
                </div>

                <div class="h-44 sm:h-48 relative w-full flex-1 flex items-center justify-center">
                    <canvas id="scadaEquipmentRadarChart"></canvas>
                </div>

                <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9.5px]">
                    <span class="text-[#0170b9] font-bold flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-[#0170b9]"></span>RS/TT (6): <strong>93.0%</strong>
                    </span>
                    <span class="text-[#ea580c] font-bold flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-[#ea580c]"></span>E-RTG (5): <strong>93.2%</strong>
                    </span>
                    <span class="text-emerald-700 font-bold flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>FL CFS (4): <strong>95.8%</strong>
                    </span>
                </div>
            </div>

            <!-- Col 3: Container Dwelling Time & Demurrage Risk -->
            <div class="bg-white rounded-xl shadow-2xs border border-slate-200 p-3 sm:p-3.5 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-1 pb-1.5 border-b border-slate-100">
                        <h3 class="font-extrabold text-slate-900 text-xs flex items-center">
                            <i class="fa-solid fa-hourglass-half mr-1.5 text-emerald-600 text-xs"></i>Dwelling &amp; Demurrage
                        </h3>
                        <button type="button" onclick="setBerandaCategoryFilter('demurrage')" class="text-[8.5px] font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 px-1.5 py-0.5 rounded border border-rose-200 font-mono cursor-pointer transition">
                            <?= $fin['demurrage_box_count'] ?> Box Denda &rarr;
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-400 mb-2">Durasi inap aktual vs batas bebas denda 3 hari.</p>
                </div>

                <div class="h-44 sm:h-48 relative w-full flex-1">
                    <canvas id="dwellingTimeBarChart"></canvas>
                </div>

                <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9.5px] text-slate-600">
                    <span class="text-emerald-600 font-bold">1-2H: <span id="dwSafePct"><?= $dwell_b['pct_1_2'] ?>%</span> Safe</span>
                    <span class="text-amber-600 font-bold">3H: <span id="dwWarnPct"><?= $dwell_b['pct_3'] ?>%</span> Warn</span>
                    <span class="text-rose-600 font-bold">4+H: <span id="dwDendaPct"><?= $dwell_b['pct_4p'] ?>%</span> Denda</span>
                </div>
            </div>

            <!-- Col 4: Multimodal Modal-Split & ESG -->
            <div class="bg-white rounded-xl shadow-2xs border border-slate-200 p-3 sm:p-3.5 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-1 pb-1.5 border-b border-slate-100">
                        <h3 class="font-extrabold text-slate-900 text-xs flex items-center">
                            <i class="fa-solid fa-leaf mr-1.5 text-emerald-600 text-xs"></i>Modal-Split &amp; ESG
                        </h3>
                        <span class="text-[8.5px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 font-mono">
                            -1.84 T CO&sup2;
                        </span>
                    </div>
                    <p class="text-[10px] text-slate-400 mb-2">Pangsa moda ramah lingkungan Kereta vs Truk.</p>
                </div>

                <div class="h-44 sm:h-48 relative w-full flex-1 flex items-center justify-center">
                    <canvas id="modalSplitDonutChart"></canvas>
                </div>

                <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9.5px]">
                    <span class="text-[#0170b9] font-bold">68% Kereta Api</span>
                    <span class="text-[#ea580c] font-bold">32% Truk Jalan</span>
                    <span class="text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">
                        48 Truk Offset
                    </span>
                </div>
            </div>

        </div>

        <!-- ======================================================================= -->
        <!-- CONTROL TOWER WORKSPACE TABS (DECLUTTERED PORT TOS VIEW SWITCHER)       -->
        <!-- ======================================================================= -->
        <div class="bg-white rounded-xl p-2 shadow-2xs border border-slate-200 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-1.5 flex-wrap">
                <button type="button" id="workspaceTabBtn_ops" onclick="switchBerandaWorkspaceTab('ops')" class="px-3 py-1.5 rounded-lg text-xs font-extrabold transition flex items-center gap-1.5 bg-[#0170b9] text-white shadow-2xs cursor-pointer">
                    <i class="fa-solid fa-satellite-dish"></i>
                    <span>1. Konsinyasi &amp; Telemetri SCADA Live</span>
                </button>
                <button type="button" id="workspaceTabBtn_zones" onclick="switchBerandaWorkspaceTab('zones')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 cursor-pointer">
                    <i class="fa-solid fa-map-location-dot text-orange-600"></i>
                    <span>2. Distribusi 4 Zona, Gudang CFS &amp; CEISA 4.0</span>
                </button>
                <button type="button" id="workspaceTabBtn_finance" onclick="switchBerandaWorkspaceTab('finance')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 cursor-pointer">
                    <i class="fa-solid fa-file-invoice-dollar text-emerald-600"></i>
                    <span>3. Buku Besar Faktur Odoo 17 &amp; Demurrage</span>
                </button>
                <button type="button" id="workspaceTabBtn_all" onclick="switchBerandaWorkspaceTab('all')" class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 cursor-pointer" title="Tampilkan Seluruh Panel Sekaligus">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Tampilkan Semua</span>
                </button>
            </div>
            <div class="text-[10.5px] text-slate-500 font-medium px-2 hidden md:block">
                <i class="fa-solid fa-circle-info text-[#0170b9] mr-1"></i>Klik tab untuk berpindah cepat tanpa scroll panjang
            </div>
        </div>

        <!-- ======================================================================= -->
        <!-- 5. FACILITY SPATIAL RADAR & CUSTOMS INSPECTION HUB (TAB 2: ZONES)       -->
        <!-- ======================================================================= -->
        <div id="workspaceTabPanel_zones" class="hidden cidp-tri-panel-grid grid grid-cols-1 xl:grid-cols-12 gap-3 animate-fadeIn">
            
            <!-- Left: 4-Zone Spatial Terminal Distribution Grid (Clickable Cross-Filter) (4 Cols) -->
            <div class="xl:col-span-4 bg-white/95 backdrop-blur-xs rounded-xl shadow-2xs border border-slate-200/90 p-3 sm:p-3.5 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -right-8 -bottom-8 w-44 h-44 pointer-events-none opacity-[0.045] z-0 rounded-full"
                     style="background-image: url('assets/img/facilities/cfs_warehouse.jpg'); background-size: cover; filter: grayscale(100%); mask-image: radial-gradient(circle, black 35%, transparent 75%); -webkit-mask-image: radial-gradient(circle, black 35%, transparent 75%);">
                </div>
                <div class="flex items-center justify-between pb-1.5 border-b border-slate-100 mb-2 relative z-10">
                    <h3 class="font-extrabold text-slate-900 text-xs flex items-center">
                        <i class="fa-solid fa-map-location-dot mr-1.5 text-[#0170b9] text-xs"></i>Distribusi 4 Zona Terminal (DB Live)
                    </h3>
                    <div class="flex items-center gap-2">
                        <a href="dashboard.php?page=twin" class="text-[9.5px] font-bold text-orange-600 hover:underline flex items-center gap-1">
                            <span>Twin 3D &rarr;</span>
                        </a>
                        <a href="dashboard.php?page=yard" class="text-[9.5px] font-bold text-[#0170b9] hover:underline flex items-center gap-1">
                            <span>Yard Matrix &rarr;</span>
                        </a>
                    </div>
                </div>

                <div class="space-y-2 py-0.5 flex-1 flex flex-col justify-between text-[10.5px] relative z-10">
                    <!-- Z1: Gate & Buffer Truck -->
                    <div id="zoneCard_Z1" onclick="setBerandaZoneFilter('Z1')" class="spatial-zone-card cursor-pointer p-2 rounded-lg border border-slate-100 bg-slate-50/70 hover:border-orange-300 hover:bg-orange-50/30 transition">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                <span class="w-4 h-4 rounded bg-orange-100 text-orange-700 flex items-center justify-center text-[9px] font-mono font-bold">Z1</span>
                                Gerbang &amp; Buffer Truk (4.2 Ha)
                            </span>
                            <span id="zoneZ1PctTxt" class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-1 py-0.2 rounded border border-emerald-200"><?= $zones['z1_pct'] ?>%</span>
                        </div>
                        <div class="w-full bg-slate-200/80 rounded-full h-1.5 overflow-hidden mb-1">
                            <div id="zoneZ1Bar" class="bg-orange-500 h-1.5 rounded-full transition-all duration-500" style="width: <?= $zones['z1_pct'] ?>%"></div>
                        </div>
                        <div class="flex justify-between text-[9px] text-slate-500">
                            <span>Antrean: <strong><?= $total_trucks ?> Truk Aktif</strong></span>
                            <span class="text-orange-600 font-bold">Klik Filter Z1 &rarr;</span>
                        </div>
                    </div>

                    <!-- Z2: Stacking Yard 12,500 TEU (Blok A-E) -->
                    <div id="zoneCard_Z2" onclick="setBerandaZoneFilter('Z2')" class="spatial-zone-card cursor-pointer p-2 rounded-lg border border-slate-100 bg-slate-50/70 hover:border-blue-300 hover:bg-blue-50/30 transition">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                <span class="w-4 h-4 rounded bg-blue-100 text-[#0170b9] flex items-center justify-center text-[9px] font-mono font-bold">Z2</span>
                                Stacking Yard Blok A–E (16.5 Ha)
                            </span>
                            <span class="text-[9px] font-bold text-blue-700 bg-blue-50 px-1 py-0.2 rounded border border-blue-200"><?= $yard_occupancy ?>%</span>
                        </div>
                        <div class="w-full bg-slate-200/80 rounded-full h-1.5 overflow-hidden mb-1">
                            <div id="zoneZ2Bar" class="bg-[#0170b9] h-1.5 rounded-full transition-all duration-500" style="width: <?= $zones['z2_pct'] ?>%"></div>
                        </div>
                        <div class="flex justify-between text-[9px] text-slate-500">
                            <span><?= number_format($containers_yard) ?> / 12,500 TEU &bull; 4 RS + 4 E-RTG</span>
                            <span class="text-[#0170b9] font-bold">Klik Filter Z2 &rarr;</span>
                        </div>
                    </div>

                    <!-- Z3: CFS Warehouse Rampa D1-D5 & Palet SSCC-18 -->
                    <div id="zoneCard_Z3" onclick="setBerandaZoneFilter('Z3')" class="spatial-zone-card cursor-pointer p-2 rounded-lg border border-slate-100 bg-slate-50/70 hover:border-amber-300 hover:bg-amber-50/30 transition">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                <span class="w-4 h-4 rounded bg-amber-100 text-amber-700 flex items-center justify-center text-[9px] font-mono font-bold">Z3</span>
                                Gudang CFS &amp; Rak A–F (6.8 Ha)
                            </span>
                            <span id="zoneZ3PctTxt" class="text-[9px] font-bold text-amber-800 bg-amber-50 px-1 py-0.2 rounded border border-amber-200"><?= $zones['z3_pct'] ?>%</span>
                        </div>
                        <div class="w-full bg-slate-200/80 rounded-full h-1.5 overflow-hidden mb-1">
                            <div id="zoneZ3Bar" class="bg-amber-500 h-1.5 rounded-full transition-all duration-500" style="width: <?= $zones['z3_pct'] ?>%"></div>
                        </div>
                        <div class="flex justify-between text-[9px] text-slate-500">
                            <span><strong><?= $cfs_pallets_rack ?> Palet SSCC-18</strong> &bull; <?= $cfs_jobs ?> Job &bull; FL-01..04</span>
                            <span class="text-amber-700 font-bold">Klik Filter Z3 &rarr;</span>
                        </div>
                    </div>

                    <!-- Z4: Siding KA & Reefer Cold Chain -->
                    <div id="zoneCard_Z4" onclick="setBerandaZoneFilter('Z4')" class="spatial-zone-card cursor-pointer p-2 rounded-lg border border-slate-100 bg-slate-50/70 hover:border-cyan-300 hover:bg-cyan-50/30 transition">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                <span class="w-4 h-4 rounded bg-cyan-100 text-cyan-700 flex items-center justify-center text-[9px] font-mono font-bold">Z4</span>
                                Siding KA &amp; Reefer Hub (7.5 Ha)
                            </span>
                            <span id="zoneZ4PctTxt" class="text-[9px] font-bold text-cyan-800 bg-cyan-50 px-1 py-0.2 rounded border border-cyan-200"><?= $zones['z4_pct'] ?>%</span>
                        </div>
                        <div class="w-full bg-slate-200/80 rounded-full h-1.5 overflow-hidden mb-1">
                            <div id="zoneZ4Bar" class="bg-cyan-500 h-1.5 rounded-full transition-all duration-500" style="width: <?= $zones['z4_pct'] ?>%"></div>
                        </div>
                        <div class="flex justify-between text-[9px] text-slate-500">
                            <span>RTG-01 Rel 2x600m &bull; <?= $reefer_plugs ?>/300 Plugs</span>
                            <span class="text-cyan-700 font-bold">Klik Filter Z4 &rarr;</span>
                        </div>
                    </div>
                </div>

                <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9.5px] text-slate-500 mt-2 relative z-10">
                    <span>Luas Master Plan: <strong>35 Hektar</strong></span>
                    <span class="text-emerald-700 font-bold">15 Unit MHE + 26 BOM HW OK</span>
                </div>
            </div>

            <!-- Middle: Customs CEISA 4.0 Inspection Hub (4 Cols) -->
            <div class="xl:col-span-4 bg-white/95 backdrop-blur-xs rounded-xl shadow-2xs border border-slate-200/90 p-3 sm:p-3.5 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -right-6 -bottom-6 w-36 h-36 pointer-events-none opacity-[0.045] z-0" aria-hidden="true">
                    <svg width="100%" height="100%" viewBox="0 0 100 100" fill="none" stroke="#10b981" stroke-width="1.8">
                        <path d="M 50 15 L 80 28 V 55 C 80 72, 50 85, 50 85 C 50 85, 20 72, 20 55 V 28 Z" />
                        <circle cx="50" cy="50" r="14" stroke="#0170b9" stroke-width="1.4" />
                    </svg>
                </div>
                <div class="flex items-center justify-between pb-1.5 border-b border-slate-100 mb-2 relative z-10">
                    <h3 class="font-extrabold text-slate-900 text-xs flex items-center">
                        <i class="fa-solid fa-stamp mr-1.5 text-emerald-600 text-xs"></i>Bea Cukai CEISA 4.0 Hub
                    </h3>
                    <a href="dashboard.php?page=customs" class="text-[9.5px] font-bold text-emerald-700 hover:underline">
                        KPU BC Marunda &rarr;
                    </a>
                </div>

                <div class="space-y-3 py-0.5 flex-1 flex flex-col justify-between relative z-10">
                    <div>
                        <div class="flex justify-between text-[10px] mb-1">
                            <span class="font-bold text-slate-700">Pipeline Kanal Pabean:</span>
                            <span class="font-bold text-emerald-700">SPPB Auto: 92%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5 flex overflow-hidden border border-slate-200/80">
                            <div class="bg-emerald-500 h-2.5 cursor-pointer" onclick="setBerandaCategoryFilter('all')" style="width: 92%" title="Jalur Hijau (92%) - SPPB Rilis Otomatis"></div>
                            <div class="bg-rose-500 h-2.5 cursor-pointer" onclick="setBerandaCategoryFilter('customs_hold')" style="width: 8%" title="Jalur Merah (8%) - Wajib Behandle & X-Ray"></div>
                        </div>
                        <div class="flex justify-between items-center text-[9px] text-slate-500 mt-1">
                            <span class="flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Jalur Hijau (SPPB Auto): <strong>92%</strong>
                            </span>
                            <button type="button" onclick="setBerandaCategoryFilter('customs_hold')" class="flex items-center gap-1 text-rose-600 hover:underline cursor-pointer">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Jalur Merah: <strong>8%</strong>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="p-2.5 rounded-lg border border-slate-200 bg-slate-50/70 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-md bg-blue-100 text-[#0170b9] flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-barcode"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-[11px]">Hi-Co Container X-Ray (HW-17)</h4>
                                    <p class="text-[9px] text-slate-400">Nuctech Bi-Energy 6MeV Pemindai Peti Kemas</p>
                                </div>
                            </div>
                            <span class="text-[8.5px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded font-mono">
                                ONLINE (CAL)
                            </span>
                        </div>

                        <div class="p-2.5 rounded-lg border border-slate-200 bg-slate-50/70 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-md bg-orange-100 text-orange-700 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-magnifying-glass-chart"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-[11px]">Dermaga Pemeriksaan Behandle (Blok E)</h4>
                                    <p class="text-[9px] text-slate-400">Forklift FL-02 &amp; Shuttle CDP-TT-02 Siaga</p>
                                </div>
                            </div>
                            <span class="text-[8.5px] font-extrabold text-blue-700 bg-blue-50 border border-blue-200 px-1.5 py-0.5 rounded font-mono">
                                4 DOCK SIAGA
                            </span>
                        </div>
                    </div>

                    <div class="p-2 bg-emerald-50/70 border border-emerald-200/80 rounded-lg flex items-center justify-between">
                        <span class="text-[10px] text-emerald-900 font-semibold flex items-center gap-1.5">
                            <i class="fa-solid fa-bolt text-emerald-600"></i> Waktu Rilis Rata-rata SPPB:
                        </span>
                        <span id="ceisaSppbSec" class="text-xs font-black text-emerald-800 font-mono"><?= $sppb_sec ?> Detik</span>
                    </div>
                </div>

                <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9.5px] text-slate-500 mt-2 relative z-10">
                    <span>Sinkronisasi: <strong>Host-to-Host REST API</strong></span>
                    <span id="ceisaLatencyTxt" class="text-emerald-700 font-bold">Latency <?= $latencies['ceisa'] ?>ms</span>
                </div>
            </div>

            <!-- Right: Weekly Revenue Breakdown (Stacked Bar Chart) (4 Cols) -->
            <div class="xl:col-span-4 bg-white rounded-xl shadow-2xs border border-slate-200 p-3 sm:p-3.5 flex flex-col justify-between">
                <div class="flex items-center justify-between pb-1.5 border-b border-slate-100 mb-2">
                    <h3 class="font-extrabold text-slate-900 text-xs flex items-center">
                        <i class="fa-solid fa-sack-dollar mr-1.5 text-[#0170b9] text-xs"></i>Pendapatan Layanan (7 Hari)
                    </h3>
                    <span class="text-[9px] font-bold text-slate-500 bg-slate-50 px-1.5 py-0.5 rounded border border-slate-200 font-mono">
                        Juta Rupiah
                    </span>
                </div>

                <div class="h-44 sm:h-48 relative w-full flex-1">
                    <canvas id="weeklyRevenueChart"></canvas>
                </div>

                <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9px] text-slate-600">
                    <span><strong class="text-[#0170b9]">Lo-Lo:</strong> 45%</span>
                    <span><strong class="text-[#ea580c]">Storage/CFS:</strong> 30%</span>
                    <span><strong class="text-[#06b6d4]">Reefer:</strong> 15%</span>
                    <span><strong class="text-[#6366f1]">VGM:</strong> 10%</span>
                </div>
            </div>

        </div>

        <!-- ======================================================================= -->
        <!-- 6. OPSI C: PILAR KE-3 ALIRAN FINANSIAL, DEMURRAGE & ODOO 17 ERP LEDGER -->
        <!-- ======================================================================= -->
        <div id="workspaceTabPanel_finance" class="hidden bg-white/95 backdrop-blur-xs rounded-xl p-3.5 sm:p-4 shadow-2xs border border-slate-200/90 relative overflow-hidden animate-fadeIn">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 mb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center text-xs flex-shrink-0">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-extrabold text-slate-900 text-xs sm:text-sm">
                                Ringkasan Finansial &amp; Billing ERP
                            </h3>
                            <span class="text-[8.5px] font-extrabold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full font-mono">
                                <i class="fa-solid fa-plug-circle-check mr-0.5"></i>XML-RPC CONNECTED (<span id="odooLatencyTxt"><?= $latencies['odoo_erp'] ?></span>ms)
                            </span>
                        </div>
                        <p class="text-[10px] text-slate-500">Akrual storage, jasa gudang CFS, demurrage &gt;3 hari &amp; status rilis Gate-Out.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="report_executive.php?scope=financial" target="_blank" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-[10px] font-bold flex items-center gap-1 transition">
                        <i class="fa-solid fa-print text-[#0170b9]"></i>
                        <span>Cetak Audit Finansial</span>
                    </a>
                    <a href="dashboard.php?page=billing" class="px-2.5 py-1 rounded-lg bg-[#0170b9] hover:bg-blue-700 text-white text-[10px] font-bold flex items-center gap-1 transition">
                        <span>Buka Modul Billing ERP</span>
                        <i class="fa-solid fa-arrow-right text-[9px]"></i>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-3.5 items-stretch">
                <!-- Left (5 Cols): 4 Financial Telemetry Cards & Demurrage Exposure Alert -->
                <div class="xl:col-span-5 flex flex-col justify-between gap-2.5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <!-- Fin Card 1: Total Invoiced -->
                        <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50/70">
                            <div class="flex items-center justify-between text-[9px] font-bold text-slate-500 uppercase">
                                <span>Total Tagihan Terbit</span>
                                <i class="fa-solid fa-file-invoice text-[#0170b9]"></i>
                            </div>
                            <div id="finTotalInvoiced" class="text-sm sm:text-base font-black text-slate-900 font-mono mt-1">
                                Rp <?= number_format($fin['total_invoiced'], 0, ',', '.') ?>
                            </div>
                            <div class="text-[9px] text-slate-500 mt-0.5">E-Faktur PPN 11% Otomatis</div>
                        </div>

                        <!-- Fin Card 2: Paid / Lunas -->
                        <div class="p-2.5 rounded-xl border border-emerald-200 bg-emerald-50/40">
                            <div class="flex items-center justify-between text-[9px] font-bold text-emerald-700 uppercase">
                                <span>Terbayar / Lunas</span>
                                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                            </div>
                            <div id="finPaidRevenue" class="text-sm sm:text-base font-black text-emerald-700 font-mono mt-1">
                                Rp <?= number_format($fin['paid_revenue'], 0, ',', '.') ?>
                            </div>
                            <div class="text-[9px] text-emerald-700 font-semibold mt-0.5">Rasio Pelunasan: <strong id="finSettlementPct"><?= $fin['settlement_pct'] ?>%</strong></div>
                        </div>

                        <!-- Fin Card 3: Pending Receivable -->
                        <div class="p-2.5 rounded-xl border border-amber-200 bg-amber-50/40">
                            <div class="flex items-center justify-between text-[9px] font-bold text-amber-800 uppercase">
                                <span>Piutang Berjalan (VA)</span>
                                <i class="fa-solid fa-clock text-amber-600"></i>
                            </div>
                            <div id="finPendingReceivable" class="text-sm sm:text-base font-black text-amber-700 font-mono mt-1">
                                Rp <?= number_format($fin['pending_receivable'], 0, ',', '.') ?>
                            </div>
                            <div class="text-[9px] text-amber-800 mt-0.5">Menunggu Settlement Bank</div>
                        </div>

                        <!-- Fin Card 4: Demurrage Exposure (>3 Days) -->
                        <div onclick="setBerandaCategoryFilter('demurrage')" class="cursor-pointer p-2.5 rounded-xl border border-rose-200 bg-rose-50/50 hover:bg-rose-100/60 transition group" title="Klik untuk menampilkan kontainer yang terkena denda Demurrage (>3 hari)">
                            <div class="flex items-center justify-between text-[9px] font-bold text-rose-800 uppercase">
                                <span>Eksposur Demurrage</span>
                                <i class="fa-solid fa-triangle-exclamation text-rose-600 animate-bounce"></i>
                            </div>
                            <div id="finDemurrageAccrual" class="text-sm sm:text-base font-black text-rose-700 font-mono mt-1">
                                Rp <?= number_format($fin['demurrage_accrual_rp'], 0, ',', '.') ?>
                            </div>
                            <div class="flex items-center justify-between text-[9px] text-rose-700 font-bold mt-0.5">
                                <span><span id="finDemurrageCount"><?= $fin['demurrage_box_count'] ?></span> Box Inap &gt;3H</span>
                                <span class="underline group-hover:translate-x-0.5 transition-transform">Filter Box &rarr;</span>
                            </div>
                        </div>
                    </div>

                    <!-- Commercial Gate-Out Interlock Bar -->
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 border-l-4 border-l-emerald-500 text-slate-800 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 text-xs flex-shrink-0">
                                <i class="fa-solid fa-shield-check"></i>
                            </div>
                            <div>
                                <div class="text-[10.5px] font-extrabold text-slate-900">Interlock Komersial Gate-Out &amp; Palang ANPR</div>
                                <div class="text-[9px] text-slate-600">Palang gerbang keluar hanya terbuka otomatis jika Faktur Odoo berstatus <strong class="text-emerald-700">PAID</strong> &amp; SPPB <strong class="text-emerald-700">CLEARED</strong>.</div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-emerald-50 border border-emerald-200 text-emerald-700 font-mono font-bold text-[8.5px] flex-shrink-0">
                            ACTIVE LOCK
                        </span>
                    </div>
                </div>

                <!-- Right (7 Cols): Mini Odoo ERP Commercial Invoice Ledger with Instant Filter & Print -->
                <div class="xl:col-span-7 border border-slate-200 rounded-xl p-3 bg-slate-50/40 flex flex-col justify-between">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                        <span class="text-[11px] font-extrabold text-slate-800 flex items-center gap-1.5">
                            <i class="fa-solid fa-receipt text-[#0170b9]"></i>
                            <span>Buku Besar Faktur Komersial Terbaru (Odoo 17 Billing Ledger)</span>
                        </span>
                        <div class="flex items-center gap-1 text-[9.5px] flex-wrap">
                            <button type="button" onclick="filterBerandaInvoices('ALL')" id="invTab_ALL" class="px-2 py-0.5 rounded font-bold bg-[#0170b9] text-white cursor-pointer transition">Semua</button>
                            <button type="button" onclick="filterBerandaInvoices('PAID')" id="invTab_PAID" class="px-2 py-0.5 rounded font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 cursor-pointer transition">Lunas</button>
                            <button type="button" onclick="filterBerandaInvoices('PENDING')" id="invTab_PENDING" class="px-2 py-0.5 rounded font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 cursor-pointer transition">Menunggu</button>
                            <button type="button" onclick="filterBerandaInvoices('OVERDUE')" id="invTab_OVERDUE" class="px-2 py-0.5 rounded font-semibold bg-white border border-slate-200 text-rose-600 hover:bg-rose-50 cursor-pointer transition">Overdue</button>
                            <select id="berandaInvLimitSelect" onchange="renderBerandaInvoices()" class="ml-1 px-2 py-0.5 rounded border border-slate-200 bg-white text-slate-700 font-bold text-[9.5px] cursor-pointer">
                                <option value="5" selected>Tampil: 5</option>
                                <option value="10">Tampil: 10</option>
                                <option value="25">Tampil: 25</option>
                                <option value="all">Semua</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[520px]" id="berandaInvoiceTable">
                            <thead>
                                <tr class="bg-white text-slate-500 text-[9.5px] border-y border-slate-200 uppercase">
                                    <th class="py-1.5 px-2 font-bold text-center w-8">No</th>
                                    <th class="py-1.5 px-2 font-bold">No. Faktur</th>
                                    <th class="py-1.5 px-2 font-bold">Kontainer</th>
                                    <th class="py-1.5 px-2 font-bold">Pelanggan / Shipper</th>
                                    <th class="py-1.5 px-2 font-bold">Layanan</th>
                                    <th class="py-1.5 px-2 font-bold text-right">Tagihan</th>
                                    <th class="py-1.5 px-2 font-bold text-center">Status</th>
                                    <th class="py-1.5 px-2 font-bold text-right">Cetak</th>
                                </tr>
                            </thead>
                            <tbody id="berandaInvoiceTableBody" class="text-[10.5px] divide-y divide-slate-200/70 bg-white">
                                <!-- Di-render secara dinamis oleh JavaScript dari state faktur -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================================= -->
        <!-- 7. BOTTOM EXECUTION CENTER & INTERACTIVE CONSIGNMENT TRACKING (TAB 1)   -->
        <!-- ======================================================================= -->
        
        <!-- Banner Status Filter Lintas-Elemen (Tampil saat filter Stage / Zona aktif) -->
        <div id="activeCrossFilterBanner" class="hidden bg-orange-50 border border-orange-200 text-slate-900 rounded-xl px-4 py-2.5 shadow-2xs flex flex-wrap items-center justify-between gap-2 animate-fadeIn">
            <div class="flex items-center gap-2.5 text-xs">
                <span class="w-6 h-6 rounded-lg bg-white border border-orange-200 flex items-center justify-center text-orange-600">
                    <i class="fa-solid fa-filter"></i>
                </span>
                <div>
                    <span class="text-[10px] uppercase tracking-wider text-orange-700 font-bold block">Mode Filter Lintas-Elemen Aktif:</span>
                    <strong id="activeCrossFilterTitle" class="text-xs sm:text-sm font-extrabold text-slate-900">Menampilkan Semua Tahapan &amp; Zona</strong>
                </div>
            </div>
            <button type="button" onclick="resetBerandaFilters()" class="px-3 py-1 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-[10.5px] font-extrabold flex items-center gap-1.5 shadow-2xs transition cursor-pointer">
                <i class="fa-solid fa-rotate-left"></i>
                <span>Tampilkan Semua Data (Reset)</span>
            </button>
        </div>

        <div id="workspaceTabPanel_ops" class="cidp-tri-panel-grid grid grid-cols-1 xl:grid-cols-12 gap-3 animate-fadeIn">
            
            <!-- Left: Live SCADA Field Telemetry Feed (5 Cols) -->
            <div class="xl:col-span-5 bg-white rounded-xl shadow-2xs border border-slate-200 p-3 sm:p-3.5 flex flex-col justify-between">
                <div class="flex justify-between items-center mb-2 pb-1.5 border-b border-slate-100">
                    <h3 class="font-extrabold text-slate-900 text-xs flex items-center">
                        <i class="fa-solid fa-satellite mr-1.5 text-[#0170b9] text-xs"></i>Live SCADA Field Telemetry Feed
                    </h3>
                    <span id="scadaFeedStatusBadge" class="text-[8.5px] font-extrabold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 font-mono">
                        <i class="fa-solid fa-bolt mr-0.5 text-[8px]"></i>Live Stream Ready
                    </span>
                </div>

                <!-- Vertical Timeline Stream (Rendered Dynamically via JS so Cross-Filter & Auto-Sync work seamlessly) -->
                <div id="berandaScadaTimeline" class="relative border-l-2 border-slate-100 ml-2 space-y-2.5 py-1 flex-1">
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] mt-2">
                    <span id="scadaFeedCounter" class="text-slate-400 font-semibold">Menampilkan aktivitas terbaru</span>
                    <a href="dashboard.php?page=twin" class="font-bold text-[#0170b9] hover:underline inline-flex items-center gap-1 transition">
                        <span>Buka Digital Twin 3D</span>
                        <i class="fa-solid fa-arrow-right text-[9px]"></i>
                    </a>
                </div>
            </div>

            <!-- Right: Interactive Shipper Consignment Tracking Table (OPSI B) (7 Cols) -->
            <div class="xl:col-span-7 bg-white/95 backdrop-blur-xs rounded-xl shadow-2xs border border-slate-200/90 p-3 sm:p-3.5 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -right-6 -top-6 w-32 h-32 pointer-events-none opacity-[0.09] z-0" aria-hidden="true">
                    <svg width="100%" height="100%" viewBox="0 0 100 100" fill="none" stroke="#004b87" stroke-width="1.6">
                        <ellipse cx="50" cy="25" rx="14" ry="22" />
                        <ellipse cx="50" cy="75" rx="14" ry="22" />
                        <ellipse cx="25" cy="50" rx="22" ry="14" />
                        <ellipse cx="75" cy="50" rx="22" ry="14" />
                        <circle cx="50" cy="50" r="4" fill="#ea580c" />
                    </svg>
                </div>

                <div class="flex flex-col gap-2 mb-2.5 pb-2 border-b border-slate-100 relative z-10">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                        <h3 class="font-extrabold text-slate-900 text-xs flex items-center">
                            <i class="fa-solid fa-list-check mr-1.5 text-[#0170b9] text-xs"></i>Pelacakan Konsinyasi Peti Kemas
                        </h3>
                        <div class="flex items-center gap-1.5">
                            <span id="consignmentCountBadge" class="text-[8.5px] font-extrabold text-[#0170b9] bg-blue-50 px-2 py-0.5 rounded border border-blue-200 font-mono">
                                <?= count($containers_list) ?> Unit Aktif
                            </span>
                            <a href="dashboard.php?page=kontainer" class="text-[10px] font-bold text-[#0170b9] hover:underline">
                                Modul Kontainer &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- OPSI B: Instant Search & Category Filter Pills -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2">
                        <!-- Instant Search Input -->
                        <div class="relative flex-1">
                            <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                            <input type="text" id="consignmentSearchInput" oninput="renderBerandaConsignments()" placeholder="Cari No. Kontainer (MSKU...), Tag RFID, Shipper, atau Blok..." class="w-full pl-7 pr-7 py-1 text-[10.5px] bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-[#0170b9] focus:bg-white transition">
                            <button type="button" id="btnClearConsignmentSearch" onclick="clearConsignmentSearch()" class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-[10px]">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <!-- Quick Filter Pills + Limit -->
                        <div class="flex items-center gap-1 flex-wrap text-[9.5px]">
                            <button type="button" onclick="setBerandaCategoryFilter('all')" id="catBtn_all" class="px-2 py-1 rounded-md font-bold bg-[#0170b9] text-white cursor-pointer transition">Semua</button>
                            <button type="button" onclick="setBerandaCategoryFilter('dry_yard')" id="catBtn_dry_yard" class="px-2 py-1 rounded-md font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 cursor-pointer transition">Blok A–E</button>
                            <button type="button" onclick="setBerandaCategoryFilter('reefer')" id="catBtn_reefer" class="px-2 py-1 rounded-md font-semibold bg-cyan-50 text-cyan-700 border border-cyan-200 hover:bg-cyan-100 cursor-pointer transition">Reefer</button>
                            <button type="button" onclick="setBerandaCategoryFilter('rail')" id="catBtn_rail" class="px-2 py-1 rounded-md font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 cursor-pointer transition">Siding KA</button>
                            <button type="button" onclick="setBerandaCategoryFilter('demurrage')" id="catBtn_demurrage" class="px-2 py-1 rounded-md font-semibold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 cursor-pointer transition">⚠️ Demurrage</button>
                            <select id="berandaConsignLimitSelect" onchange="renderBerandaConsignments()" class="px-2 py-1 rounded-md border border-slate-200 bg-white text-slate-700 font-bold text-[9.5px] cursor-pointer">
                                <option value="10" selected>Tampil: 10</option>
                                <option value="25">Tampil: 25</option>
                                <option value="50">Tampil: 50</option>
                                <option value="all">Semua</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Consignment Table -->
                <div class="overflow-x-auto flex-1 relative z-10">
                    <table class="w-full text-left border-collapse min-w-[620px]" id="berandaConsignmentTable">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-[9.5px] border-y border-slate-200 uppercase">
                                <th class="py-2 px-2 font-bold text-center w-8">No</th>
                                <th class="py-2 px-2 font-bold">No. Kontainer &amp; Shipper</th>
                                <th class="py-2 px-2 font-bold">Tipe &amp; ISO</th>
                                <th class="py-2 px-2 font-bold">Tag RFID UHF</th>
                                <th class="py-2 px-2 font-bold">Posisi &amp; Dwell</th>
                                <th class="py-2 px-2 font-bold">Status Pabean</th>
                                <th class="py-2 px-2 font-bold text-right">Inspeksi 3-Aliran</th>
                            </tr>
                        </thead>
                        <tbody id="berandaConsignmentTableBody" class="text-[10.5px] divide-y divide-slate-100">
                            <!-- Di-render secara dinamis oleh JS -->
                        </tbody>
                    </table>
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-500 mt-2 relative z-10">
                    <span>Total Kapasitas Terpantau: <strong id="footerTotalContainers"><?= number_format($containers_yard) ?> Peti Kemas</strong></span>
                    <span class="text-[#0170b9] font-bold">Klik baris kontainer untuk Inspeksi 3-Aliran &amp; Cetak Dossier</span>
                </div>
            </div>

        </div>

        <!-- ======================================================================= -->
        <!-- 8. HOST-TO-HOST INTEGRATION STATUS BAR (FULL WIDTH DYNAMIC LATENCY)    -->
        <!-- ======================================================================= -->
        <div class="bg-white rounded-xl p-3 shadow-2xs border border-slate-200">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2 pb-1.5 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-network-wired text-[#0170b9] text-xs"></i>
                    <h4 class="font-extrabold text-slate-900 text-xs">Konektivitas Sistem &amp; API</h4>
                </div>
                <div class="flex items-center gap-2 text-[9.5px]">
                    <span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Semua Gateway Terhubung (5/5)
                    </span>
                    <span class="text-slate-400">TLS 1.3 / OAuth 2.0 Encrypted</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-2.5 text-[10.5px]">
                <div class="p-2 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse flex-shrink-0"></span>
                        <div class="truncate">
                            <div class="font-bold text-slate-900 text-[11px] truncate">Bea Cukai CEISA 4.0</div>
                            <div class="text-[9px] text-slate-400 truncate">REST API &bull; SPPB Auto</div>
                        </div>
                    </div>
                    <span id="latCeisaPill" class="text-[8.5px] font-mono font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.2 rounded flex-shrink-0"><?= $latencies['ceisa'] ?>ms</span>
                </div>

                <div class="p-2 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse flex-shrink-0"></span>
                        <div class="truncate">
                            <div class="font-bold text-slate-900 text-[11px] truncate">Inaportnet Kemenhub V2</div>
                            <div class="text-[9px] text-slate-400 truncate">NLE Manifest Sinkron</div>
                        </div>
                    </div>
                    <span id="latInaportPill" class="text-[8.5px] font-mono font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.2 rounded flex-shrink-0"><?= $latencies['inaport'] ?>ms</span>
                </div>

                <div class="p-2 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse flex-shrink-0"></span>
                        <div class="truncate">
                            <div class="font-bold text-slate-900 text-[11px] truncate">KAI Logistik EDI</div>
                            <div class="text-[9px] text-slate-400 truncate">Jadwal Siding KA 2518</div>
                        </div>
                    </div>
                    <span id="latKaiPill" class="text-[8.5px] font-mono font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.2 rounded flex-shrink-0"><?= $latencies['kai_edi'] ?>ms</span>
                </div>

                <div class="p-2 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse flex-shrink-0"></span>
                        <div class="truncate">
                            <div class="font-bold text-slate-900 text-[11px] truncate">Marechal IoT LoRaWAN</div>
                            <div class="text-[9px] text-slate-400 truncate">868MHz 300 Plugs Telemetri</div>
                        </div>
                    </div>
                    <span id="latLoraPill" class="text-[8.5px] font-mono font-bold text-cyan-700 bg-cyan-50 border border-cyan-200 px-1.5 py-0.2 rounded flex-shrink-0"><?= $latencies['lorawan'] ?>ms</span>
                </div>

                <div class="p-2 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2 h-2 rounded-full bg-purple-500 animate-pulse flex-shrink-0"></span>
                        <div class="truncate">
                            <div class="font-bold text-slate-900 text-[11px] truncate">Odoo 17 ERP &amp; GS1 AI</div>
                            <div class="text-[9px] text-slate-400 truncate">XML-RPC Billing &amp; SSCC</div>
                        </div>
                    </div>
                    <span id="latOdooPill" class="text-[8.5px] font-mono font-bold text-purple-700 bg-purple-50 border border-purple-200 px-1.5 py-0.2 rounded flex-shrink-0"><?= $latencies['odoo_erp'] ?>ms</span>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- =========================================================================== -->
<!-- MODAL INSPEKSI 3-ALIRAN PETI KEMAS (QUICK 3-FLOW CONTAINER DOSSIER MODAL)  -->
<!-- =========================================================================== -->
<div id="berandaInspectModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden animate-fadeIn">
        <!-- Modal Header -->
        <div class="bg-slate-50 border-b border-slate-200 px-5 py-3.5 text-slate-900 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-orange-50 border border-orange-200 flex items-center justify-center text-orange-600">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <div>
                    <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-500 block">Inspeksi Terpadu 3-Aliran Logistik</span>
                    <h3 id="mdlCtrNumber" class="text-base font-black font-mono tracking-tight text-slate-900">MSKU9920194</h3>
                </div>
            </div>
            <button type="button" onclick="closeBerandaInspectModal()" class="w-7 h-7 rounded-lg bg-white hover:bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600 cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Modal Body: 3 Flows Breakdown -->
        <div class="p-4 sm:p-5 space-y-3.5 text-xs">
            <div class="flex flex-wrap items-center justify-between gap-2 bg-slate-50 border border-slate-200 rounded-xl p-3">
                <div>
                    <span class="text-[9.5px] text-slate-400 font-semibold block">Pemilik / Shipper Konsinyasi:</span>
                    <strong id="mdlCtrOwner" class="text-slate-900 text-sm">PT Samudera Pratama Mandiri</strong>
                </div>
                <div class="text-right">
                    <span class="text-[9.5px] text-slate-400 font-semibold block">Spesifikasi ISO 6346:</span>
                    <strong id="mdlCtrSpec" class="font-mono text-[#0170b9]">40FT HC (45G1) &bull; 28,450 kg</strong>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <!-- 1. Aliran Fisik -->
                <div class="p-3 rounded-xl border border-blue-200 bg-blue-50/30 space-y-1.5">
                    <div class="font-extrabold text-slate-900 text-[10.5px] uppercase flex items-center gap-1.5 border-b border-blue-200 pb-1">
                        <i class="fa-solid fa-cubes-stacked text-[#0170b9]"></i> 1. Aliran Fisik
                    </div>
                    <div class="text-[10.5px] space-y-1">
                        <div class="flex justify-between"><span class="text-slate-500">Posisi:</span><strong id="mdlCtrPos" class="text-slate-900">Blok A / 02-04-03</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">Zona Spasial:</span><strong id="mdlCtrZone" class="text-[#0170b9]">Zona Z2</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">Berat VGM:</span><strong id="mdlCtrWeight" class="font-mono">28.450 kg</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">No. Segel:</span><strong id="mdlCtrSeal" class="font-mono">SL-MSK-88210</strong></div>
                    </div>
                </div>

                <!-- 2. Aliran Informasi -->
                <div class="p-3 rounded-xl border border-purple-200 bg-purple-50/30 space-y-1.5">
                    <div class="font-extrabold text-purple-950 text-[10.5px] uppercase flex items-center gap-1.5 border-b border-purple-200 pb-1">
                        <i class="fa-solid fa-wifi text-purple-600"></i> 2. Aliran Informasi
                    </div>
                    <div class="text-[10.5px] space-y-1">
                        <div class="flex justify-between"><span class="text-slate-500">RFID UHF:</span><strong id="mdlCtrRfid" class="font-mono text-purple-800">EPC-915-A109</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">GS1 SSCC:</span><strong id="mdlCtrSscc" class="font-mono text-[9.5px]">(00)3899...</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">CEISA 4.0:</span><strong id="mdlCtrCustoms" class="text-emerald-700">SPPB_CLEARED</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">Update:</span><strong id="mdlCtrTime" class="font-mono">16:42 WIB</strong></div>
                    </div>
                </div>

                <!-- 3. Aliran Finansial -->
                <div class="p-3 rounded-xl border border-amber-200 bg-amber-50/40 space-y-1.5">
                    <div class="font-extrabold text-amber-950 text-[10.5px] uppercase flex items-center gap-1.5 border-b border-amber-200 pb-1">
                        <i class="fa-solid fa-file-invoice-dollar text-amber-600"></i> 3. Aliran Finansial
                    </div>
                    <div class="text-[10.5px] space-y-1">
                        <div class="flex justify-between"><span class="text-slate-500">Dwell Time:</span><strong id="mdlCtrDwell" class="font-mono">2 Hari (Aman)</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">Biaya Storage:</span><strong id="mdlCtrStorage" class="font-mono">Rp 900.000</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">Denda Inap:</span><strong id="mdlCtrDemurrage" class="font-mono text-rose-700">Rp 0</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">ERP Sync:</span><strong class="text-emerald-700">Odoo 17 Ready</strong></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer Actions -->
        <div class="bg-slate-50 px-5 py-3 border-t border-slate-200 flex flex-wrap items-center justify-between gap-2">
            <a id="mdlBtnOpenFullKontainer" href="dashboard.php?page=kontainer" class="text-xs font-bold text-[#0170b9] hover:underline flex items-center gap-1">
                <span>Buka di Modul Master Kontainer</span>
                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
            </a>
            <div class="flex items-center gap-2">
                <button type="button" onclick="printInspectedContainerInvoice()" class="px-3 py-1.5 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-xs font-extrabold flex items-center gap-1.5 cursor-pointer transition">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak Faktur &amp; Dossier Resmi (A4)</span>
                </button>
                <button type="button" onclick="closeBerandaInspectModal()" class="px-3 py-1.5 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold cursor-pointer transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================================== -->
<!-- DYNAMIC TELEMETRY, CROSS-FILTER & CHART.JS ENGINE                           -->
<!-- =========================================================================== -->
<script>
// State Global Beranda Dinamis
let berandaState = {
    containers: <?= json_encode($containers_list, JSON_UNESCAPED_SLASHES) ?>,
    events: <?= json_encode($events_list, JSON_UNESCAPED_SLASHES) ?>,
    invoices: <?= json_encode($fin['invoices'], JSON_UNESCAPED_SLASHES) ?>,
    activeStage: null,     // 'stage_1' .. 'stage_6'
    activeZone: null,      // 'Z1' .. 'Z4'
    activeCategory: 'all', // 'all', 'dry_yard', 'reefer', 'rail', 'demurrage', 'customs_hold'
    activeInvoiceTab: 'ALL',
    autoSyncEnabled: true,
    syncInterval: 5,
    countdown: 5,
    inspectedContainer: null
};

const stageNamesMap = {
    'stage_1': 'Stage 1: Gate-In ANPR OCR & Identifikasi Truk',
    'stage_2': 'Stage 2: Jembatan Timbang SOLAS VGM 80T',
    'stage_3': 'Stage 3: Yard Stacking Blok A–E (4 RS + 4 E-RTG)',
    'stage_4': 'Stage 4: Gudang CFS LCL Rampa D1–D5 & Rak Palet SSCC-18 (FL-01..04)',
    'stage_5': 'Stage 5: Reefer Cold Chain Hub (-18.0°C • RS-03)',
    'stage_6': 'Stage 6: Jalur Siding Kereta Api KA Pasoso (RMGC RTG-01 & CDP-TT-01)'
};

const zoneNamesMap = {
    'Z1': 'Zona Spasial Z1: Gerbang ANPR, Timbangan VGM & Buffer Truk (4.2 Ha)',
    'Z2': 'Zona Spasial Z2: Lapangan Penumpukan Peti Kemas Blok A–E (16.5 Ha)',
    'Z3': 'Zona Spasial Z3: Gudang Konsolidasi CFS & Rak Palet SSCC-18 (6.8 Ha)',
    'Z4': 'Zona Spasial Z4: Siding Kereta Api Intermodal & Reefer Hub (7.5 Ha)'
};

function switchBerandaWorkspaceTab(tabKey) {
    const panels = {
        ops: document.getElementById('workspaceTabPanel_ops'),
        zones: document.getElementById('workspaceTabPanel_zones'),
        finance: document.getElementById('workspaceTabPanel_finance')
    };
    const btns = {
        ops: document.getElementById('workspaceTabBtn_ops'),
        zones: document.getElementById('workspaceTabBtn_zones'),
        finance: document.getElementById('workspaceTabBtn_finance'),
        all: document.getElementById('workspaceTabBtn_all')
    };
    const activeCls = 'px-3 py-1.5 rounded-lg text-xs font-extrabold transition flex items-center gap-1.5 bg-[#0170b9] text-white shadow-2xs cursor-pointer';
    const inactiveCls = 'px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 cursor-pointer';

    Object.keys(btns).forEach(k => {
        if (btns[k]) btns[k].className = (k === tabKey) ? activeCls : inactiveCls;
    });

    if (tabKey === 'all') {
        Object.values(panels).forEach(p => { if (p) p.classList.remove('hidden'); });
    } else {
        Object.keys(panels).forEach(k => {
            if (!panels[k]) return;
            if (k === tabKey) panels[k].classList.remove('hidden');
            else panels[k].classList.add('hidden');
        });
    }
    if ((tabKey === 'zones' || tabKey === 'all') && window.cidpWeeklyRevChart) {
        setTimeout(() => window.cidpWeeklyRevChart.resize(), 60);
    }
}

function formatRupiahJs(num) {
    return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
}

// 1. Render Tabel Faktur Komersial Odoo ERP (Opsi C)
function renderBerandaInvoices() {
    const tbody = document.getElementById('berandaInvoiceTableBody');
    if (!tbody) return;

    const filtered = berandaState.invoices.filter(inv => {
        if (berandaState.activeInvoiceTab === 'ALL') return true;
        return inv.payment_status === berandaState.activeInvoiceTab;
    });

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="py-4 text-center text-slate-400 text-xs">Tidak ada faktur pada kategori ini.</td></tr>`;
        return;
    }

    const limVal = document.getElementById('berandaInvLimitSelect')?.value || '5';
    const visible = (limVal === 'all') ? filtered : filtered.slice(0, parseInt(limVal, 10) || 5);

    tbody.innerHTML = visible.map((inv, idx) => {
        let badge = `<span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-bold text-[9px]">MENUNGGU</span>`;
        if (inv.payment_status === 'PAID') {
            badge = `<span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold text-[9px]">LUNAS</span>`;
        } else if (inv.payment_status === 'OVERDUE') {
            badge = `<span class="px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200 font-bold text-[9px]">OVERDUE</span>`;
        }

        const safeInv = encodeURIComponent(JSON.stringify(inv));
        return `
        <tr class="hover:bg-blue-50/30 transition">
            <td class="py-1.5 px-2 text-center font-mono text-slate-400">${idx + 1}</td>
            <td class="py-1.5 px-2 font-mono font-bold text-slate-900">${inv.invoice_number}</td>
            <td class="py-1.5 px-2 font-mono font-bold text-[#0170b9]">${inv.container_number}</td>
            <td class="py-1.5 px-2 font-semibold text-slate-800 truncate max-w-[150px]">${inv.customer_name}</td>
            <td class="py-1.5 px-2 text-slate-600 truncate max-w-[140px]">${inv.service_type}</td>
            <td class="py-1.5 px-2 font-mono font-bold text-slate-900 text-right">${formatRupiahJs(inv.total_amount)}</td>
            <td class="py-1.5 px-2 text-center">${badge}</td>
            <td class="py-1.5 px-2 text-right">
                <button type="button" onclick="printBerandaSingleInvoice('${safeInv}')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-[#0170b9] hover:text-white text-slate-700 font-bold text-[9px] transition cursor-pointer" title="Cetak Faktur Resmi A4 dengan Stempel Basah">
                    <i class="fa-solid fa-print"></i> Faktur
                </button>
            </td>
        </tr>`;
    }).join('');
}

function filterBerandaInvoices(tab) {
    berandaState.activeInvoiceTab = tab;
    ['ALL', 'PAID', 'PENDING', 'OVERDUE'].forEach(t => {
        const el = document.getElementById('invTab_' + t);
        if (!el) return;
        if (t === tab) {
            el.className = 'px-2 py-0.5 rounded font-bold bg-[#0170b9] text-white cursor-pointer transition';
        } else {
            el.className = 'px-2 py-0.5 rounded font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 cursor-pointer transition';
        }
    });
    renderBerandaInvoices();
}

function printBerandaSingleInvoice(encodedInv) {
    try {
        const inv = JSON.parse(decodeURIComponent(encodedInv));
        const no = encodeURIComponent(inv.invoice_number || '');
        const ctr = encodeURIComponent(inv.container_number || '');
        window.open(`document.php?type=invoice&no=${no}&container=${ctr}&autoprint=1`, '_blank');
    } catch (e) {
        console.error(e);
    }
}

// 2. Render Live SCADA Timeline Feed (Cross-Filtered)
function renderBerandaScadaFeed() {
    const container = document.getElementById('berandaScadaTimeline');
    const counterEl = document.getElementById('scadaFeedCounter');
    if (!container) return;

    const filtered = berandaState.events.filter(ev => {
        if (berandaState.activeStage && ev.stage_key !== berandaState.activeStage) return false;
        if (berandaState.activeZone && ev.zone_key !== berandaState.activeZone) return false;
        return true;
    });

    if (counterEl) {
        counterEl.innerText = `Menampilkan ${filtered.length} dari ${berandaState.events.length} log telemetri terbaru`;
    }

    if (filtered.length === 0) {
        container.innerHTML = `<div class="pl-4 py-6 text-slate-400 text-xs">Tidak ada log event SCADA spesifik pada filter ini. Klik "Reset" untuk menampilkan semua aliran.</div>`;
        return;
    }

    container.innerHTML = filtered.map(ev => {
        const t = (ev.raw_type || '').toUpperCase();
        let dotColor = 'bg-[#0170b9]';
        let badgeCls = 'bg-blue-50 text-blue-700 border-blue-200';
        if (t.includes('GATE_OUT')) {
            dotColor = 'bg-emerald-500';
            badgeCls = 'bg-emerald-50 text-emerald-700 border-emerald-200';
        } else if (t.includes('RELOCATION') || t.includes('LIFT') || t.includes('WEIGH')) {
            dotColor = 'bg-orange-500';
            badgeCls = 'bg-orange-50 text-orange-700 border-orange-200';
        } else if (t.includes('REEFER')) {
            dotColor = 'bg-cyan-500';
            badgeCls = 'bg-cyan-50 text-cyan-700 border-cyan-200';
        } else if (t.includes('CFS') || t.includes('STRIP')) {
            dotColor = 'bg-amber-500';
            badgeCls = 'bg-amber-50 text-amber-700 border-amber-200';
        }

        return `
        <div class="relative pl-4 animate-fadeIn">
            <div class="timeline-dot ${dotColor} ring-2 ring-white"></div>
            <div class="flex justify-between items-center">
                <span class="font-bold text-slate-800 text-[11px]">
                    ${ev.event_type}
                    <span class="font-mono ${badgeCls} border px-1 rounded text-[9.5px] ml-1">${ev.container_number}</span>
                </span>
                <span class="text-[9.5px] text-slate-400 font-mono">${ev.time_wib}</span>
            </div>
            <p class="text-[10px] text-slate-500 mt-0.5">${ev.notes}</p>
            <span class="text-[9px] text-slate-400 block mt-0.5"><i class="fa-solid fa-user-gear text-[8px] mr-1"></i>${ev.operator}</span>
        </div>`;
    }).join('');
}

// 3. Render Tabel Konsinyasi Peti Kemas Interaktif (Opsi B + Cross-Filtering)
function getFilteredConsignments() {
    const q = (document.getElementById('consignmentSearchInput')?.value || '').trim().toLowerCase();
    const clearBtn = document.getElementById('btnClearConsignmentSearch');
    if (clearBtn) {
        clearBtn.classList.toggle('hidden', q.length === 0);
    }

    return berandaState.containers.filter(c => {
        if (berandaState.activeStage && c.stage_key !== berandaState.activeStage) return false;
        if (berandaState.activeZone && c.zone_key !== berandaState.activeZone) return false;

        if (berandaState.activeCategory === 'dry_yard') {
            if (!['A', 'B', 'C', 'D', 'E'].includes(c.block)) return false;
        } else if (berandaState.activeCategory === 'reefer') {
            if (c.cargo_type !== 'reefer' && c.block !== 'REEFER') return false;
        } else if (berandaState.activeCategory === 'rail') {
            if (c.stage_key !== 'stage_6' && c.block !== 'RAIL' && c.status !== 'on_rail') return false;
        } else if (berandaState.activeCategory === 'demurrage') {
            if (!c.is_demurrage) return false;
        } else if (berandaState.activeCategory === 'customs_hold') {
            if (c.customs_status === 'SPPB_CLEARED') return false;
        }

        if (q.length > 0) {
            const hay = `${c.container_number} ${c.owner_company} ${c.rfid_tag} ${c.position_label} ${c.size_type} ${c.iso_code}`.toLowerCase();
            if (!hay.includes(q)) return false;
        }
        return true;
    });
}

function renderBerandaConsignments() {
    const tbody = document.getElementById('berandaConsignmentTableBody');
    const countBadge = document.getElementById('consignmentCountBadge');
    if (!tbody) return;

    const filtered = getFilteredConsignments();
    const limVal = document.getElementById('berandaConsignLimitSelect')?.value || '10';
    const visible = (limVal === 'all') ? filtered : filtered.slice(0, parseInt(limVal, 10) || 10);
    if (countBadge) {
        countBadge.innerText = `${visible.length} / ${filtered.length} Unit`;
    }

    if (filtered.length === 0) {
        tbody.innerHTML = `
        <tr>
            <td colspan="7" class="py-6 text-center text-slate-400 text-xs">
                Tidak ditemukan kontainer yang cocok dengan filter saat ini.
                <button type="button" onclick="resetBerandaFilters()" class="ml-2 text-[#0170b9] font-bold underline cursor-pointer">Reset Filter</button>
            </td>
        </tr>`;
        return;
    }

    tbody.innerHTML = visible.map((c, idx) => {
        const isDem = !!c.is_demurrage;
        const dwellBadge = isDem
            ? `<span class="px-1.5 py-0.2 rounded bg-rose-50 text-rose-700 border border-rose-200 font-mono font-bold text-[9px]" title="Denda Demurrage: ${formatRupiahJs(c.demurrage_rp)}">${c.dwell_days}H ⚠️ Denda</span>`
            : `<span class="px-1.5 py-0.2 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono font-bold text-[9px]">${c.dwell_days}H ✓ Aman</span>`;

        const custBadge = (c.customs_status === 'SPPB_CLEARED')
            ? `<span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-1.5 py-0.5 rounded text-[9px] font-bold">SPPB Clear ✓</span>`
            : `<span class="bg-rose-50 text-rose-700 border border-rose-200 px-1.5 py-0.5 rounded text-[9px] font-bold">${c.customs_status}</span>`;

        const safeCtr = encodeURIComponent(JSON.stringify(c));
        return `
        <tr onclick="openBerandaInspectModal('${safeCtr}')" class="hover:bg-blue-50/40 transition cursor-pointer group">
            <td class="py-2 px-2 text-center font-mono text-slate-400">${idx + 1}</td>
            <td class="py-2 px-2">
                <div class="font-mono font-bold text-[#0170b9] group-hover:underline flex items-center gap-1">
                    <span>${c.container_number}</span>
                    <span class="text-[8px] px-1 rounded bg-slate-100 text-slate-500">${c.zone_key}</span>
                </div>
                <div class="text-[9.5px] text-slate-500 truncate max-w-[165px]">${c.owner_company}</div>
            </td>
            <td class="py-2 px-2 text-slate-700 font-medium">${c.size_type} <span class="text-slate-400 font-mono text-[9.5px]">(${c.iso_code})</span></td>
            <td class="py-2 px-2 font-mono text-[9.5px] text-purple-700 font-semibold">${c.rfid_tag}</td>
            <td class="py-2 px-2">
                <div class="font-semibold text-slate-800 text-[10px]">${c.position_label}</div>
                <div class="mt-0.5">${dwellBadge}</div>
            </td>
            <td class="py-2 px-2">${custBadge}</td>
            <td class="py-2 px-2 text-right">
                <button type="button" onclick="event.stopPropagation(); openBerandaInspectModal('${safeCtr}')" class="px-2 py-1 rounded-md bg-blue-50 group-hover:bg-[#0170b9] text-[#0170b9] group-hover:text-white border border-blue-200 font-bold text-[9.5px] transition cursor-pointer">
                    <i class="fa-solid fa-magnifying-glass-plus mr-0.5"></i> 3-Flow
                </button>
            </td>
        </tr>`;
    }).join('');
}

// 4. Fungsi Cross-Filtering (Stage, Zona Spasial, & Kategori)
function updateCrossFilterUI() {
    const banner = document.getElementById('activeCrossFilterBanner');
    const titleEl = document.getElementById('activeCrossFilterTitle');
    const btnResetRibbon = document.getElementById('btnResetPipelineFilter');

    // Highlight Stage Cards
    for (let i = 1; i <= 6; i++) {
        const card = document.getElementById('ribbon_stage_' + i);
        if (!card) continue;
        if (berandaState.activeStage === 'stage_' + i) {
            card.classList.add('ring-2', 'ring-[#ea580c]', 'bg-orange-50/60');
        } else {
            card.classList.remove('ring-2', 'ring-[#ea580c]', 'bg-orange-50/60');
        }
    }

    // Highlight Top KPI Cards selaras dengan stage yang aktif
    const kpiStageMap = {
        'stage_3': { id: 'kpiCard_yard', cls: ['ring-2', 'ring-[#0170b9]', 'bg-blue-50/40'] },
        'stage_1': { id: 'kpiCard_gate', cls: ['ring-2', 'ring-[#ea580c]', 'bg-orange-50/40'] },
        'stage_6': { id: 'kpiCard_rail', cls: ['ring-2', 'ring-[#0170b9]', 'bg-blue-50/40'] },
        'stage_5': { id: 'kpiCard_reefer', cls: ['ring-2', 'ring-cyan-500', 'bg-cyan-50/40'] },
        'stage_4': { id: 'kpiCard_cfs', cls: ['ring-2', 'ring-amber-500', 'bg-amber-50/40'] },
    };
    Object.keys(kpiStageMap).forEach(stg => {
        const def = kpiStageMap[stg];
        const el = document.getElementById(def.id);
        if (!el) return;
        if (berandaState.activeStage === stg) {
            el.classList.add(...def.cls);
        } else {
            el.classList.remove(...def.cls);
        }
    });

    // Highlight Spatial Zone Cards
    ['Z1', 'Z2', 'Z3', 'Z4'].forEach(z => {
        const zCard = document.getElementById('zoneCard_' + z);
        if (!zCard) return;
        if (berandaState.activeZone === z) {
            zCard.classList.add('ring-2', 'ring-[#0170b9]', 'bg-blue-50/60');
        } else {
            zCard.classList.remove('ring-2', 'ring-[#0170b9]', 'bg-blue-50/60');
        }
    });

    // Highlight Category Buttons
    ['all', 'dry_yard', 'reefer', 'rail', 'demurrage'].forEach(cat => {
        const btn = document.getElementById('catBtn_' + cat);
        if (!btn) return;
        if (berandaState.activeCategory === cat) {
            btn.className = 'px-2 py-1 rounded-md font-bold bg-[#0170b9] text-white cursor-pointer transition shadow-2xs';
        } else {
            btn.className = 'px-2 py-1 rounded-md font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 cursor-pointer transition';
        }
    });

    const hasActiveFilter = !!(berandaState.activeStage || berandaState.activeZone || berandaState.activeCategory !== 'all');
    if (btnResetRibbon) {
        btnResetRibbon.classList.toggle('hidden', !hasActiveFilter);
    }
    if (banner && titleEl) {
        if (hasActiveFilter) {
            banner.classList.remove('hidden');
            let parts = [];
            if (berandaState.activeStage) parts.push(stageNamesMap[berandaState.activeStage]);
            if (berandaState.activeZone) parts.push(zoneNamesMap[berandaState.activeZone]);
            if (berandaState.activeCategory !== 'all') parts.push('Kategori: ' + berandaState.activeCategory.toUpperCase());
            titleEl.innerText = parts.join(' • ');
        } else {
            banner.classList.add('hidden');
        }
    }

    renderBerandaScadaFeed();
    renderBerandaConsignments();
            if (typeof currentTrackedAsset === 'string' && currentTrackedAsset) {
                executeTocSearch(currentTrackedAsset, true);
            }
}

function setBerandaStageFilter(stageKey) {
    berandaState.activeStage = (berandaState.activeStage === stageKey) ? null : stageKey;
    berandaState.activeZone = null;
    berandaState.activeCategory = 'all';
    updateCrossFilterUI();
}

function setBerandaZoneFilter(zoneKey) {
    berandaState.activeZone = (berandaState.activeZone === zoneKey) ? null : zoneKey;
    berandaState.activeStage = null;
    berandaState.activeCategory = 'all';
    updateCrossFilterUI();
}

function setBerandaCategoryFilter(catKey) {
    berandaState.activeCategory = (berandaState.activeCategory === catKey && catKey !== 'all') ? 'all' : catKey;
    berandaState.activeStage = null;
    berandaState.activeZone = null;
    updateCrossFilterUI();
}

function clearConsignmentSearch() {
    const inp = document.getElementById('consignmentSearchInput');
    if (inp) inp.value = '';
    renderBerandaConsignments();
}

function resetBerandaFilters() {
    berandaState.activeStage = null;
    berandaState.activeZone = null;
    berandaState.activeCategory = 'all';
    clearConsignmentSearch();
    updateCrossFilterUI();
}

// 5. Modal Inspeksi 3-Aliran Kontainer
function openBerandaInspectModal(encodedCtr) {
    try {
        const c = (typeof encodedCtr === 'object' && encodedCtr !== null)
            ? encodedCtr
            : JSON.parse(decodeURIComponent(encodedCtr));
        berandaState.inspectedContainer = c;
        document.getElementById('mdlCtrNumber').innerText = c.container_number;
        document.getElementById('mdlCtrOwner').innerText = c.owner_company;
        document.getElementById('mdlCtrSpec').innerText = `${c.size_type} (${c.iso_code}) • ${Number(c.gross_weight_kg).toLocaleString('id-ID')} kg`;
        document.getElementById('mdlCtrPos').innerText = c.position_label;
        document.getElementById('mdlCtrZone').innerText = c.zone_key + ' (' + c.stage_key.replace('_', ' ').toUpperCase() + ')';
        document.getElementById('mdlCtrWeight').innerText = Number(c.gross_weight_kg).toLocaleString('id-ID') + ' kg';
        document.getElementById('mdlCtrSeal').innerText = c.seal_number;
        document.getElementById('mdlCtrRfid').innerText = c.rfid_tag;
        document.getElementById('mdlCtrSscc').innerText = c.sscc_code;
        document.getElementById('mdlCtrCustoms').innerText = c.customs_status;
        document.getElementById('mdlCtrTime').innerText = c.updated_wib;
        document.getElementById('mdlCtrDwell').innerText = c.dwell_days + ' Hari ' + (c.is_demurrage ? '(Demurrage Aktif)' : '(Free Time)');
        document.getElementById('mdlCtrStorage').innerText = formatRupiahJs(c.storage_rp);
        document.getElementById('mdlCtrDemurrage').innerText = formatRupiahJs(c.demurrage_rp);
        document.getElementById('mdlBtnOpenFullKontainer').href = 'dashboard.php?page=kontainer&search=' + encodeURIComponent(c.container_number);

        const mdl = document.getElementById('berandaInspectModal');
        mdl.classList.remove('hidden');
        mdl.classList.add('flex');
    } catch (e) {
        console.error(e);
    }
}

function closeBerandaInspectModal() {
    const mdl = document.getElementById('berandaInspectModal');
    if (mdl) {
        mdl.classList.add('hidden');
        mdl.classList.remove('flex');
    }
}

function printInspectedContainerInvoice() {
    const c = berandaState.inspectedContainer;
    if (!c || !c.container_number) return;
    const ctr = encodeURIComponent(c.container_number);
    window.open(`document.php?type=invoice&container=${ctr}&autoprint=1`, '_blank');
}

// 6. OPSI A: Live Auto-Sync Telemetry Polling Engine
async function fetchLiveBerandaTelemetry(manual = false) {
    const spinner = document.getElementById('syncSpinnerIcon');
    const feedBadge = document.getElementById('scadaFeedStatusBadge');
    if (spinner) spinner.classList.add('fa-spin');
    if (feedBadge) feedBadge.innerHTML = `<i class="fa-solid fa-rotate fa-spin mr-0.5"></i>Syncing Telemetry...`;

    try {
        const res = await fetch('api/beranda_telemetry.php?jitter=1&t=' + Date.now());
        const data = await res.json();
        if (data && data.success) {
            berandaState.containers = data.containers || berandaState.containers;
            berandaState.events = data.events || berandaState.events;
            if (data.financial && data.financial.invoices) {
                berandaState.invoices = data.financial.invoices;
            }

            // Update KPI DOM & Progress Rings
            const k = data.kpi;
            const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.innerText = val; };
            const setRing = (id, pct) => {
                const el = document.getElementById(id);
                if (el) el.setAttribute('stroke-dasharray', Math.min(100, Math.max(0, Math.round(pct))) + ', 100');
            };
            setTxt('kpiYardVal', Number(k.containers_yard).toLocaleString('id-ID'));
            setTxt('kpiYardPct', Math.round(k.yard_occupancy) + '%');
            setRing('kpiYardRing', k.yard_occupancy);
            setTxt('kpiGateVal', k.gate_turnaround);
            setTxt('kpiGateGaugeTxt', k.gate_turnaround + 'm');
            setTxt('kpiTrucksCount', k.total_trucks);
            setTxt('kpiRailVal', k.loaded_wagons + '/' + k.total_wagons);
            setTxt('kpiRailPct', Math.round(k.siding_pct) + '%');
            setRing('kpiRailRing', k.siding_pct);
            setTxt('kpiReeferTemp', k.reefer_temp + '°C');
            setTxt('kpiReeferPlugs', k.reefer_plugs);
            setTxt('kpiReeferPct', Math.round(k.reefer_pct) + '%');
            setRing('kpiReeferRing', k.reefer_pct);
            setTxt('kpiCustomsVal', Number(k.customs_pct).toFixed(1) + '%');
            setTxt('kpiCustomsBox', k.customs_cleared + '/' + k.customs_total);
            setTxt('kpiCfsJobs', k.cfs_jobs);
            if (typeof k.cfs_pallets_in_rack !== 'undefined') {
                setTxt('kpiCfsPalletsRack', k.cfs_pallets_in_rack);
                setTxt('ribbonCfsPalletCount', k.cfs_pallets_in_rack);
                setTxt('stripCfsPallets', k.cfs_pallets_in_rack);
            }
            if (typeof k.cfs_weight_kg !== 'undefined') {
                setTxt('kpiCfsWeightTxt', Math.round(k.cfs_weight_kg).toLocaleString('id-ID'));
            }
            if (typeof k.cfs_volume_cbm !== 'undefined') {
                setTxt('stripCfsCbm', Number(k.cfs_volume_cbm).toFixed(1));
            }
            if (typeof k.cfs_pct !== 'undefined') {
                setTxt('kpiCfsPct', Math.round(k.cfs_pct) + '%');
                setRing('kpiCfsRing', k.cfs_pct);
            }
            setTxt('badgeScadaUptime', 'SCADA ' + k.scada_uptime + '%');
            setTxt('ceisaSppbSec', k.sppb_sec + ' Detik');

            if (data.mhe) {
                setTxt('mheRsCount', data.mhe.rs_total || 4);
                setTxt('mheRtgCount', data.mhe.rtg_total || 5);
                setTxt('mheFlCount', data.mhe.fl_total || 4);
                setTxt('mheTtCount', data.mhe.tt_total || 2);
                setTxt('mheActiveSummary', `${data.mhe.active_units || 0} Aktif • ${data.mhe.idle_units || 0} Siaga`);
            }

            if (data.zones) {
                const setBar = (barId, txtId, pct) => {
                    const b = document.getElementById(barId);
                    const t = document.getElementById(txtId);
                    if (b) b.style.width = pct + '%';
                    if (t) t.innerText = pct + '%';
                };
                setBar('zoneZ1Bar', 'zoneZ1PctTxt', data.zones.z1_pct);
                setBar('zoneZ2Bar', null, data.zones.z2_pct);
                setBar('zoneZ3Bar', 'zoneZ3PctTxt', data.zones.z3_pct);
                setBar('zoneZ4Bar', 'zoneZ4PctTxt', data.zones.z4_pct);
            }

            if (data.dwell_buckets) {
                setTxt('dwSafePct', data.dwell_buckets.pct_1_2 + '%');
                setTxt('dwWarnPct', data.dwell_buckets.pct_3 + '%');
                setTxt('dwDendaPct', data.dwell_buckets.pct_4p + '%');
                if (window.cidpDwellingChart) {
                    window.cidpDwellingChart.data.datasets[0].data = [
                        data.dwell_buckets.pct_1_2,
                        data.dwell_buckets.pct_3,
                        data.dwell_buckets.pct_4_5,
                        data.dwell_buckets.pct_6p
                    ];
                    window.cidpDwellingChart.update('none');
                }
            }

            // Update Latencies
            if (data.latencies) {
                setTxt('ceisaLatencyTxt', 'Latency ' + data.latencies.ceisa + 'ms');
                setTxt('odooLatencyTxt', data.latencies.odoo_erp);
                setTxt('latCeisaPill', data.latencies.ceisa + 'ms');
                setTxt('latInaportPill', data.latencies.inaport + 'ms');
                setTxt('latKaiPill', data.latencies.kai_edi + 'ms');
                setTxt('latLoraPill', data.latencies.lorawan + 'ms');
                setTxt('latOdooPill', data.latencies.odoo_erp + 'ms');
            }

            // Update Financial Pillar
            if (data.financial) {
                setTxt('finTotalInvoiced', formatRupiahJs(data.financial.total_invoiced));
                setTxt('finPaidRevenue', formatRupiahJs(data.financial.paid_revenue));
                setTxt('finPendingReceivable', formatRupiahJs(data.financial.pending_receivable));
                setTxt('finDemurrageAccrual', formatRupiahJs(data.financial.demurrage_accrual_rp));
                setTxt('finDemurrageCount', data.financial.demurrage_box_count);
                setTxt('finSettlementPct', data.financial.settlement_pct + '%');
            }

            // Update Quantitative SLA Performance (OTIF, MAE/MAPE, Cycle Time, Cost Efficiency)
            if (data.performance) {
                const pf = data.performance;
                setTxt('perfOtifVal', Number(pf.otif_rate_pct).toFixed(1) + '%');
                setTxt('perfMapeVal', 'MAPE ' + Number(pf.forecast_mape_pct).toFixed(1) + '%');
                setTxt('perfMaeBadge', 'MAE ' + pf.forecast_mae);
                setTxt('perfAccVal', Number(pf.forecast_accuracy_pct).toFixed(1) + '%');
                setTxt('perfCycleEffVal', '-' + Number(pf.cycle_efficiency_pct).toFixed(1) + '%');
                setTxt('perfGateTatVal', pf.gate_tat_min);
                setTxt('perfCostEffVal', Number(pf.cost_efficiency_pct).toFixed(1) + '%');
                setTxt('perfSavingVal', 'Rp ' + (Number(pf.monthly_saving_idr) / 1000000).toFixed(1).replace('.', ',') + ' Jt/bln');
                setTxt('chartVbsMae', pf.forecast_mae);
                setTxt('chartVbsMape', Number(pf.forecast_mape_pct).toFixed(1) + '%');
                if (window.cidpGateVbsChart && Array.isArray(pf.actual_series) && Array.isArray(pf.forecast_series)) {
                    window.cidpGateVbsChart.data.datasets[0].data = pf.actual_series;
                    window.cidpGateVbsChart.data.datasets[1].data = pf.forecast_series;
                    window.cidpGateVbsChart.update('none');
                }
            }

            renderBerandaInvoices();
            renderBerandaScadaFeed();
            renderBerandaConsignments();
        }
    } catch (err) {
        console.warn('Live telemetry sync error:', err);
    } finally {
        setTimeout(() => {
            if (spinner) spinner.classList.remove('fa-spin');
            if (feedBadge) feedBadge.innerHTML = `<i class="fa-solid fa-bolt mr-0.5 text-[8px]"></i>Synced ${new Date().toLocaleTimeString('id-ID')} WIB`;
        }, 300);
        berandaState.countdown = berandaState.syncInterval || 5;
    }
}

function toggleBerandaAutoSync() {
    berandaState.autoSyncEnabled = !berandaState.autoSyncEnabled;
    const dot = document.getElementById('autoSyncDot');
    const cd = document.getElementById('autoSyncCountdown');
    if (berandaState.autoSyncEnabled) {
        berandaState.countdown = berandaState.syncInterval || 5;
        if (dot) dot.className = 'w-2 h-2 rounded-full bg-emerald-500';
        if (cd) cd.innerText = '15s';
    } else {
        if (dot) dot.className = 'w-2 h-2 rounded-full bg-slate-400';
        if (cd) cd.innerText = 'OFF';
    }
}

function exportBerandaToExcel() {
    const rowsData = getFilteredConsignments();
    const headers = ['No Kontainer', 'Shipper / Pemilik', 'Tipe ISO', 'Tag RFID UHF', 'Kode GS1 SSCC', 'Posisi Lapangan', 'Zona Spasial', 'Dwell Time (Hari)', 'Status Bea Cukai', 'Biaya Storage (Rp)', 'Denda Demurrage (Rp)'];
    const rows = rowsData.map(c => [
        c.container_number,
        c.owner_company,
        c.size_type + ' (' + c.iso_code + ')',
        c.rfid_tag,
        c.sscc_code,
        c.position_label,
        c.zone_key,
        c.dwell_days,
        c.customs_status,
        c.storage_rp,
        c.demurrage_rp
    ]);
    if (typeof CIDPExport !== 'undefined') {
        CIDPExport.toExcel(headers, rows, 'Control_Tower_Consignments', 'CIDP_Control_Tower_35Ha');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi Tabel & Feed Dinamis
    renderBerandaInvoices();
    renderBerandaScadaFeed();
    renderBerandaConsignments();

    // Live Digital Clock & Auto-Sync Countdown Ticker
    function tickBerandaClockAndSync() {
        const el = document.getElementById('berandaLiveClock');
        if (el) {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            el.innerText = h + ':' + m + ':' + s + ' WIB';
        }

        if (berandaState.autoSyncEnabled) {
            berandaState.countdown--;
            const cdEl = document.getElementById('autoSyncCountdown');
            if (cdEl) cdEl.innerText = berandaState.countdown + 's';
            if (berandaState.countdown <= 0) {
                fetchLiveBerandaTelemetry(false);
            }
        }
    }
    setInterval(tickBerandaClockAndSync, 1000);
    tickBerandaClockAndSync();

    if (typeof Chart === 'undefined') {
        return;
    }

    // Konfigurasi Global Chart.js (Keterbacaan Eksekutif)
    Chart.defaults.font.family = '"Plus Jakarta Sans", sans-serif';
    Chart.defaults.font.size = 11.5;
    Chart.defaults.color = '#475569';
    Chart.defaults.plugins.tooltip.backgroundColor = '#0f172a';
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 8;
    Chart.defaults.plugins.tooltip.titleFont = { size: 12, weight: 'bold' };
    Chart.defaults.plugins.tooltip.bodyFont = { size: 11.5 };

    // 1. Dual Smooth Area Chart: Gate Throughput vs VBS Hourly Demand
    const gateVbsCanvas = document.getElementById('gateVbsAreaChart');
    if (gateVbsCanvas) {
        window.cidpGateVbsChart = new Chart(gateVbsCanvas, {
            type: 'line',
            data: {
                labels: <?= json_encode($perf['forecast_labels']) ?>,
                datasets: [
                    {
                        label: 'Aktual Gate-In',
                        data: <?= json_encode($perf['actual_series']) ?>,
                        borderColor: '#0170b9',
                        backgroundColor: 'rgba(1, 112, 185, 0.10)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2.5,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#0170b9',
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5
                    },
                    {
                        label: 'Forecast VBS',
                        data: <?= json_encode($perf['forecast_series']) ?>,
                        borderColor: '#ea580c',
                        backgroundColor: 'transparent',
                        fill: false,
                        tension: 0.35,
                        borderWidth: 2,
                        borderDash: [5, 4],
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#ea580c',
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 9, padding: 12, font: { size: 11.5, weight: '600' }, color: '#334155' }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [2, 4], color: '#e2e8f0' },
                        ticks: { font: { size: 10.5 }, color: '#475569' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10.5 }, color: '#475569' }
                    }
                }
            }
        });
    }

    // 2. Heavy Equipment SCADA OEE: 5-Axis Pentagon Spider Radar Chart (15 Unit MHE)
    const radarCanvas = document.getElementById('scadaEquipmentRadarChart');
    if (radarCanvas) {
        new Chart(radarCanvas, {
            type: 'radar',
            data: {
                labels: ['Moves/Jam', 'Efisiensi Energi', 'Uptime Mesin', 'Maneuver Speed', 'K3 Safety'],
                datasets: [
                    {
                        label: '4 RS & 2 Shuttle (6 Unit)',
                        data: [93, 88, 97, 91, 96],
                        borderColor: '#0170b9',
                        backgroundColor: 'rgba(1, 112, 185, 0.10)',
                        borderWidth: 2.0,
                        pointBackgroundColor: '#0170b9',
                        pointRadius: 2.5
                    },
                    {
                        label: '5 E-RTG & RMGC (5 Unit)',
                        data: [90, 96, 95, 86, 99],
                        borderColor: '#ea580c',
                        backgroundColor: 'rgba(234, 88, 12, 0.08)',
                        borderWidth: 2.0,
                        pointBackgroundColor: '#ea580c',
                        pointRadius: 2.5
                    },
                    {
                        label: '4 Forklift CFS (4 Unit)',
                        data: [94, 98, 97, 92, 98],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.08)',
                        borderWidth: 2.0,
                        pointBackgroundColor: '#10b981',
                        pointRadius: 2.5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 8, padding: 8, font: { size: 10, weight: '600' }, color: '#334155' }
                    }
                },
                scales: {
                    r: {
                        min: 60,
                        max: 100,
                        ticks: { display: false, stepSize: 10 },
                        grid: { color: '#e2e8f0' },
                        angleLines: { color: '#e2e8f0' },
                        pointLabels: {
                            font: { size: 10.5, weight: '700' },
                            color: '#334155'
                        }
                    }
                }
            }
        });
    }

    // 3. Container Dwelling Time & Demurrage Risk (100% Database-Driven)
    const dwellingCanvas = document.getElementById('dwellingTimeBarChart');
    if (dwellingCanvas) {
        window.cidpDwellingChart = new Chart(dwellingCanvas, {
            type: 'bar',
            data: {
                labels: ['1-2 Hari', '3 Hari', '4-5 Hari', '6+ Hari'],
                datasets: [{
                    label: 'Porsi Peti Kemas (%)',
                    data: <?= json_encode([$dwell_b['pct_1_2'], $dwell_b['pct_3'], $dwell_b['pct_4_5'], $dwell_b['pct_6p']]) ?>,
                    backgroundColor: ['#10b981', '#f59e0b', '#ea580c', '#ef4444'],
                    borderRadius: 6,
                    barThickness: 26
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                onClick: function(evt, elements) {
                    if (elements.length > 0) {
                        const idx = elements[0].index;
                        if (idx >= 2) {
                            setBerandaCategoryFilter('demurrage');
                        } else {
                            setBerandaCategoryFilter('all');
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) { return ' ' + ctx.raw + '% dari total kontainer (Klik bar untuk filter)'; }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { borderDash: [2, 4], color: '#e2e8f0' },
                        ticks: {
                            font: { size: 10.5 },
                            color: '#475569',
                            callback: function(v) { return v + '%'; }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11, weight: '600' }, color: '#334155' }
                    }
                }
            }
        });
    }

    // 4. Multimodal Modal-Split & ESG: Segmented Donut Chart (Thick Ring & High Contrast)
    const modalSplitCanvas = document.getElementById('modalSplitDonutChart');
    if (modalSplitCanvas) {
        new Chart(modalSplitCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Kereta Api (68%)', 'Truk Jalan (32%)'],
                datasets: [{
                    data: [68, 32],
                    backgroundColor: ['#0170b9', '#ea580c'],
                    borderColor: '#ffffff',
                    borderWidth: 2.5,
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '58%',
                onClick: function(evt, elements) {
                    if (elements.length > 0 && elements[0].index === 0) {
                        setBerandaStageFilter('stage_6');
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 10, padding: 12, font: { size: 11.5, weight: '700' }, color: '#334155' }
                    }
                }
            },
            plugins: [{
                id: 'centerTextSplit',
                beforeDraw: function(chart) {
                    const { ctx, chartArea } = chart;
                    if (!chartArea) return;
                    ctx.save();
                    const centerX = (chartArea.left + chartArea.right) / 2;
                    const centerY = (chartArea.top + chartArea.bottom) / 2 - 4;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.font = "bold 0.82rem 'Plus Jakarta Sans', sans-serif";
                    ctx.fillStyle = "#0170b9";
                    ctx.fillText("68% Rail", centerX, centerY - 2);
                    ctx.font = "600 0.55rem 'Plus Jakarta Sans', sans-serif";
                    ctx.fillStyle = "#64748b";
                    ctx.fillText("Green ICD", centerX, centerY + 10);
                    ctx.restore();
                }
            }]
        });
    }

    // 5. Weekly Revenue Breakdown: Stacked Bar Chart
    const revenueCanvas = document.getElementById('weeklyRevenueChart');
    if (revenueCanvas) {
        window.cidpWeeklyRevChart = new Chart(revenueCanvas, {
            type: 'bar',
            data: {
                labels: ['H-6', 'H-5', 'H-4', 'H-3', 'H-2', 'H-1', 'Hari Ini'],
                datasets: [
                    {
                        label: 'Lift-On/Off',
                        data: [14.2, 15.8, 13.5, 17.2, 18.0, 12.4, 20.5],
                        backgroundColor: '#0170b9',
                        borderRadius: { topLeft: 0, topRight: 0, bottomLeft: 2, bottomRight: 2 }
                    },
                    {
                        label: 'Storage & CFS',
                        data: [9.5, 10.2, 9.8, 11.5, 12.0, 10.1, 13.8],
                        backgroundColor: '#ea580c'
                    },
                    {
                        label: 'Reefer 380V',
                        data: [5.8, 6.2, 5.9, 6.8, 7.1, 5.5, 7.8],
                        backgroundColor: '#06b6d4'
                    },
                    {
                        label: 'SOLAS VGM',
                        data: [3.2, 3.8, 3.1, 4.2, 4.5, 2.8, 4.9],
                        backgroundColor: '#6366f1',
                        borderRadius: { topLeft: 2, topRight: 2, bottomLeft: 0, bottomRight: 0 }
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 6, padding: 4, font: { size: 8.5 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) { return ' ' + ctx.dataset.label + ': Rp ' + ctx.raw + ' Jt'; }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false },
                        ticks: { font: { size: 8.5 } }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        grid: { borderDash: [2, 4], color: '#f1f5f9' },
                        ticks: {
                            font: { size: 8.5 },
                            callback: function(v) { return v + 'Jt'; }
                        }
                    }
                }
            }
        });
    }
});

// =============================================================================
// TOC UNIVERSAL ASSET & CARGO LIVE LOCATOR CONTROLLER
// =============================================================================
let currentTrackedAsset = '';

function cycleSyncSpeed() {
    const speeds = [5, 15, 30];
    const current = berandaState.syncInterval || 5;
    const nextIdx = (speeds.indexOf(current) + 1) % speeds.length;
    berandaState.syncInterval = speeds[nextIdx];
    berandaState.countdown = berandaState.syncInterval;
    const btn = document.getElementById('btnToggleSyncSpeed');
    const cd = document.getElementById('autoSyncCountdown');
    if (btn) btn.innerText = '⚡ ' + berandaState.syncInterval + 's' + (berandaState.syncInterval === 5 ? ' (Live)' : '');
    if (cd) cd.innerText = berandaState.syncInterval + 's';
}

function closeTocResultPanel() {
    const panel = document.getElementById('tocLiveResultPanel');
    if (panel) panel.classList.add('hidden');
}

function clearTocSearch() {
    const inp = document.getElementById('tocAssetSearchInput');
    const btn = document.getElementById('btnTocClearSearch');
    if (inp) { inp.value = ''; inp.focus(); }
    if (btn) btn.classList.add('hidden');
    closeTocResultPanel();
}

function trackSampleAsset(q) {
    const inp = document.getElementById('tocAssetSearchInput');
    if (inp) inp.value = q;
    executeTocSearch(q);
}

function executeTocSearch(queryOverride, silent = false) {
    const inp = document.getElementById('tocAssetSearchInput');
    const clearBtn = document.getElementById('btnTocClearSearch');
    const q = (queryOverride || (inp ? inp.value : '')).trim();
    if (clearBtn) {
        if (q) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }
    if (!q) return;

    currentTrackedAsset = q;
    fetch('api/track_asset.php?q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
            if (data.success && data.found) {
                const panel = document.getElementById('tocLiveResultPanel');
                if (panel) panel.classList.remove('hidden');
                renderTocResult(data);
            } else {
                if (!silent) alert(data.message || 'Aset tidak ditemukan');
            }
        })
        .catch(err => {
            console.error('Error tracking asset:', err);
        });
}

function renderTocResult(data) {
    const a = data.asset;
    const p = data.position;
    const b = data.billing;

    const locText = document.getElementById('tocResLocationText');
    const locDetail = document.getElementById('tocResLocationDetail');
    const badge = document.getElementById('tocResBadge');
    const zone = document.getElementById('tocResZone');
    const cargo = document.getElementById('tocResCargo');
    const truck = document.getElementById('tocResTruck');
    const equip = document.getElementById('tocResEquip');
    const customs = document.getElementById('tocResCustoms');
    const billing = document.getElementById('tocResBilling');
    const yardLink = document.getElementById('btnTocYardLink');

    if (locText) locText.innerText = p.location_title;
    if (locDetail) locDetail.innerText = p.location_detail;
    if (badge) {
        badge.innerText = p.badge_label;
        if (p.badge_color === 'cyan') badge.className = 'px-2 py-0.5 rounded-md text-[9.5px] font-extrabold uppercase tracking-wider bg-cyan-50 text-cyan-800 border border-cyan-200';
        else if (p.badge_color === 'emerald') badge.className = 'px-2 py-0.5 rounded-md text-[9.5px] font-extrabold uppercase tracking-wider bg-emerald-50 text-emerald-800 border border-emerald-200';
        else if (p.badge_color === 'amber') badge.className = 'px-2 py-0.5 rounded-md text-[9.5px] font-extrabold uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200';
        else if (p.badge_color === 'slate') badge.className = 'px-2 py-0.5 rounded-md text-[9.5px] font-extrabold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-300';
        else badge.className = 'px-2 py-0.5 rounded-md text-[9.5px] font-extrabold uppercase tracking-wider bg-sky-50 text-sky-800 border border-sky-200';
    }
    if (zone) zone.innerText = p.zone_name;
    if (cargo) cargo.innerText = (a.cargo_type || 'DRY') + ' ' + (a.size_type || '40FT HC');
    if (truck) truck.innerText = (a.license_plate !== '-' ? a.license_plate : 'N/A') + (a.driver_name ? ' (' + a.driver_name + ')' : '');
    if (equip) equip.innerText = a.handling_equipment || 'Reach Stacker RS-02';
    if (customs) {
        customs.innerText = a.customs_status === 'SPPB_CLEARED' ? 'SPPB Cleared (Jalur Hijau)' : (a.customs_status === 'RED_HOLD' ? 'Tahanan Jalur Merah' : 'Menunggu Kliring CEISA 4.0');
        customs.className = a.customs_status === 'SPPB_CLEARED' ? 'text-emerald-700' : 'text-amber-700';
    }
    if (billing) {
        billing.innerText = (b.amount_formatted !== '-' ? b.amount_formatted : 'Rp 0') + ' (' + b.payment_status + ' • ' + (b.odoo_status.includes('SYNCED') ? 'Odoo Synced' : 'Ready') + ')';
    }
    if (yardLink) {
        if (p.block && p.block !== 'OUTSIDE') {
            yardLink.href = 'dashboard.php?page=yard&block=' + encodeURIComponent(p.block);
        } else {
            yardLink.href = 'dashboard.php?page=gate';
        }
    }

    ['OUTSIDE', 'Z1', 'Z2', 'Z3', 'Z4', 'GATE_OUT'].forEach(st => {
        const dot = document.getElementById('flowStep_' + st);
        if (dot) {
            dot.className = 'w-2 h-2 rounded-full bg-slate-300 transition';
        }
    });

    const activeStepDot = document.getElementById('flowStep_' + p.zone_code);
    if (activeStepDot) {
        activeStepDot.className = 'w-2 h-2 rounded-full bg-orange-600 ring-2 ring-orange-200 animate-pulse';
    }
}

function inspectCurrentTocAsset() {
    if (!currentTrackedAsset) return;
    const found = (berandaState.containers || []).find(c => c.container_number.toUpperCase() === currentTrackedAsset.toUpperCase());
    if (found) {
        openBerandaInspectModal(found);
    } else {
        window.location.href = 'dashboard.php?page=kontainer&search=' + encodeURIComponent(currentTrackedAsset);
    }
}
</script>
