<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek sesi login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

$iduser = $_SESSION['iduser'] ?? 1;
$nama   = (!empty($_SESSION['nama']) && $_SESSION['nama'] !== 'Guest') ? $_SESSION['nama'] : 'Zulfikar Jafarudin Fatah';
$email  = $_SESSION['email'] ?? 'admin@cidp.ac.id';
$role   = 'superadmin'; // Mode Simulasi Satu Pintu: Akses Lengkap

$page = $_GET['page'] ?? 'beranda';

// Badge & Deskripsi Akses Satu Pintu
$current_badge = ['label' => 'Konsultan (Akses Penuh)', 'color' => 'bg-emerald-100 text-emerald-800 border-emerald-200'];
$current_desc  = 'Mode Simulasi Satu Pintu Aktif. Anda memiliki hak akses penuh ke seluruh modul operasional YMS.';

// Menu Visibility Logic: Seluruh modul terbuka penuh untuk kemudahan evaluasi & demonstrasi
if (!function_exists('can_view')) {
    function can_view($menu, $role) {
        return true;
    }
}

// Struktur Navigasi Profesional: 4 Kluster Divisi Operasional Dry Port
$menu_clusters = [
    'Eksekutif & Visualisasi' => [
        'beranda' => ['icon' => 'fa-house', 'label' => 'Beranda Eksekutif'],
        'denah' => ['icon' => 'fa-map-location-dot', 'label' => 'Denah Terminal 35 Ha']
    ],
    'Operasional Utama (Core YMS)' => [
        'kontainer' => ['icon' => 'fa-boxes-stacked', 'label' => 'Pelacakan Kontainer'],
        'trucking' => ['icon' => 'fa-truck-front', 'label' => 'Manajemen Trucking'],
        'alat' => ['icon' => 'fa-dolly', 'label' => 'Lokasi Alat Berat GPS'],
        'gate' => ['icon' => 'fa-door-open', 'label' => 'Manajemen Gate'],
        'yard' => ['icon' => 'fa-cubes', 'label' => 'Manajemen Yard & Stacking'],
        'intermodal' => ['icon' => 'fa-train-subway', 'label' => 'Intermodal Rail Siding']
    ],
    'Fasilitas Khusus & Pabean' => [
        'reefer' => ['icon' => 'fa-snowflake', 'label' => 'Monitor Reefer Cold Chain'],
        'customs' => ['icon' => 'fa-shield-halved', 'label' => 'Kepabeanan & Bea Cukai'],
        'scanner' => ['icon' => 'fa-barcode', 'label' => 'Scanner SSCC / GS1']
    ],
    'Komersial & Sistem' => [
        'billing' => ['icon' => 'fa-file-invoice-dollar', 'label' => 'Billing & Faktur ERP'],
        'simulator' => ['icon' => 'fa-gamepad', 'label' => 'Panel Simulasi IoT'],
        'settings' => ['icon' => 'fa-gear', 'label' => 'Pengaturan Global']
    ]
];

// Flatten untuk kemudahan akses judul & routing
$menu_items = [];
foreach ($menu_clusters as $c_name => $items) {
    foreach ($items as $k => $v) {
        $menu_items[$k] = $v;
    }
}

// Helper untuk format tanggal Indonesia
$bulan = [
    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];
$tanggal_sekarang = date('j') . ' ' . $bulan[(int)date('n')] . ' ' . date('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CIDP YMS</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    
    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        'cdp-navy': '#004b87',
                        'cdp-dark': '#002f5e',
                        'cdp-blue': '#0170b9',
                        'cdp-light': '#f4f8fc',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f8fc; }
        .sidebar-transition { transition: width 0.3s ease-in-out; }
        
        /* Custom scrollbar for sidebar and feeds */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Activity timeline dots */
        .timeline-dot {
            position: absolute;
            left: -5px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }
    </style>
