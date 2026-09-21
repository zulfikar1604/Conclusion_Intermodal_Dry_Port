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
?>

<!-- Three.js, OrbitControls & Tween.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tween.js/18.6.4/tween.umd.js"></script>

<div class="space-y-6">
    
    <!-- Top Header Banner -->
    <div class="bg-gradient-to-r from-[#002f5e] via-[#004b87] to-[#0170b9] rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
            <i class="fa-solid fa-gamepad text-9xl"></i>
        </div>
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="flex items-center space-x-2.5 mb-2">
                    <span class="bg-emerald-500 text-white text-[11px] font-extrabold uppercase px-2.5 py-0.5 rounded-full flex items-center shadow">
                        <span class="w-2 h-2 rounded-full bg-white animate-ping mr-1.5"></span> LIVE 3D SIMULATOR
                    </span>
                    <span class="bg-white/15 text-blue-100 text-xs px-2.5 py-0.5 rounded-full font-medium border border-white/20">
                        Three.js WebGL Engine v2.0
                    </span>
                    <span class="bg-amber-400/20 text-amber-200 text-xs px-2.5 py-0.5 rounded-full font-semibold border border-amber-300/30">
                        <i class="fa-solid fa-graduation-cap mr-1"></i>Kunci Evaluasi Dosen: Real-Time Stacking Update
                    </span>
                </div>
                <h1 class="text-2xl lg:text-3xl font-bold tracking-tight">Pusat Kendali Simulasi 3D Virtual Terminal</h1>
                <p class="text-blue-100 text-sm mt-1 max-w-3xl">
                    Simulasi 3D terminal intermodal 35 Ha. Anda dapat melakukan <strong>aksi operasional langsung di lapangan 3D</strong> (Relokasi box oleh Reach Stacker, simulasi gerbang timbangan VGM, alih muat KA oleh RTG) yang tersinkronisasi <em>real-time</em> dengan basis data MySQL.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button onclick="openMoveModal()" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-gray-900 font-bold text-xs rounded-xl shadow-md transition flex items-center">
                    <i class="fa-solid fa-dolly mr-2 text-sm"></i>Pindahkan Box (RS)
                </button>
                <button onclick="triggerGateIn()" class="px-4 py-2.5 bg-white hover:bg-blue-50 text-[#004b87] font-bold text-xs rounded-xl shadow-md transition flex items-center">
                    <i class="fa-solid fa-truck-ramp-box mr-2 text-sm text-[#0170b9]"></i>Simulasi Gate-In Truk
                </button>
                <button onclick="triggerRailDischarge()" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center">
                    <i class="fa-solid fa-train-subway mr-2 text-sm"></i>Bongkar KA (RTG)
                </button>
                <button onclick="resetSimulation()" class="px-3 py-2.5 bg-white/10 hover:bg-white/20 text-white font-medium text-xs rounded-xl border border-white/20 transition flex items-center" title="Reset Simulasi ke Posisi Awal">
                    <i class="fa-solid fa-rotate-left mr-1.5"></i>Reset Demo
                </button>
            </div>
        </div>
    </div>

    <!-- 3D Canvas Viewport & Floating HUD System -->
    <div class="relative bg-gray-950 rounded-2xl overflow-hidden shadow-2xl border border-gray-800" style="height: 680px;" id="simulatorViewportContainer">
        
        <!-- WebGL Canvas Container -->
        <div id="webglCanvas" class="w-full h-full cursor-grab active:cursor-grabbing"></div>

        <!-- Floating HUD: Top Camera Controls & Environment Toggle -->
        <div class="absolute top-4 left-4 right-4 flex flex-wrap items-center justify-between gap-3 pointer-events-none">
            
            <!-- Camera Preset Selector -->
            <div class="pointer-events-auto bg-gray-900/85 backdrop-blur-md border border-gray-700/60 rounded-xl px-3 py-2 flex items-center space-x-1.5 shadow-xl text-white">
                <span class="text-xs text-gray-400 font-semibold px-1 mr-1 flex items-center">
                    <i class="fa-solid fa-video mr-1.5 text-blue-400"></i>Sudut Kamera:
                </span>
                <button onclick="setCameraView('overview')" class="cam-btn px-2.5 py-1 text-xs rounded-lg font-medium bg-blue-600 text-white hover:bg-blue-500 transition" id="btnCamOverview">
                    🛰️ Drone 35 Ha
                </button>
                <button onclick="setCameraView('gate')" class="cam-btn px-2.5 py-1 text-xs rounded-lg font-medium bg-gray-800 text-gray-300 hover:bg-gray-700 transition" id="btnCamGate">
                    🚧 Gate & VGM
                </button>
                <button onclick="setCameraView('yard')" class="cam-btn px-2.5 py-1 text-xs rounded-lg font-medium bg-gray-800 text-gray-300 hover:bg-gray-700 transition" id="btnCamYard">
                    🏗️ Blok Yard
                </button>
                <button onclick="setCameraView('rail')" class="cam-btn px-2.5 py-1 text-xs rounded-lg font-medium bg-gray-800 text-gray-300 hover:bg-gray-700 transition" id="btnCamRail">
                    🚂 Rail Siding
                </button>
                <button onclick="setCameraView('cockpit')" class="cam-btn px-2.5 py-1 text-xs rounded-lg font-medium bg-gray-800 text-gray-300 hover:bg-gray-700 transition" id="btnCamCockpit">
                    🕹️ Kabin RS (POV)
                </button>
            </div>

            <!-- Lighting & Environment Controls -->
            <div class="pointer-events-auto bg-gray-900/85 backdrop-blur-md border border-gray-700/60 rounded-xl px-3 py-2 flex items-center space-x-2 shadow-xl text-white">
                <div class="flex items-center space-x-1 border-r border-gray-700 pr-2">
                    <button onclick="setLightingMode('day')" class="light-btn px-2 py-1 text-xs rounded-lg font-medium bg-amber-500/20 text-amber-300 border border-amber-400/30" id="btnLightDay" title="Mode Siang">
                        ☀️ Siang
                    </button>
                    <button onclick="setLightingMode('sunset')" class="light-btn px-2 py-1 text-xs rounded-lg font-medium text-gray-400 hover:text-white" id="btnLightSunset" title="Mode Senja / Sore">
                        🌅 Senja
                    </button>
                    <button onclick="setLightingMode('night')" class="light-btn px-2 py-1 text-xs rounded-lg font-medium text-gray-400 hover:text-white" id="btnLightNight" title="Mode Malam (Lampu Floodlight Aktif)">
                        🌙 Malam
                    </button>
                </div>
                <button onclick="toggleFullscreen()" class="p-1.5 text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition" title="Layar Penuh">
                    <i class="fa-solid fa-expand text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Floating HUD: Bottom Left - Live IoT Activity Ticker -->
        <div class="absolute bottom-4 left-4 pointer-events-auto max-w-sm w-full">
            <div class="bg-gray-900/85 backdrop-blur-md border border-gray-700/60 rounded-xl p-3 shadow-2xl text-white">
                <div class="flex items-center justify-between pb-2 mb-2 border-b border-gray-800">
                    <div class="flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-300">Sensor Telemetry Feed</span>
                    </div>
                    <span class="text-[10px] text-gray-400 font-mono" id="liveClock">14:35:12 WIB</span>
                </div>
                <div class="space-y-1.5 max-h-24 overflow-y-auto text-[11px] font-mono" id="simLogTicker">
                    <div class="text-emerald-400 flex items-start">
                        <span class="text-gray-500 mr-1.5">[SYS]</span>
                        <span>Terminal Engine siap. 19 kontainer dimuat dari MySQL.</span>
                    </div>
                    <div class="text-blue-400 flex items-start">
                        <span class="text-gray-500 mr-1.5">[GPS]</span>
                        <span>RS-02 terhubung di Blok B (Operator: Agus Setiawan).</span>
                    </div>
                    <div class="text-amber-400 flex items-start">
                        <span class="text-gray-500 mr-1.5">[GATE]</span>
                        <span>Kamera ANPR Lane 1 & 2 Standby (Jembatan Timbang 80T Siap).</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating HUD: Bottom Center - Quick Action Control Dock -->
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 pointer-events-auto flex items-center space-x-2 bg-gray-900/90 backdrop-blur-lg border border-gray-700/80 p-2 rounded-2xl shadow-2xl">
            <button onclick="openMoveModal()" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-gray-950 font-bold text-xs rounded-xl shadow transition flex items-center space-x-2">
                <i class="fa-solid fa-arrows-up-down-left-right text-sm"></i>
                <span>Relokasi Box (RS)</span>
            </button>
            <button onclick="triggerGateIn()" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow transition flex items-center space-x-2">
                <i class="fa-solid fa-truck text-sm"></i>
                <span>Gate-In Truk & VGM</span>
            </button>
            <button onclick="triggerRailDischarge()" class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs rounded-xl shadow transition flex items-center space-x-2">
                <i class="fa-solid fa-train text-sm"></i>
                <span>Bongkar KA (RTG)</span>
            </button>
            <button onclick="refreshStateFromDB()" class="px-3 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white text-xs rounded-xl transition" title="Sinkronkan Ulang Data MySQL">
                <i class="fa-solid fa-rotate text-xs"></i>
            </button>
        </div>

        <!-- Floating HUD: Bottom Right - 3D Raycasting Inspector (Klik Objek 3D) -->
        <div class="absolute bottom-4 right-4 pointer-events-auto w-80 transition-all duration-300 hidden" id="inspectorCard">
            <div class="bg-gray-900/95 backdrop-blur-lg border border-blue-500/50 rounded-2xl p-4 shadow-2xl text-white relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-amber-500"></div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-400"></span>
                        <span class="text-xs font-bold uppercase tracking-wider text-blue-300">3D Object Inspector</span>
                    </div>
                    <button onclick="closeInspector()" class="text-gray-400 hover:text-white text-sm">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="space-y-2 text-xs">
                    <div>
                        <div class="text-[10px] text-gray-400 uppercase font-semibold">Nomor Kontainer (ISO 6346)</div>
                        <div class="text-base font-bold font-mono text-white tracking-wider flex items-center justify-between" id="inspBoxNum">
                            MSKU9182374
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-1 border-t border-gray-800">
                        <div>
                            <span class="text-gray-400 text-[10px] block">Tipe / Ukuran:</span>
                            <span class="font-semibold text-gray-200" id="inspType">40FT HIGH CUBE</span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] block">Kategori Muatan:</span>
                            <span class="font-semibold" id="inspCargo">Dry General</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-1 border-t border-gray-800">
                        <div>
                            <span class="text-gray-400 text-[10px] block">Posisi 3D Yard:</span>
                            <span class="font-bold text-amber-400 font-mono text-xs" id="inspSlot">B-05-03-02</span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] block">Berat Kotor (VGM):</span>
                            <span class="font-semibold text-gray-200" id="inspWeight">28.450 kg</span>
                        </div>
                    </div>
                    <div class="pt-1 border-t border-gray-800">
                        <span class="text-gray-400 text-[10px] block">Pemilik / Pelayaran:</span>
                        <span class="font-medium text-gray-300 truncate block" id="inspOwner">Maersk Indonesia</span>
                    </div>
                    <div class="pt-1 border-t border-gray-800">
                        <span class="text-gray-400 text-[10px] block">Tag RFID UHF:</span>
                        <span class="font-mono text-purple-300 text-[11px] block" id="inspRfid">E280117000000001</span>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-800 flex items-center space-x-2">
                    <button onclick="prefillAndOpenMove()" class="w-full py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-gray-950 font-bold text-xs rounded-xl shadow transition flex items-center justify-center space-x-1.5">
                        <i class="fa-solid fa-arrows-up-down-left-right"></i>
                        <span>Pindahkan Box Ini</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Notification Toast (Floating Top Center) -->
        <div id="simToast" class="absolute top-16 left-1/2 -translate-x-1/2 pointer-events-none transition-all duration-300 opacity-0 transform -translate-y-4 z-50">
            <div class="bg-gray-900/95 backdrop-blur-md border border-emerald-500 text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center space-x-3">
                <div class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-base" id="toastIcon">
                    <i class="fa-solid fa-check"></i>
                </div>
                <div>
                    <div class="font-bold text-xs text-white" id="toastTitle">Aksi Berhasil Dieksekusi</div>
                    <div class="text-[11px] text-gray-300" id="toastMsg">Kontainer berhasil dipindahkan.</div>
                </div>
            </div>
        </div>

    </div>

    <!-- 4 Information Cards (Metrik Operasional Simulasi Real-Time) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Kontainer di Lapangan</p>
                <h4 class="text-2xl font-bold text-gray-800 mt-1" id="statInYard">19 <span class="text-xs font-normal text-gray-400">box</span></h4>
                <p class="text-[11px] text-emerald-600 font-medium mt-0.5"><i class="fa-solid fa-check-double mr-1"></i>Tersinkronisasi MySQL</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-cdp-blue flex items-center justify-center text-xl">
                <i class="fa-solid fa-cubes-stacked"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Utilisasi Lapangan Yard</p>
                <h4 class="text-2xl font-bold text-gray-800 mt-1" id="statYardUtil">23.8%</h4>
                <div class="w-24 bg-gray-100 rounded-full h-1.5 mt-1.5">
                    <div class="bg-blue-600 h-1.5 rounded-full" id="statYardBar" style="width: 23.8%"></div>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Armada Alat Aktif</p>
                <h4 class="text-2xl font-bold text-gray-800 mt-1">4 <span class="text-xs font-normal text-gray-400">unit</span></h4>
                <p class="text-[11px] text-gray-500 mt-0.5">3 RS (Kalmar) + 1 RTG Crane</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-dolly"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Kecepatan Angin (Safety)</p>
                <h4 class="text-2xl font-bold text-emerald-600 mt-1">6.4 <span class="text-xs font-normal text-gray-400">m/s</span></h4>
                <p class="text-[11px] text-emerald-600 mt-0.5"><i class="fa-solid fa-shield-halved mr-1"></i>Batas Aman RTG &lt; 20 m/s</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-wind"></i>
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
                            <option value="A">Blok A</option>
                            <option value="B" selected>Blok B</option>
                            <option value="C">Blok C</option>
                            <option value="REEFER">Reefer</option>
                            <option value="DG">DG Yard</option>
                            <option value="EMPTY">Empty</option>
                        </select>
                    </div>
                    <div>
                        <span class="block text-[10px] text-gray-500 font-semibold mb-1">Bay (01-20)</span>
                        <input type="text" id="moveToBay" value="06" class="w-full bg-white border border-gray-200 rounded-lg px-2 py-2 text-xs font-bold text-gray-800 font-mono text-center">
                    </div>
                    <div>
                        <span class="block text-[10px] text-gray-500 font-semibold mb-1">Row (01-06)</span>
                        <input type="text" id="moveToRow" value="02" class="w-full bg-white border border-gray-200 rounded-lg px-2 py-2 text-xs font-bold text-gray-800 font-mono text-center">
                    </div>
                    <div>
                        <span class="block text-[10px] text-gray-500 font-semibold mb-1">Tier (01-04)</span>
                        <input type="text" id="moveToTier" value="01" class="w-full bg-white border border-gray-200 rounded-lg px-2 py-2 text-xs font-bold text-gray-800 font-mono text-center">
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

