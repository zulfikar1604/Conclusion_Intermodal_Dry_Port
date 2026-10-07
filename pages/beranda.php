<?php
// =============================================================================
// FILE: pages/beranda.php
// FUNGSI: Beranda Eksekutif & Telemetri Terpadu Dry Port (Original View)
// =============================================================================
?>
                <!-- Executive Cockpit Header & Real-Time Status -->
                <div class="bg-white rounded-lg px-4 py-3.5 shadow-2xs border border-[#E4DFD3] flex flex-col md:flex-row items-start md:items-center justify-between gap-2.5 transition-all">
                    <div class="flex items-center gap-3.5">
                        <div class="w-9 h-9 rounded-md bg-ink text-brass flex items-center justify-center text-sm flex-shrink-0">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5">
                                <h2 class="text-sm sm:text-[15px] font-semibold text-ink tracking-[0.005em] leading-tight">
                                    Konsol Eksekutif &amp; Telemetri Operasional Dry Port
                                </h2>
                                <span class="text-[9px] font-medium tracking-[0.16em] uppercase text-[#5E7A6B] flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#5E7A6B]"></span>
                                    Live 35 Ha
                                </span>
                            </div>
                            <p class="text-ink/50 text-[11px] mt-0.5">Simulasi Inland Container Depot (ICD), Siding KA Daop 1, &amp; Pabean CEISA 4.0</p>
                        </div>
                    </div>
                    <!-- Micro Telemetry Badges & Real-Time Clock -->
                    <div class="flex items-center gap-4 flex-wrap text-[10.5px] text-ink/60">
                        <div class="flex items-center gap-1.5">
                            <i class="fa-regular fa-calendar text-brass-deep text-[10px]"></i>
                            <span class="font-medium"><?= $tanggal_sekarang ?></span>
                            <span class="text-ink/20">&bull;</span>
                            <i class="fa-solid fa-clock text-brass-deep text-[9.5px]"></i>
                            <span id="berandaLiveClock" class="font-semibold text-ink font-mono">--:--:-- WIB</span>
                        </div>
                        <div class="flex items-center gap-1.5 pl-4 border-l border-[#E4DFD3]">
                            <i class="fa-solid fa-satellite-dish text-[9px] text-brass-deep"></i>
                            <span class="font-medium">SCADA <span class="text-ink font-semibold">99.8%</span></span>
                        </div>
                        <div class="flex items-center gap-1.5 pl-4 border-l border-[#E4DFD3]">
                            <i class="fa-solid fa-train-subway text-[9px] text-brass-deep"></i>
                            <span class="font-medium">Siding KA <span class="text-ink font-semibold">Ready</span></span>
                        </div>
                    </div>
                </div>

                <!-- Live IoT Sensor & Field Workbench Status Bar (Direct-Launch Shortcuts) -->
                <div class="bg-ink rounded-lg px-4 py-3.5 shadow-sm border border-white/[0.06] text-paper">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-3 pb-3 border-b border-white/[0.08]">
                        <div class="flex items-center gap-2.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#8FA89A]"></span>
                            <h3 class="text-[12.5px] sm:text-[13px] font-semibold tracking-[0.02em] text-paper">
                                Pusat Kendali Sensor &amp; IoT Workbench Lapangan
                                <span class="font-normal text-paper/45 ml-1">Live Operational Sync</span>
                            </h3>
                        </div>
                        <span class="text-[10px] text-paper/45 tracking-[0.06em] flex items-center gap-1.5">
                            <i class="fa-solid fa-microchip text-brass/80"></i>
                            <span>26 BOM Hardware Terkalibrasi &middot; Latensi &lt; 150ms</span>
                        </span>
                    </div>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 text-[11px]">
                        <!-- Gateway 1: Gate ANPR -->
                        <a href="dashboard.php?page=gate" class="p-2.5 rounded-md border border-white/[0.08] hover:border-brass/50 hover:bg-white/[0.03] transition-colors flex items-center justify-between gap-2 group">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <i class="fa-solid fa-door-open text-[13px] text-brass/80 w-4 text-center"></i>
                                <div class="min-w-0">
                                    <div class="font-semibold text-paper text-[11.5px] truncate">Gate &amp; Timbangan</div>
                                    <div class="text-[10px] text-paper/45 truncate">ANPR OCR + 80T Solas</div>
                                </div>
                            </div>
                            <span class="text-[9px] font-medium tracking-[0.14em] uppercase text-[#8FA89A] flex items-center gap-1.5 flex-shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#8FA89A]"></span>Online
                            </span>
                        </a>

                        <!-- Gateway 2: CFS Rampa D1-D5 -->
                        <a href="dashboard.php?page=cfs" class="p-2.5 rounded-md border border-white/[0.08] hover:border-brass/50 hover:bg-white/[0.03] transition-colors flex items-center justify-between gap-2 group">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <i class="fa-solid fa-warehouse text-[13px] text-brass/80 w-4 text-center"></i>
                                <div class="min-w-0">
                                    <div class="font-semibold text-paper text-[11.5px] truncate">CFS Rampa D1-D5</div>
                                    <div class="text-[10px] text-paper/45 truncate">Pallet 3T + Gas HW-19</div>
                                </div>
                            </div>
                            <span class="text-[9px] font-medium tracking-[0.14em] uppercase text-[#8FA89A] flex items-center gap-1.5 flex-shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#8FA89A]"></span>Online
                            </span>
                        </a>

                        <!-- Gateway 3: Reefer Terminal -->
                        <a href="dashboard.php?page=reefer" class="p-2.5 rounded-md border border-white/[0.08] hover:border-brass/50 hover:bg-white/[0.03] transition-colors flex items-center justify-between gap-2 group">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <i class="fa-solid fa-snowflake text-[13px] text-brass/80 w-4 text-center"></i>
                                <div class="min-w-0">
                                    <div class="font-semibold text-paper text-[11.5px] truncate">Reefer Cold Chain</div>
                                    <div class="text-[10px] text-paper/45 truncate">300 Plug + SHT40 LoRa</div>
                                </div>
                            </div>
                            <span class="text-[9px] font-medium tracking-[0.14em] uppercase text-[#9DB0C4] flex items-center gap-1.5 flex-shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#9DB0C4]"></span>Pulse
                            </span>
                        </a>

                        <!-- Gateway 4: Denah Terminal 35 Ha -->
                        <a href="dashboard.php?page=denah" class="p-2.5 rounded-md border border-white/[0.08] hover:border-brass/50 hover:bg-white/[0.03] transition-colors flex items-center justify-between gap-2 group">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <i class="fa-solid fa-map-location-dot text-[13px] text-brass/80 w-4 text-center"></i>
                                <div class="min-w-0">
                                    <div class="font-semibold text-paper text-[11.5px] truncate">Denah Terminal 35 Ha</div>
                                    <div class="text-[10px] text-paper/45 truncate">26 Hardware BOM Map</div>
                                </div>
                            </div>
                            <span class="text-[9px] font-medium tracking-[0.14em] uppercase text-brass flex items-center gap-1.5 flex-shrink-0">
                                Uji Real <i class="fa-solid fa-arrow-up-right-from-square text-[8px]"></i>
                            </span>
                        </a>
                    </div>
                </div>

                <!-- 6 Compact KPI Summary Cards (1-Row Grid on Desktop, Symmetrical 2x3 Grid on Mobile) -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-3">
                    
                    <!-- KPI 1: Okupansi Yard -->
                    <div class="bg-white rounded-xl p-2.5 sm:p-3 shadow-2xs border border-gray-200/80 hover:border-blue-300 hover:shadow-xs transition-all group flex flex-col justify-between">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[9.5px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">Yard Stack</p>
                                <h3 class="text-lg sm:text-xl font-extrabold text-gray-900 mt-0.5 leading-none"><?= $db_stats['containers_in_yard'] ?> <span class="text-[10px] font-normal text-gray-400">box</span></h3>
                            </div>
                            <div class="w-6 h-6 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                                <i class="fa-solid fa-boxes-stacked"></i>
                            </div>
                        </div>
                        <div class="mt-2 space-y-1">
                            <?php $yard_pct = min(100, max(5, round(($db_stats['containers_in_yard'] / 25) * 100))); ?>
                            <div class="w-full bg-gray-100 rounded-full h-1 overflow-hidden">
                                <div class="bg-blue-600 h-1 rounded-full" style="width: <?= $yard_pct ?>%"></div>
                            </div>
                            <div class="flex justify-between items-center text-[9px] text-gray-500">
                                <span class="font-bold text-blue-600"><?= $yard_pct ?>% Terisi</span>
                                <span class="text-emerald-600 font-semibold">+<?= max(1, $db_stats['containers_in_yard']) ?> box</span>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 2: Gate Turnaround Time -->
                    <div class="bg-white rounded-xl p-2.5 sm:p-3 shadow-2xs border border-gray-200/80 hover:border-indigo-300 hover:shadow-xs transition-all group flex flex-col justify-between">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[9.5px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">Gate TAT</p>
                                <h3 class="text-lg sm:text-xl font-extrabold text-gray-900 mt-0.5 leading-none">18.2 <span class="text-[10px] font-normal text-gray-400">mnt</span></h3>
                            </div>
                            <div class="w-6 h-6 rounded-md bg-indigo-50 text-indigo-600 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                                <i class="fa-solid fa-truck-fast"></i>
                            </div>
                        </div>
                        <div class="mt-2 space-y-1">
                            <div class="w-full bg-gray-100 rounded-full h-1 overflow-hidden">
                                <div class="bg-indigo-600 h-1 rounded-full" style="width: 72%"></div>
                            </div>
                            <div class="flex justify-between items-center text-[9px] text-gray-500">
                                <span class="text-indigo-600 font-semibold"><?= $db_stats['total_gate_trucks'] ?> Truk Aktif</span>
                                <span class="text-emerald-600 font-bold">On-Time 98%</span>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 3: Kereta Api Logistik -->
                    <div class="bg-white rounded-xl p-2.5 sm:p-3 shadow-2xs border border-gray-200/80 hover:border-purple-300 hover:shadow-xs transition-all group flex flex-col justify-between">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[9.5px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">Siding KA</p>
                                <h3 class="text-lg sm:text-xl font-extrabold text-gray-900 mt-0.5 leading-none"><?= $db_stats['loaded_wagons'] ?>/<?= $db_stats['total_wagons'] ?> <span class="text-[10px] font-normal text-gray-400">Wg</span></h3>
                            </div>
                            <div class="w-6 h-6 rounded-md bg-purple-50 text-purple-600 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                                <i class="fa-solid fa-train"></i>
                            </div>
                        </div>
                        <div class="mt-2 space-y-1">
                            <?php $train_pct = $db_stats['total_wagons'] > 0 ? round(($db_stats['loaded_wagons'] / $db_stats['total_wagons']) * 100) : 80; ?>
                            <div class="w-full bg-gray-100 rounded-full h-1 overflow-hidden">
                                <div class="bg-purple-600 h-1 rounded-full" style="width: <?= $train_pct ?>%"></div>
                            </div>
                            <div class="flex justify-between items-center text-[9px] text-gray-500">
                                <span class="text-purple-600 font-bold">KA 2518 (<?= $train_pct ?>%)</span>
                                <span class="text-gray-400">16:45 Dep</span>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 4: Reefer Cold Chain -->
                    <div class="bg-white rounded-xl p-2.5 sm:p-3 shadow-2xs border border-gray-200/80 hover:border-cyan-300 hover:shadow-xs transition-all group flex flex-col justify-between">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[9.5px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">Reefer Plug</p>
                                <h3 class="text-lg sm:text-xl font-extrabold text-cyan-700 mt-0.5 leading-none">-20.2<span class="text-[10px] font-normal text-gray-400">&deg;C</span></h3>
                            </div>
                            <div class="w-6 h-6 rounded-md bg-cyan-50 text-cyan-600 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                                <i class="fa-solid fa-snowflake"></i>
                            </div>
                        </div>
                        <div class="mt-2 space-y-1">
                            <?php $reefer_pct = min(100, max(5, round(($db_stats['reefer_count'] / 10) * 100))); ?>
                            <div class="w-full bg-gray-100 rounded-full h-1 overflow-hidden">
                                <div class="bg-cyan-500 h-1 rounded-full" style="width: <?= $reefer_pct ?>%"></div>
                            </div>
                            <div class="flex justify-between items-center text-[9px] text-gray-500">
                                <span class="text-cyan-700 font-bold"><?= $db_stats['reefer_count'] ?>/300 Plug</span>
                                <span class="text-emerald-600 font-semibold">Marechal 380V</span>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 5: Bea Cukai CEISA -->
                    <div class="bg-white rounded-xl p-2.5 sm:p-3 shadow-2xs border border-gray-200/80 hover:border-emerald-300 hover:shadow-xs transition-all group flex flex-col justify-between">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[9.5px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">Bea Cukai</p>
                                <h3 class="text-lg sm:text-xl font-extrabold text-emerald-700 mt-0.5 leading-none"><?= $db_stats['customs_pct'] ?><span class="text-[10px] font-normal text-gray-400">%</span></h3>
                            </div>
                            <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                        </div>
                        <div class="mt-2 space-y-1">
                            <div class="w-full bg-gray-100 rounded-full h-1 overflow-hidden">
                                <div class="bg-emerald-500 h-1 rounded-full" style="width: <?= min(100, (int)$db_stats['customs_pct']) ?>%"></div>
                            </div>
                            <div class="flex justify-between items-center text-[9px] text-gray-500">
                                <span class="text-emerald-700 font-bold">SPPB Cleared (<?= $db_stats['customs_cleared'] ?>/<?= $db_stats['customs_total'] ?>)</span>
                                <span class="text-gray-400">CEISA 4.0</span>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 6: CFS & Billing Operasional -->
                    <div class="bg-white rounded-xl p-2.5 sm:p-3 shadow-2xs border border-gray-200/80 hover:border-indigo-300 hover:shadow-xs transition-all group flex flex-col justify-between">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[9.5px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">CFS &amp; Billing</p>
                                <h3 class="text-lg sm:text-xl font-extrabold text-indigo-700 mt-0.5 leading-none"><?= $db_stats['cfs_active_jobs'] ?> <span class="text-[10px] font-normal text-gray-400">Jobs</span></h3>
                            </div>
                            <div class="w-6 h-6 rounded-md bg-indigo-50 text-indigo-600 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                            </div>
                        </div>
                        <div class="mt-2 space-y-1">
                            <div class="w-full bg-gray-100 rounded-full h-1 overflow-hidden">
                                <div class="bg-indigo-600 h-1 rounded-full" style="width: 85%"></div>
                            </div>
                            <div class="flex justify-between items-center text-[9px] text-gray-500">
                                <span class="text-indigo-700 font-bold">Rampa D1-D5 Aktif</span>
                                <span class="text-emerald-600 font-semibold">Rp <?= number_format($db_stats['billing_paid'] / 1000000, 1) ?>Jt Paid</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Visual End-to-End Operational Logistics Conveyor Pipeline (Anti-Pusing / Visual Alur) -->
                <div class="bg-white rounded-xl p-3 sm:p-4 shadow-2xs border border-gray-200/80">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 mb-2.5 pb-2 border-b border-gray-100">
                        <div>
                            <h3 class="font-bold text-gray-800 text-xs sm:text-sm flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                                <i class="fa-solid fa-arrows-split-up-and-left text-cdp-blue text-sm"></i>
                                <span>Alur Logistik End-to-End Intermodal Dry Port (Visual Pipeline 35 Ha)</span>
                            </h3>
                            <p class="text-[10.5px] text-gray-400 mt-0.5">Pandangan visual interaktif alur kargo dari pintu gerbang hingga kereta intermodal &amp; pelabuhan.</p>
                        </div>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200 self-start sm:self-auto flex items-center gap-1">
                            <i class="fa-solid fa-circle-check text-[9px]"></i> Alur Logistik Terbuka Penuh
                        </span>
                    </div>

                    <!-- 6-Stage Interactive Visual Flow Ribbon -->
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2">
                        <!-- Stage 1: Gate-In & ANPR -->
                        <a href="dashboard.php?page=gate" class="group p-2.5 rounded-lg border border-amber-200 bg-amber-50/40 hover:bg-amber-100/50 hover:border-amber-300 transition flex flex-col justify-between relative overflow-hidden">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="w-5 h-5 rounded-md bg-amber-500 text-white flex items-center justify-center text-[10px] font-bold font-mono">1</span>
                                <span class="text-[8.5px] font-bold text-amber-700 bg-amber-100/80 px-1 py-0.2 rounded uppercase">Gate-In</span>
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-[11px] font-bold text-gray-800 group-hover:text-amber-800 transition flex items-center gap-1">
                                    <i class="fa-solid fa-camera text-amber-600 text-[10px]"></i>
                                    <span>ANPR &amp; RFID OCR</span>
                                </div>
                                <p class="text-[9.5px] text-gray-500">Scan plat &amp; RFID box otomatis</p>
                            </div>
                            <div class="mt-2 pt-1 border-t border-amber-200/60 flex items-center justify-between text-[9px] text-amber-900 font-semibold">
                                <span>Akurasi: 99.4%</span>
                                <i class="fa-solid fa-arrow-right text-[8px] text-amber-500 group-hover:translate-x-0.5 transition-transform"></i>
                            </div>
                        </a>

                        <!-- Stage 2: Weighbridge SOLAS VGM -->
                        <a href="dashboard.php?page=gate" class="group p-2.5 rounded-lg border border-purple-200 bg-purple-50/40 hover:bg-purple-100/50 hover:border-purple-300 transition flex flex-col justify-between relative overflow-hidden">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="w-5 h-5 rounded-md bg-purple-600 text-white flex items-center justify-center text-[10px] font-bold font-mono">2</span>
                                <span class="text-[8.5px] font-bold text-purple-700 bg-purple-100/80 px-1 py-0.2 rounded uppercase">Timbang</span>
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-[11px] font-bold text-gray-800 group-hover:text-purple-800 transition flex items-center gap-1">
                                    <i class="fa-solid fa-weight-scale text-purple-600 text-[10px]"></i>
                                    <span>Jembatan 80 Ton</span>
                                </div>
                                <p class="text-[9.5px] text-gray-500">SOLAS VGM IMO Rule</p>
                            </div>
                            <div class="mt-2 pt-1 border-t border-purple-200/60 flex items-center justify-between text-[9px] text-purple-900 font-semibold">
                                <span>Toleransi: &plusmn;0.2%</span>
                                <i class="fa-solid fa-arrow-right text-[8px] text-purple-500 group-hover:translate-x-0.5 transition-transform"></i>
                            </div>
                        </a>

                        <!-- Stage 3: Yard Stacking & Lift-Off -->
                        <a href="dashboard.php?page=yard" class="group p-2.5 rounded-lg border border-blue-200 bg-blue-50/40 hover:bg-blue-100/50 hover:border-blue-300 transition flex flex-col justify-between relative overflow-hidden">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="w-5 h-5 rounded-md bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold font-mono">3</span>
                                <span class="text-[8.5px] font-bold text-blue-700 bg-blue-100/80 px-1 py-0.2 rounded uppercase">Penataan</span>
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-[11px] font-bold text-gray-800 group-hover:text-blue-800 transition flex items-center gap-1">
                                    <i class="fa-solid fa-cubes text-blue-600 text-[10px]"></i>
                                    <span>Yard Blok A/B/C</span>
                                </div>
                                <p class="text-[9.5px] text-gray-500">Reach Stacker Kalmar 45T</p>
                            </div>
                            <div class="mt-2 pt-1 border-t border-blue-200/60 flex items-center justify-between text-[9px] text-blue-900 font-semibold">
                                <span><?= $db_stats['containers_in_yard'] ?> Box Stack</span>
                                <i class="fa-solid fa-arrow-right text-[8px] text-blue-500 group-hover:translate-x-0.5 transition-transform"></i>
                            </div>
                        </a>

                        <!-- Stage 4: CFS Stuffing / Stripping -->
                        <a href="dashboard.php?page=cfs" class="group p-2.5 rounded-lg border border-indigo-200 bg-indigo-50/40 hover:bg-indigo-100/50 hover:border-indigo-300 transition flex flex-col justify-between relative overflow-hidden">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="w-5 h-5 rounded-md bg-indigo-600 text-white flex items-center justify-center text-[10px] font-bold font-mono">4</span>
                                <span class="text-[8.5px] font-bold text-indigo-700 bg-indigo-100/80 px-1 py-0.2 rounded uppercase">CFS Gudang</span>
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-[11px] font-bold text-gray-800 group-hover:text-indigo-800 transition flex items-center gap-1">
                                    <i class="fa-solid fa-warehouse text-indigo-600 text-[10px]"></i>
                                    <span>Rampa D1-D5 LCL</span>
                                </div>
                                <p class="text-[9.5px] text-gray-500">Pallet 3T + Gas HW-19</p>
                            </div>
                            <div class="mt-2 pt-1 border-t border-indigo-200/60 flex items-center justify-between text-[9px] text-indigo-900 font-semibold">
                                <span><?= $db_stats['cfs_active_jobs'] ?> Jobs Aktif</span>
                                <i class="fa-solid fa-arrow-right text-[8px] text-indigo-500 group-hover:translate-x-0.5 transition-transform"></i>
                            </div>
                        </a>

                        <!-- Stage 5: Reefer & Cold Chain -->
                        <a href="dashboard.php?page=reefer" class="group p-2.5 rounded-lg border border-cyan-200 bg-cyan-50/40 hover:bg-cyan-100/50 hover:border-cyan-300 transition flex flex-col justify-between relative overflow-hidden">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="w-5 h-5 rounded-md bg-cyan-600 text-white flex items-center justify-center text-[10px] font-bold font-mono">5</span>
                                <span class="text-[8.5px] font-bold text-cyan-700 bg-cyan-100/80 px-1 py-0.2 rounded uppercase">Cold Chain</span>
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-[11px] font-bold text-gray-800 group-hover:text-cyan-800 transition flex items-center gap-1">
                                    <i class="fa-solid fa-snowflake text-cyan-600 text-[10px]"></i>
                                    <span>300 Marechal Plugs</span>
                                </div>
                                <p class="text-[9.5px] text-gray-500">SHT40 LoRa &bull; CEISA SPPB</p>
                            </div>
                            <div class="mt-2 pt-1 border-t border-cyan-200/60 flex items-center justify-between text-[9px] text-cyan-900 font-semibold">
                                <span>-20.2&deg;C Stabil</span>
                                <i class="fa-solid fa-arrow-right text-[8px] text-cyan-500 group-hover:translate-x-0.5 transition-transform"></i>
                            </div>
                        </a>

                        <!-- Stage 6: Intermodal Rail Siding KA -->
                        <a href="dashboard.php?page=intermodal" class="group p-2.5 rounded-lg border border-emerald-200 bg-emerald-50/40 hover:bg-emerald-100/50 hover:border-emerald-300 transition flex flex-col justify-between relative overflow-hidden">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="w-5 h-5 rounded-md bg-emerald-600 text-white flex items-center justify-center text-[10px] font-bold font-mono">6</span>
                                <span class="text-[8.5px] font-bold text-emerald-700 bg-emerald-100/80 px-1 py-0.2 rounded uppercase">Intermodal</span>
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-[11px] font-bold text-gray-800 group-hover:text-emerald-800 transition flex items-center gap-1">
                                    <i class="fa-solid fa-train-subway text-emerald-600 text-[10px]"></i>
                                    <span>KA 2518 ke Priok</span>
                                </div>
                                <p class="text-[9.5px] text-gray-500">Track S-01 (54.8 km Siding)</p>
                            </div>
                            <div class="mt-2 pt-1 border-t border-emerald-200/60 flex items-center justify-between text-[9px] text-emerald-900 font-semibold">
                                <span><?= $db_stats['loaded_wagons'] ?>/<?= $db_stats['total_wagons'] ?> Gerbong</span>
                                <i class="fa-solid fa-arrow-right text-[8px] text-emerald-500 group-hover:translate-x-0.5 transition-transform"></i>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Row 1: Core Flow & Yard Heatmap (Equal 3-Column Cockpit) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-3.5">
                    
                    <!-- Col 1: Gate In/Out Throughput Line Chart -->
                    <div class="bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3 sm:p-3.5 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2 pb-1.5 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center">
                                <i class="fa-solid fa-chart-line mr-1.5 text-cdp-blue text-xs"></i>Throughput Gate (7 Hari)
                            </h3>
                            <span class="text-[9px] font-bold text-gray-400 bg-gray-50 px-1.5 py-0.5 rounded">In vs Out</span>
                        </div>
                        <!-- Compact Chart Container -->
                        <div class="h-36 sm:h-40 relative w-full flex-1">
                            <canvas id="gateTrendChart"></canvas>
                        </div>
                        <!-- Micro Metrics Strip -->
                        <div class="mt-2 pt-1.5 border-t border-gray-50 flex items-center justify-between text-[10px] text-gray-500">
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#0170b9]"></span>In: <strong>139 box</strong></span>
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#4f46e5]"></span>Out: <strong>121 box</strong></span>
                            <span class="text-emerald-600 font-bold">Akumulasi: +18 box</span>
                        </div>
                    </div>

                    <!-- Col 2: Yard Block Capacity Heatmap -->
                    <div class="bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3 sm:p-3.5 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2 pb-1.5 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center">
                                <i class="fa-solid fa-cubes-stacked mr-1.5 text-cdp-blue text-xs"></i>Kapasitas Blok Yard Real-Time
                            </h3>
                            <span class="text-[9px] font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200"><?= $db_stats['containers_in_yard'] ?> Box di Yard</span>
                        </div>
                        <!-- Visual Micro Block Heatmap Meters -->
                        <?php 
                            $cnt_a = $db_stats['blocks']['A'] ?? 8;
                            $pct_a = min(100, round(($cnt_a / 20) * 100));
                            $cnt_b = $db_stats['blocks']['B'] ?? 7;
                            $pct_b = min(100, round(($cnt_b / 20) * 100));
                            $cnt_c = $db_stats['blocks']['C'] ?? 2;
                            $pct_c = min(100, round(($cnt_c / 20) * 100));
                            $cnt_rf = $db_stats['blocks']['REEFER'] ?? ($db_stats['blocks']['R'] ?? $db_stats['reefer_count']);
                            $pct_rf = min(100, round(($cnt_rf / 10) * 100));
                        ?>
                        <div class="space-y-2 py-1 flex-1 flex flex-col justify-around">
                            <!-- Blok A -->
                            <div>
                                <div class="flex justify-between text-[10.5px] mb-0.5">
                                    <span class="font-semibold text-gray-700 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>Blok A (Dry 40ft Impor)
                                    </span>
                                    <span class="font-bold text-gray-800"><?= $pct_a ?>% <span class="text-[9px] text-gray-400 font-normal">(<?= $cnt_a ?> box)</span></span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: <?= $pct_a ?>%"></div>
                                </div>
                            </div>
                            <!-- Blok B -->
                            <div>
                                <div class="flex justify-between text-[10.5px] mb-0.5">
                                    <span class="font-semibold text-gray-700 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>Blok B (Dry 20ft Ekspor)
                                    </span>
                                    <span class="font-bold text-gray-800"><?= $pct_b ?>% <span class="text-[9px] text-gray-400 font-normal">(<?= $cnt_b ?> box)</span></span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: <?= $pct_b ?>%"></div>
                                </div>
                            </div>
                            <!-- Blok C -->
                            <div>
                                <div class="flex justify-between text-[10.5px] mb-0.5">
                                    <span class="font-semibold text-gray-700 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>Blok C (Buffer &amp; Domestik)
                                    </span>
                                    <span class="font-bold text-gray-800"><?= $pct_c ?>% <span class="text-[9px] text-gray-400 font-normal">(<?= $cnt_c ?> box)</span></span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-indigo-500 h-1.5 rounded-full" style="width: <?= $pct_c ?>%"></div>
                                </div>
                            </div>
                            <!-- Reefer Zone -->
                            <div>
                                <div class="flex justify-between text-[10.5px] mb-0.5">
                                    <span class="font-semibold text-gray-700 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>Reefer Cold Chain (Plugs)
                                    </span>
                                    <span class="font-bold text-cyan-700"><?= $pct_rf ?>% <span class="text-[9px] text-gray-400 font-normal">(<?= $cnt_rf ?> box aktif)</span></span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-cyan-500 h-1.5 rounded-full" style="width: <?= $pct_rf ?>%"></div>
                                </div>
                            </div>
                            <!-- DG IMO & Empty Depot -->
                            <div class="grid grid-cols-2 gap-2 pt-0.5">
                                <div>
                                    <div class="flex justify-between text-[9.5px] mb-0.5">
                                        <span class="font-medium text-gray-600">DG / IMO Yard</span>
                                        <span class="font-bold text-amber-600"><?= $db_stats['blocks']['DG'] ?? 0 ?> box (Aman)</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1 overflow-hidden">
                                        <div class="bg-amber-500 h-1 rounded-full" style="width: 25%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-[9.5px] mb-0.5">
                                        <span class="font-medium text-gray-600">Empty Depot (M&amp;R)</span>
                                        <span class="font-bold text-gray-600">Standby</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1 overflow-hidden">
                                        <div class="bg-gray-400 h-1 rounded-full" style="width: 30%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Col 3: Container Type Doughnut & Customs Pipeline -->
                    <div class="bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3 sm:p-3.5 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2 pb-1.5 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center">
                                <i class="fa-solid fa-chart-pie mr-1.5 text-cdp-blue text-xs"></i>Tipe Kargo &amp; Bea Cukai
                            </h3>
                            <span class="text-[9px] font-bold text-gray-400 bg-gray-50 px-1.5 py-0.5 rounded">CEISA 4.0</span>
                        </div>
                        <!-- Doughnut Chart Container -->
                        <div class="h-32 sm:h-36 relative w-full flex-1 flex items-center justify-center">
                            <canvas id="containerTypeChart"></canvas>
                        </div>
                        <!-- Customs Pipeline Channel Segment Bar (PMK 190/PMK.04/2022) -->
                        <div class="mt-2 pt-1.5 border-t border-gray-100 space-y-1">
                            <div class="flex justify-between text-[10px]">
                                <span class="font-semibold text-gray-600">Pipeline Kanal Pabean (PMK 190/2022):</span>
                                <span class="font-bold text-emerald-700">SPPB Auto: 92%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2 flex overflow-hidden">
                                <div class="bg-emerald-500 h-2" style="width: 92%" title="Jalur Hijau (92%) - Auto SPPB Rilis"></div>
                                <div class="bg-rose-500 h-2" style="width: 8%" title="Jalur Merah (8%) - Wajib Behandle & X-Ray"></div>
                            </div>
                            <div class="flex justify-between items-center text-[9px] text-gray-500 pt-0.5">
                                <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Hijau (SPPB Rilis): 92%</span>
                                <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Merah (Fisik/Behandle): 8%</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Row 2: Specialized Dry Port Facilities & Intermodal Telemetry (Equal 3-Column Cockpit) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-3.5">
                    
                    <!-- Col 1: Intermodal Rail Siding & Green ICD -->
                    <div class="bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3 sm:p-3.5 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2 pb-1.5 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center">
                                <i class="fa-solid fa-train-subway mr-1.5 text-cdp-blue text-xs"></i>Intermodal Rail Siding &amp; ESG
                            </h3>
                            <span class="text-[9px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200">KA 2518 Live</span>
                        </div>
                        <div class="space-y-2 py-0.5 flex-1 flex flex-col justify-between">
                            <!-- Route & Status Strip -->
                            <div class="bg-slate-50 p-2 rounded-lg border border-gray-200/80">
                                <div class="flex justify-between items-center text-[10.5px]">
                                    <span class="font-bold text-gray-800">CDP Cikarang &harr; Pel. Tj. Priok</span>
                                    <span class="text-purple-700 font-bold bg-purple-100/70 px-1.5 py-0.2 rounded text-[9.5px]">54.8 km</span>
                                </div>
                                <div class="mt-1.5 flex items-center justify-between text-[10px] text-gray-500">
                                    <span>Lokomotif: <strong>CC 206 13 42</strong></span>
                                    <span>ETA Priok: <strong>16:45 WIB</strong></span>
                                </div>
                            </div>

                            <!-- Visual Train Wagon Grid Strip (10 Slots = 30 Wg) -->
                            <div>
                                <div class="flex justify-between text-[10px] text-gray-600 mb-1">
                                    <span class="font-medium">Okupansi Gerbong Datar (PPCW):</span>
                                    <span class="font-bold text-purple-700">24 / 30 TEU (80%)</span>
                                </div>
                                <div class="grid grid-cols-10 gap-1">
                                    <div class="h-4 rounded bg-purple-600 flex items-center justify-center text-[8px] text-white font-mono font-bold" title="Slot 01: Terisi">1</div>
                                    <div class="h-4 rounded bg-purple-600 flex items-center justify-center text-[8px] text-white font-mono font-bold" title="Slot 02: Terisi">2</div>
                                    <div class="h-4 rounded bg-purple-600 flex items-center justify-center text-[8px] text-white font-mono font-bold" title="Slot 03: Terisi">3</div>
                                    <div class="h-4 rounded bg-purple-600 flex items-center justify-center text-[8px] text-white font-mono font-bold" title="Slot 04: Terisi">4</div>
                                    <div class="h-4 rounded bg-purple-600 flex items-center justify-center text-[8px] text-white font-mono font-bold" title="Slot 05: Terisi">5</div>
                                    <div class="h-4 rounded bg-purple-600 flex items-center justify-center text-[8px] text-white font-mono font-bold" title="Slot 06: Terisi">6</div>
                                    <div class="h-4 rounded bg-purple-600 flex items-center justify-center text-[8px] text-white font-mono font-bold" title="Slot 07: Terisi">7</div>
                                    <div class="h-4 rounded bg-purple-600 flex items-center justify-center text-[8px] text-white font-mono font-bold" title="Slot 08: Terisi">8</div>
                                    <div class="h-4 rounded bg-gray-200 border border-dashed border-gray-300 flex items-center justify-center text-[8px] text-gray-400 font-mono" title="Slot 09: Standby">9</div>
                                    <div class="h-4 rounded bg-gray-200 border border-dashed border-gray-300 flex items-center justify-center text-[8px] text-gray-400 font-mono" title="Slot 10: Standby">10</div>
                                </div>
                            </div>

                            <!-- ESG Green ICD Offset -->
                            <div class="p-2 bg-emerald-50/70 border border-emerald-200/80 rounded-lg flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-md bg-emerald-500 text-white flex items-center justify-center text-[10px]">
                                        <i class="fa-solid fa-leaf"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-emerald-900 leading-tight">Reduksi Karbon Modal Shift</p>
                                        <p class="text-[9px] text-emerald-700 leading-tight">Substitusi 48 truk trailer di Tol Cikampek</p>
                                    </div>
                                </div>
                                <span class="text-xs font-extrabold text-emerald-800 bg-white px-2 py-0.5 rounded shadow-2xs border border-emerald-100 font-mono">-1.84 T CO&sup2;</span>
                            </div>
                        </div>
                    </div>

                    <!-- Col 2: Heavy Equipment SCADA Fleet Matrix -->
                    <div class="bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3 sm:p-3.5 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2 pb-1.5 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center">
                                <i class="fa-solid fa-dolly mr-1.5 text-cdp-blue text-xs"></i>Kesiapan Armada &amp; Gate AIDC
                            </h3>
                            <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">4 Unit Aktif</span>
                        </div>
                        <div class="space-y-2 py-0.5 flex-1 flex flex-col justify-between">
                            <!-- 2x2 Grid Heavy Fleet SCADA -->
                            <div class="grid grid-cols-2 gap-1.5">
                                <!-- RS-01 -->
                                <div class="p-1.5 rounded-lg border border-gray-100 bg-gray-50/50 flex flex-col justify-between">
                                    <div class="flex justify-between items-center">
                                        <span class="font-mono font-bold text-[10.5px] text-gray-900 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>RS-01
                                        </span>
                                        <span class="text-[8.5px] font-bold text-blue-600 bg-blue-50 px-1 rounded">Stacking</span>
                                    </div>
                                    <p class="text-[9px] text-gray-500 truncate mt-0.5">Kalmar 45T &bull; Dani S.</p>
                                    <div class="mt-1 flex items-center justify-between text-[8.5px] text-gray-500">
                                        <span>Fuel: <strong>88%</strong></span>
                                        <span class="text-emerald-600 font-semibold">6.2 Jam</span>
                                    </div>
                                </div>
                                <!-- RS-02 -->
                                <div class="p-1.5 rounded-lg border border-gray-100 bg-gray-50/50 flex flex-col justify-between">
                                    <div class="flex justify-between items-center">
                                        <span class="font-mono font-bold text-[10.5px] text-gray-900 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>RS-02
                                        </span>
                                        <span class="text-[8.5px] font-bold text-amber-600 bg-amber-50 px-1 rounded">Transfer</span>
                                    </div>
                                    <p class="text-[9px] text-gray-500 truncate mt-0.5">Sany 45T &bull; Budi T.</p>
                                    <div class="mt-1 flex items-center justify-between text-[8.5px] text-gray-500">
                                        <span>Fuel: <strong>76%</strong></span>
                                        <span class="text-emerald-600 font-semibold">5.8 Jam</span>
                                    </div>
                                </div>
                                <!-- RS-03 -->
                                <div class="p-1.5 rounded-lg border border-gray-100 bg-gray-50/50 flex flex-col justify-between">
                                    <div class="flex justify-between items-center">
                                        <span class="font-mono font-bold text-[10.5px] text-gray-900 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>RS-03
                                        </span>
                                        <span class="text-[8.5px] font-bold text-gray-600 bg-gray-100 px-1 rounded">Standby</span>
                                    </div>
                                    <p class="text-[9px] text-gray-500 truncate mt-0.5">Kalmar 45T &bull; Eko W.</p>
                                    <div class="mt-1 flex items-center justify-between text-[8.5px] text-gray-500">
                                        <span>Fuel: <strong>94%</strong></span>
                                        <span class="text-blue-600 font-semibold">3.1 Jam</span>
                                    </div>
                                </div>
                                <!-- RTG-01 -->
                                <div class="p-1.5 rounded-lg border border-gray-100 bg-gray-50/50 flex flex-col justify-between">
                                    <div class="flex justify-between items-center">
                                        <span class="font-mono font-bold text-[10.5px] text-gray-900 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-pulse"></span>RTG-01
                                        </span>
                                        <span class="text-[8.5px] font-bold text-purple-600 bg-purple-50 px-1 rounded">Siding KA</span>
                                    </div>
                                    <p class="text-[9px] text-gray-500 truncate mt-0.5">Konecranes &bull; Yanto P.</p>
                                    <div class="mt-1 flex items-center justify-between text-[8.5px] text-gray-500">
                                        <span>Fuel: <strong>82%</strong></span>
                                        <span class="text-purple-600 font-semibold">7.4 Jam</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Gate OCR & Sensor Health -->
                            <div class="pt-1 border-t border-gray-100 flex items-center justify-between text-[9.5px]">
                                <span class="text-gray-600 flex items-center gap-1">
                                    <i class="fa-solid fa-camera text-blue-500 text-[9px]"></i>OCR Cam: <strong class="text-emerald-600">99.4%</strong>
                                </span>
                                <span class="text-gray-600 flex items-center gap-1">
                                    <i class="fa-solid fa-weight-scale text-purple-500 text-[9px]"></i>VGM Scale: <strong class="text-emerald-600">Kalibrasi OK</strong>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Col 3: Revenue Breakdown Stacked Bar Chart -->
                    <div class="bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3 sm:p-3.5 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2 pb-1.5 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center">
                                <i class="fa-solid fa-sack-dollar mr-1.5 text-cdp-blue text-xs"></i>Pendapatan Layanan (7 Hari)
                            </h3>
                            <span class="text-[9px] font-bold text-gray-400 bg-gray-50 px-1.5 py-0.5 rounded">Juta Rp</span>
                        </div>
                        <!-- Stacked Bar Chart Container -->
                        <div class="h-36 sm:h-40 relative w-full flex-1">
                            <canvas id="revenueStackedChart"></canvas>
                        </div>
                        <!-- Service Shares Summary -->
                        <div class="mt-2 pt-1.5 border-t border-gray-50 flex items-center justify-between text-[9.5px] text-gray-500">
                            <span>Lo-Lo: <strong>45%</strong></span>
                            <span>Storage: <strong>30%</strong></span>
                            <span>Reefer: <strong>15%</strong></span>
                            <span>VGM: <strong>10%</strong></span>
                        </div>
                    </div>

                </div>

                <!-- Row 3: Live Event Stream & Shipper Consignment Tracking Table -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 sm:gap-3.5">
                    
                    <!-- Left: Log Peristiwa Terminal Real-Time (4 Cols) -->
                    <div class="lg:col-span-4 bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3 sm:p-3.5 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2 pb-1.5 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center">
                                <i class="fa-solid fa-satellite mr-1.5 text-cdp-blue text-xs"></i>Log Lapangan Real-Time
                            </h3>
                            <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">
                                <i class="fa-solid fa-bolt mr-0.5 text-[8px]"></i>Auto Sync
                            </span>
                        </div>
                        
                        <!-- Compact Timeline -->
                        <div class="relative border-l-2 border-gray-100 ml-2 space-y-2.5 py-1 flex-1">
                            <?php if (!empty($db_stats['recent_events'])): ?>
                                <?php foreach ($db_stats['recent_events'] as $evt): 
                                    $type = strtoupper($evt['event_type'] ?? 'EVENT');
                                    $dot_color = 'bg-blue-500';
                                    $tag_badge = 'bg-blue-50 text-blue-700';
                                    if (strpos($type, 'GATE_IN') !== false) {
                                        $dot_color = 'bg-blue-500';
                                        $tag_badge = 'bg-blue-50 text-blue-700';
                                    } elseif (strpos($type, 'GATE_OUT') !== false) {
                                        $dot_color = 'bg-emerald-500';
                                        $tag_badge = 'bg-emerald-50 text-emerald-700';
                                    } elseif (strpos($type, 'RELOCATION') !== false || strpos($type, 'LIFT') !== false) {
                                        $dot_color = 'bg-amber-500';
                                        $tag_badge = 'bg-amber-50 text-amber-700';
                                    } elseif (strpos($type, 'REEFER') !== false) {
                                        $dot_color = 'bg-cyan-500';
                                        $tag_badge = 'bg-cyan-50 text-cyan-700';
                                    } elseif (strpos($type, 'CFS') !== false || strpos($type, 'STRIP') !== false || strpos($type, 'STUFF') !== false) {
                                        $dot_color = 'bg-indigo-500';
                                        $tag_badge = 'bg-indigo-50 text-indigo-700';
                                    } else {
                                        $dot_color = 'bg-purple-500';
                                        $tag_badge = 'bg-purple-50 text-purple-700';
                                    }
                                    $time_str = !empty($evt['created_at']) ? date('H:i', strtotime($evt['created_at'])) : '--:--';
                                    $ctr_num = !empty($evt['container_number']) ? htmlspecialchars($evt['container_number']) : '-';
                                    $notes = !empty($evt['notes']) ? htmlspecialchars($evt['notes']) : 'Aktivitas lapangan tercatat.';
                                    if (mb_strlen($notes) > 75) {
                                        $notes = mb_substr($notes, 0, 72) . '...';
                                    }
                                    $operator = !empty($evt['operator_name']) ? htmlspecialchars($evt['operator_name']) : (!empty($evt['equipment_id']) ? htmlspecialchars($evt['equipment_id']) : 'Sistem Operasi');
                                ?>
                                <div class="relative pl-4">
                                    <div class="timeline-dot <?= $dot_color ?> ring-2 ring-white"></div>
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold text-gray-800 text-[11px]"><?= htmlspecialchars(str_replace('_', ' ', $type)) ?> <span class="font-mono <?= $tag_badge ?> px-1 rounded text-[10px]"><?= $ctr_num ?></span></span>
                                        <span class="text-[9.5px] text-gray-400 font-mono"><?= $time_str ?> WIB</span>
                                    </div>
                                    <p class="text-[10px] text-gray-500 mt-0.5" title="<?= htmlspecialchars($evt['notes'] ?? '') ?>"><?= $notes ?></p>
                                    <span class="text-[9px] text-gray-400 block mt-0.5"><i class="fa-solid fa-user-gear text-[8px] mr-1"></i><?= $operator ?></span>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-4 text-gray-400 text-xs">Belum ada aktivitas lapangan tercatat.</div>
                            <?php endif; ?>
                        </div>

                        <!-- Footer Link -->
                        <div class="pt-2 border-t border-gray-100 text-center">
                            <a href="dashboard.php?page=kontainer" class="text-[10.5px] font-bold text-cdp-blue hover:text-cdp-navy inline-flex items-center gap-1 transition">
                                <span>Buka Riwayat Operasional Lengkap</span>
                                <i class="fa-solid fa-arrow-right text-[9px]"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Right: Pelacakan Konsinyasi Muatan Klien (8 Cols) -->
                    <div class="lg:col-span-8 bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3 sm:p-3.5 flex flex-col justify-between">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 mb-2 pb-1.5 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center">
                                <i class="fa-solid fa-list-check mr-1.5 text-cdp-blue text-xs"></i>Pelacakan Konsinyasi Muatan Klien (Shipper Consignments)
                            </h3>
                            <div class="flex items-center gap-1.5">
                                <span class="text-[9px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200">
                                    <i class="fa-solid fa-wifi mr-0.5 text-[8px]"></i>RFID UHF ISO 18000-6C
                                </span>
                                <a href="dashboard.php?page=kontainer" class="text-[10px] font-bold text-cdp-blue hover:underline">
                                    Semua Data &rarr;
                                </a>
                            </div>
                        </div>

                        <!-- Compact Table -->
                        <div class="overflow-x-auto flex-1">
                            <table class="w-full text-left border-collapse min-w-[620px]">
                                <thead>
                                    <tr class="bg-gray-50/70 text-gray-500 text-[10px] border-y border-gray-100">
                                        <th class="py-2 px-2.5 font-bold uppercase">No. Kontainer</th>
                                        <th class="py-2 px-2.5 font-bold uppercase">Tipe &amp; ISO</th>
                                        <th class="py-2 px-2.5 font-bold uppercase">Tag RFID</th>
                                        <th class="py-2 px-2.5 font-bold uppercase">Status</th>
                                        <th class="py-2 px-2.5 font-bold uppercase">Posisi Lapangan</th>
                                        <th class="py-2 px-2.5 font-bold uppercase text-right">Pembaruan</th>
                                    </tr>
                                </thead>
                                <tbody class="text-[11px] divide-y divide-gray-100">
                                    <?php if (!empty($db_stats['recent_containers'])): ?>
                                        <?php foreach ($db_stats['recent_containers'] as $c): 
                                            $blk = trim($c['block'] ?? '');
                                            $bay = trim($c['bay'] ?? '');
                                            $row = trim($c['row'] ?? '');
                                            $tier = trim($c['tier'] ?? '');
                                            if (!empty($blk)) {
                                                $pos = 'Blok ' . $blk . (!empty($bay) ? ' / ' . $bay . '-' . $row . '-' . $tier : '');
                                            } else {
                                                $pos = 'Gate Area / Transisi';
                                            }
                                            $c_status = $c['status'] ?? 'in_yard';
                                            $status_badge = 'bg-blue-50 text-blue-700 border-blue-200';
                                            $status_label = ucwords(str_replace('_', ' ', $c_status));
                                            if ($c_status === 'in_yard') {
                                                $status_badge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                                $status_label = 'In Yard (Stack)';
                                            } elseif ($c_status === 'gate_out') {
                                                $status_badge = 'bg-gray-100 text-gray-700 border-gray-200';
                                                $status_label = 'Gate-Out (Rilis)';
                                            } elseif ($c_status === 'gate_in') {
                                                $status_badge = 'bg-blue-50 text-blue-700 border-blue-200';
                                                $status_label = 'Gate-In (Antrean)';
                                            }
                                            $time_disp = !empty($c['created_at']) ? date('H:i', strtotime($c['created_at'])) . ' WIB' : '--:-- WIB';
                                            $rfid_disp = !empty($c['rfid_tag']) ? htmlspecialchars($c['rfid_tag']) : 'RFID-AUTO-' . substr(md5($c['container_number']), 0, 5);
                                            $size_disp = htmlspecialchars(($c['size_type'] ?? '40FT') . (!empty($c['iso_code']) ? ' (' . $c['iso_code'] . ')' : ''));
                                        ?>
                                        <tr class="hover:bg-blue-50/20 transition">
                                            <td class="py-2 px-2.5 font-mono font-bold text-[#0170b9]">
                                                <a href="dashboard.php?page=kontainer&search=<?= urlencode($c['container_number']) ?>" class="hover:underline flex items-center gap-1">
                                                    <?= htmlspecialchars($c['container_number']) ?>
                                                    <i class="fa-solid fa-arrow-up-right-from-square text-[8px] text-gray-400"></i>
                                                </a>
                                            </td>
                                            <td class="py-2 px-2.5 text-gray-600"><?= $size_disp ?></td>
                                            <td class="py-2 px-2.5 font-mono text-[10px] text-purple-700 font-semibold"><?= $rfid_disp ?></td>
                                            <td class="py-2 px-2.5"><span class="<?= $status_badge ?> border px-1.5 py-0.5 rounded text-[9.5px] font-bold"><?= $status_label ?></span></td>
                                            <td class="py-2 px-2.5 font-semibold text-gray-800"><?= htmlspecialchars($pos) ?></td>
                                            <td class="py-2 px-2.5 text-gray-400 text-right text-[10px]"><?= $time_disp ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center py-4 text-gray-400">Tidak ada kontainer terdaftar.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- Row 4: Panoramic 35 Ha Zone Radar & Host-to-Host Integration Matrix (Anti-Pusing Visual Showcase) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 sm:gap-3.5">
                    
                    <!-- Left: Radar 4-Zona Fasilitas Terminal 35 Ha (7 Cols) -->
                    <div class="lg:col-span-7 bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3 sm:p-3.5 flex flex-col justify-between">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 mb-2 pb-1.5 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center">
                                <i class="fa-solid fa-map-location-dot mr-1.5 text-cdp-blue text-xs"></i>Radar 4 Zona Fasilitas Terminal 35 Ha (Distribusi Spasial)
                            </h3>
                            <a href="dashboard.php?page=denah" class="text-[10px] font-bold text-cdp-blue hover:underline flex items-center gap-1">
                                <span>Buka Denah 35 Ha Lengkap</span>
                                <i class="fa-solid fa-arrow-up-right-from-square text-[8px]"></i>
                            </a>
                        </div>
                        
                        <!-- 4-Zone Quadrant Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 flex-1 py-1">
                            <!-- Quadrant 1: Gerbang & Buffer Truk -->
                            <div class="p-2.5 rounded-lg border border-amber-100 bg-amber-50/30 flex flex-col justify-between">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-6 h-6 rounded-md bg-amber-500/20 text-amber-700 flex items-center justify-center text-[10px] font-bold">Z1</div>
                                        <div>
                                            <span class="font-bold text-gray-800 text-[11px]">Gerbang &amp; Buffer Truk</span>
                                            <p class="text-[9px] text-gray-500">4.2 Ha &bull; 2 In / 2 Out Gate</p>
                                        </div>
                                    </div>
                                    <span class="px-1.5 py-0.2 rounded text-[8.5px] font-bold bg-emerald-100 text-emerald-800">Lancar</span>
                                </div>
                                <div class="mt-2 space-y-1">
                                    <div class="flex justify-between text-[9px] text-gray-600">
                                        <span>Antrean Gerbang:</span>
                                        <span class="font-bold text-amber-800"><?= $db_stats['total_gate_trucks'] ?> Truk Aktif</span>
                                    </div>
                                    <div class="w-full bg-gray-200/80 rounded-full h-1 overflow-hidden">
                                        <div class="bg-amber-500 h-1 rounded-full" style="width: 35%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quadrant 2: Container Yard 12.500 TEU -->
                            <div class="p-2.5 rounded-lg border border-blue-100 bg-blue-50/30 flex flex-col justify-between">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-6 h-6 rounded-md bg-blue-500/20 text-blue-700 flex items-center justify-center text-[10px] font-bold">Z2</div>
                                        <div>
                                            <span class="font-bold text-gray-800 text-[11px]">Stacking Yard Blok A-C</span>
                                            <p class="text-[9px] text-gray-500">16.5 Ha &bull; Kapasitas 12.500 TEU</p>
                                        </div>
                                    </div>
                                    <span class="px-1.5 py-0.2 rounded text-[8.5px] font-bold bg-blue-100 text-blue-800">Operasi</span>
                                </div>
                                <div class="mt-2 space-y-1">
                                    <div class="flex justify-between text-[9px] text-gray-600">
                                        <span>Okupansi Lapangan:</span>
                                        <span class="font-bold text-blue-800"><?= $db_stats['containers_in_yard'] ?> Box (<?= $yard_pct ?>%)</span>
                                    </div>
                                    <div class="w-full bg-gray-200/80 rounded-full h-1 overflow-hidden">
                                        <div class="bg-blue-600 h-1 rounded-full" style="width: <?= $yard_pct ?>%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quadrant 3: Gudang CFS & Rampa D1-D5 -->
                            <div class="p-2.5 rounded-lg border border-indigo-100 bg-indigo-50/30 flex flex-col justify-between">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-6 h-6 rounded-md bg-indigo-500/20 text-indigo-700 flex items-center justify-center text-[10px] font-bold">Z3</div>
                                        <div>
                                            <span class="font-bold text-gray-800 text-[11px]">Gudang CFS Rampa D1-D5</span>
                                            <p class="text-[9px] text-gray-500">6.8 Ha &bull; Luas 4.000m┬▓</p>
                                        </div>
                                    </div>
                                    <span class="px-1.5 py-0.2 rounded text-[8.5px] font-bold bg-indigo-100 text-indigo-800">Konsolidasi</span>
                                </div>
                                <div class="mt-2 space-y-1">
                                    <div class="flex justify-between text-[9px] text-gray-600">
                                        <span>Tugas Stripping/Stuffing:</span>
                                        <span class="font-bold text-indigo-800"><?= $db_stats['cfs_active_jobs'] ?> Jobs Berjalan</span>
                                    </div>
                                    <div class="w-full bg-gray-200/80 rounded-full h-1 overflow-hidden">
                                        <div class="bg-indigo-600 h-1 rounded-full" style="width: 80%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quadrant 4: Siding KA & Reefer Terminal -->
                            <div class="p-2.5 rounded-lg border border-cyan-100 bg-cyan-50/30 flex flex-col justify-between">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-6 h-6 rounded-md bg-cyan-500/20 text-cyan-700 flex items-center justify-center text-[10px] font-bold">Z4</div>
                                        <div>
                                            <span class="font-bold text-gray-800 text-[11px]">Siding KA &amp; Reefer Cold Chain</span>
                                            <p class="text-[9px] text-gray-500">7.5 Ha &bull; Siding 2x600m &bull; 300 Plugs</p>
                                        </div>
                                    </div>
                                    <span class="px-1.5 py-0.2 rounded text-[8.5px] font-bold bg-cyan-100 text-cyan-800">Cold Chain</span>
                                </div>
                                <div class="mt-2 space-y-1">
                                    <div class="flex justify-between text-[9px] text-gray-600">
                                        <span>Marechal 380V Telemetri:</span>
                                        <span class="font-bold text-cyan-800"><?= $db_stats['reefer_count'] ?> Box / -20.2&deg;C</span>
                                    </div>
                                    <div class="w-full bg-gray-200/80 rounded-full h-1 overflow-hidden">
                                        <div class="bg-cyan-500 h-1 rounded-full" style="width: 75%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Micro Footer Bar -->
                        <div class="mt-2 pt-1.5 border-t border-gray-100 flex items-center justify-between text-[10px] text-gray-500">
                            <span>Total Luas Master: <strong>35 Hektar (CIDP Cikarang)</strong></span>
                            <span class="text-emerald-700 font-semibold flex items-center gap-1"><i class="fa-solid fa-check-double text-[9px]"></i> 26 BOM Hardware Terpasang</span>
                        </div>
                    </div>

                    <!-- Right: Matriks Integrasi Host-to-Host & AIDC GS1 AI (5 Cols) -->
                    <div class="lg:col-span-5 bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3 sm:p-3.5 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2 pb-1.5 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center">
                                <i class="fa-solid fa-network-wired mr-1.5 text-cdp-blue text-xs"></i>Matriks Konektivitas Host-to-Host (API Live)
                            </h3>
                            <a href="dashboard.php?page=settings" class="text-[9px] font-bold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 hover:bg-slate-200 transition">
                                Pengaturan v2.4 &rarr;
                            </a>
                        </div>

                        <!-- 5 Live API Gateway Connectors -->
                        <div class="space-y-1.5 py-0.5 flex-1 flex flex-col justify-around text-[10px]">
                            <!-- Gateway 1: CEISA 4.0 -->
                            <div class="p-1.5 rounded-lg border border-gray-100 bg-gray-50/70 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <div>
                                        <span class="font-bold text-gray-800">Bea Cukai CEISA 4.0</span>
                                        <p class="text-[8.5px] text-gray-400">Host-to-Host REST API &bull; SPPB Auto-Clearance</p>
                                    </div>
                                </div>
                                <span class="font-mono text-[9px] text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded font-bold border border-emerald-200">ONLINE (42ms)</span>
                            </div>

                            <!-- Gateway 2: Inaportnet Kemenhub -->
                            <div class="p-1.5 rounded-lg border border-gray-100 bg-gray-50/70 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <div>
                                        <span class="font-bold text-gray-800">Inaportnet Kemenhub V2</span>
                                        <p class="text-[8.5px] text-gray-400">National Logistics Ecosystem (NLE) Sinkronisasi</p>
                                    </div>
                                </div>
                                <span class="font-mono text-[9px] text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded font-bold border border-emerald-200">ONLINE (58ms)</span>
                            </div>

                            <!-- Gateway 3: KAI Logistik EDI -->
                            <div class="p-1.5 rounded-lg border border-gray-100 bg-gray-50/70 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <div>
                                        <span class="font-bold text-gray-800">Kereta Api Logistik EDI</span>
                                        <p class="text-[8.5px] text-gray-400">Jadwal Siding KA 2518 &amp; Manifest PPCW</p>
                                    </div>
                                </div>
                                <span class="font-mono text-[9px] text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded font-bold border border-emerald-200">ONLINE (35ms)</span>
                            </div>

                            <!-- Gateway 4: Marechal Reefer IoT -->
                            <div class="p-1.5 rounded-lg border border-gray-100 bg-gray-50/70 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse"></span>
                                    <div>
                                        <span class="font-bold text-gray-800">Marechal IoT Gateway</span>
                                        <p class="text-[8.5px] text-gray-400">LoRaWAN 915MHz &bull; 300 Marechal Plugs Pulse</p>
                                    </div>
                                </div>
                                <span class="font-mono text-[9px] text-cyan-700 bg-cyan-50 px-1.5 py-0.5 rounded font-bold border border-cyan-200">PULSE (110ms)</span>
                            </div>

                            <!-- Gateway 5: GS1 SSCC AI Station -->
                            <div class="p-1.5 rounded-lg border border-gray-100 bg-gray-50/70 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-purple-500 animate-pulse"></span>
                                    <div>
                                        <span class="font-bold text-gray-800">GS1 SSCC &amp; RFID AIDC</span>
                                        <p class="text-[8.5px] text-gray-400">ISO 18000-6C &bull; Optical Vision Decoder</p>
                                    </div>
                                </div>
                                <span class="font-mono text-[9px] text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded font-bold border border-purple-200">99.8% AKURAT</span>
                            </div>
                        </div>

                        <!-- Micro Footer Bar -->
                        <div class="mt-2 pt-1.5 border-t border-gray-100 flex items-center justify-between text-[10px] text-gray-500">
                            <span>Protokol Keamanan: <strong>TLS 1.3 / OAuth2.0</strong></span>
                            <span class="text-emerald-700 font-semibold">Semua Layanan Aktif</span>
                        </div>
                    </div>

                </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Chart.defaults.font.family = '"Plus Jakarta Sans", sans-serif';
            Chart.defaults.font.size = 10;
            Chart.defaults.color = '#64748b';
            Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0, 47, 94, 0.92)';
            Chart.defaults.plugins.tooltip.padding = 8;
            Chart.defaults.plugins.tooltip.cornerRadius = 6;
            Chart.defaults.plugins.tooltip.titleFont = { size: 11, weight: 'bold' };
            Chart.defaults.plugins.tooltip.bodyFont = { size: 10 };
            
            // 1. Line/Area Chart: Tren Aktivitas Gate
            const gateCtx = document.getElementById('gateTrendChart');
            if (gateCtx) {
                new Chart(gateCtx, {
                    type: 'line',
                    data: {
                        labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                        datasets: [
                            {
                                label: 'Gate-In',
                                data: [18, 22, 19, 24, 25, 15, 12],
                                borderColor: '#0170b9', // cdp-blue
                                backgroundColor: 'rgba(1, 112, 185, 0.08)',
                                fill: true,
                                tension: 0.35,
                                borderWidth: 1.75,
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: '#0170b9',
                                pointBorderWidth: 1.5,
                                pointRadius: 2.5,
                                pointHoverRadius: 4.5
                            },
                            {
                                label: 'Gate-Out',
                                data: [15, 20, 18, 21, 23, 14, 10],
                                borderColor: '#4f46e5', // indigo-600
                                backgroundColor: 'transparent',
                                fill: false,
                                tension: 0.35,
                                borderWidth: 1.75,
                                borderDash: [4, 4],
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: '#4f46e5',
                                pointBorderWidth: 1.5,
                                pointRadius: 2.5,
                                pointHoverRadius: 4.5
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { usePointStyle: true, boxWidth: 6, padding: 6, font: { size: 9.5 } }
                            }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true, 
                                grid: { borderDash: [2, 4], color: '#f1f5f9' },
                                ticks: { font: { size: 9 } }
                            },
                            x: { 
                                grid: { display: false },
                                ticks: { font: { size: 9 } }
                            }
                        }
                    }
                });
            }

            // 2. Doughnut Chart: Distribusi Tipe Kontainer
            const typeCtx = document.getElementById('containerTypeChart');
            if (typeCtx) {
                <?php
                    $dry_num = max(1, (int)$db_stats['containers_in_yard'] - (int)$db_stats['reefer_count']);
                    $rf_num = max(1, (int)$db_stats['reefer_count']);
                    $empty_num = 2;
                    $dg_num = 1;
                    $tot_d = $dry_num + $rf_num + $empty_num + $dg_num;
                ?>
                new Chart(typeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: [
                            'Dry (<?= round(($dry_num / $tot_d) * 100) ?>%)', 
                            'Reefer (<?= round(($rf_num / $tot_d) * 100) ?>%)', 
                            'Empty (<?= round(($empty_num / $tot_d) * 100) ?>%)', 
                            'DG/IMO (<?= round(($dg_num / $tot_d) * 100) ?>%)'
                        ],
                        datasets: [{
                            data: [<?= $dry_num ?>, <?= $rf_num ?>, <?= $empty_num ?>, <?= $dg_num ?>],
                            backgroundColor: ['#0170b9', '#06b6d4', '#94a3b8', '#ef4444'],
                            borderWidth: 0,
                            hoverOffset: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { usePointStyle: true, boxWidth: 6, padding: 4, font: { size: 9 } }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.label;
                                    }
                                }
                            }
                        }
                    },
                    plugins: [{
                        id: 'textCenter',
                        beforeDraw: function(chart) {
                            const { ctx, chartArea } = chart;
                            if (!chartArea) return;
                            ctx.save();
                            const centerX = (chartArea.left + chartArea.right) / 2;
                            const centerY = (chartArea.top + chartArea.bottom) / 2 - 8;

                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';

                            // Main count
                            ctx.font = "bold 0.85rem 'Plus Jakarta Sans', sans-serif";
                            ctx.fillStyle = "#0f172a";
                            ctx.fillText("<?= (int)$db_stats['containers_in_yard'] ?> Box", centerX, centerY - 4);

                            // Subtitle
                            ctx.font = "600 0.55rem 'Plus Jakarta Sans', sans-serif";
                            ctx.fillStyle = "#64748b";
                            ctx.fillText("Total Yard", centerX, centerY + 8);
                            ctx.restore();
                        }
                    }]
                });
            }

            // 3. Stacked Bar Chart: Pendapatan per Jenis Layanan
            const revCtx = document.getElementById('revenueStackedChart');
            if (revCtx) {
                new Chart(revCtx, {
                    type: 'bar',
                    data: {
                        labels: ['H-6', 'H-5', 'H-4', 'H-3', 'H-2', 'H-1', 'Hari Ini'],
                        datasets: [
                            {
                                label: 'Lo-Lo',
                                data: [12.5, 14.2, 11.8, 15.6, 16.2, 10.5, 18.4],
                                backgroundColor: '#0170b9', // blue
                                borderRadius: {topLeft: 0, topRight: 0, bottomLeft: 3, bottomRight: 3}
                            },
                            {
                                label: 'Storage',
                                data: [8.2, 9.5, 8.8, 10.2, 11.5, 9.2, 12.6],
                                backgroundColor: '#4f46e5', // indigo
                            },
                            {
                                label: 'VGM',
                                data: [3.4, 4.1, 3.2, 4.8, 5.2, 2.8, 5.5],
                                backgroundColor: '#a855f7', // purple
                            },
                            {
                                label: 'Reefer',
                                data: [5.6, 5.8, 5.5, 6.2, 6.5, 5.2, 7.1],
                                backgroundColor: '#06b6d4', // cyan
                                borderRadius: {topLeft: 3, topRight: 3, bottomLeft: 0, bottomRight: 0}
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { usePointStyle: true, boxWidth: 6, padding: 5, font: { size: 9 } }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.dataset.label + ': Rp ' + context.raw + ' Jt';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: { 
                                stacked: true, 
                                grid: { display: false },
                                ticks: { font: { size: 9 } }
                            },
                            y: { 
                                stacked: true, 
                                beginAtZero: true, 
                                grid: { borderDash: [2, 4], color: '#f1f5f9' },
                                ticks: {
                                    font: { size: 8.5 },
                                    callback: function(value) {
                                        return value + 'Jt';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
