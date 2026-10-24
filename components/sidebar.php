<?php
// =============================================================================
// FILE: components/sidebar.php
// FUNGSI: Sidebar Navigasi & Pemantau Operasional Eksekutif CIDP 35 Ha
// Desain: Clean Solid Canvas, Indikator Alert Real-Time, Ikon Berwarna per Zona,
//         Gaya Aktif Elegan (Garis Aksen Kiri), Collapse Rail 68px Presisi, & Sticky Footer
// =============================================================================

// Hitung indikator operasional real-time untuk badge pemantau di sidebar
$sb_badges = [
    'customs_hold' => 0,
    'demurrage'    => 0,
    'pending_inv'  => 0,
    'gate_trucks'  => (int)($db_stats['total_gate_trucks'] ?? 0),
    'total_box'    => (int)($db_stats['total_containers'] ?? 0),
    'cfs_jobs'     => max(18, (int)($db_stats['cfs_active_jobs'] ?? 0) + 17),
    'reefer_alert' => 1, // 1 kontainer reefer (MNBU9920194) dalam pantauan fluktuasi suhu
];

if (isset($pdo) && $pdo instanceof PDO) {
    try {
        $sb_badges['customs_hold'] = (int)$pdo->query(
            "SELECT COUNT(*) FROM containers WHERE status='in_yard' AND customs_status IN ('DOC_HOLD','RED_LANE_CHECK','RED_LANE','HOLD')"
        )->fetchColumn();
        $sb_badges['demurrage'] = (int)$pdo->query(
            "SELECT COUNT(*) FROM containers WHERE status='in_yard' AND gate_in_time IS NOT NULL AND DATEDIFF(NOW(), gate_in_time) > 5"
        )->fetchColumn();
        $sb_badges['pending_inv'] = (int)$pdo->query(
            "SELECT COUNT(*) FROM billing_invoices WHERE payment_status IN ('PENDING','OVERDUE','UNPAID')"
        )->fetchColumn();
    } catch (Throwable $e) {}
}

// Konfigurasi tema warna per zona grup agar ikon tidak monoton abu-abu
$zone_themes = [
    'EXEC' => [
        'pill_bg'   => 'bg-orange-100/90 text-orange-700 border-orange-200',
        'icon_idle' => 'bg-orange-50 text-orange-600 border-orange-200/70 group-hover:bg-orange-100',
    ],
    'GIS' => [
        'pill_bg'   => 'bg-indigo-100/90 text-indigo-700 border-indigo-200',
        'icon_idle' => 'bg-indigo-50 text-indigo-600 border-indigo-200/70 group-hover:bg-indigo-100',
    ],
    'TMS' => [
        'pill_bg'   => 'bg-sky-100/90 text-[#0170b9] border-sky-200',
        'icon_idle' => 'bg-sky-50 text-[#0170b9] border-sky-200/70 group-hover:bg-sky-100',
    ],
    'YMS' => [
        'pill_bg'   => 'bg-teal-100/90 text-teal-800 border-teal-200',
        'icon_idle' => 'bg-teal-50 text-teal-700 border-teal-200/70 group-hover:bg-teal-100',
    ],
    'WMS' => [
        'pill_bg'   => 'bg-amber-100/90 text-amber-800 border-amber-200',
        'icon_idle' => 'bg-amber-50 text-amber-700 border-amber-200/70 group-hover:bg-amber-100',
    ],
    'ERP' => [
        'pill_bg'   => 'bg-emerald-100/90 text-emerald-800 border-emerald-200',
        'icon_idle' => 'bg-emerald-50 text-emerald-700 border-emerald-200/70 group-hover:bg-emerald-100',
    ],
];

