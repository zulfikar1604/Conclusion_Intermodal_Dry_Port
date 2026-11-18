<?php
/**
 * Conclusion Intermodal Dry Port (CIDP) 35 Ha
 * Modul: Real-Time 3D WebGL (Three.js) & 2D Digital Twin Monitoring Console
 * File: pages/digital_twin.php
 * 
 * Memantau 3 Aliran Sinkron secara Real-Time (Event-Driven):
 * 1. Aliran Fisik (3D WebGL Three.js Scene & 2D Spatial SVG — Alat bergerak HANYA saat ada data masuk/berubah)
 * 2. Aliran Data / Informasi (Telemetri RFID, OCR, CEISA 4.0, IoT Reefer, CAN-Bus Alat Berat)
 * 3. Aliran Finansial (Argo Akrual Tarif, LoLo, VGM, Dwell Time, Invoice)
 */

require_once __DIR__ . '/layout_master.php';
require_once __DIR__ . '/../api/twin_state_helper.php';

// Ambil snapshot awal state
$twin_initial = get_live_twin_state($pdo, false);
?>

<!-- Three.js, OrbitControls & Tween.js (Offline Local Vendor dengan CDN Fallback) -->
<script src="assets/vendor/three.min.js"></script>
<script>if (typeof THREE === 'undefined') document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"><\/script>');</script>
<script src="assets/vendor/OrbitControls.js"></script>
<script>if (typeof THREE !== 'undefined' && typeof THREE.OrbitControls === 'undefined') document.write('<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"><\/script>');</script>
<script src="assets/vendor/tween.umd.js"></script>
<script>if (typeof TWEEN === 'undefined') document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/tween.js/18.6.4/tween.umd.js"><\/script>');</script>

