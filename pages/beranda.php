<?php
// =============================================================================
// MODUL: ENTERPRISE EXECUTIVE CONTROL TOWER DASHBOARD (DPMS 35 HA)
// File: pages/beranda.php
// Proyek: Conclusion Intermodal Dry Port (CIDP 35 Hectares)
// Desain: Clean High-Contrast Light Enterprise Control Tower (#ffffff, #0170b9, #ea580c)
// Standar: UN/CEFACT, ISO 18000-6C RFID, Bea Cukai CEISA 4.0, SOLAS VGM 80T, HACCP Cold Chain
// =============================================================================

// Normalisasi & Fallback Metrik Basis Data Riil
$containers_yard = !empty($db_stats['containers_in_yard']) ? (int)$db_stats['containers_in_yard'] : 8420;
$total_trucks    = !empty($db_stats['total_gate_trucks']) ? (int)$db_stats['total_gate_trucks'] : 242;
$loaded_w        = !empty($db_stats['loaded_wagons']) ? (int)$db_stats['loaded_wagons'] : 24;
$total_w         = !empty($db_stats['total_wagons']) ? (int)$db_stats['total_wagons'] : 30;
$reefer_plugs    = !empty($db_stats['reefer_count']) ? (int)$db_stats['reefer_count'] : 184;
$customs_percent = !empty($db_stats['customs_pct']) && $db_stats['customs_pct'] > 0 ? (float)$db_stats['customs_pct'] : 92.4;
$customs_cleared = !empty($db_stats['customs_cleared']) ? (int)$db_stats['customs_cleared'] : 22;
$customs_total   = !empty($db_stats['customs_total']) ? (int)$db_stats['customs_total'] : 24;
$cfs_jobs        = !empty($db_stats['cfs_active_jobs']) ? (int)$db_stats['cfs_active_jobs'] : 18;
$paid_rev        = !empty($db_stats['billing_paid']) && $db_stats['billing_paid'] > 0 ? (float)$db_stats['billing_paid'] : 148500000;

// Kalkulasi Persentase Dinamis
$yard_cap_max    = 12500;
$yard_occupancy  = round(($containers_yard / $yard_cap_max) * 100, 1);
$siding_pct      = $total_w > 0 ? round(($loaded_w / $total_w) * 100) : 80;
$reefer_cap      = 300;
$reefer_pct      = round(($reefer_plugs / $reefer_cap) * 100, 1);
$date_display    = !empty($tanggal_sekarang) ? $tanggal_sekarang : date('d M Y');
?>