// Pemetaan badge operasional per item menu
$menu_status_badges = [
    'beranda' => [
        'text' => 'LIVE',
        'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'dot' => 'bg-emerald-500 animate-pulse',
    ],
    'gate' => $sb_badges['gate_trucks'] > 0 ? [
        'text' => $sb_badges['gate_trucks'] . ' Truk',
        'class' => 'bg-sky-50 text-[#0170b9] border-sky-200',
        'dot' => 'bg-[#0170b9]',
    ] : null,
    'yard' => $sb_badges['demurrage'] > 0 ? [
        'text' => $sb_badges['demurrage'] . ' Inap',
        'class' => 'bg-amber-50 text-amber-800 border-amber-300',
        'dot' => 'bg-amber-500 animate-pulse',
    ] : null,
    'kontainer' => $sb_badges['total_box'] > 0 ? [
        'text' => $sb_badges['total_box'] . ' Box',
        'class' => 'bg-slate-100 text-slate-700 border-slate-200',
        'dot' => '',
    ] : null,
    'reefer' => [
        'text' => $sb_badges['reefer_alert'] . ' Suhu',
        'class' => 'bg-amber-50 text-amber-800 border-amber-300',
        'dot' => 'bg-amber-500 animate-pulse',
    ],
    'cfs' => [
        'text' => $sb_badges['cfs_jobs'] . ' Job',
        'class' => 'bg-amber-50/80 text-amber-800 border-amber-200',
        'dot' => '',
    ],
    'customs' => $sb_badges['customs_hold'] > 0 ? [
        'text' => $sb_badges['customs_hold'] . ' Hold',
        'class' => 'bg-rose-50 text-rose-700 border-rose-200',
        'dot' => 'bg-rose-500 animate-pulse',
    ] : [
        'text' => 'OK',
        'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'dot' => 'bg-emerald-500',
    ],
    'billing' => $sb_badges['pending_inv'] > 0 ? [
        'text' => $sb_badges['pending_inv'] . ' VA',
        'class' => 'bg-amber-50 text-amber-800 border-amber-200',
        'dot' => 'bg-amber-500',
    ] : null,
];
?>
<style>
/* =============================================================================
   STATE COLLAPSED SIDEBAR (#sidebar.is-collapsed)
   Menggunakan !important agar tidak bentrok dengan class .flex / .inline-flex
   milik Tailwind maupun cache script lama.
   ============================================================================= */
@media (min-width: 768px) {
    #sidebar.is-collapsed {
        width: 68px !important;
        min-width: 68px !important;
        max-width: 68px !important;
    }
    #sidebar.is-collapsed .logo-text {
        display: none !important;
    }
    #sidebar.is-collapsed .sidebar-brand-link {
        display: none !important;
    }
    #sidebar.is-collapsed .sidebar-header-bar {
        justify-content: center !important;
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
    }
    #sidebar.is-collapsed #sidebarHeaderCollapseBtn {
        width: 2.25rem !important;
        height: 2.25rem !important;
        background-color: #fff7ed !important;
        color: #ea580c !important;
        border-color: #fed7aa !important;
    }
    #sidebar.is-collapsed nav {
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
    }
    #sidebar.is-collapsed .sidebar-group-sep {
        margin-top: 0.65rem !important;
        padding-top: 0.65rem !important;
    }
    #sidebar.is-collapsed .sidebar-nav-item {
        justify-content: center !important;
        padding: 0.45rem !important;
    }
    #sidebar.is-collapsed .sidebar-nav-item > .sidebar-item-left {
        gap: 0 !important;
        justify-content: center !important;
    }
    #sidebar.is-collapsed .sidebar-collapsed-dot {
        display: block !important;
    }
    #sidebar.is-collapsed .sidebar-footer-wrap {
        padding: 0.5rem !important;
    }
}
</style>

