<?php
/**
 * =============================================================================
 * FILE: pages/integration.php
 * FUNGSI: Pusat Kendali Integrasi Data, OAuth 2.0 Token Server, Simulator Sensor
 *         Pre-Advice & Aktivitas Lapangan, Dead Letter Queue (DLQ), & Odoo Sync
 * =============================================================================
 */

require_once __DIR__ . '/../api/preadvice_helper.php';
require_once __DIR__ . '/../api/oauth_helper.php';
ensure_integration_schema($pdo);

$oauth_clients = oauth_registered_clients();
$scope_map = oauth_event_scope_map();

// Hitung ringkasan awal dari database
$init_stats = [
    'outside_scheduled'  => (int)$pdo->query("SELECT COUNT(*) FROM preadvice WHERE status='SCHEDULED'")->fetchColumn(),
    'arrived'            => (int)$pdo->query("SELECT COUNT(*) FROM preadvice WHERE status='ARRIVED'")->fetchColumn(),
    'containers_in_yard' => (int)$pdo->query("SELECT COUNT(*) FROM containers WHERE status='in_yard'")->fetchColumn(),
    'events_logged'      => (int)$pdo->query("SELECT COUNT(*) FROM yard_events")->fetchColumn(),
    'dlq_open'           => (int)$pdo->query("SELECT COUNT(*) FROM integration_dlq WHERE status='QUARANTINED'")->fetchColumn(),
];

