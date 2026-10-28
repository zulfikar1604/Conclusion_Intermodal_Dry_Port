/**
 * =============================================================================
 * CIDP PRINT ENGINE — ENTERPRISE OFFICIAL DOCUMENT & DIGITAL STAMP SYSTEM
 * Conclusion Intermodal Dry Port (CIDP) 35 Ha — Yard Management System
 * Standar: Dokumen Resmi Pelabuhan Kering, Cetak Format A4, Tanpa Screenshot Layar
 * Dilengkapi: Kop Surat Resmi, Tabel Presisi, Stempel Digital Basah SVG & Tanda Tangan
 * =============================================================================
 */

const CIDPPrint = {
    // Profil Perusahaan untuk Kop Surat Resmi
    company: {
        name: 'CONCLUSION INTERMODAL DRY PORT',
        subname: 'PT MULTI TERMINAL INDONESIA — YARD MANAGEMENT SYSTEM',
        address: 'Kawasan Industri Jababeka, Jl. Pelabuhan Kering No. 1, Cikarang, Bekasi, Jawa Barat 17530',
        contact: 'Telepon: (021) 8911-2345 | Fax: (021) 8911-2346 | Email: ops@conclusion-dryport.co.id',
        web: 'www.conclusion-dryport.co.id &bull; CEISA 4.0 Connected Hub',
        license: 'Keputusan Menteri Keuangan RI No. KMK-412/KM.4/2026 tentang Kawasan Pabean Dry Port'
    },

    // Kumpulan Vektor Stempel Digital Resmi (SVG Dual-Ring Stamp)
    stamps: {
        /**
         * Stempel Digital Finance & Billing (LUNAS / PAID) - Warna Merah Resmi
         */
        finance: function(refNo, dateStr) {
            const d = dateStr || new Date().toLocaleDateString('id-ID');
            const ref = refNo || 'INV/2026/FIN';
            return `
            <div class="digital-stamp stamp-finance" style="display:inline-block; position:relative; transform:rotate(-5deg); filter:drop-shadow(0 2px 4px rgba(185,28,28,0.25)); user-select:none;">
                <svg viewBox="0 0 160 160" width="135" height="135" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <path id="stmpFinTop" d="M 22,80 A 58,58 0 1,1 138,80" fill="none" />
                        <path id="stmpFinBot" d="M 138,80 A 58,58 0 0,1 22,80" fill="none" />
                    </defs>
                    <!-- Outer Dual Circle -->
                    <circle cx="80" cy="80" r="75" fill="#fef2f2" fill-opacity="0.4" stroke="#dc2626" stroke-width="2.6" />
                    <circle cx="80" cy="80" r="70" fill="none" stroke="#dc2626" stroke-width="1" stroke-dasharray="3 2" />
                    <circle cx="80" cy="80" r="48" fill="none" stroke="#dc2626" stroke-width="1.8" />
                    <circle cx="80" cy="80" r="45" fill="none" stroke="#dc2626" stroke-width="0.6" />

                    <!-- Teks Melingkar Atas -->
                    <text font-family="'Segoe UI', Roboto, Arial, sans-serif" font-size="8.2" font-weight="900" fill="#dc2626" letter-spacing="1">
                        <textPath href="#stmpFinTop" xlink:href="#stmpFinTop" startOffset="50%" text-anchor="middle">
                            ★ PT CONCLUSION DRY PORT ★
                        </textPath>
                    </text>

                    <!-- Teks Melingkar Bawah -->
                    <text font-family="'Segoe UI', Roboto, Arial, sans-serif" font-size="7.5" font-weight="800" fill="#dc2626" letter-spacing="0.8">
                        <textPath href="#stmpFinBot" xlink:href="#stmpFinBot" startOffset="50%" text-anchor="middle">
                            FINANCE &amp; BILLING DEPT.
                        </textPath>
                    </text>

                    <!-- Bintang & Ornamen Tengah -->
                    <text x="80" y="58" font-size="8" fill="#dc2626" text-anchor="middle">★★★★★</text>

                    <!-- Kotak Status LUNAS -->
                    <rect x="25" y="65" width="110" height="28" rx="4" fill="#dc2626" fill-opacity="0.12" stroke="#dc2626" stroke-width="1.4" />
                    <text x="80" y="81" font-family="'Segoe UI', Arial, sans-serif" font-size="14.5" font-weight="900" fill="#dc2626" text-anchor="middle" letter-spacing="3.5">
                        L U N A S
                    </text>
                    <text x="80" y="90" font-family="'Segoe UI', monospace" font-size="6.5" font-weight="800" fill="#dc2626" text-anchor="middle">
                        PAID &bull; ${d}
                    </text>

                    <!-- Kode Otorisasi Digital -->
                    <text x="80" y="105" font-size="6.5" font-family="'Courier New', monospace" font-weight="bold" fill="#dc2626" text-anchor="middle">
                        AUTH: ${ref}
                    </text>
                </svg>
            </div>`;
        },

        /**
         * Stempel Digital Operasional Terminal (VERIFIED & APPROVED) - Warna Navy Port
         */
        ops: function(refNo, dateStr) {
            const d = dateStr || new Date().toLocaleDateString('id-ID');
            const ref = refNo || 'CIDP/OPS/2026';
            return `
            <div class="digital-stamp stamp-ops" style="display:inline-block; position:relative; transform:rotate(-4deg); filter:drop-shadow(0 2px 4px rgba(0,47,94,0.25)); user-select:none;">
                <svg viewBox="0 0 160 160" width="135" height="135" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <path id="stmpOpsTop" d="M 22,80 A 58,58 0 1,1 138,80" fill="none" />
                        <path id="stmpOpsBot" d="M 138,80 A 58,58 0 0,1 22,80" fill="none" />
                    </defs>
                    <circle cx="80" cy="80" r="75" fill="#f0f7ff" fill-opacity="0.45" stroke="#002f5e" stroke-width="2.6" />
                    <circle cx="80" cy="80" r="70" fill="none" stroke="#002f5e" stroke-width="1" stroke-dasharray="3 2" />
                    <circle cx="80" cy="80" r="48" fill="none" stroke="#002f5e" stroke-width="1.8" />
                    <circle cx="80" cy="80" r="45" fill="none" stroke="#002f5e" stroke-width="0.6" />

                    <text font-family="'Segoe UI', Roboto, Arial, sans-serif" font-size="8" font-weight="900" fill="#002f5e" letter-spacing="0.9">
                        <textPath href="#stmpOpsTop" xlink:href="#stmpOpsTop" startOffset="50%" text-anchor="middle">
                            ★ CONCLUSION INTERMODAL DRY PORT ★
                        </textPath>
                    </text>

                    <text font-family="'Segoe UI', Roboto, Arial, sans-serif" font-size="7.5" font-weight="800" fill="#002f5e" letter-spacing="0.8">
                        <textPath href="#stmpOpsBot" xlink:href="#stmpOpsBot" startOffset="50%" text-anchor="middle">
                            YARD &amp; TERMINAL OPERATIONS
                        </textPath>
                    </text>

                    <text x="80" y="58" font-size="8" fill="#002f5e" text-anchor="middle">★★★★★</text>

                    <rect x="23" y="65" width="114" height="28" rx="4" fill="#002f5e" fill-opacity="0.1" stroke="#002f5e" stroke-width="1.4" />
                    <text x="80" y="80" font-family="'Segoe UI', Arial, sans-serif" font-size="12" font-weight="900" fill="#002f5e" text-anchor="middle" letter-spacing="2">
                        APPROVED
                    </text>
                    <text x="80" y="90" font-family="'Segoe UI', monospace" font-size="6.5" font-weight="800" fill="#002f5e" text-anchor="middle">
                        OPERATIONAL VALIDATED
                    </text>

                    <text x="80" y="105" font-size="6.5" font-family="'Courier New', monospace" font-weight="bold" fill="#002f5e" text-anchor="middle">
                        REF: ${ref}
                    </text>
                </svg>
            </div>`;
        },

        /**
         * Stempel Digital Gudang CFS & Tally Master - Warna Hijau Zamrud
         */
        cfs: function(refNo, dateStr) {
            const d = dateStr || new Date().toLocaleDateString('id-ID');
            const ref = refNo || 'CFS/TALLY/2026';
            return `
            <div class="digital-stamp stamp-cfs" style="display:inline-block; position:relative; transform:rotate(-5deg); filter:drop-shadow(0 2px 4px rgba(5,150,105,0.25)); user-select:none;">
                <svg viewBox="0 0 160 160" width="135" height="135" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <path id="stmpCfsTop" d="M 22,80 A 58,58 0 1,1 138,80" fill="none" />
                        <path id="stmpCfsBot" d="M 138,80 A 58,58 0 0,1 22,80" fill="none" />
                    </defs>
                    <circle cx="80" cy="80" r="75" fill="#f0fdf4" fill-opacity="0.5" stroke="#059669" stroke-width="2.6" />
                    <circle cx="80" cy="80" r="70" fill="none" stroke="#059669" stroke-width="1" stroke-dasharray="3 2" />
                    <circle cx="80" cy="80" r="48" fill="none" stroke="#059669" stroke-width="1.8" />
                    <circle cx="80" cy="80" r="45" fill="none" stroke="#059669" stroke-width="0.6" />

                    <text font-family="'Segoe UI', Roboto, Arial, sans-serif" font-size="7.8" font-weight="900" fill="#059669" letter-spacing="0.9">
                        <textPath href="#stmpCfsTop" xlink:href="#stmpCfsTop" startOffset="50%" text-anchor="middle">
                            ★ CFS WAREHOUSE &amp; LOGISTICS ★
                        </textPath>
                    </text>

                    <text font-family="'Segoe UI', Roboto, Arial, sans-serif" font-size="7.5" font-weight="800" fill="#059669" letter-spacing="0.8">
                        <textPath href="#stmpCfsBot" xlink:href="#stmpCfsBot" startOffset="50%" text-anchor="middle">
                            RAMPA D1-D5 &bull; CARGO VERIFIED
                        </textPath>
                    </text>

                    <text x="80" y="58" font-size="8" fill="#059669" text-anchor="middle">★★★★★</text>

                    <rect x="23" y="65" width="114" height="28" rx="4" fill="#059669" fill-opacity="0.12" stroke="#059669" stroke-width="1.4" />
                    <text x="80" y="80" font-family="'Segoe UI', Arial, sans-serif" font-size="12" font-weight="900" fill="#059669" text-anchor="middle" letter-spacing="2">
                        TALLY PASS
                    </text>
                    <text x="80" y="90" font-family="'Segoe UI', monospace" font-size="6.5" font-weight="800" fill="#059669" text-anchor="middle">
                        GS1 SSCC INSPECTED &bull; ${d}
                    </text>

                    <text x="80" y="105" font-size="6.5" font-family="'Courier New', monospace" font-weight="bold" fill="#059669" text-anchor="middle">
                        TALLY-NO: ${ref}
                    </text>
                </svg>
            </div>`;
        },

        /**
         * Stempel Digital Gate Automation & Surat Jalan - Warna Biru Royal
         */
        gate: function(refNo, dateStr) {
            const d = dateStr || new Date().toLocaleDateString('id-ID');
            const ref = refNo || 'GATE/PASS/2026';
            return `
            <div class="digital-stamp stamp-gate" style="display:inline-block; position:relative; transform:rotate(-4deg); filter:drop-shadow(0 2px 4px rgba(37,99,235,0.25)); user-select:none;">
                <svg viewBox="0 0 160 160" width="135" height="135" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <path id="stmpGateTop" d="M 22,80 A 58,58 0 1,1 138,80" fill="none" />
                        <path id="stmpGateBot" d="M 138,80 A 58,58 0 0,1 22,80" fill="none" />
                    </defs>
                    <circle cx="80" cy="80" r="75" fill="#eff6ff" fill-opacity="0.5" stroke="#2563eb" stroke-width="2.6" />
                    <circle cx="80" cy="80" r="70" fill="none" stroke="#2563eb" stroke-width="1" stroke-dasharray="3 2" />
                    <circle cx="80" cy="80" r="48" fill="none" stroke="#2563eb" stroke-width="1.8" />
                    <circle cx="80" cy="80" r="45" fill="none" stroke="#2563eb" stroke-width="0.6" />

                    <text font-family="'Segoe UI', Roboto, Arial, sans-serif" font-size="8" font-weight="900" fill="#2563eb" letter-spacing="0.9">
                        <textPath href="#stmpGateTop" xlink:href="#stmpGateTop" startOffset="50%" text-anchor="middle">
                            ★ TERMINAL GATE AUTOMATION ★
                        </textPath>
                    </text>

                    <text font-family="'Segoe UI', Roboto, Arial, sans-serif" font-size="7.5" font-weight="800" fill="#2563eb" letter-spacing="0.8">
                        <textPath href="#stmpGateBot" xlink:href="#stmpGateBot" startOffset="50%" text-anchor="middle">
                            SOLAS VGM &bull; SECURITY CLEARED
                        </textPath>
                    </text>

                    <text x="80" y="58" font-size="8" fill="#2563eb" text-anchor="middle">★★★★★</text>

                    <rect x="23" y="65" width="114" height="28" rx="4" fill="#2563eb" fill-opacity="0.12" stroke="#2563eb" stroke-width="1.4" />
                    <text x="80" y="80" font-family="'Segoe UI', Arial, sans-serif" font-size="12" font-weight="900" fill="#2563eb" text-anchor="middle" letter-spacing="2">
                        GATE PASSED
                    </text>
                    <text x="80" y="90" font-family="'Segoe UI', monospace" font-size="6.5" font-weight="800" fill="#2563eb" text-anchor="middle">
                        TIMBANGAN 80T &bull; ${d}
                    </text>

                    <text x="80" y="105" font-size="6.5" font-family="'Courier New', monospace" font-weight="bold" fill="#2563eb" text-anchor="middle">
                        PASS: ${ref}
                    </text>
                </svg>
            </div>`;
        },

        /**
         * Stempel Digital Reefer Cold Chain - Warna Biru Kehijauan / Cyan
         */
        reefer: function(refNo, dateStr) {
            const d = dateStr || new Date().toLocaleDateString('id-ID');
            const ref = refNo || 'RF/PTI/2026';
            return `
            <div class="digital-stamp stamp-reefer" style="display:inline-block; position:relative; transform:rotate(-4deg); filter:drop-shadow(0 2px 4px rgba(8,145,178,0.25)); user-select:none;">
                <svg viewBox="0 0 160 160" width="135" height="135" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <path id="stmpRfTop" d="M 22,80 A 58,58 0 1,1 138,80" fill="none" />
                        <path id="stmpRfBot" d="M 138,80 A 58,58 0 0,1 22,80" fill="none" />
                    </defs>
                    <circle cx="80" cy="80" r="75" fill="#ecfeff" fill-opacity="0.5" stroke="#0891b2" stroke-width="2.6" />
                    <circle cx="80" cy="80" r="70" fill="none" stroke="#0891b2" stroke-width="1" stroke-dasharray="3 2" />
                    <circle cx="80" cy="80" r="48" fill="none" stroke="#0891b2" stroke-width="1.8" />
                    <circle cx="80" cy="80" r="45" fill="none" stroke="#0891b2" stroke-width="0.6" />

                    <text font-family="'Segoe UI', Roboto, Arial, sans-serif" font-size="8" font-weight="900" fill="#0891b2" letter-spacing="0.9">
                        <textPath href="#stmpRfTop" xlink:href="#stmpRfTop" startOffset="50%" text-anchor="middle">
                            ★ REEFER COLD CHAIN HUB ★
                        </textPath>
                    </text>

                    <text font-family="'Segoe UI', Roboto, Arial, sans-serif" font-size="7.5" font-weight="800" fill="#0891b2" letter-spacing="0.8">
                        <textPath href="#stmpRfBot" xlink:href="#stmpRfBot" startOffset="50%" text-anchor="middle">
                            TEMP MONITORED &bull; PTI CERTIFIED
                        </textPath>
                    </text>

                    <text x="80" y="58" font-size="8" fill="#0891b2" text-anchor="middle">★★★★★</text>

                    <rect x="23" y="65" width="114" height="28" rx="4" fill="#0891b2" fill-opacity="0.12" stroke="#0891b2" stroke-width="1.4" />
                    <text x="80" y="80" font-family="'Segoe UI', Arial, sans-serif" font-size="11.5" font-weight="900" fill="#0891b2" text-anchor="middle" letter-spacing="2">
                        COLD CERTIFIED
                    </text>
                    <text x="80" y="90" font-family="'Segoe UI', monospace" font-size="6.5" font-weight="800" fill="#0891b2" text-anchor="middle">
                        IOT TELEMETRY &bull; ${d}
                    </text>

                    <text x="80" y="105" font-size="6.5" font-family="'Courier New', monospace" font-weight="bold" fill="#0891b2" text-anchor="middle">
                        CERT: ${ref}
                    </text>
                </svg>
            </div>`;
        }
    },

    /**
     * Generator QR Code Verifikasi Keabsahan Digital Dokumen (SVG Vector)
     */
    generateQrSvg: function(refNumber, labelText) {
        const ref = refNumber || 'DOC-VERIFIED';
        const lbl = labelText || 'Pindai untuk Verifikasi Sistem CIDP';
        return `
        <div style="display:flex; align-items:center; gap:10px; font-family:'Segoe UI', Arial, sans-serif;">
            <div style="width:68px; height:68px; background:#fff; border:1px solid #002f5e; padding:4px; border-radius:6px; display:flex; align-items:center; justify-content:center; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                <svg viewBox="0 0 45 45" width="60" height="60" fill="#002f5e">
                    <!-- Standard QR Code Vector Representation -->
                    <rect x="0" y="0" width="13" height="13" fill="#002f5e" />
                    <rect x="2" y="2" width="9" height="9" fill="#fff" />
                    <rect x="4" y="4" width="5" height="5" fill="#002f5e" />

                    <rect x="32" y="0" width="13" height="13" fill="#002f5e" />
                    <rect x="34" y="2" width="9" height="9" fill="#fff" />
                    <rect x="36" y="4" width="5" height="5" fill="#002f5e" />

                    <rect x="0" y="32" width="13" height="13" fill="#002f5e" />
                    <rect x="2" y="34" width="9" height="9" fill="#fff" />
                    <rect x="4" y="36" width="5" height="5" fill="#002f5e" />

                    <!-- Data dots -->
                    <rect x="16" y="2" width="3" height="3" />
                    <rect x="22" y="2" width="3" height="3" />
                    <rect x="26" y="5" width="3" height="3" />
                    <rect x="16" y="8" width="3" height="3" />
                    <rect x="22" y="10" width="3" height="3" />
                    
                    <rect x="2" y="16" width="3" height="3" />
                    <rect x="8" y="18" width="3" height="3" />
                    <rect x="16" y="16" width="3" height="3" />
                    <rect x="22" y="16" width="3" height="3" />
                    <rect x="28" y="16" width="3" height="3" />
                    <rect x="34" y="18" width="3" height="3" />
                    <rect x="40" y="16" width="3" height="3" />

                    <rect x="16" y="22" width="3" height="3" />
                    <rect x="20" y="24" width="5" height="5" />
                    <rect x="28" y="22" width="3" height="3" />
                    <rect x="34" y="24" width="3" height="3" />

                    <rect x="16" y="34" width="3" height="3" />
                    <rect x="22" y="32" width="3" height="3" />
                    <rect x="26" y="36" width="3" height="3" />
                    <rect x="34" y="34" width="3" height="3" />
                    <rect x="38" y="38" width="3" height="3" />
                </svg>
            </div>
            <div style="font-size:10px; line-height:1.35; color:#475569;">
                <div style="font-weight:bold; color:#002f5e; font-size:10.5px;">KEABSAHAN DIGITAL TERVERIFIKASI</div>
                <div>${lbl}</div>
                <div style="font-family:monospace; font-weight:bold; color:#0f172a; margin-top:2px;">SEC-HASH: SHA256-${ref.replace(/[^a-zA-Z0-9]/g,'').slice(0,10)}</div>
                <div style="font-size:9px; color:#64748b;">Portal Verifikasi: e-port.conclusion-dryport.co.id</div>
            </div>
        </div>`;
    },

    /**
     * Generator Kop Surat Resmi Perusahaan (Letterhead)
     */
    renderLetterhead: function(docTitle, docNumber, docDate, categoryBadge) {
        const badge = categoryBadge || 'DOKUMEN OPERASIONAL RESMI';
        return `
        <div class="official-letterhead" style="border-bottom: 2.5px solid #002f5e; padding-bottom: 12px; margin-bottom: 18px; font-family:'Segoe UI', Roboto, Arial, sans-serif;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <!-- Identitas Perusahaan -->
                <div style="flex:1;">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:6px;">
                        <div style="width:48px; height:48px; border-radius:10px; background:linear-gradient(135deg, #002f5e, #0170b9); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:22px; letter-spacing:1px; box-shadow:0 3px 8px rgba(0,47,94,0.25);">
                            C
                        </div>
                        <div>
                            <h1 style="margin:0; font-size:18px; font-weight:900; color:#002f5e; letter-spacing:-0.2px; text-transform:uppercase;">
                                ${this.company.name}
                            </h1>
                            <div style="font-size:10.5px; font-weight:700; color:#0170b9; letter-spacing:0.4px;">
                                ${this.company.subname}
                            </div>
                        </div>
                    </div>
                    <div style="font-size:9.5px; color:#475569; line-height:1.45; margin-top:4px;">
                        <div>${this.company.address}</div>
                        <div>${this.company.contact} &bull; ${this.company.web}</div>
                        <div style="font-size:8.5px; color:#64748b; font-style:italic;">${this.company.license}</div>
                    </div>
                </div>

                <!-- Nomor & Metadata Dokumen -->
                <div style="text-align:right; min-width:240px;">
                    <span style="display:inline-block; padding:3px 10px; border-radius:999px; background:#e0f2fe; color:#0369a1; font-weight:800; font-size:9px; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px; border:1px solid #bae6fd;">
                        ${badge}
                    </span>
                    <div style="font-size:16px; font-weight:900; color:#002f5e; text-transform:uppercase; margin-top:2px;">
                        ${docTitle}
                    </div>
                    <div style="font-family:'Courier New', monospace; font-size:12px; font-weight:800; color:#0f172a; margin-top:3px;">
                        No: ${docNumber || '-'}
                    </div>
                    <div style="font-size:10px; color:#64748b; margin-top:2px;">
                        Tanggal: <strong>${docDate || new Date().toLocaleDateString('id-ID')}</strong>
                    </div>
                </div>
            </div>
            <!-- Garis Ganda Pembatas Kop Surat -->
            <div style="height:1px; background:#0170b9; margin-top:10px;"></div>
        </div>`;
    },

    /**
     * Buka Window Cetak Terisolasi (Print Context) Tanpa Polusi Layar Web
     */
    openPrintWindow: function(title, innerContentHtml) {
        const printWindow = window.open('', '_blank', 'width=980,height=850');
        if (!printWindow) {
            alert('Pop-up terblokir oleh peramban. Harap izinkan pop-up untuk mencetak dokumen resmi.');
            return;
        }

        const fullHtml = `
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="utf-8">
            <title>${title} — CIDP Official Document</title>
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700;800&display=swap');
                
                @page {
                    size: A4 portrait;
                    margin: 12mm 14mm 12mm 14mm;
                }

                * {
                    box-sizing: border-box;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }

                body {
                    margin: 0;
                    padding: 0;
                    background: #ffffff;
                    color: #0f172a;
                    font-family: 'Plus Jakarta Sans', Arial, sans-serif;
                    font-size: 11.5px;
                    line-height: 1.45;
                }

                .print-container {
                    width: 100%;
                    max-width: 210mm;
                    margin: 0 auto;
                    background: #ffffff;
                }

                /* Table Styling */
                table.doc-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 12px 0;
                    font-size: 11px;
                }

                table.doc-table th {
                    background-color: #002f5e !important;
                    color: #ffffff !important;
                    font-weight: 800;
                    text-transform: uppercase;
                    padding: 7px 10px;
                    text-align: left;
                    font-size: 10px;
                    letter-spacing: 0.5px;
                    border: 1px solid #002f5e;
                }

                table.doc-table td {
                    padding: 7px 10px;
                    border: 1px solid #cbd5e1;
                    color: #1e293b;
                }

                table.doc-table tr:nth-child(even) td {
                    background-color: #f8fafc !important;
                }

                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .font-mono { font-family: 'JetBrains Mono', monospace; }
                .font-bold { font-weight: 700; }
                .font-black { font-weight: 900; }

                /* Document Footer Validation Block */
                .validation-section {
                    margin-top: 24px;
                    page-break-inside: avoid;
                    border-top: 1px solid #cbd5e1;
                    padding-top: 16px;
                }

                .sig-box {
                    position: relative;
                    min-height: 110px;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                }

                .sig-person-title {
                    font-size: 10.5px;
                    font-weight: 700;
                    color: #475569;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }

                .sig-person-name {
                    font-size: 11.5px;
                    font-weight: 800;
                    color: #0f172a;
                    border-top: 1px solid #475569;
                    padding-top: 4px;
                    display: inline-block;
                }

                .sig-person-nip {
                    font-size: 9px;
                    color: #64748b;
                }

                .sig-stamp-holder {
                    position: absolute;
                    top: -15px;
                    left: 20px;
                    z-index: 10;
                    pointer-events: none;
                }

                .sig-cursive {
                    font-family: 'Brush Script MT', cursive, sans-serif;
                    font-size: 26px;
                    color: #1e293b;
                    opacity: 0.85;
                    transform: rotate(-3deg);
                    margin-left: 20px;
                    margin-bottom: -10px;
                }

                /* Floating Action Bar (Hanya tampil di layar browser sebelum print) */
                .screen-action-bar {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background: rgba(15,23,42,0.92);
                    backdrop-filter: blur(8px);
                    padding: 10px 18px;
                    border-radius: 999px;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.25);
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    z-index: 9999;
                }

                .screen-action-bar button {
                    background: #2563eb;
                    color: #fff;
                    border: none;
                    padding: 8px 18px;
                    border-radius: 999px;
                    font-weight: 800;
                    font-size: 12px;
                    cursor: pointer;
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                }

                .screen-action-bar button:hover {
                    background: #1d4ed8;
                }

                .screen-action-bar .btn-close {
                    background: #475569;
                }

                .screen-action-bar .btn-close:hover {
                    background: #334155;
                }

                @media print {
                    .screen-action-bar {
                        display: none !important;
                    }
                }
            </style>
        </head>
        <body>
            <div class="screen-action-bar">
                <span style="color:#fff; font-size:12px; font-weight:700;">Dokumen Siap Cetak (A4 Resmi)</span>
                <button onclick="window.print()">
                    🖨️ Cetak Dokumen / Simpan PDF
                </button>
                <button class="btn-close" onclick="window.close()">Tutup</button>
            </div>

            <div class="print-container">
                ${innerContentHtml}
            </div>

            <script>
                // Jalankan print dialog secara otomatis setelah aset termuat
                window.addEventListener('load', function() {
                    setTimeout(function() {
                        window.print();
                    }, 400);
                });
            <\/script>
        </body>
        </html>`;

        printWindow.document.open();
        printWindow.document.write(fullHtml);
        printWindow.document.close();
    },

    /**
     * 1. CETAK INVOICE / FAKTUR TAGIHAN RESMI (BILLING)
     */
    printInvoice: function(inv) {
        const invNo = inv.no || 'INV/2026/09/0084';
        const invDate = inv.date || new Date().toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' });
        const dueDate = inv.dueDate || '14 Oktober 2026';
        const custName = inv.customer || 'PT Unilever Indonesia Tbk';
        const custAddress = inv.address || 'Kawasan Industri Jababeka V, Cikarang, Bekasi, Jawa Barat';
        const items = inv.items || [
            { desc: 'Jasa Penumpukan (Storage) Peti Kemas 20ft GP', qty: '5 Hari', rate: 100000, total: 500000 },
            { desc: 'Lift-On / Lift-Off (LOLO) Mekanis Terminal', qty: '2 Gerakan', rate: 250000, total: 500000 },
            { desc: 'Monitoring Suhu & Plug-In Daya Listrik Reefer', qty: '24 Jam', rate: 350000, total: 350000 }
        ];
        const subtotal = inv.subtotal || 1350000;
        const ppn = inv.ppn || (subtotal * 0.11);
        const grandTotal = inv.total || (subtotal + ppn);
        const isPaid = (inv.status || 'PAID').toUpperCase() === 'PAID' || (inv.status || '').toUpperCase() === 'LUNAS';

        let rowsHtml = '';
        items.forEach((it, idx) => {
            rowsHtml += `
            <tr>
                <td class="text-center" style="width:35px;">${idx + 1}</td>
                <td><strong>${it.desc}</strong></td>
                <td class="text-center" style="width:100px;">${it.qty}</td>
                <td class="text-right font-mono" style="width:140px;">Rp ${Number(it.rate).toLocaleString('id-ID')}</td>
                <td class="text-right font-mono font-bold" style="width:150px;">Rp ${Number(it.total).toLocaleString('id-ID')}</td>
            </tr>`;
        });

        const letterhead = this.renderLetterhead('FAKTUR TAGIHAN PELABUHAN KERING (INVOICE)', invNo, invDate, 'FINANCE & BILLING RESMI');
        const stamp = this.stamps.finance(invNo, invDate);
        const qr = this.generateQrSvg(invNo, 'Pindai untuk verifikasi status pelunasan invoice di Portal ERP Odoo');

        const content = `
        ${letterhead}

        <!-- Informasi Tagihan & Pelanggan -->
        <div style="display:flex; justify-content:space-between; margin-bottom:16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px;">
            <div style="flex:1;">
                <div style="font-size:9.5px; font-weight:800; color:#64748b; text-transform:uppercase;">Ditagihkan Kepada:</div>
                <div style="font-size:13px; font-weight:900; color:#0f172a; margin-top:2px;">${custName}</div>
                <div style="font-size:10px; color:#475569; margin-top:2px; max-width:320px;">${custAddress}</div>
                <div style="font-size:9.5px; color:#002f5e; font-weight:700; margin-top:4px;">NPWP: 01.000.015.8-054.000 &bull; ID Pelanggan: CUST-CIDP-0428</div>
            </div>
            <div style="text-align:right; min-width:200px;">
                <div style="font-size:9.5px; font-weight:800; color:#64748b; text-transform:uppercase;">Ketentuan Pembayaran:</div>
                <div style="font-size:11px; margin-top:2px;">Jatuh Tempo: <strong style="color:#b91c1c;">${dueDate}</strong></div>
                <div style="font-size:11px; margin-top:2px;">Metode: <strong>Bank Transfer / Virtual Account</strong></div>
                <div style="margin-top:6px;">
                    <span style="display:inline-block; padding:3px 12px; border-radius:999px; background:${isPaid ? '#ecfdf5' : '#fef2f2'}; color:${isPaid ? '#047857' : '#b91c1c'}; border:1.5px solid ${isPaid ? '#a7f3d0' : '#fecaca'}; font-weight:900; font-size:10px; text-transform:uppercase;">
                        ${isPaid ? 'STATUS: LUNAS (PAID)' : 'STATUS: MENUNGGU PEMBAYARAN'}
                    </span>
                </div>
            </div>
        </div>

        <!-- Tabel Rincian Jasa Terminal -->
        <table class="doc-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Deskripsi Jasa Kepelabuhanan</th>
                    <th class="text-center">Volume / Durasi</th>
                    <th class="text-right">Tarif Dasar</th>
                    <th class="text-right">Jumlah (IDR)</th>
                </tr>
            </thead>
            <tbody>
                ${rowsHtml}
            </tbody>
        </table>

        <!-- Ringkasan Total & Instruksi Bank -->
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-top:14px;">
            <div style="flex:1; padding-right:24px;">
                <div style="background:#f0f7ff; border:1px solid #bfdbfe; border-radius:8px; padding:10px 12px; font-size:10px; color:#1e3a8a;">
                    <strong style="color:#002f5e; display:block; margin-bottom:4px; font-size:10.5px;">Instruksi Pembayaran Resmi:</strong>
                    Pembayaran disetorkan ke rekening resmi <strong>PT Conclusion Intermodal Dry Port</strong>:<br>
                    &bull; Bank Mandiri Cabang Kawasan Industri Cikarang: <strong>156-00-1234567-8</strong><br>
                    &bull; Bank BNI Corporate Cabang Jababeka: <strong>089-22-3344556-1</strong><br>
                    Cantumkan nomor invoice <strong>${invNo}</strong> pada berita transfer. Bukti potong PPh 23 diunggah via portal billing.
                </div>
            </div>

            <div style="width:280px;">
                <div style="display:flex; justify-content:space-between; padding:4px 0; font-size:11px;">
                    <span style="color:#64748b;">Subtotal Jasa:</span>
                    <span class="font-mono font-bold">Rp ${Number(subtotal).toLocaleString('id-ID')}</span>
                </div>
                <div style="display:flex; justify-content:space-between; padding:4px 0; font-size:11px; border-bottom:1px solid #e2e8f0;">
                    <span style="color:#64748b;">PPN Jasa Kepelabuhanan (11%):</span>
                    <span class="font-mono font-bold">Rp ${Number(ppn).toLocaleString('id-ID')}</span>
                </div>
                <div style="display:flex; justify-content:space-between; padding:8px 0; font-size:14px; font-weight:900; color:#002f5e;">
                    <span>Grand Total:</span>
                    <span class="font-mono font-black" style="color:#0170b9;">Rp ${Number(grandTotal).toLocaleString('id-ID')}</span>
                </div>
            </div>
        </div>

        <!-- Bagian Pengesahan & Stempel Digital -->
        <div class="validation-section" style="display:flex; justify-content:space-between; align-items:flex-end;">
            <div>
                ${qr}
            </div>

            <div class="sig-box" style="width:230px; text-align:center;">
                <div class="sig-person-title">Manager Keuangan &amp; Billing</div>
                
                <div style="position:relative; height:85px; display:flex; align-items:center; justify-content:center;">
                    <!-- Stempel Digital LUNAS -->
                    <div class="sig-stamp-holder">
                        ${stamp}
                    </div>
                    <!-- Tanda Tangan Cursive Pejabat -->
                    <div class="sig-cursive" style="position:relative; z-index:2;">
                        Budi Santoso
                    </div>
                </div>

                <div>
                    <div class="sig-person-name">BUDI SANTOSO, S.E., Ak., M.M.</div>
                    <div class="sig-person-nip">NPP. 201408 198205 101 &bull; Certified Port Accountant</div>
                </div>
            </div>
        </div>`;

        this.openPrintWindow(`Invoice ${invNo}`, content);
    },

    /**
     * 2. CETAK BERITA ACARA TALLY SHEET CFS (BONGKAR / MUAT)
     */
    printTallySheet: function(tally) {
        const jobNum = tally.jobNum || 'JO-STP-2026-001';
        const ctrNum = tally.ctrNum || 'MSKU8821940';
        const jobType = (tally.jobType || 'STRIPPING').toUpperCase();
        const consignee = tally.consignee || 'PT Astra Honda Motor';
        const dock = tally.dock || 'D1';
        const pkgs = tally.pkgs || 20;
        const weight = tally.weight || 10000;
        const cbm = tally.cbm || 30.0;
        const notes = tally.notes || 'Segel pabean terverifikasi utuh, tidak ditemukan kerusakan kargo. Standar GS1 SSCC label terpasang pada seluruh palet.';
        const tallyDate = tally.date || new Date().toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' });

        const letterhead = this.renderLetterhead('BERITA ACARA TALLY SHEET CFS 4.000 M²', jobNum, tallyDate, 'OPERASIONAL WMS & PABEAN');
        const stamp = this.stamps.cfs(jobNum, tallyDate);
        const qr = this.generateQrSvg(jobNum, 'Verifikasi Hasil Tally Sheet Digital & Penempatan Palet Rak');

        const content = `
        ${letterhead}

        <!-- Ringkasan Operasi Rampa -->
        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 16px; margin-bottom:14px;">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e2e8f0; pb-2; margin-bottom:10px;">
                <div>
                    <span style="font-size:10px; font-weight:800; color:#64748b; text-transform:uppercase;">Aktivitas Rampa Dock Leveler:</span>
                    <h3 style="margin:2px 0 0 0; font-size:15px; font-weight:900; color:#002f5e;">
                        RAMPA DOCK ${dock} &bull; ${jobType === 'STRIPPING' ? 'BONGKAR PETI KEMAS (STRIPPING LCL)' : 'MUAT PETI KEMAS (STUFFING LCL)'}
                    </h3>
                </div>
                <div style="text-align:right;">
                    <span style="display:inline-block; padding:4px 12px; border-radius:6px; background:#002f5e; color:#fff; font-family:monospace; font-weight:900; font-size:12px;">
                        KONT: ${ctrNum}
                    </span>
                </div>
            </div>

            <table style="width:100%; font-size:11px; border-collapse:collapse;">
                <tr>
                    <td style="width:25%; padding:4px 0; color:#64748b;">Shipper / Consignee:</td>
                    <td style="width:35%; padding:4px 0; font-weight:800; color:#0f172a;">${consignee}</td>
                    <td style="width:20%; padding:4px 0; color:#64748b;">Rampa Hidrolik:</td>
                    <td style="width:20%; padding:4px 0; font-weight:800; color:#0f172a;">Rampa ${dock} (15 Ton Cap)</td>
                </tr>
                <tr>
                    <td style="padding:4px 0; color:#64748b;">Total Palet / Koli:</td>
                    <td style="padding:4px 0; font-weight:800; color:#059669; font-size:12px;">${pkgs} Palet Standar (GS1)</td>
                    <td style="padding:4px 0; color:#64748b;">Berat Kotor (Gross):</td>
                    <td style="padding:4px 0; font-weight:800; font-family:monospace; color:#0f172a;">${Number(weight).toLocaleString('id-ID')} kg</td>
                </tr>
                <tr>
                    <td style="padding:4px 0; color:#64748b;">Estimasi Kubikasi:</td>
                    <td style="padding:4px 0; font-weight:800; font-family:monospace; color:#0f172a;">${Number(cbm).toFixed(2)} CBM</td>
                    <td style="padding:4px 0; color:#64748b;">Status Segel:</td>
                    <td style="padding:4px 0; font-weight:800; color:#059669;">INTEGRITAS UTUH (OK)</td>
                </tr>
            </table>
        </div>

        <!-- Tabel Penempatan Slot Rak Gudang -->
        <div style="margin:12px 0;">
            <div style="font-size:11px; font-weight:800; color:#002f5e; text-transform:uppercase; margin-bottom:6px;">
                Rincian Manifest Kargo &amp; Alokasi Rak Gudang 6 Blok:
            </div>
            <table class="doc-table">
                <thead>
                    <tr>
                        <th style="width:35px;" class="text-center">No</th>
                        <th>Kode Serial GS1 SSCC-18</th>
                        <th>Uraian Komoditas &amp; Kemasan</th>
                        <th class="text-center">Alokasi Blok &amp; Sektor</th>
                        <th class="text-center">Level Rak</th>
                        <th class="text-right">Berat (kg)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">1</td>
                        <td class="font-mono font-bold" style="color:#002f5e;">(00) 001899120098210411</td>
                        <td>Komponen Otomotif &amp; Sparepart Presisi</td>
                        <td class="text-center font-bold">Blok 1 &bull; Sektor A</td>
                        <td class="text-center font-mono font-bold" style="color:#2563eb;">R1 (Lantai Dasar)</td>
                        <td class="text-right font-mono">500.0</td>
                    </tr>
                    <tr>
                        <td class="text-center">2</td>
                        <td class="font-mono font-bold" style="color:#002f5e;">(00) 001899120098210412</td>
                        <td>Komponen Otomotif &amp; Sparepart Presisi</td>
                        <td class="text-center font-bold">Blok 1 &bull; Sektor A</td>
                        <td class="text-center font-mono font-bold" style="color:#2563eb;">R2 (Tingkat 2)</td>
                        <td class="text-right font-mono">500.0</td>
                    </tr>
                    <tr>
                        <td class="text-center">3</td>
                        <td class="font-mono font-bold" style="color:#002f5e;">(00) 001899120098210413</td>
                        <td>Komponen Transmisi Mesin Motor</td>
                        <td class="text-center font-bold">Blok 2 &bull; Sektor B</td>
                        <td class="text-center font-mono font-bold" style="color:#2563eb;">R1 (Lantai Dasar)</td>
                        <td class="text-right font-mono">550.0</td>
                    </tr>
                    <tr>
                        <td class="text-center">4</td>
                        <td class="font-mono font-bold" style="color:#002f5e;">(00) 001899120098210414</td>
                        <td>Komponen Kelistrikan &amp; Sensor Wiring</td>
                        <td class="text-center font-bold">Blok 3 &bull; Sektor C</td>
                        <td class="text-center font-mono font-bold" style="color:#2563eb;">R3 (Tingkat 3)</td>
                        <td class="text-right font-mono">450.0</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="font-size:10px; color:#475569; background:#fff7ed; border:1px solid #ffedd5; padding:8px 12px; border-radius:6px; margin-bottom:12px;">
            <strong>Catatan Petugas Tally &amp; Pengawas K3:</strong> ${notes}
        </div>

        <!-- 3 Pihak Penandatangan Berita Acara -->
        <div class="validation-section" style="display:flex; justify-content:space-between; align-items:flex-end;">
            <div>
                ${qr}
            </div>

            <!-- Tally Master CFS -->
            <div class="sig-box" style="width:190px; text-align:center;">
                <div class="sig-person-title">Head of CFS &amp; Tally Master</div>
                <div style="position:relative; height:80px; display:flex; align-items:center; justify-content:center;">
                    <div class="sig-stamp-holder" style="left:0px;">
                        ${stamp}
                    </div>
                    <div class="sig-cursive" style="position:relative; z-index:2;">
                        JUAN GAMALIEL
                    </div>
                </div>
                <div>
                    <div class="sig-person-name">JUAN GAMALIEL</div>
                    <div class="sig-person-nip">Yard Planning, CFS &amp; Multimodal Specialist</div>
                </div>
            </div>

            <!-- Petugas Transporter / Pengemudi -->
            <div class="sig-box" style="width:170px; text-align:center;">
                <div class="sig-person-title">Pengemudi / Transporter</div>
                <div style="position:relative; height:80px; display:flex; align-items:center; justify-content:center;">
                    <div class="sig-cursive" style="margin-left:0;">
                        Suhendar
                    </div>
                </div>
                <div>
                    <div class="sig-person-name">SUHENDAR</div>
                    <div class="sig-person-nip">SIM B2 Umum &bull; PT Samudera Kirana</div>
                </div>
            </div>

            <!-- Pengawas Hanggar Bea Cukai -->
            <div class="sig-box" style="width:190px; text-align:center;">
                <div class="sig-person-title">Pengawas Hanggar Bea Cukai</div>
                <div style="position:relative; height:80px; display:flex; align-items:center; justify-content:center;">
                    <div style="font-size:10px; color:#0369a1; font-weight:800; border:1px dashed #0284c7; padding:4px 8px; border-radius:6px;">
                        ✓ CEISA 4.0 VERIFIED
                    </div>
                </div>
                <div>
                    <div class="sig-person-name">HANGGAR BEA CUKAI</div>
                    <div class="sig-person-nip">KPPBC TMP Cikarang</div>
                </div>
            </div>
        </div>`;

        this.openPrintWindow(`Tally Sheet ${jobNum}`, content);
    },

    /**
     * 3. CETAK SURAT JALAN & ELECTRONIC GATE PASS (GATE & TRUCKING)
     */
    printGatePass: function(gp) {
        const passNo = gp.passNo || 'GP-IN-2026-0941';
        const truckPlate = gp.plate || 'B 9201 UEK';
        const driverName = gp.driver || 'Agus Suprianto';
        const simNo = gp.sim || 'SIM B2 Umum: 8901239102';
        const transporter = gp.transporter || 'PT Samudera Logistik Nusantara';
        const ctrNum = gp.ctrNum || 'TCLU8192031';
        const ctrSize = gp.ctrSize || "40' High Cube (40HC)";
        const grossWeight = gp.gross || 38450;
        const tareWeight = gp.tare || 12850;
        const netWeight = gp.net || 25600;
        const yardLocation = gp.location || 'BLOK B &bull; ROW 04 &bull; TIER 2';
        const passDate = gp.date || new Date().toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' });

        const letterhead = this.renderLetterhead('SURAT JALAN / ELECTRONIC GATE PASS (E-PASS)', passNo, passDate, 'AUTOMASI GERBANG & TMS');
        const stamp = this.stamps.gate(passNo, passDate);
        const qr = this.generateQrSvg(passNo, 'Pindai barcode pada barrier gate untuk membuka gerbang otomatis');

        const content = `
        ${letterhead}

        <!-- Kotak Armada & Identitas Sopir -->
        <div style="display:flex; justify-content:space-between; gap:14px; margin-bottom:14px;">
            <div style="flex:1; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px;">
                <div style="font-size:9.5px; font-weight:800; color:#64748b; text-transform:uppercase;">Data Armada Truk &amp; Pengemudi:</div>
                <div style="font-size:18px; font-weight:900; font-family:monospace; color:#002f5e; margin-top:2px;">${truckPlate}</div>
                <div style="font-size:11px; font-weight:700; color:#0f172a; margin-top:3px;">Pengemudi: ${driverName}</div>
                <div style="font-size:10px; color:#475569;">${simNo}</div>
                <div style="font-size:10px; color:#002f5e; font-weight:700; margin-top:2px;">Transporter: ${transporter}</div>
            </div>

            <div style="flex:1; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px;">
                <div style="font-size:9.5px; font-weight:800; color:#64748b; text-transform:uppercase;">Peti Kemas &amp; Penempatan Lapangan:</div>
                <div style="font-size:18px; font-weight:900; font-family:monospace; color:#059669; margin-top:2px;">${ctrNum}</div>
                <div style="font-size:11px; font-weight:700; color:#0f172a; margin-top:3px;">Spesifikasi: ${ctrSize}</div>
                <div style="font-size:11px; color:#002f5e; font-weight:800; margin-top:4px;">
                    Alokasi Slot Yard: <span style="background:#e0f2fe; padding:2px 6px; border-radius:4px;">${yardLocation}</span>
                </div>
            </div>
        </div>

        <!-- Bukti Penimbangan Jembatan Timbang SOLAS VGM -->
        <div style="border:1.5px solid #002f5e; border-radius:10px; padding:12px 16px; margin-bottom:16px; background:#fcfdfe;">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #cbd5e1; padding-bottom:6px; margin-bottom:10px;">
                <div style="font-size:11.5px; font-weight:900; color:#002f5e; text-transform:uppercase;">
                    ⚖️ SLIP SERTIFIKASI TIMBANGAN SOLAS VGM (VERIFIED GROSS MASS)
                </div>
                <span style="font-size:9.5px; font-weight:800; color:#059669; background:#dcfce7; padding:2px 8px; border-radius:999px;">
                    ✓ KALIBRASI DISMETROLOGI RESMI
                </span>
            </div>

            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:12px; text-align:center;">
                <div style="background:#f1f5f9; padding:8px; border-radius:8px;">
                    <span style="font-size:9.5px; color:#64748b; text-transform:uppercase; font-weight:700; display:block;">Berat Kotor (Gross):</span>
                    <span style="font-size:16px; font-weight:900; font-family:monospace; color:#0f172a;">${Number(grossWeight).toLocaleString('id-ID')} kg</span>
                </div>
                <div style="background:#f1f5f9; padding:8px; border-radius:8px;">
                    <span style="font-size:9.5px; color:#64748b; text-transform:uppercase; font-weight:700; display:block;">Berat Kosong Truk (Tare):</span>
                    <span style="font-size:16px; font-weight:900; font-family:monospace; color:#475569;">${Number(tareWeight).toLocaleString('id-ID')} kg</span>
                </div>
                <div style="background:#ecfdf5; border:1px solid #a7f3d0; padding:8px; border-radius:8px;">
                    <span style="font-size:9.5px; color:#047857; text-transform:uppercase; font-weight:700; display:block;">Berat Muatan Bersih (Net):</span>
                    <span style="font-size:16px; font-weight:900; font-family:monospace; color:#047857;">${Number(netWeight).toLocaleString('id-ID')} kg</span>
                </div>
            </div>
            
            <div style="font-size:9.5px; color:#64748b; margin-top:8px; text-align:right;">
                Timbangan: Weighbridge Mettler Toledo 80T &bull; Akurasi OIML Class III &bull; Toleransi Max &plusmn;20 kg
            </div>
        </div>

        <div style="font-size:10px; color:#475569; background:#f1f5f9; border-radius:6px; padding:8px 12px; margin-bottom:14px; line-height:1.4;">
            <strong>Ketentuan Masuk/Keluar Wilayah Terminal 35 Ha:</strong><br>
            1. Kecepatan maksimal kendaraan di area sirkulasi: <strong>20 km/jam</strong>. Gunakan APD standar K3 (Rompi Reflektif, Safety Shoes, Helm).<br>
            2. Parkir hanya pada slot bay yang telah ditentukan sistem. Tidak diperkenankan melepaskan gandengan tanpa izin Tallyman Yard.<br>
            3. Surat jalan ini berlaku selama 24 jam sejak waktu registrasi pra-gate diterbitkan.
        </div>

        <!-- Validasi Dokumen & Stempel Digital Gate -->
        <div class="validation-section" style="display:flex; justify-content:space-between; align-items:flex-end;">
            <div>
                ${qr}
            </div>

            <div class="sig-box" style="width:220px; text-align:center;">
                <div class="sig-person-title">Petugas Pengawas Gerbang (Gate Officer)</div>
                <div style="position:relative; height:80px; display:flex; align-items:center; justify-content:center;">
                    <div class="sig-stamp-holder">
                        ${stamp}
                    </div>
                    <div class="sig-cursive" style="position:relative; z-index:2;">
                        M. Yusuf
                    </div>
                </div>
                <div>
                    <div class="sig-person-name">M. YUSUF RIDWAN</div>
                    <div class="sig-person-nip">ID Gate: GT-01-OP &bull; Gate Master CIDP</div>
                </div>
            </div>
        </div>`;

        this.openPrintWindow(`Gate Pass ${passNo}`, content);
    },

    /**
     * 4. CETAK LAPORAN REKAPITULASI RESMI (UNTUK TOMBOL CETAK REKAP DI SEMUA TABEL)
     */
    printTableReport: function(title, headers, rows, metaInfo) {
        const docDate = new Date().toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' });
        const docNo = 'REP/' + (metaInfo?.category || 'YMS') + '/' + dateStrCompact() + '/' + Math.floor(100 + Math.random() * 900);
        
        let headerCells = '';
        headers.forEach(h => {
            headerCells += `<th>${h}</th>`;
        });

        let bodyRows = '';
        rows.forEach((r, idx) => {
            bodyRows += '<tr>';
            bodyRows += `<td class="text-center" style="width:35px;">${idx + 1}</td>`;
            r.forEach(cell => {
                bodyRows += `<td>${cell}</td>`;
            });
            bodyRows += '</tr>';
        });

        const letterhead = this.renderLetterhead(title, docNo, docDate, 'LAPORAN EKSEKUTIF RESMI');
        const stamp = this.stamps.ops(docNo, docDate);
        const qr = this.generateQrSvg(docNo, 'Pindai untuk memvalidasi rekaman laporan di basis data YMS');

        const metaBoxes = metaInfo?.stats ? `
        <div style="display:grid; grid-template-columns: repeat(${metaInfo.stats.length}, 1fr); gap:10px; margin-bottom:14px;">
            ${metaInfo.stats.map(s => `
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:8px 12px; text-align:center;">
                    <div style="font-size:9px; font-weight:800; color:#64748b; text-transform:uppercase;">${s.label}</div>
                    <div style="font-size:15px; font-weight:900; color:#002f5e; font-family:monospace; margin-top:2px;">${s.value}</div>
                </div>
            `).join('')}
        </div>` : '';

        const content = `
        ${letterhead}
        ${metaBoxes}

        <table class="doc-table">
            <thead>
                <tr>
                    <th style="width:35px;" class="text-center">No</th>
                    ${headerCells}
                </tr>
            </thead>
            <tbody>
                ${bodyRows}
            </tbody>
        </table>

        <!-- Ringkasan Footer Dokumen -->
        <div class="validation-section" style="display:flex; justify-content:space-between; align-items:flex-end;">
            <div>
                ${qr}
            </div>

            <div class="sig-box" style="width:230px; text-align:center;">
                <div class="sig-person-title">Kepala Divisi Operasional (Head of Yard)</div>
                <div style="position:relative; height:80px; display:flex; align-items:center; justify-content:center;">
                    <div class="sig-stamp-holder">
                        ${stamp}
                    </div>
                    <div class="sig-cursive" style="position:relative; z-index:2;">
                        Armansyah M.
                    </div>
                </div>
                <div>
                    <div class="sig-person-name">ARMANSYAH MAULANA, S.T.</div>
                    <div class="sig-person-nip">Head of Operations &bull; Certified Logistics Master</div>
                </div>
            </div>
        </div>`;

        this.openPrintWindow(title, content);
    },

    /**
     * 5. CETAK BERITA ACARA MONITORING SUHU REEFER & PTI (REEFER COLD CHAIN)
     */
    printReeferCertificate: function(data) {
        const certNo = data.haccp_cert || ('RF-CERT-2026-' + Math.floor(1000 + Math.random() * 9000));
        const ctrId = data.id || 'EITU9823102';
        const line = data.line || 'ONE Line';
        const commodity = data.commodity || 'Frozen Beef Carcass';
        const destination = data.destination || 'Tokyo (JP TYO)';
        const setpoint = typeof data.setpoint !== 'undefined' ? Number(data.setpoint).toFixed(1) : '-20.0';
        const actualTemp = typeof data.temp_actual !== 'undefined' ? Number(data.temp_actual).toFixed(1) : '-19.8';
        const supplyTemp = typeof data.temp_supply !== 'undefined' ? Number(data.temp_supply).toFixed(1) : '-21.2';
        const returnTemp = typeof data.temp_return !== 'undefined' ? Number(data.temp_return).toFixed(1) : '-19.4';
        const humidity = data.humidity || '85';
        const slot = data.slot || 'R01-S01';
        const rack = data.rack || '01';
        const voltage = data.voltage || '400';
        const amperage = data.amperage || '14.8';
        const powerKw = typeof data.power_kw !== 'undefined' ? Number(data.power_kw).toFixed(2) : '5.92';
        const kwhTotal = data.kwh_total || '142.08';
        const duration = data.duration || '24 Jam';
        const plugTime = data.plug_time || 'Hari ini, 06:00 WIB';
        const docDate = new Date().toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' });

        const letterhead = this.renderLetterhead('BERITA ACARA MONITORING SUHU REEFER & KELAIKAN COLD CHAIN (PTI)', certNo, docDate, 'HACCP & FDA 21 CFR COMPLIANT');
        const stamp = this.stamps.reefer(certNo, docDate);
        const qr = this.generateQrSvg(certNo, 'Verifikasi Integritas Rantai Dingin & Audit Telemetri BPOM/FDA');

        // Generate 12 historical 2-hour telemetry log samples
        let logRows = '';
        const baseSet = parseFloat(setpoint);
        const nowHour = 18;
        for (let i = 11; i >= 0; i--) {
            const h = (nowHour - (i * 2) + 24) % 24;
            const timeLabel = String(h).padStart(2, '0') + ':00 WIB';
            const noise = (Math.sin(i * 0.7) * 0.35);
            const rowAct = (baseSet + noise).toFixed(1);
            const rowSup = (baseSet + noise - 1.1).toFixed(1);
            const rowRet = (baseSet + noise + 0.7).toFixed(1);
            const defrost = (h === 4) ? 'DEFROST (18m)' : 'NORMAL COOLING';
            const statusBadge = (Math.abs(rowAct - baseSet) <= 1.0) ? 
                '<span style="color:#059669; font-weight:800;">✓ IN RANGE</span>' : 
                '<span style="color:#d97706; font-weight:800;">⚠ TOLERANCE</span>';

            logRows += `
            <tr>
                <td class="text-center font-mono">${timeLabel}</td>
                <td class="text-center font-mono font-bold" style="color:#0284c7;">${rowAct}°C</td>
                <td class="text-center font-mono" style="color:#059669;">${rowSup}°C</td>
                <td class="text-center font-mono" style="color:#d97706;">${rowRet}°C</td>
                <td class="text-center font-mono">${baseSet > 0 ? '+' : ''}${baseSet}°C</td>
                <td class="text-center font-bold" style="font-size:9.5px;">${defrost}</td>
                <td class="text-center font-mono">${voltage}V / ${amperage}A</td>
                <td class="text-center">${statusBadge}</td>
            </tr>`;
        }

        const content = `
        ${letterhead}

        <!-- Kotak Ringkasan Kontainer & Komoditas -->
        <div style="background:#f0fdf4; border:1.5px solid #86efac; border-radius:10px; padding:12px 16px; margin-bottom:14px;">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #bbf7d0; padding-bottom:8px; margin-bottom:10px;">
                <div>
                    <span style="font-size:10px; font-weight:800; color:#15803d; text-transform:uppercase;">Identitas Kontainer Reefer:</span>
                    <h3 style="margin:2px 0 0 0; font-size:16px; font-weight:900; color:#002f5e; font-family:monospace;">
                        ${ctrId} <span style="font-size:12px; font-weight:700; color:#475569;">(${line})</span>
                    </h3>
                </div>
                <div style="text-align:right;">
                    <span style="display:inline-block; padding:4px 10px; border-radius:6px; background:#0891b2; color:#fff; font-weight:900; font-size:11px;">
                        SLOT: ${slot} (RACK ${rack})
                    </span>
                </div>
            </div>

            <table style="width:100%; font-size:11px; border-collapse:collapse;">
                <tr>
                    <td style="width:22%; padding:4px 0; color:#475569;">Komoditas Kargo:</td>
                    <td style="width:28%; padding:4px 0; font-weight:800; color:#0f172a;">${commodity}</td>
                    <td style="width:22%; padding:4px 0; color:#475569;">Target Suhu (Setpoint):</td>
                    <td style="width:28%; padding:4px 0; font-weight:900; color:#dc2626; font-size:13px; font-family:monospace;">${Number(setpoint) > 0 ? '+' : ''}${setpoint}°C (&plusmn;1.0°C)</td>
                </tr>
                <tr>
                    <td style="padding:4px 0; color:#475569;">Pelabuhan Tujuan:</td>
                    <td style="padding:4px 0; font-weight:800; color:#0f172a;">${destination}</td>
                    <td style="padding:4px 0; color:#475569;">Suhu Aktual Sensor:</td>
                    <td style="padding:4px 0; font-weight:900; color:#0284c7; font-size:13px; font-family:monospace;">${Number(actualTemp) > 0 ? '+' : ''}${actualTemp}°C</td>
                </tr>
                <tr>
                    <td style="padding:4px 0; color:#475569;">Waktu Plug-In / Durasi:</td>
                    <td style="padding:4px 0; font-weight:700; color:#0f172a;">${plugTime} (${duration})</td>
                    <td style="padding:4px 0; color:#475569;">Supply / Return Temp:</td>
                    <td style="padding:4px 0; font-weight:800; font-family:monospace; color:#0f172a;">${supplyTemp}°C / ${returnTemp}°C</td>
                </tr>
                <tr>
                    <td style="padding:4px 0; color:#475569;">Konsumsi Energi:</td>
                    <td style="padding:4px 0; font-weight:700; color:#0f172a;">${powerKw} kW &bull; Total ${kwhTotal} kWh</td>
                    <td style="padding:4px 0; color:#475569;">Kelembaban Relatif (RH):</td>
                    <td style="padding:4px 0; font-weight:800; color:#0f172a;">${humidity}% RH</td>
                </tr>
            </table>
        </div>

        <!-- Tabel Log Telemetri 24 Jam -->
        <div style="margin:14px 0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                <span style="font-size:11px; font-weight:800; color:#002f5e; text-transform:uppercase;">
                    Log Perekaman Telemetri Suhu Otomatis (Sensor LoRaWAN Gateway Gateway-RF01):
                </span>
                <span style="font-size:9.5px; color:#64748b; font-style:italic;">Interval Sampling: Terkalibrasi ISO 17025</span>
            </div>
            <table class="doc-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width:90px;">Waktu (WIB)</th>
                        <th class="text-center" style="width:90px;">Suhu Aktual</th>
                        <th class="text-center" style="width:90px;">Supply Air</th>
                        <th class="text-center" style="width:90px;">Return Air</th>
                        <th class="text-center" style="width:80px;">Setpoint</th>
                        <th class="text-center">Mode Kompresor</th>
                        <th class="text-center" style="width:110px;">Daya Listrik (3P)</th>
                        <th class="text-center" style="width:95px;">Integritas</th>
                    </tr>
                </thead>
                <tbody>
                    ${logRows}
                </tbody>
            </table>
        </div>

        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:8px 12px; margin-bottom:14px; font-size:10px; color:#475569; line-height:1.45;">
            <strong>Pernyataan Kelaikan Rantai Dingin (Cold Chain Warranty):</strong><br>
            Berdasarkan rekaman sensor telemetri digital dan uji Pre-Trip Inspection (PTI), peti kemas dinyatakan <strong>LAIK OPERASI &amp; MEMENUHI STANDAR INTERNASIONAL</strong> untuk transportasi komoditas sensitif suhu, sesuai regulasi BPOM RI dan FDA 21 CFR Part 11.
        </div>

        <!-- Pengesahan & 3 Pihak Penandatangan Berita Acara -->
        <div class="validation-section" style="display:flex; justify-content:space-between; align-items:flex-end;">
            <div>
                ${qr}
            </div>

            <!-- Petugas Teknisi Reefer Yard -->
            <div class="sig-box" style="width:190px; text-align:center;">
                <div class="sig-person-title">Reefer PTI Specialist</div>
                <div style="position:relative; height:80px; display:flex; align-items:center; justify-content:center;">
                    <div class="sig-stamp-holder" style="left:0px;">
                        ${stamp}
                    </div>
                    <div class="sig-cursive" style="position:relative; z-index:2;">
                        Wahyu H.
                    </div>
                </div>
                <div>
                    <div class="sig-person-name">WAHYU HIDAYAT, S.T.</div>
                    <div class="sig-person-nip">Certified Carrier &amp; Daikin Master</div>
                </div>
            </div>

            <!-- Kepala Operasional Terminal -->
            <div class="sig-box" style="width:190px; text-align:center;">
                <div class="sig-person-title">Head of Yard Operations</div>
                <div style="position:relative; height:80px; display:flex; align-items:center; justify-content:center;">
                    <div class="sig-cursive" style="margin-left:0;">
                        Armansyah M.
                    </div>
                </div>
                <div>
                    <div class="sig-person-name">ARMANSYAH MAULANA, S.T.</div>
                    <div class="sig-person-nip">Head of Ops &bull; CIDP Dry Port</div>
                </div>
            </div>

            <!-- Pengawas Karantina & Mutu Pangan -->
            <div class="sig-box" style="width:190px; text-align:center;">
                <div class="sig-person-title">Balai Karantina Hewan &amp; Tumbuhan</div>
                <div style="position:relative; height:80px; display:flex; align-items:center; justify-content:center;">
                    <div style="font-size:10px; color:#059669; font-weight:800; border:1px dashed #059669; padding:4px 8px; border-radius:6px;">
                        ✓ KARANTINA PASSED
                    </div>
                </div>
                <div>
                    <div class="sig-person-name">drh. KURNIAWAN, M.Si.</div>
                    <div class="sig-person-nip">Dokter Hewan Karantina Ahli</div>
                </div>
            </div>
        </div>`;

        this.openPrintWindow(`Berita Acara Suhu ${ctrId}`, content);
    },

    /**
     * 6. CETAK LABEL LOGISTIK STANDAR GS1-128 PALLET (CFS WAREHOUSE)
     */
    printGS1Label: function(lbl) {
        const sscc = lbl.sscc || '(00) 00189912001092837101';
        const consignee = lbl.consignee || 'PT Astra Honda Motor';
        const address = lbl.address || 'Kawasan Industri MM2100, Cikarang Barat, Bekasi';
        const commodity = lbl.commodity || 'Komponen Transmisi Matic';
        const rack = lbl.rack || 'AISLE-E-02-L1';
        const cartons = lbl.cartons || '24 Cartons';
        const weight = lbl.weight || '480.0 kg';
        const docDate = new Date().toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' });

        const stamp = this.stamps.cfs('SSCC-' + sscc.replace(/[^0-9]/g,'').slice(-6), docDate);
        const qr = this.generateQrSvg(sscc, 'Pindai GS1 Digital Link untuk verifikasi pallet inventory WMS');

        const content = `
        <div style="max-width:148mm; margin:0 auto; padding:10px; font-family:'Plus Jakarta Sans', Arial, sans-serif;">
            <div style="border:3px solid #000; border-radius:12px; padding:16px; background:#fff;">
                <!-- Header Label -->
                <div style="display:flex; justify-content:space-between; align-items:flex-start; border-bottom:2px solid #000; padding-bottom:10px; margin-bottom:10px;">
                    <div>
                        <div style="font-size:16px; font-weight:900; letter-spacing:-0.2px;">CONCLUSION INTERMODAL DRY PORT</div>
                        <div style="font-size:10px; font-weight:700; color:#334155; text-transform:uppercase;">CFS WAREHOUSE &bull; GS1 STANDARD LOGISTIC PALLET LABEL</div>
                    </div>
                    <div style="text-align:right;">
                        <span style="display:inline-block; border:1.5px solid #000; padding:2px 8px; font-size:11px; font-weight:900; border-radius:4px;">
                            SSCC-18
                        </span>
                    </div>
                </div>

                <!-- Consignee / Ship-To -->
                <div style="border-bottom:2px solid #000; padding-bottom:8px; margin-bottom:10px;">
                    <div style="font-size:9px; font-weight:800; color:#475569; text-transform:uppercase;">Ship To / Consignee:</div>
                    <div style="font-size:14px; font-weight:900; color:#000; margin-top:2px;">${consignee}</div>
                    <div style="font-size:10px; color:#334155; margin-top:2px;">${address}</div>
                </div>

                <!-- Info Grid -->
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; border-bottom:2px solid #000; padding-bottom:10px; margin-bottom:12px;">
                    <div>
                        <div style="font-size:8.5px; font-weight:800; color:#475569; text-transform:uppercase;">Deskripsi Komoditas:</div>
                        <div style="font-size:11.5px; font-weight:800; color:#000;">${commodity}</div>
                    </div>
                    <div>
                        <div style="font-size:8.5px; font-weight:800; color:#475569; text-transform:uppercase;">Alokasi Rak CFS:</div>
                        <div style="font-size:13px; font-weight:900; color:#1e40af; font-family:monospace;">${rack}</div>
                    </div>
                    <div>
                        <div style="font-size:8.5px; font-weight:800; color:#475569; text-transform:uppercase;">Kuantitas Kemasan:</div>
                        <div style="font-size:12px; font-weight:800; color:#000;">${cartons}</div>
                    </div>
                    <div>
                        <div style="font-size:8.5px; font-weight:800; color:#475569; text-transform:uppercase;">Berat Kotor (Gross Weight):</div>
                        <div style="font-size:12px; font-weight:900; font-family:monospace; color:#000;">${weight}</div>
                    </div>
                </div>

                <!-- Barcode Representation -->
                <div style="text-align:center; padding:10px 0; border-bottom:2px solid #000; margin-bottom:12px;">
                    <svg viewBox="0 0 280 65" width="280" height="65" style="margin:0 auto; display:block;">
                        <rect x="10" y="5" width="2" height="48" fill="#000"/>
                        <rect x="15" y="5" width="5" height="48" fill="#000"/>
                        <rect x="23" y="5" width="2" height="48" fill="#000"/>
                        <rect x="28" y="5" width="7" height="48" fill="#000"/>
                        <rect x="38" y="5" width="3" height="48" fill="#000"/>
                        <rect x="44" y="5" width="4" height="48" fill="#000"/>
                        <rect x="51" y="5" width="6" height="48" fill="#000"/>
                        <rect x="60" y="5" width="2" height="48" fill="#000"/>
                        <rect x="66" y="5" width="8" height="48" fill="#000"/>
                        <rect x="77" y="5" width="3" height="48" fill="#000"/>
                        <rect x="83" y="5" width="5" height="48" fill="#000"/>
                        <rect x="91" y="5" width="2" height="48" fill="#000"/>
                        <rect x="96" y="5" width="7" height="48" fill="#000"/>
                        <rect x="106" y="5" width="4" height="48" fill="#000"/>
                        <rect x="113" y="5" width="2" height="48" fill="#000"/>
                        <rect x="118" y="5" width="6" height="48" fill="#000"/>
                        <rect x="127" y="5" width="3" height="48" fill="#000"/>
                        <rect x="133" y="5" width="5" height="48" fill="#000"/>
                        <rect x="141" y="5" width="2" height="48" fill="#000"/>
                        <rect x="146" y="5" width="6" height="48" fill="#000"/>
                        <rect x="155" y="5" width="4" height="48" fill="#000"/>
                        <rect x="162" y="5" width="2" height="48" fill="#000"/>
                        <rect x="167" y="5" width="7" height="48" fill="#000"/>
                        <rect x="177" y="5" width="3" height="48" fill="#000"/>
                        <rect x="183" y="5" width="5" height="48" fill="#000"/>
                        <rect x="191" y="5" width="2" height="48" fill="#000"/>
                        <rect x="196" y="5" width="8" height="48" fill="#000"/>
                        <rect x="207" y="5" width="3" height="48" fill="#000"/>
                        <rect x="213" y="5" width="5" height="48" fill="#000"/>
                        <rect x="221" y="5" width="2" height="48" fill="#000"/>
                        <rect x="226" y="5" width="6" height="48" fill="#000"/>
                        <rect x="235" y="5" width="4" height="48" fill="#000"/>
                        <rect x="242" y="5" width="2" height="48" fill="#000"/>
                        <rect x="247" y="5" width="6" height="48" fill="#000"/>
                        <rect x="256" y="5" width="4" height="48" fill="#000"/>
                        <rect x="263" y="5" width="2" height="48" fill="#000"/>
                        <rect x="268" y="5" width="5" height="48" fill="#000"/>
                    </svg>
                    <div style="font-family:monospace; font-size:13px; font-weight:900; letter-spacing:3px; margin-top:4px;">
                        ${sscc}
                    </div>
                </div>

                <!-- Footer with Stamp & QR -->
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        ${qr}
                    </div>
                    <div style="text-align:right;">
                        ${stamp}
                    </div>
                </div>
            </div>
        </div>`;

        this.openPrintWindow(`GS1 Label ${sscc}`, content);
    }
};

// Helper tanggal ringkas
function dateStrCompact() {
    const d = new Date();
    return d.getFullYear().toString() + String(d.getMonth() + 1).padStart(2, '0') + String(d.getDate()).padStart(2, '0');
}