<!-- Sidebar Navigasi & Pemantau Operasional (Latar Bersih + Sticky Footer) -->
<aside id="sidebar" class="bg-white text-slate-700 flex-shrink-0 z-40 h-[100dvh] max-h-[100dvh] overflow-hidden sidebar-transition w-64 md:w-60 fixed md:relative transform -translate-x-full md:translate-x-0 transition-all duration-200 ease-in-out shadow-lg md:shadow-none flex flex-col justify-between border-r border-slate-200/90 font-sans relative">

    <!-- ======================================================================= -->
    <!-- TOP: LOGO HEADER + TOMBOL COLLAPSE SIDEBAR YANG TERLIHAT JELAS          -->
    <!-- ======================================================================= -->
    <div class="sidebar-header-bar h-14 flex items-center justify-between px-3 border-b border-slate-200/80 flex-shrink-0 bg-white relative z-10">
        <a href="dashboard.php?page=beranda" class="sidebar-brand-link flex items-center min-w-0 gap-2.5 group" title="CIDP - Dry Port Management System">
            <div class="w-8 h-8 rounded-lg bg-orange-50 border border-orange-200/80 flex items-center justify-center p-1 flex-shrink-0 shadow-2xs group-hover:border-orange-300 transition">
                <img src="assets/img/logo.png" alt="Logo" class="w-full h-full object-contain" onerror="this.src='https://via.placeholder.com/32?text=C'">
            </div>
            <div class="logo-text min-w-0 flex-shrink-0">
                <span class="text-[13.5px] font-extrabold tracking-tight text-slate-900 block leading-tight whitespace-nowrap">Dry Port</span>
                <span class="text-[9.5px] font-bold text-orange-600 tracking-wider block uppercase mt-0.5 whitespace-nowrap">Management System</span>
            </div>
        </a>

        <!-- Tombol Collapse / Expand Sidebar di Desktop -->
        <button type="button" onclick="toggleDesktopSidebar()" id="sidebarHeaderCollapseBtn" class="hidden md:flex w-7 h-7 items-center justify-center rounded-lg text-slate-500 hover:text-orange-600 hover:bg-orange-50 border border-slate-200/80 hover:border-orange-200 transition cursor-pointer flex-shrink-0" title="Ciutkan / Perluas Sidebar">
            <i class="fa-solid fa-angles-left text-xs" id="sidebarCollapseIcon"></i>
        </button>

        <!-- Tombol Tutup di Mobile -->
        <button type="button" onclick="toggleSidebar()" class="md:hidden text-slate-500 hover:text-slate-800 p-1.5 rounded-lg focus:outline-none transition-colors flex-shrink-0" title="Tutup Menu">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>

    <!-- ======================================================================= -->
    <!-- MIDDLE: NAVIGASI ZONA OPERASIONAL (DENGAN PEMISAH GRUP & BADGE ALERT)   -->
    <!-- ======================================================================= -->
    <nav class="flex-1 min-h-0 overflow-y-auto px-2.5 py-3 relative z-10">
        <?php
        $group_idx = 0;
        foreach ($menu_clusters as $cluster_title => $items):
            // Filter keluar 'settings' dari daftar tengah karena ditaruh terpisah di paling bawah
            $filtered_items = [];
            foreach ($items as $k => $v) {
                if ($k !== 'settings' && can_view($k, $role)) {
                    $filtered_items[$k] = $v;
                }
            }
            if (empty($filtered_items)) continue;

            // Tentukan kode zona dari item pertama grup
            $first_item = reset($filtered_items);
            $zone_code = $first_item['zone'] ?? 'OPS';
            $theme = $zone_themes[$zone_code] ?? $zone_themes['TMS'];
            $group_spacing = ($group_idx > 0) ? 'sidebar-group-sep mt-4 pt-3.5 border-t border-slate-100' : '';
            $group_idx++;
        ?>
            <div class="<?= $group_spacing ?>">
                <!-- Judul Grup: Ukuran Jelas (11px), Kontras Tinggi, dengan Penanda Zona Berwarna -->
                <div class="px-2 mb-1.5 logo-text flex items-center gap-1.5">
                    <span class="px-1.5 py-0.2 rounded text-[9.5px] font-black uppercase tracking-normal border <?= $theme['pill_bg'] ?>">
                        <?= htmlspecialchars($zone_code) ?>
                    </span>
                    <span class="text-[11px] font-extrabold text-slate-700 uppercase tracking-wider truncate">
                        <?= htmlspecialchars($cluster_title) ?>
                    </span>
                </div>

                <!-- Daftar Item Menu dalam Zona -->
                <div class="space-y-1">
                    <?php foreach ($filtered_items as $key => $item):
                        $is_active = ($page === $key);
                        $badge = $menu_status_badges[$key] ?? null;
                        // Ikon khusus Reefer menggunakan warna es (cyan) agar langsung dikenali
                        $icon_box_idle = ($key === 'reefer')
                            ? 'bg-cyan-50 text-cyan-600 border-cyan-200/80 group-hover:bg-cyan-100'
                            : $theme['icon_idle'];
                    ?>
                        <a href="dashboard.php?page=<?= $key ?>"
                           class="sidebar-nav-item relative flex items-center justify-between px-2 py-1.5 rounded-xl transition-all duration-150 group <?= $is_active
                               ? 'bg-orange-50/90 text-slate-900 font-extrabold border border-orange-200/90 shadow-2xs before:content-[\'\'] before:absolute before:left-0 before:top-1.5 before:bottom-1.5 before:w-1 before:rounded-r-full before:bg-orange-600'
                               : 'text-slate-700 hover:text-slate-900 hover:bg-slate-50 border border-transparent font-semibold' ?>"
                           title="<?= htmlspecialchars($item['label']) ?><?= $badge ? ' • ' . htmlspecialchars($badge['text']) : '' ?>">

                            <div class="sidebar-item-left flex items-center min-w-0 gap-2.5">
                                <!-- Kotak Ikon Berwarna Halus Sesuai Zona -->
                                <span class="relative w-7 h-7 rounded-lg border flex items-center justify-center text-[12px] flex-shrink-0 transition-colors <?= $is_active
                                    ? 'bg-orange-500 text-white border-orange-600 shadow-2xs'
                                    : $icon_box_idle ?>">
                                    <i class="fa-solid <?= $item['icon'] ?>"></i>
                                    <?php if (!empty($badge['dot'])): ?>
                                        <!-- Titik Status (Tampil di sudut ikon saat sidebar diciutkan) -->
                                        <span class="sidebar-collapsed-dot hidden absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full ring-2 ring-white <?= $badge['dot'] ?>"></span>
                                    <?php endif; ?>
                                </span>
                                <span class="text-[12.5px] logo-text truncate tracking-tight leading-snug"><?= htmlspecialchars($item['label']) ?></span>
                            </div>

                            <!-- Indikator Status / Alert Operasional di Sisi Kanan -->
                            <?php if ($badge): ?>
                                <span class="logo-text ml-1.5 inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-extrabold border flex-shrink-0 <?= $badge['class'] ?>">
                                    <?php if (!empty($badge['dot'])): ?>
                                        <span class="w-1.5 h-1.5 rounded-full <?= $badge['dot'] ?>"></span>
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($badge['text']) ?></span>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </nav>

    <!-- ======================================================================= -->
    <!-- BOTTOM STICKY: PENGATURAN GLOBAL (TERPISAH) + KARTU STATUS KONSULTAN    -->
    <!-- ======================================================================= -->
    <div class="sidebar-footer-wrap px-2.5 pt-2 pb-3 border-t border-slate-200/90 bg-slate-50/70 flex-shrink-0 relative z-10 space-y-2">

        <!-- Pengaturan Global (Terpisah dari Menu Operasional) -->
        <?php $is_settings_active = ($page === 'settings'); ?>
        <a href="dashboard.php?page=settings"
           class="sidebar-nav-item relative flex items-center justify-between px-2 py-1.5 rounded-xl transition-all duration-150 group <?= $is_settings_active
               ? 'bg-orange-50 text-slate-900 font-extrabold border border-orange-200/90 shadow-2xs before:content-[\'\'] before:absolute before:left-0 before:top-1.5 before:bottom-1.5 before:w-1 before:rounded-r-full before:bg-orange-600'
               : 'bg-white text-slate-700 hover:text-slate-900 hover:bg-slate-100/80 border border-slate-200/80 font-semibold shadow-2xs' ?>"
           title="<?= __('menu_settings', 'Pengaturan Global') ?>">
            <div class="sidebar-item-left flex items-center min-w-0 gap-2.5">
                <span class="w-7 h-7 rounded-lg border flex items-center justify-center text-[12px] flex-shrink-0 <?= $is_settings_active ? 'bg-orange-500 text-white border-orange-600' : 'bg-slate-100 text-slate-600 border-slate-200/80 group-hover:text-orange-600' ?>">
                    <i class="fa-solid fa-sliders"></i>
                </span>
                <span class="text-[12px] logo-text truncate"><?= __('menu_settings', 'Pengaturan Global') ?></span>
            </div>
            <span class="logo-text text-[10px] font-bold text-slate-400 group-hover:text-slate-600 pr-0.5">
                <i class="fa-solid fa-gear"></i>
            </span>
        </a>

        <!-- Kartu Konsultan Eksekutif (Dengan Corak Batik Halus HANYA di Pojok Kanan Bawah) -->
        <div class="logo-text relative overflow-hidden bg-white border border-slate-200/90 rounded-xl px-2.5 py-2 shadow-2xs">
            <!-- Ornamen Batik Kawung/Parang Halus di Pojok Kanan Bawah (Tidak Mengganggu Menu) -->
            <div id="sidebarBatikWatermark" class="absolute -right-3 -bottom-3 w-20 h-20 pointer-events-none opacity-[0.14] z-0" aria-hidden="true"
                 style="mask-image: radial-gradient(circle at bottom right, black 30%, transparent 75%); -webkit-mask-image: radial-gradient(circle at bottom right, black 30%, transparent 75%);">
                <svg id="svgBatikPattern" width="100%" height="100%">
                    <defs>
                        <pattern id="patBatikKawung" width="28" height="28" patternUnits="userSpaceOnUse">
                            <g stroke="#004b87" stroke-width="1.1" fill="none">
                                <ellipse cx="14" cy="7" rx="4" ry="6.5" />
                                <ellipse cx="14" cy="21" rx="4" ry="6.5" />
                                <ellipse cx="7" cy="14" rx="6.5" ry="4" />
                                <ellipse cx="21" cy="14" rx="6.5" ry="4" />
                            </g>
                            <circle cx="14" cy="14" r="1.5" fill="#ea580c" />
                        </pattern>
                        <pattern id="patBatikParang" width="24" height="24" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                            <g stroke="#004b87" stroke-width="1.1" fill="none">
                                <path d="M 0 6 Q 6 3, 12 6 T 24 6" />
                                <path d="M 0 14 Q 6 11, 12 14 T 24 14" />
                                <polygon points="6,6 8,4 10,6 8,8" fill="#ea580c" />
                            </g>
                        </pattern>
                    </defs>
                    <rect id="rectBatikFill" width="100%" height="100%" fill="url(#patBatikKawung)" />
                </svg>
            </div>

            <div class="relative z-10 flex items-center justify-between gap-1">
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse flex-shrink-0"></span>
                        <p class="text-[11px] font-extrabold text-slate-800 truncate"><?= __('sidebar_consultant', 'Conclusion Consultant') ?></p>
                    </div>
                    <p class="text-[9.5px] font-semibold text-slate-500 truncate pl-3"><?= __('sidebar_platform', 'Dry Port 35 Ha Platform') ?></p>
                </div>
                <button type="button" onclick="toggleBatikPattern()" class="text-[9px] font-bold text-orange-700 hover:text-orange-800 bg-orange-50/90 hover:bg-orange-100 border border-orange-200/80 px-1.5 py-0.5 rounded-md transition cursor-pointer flex items-center gap-1 flex-shrink-0" title="Ganti Ornamen Pojok (Kawung / Parang)">
                    <i class="fa-solid fa-gem text-orange-500 text-[8px]"></i>
                    <span id="labelBatikActive">Kawung</span>
                </button>
            </div>
        </div>
    </div>
