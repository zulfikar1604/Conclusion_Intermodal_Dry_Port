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

$beranda_data = build_beranda_telemetry($pdo ?? null, false);
$kpi          = $beranda_data['kpi'];
$fin          = $beranda_data['financial'];
$latencies    = $beranda_data['latencies'];
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
        <!-- 1. TOP EXECUTIVE COCKPIT BAR (UNIFIED BOARDROOM HEADER + OPSI A & D)   -->
        <!-- ======================================================================= -->
        <div class="bg-white/95 backdrop-blur-md rounded-xl px-4 py-3 shadow-2xs border border-slate-200/90 relative overflow-hidden flex flex-col xl:flex-row items-start xl:items-center justify-between gap-3 transition-all">
            <!-- Subtle Luxury Batik Kawung Rosette Watermark -->
            <div class="absolute -right-6 -top-6 w-40 h-40 pointer-events-none opacity-[0.09] z-0" aria-hidden="true">
                <svg width="100%" height="100%" viewBox="0 0 100 100" fill="none" stroke="#004b87" stroke-width="1.6">
                    <ellipse cx="50" cy="22" rx="16" ry="24" />
                    <ellipse cx="50" cy="78" rx="16" ry="24" />
                    <ellipse cx="22" cy="50" rx="24" ry="16" />
                    <ellipse cx="78" cy="50" rx="24" ry="16" />
                    <circle cx="50" cy="50" r="4.5" fill="#ea580c" />
                    <circle cx="50" cy="50" r="14" stroke="#004b87" stroke-width="1" stroke-dasharray="2 2" />
                </svg>
            </div>

            <div class="flex items-center gap-3 relative z-10">
                <div class="w-9 h-9 rounded-xl bg-orange-50 border border-orange-200/80 text-orange-600 flex items-center justify-center text-sm flex-shrink-0 shadow-2xs">
                    <i class="fa-solid fa-tower-observation"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-sm sm:text-[15px] font-extrabold text-slate-900 tracking-tight leading-tight">
                            <?= __('tower_title', 'Enterprise Executive Control Tower &bull; CIDP 35 Hectares') ?>
                        </h1>
                        <span class="text-[9px] font-extrabold tracking-wider uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full flex items-center gap-1.5 shadow-2xs">
                            <span id="livePulseIndicator" class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                            <?= __('badge_live_35ha', 'LIVE 35 HA') ?>
                        </span>
                        <span class="text-[9px] font-bold tracking-wider uppercase bg-blue-50 text-[#0170b9] border border-blue-200 px-2 py-0.5 rounded-full flex items-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-satellite-dish text-[8.5px]"></i>
                            <?= __('badge_op_sync', 'DPMS 3-FLOW SYNC') ?>
                        </span>
                        <span class="text-[9px] font-bold tracking-wider uppercase bg-orange-50 text-orange-700 border border-orange-200 px-2 py-0.5 rounded-full flex items-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-network-wired text-[8.5px]"></i>
                            <span id="badgeScadaUptime">SCADA <?= $kpi['scada_uptime'] ?>%</span>
                        </span>
                    </div>
                    <div class="text-[9.5px] font-semibold text-slate-500 mt-0.5 flex items-center gap-1.5 flex-wrap">
                        <span class="text-orange-600 font-bold">Conclusion Intermodal DPMS</span> &bull; 
                        <span><?= ($current_lang === 'en') ? 'Physical, Information & Financial Flow Orchestration &bull; Pasoso Rail Siding &bull; CEISA 4.0 &bull; Odoo 17 ERP' : 'Orkestrasi 3 Aliran (Fisik, Informasi, Finansial) &bull; Rel Siding Pasoso &bull; CEISA 4.0 &bull; Odoo 17 ERP' ?></span>
                    </div>
                </div>
            </div>

            <!-- Cockpit Telemetry Strip, Live Auto-Sync Toggle & Executive Briefing Actions -->
            <div class="flex items-center gap-2 flex-wrap text-[10.5px] text-slate-600 w-full xl:w-auto justify-between xl:justify-end relative z-10">
                
                <!-- Clock Pill -->
                <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1 text-slate-700">
                    <i class="fa-regular fa-calendar text-slate-400 text-[10px]"></i>
                    <span class="font-semibold text-slate-700"><?= $date_display ?></span>
                    <span class="text-slate-300">&bull;</span>
                    <i class="fa-solid fa-clock text-orange-500 text-[9.5px]"></i>
                    <span id="berandaLiveClock" class="font-extrabold text-slate-900 font-mono">--:--:-- WIB</span>
                </div>

                <!-- OPSI A: Live Auto-Sync Polling Control Pill -->
                <div class="flex items-center gap-1.5 bg-emerald-50/70 border border-emerald-200 rounded-lg px-2 py-1">
                    <button type="button" id="btnToggleAutoSync" onclick="toggleBerandaAutoSync()" class="flex items-center gap-1.5 text-[10px] font-extrabold text-emerald-800 cursor-pointer" title="Aktifkan/Matikan Sinkronisasi Real-Time Otomatis (15 Detik)">
                        <span id="autoSyncDot" class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Auto-Sync:</span>
                        <span id="autoSyncCountdown" class="font-mono bg-white px-1.5 py-0.2 rounded border border-emerald-200 text-emerald-700">15s</span>
                    </button>
                    <span class="text-emerald-300">|</span>
                    <button type="button" onclick="fetchLiveBerandaTelemetry(true)" class="text-emerald-700 hover:text-[#0170b9] transition cursor-pointer px-1" title="Sinkronisasi Telemetri Sekarang">
                        <i id="syncSpinnerIcon" class="fa-solid fa-arrows-rotate text-[10px]"></i>
                    </button>
                </div>

                <!-- OPSI D: Dedicated Executive Briefing & Export Actions (Standalone Page Design) -->
                <div class="flex items-center gap-1.5">
                    <a href="report_executive.php" target="_blank" class="px-2.5 py-1 bg-[#002f5e] hover:bg-[#0170b9] text-white rounded-lg text-[10.5px] font-extrabold transition flex items-center gap-1.5 shadow-2xs" title="Buka Halaman Dedikasi Laporan Eksekutif Resmi A4 (Bukan Screenshot)">
                        <i class="fa-solid fa-file-shield text-orange-400 text-[10px]"></i>
                        <span><?= ($current_lang === 'en') ? 'Executive Briefing (A4)' : 'Executive Briefing (A4)' ?></span>
                    </a>
                    <a href="report_executive.php?autoprint=1" target="_blank" class="px-2.5 py-1 bg-orange-600 hover:bg-orange-500 text-white rounded-lg text-[10.5px] font-extrabold transition flex items-center gap-1 shadow-2xs" title="Cetak / Unduh PDF Format Resmi Direksi dengan Kop & Stempel Basah">
                        <i class="fa-solid fa-print text-[9.5px]"></i>
                        <span><?= ($current_lang === 'en') ? 'Print Official PDF' : 'Cetak PDF Resmi' ?></span>
                    </a>
                    <button type="button" onclick="exportBerandaToExcel()" class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 rounded-lg text-[10.5px] font-extrabold transition flex items-center gap-1 shadow-2xs cursor-pointer" title="Unduh Data Konsinyasi & Finansial ke Excel (.XLSX)">
                        <i class="fa-solid fa-file-excel text-emerald-600 text-[10px]"></i>
                        <span>XLSX</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ======================================================================= -->
        <!-- 2. TOP METRIC ROW (6 UNIFIED KPI CARDS - CLICKABLE DYNAMIC INSTRUMENT) -->
        <!-- ======================================================================= -->
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
            
            <!-- KPI 1: Yard Stacking -->
            <div onclick="setBerandaCategoryFilter('dry_yard')" class="cursor-pointer bg-white rounded-xl p-3 shadow-2xs border border-slate-200/90 hover:border-blue-400 hover:shadow-sm transition flex flex-col justify-between group" title="Klik untuk memfilter kontainer di Stacking Yard Blok A-C">
                <div class="flex items-center justify-between">
                    <span class="text-[9.5px] font-extrabold uppercase tracking-wider text-slate-500 group-hover:text-[#0170b9] transition"><?= __('kpi_yard_stacking', 'Yard Stacking') ?></span>
                    <span class="w-6 h-6 rounded-md bg-blue-50 text-[#0170b9] border border-blue-100 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </span>
                </div>
                
                <div class="my-1.5 flex items-baseline justify-between">
                    <div>
                        <h3 id="kpiYardVal" class="text-lg sm:text-xl font-black text-slate-900 tracking-tight leading-none"><?= number_format($containers_yard) ?></h3>
                        <span class="text-[9px] text-slate-400 font-semibold">/ <?= number_format($yard_cap_max) ?> TEU</span>
                    </div>
                    <div class="relative w-10 h-10 flex-shrink-0 flex items-center justify-center">
                        <svg class="w-10 h-10 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-slate-100" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path id="kpiYardRing" class="text-[#0170b9] transition-all duration-500" stroke-dasharray="<?= min(100, (int)$yard_occupancy) ?>, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <span id="kpiYardPct" class="absolute text-[8.5px] font-black text-slate-800 font-mono"><?= $yard_occupancy ?>%</span>
                    </div>
                </div>

                <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9px]">
                    <div class="flex items-center gap-1 text-emerald-600 font-bold">
                        <i class="fa-solid fa-arrow-trend-up text-[8px]"></i>
                        <span><?= __('kpi_yard_trend', '+4.2% (24h)') ?></span>
                    </div>
                    <span class="text-[8.5px] font-bold text-[#0170b9] opacity-0 group-hover:opacity-100 transition">Filter &rarr;</span>
                </div>
            </div>

            <!-- KPI 2: Gate Turnaround Time -->
            <div onclick="setBerandaStageFilter('stage_1')" class="cursor-pointer bg-white rounded-xl p-3 shadow-2xs border border-slate-200/90 hover:border-orange-400 hover:shadow-sm transition flex flex-col justify-between group" title="Klik untuk memfilter antrean & aktivitas Gerbang ANPR / Timbangan VGM">
                <div class="flex items-center justify-between">
                    <span class="text-[9.5px] font-extrabold uppercase tracking-wider text-slate-500 group-hover:text-[#ea580c] transition"><?= __('kpi_gate_turnaround', 'Gate Turnaround') ?></span>
                    <span class="w-6 h-6 rounded-md bg-orange-50 text-[#ea580c] border border-orange-100 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                        <i class="fa-solid fa-truck-fast"></i>
                    </span>
                </div>

                <div class="my-1.5 flex items-baseline justify-between">
                    <div>
                        <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight leading-none"><span id="kpiGateVal"><?= $gate_turnaround ?></span> <span class="text-[10px] font-semibold text-slate-400"><?= ($current_lang === 'en') ? 'min' : 'mnt' ?></span></h3>
                        <span class="text-[9px] text-emerald-600 font-bold"><?= __('kpi_gate_target', '&le; 20m Target INSA') ?></span>
                    </div>
                    <div class="relative w-11 h-9 flex-shrink-0 flex flex-col items-center justify-end">
                        <svg class="w-11 h-7" viewBox="0 0 36 20">
                            <path d="M 4 18 A 14 14 0 0 1 32 18" fill="none" stroke="#f1f5f9" stroke-width="4" stroke-linecap="round"/>
                            <path d="M 4 18 A 14 14 0 0 1 32 18" fill="none" stroke="#ea580c" stroke-width="4" stroke-dasharray="32, 44" stroke-linecap="round"/>
                        </svg>
                        <span id="kpiGateGaugeTxt" class="absolute bottom-0 text-[8px] font-black text-orange-600 font-mono"><?= $gate_turnaround ?>m</span>
                    </div>
                </div>

                <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9px] text-slate-500">
                    <span class="font-semibold text-slate-700"><span id="kpiTrucksCount"><?= $total_trucks ?></span> <?= __('kpi_gate_trucks', 'Truk Aktif') ?></span>
                    <span class="text-emerald-600 font-bold"><?= __('kpi_gate_vbs', 'VBS 98% On-Time') ?></span>
                </div>
            </div>

            <!-- KPI 3: Siding KA Intermodal -->
            <div onclick="setBerandaStageFilter('stage_6')" class="cursor-pointer bg-white rounded-xl p-3 shadow-2xs border border-slate-200/90 hover:border-sky-400 hover:shadow-sm transition flex flex-col justify-between group" title="Klik untuk memfilter kontainer di Jalur Rel Siding KA 2518">
                <div class="flex items-center justify-between">
                    <span class="text-[9.5px] font-extrabold uppercase tracking-wider text-slate-500 group-hover:text-[#0170b9] transition"><?= __('kpi_rail_siding', 'Siding KA Intermodal') ?></span>
                    <span class="w-6 h-6 rounded-md bg-blue-50 text-[#0170b9] border border-blue-100 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                        <i class="fa-solid fa-train"></i>
                    </span>
                </div>

                <div class="my-1.5 flex items-baseline justify-between">
                    <div>
                        <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight leading-none"><span id="kpiRailVal"><?= $loaded_w ?>/<?= $total_w ?></span> <span class="text-[10px] font-semibold text-slate-400">TEU</span></h3>
                        <span class="text-[9px] text-[#0170b9] font-bold"><?= __('kpi_rail_sub', 'KA 2518 ke Priok') ?></span>
                    </div>
                    <div class="relative w-10 h-10 flex-shrink-0 flex items-center justify-center">
                        <svg class="w-10 h-10 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-slate-100" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path id="kpiRailRing" class="text-[#0170b9] transition-all duration-500" stroke-dasharray="<?= $siding_pct ?>, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <span id="kpiRailPct" class="absolute text-[8.5px] font-black text-slate-800 font-mono"><?= $siding_pct ?>%</span>
                    </div>
                </div>

                <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9px] text-slate-500">
                    <span class="font-medium text-slate-600"><?= __('kpi_rail_track', 'Track S-01 (600m)') ?></span>
                    <span class="font-bold text-orange-600"><?= __('kpi_rail_eta', 'ETA 16:45 WIB') ?></span>
                </div>
            </div>

            <!-- KPI 4: Reefer Cold Chain -->
            <div onclick="setBerandaStageFilter('stage_5')" class="cursor-pointer bg-white rounded-xl p-3 shadow-2xs border border-slate-200/90 hover:border-cyan-400 hover:shadow-sm transition flex flex-col justify-between group" title="Klik untuk memfilter kontainer berpendingin Reefer 380V">
                <div class="flex items-center justify-between">
                    <span class="text-[9.5px] font-extrabold uppercase tracking-wider text-slate-500 group-hover:text-cyan-600 transition"><?= __('kpi_reefer_chain', 'Reefer Cold Chain') ?></span>
                    <span class="w-6 h-6 rounded-md bg-cyan-50 text-[#06b6d4] border border-cyan-100 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                        <i class="fa-solid fa-snowflake"></i>
                    </span>
                </div>

                <div class="my-1.5 flex items-baseline justify-between">
                    <div>
                        <h3 id="kpiReeferTemp" class="text-lg sm:text-xl font-black text-cyan-600 font-mono tracking-tight leading-none"><?= $reefer_temp ?>&deg;C</h3>
                        <span class="text-[9px] text-cyan-700 font-bold"><span id="kpiReeferPlugs"><?= $reefer_plugs ?></span> / 300 <?= __('kpi_reefer_plugs', 'Plugs') ?></span>
                    </div>
                    <div class="relative w-10 h-10 flex-shrink-0 flex items-center justify-center">
                        <svg class="w-10 h-10 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-slate-100" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path id="kpiReeferRing" class="text-cyan-500 transition-all duration-500" stroke-dasharray="<?= min(100, (int)$reefer_pct) ?>, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <span id="kpiReeferPct" class="absolute text-[8.5px] font-black text-cyan-800 font-mono"><?= $reefer_pct ?>%</span>
                    </div>
                </div>

                <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9px]">
                    <span class="font-bold text-slate-600"><?= __('kpi_reefer_lora', 'LoRa 868MHz') ?></span>
                    <span class="text-emerald-600 font-bold"><?= __('kpi_reefer_haccp', 'HACCP 98.8% OK') ?></span>
                </div>
            </div>

            <!-- KPI 5: Bea Cukai CEISA 4.0 -->
            <div onclick="setBerandaCategoryFilter('customs_hold')" class="cursor-pointer bg-white rounded-xl p-3 shadow-2xs border border-slate-200/90 hover:border-emerald-400 hover:shadow-sm transition flex flex-col justify-between group" title="Klik untuk memfilter status kepabeanan CEISA 4.0">
                <div class="flex items-center justify-between">
                    <span class="text-[9.5px] font-extrabold uppercase tracking-wider text-slate-500 group-hover:text-emerald-700 transition"><?= __('kpi_customs_ceisa', 'Bea Cukai CEISA 4.0') ?></span>
                    <span class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                        <i class="fa-solid fa-shield-halved"></i>
                    </span>
                </div>

                <div class="my-1.5 flex items-baseline justify-between">
                    <div>
                        <h3 id="kpiCustomsVal" class="text-lg sm:text-xl font-black text-emerald-700 tracking-tight leading-none"><?= number_format($customs_percent, 1) ?>%</h3>
                        <span class="text-[9px] text-emerald-600 font-bold"><span id="kpiCustomsBox"><?= $customs_cleared ?>/<?= $customs_total ?></span> <?= __('kpi_customs_cleared', 'Box Cleared') ?></span>
                    </div>
                    <div class="relative w-10 h-10 flex-shrink-0 flex items-center justify-center">
                        <svg class="w-10 h-10 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-slate-100" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="text-emerald-500 transition-all duration-500" stroke-dasharray="<?= min(100, (int)$customs_percent) ?>, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <span class="absolute text-[8.5px] font-black text-emerald-800 font-mono"><?= round($customs_percent) ?>%</span>
                    </div>
                </div>

                <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9px] text-slate-500">
                    <span class="font-semibold text-slate-700"><?= __('kpi_customs_release', 'SPPB Auto Release') ?></span>
                    <span class="text-emerald-600 font-bold"><?= __('kpi_customs_xray', 'Hi-Co X-Ray OK') ?></span>
                </div>
            </div>

            <!-- KPI 6: CFS & Terminal Billing -->
            <div onclick="setBerandaStageFilter('stage_4')" class="cursor-pointer bg-white rounded-xl p-3 shadow-2xs border border-slate-200/90 hover:border-amber-400 hover:shadow-sm transition flex flex-col justify-between group" title="Klik untuk memfilter pekerjaan CFS Stripping/Stuffing & Billing">
                <div class="flex items-center justify-between">
                    <span class="text-[9.5px] font-extrabold uppercase tracking-wider text-slate-500 group-hover:text-amber-600 transition"><?= __('kpi_cfs_billing', 'CFS &amp; Billing') ?></span>
                    <span class="w-6 h-6 rounded-md bg-amber-50 text-[#f59e0b] border border-amber-100 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </span>
                </div>

                <div class="my-1.5 flex items-baseline justify-between">
                    <div>
                        <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight leading-none"><span id="kpiCfsJobs"><?= $cfs_jobs ?></span> <span class="text-[10px] font-normal text-slate-400"><?= __('kpi_cfs_jobs', 'Jobs') ?></span></h3>
                        <span class="text-[9px] text-amber-600 font-bold"><?= __('kpi_cfs_ramps', 'Rampa D1-D5 Aktif') ?></span>
                    </div>
                    <div class="relative w-10 h-10 flex-shrink-0 flex items-center justify-center">
                        <svg class="w-10 h-10 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-slate-100" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="text-amber-500 transition-all duration-500" stroke-dasharray="<?= $cfs_pct ?>, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <span class="absolute text-[8.5px] font-black text-amber-700 font-mono"><?= $cfs_pct ?>%</span>
                    </div>
                </div>

                <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9px]">
                    <span class="font-medium text-slate-500"><?= __('kpi_cfs_omzet', 'Omzet Lunas') ?></span>
                    <span id="kpiBillingPaid" class="text-emerald-700 font-extrabold"><?= ($current_lang === 'en') ? 'Rp ' . number_format($paid_rev / 1000000, 1) . 'M Paid' : 'Rp ' . number_format($paid_rev / 1000000, 1) . 'Jt Paid' ?></span>
                </div>
            </div>

        </div>

        <!-- ======================================================================= -->
        <!-- 3. MULTIMODAL LOGISTICS PIPELINE CONVEYOR RIBBON (INTERACTIVE FILTER)   -->
        <!-- ======================================================================= -->
        <div class="bg-white/95 backdrop-blur-xs rounded-xl p-3 sm:p-4 shadow-2xs border border-slate-200/90 relative overflow-hidden">
            <div class="absolute -right-8 -bottom-8 w-60 h-32 pointer-events-none opacity-[0.09] z-0" aria-hidden="true">
                <svg width="100%" height="100%" viewBox="0 0 200 100" fill="none" stroke="#004b87" stroke-width="1.6">
                    <path d="M 0 25 Q 50 10, 100 25 T 200 25" />
                    <path d="M 0 55 Q 50 40, 100 55 T 200 55" />
                    <path d="M 0 85 Q 50 70, 100 85 T 200 85" stroke="#ea580c" />
                    <circle cx="25" cy="25" r="3" fill="#ea580c" />
                    <circle cx="125" cy="25" r="3" fill="#ea580c" />
                </svg>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 mb-2.5 pb-2 border-b border-slate-100 relative z-10">
                <div>
                    <h3 class="font-bold text-slate-900 text-xs sm:text-sm flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#0170b9] animate-pulse"></span>
                        <i class="fa-solid fa-arrows-split-up-and-left text-[#0170b9] text-sm"></i>
                        <span>Multimodal Logistics Pipeline Conveyor Ribbon (Interactive 6-Stage Cross-Filter)</span>
                    </h3>
                    <p class="text-[10.5px] text-slate-400 mt-0.5">Klik tahapan mana saja untuk memfilter log telemetri SCADA dan daftar konsinyasi peti kemas di bawah secara langsung.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="btnResetPipelineFilter" onclick="resetBerandaFilters()" class="hidden text-[9.5px] font-extrabold text-orange-700 bg-orange-50 hover:bg-orange-100 px-2.5 py-0.5 rounded-full border border-orange-300 transition cursor-pointer">
                        <i class="fa-solid fa-filter-circle-xmark mr-1"></i>Reset Filter Tahapan
                    </button>
                    <span class="text-[10px] font-extrabold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200 flex items-center gap-1 shadow-2xs">
                        <i class="fa-solid fa-circle-check text-[9px]"></i> Pipeline 100% Active
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-2 xl:gap-2.5 relative z-10">
                <!-- Stage 1: Gate-In ANPR OCR -->
                <div id="ribbon_stage_1" onclick="setBerandaStageFilter('stage_1')" class="pipeline-stage-card cursor-pointer group p-2.5 rounded-lg border border-slate-200 bg-slate-50/60 hover:bg-orange-50/40 hover:border-orange-300 transition flex flex-col justify-between relative overflow-hidden shadow-2xs">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-5 h-5 rounded-md bg-[#0170b9] text-white flex items-center justify-center text-[10px] font-black font-mono">1</span>
                        <div class="flex items-center gap-1">
                            <span class="text-[8px] font-extrabold text-[#0170b9] bg-blue-50 px-1 py-0.2 rounded border border-blue-200 uppercase">Stage 1</span>
                            <a href="dashboard.php?page=gate" onclick="event.stopPropagation()" class="w-4 h-4 rounded bg-white border border-slate-200 text-slate-400 hover:text-[#0170b9] flex items-center justify-center text-[8px]" title="Buka Modul Gate Automation">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        </div>
                    </div>
                    <div class="space-y-0.5">
                        <div class="text-[11px] font-bold text-slate-800 group-hover:text-orange-600 transition flex items-center gap-1 truncate">
                            <i class="fa-solid fa-camera text-[#0170b9] text-[10px]"></i>
                            <span>Gate-In ANPR OCR</span>
                        </div>
                        <p class="text-[9.5px] text-slate-500">Scan plat &amp; RFID box otomatis</p>
                    </div>
                    <div class="mt-2 pt-1 border-t border-slate-200/60 flex items-center justify-between text-[9px] text-slate-600 font-semibold">
                        <span>Akurasi: 99.4%</span>
                        <span class="text-[8px] font-bold text-[#0170b9] group-hover:text-orange-600">Klik Filter</span>
                    </div>
                </div>

                <!-- Stage 2: Weighbridge SOLAS VGM 80T -->
                <div id="ribbon_stage_2" onclick="setBerandaStageFilter('stage_2')" class="pipeline-stage-card cursor-pointer group p-2.5 rounded-lg border border-slate-200 bg-slate-50/60 hover:bg-orange-50/40 hover:border-orange-300 transition flex flex-col justify-between relative overflow-hidden shadow-2xs">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-5 h-5 rounded-md bg-orange-600 text-white flex items-center justify-center text-[10px] font-black font-mono">2</span>
                        <div class="flex items-center gap-1">
                            <span class="text-[8px] font-extrabold text-orange-700 bg-orange-50 px-1 py-0.2 rounded border border-orange-200 uppercase">Stage 2</span>
                            <a href="dashboard.php?page=gate" onclick="event.stopPropagation()" class="w-4 h-4 rounded bg-white border border-slate-200 text-slate-400 hover:text-orange-600 flex items-center justify-center text-[8px]" title="Buka Modul Timbangan VGM">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        </div>
                    </div>
                    <div class="space-y-0.5">
                        <div class="text-[11px] font-bold text-slate-800 group-hover:text-orange-600 transition flex items-center gap-1 truncate">
                            <i class="fa-solid fa-weight-scale text-orange-600 text-[10px]"></i>
                            <span>Weighbridge 80T VGM</span>
                        </div>
                        <p class="text-[9.5px] text-slate-500">Verifikasi SOLAS IMO Rule</p>
                    </div>
                    <div class="mt-2 pt-1 border-t border-slate-200/60 flex items-center justify-between text-[9px] text-slate-600 font-semibold">
                        <span>Toleransi &plusmn;0.2%</span>
                        <span class="text-[8px] font-bold text-[#0170b9] group-hover:text-orange-600">Klik Filter</span>
                    </div>
                </div>

                <!-- Stage 3: Yard Stacking Blocks A/B/C -->
                <div id="ribbon_stage_3" onclick="setBerandaStageFilter('stage_3')" class="pipeline-stage-card cursor-pointer group p-2.5 rounded-lg border border-slate-200 bg-slate-50/60 hover:bg-orange-50/40 hover:border-orange-300 transition flex flex-col justify-between relative overflow-hidden shadow-2xs">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-5 h-5 rounded-md bg-[#0170b9] text-white flex items-center justify-center text-[10px] font-black font-mono">3</span>
                        <div class="flex items-center gap-1">
                            <span class="text-[8px] font-extrabold text-blue-700 bg-blue-50 px-1 py-0.2 rounded border border-blue-200 uppercase">Stage 3</span>
                            <a href="dashboard.php?page=yard" onclick="event.stopPropagation()" class="w-4 h-4 rounded bg-white border border-slate-200 text-slate-400 hover:text-[#0170b9] flex items-center justify-center text-[8px]" title="Buka Modul Yard Stacking">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        </div>
                    </div>
                    <div class="space-y-0.5">
                        <div class="text-[11px] font-bold text-slate-800 group-hover:text-orange-600 transition flex items-center gap-1 truncate">
                            <i class="fa-solid fa-cubes-stacked text-[#0170b9] text-[10px]"></i>
                            <span>Yard Stacking A/B/C</span>
                        </div>
                        <p class="text-[9.5px] text-slate-500">Reach Stacker Kalmar 45T</p>
                    </div>
                    <div class="mt-2 pt-1 border-t border-slate-200/60 flex items-center justify-between text-[9px] text-slate-600 font-semibold">
                        <span><?= number_format($containers_yard) ?> Box Stack</span>
                        <span class="text-[8px] font-bold text-[#0170b9] group-hover:text-orange-600">Klik Filter</span>
                    </div>
                </div>

                <!-- Stage 4: CFS Rampa D1-D5 -->
                <div id="ribbon_stage_4" onclick="setBerandaStageFilter('stage_4')" class="pipeline-stage-card cursor-pointer group p-2.5 rounded-lg border border-slate-200 bg-slate-50/60 hover:bg-orange-50/40 hover:border-orange-300 transition flex flex-col justify-between relative overflow-hidden shadow-2xs">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-5 h-5 rounded-md bg-orange-600 text-white flex items-center justify-center text-[10px] font-black font-mono">4</span>
                        <div class="flex items-center gap-1">
                            <span class="text-[8px] font-extrabold text-orange-700 bg-orange-50 px-1 py-0.2 rounded border border-orange-200 uppercase">Stage 4</span>
                            <a href="dashboard.php?page=cfs" onclick="event.stopPropagation()" class="w-4 h-4 rounded bg-white border border-slate-200 text-slate-400 hover:text-orange-600 flex items-center justify-center text-[8px]" title="Buka Modul Gudang CFS">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        </div>
                    </div>
                    <div class="space-y-0.5">
                        <div class="text-[11px] font-bold text-slate-800 group-hover:text-orange-600 transition flex items-center gap-1 truncate">
                            <i class="fa-solid fa-warehouse text-orange-600 text-[10px]"></i>
                            <span>CFS Rampa D1-D5</span>
                        </div>
                        <p class="text-[9.5px] text-slate-500">Stuffing &amp; Stripping LCL</p>
                    </div>
                    <div class="mt-2 pt-1 border-t border-slate-200/60 flex items-center justify-between text-[9px] text-slate-600 font-semibold">
                        <span><?= $cfs_jobs ?> Jobs Aktif</span>
                        <span class="text-[8px] font-bold text-[#0170b9] group-hover:text-orange-600">Klik Filter</span>
                    </div>
                </div>

                <!-- Stage 5: Reefer Cold Chain Hub -->
                <div id="ribbon_stage_5" onclick="setBerandaStageFilter('stage_5')" class="pipeline-stage-card cursor-pointer group p-2.5 rounded-lg border border-slate-200 bg-slate-50/60 hover:bg-cyan-50/40 hover:border-cyan-300 transition flex flex-col justify-between relative overflow-hidden shadow-2xs">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-5 h-5 rounded-md bg-cyan-600 text-white flex items-center justify-center text-[10px] font-black font-mono">5</span>
                        <div class="flex items-center gap-1">
                            <span class="text-[8px] font-extrabold text-cyan-700 bg-cyan-50 px-1 py-0.2 rounded border border-cyan-200 uppercase">Stage 5</span>
                            <a href="dashboard.php?page=reefer" onclick="event.stopPropagation()" class="w-4 h-4 rounded bg-white border border-slate-200 text-slate-400 hover:text-cyan-600 flex items-center justify-center text-[8px]" title="Buka Modul Reefer Cold Chain">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        </div>
                    </div>
                    <div class="space-y-0.5">
                        <div class="text-[11px] font-bold text-slate-800 group-hover:text-cyan-700 transition flex items-center gap-1 truncate">
                            <i class="fa-solid fa-snowflake text-cyan-600 text-[10px]"></i>
                            <span>Reefer Cold Chain</span>
                        </div>
                        <p class="text-[9.5px] text-slate-500">300 Marechal Steker 380V</p>
                    </div>
                    <div class="mt-2 pt-1 border-t border-slate-200/60 flex items-center justify-between text-[9px] text-cyan-800 font-semibold">
                        <span><?= $reefer_temp ?>&deg;C &bull; SHT40</span>
                        <span class="text-[8px] font-bold text-cyan-700">Klik Filter</span>
                    </div>
                </div>

                <!-- Stage 6: Rail Siding KA 2518 ke Tanjung Priok -->
                <div id="ribbon_stage_6" onclick="setBerandaStageFilter('stage_6')" class="pipeline-stage-card cursor-pointer group p-2.5 rounded-lg border border-slate-200 bg-slate-50/60 hover:bg-blue-50/40 hover:border-blue-300 transition flex flex-col justify-between relative overflow-hidden shadow-2xs">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-5 h-5 rounded-md bg-emerald-600 text-white flex items-center justify-center text-[10px] font-black font-mono">6</span>
                        <div class="flex items-center gap-1">
                            <span class="text-[8px] font-extrabold text-emerald-700 bg-emerald-50 px-1 py-0.2 rounded border border-emerald-200 uppercase">Stage 6</span>
                            <a href="dashboard.php?page=intermodal" onclick="event.stopPropagation()" class="w-4 h-4 rounded bg-white border border-slate-200 text-slate-400 hover:text-emerald-600 flex items-center justify-center text-[8px]" title="Buka Modul Siding Kereta Api">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        </div>
                    </div>
                    <div class="space-y-0.5">
                        <div class="text-[11px] font-bold text-slate-800 group-hover:text-[#0170b9] transition flex items-center gap-1 truncate">
                            <i class="fa-solid fa-train-subway text-emerald-600 text-[10px]"></i>
                            <span>Rail Siding KA 2518</span>
                        </div>
                        <p class="text-[9.5px] text-slate-500">54.8 km ke Pel. Tj. Priok</p>
                    </div>
                    <div class="mt-2 pt-1 border-t border-slate-200/60 flex items-center justify-between text-[9px] text-slate-600 font-semibold">
                        <span><?= $loaded_w ?>/<?= $total_w ?> Gerbong</span>
                        <span class="text-[8px] font-bold text-emerald-700">Klik Filter</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================================= -->
        <!-- 4. MID ANALYTICAL VISUAL GRID (CHARTS & DIAGRAMS - 4 COLUMNS)          -->
        <!-- ======================================================================= -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
            
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
                    <p class="text-[10px] text-slate-400 mb-2">Throughput truk aktual vs kuota booking VBS per 2 jam.</p>
                </div>
                
                <div class="h-44 sm:h-48 relative w-full flex-1">
                    <canvas id="gateVbsAreaChart"></canvas>
                </div>

                <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9.5px] text-slate-600">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#0170b9]"></span>Aktual: <strong><?= $total_trucks ?> Truk</strong></span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#ea580c]"></span>VBS: <strong>250 Slot</strong></span>
                    <span class="text-emerald-600 font-bold">Kesesuaian: 96.8%</span>
                </div>
            </div>

            <!-- Col 2: Heavy Equipment SCADA OEE -->
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
                            <i class="fa-solid fa-chart-radar mr-1.5 text-orange-600 text-xs"></i>Alat Berat SCADA OEE
                        </h3>
                        <span class="text-[8.5px] font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200 font-mono">
                            RS-01 vs RTG
                        </span>
                    </div>
                    <p class="text-[10px] text-slate-400 mb-2">Spider radar OEE 5 sumbu Kalmar RS-01 vs RTG Konecranes.</p>
                </div>

                <div class="h-44 sm:h-48 relative w-full flex-1 flex items-center justify-center">
                    <canvas id="scadaEquipmentRadarChart"></canvas>
                </div>

                <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9.5px]">
                    <span class="text-[#0170b9] font-bold flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-[#0170b9]"></span>RS-01: <strong>91.4%</strong>
                    </span>
                    <span class="text-[#ea580c] font-bold flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-[#ea580c]"></span>RTG-01: <strong>88.7%</strong>
                    </span>
                    <span class="text-slate-500 font-semibold">Uptime: 98.2%</span>
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
                    <p class="text-[10px] text-slate-400 mb-2">Durasi inap kontainer dengan ambang batas bebas denda (3 hari).</p>
                </div>

                <div class="h-44 sm:h-48 relative w-full flex-1">
                    <canvas id="dwellingTimeBarChart"></canvas>
                </div>

                <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9.5px] text-slate-600">
                    <span class="text-emerald-600 font-bold">1-2H: 68% Safe</span>
                    <span class="text-amber-600 font-bold">3H: 22% Warn</span>
                    <span class="text-rose-600 font-bold">4+H: 10% Denda</span>
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
                    <p class="text-[10px] text-slate-400 mb-2">Pangsa moda angkutan ramah lingkungan Kereta vs Truk.</p>
                </div>

                <div class="h-44 sm:h-48 relative w-full flex-1 flex items-center justify-center">
                    <canvas id="modalSplitDonutChart"></canvas>
                </div>

                <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9.5px]">
                    <span class="text-[#0170b9] font-bold">68% Kereta Api</span>
                    <span class="text-slate-500 font-semibold">32% Truk Jalan</span>
                    <span class="text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">
                        48 Truk Offset
                    </span>
                </div>
            </div>

        </div>

        <!-- ======================================================================= -->
        <!-- 5. FACILITY SPATIAL RADAR & CUSTOMS INSPECTION HUB (INTERACTIVE ZONES) -->
        <!-- ======================================================================= -->
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-3">
            
            <!-- Left: 4-Zone Spatial Terminal Distribution Grid (Clickable Cross-Filter) (4 Cols) -->
            <div class="xl:col-span-4 bg-white/95 backdrop-blur-xs rounded-xl shadow-2xs border border-slate-200/90 p-3 sm:p-3.5 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -right-8 -bottom-8 w-44 h-44 pointer-events-none opacity-[0.045] z-0 rounded-full"
                     style="background-image: url('assets/img/facilities/cfs_warehouse.jpg'); background-size: cover; filter: grayscale(100%); mask-image: radial-gradient(circle, black 35%, transparent 75%); -webkit-mask-image: radial-gradient(circle, black 35%, transparent 75%);">
                </div>
                <div class="flex items-center justify-between pb-1.5 border-b border-slate-100 mb-2 relative z-10">
                    <h3 class="font-extrabold text-slate-900 text-xs flex items-center">
                        <i class="fa-solid fa-map-location-dot mr-1.5 text-[#0170b9] text-xs"></i>Radar 4 Zona Spasial (Klik Filter)
                    </h3>
                    <a href="dashboard.php?page=denah" class="text-[9.5px] font-bold text-[#0170b9] hover:underline flex items-center gap-1">
                        <span>GIS Denah &rarr;</span>
                    </a>
                </div>

                <div class="space-y-2 py-0.5 flex-1 flex flex-col justify-between text-[10.5px] relative z-10">
                    <!-- Z1: Gate & Buffer Truck -->
                    <div id="zoneCard_Z1" onclick="setBerandaZoneFilter('Z1')" class="spatial-zone-card cursor-pointer p-2 rounded-lg border border-slate-100 bg-slate-50/70 hover:border-orange-300 hover:bg-orange-50/30 transition">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                <span class="w-4 h-4 rounded bg-orange-100 text-orange-700 flex items-center justify-center text-[9px] font-mono font-bold">Z1</span>
                                Gerbang &amp; Buffer Truk (4.2 Ha)
                            </span>
                            <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-1 py-0.2 rounded border border-emerald-200">Lancar</span>
                        </div>
                        <div class="w-full bg-slate-200/80 rounded-full h-1.5 overflow-hidden mb-1">
                            <div class="bg-orange-500 h-1.5 rounded-full" style="width: 35%"></div>
                        </div>
                        <div class="flex justify-between text-[9px] text-slate-500">
                            <span>Antrean: <strong><?= $total_trucks ?> Truk Aktif</strong></span>
                            <span class="text-orange-600 font-bold">Klik Filter Z1 &rarr;</span>
                        </div>
                    </div>

                    <!-- Z2: Stacking Yard 12,500 TEU -->
                    <div id="zoneCard_Z2" onclick="setBerandaZoneFilter('Z2')" class="spatial-zone-card cursor-pointer p-2 rounded-lg border border-slate-100 bg-slate-50/70 hover:border-blue-300 hover:bg-blue-50/30 transition">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                <span class="w-4 h-4 rounded bg-blue-100 text-[#0170b9] flex items-center justify-center text-[9px] font-mono font-bold">Z2</span>
                                Stacking Yard Blok A-C (16.5 Ha)
                            </span>
                            <span class="text-[9px] font-bold text-blue-700 bg-blue-50 px-1 py-0.2 rounded border border-blue-200"><?= $yard_occupancy ?>%</span>
                        </div>
                        <div class="w-full bg-slate-200/80 rounded-full h-1.5 overflow-hidden mb-1">
                            <div class="bg-[#0170b9] h-1.5 rounded-full" style="width: <?= min(100, (int)$yard_occupancy) ?>%"></div>
                        </div>
                        <div class="flex justify-between text-[9px] text-slate-500">
                            <span><?= number_format($containers_yard) ?> / 12,500 TEU</span>
                            <span class="text-[#0170b9] font-bold">Klik Filter Z2 &rarr;</span>
                        </div>
                    </div>

                    <!-- Z3: CFS Warehouse Rampa D1-D5 -->
                    <div id="zoneCard_Z3" onclick="setBerandaZoneFilter('Z3')" class="spatial-zone-card cursor-pointer p-2 rounded-lg border border-slate-100 bg-slate-50/70 hover:border-amber-300 hover:bg-amber-50/30 transition">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                <span class="w-4 h-4 rounded bg-amber-100 text-amber-700 flex items-center justify-center text-[9px] font-mono font-bold">Z3</span>
                                CFS Gudang Rampa D1-D5 (6.8 Ha)
                            </span>
                            <span class="text-[9px] font-bold text-amber-800 bg-amber-50 px-1 py-0.2 rounded border border-amber-200">LCL Hub</span>
                        </div>
                        <div class="w-full bg-slate-200/80 rounded-full h-1.5 overflow-hidden mb-1">
                            <div class="bg-amber-500 h-1.5 rounded-full" style="width: 78%"></div>
                        </div>
                        <div class="flex justify-between text-[9px] text-slate-500">
                            <span>Luas: <strong>4.000 m²</strong> (<?= $cfs_jobs ?> Jobs)</span>
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
                            <span class="text-[9px] font-bold text-cyan-800 bg-cyan-50 px-1 py-0.2 rounded border border-cyan-200">Cold Chain</span>
                        </div>
                        <div class="w-full bg-slate-200/80 rounded-full h-1.5 overflow-hidden mb-1">
                            <div class="bg-cyan-500 h-1.5 rounded-full" style="width: 80%"></div>
                        </div>
                        <div class="flex justify-between text-[9px] text-slate-500">
                            <span>Rel 2x600m &bull; 300 Plugs</span>
                            <span class="text-cyan-700 font-bold">Klik Filter Z4 &rarr;</span>
                        </div>
                    </div>
                </div>

                <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9.5px] text-slate-500 mt-2 relative z-10">
                    <span>Luas Master Plan: <strong>35 Hektar</strong></span>
                    <span class="text-emerald-700 font-bold">26 BOM HW OK</span>
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
                                    <h4 class="font-bold text-slate-900 text-[11px]">Dermaga Pemeriksaan Behandle</h4>
                                    <p class="text-[9px] text-slate-400">Dermaga Fisik &amp; Karantina KPU Bea Cukai</p>
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
                    <span><strong class="text-[#ea580c]">Storage:</strong> 30%</span>
                    <span><strong class="text-[#06b6d4]">Reefer:</strong> 15%</span>
                    <span><strong class="text-[#6366f1]">VGM:</strong> 10%</span>
                </div>
            </div>

        </div>

        <!-- ======================================================================= -->
        <!-- 6. OPSI C: PILAR KE-3 ALIRAN FINANSIAL, DEMURRAGE & ODOO 17 ERP LEDGER -->
        <!-- ======================================================================= -->
        <div class="bg-white/95 backdrop-blur-xs rounded-xl p-3.5 sm:p-4 shadow-2xs border border-slate-200/90 relative overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 mb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center text-xs flex-shrink-0">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-extrabold text-slate-900 text-xs sm:text-sm">
                                Pilar Aliran Finansial Terpadu, Eksposur Demurrage &amp; Integrasi Billing Odoo 17 ERP
                            </h3>
                            <span class="text-[8.5px] font-extrabold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full font-mono">
                                <i class="fa-solid fa-plug-circle-check mr-0.5"></i>XML-RPC CONNECTED (<span id="odooLatencyTxt"><?= $latencies['odoo_erp'] ?></span>ms)
                            </span>
                        </div>
                        <p class="text-[10px] text-slate-500">Pemantauan otomatis akrual sewa penumpukan (Storage), denda keterlambatan inap (&gt;3 hari), dan izin Gate-Out komersial.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="report_executive.php?scope=financial" target="_blank" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-[10px] font-bold flex items-center gap-1 transition">
                        <i class="fa-solid fa-print text-[#0170b9]"></i>
                        <span>Cetak Audit Finansial</span>
                    </a>
                    <a href="dashboard.php?page=billing" class="px-2.5 py-1 rounded-lg bg-[#0170b9] hover:bg-[#004b87] text-white text-[10px] font-bold flex items-center gap-1 transition">
                        <span>Buka Modul Billing ERP</span>
                        <i class="fa-solid fa-arrow-right text-[9px]"></i>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-3.5 items-stretch">
                <!-- Left (5 Cols): 4 Financial Telemetry Cards & Demurrage Exposure Alert -->
                <div class="xl:col-span-5 flex flex-col justify-between gap-2.5">
                    <div class="grid grid-cols-2 gap-2.5">
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
                    <div class="p-2.5 rounded-xl bg-gradient-to-r from-[#002f5e] to-[#0170b9] text-white flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-white/15 flex items-center justify-center text-orange-300 text-xs flex-shrink-0">
                                <i class="fa-solid fa-lock-open"></i>
                            </div>
                            <div>
                                <div class="text-[10.5px] font-extrabold">Interlock Komersial Gate-Out &amp; Palang ANPR</div>
                                <div class="text-[9px] text-blue-100">Palang gerbang keluar hanya terbuka otomatis jika Faktur Odoo berstatus <strong>PAID</strong> &amp; SPPB <strong>CLEARED</strong>.</div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-emerald-400/20 border border-emerald-300/40 text-emerald-200 font-mono font-bold text-[8.5px] flex-shrink-0">
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
                        <div class="flex items-center gap-1 text-[9.5px]">
                            <button type="button" onclick="filterBerandaInvoices('ALL')" id="invTab_ALL" class="px-2 py-0.5 rounded font-bold bg-[#0170b9] text-white cursor-pointer transition">Semua</button>
                            <button type="button" onclick="filterBerandaInvoices('PAID')" id="invTab_PAID" class="px-2 py-0.5 rounded font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 cursor-pointer transition">Lunas</button>
                            <button type="button" onclick="filterBerandaInvoices('PENDING')" id="invTab_PENDING" class="px-2 py-0.5 rounded font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 cursor-pointer transition">Menunggu</button>
                            <button type="button" onclick="filterBerandaInvoices('OVERDUE')" id="invTab_OVERDUE" class="px-2 py-0.5 rounded font-semibold bg-white border border-slate-200 text-rose-600 hover:bg-rose-50 cursor-pointer transition">Overdue</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[520px]">
                            <thead>
                                <tr class="bg-white text-slate-500 text-[9.5px] border-y border-slate-200 uppercase">
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
        <!-- 7. BOTTOM EXECUTION CENTER & INTERACTIVE CONSIGNMENT TRACKING           -->
        <!-- ======================================================================= -->
        
        <!-- Banner Status Filter Lintas-Elemen (Tampil saat filter Stage / Zona aktif) -->
        <div id="activeCrossFilterBanner" class="hidden bg-gradient-to-r from-[#002f5e] via-[#0170b9] to-[#004b87] text-white rounded-xl px-4 py-2.5 shadow-sm flex flex-wrap items-center justify-between gap-2 animate-fadeIn">
            <div class="flex items-center gap-2.5 text-xs">
                <span class="w-6 h-6 rounded-lg bg-white/15 flex items-center justify-center text-orange-300">
                    <i class="fa-solid fa-filter"></i>
                </span>
                <div>
                    <span class="text-[10px] uppercase tracking-wider text-blue-200 font-bold block">Mode Filter Lintas-Elemen Aktif:</span>
                    <strong id="activeCrossFilterTitle" class="text-xs sm:text-sm font-extrabold text-white">Menampilkan Semua Tahapan &amp; Zona</strong>
                </div>
            </div>
            <button type="button" onclick="resetBerandaFilters()" class="px-3 py-1 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-[10.5px] font-extrabold flex items-center gap-1.5 shadow-xs transition cursor-pointer">
                <i class="fa-solid fa-rotate-left"></i>
                <span>Tampilkan Semua Data (Reset)</span>
            </button>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-3">
            
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
                    <a href="dashboard.php?page=kontainer" class="font-bold text-[#0170b9] hover:underline inline-flex items-center gap-1 transition">
                        <span>Riwayat Log Penuh</span>
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
                            <i class="fa-solid fa-list-check mr-1.5 text-[#0170b9] text-xs"></i>Pelacakan Konsinyasi Peti Kemas Interaktif (Shipper Consignments)
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

                        <!-- Quick Filter Pills -->
                        <div class="flex items-center gap-1 flex-wrap text-[9.5px]">
                            <button type="button" onclick="setBerandaCategoryFilter('all')" id="catBtn_all" class="px-2 py-1 rounded-md font-bold bg-[#0170b9] text-white cursor-pointer transition">Semua</button>
                            <button type="button" onclick="setBerandaCategoryFilter('dry_yard')" id="catBtn_dry_yard" class="px-2 py-1 rounded-md font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 cursor-pointer transition">Blok A-C</button>
                            <button type="button" onclick="setBerandaCategoryFilter('reefer')" id="catBtn_reefer" class="px-2 py-1 rounded-md font-semibold bg-cyan-50 text-cyan-700 border border-cyan-200 hover:bg-cyan-100 cursor-pointer transition">Reefer</button>
                            <button type="button" onclick="setBerandaCategoryFilter('rail')" id="catBtn_rail" class="px-2 py-1 rounded-md font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 cursor-pointer transition">Siding KA</button>
                            <button type="button" onclick="setBerandaCategoryFilter('demurrage')" id="catBtn_demurrage" class="px-2 py-1 rounded-md font-semibold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 cursor-pointer transition">⚠️ Demurrage</button>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Consignment Table -->
                <div class="overflow-x-auto flex-1 relative z-10">
                    <table class="w-full text-left border-collapse min-w-[620px]">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-[9.5px] border-y border-slate-200 uppercase">
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
                    <h4 class="font-extrabold text-slate-900 text-xs">Matriks Konektivitas Host-to-Host &amp; API Integrasi Real-Time</h4>
                </div>
                <div class="flex items-center gap-2 text-[9.5px]">
                    <span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Semua Gateway Terhubung (5/5)
                    </span>
                    <span class="text-slate-400">TLS 1.3 / OAuth 2.0 Encrypted</span>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-2 xl:gap-2.5 text-[10.5px]">
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
        <div class="bg-gradient-to-r from-[#002f5e] to-[#0170b9] px-5 py-3.5 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center text-orange-300">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <div>
                    <span class="text-[9px] font-extrabold uppercase tracking-wider text-blue-200 block">Inspeksi Terpadu 3-Aliran Logistik</span>
                    <h3 id="mdlCtrNumber" class="text-base font-black font-mono tracking-tight">MSKU9920194</h3>
                </div>
            </div>
            <button type="button" onclick="closeBerandaInspectModal()" class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center text-white cursor-pointer">
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
                    <div class="font-extrabold text-[#002f5e] text-[10.5px] uppercase flex items-center gap-1.5 border-b border-blue-200 pb-1">
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
                <button type="button" onclick="printInspectedContainerInvoice()" class="px-3 py-1.5 rounded-lg bg-[#002f5e] hover:bg-[#0170b9] text-white text-xs font-extrabold flex items-center gap-1.5 cursor-pointer transition">
                    <i class="fa-solid fa-print text-orange-300"></i>
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
    countdown: 15,
    inspectedContainer: null
};

