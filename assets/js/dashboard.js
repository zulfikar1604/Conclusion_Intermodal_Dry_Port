// =============================================================================
// CIDP YMS - Dashboard UI Interactions, Live Telemetry & Control Engine
// Proyek: Conclusion Intermodal Dry Port (CIDP 35 Hectares DPMS)
// =============================================================================

let isSidebarOpenMobile = false;
let isSidebarCollapsedDesktop = false;

// =============================================================================
// SIDEBAR TOGGLES (MOBILE & DESKTOP COLLAPSE)
// =============================================================================
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (!sidebar || !overlay) return;

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
    const sidebar = document.getElementById('sidebar');
    const desktopToggleIcon = document.getElementById('desktopToggleIcon');
    const logoTexts = document.querySelectorAll('.logo-text');
    if (!sidebar) return;

    isSidebarCollapsedDesktop = !isSidebarCollapsedDesktop;
    if (isSidebarCollapsedDesktop) {
        sidebar.classList.remove('w-64', 'md:w-56', 'w-56');
        sidebar.classList.add('w-16');
        logoTexts.forEach(el => el.classList.add('hidden'));
        if (desktopToggleIcon) desktopToggleIcon.classList.replace('fa-bars-staggered', 'fa-bars');
    } else {
        sidebar.classList.remove('w-16');
        sidebar.classList.add('w-64', 'md:w-56');
        if (desktopToggleIcon) desktopToggleIcon.classList.replace('fa-bars', 'fa-bars-staggered');
        setTimeout(() => {
            logoTexts.forEach(el => el.classList.remove('hidden'));
        }, 200);
    }
}

// =============================================================================
// GLOBAL UNIVERSAL SEARCH (COMMAND PALETTE CTRL+K & MOBILE DRAWER)
// =============================================================================
function showSearchSuggestions() {
    const flyout = document.getElementById('searchSuggestionsFlyout');
    if (flyout) flyout.classList.remove('hidden');
}

function hideSearchSuggestions() {
    const flyout = document.getElementById('searchSuggestionsFlyout');
    if (flyout) flyout.classList.add('hidden');
}

function toggleMobileSearch() {
    const drawer = document.getElementById('mobileSearchDrawer');
    const input = document.getElementById('globalQuickSearchMobile');
    if (!drawer) return;
    const isHidden = drawer.classList.toggle('hidden');
    if (!isHidden && input) {
        setTimeout(() => input.focus(), 80);
    }
}

// =============================================================================
// CONTROL TOWER FULLSCREEN DISPLAY MODE (F11 / WALL DISPLAY)
// =============================================================================
function toggleFullScreen() {
    const fsIcon = document.getElementById('fullscreenIcon');
    if (!document.fullscreenElement) {
        if (document.documentElement.requestFullscreen) {
            document.documentElement.requestFullscreen();
        } else if (document.documentElement.webkitRequestFullscreen) {
            document.documentElement.webkitRequestFullscreen();
        }
        if (fsIcon) fsIcon.classList.replace('fa-expand', 'fa-compress');
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        }
        if (fsIcon) fsIcon.classList.replace('fa-compress', 'fa-expand');
    }
}

document.addEventListener('fullscreenchange', function() {
    const fsIcon = document.getElementById('fullscreenIcon');
    if (!fsIcon) return;
    if (document.fullscreenElement) {
        fsIcon.classList.replace('fa-expand', 'fa-compress');
    } else {
        fsIcon.classList.replace('fa-compress', 'fa-expand');
    }
});

// =============================================================================
// INDONESIAN BATIK WATERMARK MOTIF ENGINE (SYNCHRONIZED SIDEBAR & HEADER)
// =============================================================================
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

function syncBatikOnLoad() {
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
}

// =============================================================================
// NOTIFIKASI DROPDOWN & KATEGORI FILTER
// =============================================================================
function toggleNotifDropdown() {
    const notifCard = document.getElementById('notifDropdownCard');
    const profileCard = document.getElementById('profileDropdownCard');
    const profileChevron = document.getElementById('profileChevron');
    
    if (profileCard && !profileCard.classList.contains('hidden')) {
        profileCard.classList.add('hidden');
        if (profileChevron) profileChevron.classList.remove('rotate-180');
    }
    
    if (notifCard) {
        notifCard.classList.toggle('hidden');
    }
}