<!-- SCRIPT UTAMA 3D ENGINE (THREE.JS + TWEEN.JS) -->
<script>
// =============================================================================
// GLOBAL SIMULATION STATE & THREE.JS OBJECTS
// =============================================================================
let scene, camera, renderer, controls;
let containerMeshes = {};       // Mapping container_id / number -> Three.js Mesh
let equipmentMeshes = {};       // Mapping equipment_id -> Three.js Mesh
let truckMesh = null;
let trainMesh = null;
let boomBarrierMesh = null;
let floodlightLights = [];

let currentLighting = 'day';
let isAnimating = false;
let selectedContainerData = null;

// Database state
let simState = {
    containers: [],
    equipment: [],
    trucks: [],
    trains: []
};

// Layout coordinates mapping for terminal blocks
const BLOCK_COORDS = {
    'A':      { x: -35, z: -15, label: 'BLOK A (IMPORT / EXPORT)' },
    'B':      { x: 5,   z: -15, label: 'BLOK B (DOMESTIC / SIDING)' },
    'C':      { x: 45,  z: -15, label: 'BLOK C (HIGH DENSITY)' },
    'REEFER': { x: -35, z: 25,  label: 'REEFER ZONE (300 PLUGS)' },
    'DG':     { x: 5,   z: 25,  label: 'DG YARD (IMO CLASS)' },
    'EMPTY':  { x: 45,  z: 25,  label: 'EMPTY DEPOT (REPAIR)' }
};

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

    // Scene
    scene = new THREE.Scene();
    scene.background = new THREE.Color(0xdcecf8);
    scene.fog = new THREE.FogExp2(0xdcecf8, 0.005);

    // Camera
    camera = new THREE.PerspectiveCamera(45, width / height, 1, 1000);
    camera.position.set(0, 75, 110);

    // Renderer
    renderer = new THREE.WebGLRenderer({ antialias: true, powerPreference: 'high-performance' });
    renderer.setSize(width, height);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    container.appendChild(renderer.domElement);

    // Controls
    controls = new THREE.OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.05;
    controls.maxPolarAngle = Math.PI / 2 - 0.05; // Do not go below ground
    controls.minDistance = 15;
    controls.maxDistance = 220;
    controls.target.set(10, 0, 5);

    // Setup Lighting
    setupLighting();

    // Raycasting for interactive clicks
    setupRaycasting(container);

    // Animation loop
    animate();
}

