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

// Ambil snapshot awal state & katalog SSoT 10 lajur Gate (6 Gate-In + 4 Gate-Out)
$twin_initial = get_live_twin_state($pdo, false);
$twin_gate_lanes = array_values(cidp_gate_lane_catalog());
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
    white-space: nowrap;
    max-width: 195px;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: opacity 0.15s ease;
    will-change: left, top;
}
.tos-scroll::-webkit-scrollbar { height: 5px; width: 5px; }
.tos-scroll::-webkit-scrollbar-track { background: transparent; }
.tos-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
.tos-bay-cell {
    transition: transform 0.12s ease, box-shadow 0.12s ease;
}
.tos-bay-cell:hover {
    transform: scale(1.32);
    z-index: 10;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.35);
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
            <a href="dashboard.php?page=simulator" class="px-3 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold text-xs shadow-2xs transition-all flex items-center gap-1.5" title="Buka Simulasi Tour Arus Otonom 35 Ha (Read-Only Sandbox)">
                <i class="fa-solid fa-vr-cardboard text-xs"></i>
                <span>Tour Simulator 3D</span>
            </a>

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
    <!-- 2. MAIN SPLIT-VIEW CONSOLE: 3D TERMINAL (KIRI) & TOS PLAN (KANAN)   -->
    <!-- =================================================================== -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 items-stretch">
        
        <!-- KOLOM KIRI (XL:COL-7): KANVAS 3D WEBGL THREE.JS -->
        <div class="xl:col-span-7 bg-white rounded-2xl p-3 sm:p-4 shadow-xs border border-slate-200/90 relative overflow-hidden flex flex-col justify-between min-h-[600px] xl:min-h-[730px]">
            
            <!-- Canvas Toolbar: Camera Presets, Color Mode, Coordinates, Lighting -->
            <div class="flex items-center justify-between pb-2.5 mb-2 border-b border-slate-100 flex-wrap gap-2 text-xs">
                <div class="flex items-center gap-2 font-mono flex-wrap">
                    <span id="viewportModeTitle" class="text-[#0170b9] font-extrabold uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-cubes"></i> 3D TERMINAL LIVE (15 MHE + CFS)
                    </span>
                    <span class="text-slate-300">|</span>
                    <span id="canvasGpsHover" class="text-slate-500 text-[10.5px] font-mono">Koordinat: (X: 350, Y: 300)</span>
                </div>

                <!-- 3D Camera Preset & Theme Controls -->
                <div id="toolbar3DCameras" class="flex items-center gap-1 flex-wrap">
                    <button type="button" onclick="setTwin3DCamera('overview')" class="px-2 py-1 rounded-lg bg-[#0170b9] text-white text-[10.5px] font-bold transition cursor-pointer" id="camBtn-overview" title="Sudut Drone 35 Hektar">Drone</button>
                    <button type="button" onclick="setTwin3DCamera('cfs')" class="px-2 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-[10.5px] font-bold transition cursor-pointer" id="camBtn-cfs" title="Zoom Interior Gudang CFS, Forklift FL-01..04 &amp; Rak Palet SSCC">CFS</button>
                    <button type="button" onclick="setTwin3DCamera('gate')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10.5px] font-bold transition cursor-pointer" id="camBtn-gate" title="Plaza Gate 6-In / 4-Out &amp; Koridor Jarak Yard">Gate</button>
                    <button type="button" onclick="setTwin3DCamera('yard')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10.5px] font-bold transition cursor-pointer" id="camBtn-yard" title="Container Yard Blok A-D &amp; E-RTG">Yard</button>
                    <button type="button" onclick="setTwin3DCamera('reefer')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10.5px] font-bold transition cursor-pointer" id="camBtn-reefer" title="Reefer Cold Chain &amp; Behandle Blok E">Reefer</button>
                    <button type="button" onclick="setTwin3DCamera('rail')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10.5px] font-bold transition cursor-pointer" id="camBtn-rail" title="Intermodal Rail Siding Pasoso">Rel KA</button>
                    <button type="button" onclick="setTwin3DCamera('top')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10.5px] font-bold transition cursor-pointer" id="camBtn-top" title="Tampak Atas Orthographic">90&deg;</button>
                    <button type="button" onclick="toggleTwinColorMode()" id="btnColorMode" class="px-2 py-1 rounded-lg bg-orange-50 hover:bg-orange-100 text-orange-700 border border-orange-200 text-[10.5px] font-bold transition cursor-pointer" title="Ubah Mode Warna 3D: Aliran Unload/Load vs Jenis Kargo">
                        <i class="fa-solid fa-palette mr-0.5"></i><span>Warna: Flow</span>
                    </button>
                    <button type="button" onclick="toggleTwin3DLabels()" class="px-2 py-1 rounded-lg bg-slate-800 text-white text-[10.5px] font-bold transition cursor-pointer" id="btnToggle3DLabels" title="Tampilkan / Sembunyikan Label Mengambang"><i class="fa-solid fa-tag"></i></button>
                    <button type="button" onclick="toggleTwin3DNightMode()" class="w-7 h-7 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 flex items-center justify-center text-xs transition cursor-pointer ml-0.5" id="btnTwinNight" title="Ubah Tema Siang Terang (TOS Daylight) / Shift Malam">
                        <i class="fa-solid fa-moon" id="iconTwinLight"></i>
                    </button>
                </div>

            </div>

            <!-- =========================================================== -->
            <!-- VIEWPORT 1: 3D WEBGL THREE.JS CANVAS CONTAINER (DEFAULT)    -->
            <!-- =========================================================== -->
            <div id="webglTwinContainer" class="relative w-full flex-1 h-[480px] sm:h-[550px] xl:h-[630px] bg-[#cbd5e1] rounded-xl overflow-hidden border border-slate-300 select-none">
                <canvas id="twinWebglCanvas" class="w-full h-full block cursor-grab active:cursor-grabbing"></canvas>

                <!-- Floating 3D HTML Labels Overlay (Projected from 3D world coords) -->
                <div id="twin3DLabelsOverlay" class="absolute inset-0 pointer-events-none overflow-hidden z-10"></div>

                <!-- Floating Financial & Telemetry Toasts Overlay -->
                <div id="twinFloatingToasts" class="absolute bottom-14 right-4 flex flex-col gap-2 pointer-events-none z-30"></div>

                <!-- Top-Left Live WebGL Telemetry + TOS Legend Pill (Matching Reference Image) -->
                <div class="absolute top-2.5 left-2.5 right-2.5 pointer-events-none flex flex-wrap items-center justify-between gap-2 z-20">
                    <div class="bg-white/90 backdrop-blur-md border border-slate-300/90 rounded-xl px-2.5 py-1 text-[10.5px] text-slate-800 font-mono flex items-center gap-2 shadow-sm">
                        <span id="webglPulseDot" class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="font-extrabold text-[#0170b9]">3D LIVE TOS</span>
                        <span class="text-slate-300">|</span>
                        <span id="webglActiveActionText" class="text-slate-700 font-semibold max-w-[200px] sm:max-w-xs truncate">Sinkronisasi Realtime DB...</span>
                    </div>
                    <div class="bg-white/90 backdrop-blur-md border border-slate-300/90 rounded-xl px-2.5 py-1 text-[10px] text-slate-700 font-bold flex items-center gap-2.5 shadow-sm">
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-xs bg-orange-500 inline-block"></span>Unload In</span>
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-xs bg-emerald-500 inline-block"></span>Load Out</span>
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-xs bg-slate-500 inline-block"></span>Stored</span>
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-xs bg-rose-600 inline-block"></span>Hold</span>
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

                <!-- Bottom-Left Hint Strip -->
                <div class="absolute bottom-2.5 right-2.5 pointer-events-none bg-white/85 backdrop-blur-sm border border-slate-300 rounded-lg px-2.5 py-1 text-[10px] font-mono text-slate-700 z-10 hidden sm:flex items-center gap-2 shadow-xs">
                    <span><i class="fa-solid fa-computer-mouse text-[#0170b9] mr-1"></i>Drag: Putar</span>
                    <span>&bull;</span>
                    <span>Scroll: Zoom</span>
                    <span>&bull;</span>
                    <span>Klik Objek: Detail</span>
                </div>
            </div>

            <!-- Canvas Bottom Legend -->
            <div class="mt-2.5 pt-2 border-t border-slate-100 flex flex-wrap items-center justify-between text-[11px] text-slate-500 gap-2">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-xs bg-orange-500 inline-block"></span> Unload In</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-xs bg-emerald-500 inline-block"></span> Load Out</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-xs bg-slate-500 inline-block"></span> Stored / Transit</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-xs bg-rose-600 inline-block"></span> Hold / Alarm</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-xs bg-cyan-500 inline-block"></span> 15 MHE &amp; CFS Live</span>
                </div>
                <div class="text-[10.5px] font-mono text-slate-400">
                    <span class="text-[#0170b9] font-bold">Three.js WebGL</span> + SSE SSoT
                </div>
            </div>

        </div>

        <!-- KOLOM KANAN (XL:COL-5): EQUIPMENT WORK QUEUES & BAY PLAN MATRIX ALA TOS -->
        <div class="xl:col-span-5 flex flex-col gap-4">

            <!-- PANEL KANAN ATAS: EQUIPMENT WORK QUEUES (ANTREAN LOADING / UNLOADING) -->
            <div class="bg-white rounded-2xl p-3.5 sm:p-4 shadow-xs border border-slate-200/90 flex flex-col">
                <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100 gap-2 flex-wrap">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-orange-50 border border-orange-200 text-orange-600 flex items-center justify-center text-[11px]">
                            <i class="fa-solid fa-list-check"></i>
                        </span>
                        <div>
                            <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Work Queues &mdash; Antrean Alat Berat</h3>
                            <p class="text-[10px] text-slate-500">Klik unit atau tugas untuk fokus kamera 3D &amp; detail kontainer</p>
                        </div>
                    </div>
                    <span id="tosTotalQueueMoves" class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        Sync...
                    </span>
                </div>

                <!-- List of Equipment Work Rows (RS-01, RS-02, RS-03, RTG-01, FL-01..04) -->
                <div id="tosEquipmentQueuesList" class="divide-y divide-slate-100 text-xs">
                    <div class="py-4 text-center text-slate-400 text-[11px] font-mono">Memuat antrean kerja alat berat...</div>
                </div>
            </div>

            <!-- PANEL KANAN BAWAH: BAY PLAN / PETA OKUPANSI BLOK & SLOT REAL-TIME -->
            <div class="bg-white rounded-2xl p-3.5 sm:p-4 shadow-xs border border-slate-200/90 flex-1 flex flex-col justify-between relative">
                <div>
                    <div class="flex items-center justify-between pb-2 mb-2.5 border-b border-slate-100 gap-2 flex-wrap">
                        <div class="flex items-center gap-2">
                            <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">BAY PLAN</h3>
                            <div class="inline-flex rounded-lg bg-slate-100 p-0.5 border border-slate-200 text-[10px] font-bold">
                                <button type="button" onclick="setBayPlanFilter('all')" id="bpTab-all" class="px-2 py-0.5 rounded-md bg-white text-slate-900 shadow-2xs cursor-pointer">Semua</button>
                                <button type="button" onclick="setBayPlanFilter('yard')" id="bpTab-yard" class="px-2 py-0.5 rounded-md text-slate-600 hover:text-slate-900 cursor-pointer">Yard A-D</button>
                                <button type="button" onclick="setBayPlanFilter('special')" id="bpTab-special" class="px-2 py-0.5 rounded-md text-slate-600 hover:text-slate-900 cursor-pointer">Reefer/E/Empty</button>
                                <button type="button" onclick="setBayPlanFilter('facility')" id="bpTab-facility" class="px-2 py-0.5 rounded-md text-slate-600 hover:text-slate-900 cursor-pointer">Rail/CFS/Gate</button>
                            </div>
                        </div>
                        <span id="bayPlanSummaryPill" class="text-[10px] font-mono font-bold text-slate-500">Live condition</span>
                    </div>

                    <!-- Floating Bay Plan Cell Hover Tooltip -->
                    <div id="bayPlanHoverTooltip" class="hidden fixed z-50 pointer-events-none bg-slate-900/95 text-white border border-slate-700 rounded-xl px-3 py-2 text-[10.5px] font-mono shadow-xl max-w-xs"></div>

                    <!-- Multi-Block Bay Matrix Grid (Blok A..D, Reefer, E/DG, Empty, Rail, CFS/Gate) -->
                    <div id="tosBayPlanGrid" class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        <div class="col-span-full py-6 text-center text-slate-400 text-[11px] font-mono">Memuat peta slot blok...</div>
                    </div>
                </div>

                <!-- Reference TOS Color Legend Footer -->
                <div class="mt-3 pt-2.5 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-[10px] text-slate-600">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-[2px] bg-orange-500 inline-block"></span> Unload Inbound</span>
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-[2px] bg-emerald-500 inline-block"></span> Load Outbound</span>
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-[2px] bg-slate-500 inline-block"></span> Stored / Transit</span>
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-[2px] bg-rose-600 inline-block"></span> Hold / Alarm</span>
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-[2px] bg-white border border-slate-300 inline-block"></span> Empty Slot</span>
                    </div>
                    <span class="text-[9.5px] text-slate-400 italic">Hover kotak = detail &bull; Klik = zoom 3D</span>
                </div>
            </div>

        </div>

    </div>

    <!-- =================================================================== -->
    <!-- 2B. PANEL SINKRONISASI 3 ALIRAN (GATE/MHE, CFS/CEISA, & FINANSIAL)  -->
    <!-- =================================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-stretch">

        <!-- KARTU 1: SCANNER GERBANG & TELEMETRI CAN-BUS MHE -->
        <div class="lg:col-span-4 bg-white rounded-2xl p-4 shadow-xs border border-slate-200/90 flex flex-col justify-between gap-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-microchip text-blue-600"></i>
                    <span>Telemetri Gate &amp; CAN-Bus MHE</span>
                </h3>
                <span class="text-[10px] font-mono text-blue-600 bg-blue-50 px-2 py-0.5 rounded font-bold">PILAR 2A</span>
            </div>

            <div class="space-y-2.5 text-xs flex-1">
                <!-- Scanner Card: ANPR, OCR & Multi-Lane Plaza (6 IN + 4 OUT) -->
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex flex-col gap-1.5">
                    <div class="flex items-center justify-between text-[11px] font-mono text-slate-500">
                        <span class="font-bold text-slate-700">PLAZA GERBANG (6 IN &bull; 4 OUT)</span>
                        <span id="ocrStatusBadge" class="text-emerald-600 font-bold"><i class="fa-solid fa-circle-check"></i> 10 LAJUR LIVE</span>
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
                    <!-- Live 10-Lane Pill Grid: 6 Gate-In + 4 Gate-Out -->
                    <div class="pt-1.5 mt-1 border-t border-slate-200/80 space-y-1">
                        <div class="flex items-center justify-between text-[9.5px] font-mono font-bold text-slate-500">
                            <span>INBOUND (IN-01..06)</span>
                            <span>OUTBOUND (OUT-01..04)</span>
                        </div>
                        <div id="twinGateLanePills" class="grid grid-cols-5 gap-1 font-mono text-[9.5px]"></div>
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
            </div>
        </div>

        <!-- KARTU 2: GUDANG CFS SSCC-18, CEISA 4.0 & REEFER IOT -->
        <div class="lg:col-span-4 bg-white rounded-2xl p-4 shadow-xs border border-slate-200/90 flex flex-col justify-between gap-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-warehouse text-emerald-600"></i>
                    <span>CFS SSCC-18, CEISA &amp; Reefer</span>
                </h3>
                <span class="text-[10px] font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded font-bold">PILAR 2B</span>
            </div>

            <div class="space-y-2.5 text-xs flex-1">
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
                            <?= number_format((float)($twin_initial['physical']['cfs_summary']['total_weight_kg'] ?? 0), 0, ',', '.') ?> kg &bull; <?= number_format((float)($twin_initial['physical']['cfs_summary']['total_cbm'] ?? 0), 1, ',', '.') ?> CBM
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-[10.5px] font-mono">
                        <span class="text-slate-600">Ramp &amp; Forklift Aktif:</span>
                        <span id="cfsActiveRampsText" class="font-bold text-emerald-800">FL-01, FL-03, FL-04 &bull; Ramp D1-D5</span>
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
                            SPPB CLEARED &#10003;
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

        <!-- KARTU 3: ALIRAN FINANSIAL & BILLING ENGINE LIVE -->
        <div class="lg:col-span-4 bg-white text-slate-800 rounded-2xl p-4 shadow-xs border border-slate-200/90 flex flex-col justify-between gap-3">
            <div>
                <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
                    <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice-dollar text-orange-600"></i>
                        <span>Aliran Finansial &amp; Billing</span>
                    </h3>
                    <span class="text-[10px] font-mono text-orange-700 bg-orange-50 px-2 py-0.5 rounded border border-orange-200 font-bold">PILAR 3</span>
                </div>

                <!-- Grand Total Accrued Revenue Today -->
                <div class="mb-3 p-3 rounded-xl bg-emerald-50/60 border border-emerald-200">
                    <div class="text-[10.5px] font-bold text-emerald-800 uppercase tracking-wider">Total Akrual Pendapatan Berjalan:</div>
                    <div id="financialTotalRevenue" class="text-2xl font-black text-emerald-700 font-mono tracking-tight my-0.5">
                        Rp <?= number_format($twin_initial['financial']['total_revenue_accrued_idr'], 0, ',', '.') ?>
                    </div>
                    <div class="text-[10px] text-emerald-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-bolt text-orange-500"></i>
                        <span>Terhitung otomatis per pergerakan sensor fisik</span>
                    </div>
                </div>

                <!-- 5 Tariff Breakdown Chips -->
                <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                    <div class="p-2 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="text-[10px] text-slate-500 font-semibold">Jasa Timbang 80T:</div>
                        <div id="finWeighFee" class="text-xs font-bold font-mono text-slate-900 mt-0.5">
                            Rp <?= number_format($twin_initial['financial']['weighbridge_fee_idr'], 0, ',', '.') ?>
                        </div>
                    </div>
                    <div class="p-2 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="text-[10px] text-slate-500 font-semibold">Lift-Off / Lift-On:</div>
                        <div id="finLoLoFee" class="text-xs font-bold font-mono text-slate-900 mt-0.5">
                            Rp <?= number_format($twin_initial['financial']['liftoff_fee_idr'], 0, ',', '.') ?>
                        </div>
                    </div>
                    <div class="p-2 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="text-[10px] text-slate-500 font-semibold">Dwell Time Yard:</div>
                        <div id="finStorageFee" class="text-xs font-bold font-mono text-slate-900 mt-0.5">
                            Rp <?= number_format($twin_initial['financial']['storage_dwell_fee_idr'], 0, ',', '.') ?>
                        </div>
                    </div>
                    <div class="p-2 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="text-[10px] text-slate-500 font-semibold">Daya Reefer 380V:</div>
                        <div id="finReeferFee" class="text-xs font-bold font-mono text-slate-900 mt-0.5">
                            Rp <?= number_format($twin_initial['financial']['reefer_power_fee_idr'], 0, ',', '.') ?>
                        </div>
                    </div>
                    <div class="col-span-2 p-2 rounded-xl bg-emerald-50/60 border border-emerald-200 flex items-center justify-between">
                        <div>
                            <div class="text-[10px] text-emerald-800 font-bold uppercase">Jasa Gudang CFS (LCL &amp; SSCC-18):</div>
                            <div class="text-[9.5px] text-slate-500">Rp 450rb/box + Rp 85rb/palet</div>
                        </div>
                        <div id="finCfsFee" class="text-xs font-extrabold font-mono text-emerald-700">
                            Rp <?= number_format((int)($twin_initial['financial']['cfs_fee_idr'] ?? 0), 0, ',', '.') ?>
                        </div>
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
const TWIN_GATE_LANES = <?= json_encode($twin_gate_lanes, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const TWIN_GATE_LANE_MAP = Object.fromEntries(TWIN_GATE_LANES.map(l => [l.code, l]));
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
    truck: { currentX: 658, currentY: -112, targetX: 658, targetY: -112, heading: -Math.PI / 2, isMoving: false, visible: true },
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
let twinColorMode = 'flow'; // 'flow' (TOS Unload/Load/Stored/Hold) | 'cargo'
let twinBayPlanFilter = 'all';
let twinYardSlotsGroup = null;
let twinCfsPalletsGroup = null;
let twinCfsDockBoxesGroup = null;
let twinTransitTrucksGroup = null;
let twinGateTrucksGroup = null;
let twinRailWagonsGroup = null;
let twinEnvMeshes = { ground: null, grid: null, pads: [] };
let containerCorrugationTex = null;
const gateBarrierPivots = {};
const gateOcrLaserMeshes = {};
const gateLaneLedMaterials = {};
let lastActiveEquipId = 'RS-02';

// Cached procedural corrugated steel texture for ultra-realistic ISO containers
function getContainerCorrugationTexture() {
    if (containerCorrugationTex || typeof document === 'undefined') return containerCorrugationTex;
    const canvas = document.createElement('canvas');
    canvas.width = 256;
    canvas.height = 128;
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#e5e7eb';
    ctx.fillRect(0, 0, 256, 128);
    // Vertical ISO corrugation ribs
    for (let x = 0; x < 256; x += 8) {
        ctx.fillStyle = 'rgba(15, 23, 42, 0.16)';
        ctx.fillRect(x, 6, 3, 116);
        ctx.fillStyle = 'rgba(255, 255, 255, 0.22)';
        ctx.fillRect(x + 3, 6, 2, 116);
    }
    // Top and bottom corner rail bars
    ctx.fillStyle = 'rgba(15, 23, 42, 0.28)';
    ctx.fillRect(0, 0, 256, 6);
    ctx.fillRect(0, 122, 256, 6);
    ctx.fillRect(0, 0, 6, 128);
    ctx.fillRect(250, 0, 6, 128);

    containerCorrugationTex = new THREE.CanvasTexture(canvas);
    containerCorrugationTex.wrapS = THREE.RepeatWrapping;
    containerCorrugationTex.wrapT = THREE.ClampToEdgeWrapping;
    return containerCorrugationTex;
}

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
// 1. INISIALISASI ENGINE THREE.JS WEBGL (REALISTIC DAYLIGHT TOS DEFAULT)
// =========================================================================
function initTwinWebGL() {
    const container = document.getElementById('webglTwinContainer');
    const canvas = document.getElementById('twinWebglCanvas');
    if (!container || !canvas || typeof THREE === 'undefined') return;

    const width = container.clientWidth || 800;
    const height = container.clientHeight || 540;

    twinScene = new THREE.Scene();
    twinScene.background = new THREE.Color(0xcfdbe6);
    twinScene.fog = new THREE.FogExp2(0xcfdbe6, 0.0011);

    twinCamera = new THREE.PerspectiveCamera(42, width / height, 1, 1500);
    twinCamera.position.set(0, 195, 278);

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
    twinControls.maxDistance = 520;
    twinControls.target.set(0, 0, -24);

    // Bright Daylight Lighting (Crisp Architectural Shadows)
    twinHemiLight = new THREE.HemisphereLight(0xf8fafc, 0x64748b, 0.92);
    twinHemiLight.position.set(0, 120, 0);
    twinScene.add(twinHemiLight);

    twinDirLight = new THREE.DirectionalLight(0xfffdf5, 1.28);
    twinDirLight.position.set(110, 185, 85);
    twinDirLight.castShadow = true;
    twinDirLight.shadow.mapSize.width = 2048;
    twinDirLight.shadow.mapSize.height = 2048;
    twinDirLight.shadow.camera.near = 10;
    twinDirLight.shadow.camera.far = 520;
    const d = 215;
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
//    Gate Complex 10 Lajur (6 Gate-In + 4 Gate-Out) & Koridor Jarak ke Yard
// =========================================================================
function createGateTextTexture(title, subtitle, accentColor, bgColor = '#0f172a') {
    const c = document.createElement('canvas');
    c.width = 1024;
    c.height = 128;
    const ctx = c.getContext('2d');
    ctx.fillStyle = bgColor;
    ctx.fillRect(0, 0, 1024, 128);
    ctx.fillStyle = accentColor;
    ctx.fillRect(0, 0, 18, 128);
    ctx.fillStyle = '#f8fafc';
    ctx.font = 'bold 42px monospace';
    ctx.textBaseline = 'middle';
    ctx.fillText(title, 42, subtitle ? 44 : 64);
    if (subtitle) {
        ctx.fillStyle = accentColor;
        ctx.font = 'bold 26px sans-serif';
        ctx.fillText(subtitle, 42, 92);
    }
    return new THREE.CanvasTexture(c);
}

function createGateLanePavementTexture(code, isInbound, hasWeighbridge) {
    const c = document.createElement('canvas');
    c.width = 128;
    c.height = 256;
    const ctx = c.getContext('2d');
    ctx.fillStyle = '#1e293b';
    ctx.fillRect(0, 0, 128, 256);

    const accent = isInbound ? '#38bdf8' : '#f97316';
    ctx.fillStyle = accent;
    ctx.font = 'bold 30px monospace';
    ctx.textAlign = 'center';
    ctx.fillText(code, 64, 52);

    if (hasWeighbridge) {
        ctx.fillStyle = '#fbbf24';
        ctx.font = 'bold 17px monospace';
        ctx.fillText('VGM 80T', 64, 82);
    }

    // Directional chevron arrow on asphalt (Down/+Z for IN, Up/-Z for OUT)
    ctx.strokeStyle = isInbound ? '#22c55e' : '#ef4444';
    ctx.lineWidth = 10;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.beginPath();
    if (isInbound) {
        ctx.moveTo(64, 115); ctx.lineTo(64, 215);
        ctx.moveTo(32, 178); ctx.lineTo(64, 215); ctx.lineTo(96, 178);
    } else {
        ctx.moveTo(64, 215); ctx.lineTo(64, 115);
        ctx.moveTo(32, 152); ctx.lineTo(64, 115); ctx.lineTo(96, 152);
    }
    ctx.stroke();
    return new THREE.CanvasTexture(c);
}

function buildTwin3DTerminalEnvironment() {
    // A. Lantai Dasar Beton Kawasan Diperluas ke Utara (380m x 305m; Z: -175.5..+129.5)
    // Memberi ruang lapang Gate Plaza 10 Lajur + Buffer Corridor ±87m menuju Container Yard
    const groundGeo = new THREE.PlaneGeometry(380, 305);
    const groundMat = new THREE.MeshStandardMaterial({ color: 0xb8c4d0, roughness: 0.92, metalness: 0.04 });
    const ground = new THREE.Mesh(groundGeo, groundMat);
    ground.rotation.x = -Math.PI / 2;
    ground.position.set(0, 0, -23);
    ground.receiveShadow = true;
    twinScene.add(ground);
    twinEnvMeshes.ground = ground;

    // Concrete Expansion Grid
    const grid = new THREE.GridHelper(360, 72, 0x94a3b8, 0xa3b2c2);
    grid.position.set(0, 0.04, -23);
    if (grid.material) {
        grid.material.transparent = true;
        grid.material.opacity = 0.32;
    }
    twinScene.add(grid);
    twinEnvMeshes.grid = grid;

    // Perimeter Fence & Green Belt Landscaping (diperluas ke Utara Z = -158)
    const fenceMat = new THREE.LineBasicMaterial({ color: 0x0284c7, transparent: true, opacity: 0.55 });
    const fencePts = [
        new THREE.Vector3(-164, 0.3, -158),
        new THREE.Vector3(164, 0.3, -158),
        new THREE.Vector3(164, 0.3, 96),
        new THREE.Vector3(-164, 0.3, 96),
        new THREE.Vector3(-164, 0.3, -158)
    ];
    const fenceLine = new THREE.Line(new THREE.BufferGeometry().setFromPoints(fencePts), fenceMat);
    twinScene.add(fenceLine);

    // High-Mast Terminal Floodlight Towers (termasuk sepanjang koridor utara & apron gate)
    const poleMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, metalness: 0.6, roughness: 0.4 });
    const headMat = new THREE.MeshBasicMaterial({ color: 0xfef08a });
    [[-156, -150], [156, -150], [-156, -88], [156, -88], [-152, 90], [152, 90], [0, -86], [0, 48]].forEach(([lx, lz]) => {
        const pole = new THREE.Mesh(new THREE.CylinderGeometry(0.32, 0.55, 22, 8), poleMat);
        pole.position.set(lx, 11, lz);
        pole.castShadow = true;
        const head = new THREE.Mesh(new THREE.BoxGeometry(2.6, 0.8, 1.4), poleMat);
        head.position.set(lx, 22, lz);
        const lamp = new THREE.Mesh(new THREE.BoxGeometry(2.2, 0.25, 1.1), headMat);
        lamp.position.set(lx, 21.55, lz);
        twinScene.add(pole, head, lamp);
    });

    // Stylized Landscaping Trees Along Northern Perimeter (membuka gap untuk akses jalan tol Gate)
    const trunkMat = new THREE.MeshStandardMaterial({ color: 0x78350f, roughness: 0.9 });
    const leafMat = new THREE.MeshStandardMaterial({ color: 0x15803d, roughness: 0.8 });
    [-145, -125, -105, -85, -45, -18, 134, 150].forEach(tx => {
        const trunk = new THREE.Mesh(new THREE.CylinderGeometry(0.3, 0.45, 2.2, 6), trunkMat);
        trunk.position.set(tx, 1.1, -161);
        const crown = new THREE.Mesh(new THREE.ConeGeometry(1.9, 4.8, 7), leafMat);
        crown.position.set(tx, 4.2, -161);
        crown.castShadow = true;
        twinScene.add(trunk, crown);
    });

    // B. Jalan Sirkulasi Internal Aspal (Haul Roads + Koridor Buffer Gate <-> Yard)
    const roadMat = new THREE.MeshStandardMaterial({ color: 0x334155, roughness: 0.85 });
    const laneStripeMat = new THREE.MeshBasicMaterial({ color: 0xf8fafc, transparent: true, opacity: 0.62 });
    function addRoadStrip(svgX1, svgY1, svgX2, svgY2, widthM, showStripe = true) {
        const p1 = svgTo3D(svgX1, svgY1);
        const p2 = svgTo3D(svgX2, svgY2);
        const dx = p2.x - p1.x;
        const dz = p2.z - p1.z;
        const len = Math.hypot(dx, dz);
        const angle = -Math.atan2(dz, dx);
        const mesh = new THREE.Mesh(new THREE.BoxGeometry(len + widthM, 0.14, widthM), roadMat);
        mesh.position.set((p1.x + p2.x) / 2, 0.07, (p1.z + p2.z) / 2);
        mesh.rotation.y = angle;
        mesh.receiveShadow = true;
        twinScene.add(mesh);

        if (showStripe) {
            const stripe = new THREE.Mesh(new THREE.BoxGeometry(len * 0.94, 0.16, 0.25), laneStripeMat);
            stripe.position.set((p1.x + p2.x) / 2, 0.08, (p1.z + p2.z) / 2);
            stripe.rotation.y = angle;
            twinScene.add(stripe);
        }
    }
    // Loop Roads & Cross Roads Yard
    addRoadStrip(90, 120, 1110, 120, 10.5);
    addRoadStrip(90, 660, 1110, 660, 9);
    addRoadStrip(90, 120, 90, 660, 9);
    addRoadStrip(1110, 120, 1110, 660, 9);
    addRoadStrip(600, 120, 600, 660, 8.5);
    addRoadStrip(90, 380, 1110, 380, 7.5);

    // Jalan Tol Pendekatan Eksternal (Utara, di luar Plaza Gate: Z = -168m)
    addRoadStrip(280, -214, 1140, -214, 13.0);
    // Aspal Apron Kolektor Langsung di Selatan Exit Barrier Gate menuju Koridor CY
    addRoadStrip(650, 26, 995, 26, 18.0, false);
    // Koridor Kolektor Inbound (menyatukan IN-01..IN-06 melewati buffer apron menuju Loop Road Yard)
    addRoadStrip(735, -18, 735, 120, 15.0);
    // Koridor Kolektor Outbound (dari Loop Road Yard naik menuju OUT-01..OUT-04)
    addRoadStrip(938, -18, 938, 120, 12.0);
    // Jalan Penghubung Kantor HQ Utara <-> Loop Road Yard & Buffer Avenue
    addRoadStrip(355, -20, 970, -20, 10.0);
    addRoadStrip(355, -58, 355, 120, 8.5);

    // Apron Staging Buffer & Pull-Out Re-Weigh Inspection (di antara Gate & Yard Blok REEFER/EMPTY)
    const bufferApron = new THREE.Mesh(
        new THREE.BoxGeometry(46, 0.12, 24),
        new THREE.MeshStandardMaterial({ color: 0x475569, roughness: 0.9 })
    );
    const bufPos = svgTo3D(1070, 35);
    bufferApron.position.set(bufPos.x, 0.06, bufPos.z);
    bufferApron.receiveShadow = true;
    twinScene.add(bufferApron);
    const bufferEdge = new THREE.LineSegments(
        new THREE.EdgesGeometry(new THREE.BoxGeometry(46.2, 0.16, 24.2)),
        new THREE.LineBasicMaterial({ color: 0xf59e0b })
    );
    bufferEdge.position.set(bufPos.x, 0.1, bufPos.z);
    twinScene.add(bufferEdge);

    // C. ZONA 1: GATE PLAZA COMPLEX 10 LAJUR (6 GATE-IN + 4 GATE-OUT) @ Z = -123.9m
    const gatePos = svgTo3D(826, -58);
    const gateGroup = new THREE.Group();
    gateGroup.position.set(gatePos.x, 0, gatePos.z);

    // Plat Aspal Plaza Gate Induk (116m x 62m menghubungkan Tol Luar hingga Buffer Collector)
    const plazaDeck = new THREE.Mesh(
        new THREE.BoxGeometry(118, 0.13, 62),
        new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.88 })
    );
    plazaDeck.position.set(0, 0.065, -2);
    plazaDeck.receiveShadow = true;
    gateGroup.add(plazaDeck);

    // Kanopi Baja Utama Plaza Gate (114m x 19m, bentang 10 Lajur)
    const canopyMat = new THREE.MeshStandardMaterial({ color: 0x0284c7, roughness: 0.35, metalness: 0.35 });
    const canopy = new THREE.Mesh(new THREE.BoxGeometry(114, 1.3, 19), canopyMat);
    canopy.position.set(0, 8.8, 0);
    canopy.castShadow = true;
    gateGroup.add(canopy);

    // Fascia Strip Oranye PT MTI di Sisi Selatan & Utara Kanopi
    const fasciaMat = new THREE.MeshStandardMaterial({ color: 0xf97316, roughness: 0.4 });
    [-9.55, 9.55].forEach(fz => {
        const fascia = new THREE.Mesh(new THREE.BoxGeometry(114.2, 0.45, 0.25), fasciaMat);
        fascia.position.set(0, 9.2, fz);
        gateGroup.add(fascia);
    });

    // Overhead Signboards pada Fascia Kanopi (Rasio tepat 8:1, sejajar dengan 1024x128 CanvasTexture)
    const inSignMat = new THREE.MeshBasicMaterial({
        map: createGateTextTexture('6 GATE-IN COMPLEX (IN-01..06)', 'ANPR + OCR ISO 6346 + WEIGHBRIDGE 80T', '#38bdf8')
    });
    const inSign = new THREE.Mesh(new THREE.BoxGeometry(36, 4.5, 0.45), inSignMat);
    inSign.position.set(-24, 11.8, 9.5);
    gateGroup.add(inSign);

    const outSignMat = new THREE.MeshBasicMaterial({
        map: createGateTextTexture('4 GATE-OUT (OUT-01..04)', 'SPPB CEISA + BILLING + E-SEAL', '#f97316')
    });
    const outSign = new THREE.Mesh(new THREE.BoxGeometry(28, 3.5, 0.45), outSignMat);
    outSign.position.set(33, 11.3, 9.5);
    gateGroup.add(outSign);

    // Gardu Supervisor / Median Pemisah antara IN-06 dan OUT-01 (localX = +9.5)
    const medianMat = new THREE.MeshStandardMaterial({ color: 0xe2e8f0, roughness: 0.5 });
    const supIsland = new THREE.Mesh(new THREE.BoxGeometry(7.2, 0.45, 22), medianMat);
    supIsland.position.set(9.5, 0.22, 0);
    const supCabin = new THREE.Mesh(new THREE.BoxGeometry(4.8, 3.8, 9.0), new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.25, metalness: 0.5 }));
    supCabin.position.set(9.5, 2.1, 0);
    supCabin.castShadow = true;
    const supRoof = new THREE.Mesh(new THREE.BoxGeometry(5.4, 0.45, 9.6), fasciaMat);
    supRoof.position.set(9.5, 4.1, 0);
    gateGroup.add(supIsland, supCabin, supRoof);

    // Tiang Kolom Baja Utama Penopang Kanopi
    const pillarMat = new THREE.MeshStandardMaterial({ color: 0x94a3b8, metalness: 0.55, roughness: 0.35 });
    [-53.5, -28.5, -4.8, 9.5, 33.2, 53.5].forEach(px => {
        [-7.5, 7.5].forEach(pz => {
            const pillar = new THREE.Mesh(new THREE.CylinderGeometry(0.42, 0.48, 8.8, 12), pillarMat);
            pillar.position.set(px, 4.4, pz);
            pillar.castShadow = true;
            gateGroup.add(pillar);
        });
    });

    // Bangun seluruh 10 Lajur (6 Gate-In + 4 Gate-Out) secara seragam dari TWIN_GATE_LANES
    const boothBodyMat = new THREE.MeshStandardMaterial({ color: 0xcbd5e1, roughness: 0.5 });
    const curbMat = new THREE.MeshStandardMaterial({ color: 0xfbbf24, roughness: 0.4 });
    const wbDeckMat = new THREE.MeshStandardMaterial({ color: 0x475569, metalness: 0.65, roughness: 0.35 });

    TWIN_GATE_LANES.forEach(lane => {
        const laneWorld = svgTo3D(lane.svg_x, lane.svg_y);
        const lx = laneWorld.x - gatePos.x;
        const isInbound = (lane.direction === 'IN');

        // 1. Marka Aspal Kode Lajur & Panah Arah di permukaan lajur
        const paveMesh = new THREE.Mesh(
            new THREE.PlaneGeometry(5.8, 13.0),
            new THREE.MeshBasicMaterial({ map: createGateLanePavementTexture(lane.code, isInbound, Boolean(lane.has_weighbridge)) })
        );
        paveMesh.rotation.x = -Math.PI / 2;
        paveMesh.position.set(lx, 0.15, isInbound ? -14.0 : 14.0);
        gateGroup.add(paveMesh);

        // 2. Pulau Gardu Tol / Kiosk RFID di sisi kiri lajur
        const island = new THREE.Mesh(new THREE.BoxGeometry(1.4, 0.35, 14.0), boothBodyMat);
        island.position.set(lx - 4.35, 0.18, 0);
        const kiosk = new THREE.Mesh(new THREE.BoxGeometry(1.1, 2.8, 2.4), new THREE.MeshStandardMaterial({ color: isInbound ? 0x0369a1 : 0x9a3412, roughness: 0.4 }));
        kiosk.position.set(lx - 4.35, 1.55, 0);
        gateGroup.add(island, kiosk);

        // 3. Portal OCR / ANPR Mini Gantry + Lampu Status LED di Kanopi Depan
        const portalArch = new THREE.Mesh(
            new THREE.BoxGeometry(7.2, 0.38, 0.5),
            new THREE.MeshStandardMaterial({ color: isInbound ? 0x0284c7 : 0xf97316, roughness: 0.3 })
        );
        portalArch.position.set(lx, 6.9, isInbound ? -3.2 : 3.2);
        gateGroup.add(portalArch);

        const ledMat = new THREE.MeshBasicMaterial({ color: isInbound ? 0xef4444 : 0xef4444 });
        const ledMesh = new THREE.Mesh(new THREE.BoxGeometry(2.6, 0.55, 0.35), ledMat);
        ledMesh.position.set(lx, 7.75, 9.6);
        gateGroup.add(ledMesh);
        gateLaneLedMaterials[lane.code] = ledMat;

        // 4. Sinar Laser Pemindai OCR (khusus Gate-In saat truk terdeteksi)
        if (isInbound) {
            const laserMat = new THREE.MeshBasicMaterial({ color: 0x06b6d4, transparent: true, opacity: 0.0, side: THREE.DoubleSide });
            const laserMesh = new THREE.Mesh(new THREE.PlaneGeometry(6.8, 5.2), laserMat);
            laserMesh.position.set(lx, 3.1, -3.2);
            gateGroup.add(laserMesh);
            gateOcrLaserMeshes[lane.code] = laserMesh;
        }

        // 5. Jembatan Timbang VGM 80 Ton (16m x 3.6m) pada lajur yang dilengkapi weighbridge
        if (lane.has_weighbridge) {
            const wbDeck = new THREE.Mesh(new THREE.BoxGeometry(3.6, 0.24, 15.5), wbDeckMat);
            wbDeck.position.set(lx, 0.14, 9.5);
            wbDeck.receiveShadow = true;
            const c1 = new THREE.Mesh(new THREE.BoxGeometry(0.28, 0.42, 15.5), curbMat);
            c1.position.set(lx - 1.9, 0.28, 9.5);
            const c2 = c1.clone();
            c2.position.set(lx + 1.9, 0.28, 9.5);
            gateGroup.add(wbDeck, c1, c2);
        }

        // 6. Palang Barrier Gate Otomatis Independen per Lajur (Berputar mulus via TWEEN)
        const barrierZ = isInbound ? 19.2 : -11.5;
        const bPivot = new THREE.Group();
        bPivot.position.set(lx - 3.6, 1.45, barrierZ);
        const arm = new THREE.Mesh(
            new THREE.BoxGeometry(6.8, 0.24, 0.24),
            new THREE.MeshStandardMaterial({ color: isInbound ? 0xef4444 : 0x22c55e, emissive: isInbound ? 0x7f1d1d : 0x14532d, emissiveIntensity: 0.35 })
        );
        arm.position.set(3.4, 0, 0);
        bPivot.add(arm);
        const post = new THREE.Mesh(new THREE.CylinderGeometry(0.25, 0.3, 1.5, 10), curbMat);
        post.position.set(lx - 3.6, 0.75, barrierZ);
        gateGroup.add(bPivot, post);
        gateBarrierPivots[lane.code] = bPivot;
    });

    twinScene.add(gateGroup);

    // D. ZONA 2: 7 BLOK CONTAINER YARD PENUMPUKAN (+ EMPTY DEPOT)
    const blockDefs = [
        { id: 'A', svgX: 270, svgY: 250, color: 0x3b82f6, label: 'BLOK A (EKSPOR)' },
        { id: 'B', svgX: 490, svgY: 250, color: 0x0ea5e9, label: 'BLOK B (IMPOR)' },
        { id: 'C', svgX: 270, svgY: 470, color: 0x8b5cf6, label: 'BLOK C (DOMESTIK)' },
        { id: 'D', svgX: 490, svgY: 470, color: 0x6366f1, label: 'BLOK D (BUFFER)' },
        { id: 'REEFER', svgX: 770, svgY: 250, color: 0x06b6d4, label: 'REEFER HUB 380V' },
        { id: 'E', svgX: 770, svgY: 470, color: 0xef4444, label: 'BLOK E (DG & BEHANDLE)' },
        { id: 'EMPTY', svgX: 1005, svgY: 250, color: 0x64748b, label: 'EMPTY CONTAINER DEPOT' }
    ];

    blockDefs.forEach(b => {
        const pos = svgTo3D(b.svgX, b.svgY);
        const pad = new THREE.Mesh(
            new THREE.BoxGeometry(50, 0.22, 50),
            new THREE.MeshStandardMaterial({ color: 0x64748b, roughness: 0.85 })
        );
        pad.position.set(pos.x, 0.11, pos.z);
        pad.receiveShadow = true;
        twinScene.add(pad);
        twinEnvMeshes.pads.push(pad);

        // Garis Outline Warna Blok & Painted Slot Bays
        const edges = new THREE.EdgesGeometry(new THREE.BoxGeometry(50.2, 0.26, 50.2));
        const line = new THREE.LineSegments(edges, new THREE.LineBasicMaterial({ color: b.color }));
        line.position.set(pos.x, 0.16, pos.z);
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
    // 1. Gedung Kantor Utama & CEISA 4.0 (Utara Barat Plaza Gate: SVG 355, -58)
    const hqPos = svgTo3D(355, -58);
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

    // 3. Workshop M&R Kalmar (Barat Laut di atas Loop Road: SVG 105, 35)
    const wsPos = svgTo3D(105, 35);
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

    // 4 Gerbong Datar PPCW dengan Kontainer Dinamis di belakang Lokomotif
    const wagonColors = [0xf97316, 0x10b981, 0x64748b, 0xf97316];
    for (let w = 0; w < 4; w++) {
        const wx = 15 + w * 15.5;
        const flatcar = new THREE.Mesh(
            new THREE.BoxGeometry(14, 0.6, 2.9),
            new THREE.MeshStandardMaterial({ color: 0x334155 })
        );
        flatcar.position.set(wx, 0.8, 0);
        const wBox = createTwin3DContainerBox(12.2, 2.6, 2.5, wagonColors[w], w === 1, false);
        wBox.position.set(wx, 2.4, 0);
        locoGroup.add(flatcar, wBox);
    }
    railGroup.add(locoGroup);
    twinScene.add(railGroup);

    // Grup Dinamis untuk Truk In-Transit (Transfer Gate -> Blok), Truk Multi-Lane Gate, & Gerbong Tambahan On-Rail
    twinTransitTrucksGroup = new THREE.Group();
    twinScene.add(twinTransitTrucksGroup);

    twinGateTrucksGroup = new THREE.Group();
    twinScene.add(twinGateTrucksGroup);

    twinRailWagonsGroup = new THREE.Group();
    twinRailWagonsGroup.position.set(railPos.x + 25, 0, railPos.z + 1.8);
    twinScene.add(twinRailWagonsGroup);
}

// Helper Pembuat Boks Kontainer 3D Bertekstur Corrugated Steel Realistis
function createTwin3DContainerBox(length, height, width, colorHex, isCleared = false, isReefer = false, isHold = false) {
    const group = new THREE.Group();
    const tex = getContainerCorrugationTexture();
    const mat = new THREE.MeshStandardMaterial({
        color: colorHex,
        map: tex || null,
        roughness: 0.42,
        metalness: 0.25
    });
    const mainBox = new THREE.Mesh(new THREE.BoxGeometry(length, height, width), mat);
    mainBox.castShadow = true;
    mainBox.receiveShadow = true;
    group.add(mainBox);

    const edges = new THREE.EdgesGeometry(new THREE.BoxGeometry(length + 0.04, height + 0.04, width + 0.04));
    const edgeColor = isHold ? 0xfef08a : (isCleared ? 0x059669 : 0x1e293b);
    const wire = new THREE.LineSegments(edges, new THREE.LineBasicMaterial({ color: edgeColor }));
    group.add(wire);

    if (isReefer) {
        const cooler = new THREE.Mesh(
            new THREE.BoxGeometry(0.42, height * 0.82, width * 0.85),
            new THREE.MeshStandardMaterial({ color: 0xf8fafc, roughness: 0.3 })
        );
        cooler.position.set(length / 2 + 0.16, 0, 0);
        group.add(cooler);
    }

    if (isHold) {
        const warnBand = new THREE.Mesh(
            new THREE.BoxGeometry(length * 0.92, 0.22, width * 0.92),
            new THREE.MeshBasicMaterial({ color: 0xfef08a })
        );
        warnBand.position.y = height / 2 + 0.12;
        group.add(warnBand);
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

// Model 3D Kalmar E-RTG / RMGC Gantry Crane (RTG-01..05) dengan Bogie, Trolley, Kabel Hoist, & Spreader
function createTwin3DRTGCrane(unitId, frameColorHex = 0xeab308) {
    const group = new THREE.Group();
    group.userData = { type: 'RTG_CRANE', id: unitId };

    const mainMat = new THREE.MeshStandardMaterial({ color: frameColorHex, roughness: 0.38, metalness: 0.3 });
    const darkMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.7 });
    const bogieTireGeo = new THREE.CylinderGeometry(0.65, 0.65, 0.5, 14);
    bogieTireGeo.rotateX(Math.PI / 2);

    [-7, 7].forEach(px => {
        // Sill beam bawah + roda bogie
        const sillBeam = new THREE.Mesh(new THREE.BoxGeometry(1.1, 0.7, 17.5), darkMat);
        sillBeam.position.set(px, 0.9, 0);
        group.add(sillBeam);

        [-8, 8].forEach(pz => {
            const leg = new THREE.Mesh(new THREE.BoxGeometry(0.85, 13.5, 0.85), mainMat);
            leg.position.set(px, 7.6, pz);
            leg.castShadow = true;
            group.add(leg);

            const tire = new THREE.Mesh(bogieTireGeo, darkMat);
            tire.position.set(px, 0.65, pz);
            group.add(tire);
        });
    });

    [-7, 7].forEach(px => {
        const beam = new THREE.Mesh(new THREE.BoxGeometry(1.1, 1.3, 18), mainMat);
        beam.position.set(px, 14.4, 0);
        beam.castShadow = true;
        group.add(beam);
    });
    const topBridge = new THREE.Mesh(new THREE.BoxGeometry(15.5, 1.0, 3.8), mainMat);
    topBridge.position.set(0, 15.2, 0);
    group.add(topBridge);

    // Operator Control Cab hanging beside bridge
    const cab = new THREE.Mesh(
        new THREE.BoxGeometry(2.2, 1.8, 2.2),
        new THREE.MeshStandardMaterial({ color: 0x0ea5e9, transparent: true, opacity: 0.75, roughness: 0.2 })
    );
    cab.position.set(-4.5, 12.8, 2.2);
    group.add(cab);

    const trolley = new THREE.Mesh(new THREE.BoxGeometry(3.8, 0.8, 3.8), darkMat);
    trolley.position.set(0, 14.0, 0);
    group.add(trolley);

    // Hoist Cables (4 Steel Wire Ropes) + Yellow Spreader Rig
    const cableMat = new THREE.MeshBasicMaterial({ color: 0x475569 });
    const hoistCables = [];
    [[-1.6, -1.0], [1.6, -1.0], [-1.6, 1.0], [1.6, 1.0]].forEach(([cx, cz]) => {
        const rope = new THREE.Mesh(new THREE.CylinderGeometry(0.06, 0.06, 1, 6), cableMat);
        rope.position.set(cx, -1.6, cz);
        rope.scale.y = 3.2;
        trolley.add(rope);
        hoistCables.push(rope);
    });

    const spreaderRig = new THREE.Group();
    spreaderRig.position.set(0, -3.2, 0);
    const spreaderBeam = new THREE.Mesh(
        new THREE.BoxGeometry(12.2, 0.42, 2.6),
        new THREE.MeshStandardMaterial({ color: 0xfacc15, roughness: 0.35 })
    );
    spreaderRig.add(spreaderBeam);

    const carriedBox = createTwin3DContainerBox(12.2, 2.55, 2.5, 0xf97316, false, false);
    carriedBox.position.set(0, -1.45, 0);
    carriedBox.visible = false;
    spreaderRig.add(carriedBox);
    trolley.add(spreaderRig);

    topBridge.userData = { parentUnit: unitId };
    trolley.userData = { parentUnit: unitId };
    spreaderBeam.userData = { parentUnit: unitId };
    twinInteractableMeshes.push(topBridge, trolley, spreaderBeam);

    return { group, trolley, spreaderRig, hoistCables, carriedBox };
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
function getFlowColorHex(roleFlow, fallbackType, blkKey) {
    if (twinColorMode === 'cargo') {
        if (fallbackType === 'reefer' || blkKey === 'REEFER') return 0x06b6d4;
        if (fallbackType === 'dg' || blkKey === 'E' || blkKey === 'DG') return 0xdc2626;
        if (blkKey === 'EMPTY') return 0x94a3b8;
        if (blkKey === 'C') return 0x7c3aed;
        return 0x2563eb;
    }
    if (roleFlow === 'unload_in') return 0xf97316;
    if (roleFlow === 'load_out') return 0x10b981;
    if (roleFlow === 'hold') return 0xe11d48;
    return 0x64748b;
}

function toggleTwinColorMode() {
    twinColorMode = (twinColorMode === 'flow') ? 'cargo' : 'flow';
    const btn = document.getElementById('btnColorMode');
    if (btn) {
        btn.className = (twinColorMode === 'flow')
            ? 'px-2 py-1 rounded-lg bg-orange-50 hover:bg-orange-100 text-orange-700 border border-orange-200 text-[10.5px] font-bold transition cursor-pointer'
            : 'px-2 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-[10.5px] font-bold transition cursor-pointer';
        const span = btn.querySelector('span');
        if (span) span.textContent = (twinColorMode === 'flow') ? 'Warna: Flow' : 'Warna: Kargo';
    }
    if (currentTwinState && currentTwinState.physical) {
        render3DBlockSlots(currentTwinState.physical.active_slots || []);
        render3DTransitAndRail(currentTwinState.physical);
    }
}

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
        'E': { x: 755, y: 465 },
        'DG': { x: 805, y: 485 },
        'EMPTY': { x: 1005, y: 250 }
    };

    slots.forEach((slot) => {
        const blkKey = slot.block ? slot.block.toUpperCase() : 'A';
        if (blkKey === 'CFS') return; // Ditangani oleh renderCfsWarehouseState di dock Rampa D1-D5

        const centerSvg = blockCentersSVG[blkKey] || blockCentersSVG['A'];
        const center3D = svgTo3D(centerSvg.x, centerSvg.y);

        const bay = Math.max(1, parseInt(slot.bay || '1', 10) || 1);
        const row = Math.max(1, parseInt(slot.row || '1', 10) || 1);
        const tier = Math.max(1, parseInt(slot.tier || '1', 10) || 1);

        const colIdx = (bay - 1) % 3;
        const rowIdx = ((row - 1) % 4);
        const offsetX = (colIdx - 1) * 14.5;
        const offsetZ = (rowIdx - 1.5) * 9.5;
        const posY = 1.48 + (tier - 1) * 2.68;

        const isReefer = (slot.type === 'reefer' || blkKey === 'REEFER');
        const isHold = (slot.role_flow === 'hold');
        const isCleared = (slot.customs === 'SPPB_CLEARED');
        const colorHex = getFlowColorHex(slot.role_flow || 'stored', slot.type, blkKey);

        const box3D = createTwin3DContainerBox(12.2, 2.55, 2.5, colorHex, isCleared, isReefer, isHold);
        box3D.position.set(center3D.x + offsetX, posY, center3D.z + offsetZ);
        box3D.children.forEach(ch => {
            ch.userData = { slotData: slot };
            twinDynamicSlotMeshes.push(ch);
        });

        twinYardSlotsGroup.add(box3D);
    });
}

