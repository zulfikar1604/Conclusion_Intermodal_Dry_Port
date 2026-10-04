<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek sesi login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/connection.php';
$db_stats = [
    'containers_in_yard' => 0,
    'total_containers' => 0,
    'total_gate_trucks' => 0,
    'loaded_wagons' => 0,
    'total_wagons' => 0,
    'reefer_count' => 0,
    'customs_total' => 0,
    'customs_cleared' => 0,
    'customs_pct' => 100.0,
    'cfs_active_jobs' => 0,
    'billing_paid' => 0,
    'blocks' => ['A' => 0, 'B' => 0, 'C' => 0, 'REEFER' => 0, 'DG' => 0],
    'recent_events' => [],
    'recent_containers' => []
];
try {
    $c_stmt = $pdo->query("SELECT COUNT(*) FROM containers WHERE status = 'in_yard'");
    $db_stats['containers_in_yard'] = (int)$c_stmt->fetchColumn();

    $tot_c_stmt = $pdo->query("SELECT COUNT(*) FROM containers");
    $db_stats['total_containers'] = (int)$tot_c_stmt->fetchColumn();

    $t_stmt = $pdo->query("SELECT COUNT(*) FROM trucks WHERE status != 'gate_out'");
    $db_stats['total_gate_trucks'] = (int)$t_stmt->fetchColumn();

    $w_stmt = $pdo->query("SELECT SUM(loaded_wagons), SUM(total_wagons) FROM trains");
    $row = $w_stmt->fetch(PDO::FETCH_NUM);
    if ($row) {
        $db_stats['loaded_wagons'] = (int)$row[0];
        $db_stats['total_wagons'] = (int)$row[1];
    }

    // Reefer containers count
    $rf_stmt = $pdo->query("SELECT COUNT(*) FROM containers WHERE cargo_type LIKE '%reefer%' OR size_type LIKE '%R%' OR block = 'R' OR block = 'REEFER'");
    $db_stats['reefer_count'] = (int)$rf_stmt->fetchColumn();

    // Customs clearance stats
    $db_stats['customs_total'] = $db_stats['total_containers'];
    $cust_clr_stmt = $pdo->query("SELECT COUNT(*) FROM containers WHERE customs_status = 'SPPB_CLEARED'");
    $db_stats['customs_cleared'] = (int)$cust_clr_stmt->fetchColumn();
    if ($db_stats['customs_total'] > 0) {
        $db_stats['customs_pct'] = round(($db_stats['customs_cleared'] / $db_stats['customs_total']) * 100, 1);
    }

    // CFS active jobs
    $cfs_stmt = $pdo->query("SELECT COUNT(*) FROM cfs_jobs WHERE status != 'COMPLETED'");
    $db_stats['cfs_active_jobs'] = (int)$cfs_stmt->fetchColumn();

    // Billing revenue paid
    $bill_stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM billing_invoices WHERE payment_status = 'PAID'");
    $db_stats['billing_paid'] = (float)$bill_stmt->fetchColumn();

    // Blocks occupancy
    $blk_stmt = $pdo->query("SELECT UPPER(block) as blk, COUNT(*) as cnt FROM containers GROUP BY block");
    while ($b_row = $blk_stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($b_row['blk'])) {
            $db_stats['blocks'][$b_row['blk']] = (int)$b_row['cnt'];
        }
    }

    // Recent 5 yard events for Live Event Stream
    $ev_stmt = $pdo->query("SELECT * FROM yard_events ORDER BY id DESC LIMIT 5");
    $db_stats['recent_events'] = $ev_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent 5 containers for Consignment Tracking
    $rc_stmt = $pdo->query("SELECT * FROM containers ORDER BY id DESC LIMIT 5");
    $db_stats['recent_containers'] = $rc_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {}


$iduser = $_SESSION['iduser'] ?? 1;
$nama   = (!empty($_SESSION['nama']) && $_SESSION['nama'] !== 'Guest') ? $_SESSION['nama'] : 'Zulfikar Jafarudin Fatah';
$email  = $_SESSION['email'] ?? 'admin@cidp.ac.id';
$role   = 'superadmin'; // Mode Simulasi Satu Pintu: Akses Lengkap

// Pembersihan nama display & inisial avatar eksekutif
$display_nama = trim(preg_replace('/\s*\(.*?\)\s*/', '', $nama));
$name_parts   = explode(' ', $display_nama);
$avatar_initials = strtoupper(substr($name_parts[0], 0, 1) . (count($name_parts) > 1 ? substr(end($name_parts), 0, 1) : substr($name_parts[0], 1, 1)));
if (empty($avatar_initials)) {
    $avatar_initials = 'ZF';
}

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

// Navigation Clusters: 4 Concise English Core Divisions
$menu_clusters = [
    'Executive' => [
        'beranda' => ['icon' => 'fa-house', 'label' => 'Dashboard'],
        'denah' => ['icon' => 'fa-map-location-dot', 'label' => 'Terminal Map']
    ],
    'Operations' => [
        'kontainer' => ['icon' => 'fa-boxes-stacked', 'label' => 'Containers'],
        'trucking' => ['icon' => 'fa-truck-front', 'label' => 'Trucking'],
        'alat' => ['icon' => 'fa-dolly', 'label' => 'Equipment GPS'],
        'gate' => ['icon' => 'fa-door-open', 'label' => 'Gate Control'],
        'yard' => ['icon' => 'fa-cubes', 'label' => 'Yard & Stacking'],
        'cfs' => ['icon' => 'fa-warehouse', 'label' => 'CFS Warehouse'],
        'intermodal' => ['icon' => 'fa-train-subway', 'label' => 'Rail Siding']
    ],
    'Facilities & Customs' => [
        'reefer' => ['icon' => 'fa-snowflake', 'label' => 'Reefer Terminal'],
        'customs' => ['icon' => 'fa-shield-halved', 'label' => 'Customs & CEISA'],
        'scanner' => ['icon' => 'fa-barcode', 'label' => 'GS1 Scanner']
    ],
    'System & Finance' => [
        'billing' => ['icon' => 'fa-file-invoice-dollar', 'label' => 'Billing & Invoicing'],
        'simulator' => ['icon' => 'fa-vr-cardboard', 'label' => 'Simulation Panel'],
        'settings' => ['icon' => 'fa-gear', 'label' => 'Settings']
    ]
];

// Flatten for routing and header titles
$menu_items = [];
foreach ($menu_clusters as $c_name => $items) {
    foreach ($items as $k => $v) {
        $menu_items[$k] = $v;
    }
}