$recent_yard_events = $pdo->query("SELECT * FROM yard_events ORDER BY id DESC LIMIT 12")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="space-y-4">

    <!-- =================================================================== -->
    <!-- HEADER BANNER & PANDUAN ALUR DEMO 4 LANGKAH                         -->
    <!-- =================================================================== -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 text-slate-800 shadow-2xs border border-slate-200 border-l-4 border-l-orange-500">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-orange-50 text-orange-700 border border-orange-200">
                        <i class="fa-solid fa-network-wired mr-1"></i> Data Integration & OAuth 2.0 Hub
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Zero Re-Entry • Contract-First • HMAC-SHA256
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                        RFC 6749 & RFC 7519
                    </span>
                </div>
                <h1 class="text-lg sm:text-xl font-extrabold tracking-tight text-slate-900">
                    Pusat Kendali Integrasi Data, Sensor Hardware & Keamanan Payload OAuth 2.0
                </h1>
                <p class="text-xs text-slate-600 max-w-4xl leading-relaxed">
                    Seluruh visualisasi di aplikasi CIDP diambil murni dari proses database & ERP Odoo. Truk dan kontainer didaftarkan terlebih dahulu pada antrean <strong class="text-slate-800">Pre-Advice (berstatus di luar dry port)</strong>, lalu masuk ke dalam ekosistem terminal ketika <strong class="text-slate-800">sensor hardware (ANPR/OCR/RFID/Scanner)</strong> memicu event terautentikasi OAuth 2.0.
                </p>
            </div>

            <!-- Tombol Kontrol Cepat Demo -->
            <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                <a href="docs/PRD_CONCLUSION_INTERMODAL_DRY_PORT.md" target="_blank" class="px-3 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold shadow-2xs transition flex items-center gap-1.5 cursor-pointer" title="Buka Dokumen PRD v2.0 (Single Source of Truth Patokan Tim)">
                    <i class="fa-solid fa-file-contract text-orange-600"></i> PRD v2.0 (Patokan)
                </a>
                <a href="docs/KATALOG_50_KEGIATAN_OPERASIONAL_CIDP.md" target="_blank" class="px-3 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold shadow-2xs transition flex items-center gap-1.5 cursor-pointer" title="Buka Katalog 50 Kegiatan Operasional (ACT-01 s/d ACT-50)">
                    <i class="fa-solid fa-list-check text-sky-600"></i> Katalog 50 Kegiatan
                </a>
                <button type="button" onclick="openPreGateModal()" class="px-3.5 py-2 rounded-xl bg-orange-600 hover:bg-orange-500 text-white text-xs font-bold shadow-2xs transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-plus-circle"></i> Daftar Pre-Gate Baru
                </button>
                <button type="button" onclick="runAutoCycleNext()" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-2xs transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-play"></i> Siklus 1 Truk (Step 1–6)
                </button>
                <button type="button" onclick="resetDemoDatabase()" class="px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold shadow-2xs transition flex items-center gap-1.5 cursor-pointer" title="Kosongkan transaksi & kembalikan 30 truk ke status Di Luar Dry Port">
                    <i class="fa-solid fa-rotate-left"></i> Reset Demo ke 0
                </button>
            </div>
        </div>

        <!-- Alur Demo 4 Langkah (Menjawab "Mulai Demo dari Mana?") -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-2.5 mt-4 pt-3.5 border-t border-slate-100 text-xs">
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 flex items-start gap-2.5">
                <span class="w-6 h-6 rounded-lg bg-orange-500 text-white font-black flex items-center justify-center flex-shrink-0 text-[11px]">1</span>
                <div>
                    <div class="font-bold text-slate-900">Pre-Advice (Di Luar Dry Port)</div>
                    <p class="text-[11px] text-slate-600 mt-0.5">30 truk & kontainer siap di luar (<code class="text-orange-700 font-bold">SCHEDULED</code>). Yard & tagihan mulai dari 0.</p>
                </div>
            </div>
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 flex items-start gap-2.5">
                <span class="w-6 h-6 rounded-lg bg-sky-600 text-white font-black flex items-center justify-center flex-shrink-0 text-[11px]">2</span>
                <div>
                    <div class="font-bold text-slate-900">Trigger Sensor / Scan Barcode</div>
                    <p class="text-[11px] text-slate-600 mt-0.5">Klik <strong>Scan Gate-In</strong> pada truk atau jalankan Step 1–6 untuk memasukkan kontainer ke Yard.</p>
                </div>
            </div>
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 flex items-start gap-2.5">
                <span class="w-6 h-6 rounded-lg bg-teal-600 text-white font-black flex items-center justify-center flex-shrink-0 text-[11px]">3</span>
                <div>
                    <div class="font-bold text-slate-900">Aktivitas Multi-Zona Lapangan</div>
                    <p class="text-[11px] text-slate-600 mt-0.5">Uji Relokasi RTK, Stripping & Palet GS1 CFS, Suhu Reefer, X-Ray Pabean, & Kereta Api.</p>
                </div>
            </div>
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 flex items-start gap-2.5">
                <span class="w-6 h-6 rounded-lg bg-amber-600 text-white font-black flex items-center justify-center flex-shrink-0 text-[11px]">4</span>
                <div>
                    <div class="font-bold text-slate-900">Pembuktian OAuth 2.0, HMAC & DLQ</div>
                    <p class="text-[11px] text-slate-600 mt-0.5">Uji token JWT, serangan manipulasi payload (MITM), & pantau karantina error di tab DLQ.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- 5 KARTU KPI REAL-TIME EKOSISTEM INTEGRASI                           -->
    <!-- =================================================================== -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        <div class="bg-white rounded-xl p-3.5 border border-slate-200/90 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Di Luar (Pre-Advice)</div>
                <div id="kpiOutsideScheduled" class="text-2xl font-black text-slate-900 mt-0.5"><?= $init_stats['outside_scheduled'] ?></div>
                <div class="text-[11px] text-amber-600 font-semibold">Truk Terjadwal (EDI 940)</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center text-base">
                <i class="fa-solid fa-truck-clock"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl p-3.5 border border-slate-200/90 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Sudah Masuk Gate</div>
                <div id="kpiArrived" class="text-2xl font-black text-sky-700 mt-0.5"><?= $init_stats['arrived'] ?></div>
                <div class="text-[11px] text-sky-600 font-semibold">Terverifikasi ANPR/OCR</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-sky-50 border border-sky-200 text-sky-600 flex items-center justify-center text-base">
                <i class="fa-solid fa-archway"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl p-3.5 border border-slate-200/90 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Kontainer di Yard</div>
                <div id="kpiInYard" class="text-2xl font-black text-teal-700 mt-0.5"><?= $init_stats['containers_in_yard'] ?></div>
                <div class="text-[11px] text-teal-600 font-semibold">Aktif di Blok A–E / CFS</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-200 text-teal-600 flex items-center justify-center text-base">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl p-3.5 border border-slate-200/90 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Event Terintegrasi</div>
                <div id="kpiEventsLogged" class="text-2xl font-black text-indigo-700 mt-0.5"><?= $init_stats['events_logged'] ?></div>
                <div class="text-[11px] text-indigo-600 font-semibold">Jejak Audit yard_events</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-600 flex items-center justify-center text-base">
                <i class="fa-solid fa-bolt"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl p-3.5 border border-slate-200/90 shadow-2xs flex items-center justify-between col-span-2 sm:col-span-1">
            <div>
                <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Dead Letter Queue</div>
                <div id="kpiDlqOpen" class="text-2xl font-black <?= $init_stats['dlq_open'] > 0 ? 'text-rose-600' : 'text-emerald-600' ?> mt-0.5"><?= $init_stats['dlq_open'] ?></div>
                <div class="text-[11px] text-rose-600 font-semibold">Payload Ditolak / Karantina</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center text-base">
                <i class="fa-solid fa-shield-virus"></i>
            </div>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- NAVIGASI 4 TAB UTAMA                                                -->
    <!-- =================================================================== -->
    <div class="bg-white rounded-xl p-1.5 border border-slate-200/90 shadow-2xs flex flex-wrap gap-1.5">
        <button type="button" onclick="switchIntegrationTab('tab-simulator')" id="btn-tab-simulator" class="int-tab-btn px-3.5 py-2 rounded-lg text-xs font-extrabold transition flex items-center gap-2 bg-slate-900 text-white shadow-2xs cursor-pointer">
            <i class="fa-solid fa-satellite-dish text-orange-400"></i>
            <span>1. Pre-Advice & Trigger Sensor Lapangan</span>
        </button>
        <button type="button" onclick="switchIntegrationTab('tab-oauth')" id="btn-tab-oauth" class="int-tab-btn px-3.5 py-2 rounded-lg text-xs font-extrabold transition flex items-center gap-2 text-slate-600 hover:bg-slate-100 cursor-pointer">
            <i class="fa-solid fa-key text-indigo-500"></i>
            <span>2. OAuth 2.0 Server & Uji Keamanan Payload (Slide 7–8)</span>
        </button>
        <button type="button" onclick="switchIntegrationTab('tab-dlq')" id="btn-tab-dlq" class="int-tab-btn px-3.5 py-2 rounded-lg text-xs font-extrabold transition flex items-center gap-2 text-slate-600 hover:bg-slate-100 cursor-pointer">
            <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
            <span>3. Dead Letter Queue (DLQ) & Audit Trail (Slide 9–10)</span>
            <span id="tabDlqBadge" class="px-1.5 py-0.5 rounded-full text-[10px] bg-rose-100 text-rose-700 font-black"><?= $init_stats['dlq_open'] ?></span>
        </button>
        <button type="button" onclick="switchIntegrationTab('tab-odoo')" id="btn-tab-odoo" class="int-tab-btn px-3.5 py-2 rounded-lg text-xs font-extrabold transition flex items-center gap-2 text-slate-600 hover:bg-slate-100 cursor-pointer">
            <i class="fa-solid fa-cloud-arrow-up text-emerald-600"></i>
            <span>4. Sinkronisasi Cloud ERP Odoo JSON-RPC (Slide 6 & 12)</span>
        </button>
    </div>

    <!-- =================================================================== -->
    <!-- TAB 1: PRE-ADVICE & SIMULATOR SENSOR SEMUA AKTIVITAS LAPANGAN       -->
    <!-- =================================================================== -->
    <div id="tab-simulator" class="int-tab-pane space-y-4">

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">

            <!-- KOLOM KIRI (7 KOLOM): PANEL TRIGGER SENSOR 6-STEP & 9 AKTIVITAS LAPANGAN -->
            <div class="xl:col-span-7 space-y-4">

                <!-- A. Siklus End-to-End 6 Langkah Utama (11 Hardware Sensor Telemetry) -->
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-2.5">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-sky-600">A. Alur Utama End-to-End Handshake</span>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-orange-100 text-orange-800 border border-orange-200">11 SENSOR HARDWARE</span>
                            </div>
                            <h2 class="text-sm font-extrabold text-slate-900 mt-0.5">Trigger Sensor 6 Langkah Berurutan (Zero Re-Entry)</h2>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <a href="dashboard.php?page=scanner" target="_blank" class="px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold transition flex items-center gap-1 cursor-pointer" title="Uji pemindaian barcode ISO 6346 & QR tiket menggunakan kamera webcam/HP">
                                <i class="fa-solid fa-camera text-indigo-600"></i> Kamera Scanner (HW-02)
                            </a>
                            <button type="button" onclick="openHwModal()" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1 cursor-pointer" title="Lihat Matriks 11 Hardware Industri & Format JSON">
                                <i class="fa-solid fa-microchip text-slate-600"></i> Matriks 11 HW
                            </button>
                            <button type="button" onclick="runAutoCycleNext()" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                                <i class="fa-solid fa-bolt"></i> Auto-Run 6 Langkah
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        <!-- Step 1: HW-01 & HW-02 & HW-04 -->
                        <button type="button" onclick="triggerStep(1)" class="p-2.5 rounded-xl border border-slate-200 hover:border-sky-400 hover:bg-sky-50/50 text-left transition group cursor-pointer">
                            <div class="flex items-center justify-between">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-sky-100 text-sky-800">STEP 1</span>
                                <span class="text-[9px] font-black text-orange-600 bg-orange-50 px-1 py-0.5 rounded border border-orange-200">HW-01 • HW-02 • HW-04</span>
                            </div>
                            <div class="text-xs font-extrabold text-slate-800 mt-1.5 group-hover:text-sky-700">1. Gate-In ANPR/OCR/RFID</div>
                            <div class="text-[11px] text-slate-500">Kamera ANPR Hikvision + OCR ISO 6346 + e-Pass</div>
                        </button>

                        <!-- Step 2: HW-03 -->
                        <button type="button" onclick="triggerStep(2)" class="p-2.5 rounded-xl border border-slate-200 hover:border-sky-400 hover:bg-sky-50/50 text-left transition group cursor-pointer">
                            <div class="flex items-center justify-between">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-sky-100 text-sky-800">STEP 2</span>
                                <span class="text-[9px] font-black text-orange-600 bg-orange-50 px-1 py-0.5 rounded border border-orange-200">HW-03</span>
                            </div>
                            <div class="text-xs font-extrabold text-slate-800 mt-1.5 group-hover:text-sky-700">2. Jembatan Timbang 80T VGM</div>
                            <div class="text-[11px] text-slate-500">Fangda 80T Load Cell + SOLAS VGM Rp 75rb</div>
                        </button>

                        <!-- Step 3: HW-08 -->
                        <button type="button" onclick="triggerStep(3)" class="p-2.5 rounded-xl border border-slate-200 hover:border-teal-400 hover:bg-teal-50/50 text-left transition group cursor-pointer">
                            <div class="flex items-center justify-between">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-teal-100 text-teal-800">STEP 3</span>
                                <span class="text-[9px] font-black text-orange-600 bg-orange-50 px-1 py-0.5 rounded border border-orange-200">HW-08</span>
                            </div>
                            <div class="text-xs font-extrabold text-slate-800 mt-1.5 group-hover:text-teal-700">3. Lift-Off Spreader Twistlock</div>
                            <div class="text-[11px] text-slate-500">Bromma Spreader RS-02 + Invoice Rp 325rb</div>
                        </button>

                        <!-- Step 4: HW-07 -->
                        <button type="button" onclick="triggerStep(4)" class="p-2.5 rounded-xl border border-slate-200 hover:border-teal-400 hover:bg-teal-50/50 text-left transition group cursor-pointer">
                            <div class="flex items-center justify-between">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-teal-100 text-teal-800">STEP 4</span>
                                <span class="text-[9px] font-black text-orange-600 bg-orange-50 px-1 py-0.5 rounded border border-orange-200">HW-07</span>
                            </div>
                            <div class="text-xs font-extrabold text-slate-800 mt-1.5 group-hover:text-teal-700">4. Yard Stacking DGPS/RTK</div>
                            <div class="text-[11px] text-slate-500">CHCNAV RTK <2cm kunci Blok-Bay-Row-Tier</div>
                        </button>

                        <!-- Step 5: HW-11 -->
                        <button type="button" onclick="triggerStep(5)" class="p-2.5 rounded-xl border border-slate-200 hover:border-purple-400 hover:bg-purple-50/50 text-left transition group cursor-pointer">
                            <div class="flex items-center justify-between">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-purple-100 text-purple-800">STEP 5</span>
                                <span class="text-[9px] font-black text-orange-600 bg-orange-50 px-1 py-0.5 rounded border border-orange-200">HW-11</span>
                            </div>
                            <div class="text-xs font-extrabold text-slate-800 mt-1.5 group-hover:text-purple-700">5. Webhook E-Seal CEISA 4.0</div>
                            <div class="text-[11px] text-slate-500">Jointech JT701 GPS + Rilis SPPB otomatis</div>
                        </button>

                        <!-- Step 6: HW-05 & HW-06 -->
                        <button type="button" onclick="triggerStep(6)" class="p-2.5 rounded-xl border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50/50 text-left transition group cursor-pointer">
                            <div class="flex items-center justify-between">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-emerald-100 text-emerald-800">STEP 6</span>
                                <span class="text-[9px] font-black text-orange-600 bg-orange-50 px-1 py-0.5 rounded border border-orange-200">HW-05 • HW-06</span>
                            </div>
                            <div class="text-xs font-extrabold text-slate-800 mt-1.5 group-hover:text-emerald-700">6. Pelunasan & Edge Interlock</div>
                            <div class="text-[11px] text-slate-500">Advantech Edge PC + LED Chipshow + Gate-Out</div>
                        </button>
                    </div>
                </div>

                <!-- B. Aktivitas Operasional Lapangan Multi-Zona (Relokasi, CFS, Reefer, Bea Cukai, Kereta, Gate-Out) -->
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 space-y-3">
                    <div class="border-b border-slate-100 pb-2.5">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-orange-600">B. Aktivitas Lapangan Terintegrasi (Sensor Hardware -> Software -> DB)</span>
                        <h2 class="text-sm font-extrabold text-slate-900">Relokasi Yard, Gudang CFS, Cold Chain Reefer, Custom Clearance & Siding KA</h2>
                        <p class="text-[11px] text-slate-500">Klik aktivitas di bawah setelah ada minimal 1 kontainer di dalam Yard (atau pilih kontainer spesifik pada input opsional).</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200/80">
                        <label class="text-xs font-bold text-slate-600">Target Kontainer (Opsional):</label>
                        <input type="text" id="activityTargetContainer" placeholder="Otomatis pilih kontainer di yard (atau ketik mis. MSCU9180379)" class="flex-1 min-w-[220px] px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-mono bg-white">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        <!-- 1. Relokasi Yard: HW-07 & HW-08 -->
                        <button type="button" onclick="triggerActivity('relocate')" class="p-2.5 rounded-xl border border-slate-200 hover:border-teal-400 hover:bg-teal-50/50 text-left transition cursor-pointer">
                            <div class="flex items-center justify-between text-[10px]">
                                <span class="font-extrabold text-teal-700"><i class="fa-solid fa-arrows-up-down-left-right mr-1"></i> YMS • RTK GNSS</span>
                                <span class="text-[9px] font-black text-orange-600 bg-orange-50 px-1 py-0.5 rounded border border-orange-200">HW-07 • HW-08</span>
                            </div>
                            <div class="text-xs font-extrabold text-slate-800 mt-1">Relokasi / Shifting Slot (RTK)</div>
                            <div class="text-[11px] text-slate-500">CHCNAV GNSS + Twistlock Bromma (Rp 200rb)</div>
                        </button>

                        <!-- 2. CFS Stripping & Scan 3 Palet: HW-04 -->
                        <button type="button" onclick="triggerActivity('cfs_cycle')" class="p-2.5 rounded-xl border border-slate-200 hover:border-amber-400 hover:bg-amber-50/50 text-left transition cursor-pointer">
                            <div class="flex items-center justify-between text-[10px]">
                                <span class="font-extrabold text-amber-700"><i class="fa-solid fa-barcode mr-1"></i> WMS • GS1 SSCC-18</span>
                                <span class="text-[9px] font-black text-orange-600 bg-orange-50 px-1 py-0.5 rounded border border-orange-200">HW-04</span>
                            </div>
                            <div class="text-xs font-extrabold text-slate-800 mt-1">Stripping & Scan Palet CFS</div>
                            <div class="text-[11px] text-slate-500">Honeywell GS1 SSCC-18 + Modulo 10 Valid</div>
                        </button>

                        <!-- 3. CFS Dispatch Palet: HW-06 -->
                        <button type="button" onclick="triggerActivity('cfs_dispatch')" class="p-2.5 rounded-xl border border-slate-200 hover:border-amber-400 hover:bg-amber-50/50 text-left transition cursor-pointer">
                            <div class="flex items-center justify-between text-[10px]">
                                <span class="font-extrabold text-amber-700"><i class="fa-solid fa-dolly mr-1"></i> WMS • Forklift Gate</span>
                                <span class="text-[9px] font-black text-orange-600 bg-orange-50 px-1 py-0.5 rounded border border-orange-200">HW-06</span>
                            </div>
                            <div class="text-xs font-extrabold text-slate-800 mt-1">Dispatch Palet CFS Keluar</div>
                            <div class="text-[11px] text-slate-500">Advantech Edge PC Interlock Pabean</div>
                        </button>

                        <!-- 4. Bea Cukai X-Ray Anomaly: HW-11 -->
                        <button type="button" onclick="triggerActivity('customs_inspect', '&result=ANOMALY')" class="p-2.5 rounded-xl border border-slate-200 hover:border-rose-400 hover:bg-rose-50/50 text-left transition cursor-pointer">
                            <div class="flex items-center justify-between text-[10px]">
                                <span class="font-extrabold text-rose-700"><i class="fa-solid fa-x-ray mr-1"></i> DJBC • X-Ray 6MeV</span>
                                <span class="text-[9px] font-black text-orange-600 bg-orange-50 px-1 py-0.5 rounded border border-orange-200">HW-11</span>
                            </div>
                            <div class="text-xs font-extrabold text-slate-800 mt-1">X-Ray: Temuan Jalur Merah</div>
                            <div class="text-[11px] text-slate-500">Jointech E-Seal Alert + Box & CFS HOLD</div>
                        </button>

                        <!-- 5. Bea Cukai Rilis SPPB: HW-11 -->
                        <button type="button" onclick="triggerActivity('customs_release')" class="p-2.5 rounded-xl border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50/50 text-left transition cursor-pointer">
                            <div class="flex items-center justify-between text-[10px]">
                                <span class="font-extrabold text-emerald-700"><i class="fa-solid fa-file-shield mr-1"></i> DJBC • CEISA 4.0</span>
                                <span class="text-[9px] font-black text-orange-600 bg-orange-50 px-1 py-0.5 rounded border border-orange-200">HW-11</span>
                            </div>
                            <div class="text-xs font-extrabold text-slate-800 mt-1">Rilis SPPB (Buka Jalur Merah)</div>
                            <div class="text-[11px] text-slate-500">Jointech JT701 Unlocked + Buka HOLD</div>
                        </button>

                        <!-- 6. Cold Chain Plug-in: HW-09 -->
                        <button type="button" onclick="triggerActivity('reefer_plug')" class="p-2.5 rounded-xl border border-slate-200 hover:border-cyan-400 hover:bg-cyan-50/50 text-left transition cursor-pointer">
                            <div class="flex items-center justify-between text-[10px]">
                                <span class="font-extrabold text-cyan-700"><i class="fa-solid fa-plug-circle-bolt mr-1"></i> COLD • 380V IoT</span>
                                <span class="text-[9px] font-black text-orange-600 bg-orange-50 px-1 py-0.5 rounded border border-orange-200">HW-09</span>
                            </div>
                            <div class="text-xs font-extrabold text-slate-800 mt-1">Colok Soket Reefer (-18°C)</div>
                            <div class="text-[11px] text-slate-500">Cavotec 380V/32A + LoRaWAN SHT40</div>
                        </button>

                        <!-- 7. Cold Chain Telemetri Normal: HW-09 -->
                        <button type="button" onclick="triggerActivity('reefer_telemetry')" class="p-2.5 rounded-xl border border-slate-200 hover:border-cyan-400 hover:bg-cyan-50/50 text-left transition cursor-pointer">
                            <div class="flex items-center justify-between text-[10px]">
                                <span class="font-extrabold text-cyan-700"><i class="fa-solid fa-temperature-low mr-1"></i> COLD • LoRaWAN</span>
                                <span class="text-[9px] font-black text-orange-600 bg-orange-50 px-1 py-0.5 rounded border border-orange-200">HW-09</span>
                            </div>
                            <div class="text-xs font-extrabold text-slate-800 mt-1">Telemetri Reefer Normal</div>
                            <div class="text-[11px] text-slate-500">Suhu -18°C + Billing Listrik Rp 35rb/jam</div>
                        </button>

                        <!-- 8. Cold Chain Alarm Suhu: HW-09 -->
                        <button type="button" onclick="triggerActivity('reefer_telemetry', '&fault=1')" class="p-2.5 rounded-xl border border-slate-200 hover:border-rose-400 hover:bg-rose-50/50 text-left transition cursor-pointer">
                            <div class="flex items-center justify-between text-[10px]">
                                <span class="font-extrabold text-rose-600"><i class="fa-solid fa-bell mr-1"></i> COLD • Suhu Naik</span>
                                <span class="text-[9px] font-black text-orange-600 bg-orange-50 px-1 py-0.5 rounded border border-orange-200">HW-09</span>
                            </div>
                            <div class="text-xs font-extrabold text-slate-800 mt-1">Alarm Deviasi Suhu (-13.2°C)</div>
                            <div class="text-[11px] text-slate-500">Deviasi > 2°C memicu status ALARM HACCP</div>
                        </button>

                        <!-- 9. Rail Loading (HW-10) & Gate Out (HW-01, HW-05, HW-06) -->
                        <div class="grid grid-cols-2 gap-1.5">
                            <button type="button" onclick="triggerActivity('rail_load')" class="p-2 rounded-xl border border-slate-200 hover:border-indigo-400 hover:bg-indigo-50/50 text-left transition cursor-pointer">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-extrabold text-indigo-700"><i class="fa-solid fa-train"></i> KA Pasoso</span>
                                    <span class="text-[8px] font-black text-orange-600 bg-orange-50 px-1 py-0.2 rounded border border-orange-200">HW-10</span>
                                </div>
                                <div class="text-[11px] font-extrabold text-slate-800 mt-0.5">Muat Kereta Siding</div>
                                <div class="text-[10px] text-slate-500">Frauscher 126 Gandar</div>
                            </button>
                            <button type="button" onclick="triggerActivity('gate_out')" class="p-2 rounded-xl border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50/50 text-left transition cursor-pointer">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-extrabold text-emerald-700"><i class="fa-solid fa-truck-fast"></i> Gate-Out</span>
                                    <span class="text-[8px] font-black text-orange-600 bg-orange-50 px-1 py-0.2 rounded border border-orange-200">HW-05/06</span>
                                </div>
                                <div class="text-[11px] font-extrabold text-slate-800 mt-0.5">Rilis Keluar Tol</div>
                                <div class="text-[10px] text-slate-500">LED P6/P8 + Interlock</div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN (5 KOLOM): LIVE INSPECTOR KORELASI 3 ALIRAN & PAYLOAD OAUTH -->
            <div class="xl:col-span-5 flex flex-col gap-4">
                <div class="bg-white rounded-2xl border border-slate-200 p-4 text-slate-800 flex-1 flex flex-col justify-between shadow-2xs">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-800">Live Handshake & Tri-Flow Inspector</span>
                            </div>
                            <span id="consoleStatusBadge" class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-slate-100 text-slate-700 border border-slate-200">SIAP MENERIMA SENSOR</span>
                        </div>

                        <!-- Badge Bukti Verifikasi OAuth 2.0 & HMAC -->
                        <div id="oauthVerifiedCard" class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-700"><i class="fa-solid fa-shield-halved text-orange-600 mr-1"></i> Cryptographic Trust (OAuth 2.0 + HMAC)</span>
                                <span id="ovIntegrity" class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">STANDBY</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 pt-1 text-[11px]">
                                <div><span class="text-slate-500">Client ID:</span> <code id="ovClient" class="text-slate-900 font-bold">-</code></div>
                                <div><span class="text-slate-500">Scope Matched:</span> <code id="ovScope" class="text-orange-700 font-bold">-</code></div>
                            </div>
                        </div>

                        <!-- Korelasi 3 Aliran (Tri-Flow Correlation) -->
                        <div class="space-y-2 text-xs">
                            <div class="p-2.5 rounded-xl bg-sky-50/70 border border-sky-200">
                                <div class="text-[10px] font-extrabold uppercase text-sky-800"><i class="fa-solid fa-truck-ramp-box mr-1"></i> 1. Physical Flow (Aliran Fisik Lapangan)</div>
                                <div id="tfPhysical" class="text-slate-700 mt-0.5 text-[11.5px]">Belum ada sensor yang dipicu. Klik salah satu tombol sensor di sebelah kiri atau tombol Scan pada tabel di bawah.</div>
                            </div>
                            <div class="p-2.5 rounded-xl bg-orange-50/70 border border-orange-200">
                                <div class="text-[10px] font-extrabold uppercase text-orange-800"><i class="fa-solid fa-microchip mr-1"></i> 2. Information Flow (Aliran Informasi & Dokumen)</div>
                                <div id="tfInformation" class="text-slate-700 mt-0.5 text-[11.5px]">Menunggu pembacaan identitas dari pre-advice database.</div>
                            </div>
                            <div class="p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-200">
                                <div class="text-[10px] font-extrabold uppercase text-emerald-800"><i class="fa-solid fa-coins mr-1"></i> 3. Financial Flow (Aliran Keuangan & Billing ERP)</div>
                                <div id="tfFinancial" class="text-slate-700 mt-0.5 text-[11.5px]">Tagihan otomatis diakrual saat timbangan, lift-off, relokasi, CFS, atau reefer berjalan.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Raw JSON Response Output -->
                    <div class="mt-3 pt-3 border-t border-slate-100">
                        <div class="flex items-center justify-between text-[10px] text-slate-500 mb-1">
                            <span class="font-mono font-bold">JSON Response (api/ingest_event.php)</span>
                            <button type="button" onclick="copyConsoleJson()" class="hover:text-slate-900 cursor-pointer font-semibold"><i class="fa-regular fa-copy mr-1"></i>Salin JSON</button>
                        </div>
                        <pre id="liveJsonConsole" class="bg-slate-50 text-slate-800 font-mono text-[11px] p-3 rounded-xl border border-slate-200 overflow-x-auto max-h-48 leading-relaxed">{ "status": "READY", "hint": "Pilih truk dari tabel Pre-Advice atau klik tombol sensor." }</pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABEL ANTREAN PRE-ADVICE (TRUK & KONTAINER DI LUAR DRY PORT) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-600">Master Booking / EDI 940 Shipping Line</span>
                    <h2 class="text-sm font-extrabold text-slate-900">Daftar Antrean Pre-Advice (Truk & Kontainer Terjadwal di Luar Dry Port)</h2>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <select id="preadviceFilterStatus" onchange="loadPreadviceTable()" class="px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs font-bold bg-white">
                        <option value="ALL">Semua Status (Di Luar & Sudah Masuk)</option>
                        <option value="SCHEDULED" selected>SCHEDULED (Masih di Luar Dry Port)</option>
                        <option value="ARRIVED">ARRIVED (Sudah Masuk Gate)</option>
                    </select>
                    <input type="text" id="preadviceSearch" oninput="renderPreadviceFiltered()" placeholder="Cari plat / kontainer / DO..." class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs">
                    <button type="button" onclick="openPreGateModal()" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold cursor-pointer">
                        <i class="fa-solid fa-plus mr-1"></i> Daftar Pre-Gate
                    </button>
                    <button type="button" onclick="exportPreadviceJson()" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer" title="Unduh Manifest Pre-Advice (.json)">
                        <i class="fa-solid fa-download mr-1"></i> JSON
                    </button>
                    <button type="button" onclick="openImportJsonModal()" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer" title="Impor Pre-Advice dari JSON">
                        <i class="fa-solid fa-upload mr-1"></i> Import JSON
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 uppercase text-[10px] font-extrabold border-b border-slate-200">
                            <th class="py-2.5 px-3">Kode Pre-Gate</th>
                            <th class="py-2.5 px-3">Plat Truk & Supir</th>
                            <th class="py-2.5 px-3">No. Kontainer (ISO 6346)</th>
                            <th class="py-2.5 px-3">Tipe & Blok Tujuan</th>
                            <th class="py-2.5 px-3">Principal / DO</th>
                            <th class="py-2.5 px-3">Status Posisi</th>
                            <th class="py-2.5 px-3 text-right">Trigger Sensor / Barcode</th>
                        </tr>
                    </thead>
                    <tbody id="preadviceTableBody" class="divide-y divide-slate-100">
                        <tr><td colspan="7" class="py-6 text-center text-slate-400">Memuat data pre-advice...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- TAB 2: OAUTH 2.0 TOKEN SERVER & UJI KEAMANAN PAYLOAD (SLIDE 7-8)    -->
    <!-- =================================================================== -->
    <div id="tab-oauth" class="int-tab-pane hidden space-y-4">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">

            <!-- Kiri: Generator Bearer Token & Daftar Client OAuth 2.0 -->
            <div class="xl:col-span-6 bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 space-y-3">
                <div class="border-b border-slate-100 pb-2.5">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-600">Slide 7 • OAuth 2.0 Authorization Server (RFC 6749)</span>
                    <h2 class="text-sm font-extrabold text-slate-900">Penerbitan Short-Lived Bearer Token (JWT) & Granular Scopes</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Pilih Entitas Sistem Pengirim (`client_id`):</label>
                        <select id="oauthClientSelect" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold bg-white">
                            <?php foreach ($oauth_clients as $cid => $cl): ?>
                                <option value="<?= htmlspecialchars($cid) ?>"><?= htmlspecialchars($cid) ?> — <?= htmlspecialchars($cl['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="button" onclick="requestOAuthToken()" class="w-full py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-extrabold shadow-xs transition cursor-pointer">
                            <i class="fa-solid fa-key mr-1"></i> Minta Bearer Token
                        </button>
                    </div>
                </div>

                <div class="bg-slate-900 text-slate-100 rounded-xl p-3 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase text-indigo-300">Authorization Header (JWT HS256 • TTL 3600s)</span>
                        <span id="jwtExpiryBadge" class="text-[10px] font-mono text-emerald-400">Siap diterbitkan</span>
                    </div>
                    <textarea id="jwtTokenOutput" rows="3" readonly class="w-full bg-slate-950 text-amber-300 font-mono text-[11px] p-2 rounded-lg border border-slate-800 focus:outline-none" placeholder="Klik 'Minta Bearer Token' untuk menerbitkan JWT..."></textarea>
                    <div class="text-[10px] font-extrabold uppercase text-sky-300 pt-1">Decoded JWT Claims (Payload & Granular Scopes):</div>
                    <pre id="jwtClaimsOutput" class="bg-slate-950 text-sky-200 font-mono text-[11px] p-2.5 rounded-lg border border-slate-800 overflow-x-auto max-h-40">{ "info": "Pilih client_id lalu klik Minta Bearer Token" }</pre>
                </div>

                <!-- Tabel Matriks Client & Scope -->
                <div class="overflow-x-auto pt-1">
                    <table class="w-full text-left border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 uppercase text-[10px] font-extrabold border-b border-slate-200">
                                <th class="py-2 px-2.5">Client ID</th>
                                <th class="py-2 px-2.5">Perangkat / Sistem</th>
                                <th class="py-2 px-2.5">Granular Scopes Diizinkan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($oauth_clients as $cid => $cl): ?>
                                <tr>
                                    <td class="py-2 px-2.5 font-mono font-bold text-indigo-700"><?= htmlspecialchars($cid) ?></td>
                                    <td class="py-2 px-2.5 text-slate-700"><?= htmlspecialchars($cl['name']) ?></td>
                                    <td class="py-2 px-2.5">
                                        <div class="flex flex-wrap gap-1">
                                            <?php foreach ($cl['scopes'] as $sc): ?>
                                                <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 font-mono text-[10px]"><?= htmlspecialchars($sc) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Kanan: 5 Skenario Simulasi Serangan & Pertahanan Dosen (Slide 8) -->
            <div class="xl:col-span-6 bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 space-y-3 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="border-b border-slate-100 pb-2.5">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-600">Slide 8 • Contract-First Design & Cryptographic Trust</span>
                        <h2 class="text-sm font-extrabold text-slate-900">Laboratorium Uji Keamanan Payload & Pertahanan Sidang Dosen</h2>
                        <p class="text-[11px] text-slate-500">Klik skenario di bawah untuk membuktikan bagaimana API Gateway menolak manipulasi payload (MITM), pelanggaran wewenang scope, dan duplikasi tagihan.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <button type="button" onclick="runSecurityScenario('tamper')" class="p-3 rounded-xl border border-rose-200 bg-rose-50/50 hover:bg-rose-50 text-left transition cursor-pointer">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-rose-600 text-white">SKENARIO 1 • HTTP 401</span>
                                <i class="fa-solid fa-user-secret text-rose-600"></i>
                            </div>
                            <div class="text-xs font-extrabold text-slate-900 mt-1.5">Serangan Manipulasi Payload (MITM)</div>
                            <p class="text-[11px] text-slate-600 mt-0.5">Tanda tangan HMAC dibuat untuk berat <code>34.850 kg</code>, lalu diubah menjadi <code>20.000 kg</code> di tengah jalan. Sistem wajib menolak & memasukkan ke DLQ.</p>
                        </button>

                        <button type="button" onclick="runSecurityScenario('scope')" class="p-3 rounded-xl border border-amber-200 bg-amber-50/50 hover:bg-amber-50 text-left transition cursor-pointer">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-amber-600 text-white">SKENARIO 2 • HTTP 403</span>
                                <i class="fa-solid fa-ban text-amber-600"></i>
                            </div>
                            <div class="text-xs font-extrabold text-slate-900 mt-1.5">Pelanggaran Scope (Least Privilege)</div>
                            <p class="text-[11px] text-slate-600 mt-0.5">Token komputer gerbang (<code>cidp-edge-gate-01</code>) mencoba memalsukan status lunas <code>PAYMENT_SETTLED</code>. Ditolak karena tanpa scope <code>billing:settle</code>.</p>
                        </button>

                        <button type="button" onclick="runSecurityScenario('unregistered')" class="p-3 rounded-xl border border-purple-200 bg-purple-50/50 hover:bg-purple-50 text-left transition cursor-pointer">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-purple-600 text-white">SKENARIO 3 • HTTP 422</span>
                                <i class="fa-solid fa-truck-Burst text-purple-600"></i>
                            </div>
                            <div class="text-xs font-extrabold text-slate-900 mt-1.5">Truk Tanpa Pre-Advice (Ilegal)</div>
                            <p class="text-[11px] text-slate-600 mt-0.5">Kamera membaca truk <code>B 0000 XXX</code> yang tidak ada di manifest Pre-Advice. Palang tetap terkunci & masuk karantina DLQ.</p>
                        </button>

                        <button type="button" onclick="runSecurityScenario('idempotency')" class="p-3 rounded-xl border border-emerald-200 bg-emerald-50/50 hover:bg-emerald-50 text-left transition cursor-pointer">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-600 text-white">SKENARIO 4 • IDEMPOTENT</span>
                                <i class="fa-solid fa-repeat text-emerald-600"></i>
                            </div>
                            <div class="text-xs font-extrabold text-slate-900 mt-1.5">Uji Idempotency (Zero Double-Billing)</div>
                            <p class="text-[11px] text-slate-600 mt-0.5">Mengirim ulang paket event dengan <code>event_id</code> yang sama 2 kali akibat sinyal berkedip. Tembakan ke-2 dikenali sebagai duplikat.</p>
                        </button>
                    </div>
                </div>

                <div class="bg-slate-900 rounded-xl p-3 text-xs space-y-1.5 mt-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase text-amber-300">Hasil Verifikasi Gateway (`api/ingest_event.php`)</span>
                        <span id="secTestStatus" class="text-[10px] font-mono text-slate-400">Pilih salah satu skenario di atas</span>
                    </div>
                    <pre id="secTestOutput" class="bg-slate-950 text-emerald-300 font-mono text-[11px] p-3 rounded-lg border border-slate-800 overflow-x-auto max-h-56">{ "ready": true }</pre>
                </div>
            </div>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- TAB 3: DEAD LETTER QUEUE (DLQ) & JEJAK AUDIT YARD_EVENTS             -->
    <!-- =================================================================== -->
    <div id="tab-dlq" class="int-tab-pane hidden space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-2.5">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-600">Slide 9 & 10 • Pisahkan Error, Pertahankan Alur Utama</span>
                    <h2 class="text-sm font-extrabold text-slate-900">Karantina Dead Letter Queue (`integration_dlq`) — Zero Head-of-Line Blocking</h2>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="runSecurityScenario('tamper')" class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold cursor-pointer">
                        <i class="fa-solid fa-bug mr-1"></i> Simulasikan Error ke DLQ
                    </button>
                    <button type="button" onclick="loadDlqTable()" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer">
                        <i class="fa-solid fa-rotate mr-1"></i> Muat Ulang DLQ
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 uppercase text-[10px] font-extrabold border-b border-slate-200">
                            <th class="py-2.5 px-3">ID / Waktu</th>
                            <th class="py-2.5 px-3">Event ID & Tipe</th>
                            <th class="py-2.5 px-3">Perangkat Sumber</th>
                            <th class="py-2.5 px-3">Alasan Penolakan / Root Cause</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3 text-right">Tindakan QA</th>
                        </tr>
                    </thead>
                    <tbody id="dlqTableBody" class="divide-y divide-slate-100">
                        <tr><td colspan="6" class="py-6 text-center text-slate-400">Memuat antrean DLQ...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- TAB 4: LIVE CLOUD ERP ODOO JSON-RPC 2.0 SYNC                        -->
    <!-- =================================================================== -->
    <div id="tab-odoo" class="int-tab-pane hidden space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-2.5">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600">Slide 6 & 12 • JSON-RPC 2.0 Over HTTPS</span>
                    <h2 class="text-sm font-extrabold text-slate-900">Konektor Live Cloud ERP Odoo (`conclusion-intermodal-dry-port.odoo.com`)</h2>
                    <p class="text-[11px] text-slate-500">Menguji koneksi autentikasi UID, menarik faktur `account.move`, dan menyinkronkan invoice hasil transaksi sensor lapangan ke Odoo Cloud.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" onclick="callOdooApi('test_connection')" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold cursor-pointer">
                        <i class="fa-solid fa-plug mr-1"></i> Test Connection Odoo
                    </button>
                    <button type="button" onclick="callOdooApi('get_invoices')" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold cursor-pointer">
                        <i class="fa-solid fa-file-invoice-dollar mr-1"></i> Tarik Invoice Odoo (`account.move`)
                    </button>
                    <button type="button" onclick="callOdooApi('sync_all_invoices')" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold cursor-pointer">
                        <i class="fa-solid fa-cloud-arrow-up mr-1"></i> Sync Semua Invoice DB ke Odoo
                    </button>
                </div>
            </div>

            <pre id="odooJsonOutput" class="bg-slate-950 text-emerald-300 font-mono text-xs p-4 rounded-xl border border-slate-800 overflow-x-auto max-h-96">{ "hint": "Klik salah satu tombol di atas untuk berkomunikasi langsung dengan server Cloud Odoo." }</pre>
        </div>
    </div>

</div>

<!-- ======================================================================= -->
<!-- MODAL 1: DAFTAR PRE-GATE BARU (TRUK & KONTAINER DI LUAR DRY PORT)       -->
<!-- ======================================================================= -->
<div id="preGateModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-5 shadow-xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <span class="text-[10px] font-extrabold uppercase text-indigo-600">Pre-Advice / EDI 940 Booking</span>
                <h3 class="text-base font-extrabold text-slate-900">Daftarkan Truk & Kontainer Baru (Status Di Luar)</h3>
            </div>
            <button type="button" onclick="closePreGateModal()" class="text-slate-400 hover:text-slate-700 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>

        <form id="preGateForm" onsubmit="submitPreGateForm(event)" class="space-y-3 text-xs">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nomor Plat Truk *</label>
                    <input type="text" name="truck_plate" id="pgPlate" required placeholder="B 9421 KFA" class="w-full px-3 py-2 rounded-lg border border-slate-300 font-mono uppercase">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="font-bold text-slate-700">No. Kontainer (ISO 6346) *</label>
                        <button type="button" onclick="generateValidIsoContainer()" class="text-[10px] font-extrabold text-indigo-600 hover:underline cursor-pointer">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Buat Otomatis
                        </button>
                    </div>
                    <input type="text" name="container_number" id="pgContainer" required placeholder="MSCU9182379" class="w-full px-3 py-2 rounded-lg border border-slate-300 font-mono uppercase">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Supir</label>
                    <input type="text" name="driver_name" value="Hendra Wijaya" class="w-full px-3 py-2 rounded-lg border border-slate-300">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Perusahaan Angkutan</label>
                    <input type="text" name="company" value="PT Cikarang Trans Logistik" class="w-full px-3 py-2 rounded-lg border border-slate-300">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tipe Kargo</label>
                    <select name="cargo_type" id="pgCargoType" onchange="if(this.value==='reefer') document.getElementById('pgBlock').value='REEFER';" class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white">
                        <option value="dry">Dry Container (General)</option>
                        <option value="reefer">Reefer Cold Chain (-18°C)</option>
                        <option value="dg">Dangerous Goods (IMO/DG)</option>
                        <option value="empty">Empty Container</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Blok Yard Tujuan</label>
                    <select name="target_block" id="pgBlock" class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white">
                        <option value="A">Blok A (Import Stacking)</option>
                        <option value="B" selected>Blok B (Main Stacking)</option>
                        <option value="C">Blok C (Export / Intermodal)</option>
                        <option value="REEFER">Blok REEFER (Cold Chain)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Principal / Pemilik</label>
                    <input type="text" name="owner_company" value="Maersk Line" class="w-full px-3 py-2 rounded-lg border border-slate-300">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nomor Delivery Order (DO)</label>
                    <input type="text" name="do_number" value="DO-<?= date('Y') ?>-<?= rand(8000,9999) ?>" class="w-full px-3 py-2 rounded-lg border border-slate-300 font-mono">
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closePreGateModal()" class="px-3.5 py-2 rounded-lg bg-slate-100 text-slate-700 font-bold cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold cursor-pointer">
                    <i class="fa-solid fa-check mr-1"></i> Simpan ke Pre-Advice (Di Luar)
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================================= -->
<!-- MODAL 2: TIKET BARCODE / QR PRE-GATE & SIMULASI SCANNER OPTIK           -->
<!-- ======================================================================= -->
<div id="barcodeTicketModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-5 shadow-xl border border-slate-200 space-y-4 text-center">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5 text-left">
            <div>
                <span class="text-[10px] font-extrabold uppercase text-sky-600">e-Ticket Pre-Gate Pass (AIDC Optical / RFID)</span>
                <h3 id="btCode" class="text-base font-black text-slate-900 font-mono">PG-261025-001</h3>
            </div>
            <button type="button" onclick="document.getElementById('barcodeTicketModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>

        <!-- Visual Barcode Garis -->
        <div class="bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl p-4 space-y-2">
            <div class="h-14 w-full flex items-center justify-center gap-[2px] overflow-hidden px-4" id="btBars"></div>
            <div id="btBarcodeText" class="font-mono text-sm font-black tracking-[0.25em] text-slate-900"></div>
            <div id="btRfidText" class="text-[10px] font-mono text-slate-500"></div>
        </div>

        <div class="grid grid-cols-2 gap-2 text-left text-xs bg-slate-50 p-3 rounded-xl border border-slate-200">
            <div><span class="text-slate-400 block text-[10px]">Plat Truk:</span><strong id="btPlate" class="font-mono"></strong></div>
            <div><span class="text-slate-400 block text-[10px]">No. Kontainer:</span><strong id="btContainer" class="font-mono text-indigo-700"></strong></div>
            <div><span class="text-slate-400 block text-[10px]">Supir / Ekspedisi:</span><strong id="btDriver"></strong></div>
            <div><span class="text-slate-400 block text-[10px]">Tipe & Blok Tujuan:</span><strong id="btBlock"></strong></div>
        </div>

        <div class="flex items-center gap-2 pt-1">
            <button type="button" id="btTriggerGateBtn" class="flex-1 py-2.5 px-3 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-xs font-extrabold cursor-pointer">
                <i class="fa-solid fa-camera mr-1"></i> Scan Barcode Ini (Masuk Gate-In)
            </button>
            <button type="button" id="btTriggerAutoBtn" class="flex-1 py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-extrabold cursor-pointer">
                <i class="fa-solid fa-bolt mr-1"></i> Scan + Auto 6-Step
            </button>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- MODAL 4: MATRIKS 11 HARDWARE TELEMETRI & JSON SIMULATOR                -->
<!-- ======================================================================= -->
<div id="hwModal" class="hidden fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-4xl w-full p-5 shadow-2xl border border-slate-200 space-y-4 max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 flex-shrink-0">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-orange-100 text-orange-800 border border-orange-200">STANDAR KELOMPOK 7 & ITL TRISAKTI</span>
                    <span class="text-xs font-mono text-slate-400">11 SENSOR HARDWARE</span>
                </div>
                <h3 class="text-base font-extrabold text-slate-900 mt-0.5">Matriks 11 Hardware Telemetri, Integrasi JSON &amp; Finansial</h3>
            </div>
            <button type="button" onclick="closeHwModal()" class="text-slate-400 hover:text-slate-700 p-1 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>

        <div class="overflow-y-auto flex-1 pr-1 space-y-3">
            <p class="text-xs text-slate-600 leading-relaxed">
                Di lapangan nyata, perangkat keras mendeteksi objek fisik dan mengirimkan paket telemetri terautentikasi ke sistem. Khusus <strong>HW-02</strong> dapat diuji langsung menggunakan <strong>kamera webcam</strong> di modul Scanner, sedangkan 10 alat lainnya disimulasikan menggunakan <strong>JSON Payload signed OAuth 2.0 &amp; HMAC-SHA256</strong>:
            </p>

            <div class="border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-[10px] font-extrabold uppercase text-slate-500 tracking-wider">
                        <tr>
                            <th class="p-2.5">Kode</th>
                            <th class="p-2.5">Perangkat &amp; Model Industri</th>
                            <th class="p-2.5">Pemicu Fisik / Sensor</th>
                            <th class="p-2.5">Dampak Finansial &amp; ERP</th>
                            <th class="p-2.5 text-right">Aksi Demo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        <!-- HW-01 -->
                        <tr class="hover:bg-slate-50/80">
                            <td class="p-2.5 font-black text-orange-600 font-mono">HW-01</td>
                            <td class="p-2.5"><strong>Kamera ANPR Gate</strong><br><span class="text-[11px] text-slate-400">Hikvision DS-TCG406-E</span></td>
                            <td class="p-2.5">Inductive Loop Detector baca plat truk</td>
                            <td class="p-2.5 text-emerald-700 font-bold">Pas Masuk Rp 50.000</td>
                            <td class="p-2.5 text-right">
                                <button type="button" onclick="closeHwModal(); triggerStep(1);" class="px-2 py-1 rounded bg-sky-600 hover:bg-sky-500 text-white text-[10px] font-bold cursor-pointer">Kirim JSON</button>
                            </td>
                        </tr>
                        <!-- HW-02 -->
                        <tr class="hover:bg-indigo-50/50 bg-indigo-50/20">
                            <td class="p-2.5 font-black text-indigo-600 font-mono">HW-02</td>
                            <td class="p-2.5"><strong>Portal OCR &amp; Scanner ISO 6346</strong><br><span class="text-[11px] text-indigo-600 font-semibold">Hikvision iDS-TCV300 / Webcam</span></td>
                            <td class="p-2.5">Pemindaian optik no. kontainer (Kamera Live)</td>
                            <td class="p-2.5 text-slate-600">Free Time 3 Hari &bull; Rp 180rb/hari</td>
                            <td class="p-2.5 text-right flex items-center justify-end gap-1">
                                <a href="dashboard.php?page=scanner" target="_blank" class="px-2 py-1 rounded bg-indigo-600 hover:bg-indigo-500 text-white text-[10px] font-bold cursor-pointer flex items-center gap-1">
                                    <i class="fa-solid fa-camera"></i> Kamera
                                </a>
                                <button type="button" onclick="closeHwModal(); triggerStep(1);" class="px-2 py-1 rounded bg-slate-200 hover:bg-slate-300 text-slate-700 text-[10px] font-bold cursor-pointer">JSON</button>
                            </td>
                        </tr>
                        <!-- HW-03 -->
                        <tr class="hover:bg-slate-50/80">
                            <td class="p-2.5 font-black text-orange-600 font-mono">HW-03</td>
                            <td class="p-2.5"><strong>Jembatan Timbang 80T VGM</strong><br><span class="text-[11px] text-slate-400">Fangda Electronic Load Cell 80 Ton</span></td>
                            <td class="p-2.5">Platform timbangan digital 5 detik</td>
                            <td class="p-2.5 text-emerald-700 font-bold">VGM SOLAS Rp 75.000</td>
                            <td class="p-2.5 text-right">
                                <button type="button" onclick="closeHwModal(); triggerStep(2);" class="px-2 py-1 rounded bg-sky-600 hover:bg-sky-500 text-white text-[10px] font-bold cursor-pointer">Kirim JSON</button>
                            </td>
                        </tr>
                        <!-- HW-04 -->
                        <tr class="hover:bg-slate-50/80">
                            <td class="p-2.5 font-black text-orange-600 font-mono">HW-04</td>
                            <td class="p-2.5"><strong>Long-Range UHF RFID Reader</strong><br><span class="text-[11px] text-slate-400">EPC Gen2 ISO 18000-6C (860–960MHz)</span></td>
                            <td class="p-2.5">Antena 20m baca e-Pass Driver / Tag Box</td>
                            <td class="p-2.5 text-slate-600">Auto-Debit Akun Deposit Trucking</td>
                            <td class="p-2.5 text-right">
                                <button type="button" onclick="closeHwModal(); triggerActivity('cfs_cycle');" class="px-2 py-1 rounded bg-amber-600 hover:bg-amber-500 text-white text-[10px] font-bold cursor-pointer">Kirim JSON</button>
                            </td>
                        </tr>
                        <!-- HW-05 -->
                        <tr class="hover:bg-slate-50/80">
                            <td class="p-2.5 font-black text-orange-600 font-mono">HW-05</td>
                            <td class="p-2.5"><strong>LED Information Display Gate</strong><br><span class="text-[11px] text-slate-400">Chipshow Outdoor P6/P8 Industrial</span></td>
                            <td class="p-2.5">Display instruksi visual blok/slot driver</td>
                            <td class="p-2.5 text-slate-600">Reduksi TAT Gerbang 45m &rarr; 18m</td>
                            <td class="p-2.5 text-right">
                                <button type="button" onclick="closeHwModal(); triggerStep(6);" class="px-2 py-1 rounded bg-slate-700 hover:bg-slate-600 text-white text-[10px] font-bold cursor-pointer">Status LED</button>
                            </td>
                        </tr>
                        <!-- HW-06 -->
                        <tr class="hover:bg-slate-50/80">
                            <td class="p-2.5 font-black text-orange-600 font-mono">HW-06</td>
                            <td class="p-2.5"><strong>Industrial Fanless Edge AI PC</strong><br><span class="text-[11px] text-slate-400">Advantech ARK-3532 / Neousys Nuvo-9000</span></td>
                            <td class="p-2.5">Interlocking PLC Barrier &amp; Edge Token</td>
                            <td class="p-2.5 text-slate-600">Zero Idle Downtime (Offline Buffer)</td>
                            <td class="p-2.5 text-right">
                                <button type="button" onclick="closeHwModal(); triggerStep(6);" class="px-2 py-1 rounded bg-emerald-700 hover:bg-emerald-600 text-white text-[10px] font-bold cursor-pointer">Interlock</button>
                            </td>
                        </tr>
                        <!-- HW-07 -->
                        <tr class="hover:bg-slate-50/80">
                            <td class="p-2.5 font-black text-orange-600 font-mono">HW-07</td>
                            <td class="p-2.5"><strong>DGPS / RTK GNSS Receiver</strong><br><span class="text-[11px] text-slate-400">CHCNAV CGI-610 Dual Antenna (&lt;2cm)</span></td>
                            <td class="p-2.5">Kordinat 3D Bay-Row-Tier kabin Reach Stacker</td>
                            <td class="p-2.5 text-slate-600">Meniadakan Biaya Misplaced Box</td>
                            <td class="p-2.5 text-right">
                                <button type="button" onclick="closeHwModal(); triggerStep(4);" class="px-2 py-1 rounded bg-teal-600 hover:bg-teal-500 text-white text-[10px] font-bold cursor-pointer">Kirim JSON</button>
                            </td>
                        </tr>
                        <!-- HW-08 -->
                        <tr class="hover:bg-slate-50/80">
                            <td class="p-2.5 font-black text-orange-600 font-mono">HW-08</td>
                            <td class="p-2.5"><strong>Spreader Twistlock &amp; Load Cell Kit</strong><br><span class="text-[11px] text-slate-400">Bromma SmartSpreader / Kalmar DRG450</span></td>
                            <td class="p-2.5">Pin twistlock putar 90&deg; &amp; angkat peti kemas</td>
                            <td class="p-2.5 text-emerald-700 font-bold">Jasa Lift-Off Rp 250.000</td>
                            <td class="p-2.5 text-right">
                                <button type="button" onclick="closeHwModal(); triggerStep(3);" class="px-2 py-1 rounded bg-teal-600 hover:bg-teal-500 text-white text-[10px] font-bold cursor-pointer">Kirim JSON</button>
                            </td>
                        </tr>
                        <!-- HW-09 -->
                        <tr class="hover:bg-slate-50/80">
                            <td class="p-2.5 font-black text-orange-600 font-mono">HW-09</td>
                            <td class="p-2.5"><strong>Smart Reefer Socket 380V &amp; LoRaWAN</strong><br><span class="text-[11px] text-slate-400">Cavotec/Marechal 380V/32A + Sensirion SHT40</span></td>
                            <td class="p-2.5">Colok daya 380V, arus kW/kWh, suhu HACCP</td>
                            <td class="p-2.5 text-emerald-700 font-bold">Plugging Fee Rp 35.000/jam</td>
                            <td class="p-2.5 text-right">
                                <button type="button" onclick="closeHwModal(); triggerActivity('reefer_plug');" class="px-2 py-1 rounded bg-cyan-600 hover:bg-cyan-500 text-white text-[10px] font-bold cursor-pointer">Kirim JSON</button>
                            </td>
                        </tr>
                        <!-- HW-10 -->
                        <tr class="hover:bg-slate-50/80">
                            <td class="p-2.5 font-black text-orange-600 font-mono">HW-10</td>
                            <td class="p-2.5"><strong>Rail Trackside Axle Counter</strong><br><span class="text-[11px] text-slate-400">Frauscher RSR123 Wheel Sensor (126 Gandar)</span></td>
                            <td class="p-2.5">Deteksi pelek roda lokomotif &amp; gerbong datar</td>
                            <td class="p-2.5 text-emerald-700 font-bold">Freight KA Rp 1.850.000/TEU</td>
                            <td class="p-2.5 text-right">
                                <button type="button" onclick="closeHwModal(); triggerActivity('rail_load');" class="px-2 py-1 rounded bg-indigo-700 hover:bg-indigo-600 text-white text-[10px] font-bold cursor-pointer">Kirim JSON</button>
                            </td>
                        </tr>
                        <!-- HW-11 -->
                        <tr class="hover:bg-slate-50/80">
                            <td class="p-2.5 font-black text-orange-600 font-mono">HW-11</td>
                            <td class="p-2.5"><strong>Electronic Cargo Smart Seal (E-Seal)</strong><br><span class="text-[11px] text-slate-400">Jointech JT701 GPS/GSM Pabean CEISA 4.0</span></td>
                            <td class="p-2.5">Kawat segel anti-tamper &amp; rilis pabean SPPB</td>
                            <td class="p-2.5 text-slate-600">Pelepasan Customs Bond Miliaran</td>
                            <td class="p-2.5 text-right">
                                <button type="button" onclick="closeHwModal(); triggerStep(5);" class="px-2 py-1 rounded bg-purple-700 hover:bg-purple-600 text-white text-[10px] font-bold cursor-pointer">Kirim JSON</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-slate-100 pt-3 flex-shrink-0">
            <button type="button" onclick="closeHwModal()" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold cursor-pointer">Tutup Matriks</button>
        </div>
    </div>
</div>
<div id="importJsonModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-5 shadow-xl border border-slate-200 space-y-3">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
            <h3 class="text-sm font-extrabold text-slate-900">Import Pre-Advice Manifest (JSON)</h3>
            <button type="button" onclick="document.getElementById('importJsonModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <p class="text-xs text-slate-500">Tempelkan JSON array berisi <code>truck_plate</code> dan <code>container_number</code> (wajib lolos check digit ISO 6346 Modulo 11):</p>
        <textarea id="importJsonTextarea" rows="7" class="w-full font-mono text-xs p-3 rounded-xl border border-slate-300 bg-slate-50"></textarea>
        <div class="flex justify-end gap-2">
            <button type="button" onclick="document.getElementById('importJsonModal').classList.add('hidden')" class="px-3 py-1.5 rounded-lg bg-slate-100 text-xs font-bold cursor-pointer">Batal</button>
            <button type="button" onclick="submitImportJson()" class="px-4 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-bold cursor-pointer">Import Sekarang</button>
        </div>
    </div>
</div>

<script>
let allPreadviceRows = [];

function openHwModal() {
    document.getElementById('hwModal')?.classList.remove('hidden');
}
function closeHwModal() {
    document.getElementById('hwModal')?.classList.add('hidden');
}

function switchIntegrationTab(tabId) {
    document.querySelectorAll('.int-tab-pane').forEach(el => el.classList.add('hidden'));
    document.getElementById(tabId)?.classList.remove('hidden');
    document.querySelectorAll('.int-tab-btn').forEach(btn => {
        btn.classList.remove('bg-orange-600', 'text-white', 'shadow-2xs');
        btn.classList.add('text-slate-600', 'hover:bg-slate-100');
    });
    const activeBtn = document.getElementById('btn-' + tabId);
    if (activeBtn) {
        activeBtn.classList.remove('text-slate-600', 'hover:bg-slate-100');
        activeBtn.classList.add('bg-orange-600', 'text-white', 'shadow-2xs');
    }
    if (tabId === 'tab-dlq') loadDlqTable();
}

function updateLiveConsole(data, label = 'DONE') {
    const badge = document.getElementById('consoleStatusBadge');
    const pre = document.getElementById('liveJsonConsole');
    pre.textContent = JSON.stringify(data, null, 2);

    // Cari objek result yang memiliki tri_flow atau oauth_verified
    let resObj = data?.result || data;
    if (Array.isArray(data?.steps) && data.steps.length > 0) {
        const lastStep = data.steps[data.steps.length - 1];
        resObj = lastStep?.result || lastStep;
    }

    const ok = Boolean(data?.success && (resObj?.success !== false));
    badge.textContent = ok ? (label + ' • HTTP 200 OK') : (label + ' • DITOLAK KE DLQ');
    badge.className = ok
        ? 'px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200'
        : 'px-2 py-0.5 rounded text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200';

    if (resObj?.oauth_verified) {
        document.getElementById('ovClient').textContent = resObj.oauth_verified.client_id || '-';
        document.getElementById('ovScope').textContent = resObj.oauth_verified.scope_matched || '-';
        document.getElementById('ovIntegrity').textContent = resObj.oauth_verified.integrity_check || 'VALID';
        document.getElementById('ovIntegrity').className = 'px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200';
    } else if (resObj?.oauth_error) {
        document.getElementById('ovIntegrity').textContent = resObj.oauth_error.toUpperCase();
        document.getElementById('ovIntegrity').className = 'px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-rose-50 text-rose-700 border border-rose-200';
    }

    if (resObj?.tri_flow) {
        document.getElementById('tfPhysical').textContent = resObj.tri_flow.physical || '-';
        document.getElementById('tfInformation').textContent = resObj.tri_flow.information || '-';
        document.getElementById('tfFinancial').textContent = resObj.tri_flow.financial || '-';
    } else if (resObj?.error || data?.error) {
        document.getElementById('tfPhysical').textContent = 'Transaksi dihentikan oleh penjaga gerbang API (Rollback).';
        document.getElementById('tfInformation').textContent = resObj?.error || data?.error;
        document.getElementById('tfFinancial').textContent = '0 tagihan dibuat; pesan dialihkan ke Dead Letter Queue (DLQ).';
    }
}

async function refreshIntegrationStats() {
    try {
        const r = await fetch('api/demo_control.php?action=status');
        const d = await r.json();
        if (d.success) {
            document.getElementById('kpiOutsideScheduled').textContent = d.outside_scheduled;
            document.getElementById('kpiArrived').textContent = d.arrived;
            document.getElementById('kpiInYard').textContent = d.containers_in_yard;
            document.getElementById('kpiEventsLogged').textContent = d.events_logged;
            document.getElementById('kpiDlqOpen').textContent = d.dlq_open;
            document.getElementById('tabDlqBadge').textContent = d.dlq_open;
        }
    } catch (e) {}
}

async function loadPreadviceTable() {
    const st = document.getElementById('preadviceFilterStatus').value;
    try {
        const r = await fetch('api/demo_control.php?action=list&status=' + encodeURIComponent(st));
        const d = await r.json();
        allPreadviceRows = d.data || [];
        renderPreadviceFiltered();
    } catch (e) {}
}

function renderPreadviceFiltered() {
    const q = (document.getElementById('preadviceSearch').value || '').toLowerCase().trim();
    const tbody = document.getElementById('preadviceTableBody');
    const rows = allPreadviceRows.filter(r => {
        if (!q) return true;
        return (r.pre_gate_code || '').toLowerCase().includes(q)
            || (r.truck_plate || '').toLowerCase().includes(q)
            || (r.container_number || '').toLowerCase().includes(q)
            || (r.driver_name || '').toLowerCase().includes(q)
            || (r.owner_company || '').toLowerCase().includes(q);
    });

    if (rows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="py-6 text-center text-slate-400">Tidak ada data pre-advice yang cocok.</td></tr>';
        return;
    }

    tbody.innerHTML = rows.map(r => {
        const isSched = r.status === 'SCHEDULED';
        const cargoColors = {
            dry: 'bg-slate-100 text-slate-700',
            reefer: 'bg-cyan-50 text-cyan-700 border border-cyan-200',
            dg: 'bg-rose-50 text-rose-700 border border-rose-200',
            empty: 'bg-amber-50 text-amber-700'
        };
        const cBadge = cargoColors[r.cargo_type] || cargoColors.dry;
        const rowJson = encodeURIComponent(JSON.stringify(r));

        return `<tr class="hover:bg-slate-50/80 transition">
            <td class="py-2.5 px-3 font-mono font-bold text-slate-800">${r.pre_gate_code}</td>
            <td class="py-2.5 px-3">
                <div class="font-mono font-extrabold text-slate-900">${r.truck_plate}</div>
                <div class="text-[11px] text-slate-500">${r.driver_name || '-'} • ${r.company || ''}</div>
            </td>
            <td class="py-2.5 px-3">
                <span class="font-mono font-extrabold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200/70">${r.container_number}</span>
                <div class="text-[10px] text-slate-400 mt-0.5">${r.size_type || '40FT HC'} (${r.iso_code || '42G1'})</div>
            </td>
            <td class="py-2.5 px-3">
                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase ${cBadge}">${r.cargo_type}</span>
                <span class="ml-1 text-[11px] font-bold text-slate-600">→ Blok ${r.target_block || 'B'}</span>
            </td>
            <td class="py-2.5 px-3">
                <div class="font-semibold text-slate-800">${r.owner_company || '-'}</div>
                <div class="text-[10px] font-mono text-slate-400">${r.do_number || ''} • ${r.source || 'EDI-940'}</div>
            </td>
            <td class="py-2.5 px-3">
                ${isSched
                    ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>DI LUAR DRY PORT</span>'
                    : '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>SUDAH MASUK GATE</span>'
                }
            </td>
            <td class="py-2.5 px-3 text-right whitespace-nowrap space-x-1">
                <button type="button" onclick="openBarcodeTicket('${rowJson}')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold cursor-pointer" title="Lihat Barcode / QR Pre-Gate">
                    <i class="fa-solid fa-barcode"></i> Barcode
                </button>
                ${isSched ? `
                    <button type="button" onclick="triggerStep(1, '${r.pre_gate_code}')" class="px-2.5 py-1 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-[11px] font-bold cursor-pointer" title="Sensor ANPR/OCR membaca truk ini masuk Gate">
                        <i class="fa-solid fa-archway mr-0.5"></i> Scan Gate-In
                    </button>
                    <button type="button" onclick="runAutoCycleForCode('${r.pre_gate_code}')" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-bold cursor-pointer" title="Jalankan penuh Step 1 s/d Step 6 untuk truk ini">
                        <i class="fa-solid fa-bolt"></i> 6-Step
                    </button>
                ` : `
                    <button type="button" onclick="selectContainerForActivity('${r.container_number}')" class="px-2.5 py-1 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 text-[11px] font-bold cursor-pointer">
                        <i class="fa-solid fa-crosshairs mr-0.5"></i> Pilih Aktivitas
                    </button>
                `}
            </td>
        </tr>`;
    }).join('');
}

function selectContainerForActivity(cn) {
    const inp = document.getElementById('activityTargetContainer');
    inp.value = cn;
    inp.scrollIntoView({ behavior: 'smooth', block: 'center' });
    inp.classList.add('ring-2', 'ring-teal-500');
    setTimeout(() => inp.classList.remove('ring-2', 'ring-teal-500'), 1800);
}

async function triggerStep(stepId, code = '') {
    let url = `api/edge_simulator.php?action=step&step_id=${stepId}`;
    if (code) url += `&code=${encodeURIComponent(code)}`;
    const r = await fetch(url);
    const d = await r.json();
    updateLiveConsole(d, `STEP ${stepId}`);
    await refreshIntegrationStats();
    await loadPreadviceTable();
}

async function runAutoCycleNext() {
    const r = await fetch('api/edge_simulator.php?action=auto_cycle');
    const d = await r.json();
    updateLiveConsole(d, 'AUTO 6-STEP');
    await refreshIntegrationStats();
    await loadPreadviceTable();
}

async function runAutoCycleForCode(code) {
    const r = await fetch('api/edge_simulator.php?action=auto_cycle&code=' + encodeURIComponent(code));
    const d = await r.json();
    updateLiveConsole(d, `AUTO 6-STEP (${code})`);
    await refreshIntegrationStats();
    await loadPreadviceTable();
}

async function triggerActivity(type, extraQuery = '') {
    const cn = (document.getElementById('activityTargetContainer').value || '').trim();
    let url = `api/edge_simulator.php?action=activity&type=${encodeURIComponent(type)}${extraQuery}`;
    if (cn) url += `&container=${encodeURIComponent(cn)}`;
    const r = await fetch(url);
    const d = await r.json();
    updateLiveConsole(d, `ACTIVITY: ${type.toUpperCase()}`);
    await refreshIntegrationStats();
    await loadPreadviceTable();
}

async function resetDemoDatabase() {
    if (!confirm('Reset seluruh transaksi & kembalikan 30 truk + kontainer ke status DI LUAR DRY PORT (yard = 0)?')) return;
    const r = await fetch('api/demo_control.php?action=reset');
    const d = await r.json();
    document.getElementById('activityTargetContainer').value = '';
    updateLiveConsole(d, 'DEMO RESET');
    await refreshIntegrationStats();
    await loadPreadviceTable();
    await loadDlqTable();
}

// OAuth 2.0 & Security Scenarios
async function requestOAuthToken() {
    const cid = document.getElementById('oauthClientSelect').value;
    const r = await fetch('api/oauth_server.php?action=token', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ grant_type: 'client_credentials', client_id: cid })
    });
    const d = await r.json();
    if (d.success) {
        document.getElementById('jwtTokenOutput').value = 'Bearer ' + d.access_token;
        document.getElementById('jwtExpiryBadge').textContent = 'AKTIF • Exp: ' + d.expires_in + ' dtk (' + d.client_id + ')';
        document.getElementById('jwtClaimsOutput').textContent = JSON.stringify({
            client_id: d.client_id,
            client_name: d.client_name,
            granted_scopes: d.granted_scopes,
            issued_at: d.issued_at,
            expires_at: d.expires_at,
            jwt_claims: d.claims
        }, null, 2);
    }
}

