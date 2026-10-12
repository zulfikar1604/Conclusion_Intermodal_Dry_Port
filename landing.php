<?php
// =============================================================================
// FILE       : landing.php
// PROYEK     : Conclusion Intermodal Dry Port Management System (CIDP DPMS)
// KONSULTAN  : Conclusion Supply Chain Consultant
// TEMA       : Korporat Terang, Segar & Optimis (Bright, Clean, Crisp Corporate)
// IDENTITAS  : Conclusion Orange (#ea580c), Royal Blue (#0170b9), Navy (#002f5e)
// =============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/connection.php';

$error_message = '';
$showLoginModal = isset($_GET['login']) && $_GET['login'] == '1';

// PROSES AUTENTIKASI MASUK (LOGIN)
if (isset($_POST['btn_login'])) {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error_message = 'Alamat email dan kata sandi wajib diisi!';
        $showLoginModal = true;
    } else {
        try {
            $stmt = $pdo->prepare("SELECT iduser, nama, email, password, role FROM login WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['login']  = true;
                $_SESSION['iduser'] = $user['iduser'];
                $_SESSION['nama']   = $user['nama'];
                $_SESSION['email']  = $user['email'];
                $_SESSION['role']   = $user['role'];

                header("Location: dashboard.php");
                exit;
            } else {
                $error_message = 'Email atau kata sandi yang Anda masukkan tidak sesuai!';
                $showLoginModal = true;
            }
        } catch (PDOException $e) {
            $error_message = 'Terjadi kendala pada sistem: ' . $e->getMessage();
            $showLoginModal = true;
        }
    }
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 (Local with CDN fallback) -->
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
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
                            sky: '#0284c7',
                            light: '#f8fafc',
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

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #ea580c;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #c2410c;
        }

        /* Hero Carousel Slider */
        .hero-slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            visibility: hidden;
            transition: opacity 1s cubic-bezier(0.4, 0, 0.2, 1);
            background-size: cover;
            background-position: center;
        }
        .hero-slide.active {
            opacity: 1;
            visibility: visible;
            z-index: 10;
        }

        /* Hero Daylight Contrast Vignette (Cerah, tidak hitam pekat) */
        .hero-daylight-vignette {
            background: linear-gradient(90deg, rgba(15, 23, 42, 0.85) 0%, rgba(15, 23, 42, 0.5) 55%, rgba(15, 23, 42, 0.15) 100%),
                        linear-gradient(180deg, rgba(15, 23, 42, 0.4) 0%, transparent 40%, rgba(15, 23, 42, 0.7) 100%);
        }

        /* Hero Bottom Progress Bar */
        .hero-tab {
            cursor: pointer;
            transition: all 0.25s ease;
        }
        .hero-tab .tab-name {
            color: rgba(255, 255, 255, 0.7);
            transition: color 0.25s ease;
        }
        .hero-tab.active .tab-name {
            color: #ffffff;
            font-weight: 800;
        }
        .hero-tab .track-bar {
            height: 3px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 999px;
            overflow: hidden;
            margin-top: 6px;
        }
        .hero-tab .fill-bar {
            height: 100%;
            width: 0%;
            background: #ea580c;
            border-radius: 999px;
            transition: width 0.1s linear;
        }

        /* Tall Cards (Tridharma DPMS) */
        .pillar-box {
            position: relative;
            height: 480px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 30px -10px rgba(0, 75, 135, 0.15);
            transition: all 0.4s ease;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        .pillar-box .box-img {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            transition: transform 0.7s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .pillar-box:hover .box-img {
            transform: scale(1.06);
        }
        .pillar-box .box-grad {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(0, 47, 94, 0.15) 0%, rgba(0, 75, 135, 0.75) 60%, rgba(0, 47, 94, 0.95) 100%);
            transition: background 0.4s ease;
        }
        .pillar-box:hover .box-grad {
            background: linear-gradient(180deg, rgba(0, 47, 94, 0.35) 0%, rgba(0, 75, 135, 0.9) 45%, rgba(0, 47, 94, 1) 100%);
        }
        .pillar-box .box-base {
            position: absolute;
            bottom: 24px;
            left: 24px;
            right: 24px;
            transition: all 0.35s ease;
        }
        .pillar-box .box-hover {
            position: absolute;
            bottom: 24px;
            left: 24px;
            right: 24px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .pillar-box:hover .box-base {
            opacity: 0;
            transform: translateY(-16px);
        }
        .pillar-box:hover .box-hover {
            opacity: 1;
            transform: translateY(0);
        }

        /* Floating Side Nav */
        .side-nav-float {
            position: fixed;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            z-index: 35;
        }
        .side-nav-float .float-btn {
            width: 42px;
            height: 42px;
            background: #ffffff;
            border-left: 3px solid #ea580c;
            border-radius: 8px 0 0 8px;
            box-shadow: -2px 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #004b87;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }
        .side-nav-float .float-btn:hover {
            background: #ea580c;
            color: #ffffff;
            width: 48px;
        }
        .side-nav-float .popover-social {
            position: absolute;
            right: 44px;
            top: 0;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px 0 0 8px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 12px;
            opacity: 0;
            pointer-events: none;
            transform: translateX(10px);
            transition: all 0.25s ease;
            box-shadow: -4px 4px 16px rgba(0,0,0,0.1);
        }
        .side-nav-float .float-social:hover .popover-social {
            opacity: 1;
            pointer-events: auto;
            transform: translateX(0);
        }

        /* Signature Readmore Link (Petronas-style) */
        .link-arrow-circle {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 700;
            transition: all 0.2s ease;
        }
        .link-arrow-circle i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 999px;
            background: #ea580c;
            color: #ffffff;
            font-size: 10px;
            transition: transform 0.2s ease;
        }
        .link-arrow-circle:hover i {
            transform: translateX(4px);
        }
    </style>
    <!-- Multilingual Engine (Indonesian / English) -->
    <script src="assets/js/landing-i18n.js"></script>
</head>
<body class="bg-white text-slate-800 antialiased selection:bg-orange-500 selection:text-white">

    <!-- =================================================================== -->
    <!-- 1. BILAH NAVIGASI DUA TINGKAT (Clean Bright White Corporate Header) -->
    <!-- TIDAK TERTABRAK: Header solid putih dengan batas tegas              -->
    <!-- =================================================================== -->
    <header id="mainHeader" class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-xs">
        <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- BARIS ATAS: LOGO & UTILITAS PENCARIAN -->
            <div class="flex items-center justify-between py-2 border-b border-slate-100">
                
                <!-- BRAND LOGO -->
                <a href="index.php" class="flex items-center gap-3 group">
                    <img src="assets/img/logo.png" alt="Conclusion Logo" class="h-9 sm:h-10 w-auto group-hover:scale-105 transition-transform">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-black text-lg tracking-tight uppercase text-cdp-navy">CONCLUSION</span>
                            <span class="text-[9px] uppercase font-bold tracking-widest bg-orange-600 text-white px-2 py-0.5 rounded-full shadow-xs">CIDP</span>
                        </div>
                        <div class="text-[10px] font-medium tracking-wide text-slate-500">Intermodal Dry Port &bull; 35 Ha Hub</div>
                    </div>
                </a>

                <!-- LINK UTILITAS KANAN (Desktop) -->
                <div class="hidden lg:flex items-center gap-6 text-xs font-semibold text-slate-600">
                    <a href="#about" class="hover:text-orange-600 transition-colors" data-i18n="nav_about">Tentang Kami</a>
                    <a href="#updates" class="hover:text-orange-600 transition-colors" data-i18n="nav_updates">Berita &amp; Rilis</a>
                    <a href="#performance" class="hover:text-orange-600 transition-colors" data-i18n="nav_perf">Kinerja 35 Ha</a>
                    <a href="#highlights" class="hover:text-orange-600 transition-colors" data-i18n="nav_facilities">Fasilitas &amp; NOC</a>
                    <a href="#network" class="hover:text-orange-600 transition-colors" data-i18n="nav_network">Mitra Ekosistem</a>
                    
                    <span class="text-slate-200">|</span>
                    
                    <!-- Search Trigger -->
                    <button onclick="toggleSearchModal(true)" class="p-1 hover:text-orange-600 text-slate-500 transition-colors" title="Pencarian Cepat">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    
                    <!-- Language Box (Functional ID / EN Switcher) -->
                    <div class="flex items-center gap-1 bg-slate-100 border border-slate-200/60 px-2 py-0.5 rounded text-[11px] font-mono text-slate-700">
                        <button type="button" id="langBtnID" onclick="setLanguage('id')" class="font-bold text-orange-600 transition-colors focus:outline-none">ID</button>
                        <span class="text-slate-300">/</span>
                        <button type="button" id="langBtnEN" onclick="setLanguage('en')" class="text-slate-500 hover:text-slate-900 transition-colors focus:outline-none">EN</button>
                    </div>
                </div>

                <!-- BURGER MENU BUTTON (Mobile) -->
                <button onclick="toggleMobileMenu()" class="lg:hidden p-2 text-slate-700 hover:text-orange-600 focus:outline-none">
                    <i class="fa-solid fa-bars text-xl" id="burgerIcon"></i>
                </button>
            </div>

            <!-- BARIS BAWAH: NAVIGASI UTAMA & TOMBOL MASUK PORTAL -->
            <div class="hidden lg:flex items-center justify-between py-2 sm:py-2.5">
                <nav class="flex items-center gap-8 text-xs font-bold tracking-wide text-slate-700 uppercase">
                    
                    <!-- Mega Menu 1: Ekosistem Intermodal -->
                    <div class="relative group">
                        <button class="flex items-center gap-1.5 py-1 hover:text-orange-600 transition-colors focus:outline-none">
                            <span data-i18n="mega_1_title">Ekosistem Intermodal</span>
                            <i class="fa-solid fa-chevron-down text-[9px] group-hover:rotate-180 transition-transform"></i>
                        </button>
                        <div class="absolute left-0 top-full mt-2 w-72 bg-white border border-slate-200 rounded-2xl p-3 shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform group-hover:translate-y-0 translate-y-2 z-50">
                            <a href="#pillars" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-orange-50/60 transition-colors">
                                <i class="fa-solid fa-cubes-stacked text-orange-600 mt-1"></i>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs normal-case" data-i18n="mega_1_sub1">Yard Management (YMS)</div>
                                    <div class="text-[11px] text-slate-500 normal-case" data-i18n="mega_1_sub1_desc">Blok A-E, 4-tier stacking & RTG</div>
                                </div>
                            </a>
                            <a href="#pillars" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-blue-50/60 transition-colors">
                                <i class="fa-solid fa-train text-cdp-blue mt-1"></i>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs normal-case" data-i18n="mega_1_sub2">Rail Siding Pasoso (TMS)</div>
                                    <div class="text-[11px] text-slate-500 normal-case" data-i18n="mega_1_sub2_desc">60 TEU rangkaian kereta logistik</div>
                                </div>
                            </a>
                            <a href="#pillars" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-emerald-50/60 transition-colors">
                                <i class="fa-solid fa-warehouse text-emerald-600 mt-1"></i>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs normal-case" data-i18n="mega_1_sub3">CFS & Konsolidasi (WMS)</div>
                                    <div class="text-[11px] text-slate-500 normal-case" data-i18n="mega_1_sub3_desc">Cross-docking & barcode palet GS1</div>
                                </div>
                            </a>
                            <a href="#highlights" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-cyan-50/60 transition-colors">
                                <i class="fa-solid fa-snowflake text-cyan-600 mt-1"></i>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs normal-case" data-i18n="mega_1_sub4">Reefer Cold Chain Hub</div>
                                    <div class="text-[11px] text-slate-500 normal-case" data-i18n="mega_1_sub4_desc">120 soket telemetri IoT real-time presisi</div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Mega Menu 2: Green Dry Port -->
                    <div class="relative group">
                        <button class="flex items-center gap-1.5 py-1 hover:text-orange-600 transition-colors focus:outline-none">
                            <span data-i18n="mega_2_title">Keberlanjutan & Green Port</span>
                            <i class="fa-solid fa-chevron-down text-[9px] group-hover:rotate-180 transition-transform"></i>
                        </button>
                        <div class="absolute left-0 top-full mt-2 w-72 bg-white border border-slate-200 rounded-2xl p-3 shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform group-hover:translate-y-0 translate-y-2 z-50">
                            <a href="#performance" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-emerald-50/60 transition-colors">
                                <i class="fa-solid fa-leaf text-emerald-600 mt-1"></i>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs normal-case" data-i18n="mega_2_sub1">Reduksi Emisi -76% CO₂</div>
                                    <div class="text-[11px] text-slate-500 normal-case" data-i18n="mega_2_sub1_desc">Modal shift dari truk ke kereta api</div>
                                </div>
                            </a>
                            <a href="#highlights" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-amber-50/60 transition-colors">
                                <i class="fa-solid fa-bolt text-amber-500 mt-1"></i>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs normal-case" data-i18n="mega_2_sub2">Elektrifikasi Alat Terminal</div>
                                    <div class="text-[11px] text-slate-500 normal-case" data-i18n="mega_2_sub2_desc">E-RTG & efisiensi solar Kalmar</div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Mega Menu 3: Kinerja & Dokumen -->
                    <div class="relative group">
                        <button class="flex items-center gap-1.5 py-1 hover:text-orange-600 transition-colors focus:outline-none">
                            <span data-i18n="mega_3_title">Kinerja & Laporan</span>
                            <i class="fa-solid fa-chevron-down text-[9px] group-hover:rotate-180 transition-transform"></i>
                        </button>
                        <div class="absolute left-0 top-full mt-2 w-72 bg-white border border-slate-200 rounded-2xl p-3 shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform group-hover:translate-y-0 translate-y-2 z-50">
                            <a href="#performance" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-blue-50/60 transition-colors">
                                <i class="fa-solid fa-chart-line text-blue-600 mt-1"></i>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs normal-case" data-i18n="mega_3_sub1">Indikator Dwell Time 1.8 Hari</div>
                                    <div class="text-[11px] text-slate-500 normal-case" data-i18n="mega_3_sub1_desc">Efisiensi operasional 2026</div>
                                </div>
                            </a>
                            <a href="#documents" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-orange-50/60 transition-colors">
                                <i class="fa-solid fa-file-contract text-orange-600 mt-1"></i>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs normal-case" data-i18n="mega_3_sub2">SOP & Regulasi CEISA 4.0</div>
                                    <div class="text-[11px] text-slate-500 normal-case" data-i18n="mega_3_sub2_desc">Dokumen kepabeanan & kepatuhan K3</div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- 3D Digital Twin Link -->
                    <a href="dashboard.php?page=simulator" class="hover:text-orange-600 transition-colors flex items-center gap-1.5 text-cdp-blue">
                        <i class="fa-solid fa-vr-cardboard"></i>
                        <span data-i18n="nav_3d">3D Digital Twin</span>
                    </a>
                </nav>

                <!-- TOMBOL CTA MASUK SISTEM -->
                <div class="flex items-center gap-3">
                    <?php if ($isLoggedIn): ?>
                        <a href="dashboard.php" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-orange-500/25 transition-all transform hover:-translate-y-0.5">
                            <i class="fa-solid fa-gauge-high"></i>
                            <span data-i18n="nav_dashboard">Buka Dashboard (<?= htmlspecialchars($userName) ?>)</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    <?php else: ?>
                        <button type="button" onclick="openLoginModal()" class="inline-flex items-center gap-2.5 px-6 py-2.5 rounded-full bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-orange-500/25 transition-all transform hover:-translate-y-0.5">
                            <i class="fa-solid fa-right-to-bracket"></i>
                            <span data-i18n="nav_cta">Masuk Portal DPMS</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- MENU MOBILE POPUP -->
        <div id="mobileMenu" class="hidden lg:hidden bg-white border-b border-slate-200 px-6 py-5 text-slate-700 shadow-xl">
            <!-- Mobile Language Switcher -->
            <div class="flex items-center justify-between pb-3 mb-2 border-b border-slate-100 text-xs">
                <span class="text-slate-500 font-medium" data-i18n="mobile_lang_label">Pilih Bahasa:</span>
                <div class="flex items-center gap-1 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded font-mono">
                    <button type="button" id="mobileLangBtnID" onclick="setLanguage('id')" class="font-bold text-orange-600 px-1">ID</button>
                    <span class="text-slate-300">/</span>
                    <button type="button" id="mobileLangBtnEN" onclick="setLanguage('en')" class="text-slate-500 hover:text-slate-900 px-1">EN</button>
                </div>
            </div>
            
            <div class="flex flex-col gap-2.5 text-sm font-semibold">
                <a href="#about" onclick="toggleMobileMenu()" class="py-2 hover:text-orange-600 border-b border-slate-100" data-i18n="mobile_link_about">Tentang CIDP 35 Ha</a>
                <a href="#pillars" onclick="toggleMobileMenu()" class="py-2 hover:text-orange-600 border-b border-slate-100" data-i18n="mobile_link_pillars">Pilar YMS &bull; TMS &bull; WMS</a>
                <a href="#performance" onclick="toggleMobileMenu()" class="py-2 hover:text-orange-600 border-b border-slate-100" data-i18n="mobile_link_perf">Kinerja & Reduksi Emisi</a>
                <a href="#highlights" onclick="toggleMobileMenu()" class="py-2 hover:text-orange-600 border-b border-slate-100" data-i18n="mobile_link_facilities">Fasilitas NOC & Gatehouse</a>
                <a href="#network" onclick="toggleMobileMenu()" class="py-2 hover:text-orange-600 border-b border-slate-100" data-i18n="mobile_link_network">Jejak Modul & Kemitraan</a>
                <a href="dashboard.php?page=simulator" class="py-2 text-cdp-blue hover:text-blue-700 border-b border-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-vr-cardboard"></i>
                    <span data-i18n="mobile_link_3d">Simulasi 3D Digital Twin</span>
                </a>
                <div class="pt-3">
                    <?php if ($isLoggedIn): ?>
                        <a href="dashboard.php" class="w-full inline-flex items-center justify-center gap-2 py-3 rounded-xl bg-orange-600 text-white font-bold text-xs uppercase tracking-wider">
                            <i class="fa-solid fa-gauge-high"></i>
                            <span data-i18n="nav_dashboard">Buka Dashboard</span>
                        </a>
                    <?php else: ?>
                        <button type="button" onclick="openLoginModal(); toggleMobileMenu();" class="w-full inline-flex items-center justify-center gap-2 py-3 rounded-xl bg-orange-600 text-white font-bold text-xs uppercase tracking-wider">
                            <i class="fa-solid fa-right-to-bracket"></i>
                            <span data-i18n="nav_cta">Masuk Portal DPMS</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- =================================================================== -->
    <!-- 2. FLOATING RIGHT-SIDE VERTICAL QUICK-NAV (Petronas JsideNavi)       -->
    <!-- =================================================================== -->
    <div class="side-nav-float hidden md:block">
        <a href="#updates" class="float-btn" title="Pembaruan Terkini">
            <i class="fa-solid fa-bullhorn text-sm"></i>
        </a>
        <div onclick="toggleSearchModal(true)" class="float-btn" title="Cari di Situs">
            <i class="fa-solid fa-magnifying-glass text-sm"></i>
        </div>
        <div class="float-btn float-social" title="Bagikan & Media Sosial">
            <i class="fa-solid fa-share-nodes text-sm"></i>
            <div class="popover-social">
                <a href="https://linkedin.com" target="_blank" class="text-slate-600 hover:text-blue-600 transition-colors" title="LinkedIn"><i class="fa-brands fa-linkedin text-lg"></i></a>
                <a href="https://youtube.com" target="_blank" class="text-slate-600 hover:text-red-600 transition-colors" title="YouTube"><i class="fa-brands fa-youtube text-lg"></i></a>
                <a href="https://instagram.com" target="_blank" class="text-slate-600 hover:text-pink-600 transition-colors" title="Instagram"><i class="fa-brands fa-instagram text-lg"></i></a>
                <a href="https://twitter.com" target="_blank" class="text-slate-600 hover:text-sky-500 transition-colors" title="Twitter / X"><i class="fa-brands fa-x-twitter text-lg"></i></a>
            </div>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- 3. HERO BANNER CAROUSEL DENGAN PROGRESS BAR (Petronas Banner Nav)  -->
    <!-- PERBAIKAN TOTAL TUMPANG TINDIH:                                     -->
    <!-- 1. Tabs ditaruh absolute di bottom-0 secara eksplisit              -->
    <!-- 2. Konten diberi jarak pt-36 pb-32 agar aman dari header & tabs    -->
    <!-- =================================================================== -->
    <section class="relative w-full h-[100vh] min-h-[660px] max-h-[860px] overflow-hidden bg-slate-900">
        
        <!-- SLIDE 1: Solusi Logistik Intermodal 35 Ha -->
        <div class="hero-slide active" style="background-image: url('assets/img/facilities/stacking_yard.jpg');" data-index="0">
            <div class="absolute inset-0 hero-daylight-vignette"></div>
            <div class="relative max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 h-full flex flex-col justify-center pt-28 pb-28 text-white">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 bg-orange-600 text-white text-[11px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-4 shadow-sm">
                        <i class="fa-solid fa-network-wired"></i> <span data-i18n="hero_1_badge">Hub Intermodal 35 Hektare</span>
                    </span>
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight text-white mb-4" data-i18n-html="hero_1_title">
                        Solusi Logistik <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 via-amber-300 to-yellow-200">Terintegrasi Intermodal</span>
                    </h1>
                    <p class="text-sm sm:text-base lg:text-lg text-slate-100 font-normal leading-relaxed mb-8 max-w-2xl drop-shadow" data-i18n="hero_1_desc">
                        Menghubungkan simpul pelabuhan laut, jalur ganda kereta api logistik, dan armada angkutan darat dengan kapasitas throughput 45.000 TEU per tahun demi efisiensi rantai pasok nasional.
                    </p>
                    <div class="flex flex-wrap items-center gap-4">
                        <button type="button" onclick="openLoginModal()" class="inline-flex items-center gap-3 px-7 py-3.5 rounded-full bg-orange-600 hover:bg-orange-500 text-white font-bold text-sm tracking-wide shadow-xl shadow-orange-950/40 transition-all transform hover:scale-105">
                            <span data-i18n="hero_1_btn1">Jelajahi Portal DPMS</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                        <a href="#about" class="link-arrow-circle text-white hover:text-orange-400 px-3 py-2">
                            <i><i class="fa-solid fa-arrow-right"></i></i>
                            <span data-i18n="hero_1_btn2">Pelajari Profil Terminal</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- SLIDE 2: Rail Siding Pasoso Connection -->
        <div class="hero-slide" style="background-image: url('assets/img/facilities/rail_siding.jpg');" data-index="1">
            <div class="absolute inset-0 hero-daylight-vignette"></div>
            <div class="relative max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 h-full flex flex-col justify-center pt-28 pb-28 text-white">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 bg-blue-600 text-white text-[11px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-4 shadow-sm">
                        <i class="fa-solid fa-train"></i> <span data-i18n="hero_2_badge">Konektivitas Rel Pasoso</span>
                    </span>
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight text-white mb-4" data-i18n-html="hero_2_title">
                        Akses Kereta Api <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 via-cyan-300 to-blue-200">Langsung ke Tanjung Priok</span>
                    </h1>
                    <p class="text-sm sm:text-base lg:text-lg text-slate-100 font-normal leading-relaxed mb-8 max-w-2xl drop-shadow" data-i18n="hero_2_desc">
                        Rangkaian 60 TEU kereta kargo intermodal memotong dwell time pelabuhan dari 3,8 hari menjadi 1,8 hari tanpa hambatan kemacetan jalan arteri.
                    </p>
                    <div class="flex flex-wrap items-center gap-4">
                        <button type="button" onclick="openLoginModal()" class="inline-flex items-center gap-3 px-7 py-3.5 rounded-full bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm tracking-wide shadow-xl transition-all transform hover:scale-105">
                            <span data-i18n="hero_2_btn1">Buka Modul TMS</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                        <a href="#pillars" class="link-arrow-circle text-white hover:text-sky-300 px-3 py-2">
                            <i><i class="fa-solid fa-arrow-right"></i></i>
                            <span data-i18n="hero_2_btn2">Sistem Shunting Kereta</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- SLIDE 3: CFS Warehouse & Smart Consolidation -->
        <div class="hero-slide" style="background-image: url('assets/img/facilities/cfs_warehouse.jpg');" data-index="2">
            <div class="absolute inset-0 hero-daylight-vignette"></div>
            <div class="relative max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 h-full flex flex-col justify-center pt-28 pb-28 text-white">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 bg-emerald-600 text-white text-[11px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-4 shadow-sm">
                        <i class="fa-solid fa-warehouse"></i> <span data-i18n="hero_3_badge">CFS Warehouse Modern</span>
                    </span>
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight text-white mb-4" data-i18n-html="hero_3_title">
                        Konsolidasi Pintar & <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-green-200">Gudang CFS Berstandar WMS</span>
                    </h1>
                    <p class="text-sm sm:text-base lg:text-lg text-slate-100 font-normal leading-relaxed mb-8 max-w-2xl drop-shadow" data-i18n="hero_3_desc">
                        Stuffing, stripping, dan cross-docking LCL/FCL otomatis dengan pembacaan barcode palet GS1 serta integrasi faktur perpajakan kepabeanan.
                    </p>
                    <div class="flex flex-wrap items-center gap-4">
                        <button type="button" onclick="openLoginModal()" class="inline-flex items-center gap-3 px-7 py-3.5 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm tracking-wide shadow-xl transition-all transform hover:scale-105">
                            <span data-i18n="hero_3_btn1">Buka Modul WMS</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                        <a href="#pillars" class="link-arrow-circle text-white hover:text-emerald-300 px-3 py-2">
                            <i><i class="fa-solid fa-arrow-right"></i></i>
                            <span data-i18n="hero_3_btn2">Pelajari Alur Konsolidasi</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- SLIDE 4: Reefer Terminal & Precision Cold Chain -->
        <div class="hero-slide" style="background-image: url('assets/img/facilities/reefer_terminal.jpg');" data-index="3">
            <div class="absolute inset-0 hero-daylight-vignette"></div>
            <div class="relative max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 h-full flex flex-col justify-center pt-28 pb-28 text-white">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 bg-cyan-600 text-white text-[11px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-4 shadow-sm">
                        <i class="fa-solid fa-snowflake"></i> <span data-i18n="hero_4_badge">Rantai Pendingin Presisi</span>
                    </span>
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight text-white mb-4" data-i18n-html="hero_4_title">
                        Terminal Reefer & <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-blue-300 to-sky-200">Pemantauan Suhu IoT Real-Time</span>
                    </h1>
                    <p class="text-sm sm:text-base lg:text-lg text-slate-100 font-normal leading-relaxed mb-8 max-w-2xl drop-shadow" data-i18n="hero_4_desc">
                        120 soket plug points mandiri dengan sistem deteksi deviasi suhu real-time untuk kargo farmasi, daging, susu, dan buah bernilai tinggi.
                    </p>
                    <div class="flex flex-wrap items-center gap-4">
                        <button type="button" onclick="openLoginModal()" class="inline-flex items-center gap-3 px-7 py-3.5 rounded-full bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-sm tracking-wide shadow-xl transition-all transform hover:scale-105">
                            <span data-i18n="hero_4_btn1">Monitoring Reefer</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                        <a href="#highlights" class="link-arrow-circle text-white hover:text-cyan-300 px-3 py-2">
                            <i><i class="fa-solid fa-arrow-right"></i></i>
                            <span data-i18n="hero_4_btn2">Spesifikasi Terminal</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- SLIDE 5: Customs CEISA 4.0 & Smart Gate -->
        <div class="hero-slide" style="background-image: url('assets/img/facilities/gatehouse_in.jpg');" data-index="4">
            <div class="absolute inset-0 hero-daylight-vignette"></div>
            <div class="relative max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 h-full flex flex-col justify-center pt-28 pb-28 text-white">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 bg-amber-600 text-white text-[11px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-4 shadow-sm">
                        <i class="fa-solid fa-shield-halved"></i> <span data-i18n="hero_5_badge">Otomasi Gerbang & CEISA 4.0</span>
                    </span>
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight text-white mb-4" data-i18n-html="hero_5_title">
                        Smart Gate Otomatis & <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-orange-300 to-yellow-200">Clearance Bea Cukai Instan</span>
                    </h1>
                    <p class="text-sm sm:text-base lg:text-lg text-slate-100 font-normal leading-relaxed mb-8 max-w-2xl drop-shadow" data-i18n="hero_5_desc">
                        Pembacaan RFID UHF ISO 18000-6C, kamera OCR pelat nomor, dan timbangan gantry 80 ton otomatis yang tersinkronisasi langsung dengan SPPB DJBC.
                    </p>
                    <div class="flex flex-wrap items-center gap-4">
                        <button type="button" onclick="openLoginModal()" class="inline-flex items-center gap-3 px-7 py-3.5 rounded-full bg-amber-600 hover:bg-amber-500 text-white font-bold text-sm tracking-wide shadow-xl transition-all transform hover:scale-105">
                            <span data-i18n="hero_5_btn1">Buka Modul Gate</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                        <a href="#documents" class="link-arrow-circle text-white hover:text-amber-300 px-3 py-2">
                            <i><i class="fa-solid fa-arrow-right"></i></i>
                            <span data-i18n="hero_5_btn2">Standar Keselamatan Gate</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- PROGRESS BAR BOTTOM TABS (TERLETAK PASTI DI BOTTOM: 0, TIDAK BISA TERTABRAK HEADER!) -->
        <div class="absolute bottom-0 left-0 right-0 z-30 pt-6 pb-5 bg-gradient-to-t from-slate-950 via-slate-950/70 to-transparent">
            <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 sm:gap-4 border-t border-white/20 pt-3">
                    
                    <div class="hero-tab active" onclick="switchHeroSlide(0)">
                        <div class="text-[11px] sm:text-xs tracking-wider uppercase tab-name truncate font-semibold" data-i18n="tab_0">Solusi Intermodal</div>
                        <div class="track-bar">
                            <div class="fill-bar" id="progressBar0"></div>
                        </div>
                    </div>

                    <div class="hero-tab" onclick="switchHeroSlide(1)">
                        <div class="text-[11px] sm:text-xs tracking-wider uppercase tab-name truncate font-semibold" data-i18n="tab_1">Rail Siding Pasoso</div>
                        <div class="track-bar">
                            <div class="fill-bar" id="progressBar1"></div>
                        </div>
                    </div>

                    <div class="hero-tab" onclick="switchHeroSlide(2)">
                        <div class="text-[11px] sm:text-xs tracking-wider uppercase tab-name truncate font-semibold" data-i18n="tab_2">CFS & Konsolidasi</div>
                        <div class="track-bar">
                            <div class="fill-bar" id="progressBar2"></div>
                        </div>
                    </div>

                    <div class="hero-tab" onclick="switchHeroSlide(3)">
                        <div class="text-[11px] sm:text-xs tracking-wider uppercase tab-name truncate font-semibold" data-i18n="tab_3">Reefer Cold Chain</div>
                        <div class="track-bar">
                            <div class="fill-bar" id="progressBar3"></div>
                        </div>
                    </div>

                    <div class="hero-tab col-span-2 sm:col-span-1" onclick="switchHeroSlide(4)">
                        <div class="text-[11px] sm:text-xs tracking-wider uppercase tab-name truncate font-semibold" data-i18n="tab_4">Smart Gate CEISA 4.0</div>
                        <div class="track-bar">
                            <div class="fill-bar" id="progressBar4"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 4. LATEST UPDATES (Petronas Media Releases / News Section - CERAH)  -->
    <!-- =================================================================== -->
    <section id="updates" class="py-14 sm:py-16 bg-white text-slate-800 relative">
        <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
                <div>
                    <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-slate-900" data-i18n-html="upd_title">
                        <span class="text-orange-600 font-black">Pembaruan</span> Terkini &amp; Rilis Operasional
                    </h2>
                    <p class="text-slate-500 text-xs sm:text-sm mt-1.5 max-w-xl" data-i18n="upd_desc">
                        Pengumuman kepabeanan, jadwal shunting kereta api, dan pembaruan telemetri operasional.
                    </p>
                </div>
                <a href="#documents" class="link-arrow-circle text-orange-600 hover:text-orange-700">
                    <i><i class="fa-solid fa-arrow-right"></i></i>
                    <span data-i18n="upd_all">Lihat Semua Dokumen &amp; Rilis</span>
                </a>
            </div>

            <!-- News Grid: 1 Large Featured Card + 3 Side Cards (Petronas Layout) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
                
                <!-- FEATURED CARD (Col 5) -->
                <div class="lg:col-span-5">
                    <div onclick="openLoginModal()" class="group relative block h-full min-h-[440px] rounded-3xl overflow-hidden shadow-md hover:shadow-xl border border-slate-200 cursor-pointer transition-all">
                        <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-105" style="background-image: url('assets/img/facilities/customs_office.jpg');"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/50 to-transparent"></div>
                        
                        <!-- Badge Featured -->
                        <div class="absolute top-6 left-6">
                            <span class="bg-orange-600 text-white font-bold text-[11px] uppercase tracking-wider px-3.5 py-1.5 rounded-full shadow-md" data-i18n="upd_f_badge">
                                Rilis Utama
                            </span>
                        </div>

                        <!-- Content Bottom -->
                        <div class="absolute bottom-6 left-6 right-6 text-white">
                            <div class="flex items-center justify-between text-xs font-mono text-orange-400 mb-2">
                                <span data-i18n="upd_f_cat">KEPABEANAN & CEISA</span>
                                <span>12 Oktober 2026</span>
                            </div>
                            <h3 class="text-xl sm:text-2xl font-bold leading-snug group-hover:text-orange-300 transition-colors" data-i18n="upd_f_title">
                                CIDP Resmikan Integrasi API Host-to-Host CEISA 4.0 Bea Cukai untuk Otomasi SPPB Real-Time
                            </h3>
                            <div class="mt-4 flex items-center gap-2 text-xs font-semibold text-slate-200 group-hover:text-white transition-colors">
                                <span data-i18n="upd_f_btn">Buka Akses Konsol Sistem</span>
                                <i class="fa-solid fa-arrow-right text-[10px] transform group-hover:translate-x-1 transition-transform"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT CARDS (Col 7 - 3 Cards Horizontal / Stack) -->
                <div class="lg:col-span-7 flex flex-col justify-between gap-5">
                    
                    <!-- Card 1 -->
                    <div onclick="openLoginModal()" class="group flex flex-col sm:flex-row gap-5 p-5 rounded-2xl bg-slate-50 border border-slate-200 hover:border-orange-500 hover:bg-orange-50/20 hover:shadow-md transition-all cursor-pointer">
                        <div class="w-full sm:w-44 h-32 rounded-xl overflow-hidden flex-shrink-0 bg-cover bg-center shadow-xs" style="background-image: url('assets/img/facilities/workshop_mr.jpg');"></div>
                        <div class="flex flex-col justify-center">
                            <div class="flex items-center justify-between text-xs font-mono text-slate-500 mb-1.5">
                                <span class="text-blue-600 font-bold" data-i18n="upd_c1_cat">MAINTENANCE & REPAIR</span>
                                <span>8 Oktober 2026</span>
                            </div>
                            <h4 class="text-base font-bold text-slate-900 group-hover:text-orange-600 transition-colors leading-snug" data-i18n="upd_c1_title">
                                Workshop M&R Rampungkan Kalibrasi Berkala 3 Unit Reach Stacker Kalmar DRG450
                            </h4>
                            <p class="text-xs text-slate-500 mt-2 line-clamp-2 leading-relaxed" data-i18n="upd_c1_desc">
                                Uji beban dinamis dan pemeriksaan sertifikasi twistlock memastikan kesiapan alat berat dalam melayani penumpukan 4-tier berstandar keselamatan internasional.
                            </p>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div onclick="openLoginModal()" class="group flex flex-col sm:flex-row gap-5 p-5 rounded-2xl bg-slate-50 border border-slate-200 hover:border-orange-500 hover:bg-orange-50/20 hover:shadow-md transition-all cursor-pointer">
                        <div class="w-full sm:w-44 h-32 rounded-xl overflow-hidden flex-shrink-0 bg-cover bg-center shadow-xs" style="background-image: url('assets/img/facilities/datacenter_noc.jpg');"></div>
                        <div class="flex flex-col justify-center">
                            <div class="flex items-center justify-between text-xs font-mono text-slate-500 mb-1.5">
                                <span class="text-purple-600 font-bold" data-i18n="upd_c2_cat">INFRASTRUKTUR NOC</span>
                                <span>1 Oktober 2026</span>
                            </div>
                            <h4 class="text-base font-bold text-slate-900 group-hover:text-orange-600 transition-colors leading-snug" data-i18n="upd_c2_title">
                                Peningkatan Data Center Tier-3 CIDP Mencapai Uptime 99.98% untuk Sistem Billing & IoT
                            </h4>
                            <p class="text-xs text-slate-500 mt-2 line-clamp-2 leading-relaxed" data-i18n="upd_c2_desc">
                                Pemantauan telemetri terpadu 26 titik hardware IoT dan sistem redundansi cloud multi-zona menjamin kelancaran operasional gerbang dan penumpukan tanpa henti.
                            </p>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div onclick="openLoginModal()" class="group flex flex-col sm:flex-row gap-5 p-5 rounded-2xl bg-slate-50 border border-slate-200 hover:border-orange-500 hover:bg-orange-50/20 hover:shadow-md transition-all cursor-pointer">
                        <div class="w-full sm:w-44 h-32 rounded-xl overflow-hidden flex-shrink-0 bg-cover bg-center shadow-xs" style="background-image: url('assets/img/facilities/kantor_utama.jpg');"></div>
                        <div class="flex flex-col justify-center">
                            <div class="flex items-center justify-between text-xs font-mono text-slate-500 mb-1.5">
                                <span class="text-emerald-600 font-bold" data-i18n="upd_c3_cat">KEMITRAAN EKOSISTEM</span>
                                <span>24 September 2026</span>
                            </div>
                            <h4 class="text-base font-bold text-slate-900 group-hover:text-orange-600 transition-colors leading-snug" data-i18n="upd_c3_title">
                                Pembukaan Loket Satu Atap Bea Cukai, Bank Mandiri, dan Kantor Perwakilan ALFI
                            </h4>
                            <p class="text-xs text-slate-500 mt-2 line-clamp-2 leading-relaxed" data-i18n="upd_c3_desc">
                                Percepat proses pengurusan dokumen ekspor-impor dan pembayaran nota tagihan jasa pelabuhan kering hanya dalam hitungan menit di Gedung Utama.
                            </p>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 5. OUR INTERMODAL JOURNEY (Petronas 3-Card Transition Journey)       -->
    <!-- =================================================================== -->
    <section id="pillars" class="py-14 sm:py-16 bg-slate-50 border-y border-slate-200">
        <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 sm:mb-10 gap-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-orange-600">TRIDHARMA LOGISTIK CIDP</span>
                    <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-slate-900 mt-1.5">
                        Pilar <span class="text-orange-600">Ekosistem Digital</span> Terpadu
                    </h2>
                    <p class="text-slate-600 text-xs sm:text-sm mt-1.5 max-w-xl">
                        Tiga sistem inti untuk melacak, menggerakkan, dan mengonsolidasi muatan kargo antarmoda secara presisi.
                    </p>
                </div>
                <button type="button" onclick="openLoginModal()" class="link-arrow-circle text-orange-600 hover:text-orange-700">
                    <i><i class="fa-solid fa-arrow-right"></i></i>
                    <span>Buka Seluruh Modul Terpadu</span>
                </button>
            </div>

            <!-- 3 Tall Rounded Image Cards with Slide-Up Hover Overlays -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- CARD 1: Yard Management System (YMS) -->
                <div class="pillar-box group">
                    <div class="box-img" style="background-image: url('assets/img/facilities/stacking_yard.jpg');"></div>
                    <div class="box-grad"></div>
                    
                    <!-- Default State -->
                    <div class="box-base text-white">
                        <div class="text-xs font-mono text-amber-300 uppercase tracking-widest mb-1">01 &bull; MANAJEMEN LAPANGAN</div>
                        <h3 class="text-2xl font-black">Yard Management System</h3>
                        <div class="mt-3 flex items-center gap-2 text-xs text-slate-200">
                            <span>Arahkan kursor untuk rincian</span>
                            <i class="fa-solid fa-arrow-up text-[10px]"></i>
                        </div>
                    </div>

                    <!-- Hover State -->
                    <div class="box-hover text-white">
                        <div class="text-xs font-mono text-amber-300 uppercase tracking-widest mb-1">01 &bull; YMS ENGINE</div>
                        <h4 class="text-xl font-black mb-2">Presisi Spasial & Zero Misplacement</h4>
                        <p class="text-xs text-slate-200 leading-relaxed mb-4">
                            Otomasi penataan Blok A hingga E dengan batasan 4-tier stacking, kalkulasi dwell time otomatis, dan pelacakan GPS alat berat Kalmar Reach Stacker dalam visualisasi denah interaktif.
                        </p>
                        <div class="pt-3 border-t border-white/20 flex items-center justify-between">
                            <span class="text-[11px] font-mono text-emerald-300"><i class="fa-solid fa-check mr-1"></i> 100% Akurasi Slot</span>
                            <button type="button" onclick="openLoginModal()" class="text-xs font-bold text-amber-300 hover:text-white flex items-center gap-1.5">
                                <span>Buka Modul</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: Transport Management System (TMS) -->
                <div class="pillar-box group">
                    <div class="box-img" style="background-image: url('assets/img/facilities/rail_siding.jpg');"></div>
                    <div class="box-grad"></div>
                    
                    <!-- Default State -->
                    <div class="box-base text-white">
                        <div class="text-xs font-mono text-cyan-300 uppercase tracking-widest mb-1">02 &bull; ANTARMODA KERETA & TRUK</div>
                        <h3 class="text-2xl font-black">Transport Management System</h3>
                        <div class="mt-3 flex items-center gap-2 text-xs text-slate-200">
                            <span>Arahkan kursor untuk rincian</span>
                            <i class="fa-solid fa-arrow-up text-[10px]"></i>
                        </div>
                    </div>

                    <!-- Hover State -->
                    <div class="box-hover text-white">
                        <div class="text-xs font-mono text-cyan-300 uppercase tracking-widest mb-1">02 &bull; TMS ENGINE</div>
                        <h4 class="text-xl font-black mb-2">Sinkronisasi Shunting Pasoso</h4>
                        <p class="text-xs text-slate-200 leading-relaxed mb-4">
                            Manajemen jadwal rangkaian 60 TEU kereta api logistik rute Tanjung Priok &ndash; CIDP, serta pengaturan internal shunting Head Truck KTT-01/02/03 demi kelancaran gate dispatch.
                        </p>
                        <div class="pt-3 border-t border-white/20 flex items-center justify-between">
                            <span class="text-[11px] font-mono text-cyan-300"><i class="fa-solid fa-train mr-1"></i> Rangkaian 60 TEU</span>
                            <button type="button" onclick="openLoginModal()" class="text-xs font-bold text-cyan-300 hover:text-white flex items-center gap-1.5">
                                <span>Buka Modul</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- CARD 3: Warehouse Management System (WMS & CFS) -->
                <div class="pillar-box group">
                    <div class="box-img" style="background-image: url('assets/img/facilities/cfs_warehouse.jpg');"></div>
                    <div class="box-grad"></div>
                    
                    <!-- Default State -->
                    <div class="box-base text-white">
                        <div class="text-xs font-mono text-emerald-300 uppercase tracking-widest mb-1">03 &bull; CFS & GUDANG KONSOLIDASI</div>
                        <h3 class="text-2xl font-black">Warehouse Management System</h3>
                        <div class="mt-3 flex items-center gap-2 text-xs text-slate-200">
                            <span>Arahkan kursor untuk rincian</span>
                            <i class="fa-solid fa-arrow-up text-[10px]"></i>
                        </div>
                    </div>

                    <!-- Hover State -->
                    <div class="box-hover text-white">
                        <div class="text-xs font-mono text-emerald-300 uppercase tracking-widest mb-1">03 &bull; WMS & CFS ENGINE</div>
                        <h4 class="text-xl font-black mb-2">Stripping, Stuffing & Barcoding</h4>
                        <p class="text-xs text-slate-200 leading-relaxed mb-4">
                            Pengelolaan alur konsolidasi LCL/FCL, pelacakan barcode palet berstandar GS1, penataan racking gudang CFS, dan koneksi langsung dengan penagihan biaya storage ERP.
                        </p>
                        <div class="pt-3 border-t border-white/20 flex items-center justify-between">
                            <span class="text-[11px] font-mono text-emerald-300"><i class="fa-solid fa-barcode mr-1"></i> Barcode GS1 Ready</span>
                            <button type="button" onclick="openLoginModal()" class="text-xs font-bold text-emerald-300 hover:text-white flex items-center gap-1.5">
                                <span>Buka Modul</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 6. OPERATIONAL & FINANCIAL RESULTS (Bright Blue Corporate Section)  -->
    <!-- =================================================================== -->
    <section id="performance" class="relative py-14 sm:py-16 bg-gradient-to-br from-[#004b87] via-[#0170b9] to-[#002f5e] text-white overflow-hidden">
        <!-- Subtle Pattern Overlay -->
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:20px_20px]"></div>

        <div class="relative z-10 max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Heading -->
            <div class="max-w-2xl mb-8">
                <span class="text-xs font-mono text-amber-300 uppercase tracking-widest font-bold">KINERJA OPERASIONAL &amp; HSSE</span>
                <h2 class="text-2xl sm:text-4xl font-black text-white mt-1.5 leading-tight">
                    Hasil Kinerja Terminal <br><span class="text-amber-300">Tahun Berjalan 2026</span>
                </h2>
                <p class="text-blue-100 text-xs sm:text-sm mt-2 leading-relaxed font-normal">
                    Efisiensi teruji melalui integrasi digital penuh dan koridor kereta kargo antarmoda terpadu.
                </p>
            </div>

            <!-- 3 Rounded Glass Stat Cards (Frosted Glass Cerah & Elegan) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8 sm:mb-10">
                
                <div class="rounded-2xl p-6 bg-white/15 backdrop-blur-md border border-white/25 shadow-xl">
                    <div class="text-xs font-mono text-blue-100 uppercase tracking-wider font-semibold">Kapasitas Throughput</div>
                    <div class="text-3xl sm:text-4xl font-extrabold text-white my-3 font-mono">45.000 <span class="text-xl text-amber-300">TEU</span></div>
                    <div class="text-xs text-blue-100 flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-trend-up text-amber-300"></i>
                        <span>Meningkat 28% dibandingkan tahun lalu</span>
                    </div>
                </div>

                <div class="rounded-2xl p-6 bg-white/15 backdrop-blur-md border border-white/25 shadow-xl">
                    <div class="text-xs font-mono text-blue-100 uppercase tracking-wider font-semibold">Rata-Rata Dwell Time</div>
                    <div class="text-3xl sm:text-4xl font-extrabold text-white my-3 font-mono">1.8 <span class="text-xl text-cyan-300">Hari</span></div>
                    <div class="text-xs text-blue-100 flex items-center gap-1.5">
                        <i class="fa-solid fa-clock text-cyan-300"></i>
                        <span>Di bawah rata-rata konvensional 3.8 hari</span>
                    </div>
                </div>

                <div class="rounded-2xl p-6 bg-white/15 backdrop-blur-md border border-white/25 shadow-xl">
                    <div class="text-xs font-mono text-blue-100 uppercase tracking-wider font-semibold">Reduksi Emisi Gas Buang (CO₂)</div>
                    <div class="text-3xl sm:text-4xl font-extrabold text-white my-3 font-mono">-76% <span class="text-xl text-emerald-300">CO₂</span></div>
                    <div class="text-xs text-blue-100 flex items-center gap-1.5">
                        <i class="fa-solid fa-leaf text-emerald-300"></i>
                        <span>Pengalihan ke moda kereta logistik hijau</span>
                    </div>
                </div>

            </div>

            <!-- KEY DOCUMENTS DOWNLOAD ROWS -->
            <div id="documents" class="pt-8 border-t border-white/20">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-file-pdf text-amber-300"></i>
                        <span>Dokumen Regulasi, SOP & Laporan Resmi</span>
                    </h3>
                    <span class="text-xs font-mono text-blue-200">Format: Dokumen Resmi Terverifikasi</span>
                </div>

                <div class="space-y-3">
                    
                    <!-- Document Row 1 -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-2xl bg-white/10 border border-white/20 hover:bg-white/15 transition-colors gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-orange-500 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                SOP
                            </div>
                            <div>
                                <div class="text-sm font-bold text-white">Standar Operasional Prosedur (SOP) Gate In/Out & Pemeriksaan CEISA 4.0</div>
                                <div class="text-[11px] text-blue-100">Pedoman resmi pengemudi truk, kartu RFID, dan validasi SPPB Bea Cukai</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 text-xs font-mono text-blue-100">
                            <span>28 Feb 2026</span>
                            <a href="docs/CIDP_DPMS_Comprehensive_Tutorial_Guide.docx" download class="px-4 py-2 rounded-xl bg-white text-cdp-navy hover:bg-orange-500 hover:text-white font-bold transition-all shadow-xs flex items-center gap-2">
                                <i class="fa-solid fa-download text-xs"></i>
                                <span>Unduh</span>
                            </a>
                        </div>
                    </div>

                    <!-- Document Row 2 -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-2xl bg-white/10 border border-white/20 hover:bg-white/15 transition-colors gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-cyan-500 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                LPR
                            </div>
                            <div>
                                <div class="text-sm font-bold text-white">Laporan Kinerja Integrasi Antarmoda Dry Port FY2025/2026</div>
                                <div class="text-[11px] text-blue-100">Analisis statistik dwell time, penghematan bahan bakar, dan throughput kontainer</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 text-xs font-mono text-blue-100">
                            <span>15 Jan 2026</span>
                            <a href="docs/CIDP_DPMS_Comprehensive_Tutorial_Guide.docx" download class="px-4 py-2 rounded-xl bg-white text-cdp-navy hover:bg-cyan-500 hover:text-white font-bold transition-all shadow-xs flex items-center gap-2">
                                <i class="fa-solid fa-download text-xs"></i>
                                <span>Unduh</span>
                            </a>
                        </div>
                    </div>

                    <!-- Document Row 3 -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-2xl bg-white/10 border border-white/20 hover:bg-white/15 transition-colors gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                K3
                            </div>
                            <div>
                                <div class="text-sm font-bold text-white">Manual Kepatuhan HSSE & Tanggap Darurat Dangerous Goods (DG Yard)</div>
                                <div class="text-[11px] text-blue-100">Protokol keselamatan kerja zona penumpukan muatan B3 berstandar IMO</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 text-xs font-mono text-blue-100">
                            <span>10 Jan 2026</span>
                            <a href="docs/CIDP_DPMS_Comprehensive_Tutorial_Guide.docx" download class="px-4 py-2 rounded-xl bg-white text-cdp-navy hover:bg-emerald-500 hover:text-white font-bold transition-all shadow-xs flex items-center gap-2">
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
    <!-- 7. HIGHLIGHTS (Petronas Accordion + Dynamic Image Swap - CERAH)      -->
    <!-- =================================================================== -->
    <section id="highlights" class="py-14 sm:py-16 bg-white text-slate-800 relative">
        <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header -->
            <div class="mb-8">
                <span class="text-xs font-mono text-orange-600 uppercase tracking-widest font-bold">INFRASTRUKTUR TERMINAL 35 HA</span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 mt-1">
                    Sorotan <span class="text-orange-600">Fasilitas Unggulan</span>
                </h2>
                <p class="text-slate-500 text-xs sm:text-sm mt-1 max-w-xl">
                    Pilih fasilitas untuk memeriksa spesifikasi teknis dan visualisasi operasional.
                </p>
            </div>

            <!-- 2-Column: Left Dynamic Image Stage + Right Compact Accordion -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- LEFT IMAGE STAGE (Desktop) -->
                <div class="lg:col-span-6 hidden lg:block">
                    <div class="relative w-full h-[450px] rounded-2xl overflow-hidden shadow-lg border border-slate-200">
                        <div id="highlightImageDisplay" class="absolute inset-0 bg-cover bg-center transition-all duration-300" style="background-image: url('assets/img/facilities/datacenter_noc.jpg');"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>
                        <div class="absolute bottom-6 left-6 right-6 text-white">
                            <span id="highlightBadge" class="bg-orange-600 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full shadow-xs">
                                PUSAT KENDALI NOC
                            </span>
                            <h4 id="highlightCaption" class="text-lg sm:text-xl font-bold mt-2">
                                Data Center Tier-3 & Network Operations Center (NOC)
                            </h4>
                            <div id="highlightSpecs" class="mt-2.5 flex flex-wrap gap-2 text-[10px] text-slate-200">
                                <span class="bg-white/15 px-2.5 py-0.5 rounded backdrop-blur-xs font-mono">Uptime 99.98%</span>
                                <span class="bg-white/15 px-2.5 py-0.5 rounded backdrop-blur-xs font-mono">26 Sensor IoT</span>
                                <span class="bg-white/15 px-2.5 py-0.5 rounded backdrop-blur-xs font-mono">Redundansi Cloud</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT ACCORDION LIST -->
                <div class="lg:col-span-6 space-y-3">
                    
                    <!-- Accordion Item 1 (Active by default) -->
                    <div class="accordion-item-box p-4 rounded-2xl bg-orange-50/50 border border-orange-500 cursor-pointer transition-all shadow-xs" onclick="selectHighlight(0, 'assets/img/facilities/datacenter_noc.jpg', 'PUSAT KENDALI NOC', 'Data Center Tier-3 & Network Operations Center (NOC)', ['Uptime 99.98%', '26 Sensor IoT', 'Redundansi Cloud'])">
                        <!-- Mobile Thumbnail -->
                        <div class="lg:hidden w-full h-40 rounded-xl bg-cover bg-center mb-3 shadow-xs" style="background-image: url('assets/img/facilities/datacenter_noc.jpg');"></div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-orange-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                    01
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-900 text-sm sm:text-base">Data Center Tier-3 & NOC CIDP</h3>
                                    <div class="text-[11px] text-slate-500">Pusat Komando Jaringan & Telemetri</div>
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-right text-orange-600 text-xs"></i>
                        </div>
                        <div class="accordion-desc mt-2.5 text-xs text-slate-600 leading-relaxed pl-11">
                            <p class="mb-2">Pusat kontrol 26 perangkat hardware IoT, server redundansi DPMS, dan pengawasan CCTV AI perimeter berkesinambungan.</p>
                            <div class="flex flex-wrap gap-1.5 text-[10px]">
                                <span class="px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-700 font-mono">Tier-3 Uptime 99.98%</span>
                                <span class="px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-700 font-mono">26 Hardware IoT</span>
                                <span class="px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-700 font-mono">AI Perimeter CCTV</span>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Item 2 -->
                    <div class="accordion-item-box p-4 rounded-2xl bg-slate-50 border border-slate-200 cursor-pointer hover:border-slate-300 transition-all" onclick="selectHighlight(1, 'assets/img/facilities/workshop_mr.jpg', 'BENGKEL ALAT & KONTAINER', 'Workshop Maintenance & Repair (M&R) Kalmar', ['Standar IICL', 'Kalmar DRG450', 'Uji Segel ISO'])">
                        <!-- Mobile Thumbnail -->
                        <div class="lg:hidden w-full h-40 rounded-xl bg-cover bg-center mb-3 shadow-xs" style="background-image: url('assets/img/facilities/workshop_mr.jpg');"></div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0">
                                    02
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-900 text-sm sm:text-base">Workshop Maintenance & Repair (M&R)</h3>
                                    <div class="text-[11px] text-slate-500">Bengkel Kalmar & Rekondisi Kontainer</div>
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-right text-slate-400 text-xs"></i>
                        </div>
                        <div class="accordion-desc mt-2.5 text-xs text-slate-600 leading-relaxed pl-11 hidden">
                            <p class="mb-2">Pemeliharaan alat berat Kalmar Reach Stacker, pengelasan struktur kontainer, dan pencucian kimia standar IICL.</p>
                            <div class="flex flex-wrap gap-1.5 text-[10px]">
                                <span class="px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-700 font-mono">Standar IICL</span>
                                <span class="px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-700 font-mono">Kalmar DRG450 Care</span>
                                <span class="px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-700 font-mono">Uji Segel ISO</span>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Item 3 -->
                    <div class="accordion-item-box p-4 rounded-2xl bg-slate-50 border border-slate-200 cursor-pointer hover:border-slate-300 transition-all" onclick="selectHighlight(2, 'assets/img/facilities/gatehouse_in.jpg', 'GERBANG OTOMATIS & TIMBANGAN', 'Gatehouse Otomatis 4 Lajur OCR & Timbangan Gantry', ['4 Lajur Ganda', 'OCR & UHF RFID', 'Timbangan 80 Ton'])">
                        <!-- Mobile Thumbnail -->
                        <div class="lg:hidden w-full h-40 rounded-xl bg-cover bg-center mb-3 shadow-xs" style="background-image: url('assets/img/facilities/gatehouse_in.jpg');"></div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0">
                                    03
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-900 text-sm sm:text-base">Gatehouse Otomatis 4 Lajur & Timbangan</h3>
                                    <div class="text-[11px] text-slate-500">Otomasi Gerbang & Verifikasi Muatan</div>
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-right text-slate-400 text-xs"></i>
                        </div>
                        <div class="accordion-desc mt-2.5 text-xs text-slate-600 leading-relaxed pl-11 hidden">
                            <p class="mb-2">Empat lajur otomatis dengan kamera OCR nomor kontainer & pelat truk, scanner RFID UHF, dan timbangan 80 ton.</p>
                            <div class="flex flex-wrap gap-1.5 text-[10px]">
                                <span class="px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-700 font-mono">4 Lajur Cerdas</span>
                                <span class="px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-700 font-mono">OCR & RFID UHF</span>
                                <span class="px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-700 font-mono">Jembatan Timbang 80 Ton</span>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Item 4 -->
                    <div class="accordion-item-box p-4 rounded-2xl bg-slate-50 border border-slate-200 cursor-pointer hover:border-slate-300 transition-all" onclick="selectHighlight(3, 'assets/img/facilities/kantor_utama.jpg', 'LAYANAN SATU ATAP', 'Gedung Administrasi & Hanggar Bea Cukai CIDP', ['Hanggar Bea Cukai', 'One Stop Service', 'Host CEISA 4.0'])">
                        <!-- Mobile Thumbnail -->
                        <div class="lg:hidden w-full h-40 rounded-xl bg-cover bg-center mb-3 shadow-xs" style="background-image: url('assets/img/facilities/kantor_utama.jpg');"></div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0">
                                    04
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-900 text-sm sm:text-base">Gedung Kantor Utama & Bea Cukai</h3>
                                    <div class="text-[11px] text-slate-500">Pusat Pelayanan Terpadu & Perbankan</div>
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-right text-slate-400 text-xs"></i>
                        </div>
                        <div class="accordion-desc mt-2.5 text-xs text-slate-600 leading-relaxed pl-11 hidden">
                            <p class="mb-2">Pelayanan satu atap (One Stop Service) terpadu: perwakilan Hanggar Bea Cukai, perbankan, dan loket verifikasi dokumen.</p>
                            <div class="flex flex-wrap gap-1.5 text-[10px]">
                                <span class="px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-700 font-mono">Hanggar Bea Cukai</span>
                                <span class="px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-700 font-mono">One Stop Service</span>
                                <span class="px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-700 font-mono">Validasi CEISA 4.0</span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 8. OUR NETWORK (Petronas Collapsible Discover More Section - CERAH) -->
    <!-- =================================================================== -->
    <section id="network" class="py-14 sm:py-16 bg-slate-50 text-slate-800 border-t border-slate-200">
        <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Collapsible Section Header (Petronas discover-more header) -->
            <div class="flex items-center justify-between mb-6 cursor-pointer" onclick="toggleNetworkSection()">
                <div>
                    <span class="text-xs font-mono text-orange-600 uppercase tracking-widest font-bold">JEJAK JARINGAN KAMI</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">
                        Modul Sistem DPMS &amp; Jaringan Mitra
                    </h2>
                </div>
                <button class="w-10 h-10 rounded-full bg-white border border-slate-200 hover:bg-orange-600 hover:text-white text-slate-700 flex items-center justify-center transition-all shadow-xs" title="Buka/Tutup Jaringan">
                    <i class="fa-solid fa-chevron-down transition-transform duration-300" id="networkToggleCaret"></i>
                </button>
            </div>

            <!-- Collapsible Body Content -->
            <div id="networkContent" class="transition-all duration-500 overflow-hidden">
                
                <!-- 8 Core Modules of DPMS (Clean Bright Cards) -->
                <div class="mb-8">
                    <div class="text-xs font-mono text-slate-500 uppercase tracking-wider mb-5 flex items-center gap-2 font-bold">
                        <i class="fa-solid fa-layer-group text-orange-600"></i>
                        <span>8 Modul Fungsional Conclusion DPMS</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                        
                        <!-- Card 1: Gate -->
                        <div onclick="openLoginModal()" class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/90 hover:border-orange-500 hover:shadow-md transition-all cursor-pointer flex flex-col justify-between min-h-[140px]">
                            <div class="flex items-start justify-between">
                                <div class="w-9 h-9 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-base">
                                    <i class="fa-solid fa-id-card-clip"></i>
                                </div>
                                <span class="text-slate-400 text-xs font-mono">01</span>
                            </div>
                            <div class="mt-3">
                                <h4 class="font-bold text-slate-900 text-sm">Gate Automation</h4>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2">RFID UHF, kamera OCR, timbangan gantry otomatis & cetak e-Ticket SPK.</p>
                            </div>
                        </div>

                        <!-- Card 2: Yard Stacking -->
                        <div onclick="openLoginModal()" class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/90 hover:border-orange-500 hover:shadow-md transition-all cursor-pointer flex flex-col justify-between min-h-[140px]">
                            <div class="flex items-start justify-between">
                                <div class="w-9 h-9 rounded-xl bg-blue-100 text-cdp-blue flex items-center justify-center text-base">
                                    <i class="fa-solid fa-cubes"></i>
                                </div>
                                <span class="text-slate-400 text-xs font-mono">02</span>
                            </div>
                            <div class="mt-3">
                                <h4 class="font-bold text-slate-900 text-sm">Yard Stacking Engine</h4>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2">Slot Blok A-E, 4-tier stacking, dan optimasi Port of Discharge.</p>
                            </div>
                        </div>

                        <!-- Card 3: Rail Siding -->
                        <div onclick="openLoginModal()" class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/90 hover:border-orange-500 hover:shadow-md transition-all cursor-pointer flex flex-col justify-between min-h-[140px]">
                            <div class="flex items-start justify-between">
                                <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-base">
                                    <i class="fa-solid fa-train-subway"></i>
                                </div>
                                <span class="text-slate-400 text-xs font-mono">03</span>
                            </div>
                            <div class="mt-3">
                                <h4 class="font-bold text-slate-900 text-sm">Rail Siding Pasoso</h4>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2">Rangkaian 60 TEU kereta kargo langsung terhubung Pelabuhan Tanjung Priok.</p>
                            </div>
                        </div>

                        <!-- Card 4: CFS & Konsolidasi -->
                        <div onclick="openLoginModal()" class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/90 hover:border-orange-500 hover:shadow-md transition-all cursor-pointer flex flex-col justify-between min-h-[140px]">
                            <div class="flex items-start justify-between">
                                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-base">
                                    <i class="fa-solid fa-dolly"></i>
                                </div>
                                <span class="text-slate-400 text-xs font-mono">04</span>
                            </div>
                            <div class="mt-3">
                                <h4 class="font-bold text-slate-900 text-sm">CFS Consolidation</h4>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2">Stripping/stuffing kargo LCL dan pelacakan barcode palet berstandar GS1.</p>
                            </div>
                        </div>

                        <!-- Card 5: Reefer Telematics -->
                        <div onclick="openLoginModal()" class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/90 hover:border-orange-500 hover:shadow-md transition-all cursor-pointer flex flex-col justify-between min-h-[140px]">
                            <div class="flex items-start justify-between">
                                <div class="w-9 h-9 rounded-xl bg-cyan-100 text-cyan-600 flex items-center justify-center text-base">
                                    <i class="fa-solid fa-temperature-arrow-down"></i>
                                </div>
                                <span class="text-slate-400 text-xs font-mono">05</span>
                            </div>
                            <div class="mt-3">
                                <h4 class="font-bold text-slate-900 text-sm">Reefer Telematics</h4>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2">120 soket plug points mandiri dengan telemetri suhu real-time presisi.</p>
                            </div>
                        </div>

                        <!-- Card 6: Kalmar Equipment GPS -->
                        <div onclick="openLoginModal()" class="p-5 rounded-2xl bg-white border border-slate-200/90 hover:border-orange-500 hover:shadow-md transition-all cursor-pointer flex flex-col justify-between min-h-[145px]">
                            <div class="flex items-start justify-between">
                                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg">
                                    <i class="fa-solid fa-truck-moving"></i>
                                </div>
                                <span class="text-slate-400 text-xs font-mono">06</span>
                            </div>
                            <div class="mt-3">
                                <h4 class="font-bold text-slate-900 text-sm">Equipment Telemetry</h4>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2">Telemetri BBM, jam kerja, dan GPS presisi Kalmar DRG450 Reach Stacker.</p>
                            </div>
                        </div>

                        <!-- Card 7: Customs CEISA 4.0 -->
                        <div onclick="openLoginModal()" class="p-5 rounded-2xl bg-white border border-slate-200/90 hover:border-orange-500 hover:shadow-md transition-all cursor-pointer flex flex-col justify-between min-h-[145px]">
                            <div class="flex items-start justify-between">
                                <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-lg">
                                    <i class="fa-solid fa-stamp"></i>
                                </div>
                                <span class="text-slate-400 text-xs font-mono">07</span>
                            </div>
                            <div class="mt-3">
                                <h4 class="font-bold text-slate-900 text-sm">Bea Cukai CEISA 4.0</h4>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2">Pertukaran data elektronik manifes, SPPB, PEB, PIB instan DJBC.</p>
                            </div>
                        </div>

                        <!-- Card 8: Billing ERP & Tarif -->
                        <div onclick="openLoginModal()" class="p-5 rounded-2xl bg-white border border-slate-200/90 hover:border-orange-500 hover:shadow-md transition-all cursor-pointer flex flex-col justify-between min-h-[145px]">
                            <div class="flex items-start justify-between">
                                <div class="w-10 h-10 rounded-xl bg-green-100 text-green-600 flex items-center justify-center text-lg">
                                    <i class="fa-solid fa-receipt"></i>
                                </div>
                                <span class="text-slate-400 text-xs font-mono">08</span>
                            </div>
                            <div class="mt-3">
                                <h4 class="font-bold text-slate-900 text-sm">Billing ERP Engine</h4>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2">Otomasi kalkulasi lift-on/off, penumpukan progresif & faktur e-Tax.</p>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Mitra Terintegrasi -->
                <div class="pt-8 border-t border-slate-200">
                    <div class="text-xs font-mono text-slate-500 uppercase tracking-wider mb-6 flex items-center gap-2 font-bold">
                        <i class="fa-solid fa-handshake text-cdp-blue"></i>
                        <span>Ekosistem Mitra Logistik Terintegrasi</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                        <div class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-orange-500 transition-colors shadow-2xs">
                            <div class="font-extrabold text-sm sm:text-base text-cdp-navy">KAI LOGISTIK</div>
                            <div class="text-[10px] text-slate-500 mt-1">Operator Kereta Intermodal</div>
                        </div>
                        <div class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-orange-500 transition-colors shadow-2xs">
                            <div class="font-extrabold text-sm sm:text-base text-cdp-navy">PELINDO</div>
                            <div class="text-[10px] text-slate-500 mt-1">Pelabuhan Tanjung Priok</div>
                        </div>
                        <div class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-orange-500 transition-colors shadow-2xs">
                            <div class="font-extrabold text-sm sm:text-base text-cdp-navy">BEA CUKAI</div>
                            <div class="text-[10px] text-slate-500 mt-1">CEISA 4.0 H2H Clearance</div>
                        </div>
                        <div class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-orange-500 transition-colors shadow-2xs">
                            <div class="font-extrabold text-sm sm:text-base text-cdp-navy">ALFI / ILFA</div>
                            <div class="text-[10px] text-slate-500 mt-1">Asosiasi Forwarder Indonesia</div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 9. ABOUT / CALL TO ACTION (Vibrant Orange & Blue Corporate Banner)  -->
    <!-- =================================================================== -->
    <section id="about" class="py-12 sm:py-14 bg-gradient-to-r from-orange-600 via-orange-500 to-[#0170b9] text-white relative overflow-hidden shadow-inner">
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px]"></div>
        
        <div class="relative z-10 max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="inline-flex items-center gap-2 bg-white/20 text-white text-[10px] font-bold uppercase tracking-widest px-3.5 py-1 rounded-full mb-4 backdrop-blur-sm">
                <i class="fa-solid fa-crown text-amber-300"></i> Solusi Dry Port Terdepan di Indonesia
            </span>
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight max-w-3xl mx-auto leading-tight drop-shadow-sm">
                Siap Meningkatkan Efisiensi Rantai Pasok Anda Bersama Conclusion DPMS?
            </h2>
            <p class="text-orange-50 text-xs sm:text-sm mt-2.5 max-w-2xl mx-auto leading-relaxed font-normal">
                Akses konsol kendali operasional, pantau pergerakan kontainer real-time, atau jalankan simulasi 3D Digital Twin sekarang.
            </p>

            <div class="mt-6 flex flex-wrap items-center justify-center gap-3.5">
                <button type="button" onclick="openLoginModal()" class="inline-flex items-center gap-2.5 px-7 py-3 rounded-full bg-white hover:bg-slate-50 text-cdp-navy font-bold text-xs uppercase tracking-wider shadow-xl transition-all duration-200 transform hover:scale-105">
                    <i class="fa-solid fa-right-to-bracket text-orange-600"></i>
                    <span>Masuk ke Konsol DPMS</span>
                </button>
                <a href="dashboard.php?page=simulator" class="inline-flex items-center gap-2 px-7 py-3 rounded-full bg-white/20 hover:bg-white/30 text-white font-bold text-xs uppercase tracking-wider backdrop-blur-md border border-white/30 transition-all">
                    <i class="fa-solid fa-vr-cardboard"></i>
                    <span>Buka Simulasi 3D Real-Time</span>
                </a>
            </div>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 10. FOOTER (Petronas Multilevel Corporate Footer)                  -->
    <!-- =================================================================== -->
    <footer class="bg-[#002244] text-slate-300 text-xs border-t border-blue-900/40 relative z-20">
        <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 xl:pr-24 py-12 sm:py-14">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 mb-10">
                
                <!-- Col 1: Corporate Profile & Certifications (lg:col-span-4) -->
                <div class="lg:col-span-4 space-y-4">
                    <div class="flex items-center gap-3">
                        <img src="assets/img/logo.png" alt="Conclusion Logo" class="h-11 w-auto bg-white/10 rounded-xl p-1.5 border border-white/10">
                        <div>
                            <span class="font-black text-lg tracking-tight uppercase text-white block leading-tight">CONCLUSION</span>
                            <div class="text-[10px] text-orange-400 font-bold uppercase tracking-wider">Intermodal Dry Port &bull; DPMS Platform</div>
                        </div>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed max-w-sm">
                        Konsorsium infrastruktur pelabuhan darat 35 Hektare berstandar kelas dunia yang menghubungkan multimoda kereta api logistik, terminal peti kemas digital, serta ekosistem kepabeanan CEISA 4.0 secara berkesinambungan.
                    </p>
                    
                    <!-- Certification badges -->
                    <div class="pt-2">
                        <div class="text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-2">Standar & Kepatuhan Terakreditasi</div>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-white/5 border border-white/10 text-[10px] text-slate-200 font-mono">
                                <i class="fa-solid fa-shield-halved text-orange-400"></i> ISPS Code
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-white/5 border border-white/10 text-[10px] text-slate-200 font-mono">
                                <i class="fa-solid fa-network-wired text-blue-400"></i> CEISA 4.0 B2B
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-white/5 border border-white/10 text-[10px] text-slate-200 font-mono">
                                <i class="fa-solid fa-certificate text-emerald-400"></i> ISO 9001 &bull; 27001
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Col 2: Solusi Fungsional DPMS (lg:col-span-3) -->
                <div class="lg:col-span-3">
                    <h5 class="text-white font-bold uppercase tracking-wider text-xs mb-4 flex items-center gap-2">
                        <span class="w-1.5 h-3 bg-orange-500 rounded-full inline-block"></span>
                        Solusi Sistem DPMS
                    </h5>
                    <ul class="space-y-2.5 text-xs text-slate-300">
                        <li><a href="#pillars" class="hover:text-orange-400 transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[9px] text-slate-500"></i> Yard Management System (YMS)</a></li>
                        <li><a href="#pillars" class="hover:text-orange-400 transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[9px] text-slate-500"></i> Transport & Shunting KA (TMS)</a></li>
                        <li><a href="#pillars" class="hover:text-orange-400 transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[9px] text-slate-500"></i> CFS Warehouse & Konsolidasi (WMS)</a></li>
                        <li><a href="#highlights" class="hover:text-orange-400 transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[9px] text-slate-500"></i> Terminal Rantai Pendingin (Reefer Hub)</a></li>
                        <li><a href="dashboard.php?page=simulator" class="hover:text-orange-400 transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[9px] text-slate-500"></i> 3D Digital Twin Engine Simulator</a></li>
                        <li><a href="#network" class="hover:text-orange-400 transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[9px] text-slate-500"></i> Gerbang Otomatis RFID & ANPR</a></li>
                    </ul>
                </div>

                <!-- Col 3: Regulasi & Tata Kelola (lg:col-span-2) -->
                <div class="lg:col-span-2">
                    <h5 class="text-white font-bold uppercase tracking-wider text-xs mb-4 flex items-center gap-2">
                        <span class="w-1.5 h-3 bg-blue-500 rounded-full inline-block"></span>
                        Tata Kelola & HSSE
                    </h5>
                    <ul class="space-y-2.5 text-xs text-slate-300">
                        <li><a href="#documents" class="hover:text-orange-400 transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[9px] text-slate-500"></i> SOP Gate & CEISA 4.0</a></li>
                        <li><a href="#documents" class="hover:text-orange-400 transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[9px] text-slate-500"></i> Kebijakan K3 & HSSE Terminal</a></li>
                        <li><a href="#documents" class="hover:text-orange-400 transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[9px] text-slate-500"></i> Inisiatif Dekarbonisasi Green Port</a></li>
                        <li><a href="#documents" class="hover:text-orange-400 transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[9px] text-slate-500"></i> Pedoman Pengemudi Truk Logistik</a></li>
                        <li><a href="#documents" class="hover:text-orange-400 transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[9px] text-slate-500"></i> Whistleblowing System (WBS)</a></li>
                    </ul>
                </div>

                <!-- Col 4: Pusat Operasi & Komando (lg:col-span-3) -->
                <div class="lg:col-span-3 pr-4">
                    <h5 class="text-white font-bold uppercase tracking-wider text-xs mb-4 flex items-center gap-2">
                        <span class="w-1.5 h-3 bg-emerald-500 rounded-full inline-block"></span>
                        Pusat Operasi & Komando
                    </h5>
                    <div class="space-y-3 text-xs text-slate-300">
                        <div class="flex items-start gap-2.5">
                            <i class="fa-solid fa-location-dot text-orange-400 mt-0.5 text-xs shrink-0"></i>
                            <div>
                                <span class="text-slate-400 text-[11px] block">Terminal & Kantor Pusat:</span>
                                <span class="text-white font-medium">Kawasan Intermodal Pasoso, Km 32 Koridor Cikarang &ndash; Tanjung Priok</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="fa-solid fa-phone text-orange-400 mt-0.5 text-xs shrink-0"></i>
                            <div>
                                <span class="text-slate-400 text-[11px] block">Layanan Informasi Korporat:</span>
                                <span class="text-white font-medium">(021) 893-7890 (Hunting)</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="fa-solid fa-tower-broadcast text-blue-400 mt-0.5 text-xs shrink-0"></i>
                            <div>
                                <span class="text-slate-400 text-[11px] block">Pusat Kendali Operasi NOC:</span>
                                <span class="text-white font-medium">noc-support@conclusion-dryport.com</span>
                            </div>
                        </div>
                        
                        <!-- Prestigious Operational Status Card -->
                        <div class="mt-4 p-3.5 rounded-xl bg-white/[0.04] border border-white/10">
                            <div class="flex items-center gap-2.5 mb-1.5">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </span>
                                <span class="text-[11px] font-bold text-white tracking-wide uppercase">Kesiapan Operasional Penuh</span>
                            </div>
                            <p class="text-[10px] text-slate-400 leading-relaxed">
                                Seluruh subsistem gerbang RFID, reach stacker, rel, dan telemetri IoT beroperasi berkesinambungan dengan siklus multi-shift non-stop.
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Petronas Governance & Policy Row -->
            <div class="pt-8 border-t border-white/10 flex flex-wrap items-center justify-between gap-4 text-xs text-slate-400">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-[11px]">
                    <a href="#documents" class="hover:text-white transition-colors">Syarat &amp; Ketentuan Layanan</a>
                    <span class="text-white/20">&bull;</span>
                    <a href="#documents" class="hover:text-white transition-colors">Kebijakan Privasi &amp; Data</a>
                    <span class="text-white/20">&bull;</span>
                    <a href="#documents" class="hover:text-white transition-colors">Whistleblowing System (WBS)</a>
                    <span class="text-white/20">&bull;</span>
                    <a href="#documents" class="hover:text-white transition-colors">Kebijakan HSSE &amp; K3L</a>
                    <span class="text-white/20">&bull;</span>
                    <a href="#network" class="hover:text-white transition-colors">Peta Ekosistem Antarmoda</a>
                </div>
                <div class="text-[11px] text-slate-400 font-mono">
                    Sistem Manajemen Keamanan Informasi &bull; ISO/IEC 27001
                </div>
            </div>

            <!-- Footer Bottom: Copyright & Social Media Links -->
            <div class="mt-6 pt-6 border-t border-white/5 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-[11px] text-slate-400 text-center sm:text-left">
                    Hak Cipta &copy; <?= date('Y') ?> PT Conclusion Intermodal Dry Port Tbk. Seluruh hak cipta dilindungi undang-undang.
                </div>

                <div class="flex items-center gap-2.5 text-xs text-slate-400">
                    <a href="https://linkedin.com" target="_blank" class="w-8 h-8 rounded-full bg-white/5 hover:bg-orange-600 hover:text-white border border-white/10 flex items-center justify-center transition-all" title="LinkedIn"><i class="fa-brands fa-linkedin text-xs"></i></a>
                    <a href="https://youtube.com" target="_blank" class="w-8 h-8 rounded-full bg-white/5 hover:bg-orange-600 hover:text-white border border-white/10 flex items-center justify-center transition-all" title="YouTube"><i class="fa-brands fa-youtube text-xs"></i></a>
                    <a href="https://instagram.com" target="_blank" class="w-8 h-8 rounded-full bg-white/5 hover:bg-orange-600 hover:text-white border border-white/10 flex items-center justify-center transition-all" title="Instagram"><i class="fa-brands fa-instagram text-xs"></i></a>
                    <a href="https://twitter.com" target="_blank" class="w-8 h-8 rounded-full bg-white/5 hover:bg-orange-600 hover:text-white border border-white/10 flex items-center justify-center transition-all" title="Twitter / X"><i class="fa-brands fa-x-twitter text-xs"></i></a>
                </div>
            </div>

        </div>
    </footer>

    <!-- =================================================================== -->
    <!-- 11. SEARCH OVERLAY MODAL (Petronas-style Fullscreen Search)         -->
    <!-- =================================================================== -->
    <div id="searchOverlayModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-md hidden flex items-start justify-center pt-20 px-4 transition-all">
        <div class="w-full max-w-3xl bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-2xl relative text-slate-800">
            
            <button onclick="toggleSearchModal(false)" class="absolute top-6 right-6 w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>

            <h3 class="text-xl font-bold mb-4 flex items-center gap-2 text-slate-900">
                <i class="fa-solid fa-magnifying-glass text-orange-600"></i>
                <span>Pencarian Cepat CIDP DPMS</span>
            </h3>

            <div class="relative mb-6">
                <input type="text" id="quickSearchInput" oninput="handleQuickSearch(this.value)" placeholder="Ketik kata kunci: YMS, Reefer, CEISA, SOP, Shunting, atau Tarif..." class="w-full px-5 py-3.5 pl-12 rounded-2xl bg-slate-50 border border-slate-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-orange-500 font-medium text-sm">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            </div>

            <div class="flex flex-wrap items-center gap-2 mb-6 text-xs">
                <span class="text-slate-500">Paling Sering Dicari:</span>
                <button onclick="fillSearchKeyword('YMS')" class="px-2.5 py-1 rounded-lg bg-orange-50 hover:bg-orange-100 text-orange-700 border border-orange-200 transition-colors">YMS Stacking</button>
                <button onclick="fillSearchKeyword('Reefer')" class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 transition-colors">Reefer Plug</button>
                <button onclick="fillSearchKeyword('CEISA')" class="px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 transition-colors">CEISA 4.0</button>
                <button onclick="fillSearchKeyword('Rail')" class="px-2.5 py-1 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 transition-colors">Kereta Pasoso</button>
                <button onclick="fillSearchKeyword('SOP')" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 transition-colors">Unduh SOP</button>
            </div>

            <div id="searchResultsBox" class="space-y-2 max-h-60 overflow-y-auto pr-2">
                <div class="text-xs text-slate-400 italic p-3 text-center">
                    Masukkan kata kunci di atas untuk mencari data fasilitas, modul, atau dokumen.
                </div>
            </div>

        </div>
    </div>

    <!-- =================================================================== -->
    <!-- 12. MODAL MASUK SISTEM DPMS (Satu-satunya Pintu Masuk Terpadu)      -->
    <!-- =================================================================== -->
    <div id="loginModal" class="fixed inset-0 z-[60] bg-slate-900/60 backdrop-blur-sm <?= $showLoginModal ? '' : 'hidden' ?> flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden text-slate-800 relative animate-fadeIn">
            
            <!-- Kepala Modal -->
            <div class="bg-gradient-to-r from-[#002f5e] via-[#0170b9] to-orange-600 px-6 py-5 text-white flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-xs flex items-center justify-center border border-white/20 p-1">
                        <img src="assets/img/logo.png" alt="Conclusion Logo" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h3 class="font-bold text-base leading-tight">Masuk Portal DPMS</h3>
                        <p class="text-[11px] text-blue-100 font-medium">Conclusion Intermodal Dry Port Hub 35 Ha</p>
                    </div>
                </div>
                <button type="button" onclick="closeLoginModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Form Body -->
            <div class="p-6 space-y-4">
                
                <?php if (!empty($error_message)): ?>
                    <div class="p-3.5 rounded-2xl bg-red-50 border border-red-200 text-xs text-red-700 flex items-start space-x-2.5">
                        <i class="fa-solid fa-circle-exclamation text-red-500 text-sm mt-0.5 shrink-0"></i>
                        <span class="font-medium leading-relaxed"><?= htmlspecialchars($error_message); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="landing.php" id="formLoginModal" class="space-y-4">
                    <div>
                        <label for="modalEmail" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Alamat Email
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 pointer-events-none text-xs">
                                <i class="fa-regular fa-envelope"></i>
                            </span>
                            <input type="email" id="modalEmail" name="email" value="admin@cidp.ac.id" required 
                                   placeholder="admin@cidp.ac.id"
                                   class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all font-medium">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="modalPassword" class="block text-xs font-bold text-slate-700">
                                Kata Sandi
                            </label>
                            <span class="text-[10px] text-slate-500 font-mono">Default: admin123</span>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 pointer-events-none text-xs">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" id="modalPassword" name="password" value="admin123" required 
                                   placeholder="Kata Sandi Anda"
                                   class="w-full pl-9 pr-10 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all font-medium">
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 text-xs focus:outline-none">
                                <i class="fa-regular fa-eye" id="iconEye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pt-1">
                        <button type="submit" name="btn_login" 
                                class="w-full py-3 rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-orange-500/25 transition-all flex items-center justify-center space-x-2">
                            <span>Masuk ke Konsol DPMS</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                </form>

                <!-- AKUN DEMONSTRASI (1-Click Fill) -->
                <div class="pt-3 border-t border-slate-200">
                    <div class="text-[11px] font-bold text-slate-700 mb-2 flex items-center justify-between">
                        <span>Pilih Akun Demo Cepat:</span>
                        <span class="text-[10px] text-orange-600 font-mono">1-Klik Terisi</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-left">
                        <button type="button" onclick="setDemoCredentials('admin@cidp.ac.id', 'admin123')"
                                class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 hover:border-orange-500 hover:bg-orange-50/50 transition-all text-[11px] group">
                            <div class="font-bold text-slate-800 group-hover:text-orange-600 flex items-center gap-1.5">
                                <i class="fa-solid fa-user-shield text-orange-500 text-[10px]"></i>
                                <span>Super Admin</span>
                            </div>
                            <div class="text-[9.5px] text-slate-500 font-mono truncate">admin@cidp.ac.id</div>
                        </button>

                        <button type="button" onclick="setDemoCredentials('operator@cidp.ac.id', 'admin123')"
                                class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 hover:border-blue-500 hover:bg-blue-50/50 transition-all text-[11px] group">
                            <div class="font-bold text-slate-800 group-hover:text-cdp-blue flex items-center gap-1.5">
                                <i class="fa-solid fa-person-digging text-blue-500 text-[10px]"></i>
                                <span>Operator Yard</span>
                            </div>
                            <div class="text-[9.5px] text-slate-500 font-mono truncate">operator@cidp.ac.id</div>
                        </button>

                        <button type="button" onclick="setDemoCredentials('client@cidp.ac.id', 'admin123')"
                                class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/50 transition-all text-[11px] group">
                            <div class="font-bold text-slate-800 group-hover:text-emerald-600 flex items-center gap-1.5">
                                <i class="fa-solid fa-building text-emerald-500 text-[10px]"></i>
                                <span>Shipper / Client</span>
                            </div>
                            <div class="text-[9.5px] text-slate-500 font-mono truncate">client@cidp.ac.id</div>
                        </button>

                        <button type="button" onclick="setDemoCredentials('driver@cidp.ac.id', 'admin123')"
                                class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 hover:border-purple-500 hover:bg-purple-50/50 transition-all text-[11px] group">
                            <div class="font-bold text-slate-800 group-hover:text-purple-600 flex items-center gap-1.5">
                                <i class="fa-solid fa-truck text-purple-500 text-[10px]"></i>
                                <span>Supir Truk</span>
                            </div>
                            <div class="text-[9.5px] text-slate-500 font-mono truncate">driver@cidp.ac.id</div>
                        </button>
                    </div>

                    <!-- 1-Click Instant Direct Enter -->
                    <div class="mt-3">
                        <button type="button" onclick="quickDirectLogin()" 
                                class="w-full py-2.5 px-3 rounded-xl bg-cdp-navy hover:bg-blue-900 text-white font-bold text-xs transition-all flex items-center justify-center gap-2 shadow-xs">
                            <i class="fa-solid fa-bolt text-amber-300"></i>
                            <span>Masuk Otomatis Instan (Super Admin)</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- JAVASCRIPT CONTROLLERS                                             -->
    <!-- =================================================================== -->
    <script>
        // 1. MOBILE MENU TOGGLE
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

        // 2. HERO CAROUSEL ENGINE WITH AUTOPLAY PROGRESS BARS
        let currentSlide = 0;
        const totalSlides = 5;
        const slideDuration = 6000;
        let progressStart = Date.now();
        let animationFrameId = null;

        const slides = document.querySelectorAll('.hero-slide');
        const tabItems = document.querySelectorAll('.hero-tab');
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

            slides.forEach((slide, idx) => {
                if (idx === targetIndex) {
                    slide.classList.add('active');
                } else {
                    slide.classList.remove('active');
                }
            });

            tabItems.forEach((tab, idx) => {
                if (idx === targetIndex) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });

            progressBars.forEach((bar, idx) => {
                if (bar) {
                    if (idx < targetIndex) bar.style.width = '100%';
                    else if (idx > targetIndex) bar.style.width = '0%';
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

        switchHeroSlide(0);
        runCarouselLoop();

        // 3. HIGHLIGHTS ACCORDION IMAGE SWAP ENGINE
        function selectHighlight(index, imagePath, badgeText, captionText, specs = []) {
            const imgDisplay = document.getElementById('highlightImageDisplay');
            const badge = document.getElementById('highlightBadge');
            const caption = document.getElementById('highlightCaption');
            const specsEl = document.getElementById('highlightSpecs');
            const cards = document.querySelectorAll('.accordion-item-box');

            if (imgDisplay) {
                imgDisplay.style.opacity = '0.3';
                setTimeout(() => {
                    imgDisplay.style.backgroundImage = `url('${imagePath}')`;
                    if (badge) badge.innerText = badgeText;
                    if (caption) caption.innerText = captionText;
                    if (specsEl && specs.length) {
                        specsEl.innerHTML = specs.map(s => `<span class="bg-white/15 px-2.5 py-0.5 rounded backdrop-blur-xs font-mono">${s}</span>`).join('');
                    }
                    imgDisplay.style.opacity = '1';
                }, 150);
            }

            cards.forEach((card, idx) => {
                const desc = card.querySelector('.accordion-desc');
                const chevron = card.querySelector('.fa-chevron-right');
                const numBox = card.querySelector('.w-8.h-8');

                if (idx === index) {
                    card.classList.remove('bg-slate-50', 'border-slate-200');
                    card.classList.add('bg-orange-50/50', 'border-orange-500');
                    if (desc) desc.classList.remove('hidden');
                    if (chevron) {
                        chevron.classList.remove('text-slate-400');
                        chevron.classList.add('text-orange-600');
                    }
                    if (numBox) {
                        numBox.classList.remove('bg-slate-200', 'text-slate-700');
                        numBox.classList.add('bg-orange-600', 'text-white', 'shadow-xs');
                    }
                } else {
                    card.classList.add('bg-slate-50', 'border-slate-200');
                    card.classList.remove('bg-orange-50/50', 'border-orange-500');
                    if (desc) desc.classList.add('hidden');
                    if (chevron) {
                        chevron.classList.add('text-slate-400');
                        chevron.classList.remove('text-orange-600');
                    }
                    if (numBox) {
                        numBox.classList.add('bg-slate-200', 'text-slate-700');
                        numBox.classList.remove('bg-orange-600', 'text-white', 'shadow-xs');
                    }
                }
            });
        }

        // 4. OUR NETWORK COLLAPSIBLE SECTION TOGGLE
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

        // 5. SEARCH MODAL INTERACTION
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

        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                toggleSearchModal(false);
                closeLoginModal();
            }
        });

        const searchDatabase = [
            { title: 'Yard Management System (YMS)', desc: 'Penataan kontainer blok A-E, 4-tier stacking, dan alat berat Kalmar RS-01..03', action: 'openLoginModal()' },
            { title: 'Rail Siding Pasoso (TMS)', desc: 'Konektivitas rangkaian 60 TEU kereta api logistik langsung ke Tanjung Priok', action: 'openLoginModal()' },
            { title: 'CFS Warehouse (WMS)', desc: 'Gudang konsolidasi LCL/FCL, auto-dock leveller, dan pelacakan barcode palet GS1', action: 'openLoginModal()' },
            { title: 'Reefer Cold Chain Terminal', desc: '120 soket plug points telemetri IoT real-time presisi untuk kargo dingin bernilai tinggi', link: '#highlights' },
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
                    <div class="text-xs text-slate-400 italic p-3 text-center">
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
                    <div class="text-xs text-slate-500 p-4 text-center">
                        Tidak ditemukan hasil yang cocok dengan "<strong>${query}</strong>". Coba kata kunci lain seperti YMS, Reefer, atau Kereta.
                    </div>
                `;
            } else {
                resultsBox.innerHTML = matched.map(item => `
                    <div onclick="${item.action || "window.location.href='" + item.link + "'"}" class="block p-3 rounded-xl bg-slate-50 hover:bg-orange-50 border border-slate-200 hover:border-orange-400 transition-colors cursor-pointer">
                        <div class="font-bold text-slate-900 text-xs flex items-center justify-between">
                            <span>${item.title}</span>
                            <i class="fa-solid fa-arrow-right text-[10px] text-orange-600"></i>
                        </div>
                        <div class="text-[11px] text-slate-500 mt-1">${item.desc}</div>
                    </div>
                `).join('');
            }
        }

        // 6. LOGIN MODAL LOGIC
        function openLoginModal() {
            document.getElementById('loginModal').classList.remove('hidden');
        }

        function closeLoginModal() {
            document.getElementById('loginModal').classList.add('hidden');
        }

        function togglePasswordVisibility() {
            const passInput = document.getElementById('modalPassword');
            const eyeIcon = document.getElementById('iconEye');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        function setDemoCredentials(email, password) {
            document.getElementById('modalEmail').value = email;
            document.getElementById('modalPassword').value = password;
        }

        function quickDirectLogin() {
            document.getElementById('modalEmail').value = 'admin@cidp.ac.id';
            document.getElementById('modalPassword').value = 'admin123';
            const form = document.getElementById('formLoginModal');
            if (form) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'btn_login';
                hiddenInput.value = '1';
                form.appendChild(hiddenInput);
                form.submit();
            }
        }
    </script>
</body>
</html>