// Active cluster for breadcrumb navigation
$current_cluster = 'Operations';
foreach ($menu_clusters as $c_name => $c_items) {
    if (isset($c_items[$page])) {
        $current_cluster = $c_name;
        break;
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
    <title>Simulation Project Conclusion</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
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

        /* Smooth Dropdown Animation */
        @keyframes fadeInScale {
            from { opacity: 0; transform: translateY(-8px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .animate-fadeIn {
            animation: fadeInScale 0.16s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
    <!-- SheetJS for Excel Export -->
    <script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>
    <!-- jsPDF for PDF Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.2/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.4/jspdf.plugin.autotable.min.js"></script>
    <!-- CIDP Export Utilities -->
    <script src="assets/js/export-utils.js"></script>
</head>
<body class="text-gray-800 antialiased min-h-screen h-[100dvh] overflow-hidden flex bg-cdp-light">

    <!-- Sidebar (Executive Consultant Modern Dark Theme) -->
    <aside id="sidebar" class="bg-gradient-to-b from-[#001d3a] via-[#002f5e] to-[#001830] text-white flex-shrink-0 z-40 h-full overflow-hidden sidebar-transition w-64 fixed md:relative transform -translate-x-full md:translate-x-0 transition-all duration-300 ease-in-out shadow-2xl md:shadow-md flex flex-col justify-between border-r border-white/5 font-sans">
        <!-- Top: Logo Header (Height aligned with top header: h-14 / 56px) -->
        <div class="h-14 flex items-center justify-between px-4 border-b border-white/10 bg-black/20 flex-shrink-0">
            <div class="flex items-center overflow-hidden space-x-3">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#0170b9] to-emerald-400 p-[1.5px] shadow-sm flex-shrink-0">
                    <div class="w-full h-full bg-[#002f5e] rounded-[10px] flex items-center justify-center p-1">
                        <img src="assets/img/logo.png" alt="Logo" class="w-full h-full object-contain" onerror="this.src='https://via.placeholder.com/32?text=C'">
                    </div>
                </div>
                <div class="logo-text min-w-0">
                    <span class="text-sm font-extrabold tracking-tight text-white block leading-tight">CIDP YMS</span>
                    <span class="text-[9.5px] text-blue-200/80 font-mono tracking-wider block uppercase">Hub 35 Ha &bull; v2.4</span>
                </div>
            </div>
            <!-- Mobile Close Button -->
            <button onclick="toggleSidebar()" class="md:hidden text-white/70 hover:text-white p-1.5 rounded-lg focus:outline-none transition-colors" title="Tutup Menu">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        
        <!-- Middle: Navigation (4 Kluster Divisi Operasional Dry Port) -->
        <nav class="flex-1 overflow-y-auto p-3 space-y-4">
            <?php foreach ($menu_clusters as $cluster_title => $items): ?>
                <div class="space-y-1">
                    <div class="text-[10px] font-bold text-blue-300/50 uppercase tracking-widest px-3 mb-1.5 logo-text flex items-center justify-between">
                        <span><?= $cluster_title ?></span>
                    </div>
                    <div class="space-y-0.5">
                        <?php foreach ($items as $key => $item): 
                            if (can_view($key, $role)): 
                                $is_active = ($page === $key);
                        ?>
                            <a href="dashboard.php?page=<?= $key ?>" 
                               class="flex items-center justify-between px-3 py-2 rounded-xl transition-all duration-200 group <?= $is_active ? 'bg-gradient-to-r from-blue-600 to-[#0170b9] text-white shadow-md shadow-blue-900/40 font-semibold ring-1 ring-white/10' : 'text-slate-300 hover:text-white hover:bg-white/[0.08] font-medium' ?>"
                               title="<?= $item['label'] ?>">
                                <div class="flex items-center min-w-0 space-x-2.5">
                                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs flex-shrink-0 transition-all <?= $is_active ? 'bg-white/20 text-white shadow-inner' : 'bg-white/5 text-slate-400 group-hover:text-blue-300 group-hover:bg-white/10' ?>">
                                        <i class="fa-solid <?= $item['icon'] ?>"></i>
                                    </span>
                                    <span class="text-xs logo-text truncate tracking-tight"><?= $item['label'] ?></span>
                                </div>
                                <?php if ($is_active): ?>
                                    <span class="logo-text w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                <?php endif; ?>
                            </a>
                        <?php 
                            endif;
                        endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </nav>

        <!-- Bottom: Executive Consultant Status Card -->
        <div class="p-3 border-t border-white/10 bg-black/25 logo-text flex-shrink-0">
            <div class="bg-white/5 border border-white/10 rounded-xl p-2.5 space-y-1.5 shadow-inner">
                <div class="flex items-center justify-between">
                    <span class="text-[9.5px] font-mono font-bold text-blue-200 uppercase tracking-wider">SYSTEM CONSULTANT</span>
                    <span class="flex items-center text-[9px] text-emerald-400 font-mono font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1 animate-ping"></span>
                        ONLINE
                    </span>
                </div>
                <p class="text-[11px] font-bold text-white tracking-tight truncate">Conclusion Consultant</p>
                <p class="text-[9.5px] text-slate-300/80 truncate">ITL Trisakti &bull; Dr. Tigor Franky</p>
            </div>
        </div>
    </aside>

    <!-- Main Wrapper -->
    <div class="flex-1 flex flex-col h-full overflow-hidden relative">
        
        <!-- Overlay for mobile sidebar -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs z-30 hidden md:hidden transition-opacity" onclick="toggleSidebar()"></div>

        <!-- Top Header (Aligned with sidebar logo: h-14 / 56px) -->
        <header class="h-14 bg-white/95 backdrop-blur-md border-b border-gray-200/80 z-20 flex-shrink-0 sticky top-0 shadow-2xs font-sans">
            <div class="h-full w-full max-w-[1680px] mx-auto flex items-center justify-between px-3.5 sm:px-5 lg:px-8">
                <!-- Left: Toggle & Clean Page Title -->
                <div class="flex items-center min-w-0 mr-2">
                    <!-- Mobile Toggle -->
                    <button onclick="toggleSidebar()" class="mr-2.5 sm:mr-3 p-1.5 text-gray-500 hover:text-cdp-blue focus:outline-none md:hidden transition-colors rounded-lg hover:bg-gray-100" title="Buka Menu">
                        <i class="fa-solid fa-bars text-base"></i>
                    </button>
                    <!-- Desktop Toggle -->
                    <button onclick="toggleDesktopSidebar()" class="mr-2.5 sm:mr-3 p-1.5 text-gray-500 hover:text-cdp-blue focus:outline-none hidden md:block transition-colors rounded-lg hover:bg-gray-100" title="Kecilkan/Perbesar Menu">
                        <i class="fa-solid fa-bars-staggered text-base" id="desktopToggleIcon"></i>
                    </button>
                    <h1 class="text-sm sm:text-base font-bold text-gray-800 capitalize truncate">
                        <?= isset($menu_items[$page]) ? $menu_items[$page]['label'] : 'Dashboard' ?>
                    </h1>
                </div>
                
                <!-- Right: User & Actions (Executive Consultant Theme) -->
                <div class="flex items-center space-x-1.5 sm:space-x-2 flex-shrink-0">
                    <!-- Real-Time Operational Live Clock -->
                    <div class="flex items-center space-x-1.5 bg-slate-50 hover:bg-slate-100/90 border border-gray-200/90 rounded-lg px-2 sm:px-2.5 py-1 text-gray-700 shadow-2xs font-mono text-[10px] sm:text-[11px] transition-colors" title="Waktu Nyata Operasional Terminal CIDP (WIB)">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse flex-shrink-0"></span>
                        <i class="fa-regular fa-clock text-cdp-blue text-[10.5px]"></i>
                        <span id="navLiveClock" class="font-bold tracking-tight text-gray-800 font-mono">--:--:-- WIB</span>
                    </div>

                    <div class="h-4 w-px bg-gray-200/80"></div>

                    <!-- Notification Bell & Dropdown -->
                    <div class="relative" id="notifDropdownWrapper">
                        <button onclick="toggleNotifDropdown()" id="notifTriggerBtn" class="relative w-8 h-8 flex items-center justify-center text-gray-500 hover:text-cdp-blue transition-all rounded-lg hover:bg-blue-50/60 focus:outline-none" title="Notifikasi Sistem Operasional Yard">
                            <i class="fa-regular fa-bell text-[13px]" id="notifBellIcon"></i>
                            <span id="notifBadge" class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white animate-pulse"></span>
                        </button>

                        <!-- Notification Dropdown Card -->
                        <div id="notifDropdownCard" class="hidden absolute right-0 sm:right-[-40px] md:right-0 top-11 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-gray-200/90 overflow-hidden z-50 animate-fadeIn text-xs">
                            <!-- Header -->
                            <div class="bg-gradient-to-r from-[#002f5e] to-[#0170b9] p-3.5 text-white flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <i class="fa-solid fa-bell text-blue-200 text-xs"></i>
                                    <span class="font-bold text-sm">Notifikasi Sistem Yard</span>
                                    <span id="notifCountPill" class="px-2 py-0.5 rounded-full bg-emerald-500/90 text-white text-[10px] font-bold font-mono">4 Baru</span>
                                </div>
                                <button onclick="markAllNotificationsRead()" class="text-[11px] text-blue-100 hover:text-white transition font-medium flex items-center space-x-1">
                                    <i class="fa-solid fa-check-double text-[10px]"></i>
                                    <span>Tandai Dibaca</span>
                                </button>
                            </div>

                            <!-- Notification Feed List -->
                            <div id="notifFeedList" class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                                <!-- Item 1: Intermodal -->
                                <a href="dashboard.php?page=intermodal" class="notif-item p-3.5 flex items-start space-x-3 hover:bg-blue-50/40 transition group bg-blue-50/20">
                                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0 mt-0.5 group-hover:scale-105 transition-transform">
                                        <i class="fa-solid fa-train text-xs"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-gray-900 group-hover:text-cdp-blue transition-colors">KA 2518 Tiba di Siding Track-01</span>
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 notif-unread-dot flex-shrink-0"></span>
                                        </div>
                                        <p class="text-gray-600 text-[11px] mt-0.5 leading-relaxed">
                                            Sensor Frauscher memvalidasi 126 as roda (30 gerbong PPCW). Siap alih muat 48 TEU.
                                        </p>
                                        <span class="text-[10px] text-gray-400 mt-1 block font-mono">2 menit lalu &bull; Intermodal Siding</span>
                                    </div>
                                </a>

                                <!-- Item 2: Gate Management -->
                                <a href="dashboard.php?page=gate" class="notif-item p-3.5 flex items-start space-x-3 hover:bg-blue-50/40 transition group bg-blue-50/20">
                                    <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center flex-shrink-0 mt-0.5 group-hover:scale-105 transition-transform">
                                        <i class="fa-solid fa-truck text-xs"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-gray-900 group-hover:text-cdp-blue transition-colors">Truk B 9481 UEK Gate-In Sukses</span>
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 notif-unread-dot flex-shrink-0"></span>
                                        </div>
                                        <p class="text-gray-600 text-[11px] mt-0.5 leading-relaxed">
                                            Peti kemas MSKU9182374 lolos timbangan VGM 28,4 Ton & diverifikasi RFID UHF.
                                        </p>
                                        <span class="text-[10px] text-gray-400 mt-1 block font-mono">14 menit lalu &bull; Gate In 02</span>
                                    </div>
                                </a>

                                <!-- Item 3: Reefer Monitor -->
                                <a href="dashboard.php?page=reefer" class="notif-item p-3.5 flex items-start space-x-3 hover:bg-blue-50/40 transition group">
                                    <div class="w-8 h-8 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center flex-shrink-0 mt-0.5 group-hover:scale-105 transition-transform">
                                        <i class="fa-solid fa-snowflake text-xs"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-gray-900 group-hover:text-cdp-blue transition-colors">Reefer Plug #04 Terhubung</span>
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 notif-unread-dot flex-shrink-0"></span>
                                        </div>
                                        <p class="text-gray-600 text-[11px] mt-0.5 leading-relaxed">
                                            CMAU7234910 pasokan daya 380V aktif, suhu termometer stabil pada -20,2°C.
                                        </p>
                                        <span class="text-[10px] text-gray-400 mt-1 block font-mono">35 menit lalu &bull; Reefer Cold Chain</span>
                                    </div>
                                </a>

                                <!-- Item 4: Customs SPPB -->
                                <a href="dashboard.php?page=customs" class="notif-item p-3.5 flex items-start space-x-3 hover:bg-blue-50/40 transition group">
                                    <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center flex-shrink-0 mt-0.5 group-hover:scale-105 transition-transform">
                                        <i class="fa-solid fa-shield-halved text-xs"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-gray-900 group-hover:text-cdp-blue transition-colors">SPPB Bea Cukai Terbit (Jalur Hijau)</span>
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 notif-unread-dot flex-shrink-0"></span>
                                        </div>
                                        <p class="text-gray-600 text-[11px] mt-0.5 leading-relaxed">
                                            Dokumen PIB #091244/KPU.01/2026 disetujui sistem CEISA. Kontainer siap rilis.
                                        </p>
                                        <span class="text-[10px] text-gray-400 mt-1 block font-mono">1 jam lalu &bull; Bea Cukai Terpadu</span>
                                    </div>
                                </a>
                            </div>

                            <!-- Footer -->
                            <div class="p-2.5 bg-gray-50 border-t border-gray-100 text-center">
                                <a href="dashboard.php?page=kontainer" class="text-[11px] font-bold text-cdp-blue hover:text-cdp-navy transition inline-flex items-center space-x-1">
                                    <span>Lihat Seluruh Aktivitas &amp; Log Yard</span>
                                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="h-4 w-px bg-gray-200/80 mx-0.5"></div>

                    <!-- Profile Trigger & Dropdown -->
                    <div class="relative" id="profileDropdownWrapper">
                        <button onclick="toggleProfileDropdown()" class="flex items-center space-x-2 py-1 px-1.5 sm:px-2 rounded-xl hover:bg-gray-50 transition-all group focus:outline-none" id="profileTriggerBtn">
                            <!-- Name & Role (desktop only) -->
                            <div class="hidden sm:flex flex-col text-right mr-0.5">
                                <span class="text-[11px] font-semibold text-gray-800 leading-tight tracking-tight"><?= htmlspecialchars($display_nama) ?></span>
                                <span class="text-[9px] text-cdp-blue font-medium leading-tight mt-px">Lead System Architect</span>
                            </div>
                            <!-- Avatar with gradient ring -->
                            <div class="relative flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cdp-navy via-cdp-blue to-emerald-500 p-[2px] shadow-sm group-hover:shadow-md transition-shadow">
                                    <div class="w-full h-full rounded-full bg-white flex items-center justify-center">
                                        <span class="text-[11px] font-bold text-cdp-navy"><?= $avatar_initials ?></span>
                                    </div>
                                </div>
                                <!-- Online status dot -->
                                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-500 rounded-full ring-[2px] ring-white"></span>
                            </div>
                            <!-- Chevron -->
                            <i class="fa-solid fa-chevron-down text-[8px] text-gray-400 group-hover:text-gray-600 transition-transform duration-200 hidden sm:block" id="profileChevron"></i>
                        </button>

                        <!-- Profile Dropdown Card -->
                        <div id="profileDropdownCard" class="hidden absolute right-0 top-12 w-72 bg-white rounded-2xl shadow-2xl border border-gray-200/90 overflow-hidden z-50 animate-fadeIn text-xs">
                            <!-- Card Header -->
                            <div class="bg-gradient-to-br from-[#002f5e] via-[#004b87] to-[#0170b9] p-4 text-white">
                                <div class="flex items-center space-x-3">
                                    <div class="w-11 h-11 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center border-2 border-white/40 flex-shrink-0 shadow-inner">
                                        <span class="text-base font-bold tracking-wider text-white"><?= $avatar_initials ?></span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold leading-tight truncate"><?= htmlspecialchars($display_nama) ?></p>
                                        <p class="text-[10.5px] text-blue-100 truncate"><?= htmlspecialchars($email) ?></p>
                                        <div class="mt-1 flex items-center space-x-1">
                                            <span class="px-2 py-0.5 bg-emerald-500/25 border border-emerald-300/40 rounded-full text-[9px] font-bold text-emerald-200 flex items-center">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                                                Lead System Architect
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Role Switcher (Simulasi Evaluator ITL Trisakti) -->
                            <div class="p-3 bg-slate-50 border-b border-gray-100">
                                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1.5">
                                    <i class="fa-solid fa-users-gear mr-1 text-blue-600"></i> Mode Simulasi Hak Akses
                                </span>
                                <div class="grid grid-cols-2 gap-1.5 text-[11px]">
                                    <div class="bg-white border-2 border-blue-500 text-blue-800 font-bold p-1.5 rounded-lg flex items-center space-x-1.5 shadow-2xs">
                                        <i class="fa-solid fa-user-tie text-[10px]"></i>
                                        <span class="truncate">Lead Architect ✓</span>
                                    </div>
                                    <a href="dashboard.php?page=alat" class="bg-white hover:bg-gray-100 border border-gray-200 text-gray-600 p-1.5 rounded-lg flex items-center space-x-1.5 transition">
                                        <i class="fa-solid fa-dolly text-[10px] text-orange-500"></i>
                                        <span class="truncate">Operator RS</span>
                                    </a>
                                    <a href="dashboard.php?page=trucking" class="bg-white hover:bg-gray-100 border border-gray-200 text-gray-600 p-1.5 rounded-lg flex items-center space-x-1.5 transition">
                                        <i class="fa-solid fa-truck-moving text-[10px] text-emerald-500"></i>
                                        <span class="truncate">Supir Armada</span>
                                    </a>
                                    <a href="dashboard.php?page=billing" class="bg-white hover:bg-gray-100 border border-gray-200 text-gray-600 p-1.5 rounded-lg flex items-center space-x-1.5 transition">
                                        <i class="fa-solid fa-building text-[10px] text-purple-500"></i>
                                        <span class="truncate">Klien Shipper</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Card Menu -->
                            <div class="p-2 space-y-0.5">
                                <a href="dashboard.php?page=settings" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl hover:bg-blue-50 text-gray-700 hover:text-cdp-blue transition-colors group">
                                    <i class="fa-solid fa-gear text-xs text-gray-400 group-hover:text-cdp-blue transition-colors w-4 text-center"></i>
                                    <span class="font-medium">Pengaturan Akun &amp; Sistem</span>
                                </a>
                                <a href="dashboard.php?page=simulator" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl hover:bg-blue-50 text-gray-700 hover:text-cdp-blue transition-colors group">
                                    <i class="fa-solid fa-tower-broadcast text-xs text-gray-400 group-hover:text-cdp-blue transition-colors w-4 text-center"></i>
                                    <span class="font-medium">Panel Simulasi IoT &amp; Sensor</span>
                                </a>
                                <a href="dashboard.php?page=denah" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl hover:bg-blue-50 text-gray-700 hover:text-cdp-blue transition-colors group">
                                    <i class="fa-solid fa-map-location-dot text-xs text-gray-400 group-hover:text-cdp-blue transition-colors w-4 text-center"></i>
                                    <span class="font-medium">Site Plan Denah Terminal 35 Ha</span>
                                </a>

                                <div class="my-1.5 border-t border-gray-100"></div>

                                <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari konsol simulasi CIDP YMS?');" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl hover:bg-red-50 text-red-600 transition-colors group">
                                    <i class="fa-solid fa-right-from-bracket text-xs text-red-400 group-hover:text-red-600 transition-colors w-4 text-center"></i>
                                    <span class="font-bold">Keluar dari Sistem</span>
                                </a>
                            </div>

                            <!-- Card Footer -->
                            <div class="px-4 py-2.5 bg-gray-50 border-t border-gray-100 text-center">
                                <p class="text-[9.5px] text-gray-500 font-medium">Conclusion Supply Chain Consultant &copy; 2026</p>
                                <p class="text-[9px] text-gray-400">ITL Trisakti &bull; Dr. Tigor Franky, S.T., M.T.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Scrollable Area (Symmetrical Container) -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-cdp-light relative">
            <div class="p-3.5 sm:p-5 lg:p-8 w-full max-w-[1680px] mx-auto space-y-4 sm:space-y-5">
                
                <?php if ($page === 'beranda'): ?>
                
                <!-- Executive Cockpit Header & Real-Time Status -->
                <div class="bg-white rounded-xl p-3 sm:p-3.5 shadow-2xs border border-gray-200/80 flex flex-col md:flex-row items-start md:items-center justify-between gap-2.5 transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-cdp-blue flex items-center justify-center text-sm font-bold flex-shrink-0 border border-blue-100">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-sm sm:text-base font-bold text-gray-900 leading-tight">
                                    Konsol Eksekutif &amp; Telemetri Operasional Dry Port
                                </h2>
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    LIVE 35 Ha
                                </span>
                            </div>
                            <p class="text-gray-500 text-[11px] mt-0.5">Simulasi Inland Container Depot (ICD), Siding KA Daop 1, &amp; Pabean CEISA 4.0</p>
                        </div>
                    </div>
                    <!-- Micro Telemetry Badges & Real-Time Clock -->
                    <div class="flex items-center gap-1.5 flex-wrap text-[10px]">
                        <div class="bg-gray-50 border border-gray-200/80 px-2 py-1 rounded-lg text-gray-700 flex items-center gap-1.5 shadow-2xs font-mono">
                            <i class="fa-regular fa-calendar text-cdp-blue text-[10px]"></i>
                            <span class="font-sans font-medium text-gray-600"><?= $tanggal_sekarang ?></span>
                            <span class="text-gray-300">&bull;</span>
                            <i class="fa-solid fa-clock text-cdp-blue text-[9.5px]"></i>
                            <span id="berandaLiveClock" class="font-bold text-cdp-navy">--:--:-- WIB</span>
                        </div>
                        <div class="bg-blue-50/70 border border-blue-200/60 px-2 py-1 rounded-lg text-blue-700 flex items-center gap-1.5 font-semibold">
                            <i class="fa-solid fa-satellite-dish text-[9px] text-blue-500"></i>
                            <span>SCADA: 99.8%</span>
                        </div>
                        <div class="bg-purple-50/70 border border-purple-200/60 px-2 py-1 rounded-lg text-purple-700 flex items-center gap-1.5 font-semibold">
                            <i class="fa-solid fa-train-subway text-[9px] text-purple-500"></i>
                            <span>Siding KA: Ready</span>
                        </div>
                    </div>
                </div>

                <!-- Live IoT Sensor & Field Workbench Status Bar (Direct-Launch Shortcuts) -->
                <div class="bg-gradient-to-r from-[#002f5e] to-[#004b87] rounded-xl p-3 sm:p-3.5 shadow-sm border border-blue-900/40 text-white">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-2 pb-2 border-b border-white/10">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                            <span class="w-2 h-2 rounded-full bg-emerald-400 -ml-3"></span>
                            <h3 class="text-xs sm:text-sm font-bold tracking-wide flex items-center gap-1.5">
                                <i class="fa-solid fa-tower-broadcast text-cyan-300"></i>
                                <span>Pusat Kendali Sensor &amp; IoT Workbench Lapangan (Live Operational Sync)</span>
                            </h3>
                        </div>
                        <span class="text-[10px] text-blue-200/90 font-mono flex items-center gap-1.5">
                            <i class="fa-solid fa-microchip text-cyan-300"></i>
                            <span>26 BOM Hardware Terkalibrasi &bull; Latensi &lt; 150ms</span>
                        </span>
                    </div>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 text-[11px]">
                        <!-- Gateway 1: Gate ANPR -->
                        <a href="dashboard.php?page=gate" class="bg-white/10 hover:bg-white/15 p-2 rounded-lg border border-white/10 transition flex items-center justify-between group">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-md bg-amber-500/20 text-amber-300 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-door-open"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-white group-hover:text-amber-300 transition text-[11px]">Gate &amp; Timbangan</div>
                                    <div class="text-[9.5px] text-blue-200">ANPR OCR + 80T Solas</div>
                                </div>
                            </div>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> ONLINE
                            </span>
                        </a>

                        <!-- Gateway 2: CFS Rampa D1-D5 -->
                        <a href="dashboard.php?page=cfs" class="bg-white/10 hover:bg-white/15 p-2 rounded-lg border border-white/10 transition flex items-center justify-between group">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-md bg-indigo-500/20 text-indigo-300 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-warehouse"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-white group-hover:text-indigo-300 transition text-[11px]">CFS Rampa D1-D5</div>
                                    <div class="text-[9.5px] text-blue-200">Pallet 3T + Gas HW-19</div>
                                </div>
                            </div>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> ONLINE
                            </span>
                        </a>

                        <!-- Gateway 3: Reefer Terminal -->
                        <a href="dashboard.php?page=reefer" class="bg-white/10 hover:bg-white/15 p-2 rounded-lg border border-white/10 transition flex items-center justify-between group">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-md bg-cyan-500/20 text-cyan-300 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-snowflake"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-white group-hover:text-cyan-300 transition text-[11px]">Reefer Cold Chain</div>
                                    <div class="text-[9.5px] text-blue-200">300 Plug + SHT40 LoRa</div>
                                </div>
                            </div>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-400/30 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span> PULSE
                            </span>
                        </a>

                        <!-- Gateway 4: Denah Terminal 35 Ha -->
                        <a href="dashboard.php?page=denah" class="bg-white/10 hover:bg-white/15 p-2 rounded-lg border border-white/10 transition flex items-center justify-between group">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-md bg-emerald-500/20 text-emerald-300 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-map-location-dot"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-white group-hover:text-emerald-300 transition text-[11px]">Denah Terminal 35 Ha</div>
                                    <div class="text-[9.5px] text-blue-200">26 Hardware BOM Map</div>
                                </div>
                            </div>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-500/20 text-blue-200 border border-blue-400/30 flex items-center gap-1">
                                <i class="fa-solid fa-up-right-from-square text-[8px]"></i> UJI REAL
                            </span>
                        </a>
                    </div>
                </div>

                <!-- 6 Compact KPI Summary Cards (1-Row Grid on Desktop, Symmetrical 2x3 Grid on Mobile) -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-3">
                    
                    <!-- KPI 1: Okupansi Yard -->
                    <div class="bg-white rounded-xl p-2.5 sm:p-3 shadow-2xs border border-gray-200/80 hover:border-blue-300 hover:shadow-xs transition-all group flex flex-col justify-between">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[9.5px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">Yard Stack</p>
                                <h3 class="text-lg sm:text-xl font-extrabold text-gray-900 mt-0.5 leading-none"><?= $db_stats['containers_in_yard'] ?> <span class="text-[10px] font-normal text-gray-400">box</span></h3>
                            </div>
                            <div class="w-6 h-6 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                                <i class="fa-solid fa-boxes-stacked"></i>
                            </div>
                        </div>
                        <div class="mt-2 space-y-1">
                            <?php $yard_pct = min(100, max(5, round(($db_stats['containers_in_yard'] / 25) * 100))); ?>
                            <div class="w-full bg-gray-100 rounded-full h-1 overflow-hidden">
                                <div class="bg-blue-600 h-1 rounded-full" style="width: <?= $yard_pct ?>%"></div>
                            </div>
                            <div class="flex justify-between items-center text-[9px] text-gray-500">
                                <span class="font-bold text-blue-600"><?= $yard_pct ?>% Terisi</span>
                                <span class="text-emerald-600 font-semibold">+<?= max(1, $db_stats['containers_in_yard']) ?> box</span>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 2: Gate Turnaround Time -->
                    <div class="bg-white rounded-xl p-2.5 sm:p-3 shadow-2xs border border-gray-200/80 hover:border-indigo-300 hover:shadow-xs transition-all group flex flex-col justify-between">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[9.5px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">Gate TAT</p>
                                <h3 class="text-lg sm:text-xl font-extrabold text-gray-900 mt-0.5 leading-none">18.2 <span class="text-[10px] font-normal text-gray-400">mnt</span></h3>
                            </div>
                            <div class="w-6 h-6 rounded-md bg-indigo-50 text-indigo-600 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                                <i class="fa-solid fa-truck-fast"></i>
                            </div>
                        </div>
                        <div class="mt-2 space-y-1">
                            <div class="w-full bg-gray-100 rounded-full h-1 overflow-hidden">
                                <div class="bg-indigo-600 h-1 rounded-full" style="width: 72%"></div>
                            </div>
                            <div class="flex justify-between items-center text-[9px] text-gray-500">
                                <span class="text-indigo-600 font-semibold"><?= $db_stats['total_gate_trucks'] ?> Truk Aktif</span>
                                <span class="text-emerald-600 font-bold">On-Time 98%</span>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 3: Kereta Api Logistik -->
                    <div class="bg-white rounded-xl p-2.5 sm:p-3 shadow-2xs border border-gray-200/80 hover:border-purple-300 hover:shadow-xs transition-all group flex flex-col justify-between">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[9.5px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">Siding KA</p>
                                <h3 class="text-lg sm:text-xl font-extrabold text-gray-900 mt-0.5 leading-none"><?= $db_stats['loaded_wagons'] ?>/<?= $db_stats['total_wagons'] ?> <span class="text-[10px] font-normal text-gray-400">Wg</span></h3>
                            </div>
                            <div class="w-6 h-6 rounded-md bg-purple-50 text-purple-600 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                                <i class="fa-solid fa-train"></i>
                            </div>
                        </div>
                        <div class="mt-2 space-y-1">
                            <?php $train_pct = $db_stats['total_wagons'] > 0 ? round(($db_stats['loaded_wagons'] / $db_stats['total_wagons']) * 100) : 80; ?>
                            <div class="w-full bg-gray-100 rounded-full h-1 overflow-hidden">
                                <div class="bg-purple-600 h-1 rounded-full" style="width: <?= $train_pct ?>%"></div>
                            </div>
                            <div class="flex justify-between items-center text-[9px] text-gray-500">
                                <span class="text-purple-600 font-bold">KA 2518 (<?= $train_pct ?>%)</span>
                                <span class="text-gray-400">16:45 Dep</span>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 4: Reefer Cold Chain -->
                    <div class="bg-white rounded-xl p-2.5 sm:p-3 shadow-2xs border border-gray-200/80 hover:border-cyan-300 hover:shadow-xs transition-all group flex flex-col justify-between">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[9.5px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">Reefer Plug</p>
                                <h3 class="text-lg sm:text-xl font-extrabold text-cyan-700 mt-0.5 leading-none">-20.2<span class="text-[10px] font-normal text-gray-400">&deg;C</span></h3>
                            </div>
                            <div class="w-6 h-6 rounded-md bg-cyan-50 text-cyan-600 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                                <i class="fa-solid fa-snowflake"></i>
                            </div>
                        </div>
                        <div class="mt-2 space-y-1">
                            <?php $reefer_pct = min(100, max(5, round(($db_stats['reefer_count'] / 10) * 100))); ?>
                            <div class="w-full bg-gray-100 rounded-full h-1 overflow-hidden">
                                <div class="bg-cyan-500 h-1 rounded-full" style="width: <?= $reefer_pct ?>%"></div>
                            </div>
                            <div class="flex justify-between items-center text-[9px] text-gray-500">
                                <span class="text-cyan-700 font-bold"><?= $db_stats['reefer_count'] ?>/300 Plug</span>
                                <span class="text-emerald-600 font-semibold">Marechal 380V</span>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 5: Bea Cukai CEISA -->
                    <div class="bg-white rounded-xl p-2.5 sm:p-3 shadow-2xs border border-gray-200/80 hover:border-emerald-300 hover:shadow-xs transition-all group flex flex-col justify-between">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[9.5px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">Bea Cukai</p>
                                <h3 class="text-lg sm:text-xl font-extrabold text-emerald-700 mt-0.5 leading-none"><?= $db_stats['customs_pct'] ?><span class="text-[10px] font-normal text-gray-400">%</span></h3>
                            </div>
                            <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                        </div>
                        <div class="mt-2 space-y-1">
                            <div class="w-full bg-gray-100 rounded-full h-1 overflow-hidden">
                                <div class="bg-emerald-500 h-1 rounded-full" style="width: <?= min(100, (int)$db_stats['customs_pct']) ?>%"></div>
                            </div>
                            <div class="flex justify-between items-center text-[9px] text-gray-500">
                                <span class="text-emerald-700 font-bold">SPPB Cleared (<?= $db_stats['customs_cleared'] ?>/<?= $db_stats['customs_total'] ?>)</span>
                                <span class="text-gray-400">CEISA 4.0</span>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 6: CFS & Billing Operasional -->
                    <div class="bg-white rounded-xl p-2.5 sm:p-3 shadow-2xs border border-gray-200/80 hover:border-indigo-300 hover:shadow-xs transition-all group flex flex-col justify-between">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[9.5px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">CFS &amp; Billing</p>
                                <h3 class="text-lg sm:text-xl font-extrabold text-indigo-700 mt-0.5 leading-none"><?= $db_stats['cfs_active_jobs'] ?> <span class="text-[10px] font-normal text-gray-400">Jobs</span></h3>
                            </div>
                            <div class="w-6 h-6 rounded-md bg-indigo-50 text-indigo-600 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform flex-shrink-0">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                            </div>
                        </div>
                        <div class="mt-2 space-y-1">
                            <div class="w-full bg-gray-100 rounded-full h-1 overflow-hidden">
                                <div class="bg-indigo-600 h-1 rounded-full" style="width: 85%"></div>
                            </div>
                            <div class="flex justify-between items-center text-[9px] text-gray-500">
                                <span class="text-indigo-700 font-bold">Rampa D1-D5 Aktif</span>
                                <span class="text-emerald-600 font-semibold">Rp <?= number_format($db_stats['billing_paid'] / 1000000, 1) ?>Jt Paid</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Visual End-to-End Operational Logistics Conveyor Pipeline (Anti-Pusing / Visual Alur) -->
                <div class="bg-white rounded-xl p-3 sm:p-4 shadow-2xs border border-gray-200/80">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 mb-2.5 pb-2 border-b border-gray-100">
                        <div>
                            <h3 class="font-bold text-gray-800 text-xs sm:text-sm flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                                <i class="fa-solid fa-arrows-split-up-and-left text-cdp-blue text-sm"></i>
                                <span>Alur Logistik End-to-End Intermodal Dry Port (Visual Pipeline 35 Ha)</span>
                            </h3>
                            <p class="text-[10.5px] text-gray-400 mt-0.5">Pandangan visual interaktif alur kargo dari pintu gerbang hingga kereta intermodal &amp; pelabuhan.</p>
                        </div>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200 self-start sm:self-auto flex items-center gap-1">
                            <i class="fa-solid fa-circle-check text-[9px]"></i> Alur Logistik Terbuka Penuh
                        </span>
                    </div>

                    <!-- 6-Stage Interactive Visual Flow Ribbon -->
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2">
                        <!-- Stage 1: Gate-In & ANPR -->
                        <a href="dashboard.php?page=gate" class="group p-2.5 rounded-lg border border-amber-200 bg-amber-50/40 hover:bg-amber-100/50 hover:border-amber-300 transition flex flex-col justify-between relative overflow-hidden">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="w-5 h-5 rounded-md bg-amber-500 text-white flex items-center justify-center text-[10px] font-bold font-mono">1</span>
                                <span class="text-[8.5px] font-bold text-amber-700 bg-amber-100/80 px-1 py-0.2 rounded uppercase">Gate-In</span>
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-[11px] font-bold text-gray-800 group-hover:text-amber-800 transition flex items-center gap-1">
                                    <i class="fa-solid fa-camera text-amber-600 text-[10px]"></i>
                                    <span>ANPR &amp; RFID OCR</span>
                                </div>
                                <p class="text-[9.5px] text-gray-500">Scan plat &amp; RFID box otomatis</p>
                            </div>
                            <div class="mt-2 pt-1 border-t border-amber-200/60 flex items-center justify-between text-[9px] text-amber-900 font-semibold">
                                <span>Akurasi: 99.4%</span>
                                <i class="fa-solid fa-arrow-right text-[8px] text-amber-500 group-hover:translate-x-0.5 transition-transform"></i>
                            </div>
                        </a>

                        <!-- Stage 2: Weighbridge SOLAS VGM -->
                        <a href="dashboard.php?page=gate" class="group p-2.5 rounded-lg border border-purple-200 bg-purple-50/40 hover:bg-purple-100/50 hover:border-purple-300 transition flex flex-col justify-between relative overflow-hidden">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="w-5 h-5 rounded-md bg-purple-600 text-white flex items-center justify-center text-[10px] font-bold font-mono">2</span>
                                <span class="text-[8.5px] font-bold text-purple-700 bg-purple-100/80 px-1 py-0.2 rounded uppercase">Timbang</span>
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-[11px] font-bold text-gray-800 group-hover:text-purple-800 transition flex items-center gap-1">
                                    <i class="fa-solid fa-weight-scale text-purple-600 text-[10px]"></i>
                                    <span>Jembatan 80 Ton</span>
                                </div>
                                <p class="text-[9.5px] text-gray-500">SOLAS VGM IMO Rule</p>
                            </div>
                            <div class="mt-2 pt-1 border-t border-purple-200/60 flex items-center justify-between text-[9px] text-purple-900 font-semibold">
                                <span>Toleransi: &plusmn;0.2%</span>
                                <i class="fa-solid fa-arrow-right text-[8px] text-purple-500 group-hover:translate-x-0.5 transition-transform"></i>
                            </div>
                        </a>

                        <!-- Stage 3: Yard Stacking & Lift-Off -->
                        <a href="dashboard.php?page=yard" class="group p-2.5 rounded-lg border border-blue-200 bg-blue-50/40 hover:bg-blue-100/50 hover:border-blue-300 transition flex flex-col justify-between relative overflow-hidden">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="w-5 h-5 rounded-md bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold font-mono">3</span>
                                <span class="text-[8.5px] font-bold text-blue-700 bg-blue-100/80 px-1 py-0.2 rounded uppercase">Penataan</span>
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-[11px] font-bold text-gray-800 group-hover:text-blue-800 transition flex items-center gap-1">
                                    <i class="fa-solid fa-cubes text-blue-600 text-[10px]"></i>
                                    <span>Yard Blok A/B/C</span>
                                </div>
                                <p class="text-[9.5px] text-gray-500">Reach Stacker Kalmar 45T</p>
                            </div>
                            <div class="mt-2 pt-1 border-t border-blue-200/60 flex items-center justify-between text-[9px] text-blue-900 font-semibold">
                                <span><?= $db_stats['containers_in_yard'] ?> Box Stack</span>
                                <i class="fa-solid fa-arrow-right text-[8px] text-blue-500 group-hover:translate-x-0.5 transition-transform"></i>
                            </div>
                        </a>

                        <!-- Stage 4: CFS Stuffing / Stripping -->
                        <a href="dashboard.php?page=cfs" class="group p-2.5 rounded-lg border border-indigo-200 bg-indigo-50/40 hover:bg-indigo-100/50 hover:border-indigo-300 transition flex flex-col justify-between relative overflow-hidden">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="w-5 h-5 rounded-md bg-indigo-600 text-white flex items-center justify-center text-[10px] font-bold font-mono">4</span>
                                <span class="text-[8.5px] font-bold text-indigo-700 bg-indigo-100/80 px-1 py-0.2 rounded uppercase">CFS Gudang</span>
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-[11px] font-bold text-gray-800 group-hover:text-indigo-800 transition flex items-center gap-1">
                                    <i class="fa-solid fa-warehouse text-indigo-600 text-[10px]"></i>
                                    <span>Rampa D1-D5 LCL</span>
                                </div>
                                <p class="text-[9.5px] text-gray-500">Pallet 3T + Gas HW-19</p>
                            </div>
                            <div class="mt-2 pt-1 border-t border-indigo-200/60 flex items-center justify-between text-[9px] text-indigo-900 font-semibold">
                                <span><?= $db_stats['cfs_active_jobs'] ?> Jobs Aktif</span>
                                <i class="fa-solid fa-arrow-right text-[8px] text-indigo-500 group-hover:translate-x-0.5 transition-transform"></i>
                            </div>
                        </a>

                        <!-- Stage 5: Reefer & Cold Chain -->
                        <a href="dashboard.php?page=reefer" class="group p-2.5 rounded-lg border border-cyan-200 bg-cyan-50/40 hover:bg-cyan-100/50 hover:border-cyan-300 transition flex flex-col justify-between relative overflow-hidden">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="w-5 h-5 rounded-md bg-cyan-600 text-white flex items-center justify-center text-[10px] font-bold font-mono">5</span>
                                <span class="text-[8.5px] font-bold text-cyan-700 bg-cyan-100/80 px-1 py-0.2 rounded uppercase">Cold Chain</span>
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-[11px] font-bold text-gray-800 group-hover:text-cyan-800 transition flex items-center gap-1">
                                    <i class="fa-solid fa-snowflake text-cyan-600 text-[10px]"></i>
                                    <span>300 Marechal Plugs</span>
                                </div>
                                <p class="text-[9.5px] text-gray-500">SHT40 LoRa &bull; CEISA SPPB</p>
                            </div>
                            <div class="mt-2 pt-1 border-t border-cyan-200/60 flex items-center justify-between text-[9px] text-cyan-900 font-semibold">
                                <span>-20.2&deg;C Stabil</span>
                                <i class="fa-solid fa-arrow-right text-[8px] text-cyan-500 group-hover:translate-x-0.5 transition-transform"></i>
                            </div>
                        </a>

                        <!-- Stage 6: Intermodal Rail Siding KA -->
                        <a href="dashboard.php?page=intermodal" class="group p-2.5 rounded-lg border border-emerald-200 bg-emerald-50/40 hover:bg-emerald-100/50 hover:border-emerald-300 transition flex flex-col justify-between relative overflow-hidden">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="w-5 h-5 rounded-md bg-emerald-600 text-white flex items-center justify-center text-[10px] font-bold font-mono">6</span>
                                <span class="text-[8.5px] font-bold text-emerald-700 bg-emerald-100/80 px-1 py-0.2 rounded uppercase">Intermodal</span>
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-[11px] font-bold text-gray-800 group-hover:text-emerald-800 transition flex items-center gap-1">
                                    <i class="fa-solid fa-train-subway text-emerald-600 text-[10px]"></i>
                                    <span>KA 2518 ke Priok</span>
                                </div>
                                <p class="text-[9.5px] text-gray-500">Track S-01 (54.8 km Siding)</p>
                            </div>
                            <div class="mt-2 pt-1 border-t border-emerald-200/60 flex items-center justify-between text-[9px] text-emerald-900 font-semibold">
                                <span><?= $db_stats['loaded_wagons'] ?>/<?= $db_stats['total_wagons'] ?> Gerbong</span>
                                <i class="fa-solid fa-arrow-right text-[8px] text-emerald-500 group-hover:translate-x-0.5 transition-transform"></i>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Row 1: Core Flow & Yard Heatmap (Equal 3-Column Cockpit) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-3.5">
                    
                    <!-- Col 1: Gate In/Out Throughput Line Chart -->
                    <div class="bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3 sm:p-3.5 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2 pb-1.5 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center">
                                <i class="fa-solid fa-chart-line mr-1.5 text-cdp-blue text-xs"></i>Throughput Gate (7 Hari)
                            </h3>
                            <span class="text-[9px] font-bold text-gray-400 bg-gray-50 px-1.5 py-0.5 rounded">In vs Out</span>
                        </div>
                        <!-- Compact Chart Container -->
                        <div class="h-36 sm:h-40 relative w-full flex-1">
                            <canvas id="gateTrendChart"></canvas>
                        </div>
                        <!-- Micro Metrics Strip -->
                        <div class="mt-2 pt-1.5 border-t border-gray-50 flex items-center justify-between text-[10px] text-gray-500">
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#0170b9]"></span>In: <strong>139 box</strong></span>
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#4f46e5]"></span>Out: <strong>121 box</strong></span>
                            <span class="text-emerald-600 font-bold">Akumulasi: +18 box</span>
                        </div>
                    </div>

                    <!-- Col 2: Yard Block Capacity Heatmap -->
                    <div class="bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3 sm:p-3.5 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2 pb-1.5 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center">
                                <i class="fa-solid fa-cubes-stacked mr-1.5 text-cdp-blue text-xs"></i>Kapasitas Blok Yard Real-Time
                            </h3>
                            <span class="text-[9px] font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200"><?= $db_stats['containers_in_yard'] ?> Box di Yard</span>
                        </div>
                        <!-- Visual Micro Block Heatmap Meters -->
                        <?php 
                            $cnt_a = $db_stats['blocks']['A'] ?? 8;
                            $pct_a = min(100, round(($cnt_a / 20) * 100));
                            $cnt_b = $db_stats['blocks']['B'] ?? 7;
                            $pct_b = min(100, round(($cnt_b / 20) * 100));
                            $cnt_c = $db_stats['blocks']['C'] ?? 2;
                            $pct_c = min(100, round(($cnt_c / 20) * 100));
                            $cnt_rf = $db_stats['blocks']['REEFER'] ?? ($db_stats['blocks']['R'] ?? $db_stats['reefer_count']);
                            $pct_rf = min(100, round(($cnt_rf / 10) * 100));
                        ?>
                        <div class="space-y-2 py-1 flex-1 flex flex-col justify-around">
                            <!-- Blok A -->
                            <div>
                                <div class="flex justify-between text-[10.5px] mb-0.5">
                                    <span class="font-semibold text-gray-700 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>Blok A (Dry 40ft Impor)
                                    </span>
                                    <span class="font-bold text-gray-800"><?= $pct_a ?>% <span class="text-[9px] text-gray-400 font-normal">(<?= $cnt_a ?> box)</span></span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: <?= $pct_a ?>%"></div>
                                </div>
                            </div>
                            <!-- Blok B -->
                            <div>
                                <div class="flex justify-between text-[10.5px] mb-0.5">
                                    <span class="font-semibold text-gray-700 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>Blok B (Dry 20ft Ekspor)
                                    </span>
                                    <span class="font-bold text-gray-800"><?= $pct_b ?>% <span class="text-[9px] text-gray-400 font-normal">(<?= $cnt_b ?> box)</span></span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: <?= $pct_b ?>%"></div>
                                </div>
                            </div>
                            <!-- Blok C -->
                            <div>
                                <div class="flex justify-between text-[10.5px] mb-0.5">
                                    <span class="font-semibold text-gray-700 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>Blok C (Buffer &amp; Domestik)
                                    </span>
                                    <span class="font-bold text-gray-800"><?= $pct_c ?>% <span class="text-[9px] text-gray-400 font-normal">(<?= $cnt_c ?> box)</span></span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-indigo-500 h-1.5 rounded-full" style="width: <?= $pct_c ?>%"></div>
                                </div>
                            </div>
                            <!-- Reefer Zone -->
                            <div>
                                <div class="flex justify-between text-[10.5px] mb-0.5">
                                    <span class="font-semibold text-gray-700 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>Reefer Cold Chain (Plugs)
                                    </span>
                                    <span class="font-bold text-cyan-700"><?= $pct_rf ?>% <span class="text-[9px] text-gray-400 font-normal">(<?= $cnt_rf ?> box aktif)</span></span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-cyan-500 h-1.5 rounded-full" style="width: <?= $pct_rf ?>%"></div>
                                </div>
                            </div>
                            <!-- DG IMO & Empty Depot -->
                            <div class="grid grid-cols-2 gap-2 pt-0.5">
                                <div>
                                    <div class="flex justify-between text-[9.5px] mb-0.5">
                                        <span class="font-medium text-gray-600">DG / IMO Yard</span>
                                        <span class="font-bold text-amber-600"><?= $db_stats['blocks']['DG'] ?? 0 ?> box (Aman)</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1 overflow-hidden">
                                        <div class="bg-amber-500 h-1 rounded-full" style="width: 25%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-[9.5px] mb-0.5">
                                        <span class="font-medium text-gray-600">Empty Depot (M&amp;R)</span>
                                        <span class="font-bold text-gray-600">Standby</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1 overflow-hidden">
                                        <div class="bg-gray-400 h-1 rounded-full" style="width: 30%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Col 3: Container Type Doughnut & Customs Pipeline -->
                    <div class="bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3 sm:p-3.5 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2 pb-1.5 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center">
                                <i class="fa-solid fa-chart-pie mr-1.5 text-cdp-blue text-xs"></i>Tipe Kargo &amp; Bea Cukai
                            </h3>
                            <span class="text-[9px] font-bold text-gray-400 bg-gray-50 px-1.5 py-0.5 rounded">CEISA 4.0</span>
                        </div>
                        <!-- Doughnut Chart Container -->
                        <div class="h-32 sm:h-36 relative w-full flex-1 flex items-center justify-center">
                            <canvas id="containerTypeChart"></canvas>
                        </div>
                        <!-- Customs Pipeline Channel Segment Bar (PMK 190/PMK.04/2022) -->
                        <div class="mt-2 pt-1.5 border-t border-gray-100 space-y-1">
                            <div class="flex justify-between text-[10px]">
                                <span class="font-semibold text-gray-600">Pipeline Kanal Pabean (PMK 190/2022):</span>
                                <span class="font-bold text-emerald-700">SPPB Auto: 92%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2 flex overflow-hidden">
                                <div class="bg-emerald-500 h-2" style="width: 92%" title="Jalur Hijau (92%) - Auto SPPB Rilis"></div>
                                <div class="bg-rose-500 h-2" style="width: 8%" title="Jalur Merah (8%) - Wajib Behandle & X-Ray"></div>
                            </div>
                            <div class="flex justify-between items-center text-[9px] text-gray-500 pt-0.5">
                                <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Hijau (SPPB Rilis): 92%</span>
                                <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Merah (Fisik/Behandle): 8%</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Row 2: Specialized Dry Port Facilities & Intermodal Telemetry (Equal 3-Column Cockpit) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-3.5">
                    
                    <!-- Col 1: Intermodal Rail Siding & Green ICD -->
                    <div class="bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3 sm:p-3.5 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2 pb-1.5 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center">
                                <i class="fa-solid fa-train-subway mr-1.5 text-cdp-blue text-xs"></i>Intermodal Rail Siding &amp; ESG
                            </h3>
                            <span class="text-[9px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200">KA 2518 Live</span>
                        </div>
                        <div class="space-y-2 py-0.5 flex-1 flex flex-col justify-between">
                            <!-- Route & Status Strip -->
                            <div class="bg-slate-50 p-2 rounded-lg border border-gray-200/80">
                                <div class="flex justify-between items-center text-[10.5px]">
                                    <span class="font-bold text-gray-800">CDP Cikarang &harr; Pel. Tj. Priok</span>
                                    <span class="text-purple-700 font-bold bg-purple-100/70 px-1.5 py-0.2 rounded text-[9.5px]">54.8 km</span>
                                </div>
                                <div class="mt-1.5 flex items-center justify-between text-[10px] text-gray-500">
                                    <span>Lokomotif: <strong>CC 206 13 42</strong></span>
                                    <span>ETA Priok: <strong>16:45 WIB</strong></span>
                                </div>
                            </div>

                            <!-- Visual Train Wagon Grid Strip (10 Slots = 30 Wg) -->
                            <div>
                                <div class="flex justify-between text-[10px] text-gray-600 mb-1">
                                    <span class="font-medium">Okupansi Gerbong Datar (PPCW):</span>
                                    <span class="font-bold text-purple-700">24 / 30 TEU (80%)</span>
                                </div>
                                <div class="grid grid-cols-10 gap-1">
                                    <div class="h-4 rounded bg-purple-600 flex items-center justify-center text-[8px] text-white font-mono font-bold" title="Slot 01: Terisi">1</div>
                                    <div class="h-4 rounded bg-purple-600 flex items-center justify-center text-[8px] text-white font-mono font-bold" title="Slot 02: Terisi">2</div>
                                    <div class="h-4 rounded bg-purple-600 flex items-center justify-center text-[8px] text-white font-mono font-bold" title="Slot 03: Terisi">3</div>
                                    <div class="h-4 rounded bg-purple-600 flex items-center justify-center text-[8px] text-white font-mono font-bold" title="Slot 04: Terisi">4</div>
                                    <div class="h-4 rounded bg-purple-600 flex items-center justify-center text-[8px] text-white font-mono font-bold" title="Slot 05: Terisi">5</div>
                                    <div class="h-4 rounded bg-purple-600 flex items-center justify-center text-[8px] text-white font-mono font-bold" title="Slot 06: Terisi">6</div>
                                    <div class="h-4 rounded bg-purple-600 flex items-center justify-center text-[8px] text-white font-mono font-bold" title="Slot 07: Terisi">7</div>
                                    <div class="h-4 rounded bg-purple-600 flex items-center justify-center text-[8px] text-white font-mono font-bold" title="Slot 08: Terisi">8</div>
                                    <div class="h-4 rounded bg-gray-200 border border-dashed border-gray-300 flex items-center justify-center text-[8px] text-gray-400 font-mono" title="Slot 09: Standby">9</div>
                                    <div class="h-4 rounded bg-gray-200 border border-dashed border-gray-300 flex items-center justify-center text-[8px] text-gray-400 font-mono" title="Slot 10: Standby">10</div>
                                </div>
                            </div>

                            <!-- ESG Green ICD Offset -->
                            <div class="p-2 bg-emerald-50/70 border border-emerald-200/80 rounded-lg flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-md bg-emerald-500 text-white flex items-center justify-center text-[10px]">
                                        <i class="fa-solid fa-leaf"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-emerald-900 leading-tight">Reduksi Karbon Modal Shift</p>
                                        <p class="text-[9px] text-emerald-700 leading-tight">Substitusi 48 truk trailer di Tol Cikampek</p>
                                    </div>
                                </div>
                                <span class="text-xs font-extrabold text-emerald-800 bg-white px-2 py-0.5 rounded shadow-2xs border border-emerald-100 font-mono">-1.84 T CO&sup2;</span>
                            </div>
                        </div>
                    </div>

                    <!-- Col 2: Heavy Equipment SCADA Fleet Matrix -->
                    <div class="bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3 sm:p-3.5 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2 pb-1.5 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center">
                                <i class="fa-solid fa-dolly mr-1.5 text-cdp-blue text-xs"></i>Kesiapan Armada &amp; Gate AIDC
                            </h3>
                            <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">4 Unit Aktif</span>
                        </div>
                        <div class="space-y-2 py-0.5 flex-1 flex flex-col justify-between">
                            <!-- 2x2 Grid Heavy Fleet SCADA -->
                            <div class="grid grid-cols-2 gap-1.5">
                                <!-- RS-01 -->
                                <div class="p-1.5 rounded-lg border border-gray-100 bg-gray-50/50 flex flex-col justify-between">
                                    <div class="flex justify-between items-center">
                                        <span class="font-mono font-bold text-[10.5px] text-gray-900 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>RS-01
                                        </span>
                                        <span class="text-[8.5px] font-bold text-blue-600 bg-blue-50 px-1 rounded">Stacking</span>
                                    </div>
                                    <p class="text-[9px] text-gray-500 truncate mt-0.5">Kalmar 45T &bull; Dani S.</p>
                                    <div class="mt-1 flex items-center justify-between text-[8.5px] text-gray-500">
                                        <span>Fuel: <strong>88%</strong></span>
                                        <span class="text-emerald-600 font-semibold">6.2 Jam</span>
                                    </div>
                                </div>
                                <!-- RS-02 -->
                                <div class="p-1.5 rounded-lg border border-gray-100 bg-gray-50/50 flex flex-col justify-between">
                                    <div class="flex justify-between items-center">
                                        <span class="font-mono font-bold text-[10.5px] text-gray-900 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>RS-02
                                        </span>
                                        <span class="text-[8.5px] font-bold text-amber-600 bg-amber-50 px-1 rounded">Transfer</span>
                                    </div>
                                    <p class="text-[9px] text-gray-500 truncate mt-0.5">Sany 45T &bull; Budi T.</p>
                                    <div class="mt-1 flex items-center justify-between text-[8.5px] text-gray-500">
                                        <span>Fuel: <strong>76%</strong></span>
                                        <span class="text-emerald-600 font-semibold">5.8 Jam</span>
                                    </div>
                                </div>
                                <!-- RS-03 -->
                                <div class="p-1.5 rounded-lg border border-gray-100 bg-gray-50/50 flex flex-col justify-between">
                                    <div class="flex justify-between items-center">
                                        <span class="font-mono font-bold text-[10.5px] text-gray-900 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>RS-03
                                        </span>
                                        <span class="text-[8.5px] font-bold text-gray-600 bg-gray-100 px-1 rounded">Standby</span>
                                    </div>
                                    <p class="text-[9px] text-gray-500 truncate mt-0.5">Kalmar 45T &bull; Eko W.</p>
                                    <div class="mt-1 flex items-center justify-between text-[8.5px] text-gray-500">
                                        <span>Fuel: <strong>94%</strong></span>
                                        <span class="text-blue-600 font-semibold">3.1 Jam</span>
                                    </div>
                                </div>
                                <!-- RTG-01 -->
                                <div class="p-1.5 rounded-lg border border-gray-100 bg-gray-50/50 flex flex-col justify-between">
                                    <div class="flex justify-between items-center">
                                        <span class="font-mono font-bold text-[10.5px] text-gray-900 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-pulse"></span>RTG-01
                                        </span>
                                        <span class="text-[8.5px] font-bold text-purple-600 bg-purple-50 px-1 rounded">Siding KA</span>
                                    </div>
                                    <p class="text-[9px] text-gray-500 truncate mt-0.5">Konecranes &bull; Yanto P.</p>
                                    <div class="mt-1 flex items-center justify-between text-[8.5px] text-gray-500">
                                        <span>Fuel: <strong>82%</strong></span>
                                        <span class="text-purple-600 font-semibold">7.4 Jam</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Gate OCR & Sensor Health -->
                            <div class="pt-1 border-t border-gray-100 flex items-center justify-between text-[9.5px]">
                                <span class="text-gray-600 flex items-center gap-1">
                                    <i class="fa-solid fa-camera text-blue-500 text-[9px]"></i>OCR Cam: <strong class="text-emerald-600">99.4%</strong>
                                </span>
                                <span class="text-gray-600 flex items-center gap-1">
                                    <i class="fa-solid fa-weight-scale text-purple-500 text-[9px]"></i>VGM Scale: <strong class="text-emerald-600">Kalibrasi OK</strong>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Col 3: Revenue Breakdown Stacked Bar Chart -->
                    <div class="bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3 sm:p-3.5 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2 pb-1.5 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center">
                                <i class="fa-solid fa-sack-dollar mr-1.5 text-cdp-blue text-xs"></i>Pendapatan Layanan (7 Hari)
                            </h3>
                            <span class="text-[9px] font-bold text-gray-400 bg-gray-50 px-1.5 py-0.5 rounded">Juta Rp</span>
                        </div>
                        <!-- Stacked Bar Chart Container -->
                        <div class="h-36 sm:h-40 relative w-full flex-1">
                            <canvas id="revenueStackedChart"></canvas>
                        </div>
                        <!-- Service Shares Summary -->
                        <div class="mt-2 pt-1.5 border-t border-gray-50 flex items-center justify-between text-[9.5px] text-gray-500">
                            <span>Lo-Lo: <strong>45%</strong></span>
                            <span>Storage: <strong>30%</strong></span>
                            <span>Reefer: <strong>15%</strong></span>
                            <span>VGM: <strong>10%</strong></span>
                        </div>
                    </div>

                </div>

                <!-- Row 3: Live Event Stream & Shipper Consignment Tracking Table -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 sm:gap-3.5">
                    
                    <!-- Left: Log Peristiwa Terminal Real-Time (4 Cols) -->
                    <div class="lg:col-span-4 bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3 sm:p-3.5 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2 pb-1.5 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center">
                                <i class="fa-solid fa-satellite mr-1.5 text-cdp-blue text-xs"></i>Log Lapangan Real-Time
                            </h3>
                            <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">
                                <i class="fa-solid fa-bolt mr-0.5 text-[8px]"></i>Auto Sync
                            </span>
                        </div>
                        
                        <!-- Compact Timeline -->
                        <div class="relative border-l-2 border-gray-100 ml-2 space-y-2.5 py-1 flex-1">
                            <?php if (!empty($db_stats['recent_events'])): ?>
                                <?php foreach ($db_stats['recent_events'] as $evt): 
                                    $type = strtoupper($evt['event_type'] ?? 'EVENT');
                                    $dot_color = 'bg-blue-500';
                                    $tag_badge = 'bg-blue-50 text-blue-700';
                                    if (strpos($type, 'GATE_IN') !== false) {
                                        $dot_color = 'bg-blue-500';
                                        $tag_badge = 'bg-blue-50 text-blue-700';
                                    } elseif (strpos($type, 'GATE_OUT') !== false) {
                                        $dot_color = 'bg-emerald-500';
                                        $tag_badge = 'bg-emerald-50 text-emerald-700';
                                    } elseif (strpos($type, 'RELOCATION') !== false || strpos($type, 'LIFT') !== false) {
                                        $dot_color = 'bg-amber-500';
                                        $tag_badge = 'bg-amber-50 text-amber-700';
                                    } elseif (strpos($type, 'REEFER') !== false) {
                                        $dot_color = 'bg-cyan-500';
                                        $tag_badge = 'bg-cyan-50 text-cyan-700';
                                    } elseif (strpos($type, 'CFS') !== false || strpos($type, 'STRIP') !== false || strpos($type, 'STUFF') !== false) {
                                        $dot_color = 'bg-indigo-500';
                                        $tag_badge = 'bg-indigo-50 text-indigo-700';
                                    } else {
                                        $dot_color = 'bg-purple-500';
                                        $tag_badge = 'bg-purple-50 text-purple-700';
                                    }
                                    $time_str = !empty($evt['created_at']) ? date('H:i', strtotime($evt['created_at'])) : '--:--';
                                    $ctr_num = !empty($evt['container_number']) ? htmlspecialchars($evt['container_number']) : '-';
                                    $notes = !empty($evt['notes']) ? htmlspecialchars($evt['notes']) : 'Aktivitas lapangan tercatat.';
                                    if (mb_strlen($notes) > 75) {
                                        $notes = mb_substr($notes, 0, 72) . '...';
                                    }
                                    $operator = !empty($evt['operator_name']) ? htmlspecialchars($evt['operator_name']) : (!empty($evt['equipment_id']) ? htmlspecialchars($evt['equipment_id']) : 'Sistem Operasi');
                                ?>
                                <div class="relative pl-4">
                                    <div class="timeline-dot <?= $dot_color ?> ring-2 ring-white"></div>
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold text-gray-800 text-[11px]"><?= htmlspecialchars(str_replace('_', ' ', $type)) ?> <span class="font-mono <?= $tag_badge ?> px-1 rounded text-[10px]"><?= $ctr_num ?></span></span>
                                        <span class="text-[9.5px] text-gray-400 font-mono"><?= $time_str ?> WIB</span>
                                    </div>
                                    <p class="text-[10px] text-gray-500 mt-0.5" title="<?= htmlspecialchars($evt['notes'] ?? '') ?>"><?= $notes ?></p>
                                    <span class="text-[9px] text-gray-400 block mt-0.5"><i class="fa-solid fa-user-gear text-[8px] mr-1"></i><?= $operator ?></span>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-4 text-gray-400 text-xs">Belum ada aktivitas lapangan tercatat.</div>
                            <?php endif; ?>
                        </div>

                        <!-- Footer Link -->
                        <div class="pt-2 border-t border-gray-100 text-center">
                            <a href="dashboard.php?page=kontainer" class="text-[10.5px] font-bold text-cdp-blue hover:text-cdp-navy inline-flex items-center gap-1 transition">
                                <span>Buka Riwayat Operasional Lengkap</span>
                                <i class="fa-solid fa-arrow-right text-[9px]"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Right: Pelacakan Konsinyasi Muatan Klien (8 Cols) -->
                    <div class="lg:col-span-8 bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3 sm:p-3.5 flex flex-col justify-between">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 mb-2 pb-1.5 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center">
                                <i class="fa-solid fa-list-check mr-1.5 text-cdp-blue text-xs"></i>Pelacakan Konsinyasi Muatan Klien (Shipper Consignments)
                            </h3>
                            <div class="flex items-center gap-1.5">
                                <span class="text-[9px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200">
                                    <i class="fa-solid fa-wifi mr-0.5 text-[8px]"></i>RFID UHF ISO 18000-6C
                                </span>
                                <a href="dashboard.php?page=kontainer" class="text-[10px] font-bold text-cdp-blue hover:underline">
                                    Semua Data &rarr;
                                </a>
                            </div>
                        </div>

                        <!-- Compact Table -->
                        <div class="overflow-x-auto flex-1">
                            <table class="w-full text-left border-collapse min-w-[620px]">
                                <thead>
                                    <tr class="bg-gray-50/70 text-gray-500 text-[10px] border-y border-gray-100">
                                        <th class="py-2 px-2.5 font-bold uppercase">No. Kontainer</th>
                                        <th class="py-2 px-2.5 font-bold uppercase">Tipe &amp; ISO</th>
                                        <th class="py-2 px-2.5 font-bold uppercase">Tag RFID</th>
                                        <th class="py-2 px-2.5 font-bold uppercase">Status</th>
                                        <th class="py-2 px-2.5 font-bold uppercase">Posisi Lapangan</th>
                                        <th class="py-2 px-2.5 font-bold uppercase text-right">Pembaruan</th>
                                    </tr>
                                </thead>
                                <tbody class="text-[11px] divide-y divide-gray-100">
                                    <?php if (!empty($db_stats['recent_containers'])): ?>
                                        <?php foreach ($db_stats['recent_containers'] as $c): 
                                            $blk = trim($c['block'] ?? '');
                                            $bay = trim($c['bay'] ?? '');
                                            $row = trim($c['row'] ?? '');
                                            $tier = trim($c['tier'] ?? '');
                                            if (!empty($blk)) {
                                                $pos = 'Blok ' . $blk . (!empty($bay) ? ' / ' . $bay . '-' . $row . '-' . $tier : '');
                                            } else {
                                                $pos = 'Gate Area / Transisi';
                                            }
                                            $c_status = $c['status'] ?? 'in_yard';
                                            $status_badge = 'bg-blue-50 text-blue-700 border-blue-200';
                                            $status_label = ucwords(str_replace('_', ' ', $c_status));
                                            if ($c_status === 'in_yard') {
                                                $status_badge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                                $status_label = 'In Yard (Stack)';
                                            } elseif ($c_status === 'gate_out') {
                                                $status_badge = 'bg-gray-100 text-gray-700 border-gray-200';
                                                $status_label = 'Gate-Out (Rilis)';
                                            } elseif ($c_status === 'gate_in') {
                                                $status_badge = 'bg-blue-50 text-blue-700 border-blue-200';
                                                $status_label = 'Gate-In (Antrean)';
                                            }
                                            $time_disp = !empty($c['created_at']) ? date('H:i', strtotime($c['created_at'])) . ' WIB' : '--:-- WIB';
                                            $rfid_disp = !empty($c['rfid_tag']) ? htmlspecialchars($c['rfid_tag']) : 'RFID-AUTO-' . substr(md5($c['container_number']), 0, 5);
                                            $size_disp = htmlspecialchars(($c['size_type'] ?? '40FT') . (!empty($c['iso_code']) ? ' (' . $c['iso_code'] . ')' : ''));
                                        ?>
                                        <tr class="hover:bg-blue-50/20 transition">
                                            <td class="py-2 px-2.5 font-mono font-bold text-[#0170b9]">
                                                <a href="dashboard.php?page=kontainer&search=<?= urlencode($c['container_number']) ?>" class="hover:underline flex items-center gap-1">
                                                    <?= htmlspecialchars($c['container_number']) ?>
                                                    <i class="fa-solid fa-arrow-up-right-from-square text-[8px] text-gray-400"></i>
                                                </a>
                                            </td>
                                            <td class="py-2 px-2.5 text-gray-600"><?= $size_disp ?></td>
                                            <td class="py-2 px-2.5 font-mono text-[10px] text-purple-700 font-semibold"><?= $rfid_disp ?></td>
                                            <td class="py-2 px-2.5"><span class="<?= $status_badge ?> border px-1.5 py-0.5 rounded text-[9.5px] font-bold"><?= $status_label ?></span></td>
                                            <td class="py-2 px-2.5 font-semibold text-gray-800"><?= htmlspecialchars($pos) ?></td>
                                            <td class="py-2 px-2.5 text-gray-400 text-right text-[10px]"><?= $time_disp ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center py-4 text-gray-400">Tidak ada kontainer terdaftar.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- Row 4: Panoramic 35 Ha Zone Radar & Host-to-Host Integration Matrix (Anti-Pusing Visual Showcase) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 sm:gap-3.5">
                    
                    <!-- Left: Radar 4-Zona Fasilitas Terminal 35 Ha (7 Cols) -->
                    <div class="lg:col-span-7 bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3 sm:p-3.5 flex flex-col justify-between">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 mb-2 pb-1.5 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center">
                                <i class="fa-solid fa-map-location-dot mr-1.5 text-cdp-blue text-xs"></i>Radar 4 Zona Fasilitas Terminal 35 Ha (Distribusi Spasial)
                            </h3>
                            <a href="dashboard.php?page=denah" class="text-[10px] font-bold text-cdp-blue hover:underline flex items-center gap-1">
                                <span>Buka Denah 35 Ha Lengkap</span>
                                <i class="fa-solid fa-arrow-up-right-from-square text-[8px]"></i>
                            </a>
                        </div>
                        
                        <!-- 4-Zone Quadrant Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 flex-1 py-1">
                            <!-- Quadrant 1: Gerbang & Buffer Truk -->
                            <div class="p-2.5 rounded-lg border border-amber-100 bg-amber-50/30 flex flex-col justify-between">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-6 h-6 rounded-md bg-amber-500/20 text-amber-700 flex items-center justify-center text-[10px] font-bold">Z1</div>
                                        <div>
                                            <span class="font-bold text-gray-800 text-[11px]">Gerbang &amp; Buffer Truk</span>
                                            <p class="text-[9px] text-gray-500">4.2 Ha &bull; 2 In / 2 Out Gate</p>
                                        </div>
                                    </div>
                                    <span class="px-1.5 py-0.2 rounded text-[8.5px] font-bold bg-emerald-100 text-emerald-800">Lancar</span>
                                </div>
                                <div class="mt-2 space-y-1">
                                    <div class="flex justify-between text-[9px] text-gray-600">
                                        <span>Antrean Gerbang:</span>
                                        <span class="font-bold text-amber-800"><?= $db_stats['total_gate_trucks'] ?> Truk Aktif</span>
                                    </div>
                                    <div class="w-full bg-gray-200/80 rounded-full h-1 overflow-hidden">
                                        <div class="bg-amber-500 h-1 rounded-full" style="width: 35%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quadrant 2: Container Yard 12.500 TEU -->
                            <div class="p-2.5 rounded-lg border border-blue-100 bg-blue-50/30 flex flex-col justify-between">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-6 h-6 rounded-md bg-blue-500/20 text-blue-700 flex items-center justify-center text-[10px] font-bold">Z2</div>
                                        <div>
                                            <span class="font-bold text-gray-800 text-[11px]">Stacking Yard Blok A-C</span>
                                            <p class="text-[9px] text-gray-500">16.5 Ha &bull; Kapasitas 12.500 TEU</p>
                                        </div>
                                    </div>
                                    <span class="px-1.5 py-0.2 rounded text-[8.5px] font-bold bg-blue-100 text-blue-800">Operasi</span>
                                </div>
                                <div class="mt-2 space-y-1">
                                    <div class="flex justify-between text-[9px] text-gray-600">
                                        <span>Okupansi Lapangan:</span>
                                        <span class="font-bold text-blue-800"><?= $db_stats['containers_in_yard'] ?> Box (<?= $yard_pct ?>%)</span>
                                    </div>
                                    <div class="w-full bg-gray-200/80 rounded-full h-1 overflow-hidden">
                                        <div class="bg-blue-600 h-1 rounded-full" style="width: <?= $yard_pct ?>%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quadrant 3: Gudang CFS & Rampa D1-D5 -->
                            <div class="p-2.5 rounded-lg border border-indigo-100 bg-indigo-50/30 flex flex-col justify-between">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-6 h-6 rounded-md bg-indigo-500/20 text-indigo-700 flex items-center justify-center text-[10px] font-bold">Z3</div>
                                        <div>
                                            <span class="font-bold text-gray-800 text-[11px]">Gudang CFS Rampa D1-D5</span>
                                            <p class="text-[9px] text-gray-500">6.8 Ha &bull; Luas 4.000m²</p>
                                        </div>
                                    </div>
                                    <span class="px-1.5 py-0.2 rounded text-[8.5px] font-bold bg-indigo-100 text-indigo-800">Konsolidasi</span>
                                </div>
                                <div class="mt-2 space-y-1">
                                    <div class="flex justify-between text-[9px] text-gray-600">
                                        <span>Tugas Stripping/Stuffing:</span>
                                        <span class="font-bold text-indigo-800"><?= $db_stats['cfs_active_jobs'] ?> Jobs Berjalan</span>
                                    </div>
                                    <div class="w-full bg-gray-200/80 rounded-full h-1 overflow-hidden">
                                        <div class="bg-indigo-600 h-1 rounded-full" style="width: 80%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quadrant 4: Siding KA & Reefer Terminal -->
                            <div class="p-2.5 rounded-lg border border-cyan-100 bg-cyan-50/30 flex flex-col justify-between">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-6 h-6 rounded-md bg-cyan-500/20 text-cyan-700 flex items-center justify-center text-[10px] font-bold">Z4</div>
                                        <div>
                                            <span class="font-bold text-gray-800 text-[11px]">Siding KA &amp; Reefer Cold Chain</span>
                                            <p class="text-[9px] text-gray-500">7.5 Ha &bull; Siding 2x600m &bull; 300 Plugs</p>
                                        </div>
                                    </div>
                                    <span class="px-1.5 py-0.2 rounded text-[8.5px] font-bold bg-cyan-100 text-cyan-800">Cold Chain</span>
                                </div>
                                <div class="mt-2 space-y-1">
                                    <div class="flex justify-between text-[9px] text-gray-600">
                                        <span>Marechal 380V Telemetri:</span>
                                        <span class="font-bold text-cyan-800"><?= $db_stats['reefer_count'] ?> Box / -20.2&deg;C</span>
                                    </div>
                                    <div class="w-full bg-gray-200/80 rounded-full h-1 overflow-hidden">
                                        <div class="bg-cyan-500 h-1 rounded-full" style="width: 75%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Micro Footer Bar -->
                        <div class="mt-2 pt-1.5 border-t border-gray-100 flex items-center justify-between text-[10px] text-gray-500">
                            <span>Total Luas Master: <strong>35 Hektar (CIDP Cikarang)</strong></span>
                            <span class="text-emerald-700 font-semibold flex items-center gap-1"><i class="fa-solid fa-check-double text-[9px]"></i> 26 BOM Hardware Terpasang</span>
                        </div>
                    </div>

                    <!-- Right: Matriks Integrasi Host-to-Host & AIDC GS1 AI (5 Cols) -->
                    <div class="lg:col-span-5 bg-white rounded-xl shadow-2xs border border-gray-200/80 p-3 sm:p-3.5 flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-2 pb-1.5 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center">
                                <i class="fa-solid fa-network-wired mr-1.5 text-cdp-blue text-xs"></i>Matriks Konektivitas Host-to-Host (API Live)
                            </h3>
                            <a href="dashboard.php?page=settings" class="text-[9px] font-bold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 hover:bg-slate-200 transition">
                                Pengaturan v2.4 &rarr;
                            </a>
                        </div>

                        <!-- 5 Live API Gateway Connectors -->
                        <div class="space-y-1.5 py-0.5 flex-1 flex flex-col justify-around text-[10px]">
                            <!-- Gateway 1: CEISA 4.0 -->
                            <div class="p-1.5 rounded-lg border border-gray-100 bg-gray-50/70 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <div>
                                        <span class="font-bold text-gray-800">Bea Cukai CEISA 4.0</span>
                                        <p class="text-[8.5px] text-gray-400">Host-to-Host REST API &bull; SPPB Auto-Clearance</p>
                                    </div>
                                </div>
                                <span class="font-mono text-[9px] text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded font-bold border border-emerald-200">ONLINE (42ms)</span>
                            </div>

                            <!-- Gateway 2: Inaportnet Kemenhub -->
                            <div class="p-1.5 rounded-lg border border-gray-100 bg-gray-50/70 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <div>
                                        <span class="font-bold text-gray-800">Inaportnet Kemenhub V2</span>
                                        <p class="text-[8.5px] text-gray-400">National Logistics Ecosystem (NLE) Sinkronisasi</p>
                                    </div>
                                </div>
                                <span class="font-mono text-[9px] text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded font-bold border border-emerald-200">ONLINE (58ms)</span>
                            </div>

                            <!-- Gateway 3: KAI Logistik EDI -->
                            <div class="p-1.5 rounded-lg border border-gray-100 bg-gray-50/70 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <div>
                                        <span class="font-bold text-gray-800">Kereta Api Logistik EDI</span>
                                        <p class="text-[8.5px] text-gray-400">Jadwal Siding KA 2518 &amp; Manifest PPCW</p>
                                    </div>
                                </div>
                                <span class="font-mono text-[9px] text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded font-bold border border-emerald-200">ONLINE (35ms)</span>
                            </div>

                            <!-- Gateway 4: Marechal Reefer IoT -->
                            <div class="p-1.5 rounded-lg border border-gray-100 bg-gray-50/70 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse"></span>
                                    <div>
                                        <span class="font-bold text-gray-800">Marechal IoT Gateway</span>
                                        <p class="text-[8.5px] text-gray-400">LoRaWAN 915MHz &bull; 300 Marechal Plugs Pulse</p>
                                    </div>
                                </div>
                                <span class="font-mono text-[9px] text-cyan-700 bg-cyan-50 px-1.5 py-0.5 rounded font-bold border border-cyan-200">PULSE (110ms)</span>
                            </div>

                            <!-- Gateway 5: GS1 SSCC AI Station -->
                            <div class="p-1.5 rounded-lg border border-gray-100 bg-gray-50/70 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-purple-500 animate-pulse"></span>
                                    <div>
                                        <span class="font-bold text-gray-800">GS1 SSCC &amp; RFID AIDC</span>
                                        <p class="text-[8.5px] text-gray-400">ISO 18000-6C &bull; Optical Vision Decoder</p>
                                    </div>
                                </div>
                                <span class="font-mono text-[9px] text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded font-bold border border-purple-200">99.8% AKURAT</span>
                            </div>
                        </div>

                        <!-- Micro Footer Bar -->
                        <div class="mt-2 pt-1.5 border-t border-gray-100 flex items-center justify-between text-[10px] text-gray-500">
                            <span>Protokol Keamanan: <strong>TLS 1.3 / OAuth2.0</strong></span>
                            <span class="text-emerald-700 font-semibold">Semua Layanan Aktif</span>
                        </div>
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
                <?php elseif ($page === 'gate'): ?>
                    <?php include_once __DIR__ . '/pages/gate.php'; ?>
                <?php elseif ($page === 'denah'): ?>
                    <?php include_once __DIR__ . '/pages/denah.php'; ?>
                <?php elseif ($page === 'intermodal'): ?>
                    <?php include_once __DIR__ . '/pages/intermodal.php'; ?>
                <?php elseif ($page === 'yard'): ?>
                    <?php include_once __DIR__ . '/pages/yard.php'; ?>
                <?php elseif ($page === 'reefer'): ?>
                    <?php include_once __DIR__ . '/pages/reefer.php'; ?>
                <?php elseif ($page === 'cfs'): ?>
                    <?php include_once __DIR__ . '/pages/cfs.php'; ?>
                <?php elseif ($page === 'billing'): ?>
                    <?php include_once __DIR__ . '/pages/billing.php'; ?>
                <?php elseif ($page === 'scanner'): ?>
                    <?php include_once __DIR__ . '/pages/scanner.php'; ?>
                <?php elseif ($page === 'customs'): ?>
                    <?php include_once __DIR__ . '/pages/customs.php'; ?>
                <?php elseif ($page === 'settings'): ?>
                    <?php include_once __DIR__ . '/pages/settings.php'; ?>
                <?php else: ?>
                <!-- Fallback: Halaman Tidak Ditemukan -->
                <div class="bg-white rounded-2xl p-8 sm:p-12 shadow-sm border border-gray-100 text-center flex flex-col items-center justify-center max-w-2xl mx-auto my-8 animate-fadeIn">
                    <div class="w-20 h-20 bg-red-50 text-red-500 rounded-2xl flex items-center justify-center mb-5 shadow-inner text-3xl">
                        <i class="fa-solid fa-circle-exclamation"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Halaman Tidak Ditemukan</h2>
                    <p class="text-gray-600 text-sm leading-relaxed mb-6">Modul yang Anda cari tidak tersedia atau belum terdaftar dalam sistem.</p>
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

        // =====================================================================
        // INTERAKSI DROPDOWN NOTIFIKASI & PROFIL EKSEKUTIF
        // =====================================================================
        function toggleNotifDropdown() {
            const notifCard = document.getElementById('notifDropdownCard');
            const profileCard = document.getElementById('profileDropdownCard');
            const profileChevron = document.getElementById('profileChevron');
            
            // Tutup profil jika sedang terbuka
            if (profileCard && !profileCard.classList.contains('hidden')) {
                profileCard.classList.add('hidden');
                if (profileChevron) profileChevron.classList.remove('rotate-180');
            }
            
            if (notifCard) {
                notifCard.classList.toggle('hidden');
            }
        }

        function markAllNotificationsRead() {
            const badge = document.getElementById('notifBadge');
            const countPill = document.getElementById('notifCountPill');
            const unreadDots = document.querySelectorAll('.notif-unread-dot');
            const notifItems = document.querySelectorAll('.notif-item');
            
            if (badge) badge.classList.add('hidden');
            if (countPill) {
                countPill.textContent = '0 Baru';
                countPill.className = 'px-2 py-0.5 rounded-full bg-slate-500/80 text-white text-[10px] font-bold font-mono';
            }
            unreadDots.forEach(d => d.classList.add('hidden'));
            notifItems.forEach(item => item.classList.remove('bg-blue-50/20'));
        }

        function toggleProfileDropdown() {
            const profileCard = document.getElementById('profileDropdownCard');
            const notifCard = document.getElementById('notifDropdownCard');
            const profileChevron = document.getElementById('profileChevron');
            
            // Tutup notifikasi jika sedang terbuka
            if (notifCard && !notifCard.classList.contains('hidden')) {
                notifCard.classList.add('hidden');
            }
            
            if (profileCard) {
                const isHidden = profileCard.classList.toggle('hidden');
                if (profileChevron) {
                    if (!isHidden) {
                        profileChevron.classList.add('rotate-180');
                    } else {
                        profileChevron.classList.remove('rotate-180');
                    }
                }
            }
        }

        // Event Listener: Tutup dropdown saat klik di luar area trigger
        document.addEventListener('click', function(event) {
            const notifWrapper = document.getElementById('notifDropdownWrapper');
            const notifCard = document.getElementById('notifDropdownCard');
            const profileWrapper = document.getElementById('profileDropdownWrapper');
            const profileCard = document.getElementById('profileDropdownCard');
            const profileChevron = document.getElementById('profileChevron');
            
            if (notifWrapper && !notifWrapper.contains(event.target)) {
                if (notifCard) notifCard.classList.add('hidden');
            }
            
            if (profileWrapper && !profileWrapper.contains(event.target)) {
                if (profileCard) profileCard.classList.add('hidden');
                if (profileChevron) profileChevron.classList.remove('rotate-180');
            }
        });

        // Event Listener: Tutup dropdown saat tombol Escape ditekan
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const notifCard = document.getElementById('notifDropdownCard');
                const profileCard = document.getElementById('profileDropdownCard');
                const profileChevron = document.getElementById('profileChevron');
                
                if (notifCard) notifCard.classList.add('hidden');
                if (profileCard) profileCard.classList.add('hidden');
                if (profileChevron) profileChevron.classList.remove('rotate-180');
            }
        });

        // =====================================================================
        // JAM REAL-TIME OPERASIONAL TERMINAL (WIB / GMT+7)
        // =====================================================================
        function updateRealTimeClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const timeString = `${hours}:${minutes}:${seconds} WIB`;
            
            const navClock = document.getElementById('navLiveClock');
            if (navClock) {
                navClock.textContent = timeString;
            }
            
            const berandaClock = document.getElementById('berandaLiveClock');
            if (berandaClock) {
                berandaClock.textContent = timeString;
            }
        }
        
        // Jalankan seketika dan perbarui setiap detik
        updateRealTimeClock();
        setInterval(updateRealTimeClock, 1000);
    </script>
    
    <!-- Chart.js Initializations -->
    <?php if ($page === 'beranda'): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Chart.defaults.font.family = '"Plus Jakarta Sans", sans-serif';
            Chart.defaults.font.size = 10;
            Chart.defaults.color = '#64748b';
            Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0, 47, 94, 0.92)';
            Chart.defaults.plugins.tooltip.padding = 8;
            Chart.defaults.plugins.tooltip.cornerRadius = 6;
            Chart.defaults.plugins.tooltip.titleFont = { size: 11, weight: 'bold' };
            Chart.defaults.plugins.tooltip.bodyFont = { size: 10 };
            
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
                                backgroundColor: 'rgba(1, 112, 185, 0.08)',
                                fill: true,
                                tension: 0.35,
                                borderWidth: 1.75,
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: '#0170b9',
                                pointBorderWidth: 1.5,
                                pointRadius: 2.5,
                                pointHoverRadius: 4.5
                            },
                            {
                                label: 'Gate-Out',
                                data: [15, 20, 18, 21, 23, 14, 10],
                                borderColor: '#4f46e5', // indigo-600
                                backgroundColor: 'transparent',
                                fill: false,
                                tension: 0.35,
                                borderWidth: 1.75,
                                borderDash: [4, 4],
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: '#4f46e5',
                                pointBorderWidth: 1.5,
                                pointRadius: 2.5,
                                pointHoverRadius: 4.5
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
                                labels: { usePointStyle: true, boxWidth: 6, padding: 6, font: { size: 9.5 } }
                            }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true, 
                                grid: { borderDash: [2, 4], color: '#f1f5f9' },
                                ticks: { font: { size: 9 } }
                            },
                            x: { 
                                grid: { display: false },
                                ticks: { font: { size: 9 } }
                            }
                        }
                    }
                });
            }

            // 2. Doughnut Chart: Distribusi Tipe Kontainer
            const typeCtx = document.getElementById('containerTypeChart');
            if (typeCtx) {
                <?php
                    $dry_num = max(1, (int)$db_stats['containers_in_yard'] - (int)$db_stats['reefer_count']);
                    $rf_num = max(1, (int)$db_stats['reefer_count']);
                    $empty_num = 2;
                    $dg_num = 1;
                    $tot_d = $dry_num + $rf_num + $empty_num + $dg_num;
                ?>
                new Chart(typeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: [
                            'Dry (<?= round(($dry_num / $tot_d) * 100) ?>%)', 
                            'Reefer (<?= round(($rf_num / $tot_d) * 100) ?>%)', 
                            'Empty (<?= round(($empty_num / $tot_d) * 100) ?>%)', 
                            'DG/IMO (<?= round(($dg_num / $tot_d) * 100) ?>%)'
                        ],
                        datasets: [{
                            data: [<?= $dry_num ?>, <?= $rf_num ?>, <?= $empty_num ?>, <?= $dg_num ?>],
                            backgroundColor: ['#0170b9', '#06b6d4', '#94a3b8', '#ef4444'],
                            borderWidth: 0,
                            hoverOffset: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { usePointStyle: true, boxWidth: 6, padding: 4, font: { size: 9 } }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.label;
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
                            const centerY = (chartArea.top + chartArea.bottom) / 2 - 8;

                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';

                            // Main count
                            ctx.font = "bold 0.85rem 'Plus Jakarta Sans', sans-serif";
                            ctx.fillStyle = "#0f172a";
                            ctx.fillText("<?= (int)$db_stats['containers_in_yard'] ?> Box", centerX, centerY - 4);

                            // Subtitle
                            ctx.font = "600 0.55rem 'Plus Jakarta Sans', sans-serif";
                            ctx.fillStyle = "#64748b";
                            ctx.fillText("Total Yard", centerX, centerY + 8);
                            ctx.restore();
                        }
                    }]
                });
            }

            // 3. Stacked Bar Chart: Pendapatan per Jenis Layanan
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
                                borderRadius: {topLeft: 0, topRight: 0, bottomLeft: 3, bottomRight: 3}
                            },
                            {
                                label: 'Storage',
                                data: [8.2, 9.5, 8.8, 10.2, 11.5, 9.2, 12.6],
                                backgroundColor: '#4f46e5', // indigo
                            },
                            {
                                label: 'VGM',
                                data: [3.4, 4.1, 3.2, 4.8, 5.2, 2.8, 5.5],
                                backgroundColor: '#a855f7', // purple
                            },
                            {
                                label: 'Reefer',
                                data: [5.6, 5.8, 5.5, 6.2, 6.5, 5.2, 7.1],
                                backgroundColor: '#06b6d4', // cyan
                                borderRadius: {topLeft: 3, topRight: 3, bottomLeft: 0, bottomRight: 0}
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
                                labels: { usePointStyle: true, boxWidth: 6, padding: 5, font: { size: 9 } }
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
                            x: { 
                                stacked: true, 
                                grid: { display: false },
                                ticks: { font: { size: 9 } }
                            },
                            y: { 
                                stacked: true, 
                                beginAtZero: true, 
                                grid: { borderDash: [2, 4], color: '#f1f5f9' },
                                ticks: {
                                    font: { size: 8.5 },
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
