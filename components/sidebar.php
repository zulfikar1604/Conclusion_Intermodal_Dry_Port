<?php
// =============================================================================
// FILE: components/sidebar.php
// FUNGSI: Sidebar Navigasi Eksekutif CIDP dengan Motif Batik Nusantara & Glassmorphism
// Proyek: Conclusion Intermodal Dry Port (CIDP 35 Ha)
// Desain: Clean Executive White/Transparan + Subtle Batik Watermark (Kawung & Parang)
// =============================================================================
?>
<!-- Sidebar (Clean Executive Glassmorphism + Subtle Batik Nusantara Watermark) -->
<aside id="sidebar" class="bg-white/92 backdrop-blur-md text-slate-700 flex-shrink-0 z-40 h-full overflow-hidden sidebar-transition w-64 md:w-56 fixed md:relative transform -translate-x-full md:translate-x-0 transition-all duration-300 ease-in-out shadow-lg md:shadow-none flex flex-col justify-between border-r border-slate-200/90 font-sans relative">
    
    <!-- ======================================================================= -->
    <!-- LAYER WATERMARK MOTIF BATIK NUSANTARA MARITIM (SUBTLE LUXURY VECTOR)  -->
    <!-- ======================================================================= -->
    <div id="sidebarBatikWatermark" class="absolute inset-0 pointer-events-none opacity-[0.13] z-0 overflow-hidden transition-opacity duration-300" aria-hidden="true">
        <svg id="svgBatikPattern" width="100%" height="100%">
            <defs>
                <!-- MOTIF 1: BATIK KAWUNG MARITIM (Filosofi 4 Penjuru Kargo & Keteraturan Logistik) -->
                <pattern id="patBatikKawung" width="56" height="56" patternUnits="userSpaceOnUse">
                    <g stroke="#004b87" stroke-width="1.25" fill="none">
                        <!-- Petal Ellipses (Kelopak Utama 4 Sumbu) -->
                        <ellipse cx="28" cy="14" rx="8" ry="13" />
                        <ellipse cx="28" cy="42" rx="8" ry="13" />
                        <ellipse cx="14" cy="28" rx="13" ry="8" />
                        <ellipse cx="42" cy="28" rx="13" ry="8" />
                        <!-- Concentric Petal Cecek Inner Rings -->
                        <ellipse cx="28" cy="14" rx="4.5" ry="8" stroke-dasharray="2 1.5" />
                        <ellipse cx="28" cy="42" rx="4.5" ry="8" stroke-dasharray="2 1.5" />
                        <ellipse cx="14" cy="28" rx="8" ry="4.5" stroke-dasharray="2 1.5" />
                        <ellipse cx="42" cy="28" rx="8" ry="4.5" stroke-dasharray="2 1.5" />
                        <!-- Seamless Wrap Corner Petals -->
                        <ellipse cx="0" cy="0" rx="8" ry="13" />
                        <ellipse cx="56" cy="0" rx="8" ry="13" />
                        <ellipse cx="0" cy="56" rx="8" ry="13" />
                        <ellipse cx="56" cy="56" rx="8" ry="13" />
                        <ellipse cx="0" cy="0" rx="13" ry="8" />
                        <ellipse cx="56" cy="0" rx="13" ry="8" />
                        <ellipse cx="0" cy="56" rx="13" ry="8" />
                        <ellipse cx="56" cy="56" rx="13" ry="8" />
                    </g>
                    <!-- Center Cecek Papat / Stamen (Conclusion Orange Hero Accent) -->
                    <circle cx="28" cy="28" r="2.5" fill="#ea580c" />
                    <circle cx="0" cy="0" r="2.5" fill="#ea580c" />
                    <circle cx="56" cy="0" r="2.5" fill="#ea580c" />
                    <circle cx="0" cy="56" r="2.5" fill="#ea580c" />
                    <circle cx="56" cy="56" r="2.5" fill="#ea580c" />
                    <!-- Interlocking Mlinjon Diamonds -->
                    <polygon points="28,2 30,5 28,8 26,5" fill="#004b87" />
                    <polygon points="28,48 30,51 28,54 26,51" fill="#004b87" />
                    <polygon points="2,28 5,30 8,28 5,26" fill="#004b87" />
                    <polygon points="48,28 51,30 54,28 51,26" fill="#004b87" />
                </pattern>

                <!-- MOTIF 2: BATIK PARANG BAHARI (Filosofi Ombak Samudra & Ketangkasan Maritim) -->
                <pattern id="patBatikParang" width="48" height="48" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                    <g stroke="#004b87" stroke-width="1.25" fill="none">
                        <path d="M 0 12 Q 12 6, 24 12 T 48 12" />
                        <path d="M 0 24 Q 12 18, 24 24 T 48 24" />
                        <path d="M 0 36 Q 12 30, 24 36 T 48 36" />
                        <polygon points="12,12 15,9 18,12 15,15" fill="#ea580c" />
                        <polygon points="36,24 39,21 42,24 39,27" fill="#ea580c" />
                        <circle cx="24" cy="18" r="1.5" fill="#004b87" />
                        <circle cx="12" cy="30" r="1.5" fill="#004b87" />
                    </g>
                </pattern>
            </defs>
            <rect id="rectBatikFill" width="100%" height="100%" fill="url(#patBatikKawung)" />
        </svg>
    </div>

    <!-- ======================================================================= -->
    <!-- TOP: LOGO HEADER (ALIGNED WITH HEADER BAR)                             -->
    <!-- ======================================================================= -->
    <div class="h-14 flex items-center justify-between px-3.5 border-b border-slate-200/80 flex-shrink-0 bg-white/95 backdrop-blur-md relative z-10">
        <div class="flex items-center min-w-0 space-x-2.5">
            <div class="w-8 h-8 rounded-lg bg-orange-50 border border-orange-200/70 flex items-center justify-center p-1 flex-shrink-0 shadow-2xs">
                <img src="assets/img/logo.png" alt="Logo" class="w-full h-full object-contain" onerror="this.src='https://via.placeholder.com/32?text=C'">
            </div>
            <div class="logo-text min-w-0 flex-shrink-0" title="CIDP - Dry Port Management System">
                <span class="text-[13px] font-extrabold tracking-tight text-slate-900 block leading-tight whitespace-nowrap">Dry Port</span>
                <span class="text-[9px] font-bold text-orange-600 tracking-wider block uppercase mt-0.5 whitespace-nowrap">Management System</span>
            </div>
        </div>
        <!-- Mobile Close Button -->
        <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-slate-700 p-1.5 rounded-lg focus:outline-none transition-colors flex-shrink-0" title="Tutup Menu">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>
    
    <!-- ======================================================================= -->
    <!-- MIDDLE: NAVIGATION (5 KLUSTER DIVISI DPMS)                             -->
    <!-- ======================================================================= -->
    <nav class="flex-1 overflow-y-auto px-2.5 py-3 space-y-3.5 relative z-10">
        <?php foreach ($menu_clusters as $cluster_title => $items): ?>
            <div class="space-y-0.5">
                <div class="text-[9px] font-extrabold text-slate-500 uppercase tracking-[0.16em] px-2.5 mb-1 logo-text flex items-center justify-between">
                    <span><?= $cluster_title ?></span>
                </div>
                <div class="space-y-0.5">
                    <?php foreach ($items as $key => $item): 
                        if (can_view($key, $role)): 
                            $is_active = ($page === $key);
                    ?>
                        <a href="dashboard.php?page=<?= $key ?>" 
                           class="relative flex items-center justify-between px-2.5 py-1.5 rounded-lg transition-all duration-150 group <?= $is_active ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white font-bold shadow-xs shadow-orange-500/25' : 'text-slate-700 hover:text-orange-600 hover:bg-orange-50/80 bg-white/40 backdrop-blur-[0.5px] font-semibold' ?>"
                           title="<?= $item['label'] ?>">
                            <div class="flex items-center min-w-0 space-x-2.5">
                                <span class="w-4 flex items-center justify-center text-[12px] flex-shrink-0 transition-colors <?= $is_active ? 'text-white' : 'text-slate-500 group-hover:text-orange-500' ?>">
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

    <!-- ======================================================================= -->
    <!-- BOTTOM: EXECUTIVE CONSULTANT STATUS CARD WITH BATIK SWITCHER           -->
    <!-- ======================================================================= -->
    <div class="p-2.5 border-t border-slate-100 logo-text flex-shrink-0 bg-white/70 backdrop-blur-xs relative z-10">
        <div class="bg-gradient-to-br from-white to-orange-50/50 border border-orange-200/70 rounded-xl p-2.5 shadow-2xs">
            <div class="flex items-center justify-between mb-1">
                <span class="flex items-center text-[8.5px] font-bold text-slate-400 uppercase tracking-widest gap-1">
                    <i class="fa-solid fa-gem text-orange-500 text-[8px]"></i>
                    <span id="labelBatikActive"><?= __('sidebar_pattern_kawung', 'Kawung Maritim') ?></span>
                </span>
                <!-- Tombol Ganti Motif Batik -->
                <button type="button" onclick="toggleBatikPattern()" class="text-[8px] font-bold text-orange-600 hover:text-orange-700 bg-orange-50 hover:bg-orange-100 border border-orange-200/80 px-1.5 py-0.2 rounded transition cursor-pointer flex items-center gap-0.5 shadow-2xs" title="Ganti Motif Batik (Kawung / Parang)">
                    <span><?= __('sidebar_switch_btn', 'Ubah') ?></span>
                    <i class="fa-solid fa-rotate text-[7px]"></i>
                </button>
            </div>
            <div class="flex items-center justify-between mt-1">
                <div>
                    <p class="text-[11.5px] font-bold text-slate-900 truncate"><?= __('sidebar_consultant', 'Conclusion Consultant') ?></p>
                    <p class="text-[9px] text-slate-500 truncate mt-0.2"><?= __('sidebar_platform', 'Dry Port 35 Ha Platform') ?></p>
                </div>
                <span class="flex items-center text-[8.5px] text-emerald-600 font-bold uppercase tracking-wider flex-shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse mr-1"></span>
                    <?= __('sidebar_online', 'ONLINE') ?>
                </span>
            </div>
        </div>
    </div>