async function runSecurityScenario(kind) {
    const out = document.getElementById('secTestOutput');
    const st = document.getElementById('secTestStatus');
    let d = {};
    if (kind === 'tamper') {
        const r = await fetch('api/edge_simulator.php?action=tamper_test');
        d = await r.json();
        st.textContent = 'DITOLAK • HTTP 401 HMAC_SIGNATURE_MISMATCH → DLQ';
        st.className = 'text-[10px] font-mono text-rose-400 font-bold';
    } else if (kind === 'scope') {
        const r = await fetch('api/edge_simulator.php?action=scope_violation_test');
        d = await r.json();
        st.textContent = 'DITOLAK • HTTP 403 INSUFFICIENT_SCOPE → DLQ';
        st.className = 'text-[10px] font-mono text-amber-400 font-bold';
    } else if (kind === 'unregistered') {
        const r = await fetch('api/edge_simulator.php?action=send_unregistered');
        d = await r.json();
        st.textContent = 'DITOLAK • HTTP 422 UNREGISTERED TRUCK → DLQ';
        st.className = 'text-[10px] font-mono text-purple-400 font-bold';
    } else if (kind === 'idempotency') {
        const evId = 'IDEMP-TEST-' + Date.now();
        const body = {
            event_id: evId,
            event_type: 'EQUIPMENT_MOVE',
            payload: { equipment_id: 'RS-01', gps_x: 210, gps_y: 190, fuel_percent: 85, status: 'operating' }
        };
        const r1 = await (await fetch('api/ingest_event.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(body) })).json();
        const r2 = await (await fetch('api/ingest_event.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(body) })).json();
        d = { event_id_tested: evId, first_send: r1, second_send_replay: r2 };
        st.textContent = 'TERBUKTI • Tembakan ke-2 duplicate=' + String(r2.duplicate);
        st.className = 'text-[10px] font-mono text-emerald-400 font-bold';
    }
    out.textContent = JSON.stringify(d, null, 2);
    updateLiveConsole(d, 'SECURITY TEST');
    await refreshIntegrationStats();
    await loadDlqTable();
}

// Dead Letter Queue
async function loadDlqTable() {
    const tbody = document.getElementById('dlqTableBody');
    try {
        const r = await fetch('api/demo_control.php?action=dlq');
        const d = await r.json();
        const rows = d.data || [];
        if (rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="py-6 text-center text-emerald-600 font-bold"><i class="fa-solid fa-circle-check mr-1"></i> Tidak ada error di antrean Dead Letter Queue (0 Quarantined).</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(x => `
            <tr class="hover:bg-slate-50">
                <td class="py-2.5 px-3 font-mono text-[11px] text-slate-600">#${x.id}<div class="text-[10px] text-slate-400">${x.created_at}</div></td>
                <td class="py-2.5 px-3"><div class="font-mono font-bold text-slate-900">${x.event_type || '-'}</div><div class="text-[10px] font-mono text-slate-400">${x.event_id || ''}</div></td>
                <td class="py-2.5 px-3 font-mono text-xs text-slate-700">${x.source_device || '-'}</td>
                <td class="py-2.5 px-3 text-rose-700 font-semibold max-w-md">${x.reason}</td>
                <td class="py-2.5 px-3">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold ${x.status === 'QUARANTINED' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'}">${x.status}</span>
                </td>
                <td class="py-2.5 px-3 text-right space-x-1">
                    ${x.status === 'QUARANTINED' ? `
                        <button type="button" onclick="resolveDlq(${x.id}, 'dlq_resolve')" class="px-2 py-1 rounded bg-emerald-600 text-white text-[10px] font-bold cursor-pointer">Resolve</button>
                        <button type="button" onclick="resolveDlq(${x.id}, 'dlq_discard')" class="px-2 py-1 rounded bg-slate-200 text-slate-700 text-[10px] font-bold cursor-pointer">Discard</button>
                    ` : '<span class="text-[10px] text-slate-400">Selesai</span>'}
                </td>
            </tr>
        `).join('');
    } catch (e) {}
}

async function resolveDlq(id, act) {
    await fetch(`api/demo_control.php?action=${act}&id=${id}`);
    await refreshIntegrationStats();
    await loadDlqTable();
}

// Pre-Gate Modal & ISO 6346 Generator
function openPreGateModal() {
    generateValidIsoContainer();
    document.getElementById('preGateModal').classList.remove('hidden');
}
function closePreGateModal() {
    document.getElementById('preGateModal').classList.add('hidden');
}
async function generateValidIsoContainer() {
    const r = await fetch('api/demo_control.php?action=generate_iso&prefix=MSCU');
    const d = await r.json();
    if (d.container_number) document.getElementById('pgContainer').value = d.container_number;
}
async function submitPreGateForm(e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    const obj = Object.fromEntries(fd.entries());
    const r = await fetch('api/demo_control.php?action=register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(obj)
    });
    const d = await r.json();
    if (!d.success) {
        alert(d.error || 'Gagal mendaftarkan pre-gate.');
        return;
    }
    closePreGateModal();
    updateLiveConsole(d, 'PRE-GATE REGISTERED');
    await refreshIntegrationStats();
    await loadPreadviceTable();
}

// Barcode Ticket Modal
function openBarcodeTicket(encodedJson) {
    const r = JSON.parse(decodeURIComponent(encodedJson));
    document.getElementById('btCode').textContent = r.pre_gate_code;
    document.getElementById('btBarcodeText').textContent = r.container_number;
    document.getElementById('btRfidText').textContent = 'UHF RFID: ' + (r.rfid_tag || '-');
    document.getElementById('btPlate').textContent = r.truck_plate;
    document.getElementById('btContainer').textContent = r.container_number;
    document.getElementById('btDriver').textContent = (r.driver_name || '-') + ' (' + (r.company || '') + ')';
    document.getElementById('btBlock').textContent = (r.cargo_type || 'dry').toUpperCase() + ' → Blok ' + (r.target_block || 'B');

    // Render decorative barcode bars
    const barsEl = document.getElementById('btBars');
    let barsHtml = '';
    const seedStr = r.pre_gate_code + r.container_number;
    for (let i = 0; i < 46; i++) {
        const w = ((seedStr.charCodeAt(i % seedStr.length) + i) % 3) + 1;
        barsHtml += `<span class="bg-slate-900 h-full inline-block" style="width:${w * 2}px"></span>`;
    }
    barsEl.innerHTML = barsHtml;

    document.getElementById('btTriggerGateBtn').onclick = () => {
        document.getElementById('barcodeTicketModal').classList.add('hidden');
        triggerStep(1, r.pre_gate_code);
    };
    document.getElementById('btTriggerAutoBtn').onclick = () => {
        document.getElementById('barcodeTicketModal').classList.add('hidden');
        runAutoCycleForCode(r.pre_gate_code);
    };
    document.getElementById('barcodeTicketModal').classList.remove('hidden');
}

// Export & Import JSON
function exportPreadviceJson() {
    window.open('api/demo_control.php?action=export_json', '_blank');
}
async function openImportJsonModal() {
    const r = await fetch('api/demo_control.php?action=generate_iso&prefix=CMAU');
    const d = await r.json();
    const sample = [
        {
            truck_plate: "B 9771 UTX",
            container_number: d.container_number || "CMAU9180379",
            driver_name: "Wahyu Setiawan",
            company: "PT Nusantara Intermodal",
            cargo_type: "dry",
            owner_company: "CMA CGM",
            target_block: "C"
        }
    ];
    document.getElementById('importJsonTextarea').value = JSON.stringify(sample, null, 2);
    document.getElementById('importJsonModal').classList.remove('hidden');
}
async function submitImportJson() {
    const raw = document.getElementById('importJsonTextarea').value;
    const r = await fetch('api/demo_control.php?action=import_json', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: raw
    });
    const d = await r.json();
    if (!d.success) {
        alert(d.error || 'Format JSON tidak valid');
        return;
    }
    document.getElementById('importJsonModal').classList.add('hidden');
    updateLiveConsole(d, 'JSON IMPORTED');
    await refreshIntegrationStats();
    await loadPreadviceTable();
}

// Odoo Cloud JSON-RPC
async function callOdooApi(act) {
    const out = document.getElementById('odooJsonOutput');
    out.textContent = 'Menghubungi https://conclusion-intermodal-dry-port.odoo.com via JSON-RPC 2.0...';
    const r = await fetch('api/odoo_client.php?action=' + encodeURIComponent(act));
    const d = await r.json();
    out.textContent = JSON.stringify(d, null, 2);
}

function copyConsoleJson() {
    navigator.clipboard.writeText(document.getElementById('liveJsonConsole').textContent || '');
}

document.addEventListener('DOMContentLoaded', () => {
    loadPreadviceTable();
    requestOAuthToken();
});
</script>