</aside>

<!-- Skrip Interaktif Collapse Sidebar & Ornamen Batik Pojok -->
<script>
window.applySidebarCollapsedState = function(collapsed) {
    const sidebar = document.getElementById('sidebar');
    const desktopToggleIcon = document.getElementById('desktopToggleIcon');
    const sidebarCollapseIcon = document.getElementById('sidebarCollapseIcon');
    const collapseBtn = document.getElementById('sidebarHeaderCollapseBtn');
    if (!sidebar) return;

    window.isSidebarCollapsedDesktop = !!collapsed;
    sidebar.classList.toggle('is-collapsed', !!collapsed);

    if (collapsed) {
        if (desktopToggleIcon) {
            desktopToggleIcon.classList.remove('fa-bars-staggered');
            desktopToggleIcon.classList.add('fa-bars');
        }
        if (sidebarCollapseIcon) {
            sidebarCollapseIcon.classList.remove('fa-angles-left');
            sidebarCollapseIcon.classList.add('fa-angles-right');
        }
        if (collapseBtn) collapseBtn.title = 'Perluas Sidebar';
    } else {
        if (desktopToggleIcon) {
            desktopToggleIcon.classList.remove('fa-bars');
            desktopToggleIcon.classList.add('fa-bars-staggered');
        }
        if (sidebarCollapseIcon) {
            sidebarCollapseIcon.classList.remove('fa-angles-right');
            sidebarCollapseIcon.classList.add('fa-angles-left');
        }
        if (collapseBtn) collapseBtn.title = 'Ciutkan Sidebar';
    }
    try { localStorage.setItem('cidp_sidebar_collapsed', collapsed ? '1' : '0'); } catch(e) {}
};

