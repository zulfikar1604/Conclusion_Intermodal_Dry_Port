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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conclusion Supply Chain Consultant &bull; Demonstrasi Yard Management System</title>
    
    <!-- Favicon / Ikon Tab Peramban (Browser Tab Icon) -->
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link rel="shortcut icon" type="image/png" href="assets/img/logo.png">
    <link rel="apple-touch-icon" href="assets/img/logo.png">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cdp: {
                            navy: '#004b87',
                            dark: '#002f5e',
                            blue: '#0170b9',
                            light: '#f4f8fc'
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace']
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome 6 CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            background-color: #ffffff;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-cdp-blue selection:text-white">

    <!-- =================================================================== -->
    <!-- 1. BILAH NAVIGASI ATAS (Standar Cikarang Dry Port) -->
    <!-- =================================================================== -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-4">
            
            <!-- LOGO MEREK: Conclusion Supply Chain Consultant -->
            <a href="login.php" class="flex items-center space-x-3 group flex-shrink-0">
                <img src="assets/img/logo.png" alt="Conclusion Logo" class="w-10 h-10 object-contain transition-transform group-hover:scale-105">
                <div>
                    <div class="font-extrabold text-base sm:text-lg text-cdp-navy tracking-tight leading-none">
                        CONCLUSION
                    </div>
                    <div class="text-[10px] font-bold text-cdp-blue tracking-wider uppercase mt-0.5">
                        Supply Chain Consultant
                    </div>
                </div>
            </a>

            <!-- TAUTAN MENU UTAMA -->
            <nav class="hidden lg:flex items-center space-x-7 text-xs font-bold text-slate-700">
                <a href="#tentang" class="hover:text-cdp-blue transition">Tentang Kami</a>
                <a href="#fasilitas" class="hover:text-cdp-blue transition">Fasilitas &amp; Solusi</a>
                <a href="#tiga-alur" class="hover:text-cdp-blue transition">3 Alur Operasional</a>
                <a href="#tim" class="hover:text-cdp-blue transition">Tim Konsultan</a>
            </nav>

            <!-- BAGIAN KANAN: PENCARIAN & TOMBOL MASUK DEMO YMS -->
            <div class="flex items-center space-x-3">
                <div class="hidden sm:flex items-center bg-slate-100 rounded-full px-3 py-1.5 border border-slate-200 text-xs text-slate-500">
                    <i class="fa-solid fa-magnifying-glass mr-2 text-slate-400"></i>
                    <input type="text" placeholder="Masukkan No. Peti Kemas / B/L" class="bg-transparent border-none outline-none text-slate-700 w-48 text-xs">
                    <span class="bg-white px-2 py-0.5 rounded-full text-[10px] font-bold text-slate-600 border ml-1 shadow-2xs">Peti Kemas</span>
                </div>

                <button type="button" onclick="openLoginModal()" 
                        class="bg-cdp-navy hover:bg-cdp-blue text-white px-4 py-2 rounded-lg text-xs font-bold shadow-sm transition flex items-center space-x-2">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    <span>Masuk Demo YMS</span>
                </button>
            </div>

        </div>
    </header>

    <!-- =================================================================== -->
    <!-- 2. CAROUSEL BANNER OTOMATIS BERGESER (SMOOTH DRY PORT SLIDER) -->
    <!-- =================================================================== -->
    <section id="heroCarousel" class="relative overflow-hidden min-h-[560px] bg-slate-900 text-white select-none">

        <!-- SLIDE 1: Terminal Peti Kemas & Derek Gantry Pelabuhan Kering (Outdoor Dry Port) -->
        <div class="carousel-slide active absolute inset-0 flex items-center justify-center text-center px-4 sm:px-6"
             style="background-image: linear-gradient(rgba(0, 47, 94, 0.78), rgba(0, 75, 135, 0.85)), url('https://images.unsplash.com/photo-1605371924599-2d0365da1ae0?auto=format&fit=crop&w=2000&q=80');">
            <div class="max-w-3xl mx-auto space-y-5 py-20">
                <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md text-xs font-semibold text-blue-100 border border-white/20">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Aplikasi Demonstrasi &bull; Prototipe Yard Management System (YMS)</span>
                </div>

                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white leading-tight">
                    Simulasi Sistem Manajemen Lapangan Peti Kemas
                </h1>

                <p class="text-sm sm:text-base text-blue-100 font-normal leading-relaxed max-w-2xl mx-auto">
                    Terhubung melalui moda rel kereta api dan jalan raya, kami menyusun rekomendasi arsitektur pelabuhan kering modern. Aplikasi percontohan (demo) ini menyimulasikan integrasi perangkat keras IoT, pemetaan lapangan 3D, dan rekonsiliasi finansial ERP.
                </p>

                <div class="pt-3">
                    <button type="button" onclick="openLoginModal()"
                            class="bg-white hover:bg-slate-100 text-cdp-navy font-extrabold px-8 py-3.5 rounded-lg text-xs sm:text-sm shadow-xl transition transform hover:-translate-y-0.5 inline-flex items-center space-x-2">
                        <span>Uji Coba Demonstrasi YMS</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- SLIDE 2: Rel Kereta Api Logistik (KAI Logistik & Intermodal Siding) -->
        <div class="carousel-slide inactive absolute inset-0 flex items-center justify-center text-center px-4 sm:px-6"
             style="background-image: linear-gradient(rgba(0, 47, 94, 0.78), rgba(0, 75, 135, 0.85)), url('https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?auto=format&fit=crop&w=2000&q=80');">
            <div class="max-w-3xl mx-auto space-y-5 py-20">
                <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md text-xs font-semibold text-cyan-200 border border-white/20">
                    <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                    <span>Konektivitas Rel Kereta Api Antarmoda &bull; Double Track Siding</span>
                </div>

                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white leading-tight">
                    Integrasi Angkutan Rel Peti Kemas Antarmoda
                </h1>

                <p class="text-sm sm:text-base text-blue-100 font-normal leading-relaxed max-w-2xl mx-auto">
                    Menghubungkan pusat kawasan industri langsung ke pelabuhan laut Tanjung Priok melalui jalur rel kereta api barang berkapasitas 30 hingga 60 TEUs per rangkaian untuk memangkas kongesti jalan raya.
                </p>

                <div class="pt-3">
                    <button type="button" onclick="openLoginModal()"
                            class="bg-white hover:bg-slate-100 text-cdp-navy font-extrabold px-8 py-3.5 rounded-lg text-xs sm:text-sm shadow-xl transition transform hover:-translate-y-0.5 inline-flex items-center space-x-2">
                        <span>Pelajari Solusi Antarmoda YMS</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- SLIDE 3: Penataan Lapangan Peti Kemas 3D & Alat Angkat Kalmar -->
        <div class="carousel-slide inactive absolute inset-0 flex items-center justify-center text-center px-4 sm:px-6"
             style="background-image: linear-gradient(rgba(0, 47, 94, 0.78), rgba(0, 75, 135, 0.85)), url('https://images.unsplash.com/photo-1578575437130-527eed3abbec?auto=format&fit=crop&w=2000&q=80');">
            <div class="max-w-3xl mx-auto space-y-5 py-20">
                <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md text-xs font-semibold text-amber-200 border border-white/20">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    <span>Optimasi Penataan Lapangan &bull; Aturan Heaviest-on-Bottom</span>
                </div>

                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white leading-tight">
                    Pemetaan Presisi Koordinat Blok, Bay, Row, dan Tier
                </h1>

                <p class="text-sm sm:text-base text-blue-100 font-normal leading-relaxed max-w-2xl mx-auto">
                    Mencegah pergeseran kontainer liar (*unproductive shuffling*) dengan pemetaan visual lapangan 3D dan sensor telemetri RTK pada alat angkat *reach stacker* Kalmar Gloria.
                </p>

                <div class="pt-3">
                    <button type="button" onclick="openLoginModal()"
                            class="bg-white hover:bg-slate-100 text-cdp-navy font-extrabold px-8 py-3.5 rounded-lg text-xs sm:text-sm shadow-xl transition transform hover:-translate-y-0.5 inline-flex items-center space-x-2">
                        <span>Simulasikan Penataan Lapangan</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- SLIDE 4: Gerbang Cerdas OCR & Integrasi Penagihan Sistem ERP -->
        <div class="carousel-slide inactive absolute inset-0 flex items-center justify-center text-center px-4 sm:px-6"
             style="background-image: linear-gradient(rgba(0, 47, 94, 0.78), rgba(0, 75, 135, 0.85)), url('https://images.unsplash.com/photo-1519003722824-194d4455a60c?auto=format&fit=crop&w=2000&q=80');">
            <div class="max-w-3xl mx-auto space-y-5 py-20">
                <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md text-xs font-semibold text-emerald-200 border border-white/20">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Otomasi Gerbang Cerdas &bull; Rekonsiliasi Finansial ERP 100%</span>
                </div>

                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white leading-tight">
                    Otomasi Gerbang Masuk &amp; Integrasi Sistem ERP
                </h1>

                <p class="text-sm sm:text-base text-blue-100 font-normal leading-relaxed max-w-2xl mx-auto">
                    Kamera OCR ISO 6346 dan jembatan timbang terintegrasi secara instan dengan modul akuntansi sistem ERP terpadu untuk menerbitkan faktur komersial otomatis tanpa potensi kebocoran pendapatan.
                </p>

                <div class="pt-3">
                    <button type="button" onclick="openLoginModal()"
                            class="bg-white hover:bg-slate-100 text-cdp-navy font-extrabold px-8 py-3.5 rounded-lg text-xs sm:text-sm shadow-xl transition transform hover:-translate-y-0.5 inline-flex items-center space-x-2">
                        <span>Eksplorasi Rekonsiliasi Finansial</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- TOMBOL NAVIGASI MANUAL KIRI & KANAN -->
        <button type="button" onclick="prevSlide()" aria-label="Slide Sebelumnya"
                class="absolute left-4 sm:left-8 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-black/30 hover:bg-black/60 text-white flex items-center justify-center transition backdrop-blur-xs z-20">
            <i class="fa-solid fa-chevron-left text-sm"></i>
        </button>
        <button type="button" onclick="nextSlide()" aria-label="Slide Berikutnya"
                class="absolute right-4 sm:right-8 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-black/30 hover:bg-black/60 text-white flex items-center justify-center transition backdrop-blur-xs z-20">
            <i class="fa-solid fa-chevron-right text-sm"></i>
        </button>

        <!-- INDIKATOR TITIK (DOTS) DI BAGIAN BAWAH CAROUSEL -->
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex items-center space-x-2.5 z-20">
            <button type="button" onclick="goToSlide(0)" class="dot-indicator active w-7 h-2.5 rounded-full bg-white transition-all"></button>
            <button type="button" onclick="goToSlide(1)" class="dot-indicator w-2.5 h-2.5 rounded-full bg-white/50 hover:bg-white/80 transition-all"></button>
            <button type="button" onclick="goToSlide(2)" class="dot-indicator w-2.5 h-2.5 rounded-full bg-white/50 hover:bg-white/80 transition-all"></button>
            <button type="button" onclick="goToSlide(3)" class="dot-indicator w-2.5 h-2.5 rounded-full bg-white/50 hover:bg-white/80 transition-all"></button>
        </div>

    </section>

    <!-- =================================================================== -->
    <!-- 3. TENTANG KAMI: PERAN STRATEGIS KONSULTAN RANTAI PASOK -->
    <!-- =================================================================== -->
    <section id="tentang" class="py-20 bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            
            <div class="max-w-3xl mx-auto text-center space-y-3 mb-16">
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-blue-50 text-cdp-blue text-xs font-bold border border-blue-100 uppercase tracking-wider">
                    <i class="fa-solid fa-compass-drafting"></i>
                    <span>Profil Konsultan &bull; Conclusion Supply Chain Consultant</span>
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Mitra Strategis Pengembangan Pelabuhan Kering Antarmoda
                </h2>
                <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
                    Kami mendesain dan mengarahkan implementasi solusi logistik terpadu yang menghubungkan simpul pelabuhan laut utama dengan sentra industri manufaktur. Melalui aplikasi percontohan <strong>Yard Management System (YMS)</strong> ini, kami membuktikan bagaimana efisiensi operasional, keandalan telemetri, dan kepatuhan finansial dapat dicapai secara nyata.
                </p>
            </div>

            <!-- 4 Nilai Unggulan Konsultansi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-cdp-blue hover:shadow-md transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-cdp-navy flex items-center justify-center text-xl mb-4 group-hover:bg-cdp-navy group-hover:text-white transition">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <h3 class="font-bold text-base text-slate-900 mb-2">Reduksi Waktu Inap</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Memangkas masa inap kontainer (<em>dwelling time</em>) di pelabuhan laut hingga 40% melalui pemindahan izin kepabeanan langsung ke pelabuhan kering pedalaman.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-cdp-blue hover:shadow-md transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-cyan-100 text-cdp-blue flex items-center justify-center text-xl mb-4 group-hover:bg-cdp-blue group-hover:text-white transition">
                        <i class="fa-solid fa-train-subway"></i>
                    </div>
                    <h3 class="font-bold text-base text-slate-900 mb-2">Konektivitas Rel Barang</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pengalihan beban kargo dari jalan raya ke jalur rel ganda (<em>double track siding</em>) berkapasitas masif guna menghemat biaya logistik dan emisi karbon.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-cdp-blue hover:shadow-md transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl mb-4 group-hover:bg-amber-600 group-hover:text-white transition">
                        <i class="fa-solid fa-microchip"></i>
                    </div>
                    <h3 class="font-bold text-base text-slate-900 mb-2">Otomasi Telemetri IoT</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Integrasi sensor <em>twistlock</em>, penimbangan bersertifikasi VGM SOLAS, dan pemindai optik OCR kamera untuk pelacakan kontainer secara waktu-nyata.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-cdp-blue hover:shadow-md transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl mb-4 group-hover:bg-emerald-600 group-hover:text-white transition">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h3 class="font-bold text-base text-slate-900 mb-2">Nir-Kebocoran Finansial</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Sinkronisasi instan setiap manuver alat lapangan ke modul tagihan komersial sistem ERP guna menjamin nol kebocoran pendapatan (<em>zero revenue leakage</em>).
                    </p>
                </div>

            </div>

        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 4. FASILITAS & SOLUSI TERPADU (GAYA CIKARANG DRY PORT) -->
    <!-- =================================================================== -->
    <section id="fasilitas" class="py-20 bg-slate-50 border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            
            <div class="max-w-3xl mx-auto text-center space-y-3 mb-16">
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-cdp-navy/10 text-cdp-navy text-xs font-bold border border-cdp-navy/20 uppercase tracking-wider">
                    <i class="fa-solid fa-warehouse"></i>
                    <span>Infrastruktur &amp; Kapabilitas Layanan</span>
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Fasilitas &amp; Solusi Pelabuhan Kering Antarmoda
                </h2>
                <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
                    Dirancang dengan standar operasional terminal peti kemas internasional, berikut adalah enam infrastruktur utama yang disimulasikan dan diintegrasikan oleh <strong>Conclusion Supply Chain Consultant</strong>.
                </p>
            </div>

            <!-- Grid 6 Fasilitas Utama -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <!-- Fasilitas 1: KPPT Bea Cukai & Karantina -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-xl hover:border-cdp-blue transition-all overflow-hidden flex flex-col group">
                    <div class="h-48 overflow-hidden relative">
                        <img src="https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=800&q=80" 
                             alt="Kawasan Pelayanan Pabean Terpadu" 
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-cdp-navy/90 backdrop-blur-xs text-white text-[11px] font-bold px-2.5 py-1 rounded-md">
                            Layanan Terpadu Pabean
                        </span>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <h3 class="font-extrabold text-base text-slate-900 group-hover:text-cdp-blue transition">
                                Kawasan Pelayanan Pabean Terpadu (KPPT) &amp; Karantina
                            </h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Penyelesaian administrasi kepabeanan satu atap bersama instansi Bea Cukai dan Balai Karantina sebelum peti kemas meninggalkan pelabuhan kering menuju pabrik.
                            </p>
                        </div>
                        <ul class="text-xs text-slate-600 space-y-2 pt-3 border-t border-slate-100">
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Sinkronisasi dokumen elektronik SPPB, BC 1.1, BC 1.2</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Area jalur hijau, kuning, dan jalur merah pemeriksaan fisik</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Pemeriksaan karantina hewan, tumbuhan, dan ikan (HPIK)</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Fasilitas 2: Emplasemen Rel Antarmoda -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-xl hover:border-cdp-blue transition-all overflow-hidden flex flex-col group">
                    <div class="h-48 overflow-hidden relative">
                        <img src="https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?auto=format&fit=crop&w=800&q=80" 
                             alt="Emplasemen Kereta Api Antarmoda" 
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-cyan-700/90 backdrop-blur-xs text-white text-[11px] font-bold px-2.5 py-1 rounded-md">
                            Rel Antarmoda KAI Logistik
                        </span>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <h3 class="font-extrabold text-base text-slate-900 group-hover:text-cdp-blue transition">
                                Emplasemen Jalur Rel Kereta Api Antarmoda
                            </h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Jalur rel ganda khusus (<em>dedicated double track siding</em>) yang terhubung langsung ke koridor rel nasional menuju dermaga Pelabuhan Tanjung Priok Jakarta.
                            </p>
                        </div>
                        <ul class="text-xs text-slate-600 space-y-2 pt-3 border-t border-slate-100">
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Kapasitas rangkaian 30 hingga 60 TEUs per keberangkatan</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Dukungan bongkar muat cepat gerbong datar (*flat car*)</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Pencegahan kongesti lalu lintas jalan raya arteri dan tol</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Fasilitas 3: Pusat Logistik Berikat & CFS -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-xl hover:border-cdp-blue transition-all overflow-hidden flex flex-col group">
                    <div class="h-48 overflow-hidden relative">
                        <img src="https://images.unsplash.com/photo-1587293852726-70cdb56c2866?auto=format&fit=crop&w=800&q=80" 
                             alt="Gudang Berikat & CFS" 
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-amber-700/90 backdrop-blur-xs text-white text-[11px] font-bold px-2.5 py-1 rounded-md">
                            Pergudangan Berikat &amp; CFS
                        </span>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <h3 class="font-extrabold text-base text-slate-900 group-hover:text-cdp-blue transition">
                                Pusat Logistik Berikat (PLB) &amp; Gudang CFS
                            </h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Fasilitas penangguhan bea masuk serta gudang <em>Container Freight Station</em> (CFS) modern untuk konsolidasi dan dekonsolidasi kargo muatan ekspor-impor.
                            </p>
                        </div>
                        <ul class="text-xs text-slate-600 space-y-2 pt-3 border-t border-slate-100">
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Penangguhan bea masuk &amp; pajak impor hingga pengeluaran barang</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Konsolidasi kargo LCL (<em>Less than Container Load</em>)</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Pengawasan CCTV terintegrasi 24 jam dan manajemen WMS</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Fasilitas 4: Depo Rantai Dingin (Reefer Hub) -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-xl hover:border-cdp-blue transition-all overflow-hidden flex flex-col group">
                    <div class="h-48 overflow-hidden relative">
                        <img src="https://images.unsplash.com/photo-1586528116493-a029325540fa?auto=format&fit=crop&w=800&q=80" 
                             alt="Depo Peti Kemas Reefer" 
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-blue-700/90 backdrop-blur-xs text-white text-[11px] font-bold px-2.5 py-1 rounded-md">
                            Depo Rantai Dingin
                        </span>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <h3 class="font-extrabold text-base text-slate-900 group-hover:text-cdp-blue transition">
                                Depo Rantai Dingin (Reefer Hub 300+ Plugs)
                            </h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Stasiun penanganan peti kemas berpendingin untuk menjamin mutu komoditas suhu terkendali seperti produk farmasi, produk susu, daging beku, dan hasil pertanian.
                            </p>
                        </div>
                        <ul class="text-xs text-slate-600 space-y-2 pt-3 border-t border-slate-100">
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>300+ titik colokan daya listrik (<em>reefer plugs</em>) bersertifikasi</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Pencatatan suhu otomatis berkala oleh teknisi rantai dingin</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Genset cadangan berdaya besar (<em>heavy-duty power back-up</em>)</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Fasilitas 5: Gerbang Cerdas & Timbangan VGM SOLAS -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-xl hover:border-cdp-blue transition-all overflow-hidden flex flex-col group">
                    <div class="h-48 overflow-hidden relative">
                        <img src="https://images.unsplash.com/photo-1519003722824-194d4455a60c?auto=format&fit=crop&w=800&q=80" 
                             alt="Gerbang Cerdas & Jembatan Timbang" 
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-emerald-700/90 backdrop-blur-xs text-white text-[11px] font-bold px-2.5 py-1 rounded-md">
                            Otomasi Gerbang &amp; Timbang
                        </span>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <h3 class="font-extrabold text-base text-slate-900 group-hover:text-cdp-blue transition">
                                Gerbang Cerdas &amp; Jembatan Timbang VGM SOLAS
                            </h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Pintu masuk dan keluar otomatis berbasis pemindai optik nomor kontainer, pengenal pelat nomor truk, dan jembatan timbang bersertifikasi internasional.
                            </p>
                        </div>
                        <ul class="text-xs text-slate-600 space-y-2 pt-3 border-t border-slate-100">
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Kamera OCR ISO 6346 &amp; kamera ANPR (pelat truk) otomatis</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Jembatan timbang terkalibrasi VGM (<em>Verified Gross Mass</em>) IMO SOLAS</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Penerbitan surat jalan elektronik (<em>e-Interchange Receipt</em>) instan</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Fasilitas 6: Armada Penanganan Lapangan -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-xl hover:border-cdp-blue transition-all overflow-hidden flex flex-col group">
                    <div class="h-48 overflow-hidden relative">
                        <img src="https://images.unsplash.com/photo-1578575437130-527eed3abbec?auto=format&fit=crop&w=800&q=80" 
                             alt="Armada Alat Angkat Lapangan" 
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-indigo-700/90 backdrop-blur-xs text-white text-[11px] font-bold px-2.5 py-1 rounded-md">
                            Armada Alat Berat Lapangan
                        </span>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <h3 class="font-extrabold text-base text-slate-900 group-hover:text-cdp-blue transition">
                                Armada Alat Angkat Berat Kalmar Gloria 45 Ton
                            </h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Didukung unit <em>Reach Stacker</em> generasi mutakhir berdaya angkat 45 ton, RTG (<em>Rubber Tyred Gantry</em>), serta <em>Empty Container Handler</em> berteknologi telemetri.
                            </p>
                        </div>
                        <ul class="text-xs text-slate-600 space-y-2 pt-3 border-t border-slate-100">
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Sensor <em>twistlock</em> pintar &amp; telemetri tekanan hidrolik *spreader*</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Pemosisian satelit akurat DGPS RTK pada kabin alat angkat</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Instruksi kerja nirkabel langsung tersinkronisasi ke konsol YMS</span>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 5. TIGA ALUR OPERASIONAL TERINTEGRASI (ARSITEKTUR END-TO-END) -->
    <!-- =================================================================== -->
    <section id="tiga-alur" class="py-20 bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            
            <div class="max-w-3xl mx-auto text-center space-y-3 mb-16">
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-blue-50 text-cdp-blue text-xs font-bold border border-blue-100 uppercase tracking-wider">
                    <i class="fa-solid fa-diagram-project"></i>
                    <span>Arsitektur Ekosistem YMS</span>
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    3 Alur Operasional Terintegrasi
                </h2>
                <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
                    Sistem yang kami bangun menjamin bahwa <strong>setiap gerakan fisik di lapangan</strong> secara otomatis memicu <strong>aliran data telemetri waktu-nyata</strong>, yang kemudian secara langsung tercatat ke dalam <strong>modul keuangan sistem ERP</strong> tanpa campur tangan manual.
                </p>
            </div>

            <!-- Tiga Pilar Komparasi -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
                
                <!-- Pilar 1: Alur Fisik Lapangan -->
                <div class="bg-gradient-to-b from-blue-50/50 to-white rounded-2xl border-2 border-blue-200/80 p-6 flex flex-col justify-between relative shadow-xs">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="w-8 h-8 rounded-lg bg-cdp-navy text-white flex items-center justify-center font-extrabold text-xs">01</span>
                            <span class="text-[11px] font-bold text-cdp-navy bg-blue-100 px-2.5 py-0.5 rounded-full uppercase tracking-wider">Lapangan Fisik</span>
                        </div>
                        <h3 class="text-lg font-extrabold text-slate-900">Alur Fisik Lapangan</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Pergerakan fisik armada truk kontainer, gerbong kereta api barang, dan alat angkat <em>reach stacker</em> di seluruh penjuru pelabuhan kering.
                        </p>

                        <div class="space-y-3 pt-4 border-t border-slate-200/60 text-xs">
                            <div class="flex items-start space-x-3">
                                <div class="w-5 h-5 rounded-full bg-blue-200 text-cdp-navy flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">1</div>
                                <div>
                                    <strong class="text-slate-800">Pemeriksaan Gerbang Masuk:</strong>
                                    <p class="text-slate-500 text-[11px]">Truk tiba di <em>in-gate</em>, penimbangan bobot kotor di jembatan timbang VGM SOLAS.</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-5 h-5 rounded-full bg-blue-200 text-cdp-navy flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">2</div>
                                <div>
                                    <strong class="text-slate-800">Penataan Lapangan Penumpukan:</strong>
                                    <p class="text-slate-500 text-[11px]">Penempatan di slot Blok-Bay-Row-Tier dengan kaidah beban terberat di lapisan bawah (<em>heaviest-on-bottom</em>).</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-5 h-5 rounded-full bg-blue-200 text-cdp-navy flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">3</div>
                                <div>
                                    <strong class="text-slate-800">Alih Muat Antarmoda Rel:</strong>
                                    <p class="text-slate-500 text-[11px]">Pemindahan kontainer ke rangkaian kereta api barang KAI Logistik menuju Tanjung Priok.</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-5 h-5 rounded-full bg-blue-200 text-cdp-navy flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">4</div>
                                <div>
                                    <strong class="text-slate-800">Gerbang Keluar (Out-Gate):</strong>
                                    <p class="text-slate-500 text-[11px]">Verifikasi dokumen SPPB kepabeanan dan pelepasan segel elektronik RFID.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-semibold">
                        <span>Fokus: Ketepatan Fisik &amp; K3</span>
                        <i class="fa-solid fa-truck-ramp-box text-cdp-navy"></i>
                    </div>
                </div>

                <!-- Pilar 2: Alur Informasi & Telemetri IoT -->
                <div class="bg-gradient-to-b from-cyan-50/50 to-white rounded-2xl border-2 border-cyan-200/80 p-6 flex flex-col justify-between relative shadow-xs">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="w-8 h-8 rounded-lg bg-cdp-blue text-white flex items-center justify-center font-extrabold text-xs">02</span>
                            <span class="text-[11px] font-bold text-cdp-blue bg-cyan-100 px-2.5 py-0.5 rounded-full uppercase tracking-wider">Telemetri &amp; Data</span>
                        </div>
                        <h3 class="text-lg font-extrabold text-slate-900">Alur Informasi &amp; IoT</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Transmisi data telemetri otomatis dari perangkat keras lapangan ke konsol visual <em>Yard Management System</em> berbasis format standar JSON.
                        </p>

                        <div class="space-y-3 pt-4 border-t border-slate-200/60 text-xs">
                            <div class="flex items-start space-x-3">
                                <div class="w-5 h-5 rounded-full bg-cyan-200 text-cdp-blue flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">1</div>
                                <div>
                                    <strong class="text-slate-800">Pemindaian Optik OCR &amp; ANPR:</strong>
                                    <p class="text-slate-500 text-[11px]">Pengenalan instan kode ISO 6346 dan pelat nomor truk dengan akurasi tinggi &gt;99%.</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-5 h-5 rounded-full bg-cyan-200 text-cdp-blue flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">2</div>
                                <div>
                                    <strong class="text-slate-800">Sensor Twistlock &amp; Spreader:</strong>
                                    <p class="text-slate-500 text-[11px]">Sensor mekanis alat angkat mendeteksi penguncian (<em>locked</em>) dan pelepasan (<em>unlocked</em>).</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-5 h-5 rounded-full bg-cyan-200 text-cdp-blue flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">3</div>
                                <div>
                                    <strong class="text-slate-800">Transmisi Payload JSON:</strong>
                                    <p class="text-slate-500 text-[11px]">Pengiriman paket data telemetri via jaringan lokal berkecepatan tinggi ke *broker* sistem YMS.</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-5 h-5 rounded-full bg-cyan-200 text-cdp-blue flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">4</div>
                                <div>
                                    <strong class="text-slate-800">Sinkronisasi Visual 3D:</strong>
                                    <p class="text-slate-500 text-[11px]">Tampilan peta lapangan di layar supervisor langsung diperbarui dalam milidetik.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-semibold">
                        <span>Fokus: Visibilitas Waktu-Nyata</span>
                        <i class="fa-solid fa-network-wired text-cdp-blue"></i>
                    </div>
                </div>

                <!-- Pilar 3: Alur Finansial Sistem ERP -->
                <div class="bg-gradient-to-b from-emerald-50/50 to-white rounded-2xl border-2 border-emerald-200/80 p-6 flex flex-col justify-between relative shadow-xs">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-extrabold text-xs">03</span>
                            <span class="text-[11px] font-bold text-emerald-700 bg-emerald-100 px-2.5 py-0.5 rounded-full uppercase tracking-wider">Keuangan ERP</span>
                        </div>
                        <h3 class="text-lg font-extrabold text-slate-900">Alur Finansial (Sistem ERP)</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Penetapan tarif jasa komersial secara otomatis berdasarkan rekaman peristiwa lapangan (<em>event-driven billing</em>) ke dalam buku besar akuntansi.
                        </p>

                        <div class="space-y-3 pt-4 border-t border-slate-200/60 text-xs">
                            <div class="flex items-start space-x-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-200 text-emerald-700 flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">1</div>
                                <div>
                                    <strong class="text-slate-800">Pemicu Tarif Berbasis Peristiwa:</strong>
                                    <p class="text-slate-500 text-[11px]">Setiap aktivitas angkat (*Lift-On/Lift-Off*) langsung membentuk entri tagihan tertaut.</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-200 text-emerald-700 flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">2</div>
                                <div>
                                    <strong class="text-slate-800">Perhitungan Penimbangan VGM:</strong>
                                    <p class="text-slate-500 text-[11px]">Biaya sertifikasi penimbangan kontainer langsung dibebankan ke akun tagihan pemilik barang.</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-200 text-emerald-700 flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">3</div>
                                <div>
                                    <strong class="text-slate-800">Akrual Biaya Penumpukan:</strong>
                                    <p class="text-slate-500 text-[11px]">Perhitungan tarif harian masa inap (<em>dwell time storage</em>) dihitung otomatis per jam 00:00.</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-200 text-emerald-700 flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">4</div>
                                <div>
                                    <strong class="text-slate-800">Penerbitan Faktur Sistem ERP:</strong>
                                    <p class="text-slate-500 text-[11px]">Faktur komersial langsung tersinkronisasi ke jurnal umum tanpa selisih atau manipulasi.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-semibold">
                        <span>Fokus: Nir-Kebocoran Pendapatan</span>
                        <i class="fa-solid fa-file-invoice-dollar text-emerald-600"></i>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 6. INFORMASI TIM KONSULTAN (KELOMPOK 3 ITL TRISAKTI) -->
    <!-- =================================================================== -->
    <section id="tim" class="py-20 bg-slate-50 border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            
            <div class="max-w-3xl mx-auto text-center space-y-3 mb-16">
                <div class="inline-flex items-center space-x-2 px-3.5 py-1 rounded-full bg-cdp-navy/10 text-cdp-navy text-xs font-bold border border-cdp-navy/20 uppercase tracking-wider">
                    <i class="fa-solid fa-graduation-cap text-amber-500"></i>
                    <span>Kelompok 3 &bull; Inland Container Depot &amp; Dry Port Management</span>
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Tim Pengembang &amp; Konsultan Sistem
                </h2>
                <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
                    Disusun untuk memenuhi luaran proyek perancangan sistem terpadu pada mata kuliah <strong>Teknologi dan Perangkat Lunak Logistik</strong> di bawah bimbingan Dosen Pengampu <strong>Dr. Tigor Franky, S.T., M.T.</strong>
                </p>
                <div class="text-xs text-slate-500 font-medium">
                    Program Studi S1 Logistik &bull; Fakultas Sistem Transportasi dan Logistik &bull; <strong>Institut Transportasi dan Logistik (ITL) Trisakti, Jakarta &bull; 2026</strong>
                </div>
            </div>

            <!-- Grid 5 Anggota Tim Konsultan -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                
                <!-- 1. Zulfikar Jafarudin Fatah -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs hover:border-cdp-blue hover:shadow-lg transition-all space-y-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-14 h-14 rounded-2xl bg-cdp-navy text-white flex items-center justify-center font-extrabold text-lg shadow-md flex-shrink-0">
                            ZF
                        </div>
                        <div>
                            <h3 class="font-extrabold text-base text-slate-900 leading-snug">Zulfikar Jafarudin Fatah</h3>
                            <span class="inline-block mt-0.5 text-[11px] font-bold text-cdp-blue bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">
                                Lead System Architect (Ketua Tim)
                            </span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Bertanggung jawab memimpin perancangan arsitektur sistem menyeluruh YMS, orkestrasi integrasi antarmoda pelabuhan kering, koordinasi tim pengembang, dan penyusunan tata kelola data sistem.
                    </p>
                    <div class="pt-3 border-t border-slate-100 flex flex-wrap gap-1.5 text-[10px] font-semibold text-slate-600">
                        <span class="bg-slate-100 px-2 py-0.5 rounded">Lead Architect</span>
                        <span class="bg-slate-100 px-2 py-0.5 rounded">Ketua Tim</span>
                        <span class="bg-slate-100 px-2 py-0.5 rounded">Integrasi Antarmoda</span>
                    </div>
                </div>

                <!-- 2. Armansyah Muchtarrom -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs hover:border-cdp-blue hover:shadow-lg transition-all space-y-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-14 h-14 rounded-2xl bg-cyan-700 text-white flex items-center justify-center font-extrabold text-lg shadow-md flex-shrink-0">
                            AM
                        </div>
                        <div>
                            <h3 class="font-extrabold text-base text-slate-900 leading-snug">Armansyah Muchtarrom</h3>
                            <span class="inline-block mt-0.5 text-[11px] font-bold text-cyan-800 bg-cyan-50 px-2 py-0.5 rounded-md border border-cyan-100">
                                Hardware &amp; Infrastructure Specialist
                            </span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Bertanggung jawab atas analisis infrastruktur fisik lapangan, integrasi sensor telemetri alat angkat (<em>spreader/twistlock</em>), jembatan timbang bersertifikasi VGM SOLAS, dan otomasi gerbang masuk/keluar.
                    </p>
                    <div class="pt-3 border-t border-slate-100 flex flex-wrap gap-1.5 text-[10px] font-semibold text-slate-600">
                        <span class="bg-slate-100 px-2 py-0.5 rounded">Infrastruktur Lapangan</span>
                        <span class="bg-slate-100 px-2 py-0.5 rounded">Sensor Twistlock</span>
                        <span class="bg-slate-100 px-2 py-0.5 rounded">Otomasi Gerbang</span>
                    </div>
                </div>

                <!-- 3. Afriansayah Ayubi -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs hover:border-cdp-blue hover:shadow-lg transition-all space-y-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-14 h-14 rounded-2xl bg-amber-600 text-white flex items-center justify-center font-extrabold text-lg shadow-md flex-shrink-0">
                            AA
                        </div>
                        <div>
                            <h3 class="font-extrabold text-base text-slate-900 leading-snug">Afriansayah Ayubi</h3>
                            <span class="inline-block mt-0.5 text-[11px] font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-100">
                                Software &amp; ERP Process Specialist
                            </span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Bertanggung jawab atas rekayasa logika proses perangkat lunak, perancangan alur penanganan peti kemas, serta sinkronisasi peristiwa operasional lapangan ke dalam modul keuangan sistem ERP terpadu.
                    </p>
                    <div class="pt-3 border-t border-slate-100 flex flex-wrap gap-1.5 text-[10px] font-semibold text-slate-600">
                        <span class="bg-slate-100 px-2 py-0.5 rounded">Rekayasa Software</span>
                        <span class="bg-slate-100 px-2 py-0.5 rounded">Proses Sistem ERP</span>
                        <span class="bg-slate-100 px-2 py-0.5 rounded">Logika Alur Kerja</span>
                    </div>
                </div>

                <!-- 4. Juan Gamaliel -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs hover:border-cdp-blue hover:shadow-lg transition-all space-y-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-extrabold text-lg shadow-md flex-shrink-0">
                            JG
                        </div>
                        <div>
                            <h3 class="font-extrabold text-base text-slate-900 leading-snug">Juan Gamaliel</h3>
                            <span class="inline-block mt-0.5 text-[11px] font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">
                                Data Integration Specialist
                            </span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Bertanggung jawab atas integrasi aliran data antarsistem, pemodelan pertukaran data JSON/API, rekonsiliasi basis data waktu-nyata, dan sinkronisasi informasi kepabeanan serta pelayaran.
                    </p>
                    <div class="pt-3 border-t border-slate-100 flex flex-wrap gap-1.5 text-[10px] font-semibold text-slate-600">
                        <span class="bg-slate-100 px-2 py-0.5 rounded">Integrasi Data API</span>
                        <span class="bg-slate-100 px-2 py-0.5 rounded">Format JSON Telemetri</span>
                        <span class="bg-slate-100 px-2 py-0.5 rounded">Sinkronisasi Basis Data</span>
                    </div>
                </div>

                <!-- 5. Naufal Andika Heditya -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs hover:border-cdp-blue hover:shadow-lg transition-all space-y-4 md:col-span-2 lg:col-span-1">
                    <div class="flex items-center space-x-4">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-extrabold text-lg shadow-md flex-shrink-0">
                            NA
                        </div>
                        <div>
                            <h3 class="font-extrabold text-base text-slate-900 leading-snug">Naufal Andika Heditya</h3>
                            <span class="inline-block mt-0.5 text-[11px] font-bold text-indigo-800 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                                Business Analyst &amp; QA Specialist
                            </span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Bertanggung jawab atas analisis kebutuhan proses bisnis operasional pelabuhan kering, standardisasi mutu, pengujian penjaminan kualitas (<em>Quality Assurance</em>), dan kepatuhan regulasi pabean.
                    </p>
                    <div class="pt-3 border-t border-slate-100 flex flex-wrap gap-1.5 text-[10px] font-semibold text-slate-600">
                        <span class="bg-slate-100 px-2 py-0.5 rounded">Analisis Bisnis</span>
                        <span class="bg-slate-100 px-2 py-0.5 rounded">Quality Assurance (QA)</span>
                        <span class="bg-slate-100 px-2 py-0.5 rounded">Regulasi Pabean</span>
                    </div>
                </div>

                <!-- Kartu Identitas Akademis & Mata Kuliah ITL Trisakti -->
                <div class="bg-gradient-to-br from-cdp-navy to-cdp-dark rounded-2xl p-6 text-white flex flex-col justify-between shadow-md md:col-span-2 lg:col-span-1 border border-blue-900">
                    <div class="space-y-3">
                        <div class="inline-flex items-center space-x-2 text-xs text-blue-200 font-bold uppercase tracking-wider">
                            <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                            <span>Afiliasi Akademis Resmi</span>
                        </div>
                        <h3 class="text-base font-extrabold text-white">
                            Institut Transportasi dan Logistik (ITL) Trisakti
                        </h3>
                        <div class="space-y-1 text-xs text-blue-100 leading-relaxed">
                            <p><strong>Mata Kuliah:</strong> Teknologi dan Perangkat Lunak Logistik</p>
                            <p><strong>Dosen Pengampu:</strong> Dr. Tigor Franky, S.T., M.T.</p>
                            <p><strong>Program Studi:</strong> S1 Logistik, Fakultas Sistem Transportasi dan Logistik</p>
                            <p><strong>Topik Proyek:</strong> Inland Container Depot &amp; Dry Port Management</p>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-white/20 flex items-center justify-between text-xs text-blue-200">
                        <span>Jakarta, Indonesia</span>
                        <span class="font-bold text-white">Kelompok 3 &bull; 2026</span>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 7. CATATAN KAKI RESMI (FOOTER KORPORAT) -->
    <!-- =================================================================== -->
    <footer class="bg-cdp-dark text-slate-300 text-xs py-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                
                <!-- Kolom 1: Merek & Profil -->
                <div class="md:col-span-2 space-y-3">
                    <div class="flex items-center space-x-3">
                        <img src="assets/img/logo.png" alt="Conclusion Logo" class="w-9 h-9 object-contain bg-white rounded-lg p-0.5">
                        <div>
                            <span class="font-extrabold text-base text-white tracking-tight">CONCLUSION</span>
                            <span class="block text-[10px] font-bold text-blue-300 uppercase tracking-widest">Supply Chain Consultant</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 max-w-md leading-relaxed">
                        Aplikasi ini merupakan sarana demonstrasi dan studi percontohan <strong>Yard Management System (YMS)</strong> untuk pelabuhan kering antarmoda. Seluruh data transaksi, sensor, dan pergerakan disimulasikan untuk keperluan konsultansi profesional dan tugas mata kuliah Teknologi dan Perangkat Lunak Logistik.
                    </p>
                </div>

                <!-- Kolom 2: Navigasi Cepat -->
                <div class="space-y-2">
                    <span class="text-xs font-bold text-white uppercase tracking-wider block mb-2">Tautan Bagian</span>
                    <ul class="space-y-1.5 text-xs text-slate-400">
                        <li><a href="#tentang" class="hover:text-white transition">Tentang Kami</a></li>
                        <li><a href="#fasilitas" class="hover:text-white transition">Fasilitas &amp; Solusi</a></li>
                        <li><a href="#tiga-alur" class="hover:text-white transition">3 Alur Operasional</a></li>
                        <li><a href="#tim" class="hover:text-white transition">Tim Konsultan</a></li>
                    </ul>
                </div>

                <!-- Kolom 3: Kontak & Demonstrasi -->
                <div class="space-y-2">
                    <span class="text-xs font-bold text-white uppercase tracking-wider block mb-2">Akses Demonstrasi</span>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Gunakan akun pengujian terdaftar untuk menguji fitur penataan lapangan dan pelacakan telemetri.
                    </p>
                    <button type="button" onclick="openLoginModal()" 
                            class="mt-2 bg-cdp-blue hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-xs font-bold shadow-sm transition inline-flex items-center space-x-2">
                        <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                        <span>Buka Modal Masuk</span>
                    </button>
                </div>

            </div>

            <div class="pt-8 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-slate-500">
                <div>
                    &copy; 2026 <strong>Conclusion Supply Chain Consultant</strong> &bull; Disusun oleh Kelompok 3 S1 Logistik ITL Trisakti (Dosen Pengampu: Dr. Tigor Franky, S.T., M.T.).
                </div>
                <div class="flex items-center space-x-4">
                    <span>Bahasa Indonesia (EYD)</span>
                    <span>&bull;</span>
                    <span>Yard Management System Demo</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- =================================================================== -->
    <!-- 6. MODAL MASUK DEMONSTRASI YMS (SESUAI EYD & TABEL LOGIN) -->
    <!-- =================================================================== -->
    <div id="loginModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs <?= empty($error_message) ? 'hidden' : ''; ?> flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden animate-fadeIn">
            
            <!-- Kepala Modal -->
            <div class="bg-cdp-navy px-6 py-4 text-white flex items-center justify-between">
                <div class="flex items-center space-x-2.5">
                    <img src="assets/img/logo.png" alt="Conclusion Logo" class="w-8 h-8 object-contain bg-white rounded-md p-0.5 shadow-xs">
                    <div>
                        <h3 class="font-bold text-sm">Masuk Demonstrasi YMS</h3>
                        <p class="text-[10px] text-blue-200">Conclusion Supply Chain Consultant</p>
                    </div>
                </div>
                <button type="button" onclick="closeLoginModal()" class="text-slate-300 hover:text-white text-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Badan Modal Form (Satu Pintu / Single-Door Simulation Access) -->
            <div class="p-6 space-y-4">
                
                <!-- Info Banner Satu Pintu -->
                <div class="bg-blue-50/80 border border-blue-200 rounded-xl p-3.5 flex items-start space-x-3">
                    <div class="w-8 h-8 rounded-lg bg-[#0170b9] text-white flex items-center justify-center flex-shrink-0 text-sm mt-0.5 shadow-sm">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-[#002f5e] uppercase tracking-wide">Akses Demonstrasi Satu Pintu</h4>
                        <p class="text-[11px] text-slate-600 leading-relaxed mt-0.5">
                            Konsol simulasi disiapkan dengan <strong>satu pintu akses penuh (All-in-One Consultant Access)</strong> agar evaluator dapat langsung menguji seluruh 8 modul operasional (Gate, Yard, Denah 3D, Intermodal KA-Truk, Reefer IoT, Faktur ERP, dan Scanner GS1) tanpa batasan peran.
                        </p>
                    </div>
                </div>

                <!-- Pesan Kesalahan jika login tidak valid -->
                <?php if (!empty($error_message)): ?>
                    <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700 flex items-start space-x-2">
                        <i class="fa-solid fa-circle-exclamation text-base flex-shrink-0 mt-0.5"></i>
                        <div><?= htmlspecialchars($error_message); ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" id="formLoginModal" class="space-y-3.5">
                    <div>
                        <label for="modalEmail" class="block text-xs font-bold text-slate-700 uppercase mb-1 flex justify-between">
                            <span>Alamat Pos-el (Email)</span>
                            <span class="text-[10px] text-[#0170b9] font-semibold lowercase">akun konsultan utama</span>
                        </label>
                        <input type="email" id="modalEmail" name="email" value="admin@cidp.ac.id" required 
                               placeholder="admin@cidp.ac.id"
                               class="w-full px-3.5 py-2.5 text-xs font-mono font-bold text-slate-800 border border-slate-300 rounded-lg focus:ring-2 focus:ring-cdp-blue outline-none bg-slate-50">
                    </div>

                    <div>
                        <label for="modalPassword" class="block text-xs font-bold text-slate-700 uppercase mb-1 flex justify-between">
                            <span>Kata Sandi (Password)</span>
                            <span class="text-[10px] text-emerald-600 font-semibold font-mono">admin123</span>
                        </label>
                        <input type="password" id="modalPassword" name="password" value="admin123" required 
                               placeholder="Masukkan kata sandi"
                               class="w-full px-3.5 py-2.5 text-xs font-mono font-bold text-slate-800 border border-slate-300 rounded-lg focus:ring-2 focus:ring-cdp-blue outline-none bg-slate-50">
                    </div>

                    <div class="pt-1 space-y-2">
                        <!-- Tombol Utama: Masuk Konsol -->
                        <button type="submit" name="btn_login" 
                                class="w-full py-2.5 px-4 rounded-lg bg-cdp-navy hover:bg-cdp-blue text-white text-xs font-bold shadow-sm transition flex items-center justify-center space-x-2">
                            <i class="fa-solid fa-arrow-right-to-bracket"></i>
                            <span>Masuk ke Konsol Demonstrasi (Akses Penuh)</span>
                        </button>

                        <!-- Tombol 1-Klik Cepat -->
                        <button type="button" onclick="quickSingleDoorLogin()"
                                class="w-full py-2 px-4 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition flex items-center justify-center space-x-2">
                            <i class="fa-solid fa-bolt text-yellow-300"></i>
                            <span>Masuk Cepat Langsung (1-Klik Tanpa Ketik)</span>
                        </button>
                    </div>
                </form>

                <div class="pt-3 border-t border-slate-200 text-center">
                    <span class="text-[11px] text-slate-400 font-medium">
                        <i class="fa-solid fa-shield-halved text-emerald-600 mr-1"></i>Hak Akses Superadmin / Lead System Architect otomatis aktif.
                    </span>
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

        function fillDemoCredentials() {
            setCredentials('admin@cidp.ac.id', 'admin123');
        }

        // Menutup modal apabila tombol Escape ditekan
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeLoginModal();
        });
    </script>
</body>
</html>