// Lighting Setup
let ambientLight, sunLight;
function setupLighting() {
    ambientLight = new THREE.AmbientLight(0xffffff, 0.65);
    scene.add(ambientLight);

    sunLight = new THREE.DirectionalLight(0xfff8ee, 0.9);
    sunLight.position.set(60, 100, 50);
    sunLight.castShadow = true;
    sunLight.shadow.mapSize.width = 2048;
    sunLight.shadow.mapSize.height = 2048;
    sunLight.shadow.camera.near = 10;
    sunLight.shadow.camera.far = 300;
    sunLight.shadow.camera.left = -100;
    sunLight.shadow.camera.right = 100;
    sunLight.shadow.camera.top = 100;
    sunLight.shadow.camera.bottom = -100;
    sunLight.shadow.bias = -0.0005;
    scene.add(sunLight);
}

// =============================================================================
// BUILD TERMINAL ENVIRONMENT (GROUND, ZONES, GANTRY, TRACKS, BUILDINGS)
// =============================================================================
function buildTerminalEnvironment() {
    // 1. Concrete Ground Floor (35 Ha Yard)
    const groundGeo = new THREE.PlaneGeometry(240, 180);
    const groundMat = new THREE.MeshStandardMaterial({
        color: 0x334155, // Dark asphalt / concrete
        roughness: 0.85,
        metalness: 0.1
    });
    const ground = new THREE.Mesh(groundGeo, groundMat);
    ground.rotation.x = -Math.PI / 2;
    ground.position.y = 0;
    ground.receiveShadow = true;
    scene.add(ground);

    // Grid Floor Helper (subtle pavement lines)
    const grid = new THREE.GridHelper(240, 48, 0x64748b, 0x475569);
    grid.position.y = 0.02;
    scene.add(grid);

    // 2. Build Yard Stacking Blocks Markings & Concrete Slabs
    for (const [key, bData] of Object.entries(BLOCK_COORDS)) {
        createBlockZone(key, bData.x, bData.z, bData.label);
    }

    // 3. Rail Siding Intermodal (Dual Tracks + Train)
    buildRailSiding();

    // 4. Gate Complex (Gate-In, Weighbridge, Barriers, Scanner Gantry)
    buildGateComplex();

    // 5. Yard Floodlight Towers (for 24/7 night lighting)
    buildFloodlightTowers();

    // 6. Spawn 3D Heavy Equipment Models (RS-01, RS-02, RS-03, RTG-01)
    spawnEquipmentModels();

    // 7. Spawn Prime Mover Truck on Gate Road
    spawnTruckModel();
}