<style>
@keyframes twinFloatToast {
    0% { opacity: 0; transform: translateY(12px) scale(0.9); }
    15% { opacity: 1; transform: translateY(0px) scale(1.02); }
    80% { opacity: 1; transform: translateY(-16px) scale(1); }
    100% { opacity: 0; transform: translateY(-32px) scale(0.95); }
}
.twin-toast-anim {
    animation: twinFloatToast 2.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.twin-3d-label {
    position: absolute;
    transform: translate(-50%, -100%);
    pointer-events: auto;
    cursor: pointer;
    user-select: none;
    transition: opacity 0.15s ease;
    will-change: left, top;
}
</style>

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
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-blue-50 text-[#0170b9] border border-blue-200">
                    <i class="fa-solid fa-cube animate-pulse"></i> WebGL 3D + Three.js Engine
                </span>
                <span id="twinConnectionBadge" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                    <span id="twinConnectionText">SSE Stream Live</span>
                </span>
                <span id="twinMotionStateBadge" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200">
                    <i class="fa-solid fa-satellite-dish text-slate-400"></i>
                    <span id="twinMotionStateText">Standby: Menunggu Data Masuk</span>
                </span>
                <span class="text-[11px] font-mono text-slate-400">
                    Latensi: <strong id="latencyDisplay" class="text-slate-600 font-bold">&lt; 35ms</strong>
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1">
                Dashboard Digital Twin 3D <span class="text-[#0170b9]">WebGL</span> &amp; <span class="text-orange-600">Monitoring Real-Time</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5 max-w-3xl leading-relaxed">
                Visualisasi spasial 3D berbasis <strong>Three.js &amp; WebGL</strong> yang digerakkan murni oleh data masuk (<em>Event-Driven</em>): alat berat (Reach Stacker, RTG, Truk, Palang Gate) <strong>hanya bergerak ketika ada paket telemetri atau transaksi data baru</strong> yang masuk ke sistem.
            </p>
        </div>

        <!-- Right Quick Controls: Edge Telemetry Simulator Panel Trigger -->
        <div class="relative z-10 flex flex-wrap items-center gap-2 w-full xl:w-auto">
            <!-- Mode Switcher: 3D WebGL vs 2D SVG -->
            <div class="inline-flex rounded-xl p-0.5 bg-slate-100 border border-slate-200/90">
                <button type="button" onclick="switchTwinRenderMode('3d')" id="btnMode3D" class="px-3 py-1.5 rounded-lg text-xs font-extrabold transition-all flex items-center gap-1.5 bg-[#0170b9] text-white shadow-xs cursor-pointer">
                    <i class="fa-solid fa-cubes"></i>
                    <span>3D WebGL</span>
                </button>
                <button type="button" onclick="switchTwinRenderMode('2d')" id="btnMode2D" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-slate-600 hover:text-slate-900 cursor-pointer">
                    <i class="fa-solid fa-map"></i>
                    <span>2D Blueprint</span>
                </button>
            </div>

            <button type="button" onclick="runSimulatorAutoCycle()" id="btnAutoCycle" class="px-3.5 py-2 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs shadow-xs transition-all flex items-center gap-2 transform active:scale-95 cursor-pointer">
                <i class="fa-solid fa-play text-xs"></i>
                <span>Jalankan Siklus Data (Step 1-6)</span>
            </button>

            <div class="relative inline-block text-left">
                <button type="button" onclick="toggleSimStepDropdown()" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs border border-slate-300/80 transition-colors flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-sliders text-xs text-slate-500"></i>
                    <span>Pemicu Event Fisik (Yard &amp; Gudang)</span>
                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                </button>
                <div id="simStepDropdown" class="hidden absolute right-0 mt-2 w-96 max-h-[480px] overflow-y-auto bg-white rounded-2xl shadow-xl border border-slate-200 p-2 z-50 space-y-1 text-xs">
                    <div class="px-3 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        A. Alur Utama Gate &rarr; Yard &rarr; Billing (Step 1–6):
                    </div>
                    <button type="button" onclick="triggerSimStep(1)" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-orange-50 text-slate-700 hover:text-orange-600 font-medium flex items-center gap-2 cursor-pointer">
                        <span class="w-5 h-5 rounded-md bg-orange-100 text-orange-600 font-bold text-[10px] flex items-center justify-center shrink-0">1</span>
                        <span>Truk Tiba di Gate In (Scan OCR &amp; Buka Barrier)</span>
                    </button>
                    <button type="button" onclick="triggerSimStep(2)" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-orange-50 text-slate-700 hover:text-orange-600 font-medium flex items-center gap-2 cursor-pointer">
                        <span class="w-5 h-5 rounded-md bg-blue-100 text-blue-600 font-bold text-[10px] flex items-center justify-center shrink-0">2</span>
                        <span>Timbangan 80T VGM &amp; Truk ke Transfer Blok (+Rp 75k)</span>
                    </button>
                    <button type="button" onclick="triggerSimStep(3)" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-orange-50 text-slate-700 hover:text-orange-600 font-medium flex items-center gap-2 cursor-pointer">
                        <span class="w-5 h-5 rounded-md bg-amber-100 text-amber-600 font-bold text-[10px] flex items-center justify-center shrink-0">3</span>
                        <span>RS-02 Jalan ke Truk &amp; Angkat Kontainer (+Rp 325k)</span>
                    </button>
                    <button type="button" onclick="triggerSimStep(4)" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-orange-50 text-slate-700 hover:text-orange-600 font-medium flex items-center gap-2 cursor-pointer">
                        <span class="w-5 h-5 rounded-md bg-purple-100 text-purple-600 font-bold text-[10px] flex items-center justify-center shrink-0">4</span>
                        <span>RS-02 Tumpuk Kontainer di Slot Yard (Blok-Bay-Row-Tier)</span>
                    </button>
                    <button type="button" onclick="triggerSimStep(5)" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-orange-50 text-slate-700 hover:text-orange-600 font-medium flex items-center gap-2 cursor-pointer">
                        <span class="w-5 h-5 rounded-md bg-emerald-100 text-emerald-600 font-bold text-[10px] flex items-center justify-center shrink-0">5</span>
                        <span>CEISA 4.0 DJBC: Validasi SPPB Cleared</span>
                    </button>
                    <button type="button" onclick="triggerSimStep(6)" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-orange-50 text-slate-700 hover:text-orange-600 font-medium flex items-center gap-2 cursor-pointer">
                        <span class="w-5 h-5 rounded-md bg-green-100 text-green-700 font-bold text-[10px] flex items-center justify-center shrink-0">6</span>
                        <span>Pelunasan Faktur ERP &amp; Interlock Gate-Out</span>
                    </button>

                    <div class="px-3 pt-2 pb-1 text-[10px] font-bold text-emerald-700 uppercase tracking-wider border-t border-slate-100">
                        B. Proses Fisik di Dalam Gudang CFS (Forklift &amp; Palet SSCC-18):
                    </div>
                    <button type="button" onclick="triggerTwinActivity('cfs_stripping')" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 font-medium flex items-center gap-2 cursor-pointer">
                        <span class="w-5 h-5 rounded-md bg-emerald-100 text-emerald-700 font-bold text-[10px] flex items-center justify-center shrink-0"><i class="fa-solid fa-warehouse"></i></span>
                        <span>1. Dorong Kontainer ke Rampa CFS (FL-03 &amp; CDP-TT-01)</span>
                    </button>
                    <button type="button" onclick="triggerTwinActivity('cfs_pallet')" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 font-medium flex items-center gap-2 cursor-pointer">
                        <span class="w-5 h-5 rounded-md bg-amber-100 text-amber-700 font-bold text-[10px] flex items-center justify-center shrink-0"><i class="fa-solid fa-barcode"></i></span>
                        <span>2. Timbang 3T &amp; Susun Palet SSCC-18 ke Rak (FL-01 &amp; FL-04)</span>
                    </button>
                    <button type="button" onclick="triggerTwinActivity('cfs_complete')" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 font-medium flex items-center gap-2 cursor-pointer">
                        <span class="w-5 h-5 rounded-md bg-teal-100 text-teal-700 font-bold text-[10px] flex items-center justify-center shrink-0"><i class="fa-solid fa-check-double"></i></span>
                        <span>3. Selesai Stripping Rampa &amp; Akrual Tagihan CFS</span>
                    </button>
                    <button type="button" onclick="triggerTwinActivity('cfs_dispatch')" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 font-medium flex items-center gap-2 cursor-pointer">
                        <span class="w-5 h-5 rounded-md bg-sky-100 text-sky-700 font-bold text-[10px] flex items-center justify-center shrink-0"><i class="fa-solid fa-dolly"></i></span>
                        <span>4. Dispatch Palet LCL Keluar Rak Gudang CFS (FL-04)</span>
                    </button>

                    <div class="px-3 pt-2 pb-1 text-[10px] font-bold text-blue-700 uppercase tracking-wider border-t border-slate-100">
                        C. Operasi Lapangan Yard, Reefer, Behandle &amp; Rel KA:
                    </div>
                    <button type="button" onclick="triggerTwinActivity('relocate')" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-medium flex items-center gap-2 cursor-pointer">
                        <span class="w-5 h-5 rounded-md bg-blue-100 text-blue-700 font-bold text-[10px] flex items-center justify-center shrink-0"><i class="fa-solid fa-arrows-up-down-left-right"></i></span>
                        <span>Relokasi Slot Yard (RS-01 &amp; E-RTG +Rp 200k)</span>
                    </button>
                    <button type="button" onclick="triggerTwinActivity('reefer_plug')" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-cyan-50 text-slate-700 hover:text-cyan-700 font-medium flex items-center gap-2 cursor-pointer">
                        <span class="w-5 h-5 rounded-md bg-cyan-100 text-cyan-700 font-bold text-[10px] flex items-center justify-center shrink-0"><i class="fa-solid fa-plug-circle-bolt"></i></span>
                        <span>Plug-In &amp; Telemetri Reefer -18&deg;C (RS-03)</span>
                    </button>
                    <button type="button" onclick="triggerTwinActivity('customs_inspect')" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-rose-50 text-slate-700 hover:text-rose-700 font-medium flex items-center gap-2 cursor-pointer">
                        <span class="w-5 h-5 rounded-md bg-rose-100 text-rose-700 font-bold text-[10px] flex items-center justify-center shrink-0"><i class="fa-solid fa-x-ray"></i></span>
                        <span>Inspeksi Jalur Merah Behandle Blok E (FL-02 &amp; CDP-TT-02)</span>
                    </button>
                    <button type="button" onclick="triggerTwinActivity('rail_load')" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-indigo-50 text-slate-700 hover:text-indigo-700 font-medium flex items-center gap-2 cursor-pointer">
                        <span class="w-5 h-5 rounded-md bg-indigo-100 text-indigo-700 font-bold text-[10px] flex items-center justify-center shrink-0"><i class="fa-solid fa-train"></i></span>
                        <span>Muat Kontainer ke Gerbong KA Pasoso (RTG-01 &amp; CDP-TT-01)</span>
                    </button>
                    <button type="button" onclick="triggerTwinActivity('gate_out')" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 font-medium flex items-center gap-2 cursor-pointer">
                        <span class="w-5 h-5 rounded-md bg-emerald-100 text-emerald-700 font-bold text-[10px] flex items-center justify-center shrink-0"><i class="fa-solid fa-right-from-bracket"></i></span>
                        <span>Rilis Truk Gate-Out (Buka Palang Outbound)</span>
                    </button>

                    <div class="px-3 pt-2 pb-1 text-[10px] font-bold text-rose-700 uppercase tracking-wider border-t border-slate-100">
                        D. Simulasi Anomali &amp; Masalah Lapangan (SOP Tolak / Atasi):
                    </div>
                    <button type="button" onclick="triggerTwinAnomaly('doc_expired')" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-rose-50 text-slate-700 hover:text-rose-700 font-medium flex items-center gap-2 cursor-pointer">
                        <span class="w-5 h-5 rounded-md bg-rose-100 text-rose-700 font-bold text-[10px] flex items-center justify-center shrink-0"><i class="fa-solid fa-file-circle-xmark"></i></span>
                        <span>1. Anomali e-DO Expired &amp; Pajak Belum Lunas (Lock Gate)</span>
                    </button>
                    <button type="button" onclick="triggerTwinAnomaly('container_mismatch')" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-rose-50 text-slate-700 hover:text-rose-700 font-medium flex items-center gap-2 cursor-pointer">
                        <span class="w-5 h-5 rounded-md bg-rose-100 text-rose-700 font-bold text-[10px] flex items-center justify-center shrink-0"><i class="fa-solid fa-boxes-packing"></i></span>
                        <span>2. Anomali Salah Kontainer (OCR Fisik ≠ Manifest B/L)</span>
                    </button>
                    <button type="button" onclick="triggerTwinAnomaly('vgm_overweight')" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-amber-50 text-slate-700 hover:text-amber-800 font-medium flex items-center gap-2 cursor-pointer">
                        <span class="w-5 h-5 rounded-md bg-amber-100 text-amber-800 font-bold text-[10px] flex items-center justify-center shrink-0"><i class="fa-solid fa-weight-hanging"></i></span>
                        <span>3. Anomali Timbangan 80T SOLAS VGM &gt; 5% (Overload 39,2T)</span>
                    </button>
                    <button type="button" onclick="triggerTwinAnomaly('seal_tampered')" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-rose-50 text-slate-700 hover:text-rose-700 font-medium flex items-center gap-2 cursor-pointer">
                        <span class="w-5 h-5 rounded-md bg-rose-100 text-rose-700 font-bold text-[10px] flex items-center justify-center shrink-0"><i class="fa-solid fa-link-slash"></i></span>
                        <span>4. Anomali Segel Putus &amp; Dinding Kontainer Penyok</span>
                    </button>
                    <button type="button" onclick="triggerTwinAnomaly('customs_mismatch')" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-purple-50 text-slate-700 hover:text-purple-700 font-medium flex items-center gap-2 cursor-pointer">
                        <span class="w-5 h-5 rounded-md bg-purple-100 text-purple-700 font-bold text-[10px] flex items-center justify-center shrink-0"><i class="fa-solid fa-building-shield"></i></span>
                        <span>5. Anomali Dokumen BC 2.3 Tidak Cocok CEISA (Jalur Merah)</span>
                    </button>
                    <button type="button" onclick="triggerTwinAnomaly('gate_out_unpaid')" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-slate-100 text-slate-700 hover:text-slate-900 font-medium flex items-center gap-2 cursor-pointer">
                        <span class="w-5 h-5 rounded-md bg-slate-200 text-slate-800 font-bold text-[10px] flex items-center justify-center shrink-0"><i class="fa-solid fa-ban"></i></span>
                        <span>6. Anomali Gate-Out Ditolak (Tagihan Belum Lunas)</span>
                    </button>

                    <div class="border-t border-slate-100 pt-1">
                        <button type="button" onclick="resetSimulatorState()" class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-red-50 text-red-600 font-bold flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-rotate-left text-xs"></i>
                            <span>Reset Posisi 15 Alat &amp; Status Simulasi</span>
                        </button>
                    </div>
                </div>
            </div>

            <button type="button" onclick="resetSimulatorState()" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors cursor-pointer" title="Reset State Benchmark">
                <i class="fa-solid fa-rotate-right text-xs"></i>
            </button>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- 1A-ANOMALY: PANEL INTERLOCK & KEPUTUSAN SOP PENOLAKAN / RESOLUSI    -->
    <!-- =================================================================== -->
    <div id="twinAnomalyBanner" class="hidden bg-gradient-to-r from-rose-50 via-amber-50/70 to-white rounded-2xl border-2 border-rose-400 shadow-md p-3.5 sm:p-4 space-y-3 text-xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-2.5 border-b border-rose-200 pb-2.5">
            <div class="flex items-start gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-rose-600 text-white flex items-center justify-center text-sm shrink-0 animate-pulse">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-rose-600 text-white">INTERLOCK 3D AKTIF — OPERASI DITAHAN</span>
                        <span id="twinAnomCode" class="px-2 py-0.5 rounded text-[10px] font-mono font-extrabold bg-rose-100 text-rose-800 border border-rose-300">DOC_DO_EXPIRED</span>
                        <span id="twinAnomAsset" class="font-mono font-extrabold text-slate-900 bg-white px-2 py-0.5 rounded border border-slate-300">-</span>
                    </div>
                    <h3 id="twinAnomTitle" class="text-sm font-black text-rose-950 mt-1">Masalah Validasi Dokumen / Fisik Terdeteksi</h3>
                    <p id="twinAnomDesc" class="text-[11.5px] text-slate-700 mt-0.5 leading-snug">-</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('twinAnomalyBanner').classList.add('hidden')" class="self-end lg:self-start text-slate-400 hover:text-slate-700 font-bold px-2 py-1 cursor-pointer">
                <i class="fa-solid fa-xmark mr-1"></i>Tutup
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
            <div class="bg-white rounded-xl p-3 border border-rose-200 flex flex-col justify-between gap-2">
                <div>
                    <span class="text-[10px] font-black uppercase text-rose-700"><i class="fa-solid fa-ban mr-1"></i>OPSI 1 — SOP PENOLAKAN &amp; PUTAR BALIK (TURN-BACK)</span>
                    <p id="twinAnomRejectTxt" class="text-[11px] text-slate-600 mt-1">Tolak truk masuk terminal, kunci palang gate, dan terbitkan Berita Acara Penolakan Gate.</p>
                </div>
                <button type="button" onclick="resolveTwinAnomaly('reject')" class="w-full py-2 px-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-truck-arrow-right"></i>
                    <span>Tolak &amp; Putar Balik (Cetak BA Penolakan)</span>
                </button>
            </div>
            <div class="bg-white rounded-xl p-3 border border-emerald-200 flex flex-col justify-between gap-2">
                <div>
                    <span class="text-[10px] font-black uppercase text-emerald-700"><i class="fa-solid fa-screwdriver-wrench mr-1"></i>OPSI 2 — SOP PENYELESAIAN / KOREKSI &amp; OVERRIDE</span>
                    <p id="twinAnomResolveTxt" class="text-[11px] text-slate-600 mt-1">Koreksi dokumen / terbitkan amandemen VGM / pasang segel baru, buka interlock, dan lanjutkan ke Yard.</p>
                </div>
                <button type="button" onclick="resolveTwinAnomaly('resolve')" class="w-full py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Atasi Masalah Sesuai SOP &amp; Lanjutkan 3D</span>
                </button>
            </div>
        </div>
    </div>

    <div id="twinBaSlipBanner" class="hidden bg-white rounded-2xl border-2 border-slate-800 shadow-md p-3.5 flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2.5">
            <span id="twinBaIcon" class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-sm shrink-0"><i class="fa-solid fa-file-signature"></i></span>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span id="twinBaNo" class="font-mono font-black text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">BA-CIDP</span>
                    <strong id="twinBaTitle" class="text-slate-900 font-black">-</strong>
                </div>
                <div id="twinBaDesc" class="text-[11px] text-slate-600 mt-0.5">-</div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="dashboard.php?page=integration" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold">Lihat Antrean &amp; DLQ</a>
            <button type="button" onclick="document.getElementById('twinBaSlipBanner').classList.add('hidden')" class="px-2.5 py-1.5 rounded-lg bg-slate-900 text-white font-bold cursor-pointer">OK</button>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- 1B. PANEL DISPATCH DATA GERAK ALAT REAL-TIME (15 UNIT MHE)          -->
    <!-- =================================================================== -->
    <div class="bg-white rounded-2xl p-3 sm:px-4 shadow-xs border border-slate-200/90 flex flex-col lg:flex-row lg:items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-xl bg-blue-50 border border-blue-200 text-[#0170b9] flex items-center justify-center shrink-0">
                <i class="fa-solid fa-tower-broadcast"></i>
            </span>
            <div>
                <div class="font-extrabold text-slate-800 flex items-center gap-2 flex-wrap">
                    <span>Injeksi Data Gerak 15 Armada &amp; MHE (Yard &amp; Gudang CFS)</span>
                    <span class="px-2 py-0.2 rounded text-[9.5px] font-mono bg-emerald-50 text-emerald-700 border border-emerald-200">POST /api/ingest_event.php</span>
                </div>
                <div class="text-[11px] text-slate-500">Pilih unit (Reach Stacker, E-RTG, Forklift Gudang, atau Shuttle) lalu kirim instruksi koordinat atau aksi fisik.</div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-1.5">
            <!-- Pilih 1 dari 15 Unit Alat Berat / MHE -->
            <div class="flex items-center gap-1.5 bg-slate-50 px-2.5 py-1.5 rounded-xl border border-slate-200">
                <span class="text-[10px] font-bold text-slate-400 uppercase">Unit:</span>
                <select id="dispatchEquipSelect" class="bg-transparent font-extrabold text-slate-800 focus:outline-none cursor-pointer text-xs">
                    <optgroup label="Reach Stacker (4 Unit)">
                        <option value="RS-02">RS-02 (Kalmar Blok B)</option>
                        <option value="RS-01">RS-01 (Kalmar Blok A)</option>
                        <option value="RS-03">RS-03 (Kalmar Reefer)</option>
                        <option value="RS-04">RS-04 (Kalmar Blok C/D)</option>
                    </optgroup>
                    <optgroup label="E-RTG &amp; Rail Gantry (5 Unit)">
                        <option value="RTG-01">RTG-01 (RMGC Rail Siding)</option>
                        <option value="RTG-02">RTG-02 (E-RTG Blok A)</option>
                        <option value="RTG-03">RTG-03 (E-RTG Blok B)</option>
                        <option value="RTG-04">RTG-04 (E-RTG Blok C)</option>
                        <option value="RTG-05">RTG-05 (E-RTG Blok D)</option>
                    </optgroup>
                    <optgroup label="Forklift Gudang CFS &amp; Behandle (4 Unit)">
                        <option value="FL-01">FL-01 (Forklift 3.5T Timbangan CFS)</option>
                        <option value="FL-03">FL-03 (Forklift 3.5T Rampa D1-D5)</option>
                        <option value="FL-04">FL-04 (Reach Truck Rak Aisle A-F)</option>
                        <option value="FL-02">FL-02 (Forklift 5T Behandle Blok E)</option>
                    </optgroup>
                    <optgroup label="Shuttle Terminal Tractor (2 Unit)">
                        <option value="CDP-TT-01">CDP-TT-01 (Shuttle CFS &harr; Rail)</option>
                        <option value="CDP-TT-02">CDP-TT-02 (Shuttle Customs &harr; Blok E)</option>
                    </optgroup>
                </select>
            </div>

            <!-- Tombol Target Zona Cepat (Termasuk Gudang CFS & Behandle) -->
            <button type="button" onclick="dispatchEquipmentMoveZone('CFS_DOCK', 145, 308)" class="px-2.5 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold border border-emerald-200 transition cursor-pointer flex items-center gap-1">
                <i class="fa-solid fa-warehouse text-[10px]"></i><span>Ke Gudang CFS</span>
            </button>
            <button type="button" onclick="dispatchEquipmentMoveZone('CFS_RACK', 92, 345)" class="px-2.5 py-1.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 font-bold border border-teal-200 transition cursor-pointer flex items-center gap-1">
                <i class="fa-solid fa-boxes-Packing text-[10px]"></i><span>Ke Rak Palet</span>
            </button>
            <button type="button" onclick="dispatchEquipmentMoveZone('BLOK_A', 270, 220)" class="px-2.5 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold border border-blue-200 transition cursor-pointer flex items-center gap-1">
                <i class="fa-solid fa-location-dot text-[10px]"></i><span>Blok A</span>
            </button>
            <button type="button" onclick="dispatchEquipmentMoveZone('BLOK_B', 490, 220)" class="px-2.5 py-1.5 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 font-bold border border-sky-200 transition cursor-pointer flex items-center gap-1">
                <i class="fa-solid fa-location-dot text-[10px]"></i><span>Blok B</span>
            </button>
            <button type="button" onclick="dispatchEquipmentMoveZone('BLOK_C', 270, 465)" class="px-2.5 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold border border-purple-200 transition cursor-pointer flex items-center gap-1">
                <i class="fa-solid fa-location-dot text-[10px]"></i><span>Blok C</span>
            </button>
            <button type="button" onclick="dispatchEquipmentMoveZone('REEFER', 755, 225)" class="px-2.5 py-1.5 rounded-xl bg-cyan-50 hover:bg-cyan-100 text-cyan-700 font-bold border border-cyan-200 transition cursor-pointer flex items-center gap-1">
                <i class="fa-solid fa-snowflake text-[10px]"></i><span>Reefer</span>
            </button>
            <button type="button" onclick="dispatchEquipmentMoveZone('BEHANDLE_E', 805, 468)" class="px-2.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold border border-rose-200 transition cursor-pointer flex items-center gap-1">
                <i class="fa-solid fa-x-ray text-[10px]"></i><span>Behandle E</span>
            </button>
            <button type="button" onclick="dispatchEquipmentMoveZone('RAIL_SIDING', 600, 615)" class="px-2.5 py-1.5 rounded-xl bg-orange-50 hover:bg-orange-100 text-orange-700 font-bold border border-orange-200 transition cursor-pointer flex items-center gap-1">
                <i class="fa-solid fa-train text-[10px]"></i><span>Rel KA</span>
            </button>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- 2. MAIN LAYOUT GRID: KANVAS 3D/2D (KIRI 8 COL) & PANEL (KANAN 4 COL)-->
    <!-- =================================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
        
        <!-- KOLOM KIRI (LG:COL-8): KANVAS 3D WEBGL THREE.JS & 2D SVG -->
        <div class="lg:col-span-8 bg-white rounded-2xl p-3 sm:p-4 shadow-xs border border-slate-200/90 relative overflow-hidden flex flex-col justify-between min-h-[640px]">
            
            <!-- Canvas Toolbar: Camera Presets, Coordinates, Lighting -->
            <div class="flex items-center justify-between pb-3 mb-2 border-b border-slate-100 flex-wrap gap-2 text-xs">
                <div class="flex items-center gap-2 font-mono flex-wrap">
                    <span id="viewportModeTitle" class="text-[#0170b9] font-extrabold uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-cubes"></i> 3D WEBGL DIGITAL TWIN (15 MHE + INTERIOR CFS)
                    </span>
                    <span class="text-slate-300">|</span>
                    <span id="canvasGpsHover" class="text-slate-500 text-[11px] font-mono">Koordinat: (X: 350, Y: 300)</span>
                </div>

                <!-- 3D Camera Preset Buttons -->
                <div id="toolbar3DCameras" class="flex items-center gap-1 flex-wrap">
                    <button type="button" onclick="setTwin3DCamera('overview')" class="px-2.5 py-1 rounded-lg bg-[#0170b9] text-white text-[11px] font-bold transition cursor-pointer" id="camBtn-overview" title="Sudut Drone 35 Hektar">Drone 35Ha</button>
                    <button type="button" onclick="setTwin3DCamera('cfs')" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-bold transition cursor-pointer" id="camBtn-cfs" title="Zoom Interior Gudang CFS, Forklift FL-01..04 &amp; Rak Palet SSCC">Gudang CFS</button>
                    <button type="button" onclick="setTwin3DCamera('gate')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold transition cursor-pointer" id="camBtn-gate" title="Gate Inbound &amp; Timbangan 80T">Gate &amp; 80T</button>
                    <button type="button" onclick="setTwin3DCamera('yard')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold transition cursor-pointer" id="camBtn-yard" title="Container Yard Blok A-D &amp; E-RTG">Yard Blok A-D</button>
                    <button type="button" onclick="setTwin3DCamera('reefer')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold transition cursor-pointer" id="camBtn-reefer" title="Reefer Cold Chain &amp; Behandle Blok E">Reefer &amp; Blok E</button>
                    <button type="button" onclick="setTwin3DCamera('rail')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold transition cursor-pointer" id="camBtn-rail" title="Intermodal Rail Siding Pasoso">Rel KA</button>
                    <button type="button" onclick="setTwin3DCamera('top')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold transition cursor-pointer" id="camBtn-top" title="Tampak Atas Orthographic">Top 90&deg;</button>
                    <button type="button" onclick="toggleTwin3DLabels()" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold transition cursor-pointer" id="btnToggle3DLabels" title="Tampilkan / Sembunyikan Label Mengambang"><i class="fa-solid fa-tag"></i></button>
                    <button type="button" onclick="toggleTwin3DNightMode()" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-amber-600 flex items-center justify-center text-xs transition cursor-pointer ml-0.5" id="btnTwinNight" title="Ubah Pencahayaan Siang / Malam">
                        <i class="fa-solid fa-sun" id="iconTwinLight"></i>
                    </button>
                </div>

                <!-- 2D Zoom Controls (Hidden when in 3D mode) -->
                <div id="toolbar2DZoom" class="hidden items-center gap-1">
                    <button type="button" onclick="zoomCanvas(1.2)" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs" title="Perbesar">+</button>
                    <button type="button" onclick="zoomCanvas(0.8)" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs" title="Perkecil">-</button>
                    <button type="button" onclick="resetCanvasView()" class="px-2 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold" title="Reset Tampilan">Fit View</button>
                    <span id="zoomLevelDisplay" class="text-[11px] font-mono text-slate-400 pl-1">100%</span>
                </div>
            </div>

            <!-- =========================================================== -->
            <!-- VIEWPORT 1: 3D WEBGL THREE.JS CANVAS CONTAINER (DEFAULT)    -->
            <!-- =========================================================== -->
            <div id="webglTwinContainer" class="relative w-full h-[540px] bg-[#070e1a] rounded-xl overflow-hidden border border-slate-800 select-none">
                <canvas id="twinWebglCanvas" class="w-full h-full block cursor-grab active:cursor-grabbing"></canvas>

                <!-- Floating 3D HTML Labels Overlay (Projected from 3D world coords) -->
                <div id="twin3DLabelsOverlay" class="absolute inset-0 pointer-events-none overflow-hidden z-10"></div>

                <!-- Floating Financial & Telemetry Toasts Overlay -->
                <div id="twinFloatingToasts" class="absolute bottom-14 right-4 flex flex-col gap-2 pointer-events-none z-30"></div>

                <!-- Top-Left Live WebGL Telemetry Overlay Pill -->
                <div class="absolute top-3 left-3 pointer-events-none flex flex-col gap-1.5 z-20">
                    <div class="bg-slate-900/90 backdrop-blur-md border border-slate-700/80 rounded-xl px-3 py-1.5 text-[11px] text-white font-mono flex items-center gap-2 shadow-lg">
                        <span id="webglPulseDot" class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="font-bold text-cyan-300">WEBGL 3D REAL-TIME</span>
                        <span class="text-slate-500">|</span>
                        <span id="webglActiveActionText" class="text-slate-200">Menunggu Paket Data...</span>
                    </div>
                </div>

                <!-- Bottom-Left Selected 3D Object Inspector Card -->
                <div id="twin3DInspectorCard" class="hidden absolute bottom-3 left-3 bg-slate-900/95 backdrop-blur-md border border-cyan-500/40 rounded-xl p-3 text-xs text-white font-mono shadow-xl z-20 max-w-xs">
                    <div class="flex items-center justify-between pb-1.5 mb-1.5 border-b border-slate-800">
                        <span id="inspTitle" class="font-extrabold text-cyan-400">RS-02 KALMAR DRG450</span>
                        <button type="button" onclick="close3DInspector()" class="text-slate-400 hover:text-white text-xs ml-3 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div id="inspBody" class="space-y-1 text-[10.5px] text-slate-300"></div>
                </div>

                <!-- Bottom-Right Controls Hint -->
                <div class="absolute bottom-2.5 right-3 pointer-events-none bg-slate-900/80 backdrop-blur-sm border border-slate-800 rounded-lg px-2.5 py-1 text-[10px] font-mono text-slate-400 z-10 hidden sm:flex items-center gap-2">
                    <span><i class="fa-solid fa-computer-mouse text-cyan-400 mr-1"></i>Klik Kiri: Putar 3D</span>
                    <span>&bull;</span>
                    <span>Scroll: Zoom</span>
                    <span>&bull;</span>
                    <span>Klik Kanan: Geser</span>
                </div>
            </div>

            <!-- =========================================================== -->
            <!-- VIEWPORT 2: 2D SVG BLUEPRINT CONTAINER (TOGGLEABLE)         -->
            <!-- =========================================================== -->
            <div id="svgCanvasContainer" class="hidden relative w-full h-[540px] bg-slate-900 rounded-xl overflow-hidden border border-slate-800 cursor-grab active:cursor-grabbing select-none" onmousemove="handleCanvasMouseMove(event)">
                
                <svg id="twinSvg" viewBox="0 0 1200 760" class="w-full h-full transition-transform duration-75 ease-out">
                    <defs>
                        <pattern id="gridFine" width="10" height="10" patternUnits="userSpaceOnUse">
                            <path d="M 10 0 L 0 0 0 10" fill="none" stroke="#1e293b" stroke-width="0.3" />
                        </pattern>
                        <pattern id="gridMajor" width="50" height="50" patternUnits="userSpaceOnUse">
                            <path d="M 50 0 L 0 0 0 50" fill="none" stroke="#334155" stroke-width="0.5" />
                        </pattern>
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
                        <path d="M 90 120 L 1110 120 L 1110 660 L 90 660 Z" />
                        <path d="M 600 120 L 600 660" stroke-width="28" />
                        <path d="M 90 380 L 1110 380" stroke-width="24" />
                    </g>
                    <g fill="none" stroke="#38bdf8" stroke-width="1.2" stroke-dasharray="8 6" opacity="0.4">
                        <path d="M 90 120 L 1110 120 L 1110 660 L 90 660 Z" />
                        <path d="M 600 120 L 600 660" />
                        <path d="M 90 380 L 1110 380" />
                    </g>

                    <!-- ZONA 1: GATE & TIMBANGAN 80T -->
                    <g id="zoneGate">
                        <rect x="740" y="55" width="220" height="85" fill="#0f172a" stroke="#0284c7" stroke-width="1.5" rx="6" />
                        <text x="750" y="73" fill="#38bdf8" font-size="11" font-weight="bold">GATE COMPLEX (4 LANES)</text>
                        <rect x="755" y="82" width="85" height="22" fill="#1e293b" stroke="#334155" rx="3" />
                        <text x="760" y="96" fill="#94a3b8" font-size="9" font-family="monospace">LANE 1 IN (OCR)</text>
                        <line id="gateBarrierIn" x1="840" y1="80" x2="840" y2="105" stroke="#ef4444" stroke-width="4" />

                        <rect x="860" y="82" width="85" height="22" fill="#1e293b" stroke="#334155" rx="3" />
                        <text x="865" y="96" fill="#94a3b8" font-size="9" font-family="monospace">LANE 2 OUT</text>
                        <line id="gateBarrierOut" x1="945" y1="80" x2="945" y2="105" stroke="#ef4444" stroke-width="4" />

                        <rect id="weighbridgePlat" x="755" y="112" width="190" height="24" fill="#1e293b" stroke="#f59e0b" stroke-width="1.5" rx="3" />
                        <text x="762" y="128" fill="#fbbf24" font-size="10" font-weight="bold">WEIGHBRIDGE 80 TON (SOLAS VGM)</text>
                        <rect x="955" y="112" width="70" height="24" fill="#020617" stroke="#fbbf24" rx="3" />
                        <text id="svgWeighDisplay" x="990" y="128" fill="#fbbf24" font-size="10" font-family="monospace" font-weight="bold" text-anchor="middle">0.00 T</text>
                    </g>

                    <!-- ZONA 2: CONTAINER YARD PENUMPUKAN -->
                    <g id="blockYardA" transform="translate(180, 160)">
                        <rect width="180" height="180" fill="#0f172a" stroke="#3b82f6" stroke-width="1.5" rx="5" />
                        <text x="10" y="20" fill="#60a5fa" font-size="11" font-weight="bold">BLOK A (LADEN EKSPOR)</text>
                        <text x="10" y="34" fill="#64748b" font-size="9" font-family="monospace">Bay 01-12 • 4-Tier</text>
                        <g id="slotsBlockA" transform="translate(10, 42)"></g>
                    </g>
                    <g id="blockYardB" transform="translate(400, 160)">
                        <rect width="180" height="180" fill="#0f172a" stroke="#0ea5e9" stroke-width="1.5" rx="5" />
                        <text x="10" y="20" fill="#38bdf8" font-size="11" font-weight="bold">BLOK B (LADEN IMPOR)</text>
                        <text x="10" y="34" fill="#64748b" font-size="9" font-family="monospace">Bay 01-12 • 4-Tier</text>
                        <g id="slotsBlockB" transform="translate(10, 42)"></g>
                    </g>
                    <g id="blockYardC" transform="translate(180, 380)">
                        <rect width="180" height="180" fill="#0f172a" stroke="#8b5cf6" stroke-width="1.5" rx="5" />
                        <text x="10" y="20" fill="#a78bfa" font-size="11" font-weight="bold">BLOK C (DOMESTIK)</text>
                        <text x="10" y="34" fill="#64748b" font-size="9" font-family="monospace">Bay 01-12 • 4-Tier</text>
                        <g id="slotsBlockC" transform="translate(10, 42)"></g>
                    </g>
                    <g id="blockYardD" transform="translate(400, 380)">
                        <rect width="180" height="180" fill="#0f172a" stroke="#6366f1" stroke-width="1.5" rx="5" />
                        <text x="10" y="20" fill="#818cf8" font-size="11" font-weight="bold">BLOK D (BUFFER YARD)</text>
                        <text x="10" y="34" fill="#64748b" font-size="9" font-family="monospace">Bay 01-12 • 4-Tier</text>
                        <g id="slotsBlockD" transform="translate(10, 42)"></g>
                    </g>
                    <g id="blockYardReefer" transform="translate(680, 160)">
                        <rect width="180" height="180" fill="#0f172a" stroke="#06b6d4" stroke-width="1.5" rx="5" />
                        <text x="10" y="20" fill="#22d3ee" font-size="11" font-weight="bold">REEFER COLD CHAIN ❄️</text>
                        <text x="10" y="34" fill="#64748b" font-size="9" font-family="monospace">120 Smart Plugs 380V</text>
                        <g id="slotsBlockReefer" transform="translate(10, 42)"></g>
                    </g>
                    <g id="blockYardDG" transform="translate(680, 380)">
                        <rect width="180" height="180" fill="#0f172a" stroke="#ef4444" stroke-width="1.5" rx="5" />
                        <text x="10" y="20" fill="#f87171" font-size="11" font-weight="bold">BLOK E: DG YARD ⚠️</text>
                        <text x="10" y="34" fill="#64748b" font-size="9" font-family="monospace">IMO Class Bunded Zone</text>
                        <g id="slotsBlockDG" transform="translate(10, 42)"></g>
                    </g>

                    <!-- ZONA 3: FASILITAS CFS WAREHOUSE (INTERIOR CUTAWAY 2D), M&R & KANTOR -->
                    <g id="facCFS" transform="translate(52, 195)">
                        <rect width="102" height="225" fill="#06221e" stroke="#10b981" stroke-width="1.8" rx="5" />
                        <rect x="4" y="4" width="94" height="26" fill="#064e3b" rx="3" />
                        <text x="51" y="15" fill="#34d399" font-size="8.5" font-weight="bold" text-anchor="middle">GUDANG CFS 4.000m²</text>
                        <text x="51" y="25" fill="#a7f3d0" font-size="6.5" font-family="monospace" text-anchor="middle">Racks A-F • Scale 3T • D1-D5</text>
                        <!-- 6 Rack Aisles Background (Aisle A-F) -->
                        <?php for ($ri = 0; $ri < 6; $ri++): $ry = 36 + ($ri * 28); ?>
                            <rect x="7" y="<?= $ry ?>" width="58" height="22" fill="#0f172a" stroke="#059669" stroke-width="0.8" stroke-dasharray="2 1" rx="2" />
                            <text x="11" y="<?= $ry + 8 ?>" fill="#6ee7b7" font-size="5.5" font-family="monospace">RAK-<?= chr(65 + $ri) ?></text>
                        <?php endfor; ?>
                        <!-- Dynamic 2D Pallets Layer inside CFS Racks -->
                        <g id="slotsCfsPallets" transform="translate(8, 37)"></g>
                        <!-- 3T Floor Scale inside CFS -->
                        <rect x="68" y="38" width="22" height="32" fill="#1e293b" stroke="#fbbf24" stroke-width="1.2" rx="2" />
                        <text x="79" y="52" fill="#fde047" font-size="6" font-weight="bold" text-anchor="middle">SCALE</text>
                        <text x="79" y="61" fill="#fef08a" font-size="6.5" font-family="monospace" text-anchor="middle">3.0T</text>
                        <!-- 5 Loading Dock Ramps (D1-D5) on East Apron -->
                        <?php for ($di = 0; $di < 5; $di++): $dy = 76 + ($di * 27); ?>
                            <rect x="86" y="<?= $dy ?>" width="18" height="20" fill="#0f172a" stroke="#34d399" stroke-width="1" rx="2" />
                            <text x="95" y="<?= $dy + 13 ?>" fill="#6ee7b7" font-size="6.5" font-weight="bold" font-family="monospace" text-anchor="middle">D<?= $di + 1 ?></text>
                        <?php endfor; ?>
                        <text x="51" y="217" fill="#6ee7b7" font-size="6.5" font-family="monospace" text-anchor="middle">GS1 SSCC-18 PALLET RACKING</text>
                    </g>
                    <g id="facWorkshop" transform="translate(60, 80)">
                        <rect width="90" height="90" fill="#0f172a" stroke="#f97316" stroke-width="1.5" rx="4" />
                        <text x="45" y="25" fill="#fb923c" font-size="10" font-weight="bold" text-anchor="middle">WORKSHOP</text>
                        <text x="45" y="40" fill="#fb923c" font-size="8" text-anchor="middle">M&amp;R KALMAR</text>
                    </g>
                    <g id="facHQ" transform="translate(320, 55)">
                        <rect width="240" height="50" fill="#0f172a" stroke="#8b5cf6" stroke-width="1.5" rx="4" />
                        <text x="120" y="25" fill="#c084fc" font-size="10" font-weight="bold" text-anchor="middle">GEDUNG KANTOR UTAMA &amp; CEISA 4.0</text>
                        <text x="120" y="40" fill="#94a3b8" font-size="8" text-anchor="middle">NOC Datacenter • Bea Cukai • Perbankan</text>
                    </g>

                    <!-- ZONA 4: INTERMODAL RAIL SIDING PASOSO -->
                    <g id="zoneRailSiding" transform="translate(120, 600)">
                        <rect width="960" height="50" fill="#020617" stroke="#eab308" stroke-width="1.5" rx="4" />
                        <line x1="20" y1="18" x2="940" y2="18" stroke="#cbd5e1" stroke-width="2" />
                        <line x1="20" y1="32" x2="940" y2="32" stroke="#cbd5e1" stroke-width="2" />
                        <?php for ($rx = 30; $rx < 930; $rx += 20): ?>
                            <line x1="<?= $rx ?>" y1="12" x2="<?= $rx ?>" y2="38" stroke="#64748b" stroke-width="1.5" />
                        <?php endfor; ?>
                        <text x="480" y="46" fill="#fde047" font-size="10" font-weight="bold" text-anchor="middle">JALUR GANDA KERETA API LOGISTIK PASOSO (RANGKAIAN 60 TEU KE TANJUNG PRIOK)</text>
                        <g id="trainSprite" transform="translate(60, 10)">
                            <rect width="45" height="18" fill="#ea580c" rx="3" stroke="#fff" stroke-width="0.8" />
                            <circle cx="8" cy="9" r="3" fill="#fde047" />
                            <text x="24" y="13" fill="#fff" font-size="7" font-weight="bold">CC206</text>
                            <rect x="52" y="3" width="55" height="12" fill="#1e293b" stroke="#64748b" rx="2" />
                            <rect x="54" y="4" width="51" height="10" fill="#2563eb" rx="1" />
                            <text x="79" y="12" fill="#fff" font-size="6" text-anchor="middle">TEMU4910</text>
                            <rect x="112" y="3" width="55" height="12" fill="#1e293b" stroke="#64748b" rx="2" />
                            <rect x="114" y="4" width="51" height="10" fill="#059669" rx="1" />
                            <text x="139" y="12" fill="#fff" font-size="6" text-anchor="middle">MSKU2201</text>
                        </g>
                    </g>

                    <!-- DYNAMIC SPRITES LAYER (2D SVG - ALL 15 CANONICAL MHE UNITS + ACTIVE TRUCK) -->
                    <g id="spriteTruck" transform="translate(800, 112)">
                        <rect width="52" height="18" fill="#0284c7" stroke="#fff" stroke-width="1" rx="3" />
                        <rect x="36" y="2" width="14" height="14" fill="#0f172a" rx="2" />
                        <rect x="42" y="4" width="6" height="10" fill="#7dd3fc" rx="1" />
                        <text id="spriteTruckPlate" x="20" y="12" fill="#fff" font-size="7" font-weight="bold" text-anchor="middle">B 9812 UIX</text>
                    </g>
                    <!-- 4 Reach Stackers (RS-01..04) -->
                    <g id="spriteRS01" transform="translate(150, 200)">
                        <circle cx="0" cy="0" r="15" fill="#f97316" stroke="#fff" stroke-width="2" />
                        <text x="0" y="3.5" fill="#fff" font-size="8.5" font-weight="bold" text-anchor="middle">RS1</text>
                        <line x1="0" y1="0" x2="21" y2="0" stroke="#f97316" stroke-width="3.5" stroke-linecap="round" />
                    </g>
                    <g id="spriteRS02" transform="translate(350, 300)">
                        <circle cx="0" cy="0" r="16" fill="#ea580c" stroke="#fff" stroke-width="2.5" />
                        <text x="0" y="3.5" fill="#fff" font-size="8.5" font-weight="extrabold" text-anchor="middle">RS2</text>
                        <line id="rs02Boom" x1="0" y1="0" x2="25" y2="0" stroke="#ea580c" stroke-width="4" stroke-linecap="round" />
                        <g id="rs02Container" transform="translate(19, -11)">
                            <rect width="34" height="22" fill="#2563eb" stroke="#fbbf24" stroke-width="1.5" rx="2" />
                            <text x="17" y="14" fill="#fff" font-size="6.5" font-weight="bold" text-anchor="middle">MSKU</text>
                        </g>
                    </g>
                    <g id="spriteRS03" transform="translate(720, 240)">
                        <circle cx="0" cy="0" r="15" fill="#0284c7" stroke="#fff" stroke-width="2" />
                        <text x="0" y="3.5" fill="#fff" font-size="8.5" font-weight="bold" text-anchor="middle">RS3</text>
                        <line x1="0" y1="0" x2="21" y2="0" stroke="#0284c7" stroke-width="3.5" stroke-linecap="round" />
                    </g>
                    <g id="spriteRS04" transform="translate(270, 545)">
                        <circle cx="0" cy="0" r="15" fill="#d97706" stroke="#fff" stroke-width="2" />
                        <text x="0" y="3.5" fill="#fff" font-size="8.5" font-weight="bold" text-anchor="middle">RS4</text>
                        <line x1="0" y1="0" x2="21" y2="0" stroke="#d97706" stroke-width="3.5" stroke-linecap="round" />
                    </g>
                    <!-- 5 RTG / RMGC Cranes (RTG-01..05) -->
                    <g id="spriteRTG01" transform="translate(600, 600)">
                        <rect x="-20" y="-14" width="40" height="28" fill="#0f172a" fill-opacity="0.7" stroke="#eab308" stroke-width="2.5" rx="2" />
                        <text x="0" y="3.5" fill="#fde047" font-size="7.5" font-weight="bold" text-anchor="middle">RTG-01</text>
                    </g>
                    <g id="spriteRTG02" transform="translate(270, 250)">
                        <rect x="-18" y="-13" width="36" height="26" fill="#0f172a" fill-opacity="0.7" stroke="#38bdf8" stroke-width="2" rx="2" />
                        <text x="0" y="3" fill="#7dd3fc" font-size="7" font-weight="bold" text-anchor="middle">RTG-02</text>
                    </g>
                    <g id="spriteRTG03" transform="translate(490, 250)">
                        <rect x="-18" y="-13" width="36" height="26" fill="#0f172a" fill-opacity="0.7" stroke="#38bdf8" stroke-width="2" rx="2" />
                        <text x="0" y="3" fill="#7dd3fc" font-size="7" font-weight="bold" text-anchor="middle">RTG-03</text>
                    </g>
                    <g id="spriteRTG04" transform="translate(270, 470)">
                        <rect x="-18" y="-13" width="36" height="26" fill="#0f172a" fill-opacity="0.7" stroke="#38bdf8" stroke-width="2" rx="2" />
                        <text x="0" y="3" fill="#7dd3fc" font-size="7" font-weight="bold" text-anchor="middle">RTG-04</text>
                    </g>
                    <g id="spriteRTG05" transform="translate(490, 470)">
                        <rect x="-18" y="-13" width="36" height="26" fill="#0f172a" fill-opacity="0.7" stroke="#38bdf8" stroke-width="2" rx="2" />
                        <text x="0" y="3" fill="#7dd3fc" font-size="7" font-weight="bold" text-anchor="middle">RTG-05</text>
                    </g>
                    <!-- 4 Electric Forklifts (FL-01..04) inside CFS Warehouse & Behandle -->
                    <g id="spriteFL01" transform="translate(140, 240)">
                        <rect x="-10" y="-8" width="20" height="16" fill="#10b981" stroke="#fff" stroke-width="1.5" rx="3" />
                        <text x="0" y="3" fill="#fff" font-size="6.5" font-weight="bold" text-anchor="middle">FL1</text>
                    </g>
                    <g id="spriteFL02" transform="translate(835, 470)">
                        <rect x="-10" y="-8" width="20" height="16" fill="#10b981" stroke="#fff" stroke-width="1.5" rx="3" />
                        <text x="0" y="3" fill="#fff" font-size="6.5" font-weight="bold" text-anchor="middle">FL2</text>
                    </g>
                    <g id="spriteFL03" transform="translate(148, 310)">
                        <rect x="-10" y="-8" width="20" height="16" fill="#059669" stroke="#fff" stroke-width="1.5" rx="3" />
                        <text x="0" y="3" fill="#fff" font-size="6.5" font-weight="bold" text-anchor="middle">FL3</text>
                    </g>
                    <g id="spriteFL04" transform="translate(90, 350)">
                        <rect x="-10" y="-8" width="20" height="16" fill="#047857" stroke="#fff" stroke-width="1.5" rx="3" />
                        <text x="0" y="3" fill="#fff" font-size="6.5" font-weight="bold" text-anchor="middle">FL4</text>
                    </g>
                    <!-- 2 Terminal Shuttle Tractors (CDP-TT-01..02) -->
                    <g id="spriteTT01" transform="translate(360, 585)">
                        <rect x="-14" y="-7" width="28" height="14" fill="#8b5cf6" stroke="#fff" stroke-width="1.5" rx="2.5" />
                        <text x="0" y="3" fill="#fff" font-size="6.5" font-weight="bold" text-anchor="middle">TT01</text>
                    </g>
                    <g id="spriteTT02" transform="translate(635, 470)">
                        <rect x="-14" y="-7" width="28" height="14" fill="#7c3aed" stroke="#fff" stroke-width="1.5" rx="2.5" />
                        <text x="0" y="3" fill="#fff" font-size="6.5" font-weight="bold" text-anchor="middle">TT02</text>
                    </g>
                </svg>

                <!-- Tooltip floating over 2D Canvas -->
                <div id="canvasTooltip" class="hidden absolute pointer-events-none bg-slate-900/90 text-white text-[11px] px-3 py-2 rounded-xl shadow-xl border border-slate-700 backdrop-blur-md z-30 font-mono">
                    Tooltip info
                </div>
            </div>

            <!-- Canvas Bottom Legend -->
            <div class="mt-3 pt-2.5 border-t border-slate-100 flex flex-wrap items-center justify-between text-xs text-slate-500 gap-2">
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-blue-600 inline-block"></span> Dry 40HC</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-cyan-500 inline-block"></span> Reefer (-18&deg;C)</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-red-500 inline-block"></span> Dangerous Goods</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-emerald-500 inline-block"></span> Gudang CFS &amp; Palet SSCC-18</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-orange-500 inline-block"></span> 15 Unit MHE (RS / RTG / FL / TT)</span>
                </div>
                <div class="text-[11px] font-mono text-slate-400">
                    Engine: <span class="text-[#0170b9] font-bold">Three.js r128 WebGL + SSE Real-Time (Zero-Loop)</span>
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
                        <span>Aliran Data &amp; Telemetri</span>
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

                    <!-- Equipment CAN-Bus Live Telemetry Card -->
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex flex-col gap-1.5">
                        <div class="flex items-center justify-between text-[11px] font-mono">
                            <span class="font-bold text-slate-700">TELEMETRI CAN-BUS ALAT BERAT</span>
                            <span id="rs02TelemetryBadge" class="px-2 py-0.2 rounded text-[9.5px] font-bold bg-orange-100 text-orange-700 border border-orange-200">RS-02 STANDBY</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] font-mono">
                            <span class="text-slate-500">Posisi GPS Spasial:</span>
                            <span id="rs02GpsCoordText" class="font-bold text-slate-800">(X: 350, Y: 300)</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] font-mono">
                            <span class="text-slate-500">Spreader / Mast:</span>
                            <span id="rs02SpreaderText" class="font-bold text-slate-700">UNLOCKED (Kosong)</span>
                        </div>
                    </div>

                    <!-- Gudang CFS & LCL Pallet Racking Live Telemetry Card -->
                    <div class="p-3 rounded-xl bg-emerald-50/50 border border-emerald-200 flex flex-col gap-1.5">
                        <div class="flex items-center justify-between text-[11px] font-mono">
                            <span class="font-bold text-emerald-900 flex items-center gap-1.5">
                                <i class="fa-solid fa-warehouse text-emerald-600"></i>
                                GUDANG CFS &amp; PALET SSCC-18
                            </span>
                            <span id="cfsActiveJobsBadge" class="px-2 py-0.2 rounded text-[9.5px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                <?= (int)($twin_initial['physical']['cfs_summary']['active_jobs'] ?? 0) ?> JOB AKTIF
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] font-mono">
                            <span class="text-slate-600">Palet di Rak (Aisle A-F):</span>
                            <span id="cfsPalletsInRackText" class="font-bold text-emerald-700">
                                <?= (int)($twin_initial['physical']['cfs_summary']['pallets_in_rack'] ?? 0) ?> Palet (<?= (int)($twin_initial['physical']['cfs_summary']['pallets_dispatched'] ?? 0) ?> Out)
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] font-mono">
                            <span class="text-slate-600">Timbangan Lantai 3T / Vol:</span>
                            <span id="cfsTotalWeightCbmText" class="font-bold text-slate-800">
                                <?= number_format((float)($twin_initial['physical']['cfs_summary']['total_weight_kg'] ?? 0), 0, ',', '.') ?> kg • <?= number_format((float)($twin_initial['physical']['cfs_summary']['total_cbm'] ?? 0), 1, ',', '.') ?> CBM
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[10.5px] font-mono">
                            <span class="text-slate-600">Ramp &amp; Forklift Aktif:</span>
                            <span id="cfsActiveRampsText" class="font-bold text-emerald-800">FL-01, FL-03, FL-04 • Ramp D1-D5</span>
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
                            <div id="reeferTempDisplay" class="text-base font-extrabold font-mono text-cyan-600">-18.4 &deg;C</div>
                            <div class="text-[9.5px] text-emerald-600 font-bold">120 Plug Mandiri</div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- PANEL 2: ALIRAN FINANSIAL & BILLING ENGINE LIVE -->
            <div class="bg-white text-slate-800 rounded-2xl p-4 sm:p-5 shadow-2xs border border-slate-200 relative overflow-hidden">
                <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
                    <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice-dollar text-orange-600"></i>
                        <span>Aliran Finansial &amp; Billing</span>
                    </h3>
                    <span class="text-[10px] font-mono text-orange-700 bg-orange-50 px-2 py-0.5 rounded border border-orange-200 font-bold">PILAR 3</span>
                </div>

                <!-- Grand Total Accrued Revenue Today -->
                <div class="mb-4 p-3 rounded-xl bg-emerald-50/60 border border-emerald-200">
                    <div class="text-[10.5px] font-bold text-emerald-800 uppercase tracking-wider">Total Akrual Pendapatan Berjalan:</div>
                    <div id="financialTotalRevenue" class="text-2xl sm:text-3xl font-black text-emerald-700 font-mono tracking-tight my-0.5">
                        Rp <?= number_format($twin_initial['financial']['total_revenue_accrued_idr'], 0, ',', '.') ?>
                    </div>
                    <div class="text-[10px] text-emerald-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-bolt text-orange-500"></i>
                        <span>Terhitung otomatis per pergerakan sensor fisik</span>
                    </div>
                </div>

                <!-- 5 Tariff Breakdown Chips (Including CFS LCL Fee) -->
                <div class="grid grid-cols-2 gap-2 text-xs mb-4">
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="text-[10px] text-slate-500 font-semibold">Jasa Timbang 80T:</div>
                        <div id="finWeighFee" class="text-sm font-bold font-mono text-slate-900 mt-0.5">
                            Rp <?= number_format($twin_initial['financial']['weighbridge_fee_idr'], 0, ',', '.') ?>
                        </div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="text-[10px] text-slate-500 font-semibold">Lift-Off / Lift-On:</div>
                        <div id="finLoLoFee" class="text-sm font-bold font-mono text-slate-900 mt-0.5">
                            Rp <?= number_format($twin_initial['financial']['liftoff_fee_idr'], 0, ',', '.') ?>
                        </div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="text-[10px] text-slate-500 font-semibold">Dwell Time Yard:</div>
                        <div id="finStorageFee" class="text-sm font-bold font-mono text-slate-900 mt-0.5">
                            Rp <?= number_format($twin_initial['financial']['storage_dwell_fee_idr'], 0, ',', '.') ?>
                        </div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="text-[10px] text-slate-500 font-semibold">Daya Reefer 380V:</div>
                        <div id="finReeferFee" class="text-sm font-bold font-mono text-slate-900 mt-0.5">
                            Rp <?= number_format($twin_initial['financial']['reefer_power_fee_idr'], 0, ',', '.') ?>
                        </div>
                    </div>
                    <div class="col-span-2 p-2.5 rounded-xl bg-emerald-50/60 border border-emerald-200 flex items-center justify-between">
                        <div>
                            <div class="text-[10px] text-emerald-800 font-bold uppercase">Jasa Gudang CFS (Stripping/Stuffing &amp; Rak LCL):</div>
                            <div class="text-[9.5px] text-slate-500">Rp 450.000/kontainer + Rp 85.000/palet SSCC-18</div>
                        </div>
                        <div id="finCfsFee" class="text-sm font-extrabold font-mono text-emerald-700">
                            Rp <?= number_format((int)($twin_initial['financial']['cfs_fee_idr'] ?? 0), 0, ',', '.') ?>
                        </div>
                    </div>
                </div>

                <!-- Settlement Status Progress -->
                <div class="pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-1.5">
                        <span>Status Pelunasan Kasir / Bank:</span>
                        <span id="settlementPctDisplay" class="font-bold text-emerald-700 font-mono"><?= $twin_initial['financial']['settlement_ratio_pct'] ?>% Lunas</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden border border-slate-200">
                        <div id="settlementBar" class="h-full bg-emerald-500 transition-all duration-500" style="width: <?= $twin_initial['financial']['settlement_ratio_pct'] ?>%;"></div>
                    </div>
                    <div class="flex items-center justify-between text-[10px] font-mono text-slate-500 mt-2">
                        <span>Lunas: <strong id="paidAmountDisplay" class="text-emerald-700">Rp <?= number_format($twin_initial['financial']['paid_amount_idr'], 0, ',', '.') ?></strong></span>
                        <span>Piutang: <strong id="pendingAmountDisplay" class="text-amber-700">Rp <?= number_format($twin_initial['financial']['pending_amount_idr'], 0, ',', '.') ?></strong></span>
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
                <span>Hardware Event Bus &amp; Activity Log (Real-Time Ingestion Feed)</span>
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
<!-- JAVASCRIPT: THREE.JS WEBGL 3D ENGINE + SSE REAL-TIME EVENT KINEMATICS   -->
<!-- ======================================================================= -->
<script>
/**
 * 3D WebGL (Three.js) & 2D SVG Real-Time Digital Twin Engine
 * Prinsip Utama: EVENT-DRIVEN KINEMATICS
 * Alat berat (RS-01, RS-02, RS-03, RTG-01, Truk, Gate Barrier) diam saat standby,
 * dan HANYA bergerak ketika ada perubahan data/event masuk dari SSE / API!
 */