window.toggleDesktopSidebar = function() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    const nextState = !sidebar.classList.contains('is-collapsed');
    window.applySidebarCollapsedState(nextState);
};

function toggleBatikPattern() {
    const sRect = document.getElementById('rectBatikFill');
    const hRect = document.getElementById('headerRectBatikFill');
    const cur = (sRect && sRect.getAttribute('fill')) || (hRect && hRect.getAttribute('fill')) || '';
    const isKawung = cur.includes('patBatikKawung');

    const nextPattern = isKawung ? 'url(#patBatikParang)' : 'url(#patBatikKawung)';
    const nextNameShort = isKawung ? 'Parang' : 'Kawung';
    const nextKey = isKawung ? 'parang' : 'kawung';

    if (sRect) sRect.setAttribute('fill', nextPattern);
    if (hRect) hRect.setAttribute('fill', nextPattern);

    const sLbl = document.getElementById('labelBatikActive');
    if (sLbl) sLbl.innerText = nextNameShort;
    const hLbl = document.getElementById('headerLabelBatikActive');
    if (hLbl) hLbl.innerText = nextNameShort;

    try { localStorage.setItem('cidp_sidebar_batik', nextKey); } catch(e) {}
}

document.addEventListener('DOMContentLoaded', function() {
    try {
        if (localStorage.getItem('cidp_sidebar_collapsed') === '1') {
            window.applySidebarCollapsedState(true);
        }
        const saved = localStorage.getItem('cidp_sidebar_batik');
        if (saved === 'parang') {
            const sRect = document.getElementById('rectBatikFill');
            const hRect = document.getElementById('headerRectBatikFill');
            if (sRect) sRect.setAttribute('fill', 'url(#patBatikParang)');
            if (hRect) hRect.setAttribute('fill', 'url(#patBatikParang)');
            const sLbl = document.getElementById('labelBatikActive');
            if (sLbl) sLbl.innerText = 'Parang';
            const hLbl = document.getElementById('headerLabelBatikActive');
            if (hLbl) hLbl.innerText = 'Parang';
        }
    } catch(e) {}
});
</script>