function render3DTransitAndRail(physical) {
    // 1. Render konvoi truk transfer in-transit di jalur inbound (dengan kontainer Oranye = Unload Inbound)
    if (twinTransitTrucksGroup) {
        while (twinTransitTrucksGroup.children.length > 0) {
            twinTransitTrucksGroup.remove(twinTransitTrucksGroup.children[0]);
        }
        const transit = (physical.in_transit_containers || []).slice(0, 3);
        transit.forEach((tr, idx) => {
            // Sebar armada in-transit melewati koridor jarak panjang Gate -> Loop Road Utara Yard
            const waypoints = [
                { x: 735, y: 66,  rot: -Math.PI / 2 },
                { x: 690, y: 120, rot: Math.PI },
                { x: 540, y: 120, rot: Math.PI }
            ];
            const wp = waypoints[idx % waypoints.length];
            const p = svgTo3D(wp.x, wp.y);
            const g = new THREE.Group();
            g.position.set(p.x, 0, p.z);
            g.rotation.y = wp.rot;

            const cab = new THREE.Mesh(new THREE.BoxGeometry(3.0, 2.6, 2.3), new THREE.MeshStandardMaterial({ color: 0xf97316, roughness: 0.35 }));
            cab.position.set(5.6, 2.0, 0);
            cab.castShadow = true;
            const trailer = new THREE.Mesh(new THREE.BoxGeometry(13.2, 0.45, 2.4), new THREE.MeshStandardMaterial({ color: 0x334155 }));
            trailer.position.set(-0.5, 1.0, 0);
            const box = createTwin3DContainerBox(12.0, 2.5, 2.4, getFlowColorHex('unload_in', tr.type, tr.block), false, tr.type === 'reefer', false);
            box.position.set(-0.8, 2.55, 0);
            box.children.forEach(ch => {
                ch.userData = { slotData: tr };
                twinDynamicSlotMeshes.push(ch);
            });
            g.add(cab, trailer, box);
            twinTransitTrucksGroup.add(g);
        });
    }

    // 2. Render gerbong datar tambahan di Jalur Rel 2 untuk kontainer on_rail nyata
    if (twinRailWagonsGroup) {
        while (twinRailWagonsGroup.children.length > 0) {
            twinRailWagonsGroup.remove(twinRailWagonsGroup.children[0]);
        }
        const onRail = (physical.on_rail_containers || []).slice(0, 5);
        onRail.forEach((rc, idx) => {
            const wx = (idx - 1.5) * 15.5;
            const flatcar = new THREE.Mesh(new THREE.BoxGeometry(14, 0.55, 2.85), new THREE.MeshStandardMaterial({ color: 0x475569 }));
            flatcar.position.set(wx, 0.85, 0);
            const box = createTwin3DContainerBox(12.0, 2.55, 2.45, getFlowColorHex(rc.role_flow || 'stored', rc.type, 'RAIL'), rc.customs === 'SPPB_CLEARED', rc.type === 'reefer', rc.role_flow === 'hold');
            box.position.set(wx, 2.42, 0);
            box.children.forEach(ch => {
                ch.userData = { slotData: rc };
                twinDynamicSlotMeshes.push(ch);
            });
            twinRailWagonsGroup.add(flatcar, box);
        });
    }
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

    // --- ALIRAN 1: FISIK (3D WebGL & Multi-Lane Gate Plaza 6 IN + 4 OUT) ---
    // A. Update 10 Palang Barrier Independen, Lampu LED, & Laser OCR per Lajur
    const gateObj = (state.physical && state.physical.gate) ? state.physical.gate : {};
    const lanesMap = gateObj.lanes || {};
    const activeInLanes = Array.isArray(gateObj.active_inbound_lanes) ? gateObj.active_inbound_lanes : [];
    const activeOutLanes = Array.isArray(gateObj.active_outbound_lanes) ? gateObj.active_outbound_lanes : [];

    TWIN_GATE_LANES.forEach(laneMeta => {
        const code = laneMeta.code;
        const lState = lanesMap[code] || {};
        const isOpen = (lState.barrier === 'OPEN');
        const pivot = gateBarrierPivots[code];
        if (pivot) {
            const targetRot = isOpen ? (Math.PI / 2.2) : 0;
            if (typeof TWEEN !== 'undefined') {
                new TWEEN.Tween(pivot.rotation).to({ z: targetRot }, 580).start();
            } else {
                pivot.rotation.z = targetRot;
            }
        }
        const ledMat = gateLaneLedMaterials[code];
        if (ledMat) {
            ledMat.color.setHex(isOpen ? 0x22c55e : (laneMeta.direction === 'IN' ? 0x0ea5e9 : 0xf97316));
        }
        const laser = gateOcrLaserMeshes[code];
        if (laser && laser.material) {
            laser.material.opacity = isOpen ? 0.55 : 0.0;
        }
    });

    renderTwinGateLanePills(lanesMap);

    // B. Posisi Truk Utama + Konvoi Multi-Truk Lajur Gerbang (IN-01..06 / OUT-01..04)
    const trucksArr = Array.isArray(state.physical.trucks) ? state.physical.trucks : [];
    const truck = trucksArr[0] || null;
    const outboundOpen = (gateObj.outbound_barrier === 'OPEN') || activeOutLanes.length > 0;

    if (truck) {
        if (twin3DUnits.truck) twin3DUnits.truck.group.visible = true;
        const assignedIn = TWIN_GATE_LANE_MAP[truck.gate_lane] || TWIN_GATE_LANES[0];

        if (truck.status === 'at_gate') {
            sprites.truck.targetX = assignedIn.svg_x;
            sprites.truck.targetY = assignedIn.svg_y - 4;
            if (twin3DUnits.truck) twin3DUnits.truck.cargoContainer.visible = true;
            updateLabelStatus('truck', truck.license_plate, `${assignedIn.code} (OCR/VGM)`);
        } else if (truck.status === 'in_yard') {
            // Meluncur melewati koridor penghubung jarak Gate <-> Container Yard
            sprites.truck.targetX = 490;
            sprites.truck.targetY = 132;
            if (twin3DUnits.truck) twin3DUnits.truck.cargoContainer.visible = true;
            updateLabelStatus('truck', truck.license_plate, `MENUJU CY (${assignedIn.code})`);
        } else if (truck.status === 'loading') {
            sprites.truck.targetX = 490;
            sprites.truck.targetY = 152;
            if (twin3DUnits.truck) twin3DUnits.truck.cargoContainer.visible = false;
            updateLabelStatus('truck', truck.license_plate, 'KONTAINER DIANGKAT');
        } else {
            sprites.truck.targetX = assignedIn.svg_x;
            sprites.truck.targetY = assignedIn.svg_y - 54;
            updateLabelStatus('truck', truck.license_plate, `BUFFER ${assignedIn.code}`);
        }
    } else if (outboundOpen) {
        const outLane = TWIN_GATE_LANE_MAP[activeOutLanes[0] || 'OUT-01'] || TWIN_GATE_LANE_MAP['OUT-01'];
        sprites.truck.targetX = outLane.svg_x;
        sprites.truck.targetY = outLane.svg_y - 34;
        if (twin3DUnits.truck) twin3DUnits.truck.cargoContainer.visible = true;
        updateLabelStatus('truck', 'ARMADA OUT', `${outLane.code} (SPPB OPEN)`);
    } else {
        sprites.truck.targetX = 658;
        sprites.truck.targetY = -98;
        if (twin3DUnits.truck) twin3DUnits.truck.cargoContainer.visible = true;
        updateLabelStatus('truck', 'B 9812 UIX', 'PRE-GATE (IN-01)');
    }

    render3DGateQueueTrucks(trucksArr.slice(1, 7), lanesMap);

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
            sprites[key].isActiveJob = (eqData.status === 'operating' || eqData.status === 'loading' || hasContainer);

            if (!isInitial && (Math.hypot(newX - prevX, newY - prevY) > 1.5 || hasContainer !== Boolean(prevCarried))) {
                lastActiveEquipId = eqId;
            }

            const unit3D = twin3DUnits[key];
            if (unit3D && unit3D.carriedBox) {
                unit3D.carriedBox.visible = hasContainer;

                if (!isInitial && hasContainer !== Boolean(prevCarried) && typeof TWEEN !== 'undefined') {
                    if (unit3D.boomPivot) {
                        new TWEEN.Tween(unit3D.boomPivot.rotation).to({ z: 0.14 }, 450).yoyo(true).repeat(1).start();
                    } else if (unit3D.forkCarriage) {
                        new TWEEN.Tween(unit3D.forkCarriage.position).to({ y: 2.1 }, 420).yoyo(true).repeat(1).start();
                    } else if (unit3D.trolley) {
                        new TWEEN.Tween(unit3D.trolley.position).to({ z: 3.8 }, 480).yoyo(true).repeat(1).start();
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

        // Sinkronisasi status active work queue ke flag kinematika 3D
        const queues = state.physical.equipment_queues || {};
        Object.keys(queues).forEach(qKey => {
            const q = queues[qKey];
            const hasActive = (q.jobs || []).some(j => j.mode === 'ACTIVE');
            if (qKey === 'FL-CFS' && hasActive) {
                if (sprites.fl01) sprites.fl01.isActiveJob = true;
                if (sprites.fl03) sprites.fl03.isActiveJob = true;
            } else if (EQUIP_ID_TO_KEY[qKey]) {
                const k = EQUIP_ID_TO_KEY[qKey];
                if (sprites[k] && hasActive) sprites[k].isActiveJob = true;
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

    // C. Render Boks Kontainer di Yard, In-Transit Trucks, On-Rail Wagons, CFS & TOS Right Console
    renderBlockSlots(state.physical.active_slots || []);
    render3DBlockSlots(state.physical.active_slots || []);
    render3DTransitAndRail(state.physical);
    renderCfsWarehouseState(
        state.physical.cfs_pallets || [],
        state.physical.cfs_summary || {},
        state.physical.cfs_containers || []
    );
    renderTosEquipmentQueues(state.physical.equipment_queues || {});
    renderTosBayPlan(state.physical);

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
                .filter(([, r]) => r && r.status && r.status !== 'IDLE' && r.status !== 'STANDBY')
                .map(([rk, r]) => `${rk}:${(r.job_type || r.status) === 'STRIPPING' ? 'STRIP' : 'STUFF'}`);
            rampText.textContent = busyRamps.length > 0 ? busyRamps.join(' • ') : 'FL-01, FL-03, FL-04 • Ramp D1-D5 Siaga';
        }
    }

    // --- ALIRAN 2: INFORMASI (Information Telemetry Feed) ---
    if (truck) {
        const livePlate = document.getElementById('liveTruckPlate');
        const liveBox = document.getElementById('liveContainerNo');
        const liveWeight = document.getElementById('liveWeightKg');
        const ocrBadge = document.getElementById('ocrStatusBadge');
        if (livePlate) livePlate.textContent = truck.license_plate;
        if (liveBox) liveBox.textContent = truck.container_number || 'MSKU9182374';
        if (liveWeight) liveWeight.textContent = truck.weight_gross > 0 ? Number(truck.weight_gross).toLocaleString('id-ID') + ' kg (VGM Valid)' : 'Menunggu Timbang';
        if (ocrBadge && truck.gate_lane) {
            ocrBadge.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${truck.gate_lane} AKTIF`;
        }
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

function runTwinMasterRenderLoop(timeMs = 0) {
    requestAnimationFrame(runTwinMasterRenderLoop);

    if (typeof TWEEN !== 'undefined') TWEEN.update();

    const tSec = (timeMs || performance.now()) * 0.0016;

    Object.keys(sprites).forEach((key, idx) => {
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

            // Realistic Loading/Unloading Hoist Kinematics for Active Equipment
            const phase = tSec + idx * 1.15;
            const working = Boolean(sp.isActiveJob || sp.isMoving);

            if (unit3D.boomPivot && unit3D.spreaderPivot) {
                const targetBoom = working ? (0.31 + Math.sin(phase) * 0.11) : 0.32;
                unit3D.boomPivot.rotation.z += (targetBoom - unit3D.boomPivot.rotation.z) * 0.08;
                unit3D.spreaderPivot.rotation.z = -unit3D.boomPivot.rotation.z;
                if (unit3D.beacon && unit3D.beacon.material) {
                    unit3D.beacon.material.color.setHex(working ? ((Math.sin(phase * 4) > 0) ? 0xf97316 : 0xfacc15) : 0xfbbf24);
                }
            }

            if (unit3D.trolley && unit3D.spreaderRig) {
                const targetTz = working ? (Math.sin(phase * 0.75) * 3.6) : 0;
                const targetHoistY = working ? (-4.5 + Math.cos(phase * 1.1) * 1.7) : -3.2;
                unit3D.trolley.position.z += (targetTz - unit3D.trolley.position.z) * 0.06;
                unit3D.spreaderRig.position.y += (targetHoistY - unit3D.spreaderRig.position.y) * 0.06;
                if (unit3D.hoistCables) {
                    const cableLen = Math.max(0.8, Math.abs(unit3D.spreaderRig.position.y));
                    unit3D.hoistCables.forEach(rope => {
                        rope.scale.y = cableLen;
                        rope.position.y = -cableLen * 0.5;
                    });
                }
            }

            if (unit3D.forkCarriage) {
                const targetForkY = working ? (1.25 + Math.sin(phase * 1.2) * 0.75) : 0.6;
                unit3D.forkCarriage.position.y += (targetForkY - unit3D.forkCarriage.position.y) * 0.08;
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
// 9. KONTROL KAMERA 3D & MODE SIANG/MALAM WEBGL
// =========================================================================
function switchTwinRenderMode() {
    activeRenderMode = '3d';
    const c3d = document.getElementById('webglTwinContainer');
    const t3dCam = document.getElementById('toolbar3DCameras');
    const titleEl = document.getElementById('viewportModeTitle');
    if (c3d) c3d.classList.remove('hidden');
    if (t3dCam) t3dCam.classList.remove('hidden');
    if (titleEl) titleEl.innerHTML = '<i class="fa-solid fa-cubes"></i> 3D TERMINAL LIVE (15 MHE + CFS)';
    if (typeof onTwinWindowResize === 'function') onTwinWindowResize();
}

function moveTwin3DCamera(pos, tar, activePreset = 'overview', durationMs = 900) {
    if (!twinCamera || !twinControls) return;
    ['overview', 'cfs', 'gate', 'yard', 'reefer', 'rail', 'top'].forEach(k => {
        const btn = document.getElementById(`camBtn-${k}`);
        if (!btn) return;
        if (k === activePreset) {
            btn.className = 'px-2 py-1 rounded-lg bg-[#0170b9] text-white text-[10.5px] font-bold transition cursor-pointer flex items-center gap-1';
        } else if (k === 'cfs') {
            btn.className = 'px-2 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-[10.5px] font-bold transition cursor-pointer flex items-center gap-1';
        } else {
            btn.className = 'px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10.5px] font-bold transition cursor-pointer';
        }
    });

    if (typeof TWEEN !== 'undefined') {
        new TWEEN.Tween(twinCamera.position).to(pos, durationMs).easing(TWEEN.Easing.Cubic.Out).start();
        new TWEEN.Tween(twinControls.target).to(tar, durationMs).easing(TWEEN.Easing.Cubic.Out).start();
    } else {
        twinCamera.position.set(pos.x, pos.y, pos.z);
        twinControls.target.set(tar.x, tar.y, tar.z);
    }
}

function setTwin3DCamera(preset) {
    const presets = {
        overview: { pos: { x: 0, y: 195, z: 278 },   tar: { x: 0, y: 0, z: -24 } },
        cfs:      { pos: { x: -78, y: 32, z: 22 },   tar: { x: -140, y: 2, z: -20 } },
        gate:     { pos: { x: 88, y: 118, z: 24 },   tar: { x: 64, y: 0, z: -98 } },
        yard:     { pos: { x: -45, y: 65, z: 45 },   tar: { x: -45, y: 2, z: -30 } },
        reefer:   { pos: { x: 85, y: 60, z: 50 },    tar: { x: 48, y: 2, z: -10 } },
        rail:     { pos: { x: -55, y: 42, z: 125 },  tar: { x: -10, y: 2, z: 68 } },
        top:      { pos: { x: 0, y: 295, z: -20 },   tar: { x: 0, y: 0, z: -22 } }
    };
    const p = presets[preset] || presets.overview;
    moveTwin3DCamera(p.pos, p.tar, preset, 950);
}

function renderTwinGateLanePills(lanesMap) {
    const container = document.getElementById('twinGateLanePills');
    if (!container) return;
    let html = '';
    TWIN_GATE_LANES.forEach(meta => {
        const st = lanesMap[meta.code] || {};
        const isOpen = (st.barrier === 'OPEN');
        const isIn = (meta.direction === 'IN');
        const baseCls = isOpen
            ? 'bg-emerald-100 text-emerald-800 border-emerald-400 font-extrabold ring-1 ring-emerald-300'
            : (isIn
                ? 'bg-sky-50 text-sky-800 border-sky-200 hover:bg-sky-100'
                : 'bg-orange-50 text-orange-800 border-orange-200 hover:bg-orange-100');
        const dotCls = isOpen ? 'bg-emerald-500 animate-ping' : (isIn ? 'bg-sky-400' : 'bg-orange-400');
        const tip = `${meta.name}${st.truck_plate ? ' • ' + st.truck_plate : ''} (${st.status || 'STANDBY'})`.replace(/"/g, '&quot;');
        html += `<button type="button" onclick="focusTwinGateLane('${meta.code}')" title="${tip}"
            class="px-1.5 py-1 rounded border text-left flex items-center justify-between gap-1 transition cursor-pointer ${baseCls}">
            <span class="flex items-center gap-1 truncate"><span class="w-1.5 h-1.5 rounded-full ${dotCls} shrink-0"></span>${meta.code}</span>
            ${meta.has_weighbridge ? '<span class="text-[8px] px-1 rounded bg-amber-200/80 text-amber-900 font-bold">80T</span>' : ''}
        </button>`;
    });
    container.innerHTML = html;
}

function focusTwinGateLane(laneCode) {
    const meta = TWIN_GATE_LANE_MAP[laneCode];
    if (!meta) {
        setTwin3DCamera('gate');
        return;
    }
    const target3D = svgTo3D(meta.svg_x, meta.svg_y);
    moveTwin3DCamera(
        { x: target3D.x + 14, y: 28, z: target3D.z + 44 },
        { x: target3D.x, y: 2, z: target3D.z + 6 },
        'gate',
        800
    );
}

function render3DGateQueueTrucks(extraTrucks, lanesMap) {
    if (!twinGateTrucksGroup) return;
    while (twinGateTrucksGroup.children.length > 0) {
        twinGateTrucksGroup.remove(twinGateTrucksGroup.children[0]);
    }
    if (!Array.isArray(extraTrucks) || extraTrucks.length === 0) return;

    extraTrucks.forEach((t, idx) => {
        const defCode = `IN-0${(idx % 5) + 2}`;
        const laneMeta = TWIN_GATE_LANE_MAP[t.gate_lane] || TWIN_GATE_LANE_MAP[defCode] || TWIN_GATE_LANES[1];
        let svgX = laneMeta.svg_x;
        let svgY = laneMeta.svg_y - 42;
        const rotY = -Math.PI / 2;

        if (t.status === 'at_gate') {
            svgY = laneMeta.svg_y + 4;
        } else if (t.status === 'in_yard' || t.status === 'loading') {
            svgX = 685 + (idx % 2) * 38;
            svgY = 48 + idx * 18;
        }

        const p = svgTo3D(svgX, svgY);
        const g = new THREE.Group();
        g.position.set(p.x, 0, p.z);
        g.rotation.y = rotY;

        const cab = new THREE.Mesh(new THREE.BoxGeometry(3.1, 2.7, 2.3), new THREE.MeshStandardMaterial({ color: 0x0284c7, roughness: 0.35 }));
        cab.position.set(5.6, 2.0, 0);
        cab.castShadow = true;
        const chassis = new THREE.Mesh(new THREE.BoxGeometry(13.2, 0.45, 2.4), new THREE.MeshStandardMaterial({ color: 0x1e293b }));
        chassis.position.set(-0.5, 1.0, 0);
        const hasBox = Boolean(t.container_number);
        if (hasBox) {
            const box = createTwin3DContainerBox(12.0, 2.5, 2.35, 0x2563eb, false, (t.target_block === 'REEFER'), false);
            box.position.set(-0.8, 2.55, 0);
            g.add(box);
        }
        g.add(cab, chassis);
        twinGateTrucksGroup.add(g);
    });
}

function toggleTwin3DNightMode() {
    isTwinNightMode = !isTwinNightMode;
    const icon = document.getElementById('iconTwinLight');
    const container = document.getElementById('webglTwinContainer');
    if (!twinScene || !twinDirLight || !twinHemiLight) return;

    if (isTwinNightMode) {
        twinScene.background.setHex(0x070e1a);
        if (twinScene.fog) twinScene.fog.color.setHex(0x070e1a);
        twinDirLight.intensity = 0.55;
        twinDirLight.color.setHex(0x93c5fd);
        twinHemiLight.intensity = 0.42;
        if (twinEnvMeshes.ground) twinEnvMeshes.ground.material.color.setHex(0x0b1324);
        twinEnvMeshes.pads.forEach(p => { if (p && p.material) p.material.color.setHex(0x0f172a); });
        if (container) container.style.backgroundColor = '#070e1a';
        if (icon) icon.className = 'fa-solid fa-sun text-amber-500';
    } else {
        twinScene.background.setHex(0xcfdbe6);
        if (twinScene.fog) twinScene.fog.color.setHex(0xcfdbe6);
        twinDirLight.intensity = 1.28;
        twinDirLight.color.setHex(0xfffdf5);
        twinHemiLight.intensity = 0.92;
        if (twinEnvMeshes.ground) twinEnvMeshes.ground.material.color.setHex(0xb8c4d0);
        twinEnvMeshes.pads.forEach(p => { if (p && p.material) p.material.color.setHex(0x64748b); });
        if (container) container.style.backgroundColor = '#cbd5e1';
        if (icon) icon.className = 'fa-solid fa-moon text-sky-700';
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

// =========================================================================
// 13. TOS WORK QUEUES & INTERACTIVE BAY PLAN RENDERER (MATCHING REFERENCE)
// =========================================================================
function escapeHtmlTwin(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function getBlockCameraPreset(blk) {
    const b = String(blk || '').toUpperCase();
    if (b === 'CFS') return 'cfs';
    if (b === 'GATE') return 'gate';
    if (b === 'RAIL') return 'rail';
    if (b === 'REEFER' || b === 'E' || b === 'DG' || b === 'EMPTY') return 'reefer';
    return 'yard';
}

function setBayPlanFilter(filterKey) {
    twinBayPlanFilter = filterKey || 'all';
    ['all', 'yard', 'special', 'facility'].forEach(k => {
        const btn = document.getElementById(`bpTab-${k}`);
        if (!btn) return;
        btn.className = (k === twinBayPlanFilter)
            ? 'px-2 py-0.5 rounded-md bg-white text-slate-900 shadow-2xs cursor-pointer'
            : 'px-2 py-0.5 rounded-md text-slate-600 hover:text-slate-900 cursor-pointer';
    });
    if (currentTwinState && currentTwinState.physical) {
        renderTosBayPlan(currentTwinState.physical);
    }
}

function renderTosEquipmentQueues(queues) {
    const listEl = document.getElementById('tosEquipmentQueuesList');
    const totalPill = document.getElementById('tosTotalQueueMoves');
    if (!listEl) return;

    const entries = Object.entries(queues || {});
    if (entries.length === 0) {
        listEl.innerHTML = '<div class="py-3 text-center text-slate-400 text-[11px] font-mono">Belum ada antrean aktif</div>';
        if (totalPill) totalPill.textContent = '0 Moves';
        return;
    }

    let grandMoves = 0;
    const badgePalette = {
        sky:     'text-sky-700 bg-sky-50 border-sky-200',
        orange:  'text-orange-700 bg-orange-50 border-orange-200',
        cyan:    'text-cyan-700 bg-cyan-50 border-cyan-200',
        emerald: 'text-emerald-700 bg-emerald-50 border-emerald-200',
        teal:    'text-teal-700 bg-teal-50 border-teal-200'
    };

    listEl.innerHTML = entries.map(([qKey, q]) => {
        const totalMoves = Number(q.total_moves ?? (q.jobs || []).length);
        grandMoves += totalMoves;
        const jobs = q.jobs || [];
        const hasActive = jobs.some(j => j.mode === 'ACTIVE');
        const camPreset = q.cam || 'overview';
        const palette = badgePalette[q.color] || badgePalette.sky;

        const pillsHtml = jobs.length > 0
            ? jobs.map((j, idx) => {
                let cls = 'border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100';
                let dot = '';
                if (j.mode === 'ACTIVE') {
                    cls = 'border-2 border-orange-500 bg-orange-50 text-orange-950 font-extrabold shadow-2xs';
                    dot = '<span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-ping shrink-0"></span>';
                } else if (j.mode === 'READY_LOAD') {
                    cls = 'border border-emerald-400 bg-emerald-50/90 text-emerald-900 font-bold hover:bg-emerald-100';
                    dot = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>';
                } else if (j.mode === 'HOLD') {
                    cls = 'border border-rose-400 bg-rose-50 text-rose-900 font-bold';
                    dot = '<span class="w-1.5 h-1.5 rounded-full bg-rose-600 shrink-0"></span>';
                }
                const targetBlk = escapeHtmlTwin(j.block || q.zone || 'A');
                const targetCnt = escapeHtmlTwin(j.container || '');
                return `
                    <button type="button" onclick="focusFromQueueJob('${targetBlk}', '${targetCnt}')"
                        class="px-2 py-1 rounded-md text-[10px] font-mono whitespace-nowrap inline-flex items-center gap-1 transition cursor-pointer shrink-0 ${cls}"
                        title="${escapeHtmlTwin(j.container)} • ${escapeHtmlTwin(j.slot)} (${escapeHtmlTwin(j.mode)})">
                        <span class="text-slate-400 font-semibold">${idx + 1}</span>
                        ${dot}
                        <span>${escapeHtmlTwin(j.label)}</span>
                    </button>
                `;
            }).join('')
            : '<span class="text-[10px] font-mono text-slate-400 italic">Standby — Menunggu Work Order</span>';

        return `
            <div class="py-2 flex items-center gap-2.5">
                <button type="button" onclick="setTwin3DCamera('${escapeHtmlTwin(camPreset)}'); inspectTwinUnit('${escapeHtmlTwin(EQUIP_ID_TO_KEY[q.id] || 'rs02')}');"
                    class="w-24 sm:w-28 shrink-0 text-left group cursor-pointer">
                    <div class="flex items-center gap-1.5">
                        <span class="px-1.5 py-0.5 rounded border text-[10px] font-black font-mono ${palette} group-hover:brightness-95">${escapeHtmlTwin(q.id)}</span>
                        ${hasActive ? '<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" title="Aktif Beroperasi"></span>' : ''}
                    </div>
                    <div class="text-[9.5px] font-mono text-slate-500 mt-0.5 truncate">${totalMoves} jobs • ${escapeHtmlTwin(q.zone)}</div>
                </button>
                <div class="flex-1 flex items-center gap-1.5 overflow-x-auto tos-scroll pb-0.5">
                    ${pillsHtml}
                </div>
            </div>
        `;
    }).join('');

    if (totalPill) {
        totalPill.textContent = `${entries.length} Unit • ${grandMoves} Antrean`;
    }
}

function renderTosBayPlan(physical) {
    const gridEl = document.getElementById('tosBayPlanGrid');
    const summaryEl = document.getElementById('bayPlanSummaryPill');
    if (!gridEl) return;

    const activeSlots = physical.active_slots || [];
    const inTransit = physical.in_transit_containers || [];
    const onRail = physical.on_rail_containers || [];
    const trains = physical.trains || [];

    const allBlocks = [
        { key: 'A',      title: 'BLOK A',      sub: 'Ekspor',       group: 'yard',     cols: 6, tiers: 4, cam: 'yard' },
        { key: 'B',      title: 'BLOK B',      sub: 'Impor',        group: 'yard',     cols: 6, tiers: 4, cam: 'yard' },
        { key: 'C',      title: 'BLOK C',      sub: 'Domestik',     group: 'yard',     cols: 6, tiers: 4, cam: 'yard' },
        { key: 'D',      title: 'BLOK D',      sub: 'Buffer',       group: 'yard',     cols: 6, tiers: 4, cam: 'yard' },
        { key: 'REEFER', title: 'REEFER 380V', sub: 'Cold Chain',   group: 'special',  cols: 6, tiers: 3, cam: 'reefer' },
        { key: 'EDG',    title: 'BLOK E / DG', sub: 'Behandle',     group: 'special',  cols: 6, tiers: 3, cam: 'reefer' },
        { key: 'EMPTY',  title: 'EMPTY DEPOT', sub: 'Kosong',       group: 'special',  cols: 6, tiers: 3, cam: 'reefer' },
        { key: 'RAIL',   title: 'RAIL KA',     sub: 'Pasoso',       group: 'facility', cols: 6, tiers: 3, cam: 'rail' },
        { key: 'CFSGATE',title: 'CFS & GATE',  sub: 'Ramp/Transit', group: 'facility', cols: 6, tiers: 3, cam: 'cfs' }
    ];

    const visibleBlocks = allBlocks.filter(b => twinBayPlanFilter === 'all' || b.group === twinBayPlanFilter);

    // Bucket containers cleanly per Bay Plan Card
    const buckets = { A: [], B: [], C: [], D: [], REEFER: [], EDG: [], EMPTY: [], RAIL: [], CFSGATE: [] };
    activeSlots.forEach(s => {
        const blk = String(s.block || 'A').toUpperCase();
        if (blk === 'E' || blk === 'DG') buckets.EDG.push(s);
        else if (blk === 'CFS') buckets.CFSGATE.push(s);
        else if (buckets[blk]) buckets[blk].push(s);
        else buckets.A.push(s);
    });
    inTransit.forEach(t => buckets.CFSGATE.push(t));
    onRail.forEach(r => buckets.RAIL.push(r));

    let totalUnload = inTransit.length;
    let totalLoad = 0;
    let totalHold = 0;

    gridEl.innerHTML = visibleBlocks.map(def => {
        const items = buckets[def.key] || [];
        let uCnt = 0, lCnt = 0, hCnt = 0, sCnt = 0;
        items.forEach(it => {
            if (it.role_flow === 'unload_in') uCnt++;
            else if (it.role_flow === 'load_out') lCnt++;
            else if (it.role_flow === 'hold') hCnt++;
            else sCnt++;
        });
        totalLoad += lCnt;
        totalHold += hCnt;

        // Place items cleanly into a cols x tiers visual matrix (Tier 1 at bottom, Tier N at top)
        const matrix = Array.from({ length: def.tiers }, () => Array(def.cols).fill(null));
        items.slice(0, def.tiers * def.cols).forEach((item, i) => {
            let tIdx = (parseInt(item.tier || '0', 10) || 0) - 1;
            let cIdx = ((parseInt(item.bay || '1', 10) || 1) - 1 + ((parseInt(item.row || '1', 10) || 1) - 1) * 2) % def.cols;
            if (tIdx < 0 || tIdx >= def.tiers || isNaN(tIdx)) {
                tIdx = Math.floor(i / def.cols) % def.tiers;
                cIdx = i % def.cols;
            }
            // Prevent overlap inside visual cell by finding nearest free column at that tier or next tier
            if (matrix[tIdx][cIdx]) {
                let placed = false;
                for (let step = 0; step < def.tiers * def.cols; step++) {
                    const tryT = (tIdx + Math.floor((cIdx + step) / def.cols)) % def.tiers;
                    const tryC = (cIdx + step) % def.cols;
                    if (!matrix[tryT][tryC]) {
                        matrix[tryT][tryC] = item;
                        placed = true;
                        break;
                    }
                }
                if (!placed) matrix[tIdx][cIdx] = item;
            } else {
                matrix[tIdx][cIdx] = item;
            }
        });

        // Build rows top-down (Highest Tier on top, Tier 1 resting on base bar like ship/yard cross-section)
        let rowsHtml = '';
        for (let t = def.tiers - 1; t >= 0; t--) {
            let cellsHtml = '';
            for (let c = 0; c < def.cols; c++) {
                const cellItem = matrix[t][c];
                if (cellItem) {
                    let colorCls = 'bg-slate-500 border-slate-600';
                    if (cellItem.role_flow === 'unload_in') colorCls = 'bg-orange-500 border-orange-600';
                    else if (cellItem.role_flow === 'load_out') colorCls = 'bg-emerald-500 border-emerald-600';
                    else if (cellItem.role_flow === 'hold') colorCls = 'bg-rose-600 border-rose-700 animate-pulse';

                    const payloadAttr = escapeHtmlTwin(JSON.stringify(cellItem));
                    cellsHtml += `<button type="button" data-slot="${payloadAttr}" onmouseenter="showBayPlanCellTooltip(event, this)" onmouseleave="hideBayPlanCellTooltip()" onclick="focusFromBayPlan(this, '${def.cam}')" class="tos-bay-cell w-3.5 h-3.5 rounded-[2px] border cursor-pointer ${colorCls}"></button>`;
                } else {
                    const emptyAddr = `${def.title} • Bay 0${c + 1} • Tier ${t + 1} (Kosong)`;
                    cellsHtml += `<div onmouseenter="showEmptySlotTooltip(event, '${escapeHtmlTwin(emptyAddr)}')" onmouseleave="hideBayPlanCellTooltip()" class="w-3.5 h-3.5 rounded-[2px] border border-slate-300 bg-white"></div>`;
                }
            }
            rowsHtml += `<div class="flex items-center justify-center gap-0.5">${cellsHtml}</div>`;
        }

        const rightBadge = hCnt > 0
            ? `<span class="text-rose-600 font-extrabold">H:${hCnt}</span> <span class="text-emerald-600">L:${lCnt}</span>`
            : `<span class="text-orange-600">U:${uCnt}</span> <span class="text-slate-300">&bull;</span> <span class="text-emerald-600">L:${lCnt}</span>`;

        return `
            <div class="p-2 rounded-xl bg-slate-50/80 border border-slate-200/90 hover:border-blue-300 transition flex flex-col justify-between">
                <div class="flex items-center justify-between gap-1 mb-1.5">
                    <button type="button" onclick="setTwin3DCamera('${def.cam}')" class="text-[10px] font-black text-slate-900 hover:text-[#0170b9] truncate cursor-pointer" title="Fokus Kamera ke ${def.title}">${def.title}</button>
                    <span class="text-[9.5px] font-mono font-bold whitespace-nowrap">${rightBadge}</span>
                </div>
                <div class="flex flex-col gap-0.5 py-1 border-b-2 border-slate-400/70 bg-white/70 rounded-t px-1">
                    ${rowsHtml}
                </div>
                <div class="flex items-center justify-between text-[9px] text-slate-400 font-mono mt-1">
                    <span>${def.sub}</span>
                    <span class="font-bold text-slate-600">${items.length} unit</span>
                </div>
            </div>
        `;
    }).join('');

    if (summaryEl) {
        const kaLoaded = trains.slice(0, 2).reduce((acc, t) => acc + Number(t.loaded_wagons || 0), 0);
        summaryEl.textContent = `${activeSlots.length} Yard • ${inTransit.length} Transit • ${onRail.length} Rail (${kaLoaded} Grb)`;
    }
}

function showBayPlanCellTooltip(e, el) {
    const tip = document.getElementById('bayPlanHoverTooltip');
    if (!tip || !el || !el.dataset.slot) return;
    try {
        const s = JSON.parse(el.dataset.slot);
        const flowText = s.role_flow === 'unload_in' ? 'UNLOAD INBOUND'
            : (s.role_flow === 'load_out' ? 'LOAD OUTBOUND (CLEARED)'
            : (s.role_flow === 'hold' ? 'HOLD / INTERLOCK' : 'STORED IN YARD'));
        const flowCls = s.role_flow === 'unload_in' ? 'text-orange-400'
            : (s.role_flow === 'load_out' ? 'text-emerald-400'
            : (s.role_flow === 'hold' ? 'text-rose-400' : 'text-slate-300'));
        const addr = `Blok ${s.block || '-'} • Bay ${s.bay || '-'} • Row ${s.row || '-'} • Tier ${s.tier || '-'}`;
        tip.innerHTML = `
            <div class="font-extrabold text-white">${escapeHtmlTwin(s.container_number)}</div>
            <div class="text-[9.5px] font-bold ${flowCls} mt-0.5">${flowText}</div>
            <div class="text-[9.5px] text-cyan-300 mt-1">${escapeHtmlTwin(addr)}</div>
            <div class="text-[9.5px] text-slate-300">${escapeHtmlTwin(s.owner || '-')} • ${Number(s.weight || 0).toLocaleString('id-ID')} kg</div>
            <div class="text-[9px] text-slate-400 mt-0.5">Pabean: ${escapeHtmlTwin(s.customs)} • Billing: ${escapeHtmlTwin(s.billing || '-')}</div>
        `;
        tip.classList.remove('hidden');
        tip.style.left = Math.min(window.innerWidth - 250, e.clientX + 14) + 'px';
        tip.style.top = Math.min(window.innerHeight - 130, e.clientY + 14) + 'px';
    } catch (err) {}
}

function showEmptySlotTooltip(e, addr) {
    const tip = document.getElementById('bayPlanHoverTooltip');
    if (!tip) return;
    tip.innerHTML = `<div class="font-bold text-slate-300">${addr}</div><div class="text-[9.5px] text-slate-400">Siap diisi (Available Slot)</div>`;
    tip.classList.remove('hidden');
    tip.style.left = Math.min(window.innerWidth - 230, e.clientX + 14) + 'px';
    tip.style.top = Math.min(window.innerHeight - 80, e.clientY + 14) + 'px';
}

function hideBayPlanCellTooltip() {
    const tip = document.getElementById('bayPlanHoverTooltip');
    if (tip) tip.classList.add('hidden');
}

function focusFromBayPlan(el, camPreset) {
    if (activeRenderMode === '3d') {
        setTwin3DCamera(camPreset || 'yard');
    }
    try {
        const s = JSON.parse(el.dataset.slot);
        inspectTwinSlotOrPallet({ slotData: s });
    } catch (err) {}
}

function focusFromQueueJob(blk, containerNo) {
    if (activeRenderMode === '3d') {
        setTwin3DCamera(getBlockCameraPreset(blk));
    }
    const slots = (currentTwinState.physical && currentTwinState.physical.active_slots) || [];
    const transit = (currentTwinState.physical && currentTwinState.physical.in_transit_containers) || [];
    const onRail = (currentTwinState.physical && currentTwinState.physical.on_rail_containers) || [];
    const match = slots.concat(transit, onRail).find(s => s.container_number === containerNo);
    if (match) {
        inspectTwinSlotOrPallet({ slotData: match });
    }
}

function initTwinDeepLinking() {
    const p = new URLSearchParams(window.location.search);
    const focusBox = p.get('focus_box') || p.get('ctr') || p.get('search');
    const viewParam = (p.get('view') || p.get('zone') || '').toLowerCase();
    const blockParam = (p.get('highlight_block') || p.get('block') || '').toUpperCase();
    const facParam = (p.get('fac_id') || '').toLowerCase();

    setTimeout(() => {
        if (viewParam === 'cfs') {
            setTwin3DCamera('cfs');
        } else if (viewParam === 'reefer' || blockParam === 'REEFER' || blockParam === 'R') {
            setTwin3DCamera('reefer');
        } else if (viewParam === 'rail' || blockParam === 'D' || blockParam === 'RAIL') {
            setTwin3DCamera('rail');
        } else if (viewParam === 'gate' || facParam.includes('gate')) {
            setTwin3DCamera('gate');
        } else if (blockParam) {
            setTwin3DCamera(getBlockCameraPreset(blockParam));
        } else if (viewParam === 'yard') {
            setTwin3DCamera('yard');
        }

        if (focusBox) {
            const clean = focusBox.replace(/[^A-Z0-9]/gi, '').toUpperCase();
            const slots = (currentTwinState?.physical?.active_slots) || [];
            const transit = (currentTwinState?.physical?.in_transit_containers) || [];
            const onRail = (currentTwinState?.physical?.on_rail_containers) || [];
            const match = slots.concat(transit, onRail).find(s =>
                String(s.container_number || '').replace(/[^A-Z0-9]/gi, '').toUpperCase() === clean
            );
            if (match) {
                setTwin3DCamera(getBlockCameraPreset(match.block));
                inspectTwinSlotOrPallet({ slotData: match });
            }
        }
    }, 250);
}

// Start 3D WebGL Digital Twin & SSE Stream on DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    initTwinWebGL();
    applyTwinStateUpdate(currentTwinState, true);
    initTwinStream();
    runTwinMasterRenderLoop();
    initTwinDeepLinking();
});
</script>