</head>
<body class="text-gray-800 antialiased min-h-screen h-[100dvh] overflow-hidden flex bg-cdp-light">

    <!-- Sidebar -->
    <aside id="sidebar" class="bg-cdp-dark text-white flex-shrink-0 z-40 h-full overflow-y-auto sidebar-transition w-64 fixed md:relative transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out shadow-xl md:shadow-none">
        <!-- Logo (Height aligned with top header: h-14 / 56px) -->
        <div class="h-14 flex items-center justify-between px-4 border-b border-white/10 bg-cdp-navy/50">
            <div class="flex items-center overflow-hidden">
                <img src="assets/img/logo.png" alt="Logo" class="w-8 h-8 object-contain mr-3 bg-white rounded p-0.5 flex-shrink-0" onerror="this.src='https://via.placeholder.com/32?text=C'">
                <span class="text-lg font-bold tracking-wide logo-text whitespace-nowrap">CIDP YMS</span>
            </div>
            <!-- Mobile Close Button -->
            <button onclick="toggleSidebar()" class="md:hidden text-white/70 hover:text-white p-1.5 rounded-lg focus:outline-none transition-colors" title="Tutup Menu">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        
        <!-- Navigation: 4 Kluster Divisi Operasional -->
        <nav class="p-3 space-y-4">
            <?php foreach ($menu_clusters as $cluster_title => $items): ?>
                <div>
                    <div class="text-[10px] font-bold text-white/40 uppercase tracking-wider mb-1.5 px-3 logo-text whitespace-nowrap">
                        <?= $cluster_title ?>
                    </div>
                    <div class="space-y-1">
                        <?php foreach ($items as $key => $item): ?>
                            <?php if (can_view($key, $role)): ?>
                                <a href="dashboard.php?page=<?= $key ?>" 
                                   class="flex items-center px-3 py-2 rounded-lg transition-colors group <?= $page === $key ? 'bg-cdp-blue text-white shadow-md' : 'text-gray-300 hover:bg-white/10 hover:text-white' ?>"
                                   title="<?= $item['label'] ?>">
                                    <i class="fa-solid <?= $item['icon'] ?> w-5 text-center text-sm <?= $page === $key ? 'text-white' : 'text-gray-400 group-hover:text-white' ?>"></i>
                                    <span class="ml-2.5 text-xs font-medium logo-text whitespace-nowrap"><?= $item['label'] ?></span>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </nav>
    </aside>

    <!-- Main Wrapper -->
    <div class="flex-1 flex flex-col h-full overflow-hidden relative">
        
        <!-- Overlay for mobile sidebar -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs z-30 hidden md:hidden transition-opacity" onclick="toggleSidebar()"></div>

        <!-- Top Header (Aligned with sidebar logo: h-14 / 56px) -->
        <header class="h-14 bg-white border-b border-gray-200/80 z-10 flex-shrink-0">
            <div class="h-full w-full max-w-[1680px] mx-auto flex items-center justify-between px-3.5 sm:px-5 lg:px-8">
                <!-- Left: Toggle & Page Title -->
                <div class="flex items-center min-w-0 mr-2">
                    <!-- Mobile Toggle -->
                    <button onclick="toggleSidebar()" class="mr-3 p-1.5 text-gray-500 hover:text-cdp-blue focus:outline-none md:hidden transition-colors rounded-lg hover:bg-gray-100" title="Buka Menu">
                        <i class="fa-solid fa-bars text-base"></i>
                    </button>
                    <!-- Desktop Toggle -->
                    <button onclick="toggleDesktopSidebar()" class="mr-3 p-1.5 text-gray-500 hover:text-cdp-blue focus:outline-none hidden md:block transition-colors rounded-lg hover:bg-gray-100" title="Kecilkan/Perbesar Menu">
                        <i class="fa-solid fa-bars-staggered text-base" id="desktopToggleIcon"></i>
                    </button>
                    <h1 class="text-sm sm:text-base font-bold text-gray-800 capitalize truncate">
                        <?= isset($menu_items[$page]) ? $menu_items[$page]['label'] : 'Dashboard' ?>
                    </h1>
                </div>
                
                <!-- Right: User & Actions -->
                <div class="flex items-center space-x-2 sm:space-x-3 flex-shrink-0">
                    <button class="relative p-1.5 text-gray-400 hover:text-cdp-blue transition-colors rounded-lg hover:bg-gray-100" title="Notifikasi Sistem">
                        <i class="fa-regular fa-bell text-base"></i>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                    </button>
                    
                    <div class="h-5 w-px bg-gray-200 mx-0.5 sm:mx-1"></div>
                    
                    <div class="flex items-center space-x-2.5 group">
                        <div class="flex flex-col text-right hidden sm:flex">
                            <span class="text-xs font-bold text-gray-800 leading-tight"><?= htmlspecialchars($nama) ?></span>
                            <span class="text-[9px] uppercase font-bold px-1.5 py-0.5 rounded border <?= $current_badge['color'] ?> inline-block mt-0.5 self-end">
                                <?= $current_badge['label'] ?>
                            </span>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-cdp-blue text-white flex items-center justify-center font-bold text-xs shadow-xs border border-white ring-1 ring-gray-200 group-hover:ring-cdp-blue/30 transition-all cursor-pointer">
                            <?= strtoupper(substr($nama, 0, 1)) ?>
                        </div>
                    </div>
                    
                    <a href="logout.php" class="p-1.5 text-gray-400 hover:text-red-500 transition-colors rounded-lg hover:bg-red-50" title="Keluar" onclick="return confirm('Apakah Anda yakin ingin keluar?');">
                        <i class="fa-solid fa-right-from-bracket text-base"></i>
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content Scrollable Area (Symmetrical Container) -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-cdp-light relative">
            <div class="p-3.5 sm:p-5 lg:p-8 w-full max-w-[1680px] mx-auto space-y-4 sm:space-y-5">
                
                <?php if ($page === 'beranda'): ?>
                
                <!-- Welcome Banner (Slim, Crisp, & Compact) -->
                <div class="bg-white rounded-xl p-3.5 sm:p-4 shadow-2xs border border-gray-200/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 transition-all hover:border-blue-200">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-1.5">
                            Selamat datang, <?= htmlspecialchars($nama) ?>! 👋
                        </h2>
                        <p class="text-gray-500 text-xs mt-0.5"><?= $current_desc ?></p>
                    </div>
                    <div class="text-xs text-gray-600 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200/80 flex items-center shadow-2xs flex-shrink-0 self-stretch sm:self-auto justify-between sm:justify-start">
                        <span class="flex items-center">
                            <i class="fa-regular fa-calendar mr-1.5 text-cdp-blue text-xs"></i>
                            <?= $tanggal_sekarang ?>
                        </span>
                        <span class="sm:hidden text-[10px] font-bold text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded ml-2">Live</span>
                    </div>
                </div>

                <!-- 6 KPI Summary Cards (Compact 1-Row Grid on Desktop, Symmetrical 2x3 Grid on Mobile) -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-3.5">
                    
                    <!-- KPI 1 -->
                    <div class="bg-white rounded-xl p-3 sm:p-3.5 shadow-2xs border border-gray-200/80 hover:border-blue-300 hover:shadow-xs transition-all group flex flex-col justify-between h-full">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[10px] sm:text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Yard Stack</p>
                                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mt-0.5">126 <span class="text-[11px] font-normal text-gray-400">box</span></h3>
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs group-hover:scale-105 transition-transform flex-shrink-0">
                                <i class="fa-solid fa-boxes-stacked"></i>
                            </div>
                        </div>
                        <div class="mt-2.5 flex items-center text-[10px] sm:text-[11px] text-emerald-600 font-medium">
                            <i class="fa-solid fa-arrow-trend-up mr-1 text-[9px]"></i> +8 dari kemarin
                        </div>
                    </div>

                    <!-- KPI 2 -->
                    <div class="bg-white rounded-xl p-3 sm:p-3.5 shadow-2xs border border-gray-200/80 hover:border-indigo-300 hover:shadow-xs transition-all group flex flex-col justify-between h-full">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[10px] sm:text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Truk Hari Ini</p>
                                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mt-0.5">14 <span class="text-[11px] font-normal text-gray-400">unit</span></h3>
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs group-hover:scale-105 transition-transform flex-shrink-0">
                                <i class="fa-solid fa-truck"></i>
                            </div>
                        </div>
                        <div class="mt-2.5 flex items-center text-[10px] sm:text-[11px] text-gray-500 space-x-2">
                            <span class="text-indigo-600 font-medium">In: 9</span>
                            <span class="text-gray-400">•</span>
                            <span>Out: 5</span>
                        </div>
                    </div>

                    <!-- KPI 3 -->
                    <div class="bg-white rounded-xl p-3 sm:p-3.5 shadow-2xs border border-gray-200/80 hover:border-purple-300 hover:shadow-xs transition-all group flex flex-col justify-between h-full">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[10px] sm:text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Kereta Api</p>
                                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mt-0.5">2 <span class="text-[11px] font-normal text-gray-400">KA</span></h3>
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs group-hover:scale-105 transition-transform flex-shrink-0">
                                <i class="fa-solid fa-train"></i>
                            </div>
                        </div>
                        <div class="mt-2.5 flex items-center text-[10px] sm:text-[11px] text-purple-600 font-medium truncate">
                            JKT - SMG Active
                        </div>
                    </div>

                    <!-- KPI 4 -->
                    <div class="bg-white rounded-xl p-3 sm:p-3.5 shadow-2xs border border-gray-200/80 hover:border-cyan-300 hover:shadow-xs transition-all group flex flex-col justify-between h-full">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[10px] sm:text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Reefer Plug</p>
                                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mt-0.5">38 <span class="text-[11px] font-normal text-gray-400">unit</span></h3>
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center text-xs group-hover:scale-105 transition-transform flex-shrink-0">
                                <i class="fa-solid fa-snowflake"></i>
                            </div>
                        </div>
                        <div class="mt-2.5 flex items-center text-[10px] sm:text-[11px] text-emerald-600 font-medium">
                            <i class="fa-solid fa-check mr-1 text-[9px]"></i> Suhu Aman
                        </div>
                    </div>

                    <!-- KPI 5 -->
                    <div class="bg-white rounded-xl p-3 sm:p-3.5 shadow-2xs border border-gray-200/80 hover:border-red-300 hover:shadow-xs transition-all group flex flex-col justify-between h-full">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[10px] sm:text-[11px] font-semibold text-gray-500 uppercase tracking-wider">DG / IMO</p>
                                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mt-0.5">3 <span class="text-[11px] font-normal text-gray-400">box</span></h3>
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center text-xs group-hover:scale-105 transition-transform flex-shrink-0">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                        </div>
                        <div class="mt-2.5 flex items-center text-[10px] sm:text-[11px] text-amber-600 font-medium">
                            Terisolasi di DG
                        </div>
                    </div>

                    <!-- KPI 6 -->
                    <div class="bg-white rounded-xl p-3 sm:p-3.5 shadow-2xs border border-gray-200/80 hover:border-emerald-300 hover:shadow-xs transition-all group flex flex-col justify-between h-full">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[10px] sm:text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Revenue</p>
                                <h3 class="text-xl sm:text-2xl font-bold text-emerald-700 mt-0.5">Rp 47.2M</h3>
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs group-hover:scale-105 transition-transform flex-shrink-0">
                                <i class="fa-solid fa-money-bill-wave"></i>
                            </div>
                        </div>
                        <div class="mt-2.5 flex items-center text-[10px] sm:text-[11px] text-gray-500 truncate">
                            Gross hari ini
                        </div>
                    </div>

                </div>

                <!-- Row 1: 2 Charts Side-by-side (Equal Proportions & Symmetrical) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3.5 sm:gap-4">
                    <!-- Line Chart -->
                    <div class="bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3.5 sm:p-4 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2.5 pb-2 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs sm:text-sm flex items-center">
                                <i class="fa-solid fa-chart-line mr-2 text-cdp-blue text-xs"></i>Tren Aktivitas Gate (7 Hari Terakhir)
                            </h3>
                            <span class="text-[10px] font-semibold text-gray-400 bg-gray-50 px-2 py-0.5 rounded">In vs Out</span>
                        </div>
                        <div class="h-56 sm:h-60 relative w-full flex-1">
                            <canvas id="gateTrendChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Doughnut Chart -->
                    <div class="bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3.5 sm:p-4 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2.5 pb-2 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs sm:text-sm flex items-center">
                                <i class="fa-solid fa-chart-pie mr-2 text-cdp-blue text-xs"></i>Distribusi Tipe Kontainer
                            </h3>
                            <span class="text-[10px] font-semibold text-gray-400 bg-gray-50 px-2 py-0.5 rounded">Total 126 Box</span>
                        </div>
                        <div class="h-56 sm:h-60 relative w-full flex-1 flex items-center justify-center">
                            <canvas id="containerTypeChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Row 2: 3 Columns (Equal Proportions & Aligned Headers) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-3.5 sm:gap-4">
                    <!-- Vertical Bar Chart -->
                    <div class="bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3.5 sm:p-4 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2.5 pb-2 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs sm:text-sm flex items-center">
                                <i class="fa-solid fa-chart-column mr-2 text-cdp-blue text-xs"></i>Throughput per Blok Yard
                            </h3>
                            <span class="text-[10px] font-semibold text-gray-400 bg-gray-50 px-2 py-0.5 rounded">TEU</span>
                        </div>
                        <div class="h-56 sm:h-60 relative w-full flex-1">
                            <canvas id="yardBlockChart"></canvas>
                        </div>
                    </div>

                    <!-- Gauges (Symmetrical Header, Responsive Side-by-Side on Mobile) -->
                    <div class="bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3.5 sm:p-4 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2.5 pb-2 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs sm:text-sm flex items-center">
                                <i class="fa-solid fa-gauge mr-2 text-cdp-blue text-xs"></i>Utilisasi & Efisiensi Yard
                            </h3>
                            <span class="text-[10px] font-semibold text-gray-400 bg-gray-50 px-2 py-0.5 rounded">KPI Yard</span>
                        </div>
                        
                        <div class="grid grid-cols-2 lg:grid-cols-1 gap-3 py-1 flex-1 items-center justify-around">
                            <!-- Gauge 1 -->
                            <div class="text-center flex flex-col items-center justify-center">
                                <h4 class="font-bold text-gray-700 text-xs mb-1">Utilisasi Yard</h4>
                                <div class="relative w-36 h-[72px] mx-auto overflow-hidden">
                                    <svg viewBox="0 0 200 100" class="w-full h-full">
                                        <path d="M 20 100 A 80 80 0 0 1 180 100" fill="none" stroke="#e5e7eb" stroke-width="16" stroke-linecap="round"/>
                                        <path d="M 20 100 A 80 80 0 0 1 180 100" fill="none" stroke="#eab308" stroke-width="16" stroke-linecap="round" stroke-dasharray="251.2" stroke-dashoffset="92.9"/>
                                    </svg>
                                    <div class="absolute bottom-0 w-full text-center">
                                        <span class="text-xl sm:text-2xl font-bold text-gray-800">63%</span>
                                    </div>
                                </div>
                                <p class="text-[10px] text-gray-500 mt-1">126 dari 200 slot terisi</p>
                            </div>
                            
                            <!-- Gauge 2 -->
                            <div class="text-center flex flex-col items-center justify-center">
                                <h4 class="font-bold text-gray-700 text-xs mb-1">Rata-rata Dwell Time</h4>
                                <div class="relative w-36 h-[72px] mx-auto overflow-hidden">
                                    <svg viewBox="0 0 200 100" class="w-full h-full">
                                        <path d="M 20 100 A 80 80 0 0 1 180 100" fill="none" stroke="#e5e7eb" stroke-width="16" stroke-linecap="round"/>
                                        <path d="M 20 100 A 80 80 0 0 1 180 100" fill="none" stroke="#22c55e" stroke-width="16" stroke-linecap="round" stroke-dasharray="251.2" stroke-dashoffset="75.4"/>
                                    </svg>
                                    <div class="absolute bottom-0 w-full text-center">
                                        <span class="text-xl sm:text-2xl font-bold text-gray-800">2.1</span>
                                    </div>
                                </div>
                                <p class="text-[10px] text-gray-500 mt-1">Target &lt; 3.0 hari</p>
                            </div>
                        </div>
                    </div>

                    <!-- Horizontal Bar Chart -->
                    <div class="bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3.5 sm:p-4 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2.5 pb-2 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs sm:text-sm flex items-center">
                                <i class="fa-solid fa-list-ol mr-2 text-cdp-blue text-xs"></i>Top 5 Perusahaan Pelanggan
                            </h3>
                            <span class="text-[10px] font-semibold text-gray-400 bg-gray-50 px-2 py-0.5 rounded">Volume</span>
                        </div>
                        <div class="h-56 sm:h-60 relative w-full flex-1">
                            <canvas id="topCustomersChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Row 3: 2 Cards (Balanced Heights on Desktop & Mobile) -->
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-3.5 sm:gap-4">
                    <!-- Stacked Bar Chart -->
                    <div class="lg:col-span-3 bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3.5 sm:p-4 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2.5 pb-2 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs sm:text-sm flex items-center">
                                <i class="fa-solid fa-sack-dollar mr-2 text-cdp-blue text-xs"></i>Pendapatan per Jenis Layanan (7 Hari)
                            </h3>
                            <span class="text-[10px] font-semibold text-gray-400 bg-gray-50 px-2 py-0.5 rounded">Juta Rupiah</span>
                        </div>
                        <div class="h-64 sm:h-72 lg:h-[290px] relative w-full flex-1">
                            <canvas id="revenueStackedChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Summary Stats Card: Performa Terminal -->
                    <div class="lg:col-span-2 bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3.5 sm:p-4 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2.5 pb-2 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs sm:text-sm flex items-center">
                                <i class="fa-solid fa-gauge-high mr-2 text-cdp-blue text-xs"></i>Performa Terminal
                            </h3>
                            <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">Operasional</span>
                        </div>
                        
                        <div class="space-y-3 py-1 flex-1 flex flex-col justify-around">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs flex-shrink-0">
                                        <i class="fa-solid fa-stopwatch"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-500 font-medium">Rata-rata Waktu Gate-In</p>
                                        <p class="text-sm font-bold text-gray-800">2 mnt 15 dtk</p>
                                    </div>
                                </div>
                                <span class="text-green-600 text-[10px] font-bold bg-green-50 px-2 py-0.5 rounded-full"><i class="fa-solid fa-arrow-down mr-1"></i>12s</span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs flex-shrink-0">
                                        <i class="fa-solid fa-truck-ramp-box"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-500 font-medium">Truk Ditangani (Bulan Ini)</p>
                                        <p class="text-sm font-bold text-gray-800">342 <span class="text-[10px] font-normal text-gray-400">kendaraan</span></p>
                                    </div>
                                </div>
                                <span class="text-indigo-600 text-[10px] font-bold bg-indigo-50 px-2 py-0.5 rounded-full"><i class="fa-solid fa-check mr-1"></i>98% On-Time</span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs flex-shrink-0">
                                        <i class="fa-solid fa-file-invoice"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-500 font-medium">Invoice Terbit (Bulan Ini)</p>
                                        <p class="text-sm font-bold text-gray-800">289 <span class="text-[10px] font-normal text-gray-400">faktur</span></p>
                                    </div>
                                </div>
                                <span class="text-purple-600 text-[10px] font-bold bg-purple-50 px-2 py-0.5 rounded-full"><i class="fa-solid fa-arrow-up mr-1"></i>+5.2%</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs flex-shrink-0">
                                        <i class="fa-solid fa-money-check-dollar"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-500 font-medium">Total Revenue (Bulan Ini)</p>
                                        <p class="text-sm font-bold text-gray-800">Rp 1.42 M</p>
                                    </div>
                                </div>
                                <span class="text-emerald-600 text-[10px] font-bold bg-emerald-50 px-2 py-0.5 rounded-full"><i class="fa-solid fa-arrow-trend-up mr-1"></i>104% Target</span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center text-xs flex-shrink-0">
                                        <i class="fa-solid fa-shield-halved"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-500 font-medium">Tingkat Kecelakaan</p>
                                        <p class="text-sm font-bold text-gray-800">0 <span class="text-[10px] font-normal text-gray-400">insiden</span></p>
                                    </div>
                                </div>
                                <span class="text-green-600 text-[10px] font-bold bg-green-100 px-2 py-0.5 rounded"><i class="fa-solid fa-check mr-1"></i>Zero Incident</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 4: Two-Column Layout: Aktivitas Terkini & Antrian Gerbang -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-3.5 sm:gap-4">
                    
                    <!-- Left Col: Aktivitas Terkini (span 2) -->
                    <div class="lg:col-span-2 bg-white rounded-xl shadow-2xs border border-gray-200/80 flex flex-col justify-between">
                        <div class="p-3.5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50 rounded-t-xl">
                            <h3 class="font-bold text-gray-800 text-xs sm:text-sm flex items-center">
                                <i class="fa-solid fa-clock-rotate-left mr-2 text-cdp-blue text-xs"></i>Aktivitas Terkini
                            </h3>
                            <a href="dashboard.php?page=kontainer" class="text-xs text-cdp-blue hover:underline font-medium">Lihat Semua</a>
                        </div>
                        <div class="p-3.5 flex-1">
                            <div class="relative border-l-2 border-gray-100 ml-2.5 space-y-3.5">
                                
                                <!-- Event 1 -->
                                <div class="relative pl-5">
                                    <div class="timeline-dot bg-blue-500 ring-2 ring-white"></div>
                                    <div class="flex justify-between items-start mb-0.5">
                                        <div class="font-semibold text-gray-800 text-xs">Gate-In Truk <span class="text-blue-600 bg-blue-50 px-1.5 py-0.2 rounded text-[10px] border border-blue-100 font-mono">B 9481 UEK</span></div>
                                        <div class="text-[10px] text-gray-400 font-medium bg-gray-50 px-1.5 py-0.5 rounded">14:32</div>
                                    </div>
                                    <p class="text-[11px] text-gray-500">MSKU9182374 | 40ft HC</p>
                                </div>

                                <!-- Event 2 -->
                                <div class="relative pl-5">
                                    <div class="timeline-dot bg-orange-500 ring-2 ring-white"></div>
                                    <div class="flex justify-between items-start mb-0.5">
                                        <div class="font-semibold text-gray-800 text-xs">Lift-Off RS-03 <i class="fa-solid fa-arrow-right text-gray-300 mx-1"></i> BLOCK-B Bay-08 Row-03 Tier-02</div>
                                        <div class="text-[10px] text-gray-400 font-medium bg-gray-50 px-1.5 py-0.5 rounded">14:28</div>
                                    </div>
                                </div>

                                <!-- Event 3 -->
                                <div class="relative pl-5">
                                    <div class="timeline-dot bg-red-500 ring-2 ring-white animate-pulse"></div>
                                    <div class="flex justify-between items-start mb-0.5">
                                        <div class="font-semibold text-gray-800 text-xs">Reefer Alert <span class="text-red-600 bg-red-50 px-1.5 py-0.2 rounded text-[10px] border border-red-100 font-mono">TEMU4819203</span></div>
                                        <div class="text-[10px] text-red-500 font-bold bg-red-50 px-1.5 py-0.5 rounded">14:15</div>
                                    </div>
                                    <p class="text-[11px] text-gray-500">Suhu -18.2°C (Batas -20°C)</p>
                                </div>

                                <!-- Event 4 -->
                                <div class="relative pl-5">
                                    <div class="timeline-dot bg-blue-500 ring-2 ring-white"></div>
                                    <div class="flex justify-between items-start mb-0.5">
                                        <div class="font-semibold text-gray-800 text-xs">Gate-Out Truk <span class="text-blue-600 bg-blue-50 px-1.5 py-0.2 rounded text-[10px] border border-blue-100 font-mono">B 7712 SCK</span></div>
                                        <div class="text-[10px] text-gray-400 font-medium bg-gray-50 px-1.5 py-0.5 rounded">13:55</div>
                                    </div>
                                    <p class="text-[11px] text-gray-500">TCLU8827415 | Invoice #INV-0089</p>
                                </div>

                                <!-- Event 5 -->
                                <div class="relative pl-5">
                                    <div class="timeline-dot bg-purple-500 ring-2 ring-white"></div>
                                    <div class="flex justify-between items-start mb-0.5">
                                        <div class="font-semibold text-gray-800 text-xs">Kereta KA-LOG-JKT-SMG tiba di Rail Siding</div>
                                        <div class="text-[10px] text-gray-400 font-medium bg-gray-50 px-1.5 py-0.5 rounded">13:42</div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Right Col: Panel Ringkasan -->
                    <div class="flex flex-col space-y-3.5 sm:space-y-4">
                        
                        <!-- Antrian Gate -->
                        <div class="bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3.5">
                            <h3 class="font-bold text-gray-800 text-xs sm:text-sm mb-3 border-b border-gray-100 pb-1.5 flex items-center">
                                <i class="fa-solid fa-truck-fast mr-2 text-cdp-blue text-xs"></i>Antrian Gate
                            </h3>
                            
                            <div class="space-y-3">
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-600 font-medium">Gate-In (3 truk)</span>
                                        <span class="text-gray-800 font-bold">60%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                                        <div class="bg-indigo-500 h-1.5 rounded-full" style="width: 60%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-600 font-medium">Gate-Out (1 truk)</span>
                                        <span class="text-gray-800 font-bold">20%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                                        <div class="bg-blue-400 h-1.5 rounded-full" style="width: 20%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Okupansi Yard -->
                        <div class="bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3.5 flex-1 flex flex-col justify-between">
                            <div>
                                <div class="border-b border-gray-100 pb-1.5 mb-3 flex justify-between items-center">
                                    <h3 class="font-bold text-gray-800 text-xs sm:text-sm flex items-center">
                                        <i class="fa-solid fa-chart-pie mr-2 text-cdp-blue text-xs"></i>Okupansi Yard
                                    </h3>
                                    <span class="text-[10px] font-semibold text-gray-600 bg-gray-100 px-1.5 py-0.5 rounded">126 / 200 slot</span>
                                </div>
                                
                                <div class="space-y-2.5">
                                    <!-- Block A -->
                                    <div>
                                        <div class="flex justify-between text-[11px] mb-1">
                                            <span class="text-gray-600 font-medium">Block A</span>
                                            <span class="text-gray-800 font-bold">78%</span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                                            <div class="bg-blue-600 h-1.5 rounded-full" style="width: 78%"></div>
                                        </div>
                                    </div>
                                    <!-- Block B -->
                                    <div>
                                        <div class="flex justify-between text-[11px] mb-1">
                                            <span class="text-gray-600 font-medium">Block B</span>
                                            <span class="text-gray-800 font-bold">62%</span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                                            <div class="bg-blue-400 h-1.5 rounded-full" style="width: 62%"></div>
                                        </div>
                                    </div>
                                    <!-- Reefer Zone -->
                                    <div>
                                        <div class="flex justify-between text-[11px] mb-1">
                                            <span class="text-gray-600 font-medium">Reefer Zone</span>
                                            <span class="text-red-500 font-bold">83%</span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                                            <div class="bg-cyan-400 h-1.5 rounded-full" style="width: 83%"></div>
                                        </div>
                                    </div>
                                    <!-- DG Yard -->
                                    <div>
                                        <div class="flex justify-between text-[11px] mb-1">
                                            <span class="text-gray-600 font-medium">DG Yard</span>
                                            <span class="text-gray-800 font-bold">33%</span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                                            <div class="bg-red-500 h-1.5 rounded-full" style="width: 33%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Tabel Pelacakan Muatan Klien (Shipper Consignment Tracking) -->
                <div class="bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3.5 sm:p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 mb-3 pb-2 border-b border-gray-100">
                        <h3 class="font-bold text-gray-800 text-xs sm:text-sm flex items-center">
                            <i class="fa-solid fa-list-check mr-2 text-cdp-blue text-xs"></i>Pelacakan Konsinyasi Muatan Klien (Shipper Consignment Tracking)
                        </h3>
                        <div class="flex items-center space-x-2 flex-wrap">
                            <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                <i class="fa-solid fa-satellite-dish mr-1 text-[9px]"></i>Live Tracking
                            </span>
                            <a href="dashboard.php?page=kontainer" class="text-[11px] font-bold text-[#0170b9] hover:text-[#004b87] bg-blue-50 px-2 py-0.5 rounded border border-blue-200 transition">
                                Buka Semua Milestone <i class="fa-solid fa-arrow-right ml-1 text-[9px]"></i>
                            </a>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[680px]">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 text-[11px] border-y border-gray-100">
                                    <th class="py-2.5 px-3 font-semibold">Nomor Kontainer</th>
                                    <th class="py-2.5 px-3 font-semibold">Tipe & Ukuran</th>
                                    <th class="py-2.5 px-3 font-semibold">Tag RFID UHF</th>
                                    <th class="py-2.5 px-3 font-semibold">Status Muatan</th>
                                    <th class="py-2.5 px-3 font-semibold">Posisi di Lapangan</th>
                                    <th class="py-2.5 px-3 font-semibold text-right">Pembaruan Terakhir</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs divide-y divide-gray-100">
                                <tr class="hover:bg-blue-50/20 transition">
                                    <td class="py-2.5 px-3 font-mono font-bold text-[#0170b9]">MSKU9182374</td>
                                    <td class="py-2.5 px-3 text-gray-600">40ft High Cube (Dry)</td>
                                    <td class="py-2.5 px-3 font-mono text-purple-700 font-bold">E280117000000001</td>
                                    <td class="py-2.5 px-3"><span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-[10px] font-bold">In Yard (Penumpukan)</span></td>
                                    <td class="py-2.5 px-3 font-semibold text-gray-800">Blok B / Bay 08 / Row 03 / Tier 02</td>
                                    <td class="py-2.5 px-3 text-gray-500 text-right">Hari ini, 14:32 WIB</td>
                                </tr>
                                <tr class="hover:bg-blue-50/20 transition">
                                    <td class="py-2.5 px-3 font-mono font-bold text-[#0170b9]">TCLU8827415</td>
                                    <td class="py-2.5 px-3 text-gray-600">20ft Standard (Dry)</td>
                                    <td class="py-2.5 px-3 font-mono text-purple-700 font-bold">E280117000000002</td>
                                    <td class="py-2.5 px-3"><span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-[10px] font-bold">Gate Out (Menuju Pelabuhan)</span></td>
                                    <td class="py-2.5 px-3 text-gray-600">Dalam Armada Truk B 7712 SCK</td>
                                    <td class="py-2.5 px-3 text-gray-500 text-right">Hari ini, 13:55 WIB</td>
                                </tr>
                                <tr class="hover:bg-blue-50/20 transition">
                                    <td class="py-2.5 px-3 font-mono font-bold text-[#0170b9]">TEMU4819203</td>
                                    <td class="py-2.5 px-3 text-gray-600">40ft Reefer Cold Chain</td>
                                    <td class="py-2.5 px-3 font-mono text-purple-700 font-bold">E280117000000004</td>
                                    <td class="py-2.5 px-3"><span class="bg-cyan-100 text-cyan-700 px-2 py-0.5 rounded text-[10px] font-bold">Terkoneksi Listrik (-18.2°C)</span></td>
                                    <td class="py-2.5 px-3 font-semibold text-cyan-700">Reefer Rack R-02 Plug #14</td>
                                    <td class="py-2.5 px-3 text-gray-500 text-right">Kemarin, 16:40 WIB</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php elseif ($page === 'kontainer'): ?>
                    <?php include_once __DIR__ . '/pages/kontainer.php'; ?>
                <?php elseif ($page === 'trucking'): ?>
                    <?php include_once __DIR__ . '/pages/trucking.php'; ?>
                <?php elseif ($page === 'alat'): ?>
                    <?php include_once __DIR__ . '/pages/alat.php'; ?>
                <?php elseif ($page === 'simulator'): ?>
                    <?php include_once __DIR__ . '/pages/simulator.php'; ?>
                <?php else: ?>
                
                <?php
                // Pemetaan Tanggung Jawab Anggota Tim & Status Menunggu Input
                $divisi_info = [
                    'gate' => [
                        'pic' => 'Muhammad Arman (2344190012)',
                        'role' => 'Infrastructure, Hardware & Automation Integrator',
                        'desc' => 'Spesifikasi teknis hardware sensor Gate (Kamera ANPR, OCR Kontainer ISO 6346, Jembatan Timbang VGM 80 Ton, dan Pembaca RFID UHF) sedang disusun oleh tim integrasi hardware.',
                        'icon' => 'fa-door-open',
                        'status' => 'Menunggu Finalisasi Hardware dari Arman'
                    ],
                    'denah' => [
                        'pic' => 'Juan Manuel (2344190013) & Muhammad Arman (2344190012)',
                        'role' => 'Yard Layout Specialist & Automation Integrator',
                        'desc' => 'Sketsa tata letak fisik terminal 35 Ha (Blok Penumpukan A-E, Jalur Rel, Area Reefer, DG Yard & Depo M&R) sedang digambar oleh Juan dan akan dimasukkan ke dalam prototipe sistem oleh Arman.',
                        'icon' => 'fa-map-location-dot',
                        'status' => 'Menunggu Gambar Denah dari Juan & Arman'
                    ],
                    'intermodal' => [
                        'pic' => 'Juan Manuel & Muhammad Daffa Al Hafizh',
                        'role' => 'Terminal Ops & Business Workflow Analyst',
                        'desc' => 'Alur perpindahan moda logistik kereta api (Rail Siding) dan armada truk jalan raya sedang diselaraskan dengan SOP operasional alih muat antarmoda dry port.',
                        'icon' => 'fa-train-subway',
                        'status' => 'Menunggu Penyelarasan Alur Antarmoda KA-Truk'
                    ],
                    'yard' => [
                        'pic' => 'Juan Manuel (2344190013)',
                        'role' => 'Yard Layout & Terminal Ops Specialist',
                        'desc' => 'Aturan penataan kontainer di lapangan (Stacking Rules, Alokasi Bay-Row-Tier, dan Audit Dwell Time) sedang dirumuskan sesuai standar operasional ICD.',
                        'icon' => 'fa-boxes-stacked',
                        'status' => 'Menunggu Rumusan Stacking Rules dari Juan'
                    ],
                    'reefer' => [
                        'pic' => 'Muhammad Arman (2344190012)',
                        'role' => 'Infrastructure, Hardware & Automation Integrator',
                        'desc' => 'Integrasi sensor telemetri nirkabel (LoRaWAN/IoT), monitoring steker listrik reefer (300 plugs), dan pemantauan suhu komoditas rantai dingin sedang dikoordinasikan dengan tim infrastruktur.',
                        'icon' => 'fa-snowflake',
                        'status' => 'Menunggu Spesifikasi Telemetri IoT dari Arman'
                    ],
                    'billing' => [
                        'pic' => 'Muhammad Daffa Al Hafizh (2344190001)',
                        'role' => 'Business Process & Workflow Analyst',
                        'desc' => 'Alur proses bisnis komersial, struktur tarif jasa terminal (Lo-Lo, Penumpukan, VGM), serta integrasi data finansial ke Sistem ERP sedang dirancang oleh tim analis proses bisnis.',
                        'icon' => 'fa-file-invoice-dollar',
                        'status' => 'Menunggu Perancangan Alur ERP dari Daffa'
                    ],
                    'scanner' => [
                        'pic' => 'Muhammad Bagoes Syahputra (2344190022)',
                        'role' => 'Logistics Data & Standards Officer',
                        'desc' => 'Standar identifikasi logistik global (GS1-128, SSCC-18, format e-Labeling, dan skema konektivitas data API) sedang disusun oleh tim standar data logistik.',
                        'icon' => 'fa-barcode',
                        'status' => 'Menunggu Standar Data GS1 & API dari Bagoes'
                    ],
                    'customs' => [
                        'pic' => 'Tim Kepabeanan & Konsultan Pabean CIDP',
                        'role' => 'Customs & Inland Port Clearance Specialist',
                        'desc' => 'Integrasi sistem CEISA 4.0 DJBC untuk rilis dokumen SPPB, penetapan jalur pabean (Merah/Kuning/Hijau), pemeriksaan fisik behandle, dan segel elektronik kontainer.',
                        'icon' => 'fa-shield-halved',
                        'status' => 'Proyeksi Kawasan Pabean Mandiri'
                    ],
                    'simulator' => [
                        'pic' => 'Zulfikar Jafarudin Fatah & Muhammad Arman',
                        'role' => 'Virtual IoT & Hardware Simulation Specialist',
                        'desc' => 'Pusat simulasi skenario terminal dry port (simulasi kedatangan truk di gerbang, simulasi pergerakan Reach Stacker, dan simulasi jadwal rangkaian KA Logistik).',
                        'icon' => 'fa-gamepad',
                        'status' => 'Proyeksi Sandbox Simulasi Virtual IoT'
                    ],
                    'settings' => [
                        'pic' => 'Zulfikar Jafarudin Fatah (2344190003)',
                        'role' => 'Lead Project & System Architect',
                        'desc' => 'Konfigurasi parameter global sistem YMS, manajemen koneksi basis data, dan kontrol hak akses konsultan.',
                        'icon' => 'fa-gear',
                        'status' => 'Konfigurasi Sistem Utama'
                    ]
                ];

                $cur_info = $divisi_info[$page] ?? [
                    'pic' => 'Tim Proyek Conclusion',
                    'role' => 'Divisi Terkait',
                    'desc' => 'Halaman ini sedang dalam tahap koordinasi bersama rekan tim.',
                    'icon' => 'fa-person-digging',
                    'status' => 'Dalam Koordinasi Tim'
                ];
                ?>

                <!-- Kartu Status Modul: Menunggu Input Rekan Tim -->
                <div class="bg-white rounded-2xl p-8 sm:p-12 shadow-sm border border-gray-100 text-center flex flex-col items-center justify-center max-w-2xl mx-auto my-8 animate-fadeIn">
                    <div class="w-20 h-20 bg-blue-50 text-[#0170b9] rounded-2xl flex items-center justify-center mb-5 shadow-inner text-3xl">
                        <i class="fa-solid <?= $cur_info['icon'] ?>"></i>
                    </div>
                    
                    <span class="px-3.5 py-1.5 bg-amber-50 text-amber-800 border border-amber-200 rounded-full text-xs font-bold tracking-wide mb-3 flex items-center shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-amber-500 mr-2 animate-pulse"></span><?= $cur_info['status'] ?>
                    </span>

                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Modul <?= isset($menu_items[$page]) ? $menu_items[$page]['label'] : 'Tidak Dikenal' ?></h2>
                    
                    <p class="text-gray-600 text-xs sm:text-sm leading-relaxed mb-6 max-w-lg">
                        <?= $cur_info['desc'] ?>
                    </p>

                    <!-- Kartu PIC Anggota Tim Penanggung Jawab -->
                    <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 text-left w-full max-w-md mb-8 flex items-center space-x-3.5">
                        <div class="w-11 h-11 rounded-full bg-[#002f5e] text-white flex items-center justify-center font-bold text-base flex-shrink-0 shadow-xs">
                            <i class="fa-solid fa-user-pen"></i>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider">Penanggung Jawab Divisi:</span>
                            <p class="font-bold text-gray-900 text-xs sm:text-sm mt-0.5"><?= $cur_info['pic'] ?></p>
                            <span class="text-[11px] text-[#0170b9] font-semibold block"><?= $cur_info['role'] ?></span>
                        </div>
                    </div>

                    <a href="dashboard.php?page=beranda" class="px-6 py-2.5 bg-[#0170b9] hover:bg-[#004b87] text-white text-xs font-bold rounded-lg transition-colors shadow-sm inline-flex items-center space-x-2">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Kembali ke Beranda Eksekutif</span>
                    </a>
                </div>

                <?php endif; ?>

            </div>
            
            <!-- Footer -->
            <footer class="text-center p-6 text-sm text-gray-400 border-t border-gray-200/60 mt-auto bg-transparent">
                © 2026 Conclusion Supply Chain Consultant • ITL Trisakti • Topik 7: ICD & Dry Port Management
            </footer>
        </main>
    </div>

    <!-- Script untuk interaksi Sidebar -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const logoTexts = document.querySelectorAll('.logo-text');
        const desktopToggleIcon = document.getElementById('desktopToggleIcon');
        
        let isSidebarOpenMobile = false;
        let isSidebarCollapsedDesktop = false;

        function toggleSidebar() {
            isSidebarOpenMobile = !isSidebarOpenMobile;
            
            if (isSidebarOpenMobile) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        function toggleDesktopSidebar() {
            isSidebarCollapsedDesktop = !isSidebarCollapsedDesktop;
            
            if (isSidebarCollapsedDesktop) {
                sidebar.classList.replace('w-64', 'w-20');
                logoTexts.forEach(el => el.classList.add('hidden'));
                desktopToggleIcon.classList.replace('fa-bars-staggered', 'fa-bars');
            } else {
                sidebar.classList.replace('w-20', 'w-64');
                desktopToggleIcon.classList.replace('fa-bars', 'fa-bars-staggered');
                setTimeout(() => {
                    logoTexts.forEach(el => el.classList.remove('hidden'));
                }, 200);
            }
        }
    </script>
    
    <!-- Chart.js Initializations -->
    <?php if ($page === 'beranda'): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Chart.defaults.font.family = '"Plus Jakarta Sans", sans-serif';
            Chart.defaults.color = '#6b7280';
            Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0, 47, 94, 0.9)';
            Chart.defaults.plugins.tooltip.padding = 10;
            Chart.defaults.plugins.tooltip.cornerRadius = 8;
            
            // 1. Line/Area Chart: Tren Aktivitas Gate
            const gateCtx = document.getElementById('gateTrendChart');
            if (gateCtx) {
                new Chart(gateCtx, {
                    type: 'line',
                    data: {
                        labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                        datasets: [
                            {
                                label: 'Gate-In',
                                data: [18, 22, 19, 24, 25, 15, 12],
                                borderColor: '#0170b9', // cdp-blue
                                backgroundColor: 'rgba(1, 112, 185, 0.1)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2,
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: '#0170b9',
                                pointBorderWidth: 2,
                                pointRadius: 4
                            },
                            {
                                label: 'Gate-Out',
                                data: [15, 20, 18, 21, 23, 14, 10],
                                borderColor: '#4f46e5', // indigo-600
                                backgroundColor: 'transparent',
                                fill: false,
                                tension: 0.4,
                                borderWidth: 2,
                                borderDash: [5, 5],
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: '#4f46e5',
                                pointBorderWidth: 2,
                                pointRadius: 4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { usePointStyle: true, boxWidth: 8 }
                            }
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { borderDash: [2, 4], color: '#f3f4f6' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // 2. Doughnut Chart: Distribusi Tipe Kontainer
            const typeCtx = document.getElementById('containerTypeChart');
            if (typeCtx) {
                new Chart(typeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Dry Container', 'Reefer', 'Empty/Depot', 'DG/IMO'],
                        datasets: [{
                            data: [55, 25, 15, 5],
                            backgroundColor: ['#0170b9', '#06b6d4', '#9ca3af', '#ef4444'],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { usePointStyle: true, boxWidth: 8 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.label + ': ' + context.raw + '%';
                                    }
                                }
                            }
                        }
                    },
                    plugins: [{
                        id: 'textCenter',
                        beforeDraw: function(chart) {
                            const { ctx, chartArea } = chart;
                            if (!chartArea) return;
                            ctx.save();
                            const centerX = (chartArea.left + chartArea.right) / 2;
                            const centerY = (chartArea.top + chartArea.bottom) / 2;

                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';

                            // Main count
                            ctx.font = "bold 1.2rem 'Plus Jakarta Sans', sans-serif";
                            ctx.fillStyle = "#111827";
                            ctx.fillText("126 Unit", centerX, centerY - 6);

                            // Subtitle
                            ctx.font = "600 0.65rem 'Plus Jakarta Sans', sans-serif";
                            ctx.fillStyle = "#6b7280";
                            ctx.fillText("Total Yard", centerX, centerY + 12);
                            ctx.restore();
                        }
                    }]
                });
            }

            // 3. Vertical Bar Chart: Throughput per Blok Yard
            const yardCtx = document.getElementById('yardBlockChart');
            if (yardCtx) {
                new Chart(yardCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Blok A', 'Blok B', 'Blok C', 'Blok D', 'Blok E'],
                        datasets: [
                            {
                                label: 'Masuk',
                                data: [45, 32, 28, 15, 10],
                                backgroundColor: '#0170b9',
                                borderRadius: 4
                            },
                            {
                                label: 'Keluar',
                                data: [38, 28, 20, 12, 8],
                                backgroundColor: '#4f46e5',
                                borderRadius: 4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { usePointStyle: true, boxWidth: 8 }
                            }
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { borderDash: [2, 4], color: '#f3f4f6' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // 4. Horizontal Bar Chart: Top 5 Perusahaan Pelanggan
            const custCtx = document.getElementById('topCustomersChart');
            if (custCtx) {
                new Chart(custCtx, {
                    type: 'bar',
                    data: {
                        labels: ['PT Samudera Log.', 'PT Evergreen', 'Maersk Ind.', 'CMA CGM', 'PT Meratus'],
                        datasets: [{
                            label: 'Volume Kontainer',
                            data: [28, 24, 19, 15, 12],
                            backgroundColor: [
                                '#002f5e', // cdp-dark
                                '#004b87', // cdp-navy
                                '#0170b9', // cdp-blue
                                '#3b82f6', // blue-500
                                '#60a5fa'  // blue-400
                            ],
                            borderRadius: 4
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: { beginAtZero: true, grid: { borderDash: [2, 4], color: '#f3f4f6' } },
                            y: { grid: { display: false } }
                        }
                    }
                });
            }

            // 5. Stacked Bar Chart: Pendapatan per Jenis Layanan
            const revCtx = document.getElementById('revenueStackedChart');
            if (revCtx) {
                new Chart(revCtx, {
                    type: 'bar',
                    data: {
                        labels: ['H-6', 'H-5', 'H-4', 'H-3', 'H-2', 'H-1', 'Hari Ini'],
                        datasets: [
                            {
                                label: 'Lo-Lo',
                                data: [12.5, 14.2, 11.8, 15.6, 16.2, 10.5, 18.4],
                                backgroundColor: '#0170b9', // blue
                                borderRadius: {topLeft: 0, topRight: 0, bottomLeft: 4, bottomRight: 4}
                            },
                            {
                                label: 'Storage Fee',
                                data: [8.2, 9.5, 8.8, 10.2, 11.5, 9.2, 12.6],
                                backgroundColor: '#4f46e5', // indigo
                            },
                            {
                                label: 'VGM Weighing',
                                data: [3.4, 4.1, 3.2, 4.8, 5.2, 2.8, 5.5],
                                backgroundColor: '#a855f7', // purple
                            },
                            {
                                label: 'Reefer Power',
                                data: [5.6, 5.8, 5.5, 6.2, 6.5, 5.2, 7.1],
                                backgroundColor: '#06b6d4', // cyan
                                borderRadius: {topLeft: 4, topRight: 4, bottomLeft: 0, bottomRight: 0}
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { usePointStyle: true, boxWidth: 8 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.dataset.label + ': Rp ' + context.raw + ' Jt';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: { stacked: true, grid: { display: false } },
                            y: { 
                                stacked: true, 
                                beginAtZero: true, 
                                grid: { borderDash: [2, 4], color: '#f3f4f6' },
                                ticks: {
                                    callback: function(value) {
                                        return value + 'Jt';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
