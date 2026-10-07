<?php
// =============================================================================
// FILE: components/sidebar.php
// FUNGSI: Sidebar Navigasi Eksekutif CIDP (4 Kluster Divisi Operasional)
// =============================================================================
?>
<!-- Sidebar (Executive Ink: navy matte, putih tulang, aksen kuningan) -->
<aside id="sidebar" class="bg-ink text-paper flex-shrink-0 z-40 h-full overflow-hidden sidebar-transition w-64 fixed md:relative transform -translate-x-full md:translate-x-0 transition-all duration-300 ease-in-out shadow-2xl md:shadow-none flex flex-col justify-between border-r border-white/[0.06] font-sans">
    <!-- Top: Logo Header (Height aligned with top header: h-14 / 56px) -->
    <div class="h-14 flex items-center justify-between px-4 border-b border-white/[0.07] flex-shrink-0">
        <div class="flex items-center overflow-hidden space-x-3">
            <div class="w-8 h-8 rounded-lg border border-brass/40 bg-white/[0.03] flex items-center justify-center p-1 flex-shrink-0">
                <img src="assets/img/logo.png" alt="Logo" class="w-full h-full object-contain" onerror="this.src='https://via.placeholder.com/32?text=C'">
            </div>
            <div class="logo-text min-w-0">
                <span class="text-[13px] font-semibold tracking-[0.04em] text-paper block leading-tight">CIDP YMS</span>
                <span class="text-[9px] text-paper/45 tracking-[0.18em] block uppercase mt-0.5">Hub 35 Ha &middot; v2.4</span>
            </div>
        </div>
        <!-- Mobile Close Button -->
        <button onclick="toggleSidebar()" class="md:hidden text-paper/60 hover:text-paper p-1.5 rounded-lg focus:outline-none transition-colors" title="Tutup Menu">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>
    
    <!-- Middle: Navigation (4 Kluster Divisi Operasional Dry Port) -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-5">
        <?php foreach ($menu_clusters as $cluster_title => $items): ?>
            <div class="space-y-1">
                <div class="text-[9.5px] font-medium text-brass/70 uppercase tracking-[0.22em] px-3 mb-2 logo-text flex items-center justify-between">
                    <span><?= $cluster_title ?></span>
                </div>
                <div class="space-y-px">
                    <?php foreach ($items as $key => $item): 
                        if (can_view($key, $role)): 
                            $is_active = ($page === $key);
                    ?>
                        <a href="dashboard.php?page=<?= $key ?>" 
                           class="relative flex items-center justify-between pl-3 pr-3 py-2 rounded-md transition-colors duration-200 group <?= $is_active ? 'bg-white/[0.07] text-paper font-semibold' : 'text-paper/60 hover:text-paper hover:bg-white/[0.04] font-medium' ?>"
                           title="<?= $item['label'] ?>">
                            <?php if ($is_active): ?>
                                <span class="absolute left-0 top-1.5 bottom-1.5 w-[2px] rounded-full bg-brass"></span>
                            <?php endif; ?>
                            <div class="flex items-center min-w-0 space-x-3">
                                <span class="w-5 flex items-center justify-center text-[13px] flex-shrink-0 transition-colors <?= $is_active ? 'text-brass' : 'text-paper/40 group-hover:text-paper/80' ?>">
                                    <i class="fa-solid <?= $item['icon'] ?>"></i>
                                </span>
                                <span class="text-[12.5px] logo-text truncate tracking-[0.01em]"><?= $item['label'] ?></span>
                            </div>
                        </a>
                    <?php 
                        endif;
                    endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </nav>

    <!-- Bottom: Executive Consultant Status Card -->
    <div class="px-4 py-3.5 border-t border-white/[0.07] logo-text flex-shrink-0">
        <div class="flex items-center justify-between mb-1.5">
            <span class="text-[9px] font-medium text-paper/40 uppercase tracking-[0.22em]">System Consultant</span>
            <span class="flex items-center text-[9px] text-[#8FA89A] tracking-[0.14em] uppercase">
                <span class="w-1.5 h-1.5 rounded-full bg-[#8FA89A] mr-1.5"></span>
                Online
            </span>
        </div>
        <p class="text-[12px] font-semibold text-paper tracking-[0.01em] truncate">Conclusion Consultant</p>
        <p class="text-[10px] text-paper/45 truncate mt-0.5">ITL Trisakti &middot; Dr. Tigor Franky</p>
    </div>
</aside>
