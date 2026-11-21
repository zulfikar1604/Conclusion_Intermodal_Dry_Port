/**
 * CIDP Action Guard: Global Double-Verification ("Ya / Tidak") & Zero-Reload Engine
 * Prevents full page refreshes & preserves <main>.scrollTop, active tabs, and filters.
 */
(function () {
    'use strict';

    if (window.CIDPActionGuardLoaded) return;
    window.CIDPActionGuardLoaded = true;

    const MAIN_SELECTOR = '#appFrame > main';
    let activeConfirmResolver = null;

    function getMainEl() {
        return document.querySelector(MAIN_SELECTOR);
    }

    function getMainInner() {
        const main = getMainEl();
        return main ? main.querySelector(':scope > div') : null;
    }

    function escHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    // =========================================================================
    // 1. FLOATING TOAST SYSTEM (Non-Blocking Feedback at Current Viewport)
    // =========================================================================
    function ensureToastHost() {
        let host = document.getElementById('cidpGlobalToastHost');
        if (!host) {
            host = document.createElement('div');
            host.id = 'cidpGlobalToastHost';
            host.className = 'fixed top-16 sm:top-20 right-4 sm:right-6 z-[115] flex flex-col gap-2.5 max-w-[340px] w-[calc(100%-2rem)] pointer-events-none';
            document.body.appendChild(host);
        }
        return host;
    }

    window.cidpToast = function (title, message, type) {
        const tone = type || 'success';
        const styles = {
            success: {
                border: 'border-emerald-200',
                iconWrap: 'bg-emerald-100 text-emerald-700',
                icon: 'fa-circle-check',
                bar: 'from-emerald-500 to-teal-500'
            },
            error: {
                border: 'border-rose-200',
                iconWrap: 'bg-rose-100 text-rose-700',
                icon: 'fa-circle-exclamation',
                bar: 'from-rose-500 to-red-600'
            },
            danger: {
                border: 'border-rose-200',
                iconWrap: 'bg-rose-100 text-rose-700',
                icon: 'fa-triangle-exclamation',
                bar: 'from-rose-500 to-red-600'
            },
            warning: {
                border: 'border-amber-200',
                iconWrap: 'bg-amber-100 text-amber-700',
                icon: 'fa-triangle-exclamation',
                bar: 'from-amber-500 to-orange-500'
            },
            info: {
                border: 'border-sky-200',
                iconWrap: 'bg-sky-100 text-[#0170b9]',
                icon: 'fa-circle-info',
                bar: 'from-[#0170b9] to-cyan-500'
            }
        }[tone] || {
            border: 'border-emerald-200',
            iconWrap: 'bg-emerald-100 text-emerald-700',
            icon: 'fa-circle-check',
            bar: 'from-emerald-500 to-teal-500'
        };

        const host = ensureToastHost();
        const card = document.createElement('div');
        card.className = 'pointer-events-auto bg-white/95 backdrop-blur-md shadow-xl rounded-xl border ' + styles.border + ' overflow-hidden transition-all duration-300 transform translate-y-0 opacity-100 animate-fadeIn';
        card.setAttribute('role', 'status');

        card.innerHTML =
            '<div class="p-3.5 flex items-start gap-2.5">' +
                '<div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 ' + styles.iconWrap + '">' +
                    '<i class="fa-solid ' + styles.icon + ' text-sm"></i>' +
                '</div>' +
                '<div class="flex-1 min-w-0">' +
                    '<div class="flex items-start justify-between gap-2">' +
                        '<p class="text-xs font-extrabold text-slate-900 leading-snug">' + escHtml(title || 'Notifikasi') + '</p>' +
                        '<button type="button" class="cidp-toast-close text-slate-400 hover:text-slate-700 p-0.5 leading-none cursor-pointer" aria-label="Tutup">' +
                            '<i class="fa-solid fa-xmark text-xs"></i>' +
                        '</button>' +
                    '</div>' +
                    (message ? '<p class="text-[11px] text-slate-600 mt-0.5 leading-relaxed break-words">' + escHtml(message) + '</p>' : '') +
                '</div>' +
            '</div>' +
            '<div class="h-1 bg-slate-100 w-full overflow-hidden">' +
                '<div class="cidp-toast-bar h-full bg-gradient-to-r ' + styles.bar + ' w-full transition-all duration-[4200ms] ease-linear"></div>' +
            '</div>';

        const removeCard = function () {
            card.style.opacity = '0';
            card.style.transform = 'translateY(-8px)';
            setTimeout(function () {
                if (card.parentNode) card.parentNode.removeChild(card);
            }, 250);
        };

        card.querySelector('.cidp-toast-close').addEventListener('click', removeCard);
        host.appendChild(card);

        requestAnimationFrame(function () {
            const bar = card.querySelector('.cidp-toast-bar');
            if (bar) bar.style.width = '0%';
        });
        setTimeout(removeCard, 4300);
    };

    // =========================================================================
    // 2. DOUBLE VERIFICATION MODAL ("Ya / Tidak")
    // =========================================================================
    function ensureConfirmModal() {
        let modal = document.getElementById('cidpActionConfirmModal');
        if (modal) return modal;

        modal = document.createElement('div');
        modal.id = 'cidpActionConfirmModal';
        modal.className = 'fixed inset-0 z-[120] hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4';
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');
        modal.innerHTML =
            '<div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200 animate-fadeIn">' +
                '<div id="cidpConfirmHeader" class="px-5 py-4 border-b border-slate-100 flex items-start gap-3 bg-slate-50/70">' +
                    '<div id="cidpConfirmIconWrap" class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-blue-100 text-[#0170b9]">' +
                        '<i id="cidpConfirmIcon" class="fa-solid fa-shield-check text-base"></i>' +
                    '</div>' +
                    '<div class="flex-1 min-w-0">' +
                        '<span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Verifikasi Ganda Aksi</span>' +
                        '<h3 id="cidpConfirmTitle" class="text-sm font-extrabold text-slate-900 mt-0.5 leading-snug">Konfirmasi Aksi</h3>' +
                    '</div>' +
                    '<button type="button" id="cidpConfirmCloseX" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg transition cursor-pointer" aria-label="Tutup">' +
                        '<i class="fa-solid fa-xmark text-sm"></i>' +
                    '</button>' +
                '</div>' +
                '<div class="p-5 space-y-3">' +
                    '<div id="cidpConfirmTargetWrap" class="hidden">' +
                        '<span id="cidpConfirmTarget" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 font-mono text-[11px] font-extrabold text-slate-800"></span>' +
                    '</div>' +
                    '<p id="cidpConfirmMessage" class="text-xs text-slate-600 leading-relaxed whitespace-pre-line"></p>' +
                    '<p class="text-[11px] text-slate-400 flex items-center gap-1.5 pt-1">' +
                        '<i class="fa-solid fa-bolt text-amber-500"></i>' +
                        '<span>Eksekusi diproses langsung tanpa me-refresh halaman &amp; posisi scroll tetap terjaga.</span>' +
                    '</p>' +
                '</div>' +
                '<div class="px-5 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">' +
                    '<button type="button" id="cidpConfirmNoBtn" class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 text-xs font-bold transition cursor-pointer">' +
                        'Tidak, Batal' +
                    '</button>' +
                    '<button type="button" id="cidpConfirmYesBtn" class="px-4 py-2 rounded-xl bg-[#0170b9] hover:bg-[#004b87] text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5 cursor-pointer">' +
                        '<i class="fa-solid fa-check text-[11px]"></i>' +
                        '<span id="cidpConfirmYesText">Ya, Lanjutkan</span>' +
                    '</button>' +
                '</div>' +
            '</div>';

        document.body.appendChild(modal);

        const settle = function (result) {
            modal.classList.add('hidden');
            const resolver = activeConfirmResolver;
            activeConfirmResolver = null;
            if (typeof resolver === 'function') resolver(Boolean(result));
        };

        modal.querySelector('#cidpConfirmNoBtn').addEventListener('click', function () { settle(false); });
        modal.querySelector('#cidpConfirmCloseX').addEventListener('click', function () { settle(false); });
        modal.querySelector('#cidpConfirmYesBtn').addEventListener('click', function () { settle(true); });
        modal.addEventListener('click', function (ev) {
            if (ev.target === modal) settle(false);
        });
        document.addEventListener('keydown', function (ev) {
            if (ev.key === 'Escape' && !modal.classList.contains('hidden')) {
                ev.preventDefault();
                settle(false);
            }
        });

        return modal;
    }

    window.cidpConfirm = function (options) {
        const opts = (typeof options === 'string') ? { message: options } : (options || {});
        if (typeof activeConfirmResolver === 'function') {
            activeConfirmResolver(false);
            activeConfirmResolver = null;
        }

        const modal = ensureConfirmModal();
        const tone = opts.tone || 'primary';
        const iconWrap = modal.querySelector('#cidpConfirmIconWrap');
        const icon = modal.querySelector('#cidpConfirmIcon');
        const yesBtn = modal.querySelector('#cidpConfirmYesBtn');
        const targetWrap = modal.querySelector('#cidpConfirmTargetWrap');
        const targetEl = modal.querySelector('#cidpConfirmTarget');

        const toneCfg = {
            danger: {
                wrap: 'bg-rose-100 text-rose-700',
                icon: 'fa-solid fa-triangle-exclamation text-base',
                btn: 'bg-rose-600 hover:bg-rose-700 text-white'
            },
            warning: {
                wrap: 'bg-amber-100 text-amber-700',
                icon: 'fa-solid fa-circle-question text-base',
                btn: 'bg-amber-600 hover:bg-amber-700 text-white'
            },
            primary: {
                wrap: 'bg-blue-100 text-[#0170b9]',
                icon: 'fa-solid fa-shield-halved text-base',
                btn: 'bg-[#0170b9] hover:bg-[#004b87] text-white'
            }
        }[tone] || {
            wrap: 'bg-blue-100 text-[#0170b9]',
            icon: 'fa-solid fa-shield-halved text-base',
            btn: 'bg-[#0170b9] hover:bg-[#004b87] text-white'
        };

        iconWrap.className = 'w-10 h-10 rounded-xl flex items-center justify-center shrink-0 ' + toneCfg.wrap;
        icon.className = toneCfg.icon;
        yesBtn.className = 'px-4 py-2 rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-1.5 cursor-pointer ' + toneCfg.btn;

        modal.querySelector('#cidpConfirmTitle').textContent = opts.title || 'Konfirmasi Eksekusi Aksi';
        modal.querySelector('#cidpConfirmMessage').textContent = opts.message || 'Apakah Anda yakin ingin menjalankan tindakan ini?';
        modal.querySelector('#cidpConfirmNoBtn').textContent = opts.cancelText || 'Tidak, Batal';
        modal.querySelector('#cidpConfirmYesText').textContent = opts.confirmText || 'Ya, Lanjutkan';

        if (opts.target) {
            targetEl.textContent = String(opts.target);
            targetWrap.classList.remove('hidden');
        } else {
            targetWrap.classList.add('hidden');
        }

        modal.classList.remove('hidden');
        setTimeout(function () {
            const noBtn = modal.querySelector('#cidpConfirmNoBtn');
            if (noBtn) noBtn.focus();
        }, 20);

        return new Promise(function (resolve) {
            activeConfirmResolver = resolve;
        });
    };

    // =========================================================================
    // 3. LEGACY INLINE CONFIRM EXTRACTION & CONTEXT INFERENCE
    // =========================================================================
    function extractConfirmLiteral(onsubmitAttr) {
        if (!onsubmitAttr) return null;
        const m = String(onsubmitAttr).match(/^\s*return\s+confirm\s*\(\s*(['"])([\s\S]*?)\1\s*\)\s*;?\s*$/i);
        if (!m) return null;
        return m[2]
            .replace(/\\'/g, "'")
            .replace(/\\"/g, '"')
            .replace(/\\n/g, '\n');
    }

    function normalizeLegacyConfirmForms(root) {
        if (!root) return;
        root.querySelectorAll('form[onsubmit]').forEach(function (form) {
            const raw = form.getAttribute('onsubmit') || '';
            const extracted = extractConfirmLiteral(raw);
            if (extracted !== null) {
                if (!form.dataset.confirm) form.dataset.confirm = extracted;
                form.removeAttribute('onsubmit');
            }
        });
    }

    function inferTone(text, submitter) {
        const s = (String(text || '') + ' ' + String(submitter && submitter.className ? submitter.className : '')).toLowerCase();
        if (/(tahan|jalur merah|hold|lepas|hapus|delete|reset|nonaktif|putus|alarm|rose-600|red-600)/.test(s)) return 'danger';
        if (/(tutup|selesai|rilis|gate.?out|unlock|uji|ats|unplug|amber-600|orange-600|purple-600)/.test(s)) return 'warning';
        return 'primary';
    }

    function inferTargetLabel(form) {
        if (form.dataset.confirmTarget) return form.dataset.confirmTarget;
        const priorityNames = ['container_number', 'invoice_number', 'license_plate', 'equipment_id', 'slot_id', 'rack_id', 'truck_id', 'job_id', 'email'];
        for (let i = 0; i < priorityNames.length; i++) {
            const inp = form.querySelector('[name="' + priorityNames[i] + '"]');
            if (inp && inp.value && String(inp.value).trim()) {
                return String(inp.value).trim();
            }
        }
        return '';
    }

    function buildConfirmPayload(form, submitter) {
        const explicitMsg = (submitter && submitter.dataset && submitter.dataset.confirm) || form.dataset.confirm || '';
        const title = (submitter && submitter.dataset && submitter.dataset.confirmTitle) || form.dataset.confirmTitle || 'Konfirmasi Eksekusi Aksi';
        const btnText = submitter
            ? ((submitter.innerText || submitter.value || submitter.getAttribute('title') || '').replace(/\s+/g, ' ').trim())
            : '';
        const fallbackMsg = btnText
            ? ('Apakah Anda yakin ingin memproses aksi "' + btnText + '"?')
            : 'Apakah Anda yakin ingin menyimpan / memproses aksi ini?';
        const message = explicitMsg || fallbackMsg;
        const tone = (submitter && submitter.dataset && submitter.dataset.confirmTone)
            || form.dataset.confirmTone
            || inferTone(message + ' ' + btnText, submitter);
        const target = (submitter && submitter.dataset && submitter.dataset.confirmTarget) || inferTargetLabel(form);

        return {
            title: title,
            message: message,
            target: target,
            tone: tone,
            confirmText: (submitter && submitter.dataset && submitter.dataset.confirmYes) || form.dataset.confirmYes || 'Ya, Proses',
            cancelText: 'Tidak, Batal'
        };
    }

    // =========================================================================
    // 4. ZERO-RELOAD PARTIAL DOM SNAPSHOT & STATE PRESERVATION ENGINE
    // =========================================================================
    function syncBracketedJsonGlobal(scriptText, varName) {
        const pattern = new RegExp('(?:window\\.' + varName + '|(?:var|let|const)\\s+' + varName + ')\\s*=\\s*', 'g');
        let match;
        while ((match = pattern.exec(scriptText)) !== null) {
            const startIdx = match.index + match[0].length;
            const firstChar = scriptText[startIdx];
            if (firstChar !== '[' && firstChar !== '{') continue;
            const openChar = firstChar;
            const closeChar = (openChar === '[') ? ']' : '}';
            let depth = 0;
            let inStr = false;
            let escaped = false;
            let endIdx = -1;
            for (let i = startIdx; i < scriptText.length; i++) {
                const ch = scriptText[i];
                if (escaped) { escaped = false; continue; }
                if (ch === '\\' && inStr) { escaped = true; continue; }
                if (ch === '"') { inStr = !inStr; continue; }
                if (inStr) continue;
                if (ch === openChar) depth++;
                else if (ch === closeChar) {
                    depth--;
                    if (depth === 0) { endIdx = i; break; }
                }
            }
            if (endIdx > startIdx) {
                try {
                    const parsed = JSON.parse(scriptText.slice(startIdx, endIdx + 1));
                    window[varName] = parsed;
                } catch (e) {}
            }
        }
    }

    function syncPageJsonGlobalsFromDoc(parsedDoc) {
        const tracked = ['activeReefersData', 'customsData', 'wagonsInbound', 'wagonsOutbound', 'cfsPalletsData'];
        const scripts = parsedDoc.querySelectorAll('#appFrame > main script');
        scripts.forEach(function (sc) {
            if (sc.src) return;
            const text = sc.textContent || '';
            for (let i = 0; i < tracked.length; i++) {
                if (text.indexOf(tracked[i]) !== -1) {
                    syncBracketedJsonGlobal(text, tracked[i]);
                }
            }
        });
    }

    function detectServerFeedbackBanner(mainWrap) {
        if (!mainWrap) return null;
        const candidates = mainWrap.querySelectorAll('div.rounded-xl.border.flex.items-start, div.mb-4.rounded-xl.border');
        for (let i = 0; i < Math.min(candidates.length, 10); i++) {
            const el = candidates[i];
            if (!el || el.id === 'execStatusBar' || el.closest('#execStatusBar') || el.closest('.fixed.inset-0')) continue;
            const cls = el.className || '';
            const isNotice = /(bg-emerald-50|bg-green-50|bg-rose-50|bg-red-50|bg-amber-50|bg-blue-50)/.test(cls)
                && /(border-emerald|border-green|border-rose|border-red|border-amber|border-blue)/.test(cls);
            if (!isNotice) continue;

            const h = el.querySelector('h4, strong, .font-bold');
            const p = el.querySelector('p');
            const fullText = (el.textContent || '').replace(/\s+/g, ' ').trim();
            if (!fullText || fullText.length < 4) continue;

            let tone = 'success';
            if (/(rose|red)/.test(cls)) tone = 'error';
            else if (/amber/.test(cls)) tone = 'warning';
            else if (/blue/.test(cls) && !/berhasil|sukses/i.test(fullText)) tone = 'info';

            return {
                title: h ? h.textContent.replace(/\s+/g, ' ').trim() : (tone === 'error' ? 'Aksi Gagal' : 'Aksi Berhasil Diproses'),
                message: p ? p.textContent.replace(/\s+/g, ' ').trim() : fullText,
                type: tone
            };
        }
        return null;
    }

    function captureActiveTabs(mainWrap) {
        const active = {};
        const groups = [
            { fn: 'switchGateTab', sel: '.gate-tab-content:not(.hidden)' },
            { fn: 'switchCustomsTab', sel: '.customs-tab-content:not(.hidden)' },
            { fn: 'switchReeferTab', sel: '.reefer-tab-content:not(.hidden)' },
            { fn: 'switchSettingsTab', sel: '.settings-tab-content:not(.hidden)' }
        ];
        groups.forEach(function (g) {
            const el = mainWrap.querySelector(g.sel);
            if (el && el.id) active[g.fn] = el.id;
        });
        return active;
    }

    function captureFilterInputs(mainWrap) {
        const saved = {};
        mainWrap.querySelectorAll('input[id], select[id]').forEach(function (el) {
            if (el.closest('.fixed.inset-0')) return;
            const id = el.id;
            if (!id) return;
            if (/search|filter|limit|selectReeferGraphBox|selectReeferRack/i.test(id) || el.type === 'search' || el.hasAttribute('data-limit-target')) {
                saved[id] = el.value;
            }
        });
        return saved;
    }

    window.cidpApplyHtmlSnapshot = function (htmlText, opts) {
        const options = opts || {};
        const mainEl = getMainEl();
        const curInner = getMainInner();
        if (!mainEl || !curInner) return false;

        const parser = new DOMParser();
        const parsedDoc = parser.parseFromString(htmlText, 'text/html');
        const newInner = parsedDoc.querySelector(MAIN_SELECTOR + ' > div');

        if (!newInner) {
            return false;
        }

        const savedScrollTop = mainEl.scrollTop;
        const activeTabs = captureActiveTabs(curInner);
        const savedFilters = captureFilterInputs(curInner);

        // Tutup modal yang mungkin terbuka sebelum DOM diganti
        curInner.querySelectorAll('.fixed.inset-0:not(.hidden)').forEach(function (m) {
            m.classList.add('hidden');
            m.classList.remove('flex');
        });

        // Sinkronisasi dataset JSON PHP ke global window sebelum tag script dibuang
        syncPageJsonGlobalsFromDoc(parsedDoc);

        const clone = newInner.cloneNode(true);
        clone.querySelectorAll('script').forEach(function (sc) {
            sc.parentNode.removeChild(sc);
        });

        const frag = document.createDocumentFragment();
        while (clone.firstChild) {
            frag.appendChild(clone.firstChild);
        }
        curInner.replaceChildren(frag);

        // Re-aktivasi tab aktif kecuali form secara eksplisit meminta ?tab= baru
        let explicitTab = null;
        if (options.actionUrl) {
            try {
                const u = new URL(options.actionUrl, window.location.href);
                const t = u.searchParams.get('tab');
                if (t) explicitTab = t.startsWith('tab-') ? t : ('tab-' + t);
            } catch (e) {}
        }
        Object.keys(activeTabs).forEach(function (fnName) {
            const targetTabId = explicitTab || activeTabs[fnName];
            if (typeof window[fnName] === 'function' && targetTabId && document.getElementById(targetTabId)) {
                try { window[fnName](targetTabId); } catch (e) {}
            }
        });

        // Pulihkan input search / filter / limit tampilan baris tabel
        Object.keys(savedFilters).forEach(function (id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.value = savedFilters[id];
            if (el.hasAttribute('data-limit-target') || el.tagName === 'SELECT') {
                el.dispatchEvent(new Event('change', { bubbles: true }));
            } else {
                el.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });

        if (window.CIDPTableTools && typeof window.CIDPTableTools.initAll === 'function') {
            window.CIDPTableTools.initAll();
        }
        if (typeof window.cidpOnSoftRefresh === 'function') {
            try { window.cidpOnSoftRefresh(parsedDoc); } catch (e) {}
        }

        normalizeLegacyConfirmForms(curInner);

        // Kunci posisi scroll <main> persis di tempat terakhir
        const restoreScroll = function () {
            mainEl.scrollTop = savedScrollTop;
        };
        restoreScroll();
        requestAnimationFrame(restoreScroll);
        setTimeout(restoreScroll, 50);

        if (options.toast) {
            window.cidpToast(options.toast.title, options.toast.message, options.toast.type || 'success');
        } else {
            const detected = detectServerFeedbackBanner(curInner);
            if (detected) {
                window.cidpToast(detected.title, detected.message, detected.type);
            } else if (!options.silentToast) {
                window.cidpToast('Aksi Berhasil Diproses', 'Data diperbarui di tempat tanpa me-refresh halaman.', 'success');
            }
        }

        return true;
    };

    window.cidpSoftRefreshContent = async function (toastOpts) {
        const res = await fetch(window.location.href, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'CIDP-Soft-Refresh' }
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const htmlText = await res.text();
        const ok = window.cidpApplyHtmlSnapshot(htmlText, { toast: toastOpts });
        if (!ok) {
            window.location.reload();
        }
    };

    // =========================================================================
    // 5. GLOBAL CAPTURE SUBMIT INTERCEPTOR FOR NATIVE PHP POST FORMS
    // =========================================================================
    function setButtonBusy(btn, busy) {
        if (!btn) return;
        if (busy) {
            if (btn.dataset.cidpBusy === '1') return;
            btn.dataset.cidpBusy = '1';
            btn.dataset.cidpOrigHtml = btn.innerHTML;
            btn.disabled = true;
            btn.classList.add('opacity-75', 'pointer-events-none');
            const rect = btn.getBoundingClientRect();
            if (rect.width > 70) {
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1 text-xs"></i><span>Memproses...</span>';
            } else {
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i>';
            }
        } else if (btn.dataset.cidpBusy === '1') {
            btn.disabled = false;
            btn.classList.remove('opacity-75', 'pointer-events-none');
            if (btn.dataset.cidpOrigHtml !== undefined) {
                btn.innerHTML = btn.dataset.cidpOrigHtml;
                delete btn.dataset.cidpOrigHtml;
            }
            delete btn.dataset.cidpBusy;
        }
    }

    document.addEventListener('submit', async function (ev) {
        const form = ev.target;
        if (!form || form.tagName !== 'FORM') return;
        if (form.dataset.cidpBypass === '1' || form.dataset.noIntercept === '1') return;
        if (!form.closest('#appFrame')) return;

        const method = (form.getAttribute('method') || 'GET').toUpperCase();
        if (method !== 'POST') return;
        if (form.getAttribute('target') === '_blank') return;

        // Hormati form yang sudah memakai custom JS submit handler sendiri (api/crud.php, dll.)
        const inlineOnsubmit = (form.getAttribute('onsubmit') || '').trim();
        if (inlineOnsubmit && extractConfirmLiteral(inlineOnsubmit) === null) {
            return;
        }

        ev.preventDefault();
        ev.stopPropagation();

        if (form.dataset.cidpSubmitting === '1') return;

        let submitter = ev.submitter || document.activeElement;
        if (!submitter || !form.contains(submitter) || (submitter.type !== 'submit' && submitter.tagName !== 'BUTTON')) {
            submitter = form.querySelector('button[type="submit"], input[type="submit"]');
        }

        if (form.dataset.noConfirm !== '1' && !(submitter && submitter.dataset && submitter.dataset.noConfirm === '1')) {
            const payload = buildConfirmPayload(form, submitter);
            const approved = await window.cidpConfirm(payload);
            if (!approved) return;
        }

        if (form.dataset.fullReload === '1' || (submitter && submitter.dataset && submitter.dataset.fullReload === '1')) {
            form.dataset.cidpBypass = '1';
            if (submitter && submitter.name && !form.querySelector('input[type="hidden"][name="' + submitter.name + '"]')) {
                const hid = document.createElement('input');
                hid.type = 'hidden';
                hid.name = submitter.name;
                hid.value = submitter.value || '';
                form.appendChild(hid);
            }
            form.submit();
            return;
        }

        form.dataset.cidpSubmitting = '1';
        setButtonBusy(submitter, true);

        try {
            let fd;
            try {
                fd = submitter ? new FormData(form, submitter) : new FormData(form);
            } catch (err) {
                fd = new FormData(form);
            }
            if (submitter && submitter.name && !fd.has(submitter.name)) {
                fd.append(submitter.name, submitter.value || '');
            }

            const actionUrl = form.getAttribute('action') || window.location.href;
            const res = await fetch(actionUrl, {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'CIDP-Async-Action' }
            });

            if (!res.ok) {
                throw new Error('Server mengembalikan status HTTP ' + res.status);
            }

            const htmlText = await res.text();
            const swapped = window.cidpApplyHtmlSnapshot(htmlText, {
                actionUrl: actionUrl,
                submitter: submitter
            });

            if (!swapped) {
                form.dataset.cidpBypass = '1';
                form.submit();
            }
        } catch (err) {
            console.error('[CIDP ActionGuard] Submit error:', err);
            window.cidpToast('Gagal Memproses Aksi', err.message || 'Terjadi gangguan koneksi ke server.', 'error');
        } finally {
            delete form.dataset.cidpSubmitting;
            setButtonBusy(submitter, false);
        }
    }, true);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            normalizeLegacyConfirmForms(getMainInner() || document.body);
        });
    } else {
        normalizeLegacyConfirmForms(getMainInner() || document.body);
    }
})();