// Create Block Stacking Zone Area
function createBlockZone(blockKey, centerX, centerZ, labelText) {
    const blockWidth = 30;
    const blockDepth = 22;

    // Concrete Slab
    let slabColor = 0x1e293b;
    if (blockKey === 'REEFER') slabColor = 0x0f172a;
    if (blockKey === 'DG') slabColor = 0x450a0a;

    const slabGeo = new THREE.BoxGeometry(blockWidth, 0.2, blockDepth);
    const slabMat = new THREE.MeshStandardMaterial({
        color: slabColor,
        roughness: 0.7
    });
    const slab = new THREE.Mesh(slabGeo, slabMat);
    slab.position.set(centerX + blockWidth/2, 0.1, centerZ + blockDepth/2);
    slab.receiveShadow = true;
    scene.add(slab);

    // Yellow Boundary Safety Line
    const edgeGeo = new THREE.BoxGeometry(blockWidth + 0.6, 0.05, blockDepth + 0.6);
    const edgeMat = new THREE.MeshBasicMaterial({ color: blockKey === 'DG' ? 0xef4444 : 0xf59e0b });
    const edge = new THREE.Mesh(edgeGeo, edgeMat);
    edge.position.set(centerX + blockWidth/2, 0.21, centerZ + blockDepth/2);
    scene.add(edge);

    // Block Label Billboard Sprite
    createBlockLabelSprite(labelText, centerX + blockWidth/2, 5.5, centerZ - 2);

    // Reefer Power Gantry (if reefer block)
    if (blockKey === 'REEFER') {
        createReeferPowerRacks(centerX, centerZ, blockWidth, blockDepth);
    }
}

// Block Label Sprite in 3D Space
function createBlockLabelSprite(text, x, y, z) {
    const canvas = document.createElement('canvas');
    canvas.width = 512;
    canvas.height = 128;
    const ctx = canvas.getContext('2d');
    
    ctx.fillStyle = 'rgba(15, 23, 42, 0.85)';
    ctx.roundRect(10, 10, 492, 108, 20);
    ctx.fill();
    ctx.lineWidth = 4;
    ctx.strokeStyle = '#0170b9';
    ctx.roundRect(10, 10, 492, 108, 20);
    ctx.stroke();

    ctx.font = 'bold 36px Plus Jakarta Sans, sans-serif';
    ctx.fillStyle = '#ffffff';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(text, 256, 64);

    const texture = new THREE.CanvasTexture(canvas);
    const spriteMat = new THREE.SpriteMaterial({ map: texture, transparent: true });
    const sprite = new THREE.Sprite(spriteMat);
    sprite.position.set(x, y, z);
    sprite.scale.set(16, 4, 1);
    scene.add(sprite);
}

// Reefer Power Racks Structure
function createReeferPowerRacks(bx, bz, bw, bd) {
    const rackGeo = new THREE.BoxGeometry(bw - 2, 3.5, 0.4);
    const rackMat = new THREE.MeshStandardMaterial({ color: 0x94a3b8, metalness: 0.6, roughness: 0.3 });
    const rack = new THREE.Mesh(rackGeo, rackMat);
    rack.position.set(bx + bw/2, 1.8, bz + bd/2);
    scene.add(rack);

    // Blue LED sockets
    for (let i = -10; i <= 10; i += 3) {
        const ledGeo = new THREE.SphereGeometry(0.15, 8, 8);
        const ledMat = new THREE.MeshBasicMaterial({ color: 0x38bdf8 });
        const led = new THREE.Mesh(ledGeo, ledMat);
        led.position.set(bx + bw/2 + i, 2.5, bz + bd/2 + 0.25);
        scene.add(led);
    }
}

// Intermodal Rail Siding (Twin Railway Tracks + Freight Train)
function buildRailSiding() {
    const trackZ = -45;
    const railLength = 200;

    // Ballast Gravel Bed
    const ballastGeo = new THREE.BoxGeometry(railLength, 0.3, 14);
    const ballastMat = new THREE.MeshStandardMaterial({ color: 0x475569, roughness: 0.95 });
    const ballast = new THREE.Mesh(ballastGeo, ballastMat);
    ballast.position.set(0, 0.15, trackZ);
    ballast.receiveShadow = true;
    scene.add(ballast);

    // Sleepers (Bantalan Rel Beton)
    const sleeperGeo = new THREE.BoxGeometry(0.4, 0.2, 12);
    const sleeperMat = new THREE.MeshStandardMaterial({ color: 0xcbd5e1, roughness: 0.8 });
    for (let x = -95; x <= 95; x += 2.5) {
        const sleeper = new THREE.Mesh(sleeperGeo, sleeperMat);
        sleeper.position.set(x, 0.35, trackZ);
        scene.add(sleeper);
    }

    // Steel Rails (Rel Baja - Track 1 & Track 2)
    const railSteelMat = new THREE.MeshStandardMaterial({ color: 0xe2e8f0, metalness: 0.9, roughness: 0.2 });
    const railOffsets = [-4.5, -2.5, 2.5, 4.5];
    railOffsets.forEach(offsetZ => {
        const railGeo = new THREE.BoxGeometry(railLength, 0.25, 0.15);
        const rail = new THREE.Mesh(railGeo, railSteelMat);
        rail.position.set(0, 0.55, trackZ + offsetZ);
        scene.add(rail);
    });

    // Spawn Freight Train on Track 1
    buildFreightTrain(trackZ - 3.5);

    // Rail Siding Signboard
    createBlockLabelSprite('RAIL SIDING KAI LOGISTIK (INTERMODAL)', 0, 7, trackZ + 9);
}