const stageNamesMap = {
    'stage_1': 'Stage 1: Gate-In ANPR OCR & Identifikasi Truk',
    'stage_2': 'Stage 2: Jembatan Timbang SOLAS VGM 80T',
    'stage_3': 'Stage 3: Yard Stacking Blok A, B, dan C',
    'stage_4': 'Stage 4: Gudang CFS LCL Rampa D1–D5',
    'stage_5': 'Stage 5: Reefer Cold Chain Hub (-20.2°C)',
    'stage_6': 'Stage 6: Jalur Siding Kereta Api KA 2518 Pasoso–Priok'
};

const zoneNamesMap = {
    'Z1': 'Zona Spasial Z1: Gerbang ANPR, Timbangan VGM & Buffer Truk (4.2 Ha)',
    'Z2': 'Zona Spasial Z2: Lapangan Penumpukan Peti Kemas Blok A–C (16.5 Ha)',
    'Z3': 'Zona Spasial Z3: Gudang Konsolidasi CFS Rampa D1–D5 (6.8 Ha)',
    'Z4': 'Zona Spasial Z4: Siding Kereta Api Intermodal & Reefer Hub (7.5 Ha)'
};

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
        tbody.innerHTML = `<tr><td colspan="7" class="py-4 text-center text-slate-400 text-xs">Tidak ada faktur pada kategori ini.</td></tr>`;
        return;
    }

    tbody.innerHTML = filtered.map(inv => {
        let badge = `<span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-bold text-[9px]">MENUNGGU</span>`;
        if (inv.payment_status === 'PAID') {
            badge = `<span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold text-[9px]">LUNAS</span>`;
        } else if (inv.payment_status === 'OVERDUE') {
            badge = `<span class="px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200 font-bold text-[9px]">OVERDUE</span>`;
        }

        const safeInv = encodeURIComponent(JSON.stringify(inv));
        return `
        <tr class="hover:bg-blue-50/30 transition">
            <td class="py-1.5 px-2 font-mono font-bold text-[#002f5e]">${inv.invoice_number}</td>
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
        if (typeof CIDPPrint !== 'undefined') {
            const sub = Math.round(inv.total_amount / 1.11);
            const ppn = inv.total_amount - sub;
            CIDPPrint.printInvoice({
                no: inv.invoice_number,
                date: inv.date_str,
                dueDate: inv.date_str,
                customer: inv.customer_name,
                status: inv.payment_status,
                subtotal: sub,
                ppn: ppn,
                total: inv.total_amount,
                items: [
                    { desc: inv.service_type + ' — Kontainer ' + inv.container_number, qty: '1 Paket', rate: sub, total: sub }
                ]
            });
        }
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
            if (!['A', 'B', 'C'].includes(c.block)) return false;
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
    if (countBadge) {
        countBadge.innerText = `${filtered.length} / ${berandaState.containers.length} Unit`;
    }

    if (filtered.length === 0) {
        tbody.innerHTML = `
        <tr>
            <td colspan="6" class="py-6 text-center text-slate-400 text-xs">
                Tidak ditemukan kontainer yang cocok dengan filter saat ini.
                <button type="button" onclick="resetBerandaFilters()" class="ml-2 text-[#0170b9] font-bold underline cursor-pointer">Reset Filter</button>
            </td>
        </tr>`;
        return;
    }

    tbody.innerHTML = filtered.map(c => {
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
        const c = JSON.parse(decodeURIComponent(encodedCtr));
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
    if (!c || typeof CIDPPrint === 'undefined') return;
    const sub = Number(c.storage_rp || 900000) + Number(c.demurrage_rp || 0) + 500000;
    const ppn = Math.round(sub * 0.11);
    const items = [
        { desc: `Lift-On / Lift-Off (Lo-Lo) Reach Stacker Kalmar — ${c.container_number}`, qty: '2 Moves', rate: 250000, total: 500000 },
        { desc: `Jasa Penumpukan Yard (${c.position_label})`, qty: `${c.dwell_days} Hari`, rate: Math.round(c.storage_rp / Math.max(1, c.dwell_days)), total: c.storage_rp }
    ];
    if (c.demurrage_rp > 0) {
        items.push({ desc: `Biaya Demurrage Kelebihan Inap (>3 Hari)`, qty: `${c.dwell_days - 3} Hari`, rate: Math.round(c.demurrage_rp / Math.max(1, c.dwell_days - 3)), total: c.demurrage_rp });
    }
    CIDPPrint.printInvoice({
        no: 'INV/2026/CTR-' + c.container_number.slice(-4),
        date: new Date().toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' }),
        dueDate: new Date().toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' }),
        customer: c.owner_company,
        status: c.is_demurrage ? 'PENDING' : 'PAID',
        subtotal: sub,
        ppn: ppn,
        total: sub + ppn,
        items: items
    });
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

            // Update KPI DOM
            const k = data.kpi;
            const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.innerText = val; };
            setTxt('kpiYardVal', Number(k.containers_yard).toLocaleString('id-ID'));
            setTxt('kpiYardPct', k.yard_occupancy + '%');
            setTxt('kpiGateVal', k.gate_turnaround);
            setTxt('kpiGateGaugeTxt', k.gate_turnaround + 'm');
            setTxt('kpiTrucksCount', k.total_trucks);
            setTxt('kpiRailVal', k.loaded_wagons + '/' + k.total_wagons);
            setTxt('kpiRailPct', k.siding_pct + '%');
            setTxt('kpiReeferTemp', k.reefer_temp + '°C');
            setTxt('kpiReeferPlugs', k.reefer_plugs);
            setTxt('kpiReeferPct', k.reefer_pct + '%');
            setTxt('kpiCustomsVal', Number(k.customs_pct).toFixed(1) + '%');
            setTxt('kpiCustomsBox', k.customs_cleared + '/' + k.customs_total);
            setTxt('kpiCfsJobs', k.cfs_jobs);
            setTxt('badgeScadaUptime', 'SCADA ' + k.scada_uptime + '%');
            setTxt('ceisaSppbSec', k.sppb_sec + ' Detik');

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
        berandaState.countdown = 15;
    }
}

function toggleBerandaAutoSync() {
    berandaState.autoSyncEnabled = !berandaState.autoSyncEnabled;
    const dot = document.getElementById('autoSyncDot');
    const cd = document.getElementById('autoSyncCountdown');
    if (berandaState.autoSyncEnabled) {
        berandaState.countdown = 15;
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

    // Konfigurasi Global Chart.js
    Chart.defaults.font.family = '"Plus Jakarta Sans", sans-serif';
    Chart.defaults.font.size = 9.5;
    Chart.defaults.color = '#64748b';
    Chart.defaults.plugins.tooltip.backgroundColor = '#0f172a';
    Chart.defaults.plugins.tooltip.padding = 7;
    Chart.defaults.plugins.tooltip.cornerRadius = 6;
    Chart.defaults.plugins.tooltip.titleFont = { size: 10.5, weight: 'bold' };
    Chart.defaults.plugins.tooltip.bodyFont = { size: 9.5 };

    // 1. Dual Smooth Area Chart: Gate Throughput vs VBS Hourly Demand
    const gateVbsCanvas = document.getElementById('gateVbsAreaChart');
    if (gateVbsCanvas) {
        new Chart(gateVbsCanvas, {
            type: 'line',
            data: {
                labels: ['06:00', '08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00', '22:00'],
                datasets: [
                    {
                        label: 'Aktual Gate-In',
                        data: [14, 28, 42, 35, 48, 52, 38, 22, 16],
                        borderColor: '#0170b9',
                        backgroundColor: 'rgba(1, 112, 185, 0.12)',
                        fill: true,
                        tension: 0.38,
                        borderWidth: 2,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#0170b9',
                        pointBorderWidth: 1.5,
                        pointRadius: 2.5,
                        pointHoverRadius: 4.5
                    },
                    {
                        label: 'VBS Booked Truk',
                        data: [16, 25, 40, 38, 50, 48, 32, 20, 12],
                        borderColor: '#ea580c',
                        backgroundColor: 'rgba(234, 88, 12, 0.08)',
                        fill: true,
                        tension: 0.38,
                        borderWidth: 1.75,
                        borderDash: [4, 4],
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#ea580c',
                        pointBorderWidth: 1.5,
                        pointRadius: 2.5,
                        pointHoverRadius: 4.5
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
                        labels: { usePointStyle: true, boxWidth: 6, padding: 5, font: { size: 9 } }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [2, 4], color: '#f1f5f9' },
                        ticks: { font: { size: 8.5 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 8.5 } }
                    }
                }
            }
        });
    }

    // 2. Heavy Equipment SCADA OEE: 5-Axis Pentagon Spider Radar Chart
    const radarCanvas = document.getElementById('scadaEquipmentRadarChart');
    if (radarCanvas) {
        new Chart(radarCanvas, {
            type: 'radar',
            data: {
                labels: ['Moves/Jam', 'Efisiensi BBM', 'Uptime Mesin', 'Maneuver Speed', 'K3 Safety'],
                datasets: [
                    {
                        label: 'Kalmar RS-01',
                        data: [92, 85, 96, 88, 95],
                        borderColor: '#0170b9',
                        backgroundColor: 'rgba(1, 112, 185, 0.22)',
                        borderWidth: 1.75,
                        pointBackgroundColor: '#0170b9',
                        pointRadius: 2.5
                    },
                    {
                        label: 'RTG Konecranes',
                        data: [88, 92, 94, 80, 98],
                        borderColor: '#ea580c',
                        backgroundColor: 'rgba(234, 88, 12, 0.20)',
                        borderWidth: 1.75,
                        pointBackgroundColor: '#ea580c',
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
                        labels: { usePointStyle: true, boxWidth: 6, padding: 4, font: { size: 8.5 } }
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
                            font: { size: 8.5, weight: '700' },
                            color: '#475569'
                        }
                    }
                }
            }
        });
    }

    // 3. Container Dwelling Time & Demurrage Risk (Interactive Click filters Demurrage)
    const dwellingCanvas = document.getElementById('dwellingTimeBarChart');
    if (dwellingCanvas) {
        new Chart(dwellingCanvas, {
            type: 'bar',
            data: {
                labels: ['1-2 Hari', '3 Hari', '4-5 Hari', '6+ Hari'],
                datasets: [{
                    label: 'Porsi Peti Kemas (%)',
                    data: [68, 22, 7, 3],
                    backgroundColor: ['#10b981', '#f59e0b', '#ea580c', '#ef4444'],
                    borderRadius: 4,
                    barThickness: 22
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
                        max: 80,
                        grid: { borderDash: [2, 4], color: '#f1f5f9' },
                        ticks: {
                            font: { size: 8.5 },
                            callback: function(v) { return v + '%'; }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 8.5 } }
                    }
                }
            }
        });
    }

    // 4. Multimodal Modal-Split & ESG: Segmented Donut Chart
    const modalSplitCanvas = document.getElementById('modalSplitDonutChart');
    if (modalSplitCanvas) {
        new Chart(modalSplitCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Kereta Api (68%)', 'Truk Jalan (32%)'],
                datasets: [{
                    data: [68, 32],
                    backgroundColor: ['#0170b9', '#94a3b8'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '74%',
                onClick: function(evt, elements) {
                    if (elements.length > 0 && elements[0].index === 0) {
                        setBerandaStageFilter('stage_6');
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 6, padding: 4, font: { size: 8.5 } }
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
        new Chart(revenueCanvas, {
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
                        label: 'Storage',
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
</script>