let currentTwinState = <?= json_encode($twin_initial, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
let previousEventId = null;
let previousRevenue = <?= (float)($twin_initial['financial']['total_revenue_accrued_idr'] ?? 0) ?>;
let activeRenderMode = '3d'; // '3d' | '2d'

let sseConnection = null;
let reconnectTimer = null;
let pollFallbackTimer = null;

// 2D Canvas Pan & Zoom State
let canvasScale = 1.0;
let canvasTranslateX = 0;
let canvasTranslateY = 0;

// 2D & 3D Target Coordinates (in 1200x760 SVG coordinate space, mapped to 3D world space)
// mencakup 15 Unit MHE Kanonik Perusahaan + 1 Truk Kontainer Aktif
const sprites = {
    truck: { currentX: 800, currentY: 82,  targetX: 800, targetY: 82,  heading: Math.PI, isMoving: false, visible: true },
    rs01:  { currentX: 150, currentY: 200, targetX: 150, targetY: 200, heading: 0, isMoving: false, carried: null },
    rs02:  { currentX: 350, currentY: 300, targetX: 350, targetY: 300, heading: 0, isMoving: false, carried: null },
    rs03:  { currentX: 720, currentY: 240, targetX: 720, targetY: 240, heading: 0, isMoving: false, carried: null },
    rs04:  { currentX: 270, currentY: 545, targetX: 270, targetY: 545, heading: 0, isMoving: false, carried: null },
    rtg01: { currentX: 600, currentY: 600, targetX: 600, targetY: 600, heading: 0, isMoving: false, carried: null },
    rtg02: { currentX: 270, currentY: 250, targetX: 270, targetY: 250, heading: 0, isMoving: false, carried: null },
    rtg03: { currentX: 490, currentY: 250, targetX: 490, targetY: 250, heading: 0, isMoving: false, carried: null },
    rtg04: { currentX: 270, currentY: 470, targetX: 270, targetY: 470, heading: 0, isMoving: false, carried: null },
    rtg05: { currentX: 490, currentY: 470, targetX: 490, targetY: 470, heading: 0, isMoving: false, carried: null },
    fl01:  { currentX: 140, currentY: 240, targetX: 140, targetY: 240, heading: 0, isMoving: false, carried: null },
    fl02:  { currentX: 835, currentY: 470, targetX: 835, targetY: 470, heading: 0, isMoving: false, carried: null },
    fl03:  { currentX: 148, currentY: 310, targetX: 148, targetY: 310, heading: 0, isMoving: false, carried: null },
    fl04:  { currentX: 90,  currentY: 350, targetX: 90,  targetY: 350, heading: 0, isMoving: false, carried: null },
    tt01:  { currentX: 360, currentY: 585, targetX: 360, targetY: 585, heading: 0, isMoving: false, carried: null },
    tt02:  { currentX: 635, currentY: 470, targetX: 635, targetY: 470, heading: 0, isMoving: false, carried: null }
};

const EQUIP_ID_TO_KEY = {
    'RS-01': 'rs01', 'RS-02': 'rs02', 'RS-03': 'rs03', 'RS-04': 'rs04',
    'RTG-01': 'rtg01', 'RTG-02': 'rtg02', 'RTG-03': 'rtg03', 'RTG-04': 'rtg04', 'RTG-05': 'rtg05',
    'FL-01': 'fl01', 'FL-02': 'fl02', 'FL-03': 'fl03', 'FL-04': 'fl04',
    'CDP-TT-01': 'tt01', 'CDP-TT-02': 'tt02'
};
const KEY_TO_EQUIP_ID = Object.fromEntries(Object.entries(EQUIP_ID_TO_KEY).map(([k, v]) => [v, k]));

// =========================================================================
// KONVERSI KOORDINAT TUNGGAL: SVG (0..1200, 0..760) -> THREE.JS 3D WORLD
// Ground 3D berukuran 340m (X: -170..+170) x 215m (Z: -107.5..+107.5)
// =========================================================================
function svgTo3D(svgX, svgY) {
    return {
        x: (svgX - 600) * (340 / 1200),
        z: (svgY - 380) * (215 / 760)
    };
}

// =========================================================================
// THREE.JS 3D WEBGL SCENE VARIABLES
// =========================================================================
let twinScene, twinCamera, twinRenderer, twinControls;
let twinAmbientLight, twinDirLight, twinHemiLight;
let isTwinNightMode = false;
let showTwin3DLabels = true;
let twinYardSlotsGroup = null;
let twinCfsPalletsGroup = null;
let twinCfsDockBoxesGroup = null;
let gateBarrierInPivot = null;
let gateBarrierOutPivot = null;
let ocrLaserBeamMesh = null;
let ocrLaserOpacity = 0;
let lastActiveEquipId = 'RS-02';

// 3D Dynamic Equipment Objects (15 MHE + 1 Truck)
const twin3DUnits = {
    truck: null,
    rs01: null, rs02: null, rs03: null, rs04: null,
    rtg01: null, rtg02: null, rtg03: null, rtg04: null, rtg05: null,
    fl01: null, fl02: null, fl03: null, fl04: null,
    tt01: null, tt02: null
};

// 3D HTML Floating Labels Map
const twin3DLabels = {};

// Raycaster for 3D Object Inspection
let twinRaycaster, twinMouse;
let twinInteractableMeshes = [];
let twinDynamicSlotMeshes = [];

// =========================================================================
// 1. INISIALISASI ENGINE THREE.JS WEBGL
// =========================================================================
function initTwinWebGL() {
    const container = document.getElementById('webglTwinContainer');
    const canvas = document.getElementById('twinWebglCanvas');
    if (!container || !canvas || typeof THREE === 'undefined') return;

    const width = container.clientWidth || 800;
    const height = container.clientHeight || 540;

    twinScene = new THREE.Scene();
    twinScene.background = new THREE.Color(0x070e1a);
    twinScene.fog = new THREE.FogExp2(0x070e1a, 0.0018);

    twinCamera = new THREE.PerspectiveCamera(42, width / height, 1, 1500);
    twinCamera.position.set(0, 145, 215);

    twinRenderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, powerPreference: 'high-performance' });
    twinRenderer.setSize(width, height);
    twinRenderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    twinRenderer.shadowMap.enabled = true;
    twinRenderer.shadowMap.type = THREE.PCFSoftShadowMap;

    twinControls = new THREE.OrbitControls(twinCamera, twinRenderer.domElement);
    twinControls.enableDamping = true;
    twinControls.dampingFactor = 0.06;
    twinControls.maxPolarAngle = Math.PI / 2 - 0.03;
    twinControls.minDistance = 20;
    twinControls.maxDistance = 420;
    twinControls.target.set(0, 0, -5);

    // Lighting
    twinHemiLight = new THREE.HemisphereLight(0xdbeafe, 0x0f172a, 0.75);
    twinHemiLight.position.set(0, 120, 0);
    twinScene.add(twinHemiLight);

    twinDirLight = new THREE.DirectionalLight(0xfffbeb, 1.15);
    twinDirLight.position.set(90, 150, 80);
    twinDirLight.castShadow = true;
    twinDirLight.shadow.mapSize.width = 2048;
    twinDirLight.shadow.mapSize.height = 2048;
    twinDirLight.shadow.camera.near = 10;
    twinDirLight.shadow.camera.far = 450;
    const d = 180;
    twinDirLight.shadow.camera.left = -d;
    twinDirLight.shadow.camera.right = d;
    twinDirLight.shadow.camera.top = d;
    twinDirLight.shadow.camera.bottom = -d;
    twinScene.add(twinDirLight);

    // Build Complete 35 Ha Terminal in 3D
    buildTwin3DTerminalEnvironment();
    buildTwin3DDynamicEquipment();
    createTwin3DFloatingLabels();

    // Setup Raycaster for clicking 3D objects
    twinRaycaster = new THREE.Raycaster();
    twinMouse = new THREE.Vector2();
    canvas.addEventListener('click', onTwinCanvasClick);
    canvas.addEventListener('mousemove', onTwinCanvasHover);
    window.addEventListener('resize', onTwinWindowResize);
}