function filterNotifCategory(category) {
    const tabs = document.querySelectorAll('.notif-filter-tab');
    const items = document.querySelectorAll('.notif-feed-item');
    
    tabs.forEach(tab => {
        if (tab.dataset.category === category) {
            tab.className = 'notif-filter-tab px-2 py-0.5 rounded-md text-[10px] font-bold bg-white text-orange-600 shadow-2xs border border-orange-200/60 transition cursor-pointer';
        } else {
            tab.className = 'notif-filter-tab px-2 py-0.5 rounded-md text-[10px] font-semibold text-slate-500 hover:text-slate-800 transition cursor-pointer';
        }
    });
    
    items.forEach(item => {
        if (category === 'all' || item.dataset.category === category) {
            item.classList.remove('hidden');
        } else {
            item.classList.add('hidden');
        }
    });
}

function markAllNotificationsRead() {
    const badge = document.getElementById('notifBadge');
    const countPill = document.getElementById('notifCountPill');
    const unreadDots = document.querySelectorAll('.notif-unread-dot');
    const notifItems = document.querySelectorAll('.notif-item');
    
    if (badge) badge.classList.add('hidden');
    if (countPill) {
        countPill.textContent = '0 Baru';
        countPill.className = 'px-2 py-0.5 rounded-full bg-slate-500/80 text-white text-[10px] font-bold font-mono shadow-xs';
    }
    unreadDots.forEach(d => d.classList.add('hidden'));
    notifItems.forEach(item => item.classList.remove('bg-blue-50/20', 'bg-orange-50/20'));
}

// =============================================================================
// PROFIL EKSEKUTIF DROPDOWN
// =============================================================================
function toggleProfileDropdown() {
    const profileCard = document.getElementById('profileDropdownCard');
    const notifCard = document.getElementById('notifDropdownCard');
    const profileChevron = document.getElementById('profileChevron');
    
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

// =============================================================================
// EVENT LISTENERS: CLICK OUTSIDE & SHORTCUT KEYS (CTRL+K, ESC)
// =============================================================================
document.addEventListener('click', function(event) {
    const notifWrapper = document.getElementById('notifDropdownWrapper');
    const notifCard = document.getElementById('notifDropdownCard');
    const profileWrapper = document.getElementById('profileDropdownWrapper');
    const profileCard = document.getElementById('profileDropdownCard');
    const profileChevron = document.getElementById('profileChevron');
    const searchWrapper = document.getElementById('globalSearchWrapper');
    const searchFlyout = document.getElementById('searchSuggestionsFlyout');
    
    if (notifWrapper && !notifWrapper.contains(event.target)) {
        if (notifCard) notifCard.classList.add('hidden');
    }
    
    if (profileWrapper && !profileWrapper.contains(event.target)) {
        if (profileCard) profileCard.classList.add('hidden');
        if (profileChevron) profileChevron.classList.remove('rotate-180');
    }

    if (searchWrapper && !searchWrapper.contains(event.target)) {
        if (searchFlyout) searchFlyout.classList.add('hidden');
    }
});

document.addEventListener('keydown', function(event) {
    // ESCAPE KEY: Close all overlays, flyouts, and dropdowns
    if (event.key === 'Escape') {
        const notifCard = document.getElementById('notifDropdownCard');
        const profileCard = document.getElementById('profileDropdownCard');
        const profileChevron = document.getElementById('profileChevron');
        const searchFlyout = document.getElementById('searchSuggestionsFlyout');
        const mobileDrawer = document.getElementById('mobileSearchDrawer');
        
        if (notifCard) notifCard.classList.add('hidden');
        if (profileCard) profileCard.classList.add('hidden');
        if (profileChevron) profileChevron.classList.remove('rotate-180');
        if (searchFlyout) searchFlyout.classList.add('hidden');
        if (mobileDrawer) mobileDrawer.classList.add('hidden');
    }

    // CTRL+K / CMD+K: Quick Search Command Palette Focus
    if ((event.ctrlKey || event.metaKey) && (event.key === 'k' || event.key === 'K')) {
        event.preventDefault();
        const searchInput = document.getElementById('globalQuickSearch') || document.getElementById('globalQuickSearchMobile');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
            showSearchSuggestions();
        }
    }
});

// =============================================================================
// JAM REAL-TIME OPERASIONAL TERMINAL (WIB / GMT+7)
// =============================================================================
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

// Inisialisasi Saat DOM Siap
document.addEventListener('DOMContentLoaded', function() {
    updateRealTimeClock();
    setInterval(updateRealTimeClock, 1000);
    syncBatikOnLoad();
});
