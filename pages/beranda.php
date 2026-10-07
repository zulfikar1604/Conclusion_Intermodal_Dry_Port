<?php
// =============================================================================
// FILE: pages/beranda.php
// FUNGSI: Beranda Eksekutif & Telemetri Terpadu Dry Port Cikarang 35 Ha
// DESAIN: Mengadopsi Layout Kaya Grafik & Simbol Sesuai Referensi Proyek
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

<!-- Sub-Header: Breadcrumb Navigation & Primary Hero Metric (Ref: media_1791395233559.png) -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
    <div class="flex items-center gap-2 text-[11.5px] sm:text-[12px] text-ink/60">
        <span class="hover:text-ink cursor-pointer transition">Home</span>
        <span class="text-ink/30">/</span>
        <span class="font-semibold text-ink">Operational Dashboard (35 Ha Cikarang Dry Port)</span>
        <span class="hidden md:inline-block text-[10px] bg-slate-200/70 text-slate-700 px-2 py-0.5 rounded font-mono font-medium ml-2">ICD-CKR-2026</span>
    </div>

    <!-- Hero Metric: Throughput YTD Banner (Ref: 27,33 M in image) -->
    <div class="bg-gradient-to-r from-ink via-ink-2 to-[#1b2d47] text-paper rounded-lg px-4 py-2 shadow-xs border border-white/10 flex items-center justify-between sm:justify-end gap-3 self-stretch sm:self-auto">
        <div class="text-left sm:text-right">
            <span class="text-[9.5px] uppercase tracking-wider text-paper/50 block font-semibold leading-tight">Total Throughput YTD</span>
            <div class="flex items-baseline gap-1.5 leading-none mt-0.5">
                <span class="text-xl sm:text-2xl font-extrabold text-brass font-mono tracking-tight">27,33 M</span>
                <span class="text-[10.5px] text-paper/70 font-medium">TEU Equivalent</span>
            </div>
        </div>
        <div class="w-9 h-9 rounded-md bg-white/[0.08] flex items-center justify-center text-brass text-base flex-shrink-0 border border-white/10">
            <i class="fa-solid fa-chart-line-up"></i>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- ROW 1: DAFTAR OPERASI, STATUS ANALISIS, MATRIKS OPERASI & STATUS ARMADA  -->