function onTwinWindowResize() {
    const container = document.getElementById('webglTwinContainer');
    if (!container || !twinRenderer || !twinCamera) return;
    const width = container.clientWidth;
    const height = container.clientHeight;
    if (width === 0 || height === 0) return;
    twinCamera.aspect = width / height;
    twinCamera.updateProjectionMatrix();
    twinRenderer.setSize(width, height);
}

// =========================================================================
// 2. PEMBANGUNAN ARSITEKTUR TERMINAL 35 HA DI THREE.JS
// =========================================================================
function buildTwin3DTerminalEnvironment() {
    // A. Lantai Dasar Beton Kawasan (340m x 215m)
    const groundGeo = new THREE.PlaneGeometry(340, 215);
    const groundMat = new THREE.MeshStandardMaterial({ color: 0x0b1324, roughness: 0.9, metalness: 0.05 });
    const ground = new THREE.Mesh(groundGeo, groundMat);
    ground.rotation.x = -Math.PI / 2;
    ground.receiveShadow = true;
    twinScene.add(ground);

    // Engineering Grid
    const grid = new THREE.GridHelper(340, 68, 0x1e3a8a, 0x172554);
    grid.position.y = 0.05;
    twinScene.add(grid);

    // Perimeter Fence Border (317m x 192m)
    const fenceMat = new THREE.LineBasicMaterial({ color: 0x38bdf8, transparent: true, opacity: 0.4 });
    const fencePts = [
        new THREE.Vector3(-158, 0.3, -96),
        new THREE.Vector3(158, 0.3, -96),
        new THREE.Vector3(158, 0.3, 96),
        new THREE.Vector3(-158, 0.3, 96),
        new THREE.Vector3(-158, 0.3, -96)
    ];
    const fenceLine = new THREE.Line(new THREE.BufferGeometry().setFromPoints(fencePts), fenceMat);
    twinScene.add(fenceLine);

    // B. Jalan Sirkulasi Internal (Haul Roads)
    const roadMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.8 });
    function addRoadStrip(svgX1, svgY1, svgX2, svgY2, widthM) {
        const p1 = svgTo3D(svgX1, svgY1);
        const p2 = svgTo3D(svgX2, svgY2);
        const dx = p2.x - p1.x;
        const dz = p2.z - p1.z;
        const len = Math.hypot(dx, dz);
        const mesh = new THREE.Mesh(new THREE.BoxGeometry(len + widthM, 0.12, widthM), roadMat);
        mesh.position.set((p1.x + p2.x) / 2, 0.06, (p1.z + p2.z) / 2);
        mesh.rotation.y = -Math.atan2(dz, dx);
        mesh.receiveShadow = true;
        twinScene.add(mesh);
    }
    // Loop Roads & Cross Roads matching 2D SVG
    addRoadStrip(90, 120, 1110, 120, 9);
    addRoadStrip(90, 660, 1110, 660, 9);
    addRoadStrip(90, 120, 90, 660, 9);
    addRoadStrip(1110, 120, 1110, 660, 9);
    addRoadStrip(600, 120, 600, 660, 8);
    addRoadStrip(90, 380, 1110, 380, 7.5);

    // C. ZONA 1: GATE COMPLEX & TIMBANGAN 80 TON (SVG: 740..960, 55..135)
    const gatePos = svgTo3D(850, 90);
    const gateGroup = new THREE.Group();
    gateGroup.position.set(gatePos.x, 0, gatePos.z);

    // Kanopi Baja Gatehouse
    const canopyMat = new THREE.MeshStandardMaterial({ color: 0x0284c7, roughness: 0.35, metalness: 0.3 });
    const canopy = new THREE.Mesh(new THREE.BoxGeometry(58, 1.2, 16), canopyMat);
    canopy.position.set(0, 8.5, 0);
    canopy.castShadow = true;
    gateGroup.add(canopy);

    // Tiang-tiang Kanopi Gate
    const pillarMat = new THREE.MeshStandardMaterial({ color: 0x94a3b8, metalness: 0.5, roughness: 0.4 });
    [-25, -8, 8, 25].forEach(px => {
        const pillar = new THREE.Mesh(new THREE.CylinderGeometry(0.45, 0.45, 8.5, 12), pillarMat);
        pillar.position.set(px, 4.25, 0);
        pillar.castShadow = true;
        gateGroup.add(pillar);
    });

    // Portal Kamera OCR & RFID (Berwarna Cyan/Amber)
    const ocrFrameMat = new THREE.MeshStandardMaterial({ color: 0xf59e0b, roughness: 0.3 });
    const ocrArch = new THREE.Mesh(new THREE.BoxGeometry(16, 6.8, 0.8), ocrFrameMat);
    ocrArch.position.set(-14, 3.4, -3);
    gateGroup.add(ocrArch);
    const ocrCutout = new THREE.Mesh(new THREE.BoxGeometry(13.5, 5.8, 1.0), new THREE.MeshStandardMaterial({ color: 0x070e1a }));
    ocrCutout.position.set(-14, 2.9, -3);
    gateGroup.add(ocrCutout);

    // Sinar Laser Pemindai OCR (Aktif saat truk masuk Gate)
    const laserGeo = new THREE.PlaneGeometry(13, 5.8);
    const laserMat = new THREE.MeshBasicMaterial({ color: 0x06b6d4, transparent: true, opacity: 0.0, side: THREE.DoubleSide });
    ocrLaserBeamMesh = new THREE.Mesh(laserGeo, laserMat);
    ocrLaserBeamMesh.position.set(-14, 3.0, -3);
    gateGroup.add(ocrLaserBeamMesh);

    // Palang Barrier Gate Inbound (Berputar 90 derajat saat OPEN)
    gateBarrierInPivot = new THREE.Group();
    gateBarrierInPivot.position.set(-7.5, 1.6, 2.5);
    const barrierArm = new THREE.Mesh(
        new THREE.BoxGeometry(11, 0.28, 0.28),
        new THREE.MeshStandardMaterial({ color: 0xef4444, emissive: 0x7f1d1d, emissiveIntensity: 0.4 })
    );
    barrierArm.position.set(-5.5, 0, 0);
    gateBarrierInPivot.add(barrierArm);
    gateGroup.add(gateBarrierInPivot);

    // Palang Barrier Gate Outbound (Berputar 90 derajat saat GATE_OUT)
    gateBarrierOutPivot = new THREE.Group();
    gateBarrierOutPivot.position.set(10.5, 1.6, 2.5);
    const barrierArmOut = new THREE.Mesh(
        new THREE.BoxGeometry(11, 0.28, 0.28),
        new THREE.MeshStandardMaterial({ color: 0x22c55e, emissive: 0x14532d, emissiveIntensity: 0.4 })
    );
    barrierArmOut.position.set(5.5, 0, 0);
    gateBarrierOutPivot.add(barrierArmOut);
    gateGroup.add(gateBarrierOutPivot);

    // Jembatan Timbang 80 Ton (SOLAS VGM Pitless Weighbridge)
    const wbDeck = new THREE.Mesh(
        new THREE.BoxGeometry(24, 0.35, 6.5),
        new THREE.MeshStandardMaterial({ color: 0x334155, metalness: 0.6, roughness: 0.4 })
    );
    wbDeck.position.set(-12, 0.18, 8.5);
    wbDeck.receiveShadow = true;
    gateGroup.add(wbDeck);

    // Rel Pembatas Kuning Jembatan Timbang
    const curbMat = new THREE.MeshStandardMaterial({ color: 0xfbbf24 });
    const curb1 = new THREE.Mesh(new THREE.BoxGeometry(24, 0.5, 0.35), curbMat);
    curb1.position.set(-12, 0.35, 5.1);
    const curb2 = curb1.clone();
    curb2.position.set(-12, 0.35, 11.9);
    gateGroup.add(curb1, curb2);

    twinScene.add(gateGroup);

    // D. ZONA 2: 6 BLOK CONTAINER YARD PENUMPUKAN
    const blockDefs = [
        { id: 'A', svgX: 270, svgY: 250, color: 0x3b82f6, label: 'BLOK A (EKSPOR)' },
        { id: 'B', svgX: 490, svgY: 250, color: 0x0ea5e9, label: 'BLOK B (IMPOR)' },
        { id: 'C', svgX: 270, svgY: 470, color: 0x8b5cf6, label: 'BLOK C (DOMESTIK)' },
        { id: 'D', svgX: 490, svgY: 470, color: 0x6366f1, label: 'BLOK D (BUFFER)' },
        { id: 'REEFER', svgX: 770, svgY: 250, color: 0x06b6d4, label: 'REEFER HUB 380V' },
        { id: 'E', svgX: 770, svgY: 470, color: 0xef4444, label: 'BLOK E (DG & BEHANDLE)' }
    ];

    blockDefs.forEach(b => {
        const pos = svgTo3D(b.svgX, b.svgY);
        const pad = new THREE.Mesh(
            new THREE.BoxGeometry(50, 0.2, 50),
            new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.85 })
        );
        pad.position.set(pos.x, 0.1, pos.z);
        pad.receiveShadow = true;
        twinScene.add(pad);

        // Garis Outline Warna Blok
        const edges = new THREE.EdgesGeometry(new THREE.BoxGeometry(50.2, 0.25, 50.2));
        const line = new THREE.LineSegments(edges, new THREE.LineBasicMaterial({ color: b.color }));
        line.position.set(pos.x, 0.15, pos.z);
        twinScene.add(line);

        // Jika Reefer Hub, tambahkan rak Gantry Listrik 380V
        if (b.id === 'REEFER') {
            const rackMat = new THREE.MeshStandardMaterial({ color: 0x06b6d4, metalness: 0.5 });
            for (let rz = -16; rz <= 16; rz += 16) {
                const rack = new THREE.Mesh(new THREE.BoxGeometry(44, 3.2, 0.6), rackMat);
                rack.position.set(pos.x, 1.6, pos.z + rz - 4.5);
                twinScene.add(rack);
            }
        }
    });

    // Grup Dinamis untuk Boks Kontainer di Yard
    twinYardSlotsGroup = new THREE.Group();
    twinScene.add(twinYardSlotsGroup);

    // E. ZONA 3: GEDUNG KANTOR UTAMA, GUDANG CFS CUTAWAY 3D, & WORKSHOP KALMAR
    // 1. Gedung Kantor Utama & CEISA 4.0 (Utara: SVG 440, 80)
    const hqPos = svgTo3D(440, 80);
    const hqGroup = new THREE.Group();
    hqGroup.position.set(hqPos.x, 0, hqPos.z);
    const hqBody = new THREE.Mesh(
        new THREE.BoxGeometry(64, 11, 14),
        new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.4, metalness: 0.3 })
    );
    hqBody.position.y = 5.5;
    hqBody.castShadow = true;
    const hqGlass = new THREE.Mesh(
        new THREE.BoxGeometry(62, 7.5, 14.3),
        new THREE.MeshStandardMaterial({ color: 0x38bdf8, roughness: 0.15, metalness: 0.8, transparent: true, opacity: 0.65 })
    );
    hqGlass.position.y = 6.0;
    const hqAccent = new THREE.Mesh(
        new THREE.BoxGeometry(65, 0.8, 15),
        new THREE.MeshStandardMaterial({ color: 0x8b5cf6 })
    );
    hqAccent.position.y = 11.2;
    hqGroup.add(hqBody, hqGlass, hqAccent);
    twinScene.add(hqGroup);

    // 2. GUDANG CFS KONSOLIDASI LCL 4.000 m² — CUTAWAY 3D INTERIOR (Barat: SVG 103, 308)
    // Mencakup: Lantai Epoxy, Atap & Dinding Kaca Transparan Cutaway, 6 Rak Palet (Aisle A-F),
    // Timbangan Lantai 3 Ton, dan 5 Loading Dock Ramps (D1-D5)
    const cfsPos = svgTo3D(103, 308);
    const cfsGroup = new THREE.Group();
    cfsGroup.position.set(cfsPos.x, 0, cfsPos.z);

    // Lantai Epoxy Hijau Tua Gudang CFS (30m x 64m)
    const cfsFloor = new THREE.Mesh(
        new THREE.BoxGeometry(30, 0.28, 64),
        new THREE.MeshStandardMaterial({ color: 0x062c24, roughness: 0.65, metalness: 0.15 })
    );
    cfsFloor.position.y = 0.14;
    cfsFloor.receiveShadow = true;
    cfsGroup.add(cfsFloor);

    const cfsFloorEdge = new THREE.LineSegments(
        new THREE.EdgesGeometry(new THREE.BoxGeometry(30.2, 0.32, 64.2)),
        new THREE.LineBasicMaterial({ color: 0x10b981 })
    );
    cfsFloorEdge.position.y = 0.16;
    cfsGroup.add(cfsFloorEdge);

    // Rangka Baja Kolom & Truss Atap Gudang CFS (Cutaway agar interior terlihat jelas)
    const cfsSteelMat = new THREE.MeshStandardMaterial({ color: 0x10b981, metalness: 0.5, roughness: 0.35 });
    for (let cz = -30; cz <= 30; cz += 12) {
        [-14.5, 14.5].forEach(cx => {
            const col = new THREE.Mesh(new THREE.BoxGeometry(0.45, 9.2, 0.45), cfsSteelMat);
            col.position.set(cx, 4.6, cz);
            cfsGroup.add(col);
        });
        const roofBeam = new THREE.Mesh(new THREE.BoxGeometry(30, 0.38, 0.45), cfsSteelMat);
        roofBeam.position.set(0, 9.2, cz);
        cfsGroup.add(roofBeam);
    }

    // Atap Skylight & Dinding Kaca Cutaway (Transparan 18% agar seluruh Rak & Forklift terlihat)
    const cfsCutawayWall = new THREE.Mesh(
        new THREE.BoxGeometry(29.6, 8.8, 63.6),
        new THREE.MeshStandardMaterial({ color: 0x0f172a, transparent: true, opacity: 0.18, roughness: 0.2, depthWrite: false })
    );
    cfsCutawayWall.position.y = 4.6;
    cfsGroup.add(cfsCutawayWall);

    const cfsWireShell = new THREE.LineSegments(
        new THREE.EdgesGeometry(new THREE.BoxGeometry(30.0, 9.2, 64.0)),
        new THREE.LineBasicMaterial({ color: 0x34d399 })
    );
    cfsWireShell.position.y = 4.6;
    cfsGroup.add(cfsWireShell);

    // Timbangan Lantai 3.0 Ton (Floor Scale) di Dalam Gudang CFS (Dekat Ramp D1-D2)
    const scalePad = new THREE.Mesh(
        new THREE.BoxGeometry(4.5, 0.18, 5.2),
        new THREE.MeshStandardMaterial({ color: 0xfbbf24, metalness: 0.5, roughness: 0.3 })
    );
    scalePad.position.set(6.5, 0.32, -21);
    cfsGroup.add(scalePad);

    // 5 Pintu Loading Dock Ramps (D1..D5) di Sisi Timur Gudang CFS (Menghadap Yard)
    const rampMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, metalness: 0.4, roughness: 0.6 });
    const bumperMat = new THREE.MeshStandardMaterial({ color: 0xfacc15 });
    for (let di = 0; di < 5; di++) {
        const dz = -18 + di * 9.5;
        const dockPad = new THREE.Mesh(new THREE.BoxGeometry(4.2, 0.45, 5.5), rampMat);
        dockPad.position.set(16.2, 0.22, dz);
        const dockPortal = new THREE.Mesh(new THREE.BoxGeometry(0.6, 4.2, 4.2), bumperMat);
        dockPortal.position.set(14.8, 2.2, dz);
        cfsGroup.add(dockPad, dockPortal);
    }

    // 6 Baris Rak Penyimpanan Palet Bertingkat (Multi-Tier Racks: AISLE-A s/d AISLE-F)
    const rackPostMat = new THREE.MeshStandardMaterial({ color: 0x0284c7, metalness: 0.5, roughness: 0.4 });
    const rackShelfMat = new THREE.MeshStandardMaterial({ color: 0xf97316, roughness: 0.5 });
    for (let ai = 0; ai < 6; ai++) {
        const az = -22 + ai * 8.8;
        const rackGroup = new THREE.Group();
        rackGroup.position.set(-5.2, 0, az);

        [-6.5, -2.2, 2.2, 6.5].forEach(rx => {
            [-1.1, 1.1].forEach(rz => {
                const post = new THREE.Mesh(new THREE.BoxGeometry(0.22, 5.4, 0.22), rackPostMat);
                post.position.set(rx, 2.7, rz);
                rackGroup.add(post);
            });
        });

        [0.45, 2.15, 3.85].forEach(ry => {
            const shelf = new THREE.Mesh(new THREE.BoxGeometry(13.4, 0.14, 2.3), rackShelfMat);
            shelf.position.set(0, ry, 0);
            rackGroup.add(shelf);
        });

        cfsGroup.add(rackGroup);
    }

    // Grup Dinamis untuk Palet SSCC-18 di Rak & Kontainer di Loading Dock Ramp D1-D5
    twinCfsPalletsGroup = new THREE.Group();
    cfsGroup.add(twinCfsPalletsGroup);

    twinCfsDockBoxesGroup = new THREE.Group();
    cfsGroup.add(twinCfsDockBoxesGroup);

    twinScene.add(cfsGroup);

    // 3. Workshop M&R Kalmar (Barat Laut: SVG 105, 125)
    const wsPos = svgTo3D(105, 125);
    const wsMesh = new THREE.Mesh(
        new THREE.BoxGeometry(25, 8, 25),
        new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.5 })
    );
    wsMesh.position.set(wsPos.x, 4, wsPos.z);
    wsMesh.castShadow = true;
    const wsRoof = new THREE.Mesh(
        new THREE.BoxGeometry(26, 0.6, 26),
        new THREE.MeshStandardMaterial({ color: 0xf97316 })
    );
    wsRoof.position.set(wsPos.x, 8.2, wsPos.z);
    twinScene.add(wsMesh, wsRoof);

    // F. ZONA 4: INTERMODAL RAIL SIDING PASOSO & RANGKAIAN KERETA API (Selatan: SVG 600, 625)
    const railPos = svgTo3D(600, 625);
    const railGroup = new THREE.Group();
    railGroup.position.set(railPos.x, 0, railPos.z);

    // Ballast Batu Kricak Rel
    const ballast = new THREE.Mesh(
        new THREE.BoxGeometry(272, 0.35, 14),
        new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.95 })
    );
    ballast.position.y = 0.18;
    railGroup.add(ballast);

    // Batang Rel Baja Ganda
    const railSteelMat = new THREE.MeshStandardMaterial({ color: 0x94a3b8, metalness: 0.8, roughness: 0.2 });
    [-3.2, -1.2, 1.2, 3.2].forEach(rz => {
        const railBar = new THREE.Mesh(new THREE.BoxGeometry(268, 0.22, 0.18), railSteelMat);
        railBar.position.set(0, 0.42, rz);
        railGroup.add(railBar);
    });

    // Lokomotif CC206 + Gerbong Datar Kontainer
    const locoGroup = new THREE.Group();
    locoGroup.position.set(-95, 0.5, -2.2);
    const locoBody = new THREE.Mesh(
        new THREE.BoxGeometry(16, 3.8, 3.1),
        new THREE.MeshStandardMaterial({ color: 0xea580c, roughness: 0.35 })
    );
    locoBody.position.y = 2.1;
    locoBody.castShadow = true;
    const locoStripe = new THREE.Mesh(
        new THREE.BoxGeometry(16.1, 0.7, 3.15),
        new THREE.MeshStandardMaterial({ color: 0xffffff })
    );
    locoStripe.position.y = 1.8;
    const locoCab = new THREE.Mesh(
        new THREE.BoxGeometry(4.2, 1.6, 2.9),
        new THREE.MeshStandardMaterial({ color: 0x0f172a })
    );
    locoCab.position.set(-4.5, 3.1, 0);
    locoGroup.add(locoBody, locoStripe, locoCab);

    // 4 Gerbong Datar PPCW dengan Kontainer di belakang Lokomotif
    const wagonColors = [0x2563eb, 0x059669, 0x0284c7, 0xd97706];
    for (let w = 0; w < 4; w++) {
        const wx = 15 + w * 15.5;
        const flatcar = new THREE.Mesh(
            new THREE.BoxGeometry(14, 0.6, 2.9),
            new THREE.MeshStandardMaterial({ color: 0x334155 })
        );
        flatcar.position.set(wx, 0.8, 0);
        const wBox = createTwin3DContainerBox(12.2, 2.6, 2.5, wagonColors[w], false);
        wBox.position.set(wx, 2.4, 0);
        locoGroup.add(flatcar, wBox);
    }
    railGroup.add(locoGroup);
    twinScene.add(railGroup);
}

