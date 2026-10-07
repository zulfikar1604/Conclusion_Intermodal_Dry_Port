// =============================================================================
// CIDP YMS - Dashboard UI Interactions & Live Clock
// =============================================================================

let isSidebarOpenMobile = false;
let isSidebarCollapsedDesktop = false;

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
        sidebar.classList.replace('w-56', 'w-16');
        logoTexts.forEach(el => el.classList.add('hidden'));
        if (desktopToggleIcon) desktopToggleIcon.classList.replace('fa-bars-staggered', 'fa-bars');
    } else {
        sidebar.classList.replace('w-16', 'w-56');
        if (desktopToggleIcon) desktopToggleIcon.classList.replace('fa-bars', 'fa-bars-staggered');
        setTimeout(() => {
            logoTexts.forEach(el => el.classList.remove('hidden'));
        }, 200);
    }
}

// =============================================================================
// INTERAKSI DROPDOWN NOTIFIKASI & PROFIL EKSEKUTIF
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

// Jalankan seketika dan perbarui setiap detik
document.addEventListener('DOMContentLoaded', function() {
    updateRealTimeClock();
    setInterval(updateRealTimeClock, 1000);
});
