<?php
// =============================================================================
// FILE       : login.php
// KONSULTAN  : Conclusion Supply Chain Consultant
// FUNGSI     : Laman Utama Konsultan & Modal Masuk Demonstrasi YMS
// STANDAR    : Bahasa Indonesia Baku (EYD) & Tata Letak Korporat Cikarang Dry Port
// FITUR BARU : Carousel Banner Bergambar Otomatis Dry Port yang Halus (Smooth)
// =============================================================================

session_start();

// 1. Apabila pengguna telah masuk (login), alihkan langsung ke dashboard.php
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header("Location: dashboard.php");
    exit;
}

// 2. Muat berkas koneksi basis data (database cidp_yms)
require_once __DIR__ . '/connection.php';

$error_message = '';

// 3. Tangani pengiriman formulir masuk (metode POST)
if (isset($_POST['btn_login'])) {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error_message = 'Alamat pos-el (email) dan kata sandi wajib diisi!';
    } else {
        try {
            // Ambil akun pengguna dari tabel login berdasarkan alamat pos-el
            $stmt = $pdo->prepare("SELECT iduser, nama, email, password, role FROM login WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Verifikasi kesesuaian kata sandi hash menggunakan password_verify
            if ($user && password_verify($password, $user['password'])) {
                // Autentikasi berhasil: simpan identitas ke dalam sesi (session)
                $_SESSION['login']  = true;
                $_SESSION['iduser'] = $user['iduser'];
                $_SESSION['nama']   = $user['nama'];
                $_SESSION['email']  = $user['email'];
                $_SESSION['role']   = $user['role'];

                // Alihkan ke konsol demonstrasi utama
                header("Location: dashboard.php");
                exit;
            } else {
                $error_message = 'Alamat pos-el atau kata sandi yang Anda masukkan tidak sesuai!';
            }
        } catch (PDOException $e) {
            $error_message = 'Terjadi kendala pada sistem: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conclusion Supply Chain Consultant &bull; Demonstrasi Yard Management System</title>
    
    <!-- Favicon / Ikon Tab Peramban (Browser Tab Icon) -->
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link rel="shortcut icon" type="image/png" href="assets/img/logo.png">
    <link rel="apple-touch-icon" href="assets/img/logo.png">
    
    <!-- Tailwind CSS (Offline Local Vendor) -->
    <script src="assets/vendor/tailwindcss.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cdp: {
                            navy: '#004b87',
                            dark: '#002f5e',
                            blue: '#0170b9',
                            light: '#f4f8fc',
                            orange: '#ea580c',
                            'orange-light': '#fff7ed'
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace']
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome 6 (Offline Local Vendor) -->
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* Gaya Transisi Halus (Smooth Crossfade Carousel) */
        .carousel-slide {
            transition: opacity 1.2s cubic-bezier(0.4, 0, 0.2, 1), transform 1.2s cubic-bezier(0.4, 0, 0.2, 1);
            background-size: cover;
            background-position: center;
        }
        .carousel-slide.active {
            opacity: 1;
            transform: scale(1);
            pointer-events: auto;
            z-index: 10;
        }
        .carousel-slide.inactive {
            opacity: 0;
            transform: scale(1.03);
            pointer-events: none;
            z-index: 0;
        }

        /* Titik Indikator Halus */
        .dot-indicator {
            transition: all 0.4s ease;
        }
        .dot-indicator.active {
            width: 28px;
            background-color: #ea580c;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-orange-500 selection:text-white overflow-x-hidden min-h-screen">

    <!-- =================================================================== -->
    <!-- 1. BILAH NAVIGASI ATAS (Standar Cikarang Dry Port - Responsive & Balanced) -->
    <!-- =================================================================== -->
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-40 shadow-xs w-full">
        <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-2.5 sm:py-3 flex items-center justify-between gap-4 w-full">
            
            <!-- LOGO MEREK: Conclusion Supply Chain Consultant (Sisi Kiri) -->
            <a href="login.php" class="flex items-center space-x-3 group shrink-0">
                <img src="assets/img/logo.png" alt="Conclusion Logo" class="w-9 h-9 sm:w-10 sm:h-10 object-contain transition-transform group-hover:scale-105">
                <div>
                    <div class="font-extrabold text-base sm:text-lg text-cdp-navy tracking-tight leading-none">
                        CONCLUSION
                    </div>
                    <div class="text-[9px] sm:text-[10px] font-bold text-orange-600 tracking-wider uppercase mt-0.5">
                        Supply Chain Consultant
                    </div>
                </div>
            </a>

            <!-- TAUTAN MENU UTAMA (Tengah Layar / Centered) -->
            <nav class="hidden lg:flex items-center justify-center space-x-5 xl:space-x-8 text-xs font-bold text-slate-700">
                <a href="#solusi" class="hover:text-orange-600 transition whitespace-nowrap">Solusi Dry Port</a>
                <a href="#fasilitas" class="hover:text-orange-600 transition whitespace-nowrap">Fasilitas 35 Ha</a>
                <a href="#tiga-alur" class="hover:text-orange-600 transition whitespace-nowrap">3 Alur Operasional</a>
            </nav>

            <!-- BAGIAN KANAN: PENCARIAN & TOMBOL MASUK DEMO YMS (Sisi Kanan) -->
            <div class="flex items-center space-x-2.5 sm:space-x-3 shrink-0">
                <!-- Search Bar (Hanya tampil di desktop lebar agar navbar tidak sesak) -->
                <div class="hidden xl:flex items-center bg-slate-100 rounded-full px-3 py-1.5 border border-slate-200 text-xs text-slate-500">
                    <i class="fa-solid fa-magnifying-glass mr-2 text-slate-400 text-xs"></i>
                    <input type="text" placeholder="No. Peti Kemas / B/L" class="bg-transparent border-none outline-none text-slate-700 w-32 2xl:w-44 text-xs placeholder:text-slate-400">
                    <span class="bg-white px-2 py-0.5 rounded-full text-[10px] font-bold text-slate-600 border ml-1 shadow-2xs">Peti Kemas</span>
                </div>

                <!-- Tombol Masuk Demo YMS -->
                <button type="button" onclick="openLoginModal()" 
                        class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-3.5 py-2 sm:px-4 sm:py-2 rounded-lg text-xs font-bold shadow-sm shadow-orange-500/25 transition-all flex items-center space-x-2 shrink-0">
                    <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                    <span class="hidden sm:inline">Masuk Demo YMS</span>
                    <span class="sm:hidden">Masuk</span>
                </button>
            </div>

        </div>
    </header>

    <!-- =================================================================== -->
    <!-- 2. CAROUSEL BANNER OTOMATIS BERGESER (SMOOTH DRY PORT SLIDER) -->
    <!-- =================================================================== -->
    <section id="heroCarousel" class="relative overflow-hidden w-full min-h-[560px] lg:min-h-[640px] xl:min-h-[720px] bg-slate-900 text-white select-none flex items-center">

        <!-- SLIDE 1: Terminal Peti Kemas & Derek Gantry Pelabuhan Kering (Outdoor Dry Port) -->
        <div class="carousel-slide active absolute inset-0 w-full h-full flex items-center justify-center text-center px-4 sm:px-6 lg:px-8"
             style="background-image: linear-gradient(rgba(0, 47, 94, 0.78), rgba(0, 75, 135, 0.85)), url('https://images.unsplash.com/photo-1605371924599-2d0365da1ae0?auto=format&fit=crop&w=2000&q=80');">
            <div class="w-full max-w-3xl lg:max-w-4xl xl:max-w-5xl mx-auto space-y-4 sm:space-y-5 lg:space-y-6 py-16 sm:py-20 lg:py-24">
                <div class="inline-flex items-center space-x-2 px-3 py-1 sm:px-3.5 sm:py-1.5 rounded-full bg-white/10 backdrop-blur-md text-xs sm:text-sm font-semibold text-blue-100 border border-white/20">
                    <span class="w-2 h-2 rounded-full bg-orange-400 animate-pulse"></span>
                    <span>Prototipe Yard Management System (YMS)</span>
                </div>

                <h1 class="text-2xl sm:text-4xl lg:text-5xl xl:text-6xl font-extrabold tracking-tight text-white leading-tight">
                    Simulasi Manajemen Lapangan Peti Kemas
                </h1>

                <p class="text-xs sm:text-sm lg:text-base xl:text-lg text-blue-100 font-normal leading-relaxed max-w-2xl lg:max-w-3xl mx-auto">
                    Platform intermodal rel-jalan raya 35 Ha terintegrasi sensor IoT, visualisasi digital twin, dan rekonsiliasi ERP waktu-nyata.
                </p>

                <div class="pt-2 sm:pt-4">
                    <button type="button" onclick="openLoginModal()"
                            class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-extrabold px-6 py-3 sm:px-8 sm:py-3.5 rounded-lg text-xs sm:text-sm shadow-xl shadow-orange-950/40 transition transform hover:-translate-y-0.5 inline-flex items-center space-x-2.5">
                        <span>Uji Coba Demonstrasi YMS</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- SLIDE 2: Rel Kereta Api Logistik (KAI Logistik & Intermodal Siding) -->
        <div class="carousel-slide inactive absolute inset-0 w-full h-full flex items-center justify-center text-center px-4 sm:px-6 lg:px-8"
             style="background-image: linear-gradient(rgba(0, 47, 94, 0.78), rgba(0, 75, 135, 0.85)), url('https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?auto=format&fit=crop&w=2000&q=80');">
            <div class="w-full max-w-3xl lg:max-w-4xl xl:max-w-5xl mx-auto space-y-4 sm:space-y-5 lg:space-y-6 py-16 sm:py-20 lg:py-24">
                <div class="inline-flex items-center space-x-2 px-3 py-1 sm:px-3.5 sm:py-1.5 rounded-full bg-white/10 backdrop-blur-md text-xs sm:text-sm font-semibold text-cyan-200 border border-white/20">
                    <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                    <span>Konektivitas Rel Kereta Api Antarmoda</span>
                </div>

                <h1 class="text-2xl sm:text-4xl lg:text-5xl xl:text-6xl font-extrabold tracking-tight text-white leading-tight">
                    Integrasi Angkutan Rel Peti Kemas
                </h1>

                <p class="text-xs sm:text-sm lg:text-base xl:text-lg text-blue-100 font-normal leading-relaxed max-w-2xl lg:max-w-3xl mx-auto">
                    Konektivitas rel ganda Cikarang – Pelabuhan Tanjung Priok dengan kapasitas 30 hingga 60 TEUs per rangkaian.
                </p>

                <div class="pt-2 sm:pt-4">
                    <button type="button" onclick="openLoginModal()"
                            class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-extrabold px-6 py-3 sm:px-8 sm:py-3.5 rounded-lg text-xs sm:text-sm shadow-xl shadow-orange-950/40 transition transform hover:-translate-y-0.5 inline-flex items-center space-x-2.5">
                        <span>Pelajari Solusi Antarmoda</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- SLIDE 3: Penataan Lapangan Peti Kemas 3D & Alat Angkat Kalmar -->
        <div class="carousel-slide inactive absolute inset-0 w-full h-full flex items-center justify-center text-center px-4 sm:px-6 lg:px-8"
             style="background-image: linear-gradient(rgba(0, 47, 94, 0.78), rgba(0, 75, 135, 0.85)), url('https://images.unsplash.com/photo-1578575437130-527eed3abbec?auto=format&fit=crop&w=2000&q=80');">
            <div class="w-full max-w-3xl lg:max-w-4xl xl:max-w-5xl mx-auto space-y-4 sm:space-y-5 lg:space-y-6 py-16 sm:py-20 lg:py-24">
                <div class="inline-flex items-center space-x-2 px-3 py-1 sm:px-3.5 sm:py-1.5 rounded-full bg-white/10 backdrop-blur-md text-xs sm:text-sm font-semibold text-amber-200 border border-white/20">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    <span>Optimasi Penataan Lapangan 3D</span>
                </div>

                <h1 class="text-2xl sm:text-4xl lg:text-5xl xl:text-6xl font-extrabold tracking-tight text-white leading-tight">
                    Pemetaan Presisi Blok, Bay, Row &amp; Tier
                </h1>

                <p class="text-xs sm:text-sm lg:text-base xl:text-lg text-blue-100 font-normal leading-relaxed max-w-2xl lg:max-w-3xl mx-auto">
                    Cegah shuffling liar dengan digital twin 3D, kaidah beban terberat di bawah, dan telemetri RTK reach stacker.
                </p>

                <div class="pt-2 sm:pt-4">
                    <button type="button" onclick="openLoginModal()"
                            class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-extrabold px-6 py-3 sm:px-8 sm:py-3.5 rounded-lg text-xs sm:text-sm shadow-xl shadow-orange-950/40 transition transform hover:-translate-y-0.5 inline-flex items-center space-x-2.5">
                        <span>Simulasikan Penataan Lapangan</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- SLIDE 4: Gerbang Cerdas OCR & Integrasi Penagihan Sistem ERP -->
        <div class="carousel-slide inactive absolute inset-0 w-full h-full flex items-center justify-center text-center px-4 sm:px-6 lg:px-8"
             style="background-image: linear-gradient(rgba(0, 47, 94, 0.78), rgba(0, 75, 135, 0.85)), url('https://images.unsplash.com/photo-1519003722824-194d4455a60c?auto=format&fit=crop&w=2000&q=80');">
            <div class="w-full max-w-3xl lg:max-w-4xl xl:max-w-5xl mx-auto space-y-4 sm:space-y-5 lg:space-y-6 py-16 sm:py-20 lg:py-24">
                <div class="inline-flex items-center space-x-2 px-3 py-1 sm:px-3.5 sm:py-1.5 rounded-full bg-white/10 backdrop-blur-md text-xs sm:text-sm font-semibold text-emerald-200 border border-white/20">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Otomasi Gerbang &amp; Rekonsiliasi Finansial</span>
                </div>

                <h1 class="text-2xl sm:text-4xl lg:text-5xl xl:text-6xl font-extrabold tracking-tight text-white leading-tight">
                    Otomasi Gate &amp; Penagihan ERP
                </h1>

                <p class="text-xs sm:text-sm lg:text-base xl:text-lg text-blue-100 font-normal leading-relaxed max-w-2xl lg:max-w-3xl mx-auto">
                    Kamera OCR ISO 6346, jembatan timbang 80T SOLAS VGM, dan penagihan komersial otomatis tanpa kebocoran pendapatan.
                </p>

                <div class="pt-2 sm:pt-4">
                    <button type="button" onclick="openLoginModal()"
                            class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-extrabold px-6 py-3 sm:px-8 sm:py-3.5 rounded-lg text-xs sm:text-sm shadow-xl shadow-orange-950/40 transition transform hover:-translate-y-0.5 inline-flex items-center space-x-2.5">
                        <span>Eksplorasi Otomasi Gate &amp; ERP</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- TOMBOL NAVIGASI MANUAL KIRI & KANAN (SIMETRIS) -->
        <button type="button" onclick="prevSlide()" aria-label="Slide Sebelumnya"
                class="absolute left-3 sm:left-6 lg:left-8 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-black/30 hover:bg-black/60 text-white flex items-center justify-center transition backdrop-blur-xs z-20 shadow-md">
            <i class="fa-solid fa-chevron-left text-xs sm:text-sm"></i>
        </button>
        <button type="button" onclick="nextSlide()" aria-label="Slide Berikutnya"
                class="absolute right-3 sm:right-6 lg:right-8 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-black/30 hover:bg-black/60 text-white flex items-center justify-center transition backdrop-blur-xs z-20 shadow-md">
            <i class="fa-solid fa-chevron-right text-xs sm:text-sm"></i>
        </button>

        <!-- INDIKATOR TITIK (DOTS) DI BAGIAN BAWAH CAROUSEL (SIMETRIS) -->
        <div class="absolute bottom-5 sm:bottom-8 left-1/2 -translate-x-1/2 flex items-center space-x-2 sm:space-x-3 z-20">
            <button type="button" onclick="goToSlide(0)" class="dot-indicator active w-7 sm:w-8 h-2 sm:h-2.5 rounded-full bg-white transition-all"></button>
            <button type="button" onclick="goToSlide(1)" class="dot-indicator w-2 sm:w-2.5 h-2 sm:h-2.5 rounded-full bg-white/50 hover:bg-white/80 transition-all"></button>
            <button type="button" onclick="goToSlide(2)" class="dot-indicator w-2 sm:w-2.5 h-2 sm:h-2.5 rounded-full bg-white/50 hover:bg-white/80 transition-all"></button>
            <button type="button" onclick="goToSlide(3)" class="dot-indicator w-2 sm:w-2.5 h-2 sm:h-2.5 rounded-full bg-white/50 hover:bg-white/80 transition-all"></button>
        </div>

    </section>

    <!-- =================================================================== -->
    <!-- 3. SOLUSI STRATEGIS KONSULTAN -->
    <!-- =================================================================== -->
    <section id="solusi" class="py-16 lg:py-20 bg-white border-b border-slate-200">
        <a id="tentang"></a>
        <div class="max-w-[1680px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12">
            
            <div class="max-w-3xl mx-auto text-center space-y-3 mb-12 lg:mb-16">
                <div class="inline-flex items-center space-x-2 px-3.5 py-1 rounded-full bg-orange-50 text-orange-600 text-xs font-bold border border-orange-200/80 uppercase tracking-wider">
                    <i class="fa-solid fa-compass-drafting"></i>
                    <span>Solusi Terpadu &bull; Conclusion Supply Chain</span>
                </div>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Pengembangan Pelabuhan Kering Antarmoda
                </h2>
                <p class="text-xs sm:text-sm lg:text-base text-slate-600 leading-relaxed max-w-2xl mx-auto">
                    Arsitektur sistem manajemen dry port terintegrasi: percepatan logistik, otomasi telemetri, dan tata kelola finansial presisi.
                </p>
            </div>

            <!-- 4 Nilai Unggulan Konsultansi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 xl:gap-6">
                
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-blue-500 hover:shadow-md transition-all group flex flex-col justify-between">
                    <div>
                        <div class="w-11 h-11 rounded-xl bg-blue-100 text-cdp-navy flex items-center justify-center text-lg mb-4 group-hover:bg-cdp-navy group-hover:text-white transition shadow-2xs">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <h3 class="font-bold text-sm lg:text-base text-slate-900 mb-1.5">Reduksi Waktu Inap</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Pemotongan masa inap kontainer di pelabuhan laut melalui kliring kepabeanan langsung di pedalaman.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-200/60 flex items-center justify-between">
                        <span class="text-[11px] font-bold text-blue-700 bg-blue-100/70 px-2.5 py-0.5 rounded-full">-40% Dwelling Time</span>
                        <i class="fa-solid fa-arrow-trend-down text-blue-500 text-xs"></i>
                    </div>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-cyan-500 hover:shadow-md transition-all group flex flex-col justify-between">
                    <div>
                        <div class="w-11 h-11 rounded-xl bg-cyan-100 text-cdp-blue flex items-center justify-center text-lg mb-4 group-hover:bg-cdp-blue group-hover:text-white transition shadow-2xs">
                            <i class="fa-solid fa-train-subway"></i>
                        </div>
                        <h3 class="font-bold text-sm lg:text-base text-slate-900 mb-1.5">Konektivitas Rel Barang</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Pengalihan beban jalan raya ke koridor rel ganda langsung berkapasitas masif dan ramah lingkungan.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-200/60 flex items-center justify-between">
                        <span class="text-[11px] font-bold text-cyan-800 bg-cyan-100 px-2.5 py-0.5 rounded-full">30–60 TEU / Rangkaian</span>
                        <i class="fa-solid fa-train text-cyan-600 text-xs"></i>
                    </div>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-orange-500 hover:shadow-md transition-all group flex flex-col justify-between">
                    <div>
                        <div class="w-11 h-11 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-lg mb-4 group-hover:bg-gradient-to-r group-hover:from-orange-500 group-hover:to-orange-600 group-hover:text-white transition shadow-2xs">
                            <i class="fa-solid fa-microchip"></i>
                        </div>
                        <h3 class="font-bold text-sm lg:text-base text-slate-900 mb-1.5">Otomasi Telemetri IoT</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Integrasi sensor twistlock alat angkat, timbangan VGM SOLAS, dan pemindai optik OCR kamera.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-200/60 flex items-center justify-between">
                        <span class="text-[11px] font-bold text-orange-700 bg-orange-100 px-2.5 py-0.5 rounded-full">Akurasi &gt; 99%</span>
                        <i class="fa-solid fa-satellite text-orange-500 text-xs"></i>
                    </div>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-emerald-500 hover:shadow-md transition-all group flex flex-col justify-between">
                    <div>
                        <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg mb-4 group-hover:bg-emerald-600 group-hover:text-white transition shadow-2xs">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <h3 class="font-bold text-sm lg:text-base text-slate-900 mb-1.5">Nir-Kebocoran Finansial</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Sinkronisasi otomatis setiap manuver fisik ke modul tagihan komersial sistem ERP secara instan.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-200/60 flex items-center justify-between">
                        <span class="text-[11px] font-bold text-emerald-800 bg-emerald-100 px-2.5 py-0.5 rounded-full">Zero Revenue Leakage</span>
                        <i class="fa-solid fa-receipt text-emerald-600 text-xs"></i>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 4. FASILITAS & SOLUSI TERPADU (GAYA CIKARANG DRY PORT) -->
    <!-- =================================================================== -->
    <section id="fasilitas" class="py-16 lg:py-20 bg-slate-50 border-b border-slate-200">
        <div class="max-w-[1680px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12">
            
            <div class="max-w-3xl mx-auto text-center space-y-3 mb-12 lg:mb-16">
                <div class="inline-flex items-center space-x-2 px-3.5 py-1 rounded-full bg-orange-50 text-orange-600 text-xs font-bold border border-orange-200/80 uppercase tracking-wider">
                    <i class="fa-solid fa-warehouse"></i>
                    <span>Infrastruktur Lapangan</span>
                </div>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Fasilitas &amp; Solusi Terpadu 35 Ha
                </h2>
                <p class="text-xs sm:text-sm lg:text-base text-slate-600 leading-relaxed max-w-2xl mx-auto">
                    Infrastruktur modern berstandar internasional yang disimulasikan dan diintegrasikan secara menyeluruh.
                </p>
            </div>

            <!-- Grid 6 Fasilitas Utama -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 xl:gap-8">
                
                <!-- Fasilitas 1: KPPT Bea Cukai & Karantina -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-lg hover:border-cdp-blue transition-all overflow-hidden flex flex-col group">
                    <div class="h-44 lg:h-48 overflow-hidden relative">
                        <img src="https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=800&q=80" 
                             alt="Kawasan Pelayanan Pabean Terpadu" 
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-cdp-navy/90 backdrop-blur-xs text-white text-[10px] font-bold px-2.5 py-1 rounded-md shadow-xs">
                            Pelayanan Pabean
                        </span>
                    </div>
                    <div class="p-5 lg:p-6 flex-1 flex flex-col justify-between space-y-3.5">
                        <div class="space-y-1.5">
                            <h3 class="font-bold text-sm lg:text-base text-slate-900 group-hover:text-cdp-blue transition">
                                Kawasan Pabean Terpadu &amp; Karantina
                            </h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Kliring dokumen kepabeanan satu atap jalur hijau/merah dan sertifikasi karantina terpadu.
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-1.5 pt-3 border-t border-slate-100 text-[10.5px] font-semibold text-slate-700">
                            <span class="bg-slate-100 px-2.5 py-0.5 rounded-md">SPPB &amp; BC 1.1</span>
                            <span class="bg-slate-100 px-2.5 py-0.5 rounded-md">Jalur Hijau / Merah</span>
                            <span class="bg-slate-100 px-2.5 py-0.5 rounded-md">Karantina HPIK</span>
                        </div>
                    </div>
                </div>

                <!-- Fasilitas 2: Emplasemen Rel Antarmoda -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-lg hover:border-cdp-blue transition-all overflow-hidden flex flex-col group">
                    <div class="h-44 lg:h-48 overflow-hidden relative">
                        <img src="https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?auto=format&fit=crop&w=800&q=80" 
                             alt="Emplasemen Kereta Api Antarmoda" 
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-cyan-700/90 backdrop-blur-xs text-white text-[10px] font-bold px-2.5 py-1 rounded-md shadow-xs">
                            Rel Antarmoda KA
                        </span>
                    </div>
                    <div class="p-5 lg:p-6 flex-1 flex flex-col justify-between space-y-3.5">
                        <div class="space-y-1.5">
                            <h3 class="font-bold text-sm lg:text-base text-slate-900 group-hover:text-cdp-blue transition">
                                Emplasemen Jalur Rel Kereta Api
                            </h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Jalur rel ganda khusus terhubung langsung ke koridor rel nasional menuju Pelabuhan Tanjung Priok.
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-1.5 pt-3 border-t border-slate-100 text-[10.5px] font-semibold text-slate-700">
                            <span class="bg-slate-100 px-2.5 py-0.5 rounded-md">Double Track Siding</span>
                            <span class="bg-slate-100 px-2.5 py-0.5 rounded-md">30–60 TEU / Rangkaian</span>
                            <span class="bg-slate-100 px-2.5 py-0.5 rounded-md">Gerbong PPCW</span>
                        </div>
                    </div>
                </div>

                <!-- Fasilitas 3: Pusat Logistik Berikat & CFS -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-lg hover:border-cdp-blue transition-all overflow-hidden flex flex-col group">
                    <div class="h-44 lg:h-48 overflow-hidden relative">
                        <img src="https://images.unsplash.com/photo-1587293852726-70cdb56c2866?auto=format&fit=crop&w=800&q=80" 
                             alt="Gudang Berikat & CFS" 
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-amber-700/90 backdrop-blur-xs text-white text-[10px] font-bold px-2.5 py-1 rounded-md shadow-xs">
                            Gudang CFS &amp; PLB
                        </span>
                    </div>
                    <div class="p-5 lg:p-6 flex-1 flex flex-col justify-between space-y-3.5">
                        <div class="space-y-1.5">
                            <h3 class="font-bold text-sm lg:text-base text-slate-900 group-hover:text-cdp-blue transition">
                                Pusat Logistik Berikat &amp; Gudang CFS
                            </h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Fasilitas penangguhan bea masuk serta gudang konsolidasi &amp; dekonsolidasi muatan LCL/FCL.
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-1.5 pt-3 border-t border-slate-100 text-[10.5px] font-semibold text-slate-700">
                            <span class="bg-slate-100 px-2.5 py-0.5 rounded-md">Rampa D1–D5 Hidrolik</span>
                            <span class="bg-slate-100 px-2.5 py-0.5 rounded-md">Konsolidasi LCL</span>
                            <span class="bg-slate-100 px-2.5 py-0.5 rounded-md">WMS &amp; CCTV 24 Jam</span>
                        </div>
                    </div>
                </div>

                <!-- Fasilitas 4: Depo Rantai Dingin (Reefer Hub) -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-lg hover:border-cdp-blue transition-all overflow-hidden flex flex-col group">
                    <div class="h-44 lg:h-48 overflow-hidden relative">
                        <img src="https://images.unsplash.com/photo-1586528116493-a029325540fa?auto=format&fit=crop&w=800&q=80" 
                             alt="Depo Peti Kemas Reefer" 
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-blue-700/90 backdrop-blur-xs text-white text-[10px] font-bold px-2.5 py-1 rounded-md shadow-xs">
                            Cold Chain Hub
                        </span>
                    </div>
                    <div class="p-5 lg:p-6 flex-1 flex flex-col justify-between space-y-3.5">
                        <div class="space-y-1.5">
                            <h3 class="font-bold text-sm lg:text-base text-slate-900 group-hover:text-cdp-blue transition">
                                Depo Rantai Dingin (Reefer Hub)
                            </h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Stasiun penanganan peti kemas berpendingin untuk komoditas sensitif suhu (-20&deg;C stabil).
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-1.5 pt-3 border-t border-slate-100 text-[10.5px] font-semibold text-slate-700">
                            <span class="bg-slate-100 px-2.5 py-0.5 rounded-md">300+ Reefer Plugs</span>
                            <span class="bg-slate-100 px-2.5 py-0.5 rounded-md">Telemetri Suhu -20&deg;C</span>
                            <span class="bg-slate-100 px-2.5 py-0.5 rounded-md">Backup Genset 24 Jam</span>
                        </div>
                    </div>
                </div>

                <!-- Fasilitas 5: Gerbang Cerdas & Timbangan VGM SOLAS -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-lg hover:border-cdp-blue transition-all overflow-hidden flex flex-col group">
                    <div class="h-44 lg:h-48 overflow-hidden relative">
                        <img src="https://images.unsplash.com/photo-1519003722824-194d4455a60c?auto=format&fit=crop&w=800&q=80" 
                             alt="Gerbang Cerdas & Jembatan Timbang" 
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-emerald-700/90 backdrop-blur-xs text-white text-[10px] font-bold px-2.5 py-1 rounded-md shadow-xs">
                            Gerbang Cerdas &amp; VGM
                        </span>
                    </div>
                    <div class="p-5 lg:p-6 flex-1 flex flex-col justify-between space-y-3.5">
                        <div class="space-y-1.5">
                            <h3 class="font-bold text-sm lg:text-base text-slate-900 group-hover:text-cdp-blue transition">
                                Gerbang Cerdas &amp; Timbangan VGM
                            </h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Pintu masuk-keluar otomatis berbasis kamera OCR nomor kontainer, ANPR truk, dan timbangan SOLAS.
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-1.5 pt-3 border-t border-slate-100 text-[10.5px] font-semibold text-slate-700">
                            <span class="bg-slate-100 px-2.5 py-0.5 rounded-md">Kamera OCR ISO 6346</span>
                            <span class="bg-slate-100 px-2.5 py-0.5 rounded-md">Timbangan 80T SOLAS</span>
                            <span class="bg-slate-100 px-2.5 py-0.5 rounded-md">e-Pass Instan</span>
                        </div>
                    </div>
                </div>

                <!-- Fasilitas 6: Armada Penanganan Lapangan -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-lg hover:border-cdp-blue transition-all overflow-hidden flex flex-col group">
                    <div class="h-44 lg:h-48 overflow-hidden relative">
                        <img src="https://images.unsplash.com/photo-1578575437130-527eed3abbec?auto=format&fit=crop&w=800&q=80" 
                             alt="Armada Alat Angkat Lapangan" 
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-indigo-700/90 backdrop-blur-xs text-white text-[10px] font-bold px-2.5 py-1 rounded-md shadow-xs">
                            Armada Alat Berat
                        </span>
                    </div>
                    <div class="p-5 lg:p-6 flex-1 flex flex-col justify-between space-y-3.5">
                        <div class="space-y-1.5">
                            <h3 class="font-bold text-sm lg:text-base text-slate-900 group-hover:text-cdp-blue transition">
                                Armada Reach Stacker &amp; RTG 45 Ton
                            </h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Unit reach stacker Kalmar Gloria dan RTG berteknologi telemetri sensor twistlock dan GPS presisi.
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-1.5 pt-3 border-t border-slate-100 text-[10.5px] font-semibold text-slate-700">
                            <span class="bg-slate-100 px-2.5 py-0.5 rounded-md">Daya Angkat 45 Ton</span>
                            <span class="bg-slate-100 px-2.5 py-0.5 rounded-md">Sensor Twistlock</span>
                            <span class="bg-slate-100 px-2.5 py-0.5 rounded-md">Navigasi DGPS RTK</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 5. TIGA ALUR OPERASIONAL TERINTEGRASI -->
    <!-- =================================================================== -->
    <section id="tiga-alur" class="py-16 lg:py-20 bg-white border-b border-slate-200">
        <div class="max-w-[1680px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12">
            
            <div class="max-w-3xl mx-auto text-center space-y-3 mb-12 lg:mb-16">
                <div class="inline-flex items-center space-x-2 px-3.5 py-1 rounded-full bg-orange-50 text-orange-600 text-xs font-bold border border-orange-200/80 uppercase tracking-wider">
                    <i class="fa-solid fa-diagram-project"></i>
                    <span>Arsitektur End-to-End</span>
                </div>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    3 Alur Operasional Terintegrasi
                </h2>
                <p class="text-xs sm:text-sm lg:text-base text-slate-600 leading-relaxed max-w-2xl mx-auto">
                    Setiap gerakan fisik di lapangan memicu transmisi telemetri IoT dan mencatat faktur finansial ERP secara otomatis.
                </p>
            </div>

            <!-- Tiga Pilar Komparasi -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 xl:gap-8">
                
                <!-- Pilar 1: Alur Fisik Lapangan -->
                <div class="bg-gradient-to-b from-blue-50/40 to-white rounded-2xl border border-blue-200/80 p-6 lg:p-7 flex flex-col justify-between shadow-xs">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="w-8 h-8 rounded-lg bg-cdp-navy text-white flex items-center justify-center font-bold text-xs shadow-xs">01</span>
                            <span class="text-[10.5px] font-bold text-cdp-navy bg-blue-100 px-2.5 py-0.5 rounded-full uppercase tracking-wider">Lapangan Fisik</span>
                        </div>
                        <div>
                            <h3 class="text-base lg:text-lg font-bold text-slate-900">Alur Fisik Lapangan</h3>
                            <p class="text-xs text-slate-600 mt-1">Pergerakan fisik truk kontainer, rangkaian KA kargo, dan reach stacker di lapangan.</p>
                        </div>

                        <div class="space-y-2.5 pt-3 border-t border-slate-200/60 text-xs">
                            <div class="flex items-start space-x-2.5">
                                <span class="w-5 h-5 rounded-full bg-blue-100 text-cdp-navy flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">1</span>
                                <div>
                                    <strong class="text-slate-800">Gerbang Masuk:</strong>
                                    <span class="text-slate-500">Truk tiba di in-gate dan penimbangan 80T SOLAS VGM.</span>
                                </div>
                            </div>
                            <div class="flex items-start space-x-2.5">
                                <span class="w-5 h-5 rounded-full bg-blue-100 text-cdp-navy flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">2</span>
                                <div>
                                    <strong class="text-slate-800">Penataan Yard:</strong>
                                    <span class="text-slate-500">Penempatan di slot Blok-Bay-Row-Tier (heaviest-on-bottom).</span>
                                </div>
                            </div>
                            <div class="flex items-start space-x-2.5">
                                <span class="w-5 h-5 rounded-full bg-blue-100 text-cdp-navy flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">3</span>
                                <div>
                                    <strong class="text-slate-800">Alih Muat Rel:</strong>
                                    <span class="text-slate-500">Transfer kontainer ke gerbong KA Logistik ke Priok.</span>
                                </div>
                            </div>
                            <div class="flex items-start space-x-2.5">
                                <span class="w-5 h-5 rounded-full bg-blue-100 text-cdp-navy flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">4</span>
                                <div>
                                    <strong class="text-slate-800">Gerbang Keluar:</strong>
                                    <span class="text-slate-500">Verifikasi dokumen SPPB pabean dan pelepasan rilis out-gate.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-semibold">
                        <span>Fokus: Ketepatan Fisik &amp; K3</span>
                        <i class="fa-solid fa-truck-ramp-box text-cdp-navy text-xs"></i>
                    </div>
                </div>

                <!-- Pilar 2: Alur Informasi & Telemetri IoT -->
                <div class="bg-gradient-to-b from-cyan-50/40 to-white rounded-2xl border border-cyan-200/80 p-6 lg:p-7 flex flex-col justify-between shadow-xs">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="w-8 h-8 rounded-lg bg-cdp-blue text-white flex items-center justify-center font-bold text-xs shadow-xs">02</span>
                            <span class="text-[10.5px] font-bold text-cdp-blue bg-cyan-100 px-2.5 py-0.5 rounded-full uppercase tracking-wider">Telemetri &amp; Data</span>
                        </div>
                        <div>
                            <h3 class="text-base lg:text-lg font-bold text-slate-900">Alur Informasi &amp; IoT</h3>
                            <p class="text-xs text-slate-600 mt-1">Transmisi data telemetri otomatis dari perangkat lapangan ke konsol YMS.</p>
                        </div>

                        <div class="space-y-2.5 pt-3 border-t border-slate-200/60 text-xs">
                            <div class="flex items-start space-x-2.5">
                                <span class="w-5 h-5 rounded-full bg-cyan-100 text-cdp-blue flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">1</span>
                                <div>
                                    <strong class="text-slate-800">OCR &amp; ANPR:</strong>
                                    <span class="text-slate-500">Pemindaian otomatis kode ISO kontainer &amp; plat truk (&gt;99%).</span>
                                </div>
                            </div>
                            <div class="flex items-start space-x-2.5">
                                <span class="w-5 h-5 rounded-full bg-cyan-100 text-cdp-blue flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">2</span>
                                <div>
                                    <strong class="text-slate-800">Sensor Twistlock:</strong>
                                    <span class="text-slate-500">Deteksi penguncian spreader reach stacker secara otomatis.</span>
                                </div>
                            </div>
                            <div class="flex items-start space-x-2.5">
                                <span class="w-5 h-5 rounded-full bg-cyan-100 text-cdp-blue flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">3</span>
                                <div>
                                    <strong class="text-slate-800">Payload JSON:</strong>
                                    <span class="text-slate-500">Transmisi paket telemetri via jaringan lokal ke broker YMS.</span>
                                </div>
                            </div>
                            <div class="flex items-start space-x-2.5">
                                <span class="w-5 h-5 rounded-full bg-cyan-100 text-cdp-blue flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">4</span>
                                <div>
                                    <strong class="text-slate-800">Digital Twin 3D:</strong>
                                    <span class="text-slate-500">Pembaruan posisi visual denah lapangan secara instan.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-semibold">
                        <span>Fokus: Visibilitas Waktu-Nyata</span>
                        <i class="fa-solid fa-network-wired text-cdp-blue text-xs"></i>
                    </div>
                </div>

                <!-- Pilar 3: Alur Finansial Sistem ERP -->
                <div class="bg-gradient-to-b from-emerald-50/40 to-white rounded-2xl border border-emerald-200/80 p-6 lg:p-7 flex flex-col justify-between shadow-xs">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">03</span>
                            <span class="text-[10.5px] font-bold text-emerald-700 bg-emerald-100 px-2.5 py-0.5 rounded-full uppercase tracking-wider">Keuangan ERP</span>
                        </div>
                        <div>
                            <h3 class="text-base lg:text-lg font-bold text-slate-900">Alur Finansial (Sistem ERP)</h3>
                            <p class="text-xs text-slate-600 mt-1">Penetapan tarif dan penerbitan faktur otomatis berbasis rekaman aktivitas lapangan.</p>
                        </div>

                        <div class="space-y-2.5 pt-3 border-t border-slate-200/60 text-xs">
                            <div class="flex items-start space-x-2.5">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">1</span>
                                <div>
                                    <strong class="text-slate-800">Event Billing:</strong>
                                    <span class="text-slate-500">Pemicu tarif otomatis pada setiap aktivitas Lift-On / Lift-Off.</span>
                                </div>
                            </div>
                            <div class="flex items-start space-x-2.5">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">2</span>
                                <div>
                                    <strong class="text-slate-800">Biaya VGM:</strong>
                                    <span class="text-slate-500">Pembebanan jasa penimbangan resmi IMO SOLAS ke tagihan shipper.</span>
                                </div>
                            </div>
                            <div class="flex items-start space-x-2.5">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">3</span>
                                <div>
                                    <strong class="text-slate-800">Tarif Penumpukan:</strong>
                                    <span class="text-slate-500">Perhitungan harian masa inap (dwell time) terhitung otomatis.</span>
                                </div>
                            </div>
                            <div class="flex items-start space-x-2.5">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">4</span>
                                <div>
                                    <strong class="text-slate-800">Faktur ERP:</strong>
                                    <span class="text-slate-500">Penerbitan faktur komersial langsung tersinkronisasi ke jurnal umum.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-semibold">
                        <span>Fokus: Nir-Kebocoran Pendapatan</span>
                        <i class="fa-solid fa-file-invoice-dollar text-emerald-600 text-xs"></i>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 6. CATATAN KAKI RESMI (FOOTER KORPORAT) -->
    <!-- =================================================================== -->
    <footer class="bg-cdp-dark text-slate-300 text-xs py-10 lg:py-12 border-t border-slate-800">
        <div class="max-w-[1680px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 lg:gap-8 mb-8">
                
                <!-- Kolom 1: Merek & Profil -->
                <div class="md:col-span-2 space-y-2.5">
                    <div class="flex items-center space-x-3">
                        <img src="assets/img/logo.png" alt="Conclusion Logo" class="w-8 h-8 object-contain bg-white rounded-lg p-0.5">
                        <div>
                            <span class="font-extrabold text-sm lg:text-base text-white tracking-tight">CONCLUSION</span>
                            <span class="block text-[9px] font-bold text-orange-400 uppercase tracking-widest">Supply Chain Consultant</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 max-w-md leading-relaxed">
                        Aplikasi percontohan Yard Management System (YMS) dry port 35 Ha terintegrasi otomasi lapangan, telemetri IoT, dan penagihan ERP.
                    </p>
                </div>

                <!-- Kolom 2: Navigasi Cepat -->
                <div class="space-y-2">
                    <span class="text-xs font-bold text-white uppercase tracking-wider block">Menu</span>
                    <ul class="space-y-1.5 text-xs text-slate-400">
                        <li><a href="#solusi" class="hover:text-white transition">Solusi Dry Port</a></li>
                        <li><a href="#fasilitas" class="hover:text-white transition">Fasilitas 35 Ha</a></li>
                        <li><a href="#tiga-alur" class="hover:text-white transition">3 Alur Operasional</a></li>
                    </ul>
                </div>

                <!-- Kolom 3: Kontak & Demonstrasi -->
                <div class="space-y-2">
                    <span class="text-xs font-bold text-white uppercase tracking-wider block">Akses Demonstrasi</span>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Gunakan akun demo terdaftar untuk menguji fitur penataan lapangan dan telemetri.
                    </p>
                    <button type="button" onclick="openLoginModal()" 
                            class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-3.5 py-1.5 rounded-lg text-xs font-bold shadow-sm shadow-orange-500/25 transition-all inline-flex items-center space-x-1.5">
                        <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                        <span>Masuk Demo</span>
                    </button>
                </div>

            </div>

            <div class="pt-6 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-slate-500">
                <div>
                    &copy; 2026 <strong>Conclusion Supply Chain Consultant</strong> &bull; Inland Dry Port Yard Management System.
                </div>
                <div class="flex items-center space-x-3">
                    <span>Yard Management System</span>
                    <span>&bull;</span>
                    <span>Intermodal Dry Port 35 Ha</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- =================================================================== -->
    <!-- 6. MODAL MASUK DEMONSTRASI YMS (CLEAN & MINIMALIST) -->
    <!-- =================================================================== -->
    <div id="loginModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs <?= empty($error_message) ? 'hidden' : ''; ?> flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden animate-fadeIn font-sans">
            
            <!-- Kepala Modal -->
            <div class="bg-gradient-to-r from-[#002f5e] via-[#0170b9] to-orange-600 px-6 py-4 text-white flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-white/10 backdrop-blur-xs flex items-center justify-center border border-white/20 p-1">
                        <img src="assets/img/logo.png" alt="Conclusion Logo" class="w-full h-full object-contain" onerror="this.src='https://via.placeholder.com/32?text=C'">
                    </div>
                    <div>
                        <h3 class="font-bold text-sm leading-tight">Masuk ke Sistem</h3>
                        <p class="text-[10.5px] text-orange-200">CIDP Yard Management System</p>
                    </div>
                </div>
                <button type="button" onclick="closeLoginModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center transition-colors" title="Tutup Modal">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Badan Modal Form -->
            <div class="p-6 space-y-4">
                
                <!-- Pesan Kesalahan jika login tidak valid -->
                <?php if (!empty($error_message)): ?>
                    <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700 flex items-start space-x-2">
                        <i class="fa-solid fa-circle-exclamation text-base flex-shrink-0 mt-0.5"></i>
                        <div><?= htmlspecialchars($error_message); ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" id="formLoginModal" class="space-y-4">
                    <div>
                        <label for="modalEmail" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Email
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none text-xs">
                                <i class="fa-regular fa-envelope"></i>
                            </span>
                            <input type="email" id="modalEmail" name="email" value="admin@cidp.ac.id" required 
                                   placeholder="admin@cidp.ac.id"
                                   class="w-full pl-9 pr-3.5 py-2.5 text-xs text-gray-800 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none bg-gray-50/60 font-mono transition">
                        </div>
                    </div>

                    <div>
                        <label for="modalPassword" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Kata Sandi
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none text-xs">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" id="modalPassword" name="password" value="admin123" required 
                                   placeholder="••••••••"
                                   class="w-full pl-9 pr-10 py-2.5 text-xs text-gray-800 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none bg-gray-50/60 font-mono transition">
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 text-xs focus:outline-none" title="Lihat/Sembunyikan Kata Sandi">
                                <i class="fa-regular fa-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" name="btn_login" 
                                class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white text-xs font-bold shadow-md shadow-orange-500/25 transition-all flex items-center justify-center space-x-2">
                            <span>Masuk ke Dashboard</span>
                            <i class="fa-solid fa-arrow-right text-[11px]"></i>
                        </button>
                    </div>
                </form>

                <div class="pt-2 text-center border-t border-gray-100">
                    <p class="text-[11px] text-gray-400">
                        Akun Demo: <span class="font-mono text-orange-600 font-bold">admin@cidp.ac.id</span> &bull; <span class="font-mono text-orange-600 font-bold">admin123</span>
                    </p>
                </div>

            </div>

        </div>
    </div>

    <!-- =================================================================== -->
    <!-- 7. SKRIP LOGIKA CAROUSEL OTOMATIS & MODAL MASUK -->
    <!-- =================================================================== -->
    <script>
        // Logika Carousel Otomatis (Smooth Auto-Slide)
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const dots = document.querySelectorAll('.dot-indicator');
        const totalSlides = slides.length;
        let slideInterval = null;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                if (i === index) {
                    slide.classList.remove('inactive');
                    slide.classList.add('active');
                } else {
                    slide.classList.remove('active');
                    slide.classList.add('inactive');
                }
            });

            dots.forEach((dot, i) => {
                if (i === index) {
                    dot.classList.add('active', 'w-7', 'bg-white');
                    dot.classList.remove('w-2.5', 'bg-white/50');
                } else {
                    dot.classList.remove('active', 'w-7', 'bg-white');
                    dot.classList.add('w-2.5', 'bg-white/50');
                }
            });

            currentSlide = index;
        }

        function nextSlide() {
            const next = (currentSlide + 1) % totalSlides;
            showSlide(next);
            resetSlideTimer();
        }

        function prevSlide() {
            const prev = (currentSlide - 1 + totalSlides) % totalSlides;
            showSlide(prev);
            resetSlideTimer();
        }

        function goToSlide(index) {
            showSlide(index);
            resetSlideTimer();
        }

        function startSlideTimer() {
            slideInterval = setInterval(nextSlide, 5500); // Bergeser otomatis setiap 5,5 detik secara halus
        }

        function resetSlideTimer() {
            clearInterval(slideInterval);
            startSlideTimer();
        }

        // Jalankan carousel otomatis
        startSlideTimer();

        // Jeda auto-slide saat kursor berada di atas banner
        const carouselSection = document.getElementById('heroCarousel');
        carouselSection.addEventListener('mouseenter', () => clearInterval(slideInterval));
        carouselSection.addEventListener('mouseleave', () => startSlideTimer());

        // Logika Modal Masuk
        function openLoginModal() {
            document.getElementById('loginModal').classList.remove('hidden');
        }

        function closeLoginModal() {
            document.getElementById('loginModal').classList.add('hidden');
        }

        function setCredentials(email, password) {
            document.getElementById('modalEmail').value = email;
            document.getElementById('modalPassword').value = password;
        }

        function quickSingleDoorLogin() {
            document.getElementById('modalEmail').value = 'admin@cidp.ac.id';
            document.getElementById('modalPassword').value = 'admin123';
            const form = document.getElementById('formLoginModal');
            if (form) {
                // Buat hidden input btn_login agar terdeteksi oleh PHP
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'btn_login';
                hiddenInput.value = '1';
                form.appendChild(hiddenInput);
                form.submit();
            }
        }

        function togglePasswordVisibility() {
            const pwdInput = document.getElementById('modalPassword');
            const pwdIcon = document.getElementById('togglePasswordIcon');
            if (!pwdInput) return;
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                if (pwdIcon) pwdIcon.className = 'fa-regular fa-eye-slash';
            } else {
                pwdInput.type = 'password';
                if (pwdIcon) pwdIcon.className = 'fa-regular fa-eye';
            }
        }

        // Menutup modal apabila tombol Escape ditekan
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeLoginModal();
        });
    </script>
</body>
</html>