// Helper Pembuat Boks Kontainer 3D Bertekstur Corrugated
function createTwin3DContainerBox(length, height, width, colorHex, isCleared = false, isReefer = false) {
    const group = new THREE.Group();
    const mat = new THREE.MeshStandardMaterial({ color: colorHex, roughness: 0.45, metalness: 0.2 });
    const mainBox = new THREE.Mesh(new THREE.BoxGeometry(length, height, width), mat);
    mainBox.castShadow = true;
    mainBox.receiveShadow = true;
    group.add(mainBox);

    const edges = new THREE.EdgesGeometry(new THREE.BoxGeometry(length + 0.04, height + 0.04, width + 0.04));
    const edgeColor = isCleared ? 0x10b981 : 0x0f172a;
    const wire = new THREE.LineSegments(edges, new THREE.LineBasicMaterial({ color: edgeColor }));
    group.add(wire);

    if (isReefer) {
        const cooler = new THREE.Mesh(
            new THREE.BoxGeometry(0.4, height * 0.82, width * 0.85),
            new THREE.MeshStandardMaterial({ color: 0xf8fafc, roughness: 0.3 })
        );
        cooler.position.set(length / 2 + 0.15, 0, 0);
        group.add(cooler);
    }

    if (isCleared) {
        const badge = new THREE.Mesh(
            new THREE.BoxGeometry(length * 0.9, 0.14, width * 0.9),
            new THREE.MeshBasicMaterial({ color: 0x10b981 })
        );
        badge.position.y = height / 2 + 0.08;
        group.add(badge);
    }

    return group;
}

// Helper Pembuat Palet Kargo LCL (GS1 SSCC-18) 3D
function createTwin3DPalletMesh(cargoColorHex = 0xd97706) {
    const group = new THREE.Group();
    const base = new THREE.Mesh(
        new THREE.BoxGeometry(1.6, 0.22, 1.5),
        new THREE.MeshStandardMaterial({ color: 0x92400e, roughness: 0.8 })
    );
    base.position.y = 0.11;
    const carton = new THREE.Mesh(
        new THREE.BoxGeometry(1.45, 1.15, 1.35),
        new THREE.MeshStandardMaterial({ color: cargoColorHex, roughness: 0.45 })
    );
    carton.position.y = 0.8;
    carton.castShadow = true;
    const label = new THREE.Mesh(
        new THREE.BoxGeometry(0.65, 0.38, 1.38),
        new THREE.MeshBasicMaterial({ color: 0xffffff })
    );
    label.position.set(0, 0.85, 0);
    group.add(base, carton, label);
    return { group, carton };
}

// =========================================================================
// 3. PEMBANGUNAN 15 UNIT ALAT BERAT DINAMIS 3D + TRUK KONTAINER
// =========================================================================
function buildTwin3DDynamicEquipment() {
    // A. 3D CONTAINER TRUCK (B 9812 UIX)
    twin3DUnits.truck = createTwin3DTruck();
    const tPos = svgTo3D(sprites.truck.currentX, sprites.truck.currentY);
    twin3DUnits.truck.group.position.set(tPos.x, 0, tPos.z);
    twinScene.add(twin3DUnits.truck.group);

    // B. 4 REACH STACKERS (RS-01..04)
    const rsColors = { rs01: 0xf97316, rs02: 0xea580c, rs03: 0x0284c7, rs04: 0xd97706 };
    ['rs01', 'rs02', 'rs03', 'rs04'].forEach(key => {
        const eqId = KEY_TO_EQUIP_ID[key];
        twin3DUnits[key] = createTwin3DReachStacker(eqId, rsColors[key]);
        const p = svgTo3D(sprites[key].currentX, sprites[key].currentY);
        twin3DUnits[key].group.position.set(p.x, 0, p.z);
        twinScene.add(twin3DUnits[key].group);
    });

    // C. 5 RTG / RMGC GANTRY CRANES (RTG-01..05)
    const rtgColors = { rtg01: 0xeab308, rtg02: 0x0284c7, rtg03: 0x0284c7, rtg04: 0x0ea5e9, rtg05: 0x0ea5e9 };
    ['rtg01', 'rtg02', 'rtg03', 'rtg04', 'rtg05'].forEach(key => {
        const eqId = KEY_TO_EQUIP_ID[key];
        twin3DUnits[key] = createTwin3DRTGCrane(eqId, rtgColors[key]);
        const p = svgTo3D(sprites[key].currentX, sprites[key].currentY);
        twin3DUnits[key].group.position.set(p.x, 0, p.z);
        twinScene.add(twin3DUnits[key].group);
    });

    // D. 4 ELECTRIC FORKLIFTS (FL-01..04) DI GUDANG CFS & BEHANDLE
    const flColors = { fl01: 0x10b981, fl02: 0x10b981, fl03: 0x059669, fl04: 0x34d399 };
    ['fl01', 'fl02', 'fl03', 'fl04'].forEach(key => {
        const eqId = KEY_TO_EQUIP_ID[key];
        twin3DUnits[key] = createTwin3DForklift(eqId, flColors[key]);
        const p = svgTo3D(sprites[key].currentX, sprites[key].currentY);
        twin3DUnits[key].group.position.set(p.x, 0, p.z);
        twinScene.add(twin3DUnits[key].group);
    });

    // E. 2 TERMINAL SHUTTLE TRACTORS (CDP-TT-01..02)
    ['tt01', 'tt02'].forEach(key => {
        const eqId = KEY_TO_EQUIP_ID[key];
        twin3DUnits[key] = createTwin3DShuttleTractor(eqId);
        const p = svgTo3D(sprites[key].currentX, sprites[key].currentY);
        twin3DUnits[key].group.position.set(p.x, 0, p.z);
        twinScene.add(twin3DUnits[key].group);
    });
}

// Model 3D Truk Kontainer (Kepala Truk + Sasis Trailer + Boks Kontainer)
function createTwin3DTruck() {
    const group = new THREE.Group();
    group.userData = { type: 'TRUCK', id: 'B 9812 UIX' };

    const chassis = new THREE.Mesh(
        new THREE.BoxGeometry(14.5, 0.5, 2.6),
        new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.6 })
    );
    chassis.position.set(0, 1.1, 0);
    chassis.castShadow = true;
    group.add(chassis);

    const cab = new THREE.Mesh(
        new THREE.BoxGeometry(3.4, 3.0, 2.6),
        new THREE.MeshStandardMaterial({ color: 0x0284c7, roughness: 0.3 })
    );
    cab.position.set(6.2, 2.3, 0);
    cab.castShadow = true;
    group.add(cab);

    const glass = new THREE.Mesh(
        new THREE.BoxGeometry(0.2, 1.2, 2.3),
        new THREE.MeshStandardMaterial({ color: 0x7dd3fc, roughness: 0.1, metalness: 0.8 })
    );
    glass.position.set(7.85, 2.8, 0);
    group.add(glass);

    const wheels = [];
    const wheelGeo = new THREE.CylinderGeometry(0.55, 0.55, 0.45, 16);
    wheelGeo.rotateX(Math.PI / 2);
    const wheelMat = new THREE.MeshStandardMaterial({ color: 0x090d16, roughness: 0.85 });
    [-5.5, -3.5, 2.5, 6.5].forEach(wx => {
        [-1.3, 1.3].forEach(wz => {
            const w = new THREE.Mesh(wheelGeo, wheelMat);
            w.position.set(wx, 0.55, wz);
            group.add(w);
            wheels.push(w);
        });
    });

    const cargoContainer = createTwin3DContainerBox(12.2, 2.6, 2.5, 0x2563eb, false, false);
    cargoContainer.position.set(-1.0, 2.65, 0);
    group.add(cargoContainer);

    twinInteractableMeshes.push(cab, chassis);
    cab.userData = { parentUnit: 'TRUCK' };
    chassis.userData = { parentUnit: 'TRUCK' };

    return { group, wheels, cargoContainer };
}

// Model 3D Kalmar DRG450 Reach Stacker (Dengan Boom Teleskopik & Spreader Aktif)
function createTwin3DReachStacker(unitId, bodyColorHex) {
    const group = new THREE.Group();
    group.userData = { type: 'REACH_STACKER', id: unitId };

    const body = new THREE.Mesh(
        new THREE.BoxGeometry(7.2, 2.1, 3.6),
        new THREE.MeshStandardMaterial({ color: bodyColorHex, roughness: 0.35, metalness: 0.25 })
    );
    body.position.set(0, 1.8, 0);
    body.castShadow = true;
    group.add(body);

    const counterweight = new THREE.Mesh(
        new THREE.BoxGeometry(2.2, 2.4, 3.5),
        new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.7 })
    );
    counterweight.position.set(-2.8, 2.0, 0);
    group.add(counterweight);

    const cab = new THREE.Mesh(
        new THREE.BoxGeometry(2.6, 2.2, 2.2),
        new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.2, metalness: 0.6 })
    );
    cab.position.set(0.6, 3.6, 0);
    group.add(cab);

    const beacon = new THREE.Mesh(
        new THREE.CylinderGeometry(0.25, 0.25, 0.4, 12),
        new THREE.MeshBasicMaterial({ color: 0xfbbf24 })
    );
    beacon.position.set(0.6, 4.9, 0);
    group.add(beacon);

    const wheels = [];
    const tireGeo = new THREE.CylinderGeometry(0.95, 0.95, 0.75, 20);
    tireGeo.rotateX(Math.PI / 2);
    const tireMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.85 });
    [-2.2, 2.4].forEach(wx => {
        [-1.8, 1.8].forEach(wz => {
            const tire = new THREE.Mesh(tireGeo, tireMat);
            tire.position.set(wx, 0.95, wz);
            tire.castShadow = true;
            group.add(tire);
            wheels.push(tire);
        });
    });

    const boomPivot = new THREE.Group();
    boomPivot.position.set(-2.0, 3.2, 0);
    boomPivot.rotation.z = 0.32;

    const outerBoom = new THREE.Mesh(
        new THREE.BoxGeometry(9.5, 0.85, 1.1),
        new THREE.MeshStandardMaterial({ color: 0x334155, metalness: 0.5, roughness: 0.4 })
    );
    outerBoom.position.set(4.2, 0, 0);
    outerBoom.castShadow = true;
    boomPivot.add(outerBoom);

    const spreaderPivot = new THREE.Group();
    spreaderPivot.position.set(8.6, -0.4, 0);
    spreaderPivot.rotation.z = -0.32;

    const spreaderBeam = new THREE.Mesh(
        new THREE.BoxGeometry(2.6, 0.45, 12.0),
        new THREE.MeshStandardMaterial({ color: 0xfacc15, roughness: 0.4 })
    );
    spreaderPivot.add(spreaderBeam);

    const carriedBox = createTwin3DContainerBox(2.5, 2.6, 12.2, 0x2563eb, true, false);
    carriedBox.position.set(0, -1.55, 0);
    carriedBox.visible = false;
    spreaderPivot.add(carriedBox);

    boomPivot.add(spreaderPivot);
    group.add(boomPivot);

    body.userData = { parentUnit: unitId };
    twinInteractableMeshes.push(body);

    return { group, wheels, boomPivot, spreaderPivot, carriedBox, beacon };
}

