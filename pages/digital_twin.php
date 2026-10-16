<?php
/**
 * Conclusion Intermodal Dry Port (CIDP) 35 Ha
 * Modul: Real-Time 2D Digital Twin Monitoring Console (Read-Only)
 * File: pages/digital_twin.php
 * 
 * Memantau 3 Aliran Sinkron secara Real-Time:
 * 1. Aliran Fisik (Kanvas 2D Spasial Arsitektural SVG Master Plan)
 * 2. Aliran Data / Informasi (Telemetri RFID, OCR, CEISA 4.0, IoT Reefer)
 * 3. Aliran Finansial (Argo Akrual Tarif, LoLo, VGM, Dwell Time, Invoice)
 */

require_once __DIR__ . '/layout_master.php';
require_once __DIR__ . '/../api/twin_state_helper.php';

// Ambil snapshot awal state
$twin_initial = get_live_twin_state($pdo, false);
?>

<div class="space-y-4 font-sans text-slate-800 animate-fadeIn">
    
    <!-- =================================================================== -->
    <!-- 1. TOP HEADER BAR: STATUS ENGINE, KONEKSI REAL-TIME, QUICK TRIGGER -->
    <!-- =================================================================== -->
    <div class="bg-white/95 backdrop-blur-md rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200/90 relative overflow-hidden flex flex-col xl:flex-row items-start xl:items-center justify-between gap-4">
        <!-- Subtle Vector Batik Watermark -->
        <div class="absolute -right-6 -top-6 w-36 h-36 pointer-events-none opacity-[0.06] z-0" aria-hidden="true">
            <svg width="100%" height="100%" viewBox="0 0 100 100" fill="none" stroke="#004b87" stroke-width="1.6">
                <ellipse cx="50" cy="22" rx="16" ry="24" />
                <ellipse cx="50" cy="78" rx="16" ry="24" />
                <ellipse cx="22" cy="50" rx="24" ry="16" />
                <ellipse cx="78" cy="50" rx="24" ry="16" />
                <circle cx="50" cy="50" r="4.5" fill="#ea580c" />
            </svg>
        </div>

        <div class="relative z-10">
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-orange-100 text-orange-700 border border-orange-200">
                    <i class="fa-solid fa-satellite-dish animate-pulse"></i> Passive Digital Twin
                </span>
                <span id="twinConnectionBadge" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                    <span id="twinConnectionText">SSE Stream Live</span>
                </span>
                <span class="text-[11px] font-mono text-slate-400">
                    Latensi: <strong id="latencyDisplay" class="text-slate-600 font-bold">&lt; 35ms</strong>
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1">
                Dashboard Digital Twin 2D <span class="text-orange-600">Monitoring Real-Time</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5 max-w-2xl leading-relaxed">
                Pemantauan murni (read-only) tiga pilar serentak: <strong>Aliran Fisik</strong> (posisi spasial koordinat alat berat & slot), <strong>Aliran Data</strong> (RFID, OCR, CEISA 4.0), dan <strong>Aliran Finansial</strong> (akrual tarif otomatis).
            </p>
        </div>

        <!-- Right Quick Controls: Edge Telemetry Simulator Panel Trigger -->
        <div class="relative z-10 flex flex-wrap items-center gap-2 w-full xl:w-auto">
            <button type="button" onclick="runSimulatorAutoCycle()" id="btnAutoCycle" class="px-3.5 py-2 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs shadow-xs transition-all flex items-center gap-2 transform active:scale-95">
                <i class="fa-solid fa-play text-xs"></i>
                <span>Jalankan Siklus Nyata (Auto Run)</span>
            </button>
            <div class="relative inline-block text-left">
                <button type="button" onclick="toggleSimStepDropdown()" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs border border-slate-300/80 transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-sliders text-xs text-slate-500"></i>
                    <span>Pemicu Perangkat</span>
                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                </button>
                <div id="simStepDropdown" class="hidden absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-slate-200 p-2 z-50 space-y-1 text-xs">
                    <div class="px-3 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        Injeksi Paket Telemetri Hardware:
                    </div>
                    <button type="button" onclick="triggerSimStep(1)" class="w-full text-left px-3 py-2 rounded-lg hover:bg-orange-50 text-slate-700 hover:text-orange-600 font-medium flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-orange-100 text-orange-600 font-bold text-[10px] flex items-center justify-center">1</span>
                        <span>Truk Tiba di Gate In (OCR & RFID)</span>
                    </button>
                    <button type="button" onclick="triggerSimStep(2)" class="w-full text-left px-3 py-2 rounded-lg hover:bg-orange-50 text-slate-700 hover:text-orange-600 font-medium flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-blue-100 text-blue-600 font-bold text-[10px] flex items-center justify-center">2</span>
                        <span>Penimbangan 80T (VGM Tiket + Rp 75k)</span>
                    </button>
                    <button type="button" onclick="triggerSimStep(3)" class="w-full text-left px-3 py-2 rounded-lg hover:bg-orange-50 text-slate-700 hover:text-orange-600 font-medium flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-amber-100 text-amber-600 font-bold text-[10px] flex items-center justify-center">3</span>
                        <span>RS-02 Angkat Kontainer (+ Rp 325k)</span>
                    </button>
                    <button type="button" onclick="triggerSimStep(4)" class="w-full text-left px-3 py-2 rounded-lg hover:bg-orange-50 text-slate-700 hover:text-orange-600 font-medium flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-purple-100 text-purple-600 font-bold text-[10px] flex items-center justify-center">4</span>
                        <span>RS-02 Tumpuk di Blok B (B-08-03-02)</span>
                    </button>
                    <button type="button" onclick="triggerSimStep(5)" class="w-full text-left px-3 py-2 rounded-lg hover:bg-orange-50 text-slate-700 hover:text-orange-600 font-medium flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-emerald-100 text-emerald-600 font-bold text-[10px] flex items-center justify-center">5</span>
                        <span>CEISA 4.0 DJBC: SPPB Cleared</span>
                    </button>
                    <button type="button" onclick="triggerSimStep(6)" class="w-full text-left px-3 py-2 rounded-lg hover:bg-orange-50 text-slate-700 hover:text-orange-600 font-medium flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-green-100 text-green-700 font-bold text-[10px] flex items-center justify-center">6</span>
                        <span>Pelunasan Bank Mandiri H2H (Lunas)</span>
                    </button>
                    <div class="border-t border-slate-100 pt-1">
                        <button type="button" onclick="resetSimulatorState()" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-red-50 text-red-600 font-bold flex items-center gap-2">
                            <i class="fa-solid fa-rotate-left text-xs"></i>
                            <span>Reset Status Simulasi</span>
                        </button>
                    </div>
                </div>
            </div>
            <button type="button" onclick="resetSimulatorState()" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors" title="Reset State Benchmark">
                <i class="fa-solid fa-rotate-right text-xs"></i>
            </button>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- 2. MAIN LAYOUT GRID: KANVAS 2D (KIRI 70%) & PANEL ALIRAN (KANAN 30%)-->
    <!-- =================================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
        
        <!-- KOLOM KIRI (LG:COL-8): KANVAS 2D SPASIAL ARSITEKTURAL -->
        <div class="lg:col-span-8 bg-white rounded-2xl p-3 sm:p-4 shadow-xs border border-slate-200/90 relative overflow-hidden flex flex-col justify-between min-h-[620px]">
            
            <!-- Canvas Toolbar: Zoom, Coordinates, Layer Indicators -->
            <div class="flex items-center justify-between pb-3 mb-2 border-b border-slate-100 flex-wrap gap-2 text-xs">
                <div class="flex items-center gap-2 font-mono">
                    <span class="text-orange-600 font-bold uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-layer-group"></i> 2D SPATIAL SITE PLAN 35 HA
                    </span>
                    <span class="text-slate-300">|</span>
                    <span class="text-slate-500 text-[11px]">Skala 1:2.000</span>
                    <span class="text-slate-300">|</span>
                    <span id="canvasGpsHover" class="text-slate-400 text-[11px] font-mono">GPS: (X: --, Y: --)</span>
                </div>

                <!-- Floating Canvas Controls -->
                <div class="flex items-center gap-1">
                    <button type="button" onclick="zoomCanvas(1.2)" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs" title="Perbesar">+</button>
                    <button type="button" onclick="zoomCanvas(0.8)" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs" title="Perkecil">-</button>
                    <button type="button" onclick="resetCanvasView()" class="px-2 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold" title="Reset Tampilan">Fit View</button>
                    <span id="zoomLevelDisplay" class="text-[11px] font-mono text-slate-400 pl-1">100%</span>
                </div>
            </div>

            <!-- THE INTERACTIVE 2D SVG CANVAS WRAPPER -->
            <div id="svgCanvasContainer" class="relative w-full h-[520px] bg-slate-900 rounded-xl overflow-hidden border border-slate-800 cursor-grab active:cursor-grabbing select-none" onmousemove="handleCanvasMouseMove(event)">
                
                <svg id="twinSvg" viewBox="0 0 1200 760" class="w-full h-full transition-transform duration-75 ease-out">
                    <defs>
                        <!-- Fine Blueprint Engineering Grid -->
                        <pattern id="gridFine" width="10" height="10" patternUnits="userSpaceOnUse">
                            <path d="M 10 0 L 0 0 0 10" fill="none" stroke="#1e293b" stroke-width="0.3" />
                        </pattern>
                        <pattern id="gridMajor" width="50" height="50" patternUnits="userSpaceOnUse">
                            <path d="M 50 0 L 0 0 0 50" fill="none" stroke="#334155" stroke-width="0.5" />
                        </pattern>
                        
                        <!-- Concrete Yard Hatch -->
                        <pattern id="patConcrete" width="6" height="6" patternTransform="rotate(45)" patternUnits="userSpaceOnUse">
                            <line x1="0" y1="0" x2="0" y2="6" stroke="#334155" stroke-width="0.4" />
                        </pattern>

                        <!-- Marker Arrow -->
                        <marker id="arrowHead" markerWidth="6" markerHeight="6" refX="3" refY="3" orient="auto">
                            <path d="M 0 0 L 6 3 L 0 6 z" fill="#ea580c" />
                        </marker>
                    </defs>

                    <!-- Layer 1: Background & Engineering Grids -->
                    <rect width="100%" height="100%" fill="#0b1120" />
                    <rect width="100%" height="100%" fill="url(#gridFine)" />
                    <rect width="100%" height="100%" fill="url(#gridMajor)" />

                    <!-- Perimeter Fencing (Pagar 35 Ha) -->
                    <rect x="40" y="40" width="1120" height="680" fill="none" stroke="#475569" stroke-width="2" stroke-dasharray="6 3" />
                    <text x="50" y="32" fill="#64748b" font-size="10" font-family="monospace">PAGAR PERIMETER KAWASAN 35 HA (380m x 260m)</text>

                    <!-- Internal Road Circulation (Haul Roads) -->
                    <g id="haulRoads" fill="none" stroke="#1e293b" stroke-width="32" stroke-linejoin="round">
                        <!-- Main loop road -->
                        <path d="M 90 120 L 1110 120 L 1110 660 L 90 660 Z" />
                        <!-- Central access road -->
                        <path d="M 600 120 L 600 660" stroke-width="28" />
                        <!-- Cross connectors -->
                        <path d="M 90 380 L 1110 380" stroke-width="24" />
                    </g>
                    <!-- Road Centerlines -->
                    <g fill="none" stroke="#38bdf8" stroke-width="1.2" stroke-dasharray="8 6" opacity="0.4">
                        <path d="M 90 120 L 1110 120 L 1110 660 L 90 660 Z" />
                        <path d="M 600 120 L 600 660" />
                        <path d="M 90 380 L 1110 380" />
                    </g>

                    <!-- ======================================================= -->
                    <!-- ZONA 1: GATE & TIMBANGAN 80T (TIMUR LAUT / ATAS KANAN) -->
                    <!-- ======================================================= -->
                    <g id="zoneGate">
                        <!-- Gate complex footprint -->
                        <rect x="740" y="55" width="220" height="85" fill="#0f172a" stroke="#0284c7" stroke-width="1.5" rx="6" />
                        <text x="750" y="73" fill="#38bdf8" font-size="11" font-weight="bold">GATE COMPLEX (4 LANES)</text>
                        
                        <!-- Lane 1: Inbound (with barrier) -->
                        <rect x="755" y="82" width="85" height="22" fill="#1e293b" stroke="#334155" rx="3" />
                        <text x="760" y="96" fill="#94a3b8" font-size="9" font-family="monospace">LANE 1 IN (OCR)</text>
                        <line id="gateBarrierIn" x1="840" y1="80" x2="840" y2="105" stroke="#ef4444" stroke-width="4" />

                        <!-- Lane 2: Outbound (with barrier) -->
                        <rect x="860" y="82" width="85" height="22" fill="#1e293b" stroke="#334155" rx="3" />
                        <text x="865" y="96" fill="#94a3b8" font-size="9" font-family="monospace">LANE 2 OUT</text>
                        <line id="gateBarrierOut" x1="945" y1="80" x2="945" y2="105" stroke="#ef4444" stroke-width="4" />

                        <!-- Jembatan Timbang 80 Ton (Weighbridge Gantry) -->
                        <rect id="weighbridgePlat" x="755" y="112" width="190" height="24" fill="#1e293b" stroke="#f59e0b" stroke-width="1.5" rx="3" />
                        <text x="762" y="128" fill="#fbbf24" font-size="10" font-weight="bold">WEIGHBRIDGE 80 TON (SOLAS VGM)</text>
                        <!-- Digital Scale Meter on Canvas -->
                        <rect x="955" y="112" width="70" height="24" fill="#020617" stroke="#fbbf24" rx="3" />
                        <text id="svgWeighDisplay" x="990" y="128" fill="#fbbf24" font-size="10" font-family="monospace" font-weight="bold" text-anchor="middle">0.00 T</text>
                    </g>

                    <!-- ======================================================= -->
                    <!-- ZONA 2: CONTAINER YARD PENUMPUKAN (BLOK A, B, C, D, E) -->
                    <!-- ======================================================= -->
                    <!-- BLOK A (Laden Ekspor) -->
                    <g id="blockYardA" transform="translate(180, 160)">
                        <rect width="180" height="180" fill="#0f172a" stroke="#3b82f6" stroke-width="1.5" rx="5" />
                        <text x="10" y="20" fill="#60a5fa" font-size="11" font-weight="bold">BLOK A (LADEN EKSPOR)</text>
                        <text x="10" y="34" fill="#64748b" font-size="9" font-family="monospace">Bay 01-12 • 4-Tier</text>
                        <!-- Container Slots Visual Grid -->
                        <g id="slotsBlockA" transform="translate(10, 42)">
                            <!-- Render dynamic grid slots -->
                        </g>
                    </g>

                    <!-- BLOK B (Laden Impor) -->
                    <g id="blockYardB" transform="translate(400, 160)">
                        <rect width="180" height="180" fill="#0f172a" stroke="#0ea5e9" stroke-width="1.5" rx="5" />
                        <text x="10" y="20" fill="#38bdf8" font-size="11" font-weight="bold">BLOK B (LADEN IMPOR)</text>
                        <text x="10" y="34" fill="#64748b" font-size="9" font-family="monospace">Bay 01-12 • 4-Tier</text>
                        <g id="slotsBlockB" transform="translate(10, 42)">
                            <!-- Render dynamic grid slots -->
                        </g>
                    </g>

                    <!-- BLOK C (Domestik) -->
                    <g id="blockYardC" transform="translate(180, 380)">
                        <rect width="180" height="180" fill="#0f172a" stroke="#8b5cf6" stroke-width="1.5" rx="5" />
                        <text x="10" y="20" fill="#a78bfa" font-size="11" font-weight="bold">BLOK C (DOMESTIK)</text>
                        <text x="10" y="34" fill="#64748b" font-size="9" font-family="monospace">Bay 01-12 • 4-Tier</text>
                        <g id="slotsBlockC" transform="translate(10, 42)"></g>
                    </g>

                    <!-- BLOK D (Buffer / Transshipment) -->
                    <g id="blockYardD" transform="translate(400, 380)">
                        <rect width="180" height="180" fill="#0f172a" stroke="#6366f1" stroke-width="1.5" rx="5" />
                        <text x="10" y="20" fill="#818cf8" font-size="11" font-weight="bold">BLOK D (BUFFER YARD)</text>
                        <text x="10" y="34" fill="#64748b" font-size="9" font-family="monospace">Bay 01-12 • 4-Tier</text>
                        <g id="slotsBlockD" transform="translate(10, 42)"></g>
                    </g>

                    <!-- REEFER COLD CHAIN HUB (300 Plugs) -->
                    <g id="blockYardReefer" transform="translate(680, 160)">
                        <rect width="180" height="180" fill="#0f172a" stroke="#06b6d4" stroke-width="1.5" rx="5" />
                        <text x="10" y="20" fill="#22d3ee" font-size="11" font-weight="bold">REEFER COLD CHAIN ❄️</text>
                        <text x="10" y="34" fill="#64748b" font-size="9" font-family="monospace">120 Smart Plugs 380V</text>
                        <g id="slotsBlockReefer" transform="translate(10, 42)"></g>
                    </g>

                    <!-- BLOK E: DANGEROUS GOODS (DG BUNDED YARD) -->
                    <g id="blockYardDG" transform="translate(680, 380)">
                        <rect width="180" height="180" fill="#0f172a" stroke="#ef4444" stroke-width="1.5" rx="5" />
                        <text x="10" y="20" fill="#f87171" font-size="11" font-weight="bold">BLOK E: DG YARD ⚠️</text>
                        <text x="10" y="34" fill="#64748b" font-size="9" font-family="monospace">IMO Class Bunded Zone</text>
                        <g id="slotsBlockDG" transform="translate(10, 42)"></g>
                    </g>

                    <!-- ======================================================= -->
                    <!-- ZONA 3: FASILITAS CFS WAREHOUSE, M&R & KANTOR (BARAT)   -->
                    <!-- ======================================================= -->
                    <!-- CFS WAREHOUSE (Gudang Konsolidasi LCL) -->
                    <g id="facCFS" transform="translate(60, 200)">
                        <rect width="90" height="200" fill="#0f172a" stroke="#10b981" stroke-width="1.5" rx="4" />
                        <text x="45" y="25" fill="#34d399" font-size="10" font-weight="bold" text-anchor="middle">CFS</text>
                        <text x="45" y="40" fill="#34d399" font-size="8" text-anchor="middle">WAREHOUSE</text>
                        <text x="45" y="100" fill="#64748b" font-size="8" text-anchor="middle">Cross-Dock</text>
                        <text x="45" y="115" fill="#64748b" font-size="8" text-anchor="middle">GS1 Pallet</text>
                    </g>

                    <!-- M&R WORKSHOP KALMAR -->
                    <g id="facWorkshop" transform="translate(60, 80)">
                        <rect width="90" height="90" fill="#0f172a" stroke="#f97316" stroke-width="1.5" rx="4" />
                        <text x="45" y="25" fill="#fb923c" font-size="10" font-weight="bold" text-anchor="middle">WORKSHOP</text>
                        <text x="45" y="40" fill="#fb923c" font-size="8" text-anchor="middle">M&R KALMAR</text>
                    </g>

                    <!-- GEDUNG KANTOR ADMINISTRASI & BEA CUKAI (UTARA) -->
                    <g id="facHQ" transform="translate(320, 55)">
                        <rect width="240" height="50" fill="#0f172a" stroke="#8b5cf6" stroke-width="1.5" rx="4" />
                        <text x="120" y="25" fill="#c084fc" font-size="10" font-weight="bold" text-anchor="middle">GEDUNG KANTOR UTAMA & CEISA 4.0</text>
                        <text x="120" y="40" fill="#94a3b8" font-size="8" text-anchor="middle">NOC Datacenter • Bea Cukai • Perbankan</text>
                    </g>

                    <!-- ======================================================= -->
                    <!-- ZONA 4: INTERMODAL RAIL SIDING PASOSO (SELATAN / BAWAH) -->
                    <!-- ======================================================= -->
                    <g id="zoneRailSiding" transform="translate(120, 600)">
                        <!-- Dual Track Foundation -->
                        <rect width="960" height="50" fill="#020617" stroke="#eab308" stroke-width="1.5" rx="4" />
                        <!-- Railway Sleepers (Tie Marks) -->
                        <line x1="20" y1="18" x2="940" y2="18" stroke="#cbd5e1" stroke-width="2" />
                        <line x1="20" y1="32" x2="940" y2="32" stroke="#cbd5e1" stroke-width="2" />
                        <?php for ($rx = 30; $rx < 930; $rx += 20): ?>
                            <line x1="<?= $rx ?>" y1="12" x2="<?= $rx ?>" y2="38" stroke="#64748b" stroke-width="1.5" />
                        <?php endfor; ?>
                        <text x="480" y="46" fill="#fde047" font-size="10" font-weight="bold" text-anchor="middle">JALUR GANDA KERETA API LOGISTIK PASOSO (RANGKAIAN 60 TEU KE TANJUNG PRIOK)</text>

                        <!-- Train Locomotive & Wagons Visual -->
                        <g id="trainSprite" transform="translate(60, 10)">
                            <!-- Locomotive -->
                            <rect width="45" height="18" fill="#ea580c" rx="3" stroke="#fff" stroke-width="0.8" />
                            <circle cx="8" cy="9" r="3" fill="#fde047" />
                            <text x="24" y="13" fill="#fff" font-size="7" font-weight="bold">CC206</text>
                            <!-- Flatcar 1 with Container -->
                            <rect x="52" y="3" width="55" height="12" fill="#1e293b" stroke="#64748b" rx="2" />
                            <rect x="54" y="4" width="51" height="10" fill="#2563eb" rx="1" />
                            <text x="79" y="12" fill="#fff" font-size="6" text-anchor="middle">TEMU4910</text>
                            <!-- Flatcar 2 with Container -->
                            <rect x="112" y="3" width="55" height="12" fill="#1e293b" stroke="#64748b" rx="2" />
                            <rect x="114" y="4" width="51" height="10" fill="#059669" rx="1" />
                            <text x="139" y="12" fill="#fff" font-size="6" text-anchor="middle">MSKU2201</text>
                        </g>
                    </g>

                    <!-- ======================================================= -->
                    <!-- DYNAMIC SPRITES LAYER (ANIMATED REACH STACKERS & TRUCK)  -->
                    <!-- ======================================================= -->
                    <!-- DYNAMIC TRUCK SPRITE (Bergerak di Gerbang & Timbangan) -->
                    <g id="spriteTruck" transform="translate(800, 112)" class="transition-all duration-300">
                        <rect width="52" height="18" fill="#0284c7" stroke="#fff" stroke-width="1" rx="3" />
                        <rect x="36" y="2" width="14" height="14" fill="#0f172a" rx="2" />
                        <!-- Windshield -->
                        <rect x="42" y="4" width="6" height="10" fill="#7dd3fc" rx="1" />
                        <!-- Plate Text -->
                        <text id="spriteTruckPlate" x="20" y="12" fill="#fff" font-size="7" font-weight="bold" text-anchor="middle">B 9812 UIX</text>
                    </g>

                    <!-- DYNAMIC REACH STACKER RS-01 -->
                    <g id="spriteRS01" transform="translate(150, 200)" class="transition-all duration-300">
                        <circle cx="0" cy="0" r="16" fill="#f97316" stroke="#fff" stroke-width="2" class="shadow-lg" />
                        <text x="0" y="4" fill="#fff" font-size="9" font-weight="bold" text-anchor="middle">RS1</text>
                        <line x1="0" y1="0" x2="22" y2="0" stroke="#f97316" stroke-width="4" stroke-linecap="round" />
                    </g>

                    <!-- DYNAMIC REACH STACKER RS-02 (ACTIVE DEMO UNIT) -->
                    <g id="spriteRS02" transform="translate(350, 300)" class="transition-all duration-300">
                        <circle cx="0" cy="0" r="17" fill="#ea580c" stroke="#fff" stroke-width="2.5" />
                        <text x="0" y="4" fill="#fff" font-size="9" font-weight="extrabold" text-anchor="middle">RS2</text>
                        <!-- Telescopic Boom -->
                        <line id="rs02Boom" x1="0" y1="0" x2="26" y2="0" stroke="#ea580c" stroke-width="4.5" stroke-linecap="round" />
                        <!-- Carried Container Spreader Box -->
                        <g id="rs02Container" transform="translate(20, -12)">
                            <rect width="36" height="24" fill="#2563eb" stroke="#fbbf24" stroke-width="1.5" rx="2" />
                            <text x="18" y="15" fill="#fff" font-size="7" font-weight="bold" text-anchor="middle">MSKU</text>
                        </g>
                    </g>

                    <!-- DYNAMIC REACH STACKER RS-03 -->
                    <g id="spriteRS03" transform="translate(720, 240)" class="transition-all duration-300">
                        <circle cx="0" cy="0" r="16" fill="#0284c7" stroke="#fff" stroke-width="2" />
                        <text x="0" y="4" fill="#fff" font-size="9" font-weight="bold" text-anchor="middle">RS3</text>
                        <line x1="0" y1="0" x2="22" y2="0" stroke="#0284c7" stroke-width="4" stroke-linecap="round" />
                    </g>

                    <!-- DYNAMIC RTG CRANE 01 -->
                    <g id="spriteRTG01" transform="translate(900, 595)">
                        <rect x="-20" y="-15" width="40" height="30" fill="none" stroke="#eab308" stroke-width="3" rx="2" />
                        <text x="0" y="4" fill="#fde047" font-size="8" font-weight="bold" text-anchor="middle">RTG-01</text>
                    </g>
                </svg>

                <!-- Tooltip floating over Canvas -->
                <div id="canvasTooltip" class="hidden absolute pointer-events-none bg-slate-900/90 text-white text-[11px] px-3 py-2 rounded-xl shadow-xl border border-slate-700 backdrop-blur-md z-30 font-mono">
                    Tooltip info
                </div>
            </div>

            <!-- Canvas Bottom Legend -->
            <div class="mt-3 pt-2.5 border-t border-slate-100 flex flex-wrap items-center justify-between text-xs text-slate-500 gap-2">
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-blue-600 inline-block"></span> Dry 40HC</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-cyan-500 inline-block"></span> Reefer (-18°C)</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-red-500 inline-block"></span> Dangerous Goods</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-emerald-500 inline-block"></span> SPPB Cleared</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-orange-500 inline-block"></span> Reach Stacker</span>
                </div>
                <div class="text-[11px] font-mono text-slate-400">
                    Posisi: <span class="text-orange-600 font-bold">WGS84 Real GPS Grid</span>
                </div>
            </div>

        </div>

        <!-- KOLOM KANAN (LG:COL-4): DUA PANEL SINKRONISASI (INFORMASI & FINANSIAL) -->
        <div class="lg:col-span-4 space-y-4">
            
            <!-- PANEL 1: ALIRAN DATA / INFORMASI (TELEMETRI & AUDIT PABEAN) -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200/90 relative overflow-hidden">
                <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-microchip text-blue-600"></i>
                        <span>Aliran Data & Telemetri</span>
                    </h3>
                    <span class="text-[10px] font-mono text-blue-600 bg-blue-50 px-2 py-0.5 rounded font-bold">PILAR 2</span>
                </div>

                <div class="space-y-3 text-xs">
                    
                    <!-- Scanner Card: ANPR & RFID Gate Inbound -->
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex flex-col gap-1.5">
                        <div class="flex items-center justify-between text-[11px] font-mono text-slate-500">
                            <span class="font-bold text-slate-700">SCANNER GERBANG INBOUND</span>
                            <span id="ocrStatusBadge" class="text-emerald-600 font-bold"><i class="fa-solid fa-circle-check"></i> OCR MATCH 99%</span>
                        </div>
                        <div class="flex items-center justify-between font-mono">
                            <span class="text-slate-500 text-[11px]">Truk / Pelat:</span>
                            <span id="liveTruckPlate" class="font-bold text-slate-900 text-sm">B 9812 UIX</span>
                        </div>
                        <div class="flex items-center justify-between font-mono">
                            <span class="text-slate-500 text-[11px]">Boks Kontainer:</span>
                            <span id="liveContainerNo" class="font-bold text-blue-600 text-sm">MSKU9182374</span>
                        </div>
                        <div class="flex items-center justify-between font-mono text-[11px]">
                            <span class="text-slate-500">Berat Timbangan:</span>
                            <span id="liveWeightKg" class="font-bold text-amber-600">34.850 kg (VGM Valid)</span>
                        </div>
                    </div>

                    <!-- CEISA 4.0 Bea Cukai Validation Card -->
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex flex-col gap-1.5">
                        <div class="flex items-center justify-between text-[11px] font-mono">
                            <span class="font-bold text-slate-700">INTEGRASI CEISA 4.0 DJBC</span>
                            <span id="ceisaSyncTime" class="text-slate-400">Sync: Real-time</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-[11px]">Persetujuan Pengeluaran:</span>
                            <span id="ceisaStatusBadge" class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-700 border border-emerald-200">
                                SPPB CLEARED ✓
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] font-mono text-slate-500">
                            <span>Tingkat Kepatuhan:</span>
                            <span id="ceisaCompliancePct" class="font-bold text-slate-800">100% Zero Defect</span>
                        </div>
                    </div>

                    <!-- Reefer Telematics Cold Chain -->
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-slate-700 text-[11px]">REEFER MONITORING IOT</div>
                            <div class="text-[10px] text-slate-500 mt-0.5">Suhu kargo rata-rata presisi</div>
                        </div>
                        <div class="text-right">
                            <div id="reeferTempDisplay" class="text-base font-extrabold font-mono text-cyan-600">-18.4 °C</div>
                            <div class="text-[9.5px] text-emerald-600 font-bold">120 Plug Mandiri</div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- PANEL 2: ALIRAN FINANSIAL & BILLING ENGINE LIVE -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-950 text-white rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-800 relative overflow-hidden">
                <div class="flex items-center justify-between mb-3 border-b border-slate-800 pb-2">
                    <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice-dollar text-amber-400"></i>
                        <span>Aliran Finansial & Billing</span>
                    </h3>
                    <span class="text-[10px] font-mono text-amber-300 bg-amber-950/80 px-2 py-0.5 rounded border border-amber-800/60 font-bold">PILAR 3</span>
                </div>

                <!-- Grand Total Accrued Revenue Today -->
                <div class="mb-4">
                    <div class="text-[11px] font-mono text-slate-400 uppercase tracking-wider">Total Akrual Pendapatan Berjalan:</div>
                    <div id="financialTotalRevenue" class="text-2xl sm:text-3xl font-black text-amber-300 font-mono tracking-tight my-1">
                        Rp <?= number_format($twin_initial['financial']['total_revenue_accrued_idr'], 0, ',', '.') ?>
                    </div>
                    <div class="text-[10px] text-slate-400 flex items-center gap-1.5">
                        <i class="fa-solid fa-bolt text-amber-400"></i>
                        <span>Terhitung otomatis per pergerakan sensor fisik</span>
                    </div>
                </div>

                <!-- 4 Tariff Breakdown Chips -->
                <div class="grid grid-cols-2 gap-2 text-xs mb-4">
                    <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                        <div class="text-[10px] text-slate-400 font-mono">Jasa Timbang 80T:</div>
                        <div id="finWeighFee" class="text-sm font-bold font-mono text-white mt-0.5">
                            Rp <?= number_format($twin_initial['financial']['weighbridge_fee_idr'], 0, ',', '.') ?>
                        </div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                        <div class="text-[10px] text-slate-400 font-mono">Lift-Off / Lift-On:</div>
                        <div id="finLoLoFee" class="text-sm font-bold font-mono text-white mt-0.5">
                            Rp <?= number_format($twin_initial['financial']['liftoff_fee_idr'], 0, ',', '.') ?>
                        </div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                        <div class="text-[10px] text-slate-400 font-mono">Dwell Time Yard:</div>
                        <div id="finStorageFee" class="text-sm font-bold font-mono text-white mt-0.5">
                            Rp <?= number_format($twin_initial['financial']['storage_dwell_fee_idr'], 0, ',', '.') ?>
                        </div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                        <div class="text-[10px] text-slate-400 font-mono">Daya Reefer 380V:</div>
                        <div id="finReeferFee" class="text-sm font-bold font-mono text-white mt-0.5">
                            Rp <?= number_format($twin_initial['financial']['reefer_power_fee_idr'], 0, ',', '.') ?>
                        </div>
                    </div>
                </div>

                <!-- Settlement Status Progress -->
                <div class="pt-2 border-t border-white/10">
                    <div class="flex items-center justify-between text-[11px] font-mono text-slate-400 mb-1.5">
                        <span>Status Pelunasan Kasir / Bank:</span>
                        <span id="settlementPctDisplay" class="font-bold text-emerald-400"><?= $twin_initial['financial']['settlement_ratio_pct'] ?>% Lunas</span>
                    </div>
                    <div class="w-full bg-white/10 h-2 rounded-full overflow-hidden">
                        <div id="settlementBar" class="h-full bg-gradient-to-r from-emerald-500 to-amber-400 transition-all duration-500" style="width: <?= $twin_initial['financial']['settlement_ratio_pct'] ?>%;"></div>
                    </div>
                    <div class="flex items-center justify-between text-[10px] font-mono text-slate-400 mt-2">
                        <span>Lunas: <strong id="paidAmountDisplay" class="text-white">Rp <?= number_format($twin_initial['financial']['paid_amount_idr'], 0, ',', '.') ?></strong></span>
                        <span>Piutang: <strong id="pendingAmountDisplay" class="text-amber-300">Rp <?= number_format($twin_initial['financial']['pending_amount_idr'], 0, ',', '.') ?></strong></span>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- =================================================================== -->
    <!-- 3. BOTTOM FULL-WIDTH: LIVE HARDWARE BUS & EVENT LOG STREAM TICKER   -->
    <!-- =================================================================== -->
    <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-200/90 relative overflow-hidden">
        <div class="flex items-center justify-between mb-2">
            <div class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-network-wired text-orange-600"></i>
                <span>Hardware Event Bus & Activity Log (Real-Time Ingestion Feed)</span>
            </div>
            <div class="text-[11px] font-mono text-slate-400">
                Pembaruan Terakhir: <span id="lastEventTime" class="text-slate-700 font-bold"><?= date('H:i:s') ?> WIB</span>
            </div>
        </div>

        <div id="hardwareEventStream" class="space-y-1.5 font-mono text-xs max-h-36 overflow-y-auto pr-1">
            <?php foreach (array_slice($twin_initial['information']['recent_events'], 0, 5) as $ev): ?>
                <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200/70 hover:bg-orange-50/50 transition-colors">
                    <div class="flex items-center gap-2.5 truncate">
                        <span class="px-2 py-0.5 rounded text-[9.5px] font-bold uppercase bg-slate-200 text-slate-700">
                            <?= htmlspecialchars($ev['event_type']) ?>
                        </span>
                        <span class="font-bold text-slate-900"><?= htmlspecialchars($ev['container_number'] ?? '-') ?></span>
                        <span class="text-slate-500 truncate"><?= htmlspecialchars($ev['notes'] ?? '') ?></span>
                    </div>
                    <div class="flex items-center gap-3 shrink-0 text-[11px] text-slate-400">
                        <?php if ((float)$ev['billable_amount'] > 0): ?>
                            <span class="text-emerald-600 font-bold">+Rp <?= number_format($ev['billable_amount'], 0, ',', '.') ?></span>
                        <?php endif; ?>
                        <span><?= date('H:i:s', strtotime($ev['created_at'])) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- ======================================================================= -->