<div class="relative animate-fadeIn pb-12">

    <!-- ======================================================================= -->
    <!-- AMBIENT SPATIAL BACKGROUND LAYER (BATIK NUSANTARA & DRY PORT BLUEPRINT) -->
    <!-- ======================================================================= -->
    <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden select-none" aria-hidden="true">
        <!-- Full-Canvas Subtle Luxury Batik Kawung Maritim Watermark (Harmonized with Sidebar Heritage) -->
        <svg class="absolute inset-0 w-full h-full opacity-[0.045]" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="canvasBatikKawung" width="56" height="56" patternUnits="userSpaceOnUse">
                    <g stroke="#0170b9" stroke-width="1.1" fill="none">
                        <ellipse cx="28" cy="14" rx="8" ry="13" />
                        <ellipse cx="28" cy="42" rx="8" ry="13" />
                        <ellipse cx="14" cy="28" rx="13" ry="8" />
                        <ellipse cx="42" cy="28" rx="13" ry="8" />
                        <ellipse cx="28" cy="14" rx="4.5" ry="8" stroke-dasharray="2 1.5" />
                        <ellipse cx="28" cy="42" rx="4.5" ry="8" stroke-dasharray="2 1.5" />
                        <ellipse cx="14" cy="28" rx="8" ry="4.5" stroke-dasharray="2 1.5" />
                        <ellipse cx="42" cy="28" rx="8" ry="4.5" stroke-dasharray="2 1.5" />
                        <ellipse cx="0" cy="0" rx="8" ry="13" />
                        <ellipse cx="56" cy="0" rx="8" ry="13" />
                        <ellipse cx="0" cy="56" rx="8" ry="13" />
                        <ellipse cx="56" cy="56" rx="8" ry="13" />
                    </g>
                    <circle cx="28" cy="28" r="2.5" fill="#ea580c" />
                    <circle cx="0" cy="0" r="2" fill="#ea580c" />
                    <circle cx="56" cy="0" r="2" fill="#ea580c" />
                    <circle cx="0" cy="56" r="2" fill="#ea580c" />
                    <circle cx="56" cy="56" r="2" fill="#ea580c" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#canvasBatikKawung)" />
        </svg>

        <!-- Subtle Dry Port Aerial Layout Blueprint (Soft Atmospheric Watermark) -->
        <div class="absolute top-0 right-0 w-full lg:w-3/4 max-w-[1000px] h-[550px] opacity-[0.04] bg-no-repeat bg-right-top"
             style="background-image: url('assets/img/facilities/stacking_yard.jpg'); background-size: cover; filter: grayscale(100%); mask-image: radial-gradient(circle at 85% 15%, black 30%, transparent 75%); -webkit-mask-image: radial-gradient(circle at 85% 15%, black 30%, transparent 75%);">
        </div>
    </div>

    <!-- MAIN DASHBOARD CONTENT (ELEVATED Z-10) -->
    <div class="relative z-10 space-y-4">

        <!-- ======================================================================= -->
        <!-- 1. TOP EXECUTIVE COCKPIT BAR (UNIFIED BOARDROOM HEADER)                -->
        <!-- ======================================================================= -->
        <div class="bg-white/95 backdrop-blur-md rounded-xl px-4 py-3 shadow-2xs border border-slate-200/90 relative overflow-hidden flex flex-col xl:flex-row items-start xl:items-center justify-between gap-3 transition-all">
            <!-- Subtle Luxury Batik Kawung Rosette Watermark (Harmonized with Sidebar) -->
            <div class="absolute -right-6 -top-6 w-40 h-40 pointer-events-none opacity-[0.06] z-0" aria-hidden="true">
                <svg width="100%" height="100%" viewBox="0 0 100 100" fill="none" stroke="#0170b9" stroke-width="1.6">
                    <ellipse cx="50" cy="22" rx="16" ry="24" />
                    <ellipse cx="50" cy="78" rx="16" ry="24" />
                    <ellipse cx="22" cy="50" rx="24" ry="16" />
                    <ellipse cx="78" cy="50" rx="24" ry="16" />
                    <circle cx="50" cy="50" r="4.5" fill="#ea580c" />
                    <circle cx="50" cy="50" r="14" stroke="#0170b9" stroke-width="1" stroke-dasharray="2 2" />
                </svg>
            </div>

            <div class="flex items-center gap-3 relative z-10">
                <div class="w-9 h-9 rounded-xl bg-orange-50 border border-orange-200/80 text-orange-600 flex items-center justify-center text-sm flex-shrink-0 shadow-2xs">
                    <i class="fa-solid fa-tower-observation"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-sm sm:text-[15px] font-extrabold text-slate-900 tracking-tight leading-tight">
                            Enterprise Executive Control Tower &bull; CIDP 35 Hectares
                        </h1>
                        <span class="text-[9px] font-extrabold tracking-wider uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full flex items-center gap-1.5 shadow-2xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Live 35 Ha
                        </span>
                        <span class="text-[9px] font-bold tracking-wider uppercase bg-blue-50 text-[#0170b9] border border-blue-200 px-2 py-0.5 rounded-full flex items-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-satellite-dish text-[8.5px]"></i>
                            DPMS Operational Sync
                        </span>
                        <span class="text-[9px] font-bold tracking-wider uppercase bg-orange-50 text-orange-700 border border-orange-200 px-2 py-0.5 rounded-full flex items-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-network-wired text-[8.5px]"></i>
                            SCADA 99.8%
                        </span>
                    </div>
                    <div class="text-[9.5px] font-semibold text-slate-500 mt-0.5 flex items-center gap-1.5">
                        <span class="text-orange-600 font-bold">Conclusion Intermodal DPMS</span> &bull; 
                        <span>Orkestrasi Multimoda Yard 35 Ha &bull; Rel Siding Pasoso &bull; CFS Gudang &bull; CEISA 4.0</span>
                    </div>
                </div>
            </div>

            <!-- Cockpit Telemetry Strip & Quick Sensor Shortcuts -->
            <div class="flex items-center gap-2.5 sm:gap-3 flex-wrap text-[10.5px] text-slate-600 w-full xl:w-auto justify-between xl:justify-end relative z-10">
                <!-- Clock Pill -->
                <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1 text-slate-700">
                    <i class="fa-regular fa-calendar text-slate-400 text-[10px]"></i>
                    <span class="font-semibold text-slate-700"><?= $date_display ?></span>
                    <span class="text-slate-300">&bull;</span>
                    <i class="fa-solid fa-clock text-orange-500 text-[9.5px]"></i>
                    <span id="berandaLiveClock" class="font-extrabold text-slate-900 font-mono">--:--:-- WIB</span>
                </div>

                <!-- Siding KA Ready Pill -->
                <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-slate-700">
                    <i class="fa-solid fa-train-subway text-[#0170b9] text-[10px]"></i>
                    <span class="text-slate-500 font-medium">Siding KA:</span>
                    <span class="font-bold text-emerald-700 bg-emerald-50 px-1 py-0.2 rounded border border-emerald-200 text-[9.5px]">Track S-01 Ready</span>
                </div>

                <!-- Direct Quick-Launch Sensor Shortcuts -->
                <div class="flex items-center gap-1.5">
                    <a href="dashboard.php?page=gate" class="px-2 py-1 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 hover:text-[#0170b9] rounded-lg text-[10.5px] font-bold transition flex items-center gap-1 shadow-2xs" title="Buka Modul Gate ANPR & Jembatan Timbang SOLAS">
                        <i class="fa-solid fa-door-open text-[#0170b9] text-[9.5px]"></i>
                        <span>Gate ANPR</span>
                    </a>
                    <a href="dashboard.php?page=cfs" class="px-2 py-1 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 hover:text-orange-600 rounded-lg text-[10.5px] font-bold transition flex items-center gap-1 shadow-2xs" title="Buka Gudang Konsolidasi CFS Rampa D1-D5">
                        <i class="fa-solid fa-warehouse text-orange-500 text-[9.5px]"></i>
                        <span>CFS Rampa</span>
                    </a>
                    <a href="dashboard.php?page=reefer" class="px-2 py-1 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 hover:text-cyan-600 rounded-lg text-[10.5px] font-bold transition flex items-center gap-1 shadow-2xs" title="Buka Modul Reefer Cold Chain 300 Plugs">
                        <i class="fa-solid fa-snowflake text-cyan-500 text-[9.5px]"></i>
                        <span>Reefer Plug</span>
                    </a>
                    <a href="dashboard.php?page=denah" class="px-2 py-1 bg-orange-50 hover:bg-orange-100 border border-orange-200 text-orange-700 rounded-lg text-[10.5px] font-bold transition flex items-center gap-1 shadow-2xs" title="Buka Denah Spasial GIS Terminal 35 Ha">
                        <i class="fa-solid fa-map-location-dot text-orange-600 text-[9.5px]"></i>
                        <span>GIS Denah</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- ======================================================================= -->
        <!-- 2. TOP METRIC ROW (6 UNIFIED KPI CARDS - COHESIVE INSTRUMENT DECK)     -->
        <!-- ======================================================================= -->
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
            
            <!-- KPI 1: Yard Stacking (TEU + Circular Progress Ring + 24h Delta Sparkline) -->
            <div class="bg-white rounded-xl p-3 shadow-2xs border border-slate-200/90 hover:border-blue-300 hover:shadow-xs transition flex flex-col justify-between group">
                <div class="flex items-center justify-between">
                    <span class="text-[9.5px] font-extrabold uppercase tracking-wider text-slate-500">Yard Stacking</span>
                    <span class="w-6 h-6 rounded-md bg-blue-50 text-[#0170b9] border border-blue-100 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </span>
                </div>
                
                <div class="my-1.5 flex items-baseline justify-between">
                    <div>
                        <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight leading-none"><?= number_format($containers_yard) ?></h3>
                        <span class="text-[9px] text-slate-400 font-semibold">/ <?= number_format($yard_cap_max) ?> TEU</span>
                    </div>
                    <!-- Circular SVG Progress Ring (67.4%) -->
                    <div class="relative w-10 h-10 flex-shrink-0 flex items-center justify-center">
                        <svg class="w-10 h-10 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-slate-100" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="text-[#0170b9]" stroke-dasharray="<?= min(100, (int)$yard_occupancy) ?>, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <span class="absolute text-[8.5px] font-black text-slate-800 font-mono"><?= $yard_occupancy ?>%</span>
                    </div>
                </div>

                <!-- 24h Delta Sparkline SVG -->
                <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9px]">
                    <div class="flex items-center gap-1 text-emerald-600 font-bold">
                        <i class="fa-solid fa-arrow-trend-up text-[8px]"></i>
                        <span>+4.2% (24h)</span>
                    </div>
                    <svg class="w-12 h-3.5" viewBox="0 0 48 14" fill="none">
                        <path d="M2 11 L10 9 L18 10 L26 5 L34 7 L42 3 L46 4" stroke="#0170b9" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>

            <!-- KPI 2: Gate Turnaround Time (Radial Symmetrical Gauge 18.2 min + Throughput) -->
            <div class="bg-white rounded-xl p-3 shadow-2xs border border-slate-200/90 hover:border-orange-300 hover:shadow-xs transition flex flex-col justify-between group">
                <div class="flex items-center justify-between">
                    <span class="text-[9.5px] font-extrabold uppercase tracking-wider text-slate-500">Gate Turnaround</span>
                    <span class="w-6 h-6 rounded-md bg-orange-50 text-[#ea580c] border border-orange-100 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                        <i class="fa-solid fa-truck-fast"></i>
                    </span>
                </div>

                <div class="my-1.5 flex items-baseline justify-between">
                    <div>
                        <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight leading-none">18.2 <span class="text-[10px] font-semibold text-slate-400">mnt</span></h3>
                        <span class="text-[9px] text-emerald-600 font-bold">&le; 20m Target INSA</span>
                    </div>
                    <div class="relative w-11 h-9 flex-shrink-0 flex flex-col items-center justify-end">
                        <svg class="w-11 h-7" viewBox="0 0 36 20">
                            <path d="M 4 18 A 14 14 0 0 1 32 18" fill="none" stroke="#f1f5f9" stroke-width="4" stroke-linecap="round"/>
                            <path d="M 4 18 A 14 14 0 0 1 32 18" fill="none" stroke="#ea580c" stroke-width="4" stroke-dasharray="32, 44" stroke-linecap="round"/>
                        </svg>
                        <span class="absolute bottom-0 text-[8px] font-black text-orange-600 font-mono">18.2m</span>
                    </div>
                </div>

                <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9px] text-slate-500">
                    <span class="font-semibold text-slate-700"><?= $total_trucks ?> Truk Aktif</span>
                    <span class="text-emerald-600 font-bold">VBS 98% On-Time</span>
                </div>
            </div>

            <!-- KPI 3: Siding KA Intermodal (24/30 TEU + Departure ETA) -->
            <div class="bg-white rounded-xl p-3 shadow-2xs border border-slate-200/90 hover:border-sky-300 hover:shadow-xs transition flex flex-col justify-between group">
                <div class="flex items-center justify-between">
                    <span class="text-[9.5px] font-extrabold uppercase tracking-wider text-slate-500">Siding KA Intermodal</span>
                    <span class="w-6 h-6 rounded-md bg-blue-50 text-[#0170b9] border border-blue-100 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                        <i class="fa-solid fa-train"></i>
                    </span>
                </div>

                <div class="my-1.5 flex items-baseline justify-between">
                    <div>
                        <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight leading-none"><?= $loaded_w ?>/<?= $total_w ?> <span class="text-[10px] font-semibold text-slate-400">TEU</span></h3>
                        <span class="text-[9px] text-[#0170b9] font-bold">KA 2518 ke Priok</span>
                    </div>
                    <div class="relative w-10 h-10 flex-shrink-0 flex items-center justify-center">
                        <svg class="w-10 h-10 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-slate-100" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="text-[#0170b9]" stroke-dasharray="<?= $siding_pct ?>, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <span class="absolute text-[8.5px] font-black text-slate-800 font-mono"><?= $siding_pct ?>%</span>
                    </div>
                </div>

                <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9px] text-slate-500">
                    <span class="font-medium text-slate-600">Track S-01 (600m)</span>
                    <span class="font-bold text-orange-600">ETA 16:45 WIB</span>
                </div>
            </div>

            <!-- KPI 4: Reefer Cold Chain (Cyan Telemetry -20.2°C + 300 Plugs) -->
            <div class="bg-white rounded-xl p-3 shadow-2xs border border-slate-200/90 hover:border-cyan-300 hover:shadow-xs transition flex flex-col justify-between group">
                <div class="flex items-center justify-between">
                    <span class="text-[9.5px] font-extrabold uppercase tracking-wider text-slate-500">Reefer Cold Chain</span>
                    <span class="w-6 h-6 rounded-md bg-cyan-50 text-[#06b6d4] border border-cyan-100 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                        <i class="fa-solid fa-snowflake"></i>
                    </span>
                </div>

                <div class="my-1.5 flex items-baseline justify-between">
                    <div>
                        <h3 class="text-lg sm:text-xl font-black text-cyan-600 font-mono tracking-tight leading-none">-20.2&deg;C</h3>
                        <span class="text-[9px] text-cyan-700 font-bold"><?= $reefer_plugs ?> / 300 Plugs</span>
                    </div>
                    <div class="relative w-10 h-10 flex-shrink-0 flex items-center justify-center">
                        <svg class="w-10 h-10 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-slate-100" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="text-cyan-500" stroke-dasharray="<?= min(100, (int)$reefer_pct) ?>, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <span class="absolute text-[8.5px] font-black text-cyan-800 font-mono"><?= $reefer_pct ?>%</span>
                    </div>
                </div>

                <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9px]">
                    <span class="font-bold text-slate-600">LoRa 868MHz</span>
                    <span class="text-emerald-600 font-bold">HACCP 98.8% OK</span>
                </div>
            </div>

            <!-- KPI 5: Bea Cukai CEISA 4.0 (Clearance Pct + SPPB Counter) -->
            <div class="bg-white rounded-xl p-3 shadow-2xs border border-slate-200/90 hover:border-emerald-300 hover:shadow-xs transition flex flex-col justify-between group">
                <div class="flex items-center justify-between">
                    <span class="text-[9.5px] font-extrabold uppercase tracking-wider text-slate-500">Bea Cukai CEISA 4.0</span>
                    <span class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                        <i class="fa-solid fa-shield-halved"></i>
                    </span>
                </div>

                <div class="my-1.5 flex items-baseline justify-between">
                    <div>
                        <h3 class="text-lg sm:text-xl font-black text-emerald-700 tracking-tight leading-none"><?= number_format($customs_percent, 1) ?>%</h3>
                        <span class="text-[9px] text-emerald-600 font-bold"><?= $customs_cleared ?>/<?= $customs_total ?> Box Cleared</span>
                    </div>
                    <div class="relative w-10 h-10 flex-shrink-0 flex items-center justify-center">
                        <svg class="w-10 h-10 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-slate-100" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="text-emerald-500" stroke-dasharray="<?= min(100, (int)$customs_percent) ?>, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <span class="absolute text-[8.5px] font-black text-emerald-800 font-mono"><?= round($customs_percent) ?>%</span>
                    </div>
                </div>

                <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9px] text-slate-500">
                    <span class="font-semibold text-slate-700">SPPB Auto Release</span>
                    <span class="text-emerald-600 font-bold">Hi-Co X-Ray OK</span>
                </div>
            </div>

            <!-- KPI 6: CFS & Terminal Billing (Active Jobs + Paid Revenue) -->
            <div class="bg-white rounded-xl p-3 shadow-2xs border border-slate-200/90 hover:border-amber-300 hover:shadow-xs transition flex flex-col justify-between group">
                <div class="flex items-center justify-between">
                    <span class="text-[9.5px] font-extrabold uppercase tracking-wider text-slate-500">CFS &amp; Billing</span>
                    <span class="w-6 h-6 rounded-md bg-amber-50 text-[#f59e0b] border border-amber-100 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </span>
                </div>

                <div class="my-1.5 flex items-baseline justify-between">
                    <div>
                        <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight leading-none"><?= $cfs_jobs ?> <span class="text-[10px] font-normal text-slate-400">Jobs</span></h3>
                        <span class="text-[9px] text-amber-600 font-bold">Rampa D1-D5 Aktif</span>
                    </div>
                    <div class="relative w-10 h-10 flex-shrink-0 flex items-center justify-center">
                        <svg class="w-10 h-10 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-slate-100" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="text-amber-500" stroke-dasharray="75, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <span class="absolute text-[8.5px] font-black text-amber-700 font-mono">75%</span>
                    </div>
                </div>

                <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9px]">
                    <span class="font-medium text-slate-500">Omzet 7 Hari</span>
                    <span class="text-emerald-700 font-extrabold">Rp <?= number_format($paid_rev / 1000000, 1) ?>Jt Paid</span>
                </div>
            </div>

        </div>

    <!-- ======================================================================= -->
    <!-- 3. MULTIMODAL LOGISTICS PIPELINE CONVEYOR RIBBON (6-STAGE WORKFLOW STRIP)-->
    <!-- ======================================================================= -->
    <div class="bg-white/95 backdrop-blur-xs rounded-xl p-3 sm:p-4 shadow-2xs border border-slate-200/90 relative overflow-hidden">
        <!-- Subtle Parang Bahari & Rail Pipeline Vector Watermark -->
        <div class="absolute -right-8 -bottom-8 w-60 h-32 pointer-events-none opacity-[0.045] z-0" aria-hidden="true">
            <svg width="100%" height="100%" viewBox="0 0 200 100" fill="none" stroke="#0170b9" stroke-width="1.6">
                <path d="M 0 25 Q 50 10, 100 25 T 200 25" />
                <path d="M 0 55 Q 50 40, 100 55 T 200 55" />
                <path d="M 0 85 Q 50 70, 100 85 T 200 85" stroke="#ea580c" />
                <circle cx="25" cy="25" r="3" fill="#ea580c" />
                <circle cx="125" cy="25" r="3" fill="#ea580c" />
            </svg>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 mb-2.5 pb-2 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-slate-900 text-xs sm:text-sm flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#0170b9] animate-pulse"></span>
                    <i class="fa-solid fa-arrows-split-up-and-left text-[#0170b9] text-sm"></i>
                    <span>Multimodal Logistics Pipeline Conveyor Ribbon (6-Stage End-to-End Visual Workflow)</span>
                </h3>
                <p class="text-[10.5px] text-slate-400 mt-0.5">Alur kargo terpadu 35 Hektar dari gerbang ANPR hingga pemberangkatan kereta api intermodal ke Pelabuhan Tanjung Priok.</p>
            </div>
            <span class="text-[10px] font-extrabold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200 self-start sm:self-auto flex items-center gap-1 shadow-2xs">
                <i class="fa-solid fa-circle-check text-[9px]"></i> Pipeline 100% Active
            </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-2 xl:gap-2.5">
            <!-- Stage 1: Gate-In ANPR OCR -->
            <a href="dashboard.php?page=gate" class="group p-2.5 rounded-lg border border-slate-200 bg-slate-50/60 hover:bg-orange-50/40 hover:border-orange-300 transition flex flex-col justify-between relative overflow-hidden shadow-2xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="w-5 h-5 rounded-md bg-[#0170b9] text-white flex items-center justify-center text-[10px] font-black font-mono">1</span>
                    <span class="text-[8px] font-extrabold text-[#0170b9] bg-blue-50 px-1 py-0.2 rounded border border-blue-200 uppercase">Stage 1</span>
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
                    <i class="fa-solid fa-arrow-right text-[8px] text-slate-400 group-hover:text-orange-500 group-hover:translate-x-0.5 transition-transform"></i>
                </div>
            </a>

            <!-- Stage 2: Weighbridge SOLAS VGM 80T -->
            <a href="dashboard.php?page=gate" class="group p-2.5 rounded-lg border border-slate-200 bg-slate-50/60 hover:bg-orange-50/40 hover:border-orange-300 transition flex flex-col justify-between relative overflow-hidden shadow-2xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="w-5 h-5 rounded-md bg-orange-600 text-white flex items-center justify-center text-[10px] font-black font-mono">2</span>
                    <span class="text-[8px] font-extrabold text-orange-700 bg-orange-50 px-1 py-0.2 rounded border border-orange-200 uppercase">Stage 2</span>
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
                    <i class="fa-solid fa-arrow-right text-[8px] text-slate-400 group-hover:text-orange-500 group-hover:translate-x-0.5 transition-transform"></i>
                </div>
            </a>

            <!-- Stage 3: Yard Stacking Blocks A/B/C -->
            <a href="dashboard.php?page=yard" class="group p-2.5 rounded-lg border border-slate-200 bg-slate-50/60 hover:bg-orange-50/40 hover:border-orange-300 transition flex flex-col justify-between relative overflow-hidden shadow-2xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="w-5 h-5 rounded-md bg-[#0170b9] text-white flex items-center justify-center text-[10px] font-black font-mono">3</span>
                    <span class="text-[8px] font-extrabold text-blue-700 bg-blue-50 px-1 py-0.2 rounded border border-blue-200 uppercase">Stage 3</span>
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
                    <i class="fa-solid fa-arrow-right text-[8px] text-slate-400 group-hover:text-orange-500 group-hover:translate-x-0.5 transition-transform"></i>
                </div>
            </a>

            <!-- Stage 4: CFS Rampa D1-D5 -->
            <a href="dashboard.php?page=cfs" class="group p-2.5 rounded-lg border border-slate-200 bg-slate-50/60 hover:bg-orange-50/40 hover:border-orange-300 transition flex flex-col justify-between relative overflow-hidden shadow-2xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="w-5 h-5 rounded-md bg-orange-600 text-white flex items-center justify-center text-[10px] font-black font-mono">4</span>
                    <span class="text-[8px] font-extrabold text-orange-700 bg-orange-50 px-1 py-0.2 rounded border border-orange-200 uppercase">Stage 4</span>
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
                    <i class="fa-solid fa-arrow-right text-[8px] text-slate-400 group-hover:text-orange-500 group-hover:translate-x-0.5 transition-transform"></i>
                </div>
            </a>

            <!-- Stage 5: Reefer Cold Chain Hub -->
            <a href="dashboard.php?page=reefer" class="group p-2.5 rounded-lg border border-slate-200 bg-slate-50/60 hover:bg-cyan-50/40 hover:border-cyan-300 transition flex flex-col justify-between relative overflow-hidden shadow-2xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="w-5 h-5 rounded-md bg-cyan-600 text-white flex items-center justify-center text-[10px] font-black font-mono">5</span>
                    <span class="text-[8px] font-extrabold text-cyan-700 bg-cyan-50 px-1 py-0.2 rounded border border-cyan-200 uppercase">Stage 5</span>
                </div>
                <div class="space-y-0.5">
                    <div class="text-[11px] font-bold text-slate-800 group-hover:text-cyan-700 transition flex items-center gap-1 truncate">
                        <i class="fa-solid fa-snowflake text-cyan-600 text-[10px]"></i>
                        <span>Reefer Cold Chain</span>
                    </div>
                    <p class="text-[9.5px] text-slate-500">300 Marechal Steker 380V</p>
                </div>
                <div class="mt-2 pt-1 border-t border-slate-200/60 flex items-center justify-between text-[9px] text-cyan-800 font-semibold">
                    <span>-20.2&deg;C &bull; SHT40 LoRa</span>
                    <i class="fa-solid fa-arrow-right text-[8px] text-slate-400 group-hover:text-cyan-600 group-hover:translate-x-0.5 transition-transform"></i>
                </div>
            </a>

            <!-- Stage 6: Rail Siding KA 2518 ke Tanjung Priok -->
            <a href="dashboard.php?page=intermodal" class="group p-2.5 rounded-lg border border-slate-200 bg-slate-50/60 hover:bg-blue-50/40 hover:border-blue-300 transition flex flex-col justify-between relative overflow-hidden shadow-2xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="w-5 h-5 rounded-md bg-emerald-600 text-white flex items-center justify-center text-[10px] font-black font-mono">6</span>
                    <span class="text-[8px] font-extrabold text-emerald-700 bg-emerald-50 px-1 py-0.2 rounded border border-emerald-200 uppercase">Stage 6</span>
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
                    <i class="fa-solid fa-arrow-right text-[8px] text-slate-400 group-hover:text-emerald-500 group-hover:translate-x-0.5 transition-transform"></i>
                </div>
            </a>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- 4. MID ANALYTICAL VISUAL GRID (CHARTS & DIAGRAMS - 4 COLUMNS)          -->
    <!-- ======================================================================= -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
        
        <!-- Col 1: Gate Throughput vs VBS Hourly Demand (Dual Smooth Area Chart) -->
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
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#0170b9]"></span>Aktual: <strong>242 Truk</strong></span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#ea580c]"></span>VBS: <strong>250 Slot</strong></span>
                <span class="text-emerald-600 font-bold">Kesesuaian: 96.8%</span>
            </div>
        </div>

        <!-- Col 2: Heavy Equipment SCADA OEE (5-Axis Pentagon Spider Radar Chart) -->
        <div class="bg-white/95 backdrop-blur-xs rounded-xl shadow-2xs border border-slate-200/90 p-3 sm:p-3.5 flex flex-col justify-between relative overflow-hidden">
            <!-- Subtle SCADA Geometric Radar Ring Watermark -->
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

        <!-- Col 3: Container Dwelling Time & Demurrage Risk (Tiered Vertical Bar Chart) -->
        <div class="bg-white rounded-xl shadow-2xs border border-slate-200 p-3 sm:p-3.5 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-1 pb-1.5 border-b border-slate-100">
                    <h3 class="font-extrabold text-slate-900 text-xs flex items-center">
                        <i class="fa-solid fa-hourglass-half mr-1.5 text-emerald-600 text-xs"></i>Dwelling &amp; Demurrage
                    </h3>
                    <span class="text-[8.5px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 font-mono">
                        Rerata: 2.4 Hari
                    </span>
                </div>
                <p class="text-[10px] text-slate-400 mb-2">Durasi inap kontainer dengan garis ambang batas batas aman.</p>
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

        <!-- Col 4: Multimodal Modal-Split & ESG (Segmented Donut + ESG Carbon Badge) -->
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
    <!-- 5. FACILITY SPATIAL RADAR & CUSTOMS INSPECTION HUB (3 COLUMNS)         -->
    <!-- ======================================================================= -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-3">
        
        <!-- Left: 4-Zone Spatial Terminal Distribution Grid (Z1 - Z4) (4 Cols) -->
        <div class="xl:col-span-4 bg-white/95 backdrop-blur-xs rounded-xl shadow-2xs border border-slate-200/90 p-3 sm:p-3.5 flex flex-col justify-between relative overflow-hidden">
            <!-- Subtle Terminal Blueprint Layout Silhouette -->
            <div class="absolute -right-8 -bottom-8 w-44 h-44 pointer-events-none opacity-[0.045] z-0 rounded-full"
                 style="background-image: url('assets/img/facilities/cfs_warehouse.jpg'); background-size: cover; filter: grayscale(100%); mask-image: radial-gradient(circle, black 35%, transparent 75%); -webkit-mask-image: radial-gradient(circle, black 35%, transparent 75%);">
            </div>
            <div class="flex items-center justify-between pb-1.5 border-b border-slate-100 mb-2 relative z-10">
                <h3 class="font-extrabold text-slate-900 text-xs flex items-center">
                    <i class="fa-solid fa-map-location-dot mr-1.5 text-[#0170b9] text-xs"></i>Radar 4 Zona Spasial (35 Ha)
                </h3>
                <a href="dashboard.php?page=denah" class="text-[9.5px] font-bold text-[#0170b9] hover:underline flex items-center gap-1">
                    <span>GIS Denah &rarr;</span>
                </a>
            </div>

            <div class="space-y-2 py-0.5 flex-1 flex flex-col justify-between text-[10.5px]">
                <!-- Z1: Gate & Buffer Truck -->
                <div class="p-2 rounded-lg border border-slate-100 bg-slate-50/70">
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
                        <span>2 In / 2 Out Gate</span>
                    </div>
                </div>

                <!-- Z2: Stacking Yard 12,500 TEU -->
                <div class="p-2 rounded-lg border border-slate-100 bg-slate-50/70">
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
                        <span class="text-blue-700 font-semibold">Reach Stacker OK</span>
                    </div>
                </div>

                <!-- Z3: CFS Warehouse Rampa D1-D5 -->
                <div class="p-2 rounded-lg border border-slate-100 bg-slate-50/70">
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
                        <span>Luas: <strong>4.000 m²</strong></span>
                        <span><?= $cfs_jobs ?> Jobs Berjalan</span>
                    </div>
                </div>

                <!-- Z4: Siding KA & Reefer Cold Chain -->
                <div class="p-2 rounded-lg border border-slate-100 bg-slate-50/70">
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
                        <span class="font-mono text-cyan-700 font-bold">-20.2&deg;C OK</span>
                    </div>
                </div>
            </div>

            <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9.5px] text-slate-500 mt-2">
                <span>Luas Master Plan: <strong>35 Hektar</strong></span>
                <span class="text-emerald-700 font-bold">26 BOM HW OK</span>
            </div>
        </div>

        <!-- Middle: Customs CEISA 4.0 Inspection Hub (4 Cols) -->
        <div class="xl:col-span-4 bg-white/95 backdrop-blur-xs rounded-xl shadow-2xs border border-slate-200/90 p-3 sm:p-3.5 flex flex-col justify-between relative overflow-hidden">
            <!-- Subtle Customs CEISA Shield / Seal Vector Watermark -->
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

            <div class="space-y-3 py-0.5 flex-1 flex flex-col justify-between">
                <!-- Segmented Channel Bar (PMK 190/2022) -->
                <div>
                    <div class="flex justify-between text-[10px] mb-1">
                        <span class="font-bold text-slate-700">Pipeline Kanal Pabean:</span>
                        <span class="font-bold text-emerald-700">SPPB Auto: 92%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5 flex overflow-hidden border border-slate-200/80">
                        <div class="bg-emerald-500 h-2.5" style="width: 92%" title="Jalur Hijau (92%) - SPPB Rilis Otomatis"></div>
                        <div class="bg-rose-500 h-2.5" style="width: 8%" title="Jalur Merah (8%) - Wajib Behandle & X-Ray"></div>
                    </div>
                    <div class="flex justify-between items-center text-[9px] text-slate-500 mt-1">
                        <span class="flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Jalur Hijau (SPPB Auto): <strong>92%</strong>
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Jalur Merah: <strong>8%</strong>
                        </span>
                    </div>
                </div>

                <!-- Inspection Badges (X-Ray & Behandle) -->
                <div class="space-y-2">
                    <!-- Hi-Co X-Ray Scanner -->
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

                    <!-- Physical Behandle Inspection Docks -->
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

                <!-- Mean Release Time Metric -->
                <div class="p-2 bg-emerald-50/70 border border-emerald-200/80 rounded-lg flex items-center justify-between">
                    <span class="text-[10px] text-emerald-900 font-semibold flex items-center gap-1.5">
                        <i class="fa-solid fa-bolt text-emerald-600"></i> Waktu Rilis Rata-rata SPPB:
                    </span>
                    <span class="text-xs font-black text-emerald-800 font-mono">14.2 Detik</span>
                </div>
            </div>

            <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[9.5px] text-slate-500 mt-2">
                <span>Sinkronisasi: <strong>Host-to-Host REST API</strong></span>
                <span class="text-emerald-700 font-bold">Latency 42ms</span>
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
    <!-- 6. BOTTOM EXECUTION CENTER & SHIPPER CONSIGNMENT DATA (2 COLUMNS)      -->
    <!-- ======================================================================= -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-3">
        
        <!-- Left: Live SCADA Field Telemetry Feed (5 Cols) -->
        <div class="xl:col-span-5 bg-white rounded-xl shadow-2xs border border-slate-200 p-3 sm:p-3.5 flex flex-col justify-between">
            <div class="flex justify-between items-center mb-2 pb-1.5 border-b border-slate-100">
                <h3 class="font-extrabold text-slate-900 text-xs flex items-center">
                    <i class="fa-solid fa-satellite mr-1.5 text-[#0170b9] text-xs"></i>Live SCADA Field Telemetry Feed
                </h3>
                <span class="text-[8.5px] font-extrabold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 font-mono">
                    <i class="fa-solid fa-bolt mr-0.5 text-[8px]"></i>Auto Sync &lt; 150ms
                </span>
            </div>

            <!-- Vertical Timeline Stream -->
            <div class="relative border-l-2 border-slate-100 ml-2 space-y-2.5 py-1 flex-1">
                <?php if (!empty($db_stats['recent_events'])): ?>
                    <?php foreach ($db_stats['recent_events'] as $evt): 
                        $type = strtoupper($evt['event_type'] ?? 'EVENT');
                        $dot_color = 'bg-[#0170b9]';
                        $tag_badge = 'bg-blue-50 text-blue-700 border-blue-200';
                        if (strpos($type, 'GATE_IN') !== false) {
                            $dot_color = 'bg-[#0170b9]';
                            $tag_badge = 'bg-blue-50 text-blue-700 border-blue-200';
                        } elseif (strpos($type, 'GATE_OUT') !== false) {
                            $dot_color = 'bg-emerald-500';
                            $tag_badge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        } elseif (strpos($type, 'RELOCATION') !== false || strpos($type, 'LIFT') !== false) {
                            $dot_color = 'bg-orange-500';
                            $tag_badge = 'bg-orange-50 text-orange-700 border-orange-200';
                        } elseif (strpos($type, 'REEFER') !== false) {
                            $dot_color = 'bg-cyan-500';
                            $tag_badge = 'bg-cyan-50 text-cyan-700 border-cyan-200';
                        } elseif (strpos($type, 'CFS') !== false || strpos($type, 'STRIP') !== false || strpos($type, 'STUFF') !== false) {
                            $dot_color = 'bg-amber-500';
                            $tag_badge = 'bg-amber-50 text-amber-700 border-amber-200';
                        } else {
                            $dot_color = 'bg-slate-500';
                            $tag_badge = 'bg-slate-50 text-slate-700 border-slate-200';
                        }
                        $time_str = !empty($evt['created_at']) ? date('H:i', strtotime($evt['created_at'])) : date('H:i');
                        $ctr_num = !empty($evt['container_number']) ? htmlspecialchars($evt['container_number']) : '-';
                        $notes = !empty($evt['notes']) ? htmlspecialchars($evt['notes']) : 'Aktivitas pergerakan peti kemas terekam SCADA.';
                        if (mb_strlen($notes) > 75) {
                            $notes = mb_substr($notes, 0, 72) . '...';
                        }
                        $operator = !empty($evt['operator_name']) ? htmlspecialchars($evt['operator_name']) : (!empty($evt['equipment_id']) ? htmlspecialchars($evt['equipment_id']) : 'SCADA System');
                    ?>
                    <div class="relative pl-4">
                        <div class="timeline-dot <?= $dot_color ?> ring-2 ring-white"></div>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-slate-800 text-[11px]">
                                <?= htmlspecialchars(str_replace('_', ' ', $type)) ?> 
                                <span class="font-mono <?= $tag_badge ?> border px-1 rounded text-[9.5px]"><?= $ctr_num ?></span>
                            </span>
                            <span class="text-[9.5px] text-slate-400 font-mono"><?= $time_str ?> WIB</span>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-0.5" title="<?= htmlspecialchars($evt['notes'] ?? '') ?>"><?= $notes ?></p>
                        <span class="text-[9px] text-slate-400 block mt-0.5"><i class="fa-solid fa-user-gear text-[8px] mr-1"></i><?= $operator ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Realistic Telemetry Feed Default Fallback -->
                    <div class="relative pl-4">
                        <div class="timeline-dot bg-[#0170b9] ring-2 ring-white"></div>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-slate-800 text-[11px]">GATE IN ANPR <span class="font-mono bg-blue-50 text-blue-700 border border-blue-200 px-1 rounded text-[9.5px]">MSKU9920194</span></span>
                            <span class="text-[9.5px] text-slate-400 font-mono">16:42 WIB</span>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-0.5">Truk B 9812 UIX lolos OCR &bull; Berat kotor 28.450 kg SOLAS VGM Valid.</p>
                        <span class="text-[9px] text-slate-400 block mt-0.5"><i class="fa-solid fa-user-gear text-[8px] mr-1"></i>ANPR OCR Gate 02</span>
                    </div>
                    <div class="relative pl-4">
                        <div class="timeline-dot bg-orange-500 ring-2 ring-white"></div>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-slate-800 text-[11px]">RELOCATION <span class="font-mono bg-orange-50 text-orange-700 border border-orange-200 px-1 rounded text-[9.5px]">EITU9823102</span></span>
                            <span class="text-[9.5px] text-slate-400 font-mono">16:35 WIB</span>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-0.5">Pemindahan ke Blok B (02-04-03) untuk persiapan muat KA 2518.</p>
                        <span class="text-[9px] text-slate-400 block mt-0.5"><i class="fa-solid fa-user-gear text-[8px] mr-1"></i>Kalmar RS-01 (Dani S.)</span>
                    </div>
                    <div class="relative pl-4">
                        <div class="timeline-dot bg-cyan-500 ring-2 ring-white"></div>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-slate-800 text-[11px]">REEFER PLUG IN <span class="font-mono bg-cyan-50 text-cyan-700 border border-cyan-200 px-1 rounded text-[9.5px]">ONEU8812903</span></span>
                            <span class="text-[9.5px] text-slate-400 font-mono">16:18 WIB</span>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-0.5">Steker R04-S22 terhubung daya 380V &bull; Setpoint -40.0°C stabil.</p>
                        <span class="text-[9px] text-slate-400 block mt-0.5"><i class="fa-solid fa-user-gear text-[8px] mr-1"></i>Armansyah M. (Teknisi K3)</span>
                    </div>
                    <div class="relative pl-4">
                        <div class="timeline-dot bg-amber-500 ring-2 ring-white"></div>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-slate-800 text-[11px]">CFS STRIPPING <span class="font-mono bg-amber-50 text-amber-700 border border-amber-200 px-1 rounded text-[9.5px]">TCKU2219401</span></span>
                            <span class="text-[9.5px] text-slate-400 font-mono">15:52 WIB</span>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-0.5">Bongkar muat 24 pallet cokelat impor &bull; GS1 SSCC terbit otomatis.</p>
                        <span class="text-[9px] text-slate-400 block mt-0.5"><i class="fa-solid fa-user-gear text-[8px] mr-1"></i>Forklift FL-02 (Budi T.)</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="pt-2 border-t border-slate-100 text-center mt-2">
                <a href="dashboard.php?page=kontainer" class="text-[10.5px] font-bold text-[#0170b9] hover:underline inline-flex items-center gap-1 transition">
                    <span>Buka Seluruh Riwayat Log SCADA</span>
                    <i class="fa-solid fa-arrow-right text-[9px]"></i>
                </a>
            </div>
        </div>

        <!-- Right: Shipper Consignment Tracking Table (7 Cols) -->
        <div class="xl:col-span-7 bg-white/95 backdrop-blur-xs rounded-xl shadow-2xs border border-slate-200/90 p-3 sm:p-3.5 flex flex-col justify-between relative overflow-hidden">
            <!-- Subtle Luxury Batik Kawung Corner Watermark -->
            <div class="absolute -right-6 -top-6 w-32 h-32 pointer-events-none opacity-[0.04] z-0" aria-hidden="true">
                <svg width="100%" height="100%" viewBox="0 0 100 100" fill="none" stroke="#0170b9" stroke-width="1.6">
                    <ellipse cx="50" cy="25" rx="14" ry="22" />
                    <ellipse cx="50" cy="75" rx="14" ry="22" />
                    <ellipse cx="25" cy="50" rx="22" ry="14" />
                    <ellipse cx="75" cy="50" rx="22" ry="14" />
                    <circle cx="50" cy="50" r="4" fill="#ea580c" />
                </svg>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 mb-2 pb-1.5 border-b border-slate-100 relative z-10">
                <h3 class="font-extrabold text-slate-900 text-xs flex items-center">
                    <i class="fa-solid fa-list-check mr-1.5 text-[#0170b9] text-xs"></i>Pelacakan Konsinyasi Peti Kemas (Shipper Consignments)
                </h3>
                <div class="flex items-center gap-1.5">
                    <span class="text-[8.5px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200 font-mono">
                        <i class="fa-solid fa-wifi mr-0.5 text-[8px]"></i>RFID UHF ISO 18000-6C
                    </span>
                    <a href="dashboard.php?page=kontainer" class="text-[10px] font-bold text-[#0170b9] hover:underline">
                        Semua Data &rarr;
                    </a>
                </div>
            </div>

            <!-- Compact Table -->
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse min-w-[580px]">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[10px] border-y border-slate-200">
                            <th class="py-2 px-2.5 font-bold uppercase">No. Kontainer</th>
                            <th class="py-2 px-2.5 font-bold uppercase">Tipe &amp; ISO</th>
                            <th class="py-2 px-2.5 font-bold uppercase">Tag RFID UHF</th>
                            <th class="py-2 px-2.5 font-bold uppercase">Status</th>
                            <th class="py-2 px-2.5 font-bold uppercase">Posisi Lapangan</th>
                            <th class="py-2 px-2.5 font-bold uppercase text-right">Pembaruan</th>
                        </tr>
                    </thead>
                    <tbody class="text-[11px] divide-y divide-slate-100">
                        <?php if (!empty($db_stats['recent_containers'])): ?>
                            <?php foreach ($db_stats['recent_containers'] as $c): 
                                $blk = trim($c['block'] ?? '');
                                $bay = trim($c['bay'] ?? '');
                                $row = trim($c['row'] ?? '');
                                $tier = trim($c['tier'] ?? '');
                                $pos = !empty($blk) ? ('Blok ' . $blk . (!empty($bay) ? " / $bay-$row-$tier" : '')) : 'Gate Transit Area';
                                $c_status = $c['status'] ?? 'in_yard';
                                $status_badge = 'bg-blue-50 text-blue-700 border-blue-200';
                                $status_label = ucwords(str_replace('_', ' ', $c_status));
                                if ($c_status === 'in_yard') {
                                    $status_badge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                    $status_label = 'In Yard (Stack)';
                                } elseif ($c_status === 'gate_out') {
                                    $status_badge = 'bg-slate-100 text-slate-700 border-slate-200';
                                    $status_label = 'Gate-Out (Rilis)';
                                } elseif ($c_status === 'gate_in') {
                                    $status_badge = 'bg-blue-50 text-blue-700 border-blue-200';
                                    $status_label = 'Gate-In (Antrean)';
                                }
                                $time_disp = !empty($c['created_at']) ? date('H:i', strtotime($c['created_at'])) . ' WIB' : date('H:i') . ' WIB';
                                $rfid_disp = !empty($c['rfid_tag']) ? htmlspecialchars($c['rfid_tag']) : 'EPC-915-' . substr(md5($c['container_number']), 0, 5);
                                $size_disp = htmlspecialchars(($c['size_type'] ?? '40FT') . (!empty($c['iso_code']) ? ' (' . $c['iso_code'] . ')' : ''));
                            ?>
                            <tr class="hover:bg-blue-50/20 transition">
                                <td class="py-2 px-2.5 font-mono font-bold text-[#0170b9]">
                                    <a href="dashboard.php?page=kontainer&search=<?= urlencode($c['container_number']) ?>" class="hover:underline flex items-center gap-1">
                                        <?= htmlspecialchars($c['container_number']) ?>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[8px] text-slate-400"></i>
                                    </a>
                                </td>
                                <td class="py-2 px-2.5 text-slate-600"><?= $size_disp ?></td>
                                <td class="py-2 px-2.5 font-mono text-[9.5px] text-purple-700 font-semibold"><?= $rfid_disp ?></td>
                                <td class="py-2 px-2.5"><span class="<?= $status_badge ?> border px-1.5 py-0.5 rounded text-[9.5px] font-bold"><?= $status_label ?></span></td>
                                <td class="py-2 px-2.5 font-semibold text-slate-800"><?= htmlspecialchars($pos) ?></td>
                                <td class="py-2 px-2.5 text-slate-400 text-right text-[10px] font-mono"><?= $time_disp ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Realistic Default Table Rows -->
                            <tr class="hover:bg-blue-50/20 transition">
                                <td class="py-2 px-2.5 font-mono font-bold text-[#0170b9]">
                                    <a href="dashboard.php?page=kontainer&search=MSKU9920194" class="hover:underline flex items-center gap-1">
                                        MSKU9920194 <i class="fa-solid fa-arrow-up-right-from-square text-[8px] text-slate-400"></i>
                                    </a>
                                </td>
                                <td class="py-2 px-2.5 text-slate-600">40FT HC (45R1)</td>
                                <td class="py-2 px-2.5 font-mono text-[9.5px] text-purple-700 font-semibold">EPC-915-A109</td>
                                <td class="py-2 px-2.5"><span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-1.5 py-0.5 rounded text-[9.5px] font-bold">In Yard (Stack)</span></td>
                                <td class="py-2 px-2.5 font-semibold text-slate-800">Blok A / 02-04-03</td>
                                <td class="py-2 px-2.5 text-slate-400 text-right text-[10px] font-mono">16:42 WIB</td>
                            </tr>
                            <tr class="hover:bg-blue-50/20 transition">
                                <td class="py-2 px-2.5 font-mono font-bold text-[#0170b9]">
                                    <a href="dashboard.php?page=kontainer&search=EITU9823102" class="hover:underline flex items-center gap-1">
                                        EITU9823102 <i class="fa-solid fa-arrow-up-right-from-square text-[8px] text-slate-400"></i>
                                    </a>
                                </td>
                                <td class="py-2 px-2.5 text-slate-600">40FT HC (45R1)</td>
                                <td class="py-2 px-2.5 font-mono text-[9.5px] text-purple-700 font-semibold">EPC-915-E842</td>
                                <td class="py-2 px-2.5"><span class="bg-blue-50 text-blue-700 border border-blue-200 px-1.5 py-0.5 rounded text-[9.5px] font-bold">Siding KA Siaga</span></td>
                                <td class="py-2 px-2.5 font-semibold text-slate-800">Blok B / 01-02-02</td>
                                <td class="py-2 px-2.5 text-slate-400 text-right text-[10px] font-mono">16:35 WIB</td>
                            </tr>
                            <tr class="hover:bg-blue-50/20 transition">
                                <td class="py-2 px-2.5 font-mono font-bold text-[#0170b9]">
                                    <a href="dashboard.php?page=kontainer&search=ONEU8812903" class="hover:underline flex items-center gap-1">
                                        ONEU8812903 <i class="fa-solid fa-arrow-up-right-from-square text-[8px] text-slate-400"></i>
                                    </a>
                                </td>
                                <td class="py-2 px-2.5 text-slate-600">40FT RF (45R1)</td>
                                <td class="py-2 px-2.5 font-mono text-[9.5px] text-purple-700 font-semibold">EPC-915-C339</td>
                                <td class="py-2 px-2.5"><span class="bg-cyan-50 text-cyan-700 border border-cyan-200 px-1.5 py-0.5 rounded text-[9.5px] font-bold">Cold Chain Aktif</span></td>
                                <td class="py-2 px-2.5 font-semibold text-slate-800">Reefer / R04-S22</td>
                                <td class="py-2 px-2.5 text-slate-400 text-right text-[10px] font-mono">16:18 WIB</td>
                            </tr>
                            <tr class="hover:bg-blue-50/20 transition">
                                <td class="py-2 px-2.5 font-mono font-bold text-[#0170b9]">
                                    <a href="dashboard.php?page=kontainer&search=TCKU2219401" class="hover:underline flex items-center gap-1">
                                        TCKU2219401 <i class="fa-solid fa-arrow-up-right-from-square text-[8px] text-slate-400"></i>
                                    </a>
                                </td>
                                <td class="py-2 px-2.5 text-slate-600">20FT GP (22G1)</td>
                                <td class="py-2 px-2.5 font-mono text-[9.5px] text-purple-700 font-semibold">EPC-915-F910</td>
                                <td class="py-2 px-2.5"><span class="bg-amber-50 text-amber-700 border border-amber-200 px-1.5 py-0.5 rounded text-[9.5px] font-bold">CFS Stripping</span></td>
                                <td class="py-2 px-2.5 font-semibold text-slate-800">CFS / Rampa D-02</td>
                                <td class="py-2 px-2.5 text-slate-400 text-right text-[10px] font-mono">15:52 WIB</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-500 mt-2">
                <span>Total Data Terpantau: <strong><?= number_format($containers_yard) ?> Peti Kemas</strong></span>
                <span class="text-[#0170b9] font-bold">ISO 6346 Validasi Aktif</span>
            </div>
        </div>

    </div>

    <!-- ======================================================================= -->
    <!-- 7. HOST-TO-HOST INTEGRATION STATUS BAR (FULL WIDTH)                    -->
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
            <!-- Pill 1: CEISA 4.0 -->
            <div class="p-2 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-between">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse flex-shrink-0"></span>
                    <div class="truncate">
                        <div class="font-bold text-slate-900 text-[11px] truncate">Bea Cukai CEISA 4.0</div>
                        <div class="text-[9px] text-slate-400 truncate">REST API &bull; SPPB Auto</div>
                    </div>
                </div>
                <span class="text-[8.5px] font-mono font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.2 rounded flex-shrink-0">42ms</span>
            </div>

            <!-- Pill 2: Inaportnet Kemenhub -->
            <div class="p-2 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-between">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse flex-shrink-0"></span>
                    <div class="truncate">
                        <div class="font-bold text-slate-900 text-[11px] truncate">Inaportnet Kemenhub V2</div>
                        <div class="text-[9px] text-slate-400 truncate">NLE Manifest Sinkron</div>
                    </div>
                </div>
                <span class="text-[8.5px] font-mono font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.2 rounded flex-shrink-0">58ms</span>
            </div>

            <!-- Pill 3: KAI Logistik EDI -->
            <div class="p-2 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-between">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse flex-shrink-0"></span>
                    <div class="truncate">
                        <div class="font-bold text-slate-900 text-[11px] truncate">KAI Logistik EDI</div>
                        <div class="text-[9px] text-slate-400 truncate">Jadwal Siding KA 2518</div>
                    </div>
                </div>
                <span class="text-[8.5px] font-mono font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.2 rounded flex-shrink-0">35ms</span>
            </div>

            <!-- Pill 4: Marechal IoT LoRaWAN -->
            <div class="p-2 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-between">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse flex-shrink-0"></span>
                    <div class="truncate">
                        <div class="font-bold text-slate-900 text-[11px] truncate">Marechal IoT LoRaWAN</div>
                        <div class="text-[9px] text-slate-400 truncate">868MHz 300 Plugs Telemetri</div>
                    </div>
                </div>
                <span class="text-[8.5px] font-mono font-bold text-cyan-700 bg-cyan-50 border border-cyan-200 px-1.5 py-0.2 rounded flex-shrink-0">110ms</span>
            </div>

            <!-- Pill 5: GS1 SSCC Decoder -->
            <div class="p-2 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-between">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-purple-500 animate-pulse flex-shrink-0"></span>
                    <div class="truncate">
                        <div class="font-bold text-slate-900 text-[11px] truncate">GS1 SSCC AI Station</div>
                        <div class="text-[9px] text-slate-400 truncate">Optical Vision 99.8%</div>
                    </div>
                </div>
                <span class="text-[8.5px] font-mono font-bold text-purple-700 bg-purple-50 border border-purple-200 px-1.5 py-0.2 rounded flex-shrink-0">ONLINE</span>
            </div>
        </div>
    </div>
    </div>
</div>

<!-- =========================================================================== -->
<!-- CHART.JS INITIALIZATION SCRIPT (ULTRA HIGH-DENSITY ENTERPRISE ENGINE)       -->
<!-- =========================================================================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 0. Live Digital Clock Ticker
    function updateBerandaLiveClock() {
        const el = document.getElementById('berandaLiveClock');
        if (!el) return;
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        el.innerText = h + ':' + m + ':' + s + ' WIB';
    }
    setInterval(updateBerandaLiveClock, 1000);
    updateBerandaLiveClock();

    if (typeof Chart === 'undefined') {
        console.warn('Chart.js library is not loaded');
        return;
    }

    // Konfigurasi Global Chart.js (Clean High-Contrast Light Theme)
    Chart.defaults.font.family = '"Plus Jakarta Sans", sans-serif';
    Chart.defaults.font.size = 9.5;
    Chart.defaults.color = '#64748b';
    Chart.defaults.plugins.tooltip.backgroundColor = '#0f172a';
    Chart.defaults.plugins.tooltip.padding = 7;
    Chart.defaults.plugins.tooltip.cornerRadius = 6;
    Chart.defaults.plugins.tooltip.titleFont = { size: 10.5, weight: 'bold' };
    Chart.defaults.plugins.tooltip.bodyFont = { size: 9.5 };

    // -------------------------------------------------------------------------
    // 1. Dual Smooth Area Chart: Gate Throughput vs VBS Hourly Demand
    // -------------------------------------------------------------------------
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

    // -------------------------------------------------------------------------
    // 2. Heavy Equipment SCADA OEE: 5-Axis Pentagon Spider Radar Chart
    // -------------------------------------------------------------------------
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

    // -------------------------------------------------------------------------
    // 3. Container Dwelling Time & Demurrage Risk: Tiered Vertical Bar Chart
    // -------------------------------------------------------------------------
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
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) { return ' ' + ctx.raw + '% dari total kontainer'; }
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

    // -------------------------------------------------------------------------
    // 4. Multimodal Modal-Split & ESG: Segmented Donut Chart
    // -------------------------------------------------------------------------
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

    // -------------------------------------------------------------------------
    // 5. Weekly Revenue Breakdown: Stacked Bar Chart (Storage, Lo-Lo, Reefer, VGM)
    // -------------------------------------------------------------------------
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