<!-- ========================================================================= -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-12 gap-3.5 sm:gap-4 items-stretch">

    <!-- CARD 1: DAFTAR OPERASI DETAIL (xl:col-span-5) -->
    <div class="xl:col-span-5 bg-white rounded-lg shadow-2xs border border-[#E4DFD3] flex flex-col justify-between overflow-hidden">
        <!-- Card Navy Header Bar -->
        <div class="bg-[#1B2D47] text-white px-3.5 py-2.5 flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-table-list text-brass text-[12px]"></i>
                <h3 class="text-[11.5px] font-bold tracking-wider uppercase text-paper">Daftar Operasi Detail</h3>
            </div>
            <a href="dashboard.php?page=kontainer" class="text-[10px] text-paper/60 hover:text-white transition flex items-center gap-1">
                <span>Lihat Semua</span>
                <i class="fa-solid fa-arrow-up-right-from-square text-[8px]"></i>
            </a>
        </div>

        <!-- Operations Table -->
        <?php
            $table_containers = !empty($db_stats['recent_containers']) ? $db_stats['recent_containers'] : [];
            $sample_pool = [
                ['container_number' => 'MSKU 9182374', 'sscc_code' => 'DO-127.500/64483', 'status' => 'in_yard', 'customs_status' => 'SPPB_CLEARED', 'size_type' => '40FT HC Dry', 'created_at' => '2026-10-08 08:30:00', 'dwell' => '1.2 Hari', 'pos' => 'Blok B-08'],
                ['container_number' => 'TCLU 4829103', 'sscc_code' => 'DO-127.590/04463', 'status' => 'in_yard', 'customs_status' => 'STACKING', 'size_type' => '20FT GP Dry', 'created_at' => '2026-10-08 09:15:00', 'dwell' => '0.8 Hari', 'pos' => 'Blok A-02'],
                ['container_number' => 'CMAU 7234910', 'sscc_code' => 'DO-127.390/06463', 'status' => 'in_yard', 'customs_status' => 'REEFER_ACTIVE', 'size_type' => '40FT Reefer', 'created_at' => '2026-10-07 14:20:00', 'dwell' => '2.1 Hari', 'pos' => 'Reefer R-04'],
                ['container_number' => 'TEMU 5192034', 'sscc_code' => 'DO-127.590/08481', 'status' => 'in_transit', 'customs_status' => 'GATE_IN', 'size_type' => '40FT HC Dry', 'created_at' => '2026-10-08 11:45:00', 'dwell' => '0.3 Hari', 'pos' => 'Gate Lane 01'],
                ['container_number' => 'CSQU 3091824', 'sscc_code' => 'DO-127.560/68481', 'status' => 'on_rail', 'customs_status' => 'RAIL_SIDING', 'size_type' => '40FT HC Dry', 'created_at' => '2026-10-06 16:00:00', 'dwell' => '3.4 Hari', 'pos' => 'Track S-01'],
                ['container_number' => 'FCIU 8192039', 'sscc_code' => 'DO-127.500/08455', 'status' => 'in_yard', 'customs_status' => 'SPPB_CLEARED', 'size_type' => '20FT DG IMO 3', 'created_at' => '2026-10-07 10:10:00', 'dwell' => '1.5 Hari', 'pos' => 'DG Yard D-01'],
                ['container_number' => 'HLXU 6291048', 'sscc_code' => 'DO-127.300/06461', 'status' => 'gate_out', 'customs_status' => 'GATE_OUT', 'size_type' => '20FT GP Dry', 'created_at' => '2026-10-08 12:00:00', 'dwell' => '0.5 Hari', 'pos' => 'Gate Out 02']
            ];
            while (count($table_containers) < 7 && !empty($sample_pool)) {
                $table_containers[] = array_shift($sample_pool);
            }
            $table_containers = array_slice($table_containers, 0, 7);
        ?>
        <div class="overflow-x-auto flex-1">
            <table class="w-full text-left border-collapse min-w-[540px]">
                <thead>
                    <tr class="bg-slate-50/90 text-gray-500 text-[10px] uppercase font-bold border-b border-gray-100">
                        <th class="py-2 px-2.5">No &amp; Peti Kemas</th>
                        <th class="py-2 px-2">No. ID / DO</th>
                        <th class="py-2 px-2 text-center">Status</th>
                        <th class="py-2 px-2">Kategori</th>
                        <th class="py-2 px-2">Tanggal</th>
                        <th class="py-2 px-2 text-center">Dwell</th>
                        <th class="py-2 px-2.5 text-right">Posisi</th>
                    </tr>
                </thead>
                <tbody class="text-[11px] divide-y divide-gray-100 font-sans">
                    <?php $idx = 1; foreach ($table_containers as $tc): 
                        $num = htmlspecialchars($tc['container_number'] ?? 'MSKU 0000000');
                        $doc = htmlspecialchars($tc['sscc_code'] ?? ('DO-127.' . rand(300, 590) . '/' . rand(10000, 99999)));
                        $status = strtolower($tc['status'] ?? 'in_yard');
                        $cat = htmlspecialchars($tc['size_type'] ?? ($tc['cargo_type'] ?? 'Dry 40ft'));
                        $date_str = !empty($tc['created_at']) ? date('Y-m-d', strtotime($tc['created_at'])) : date('Y-m-d');
                        $dwell = htmlspecialchars($tc['dwell'] ?? (rand(0, 3) . '.' . rand(1, 9) . ' Hari'));
                        
                        $pos = htmlspecialchars($tc['pos'] ?? (!empty($tc['block']) ? 'Blok ' . $tc['block'] . (!empty($tc['bay']) ? '-' . $tc['bay'] : '') : 'Yard Core'));
                        
                        // Status Pill Styling
                        if (strpos($status, 'yard') !== false || ($tc['customs_status'] ?? '') === 'SPPB_CLEARED') {
                            $pill_cls = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                            $pill_lbl = 'Cleared';
                        } elseif (strpos($status, 'rail') !== false || ($tc['customs_status'] ?? '') === 'RAIL_SIDING') {
                            $pill_cls = 'bg-purple-50 text-purple-700 border-purple-200';
                            $pill_lbl = 'Rail-Siding';
                        } elseif (strpos($status, 'transit') !== false || ($tc['customs_status'] ?? '') === 'GATE_IN') {
                            $pill_cls = 'bg-blue-50 text-blue-700 border-blue-200';
                            $pill_lbl = 'In-Gate';
                        } elseif (strpos($status, 'out') !== false) {
                            $pill_cls = 'bg-slate-100 text-slate-700 border-slate-200';
                            $pill_lbl = 'Gate-Out';
                        } else {
                            $pill_cls = 'bg-amber-50 text-amber-700 border-amber-200';
                            $pill_lbl = 'Stacking';
                        }
                    ?>
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-2 px-2.5 font-mono font-semibold text-ink flex items-center gap-1.5 whitespace-nowrap">
                            <span class="text-[9.5px] text-gray-400 font-sans"><?= $idx ?>.</span>
                            <a href="dashboard.php?page=kontainer&search=<?= urlencode($num) ?>" class="text-[#0170b9] hover:underline">
                                <?= $num ?>
                            </a>
                        </td>
                        <td class="py-2 px-2 font-mono text-[10px] text-gray-500 whitespace-nowrap"><?= $doc ?></td>
                        <td class="py-2 px-2 text-center whitespace-nowrap">
                            <span class="px-1.5 py-0.5 rounded text-[9.5px] font-semibold border <?= $pill_cls ?>"><?= $pill_lbl ?></span>
                        </td>
                        <td class="py-2 px-2 text-gray-600 whitespace-nowrap"><?= $cat ?></td>
                        <td class="py-2 px-2 font-mono text-[10px] text-gray-500 whitespace-nowrap"><?= $date_str ?></td>
                        <td class="py-2 px-2 font-mono text-[10.5px] text-center text-gray-600 whitespace-nowrap"><?= $dwell ?></td>
                        <td class="py-2 px-2.5 text-right font-medium text-gray-700 whitespace-nowrap"><?= $pos ?></td>
                    </tr>
                    <?php $idx++; endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Micro Footer Bar -->
        <div class="px-3 py-1.5 bg-slate-50/70 border-t border-gray-100 flex items-center justify-between text-[10px] text-gray-400">
            <span>Sinkronisasi Otomatis Terminal Operating System (TOS)</span>
            <span class="font-medium text-gray-500">7 Transaksi Aktif Terakhir</span>
        </div>
    </div>

    <!-- CARD 2: STATUS ANALISIS (xl:col-span-3) -->
    <div class="xl:col-span-3 bg-white rounded-lg shadow-2xs border border-[#E4DFD3] flex flex-col justify-between overflow-hidden">
        <!-- Card Navy Header Bar -->
        <div class="bg-[#1B2D47] text-white px-3.5 py-2.5 flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-brass text-[12px]"></i>
                <h3 class="text-[11.5px] font-bold tracking-wider uppercase text-paper">Status Analisis</h3>
            </div>
            <span class="text-[10px] font-mono text-paper/60">35 Ha Yard</span>
        </div>

        <!-- Donut Chart & Legend -->
        <div class="p-3 sm:p-3.5 flex flex-col justify-between flex-1">
            <!-- Donut Chart Container with Center Metric -->
            <div class="h-36 relative flex items-center justify-center w-full">
                <canvas id="containerTypeChart"></canvas>
            </div>

            <!-- Structured Vertical Breakdown List (Matching Reference Image) -->
            <div class="mt-2.5 pt-2 border-t border-gray-100 space-y-1.5 text-[11px]">
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-1.5 text-gray-600 font-medium">
                        <span class="w-2 h-2 rounded-full bg-[#0170b9]"></span>Blok A (Dry Impor)
                    </span>
                    <span class="font-bold text-gray-800 font-mono text-[10.5px]">35.00%</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-1.5 text-gray-600 font-medium">
                        <span class="w-2 h-2 rounded-full bg-[#4f46e5]"></span>Blok B (Dry Ekspor)
                    </span>
                    <span class="font-bold text-gray-800 font-mono text-[10.5px]">28.50%</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-1.5 text-gray-600 font-medium">
                        <span class="w-2 h-2 rounded-full bg-[#3b82f6]"></span>Blok C (Buffer Yard)
                    </span>
                    <span class="font-bold text-gray-800 font-mono text-[10.5px]">18.20%</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-1.5 text-gray-600 font-medium">
                        <span class="w-2 h-2 rounded-full bg-[#06b6d4]"></span>Reefer Cold Chain
                    </span>
                    <span class="font-bold text-gray-800 font-mono text-[10.5px]">12.30%</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-1.5 text-gray-600 font-medium">
                        <span class="w-2 h-2 rounded-full bg-[#ef4444]"></span>DG Yard (IMO)
                    </span>
                    <span class="font-bold text-gray-800 font-mono text-[10.5px]">6.00%</span>
                </div>
            </div>
        </div>

        <!-- Micro Footer Bar -->
        <div class="px-3 py-1.5 bg-slate-50/70 border-t border-gray-100 flex items-center justify-between text-[10px] text-gray-400">
            <span>Okupansi Stacking: <strong>68%</strong></span>
            <span class="text-emerald-700 font-semibold font-mono">1.420 / 2.100 TEU</span>
        </div>
    </div>

    <!-- CARD 3: MATRIKS OPERASI GATE & SIDING (xl:col-span-2) -->
    <div class="xl:col-span-2 bg-white rounded-lg shadow-2xs border border-[#E4DFD3] flex flex-col justify-between overflow-hidden">
        <!-- Card Navy Header Bar -->
        <div class="bg-[#1B2D47] text-white px-3 py-2.5 flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-table-cells text-brass text-[12px]"></i>
                <h3 class="text-[11.5px] font-bold tracking-wider uppercase text-paper">Matriks Operasi</h3>
            </div>
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        </div>

        <!-- Matrix Comparison Table (Matching Reference Image) -->
        <div class="p-0 overflow-x-auto flex-1 flex flex-col justify-between">
            <table class="w-full text-left border-collapse text-[11px]">
                <thead>
                    <tr class="bg-slate-50/90 text-gray-500 text-[10px] uppercase font-bold border-b border-gray-100">
                        <th class="py-2 px-2.5">Sub-Sistem</th>
                        <th class="py-2 px-1.5 text-center">Tot</th>
                        <th class="py-2 px-1.5 text-center">Aktif</th>
                        <th class="py-2 px-2 text-right">Stdby</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-sans">
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-2 px-2.5 font-medium text-gray-700">Gate Truk</td>
                        <td class="py-2 px-1.5 text-center font-mono font-semibold text-gray-800">139</td>
                        <td class="py-2 px-1.5 text-center font-mono text-emerald-600 font-bold">135</td>
                        <td class="py-2 px-2 text-right font-mono text-gray-400">4</td>
                    </tr>
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-2 px-2.5 font-medium text-gray-700">Timbang 80T</td>
                        <td class="py-2 px-1.5 text-center font-mono font-semibold text-gray-800">139</td>
                        <td class="py-2 px-1.5 text-center font-mono text-emerald-600 font-bold">139</td>
                        <td class="py-2 px-2 text-right font-mono text-gray-400">0</td>
                    </tr>
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-2 px-2.5 font-medium text-gray-700">Siding KA</td>
                        <td class="py-2 px-1.5 text-center font-mono font-semibold text-gray-800">30</td>
                        <td class="py-2 px-1.5 text-center font-mono text-purple-600 font-bold">24</td>
                        <td class="py-2 px-2 text-right font-mono text-gray-400">6</td>
                    </tr>
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-2 px-2.5 font-medium text-gray-700">CFS Gudang</td>
                        <td class="py-2 px-1.5 text-center font-mono font-semibold text-gray-800">12</td>
                        <td class="py-2 px-1.5 text-center font-mono text-indigo-600 font-bold">8</td>
                        <td class="py-2 px-2 text-right font-mono text-gray-400">4</td>
                    </tr>
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-2 px-2.5 font-medium text-gray-700">Reefer 380V</td>
                        <td class="py-2 px-1.5 text-center font-mono font-semibold text-gray-800">300</td>
                        <td class="py-2 px-1.5 text-center font-mono text-cyan-600 font-bold">48</td>
                        <td class="py-2 px-2 text-right font-mono text-gray-400">252</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Micro Footer Bar -->
        <div class="px-3 py-1.5 bg-slate-50/70 border-t border-gray-100 flex items-center justify-between text-[10px] text-gray-400">
            <span>Status Gateway</span>
            <span class="text-emerald-700 font-semibold">5 Sub-Sistem OK</span>
        </div>
    </div>

    <!-- CARD 4: STATUS ARMADA HEAVY FLEET (xl:col-span-2) -->
    <div class="xl:col-span-2 bg-white rounded-lg shadow-2xs border border-[#E4DFD3] flex flex-col justify-between overflow-hidden">
        <!-- Card Navy Header Bar -->
        <div class="bg-[#1B2D47] text-white px-3 py-2.5 flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-dolly text-brass text-[12px]"></i>
                <h3 class="text-[11.5px] font-bold tracking-wider uppercase text-paper">Status Armada</h3>
            </div>
            <span class="text-[10px] font-mono text-paper/60">4 Unit</span>
        </div>

        <!-- Equipment List with Green Progress Bars (Ref: media_1791395233559.png) -->
        <?php
            $fleet_items = !empty($db_stats['equipment']) ? $db_stats['equipment'] : [
                ['equipment_id' => 'RS-01', 'brand_model' => 'Kalmar 45T', 'operator_name' => 'Dani S.', 'status' => 'Stacking', 'fuel_percent' => 88, 'hours_today' => 6.2],
                ['equipment_id' => 'RS-02', 'brand_model' => 'Sany 45T', 'operator_name' => 'Budi T.', 'status' => 'Transfer', 'fuel_percent' => 76, 'hours_today' => 5.8],
                ['equipment_id' => 'RTG-01', 'brand_model' => 'ZPMC 16W', 'operator_name' => 'Yanto P.', 'status' => 'Siding KA', 'fuel_percent' => 82, 'hours_today' => 7.4],
                ['equipment_id' => 'RS-03', 'brand_model' => 'Kalmar 45T', 'operator_name' => 'Eko W.', 'status' => 'Standby', 'fuel_percent' => 94, 'hours_today' => 3.1]
            ];
        ?>
        <div class="p-3 space-y-2.5 flex-1 flex flex-col justify-around">
            <?php foreach ($fleet_items as $eq): 
                $eq_id = htmlspecialchars($eq['equipment_id'] ?? 'EQ-01');
                $eq_type = htmlspecialchars($eq['brand_model'] ?? 'Reach Stacker');
                $eq_op = htmlspecialchars($eq['operator_name'] ?? 'Operator');
                $eq_stat = htmlspecialchars(ucwords($eq['status'] ?? 'operating'));
                $util_pct = (int)($eq['fuel_percent'] ?? 85);
            ?>
            <div class="space-y-1">
                <div class="flex items-center justify-between text-[11px]">
                    <span class="font-bold text-gray-800 font-mono flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full <?= $eq_stat === 'Standby' ? 'bg-blue-400' : 'bg-emerald-500' ?>"></span>
                        <?= $eq_id ?>
                    </span>
                    <span class="text-[10px] text-gray-500"><?= $eq_stat ?></span>
                </div>
                <div class="flex items-center justify-between text-[9.5px] text-gray-400">
                    <span class="truncate"><?= $eq_op ?></span>
                    <span class="font-mono font-semibold text-emerald-700"><?= $util_pct ?>%</span>
                </div>
                <!-- Horizontal Green Progress Bar (Ref: Image progress bars) -->
                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-emerald-500 h-1.5 rounded-full transition-all" style="width: <?= $util_pct ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Micro Footer Bar -->
        <div class="px-3 py-1.5 bg-slate-50/70 border-t border-gray-100 flex items-center justify-between text-[10px] text-gray-400">
            <span>Kesiapan Heavy Fleet</span>
            <span class="text-emerald-700 font-semibold font-mono">100% Ready</span>
        </div>
    </div>

