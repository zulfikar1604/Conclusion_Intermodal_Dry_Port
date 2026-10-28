/* =============================================================================
   FILE: assets/js/executive-ux.js
   FUNGSI: Lapisan kejelasan eksekutif GLOBAL (dimuat di dashboard.php, setelah
           Chart.js, sebelum konten halaman) sehingga berlaku di SEMUA modul.

   1. Tema Chart.js eksekutif  : font terbaca, fill transparan sewarna garis,
                                 tidak ada area fill yang saling menimpa,
                                 donut dengan cincin tebal & legenda besar.
   2. Perangkum badge header   : header modul cukup 1 badge status; badge
                                 teknis lainnya dirangkum ke chip "+N" (tooltip).
   3. Strip status eksekutif   : buka/tutup diingat per browser (localStorage).
   4. ResizeObserver <main>    : otomatis me-resize seluruh grafik Chart.js saat
                                 sidebar diciutkan/diperluas atau ukuran layar berubah.
   ============================================================================= */
(function () {
    'use strict';

    /* ---------------------------------------------------------------------
       Util warna: ubah hex / rgb / rgba menjadi rgba dengan alpha baru
       ------------------------------------------------------------------ */
    function toRgba(color, alpha) {
        if (typeof color !== 'string') return null;
        var c = color.trim();
        var m;
        if (c.charAt(0) === '#') {
            var hex = c.slice(1);
            if (hex.length === 3) hex = hex.split('').map(function (h) { return h + h; }).join('');
            if (hex.length < 6) return null;
            var r = parseInt(hex.slice(0, 2), 16), g = parseInt(hex.slice(2, 4), 16), b = parseInt(hex.slice(4, 6), 16);
            return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
        }
        m = c.match(/rgba?\(\s*([\d.]+)\s*,\s*([\d.]+)\s*,\s*([\d.]+)/i);
        if (m) return 'rgba(' + m[1] + ', ' + m[2] + ', ' + m[3] + ', ' + alpha + ')';
        return null;
    }

    function bumpFont(fontObj, min) {
        if (!fontObj || typeof fontObj !== 'object') return { size: min };
        if (typeof fontObj.size !== 'number' || fontObj.size < min) fontObj.size = min;
        return fontObj;
    }

    /* ---------------------------------------------------------------------
       1. TEMA CHART.JS EKSEKUTIF
       ------------------------------------------------------------------ */
    function installChartTheme() {
        if (typeof Chart === 'undefined' || Chart.__cidpExecTheme) return;
        Chart.__cidpExecTheme = true;

        Chart.defaults.font.family = "'Plus Jakarta Sans', system-ui, sans-serif";
        Chart.defaults.font.size = 11.5;
        Chart.defaults.color = '#475569';

        Chart.register({
            id: 'cidpExecutiveTheme',
            beforeInit: function (chart) {
                var cfg = chart.config;
                var type = cfg.type;
                var opts = cfg.options || (cfg.options = {});
                var plugins = opts.plugins || (opts.plugins = {});
                var data = cfg.data || {};
                var datasets = data.datasets || [];

                // --- Legenda: lebih besar, ruang napas cukup ---
                var legend = plugins.legend || (plugins.legend = {});
                var labels = legend.labels || (legend.labels = {});
                labels.font = bumpFont(labels.font, type === 'doughnut' || type === 'pie' ? 12 : 11.5);
                labels.color = labels.color || '#334155';
                if (!labels.boxWidth || labels.boxWidth < 9) labels.boxWidth = 9;
                if (!labels.padding || labels.padding < 12) labels.padding = 12;

                // --- Tooltip terbaca ---
                var tip = plugins.tooltip || (plugins.tooltip = {});
                tip.titleFont = bumpFont(tip.titleFont, 12);
                tip.bodyFont = bumpFont(tip.bodyFont, 11.5);
                if (!tip.padding || tip.padding < 10) tip.padding = 10;

                // --- Sumbu ---
                var scales = opts.scales || {};
                Object.keys(scales).forEach(function (k) {
                    var s = scales[k] || {};
                    s.ticks = s.ticks || {};
                    s.ticks.font = bumpFont(s.ticks.font, 10.5);
                    if (!s.ticks.color) s.ticks.color = '#475569';
                    if (s.pointLabels) {
                        s.pointLabels.font = bumpFont(s.pointLabels.font, 11);
                        s.pointLabels.color = '#334155';
                    }
                    if (s.grid && (!s.grid.color || s.grid.color === '#f1f5f9')) s.grid.color = '#eef2f6';
                });

                // --- Line / area: fill sewarna garis & tidak saling menimpa ---
                if (type === 'line' || datasets.some(function (d) { return d.type === 'line'; })) {
                    var filledSeen = 0;
                    datasets.forEach(function (d) {
                        if (d.type && d.type !== 'line') return;
                        var isDashed = Array.isArray(d.borderDash) && d.borderDash.length > 0;
                        if (d.fill && d.fill !== false) {
                            if (isDashed || filledSeen >= 1) {
                                // Seri pembanding (putus-putus / seri ke-2): garis saja
                                d.fill = false;
                            } else {
                                var tint = toRgba(d.borderColor, 0.10);
                                if (tint) d.backgroundColor = tint;
                                filledSeen++;
                            }
                        }
                        if (typeof d.borderWidth !== 'number' || d.borderWidth < 2) d.borderWidth = isDashed ? 2 : 2.5;
                    });
                }

                // --- Radar: fill tipis sewarna garis (bukan abu keruh) ---
                if (type === 'radar') {
                    datasets.forEach(function (d) {
                        var tint = toRgba(d.borderColor, 0.10);
                        if (tint) d.backgroundColor = tint;
                        if (typeof d.borderWidth !== 'number' || d.borderWidth < 2) d.borderWidth = 2;
                        if (!d.pointRadius || d.pointRadius < 3) d.pointRadius = 3;
                    });
                }

                // --- Donut / pie: cincin tebal, tanpa celah abu pudar ---
                if (type === 'doughnut') {
                    var cut = opts.cutout;
                    var pct = typeof cut === 'string' ? parseFloat(cut) : (typeof cut === 'number' ? cut : 50);
                    if (isNaN(pct) || pct > 62) opts.cutout = '60%';
                }
                if (type === 'doughnut' || type === 'pie') {
                    datasets.forEach(function (d) {
                        if (Array.isArray(d.backgroundColor)) {
                            // Abu-biru pudar (#94a3b8 / #cbd5e1) sulit dibedakan dari biru utama
                            d.backgroundColor = d.backgroundColor.map(function (c, i) {
                                var lc = String(c).toLowerCase();
                                if (i > 0 && (lc === '#94a3b8' || lc === '#cbd5e1' || lc === '#64748b')) return '#ea580c';
                                return c;
                            });
                        }
                        if (d.borderWidth === 0 || d.borderWidth === undefined) {
                            d.borderWidth = 2;
                            d.borderColor = '#ffffff';
                        }
                    });
                }
            }
        });
    }

    /* ---------------------------------------------------------------------
       2. PERANGKUM BADGE & TOMBOL HEADER MODUL + AUTO-FLOW PIPELINE
       - Header modul cukup memuat 1 badge status utama; badge teknis lainnya
         dirangkum menjadi chip "+N" (klik untuk tampil/sembunyikan).
       - Bila deretan tombol aksi di kanan header > 2 tombol, tombol ke-3 dst.
         dirangkum ke menu dropdown "Lainnya ▾" agar fokus pada CTA utama.
       ------------------------------------------------------------------ */
    function declutterHeaderBadges() {
        var main = document.querySelector('main');
        if (!main) return;
        main.querySelectorAll('h1, h2').forEach(function (heading) {
            var parent = heading.parentElement;
            if (!parent || parent.dataset.cidpDecluttered) return;
            var badges = Array.prototype.filter.call(parent.children, function (el) {
                return el !== heading && el.tagName === 'SPAN' && !el.id && el.textContent.trim().length > 0 &&
                       (heading.compareDocumentPosition(el) & Node.DOCUMENT_POSITION_FOLLOWING);
            });
            if (badges.length <= 1) return;
            parent.dataset.cidpDecluttered = '1';
            var hidden = badges.slice(1);
            var names = hidden.map(function (b) { return b.textContent.replace(/\s+/g, ' ').trim(); });
            hidden.forEach(function (b) { b.classList.add('cidp-badge-collapsed'); });
            var more = document.createElement('span');
            more.className = 'cidp-badge-more';
            more.textContent = '+' + hidden.length;
            more.title = 'Klik untuk melihat detail: ' + names.join(' \u2022 ');
            more.setAttribute('aria-label', 'Info tambahan: ' + names.join(', '));
            more.addEventListener('click', function () {
                var isHidden = hidden[0].classList.contains('cidp-badge-collapsed');
                hidden.forEach(function (b) { b.classList.toggle('cidp-badge-collapsed', !isHidden); });
                more.textContent = isHidden ? 'Ringkas' : ('+' + hidden.length);
            });
            hidden[hidden.length - 1].insertAdjacentElement('afterend', more);
        });
    }

    function declutterHeaderActions() {
        var main = document.querySelector('main');
        if (!main) return;
        var firstHeading = main.querySelector('h1, h2');
        if (!firstHeading) return;
        var headerCard = firstHeading.closest('.bg-white, .bg-white\\/95');
        if (!headerCard || headerCard.querySelector('.cidp-menu')) return;

        // Cari kelompok tombol di sisi kanan header
        var groups = headerCard.querySelectorAll('div.flex');
        var actionGroup = null;
        for (var i = groups.length - 1; i >= 0; i--) {
            var g = groups[i];
            if (g.contains(firstHeading)) continue;
            var btns = Array.prototype.filter.call(g.children, function (c) {
                return (c.tagName === 'BUTTON' || c.tagName === 'A') && !c.classList.contains('tab-btn');
            });
            if (btns.length >= 4) {
                actionGroup = g;
                break;
            }
        }
        if (!actionGroup) return;

        var items = Array.prototype.filter.call(actionGroup.children, function (c) {
            return (c.tagName === 'BUTTON' || c.tagName === 'A') && !c.classList.contains('tab-btn');
        });
        if (items.length < 4) return;

        var extras = items.slice(2);
        var details = document.createElement('details');
        details.className = 'cidp-menu';
        var summary = document.createElement('summary');
        summary.className = 'px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 text-[11px] font-bold flex items-center gap-1.5 transition select-none';
        summary.innerHTML = '<span>Lainnya (' + extras.length + ')</span><i class="fa-solid fa-chevron-down text-[9px] text-slate-500"></i>';
        var panel = document.createElement('div');
        panel.className = 'cidp-menu__panel';

        extras.forEach(function (el) {
            // Jika tombol hanya berisi ikon (mis. tombol print), beri label dari title
            if (el.textContent.trim().length === 0 && el.title) {
                var span = document.createElement('span');
                span.textContent = el.title;
                el.appendChild(span);
            }
            el.className = '';
            panel.appendChild(el);
        });

        details.appendChild(summary);
        details.appendChild(panel);
        actionGroup.appendChild(details);
    }

    // Tutup dropdown .cidp-menu saat klik di luar
    document.addEventListener('click', function (e) {
        document.querySelectorAll('details.cidp-menu[open]').forEach(function (d) {
            if (!d.contains(e.target)) d.removeAttribute('open');
        });
    });

    /* ---------------------------------------------------------------------
       3. STRIP STATUS EKSEKUTIF: buka / tutup
       ------------------------------------------------------------------ */
    window.toggleExecStatus = function () {
        var el = document.getElementById('execStatusStrip');
        if (!el) return;
        var collapsed = el.classList.toggle('is-collapsed');
        try { localStorage.setItem('cidp_exec_status_collapsed', collapsed ? '1' : '0'); } catch (e) {}
        var btn = document.getElementById('execStatusToggleBtn');
        if (btn) btn.innerHTML = collapsed
            ? '<i class="fa-solid fa-chevron-down"></i>'
            : '<i class="fa-solid fa-chevron-up"></i>';
    };

    function restoreExecStatus() {
        var el = document.getElementById('execStatusStrip');
        if (!el) return;
        var collapsed = false;
        try { collapsed = localStorage.getItem('cidp_exec_status_collapsed') === '1'; } catch (e) {}
        // Status kritis selalu tampil penuh, tidak boleh tersembunyi
        if (collapsed && el.dataset.level !== 'kritis') window.toggleExecStatus();
    }

    /* ---------------------------------------------------------------------
       4. RESIZE OBSERVER KANVAS <main>:
          Menjamin seluruh grafik Chart.js & kanvas interaktif otomatis
          menyesuaikan ulang ukuran saat Sidebar diciutkan/diperluas atau
          ketika layar berpindah antara HP, Tablet, Laptop, dan Monitor.
       ------------------------------------------------------------------ */
    function installContainerResizeSync() {
        var main = document.querySelector('main');
        if (!main || typeof ResizeObserver === 'undefined') return;
        var resizeTimer = null;
        var ro = new ResizeObserver(function () {
            if (resizeTimer) clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                if (typeof Chart !== 'undefined' && Chart.instances) {
                    Object.keys(Chart.instances).forEach(function (key) {
                        var inst = Chart.instances[key];
                        if (inst && typeof inst.resize === 'function') {
                            try { inst.resize(); } catch (e) {}
                        }
                    });
                }
            }, 60);
        });
        ro.observe(main);
    }

    installChartTheme();
    document.addEventListener('DOMContentLoaded', function () {
        installChartTheme();
        declutterHeaderBadges();
        declutterHeaderActions();
        restoreExecStatus();
        installContainerResizeSync();
    });
})();
