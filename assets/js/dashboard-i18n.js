/**
 * Conclusion Intermodal Dry Port (CIDP)
 * Dashboard Multilingual Client Engine
 * File: assets/js/dashboard-i18n.js
 */

(function() {
    'use strict';

    // 1. Sync localStorage language with PHP session/cookie on initial load
    const savedLang = localStorage.getItem('cidp_lang') || 'id';
    const urlParams = new URLSearchParams(window.location.search);
    const urlLang = urlParams.get('lang');

    // If URL has explicit lang parameter, update localStorage and cookie
    if (urlLang && (urlLang === 'id' || urlLang === 'en')) {
        if (savedLang !== urlLang) {
            try {
                localStorage.setItem('cidp_lang', urlLang);
                document.cookie = "cidp_lang=" + urlLang + "; path=/; max-age=" + (86400 * 30);
            } catch(e) {}
        }
    } else {
        // If no URL lang param, but localStorage is 'en' and page didn't load in 'en'
        const currentDocLang = document.documentElement.lang || 'id';
        if (savedLang !== currentDocLang && (savedLang === 'en' || savedLang === 'id')) {
            // Set cookie so next request or reload is aligned
            document.cookie = "cidp_lang=" + savedLang + "; path=/; max-age=" + (86400 * 30);
            if (!sessionStorage.getItem('cidp_lang_synced')) {
                sessionStorage.setItem('cidp_lang_synced', 'true');
                urlParams.set('lang', savedLang);
                window.location.search = urlParams.toString();
                return;
            }
        }
    }

    /**
     * Switch dashboard language dynamically
     * @param {string} lang - 'id' or 'en'
     */
    window.switchDashboardLanguage = function(lang) {
        if (lang !== 'id' && lang !== 'en') return;
        try {
            localStorage.setItem('cidp_lang', lang);
            document.cookie = "cidp_lang=" + lang + "; path=/; max-age=" + (86400 * 30);
            sessionStorage.setItem('cidp_lang_synced', 'true');
        } catch(e) {}

        const currentParams = new URLSearchParams(window.location.search);
        currentParams.set('lang', lang);
        window.location.search = currentParams.toString();
    };

    // Client-side dictionary for dynamic runtime JS features
    window.cidpDict = {
        id: {
            live_status: 'ONLINE',
            searching: 'Mencari...',
            no_results: 'Tidak ada data ditemukan',
            esc_close: 'Esc untuk tutup',
            toast_copied: 'Tersalin ke clipboard!',
            confirm_logout: 'Apakah Anda yakin ingin keluar?',
            connected: 'Terhubung'
        },
        en: {
            live_status: 'ONLINE',
            searching: 'Searching...',
            no_results: 'No data found',
            esc_close: 'Esc to close',
            toast_copied: 'Copied to clipboard!',
            confirm_logout: 'Are you sure you want to sign out?',
            connected: 'Connected'
        }
    };

    window.getLangText = function(key) {
        const lang = localStorage.getItem('cidp_lang') || 'id';
        return (window.cidpDict[lang] && window.cidpDict[lang][key]) || key;
    };
})();