</div>

<!-- ========================================================================= -->
<!-- ROW 2: PERFORMA & TREN THROUGHPUT YARD & PRODUKTIVITAS TARIF LAYANAN     -->
<!-- ========================================================================= -->
<div class="grid grid-cols-1 xl:grid-cols-12 gap-3.5 sm:gap-4 items-stretch">

    <!-- CARD 5: PERFORMA & TREN THROUGHPUT YARD (7 HARI) (xl:col-span-7) -->
    <div class="xl:col-span-7 bg-white rounded-lg shadow-2xs border border-[#E4DFD3] flex flex-col justify-between overflow-hidden">
        <!-- Card Navy Header Bar -->
        <div class="bg-[#1B2D47] text-white px-3.5 py-2.5 flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-brass text-[12px]"></i>
                <h3 class="text-[11.5px] font-bold tracking-wider uppercase text-paper">Performa &amp; Tren Throughput Yard (7 Hari)</h3>
            </div>
            <span class="text-[10px] text-paper/60 font-mono">Throughput Siklus In/Out</span>
        </div>

        <div class="p-3 sm:p-4 space-y-3 flex-1 flex flex-col justify-between">
            <!-- 4 Colored KPI Stat Boxes (Ref: Top Stat Boxes in Reference Image) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                <!-- Box 1: Light Green Tint -->
                <div class="bg-emerald-50/70 border border-emerald-200/80 p-2 sm:p-2.5 rounded-md flex flex-col justify-between">
                    <span class="text-[9.5px] font-bold text-emerald-800 uppercase tracking-wider leading-tight">Total Throughput</span>
                    <div class="mt-1">
                        <div class="text-base sm:text-lg font-extrabold text-emerald-950 font-mono leading-none">27,33 M</div>
                        <span class="text-[9px] text-emerald-700 font-medium">+14.2% Rencana</span>
                    </div>
                </div>

                <!-- Box 2: Light Red/Amber Tint -->
                <div class="bg-amber-50/70 border border-amber-200/80 p-2 sm:p-2.5 rounded-md flex flex-col justify-between">
                    <span class="text-[9.5px] font-bold text-amber-800 uppercase tracking-wider leading-tight">Rata-rata TAT Truk</span>
                    <div class="mt-1">
                        <div class="text-base sm:text-lg font-extrabold text-amber-950 font-mono leading-none">12.48 mnt</div>
                        <span class="text-[9px] text-amber-700 font-medium">Target &lt; 15 mnt</span>
                    </div>
                </div>

                <!-- Box 3: Light Blue Tint -->
                <div class="bg-blue-50/70 border border-blue-200/80 p-2 sm:p-2.5 rounded-md flex flex-col justify-between">
                    <span class="text-[9.5px] font-bold text-blue-800 uppercase tracking-wider leading-tight">Kepatuhan SOLAS</span>
                    <div class="mt-1">
                        <div class="text-base sm:text-lg font-extrabold text-blue-950 font-mono leading-none">21,69 M</div>
                        <span class="text-[9px] text-blue-700 font-medium">VGM 80T Valid</span>
                    </div>
                </div>

                <!-- Box 4: Light Purple Tint -->
                <div class="bg-purple-50/70 border border-purple-200/80 p-2 sm:p-2.5 rounded-md flex flex-col justify-between">
                    <span class="text-[9.5px] font-bold text-purple-800 uppercase tracking-wider leading-tight">Akurasi Sensor</span>
                    <div class="mt-1">
                        <div class="text-base sm:text-lg font-extrabold text-purple-950 font-mono leading-none">95.55%</div>
                        <span class="text-[9px] text-purple-700 font-medium">BOM 26 Hardware</span>
                    </div>
                </div>
            </div>

            <!-- Wave / Spline Area Line Chart Container -->
            <div class="h-44 sm:h-48 relative w-full flex-1">
                <canvas id="gateTrendChart"></canvas>
            </div>

            <!-- Chart Baseline Indicators -->
            <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-[10px] text-gray-500">
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-[#1B2D47]"></span>Throughput Aktual</span>
                    <span class="flex items-center gap-1.5"><span class="w-2 h-0.5 bg-[#B8955A]"></span>Target Operasi</span>
                </div>
                <span class="text-emerald-700 font-bold font-mono">Efisiensi Siklus: 98.4%</span>
            </div>
        </div>
    </div>

    <!-- CARD 6: PRODUKTIVITAS & TARIF LAYANAN DRY PORT (xl:col-span-5) -->
    <div class="xl:col-span-5 bg-white rounded-lg shadow-2xs border border-[#E4DFD3] flex flex-col justify-between overflow-hidden">
        <!-- Card Navy Header Bar -->
        <div class="bg-[#1B2D47] text-white px-3.5 py-2.5 flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-sack-dollar text-brass text-[12px]"></i>
                <h3 class="text-[11.5px] font-bold tracking-wider uppercase text-paper">Produktivitas &amp; Tarif Layanan Dry Port</h3>
            </div>
            <span class="text-[10px] text-paper/60 font-mono">BOM Tarif 2026</span>
        </div>

        <!-- Tariff & Commercial Matrix with Green Progress Bars (Ref: media_1791395233559.png) -->
        <div class="overflow-x-auto flex-1 flex flex-col justify-between">
            <table class="w-full text-left border-collapse min-w-[420px]">
                <thead>
                    <tr class="bg-slate-50/90 text-gray-500 text-[10px] uppercase font-bold border-b border-gray-100">
                        <th class="py-2 px-2.5">Kode / Tarif</th>
                        <th class="py-2 px-2">Layanan Terminal</th>
                        <th class="py-2 px-2 text-center">Volume</th>
                        <th class="py-2 px-2 text-right">Omzet</th>
                        <th class="py-2 px-2.5 text-right w-24">Utilisasi</th>
                    </tr>
                </thead>
                <tbody class="text-[11px] divide-y divide-gray-100 font-sans">
                    <!-- 1. Lift-On / Lift-Off -->
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-2 px-2.5 font-mono text-[10px] text-gray-500 font-semibold">TRF-LOLO</td>
                        <td class="py-2 px-2 font-medium text-gray-800">Lift-On / Lift-Off (RS)</td>
                        <td class="py-2 px-2 font-mono text-[10px] text-center text-gray-600">1.100 Mv</td>
                        <td class="py-2 px-2 font-mono text-[10.5px] text-right font-bold text-gray-900">Rp 485,0 Jt</td>
                        <td class="py-2 px-2.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <span class="font-mono text-[9.5px] font-bold text-emerald-700">33.29%</span>
                                <div class="w-12 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: 33.29%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- 2. Storage Penumpukan -->
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-2 px-2.5 font-mono text-[10px] text-gray-500 font-semibold">TRF-STOR</td>
                        <td class="py-2 px-2 font-medium text-gray-800">Storage Yard Blok A-C</td>
                        <td class="py-2 px-2 font-mono text-[10px] text-center text-gray-600">850 TEU</td>
                        <td class="py-2 px-2 font-mono text-[10.5px] text-right font-bold text-gray-900">Rp 320,0 Jt</td>
                        <td class="py-2 px-2.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <span class="font-mono text-[9.5px] font-bold text-emerald-700">35.65%</span>
                                <div class="w-12 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: 35.65%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- 3. Reefer Cold Chain -->
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-2 px-2.5 font-mono text-[10px] text-gray-500 font-semibold">TRF-REEF</td>
                        <td class="py-2 px-2 font-medium text-gray-800">Reefer Plug 380V Monitor</td>
                        <td class="py-2 px-2 font-mono text-[10px] text-center text-gray-600">48 Box</td>
                        <td class="py-2 px-2 font-mono text-[10.5px] text-right font-bold text-gray-900">Rp 185,0 Jt</td>
                        <td class="py-2 px-2.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <span class="font-mono text-[9.5px] font-bold text-emerald-700">25.25%</span>
                                <div class="w-12 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: 25.25%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- 4. Rail Haulage Siding -->
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-2 px-2.5 font-mono text-[10px] text-gray-500 font-semibold">TRF-HAUL</td>
                        <td class="py-2 px-2 font-medium text-gray-800">Haulage Siding KA Daop 1</td>
                        <td class="py-2 px-2 font-mono text-[10px] text-center text-gray-600">24 PPCW</td>
                        <td class="py-2 px-2 font-mono text-[10.5px] text-right font-bold text-gray-900">Rp 640,0 Jt</td>
                        <td class="py-2 px-2.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <span class="font-mono text-[9.5px] font-bold text-emerald-700">24.35%</span>
                                <div class="w-12 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: 24.35%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- 5. CFS Gudang -->
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-2 px-2.5 font-mono text-[10px] text-gray-500 font-semibold">TRF-CFS</td>
                        <td class="py-2 px-2 font-medium text-gray-800">CFS Stripping &amp; Stuffing</td>
                        <td class="py-2 px-2 font-mono text-[10px] text-center text-gray-600">32 Job</td>
                        <td class="py-2 px-2 font-mono text-[10.5px] text-right font-bold text-gray-900">Rp 210,0 Jt</td>
                        <td class="py-2 px-2.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <span class="font-mono text-[9.5px] font-bold text-emerald-700">15.87%</span>
                                <div class="w-12 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: 15.87%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- 6. Weighbridge VGM Solas -->
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-2 px-2.5 font-mono text-[10px] text-gray-500 font-semibold">TRF-VGM</td>
                        <td class="py-2 px-2 font-medium text-gray-800">Weighbridge 80T Solas</td>
                        <td class="py-2 px-2 font-mono text-[10px] text-center text-gray-600">139 Truk</td>
                        <td class="py-2 px-2 font-mono text-[10.5px] text-right font-bold text-gray-900">Rp 69,5 Jt</td>
                        <td class="py-2 px-2.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <span class="font-mono text-[9.5px] font-bold text-emerald-700">18.80%</span>
                                <div class="w-12 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: 18.80%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Micro Footer Bar -->
        <div class="px-3 py-1.5 bg-slate-50/70 border-t border-gray-100 flex items-center justify-between text-[10px] text-gray-400">
            <span>Total Akumulasi Billing</span>
            <span class="font-mono font-bold text-gray-800">Rp 1.909,5 Jt Paid</span>
        </div>
    </div>

</div>

<!-- ========================================================================= -->
<!-- ROW 3: AUDIT LOG OPERASIONAL, NOTIFIKASI IOT & KONSUMSI BBM/ENERGI       -->
<!-- ========================================================================= -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-12 gap-3.5 sm:gap-4 items-stretch">

    <!-- CARD 7: AUDIT LOG OPERASIONAL (xl:col-span-4) -->
    <div class="xl:col-span-4 bg-white rounded-lg shadow-2xs border border-[#E4DFD3] flex flex-col justify-between overflow-hidden">
        <!-- Card Navy Header Bar -->
        <div class="bg-[#1B2D47] text-white px-3.5 py-2.5 flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-brass text-[12px]"></i>
                <h3 class="text-[11.5px] font-bold tracking-wider uppercase text-paper">Audit Log Operasional</h3>
            </div>
            <span class="text-[10px] text-paper/60 font-mono">Live Stream</span>
        </div>

        <!-- Chronological Stream (Ref: media_1791395233559.png) -->
        <?php
            $events_feed = !empty($db_stats['recent_events']) ? $db_stats['recent_events'] : [];
            $sample_events = [
                ['event_type' => 'RAIL_ARRIVAL', 'container_number' => 'KA 2518', 'created_at' => date('Y-m-d 13:30:00'), 'notes' => 'Rangkaian KA 2518 tiba di Siding Track-01 (126 as roda lolos sensor Frauscher)', 'operator_name' => 'KAI Logistik'],
                ['event_type' => 'GATE_IN', 'container_number' => 'B 9481 UEK', 'created_at' => date('Y-m-d 13:10:00'), 'notes' => 'Gate-In lolos timbangan Avery 80T (VGM 28.4 Ton SOLAS terverifikasi)', 'operator_name' => 'Gate In 01'],
                ['event_type' => 'RELOCATION', 'container_number' => 'MSKU9182374', 'created_at' => date('Y-m-d 13:00:00'), 'notes' => 'RS-01 relokasi kontainer ke Blok B-08-03 Tier 2', 'operator_name' => 'Dani S. (RS-01)'],
                ['event_type' => 'CUSTOMS_SPPB', 'container_number' => 'PIB-091244', 'created_at' => date('Y-m-d 12:45:00'), 'notes' => 'Bea Cukai CEISA 4.0 terbitkan SPPB Rilis jalur hijau', 'operator_name' => 'CEISA Auto API'],
                ['event_type' => 'REEFER_PULSE', 'container_number' => 'CMAU7234910', 'created_at' => date('Y-m-d 12:15:00'), 'notes' => 'Reefer Plug #04 aktif catu daya 380V (Suhu stabil -20.2°C)', 'operator_name' => 'SHT40 LoRa']
            ];
            while (count($events_feed) < 5 && !empty($sample_events)) {
                $events_feed[] = array_shift($sample_events);
            }
            $events_feed = array_slice($events_feed, 0, 5);
        ?>
        <div class="p-3 sm:p-3.5 space-y-2.5 flex-1 flex flex-col justify-around">
            <?php foreach ($events_feed as $ef): 
                $time_fmt = !empty($ef['created_at']) ? date('H:i', strtotime($ef['created_at'])) : '13:00';
                $notes_str = htmlspecialchars($ef['notes'] ?? 'Aktivitas lapangan terminal dry port tercatat.');
                if (mb_strlen($notes_str) > 72) {
                    $notes_str = mb_substr($notes_str, 0, 70) . '...';
                }
                $op_str = htmlspecialchars($ef['operator_name'] ?? 'Sistem');
            ?>
            <div class="flex items-start gap-2.5 text-[11px]">
                <span class="font-mono font-semibold text-gray-500 text-[10.5px] flex-shrink-0 pt-0.5"><?= $time_fmt ?></span>
                <div class="w-1.5 h-1.5 rounded-full bg-blue-500 mt-1.5 flex-shrink-0"></div>
                <div class="min-w-0 flex-1">
                    <p class="text-gray-800 leading-snug"><?= $notes_str ?></p>
                    <span class="text-[9.5px] text-gray-400 font-mono"><?= $op_str ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Micro Footer Bar -->
        <div class="px-3 py-1.5 bg-slate-50/70 border-t border-gray-100 text-center">
            <a href="dashboard.php?page=kontainer" class="text-[10px] font-bold text-[#0170b9] hover:underline inline-flex items-center gap-1">
                <span>Lihat Riwayat Audit Lengkap</span>
                <i class="fa-solid fa-arrow-right text-[8px]"></i>
            </a>
        </div>
    </div>

    <!-- CARD 8: NOTIFIKASI TELEMETRI IOT (xl:col-span-4) -->
    <div class="xl:col-span-4 bg-white rounded-lg shadow-2xs border border-[#E4DFD3] flex flex-col justify-between overflow-hidden">
        <!-- Card Navy Header Bar -->
        <div class="bg-[#1B2D47] text-white px-3.5 py-2.5 flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-bell text-brass text-[12px]"></i>
                <h3 class="text-[11.5px] font-bold tracking-wider uppercase text-paper">Notifikasi Telemetri IoT</h3>
            </div>
            <span class="text-[10px] text-paper/60 font-mono">4 Gateway</span>
        </div>

        <!-- 4 Structured Telemetry Alert Cards (Ref: media_1791395233559.png) -->
        <div class="p-3 space-y-2 flex-1 flex flex-col justify-around text-[11px]">
            <!-- Alert 1: Reefer -->
            <div class="p-2 rounded-md border border-amber-200/60 bg-amber-50/40 flex items-start gap-2.5">
                <span class="w-2 h-2 rounded-full bg-amber-500 mt-1 flex-shrink-0"></span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between text-[10.5px]">
                        <span class="font-bold text-gray-800">Sensor Suhu SHT40 LoRa</span>
                        <span class="text-[9px] text-gray-400 font-mono">42 dtk lalu</span>
                    </div>
                    <p class="text-[10px] text-gray-600 mt-0.5">Reefer Zone Plug #04 suhu -20.2&deg;C (Warning limit di -15&deg;C).</p>
                </div>
            </div>

            <!-- Alert 2: Jembatan Timbang -->
            <div class="p-2 rounded-md border border-emerald-200/60 bg-emerald-50/40 flex items-start gap-2.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 mt-1 flex-shrink-0"></span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between text-[10.5px]">
                        <span class="font-bold text-gray-800">Timbangan Avery 80T VGM</span>
                        <span class="text-[9px] text-gray-400 font-mono">14 mnt lalu</span>
                    </div>
                    <p class="text-[10px] text-gray-600 mt-0.5">Kalibrasi load cell 80 Ton zero-drift verified (Toleransi &plusmn;0.2%).</p>
                </div>
            </div>

            <!-- Alert 3: Siding KA -->
            <div class="p-2 rounded-md border border-blue-200/60 bg-blue-50/40 flex items-start gap-2.5">
                <span class="w-2 h-2 rounded-full bg-blue-500 mt-1 flex-shrink-0"></span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between text-[10.5px]">
                        <span class="font-bold text-gray-800">Rel Siding KA Daop 1</span>
                        <span class="text-[9px] text-gray-400 font-mono">35 mnt lalu</span>
                    </div>
                    <p class="text-[10px] text-gray-600 mt-0.5">Axle counter Frauscher deteksi 126 as roda CC 206 (30 PPCW).</p>
                </div>
            </div>

            <!-- Alert 4: CEISA 4.0 -->
            <div class="p-2 rounded-md border border-purple-200/60 bg-purple-50/40 flex items-start gap-2.5">
                <span class="w-2 h-2 rounded-full bg-purple-500 mt-1 flex-shrink-0"></span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between text-[10.5px]">
                        <span class="font-bold text-gray-800">Bea Cukai CEISA 4.0</span>
                        <span class="text-[9px] text-gray-400 font-mono">1 jam lalu</span>
                    </div>
                    <p class="text-[10px] text-gray-600 mt-0.5">Host-to-Host REST API sinkronisasi 14 deklarasi PIB jalur hijau.</p>
                </div>
            </div>
        </div>

        <!-- Micro Footer Bar -->
        <div class="px-3 py-1.5 bg-slate-50/70 border-t border-gray-100 flex items-center justify-between text-[10px] text-gray-400">
            <span>Sensor Protocol</span>
            <span class="text-emerald-700 font-semibold font-mono">MQTT &bull; Modbus &bull; LoRaWAN</span>
        </div>
    </div>

    <!-- CARD 9: KONSUMSI BAHAN BAKAR & ENERGI ALAT (xl:col-span-4) -->
    <div class="xl:col-span-4 bg-white rounded-lg shadow-2xs border border-[#E4DFD3] flex flex-col justify-between overflow-hidden">
        <!-- Card Navy Header Bar -->
        <div class="bg-[#1B2D47] text-white px-3.5 py-2.5 flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-gas-pump text-brass text-[12px]"></i>
                <h3 class="text-[11.5px] font-bold tracking-wider uppercase text-paper">Konsumsi BBM &amp; Energi Alat</h3>
            </div>
            <span class="text-[10px] text-paper/60 font-mono">SCADA RT</span>
        </div>

        <div class="p-3 sm:p-3.5 space-y-2 flex-1 flex flex-col justify-between">
            <!-- 3 Mini Metric Summary Badges (Ref: media_1791395233559.png) -->
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="p-1.5 bg-slate-50 rounded border border-gray-100">
                    <span class="text-[9px] text-gray-400 block font-semibold uppercase">Total Solar</span>
                    <span class="text-xs sm:text-sm font-bold text-gray-900 font-mono">1.455 L</span>
                </div>
                <div class="p-1.5 bg-slate-50 rounded border border-gray-100">
                    <span class="text-[9px] text-gray-400 block font-semibold uppercase">Tertinggi</span>
                    <span class="text-xs sm:text-sm font-bold text-amber-700 font-mono">13,13 L/J</span>
                </div>
                <div class="p-1.5 bg-slate-50 rounded border border-gray-100">
                    <span class="text-[9px] text-gray-400 block font-semibold uppercase">Daya KWh</span>
                    <span class="text-xs sm:text-sm font-bold text-emerald-700 font-mono">50.000</span>
                </div>
            </div>

            <!-- Vertical Bar Chart for Equipment Consumption -->
            <div class="h-36 sm:h-40 relative w-full flex-1">
                <canvas id="fuelChart"></canvas>
            </div>

            <!-- Micro Legend -->
            <div class="pt-1.5 border-t border-gray-100 flex items-center justify-between text-[9.5px] text-gray-500">
                <span>Efisiensi Mesin: <strong>Stage IV/V Low Emission</strong></span>
                <span class="font-mono text-emerald-600 font-semibold">&minus;12% BBM</span>
            </div>
        </div>
    </div>

</div>

<!-- Chart.js Initializations for Beranda (Ref: media_1791395233559.png) -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Chart.defaults.font.family = '"Plus Jakarta Sans", sans-serif';
        Chart.defaults.font.size = 10;
        Chart.defaults.color = '#64748b';
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 27, 45, 0.94)';
        Chart.defaults.plugins.tooltip.padding = 8;
        Chart.defaults.plugins.tooltip.cornerRadius = 6;
        Chart.defaults.plugins.tooltip.titleFont = { size: 11, weight: 'bold' };
        Chart.defaults.plugins.tooltip.bodyFont = { size: 10 };
        
        // 1. Wave Spline Line/Area Chart: Tren Throughput Yard
        const gateCtx = document.getElementById('gateTrendChart');
        if (gateCtx) {
            const ctx2d = gateCtx.getContext('2d');
            const gradientThroughput = ctx2d.createLinearGradient(0, 0, 0, 180);
            gradientThroughput.addColorStop(0, 'rgba(27, 45, 71, 0.35)');
            gradientThroughput.addColorStop(1, 'rgba(27, 45, 71, 0.0)');

            new Chart(gateCtx, {
                type: 'line',
                data: {
                    labels: ['01', '03', '05', '07', '09', '11', '13', '15', '17', '19', '21', '23', '25', '27', '29', '31'],
                    datasets: [
                        {
                            label: 'Throughput Aktual',
                            data: [3, 2, 4, 3, 2, 3, 2, 19, 2, 18, 3, 19, 2, 18, 3, 20],
                            borderColor: '#1B2D47',
                            backgroundColor: gradientThroughput,
                            fill: true,
                            tension: 0.42,
                            borderWidth: 2,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#1B2D47',
                            pointBorderWidth: 1.5,
                            pointRadius: 3,
                            pointHoverRadius: 5
                        },
                        {
                            label: 'Target Operasi',
                            data: [5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5],
                            borderColor: '#B8955A',
                            backgroundColor: 'transparent',
                            fill: false,
                            borderDash: [4, 4],
                            borderWidth: 1.5,
                            pointRadius: 0,
                            pointHoverRadius: 3
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
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.dataset.label + ': ' + context.raw + ' Ribu TEU';
                                }
                            }
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            max: 25,
                            grid: { borderDash: [2, 4], color: '#f1f5f9' },
                            ticks: { 
                                font: { size: 9 },
                                callback: function(value) { return value + 'K'; }
                            }
                        },
                        x: { 
                            grid: { display: false },
                            ticks: { font: { size: 9 } }
                        }
                    }
                }
            });
        }

        // 2. Doughnut Chart: Distribusi Kapasitas & Tipe Yard
        const typeCtx = document.getElementById('containerTypeChart');
        if (typeCtx) {
            new Chart(typeCtx, {
                type: 'doughnut',
                data: {
                    labels: [
                        'Blok A (35%)', 
                        'Blok B (28.5%)', 
                        'Blok C (18.2%)', 
                        'Reefer (12.3%)', 
                        'DG IMO (6%)'
                    ],
                    datasets: [{
                        data: [35.0, 28.5, 18.2, 12.3, 6.0],
                        backgroundColor: ['#0170b9', '#4f46e5', '#3b82f6', '#06b6d4', '#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: { display: false },
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
                        const centerY = (chartArea.top + chartArea.bottom) / 2 - 6;

                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';

                        ctx.font = "bold 0.85rem 'Plus Jakarta Sans', sans-serif";
                        ctx.fillStyle = "#0f172a";
                        ctx.fillText("1.420 TEU", centerX, centerY - 4);

                        ctx.font = "600 0.55rem 'Plus Jakarta Sans', sans-serif";
                        ctx.fillStyle = "#64748b";
                        ctx.fillText("Total Stacking", centerX, centerY + 8);
                        ctx.restore();
                    }
                }]
            });
        }

        // 3. Vertical Bar Chart: Konsumsi Bahan Bakar Alat Berat
        const fuelCtx = document.getElementById('fuelChart');
        if (fuelCtx) {
            new Chart(fuelCtx, {
                type: 'bar',
                data: {
                    labels: ['RS-01', 'RS-02', 'RS-03', 'RTG-01', 'PM-01', 'Genset'],
                    datasets: [
                        {
                            label: 'Konsumsi Solar & Energi',
                            data: [1280, 1100, 940, 890, 680, 520],
                            backgroundColor: [
                                '#1B2D47',
                                '#25446B',
                                '#3A5578',
                                '#4B6B94',
                                '#6082AC',
                                '#7A9DC6'
                            ],
                            borderRadius: { topLeft: 4, topRight: 4, bottomLeft: 0, bottomRight: 0 }
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.dataset.label + ': ' + context.raw + ' Liter';
                                }
                            }
                        }
                    },
                    scales: {
                        x: { 
                            grid: { display: false },
                            ticks: { font: { size: 9 } }
                        },
                        y: { 
                            beginAtZero: true, 
                            grid: { borderDash: [2, 4], color: '#f1f5f9' },
                            ticks: {
                                font: { size: 8.5 },
                                callback: function(value) {
                                    return value + ' L';
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