// Freight Train Model
function buildFreightTrain(zPos) {
    trainMesh = new THREE.Group();

    // Diesel Locomotive (PT KAI Logistik Orange / White)
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
    }

    trainMesh.position.set(0, 0, zPos);
    scene.add(trainMesh);
}

// Gate Complex (Gate-In, Weighbridge, Boom Barriers, Scanner Arch)
function buildGateComplex() {
    const gateZ = 55;
    const gateX = -10;

    // Gate Gantry Arch
    const gantryMat = new THREE.MeshStandardMaterial({ color: 0x004b87, metalness: 0.5, roughness: 0.4 });
    
    // Pillars
    const pillarGeo = new THREE.BoxGeometry(1.2, 7, 1.2);
    const p1 = new THREE.Mesh(pillarGeo, gantryMat);
    p1.position.set(gateX - 14, 3.5, gateZ);
    scene.add(p1);

    const p2 = new THREE.Mesh(pillarGeo, gantryMat);
    p2.position.set(gateX + 14, 3.5, gateZ);
    scene.add(p2);

    // Crossbeam
    const beamGeo = new THREE.BoxGeometry(29.2, 1.6, 1.6);
    const beam = new THREE.Mesh(beamGeo, gantryMat);
    beam.position.set(gateX, 7.8, gateZ);
    scene.add(beam);

    // Signboard on Gantry
    createBlockLabelSprite('MAIN GATE & JEMBATAN TIMBANG VGM (80T)', gateX, 9.8, gateZ);

    // Weighbridge Pit & Steel Scale Platform (Lane 1)
    const scaleGeo = new THREE.BoxGeometry(16, 0.15, 4.5);
    const scaleMat = new THREE.MeshStandardMaterial({ color: 0x475569, metalness: 0.7, roughness: 0.3 });
    const scale = new THREE.Mesh(scaleGeo, scaleMat);
    scale.position.set(gateX - 7, 0.08, gateZ);
    scale.receiveShadow = true;
    scene.add(scale);

    // Digital Weighbridge Indicator Display Gantry
    const ledDispGeo = new THREE.BoxGeometry(2.5, 1.2, 0.4);
    const ledDispMat = new THREE.MeshBasicMaterial({ color: 0x10b981 });
    const ledDisp = new THREE.Mesh(ledDispGeo, ledDispMat);
    ledDisp.position.set(gateX - 7, 4.5, gateZ - 3);
    scene.add(ledDisp);

    // Boom Barrier Pole (Animated Palang Pintu)
    const barrierBaseGeo = new THREE.CylinderGeometry(0.4, 0.4, 1.4, 16);
    const barrierBaseMat = new THREE.MeshStandardMaterial({ color: 0xf59e0b });
    const bBase = new THREE.Mesh(barrierBaseGeo, barrierBaseMat);
    bBase.position.set(gateX - 11, 0.7, gateZ + 3);
    scene.add(bBase);

    const poleGeo = new THREE.CylinderGeometry(0.1, 0.1, 7, 8);
    const poleMat = new THREE.MeshBasicMaterial({ color: 0xdc2626 });
    boomBarrierMesh = new THREE.Mesh(poleGeo, poleMat);
    boomBarrierMesh.rotation.z = Math.PI / 2;
    boomBarrierMesh.position.set(gateX - 7.5, 1.2, gateZ + 3);
    scene.add(boomBarrierMesh);
}

// Yard Floodlight High Towers
function buildFloodlightTowers() {
    const towerCoords = [
        { x: -55, z: -35 },
        { x: 75,  z: -35 },
        { x: -55, z: 45 },
        { x: 75,  z: 45 }
    ];

    towerCoords.forEach((tc, idx) => {
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

// Spawn Heavy Equipment (3 Reach Stackers + 1 RTG Crane)
function spawnEquipmentModels() {
    // RS-01 (Block A Area)
    equipmentMeshes['RS-01'] = createReachStackerModel('RS-01', -25, 0, 0);
    
    // RS-02 (Block B Area - Primary Active)
    equipmentMeshes['RS-02'] = createReachStackerModel('RS-02', 18, 0, 0);

    // RS-03 (Reefer Area)
    equipmentMeshes['RS-03'] = createReachStackerModel('RS-03', -25, 0, 38);

    // RTG-01 (Rail Siding Crane spanning across track and buffer)
    equipmentMeshes['RTG-01'] = createRTGCraneModel('RTG-01', -15, 0, -38);
}

// Procedural 3D Reach Stacker Model (Kalmar DRG450)
function createReachStackerModel(name, x, y, z) {
    const rs = new THREE.Group();
    rs.name = name;

    // 1. Chassis Body (Kalmar Red / Navy)
    const chassisGeo = new THREE.BoxGeometry(8, 2.2, 4);
    const chassisMat = new THREE.MeshStandardMaterial({ color: 0xd9232a, roughness: 0.4 });
    const chassis = new THREE.Mesh(chassisGeo, chassisMat);
    chassis.position.set(0, 1.8, 0);
    chassis.castShadow = true;
    rs.add(chassis);

    // 2. Heavy Rubber Wheels
    const wheelGeo = new THREE.CylinderGeometry(1.2, 1.2, 0.9, 16);
    const wheelMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.9 });
    const wheelOffsets = [
        { x: -2.8, z: 2.2 }, { x: 2.8, z: 2.2 },
        { x: -2.8, z: -2.2 }, { x: 2.8, z: -2.2 }
    ];
    wheelOffsets.forEach(wo => {
        const wheel = new THREE.Mesh(wheelGeo, wheelMat);
        wheel.rotation.x = Math.PI / 2;
        wheel.position.set(wo.x, 1.2, wo.z);
        wheel.castShadow = true;
        rs.add(wheel);
    });

    // 3. Cabin (Glass Canopy)
    const cabGeo = new THREE.BoxGeometry(2.5, 2.0, 2.2);
    const cabMat = new THREE.MeshStandardMaterial({ color: 0x38bdf8, roughness: 0.1, transparent: true, opacity: 0.75 });
    const cab = new THREE.Mesh(cabGeo, cabMat);
    cab.position.set(-1.2, 3.8, 0);
    rs.add(cab);

    // 4. Rear Counterweight
    const cwGeo = new THREE.BoxGeometry(2.4, 2.6, 3.8);
    const cwMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.5 });
    const cw = new THREE.Mesh(cwGeo, cwMat);
    cw.position.set(-3.2, 2.4, 0);
    rs.add(cw);

    // 5. Telescopic Boom Arm & Hydraulic Piston
    const boomGeo = new THREE.BoxGeometry(9, 1.0, 1.2);
    const boomMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, metalness: 0.7, roughness: 0.3 });
    const boom = new THREE.Mesh(boomGeo, boomMat);
    boom.position.set(2.8, 5.2, 0);
    boom.rotation.z = Math.PI / 10;
    boom.castShadow = true;
    rs.add(boom);

    // 6. Spreader Bar with Twistlocks
    const spreaderGeo = new THREE.BoxGeometry(6.5, 0.6, 2.4);
    const spreaderMat = new THREE.MeshStandardMaterial({ color: 0xf59e0b, roughness: 0.4 });
    const spreader = new THREE.Mesh(spreaderGeo, spreaderMat);
    spreader.position.set(6.8, 5.8, 0);
    spreader.castShadow = true;
    rs.add(spreader);

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
    trolley.position.set(0, 18.5, 0);
    rtg.add(trolley);

    // Spreader Suspended
    const rtgSpreaderGeo = new THREE.BoxGeometry(6.5, 0.6, 2.5);
    const rtgSpreader = new THREE.Mesh(rtgSpreaderGeo, trolleyMat);
    rtgSpreader.position.set(0, 12, 0);
    rtgSpreader.castShadow = true;
    rtg.add(rtgSpreader);

    rtg.position.set(x, y, z);
    scene.add(rtg);
    return rtg;
}

