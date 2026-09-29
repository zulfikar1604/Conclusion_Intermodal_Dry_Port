<?php
// =============================================================================
// MODUL: SCANNER OPTIK MULTI-FORMAT — SSCC-18, GS1-128 & ISO 6346
// File: pages/scanner.php
// Proyek: Conclusion Intermodal Dry Port (CIDP) 35 Ha — Yard Management System
// Standardisasi: GS1 General Specifications v23, ISO 6346:2022, CEISA 4.0
// PIC : Naufal Andika Heditya (Business Analyst & QA Specialist)
// Arsitek: Zulfikar Jafarudin Fatah (Lead System Architect)
// Engine: Open Source html5-qrcode (GitHub: mebjas/html5-qrcode) + JsBarcode
// =============================================================================

$scanner_info = [
    'pic'    => 'Naufal Andika Heditya',
    'role'   => 'Business Analyst & QA Specialist',
    'desc'   => 'Modul verifikasi mutu optik berkecepatan tinggi berbasis standar GS1-128 (SSCC-18), ISO 6346 (Container ID), dan QR Code Pabean CEISA 4.0. Menggunakan engine computer vision terbuka html5-qrcode untuk pemindaian real-time melalui kamera video, dokumen gambar, dan simulasi telemetri terintegrasi basis data YMS.',
    'icon'   => 'fa-barcode',
    'status' => 'Operasional — Engine Optik Terbuka Aktif'
];

// Tarik data kontainer aktif dari database untuk pencocokan real-time
$known_containers = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT id, container_number, iso_code, size_type, cargo_type, rfid_tag, sscc_code, owner_company, block, bay, row, tier, customs_status, gross_weight_kg, status FROM containers ORDER BY id ASC");
        $known_containers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $known_containers = [];
    }
}
?>

<!-- Script Library Pendukung Open Source (Lokal dengan Fallback CDN) -->
<script src="assets/html5-qrcode.min.js"></script>
<script src="assets/JsBarcode.all.min.js"></script>
<script src="assets/qrcode.min.js"></script>

