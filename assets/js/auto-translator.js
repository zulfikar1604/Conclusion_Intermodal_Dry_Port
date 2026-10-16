/**
 * Conclusion Intermodal Dry Port (CIDP)
 * Silent Auto-Translator Engine (Google Cloud Translation Web Engine)
 * File: assets/js/auto-translator.js
 *
 * Menerjemahkan seluruh halaman (Landing Page & Dashboard Aplikasi)
 * secara otomatis ke Bahasa Inggris ketika online/terkoneksi internet,
 * tanpa banner atau toolbar yang mengganggu (Clean Corporate UX).
 */

(function() {
    'use strict';

    // 1. INJECT CLEAN CORPORATE CSS (Sembunyikan toolbar/banner bawaan Google)
    const styleEl = document.createElement('style');
    styleEl.setAttribute('id', 'cidp-auto-translate-css');
    styleEl.textContent = `
        /* Sembunyikan banner atas Google Translate */
        .goog-te-banner-frame.skiptranslate,
        .goog-te-banner-frame,
        iframe.goog-te-banner-frame,
        .goog-te-banner-frame.skiptranslate {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            opacity: 0 !important;
        }
        body {
            top: 0px !important;
            position: static !important;
        }
        /* Sembunyikan widget container dan elemen skiptranslate */
        #google_translate_element,
        .skiptranslate:not(.keep-visible) {
            display: none !important;
        }
        /* Hilangkan highlight kuning / tooltip hover Google */
        .goog-text-highlight {
            background-color: transparent !important;
            box-shadow: none !important;
            border: none !important;
        }
        #goog-gt-tt,
        .goog-tooltip,
        .goog-tooltip:hover,
        .goog-te-balloon-frame {
            display: none !important;
            visibility: hidden !important;
        }
        /* Jangan ubah font style sistem */
        font {
            background-color: transparent !important;
            box-shadow: none !important;
        }
    `;
    document.head.appendChild(styleEl);

    // 2. INJECT HIDDEN TRANSLATE CONTAINER
    if (!document.getElementById('google_translate_element')) {
        const div = document.createElement('div');
        div.id = 'google_translate_element';
        div.style.display = 'none';
        document.body ? document.body.appendChild(div) : document.addEventListener('DOMContentLoaded', () => document.body.appendChild(div));
    }

    // 3. COOKIE HELPER
    function setCookie(name, value, days) {
        const maxAge = days ? days * 86400 : 86400 * 30;
        const hostname = window.location.hostname;
        // Set untuk path root dan current domain
        document.cookie = `${name}=${value}; path=/; max-age=${maxAge}`;
        if (hostname && hostname !== 'localhost') {
            document.cookie = `${name}=${value}; path=/; domain=.${hostname}; max-age=${maxAge}`;
        }
    }

    function deleteCookie(name) {
        const hostname = window.location.hostname;
        document.cookie = `${name}=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;`;
        if (hostname && hostname !== 'localhost') {
            document.cookie = `${name}=; path=/; domain=.${hostname}; expires=Thu, 01 Jan 1970 00:00:00 UTC;`;
        }
    }

    function getCookie(name) {
        const match = document.cookie.match(new RegExp('(^|;\\s*)(' + name + ')=([^;]*)'));
        return match ? decodeURIComponent(match[3]) : null;
    }

    // 4. GOOGLE TRANSLATE INITIALIZATION CALLBACK
    window.googleTranslateElementInit = function() {
        if (!window.google || !window.google.translate) return;
        new window.google.translate.TranslateElement({
            pageLanguage: 'id',
            includedLanguages: 'id,en',
            autoDisplay: false,
            multilanguagePage: false
        }, 'google_translate_element');

        // Jika preferensi tersimpan adalah EN, sinkronkan combo begitu siap
        const preferred = localStorage.getItem('cidp_lang') || (getCookie('googtrans') && getCookie('googtrans').includes('en') ? 'en' : 'id');
        if (preferred === 'en') {
            pollAndSelectLanguage('en', 15);
        }
    };

    // 5. ASYNC LOAD GOOGLE TRANSLATE SCRIPT
    function loadGoogleTranslateScript() {
        if (document.getElementById('google-translate-script')) return;
        const script = document.createElement('script');
        script.id = 'google-translate-script';
        script.type = 'text/javascript';
        script.async = true;
        script.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
        script.onerror = function() {
            console.warn('[CIDP i18n] Google Translate offline / tidak dapat dijangkau. Menjalankan fallback lokal.');
        };
        document.head.appendChild(script);
    }

    // 6. POLL SELECTOR DAN UBAH BAHASA SECARA OTOMATIS
    function pollAndSelectLanguage(targetLang, retries) {
        const combo = document.querySelector('.goog-te-combo');
        if (combo) {
            if (combo.value !== targetLang) {
                combo.value = targetLang;
                combo.dispatchEvent(new Event('change'));
            }
            updateButtonVisuals(targetLang);
        } else if (retries > 0) {
            setTimeout(function() {
                pollAndSelectLanguage(targetLang, retries - 1);
            }, 300);
        }
    }

    // 7. UPDATE TAMPILAN TOMBOL ID | EN DI HALAMAN (Landing & Dashboard)
    function updateButtonVisuals(lang) {
        // A. Tombol Desktop di Landing Page
        const btnID = document.getElementById('langBtnID');
        const btnEN = document.getElementById('langBtnEN');
        if (btnID && btnEN) {
            if (lang === 'en') {
                btnID.className = 'text-slate-500 hover:text-slate-900 transition-colors font-normal focus:outline-none';
                btnEN.className = 'font-bold text-orange-600 transition-colors focus:outline-none';
            } else {
                btnID.className = 'font-bold text-orange-600 transition-colors focus:outline-none';
                btnEN.className = 'text-slate-500 hover:text-slate-900 transition-colors font-normal focus:outline-none';
            }
        }

        // B. Tombol Mobile di Landing Page
        const mBtnID = document.getElementById('mobileLangBtnID');
        const mBtnEN = document.getElementById('mobileLangBtnEN');
        if (mBtnID && mBtnEN) {
            if (lang === 'en') {
                mBtnID.className = 'text-slate-500 hover:text-slate-900 transition-colors font-normal focus:outline-none';
                mBtnEN.className = 'font-bold text-orange-600 transition-colors focus:outline-none';
            } else {
                mBtnID.className = 'font-bold text-orange-600 transition-colors focus:outline-none';
                mBtnEN.className = 'text-slate-500 hover:text-slate-900 transition-colors font-normal focus:outline-none';
            }
        }

        // C. Tombol Header di Dashboard
        const dashBtnID = document.getElementById('dashLangBtnID');
        const dashBtnEN = document.getElementById('dashLangBtnEN');
        if (dashBtnID && dashBtnEN) {
            if (lang === 'en') {
                dashBtnID.className = 'px-2 py-0.5 rounded-md transition-all cursor-pointer text-slate-500 hover:text-slate-800';
                dashBtnEN.className = 'px-2 py-0.5 rounded-md transition-all cursor-pointer bg-white text-slate-800 shadow-2xs font-extrabold';
            } else {
                dashBtnID.className = 'px-2 py-0.5 rounded-md transition-all cursor-pointer bg-white text-slate-800 shadow-2xs font-extrabold';
                dashBtnEN.className = 'px-2 py-0.5 rounded-md transition-all cursor-pointer text-slate-500 hover:text-slate-800';
            }
        }
    }

    // 8. MASTER SWITCH FUNCTION (DIPANGGIL DARI KLIK TOMBOL ID / EN)
    window.setCIDPLanguage = function(lang) {
        if (lang !== 'id' && lang !== 'en') return;

        try {
            localStorage.setItem('cidp_lang', lang);
        } catch(e) {}

        // Atur cookie preferensi internal
        setCookie('cidp_lang', lang, 30);

        if (lang === 'en') {
            // Pasang cookie Google Translate agar seluruh halaman otomatis diterjemahkan
            setCookie('googtrans', '/id/en', 30);
            updateButtonVisuals('en');

            const combo = document.querySelector('.goog-te-combo');
            if (combo) {
                combo.value = 'en';
                combo.dispatchEvent(new Event('change'));
            } else {
                // Jika combo belum ada di memori, muat ulang halaman dengan cookie googtrans aktif
                window.location.reload();
            }
        } else {
            // Kembali ke Bahasa Indonesia
            deleteCookie('googtrans');
            setCookie('googtrans', '/id/id', 30);
            updateButtonVisuals('id');

            const combo = document.querySelector('.goog-te-combo');
            if (combo) {
                combo.value = 'id';
                combo.dispatchEvent(new Event('change'));
            }
            // Muat ulang agar struktur DOM kembali ke Bahasa Indonesia murni
            window.location.reload();
        }
    };

    // ALIASES untuk kompatibilitas fungsi yang sudah ada di template
    window.setLanguage = window.setCIDPLanguage;
    window.switchDashboardLanguage = window.setCIDPLanguage;

    // 9. ON DOM READY
    function init() {
        loadGoogleTranslateScript();

        // Cek status bahasa yang tersimpan
        const current = localStorage.getItem('cidp_lang') || (getCookie('googtrans') && getCookie('googtrans').includes('en') ? 'en' : 'id');
        updateButtonVisuals(current);

        // Jika tersimpan 'en' tapi cookie googtrans belum terpasang, pasang
        if (current === 'en') {
            const gt = getCookie('googtrans');
            if (!gt || !gt.includes('en')) {
                setCookie('googtrans', '/id/en', 30);
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