// Model 3D Kalmar E-RTG / RMGC Gantry Crane (RTG-01..05)
function createTwin3DRTGCrane(unitId, frameColorHex = 0xeab308) {
    const group = new THREE.Group();
    group.userData = { type: 'RTG_CRANE', id: unitId };

    const mainMat = new THREE.MeshStandardMaterial({ color: frameColorHex, roughness: 0.4, metalness: 0.3 });
    const darkMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.7 });

    [-7, 7].forEach(px => {
        [-8, 8].forEach(pz => {
            const leg = new THREE.Mesh(new THREE.BoxGeometry(0.85, 14, 0.85), mainMat);
            leg.position.set(px, 7, pz);
            leg.castShadow = true;
            group.add(leg);
        });
    });

    [-7, 7].forEach(px => {
        const beam = new THREE.Mesh(new THREE.BoxGeometry(1.1, 1.3, 18), mainMat);
        beam.position.set(px, 14.2, 0);
        beam.castShadow = true;
        group.add(beam);
    });
    const topBridge = new THREE.Mesh(new THREE.BoxGeometry(15.5, 1.0, 3.8), mainMat);
    topBridge.position.set(0, 15.0, 0);
    group.add(topBridge);

    const trolley = new THREE.Mesh(new THREE.BoxGeometry(3.5, 0.8, 3.5), darkMat);
    trolley.position.set(0, 13.8, 0);
    group.add(trolley);

    const carriedBox = createTwin3DContainerBox(12.2, 2.55, 2.5, 0x0284c7, true, false);
    carriedBox.position.set(0, -3.2, 0);
    carriedBox.visible = false;
    trolley.add(carriedBox);

    topBridge.userData = { parentUnit: unitId };
    twinInteractableMeshes.push(topBridge);

    return { group, trolley, carriedBox };
}

// Model 3D Electric Counterbalanced Forklift 3.5T-5.0T (FL-01..04) untuk Gudang CFS & Behandle
function createTwin3DForklift(unitId, bodyColorHex = 0x10b981) {
    const group = new THREE.Group();
    group.userData = { type: 'FORKLIFT', id: unitId };

    const body = new THREE.Mesh(
        new THREE.BoxGeometry(2.6, 1.3, 1.6),
        new THREE.MeshStandardMaterial({ color: bodyColorHex, roughness: 0.35, metalness: 0.25 })
    );
    body.position.set(0, 0.95, 0);
    body.castShadow = true;
    group.add(body);

    const cabGuard = new THREE.Mesh(
        new THREE.BoxGeometry(1.6, 1.3, 1.4),
        new THREE.MeshStandardMaterial({ color: 0x0f172a, transparent: true, opacity: 0.65 })
    );
    cabGuard.position.set(0.1, 2.1, 0);
    group.add(cabGuard);

    const mastMat = new THREE.MeshStandardMaterial({ color: 0x334155, metalness: 0.6 });
    [-0.5, 0.5].forEach(mz => {
        const rail = new THREE.Mesh(new THREE.BoxGeometry(0.18, 3.2, 0.18), mastMat);
        rail.position.set(1.4, 1.7, mz);
        group.add(rail);
    });

    const forkCarriage = new THREE.Group();
    forkCarriage.position.set(2.1, 0.6, 0);
    [-0.4, 0.4].forEach(fz => {
        const tine = new THREE.Mesh(
            new THREE.BoxGeometry(1.4, 0.1, 0.18),
            new THREE.MeshStandardMaterial({ color: 0xfacc15, metalness: 0.5 })
        );
        tine.position.set(0, 0, fz);
        forkCarriage.add(tine);
    });

    const carriedPalletObj = createTwin3DPalletMesh(0xf59e0b);
    carriedPalletObj.group.position.set(0.1, 0.08, 0);
    carriedPalletObj.group.visible = false;
    forkCarriage.add(carriedPalletObj.group);
    group.add(forkCarriage);

    const wheels = [];
    const wheelGeo = new THREE.CylinderGeometry(0.38, 0.38, 0.3, 14);
    wheelGeo.rotateX(Math.PI / 2);
    const wheelMat = new THREE.MeshStandardMaterial({ color: 0x090d16, roughness: 0.85 });
    [-0.85, 0.85].forEach(wx => {
        [-0.8, 0.8].forEach(wz => {
            const w = new THREE.Mesh(wheelGeo, wheelMat);
            w.position.set(wx, 0.38, wz);
            group.add(w);
            wheels.push(w);
        });
    });

    body.userData = { parentUnit: unitId };
    twinInteractableMeshes.push(body);

    return { group, wheels, forkCarriage, carriedBox: carriedPalletObj.group };
}

// Model 3D Kalmar Ottawa T2 Terminal Shuttle Tractor (CDP-TT-01..02)
function createTwin3DShuttleTractor(unitId) {
    const group = new THREE.Group();
    group.userData = { type: 'TERMINAL_TRACTOR', id: unitId };

    const chassis = new THREE.Mesh(
        new THREE.BoxGeometry(12.8, 0.45, 2.5),
        new THREE.MeshStandardMaterial({ color: 0x334155, roughness: 0.6 })
    );
    chassis.position.set(0, 1.0, 0);
    group.add(chassis);

    const cab = new THREE.Mesh(
        new THREE.BoxGeometry(2.8, 2.7, 2.4),
        new THREE.MeshStandardMaterial({ color: 0x8b5cf6, roughness: 0.35 })
    );
    cab.position.set(5.2, 2.1, 0);
    cab.castShadow = true;
    group.add(cab);

    const wheels = [];
    const wheelGeo = new THREE.CylinderGeometry(0.5, 0.5, 0.4, 14);
    wheelGeo.rotateX(Math.PI / 2);
    const wheelMat = new THREE.MeshStandardMaterial({ color: 0x090d16, roughness: 0.85 });
    [-4.8, 1.8, 5.4].forEach(wx => {
        [-1.25, 1.25].forEach(wz => {
            const w = new THREE.Mesh(wheelGeo, wheelMat);
            w.position.set(wx, 0.5, wz);
            group.add(w);
            wheels.push(w);
        });
    });

    const carriedBox = createTwin3DContainerBox(12.0, 2.5, 2.45, 0x7c3aed, true, false);
    carriedBox.position.set(-0.6, 2.48, 0);
    carriedBox.visible = false;
    group.add(carriedBox);

    cab.userData = { parentUnit: unitId };
    chassis.userData = { parentUnit: unitId };
    twinInteractableMeshes.push(cab, chassis);

    return { group, wheels, carriedBox };
}

// =========================================================================
// 4. FLOATING 3D HTML LABELS DI ATAS 15 ALAT BERAT & TRUK
// =========================================================================
function createTwin3DFloatingLabels() {
    const overlay = document.getElementById('twin3DLabelsOverlay');
    if (!overlay) return;
    overlay.innerHTML = '';

    const labelConfigs = [
        { key: 'rs02',  title: 'RS-02',     sub: 'Kalmar Active',   color: 'bg-orange-600 border-orange-400' },
        { key: 'rs01',  title: 'RS-01',     sub: 'Blok A Standby',  color: 'bg-slate-800/90 border-orange-500/60' },
        { key: 'rs03',  title: 'RS-03',     sub: 'Reefer Hub',      color: 'bg-slate-800/90 border-cyan-500/60' },
        { key: 'rs04',  title: 'RS-04',     sub: 'Blok C/D',        color: 'bg-slate-800/90 border-amber-500/60' },
        { key: 'rtg01', title: 'RTG-01',    sub: 'Rail Gantry',     color: 'bg-slate-800/90 border-yellow-500/60' },
        { key: 'rtg02', title: 'RTG-02',    sub: 'Gantry Blok A',   color: 'bg-slate-800/90 border-sky-500/60' },
        { key: 'rtg03', title: 'RTG-03',    sub: 'Gantry Blok B',   color: 'bg-slate-800/90 border-sky-500/60' },
        { key: 'rtg04', title: 'RTG-04',    sub: 'Gantry Blok C',   color: 'bg-slate-800/90 border-indigo-500/60' },
        { key: 'rtg05', title: 'RTG-05',    sub: 'Gantry Blok D',   color: 'bg-slate-800/90 border-indigo-500/60' },
        { key: 'fl01',  title: 'FL-01',     sub: 'CFS Ramp D1',     color: 'bg-emerald-700/95 border-emerald-400' },
        { key: 'fl02',  title: 'FL-02',     sub: 'Behandle Blok E', color: 'bg-emerald-800/90 border-emerald-500/60' },
        { key: 'fl03',  title: 'FL-03',     sub: 'CFS Stuffing D3', color: 'bg-emerald-700/95 border-emerald-400' },
        { key: 'fl04',  title: 'FL-04',     sub: 'CFS Racking A-F', color: 'bg-emerald-700/95 border-emerald-400' },
        { key: 'tt01',  title: 'CDP-TT-01', sub: 'Shuttle KA',      color: 'bg-purple-800/90 border-purple-400/60' },
        { key: 'tt02',  title: 'CDP-TT-02', sub: 'Shuttle CFS/E',   color: 'bg-purple-800/90 border-purple-400/60' },
        { key: 'truck', title: 'B 9812 UIX',sub: 'Truk Kontainer',  color: 'bg-sky-700 border-sky-400' }
    ];

    labelConfigs.forEach(cfg => {
        const el = document.createElement('div');
        el.className = `twin-3d-label px-1.5 py-0.5 rounded-md border text-white font-mono text-[8.5px] shadow-md flex items-center gap-1 backdrop-blur-sm ${cfg.color}`;
        el.innerHTML = `
            <span class="w-1.5 h-1.5 rounded-full bg-white" id="lblDot-${cfg.key}"></span>
            <strong id="lblTitle-${cfg.key}">${cfg.title}</strong>
            <span class="opacity-85 text-[8px]" id="lblSub-${cfg.key}">${cfg.sub}</span>
        `;
        el.onclick = () => inspectTwinUnit(cfg.key);
        overlay.appendChild(el);
        twin3DLabels[cfg.key] = el;
    });
}

function toggleTwin3DLabels() {
    showTwin3DLabels = !showTwin3DLabels;
    const overlay = document.getElementById('twin3DLabelsOverlay');
    const btn = document.getElementById('btnToggle3DLabels');
    if (overlay) overlay.style.display = showTwin3DLabels ? 'block' : 'none';
    if (btn) {
        btn.className = showTwin3DLabels
            ? 'px-2.5 py-1 rounded-lg bg-slate-800 text-white text-[11px] font-bold transition cursor-pointer flex items-center gap-1'
            : 'px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-[11px] font-bold transition cursor-pointer flex items-center gap-1';
    }
}

function updateTwin3DFloatingLabels() {
    if (!twinCamera || activeRenderMode !== '3d' || !showTwin3DLabels) return;
    const container = document.getElementById('webglTwinContainer');
    if (!container) return;
    const width = container.clientWidth;
    const height = container.clientHeight;

    const heights = {
        rs01: 6.5, rs02: 7.5, rs03: 6.5, rs04: 6.5,
        rtg01: 16.8, rtg02: 16.8, rtg03: 16.8, rtg04: 16.8, rtg05: 16.8,
        fl01: 4.2, fl02: 4.2, fl03: 4.2, fl04: 4.2,
        tt01: 4.8, tt02: 4.8, truck: 5.2
    };

    Object.keys(twin3DLabels).forEach(key => {
        const unit = twin3DUnits[key];
        const el = twin3DLabels[key];
        if (!unit || !el) return;

        if (!unit.group.visible) {
            el.style.opacity = '0';
            return;
        }

        const pos = unit.group.position.clone();
        pos.y += (heights[key] || 6.0);
        pos.project(twinCamera);

        if (pos.z > 1) {
            el.style.opacity = '0';
            return;
        }

        const x = (pos.x * 0.5 + 0.5) * width;
        const y = (-(pos.y * 0.5) + 0.5) * height;

        el.style.opacity = '1';
        el.style.left = `${x}px`;
        el.style.top = `${y}px`;
    });
}

// =========================================================================
// 5. RENDER KONTAINER DI YARD & PALET DI DALAM GUDANG CFS (3D & 2D)
// =========================================================================
function render3DBlockSlots(slots) {
    if (!twinYardSlotsGroup) return;

    while (twinYardSlotsGroup.children.length > 0) {
        twinYardSlotsGroup.remove(twinYardSlotsGroup.children[0]);
    }
    twinDynamicSlotMeshes = [];

    const blockCentersSVG = {
        'A': { x: 270, y: 250 },
        'B': { x: 490, y: 250 },
        'C': { x: 270, y: 470 },
        'D': { x: 490, y: 470 },
        'REEFER': { x: 770, y: 250 },
        'E': { x: 770, y: 470 },
        'DG': { x: 770, y: 470 }
    };

    slots.forEach((slot) => {
        const blkKey = slot.block ? slot.block.toUpperCase() : 'A';
        const centerSvg = blockCentersSVG[blkKey] || blockCentersSVG['A'];
        const center3D = svgTo3D(centerSvg.x, centerSvg.y);

        const bay = Math.max(1, parseInt(slot.bay || '1', 10));
        const row = Math.max(1, parseInt(slot.row || '1', 10));
        const tier = Math.max(1, parseInt(slot.tier || '1', 10));

        const colIdx = (bay - 1) % 3;
        const rowIdx = ((row - 1) % 4);
        const offsetX = (colIdx - 1) * 14.5;
        const offsetZ = (rowIdx - 1.5) * 9.5;
        const posY = 1.45 + (tier - 1) * 2.65;

        let colorHex = 0x2563eb;
        let isReefer = false;
        if (slot.type === 'reefer' || blkKey === 'REEFER') {
            colorHex = 0x06b6d4;
            isReefer = true;
        } else if (slot.type === 'dg' || blkKey === 'E' || blkKey === 'DG') {
            colorHex = 0xdc2626;
        } else if (blkKey === 'C') {
            colorHex = 0x7c3aed;
        }

        const isCleared = (slot.customs === 'SPPB_CLEARED');
        const box3D = createTwin3DContainerBox(12.2, 2.55, 2.5, colorHex, isCleared, isReefer);
        box3D.position.set(center3D.x + offsetX, posY, center3D.z + offsetZ);
        box3D.children.forEach(ch => {
            ch.userData = { slotData: slot };
            twinDynamicSlotMeshes.push(ch);
        });

        twinYardSlotsGroup.add(box3D);
    });
}

// Render Palet GS1 SSCC-18 di Rak Aisle A-F & Kontainer di Loading Dock Ramp D1-D5 (3D & 2D)
function renderCfsWarehouseState(pallets, cfsSummary, cfsContainers) {
    // 1. Render 3D Pallets inside Cutaway CFS Warehouse Racks
    if (twinCfsPalletsGroup) {
        while (twinCfsPalletsGroup.children.length > 0) {
            twinCfsPalletsGroup.remove(twinCfsPalletsGroup.children[0]);
        }

        const aisleIndexMap = { 'A': 0, 'B': 1, 'C': 2, 'D': 3, 'E': 4, 'F': 5 };
        const inRackPallets = (pallets || []).filter(p => p.status !== 'DISPATCHED');

        inRackPallets.forEach((p, idx) => {
            const loc = (p.rack_location || 'AISLE-A-01').toUpperCase();
            let aisleChar = 'A';
            const m = loc.match(/AISLE-([A-F])/);
            if (m && m[1]) aisleChar = m[1];
            else aisleChar = String.fromCharCode(65 + (idx % 6));

            const ai = aisleIndexMap[aisleChar] !== undefined ? aisleIndexMap[aisleChar] : (idx % 6);
            const az = -22 + ai * 8.8;
            const slotInAisle = idx % 6;
            const rx = -5.2 + ((slotInAisle % 3) - 1) * 3.8;
            const tierIdx = Math.floor(slotInAisle / 3) % 3;
            const ry = [0.52, 2.22, 3.92][tierIdx];

            const cargoColors = [0xd97706, 0x10b981, 0x0284c7, 0xf59e0b, 0x8b5cf6];
            const palObj = createTwin3DPalletMesh(cargoColors[idx % cargoColors.length]);
            palObj.group.position.set(rx, ry, az);
            palObj.carton.userData = { palletData: p };
            twinDynamicSlotMeshes.push(palObj.carton);
            twinCfsPalletsGroup.add(palObj.group);
        });
    }

    // 2. Render 3D Containers Docked at CFS Loading Ramps D1-D5
    if (twinCfsDockBoxesGroup) {
        while (twinCfsDockBoxesGroup.children.length > 0) {
            twinCfsDockBoxesGroup.remove(twinCfsDockBoxesGroup.children[0]);
        }
        const activeCfsBoxes = (cfsContainers || []).filter(c => c.status !== 'COMPLETED').slice(0, 5);
        activeCfsBoxes.forEach((c, idx) => {
            const rampNum = parseInt(String(c.ramp_no || '').replace(/\D/g, ''), 10) || (idx + 1);
            const di = Math.max(0, Math.min(4, rampNum - 1));
            const dz = -18 + di * 9.5;
            const isStuffing = (c.operation_type === 'STUFFING');
            const boxMesh = createTwin3DContainerBox(10.5, 2.5, 2.4, isStuffing ? 0x0284c7 : 0x059669, true, false);
            boxMesh.position.set(21.5, 1.45, dz);
            boxMesh.children.forEach(ch => {
                ch.userData = { cfsContainerData: c };
                twinDynamicSlotMeshes.push(ch);
            });
            twinCfsDockBoxesGroup.add(boxMesh);
        });
    }

    // 3. Render 2D SVG Pallets inside #slotsCfsPallets
    const svgCfsGroup = document.getElementById('slotsCfsPallets');
    if (svgCfsGroup) {
        svgCfsGroup.innerHTML = '';
        const aisleIndexMap = { 'A': 0, 'B': 1, 'C': 2, 'D': 3, 'E': 4, 'F': 5 };
        const inRack = (pallets || []).filter(p => p.status !== 'DISPATCHED').slice(0, 24);
        const aisleCounts = { 0: 0, 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 };

        inRack.forEach((p, idx) => {
            const loc = (p.rack_location || 'AISLE-A-01').toUpperCase();
            const m = loc.match(/AISLE-([A-F])/);
            const aisleChar = (m && m[1]) ? m[1] : String.fromCharCode(65 + (idx % 6));
            const ai = aisleIndexMap[aisleChar] !== undefined ? aisleIndexMap[aisleChar] : (idx % 6);
            const colInAisle = aisleCounts[ai] % 4;
            aisleCounts[ai]++;

            const px = 18 + colInAisle * 9.5;
            const py = (ai * 28) + 10;

            const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
            rect.setAttribute('x', px);
            rect.setAttribute('y', py);
            rect.setAttribute('width', '7.5');
            rect.setAttribute('height', '9');
            rect.setAttribute('rx', '1.5');
            rect.setAttribute('fill', '#f59e0b');
            rect.setAttribute('stroke', '#fde047');
            rect.setAttribute('stroke-width', '0.8');
            rect.style.cursor = 'pointer';
            rect.onmouseover = (e) => showCfsPalletTooltip(e, p);
            rect.onmouseout = hideCanvasTooltip;
            svgCfsGroup.appendChild(rect);
        });
    }
}

