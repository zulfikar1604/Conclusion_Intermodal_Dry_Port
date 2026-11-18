/**
 * CIDP YMS Shared Table Utilities
 * Handles row-limit dropdown filtering ("Tampilkan N data") and consecutive row numbering (No).
 */
(function () {
    'use strict';

    function getDataRows(table) {
        if (!table) return [];
        const tbody = table.tBodies && table.tBodies[0] ? table.tBodies[0] : table.querySelector('tbody');
        if (!tbody) return [];
        return Array.from(tbody.querySelectorAll('tr')).filter(function (tr) {
            if (tr.hasAttribute('data-empty-row')) return false;
            const cells = tr.children;
            if (cells.length === 1 && cells[0].hasAttribute('colspan')) return false;
            return true;
        });
    }

    function parseLimit(selectEl) {
        if (!selectEl) return Infinity;
        const val = String(selectEl.value || 'all').toLowerCase();
        if (val === 'all' || val === '') return Infinity;
        const n = parseInt(val, 10);
        return Number.isFinite(n) && n > 0 ? n : Infinity;
    }

    function apply(targetOrSelector, options) {
        const opts = options || {};
        const table = (typeof targetOrSelector === 'string')
            ? document.querySelector(targetOrSelector)
            : targetOrSelector;
        if (!table) return { totalMatched: 0, shownCount: 0, totalRows: 0 };

        const selectEl = opts.selectEl
            || (opts.selectSelector ? document.querySelector(opts.selectSelector) : null)
            || (table.id ? document.querySelector('[data-limit-target="#' + table.id + '"]') : null)
            || (table.getAttribute('data-cidp-table')
                ? document.querySelector('[data-limit-target="[data-cidp-table=\'' + table.getAttribute('data-cidp-table') + '\']"]')
                : null);

        const limit = parseLimit(selectEl);
        const rows = getDataRows(table);

        let totalMatched = 0;
        let shownCount = 0;

        rows.forEach(function (row) {
            const isFilteredOut = row.dataset.filteredOut === '1';
            if (isFilteredOut) {
                row.style.display = 'none';
                return;
            }
            totalMatched++;
            if (shownCount < limit) {
                row.style.display = '';
                shownCount++;
                const numCell = row.querySelector('[data-row-num]');
                if (numCell) {
                    numCell.textContent = String(shownCount);
                }
            } else {
                row.style.display = 'none';
            }
        });

        const countTarget = opts.countEl
            || (opts.countSelector ? document.querySelector(opts.countSelector) : null)
            || (selectEl && selectEl.dataset.countTarget ? document.querySelector(selectEl.dataset.countTarget) : null);

        if (countTarget) {
            if (typeof opts.formatCount === 'function') {
                countTarget.textContent = opts.formatCount(shownCount, totalMatched, rows.length);
            } else if (selectEl && selectEl.dataset.countUnit) {
                const unit = selectEl.dataset.countUnit;
                countTarget.textContent = (shownCount < totalMatched)
                    ? ('Menampilkan ' + shownCount + ' dari ' + totalMatched + ' ' + unit)
                    : ('Menampilkan ' + shownCount + ' ' + unit);
            }
        }

        return { totalMatched: totalMatched, shownCount: shownCount, totalRows: rows.length };
    }

    function initAll() {
        const selects = document.querySelectorAll('select[data-limit-target]');
        selects.forEach(function (sel) {
            if (sel.dataset.limitBound === '1') return;
            sel.dataset.limitBound = '1';

            const run = function () {
                const cbName = sel.dataset.limitCallback;
                if (cbName && typeof window[cbName] === 'function') {
                    window[cbName]();
                } else {
                    apply(sel.dataset.limitTarget, { selectEl: sel });
                }
            };

            sel.addEventListener('change', run);

            const table = document.querySelector(sel.dataset.limitTarget);
            if (table) {
                const tbody = table.tBodies && table.tBodies[0] ? table.tBodies[0] : table.querySelector('tbody');
                if (tbody && sel.dataset.observeBody === '1') {
                    const obs = new MutationObserver(function () {
                        // Avoid re-entrancy when updating data-row-num text
                        if (tbody.dataset.applyingLimit === '1') return;
                        tbody.dataset.applyingLimit = '1';
                        try {
                            apply(table, { selectEl: sel });
                        } finally {
                            tbody.dataset.applyingLimit = '0';
                        }
                    });
                    obs.observe(tbody, { childList: true });
                }
            }

            // Apply initial limit cleanly on page load
            run();
        });
    }

    window.CIDPTableTools = {
        apply: apply,
        initAll: initAll,
        parseLimit: parseLimit
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        setTimeout(initAll, 0);
    }
})();
