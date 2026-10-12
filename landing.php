<?php
// =============================================================================
// FILE       : landing.php
// PROYEK     : Conclusion Intermodal Dry Port Management System (CIDP DPMS)
// KONSULTAN  : Conclusion Supply Chain Consultant
// DESAIN     : Terinspirasi dari Tata Letak & Interaktivitas Korporat Petronas.com
// WARNA      : Conclusion Orange (#ea580c), DPMS Navy (#004b87, #002f5e), Cyan (#0170b9)
// =============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['login']) && $_SESSION['login'] === true;
$userName   = $_SESSION['nama'] ?? 'Pengguna';
$userRole   = $_SESSION['role'] ?? 'Tamu';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conclusion Intermodal Dry Port &bull; Integrated DPMS Hub 35 Ha</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link rel="apple-touch-icon" href="assets/img/logo.png">
    
    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 (Local with CDN fallback) -->
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- AOS Animation Library (CDN) -->
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    
    <!-- Tailwind CSS (Local with CDN fallback) -->
    <script src="assets/vendor/tailwindcss.js"></script>
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
                            light: '#f4f8fc',
                            orange: '#ea580c',
                            'orange-hover': '#c2410c',
                            accent: '#f97316'
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace']
                    }
                }
            }
        };
    </script>
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* --- Custom Scrollbar --- */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        ::-webkit-scrollbar-thumb {
            background: #ea580c;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #c2410c;
        }

        /* --- Header Navigation Transition (Petronas-style) --- */
        .petronas-nav {
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .petronas-nav.nav-scrolled {
            background-color: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(12px);
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.12);
            color: #0f172a !important;
            padding-top: 8px;
            padding-bottom: 8px;
        }
        .petronas-nav.nav-scrolled .nav-link-top {
            color: #475569;
        }
        .petronas-nav.nav-scrolled .nav-link-top:hover {
            color: #ea580c;
        }
        .petronas-nav.nav-scrolled .nav-link-main {
            color: #0f172a;
        }
        .petronas-nav.nav-scrolled .nav-link-main:hover {
            color: #ea580c;
        }
        .petronas-nav.nav-scrolled .logo-text-sub {
            color: #64748b;
        }

        /* --- Floating Side Navigation (Petronas JsideNaviContainer) --- */
        .side-floating-nav {
            position: fixed;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            z-index: 50;
        }
        .side-floating-nav .nav-item {
            width: 48px;
            height: 48px;
            background: rgba(15, 23, 42, 0.92);
            border-left: 2px solid #ea580c;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            cursor: pointer;
            transition: all 0.25s ease;
            position: relative;
        }
        .side-floating-nav .nav-item:hover {
            background: #ea580c;
            width: 54px;
            color: #ffffff;
        }
        .side-floating-nav .social-drawer {
            position: absolute;
            right: 48px;
            top: 0;
            background: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px 0 0 8px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 12px;
            opacity: 0;
            pointer-events: none;
            transform: translateX(10px);
            transition: all 0.3s ease;
            box-shadow: -4px 4px 16px rgba(0,0,0,0.4);
        }
        .side-floating-nav .nav-item-social:hover .social-drawer {
            opacity: 1;
            pointer-events: auto;
            transform: translateX(0);
        }

        /* --- Hero Carousel Slider with Crossfade --- */
        .hero-slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            visibility: hidden;
            transition: opacity 1.2s cubic-bezier(0.4, 0, 0.2, 1), transform 6s linear;
            transform: scale(1.05);
            background-size: cover;
            background-position: center;
        }
        .hero-slide.active {
            opacity: 1;
            visibility: visible;
            transform: scale(1);
            z-index: 10;
        }

        /* Vignette Gradient Overlay (Petronas standard) */
        .hero-vignette {
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.65) 0%, rgba(15, 23, 42, 0.45) 40%, rgba(15, 23, 42, 0.95) 100%),
                        linear-gradient(90deg, rgba(15, 23, 42, 0.85) 0%, rgba(15, 23, 42, 0.3) 60%, transparent 100%);
        }

        /* Hero Nav Tabs & Progress Bar (Petronas Banner Nav) */
        .banner-tab-item {
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }
        .banner-tab-item .tab-title {
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }
        .banner-tab-item.active .tab-title {
            opacity: 1;
            font-weight: 700;
            color: #ffffff;
        }
        .banner-tab-item .progress-track {
            height: 3px;
            background: rgba(255, 255, 255, 0.25);
            border-radius: 999px;
            overflow: hidden;
            margin-top: 8px;
        }
        .banner-tab-item .progress-bar-fill {
            height: 100%;
            width: 0%;
            background: #ea580c;
            border-radius: 999px;
            transition: width 0.1s linear;
        }
        .banner-tab-item.active .progress-bar-fill {
            background: #ea580c;
        }

        /* --- Tall Rounded Cards (Petronas Journey Cards) --- */
        .pillar-card {
            position: relative;
            height: 480px;
            border-radius: 28px;
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.3);
        }
        .pillar-card .pillar-bg {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .pillar-card:hover .pillar-bg {
            transform: scale(1.08);
        }
        .pillar-card .pillar-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(0, 47, 94, 0.2) 0%, rgba(0, 47, 94, 0.85) 70%, rgba(15, 23, 42, 0.98) 100%);
            transition: all 0.4s ease;
        }
        .pillar-card:hover .pillar-overlay {
            background: linear-gradient(180deg, rgba(0, 47, 94, 0.5) 0%, rgba(0, 47, 94, 0.95) 50%, rgba(15, 23, 42, 1) 100%);
        }
        .pillar-card .pillar-body-default {
            position: absolute;
            bottom: 28px;
            left: 28px;
            right: 28px;
            transition: all 0.4s ease;
        }
        .pillar-card .pillar-body-hover {
            position: absolute;
            bottom: 28px;
            left: 28px;
            right: 28px;
            opacity: 0;
            transform: translateY(24px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .pillar-card:hover .pillar-body-default {
            opacity: 0;
            transform: translateY(-20px);
        }
        .pillar-card:hover .pillar-body-hover {
            opacity: 1;
            transform: translateY(0);
        }

        /* --- Accordion Image Preview Transition --- */
        .highlight-image-stage {
            transition: opacity 0.4s ease, transform 0.4s ease;
            background-size: cover;
            background-position: center;
        }

        /* --- Flip / Hover Card for Our Network (Petronas dm-card) --- */
        .dm-card-box {
            position: relative;
            height: 160px;
            perspective: 1000px;
            cursor: pointer;
        }
        .dm-card-inner {
            position: relative;
            width: 100%;
            height: 100%;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            transform-style: preserve-3d;
            border-radius: 20px;
        }
        .dm-card-box:hover .dm-card-inner {
            transform: rotateY(180deg);
        }
        .dm-face-front, .dm-face-back {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            border-radius: 20px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .dm-face-front {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }
        .dm-face-back {
            background: linear-gradient(135deg, #ea580c 0%, #004b87 100%);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            transform: rotateY(180deg);
        }

        /* --- Search Modal Overlay --- */
        .modal-blur {
            backdrop-filter: blur(16px);
            background: rgba(15, 23, 42, 0.85);
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 antialiased selection:bg-orange-500 selection:text-white">

    <!-- =================================================================== -->
    <!-- 1. HEADER DUA TINGKAT (Petronas Two-Tier Navigation)               -->
    <!-- =================================================================== -->
    <header id="mainHeader" class="petronas-nav fixed top-0 left-0 right-0 z-40 text-white w-full">
        <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between py-2 sm:py-3 border-b border-white/10" id="headerTopRow">
                
                <!-- BRAND LOGO & BADGE -->
                <a href="landing.php" class="flex items-center gap-3 group">
                    <img src="assets/img/logo.png" alt="Conclusion Logo" class="h-9 sm:h-11 w-auto drop-shadow-md group-hover:scale-105 transition-transform duration-200">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-black text-lg sm:text-xl tracking-tight uppercase text-white drop-shadow" id="brandTextMain">CONCLUSION</span>
                            <span class="text-[10px] uppercase font-bold tracking-widest bg-orange-600 text-white px-2 py-0.5 rounded-full shadow-sm">CIDP</span>
                        </div>
                        <div class="text-[11px] font-medium tracking-wide text-slate-300 logo-text-sub" id="brandTextSub">Intermodal Dry Port &bull; 35 Ha Hub</div>
                    </div>
                </a>

                <!-- TOP NAV UTILITY LINKS (Petronas-style) -->
                <div class="hidden lg:flex items-center gap-6 text-xs font-semibold text-slate-200">
                    <a href="#about" class="nav-link-top hover:text-orange-400 transition-colors">Tentang Kami</a>
                    <a href="#updates" class="nav-link-top hover:text-orange-400 transition-colors">Berita & Rilis</a>
                    <a href="#performance" class="nav-link-top hover:text-orange-400 transition-colors">Kinerja 35 Ha</a>
                    <a href="#highlights" class="nav-link-top hover:text-orange-400 transition-colors">Fasilitas & NOC</a>
                    <a href="#network" class="nav-link-top hover:text-orange-400 transition-colors">Mitra Ekosistem</a>
                    
                    <span class="text-white/20">|</span>
                    
                    <!-- Search Icon Trigger -->
                    <button onclick="toggleSearchModal(true)" class="p-1.5 hover:text-orange-400 transition-colors" title="Cari Informasi...">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    
                    <!-- Language Indicator -->
                    <div class="flex items-center gap-1 bg-white/10 px-2 py-1 rounded text-[11px] font-mono">
                        <span class="text-orange-400 font-bold">ID</span>
                        <span class="text-white/40">/</span>
                        <span class="text-slate-400 hover:text-white cursor-pointer">EN</span>
                    </div>
                </div>

                <!-- MOBILE BURGER TOGGLE -->
                <button onclick="toggleMobileMenu()" class="lg:hidden p-2 text-white hover:text-orange-400 focus:outline-none">
                    <i class="fa-solid fa-bars text-xl" id="burgerIcon"></i>
                </button>
            </div>

            <!-- BOTTOM ROW: MEGA MENU & CTA PORTAL -->
            <div class="hidden lg:flex items-center justify-between py-2.5">
                <nav class="flex items-center gap-8 text-sm font-semibold tracking-wide">
                    <!-- Dropdown 1: Ekosistem Intermodal -->
                    <div class="relative group">
                        <button class="nav-link-main flex items-center gap-1.5 py-1 hover:text-orange-400 transition-colors focus:outline-none">
                            <span>Ekosistem Intermodal</span>
                            <i class="fa-solid fa-chevron-down text-[10px] group-hover:rotate-180 transition-transform duration-200"></i>
                        </button>
                        <div class="absolute left-0 top-full mt-2 w-72 bg-slate-900/95 backdrop-blur-xl border border-slate-700/80 rounded-2xl p-3 shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform group-hover:translate-y-0 translate-y-2 z-50">
                            <a href="#pillars" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-white/10 transition-colors">
                                <i class="fa-solid fa-cubes-stacked text-orange-400 mt-1"></i>
                                <div>
                                    <div class="font-bold text-white text-xs">Yard Management (YMS)</div>
                                    <div class="text-[11px] text-slate-400">Blok A-E, 4-tier stacking & RTG</div>
                                </div>
                            </a>
                            <a href="#pillars" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-white/10 transition-colors">
                                <i class="fa-solid fa-train text-sky-400 mt-1"></i>
                                <div>
                                    <div class="font-bold text-white text-xs">Rail Siding Pasoso (TMS)</div>
                                    <div class="text-[11px] text-slate-400">60 TEU rangkaian kereta logistik</div>
                                </div>
                            </a>
                            <a href="#pillars" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-white/10 transition-colors">
                                <i class="fa-solid fa-warehouse text-emerald-400 mt-1"></i>
                                <div>
                                    <div class="font-bold text-white text-xs">CFS & Konsolidasi (WMS)</div>
                                    <div class="text-[11px] text-slate-400">Cross-docking & barcode palet GS1</div>
                                </div>
                            </a>
                            <a href="#highlights" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-white/10 transition-colors">
                                <i class="fa-solid fa-snowflake text-cyan-400 mt-1"></i>
                                <div>
                                    <div class="font-bold text-white text-xs">Reefer Cold Chain Hub</div>
                                    <div class="text-[11px] text-slate-400">120 plug points telemetri IoT 24/7</div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Dropdown 2: Keberlanjutan & Green Port -->
                    <div class="relative group">
                        <button class="nav-link-main flex items-center gap-1.5 py-1 hover:text-orange-400 transition-colors focus:outline-none">
                            <span>Keberlanjutan & Green Port</span>
                            <i class="fa-solid fa-chevron-down text-[10px] group-hover:rotate-180 transition-transform duration-200"></i>
                        </button>
                        <div class="absolute left-0 top-full mt-2 w-72 bg-slate-900/95 backdrop-blur-xl border border-slate-700/80 rounded-2xl p-3 shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform group-hover:translate-y-0 translate-y-2 z-50">
                            <a href="#performance" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-white/10 transition-colors">
                                <i class="fa-solid fa-leaf text-emerald-400 mt-1"></i>
                                <div>
                                    <div class="font-bold text-white text-xs">Reduksi Emisi -76% CO₂</div>
                                    <div class="text-[11px] text-slate-400">Modal shift dari truk ke kereta api</div>
                                </div>
                            </a>
                            <a href="#highlights" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-white/10 transition-colors">
                                <i class="fa-solid fa-bolt text-amber-400 mt-1"></i>
                                <div>
                                    <div class="font-bold text-white text-xs">Elektrifikasi Alat Terminal</div>
                                    <div class="text-[11px] text-slate-400">E-RTG & efisiensi solar Kalmar</div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Dropdown 3: Kinerja & Dokumen -->
                    <div class="relative group">
                        <button class="nav-link-main flex items-center gap-1.5 py-1 hover:text-orange-400 transition-colors focus:outline-none">
                            <span>Kinerja & Laporan</span>
                            <i class="fa-solid fa-chevron-down text-[10px] group-hover:rotate-180 transition-transform duration-200"></i>
                        </button>
                        <div class="absolute left-0 top-full mt-2 w-72 bg-slate-900/95 backdrop-blur-xl border border-slate-700/80 rounded-2xl p-3 shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform group-hover:translate-y-0 translate-y-2 z-50">
                            <a href="#performance" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-white/10 transition-colors">
                                <i class="fa-solid fa-chart-line text-blue-400 mt-1"></i>
                                <div>
                                    <div class="font-bold text-white text-xs">Indikator Dwell Time 1.8 Hari</div>
                                    <div class="text-[11px] text-slate-400">Efisiensi operasional 2026</div>
                                </div>
                            </a>
                            <a href="#documents" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-white/10 transition-colors">
                                <i class="fa-solid fa-file-contract text-orange-400 mt-1"></i>
                                <div>
                                    <div class="font-bold text-white text-xs">SOP & Regulasi CEISA 4.0</div>
                                    <div class="text-[11px] text-slate-400">Dokumen kepabeanan & kepatuhan K3</div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Direct Link: Digital Twin -->
                    <a href="dashboard.php?page=simulator" class="nav-link-main hover:text-orange-400 transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-vr-cardboard text-purple-400"></i>
                        <span>3D Digital Twin</span>
                    </a>
                </nav>

                <!-- RIGHT CTA BUTTON -->
                <div class="flex items-center gap-3">
                    <?php if ($isLoggedIn): ?>
                        <a href="dashboard.php" class="inline-flex items-center gap-2.5 px-5 py-2.5 rounded-full bg-gradient-to-r from-orange-600 to-amber-600 text-white font-bold text-xs uppercase tracking-wider hover:from-orange-500 hover:to-amber-500 shadow-lg shadow-orange-900/30 transition-all duration-200 transform hover:-translate-y-0.5">
                            <i class="fa-solid fa-gauge-high"></i>
                            <span>Buka Dashboard (<?= htmlspecialchars($userName) ?>)</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="inline-flex items-center gap-2.5 px-6 py-2.5 rounded-full bg-gradient-to-r from-orange-600 to-amber-600 text-white font-bold text-xs uppercase tracking-wider hover:from-orange-500 hover:to-amber-500 shadow-lg shadow-orange-900/30 transition-all duration-200 transform hover:-translate-y-0.5">
                            <i class="fa-solid fa-right-to-bracket"></i>
                            <span>Masuk Portal DPMS</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- MOBILE MENU OVERLAY -->
        <div id="mobileMenu" class="hidden lg:hidden bg-slate-900/98 backdrop-blur-2xl border-b border-slate-800 px-6 py-6 text-slate-200 transition-all">
            <div class="flex flex-col gap-4 text-sm font-semibold">
                <a href="#about" onclick="toggleMobileMenu()" class="py-2 hover:text-orange-400 border-b border-slate-800">Tentang CIDP 35 Ha</a>
                <a href="#pillars" onclick="toggleMobileMenu()" class="py-2 hover:text-orange-400 border-b border-slate-800">Pilar YMS &bull; TMS &bull; WMS</a>
                <a href="#performance" onclick="toggleMobileMenu()" class="py-2 hover:text-orange-400 border-b border-slate-800">Kinerja & Reduksi Emisi</a>
                <a href="#highlights" onclick="toggleMobileMenu()" class="py-2 hover:text-orange-400 border-b border-slate-800">Fasilitas NOC & Gatehouse</a>
                <a href="#network" onclick="toggleMobileMenu()" class="py-2 hover:text-orange-400 border-b border-slate-800">Jejak Modul & Kemitraan</a>
                <a href="dashboard.php?page=simulator" class="py-2 text-purple-400 hover:text-purple-300 border-b border-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-vr-cardboard"></i>
                    <span>Simulasi 3D Digital Twin</span>
                </a>
                <div class="pt-4">
                    <a href="login.php" class="w-full inline-flex items-center justify-center gap-2 py-3 rounded-xl bg-orange-600 text-white font-bold text-xs uppercase tracking-wider hover:bg-orange-500">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        <span>Masuk Portal DPMS</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- =================================================================== -->
    <!-- 2. FLOATING RIGHT-SIDE VERTICAL QUICK-NAV (Petronas JsideNavi)       -->
    <!-- =================================================================== -->
    <div class="side-floating-nav hidden md:block">
        <!-- Quick Link 1: Media Updates -->
        <a href="#updates" class="nav-item" title="Pembaruan Terkini">
            <i class="fa-solid fa-bullhorn text-sm"></i>
        </a>
        
        <!-- Quick Link 2: Search Modal Trigger -->
        <div onclick="toggleSearchModal(true)" class="nav-item" title="Cari di Situs">
            <i class="fa-solid fa-magnifying-glass text-sm"></i>
        </div>
        
        <!-- Quick Link 3: Social Share Popover Drawer -->
        <div class="nav-item nav-item-social" title="Bagikan & Media Sosial">
            <i class="fa-solid fa-share-nodes text-sm"></i>
            <div class="social-drawer">
                <a href="https://linkedin.com" target="_blank" class="text-slate-300 hover:text-blue-400 transition-colors" title="LinkedIn"><i class="fa-brands fa-linkedin text-lg"></i></a>
                <a href="https://youtube.com" target="_blank" class="text-slate-300 hover:text-red-500 transition-colors" title="YouTube"><i class="fa-brands fa-youtube text-lg"></i></a>
                <a href="https://instagram.com" target="_blank" class="text-slate-300 hover:text-pink-400 transition-colors" title="Instagram"><i class="fa-brands fa-instagram text-lg"></i></a>
                <a href="https://twitter.com" target="_blank" class="text-slate-300 hover:text-sky-400 transition-colors" title="Twitter / X"><i class="fa-brands fa-x-twitter text-lg"></i></a>
            </div>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- 3. HERO BANNER CAROUSEL (Petronas 5-Slide Fullscreen Slider)        -->
    <!-- =================================================================== -->
    <section class="relative h-screen min-h-[640px] w-full overflow-hidden flex flex-col justify-end">
        
        <!-- SLIDE 1: Solusi Logistik Intermodal 35 Ha -->
        <div class="hero-slide active" style="background-image: url('assets/img/facilities/stacking_yard.jpg');" data-index="0">
            <div class="absolute inset-0 hero-vignette"></div>
            <div class="relative h-full max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 flex flex-col justify-center text-white pb-24 md:pb-32">
                <div class="max-w-3xl" data-aos="fade-up" data-aos-duration="800">
                    <span class="inline-flex items-center gap-2 bg-orange-600/90 text-white text-[11px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-4 backdrop-blur-sm">
                        <i class="fa-solid fa-network-wired"></i> Hub Intermodal 35 Hektare
                    </span>
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight text-white mb-4">
                        Solusi Logistik <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-amber-300">Terintegrasi Intermodal</span>
                    </h1>
                    <p class="text-base sm:text-lg text-slate-200/90 leading-relaxed mb-8 max-w-2xl">
                        Menghubungkan simpul pelabuhan laut, jalur ganda kereta api logistik, dan armada angkutan darat dengan kapasitas throughput 45.000 TEU per tahun demi efisiensi rantai pasok nasional.
                    </p>
                    <div class="flex flex-wrap items-center gap-4">
                        <a href="login.php" class="inline-flex items-center gap-3 px-7 py-3.5 rounded-full bg-orange-600 hover:bg-orange-500 text-white font-bold text-sm tracking-wide shadow-xl shadow-orange-950/40 transition-all duration-200 transform hover:scale-105">
                            <span>Jelajahi Portal DPMS</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                        <a href="#about" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-full bg-white/10 hover:bg-white/20 text-white font-semibold text-sm backdrop-blur-md border border-white/20 transition-all">
                            <span>Profil Terminal</span>
                            <i class="fa-solid fa-circle-info text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- SLIDE 2: Rail Siding Pasoso Connection -->
        <div class="hero-slide" style="background-image: url('assets/img/facilities/rail_siding.jpg');" data-index="1">
            <div class="absolute inset-0 hero-vignette"></div>
            <div class="relative h-full max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 flex flex-col justify-center text-white pb-24 md:pb-32">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 bg-sky-600/90 text-white text-[11px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-4 backdrop-blur-sm">
                        <i class="fa-solid fa-train"></i> Konektivitas Rel Pasoso
                    </span>
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight text-white mb-4">
                        Akses Kereta Api <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-cyan-300">Langsung ke Tanjung Priok</span>
                    </h1>
                    <p class="text-base sm:text-lg text-slate-200/90 leading-relaxed mb-8 max-w-2xl">
                        Rangkaian 60 TEU kereta kargo intermodal memotong dwell time pelabuhan dari 3,8 hari menjadi 1,8 hari tanpa hambatan kemacetan jalur arteri.
                    </p>
                    <div class="flex flex-wrap items-center gap-4">
                        <a href="#pillars" class="inline-flex items-center gap-3 px-7 py-3.5 rounded-full bg-orange-600 hover:bg-orange-500 text-white font-bold text-sm tracking-wide shadow-xl transition-all">
                            <span>Sistem Shunting TMS</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- SLIDE 3: CFS Warehouse & Smart Consolidation -->
        <div class="hero-slide" style="background-image: url('assets/img/facilities/cfs_warehouse.jpg');" data-index="2">
            <div class="absolute inset-0 hero-vignette"></div>
            <div class="relative h-full max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 flex flex-col justify-center text-white pb-24 md:pb-32">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 bg-emerald-600/90 text-white text-[11px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-4 backdrop-blur-sm">
                        <i class="fa-solid fa-warehouse"></i> CFS Warehouse Modern
                    </span>
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight text-white mb-4">
                        Konsolidasi Pintar & <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-300">Gudang CFS Berstandar WMS</span>
                    </h1>
                    <p class="text-base sm:text-lg text-slate-200/90 leading-relaxed mb-8 max-w-2xl">
                        Stuffing, stripping, dan cross-docking LCL/FCL otomatis dengan pembacaan barcode palet GS1 serta integrasi faktur perpajakan kepabeanan.
                    </p>
                    <div class="flex flex-wrap items-center gap-4">
                        <a href="#pillars" class="inline-flex items-center gap-3 px-7 py-3.5 rounded-full bg-orange-600 hover:bg-orange-500 text-white font-bold text-sm tracking-wide shadow-xl transition-all">
                            <span>Solusi WMS Konsolidasi</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- SLIDE 4: Reefer Terminal & Precision Cold Chain -->
        <div class="hero-slide" style="background-image: url('assets/img/facilities/reefer_terminal.jpg');" data-index="3">
            <div class="absolute inset-0 hero-vignette"></div>
            <div class="relative h-full max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 flex flex-col justify-center text-white pb-24 md:pb-32">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 bg-cyan-600/90 text-white text-[11px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-4 backdrop-blur-sm">
                        <i class="fa-solid fa-snowflake"></i> Rantai Pendingin Presisi
                    </span>
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight text-white mb-4">
                        Terminal Reefer & <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-300">Pemantauan Suhu IoT 24/7</span>
                    </h1>
                    <p class="text-base sm:text-lg text-slate-200/90 leading-relaxed mb-8 max-w-2xl">
                        120 soket plug points mandiri dengan sistem deteksi deviasi suhu real-time untuk kargo farmasi, daging, susu, dan buah bernilai tinggi.
                    </p>
                    <div class="flex flex-wrap items-center gap-4">
                        <a href="#highlights" class="inline-flex items-center gap-3 px-7 py-3.5 rounded-full bg-orange-600 hover:bg-orange-500 text-white font-bold text-sm tracking-wide shadow-xl transition-all">
                            <span>Spesifikasi Reefer Plug</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- SLIDE 5: Customs CEISA 4.0 & Smart Gate -->
        <div class="hero-slide" style="background-image: url('assets/img/facilities/gatehouse_in.jpg');" data-index="4">
            <div class="absolute inset-0 hero-vignette"></div>
            <div class="relative h-full max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 flex flex-col justify-center text-white pb-24 md:pb-32">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 bg-amber-600/90 text-white text-[11px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-4 backdrop-blur-sm">
                        <i class="fa-solid fa-shield-halved"></i> Host-to-Host CEISA 4.0
                    </span>
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight text-white mb-4">
                        Smart Gate Otomatis & <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-300">Clearance Bea Cukai Instan</span>
                    </h1>
                    <p class="text-base sm:text-lg text-slate-200/90 leading-relaxed mb-8 max-w-2xl">
                        Pembacaan RFID UHF ISO 18000-6C, kamera OCR pelat nomor, dan timbangan gantry 80 ton otomatis yang tersinkronisasi langsung dengan SPPB DJBC.
                    </p>
                    <div class="flex flex-wrap items-center gap-4">
                        <a href="#documents" class="inline-flex items-center gap-3 px-7 py-3.5 rounded-full bg-orange-600 hover:bg-orange-500 text-white font-bold text-sm tracking-wide shadow-xl transition-all">
                            <span>Lihat Alur Gate CEISA</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTTOM NAV TABS WITH ANIMATED PROGRESS BARS (Petronas Carousel Nav) -->
        <div class="relative z-20 w-full max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 pb-6">
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 sm:gap-4 border-t border-white/10 pt-4">
                
                <div class="banner-tab-item active" onclick="switchHeroSlide(0)">
                    <div class="text-[11px] sm:text-xs tracking-wider uppercase tab-title truncate">Solusi Intermodal</div>
                    <div class="progress-track">
                        <div class="progress-bar-fill" id="progressBar0"></div>
                    </div>
                </div>

                <div class="banner-tab-item" onclick="switchHeroSlide(1)">
                    <div class="text-[11px] sm:text-xs tracking-wider uppercase tab-title truncate">Rail Siding Pasoso</div>
                    <div class="progress-track">
                        <div class="progress-bar-fill" id="progressBar1"></div>
                    </div>
                </div>

                <div class="banner-tab-item" onclick="switchHeroSlide(2)">
                    <div class="text-[11px] sm:text-xs tracking-wider uppercase tab-title truncate">CFS & Konsolidasi</div>
                    <div class="progress-track">
                        <div class="progress-bar-fill" id="progressBar2"></div>
                    </div>
                </div>

                <div class="banner-tab-item" onclick="switchHeroSlide(3)">
                    <div class="text-[11px] sm:text-xs tracking-wider uppercase tab-title truncate">Reefer Cold Chain</div>
                    <div class="progress-track">
                        <div class="progress-bar-fill" id="progressBar3"></div>
                    </div>
                </div>

                <div class="banner-tab-item col-span-2 sm:col-span-1" onclick="switchHeroSlide(4)">
                    <div class="text-[11px] sm:text-xs tracking-wider uppercase tab-title truncate">Smart Gate CEISA 4.0</div>
                    <div class="progress-track">
                        <div class="progress-bar-fill" id="progressBar4"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 4. LATEST UPDATES (Petronas Media Releases / News Section)         -->
    <!-- =================================================================== -->
    <section id="updates" class="py-20 bg-slate-950 text-white relative">
        <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6" data-aos="fade-up">
                <div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">
                        <span class="text-orange-500 font-black">Pembaruan</span> Terkini & Rilis Operasional
                    </h2>
                    <p class="text-slate-400 text-sm sm:text-base mt-2 max-w-xl">
                        Dapatkan informasi terbaru mengenai pengumuman kepabeanan, pembaruan jadwal shunting kereta, dan inisiatif digital CIDP DPMS.
                    </p>
                </div>
                <a href="#documents" class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-orange-400 hover:text-orange-300 transition-colors">
                    <span>Lihat Semua Dokumen & Rilis</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <!-- News Grid: 1 Large Featured Card + 3 Side Cards (Petronas Layout) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
                
                <!-- FEATURED CARD (Col 5) -->
                <div class="lg:col-span-5" data-aos="fade-right">
                    <a href="login.php" class="group relative block h-full min-h-[420px] rounded-3xl overflow-hidden shadow-2xl border border-white/10">
                        <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-105" style="background-image: url('assets/img/facilities/customs_office.jpg');"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-transparent"></div>
                        
                        <!-- Badge Featured -->
                        <div class="absolute top-6 left-6">
                            <span class="bg-orange-600 text-white font-bold text-[11px] uppercase tracking-wider px-3.5 py-1.5 rounded-full shadow-lg">
                                Rilis Utama
                            </span>
                        </div>

                        <!-- Content Bottom -->
                        <div class="absolute bottom-6 left-6 right-6">
                            <div class="flex items-center justify-between text-xs font-mono text-orange-400 mb-2">
                                <span>KEPABEANAN & CEISA</span>
                                <span>12 Oktober 2026</span>
                            </div>
                            <h3 class="text-xl sm:text-2xl font-bold text-white leading-snug group-hover:text-orange-300 transition-colors">
                                CIDP Resmikan Integrasi API Host-to-Host CEISA 4.0 Bea Cukai untuk Otomasi SPPB 24 Jam
                            </h3>
                            <div class="mt-4 flex items-center gap-2 text-xs font-semibold text-slate-300 group-hover:text-white transition-colors">
                                <span>Baca Rilis Lengkap</span>
                                <i class="fa-solid fa-arrow-right text-[10px] transform group-hover:translate-x-1 transition-transform"></i>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- RIGHT CARDS (Col 7 - 3 Cards Horizontal / Stack) -->
                <div class="lg:col-span-7 flex flex-col justify-between gap-6" data-aos="fade-left">
                    
                    <!-- Card 1 -->
                    <a href="#highlights" class="group flex flex-col sm:flex-row gap-5 p-5 rounded-2xl bg-slate-900/80 border border-slate-800 hover:border-orange-500/50 hover:bg-slate-800/60 transition-all duration-200">
                        <div class="sm:w-44 h-36 rounded-xl overflow-hidden flex-shrink-0 bg-cover bg-center" style="background-image: url('assets/img/facilities/workshop_mr.jpg');"></div>
                        <div class="flex flex-col justify-center">
                            <div class="flex items-center justify-between text-xs font-mono text-slate-400 mb-2">
                                <span class="text-sky-400 font-semibold">MAINTENANCE & REPAIR</span>
                                <span>8 Oktober 2026</span>
                            </div>
                            <h4 class="text-base sm:text-lg font-bold text-white group-hover:text-orange-400 transition-colors leading-snug">
                                Workshop M&R Rampungkan Kalibrasi Berkala 3 Unit Reach Stacker Kalmar DRG450
                            </h4>
                            <p class="text-xs text-slate-400 mt-2 line-clamp-2">
                                Uji beban dinamis dan pemeriksaan sertifikasi twistlock memastikan kesiapan alat berat dalam melayani penumpukan 4-tier berstandar keselamatan internasional.
                            </p>
                        </div>
                    </a>

                    <!-- Card 2 -->
                    <a href="#highlights" class="group flex flex-col sm:flex-row gap-5 p-5 rounded-2xl bg-slate-900/80 border border-slate-800 hover:border-orange-500/50 hover:bg-slate-800/60 transition-all duration-200">
                        <div class="sm:w-44 h-36 rounded-xl overflow-hidden flex-shrink-0 bg-cover bg-center" style="background-image: url('assets/img/facilities/datacenter_noc.jpg');"></div>
                        <div class="flex flex-col justify-center">
                            <div class="flex items-center justify-between text-xs font-mono text-slate-400 mb-2">
                                <span class="text-purple-400 font-semibold">INFRASTRUKTUR NOC</span>
                                <span>1 Oktober 2026</span>
                            </div>
                            <h4 class="text-base sm:text-lg font-bold text-white group-hover:text-orange-400 transition-colors leading-snug">
                                Peningkatan Data Center Tier-3 CIDP Mencapai Uptime 99.98% untuk Sistem Billing & IoT
                            </h4>
                            <p class="text-xs text-slate-400 mt-2 line-clamp-2">
                                Pemantauan 24/7 telemetri 26 titik hardware IoT dan sistem auto-failover cloud menjamin operasional gerbang dan penumpukan tanpa henti.
                            </p>
                        </div>
                    </a>

                    <!-- Card 3 -->
                    <a href="#about" class="group flex flex-col sm:flex-row gap-5 p-5 rounded-2xl bg-slate-900/80 border border-slate-800 hover:border-orange-500/50 hover:bg-slate-800/60 transition-all duration-200">
                        <div class="sm:w-44 h-36 rounded-xl overflow-hidden flex-shrink-0 bg-cover bg-center" style="background-image: url('assets/img/facilities/kantor_utama.jpg');"></div>
                        <div class="flex flex-col justify-center">
                            <div class="flex items-center justify-between text-xs font-mono text-slate-400 mb-2">
                                <span class="text-emerald-400 font-semibold">KEMITRAAN EKOSISTEM</span>
                                <span>24 September 2026</span>
                            </div>
                            <h4 class="text-base sm:text-lg font-bold text-white group-hover:text-orange-400 transition-colors leading-snug">
                                Pembukaan Loket Satu Atap Bea Cukai, Bank Mandiri, dan Kantor Perwakilan ALFI
                            </h4>
                            <p class="text-xs text-slate-400 mt-2 line-clamp-2">
                                Percepat proses pengurusan dokumen ekspor-impor dan pembayaran nota tagihan jasa pelabuhan kering hanya dalam hitungan menit di Gedung Utama.
                            </p>
                        </div>
                    </a>

                </div>

            </div>

        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 5. OUR INTERMODAL JOURNEY (Petronas 3-Card Transition Journey)       -->
    <!-- =================================================================== -->
    <section id="pillars" class="py-24 bg-slate-900 relative">
        <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6" data-aos="fade-up">
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-orange-400">TRIDHARMA LOGISTIK CIDP</span>
                    <h2 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white mt-2">
                        Pilar <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-amber-300">Ekosistem Digital</span>
                    </h2>
                    <p class="text-slate-400 text-sm sm:text-base mt-3 max-w-2xl leading-relaxed">
                        Tiga serangkai sistem yang dirancang selaras untuk melacak, menggerakkan, dan mengonsolidasi muatan dari hulu ke hilir dengan ketepatan data 100%.
                    </p>
                </div>
                <a href="dashboard.php" class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-orange-400 hover:text-orange-300 transition-colors">
                    <span>Lihat Seluruh Modul Terpadu</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <!-- 3 Tall Rounded Image Cards with Slide-Up Hover Overlays -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- CARD 1: Yard Management System (YMS) -->
                <div class="pillar-card group" data-aos="fade-up" data-aos-delay="100">
                    <div class="pillar-bg" style="background-image: url('assets/img/facilities/stacking_yard.jpg');"></div>
                    <div class="pillar-overlay"></div>
                    
                    <!-- Default State (Title at Bottom) -->
                    <div class="pillar-body-default">
                        <div class="text-xs font-mono text-orange-400 uppercase tracking-widest mb-1">01 &bull; MANAJEMEN LAPANGAN</div>
                        <h3 class="text-2xl font-black text-white">Yard Management System</h3>
                        <div class="mt-3 flex items-center gap-2 text-xs text-slate-300">
                            <span>Arahkan kursor untuk rincian</span>
                            <i class="fa-solid fa-arrow-up text-[10px]"></i>
                        </div>
                    </div>

                    <!-- Hover State (Slide Up Full Content) -->
                    <div class="pillar-body-hover">
                        <div class="text-xs font-mono text-orange-400 uppercase tracking-widest mb-1">01 &bull; YMS ENGINE</div>
                        <h4 class="text-xl font-black text-white mb-2">Presisi Spasial & Zero Misplacement</h4>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            Otomasi penataan Blok A hingga E dengan batasan 4-tier stacking, kalkulasi dwell time otomatis, dan pelacakan GPS alat berat Kalmar Reach Stacker dalam visualisasi denah interaktif.
                        </p>
                        <div class="pt-2 border-t border-white/20 flex items-center justify-between">
                            <span class="text-[11px] font-mono text-emerald-400"><i class="fa-solid fa-check mr-1"></i> 100% Akurasi Slot</span>
                            <a href="dashboard.php?page=denah" class="text-xs font-bold text-orange-400 hover:text-orange-300 flex items-center gap-1.5">
                                <span>Buka Denah</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: Transport Management System (TMS) -->
                <div class="pillar-card group" data-aos="fade-up" data-aos-delay="200">
                    <div class="pillar-bg" style="background-image: url('assets/img/facilities/rail_siding.jpg');"></div>
                    <div class="pillar-overlay"></div>
                    
                    <!-- Default State -->
                    <div class="pillar-body-default">
                        <div class="text-xs font-mono text-sky-400 uppercase tracking-widest mb-1">02 &bull; ANTARMODA KERETA & TRUK</div>
                        <h3 class="text-2xl font-black text-white">Transport Management System</h3>
                        <div class="mt-3 flex items-center gap-2 text-xs text-slate-300">
                            <span>Arahkan kursor untuk rincian</span>
                            <i class="fa-solid fa-arrow-up text-[10px]"></i>
                        </div>
                    </div>

                    <!-- Hover State -->
                    <div class="pillar-body-hover">
                        <div class="text-xs font-mono text-sky-400 uppercase tracking-widest mb-1">02 &bull; TMS ENGINE</div>
                        <h4 class="text-xl font-black text-white mb-2">Sinkronisasi Shunting Pasoso</h4>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            Manajemen jadwal rangkaian 60 TEU kereta api logistik rute Tanjung Priok &ndash; CIDP, serta pengaturan internal shunting Head Truck KTT-01/02/03 demi kelancaran gate dispatch.
                        </p>
                        <div class="pt-2 border-t border-white/20 flex items-center justify-between">
                            <span class="text-[11px] font-mono text-sky-400"><i class="fa-solid fa-train mr-1"></i> Rangkaian 60 TEU</span>
                            <a href="dashboard.php?page=trucking" class="text-xs font-bold text-orange-400 hover:text-orange-300 flex items-center gap-1.5">
                                <span>Buka Modul TMS</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- CARD 3: Warehouse Management System (WMS & CFS) -->
                <div class="pillar-card group" data-aos="fade-up" data-aos-delay="300">
                    <div class="pillar-bg" style="background-image: url('assets/img/facilities/cfs_warehouse.jpg');"></div>
                    <div class="pillar-overlay"></div>
                    
                    <!-- Default State -->
                    <div class="pillar-body-default">
                        <div class="text-xs font-mono text-emerald-400 uppercase tracking-widest mb-1">03 &bull; CFS & GUDANG KONSOLIDASI</div>
                        <h3 class="text-2xl font-black text-white">Warehouse Management System</h3>
                        <div class="mt-3 flex items-center gap-2 text-xs text-slate-300">
                            <span>Arahkan kursor untuk rincian</span>
                            <i class="fa-solid fa-arrow-up text-[10px]"></i>
                        </div>
                    </div>

                    <!-- Hover State -->
                    <div class="pillar-body-hover">
                        <div class="text-xs font-mono text-emerald-400 uppercase tracking-widest mb-1">03 &bull; WMS & CFS ENGINE</div>
                        <h4 class="text-xl font-black text-white mb-2">Stripping, Stuffing & Barcoding</h4>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            Pengelolaan alur konsolidasi LCL/FCL, pelacakan barcode palet berstandar GS1, penataan racking gudang CFS, dan koneksi langsung dengan penagihan biaya storage ERP.
                        </p>
                        <div class="pt-2 border-t border-white/20 flex items-center justify-between">
                            <span class="text-[11px] font-mono text-emerald-400"><i class="fa-solid fa-barcode mr-1"></i> Barcode GS1 Ready</span>
                            <a href="dashboard.php?page=cfs" class="text-xs font-bold text-orange-400 hover:text-orange-300 flex items-center gap-1.5">
                                <span>Buka Modul CFS</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 6. OPERATIONAL & FINANCIAL RESULTS (Petronas Glass Stat Cards)     -->
    <!-- =================================================================== -->
    <section id="performance" class="relative py-24 bg-slate-950 text-white overflow-hidden">
        <!-- Full-bleed Background Image with Dark Tint -->
        <div class="absolute inset-0 bg-cover bg-center opacity-30" style="background-image: url('assets/img/facilities/datacenter_noc.jpg');"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/90 to-slate-950/70"></div>

        <div class="relative z-10 max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Heading -->
            <div class="max-w-2xl mb-14" data-aos="fade-right">
                <span class="text-xs font-mono text-orange-400 uppercase tracking-widest">PENGUMUMAN KINERJA OPERASIONAL & HSSE</span>
                <h2 class="text-3xl sm:text-5xl font-black text-white mt-2 leading-tight">
                    Hasil Kinerja Terminal <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-amber-300">Tahun Berjalan 2026</span>
                </h2>
                <p class="text-slate-300 text-sm sm:text-base mt-4 leading-relaxed">
                    Di tengah tantangan volatilitas logistik nasional, Conclusion Intermodal Dry Port membuktikan efisiensi melalui otomasi penuh dan koridor kereta kargo terpadu.
                </p>
            </div>

            <!-- 3 Rounded Glass Stat Cards (Petronas Style) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
                
                <div class="rounded-3xl p-8 bg-white/10 backdrop-blur-md border border-white/20 shadow-xl" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-xs font-mono text-slate-300 uppercase tracking-wider">Kapasitas Throughput</div>
                    <div class="text-4xl sm:text-5xl font-extrabold text-orange-400 my-4 font-mono">45.000 <span class="text-2xl text-white">TEU</span></div>
                    <div class="text-xs text-slate-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-trend-up text-emerald-400"></i>
                        <span>Meningkat 28% dibandingkan tahun buku 2025</span>
                    </div>
                </div>

                <div class="rounded-3xl p-8 bg-white/10 backdrop-blur-md border border-white/20 shadow-xl" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-xs font-mono text-slate-300 uppercase tracking-wider">Rata-Rata Dwell Time</div>
                    <div class="text-4xl sm:text-5xl font-extrabold text-sky-400 my-4 font-mono">1.8 <span class="text-2xl text-white">Hari</span></div>
                    <div class="text-xs text-slate-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-clock text-sky-400"></i>
                        <span>Jauh di bawah rata-rata pelabuhan konvensional 3.8 hari</span>
                    </div>
                </div>

                <div class="rounded-3xl p-8 bg-white/10 backdrop-blur-md border border-white/20 shadow-xl" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-xs font-mono text-slate-300 uppercase tracking-wider">Reduksi Emisi Gas Buang (CO₂)</div>
                    <div class="text-4xl sm:text-5xl font-extrabold text-emerald-400 my-4 font-mono">-76% <span class="text-2xl text-white">CO₂</span></div>
                    <div class="text-xs text-slate-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-leaf text-emerald-400"></i>
                        <span>Pengalihan angkutan truk ke rangkaian kereta logistik hijau</span>
                    </div>
                </div>

            </div>

            <!-- KEY DOCUMENTS (Petronas Key Documents Download Row) -->
            <div id="documents" class="pt-8 border-t border-white/10" data-aos="fade-up">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-file-pdf text-red-400"></i>
                        <span>Dokumen Regulasi, SOP & Laporan Resmi</span>
                    </h3>
                    <span class="text-xs font-mono text-slate-400">Format: Dokumen Resmi Terverifikasi</span>
                </div>

                <div class="space-y-3">
                    
                    <!-- Document Row 1 -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-colors gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-orange-600/20 text-orange-400 flex items-center justify-center font-bold text-sm">
                                SOP
                            </div>
                            <div>
                                <div class="text-sm font-bold text-white">Standar Operasional Prosedur (SOP) Gate In/Out & Pemeriksaan CEISA 4.0</div>
                                <div class="text-[11px] text-slate-400">Pedoman resmi pengemudi truk, kartu RFID, dan validasi SPPB Bea Cukai</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 text-xs font-mono text-slate-400">
                            <span>28 Feb 2026</span>
                            <a href="docs/CIDP_DPMS_Comprehensive_Tutorial_Guide.docx" download class="px-4 py-2 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-bold transition-colors flex items-center gap-2">
                                <i class="fa-solid fa-download text-xs"></i>
                                <span>Unduh</span>
                            </a>
                        </div>
                    </div>

                    <!-- Document Row 2 -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-colors gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-sky-600/20 text-sky-400 flex items-center justify-center font-bold text-sm">
                                LPR
                            </div>
                            <div>
                                <div class="text-sm font-bold text-white">Laporan Kinerja Integrasi Antarmoda Dry Port FY2025/2026</div>
                                <div class="text-[11px] text-slate-400">Analisis statistik dwell time, penghematan bahan bakar, dan throughput kontainer</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 text-xs font-mono text-slate-400">
                            <span>15 Jan 2026</span>
                            <a href="docs/CIDP_DPMS_Comprehensive_Tutorial_Guide.docx" download class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold transition-colors flex items-center gap-2">
                                <i class="fa-solid fa-download text-xs"></i>
                                <span>Unduh</span>
                            </a>
                        </div>
                    </div>

                    <!-- Document Row 3 -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-colors gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-600/20 text-emerald-400 flex items-center justify-center font-bold text-sm">
                                K3
                            </div>
                            <div>
                                <div class="text-sm font-bold text-white">Manual Kepatuhan HSSE & Tanggap Darurat Dangerous Goods (DG Yard)</div>
                                <div class="text-[11px] text-slate-400">Protokol keselamatan kerja zona penumpukan muatan B3 berstandar IMO</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 text-xs font-mono text-slate-400">
                            <span>10 Jan 2026</span>
                            <a href="docs/CIDP_DPMS_Comprehensive_Tutorial_Guide.docx" download class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold transition-colors flex items-center gap-2">
                                <i class="fa-solid fa-download text-xs"></i>
                                <span>Unduh</span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 7. HIGHLIGHTS (Petronas Accordion + Dynamic Image Swap)             -->
    <!-- =================================================================== -->
    <section id="highlights" class="py-24 bg-slate-900 text-white relative">
        <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header -->
            <div class="mb-14" data-aos="fade-up">
                <span class="text-xs font-mono text-orange-400 uppercase tracking-widest">INFRASTRUKTUR FISIK KELAS DUNIA</span>
                <h2 class="text-3xl sm:text-5xl font-black text-white mt-2">
                    Sorotan <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-amber-300">Fasilitas Unggulan</span>
                </h2>
                <p class="text-slate-400 text-sm sm:text-base mt-3 max-w-xl">
                    Klik tiap fasilitas untuk melihat visualisasi dan spesifikasi operasionalnya.
                </p>
            </div>

            <!-- 2-Column: Left Large Square Image Stage + Right Accordion -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                
                <!-- LEFT IMAGE STAGE (Col 6) -->
                <div class="lg:col-span-6" data-aos="fade-right">
                    <div class="relative w-full aspect-square rounded-3xl overflow-hidden shadow-2xl border border-white/10">
                        <div id="highlightImageDisplay" class="highlight-image-stage absolute inset-0" style="background-image: url('assets/img/facilities/datacenter_noc.jpg');"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent"></div>
                        <div class="absolute bottom-6 left-6 right-6">
                            <span id="highlightBadge" class="bg-orange-600 text-white text-[11px] font-bold uppercase tracking-wider px-3 py-1 rounded-full">
                                PUSAT KENDALI NOC
                            </span>
                            <h4 id="highlightCaption" class="text-xl font-bold text-white mt-2">
                                Data Center Tier-3 & Network Operations Center (NOC)
                            </h4>
                        </div>
                    </div>
                </div>

                <!-- RIGHT ACCORDION LIST (Col 6) -->
                <div class="lg:col-span-6 space-y-4" data-aos="fade-left">
                    
                    <!-- Accordion Item 1 (Active by default) -->
                    <div class="accordion-card p-5 rounded-2xl bg-slate-800/80 border border-orange-500/60 cursor-pointer transition-all" onclick="selectHighlight(0, 'assets/img/facilities/datacenter_noc.jpg', 'PUSAT KENDALI NOC', 'Data Center Tier-3 & Network Operations Center (NOC)')">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-orange-600 text-white flex items-center justify-center font-bold text-xs">
                                    01
                                </div>
                                <h3 class="font-bold text-white text-base sm:text-lg">Data Center Tier-3 & NOC CIDP</h3>
                            </div>
                            <i class="fa-solid fa-chevron-right text-orange-400 text-xs"></i>
                        </div>
                        <div class="accordion-content mt-3 text-xs text-slate-300 leading-relaxed pl-11">
                            Pusat komando telemetri 26 perangkat hardware IoT, server redundansi high-availability untuk DPMS, dan pengawasan CCTV AI 24/7 di seluruh perimeter 35 Ha.
                        </div>
                    </div>

                    <!-- Accordion Item 2 -->
                    <div class="accordion-card p-5 rounded-2xl bg-slate-900/60 border border-slate-800 cursor-pointer hover:border-slate-700 transition-all" onclick="selectHighlight(1, 'assets/img/facilities/workshop_mr.jpg', 'BENGKEL ALAT & KONTAINER', 'Workshop Maintenance & Repair (M&R) Kalmar')">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-slate-800 text-slate-300 flex items-center justify-center font-bold text-xs">
                                    02
                                </div>
                                <h3 class="font-bold text-white text-base sm:text-lg">Workshop Maintenance & Repair (M&R)</h3>
                            </div>
                            <i class="fa-solid fa-chevron-right text-slate-500 text-xs"></i>
                        </div>
                        <div class="accordion-content mt-3 text-xs text-slate-300 leading-relaxed pl-11 hidden">
                            Fasilitas perawatan alat berat Kalmar DRG450 Reach Stacker, pengelasan struktur peti kemas, pencucian kimia berstandar IICL, dan perbaikan segel ISO.
                        </div>
                    </div>

                    <!-- Accordion Item 3 -->
                    <div class="accordion-card p-5 rounded-2xl bg-slate-900/60 border border-slate-800 cursor-pointer hover:border-slate-700 transition-all" onclick="selectHighlight(2, 'assets/img/facilities/gatehouse_in.jpg', 'GERBANG OTOMATIS & TIMBANGAN', 'Gatehouse Otomatis 4 Lajur OCR & Timbangan Gantry')">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-slate-800 text-slate-300 flex items-center justify-center font-bold text-xs">
                                    03
                                </div>
                                <h3 class="font-bold text-white text-base sm:text-lg">Gatehouse Otomatis 4 Lajur & Timbangan</h3>
                            </div>
                            <i class="fa-solid fa-chevron-right text-slate-500 text-xs"></i>
                        </div>
                        <div class="accordion-content mt-3 text-xs text-slate-300 leading-relaxed pl-11 hidden">
                            Empat lajur cerdas dilengkapi kamera OCR nomor kontainer dan pelat truk, scanner kartu RFID UHF, pembaca barcode SPK, dan jembatan timbang gantry 80 ton.
                        </div>
                    </div>

                    <!-- Accordion Item 4 -->
                    <div class="accordion-card p-5 rounded-2xl bg-slate-900/60 border border-slate-800 cursor-pointer hover:border-slate-700 transition-all" onclick="selectHighlight(3, 'assets/img/facilities/kantor_utama.jpg', 'LAYANAN SATU ATAP', 'Gedung Administrasi & Hanggar Bea Cukai Hanggar CIDP')">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-slate-800 text-slate-300 flex items-center justify-center font-bold text-xs">
                                    04
                                </div>
                                <h3 class="font-bold text-white text-base sm:text-lg">Gedung Kantor Utama & Bea Cukai</h3>
                            </div>
                            <i class="fa-solid fa-chevron-right text-slate-500 text-xs"></i>
                        </div>
                        <div class="accordion-content mt-3 text-xs text-slate-300 leading-relaxed pl-11 hidden">
                            Pusat pelayanan administrasi terpadu satu atap (One Stop Service) mencakup perwakilan Hanggar Bea Cukai, perbankan, customer care, dan verifikasi dokumen pengapalan.
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 8. OUR NETWORK (Petronas Collapsible Flip Grid)                    -->
    <!-- =================================================================== -->
    <section id="network" class="py-24 bg-slate-950 text-white border-t border-slate-800">
        <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Collapsible Section Header (Petronas discover-more header) -->
            <div class="flex items-center justify-between mb-8 cursor-pointer" onclick="toggleNetworkSection()">
                <div>
                    <span class="text-xs font-mono text-orange-400 uppercase tracking-widest">JEJAK JARINGAN KAMI</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white mt-1">
                        Modul Sistem DPMS & Jaringan Mitra
                    </h2>
                </div>
                <button class="w-12 h-12 rounded-full bg-slate-800 hover:bg-orange-600 text-white flex items-center justify-center transition-all duration-200" title="Buka/Tutup Jaringan">
                    <i class="fa-solid fa-chevron-down transition-transform duration-300" id="networkToggleCaret"></i>
                </button>
            </div>

            <!-- Collapsible Body Content -->
            <div id="networkContent" class="transition-all duration-500 overflow-hidden">
                
                <!-- Subtitle 1: 8 Core Modules of DPMS (Flip Cards) -->
                <div class="mb-12">
                    <div class="text-xs font-mono text-slate-400 uppercase tracking-wider mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-layer-group text-orange-400"></i>
                        <span>8 Modul Fungsional Conclusion DPMS</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        
                        <!-- Flip Card 1: Gate -->
                        <div class="dm-card-box">
                            <div class="dm-card-inner">
                                <div class="dm-face-front">
                                    <div class="w-10 h-10 rounded-xl bg-orange-600/20 text-orange-400 flex items-center justify-center text-lg">
                                        <i class="fa-solid fa-id-card-clip"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white text-sm">Gate Automation</h4>
                                        <span class="text-[11px] text-slate-400">RFID UHF & OCR Camera</span>
                                    </div>
                                </div>
                                <div class="dm-face-back">
                                    <p class="text-xs leading-relaxed">
                                        Deteksi otomatis truk di timbangan, validasi e-Ticket SPK, dan cetak instruksi slot CMS secara real-time.
                                    </p>
                                    <div class="text-right">
                                        <span class="text-[11px] font-bold uppercase underline">Buka Gate &rarr;</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Flip Card 2: Yard Stacking -->
                        <div class="dm-card-box">
                            <div class="dm-card-inner">
                                <div class="dm-face-front">
                                    <div class="w-10 h-10 rounded-xl bg-sky-600/20 text-sky-400 flex items-center justify-center text-lg">
                                        <i class="fa-solid fa-cubes"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white text-sm">Yard Stacking Engine</h4>
                                        <span class="text-[11px] text-slate-400">Slot Blok A-E & 4-Tier</span>
                                    </div>
                                </div>
                                <div class="dm-face-back">
                                    <p class="text-xs leading-relaxed">
                                        Optimasi penataan kontainer berdasarkan Port of Discharge (POD) dan tanggal jadwal keberangkatan KA.
                                    </p>
                                    <div class="text-right">
                                        <span class="text-[11px] font-bold uppercase underline">Lihat Yard &rarr;</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Flip Card 3: Rail Siding -->
                        <div class="dm-card-box">
                            <div class="dm-card-inner">
                                <div class="dm-face-front">
                                    <div class="w-10 h-10 rounded-xl bg-purple-600/20 text-purple-400 flex items-center justify-center text-lg">
                                        <i class="fa-solid fa-train-subway"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white text-sm">Rail Siding Pasoso</h4>
                                        <span class="text-[11px] text-slate-400">60 TEU Rangkaian Kereta</span>
                                    </div>
                                </div>
                                <div class="dm-face-back">
                                    <p class="text-xs leading-relaxed">
                                        Manajemen jadwal kedatangan gerbong datar, proses lift on/off RTG, dan manifest perjalanan KA logistik.
                                    </p>
                                    <div class="text-right">
                                        <span class="text-[11px] font-bold uppercase underline">Lihat Rail &rarr;</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Flip Card 4: CFS & Konsolidasi -->
                        <div class="dm-card-box">
                            <div class="dm-card-inner">
                                <div class="dm-face-front">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-600/20 text-emerald-400 flex items-center justify-center text-lg">
                                        <i class="fa-solid fa-dolly"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white text-sm">CFS Consolidation</h4>
                                        <span class="text-[11px] text-slate-400">Barcode GS1 Pallet</span>
                                    </div>
                                </div>
                                <div class="dm-face-back">
                                    <p class="text-xs leading-relaxed">
                                        Pencatatan stuffing dan stripping barang parsial LCL di gudang CFS dengan pelacakan lokasi rak bertingkat.
                                    </p>
                                    <div class="text-right">
                                        <span class="text-[11px] font-bold uppercase underline">Buka CFS &rarr;</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Flip Card 5: Reefer Telematics -->
                        <div class="dm-card-box">
                            <div class="dm-card-inner">
                                <div class="dm-face-front">
                                    <div class="w-10 h-10 rounded-xl bg-cyan-600/20 text-cyan-400 flex items-center justify-center text-lg">
                                        <i class="fa-solid fa-temperature-arrow-down"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white text-sm">Reefer Telematics</h4>
                                        <span class="text-[11px] text-slate-400">120 Plug IoT Sensors</span>
                                    </div>
                                </div>
                                <div class="dm-face-back">
                                    <p class="text-xs leading-relaxed">
                                        Monitoring data logger temperatur dan daya genset reefer container 24 jam nonstop dengan peringatan alarm instan.
                                    </p>
                                    <div class="text-right">
                                        <span class="text-[11px] font-bold uppercase underline">Buka Reefer &rarr;</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Flip Card 6: Kalmar Equipment GPS -->
                        <div class="dm-card-box">
                            <div class="dm-card-inner">
                                <div class="dm-face-front">
                                    <div class="w-10 h-10 rounded-xl bg-amber-600/20 text-amber-400 flex items-center justify-center text-lg">
                                        <i class="fa-solid fa-truck-moving"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white text-sm">Equipment Telemetry</h4>
                                        <span class="text-[11px] text-slate-400">Reach Stacker RS-01..03</span>
                                    </div>
                                </div>
                                <div class="dm-face-back">
                                    <p class="text-xs leading-relaxed">
                                        Pantau konsumsi BBM, jam operasi, lokasi GPS akurat, dan status kargo yang sedang diangkat secara real-time.
                                    </p>
                                    <div class="text-right">
                                        <span class="text-[11px] font-bold uppercase underline">Buka Telemetri &rarr;</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Flip Card 7: Customs CEISA 4.0 -->
                        <div class="dm-card-box">
                            <div class="dm-card-inner">
                                <div class="dm-face-front">
                                    <div class="w-10 h-10 rounded-xl bg-red-600/20 text-red-400 flex items-center justify-center text-lg">
                                        <i class="fa-solid fa-stamp"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white text-sm">Bea Cukai CEISA 4.0</h4>
                                        <span class="text-[11px] text-slate-400">Host-to-Host Host API</span>
                                    </div>
                                </div>
                                <div class="dm-face-back">
                                    <p class="text-xs leading-relaxed">
                                        Sinkronisasi otomatis nomor SPPB, PEB, PIB, dan status jalur hijau/merah langsung dari server DJBC.
                                    </p>
                                    <div class="text-right">
                                        <span class="text-[11px] font-bold uppercase underline">Buka CEISA &rarr;</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Flip Card 8: Billing ERP & Tarif -->
                        <div class="dm-card-box">
                            <div class="dm-card-inner">
                                <div class="dm-face-front">
                                    <div class="w-10 h-10 rounded-xl bg-green-600/20 text-green-400 flex items-center justify-center text-lg">
                                        <i class="fa-solid fa-receipt"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white text-sm">Billing ERP Engine</h4>
                                        <span class="text-[11px] text-slate-400">e-Invoice & Progresif</span>
                                    </div>
                                </div>
                                <div class="dm-face-back">
                                    <p class="text-xs leading-relaxed">
                                        Kalkulasi otomatis biaya lift on/off, penumpukan progresif, plug-in reefer, dan penerbitan nota e-Faktur pajak.
                                    </p>
                                    <div class="text-right">
                                        <span class="text-[11px] font-bold uppercase underline">Buka Billing &rarr;</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Subtitle 2: Mitra Terintegrasi -->
                <div class="pt-8 border-t border-slate-800">
                    <div class="text-xs font-mono text-slate-400 uppercase tracking-wider mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-handshake text-sky-400"></i>
                        <span>Ekosistem Mitra Logistik Terintegrasi</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                        <div class="p-4 rounded-2xl bg-white/5 border border-white/10 hover:border-orange-500/40 transition-colors">
                            <div class="font-extrabold text-sm sm:text-base text-white">KAI LOGISTIK</div>
                            <div class="text-[10px] text-slate-400 mt-1">Operator Kereta Intermodal</div>
                        </div>
                        <div class="p-4 rounded-2xl bg-white/5 border border-white/10 hover:border-orange-500/40 transition-colors">
                            <div class="font-extrabold text-sm sm:text-base text-white">PELINDO</div>
                            <div class="text-[10px] text-slate-400 mt-1">Pelabuhan Tanjung Priok</div>
                        </div>
                        <div class="p-4 rounded-2xl bg-white/5 border border-white/10 hover:border-orange-500/40 transition-colors">
                            <div class="font-extrabold text-sm sm:text-base text-white">BEA CUKAI</div>
                            <div class="text-[10px] text-slate-400 mt-1">CEISA 4.0 H2H Clearance</div>
                        </div>
                        <div class="p-4 rounded-2xl bg-white/5 border border-white/10 hover:border-orange-500/40 transition-colors">
                            <div class="font-extrabold text-sm sm:text-base text-white">ALFI / ILFA</div>
                            <div class="text-[10px] text-slate-400 mt-1">Asosiasi Forwarder Indonesia</div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 9. ABOUT / CALL TO ACTION (Corporate Banner)                       -->
    <!-- =================================================================== -->
    <section id="about" class="py-20 bg-gradient-to-br from-orange-600 via-orange-700 to-slate-900 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px]"></div>
        
        <div class="relative z-10 max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="inline-flex items-center gap-2 bg-white/20 text-white text-[11px] font-bold uppercase tracking-widest px-4 py-1.5 rounded-full mb-6 backdrop-blur-sm">
                <i class="fa-solid fa-crown"></i> Solusi Dry Port Terdepan di Indonesia
            </span>
            <h2 class="text-3xl sm:text-5xl font-black tracking-tight max-w-4xl mx-auto leading-tight">
                Siap Meningkatkan Efisiensi Rantai Pasok Anda Bersama Conclusion DPMS?
            </h2>
            <p class="text-slate-100 text-sm sm:text-base mt-4 max-w-2xl mx-auto leading-relaxed">
                Akses konsol kendali operasional terpadu, pantau pergerakan kontainer secara langsung, atau jalankan simulasi 3D Digital Twin sekarang.
            </p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a href="login.php" class="inline-flex items-center gap-3 px-8 py-4 rounded-full bg-slate-950 hover:bg-slate-900 text-white font-extrabold text-xs uppercase tracking-wider shadow-2xl transition-all duration-200 transform hover:scale-105">
                    <i class="fa-solid fa-right-to-bracket text-orange-400"></i>
                    <span>Masuk ke Konsol DPMS</span>
                </a>
                <a href="dashboard.php?page=simulator" class="inline-flex items-center gap-2 px-8 py-4 rounded-full bg-white/20 hover:bg-white/30 text-white font-bold text-xs uppercase tracking-wider backdrop-blur-md border border-white/30 transition-all">
                    <i class="fa-solid fa-vr-cardboard"></i>
                    <span>Buka Simulasi 3D Real-Time</span>
                </a>
            </div>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 10. FOOTER (Petronas Multilevel Corporate Footer)                  -->
    <!-- =================================================================== -->
    <footer class="bg-slate-950 text-slate-400 text-xs border-t border-slate-800">
        <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-16">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 mb-14">
                
                <!-- Col 1: Brand Info -->
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <img src="assets/img/logo.png" alt="Conclusion Logo" class="h-10 w-auto">
                        <div>
                            <span class="font-black text-lg tracking-tight uppercase text-white">CONCLUSION</span>
                            <div class="text-[10px] text-orange-400 font-bold uppercase tracking-wider">Intermodal Dry Port &bull; DPMS</div>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed max-w-sm mb-6">
                        Pusat logistik terintegrasi 35 Hektare berstandar internasional yang menghubungkan ekosistem pelabuhan laut, kereta api logistik, dan pergudangan modern dengan kepatuhan Bea Cukai CEISA 4.0.
                    </p>
                    <div class="text-[11px] text-slate-500">
                        Lokasi: Koridor Industri Intermodal &bull; Hub Cikarang &ndash; Tanjung Priok
                    </div>
                </div>

                <!-- Col 2: Solusi Fungsional -->
                <div>
                    <h5 class="text-white font-bold uppercase tracking-wider text-xs mb-4">Solusi DPMS</h5>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="#pillars" class="hover:text-orange-400 transition-colors">Yard Management (YMS)</a></li>
                        <li><a href="#pillars" class="hover:text-orange-400 transition-colors">Transport & Shunting (TMS)</a></li>
                        <li><a href="#pillars" class="hover:text-orange-400 transition-colors">CFS & Konsolidasi (WMS)</a></li>
                        <li><a href="#highlights" class="hover:text-orange-400 transition-colors">Reefer Cold Chain 24/7</a></li>
                        <li><a href="dashboard.php?page=simulator" class="hover:text-orange-400 transition-colors">3D Digital Twin Engine</a></li>
                    </ul>
                </div>

                <!-- Col 3: Regulasi & Dokumen -->
                <div>
                    <h5 class="text-white font-bold uppercase tracking-wider text-xs mb-4">Regulasi & Tata Kelola</h5>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="#documents" class="hover:text-orange-400 transition-colors">SOP Gate & CEISA 4.0</a></li>
                        <li><a href="#documents" class="hover:text-orange-400 transition-colors">Kebijakan K3 & HSSE Terminal</a></li>
                        <li><a href="#documents" class="hover:text-orange-400 transition-colors">Laporan Emisi Net-Zero 2050</a></li>
                        <li><a href="#documents" class="hover:text-orange-400 transition-colors">Pedoman Pengemudi Truk</a></li>
                        <li><a href="#documents" class="hover:text-orange-400 transition-colors">Whistleblowing System</a></li>
                    </ul>
                </div>

                <!-- Col 4: Hubungi Kami -->
                <div>
                    <h5 class="text-white font-bold uppercase tracking-wider text-xs mb-4">Hubungi Kami</h5>
                    <ul class="space-y-2.5 text-xs">
                        <li class="flex items-center gap-2"><i class="fa-solid fa-phone text-orange-400"></i> (021) 893-7890</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-envelope text-orange-400"></i> info@conclusion-dryport.com</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-headset text-orange-400"></i> Layanan NOC 24/7</li>
                        <li class="pt-2">
                            <span class="inline-block bg-emerald-950/80 border border-emerald-500/40 text-emerald-400 text-[10px] font-mono px-2.5 py-1 rounded">
                                ● Terminal Aktif 24/7
                            </span>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Footer Bottom: Copyright & Social Media Links -->
            <div class="pt-8 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-[11px] text-slate-500 text-center sm:text-left">
                    Hak Cipta &copy; <?= date('Y') ?> Conclusion Intermodal Dry Port (CIDP). Didesain dengan standar arsitektur kelas dunia. Hak cipta dilindungi undang-undang.
                </div>

                <div class="flex items-center gap-5 text-sm text-slate-400">
                    <a href="https://linkedin.com" target="_blank" class="hover:text-blue-400 transition-colors"><i class="fa-brands fa-linkedin"></i></a>
                    <a href="https://youtube.com" target="_blank" class="hover:text-red-500 transition-colors"><i class="fa-brands fa-youtube"></i></a>
                    <a href="https://instagram.com" target="_blank" class="hover:text-pink-400 transition-colors"><i class="fa-brands fa-instagram"></i></a>
                    <a href="https://twitter.com" target="_blank" class="hover:text-sky-400 transition-colors"><i class="fa-brands fa-x-twitter"></i></a>
                </div>
            </div>

        </div>
    </footer>

    <!-- =================================================================== -->
    <!-- 11. SEARCH OVERLAY MODAL (Petronas-style Fullscreen Search)         -->
    <!-- =================================================================== -->
    <div id="searchOverlayModal" class="fixed inset-0 z-50 modal-blur hidden flex items-start justify-center pt-20 px-4 transition-all">
        <div class="w-full max-w-3xl bg-slate-900 border border-slate-700 rounded-3xl p-6 sm:p-8 shadow-2xl relative text-white">
            
            <!-- Close Button -->
            <button onclick="toggleSearchModal(false)" class="absolute top-6 right-6 w-9 h-9 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition-colors">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>

            <h3 class="text-xl font-bold mb-4 flex items-center gap-2 text-white">
                <i class="fa-solid fa-magnifying-glass text-orange-400"></i>
                <span>Pencarian Cepat CIDP DPMS</span>
            </h3>

            <!-- Search Input Box -->
            <div class="relative mb-6">
                <input type="text" id="quickSearchInput" oninput="handleQuickSearch(this.value)" placeholder="Ketik kata kunci: YMS, Reefer, CEISA, SOP, Shunting, atau Tarif..." class="w-full px-5 py-3.5 pl-12 rounded-2xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:border-orange-500 font-medium text-sm">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            </div>

            <!-- Popular Quick Tags -->
            <div class="flex flex-wrap items-center gap-2 mb-6 text-xs">
                <span class="text-slate-400">Paling Sering Dicari:</span>
                <button onclick="fillSearchKeyword('YMS')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-orange-600/30 text-orange-400 border border-slate-700 transition-colors">YMS Stacking</button>
                <button onclick="fillSearchKeyword('Reefer')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-sky-600/30 text-sky-400 border border-slate-700 transition-colors">Reefer Plug</button>
                <button onclick="fillSearchKeyword('CEISA')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-amber-600/30 text-amber-400 border border-slate-700 transition-colors">CEISA 4.0</button>
                <button onclick="fillSearchKeyword('Rail')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-purple-600/30 text-purple-400 border border-slate-700 transition-colors">Kereta Pasoso</button>
                <button onclick="fillSearchKeyword('SOP')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-emerald-600/30 text-emerald-400 border border-slate-700 transition-colors">Unduh SOP</button>
            </div>

            <!-- Live Search Results Container -->
            <div id="searchResultsBox" class="space-y-2 max-h-60 overflow-y-auto pr-2">
                <div class="text-xs text-slate-500 italic p-3 text-center">
                    Masukkan kata kunci di atas untuk mencari data fasilitas, modul, atau dokumen.
                </div>
            </div>

        </div>
    </div>

    <!-- AOS JS Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <!-- =================================================================== -->
    <!-- INTERACTIVE JAVASCRIPT CONTROLLERS                                 -->
    <!-- =================================================================== -->
    <script>
        // Inisialisasi AOS (Animate on Scroll)
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof AOS !== 'undefined') {
                AOS.init({
                    duration: 700,
                    once: true,
                    offset: 80
                });
            }
        });

        // 1. NAVBAR SCROLL EFFECT (Petronas: Transparent to Solid White)
        const mainHeader = document.getElementById('mainHeader');
        const brandTextMain = document.getElementById('brandTextMain');
        const brandTextSub = document.getElementById('brandTextSub');

        window.addEventListener('scroll', function() {
            if (window.scrollY > 40) {
                mainHeader.classList.add('nav-scrolled');
                brandTextMain.classList.remove('text-white');
                brandTextMain.classList.add('text-slate-900');
            } else {
                mainHeader.classList.remove('nav-scrolled');
                brandTextMain.classList.add('text-white');
                brandTextMain.classList.remove('text-slate-900');
            }
        });

        // 2. MOBILE MENU TOGGLE
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const icon = document.getElementById('burgerIcon');
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-xmark');
            } else {
                menu.classList.add('hidden');
                icon.classList.remove('fa-xmark');
                icon.classList.add('fa-bars');
            }
        }

        // 3. HERO CAROUSEL ENGINE WITH AUTOPLAY PROGRESS BARS (Petronas-style)
        let currentSlide = 0;
        const totalSlides = 5;
        const slideDuration = 6000; // 6 detik per slide
        let progressStart = Date.now();
        let animationFrameId = null;

        const slides = document.querySelectorAll('.hero-slide');
        const tabItems = document.querySelectorAll('.banner-tab-item');
        const progressBars = [
            document.getElementById('progressBar0'),
            document.getElementById('progressBar1'),
            document.getElementById('progressBar2'),
            document.getElementById('progressBar3'),
            document.getElementById('progressBar4')
        ];

        function switchHeroSlide(targetIndex) {
            currentSlide = targetIndex;
            progressStart = Date.now();

            // Perbarui class active pada slide
            slides.forEach((slide, idx) => {
                if (idx === targetIndex) {
                    slide.classList.add('active');
                } else {
                    slide.classList.remove('active');
                }
            });

            // Perbarui class active pada tab item
            tabItems.forEach((tab, idx) => {
                if (idx === targetIndex) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });

            // Reset progress bar lainnya
            progressBars.forEach((bar, idx) => {
                if (bar) {
                    if (idx < targetIndex) {
                        bar.style.width = '100%';
                    } else if (idx > targetIndex) {
                        bar.style.width = '0%';
                    }
                }
            });
        }

        function runCarouselLoop() {
            const now = Date.now();
            const elapsed = now - progressStart;
            const percentage = Math.min((elapsed / slideDuration) * 100, 100);

            if (progressBars[currentSlide]) {
                progressBars[currentSlide].style.width = percentage + '%';
            }

            if (elapsed >= slideDuration) {
                const nextSlide = (currentSlide + 1) % totalSlides;
                switchHeroSlide(nextSlide);
            }

            animationFrameId = requestAnimationFrame(runCarouselLoop);
        }

        // Mulai autoplay carousel
        switchHeroSlide(0);
        runCarouselLoop();

        // 4. HIGHLIGHTS ACCORDION IMAGE SWAP ENGINE
        function selectHighlight(index, imagePath, badgeText, captionText) {
            const imgDisplay = document.getElementById('highlightImageDisplay');
            const badge = document.getElementById('highlightBadge');
            const caption = document.getElementById('highlightCaption');
            const cards = document.querySelectorAll('.accordion-card');

            // Fade transition out
            imgDisplay.style.opacity = '0.3';
            imgDisplay.style.transform = 'scale(0.98)';

            setTimeout(() => {
                imgDisplay.style.backgroundImage = `url('${imagePath}')`;
                badge.innerText = badgeText;
                caption.innerText = captionText;
                imgDisplay.style.opacity = '1';
                imgDisplay.style.transform = 'scale(1)';
            }, 180);

            // Update border style and open content
            cards.forEach((card, idx) => {
                const content = card.querySelector('.accordion-content');
                const chevron = card.querySelector('.fa-chevron-right');

                if (idx === index) {
                    card.classList.remove('bg-slate-900/60', 'border-slate-800');
                    card.classList.add('bg-slate-800/80', 'border-orange-500/60');
                    content.classList.remove('hidden');
                    if (chevron) {
                        chevron.classList.remove('text-slate-500');
                        chevron.classList.add('text-orange-400');
                    }
                } else {
                    card.classList.add('bg-slate-900/60', 'border-slate-800');
                    card.classList.remove('bg-slate-800/80', 'border-orange-500/60');
                    content.classList.add('hidden');
                    if (chevron) {
                        chevron.classList.add('text-slate-500');
                        chevron.classList.remove('text-orange-400');
                    }
                }
            });
        }

        // 5. OUR NETWORK COLLAPSIBLE SECTION TOGGLE
        let networkOpen = true;
        function toggleNetworkSection() {
            const content = document.getElementById('networkContent');
            const caret = document.getElementById('networkToggleCaret');
            networkOpen = !networkOpen;

            if (networkOpen) {
                content.style.maxHeight = '2000px';
                content.style.opacity = '1';
                caret.style.transform = 'rotate(0deg)';
            } else {
                content.style.maxHeight = '0px';
                content.style.opacity = '0';
                caret.style.transform = 'rotate(-90deg)';
            }
        }

        // 6. SEARCH MODAL INTERACTION
        function toggleSearchModal(show) {
            const modal = document.getElementById('searchOverlayModal');
            const input = document.getElementById('quickSearchInput');
            if (show) {
                modal.classList.remove('hidden');
                setTimeout(() => input.focus(), 100);
            } else {
                modal.classList.add('hidden');
            }
        }

        // Keyboard ESC to close modal
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                toggleSearchModal(false);
            }
        });

        // Search directory data
        const searchDatabase = [
            { title: 'Yard Management System (YMS)', desc: 'Penataan kontainer blok A-E, 4-tier stacking, dan alat berat Kalmar RS-01..03', link: 'dashboard.php?page=denah' },
            { title: 'Rail Siding Pasoso (TMS)', desc: 'Konektivitas rangkaian 60 TEU kereta api logistik langsung ke Tanjung Priok', link: 'dashboard.php?page=trucking' },
            { title: 'CFS Warehouse (WMS)', desc: 'Gudang konsolidasi LCL/FCL, auto-dock leveller, dan pelacakan barcode palet GS1', link: 'dashboard.php?page=cfs' },
            { title: 'Reefer Cold Chain Terminal', desc: '120 soket plug points telemetri IoT 24/7 untuk kargo dingin bernilai tinggi', link: '#highlights' },
            { title: 'Integrasi Host-to-Host CEISA 4.0', desc: 'Otomasi validasi SPPB, PEB, PIB, dan gate in clearance Bea Cukai', link: '#documents' },
            { title: 'SOP Gate & Panduan Pengemudi', desc: 'Dokumen panduan kartu RFID UHF dan standar keselamatan operasional', link: 'docs/CIDP_DPMS_Comprehensive_Tutorial_Guide.docx' },
            { title: '3D Digital Twin Simulation', desc: 'Simulasi spasial visualisasi interaktif denah terminal Three.js 1:1 ISO scale', link: 'dashboard.php?page=simulator' }
        ];

        function fillSearchKeyword(word) {
            const input = document.getElementById('quickSearchInput');
            input.value = word;
            handleQuickSearch(word);
        }

        function handleQuickSearch(query) {
            const resultsBox = document.getElementById('searchResultsBox');
            const q = query.trim().toLowerCase();

            if (!q) {
                resultsBox.innerHTML = `
                    <div class="text-xs text-slate-500 italic p-3 text-center">
                        Masukkan kata kunci di atas untuk mencari data fasilitas, modul, atau dokumen.
                    </div>
                `;
                return;
            }

            const matched = searchDatabase.filter(item => 
                item.title.toLowerCase().includes(q) || item.desc.toLowerCase().includes(q)
            );

            if (matched.length === 0) {
                resultsBox.innerHTML = `
                    <div class="text-xs text-slate-400 p-4 text-center">
                        Tidak ditemukan hasil yang cocok dengan "<strong>${query}</strong>". Coba kata kunci lain seperti YMS, Reefer, atau Kereta.
                    </div>
                `;
            } else {
                resultsBox.innerHTML = matched.map(item => `
                    <a href="${item.link}" class="block p-3 rounded-xl bg-slate-800/80 hover:bg-orange-600/20 border border-slate-700 hover:border-orange-500/50 transition-colors">
                        <div class="font-bold text-white text-xs flex items-center justify-between">
                            <span>${item.title}</span>
                            <i class="fa-solid fa-arrow-right text-[10px] text-orange-400"></i>
                        </div>
                        <div class="text-[11px] text-slate-300 mt-1">${item.desc}</div>
                    </a>
                `).join('');
            }
        }
    </script>
</body>
</html>