// =========================================================================
// 6. KONEKSI STREAM REAL-TIME (SSE & FALLBACK POLLING)
// =========================================================================
function initTwinStream() {
    if (window.EventSource) {
        try {
            if (sseConnection) sseConnection.close();
            sseConnection = new EventSource('api/stream_twin.php');

            sseConnection.addEventListener('initial_state', function(e) {
                updateConnectionStatus(true, 'SSE Stream Live');
                const data = JSON.parse(e.data);
                applyTwinStateUpdate(data, true);
            });

            sseConnection.addEventListener('state_update', function(e) {
                updateConnectionStatus(true, 'SSE Stream Live');
                const data = JSON.parse(e.data);
                applyTwinStateUpdate(data, false);
            });

            sseConnection.addEventListener('heartbeat', function() {
                updateConnectionStatus(true, 'SSE Stream Live');
            });

            sseConnection.onerror = function() {
                updateConnectionStatus(false, 'Reconnecting SSE...');
                sseConnection.close();
                clearTimeout(reconnectTimer);
                reconnectTimer = setTimeout(initTwinStream, 2500);
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
                updateConnectionStatus(true, 'Live Sync Active');
                applyTwinStateUpdate(data, false);
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

// =========================================================================
// 7. SINKRONISASI STATE 3 ALIRAN & PICU GERAKAN 3D SAAT DATA BERUBAH
// =========================================================================
function applyTwinStateUpdate(state, isInitial = false) {
    if (!state || !state.physical) return;
    currentTwinState = state;

    const latestEv = (state.information && state.information.recent_events && state.information.recent_events[0])
        ? state.information.recent_events[0]
        : null;
    const latestEvKey = latestEv ? `${latestEv.id}_${latestEv.event_type}_${latestEv.created_at}` : null;
    const isNewEventArrived = (!isInitial && latestEvKey && latestEvKey !== previousEventId);
    if (latestEvKey) previousEventId = latestEvKey;

    if (isNewEventArrived && latestEv) {
        triggerEventVisualFeedback(latestEv);
    }

    // --- ALIRAN 1: FISIK (3D WebGL & 2D SVG Sprites) ---
    // A. Update Posisi & Status Truk Kontainer + Gate Inbound/Outbound Barrier
    const truck = (state.physical.trucks && state.physical.trucks[0]) ? state.physical.trucks[0] : null;
    const spriteTruck = document.getElementById('spriteTruck');
    const plateText = document.getElementById('spriteTruckPlate');
    const barrierIn = document.getElementById('gateBarrierIn');
    const barrierOut = document.getElementById('gateBarrierOut');
    const weighDisplay = document.getElementById('svgWeighDisplay');

    const outboundOpen = (state.physical.gate && state.physical.gate.outbound_barrier === 'OPEN');
    if (gateBarrierOutPivot && typeof TWEEN !== 'undefined') {
        new TWEEN.Tween(gateBarrierOutPivot.rotation).to({ z: outboundOpen ? Math.PI / 2.2 : 0 }, 600).start();
    }
    if (barrierOut) {
        barrierOut.setAttribute('y2', outboundOpen ? '80' : '105');
        barrierOut.setAttribute('stroke', outboundOpen ? '#22c55e' : '#ef4444');
    }

    if (truck) {
        if (plateText) plateText.textContent = truck.license_plate;
        if (spriteTruck) spriteTruck.style.display = 'block';
        if (twin3DUnits.truck) twin3DUnits.truck.group.visible = true;

        if (truck.status === 'at_gate') {
            sprites.truck.targetX = 800;
            sprites.truck.targetY = 82;
            if (twin3DUnits.truck) twin3DUnits.truck.cargoContainer.visible = true;

            if (gateBarrierInPivot && typeof TWEEN !== 'undefined') {
                new TWEEN.Tween(gateBarrierInPivot.rotation).to({ z: -Math.PI / 2.2 }, 600).start();
            }
            if (ocrLaserBeamMesh) ocrLaserBeamMesh.material.opacity = 0.55;

            if (barrierIn) {
                barrierIn.setAttribute('y2', '80');
                barrierIn.setAttribute('stroke', '#22c55e');
            }
            updateLabelStatus('truck', truck.license_plate, 'DI GATE IN (OCR)');
        } else if (truck.status === 'in_yard') {
            sprites.truck.targetX = 480;
            sprites.truck.targetY = 142;
            if (twin3DUnits.truck) twin3DUnits.truck.cargoContainer.visible = true;

            if (gateBarrierInPivot && typeof TWEEN !== 'undefined') {
                new TWEEN.Tween(gateBarrierInPivot.rotation).to({ z: 0 }, 600).start();
            }
            if (ocrLaserBeamMesh) ocrLaserBeamMesh.material.opacity = 0.0;

            if (barrierIn) {
                barrierIn.setAttribute('y2', '105');
                barrierIn.setAttribute('stroke', '#ef4444');
            }
            updateLabelStatus('truck', truck.license_plate, 'TRANSFER BLOK B');
        } else if (truck.status === 'loading') {
            sprites.truck.targetX = 480;
            sprites.truck.targetY = 142;
            if (twin3DUnits.truck) twin3DUnits.truck.cargoContainer.visible = false;
            updateLabelStatus('truck', truck.license_plate, 'KONTAINER DIANGKAT');
        } else {
            sprites.truck.targetX = 920;
            sprites.truck.targetY = 82;
        }

        if (weighDisplay) {
            const kg = parseFloat(truck.weight_gross || 0);
            weighDisplay.textContent = kg > 0 ? (kg / 1000).toFixed(2) + ' T' : '0.00 T';
        }
    } else {
        sprites.truck.targetX = 960;
        sprites.truck.targetY = 82;
        if (gateBarrierInPivot) gateBarrierInPivot.rotation.z = 0;
        if (ocrLaserBeamMesh) ocrLaserBeamMesh.material.opacity = 0.0;
        if (twin3DUnits.truck) twin3DUnits.truck.cargoContainer.visible = true;
        updateLabelStatus('truck', 'B 9812 UIX', outboundOpen ? 'GATE OUT (SPPB)' : 'STANDBY LUAR GATE');
    }

    // B. Update Posisi & Spreader/Mast Seluruh 15 Unit Alat Berat (RS-01..04, RTG-01..05, FL-01..04, CDP-TT-01..02)
    if (state.physical.equipment) {
        Object.keys(EQUIP_ID_TO_KEY).forEach(eqId => {
            const key = EQUIP_ID_TO_KEY[eqId];
            const eqData = state.physical.equipment[eqId];
            if (!eqData || !sprites[key]) return;

            const prevX = sprites[key].targetX;
            const prevY = sprites[key].targetY;
            const newX = parseFloat(eqData.gps_x) || sprites[key].targetX;
            const newY = parseFloat(eqData.gps_y) || sprites[key].targetY;
            sprites[key].targetX = newX;
            sprites[key].targetY = newY;

            const hasContainer = Boolean(eqData.carried_container);
            const prevCarried = sprites[key].carried;
            sprites[key].carried = eqData.carried_container || null;

            if (!isInitial && (Math.hypot(newX - prevX, newY - prevY) > 1.5 || hasContainer !== Boolean(prevCarried))) {
                lastActiveEquipId = eqId;
            }

            const unit3D = twin3DUnits[key];
            if (unit3D && unit3D.carriedBox) {
                unit3D.carriedBox.visible = hasContainer;

                if (!isInitial && hasContainer !== Boolean(prevCarried) && typeof TWEEN !== 'undefined') {
                    if (unit3D.boomPivot) {
                        new TWEEN.Tween(unit3D.boomPivot.rotation).to({ z: 0.12 }, 450).yoyo(true).repeat(1).start();
                    } else if (unit3D.forkCarriage) {
                        new TWEEN.Tween(unit3D.forkCarriage.position).to({ y: 1.85 }, 420).yoyo(true).repeat(1).start();
                    } else if (unit3D.trolley) {
                        new TWEEN.Tween(unit3D.trolley.position).to({ z: 3.5 }, 480).yoyo(true).repeat(1).start();
                    }
                }
            }

            const statusLabel = hasContainer
                ? `ANGKUT ${eqData.carried_container}`
                : (eqData.status === 'operating' ? `AKTIF (${Math.round(newX)},${Math.round(newY)})` : 'STANDBY');
            updateLabelStatus(key, eqId, statusLabel);

            if (eqId === 'RS-02') {
                const containerBox2D = document.getElementById('rs02Container');
                if (containerBox2D) containerBox2D.style.display = hasContainer ? 'block' : 'none';
            }
        });

        // Update Kartu Telemetri CAN-Bus Kanan dengan Unit MHE yang Paling Baru Aktif
        const focusEq = state.physical.equipment[lastActiveEquipId] || state.physical.equipment['RS-02'];
        if (focusEq) {
            const rsBadge = document.getElementById('rs02TelemetryBadge');
            const rsCoord = document.getElementById('rs02GpsCoordText');
            const rsSpread = document.getElementById('rs02SpreaderText');
            const hasC = Boolean(focusEq.carried_container);
            if (rsCoord) rsCoord.textContent = `${focusEq.id} • (X: ${Math.round(focusEq.gps_x)}, Y: ${Math.round(focusEq.gps_y)})`;
            if (rsSpread) {
                rsSpread.textContent = hasC ? `LOCKED (${focusEq.carried_container})` : 'UNLOCKED (Siaga)';
                rsSpread.className = hasC ? 'font-bold text-emerald-600' : 'font-bold text-slate-700';
            }
            if (rsBadge) {
                rsBadge.textContent = hasC ? `${focusEq.id} CARRYING` : (focusEq.status === 'operating' ? `${focusEq.id} OPERATING` : `${focusEq.id} STANDBY`);
            }
        }
    }

    // C. Render Boks Kontainer di Yard & Palet di Dalam Gudang CFS (2D SVG & 3D WebGL)
    renderBlockSlots(state.physical.active_slots || []);
    render3DBlockSlots(state.physical.active_slots || []);
    renderCfsWarehouseState(
        state.physical.cfs_pallets || [],
        state.physical.cfs_summary || {},
        state.physical.cfs_containers || []
    );

    // D. Update Kartu Telemetri Gudang CFS & Palet LCL di Panel Kanan
    if (state.physical.cfs_summary) {
        const cfsSum = state.physical.cfs_summary;
        const jobBadge = document.getElementById('cfsActiveJobsBadge');
        const palText = document.getElementById('cfsPalletsInRackText');
        const wtText = document.getElementById('cfsTotalWeightCbmText');
        const rampText = document.getElementById('cfsActiveRampsText');

        if (jobBadge) jobBadge.textContent = `${cfsSum.active_jobs || 0} JOB AKTIF`;
        if (palText) palText.textContent = `${cfsSum.pallets_in_rack || 0} Palet (${cfsSum.pallets_dispatched || 0} Out)`;
        if (wtText) {
            wtText.textContent = `${Number(cfsSum.total_weight_kg || 0).toLocaleString('id-ID')} kg • ${Number(cfsSum.total_cbm || 0).toFixed(1)} CBM`;
        }
        if (rampText && cfsSum.ramps) {
            const busyRamps = Object.entries(cfsSum.ramps)
                .filter(([, r]) => r && r.status !== 'IDLE')
                .map(([rk, r]) => `${rk}:${r.status === 'STRIPPING' ? 'STRIP' : 'STUFF'}`);
            rampText.textContent = busyRamps.length > 0 ? busyRamps.join(' • ') : 'FL-01, FL-03, FL-04 • Ramp D1-D5 Siaga';
        }
    }

    // --- ALIRAN 2: INFORMASI (Information Telemetry Feed) ---
    if (truck) {
        const livePlate = document.getElementById('liveTruckPlate');
        const liveBox = document.getElementById('liveContainerNo');
        const liveWeight = document.getElementById('liveWeightKg');
        if (livePlate) livePlate.textContent = truck.license_plate;
        if (liveBox) liveBox.textContent = truck.container_number || 'MSKU9182374';
        if (liveWeight) liveWeight.textContent = truck.weight_gross > 0 ? Number(truck.weight_gross).toLocaleString('id-ID') + ' kg (VGM Valid)' : 'Menunggu Timbang';
    }

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

    if (state.information && state.information.reefer) {
        const rTemp = document.getElementById('reeferTempDisplay');
        if (rTemp) rTemp.textContent = state.information.reefer.avg_temp_celsius + ' °C';
    }

    // --- ALIRAN 3: FINANSIAL (Accruals & Billing Ledger) ---
    if (state.financial) {
        const newRev = parseFloat(state.financial.total_revenue_accrued_idr || 0);
        if (!isInitial && newRev > previousRevenue) {
            const diff = newRev - previousRevenue;
            spawnTwinFloatingToast(`+Rp ${diff.toLocaleString('id-ID')}`, 'Akrual Tarif Otomatis', 'amber');
        }
        previousRevenue = newRev;

        const finTotal = document.getElementById('financialTotalRevenue');
        const finWeigh = document.getElementById('finWeighFee');
        const finLoLo = document.getElementById('finLoLoFee');
        const finStorage = document.getElementById('finStorageFee');
        const finReefer = document.getElementById('finReeferFee');
        const finCfs = document.getElementById('finCfsFee');
        const setPct = document.getElementById('settlementPctDisplay');
        const setBar = document.getElementById('settlementBar');
        const paidDisp = document.getElementById('paidAmountDisplay');
        const pendDisp = document.getElementById('pendingAmountDisplay');

        if (finTotal) finTotal.textContent = 'Rp ' + Number(state.financial.total_revenue_accrued_idr).toLocaleString('id-ID');
        if (finWeigh) finWeigh.textContent = 'Rp ' + Number(state.financial.weighbridge_fee_idr).toLocaleString('id-ID');
        if (finLoLo) finLoLo.textContent = 'Rp ' + Number(state.financial.liftoff_fee_idr).toLocaleString('id-ID');
        if (finStorage) finStorage.textContent = 'Rp ' + Number(state.financial.storage_dwell_fee_idr).toLocaleString('id-ID');
        if (finReefer) finReefer.textContent = 'Rp ' + Number(state.financial.reefer_power_fee_idr).toLocaleString('id-ID');
        if (finCfs && state.financial.cfs_fee_idr !== undefined) {
            finCfs.textContent = 'Rp ' + Number(state.financial.cfs_fee_idr).toLocaleString('id-ID');
        }

        const pct = state.financial.settlement_ratio_pct || 95.0;
        if (setPct) setPct.textContent = pct + '% Lunas';
        if (setBar) setBar.style.width = pct + '%';
        if (paidDisp) paidDisp.textContent = 'Rp ' + Number(state.financial.paid_amount_idr).toLocaleString('id-ID');
        if (pendDisp) pendDisp.textContent = 'Rp ' + Number(state.financial.pending_amount_idr).toLocaleString('id-ID');
    }

    if (state.information && state.information.recent_events) {
        renderEventTicker(state.information.recent_events);
    }
}

function updateLabelStatus(key, title, sub) {
    const tEl = document.getElementById(`lblTitle-${key}`);
    const sEl = document.getElementById(`lblSub-${key}`);
    if (tEl) tEl.textContent = title;
    if (sEl) sEl.textContent = sub;
}

function triggerEventVisualFeedback(ev) {
    const actionText = document.getElementById('webglActiveActionText');
    const motionBadge = document.getElementById('twinMotionStateBadge');
    const motionText = document.getElementById('twinMotionStateText');

    const desc = `${ev.event_type}: ${ev.notes || ev.container_number || ''}`;
    if (actionText) actionText.textContent = desc;

    if (motionBadge && motionText) {
        motionBadge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-300';
        motionText.textContent = `Data Masuk: Menggerakkan Alat (${ev.event_type})`;
        setTimeout(() => {
            motionBadge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200';
            motionText.textContent = 'Standby: Menunggu Data Masuk';
        }, 3200);
    }

    spawnTwinFloatingToast(ev.event_type, ev.notes || 'Sinkronisasi 3D Selesai', 'cyan');
}

function spawnTwinFloatingToast(title, subtitle, theme = 'cyan') {
    const container = document.getElementById('twinFloatingToasts');
    if (!container) return;
    const toast = document.createElement('div');
    const bgClass = theme === 'amber'
        ? 'bg-amber-500/95 text-slate-950 border-amber-300'
        : 'bg-slate-900/95 text-cyan-300 border-cyan-500/50';
    toast.className = `twin-toast-anim px-3.5 py-2 rounded-xl border shadow-xl font-mono text-xs flex items-center gap-2.5 backdrop-blur-md ${bgClass}`;
    toast.innerHTML = `
        <i class="fa-solid ${theme === 'amber' ? 'fa-coins text-slate-900' : 'fa-bolt text-cyan-400'}"></i>
        <div>
            <div class="font-extrabold text-[11px] leading-tight">${title}</div>
            <div class="text-[9.5px] opacity-85 truncate max-w-[220px]">${subtitle}</div>
        </div>
    `;
    container.appendChild(toast);
    setTimeout(() => { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 2900);
}

// =========================================================================
// 8. LOOP ANIMASI 60 FPS (EVENT-DRIVEN KINEMATICS — DIAM SAAT IDLE)
// =========================================================================
const SPRITE_DOM_MAP = {
    truck: 'spriteTruck',
    rs01: 'spriteRS01', rs02: 'spriteRS02', rs03: 'spriteRS03', rs04: 'spriteRS04',
    rtg01: 'spriteRTG01', rtg02: 'spriteRTG02', rtg03: 'spriteRTG03', rtg04: 'spriteRTG04', rtg05: 'spriteRTG05',
    fl01: 'spriteFL01', fl02: 'spriteFL02', fl03: 'spriteFL03', fl04: 'spriteFL04',
    tt01: 'spriteTT01', tt02: 'spriteTT02'
};

function runTwinMasterRenderLoop() {
    requestAnimationFrame(runTwinMasterRenderLoop);

    if (typeof TWEEN !== 'undefined') TWEEN.update();

    Object.keys(sprites).forEach(key => {
        const sp = sprites[key];
        const dx = sp.targetX - sp.currentX;
        const dy = sp.targetY - sp.currentY;
        const dist = Math.hypot(dx, dy);
        const isRtg = key.startsWith('rtg');

        if (dist > 0.35) {
            sp.isMoving = true;
            sp.currentX += dx * 0.08;
            sp.currentY += dy * 0.08;

            if (!isRtg && dist > 1.0) {
                const targetHeading = -Math.atan2(dy, dx);
                let diffH = targetHeading - sp.heading;
                while (diffH > Math.PI) diffH -= Math.PI * 2;
                while (diffH < -Math.PI) diffH += Math.PI * 2;
                sp.heading += diffH * 0.15;
            }
        } else {
            sp.currentX = sp.targetX;
            sp.currentY = sp.targetY;
            sp.isMoving = false;
        }

        const unit3D = twin3DUnits[key];
        if (unit3D && unit3D.group) {
            const p3d = svgTo3D(sp.currentX, sp.currentY);
            unit3D.group.position.set(p3d.x, 0, p3d.z);
            if (!isRtg) {
                unit3D.group.rotation.y = sp.heading;
            }

            if (sp.isMoving && unit3D.wheels) {
                unit3D.wheels.forEach(w => { w.rotation.z -= 0.18; });
            }
        }

        const domId = SPRITE_DOM_MAP[key];
        if (domId) {
            const svgEl = document.getElementById(domId);
            if (svgEl) svgEl.setAttribute('transform', `translate(${sp.currentX.toFixed(1)}, ${sp.currentY.toFixed(1)})`);
        }
    });

    if (activeRenderMode === '3d' && twinRenderer && twinScene && twinCamera) {
        if (twinControls) twinControls.update();
        twinRenderer.render(twinScene, twinCamera);
        updateTwin3DFloatingLabels();
    }
}

// =========================================================================
// 9. KONTROL KAMERA 3D, MODE SIANG/MALAM, DAN SWITCHER 3D <-> 2D
// =========================================================================
function switchTwinRenderMode(mode) {
    activeRenderMode = mode;
    const c3d = document.getElementById('webglTwinContainer');
    const c2d = document.getElementById('svgCanvasContainer');
    const b3d = document.getElementById('btnMode3D');
    const b2d = document.getElementById('btnMode2D');
    const t3dCam = document.getElementById('toolbar3DCameras');
    const t2dZoom = document.getElementById('toolbar2DZoom');
    const titleEl = document.getElementById('viewportModeTitle');

    if (mode === '3d') {
        c3d.classList.remove('hidden');
        c2d.classList.add('hidden');
        t3dCam.classList.remove('hidden');
        t2dZoom.classList.add('hidden');
        t2dZoom.classList.remove('flex');
        b3d.className = 'px-3 py-1.5 rounded-lg text-xs font-extrabold transition-all flex items-center gap-1.5 bg-[#0170b9] text-white shadow-xs cursor-pointer';
        b2d.className = 'px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-slate-600 hover:text-slate-900 cursor-pointer';
        if (titleEl) titleEl.innerHTML = '<i class="fa-solid fa-cubes"></i> 3D WEBGL DIGITAL TWIN (THREE.JS)';
        onTwinWindowResize();
    } else {
        c3d.classList.add('hidden');
        c2d.classList.remove('hidden');
        t3dCam.classList.add('hidden');
        t2dZoom.classList.remove('hidden');
        t2dZoom.classList.add('flex');
        b2d.className = 'px-3 py-1.5 rounded-lg text-xs font-extrabold transition-all flex items-center gap-1.5 bg-[#0170b9] text-white shadow-xs cursor-pointer';
        b3d.className = 'px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-slate-600 hover:text-slate-900 cursor-pointer';
        if (titleEl) titleEl.innerHTML = '<i class="fa-solid fa-layer-group"></i> 2D SPATIAL SITE PLAN 35 HA (SVG)';
    }
}

function setTwin3DCamera(preset) {
    if (!twinCamera || !twinControls) return;
    const presets = {
        overview: { pos: { x: 0, y: 145, z: 215 },   tar: { x: 0, y: 0, z: -5 } },
        cfs:      { pos: { x: -78, y: 32, z: 22 },   tar: { x: -140, y: 2, z: -20 } },
        gate:     { pos: { x: 85, y: 38, z: -20 },   tar: { x: 65, y: 2, z: -78 } },
        yard:     { pos: { x: -45, y: 65, z: 45 },   tar: { x: -45, y: 2, z: -30 } },
        reefer:   { pos: { x: 85, y: 60, z: 50 },    tar: { x: 48, y: 2, z: -10 } },
        rail:     { pos: { x: -55, y: 42, z: 125 },  tar: { x: -10, y: 2, z: 68 } },
        top:      { pos: { x: 0, y: 255, z: 1 },     tar: { x: 0, y: 0, z: 0 } }
    };
    const p = presets[preset] || presets.overview;

    ['overview', 'cfs', 'gate', 'yard', 'reefer', 'rail', 'top'].forEach(k => {
        const btn = document.getElementById(`camBtn-${k}`);
        if (btn) {
            if (k === preset) {
                btn.className = 'px-2.5 py-1 rounded-lg bg-[#0170b9] text-white text-[11px] font-bold transition cursor-pointer flex items-center gap-1';
            } else if (k === 'cfs') {
                btn.className = 'px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-[11px] font-bold transition cursor-pointer flex items-center gap-1';
            } else {
                btn.className = 'px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold transition cursor-pointer';
            }
        }
    });

    if (typeof TWEEN !== 'undefined') {
        new TWEEN.Tween(twinCamera.position).to(p.pos, 950).easing(TWEEN.Easing.Cubic.Out).start();
        new TWEEN.Tween(twinControls.target).to(p.tar, 950).easing(TWEEN.Easing.Cubic.Out).start();
    } else {
        twinCamera.position.set(p.pos.x, p.pos.y, p.pos.z);
        twinControls.target.set(p.tar.x, p.tar.y, p.tar.z);
    }
}

function toggleTwin3DNightMode() {
    isTwinNightMode = !isTwinNightMode;
    const icon = document.getElementById('iconTwinLight');
    if (!twinScene || !twinDirLight || !twinHemiLight) return;

    if (isTwinNightMode) {
        twinScene.background.setHex(0x030712);
        twinDirLight.intensity = 0.45;
        twinDirLight.color.setHex(0x60a5fa);
        twinHemiLight.intensity = 0.35;
        if (icon) icon.className = 'fa-solid fa-moon text-indigo-500';
    } else {
        twinScene.background.setHex(0x070e1a);
        twinDirLight.intensity = 1.15;
        twinDirLight.color.setHex(0xfffbeb);
        twinHemiLight.intensity = 0.75;
        if (icon) icon.className = 'fa-solid fa-sun text-amber-500';
    }
}

// =========================================================================
// 10. INSPECTOR OBJEK 3D (RAYCASTING KLIK MHE, KONTAINER YARD, & PALET CFS)
// =========================================================================
function inspectTwinUnit(unitKey) {
    const card = document.getElementById('twin3DInspectorCard');
    const title = document.getElementById('inspTitle');
    const body = document.getElementById('inspBody');
    if (!card || !title || !body) return;

    const eqId = KEY_TO_EQUIP_ID[unitKey] || (unitKey === 'truck' ? 'TRUCK' : unitKey);

    if (eqId === 'TRUCK') {
        const t = (currentTwinState.physical && currentTwinState.physical.trucks && currentTwinState.physical.trucks[0])
            ? currentTwinState.physical.trucks[0]
            : { license_plate: 'B 9812 UIX', container_number: 'MSKU9182374', status: 'standby', weight_gross: 34850 };
        title.textContent = `TRUK ${t.license_plate}`;
        body.innerHTML = `
            <div>Kontainer: <strong class="text-white">${t.container_number || 'MSKU9182374'}</strong></div>
            <div>Status: <strong class="text-emerald-400 uppercase">${t.status}</strong></div>
            <div>Berat VGM: <strong class="text-amber-300">${Number(t.weight_gross || 0).toLocaleString('id-ID')} kg</strong></div>
        `;
    } else {
        const eq = (currentTwinState.physical && currentTwinState.physical.equipment && currentTwinState.physical.equipment[eqId])
            ? currentTwinState.physical.equipment[eqId]
            : { id: eqId, brand: 'Kalmar MHE', operator: 'Operator Siaga', status: 'idle', gps_x: 350, gps_y: 300, fuel_percent: 85, carried_container: null };
        title.textContent = `${eq.id} • ${eq.brand || 'Kalmar'}`;
        body.innerHTML = `
            <div>Operator: <strong class="text-white">${eq.operator}</strong></div>
            <div>Status: <strong class="text-emerald-400 uppercase">${eq.status}</strong></div>
            <div>Koordinat: <strong class="text-cyan-300">X: ${Math.round(eq.gps_x)}, Y: ${Math.round(eq.gps_y)}</strong></div>
            <div>Muatan Aktif: <strong class="text-amber-300">${eq.carried_container || 'Kosong (Unlocked)'}</strong></div>
            <div>Energi/BBM: <strong class="text-white">${eq.fuel_percent}%</strong></div>
        `;
    }
    card.classList.remove('hidden');
}

function inspectTwinSlotOrPallet(userData) {
    const card = document.getElementById('twin3DInspectorCard');
    const title = document.getElementById('inspTitle');
    const body = document.getElementById('inspBody');
    if (!card || !title || !body) return;

    if (userData.palletData) {
        const p = userData.palletData;
        title.textContent = `PALET CFS • ${p.pallet_code}`;
        body.innerHTML = `
            <div>GS1 SSCC-18: <strong class="text-amber-300">${p.sscc_barcode || '-'}</strong></div>
            <div>Kontainer Asal: <strong class="text-white">${p.container_number}</strong></div>
            <div>Lokasi Rak: <strong class="text-emerald-400">${p.rack_location}</strong></div>
            <div>Kargo: <strong class="text-slate-200">${p.cargo_description}</strong></div>
            <div>Berat / Vol: <strong class="text-cyan-300">${Number(p.weight_kg || 0).toLocaleString('id-ID')} kg • ${p.volume_cbm} CBM</strong></div>
            <div>Pemilik: <strong class="text-white">${p.consignee}</strong></div>
        `;
        card.classList.remove('hidden');
    } else if (userData.cfsContainerData) {
        const c = userData.cfsContainerData;
        title.textContent = `CFS DOCK ${c.ramp_no} • ${c.container_number}`;
        body.innerHTML = `
            <div>Operasi: <strong class="text-emerald-400">${c.operation_type} (${c.status})</strong></div>
            <div>Progres Palet: <strong class="text-amber-300">${c.processed_pallets} / ${c.total_pallets} Palet</strong></div>
            <div>House B/L: <strong class="text-white">${c.bl_number}</strong></div>
            <div>Consignee: <strong class="text-slate-200">${c.consignee}</strong></div>
        `;
        card.classList.remove('hidden');
    } else if (userData.slotData) {
        const s = userData.slotData;
        title.textContent = `KONTAINER ${s.container_number}`;
        body.innerHTML = `
            <div>Slot Yard: <strong class="text-cyan-300">Blok ${s.block}-${s.bay}-${s.row}-T${s.tier}</strong></div>
            <div>Tipe / Berat: <strong class="text-white">${(s.type || 'dry').toUpperCase()} • ${Number(s.weight || 0).toLocaleString('id-ID')} kg</strong></div>
            <div>Bea Cukai: <strong class="text-emerald-400">${s.customs}</strong></div>
            <div>Pemilik: <strong class="text-slate-200">${s.owner || '-'}</strong></div>
        `;
        card.classList.remove('hidden');
    }
}

function close3DInspector() {
    const card = document.getElementById('twin3DInspectorCard');
    if (card) card.classList.add('hidden');
}

function onTwinCanvasClick(e) {
    if (!twinRaycaster || !twinCamera || !twinRenderer) return;
    const rect = twinRenderer.domElement.getBoundingClientRect();
    twinMouse.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
    twinMouse.y = -((e.clientY - rect.top) / rect.height) * 2 + 1;

    twinRaycaster.setFromCamera(twinMouse, twinCamera);
    const allTargets = twinInteractableMeshes.concat(twinDynamicSlotMeshes);
    const intersects = twinRaycaster.intersectObjects(allTargets, true);
    if (intersects.length > 0) {
        const obj = intersects[0].object;
        if (obj.userData && (obj.userData.palletData || obj.userData.slotData || obj.userData.cfsContainerData)) {
            inspectTwinSlotOrPallet(obj.userData);
            return;
        }
        const pUnit = obj.userData ? obj.userData.parentUnit : null;
        if (pUnit) {
            const key = EQUIP_ID_TO_KEY[pUnit] || (pUnit === 'TRUCK' ? 'truck' : 'rs02');
            inspectTwinUnit(key);
        }
    }
}

function onTwinCanvasHover(e) {
    const rect = e.currentTarget.getBoundingClientRect();
    const relX = Math.round(((e.clientX - rect.left) / rect.width) * 1200);
    const relY = Math.round(((e.clientY - rect.top) / rect.height) * 760);
    const hoverEl = document.getElementById('canvasGpsHover');
    if (hoverEl) hoverEl.textContent = `Koordinat: (X: ${relX}, Y: ${relY})`;
}

// =========================================================================
// 11. DISPATCH PERINTAH GERAK ALAT REAL-TIME, STEP 1-6, & AKTIVITAS CFS/YARD
// =========================================================================
function dispatchEquipmentMoveZone(zoneName, targetX, targetY) {
    const select = document.getElementById('dispatchEquipSelect');
    const eqId = select ? select.value : 'RS-02';
    lastActiveEquipId = eqId;

    const formData = new URLSearchParams();
    formData.append('action', 'move_equipment');
    formData.append('equipment_id', eqId);
    formData.append('gps_x', targetX);
    formData.append('gps_y', targetY);
    formData.append('status', 'operating');

    fetch('api/edge_simulator.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(res => res.json())
    .then(() => {
        fetch('api/stream_twin.php?poll=1&force=1')
            .then(r => r.json())
            .then(st => applyTwinStateUpdate(st, false));
    });
}

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
    const drop = document.getElementById('simStepDropdown');
    if (drop) drop.classList.add('hidden');

    fetch('api/edge_simulator.php?action=step&step_id=' + stepId, { method: 'POST' })
        .then(res => res.json())
        .then(() => {
            fetch('api/stream_twin.php?poll=1&force=1')
                .then(r => r.json())
                .then(st => applyTwinStateUpdate(st, false));
        });
}

let currentTwinAnomalyCtx = null;

function triggerTwinActivity(actType) {
    const drop = document.getElementById('simStepDropdown');
    if (drop) drop.classList.add('hidden');

    // Jika aktivitas di dalam Gudang CFS dan sedang mode 3D, arahkan kamera ke Cutaway Gudang CFS
    if (actType.startsWith('cfs_') && activeRenderMode === '3d') {
        setTwin3DCamera('cfs');
    }

    fetch('api/edge_simulator.php?action=activity&type=' + encodeURIComponent(actType), { method: 'POST' })
        .then(res => res.json())
        .then(() => {
            fetch('api/stream_twin.php?poll=1&force=1')
                .then(r => r.json())
                .then(st => applyTwinStateUpdate(st, false));
        });
}

function triggerTwinAnomaly(anomType) {
    const drop = document.getElementById('simStepDropdown');
    if (drop) drop.classList.add('hidden');
    document.getElementById('twinBaSlipBanner')?.classList.add('hidden');

    if (activeRenderMode === '3d') {
        setTwin3DCamera(anomType === 'customs_mismatch' ? 'yard' : 'gate');
    }

    fetch('api/edge_simulator.php?action=trigger_anomaly&type=' + encodeURIComponent(anomType), { method: 'POST' })
        .then(res => res.json())
        .then(d => {
            const resObj = d.result || d;
            const anom = resObj.anomaly || {
                anomaly_code: d.anomaly_code || anomType.toUpperCase(),
                anomaly_title: d.message || 'Anomali Validasi Sensor & Dokumen',
                anomaly_notes: resObj.error || d.message || '',
                pre_gate_code: d.pre_gate_code || '',
                truck_plate: d.truck_plate || '',
                container_number: d.container_number || ''
            };
            currentTwinAnomalyCtx = {
                dlq_id: resObj.dlq_id || 0,
                pre_gate_code: anom.pre_gate_code || d.pre_gate_code || '',
                anomaly_code: anom.anomaly_code || d.anomaly_code || '',
                container_number: anom.container_number || d.container_number || '',
                truck_plate: anom.truck_plate || d.truck_plate || ''
            };

            const banner = document.getElementById('twinAnomalyBanner');
            if (banner) {
                document.getElementById('twinAnomCode').textContent = anom.anomaly_code || 'ANOMALY';
                document.getElementById('twinAnomAsset').textContent = `${anom.truck_plate || '-'} • ${anom.container_number || '-'} (${anom.pre_gate_code || 'GATE'})`;
                document.getElementById('twinAnomTitle').textContent = anom.anomaly_title || 'Interlock Aktif: Masalah Dokumen / Fisik';
                document.getElementById('twinAnomDesc').textContent = anom.anomaly_notes || resObj.error || '-';
                document.getElementById('twinAnomRejectTxt').textContent = anom.sop_reject || 'Tolak truk masuk terminal, kunci palang gate, dan terbitkan Berita Acara Penolakan Gate.';
                document.getElementById('twinAnomResolveTxt').textContent = anom.sop_resolve || 'Koreksi dokumen / terbitkan amandemen VGM / pasang segel baru, buka interlock, dan lanjutkan ke Yard.';
                banner.classList.remove('hidden');
            }

            spawnTwinFloatingToast('INTERLOCK AKTIF: ' + (anom.anomaly_code || 'ANOMALI'), anom.anomaly_title || 'Operasi Ditahan', 'amber');
            fetch('api/stream_twin.php?poll=1&force=1')
                .then(r => r.json())
                .then(st => applyTwinStateUpdate(st, false));
        });
}

function resolveTwinAnomaly(decision) {
    if (!currentTwinAnomalyCtx) return;
    fetch('api/demo_control.php?action=handle_anomaly', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            decision: decision,
            dlq_id: currentTwinAnomalyCtx.dlq_id || 0,
            pre_gate_code: currentTwinAnomalyCtx.pre_gate_code || '',
            anomaly_code: currentTwinAnomalyCtx.anomaly_code || '',
            container_number: currentTwinAnomalyCtx.container_number || '',
            truck_plate: currentTwinAnomalyCtx.truck_plate || ''
        })
    })
    .then(res => res.json())
    .then(d => {
        document.getElementById('twinAnomalyBanner')?.classList.add('hidden');
        const slipBanner = document.getElementById('twinBaSlipBanner');
        if (slipBanner && d.slip) {
            const isRes = decision !== 'reject';
            document.getElementById('twinBaNo').textContent = d.slip.ba_number || '-';
            document.getElementById('twinBaTitle').textContent = `${d.slip.decision_label} • ${d.slip.truck_plate || ''} (${d.slip.container_number || ''})`;
            document.getElementById('twinBaDesc').textContent = d.slip.action_taken || d.message || '-';
            const icon = document.getElementById('twinBaIcon');
            if (icon) {
                icon.className = 'w-8 h-8 rounded-xl text-white flex items-center justify-center text-sm shrink-0 ' + (isRes ? 'bg-emerald-600' : 'bg-rose-600');
                icon.innerHTML = isRes ? '<i class="fa-solid fa-file-circle-check"></i>' : '<i class="fa-solid fa-file-circle-xmark"></i>';
            }
            slipBanner.classList.remove('hidden');
        }
        spawnTwinFloatingToast(d.ba_number || 'SOP SELESAI', d.message || 'Keputusan SOP diterapkan', decision === 'reject' ? 'amber' : 'cyan');
        fetch('api/stream_twin.php?poll=1&force=1')
            .then(r => r.json())
            .then(st => applyTwinStateUpdate(st, false));
    });
}

function runSimulatorAutoCycle() {
    const btn = document.getElementById('btnAutoCycle');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Siklus Data Berjalan...</span>';
    }

    let currentStep = 1;
    function runNext() {
        if (currentStep <= 6) {
            fetch('api/edge_simulator.php?action=step&step_id=' + currentStep, { method: 'POST' })
                .then(res => res.json())
                .then(() => {
                    fetch('api/stream_twin.php?poll=1&force=1')
                        .then(r => r.json())
                        .then(st => applyTwinStateUpdate(st, false));
                    currentStep++;
                    setTimeout(runNext, 2600);
                });
        } else {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-play text-xs"></i> <span>Jalankan Siklus Data (Step 1-6)</span>';
            }
        }
    }
    runNext();
}