// Procedural 3D Truck Prime Mover
function spawnTruckModel() {
    truckMesh = new THREE.Group();

    // Cabin
    const cabGeo = new THREE.BoxGeometry(3.5, 3.2, 2.8);
    const cabMat = new THREE.MeshStandardMaterial({ color: 0x0284c7, roughness: 0.4 });
    const cab = new THREE.Mesh(cabGeo, cabMat);
    cab.position.set(6, 2.2, 0);
    cab.castShadow = true;
    truckMesh.add(cab);

    // Windshield
    const wsGeo = new THREE.BoxGeometry(0.8, 1.4, 2.7);
    const wsMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.1 });
    const ws = new THREE.Mesh(wsGeo, wsMat);
    ws.position.set(7.5, 2.6, 0);
    truckMesh.add(ws);

    // Trailer Chassis
    const trailerGeo = new THREE.BoxGeometry(13, 0.8, 2.8);
    const trailerMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, metalness: 0.6 });
    const trailer = new THREE.Mesh(trailerGeo, trailerMat);
    trailer.position.set(-1.5, 1.2, 0);
    trailer.castShadow = true;
    truckMesh.add(trailer);

    // Wheels
    const wGeo = new THREE.CylinderGeometry(0.9, 0.9, 0.7, 16);
    const wMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.9 });
    [-6.5, -4.5, 4.5, 6.8].forEach(wx => {
        [-1.5, 1.5].forEach(wz => {
            const wheel = new THREE.Mesh(wGeo, wMat);
            wheel.rotation.x = Math.PI / 2;
            wheel.position.set(wx, 0.9, wz);
            truckMesh.add(wheel);
        });
    });

    // Container on Truck
    const cGeo = new THREE.BoxGeometry(11.8, 2.7, 2.6);
    const cMat = new THREE.MeshStandardMaterial({ color: 0xd49b00, roughness: 0.5 });
    const box = new THREE.Mesh(cGeo, cMat);
    box.position.set(-1.5, 2.9, 0);
    box.castShadow = true;
    truckMesh.add(box);

    truckMesh.position.set(-17, 0, 55); // Parked at Gate-In weighbridge
    scene.add(truckMesh);
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

// Render Containers onto 3D Yard Blocks
function renderContainersFromState(containers) {
    // Clear existing container meshes
    for (const [key, mesh] of Object.entries(containerMeshes)) {
        scene.remove(mesh);
    }
    containerMeshes = {};

    containers.forEach(c => {
        if (c.status !== 'in_yard') return;

        const bCoord = BLOCK_COORDS[c.block];
        if (!bCoord) return;

        const bayNum  = parseInt(c.bay, 10) || 1;
        const rowNum  = parseInt(c.row, 10) || 1;
        const tierNum = parseInt(c.tier, 10) || 1;

        // Container 3D Dimensions
        const cLength = 5.8;
        const cWidth  = 2.4;
        const cHeight = 2.4;

        // Calculate 3D World Position
        // X spreads by Bay (01-05), Z spreads by Row (01-04), Y stacks by Tier (01-04)
        const posX = bCoord.x + 3.0 + ((bayNum - 1) % 5) * 6.2;
        const posZ = bCoord.z + 2.5 + ((rowNum - 1) % 3) * 3.2;
        const posY = (cHeight / 2) + ((tierNum - 1) * cHeight);

        // Pick color based on cargo or company
        let colorHex = SHIPPING_COLORS['Maersk'];
        if (c.cargo_type === 'reefer') colorHex = SHIPPING_COLORS['reefer'];
        else if (c.cargo_type === 'dg') colorHex = SHIPPING_COLORS['dg'];
        else if (c.cargo_type === 'empty') colorHex = SHIPPING_COLORS['empty'];
        else if (c.owner_company && c.owner_company.includes('Evergreen')) colorHex = SHIPPING_COLORS['Evergreen'];
        else if (c.owner_company && c.owner_company.includes('CMA')) colorHex = SHIPPING_COLORS['CMA CGM'];
        else if (c.owner_company && c.owner_company.includes('Meratus')) colorHex = SHIPPING_COLORS['Meratus'];
        else if (c.owner_company && c.owner_company.includes('Samudera')) colorHex = SHIPPING_COLORS['Samudera'];

        // Create 3D Container Mesh
        const mesh = createContainerMesh(cLength, cHeight, cWidth, colorHex, c.container_number);
        mesh.position.set(posX, posY, posZ);
        mesh.userData = c; // Attach full DB data for raycasting inspection!

        scene.add(mesh);
        containerMeshes[c.container_number] = mesh;
    });
}

