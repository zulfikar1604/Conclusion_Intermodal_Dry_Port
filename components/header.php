<?php
// =============================================================================
// FILE: components/header.php
// FUNGSI: Top Navigation Header Eksekutif CIDP 35 Ha
// DESAIN: Clean Executive Minimalist - Seamless, Airy, Unified with Sidebar
// Proyek: Conclusion Intermodal Dry Port (CIDP 35 Hectares DPMS)
// =============================================================================
?>
<!-- Overlay for mobile sidebar -->
<div id="sidebarOverlay" class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs z-30 hidden md:hidden transition-opacity" onclick="toggleSidebar()"></div>

<!-- Top Header (Aligned with sidebar header: h-14 / 56px, Seamless Background & Border) -->
<header class="h-14 bg-white/95 backdrop-blur-md border-b border-slate-200/80 z-20 flex-shrink-0 sticky top-0 font-sans relative">
    <div class="h-full w-full flex items-center justify-between px-3 sm:px-4 lg:px-5 xl:px-6">
        
        <!-- LEFT: Toggles & Clean Unified Breadcrumbs -->
        <div class="flex items-center min-w-0 mr-2 sm:mr-3 xl:mr-4 flex-shrink">
            <!-- Mobile Toggle -->
            <button onclick="toggleSidebar()" class="mr-2 sm:mr-3 p-1.5 text-slate-500 hover:text-orange-600 focus:outline-none md:hidden transition-colors rounded-lg hover:bg-slate-100 cursor-pointer flex-shrink-0" title="Buka Menu Navigasi">
                <i class="fa-solid fa-bars text-base"></i>
            </button>
            <!-- Desktop Toggle -->
            <button onclick="toggleDesktopSidebar()" class="mr-2 sm:mr-2.5 p-1.5 text-slate-500 hover:text-orange-600 focus:outline-none hidden md:block transition-colors rounded-lg hover:bg-slate-100 cursor-pointer flex-shrink-0" title="Kecilkan / Perbesar Sidebar">
                <i class="fa-solid fa-bars-staggered text-sm" id="desktopToggleIcon"></i>
            </button>
            
            <!-- Clean Unified Breadcrumbs -->
            <div class="flex items-center space-x-1.5 sm:space-x-2 text-xs min-w-0 truncate">
                <span class="text-slate-400 font-medium hidden xl:inline truncate flex-shrink-0">
                    <?= htmlspecialchars($current_cluster) ?>
                </span>
                <i class="fa-solid fa-chevron-right text-[8.5px] text-slate-300 hidden xl:inline flex-shrink-0"></i>
                <div class="flex items-center space-x-1.5 sm:space-x-2 truncate min-w-0">
                    <span class="text-orange-600 text-xs flex-shrink-0">
                        <i class="fa-solid <?= $menu_items[$page]['icon'] ?? 'fa-cube' ?>"></i>
                    </span>
                    <h1 class="text-xs sm:text-sm font-bold text-slate-800 tracking-tight capitalize truncate">
                        <?= isset($menu_items[$page]) ? $menu_items[$page]['label'] : 'Dashboard' ?>
                    </h1>
                </div>
            </div>
        </div>

        <!-- CENTER: Clean Universal Quick Search Bar (Ctrl+K Command Palette) -->
        <div class="hidden md:flex items-center flex-1 max-w-[180px] lg:max-w-[240px] xl:max-w-sm 2xl:max-w-md mx-2 lg:mx-3 xl:mx-4 relative min-w-0" id="globalSearchWrapper">
            <form action="dashboard.php" method="GET" class="w-full relative flex items-center" id="globalSearchForm">
                <input type="hidden" name="page" value="kontainer">
                <div class="absolute left-2.5 text-slate-400 pointer-events-none flex items-center">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
                <input type="text" name="search" id="globalQuickSearch" 
                       placeholder="Cari kontainer (Ctrl+K)..." 
                       autocomplete="off"
                       onfocus="showSearchSuggestions()"
                       class="w-full pl-8 pr-12 py-1.5 bg-slate-100/70 hover:bg-slate-100 focus:bg-white text-xs text-slate-800 placeholder-slate-400 rounded-lg border border-slate-200/70 focus:border-[#0170b9] focus:ring-2 focus:ring-[#0170b9]/15 transition-all outline-none">
                <div class="absolute right-2 flex items-center space-x-1 pointer-events-none">
                    <kbd class="px-1.5 py-0.5 text-[9px] font-bold font-mono text-slate-400 bg-white border border-slate-200/80 rounded shadow-2xs">Ctrl+K</kbd>
                </div>
            </form>
            
            <!-- Quick Suggestions Flyout -->
            <div id="searchSuggestionsFlyout" class="hidden absolute top-10 left-0 right-0 bg-white rounded-xl shadow-2xl border border-slate-200/90 p-3 z-50 animate-fadeIn text-xs">
                <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                    <span><i class="fa-solid fa-bolt text-orange-500 mr-1"></i> Pintasan Akses Cepat</span>
                    <span class="text-slate-400 font-mono">Esc untuk tutup</span>
                </div>
                <div class="space-y-1">
                    <a href="dashboard.php?page=kontainer&search=MSKU9182374" class="flex items-center justify-between p-1.5 rounded-lg hover:bg-slate-50 transition group">
                        <span class="flex items-center text-slate-700 font-mono text-[11px] group-hover:text-[#0170b9]">
                            <i class="fa-solid fa-box text-blue-500 mr-2 text-[10px]"></i> MSKU9182374
                        </span>
                        <span class="text-[9.5px] text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded font-mono">40ft HC &bull; Yard B3</span>
                    </a>
                    <a href="dashboard.php?page=gate&search=B 9481 UEK" class="flex items-center justify-between p-1.5 rounded-lg hover:bg-slate-50 transition group">
                        <span class="flex items-center text-slate-700 font-mono text-[11px] group-hover:text-[#0170b9]">
                            <i class="fa-solid fa-truck text-emerald-500 mr-2 text-[10px]"></i> B 9481 UEK
                        </span>
                        <span class="text-[9.5px] text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded font-bold">Gate-In Sukses</span>
                    </a>
                    <a href="dashboard.php?page=intermodal" class="flex items-center justify-between p-1.5 rounded-lg hover:bg-slate-50 transition group">
                        <span class="flex items-center text-slate-700 font-mono text-[11px] group-hover:text-[#0170b9]">
                            <i class="fa-solid fa-train text-purple-500 mr-2 text-[10px]"></i> KA 2518 Pasoso
                        </span>
                        <span class="text-[9.5px] text-purple-600 bg-purple-50 px-1.5 py-0.5 rounded font-bold">Siding Track-01</span>
                    </a>
                    <a href="dashboard.php?page=reefer" class="flex items-center justify-between p-1.5 rounded-lg hover:bg-slate-50 transition group">
                        <span class="flex items-center text-slate-700 font-mono text-[11px] group-hover:text-[#0170b9]">
                            <i class="fa-solid fa-snowflake text-cyan-500 mr-2 text-[10px]"></i> Plug #04 Marechal
                        </span>
                        <span class="text-[9.5px] text-cyan-600 bg-cyan-50 px-1.5 py-0.5 rounded font-mono">-20.2°C Stabil</span>
                    </a>
                    <a href="dashboard.php?page=customs" class="flex items-center justify-between p-1.5 rounded-lg hover:bg-slate-50 transition group">
                        <span class="flex items-center text-slate-700 font-mono text-[11px] group-hover:text-[#0170b9]">
                            <i class="fa-solid fa-shield-halved text-indigo-500 mr-2 text-[10px]"></i> SPPB CEISA 4.0
                        </span>
                        <span class="text-[9.5px] text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded font-bold">Jalur Hijau</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- RIGHT: Executive Utilities (Calm, Airy, Professional) -->
        <div class="flex items-center space-x-2 sm:space-x-3 flex-shrink-0">
            
            <!-- Mobile Search Icon Trigger -->
            <button onclick="toggleMobileSearch()" class="p-2 text-slate-500 hover:text-slate-800 focus:outline-none md:hidden transition-colors rounded-lg hover:bg-slate-100 cursor-pointer" title="Cari Kontainer / Truk">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </button>

            <!-- Fullscreen Toggle Button -->
            <button onclick="toggleFullScreen()" class="w-8 h-8 hidden sm:flex items-center justify-center text-slate-500 hover:text-slate-800 transition-colors rounded-lg hover:bg-slate-100 focus:outline-none cursor-pointer" title="Layar Penuh (F11)">
                <i class="fa-solid fa-expand text-xs" id="fullscreenIcon"></i>
            </button>

            <!-- Real-Time Operational Clock (Clean subtle monospace) - Hidden on laptop < xl -->
            <div class="hidden xl:flex items-center space-x-1.5 text-xs text-slate-500 font-mono font-medium px-2 py-1 rounded-md" title="Waktu Nyata Operasional Terminal CIDP (WIB)">
                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                <span id="navLiveClock">--:--:-- WIB</span>
            </div>

            <!-- Language Switcher (Clean minimal toggle) -->
            <div class="flex items-center bg-slate-100/80 rounded-lg p-0.5 text-[11px] font-bold" title="Pilihan Bahasa Sistem">
                <a href="dashboard.php?page=<?= htmlspecialchars($page) ?>&lang=id" onclick="localStorage.setItem('cidp_lang', 'id')" class="px-2 py-0.5 rounded-md transition-all <?= ($current_lang === 'id') ? 'bg-white text-slate-800 shadow-2xs font-extrabold' : 'text-slate-500 hover:text-slate-800' ?>">
                    ID
                </a>
                <a href="dashboard.php?page=<?= htmlspecialchars($page) ?>&lang=en" onclick="localStorage.setItem('cidp_lang', 'en')" class="px-2 py-0.5 rounded-md transition-all <?= ($current_lang === 'en') ? 'bg-white text-slate-800 shadow-2xs font-extrabold' : 'text-slate-500 hover:text-slate-800' ?>">
                    EN
                </a>
            </div>

            <!-- Notification Bell & Categorized Dropdown Card -->
            <div class="relative" id="notifDropdownWrapper">
                <button onclick="toggleNotifDropdown()" id="notifTriggerBtn" class="relative w-8 h-8 flex items-center justify-center text-slate-500 hover:text-slate-800 transition-colors rounded-lg hover:bg-slate-100 focus:outline-none cursor-pointer" title="Notifikasi Sistem Operasional Yard">
                    <i class="fa-regular fa-bell text-sm" id="notifBellIcon"></i>
                    <span id="notifBadge" class="absolute top-1.5 right-1.5 w-2 h-2 bg-orange-500 rounded-full ring-2 ring-white animate-pulse"></span>
                </button>

                <!-- Notification Dropdown Card -->
                <div id="notifDropdownCard" class="hidden absolute right-0 sm:right-[-40px] md:right-0 top-11 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-gray-200/90 overflow-hidden z-50 animate-fadeIn text-xs">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-[#0170b9] via-[#0284c7] to-orange-600 p-3.5 text-white flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <i class="fa-solid fa-bell text-orange-200 text-xs"></i>
                            <span class="font-bold text-sm">Notifikasi Sistem Yard</span>
                            <span id="notifCountPill" class="px-2 py-0.5 rounded-full bg-gradient-to-r from-orange-500 to-orange-600 text-white text-[10px] font-bold font-mono shadow-xs">4 Baru</span>
                        </div>
                        <button onclick="markAllNotificationsRead()" class="text-[11px] text-blue-100 hover:text-white transition font-medium flex items-center space-x-1 cursor-pointer">
                            <i class="fa-solid fa-check-double text-[10px]"></i>
                            <span>Tandai Dibaca</span>
                        </button>
                    </div>

                    <!-- Category Filter Tabs -->
                    <div class="px-3 py-1.5 bg-slate-50 border-b border-gray-100 flex items-center space-x-1">
                        <button onclick="filterNotifCategory('all')" data-category="all" class="notif-filter-tab px-2 py-0.5 rounded-md text-[10px] font-bold bg-white text-orange-600 shadow-2xs border border-orange-200/60 transition cursor-pointer">
                            Semua (4)
                        </button>
                        <button onclick="filterNotifCategory('logistics')" data-category="logistics" class="notif-filter-tab px-2 py-0.5 rounded-md text-[10px] font-semibold text-slate-500 hover:text-slate-800 transition cursor-pointer">
                            Logistik (2)
                        </button>
                        <button onclick="filterNotifCategory('customs')" data-category="customs" class="notif-filter-tab px-2 py-0.5 rounded-md text-[10px] font-semibold text-slate-500 hover:text-slate-800 transition cursor-pointer">
                            Bea Cukai (1)
                        </button>
                        <button onclick="filterNotifCategory('iot')" data-category="iot" class="notif-filter-tab px-2 py-0.5 rounded-md text-[10px] font-semibold text-slate-500 hover:text-slate-800 transition cursor-pointer">
                            IoT (1)
                        </button>
                    </div>

                    <!-- Notification Feed List -->
                    <div id="notifFeedList" class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                        <!-- Item 1: Intermodal -->
                        <a href="dashboard.php?page=intermodal" data-category="logistics" class="notif-item notif-feed-item p-3.5 flex items-start space-x-3 hover:bg-blue-50/40 transition group bg-blue-50/20">
                            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0 mt-0.5 group-hover:scale-105 transition-transform">
                                <i class="fa-solid fa-train text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-gray-900 group-hover:text-orange-600 transition-colors">KA 2518 Tiba di Siding Track-01</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500 notif-unread-dot flex-shrink-0"></span>
                                </div>
                                <p class="text-gray-600 text-[11px] mt-0.5 leading-relaxed">
                                    Sensor Frauscher memvalidasi 126 as roda (30 gerbong PPCW). Siap alih muat 48 TEU.
                                </p>
                                <span class="text-[10px] text-gray-400 mt-1 block font-mono">2 menit lalu &bull; Intermodal Siding</span>
                            </div>
                        </a>

                        <!-- Item 2: Gate Management -->
                        <a href="dashboard.php?page=gate" data-category="logistics" class="notif-item notif-feed-item p-3.5 flex items-start space-x-3 hover:bg-orange-50/40 transition group bg-orange-50/20">
                            <div class="w-8 h-8 rounded-xl bg-orange-100 text-orange-700 flex items-center justify-center flex-shrink-0 mt-0.5 group-hover:scale-105 transition-transform">
                                <i class="fa-solid fa-truck text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-gray-900 group-hover:text-orange-600 transition-colors">Truk B 9481 UEK Gate-In Sukses</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500 notif-unread-dot flex-shrink-0"></span>
                                </div>
                                <p class="text-gray-600 text-[11px] mt-0.5 leading-relaxed">
                                    Peti kemas MSKU9182374 lolos timbangan VGM 28,4 Ton & diverifikasi RFID UHF.
                                </p>
                                <span class="text-[10px] text-gray-400 mt-1 block font-mono">14 menit lalu &bull; Gate In 02</span>
                            </div>
                        </a>

                        <!-- Item 3: Reefer Monitor -->
                        <a href="dashboard.php?page=reefer" data-category="iot" class="notif-item notif-feed-item p-3.5 flex items-start space-x-3 hover:bg-orange-50/40 transition group">
                            <div class="w-8 h-8 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center flex-shrink-0 mt-0.5 group-hover:scale-105 transition-transform">
                                <i class="fa-solid fa-snowflake text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-gray-900 group-hover:text-orange-600 transition-colors">Reefer Plug #04 Terhubung</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500 notif-unread-dot flex-shrink-0"></span>
                                </div>
                                <p class="text-gray-600 text-[11px] mt-0.5 leading-relaxed">
                                    CMAU7234910 pasokan daya 380V aktif, suhu termometer stabil pada -20,2°C.
                                </p>
                                <span class="text-[10px] text-gray-400 mt-1 block font-mono">35 menit lalu &bull; Reefer Cold Chain</span>
                            </div>
                        </a>

                        <!-- Item 4: Customs SPPB -->
                        <a href="dashboard.php?page=customs" data-category="customs" class="notif-item notif-feed-item p-3.5 flex items-start space-x-3 hover:bg-orange-50/40 transition group">
                            <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center flex-shrink-0 mt-0.5 group-hover:scale-105 transition-transform">
                                <i class="fa-solid fa-shield-halved text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-gray-900 group-hover:text-orange-600 transition-colors">SPPB Bea Cukai Terbit (Jalur Hijau)</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500 notif-unread-dot flex-shrink-0"></span>
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
                        <a href="dashboard.php?page=kontainer" class="text-[11px] font-bold text-orange-600 hover:text-orange-700 transition inline-flex items-center space-x-1">
                            <span>Lihat Seluruh Aktivitas &amp; Log Yard</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Subtle Vertical Divider -->
            <div class="h-5 w-px bg-slate-200 hidden sm:block"></div>

            <!-- Profile Trigger & Dropdown Card -->
            <div class="relative" id="profileDropdownWrapper">
                <button onclick="toggleProfileDropdown()" class="flex items-center space-x-2 py-1 px-1.5 rounded-lg hover:bg-slate-100 transition-colors group focus:outline-none cursor-pointer" id="profileTriggerBtn">
                    <!-- Avatar with subtle ring -->
                    <div class="relative flex-shrink-0">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#0170b9] to-orange-500 p-[1.5px] shadow-2xs">
                            <div class="w-full h-full rounded-full bg-white flex items-center justify-center">
                                <span class="text-[11px] font-bold text-[#0170b9]"><?= $avatar_initials ?></span>
                            </div>
                        </div>
                        <span class="absolute bottom-0 right-0 w-2 h-2 bg-emerald-500 rounded-full ring-2 ring-white"></span>
                    </div>
                    <!-- Name & Role (Desktop XL+ Only: prevents laptop clipping) -->
                    <div class="hidden xl:flex flex-col text-left max-w-[140px] truncate">
                        <span class="text-xs font-semibold text-slate-800 leading-tight truncate"><?= htmlspecialchars($display_nama) ?></span>
                        <span class="text-[10px] text-slate-400 font-medium leading-tight mt-0.5 truncate">Lead Architect</span>
                    </div>
                    <!-- Chevron -->
                    <i class="fa-solid fa-chevron-down text-[8px] text-slate-400 group-hover:text-slate-600 transition-transform duration-200 hidden xl:block" id="profileChevron"></i>
                </button>

                <!-- Profile Dropdown Card -->
                <div id="profileDropdownCard" class="hidden absolute right-0 top-12 w-72 sm:w-80 bg-white rounded-2xl shadow-2xl border border-gray-200/90 overflow-hidden z-50 animate-fadeIn text-xs">
                    <!-- Card Header -->
                    <div class="bg-gradient-to-br from-[#0170b9] via-[#0284c7] to-[#0369a1] p-4 text-white">
                        <div class="flex items-center space-x-3">
                            <div class="w-11 h-11 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center border-2 border-white/40 flex-shrink-0 shadow-inner">
                                <span class="text-base font-bold tracking-wider text-white"><?= $avatar_initials ?></span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold leading-tight truncate"><?= htmlspecialchars($display_nama) ?></p>
                                <p class="text-[10.5px] text-blue-100 truncate"><?= htmlspecialchars($email) ?></p>
                                <div class="mt-1 flex items-center space-x-1">
                                    <span class="px-2 py-0.5 bg-gradient-to-r from-orange-500 to-orange-600 rounded-full text-[9px] font-bold text-white flex items-center shadow-xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white mr-1.5 animate-pulse"></span>
                                        Lead System Architect
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Role Switcher (Simulasi Evaluator ITL Trisakti) -->
                    <div class="p-3 bg-slate-50 border-b border-gray-100">
                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1.5">
                            <i class="fa-solid fa-users-gear mr-1 text-orange-500"></i> Mode Simulasi Hak Akses
                        </span>
                        <div class="grid grid-cols-2 gap-1.5 text-[11px]">
                            <div class="bg-white border-2 border-orange-500 text-orange-700 bg-orange-50/40 font-bold p-1.5 rounded-lg flex items-center space-x-1.5 shadow-2xs">
                                <i class="fa-solid fa-user-tie text-[10px] text-orange-600"></i>
                                <span class="truncate">Lead Architect ✓</span>
                            </div>
                            <a href="dashboard.php?page=alat" class="bg-white hover:bg-orange-50/60 border border-gray-200 text-gray-600 hover:text-orange-700 p-1.5 rounded-lg flex items-center space-x-1.5 transition">
                                <i class="fa-solid fa-dolly text-[10px] text-orange-500"></i>
                                <span class="truncate">Operator RS</span>
                            </a>
                            <a href="dashboard.php?page=trucking" class="bg-white hover:bg-orange-50/60 border border-gray-200 text-gray-600 hover:text-orange-700 p-1.5 rounded-lg flex items-center space-x-1.5 transition">
                                <i class="fa-solid fa-truck-moving text-[10px] text-emerald-500"></i>
                                <span class="truncate">Supir Armada</span>
                            </a>
                            <a href="dashboard.php?page=billing" class="bg-white hover:bg-orange-50/60 border border-gray-200 text-gray-600 hover:text-orange-700 p-1.5 rounded-lg flex items-center space-x-1.5 transition">
                                <i class="fa-solid fa-building text-[10px] text-purple-500"></i>
                                <span class="truncate">Klien Shipper</span>
                            </a>
                        </div>
                    </div>

                    <!-- Card Menu -->
                    <div class="p-2 space-y-0.5">
                        <a href="dashboard.php?page=settings" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl hover:bg-orange-50 text-gray-700 hover:text-orange-600 transition-colors group">
                            <i class="fa-solid fa-gear text-xs text-gray-400 group-hover:text-orange-600 transition-colors w-4 text-center"></i>
                            <span class="font-medium">Pengaturan Akun &amp; Sistem</span>
                        </a>
                        <a href="dashboard.php?page=simulator" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl hover:bg-orange-50 text-gray-700 hover:text-orange-600 transition-colors group">
                            <i class="fa-solid fa-tower-broadcast text-xs text-gray-400 group-hover:text-orange-600 transition-colors w-4 text-center"></i>
                            <span class="font-medium">Panel Simulasi IoT &amp; Sensor</span>
                        </a>
                        <a href="dashboard.php?page=denah" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl hover:bg-orange-50 text-gray-700 hover:text-orange-600 transition-colors group">
                            <i class="fa-solid fa-map-location-dot text-xs text-gray-400 group-hover:text-orange-600 transition-colors w-4 text-center"></i>
                            <span class="font-medium">Site Plan Denah Terminal 35 Ha</span>
                        </a>

                        <div class="my-1.5 border-t border-gray-100"></div>

                        <!-- System Diagnostics Quick Info -->
                        <div class="px-3 py-1.5 bg-slate-50/80 rounded-lg text-[10px] text-slate-500 flex items-center justify-between font-mono">
                            <span class="flex items-center gap-1"><i class="fa-solid fa-database text-emerald-500"></i> DB Connected</span>
                            <span class="text-slate-400">Ping 12ms</span>
                        </div>

                        <button type="button" onclick="openLogoutConfirmModal()" class="w-full text-left flex items-center space-x-2.5 px-3 py-2 rounded-xl hover:bg-red-50 text-red-600 transition-colors group cursor-pointer">
                            <i class="fa-solid fa-right-from-bracket text-xs text-red-400 group-hover:text-red-600 transition-colors w-4 text-center"></i>
                            <span class="font-bold">Keluar dari Sistem</span>
                        </button>
                    </div>

                    <!-- Card Footer -->
                    <div class="px-4 py-2.5 bg-gray-50 border-t border-gray-100 text-center">
                        <p class="text-[9.5px] text-gray-500 font-medium">Conclusion Supply Chain Consultant &copy; 2026</p>
                        <p class="text-[9px] text-gray-400">CIDP Yard Management System &bull; 35 Ha Terminal</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Quick Search Drawer (Expandable when mobile search button clicked) -->
    <div id="mobileSearchDrawer" class="hidden md:hidden px-3.5 py-2.5 bg-white/95 backdrop-blur-md border-b border-gray-200/90 shadow-md animate-fadeIn relative z-20">
        <form action="dashboard.php" method="GET" class="relative flex items-center">
            <input type="hidden" name="page" value="kontainer">
            <i class="fa-solid fa-magnifying-glass absolute left-3 text-slate-400 text-xs"></i>
            <input type="text" name="search" id="globalQuickSearchMobile" placeholder="Cari kontainer, nopol truk, dokumen..." 
                   class="w-full pl-8 pr-8 py-1.5 bg-slate-50 text-xs text-slate-800 rounded-lg border border-slate-200 outline-none focus:border-orange-500 focus:bg-white transition-all">
            <button type="button" onclick="toggleMobileSearch()" class="absolute right-2 text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </form>
    </div>
</header>

<!-- ======================================================================= -->
<!-- MODAL KONFIRMASI KELUAR SISTEM (COMPACT LOGOUT CONFIRMATION DIALOG) -->
<!-- ======================================================================= -->
<div id="logoutConfirmModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-3.5">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-[360px] overflow-hidden text-slate-800 relative animate-fadeIn">
        <!-- Subtle Batik Kawung Rosette Watermark in Modal Background -->
        <div class="absolute -right-6 -bottom-6 w-36 h-36 pointer-events-none opacity-[0.05] z-0" aria-hidden="true">
            <svg width="100%" height="100%" viewBox="0 0 100 100" fill="none" stroke="#0170b9" stroke-width="1.6">
                <ellipse cx="50" cy="22" rx="16" ry="24" />
                <ellipse cx="50" cy="78" rx="16" ry="24" />
                <ellipse cx="22" cy="50" rx="24" ry="16" />
                <ellipse cx="78" cy="50" rx="24" ry="16" />
                <circle cx="50" cy="50" r="4.5" fill="#ea580c" />
            </svg>
        </div>

        <!-- Top Header Band -->
        <div class="bg-gradient-to-r from-[#002f5e] via-[#0170b9] to-orange-600 px-4.5 py-3 text-white flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div class="w-6 h-6 rounded-md bg-white/10 backdrop-blur-xs flex items-center justify-center border border-white/20 p-0.5">
                    <img src="assets/img/logo.png" alt="Conclusion Logo" class="w-full h-full object-contain">
                </div>
                <div>
                    <h3 class="font-extrabold text-xs leading-tight">Konfirmasi Keluar Sesi</h3>
                    <p class="text-[9px] text-blue-100 font-medium">Conclusion Intermodal DPMS</p>
                </div>
            </div>
            <button type="button" onclick="closeLogoutConfirmModal()" class="w-6 h-6 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
                <i class="fa-solid fa-xmark text-[11px]"></i>
            </button>
        </div>

        <!-- Modal Content Body -->
        <div class="p-4 relative z-10">
            <div class="flex items-start gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-orange-50 border border-orange-200 text-orange-600 flex items-center justify-center text-sm shrink-0 shadow-2xs">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </div>
                <div>
                    <h4 class="text-sm font-extrabold text-slate-900 tracking-tight">Akhiri Sesi Operasional?</h4>
                    <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                        Log kontainer dan konfigurasi SCADA Anda telah tersimpan aman di database.
                    </p>
                </div>
            </div>

            <!-- Session Info Strip -->
            <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-200/80 mb-4 text-[10.5px] space-y-1">
                <div class="flex items-center justify-between text-slate-600">
                    <span class="font-medium">User:</span>
                    <span class="font-bold text-slate-800 truncate max-w-[190px]"><?= htmlspecialchars($display_nama) ?></span>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                    <span class="font-medium">Status:</span>
                    <span class="text-emerald-600 font-semibold flex items-center gap-1"><i class="fa-solid fa-check text-[8.5px]"></i> Data Aman &amp; Tersinkron</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-2">
                <button type="button" onclick="closeLogoutConfirmModal()" class="px-3.5 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors shadow-2xs">
                    Batal
                </button>
                <a href="logout.php" class="px-4 py-2 rounded-lg bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white font-bold text-xs shadow-md shadow-orange-500/20 transition-all flex items-center gap-1.5">
                    <i class="fa-solid fa-right-from-bracket text-[11px]"></i>
                    <span>Ya, Keluar</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function openLogoutConfirmModal() {
    const modal = document.getElementById('logoutConfirmModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}
function closeLogoutConfirmModal() {
    const modal = document.getElementById('logoutConfirmModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}
window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLogoutConfirmModal();
});
</script>