function resetSimulatorState() {
    fetch('api/edge_simulator.php?action=reset', { method: 'POST' })
        .then(res => res.json())
        .then(() => {
            fetch('api/stream_twin.php?poll=1&force=1')
                .then(r => r.json())
                .then(st => applyTwinStateUpdate(st, false));
        });
}

// =========================================================================
// 12. FUNGSI PENDUKUNG 2D SVG & TICKER LOG
// =========================================================================
function renderBlockSlots(slots) {
    const blocks = { 'A': 'slotsBlockA', 'B': 'slotsBlockB', 'C': 'slotsBlockC', 'D': 'slotsBlockD', 'E': 'slotsBlockDG', 'REEFER': 'slotsBlockReefer' };
    Object.values(blocks).forEach(id => {
        const el = document.getElementById(id);
        if (el) el.innerHTML = '';
    });

    slots.forEach(slot => {
        const blkId = blocks[slot.block ? slot.block.toUpperCase() : 'A'];
        const container = document.getElementById(blkId);
        if (!container) return;

        const bay = parseInt(slot.bay || '1', 10);
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
    if (svg) svg.style.transform = `translate(${canvasTranslateX}px, ${canvasTranslateY}px) scale(${canvasScale})`;
    if (disp) disp.textContent = Math.round(canvasScale * 100) + '%';
}

function handleCanvasMouseMove(e) {
    const rect = e.currentTarget.getBoundingClientRect();
    const x = Math.round((e.clientX - rect.left) / canvasScale);
    const y = Math.round((e.clientY - rect.top) / canvasScale);
    const hoverEl = document.getElementById('canvasGpsHover');
    if (hoverEl) hoverEl.textContent = `Koordinat: (X: ${x}, Y: ${y})`;
}

function showCanvasTooltip(e, slot) {
    const tooltip = document.getElementById('canvasTooltip');
    if (!tooltip) return;
    tooltip.innerHTML = `
        <div class="font-bold text-orange-400">${slot.container_number}</div>
        <div class="text-[10px] text-slate-300">Slot: Blok ${slot.block}-${slot.bay}-${slot.row}-${slot.tier}</div>
        <div class="text-[10px] text-slate-300">Tipe: ${(slot.type || 'dry').toUpperCase()} • Berat: ${Number(slot.weight || 0).toLocaleString('id-ID')} kg</div>
        <div class="text-[10px] text-emerald-400 font-bold">Bea Cukai: ${slot.customs}</div>
    `;
    tooltip.classList.remove('hidden');
    tooltip.style.left = (e.pageX + 15) + 'px';
    tooltip.style.top = (e.pageY + 10) + 'px';
}

function showCfsPalletTooltip(e, p) {
    const tooltip = document.getElementById('canvasTooltip');
    if (!tooltip) return;
    tooltip.innerHTML = `
        <div class="font-bold text-emerald-400">${p.pallet_code} • ${p.rack_location}</div>
        <div class="text-[10px] text-amber-300">SSCC-18: ${p.sscc_barcode || '-'}</div>
        <div class="text-[10px] text-slate-300">${p.cargo_description} (${Number(p.weight_kg || 0).toLocaleString('id-ID')} kg • ${p.volume_cbm} CBM)</div>
        <div class="text-[10px] text-slate-400">Kontainer: ${p.container_number} • ${p.consignee}</div>
    `;
    tooltip.classList.remove('hidden');
    tooltip.style.left = (e.pageX + 15) + 'px';
    tooltip.style.top = (e.pageY + 10) + 'px';
}

function hideCanvasTooltip() {
    const tooltip = document.getElementById('canvasTooltip');
    if (tooltip) tooltip.classList.add('hidden');
}

// Start 3D WebGL Digital Twin & SSE Stream on DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    initTwinWebGL();
    applyTwinStateUpdate(currentTwinState, true);
    initTwinStream();
    runTwinMasterRenderLoop();
});
</script>
