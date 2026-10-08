<?php
// =============================================================================
// FILE: pages/simulator.php
// FUNGSI: Modul Simulasi 3D Virtual Terminal Interaktif (CIDP 3D YMS Engine)
// Mendukung aksi pemindahan Reach Stacker, Gate-In VGM SOLAS, Alih Muat KA,
// Raycasting 3D Inspector, Preset Kamera Sinematik, dan Sinkronisasi MySQL.
// =============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sumber koordinat tunggal tata letak (dipakai bersama denah.php)
require_once __DIR__ . '/layout_master.php';
?>

<!-- Three.js, OrbitControls & Tween.js (Offline Local Vendor dengan CDN Fallback) -->
<script src="assets/vendor/three.min.js"></script>
<script>if (typeof THREE === 'undefined') document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"><\/script>');</script>
<script src="assets/vendor/OrbitControls.js"></script>
<script>if (typeof THREE !== 'undefined' && typeof THREE.OrbitControls === 'undefined') document.write('<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"><\/script>');</script>
<script src="assets/vendor/tween.umd.js"></script>
<script>if (typeof TWEEN === 'undefined') document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/tween.js/18.6.4/tween.umd.js"><\/script>');</script>

<div class="space-y-3.5">
    
    <!-- Top Header Banner (Executive, Clean & Modern) -->
    <div class="bg-gradient-to-r from-[#002f5e] via-[#004b87] to-[#0170b9] rounded-xl p-3.5 sm:p-4 text-white shadow-xs border border-blue-900/30">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <div>
                <div class="flex items-center space-x-2 mb-1">
                    <span class="bg-emerald-500 text-white text-[10px] font-extrabold uppercase px-2 py-0.5 rounded flex items-center tracking-wide shadow-2xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping mr-1.5"></span> LIVE 3D TWIN
                    </span>
                    <span class="bg-white/10 text-blue-100 text-[10px] px-2 py-0.5 rounded font-mono border border-white/15">
                        CIDP 35 Ha Terminal Hub
                    </span>
                    <span class="bg-amber-400/20 text-amber-200 text-[10px] px-2 py-0.5 rounded font-semibold border border-amber-300/30 hidden sm:inline-flex items-center">
                        <i class="fa-solid fa-bolt mr-1 text-[9px]"></i>Real-Time IoT Interlock
                    </span>
                </div>
                <h1 class="text-base sm:text-lg font-bold tracking-tight">Virtual 3D Twin &amp; Konsol Sensor IoT</h1>
            </div>
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <button onclick="runAutoInboundDemo()" id="btnAutoDemo" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center border border-emerald-400/40">
                    <i class="fa-solid fa-play mr-1.5 text-xs"></i>Jalankan Alur Otomatis
                </button>
                <button onclick="openEndToEndGuideModal()" class="px-3 py-1.5 bg-white/15 hover:bg-white/25 text-white font-bold text-xs rounded-lg border border-white/20 transition flex items-center">
                    <i class="fa-solid fa-diagram-project mr-1.5 text-xs text-blue-200"></i>Alur 11 Hardware &amp; Finansial
                </button>
                <button onclick="resetSimulation()" class="px-2.5 py-1.5 bg-white/10 hover:bg-white/20 text-white text-xs rounded-lg border border-white/15 transition flex items-center" title="Reset Posisi Demo">
                    <i class="fa-solid fa-rotate-left mr-1"></i>Reset
                </button>
            </div>
        </div>
    </div>

    <!-- 4 Quick KPI Micro-Cards (Clean & Compact, Positioned at Top like Denah) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5">
        <div class="bg-white rounded-xl p-3 shadow-2xs border border-gray-200/80 flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-[10.5px] font-semibold text-gray-500 uppercase tracking-wide truncate">Box di Yard</p>
                <h4 class="text-xl font-bold text-gray-800 mt-0.5 leading-none" id="statInYard">19 <span class="text-xs font-normal text-gray-400">box</span></h4>
                <p class="text-[10px] text-emerald-600 font-medium mt-1 truncate"><i class="fa-solid fa-check-double mr-1"></i>Sync MySQL</p>
            </div>
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-cdp-blue flex items-center justify-center text-sm shrink-0 ml-2">
                <i class="fa-solid fa-cubes-stacked"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl p-3 shadow-2xs border border-gray-200/80 flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-[10.5px] font-semibold text-gray-500 uppercase tracking-wide truncate">Utilisasi Yard</p>
                <h4 class="text-xl font-bold text-gray-800 mt-0.5 leading-none" id="statYardUtil">23.8%</h4>
                <div class="w-20 bg-gray-100 rounded-full h-1 mt-1.5">
                    <div class="bg-blue-600 h-1 rounded-full" id="statYardBar" style="width: 23.8%"></div>
                </div>
            </div>
            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm shrink-0 ml-2">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl p-3 shadow-2xs border border-gray-200/80 flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-[10.5px] font-semibold text-gray-500 uppercase tracking-wide truncate">Armada SCADA</p>
                <h4 class="text-xl font-bold text-gray-800 mt-0.5 leading-none">4 <span class="text-xs font-normal text-gray-400">unit</span></h4>
                <p class="text-[10px] text-gray-500 mt-1 truncate">3 RS + 1 RTG Crane</p>
            </div>
            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm shrink-0 ml-2">
                <i class="fa-solid fa-dolly"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl p-3 shadow-2xs border border-gray-200/80 flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-[10.5px] font-semibold text-gray-500 uppercase tracking-wide truncate">Kondisi Angin</p>
                <h4 class="text-xl font-bold text-emerald-600 mt-0.5 leading-none">6.4 <span class="text-xs font-normal text-gray-400">m/s</span></h4>
                <p class="text-[10px] text-emerald-600 font-medium mt-1 truncate"><i class="fa-solid fa-shield-halved mr-1"></i>Batas Aman &lt; 20 m/s</p>
            </div>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm shrink-0 ml-2">
                <i class="fa-solid fa-wind"></i>
            </div>
        </div>
    </div>

    <!-- Toolbar Pengendali Navigasi 3D & Sudut Pandang CAD (Pola Bersih Seperti Denah) -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-sm space-y-3.5">
        
        <!-- Row 1: Camera Angle Presets (CAD & Operasional) -->
        <div class="border-b border-gray-100 pb-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                <span class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-compass-drafting text-[#0170b9] mr-2"></i>
                    Sudut Pandang Kamera (CAD &amp; Operasional)
                </span>
                <span class="text-[11px] text-gray-400 hidden sm:inline">Pilih sudut kamera untuk inspeksi atau gunakan zoom dock di kanvas 3D</span>
            </div>
            <div class="flex flex-wrap items-center gap-1.5" id="cameraPresetsToolbar">
                <!-- CAD / Mode Khusus -->
                <button onclick="setCameraView('plan')" class="cam-btn px-2.5 py-1.5 text-xs rounded-xl font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 transition flex items-center gap-1.5" id="btnCamPlan" title="Denah 2D Top-Down Sipil (Sama persis dengan denah.php)">
                    <i class="fa-solid fa-map text-cyan-600 text-xs"></i><span>Denah 2D CAD</span>
                </button>
                <button onclick="setCameraView('iso')" class="cam-btn px-2.5 py-1.5 text-xs rounded-xl font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 transition flex items-center gap-1.5" id="btnCamIso" title="Sudut Isometrik Axonometric 45° BIM">
                    <i class="fa-solid fa-cube text-purple-600 text-xs"></i><span>Isometrik 45°</span>
                </button>
                <button onclick="setCameraView('overview')" class="cam-btn px-2.5 py-1.5 text-xs rounded-xl font-bold bg-[#002f5e] text-white hover:bg-blue-800 transition flex items-center gap-1.5 shadow-xs" id="btnCamOverview" title="Drone 3D Bebas 35 Hektar">
                    <i class="fa-solid fa-satellite text-xs"></i><span>Drone 35 Ha</span>
                </button>
                <div class="h-5 w-px bg-gray-200 mx-1 hidden sm:block"></div>
                <!-- Zona Lapangan -->
                <button onclick="setCameraView('office')" class="cam-btn px-2.5 py-1.5 text-xs rounded-xl font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200 transition flex items-center gap-1.5" id="btnCamOffice" title="Kantor Utama PT MTI &amp; Datacenter NOC (Zona 6 &amp; 7 Utara)">
                    <i class="fa-solid fa-building text-blue-700 text-xs"></i><span>Kantor Utama</span>
                </button>
                <button onclick="setCameraView('gate')" class="cam-btn px-2.5 py-1.5 text-xs rounded-xl font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200 transition flex items-center gap-1.5" id="btnCamGate">
                    <i class="fa-solid fa-archway text-blue-600 text-xs"></i><span>Gate &amp; VGM</span>
                </button>
                <button onclick="setCameraView('yard')" class="cam-btn px-2.5 py-1.5 text-xs rounded-xl font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200 transition flex items-center gap-1.5" id="btnCamYard">
                    <i class="fa-solid fa-boxes-stacked text-amber-600 text-xs"></i><span>Yard 15 Ha</span>
                </button>
                <button onclick="setCameraView('reefer')" class="cam-btn px-2.5 py-1.5 text-xs rounded-xl font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200 transition flex items-center gap-1.5" id="btnCamReefer">
                    <i class="fa-solid fa-snowflake text-cyan-500 text-xs"></i><span>Reefer</span>
                </button>
                <button onclick="setCameraView('rail')" class="cam-btn px-2.5 py-1.5 text-xs rounded-xl font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200 transition flex items-center gap-1.5" id="btnCamRail">
                    <i class="fa-solid fa-train text-rose-600 text-xs"></i><span>Rail Siding</span>
                </button>
                <button onclick="setCameraView('cfs')" class="cam-btn px-2.5 py-1.5 text-xs rounded-xl font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200 transition flex items-center gap-1.5" id="btnCamCfs">
                    <i class="fa-solid fa-warehouse text-emerald-600 text-xs"></i><span>CFS &amp; M&amp;R</span>
                </button>
                <button onclick="setCameraView('customs')" class="cam-btn px-2.5 py-1.5 text-xs rounded-xl font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200 transition flex items-center gap-1.5" id="btnCamCustoms">
                    <i class="fa-solid fa-stamp text-indigo-600 text-xs"></i><span>Bea Cukai</span>
                </button>
                <button onclick="setCameraView('cockpit')" class="cam-btn px-2.5 py-1.5 text-xs rounded-xl font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200 transition flex items-center gap-1.5" id="btnCamCockpit">
                    <i class="fa-solid fa-vr-cardboard text-violet-600 text-xs"></i><span>Kabin RS</span>
                </button>
            </div>
        </div>

        <!-- Row 2: Lighting & Technical CAD Layers & Responsive Height -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <!-- Pencahayaan Waktu -->
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-gray-500 flex items-center"><i class="fa-solid fa-sun mr-1.5 text-amber-500"></i>Pencahayaan:</span>
                <div class="inline-flex rounded-xl p-0.5 bg-gray-100 border border-gray-200">
                    <button onclick="setLightingMode('day')" class="light-btn px-2.5 py-1 text-xs rounded-lg font-bold bg-white text-amber-700 shadow-2xs transition" id="btnLightDay" title="Mode Siang Terang">
                        <i class="fa-solid fa-sun mr-1 text-amber-500"></i>Siang
                    </button>
                    <button onclick="setLightingMode('sunset')" class="light-btn px-2.5 py-1 text-xs rounded-lg font-medium text-gray-600 hover:text-gray-900 transition" id="btnLightSunset" title="Mode Senja Emas">
                        <i class="fa-solid fa-cloud-sun mr-1 text-orange-400"></i>Senja
                    </button>
                    <button onclick="setLightingMode('night')" class="light-btn px-2.5 py-1 text-xs rounded-lg font-medium text-gray-600 hover:text-gray-900 transition" id="btnLightNight" title="Mode Malam &amp; Lampu Sorot Tower">
                        <i class="fa-solid fa-moon mr-1 text-indigo-500"></i>Malam
                    </button>
                </div>
            </div>

            <!-- Layer CAD & Ukuran Layar Dinamis -->
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-gray-500 flex items-center"><i class="fa-solid fa-layer-group mr-1.5 text-blue-500"></i>Utilitas:</span>
                <button onclick="toggleTechnicalDimensions()" class="tech-btn px-2.5 py-1 text-xs rounded-xl font-medium bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition flex items-center gap-1" id="btnToggleDimensions" title="Garis Ukur Dimensi CAD 3D">
                    <i class="fa-solid fa-ruler-combined text-xs"></i><span>Garis Dimensi</span>
                </button>
                <button onclick="toggleCADLayer('gridAxes')" id="cadLayerBtn-gridAxes" class="tech-btn px-2.5 py-1 text-xs rounded-xl font-medium bg-sky-50 text-sky-700 border border-sky-200 hover:bg-sky-100 transition flex items-center gap-1" title="Grid Aksis AS 1-5 / A-E">
                    <i class="fa-solid fa-border-all text-xs"></i><span>Grid As</span>
                </button>
                <button onclick="toggleCADLayer('drainage')" id="cadLayerBtn-drainage" class="tech-btn px-2.5 py-1 text-xs rounded-xl font-medium bg-sky-50 text-sky-700 border border-sky-200 hover:bg-sky-100 transition flex items-center gap-1" title="Drainase U-Ditch">
                    <i class="fa-solid fa-water text-xs"></i><span>Drainase</span>
                </button>
                <button onclick="toggleCADLayer('pavement')" id="cadLayerBtn-pavement" class="tech-btn px-2.5 py-1 text-xs rounded-xl font-medium bg-sky-50 text-sky-700 border border-sky-200 hover:bg-sky-100 transition flex items-center gap-1" title="Perkerasan Beton Kaku">
                    <i class="fa-solid fa-road text-xs"></i><span>Perkerasan</span>
                </button>
                <button onclick="toggleCADLayer('heavyEquipment')" id="cadLayerBtn-heavyEquipment" class="tech-btn px-2.5 py-1 text-xs rounded-xl font-medium bg-sky-50 text-sky-700 border border-sky-200 hover:bg-sky-100 transition flex items-center gap-1" title="Alat Berat (RS/RTG)">
                    <i class="fa-solid fa-truck-monster text-xs"></i><span>Alat Berat</span>
                </button>
                <button onclick="toggleCADTitleBlock()" class="tech-btn px-2.5 py-1 text-xs rounded-xl font-medium bg-gray-100 text-gray-700 border border-gray-200 hover:bg-gray-200 transition flex items-center gap-1" id="btnToggleTitleBlock" title="Etiket Gambar Teknik Sipil">
                    <i class="fa-solid fa-id-card-clip text-xs"></i><span>Etiket CAD</span>
                </button>
                <div class="h-4 w-px bg-gray-200 mx-0.5 hidden sm:block"></div>
                <button onclick="toggleDynamicViewportHeight()" id="btnToggleHeight" class="px-2.5 py-1 text-xs rounded-xl font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 transition flex items-center gap-1.5" title="Perbesar Tinggi Layar 3D (Dynamic Viewport)">
                    <i class="fa-solid fa-up-down-left-right text-xs text-slate-500"></i>
                    <span id="btnHeightText">Layar Tinggi</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 3D Canvas Viewport & Floating HUD System (Dinamis & Bebas Tabrakan) -->
    <div class="relative bg-gray-950 rounded-2xl overflow-hidden shadow-md border border-gray-800 h-[560px] sm:h-[620px] lg:h-[680px] xl:h-[740px] transition-all duration-300" id="simulatorViewportContainer">
        
        <!-- WebGL Canvas Container -->
        <div id="webglCanvas" class="w-full h-full cursor-grab active:cursor-grabbing"></div>

        <!-- Floating Badge: Top Left Live Status & Proyeksi Aktif -->
        <div class="absolute top-3.5 left-3.5 pointer-events-auto flex items-center space-x-2 z-10">
            <span class="bg-slate-900/90 backdrop-blur-md border border-slate-700/80 text-white text-[11px] font-mono px-3 py-1.5 rounded-xl shadow-xl flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="font-bold text-cyan-300" id="cadProjectionLabel">PERSPEKTIF DRONE 3D</span>
            </span>
        </div>

        <!-- Floating HUD: Top Center - Live Operational Workflow Status -->
        <div id="opFlowHUD" class="absolute top-3.5 left-1/2 -translate-x-1/2 pointer-events-auto transition-all duration-300 opacity-0 pointer-events-none z-30 max-w-lg w-[90vw]">
            <div class="bg-slate-900/95 backdrop-blur-xl border border-cyan-500/60 rounded-2xl p-2.5 shadow-2xl text-white">
                <div class="flex items-center justify-between pb-1.5 mb-1.5 border-b border-slate-800 text-xs">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                        <span id="opFlowTitle" class="font-bold tracking-wide uppercase text-cyan-300 font-mono text-[11px]">SIKLUS OPERASIONAL BERJALAN</span>
                    </div>
                    <span id="opFlowStepBadge" class="px-2 py-0.5 rounded-full text-[9.5px] font-mono font-bold bg-blue-600/40 text-blue-300 border border-blue-400/30">TAHAP 1/4</span>
                </div>
                <!-- 4 Step Flow Indicator -->
                <div class="grid grid-cols-4 gap-1 text-center text-[9.5px] font-semibold mb-1.5">
                    <div id="flowStep1" class="py-1 px-0.5 rounded-lg bg-blue-900/80 text-blue-200 border border-blue-500/50">1. Gate-In &amp; VGM</div>
                    <div id="flowStep2" class="py-1 px-0.5 rounded-lg bg-slate-800 text-slate-400 border border-slate-700/50">2. Sirkulasi Yard</div>
                    <div id="flowStep3" class="py-1 px-0.5 rounded-lg bg-slate-800 text-slate-400 border border-slate-700/50">3. Angkat RS</div>
                    <div id="flowStep4" class="py-1 px-0.5 rounded-lg bg-slate-800 text-slate-400 border border-slate-700/50">4. Gate-Out</div>
                </div>
                <div class="flex items-center justify-between text-[10.5px] text-slate-300 bg-slate-950/80 px-2 py-1 rounded-xl border border-slate-800 font-mono">
                    <span id="opFlowDetailText" class="truncate">Truk melintasi jembatan timbang 80T &amp; verifikasi sensor...</span>
                    <span id="opFlowSpeedText" class="text-amber-400 font-bold ml-2 shrink-0">1.25x</span>
                </div>
            </div>
        </div>

        <!-- Floating HUD: Top Left Dynamic 3D Azimuth Compass Rose (Di bawah badge status) -->
        <div id="cadCompassRose" class="absolute top-14 left-3.5 pointer-events-auto bg-slate-900/90 backdrop-blur-md border border-slate-700/80 rounded-xl p-2 shadow-2xl text-center hidden md:flex flex-col items-center justify-center z-10 transition-all duration-300">
            <div class="relative w-11 h-11 flex items-center justify-center">
                <div id="compassDial" class="w-9 h-9 rounded-full border border-slate-600/80 flex items-center justify-center transition-transform duration-75">
                    <div class="w-1 h-7 bg-gradient-to-b from-rose-500 to-slate-400 rounded-full"></div>
                </div>
                <span class="absolute top-0 text-[8px] font-bold font-mono text-rose-400">N</span>
                <span class="absolute bottom-0 text-[8px] font-bold font-mono text-slate-400">S</span>
                <span class="absolute left-0 text-[8px] font-bold font-mono text-slate-400">W</span>
                <span class="absolute right-0 text-[8px] font-bold font-mono text-slate-400">E</span>
            </div>
            <span id="compassHeading" class="text-[9px] font-mono font-bold text-slate-300 mt-0.5">360° N</span>
        </div>

        <!-- Floating HUD: Top Right Dedicated Zoom & Screen Dock (Fitur Zoom Mirip Denah) -->
        <div class="absolute top-3.5 right-3.5 pointer-events-auto flex items-center bg-slate-900/90 backdrop-blur-md border border-slate-700/80 rounded-xl p-1 shadow-2xl z-10 text-white divide-x divide-slate-700/80">
            <!-- Zoom In -->
            <button onclick="simZoom(1)" class="w-8 h-8 flex items-center justify-center text-slate-200 hover:text-white hover:bg-slate-800 rounded-lg transition" title="Perbesar Tampilan / Zoom In (+)">
                <i class="fa-solid fa-plus text-xs"></i>
            </button>
            <!-- Zoom Level Percentage Display -->
            <div class="px-2.5 h-8 flex items-center justify-center font-mono font-bold text-xs text-amber-300 min-w-[54px] select-none" id="simZoomLevelDisplay" title="Tingkat Zoom Kamera">
                100%
            </div>
            <!-- Zoom Out -->
            <button onclick="simZoom(-1)" class="w-8 h-8 flex items-center justify-center text-slate-200 hover:text-white hover:bg-slate-800 rounded-lg transition" title="Perkecil Tampilan / Zoom Out (-)">
                <i class="fa-solid fa-minus text-xs"></i>
            </button>
            <!-- Reset View -->
            <button onclick="resetSimCameraView()" class="w-8 h-8 flex items-center justify-center text-slate-200 hover:text-cyan-300 hover:bg-slate-800 rounded-lg transition" title="Reset Pandangan Standar (↺)">
                <i class="fa-solid fa-rotate-left text-xs"></i>
            </button>
            <!-- Fullscreen Toggle -->
            <button onclick="toggleFullscreen()" class="w-8 h-8 flex items-center justify-center text-slate-200 hover:text-white hover:bg-slate-800 rounded-lg transition" title="Layar Penuh (F11)">
                <i class="fa-solid fa-expand text-xs"></i>
            </button>
        </div>

        <!-- Floating HUD: CAD Engineering Title Block (Etiket Gambar Teknik Sipil) -->
        <div id="cadTitleBlock" class="absolute top-14 right-3.5 pointer-events-auto bg-slate-900/95 backdrop-blur-md border border-slate-700/80 rounded-xl p-3 shadow-2xl text-[10px] font-mono text-slate-300 space-y-1.5 hidden md:block max-w-[280px] z-10 transition-all duration-300">
            <div class="flex items-center justify-between pb-1 border-b border-slate-700 font-bold text-white">
                <span class="text-blue-400 flex items-center gap-1"><i class="fa-solid fa-compass-drafting"></i>CIDP 35 HA TWIN</span>
                <div class="flex items-center gap-1.5">
                    <span class="text-[8.5px] bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 px-1 py-0.2 rounded">AS-BUILT</span>
                    <button onclick="toggleCADTitleBlock()" class="text-slate-400 hover:text-white text-xs px-1" title="Tutup Etiket"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-x-2 gap-y-0.5 text-[9.5px]">
                <div><span class="text-slate-500">DWG NO:</span> <span class="text-white font-bold">CIDP-35HA-2026</span></div>
                <div><span class="text-slate-500">SKALA:</span> <span class="text-amber-400 font-bold">1:2.000</span></div>
                <div><span class="text-slate-500">PROYEKSI:</span> <span id="cadProjectionLabelDetail" class="text-emerald-400 font-bold">PERSPEKTIF 3D</span></div>
                <div><span class="text-slate-500">DATUM:</span> <span class="text-white font-bold">EL +0.00 M</span></div>
                <div class="col-span-2 truncate"><span class="text-slate-500">KOORDINAT:</span> UTM 48S (107°08'E 06°18'S)</div>
                <div class="col-span-2 truncate"><span class="text-slate-500">KONSULTAN:</span> Conclusion Supply Chain</div>
            </div>
        </div>

        <!-- Floating HUD: Real-time 3D Navigation Target Indicator (Centered at Top) -->
        <div id="hudTargetPill" class="absolute top-3.5 left-1/2 -translate-x-1/2 pointer-events-auto transition-all duration-300 opacity-0 hidden z-20">
            <div class="bg-slate-900/95 backdrop-blur-md border border-cyan-500/70 text-white px-3.5 py-1.5 rounded-full shadow-2xl flex items-center space-x-2 text-xs font-mono">
                <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-ping shrink-0"></span>
                <span class="text-cyan-300 font-bold uppercase text-[10px] shrink-0"><i class="fa-solid fa-crosshairs mr-1"></i>Arah 3D:</span>
                <span class="text-white font-bold truncate max-w-[200px] sm:max-w-xs" id="hudTargetText">Smart Gate Inbound Lane 1</span>
                <span class="px-1.5 py-0.2 rounded bg-cyan-500/20 text-cyan-200 border border-cyan-400/30 text-[9.5px] shrink-0" id="hudTargetHw">HW-01</span>
                <span class="text-slate-400 text-[9.5px] hidden sm:inline shrink-0" id="hudTargetCoord">X: -10.0, Z: 55.0</span>
                <button onclick="dismissTargetPill()" class="text-slate-400 hover:text-white ml-1 text-xs shrink-0" title="Tutup">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        <!-- Floating HUD: Bottom Left - Digital Twin Intermodal Container Tracking & Yard Telemetry -->
        <div id="shipmentTrackingCard" class="absolute bottom-3.5 left-3.5 pointer-events-auto w-72 sm:w-80 transition-all duration-300 z-20">
            <div class="bg-white/95 backdrop-blur-md border border-slate-200/90 rounded-2xl p-3.5 shadow-2xl text-slate-800 space-y-2.5 relative">
                <!-- Header with Status Pill and Minimizer -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                            SPPB Cleared
                        </span>
                        <span class="font-mono font-bold text-xs text-blue-900 tracking-wider">MSKU 9182374</span>
                    </div>
                    <button onclick="toggleShipmentCard()" class="text-slate-400 hover:text-slate-700 text-xs px-1" title="Sembunyikan / Buka Detail">
                        <i id="shipmentCardChevron" class="fa-solid fa-chevron-down"></i>
                    </button>
                </div>

                <!-- Collapsible Body Content -->
                <div id="shipmentCardContent" class="space-y-2.5 text-xs transition-all duration-200">
                    <!-- Route Information: Port to Dry Port -->
                    <div class="bg-slate-50 border border-slate-200/70 rounded-xl p-2.5 space-y-1.5">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-slate-400 font-semibold uppercase text-[9px]">Pelabuhan Muat (POL)</span>
                            <span class="font-bold text-slate-800">Pelabuhan Tg. Priok (JICT)</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-slate-400 font-semibold uppercase text-[9px]">Terminal Tujuan (POD)</span>
                            <span class="font-bold text-blue-600">CIDP Dry Port • Blok B-05</span>
                        </div>
                    </div>

                    <!-- 3-Step Port Logistics Intermodal Stepper -->
                    <div class="pt-1">
                        <div class="flex items-center justify-between text-[9px] font-medium text-slate-500 mb-1">
                            <span class="text-emerald-600 font-bold">1. Gate-In OCR</span>
                            <span class="text-emerald-600 font-bold">2. Timbang VGM</span>
                            <span class="text-blue-600 font-bold">3. Lift-Off RS</span>
                        </div>
                        <div class="relative flex items-center justify-between">
                            <div class="absolute left-2 right-2 top-1/2 -translate-y-1/2 h-1 bg-slate-200 -z-0"></div>
                            <div class="absolute left-2 w-3/4 top-1/2 -translate-y-1/2 h-1 bg-gradient-to-r from-emerald-500 to-blue-500 -z-0"></div>
                            <!-- Step 1 -->
                            <div class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[8px] font-bold z-10 shadow-sm">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <!-- Step 2 -->
                            <div class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[8px] font-bold z-10 shadow-sm">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <!-- Step 3 (Active) -->
                            <div class="w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center text-[8px] font-bold z-10 shadow-md ring-4 ring-blue-100">
                                <i class="fa-solid fa-arrows-down-to-line"></i>
                            </div>
                        </div>
                    </div>

                    <!-- ETA & Truck License Plate -->
                    <div class="flex items-center justify-between text-[10.5px] pt-1 border-t border-slate-100 text-slate-600">
                        <span><i class="fa-solid fa-trailer text-blue-600 mr-1"></i>Chassis: <strong>40FT Skeletal</strong></span>
                        <span class="font-mono text-[10px] bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 text-slate-700">B 9812 UI</span>
                    </div>

                    <!-- Port Equipment Telemetry Highlights -->
                    <div class="pt-1 border-t border-slate-100 grid grid-cols-2 gap-1 text-[9.5px] font-semibold text-slate-600">
                        <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-lg p-1 px-1.5 flex items-center gap-1 truncate" title="Reach Stacker Blok A">
                            <i class="fa-solid fa-truck-ramp-box text-blue-600 text-[10px]"></i>
                            <span>RS-01: Blok A Ready</span>
                        </div>
                        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-1 px-1.5 flex items-center gap-1 truncate" title="RTG Crane di Rail Siding">
                            <i class="fa-solid fa-bridge text-amber-600 text-[10px]"></i>
                            <span>RTG-01: Rail Standby</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating HUD: Bottom Right - 3D Raycasting Inspector (Only visible when a container is clicked) -->
        <div class="absolute bottom-3.5 right-3.5 pointer-events-auto w-72 sm:w-80 transition-all duration-300 hidden z-30" id="inspectorCard">
            <div class="bg-slate-900/95 backdrop-blur-lg border border-blue-500/50 rounded-xl p-3.5 shadow-2xl text-white relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-amber-500"></div>
                <div class="flex items-center justify-between mb-2.5">
                    <div class="flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-blue-300">3D Container Inspector</span>
                    </div>
                    <button onclick="closeInspector()" class="text-slate-400 hover:text-white text-xs">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="space-y-1.5 text-xs">
                    <div>
                        <div class="text-[9px] text-slate-400 uppercase font-semibold">Nomor Kontainer (ISO 6346)</div>
                        <div class="text-sm font-bold font-mono text-white tracking-wider flex items-center justify-between" id="inspBoxNum">
                            MSKU9182374
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-800 text-[11px]">
                        <div>
                            <span class="text-slate-400 text-[9px] block">Tipe / Ukuran:</span>
                            <span class="font-semibold text-slate-200" id="inspType">40FT HIGH CUBE</span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[9px] block">Kategori Muatan:</span>
                            <span class="font-semibold" id="inspCargo">Dry General</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-800 text-[11px]">
                        <div>
                            <span class="text-slate-400 text-[9px] block">Posisi 3D Yard:</span>
                            <span class="font-bold text-amber-400 font-mono" id="inspSlot">B-05-03-02</span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[9px] block">Berat (VGM):</span>
                            <span class="font-semibold text-slate-200" id="inspWeight">28.450 kg</span>
                        </div>
                    </div>
                    <div class="pt-1 border-t border-slate-800 text-[11px]">
                        <span class="text-slate-400 text-[9px] block">Pemilik / Pelayaran:</span>
                        <span class="font-medium text-slate-300 truncate block" id="inspOwner">Maersk Indonesia</span>
                    </div>
                    <div class="pt-1 border-t border-slate-800 text-[11px]">
                        <span class="text-slate-400 text-[9px] block">Tag RFID UHF:</span>
                        <span class="font-mono text-purple-300 text-[10px] block" id="inspRfid">E280117000000001</span>
                    </div>
                </div>

                <div class="mt-3 pt-2.5 border-t border-slate-800">
                    <button onclick="prefillAndOpenMove()" class="w-full py-1.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-gray-950 font-bold text-xs rounded-lg shadow transition flex items-center justify-center space-x-1.5 cursor-pointer">
                        <i class="fa-solid fa-arrows-up-down-left-right text-[11px]"></i>
                        <span>Pindahkan Box Ini</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Notification Toast (Floating Top Center) -->
        <div id="simToast" class="absolute top-16 left-1/2 -translate-x-1/2 pointer-events-none transition-all duration-300 opacity-0 transform -translate-y-4 z-50">
            <div class="bg-slate-900/95 backdrop-blur-md border border-emerald-500 text-white px-4 py-2.5 rounded-xl shadow-2xl flex items-center space-x-3">
                <div class="w-7 h-7 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-sm shrink-0" id="toastIcon">
                    <i class="fa-solid fa-check"></i>
                </div>
                <div>
                    <div class="font-bold text-xs text-white" id="toastTitle">Aksi Berhasil Dieksekusi</div>
                    <div class="text-[11px] text-slate-300" id="toastMsg">Kontainer berhasil dipindahkan.</div>
                </div>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- PANEL KONTROL CEPAT & TELEMETRI SCADA (DITEMPATKAN RAPI DI LUAR LAYAR 3D) -->
    <!-- ========================================================================= -->
    <div class="bg-slate-900 rounded-2xl border border-slate-800 p-3 sm:p-4 shadow-xl text-white">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3.5 items-center">
            
            <!-- Kolom Kiri (7 Kolom): Tombol Aksi Cepat Operasional Lapangan -->
            <div class="lg:col-span-7 xl:col-span-7 flex flex-col space-y-2.5">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-pulse"></span>
                        <span class="font-bold text-slate-200 uppercase tracking-wider text-xs font-mono flex items-center gap-1.5">
                            <i class="fa-solid fa-gamepad text-cyan-400"></i>Aksi Cepat Lapangan
                        </span>
                    </div>
                    <!-- Pengatur Kecepatan & Tombol Sinkronisasi Database -->
                    <div class="flex items-center space-x-1.5">
                        <button onclick="toggleSimSpeed()" id="btnSimSpeed" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-amber-300 hover:text-amber-200 font-bold text-xs rounded-lg transition shrink-0 cursor-pointer border border-amber-500/30 flex items-center gap-1.5" title="Atur Kecepatan Simulasi Alur Gerakan">
                            <i class="fa-solid fa-gauge-high text-xs"></i><span id="speedBtnText">1.25x</span>
                        </button>
                        <button onclick="refreshStateFromDB()" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs rounded-lg transition shrink-0 cursor-pointer border border-slate-700 flex items-center gap-1.5" title="Sinkronkan MySQL">
                            <i class="fa-solid fa-rotate text-xs"></i><span class="text-[11px] font-semibold">Sync DB</span>
                        </button>
                    </div>
                </div>

                <!-- Kelompok Tombol Aksi Cepat (Bersih, Rapi & Tanpa Scrollbar Abu-Abu) -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- 1. Drop-Off: Alur Penuh (Masuk ➔ Bongkar RS ➔ Keluar) -->
                    <button onclick="triggerGateIn('drop_off')" class="px-3.5 py-2 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center space-x-1.5 cursor-pointer flex-1 sm:flex-none justify-center" title="Simulasi Alur Penuh: Truk Masuk Gerbang ➔ Timbang VGM ➔ Melaju ke Yard ➔ Lift-Off RS-01 ➔ Gate-Out Keluar">
                        <i class="fa-solid fa-arrow-down-to-bracket text-xs text-blue-200"></i>
                        <span>Truk Drop-Off</span>
                    </button>
                    <!-- 2. Pick-Up: Alur Penuh (Masuk Kosong ➔ Muat RS ➔ Keluar) -->
                    <button onclick="triggerGateIn('pick_up')" class="px-3.5 py-2 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center space-x-1.5 cursor-pointer flex-1 sm:flex-none justify-center" title="Simulasi Alur Penuh: Truk Masuk Kosong ➔ Verifikasi DO ➔ Melaju ke Yard ➔ Lift-On RS-02 ➔ Gate-Out Keluar">
                        <i class="fa-solid fa-arrow-up-from-bracket text-xs text-amber-200"></i>
                        <span>Truk Pick-Up</span>
                    </button>
                    <!-- 3. Gate-Out Saja -->
                    <button onclick="triggerGateOut()" class="px-3.5 py-2 bg-gradient-to-r from-purple-600 to-purple-500 hover:from-purple-500 hover:to-purple-400 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center space-x-1.5 cursor-pointer flex-1 sm:flex-none justify-center" title="Simulasi Truk Keluar Gerbang (Gate-Out Selesai)">
                        <i class="fa-solid fa-door-closed text-xs text-purple-200"></i>
                        <span>Gate-Out</span>
                    </button>
                    <!-- 4. Relokasi Box (RS) -->
                    <button onclick="openMoveModal()" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center space-x-1.5 border border-slate-700 cursor-pointer flex-1 sm:flex-none justify-center" title="Simulasi Pemindahan Box Antar-Blok oleh Reach Stacker">
                        <i class="fa-solid fa-arrows-up-down-left-right text-xs text-amber-400"></i>
                        <span>Relokasi RS</span>
                    </button>
                    <!-- 5. Alih Muat KA (RTG) -->
                    <button onclick="triggerRailDischarge()" class="px-3.5 py-2 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center space-x-1.5 cursor-pointer flex-1 sm:flex-none justify-center" title="Simulasi RTG Mengangkat Box dari Rangkaian Kereta ke Yard">
                        <i class="fa-solid fa-train text-xs text-indigo-200"></i>
                        <span>Alih Muat KA</span>
                    </button>
                </div>
            </div>

            <!-- Kolom Kanan (5 Kolom): Live SCADA Telemetry Feed Terminal (Tertata Rapi & Modern) -->
            <div class="lg:col-span-5 xl:col-span-5 bg-slate-950/90 rounded-xl p-3 border border-slate-800 shadow-inner">
                <div class="flex items-center justify-between pb-1.5 mb-1.5 border-b border-slate-800 text-[10.5px]">
                    <div class="flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="font-bold uppercase tracking-wider text-slate-300 font-mono">SCADA Telemetry Feed</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-slate-400 font-mono text-[9.5px]" id="liveClock">--:--:-- WIB</span>
                        <button onclick="toggleScadaFeed()" class="text-slate-400 hover:text-white text-xs px-1 cursor-pointer" title="Sembunyikan/Tampilkan Feed">
                            <i class="fa-solid fa-chevron-down text-[10px]" id="scadaFeedChevron"></i>
                        </button>
                    </div>
                </div>
                <div class="space-y-1 max-h-20 sm:max-h-24 overflow-y-auto text-[10.5px] font-mono leading-tight transition-all duration-200 pr-1" id="simLogTicker">
                    <div class="text-emerald-400 flex items-start">
                        <span class="text-slate-500 mr-1.5">[SYS]</span>
                        <span>Terminal Engine aktif. 19 box disinkronkan dari MySQL.</span>
                    </div>
                    <div class="text-blue-400 flex items-start">
                        <span class="text-slate-500 mr-1.5">[GPS]</span>
                        <span>RS-02 terkoneksi di Blok B (Operator: Agus Setiawan).</span>
                    </div>
                    <div class="text-amber-400 flex items-start">
                        <span class="text-slate-500 mr-1.5">[GATE]</span>
                        <span>Kamera ANPR Lane 1 &amp; 2 Siap (Jembatan 80T Siap).</span>
                    </div>
                </div>
            </div>

        </div>
    </div>


    <!-- ========================================================================= -->
    <!-- KONSOL SIMULASI OPERASIONAL LAPANGAN (DUAL-WORKFLOW ENGINE & SCADA HUB)   -->
    <!-- ========================================================================= -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white p-3.5 sm:p-4 rounded-xl border border-slate-700 shadow-md space-y-3">
        
        <!-- Baris 1: Judul & Tombol Eksekusi Alur Master -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 pb-3 border-b border-slate-700/80">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-emerald-500 text-white flex items-center justify-center text-base shadow-sm shrink-0">
                    <i class="fa-solid fa-tower-broadcast"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-2 flex-wrap">
                        <h4 class="font-bold text-sm sm:text-base text-white tracking-tight">Konsol Simulasi Operasional Lapangan</h4>
                        <span class="px-2 py-0.5 rounded text-[9px] font-mono font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">DUAL-WORKFLOW SCADA HUB</span>
                    </div>
                    <p class="text-[11px] text-slate-300 mt-0.5">Pilih alur simulasi otomatis terpisah antara armada truk dan kereta api, atau uji sensor mandiri</p>
                </div>
            </div>

            <!-- Tombol Master Alur Otomatis (Truk vs Kereta Api) -->
            <div class="flex flex-wrap items-center gap-2">
                <button onclick="runAutoTruckDemo()" id="btnAutoTruck" class="px-3.5 py-2 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-bold text-xs rounded-lg shadow-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-truck-moving text-xs"></i>
                    <span>1. Alur Otomatis Truk (6 Tahap)</span>
                </button>
                <button onclick="runAutoTrainDemo()" id="btnAutoTrain" class="px-3.5 py-2 bg-gradient-to-r from-purple-700 to-purple-600 hover:from-purple-600 hover:to-purple-500 text-white font-bold text-xs rounded-lg shadow-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-train-subway text-xs"></i>
                    <span>2. Alur Otomatis KA (6 Tahap)</span>
                </button>
                <button onclick="resetSimulation()" class="px-3 py-2 bg-white/10 hover:bg-white/20 text-white text-xs font-semibold rounded-lg border border-white/20 transition flex items-center gap-1.5" title="Reset Simulasi ke Posisi Awal">
                    <i class="fa-solid fa-rotate-left text-xs"></i><span>Reset</span>
                </button>
            </div>
        </div>

        <!-- Baris 2: Navigasi Tab Pengendalian Terpadu -->
        <div class="flex items-center justify-between flex-wrap gap-2 pt-1">
            <div class="flex items-center space-x-1.5 overflow-x-auto pb-1 sm:pb-0" id="simTabNav">
                <button onclick="switchSimTab('truck')" id="tabBtnTruck" class="sim-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-600 text-white shadow-xs transition flex items-center gap-1.5">
                    <i class="fa-solid fa-truck-ramp-box text-[11px]"></i>
                    <span>Alur Truk (Road-to-Yard)</span>
                </button>
                <button onclick="switchSimTab('train')" id="tabBtnTrain" class="sim-tab-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-train text-[11px]"></i>
                    <span>Alur Kereta Api (Rail-to-Yard)</span>
                </button>
                <button onclick="switchSimTab('scada')" id="tabBtnScada" class="sim-tab-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-sliders text-[11px]"></i>
                    <span>11 Sensor SCADA Mandiri</span>
                </button>
                <button onclick="switchSimTab('scanner')" id="tabBtnScanner" class="sim-tab-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-barcode text-[11px]"></i>
                    <span>Uji Scanner AIDC / GS1</span>
                </button>
                <button onclick="switchSimTab('catalog')" id="tabBtnCatalog" class="sim-tab-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-book-open text-[11px]"></i>
                    <span>Katalog Hardware &amp; Tarif ERP</span>
                </button>
            </div>
            
            <div class="flex items-center space-x-2 text-xs">
                <button onclick="openMoveModal()" class="px-2.5 py-1 bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-400/40 rounded-lg font-bold text-[11px] transition flex items-center gap-1">
                    <i class="fa-solid fa-arrows-up-down-left-right text-[10px]"></i><span>Relokasi RS</span>
                </button>
                <button onclick="triggerRailDischarge()" class="px-2.5 py-1 bg-purple-500/20 hover:bg-purple-500/30 text-purple-300 border border-purple-400/40 rounded-lg font-bold text-[11px] transition flex items-center gap-1">
                    <i class="fa-solid fa-dolly text-[10px]"></i><span>Bongkar RTG</span>
                </button>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- WADAH KONTEN TABULASI SIMULASI OPERASIONAL                               -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-xl shadow-xs border border-gray-200/80 p-4 sm:p-5 space-y-4">

        <!-- --------------------------------------------------------------------- -->
        <!-- TAB 1: ALUR SIMULASI OPERASIONAL TRUK KONTAINER (ROAD-TO-YARD)         -->
        <!-- --------------------------------------------------------------------- -->
        <div id="tabContentTruck" class="space-y-4">
            
            <!-- Banner Penjelasan Konseptual Alur Truk -->
            <div class="bg-gradient-to-r from-blue-50 via-slate-50 to-blue-50 border border-blue-200/80 rounded-xl p-3.5 sm:p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex items-start space-x-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-600 text-white flex items-center justify-center text-sm shadow-xs shrink-0 mt-0.5">
                        <i class="fa-solid fa-truck"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 text-xs sm:text-sm">Alur Operasional Truk Kontainer (Road / Highway Inbound)</h4>
                        <p class="text-[11.5px] text-gray-600 mt-0.5 leading-relaxed max-w-4xl">
                            Truk tiba dari jalan tol/kawasan industri membawa muatan dry/reefer. Melewati gerbang otomatis berkecepatan tinggi, portal optik OCR, jembatan timbang SOLAS 80 Ton, penguncian spreader Reach Stacker dengan RTK DGPS, hingga plugging cold chain.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 self-start md:self-auto shrink-0">
                    <button onclick="runAutoTruckDemo()" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1.5">
                        <i class="fa-solid fa-play text-[10px]"></i><span>Jalankan Alur Truk Lengkap</span>
                    </button>
                </div>
            </div>

            <!-- Stepper 6-Tahap Truk (Visual Card Grid) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                
                <!-- Tahap 1 Truk -->
                <div class="bg-slate-50/80 rounded-xl border border-gray-200 p-3.5 flex flex-col justify-between hover:border-blue-300 transition group">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-blue-100 text-blue-800 uppercase font-mono">Tahap 01</span>
                            <span class="text-[9.5px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">Gerbang Inbound</span>
                        </div>
                        <h5 class="font-bold text-gray-900 text-xs sm:text-sm">Pindai Plat Truk &amp; Tag Supir</h5>
                        <p class="text-[11px] text-gray-600 leading-relaxed">
                            Loop sensor aspal mendeteksi truk berhenti. Kamera ANPR membaca plat nomor truk dan antena UHF RFID membaca e-Pass supir dari jarak 20 meter.
                        </p>
                        <div class="p-2 bg-slate-900 text-white rounded-lg text-[10px] font-mono space-y-0.5">
                            <div><span class="text-slate-400">Hardware:</span> <strong class="text-blue-300">HW-01 (ANPR) &amp; HW-04 (RFID)</strong></div>
                            <div><span class="text-slate-400">Data Input:</span> B 9481 UEK &bull; RFID Tag 24-Hex</div>
                            <div><span class="text-slate-400">Billing ERP:</span> <strong class="text-emerald-400">Pas Gerbang Truk (Rp 50.000)</strong></div>
                        </div>
                    </div>
                    <div class="pt-2.5 mt-2 border-t border-gray-200/80 flex items-center justify-between">
                        <span class="text-[10px] text-gray-500">Otentikasi Supir &amp; Saldo</span>
                        <button onclick="testTruckStep(1)" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded-md text-[10.5px] font-bold transition flex items-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-play text-[9px]"></i><span>Uji Tahap 1</span>
                        </button>
                    </div>
                </div>

                <!-- Tahap 2 Truk -->
                <div class="bg-slate-50/80 rounded-xl border border-gray-200 p-3.5 flex flex-col justify-between hover:border-purple-300 transition group">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-purple-100 text-purple-800 uppercase font-mono">Tahap 02</span>
                            <span class="text-[9.5px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.2 rounded border border-purple-200">Portal Optik</span>
                        </div>
                        <h5 class="font-bold text-gray-900 text-xs sm:text-sm">Identifikasi Box ISO 6346</h5>
                        <p class="text-[11px] text-gray-600 leading-relaxed">
                            Portal kamera multi-sudut membaca nomor peti kemas ISO 6346 (4 huruf kode pemilik + 6 digit seri + 1 check digit) dan tipe ukuran (misal 42G1).
                        </p>
                        <div class="p-2 bg-slate-900 text-white rounded-lg text-[10px] font-mono space-y-0.5">
                            <div><span class="text-slate-400">Hardware:</span> <strong class="text-purple-300">HW-02 (Portal OCR ISO 6346)</strong></div>
                            <div><span class="text-slate-400">Data Input:</span> MSKU9182374 &bull; 40ft High Cube</div>
                            <div><span class="text-slate-400">Dampak YMS:</span> <strong class="text-amber-400">Demurrage Free Time Dimulai</strong></div>
                        </div>
                    </div>
                    <div class="pt-2.5 mt-2 border-t border-gray-200/80 flex items-center justify-between">
                        <span class="text-[10px] text-gray-500">Meniadakan Typo Manual</span>
                        <button onclick="testTruckStep(2)" class="px-2.5 py-1 bg-purple-600 hover:bg-purple-500 text-white rounded-md text-[10.5px] font-bold transition flex items-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-play text-[9px]"></i><span>Uji Tahap 2</span>
                        </button>
                    </div>
                </div>

                <!-- Tahap 3 Truk -->
                <div class="bg-slate-50/80 rounded-xl border border-gray-200 p-3.5 flex flex-col justify-between hover:border-emerald-300 transition group">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-emerald-100 text-emerald-800 uppercase font-mono">Tahap 03</span>
                            <span class="text-[9.5px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">SOLAS VGM</span>
                        </div>
                        <h5 class="font-bold text-gray-900 text-xs sm:text-sm">Penimbangan Jembatan 80 Ton</h5>
                        <p class="text-[11px] text-gray-600 leading-relaxed">
                            Truk melintas di timbangan baja 80T. Sensor strain gauge mencatat bobot Bruto, Tara, dan menerbitkan sertifikat Net VGM sesuai amandemen SOLAS IMO.
                        </p>
                        <div class="p-2 bg-slate-900 text-white rounded-lg text-[10px] font-mono space-y-0.5">
                            <div><span class="text-slate-400">Hardware:</span> <strong class="text-emerald-300">HW-03 (Fangda Truck Scale 80T)</strong></div>
                            <div><span class="text-slate-400">Data Output:</span> Net 26.450 kg (SOLAS PASS)</div>
                            <div><span class="text-slate-400">Billing ERP:</span> <strong class="text-emerald-400">Jasa Sertifikasi VGM (Rp 120.000)</strong></div>
                        </div>
                    </div>
                    <div class="pt-2.5 mt-2 border-t border-gray-200/80 flex items-center justify-between">
                        <span class="text-[10px] text-gray-500">Legalitas Maritim IMO</span>
                        <button onclick="testTruckStep(3)" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded-md text-[10.5px] font-bold transition flex items-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-play text-[9px]"></i><span>Uji Tahap 3</span>
                        </button>
                    </div>
                </div>

                <!-- Tahap 4 Truk -->
                <div class="bg-slate-50/80 rounded-xl border border-gray-200 p-3.5 flex flex-col justify-between hover:border-amber-300 transition group">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-amber-100 text-amber-800 uppercase font-mono">Tahap 04</span>
                            <span class="text-[9.5px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.2 rounded border border-amber-200">Interlock Palang</span>
                        </div>
                        <h5 class="font-bold text-gray-900 text-xs sm:text-sm">Buka Palang &amp; Panduan Rute VMS</h5>
                        <p class="text-[11px] text-gray-600 leading-relaxed">
                            Edge AI PC memvalidasi izin masuk dalam latensi 12ms, memicu pulsa relay palang terbuka. Display LED VMS memandu supir langsung ke blok yard tujuan.
                        </p>
                        <div class="p-2 bg-slate-900 text-white rounded-lg text-[10px] font-mono space-y-0.5">
                            <div><span class="text-slate-400">Hardware:</span> <strong class="text-amber-300">HW-06 (Edge PC) &amp; HW-05 (VMS LED)</strong></div>
                            <div><span class="text-slate-400">Instruksi:</span> "LANE 1: TRUK MENUJU BLOK B-08"</div>
                            <div><span class="text-slate-400">Dampak TAT:</span> <strong class="text-blue-300">Pangkas Waktu Antre 60%</strong></div>
                        </div>
                    </div>
                    <div class="pt-2.5 mt-2 border-t border-gray-200/80 flex items-center justify-between">
                        <span class="text-[10px] text-gray-500">Otomasi Tanpa Petugas</span>
                        <button onclick="testTruckStep(4)" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-500 text-white rounded-md text-[10.5px] font-bold transition flex items-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-play text-[9px]"></i><span>Uji Tahap 4</span>
                        </button>
                    </div>
                </div>

                <!-- Tahap 5 Truk -->
                <div class="bg-slate-50/80 rounded-xl border border-gray-200 p-3.5 flex flex-col justify-between hover:border-blue-300 transition group">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-blue-100 text-blue-800 uppercase font-mono">Tahap 05</span>
                            <span class="text-[9.5px] font-bold text-blue-700 bg-blue-50 px-1.5 py-0.2 rounded border border-blue-200">Penumpukan Yard</span>
                        </div>
                        <h5 class="font-bold text-gray-900 text-xs sm:text-sm">Bongkar Box oleh Reach Stacker</h5>
                        <p class="text-[11px] text-gray-600 leading-relaxed">
                            Reach Stacker (RS-02) menurunkan spreader Bromma, twistlock mengunci 4 pin corner casting. RTK GNSS memandu penempatan ke slot 3D B-08-03-02 akurasi &lt;2cm.
                        </p>
                        <div class="p-2 bg-slate-900 text-white rounded-lg text-[10px] font-mono space-y-0.5">
                            <div><span class="text-slate-400">Hardware:</span> <strong class="text-blue-300">HW-07 (RTK GNSS) &amp; HW-08 (Spreader)</strong></div>
                            <div><span class="text-slate-400">Koordinat:</span> Slot B-08-03-02 &bull; Beban 28.45T</div>
                            <div><span class="text-slate-400">Billing ERP:</span> <strong class="text-emerald-400">Jasa Lo-Lo Lift-Off (Rp 250.000)</strong></div>
                        </div>
                    </div>
                    <div class="pt-2.5 mt-2 border-t border-gray-200/80 flex items-center justify-between">
                        <span class="text-[10px] text-gray-500">Zero Misplacement Box</span>
                        <button onclick="testTruckStep(5)" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded-md text-[10.5px] font-bold transition flex items-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-play text-[9px]"></i><span>Uji Tahap 5</span>
                        </button>
                    </div>
                </div>

                <!-- Tahap 6 Truk -->
                <div class="bg-slate-50/80 rounded-xl border border-gray-200 p-3.5 flex flex-col justify-between hover:border-cyan-300 transition group">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-cyan-100 text-cyan-800 uppercase font-mono">Tahap 06</span>
                            <span class="text-[9.5px] font-bold text-cyan-700 bg-cyan-50 px-1.5 py-0.2 rounded border border-cyan-200">Reefer Cold Chain</span>
                        </div>
                        <h5 class="font-bold text-gray-900 text-xs sm:text-sm">Colok Daya &amp; Monitoring Suhu</h5>
                        <p class="text-[11px] text-gray-600 leading-relaxed">
                            Jika muatan berpendingin, box ditaruh di rak reefer dan dicolokkan ke soket industri 380V. Sensor Modbus membaca arus, tegangan, dan suhu -20.2&deg;C secara live.
                        </p>
                        <div class="p-2 bg-slate-900 text-white rounded-lg text-[10px] font-mono space-y-0.5">
                            <div><span class="text-slate-400">Hardware:</span> <strong class="text-cyan-300">HW-09 (Marechal Smart Socket)</strong></div>
                            <div><span class="text-slate-400">Telemetri:</span> -20.2&deg;C &bull; 388.4V &bull; 18.2 kW</div>
                            <div><span class="text-slate-400">Billing ERP:</span> <strong class="text-emerald-400">Reefer Electricity (Rp 35.000/jam)</strong></div>
                        </div>
                    </div>
                    <div class="pt-2.5 mt-2 border-t border-gray-200/80 flex items-center justify-between">
                        <span class="text-[10px] text-gray-500">Perlindungan Kargo Ekspor</span>
                        <button onclick="testTruckStep(6)" class="px-2.5 py-1 bg-cyan-600 hover:bg-cyan-500 text-white rounded-md text-[10.5px] font-bold transition flex items-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-play text-[9px]"></i><span>Uji Tahap 6</span>
                        </button>
                    </div>
                </div>

            </div>

            <!-- Ringkasan Finansial Alur Truk -->
            <div class="p-3 bg-blue-50/70 rounded-xl border border-blue-200 flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-2">
                <div class="flex items-center space-x-2 text-blue-900 font-semibold">
                    <i class="fa-solid fa-file-invoice-dollar text-blue-600 text-sm"></i>
                    <span>Total Billing Terbentuk pada 1 Siklus Truk Masuk (Inbound):</span>
                </div>
                <div class="font-mono text-emerald-800 font-bold bg-white px-3 py-1 rounded-lg border border-blue-200 shadow-2xs">
                    Pas Gerbang (Rp 50.000) + VGM (Rp 120.000) + Lo-Lo (Rp 250.000) = <span class="text-emerald-600 text-sm">Rp 420.000</span>
                </div>
            </div>

        </div>

        <!-- --------------------------------------------------------------------- -->
        <!-- TAB 2: ALUR SIMULASI OPERASIONAL KERETA API INTERMODAL (RAIL-TO-YARD)    -->
        <!-- --------------------------------------------------------------------- -->
        <div id="tabContentTrain" class="space-y-4 hidden">
            
            <!-- Banner Penjelasan Konseptual Alur KA -->
            <div class="bg-gradient-to-r from-purple-50 via-slate-50 to-purple-50 border border-purple-200/80 rounded-xl p-3.5 sm:p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex items-start space-x-3">
                    <div class="w-9 h-9 rounded-lg bg-purple-700 text-white flex items-center justify-center text-sm shadow-xs shrink-0 mt-0.5">
                        <i class="fa-solid fa-train"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 text-xs sm:text-sm">Alur Operasional Kereta Api Shuttle 2-Arah (Dual-Track Rail Siding)</h4>
                        <p class="text-[11.5px] text-gray-600 mt-0.5 leading-relaxed max-w-4xl">
                            <strong>Esensi Kereta Api Shuttle Dry Port 2-Arah:</strong> Menghubungkan Stasiun Pasoso (Tanjung Priok) &harr; CIDP secara kontinyu: rangkaian <em>Inbound</em> (Bongkar Impor &amp; PLP Pabean ex-kapal laut di Sepur Utara Track-01) dan rangkaian <em>Outbound</em> (Muat Ekspor terverifikasi SOLAS VGM &amp; NPE Bea Cukai di Sepur Selatan Track-02 langsung ke kapal laut). Diverifikasi sensor gandar Frauscher SIL 4 dan E-Seal CEISA 4.0.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 self-start md:self-auto shrink-0">
                    <button onclick="runAutoTrainDemo()" class="px-3.5 py-1.5 bg-purple-700 hover:bg-purple-600 text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1.5">
                        <i class="fa-solid fa-play text-[10px]"></i><span>Jalankan Alur Kereta Api Lengkap</span>
                    </button>
                </div>
            </div>

            <!-- Stepper 6-Tahap Kereta Api (Visual Card Grid) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                
                <!-- Tahap 1 KA -->
                <div class="bg-slate-50/80 rounded-xl border border-gray-200 p-3.5 flex flex-col justify-between hover:border-purple-300 transition group">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-purple-100 text-purple-800 uppercase font-mono">Tahap 01</span>
                            <span class="text-[9.5px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.2 rounded border border-purple-200">Rel Siding</span>
                        </div>
                        <h5 class="font-bold text-gray-900 text-xs sm:text-sm">Verifikasi Integritas Rangkaian KA</h5>
                        <p class="text-[11px] text-gray-600 leading-relaxed">
                            KA 2518 melintasi sensor roda di rel siding. Sensor mencacah tepat 126 gandar (1 Lok CC 206 [6 as] + 30 Gerbong PPCW [120 as]). Standar SIL 4 menjamin tidak ada gerbong terlepas.
                        </p>
                        <div class="p-2 bg-slate-900 text-white rounded-lg text-[10px] font-mono space-y-0.5">
                            <div><span class="text-slate-400">Hardware:</span> <strong class="text-purple-300">HW-10 (Frauscher Axle Counter)</strong></div>
                            <div><span class="text-slate-400">Hasil Cacah:</span> 126 As Roda (30 Gerbong Lengkap)</div>
                            <div><span class="text-slate-400">Keselamatan:</span> <strong class="text-emerald-400">Safety Integrity SIL 4 PASS</strong></div>
                        </div>
                    </div>
                    <div class="pt-2.5 mt-2 border-t border-gray-200/80 flex items-center justify-between">
                        <span class="text-[10px] text-gray-500">Pencegahan Kereta Anjlok</span>
                        <button onclick="testTrainStep(1)" class="px-2.5 py-1 bg-purple-700 hover:bg-purple-600 text-white rounded-md text-[10.5px] font-bold transition flex items-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-play text-[9px]"></i><span>Uji Tahap 1</span>
                        </button>
                    </div>
                </div>

                <!-- Tahap 2 KA -->
                <div class="bg-slate-50/80 rounded-xl border border-gray-200 p-3.5 flex flex-col justify-between hover:border-blue-300 transition group">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-blue-100 text-blue-800 uppercase font-mono">Tahap 02</span>
                            <span class="text-[9.5px] font-bold text-blue-700 bg-blue-50 px-1.5 py-0.2 rounded border border-blue-200">Smart E-Seal</span>
                        </div>
                        <h5 class="font-bold text-gray-900 text-xs sm:text-sm">Pemeriksaan Segel Pabean Nirkabel</h5>
                        <p class="text-[11px] text-gray-600 leading-relaxed">
                            Antena nirkabel membaca Smart E-Seal Jointech JT701 pada pintu peti kemas. Memastikan kawat segel berstatus INTACT (tidak pernah dibuka/dirusak selama transit Priok-Dry Port).
                        </p>
                        <div class="p-2 bg-slate-900 text-white rounded-lg text-[10px] font-mono space-y-0.5">
                            <div><span class="text-slate-400">Hardware:</span> <strong class="text-blue-300">HW-11 (Jointech JT701 GPS Seal)</strong></div>
                            <div><span class="text-slate-400">Status Kawat:</span> INTACT &bull; Baterai 96%</div>
                            <div><span class="text-slate-400">Audit Pabean:</span> <strong class="text-emerald-400">Zero Tamper Transit Verified</strong></div>
                        </div>
                    </div>
                    <div class="pt-2.5 mt-2 border-t border-gray-200/80 flex items-center justify-between">
                        <span class="text-[10px] text-gray-500">Keamanan Kargo Tersegel</span>
                        <button onclick="testTrainStep(2)" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded-md text-[10.5px] font-bold transition flex items-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-play text-[9px]"></i><span>Uji Tahap 2</span>
                        </button>
                    </div>
                </div>

                <!-- Tahap 3 KA -->
                <div class="bg-slate-50/80 rounded-xl border border-gray-200 p-3.5 flex flex-col justify-between hover:border-emerald-300 transition group">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-emerald-100 text-emerald-800 uppercase font-mono">Tahap 03</span>
                            <span class="text-[9.5px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">CEISA 4.0</span>
                        </div>
                        <h5 class="font-bold text-gray-900 text-xs sm:text-sm">Klirens Pabean &amp; SPPB Jalur Hijau</h5>
                        <p class="text-[11px] text-gray-600 leading-relaxed">
                            Data telemetri segel dikirim ke gateway Bea Cukai CEISA 4.0. Sistem pabean menerbitkan SPPB (Surat Persetujuan Pengeluaran Barang) Jalur Hijau dan membebaskan jaminan pabean.
                        </p>
                        <div class="p-2 bg-slate-900 text-white rounded-lg text-[10px] font-mono space-y-0.5">
                            <div><span class="text-slate-400">Integrasi:</span> <strong class="text-emerald-300">API Gateway CEISA 4.0 DJBC</strong></div>
                            <div><span class="text-slate-400">Nomor SPPB:</span> SPPB-86434/KPU.01/2026</div>
                            <div><span class="text-slate-400">Finansial:</span> <strong class="text-emerald-400">Rilis Jaminan Pabean (Bond Free)</strong></div>
                        </div>
                    </div>
                    <div class="pt-2.5 mt-2 border-t border-gray-200/80 flex items-center justify-between">
                        <span class="text-[10px] text-gray-500">Legalitas Pabean Kemenkeu</span>
                        <button onclick="testTrainStep(3)" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded-md text-[10.5px] font-bold transition flex items-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-play text-[9px]"></i><span>Uji Tahap 3</span>
                        </button>
                    </div>
                </div>

                <!-- Tahap 4 KA -->
                <div class="bg-slate-50/80 rounded-xl border border-gray-200 p-3.5 flex flex-col justify-between hover:border-indigo-300 transition group">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-indigo-100 text-indigo-800 uppercase font-mono">Tahap 04</span>
                            <span class="text-[9.5px] font-bold text-indigo-700 bg-indigo-50 px-1.5 py-0.2 rounded border border-indigo-200">RTG Crane</span>
                        </div>
                        <h5 class="font-bold text-gray-900 text-xs sm:text-sm">Bongkar Box dari Gerbong KA</h5>
                        <p class="text-[11px] text-gray-600 leading-relaxed">
                            RTG-01 Electric Crane bergerak di atas lintasan siding. Spreader Bromma menurunkan 4 pin twistlock mengunci corner casting kontainer dari atas gerbong datar PPCW.
                        </p>
                        <div class="p-2 bg-slate-900 text-white rounded-lg text-[10px] font-mono space-y-0.5">
                            <div><span class="text-slate-400">Hardware:</span> <strong class="text-indigo-300">RTG-01 &amp; HW-08 (Bromma Spreader)</strong></div>
                            <div><span class="text-slate-400">Beban Angkat:</span> 26.400 kg &bull; 4/4 Pin Terkunci</div>
                            <div><span class="text-slate-400">Billing ERP:</span> <strong class="text-emerald-400">Jasa Bongkar KA (Rp 350.000)</strong></div>
                        </div>
                    </div>
                    <div class="pt-2.5 mt-2 border-t border-gray-200/80 flex items-center justify-between">
                        <span class="text-[10px] text-gray-500">Alih Muat Intermodal Rel-Yard</span>
                        <button onclick="testTrainStep(4)" class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-500 text-white rounded-md text-[10.5px] font-bold transition flex items-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-play text-[9px]"></i><span>Uji Tahap 4</span>
                        </button>
                    </div>
                </div>

                <!-- Tahap 5 KA -->
                <div class="bg-slate-50/80 rounded-xl border border-gray-200 p-3.5 flex flex-col justify-between hover:border-amber-300 transition group">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-amber-100 text-amber-800 uppercase font-mono">Tahap 05</span>
                            <span class="text-[9.5px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.2 rounded border border-amber-200">RTK Positioning</span>
                        </div>
                        <h5 class="font-bold text-gray-900 text-xs sm:text-sm">Penempatan Slot Yard Presisi 3D</h5>
                        <p class="text-[11px] text-gray-600 leading-relaxed">
                            RTK GNSS Receiver memandu penempatan kontainer dari kereta ke slot penumpukan yard (Blok A) dengan akurasi sub-sentimeter (&lt;1.8 cm). Status box menjadi 'in_yard'.
                        </p>
                        <div class="p-2 bg-slate-900 text-white rounded-lg text-[10px] font-mono space-y-0.5">
                            <div><span class="text-slate-400">Hardware:</span> <strong class="text-amber-300">HW-07 (CHCNAV CGI-610 RTK)</strong></div>
                            <div><span class="text-slate-400">Koordinat:</span> Blok A-02-01-01 (18 Satelit FIX)</div>
                            <div><span class="text-slate-400">Dampak YMS:</span> <strong class="text-blue-300">Sinkronisasi Database MySQL Live</strong></div>
                        </div>
                    </div>
                    <div class="pt-2.5 mt-2 border-t border-gray-200/80 flex items-center justify-between">
                        <span class="text-[10px] text-gray-500">Meniadakan Biaya Shifting Ulang</span>
                        <button onclick="testTrainStep(5)" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-500 text-white rounded-md text-[10.5px] font-bold transition flex items-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-play text-[9px]"></i><span>Uji Tahap 5</span>
                        </button>
                    </div>
                </div>

                <!-- Tahap 6 KA -->
                <div class="bg-slate-50/80 rounded-xl border border-gray-200 p-3.5 flex flex-col justify-between hover:border-emerald-300 transition group">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-emerald-100 text-emerald-800 uppercase font-mono">Tahap 06</span>
                            <span class="text-[9.5px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">Freight &amp; ESG</span>
                        </div>
                        <h5 class="font-bold text-gray-900 text-xs sm:text-sm">Penerbitan Tagihan Rel &amp; ESG</h5>
                        <p class="text-[11px] text-gray-600 leading-relaxed">
                            Dokumen Rail Interchange Manifest diverifikasi. Biaya pengangkutan kereta api (Rp 1.850.000/TEU) tercatat, dan reduksi emisi karbon 78% dibanding truk jalan raya tercatat.
                        </p>
                        <div class="p-2 bg-slate-900 text-white rounded-lg text-[10px] font-mono space-y-0.5">
                            <div><span class="text-slate-400">Manifest KA:</span> KA-LOG-PRIOK-CIDP &bull; 48 TEU (Inbound) / 52 TEU (Outbound)</div>
                            <div><span class="text-slate-400">Billing Freight:</span> <strong class="text-emerald-400">Tarif KA (Rp 1.850.000 / TEU)</strong></div>
                            <div><span class="text-slate-400">Dampak Hijau:</span> <strong class="text-blue-300">Reduksi Karbon 78% (ESG)</strong></div>
                        </div>
                    </div>
                    <div class="pt-2.5 mt-2 border-t border-gray-200/80 flex items-center justify-between">
                        <span class="text-[10px] text-gray-500">Green Logistics Terminal</span>
                        <button onclick="testTrainStep(6)" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded-md text-[10.5px] font-bold transition flex items-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-play text-[9px]"></i><span>Uji Tahap 6</span>
                        </button>
                    </div>
                </div>

            </div>

            <!-- Ringkasan Finansial Alur Kereta Api -->
            <div class="p-3 bg-purple-50/70 rounded-xl border border-purple-200 flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-2">
                <div class="flex items-center space-x-2 text-purple-900 font-semibold">
                    <i class="fa-solid fa-file-invoice-dollar text-purple-600 text-sm"></i>
                    <span>Total Billing Terbentuk pada 1 Siklus Alih Muat Kereta Api (Intermodal):</span>
                </div>
                <div class="font-mono text-emerald-800 font-bold bg-white px-3 py-1 rounded-lg border border-purple-200 shadow-2xs">
                    Freight KA (Rp 1.850.000) + Jasa RTG (Rp 350.000) = <span class="text-emerald-600 text-sm">Rp 2.200.000 / TEU</span>
                </div>
            </div>

        </div>

        <!-- --------------------------------------------------------------------- -->
        <!-- TAB 3: SCADA 11 SENSOR MANDIRI (4 STASIUN TELEMETRI LAPANGAN)         -->
        <!-- --------------------------------------------------------------------- -->
        <div id="tabContentScada" class="space-y-4 hidden">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-2 border-b border-gray-100 gap-2">
                <div>
                    <h4 class="font-bold text-gray-900 text-xs sm:text-sm">Uji Coba Mandiri 11 Sensor &amp; Perangkat Keras Lapangan</h4>
                    <p class="text-[11px] text-gray-500">Setiap tombol di bawah memicu telemetri live, pembaruan database MySQL, dan simulasi tarif billing ERP</p>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[9px] font-mono font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 self-start sm:self-auto">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block mr-1 animate-pulse"></span>11/11 SENSOR SIAP
                </span>
            </div>

            <!-- SCADA 2x2 Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3.5">
                
                <!-- STASIUN 01: GATE OTOMASI & TIMBANGAN VGM (HW-01 s/d HW-06) -->
                <div class="bg-slate-50/80 rounded-xl border border-gray-200/90 p-4 flex flex-col justify-between hover:border-blue-300 transition group">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-blue-100 text-blue-800 uppercase font-mono">Stasiun 01</span>
                                <h4 class="font-bold text-gray-900 text-xs sm:text-sm">Smart Gate &amp; Timbangan Inbound (6 Hardware)</h4>
                            </div>
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" title="6 Perangkat Online"></span>
                        </div>
                        <p class="text-[10px] text-gray-500 mb-2.5">HW-01 (ANPR), HW-02 (OCR), HW-03 (Timbangan 80T), HW-04 (RFID), HW-05 (VMS), HW-06 (Edge PC)</p>

                        <!-- SCADA Digital Readout Matrix -->
                        <div class="bg-slate-900 rounded-lg p-3 text-white font-mono text-[11px] space-y-2 border border-slate-800 mb-3 shadow-inner">
                            <div class="grid grid-cols-2 gap-2 pb-1.5 border-b border-slate-800">
                                <div>
                                    <span class="text-[9.5px] text-slate-400 block uppercase">HW-01 ANPR Truk:</span>
                                    <span class="font-bold text-blue-400" id="sensorAnprVal">B 9481 UEK (99.4%)</span>
                                </div>
                                <div>
                                    <span class="text-[9.5px] text-slate-400 block uppercase">HW-02 Portal OCR:</span>
                                    <span class="font-bold text-purple-400" id="sensorOcrVal">MSKU9182374 (42G1)</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 pb-1.5 border-b border-slate-800">
                                <div>
                                    <span class="text-[9.5px] text-slate-400 block uppercase">HW-03 Timbangan 80T:</span>
                                    <span class="font-bold text-emerald-400" id="sensorVgmVal">Net 26.450 kg (PASS)</span>
                                </div>
                                <div>
                                    <span class="text-[9.5px] text-slate-400 block uppercase">HW-04 RFID Tag:</span>
                                    <span class="font-bold text-indigo-300" id="sensorRfidVal">E280117000... (AUTH)</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <span class="text-[9.5px] text-slate-400 block uppercase">HW-05 VMS Display:</span>
                                    <span class="font-bold text-amber-300 truncate block" id="sensorVmsVal">LANE 1 -> BLOK B-08</span>
                                </div>
                                <div>
                                    <span class="text-[9.5px] text-slate-400 block uppercase">HW-06 Edge Relay:</span>
                                    <span class="font-bold text-emerald-300" id="sensorEdgeVal">INTERLOCK (12ms / 1.2s)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 6 Dedicated Sensor Test Buttons -->
                    <div class="space-y-2 pt-2 border-t border-gray-200/70">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">Pemicu Sensor Mandiri:</div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5">
                            <button onclick="testSensorAction('hw01_anpr')" class="py-1.5 px-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-bold text-[10.5px] transition flex items-center justify-center gap-1 shadow-2xs">
                                <span class="px-1 py-0.2 bg-white/20 rounded text-[9px] font-mono">HW-01</span>
                                <span>ANPR Plat</span>
                            </button>
                            <button onclick="testSensorAction('hw02_ocr')" class="py-1.5 px-2 bg-purple-600 hover:bg-purple-500 text-white rounded-lg font-bold text-[10.5px] transition flex items-center justify-center gap-1 shadow-2xs">
                                <span class="px-1 py-0.2 bg-white/20 rounded text-[9px] font-mono">HW-02</span>
                                <span>OCR Box</span>
                            </button>
                            <button onclick="testSensorAction('hw03_vgm')" class="py-1.5 px-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg font-bold text-[10.5px] transition flex items-center justify-center gap-1 shadow-2xs">
                                <span class="px-1 py-0.2 bg-white/20 rounded text-[9px] font-mono">HW-03</span>
                                <span>Timbang 80T</span>
                            </button>
                            <button onclick="testSensorAction('hw04_rfid')" class="py-1.5 px-2 bg-white hover:bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg font-bold text-[10.5px] transition flex items-center justify-center gap-1">
                                <span class="px-1 py-0.2 bg-indigo-100 rounded text-[9px] font-mono">HW-04</span>
                                <span>RFID Tag</span>
                            </button>
                            <button onclick="testSensorAction('hw05_vms')" class="py-1.5 px-2 bg-white hover:bg-amber-50 text-amber-800 border border-amber-200 rounded-lg font-bold text-[10.5px] transition flex items-center justify-center gap-1">
                                <span class="px-1 py-0.2 bg-amber-100 rounded text-[9px] font-mono">HW-05</span>
                                <span>VMS LED</span>
                            </button>
                            <button onclick="testSensorAction('hw06_edge')" class="py-1.5 px-2 bg-white hover:bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg font-bold text-[10.5px] transition flex items-center justify-center gap-1">
                                <span class="px-1 py-0.2 bg-emerald-100 rounded text-[9px] font-mono">HW-06</span>
                                <span>Relay Palang</span>
                            </button>
                        </div>
                        <div class="px-2.5 py-1 bg-emerald-50 rounded-md border border-emerald-200/80 text-[10px] text-emerald-800 font-semibold flex items-center justify-between">
                            <span><i class="fa-solid fa-file-invoice mr-1 text-emerald-600"></i>Auto-Billing ERP:</span>
                            <span class="font-bold text-emerald-900">Pas Masuk Truk (Rp 50.000) &bull; VGM SOLAS (Rp 120.000)</span>
                        </div>
                    </div>
                </div>

                <!-- STASIUN 02: TELEMETRI RTK & SPREADER ALAT BERAT (HW-07 & HW-08) -->
                <div class="bg-slate-50/80 rounded-xl border border-gray-200/90 p-4 flex flex-col justify-between hover:border-amber-300 transition group">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-amber-100 text-amber-800 uppercase font-mono">Stasiun 02</span>
                                <h4 class="font-bold text-gray-900 text-xs sm:text-sm">Telemetri RTK Lapangan &amp; Spreader RS (2 Hardware)</h4>
                            </div>
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" title="2 Perangkat Online"></span>
                        </div>
                        <p class="text-[10px] text-gray-500 mb-2.5">HW-07 (CHCNAV RTK GNSS &lt;2cm), HW-08 (Bromma Twistlock &amp; Load Cell Kit)</p>

                        <!-- SCADA Digital Readout Matrix -->
                        <div class="bg-slate-900 rounded-lg p-3 text-white font-mono text-[11px] space-y-2 border border-slate-800 mb-3 shadow-inner">
                            <div class="grid grid-cols-2 gap-2 pb-1.5 border-b border-slate-800">
                                <div>
                                    <span class="text-[9.5px] text-slate-400 block uppercase">HW-07 RTK DGPS:</span>
                                    <span class="font-bold text-emerald-400" id="sensorRtkVal">FIX (&lt;1.8 cm, 18 Sat)</span>
                                </div>
                                <div>
                                    <span class="text-[9.5px] text-slate-400 block uppercase">Koordinat Slot 3D:</span>
                                    <span class="font-bold text-amber-400" id="sensorSlotVal">B-08-03-02</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <span class="text-[9.5px] text-slate-400 block uppercase">HW-08 Twistlock:</span>
                                    <span class="font-bold text-blue-400" id="sensorTwistlockVal">LOCKED (Mengunci 4 Pin)</span>
                                </div>
                                <div>
                                    <span class="text-[9.5px] text-slate-400 block uppercase">Load Cell Sensor:</span>
                                    <span class="font-bold text-slate-200" id="sensorLoadVal">28.45 Ton</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dedicated Sensor Test Buttons + Operational Move Action -->
                    <div class="space-y-2 pt-2 border-t border-gray-200/70">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">Pemicu Sensor &amp; Aksi Fisik:</div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-1.5">
                            <button onclick="testSensorAction('hw07_rtk')" class="py-1.5 px-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg font-bold text-[10.5px] transition flex items-center justify-center gap-1 shadow-2xs">
                                <span class="px-1 py-0.2 bg-white/20 rounded text-[9px] font-mono">HW-07</span>
                                <span>Kalibrasi RTK</span>
                            </button>
                            <button onclick="testSensorAction('hw08_twistlock')" class="py-1.5 px-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-bold text-[10.5px] transition flex items-center justify-center gap-1 shadow-2xs">
                                <span class="px-1 py-0.2 bg-white/20 rounded text-[9px] font-mono">HW-08</span>
                                <span>Twistlock &amp; Load</span>
                            </button>
                            <button onclick="openMoveModal()" class="py-1.5 px-2 bg-amber-500 hover:bg-amber-400 text-gray-950 rounded-lg font-bold text-[10.5px] transition flex items-center justify-center gap-1 shadow-2xs">
                                <i class="fa-solid fa-dolly text-[10px]"></i>
                                <span>Relokasi 3D (RS)</span>
                            </button>
                        </div>
                        <div class="px-2.5 py-1 bg-emerald-50 rounded-md border border-emerald-200/80 text-[10px] text-emerald-800 font-semibold flex items-center justify-between">
                            <span><i class="fa-solid fa-file-invoice mr-1 text-emerald-600"></i>Auto-Billing ERP:</span>
                            <span class="font-bold text-emerald-900">Jasa Lo-Lo Lift-Off Stevedoring (Rp 250.000)</span>
                        </div>
                    </div>
                </div>

                <!-- STASIUN 03: PEMANTAUAN RANTAI DINGIN REEFER IOT (HW-09) -->
                <div class="bg-slate-50/80 rounded-xl border border-gray-200/90 p-4 flex flex-col justify-between hover:border-cyan-300 transition group">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-cyan-100 text-cyan-800 uppercase font-mono">Stasiun 03</span>
                                <h4 class="font-bold text-gray-900 text-xs sm:text-sm">Reefer Cold Chain &amp; Smart Socket (1 Hardware)</h4>
                            </div>
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" title="Perangkat Online"></span>
                        </div>
                        <p class="text-[10px] text-gray-500 mb-2.5">HW-09 (Marechal Smart Socket 380V/32A Modbus RTU &bull; Daya, Voltase, &amp; Suhu)</p>

                        <!-- SCADA Digital Readout Matrix -->
                        <div class="bg-slate-900 rounded-lg p-3 text-white font-mono text-[11px] space-y-2 border border-slate-800 mb-3 shadow-inner">
                            <div class="grid grid-cols-2 gap-2 pb-1.5 border-b border-slate-800">
                                <div>
                                    <span class="text-[9.5px] text-slate-400 block uppercase">HW-09 Colokan Rack:</span>
                                    <span class="font-bold text-cyan-300">Rack R-02 Plug #14</span>
                                </div>
                                <div>
                                    <span class="text-[9.5px] text-slate-400 block uppercase">Telemetri Suhu:</span>
                                    <span class="font-bold text-cyan-400" id="sensorReeferTempVal">-20.2&deg;C (Optimal)</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <span class="text-[9.5px] text-slate-400 block uppercase">Tegangan 3-Fasa:</span>
                                    <span class="font-bold text-slate-200" id="sensorVoltVal">388.4 V (50 Hz)</span>
                                </div>
                                <div>
                                    <span class="text-[9.5px] text-slate-400 block uppercase">Beban Konsumsi:</span>
                                    <span class="font-bold text-slate-200" id="sensorKwVal">18.2 kW (28.6 A)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dedicated Sensor Test Buttons -->
                    <div class="space-y-2 pt-2 border-t border-gray-200/70">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">Pemicu Sensor Modbus &amp; Skenario:</div>
                        <div class="grid grid-cols-2 gap-2">
                            <button onclick="testSensorAction('hw09_reefer')" class="py-1.5 px-2 bg-cyan-600 hover:bg-cyan-500 text-white rounded-lg font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-2xs">
                                <span class="px-1 py-0.2 bg-white/20 rounded text-[9px] font-mono">HW-09</span>
                                <span>Baca Modbus Suhu &amp; Daya</span>
                            </button>
                            <button onclick="simulateReeferAlarm()" class="py-1.5 px-2 bg-white hover:bg-rose-50 text-rose-700 border border-rose-300 rounded-lg font-bold text-xs transition flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-triangle-exclamation text-[11px]"></i>
                                <span>Simulasi Overheat (+4.8&deg;C)</span>
                            </button>
                        </div>
                        <div class="px-2.5 py-1 bg-emerald-50 rounded-md border border-emerald-200/80 text-[10px] text-emerald-800 font-semibold flex items-center justify-between">
                            <span><i class="fa-solid fa-file-invoice mr-1 text-emerald-600"></i>Auto-Billing ERP:</span>
                            <span class="font-bold text-emerald-900">Pasokan Daya &amp; Monitoring (Rp 35.000 / jam)</span>
                        </div>
                    </div>
                </div>

                <!-- STASIUN 04: INTERMODAL KA & KEPABEANAN CEISA 4.0 (HW-10 & HW-11) -->
                <div class="bg-slate-50/80 rounded-xl border border-gray-200/90 p-4 flex flex-col justify-between hover:border-purple-300 transition group">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-purple-100 text-purple-800 uppercase font-mono">Stasiun 04</span>
                                <h4 class="font-bold text-gray-900 text-xs sm:text-sm">Intermodal Rel KA &amp; Pabean CEISA 4.0 (2 Hardware)</h4>
                            </div>
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" title="2 Perangkat Online"></span>
                        </div>
                        <p class="text-[10px] text-gray-500 mb-2.5">HW-10 (Frauscher Axle Counter SIL 4), HW-11 (Jointech JT701 GPS Smart E-Seal)</p>

                        <!-- SCADA Digital Readout Matrix -->
                        <div class="bg-slate-900 rounded-lg p-3 text-white font-mono text-[11px] space-y-2 border border-slate-800 mb-3 shadow-inner">
                            <div class="grid grid-cols-2 gap-2 pb-1.5 border-b border-slate-800">
                                <div>
                                    <span class="text-[9.5px] text-slate-400 block uppercase">HW-10 Gandar Rel KA:</span>
                                    <span class="font-bold text-purple-300" id="sensorAxleVal">126 As Roda (KA 2518)</span>
                                </div>
                                <div>
                                    <span class="text-[9.5px] text-slate-400 block uppercase">Integritas Rangkaian:</span>
                                    <span class="font-bold text-emerald-400">30 PPCW (SIL 4 Valid)</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <span class="text-[9.5px] text-slate-400 block uppercase">HW-11 Smart E-Seal:</span>
                                    <span class="font-bold text-blue-300" id="sensorSealVal">INTACT (JT701 GPS)</span>
                                </div>
                                <div>
                                    <span class="text-[9.5px] text-slate-400 block uppercase">Status Bea Cukai:</span>
                                    <span class="font-bold text-emerald-400" id="sensorSppbVal">SPPB JALUR HIJAU</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dedicated Sensor Test Buttons + Operational Rail Discharge Action -->
                    <div class="space-y-2 pt-2 border-t border-gray-200/70">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">Pemicu Sensor &amp; Aksi Intermodal:</div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-1.5">
                            <button onclick="testSensorAction('hw10_axle')" class="py-1.5 px-2 bg-purple-600 hover:bg-purple-500 text-white rounded-lg font-bold text-[10.5px] transition flex items-center justify-center gap-1 shadow-2xs">
                                <span class="px-1 py-0.2 bg-white/20 rounded text-[9px] font-mono">HW-10</span>
                                <span>Cacah 126 Gandar</span>
                            </button>
                            <button onclick="testSensorAction('hw11_eseal')" class="py-1.5 px-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-bold text-[10.5px] transition flex items-center justify-center gap-1 shadow-2xs">
                                <span class="px-1 py-0.2 bg-white/20 rounded text-[9px] font-mono">HW-11</span>
                                <span>E-Seal &amp; SPPB</span>
                            </button>
                            <button onclick="triggerRailDischarge()" class="py-1.5 px-2 bg-purple-700 hover:bg-purple-600 text-white rounded-lg font-bold text-[10.5px] transition flex items-center justify-center gap-1 shadow-2xs">
                                <i class="fa-solid fa-train text-[10px]"></i>
                                <span>Bongkar KA (RTG)</span>
                            </button>
                        </div>
                        <div class="px-2.5 py-1 bg-emerald-50 rounded-md border border-emerald-200/80 text-[10px] text-emerald-800 font-semibold flex items-center justify-between">
                            <span><i class="fa-solid fa-file-invoice mr-1 text-emerald-600"></i>Dampak Operasional:</span>
                            <span class="font-bold text-emerald-900">Freight Rp 1.850.000/TEU &bull; Rilis Jaminan Pabean</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- --------------------------------------------------------------------- -->
        <!-- TAB 4: UJI SCANNER OPTIK & VERIFIKASI MUTU AIDC (GS1 / SSCC-18 / ISO) -->
        <!-- --------------------------------------------------------------------- -->
        <div id="tabContentScanner" class="space-y-4 hidden">
            
            <div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white p-4 rounded-xl border border-indigo-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/20 text-purple-300 flex items-center justify-center text-lg border border-purple-500/30 shrink-0">
                        <i class="fa-solid fa-barcode"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-xs sm:text-sm text-white">Konsol Uji Scanner Optik &amp; Dekoder Mutu AIDC</h4>
                        <p class="text-[11px] text-slate-300 mt-0.5">Uji verifikasi kode kontainer ISO 6346, Serial Shipping Container Code (SSCC-18), dan QR Pabean CEISA 4.0</p>
                    </div>
                </div>
                <a href="dashboard.php?page=scanner" class="px-3 py-1.5 bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold rounded-lg transition flex items-center gap-1.5 self-start sm:self-auto shrink-0 shadow-xs">
                    <i class="fa-solid fa-camera text-[11px]"></i><span>Buka Scanner Kamera Lengkap</span>
                </a>
            </div>

            <!-- Interaktif Tester Barcode & Validasi ISO 6346 -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                
                <!-- Kolom Kiri: Input & 1-Click Tester -->
                <div class="lg:col-span-6 bg-slate-50 rounded-xl border border-gray-200 p-4 space-y-3">
                    <div class="text-xs font-bold text-gray-700 uppercase tracking-wide flex items-center justify-between">
                        <span>Pilih Skenario Sampel Cepat:</span>
                        <span class="text-[10px] text-purple-600 font-mono">Standar GS1 v23</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <button onclick="testScannerSample('dry')" class="p-2.5 bg-white hover:bg-blue-50 border border-gray-200 hover:border-blue-300 rounded-lg text-left transition group">
                            <div class="text-[10px] font-bold text-blue-600 font-mono">ISO 6346 (Dry 40ft)</div>
                            <div class="font-bold text-gray-800 text-xs group-hover:text-blue-700">MSKU9182374</div>
                            <div class="text-[10px] text-gray-400 mt-0.5">Check Digit 4 &bull; Maersk Line</div>
                        </button>
                        <button onclick="testScannerSample('reefer')" class="p-2.5 bg-white hover:bg-cyan-50 border border-gray-200 hover:border-cyan-300 rounded-lg text-left transition group">
                            <div class="text-[10px] font-bold text-cyan-600 font-mono">ISO 6346 (Reefer 40ft)</div>
                            <div class="font-bold text-gray-800 text-xs group-hover:text-cyan-700">TEMU4819203</div>
                            <div class="text-[10px] text-gray-400 mt-0.5">Check Digit 3 &bull; Cold Chain</div>
                        </button>
                        <button onclick="testScannerSample('sscc')" class="p-2.5 bg-white hover:bg-purple-50 border border-gray-200 hover:border-purple-300 rounded-lg text-left transition group">
                            <div class="text-[10px] font-bold text-purple-600 font-mono">GS1-128 (SSCC-18)</div>
                            <div class="font-bold text-gray-800 text-xs group-hover:text-purple-700">(00) 38991234500000018</div>
                            <div class="text-[10px] text-gray-400 mt-0.5">Serial Shipping Pallet</div>
                        </button>
                        <button onclick="testScannerSample('customs')" class="p-2.5 bg-white hover:bg-emerald-50 border border-gray-200 hover:border-emerald-300 rounded-lg text-left transition group">
                            <div class="text-[10px] font-bold text-emerald-600 font-mono">QR Code CEISA 4.0</div>
                            <div class="font-bold text-gray-800 text-xs group-hover:text-emerald-700">SPPB-86434/KPU.01/2026</div>
                            <div class="text-[10px] text-gray-400 mt-0.5">Klirens Pabean Jalur Hijau</div>
                        </button>
                    </div>

                    <div class="pt-2 border-t border-gray-200">
                        <label class="block text-[10px] font-bold text-gray-600 uppercase mb-1">Uji Nomor Kontainer Kustom:</label>
                        <div class="flex gap-2">
                            <input type="text" id="customBoxInput" placeholder="Contoh: TCLU8827415" class="flex-1 px-3 py-1.5 text-xs font-mono uppercase bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-purple-500">
                            <button onclick="validateCustomBoxNumber()" class="px-3 py-1.5 bg-purple-700 hover:bg-purple-600 text-white text-xs font-bold rounded-lg transition">
                                Verifikasi
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Hasil Dekoder & Penjelasan Logika AIDC -->
                <div class="lg:col-span-6 bg-slate-900 text-white rounded-xl p-4 flex flex-col justify-between border border-slate-800 font-mono text-xs shadow-inner">
                    <div>
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-800 text-[10px]">
                            <span class="text-purple-400 uppercase font-bold tracking-wider">AIDC Decoder Result</span>
                            <span class="text-emerald-400" id="scanDecoderStatus"><i class="fa-solid fa-circle-check mr-1"></i>STANDBY</span>
                        </div>

                        <div class="space-y-1.5 text-[11px]" id="scanDecoderOutput">
                            <div><span class="text-slate-400">Tipe Kode:</span> <span class="text-blue-300 font-bold" id="decType">ISO 6346 Container ID</span></div>
                            <div><span class="text-slate-400">Payload Mentah:</span> <span class="text-amber-300 font-bold" id="decPayload">MSKU9182374</span></div>
                            <div><span class="text-slate-400">Owner Prefix:</span> <span class="text-slate-200" id="decOwner">MSKU (Maersk A/S)</span></div>
                            <div><span class="text-slate-400">Serial Number:</span> <span class="text-slate-200" id="decSerial">918237</span></div>
                            <div><span class="text-slate-400">Check Digit Calc:</span> <span class="text-emerald-400 font-bold" id="decCheck">4 (VALID - Modulo 11 Match)</span></div>
                            <div><span class="text-slate-400">Status YMS:</span> <span class="text-cyan-300 font-bold" id="decYms">Tersinkron di Blok B-08-03-02</span></div>
                        </div>
                    </div>

                    <div class="mt-3 pt-2 border-t border-slate-800 text-[10px] text-slate-400 font-sans leading-relaxed">
                        <strong class="text-slate-200">Mengapa AIDC Mutlak Dibutuhkan?</strong> Pemindaian optik otomatis mencegah salah catat nomor kontainer (angka 0 dan huruf O, angka 1 dan huruf I), sehingga demurrage, lokasi stack, dan penagihan billing selalu akurat 100%.
                    </div>
                </div>

            </div>

        </div>

        <!-- --------------------------------------------------------------------- -->
        <!-- TAB 5: KATALOG EDUKASI 11 HARDWARE & ALUR FINANSIAL ERP               -->
        <!-- --------------------------------------------------------------------- -->
        <div id="tabContentCatalog" class="space-y-3 hidden">
            
            <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                <div>
                    <h4 class="font-bold text-gray-900 text-xs sm:text-sm">Matriks Pemetaan 11 Perangkat Keras, Software YMS &amp; Dampak Finansial</h4>
                    <p class="text-[11px] text-gray-500">Katalog standar akademik untuk ujian Topik 7 Inland Container Depot &amp; Dry Port Management ITL Trisakti</p>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    Conclusion Consultant
                </span>
            </div>

            <div class="overflow-x-auto border border-gray-200 rounded-xl">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-slate-50 text-gray-700 font-bold border-b border-gray-200">
                        <tr>
                            <th class="p-2.5">Hardware &amp; Tipe</th>
                            <th class="p-2.5">Model Terpasang</th>
                            <th class="p-2.5">Pemicu Fisik Lapangan</th>
                            <th class="p-2.5">Sinyal / Protokol</th>
                            <th class="p-2.5">Logika Software YMS</th>
                            <th class="p-2.5">Pemicu Finansial ERP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-sans text-[11px]">
                        <tr class="hover:bg-blue-50/20">
                            <td class="p-2.5 font-bold text-cdp-navy">HW-01: Kamera ANPR</td>
                            <td class="p-2.5 text-gray-600">Hikvision DS-TCG406-E</td>
                            <td class="p-2.5">Truk melintas loop magnetik gerbang</td>
                            <td class="p-2.5 font-mono text-purple-700">String Plat Nomor via REST API</td>
                            <td class="p-2.5">Validasi izin armada &amp; catat jam masuk</td>
                            <td class="p-2.5 font-semibold text-emerald-700">Pas Gerbang Truk (Rp 50.000)</td>
                        </tr>
                        <tr class="hover:bg-blue-50/20">
                            <td class="p-2.5 font-bold text-cdp-navy">HW-02: Portal OCR Box</td>
                            <td class="p-2.5 text-gray-600">Hikvision iDS-TCV300-A6I</td>
                            <td class="p-2.5">Kontainer melintasi portal multi-sudut</td>
                            <td class="p-2.5 font-mono text-purple-700">ISO 6346 String via TCP/IP</td>
                            <td class="p-2.5">Verifikasi Check Digit ISO Modulo 11</td>
                            <td class="p-2.5 font-semibold text-emerald-700">Argo Demurrage Free Time Dimulai</td>
                        </tr>
                        <tr class="hover:bg-blue-50/20">
                            <td class="p-2.5 font-bold text-cdp-navy">HW-03: Timbangan 80T</td>
                            <td class="p-2.5 text-gray-600">Fangda Electronic Scale 80T</td>
                            <td class="p-2.5">Gandar truk berhenti di platform baja</td>
                            <td class="p-2.5 font-mono text-purple-700">Data Bobot via RS-232 / Modbus</td>
                            <td class="p-2.5">Kalkulasi Net VGM sesuai IMO SOLAS</td>
                            <td class="p-2.5 font-semibold text-emerald-700">Sertifikat VGM SOLAS (Rp 120.000)</td>
                        </tr>
                        <tr class="hover:bg-blue-50/20">
                            <td class="p-2.5 font-bold text-cdp-navy">HW-04: RFID UHF 20m</td>
                            <td class="p-2.5 text-gray-600">Hopeland Long-Range ISO 18000-6C</td>
                            <td class="p-2.5">Transponder di kaca truk terbaca 20m</td>
                            <td class="p-2.5 font-mono text-purple-700">Hex Tag ID via Wiegand-34</td>
                            <td class="p-2.5">Otentikasi touchless supir &amp; saldo e-wallet</td>
                            <td class="p-2.5 font-semibold text-emerald-700">Auto-debit saldo deposit trucking</td>
                        </tr>
                        <tr class="hover:bg-blue-50/20">
                            <td class="p-2.5 font-bold text-cdp-navy">HW-05: VMS Gate LED</td>
                            <td class="p-2.5 text-gray-600">Chipshow Outdoor P6/P8</td>
                            <td class="p-2.5">Seluruh sensor gerbang berstatus VALID</td>
                            <td class="p-2.5 font-mono text-purple-700">String Pesan Rute via IP UDP</td>
                            <td class="p-2.5">Menampilkan blok tujuan supir di layar</td>
                            <td class="p-2.5 font-semibold text-emerald-700">Pangkas Waktu Antre (TAT -60%)</td>
                        </tr>
                        <tr class="hover:bg-blue-50/20">
                            <td class="p-2.5 font-bold text-cdp-navy">HW-06: Edge AI PC</td>
                            <td class="p-2.5 text-gray-600">Advantech ARK-3532 Fanless</td>
                            <td class="p-2.5">Verifikasi data gerbang tervalidasi</td>
                            <td class="p-2.5 font-mono text-purple-700">Relay GPIO Dry-Contact 1.2 Detik</td>
                            <td class="p-2.5">Local interlock buka palang pintu (12ms)</td>
                            <td class="p-2.5 font-semibold text-emerald-700">Eliminasi downtime macet gerbang</td>
                        </tr>
                        <tr class="hover:bg-blue-50/20">
                            <td class="p-2.5 font-bold text-cdp-navy">HW-07: RTK DGPS GNSS</td>
                            <td class="p-2.5 text-gray-600">CHCNAV CGI-610 Centimeter</td>
                            <td class="p-2.5">Alat berat bergerak di koridor yard</td>
                            <td class="p-2.5 font-mono text-purple-700">NMEA-0183 ($GPGGA) Akurasi &lt;2 cm</td>
                            <td class="p-2.5">Translasi GPS ke Blok, Bay, Row, Tier</td>
                            <td class="p-2.5 font-semibold text-emerald-700">Zero Misplacement (Nol Biaya Shifting)</td>
                        </tr>
                        <tr class="hover:bg-blue-50/20">
                            <td class="p-2.5 font-bold text-cdp-navy">HW-08: Spreader Twistlock</td>
                            <td class="p-2.5 text-gray-600">Bromma Spreader &amp; Load Cell Kit</td>
                            <td class="p-2.5">Spreader mendarat di corner casting</td>
                            <td class="p-2.5 font-mono text-purple-700">4 Proximity Switch + Strain Gauge CAN</td>
                            <td class="p-2.5">Izin hoist angkat &amp; validasi bobot box</td>
                            <td class="p-2.5 font-semibold text-emerald-700">Jasa Lo-Lo Lift-Off (Rp 250.000)</td>
                        </tr>
                        <tr class="hover:bg-blue-50/20">
                            <td class="p-2.5 font-bold text-cdp-navy">HW-09: Smart Reefer Socket</td>
                            <td class="p-2.5 text-gray-600">Marechal 380V/32A Modbus RTU</td>
                            <td class="p-2.5">Steker kontainer reefer dicolokkan ke rak</td>
                            <td class="p-2.5 font-mono text-purple-700">Register Modbus RS-485 (V, A, kW, &deg;C)</td>
                            <td class="p-2.5">Logging telemetri suhu &amp; alarm anomali</td>
                            <td class="p-2.5 font-semibold text-emerald-700">Sewa Listrik Reefer (Rp 35.000 / jam)</td>
                        </tr>
                        <tr class="hover:bg-blue-50/20">
                            <td class="p-2.5 font-bold text-cdp-navy">HW-10: Frauscher Axle Counter</td>
                            <td class="p-2.5 text-gray-600">Frauscher RSR123 Wheel Sensor SIL 4</td>
                            <td class="p-2.5">Roda kereta melintasi sensor rel</td>
                            <td class="p-2.5 font-mono text-purple-700">Pulsa Induktif Gandar Kereta Api</td>
                            <td class="p-2.5">Cacah 126 as roda gerbong KA utuh</td>
                            <td class="p-2.5 font-semibold text-emerald-700">Verifikasi Integritas Rangkaian KA</td>
                        </tr>
                        <tr class="hover:bg-blue-50/20">
                            <td class="p-2.5 font-bold text-cdp-navy">HW-11: Smart E-Seal Pabean</td>
                            <td class="p-2.5 text-gray-600">Jointech JT701 GPS Smart Lock</td>
                            <td class="p-2.5">Peti kemas transit KA Priok-Cikarang</td>
                            <td class="p-2.5 font-mono text-purple-700">RFID Nirkabel &amp; GPS/GSM Telemetri</td>
                            <td class="p-2.5">Verifikasi segel INTACT ke CEISA 4.0</td>
                            <td class="p-2.5 font-semibold text-emerald-700">SPPB Terbit &amp; Rilis Jaminan Pabean</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

    </div>

</div>

<!-- MODAL AKSI: PEMINDAHAN KONTAINER OLEH REACH STACKER -->
<div id="modalMoveBox" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-gray-100 transform transition-all">
        <div class="bg-gradient-to-r from-[#002f5e] to-[#0170b9] p-5 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-amber-300 text-lg">
                    <i class="fa-solid fa-arrows-up-down-left-right"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg leading-tight">Perintah Relokasi Box (Reach Stacker)</h3>
                    <p class="text-xs text-blue-100 mt-0.5">Eksekusi pemindahan kontainer di area yard 3D</p>
                </div>
            </div>
            <button onclick="closeMoveModal()" class="text-white/80 hover:text-white text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="formMoveBox" onsubmit="handleMoveSubmit(event)" class="p-6 space-y-4">
            
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">1. Pilih Kontainer yang Akan Dipindahkan</label>
                <select id="moveContainerSelect" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#0170b9]">
                    <!-- Populated dynamically from DB -->
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">2. Alokasikan Unit Reach Stacker</label>
                <select id="moveEquipmentSelect" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#0170b9]">
                    <option value="RS-02">🚜 RS-02 (Operator: Agus Setiawan - Block B Area)</option>
                    <option value="RS-01">🚜 RS-01 (Operator: Budi Santoso - Block A Area)</option>
                    <option value="RS-03">🚜 RS-03 (Operator: Rudi Hermawan - Reefer Area)</option>
                </select>
            </div>

            <div class="p-4 bg-blue-50/50 rounded-xl border border-blue-100">
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">3. Tentukan Koordinat 3D Slot Tujuan</label>
                <div class="grid grid-cols-4 gap-2">
                    <div>
                        <span class="block text-[10px] text-gray-500 font-semibold mb-1">Blok</span>
                        <select id="moveToBlock" class="w-full bg-white border border-gray-200 rounded-lg px-2 py-2 text-xs font-bold text-gray-800">
                            <option value="A">Blok A (Export)</option>
                            <option value="B" selected>Blok B (Import)</option>
                            <option value="C">Blok C (Domestik)</option>
                            <option value="D">Blok D (Buffer)</option>
                            <option value="REEFER">Reefer</option>
                            <option value="DG">DG Hazmat</option>
                            <option value="EMPTY">Empty Depot</option>
                        </select>
                    </div>
                    <div>
                        <span class="block text-[10px] text-gray-500 font-semibold mb-1">Bay (01-05)</span>
                        <input type="text" id="moveToBay" value="02" class="w-full bg-white border border-gray-200 rounded-lg px-2 py-2 text-xs font-bold text-gray-800 font-mono text-center">
                    </div>
                    <div>
                        <span class="block text-[10px] text-gray-500 font-semibold mb-1">Row (01-07)</span>
                        <input type="text" id="moveToRow" value="03" class="w-full bg-white border border-gray-200 rounded-lg px-2 py-2 text-xs font-bold text-gray-800 font-mono text-center">
                    </div>
                    <div>
                        <span class="block text-[10px] text-gray-500 font-semibold mb-1">Tier (01-04)</span>
                        <input type="text" id="moveToTier" value="02" class="w-full bg-white border border-gray-200 rounded-lg px-2 py-2 text-xs font-bold text-gray-800 font-mono text-center">
                    </div>
                </div>
            </div>

            <div class="bg-amber-50 p-3 rounded-xl border border-amber-200 text-xs text-amber-800 flex items-start space-x-2">
                <i class="fa-solid fa-circle-info text-amber-600 mt-0.5"></i>
                <span>Aksi ini akan menganimasikan Reach Stacker di kanvas 3D, memindahkan box ke slot baru, dan mencatat event tagihan Lo-Lo (Rp 250.000) di database.</span>
            </div>

            <div class="pt-2 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeMoveModal()" class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 text-xs font-semibold">
                    Batal
                </button>
                <button type="submit" id="btnSubmitMove" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-gray-950 text-xs font-bold shadow-md transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-play"></i>
                    <span>Jalankan Relokasi Box</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================================= -->
<!-- MODAL INTERAKTIF: ARSITEKTUR ALUR END-TO-END 11 HARDWARE, SOFTWARE & FINANSIAL -->
<!-- ============================================================================= -->
<div id="modalEndToEndGuide" class="fixed inset-0 z-50 hidden bg-black/75 backdrop-blur-md flex items-center justify-center p-3 sm:p-5">
    <div class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[92vh] flex flex-col overflow-hidden border border-gray-100 transform transition-all animate-fadeIn">
        
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-[#002f5e] via-[#004b87] to-[#0170b9] p-4 sm:p-5 text-white flex items-center justify-between shrink-0">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/15 border border-white/20 flex items-center justify-center text-emerald-300 text-lg shadow-inner">
                    <i class="fa-solid fa-network-wired"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h3 class="font-bold text-base sm:text-lg leading-tight">Arsitektur Alur End-to-End: Hardware &bull; Software &bull; Finansial</h3>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-500/90 text-white uppercase tracking-wider">Master Guide</span>
                    </div>
                    <p class="text-xs text-blue-100 mt-0.5">Panduan Demonstrasi Konsultan: Interaksi 11 Hardware Live, Pengolahan Data YMS, &amp; Pemicu Billing ERP</p>
                </div>
            </div>
            <button onclick="closeEndToEndGuideModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Navigation Tabs -->
        <div class="bg-slate-100/80 px-4 sm:px-5 pt-3 border-b border-gray-200 flex items-center space-x-2 shrink-0 overflow-x-auto">
            <button onclick="switchGuideTab('pipeline')" id="tabBtnPipeline" class="guide-tab-btn px-3.5 py-2 text-xs font-bold rounded-t-xl transition-all border-b-2 border-[#0170b9] bg-white text-[#004b87] shadow-xs flex items-center space-x-2">
                <i class="fa-solid fa-diagram-project text-xs"></i>
                <span>1. Alur 6 Tahap End-to-End</span>
            </button>
            <button onclick="switchGuideTab('hardware')" id="tabBtnHardware" class="guide-tab-btn px-3.5 py-2 text-xs font-medium rounded-t-xl transition-all border-b-2 border-transparent text-gray-600 hover:text-gray-900 flex items-center space-x-2">
                <i class="fa-solid fa-microchip text-xs"></i>
                <span>2. Matriks 11 Hardware Live Telemetri</span>
            </button>
            <button onclick="switchGuideTab('cheatsheet')" id="tabBtnCheatsheet" class="guide-tab-btn px-3.5 py-2 text-xs font-medium rounded-t-xl transition-all border-b-2 border-transparent text-gray-600 hover:text-gray-900 flex items-center space-x-2">
                <i class="fa-solid fa-circle-question text-xs"></i>
                <span>3. FAQ &amp; Arsitektur Teknis YMS</span>
            </button>
        </div>

        <!-- Scrollable Tab Content Container -->
        <div class="p-4 sm:p-6 overflow-y-auto flex-1 space-y-6 text-gray-800 text-xs sm:text-sm">
            
            <!-- TAB 1: 6 TAHAP PIPELINE END-TO-END -->
            <div id="guideTabPipeline" class="space-y-4">
                <div class="bg-blue-50/70 border border-blue-200 rounded-xl p-3.5 flex items-start space-x-3">
                    <i class="fa-solid fa-circle-nodes text-cdp-blue text-base mt-0.5"></i>
                    <div>
                        <h4 class="font-bold text-gray-900 text-xs sm:text-sm">Triad Aliran Logistik Pelabuhan Kering (Dry Port Triad Flow)</h4>
                        <p class="text-xs text-gray-600 mt-0.5 leading-relaxed">
                            Di CIDP, pergerakan <strong>Fisik &amp; Sensor Hardware</strong> memicu pemutakhiran data di <strong>Software CIDP YMS</strong>, yang selanjutnya secara otomatis membentuk pos biaya dan jurnal akuntansi di modul <strong>Finansial &amp; Billing ERP</strong>.
                        </p>
                    </div>
                </div>

                <!-- 6 Step Visual Timeline -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    
                    <!-- Tahap 1 -->
                    <div class="bg-white rounded-xl border-2 border-blue-200 p-3.5 shadow-2xs hover:shadow-sm transition flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-blue-100 text-blue-800 uppercase">Tahap 01</span>
                                <span class="text-[10px] font-bold text-gray-400 font-mono">Gate Inbound</span>
                            </div>
                            <h5 class="font-bold text-gray-900 text-xs sm:text-sm mb-1">Otomasi Gerbang &amp; Timbangan VGM</h5>
                            <p class="text-[11px] text-gray-600 leading-relaxed mb-2">
                                Truk tiba &rarr; RFID membaca e-Pass sopir &rarr; ANPR merekam plat &rarr; Portal OCR memindai kontainer &rarr; Timbangan 80T mencatat tonase.
                            </p>
                            <div class="p-2 bg-slate-50 rounded-lg text-[10.5px] space-y-1 font-mono">
                                <div><strong class="text-blue-700">Hardware:</strong> HW-01, 02, 03, 04, 05, 06</div>
                                <div><strong class="text-indigo-700">Software:</strong> Validasi SOLAS VGM, Check Digit ISO</div>
                                <div><strong class="text-emerald-700">Finansial:</strong> Pas Gerbang (Rp 50k) + Sertifikat VGM (Rp 120k)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tahap 2 -->
                    <div class="bg-white rounded-xl border-2 border-amber-200 p-3.5 shadow-2xs hover:shadow-sm transition flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-amber-100 text-amber-800 uppercase">Tahap 02</span>
                                <span class="text-[10px] font-bold text-gray-400 font-mono">Yard Stacking</span>
                            </div>
                            <h5 class="font-bold text-gray-900 text-xs sm:text-sm mb-1">Penataan Blok &amp; Telemetri RTK</h5>
                            <p class="text-[11px] text-gray-600 leading-relaxed mb-2">
                                Truk ke Blok Yard &rarr; Reach Stacker dengan RTK DGPS mendeteksi koordinat &lt;2 cm &rarr; Spreader Twistlock mengunci &amp; mengangkat box.
                            </p>
                            <div class="p-2 bg-slate-50 rounded-lg text-[10.5px] space-y-1 font-mono">
                                <div><strong class="text-blue-700">Hardware:</strong> HW-07 (RTK GPS), HW-08 (Twistlock)</div>
                                <div><strong class="text-indigo-700">Software:</strong> Status 'in_yard', kunci slot Bay-Row-Tier</div>
                                <div><strong class="text-emerald-700">Finansial:</strong> Jasa Lift-Off (Rp 250k) + Argo Storage Dwell Time</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tahap 3 -->
                    <div class="bg-white rounded-xl border-2 border-cyan-200 p-3.5 shadow-2xs hover:shadow-sm transition flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-cyan-100 text-cyan-800 uppercase">Tahap 03</span>
                                <span class="text-[10px] font-bold text-gray-400 font-mono">Cold Chain Yard</span>
                            </div>
                            <h5 class="font-bold text-gray-900 text-xs sm:text-sm mb-1">Monitoring Reefer IoT</h5>
                            <p class="text-[11px] text-gray-600 leading-relaxed mb-2">
                                Peti kemas reefer ditaruh di rak &rarr; Dicolokkan ke Smart Power Socket 380V &rarr; Sensor Modbus memantau listrik &amp; suhu -20.2&deg;C.
                            </p>
                            <div class="p-2 bg-slate-50 rounded-lg text-[10.5px] space-y-1 font-mono">
                                <div><strong class="text-blue-700">Hardware:</strong> HW-09 (Smart Socket Marechal)</div>
                                <div><strong class="text-indigo-700">Software:</strong> Telemetri suhu 24 jam &amp; Alarm Ambang Batas</div>
                                <div><strong class="text-emerald-700">Finansial:</strong> Plugging &amp; Power Fee (Rp 35.000 / jam)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tahap 4 -->
                    <div class="bg-white rounded-xl border-2 border-rose-200 p-3.5 shadow-2xs hover:shadow-sm transition flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-rose-100 text-rose-800 uppercase">Tahap 04</span>
                                <span class="text-[10px] font-bold text-gray-400 font-mono">Kepabeanan</span>
                            </div>
                            <h5 class="font-bold text-gray-900 text-xs sm:text-sm mb-1">Klirens Pabean CEISA 4.0 &amp; E-Seal</h5>
                            <p class="text-[11px] text-gray-600 leading-relaxed mb-2">
                                Muatan transit Priok-Cikarang diamankan Smart E-Seal (GPS Anti-Tamper) &rarr; Integrasi API CEISA Bea Cukai &rarr; SPPB Terbit.
                            </p>
                            <div class="p-2 bg-slate-50 rounded-lg text-[10.5px] space-y-1 font-mono">
                                <div><strong class="text-blue-700">Hardware:</strong> HW-11 (Jointech E-Seal GPS)</div>
                                <div><strong class="text-indigo-700">Software:</strong> Jalur Hijau/Merah (PMK 190/2022), SPPB Auto</div>
                                <div><strong class="text-emerald-700">Finansial:</strong> Pelepasan Jaminan Pabean (Customs Bond)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tahap 5 -->
                    <div class="bg-white rounded-xl border-2 border-purple-200 p-3.5 shadow-2xs hover:shadow-sm transition flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-purple-100 text-purple-800 uppercase">Tahap 05</span>
                                <span class="text-[10px] font-bold text-gray-400 font-mono">Rail Siding</span>
                            </div>
                            <h5 class="font-bold text-gray-900 text-xs sm:text-sm mb-1">Alih Muat Kereta Api Intermodal</h5>
                            <p class="text-[11px] text-gray-600 leading-relaxed mb-2">
                                KA 2518 tiba &rarr; Axle Counter menghitung 126 as roda gerbong PPCW &rarr; RTG alih muatkan box ke KA &rarr; Status YMS 'on_rail'.
                            </p>
                            <div class="p-2 bg-slate-50 rounded-lg text-[10.5px] space-y-1 font-mono">
                                <div><strong class="text-blue-700">Hardware:</strong> HW-10 (Frauscher Axle Counter), RTG</div>
                                <div><strong class="text-indigo-700">Software:</strong> Rail Manifest Interchange, Slot Gerbong Datar</div>
                                <div><strong class="text-emerald-700">Finansial:</strong> Rail Haulage (Rp 1.85M/TEU) + ESG Karbon</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tahap 6 -->
                    <div class="bg-white rounded-xl border-2 border-emerald-200 p-3.5 shadow-2xs hover:shadow-sm transition flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-100 text-emerald-800 uppercase">Tahap 06</span>
                                <span class="text-[10px] font-bold text-gray-400 font-mono">Gate-Out &amp; Billing</span>
                            </div>
                            <h5 class="font-bold text-gray-900 text-xs sm:text-sm mb-1">Settlement ERP &amp; Pelepasan Truk</h5>
                            <p class="text-[11px] text-gray-600 leading-relaxed mb-2">
                                Validasi pelunasan proforma invoice di Billing ERP &rarr; SP2B (Surat Pengeluaran) terbit &rarr; Barrier Gate Out terbuka &rarr; Box keluar.
                            </p>
                            <div class="p-2 bg-slate-50 rounded-lg text-[10.5px] space-y-1 font-mono">
                                <div><strong class="text-blue-700">Hardware:</strong> HW-01 (ANPR Out), HW-04 (RFID), Barrier</div>
                                <div><strong class="text-indigo-700">Software:</strong> Validasi lunas kasir/deposit, status 'gate_out'</div>
                                <div><strong class="text-emerald-700">Finansial:</strong> Pelunasan Invoice, e-Faktur Pajak, Resi Bank</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- TAB 2: MATRIKS 11 HARDWARE LIVE TELEMETRI -->
            <div id="guideTabHardware" class="space-y-3 hidden">
                <p class="text-xs text-gray-500 mb-2">
                    Berikut adalah tabel pemetaan 11 perangkat keras yang terintegrasi langsung dengan antarmuka telemetri software CIDP YMS:
                </p>

                <div class="overflow-x-auto border border-gray-200 rounded-xl">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-slate-50 text-gray-700 font-bold border-b border-gray-200">
                            <tr>
                                <th class="p-2.5">No &amp; Hardware</th>
                                <th class="p-2.5">Model / Spesifikasi</th>
                                <th class="p-2.5">Pemicu Fisik Lapangan</th>
                                <th class="p-2.5">Sinyal / Protokol Data</th>
                                <th class="p-2.5">Pengolahan Data YMS</th>
                                <th class="p-2.5">Pemicu Finansial ERP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 font-sans text-[11px]">
                            <tr class="hover:bg-blue-50/20">
                                <td class="p-2.5 font-bold text-cdp-navy">HW-01: Kamera ANPR</td>
                                <td class="p-2.5 text-gray-600">Hikvision DS-TCG406-E</td>
                                <td class="p-2.5">Truk melintasi sensor loop magnetik di aspal</td>
                                <td class="p-2.5 font-mono text-purple-700">String Plat Nomor + Foto via REST API</td>
                                <td class="p-2.5">Validasi izin armada &amp; pencatatan jam gate-in</td>
                                <td class="p-2.5 font-semibold text-emerald-700">Tarif Pas Masuk Gerbang (Rp 50.000)</td>
                            </tr>
                            <tr class="hover:bg-blue-50/20">
                                <td class="p-2.5 font-bold text-cdp-navy">HW-02: Kamera OCR Kontainer</td>
                                <td class="p-2.5 text-gray-600">Hikvision iDS-TCV300-A6I</td>
                                <td class="p-2.5">Peti kemas melintasi portal multi-angle 5.5m</td>
                                <td class="p-2.5 font-mono text-purple-700">Kode ISO 6346 (4 huruf + 7 angka) via TCP/IP</td>
                                <td class="p-2.5">Validasi Check Digit ISO &amp; pencocokan DO/BL</td>
                                <td class="p-2.5 font-semibold text-emerald-700">Argo Masa Penumpukan / Free Time Aktif</td>
                            </tr>
                            <tr class="hover:bg-blue-50/20">
                                <td class="p-2.5 font-bold text-cdp-navy">HW-03: Timbangan Truk 80T</td>
                                <td class="p-2.5 text-gray-600">Fangda Electronic 80 Ton</td>
                                <td class="p-2.5">Gandar truk berhenti 5 detik di platform baja</td>
                                <td class="p-2.5 font-mono text-purple-700">Data bobot kotor numerik via RS-232 / Modbus</td>
                                <td class="p-2.5">Kalkulasi Net VGM sesuai amandemen SOLAS</td>
                                <td class="p-2.5 font-semibold text-emerald-700">Biaya Sertifikasi VGM (Rp 120.000)</td>
                            </tr>
                            <tr class="hover:bg-blue-50/20">
                                <td class="p-2.5 font-bold text-cdp-navy">HW-04: RFID Reader UHF</td>
                                <td class="p-2.5 text-gray-600">Long-Range UHF 20m 860-960MHz</td>
                                <td class="p-2.5">Kartu e-Pass di kaca truk masuk radius 20 meter</td>
                                <td class="p-2.5 font-mono text-purple-700">24-digit Hex Tag ID via Wiegand-34 / RS-485</td>
                                <td class="p-2.5">Otentikasi touchless supir &amp; status deposit armada</td>
                                <td class="p-2.5 font-semibold text-emerald-700">Auto-debit saldo deposit trucking mitra</td>
                            </tr>
                            <tr class="hover:bg-blue-50/20">
                                <td class="p-2.5 font-bold text-cdp-navy">HW-05: LED Display Gate</td>
                                <td class="p-2.5 text-gray-600">Chipshow Outdoor P6/P8</td>
                                <td class="p-2.5">Seluruh sensor gerbang berstatus VALID</td>
                                <td class="p-2.5 font-mono text-purple-700">Paket string teks instruksi ASCII via IP UDP</td>
                                <td class="p-2.5">Menampilkan blok tujuan: "SILAHKAN KE BLOK B-08"</td>
                                <td class="p-2.5 font-semibold text-emerald-700">Efisiensi TAT (Pangkas waktu antre &gt;60%)</td>
                            </tr>
                            <tr class="hover:bg-blue-50/20">
                                <td class="p-2.5 font-bold text-cdp-navy">HW-06: Industrial Edge PC</td>
                                <td class="p-2.5 text-gray-600">Advantech ARK-3532 Fanless</td>
                                <td class="p-2.5">Truk mendekat &amp; menerima aliran data sensor</td>
                                <td class="p-2.5 font-mono text-purple-700">Relay Digital Output Dry-Contact ke PLC Barrier</td>
                                <td class="p-2.5">Local interlocking: Buka palang dalam 1.2 detik</td>
                                <td class="p-2.5 font-semibold text-emerald-700">Meniadakan kerugian downtime gerbang</td>
                            </tr>
                            <tr class="hover:bg-blue-50/20">
                                <td class="p-2.5 font-bold text-cdp-navy">HW-07: DGPS/RTK GNSS</td>
                                <td class="p-2.5 text-gray-600">CHCNAV CGI-610 Centimeter</td>
                                <td class="p-2.5">Reach Stacker bergerak di koridor yard 35 Ha</td>
                                <td class="p-2.5 font-mono text-purple-700">Koordinat NMEA-0183 ($GPGGA) akurasi &lt;2 cm</td>
                                <td class="p-2.5">Translasi GPS &rarr; Blok, Bay, Row, Tier 3D</td>
                                <td class="p-2.5 font-semibold text-emerald-700">Meniadakan biaya Rehandling salah letak</td>
                            </tr>
                            <tr class="hover:bg-blue-50/20">
                                <td class="p-2.5 font-bold text-cdp-navy">HW-08: Spreader Twistlock</td>
                                <td class="p-2.5 text-gray-600">Bromma SmartSpreader Sensor Kit</td>
                                <td class="p-2.5">Pin twistlock berputar mengunci &amp; mengangkat box</td>
                                <td class="p-2.5 font-mono text-purple-700">Sinyal digital biner (LOCKED/UNLOCKED) CANopen</td>
                                <td class="p-2.5">Deteksi otomatis alih muat ('in_transit'/'in_yard')</td>
                                <td class="p-2.5 font-semibold text-emerald-700">Tagihan Jasa Lift-Off (Rp 250.000 / gerakan)</td>
                            </tr>
                            <tr class="hover:bg-blue-50/20">
                                <td class="p-2.5 font-bold text-cdp-navy">HW-09: Smart Reefer Socket</td>
                                <td class="p-2.5 text-gray-600">Marechal 380V/32A Receptacle</td>
                                <td class="p-2.5">Steker kontainer reefer dicolokkan ke stopkontak</td>
                                <td class="p-2.5 font-mono text-purple-700">Tegangan 380V, arus Ampere, suhu (-20.2&deg;C) Modbus</td>
                                <td class="p-2.5">Monitoring cold chain 24 jam &amp; alarm darurat</td>
                                <td class="p-2.5 font-semibold text-emerald-700">Tagihan Plugging Listrik (Rp 35.000 / jam)</td>
                            </tr>
                            <tr class="hover:bg-blue-50/20">
                                <td class="p-2.5 font-bold text-cdp-navy">HW-10: Rail Axle Counter</td>
                                <td class="p-2.5 text-gray-600">Frauscher RSR123 Wheel Sensor</td>
                                <td class="p-2.5">Gandar roda KA melintas di atas rel sepur simpang</td>
                                <td class="p-2.5 font-mono text-purple-700">Pulsa induksi medan magnet cacah 126 as roda</td>
                                <td class="p-2.5">Deteksi kedatangan KA logistik &amp; buka order RTG</td>
                                <td class="p-2.5 font-semibold text-emerald-700">Freight Rail Haulage (Rp 1.850.000 / TEU)</td>
                            </tr>
                            <tr class="hover:bg-blue-50/20">
                                <td class="p-2.5 font-bold text-cdp-navy">HW-11: Smart E-Seal Pabean</td>
                                <td class="p-2.5 text-gray-600">Jointech JT701 GPS Smart Lock</td>
                                <td class="p-2.5">Kawat segel dipasang di Priok / dibuka di CIDP</td>
                                <td class="p-2.5 font-mono text-purple-700">Status GPS, kawat segel INTACT via 4G ke CEISA</td>
                                <td class="p-2.5">Validasi Jalur Hijau &amp; penerbitan otomatis SPPB</td>
                                <td class="p-2.5 font-semibold text-emerald-700">Pelepasan Jaminan Pabean (Customs Bond)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: FAQ & ARSITEKTUR TEKNIS -->
            <div id="guideTabCheatsheet" class="space-y-4 hidden">
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3.5 flex items-start space-x-3">
                    <i class="fa-solid fa-lightbulb text-amber-600 text-base mt-0.5"></i>
                    <div>
                        <h4 class="font-bold text-amber-950 text-xs sm:text-sm">Arsitektur Teknis &amp; Dampak Finansial YMS</h4>
                        <p class="text-xs text-amber-800 mt-0.5">
                            Struktur operasional konsultan: <strong>1. Sinyal Fisik Hardware &rarr; 2. Logika Pemrosesan YMS &rarr; 3. Dampak Finansial &amp; Operasional Terminal</strong>.
                        </p>
                    </div>
                </div>

                <div class="space-y-3">
                    <!-- Q1 -->
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-2xs">
                        <span class="text-[10px] font-bold text-cdp-blue uppercase block mb-1">Pertanyaan 1: Integrasi Hardware Gerbang</span>
                        <h5 class="font-bold text-gray-900 text-sm mb-2">"Bagaimana kamera gerbang, timbangan, dan sistem web saling terhubung tanpa delay?"</h5>
                        <p class="text-xs text-gray-600 leading-relaxed bg-slate-50 p-3 rounded-lg border border-gray-100">
                            <em>"Seluruh sensor di gerbang (ANPR, OCR kontainer, timbangan 80T, dan RFID) tidak mengirim data mentah langsung ke server cloud yang rawan latency. Kami menempatkan <strong>Advantech Fanless Industrial Edge PC</strong> tepat di tiang gerbang. Edge PC memproses OCR dan menimbang bobot kotor VGM secara lokal dalam hitungan milidetik. Begitu seluruh parameter valid, sistem mengirim sinyal relay membuka Barrier Gate dalam 1.2 detik sembari mengarahkan supir via LED Display. Secara finansial, aksi ini seketika membentuk pos piutang Pas Gerbang (Rp 50.000) dan Jasa VGM (Rp 120.000) di modul Billing ERP."</em>
                        </p>
                    </div>

                    <!-- Q2 -->
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-2xs">
                        <span class="text-[10px] font-bold text-cdp-blue uppercase block mb-1">Pertanyaan 2: Presisi Penumpukan Lapangan</span>
                        <h5 class="font-bold text-gray-900 text-sm mb-2">"Bagaimana software tahu kontainer ditaruh di Bay-Row-Tier tertentu tanpa operator salah ketik?"</h5>
                        <p class="text-xs text-gray-600 leading-relaxed bg-slate-50 p-3 rounded-lg border border-gray-100">
                            <em>"Kami menerapkan otomatisasi ganda di Reach Stacker: Pertama, sensor <strong>CHCNAV RTK GNSS</strong> di atap kabin dengan akurasi &lt;2 cm yang terus menerjemahkan koordinat latitude/longitude ke grid 3D Blok-Bay-Row-Tier. Kedua, <strong>Bromma Spreader Twistlock &amp; Load Cell Sensor</strong>. Begitu pin twistlock membuka dan beban terlepas di atas tumpukan, sensor mendeteksi penurunan regangan dan seketika mengunci koordinat slot baru di MySQL tanpa input manual sopir. Momen pelepasan ini juga langsung memicu pencatatan biaya Lift-Off (Rp 250.000) pada tagihan pemilik kargo."</em>
                        </p>
                    </div>

                    <!-- Q3 -->
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-2xs">
                        <span class="text-[10px] font-bold text-cdp-blue uppercase block mb-1">Pertanyaan 3: Nilai Tambah Intermodal Rel KA</span>
                        <h5 class="font-bold text-gray-900 text-sm mb-2">"Mengapa harus ada rel kereta api di dalam dry port dan bagaimana hardware memantau integritasnya?"</h5>
                        <p class="text-xs text-gray-600 leading-relaxed bg-slate-50 p-3 rounded-lg border border-gray-100">
                            <em>"Pelabuhan kering Cikarang berjarak 54.8 km dari Pelabuhan Tanjung Priok. Mengangkut ribuan kontainer via Tol Cikampek menimbulkan kongesti dan biaya logistik tinggi. Jalur rail siding kami dilengkapi <strong>Frauscher Axle Counter</strong> yang memverifikasi 126 as roda dari 30 gerbong datar PPCW secara otomatis. Begitu KA terdeteksi aman, RTG langsung melakukan alih muat. Biaya angkutan rel (Rp 1.850.000/TEU) jauh lebih ekonomis dan kepastian waktunya terjamin, sekaligus menyumbang reduksi emisi karbon 1.84 Ton CO₂ per rangkaian sebagai nilai jual Green Logistics."</em>
                        </p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="p-3.5 bg-gray-50 border-t border-gray-200 flex items-center justify-between shrink-0">
            <span class="text-[11px] text-gray-500 font-medium">
                Conclusion Supply Chain Consultant &copy; 2026 &bull; CIDP Yard Management System
            </span>
            <button onclick="closeEndToEndGuideModal()" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white font-bold text-xs rounded-xl transition shadow-xs">
                Tutup Panduan
            </button>
        </div>

    </div>
</div>

<script>
function openEndToEndGuideModal() {
    const m = document.getElementById('modalEndToEndGuide');
    if (m) m.classList.remove('hidden');
}

function closeEndToEndGuideModal() {
    const m = document.getElementById('modalEndToEndGuide');
    if (m) m.classList.add('hidden');
}

function switchGuideTab(tab) {
    const tabPipeline = document.getElementById('guideTabPipeline');
    const tabHardware = document.getElementById('guideTabHardware');
    const tabCheatsheet = document.getElementById('guideTabCheatsheet');
    
    const btnPipeline = document.getElementById('tabBtnPipeline');
    const btnHardware = document.getElementById('tabBtnHardware');
    const btnCheatsheet = document.getElementById('tabBtnCheatsheet');

    // Hide all
    if (tabPipeline) tabPipeline.classList.add('hidden');
    if (tabHardware) tabHardware.classList.add('hidden');
    if (tabCheatsheet) tabCheatsheet.classList.add('hidden');

    // Reset styles
    [btnPipeline, btnHardware, btnCheatsheet].forEach(b => {
        if (b) {
            b.classList.remove('border-[#0170b9]', 'bg-white', 'text-[#004b87]', 'shadow-xs');
            b.classList.add('border-transparent', 'text-gray-600');
        }
    });

    if (tab === 'pipeline') {
        if (tabPipeline) tabPipeline.classList.remove('hidden');
        if (btnPipeline) {
            btnPipeline.classList.add('border-[#0170b9]', 'bg-white', 'text-[#004b87]', 'shadow-xs');
            btnPipeline.classList.remove('border-transparent', 'text-gray-600');
        }
    } else if (tab === 'hardware') {
        if (tabHardware) tabHardware.classList.remove('hidden');
        if (btnHardware) {
            btnHardware.classList.add('border-[#0170b9]', 'bg-white', 'text-[#004b87]', 'shadow-xs');
            btnHardware.classList.remove('border-transparent', 'text-gray-600');
        }
    } else if (tab === 'cheatsheet') {
        if (tabCheatsheet) tabCheatsheet.classList.remove('hidden');
        if (btnCheatsheet) {
            btnCheatsheet.classList.add('border-[#0170b9]', 'bg-white', 'text-[#004b87]', 'shadow-xs');
            btnCheatsheet.classList.remove('border-transparent', 'text-gray-600');
        }
    }
}
</script>

<!-- SCRIPT UTAMA 3D ENGINE (THREE.JS + TWEEN.JS) -->
<script>
// =============================================================================
// GLOBAL SIMULATION STATE & THREE.JS OBJECTS
// =============================================================================
let scene, camera, renderer, controls;
let containerMeshes = {};       // Mapping container_id / number -> Three.js Mesh
let equipmentMeshes = {};       // Mapping equipment_id -> Three.js Mesh
let truckMesh = null;
let truckBoxMesh = null;        // Container on truck trailer chassis
let trainMesh = null;
let boomBarrierMesh = null;
let outboundBarrierMesh = null; // Outbound boom barrier
let floodlightLights = [];

let currentLighting = 'day';
let isAnimating = false;
let selectedContainerData = null;
let simSpeedMultiplier = 1.25;  // Default 1.25x (smooth & operational)

// Database state
let simState = {
    containers: [],
    equipment: [],
    trucks: [],
    trains: []
};

// =============================================================================
// LAYOUT MASTER BRIDGE  (sumber data: pages/layout_master.php, dipakai juga denah.php)
// Konvensi: E = timur (KANAN di denah), N = utara (ATAS di denah).
// Dunia 3D  : x = -E, z = N.  Alasan: kamera "Denah 2D" menatap dari selatan, dan
// pada Three.js sumbu +X tampil di KIRI layar, sehingga E harus dinegasikan.
// =============================================================================
const CIDP_LAYOUT = <?= json_encode(cidp_layout_for_js($CIDP_LAYOUT), JSON_UNESCAPED_UNICODE) ?>;

function LW(id) {
    const f = CIDP_LAYOUT.facilities[id];
    if (!f) { console.warn('[Layout Master] id tidak ditemukan:', id); return { x: 0, z: 0 }; }
    return { x: -f.e, z: f.n };
}

// Relokasi bangunan ke posisi layout master tanpa menulis ulang koordinat internal bangunan.
//   relocBegin(group)  -> tandai awal objek yang akan dipindah
//   relocEnd(m, id, ox, oz, mirror) -> pindahkan semua objek baru dari pusat lama (ox,oz) ke pusat master.
//   mirror=true memantulkan bangunan terhadap pusatnya (mis. dock CFS harus menghadap yard).
function relocBegin(group) {
    return { g: group || null, ng: group ? group.children.length : 0, ns: scene.children.length };
}
function flipCanvasText(o) {
    if (!o.isMesh || !o.material) return;
    const mats = Array.isArray(o.material) ? o.material : [o.material];
    mats.forEach((mt, i) => {
        if (mt.map && mt.map.isCanvasTexture) {
            const nm = mt.clone();
            const t = mt.map.clone();
            t.wrapS = THREE.RepeatWrapping; t.repeat.x = -1; t.offset.x = 1; t.needsUpdate = true;
            nm.map = t;
            if (Array.isArray(o.material)) o.material[i] = nm; else o.material = nm;
        }
    });
}
function relocEnd(m, id, ox, oz, mirror) {
    const p = LW(id);
    const dz = p.z - oz;
    const objs = [];
    if (m.g) for (let i = m.ng; i < m.g.children.length; i++) objs.push(m.g.children[i]);
    for (let i = m.ns; i < scene.children.length; i++) { if (scene.children[i] !== m.g) objs.push(scene.children[i]); }
    if (!objs.length) return null;
    if (mirror) {
        const mg = new THREE.Group();
        mg.name = 'Relocated_' + id;
        mg.position.set(p.x + ox, 0, dz);
        mg.scale.x = -1;
        objs.forEach(o => mg.add(o));
        mg.traverse(flipCanvasText);
        scene.add(mg);
        return mg;
    }
    const dx = p.x - ox;
    objs.forEach(o => { o.position.x += dx; o.position.z += dz; });
    return null;
}

// Layout coordinates mapping for terminal blocks (posisi dari layout master)
const BLOCK_COORDS = {
    'A':      { ...LW('blok_a'), width: 72, depth: 24, label: 'BLOK A (LADEN EXPORT - 4 TIERS)' },
    'B':      { ...LW('blok_b'), width: 72, depth: 24, label: 'BLOK B (LADEN IMPORT - 4 TIERS)' },
    'C':      { ...LW('blok_c'), width: 72, depth: 24, label: 'BLOK C (DOMESTIC CARGO - 4 TIERS)' },
    'D':      { ...LW('blok_d'), width: 72, depth: 24, label: 'BLOK D (BUFFER YARD - 4 TIERS)' },
    'DG':     { ...LW('blok_e'), width: 32, depth: 24, label: 'BLOK E (HAZMAT DG BUNDED 1.4M)' },
    'REEFER': { ...LW('f_reefer_racks'), width: 32, depth: 24, label: 'REEFER ZONE (300 POWER PLUGS)' },
    'EMPTY':  { ...LW('f_empty_depot'), width: 32, depth: 56, label: 'EMPTY DEPOT (2.500 TEU - 5 TIERS)' }
};

// Technical CAD overlay states
let showTechnicalDimensions = true;
let showCADTitleBlock = true;
let technicalDimensionsGroup = null;

// =============================================================================
// CAD / BIM & CIVIL INFRASTRUCTURE ENGINE STATE (TAHAP 1 - 4)
// =============================================================================
let cadGridGroup = null;
let pavementGroup = null;
let drainageGroup = null;
let animatedHazardLights = []; // Lampu strobo putar alat berat: { mesh, light, speed, phase }
let concreteTextureCache = null;

// Layer Filter Kontrol CAD/BIM
const cadLayers = {
    pavement: true,
    drainage: true,
    structures: true,
    dimensions: true,
    gridAxes: true,
    heavyEquipment: true
};

// Generator Tekstur Prosedural Rigid Pavement K-450 dengan Sambungan Susut (Joints)
// Satu tile = 1 modul pelat beton. Tanpa argumen -> repeat default seluruh tapak 35 Ha.
// Dengan argumen (repX, repY) -> klon tekstur (berbagi canvas) untuk apron berukuran khusus.
function getRigidPavementTexture(repX, repY) {
    if (!concreteTextureCache) {
        const canvas = document.createElement('canvas');
        canvas.width = 512;
        canvas.height = 512;
        const ctx = canvas.getContext('2d');

        // Base concrete clean architectural light tone (seperti referensi WareTrack Digital Twin)
        ctx.fillStyle = '#e2e8f0';
        ctx.fillRect(0, 0, 512, 512);

        // Micro aggregate & wear noise (efek agregat halus bersih bermutu tinggi K-500)
        for (let i = 0; i < 3500; i++) {
            const x = Math.random() * 512;
            const y = Math.random() * 512;
            const c = Math.random() > 0.5 ? 200 : 235;
            ctx.fillStyle = `rgba(${c}, ${c + 4}, ${c + 10}, 0.22)`;
            ctx.fillRect(x, y, 2, 2);
        }

        // Clean subtle tire tracks & pavement tone variations
        for (let i = 0; i < 5; i++) {
            const gx = Math.random() * 512, gy = Math.random() * 512;
            const grad = ctx.createRadialGradient(gx, gy, 4, gx, gy, 70 + Math.random() * 50);
            grad.addColorStop(0, 'rgba(148,163,184,0.12)');
            grad.addColorStop(1, 'rgba(148,163,184,0)');
            ctx.fillStyle = grad;
            ctx.fillRect(0, 0, 512, 512);
        }

        // Siar Dilatasi / Contraction Joint (Modul Pelat Beton 5x5 m - garis bersih presisi)
        ctx.strokeStyle = '#cbd5e1';
        ctx.lineWidth = 3.5;
        ctx.strokeRect(0, 0, 512, 512);

        // Sealant joint lines
        ctx.strokeStyle = '#94a3b8';
        ctx.lineWidth = 1.2;
        ctx.strokeRect(2, 2, 508, 508);

        // Dowel bar tick marks pada tengah sambungan
        ctx.fillStyle = 'rgba(100,116,139,0.35)';
        for (let t = 64; t < 512; t += 128) {
            ctx.fillRect(t, 0, 5, 2.5);
            ctx.fillRect(0, t, 2.5, 5);
        }

        const texture = new THREE.CanvasTexture(canvas);
        texture.wrapS = THREE.RepeatWrapping;
        texture.wrapT = THREE.RepeatWrapping;
        texture.repeat.set(38, 26);
        if (renderer && renderer.capabilities) {
            texture.anisotropy = Math.min(8, renderer.capabilities.getMaxAnisotropy());
        }
        concreteTextureCache = texture;
    }

    if (repX === undefined || repY === undefined) return concreteTextureCache;

    const clone = concreteTextureCache.clone();
    clone.repeat.set(repX, repY);
    clone.needsUpdate = true;
    return clone;
}

// Terapkan filter layer CAD/BIM (dipakai tombol/tahap selanjutnya)
function setCadLayer(layerName, visible) {
    if (!(layerName in cadLayers)) return;
    cadLayers[layerName] = !!visible;
    const map = {
        pavement: pavementGroup,
        drainage: drainageGroup,
        dimensions: technicalDimensionsGroup,
        gridAxes: cadGridGroup
    };
    if (map[layerName]) map[layerName].visible = cadLayers[layerName];
    if (layerName === 'dimensions') showTechnicalDimensions = cadLayers[layerName];
}

// Registrasi lampu strobo putar alat berat (dipanggil dari builder alat/gedung)
function registerHazardLight(mesh, light, speed, pulse) {
    animatedHazardLights.push({ mesh: mesh, light: light || null, speed: speed || 6, phase: Math.random() * Math.PI * 2, pulse: !!pulse });
}

// Dipanggil tiap frame dari animate(time)
function updateHazardLights(time) {
    time = time || 0;
    const t = time * 0.001;
    const pulse = (Math.sin(time * 0.012) + 1) / 2; // strobo K3 ~2 Hz
    for (let i = 0; i < animatedHazardLights.length; i++) {
        const h = animatedHazardLights[i];
        if (h.pulse) {
            // Strobo amber berkedip: ubah warna material (RGB amber -> redup)
            if (h.mesh && h.mesh.material) h.mesh.material.color.setRGB(1.0, 0.6 * pulse + 0.2, 0);
        } else if (h.mesh) {
            h.mesh.rotation.y = t * h.speed + h.phase;
        }
        if (h.light) h.light.intensity = (Math.sin(t * h.speed * 1.5 + h.phase) > 0) ? 1.6 : 0.1;
    }
}

// Shipping Line Color Palette
const SHIPPING_COLORS = {
    'Maersk':     0x0090c0, // Cyan Blue
    'Evergreen':  0x007a3d, // Emerald Green
    'MSC':        0xd49b00, // Mustard Yellow
    'CMA CGM':    0x0b2341, // Deep Navy
    'ONE':        0xcc0066, // Magenta
    'Meratus':    0x881337, // Maroon
    'Samudera':   0x0284c7, // Sky Blue
    'reefer':     0xf8fafc, // Pure White
    'dg':         0xdc2626, // Hazard Red
    'empty':      0x64748b  // Slate Grey
};

// =============================================================================
// INITIALIZATION
// =============================================================================
document.addEventListener('DOMContentLoaded', function() {
    initThreeScene();
    buildTerminalEnvironment();
    fetchSimulationState();

    // Clock ticker
    setInterval(updateLiveClock, 1000);
    window.addEventListener('resize', onWindowResize);
});

// Setup Scene, Camera, Lights, and Renderer
function initThreeScene() {
    const container = document.getElementById('webglCanvas');
    const width = container.clientWidth;
    const height = container.clientHeight;

    // Scene (Bright Clean Digital Twin Atmosphere seperti referensi WareTrack)
    scene = new THREE.Scene();
    scene.background = new THREE.Color(0xecf3f9);
    scene.fog = new THREE.FogExp2(0xecf3f9, 0.0022);

    // Camera (Menghadap ke Utara / North ke arah Kantor & Gate dari koridor Rel Selatan)
    camera = new THREE.PerspectiveCamera(45, width / height, 1, 1200);
    camera.position.set(0, 110, -155);

    // Renderer (ACESFilmic Tone Mapping + sRGB Color Space untuk render bersih berkualitas tinggi)
    renderer = new THREE.WebGLRenderer({ antialias: true, powerPreference: 'high-performance' });
    renderer.setSize(width, height);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.08;
    renderer.outputEncoding = THREE.sRGBEncoding;
    container.appendChild(renderer.domElement);

    // Controls
    controls = new THREE.OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.05;
    controls.maxPolarAngle = Math.PI / 2 - 0.05; // Do not go below ground
    controls.minDistance = 15;
    controls.maxDistance = 380;
    controls.target.set(0, 0, 5);

    // Zoom Indicator on OrbitControls Changes
    controls.addEventListener('change', updateSimZoomDisplay);
    updateSimZoomDisplay();

    // Viewport Dynamic ResizeObserver
    if (window.ResizeObserver) {
        const vpContainer = document.getElementById('simulatorViewportContainer');
        if (vpContainer) {
            const ro = new ResizeObserver(() => onWindowResize());
            ro.observe(vpContainer);
        }
    }

    // Setup Lighting
    setupLighting();

    // Raycasting for interactive clicks
    setupRaycasting(container);

    // Animation loop
    animate();
}

// Lighting Setup (Soft Hemisphere Fill + Natural Warm Sunlight)
let ambientLight, sunLight;
const LIGHT_DAY = { ambient: 0.85, sun: 1.35, sunColor: 0xfffaf0 };
function setupLighting() {
    ambientLight = new THREE.HemisphereLight(0xffffff, 0xcbd5e1, LIGHT_DAY.ambient);
    scene.add(ambientLight);

    // Directional Sun Light (Matahari alami dengan bayangan kontak halus presisi)
    sunLight = new THREE.DirectionalLight(LIGHT_DAY.sunColor, LIGHT_DAY.sun);
    sunLight.position.set(75, 120, 60);
    sunLight.castShadow = true;

    // Contact soft shadow parameters
    sunLight.shadow.mapSize.width = 2048;
    sunLight.shadow.mapSize.height = 2048;
    sunLight.shadow.camera.near = 10;
    sunLight.shadow.camera.far = 350;
    sunLight.shadow.camera.left = -140;
    sunLight.shadow.camera.right = 140;
    sunLight.shadow.camera.top = 140;
    sunLight.shadow.camera.bottom = -140;
    sunLight.shadow.bias = -0.0003;
    scene.add(sunLight);
}

// =============================================================================
// BUILD TERMINAL ENVIRONMENT (GROUND, ZONES, GANTRY, TRACKS, BUILDINGS)
// =============================================================================
// =============================================================================
// BUILD TERMINAL ENVIRONMENT (GROUND, ZONES, GANTRY, TRACKS, BUILDINGS)
// 1:1 Civil & Industrial Logistics Engineering Master Plan (35 Hektar)
// =============================================================================
function buildTerminalEnvironment() {
    // 1. Civil Ground Floor & Arterial Road Network (35 Ha Yard)
    buildCivilPavementsAndRoadNetwork();

    // 2. Build Yard Stacking Blocks Markings, Concrete Slabs & Bunded DG Yard
    for (const [key, bData] of Object.entries(BLOCK_COORDS)) {
        createBlockZone(key, bData.x, bData.z, bData.label);
    }

    // 3. Rail Siding Intermodal (dipantulkan: stasiun dispatcher di BARAT, sesuai denah)
    const __mRail = relocBegin(null);
    buildRailSiding();
    relocEnd(__mRail, 'rail_siding', 0, -48, true);

    // 4. Gate Complex (Gate-In 2 Lanes, Weighbridge 80T, OCR, ANPR, Barrier, Security, Rest Area)
    buildGateComplex();

    // 5. Administration, Datacenter NOC, Masjid, Canteen, Clinic & Damkar
    buildAdministrationAndPublicZone();

    // 6. CFS Warehouse 4.000m², Transit Warehouse & M&R Heavy Equipment Workshop
    buildCFSAndWarehouseZone();

    // 7. Customs KPPBC, 6 MeV Gantry X-Ray, Red Line Behandle Canopy & Quarantine
    buildCustomsAndBehandleZone();

    // 8. Perimeter Boundary Security Fence & 4 Watchtowers
    buildPerimeterAndSecurity();

    // 9. Engineering CAD 3D Dimension Leader Lines & Benchmark Datum
    buildEngineeringDimensionLeaders();

    // 10. Yard Floodlight Towers (for 24/7 night lighting)
    buildFloodlightTowers();

    // 11. Spawn 3D Heavy Equipment Models (RS-01, RS-02, RS-03, RTG-01)
    spawnEquipmentModels();

    // 12. Spawn Prime Mover Truck on Gate Road
    spawnTruckModel();

    // 13. Create Holographic 3D Target Marker Beacon & Ground Ring
    createTargetMarker();

    // 14. Generate Baseline Yard Container Stacks (Multi-tier realistic port scale)
    generateBaselineYardContainers();

    // 15. Forklift Charging Hub & Active Electric Fleet (WareTrack Digital Twin)
    const __mFork = relocBegin(null);
    buildForkliftHubAndChargingStation();
    relocEnd(__mFork, 'f_cfs', -88, 14, true);   // ikut CFS

    // 16. Truk Kontainer Sasis 40ft (CFS Stuffing / Stripping Dock Bay 03)
    const __mCfsTruck = relocBegin(null);
    buildCFSContainerTruck();
    relocEnd(__mCfsTruck, 'f_cfs', -88, 14, true);   // ikut CFS

    // 17. Modern Stylized Low-Poly Trees & Green Belts
    buildStylizedTrees();
}

// 1. Civil Concrete Ground Pavements & Arterial Road Network (Expansive 35 Ha Dry Port Master Plan)
function buildCivilPavementsAndRoadNetwork() {
    // Layer groups (CAD/BIM layer filter)
    pavementGroup = new THREE.Group();
    pavementGroup.name = "RigidPavementLayer";
    scene.add(pavementGroup);
    drainageGroup = new THREE.Group();
    drainageGroup.name = "DrainageLayer";
    scene.add(drainageGroup);
    cadGridGroup = new THREE.Group();
    cadGridGroup.name = "CadGridAxesLayer";
    scene.add(cadGridGroup);

    // Base 35 Hektar Ground Floor: Rigid Pavement K-450 (tile 10 unit ~ pelat 5x5 m)
    const groundGeo = new THREE.PlaneGeometry(380, 260);
    const groundMat = new THREE.MeshStandardMaterial({
        map: getRigidPavementTexture(),
        color: 0xffffff,
        roughness: 0.9,
        metalness: 0.05
    });
    const ground = new THREE.Mesh(groundGeo, groundMat);
    ground.rotation.x = -Math.PI / 2;
    ground.position.y = 0;
    ground.receiveShadow = true;
    pavementGroup.add(ground);

    // CAD axis grid (grid sumbu 5 unit) - layer 'gridAxes'
    // CAD axis grid (grid sumbu 5 unit) - layer 'gridAxes'
    const grid = new THREE.GridHelper(380, 76, 0x94a3b8, 0xcbd5e1);
    grid.position.y = 0.03;
    grid.material.transparent = true;
    grid.material.opacity = 0.35;
    cadGridGroup.add(grid);

    // Concrete Apron Platforms for Each Zone (Scale-Accurate Clean Architectural Slabs)
    const aprons = [
        // Expansive Container Stacking Yard Apron (Core Terminal Blocks A-E + Empty Depot)
        { w: 260, d: 120, x: 0, z: -5, color: 0xe2e8f0 },
        // CFS / M&R / Transit Warehouse Apron (kolom BARAT, sesuai denah)
        { w: 46, d: 66, x: LW('f_cfs').x, z: -3, color: 0xedf2f7 },
        // Customs KPPBC, X-Ray & Behandle Apron (kolom TIMUR, sesuai denah)
        { w: 38, d: 86, x: LW('f_kppbc').x, z: 6, color: 0xedf2f7 },
        // Administration HQ, Datacenter NOC (UTARA-TENGAH)
        { w: 88, d: 38, x: -((LW('f_datacenter').x + LW('f_parking_staff').x) / 2), z: 52, color: 0xf1f5f9 },
        // Fasum: Masjid, Kantin, Klinik, Damkar, Genset (BARAT LAUT)
        { w: 66, d: 40, x: LW('f_kantin').x + 20, z: 46, color: 0xf1f5f9 },
        // Gate Complex & Truck Queuing Approach Apron (TIMUR LAUT)
        { w: 76, d: 38, x: -58, z: 52, color: 0xe2e8f0 },
        // Reefer Cold Chain Specialized Apron
        { w: 38, d: 36, x: LW('f_reefer_racks').x, z: 16, color: 0xe0f2fe },
        // Rail Siding Intermodal Loading Apron (South Perimeter)
        { w: 280, d: 24, x: 0, z: -52, color: 0xdbeafe }
    ];

    aprons.forEach(ap => {
        const apGeo = new THREE.BoxGeometry(ap.w, 0.12, ap.d);
        const apMat = new THREE.MeshStandardMaterial({
            map: getRigidPavementTexture(ap.w / 10, ap.d / 10),
            color: ap.color,
            roughness: 0.75
        });
        const mesh = new THREE.Mesh(apGeo, apMat);
        mesh.position.set(ap.x, 0.06, ap.z);
        mesh.receiveShadow = true;
        pavementGroup.add(mesh);
    });

    // Sistem Drainase Tepi Jalan (Precast U-Ditch dengan Steel Grating)
    buildUDitchDrainageNetwork();

    // Marka Jalan Garis Kuning Reflektif (Thermoplastic Paint) - geometri & material dipakai bersama
    const dashMat = new THREE.MeshStandardMaterial({ color: 0xf59e0b, roughness: 0.4 });
    const dashGeoH = new THREE.BoxGeometry(3.5, 0.02, 0.35);
    const dashGeoV = new THREE.BoxGeometry(0.35, 0.02, 3.5);

    const roadMarkings = [
        // North Cross Road (Connecting Gate to Admin, Transfer Lanes, Customs)
        { sx: -130, ex: 130, z: 28, orient: 'h' },
        // Central Yard Spine Road (Between Blok A/B and Blok C/D)
        { sx: -84, ex: 84, z: -5, orient: 'h' },
        // South Haul Road (Alongside Rail Siding Loading Ramp)
        { sx: -130, ex: 130, z: -39, orient: 'h' },
        // Gate Inbound Corridor (Lane 1)
        { sz: 75, ez: 28, x: -49, orient: 'v' },
        // Gate Outbound Corridor (Lane 2)
        { sz: 75, ez: 28, x: -41, orient: 'v' },
        // Central Transfer Boulevard (Between Blok A and Blok B - 16m Wide)
        { sz: 28, ez: -39, x: 0, orient: 'v' },
        // East Corridor (Connecting Reefer & DG to Customs)
        { sz: 28, ez: -39, x: -84, orient: 'v' },   // koridor Timur (Reefer/DG)
        // West Corridor (Connecting Empty Depot to CFS)
        { sz: 28, ez: -39, x: 82, orient: 'v' }    // koridor Barat (Empty Depot)
    ];

    roadMarkings.forEach(rm => {
        if (rm.orient === 'h') {
            for (let rx = rm.sx; rx <= rm.ex; rx += 6) {
                const dash = new THREE.Mesh(dashGeoH, dashMat);
                dash.position.set(rx, 0.13, rm.z);
                pavementGroup.add(dash);
            }
        } else {
            for (let rz = rm.sz; rz >= rm.ez; rz -= 6) {
                const dash = new THREE.Mesh(dashGeoV, dashMat);
                dash.position.set(rm.x, 0.13, rz);
                pavementGroup.add(dash);
            }
        }
    });

    // Marka Garis Parkir Truk di Depan Loading Dock CFS (Dock Stalls Bay 01 - 04)
    const stallLineMat = new THREE.MeshBasicMaterial({ color: 0xfacc15 });
    const stallLineGeo = new THREE.BoxGeometry(11, 0.02, 0.22);
    const stopLineGeo = new THREE.BoxGeometry(0.22, 0.02, 3.6);

    const __cfsP = LW('f_cfs');
    const __cfsMapX = (x) => (__cfsP.x - 88) - x;
    const __cfsDz = __cfsP.z - 14;
    [7, 11.5, 16, 20.5].forEach(dz => {
        // Garis samping stall parkir mundur truk
        [-1.8, 1.8].forEach(offsetZ => {
            const line = new THREE.Mesh(stallLineGeo, stallLineMat);
            line.position.set(__cfsMapX(-68.5), 0.13, dz + offsetZ + __cfsDz);
            pavementGroup.add(line);
        });
        // Garis batas roda truk (stop line)
        const stopLine = new THREE.Mesh(stopLineGeo, stallLineMat);
        stopLine.position.set(__cfsMapX(-63.0), 0.13, dz + __cfsDz);
        pavementGroup.add(stopLine);
    });
}

// Saluran Parit Drainase U-Ditch Beton Pracetak di Sepanjang Koridor Jalan
// Diletakkan di celah antar blok (bukan di bawah tumpukan kontainer): z=31 (tepi utara Blok A/B) dan z=-6.8 (jalan tengah yard).
function buildUDitchDrainageNetwork() {
    if (!drainageGroup) {
        drainageGroup = new THREE.Group();
        scene.add(drainageGroup);
    }
    drainageGroup.name = "CivilDrainageUDitch";

    const ditchTroughs = [
        { x: 0, z: 31, w: 170, d: 0.8 },
        { x: 0, z: -6.8, w: 170, d: 0.8 }
    ];

    const concMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.9 });
    const grateMat = new THREE.MeshStandardMaterial({ color: 0x64748b, metalness: 0.8, roughness: 0.3 });
    const grateGeo = new THREE.BoxGeometry(1.8, 0.04, 0.7);

    ditchTroughs.forEach(dt => {
        // Badan Parit U-Ditch (puncak sedikit di atas apron 0.12 agar terbaca)
        const ditch = new THREE.Mesh(new THREE.BoxGeometry(dt.w, 0.3, dt.d), concMat);
        ditch.position.set(dt.x, 0.0, dt.z);
        ditch.receiveShadow = true;
        drainageGroup.add(ditch);

        // Penutup Kisi Besi Galvanis (Steel Grating) per 2 unit
        for (let gx = -dt.w / 2 + 1; gx <= dt.w / 2 - 1; gx += 2) {
            const grate = new THREE.Mesh(grateGeo, grateMat);
            grate.position.set(dt.x + gx, 0.16, dt.z);
            drainageGroup.add(grate);
        }
    });

    drainageGroup.visible = cadLayers.drainage;
}

// 2. Create Block Stacking Zone Area (Blok A-E, Reefer, Empty Depot)
function createBlockZone(blockKey, centerX, centerZ, labelText) {
    const bData = BLOCK_COORDS[blockKey] || { width: 72, depth: 24 };
    const blockWidth = bData.width || 72;
    const blockDepth = bData.depth || 24;

    // Heavy-Duty Reinforced Concrete Slab (Clean Architectural Concrete)
    let slabColor = 0xdbe4ee;
    if (blockKey === 'REEFER') slabColor = 0xcfe2f7;
    if (blockKey === 'DG') slabColor = 0xfce7f3;

    const slabGeo = new THREE.BoxGeometry(blockWidth, 0.2, blockDepth);
    const slabMat = new THREE.MeshStandardMaterial({
        color: slabColor,
        roughness: 0.7
    });
    const slab = new THREE.Mesh(slabGeo, slabMat);
    slab.position.set(centerX, 0.1, centerZ);
    slab.receiveShadow = true;
    scene.add(slab);

    // Yellow Boundary Safety Line
    const edgeGeo = new THREE.BoxGeometry(blockWidth + 0.6, 0.05, blockDepth + 0.6);
    const edgeMat = new THREE.MeshBasicMaterial({ color: blockKey === 'DG' ? 0xef4444 : 0xf59e0b });
    const edge = new THREE.Mesh(edgeGeo, edgeMat);
    edge.position.set(centerX, 0.21, centerZ);
    scene.add(edge);

    // Painted Yellow Slot Ground Markings & Bay/Row Identifiers
    createSlotGroundMarkings(blockKey, centerX, centerZ, blockWidth, blockDepth);

    // Visual Stacking Tier Gauge Indicator Posts (Corner Rulers with T1-T4 Marks)
    buildStackingTierIndicatorPost(centerX - blockWidth/2 - 0.8, centerZ - blockDepth/2, blockKey);
    buildStackingTierIndicatorPost(centerX + blockWidth/2 + 0.8, centerZ - blockDepth/2, blockKey);

    // If Hazmat DG: build 1.4m reinforced concrete bund wall around it!
    if (blockKey === 'DG') {
        buildHazmatBundWall(centerX, centerZ, blockWidth, blockDepth);
    }

    // Block Label Billboard Sprite
    createBlockLabelSprite(labelText, centerX, 8.5, centerZ - blockDepth/2 - 3.0);

    // Reefer Power Racks Structure
    if (blockKey === 'REEFER') {
        createReeferPowerRacks(centerX, centerZ, blockWidth, blockDepth);
    }
}

// Visual Stacking Tier Gauge Indicator Post (Shows Height for T1, T2, T3, T4 Stacks)
function buildStackingTierIndicatorPost(px, pz, blockKey) {
    const postGroup = new THREE.Group();
    
    // Steel Mast Column (12m high)
    const mastGeo = new THREE.CylinderGeometry(0.12, 0.15, 12.0, 8);
    const mastMat = new THREE.MeshStandardMaterial({ color: 0x334155, metalness: 0.8, roughness: 0.3 });
    const mast = new THREE.Mesh(mastGeo, mastMat);
    mast.position.y = 6.0;
    postGroup.add(mast);

    // Color-Coded Tier Rings & Badges (T1=1.5m, T2=4.1m, T3=6.7m, T4=9.3m)
    const tiers = [
        { label: 'T1', y: 1.5, color: 0x10b981 }, // Green (Ground)
        { label: 'T2', y: 4.1, color: 0x0284c7 }, // Blue
        { label: 'T3', y: 6.7, color: 0xf59e0b }, // Amber
        { label: 'T4', y: 9.3, color: 0xef4444 }  // Red (Max Safe Port Stack)
    ];

    tiers.forEach(t => {
        // Horizontal Ring
        const ringGeo = new THREE.TorusGeometry(0.35, 0.05, 8, 16);
        const ringMat = new THREE.MeshBasicMaterial({ color: t.color });
        const ring = new THREE.Mesh(ringGeo, ringMat);
        ring.rotation.x = Math.PI / 2;
        ring.position.y = t.y;
        postGroup.add(ring);

        // Small Tier Signboard Sprite
        const canvas = document.createElement('canvas');
        canvas.width = 128;
        canvas.height = 64;
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#0f172a';
        ctx.fillRect(0, 0, 128, 64);
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 4;
        ctx.strokeRect(2, 2, 124, 60);
        ctx.font = 'bold 36px monospace';
        ctx.fillStyle = '#ffffff';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(t.label, 64, 32);

        const tex = new THREE.CanvasTexture(canvas);
        const spriteMat = new THREE.SpriteMaterial({ map: tex });
        const sprite = new THREE.Sprite(spriteMat);
        sprite.position.set(0.65, t.y, 0);
        sprite.scale.set(1.4, 0.7, 1);
        postGroup.add(sprite);
    });

    postGroup.position.set(px, 0.2, pz);
    scene.add(postGroup);
}

// Painted Yellow L-Corner Ground Markings & Bay/Row Markings for Yard Slots
function createSlotGroundMarkings(blockKey, cx, cz, bw, bd) {
    const isMainBlock = (blockKey === 'A' || blockKey === 'B' || blockKey === 'C' || blockKey === 'D');
    const bays = isMainBlock ? 5 : (blockKey === 'EMPTY' ? 2 : 2);
    const rows = (blockKey === 'EMPTY') ? 16 : 7;

    const markMat = new THREE.MeshBasicMaterial({ color: blockKey === 'DG' ? 0xef4444 : 0xfbbf24 });

    for (let b = 1; b <= bays; b++) {
        let sx = cx;
        if (isMainBlock) {
            sx = (cx + 27.0) - (b - 1) * 13.5;   // Bay 01 di sisi barat (kiri denah)
        } else {
            sx = (cx + 6.75) - (b - 1) * 13.5;
        }

        // Painted Bay ID Mark on the front of each bay (e.g. BAY 01, BAY 03, etc.)
        if (b <= bays) {
            const bayCanvas = document.createElement('canvas');
            bayCanvas.width = 128;
            bayCanvas.height = 64;
            const bctx = bayCanvas.getContext('2d');
            bctx.font = 'bold 38px monospace';
            bctx.fillStyle = blockKey === 'DG' ? '#ef4444' : '#f59e0b';
            bctx.textAlign = 'center';
            bctx.textBaseline = 'middle';
            bctx.fillText(`B${String(b*2-1).padStart(2, '0')}`, 64, 32);

            const bTex = new THREE.CanvasTexture(bayCanvas);
            const bSprMat = new THREE.SpriteMaterial({ map: bTex, transparent: true });
            const bSpr = new THREE.Sprite(bSprMat);
            bSpr.position.set(sx, 0.25, cz + bd/2 - 1.2);
            bSpr.scale.set(2.4, 1.2, 1);
            scene.add(bSpr);
        }

        for (let r = 1; r <= rows; r++) {
            const sz = (cz - ((rows - 1) * 3.0) / 2) + (r - 1) * 3.0;

            // Draw 4 corner marks for 40ft container footprint (12.0m x 2.44m)
            const cL = 12.0, cW = 2.44;
            const offsets = [
                { x: sx - cL/2, z: sz - cW/2, dx: 0.9, dz: 0.9 },
                { x: sx + cL/2, z: sz - cW/2, dx: -0.9, dz: 0.9 },
                { x: sx - cL/2, z: sz + cW/2, dx: 0.9, dz: -0.9 },
                { x: sx + cL/2, z: sz + cW/2, dx: -0.9, dz: -0.9 }
            ];

            offsets.forEach(co => {
                // Horizontal leg of L
                const hGeo = new THREE.BoxGeometry(0.9, 0.02, 0.15);
                const hMesh = new THREE.Mesh(hGeo, markMat);
                hMesh.position.set(co.x + co.dx/2, 0.22, co.z);
                scene.add(hMesh);

                // Vertical leg of L
                const vGeo = new THREE.BoxGeometry(0.15, 0.02, 0.9);
                const vMesh = new THREE.Mesh(vGeo, markMat);
                vMesh.position.set(co.x, 0.22, co.z + co.dz/2);
                scene.add(vMesh);
            });
        }
    }
}

// Hazmat / DG Bunded Containment Wall (1.2m Reinforced Concrete)
function buildHazmatBundWall(cx, cz, bw, bd) {
    const wallHeight = 1.4;
    const wallThick = 0.5;
    const wallMat = new THREE.MeshStandardMaterial({ color: 0x991b1b, roughness: 0.5 }); // Hazmat Red/Concrete
    const capMat = new THREE.MeshBasicMaterial({ color: 0xfacc15 }); // Yellow safety cap

    // 4 Walls: North, South, East, West with containment sump opening
    const wallDefs = [
        { w: bw + 0.8, h: wallHeight, d: wallThick, x: cx, z: cz + bd/2 + 0.3 },
        { w: bw + 0.8, h: wallHeight, d: wallThick, x: cx, z: cz - bd/2 - 0.3 },
        { w: wallThick, h: wallHeight, d: bd + 0.8, x: cx + bw/2 + 0.3, z: cz },
        { w: wallThick, h: wallHeight, d: bd + 0.8, x: cx - bw/2 - 0.3, z: cz }
    ];

    wallDefs.forEach(wd => {
        const geo = new THREE.BoxGeometry(wd.w, wd.h, wd.d);
        const mesh = new THREE.Mesh(geo, wallMat);
        mesh.position.set(wd.x, wd.h / 2 + 0.1, wd.z);
        mesh.castShadow = true;
        scene.add(mesh);

        const capGeo = new THREE.BoxGeometry(wd.w, 0.1, wd.d);
        const cap = new THREE.Mesh(capGeo, capMat);
        cap.position.set(wd.x, wd.h + 0.15, wd.z);
        scene.add(cap);
    });

    createBlockLabelSprite('HAZMAT DG BUNDED YARD (1.2M BUND WALL & SPILL SUMP)', cx, 7.8, cz - bd/2 - 3);
}

// Block Label Sprite in 3D Space
function createBlockLabelSprite(text, x, y, z, parent) {
    const canvas = document.createElement('canvas');
    canvas.width = 512;
    canvas.height = 128;
    const ctx = canvas.getContext('2d');
    
    ctx.fillStyle = 'rgba(15, 23, 42, 0.88)';
    ctx.roundRect(10, 10, 492, 108, 16);
    ctx.fill();
    ctx.lineWidth = 3.5;
    ctx.strokeStyle = '#0170b9';
    ctx.roundRect(10, 10, 492, 108, 16);
    ctx.stroke();

    ctx.font = 'bold 30px Plus Jakarta Sans, sans-serif';
    ctx.fillStyle = '#ffffff';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(text, 256, 64);

    const texture = new THREE.CanvasTexture(canvas);
    const spriteMat = new THREE.SpriteMaterial({ map: texture, transparent: true });
    const sprite = new THREE.Sprite(spriteMat);
    sprite.position.set(x, y, z);
    sprite.scale.set(16, 4, 1);
    (parent || scene).add(sprite);
    return sprite;
}

// Reefer Power Racks Structure (Cold Chain 300 Plugs)
function createReeferPowerRacks(bx, bz, bw, bd) {
    const rackGeo = new THREE.BoxGeometry(bw - 2, 3.5, 0.4);
    const rackMat = new THREE.MeshStandardMaterial({ color: 0x94a3b8, metalness: 0.6, roughness: 0.3 });
    const rack = new THREE.Mesh(rackGeo, rackMat);
    rack.position.set(bx, 1.8, bz + bd/2 - 0.4);
    scene.add(rack);

    // Blue LED sockets
    for (let i = -bw/2 + 2; i <= bw/2 - 2; i += 2.5) {
        const ledGeo = new THREE.SphereGeometry(0.18, 8, 8);
        const ledMat = new THREE.MeshBasicMaterial({ color: 0x38bdf8 });
        const led = new THREE.Mesh(ledGeo, ledMat);
        led.position.set(bx + i, 2.5, bz + bd/2 - 0.15);
        scene.add(led);
    }
}

// 3. Intermodal Rail Siding (400m Dual Tracks + Loading Ramp + Train + Dispatcher)
function buildRailSiding() {
    const trackZ = -48;
    const railLength = 210;

    // Balast Batu Pecah Bersudut (Crushed Rock Ballast Bed)
    const ballastGeo = new THREE.BoxGeometry(railLength, 0.4, 16);
    const ballastMat = new THREE.MeshStandardMaterial({ color: 0x334155, roughness: 0.98 });
    const ballast = new THREE.Mesh(ballastGeo, ballastMat);
    ballast.position.set(0, 0.2, trackZ);
    ballast.receiveShadow = true;
    scene.add(ballast);

    // Rel Baja R54 (Track 1 & Track 2)
    const railSteelMat = new THREE.MeshStandardMaterial({ color: 0xf8fafc, metalness: 0.95, roughness: 0.15 });
    const railOffsets = [-4.5, -2.5, 2.5, 4.5];

    // Bantalan Beton Pratekan (Prestressed Concrete Sleepers) + Klip Pandrol Elastis
    // Pakai InstancedMesh: ratusan bantalan/klip hanya 2 draw call.
    const sleeperXs = [];
    for (let x = -105; x <= 105; x += 2.0) sleeperXs.push(x);

    const sleeperMesh = new THREE.InstancedMesh(
        new THREE.BoxGeometry(0.42, 0.2, 13.2),
        new THREE.MeshStandardMaterial({ color: 0xcbd5e1, roughness: 0.8 }),
        sleeperXs.length
    );
    const clipMesh = new THREE.InstancedMesh(
        new THREE.BoxGeometry(0.12, 0.08, 0.1),
        new THREE.MeshStandardMaterial({ color: 0xd97706, metalness: 0.8, roughness: 0.4 }), // Pandrol Clip
        sleeperXs.length * railOffsets.length * 2
    );
    const dummy = new THREE.Object3D();
    let clipIdx = 0;
    sleeperXs.forEach((x, i) => {
        dummy.position.set(x, 0.45, trackZ);
        dummy.updateMatrix();
        sleeperMesh.setMatrixAt(i, dummy.matrix);

        // Klip pengencang di kedua sisi tiap rel
        railOffsets.forEach(rz => {
            [-0.14, 0.14].forEach(side => {
                dummy.position.set(x, 0.59, trackZ + rz + side);
                dummy.updateMatrix();
                clipMesh.setMatrixAt(clipIdx++, dummy.matrix);
            });
        });
    });
    sleeperMesh.instanceMatrix.needsUpdate = true;
    clipMesh.instanceMatrix.needsUpdate = true;
    sleeperMesh.receiveShadow = true;
    sleeperMesh.frustumCulled = false; // bounding sphere instance default berada di origin
    clipMesh.frustumCulled = false;
    scene.add(sleeperMesh);
    scene.add(clipMesh);

    railOffsets.forEach(offsetZ => {
        const railGeo = new THREE.BoxGeometry(railLength, 0.3, 0.15);
        const rail = new THREE.Mesh(railGeo, railSteelMat);
        rail.position.set(0, 0.65, trackZ + offsetZ);
        scene.add(rail);
    });

    // Concrete Loading Ramp (fac_f_loading_ramp)
    const rampGeo = new THREE.BoxGeometry(160, 1.2, 5.0);
    const rampMat = new THREE.MeshStandardMaterial({ color: 0x64748b, roughness: 0.8 });
    const ramp = new THREE.Mesh(rampGeo, rampMat);
    ramp.position.set(20, 0.6, trackZ + 9);
    ramp.receiveShadow = true;
    scene.add(ramp);

    // Rail Office / Stasiun Dispatcher (fac_f_rail_office)
    const rOffGeo = new THREE.BoxGeometry(18, 6.5, 10);
    const rOffMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.5 });
    const rOff = new THREE.Mesh(rOffGeo, rOffMat);
    rOff.position.set(-90, 3.25, trackZ + 7);
    rOff.castShadow = true;
    scene.add(rOff);

    // Observation Tower Cabin on Rail Office
    const towGeo = new THREE.BoxGeometry(8, 3.0, 7);
    const towMat = new THREE.MeshStandardMaterial({ color: 0x38bdf8, transparent: true, opacity: 0.8 });
    const tow = new THREE.Mesh(towGeo, towMat);
    tow.position.set(-90, 7.5, trackZ + 7);
    scene.add(tow);

    // Railway Signal Light Post
    const sigPole = new THREE.Mesh(new THREE.CylinderGeometry(0.12, 0.12, 7, 8), new THREE.MeshStandardMaterial({ color: 0x334155 }));
    sigPole.position.set(-78, 3.5, trackZ + 3);
    scene.add(sigPole);

    const greenSignal = new THREE.Mesh(new THREE.SphereGeometry(0.3, 8, 8), new THREE.MeshBasicMaterial({ color: 0x22c55e }));
    greenSignal.position.set(-78, 6.5, trackZ + 3);
    scene.add(greenSignal);

    // Spawn Freight Train on Track 1 (Z = trackZ + 3.5 = -44.5)
    buildFreightTrain(trackZ + 3.5);

    // Rail Siding Signboard
    createBlockLabelSprite('RAIL SIDING KAI LOGISTIK (R54, BANTALAN BETON PRATEKAN)', 0, 8.5, trackZ + 12);
}

// Freight Train Model (PT Kereta Api Logistik CC 206)
function buildFreightTrain(zPos) {
    trainMesh = new THREE.Group();

    // Diesel Locomotive (PT KAI Logistik Orange / White / Grey)
    const locoGeo = new THREE.BoxGeometry(16, 4.5, 3.2);
    const locoMat = new THREE.MeshStandardMaterial({ color: 0xe05615, roughness: 0.4 });
    const loco = new THREE.Mesh(locoGeo, locoMat);
    loco.position.set(-60, 3.0, 0);
    loco.castShadow = true;
    trainMesh.add(loco);

    // Locomotive Cabin Glass
    const cabGeo = new THREE.BoxGeometry(3, 1.6, 3.3);
    const cabMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.1 });
    const cab = new THREE.Mesh(cabGeo, cabMat);
    cab.position.set(-64, 3.8, 0);
    trainMesh.add(cab);

    // 4 Flatcars with Containers
    window.trainContainerBoxes = [];
    const containerColors = [0x0090c0, 0x007a3d, 0xd49b00, 0x0b2341];
    for (let i = 0; i < 4; i++) {
        const posX = -36 + (i * 18);
        
        // Flatcar Chassis
        const flatGeo = new THREE.BoxGeometry(14, 0.8, 3.2);
        const flatMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, metalness: 0.5 });
        const flat = new THREE.Mesh(flatGeo, flatMat);
        flat.position.set(posX, 1.2, 0);
        flat.castShadow = true;
        trainMesh.add(flat);

        // Container on Flatcar
        const cGeo = new THREE.BoxGeometry(12, 2.8, 3.0);
        const cMat = new THREE.MeshStandardMaterial({ color: containerColors[i], roughness: 0.5 });
        const cBox = new THREE.Mesh(cGeo, cMat);
        cBox.position.set(posX, 3.0, 0);
        cBox.castShadow = true;
        trainMesh.add(cBox);
        window.trainContainerBoxes.push(cBox);
    }

    trainMesh.position.set(0, 0, zPos);
    scene.add(trainMesh);
}

// 4. Gate Complex (Gate-In, Weighbridge 80T, OCR, ANPR, Barrier, Security, Rest Area)
function buildGateComplex() {
    const gateZ = 52;
    const laneInX = -49;   // Lane 1 Inbound
    const laneOutX = -41;  // Lane 2 Outbound
    const gateCenterX = (laneInX + laneOutX) / 2; // -45

    // Inbound & Outbound Gate Portal Gantry (Steel Overhead Structure)
    const gantryMat = new THREE.MeshStandardMaterial({ color: 0x004b87, metalness: 0.6, roughness: 0.3 });
    
    // Gantry Columns (Span across both Lane 1 & Lane 2)
    const colGeo = new THREE.BoxGeometry(1.2, 8.0, 1.2);
    const colLeft = new THREE.Mesh(colGeo, gantryMat);
    colLeft.position.set(laneInX - 4.5, 4.0, gateZ);
    scene.add(colLeft);

    const colMid = new THREE.Mesh(colGeo, gantryMat);
    colMid.position.set(gateCenterX, 4.0, gateZ);
    scene.add(colMid);

    const colRight = new THREE.Mesh(colGeo, gantryMat);
    colRight.position.set(laneOutX + 4.5, 4.0, gateZ);
    scene.add(colRight);

    // Overhead Portal Crossbeam
    const beam = new THREE.Mesh(new THREE.BoxGeometry(20.0, 1.6, 1.6), gantryMat);
    beam.position.set(gateCenterX, 8.0, gateZ);
    scene.add(beam);

    // Signboard on Gantry
    createBlockLabelSprite('MAIN GATE & JEMBATAN TIMBANG SOLAS 80T (LANE 1 IN • LANE 2 OUT)', gateCenterX, 10.2, gateZ);

    // Weighbridge Pit & Real-World 18m SOLAS Steel Scale Platform (Lane 1, Inbound)
    // Real-world scale: Length 18.0m (along traffic Z), Width 3.8m (across lane X)
    const scaleGeo = new THREE.BoxGeometry(3.8, 0.15, 18.0);
    const scaleMat = new THREE.MeshStandardMaterial({ color: 0x334155, metalness: 0.8, roughness: 0.25 });
    const scale = new THREE.Mesh(scaleGeo, scaleMat);
    scale.position.set(laneInX, 0.08, gateZ);
    scale.receiveShadow = true;
    scene.add(scale);

    // Weighbridge Yellow/Black Hazard Safety Curbs (Left & Right)
    const curbGeo = new THREE.BoxGeometry(0.3, 0.35, 18.0);
    const curbMat = new THREE.MeshStandardMaterial({ color: 0xf59e0b, roughness: 0.5 });
    const curbL = new THREE.Mesh(curbGeo, curbMat);
    curbL.position.set(laneInX - 2.0, 0.18, gateZ);
    scene.add(curbL);

    const curbR = new THREE.Mesh(curbGeo, curbMat);
    curbR.position.set(laneInX + 2.0, 0.18, gateZ);
    scene.add(curbR);

    // Digital Weighbridge Indicator Display on Gantry (Lane 1)
    const ledDispGeo = new THREE.BoxGeometry(2.8, 1.2, 0.4);
    const ledDispMat = new THREE.MeshBasicMaterial({ color: 0x10b981 });
    const ledDisp = new THREE.Mesh(ledDispGeo, ledDispMat);
    ledDisp.position.set(laneInX, 6.2, gateZ);
    scene.add(ledDisp);

    // Inbound Boom Barrier Pole (Lane 1, Animated Palang Pintu Otomatis)
    const barrierBaseGeo = new THREE.CylinderGeometry(0.35, 0.4, 1.4, 16);
    const barrierBaseMat = new THREE.MeshStandardMaterial({ color: 0xf59e0b });
    const bBaseIn = new THREE.Mesh(barrierBaseGeo, barrierBaseMat);
    bBaseIn.position.set(laneInX - 2.2, 0.7, 43);
    scene.add(bBaseIn);

    const poleGeo = new THREE.CylinderGeometry(0.08, 0.08, 4.4, 12);
    const poleMat = new THREE.MeshBasicMaterial({ color: 0xdc2626 });
    boomBarrierMesh = new THREE.Mesh(poleGeo, poleMat);
    boomBarrierMesh.rotation.z = Math.PI / 2;
    boomBarrierMesh.position.set(laneInX, 1.2, 43);
    scene.add(boomBarrierMesh);

    // Outbound Boom Barrier Pole (Lane 2, Animated Palang Pintu Keluar)
    const bBaseOut = new THREE.Mesh(barrierBaseGeo, barrierBaseMat);
    bBaseOut.position.set(laneOutX + 2.2, 0.7, 43);
    scene.add(bBaseOut);

    outboundBarrierMesh = new THREE.Mesh(poleGeo, poleMat);
    outboundBarrierMesh.rotation.z = Math.PI / 2;
    outboundBarrierMesh.position.set(laneOutX, 1.2, 43);
    scene.add(outboundBarrierMesh);

    // Outbound Lane Display
    const outDispMat = new THREE.MeshBasicMaterial({ color: 0x06b6d4 });
    const outDisp = new THREE.Mesh(ledDispGeo, outDispMat);
    outDisp.position.set(laneOutX, 6.2, gateZ);
    scene.add(outDisp);

    createBlockLabelSprite('OUTBOUND GATE (CHECKOUT & E-SEAL)', laneOutX, 7.5, 43);

    const __mSec = relocBegin(null);
    // Security Post 24 Jam (fac_f_security_gate)
    const secGeo = new THREE.BoxGeometry(8, 4.5, 8);
    const secMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.4 });
    const secPost = new THREE.Mesh(secGeo, secMat);
    secPost.position.set(-65, 2.25, gateZ);
    secPost.castShadow = true;
    scene.add(secPost);

    createBlockLabelSprite('SECURITY POST 24 JAM', -65, 5.5, gateZ);

    relocEnd(__mSec, 'f_security_gate', -65, 52);
    const __mRest = relocBegin(null);
    // Driver Rest Area & Kiosk (fac_f_driver_rest)
    const restGeo = new THREE.BoxGeometry(14, 4.5, 8);
    const restMat = new THREE.MeshStandardMaterial({ color: 0x475569, roughness: 0.6 });
    const restArea = new THREE.Mesh(restGeo, restMat);
    restArea.position.set(-80, 2.25, 38);
    restArea.castShadow = true;
    scene.add(restArea);

    createBlockLabelSprite('REST AREA SOPIR & KIOSK', -80, 5.5, 38);

    relocEnd(__mRest, 'f_driver_rest', -80, 38);
    const __mQ = relocBegin(null);
    // Truck Queuing Parking Yard (fac_f_truck_queue)
    const queueGeo = new THREE.BoxGeometry(18, 0.1, 14);
    const queueMat = new THREE.MeshStandardMaterial({ color: 0x334155, roughness: 0.9 });
    const queueYard = new THREE.Mesh(queueGeo, queueMat);
    queueYard.position.set(-80, 0.08, 54);
    scene.add(queueYard);

    // Striped queue parking lines
    for (let qx = -87; qx <= -73; qx += 3.5) {
        const lineGeo = new THREE.BoxGeometry(0.15, 0.02, 12);
        const lineMat = new THREE.MeshBasicMaterial({ color: 0xf59e0b });
        const line = new THREE.Mesh(lineGeo, lineMat);
        line.position.set(qx, 0.14, 54);
        scene.add(line);
    }

    createBlockLabelSprite('PARKIR TRUK ANTRIAN (40 TRAILER)', -80, 6.0, 54);
    relocEnd(__mQ, 'f_truck_queue', -80, 54);
}

// 5. Administration, Datacenter NOC, Mosque, Canteen, Clinic & Damkar
function buildAdministrationAndPublicZone() {
    const adminGroup = new THREE.Group();
    adminGroup.name = "AdministrationZone";

    const __mA0 = relocBegin(adminGroup);
    // 1. Main Office / Admin Building (fac_f_admin_office) - 2 Floors
    const officeBaseGeo = new THREE.BoxGeometry(22, 4.5, 12);
    const officeBaseMat = new THREE.MeshStandardMaterial({ color: 0xe2e8f0, roughness: 0.5 });
    const officeBase = new THREE.Mesh(officeBaseGeo, officeBaseMat);
    officeBase.position.set(-20, 2.25, 54);
    officeBase.castShadow = true;
    adminGroup.add(officeBase);

    // Floor 2 Glass Curtain Wall
    const glassGeo = new THREE.BoxGeometry(21.6, 3.5, 11.6);
    const glassMat = new THREE.MeshStandardMaterial({ color: 0x0284c7, roughness: 0.1, transparent: true, opacity: 0.8, metalness: 0.8 });
    const glassFloor = new THREE.Mesh(glassGeo, glassMat);
    glassFloor.position.set(-20, 6.25, 54);
    adminGroup.add(glassFloor);

    // Modern Roof Coping & HVAC
    const roofCopingGeo = new THREE.BoxGeometry(22.4, 0.4, 12.4);
    const roofMat = new THREE.MeshStandardMaterial({ color: 0x334155, roughness: 0.6 });
    const roofCoping = new THREE.Mesh(roofCopingGeo, roofMat);
    roofCoping.position.set(-20, 8.2, 54);
    adminGroup.add(roofCoping);

    const hvacGeo = new THREE.BoxGeometry(3, 1.2, 3);
    const hvacMat = new THREE.MeshStandardMaterial({ color: 0x94a3b8, metalness: 0.6 });
    const hvac = new THREE.Mesh(hvacGeo, hvacMat);
    hvac.position.set(-16, 8.8, 54);
    adminGroup.add(hvac);

    // Entrance Canopy (Blue corporate MTI)
    const canopyGeo = new THREE.BoxGeometry(7, 0.3, 4);
    const canopyMat = new THREE.MeshStandardMaterial({ color: 0x004b87, roughness: 0.3 });
    const canopy = new THREE.Mesh(canopyGeo, canopyMat);
    canopy.position.set(-20, 2.5, 46.5);
    adminGroup.add(canopy);

    // Indonesian Flag Pole
    const poleGeo = new THREE.CylinderGeometry(0.08, 0.08, 10, 8);
    const poleMat = new THREE.MeshStandardMaterial({ color: 0xcbd5e1, metalness: 0.8 });
    const pole = new THREE.Mesh(poleGeo, poleMat);
    pole.position.set(-20, 5, 43);
    adminGroup.add(pole);

    const flagGeo = new THREE.BoxGeometry(1.8, 1.0, 0.05);
    const flagMat = new THREE.MeshBasicMaterial({ color: 0xdc2626 });
    const flag = new THREE.Mesh(flagGeo, flagMat);
    flag.position.set(-19, 9.3, 43);
    adminGroup.add(flag);

    createBlockLabelSprite('MAIN OFFICE PT MTI (HEADQUARTERS)', -20, 10.5, 54);

    relocEnd(__mA0, 'f_admin_office', -20, 54);
    const __mA1 = relocBegin(adminGroup);
    // 2. Datacenter / Server Room (NOC) (fac_f_datacenter)
    const dcGeo = new THREE.BoxGeometry(14, 6.5, 12);
    const dcMat = new THREE.MeshStandardMaterial({ color: 0x334155, roughness: 0.7 });
    const dc = new THREE.Mesh(dcGeo, dcMat);
    dc.position.set(-4, 3.25, 54);
    dc.castShadow = true;
    adminGroup.add(dc);

    // High Microwave & Telecom Lattice Mast
    const mastGeo = new THREE.CylinderGeometry(0.15, 0.6, 16, 6);
    const mastMat = new THREE.MeshStandardMaterial({ color: 0xdc2626, metalness: 0.6 });
    const mast = new THREE.Mesh(mastGeo, mastMat);
    mast.position.set(-4, 14.5, 54);
    adminGroup.add(mast);

    // Microwave Dish Antenna
    const dishGeo = new THREE.CylinderGeometry(1.2, 0.2, 0.4, 16);
    const dishMat = new THREE.MeshStandardMaterial({ color: 0xffffff, roughness: 0.4 });
    const dish = new THREE.Mesh(dishGeo, dishMat);
    dish.rotation.x = Math.PI / 2;
    dish.position.set(-4, 18, 54);
    adminGroup.add(dish);

    createBlockLabelSprite('DATACENTER & NOC (TIER-3 YMS)', -4, 9.0, 54);

    relocEnd(__mA1, 'f_datacenter', -4, 54);
    const __mA2 = relocBegin(adminGroup);
    // 3. Meeting & Training Room (fac_f_meeting_room)
    const meetGeo = new THREE.BoxGeometry(12, 5.5, 12);
    const meetMat = new THREE.MeshStandardMaterial({ color: 0x475569, roughness: 0.6 });
    const meet = new THREE.Mesh(meetGeo, meetMat);
    meet.position.set(12, 2.75, 54);
    meet.castShadow = true;
    adminGroup.add(meet);

    relocEnd(__mA2, 'f_meeting_room', 12, 54);
    const __mA3 = relocBegin(adminGroup);
    // 4. Staff & Guest Parking (fac_f_parking_staff)
    const parkGeo = new THREE.BoxGeometry(12, 0.1, 12);
    const parkMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.9 });
    const park = new THREE.Mesh(parkGeo, parkMat);
    park.position.set(26, 0.06, 54);
    adminGroup.add(park);

    // White parking lot lines
    for (let pz = 49; pz <= 59; pz += 3) {
        const lineGeo = new THREE.BoxGeometry(10, 0.02, 0.15);
        const lineMat = new THREE.MeshBasicMaterial({ color: 0xffffff });
        const line = new THREE.Mesh(lineGeo, lineMat);
        line.position.set(26, 0.12, pz);
        adminGroup.add(line);
    }

    relocEnd(__mA3, 'f_parking_staff', 26, 54);
    const __mA4 = relocBegin(adminGroup);
    // 5. Masjid Al-Hidayah (fac_f_musholla)
    const mosqueGeo = new THREE.BoxGeometry(14, 5.5, 10);
    const mosqueMat = new THREE.MeshStandardMaterial({ color: 0xf8fafc, roughness: 0.4 });
    const mosque = new THREE.Mesh(mosqueGeo, mosqueMat);
    mosque.position.set(-20, 2.75, 38);
    mosque.castShadow = true;
    adminGroup.add(mosque);

    // Green Dome
    const domeGeo = new THREE.SphereGeometry(2.5, 16, 12, 0, Math.PI * 2, 0, Math.PI / 2);
    const domeMat = new THREE.MeshStandardMaterial({ color: 0x10b981, roughness: 0.3, metalness: 0.2 });
    const dome = new THREE.Mesh(domeGeo, domeMat);
    dome.position.set(-20, 5.5, 38);
    adminGroup.add(dome);

    // Minaret
    const minaretGeo = new THREE.CylinderGeometry(0.6, 0.8, 12, 8);
    const minaret = new THREE.Mesh(minaretGeo, mosqueMat);
    minaret.position.set(-12, 6, 38);
    adminGroup.add(minaret);

    const minaretDome = new THREE.Mesh(new THREE.ConeGeometry(0.9, 2.5, 8), domeMat);
    minaretDome.position.set(-12, 13.25, 38);
    adminGroup.add(minaretDome);

    createBlockLabelSprite('MASJID AL-HIDAYAH', -20, 8.5, 38);

    relocEnd(__mA4, 'f_musholla', -20, 38);
    const __mA5 = relocBegin(adminGroup);
    // 6. Canteen & Koperasi Karyawan (fac_f_kantin, fac_f_koperasi)
    const cantGeo = new THREE.BoxGeometry(14, 4.5, 9);
    const cantMat = new THREE.MeshStandardMaterial({ color: 0x64748b, roughness: 0.6 });
    const canteen = new THREE.Mesh(cantGeo, cantMat);
    canteen.position.set(-4, 2.25, 38);
    canteen.castShadow = true;
    adminGroup.add(canteen);

    // Canteen Awning
    const cAwningGeo = new THREE.BoxGeometry(13.6, 0.2, 3);
    const cAwningMat = new THREE.MeshStandardMaterial({ color: 0x0284c7 });
    const cAwning = new THREE.Mesh(cAwningGeo, cAwningMat);
    cAwning.position.set(-4, 2.6, 32.5);
    adminGroup.add(cAwning);

    relocEnd(__mA5, 'f_kantin', -4, 38);
    const __mA6 = relocBegin(adminGroup);
    // 7. Klinik P3K K3 (fac_f_klinik)
    const clinicGeo = new THREE.BoxGeometry(10, 4.5, 9);
    const clinicMat = new THREE.MeshStandardMaterial({ color: 0xffffff, roughness: 0.3 });
    const clinic = new THREE.Mesh(clinicGeo, clinicMat);
    clinic.position.set(12, 2.25, 38);
    clinic.castShadow = true;
    adminGroup.add(clinic);

    // Red Cross Symbol on Clinic
    const cross1Geo = new THREE.BoxGeometry(0.3, 1.8, 0.05);
    const cross2Geo = new THREE.BoxGeometry(1.8, 0.3, 0.05);
    const crossMat = new THREE.MeshBasicMaterial({ color: 0xef4444 });
    const cross1 = new THREE.Mesh(cross1Geo, crossMat);
    cross1.position.set(12, 3.2, 33.4);
    const cross2 = new THREE.Mesh(cross2Geo, crossMat);
    cross2.position.set(12, 3.2, 33.4);
    adminGroup.add(cross1);
    adminGroup.add(cross2);

    relocEnd(__mA6, 'f_klinik', 12, 38);
    const __mA7 = relocBegin(adminGroup);
    // 8. Damkar & Fire Station (fac_f_damkar)
    const fireGeo = new THREE.BoxGeometry(12, 5.5, 10);
    const fireMat = new THREE.MeshStandardMaterial({ color: 0xb91c1c, roughness: 0.5 });
    const fireStation = new THREE.Mesh(fireGeo, fireMat);
    fireStation.position.set(26, 2.75, 38);
    fireStation.castShadow = true;
    adminGroup.add(fireStation);

    // 2 Red Roll-up Garage Doors
    [-3, 3].forEach(dx => {
        const doorGeo = new THREE.BoxGeometry(4.2, 4.2, 0.2);
        const doorMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.3 });
        const door = new THREE.Mesh(doorGeo, doorMat);
        door.position.set(26 + dx, 2.1, 32.9);
        adminGroup.add(door);
    });

    createBlockLabelSprite('POS DAMKAR & FIRE STATION', 26, 7.5, 38);

    relocEnd(__mA7, 'f_damkar', 26, 38);
    const __mA8 = relocBegin(adminGroup);
    // Reefer Control Room & Genset Shelter (Zone 3 Aux)
    const rCtlGeo = new THREE.BoxGeometry(14, 5, 8);
    const rCtlMat = new THREE.MeshStandardMaterial({ color: 0x0891b2, roughness: 0.4 });
    const rCtl = new THREE.Mesh(rCtlGeo, rCtlMat);
    rCtl.position.set(88, 2.5, 52);
    adminGroup.add(rCtl);
    createBlockLabelSprite('REEFER CONTROL ROOM', 88, 6.0, 52);

    relocEnd(__mA8, 'f_reefer_control', 88, 52);
    const __mA9 = relocBegin(adminGroup);
    const gensetGeo = new THREE.BoxGeometry(14, 4.5, 8);
    const gensetMat = new THREE.MeshStandardMaterial({ color: 0x334155, roughness: 0.6 });
    const genset = new THREE.Mesh(gensetGeo, gensetMat);
    genset.position.set(88, 2.25, 38);
    adminGroup.add(genset);
    createBlockLabelSprite('GENSET SHELTER (1.500 kVA)', 88, 5.5, 38);

    relocEnd(__mA9, 'f_genset_shelter', 88, 38);
    scene.add(adminGroup);
}

// 6. CFS Warehouse 4.000m², Transit Warehouse & M&R Heavy Equipment Workshop
function buildCFSAndWarehouseZone() {
    const cfsGroup = new THREE.Group();
    cfsGroup.name = "CFSAndWarehousingZone";

    const __mcF0 = relocBegin(cfsGroup);
    // 1. CFS Warehouse 4.000 m² (fac_f_cfs) - Clean Modern Architectural SaaS Aesthetic
    const cfsW = 24, cfsH = 8.5, cfsD = 18;
    const steelMat = new THREE.MeshStandardMaterial({ color: 0x1d4ed8, metalness: 0.7, roughness: 0.3 }); // Vibrant electric royal blue frame
    const claddingMat = new THREE.MeshStandardMaterial({ color: 0xf8fafc, roughness: 0.55 }); // Crisp off-white sandwich panels
    const blueTrimMat = new THREE.MeshStandardMaterial({ color: 0x1e40af, roughness: 0.4 }); // Deep blue accents
    const cfsX = -88, cfsZ = 14;
    const roofRise = (cfsW / 2) * Math.tan(Math.PI / 16);

    // Dinding Penutup Sandwich Panel Off-White Modern
    const cfs = new THREE.Mesh(new THREE.BoxGeometry(cfsW - 0.2, cfsH - 0.2, cfsD - 0.2), claddingMat);
    cfs.position.set(cfsX, (cfsH - 0.2) / 2, cfsZ);
    cfs.castShadow = true;
    cfsGroup.add(cfs);

    // Blue Architectural Accent Band at top of facade
    const topBandGeo = new THREE.BoxGeometry(cfsW + 0.1, 0.6, cfsD + 0.1);
    const topBand = new THREE.Mesh(topBandGeo, blueTrimMat);
    topBand.position.set(cfsX, cfsH - 0.3, cfsZ);
    cfsGroup.add(topBand);

    // Portal Frame: Kolom Baja WF + Kuda-kuda Atap (rafter miring) tiap 4.5 unit
    const colGeo = new THREE.BoxGeometry(0.45, cfsH, 0.45);
    const rafterGeo = new THREE.BoxGeometry(cfsW / 2 / Math.cos(Math.PI / 16) + 0.3, 0.35, 0.35);
    for (let pz = -cfsD / 2; pz <= cfsD / 2 + 0.01; pz += 4.5) {
        [-cfsW / 2, cfsW / 2].forEach(px => {
            const col = new THREE.Mesh(colGeo, steelMat);
            col.position.set(cfsX + px, cfsH / 2, cfsZ + pz);
            col.castShadow = true;
            cfsGroup.add(col);
        });

        // Rafter kiri naik ke bubungan, kanan turun (rotasi +/- PI/16)
        const rafterL = new THREE.Mesh(rafterGeo, steelMat);
        rafterL.position.set(cfsX - cfsW / 4, cfsH + roofRise / 2, cfsZ + pz);
        rafterL.rotation.z = Math.PI / 16;
        cfsGroup.add(rafterL);

        const rafterR = new THREE.Mesh(rafterGeo, steelMat);
        rafterR.position.set(cfsX + cfsW / 4, cfsH + roofRise / 2, cfsZ + pz);
        rafterR.rotation.z = -Math.PI / 16;
        cfsGroup.add(rafterR);
    }

    // Gording (purlin) memanjang sepanjang gudang di sisi atap
    const purlinGeo = new THREE.BoxGeometry(0.2, 0.2, cfsD + 0.4);
    [-0.9, -0.5, -0.1, 0.1, 0.5, 0.9].forEach(f => {
        const px = f * (cfsW / 2);
        const py = cfsH + (1 - Math.abs(f)) * roofRise + 0.25;
        const purlin = new THREE.Mesh(purlinGeo, steelMat);
        purlin.position.set(cfsX + px, py, cfsZ);
        cfsGroup.add(purlin);
    });

    // Penutup atap (roof sheet) dua bidang miring
    const roofLen = cfsW / 2 / Math.cos(Math.PI / 16) + 0.4;
    const roofGeo = new THREE.BoxGeometry(roofLen, 0.12, cfsD + 0.4);
    const roofMat = new THREE.MeshStandardMaterial({ color: 0xcfd8dc, metalness: 0.45, roughness: 0.4 });
    const roofL = new THREE.Mesh(roofGeo, roofMat);
    roofL.position.set(cfsX - cfsW / 4, cfsH + roofRise / 2 + 0.38, cfsZ);
    roofL.rotation.z = Math.PI / 16;
    roofL.castShadow = true;
    cfsGroup.add(roofL);
    const roofR = new THREE.Mesh(roofGeo, roofMat);
    roofR.position.set(cfsX + cfsW / 4, cfsH + roofRise / 2 + 0.38, cfsZ);
    roofR.rotation.z = -Math.PI / 16;
    roofR.castShadow = true;
    cfsGroup.add(roofR);

    // Dinding gable segitiga di kedua ujung gudang
    const gableShape = new THREE.Shape();
    gableShape.moveTo(-cfsW / 2 + 0.1, 0);
    gableShape.lineTo(cfsW / 2 - 0.1, 0);
    gableShape.lineTo(0, roofRise + 0.1);
    gableShape.closePath();
    const gableGeo = new THREE.ShapeGeometry(gableShape);
    const gableMat = new THREE.MeshStandardMaterial({ color: 0xf8fafc, roughness: 0.55, side: THREE.DoubleSide });
    [-1, 1].forEach(s => {
        const gable = new THREE.Mesh(gableGeo, gableMat);
        gable.position.set(cfsX, cfsH - 0.2, cfsZ + s * (cfsD / 2 - 0.1));
        cfsGroup.add(gable);
    });

    // Raised Loading Dock Platform (K-400 Concrete)
    const dockGeo = new THREE.BoxGeometry(3.5, 1.2, cfsD);
    const dockMat = new THREE.MeshStandardMaterial({ color: 0x94a3b8, roughness: 0.75 });
    const dock = new THREE.Mesh(dockGeo, dockMat);
    dock.position.set(-88 + cfsW/2 + 1.75, 0.6, 14);
    dock.receiveShadow = true;
    cfsGroup.add(dock);

    // 4 Modern Loading Dock Bays with Blue Portals, Bay Badges & Open Interior Staging
    const dockBays = [
        { dz: 7, label: 'BAY 01', open: true, hasPallets: true },
        { dz: 11.5, label: 'BAY 02', open: true, hasPallets: true },
        { dz: 16, label: 'BAY 03', open: true, hasPallets: false }, // Backed-up Box Truck
        { dz: 20.5, label: 'BAY 04', open: false, hasPallets: false }
    ];

    dockBays.forEach(bay => {
        // Blue portal frame border surround
        const frameL = new THREE.Mesh(new THREE.BoxGeometry(0.25, 4.2, 0.25), blueTrimMat);
        frameL.position.set(-88 + cfsW/2 + 0.12, 3.3, bay.dz - 1.6);
        cfsGroup.add(frameL);

        const frameR = new THREE.Mesh(new THREE.BoxGeometry(0.25, 4.2, 0.25), blueTrimMat);
        frameR.position.set(-88 + cfsW/2 + 0.12, 3.3, bay.dz + 1.6);
        cfsGroup.add(frameR);

        const frameTop = new THREE.Mesh(new THREE.BoxGeometry(0.25, 0.35, 3.45), blueTrimMat);
        frameTop.position.set(-88 + cfsW/2 + 0.12, 5.4, bay.dz);
        cfsGroup.add(frameTop);

        // Overhead Bay Number Signboard
        const baySignGeo = new THREE.BoxGeometry(0.22, 0.45, 1.5);
        const baySign = new THREE.Mesh(baySignGeo, new THREE.MeshStandardMaterial({ color: 0x1d4ed8 }));
        baySign.position.set(-88 + cfsW/2 + 0.18, 5.8, bay.dz);
        cfsGroup.add(baySign);

        if (bay.open) {
            // Door rolled up (compact roll cylinder at top)
            const rollGeo = new THREE.CylinderGeometry(0.35, 0.35, 3.1, 12);
            const rollMat = new THREE.MeshStandardMaterial({ color: 0x334155, roughness: 0.5 });
            const roll = new THREE.Mesh(rollGeo, rollMat);
            roll.rotation.x = Math.PI / 2;
            roll.position.set(-88 + cfsW/2 + 0.05, 5.0, bay.dz);
            cfsGroup.add(roll);

            // Warm interior lighting glowing from inside the dock
            const dockLight = new THREE.PointLight(0xffedd5, 1.6, 12);
            dockLight.position.set(-88 + cfsW/2 - 2.5, 3.8, bay.dz);
            cfsGroup.add(dockLight);

            // Staged cargo pallets inside the open bay
            if (bay.hasPallets) {
                const palletStack = createCargoPalletStack(-88 + cfsW/2 - 2.2, 1.25, bay.dz);
                cfsGroup.add(palletStack);
            }
        } else {
            // Closed roll door
            const dDoorGeo = new THREE.BoxGeometry(0.2, 4.0, 3.1);
            const dDoorMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.3 });
            const dDoor = new THREE.Mesh(dDoorGeo, dDoorMat);
            dDoor.position.set(-88 + cfsW/2 + 0.05, 3.2, bay.dz);
            cfsGroup.add(dDoor);
        }

        // Heavy-duty rubber dock bumper with yellow safety markings
        const bGeo = new THREE.BoxGeometry(0.4, 0.4, 3.2);
        const bMat = new THREE.MeshBasicMaterial({ color: 0xf59e0b });
        const b = new THREE.Mesh(bGeo, bMat);
        b.position.set(-88 + cfsW/2 + 3.5, 0.6, bay.dz);
        cfsGroup.add(b);
    });

    createBlockLabelSprite('CFS WAREHOUSE 4.000 M² (STEEL PORTAL FRAME)', -88, cfsH + 5.5, 14);

    relocEnd(__mcF0, 'f_cfs', -88, 14, true);
    const __mcF1 = relocBegin(cfsGroup);
    // 2. Transit Distribution Warehouse (fac_f_warehouse)
    const trW = 24, trH = 7, trD = 13;
    const trGeo = new THREE.BoxGeometry(trW, trH, trD);
    const trMat = new THREE.MeshStandardMaterial({ color: 0xe2e8f0, roughness: 0.55 });
    const trWarehouse = new THREE.Mesh(trGeo, trMat);
    trWarehouse.position.set(-88, trH/2, -3);
    trWarehouse.castShadow = true;
    cfsGroup.add(trWarehouse);

    // Navy Accent Band on Transit Warehouse
    const trBand = new THREE.Mesh(new THREE.BoxGeometry(trW + 0.1, 0.5, trD + 0.1), blueTrimMat);
    trBand.position.set(-88, trH - 0.25, -3);
    cfsGroup.add(trBand);

    createBlockLabelSprite('TRANSIT WAREHOUSE & CROSS-DOCKING', -88, trH + 2.5, -3);

    relocEnd(__mcF1, 'f_warehouse', -88, -3, true);
    const __mcF2 = relocBegin(cfsGroup);
    // 3. M&R Workshop / Bengkel Alat Berat (fac_f_workshop_mr)
    const mrW = 24, mrH = 9, mrD = 16;
    const mrGeo = new THREE.BoxGeometry(mrW, mrH, mrD);
    const mrMat = new THREE.MeshStandardMaterial({ color: 0x94a3b8, roughness: 0.5 });
    const mrWorkshop = new THREE.Mesh(mrGeo, mrMat);
    mrWorkshop.position.set(-88, mrH/2, -20);
    mrWorkshop.castShadow = true;
    cfsGroup.add(mrWorkshop);

    // Overhead Gantry Crane Rail extending outside M&R bay
    const craneBeamGeo = new THREE.BoxGeometry(12, 0.7, 0.5);
    const craneBeamMat = new THREE.MeshStandardMaterial({ color: 0xf59e0b, metalness: 0.6, roughness: 0.35 });
    const craneBeam = new THREE.Mesh(craneBeamGeo, craneBeamMat);
    craneBeam.position.set(-88 + mrW/2 + 4, 7.8, -20);
    cfsGroup.add(craneBeam);

    // Kolom penumpu runway crane di ujung luar balok
    const runwayColMat = new THREE.MeshStandardMaterial({ color: 0x1d4ed8, metalness: 0.8, roughness: 0.3 });
    [-1, 1].forEach(s => {
        const rc = new THREE.Mesh(new THREE.BoxGeometry(0.45, 7.8, 0.45), runwayColMat);
        rc.position.set(-88 + mrW/2 + 9.8, 3.9, -20 + s * 0.9);
        cfsGroup.add(rc);
    });

    createBlockLabelSprite('M&R HEAVY EQUIPMENT WORKSHOP', -88, mrH + 2.5, -20);

    relocEnd(__mcF2, 'f_workshop_mr', -88, -20, true);
    scene.add(cfsGroup);
}

// =============================================================================
// PROCEDURAL ASSETS: CARGO PALLETS, FORKLIFTS, EV CHARGING & DELIVERY BOX TRUCK
// (Matches WareTrack / 3D SaaS Digital Twin Master Reference)
// =============================================================================

// Helper: Stacked Wooden Pallet with Cardboard Cargo Boxes
function createCargoPalletStack(x, y, z) {
    const stackGroup = new THREE.Group();
    
    // Wooden pallet base (warm natural timber)
    const woodMat = new THREE.MeshStandardMaterial({ color: 0xb45309, roughness: 0.85 });
    const deckGeo = new THREE.BoxGeometry(1.6, 0.08, 1.3);
    const deck = new THREE.Mesh(deckGeo, woodMat);
    deck.position.set(0, 0.12, 0);
    stackGroup.add(deck);

    const runnerGeo = new THREE.BoxGeometry(1.6, 0.08, 0.14);
    [-0.5, 0, 0.5].forEach(rz => {
        const runner = new THREE.Mesh(runnerGeo, woodMat);
        runner.position.set(0, 0.04, rz);
        stackGroup.add(runner);
    });

    // Cardboard boxes in warm kraft packaging colors
    const boxMat1 = new THREE.MeshStandardMaterial({ color: 0xd97706, roughness: 0.8 });
    const boxMat2 = new THREE.MeshStandardMaterial({ color: 0xb45309, roughness: 0.8 });
    const boxMat3 = new THREE.MeshStandardMaterial({ color: 0x92400e, roughness: 0.8 });
    const boxMats = [boxMat1, boxMat2, boxMat3];

    const bGeo = new THREE.BoxGeometry(0.7, 0.5, 0.55);
    const boxPositions = [
        { bx: -0.36, by: 0.42, bz: -0.28, rot: 0 },
        { bx: 0.36, by: 0.42, bz: -0.28, rot: 0 },
        { bx: -0.36, by: 0.42, bz: 0.28, rot: 0 },
        { bx: 0.36, by: 0.42, bz: 0.28, rot: 0 },
        { bx: -0.15, by: 0.85, bz: 0, rot: 0.05 },
        { bx: 0.32, by: 0.85, bz: 0.1, rot: -0.05 }
    ];

    boxPositions.forEach((bp, idx) => {
        const box = new THREE.Mesh(bGeo, boxMats[idx % boxMats.length]);
        box.position.set(bp.bx, bp.by, bp.bz);
        box.rotation.y = bp.rot;
        box.castShadow = true;
        stackGroup.add(box);
    });

    stackGroup.position.set(x, y, z);
    return stackGroup;
}

// Helper: 3D Floating Speech Bubble Callout Badge ("[⚡ FL-12 Charging]")
function createChargingCalloutBadge(text, batteryPercent) {
    const canvas = document.createElement('canvas');
    canvas.width = 384;
    canvas.height = 128;
    const ctx = canvas.getContext('2d');
    
    // Background card (white with subtle drop shadow)
    ctx.fillStyle = '#ffffff';
    ctx.shadowColor = 'rgba(0,0,0,0.22)';
    ctx.shadowBlur = 10;
    ctx.shadowOffsetY = 4;
    
    const x = 12, y = 8, w = 360, h = 90, r = 18;
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.lineTo(x + w - r, y);
    ctx.quadraticCurveTo(x + w, y, x + w, y + r);
    ctx.lineTo(x + w, y + h - r);
    ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
    // Tail pointing down to the vehicle
    ctx.lineTo(x + w / 2 + 16, y + h);
    ctx.lineTo(x + w / 2, y + h + 22);
    ctx.lineTo(x + w / 2 - 16, y + h);
    ctx.lineTo(x + r, y + h);
    ctx.quadraticCurveTo(x, y + h, x, y + h - r);
    ctx.lineTo(x, y + r);
    ctx.quadraticCurveTo(x, y, x + r, y);
    ctx.closePath();
    ctx.fill();
    ctx.shadowColor = 'transparent';

    // Green battery badge icon
    ctx.fillStyle = '#10b981';
    ctx.beginPath();
    if (ctx.roundRect) {
        ctx.roundRect(26, 24, 48, 56, 10);
    } else {
        ctx.rect(26, 24, 48, 56);
    }
    ctx.fill();

    // Lightning bolt icon
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 30px sans-serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('⚡', 50, 52);

    // Vehicle Name
    ctx.fillStyle = '#0f172a';
    ctx.font = 'bold 24px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
    ctx.textAlign = 'left';
    ctx.textBaseline = 'alphabetic';
    ctx.fillText(text, 88, 48);

    // Battery percentage
    ctx.fillStyle = '#10b981';
    ctx.font = 'bold 20px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
    ctx.fillText(`${batteryPercent}% Charging`, 88, 75);

    const texture = new THREE.CanvasTexture(canvas);
    const spriteMat = new THREE.SpriteMaterial({ map: texture, transparent: true });
    const sprite = new THREE.Sprite(spriteMat);
    sprite.scale.set(6.8, 2.26, 1);
    return sprite;
}

// Procedural Industrial Forklift Model Builder
function createForkliftModel(colorHex = 0xf59e0b, hasPallet = false) {
    const flGroup = new THREE.Group();

    const bodyMat = new THREE.MeshStandardMaterial({ color: colorHex, roughness: 0.4, metalness: 0.2 });
    const darkMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.6, metalness: 0.4 });
    const blackMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.8 });
    const wheelMat = new THREE.MeshStandardMaterial({ color: 0x0a0e17, roughness: 0.9 });
    const forkMat = new THREE.MeshStandardMaterial({ color: 0x334155, metalness: 0.8, roughness: 0.3 });
    const lightMat = new THREE.MeshBasicMaterial({ color: 0xfef08a });
    const amberBeaconMat = new THREE.MeshBasicMaterial({ color: 0xf59e0b });

    // 1. Lower Chassis
    const baseGeo = new THREE.BoxGeometry(2.3, 0.55, 1.35);
    const base = new THREE.Mesh(baseGeo, bodyMat);
    base.position.set(0, 0.48, 0);
    base.castShadow = true;
    flGroup.add(base);

    // Rear counterweight
    const cwGeo = new THREE.BoxGeometry(0.7, 0.7, 1.32);
    const cw = new THREE.Mesh(cwGeo, darkMat);
    cw.position.set(-0.85, 0.7, 0);
    cw.castShadow = true;
    flGroup.add(cw);

    // 2. Cab Floor & Seat Console
    const seatGeo = new THREE.BoxGeometry(0.5, 0.5, 0.45);
    const seat = new THREE.Mesh(seatGeo, blackMat);
    seat.position.set(-0.2, 0.9, 0);
    flGroup.add(seat);

    // Steering Column & Wheel
    const colGeo = new THREE.CylinderGeometry(0.04, 0.04, 0.6);
    const col = new THREE.Mesh(colGeo, darkMat);
    col.position.set(0.3, 0.95, 0);
    col.rotation.z = -Math.PI / 8;
    flGroup.add(col);

    const steerGeo = new THREE.TorusGeometry(0.14, 0.025, 8, 16);
    const steer = new THREE.Mesh(steerGeo, darkMat);
    steer.position.set(0.38, 1.2, 0);
    steer.rotation.x = Math.PI / 2.5;
    flGroup.add(steer);

    // 3. Overhead Guard (ROPS Cage)
    const ropsPillarGeo = new THREE.BoxGeometry(0.06, 1.5, 0.06);
    [
        { x: -0.6, z: -0.55 },
        { x: -0.6, z: 0.55 },
        { x: 0.35, z: -0.55 },
        { x: 0.35, z: 0.55 }
    ].forEach(pos => {
        const pillar = new THREE.Mesh(ropsPillarGeo, darkMat);
        pillar.position.set(pos.x, 1.45, pos.z);
        flGroup.add(pillar);
    });

    const roofTopGeo = new THREE.BoxGeometry(1.05, 0.05, 1.2);
    const roofTop = new THREE.Mesh(roofTopGeo, darkMat);
    roofTop.position.set(-0.12, 2.2, 0);
    flGroup.add(roofTop);

    // Amber Safety Strobe Beacon on top
    const beaconGeo = new THREE.CylinderGeometry(0.07, 0.07, 0.12, 12);
    const beacon = new THREE.Mesh(beaconGeo, amberBeaconMat);
    beacon.position.set(-0.12, 2.28, 0);
    flGroup.add(beacon);

    // 4. Front Mast Assembly (Vertical I-beams)
    const mastGeo = new THREE.BoxGeometry(0.08, 2.1, 0.1);
    [-0.42, 0.42].forEach(mz => {
        const mast = new THREE.Mesh(mastGeo, darkMat);
        mast.position.set(1.18, 1.25, mz);
        flGroup.add(mast);
    });

    const crossGeo = new THREE.BoxGeometry(0.06, 0.08, 0.94);
    [0.7, 1.4, 2.1].forEach(my => {
        const cross = new THREE.Mesh(crossGeo, darkMat);
        cross.position.set(1.18, my, 0);
        flGroup.add(cross);
    });

    // Carriage plate & Fork Tines
    const carriageGeo = new THREE.BoxGeometry(0.06, 0.4, 0.8);
    const carriage = new THREE.Mesh(carriageGeo, darkMat);
    carriage.position.set(1.24, 0.45, 0);
    flGroup.add(carriage);

    const forkGeo = new THREE.BoxGeometry(1.25, 0.05, 0.12);
    [-0.22, 0.22].forEach(fz => {
        const fork = new THREE.Mesh(forkGeo, forkMat);
        fork.position.set(1.88, 0.18, fz);
        flGroup.add(fork);
        const forkBackGeo = new THREE.BoxGeometry(0.05, 0.38, 0.12);
        const forkBack = new THREE.Mesh(forkBackGeo, forkMat);
        forkBack.position.set(1.27, 0.36, fz);
        flGroup.add(forkBack);
    });

    // 5. Wheels
    const fWheelGeo = new THREE.CylinderGeometry(0.38, 0.38, 0.28, 18);
    [-0.64, 0.64].forEach(wz => {
        const fw = new THREE.Mesh(fWheelGeo, wheelMat);
        fw.rotation.x = Math.PI / 2;
        fw.position.set(0.68, 0.38, wz);
        fw.castShadow = true;
        flGroup.add(fw);
    });

    const rWheelGeo = new THREE.CylinderGeometry(0.30, 0.30, 0.24, 18);
    [-0.58, 0.58].forEach(wz => {
        const rw = new THREE.Mesh(rWheelGeo, wheelMat);
        rw.rotation.x = Math.PI / 2;
        rw.position.set(-0.75, 0.30, wz);
        rw.castShadow = true;
        flGroup.add(rw);
    });

    // Headlights
    [-0.45, 0.45].forEach(lz => {
        const hl = new THREE.Mesh(new THREE.BoxGeometry(0.08, 0.1, 0.1), lightMat);
        hl.position.set(1.0, 1.4, lz);
        flGroup.add(hl);
    });

    // Optional Pallet with Cargo Boxes on the forks
    if (hasPallet) {
        const pallet = createCargoPalletStack(1.95, 0.18, 0);
        pallet.scale.set(0.85, 0.85, 0.85);
        flGroup.add(pallet);
    }

    return flGroup;
}

// Dedicated Forklift Charging Hub & Active Electric Fleet
function buildForkliftHubAndChargingStation() {
    const hubGroup = new THREE.Group();
    hubGroup.name = "ForkliftChargingHub";

    // 1. Dedicated EV Charging Pad on CFS Apron (Light concrete with emerald border)
    const padW = 16, padD = 12;
    const padGeo = new THREE.BoxGeometry(padW, 0.14, padD);
    const padMat = new THREE.MeshStandardMaterial({ color: 0x334155, roughness: 0.75 });
    const pad = new THREE.Mesh(padGeo, padMat);
    pad.position.set(-62, 0.08, 23);
    pad.receiveShadow = true;
    hubGroup.add(pad);

    // Green safety boundary line around EV charging bay
    const lineGeoX = new THREE.BoxGeometry(padW + 0.2, 0.02, 0.3);
    const lineGeoZ = new THREE.BoxGeometry(0.3, 0.02, padD + 0.2);
    const greenLineMat = new THREE.MeshBasicMaterial({ color: 0x10b981 });
    [-padD/2, padD/2].forEach(lz => {
        const line = new THREE.Mesh(lineGeoX, greenLineMat);
        line.position.set(-62, 0.17, 23 + lz);
        hubGroup.add(line);
    });
    [-padW/2, padW/2].forEach(lx => {
        const line = new THREE.Mesh(lineGeoZ, greenLineMat);
        line.position.set(-62 + lx, 0.17, 23);
        hubGroup.add(line);
    });

    // EV Parking Slot Dividing Lines (3 charging bays)
    const slotLineGeo = new THREE.BoxGeometry(padW - 3, 0.02, 0.2);
    const whiteLineMat = new THREE.MeshBasicMaterial({ color: 0xffffff });
    [-2.2, 2.2].forEach(sz => {
        const sLine = new THREE.Mesh(slotLineGeo, whiteLineMat);
        sLine.position.set(-61, 0.17, 23 + sz);
        hubGroup.add(sLine);
    });

    // 2. 3 Sleek White EV Charging Bollards / Pillars
    const bollardGeo = new THREE.BoxGeometry(0.65, 1.8, 0.65);
    const bollardMat = new THREE.MeshStandardMaterial({ color: 0xf8fafc, roughness: 0.3, metalness: 0.1 });
    const screenMat = new THREE.MeshBasicMaterial({ color: 0x10b981 });
    const cableMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.9 });

    const bollardZCoords = [23 - 4.2, 23, 23 + 4.2];
    bollardZCoords.forEach((bz) => {
        const bollard = new THREE.Mesh(bollardGeo, bollardMat);
        bollard.position.set(-68.5, 0.98, bz);
        bollard.castShadow = true;
        hubGroup.add(bollard);

        // Green illuminated status panel
        const screen = new THREE.Mesh(new THREE.BoxGeometry(0.08, 0.45, 0.35), screenMat);
        screen.position.set(-68.15, 1.4, bz);
        hubGroup.add(screen);

        // Blue brand stripe on bollard
        const stripe = new THREE.Mesh(new THREE.BoxGeometry(0.67, 0.15, 0.67), new THREE.MeshStandardMaterial({ color: 0x0284c7 }));
        stripe.position.set(-68.5, 1.7, bz);
        hubGroup.add(stripe);

        // Charging Cable drooping to charging slot
        const cable = new THREE.Mesh(new THREE.CylinderGeometry(0.035, 0.035, 1.8, 8), cableMat);
        cable.position.set(-67.5, 0.7, bz + 0.3);
        cable.rotation.z = Math.PI / 3;
        hubGroup.add(cable);
    });

    // 3. Parked Charging Forklift 1 (FL-12 - Yellow) at Slot 2 (Center)
    const fl12 = createForkliftModel(0xf59e0b, false);
    fl12.position.set(-63.5, 0.08, 23);
    fl12.rotation.y = Math.PI; // facing east
    hubGroup.add(fl12);

    // Floating 3D Speech Bubble Badge "[⚡ FL-01 CFS 3.5T • 84% Baterai]" directly above FL-12
    const callout = createChargingCalloutBadge('FL-01 CFS 3.5T', 84);
    callout.position.set(-63.5, 4.6, 23);
    hubGroup.add(callout);

    // Parked Charging Forklift 2 (FL-02 CFS 5.0T - Heavy Duty Port Yellow) at Slot 3 (North)
    const fl08 = createForkliftModel(0xf59e0b, false);
    fl08.position.set(-63.5, 0.08, 23 + 4.2);
    fl08.rotation.y = Math.PI;
    hubGroup.add(fl08);

    // 4. Active Forklift 3 (FL-03 CFS - Stuffing/Stripping Kargo Palet)
    // Operating actively in front of CFS Loading Dock Bay 1
    const fl03 = createForkliftModel(0xf59e0b, true);
    fl03.position.set(-65.5, 0.08, 7.5);
    fl03.rotation.y = -Math.PI / 1.15;
    hubGroup.add(fl03);

    // Billboard Label
    createBlockLabelSprite('CFS EQUIPMENT DEPOT (FORKLIFT CFS 3.5T - 5T)', -62, 5.8, 29);

    scene.add(hubGroup);
}

// =============================================================================
// TRUK KONTAINER PELABUHAN (PRIME MOVER + SASIS TRAILER 40FT + KONTAINER ISO)
// Parkir Mundur di Loading Dock Bay 03 CFS untuk Proses Stuffing/Stripping Kargo
// =============================================================================
function buildCFSContainerTruck() {
    const truckGroup = new THREE.Group();
    truckGroup.name = "CFSContainerTruck40FT";

    // Posisi parkir mundur di CFS Loading Dock Bay 3 (z = 16.0, pintu kontainer merapat ke dock bumper di x = -74.2)
    const dockZ = 16.0;
    const rearDockX = -74.1; // Ujung belakang kontainer merapat ke dock

    // Material Standar Pelabuhan Internasional
    const cabMat = new THREE.MeshStandardMaterial({ color: 0x0284c7, roughness: 0.35, metalness: 0.2 }); // Kabin Prime Mover Biru Maritim
    const chassisMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.7, metalness: 0.5 }); // Baja Sasis Gelap
    const containerMat = new THREE.MeshStandardMaterial({ color: 0x0369a1, roughness: 0.45, metalness: 0.1 }); // Kontainer ISO 40FT Maersk/Ocean Blue
    const glassMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.1, metalness: 0.8 });
    const wheelMat = new THREE.MeshStandardMaterial({ color: 0x0a0e17, roughness: 0.9 });
    const chromeMat = new THREE.MeshStandardMaterial({ color: 0xe2e8f0, metalness: 0.9, roughness: 0.1 });
    const cornerMat = new THREE.MeshStandardMaterial({ color: 0x334155, metalness: 0.7, roughness: 0.3 }); // ISO Corner Castings
    const lightMat = new THREE.MeshBasicMaterial({ color: 0xffffff });
    const yellowMat = new THREE.MeshBasicMaterial({ color: 0xf59e0b });

    // -------------------------------------------------------------------------
    // 1. Sasis Skeletal Semi-Trailer 40FT (Panjang 12.5m, Lebar 2.45m)
    // -------------------------------------------------------------------------
    const trailerLength = 12.5;
    const trailerCenter = rearDockX + trailerLength / 2; // -67.85

    const sasisBeam = new THREE.Mesh(new THREE.BoxGeometry(trailerLength, 0.35, 2.45), chassisMat);
    sasisBeam.position.set(trailerCenter, 1.25, dockZ);
    sasisBeam.castShadow = true;
    truckGroup.add(sasisBeam);

    // 4 Corner Twistlock Bolsters pada Sasis
    [-trailerLength / 2 + 0.3, trailerLength / 2 - 0.3].forEach(bx => {
        [-1.15, 1.15].forEach(bz => {
            const bolster = new THREE.Mesh(new THREE.BoxGeometry(0.35, 0.25, 0.35), chassisMat);
            bolster.position.set(trailerCenter + bx, 1.45, dockZ + bz);
            truckGroup.add(bolster);
        });
    });

    // Roda Gandar Ganda Belakang Sasis Trailer (8 Roda / Tandem Bogie Pelabuhan)
    [-trailerLength / 2 + 1.2, -trailerLength / 2 + 2.5].forEach(axX => {
        [-1.0, -1.22, 1.0, 1.22].forEach(wz => {
            const w = new THREE.Mesh(new THREE.CylinderGeometry(0.5, 0.5, 0.22, 20), wheelMat);
            w.rotation.x = Math.PI / 2;
            w.position.set(trailerCenter + axX, 0.5, dockZ + wz);
            w.castShadow = true;
            truckGroup.add(w);
        });
    });

    // -------------------------------------------------------------------------
    // 2. Kontainer ISO 40FT High Cube (12.2m x 2.6m x 2.44m)
    // -------------------------------------------------------------------------
    const cLen = 12.2, cH = 2.6, cW = 2.44;
    const cCenter = rearDockX + cLen / 2; // -68.0

    // Bodi Utama Kontainer
    const containerBox = new THREE.Mesh(new THREE.BoxGeometry(cLen, cH, cW), containerMat);
    containerBox.position.set(cCenter, 1.45 + cH / 2, dockZ);
    containerBox.castShadow = true;
    truckGroup.add(containerBox);

    // ISO Corner Castings (8 Sudut Baja Standar ISO 1161)
    [-cLen / 2 + 0.15, cLen / 2 - 0.15].forEach(cx => {
        [-cW / 2 + 0.15, cW / 2 - 0.15].forEach(cz => {
            [1.45 + 0.15, 1.45 + cH - 0.15].forEach(cy => {
                const corner = new THREE.Mesh(new THREE.BoxGeometry(0.32, 0.32, 0.32), cornerMat);
                corner.position.set(cCenter + cx, cy, dockZ + cz);
                truckGroup.add(corner);
            });
        });
    });

    // Pintu Belakang Kontainer (Menghadap Loading Dock Bay 03) dengan Locking Rods
    [-0.5, 0.5].forEach(rz => {
        const rod = new THREE.Mesh(new THREE.CylinderGeometry(0.035, 0.035, cH - 0.4, 8), chromeMat);
        rod.position.set(rearDockX + 0.05, 1.45 + cH / 2, dockZ + rz);
        truckGroup.add(rod);
    });

    // Tulang Gelombang Dinding Kontainer (Corrugated Ribs Visual Accent)
    for (let rx = -cLen / 2 + 0.8; rx <= cLen / 2 - 0.8; rx += 0.9) {
        [-cW / 2 - 0.02, cW / 2 + 0.02].forEach(rz => {
            const rib = new THREE.Mesh(new THREE.BoxGeometry(0.04, cH - 0.3, 0.06), containerMat);
            rib.position.set(cCenter + rx, 1.45 + cH / 2, dockZ + rz);
            truckGroup.add(rib);
        });
    }

    // -------------------------------------------------------------------------
    // 3. Kepala Truk / Prime Mover (Tractor Head Standar Terminal Pelabuhan)
    // -------------------------------------------------------------------------
    const cabX = trailerCenter + trailerLength / 2 + 1.2; // Menghadap ke Timur (+X)
    const cabGeo = new THREE.BoxGeometry(3.2, 2.6, 2.45);
    const cab = new THREE.Mesh(cabGeo, cabMat);
    cab.position.set(cabX, 2.25, dockZ);
    cab.castShadow = true;
    truckGroup.add(cab);

    // Aerodynamic Air Deflector di Atap Kabin
    const roofDeflector = new THREE.Mesh(new THREE.BoxGeometry(2.0, 0.55, 2.4), cabMat);
    roofDeflector.position.set(cabX - 0.2, 3.8, dockZ);
    truckGroup.add(roofDeflector);

    // Kaca Depan Windshield (Dark Tinted Glass)
    const ws = new THREE.Mesh(new THREE.BoxGeometry(0.3, 1.1, 2.3), glassMat);
    ws.position.set(cabX + 1.5, 2.8, dockZ);
    truckGroup.add(ws);

    // Kaca Samping
    [-1.23, 1.23].forEach(wz => {
        const sw = new THREE.Mesh(new THREE.BoxGeometry(1.2, 0.8, 0.05), glassMat);
        sw.position.set(cabX + 0.3, 2.75, dockZ + wz);
        truckGroup.add(sw);
    });

    // Gril Depan & Bemper Baja Berat
    const grille = new THREE.Mesh(new THREE.BoxGeometry(0.3, 1.4, 2.1), chassisMat);
    grille.position.set(cabX + 1.6, 1.7, dockZ);
    truckGroup.add(grille);

    const bumper = new THREE.Mesh(new THREE.BoxGeometry(0.35, 0.5, 2.5), chassisMat);
    bumper.position.set(cabX + 1.65, 0.85, dockZ);
    truckGroup.add(bumper);

    // Lampu Utama Depan (Headlights)
    [-0.85, 0.85].forEach(hz => {
        const hl = new THREE.Mesh(new THREE.BoxGeometry(0.15, 0.25, 0.45), lightMat);
        hl.position.set(cabX + 1.7, 1.25, dockZ + hz);
        truckGroup.add(hl);
    });

    // Spion Ganda (Side Mirrors)
    [-1.35, 1.35].forEach(mz => {
        const mirror = new THREE.Mesh(new THREE.BoxGeometry(0.15, 0.45, 0.2), chassisMat);
        mirror.position.set(cabX + 1.1, 2.7, dockZ + mz);
        truckGroup.add(mirror);
    });

    // Knalpot Vertikal Krom (Dual Chrome Exhaust Stacks)
    [-1.0, 1.0].forEach(ez => {
        const ex = new THREE.Mesh(new THREE.CylinderGeometry(0.09, 0.09, 2.8, 12), chromeMat);
        ex.position.set(cabX - 1.2, 3.0, dockZ + ez);
        truckGroup.add(ex);
    });

    // Tangki Solar Aluminium di Bawah Sasis Kabin
    [-1.15, 1.15].forEach(tz => {
        const tank = new THREE.Mesh(new THREE.CylinderGeometry(0.38, 0.38, 1.8, 16), chromeMat);
        tank.rotation.z = Math.PI / 2;
        tank.position.set(cabX - 0.8, 0.85, dockZ + tz);
        truckGroup.add(tank);
    });

    // Roda Gandar Depan & Gandar Penggerak Prime Mover (6 Roda)
    // Gandar Depan (Kemudi)
    [-1.05, 1.05].forEach(wz => {
        const fw = new THREE.Mesh(new THREE.CylinderGeometry(0.5, 0.5, 0.32, 20), wheelMat);
        fw.rotation.x = Math.PI / 2;
        fw.position.set(cabX + 1.0, 0.5, dockZ + wz);
        fw.castShadow = true;
        truckGroup.add(fw);
    });

    // Gandar Belakang Prime Mover (Drive Axle Ganda)
    [-1.0, -1.22, 1.0, 1.22].forEach(wz => {
        const rw = new THREE.Mesh(new THREE.CylinderGeometry(0.5, 0.5, 0.22, 20), wheelMat);
        rw.rotation.x = Math.PI / 2;
        rw.position.set(cabX - 0.9, 0.5, dockZ + wz);
        rw.castShadow = true;
        truckGroup.add(rw);
    });

    scene.add(truckGroup);
}

// Modern Stylized Low-Poly Trees and Greenery Belts
function buildStylizedTrees() {
    const treesGroup = new THREE.Group();
    treesGroup.name = "ModernStylizedTrees";

    function createTree(x, z, scale = 1.0) {
        const tree = new THREE.Group();

        // Trunk
        const trunkGeo = new THREE.CylinderGeometry(0.18 * scale, 0.26 * scale, 2.4 * scale, 8);
        const trunkMat = new THREE.MeshStandardMaterial({ color: 0x78350f, roughness: 0.9 });
        const trunk = new THREE.Mesh(trunkGeo, trunkMat);
        trunk.position.y = 1.2 * scale;
        trunk.castShadow = true;
        tree.add(trunk);

        // Foliage spheres (vibrant layered fresh greens)
        const greenMat1 = new THREE.MeshStandardMaterial({ color: 0x16a34a, roughness: 0.65 });
        const greenMat2 = new THREE.MeshStandardMaterial({ color: 0x22c55e, roughness: 0.6 });
        const greenMat3 = new THREE.MeshStandardMaterial({ color: 0x4ade80, roughness: 0.6 });

        const fol1 = new THREE.Mesh(new THREE.DodecahedronGeometry(1.35 * scale, 1), greenMat1);
        fol1.position.set(0, 2.6 * scale, 0);
        fol1.castShadow = true;
        tree.add(fol1);

        const fol2 = new THREE.Mesh(new THREE.DodecahedronGeometry(1.05 * scale, 1), greenMat2);
        fol2.position.set(0.25 * scale, 3.4 * scale, 0.15 * scale);
        fol2.castShadow = true;
        tree.add(fol2);

        const fol3 = new THREE.Mesh(new THREE.DodecahedronGeometry(0.75 * scale, 1), greenMat3);
        fol3.position.set(-0.2 * scale, 3.9 * scale, -0.15 * scale);
        fol3.castShadow = true;
        tree.add(fol3);

        tree.position.set(x, 0.1, z);
        return tree;
    }

    const treePositions = [
        // Along North Boulevard & Administration HQ Green Lawn
        { x: -35, z: 66, s: 1.1 },
        { x: -25, z: 68, s: 1.25 },
        { x: -12, z: 68, s: 1.0 },
        { x: 12,  z: 68, s: 1.15 },
        { x: 26,  z: 68, s: 1.3 },
        { x: 38,  z: 66, s: 1.05 },
        // Between Gate and Admin
        { x: -52, z: 68, s: 1.2 },
        { x: -62, z: 68, s: 0.95 },
        { x: -74, z: 66, s: 1.15 },
        // Near CFS Warehouse Entrance (West Green Verge)
        { x: 167, z: 20, s: 1.2 },
        { x: 167, z: 8,  s: 1.0 },
        { x: 167, z: -4, s: 1.1 },
        { x: 167, z: -16,s: 0.95 },
        // Near Customs Complex (East Green Verge)
        { x: -167, z: 24,  s: 1.1 },
        { x: -167, z: 12,  s: 1.2 },
        { x: -167, z: 0,   s: 1.05 },
        { x: -167, z: -12, s: 1.15 },
        // South Perimeter Green Belt
        { x: -90, z: -68, s: 1.2 },
        { x: -45, z: -68, s: 1.05 },
        { x: 0,   z: -68, s: 1.15 },
        { x: 45,  z: -68, s: 1.0 },
        { x: 90,  z: -68, s: 1.25 }
    ];

    treePositions.forEach(tp => {
        treesGroup.add(createTree(tp.x, tp.z, tp.s));
    });

    scene.add(treesGroup);
}

// 7. Customs KPPBC, 6 MeV Gantry X-Ray, Red Line Behandle Canopy & Quarantine
function buildCustomsAndBehandleZone() {
    const custGroup = new THREE.Group();
    custGroup.name = "CustomsAndBehandleZone";

    const __mcK0 = relocBegin(custGroup);
    // 1. Kantor Bea Cukai KPPBC (fac_f_kppbc)
    const kppW = 20, kppH = 6, kppD = 10;
    const kppGeo = new THREE.BoxGeometry(kppW, kppH, kppD);
    const kppMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.4 });
    const kpp = new THREE.Mesh(kppGeo, kppMat);
    kpp.position.set(92, kppH/2, 18);
    kpp.castShadow = true;
    custGroup.add(kpp);

    // Red Customs Fascia
    const fasGeo = new THREE.BoxGeometry(kppW + 0.2, 1.2, kppD + 0.2);
    const fasMat = new THREE.MeshStandardMaterial({ color: 0xdc2626 });
    const fas = new THREE.Mesh(fasGeo, fasMat);
    fas.position.set(92, 5.4, 18);
    custGroup.add(fas);

    createBlockLabelSprite('KANTOR BEA CUKAI (KPPBC CEISA 4.0)', 92, kppH + 2.5, 18);

    relocEnd(__mcK0, 'f_kppbc', 92, 18, true);
    const __mcK1 = relocBegin(custGroup);
    // 2. Gantry Container X-Ray 6 MeV Nuctech (fac_f_behandle_xray)
    const archMat = new THREE.MeshStandardMaterial({ color: 0x475569, metalness: 0.7, roughness: 0.2 });
    
    // 2 Lead Radiation Shield Towers
    const towerGeo = new THREE.BoxGeometry(3, 9, 4);
    const t1 = new THREE.Mesh(towerGeo, archMat);
    t1.position.set(92 - 6, 4.5, 2);
    custGroup.add(t1);

    const t2 = new THREE.Mesh(towerGeo, archMat);
    t2.position.set(92 + 6, 4.5, 2);
    custGroup.add(t2);

    // Overhead Bridge Emitter
    const bridgeGeo = new THREE.BoxGeometry(15, 2.2, 4);
    const bridge = new THREE.Mesh(bridgeGeo, archMat);
    bridge.position.set(92, 9.5, 2);
    custGroup.add(bridge);

    // Red Radiation Warning Beacons on X-Ray
    const beaconGeo = new THREE.SphereGeometry(0.3, 8, 8);
    const beaconMat = new THREE.MeshBasicMaterial({ color: 0xef4444 });
    [-6, 6].forEach(bx => {
        const b = new THREE.Mesh(beaconGeo, beaconMat);
        b.position.set(92 + bx, 9.5, 2);
        custGroup.add(b);
    });

    createBlockLabelSprite('GANTRY CONTAINER X-RAY 6 MeV (NUCTECH)', 92, 12.0, 2);

    relocEnd(__mcK1, 'f_behandle_xray', 92, 2, true);
    const __mcK2 = relocBegin(custGroup);
    // 3. Behandle Physical Inspection Canopy (Jalur Merah)
    const canopyRoofGeo = new THREE.BoxGeometry(18, 0.4, 14);
    const canopyRoofMat = new THREE.MeshStandardMaterial({ color: 0x94a3b8, roughness: 0.4 });
    const canopyRoof = new THREE.Mesh(canopyRoofGeo, canopyRoofMat);
    canopyRoof.position.set(92, 7.5, -10);
    custGroup.add(canopyRoof);

    // 4 Steel Pillars
    const pillGeo = new THREE.CylinderGeometry(0.25, 0.25, 7.5, 8);
    const pillMat = new THREE.MeshStandardMaterial({ color: 0x0284c7 });
    [
        { x: 92 - 8, z: -10 - 6 }, { x: 92 + 8, z: -10 - 6 },
        { x: 92 - 8, z: -10 + 6 }, { x: 92 + 8, z: -10 + 6 }
    ].forEach(pp => {
        const p = new THREE.Mesh(pillGeo, pillMat);
        p.position.set(pp.x, 3.75, pp.z);
        custGroup.add(p);
    });

    // Elevated Inspection Ramp
    const rampGeo = new THREE.BoxGeometry(16, 1.2, 10);
    const rampMat = new THREE.MeshStandardMaterial({ color: 0x64748b, roughness: 0.8 });
    const ramp = new THREE.Mesh(rampGeo, rampMat);
    ramp.position.set(92, 0.6, -10);
    custGroup.add(ramp);

    createBlockLabelSprite('BEHANDLE PHYSICAL INSPECTION (JALUR MERAH)', 92, 9.5, -10);

    relocEnd(__mcK2, 'f_behandle_area', 92, -10, true);
    const __mcK3 = relocBegin(custGroup);
    // 4. Quarantine Inspection Room (fac_f_quarantine)
    const quarGeo = new THREE.BoxGeometry(18, 5, 8);
    const quarMat = new THREE.MeshStandardMaterial({ color: 0x0f766e, roughness: 0.4 });
    const quar = new THREE.Mesh(quarGeo, quarMat);
    quar.position.set(92, 2.5, -22);
    quar.castShadow = true;
    custGroup.add(quar);

    createBlockLabelSprite('QUARANTINE INSPECTION ROOM (KEMENTAN)', 92, 6.0, -22);

    relocEnd(__mcK3, 'f_quarantine', 92, -22, true);
    scene.add(custGroup);
}

// 8. Perimeter Boundary Security Fence & 4 Watchtowers
function buildPerimeterAndSecurity() {
    const perimGroup = new THREE.Group();
    perimGroup.name = "PerimeterAndSecurity";

    // Perimeter Boundary Fence Line (dari layout master: X ±fence_half_w, Z ±fence_half_d)
    const fenceMat = new THREE.MeshBasicMaterial({ color: 0x64748b, wireframe: true });
    
    // 4 Fence Segments (Height 2.5m)
    const northFence = new THREE.Mesh(new THREE.BoxGeometry(CIDP_LAYOUT.meta.fence_half_w * 2, 2.5, 0.1), fenceMat);
    northFence.position.set(0, 1.25, CIDP_LAYOUT.meta.fence_half_d);
    perimGroup.add(northFence);

    const southFence = new THREE.Mesh(new THREE.BoxGeometry(CIDP_LAYOUT.meta.fence_half_w * 2, 2.5, 0.1), fenceMat);
    southFence.position.set(0, 1.25, -CIDP_LAYOUT.meta.fence_half_d);
    perimGroup.add(southFence);

    const westFence = new THREE.Mesh(new THREE.BoxGeometry(0.1, 2.5, CIDP_LAYOUT.meta.fence_half_d * 2), fenceMat);
    westFence.position.set(-CIDP_LAYOUT.meta.fence_half_w, 1.25, 0);
    perimGroup.add(westFence);

    const eastFence = new THREE.Mesh(new THREE.BoxGeometry(0.1, 2.5, CIDP_LAYOUT.meta.fence_half_d * 2), fenceMat);
    eastFence.position.set(CIDP_LAYOUT.meta.fence_half_w, 1.25, 0);
    perimGroup.add(eastFence);

    // 4 Corner Security Watchtowers with Searchlights
    const towerCoords = [
        { x: -(CIDP_LAYOUT.meta.fence_half_w - 2), z: CIDP_LAYOUT.meta.fence_half_d - 2 },
        { x:  (CIDP_LAYOUT.meta.fence_half_w - 2), z: CIDP_LAYOUT.meta.fence_half_d - 2 },
        { x: -(CIDP_LAYOUT.meta.fence_half_w - 2), z: -(CIDP_LAYOUT.meta.fence_half_d - 2) },
        { x:  (CIDP_LAYOUT.meta.fence_half_w - 2), z: -(CIDP_LAYOUT.meta.fence_half_d - 2) }
    ];

    towerCoords.forEach(tc => {
        // Steel Legs
        const legGeo = new THREE.CylinderGeometry(0.15, 0.35, 12, 6);
        const legMat = new THREE.MeshStandardMaterial({ color: 0x475569, metalness: 0.7 });
        [-0.8, 0.8].forEach(dx => {
            [-0.8, 0.8].forEach(dz => {
                const leg = new THREE.Mesh(legGeo, legMat);
                leg.position.set(tc.x + dx, 6, tc.z + dz);
                perimGroup.add(leg);
            });
        });

        // Cabin
        const cabGeo = new THREE.BoxGeometry(2.4, 2.2, 2.4);
        const cabMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.3 });
        const cab = new THREE.Mesh(cabGeo, cabMat);
        cab.position.set(tc.x, 13.1, tc.z);
        perimGroup.add(cab);

        // Searchlight
        const searchGeo = new THREE.SphereGeometry(0.3, 8, 8);
        const searchMat = new THREE.MeshBasicMaterial({ color: 0xfef08a });
        const search = new THREE.Mesh(searchGeo, searchMat);
        search.position.set(tc.x, 14.5, tc.z);
        perimGroup.add(search);
    });

    scene.add(perimGroup);
}

// 9. Engineering CAD 3D Dimension Leader Lines & Benchmark Datum
function buildEngineeringDimensionLeaders() {
    technicalDimensionsGroup = new THREE.Group();
    technicalDimensionsGroup.name = "EngineeringDimensionLeaders";
    scene.add(technicalDimensionsGroup);

    // 1. Grid Aksis As Teknik Sipil (Grid Bubbles AS 1-5 dan AS A-E) -> layer 'gridAxes'
    buildCivilGridAxesSystem();

    // 2. ISO Dimension Leaders dengan tick 45 derajat
    createCADLeaderDimension(-CIDP_LAYOUT.meta.rail_length / 2, CIDP_LAYOUT.meta.rail_length / 2, -60, `STA 0+000 s/d 0+${CIDP_LAYOUT.meta.rail_length} (${CIDP_LAYOUT.meta.rail_length}.00 M RAIL SIDING TERMODEL)`, 0xd97706);
    createCADLeaderDimension(-80, 80, 20, '160.00 M CORE STACKING YARD (BLOK A-D)', 0x4f46e5);

    // 3. Dimensi lain (CFS & jalan haul) tetap dipertahankan
    createDimensionLineZ(26, -27, LW('f_cfs').x + 16, '← 53.00 M CFS & M&R LOGISTICS COMPLEX →', 0x0284c7);
    createDimensionLine(-52, -38, 28, '← 14.00 M TWO-WAY CONTAINER TRUCK ROAD →', 0x10b981);

    // 4. Elevation marks (duga)
    createCADLeaderElevation(0, 0.16, 28, 'EL +0.16 M (TOP SLAB PEKERASAN)');
    createCADLeaderElevation(LW('blok_b').x, 10.6, 16, 'EL +10.60 M (MAX TIER 4 HOIST LIMIT)');

    // 5. Benchmark Datum Geodetik
    createDatumBenchmarkMarker(-110, 0.5, 62);

    technicalDimensionsGroup.visible = cadLayers.dimensions && showTechnicalDimensions;
}

// Sistem Aksis Grid Arsitektur & Sipil (Grid Bubbles)
// Memakai cadGridGroup yang sudah dibuat di buildCivilPavementsAndRoadNetwork() (berisi GridHelper),
// sehingga layer 'gridAxes' mengontrol grid + as sekaligus, terpisah dari layer 'dimensions'.
function buildCivilGridAxesSystem() {
    if (!cadGridGroup) {
        cadGridGroup = new THREE.Group();
        cadGridGroup.name = "CADGridAxes";
        scene.add(cadGridGroup);
    }

    const xAxes = [
        // AS 1..7 berurut BARAT -> TIMUR (kiri -> kanan layar, sama dengan denah)
        { x: LW('f_cfs').x,                               label: 'AS 1' },
        { x: LW('f_empty_depot').x,                       label: 'AS 2' },
        { x: LW('blok_a').x,                              label: 'AS 3' },
        { x: 0,                                           label: 'AS 4' },
        { x: LW('blok_b').x,                              label: 'AS 5' },
        { x: LW('f_reefer_racks').x,                      label: 'AS 6' },
        { x: LW('f_kppbc').x,                             label: 'AS 7' }
    ];

    const zAxes = [
        { z: 52,   label: 'AS A' },
        { z: 16,   label: 'AS B' },
        { z: -5,   label: 'AS C' },
        { z: -26,  label: 'AS D' },
        { z: -52,  label: 'AS E' }
    ];

    // Material dipakai bersama (satu instance) untuk semua garis as
    const lineMat = new THREE.LineDashedMaterial({
        color: 0x0284c7,
        dashSize: 3,
        gapSize: 2
    });

    // Garis As Vertikal (sejajar sumbu Z)
    xAxes.forEach(ax => {
        const geom = new THREE.BufferGeometry().setFromPoints([
            new THREE.Vector3(ax.x, 0.3, 72),
            new THREE.Vector3(ax.x, 0.3, -72)
        ]);
        const line = new THREE.Line(geom, lineMat);
        line.computeLineDistances();
        cadGridGroup.add(line);
        createGridBubbleSprite(ax.label, ax.x, 1.2, 75);
    });

    // Garis As Horisontal (sejajar sumbu X)
    zAxes.forEach(az => {
        const geom = new THREE.BufferGeometry().setFromPoints([
            new THREE.Vector3(-CIDP_LAYOUT.meta.fence_half_w, 0.3, az.z),
            new THREE.Vector3(CIDP_LAYOUT.meta.fence_half_w, 0.3, az.z)
        ]);
        const line = new THREE.Line(geom, lineMat);
        line.computeLineDistances();
        cadGridGroup.add(line);
        createGridBubbleSprite(az.label, CIDP_LAYOUT.meta.fence_half_w + 4, 1.2, az.z);   // label di sisi barat (kiri layar)
    });

    cadGridGroup.visible = cadLayers.gridAxes;
}

// Lingkaran As Simbol Gambar Kerja (CAD Grid Head Bubble)
function createGridBubbleSprite(text, x, y, z) {
    const canvas = document.createElement('canvas');
    canvas.width = 128;
    canvas.height = 128;
    const ctx = canvas.getContext('2d');

    ctx.beginPath();
    ctx.arc(64, 64, 56, 0, Math.PI * 2);
    ctx.fillStyle = '#0284c7';
    ctx.fill();
    ctx.lineWidth = 6;
    ctx.strokeStyle = '#ffffff';
    ctx.stroke();

    ctx.font = 'bold 36px monospace';
    ctx.fillStyle = '#ffffff';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(text, 64, 64);

    const sprite = new THREE.Sprite(new THREE.SpriteMaterial({ map: new THREE.CanvasTexture(canvas), transparent: true }));
    sprite.position.set(x, y, z);
    sprite.scale.set(4, 4, 1);
    cadGridGroup.add(sprite);
}

// Garis Ukur Dimensi CAD ISO (tick 45 derajat arsitektural + extension lines)
function createCADLeaderDimension(startX, endX, zPos, labelText, colorHex) {
    const lineMat = new THREE.LineBasicMaterial({ color: colorHex || 0x38bdf8 });
    const add = (pts) => technicalDimensionsGroup.add(new THREE.Line(new THREE.BufferGeometry().setFromPoints(pts), lineMat));

    // Garis ukur utama
    add([new THREE.Vector3(startX, 1.2, zPos), new THREE.Vector3(endX, 1.2, zPos)]);

    [startX, endX].forEach(x => {
        // Tick 45 derajat
        add([new THREE.Vector3(x - 0.5, 0.7, zPos), new THREE.Vector3(x + 0.5, 1.7, zPos)]);
        // Extension line tegak ke permukaan perkerasan
        add([new THREE.Vector3(x, 0.15, zPos), new THREE.Vector3(x, 2.0, zPos)]);
    });

    createBlockLabelSprite(labelText, (startX + endX) / 2, 2.8, zPos, technicalDimensionsGroup);
}

// Simbol Ketinggian Duga (Elevation Level Mark): garis leader vertikal + label
function createCADLeaderElevation(x, y, z, text) {
    const lineMat = new THREE.LineBasicMaterial({ color: 0xf59e0b });
    technicalDimensionsGroup.add(new THREE.Line(
        new THREE.BufferGeometry().setFromPoints([new THREE.Vector3(x, y, z), new THREE.Vector3(x, y + 2.2, z)]),
        lineMat
    ));
    createBlockLabelSprite(`▼ ${text}`, x, y + 3.2, z, technicalDimensionsGroup);
}

function createDimensionLine(startX, endX, zPos, labelText, colorHex) {
    const lineMat = new THREE.LineBasicMaterial({ color: colorHex, linewidth: 2 });
    
    // Main dimension line
    const points = [
        new THREE.Vector3(startX, 1.5, zPos),
        new THREE.Vector3(endX, 1.5, zPos)
    ];
    const geom = new THREE.BufferGeometry().setFromPoints(points);
    const line = new THREE.Line(geom, lineMat);
    technicalDimensionsGroup.add(line);

    // End tick marks
    [-1, 1].forEach(side => {
        const x = side === -1 ? startX : endX;
        const tickPoints = [
            new THREE.Vector3(x, 0.5, zPos),
            new THREE.Vector3(x, 2.5, zPos)
        ];
        const tickGeom = new THREE.BufferGeometry().setFromPoints(tickPoints);
        const tick = new THREE.Line(tickGeom, lineMat);
        technicalDimensionsGroup.add(tick);
    });

    // Label Sprite
    createBlockLabelSprite(labelText, (startX + endX) / 2, 3.2, zPos, technicalDimensionsGroup);
}

function createDimensionLineZ(startZ, endZ, xPos, labelText, colorHex) {
    const lineMat = new THREE.LineBasicMaterial({ color: colorHex, linewidth: 2 });
    
    const points = [
        new THREE.Vector3(xPos, 1.5, startZ),
        new THREE.Vector3(xPos, 1.5, endZ)
    ];
    const geom = new THREE.BufferGeometry().setFromPoints(points);
    const line = new THREE.Line(geom, lineMat);
    technicalDimensionsGroup.add(line);

    [-1, 1].forEach(side => {
        const z = side === -1 ? startZ : endZ;
        const tickPoints = [
            new THREE.Vector3(xPos, 0.5, z),
            new THREE.Vector3(xPos, 2.5, z)
        ];
        const tickGeom = new THREE.BufferGeometry().setFromPoints(tickPoints);
        const tick = new THREE.Line(tickGeom, lineMat);
        technicalDimensionsGroup.add(tick);
    });

    createBlockLabelSprite(labelText, xPos, 3.2, (startZ + endZ) / 2, technicalDimensionsGroup);
}

function createDatumBenchmarkMarker(x, y, z) {
    const markerGeo = new THREE.CylinderGeometry(0.8, 1.0, 0.4, 16);
    const markerMat = new THREE.MeshStandardMaterial({ color: 0xf59e0b, roughness: 0.3 });
    const marker = new THREE.Mesh(markerGeo, markerMat);
    marker.position.set(x, y, z);
    technicalDimensionsGroup.add(marker);

    createBlockLabelSprite('DATUM BENCHMARK: EL +0.00 M (MSL)', x, y + 2.5, z, technicalDimensionsGroup);
}

// Pengendali Layer Dinamis CAD/BIM (satu pintu untuk semua layer, termasuk tombol UI)
function toggleCADLayer(layerName) {
    if (cadLayers[layerName] === undefined) return;
    const next = !cadLayers[layerName];

    if (layerName === 'heavyEquipment') {
        cadLayers.heavyEquipment = next;
        Object.values(equipmentMeshes || {}).forEach(m => { if (m) m.visible = next; });
    } else {
        setCadLayer(layerName, next); // pavement, drainage, dimensions, gridAxes
    }

    if (layerName === 'dimensions') {
        // jaga sinkron dengan tombol toolbar yang sudah ada
        const btn = document.getElementById('btnToggleDimensions');
        if (btn) {
            btn.classList.toggle('bg-blue-50', next);
            btn.classList.toggle('text-blue-700', next);
            btn.classList.toggle('border-blue-200', next);
            btn.classList.toggle('font-bold', next);
            btn.classList.toggle('bg-gray-100', !next);
            btn.classList.toggle('text-gray-700', !next);
            btn.classList.toggle('border-gray-200', !next);
        }
    }

    const chip = document.getElementById('cadLayerBtn-' + layerName);
    if (chip) {
        chip.classList.toggle('bg-sky-50', next);
        chip.classList.toggle('text-sky-700', next);
        chip.classList.toggle('border-sky-200', next);
        chip.classList.toggle('bg-gray-100', !next);
        chip.classList.toggle('text-gray-500', !next);
        chip.classList.toggle('border-gray-200', !next);
    }

    showToast('Layer CAD Diperbarui', `Layer ${layerName}: ${next ? 'Aktif' : 'Nonaktif'}`, 'info');
}

// 10. Yard Floodlight High Towers
function buildFloodlightTowers() {
    const towerCoords = [
        { x: -75, z: -35 },
        { x: 75,  z: -35 },
        { x: -75, z: 45 },
        { x: 75,  z: 45 }
    ];

    towerCoords.forEach((tc) => {
        const mastGeo = new THREE.CylinderGeometry(0.4, 0.8, 24, 8);
        const mastMat = new THREE.MeshStandardMaterial({ color: 0x94a3b8, metalness: 0.8 });
        const mast = new THREE.Mesh(mastGeo, mastMat);
        mast.position.set(tc.x, 12, tc.z);
        scene.add(mast);

        // Head Frame with Lamps
        const headGeo = new THREE.BoxGeometry(4, 1, 2);
        const head = new THREE.Mesh(headGeo, mastMat);
        head.position.set(tc.x, 24, tc.z);
        scene.add(head);

        // Spotlights
        const spot = new THREE.SpotLight(0xfffaed, 0, 140, Math.PI / 3, 0.4, 1);
        spot.position.set(tc.x, 23.8, tc.z);
        spot.target.position.set(tc.x > 0 ? 20 : -20, 0, 0);
        scene.add(spot);
        scene.add(spot.target);
        floodlightLights.push(spot);
    });
}

// 11. Spawn Heavy Equipment (3 Reach Stackers + 1 RTG Crane)
function spawnEquipmentModels() {
    // RS-01 (Blok A Transfer Corridor - Primary Drop-Off Handler)
    equipmentMeshes['RS-01'] = createReachStackerModel('RS-01', 34, 0, 28);
    
    // RS-02 (Blok B Transfer Corridor - Primary Pick-Up Handler)
    equipmentMeshes['RS-02'] = createReachStackerModel('RS-02', -54, 0, 28);

    // RS-03 (Reefer & DG Area Transfer Corridor)
    equipmentMeshes['RS-03'] = createReachStackerModel('RS-03', -104, 0, 28);

    // RTG-01 (Rail Siding Crane spanning across track and buffer)
    equipmentMeshes['RTG-01'] = createRTGCraneModel('RTG-01', 0, 0, -52);
}

// Procedural 3D Reach Stacker Model (Kalmar DRG450)
function createReachStackerModel(name, x, y, z) {
    const rs = new THREE.Group();
    rs.name = name;

    // 1. Chassis Body (Kalmar Industrial Red & Dark Grey)
    const chassisGeo = new THREE.BoxGeometry(8.2, 2.2, 3.8);
    const chassisMat = new THREE.MeshStandardMaterial({ color: 0xd9232a, roughness: 0.4 });
    const chassis = new THREE.Mesh(chassisGeo, chassisMat);
    chassis.position.set(0, 1.8, 0);
    chassis.castShadow = true;
    rs.add(chassis);

    // Front Drive Axle (Huge Dual Heavy-Duty Tyres, Radius 0.8m)
    const fWheelGeo = new THREE.CylinderGeometry(0.8, 0.8, 0.65, 24);
    const wMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.9 });
    [-1.9, 1.9].forEach(wz => {
        const fw = new THREE.Mesh(fWheelGeo, wMat);
        fw.rotation.x = Math.PI / 2;
        fw.position.set(2.8, 0.8, wz);
        fw.castShadow = true;
        rs.add(fw);
    });

    // Rear Steer Axle (Single Tyres, Radius 0.65m)
    const rWheelGeo = new THREE.CylinderGeometry(0.65, 0.65, 0.5, 24);
    [-1.7, 1.7].forEach(wz => {
        const rw = new THREE.Mesh(rWheelGeo, wMat);
        rw.rotation.x = Math.PI / 2;
        rw.position.set(-2.8, 0.65, wz);
        rw.castShadow = true;
        rs.add(rw);
    });

    // Rear Heavy Ballast Counterweight
    const cwGeo = new THREE.BoxGeometry(2.2, 2.5, 3.6);
    const cwMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.5 });
    const cw = new THREE.Mesh(cwGeo, cwMat);
    cw.position.set(-3.1, 2.3, 0);
    rs.add(cw);

    // Operator Glass Cabin (Elevated Vision Canopy)
    const cabGeo = new THREE.BoxGeometry(2.2, 2.0, 2.0);
    const cabMat = new THREE.MeshStandardMaterial({ color: 0x38bdf8, roughness: 0.1, transparent: true, opacity: 0.75 });
    const cab = new THREE.Mesh(cabGeo, cabMat);
    cab.position.set(-1.0, 3.8, 0);
    rs.add(cab);

    // Silinder Hidrolik Bertingkat: barrel (badan) + piston chrome teleskopik
    const cylMat = new THREE.MeshStandardMaterial({ color: 0xe2e8f0, metalness: 0.95, roughness: 0.1 });
    [-0.7, 0.7].forEach(cz => {
        const barrel = new THREE.Mesh(new THREE.CylinderGeometry(0.24, 0.24, 3.5, 16), chassisMat);
        barrel.position.set(0.4, 2.8, cz);
        barrel.rotation.z = -Math.PI / 6;
        rs.add(barrel);

        const piston = new THREE.Mesh(new THREE.CylinderGeometry(0.16, 0.16, 3.0, 16), cylMat);
        piston.position.set(1.4, 3.8, cz);
        piston.rotation.z = -Math.PI / 6;
        rs.add(piston);
    });

    // Lampu Strobo Putar K3 Pelabuhan (berkedip amber, material per unit agar tidak berbagi warna)
    const strobe = new THREE.Mesh(
        new THREE.CylinderGeometry(0.14, 0.14, 0.25, 12),
        new THREE.MeshBasicMaterial({ color: 0xf59e0b })
    );
    strobe.position.set(-1.0, 4.95, 0);
    strobe.name = "hazardStrobe";
    rs.add(strobe);
    registerHazardLight(strobe, null, 0, true);

    // Main Telescopic Boom
    const boomGeo = new THREE.BoxGeometry(9.5, 1.2, 1.2);
    const boomMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, metalness: 0.7, roughness: 0.3 });
    const boom = new THREE.Mesh(boomGeo, boomMat);
    boom.name = "rsBoom";
    boom.position.set(2.8, 5.2, 0);
    boom.rotation.z = Math.PI / 10;
    boom.castShadow = true;
    rs.add(boom);

    // Real-World 40FT Bromma / Elme Telescopic Spreader Bar (Length 12.0m, Width 2.44m)
    const spreaderGeo = new THREE.BoxGeometry(12.0, 0.6, 2.44);
    const spreaderMat = new THREE.MeshStandardMaterial({ color: 0xf59e0b, roughness: 0.4 });
    const spreader = new THREE.Mesh(spreaderGeo, spreaderMat);
    spreader.name = "rsSpreader";
    spreader.position.set(7.5, 6.0, 0);
    spreader.castShadow = true;
    rs.add(spreader);

    // 4 Corner Guide Flippers (Yellow triangular alignment guides on corners)
    const flipperGeo = new THREE.BoxGeometry(0.3, 0.8, 0.3);
    const flipperMat = new THREE.MeshBasicMaterial({ color: 0xeab308 });
    [
        { x: -5.9, z: -1.2 }, { x: 5.9, z: -1.2 },
        { x: -5.9, z: 1.2 },  { x: 5.9, z: 1.2 }
    ].forEach(fp => {
        const f = new THREE.Mesh(flipperGeo, flipperMat);
        f.position.set(fp.x, -0.4, fp.z);
        spreader.add(f);
    });

    rs.position.set(x, y, z);
    scene.add(rs);
    return rs;
}

// Procedural 3D RTG Crane Model (Konecranes RTG)
function createRTGCraneModel(name, x, y, z) {
    const rtg = new THREE.Group();
    rtg.name = name;

    const rtgMat = new THREE.MeshStandardMaterial({ color: 0x0170b9, roughness: 0.4 });
    const tyreMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.9 });

    // 4 Tall Gantry Legs
    const legGeo = new THREE.BoxGeometry(1.2, 18, 1.2);
    const legPositions = [
        { x: -10, z: -7 }, { x: 10, z: -7 },
        { x: -10, z: 7 },  { x: 10, z: 7 }
    ];
    legPositions.forEach(lp => {
        const leg = new THREE.Mesh(legGeo, rtgMat);
        leg.position.set(lp.x, 9, lp.z);
        leg.castShadow = true;
        rtg.add(leg);

        // Tyres
        const tGeo = new THREE.CylinderGeometry(1.1, 1.1, 0.8, 16);
        const tyre = new THREE.Mesh(tGeo, tyreMat);
        tyre.rotation.x = Math.PI / 2;
        tyre.position.set(lp.x, 1.1, lp.z);
        rtg.add(tyre);
    });

    // Top Crossbeams (Span over track & yard)
    const beamGeo = new THREE.BoxGeometry(22, 1.6, 1.6);
    const b1 = new THREE.Mesh(beamGeo, rtgMat);
    b1.position.set(0, 18, -7);
    rtg.add(b1);

    const b2 = new THREE.Mesh(beamGeo, rtgMat);
    b2.position.set(0, 18, 7);
    rtg.add(b2);

    // Overhead Traversing Trolley & Hoist Cables
    const trolleyGeo = new THREE.BoxGeometry(4, 1.2, 15);
    const trolleyMat = new THREE.MeshStandardMaterial({ color: 0xf59e0b });
    const trolley = new THREE.Mesh(trolleyGeo, trolleyMat);
    trolley.name = "rtgTrolley";
    trolley.position.set(0, 18.5, 0);
    rtg.add(trolley);

    // Spreader Suspended
    const rtgSpreaderGeo = new THREE.BoxGeometry(6.5, 0.6, 2.5);
    const rtgSpreader = new THREE.Mesh(rtgSpreaderGeo, trolleyMat);
    rtgSpreader.name = "rtgSpreader";
    rtgSpreader.position.set(0, 12, 0);
    rtgSpreader.castShadow = true;
    rtg.add(rtgSpreader);

    rtg.position.set(x, y, z);
    scene.add(rtg);
    return rtg;
}

// 12. Procedural 3D Truck Prime Mover (Realistic Scale-Accurate 40FT Semi-Trailer)
function spawnTruckModel() {
    truckMesh = new THREE.Group();
    truckMesh.name = "PrimeMoverContainerTruck";

    // --- A. Prime Mover Cabin (Head Truck: Length 3.2m, Width 2.45m, Height 2.8m) ---
    const cabGeo = new THREE.BoxGeometry(3.2, 2.6, 2.45);
    const cabMat = new THREE.MeshStandardMaterial({ color: 0x0284c7, roughness: 0.35, metalness: 0.15 });
    const cab = new THREE.Mesh(cabGeo, cabMat);
    cab.position.set(6.2, 2.25, 0);
    cab.castShadow = true;
    truckMesh.add(cab);

    // Aerodynamic Roof Air Deflector
    const roofGeo = new THREE.BoxGeometry(2.0, 0.55, 2.4);
    const roof = new THREE.Mesh(roofGeo, cabMat);
    roof.position.set(6.0, 3.8, 0);
    truckMesh.add(roof);

    // Front Chrome Radiator Grille
    const grilleGeo = new THREE.BoxGeometry(0.3, 1.4, 2.1);
    const grilleMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.3, metalness: 0.7 });
    const grille = new THREE.Mesh(grilleGeo, grilleMat);
    grille.position.set(7.8, 1.7, 0);
    truckMesh.add(grille);

    // Windshield (Dark Tinted Glass)
    const wsGeo = new THREE.BoxGeometry(0.3, 1.1, 2.3);
    const wsMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.1, transparent: true, opacity: 0.85 });
    const ws = new THREE.Mesh(wsGeo, wsMat);
    ws.position.set(7.7, 2.8, 0);
    truckMesh.add(ws);

    // Dual Front Headlights
    const headGeo = new THREE.BoxGeometry(0.2, 0.25, 0.45);
    const headMat = new THREE.MeshBasicMaterial({ color: 0xfef08a });
    [-0.85, 0.85].forEach(hz => {
        const hl = new THREE.Mesh(headGeo, headMat);
        hl.position.set(7.85, 1.25, hz);
        truckMesh.add(hl);
    });

    // Dual Vertical Chrome Exhaust Stacks
    const exGeo = new THREE.CylinderGeometry(0.1, 0.1, 2.8, 12);
    const exMat = new THREE.MeshStandardMaterial({ color: 0xe2e8f0, metalness: 0.9, roughness: 0.1 });
    [-1.0, 1.0].forEach(ez => {
        const ex = new THREE.Mesh(exGeo, exMat);
        ex.position.set(4.5, 3.0, ez);
        truckMesh.add(ex);
    });

    // Fuel Tanks (Aluminum Cylinders under chassis)
    const tankGeo = new THREE.CylinderGeometry(0.38, 0.38, 1.8, 16);
    const tankMat = new THREE.MeshStandardMaterial({ color: 0x94a3b8, metalness: 0.7 });
    [-1.15, 1.15].forEach(tz => {
        const tank = new THREE.Mesh(tankGeo, tankMat);
        tank.rotation.z = Math.PI / 2;
        tank.position.set(4.8, 0.85, tz);
        truckMesh.add(tank);
    });

    // --- B. 40FT Skeletal Semi-Trailer Chassis (Length 12.5m, Width 2.45m, Height 0.35m) ---
    const trailerGeo = new THREE.BoxGeometry(12.5, 0.35, 2.45);
    const trailerMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, metalness: 0.6, roughness: 0.4 });
    const trailer = new THREE.Mesh(trailerGeo, trailerMat);
    trailer.position.set(-1.0, 1.25, 0);
    trailer.castShadow = true;
    truckMesh.add(trailer);

    // Fifth-Wheel Coupler Turntable (Connecting Tractor & Trailer)
    const fifthGeo = new THREE.CylinderGeometry(0.48, 0.48, 0.15, 16);
    const fifthMat = new THREE.MeshStandardMaterial({ color: 0x334155, metalness: 0.8 });
    const fifth = new THREE.Mesh(fifthGeo, fifthMat);
    fifth.position.set(3.6, 1.15, 0);
    truckMesh.add(fifth);

    // 4 Corner Twistlock Bolster Castings on Trailer Deck
    const bolsterGeo = new THREE.BoxGeometry(0.35, 0.25, 0.35);
    const bolsterMat = new THREE.MeshStandardMaterial({ color: 0xf59e0b, roughness: 0.3 });
    [
        { x: -6.9, z: -1.15 }, { x: 4.9, z: -1.15 },
        { x: -6.9, z: 1.15 },  { x: 4.9, z: 1.15 }
    ].forEach(bp => {
        const b = new THREE.Mesh(bolsterGeo, bolsterMat);
        b.position.set(bp.x, 1.45, bp.z);
        truckMesh.add(b);
    });

    // Rear Underrun Protection Bumper & Red Tail Lights
    const bumpGeo = new THREE.BoxGeometry(0.2, 0.28, 2.4);
    const bumpMat = new THREE.MeshStandardMaterial({ color: 0xdc2626 });
    const bumper = new THREE.Mesh(bumpGeo, bumpMat);
    bumper.position.set(-7.3, 0.75, 0);
    truckMesh.add(bumper);

    // --- C. Real-World Wheels (Radius 0.52m, Tyre Width 0.35m) ---
    const wGeo = new THREE.CylinderGeometry(0.52, 0.52, 0.35, 20);
    const wMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.85 });
    const rimGeo = new THREE.CylinderGeometry(0.3, 0.3, 0.36, 16);
    const rimMat = new THREE.MeshStandardMaterial({ color: 0x94a3b8, metalness: 0.8 });

    // Axles:
    // Tractor Steer: X = 7.0 | Tractor Tandem Drive: X = 4.2, 2.9
    // Trailer Tri-Axle Bogie: X = -4.2, -5.5, -6.8
    const wheelXPositions = [7.0, 4.2, 2.9, -4.2, -5.5, -6.8];
    wheelXPositions.forEach(wx => {
        [-1.15, 1.15].forEach(wz => {
            const wheel = new THREE.Mesh(wGeo, wMat);
            wheel.rotation.x = Math.PI / 2;
            wheel.position.set(wx, 0.52, wz);
            wheel.castShadow = true;

            const rim = new THREE.Mesh(rimGeo, rimMat);
            wheel.add(rim);

            truckMesh.add(wheel);
        });
    });

    // --- D. 40FT ISO 6346 Container (Length 12.0m, Width 2.44m, Height 2.6m) ---
    // Sits precisely on trailer deck at Y = 1.35 + 1.30 = 2.65m
    const boxGeo = new THREE.BoxGeometry(12.0, 2.6, 2.44);
    const boxMat = new THREE.MeshStandardMaterial({ color: 0xd49b00, roughness: 0.45, metalness: 0.15 });
    const box = new THREE.Mesh(boxGeo, boxMat);
    box.position.set(-1.0, 2.65, 0);
    box.castShadow = true;
    truckMesh.add(box);
    truckBoxMesh = box; // Assign to global truckBoxMesh

    // Standby Highway Position (Lane 1 Approach)
    truckMesh.position.set(-49, 0, 75);
    truckMesh.rotation.y = Math.PI / 2; // Facing north toward Gate-In
    scene.add(truckMesh);
}

// 13. 3D Holographic Target Beacon & Ground Rings
let targetMarkerGroup = null;
let targetBeaconLight = null;

function createTargetMarker() {
    targetMarkerGroup = new THREE.Group();
    targetMarkerGroup.name = "TargetBeaconMarker";

    // 1. Concentric Hologram Ground Rings (Glowing Cyan & Emerald)
    const ringGeo = new THREE.RingGeometry(2.5, 3.4, 32);
    const ringMat = new THREE.MeshBasicMaterial({ color: 0x06b6d4, side: THREE.DoubleSide, transparent: true, opacity: 0.85 });
    const ring = new THREE.Mesh(ringGeo, ringMat);
    ring.name = "beaconOuterRing";
    ring.rotation.x = Math.PI / 2;
    ring.position.y = 0.25;
    targetMarkerGroup.add(ring);

    const innerRingGeo = new THREE.RingGeometry(0.8, 1.5, 32);
    const innerRingMat = new THREE.MeshBasicMaterial({ color: 0x10b981, side: THREE.DoubleSide, transparent: true, opacity: 0.95 });
    const innerRing = new THREE.Mesh(innerRingGeo, innerRingMat);
    innerRing.name = "beaconInnerRing";
    innerRing.rotation.x = Math.PI / 2;
    innerRing.position.y = 0.28;
    targetMarkerGroup.add(innerRing);

    // 2. Vertical Beacon Hologram Beam
    const beamGeo = new THREE.CylinderGeometry(0.3, 3.2, 22, 16, 1, true);
    const beamMat = new THREE.MeshBasicMaterial({ color: 0x38bdf8, transparent: true, opacity: 0.4, side: THREE.DoubleSide });
    const beam = new THREE.Mesh(beamGeo, beamMat);
    beam.name = "beaconBeam";
    beam.position.y = 11;
    targetMarkerGroup.add(beam);

    // 3. Glowing Diamond / Target Pin
    const pinGeo = new THREE.OctahedronGeometry(1.4, 0);
    const pinMat = new THREE.MeshStandardMaterial({ color: 0xf59e0b, emissive: 0xd97706, roughness: 0.2 });
    const pin = new THREE.Mesh(pinGeo, pinMat);
    pin.name = "beaconPin";
    pin.position.y = 17;
    targetMarkerGroup.add(pin);

    // 4. Point Light for illumination
    targetBeaconLight = new THREE.PointLight(0x38bdf8, 2.5, 35);
    targetBeaconLight.position.y = 8;
    targetMarkerGroup.add(targetBeaconLight);

    targetMarkerGroup.visible = false;
    scene.add(targetMarkerGroup);
}


// =============================================================================
// DATABASE INTEGRATION: FETCH & RENDER CONTAINERS ACCORDING TO BAY-ROW-TIER
// =============================================================================
function fetchSimulationState() {
    fetch('api/simulator_action.php?action=get_state')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                simState = data;
                renderContainersFromState(data.containers);
                updateStatsUI(data.stats);
                populateMoveSelect(data.containers);
            }
        })
        .catch(err => {
            console.error('Gagal mengambil data state simulasi:', err);
        });
}

// =============================================================================
// CONTAINER SLOT COORDINATE ENGINE & PROCEDURAL BASELINE GENERATOR
// =============================================================================

// Helper: Calculate Exact 3D World Position for Yard Slots (5 Bays, 7 Rows, 4-5 Tiers Port Standard)
function getSlotWorldPosition(blockKey, bay, row, tier, is20ft = false) {
    const bCoord = BLOCK_COORDS[blockKey] || BLOCK_COORDS['A'];
    const bayNum  = parseInt(bay, 10) || 1;
    const rowNum  = parseInt(row, 10) || 1;
    const tierNum = parseInt(tier, 10) || 1;
    const cHeight = 2.60;

    let posX = bCoord.x;
    const isMainBlock = (blockKey === 'A' || blockKey === 'B' || blockKey === 'C' || blockKey === 'D');

    if (isMainBlock) {
        // 5 bays of 40ft (spacing 13.5m), or 10 bays of 20ft (spacing 6.75m)
        if (is20ft) {
            posX = (bCoord.x + 30.375) - ((bayNum - 1) % 10) * 6.75;
        } else {
            posX = (bCoord.x + 27.0) - ((bayNum - 1) % 5) * 13.5;
        }
    } else if (blockKey === 'EMPTY') {
        posX = (bCoord.x + 6.75) - ((bayNum - 1) % 2) * 13.5;
    } else {
        // REEFER or DG (2 bays)
        posX = (bCoord.x + 6.75) - ((bayNum - 1) % 2) * 13.5;
    }

    // Rows along Z (pitch 3.0m)
    const maxR = (blockKey === 'EMPTY') ? 16 : 7;
    const posZ = (bCoord.z - ((maxR - 1) * 3.0) / 2) + ((rowNum - 1) % maxR) * 3.0;

    // Tiers along Y: Ground slab at 0.20m, container height 2.60m
    const posY = 0.20 + (cHeight / 2) + ((tierNum - 1) * cHeight);

    return { x: posX, y: posY, z: posZ };
}

// Generate Realistic Multi-Tier Stacking Yard Baseline (Populates the Expansive 35 Ha Port)
function generateBaselineYardContainers() {
    const baselineList = [
        // --- BLOK A (LADEN EXPORT) - Multi-Tier Stacks ---
        // Bay 1
        { block: 'A', bay: 1, row: 1, tier: 1, color: SHIPPING_COLORS['Maersk'],    num: 'MSKU2091823', owner: 'Maersk Line',         type: '40FT HC', cargo: 'dry', weight: 28400 },
        { block: 'A', bay: 1, row: 1, tier: 2, color: SHIPPING_COLORS['CMA CGM'],   num: 'CMAU7718291', owner: 'CMA CGM',             type: '40FT HC', cargo: 'dry', weight: 27100 },
        { block: 'A', bay: 1, row: 1, tier: 3, color: SHIPPING_COLORS['ONE'],       num: 'ONEU9928104', owner: 'Ocean Network Express',type: '40FT HC', cargo: 'dry', weight: 26500 },
        { block: 'A', bay: 1, row: 1, tier: 4, color: SHIPPING_COLORS['MSC'],       num: 'MSCU1102938', owner: 'Mediterranean Shipping',type: '40FT HC',cargo: 'dry', weight: 29300 },
        { block: 'A', bay: 1, row: 2, tier: 1, color: SHIPPING_COLORS['Evergreen'], num: 'EGLU8821940', owner: 'Evergreen Marine',   type: '40FT HC', cargo: 'dry', weight: 29000 },
        { block: 'A', bay: 1, row: 2, tier: 2, color: SHIPPING_COLORS['MSC'],       num: 'MSCU6629104', owner: 'Mediterranean Shipping',type: '40FT HC',cargo: 'dry', weight: 28800 },
        { block: 'A', bay: 1, row: 3, tier: 1, color: SHIPPING_COLORS['Meratus'],   num: 'MRTU5519284', owner: 'Meratus Line',        type: '40FT HC', cargo: 'dry', weight: 25400 },
        { block: 'A', bay: 1, row: 4, tier: 1, color: SHIPPING_COLORS['Samudera'],  num: 'SMDU3381920', owner: 'Samudera Indonesia',  type: '40FT HC', cargo: 'dry', weight: 27600 },
        { block: 'A', bay: 1, row: 4, tier: 2, color: SHIPPING_COLORS['Maersk'],    num: 'MSKU1129384', owner: 'Maersk Line',         type: '40FT HC', cargo: 'dry', weight: 28100 },
        { block: 'A', bay: 1, row: 5, tier: 1, color: SHIPPING_COLORS['CMA CGM'],   num: 'CMAU4419201', owner: 'CMA CGM',             type: '40FT HC', cargo: 'dry', weight: 26800 },
        { block: 'A', bay: 1, row: 6, tier: 1, color: SHIPPING_COLORS['Evergreen'], num: 'EGLU3301928', owner: 'Evergreen Marine',   type: '40FT HC', cargo: 'dry', weight: 27900 },
        { block: 'A', bay: 1, row: 7, tier: 1, color: SHIPPING_COLORS['MSC'],       num: 'MSCU7729103', owner: 'Mediterranean Shipping',type: '40FT HC',cargo: 'dry', weight: 28400 },

        // Bay 2 (Leaves Row 2, Tier 1 open for dynamic gate-in drop-off!)
        { block: 'A', bay: 2, row: 1, tier: 1, color: SHIPPING_COLORS['ONE'],       num: 'ONEU1029384', owner: 'Ocean Network Express',type: '40FT HC', cargo: 'dry', weight: 27200 },
        { block: 'A', bay: 2, row: 1, tier: 2, color: SHIPPING_COLORS['Maersk'],    num: 'MSKU2291029', owner: 'Maersk Line',         type: '40FT HC', cargo: 'dry', weight: 28100 },
        { block: 'A', bay: 2, row: 3, tier: 1, color: SHIPPING_COLORS['Maersk'],    num: 'MSKU8829102', owner: 'Maersk Line',         type: '40FT HC', cargo: 'dry', weight: 28600 },
        { block: 'A', bay: 2, row: 3, tier: 2, color: SHIPPING_COLORS['Evergreen'], num: 'EGLU9920183', owner: 'Evergreen Marine',   type: '40FT HC', cargo: 'dry', weight: 26900 },
        { block: 'A', bay: 2, row: 3, tier: 3, color: SHIPPING_COLORS['MSC'],       num: 'MSCU5519203', owner: 'Mediterranean Shipping',type: '40FT HC',cargo: 'dry', weight: 28700 },
        { block: 'A', bay: 2, row: 4, tier: 1, color: SHIPPING_COLORS['MSC'],       num: 'MSCU4410294', owner: 'Mediterranean Shipping',type: '40FT HC',cargo: 'dry', weight: 29100 },
        { block: 'A', bay: 2, row: 5, tier: 1, color: SHIPPING_COLORS['Meratus'],   num: 'MRTU7719203', owner: 'Meratus Line',        type: '40FT HC', cargo: 'dry', weight: 25800 },
        { block: 'A', bay: 2, row: 6, tier: 1, color: SHIPPING_COLORS['Samudera'],  num: 'SMDU9920194', owner: 'Samudera Indonesia',  type: '40FT HC', cargo: 'dry', weight: 27300 },
        { block: 'A', bay: 2, row: 7, tier: 1, color: SHIPPING_COLORS['CMA CGM'],   num: 'CMAU2219483', owner: 'CMA CGM',             type: '40FT HC', cargo: 'dry', weight: 28000 },

        // Bay 3
        { block: 'A', bay: 3, row: 1, tier: 1, color: SHIPPING_COLORS['Evergreen'], num: 'EGLU5510293', owner: 'Evergreen Marine',   type: '40FT HC', cargo: 'dry', weight: 28900 },
        { block: 'A', bay: 3, row: 2, tier: 1, color: SHIPPING_COLORS['MSC'],       num: 'MSCU3329102', owner: 'Mediterranean Shipping',type: '40FT HC',cargo: 'dry', weight: 27500 },
        { block: 'A', bay: 3, row: 2, tier: 2, color: SHIPPING_COLORS['Maersk'],    num: 'MSKU6619284', owner: 'Maersk Line',         type: '40FT HC', cargo: 'dry', weight: 28300 },
        { block: 'A', bay: 3, row: 3, tier: 1, color: SHIPPING_COLORS['ONE'],       num: 'ONEU7718293', owner: 'Ocean Network Express',type: '40FT HC', cargo: 'dry', weight: 26400 },
        { block: 'A', bay: 3, row: 4, tier: 1, color: SHIPPING_COLORS['Meratus'],   num: 'MRTU2210493', owner: 'Meratus Line',        type: '40FT HC', cargo: 'dry', weight: 25600 },
        { block: 'A', bay: 3, row: 5, tier: 1, color: SHIPPING_COLORS['CMA CGM'],   num: 'CMAU9918294', owner: 'CMA CGM',             type: '40FT HC', cargo: 'dry', weight: 27800 },
        { block: 'A', bay: 3, row: 6, tier: 1, color: SHIPPING_COLORS['Samudera'],  num: 'SMDU4410293', owner: 'Samudera Indonesia',  type: '40FT HC', cargo: 'dry', weight: 28100 },

        // Bay 4 & 5
        { block: 'A', bay: 4, row: 1, tier: 1, color: SHIPPING_COLORS['Maersk'],    num: 'MSKU8810291', owner: 'Maersk Line',         type: '40FT HC', cargo: 'dry', weight: 28500 },
        { block: 'A', bay: 4, row: 1, tier: 2, color: SHIPPING_COLORS['Evergreen'], num: 'EGLU7719284', owner: 'Evergreen Marine',   type: '40FT HC', cargo: 'dry', weight: 28700 },
        { block: 'A', bay: 4, row: 3, tier: 1, color: SHIPPING_COLORS['ONE'],       num: 'ONEU6619283', owner: 'Ocean Network Express',type: '40FT HC', cargo: 'dry', weight: 26800 },
        { block: 'A', bay: 5, row: 2, tier: 1, color: SHIPPING_COLORS['MSC'],       num: 'MSCU5519284', owner: 'Mediterranean Shipping',type: '40FT HC',cargo: 'dry', weight: 29100 },

        // --- BLOK B (LADEN IMPORT) ---
        // Bay 1
        { block: 'B', bay: 1, row: 1, tier: 1, color: SHIPPING_COLORS['MSC'],       num: 'MSCU9918294', owner: 'Mediterranean Shipping',type: '40FT HC',cargo: 'dry', weight: 29400 },
        { block: 'B', bay: 1, row: 1, tier: 2, color: SHIPPING_COLORS['Maersk'],    num: 'MSKU4419203', owner: 'Maersk Line',         type: '40FT HC', cargo: 'dry', weight: 28200 },
        { block: 'B', bay: 1, row: 2, tier: 1, color: SHIPPING_COLORS['Evergreen'], num: 'EGLU7718294', owner: 'Evergreen Marine',   type: '40FT HC', cargo: 'dry', weight: 28700 },
        { block: 'B', bay: 1, row: 3, tier: 1, color: SHIPPING_COLORS['ONE'],       num: 'ONEU3319284', owner: 'Ocean Network Express',type: '40FT HC', cargo: 'dry', weight: 26700 },
        { block: 'B', bay: 1, row: 4, tier: 1, color: SHIPPING_COLORS['CMA CGM'],   num: 'CMAU6619283', owner: 'CMA CGM',             type: '40FT HC', cargo: 'dry', weight: 27500 },
        { block: 'B', bay: 1, row: 5, tier: 1, color: SHIPPING_COLORS['Meratus'],   num: 'MRTU8819204', owner: 'Meratus Line',        type: '40FT HC', cargo: 'dry', weight: 25900 },
        { block: 'B', bay: 1, row: 6, tier: 1, color: SHIPPING_COLORS['Samudera'],  num: 'SMDU1120493', owner: 'Samudera Indonesia',  type: '40FT HC', cargo: 'dry', weight: 27800 },
        { block: 'B', bay: 1, row: 7, tier: 1, color: SHIPPING_COLORS['MSC'],       num: 'MSCU2219485', owner: 'Mediterranean Shipping',type: '40FT HC',cargo: 'dry', weight: 29000 },

        // Bay 2 (Target Pick-Up Container at Row 2, Tier 1)
        { block: 'B', bay: 2, row: 1, tier: 1, color: SHIPPING_COLORS['Maersk'],    num: 'MSKU5519283', owner: 'Maersk Line',         type: '40FT HC', cargo: 'dry', weight: 28500 },
        { block: 'B', bay: 2, row: 2, tier: 1, color: SHIPPING_COLORS['Evergreen'], num: 'MSKU4952240', owner: 'Evergreen Marine',   type: '40FT HC', cargo: 'dry', weight: 28800 },
        { block: 'B', bay: 2, row: 3, tier: 1, color: SHIPPING_COLORS['CMA CGM'],   num: 'CMAU3319204', owner: 'CMA CGM',             type: '40FT HC', cargo: 'dry', weight: 27400 },
        { block: 'B', bay: 2, row: 4, tier: 1, color: SHIPPING_COLORS['ONE'],       num: 'ONEU8819284', owner: 'Ocean Network Express',type: '40FT HC', cargo: 'dry', weight: 26800 },
        { block: 'B', bay: 2, row: 5, tier: 1, color: SHIPPING_COLORS['MSC'],       num: 'MSCU7719283', owner: 'Mediterranean Shipping',type: '40FT HC',cargo: 'dry', weight: 29200 },
        { block: 'B', bay: 2, row: 6, tier: 1, color: SHIPPING_COLORS['Meratus'],   num: 'MRTU4419203', owner: 'Meratus Line',        type: '40FT HC', cargo: 'dry', weight: 25700 },

        // Bay 3
        { block: 'B', bay: 3, row: 1, tier: 1, color: SHIPPING_COLORS['ONE'],       num: 'ONEU2219483', owner: 'Ocean Network Express',type: '40FT HC', cargo: 'dry', weight: 26500 },
        { block: 'B', bay: 3, row: 1, tier: 2, color: SHIPPING_COLORS['MSC'],       num: 'MSCU8819204', owner: 'Mediterranean Shipping',type: '40FT HC',cargo: 'dry', weight: 29100 },
        { block: 'B', bay: 3, row: 2, tier: 1, color: SHIPPING_COLORS['Maersk'],    num: 'MSKU7719284', owner: 'Maersk Line',         type: '40FT HC', cargo: 'dry', weight: 28400 },
        { block: 'B', bay: 3, row: 3, tier: 1, color: SHIPPING_COLORS['Evergreen'], num: 'EGLU4419204', owner: 'Evergreen Marine',   type: '40FT HC', cargo: 'dry', weight: 28600 },
        { block: 'B', bay: 3, row: 4, tier: 1, color: SHIPPING_COLORS['CMA CGM'],   num: 'CMAU5519283', owner: 'CMA CGM',             type: '40FT HC', cargo: 'dry', weight: 27900 },
        { block: 'B', bay: 3, row: 5, tier: 1, color: SHIPPING_COLORS['Samudera'],  num: 'SMDU6619284', owner: 'Samudera Indonesia',  type: '40FT HC', cargo: 'dry', weight: 28000 },

        // Bay 4 & 5
        { block: 'B', bay: 4, row: 2, tier: 1, color: SHIPPING_COLORS['Maersk'],    num: 'MSKU3310492', owner: 'Maersk Line',         type: '40FT HC', cargo: 'dry', weight: 28300 },
        { block: 'B', bay: 4, row: 2, tier: 2, color: SHIPPING_COLORS['ONE'],       num: 'ONEU5510294', owner: 'Ocean Network Express',type: '40FT HC', cargo: 'dry', weight: 26600 },
        { block: 'B', bay: 5, row: 3, tier: 1, color: SHIPPING_COLORS['Evergreen'], num: 'EGLU2219401', owner: 'Evergreen Marine',   type: '40FT HC', cargo: 'dry', weight: 28800 },

        // --- BLOK C (DOMESTIC CARGO) ---
        { block: 'C', bay: 1, row: 1, tier: 1, color: SHIPPING_COLORS['Meratus'],   num: 'MRTU1029381', owner: 'Meratus Line',        type: '40FT HC', cargo: 'dry', weight: 25100 },
        { block: 'C', bay: 1, row: 2, tier: 1, color: SHIPPING_COLORS['Samudera'],  num: 'SMDU2039481', owner: 'Samudera Indonesia',  type: '40FT HC', cargo: 'dry', weight: 26200 },
        { block: 'C', bay: 2, row: 1, tier: 1, color: SHIPPING_COLORS['Meratus'],   num: 'MRTU3049581', owner: 'Meratus Line',        type: '40FT HC', cargo: 'dry', weight: 24900 },
        { block: 'C', bay: 2, row: 3, tier: 1, color: SHIPPING_COLORS['Samudera'],  num: 'SMDU4059681', owner: 'Samudera Indonesia',  type: '40FT HC', cargo: 'dry', weight: 25800 },
        { block: 'C', bay: 3, row: 1, tier: 1, color: SHIPPING_COLORS['Meratus'],   num: 'MRTU5069781', owner: 'Meratus Line',        type: '40FT HC', cargo: 'dry', weight: 25300 },
        { block: 'C', bay: 3, row: 2, tier: 1, color: SHIPPING_COLORS['Samudera'],  num: 'SMDU6079881', owner: 'Samudera Indonesia',  type: '40FT HC', cargo: 'dry', weight: 26000 },
        { block: 'C', bay: 4, row: 1, tier: 1, color: SHIPPING_COLORS['Meratus'],   num: 'MRTU7089981', owner: 'Meratus Line',        type: '40FT HC', cargo: 'dry', weight: 25400 },

        // --- BLOK D (BUFFER YARD) ---
        { block: 'D', bay: 1, row: 1, tier: 1, color: SHIPPING_COLORS['Maersk'],    num: 'MSKU9018273', owner: 'Maersk Line',         type: '40FT HC', cargo: 'dry', weight: 28200 },
        { block: 'D', bay: 1, row: 2, tier: 1, color: SHIPPING_COLORS['Evergreen'], num: 'EGLU8027162', owner: 'Evergreen Marine',   type: '40FT HC', cargo: 'dry', weight: 27800 },
        { block: 'D', bay: 2, row: 2, tier: 1, color: SHIPPING_COLORS['CMA CGM'],   num: 'CMAU9920194', owner: 'CMA CGM',             type: '40FT HC', cargo: 'dry', weight: 27900 },
        { block: 'D', bay: 3, row: 1, tier: 1, color: SHIPPING_COLORS['ONE'],       num: 'ONEU7036251', owner: 'Ocean Network Express',type: '40FT HC', cargo: 'dry', weight: 26900 },

        // --- REEFER ZONE (Cold Chain Pure White) ---
        { block: 'REEFER', bay: 1, row: 1, tier: 1, color: SHIPPING_COLORS['reefer'], num: 'EITU9823102', owner: 'Maersk Reefer Cool',   type: '40FT RF', cargo: 'reefer', weight: 29500 },
        { block: 'REEFER', bay: 1, row: 2, tier: 1, color: SHIPPING_COLORS['reefer'], num: 'MSKU4410297', owner: 'Carrier Transicold',   type: '40FT RF', cargo: 'reefer', weight: 29800 },
        { block: 'REEFER', bay: 1, row: 3, tier: 1, color: SHIPPING_COLORS['reefer'], num: 'TRLU5510294', owner: 'Thermo King Cold',    type: '40FT RF', cargo: 'reefer', weight: 29200 },
        { block: 'REEFER', bay: 1, row: 4, tier: 1, color: SHIPPING_COLORS['reefer'], num: 'EITU7729104', owner: 'Daikin Reefer Tech',   type: '40FT RF', cargo: 'reefer', weight: 29600 },

        // --- HAZMAT DG (Red & Hazard Striped) ---
        { block: 'DG', bay: 1, row: 1, tier: 1, color: SHIPPING_COLORS['dg'], num: 'DGAU1102938', owner: 'Bollore Hazmat Logistics', type: '20FT DG', cargo: 'dg', weight: 22000 },
        { block: 'DG', bay: 1, row: 2, tier: 1, color: SHIPPING_COLORS['dg'], num: 'DGAU2203948', owner: 'KWE Chemical Freight',      type: '20FT DG', cargo: 'dg', weight: 21500 },

        // --- EMPTY DEPOT (Stacked up to 5 Tiers High) ---
        { block: 'EMPTY', bay: 1, row: 1, tier: 1, color: SHIPPING_COLORS['empty'], num: 'MTYU1000001', owner: 'CIDP Depot Operations', type: '40FT MT', cargo: 'empty', weight: 3800 },
        { block: 'EMPTY', bay: 1, row: 1, tier: 2, color: SHIPPING_COLORS['empty'], num: 'MTYU1000002', owner: 'CIDP Depot Operations', type: '40FT MT', cargo: 'empty', weight: 3800 },
        { block: 'EMPTY', bay: 1, row: 1, tier: 3, color: SHIPPING_COLORS['empty'], num: 'MTYU1000003', owner: 'CIDP Depot Operations', type: '40FT MT', cargo: 'empty', weight: 3800 },
        { block: 'EMPTY', bay: 1, row: 1, tier: 4, color: SHIPPING_COLORS['empty'], num: 'MTYU1000004', owner: 'CIDP Depot Operations', type: '40FT MT', cargo: 'empty', weight: 3800 },
        { block: 'EMPTY', bay: 1, row: 1, tier: 5, color: SHIPPING_COLORS['empty'], num: 'MTYU1000005', owner: 'CIDP Depot Operations', type: '40FT MT', cargo: 'empty', weight: 3800 },
        { block: 'EMPTY', bay: 1, row: 2, tier: 1, color: SHIPPING_COLORS['empty'], num: 'MTYU1000006', owner: 'CIDP Depot Operations', type: '40FT MT', cargo: 'empty', weight: 3800 },
        { block: 'EMPTY', bay: 1, row: 2, tier: 2, color: SHIPPING_COLORS['empty'], num: 'MTYU1000007', owner: 'CIDP Depot Operations', type: '40FT MT', cargo: 'empty', weight: 3800 },
        { block: 'EMPTY', bay: 1, row: 2, tier: 3, color: SHIPPING_COLORS['empty'], num: 'MTYU1000008', owner: 'CIDP Depot Operations', type: '40FT MT', cargo: 'empty', weight: 3800 }
    ];

    baselineList.forEach(c => {
        const is20ft = (c.type && c.type.includes('20'));
        const pos = getSlotWorldPosition(c.block, c.bay, c.row, c.tier, is20ft);
        const cLength = is20ft ? 6.0 : 12.0;
        const mesh = createContainerMesh(cLength, 2.60, 2.44, c.color, c.num, c.cargo);
        mesh.position.set(pos.x, pos.y, pos.z);
        mesh.userData = {
            container_number: c.num,
            size_type: c.type,
            cargo_type: c.cargo,
            block: c.block,
            bay: String(c.bay).padStart(2, '0'),
            row: String(c.row).padStart(2, '0'),
            tier: String(c.tier).padStart(2, '0'),
            gross_weight_kg: c.weight,
            owner_company: c.owner,
            rfid_tag: 'RFID-CTR-' + c.num.slice(-3),
            customs_status: 'SPPB_CLEARED',
            status: 'in_yard'
        };
        scene.add(mesh);
        containerMeshes[c.num] = mesh;
    });
}

// Render Containers onto 3D Yard Blocks (Synchronized with MySQL)
function renderContainersFromState(containers) {
    if (!containers || !Array.isArray(containers) || isAnimating) return;

    containers.forEach(c => {
        if (c.status !== 'in_yard') {
            if (containerMeshes[c.container_number]) {
                scene.remove(containerMeshes[c.container_number]);
                delete containerMeshes[c.container_number];
            }
            return;
        }

        const bCoord = BLOCK_COORDS[c.block];
        if (!bCoord) return;

        const is20ft = (c.size_type && c.size_type.includes('20')) || (c.iso_code && c.iso_code.startsWith('2'));
        const cLength = is20ft ? 6.0 : 12.0;
        const pos = getSlotWorldPosition(c.block, c.bay, c.row, c.tier, is20ft);

        let colorHex = SHIPPING_COLORS['Maersk'];
        if (c.cargo_type === 'reefer') colorHex = SHIPPING_COLORS['reefer'];
        else if (c.cargo_type === 'dg') colorHex = SHIPPING_COLORS['dg'];
        else if (c.cargo_type === 'empty') colorHex = SHIPPING_COLORS['empty'];
        else if (c.owner_company && c.owner_company.includes('Evergreen')) colorHex = SHIPPING_COLORS['Evergreen'];
        else if (c.owner_company && c.owner_company.includes('CMA')) colorHex = SHIPPING_COLORS['CMA CGM'];
        else if (c.owner_company && c.owner_company.includes('Meratus')) colorHex = SHIPPING_COLORS['Meratus'];
        else if (c.owner_company && c.owner_company.includes('Samudera')) colorHex = SHIPPING_COLORS['Samudera'];
        else if (c.owner_company && c.owner_company.includes('MSC')) colorHex = SHIPPING_COLORS['MSC'];
        else if (c.owner_company && c.owner_company.includes('ONE')) colorHex = SHIPPING_COLORS['ONE'];

        // If mesh already exists, update position & userData; else create
        let mesh = containerMeshes[c.container_number];
        if (mesh) {
            mesh.position.set(pos.x, pos.y, pos.z);
            mesh.userData = c;
        } else {
            mesh = createContainerMesh(cLength, 2.60, 2.44, colorHex, c.container_number, c.cargo_type);
            mesh.position.set(pos.x, pos.y, pos.z);
            mesh.userData = c;
            scene.add(mesh);
            containerMeshes[c.container_number] = mesh;
        }
    });
}

// Procedural 3D Container Mesh with Authentic Industrial Stacking Details
function createContainerMesh(l, h, w, colorHex, boxNumber, cargoType = 'dry') {
    const group = new THREE.Group();

    // 1. Main Structural Box (Weathered Corten Steel)
    const boxGeo = new THREE.BoxGeometry(l, h, w);
    const boxMat = new THREE.MeshStandardMaterial({
        color: colorHex,
        roughness: 0.58,
        metalness: 0.28
    });
    const mainBox = new THREE.Mesh(boxGeo, boxMat);
    mainBox.castShadow = true;
    mainBox.receiveShadow = true;
    group.add(mainBox);

    // 1b. CAD Edge Outline Wireframe (gaya model BIM Revit/Tekla) - geometri di-cache per dimensi
    const edgeKey = l + '_' + h + '_' + w;
    if (!createContainerMesh._edgeCache) createContainerMesh._edgeCache = {};
    if (!createContainerMesh._edgeMat) {
        createContainerMesh._edgeMat = new THREE.LineBasicMaterial({ color: 0x0f172a, transparent: true, opacity: 0.45 });
    }
    if (!createContainerMesh._edgeCache[edgeKey]) {
        createContainerMesh._edgeCache[edgeKey] = new THREE.EdgesGeometry(boxGeo);
    }
    group.add(new THREE.LineSegments(createContainerMesh._edgeCache[edgeKey], createContainerMesh._edgeMat));

    // 2. 8 ISO 1161 Steel Corner Castings (Top and Bottom Corners)
    const cornerMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.3, metalness: 0.8 });
    const cornerHoleMat = new THREE.MeshBasicMaterial({ color: 0x020617 });
    const cornerGeo = new THREE.BoxGeometry(0.32, 0.32, 0.32);
    const holeGeo = new THREE.BoxGeometry(0.12, 0.04, 0.18);

    const cornerX = [ -l/2 + 0.16, l/2 - 0.16 ];
    const cornerY = [ -h/2 + 0.16, h/2 - 0.16 ];
    const cornerZ = [ -w/2 + 0.16, w/2 - 0.16 ];

    cornerX.forEach(cx => {
        cornerY.forEach(cy => {
            cornerZ.forEach(cz => {
                const cornerMesh = new THREE.Mesh(cornerGeo, cornerMat);
                cornerMesh.position.set(cx, cy, cz);
                group.add(cornerMesh);

                // Aperture cutout / twistlock pocket on casting face
                const hole = new THREE.Mesh(holeGeo, cornerHoleMat);
                hole.position.set(cx, cy + (cy > 0 ? 0.15 : -0.15), cz);
                group.add(hole);
            });
        });
    });

    // 3. Stacking Interlock Cones (Twistlock Guides on 4 Top Corners)
    const twistlockGeo = new THREE.CylinderGeometry(0.04, 0.08, 0.16, 8);
    const twistlockMat = new THREE.MeshBasicMaterial({ color: 0xeab308 }); // Safety Yellow
    cornerX.forEach(cx => {
        cornerZ.forEach(cz => {
            const tl = new THREE.Mesh(twistlockGeo, twistlockMat);
            tl.position.set(cx, h/2 + 0.08, cz);
            group.add(tl);
        });
    });

    // 4. Side Corrugation Vertical Ribs (Authentic light/shadow ripples)
    const vRibGeo = new THREE.BoxGeometry(0.12, h - 0.36, 0.04);
    const vRibMat = new THREE.MeshStandardMaterial({ color: colorHex, roughness: 0.5 });
    for (let rx = -l/2 + 0.4; rx <= l/2 - 0.4; rx += 0.55) {
        [-w/2 - 0.02, w/2 + 0.02].forEach(rz => {
            const vRib = new THREE.Mesh(vRibGeo, vRibMat);
            vRib.position.set(rx, 0, rz);
            group.add(vRib);
        });
    }

    // 5. Roof Transverse Corrugations
    const rRibGeo = new THREE.BoxGeometry(0.15, 0.06, w - 0.35);
    for (let rx = -l/2 + 0.4; rx <= l/2 - 0.4; rx += 0.55) {
        const rRib = new THREE.Mesh(rRibGeo, vRibMat);
        rRib.position.set(rx, h/2 + 0.03, 0);
        group.add(rRib);
    }

    // 6. Rear End Cargo Doors (+X Face) with Locking Rods & Customs Seal
    const doorFrameGeo = new THREE.BoxGeometry(0.06, h - 0.1, w - 0.1);
    const doorFrameMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.7 });
    const doorFrame = new THREE.Mesh(doorFrameGeo, doorFrameMat);
    doorFrame.position.set(l/2 + 0.02, 0, 0);
    group.add(doorFrame);

    // Center vertical split line
    const splitGeo = new THREE.BoxGeometry(0.08, h - 0.2, 0.04);
    const splitMesh = new THREE.Mesh(splitGeo, cornerHoleMat);
    splitMesh.position.set(l/2 + 0.03, 0, 0);
    group.add(splitMesh);

    // 4 Galvanized Vertical Locking Bars (2 per door)
    const rodGeo = new THREE.CylinderGeometry(0.03, 0.03, h - 0.35, 8);
    const rodMat = new THREE.MeshStandardMaterial({ color: 0x94a3b8, metalness: 0.9, roughness: 0.2 });
    [-0.7, -0.25, 0.25, 0.7].forEach(rz => {
        const rod = new THREE.Mesh(rodGeo, rodMat);
        rod.position.set(l/2 + 0.05, 0, rz);
        group.add(rod);

        // Cam Keepers (top and bottom brackets)
        [-h/2 + 0.25, h/2 - 0.25].forEach(ry => {
            const camGeo = new THREE.BoxGeometry(0.08, 0.12, 0.1);
            const camMesh = new THREE.Mesh(camGeo, cornerMat);
            camMesh.position.set(l/2 + 0.05, ry, rz);
            group.add(camMesh);
        });
    });

    // Horizontal Door Locking Handles & Customs Bullet Seal
    const handleGeo = new THREE.BoxGeometry(0.06, 0.04, 0.25);
    const handleMesh = new THREE.Mesh(handleGeo, rodMat);
    handleMesh.position.set(l/2 + 0.07, -0.15, 0.25);
    group.add(handleMesh);

    const sealGeo = new THREE.CylinderGeometry(0.025, 0.025, 0.1, 8);
    const sealMat = new THREE.MeshBasicMaterial({ color: 0x06b6d4 }); // Cyan e-Seal
    const sealMesh = new THREE.Mesh(sealGeo, sealMat);
    sealMesh.position.set(l/2 + 0.09, -0.15, 0.20);
    group.add(sealMesh);

    // 7. Stencil Markings Canvas Decal (Box Number, ISO Code, Stacking Info)
    const decalCanvas = document.createElement('canvas');
    decalCanvas.width = 512;
    decalCanvas.height = 128;
    const dctx = decalCanvas.getContext('2d');
    dctx.clearRect(0, 0, 512, 128);
    dctx.font = 'bold 36px monospace';
    dctx.fillStyle = '#ffffff';
    dctx.fillText(boxNumber || 'MSKU1029384', 20, 45);
    dctx.font = 'bold 22px monospace';
    dctx.fillStyle = '#cbd5e1';
    dctx.fillText('45G1  HIGH CUBE  9\'6"', 20, 80);
    dctx.font = '16px monospace';
    dctx.fillStyle = '#94a3b8';
    dctx.fillText('MAX 32.500 KG | TARE 3.850 KG', 20, 110);

    const decalTex = new THREE.CanvasTexture(decalCanvas);
    const decalMat = new THREE.MeshBasicMaterial({ map: decalTex, transparent: true });
    const decalGeo = new THREE.PlaneGeometry(3.6, 0.9);
    
    // Front side decal
    const decalMeshF = new THREE.Mesh(decalGeo, decalMat);
    decalMeshF.position.set(l/2 - 2.5, 0.35, w/2 + 0.03);
    group.add(decalMeshF);

    // Rear door decal
    const doorDecalCanvas = document.createElement('canvas');
    doorDecalCanvas.width = 256;
    doorDecalCanvas.height = 128;
    const ddctx = doorDecalCanvas.getContext('2d');
    ddctx.font = 'bold 26px monospace';
    ddctx.fillStyle = '#ffffff';
    ddctx.fillText(boxNumber || 'MSKU1029384', 10, 35);
    ddctx.font = '18px monospace';
    ddctx.fillStyle = '#f59e0b';
    ddctx.fillText('WARNING 9\'6" HIGH', 10, 70);
    ddctx.font = '14px monospace';
    ddctx.fillStyle = '#94a3b8';
    ddctx.fillText('CSC SAFETY APPROVED', 10, 100);

    const doorTex = new THREE.CanvasTexture(doorDecalCanvas);
    const doorDecalMat = new THREE.MeshBasicMaterial({ map: doorTex, transparent: true });
    const doorDecalGeo = new THREE.PlaneGeometry(1.0, 0.5);
    const doorDecalMesh = new THREE.Mesh(doorDecalGeo, doorDecalMat);
    doorDecalMesh.rotation.y = Math.PI / 2;
    doorDecalMesh.position.set(l/2 + 0.06, 0.45, -0.45);
    group.add(doorDecalMesh);

    // 8. High Cube Zebra Warning Stripes on Top Front/Rear Corners (IMO Requirement)
    const zebraGeo = new THREE.BoxGeometry(0.4, 0.12, 0.02);
    const zebraCanvas = document.createElement('canvas');
    zebraCanvas.width = 128;
    zebraCanvas.height = 32;
    const zctx = zebraCanvas.getContext('2d');
    zctx.fillStyle = '#eab308';
    zctx.fillRect(0, 0, 128, 32);
    zctx.fillStyle = '#000000';
    for (let zx = 0; zx < 128; zx += 20) {
        zctx.beginPath();
        zctx.moveTo(zx, 0);
        zctx.lineTo(zx + 10, 0);
        zctx.lineTo(zx, 32);
        zctx.lineTo(zx - 10, 32);
        zctx.fill();
    }
    const zebraTex = new THREE.CanvasTexture(zebraCanvas);
    const zebraMat = new THREE.MeshBasicMaterial({ map: zebraTex });
    [-l/2 + 0.4, l/2 - 0.4].forEach(zx => {
        const zm = new THREE.Mesh(zebraGeo, zebraMat);
        zm.position.set(zx, h/2 - 0.08, w/2 + 0.025);
        group.add(zm);
    });

    // 9. Reefer Machinery Unit (if reefer cargo)
    if (cargoType === 'reefer' || colorHex === SHIPPING_COLORS['reefer']) {
        const reeferUnitGeo = new THREE.BoxGeometry(0.3, h - 0.6, w - 0.5);
        const reeferUnitMat = new THREE.MeshStandardMaterial({ color: 0xe2e8f0, roughness: 0.3 });
        const reeferUnit = new THREE.Mesh(reeferUnitGeo, reeferUnitMat);
        reeferUnit.position.set(-l/2 - 0.08, 0, 0);
        group.add(reeferUnit);

        // Green Running LED Light
        const rLed = new THREE.Mesh(new THREE.SphereGeometry(0.06, 8, 8), new THREE.MeshBasicMaterial({ color: 0x22c55e }));
        rLed.position.set(-l/2 - 0.24, 0.4, 0.5);
        group.add(rLed);
    }

    return group;
}

// =============================================================================
// INTERACTIVE RAYCASTING (KLIK KONTAINER LANGSUNG DI 3D)
// =============================================================================
let raycaster = new THREE.Raycaster();
let mouse = new THREE.Vector2();

function setupRaycasting(container) {
    container.addEventListener('click', onCanvasClick);
}

function onCanvasClick(event) {
    const rect = renderer.domElement.getBoundingClientRect();
    mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
    mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

    raycaster.setFromCamera(mouse, camera);

    // Test intersections with container meshes
    const candidates = Object.values(containerMeshes);
    const intersects = raycaster.intersectObjects(candidates, true);

    if (intersects.length > 0) {
        // Find top-level group
        let hitObject = intersects[0].object;
        while (hitObject.parent && hitObject.parent !== scene) {
            hitObject = hitObject.parent;
        }

        if (hitObject.userData && hitObject.userData.container_number) {
            inspectContainer(hitObject.userData);
        }
    }
}

// Display Container Info in Glassmorphism Inspector HUD
function inspectContainer(data) {
    selectedContainerData = data;

    document.getElementById('inspBoxNum').innerText = data.container_number;
    document.getElementById('inspType').innerText = (data.size_type || '40FT HC').toUpperCase();
    document.getElementById('inspCargo').innerText = (data.cargo_type || 'Dry').toUpperCase();
    document.getElementById('inspSlot').innerText = `${data.block}-${data.bay}-${data.row}-${data.tier}`;
    document.getElementById('inspWeight').innerText = Number(data.gross_weight_kg || 28000).toLocaleString('id-ID') + ' kg';
    document.getElementById('inspOwner').innerText = data.owner_company || 'Maersk Indonesia';
    document.getElementById('inspRfid').innerText = data.rfid_tag || 'E280117000000001';

    const card = document.getElementById('inspectorCard');
    card.classList.remove('hidden');

    // 3D Focus & Target Beacon onto inspected container
    const is20ft = (data.size_type && data.size_type.includes('20'));
    const pos = getSlotWorldPosition(data.block, data.bay, data.row, data.tier, is20ft);
    const camPreset = data.cargo_type === 'reefer' ? 'reefer' : 'yard';
    focusLocation(camPreset, `Peti Kemas ${data.container_number} (${data.block}-${data.bay}-${data.row}-${data.tier})`, { x: pos.x, y: 0, z: pos.z }, (data.cargo_type || 'DRY').toUpperCase());

    logTicker(`[INSPECTOR] Kontainer ${data.container_number} dipilih di slot ${data.block}-${data.bay}-${data.row}-${data.tier}.`);
}

function closeInspector() {
    document.getElementById('inspectorCard').classList.add('hidden');
    selectedContainerData = null;
}

function prefillAndOpenMove() {
    if (!selectedContainerData) return;
    openMoveModal();
    const sel = document.getElementById('moveContainerSelect');
    const boxNum = selectedContainerData.container_number;
    
    // Ensure the container is available in dropdown options
    let found = false;
    for (let i = 0; i < sel.options.length; i++) {
        if (sel.options[i].dataset.boxNum === boxNum || sel.options[i].value == selectedContainerData.id || sel.options[i].textContent.includes(boxNum)) {
            sel.selectedIndex = i;
            found = true;
            break;
        }
    }
    if (!found) {
        const opt = document.createElement('option');
        opt.value = selectedContainerData.id || boxNum;
        opt.dataset.boxNum = boxNum;
        opt.textContent = `📦 ${boxNum} — ${selectedContainerData.owner_company || 'Laden'} (Saat ini di ${selectedContainerData.block}-${selectedContainerData.bay}-${selectedContainerData.row}-${selectedContainerData.tier})`;
        sel.prepend(opt);
        sel.selectedIndex = 0;
    }
    
    // Suggest sensible target block/slot
    const curBlock = selectedContainerData.block || 'A';
    document.getElementById('moveToBlock').value = (curBlock === 'A') ? 'B' : 'A';
    document.getElementById('moveToBay').value = selectedContainerData.bay || '02';
    document.getElementById('moveToRow').value = selectedContainerData.row || '03';
    document.getElementById('moveToTier').value = (parseInt(selectedContainerData.tier, 10) < 4) ? String(parseInt(selectedContainerData.tier, 10) + 1).padStart(2, '0') : '01';
}

// =============================================================================
// AKSI 1: PEMINDAHAN KONTAINER OLEH REACH STACKER (LO-LO OPERATION)
// =============================================================================
function openMoveModal() {
    document.getElementById('modalMoveBox').classList.remove('hidden');
}

function closeMoveModal() {
    document.getElementById('modalMoveBox').classList.add('hidden');
}

function populateMoveSelect(containers) {
    const sel = document.getElementById('moveContainerSelect');
    if (!sel) return;
    const currentVal = sel.value;
    sel.innerHTML = '';
    
    // 1. Add containers from MySQL
    const addedBoxNums = new Set();
    if (containers && Array.isArray(containers)) {
        containers.forEach(c => {
            if (c.status === 'in_yard') {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.dataset.boxNum = c.container_number;
                opt.textContent = `📦 ${c.container_number} — ${c.owner_company} (${c.block}-${c.bay}-${c.row}-${c.tier})`;
                sel.appendChild(opt);
                addedBoxNums.add(c.container_number);
            }
        });
    }

    // 2. Also add active 3D containers from containerMeshes that might not be in DB yet
    for (const [bNum, m] of Object.entries(containerMeshes)) {
        if (!addedBoxNums.has(bNum) && m.userData && m.userData.block) {
            const u = m.userData;
            const opt = document.createElement('option');
            opt.value = u.id || bNum;
            opt.dataset.boxNum = bNum;
            opt.textContent = `📦 ${bNum} — ${u.owner_company || 'Port Yard'} (${u.block}-${u.bay}-${u.row}-${u.tier})`;
            sel.appendChild(opt);
            addedBoxNums.add(bNum);
        }
    }

    if (currentVal) sel.value = currentVal;
}

function handleMoveSubmit(e) {
    e.preventDefault();
    if (isAnimating) {
        showToast('Peringatan', 'Sedang ada operasi alat berat berlangsung. Tunggu sebentar!', 'warn');
        return;
    }

    const sel = document.getElementById('moveContainerSelect');
    const selectedOpt = sel.options[sel.selectedIndex];
    const containerId = sel.value;
    const boxNumber = selectedOpt ? (selectedOpt.dataset.boxNum || selectedOpt.textContent.split(' ')[1]) : '';
    const equipmentId = document.getElementById('moveEquipmentSelect').value;
    const toBlock     = document.getElementById('moveToBlock').value;
    const toBay       = document.getElementById('moveToBay').value;
    const toRow       = document.getElementById('moveToRow').value;
    const toTier      = document.getElementById('moveToTier').value;

    const tMesh = containerMeshes[boxNumber];
    const fromBlock = (tMesh && tMesh.userData) ? (tMesh.userData.block || 'A') : 'A';
    const fromBay   = (tMesh && tMesh.userData) ? (tMesh.userData.bay || '01') : '01';
    const fromRow   = (tMesh && tMesh.userData) ? (tMesh.userData.row || '01') : '01';
    const fromTier  = (tMesh && tMesh.userData) ? (tMesh.userData.tier || '01') : '01';
    const ownerComp = (tMesh && tMesh.userData && tMesh.userData.owner_company) ? tMesh.userData.owner_company : 'Ocean Carrier';

    const btn = document.getElementById('btnSubmitMove');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Memproses...';

    // Call REST API
    fetch('api/simulator_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'move_container',
            container_id: containerId,
            container_number: boxNumber,
            equipment_id: equipmentId,
            from_block: fromBlock,
            from_bay: fromBay,
            from_row: fromRow,
            from_tier: fromTier,
            owner_company: ownerComp,
            to_block: toBlock,
            to_bay: toBay,
            to_row: toRow,
            to_tier: toTier
        })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-play mr-1"></i> Jalankan Relokasi Box';

        if (data.success) {
            closeMoveModal();
            closeInspector();
            showToast('Relokasi Berhasil', data.message, 'success');
            logTicker(`[LIFT-OFF] ${data.equipment.equipment_id} mengangkat ${data.container.container_number} -> ${data.container.to}`);
            
            // Trigger 3D Reach Stacker Animation with physical attachment!
            animateReachStackerMove(data.container.container_number, data.container.from, data.container.to, equipmentId);
        } else {
            showToast('Slot Penuh / Gagal', data.message, 'error');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-play mr-1"></i> Jalankan Relokasi Box';
        showToast('Error Sistem', err.message, 'error');
    });
}

// Reach Stacker Moving & Stacking Animation with REAL-WORLD Physical Attachment
function animateReachStackerMove(boxNumber, fromSlot, toSlot, equipmentId) {
    let targetMesh = containerMeshes[boxNumber];
    if (!targetMesh) {
        // Fallback search across meshes
        for (const [k, m] of Object.entries(containerMeshes)) {
            if (m.userData && (m.userData.container_number === boxNumber || m.userData.id == boxNumber)) {
                targetMesh = m;
                boxNumber = k;
                break;
            }
        }
    }
    
    // Parse target coordinates
    const toParts = toSlot.split('-');
    const toBlock = toParts[0];
    const toBay = parseInt(toParts[1], 10) || 1;
    const toRow = parseInt(toParts[2], 10) || 1;
    const toTier = parseInt(toParts[3], 10) || 1;

    // Parse from coordinates
    const fromParts = fromSlot.split('-');
    const fromBlock = fromParts[0] || (targetMesh && targetMesh.userData ? targetMesh.userData.block : 'A');
    const fromBay = parseInt(fromParts[1], 10) || (targetMesh && targetMesh.userData ? parseInt(targetMesh.userData.bay, 10) : 1);
    const fromRow = parseInt(fromParts[2], 10) || (targetMesh && targetMesh.userData ? parseInt(targetMesh.userData.row, 10) : 1);
    const fromTier = parseInt(fromParts[3], 10) || (targetMesh && targetMesh.userData ? parseInt(targetMesh.userData.tier, 10) : 1);

    const is20ft = targetMesh && targetMesh.userData && targetMesh.userData.size_type && targetMesh.userData.size_type.includes('20');
    
    // If targetMesh still somehow not created, spawn it at fromSlot!
    if (!targetMesh) {
        const fromPos = getSlotWorldPosition(fromBlock, fromBay, fromRow, fromTier, is20ft);
        targetMesh = createContainerMesh(is20ft ? 6.0 : 12.0, 2.60, 2.44, SHIPPING_COLORS['Maersk'], boxNumber);
        targetMesh.position.set(fromPos.x, fromPos.y, fromPos.z);
        targetMesh.userData = { container_number: boxNumber, block: fromBlock, bay: String(fromBay).padStart(2,'0'), row: String(fromRow).padStart(2,'0'), tier: String(fromTier).padStart(2,'0'), status: 'in_yard' };
        scene.add(targetMesh);
        containerMeshes[boxNumber] = targetMesh;
    }

    const rsMesh = equipmentMeshes[equipmentId] || equipmentMeshes['RS-02'] || equipmentMeshes['RS-01'];
    if (!rsMesh) {
        fetchSimulationState();
        return;
    }

    isAnimating = true;
    const targetPos = getSlotWorldPosition(toBlock, toBay, toRow, toTier, is20ft);
    const targetX = targetPos.x;
    const targetY = targetPos.y;
    const targetZ = targetPos.z;

    const boom = rsMesh.getObjectByName('rsBoom');
    const spreader = rsMesh.getObjectByName('rsSpreader');

    showOpFlowHUD(3, `RELOKASI: ${equipmentId} MEMINDAHKAN ${boxNumber}`, `Menjemput kontainer di ${fromSlot} dan menumpuk di ${toSlot} (Tier ${toTier})...`);
    focusLocation('yard', `Relokasi: ${equipmentId} Menjemput ${boxNumber} (${fromSlot})`, { x: targetMesh.position.x, y: 0, z: targetMesh.position.z }, equipmentId);
    logTicker(`[RELOKASI] ${equipmentId} bergerak mendekati slot ${fromSlot} untuk mengangkut kontainer ${boxNumber}...`);

    // Source world coordinates
    const srcX = targetMesh.position.x;
    const srcY = targetMesh.position.y;
    const srcZ = targetMesh.position.z;

    // Step 1: RS approaches container and aligns spreader over the top
    // Reach Stacker faces +X (rotation Y = 0) with spreader at offset +7.5m along X
    rsMesh.rotation.set(0, 0, 0);
    new TWEEN.Tween(rsMesh.position)
        .to({ x: srcX - 7.5, z: srcZ }, dur(1200))
        .easing(TWEEN.Easing.Quadratic.InOut)
        .onComplete(() => {
            // Step 2: Spreader tilts and lowers down onto container
            if (boom) new TWEEN.Tween(boom.rotation).to({ z: Math.PI / 18 }, dur(400)).start();
            
            setTimeout(() => {
                playSimSound('twistlock');
                logTicker(`[TWISTLOCK ENGAGED] 4 Corner casting ${boxNumber} terkunci sempurna ke spreader ${equipmentId}.`);

                // Step 3: ATTACH CONTAINER TO REACH STACKER!
                // Using Three.js attach: preserves world transform while parenting to rsMesh!
                rsMesh.attach(targetMesh);

                // Step 4: Lift container up in local RS space to clear stacks (Tier 4+ safety height)
                if (boom) new TWEEN.Tween(boom.rotation).to({ z: Math.PI / 6 }, dur(600)).start();
                new TWEEN.Tween(targetMesh.position)
                    .to({ y: targetMesh.position.y + 6.0 }, dur(700))
                    .easing(TWEEN.Easing.Quadratic.Out)
                    .onComplete(() => {
                        logTicker(`[LIFT-OFF] Kontainer ${boxNumber} diangkat ke ketinggian bebas rintangan (Tier 4+ Clearance).`);
                        focusLocation('yard', `Relokasi: Membawa ${boxNumber} ke ${toSlot}`, { x: targetX, y: 0, z: targetZ }, `TARGET ${toBlock}`);

                        // Step 5: RS travels with container attached along terminal haul road
                        // Intermediate waypoints:
                        // Waypoint 1: Back out to transfer road (Z = 28 or Z = -5)
                        const haulZ = (srcZ > 0 && targetZ > 0) ? 28 : ((srcZ < 0 && targetZ < 0) ? -39 : -5);
                        
                        new TWEEN.Tween(rsMesh.position)
                            .to({ x: srcX - 10, z: haulZ }, dur(900))
                            .easing(TWEEN.Easing.Quadratic.InOut)
                            .onComplete(() => {
                                // Waypoint 2: Drive along haul road to destination X
                                new TWEEN.Tween(rsMesh.position)
                                    .to({ x: targetX - 7.5, z: haulZ }, dur(1400))
                                    .easing(TWEEN.Easing.Quadratic.InOut)
                                    .onComplete(() => {
                                        // Waypoint 3: Turn into destination bay aisle at (targetX - 7.5, targetZ)
                                        new TWEEN.Tween(rsMesh.position)
                                            .to({ x: targetX - 7.5, z: targetZ }, dur(900))
                                            .easing(TWEEN.Easing.Quadratic.InOut)
                                            .onComplete(() => {
                                                logTicker(`[STACKING] ${equipmentId} menempatkan ${boxNumber} di ${toSlot} (Tier ${toTier})...`);

                                                // Step 6: Lower spreader and container smoothly to target tier elevation
                                                if (boom) new TWEEN.Tween(boom.rotation).to({ z: Math.PI / 16 }, dur(500)).start();
                                                
                                                new TWEEN.Tween(targetMesh.position)
                                                    .to({ y: targetY }, dur(800))
                                                    .easing(TWEEN.Easing.Quadratic.InOut)
                                                    .onComplete(() => {
                                                        playSimSound('thud');
                                                        playSimSound('twistlock');

                                                        // Step 7: DETACH CONTAINER & LOCK PERMANENTLY AT TARGET WORLD POSITION
                                                        scene.attach(targetMesh);
                                                        targetMesh.position.set(targetX, targetY, targetZ);
                                                        targetMesh.rotation.set(0, 0, 0);

                                                        // Update metadata
                                                        targetMesh.userData.block = toBlock;
                                                        targetMesh.userData.bay = String(toBay).padStart(2, '0');
                                                        targetMesh.userData.row = String(toRow).padStart(2, '0');
                                                        targetMesh.userData.tier = String(toTier).padStart(2, '0');
                                                        containerMeshes[boxNumber] = targetMesh;

                                                        logTicker(`[STACKED SUKSES] Kontainer ${boxNumber} resmi ditumpuk fisik di ${toBlock}-${toBay}-${toRow}-${toTier}.`);
                                                        showToast('Relokasi Berhasil', `Kontainer ${boxNumber} sukses ditumpuk di ${toBlock}-${toBay}-${toRow}-${toTier} (Tier ${toTier})!`, 'success');

                                                        // Step 8: RS disengages and returns to standby
                                                        if (boom) new TWEEN.Tween(boom.rotation).to({ z: Math.PI / 10 }, dur(400)).start();
                                                        new TWEEN.Tween(rsMesh.position)
                                                            .to({ x: targetX - 12, z: targetZ }, dur(800))
                                                            .easing(TWEEN.Easing.Quadratic.InOut)
                                                            .onComplete(() => {
                                                                isAnimating = false;
                                                                fetchSimulationState();
                                                                setTimeout(() => hideOpFlowHUD(), 3000);
                                                            })
                                                            .start();
                                                    })
                                                    .start();
                                            })
                                            .start();
                                    })
                                    .start();
                            })
                            .start();
                    })
                    .start();
            }, dur(600));
        })
        .start();
}

// =============================================================================
// SPEED CONTROLLER & OPERATIONAL HUD HELPERS
// =============================================================================
// =============================================================================
// WEB AUDIO API REALISTIC PORT MECHANICAL SOUND SYNTHESIZER
// =============================================================================
let simAudioCtx = null;
function getSimAudioContext() {
    if (!simAudioCtx) {
        const AudioClass = window.AudioContext || window.webkitAudioContext;
        if (AudioClass) simAudioCtx = new AudioClass();
    }
    if (simAudioCtx && simAudioCtx.state === 'suspended') {
        simAudioCtx.resume();
    }
    return simAudioCtx;
}

function playSimSound(type) {
    try {
        const ctx = getSimAudioContext();
        if (!ctx) return;

        if (type === 'brake') {
            // Pneumatic Air Brake Release Hiss ("Psssshhhh")
            const bufferSize = Math.floor(ctx.sampleRate * 0.55);
            const buffer = ctx.createBuffer(1, bufferSize, ctx.sampleRate);
            const data = buffer.getChannelData(0);
            for (let i = 0; i < bufferSize; i++) {
                data[i] = Math.random() * 2 - 1;
            }
            const noise = ctx.createBufferSource();
            noise.buffer = buffer;

            const filter = ctx.createBiquadFilter();
            filter.type = 'bandpass';
            filter.frequency.setValueAtTime(1600, ctx.currentTime);
            filter.Q.setValueAtTime(1.8, ctx.currentTime);

            const gain = ctx.createGain();
            gain.gain.setValueAtTime(0.16, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);

            noise.connect(filter);
            filter.connect(gain);
            gain.connect(ctx.destination);
            noise.start();
        } else if (type === 'twistlock') {
            // Heavy Metallic Clank / Twistlock Latch Lock
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(140, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(50, ctx.currentTime + 0.18);

            gain.gain.setValueAtTime(0.35, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.22);

            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.22);

            // Metal click overtone
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'square';
            osc2.frequency.setValueAtTime(900, ctx.currentTime);
            osc2.frequency.exponentialRampToValueAtTime(250, ctx.currentTime + 0.08);

            gain2.gain.setValueAtTime(0.2, ctx.currentTime);
            gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.09);

            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start();
            osc2.stop(ctx.currentTime + 0.09);
        } else if (type === 'beep') {
            // Terminal Scanner RFID/OCR Confirmation Beep
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(1300, ctx.currentTime);
            gain.gain.setValueAtTime(0.12, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.12);

            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.12);
        } else if (type === 'thud') {
            // Container Stacking Solid Landing Impact
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(75, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(28, ctx.currentTime + 0.22);

            gain.gain.setValueAtTime(0.3, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.25);

            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.25);
        } else if (type === 'barrier') {
            // Electric Barrier Motor Buzz
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(160, ctx.currentTime);

            gain.gain.setValueAtTime(0.06, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);

            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.4);
        }
    } catch (e) {
        // Audio might be waiting for user interaction, safely bypass
    }
}

// =============================================================================
// SPEED CONTROLLER & OPERATIONAL HUD HELPERS
// =============================================================================
function dur(ms) {
    return Math.round(ms / (simSpeedMultiplier || 1.25));
}

function toggleSimSpeed() {
    if (simSpeedMultiplier < 1.1) {
        simSpeedMultiplier = 1.75;
    } else if (simSpeedMultiplier < 2.0) {
        simSpeedMultiplier = 2.5;
    } else {
        simSpeedMultiplier = 1.0;
    }
    const txt = simSpeedMultiplier.toFixed(2).replace('.00', '') + 'x';
    const btn = document.getElementById('speedBtnText');
    if (btn) btn.innerText = txt;
    const hudSpd = document.getElementById('opFlowSpeedText');
    if (hudSpd) hudSpd.innerText = txt;
    showToast('Kecepatan Simulasi', `Kecepatan diatur ke ${txt}`, 'info');
}

function showOpFlowHUD(step, title, detail) {
    const hud = document.getElementById('opFlowHUD');
    if (!hud) return;
    hud.classList.remove('opacity-0', 'pointer-events-none');
    hud.classList.add('opacity-100');

    const titleEl = document.getElementById('opFlowTitle');
    const badgeEl = document.getElementById('opFlowStepBadge');
    const detailEl = document.getElementById('opFlowDetailText');

    if (titleEl) titleEl.innerText = title;
    if (badgeEl) badgeEl.innerText = `TAHAP ${step}/4`;
    if (detailEl) detailEl.innerText = detail;

    for (let s = 1; s <= 4; s++) {
        const stepEl = document.getElementById(`flowStep${s}`);
        if (!stepEl) continue;
        if (s === step) {
            stepEl.className = 'py-1 px-0.5 rounded-lg bg-blue-600 text-white font-bold border border-blue-400 shadow-sm animate-pulse';
        } else if (s < step) {
            stepEl.className = 'py-1 px-0.5 rounded-lg bg-emerald-900/80 text-emerald-200 border border-emerald-500/50 font-semibold';
        } else {
            stepEl.className = 'py-1 px-0.5 rounded-lg bg-slate-800 text-slate-400 border border-slate-700/50 font-semibold';
        }
    }
}

function hideOpFlowHUD() {
    const hud = document.getElementById('opFlowHUD');
    if (!hud) return;
    hud.classList.remove('opacity-100');
    hud.classList.add('opacity-0', 'pointer-events-none');
}

// =============================================================================
// ALUR OPERASIONAL NYATA TRUK (REAL-WORLD CONTINUOUS 4-STAGE FLOW)
// 1. Gate-In & Timbangan 80T -> 2. Sirkulasi Yard -> 3. Angkat RS -> 4. Gate-Out
// =============================================================================
function triggerGateIn(type = 'drop_off') {
    if (!truckMesh || isAnimating) return;
    isAnimating = true;

    const isPickup = (type === 'pick_up');
    const misiText = isPickup ? 'PICK-UP (AMBIL BARANG)' : 'DROP-OFF (ANTAR BARANG)';

    // Persiapan model visual armada (Drop-off membawa box 40ft, Pick-up masuk sasis kosong)
    if (truckBoxMesh) {
        truckBoxMesh.visible = !isPickup;
    }

    // Posisi awal di jalan raya pendekatan (Highway Approach Lane 1: X = -49, Z = 75)
    truckMesh.position.set(-49, 0, 75);
    truckMesh.rotation.set(0, Math.PI / 2, 0); // Menghadap Utara (-Z) ke arah Gate-In

    // Stage 1 HUD & Kamera
    showOpFlowHUD(1, '1. GATE-IN & JEMBATAN TIMBANG SOLAS 80T', isPickup 
        ? 'Truk sasis kosong tiba di gerbang, pemindaian e-Pass supir & verifikasi bobot tara...'
        : 'Truk kontainer tiba di gerbang, identifikasi OCR ISO 6346 & timbangan 80T...');
    focusLocation('vgm', `Gate Inbound & Jembatan Timbang 80T [${misiText}]`, { x: -45, y: 0, z: 52 }, `GATE-IN • ${misiText}`);
    logTicker(`[ANPR & RFID] Mendeteksi armada mendekati Inbound Gate Lane 1 (${misiText})...`);

    // 1A. Truk melaju dari jalan raya ke atas platform jembatan timbang 18m (Z = 75 -> 52)
    new TWEEN.Tween(truckMesh.position)
        .to({ z: 52 }, dur(1600))
        .easing(TWEEN.Easing.Quadratic.Out)
        .onComplete(() => {
            playSimSound('brake'); // Suara rem angin psssh
            logTicker(`[WEIGHBRIDGE 80T] Truk berhenti di atas timbangan SOLAS bersertifikasi...`);

            // Panggil API Gate-In & Simpan ke MySQL
            fetch(`api/simulator_action.php?action=gate_in&type=${type}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        playSimSound('beep'); // Beep konfirmasi penimbangan
                        const trk = data.truck;
                        trk.allocated_slot = data.allocated_slot;
                        trk.target_container = data.target_container;
                        const desc = isPickup 
                            ? `${trk.license_plate}: Sasis Kosong (Tare ${trk.tare_weight.toLocaleString()} kg). Izin Pick-Up Kontainer Valid!`
                            : `${trk.license_plate} (${trk.company}): Gross ${trk.gross_weight.toLocaleString()} kg, VGM Net ${trk.vgm_net.toLocaleString()} kg - SOLAS PASS!`;
                        showToast(`Gate-In ${misiText} Sukses`, desc, 'success');
                        logTicker(`[GATE-IN SUKSES] Plat ${trk.license_plate}: Gross ${trk.gross_weight}kg, Status: ${trk.vgm_status}.`);

                        // 1B. Angkat Palang Pintu Gerbang Masuk (Boom Barrier Inbound)
                        if (boomBarrierMesh) {
                            playSimSound('barrier');
                            new TWEEN.Tween(boomBarrierMesh.rotation)
                                .to({ x: -Math.PI / 2.5 }, dur(600))
                                .easing(TWEEN.Easing.Quadratic.Out)
                                .start();
                        }

                        // 1C. Truk melaju melintasi palang masuk (Z = 52 -> 43 -> 28) ke jalan sirkulasi internal
                        new TWEEN.Tween(truckMesh.position)
                            .to({ z: 28 }, dur(1500))
                            .easing(TWEEN.Easing.Quadratic.InOut)
                            .onComplete(() => {
                                // Tutup kembali palang pintu masuk
                                if (boomBarrierMesh) {
                                    new TWEEN.Tween(boomBarrierMesh.rotation)
                                        .to({ x: 0 }, dur(500))
                                        .easing(TWEEN.Easing.Quadratic.In)
                                        .start();
                                }

                                // Lanjut ke Tahap 2: Sirkulasi Yard
                                runStage2_DriveToYard(type, trk);
                            })
                            .start();
                    } else {
                        showToast('Gate-In Gagal', data.message || 'Terjadi kesalahan sistem', 'error');
                        isAnimating = false;
                        hideOpFlowHUD();
                    }
                })
                .catch(err => {
                    console.error('Error gate_in API:', err);
                    isAnimating = false;
                    hideOpFlowHUD();
                });
        })
        .start();
}

function runStage2_DriveToYard(type, truckData) {
    const isPickup = (type === 'pick_up');
    const misiText = isPickup ? 'PICK-UP' : 'DROP-OFF';

    showOpFlowHUD(2, '2. SIRKULASI TERMINAL ROAD KE YARD', isPickup
        ? 'Truk sasis kosong melaju ke transfer lane Blok B (Laden Import) untuk alur pemuatan barang...'
        : 'Truk kontainer melaju di jalan sirkulasi menuju transfer bay Blok A (Laden Export)...');

    // Arahkan kamera ke blok yard tujuan
    if (isPickup) {
        focusLocation('yard', 'Truk Menuju Yard Penumpukan Blok B (Import)', { x: LW('blok_b').x, y: 0, z: 28 }, 'YARD BLOK B');
    } else {
        focusLocation('yard', 'Truk Menuju Yard Penumpukan Blok A (Export)', { x: LW('blok_a').x, y: 0, z: 28 }, 'YARD BLOK A');
    }
    logTicker(`[SIRKULASI YARD] Truk ${truckData.license_plate} melaju di jalan sirkulasi internal menuju transfer lane ${isPickup ? 'Blok B' : 'Blok A'}...`);

    // 2A. Truk berputar ke arah Timur (rotasi Y: Math.PI / 2 -> 0)
    new TWEEN.Tween(truckMesh.rotation)
        .to({ y: 0 }, dur(500))
        .easing(TWEEN.Easing.Quadratic.InOut)
        .onComplete(() => {
            // 2B. Truk melaju di jalan sirkulasi Z = 28 menuju posisi X transfer bay blok
            const targetX = isPickup ? LW('blok_b').x : LW('blok_a').x;
            new TWEEN.Tween(truckMesh.position)
                .to({ x: targetX, z: 28 }, dur(isPickup ? 2400 : 1800))
                .easing(TWEEN.Easing.Quadratic.InOut)
                .onComplete(() => {
                    playSimSound('brake');
                    runStage3_ReachStackerOperation(type, truckData);
                })
                .start();
        })
        .start();
}

function runStage3_ReachStackerOperation(type, truckData) {
    const isPickup = (type === 'pick_up');
    const rsId = isPickup ? 'RS-02' : 'RS-01';
    const rs = equipmentMeshes[rsId] || equipmentMeshes['RS-01'];

    showOpFlowHUD(3, isPickup ? '3. ANGKAT RS-02 (LIFT-ON MUATAN)' : '3. ANGKAT RS-01 (LIFT-OFF MUATAN)', isPickup
        ? 'Reach Stacker RS-02 mengambil peti kemas dari slot Blok B dan menurunkannya ke sasis trailer...'
        : 'Reach Stacker RS-01 mengunci twistlock, mengangkat box dari sasis trailer, dan menumpuk di Blok A...');

    logTicker(`[${rsId}] Reach Stacker bergerak mendekati armada ${truckData.license_plate} untuk eksekusi ${isPickup ? 'Lift-On' : 'Lift-Off'}...`);

    if (!rs) {
        if (isPickup) {
            if (truckBoxMesh) truckBoxMesh.visible = true;
        } else {
            if (truckBoxMesh) truckBoxMesh.visible = false;
        }
        setTimeout(() => runStage4_DriveToGateOut(type, truckData), dur(1500));
        return;
    }

    const boom = rs.getObjectByName('rsBoom');

    if (!isPickup) {
        // =====================================================================
        // DROP-OFF (LIFT-OFF FROM TRUCK & PHYSICALLY STACK ONTO BLOK A)
        // =====================================================================
        // Truk berada di X = 44 (Blok A), Z = 28. RS-01 standby di X = 34, Z = 28.
        // Step 1: RS maju merapat ke truk (X: 34 -> 36.5)
        new TWEEN.Tween(rs.position)
            .to({ x: 36.5 }, dur(900))
            .easing(TWEEN.Easing.Quadratic.InOut)
            .onComplete(() => {
                playSimSound('twistlock');
                logTicker(`[TWISTLOCK SENSOR] Pin twistlock spreader Bromma mengunci 4 corner casting kontainer.`);
                
                // Turunkan boom sedikit ke arah trailer
                if (boom) new TWEEN.Tween(boom.rotation).to({ z: Math.PI / 18 }, dur(400)).start();

                setTimeout(() => {
                    // Angkat kontainer dari sasis truk secara fisik!
                    const cBoxNum = truckData.container_number || ('MSKU' + Math.floor(1000000 + Math.random() * 9000000));
                    const newMesh = createContainerMesh(12.0, 2.60, 2.44, SHIPPING_COLORS['Maersk'], cBoxNum);
                    newMesh.position.set(LW('blok_a').x, 2.45, 28);
                    scene.add(newMesh);

                    // Sasis trailer kini KOSONG
                    if (truckBoxMesh) truckBoxMesh.visible = false;
                    playSimSound('thud');

                    // Kunci kontainer secara fisik ke Reach Stacker!
                    rs.attach(newMesh);

                    logTicker(`[LIFT-OFF] Kontainer ${cBoxNum} diangkat dari sasis truk ${truckData.license_plate} oleh RS-01.`);

                    // Angkat boom dan kontainer ke ketinggian aman rintangan
                    if (boom) new TWEEN.Tween(boom.rotation).to({ z: Math.PI / 7 }, dur(600)).start();
                    new TWEEN.Tween(newMesh.position)
                        .to({ y: newMesh.position.y + 4.5 }, dur(600))
                        .easing(TWEEN.Easing.Quadratic.Out)
                        .start();

                    // Step 2: RS bergerak membawa box yang tergantung ke dalam lorong Blok A
                    const targetBay = (truckData.allocated_slot && truckData.allocated_slot.bay) ? truckData.allocated_slot.bay : '02';
                    const targetRow = (truckData.allocated_slot && truckData.allocated_slot.row) ? truckData.allocated_slot.row : '02';
                    const targetTier = (truckData.allocated_slot && truckData.allocated_slot.tier) ? truckData.allocated_slot.tier : '01';
                    const targetSlotPos = getSlotWorldPosition('A', targetBay, targetRow, targetTier, false);

                    new TWEEN.Tween(rs.position)
                        .to({ x: targetSlotPos.x - 7.5, z: targetSlotPos.z }, dur(1400))
                        .easing(TWEEN.Easing.Quadratic.InOut)
                        .onComplete(() => {
                            // Step 3: Turunkan boom dan kontainer perlahan ke elevasi target tier Blok A
                            if (boom) new TWEEN.Tween(boom.rotation).to({ z: Math.PI / 16 }, dur(500)).start();

                            new TWEEN.Tween(newMesh.position)
                                .to({ y: targetSlotPos.y }, dur(600))
                                .easing(TWEEN.Easing.Quadratic.InOut)
                                .onComplete(() => {
                                    playSimSound('thud');
                                    playSimSound('twistlock');

                                    // Lepas dari RS dan kunci permanen di koordinat dunia Yard Blok A!
                                    scene.attach(newMesh);
                                    newMesh.position.set(targetSlotPos.x, targetSlotPos.y, targetSlotPos.z);
                                    newMesh.rotation.set(0, 0, 0);

                                    newMesh.userData = {
                                        container_number: cBoxNum,
                                        size_type: '40FT HIGH CUBE',
                                        cargo_type: 'dry',
                                        block: 'A',
                                        bay: String(targetBay).padStart(2, '0'),
                                        row: String(targetRow).padStart(2, '0'),
                                        tier: String(targetTier).padStart(2, '0'),
                                        gross_weight_kg: truckData.gross_weight || 28500,
                                        owner_company: truckData.company || 'PT Lintas Samudera',
                                        rfid_tag: 'RFID-CTR-' + cBoxNum.slice(-3),
                                        customs_status: 'SPPB_CLEARED',
                                        status: 'in_yard'
                                    };
                                    containerMeshes[cBoxNum] = newMesh;

                                    logTicker(`[FISIK YARD] Peti kemas ${cBoxNum} resmi ditumpuk fisik di Blok A-${targetBay}-${targetRow}-${targetTier}.`);
                                    showToast('Lift-Off Sukses', `Kontainer ${cBoxNum} fisik ditumpuk di Blok A-${targetBay}-${targetRow}-${targetTier}`, 'success');

                                    // Angkat boom kembali & kembalikan RS ke posisi standby
                                    if (boom) new TWEEN.Tween(boom.rotation).to({ z: Math.PI / 10 }, dur(400)).start();

                                    new TWEEN.Tween(rs.position)
                                        .to({ x: 34, z: 28 }, dur(1000))
                                        .easing(TWEEN.Easing.Quadratic.InOut)
                                        .onComplete(() => {
                                            runStage4_DriveToGateOut(type, truckData);
                                        })
                                        .start();
                                })
                                .start();
                        })
                        .start();
                }, dur(800));
            })
            .start();
    } else {
        // =====================================================================
        // PICK-UP (PHYSICALLY LIFT OFF FROM BLOK B STACK & LOAD ONTO TRUCK)
        // =====================================================================
        // Truk sasis kosong standby di X = -44 (Blok B), Z = 28. RS-02 standby di X = -54, Z = 28.
        let targetBoxNum = (truckData.target_container && truckData.target_container.container_number)
            ? truckData.target_container.container_number
            : 'MSKU4952240';
        
        let targetMesh = containerMeshes[targetBoxNum];
        let pickupBay = 2, pickupRow = 2, pickupTier = 1;

        if (targetMesh && targetMesh.userData) {
            pickupBay = parseInt(targetMesh.userData.bay, 10) || 2;
            pickupRow = parseInt(targetMesh.userData.row, 10) || 2;
            pickupTier = parseInt(targetMesh.userData.tier, 10) || 1;
        } else {
            // Cari kontainer apa saja di Blok B
            for (const [num, m] of Object.entries(containerMeshes)) {
                if (m.userData && m.userData.block === 'B') {
                    targetMesh = m;
                    targetBoxNum = num;
                    pickupBay = parseInt(m.userData.bay, 10) || 2;
                    pickupRow = parseInt(m.userData.row, 10) || 2;
                    pickupTier = parseInt(m.userData.tier, 10) || 1;
                    break;
                }
            }
        }

        const slotPos = getSlotWorldPosition('B', pickupBay, pickupRow, pickupTier, false);

        if (!targetMesh) {
            targetMesh = createContainerMesh(12.0, 2.60, 2.44, SHIPPING_COLORS['CMA CGM'], targetBoxNum);
            targetMesh.position.set(slotPos.x, slotPos.y, slotPos.z);
            targetMesh.userData = { container_number: targetBoxNum, block: 'B', bay: String(pickupBay).padStart(2,'0'), row: String(pickupRow).padStart(2,'0'), tier: String(pickupTier).padStart(2,'0'), status: 'in_yard' };
            scene.add(targetMesh);
            containerMeshes[targetBoxNum] = targetMesh;
        }

        // Step 1: RS-02 bergerak ke slot kontainer di Blok B
        new TWEEN.Tween(rs.position)
            .to({ x: slotPos.x - 7.5, z: slotPos.z }, dur(1100))
            .easing(TWEEN.Easing.Quadratic.InOut)
            .onComplete(() => {
                if (boom) new TWEEN.Tween(boom.rotation).to({ z: Math.PI / 18 }, dur(400)).start();

                setTimeout(() => {
                    playSimSound('twistlock');
                    logTicker(`[LIFT-ON PICKUP] RS-02 mengunci peti kemas ${targetBoxNum} di Blok B...`);

                    // Kunci kontainer secara fisik ke RS-02!
                    rs.attach(targetMesh);

                    // Angkat kontainer ke ketinggian aman
                    if (boom) new TWEEN.Tween(boom.rotation).to({ z: Math.PI / 7 }, dur(600)).start();
                    new TWEEN.Tween(targetMesh.position)
                        .to({ y: targetMesh.position.y + 5.0 }, dur(600))
                        .easing(TWEEN.Easing.Quadratic.Out)
                        .start();

                    // Step 2: RS-02 bergerak membawa kontainer menuju truk di transfer bay (X: -51.5, Z: 28)
                    new TWEEN.Tween(rs.position)
                        .to({ x: -51.5, z: 28 }, dur(1400))
                        .easing(TWEEN.Easing.Quadratic.InOut)
                        .onComplete(() => {
                            // Step 3: Turunkan spreader dan kontainer ke atas sasis trailer
                            if (boom) new TWEEN.Tween(boom.rotation).to({ z: Math.PI / 16 }, dur(500)).start();

                            new TWEEN.Tween(targetMesh.position)
                                .to({ y: 2.45 }, dur(600))
                                .easing(TWEEN.Easing.Quadratic.InOut)
                                .onComplete(() => {
                                    playSimSound('thud');
                                    playSimSound('twistlock');

                                    // Lepas dari lapangan dan pasang di sasis truk trailer!
                                    scene.remove(targetMesh);
                                    delete containerMeshes[targetBoxNum];

                                    // Truk kini bermuatan fisik!
                                    if (truckBoxMesh) truckBoxMesh.visible = true;
                                    logTicker(`[LIFT-ON SUKSES] Peti kemas ${targetBoxNum} terpasang di sasis truk ${truckData.license_plate}.`);
                                    showToast('Lift-On Sukses', `Peti kemas ${targetBoxNum} berhasil dimuat ke armada ${truckData.license_plate}`, 'success');

                                    if (boom) new TWEEN.Tween(boom.rotation).to({ z: Math.PI / 10 }, dur(400)).start();

                                    new TWEEN.Tween(rs.position)
                                        .to({ x: -54, z: 28 }, dur(900))
                                        .easing(TWEEN.Easing.Quadratic.InOut)
                                        .onComplete(() => {
                                            runStage4_DriveToGateOut(type, truckData);
                                        })
                                        .start();
                                })
                                .start();
                        })
                        .start();
                }, dur(700));
            })
            .start();
    }
}

function runStage4_DriveToGateOut(type, truckData) {
    const isPickup = (type === 'pick_up');
    const misiText = isPickup ? 'PICK-UP' : 'DROP-OFF';

    showOpFlowHUD(4, '4. GATE-OUT & PENYELESAIAN MISI', isPickup
        ? 'Truk bermuatan kontainer impor menuju Outbound Gate, validasi e-Seal & berangkat ke jalan tol...'
        : 'Truk sasis kosong menuju Outbound Gate setelah menyelesaikan proses penurunan kontainer...');

    focusLocation('vgm', 'Gerbang Keluar (Outbound Gate & Pemeriksaan Akhir)', { x: -41, y: 0, z: 43 }, 'OUTBOUND GATE');
    logTicker(`[GATE-OUT] Truk ${truckData.license_plate} bergerak dari yard penumpukan menuju Outbound Gate Lane 2...`);

    // 4A. Truk berputar menghadap Barat (rotasi Y: 0 -> Math.PI)
    new TWEEN.Tween(truckMesh.rotation)
        .to({ y: Math.PI }, dur(500))
        .easing(TWEEN.Easing.Quadratic.InOut)
        .onComplete(() => {
            // 4B. Truk melaju di jalan sirkulasi ke koridor gerbang keluar Lane 2 (X: targetX -> -41)
            new TWEEN.Tween(truckMesh.position)
                .to({ x: -41, z: 28 }, dur(1800))
                .easing(TWEEN.Easing.Quadratic.InOut)
                .onComplete(() => {
                    // 4C. Truk berputar ke arah Selatan / Gerbang Keluar (rotasi Y: Math.PI -> -Math.PI / 2)
                    new TWEEN.Tween(truckMesh.rotation)
                        .to({ y: -Math.PI / 2 }, dur(500))
                        .easing(TWEEN.Easing.Quadratic.InOut)
                        .onComplete(() => {
                            // 4D. Truk melaju ke posisi pos palang keluar (Z: 28 -> 43)
                            new TWEEN.Tween(truckMesh.position)
                                .to({ z: 43 }, dur(1200))
                                .easing(TWEEN.Easing.Quadratic.Out)
                                .onComplete(() => {
                                    playSimSound('brake');
                                    logTicker(`[OUTBOUND GATE] Memindai e-Pass keluar & verifikasi surat jalan elektronik...`);

                                    // Eksekusi API Gate-Out & sinkronisasi MySQL
                                    fetch('api/simulator_action.php?action=gate_out')
                                        .then(res => res.json())
                                        .then(outData => {
                                            playSimSound('beep');
                                            // 4E. Angkat Palang Gerbang Keluar (Lane 2)
                                            if (outboundBarrierMesh) {
                                                playSimSound('barrier');
                                                new TWEEN.Tween(outboundBarrierMesh.rotation)
                                                    .to({ x: -Math.PI / 2.5 }, dur(600))
                                                    .easing(TWEEN.Easing.Quadratic.Out)
                                                    .start();
                                            }

                                            // 4F. Truk melaju melintasi gerbang keluar menuju jalan raya (Z: 43 -> 85)
                                            new TWEEN.Tween(truckMesh.position)
                                                .to({ z: 85 }, dur(2000))
                                                .easing(TWEEN.Easing.Quadratic.In)
                                                .onComplete(() => {
                                                    // Tutup kembali palang gerbang keluar
                                                    if (outboundBarrierMesh) {
                                                        new TWEEN.Tween(outboundBarrierMesh.rotation)
                                                            .to({ x: 0 }, dur(500))
                                                            .easing(TWEEN.Easing.Quadratic.In)
                                                            .start();
                                                    }

                                                    showToast(`Misi ${misiText} 100% Selesai!`, `Alur operasional lapangan tuntas dan tersinkronisasi di MySQL.`, 'success');
                                                    logTicker(`[OPERASIONAL SUKSES] Truk ${truckData.license_plate} resmi Gate-Out. Status armada diselesaikan di database.`);

                                                    // Kembalikan posisi standby truk di highway untuk demo berikutnya (Lane 1 Approach: X = -49, Z = 75)
                                                    truckMesh.position.set(-49, 0, 75);
                                                    truckMesh.rotation.set(0, Math.PI / 2, 0);
                                                    if (truckBoxMesh) truckBoxMesh.visible = true;

                                                    isAnimating = false;
                                                    fetchSimulationState();

                                                    setTimeout(() => {
                                                        hideOpFlowHUD();
                                                    }, 3000);
                                                })
                                                .start();
                                        })
                                        .catch(err => {
                                            console.error('Error gate_out API:', err);
                                            isAnimating = false;
                                            hideOpFlowHUD();
                                        });
                                })
                                .start();
                        })
                        .start();
                })
                .start();
        })
        .start();
}

// =============================================================================
// AKSI 2B: SIMULASI GATE-OUT TRUK MANDIRI (JIKA TRUK SUDAH DI DALAM YARD)
// =============================================================================
function triggerGateOut() {
    if (!truckMesh || isAnimating) return;
    isAnimating = true;

    showOpFlowHUD(4, '4. GATE-OUT ARMADA MANDIRI', 'Truk di dalam terminal menuju Outbound Gate untuk proses checkout...');
    focusLocation('vgm', 'Gate Outbound: Pemeriksaan Akhir & Barrier Keluar', { x: -41, y: 0, z: 43 }, 'GATE-OUT TERMINAL');
    logTicker(`[GATE-OUT] Truk menyelesaikan operasional dan menuju gerbang keluar Lane 2...`);

    // Posisikan truk di jalur keluar menuju barrier Lane 2 (X = -41)
    truckMesh.position.set(-41, 0, 28);
    truckMesh.rotation.set(0, -Math.PI / 2, 0);

    new TWEEN.Tween(truckMesh.position)
        .to({ z: 43 }, dur(1200))
        .easing(TWEEN.Easing.Quadratic.InOut)
        .onComplete(() => {
            playSimSound('brake');
            fetch('api/simulator_action.php?action=gate_out')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        playSimSound('beep');
                        const trk = data.truck;
                        showToast('Gate-Out Selesai', data.message, 'success');
                        logTicker(`[GATE-OUT] Truk ${trk.license_plate} sukses Gate-Out. Meninggalkan area pelabuhan kering.`);

                        if (outboundBarrierMesh) {
                            playSimSound('barrier');
                            new TWEEN.Tween(outboundBarrierMesh.rotation)
                                .to({ x: -Math.PI / 2.5 }, dur(600))
                                .easing(TWEEN.Easing.Quadratic.Out)
                                .start();
                        }

                        new TWEEN.Tween(truckMesh.position)
                            .to({ z: 85 }, dur(2000))
                            .easing(TWEEN.Easing.Quadratic.In)
                            .onComplete(() => {
                                if (outboundBarrierMesh) {
                                    new TWEEN.Tween(outboundBarrierMesh.rotation)
                                        .to({ x: 0 }, dur(500))
                                        .start();
                                }
                                truckMesh.position.set(-49, 0, 75);
                                truckMesh.rotation.set(0, Math.PI / 2, 0);
                                if (truckBoxMesh) truckBoxMesh.visible = true;

                                isAnimating = false;
                                fetchSimulationState();
                                setTimeout(() => hideOpFlowHUD(), 2500);
                            })
                            .start();
                    } else {
                        showToast('Info Gate-Out', data.message || 'Tidak ada armada truk yang siap keluar.', 'info');
                        isAnimating = false;
                        hideOpFlowHUD();
                    }
                })
                .catch(e => {
                    console.error(e);
                    isAnimating = false;
                    hideOpFlowHUD();
                });
        })
        .start();
}

// =============================================================================
// AKSI 3: SIMULASI BONGKAR MUAT KA LOGISTIK OLEH RTG CRANE (MULTI-STAGE)
// =============================================================================
function triggerRailDischarge() {
    const rtg = equipmentMeshes['RTG-01'];
    if (!rtg || isAnimating) return;

    isAnimating = true;
    focusLocation('rail', 'Alih Muat Kereta Api Logistik (RTG-01 Crane)', { x: 18, y: 0, z: -46 }, 'RTG-01 & RAIL');
    logTicker(`[RTG-01] Memulai alih muat kontainer dari Rangkaian Kereta Api...`);

    const trolley = rtg.getObjectByName('rtgTrolley');
    const spreader = rtg.getObjectByName('rtgSpreader');

    // 1. RTG Gantry bergeser ke gerbong 1 (X = 18)
    new TWEEN.Tween(rtg.position)
        .to({ x: 18 }, dur(1400))
        .easing(TWEEN.Easing.Quadratic.InOut)
        .onComplete(() => {
            logTicker(`[RTG-01] Menurunkan spreader teleskopik ke arah gerbong datar KA di X: 18...`);
            
            // 2. Turunkan spreader ke kontainer gerbong KA (Y: 12 -> 3.2)
            if (spreader) {
                new TWEEN.Tween(spreader.position)
                    .to({ y: 3.2 }, dur(1000))
                    .easing(TWEEN.Easing.Quadratic.InOut)
                    .onComplete(() => {
                        playSimSound('twistlock');
                        logTicker(`[RTG-01 TWISTLOCK] Spreader mengunci 4 sudut peti kemas KA.`);
                        
                        setTimeout(() => {
                            // --- SECARA FISIK DIANGKAT DARI GERBONG KA (GERBONG MENJADI KOSONG) ---
                            if (window.trainContainerBoxes && window.trainContainerBoxes[1]) {
                                window.trainContainerBoxes[1].visible = false;
                                logTicker(`[FISIK KA] Gerbong datar #2 kini KOSONG (kontainer diangkat oleh RTG-01).`);
                            }

                            // Buat mesh kontainer yang tergantung langsung di bawah Spreader RTG!
                            const carriedRailBox = createContainerMesh(12.0, 2.60, 2.44, SHIPPING_COLORS['Evergreen'], 'KAIU8821940');
                            carriedRailBox.position.set(0, -1.6, 0);
                            spreader.add(carriedRailBox);

                            // 3. Angkat kontainer ke atas (Y: 3.2 -> 13)
                            new TWEEN.Tween(spreader.position)
                                .to({ y: 13 }, dur(1000))
                                .easing(TWEEN.Easing.Quadratic.InOut)
                                .onComplete(() => {
                                    logTicker(`[RTG-01 TROLLEY] Trolley melintasi gantry membawa peti kemas menuju buffer yard...`);
                                    
                                    // 4. Trolley & Spreader bergeser melintang ke arah buffer yard (Z: 0 -> 8)
                                    if (trolley) {
                                        new TWEEN.Tween(trolley.position)
                                            .to({ z: 8 }, dur(1200))
                                            .easing(TWEEN.Easing.Quadratic.InOut)
                                            .start();
                                    }
                                    new TWEEN.Tween(spreader.position)
                                        .to({ z: 8 }, dur(1200))
                                        .easing(TWEEN.Easing.Quadratic.InOut)
                                        .onComplete(() => {
                                            // 5. Turunkan kontainer ke slot buffer yard (Y: 13 -> 1.5)
                                            new TWEEN.Tween(spreader.position)
                                                .to({ y: 1.5 }, dur(1000))
                                                .easing(TWEEN.Easing.Quadratic.InOut)
                                                .onComplete(() => {
                                                    playSimSound('thud');
                                                    playSimSound('twistlock');

                                                    // Panggil API Rail Discharge & update MySQL
                                                    fetch('api/simulator_action.php?action=rail_discharge')
                                                        .then(res => res.json())
                                                        .then(data => {
                                                            if (data.success) {
                                                                showToast('Alih Muat KA Berhasil', data.message, 'success');
                                                                const cNum = data.container.container_number;
                                                                const toBlk = data.container.block || 'D';
                                                                const toBay = data.container.bay || '02';
                                                                const toRow = data.container.row || '01';
                                                                const toTier = data.container.tier || '01';

                                                                // --- SECARA FISIK DITAMBAHKAN KE LAPANGAN BUFFER YARD ---
                                                                const bPos = getSlotWorldPosition(toBlk, toBay, toRow, toTier, false);
                                                                
                                                                // Lepas dari spreader RTG dan tempatkan di koordinat slot dunia!
                                                                spreader.remove(carriedRailBox);
                                                                carriedRailBox.position.set(bPos.x, bPos.y, bPos.z);
                                                                carriedRailBox.rotation.set(0, 0, 0);
                                                                scene.add(carriedRailBox);

                                                                carriedRailBox.userData = {
                                                                    container_number: cNum,
                                                                    size_type: '40FT HIGH CUBE',
                                                                    cargo_type: 'dry',
                                                                    block: toBlk,
                                                                    bay: String(toBay).padStart(2, '0'),
                                                                    row: String(toRow).padStart(2, '0'),
                                                                    tier: String(toTier).padStart(2, '0'),
                                                                    owner_company: 'PT Kereta Api Logistik',
                                                                    gross_weight_kg: 26400,
                                                                    rfid_tag: 'RFID-CTR-' + cNum.slice(-3),
                                                                    customs_status: 'SPPB_CLEARED',
                                                                    status: 'in_yard'
                                                                };
                                                                containerMeshes[cNum] = carriedRailBox;

                                                                logTicker(`[FISIK BUFFER] Peti kemas ${cNum} resmi ditempatkan fisik di ${toBlk}-${toBay}-${toRow}-${toTier}.`);

                                                                // 6. Angkat spreader kembali & pulihkan posisi RTG
                                                                new TWEEN.Tween(spreader.position)
                                                                    .to({ y: 12 }, dur(800))
                                                                    .easing(TWEEN.Easing.Quadratic.InOut)
                                                                    .onComplete(() => {
                                                                        if (trolley) {
                                                                            new TWEEN.Tween(trolley.position).to({ z: 0 }, dur(900)).start();
                                                                        }
                                                                        new TWEEN.Tween(spreader.position).to({ z: 0 }, dur(900)).start();

                                                                        new TWEEN.Tween(rtg.position)
                                                                            .to({ x: 0 }, dur(1000))
                                                                            .easing(TWEEN.Easing.Quadratic.InOut)
                                                                            .onComplete(() => {
                                                                                isAnimating = false;
                                                                                fetchSimulationState();
                                                                            })
                                                                            .start();
                                                                    })
                                                                    .start();
                                                            } else {
                                                                isAnimating = false;
                                                            }
                                                        })
                                                        .catch(err => {
                                                            console.error(err);
                                                            isAnimating = false;
                                                        });
                                                })
                                                .start();
                                        })
                                        .start();
                                })
                                .start();
                        }, dur(800));
                    })
                    .start();
            } else {
                isAnimating = false;
            }
        })
        .start();
}

// =============================================================================
// RESET SIMULATION
// =============================================================================
function resetSimulation() {
    if (!confirm('Apakah Anda ingin mereset seluruh posisi kontainer dan alat berat kembali ke konfigurasi awal demo?')) return;

    fetch('api/simulator_action.php?action=reset_simulation')
        .then(res => res.json())
        .then(data => {
            focusLocation('overview', 'Master Plan CIDP 35 Hektar (Konfigurasi Awal)', { x: 0, y: 0, z: 0 }, 'TERMINAL OVERVIEW');
            showToast('Reset Berhasil', data.message, 'info');
            logTicker(`[RESET] Seluruh posisi terminal dikembalikan ke konfigurasi awal demo.`);
            closeInspector();
            fetchSimulationState();
        });
}

function refreshStateFromDB() {
    fetchSimulationState();
    showToast('Sinkronisasi', 'Data terminal berhasil diperbarui dari MySQL.', 'info');
    logTicker(`[SYNC] Sinkronisasi data basis data MySQL selesai.`);
}

// =============================================================================
// CAMERA PRESETS & ENVIRONMENT CONTROLS (CAD MULTI-VIEW ENGINE)
// =============================================================================
function setCameraView(preset) {
    document.querySelectorAll('.cam-btn').forEach(btn => {
        btn.classList.remove('bg-[#002f5e]', 'bg-blue-600', 'text-white', 'shadow-xs', 'border-[#002f5e]');
        btn.classList.add('bg-gray-100', 'text-gray-700', 'border-gray-200');
    });

    const activateBtn = (id) => {
        const b = document.getElementById(id);
        if (b) {
            b.classList.remove('bg-gray-100', 'text-gray-700', 'border-gray-200');
            b.classList.add('bg-[#002f5e]', 'text-white', 'border-[#002f5e]', 'shadow-xs');
        }
    };

    const setProjectionText = (txt) => {
        const p1 = document.getElementById('cadProjectionLabel');
        const p2 = document.getElementById('cadProjectionLabelDetail');
        if (p1) p1.innerText = txt;
        if (p2) p2.innerText = txt;
    };

    let targetPos, targetLook;

    switch(preset) {
        case 'plan':
            // Denah 2D Top-Down CAD: Kamera melihat lurus ke bawah, menatap dari Selatan ke Utara sehingga Kantor Utama & Gate berada di ATAS (Utara), Rel Siding di BAWAH (Selatan) persis seperti denah.php
            targetPos = { x: 0, y: 180, z: -0.01 };
            targetLook = { x: 0, y: 0, z: 0 };
            activateBtn('btnCamPlan');
            setProjectionText('ORTOGRAFIK 2D (CAD PLAN - UTARA DI ATAS)');
            break;
        case 'iso':
            // Sudut Isometrik 45° dari arah Barat Daya menatap Timur Laut (Barat = +X dunia)
            targetPos = { x: 105, y: 90, z: -115 };
            targetLook = { x: 0, y: 0, z: 10 };
            activateBtn('btnCamIso');
            setProjectionText('AKSONOMETRI ISOMETRIK 45° BIM');
            break;
        case 'overview':
            // Drone 35 Ha: Kamera menyorot ke Utara dari sisi Selatan koridor rel KA
            targetPos = { x: 0, y: 90, z: -125 };
            targetLook = { x: 0, y: 0, z: 15 };
            activateBtn('btnCamOverview');
            setProjectionText('PERSPEKTIF DRONE 3D (MENYOROT UTARA)');
            break;
        case 'office':
            // Fokus ke Kantor Utama PT MTI & Datacenter NOC di Zona 6 & 7 (Utara)
            targetPos = { x: LW('f_admin_office').x, y: 22, z: 22 };
            targetLook = { x: LW('f_admin_office').x, y: 4, z: 54 };
            activateBtn('btnCamOffice');
            setProjectionText('DETAIL ZONA 6 (KANTOR UTAMA MTI & NOC UTARA)');
            break;
        case 'gate':
            targetPos = { x: -45, y: 24, z: 22 };
            targetLook = { x: -45, y: 2, z: 54 };
            activateBtn('btnCamGate');
            setProjectionText('DETAIL ZONA 1 (GATE COMPLEX UTARA)');
            break;
        case 'vgm':
            targetPos = { x: -49, y: 16, z: 28 };
            targetLook = { x: -49, y: 1.5, z: 52 };
            activateBtn('btnCamGate');
            setProjectionText('DETAIL WEIGHBRIDGE VGM (80T)');
            break;
        case 'yard':
            targetPos = { x: 0, y: 45, z: -55 };
            targetLook = { x: 0, y: 2, z: -10 };
            activateBtn('btnCamYard');
            setProjectionText('DETAIL ZONA 2 (STACKING YARD BLOK A-E)');
            break;
        case 'reefer':
            targetPos = { x: LW('f_reefer_racks').x + 40, y: 26, z: 16 };
            targetLook = { x: LW('f_reefer_racks').x, y: 3, z: 16 };
            activateBtn('btnCamReefer');
            setProjectionText('DETAIL ZONA 3 (REEFER COLD CHAIN TIMUR LAUT)');
            break;
        case 'cfs':
            targetPos = { x: LW('f_cfs').x - 33, y: 28, z: LW('f_cfs').z };
            targetLook = { x: LW('f_cfs').x, y: 3, z: LW('f_cfs').z };
            activateBtn('btnCamCfs');
            setProjectionText('DETAIL CFS & M&R WORKSHOP (BARAT)');
            break;
        case 'customs':
            targetPos = { x: LW('f_kppbc').x + 32, y: 28, z: 0 };
            targetLook = { x: LW('f_kppbc').x, y: 3, z: 0 };
            activateBtn('btnCamCustoms');
            setProjectionText('DETAIL ZONA 5 (BEA CUKAI & X-RAY TIMUR)');
            break;
        case 'rail':
            targetPos = { x: 0, y: 28, z: -85 };
            targetLook = { x: 0, y: 3, z: -48 };
            activateBtn('btnCamRail');
            setProjectionText('DETAIL ZONA 4 (RAIL SIDING 210M SELATAN)');
            break;
        case 'cockpit':
            const rs = equipmentMeshes['RS-02'];
            const rsX = rs ? rs.position.x : 0;
            const rsZ = rs ? rs.position.z : 2;
            targetPos = { x: rsX - 1.2, y: 4.5, z: rsZ };
            targetLook = { x: rsX + 30, y: 4, z: rsZ };
            activateBtn('btnCamCockpit');
            setProjectionText('FIRST-PERSON CABIN RS-02');
            break;
        default:
            targetPos = { x: 0, y: 90, z: -125 };
            targetLook = { x: 0, y: 0, z: 15 };
            activateBtn('btnCamOverview');
            setProjectionText('PERSPEKTIF DRONE 3D');
    }

    new TWEEN.Tween(camera.position)
        .to(targetPos, 1000)
        .easing(TWEEN.Easing.Cubic.Out)
        .onUpdate(updateSimZoomDisplay)
        .onComplete(updateSimZoomDisplay)
        .start();

    new TWEEN.Tween(controls.target)
        .to(targetLook, 1000)
        .easing(TWEEN.Easing.Cubic.Out)
        .start();
}

// =============================================================================
// ZOOM ENGINE & DISPLAY SYSTEM (DEDICATED CONTROLS LIKE DENAH.PHP)
// =============================================================================
const BASE_ZOOM_DIST = 135;

function updateSimZoomDisplay() {
    if (!camera || !controls) return;
    const dist = camera.position.distanceTo(controls.target);
    const pct = Math.round((BASE_ZOOM_DIST / Math.max(5, dist)) * 100);
    const zoomEl = document.getElementById('simZoomLevelDisplay');
    if (zoomEl) {
        zoomEl.innerText = Math.min(999, Math.max(15, pct)) + '%';
    }
}

function simZoom(direction) {
    if (!camera || !controls) return;
    const factor = direction > 0 ? 0.72 : 1.38;
    const currentDist = camera.position.distanceTo(controls.target);
    const newDist = Math.max(controls.minDistance, Math.min(controls.maxDistance, currentDist * factor));

    const dir = new THREE.Vector3().subVectors(camera.position, controls.target).normalize();
    const targetPos = new THREE.Vector3().copy(controls.target).add(dir.multiplyScalar(newDist));

    new TWEEN.Tween(camera.position)
        .to({ x: targetPos.x, y: targetPos.y, z: targetPos.z }, 350)
        .easing(TWEEN.Easing.Cubic.Out)
        .onUpdate(updateSimZoomDisplay)
        .onComplete(updateSimZoomDisplay)
        .start();
}

function resetSimCameraView() {
    setCameraView('overview');
    showToast('Reset Tampilan', 'Sudut kamera dan zoom dikembalikan ke pandangan drone standar 35 Ha.', 'info');
}

// =============================================================================
// DYNAMIC VIEWPORT HEIGHT & SCADA FEED TOGGLES
// =============================================================================
let isViewportExpanded = false;
function toggleDynamicViewportHeight() {
    isViewportExpanded = !isViewportExpanded;
    const container = document.getElementById('simulatorViewportContainer');
    const textEl = document.getElementById('btnHeightText');
    const btnEl = document.getElementById('btnToggleHeight');

    if (!container) return;

    if (isViewportExpanded) {
        container.classList.remove('h-[560px]', 'sm:h-[620px]', 'lg:h-[680px]', 'xl:h-[740px]');
        container.classList.add('h-[calc(100vh-140px)]', 'min-h-[680px]');
        if (textEl) textEl.innerText = 'Layar Normal';
        if (btnEl) {
            btnEl.classList.add('bg-[#002f5e]', 'text-white', 'border-[#002f5e]');
            btnEl.classList.remove('bg-slate-100', 'text-slate-700', 'border-slate-200');
        }
        showToast('Mode Layar Dinamis', 'Layar 3D diperluas ke tinggi maksimal viewport kerja.', 'info');
    } else {
        container.classList.remove('h-[calc(100vh-140px)]', 'min-h-[680px]');
        container.classList.add('h-[560px]', 'sm:h-[620px]', 'lg:h-[680px]', 'xl:h-[740px]');
        if (textEl) textEl.innerText = 'Layar Tinggi';
        if (btnEl) {
            btnEl.classList.remove('bg-[#002f5e]', 'text-white', 'border-[#002f5e]');
            btnEl.classList.add('bg-slate-100', 'text-slate-700', 'border-slate-200');
        }
        showToast('Mode Layar Dinamis', 'Layar 3D dikembalikan ke rasio standar.', 'info');
    }

    setTimeout(() => {
        onWindowResize();
    }, 150);
}

let isScadaCollapsed = false;
function toggleScadaFeed() {
    isScadaCollapsed = !isScadaCollapsed;
    const ticker = document.getElementById('simLogTicker');
    const chevron = document.getElementById('scadaFeedChevron');
    if (!ticker) return;
    if (isScadaCollapsed) {
        ticker.classList.add('hidden');
        if (chevron) chevron.className = 'fa-solid fa-chevron-up text-[10px]';
    } else {
        ticker.classList.remove('hidden');
        if (chevron) chevron.className = 'fa-solid fa-chevron-down text-[10px]';
    }
}

function toggleTechnicalDimensions() {
    showTechnicalDimensions = !showTechnicalDimensions;
    cadLayers.dimensions = showTechnicalDimensions;
    if (technicalDimensionsGroup) {
        technicalDimensionsGroup.visible = showTechnicalDimensions;
    }
    const btn = document.getElementById('btnToggleDimensions');
    if (btn) {
        if (showTechnicalDimensions) {
            btn.classList.add('bg-blue-50', 'text-blue-700', 'border-blue-200', 'font-bold');
            btn.classList.remove('bg-gray-100', 'text-gray-700', 'border-gray-200');
        } else {
            btn.classList.remove('bg-blue-50', 'text-blue-700', 'border-blue-200', 'font-bold');
            btn.classList.add('bg-gray-100', 'text-gray-700', 'border-gray-200');
        }
    }
    showToast('Overlay CAD', showTechnicalDimensions ? 'Garis Ukur Dimensi CAD 3D Ditampilkan' : 'Garis Ukur Dimensi CAD 3D Disembunyikan', 'info');
}

function toggleCADTitleBlock() {
    showCADTitleBlock = !showCADTitleBlock;
    const titleBlock = document.getElementById('cadTitleBlock');
    const btn = document.getElementById('btnToggleTitleBlock');
    if (titleBlock) {
        if (showCADTitleBlock) {
            titleBlock.classList.remove('hidden');
        } else {
            titleBlock.classList.add('hidden');
        }
    }
    if (btn) {
        if (showCADTitleBlock) {
            btn.classList.add('bg-blue-50', 'text-blue-700', 'border-blue-200', 'font-bold');
            btn.classList.remove('bg-gray-100', 'text-gray-700', 'border-gray-200');
        } else {
            btn.classList.remove('bg-blue-50', 'text-blue-700', 'border-blue-200', 'font-bold');
            btn.classList.add('bg-gray-100', 'text-gray-700', 'border-gray-200');
        }
    }
}

function toggleShipmentCard() {
    const content = document.getElementById('shipmentCardContent');
    const chevron = document.getElementById('shipmentCardChevron');
    if (!content) return;
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        if (chevron) {
            chevron.classList.remove('fa-chevron-up');
            chevron.classList.add('fa-chevron-down');
        }
    } else {
        content.classList.add('hidden');
        if (chevron) {
            chevron.classList.remove('fa-chevron-down');
            chevron.classList.add('fa-chevron-up');
        }
    }
}

function dismissTargetPill() {
    const hudTargetPill = document.getElementById('hudTargetPill');
    if (hudTargetPill) {
        hudTargetPill.classList.remove('opacity-100');
        hudTargetPill.classList.add('opacity-0');
        setTimeout(() => hudTargetPill.classList.add('hidden'), 300);
    }
}

let targetDismissTimer = null;
function focusLocation(preset, label, coords, hwTag) {
    // 0. Auto scroll ke panel viewport 3D simulator agar user langsung melihat aksi lapangan
    const viewport = document.getElementById('simulatorViewportContainer');
    if (viewport) {
        viewport.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // 1. Ubah sudut kamera secara dinamis
    setCameraView(preset);

    // 2. Tampilkan Hologram 3D Target Beacon Marker di atas lokasi
    if (targetMarkerGroup && coords) {
        targetMarkerGroup.position.set(coords.x, coords.y || 0, coords.z);
        targetMarkerGroup.visible = true;

        targetMarkerGroup.scale.set(0.1, 0.1, 0.1);
        new TWEEN.Tween(targetMarkerGroup.scale)
            .to({ x: 1, y: 1, z: 1 }, 700)
            .easing(TWEEN.Easing.Back.Out)
            .start();
    }

    // 3. Tampilkan HUD Target Indicator Pill di atas viewport 3D
    const hudTargetPill = document.getElementById('hudTargetPill');
    const hudTargetText = document.getElementById('hudTargetText');
    const hudTargetCoord = document.getElementById('hudTargetCoord');
    const hudTargetHw = document.getElementById('hudTargetHw');

    if (hudTargetPill) {
        hudTargetPill.classList.remove('hidden', 'opacity-0');
        hudTargetPill.classList.add('opacity-100');
    }
    if (hudTargetText) hudTargetText.innerText = label;
    if (hudTargetCoord && coords) hudTargetCoord.innerText = `X: ${coords.x.toFixed(1)}, Z: ${coords.z.toFixed(1)}`;
    if (hudTargetHw) hudTargetHw.innerText = hwTag || '';

    // Auto dismiss setelah 8 detik
    if (targetDismissTimer) clearTimeout(targetDismissTimer);
    targetDismissTimer = setTimeout(() => {
        dismissTargetPill();
    }, 8000);

    // 4. Catat di ticker log SCADA
    if (coords) {
        logTicker(`<span class="text-cyan-400 font-bold">[FOKUS 3D]</span> Kamera beralih ke: <strong>${label}</strong> (${hwTag || ''}) &bull; Koordinat X: ${coords.x.toFixed(1)}, Z: ${coords.z.toFixed(1)}`);
    }
}

// Lighting Day / Sunset / Night Toggle
function setLightingMode(mode) {
    currentLighting = mode;
    document.querySelectorAll('.light-btn').forEach(btn => {
        btn.classList.remove('bg-white', 'text-amber-700', 'shadow-2xs', 'font-bold');
        btn.classList.add('text-gray-600', 'font-medium');
    });

    const activateLightBtn = (id) => {
        const b = document.getElementById(id);
        if (b) {
            b.classList.remove('text-gray-600', 'font-medium');
            b.classList.add('bg-white', 'text-amber-700', 'shadow-2xs', 'font-bold');
        }
    };

    if (mode === 'day') {
        scene.background = new THREE.Color(0xecf3f9);
        scene.fog.color = new THREE.Color(0xecf3f9);
        ambientLight.color.setHex(0xffffff);
        ambientLight.intensity = LIGHT_DAY.ambient;
        sunLight.intensity = LIGHT_DAY.sun;
        sunLight.color.setHex(LIGHT_DAY.sunColor);
        floodlightLights.forEach(l => l.intensity = 0);
        activateLightBtn('btnLightDay');
    } else if (mode === 'sunset') {
        scene.background = new THREE.Color(0xfdba74);
        scene.fog.color = new THREE.Color(0xfdba74);
        ambientLight.color.setHex(0xfb923c);
        ambientLight.intensity = 0.5;
        sunLight.intensity = 0.7;
        sunLight.color.setHex(0xea580c);
        floodlightLights.forEach(l => l.intensity = 0.5);
        activateLightBtn('btnLightSunset');
    } else if (mode === 'night') {
        scene.background = new THREE.Color(0x020617);
        scene.fog.color = new THREE.Color(0x020617);
        ambientLight.color.setHex(0x1e293b);
        ambientLight.intensity = 0.2;
        sunLight.intensity = 0.1;
        floodlightLights.forEach(l => l.intensity = 2.2); // Floodlight ON!
        activateLightBtn('btnLightNight');
    }
}

// Fullscreen Toggle
function toggleFullscreen() {
    const elem = document.getElementById('simulatorViewportContainer');
    if (!document.fullscreenElement) {
        elem.requestFullscreen().catch(err => alert(`Error: ${err.message}`));
    } else {
        document.exitFullscreen();
    }
}

document.addEventListener('fullscreenchange', () => {
    setTimeout(onWindowResize, 100);
});

// Toast Notifications
function showToast(title, msg, type = 'success') {
    const toast = document.getElementById('simToast');
    const tTitle = document.getElementById('toastTitle');
    const tMsg = document.getElementById('toastMsg');
    const tIcon = document.getElementById('toastIcon');

    tTitle.innerText = title;
    tMsg.innerText = msg;

    if (type === 'success') {
        tIcon.innerHTML = '<i class="fa-solid fa-check text-emerald-400"></i>';
        tIcon.className = 'w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center';
    } else if (type === 'error' || type === 'warn') {
        tIcon.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-amber-400"></i>';
        tIcon.className = 'w-8 h-8 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center';
    } else {
        tIcon.innerHTML = '<i class="fa-solid fa-info text-blue-400"></i>';
        tIcon.className = 'w-8 h-8 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center';
    }

    toast.classList.remove('opacity-0', '-translate-y-4');
    toast.classList.add('opacity-100', 'translate-y-0');

    setTimeout(() => {
        toast.classList.remove('opacity-100', 'translate-y-0');
        toast.classList.add('opacity-0', '-translate-y-4');
    }, 4500);
}

// Log Ticker Output
function logTicker(msg) {
    const ticker = document.getElementById('simLogTicker');
    const line = document.createElement('div');
    line.className = 'text-gray-300 flex items-start';
    line.innerHTML = `<span class="text-blue-400 mr-1.5">&bull;</span><span>${msg}</span>`;
    ticker.prepend(line);

    // Limit to 12 items
    while (ticker.children.length > 12) {
        ticker.removeChild(ticker.lastChild);
    }
}

function updateLiveClock() {
    const now = new Date();
    const str = now.toTimeString().split(' ')[0] + ' WIB';
    const clockEl = document.getElementById('liveClock');
    if (clockEl) clockEl.innerText = str;
}

function updateStatsUI(stats) {
    if (!stats) return;
    const inYardEl = document.getElementById('statInYard');
    const yardUtilEl = document.getElementById('statYardUtil');
    const yardBarEl = document.getElementById('statYardBar');

    if (inYardEl) inYardEl.innerHTML = `${stats.in_yard} <span class="text-xs font-normal text-gray-400">box</span>`;
    if (yardUtilEl) yardUtilEl.innerText = `${stats.occupancy_pct}%`;
    if (yardBarEl) yardBarEl.style.width = `${stats.occupancy_pct}%`;
}

function onWindowResize() {
    const container = document.getElementById('webglCanvas');
    if (!container || !renderer || !camera) return;
    camera.aspect = container.clientWidth / container.clientHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(container.clientWidth, container.clientHeight);
}

// Animation Render Loop
function animate(time) {
    requestAnimationFrame(animate);
    TWEEN.update();
    controls.update();
    updateHazardLights(time);

    // Dynamic 3D Azimuth Compass Rose Heading & Dial Rotation (North = +Z Kantor Utama / Gate)
    if (controls && camera) {
        const lookDir = new THREE.Vector3().subVectors(controls.target, camera.position);
        lookDir.y = 0;
        lookDir.normalize();

        // Sudut arah kamera dari Utara (+Z Kantor MTI) searah jarum jam:
        // Timur = -X dunia (lihat layout_master.php), sehingga x dinegasikan agar E/W tidak terbalik
        let headingRad = Math.atan2(-lookDir.x, lookDir.z);
        let deg = Math.round(headingRad * (180 / Math.PI));
        if (deg < 0) deg += 360;
        if (deg === 360) deg = 0;

        const dial = document.getElementById('compassDial');
        if (dial) {
            dial.style.transform = `rotate(${-deg}deg)`;
        }
        const headingEl = document.getElementById('compassHeading');
        if (headingEl) {
            let dir = 'N';
            if (deg >= 22.5 && deg < 67.5) dir = 'NE';
            else if (deg >= 67.5 && deg < 112.5) dir = 'E';
            else if (deg >= 112.5 && deg < 157.5) dir = 'SE';
            else if (deg >= 157.5 && deg < 202.5) dir = 'S';
            else if (deg >= 202.5 && deg < 247.5) dir = 'SW';
            else if (deg >= 247.5 && deg < 292.5) dir = 'W';
            else if (deg >= 292.5 && deg < 337.5) dir = 'NW';
            headingEl.innerText = `${deg}° ${dir}`;
        }
    }

    // 3D Target Marker Hologram Animation
    if (targetMarkerGroup && targetMarkerGroup.visible) {
        const t = (time || performance.now()) * 0.003;
        const pin = targetMarkerGroup.getObjectByName("beaconPin");
        if (pin) {
            pin.rotation.y = t * 1.5;
            pin.position.y = 17 + Math.sin(t * 2.5) * 0.9;
        }
        const beam = targetMarkerGroup.getObjectByName("beaconBeam");
        if (beam) {
            beam.rotation.y = -t;
        }
        const outerRing = targetMarkerGroup.getObjectByName("beaconOuterRing");
        if (outerRing) {
            outerRing.rotation.z = t * 0.8;
        }
        const innerRing = targetMarkerGroup.getObjectByName("beaconInnerRing");
        if (innerRing) {
            innerRing.rotation.z = -t * 1.2;
        }
    }

    renderer.render(scene, camera);
}

// =========================================================================
// SENSOR EMULATION & HARDWARE TELEMETRY HANDLERS (11 SENSORS)
// =========================================================================
let twistlockState = true; // true = LOCKED, false = UNLOCKED

function testSensorAction(type) {
    const apiEndpoint = `api/simulator_action.php?action=test_${type}`;
    
    fetch(apiEndpoint)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                showToast('Gagal Simulasi Sensor', data.message || 'Terjadi kesalahan sistem', 'error');
                return;
            }

            // HW-01 ANPR
            if (type === 'hw01_anpr' || type === 'anpr') {
                focusLocation('gate', 'Kamera ANPR Lane 1 (Gate Inbound)', { x: -45, y: 0, z: 52 }, 'HW-01 ANPR');
                const anprEl = document.getElementById('sensorAnprVal');
                if (anprEl) anprEl.innerText = `${data.plate} (${data.confidence})`;
                logTicker(`[HW-01 ANPR] Kamera memindai plat truk ${data.plate} di Gate Lane 1 -> Auto-Billing: Rp 50.000 (Pas Gerbang)`);
                showToast('HW-01 ANPR Berhasil', `Plat ${data.plate} terdeteksi (${data.confidence})`, 'success');
            }
            // HW-02 OCR
            else if (type === 'hw02_ocr' || type === 'ocr') {
                focusLocation('gate', 'Portal Optik OCR ISO 6346 (Gate Inbound)', { x: -45, y: 0, z: 52 }, 'HW-02 OCR');
                const ocrEl = document.getElementById('sensorOcrVal');
                if (ocrEl) ocrEl.innerText = `${data.container_number} (${data.iso_code.split(' ')[0]})`;
                logTicker(`[HW-02 OCR] Portal membaca kontainer ${data.container_number} | ISO ${data.iso_code} | Check Digit: VALID -> Demurrage Timer Dimulai`);
                showToast('HW-02 OCR Berhasil', `Kontainer ${data.container_number} ISO 6346 Valid`, 'success');
            }
            // HW-03 VGM
            else if (type === 'hw03_vgm' || type === 'vgm') {
                focusLocation('vgm', 'Jembatan Timbang 80T & Sertifikat SOLAS VGM', { x: -49, y: 0, z: 52 }, 'HW-03 TIMBANGAN');
                const vgmEl = document.getElementById('sensorVgmVal');
                if (vgmEl) vgmEl.innerText = `Net ${Number(data.vgm.net_vgm).toLocaleString('id-ID')} kg (PASS)`;
                logTicker(`[HW-03 Timbangan 80T] Bruto ${Number(data.vgm.gross_weight).toLocaleString('id-ID')} kg, Tara ${Number(data.vgm.tare_weight).toLocaleString('id-ID')} kg, Net ${Number(data.vgm.net_vgm).toLocaleString('id-ID')} kg -> Sertifikat #${data.vgm.certificate_no} (Billing Rp 120.000)`);
                showToast('HW-03 Timbangan 80T (VGM)', `Net: ${Number(data.vgm.net_vgm).toLocaleString('id-ID')} kg (Sertifikat SOLAS Terbit)`, 'success');
            }
            // HW-04 RFID
            else if (type === 'hw04_rfid' || type === 'rfid') {
                focusLocation('gate', 'Antena UHF RFID Hopeland 20m (Gate Lane 1)', { x: -45, y: 0, z: 52 }, 'HW-04 RFID');
                const rfidEl = document.getElementById('sensorRfidVal');
                if (rfidEl) rfidEl.innerText = `${data.rfid_tag.slice(0, 12)}... (AUTH)`;
                logTicker(`[HW-04 UHF RFID] Hopeland 20m mendeteksi tag armada ${data.rfid_tag} (${data.driver}) -> ${data.e_wallet_status}`);
                showToast('HW-04 UHF RFID Terbaca', `${data.driver} | e-Wallet Terverifikasi`, 'success');
            }
            // HW-05 VMS
            else if (type === 'hw05_vms' || type === 'vms') {
                focusLocation('gate', 'VMS LED Variable Message Sign (Gate Display)', { x: -45, y: 0, z: 52 }, 'HW-05 VMS');
                const vmsEl = document.getElementById('sensorVmsVal');
                if (vmsEl) vmsEl.innerText = data.display_text;
                logTicker(`[HW-05 VMS Display] LED Gate menampilkan instruksi: "${data.display_text}" -> ${data.tat_impact}`);
                showToast('HW-05 VMS Display Aktif', data.display_text, 'info');
            }
            // HW-06 Edge AI PC
            else if (type === 'hw06_edge' || type === 'edge') {
                focusLocation('gate', 'Advantech Edge AI PC & Palang Otomatis (12ms)', { x: -45, y: 0, z: 52 }, 'HW-06 EDGE PC');
                const edgeEl = document.getElementById('sensorEdgeVal');
                if (edgeEl) edgeEl.innerText = `OPEN (${data.latency} / 1.2s)`;
                logTicker(`[HW-06 Edge PC] Advantech ARK-3532 memproses interlock gerbang dalam ${data.latency}. Relay GPIO membuka palang pintu.`);
                showToast('HW-06 Edge AI PC Interlock', `Palang Pintu Terbuka (Latency ${data.latency})`, 'success');
            }
            // HW-07 RTK GNSS
            else if (type === 'hw07_rtk' || type === 'rtk') {
                focusLocation('yard', 'CHCNAV RTK DGPS (Reach Stacker RS-02 / Blok B)', { x: -8, y: 0, z: 2 }, 'HW-07 RTK GNSS');
                const rtkEl = document.getElementById('sensorRtkVal');
                const slotEl = document.getElementById('sensorSlotVal');
                if (rtkEl) rtkEl.innerText = `FIX (${data.accuracy}, ${data.satellites.split(' ')[0]} Sat)`;
                if (slotEl) slotEl.innerText = data.slot_3d;
                logTicker(`[HW-07 RTK DGPS] CHCNAV CGI-610 mengunci koordinat akurasi ${data.accuracy} (${data.satellites}) -> Slot 3D: ${data.slot_3d}`);
                showToast('HW-07 RTK GNSS Terkunci', `Akurasi ${data.accuracy} | Slot 3D: ${data.slot_3d}`, 'success');
            }
            // HW-08 Spreader Twistlock & Load Cell
            else if (type === 'hw08_twistlock' || type === 'twistlock') {
                focusLocation('yard', 'Bromma Spreader Twistlock & Load Cell (RS-02)', { x: -8, y: 0, z: 2 }, 'HW-08 SPREADER');
                const twistEl = document.getElementById('sensorTwistlockVal');
                const loadEl = document.getElementById('sensorLoadVal');
                if (twistEl) twistEl.innerText = data.twistlock_state;
                if (loadEl) loadEl.innerText = data.load_tonnage;
                logTicker(`[HW-08 Bromma Spreader] Twistlock: ${data.twistlock_state} | Load Cell: ${data.load_tonnage} -> Auto-Billing: Rp 250.000 (Lo-Lo Lift-Off)`);
                showToast('HW-08 Spreader & Load Cell', `${data.twistlock_state} | Beban: ${data.load_tonnage}`, 'success');
            }
            // HW-09 Reefer Socket Modbus
            else if (type === 'hw09_reefer' || type === 'reefer') {
                focusLocation('reefer', 'Smart Reefer Socket Marechal 380V (Rack R-02)', { ...LW('f_reefer_racks'), y: 0 }, 'HW-09 REEFER');
                const tempEl = document.getElementById('sensorReeferTempVal');
                const voltEl = document.getElementById('sensorVoltVal');
                const kwEl = document.getElementById('sensorKwVal');
                if (tempEl) {
                    tempEl.className = 'font-bold text-cyan-400';
                    tempEl.innerText = `${data.reefer.temperature} (Optimal)`;
                }
                if (voltEl) voltEl.innerText = `${data.reefer.voltage} (50 Hz)`;
                if (kwEl) kwEl.innerText = `${data.reefer.power} (${data.reefer.current})`;
                logTicker(`[HW-09 Smart Socket] Marechal Modbus RTU: Rack R-02 Plug #14 telemetri ${data.reefer.temperature}, ${data.reefer.power} -> Billing Rp 35.000/jam`);
                showToast('HW-09 Smart Reefer Socket', `Suhu ${data.reefer.temperature} | Daya ${data.reefer.power}`, 'success');
            }
            // HW-10 Rail Axle Counter
            else if (type === 'hw10_axle' || type === 'axle') {
                focusLocation('rail', 'Frauscher Axle Counter SIL 4 (Jalur Rel Siding)', { x: 0, y: 0, z: -46 }, 'HW-10 AXLE COUNTER');
                const axleEl = document.getElementById('sensorAxleVal');
                if (axleEl) axleEl.innerText = `${data.axle.axle_count} As Roda (${data.axle.train.split(' ')[0]} ${data.axle.train.split(' ')[1]})`;
                logTicker(`[HW-10 Frauscher Axle Counter] RSR123 mendeteksi 126 gandar KA 2518 (1 Lokomotif + 30 Gerbong PPCW utuh). Sertifikasi SIL 4 verified.`);
                showToast('HW-10 Axle Counter SIL 4', `126 Gandar KA 2518 Terverifikasi Utuh`, 'success');
            }
            // HW-11 Smart E-Seal CEISA 4.0
            else if (type === 'hw11_eseal' || type === 'eseal') {
                focusLocation('rail', 'Jointech JT701 Smart E-Seal & CEISA 4.0 Bea Cukai', { x: 0, y: 0, z: -46 }, 'HW-11 SMART E-SEAL');
                const sealEl = document.getElementById('sensorSealVal');
                const sppbEl = document.getElementById('sensorSppbVal');
                if (sealEl) sealEl.innerText = `${data.eseal.wire_status} (#${data.eseal.seal_id})`;
                if (sppbEl) sppbEl.innerText = `${data.eseal.channel}`;
                logTicker(`[HW-11 Smart E-Seal] Jointech JT701 GPS/GSM: Kawat segel utuh (INTACT). Bea Cukai CEISA 4.0 menerbitkan ${data.eseal.sppb_number}`);
                showToast('HW-11 Smart E-Seal & SPPB', `Segel #${data.eseal.seal_id} INTACT -> SPPB Terbit`, 'success');
            }
            // Kombinasi ANPR + OCR
            else if (type === 'anpr_ocr') {
                focusLocation('gate', 'Portal Gabungan ANPR & OCR ISO 6346 (Gate Lane 1)', { x: -45, y: 0, z: 52 }, 'HW-01 & HW-02');
                const anprEl = document.getElementById('sensorAnprVal');
                const ocrEl = document.getElementById('sensorOcrVal');
                if (anprEl) anprEl.innerText = `${data.anpr.plate} (${data.anpr.confidence})`;
                if (ocrEl) ocrEl.innerText = `${data.ocr.container_number} (${data.ocr.check_digit})`;
                logTicker(`[HW-01 & HW-02] ANPR & Portal OCR memindai truk ${data.anpr.plate} membawa ${data.ocr.container_number} -> Auto-Billing: Rp 50.000`);
                showToast('Pindai Plat & OCR Berhasil', `Truk ${data.anpr.plate} | Kontainer ${data.ocr.container_number}`, 'success');
            }
        })
        .catch(err => {
            console.error('Sensor action error:', err);
            showToast('Error Sensor', 'Gagal menghubungi service telemetri', 'error');
        });
}

function toggleTwistlockSim() {
    focusLocation('yard', 'Bromma Spreader Twistlock & Load Cell (RS-02 Blok B)', { x: -8, y: 0, z: 2 }, 'HW-08 SPREADER');
    twistlockState = !twistlockState;
    const twistEl = document.getElementById('sensorTwistlockVal');
    const loadEl = document.getElementById('sensorLoadVal');
    
    if (twistlockState) {
        if (twistEl) {
            twistEl.innerText = 'LOCKED (Mengunci)';
            twistEl.className = 'font-bold text-blue-700';
        }
        if (loadEl) loadEl.innerText = '28.45 Ton';
        logTicker('[HW-08] Bromma Spreader: Twistlock LOCKED (Mengunci 4 pin) & Load Cell aktif: 28.45 Ton (Ready to Hoist)');
        showToast('Spreader Twistlock LOCKED', '4 Pin mengunci sempurna di corner casting box. Siap angkat.', 'info');
    } else {
        if (twistEl) {
            twistEl.innerText = 'UNLOCKED (Membuka)';
            twistEl.className = 'font-bold text-amber-600';
        }
        if (loadEl) loadEl.innerText = '0.00 Ton';
        logTicker('[HW-08] Bromma Spreader: Twistlock UNLOCKED (Membuka pin) & Load Cell: 0.00 Ton (Box dilepas)');
        showToast('Spreader Twistlock UNLOCKED', 'Pin terbuka bebas. Kontainer telah landing di slot target.', 'info');
    }
}

function simulateReeferAlarm() {
    focusLocation('reefer', 'ALARM SUHU KRITIS: Rack R-02 Plug #14 Overheat (+4.8°C)', { ...LW('f_reefer_racks'), y: 0 }, 'HW-09 ALARM');
    const tempEl = document.getElementById('sensorReeferTempVal');
    if (tempEl) {
        tempEl.className = 'font-bold text-rose-600 animate-pulse';
        tempEl.innerText = '+4.8°C (ALARM OVERHEAT!)';
    }
    
    logTicker('<span class="text-rose-400 font-bold">[ALERT HW-09]</span> Peringatan Kritis! Suhu Rack R-02 Plug #14 melonjak ke +4.8°C (Batas aman: -18°C)! SCADA mengirim notifikasi ke teknisi cold chain.');
    showToast('ALARM SUHU REEFER!', 'Suhu melonjak ke +4.8°C! Kompresor reefer memerlukan inspeksi darurat.', 'warn');
    
    // Kembalikan ke normal setelah 6 detik
    setTimeout(() => {
        if (tempEl) {
            tempEl.className = 'font-bold text-cyan-700';
            tempEl.innerText = '-20.2°C (Optimal)';
        }
        logTicker('[HW-09] Sistem pendingin cadangan aktif otomatis. Suhu kembali normal ke -20.2°C.');
    }, 6000);
}

// =========================================================================
// TAB SWITCHER CONTROLLER
// =========================================================================
function switchSimTab(tabId) {
    const tabs = ['truck', 'train', 'scada', 'scanner', 'catalog'];
    tabs.forEach(t => {
        const contentEl = document.getElementById(`tabContent${t.charAt(0).toUpperCase() + t.slice(1)}`);
        const btnEl = document.getElementById(`tabBtn${t.charAt(0).toUpperCase() + t.slice(1)}`);
        if (contentEl) {
            if (t === tabId) {
                contentEl.classList.remove('hidden');
            } else {
                contentEl.classList.add('hidden');
            }
        }
        if (btnEl) {
            if (t === tabId) {
                btnEl.className = 'sim-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-600 text-white shadow-xs transition flex items-center gap-1.5';
            } else {
                btnEl.className = 'sim-tab-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition flex items-center gap-1.5';
            }
        }
    });
}

// =========================================================================
// 1. ALUR OTOMATIS TRUK KONTAINER (ROAD-TO-YARD 6-TAHAP)
// =========================================================================
let isTruckDemoRunning = false;
function runAutoTruckDemo() {
    if (isTruckDemoRunning || isTrainDemoRunning || isAnimating) {
        showToast('Simulasi Sedang Berjalan', 'Harap tunggu hingga alur saat ini selesai', 'warn');
        return;
    }
    isTruckDemoRunning = true;
    switchSimTab('truck');

    const btn = document.getElementById('btnAutoTruck');
    if (btn) {
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i><span>Alur Truk Berjalan...</span>';
        btn.classList.add('opacity-80');
    }

    showToast('Alur Truk Dimulai', 'Mengaktifkan simulasi fisik terpadu (Gate -> Yard -> RS -> Gate-Out)...', 'info');

    // Eksekusi simulasi fisik 3D penuh
    triggerGateIn('drop_off');

    // Telemetri sensor selaras waktu
    setTimeout(() => testTruckStep(1), dur(800));
    setTimeout(() => testTruckStep(2), dur(2200));
    setTimeout(() => testTruckStep(3), dur(3800));
    setTimeout(() => testTruckStep(4), dur(5500));
    setTimeout(() => testTruckStep(5), dur(8500));
    setTimeout(() => testTruckStep(6), dur(12500));

    // Selesai & Ringkasan Finansial
    setTimeout(() => {
        showToast('Alur Truk Selesai!', 'Siklus Truk Road-to-Yard tuntas. Total ERP Billing: Rp 420.000.', 'success');
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-truck-moving text-xs"></i><span>1. Alur Otomatis Truk (6 Tahap)</span>';
            btn.classList.remove('opacity-80');
        }
        isTruckDemoRunning = false;
    }, dur(16000));
}

function testTruckStep(step) {
    // 0. Auto-scroll langsung ke panel viewport 3D simulator
    const viewport = document.getElementById('simulatorViewportContainer');
    if (viewport) {
        viewport.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    if (step === 1) {
        // TAHAP 1: Gerbang Inbound & Pindai Plat ANPR / RFID
        showOpFlowHUD(1, '1. GERBANG INBOUND (ANPR & RFID)', 'Truk armada mendekati gerbang Lane 1. Loop aspal mendeteksi kendaraan, kamera ANPR membaca plat B 9481 UEK, dan antena RFID memverifikasi e-Pass supir...');
        focusLocation('gate', 'Tahap 1: Gerbang Inbound & Pindai Plat ANPR/RFID', { x: -49, y: 0, z: 52 }, 'HW-01 & HW-04');
        
        if (truckMesh) {
            truckMesh.position.set(-49, 0, 70);
            truckMesh.rotation.set(0, -Math.PI / 2, 0);
            if (truckBoxMesh) truckBoxMesh.visible = true;

            new TWEEN.Tween(truckMesh.position)
                .to({ z: 52 }, dur(1000))
                .easing(TWEEN.Easing.Quadratic.Out)
                .onComplete(() => {
                    playSimSound('brake');
                    testSensorAction('hw01_anpr');
                    setTimeout(() => testSensorAction('hw04_rfid'), 800);
                })
                .start();
        } else {
            testSensorAction('hw01_anpr');
            setTimeout(() => testSensorAction('hw04_rfid'), 800);
        }

    } else if (step === 2) {
        // TAHAP 2: Portal Optik OCR Box ISO 6346
        showOpFlowHUD(1, '2. PORTAL OPTIK OCR BOX ISO 6346', 'Truk melaju perlahan melewati gantry portal OCR. 4 kamera multi-sudut membaca nomor seri kontainer ISO 6346 & tipe ukuran High Cube 45G1...');
        focusLocation('gate', 'Tahap 2: Portal OCR Box ISO 6346 (Gate Inbound)', { x: -49, y: 0, z: 46 }, 'HW-02 OCR');

        if (truckMesh) {
            if (truckMesh.position.z > 60 || truckMesh.position.z < 35) {
                truckMesh.position.set(-49, 0, 52);
            }
            truckMesh.rotation.set(0, -Math.PI / 2, 0);
            if (truckBoxMesh) truckBoxMesh.visible = true;

            new TWEEN.Tween(truckMesh.position)
                .to({ z: 46 }, dur(900))
                .easing(TWEEN.Easing.Quadratic.InOut)
                .onComplete(() => {
                    testSensorAction('hw02_ocr');
                })
                .start();
        } else {
            testSensorAction('hw02_ocr');
        }

    } else if (step === 3) {
        // TAHAP 3: Jembatan Timbang 80 Ton SOLAS VGM
        showOpFlowHUD(1, '3. TIMBANGAN JEMBATAN 80T (SOLAS VGM)', 'Truk berhenti tepat di platform timbangan baja 80T. Sensor strain gauge menimbang Bruto 32.500 kg, Tara 4.100 kg, dan menerbitkan sertifikat Net VGM 28.400 kg...');
        focusLocation('vgm', 'Tahap 3: Jembatan Timbang 80T & Sertifikat SOLAS VGM', { x: -49, y: 0, z: 38 }, 'HW-03 VGM');

        if (truckMesh) {
            if (truckMesh.position.z > 55 || truckMesh.position.z < 30) {
                truckMesh.position.set(-49, 0, 46);
            }
            truckMesh.rotation.set(0, -Math.PI / 2, 0);
            if (truckBoxMesh) truckBoxMesh.visible = true;

            new TWEEN.Tween(truckMesh.position)
                .to({ z: 38 }, dur(900))
                .easing(TWEEN.Easing.Quadratic.InOut)
                .onComplete(() => {
                    playSimSound('brake');
                    testSensorAction('hw03_vgm');
                })
                .start();
        } else {
            testSensorAction('hw03_vgm');
        }

    } else if (step === 4) {
        // TAHAP 4: Buka Palang Otomatis & Panduan Rute VMS LED
        showOpFlowHUD(2, '4. INTERLOCK PALANG & PANDUAN VMS', 'Edge AI PC memvalidasi izin masuk dalam latensi 12ms. Palang gerbang otomatis terbuka, display VMS LED menyala memandu armada menuju Blok A...');
        focusLocation('gate', 'Tahap 4: Edge AI PC Interlock & Panduan VMS LED', { x: -49, y: 0, z: 30 }, 'HW-05 & HW-06');

        if (truckMesh) {
            truckMesh.position.set(-49, 0, 38);
            truckMesh.rotation.set(0, -Math.PI / 2, 0);
            if (truckBoxMesh) truckBoxMesh.visible = true;

            // 4A. Angkat palang pintu gerbang masuk
            if (boomBarrierMesh) {
                playSimSound('barrier');
                new TWEEN.Tween(boomBarrierMesh.rotation)
                    .to({ x: -Math.PI / 2.5 }, dur(500))
                    .easing(TWEEN.Easing.Quadratic.Out)
                    .start();
            }

            // 4B. Truk melaju melewati palang ke jalan terminal
            setTimeout(() => {
                new TWEEN.Tween(truckMesh.position)
                    .to({ z: 28 }, dur(1100))
                    .easing(TWEEN.Easing.Quadratic.InOut)
                    .onComplete(() => {
                        // Tutup kembali palang gerbang
                        if (boomBarrierMesh) {
                            new TWEEN.Tween(boomBarrierMesh.rotation)
                                .to({ x: 0 }, dur(500))
                                .easing(TWEEN.Easing.Quadratic.In)
                                .start();
                        }
                        testSensorAction('hw06_edge');
                        setTimeout(() => testSensorAction('hw05_vms'), 700);
                    })
                    .start();
            }, dur(500));
        } else {
            testSensorAction('hw06_edge');
            setTimeout(() => testSensorAction('hw05_vms'), 700);
        }

    } else if (step === 5) {
        // TAHAP 5: Bongkar Box oleh Reach Stacker (Lift-Off ke Blok A)
        showOpFlowHUD(3, '5. BONGKAR BOX OLEH REACH STACKER', 'Reach Stacker RS-01 merapat ke truk di jalur transfer, mengunci 4 corner casting dengan spreader Bromma, mengangkat box dari trailer, dan menumpuk di Blok A...');
        focusLocation('yard', 'Tahap 5: Yard Stacking RS-01 & Telemetri RTK GNSS', { x: LW('blok_a').x, y: 0, z: 28 }, 'HW-07 & HW-08');

        // Posisikan truk di transfer bay Blok A (X = -44, Z = 28)
        if (truckMesh) {
            truckMesh.position.set(LW('blok_a').x, 0, 28);
            truckMesh.rotation.set(0, Math.PI, 0);
            if (truckBoxMesh) truckBoxMesh.visible = true;
        }

        testSensorAction('hw07_rtk');
        setTimeout(() => {
            testSensorAction('hw08_twistlock');
            toggleTwistlockSim();

            const rs = equipmentMeshes['RS-01'] || equipmentMeshes['RS-02'];
            if (rs) {
                const boom = rs.getObjectByName('rsBoom');
                new TWEEN.Tween(rs.position)
                    .to({ x: 36.5 }, dur(800))
                    .easing(TWEEN.Easing.Quadratic.InOut)
                    .onComplete(() => {
                        playSimSound('twistlock');
                        if (boom) new TWEEN.Tween(boom.rotation).to({ z: Math.PI / 18 }, dur(400)).start();
                        setTimeout(() => {
                            if (truckBoxMesh) truckBoxMesh.visible = false;
                            playSimSound('thud');
                            if (boom) new TWEEN.Tween(boom.rotation).to({ z: Math.PI / 7 }, dur(500)).start();
                            new TWEEN.Tween(rs.position)
                                .to({ x: 34, z: 28 }, dur(900))
                                .easing(TWEEN.Easing.Quadratic.InOut)
                                .onComplete(() => {
                                    if (boom) new TWEEN.Tween(boom.rotation).to({ z: Math.PI / 10 }, dur(400)).start();
                                })
                                .start();
                        }, dur(700));
                    })
                    .start();
            }
        }, 800);

    } else if (step === 6) {
        // TAHAP 6: Colok Daya Smart Reefer Socket 380V
        showOpFlowHUD(3, '6. COLOK DAYA & MONITORING SUHU REEFER', 'Peti kemas berpendingin ditempatkan di rak Reefer Terminal. Kabel daya industri 380V dicolokkan ke soket Marechal, sensor Modbus membaca suhu -20.2°C dan daya 18.2 kW secara live...');
        focusLocation('reefer', 'Tahap 6: Colok Daya Smart Reefer Socket 380V (Reefer Zone)', { ...LW('f_reefer_racks'), y: 0 }, 'HW-09 REEFER');
        testSensorAction('hw09_reefer');
    }
}

// =========================================================================
// 2. ALUR OTOMATIS KERETA API LOGISTIK (RAIL-TO-YARD 6-TAHAP)
// =========================================================================
let isTrainDemoRunning = false;
function runAutoTrainDemo() {
    if (isTruckDemoRunning || isTrainDemoRunning || isAnimating) {
        showToast('Simulasi Sedang Berjalan', 'Harap tunggu hingga alur saat ini selesai', 'warn');
        return;
    }
    isTrainDemoRunning = true;
    switchSimTab('train');

    const btn = document.getElementById('btnAutoTrain');
    if (btn) {
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i><span>Alur KA Berjalan...</span>';
        btn.classList.add('opacity-80');
    }

    showToast('Alur Kereta Api Dimulai', 'Mengaktifkan simulasi intermodal Rail-to-Yard 6-Tahap...', 'info');

    // Tahap 1: Rel Siding & Cacah Gandar SIL 4 (0.5s)
    setTimeout(() => {
        setCameraView('rail');
        testTrainStep(1);
    }, dur(500));

    // Tahap 2: Pindai Smart E-Seal Nirkabel (2.0s)
    setTimeout(() => {
        testTrainStep(2);
    }, dur(2000));

    // Tahap 3: Bea Cukai CEISA 4.0 & SPPB Jalur Hijau (3.5s)
    setTimeout(() => {
        testTrainStep(3);
    }, dur(3500));

    // Tahap 4 & 5: RTG Crane Spreader Hoist & Alih Muat Fisik ke Yard
    setTimeout(() => {
        testTrainStep(4);
        triggerRailDischarge();
        setTimeout(() => testTrainStep(5), dur(3000));
    }, dur(5000));

    // Tahap 6: Manifest Rail & Penagihan Freight
    setTimeout(() => {
        testTrainStep(6);
    }, dur(10500));

    // Selesai & Ringkasan Finansial
    setTimeout(() => {
        setCameraView('overview');
        showToast('Alur KA Intermodal Selesai!', 'Siklus Alih Muat KA tuntas. Freight Rp 1.850.000 + Bongkar Rp 350.000 terverifikasi.', 'success');
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-train-subway text-xs"></i><span>2. Alur Otomatis KA (6 Tahap)</span>';
            btn.classList.remove('opacity-80');
        }
        isTrainDemoRunning = false;
    }, dur(13500));
}

function testTrainStep(step) {
    // 0. Auto-scroll langsung ke panel viewport 3D simulator
    const viewport = document.getElementById('simulatorViewportContainer');
    if (viewport) {
        viewport.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    if (step === 1) {
        showOpFlowHUD(1, '1. REL SIDING KA & AXLE COUNTER SIL 4', 'Rangkaian Kereta Api Logistik melintas di atas rel siding. Sensor Frauscher SIL 4 mencacah gandar roda secara akurat untuk verifikasi integritas rangkaian...');
        focusLocation('rail', 'Tahap 1: Rel Siding KA & Frauscher Axle Counter SIL 4', { x: 18, y: 0, z: -46 }, 'HW-10 AXLE');
        testSensorAction('hw10_axle');

    } else if (step === 2) {
        showOpFlowHUD(2, '2. PINDAI NIRKABEL SMART E-SEAL', 'Reader nirkabel memverifikasi status segel elektronik Jointech JT701 pada peti kemas gerbong datar KA: STATUS SECURE / TAMPER-PROOF (0 Deteksi Putus)...');
        focusLocation('rail', 'Tahap 2: Pindai Nirkabel Smart E-Seal Jointech JT701', { x: 18, y: 0, z: -46 }, 'HW-11 E-SEAL');
        testSensorAction('hw11_eseal');

    } else if (step === 3) {
        showOpFlowHUD(2, '3. KLIRENS PABEAN CEISA 4.0 BEA CUKAI', 'Gateway pabean Ditjen Bea Cukai menerbitkan SPPB Jalur Hijau (#SPPB-86434/KPU.01/2026). Jaminan pabean dirilis otomatis dan kontainer siap dibongkar...');
        focusLocation('rail', 'Tahap 3: Klirens Pabean CEISA 4.0 Bea Cukai Jalur Hijau', { x: 18, y: 0, z: -46 }, 'CEISA 4.0');
        logTicker('[CEISA 4.0 Bea Cukai] Gateway pabean menerbitkan SPPB Jalur Hijau (#SPPB-86434/KPU.01/2026). Jaminan pabean dirilis.');
        showToast('CEISA 4.0 Bea Cukai', 'SPPB Jalur Hijau Terbit & Jaminan Pabean Dirilis', 'success');

    } else if (step === 4) {
        showOpFlowHUD(3, '4. RTG CRANE SPREADER HOISTING', 'RTG Crane (RTG-01) menggerakkan spreader teleskopik tepat di atas peti kemas gerbong KA, mengunci 4 pin twistlock, dan mengangkat box ke ketinggian gantry...');
        focusLocation('rail', 'Tahap 4: RTG Crane Spreader Twistlock & Hoisting', { x: 18, y: 0, z: -46 }, 'RTG-01 & HW-08');
        testSensorAction('hw08_twistlock');
        toggleTwistlockSim();

        const rtg = equipmentMeshes['RTG-01'];
        if (rtg) {
            const spreader = rtg.getObjectByName('rtgSpreader');
            if (spreader) {
                new TWEEN.Tween(spreader.position)
                    .to({ y: 3.2 }, dur(800))
                    .easing(TWEEN.Easing.Quadratic.InOut)
                    .onComplete(() => {
                        playSimSound('twistlock');
                        setTimeout(() => {
                            new TWEEN.Tween(spreader.position)
                                .to({ y: 12 }, dur(800))
                                .easing(TWEEN.Easing.Quadratic.InOut)
                                .start();
                        }, dur(600));
                    })
                    .start();
            }
        }

    } else if (step === 5) {
        showOpFlowHUD(3, '5. BONGKAR BOX KE BUFFER YARD BLOK D', 'RTG Crane memindahkan peti kemas melintasi trolley gantry dan menempatkannya secara presisi di Buffer Yard Blok D...');
        focusLocation('yard', 'Tahap 5: Penempatan Slot Yard Buffer Presisi RTK', { ...LW('blok_d'), y: 0 }, 'HW-07 RTK');
        testSensorAction('hw07_rtk');
        triggerRailDischarge();

    } else if (step === 6) {
        showOpFlowHUD(4, '6. PENERBITAN RAIL MANIFEST & ESG FREIGHT', 'Manifest perjalanan KA-LOG-JKT-SMG tervalidasi. Freight KA Rp 1.850.000/TEU diterbitkan ke modul Billing, reduksi jejak karbon ESG tercatat 78%...');
        focusLocation('rail', 'Tahap 6: Penerbitan Rail Manifest & Tagihan Freight', { x: 0, y: 0, z: -46 }, 'RAIL FREIGHT');
        logTicker('[RAIL MANIFEST] KA-LOG-JKT-SMG 48 TEU tervalidasi. Freight KA: Rp 1.850.000/TEU. Reduksi jejak karbon ESG: 78%.');
        showToast('Rail Freight Terkonfirmasi', 'Biaya Rel Rp 1.850.000/TEU & Audit ESG Tercatat', 'success');
    }
}

// Backward Compatibility
function runAutoInboundDemo() {
    runAutoTruckDemo();
}

// =========================================================================
// 3. UJI SCANNER OPTIK & VERIFIKASI MUTU AIDC (GS1 / SSCC-18 / ISO)
// =========================================================================
function testScannerSample(type) {
    const statusEl = document.getElementById('scanDecoderStatus');
    const typeEl = document.getElementById('decType');
    const payloadEl = document.getElementById('decPayload');
    const ownerEl = document.getElementById('decOwner');
    const serialEl = document.getElementById('decSerial');
    const checkEl = document.getElementById('decCheck');
    const ymsEl = document.getElementById('decYms');

    if (type === 'dry') {
        focusLocation('yard', 'Peti Kemas Dry Blok B (MSKU9182374)', { x: -8, y: 0, z: 2 }, 'AIDC ISO-6346');
        if (statusEl) statusEl.innerHTML = '<i class="fa-solid fa-circle-check text-emerald-400 mr-1"></i>DECODED OK';
        if (typeEl) typeEl.innerText = 'ISO 6346 Peti Kemas (Dry 40ft High Cube)';
        if (payloadEl) payloadEl.innerText = 'MSKU9182374';
        if (ownerEl) ownerEl.innerText = 'MSKU (Maersk A/S)';
        if (serialEl) serialEl.innerText = '918237';
        if (checkEl) checkEl.innerHTML = '<span class="text-emerald-400 font-bold">4 (VALID - Modulo 11 Match)</span>';
        if (ymsEl) ymsEl.innerText = 'Tersinkron di Blok B-08-03-02';
        logTicker('[AIDC SCAN] Uji Sukses ISO 6346: MSKU9182374 (Check Digit 4 Valid).');
        showToast('Uji Scanner ISO 6346', 'MSKU9182374 Check Digit Valid', 'success');
    } else if (type === 'reefer') {
        focusLocation('reefer', 'Peti Kemas Reefer Rack R-02 (TEMU4819203)', { ...LW('f_reefer_racks'), y: 0 }, 'AIDC REEFER');
        if (statusEl) statusEl.innerHTML = '<i class="fa-solid fa-circle-check text-emerald-400 mr-1"></i>DECODED OK';
        if (typeEl) typeEl.innerText = 'ISO 6346 Peti Kemas (Reefer 40ft Cold Chain)';
        if (payloadEl) payloadEl.innerText = 'TEMU4819203';
        if (ownerEl) ownerEl.innerText = 'TEMU (Textainer Group)';
        if (serialEl) serialEl.innerText = '481920';
        if (checkEl) checkEl.innerHTML = '<span class="text-emerald-400 font-bold">3 (VALID - Modulo 11 Match)</span>';
        if (ymsEl) ymsEl.innerText = 'Tersinkron di Blok REEFER Rack R-02 Plug #14';
        logTicker('[AIDC SCAN] Uji Sukses Reefer ISO 6346: TEMU4819203 (Check Digit 3 Valid).');
        showToast('Uji Scanner Reefer', 'TEMU4819203 Check Digit Valid', 'success');
    } else if (type === 'sscc') {
        focusLocation('cfs', 'CFS Logistics Hub / Cross-Dock (SSCC-18 Pallet)', { ...LW('f_cfs'), y: 0 }, 'GS1-128 SSCC');
        if (statusEl) statusEl.innerHTML = '<i class="fa-solid fa-circle-check text-purple-400 mr-1"></i>GS1-128 DECODED';
        if (typeEl) typeEl.innerText = 'GS1-128 SSCC-18 (Serial Shipping Container Code)';
        if (payloadEl) payloadEl.innerText = '(00) 38991234500000018';
        if (ownerEl) ownerEl.innerText = 'AI (00) - Standar Pallet & Muatan GS1 Internasional';
        if (serialEl) serialEl.innerText = '3899123450000001';
        if (checkEl) checkEl.innerHTML = '<span class="text-emerald-400 font-bold">8 (VALID - GS1 Modulo 10 Match)</span>';
        if (ymsEl) ymsEl.innerText = 'Pallet Logistik CFS Warehouse CIDP';
        logTicker('[AIDC SCAN] Uji Sukses GS1 SSCC-18: (00)38991234500000018.');
        showToast('Uji Scanner SSCC-18', 'Standar GS1-128 Tervalidasi', 'success');
    } else if (type === 'customs') {
        focusLocation('customs', 'Pos Pabean & Bea Cukai KPPBC CEISA 4.0', { ...LW('f_kppbc'), y: 0 }, 'CEISA 4.0');
        if (statusEl) statusEl.innerHTML = '<i class="fa-solid fa-circle-check text-emerald-400 mr-1"></i>CEISA QR VERIFIED';
        if (typeEl) typeEl.innerText = 'QR Code Kepabeanan CEISA 4.0 Bea Cukai';
        if (payloadEl) payloadEl.innerText = 'SPPB-86434/KPU.01/2026';
        if (ownerEl) ownerEl.innerText = 'Kantor Pelayanan Utama Bea Cukai Tanjung Priok / Cikarang';
        if (serialEl) serialEl.innerText = 'KPU.01-86434';
        if (checkEl) checkEl.innerHTML = '<span class="text-emerald-400 font-bold">DIGITAL SIGNATURE VALID (Jalur Hijau)</span>';
        if (ymsEl) ymsEl.innerText = 'Status Pabean: SPPB Cleared (Siap Keluar Gerbang)';
        logTicker('[AIDC SCAN] Uji Sukses QR CEISA 4.0: SPPB-86434/KPU.01/2026 Jalur Hijau.');
        showToast('QR Pabean Terverifikasi', 'SPPB-86434 Digital Signature Valid', 'success');
    }
}

function validateCustomBoxNumber() {
    const input = document.getElementById('customBoxInput');
    if (!input || !input.value.trim()) {
        showToast('Input Kosong', 'Masukkan nomor kontainer ISO 6346 (misal: TCLU8827415)', 'warn');
        return;
    }
    const val = input.value.trim().toUpperCase();
    if (val.length !== 11) {
        showToast('Format Salah', 'Nomor kontainer harus tepat 11 karakter (4 huruf + 7 angka)', 'warn');
        return;
    }

    // Hitung Check Digit ISO 6346 Modulo 11
    const charValues = {
        'A': 10, 'B': 12, 'C': 13, 'D': 14, 'E': 15, 'F': 16, 'G': 17, 'H': 18, 'I': 19, 'J': 20,
        'K': 21, 'L': 23, 'M': 24, 'N': 25, 'O': 26, 'P': 27, 'Q': 28, 'R': 29, 'S': 30, 'T': 31,
        'U': 32, 'V': 34, 'W': 35, 'X': 36, 'Y': 37, 'Z': 38
    };

    let sum = 0;
    for (let i = 0; i < 10; i++) {
        const ch = val[i];
        let numVal = 0;
        if (charValues[ch] !== undefined) {
            numVal = charValues[ch];
        } else if (!isNaN(parseInt(ch))) {
            numVal = parseInt(ch);
        } else {
            showToast('Karakter Tidak Valid', `Karakter '${ch}' bukan huruf atau angka ISO standar`, 'error');
            return;
        }
        sum += numVal * Math.pow(2, i);
    }

    let calculatedCheck = sum % 11;
    if (calculatedCheck === 10) calculatedCheck = 0;
    const actualCheck = parseInt(val[10]);

    const statusEl = document.getElementById('scanDecoderStatus');
    const typeEl = document.getElementById('decType');
    const payloadEl = document.getElementById('decPayload');
    const ownerEl = document.getElementById('decOwner');
    const serialEl = document.getElementById('decSerial');
    const checkEl = document.getElementById('decCheck');
    const ymsEl = document.getElementById('decYms');

    if (typeEl) typeEl.innerText = 'ISO 6346 Peti Kemas (Input Kustom)';
    if (payloadEl) payloadEl.innerText = val;
    if (ownerEl) ownerEl.innerText = val.slice(0, 4) + ' (Owner Code)';
    if (serialEl) serialEl.innerText = val.slice(4, 10);

    if (calculatedCheck === actualCheck) {
        focusLocation('yard', 'Verifikasi Peti Kemas: ' + val, { x: -8, y: 0, z: 2 }, 'ISO-6346 VALID');
        if (statusEl) statusEl.innerHTML = '<i class="fa-solid fa-circle-check text-emerald-400 mr-1"></i>CHECK DIGIT VALID';
        if (checkEl) checkEl.innerHTML = `<span class="text-emerald-400 font-bold">${actualCheck} (VALID - Modulo 11 Match)</span>`;
        if (ymsEl) ymsEl.innerText = 'Lolos Validasi AIDC - Siap Dialokasikan ke Yard';
        logTicker(`[ISO 6346 VALID] Nomor ${val} tervalidasi dengan Check Digit ${actualCheck}.`);
        showToast('Check Digit VALID!', `Kontainer ${val} memenuhi standar ISO 6346`, 'success');
    } else {
        if (statusEl) statusEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-rose-400 mr-1"></i>CHECK DIGIT MISMATCH';
        if (checkEl) checkEl.innerHTML = `<span class="text-rose-400 font-bold">${actualCheck} (INVALID - Seharusnya ${calculatedCheck})</span>`;
        if (ymsEl) ymsEl.innerText = 'Gagal Validasi - Potensi Kesalahan Ketik Nomor';
        logTicker(`[ISO 6346 PERINGATAN] Nomor ${val} memiliki check digit ${actualCheck}, seharusnya ${calculatedCheck}!`);
        showToast('Check Digit INVALID!', `Nomor ${val} salah ketik (Seharusnya ${calculatedCheck})`, 'warn');
    }
}

// URL Deep-Linking Initializer for Simulator 3D
setTimeout(() => {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const viewParam = urlParams.get('view');
        const boxParam = urlParams.get('focus_box');
        const blockParam = urlParams.get('block');

        if (boxParam) {
            if (typeof activeContainers !== 'undefined' && Array.isArray(activeContainers)) {
                const found = activeContainers.find(c => (c.container_number || '').toUpperCase() === boxParam.toUpperCase());
                if (found) {
                    const is20ft = (found.size || 40) == 20;
                    const pos = getSlotWorldPosition(found.block, found.bay, found.row, found.tier, is20ft);
                    const camPreset = (found.cargo_type === 'reefer' || found.block === 'R') ? 'reefer' : 'yard';
                    focusLocation(camPreset, `Peti Kemas ${found.container_number} (${found.block}-${found.bay}-${found.row}-${found.tier})`, { x: pos.x, y: 0, z: pos.z }, (found.cargo_type || 'DRY').toUpperCase());
                    showToast('Fokus Peti Kemas 3D', `Menyorot ${found.container_number} di Blok ${found.block}`, 'info');
                } else {
                    focusLocation('yard', `Pencarian Box: ${boxParam}`, { x: 0, y: 0, z: 0 }, 'PENCARIAN');
                }
            }
        } else if (blockParam) {
            const b = blockParam.toUpperCase();
            if (b === 'R' || b === 'REEFER') setCameraView('reefer');
            else if (b === 'RAIL') setCameraView('rail');
            else if (b === 'GATE') setCameraView('gate');
            else if (b === 'CFS') setCameraView('cfs');
            else setCameraView('yard');
        } else if (viewParam) {
            setCameraView(viewParam);
        }
    } catch(err) {
        console.warn('Simulator 3D deep-linking error:', err);
    }
}, 800);
</script>
