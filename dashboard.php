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
    
    <!-- FontAwesome 6 (Offline Local Vendor) -->
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    
    <!-- Chart.js (Offline Local Vendor) -->
    <script src="assets/vendor/chart.umd.min.js"></script>
    
    <!-- Tailwind CSS (Offline Local Vendor) -->
    <script src="assets/vendor/tailwindcss.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        // Palet Maritim CIDP: Selaras 100% dengan Landing Page (login.php)
                        'cdp-navy': '#004b87',
                        'cdp-dark': '#002f5e',
                        'cdp-blue': '#0170b9',
                        'cdp-light': '#f4f8fc',
                        'ink': '#002f5e',
                        'ink-2': '#002447',
                        'paper': '#ffffff',
                        'brass': '#0170b9',
                        'brass-deep': '#004b87',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; background-color: #f4f8fc; }
        .sidebar-transition { transition: width 0.3s ease-in-out; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        #sidebar nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); }
        #sidebar nav::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.3); }
        .timeline-dot { position: absolute; left: -5px; top: 5px; width: 10px; height: 10px; border-radius: 50%; }
        @keyframes fadeInScale {
            from { opacity: 0; transform: translateY(-8px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .animate-fadeIn {
            animation: fadeInScale 0.16s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
    <!-- SheetJS for Excel Export (Offline Local Vendor) -->
    <script src="assets/vendor/xlsx.full.min.js"></script>
    <!-- jsPDF for PDF Export (Offline Local Vendor) -->
    <script src="assets/vendor/jspdf.umd.min.js"></script>
    <script src="assets/vendor/jspdf.plugin.autotable.min.js"></script>
    <!-- CIDP Export Utilities -->
    <script src="assets/js/export-utils.js"></script>
</head>
<body class="text-gray-800 antialiased min-h-screen h-[100dvh] overflow-hidden flex bg-cdp-light">

    <!-- Sidebar Component -->
    <?php include_once __DIR__ . '/components/sidebar.php'; ?>

    <!-- Main Wrapper -->
    <div class="flex-1 flex flex-col h-full overflow-hidden relative">
        
        <!-- Header & Top Navigation Component -->
        <?php include_once __DIR__ . '/components/header.php'; ?>

        <!-- Main Content Scrollable Area (Symmetrical Container) -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-cdp-light relative">
            <div class="p-3.5 sm:p-5 lg:p-8 w-full max-w-[1680px] mx-auto space-y-4 sm:space-y-5">
                
                <?php if ($page === 'beranda'): ?>
                    <?php include_once __DIR__ . '/pages/beranda.php'; ?>
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
            
            <!-- Footer Component -->
            <?php include_once __DIR__ . '/components/footer.php'; ?>
        </main>
    </div>

    <!-- Dashboard UI & Live Clock Scripts -->
    <script src="assets/js/dashboard.js"></script>
</body>
</html>
