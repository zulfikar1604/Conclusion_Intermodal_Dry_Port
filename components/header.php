<?php
// =============================================================================
// FILE: components/header.php
// FUNGSI: Top Navigation Header, Live Clock, Notifikasi & Profil Dropdown
// =============================================================================
?>
<!-- Overlay for mobile sidebar -->
<div id="sidebarOverlay" class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs z-30 hidden md:hidden transition-opacity" onclick="toggleSidebar()"></div>

<!-- Top Header (Aligned with sidebar logo: h-14 / 56px) -->
<header class="h-14 bg-white/95 backdrop-blur-md border-b border-gray-200/80 z-20 flex-shrink-0 sticky top-0 shadow-2xs font-sans">
    <div class="h-full w-full max-w-[1680px] mx-auto flex items-center justify-between px-3.5 sm:px-5 lg:px-8">
        <!-- Left: Toggle & Clean Page Title -->
        <div class="flex items-center min-w-0 mr-2">
            <!-- Mobile Toggle -->
            <button onclick="toggleSidebar()" class="mr-2.5 sm:mr-3 p-1.5 text-gray-500 hover:text-orange-600 focus:outline-none md:hidden transition-colors rounded-lg hover:bg-orange-50/60" title="Buka Menu">
                <i class="fa-solid fa-bars text-base"></i>
            </button>
            <!-- Desktop Toggle -->
            <button onclick="toggleDesktopSidebar()" class="mr-2.5 sm:mr-3 p-1.5 text-gray-500 hover:text-orange-600 focus:outline-none hidden md:block transition-colors rounded-lg hover:bg-orange-50/60" title="Kecilkan/Perbesar Menu">
                <i class="fa-solid fa-bars-staggered text-base" id="desktopToggleIcon"></i>
            </button>
            <h1 class="text-sm sm:text-base font-bold text-gray-800 capitalize truncate">
                <?= isset($menu_items[$page]) ? $menu_items[$page]['label'] : 'Dashboard' ?>
            </h1>
        </div>
        
        <!-- Right: User & Actions (Executive Consultant Theme) -->
        <div class="flex items-center space-x-1.5 sm:space-x-2 flex-shrink-0">
            <!-- Real-Time Operational Live Clock -->
            <div class="flex items-center space-x-1.5 bg-slate-50 hover:bg-orange-50/50 border border-gray-200/90 hover:border-orange-200/80 rounded-lg px-2 sm:px-2.5 py-1 text-gray-700 shadow-2xs font-mono text-[10px] sm:text-[11px] transition-colors" title="Waktu Nyata Operasional Terminal CIDP (WIB)">
                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse flex-shrink-0"></span>
                <i class="fa-regular fa-clock text-orange-500 text-[10.5px]"></i>
                <span id="navLiveClock" class="font-bold tracking-tight text-gray-800 font-mono">--:--:-- WIB</span>
            </div>

            <div class="h-4 w-px bg-gray-200/80"></div>

            <!-- Notification Bell & Dropdown -->
            <div class="relative" id="notifDropdownWrapper">
                <button onclick="toggleNotifDropdown()" id="notifTriggerBtn" class="relative w-8 h-8 flex items-center justify-center text-gray-500 hover:text-orange-600 transition-all rounded-lg hover:bg-orange-50/60 focus:outline-none" title="Notifikasi Sistem Operasional Yard">
                    <i class="fa-regular fa-bell text-[13px]" id="notifBellIcon"></i>
                    <span id="notifBadge" class="absolute top-1.5 right-1.5 w-2 h-2 bg-orange-500 rounded-full ring-2 ring-white animate-pulse"></span>
                </button>

                <!-- Notification Dropdown Card -->
                <div id="notifDropdownCard" class="hidden absolute right-0 sm:right-[-40px] md:right-0 top-11 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-gray-200/90 overflow-hidden z-50 animate-fadeIn text-xs">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-[#002f5e] via-[#0170b9] to-orange-600 p-3.5 text-white flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <i class="fa-solid fa-bell text-orange-200 text-xs"></i>
                            <span class="font-bold text-sm">Notifikasi Sistem Yard</span>
                            <span id="notifCountPill" class="px-2 py-0.5 rounded-full bg-gradient-to-r from-orange-500 to-orange-600 text-white text-[10px] font-bold font-mono shadow-xs">4 Baru</span>
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
                        <a href="dashboard.php?page=gate" class="notif-item p-3.5 flex items-start space-x-3 hover:bg-orange-50/40 transition group bg-orange-50/20">
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
                        <a href="dashboard.php?page=reefer" class="notif-item p-3.5 flex items-start space-x-3 hover:bg-orange-50/40 transition group">
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
                        <a href="dashboard.php?page=customs" class="notif-item p-3.5 flex items-start space-x-3 hover:bg-orange-50/40 transition group">
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

            <div class="h-4 w-px bg-gray-200/80 mx-0.5"></div>

            <!-- Profile Trigger & Dropdown -->
            <div class="relative" id="profileDropdownWrapper">
                <button onclick="toggleProfileDropdown()" class="flex items-center space-x-2 py-1 px-1.5 sm:px-2 rounded-xl hover:bg-orange-50/60 transition-all group focus:outline-none" id="profileTriggerBtn">
                    <!-- Name & Role (desktop only) -->
                    <div class="hidden sm:flex flex-col text-right mr-0.5">
                        <span class="text-[11px] font-semibold text-gray-800 leading-tight tracking-tight"><?= htmlspecialchars($display_nama) ?></span>
                        <span class="text-[9px] text-orange-600 font-bold leading-tight mt-px">Lead System Architect</span>
                    </div>
                    <!-- Avatar with gradient ring -->
                    <div class="relative flex-shrink-0">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#002f5e] via-[#0170b9] to-orange-500 p-[2px] shadow-sm group-hover:shadow-md transition-shadow">
                            <div class="w-full h-full rounded-full bg-white flex items-center justify-center">
                                <span class="text-[11px] font-bold text-[#002f5e]"><?= $avatar_initials ?></span>
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

                        <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari konsol simulasi CIDP YMS?');" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl hover:bg-red-50 text-red-600 transition-colors group">
                            <i class="fa-solid fa-right-from-bracket text-xs text-red-400 group-hover:text-red-600 transition-colors w-4 text-center"></i>
                            <span class="font-bold">Keluar dari Sistem</span>
                        </a>
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
</header>
