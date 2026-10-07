<?php
// =============================================================================
// FILE: components/sidebar.php
// FUNGSI: Sidebar Navigasi Eksekutif CIDP (Maritime Navy Concept - Compact & Sleek)
// =============================================================================
?>
<!-- Sidebar (Maritime Navy: Selaras dengan Landing Page & Konten CIDP) -->
<aside id="sidebar" class="bg-[#00264d] text-white flex-shrink-0 z-40 h-full overflow-hidden sidebar-transition w-56 fixed md:relative transform -translate-x-full md:translate-x-0 transition-all duration-300 ease-in-out shadow-xl md:shadow-none flex flex-col justify-between border-r border-white/10 font-sans">
    
    <!-- Top: Logo Header (h-14 / 56px) -->
    <div class="h-14 flex items-center justify-between px-3.5 border-b border-white/10 flex-shrink-0 bg-[#001f3f]/60">
        <div class="flex items-center overflow-hidden space-x-2.5">
            <div class="w-8 h-8 rounded-lg bg-white/10 border border-white/20 flex items-center justify-center p-1 flex-shrink-0 shadow-xs">
                <img src="assets/img/logo.png" alt="Logo" class="w-full h-full object-contain" onerror="this.src='https://via.placeholder.com/32?text=C'">
            </div>
            <div class="logo-text min-w-0">
                <span class="text-[13px] font-bold tracking-tight text-white block leading-tight">CIDP YMS</span>
                <span class="text-[9px] text-blue-200/70 tracking-wider block uppercase mt-0.5">Terminal Hub 35 Ha</span>
            </div>
        </div>
        <!-- Mobile Close Button -->
        <button onclick="toggleSidebar()" class="md:hidden text-white/70 hover:text-white p-1 rounded-lg focus:outline-none transition-colors" title="Tutup Menu">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>
    
    <!-- Middle: Navigation (4 Kluster Divisi Operasional Dry Port) -->
    <nav class="flex-1 overflow-y-auto px-2.5 py-3 space-y-4">
        <?php foreach ($menu_clusters as $cluster_title => $items): ?>
            <div class="space-y-0.5">
                <div class="text-[9px] font-bold text-blue-200/50 uppercase tracking-[0.18em] px-2.5 mb-1 logo-text flex items-center justify-between">
                    <span><?= $cluster_title ?></span>
                </div>
                <div class="space-y-0.5">
                    <?php foreach ($items as $key => $item): 
                        if (can_view($key, $role)): 
                            $is_active = ($page === $key);
                    ?>
                        <a href="dashboard.php?page=<?= $key ?>" 
                           class="relative flex items-center justify-between px-2.5 py-1.5 rounded-lg transition-all duration-150 group <?= $is_active ? 'bg-[#0170b9] text-white font-semibold shadow-xs' : 'text-blue-100/75 hover:text-white hover:bg-white/10 font-medium' ?>"
                           title="<?= $item['label'] ?>">
                            <div class="flex items-center min-w-0 space-x-2.5">
                                <span class="w-4 flex items-center justify-center text-[12px] flex-shrink-0 transition-colors <?= $is_active ? 'text-white' : 'text-blue-300 group-hover:text-white' ?>">
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
    <div class="p-2.5 border-t border-white/10 logo-text flex-shrink-0 bg-[#001f3f]/40">
        <div class="bg-white/5 border border-white/10 rounded-lg p-2.5">
            <div class="flex items-center justify-between mb-1">
                <span class="text-[8.5px] font-bold text-blue-200/60 uppercase tracking-widest">Consultant</span>
                <span class="flex items-center text-[8.5px] text-emerald-400 font-bold uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse mr-1"></span>
                    Online
                </span>
            </div>
            <p class="text-[11.5px] font-bold text-white truncate">Conclusion Consultant</p>
            <p class="text-[9.5px] text-blue-200/60 truncate mt-0.5">ITL Trisakti &bull; Dr. Tigor Franky</p>
        </div>
    </div>
</aside>
