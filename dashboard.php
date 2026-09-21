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

$menu_items = [
    'beranda' => ['icon' => 'fa-home', 'label' => 'Beranda Eksekutif'],
    'kontainer' => ['icon' => 'fa-boxes-stacked', 'label' => 'Pelacakan Kontainer'],
    'trucking' => ['icon' => 'fa-truck-front', 'label' => 'Manajemen Trucking'],
    'alat' => ['icon' => 'fa-dolly', 'label' => 'Lokasi Alat Berat (GPS)'],
    'gate' => ['icon' => 'fa-door-open', 'label' => 'Manajemen Gate'],
    'yard' => ['icon' => 'fa-cubes', 'label' => 'Manajemen Yard'],
    'denah' => ['icon' => 'fa-map-location-dot', 'label' => 'Denah Terminal'],
    'intermodal' => ['icon' => 'fa-train-subway', 'label' => 'Intermodal & KA'],
    'reefer' => ['icon' => 'fa-snowflake', 'label' => 'Monitor Reefer'],
    'billing' => ['icon' => 'fa-file-invoice-dollar', 'label' => 'Billing & Faktur'],
    'scanner' => ['icon' => 'fa-barcode', 'label' => 'Scanner SSCC/GS1'],
    'settings' => ['icon' => 'fa-gear', 'label' => 'Pengaturan']
];

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
<body class="text-gray-800 antialiased h-screen overflow-hidden flex">

    <!-- Sidebar -->
    <aside id="sidebar" class="bg-cdp-dark text-white flex-shrink-0 z-20 h-full overflow-y-auto sidebar-transition w-64 absolute md:relative transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
        <!-- Logo -->
        <div class="h-16 flex items-center px-4 border-b border-white/10 bg-cdp-navy/50">
            <img src="assets/img/logo.png" alt="Logo" class="w-8 h-8 object-contain mr-3 bg-white rounded p-0.5" onerror="this.src='https://via.placeholder.com/32?text=C'">
            <span class="text-xl font-bold tracking-wide logo-text whitespace-nowrap">CIDP YMS</span>
        </div>
        
        <!-- Navigation -->
        <nav class="p-4 space-y-1">
            <div class="text-xs font-semibold text-white/50 uppercase tracking-wider mb-3 mt-2 logo-text whitespace-nowrap">Menu Utama</div>
            
            <?php foreach ($menu_items as $key => $item): ?>
                <?php if (can_view($key, $role)): ?>
                    <a href="dashboard.php?page=<?= $key ?>" 
                       class="flex items-center px-3 py-2.5 rounded-lg transition-colors group <?= $page === $key ? 'bg-cdp-blue text-white shadow-md' : 'text-gray-300 hover:bg-white/10 hover:text-white' ?>"
                       title="<?= $item['label'] ?>">
                        <i class="fa-solid <?= $item['icon'] ?> w-6 text-center <?= $page === $key ? 'text-white' : 'text-gray-400 group-hover:text-white' ?>"></i>
                        <span class="ml-3 font-medium logo-text whitespace-nowrap"><?= $item['label'] ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
    </aside>

    <!-- Main Wrapper -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
        
        <!-- Overlay for mobile sidebar -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-gray-900/50 z-10 hidden md:hidden transition-opacity" onclick="toggleSidebar()"></div>

        <!-- Top Header -->
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 lg:px-8 z-10 flex-shrink-0">
            <!-- Left: Toggle & Page Title -->
            <div class="flex items-center">
                <!-- Mobile Toggle -->
                <button onclick="toggleSidebar()" class="mr-4 text-gray-500 hover:text-cdp-blue focus:outline-none md:hidden transition-colors">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <!-- Desktop Toggle -->
                <button onclick="toggleDesktopSidebar()" class="mr-4 text-gray-500 hover:text-cdp-blue focus:outline-none hidden md:block transition-colors">
                    <i class="fa-solid fa-bars-staggered text-xl" id="desktopToggleIcon"></i>
                </button>
                <h1 class="text-xl font-bold text-gray-800 capitalize">
                    <?= isset($menu_items[$page]) ? $menu_items[$page]['label'] : 'Dashboard' ?>
                </h1>
            </div>
            
            <!-- Right: User & Actions -->
            <div class="flex items-center space-x-4">
                <button class="relative p-2 text-gray-400 hover:text-cdp-blue transition-colors">
                    <i class="fa-regular fa-bell text-xl"></i>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
                </button>
                
                <div class="h-8 w-px bg-gray-200 mx-2"></div>
                
                <div class="flex items-center space-x-3 group">
                    <div class="flex flex-col text-right hidden sm:flex">
                        <span class="text-sm font-bold text-gray-800"><?= htmlspecialchars($nama) ?></span>
                        <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded border <?= $current_badge['color'] ?> inline-block mt-0.5">
                            <?= $current_badge['label'] ?>
                        </span>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-cdp-blue text-white flex items-center justify-center font-bold shadow-sm border-2 border-white ring-2 ring-gray-100 group-hover:ring-cdp-blue/30 transition-all cursor-pointer">
                        <?= strtoupper(substr($nama, 0, 1)) ?>
                    </div>
                </div>
                
                <a href="logout.php" class="ml-2 p-2 text-gray-400 hover:text-red-500 transition-colors" title="Keluar" onclick="return confirm('Apakah Anda yakin ingin keluar?');">
                    <i class="fa-solid fa-right-from-bracket text-lg"></i>
                </a>
            </div>
        </header>

        <!-- Main Content Scrollable Area -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-cdp-light relative">
            <div class="p-4 lg:p-8 max-w-7xl mx-auto space-y-6">
                
                <?php if ($page === 'beranda'): ?>
                
                <!-- Welcome Banner -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-all hover:shadow-md">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Selamat datang, <?= htmlspecialchars($nama) ?>! 👋</h2>
                        <p class="text-gray-500 mt-1"><?= $current_desc ?></p>
                    </div>
                    <div class="text-right text-sm text-gray-500 bg-gray-50 px-4 py-2 rounded-lg border border-gray-100 flex items-center shadow-inner">
                        <i class="fa-regular fa-calendar mr-2 text-cdp-blue"></i>
                        <?= $tanggal_sekarang ?>
                    </div>
                </div>

                <!-- 6 KPI Summary Cards (Konsol Penuh) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    
                    <!-- KPI 1 -->
                    <div class="bg-white rounded-xl p-5 shadow-sm border-t-4 border-t-blue-500 hover:shadow-md transition-shadow relative overflow-hidden group">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-1">Kontainer di Yard</p>
                                <h3 class="text-3xl font-bold text-gray-800">126 <span class="text-sm font-normal text-gray-400">unit</span></h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-boxes-stacked text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <span class="text-green-500 font-medium flex items-center"><i class="fa-solid fa-arrow-trend-up mr-1 text-xs"></i> +8</span>
                            <span class="text-gray-400 ml-2">dari kemarin</span>
                        </div>
                    </div>

                    <!-- KPI 2 -->
                    <div class="bg-white rounded-xl p-5 shadow-sm border-t-4 border-t-indigo-500 hover:shadow-md transition-shadow relative overflow-hidden group">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-1">Truk Hari Ini</p>
                                <h3 class="text-3xl font-bold text-gray-800">14 <span class="text-sm font-normal text-gray-400">kendaraan</span></h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-truck text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm text-gray-500 space-x-4">
                            <span class="flex items-center"><i class="fa-solid fa-right-to-bracket text-indigo-400 mr-1.5"></i> In: 9</span>
                            <span class="flex items-center"><i class="fa-solid fa-right-from-bracket text-gray-400 mr-1.5"></i> Out: 5</span>
                        </div>
                    </div>

                    <!-- KPI 3 -->
                    <div class="bg-white rounded-xl p-5 shadow-sm border-t-4 border-t-purple-500 hover:shadow-md transition-shadow relative overflow-hidden group">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-1">Kereta Api</p>
                                <h3 class="text-3xl font-bold text-gray-800">2 <span class="text-sm font-normal text-gray-400">rangkaian</span></h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-purple-50 text-purple-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-train text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm text-gray-500">
                            <span class="truncate">Jadwal hari ini (JKT-SMG)</span>
                        </div>
                    </div>

                    <!-- KPI 4 -->
                    <div class="bg-white rounded-xl p-5 shadow-sm border-t-4 border-t-cyan-500 hover:shadow-md transition-shadow relative overflow-hidden group">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-1">Reefer Aktif</p>
                                <h3 class="text-3xl font-bold text-gray-800">38 <span class="text-sm font-normal text-gray-400">unit</span></h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-cyan-50 text-cyan-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-snowflake text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <span class="text-green-500 font-medium flex items-center"><i class="fa-solid fa-check-circle mr-1 text-xs"></i> Suhu normal semua</span>
                        </div>
                    </div>

                    <!-- KPI 5 -->
                    <div class="bg-white rounded-xl p-5 shadow-sm border-t-4 border-t-red-500 hover:shadow-md transition-shadow relative overflow-hidden group">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-1">DG / IMO</p>
                                <h3 class="text-3xl font-bold text-gray-800">3 <span class="text-sm font-normal text-gray-400">kontainer</span></h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-red-50 text-red-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm text-gray-500">
                            <span>Status berbahaya (Terisolasi)</span>
                        </div>
                    </div>

                    <!-- KPI 6 -->
                    <div class="bg-white rounded-xl p-5 shadow-sm border-t-4 border-t-emerald-500 hover:shadow-md transition-shadow relative overflow-hidden group">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-1">Pendapatan Hari Ini</p>
                                <h3 class="text-2xl font-bold text-gray-800">Rp 47.25M</h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-money-bill-wave text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm text-gray-500">
                            <span>Estimasi gross revenue harian</span>
                        </div>
                    </div>

                </div>

                <!-- Row 1: 2 Charts Side-by-side -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Line Chart -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-gray-800 text-lg"><i class="fa-solid fa-chart-line mr-2 text-cdp-blue"></i>Tren Aktivitas Gate (7 Hari Terakhir)</h3>
                        </div>
                        <div class="h-64 relative w-full">
                            <canvas id="gateTrendChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Doughnut Chart -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-gray-800 text-lg"><i class="fa-solid fa-chart-pie mr-2 text-cdp-blue"></i>Distribusi Tipe Kontainer</h3>
                        </div>
                        <div class="h-64 relative w-full flex items-center justify-center">
                            <canvas id="containerTypeChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Row 2: 3 Columns -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Vertical Bar Chart -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-gray-800 text-lg"><i class="fa-solid fa-chart-column mr-2 text-cdp-blue"></i>Throughput per Blok Yard</h3>
                        </div>
                        <div class="h-64 relative w-full">
                            <canvas id="yardBlockChart"></canvas>
                        </div>
                    </div>

                    <!-- Gauges -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col justify-around">
                        <!-- Gauge 1 -->
                        <div class="text-center mb-6">
                            <h4 class="font-bold text-gray-700 text-sm mb-2">Utilisasi Yard</h4>
                            <div class="relative w-48 h-24 mx-auto overflow-hidden">
                                <svg viewBox="0 0 200 100" class="w-full h-full">
                                    <path d="M 20 100 A 80 80 0 0 1 180 100" fill="none" stroke="#e5e7eb" stroke-width="16" stroke-linecap="round"/>
                                    <path d="M 20 100 A 80 80 0 0 1 180 100" fill="none" stroke="#eab308" stroke-width="16" stroke-linecap="round" stroke-dasharray="251.2" stroke-dashoffset="92.9"/>
                                </svg>
                                <div class="absolute bottom-0 w-full text-center">
                                    <span class="text-3xl font-bold text-gray-800">63%</span>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">126 dari 200 slot</p>
                        </div>
                        
                        <!-- Gauge 2 -->
                        <div class="text-center">
                            <h4 class="font-bold text-gray-700 text-sm mb-2">Rata-rata Dwell Time</h4>
                            <div class="relative w-48 h-24 mx-auto overflow-hidden">
                                <svg viewBox="0 0 200 100" class="w-full h-full">
                                    <path d="M 20 100 A 80 80 0 0 1 180 100" fill="none" stroke="#e5e7eb" stroke-width="16" stroke-linecap="round"/>
                                    <path d="M 20 100 A 80 80 0 0 1 180 100" fill="none" stroke="#22c55e" stroke-width="16" stroke-linecap="round" stroke-dasharray="251.2" stroke-dashoffset="75.4"/>
                                </svg>
                                <div class="absolute bottom-0 w-full text-center">
                                    <span class="text-3xl font-bold text-gray-800">2.1</span>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Target < 3.0 hari</p>
                        </div>
                    </div>

                    <!-- Horizontal Bar Chart -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-gray-800 text-lg"><i class="fa-solid fa-list-ol mr-2 text-cdp-blue"></i>Top 5 Perusahaan Pelanggan</h3>
                        </div>
                        <div class="h-64 relative w-full">
                            <canvas id="topCustomersChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Row 3: 2 Charts (Grid Cols 5) -->
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                    <!-- Stacked Bar Chart -->
                    <div class="lg:col-span-3 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-gray-800 text-lg"><i class="fa-solid fa-sack-dollar mr-2 text-cdp-blue"></i>Pendapatan per Jenis Layanan (7 Hari)</h3>
                        </div>
                        <div class="h-64 relative w-full">
                            <canvas id="revenueStackedChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Summary Stats Card -->
                    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col justify-center space-y-5">
                        <h3 class="font-bold text-gray-800 text-lg border-b border-gray-100 pb-2"><i class="fa-solid fa-gauge-high mr-2 text-cdp-blue"></i>Performa Terminal</h3>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center">
                                    <i class="fa-solid fa-stopwatch text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Rata-rata Waktu Gate-In</p>
                                    <p class="text-lg font-bold text-gray-800">2 mnt 15 dtk</p>
                                </div>
                            </div>
                            <span class="text-green-500 text-xs font-bold bg-green-50 px-2 py-1 rounded-full"><i class="fa-solid fa-arrow-down mr-1"></i>12s</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center">
                                    <i class="fa-solid fa-truck-ramp-box text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Truk Ditangani (Bulan Ini)</p>
                                    <p class="text-lg font-bold text-gray-800">342 <span class="text-sm font-normal text-gray-400">kendaraan</span></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-purple-50 text-purple-500 flex items-center justify-center">
                                    <i class="fa-solid fa-file-invoice text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Invoice Terbit (Bulan Ini)</p>
                                    <p class="text-lg font-bold text-gray-800">289 <span class="text-sm font-normal text-gray-400">faktur</span></p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center">
                                    <i class="fa-solid fa-money-check-dollar text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Total Revenue (Bulan Ini)</p>
                                    <p class="text-lg font-bold text-gray-800">Rp 1.42 M</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-green-50 text-green-500 flex items-center justify-center">
                                    <i class="fa-solid fa-shield-halved text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Tingkat Kecelakaan</p>
                                    <p class="text-lg font-bold text-gray-800">0 <span class="text-sm font-normal text-gray-400">insiden</span></p>
                                </div>
                            </div>
                            <span class="text-green-600 text-xs font-bold bg-green-100 px-2 py-1 rounded"><i class="fa-solid fa-check mr-1"></i>Aman</span>
                        </div>
                    </div>
                </div>

                <!-- Two-Column Layout: Aktivitas Terkini & Antrian Gerbang -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Left Col: Aktivitas Terkini (span 2) -->
                    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col">
                        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50 rounded-t-xl">
                            <h3 class="font-bold text-gray-800 text-lg"><i class="fa-solid fa-clock-rotate-left mr-2 text-cdp-blue"></i>Aktivitas Terkini</h3>
                            <button class="text-sm text-cdp-blue hover:underline font-medium">Lihat Semua</button>
                        </div>
                        <div class="p-5 flex-1">
                            <div class="relative border-l-2 border-gray-100 ml-3 space-y-7">
                                
                                <!-- Event 1 -->
                                <div class="relative pl-6">
                                    <div class="timeline-dot bg-blue-500 ring-4 ring-white"></div>
                                    <div class="flex justify-between items-start mb-1">
                                        <div class="font-semibold text-gray-800 text-sm">Gate-In Truk <span class="text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded text-xs border border-blue-100">B 9481 UEK</span></div>
                                        <div class="text-xs text-gray-400 font-medium bg-gray-50 px-2 py-1 rounded">14:32</div>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">MSKU9182374 | 40ft HC</p>
                                </div>

                                <!-- Event 2 -->
                                <div class="relative pl-6">
                                    <div class="timeline-dot bg-orange-500 ring-4 ring-white"></div>
                                    <div class="flex justify-between items-start mb-1">
                                        <div class="font-semibold text-gray-800 text-sm">Lift-Off RS-03 <i class="fa-solid fa-arrow-right text-gray-300 mx-1"></i> BLOCK-B Bay-08 Row-03 Tier-02</div>
                                        <div class="text-xs text-gray-400 font-medium bg-gray-50 px-2 py-1 rounded">14:28</div>
                                    </div>
                                </div>

                                <!-- Event 3 -->
                                <div class="relative pl-6">
                                    <div class="timeline-dot bg-red-500 ring-4 ring-white animate-pulse"></div>
                                    <div class="flex justify-between items-start mb-1">
                                        <div class="font-semibold text-gray-800 text-sm">Reefer Alert <span class="text-red-600 bg-red-50 px-1.5 py-0.5 rounded text-xs border border-red-100">TEMU4819203</span></div>
                                        <div class="text-xs text-red-500 font-bold bg-red-50 px-2 py-1 rounded">14:15</div>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">Suhu -18.2°C (Batas -20°C)</p>
                                </div>

                                <!-- Event 4 -->
                                <div class="relative pl-6">
                                    <div class="timeline-dot bg-blue-500 ring-4 ring-white"></div>
                                    <div class="flex justify-between items-start mb-1">
                                        <div class="font-semibold text-gray-800 text-sm">Gate-Out Truk <span class="text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded text-xs border border-blue-100">B 7712 SCK</span></div>
                                        <div class="text-xs text-gray-400 font-medium bg-gray-50 px-2 py-1 rounded">13:55</div>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">TCLU8827415 | Invoice #INV-0089</p>
                                </div>

                                <!-- Event 5 -->
                                <div class="relative pl-6">
                                    <div class="timeline-dot bg-purple-500 ring-4 ring-white"></div>
                                    <div class="flex justify-between items-start mb-1">
                                        <div class="font-semibold text-gray-800 text-sm">Kereta KA-LOG-JKT-SMG tiba di Rail Siding</div>
                                        <div class="text-xs text-gray-400 font-medium bg-gray-50 px-2 py-1 rounded">13:42</div>
                                    </div>
                                </div>
                                
                                <!-- Event 6 -->
                                <div class="relative pl-6">
                                    <div class="timeline-dot bg-orange-500 ring-4 ring-white"></div>
                                    <div class="flex justify-between items-start mb-1">
                                        <div class="font-semibold text-gray-800 text-sm">Lift-On RS-01 <i class="fa-solid fa-arrow-right text-gray-300 mx-1"></i> Loading gerbong 4</div>
                                        <div class="text-xs text-gray-400 font-medium bg-gray-50 px-2 py-1 rounded">13:30</div>
                                    </div>
                                </div>

                                <!-- Event 7 -->
                                <div class="relative pl-6">
                                    <div class="timeline-dot bg-blue-500 ring-4 ring-white"></div>
                                    <div class="flex justify-between items-start mb-1">
                                        <div class="font-semibold text-gray-800 text-sm">Gate-In Truk <span class="text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded text-xs border border-blue-100">B 1234 XYZ</span></div>
                                        <div class="text-xs text-gray-400 font-medium bg-gray-50 px-2 py-1 rounded">13:15</div>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">CSQU3054821 | 20ft Dry</p>
                                </div>

                                <!-- Event 8 -->
                                <div class="relative pl-6">
                                    <div class="timeline-dot bg-green-500 ring-4 ring-white"></div>
                                    <div class="flex justify-between items-start mb-1">
                                        <div class="font-semibold text-gray-800 text-sm">VGM Weighing <span class="text-green-600 bg-green-50 px-1.5 py-0.5 rounded text-xs border border-green-100">MSKU9182374</span></div>
                                        <div class="text-xs text-gray-400 font-medium bg-gray-50 px-2 py-1 rounded">12:58</div>
                                    </div>
                                    <p class="text-sm text-green-600 font-medium mt-1">26.850 kg <i class="fa-solid fa-check ml-1"></i> Verified</p>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Right Col: Panel Ringkasan -->
                    <div class="flex flex-col space-y-6">
                        
                        <!-- Antrian Gate -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                            <h3 class="font-bold text-gray-800 text-lg mb-5 border-b border-gray-100 pb-2"><i class="fa-solid fa-truck-fast mr-2 text-cdp-blue"></i>Antrian Gate</h3>
                            
                            <div class="space-y-5">
                                <div>
                                    <div class="flex justify-between text-sm mb-1.5">
                                        <span class="text-gray-600 font-medium">Gate-In (3 truk)</span>
                                        <span class="text-gray-800 font-bold">60%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2">
                                        <div class="bg-indigo-500 h-2 rounded-full" style="width: 60%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-sm mb-1.5">
                                        <span class="text-gray-600 font-medium">Gate-Out (1 truk)</span>
                                        <span class="text-gray-800 font-bold">20%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2">
                                        <div class="bg-blue-400 h-2 rounded-full" style="width: 20%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Okupansi Yard -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex-1">
                            <div class="border-b border-gray-100 pb-2 mb-5 flex justify-between items-center">
                                <h3 class="font-bold text-gray-800 text-lg"><i class="fa-solid fa-chart-pie mr-2 text-cdp-blue"></i>Okupansi Yard</h3>
                                <span class="text-xs font-semibold text-gray-600 bg-gray-100 px-2 py-1 rounded">126 / 200 slot terisi</span>
                            </div>
                            
                            <div class="space-y-4">
                                <!-- Block A -->
                                <div>
                                    <div class="flex justify-between text-xs mb-1.5">
                                        <span class="text-gray-600 font-medium">Block A</span>
                                        <span class="text-gray-800 font-bold">78%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: 78%"></div>
                                    </div>
                                </div>
                                <!-- Block B -->
                                <div>
                                    <div class="flex justify-between text-xs mb-1.5">
                                        <span class="text-gray-600 font-medium">Block B</span>
                                        <span class="text-gray-800 font-bold">62%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                                        <div class="bg-blue-400 h-1.5 rounded-full" style="width: 62%"></div>
                                    </div>
                                </div>
                                <!-- Block C -->
                                <div>
                                    <div class="flex justify-between text-xs mb-1.5">
                                        <span class="text-gray-600 font-medium">Block C</span>
                                        <span class="text-gray-800 font-bold">45%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                                        <div class="bg-gray-400 h-1.5 rounded-full" style="width: 45%"></div>
                                    </div>
                                </div>
                                <!-- Reefer Zone -->
                                <div>
                                    <div class="flex justify-between text-xs mb-1.5">
                                        <span class="text-gray-600 font-medium">Reefer Zone</span>
                                        <span class="text-red-500 font-bold">83%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                                        <div class="bg-cyan-400 h-1.5 rounded-full" style="width: 83%"></div>
                                    </div>
                                </div>
                                <!-- DG Yard -->
                                <div>
                                    <div class="flex justify-between text-xs mb-1.5">
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

                <!-- Tabel Pelacakan Muatan Klien (Shipper Consignment Tracking) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-100">
                        <h3 class="font-bold text-gray-800 text-lg flex items-center">
                            <i class="fa-solid fa-list-check mr-2 text-cdp-blue"></i>Pelacakan Konsinyasi Muatan Klien (Shipper Consignment Tracking)
                        </h3>
                        <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded border border-emerald-200">
                            <i class="fa-solid fa-satellite-dish mr-1"></i>Live Tracking
                        </span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 text-xs border-y border-gray-100">
                                    <th class="py-3 px-4 font-semibold">Nomor Kontainer</th>
                                    <th class="py-3 px-4 font-semibold">Tipe & Ukuran</th>
                                    <th class="py-3 px-4 font-semibold">Tag RFID UHF</th>
                                    <th class="py-3 px-4 font-semibold">Status Muatan</th>
                                    <th class="py-3 px-4 font-semibold">Posisi di Lapangan</th>
                                    <th class="py-3 px-4 font-semibold text-right">Pembaruan Terakhir</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs divide-y divide-gray-100">
                                <tr class="hover:bg-blue-50/20 transition">
                                    <td class="py-3 px-4 font-mono font-bold text-[#0170b9]">MSKU9182374</td>
                                    <td class="py-3 px-4 text-gray-600">40ft High Cube (Dry)</td>
                                    <td class="py-3 px-4 font-mono text-purple-700 font-bold">E280117000000001</td>
                                    <td class="py-3 px-4"><span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-[10px] font-bold">In Yard (Penumpukan)</span></td>
                                    <td class="py-3 px-4 font-semibold text-gray-800">Blok B / Bay 08 / Row 03 / Tier 02</td>
                                    <td class="py-3 px-4 text-gray-500 text-right">Hari ini, 14:32 WIB</td>
                                </tr>
                                <tr class="hover:bg-blue-50/20 transition">
                                    <td class="py-3 px-4 font-mono font-bold text-[#0170b9]">TCLU8827415</td>
                                    <td class="py-3 px-4 text-gray-600">20ft Standard (Dry)</td>
                                    <td class="py-3 px-4 font-mono text-purple-700 font-bold">E280117000000002</td>
                                    <td class="py-3 px-4"><span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-[10px] font-bold">Gate Out (Menuju Pelabuhan)</span></td>
                                    <td class="py-3 px-4 text-gray-600">Dalam Armada Truk B 7712 SCK</td>
                                    <td class="py-3 px-4 text-gray-500 text-right">Hari ini, 13:55 WIB</td>
                                </tr>
                                <tr class="hover:bg-blue-50/20 transition">
                                    <td class="py-3 px-4 font-mono font-bold text-[#0170b9]">TEMU4819203</td>
                                    <td class="py-3 px-4 text-gray-600">40ft Reefer Cold Chain</td>
                                    <td class="py-3 px-4 font-mono text-purple-700 font-bold">E280117000000004</td>
                                    <td class="py-3 px-4"><span class="bg-cyan-100 text-cyan-700 px-2 py-0.5 rounded text-[10px] font-bold">Terkoneksi Listrik (-18.2°C)</span></td>
                                    <td class="py-3 px-4 font-semibold text-cyan-700">Reefer Rack R-02 Plug #14</td>
                                    <td class="py-3 px-4 text-gray-500 text-right">Kemarin, 16:40 WIB</td>
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
                            var width = chart.width,
                                height = chart.height,
                                ctx = chart.ctx;

                            ctx.restore();
                            var fontSize = (height / 120).toFixed(2);
                            ctx.font = "bold " + fontSize + "em 'Plus Jakarta Sans'";
                            ctx.textBaseline = "middle";
                            ctx.fillStyle = "#1f2937";

                            var text = "126 Unit",
                                textX = Math.round((width - ctx.measureText(text).width) / 2),
                                textY = height / 2.2;

                            ctx.fillText(text, textX, textY);
                            ctx.save();
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