</aside>

<!-- Skrip Interaktif Pengalih Motif Batik Sidebar & Header -->
<script>
function toggleBatikPattern() {
    const sRect = document.getElementById('rectBatikFill');
    const hRect = document.getElementById('headerRectBatikFill');
    const cur = (sRect && sRect.getAttribute('fill')) || (hRect && hRect.getAttribute('fill')) || '';
    const isKawung = cur.includes('patBatikKawung');
    
    const nextPattern = isKawung ? 'url(#patBatikParang)' : 'url(#patBatikKawung)';
    const nextNameLong = isKawung ? 'Parang Bahari' : 'Kawung Maritim';
    const nextNameShort = isKawung ? 'Parang' : 'Kawung';
    const nextKey = isKawung ? 'parang' : 'kawung';

    if (sRect) sRect.setAttribute('fill', nextPattern);
    if (hRect) hRect.setAttribute('fill', nextPattern);
    
    const sLbl = document.getElementById('labelBatikActive');
    if (sLbl) sLbl.innerText = nextNameLong;
    const hLbl = document.getElementById('headerLabelBatikActive');
    if (hLbl) hLbl.innerText = nextNameShort;
    
    try { localStorage.setItem('cidp_sidebar_batik', nextKey); } catch(e) {}
}
document.addEventListener('DOMContentLoaded', function() {
    try {
        const saved = localStorage.getItem('cidp_sidebar_batik');
        if (saved === 'parang') {
            const sRect = document.getElementById('rectBatikFill');
            const hRect = document.getElementById('headerRectBatikFill');
            if (sRect) sRect.setAttribute('fill', 'url(#patBatikParang)');
            if (hRect) hRect.setAttribute('fill', 'url(#patBatikParang)');
            const sLbl = document.getElementById('labelBatikActive');
            if (sLbl) sLbl.innerText = 'Parang Bahari';
            const hLbl = document.getElementById('headerLabelBatikActive');
            if (hLbl) hLbl.innerText = 'Parang';
        }
    } catch(e) {}
});
</script>