// Procedural 3D Container Mesh with Corrugated Texture Simulation
function createContainerMesh(l, h, w, colorHex, boxNumber) {
    const group = new THREE.Group();

    const boxGeo = new THREE.BoxGeometry(l, h, w);
    const boxMat = new THREE.MeshStandardMaterial({
        color: colorHex,
        roughness: 0.45,
        metalness: 0.15
    });
    const mainBox = new THREE.Mesh(boxGeo, boxMat);
    mainBox.castShadow = true;
    mainBox.receiveShadow = true;
    group.add(mainBox);

    // Edge corner castings
    const edgeGeo = new THREE.BoxGeometry(l + 0.05, h + 0.05, w + 0.05);
    const edgeMat = new THREE.MeshBasicMaterial({ color: 0x0f172a, wireframe: true });
    const wire = new THREE.Mesh(edgeGeo, edgeMat);
    group.add(wire);

    // Top Corrugated Grooves (Fine details)
    const ribGeo = new THREE.BoxGeometry(0.15, 0.08, w - 0.2);
    const ribMat = new THREE.MeshStandardMaterial({ color: colorHex, roughness: 0.6 });
    for (let rx = -l/2 + 0.4; rx <= l/2 - 0.4; rx += 0.5) {
        const rib = new THREE.Mesh(ribGeo, ribMat);
        rib.position.set(rx, h/2 + 0.04, 0);
        group.add(rib);
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

    logTicker(`[INSPECTOR] Kontainer ${data.container_number} dipilih di slot ${data.block}-${data.bay}-${data.row}-${data.tier}.`);
}

function closeInspector() {
    document.getElementById('inspectorCard').classList.add('hidden');
    selectedContainerData = null;
}

function prefillAndOpenMove() {
    if (!selectedContainerData) return;
    openMoveModal();
    document.getElementById('moveContainerSelect').value = selectedContainerData.id;
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
    sel.innerHTML = '';
    containers.forEach(c => {
        if (c.status === 'in_yard') {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = `📦 ${c.container_number} — ${c.owner_company} (Saat ini di ${c.block}-${c.bay}-${c.row}-${c.tier})`;
            sel.appendChild(opt);
        }
    });
}

function handleMoveSubmit(e) {
    e.preventDefault();
    if (isAnimating) {
        showToast('Peringatan', 'Sedang ada operasi alat berat berlangsung. Tunggu sebentar!', 'warn');
        return;
    }

    const containerId = document.getElementById('moveContainerSelect').value;
    const equipmentId = document.getElementById('moveEquipmentSelect').value;
    const toBlock     = document.getElementById('moveToBlock').value;
    const toBay       = document.getElementById('moveToBay').value;
    const toRow       = document.getElementById('moveToRow').value;
    const toTier      = document.getElementById('moveToTier').value;

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
            equipment_id: equipmentId,
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
            
            // Trigger 3D Reach Stacker Animation!
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

// Reach Stacker Moving & Stacking Animation
function animateReachStackerMove(boxNumber, fromSlot, toSlot, equipmentId) {
    const targetMesh = containerMeshes[boxNumber];
    const rsMesh = equipmentMeshes[equipmentId] || equipmentMeshes['RS-02'];
    if (!targetMesh || !rsMesh) {
        fetchSimulationState();
        return;
    }

    isAnimating = true;

    // Parse target coordinates
    const toParts = toSlot.split('-');
    const toBlock = toParts[0];
    const toBay = parseInt(toParts[1], 10) || 1;
    const toRow = parseInt(toParts[2], 10) || 1;
    const toTier = parseInt(toParts[3], 10) || 1;

    const bCoord = BLOCK_COORDS[toBlock];
    const targetX = bCoord.x + 3.0 + ((toBay - 1) % 5) * 6.2;
    const targetZ = bCoord.z + 2.5 + ((toRow - 1) % 3) * 3.2;
    const targetY = 1.2 + ((toTier - 1) * 2.4);

    // Step 1: RS moves to source container
    new TWEEN.Tween(rsMesh.position)
        .to({ x: targetMesh.position.x + 8, z: targetMesh.position.z }, 1200)
        .easing(TWEEN.Easing.Quadratic.Out)
        .onComplete(() => {
            // Step 2: Lift Container Up
            new TWEEN.Tween(targetMesh.position)
                .to({ y: targetMesh.position.y + 7 }, 800)
                .easing(TWEEN.Easing.Quadratic.Out)
                .onComplete(() => {
                    // Step 3: RS & Container travel together to destination
                    new TWEEN.Tween(rsMesh.position)
                        .to({ x: targetX + 8, z: targetZ }, 1800)
                        .easing(TWEEN.Easing.Quadratic.InOut)
                        .start();

                    new TWEEN.Tween(targetMesh.position)
                        .to({ x: targetX, z: targetZ, y: targetY + 5 }, 1800)
                        .easing(TWEEN.Easing.Quadratic.InOut)
                        .onComplete(() => {
                            // Step 4: Lower and Stack onto slot
                            new TWEEN.Tween(targetMesh.position)
                                .to({ y: targetY }, 800)
                                .easing(TWEEN.Easing.Bounce.Out)
                                .onComplete(() => {
                                    isAnimating = false;
                                    logTicker(`[STACKED] ${boxNumber} telah ditumpuk sempurna di Tier ${toTier}.`);
                                    fetchSimulationState(); // refresh DB state
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
// AKSI 2: SIMULASI GATE-IN TRUK & PENIMBANGAN JEMBATAN TIMBANG VGM SOLAS
// =============================================================================
function triggerGateIn() {
    if (!truckMesh || isAnimating) return;
    isAnimating = true;

    logTicker(`[ANPR CAMERA] Mendeteksi truk mendekati Gate 1...`);

    // Reset truck position at highway approach
    truckMesh.position.set(-17, 0, 75);

    // Step 1: Drive onto Weighbridge scale
    new TWEEN.Tween(truckMesh.position)
        .to({ z: 55 }, 1500)
        .easing(TWEEN.Easing.Quadratic.Out)
        .onComplete(() => {
            // Weighbridge flash effect
            logTicker(`[WEIGHBRIDGE] Menimbang muatan kargo...`);
            
            fetch('api/simulator_action.php?action=gate_in')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const trk = data.truck;
                        showToast('Gate-In & Timbang VGM Berhasil', `${trk.license_plate} (${trk.company}): Gross ${trk.gross_weight.toLocaleString()} kg, VGM Net ${trk.vgm_net.toLocaleString()} kg - SOLAS PASS!`, 'success');
                        logTicker(`[VGM SOLAS] Plat ${trk.license_plate}: Gross ${trk.gross_weight}kg, VGM Net ${trk.vgm_net}kg. Status: ${trk.vgm_status}.`);

                        // Step 2: Open Boom Barrier (Rotate up)
                        new TWEEN.Tween(boomBarrierMesh.rotation)
                            .to({ x: -Math.PI / 2.5 }, 600)
                            .easing(TWEEN.Easing.Quadratic.Out)
                            .onComplete(() => {
                                // Step 3: Truck enters yard road
                                new TWEEN.Tween(truckMesh.position)
                                    .to({ z: 30 }, 1500)
                                    .easing(TWEEN.Easing.Quadratic.In)
                                    .onComplete(() => {
                                        // Close barrier
                                        new TWEEN.Tween(boomBarrierMesh.rotation)
                                            .to({ x: 0 }, 600)
                                            .start();
                                        isAnimating = false;
                                        fetchSimulationState();
                                    })
                                    .start();
                            })
                            .start();
                    }
                });
        })
        .start();
}

// =============================================================================
// AKSI 3: SIMULASI BONGKAR MUAT KA LOGISTIK OLEH RTG CRANE
// =============================================================================
function triggerRailDischarge() {
    const rtg = equipmentMeshes['RTG-01'];
    if (!rtg || isAnimating) return;

    isAnimating = true;
    logTicker(`[RTG-01] Memulai alih muat kontainer dari Rangkaian Kereta Api...`);

    fetch('api/simulator_action.php?action=rail_discharge')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Alih Muat KA Berhasil', data.message, 'success');
                logTicker(`[RAIL STEVEDORING] Kontainer ${data.container.container_number} dipindahkan dari kereta ke Blok A.`);
                
                // Animate RTG Trolley
                new TWEEN.Tween(rtg.position)
                    .to({ x: rtg.position.x + 8 }, 1200)
                    .easing(TWEEN.Easing.Quadratic.InOut)
                    .onComplete(() => {
                        new TWEEN.Tween(rtg.position)
                            .to({ x: rtg.position.x - 8 }, 1200)
                            .easing(TWEEN.Easing.Quadratic.InOut)
                            .onComplete(() => {
                                isAnimating = false;
                                fetchSimulationState();
                            })
                            .start();
                    })
                    .start();
            }
        });
}

// =============================================================================
// RESET SIMULATION
// =============================================================================
function resetSimulation() {
    if (!confirm('Apakah Anda ingin mereset seluruh posisi kontainer dan alat berat kembali ke konfigurasi awal demo?')) return;

    fetch('api/simulator_action.php?action=reset_simulation')
        .then(res => res.json())
        .then(data => {
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
// CAMERA PRESETS & ENVIRONMENT CONTROLS
// =============================================================================
function setCameraView(preset) {
    document.querySelectorAll('.cam-btn').forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white');
        btn.classList.add('bg-gray-800', 'text-gray-300');
    });

    let targetPos, targetLook;

    switch(preset) {
        case 'overview':
            targetPos = { x: 0, y: 75, z: 110 };
            targetLook = { x: 10, y: 0, z: 5 };
            document.getElementById('btnCamOverview').classList.add('bg-blue-600', 'text-white');
            break;
        case 'gate':
            targetPos = { x: -10, y: 15, z: 80 };
            targetLook = { x: -10, y: 2, z: 55 };
            document.getElementById('btnCamGate').classList.add('bg-blue-600', 'text-white');
            break;
        case 'yard':
            targetPos = { x: 25, y: 28, z: 25 };
            targetLook = { x: 20, y: 4, z: -10 };
            document.getElementById('btnCamYard').classList.add('bg-blue-600', 'text-white');
            break;
        case 'rail':
            targetPos = { x: 0, y: 25, z: -15 };
            targetLook = { x: 0, y: 4, z: -45 };
            document.getElementById('btnCamRail').classList.add('bg-blue-600', 'text-white');
            break;
        case 'cockpit':
            const rs = equipmentMeshes['RS-02'];
            const rsX = rs ? rs.position.x : 18;
            const rsZ = rs ? rs.position.z : 0;
            targetPos = { x: rsX - 1.2, y: 4.5, z: rsZ };
            targetLook = { x: rsX + 30, y: 4, z: rsZ };
            document.getElementById('btnCamCockpit').classList.add('bg-blue-600', 'text-white');
            break;
    }

    new TWEEN.Tween(camera.position)
        .to(targetPos, 1000)
        .easing(TWEEN.Easing.Cubic.Out)
        .start();

    new TWEEN.Tween(controls.target)
        .to(targetLook, 1000)
        .easing(TWEEN.Easing.Cubic.Out)
        .start();
}

// Lighting Day / Sunset / Night Toggle
function setLightingMode(mode) {
    currentLighting = mode;
    document.querySelectorAll('.light-btn').forEach(btn => {
        btn.classList.remove('bg-amber-500/20', 'text-amber-300', 'border', 'border-amber-400/30');
        btn.classList.add('text-gray-400');
    });

    if (mode === 'day') {
        scene.background = new THREE.Color(0xdcecf8);
        scene.fog.color = new THREE.Color(0xdcecf8);
        ambientLight.color.setHex(0xffffff);
        ambientLight.intensity = 0.65;
        sunLight.intensity = 0.9;
        sunLight.color.setHex(0xfff8ee);
        floodlightLights.forEach(l => l.intensity = 0);
        document.getElementById('btnLightDay').classList.add('bg-amber-500/20', 'text-amber-300', 'border', 'border-amber-400/30');
    } else if (mode === 'sunset') {
        scene.background = new THREE.Color(0xfdba74);
        scene.fog.color = new THREE.Color(0xfdba74);
        ambientLight.color.setHex(0xfb923c);
        ambientLight.intensity = 0.5;
        sunLight.intensity = 0.7;
        sunLight.color.setHex(0xea580c);
        floodlightLights.forEach(l => l.intensity = 0.5);
        document.getElementById('btnLightSunset').classList.add('bg-amber-500/20', 'text-amber-300', 'border', 'border-amber-400/30');
    } else if (mode === 'night') {
        scene.background = new THREE.Color(0x020617);
        scene.fog.color = new THREE.Color(0x020617);
        ambientLight.color.setHex(0x1e293b);
        ambientLight.intensity = 0.2;
        sunLight.intensity = 0.1;
        floodlightLights.forEach(l => l.intensity = 2.2); // Floodlight ON!
        document.getElementById('btnLightNight').classList.add('bg-amber-500/20', 'text-amber-300', 'border', 'border-amber-400/30');
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
    renderer.render(scene, camera);
}
</script>
