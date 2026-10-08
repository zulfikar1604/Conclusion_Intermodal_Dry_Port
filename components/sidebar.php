<?php
// =============================================================================
// FILE: components/sidebar.php
// FUNGSI: Sidebar Navigasi Eksekutif CIDP (Conclusion Orange & Clean Humanist Style)
// =============================================================================
?>
<!-- Sidebar (Clean Executive White + Conclusion Orange Hero Accent) -->
<aside id="sidebar" class="bg-white text-slate-700 flex-shrink-0 z-40 h-full overflow-hidden sidebar-transition w-56 fixed md:relative transform -translate-x-full md:translate-x-0 transition-all duration-300 ease-in-out shadow-lg md:shadow-none flex flex-col justify-between border-r border-slate-200/80 font-sans">
    
    <!-- Top: Logo Header (h-14 / 56px, Aligned with top header bar) -->
    <div class="h-14 flex items-center justify-between px-3.5 border-b border-slate-100 flex-shrink-0 bg-white">
        <div class="flex items-center overflow-hidden space-x-2.5">
            <div class="w-8 h-8 rounded-lg bg-orange-50 border border-orange-200/70 flex items-center justify-center p-1 flex-shrink-0 shadow-2xs">
                <img src="assets/img/logo.png" alt="Logo" class="w-full h-full object-contain" onerror="this.src='https://via.placeholder.com/32?text=C'">
            </div>
            <div class="logo-text min-w-0">
                <span class="text-[13px] font-extrabold tracking-tight text-slate-900 block leading-tight">CIDP YMS</span>
                <span class="text-[9px] font-bold text-orange-600 tracking-wider block uppercase mt-0.5">Hub 35 Ha &bull; v2.5</span>
            </div>
        </div>
        <!-- Mobile Close Button -->
        <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-slate-700 p-1 rounded-lg focus:outline-none transition-colors" title="Tutup Menu">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>
    
    <!-- Middle: Navigation (4 Kluster Divisi Operasional Dry Port) -->
    <nav class="flex-1 overflow-y-auto px-2.5 py-3 space-y-3.5">
        <?php foreach ($menu_clusters as $cluster_title => $items): ?>
            <div class="space-y-0.5">
                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.16em] px-2.5 mb-1 logo-text flex items-center justify-between">
                    <span><?= $cluster_title ?></span>
                </div>
                <div class="space-y-0.5">
                    <?php foreach ($items as $key => $item): 
                        if (can_view($key, $role)): 
                            $is_active = ($page === $key);
                    ?>
                        <a href="dashboard.php?page=<?= $key ?>" 
                           class="relative flex items-center justify-between px-2.5 py-1.5 rounded-lg transition-all duration-150 group <?= $is_active ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white font-bold shadow-xs shadow-orange-500/25' : 'text-slate-600 hover:text-orange-600 hover:bg-orange-50/70 font-medium' ?>"
                           title="<?= $item['label'] ?>">
                            <div class="flex items-center min-w-0 space-x-2.5">
                                <span class="w-4 flex items-center justify-center text-[12px] flex-shrink-0 transition-colors <?= $is_active ? 'text-white' : 'text-slate-400 group-hover:text-orange-500' ?>">
                                    <i class="fa-solid <?= $item['icon'] ?>"></i>
                                </span>
                                <span class="text-[12px] logo-text truncate tracking-tight"><?= $item['label'] ?></span>
                            </div>
                            <?php if ($is_active): ?>
                                <span class="w-1.5 h-1.5 rounded-full bg-white flex-shrink-0"></span>
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
    <div class="p-2.5 border-t border-slate-100 logo-text flex-shrink-0 bg-slate-50/50">
        <div class="bg-gradient-to-br from-white to-orange-50/40 border border-orange-200/60 rounded-xl p-2.5 shadow-2xs">
            <div class="flex items-center justify-between mb-1">
                <span class="text-[8.5px] font-bold text-slate-400 uppercase tracking-widest">Consultant</span>
                <span class="flex items-center text-[8.5px] text-emerald-600 font-bold uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse mr-1"></span>
                    Online
                </span>
            </div>
            <p class="text-[11.5px] font-bold text-slate-900 truncate">Conclusion Consultant</p>
            <p class="text-[9.5px] text-slate-500 truncate mt-0.5">Supply Chain &amp; Logistics YMS</p>
        </div>
    </div>
</aside>
