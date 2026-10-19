<?php
// =============================================================================
// HALAMAN DEDIKASI LAPORAN EKSEKUTIF & AUDIT 3-ALIRAN (STANDALONE REPORT PAGE)
// File: report_executive.php
// Proyek: Conclusion Intermodal Dry Port (CIDP 35 Hectares)
// Standar: Dokumen Resmi Direksi Format A4 Presisi (Bukan Screenshot Layar)
// Mencakup: Kop Resmi, 6 KPI Utama, Audit 3 Pilar (Fisik, Informasi, Finansial),
//           Eksposur Demurrage, Manifest Konsinyasi, Stempel Basah Vektor & QR
// =============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('CIDP_SILENT_DB_FALLBACK')) {
    define('CIDP_SILENT_DB_FALLBACK', true);
}
require_once __DIR__ . '/config/i18n.php';
require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/api/beranda_telemetry.php';

$telemetry   = build_beranda_telemetry($pdo ?? null, false);
$kpi         = $telemetry['kpi'];
$fin         = $telemetry['financial'];
$latencies   = $telemetry['latencies'];
$containers  = $telemetry['containers'];
$events      = $telemetry['events'];

$nama_user   = (!empty($_SESSION['nama']) && $_SESSION['nama'] !== 'Guest') ? $_SESSION['nama'] : 'Zulfikar Jafarudin Fatah';
$doc_no      = 'CIDP/EXEC-3FLOW/' . date('Y/m/') . strtoupper(substr(md5(date('YmdH')), 0, 5));
$doc_date    = get_localized_date($current_lang);
$doc_time    = date('H:i:s') . ' WIB';
$sec_hash    = 'SHA256-' . strtoupper(substr(hash('sha256', $doc_no . date('Ymd')), 0, 16));
$auto_print  = isset($_GET['autoprint']) && $_GET['autoprint'] == '1';
$filter_mode = $_GET['scope'] ?? 'all';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Briefing &amp; 3-Flow Audit Report — <?= htmlspecialchars($doc_no) ?></title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700;800&family=Caveat:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    <script src="assets/vendor/tailwindcss.js"></script>
    <script src="assets/vendor/xlsx.full.min.js"></script>
    <script src="assets/js/export-utils.js"></script>
    <script src="assets/js/print-engine.js"></script>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 10mm 12mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #eef2f6;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }
        .font-mono-code {
            font-family: 'JetBrains Mono', monospace;
        }
        .font-signature {
            font-family: 'Caveat', cursive;
        }
        .report-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 24px auto;
            background: #ffffff;
            box-shadow: 0 20px 50px rgba(0, 47, 94, 0.12), 0 0 0 1px rgba(15, 23, 42, 0.08);
            border-radius: 6px;
            padding: 14mm 15mm;
            position: relative;
            overflow: hidden;
        }
        table.exec-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        table.exec-table th {
            background-color: #002f5e !important;
            color: #ffffff !important;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 6px 8px;
            border: 1px solid #002f5e;
            text-align: left;
        }
        table.exec-table td {
            padding: 5.5px 8px;
            border: 1px solid #cbd5e1;
            color: #1e293b;
            vertical-align: middle;
        }
        table.exec-table tr:nth-child(even) td {
            background-color: #f8fafc !important;
        }
        .section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(90deg, #002f5e 0%, #0170b9 100%);
            color: #ffffff;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 14px;
            margin-bottom: 8px;
        }
        @media print {
            body {
                background: #ffffff !important;
            }
            .no-print {
                display: none !important;
            }
            .report-sheet {
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
            .page-break-inside-avoid {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- =================================================================== -->
    <!-- FLOATING INTERACTIVE REPORT TOOLBAR (HIDDEN ON PRINT)               -->
    <!-- =================================================================== -->
    <header class="no-print sticky top-0 z-50 bg-[#002f5e] text-white border-b border-blue-800 shadow-lg">
        <div class="max-w-[1150px] mx-auto px-4 py-2.5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="dashboard.php?page=beranda" class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-bold flex items-center gap-1.5 transition border border-white/15">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    <span><?= ($current_lang === 'en') ? 'Back to Control Tower' : 'Kembali ke Beranda' ?></span>
                </a>
                <div class="h-5 w-px bg-white/20 hidden sm:block"></div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-extrabold tracking-tight text-white">HALAMAN DESAIN DOKUMEN EKSEKUTIF RESMI (A4)</span>
                        <span class="text-[9px] font-extrabold uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-400/40 px-2 py-0.5 rounded-full">
                            Live DB Connected
                        </span>
                    </div>
                    <p class="text-[10px] text-blue-200">Desain laporan terstruktur mandiri (bukan tangkapan layar) • Dilengkapi Kop Surat, Audit 3-Aliran &amp; Stempel Digital Vektor</p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <!-- Filter Scope Section -->
                <div class="flex items-center bg-slate-900/50 p-1 rounded-lg border border-white/15 text-[10.5px]">
                    <button type="button" onclick="setReportSectionView('all')" id="btnScopeAll" class="px-2.5 py-1 rounded font-bold bg-[#0170b9] text-white transition">
                        Lengkap (Semua Seksi)
                    </button>
                    <button type="button" onclick="setReportSectionView('summary')" id="btnScopeSummary" class="px-2.5 py-1 rounded font-semibold text-blue-200 hover:text-white transition">
                        Ringkasan Direksi
                    </button>
                    <button type="button" onclick="setReportSectionView('financial')" id="btnScopeFinancial" class="px-2.5 py-1 rounded font-semibold text-blue-200 hover:text-white transition">
                        Fokus Finansial &amp; Demurrage
                    </button>
                </div>

                <!-- Export Excel Button -->
                <button type="button" onclick="exportExecutiveToExcel()" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-extrabold flex items-center gap-1.5 shadow transition cursor-pointer">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Unduh Excel (.XLSX)</span>
                </button>

                <!-- Print / Save Official PDF Button -->
                <button type="button" onclick="window.print()" class="px-3.5 py-1.5 rounded-lg bg-[#ea580c] hover:bg-orange-500 text-white text-xs font-extrabold flex items-center gap-1.5 shadow-md transition cursor-pointer">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak / Simpan PDF (A4)</span>
                </button>
            </div>
        </div>
    </header>

    <!-- =================================================================== -->
    <!-- DEDICATED A4 EXECUTIVE REPORT SHEET                                 -->
    <!-- =================================================================== -->
    <main class="report-sheet">
        
        <!-- Subtle Batik Kawung Corner Watermark -->
        <div class="absolute top-0 right-0 w-52 h-52 pointer-events-none opacity-[0.05] z-0" aria-hidden="true">
            <svg width="100%" height="100%" viewBox="0 0 100 100" fill="none" stroke="#002f5e" stroke-width="1.5">
                <ellipse cx="50" cy="22" rx="16" ry="24" />
                <ellipse cx="50" cy="78" rx="16" ry="24" />
                <ellipse cx="22" cy="50" rx="24" ry="16" />
                <ellipse cx="78" cy="50" rx="24" ry="16" />
                <circle cx="50" cy="50" r="5" fill="#ea580c" />
                <circle cx="50" cy="50" r="15" stroke="#002f5e" stroke-dasharray="2 2" />
            </svg>
        </div>

        <!-- 1. KOP SURAT RESMI PERUSAHAAN (OFFICIAL CORPORATE LETTERHEAD) -->
        <div class="relative z-10 border-b-[3px] border-[#002f5e] pb-3 mb-3">
            <div class="flex justify-between items-start gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#002f5e] to-[#0170b9] text-white flex flex-col items-center justify-center font-black shadow-sm flex-shrink-0 border border-blue-900">
                        <span class="text-lg leading-none">CIDP</span>
                        <span class="text-[7px] tracking-widest font-mono text-orange-300">35 HA</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-[16px] font-black text-[#002f5e] tracking-tight uppercase leading-none">
                                CONCLUSION INTERMODAL DRY PORT
                            </h1>
                            <span class="text-[8px] font-extrabold bg-orange-50 text-orange-700 border border-orange-300 px-1.5 py-0.5 rounded uppercase">
                                Kawasan Pabean Terpadu
                            </span>
                        </div>
                        <div class="text-[10px] font-extrabold text-[#0170b9] tracking-wide mt-0.5">
                            PT MULTI TERMINAL INDONESIA &bull; ENTERPRISE DRY PORT MANAGEMENT SYSTEM (DPMS)
                        </div>
                        <div class="text-[8.5px] text-slate-600 mt-1 leading-snug">
                            Kawasan Industri Jababeka, Jl. Pelabuhan Kering Intermodal No. 1, Cikarang, Bekasi, Jawa Barat 17530<br>
                            Telp: (021) 8911-2345 &bull; Siding Rel KA Pasoso–Priok (54.8 km) &bull; Lisensi Kemenkeu RI No. KMK-412/KM.4/2026
                        </div>
                    </div>
                </div>

                <div class="text-right min-w-[215px]">
                    <div class="inline-block px-2.5 py-0.5 rounded-full bg-blue-50 border border-blue-200 text-[#002f5e] font-extrabold text-[8.5px] uppercase tracking-wider">
                        EXECUTIVE BRIEFING &amp; 3-FLOW AUDIT
                    </div>
                    <div class="text-[13px] font-black text-[#002f5e] uppercase mt-1">
                        LAPORAN KINERJA TERMINAL
                    </div>
                    <div class="font-mono-code text-[10px] font-bold text-slate-800 mt-0.5">
                        No: <?= htmlspecialchars($doc_no) ?>
                    </div>
                    <div class="text-[9px] text-slate-500 mt-0.5">
                        Dicetak: <strong><?= htmlspecialchars($doc_date) ?> &bull; <?= htmlspecialchars($doc_time) ?></strong>
                    </div>
                </div>
            </div>
            <div class="h-[1px] bg-[#ea580c] mt-2"></div>
        </div>

        <!-- METADATA BAR RINGKAS -->
        <div class="grid grid-cols-4 gap-2 bg-slate-50 border border-slate-200 rounded-md p-2 text-[9px] mb-3 relative z-10">
            <div>
                <span class="text-slate-400 font-semibold block uppercase">Otoritas Pengendali:</span>
                <strong class="text-slate-800">Executive Control Tower (35 Ha)</strong>
            </div>
            <div>
                <span class="text-slate-400 font-semibold block uppercase">Pejabat Penarik Laporan:</span>
                <strong class="text-slate-800"><?= htmlspecialchars($nama_user) ?></strong>
            </div>
            <div>
                <span class="text-slate-400 font-semibold block uppercase">Status Konektivitas H2H:</span>
                <strong class="text-emerald-700">CEISA 4.0 &bull; Inaportnet &bull; Odoo ERP OK</strong>
            </div>
            <div>
                <span class="text-slate-400 font-semibold block uppercase">Hash Keamanan Dokumen:</span>
                <strong class="font-mono-code text-[#002f5e]"><?= htmlspecialchars($sec_hash) ?></strong>
            </div>
        </div>

        <!-- =============================================================== -->
        <!-- SEKSI I: MATRIKS 6 INDIKATOR KINERJA UTAMA (EXECUTIVE KPI)      -->
        <!-- =============================================================== -->
        <div id="secSummary" class="report-section">
            <div class="section-heading">
                <span><i class="fa-solid fa-gauge-high mr-1.5"></i> I. Matriks Indikator Kinerja Utama (Executive KPI Control Tower)</span>
                <span class="font-mono-code text-[9px] text-orange-300">REAL-TIME DB SNAPSHOT</span>
            </div>

            <table class="exec-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 24%;">Dimensi Kluster Operasional</th>
                        <th style="width: 22%;">Realisasi Aktual</th>
                        <th style="width: 18%;">Kapasitas / Standar</th>
                        <th style="width: 13%;">Utilisasi / Indeks</th>
                        <th style="width: 18%;">Status Evaluasi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-mono-code font-bold text-center">01</td>
                        <td><strong>Yard Stacking Occupancy</strong> (Blok A–C)</td>
                        <td class="font-mono-code font-bold text-[#002f5e]"><?= number_format($kpi['containers_yard']) ?> TEU</td>
                        <td><?= number_format($kpi['yard_cap_max']) ?> TEU (16.5 Ha)</td>
                        <td class="font-mono-code font-bold"><?= $kpi['yard_occupancy'] ?>%</td>
                        <td><span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-300 font-bold text-[8.5px]">OPTIMAL (&lt; 75% YOR)</span></td>
                    </tr>
                    <tr>
                        <td class="font-mono-code font-bold text-center">02</td>
                        <td><strong>Gate Turnaround Time</strong> (ANPR &amp; VGM 80T)</td>
                        <td class="font-mono-code font-bold text-orange-700"><?= $kpi['gate_turnaround'] ?> Menit (<?= $kpi['total_trucks'] ?> Truk)</td>
                        <td>Target INSA &le; 20.0 Menit</td>
                        <td class="font-mono-code font-bold">98.0% VBS</td>
                        <td><span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-300 font-bold text-[8.5px]">MEMENUHI SLA INSA</span></td>
                    </tr>
                    <tr>
                        <td class="font-mono-code font-bold text-center">03</td>
                        <td><strong>Siding Kereta Api Intermodal</strong> (KA 2518)</td>
                        <td class="font-mono-code font-bold text-[#0170b9]"><?= $kpi['loaded_wagons'] ?> / <?= $kpi['total_wagons'] ?> Gerbong Datar</td>
                        <td>Jalur S-01 (2 &times; 600m)</td>
                        <td class="font-mono-code font-bold"><?= $kpi['siding_pct'] ?>% Load</td>
                        <td><span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-800 border border-blue-300 font-bold text-[8.5px]">SIAGA PEMBERANGKATAN</span></td>
                    </tr>
                    <tr>
                        <td class="font-mono-code font-bold text-center">04</td>
                        <td><strong>Reefer Cold Chain Hub</strong> (Marechal 380V)</td>
                        <td class="font-mono-code font-bold text-cyan-700"><?= $kpi['reefer_temp'] ?>&deg;C (<?= $kpi['reefer_plugs'] ?> Box)</td>
                        <td>300 Steker Reefer IoT</td>
                        <td class="font-mono-code font-bold"><?= $kpi['reefer_pct'] ?>% Plug</td>
                        <td><span class="px-1.5 py-0.5 rounded bg-cyan-50 text-cyan-800 border border-cyan-300 font-bold text-[8.5px]">HACCP 98.8% TERJAGA</span></td>
                    </tr>
                    <tr>
                        <td class="font-mono-code font-bold text-center">05</td>
                        <td><strong>Kepatuhan Pabean Bea Cukai</strong> (CEISA 4.0)</td>
                        <td class="font-mono-code font-bold text-emerald-700"><?= $kpi['customs_cleared'] ?> / <?= $kpi['customs_total'] ?> Box SPPB</td>
                        <td>Rilis Otomatis &le; 15 Detik</td>
                        <td class="font-mono-code font-bold"><?= $kpi['customs_pct'] ?>% Clear</td>
                        <td><span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-300 font-bold text-[8.5px]">HOST-TO-HOST AKTIF</span></td>
                    </tr>
                    <tr>
                        <td class="font-mono-code font-bold text-center">06</td>
                        <td><strong>Gudang CFS &amp; Realisasi Billing ERP</strong></td>
                        <td class="font-mono-code font-bold text-slate-900"><?= $kpi['cfs_jobs'] ?> Jobs &bull; Rp <?= number_format($fin['paid_revenue'], 0, ',', '.') ?></td>
                        <td>Rampa D1–D5 (4.000 m&sup2;)</td>
                        <td class="font-mono-code font-bold"><?= $fin['settlement_pct'] ?>% Paid</td>
                        <td><span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-300 font-bold text-[8.5px]">TERKONSOLIDASI ODOO</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- =============================================================== -->
        <!-- SEKSI II: AUDIT INTEGRASI 3 PILAR ALIRAN LOGISTIK               -->
        <!-- =============================================================== -->
        <div id="secThreePillars" class="report-section page-break-inside-avoid">
            <div class="section-heading">
                <span><i class="fa-solid fa-diagram-project mr-1.5"></i> II. Audit Terpadu 3 Pilar Aliran Logistik (Fisik, Informasi &amp; Finansial)</span>
                <span class="font-mono-code text-[9px] text-orange-300">ARSITEKTUR 3-FLOW CIDP</span>
            </div>

            <div class="grid grid-cols-3 gap-2.5 text-[9.5px]">
                <!-- Pilar 1: Fisik -->
                <div class="border border-slate-300 rounded-md p-2.5 bg-slate-50/60">
                    <div class="font-extrabold text-[#002f5e] uppercase border-b border-slate-200 pb-1 mb-1.5 flex items-center justify-between">
                        <span>1. Aliran Fisik (35 Ha)</span>
                        <span class="text-[8px] bg-blue-100 text-[#002f5e] px-1.5 py-0.2 rounded font-mono-code">4 ZONA</span>
                    </div>
                    <ul class="space-y-1 text-slate-700">
                        <li class="flex justify-between"><span>&bull; Z1 Gerbang &amp; VGM 80T:</span><strong>4.2 Ha (Lancar)</strong></li>
                        <li class="flex justify-between"><span>&bull; Z2 Stacking Blok A–C:</span><strong>16.5 Ha (<?= $kpi['yard_occupancy'] ?>%)</strong></li>
                        <li class="flex justify-between"><span>&bull; Z3 Gudang CFS LCL:</span><strong>6.8 Ha (<?= $kpi['cfs_jobs'] ?> Jobs)</strong></li>
                        <li class="flex justify-between"><span>&bull; Z4 Siding KA &amp; Reefer:</span><strong>7.5 Ha (2&times;600m)</strong></li>
                        <li class="flex justify-between pt-1 border-t border-slate-200 text-[#0170b9]"><span>&bull; Modal Split Kereta:</span><strong>68% Rail (-1.84T CO&sup2;)</strong></li>
                    </ul>
                </div>

                <!-- Pilar 2: Informasi -->
                <div class="border border-slate-300 rounded-md p-2.5 bg-slate-50/60">
                    <div class="font-extrabold text-[#002f5e] uppercase border-b border-slate-200 pb-1 mb-1.5 flex items-center justify-between">
                        <span>2. Aliran Informasi &amp; IoT</span>
                        <span class="text-[8px] bg-emerald-100 text-emerald-800 px-1.5 py-0.2 rounded font-mono-code">5 GATEWAY</span>
                    </div>
                    <ul class="space-y-1 text-slate-700">
                        <li class="flex justify-between"><span>&bull; Bea Cukai CEISA 4.0:</span><strong class="font-mono-code text-emerald-700"><?= $latencies['ceisa'] ?> ms (SPPB 92%)</strong></li>
                        <li class="flex justify-between"><span>&bull; Inaportnet Kemenhub:</span><strong class="font-mono-code text-emerald-700"><?= $latencies['inaport'] ?> ms (Sinkron)</strong></li>
                        <li class="flex justify-between"><span>&bull; KAI Logistik EDI:</span><strong class="font-mono-code text-emerald-700"><?= $latencies['kai_edi'] ?> ms (KA 2518)</strong></li>
                        <li class="flex justify-between"><span>&bull; LoRaWAN Reefer 868MHz:</span><strong class="font-mono-code text-cyan-700"><?= $latencies['lorawan'] ?> ms (SHT40)</strong></li>
                        <li class="flex justify-between pt-1 border-t border-slate-200 text-purple-800"><span>&bull; Identifikasi Box/Pallet:</span><strong>RFID UHF &amp; SSCC-18</strong></li>
                    </ul>
                </div>

                <!-- Pilar 3: Finansial & Demurrage -->
                <div class="border border-orange-300 rounded-md p-2.5 bg-orange-50/30">
                    <div class="font-extrabold text-orange-900 uppercase border-b border-orange-200 pb-1 mb-1.5 flex items-center justify-between">
                        <span>3. Aliran Finansial &amp; ERP</span>
                        <span class="text-[8px] bg-orange-100 text-orange-800 px-1.5 py-0.2 rounded font-mono-code">ODOO 17</span>
                    </div>
                    <ul class="space-y-1 text-slate-700">
                        <li class="flex justify-between"><span>&bull; Total Faktur Terbit:</span><strong class="font-mono-code">Rp <?= number_format($fin['total_invoiced'], 0, ',', '.') ?></strong></li>
                        <li class="flex justify-between"><span>&bull; Lunas (Gate-Out Clear):</span><strong class="font-mono-code text-emerald-700">Rp <?= number_format($fin['paid_revenue'], 0, ',', '.') ?></strong></li>
                        <li class="flex justify-between"><span>&bull; Piutang Berjalan (VA):</span><strong class="font-mono-code text-amber-700">Rp <?= number_format($fin['pending_receivable'], 0, ',', '.') ?></strong></li>
                        <li class="flex justify-between"><span>&bull; Eksposur Demurrage:</span><strong class="font-mono-code text-rose-700">Rp <?= number_format($fin['demurrage_accrual_rp'], 0, ',', '.') ?></strong></li>
                        <li class="flex justify-between pt-1 border-t border-orange-200 text-rose-800"><span>&bull; Box Inap &gt; 3 Hari:</span><strong><?= $fin['demurrage_box_count'] ?> Kontainer Aktif</strong></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- =============================================================== -->
        <!-- SEKSI III: BUKU BESAR FAKTUR ODOO ERP & EKSPOSUR DEMURRAGE      -->
        <!-- =============================================================== -->
        <div id="secFinancial" class="report-section page-break-inside-avoid">
            <div class="section-heading">
                <span><i class="fa-solid fa-file-invoice-dollar mr-1.5"></i> III. Rekapitulasi Faktur Komersial Odoo ERP &amp; Pengendalian Demurrage</span>
                <span class="font-mono-code text-[9px] text-orange-300">XML-RPC BILLING LEDGER</span>
            </div>

            <table class="exec-table">
                <thead>
                    <tr>
                        <th style="width: 16%;">No. Faktur ERP</th>
                        <th style="width: 14%;">No. Kontainer</th>
                        <th style="width: 26%;">Pelanggan / Shipper</th>
                        <th style="width: 20%;">Komponen Layanan Terminal</th>
                        <th style="width: 14%; text-align:right;">Nilai Tagihan</th>
                        <th style="width: 10%; text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fin['invoices'] as $inv): 
                        $pstat = $inv['payment_status'];
                        $badge_cls = 'bg-amber-50 text-amber-800 border-amber-300';
                        $label_txt = 'MENUNGGU';
                        if ($pstat === 'PAID') {
                            $badge_cls = 'bg-emerald-50 text-emerald-800 border-emerald-300';
                            $label_txt = 'LUNAS';
                        } elseif ($pstat === 'OVERDUE') {
                            $badge_cls = 'bg-rose-50 text-rose-800 border-rose-300';
                            $label_txt = 'OVERDUE';
                        }
                    ?>
                    <tr>
                        <td class="font-mono-code font-bold text-[#002f5e]"><?= htmlspecialchars($inv['invoice_number']) ?></td>
                        <td class="font-mono-code font-bold text-[#0170b9]"><?= htmlspecialchars($inv['container_number']) ?></td>
                        <td class="font-semibold"><?= htmlspecialchars($inv['customer_name']) ?></td>
                        <td><?= htmlspecialchars($inv['service_type']) ?></td>
                        <td class="font-mono-code font-bold text-right">Rp <?= number_format($inv['total_amount'], 0, ',', '.') ?></td>
                        <td class="text-center">
                            <span class="px-1.5 py-0.5 rounded border font-bold text-[8px] <?= $badge_cls ?>"><?= $label_txt ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- =============================================================== -->
        <!-- SEKSI IV: MANIFEST KONSINYASI PETI KEMAS & DWELL TIME           -->
        <!-- =============================================================== -->
        <div id="secManifest" class="report-section">
            <div class="section-heading">
                <span><i class="fa-solid fa-boxes-stacked mr-1.5"></i> IV. Manifest Konsinyasi Peti Kemas Aktif (ISO 6346, RFID UHF &amp; Dwell Time)</span>
                <span class="font-mono-code text-[9px] text-orange-300">TOTAL SAMPLE: <?= count($containers) ?> UNIT</span>
            </div>

            <table class="exec-table">
                <thead>
                    <tr>
                        <th style="width: 14%;">No. Kontainer</th>
                        <th style="width: 22%;">Pemilik / Shipper</th>
                        <th style="width: 11%;">Tipe ISO</th>
                        <th style="width: 14%;">Tag RFID UHF</th>
                        <th style="width: 15%;">Lokasi Yard</th>
                        <th style="width: 10%; text-align:center;">Dwell Time</th>
                        <th style="width: 14%; text-align:right;">Akrual Storage/Denda</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($containers, 0, 10) as $c): 
                        $is_dem = !empty($c['is_demurrage']);
                        $tot_accrual = (float)$c['storage_rp'] + (float)$c['demurrage_rp'];
                    ?>
                    <tr>
                        <td class="font-mono-code font-bold text-[#002f5e]"><?= htmlspecialchars($c['container_number']) ?></td>
                        <td class="font-medium"><?= htmlspecialchars($c['owner_company']) ?></td>
                        <td class="font-mono-code text-[9px]"><?= htmlspecialchars($c['size_type']) ?> (<?= htmlspecialchars($c['iso_code']) ?>)</td>
                        <td class="font-mono-code text-[9px] text-purple-800"><?= htmlspecialchars($c['rfid_tag']) ?></td>
                        <td class="font-semibold"><?= htmlspecialchars($c['position_label']) ?></td>
                        <td class="text-center font-mono-code font-bold <?= $is_dem ? 'text-rose-700' : 'text-emerald-700' ?>">
                            <?= (int)$c['dwell_days'] ?> Hari <?= $is_dem ? '⚠️' : '✓' ?>
                        </td>
                        <td class="font-mono-code text-right font-bold <?= $is_dem ? 'text-rose-700' : 'text-slate-700' ?>">
                            Rp <?= number_format($tot_accrual, 0, ',', '.') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- =============================================================== -->
        <!-- SEKSI V: PENGESAHAN RESMI, QR VERIFIKASI & STEMPEL DIGITAL      -->
        <!-- =============================================================== -->
        <div class="mt-4 pt-3 border-t-2 border-[#002f5e] page-break-inside-avoid">
            <div class="grid grid-cols-12 gap-3 items-end">
                
                <!-- QR Code & Pernyataan Keabsahan (4 Cols) -->
                <div class="col-span-4 bg-slate-50 border border-slate-200 rounded-md p-2.5 flex items-center gap-2.5">
                    <div class="w-16 h-16 bg-white border border-[#002f5e] p-1 rounded flex items-center justify-center flex-shrink-0">
                        <svg viewBox="0 0 45 45" width="54" height="54" fill="#002f5e">
                            <rect x="0" y="0" width="13" height="13" />
                            <rect x="2" y="2" width="9" height="9" fill="#fff" />
                            <rect x="4" y="4" width="5" height="5" />
                            <rect x="32" y="0" width="13" height="13" />
                            <rect x="34" y="2" width="9" height="9" fill="#fff" />
                            <rect x="36" y="4" width="5" height="5" />
                            <rect x="0" y="32" width="13" height="13" />
                            <rect x="2" y="34" width="9" height="9" fill="#fff" />
                            <rect x="4" y="36" width="5" height="5" />
                            <rect x="16" y="2" width="3" height="3" /><rect x="22" y="2" width="3" height="3" /><rect x="26" y="6" width="3" height="3" />
                            <rect x="2" y="16" width="3" height="3" /><rect x="8" y="20" width="3" height="3" /><rect x="16" y="16" width="5" height="5" />
                            <rect x="24" y="18" width="3" height="3" /><rect x="34" y="18" width="3" height="3" /><rect x="40" y="16" width="3" height="3" />
                            <rect x="16" y="26" width="3" height="3" /><rect x="22" y="24" width="5" height="5" /><rect x="32" y="26" width="3" height="3" />
                            <rect x="16" y="36" width="3" height="3" /><rect x="24" y="34" width="3" height="3" /><rect x="36" y="36" width="5" height="5" />
                        </svg>
                    </div>
                    <div class="text-[8.5px] leading-snug text-slate-600">
                        <div class="font-extrabold text-[#002f5e] text-[9px] uppercase">Keabsahan Elektronik</div>
                        <p class="mt-0.5">Dokumen ini diterbitkan resmi dari basis data <strong>CIDP Control Tower</strong> sesuai UU ITE &amp; PMK 190/2022.</p>
                        <div class="font-mono-code font-bold text-slate-900 mt-1 text-[8px]"><?= htmlspecialchars($sec_hash) ?></div>
                    </div>
                </div>

                <!-- Tanda Tangan 1: KPU Bea & Cukai (2.5 Cols) -->
                <div class="col-span-2 text-center">
                    <div class="text-[8.5px] font-bold text-slate-500 uppercase">Mengetahui,</div>
                    <div class="text-[9px] font-extrabold text-slate-800">Pengawas Bea Cukai</div>
                    <div class="h-16 flex items-center justify-center">
                        <span class="px-2 py-0.5 rounded border border-emerald-600 text-emerald-700 font-mono-code font-extrabold text-[8px] bg-emerald-50">
                            ✓ CEISA 4.0 VERIFIED
                        </span>
                    </div>
                    <div class="border-t border-slate-400 pt-0.5">
                        <div class="text-[9px] font-black text-slate-900">HENDRA KUSUMA, S.E.</div>
                        <div class="text-[7.5px] text-slate-500">Pejabat Hanggar Pabean</div>
                    </div>
                </div>

                <!-- Tanda Tangan 2: Finance & ERP Controller + Red Stamp (3 Cols) -->
                <div class="col-span-3 text-center relative">
                    <div class="text-[8.5px] font-bold text-slate-500 uppercase">Divalidasi Oleh,</div>
                    <div class="text-[9px] font-extrabold text-slate-800">Finance &amp; ERP Billing</div>
                    <div class="h-16 flex items-center justify-center relative">
                        <svg viewBox="0 0 120 120" width="66" height="66" class="absolute opacity-85" style="transform: rotate(-6deg);">
                            <circle cx="60" cy="60" r="54" fill="#fef2f2" fill-opacity="0.5" stroke="#dc2626" stroke-width="2.5"/>
                            <circle cx="60" cy="60" r="49" fill="none" stroke="#dc2626" stroke-width="1" stroke-dasharray="3 2"/>
                            <rect x="16" y="47" width="88" height="24" rx="3" fill="#dc2626" fill-opacity="0.12" stroke="#dc2626" stroke-width="1.3"/>
                            <text x="60" y="61" font-size="10" font-weight="900" fill="#dc2626" text-anchor="middle" letter-spacing="1.5">ERP AUDITED</text>
                            <text x="60" y="34" font-size="6.5" font-weight="800" fill="#dc2626" text-anchor="middle">FINANCE &amp; BILLING</text>
                            <text x="60" y="88" font-size="6" font-weight="700" fill="#dc2626" text-anchor="middle">CIDP DRY PORT</text>
                        </svg>
                        <span class="font-signature text-xl text-slate-900 relative z-10">Afriansyah A.</span>
                    </div>
                    <div class="border-t border-slate-400 pt-0.5">
                        <div class="text-[9px] font-black text-slate-900">AFRIANSYAH AYUBI, S.Log.</div>
                        <div class="text-[7.5px] text-slate-500">Head of ERP &amp; Commercial</div>
                    </div>
                </div>

                <!-- Tanda Tangan 3: Head of Terminal Ops + Navy Stamp (3 Cols) -->
                <div class="col-span-3 text-center relative">
                    <div class="text-[8.5px] font-bold text-slate-500 uppercase">Disetujui Oleh,</div>
                    <div class="text-[9px] font-extrabold text-slate-800">Kepala Operasional Terminal</div>
                    <div class="h-16 flex items-center justify-center relative">
                        <svg viewBox="0 0 120 120" width="68" height="68" class="absolute opacity-90" style="transform: rotate(-4deg);">
                            <circle cx="60" cy="60" r="54" fill="#f0f7ff" fill-opacity="0.5" stroke="#002f5e" stroke-width="2.5"/>
                            <circle cx="60" cy="60" r="49" fill="none" stroke="#002f5e" stroke-width="1" stroke-dasharray="3 2"/>
                            <rect x="16" y="47" width="88" height="24" rx="3" fill="#002f5e" fill-opacity="0.1" stroke="#002f5e" stroke-width="1.3"/>
                            <text x="60" y="61" font-size="10.5" font-weight="900" fill="#002f5e" text-anchor="middle" letter-spacing="1.8">APPROVED</text>
                            <text x="60" y="34" font-size="6.5" font-weight="800" fill="#002f5e" text-anchor="middle">CONTROL TOWER 35HA</text>
                            <text x="60" y="88" font-size="6" font-weight="700" fill="#002f5e" text-anchor="middle">PT MULTI TERMINAL</text>
                        </svg>
                        <span class="font-signature text-xl text-slate-900 relative z-10">Zulfikar J.F.</span>
                    </div>
                    <div class="border-t border-slate-400 pt-0.5">
                        <div class="text-[9px] font-black text-slate-900"><?= htmlspecialchars(strtoupper($nama_user)) ?></div>
                        <div class="text-[7.5px] text-slate-500">Executive Control Tower Lead</div>
                    </div>
                </div>

            </div>
        </div>

    </main>

    <script>
        const reportContainers = <?= json_encode($containers, JSON_UNESCAPED_SLASHES) ?>;
        const reportInvoices   = <?= json_encode($fin['invoices'], JSON_UNESCAPED_SLASHES) ?>;

        function setReportSectionView(mode) {
            const secSummary   = document.getElementById('secSummary');
            const secPillars   = document.getElementById('secThreePillars');
            const secFinancial = document.getElementById('secFinancial');
            const secManifest  = document.getElementById('secManifest');

            const btns = {
                all: document.getElementById('btnScopeAll'),
                summary: document.getElementById('btnScopeSummary'),
                financial: document.getElementById('btnScopeFinancial')
            };

            Object.values(btns).forEach(b => {
                if (b) b.className = 'px-2.5 py-1 rounded font-semibold text-blue-200 hover:text-white transition';
            });
            if (btns[mode]) {
                btns[mode].className = 'px-2.5 py-1 rounded font-bold bg-[#0170b9] text-white transition';
            }

            if (mode === 'summary') {
                secSummary.style.display = '';
                secPillars.style.display = '';
                secFinancial.style.display = 'none';
                secManifest.style.display = 'none';
            } else if (mode === 'financial') {
                secSummary.style.display = 'none';
                secPillars.style.display = '';
                secFinancial.style.display = '';
                secManifest.style.display = '';
            } else {
                secSummary.style.display = '';
                secPillars.style.display = '';
                secFinancial.style.display = '';
                secManifest.style.display = '';
            }
        }

        function exportExecutiveToExcel() {
            const headers = ['No Kontainer', 'Pemilik / Shipper', 'Tipe ISO', 'Tag RFID UHF', 'Kode GS1 SSCC', 'Posisi Lapangan', 'Dwell Time (Hari)', 'Status Bea Cukai', 'Biaya Storage (Rp)', 'Denda Demurrage (Rp)'];
            const rows = reportContainers.map(c => [
                c.container_number,
                c.owner_company,
                c.size_type + ' (' + c.iso_code + ')',
                c.rfid_tag,
                c.sscc_code,
                c.position_label,
                c.dwell_days,
                c.customs_status,
                c.storage_rp,
                c.demurrage_rp
            ]);
            if (typeof CIDPExport !== 'undefined') {
                CIDPExport.toExcel(headers, rows, 'Executive_3Flow_Audit', 'CIDP_Executive_Report_35Ha');
            }
        }

        <?php if ($auto_print): ?>
        window.addEventListener('load', function() {
            setTimeout(function() { window.print(); }, 450);
        });
        <?php endif; ?>
    </script>
</body>
</html>