<!-- JAVASCRIPT: SSE CLIENT, LERP SMOOTH INTERPOLATION & CANVAS RENDER ENGINE -->
<!-- ======================================================================= -->
<script>
/**
 * 2D Digital Twin Reactive Engine
 * Handles Server-Sent Events (SSE) and Smooth Canvas Updates
 */
let currentTwinState = <?= json_encode($twin_initial, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
let sseConnection = null;
let reconnectTimer = null;
let pollFallbackTimer = null;

// Canvas Pan & Zoom State
let canvasScale = 1.0;
let canvasTranslateX = 0;
let canvasTranslateY = 0;
let isPanning = false;
let panStartX = 0;
let panStartY = 0;

// Lerp Sprites Target Positions for 60fps smoothing
const sprites = {
    truck: { currentX: 800, currentY: 112, targetX: 800, targetY: 112 },
    rs02:  { currentX: 350, currentY: 300, targetX: 350, targetY: 300 }
};

// 1. Initialize EventSource (SSE)
function initTwinStream() {
    if (window.EventSource) {
        try {
            if (sseConnection) sseConnection.close();
            
            const streamUrl = 'api/stream_twin.php';
            sseConnection = new EventSource(streamUrl);

            sseConnection.addEventListener('initial_state', function(e) {
                updateConnectionStatus(true, 'SSE Stream Live');
                const data = JSON.parse(e.data);
                applyTwinStateUpdate(data);
            });

            sseConnection.addEventListener('state_update', function(e) {
                updateConnectionStatus(true, 'SSE Stream Live');
                const data = JSON.parse(e.data);
                applyTwinStateUpdate(data);
            });

            sseConnection.addEventListener('heartbeat', function(e) {
                updateConnectionStatus(true, 'SSE Stream Live');
            });

            sseConnection.onerror = function() {
                updateConnectionStatus(false, 'Reconnecting SSE...');
                sseConnection.close();
                // Schedule reconnect
                clearTimeout(reconnectTimer);
                reconnectTimer = setTimeout(initTwinStream, 2500);
                // Fallback to active polling while reconnecting
                startPollingFallback();
            };

        } catch (err) {
            startPollingFallback();
        }
    } else {
        startPollingFallback();
    }
}

function startPollingFallback() {
    if (pollFallbackTimer) return;
    pollFallbackTimer = setInterval(function() {
        fetch('api/stream_twin.php?poll=1')
            .then(res => res.json())
            .then(data => {
                updateConnectionStatus(true, 'HTTP Polling (Fallback)');
                applyTwinStateUpdate(data);
            })
            .catch(() => {
                updateConnectionStatus(false, 'Disconnected');
            });
    }, 1500);
}

function updateConnectionStatus(online, text) {
    const badge = document.getElementById('twinConnectionBadge');
    const label = document.getElementById('twinConnectionText');
    if (!badge || !label) return;

    if (online) {
        badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-700 border border-emerald-200';
        label.textContent = text || 'SSE Stream Live';
    } else {
        badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-red-100 text-red-700 border border-red-200';
        label.textContent = text || 'Offline';
    }
}

// 2. Apply Unified State Updates across the 3 Flows
function applyTwinStateUpdate(state) {
    if (!state || !state.physical) return;
    currentTwinState = state;

    // --- ALIRAN 1: FISIK (Physical Canvas Sprites & Slots) ---
    // A. Update Truck Position & Status
    const truck = state.physical.trucks && state.physical.trucks[0] ? state.physical.trucks[0] : null;
    const spriteTruck = document.getElementById('spriteTruck');
    const plateText = document.getElementById('spriteTruckPlate');
    const barrierIn = document.getElementById('gateBarrierIn');
    const weighDisplay = document.getElementById('svgWeighDisplay');

    if (truck) {
        if (plateText) plateText.textContent = truck.license_plate;
        if (spriteTruck) spriteTruck.style.display = 'block';

        if (truck.status === 'at_gate') {
            // At gate entrance
            sprites.truck.targetX = 800;
            sprites.truck.targetY = 82;
            if (barrierIn) {
                barrierIn.setAttribute('y2', '80'); // Rotated/lifted open
                barrierIn.setAttribute('stroke', '#22c55e');
            }
        } else if (truck.status === 'in_yard' || truck.status === 'loading') {
            // Weighed & inside yard near Block B
            sprites.truck.targetX = 350;
            sprites.truck.targetY = 240;
            if (barrierIn) {
                barrierIn.setAttribute('y2', '105'); // Closed
                barrierIn.setAttribute('stroke', '#ef4444');
            }
        } else {
            sprites.truck.targetX = 900;
            sprites.truck.targetY = 82;
        }

        // Weighbridge gross display
        if (weighDisplay) {
            const kg = parseFloat(truck.weight_gross || 0);
            weighDisplay.textContent = kg > 0 ? (kg / 1000).toFixed(2) + ' T' : '0.00 T';
        }
    }

    // B. Update RS-02 Position & Spreader Lock
    if (state.physical.equipment && state.physical.equipment['RS-02']) {
        const rs = state.physical.equipment['RS-02'];
        sprites.rs02.targetX = rs.gps_x || 350;
        sprites.rs02.targetY = rs.gps_y || 300;

        const containerBox = document.getElementById('rs02Container');
        if (containerBox) {
            containerBox.style.display = rs.carried_container ? 'block' : 'none';
        }
    }

    // C. Render Stacking Slots (Blok A-E)
    renderBlockSlots(state.physical.active_slots || []);

    // --- ALIRAN 2: INFORMASI (Information Telemetry Feed) ---
    if (truck) {
        const livePlate = document.getElementById('liveTruckPlate');
        const liveBox = document.getElementById('liveContainerNo');
        const liveWeight = document.getElementById('liveWeightKg');
        if (livePlate) livePlate.textContent = truck.license_plate;
        if (liveBox) liveBox.textContent = truck.container_number || 'MSKU9182374';
        if (liveWeight) liveWeight.textContent = truck.weight_gross > 0 ? Number(truck.weight_gross).toLocaleString('id-ID') + ' kg (VGM Valid)' : 'Menunggu Timbang';
    }

    // Customs CEISA
    if (state.information && state.information.customs) {
        const cBadge = document.getElementById('ceisaStatusBadge');
        const cPct = document.getElementById('ceisaCompliancePct');
        if (cPct) cPct.textContent = state.information.customs.clearance_rate_pct + '% Clearance';
        if (cBadge) {
            if (state.information.customs.sppb_cleared > 0) {
                cBadge.className = 'px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-700 border border-emerald-200';
                cBadge.textContent = 'SPPB CLEARED ✓';
            } else {
                cBadge.className = 'px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-amber-100 text-amber-700 border border-amber-200';
                cBadge.textContent = 'AWAITING CUSTOMS';
            }
        }
    }

    // Reefer telematics
    if (state.information && state.information.reefer) {
        const rTemp = document.getElementById('reeferTempDisplay');
        if (rTemp) rTemp.textContent = state.information.reefer.avg_temp_celsius + ' °C';
    }

    // --- ALIRAN 3: FINANSIAL (Accruals & Billing Ledger) ---
    if (state.financial) {
        const finTotal = document.getElementById('financialTotalRevenue');
        const finWeigh = document.getElementById('finWeighFee');
        const finLoLo = document.getElementById('finLoLoFee');
        const finStorage = document.getElementById('finStorageFee');
        const finReefer = document.getElementById('finReeferFee');
        const setPct = document.getElementById('settlementPctDisplay');
        const setBar = document.getElementById('settlementBar');
        const paidDisp = document.getElementById('paidAmountDisplay');
        const pendDisp = document.getElementById('pendingAmountDisplay');

        if (finTotal) finTotal.textContent = 'Rp ' + Number(state.financial.total_revenue_accrued_idr).toLocaleString('id-ID');
        if (finWeigh) finWeigh.textContent = 'Rp ' + Number(state.financial.weighbridge_fee_idr).toLocaleString('id-ID');
        if (finLoLo) finLoLo.textContent = 'Rp ' + Number(state.financial.liftoff_fee_idr).toLocaleString('id-ID');
        if (finStorage) finStorage.textContent = 'Rp ' + Number(state.financial.storage_dwell_fee_idr).toLocaleString('id-ID');
        if (finReefer) finReefer.textContent = 'Rp ' + Number(state.financial.reefer_power_fee_idr).toLocaleString('id-ID');
        
        const pct = state.financial.settlement_ratio_pct || 95.0;
        if (setPct) setPct.textContent = pct + '% Lunas';
        if (setBar) setBar.style.width = pct + '%';
        if (paidDisp) paidDisp.textContent = 'Rp ' + Number(state.financial.paid_amount_idr).toLocaleString('id-ID');
        if (pendDisp) pendDisp.textContent = 'Rp ' + Number(state.financial.pending_amount_idr).toLocaleString('id-ID');
    }

    // Event Bus Activity Ticker
    if (state.information && state.information.recent_events) {
        renderEventTicker(state.information.recent_events);
    }
}

// 3. Render Block Slots Dynamically into SVG
function renderBlockSlots(slots) {
    const blocks = { 'A': 'slotsBlockA', 'B': 'slotsBlockB', 'C': 'slotsBlockC', 'D': 'slotsBlockD', 'E': 'slotsBlockDG', 'REEFER': 'slotsBlockReefer' };
    
    // Clear each slot container
    Object.values(blocks).forEach(id => {
        const el = document.getElementById(id);
        if (el) el.innerHTML = '';
    });

    // Populate active slots
    slots.forEach(slot => {
        const blkId = blocks[slot.block ? slot.block.toUpperCase() : 'A'];
        const container = document.getElementById(blkId);
        if (!container) return;

        const bay = parseInt(slot.bay || '1', 10);
        const row = parseInt(slot.row || '1', 10);
        const x = ((bay - 1) % 4) * 40;
        const y = Math.floor((bay - 1) / 4) * 32;

        let fill = '#2563eb';
        if (slot.type === 'reefer') fill = '#06b6d4';
        else if (slot.type === 'dg') fill = '#dc2626';
        else if (slot.type === 'empty') fill = '#64748b';

        let stroke = '#1d4ed8';
        if (slot.customs === 'SPPB_CLEARED') stroke = '#10b981';

        const g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        g.setAttribute('transform', `translate(${x}, ${y})`);
        g.style.cursor = 'pointer';
        g.onmouseover = (e) => showCanvasTooltip(e, slot);
        g.onmouseout = hideCanvasTooltip;

        g.innerHTML = `
            <rect width="36" height="26" fill="${fill}" stroke="${stroke}" stroke-width="1.5" rx="2" />
            <text x="18" y="16" fill="#fff" font-size="7" font-weight="bold" font-family="monospace" text-anchor="middle">${slot.container_number.substring(0, 4)}</text>
        `;
        container.appendChild(g);
    });
}

function renderEventTicker(events) {
    const stream = document.getElementById('hardwareEventStream');
    if (!stream) return;
    
    stream.innerHTML = events.slice(0, 5).map(ev => `
        <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200/70 hover:bg-orange-50/50 transition-colors">
            <div class="flex items-center gap-2.5 truncate">
                <span class="px-2 py-0.5 rounded text-[9.5px] font-bold uppercase bg-slate-200 text-slate-700">
                    ${ev.event_type}
                </span>
                <span class="font-bold text-slate-900">${ev.container_number || '-'}</span>
                <span class="text-slate-500 truncate">${ev.notes || ''}</span>
            </div>
            <div class="flex items-center gap-3 shrink-0 text-[11px] text-slate-400">
                ${parseFloat(ev.billable_amount) > 0 ? `<span class="text-emerald-600 font-bold">+Rp ${Number(ev.billable_amount).toLocaleString('id-ID')}</span>` : ''}
                <span>${ev.created_at ? ev.created_at.split(' ')[1] : ''}</span>
            </div>
        </div>
    `).join('');

    const lastTime = document.getElementById('lastEventTime');
    if (lastTime && events[0]) {
        lastTime.textContent = events[0].created_at ? events[0].created_at.split(' ')[1] + ' WIB' : '';
    }
}

// 4. Smooth 60 FPS Sprite Lerp Animation Loop
function runLerpAnimationLoop() {
    // Lerp Truck
    sprites.truck.currentX += (sprites.truck.targetX - sprites.truck.currentX) * 0.08;
    sprites.truck.currentY += (sprites.truck.targetY - sprites.truck.currentY) * 0.08;
    const truckEl = document.getElementById('spriteTruck');
    if (truckEl) {
        truckEl.setAttribute('transform', `translate(${sprites.truck.currentX}, ${sprites.truck.currentY})`);
    }

    // Lerp RS-02
    sprites.rs02.currentX += (sprites.rs02.targetX - sprites.rs02.currentX) * 0.08;
    sprites.rs02.currentY += (sprites.rs02.targetY - sprites.rs02.currentY) * 0.08;
    const rs02El = document.getElementById('spriteRS02');
    if (rs02El) {
        rs02El.setAttribute('transform', `translate(${sprites.rs02.currentX}, ${sprites.rs02.currentY})`);
    }

    requestAnimationFrame(runLerpAnimationLoop);
}

// 5. Canvas Zoom & Pan Controls
function zoomCanvas(factor) {
    canvasScale = Math.max(0.6, Math.min(3.0, canvasScale * factor));
    applyCanvasTransform();
}

function resetCanvasView() {
    canvasScale = 1.0;
    canvasTranslateX = 0;
    canvasTranslateY = 0;
    applyCanvasTransform();
}

function applyCanvasTransform() {
    const svg = document.getElementById('twinSvg');
    const disp = document.getElementById('zoomLevelDisplay');
    if (svg) {
        svg.style.transform = `translate(${canvasTranslateX}px, ${canvasTranslateY}px) scale(${canvasScale})`;
    }
    if (disp) {
        disp.textContent = Math.round(canvasScale * 100) + '%';
    }
}

function handleCanvasMouseMove(e) {
    const rect = e.currentTarget.getBoundingClientRect();
    const x = Math.round((e.clientX - rect.left) / canvasScale);
    const y = Math.round((e.clientY - rect.top) / canvasScale);
    const hoverEl = document.getElementById('canvasGpsHover');
    if (hoverEl) {
        hoverEl.textContent = `GPS: (X: ${x}, Y: ${y})`;
    }
}

// Tooltip Helpers
function showCanvasTooltip(e, slot) {
    const tooltip = document.getElementById('canvasTooltip');
    if (!tooltip) return;
    tooltip.innerHTML = `
        <div class="font-bold text-orange-400">${slot.container_number}</div>
        <div class="text-[10px] text-slate-300">Slot: Blok ${slot.block}-${slot.bay}-${slot.row}-${slot.tier}</div>
        <div class="text-[10px] text-slate-300">Tipe: ${slot.type.toUpperCase()} • Berat: ${Number(slot.weight).toLocaleString('id-ID')} kg</div>
        <div class="text-[10px] text-emerald-400 font-bold">Bea Cukai: ${slot.customs}</div>
    `;
    tooltip.classList.remove('hidden');
    tooltip.style.left = (e.pageX + 15) + 'px';
    tooltip.style.top = (e.pageY + 10) + 'px';
}

function hideCanvasTooltip() {
    const tooltip = document.getElementById('canvasTooltip');
    if (tooltip) tooltip.classList.add('hidden');
}

// 6. Interactive Simulator Edge Triggers
function toggleSimStepDropdown() {
    const drop = document.getElementById('simStepDropdown');
    if (drop) drop.classList.toggle('hidden');
}

document.addEventListener('click', function(e) {
    const drop = document.getElementById('simStepDropdown');
    if (drop && !e.target.closest('#simStepDropdown') && !e.target.closest('button[onclick="toggleSimStepDropdown()"]')) {
        drop.classList.add('hidden');
    }
});

function triggerSimStep(stepId) {
    toggleSimStepDropdown();
    fetch('api/edge_simulator.php?action=step&step_id=' + stepId, { method: 'POST' })
        .then(res => res.json())
        .then(data => {
            console.log('Step executed:', data);
        });
}

function runSimulatorAutoCycle() {
    const btn = document.getElementById('btnAutoCycle');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Siklus Sedang Berjalan...</span>';
    }

    let currentStep = 1;
    function runNext() {
        if (currentStep <= 6) {
            fetch('api/edge_simulator.php?action=step&step_id=' + currentStep, { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                    currentStep++;
                    setTimeout(runNext, 2200); // 2.2 second natural pacing between steps
                });
        } else {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-play text-xs"></i> <span>Jalankan Siklus Nyata (Auto Run)</span>';
            }
        }
    }
    runNext();
}

function resetSimulatorState() {
    fetch('api/edge_simulator.php?action=reset', { method: 'POST' })
        .then(res => res.json())
        .then(() => {
            console.log('Simulator state reset.');
        });
}

// Start everything on DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    initTwinStream();
    runLerpAnimationLoop();
    renderBlockSlots(currentTwinState.physical ? currentTwinState.physical.active_slots : []);
});
</script>