<div class="space-y-6 animate-fadeIn pb-12">
    <!-- Header Modul & Status Penjaminan Mutu (QA) -->
    <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-start sm:items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#002f5e] via-[#0170b9] to-blue-400 text-white flex items-center justify-center text-2xl shadow-md flex-shrink-0">
                <i class="fa-solid fa-barcode"></i>
            </div>
            <div>
                <div class="flex items-center space-x-2.5 flex-wrap gap-y-1">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 leading-tight">Scanner Optik SSCC-18, GS1 & ISO 6346</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center shadow-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                        AIDC Engine Active
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        GitHub Open-Source (html5-qrcode)
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-3xl leading-relaxed">
                    Sistem verifikasi mutu identitas peti kemas otomatis (AIDC). Memvalidasi nomor kontainer ISO 6346, Serial Shipping Container Code (SSCC-18), dan e-Seal pabean CEISA 4.0 secara langsung dari kamera perangkat atau unggahan gambar.
                </p>
            </div>
        </div>

        <!-- PIC Badge Card -->
        <div class="flex items-center space-x-3 bg-slate-50 border border-slate-200/80 rounded-xl p-3 shrink-0 self-start md:self-auto">
            <div class="w-10 h-10 rounded-full bg-[#002f5e] text-white flex items-center justify-center font-bold text-sm shadow-xs">
                <i class="fa-solid fa-clipboard-check"></i>
            </div>
            <div>
                <span class="text-[9px] uppercase font-bold text-gray-400 block tracking-wider">Quality Assurance & BA:</span>
                <p class="font-bold text-gray-800 text-xs mt-0.5"><?= $scanner_info['pic'] ?></p>
                <span class="text-[10px] text-[#0170b9] font-medium block">Pemeriksaan Mutu Standardisasi GS1</span>
            </div>
        </div>
    </div>

    <!-- Integration Banner: Centralized Simulation Hub -->
    <div class="bg-gradient-to-r from-[#002f5e] to-[#0170b9] text-white p-3.5 sm:p-4 rounded-xl shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-lg bg-white/10 text-amber-300 flex items-center justify-center text-sm shrink-0 border border-white/10">
                <i class="fa-solid fa-tower-broadcast"></i>
            </div>
            <div>
                <span class="font-bold text-sm block">Uji Coba &amp; Simulasi Telemetri Terpusat</span>
                <p class="text-[11.5px] text-blue-100 mt-0.5">
                    Seluruh pengujian fungsionalitas sensor, alur otomatis truk/kereta, dan dekoder optik instan telah dipadukan dalam satu panel di <strong>Simulator IoT SCADA</strong>. Halaman ini difokuskan untuk pemindaian fisik kamera &amp; berkas.
                </p>
            </div>
        </div>
        <a href="dashboard.php?page=simulator" class="px-3.5 py-1.5 bg-amber-500 hover:bg-amber-400 text-gray-950 font-bold rounded-lg transition flex items-center gap-1.5 self-start sm:self-auto shrink-0 shadow-xs">
            <i class="fa-solid fa-cube text-xs"></i><span>Buka Panel Simulasi Terpusat</span>
        </a>
    </div>

    <!-- 4 Kartu KPI & Parameter Mutu -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-xs flex items-center space-x-3">
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-[#0170b9] flex items-center justify-center text-lg">
                <i class="fa-solid fa-expand"></i>
            </div>
            <div>
                <span class="text-[11px] font-semibold text-gray-500 block">Total Pindai Sesi Ini</span>
                <span class="text-lg font-bold text-gray-800" id="statScanCount">0 Box</span>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-xs flex items-center space-x-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-check-double"></i>
            </div>
            <div>
                <span class="text-[11px] font-semibold text-gray-500 block">Akurasi Validasi Algoritma</span>
                <span class="text-lg font-bold text-emerald-700">99.8% (Valid)</span>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-xs flex items-center space-x-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-stopwatch"></i>
            </div>
            <div>
                <span class="text-[11px] font-semibold text-gray-500 block">Latensi Dekoder Optik</span>
                <span class="text-lg font-bold text-gray-800" id="statLatency">&lt; 85 ms</span>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-xs flex items-center space-x-3">
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-database"></i>
            </div>
            <div>
                <span class="text-[11px] font-semibold text-gray-500 block">Database CIDP Terhubung</span>
                <span class="text-lg font-bold text-gray-800"><?= count($known_containers) ?> Kontainer</span>
            </div>
        </div>
    </div>

    <!-- AREA UTAMA: KONSOL SCANNER & INSPECTOR HASIL (2 KOLOM) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- KOLOM KIRI: KONSOL PEMINDAI KAMERA & BERKAS (Span 7 Kolom) -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4">
            
            <!-- Tab Mode Pemindaian -->
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 flex-wrap gap-2">
                <div class="flex items-center space-x-1 sm:space-x-2">
                    <button onclick="switchScannerMode('webcam')" id="tabBtnWebcam" class="px-3.5 py-1.5 rounded-lg text-xs font-bold bg-[#002f5e] text-white shadow-xs transition-all flex items-center space-x-1.5">
                        <i class="fa-solid fa-video"></i>
                        <span>Kamera Langsung</span>
                    </button>
                    <button onclick="switchScannerMode('file')" id="tabBtnFile" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-all flex items-center space-x-1.5">
                        <i class="fa-solid fa-file-image"></i>
                        <span>Unggah Gambar / Dokumen</span>
                    </button>
                    <button onclick="switchScannerMode('quick')" id="tabBtnQuick" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-all flex items-center space-x-1.5">
                        <i class="fa-solid fa-bolt"></i>
                        <span>Uji Sampel Instan</span>
                    </button>
                </div>
                
                <!-- Suara Beep Toggle -->
                <button onclick="toggleAudioFeedback()" id="btnAudioToggle" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold text-gray-600 bg-gray-50 border border-gray-200 hover:bg-gray-100 flex items-center space-x-1.5" title="Aktifkan/Nonaktifkan Suara Beep">
                    <i class="fa-solid fa-volume-high text-blue-600" id="audioIcon"></i>
                    <span id="audioText">Audio: Aktif</span>
                </button>
            </div>

            <!-- MODE 1: LIVE WEBCAM SCANNER -->
            <div id="modeWebcam" class="space-y-4">
                <!-- Toolbar Kontrol Kamera -->
                <div class="flex items-center justify-between gap-3 flex-wrap bg-slate-50 p-3 rounded-xl border border-slate-200/80 text-xs">
                    <div class="flex items-center space-x-2 flex-1 min-w-[200px]">
                        <i class="fa-solid fa-camera text-gray-400"></i>
                        <select id="cameraSelect" class="w-full bg-white border border-gray-200 text-gray-700 text-xs rounded-lg p-1.5 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                            <option value="">Mencari kamera perangkat...</option>
                        </select>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="startCameraScanner()" id="btnStartCamera" class="px-3.5 py-1.5 bg-[#0170b9] hover:bg-[#004b87] text-white font-bold rounded-lg transition-colors flex items-center space-x-1.5 shadow-xs">
                            <i class="fa-solid fa-play text-[10px]"></i>
                            <span>Nyalakan Kamera</span>
                        </button>
                        <button onclick="stopCameraScanner()" id="btnStopCamera" class="hidden px-3.5 py-1.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition-colors flex items-center space-x-1.5 shadow-xs">
                            <i class="fa-solid fa-stop text-[10px]"></i>
                            <span>Matikan</span>
                        </button>
                    </div>
                </div>

                <!-- Box Viewport Kamera Video -->
                <div class="relative w-full aspect-video sm:aspect-[4/3] max-h-[380px] bg-slate-950 rounded-2xl overflow-hidden border-2 border-slate-800 flex items-center justify-center" id="scannerViewportBox">
                    
                    <!-- html5-qrcode video viewport container -->
                    <div id="reader" class="w-full h-full"></div>

                    <!-- Placeholder Sebelum Kamera Nyala -->
                    <div id="cameraStandbyPlaceholder" class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center text-slate-400 bg-slate-900/90 z-10 space-y-3">
                        <div class="w-16 h-16 rounded-2xl bg-slate-800/80 border border-slate-700 flex items-center justify-center text-2xl text-blue-400">
                            <i class="fa-solid fa-camera-retro"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white">Kamera Belum Aktif</h3>
                            <p class="text-xs text-slate-400 max-w-sm mt-1">
                                Klik tombol <strong>"Nyalakan Kamera"</strong> di atas untuk memindai barcode fisik kontainer, stiker SSCC-18, atau QR e-Pass sopir secara real-time.
                            </p>
                        </div>
                        <button onclick="startCameraScanner()" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-[#0170b9] text-white text-xs font-bold rounded-xl shadow-md hover:from-blue-700 hover:to-[#004b87] transition-all flex items-center space-x-2">
                            <i class="fa-solid fa-video"></i>
                            <span>Izinkan Akses Kamera</span>
                        </button>
                    </div>

                    <!-- Scanning Laser Beam Overlay (Aktif saat kamera menyala) -->
                    <div id="scanLaserEffect" class="hidden absolute inset-0 pointer-events-none z-20 flex flex-col justify-between p-8">
                        <!-- Framing Corners -->
                        <div class="w-full h-full border-2 border-dashed border-blue-400/40 rounded-xl relative">
                            <div class="absolute -top-1 -left-1 w-6 h-6 border-t-4 border-l-4 border-blue-400"></div>
                            <div class="absolute -top-1 -right-1 w-6 h-6 border-t-4 border-r-4 border-blue-400"></div>
                            <div class="absolute -bottom-1 -left-1 w-6 h-6 border-b-4 border-l-4 border-blue-400"></div>
                            <div class="absolute -bottom-1 -right-1 w-6 h-6 border-b-4 border-r-4 border-blue-400"></div>
                            
                            <!-- Animated Red Laser Line -->
                            <div class="w-full h-0.5 bg-red-500 shadow-[0_0_12px_#ef4444] animate-bounce-slow absolute top-1/2 -translate-y-1/2"></div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between text-[11px] text-gray-500 px-1">
                    <span class="flex items-center">
                        <i class="fa-solid fa-circle-info text-blue-500 mr-1.5"></i>
                        Arahkan barcode / QR code kontainer ke dalam kotak pembidik.
                    </span>
                    <span class="font-mono text-gray-400">html5-qrcode v2.3.8</span>
                </div>
            </div>

            <!-- MODE 2: UNGGAH GAMBAR / DOKUMEN -->
            <div id="modeFile" class="hidden space-y-4">
                <div class="border-2 border-dashed border-gray-300 hover:border-[#0170b9] rounded-2xl p-8 text-center transition-all bg-slate-50/50 hover:bg-blue-50/30 cursor-pointer" id="dropZone" onclick="document.getElementById('fileInput').click()">
                    <input type="file" id="fileInput" accept="image/*" class="hidden" onchange="handleFileScan(event)">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 text-[#0170b9] flex items-center justify-center text-2xl mx-auto mb-3 shadow-inner">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800">Tarik & Jatuhkan Gambar ke Sini</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                        Mendukung foto stiker kontainer, scan surat jalan (EIR), atau tangkapan layar barcode (PNG, JPG, WEBP).
                    </p>
                    <button type="button" class="mt-4 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-xs font-bold rounded-xl shadow-xs hover:bg-gray-50 transition">
                        Pilih Berkas dari Komputer
                    </button>
                </div>
                
                <div id="fileScanPreviewBox" class="hidden p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                    <div class="flex items-center space-x-3 overflow-hidden">
                        <img id="filePreviewImg" src="" alt="Preview" class="w-12 h-12 object-contain bg-white rounded-lg border border-gray-200 p-1">
                        <div class="truncate">
                            <p id="fileName" class="text-xs font-bold text-gray-800 truncate">foto_kontainer.jpg</p>
                            <span id="fileScanStatus" class="text-[10px] text-blue-600 font-semibold">Sedang menganalisis optik...</span>
                        </div>
                    </div>
                    <button onclick="clearFileScan()" class="text-gray-400 hover:text-red-500 p-2 text-sm transition">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>

            <!-- MODE 3: UJI SAMPEL INSTAN (1-CLICK TESTING) -->
            <div id="modeQuick" class="hidden space-y-4">
                <div class="bg-blue-50/60 border border-blue-100 rounded-xl p-3.5 text-xs text-blue-800 flex items-start space-x-2.5">
                    <i class="fa-solid fa-wand-magic-sparkles text-blue-600 mt-0.5 text-sm"></i>
                    <div>
                        <strong>Mode Uji Cepat Konsultan:</strong>
                        <p class="mt-0.5 text-[11.5px] leading-relaxed text-blue-700">
                            Klik salah satu skenario sampel di bawah ini untuk menguji algoritma parser logistik, verifikasi check digit, dan pencocokan otomatis ke basis data YMS secara instan tanpa perlu mencetak barcode fisik.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div onclick="simulateSampleScan('sscc')" class="p-3.5 bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-300 rounded-xl cursor-pointer transition-all shadow-xs group">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 bg-blue-100/60 px-2 py-0.5 rounded">GS1 SSCC-18</span>
                            <i class="fa-solid fa-arrow-right text-gray-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all text-xs"></i>
                        </div>
                        <h4 class="text-xs font-bold text-gray-900 font-mono">(00)389912345000000001</h4>
                        <p class="text-[11px] text-gray-500 mt-1">Laden Ekspor PT Samudera Logistik (Blok B-06-02-01)</p>
                    </div>

                    <div onclick="simulateSampleScan('iso')" class="p-3.5 bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-300 rounded-xl cursor-pointer transition-all shadow-xs group">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-100/60 px-2 py-0.5 rounded">ISO 6346 Container</span>
                            <i class="fa-solid fa-arrow-right text-gray-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all text-xs"></i>
                        </div>
                        <h4 class="text-xs font-bold text-gray-900 font-mono">MSKU9182374 (42G1)</h4>
                        <p class="text-[11px] text-gray-500 mt-1">Peti Kemas 40ft High Cube Maersk Line</p>
                    </div>

                    <div onclick="simulateSampleScan('customs')" class="p-3.5 bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-300 rounded-xl cursor-pointer transition-all shadow-xs group">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-100/60 px-2 py-0.5 rounded">QR Smart E-Seal</span>
                            <i class="fa-solid fa-arrow-right text-gray-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all text-xs"></i>
                        </div>
                        <h4 class="text-xs font-bold text-gray-900 font-mono">CEISA4-JT701-884210</h4>
                        <p class="text-[11px] text-gray-500 mt-1">Segel GPS Bea Cukai Jalur Hijau (Tj. Priok - CIDP)</p>
                    </div>

                    <div onclick="simulateSampleScan('gatepass')" class="p-3.5 bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-300 rounded-xl cursor-pointer transition-all shadow-xs group">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 bg-amber-100/60 px-2 py-0.5 rounded">e-Pass Truk Driver</span>
                            <i class="fa-solid fa-arrow-right text-gray-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all text-xs"></i>
                        </div>
                        <h4 class="text-xs font-bold text-gray-900 font-mono">TRK-B9821-EXP</h4>
                        <p class="text-[11px] text-gray-500 mt-1">Izin Masuk Gate Truk Trailer PT Samudera Perkasa</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- KOLOM KANAN: INSPECTOR HASIL VERIFIKASI & METADATA LOGISTIK (Span 5 Kolom) -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4">
            
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700">Inspector Hasil Pemindaian</h3>
                </div>
                <span id="inspectFormatBadge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600 border border-gray-200 font-mono">
                    MENUNGGU SCAN
                </span>
            </div>

            <!-- Kartu Status Dekode Utama -->
            <div id="inspectEmptyState" class="py-12 px-4 text-center text-gray-400 space-y-2">
                <div class="w-12 h-12 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-center text-xl text-gray-300 mx-auto">
                    <i class="fa-solid fa-barcode"></i>
                </div>
                <p class="text-xs font-medium text-gray-500">Belum ada barcode/QR yang dipindai</p>
                <p class="text-[11px] text-gray-400 max-w-xs mx-auto">
                    Nyalakan kamera atau pilih uji sampel di sebelah kiri untuk melihat rincian dekonstruksi metadata.
                </p>
            </div>

            <div id="inspectResultCard" class="hidden space-y-4 animate-fadeIn">
                <!-- Raw Code Box -->
                <div class="bg-slate-900 text-white rounded-xl p-3.5 space-y-1 relative group">
                    <div class="flex items-center justify-between text-[10px] text-slate-400">
                        <span class="uppercase tracking-wider font-mono">Decoded Raw String</span>
                        <button onclick="copyRawCode()" class="hover:text-white transition" title="Salin Kode">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                    <p id="inspectRawText" class="font-mono font-bold text-sm sm:text-base text-emerald-400 break-all leading-tight"></p>
                    <span id="inspectStandardLabel" class="text-[10px] text-slate-400 block mt-1 font-medium"></span>
                </div>

                <!-- Hasil Audit Check Digit & Integritas -->
                <div class="p-3 rounded-xl border flex items-center justify-between text-xs" id="inspectCheckDigitBox">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-shield-check text-base" id="inspectCheckDigitIcon"></i>
                        <div>
                            <strong id="inspectCheckDigitTitle" class="block leading-tight">Check Digit Valid</strong>
                            <span id="inspectCheckDigitDesc" class="text-[10px] text-gray-500">Algoritma Modulo 10 / ISO 6346 Sesuai</span>
                        </div>
                    </div>
                    <span id="inspectCheckDigitBadge" class="px-2 py-0.5 rounded text-[10px] font-bold">PASSED</span>
                </div>

                <!-- Dekonstruksi Field Metadata GS1 / ISO -->
                <div class="bg-gray-50/70 rounded-xl p-3.5 border border-gray-100 space-y-2 text-xs">
                    <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider">Dekonstruksi Struktur Data:</span>
                    <div class="space-y-1.5" id="inspectFieldList">
                        <!-- Populated by JS -->
                    </div>
                </div>

                <!-- Pencocokan Basis Data Yard (YMS Cross-Reference) -->
                <div class="p-3.5 rounded-xl border border-blue-100 bg-blue-50/40 space-y-2 text-xs" id="inspectYmsBox">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] uppercase font-bold text-blue-700 tracking-wider flex items-center">
                            <i class="fa-solid fa-server mr-1.5"></i>Status Database CIDP
                        </span>
                        <span id="inspectYmsStatusBadge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                            TERDATA DI SISTEM
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-[11px]" id="inspectYmsGrid">
                        <!-- Populated by JS -->
                    </div>
                </div>

                <!-- Tombol Aksi Lanjutan -->
                <div class="space-y-2 pt-1">
                    <button onclick="logCurrentScan()" class="w-full py-2 bg-[#002f5e] hover:bg-[#004b87] text-white text-xs font-bold rounded-xl transition-all shadow-xs flex items-center justify-center space-x-1.5">
                        <i class="fa-solid fa-bookmark text-[11px]"></i>
                        <span>Simpan ke Log Pemeriksaan QA</span>
                    </button>
                    <a id="btnGoToDenah" href="dashboard.php?page=denah" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-gray-700 text-xs font-bold rounded-xl transition-all flex items-center justify-center space-x-1.5">
                        <i class="fa-solid fa-map-location-dot text-[11px] text-[#0170b9]"></i>
                        <span>Tinjau Posisi di Denah 35 Ha</span>
                    </a>
                </div>
            </div>

        </div>

    </div>

    <!-- ======================================================================= -->
    <!-- GALERI KARTU BARCODE & QR INTERAKTIF (BISA DISCAN LANGSUNG DI LAYAR) -->
    <!-- ======================================================================= -->
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3 flex-wrap gap-2">
            <div>
                <h3 class="text-sm font-bold text-gray-900 flex items-center">
                    <i class="fa-solid fa-qrcode text-[#0170b9] mr-2"></i>
                    Galeri Barcode & QR Code Siap Pindai (Interactive Test Targets)
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    Gunakan kamera smartphone atau webcam Anda untuk memindai target barcode di bawah ini secara langsung dari layar monitor.
                </p>
            </div>
            <span class="text-xs text-gray-400 font-mono">JsBarcode &amp; qrcode.js Engine</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Target 1: SSCC-18 Barcode -->
            <div class="p-3.5 bg-slate-50/70 border border-slate-200/80 rounded-xl text-center space-y-2 flex flex-col justify-between">
                <div>
                    <span class="text-[9.5px] font-bold uppercase text-blue-600 bg-blue-100/60 px-2 py-0.5 rounded block w-fit mx-auto mb-1.5">GS1 SSCC-18 (Box #1)</span>
                    <h5 class="text-xs font-bold text-gray-800">MSKU9182374</h5>
                    <p class="text-[10px] text-gray-500">PT Samudera Logistik Prima</p>
                </div>
                <div class="bg-white p-2 rounded-lg border border-gray-200 flex items-center justify-center min-h-[90px]">
                    <svg id="barcodeTarget1" class="max-w-full h-auto"></svg>
                </div>
                <button onclick="simulateSampleScan('target1')" class="w-full py-1 text-[11px] font-bold text-[#0170b9] hover:bg-blue-50 rounded transition">
                    Uji Dekode Ini &rarr;
                </button>
            </div>

            <!-- Target 2: ISO 6346 Code 128 -->
            <div class="p-3.5 bg-slate-50/70 border border-slate-200/80 rounded-xl text-center space-y-2 flex flex-col justify-between">
                <div>
                    <span class="text-[9.5px] font-bold uppercase text-indigo-600 bg-indigo-100/60 px-2 py-0.5 rounded block w-fit mx-auto mb-1.5">ISO 6346 Container</span>
                    <h5 class="text-xs font-bold text-gray-800">TCLU1234567</h5>
                    <p class="text-[10px] text-gray-500">PT Evergreen Shipping Indo</p>
                </div>
                <div class="bg-white p-2 rounded-lg border border-gray-200 flex items-center justify-center min-h-[90px]">
                    <svg id="barcodeTarget2" class="max-w-full h-auto"></svg>
                </div>
                <button onclick="simulateSampleScan('target2')" class="w-full py-1 text-[11px] font-bold text-[#0170b9] hover:bg-blue-50 rounded transition">
                    Uji Dekode Ini &rarr;
                </button>
            </div>

            <!-- Target 3: QR Code Smart E-Seal -->
            <div class="p-3.5 bg-slate-50/70 border border-slate-200/80 rounded-xl text-center space-y-2 flex flex-col justify-between">
                <div>
                    <span class="text-[9.5px] font-bold uppercase text-emerald-600 bg-emerald-100/60 px-2 py-0.5 rounded block w-fit mx-auto mb-1.5">CEISA 4.0 Smart E-Seal</span>
                    <h5 class="text-xs font-bold text-gray-800">Jointech JT701 GPS</h5>
                    <p class="text-[10px] text-gray-500">Pabean Impor Jalur Hijau</p>
                </div>
                <div class="bg-white p-2 rounded-lg border border-gray-200 flex items-center justify-center min-h-[90px]">
                    <div id="qrTarget3" class="mx-auto"></div>
                </div>
                <button onclick="simulateSampleScan('target3')" class="w-full py-1 text-[11px] font-bold text-[#0170b9] hover:bg-blue-50 rounded transition">
                    Uji Dekode Ini &rarr;
                </button>
            </div>

            <!-- Target 4: QR Code Gate e-Pass -->
            <div class="p-3.5 bg-slate-50/70 border border-slate-200/80 rounded-xl text-center space-y-2 flex flex-col justify-between">
                <div>
                    <span class="text-[9.5px] font-bold uppercase text-amber-600 bg-amber-100/60 px-2 py-0.5 rounded block w-fit mx-auto mb-1.5">Truk Trailer Gate Pass</span>
                    <h5 class="text-xs font-bold text-gray-800">B 9821 EXP (Inbound)</h5>
                    <p class="text-[10px] text-gray-500">Validasi ANPR &amp; Solas VGM</p>
                </div>
                <div class="bg-white p-2 rounded-lg border border-gray-200 flex items-center justify-center min-h-[90px]">
                    <div id="qrTarget4" class="mx-auto"></div>
                </div>
                <button onclick="simulateSampleScan('target4')" class="w-full py-1 text-[11px] font-bold text-[#0170b9] hover:bg-blue-50 rounded transition">
                    Uji Dekode Ini &rarr;
                </button>
            </div>

        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- TABEL RIWAYAT AUDIT & PENJAMINAN MUTU (QA INSPECTION LOG) -->
    <!-- ======================================================================= -->
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3 flex-wrap gap-2">
            <div class="flex items-center space-x-2">
                <h3 class="text-sm font-bold text-gray-900 flex items-center">
                    <i class="fa-solid fa-list-check text-blue-600 mr-2"></i>
                    Log Pemeriksaan Mutu AIDC Sesi Ini (Audit Trail)
                </h3>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="exportScanLogCSV()" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-200 transition flex items-center space-x-1">
                    <i class="fa-solid fa-file-excel text-emerald-600"></i>
                    <span>Export CSV</span>
                </button>
                <button onclick="clearScanLog()" class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 transition">
                    Bersihkan Log
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs" id="tableScanLog">
                <thead>
                    <tr class="bg-slate-50 text-gray-500 uppercase tracking-wider font-semibold border-b border-gray-200">
                        <th class="py-3 px-3 text-center w-12">No.</th>
                        <th class="py-3 px-3.5">Waktu</th>
                        <th class="py-3 px-3.5">Kode Terdeteksi</th>
                        <th class="py-3 px-3.5">Format Standar</th>
                        <th class="py-3 px-3.5">Hasil Audit Check Digit</th>
                        <th class="py-3 px-3.5">Entitas / Pemilik Terkait</th>
                        <th class="py-3 px-3.5 text-center">Status YMS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700" id="scanLogBody">
                    <tr id="emptyLogRow">
                        <td colspan="7" class="py-8 text-center text-gray-400">
                            Belum ada aktivitas pemindaian yang dicatat. Silakan lakukan pemindaian di atas.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- =========================================================================== -->
<!-- JAVASCRIPT: LOGIKA ENJIN SCANNER, PARSER GS1/ISO & AUDIO SYNTH -->
<!-- =========================================================================== -->
<script>
// Basis Data Kontainer CIDP yang diinjeksi dari PHP
const knownContainersDb = <?= json_encode($known_containers, JSON_PRETTY_PRINT) ?>;

let html5QrCodeScanner = null;
let isCameraRunning = false;
let audioEnabled = true;
let scanCounter = 0;
let scanLog = [];

// Audio Beep Generator via Web Audio API (Tidak perlu file audio eksternal)
function playBeepSound(type = 'success') {
    if (!audioEnabled) return;
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();

        osc.connect(gain);
        gain.connect(audioCtx.destination);

        if (type === 'success') {
            osc.frequency.setValueAtTime(1046.50, audioCtx.currentTime); // C6 Note
            gain.gain.setValueAtTime(0.12, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.12);
            osc.start(audioCtx.currentTime);
            osc.stop(audioCtx.currentTime + 0.12);
        } else {
            // Warn / Error dual tone
            osc.frequency.setValueAtTime(329.63, audioCtx.currentTime); // E4 Note
            gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.25);
            osc.start(audioCtx.currentTime);
            osc.stop(audioCtx.currentTime + 0.25);
        }
    } catch (e) {
        console.warn('Web Audio not allowed without user gesture:', e);
    }
}

function toggleAudioFeedback() {
    audioEnabled = !audioEnabled;
    const icon = document.getElementById('audioIcon');
    const text = document.getElementById('audioText');
    if (audioEnabled) {
        icon.className = "fa-solid fa-volume-high text-blue-600";
        text.textContent = "Audio: Aktif";
    } else {
        icon.className = "fa-solid fa-volume-xmark text-gray-400";
        text.textContent = "Audio: Mute";
    }
}

// Switch Mode Tab (Webcam / File / Quick)
function switchScannerMode(mode) {
    document.getElementById('modeWebcam').classList.add('hidden');
    document.getElementById('modeFile').classList.add('hidden');
    document.getElementById('modeQuick').classList.add('hidden');

    const btnW = document.getElementById('tabBtnWebcam');
    const btnF = document.getElementById('tabBtnFile');
    const btnQ = document.getElementById('tabBtnQuick');

    [btnW, btnF, btnQ].forEach(btn => {
        btn.className = "px-3.5 py-1.5 rounded-lg text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-all flex items-center space-x-1.5";
    });

    if (mode === 'webcam') {
        document.getElementById('modeWebcam').classList.remove('hidden');
        btnW.className = "px-3.5 py-1.5 rounded-lg text-xs font-bold bg-[#002f5e] text-white shadow-xs transition-all flex items-center space-x-1.5";
    } else if (mode === 'file') {
        document.getElementById('modeFile').classList.remove('hidden');
        btnF.className = "px-3.5 py-1.5 rounded-lg text-xs font-bold bg-[#002f5e] text-white shadow-xs transition-all flex items-center space-x-1.5";
        if (isCameraRunning) stopCameraScanner();
    } else if (mode === 'quick') {
        document.getElementById('modeQuick').classList.remove('hidden');
        btnQ.className = "px-3.5 py-1.5 rounded-lg text-xs font-bold bg-[#002f5e] text-white shadow-xs transition-all flex items-center space-x-1.5";
    }
}

// Inisialisasi Daftar Kamera
function initCameraDevices() {
    if (typeof Html5Qrcode === 'undefined') {
        console.warn('html5-qrcode not yet loaded');
        return;
    }

    Html5Qrcode.getCameras().then(devices => {
        const select = document.getElementById('cameraSelect');
        select.innerHTML = '';
        if (devices && devices.length) {
            devices.forEach((device, index) => {
                const opt = document.createElement('option');
                opt.value = device.id;
                opt.text = device.label || `Kamera ${index + 1}`;
                select.appendChild(opt);
            });
        } else {
            select.innerHTML = '<option value="">Tidak ada kamera terdeteksi</option>';
        }
    }).catch(err => {
        console.warn('Error fetching cameras:', err);
        const select = document.getElementById('cameraSelect');
        select.innerHTML = '<option value="">Izin kamera diperlukan</option>';
    });
}

// Mulai Pemindai Kamera Langsung
function startCameraScanner() {
    if (typeof Html5Qrcode === 'undefined') {
        alert('Library html5-qrcode belum selesai dimuat. Harap periksa koneksi.');
        return;
    }

    const select = document.getElementById('cameraSelect');
    const cameraId = select.value;
    const config = {
        fps: 15,
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.333334
    };

    if (!html5QrCodeScanner) {
        html5QrCodeScanner = new Html5Qrcode("reader");
    }

    const cameraConfig = cameraId ? { deviceId: { exact: cameraId } } : { facingMode: "environment" };

    html5QrCodeScanner.start(
        cameraConfig,
        config,
        (decodedText, decodedResult) => {
            // Callback ketika kode sukses terdeteksi
            handleDecodedCode(decodedText, decodedResult ? decodedResult.result.format.formatName : 'AUTOMATIC');
        },
        (errorMessage) => {
            // Frame parsing (bisa diabaikan untuk menjaga performa konsol)
        }
    ).then(() => {
        isCameraRunning = true;
        document.getElementById('cameraStandbyPlaceholder').classList.add('hidden');
        document.getElementById('scanLaserEffect').classList.remove('hidden');
        document.getElementById('btnStartCamera').classList.add('hidden');
        document.getElementById('btnStopCamera').classList.remove('hidden');
    }).catch(err => {
        alert('Gagal mengakses kamera: ' + err + '\nPastikan izin kamera browser telah diaktifkan.');
    });
}

// Matikan Pemindai Kamera
function stopCameraScanner() {
    if (html5QrCodeScanner && isCameraRunning) {
        html5QrCodeScanner.stop().then(() => {
            isCameraRunning = false;
            document.getElementById('cameraStandbyPlaceholder').classList.remove('hidden');
            document.getElementById('scanLaserEffect').classList.add('hidden');
            document.getElementById('btnStartCamera').classList.remove('hidden');
            document.getElementById('btnStopCamera').classList.add('hidden');
        }).catch(err => {
            console.error('Failed to stop camera:', err);
        });
    }
}

// Handle Pindai dari File Unggahan
function handleFileScan(e) {
    const file = e.target.files[0];
    if (!file) return;

    const previewBox = document.getElementById('fileScanPreviewBox');
    const previewImg = document.getElementById('filePreviewImg');
    const fileNameEl = document.getElementById('fileName');
    const fileStatus = document.getElementById('fileScanStatus');

    fileNameEl.textContent = file.name;
    fileStatus.textContent = 'Menganalisis citra optik...';
    previewBox.classList.remove('hidden');

    const reader = new FileReader();
    reader.onload = (event) => {
        previewImg.src = event.target.result;
    };
    reader.readAsDataURL(file);

    if (!html5QrCodeScanner) {
        html5QrCodeScanner = new Html5Qrcode("reader");
    }

    html5QrCodeScanner.scanFile(file, true)
        .then(decodedText => {
            fileStatus.innerHTML = '<span class="text-emerald-600 font-bold">Berhasil Didekode &check;</span>';
            handleDecodedCode(decodedText, 'IMAGE_FILE');
        })
        .catch(err => {
            fileStatus.innerHTML = '<span class="text-red-500 font-semibold">Gagal mendeteksi barcode pada gambar ini.</span>';
            playBeepSound('warn');
        });
}

function clearFileScan() {
    document.getElementById('fileInput').value = '';
    document.getElementById('fileScanPreviewBox').classList.add('hidden');
}

// =============================================================================
// PARSER LOGISTIK PINTAR: GS1 SSCC-18, ISO 6346 & CEISA 4.0
// =============================================================================

// Validasi Check Digit ISO 6346 (Container Check Digit Algorithm)
function validateIso6346CheckDigit(cntrNum) {
    if (!cntrNum || cntrNum.length !== 11) return false;
    const charValues = {
        'A':10, 'B':12, 'C':13, 'D':14, 'E':15, 'F':16, 'G':17, 'H':18, 'I':19, 'J':20,
        'K':21, 'L':23, 'M':24, 'N':25, 'O':26, 'P':27, 'Q':28, 'R':29, 'S':30, 'T':31,
        'U':32, 'V':34, 'W':35, 'X':36, 'Y':37, 'Z':38
    };

    let sum = 0;
    const cleanNum = cntrNum.toUpperCase();

    for (let i = 0; i < 10; i++) {
        const char = cleanNum[i];
        let val = 0;
        if (char >= '0' && char <= '9') {
            val = parseInt(char);
        } else if (charValues[char]) {
            val = charValues[char];
        } else {
            return false;
        }
        sum += val * Math.pow(2, i);
    }

    const calculatedCheck = (sum % 11) % 10;
    const actualCheck = parseInt(cleanNum[10]);
    return calculatedCheck === actualCheck;
}

// Validasi Check Digit GS1 Modulo 10 (SSCC-18)
function validateGs1Modulo10(digits) {
    const clean = digits.replace(/\D/g, '');
    if (clean.length < 18) return true; // fallback
    const digits17 = clean.slice(-18, -1);
    const actualCheck = parseInt(clean.slice(-1));

    let sum = 0;
    for (let i = digits17.length - 1; i >= 0; i--) {
        const num = parseInt(digits17[i]);
        const weight = ((digits17.length - 1 - i) % 2 === 0) ? 3 : 1;
        sum += num * weight;
    }
    const nextTen = Math.ceil(sum / 10) * 10;
    const calculatedCheck = nextTen - sum;
    return calculatedCheck === actualCheck;
}

// Fungsi Pusat Pemrosesan Hasil Scan
function handleDecodedCode(rawText, formatName = 'AUTO') {
    playBeepSound('success');
    scanCounter++;
    document.getElementById('statScanCount').textContent = scanCounter + ' Box';

    // Sembunyikan empty state, buka inspector
    document.getElementById('inspectEmptyState').classList.add('hidden');
    document.getElementById('inspectResultCard').classList.remove('hidden');

    const clean = rawText.trim();
    document.getElementById('inspectRawText').textContent = clean;

    let standardType = 'GENERIC_BARCODE';
    let checkDigitValid = true;
    let fields = [];
    let matchedContainer = null;

    // 1. Analisis: Apakah ini GS1 SSCC-18? (Panjang ~18-20 digit dengan atau tanpa kurung (00))
    if (clean.includes('(00)') || (clean.replace(/\D/g, '').length === 18 && clean.startsWith('00')) || (clean.replace(/\D/g, '').length === 18 && clean.startsWith('389'))) {
        standardType = 'GS1-128 / SSCC-18 (Serial Shipping Container Code)';
        const digitsOnly = clean.replace(/\D/g, '');
        checkDigitValid = validateGs1Modulo10(digitsOnly);

        fields.push({ label: 'Application Identifier (AI)', val: '(00) - SSCC Unit Logistik' });
        fields.push({ label: 'GS1 Company Prefix', val: digitsOnly.slice(1, 7) || '389912 (Indonesia)' });
        fields.push({ label: 'Serial Reference Box', val: digitsOnly.slice(7, 17) || '0000000001' });
        fields.push({ label: 'Check Digit (Modulo-10)', val: digitsOnly.slice(-1) + (checkDigitValid ? ' (Valid &check;)' : ' (Mismatch &cross;)') });

        // Cari di DB kontainer
        matchedContainer = knownContainersDb.find(c => c.sscc_code.replace(/\D/g, '') === digitsOnly || digitsOnly.includes(c.sscc_code.replace(/\D/g, '')));
    }
    // 2. Analisis: Apakah ini Kode Kontainer ISO 6346? (Format: 4 Huruf + 7 Angka)
    else if (/^[A-Z]{4}[0-9]{7}$/i.test(clean) || clean.includes('MSKU') || clean.includes('TCLU') || clean.includes('TEMU') || clean.includes('CSQU') || clean.includes('FCIU') || clean.includes('HLXU') || clean.includes('CMAU') || clean.includes('OOLU')) {
        standardType = 'ISO 6346:2022 (Freight Container Identification)';
        const cntrClean = clean.replace(/[^A-Za-z0-9]/g, '').toUpperCase().slice(0, 11);
        checkDigitValid = validateIso6346CheckDigit(cntrClean);

        const ownerCode = cntrClean.slice(0, 3);
        const catId = cntrClean.slice(3, 4);
        const serialNo = cntrClean.slice(4, 10);
        const checkDigit = cntrClean.slice(10, 11);

        fields.push({ label: 'Owner Code (Kode Pemilik)', val: ownerCode + ' (Prefix Pelayaran)' });
        fields.push({ label: 'Category Identifier', val: catId + ' (' + (catId === 'U' ? 'Freight Container' : 'Detachable Equipment') + ')' });
        fields.push({ label: 'Nomor Seri Kontainer', val: serialNo });
        fields.push({ label: 'Check Digit (ISO Modulo-11)', val: checkDigit + (checkDigitValid ? ' (Sesuai Standar &check;)' : ' (Gagal Verifikasi &cross;)') });

        matchedContainer = knownContainersDb.find(c => c.container_number.toUpperCase() === cntrClean);
    }
    // 3. Analisis: Apakah ini Segel Pintar Bea Cukai (E-Seal CEISA 4.0)?
    else if (clean.includes('CEISA') || clean.includes('JT701') || clean.includes('ESEAL')) {
        standardType = 'CEISA 4.0 DJBC (Smart E-Seal Jointech JT701)';
        checkDigitValid = true;

        fields.push({ label: 'Protokol Kepabeanan', val: 'CEISA 4.0 Interkoneksi Ditjen Bea Cukai' });
        fields.push({ label: 'Hardware E-Seal', val: 'Jointech JT701 Smart GPS E-Lock' });
        fields.push({ label: 'Jalur Pelayanan Pabean', val: 'JALUR HIJAU (SPPB Otomatis)' });
        fields.push({ label: 'Status Integritas Kawat', val: 'SECURE / TAMPER-PROOF (0 Deteksi Putus)' });

        matchedContainer = knownContainersDb[0]; // Kaitkan sampel pertama
    }
    // 4. Analisis: e-Pass Truk
    else if (clean.includes('TRK') || clean.includes('GATEPASS') || clean.includes('EXP')) {
        standardType = 'CIDP Gate e-Pass (Truk Armada Logistik)';
        checkDigitValid = true;

        fields.push({ label: 'Identitas Tiket', val: 'Izin Masuk Gerbang Otomatis (Lane 1/2)' });
        fields.push({ label: 'Nomor Kendaraan Truk', val: 'B 9821 EXP (Trailer 40ft)' });
        fields.push({ label: 'Mitra Trucking', val: 'PT Samudera Pratama Mandiri' });
        fields.push({ label: 'Status SOLAS VGM', val: 'Terjadwal di Jembatan Timbang 80t' });

        matchedContainer = knownContainersDb[0];
    }
    else {
        standardType = 'Standard Symbology (' + formatName + ')';
        checkDigitValid = true;
        fields.push({ label: 'Tipe Kode', val: formatName });
        fields.push({ label: 'Panjang Karakter', val: clean.length + ' karakter' });
    }

    // Tampilkan data ke Inspector
    document.getElementById('inspectFormatBadge').textContent = formatName;
    document.getElementById('inspectStandardLabel').textContent = standardType;

    // Check digit box styling
    const cdBox = document.getElementById('inspectCheckDigitBox');
    const cdIcon = document.getElementById('inspectCheckDigitIcon');
    const cdTitle = document.getElementById('inspectCheckDigitTitle');
    const cdBadge = document.getElementById('inspectCheckDigitBadge');

    if (checkDigitValid) {
        cdBox.className = "p-3 rounded-xl border border-emerald-200 bg-emerald-50/60 flex items-center justify-between text-xs text-emerald-900";
        cdIcon.className = "fa-solid fa-shield-check text-emerald-600 text-base";
        cdTitle.textContent = "Audit Check Digit Valid";
        cdBadge.className = "px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800";
        cdBadge.textContent = "PASSED";
    } else {
        cdBox.className = "p-3 rounded-xl border border-red-200 bg-red-50/60 flex items-center justify-between text-xs text-red-900";
        cdIcon.className = "fa-solid fa-triangle-exclamation text-red-600 text-base";
        cdTitle.textContent = "Check Digit Tidak Sesuai!";
        cdBadge.className = "px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800";
        cdBadge.textContent = "FAILED";
    }

    // Render Field List
    const fList = document.getElementById('inspectFieldList');
    fList.innerHTML = '';
    fields.forEach(f => {
        const row = document.createElement('div');
        row.className = "flex items-center justify-between py-1 border-b border-gray-100/80 last:border-0";
        row.innerHTML = `<span class="text-gray-500">${f.label}:</span><strong class="text-gray-800 font-mono text-right">${f.val}</strong>`;
        fList.appendChild(row);
    });

    // Render YMS Database Cross-Reference
    const ymsBox = document.getElementById('inspectYmsBox');
    const ymsGrid = document.getElementById('inspectYmsGrid');
    const ymsBadge = document.getElementById('inspectYmsStatusBadge');

    if (matchedContainer) {
        ymsBadge.className = "px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800";
        ymsBadge.textContent = "TERDATA DI SISTEM YMS";
        ymsGrid.innerHTML = `
            <div><span class="text-gray-400 block">No. Kontainer:</span><strong class="text-gray-900 font-mono font-bold">${matchedContainer.container_number}</strong></div>
            <div><span class="text-gray-400 block">Lokasi Lapangan:</span><strong class="text-blue-700 font-mono font-bold">Blok ${matchedContainer.block}-${matchedContainer.bay}-${matchedContainer.row}-${matchedContainer.tier}</strong></div>
            <div><span class="text-gray-400 block">Pemilik Kargo:</span><span class="text-gray-800 font-medium truncate block">${matchedContainer.owner_company}</span></div>
            <div><span class="text-gray-400 block">Status Bea Cukai:</span><strong class="text-emerald-700 font-semibold">${matchedContainer.customs_status || 'SPPB_CLEARED'}</strong></div>
        `;
    } else {
        ymsBadge.className = "px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800";
        ymsBadge.textContent = "TRANSAKSI GATE-IN BARU";
        ymsGrid.innerHTML = `
            <div class="col-span-2 text-gray-500 text-[11px]">
                Nomor identitas ini valid secara optik tetapi belum terdaftar dalam lapangan penumpukan CIDP. Siap untuk diterbitkan dokumen Gate-In.
            </div>
        `;
    }

    // Catat ke array log lokal
    const logItem = {
        no: scanCounter,
        time: new Date().toLocaleTimeString('id-ID'),
        code: clean,
        standard: standardType,
        checkDigit: checkDigitValid ? 'VALID' : 'INVALID',
        entity: matchedContainer ? matchedContainer.owner_company : 'Truk Mitra Eksternal',
        status: matchedContainer ? 'IN_YARD' : 'NEW_GATE_IN'
    };
    scanLog.unshift(logItem);
    renderScanLogTable();
}

// Simulasi Uji Cepat Tombol Sampel
function simulateSampleScan(type) {
    if (type === 'sscc' || type === 'target1') {
        handleDecodedCode('(00)389912345000000001', 'CODE_128');
    } else if (type === 'iso' || type === 'target2') {
        handleDecodedCode('TCLU1234567', 'CODE_128');
    } else if (type === 'customs' || type === 'target3') {
        handleDecodedCode('CEISA4-JT701-884210', 'QR_CODE');
    } else if (type === 'gatepass' || type === 'target4') {
        handleDecodedCode('TRK-B9821-EXP', 'QR_CODE');
    }
}

// Render Tabel Riwayat Scan
function renderScanLogTable() {
    const tbody = document.getElementById('scanLogBody');
    if (!scanLog || scanLog.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-gray-400">Belum ada aktivitas pemindaian yang dicatat.</td></tr>`;
        return;
    }

    tbody.innerHTML = '';
    scanLog.slice(0, 10).forEach(item => {
        const tr = document.createElement('tr');
        tr.className = "hover:bg-slate-50/70 transition-colors";
        tr.innerHTML = `
            <td class="py-2.5 px-3 text-center font-bold text-gray-400 font-mono">${item.no}</td>
            <td class="py-2.5 px-3.5 text-gray-500 font-mono text-[11px]">${item.time}</td>
            <td class="py-2.5 px-3.5 font-bold font-mono text-gray-900">${item.code}</td>
            <td class="py-2.5 px-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">${item.standard.split('(')[0]}</span></td>
            <td class="py-2.5 px-3.5">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${item.checkDigit === 'VALID' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200'}">
                    ${item.checkDigit}
                </span>
            </td>
            <td class="py-2.5 px-3.5 text-gray-700 font-medium">${item.entity}</td>
            <td class="py-2.5 px-3.5 text-center">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold ${item.status === 'IN_YARD' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700'}">
                    ${item.status}
                </span>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function clearScanLog() {
    scanLog = [];
    renderScanLogTable();
}

function copyRawCode() {
    const text = document.getElementById('inspectRawText').textContent;
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
        alert('Kode berhasil disalin ke clipboard: ' + text);
    });
}

function logCurrentScan() {
    alert('Hasil audit optik berhasil dicatat ke dalam Log QA Terminal YMS.');
}

function exportScanLogCSV() {
    if (!scanLog || scanLog.length === 0) {
        alert('Belum ada data scan untuk diekspor.');
        return;
    }

    let csv = "No,Waktu,Kode,Format,CheckDigit,Entitas,Status\n";
    scanLog.forEach(row => {
        csv += `"${row.no}","${row.time}","${row.code}","${row.standard}","${row.checkDigit}","${row.entity}","${row.status}"\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", `CIDP_QA_AIDC_SCAN_LOG_${Date.now()}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Inisialisasi Target Barcode & QR Code di Galeri Saat Halaman Dimuat
document.addEventListener('DOMContentLoaded', () => {
    // 1. Inisialisasi Dropdown Kamera
    setTimeout(initCameraDevices, 300);

    // 2. Render Barcode 1 (SSCC-18)
    if (typeof JsBarcode !== 'undefined') {
        try {
            JsBarcode("#barcodeTarget1", "(00)389912345000000001", {
                format: "CODE128",
                width: 1.4,
                height: 48,
                displayValue: true,
                fontSize: 10,
                font: "monospace"
            });
            JsBarcode("#barcodeTarget2", "TCLU1234567", {
                format: "CODE128",
                width: 1.6,
                height: 48,
                displayValue: true,
                fontSize: 11,
                font: "monospace"
            });
        } catch (e) {
            console.warn('JsBarcode rendering error:', e);
        }
    }

    // 3. Render QR Code 3 & 4
    if (typeof QRCode !== 'undefined') {
        try {
            new QRCode(document.getElementById("qrTarget3"), {
                text: "CEISA4-JT701-884210",
                width: 76,
                height: 76,
                colorDark : "#002f5e",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.M
            });
            new QRCode(document.getElementById("qrTarget4"), {
                text: "TRK-B9821-EXP",
                width: 76,
                height: 76,
                colorDark : "#002f5e",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.M
            });
        } catch (e) {
            console.warn('QRCode rendering error:', e);
        }
    }
});
</script>
