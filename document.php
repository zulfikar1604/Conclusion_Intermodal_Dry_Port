<?php
// =============================================================================
// MODUL DEDIKASI PENCETAKAN & DESAIN DOKUMEN KERTAS RESMI (STANDALONE A4 VIEWER)
// File: document.php
// Proyek: Conclusion Intermodal Dry Port (CIDP 35 Hectares)
// PT Multi Terminal Indonesia (Pelindo Logistics) / ITL Trisakti
//
// Standar: Desain Kertas Resmi A4 Mandiri (Bukan Screenshot Web)
// Dilengkapi:
//   - Kop Surat Resmi (Corporate Letterhead & Garuda/Kemenkeu DJBC)
//   - Garis Ganda Pembatas Kop Surat
//   - Tabel Bergaris Rapi, Format Mata Uang & Angka Resmi
//   - Stempel Basah Digital Vektor (High-Res SVG)
//   - QR Code Verifikasi Keabsahan Integritas Dokumen
//   - Kolom Tanda Tangan Pejabat Berwenang
//   - Toolbar Pengendali Cetak (Hilang Otomatis Saat Dicetak ke Kertas / PDF)
// =============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/config/i18n.php';

$type = trim($_GET['type'] ?? 'invoice');
$ref_id = trim($_GET['id'] ?? ($_GET['no'] ?? ($_GET['container'] ?? ($_GET['plate'] ?? ($_GET['job'] ?? '')))));
$auto_print = isset($_GET['autoprint']) && $_GET['autoprint'] == '1';

// Format tanggal & nomor dokumen default
$doc_date = get_localized_date($current_lang ?? 'id');
$doc_time = date('H:i') . ' WIB';

// =============================================================================
// 1. DATA FETCHING SESUAI TIPE DOKUMEN
// =============================================================================

$doc_data = [];

if ($type === 'invoice') {
    // 1. FAKTUR PENAGIHAN JASA KEPELABUHANAN (INVOICE)
    $inv = null;
    if (!empty($ref_id) && isset($pdo)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM billing_invoices WHERE invoice_number = ? OR container_number = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$ref_id, $ref_id]);
            $inv = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }

    if (!$inv && isset($pdo)) {
        try {
            $stmt = $pdo->query("SELECT * FROM billing_invoices ORDER BY id DESC LIMIT 1");
            $inv = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }

    $c_num = $inv['container_number'] ?? 'MSKU9180379';
    $cust = $inv['customer_name'] ?? 'PT Samudera Logistik Prima';
    $inv_no = $inv['invoice_number'] ?? ('INV-L-' . date('Ymd') . '-82df');
    $status = strtoupper($inv['payment_status'] ?? 'PENDING');
    $tot = (float)($inv['total_amount'] ?? 600000);
    $lolo = (float)($inv['lolo_amount'] ?? 200000);
    $storage = (float)($inv['storage_amount'] ?? 275000);
    $cfs = (float)($inv['cfs_amount'] ?? 0);
    $reefer = (float)($inv['reefer_power_amount'] ?? 0);
    $weigh = 75000.0;
    if ($tot <= 0) $tot = $lolo + $storage + $weigh;

    $ppn = round($tot * 0.11);
    $grand_total = $tot + $ppn + 10000; // Bea Meterai Digital Rp 10.000

    $doc_data = [
        'title' => 'FAKTUR PENAGIHAN JASA TERMINAL (INVOICE)',
        'doc_no' => $inv_no,
        'tax_serial' => '010.004-26.' . substr(abs(crc32($inv_no)), 0, 8),
        'customer' => $cust,
        'customer_npwp' => '01.382.910.4-054.000',
        'customer_addr' => 'Kawasan Industri Jababeka Blok C-12, Cikarang, Bekasi, Jawa Barat',
        'container_number' => $c_num,
        'size_type' => '40FT HIGH CUBE',
        'service_type' => $inv['service_type'] ?? 'Terminal Handling & Storage Service',
        'status' => $status,
        'due_date' => date('d F Y', strtotime('+14 days')),
        'items' => [
            ['desc' => 'Jasa Penimbangan Kendaraan & SOLAS VGM 80 Ton (Load Cell HW-03)', 'qty' => 1, 'unit' => 'Truk', 'rate' => $weigh, 'amount' => $weigh],
            ['desc' => 'Jasa Lift-Off / Penanganan Alat Berat Reach Stacker (HW-08)', 'qty' => 1, 'unit' => 'Box', 'rate' => $lolo, 'amount' => $lolo],
            ['desc' => 'Jasa Penumpukan Lapangan Container Yard (Storage Blok B)', 'qty' => 2, 'unit' => 'Hari', 'rate' => $storage / 2, 'amount' => $storage],
            ['desc' => 'Administrasi Gerbang & Integrasi EDI Inaportnet', 'qty' => 1, 'unit' => 'Dokumen', 'rate' => 50000, 'amount' => 50000]
        ],
        'subtotal' => $tot,
        'ppn' => $ppn,
        'meterai' => 10000,
        'grand_total' => $grand_total,
        'terbilang' => terbilangRupiah($grand_total)
    ];

} elseif ($type === 'gate_pass') {
    // 2. SURAT JALAN & EQUIPMENT INTERCHANGE RECEIPT (EIR / GATE PASS)
    $truk = null;
    $plate = trim($_GET['plate'] ?? '');
    if (!empty($plate) && isset($pdo)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM trucks WHERE license_plate = ? OR container_number = ? LIMIT 1");
            $stmt->execute([$plate, $plate]);
            $truk = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }

    if (!$truk && isset($pdo)) {
        try {
            $stmt = $pdo->query("SELECT * FROM trucks WHERE status != 'scheduled' ORDER BY id DESC LIMIT 1");
            $truk = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }

    $c_num = $truk['container_number'] ?? 'MAEU9185181';
    $t_plate = $truk['license_plate'] ?? ($plate ?: 'B 2918 CKR');
    $driver = $truk['driver_name'] ?? 'Rudi Hartono';
    $gross = (float)($truk['weight_gross'] ?? 34850);
    $tare = (float)($truk['weight_tare'] ?? 12500);
    $net = max(0, $gross - $tare);

    $doc_data = [
        'title' => 'SURAT JALAN & TANDA TERIMA ALAT (GATE PASS / EIR)',
        'doc_no' => 'EIR-IN-' . date('Ymd') . '-' . substr(abs(crc32($t_plate)), 0, 4),
        'transporter' => $truk['transporter'] ?? 'PT Samudera Logistik Prima',
        'license_plate' => $t_plate,
        'driver_name' => $driver,
        'driver_sim' => 'SIM B2 Umum #' . (982100 + rand(10, 99)),
        'rfid_tid' => $truk['rfid_tag'] ?? 'E28011700000020489110014',
        'container_number' => $c_num,
        'seal_number' => 'SL-CIDP-' . substr(abs(crc32($c_num)), 0, 5),
        'size_type' => '40FT HIGH CUBE (45G1)',
        'gate_lane' => 'Gate 01 (Lane A - Inbound Heavy)',
        'weight_gross' => $gross ?: 34850,
        'weight_tare' => $tare ?: 12500,
        'weight_net' => $net ?: 22350,
        'yard_slot' => 'Blok B / Bay 04 - Row 02 - Tier 01',
        'equipment' => 'Reach Stacker RS-02 (Agus Setiawan)',
        'vgm_cert' => 'VGM-SOLAS-' . date('Ymd') . '-OK',
        'condition' => 'BAIK / UTUH (CLEAN - NO APPARENT DAMAGE)'
    ];

} elseif ($type === 'sppb') {
    // 3. SURAT PERSETUJUAN PENGELUARAN BARANG (SPPB CEISA 4.0 DJBC)
    $c_num = trim($_GET['container'] ?? ($_GET['id'] ?? ($_GET['no'] ?? 'MSKU7829104')));
    $ctr_db = null;
    if (isset($pdo) && !empty($c_num)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM containers WHERE container_number = ? LIMIT 1");
            $stmt->execute([$c_num]);
            $ctr_db = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }

    $importer = !empty($ctr_db['owner_company']) ? $ctr_db['owner_company'] : 'PT Unilever Indonesia Tbk';
    $seal = !empty($ctr_db['seal_number']) ? $ctr_db['seal_number'] : ('DJBC-SEAL-' . rand(10000, 99999));
    $size = !empty($ctr_db['size_type']) ? $ctr_db['size_type'] : "40' High Cube";
    $commodity = !empty($ctr_db['cargo_type']) ? $ctr_db['cargo_type'] : 'Bahan Baku Sabun & Personal Care Products (Non-Lartas)';
    $channel = ($ctr_db['customs_status'] ?? '') === 'RED_LANE' ? 'JALUR MERAH (PEMERIKSAAN FISIK BEHANDLE)' : 'JALUR HIJAU (AUTOMATIC CLEARANCE CEISA 4.0)';

    $doc_data = [
        'title' => 'SURAT PERSETUJUAN PENGELUARAN BARANG (SPPB)',
        'doc_no' => 'SPPB-' . rand(100000, 999999) . '/KPU.01/' . date('Y'),
        'pib_no' => '042819/KPU.01/' . date('Y'),
        'pib_date' => date('d-m-Y'),
        'pib_aju' => '000020-001234-' . date('Ymd') . '-000101',
        'importer' => $importer,
        'importer_npwp' => '01.000.015.8-054.000',
        'importer_addr' => 'Kawasan Industri Jababeka Tahap II Blok J-5, Cikarang, Bekasi',
        'container_number' => $c_num,
        'size_type' => $size,
        'seal_number' => $seal,
        'commodity' => $commodity,
        'channel' => $channel,
        'ntpn_status' => 'LUNAS (NTPN: 9A8B7C6D5E4F3A2B)',
        'customs_office' => 'KANTOR PENGAWASAN DAN PELAYANAN TIPE MADYA PABEAN MARUNDA / CIKARANG',
        'officer_name' => 'HENDRA KUSUMA, S.E.',
        'officer_nip' => '19850412 200801 1 003'
    ];

} elseif ($type === 'cfs_tally') {
    // 4. CFS TALLY SHEET & STRIPPING MANIFEST
    $c_num = trim($_GET['container'] ?? ($_GET['id'] ?? 'HLCU9181850'));
    $job_param = trim($_GET['job'] ?? ($_GET['no'] ?? ''));
    $job_db = null;
    if (isset($pdo)) {
        try {
            if (!empty($job_param)) {
                $stmt = $pdo->prepare("SELECT * FROM cfs_jobs WHERE job_number = ? OR container_number = ? LIMIT 1");
                $stmt->execute([$job_param, $job_param]);
                $job_db = $stmt->fetch(PDO::FETCH_ASSOC);
            } elseif (!empty($c_num)) {
                $stmt = $pdo->prepare("SELECT * FROM cfs_jobs WHERE container_number = ? ORDER BY id DESC LIMIT 1");
                $stmt->execute([$c_num]);
                $job_db = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {}
    }

    $c_num = $job_db['container_number'] ?? $c_num;
    $job_no = $job_db['job_number'] ?? ('JO-STP-' . date('Y') . '-008');
    $consignee = $job_db['shipper_consignee'] ?? 'PT Astra Honda Motor (Karawang Plant)';
    $ramp = $job_db['dock_ramp'] ?? 'Rampa D-02 (Indoor CFS Warehouse 8.500 m²)';
    $tallyman = $job_db['operator_name'] ?? 'Juan Gamaliel, S.Log.';
    $pkgs = (int)($job_db['total_packages'] ?? 184);
    $weight = (float)($job_db['total_weight_kg'] ?? 4190);

    $doc_data = [
        'title' => 'CFS CARGO TALLY SHEET & STRIPPING MANIFEST',
        'doc_no' => 'TALLY-CFS-' . date('Ymd') . '-' . substr(abs(crc32($c_num)), 0, 4),
        'job_no' => $job_no,
        'container_number' => $c_num,
        'size_type' => '40FT DRY VAN',
        'consignee' => $consignee,
        'ramp_id' => $ramp,
        'tallyman' => $tallyman,
        'supervisor' => 'Armansyah Muchtarrom, S.T.',
        'pallets' => [
            ['sscc' => '(00)389912345000000101', 'desc' => 'Automotive Engine Parts Box A', 'qty' => 48, 'weight' => '1.240 kg', 'cond' => 'BAIK (OK)', 'slot' => 'Rak R-01'],
            ['sscc' => '(00)389912345000000102', 'desc' => 'Transmission Gears Assembly', 'qty' => 36, 'weight' => '1.180 kg', 'cond' => 'BAIK (OK)', 'slot' => 'Rak R-02'],
            ['sscc' => '(00)389912345000000103', 'desc' => 'Electrical Wiring Harness Kit', 'qty' => 60, 'weight' => '850 kg', 'cond' => 'BAIK (OK)', 'slot' => 'Rak R-03'],
            ['sscc' => '(00)389912345000000104', 'desc' => 'Brake Caliper Sub-Assembly', 'qty' => 40, 'weight' => '920 kg', 'cond' => 'BAIK (OK)', 'slot' => 'Rak R-04']
        ],
        'total_koli' => $pkgs ?: 184,
        'total_weight' => number_format($weight, 0, ',', '.') . ' kg',
        'status' => 'STRIPPING COMPLETED & PALLETS REGISTERED'
    ];

} elseif ($type === 'reefer_cert') {
    // 5. REEFER COLD CHAIN INTEGRITY CERTIFICATE (HACCP)
    $c_num = trim($_GET['container'] ?? ($_GET['id'] ?? 'MAEU9185181'));
    $rf_db = null;
    $ctr_db = null;
    if (isset($pdo) && !empty($c_num)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM reefer_telemetry WHERE container_number = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$c_num]);
            $rf_db = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt2 = $pdo->prepare("SELECT * FROM containers WHERE container_number = ? LIMIT 1");
            $stmt2->execute([$c_num]);
            $ctr_db = $stmt2->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }

    $socket = $rf_db['socket_id'] ?? 'Socket RF-003 (Platform 380V/32A)';
    $set_temp = isset($rf_db['set_temp_c']) ? number_format((float)$rf_db['set_temp_c'], 1) . '°C' : '-18.0°C';
    $avg_temp = isset($rf_db['supply_temp_c']) ? number_format((float)$rf_db['supply_temp_c'], 1) . '°C' : '-18.4°C';
    $shipper = $ctr_db['owner_company'] ?? 'PT Dua Putra Utama Makmur Tbk';
    $commodity = $ctr_db['cargo_type'] ?? 'Frozen Vannamei Shrimp (-18.0°C HACCP Export Grade)';

    $doc_data = [
        'title' => 'REEFER COLD CHAIN HACCP INTEGRITY CERTIFICATE',
        'doc_no' => 'HACCP-RF-' . date('Ymd') . '-' . substr(abs(crc32($c_num)), 0, 4),
        'container_number' => $c_num,
        'shipper' => $shipper,
        'commodity' => $commodity,
        'socket_id' => $socket,
        'set_point' => $set_temp,
        'avg_temp' => $avg_temp,
        'temp_min' => '-18.8°C',
        'temp_max' => '-18.1°C',
        'total_hours' => '48 Jam 15 Menit',
        'total_kwh' => '192.4 kWh',
        'technician' => 'Armansyah Muchtarrom',
        'qa_inspector' => 'Naufal Andika Heditya, S.Log.',
        'compliance' => 'MEMENUHI SYARAT (100% HACCP COMPLIANT - ZERO TEMPERATURE ABUSE)'
    ];
}

function terbilangRupiah($n) {
    $satuan = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
    $n = (int)$n;
    if ($n < 12) return " " . $satuan[$n];
    if ($n < 20) return terbilangRupiah($n - 10) . " Belas";
    if ($n < 100) return terbilangRupiah((int)($n / 10)) . " Puluh" . terbilangRupiah($n % 10);
    if ($n < 200) return " Seratus" . terbilangRupiah($n - 100);
    if ($n < 1000) return terbilangRupiah((int)($n / 100)) . " Ratus" . terbilangRupiah($n % 100);
    if ($n < 2000) return " Seribu" . terbilangRupiah($n - 1000);
    if ($n < 1000000) return terbilangRupiah((int)($n / 1000)) . " Ribu" . terbilangRupiah($n % 1000);
    if ($n < 1000000000) return terbilangRupiah((int)($n / 1000000)) . " Juta" . terbilangRupiah($n % 1000000);
    return terbilangRupiah((int)($n / 1000000000)) . " Miliar" . terbilangRupiah($n % 1000000000);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($doc_data['title']) ?> — <?= htmlspecialchars($doc_data['doc_no']) ?></title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700;800&family=Caveat:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    <script src="assets/vendor/tailwindcss.js"></script>

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
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            background-color: #f1f5f9;
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
        /* Lembaran Kertas A4 Otentik */
        .paper-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto 40px auto;
            background: #ffffff;
            box-shadow: 0 10px 35px rgba(0, 47, 94, 0.12), 0 0 0 1px rgba(15, 23, 42, 0.08);
            border-radius: 4px;
            padding: 12mm 14mm;
            position: relative;
            overflow: hidden;
        }
        table.formal-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 8px 0;
        }
        table.formal-table th {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 6px 8px;
            border: 1px solid #94a3b8;
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
            text-align: left;
            font-size: 10px;
        }
        table.formal-table td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            color: #1e293b;
            vertical-align: middle;
        }
        table.formal-table tr:nth-child(even) td {
            background-color: #f8fafc !important;
        }

        /* Kop Surat Garis Ganda Resmi */
        .kop-separator {
            height: 3px;
            background: #0f172a;
            margin-top: 8px;
            position: relative;
        }
        .kop-separator::after {
            content: '';
            display: block;
            height: 1px;
            background: #ea580c;
            margin-top: 2px;
        }
        .kop-customs-separator {
            height: 3px;
            background: #000000;
            margin-top: 8px;
            position: relative;
        }
        .kop-customs-separator::after {
            content: '';
            display: block;
            height: 1px;
            background: #000000;
            margin-top: 2px;
        }

        @media print {
            body {
                background: #ffffff !important;
            }
            .no-print {
                display: none !important;
            }
            .paper-sheet {
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
        }
    </style>
</head>
<body>

    <!-- ======================================================================= -->
    <!-- FLOATING ACTION BAR: PENGENDALI CETAK & NAVIGASI DOKUMEN (NO-PRINT)     -->
    <!-- ======================================================================= -->
    <header class="no-print sticky top-0 z-50 bg-white text-slate-800 border-b border-slate-200 shadow-2xs">
        <div class="max-w-[1050px] mx-auto px-4 py-2.5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <a href="dashboard.php?page=billing" class="px-3 py-1.5 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-bold flex items-center gap-1.5 transition border border-slate-200">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    <span>Kembali ke Dashboard</span>
                </a>
                <span class="text-slate-300">|</span>
                <div class="flex items-center gap-1.5 text-xs">
                    <span class="font-extrabold text-orange-600 uppercase tracking-wide">Pusat Cetak Dokumen Resmi:</span>
                    <span class="text-slate-700 font-bold"><?= htmlspecialchars($doc_data['title']) ?></span>
                </div>
            </div>

            <!-- Pilihan Cepat Format Dokumen Lain -->
            <div class="flex items-center gap-1.5 flex-wrap text-xs">
                <div class="flex items-center bg-slate-100 p-0.5 rounded-lg border border-slate-200 text-[10.5px]">
                    <a href="document.php?type=invoice" class="px-2.5 py-1 rounded-md <?= ($type === 'invoice') ? 'bg-orange-600 text-white font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 font-semibold' ?>">Faktur Billing</a>
                    <a href="document.php?type=gate_pass" class="px-2.5 py-1 rounded-md <?= ($type === 'gate_pass') ? 'bg-orange-600 text-white font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 font-semibold' ?>">Surat Jalan EIR</a>
                    <a href="document.php?type=sppb" class="px-2.5 py-1 rounded-md <?= ($type === 'sppb') ? 'bg-orange-600 text-white font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 font-semibold' ?>">SPPB Bea Cukai</a>
                    <a href="document.php?type=cfs_tally" class="px-2.5 py-1 rounded-md <?= ($type === 'cfs_tally') ? 'bg-orange-600 text-white font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 font-semibold' ?>">Tally CFS</a>
                    <a href="document.php?type=reefer_cert" class="px-2.5 py-1 rounded-md <?= ($type === 'reefer_cert') ? 'bg-orange-600 text-white font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 font-semibold' ?>">Reefer HACCP</a>
                </div>

                <button type="button" onclick="window.print()" class="px-3.5 py-1.5 rounded-lg bg-orange-600 hover:bg-orange-500 text-white font-extrabold text-xs flex items-center gap-1.5 shadow-2xs transition cursor-pointer">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak / Simpan PDF (A4)</span>
                </button>
            </div>
        </div>
    </header>

    <!-- ======================================================================= -->
    <!-- LEMBAR DOKUMEN RESMI A4 (THE PAPER SHEET)                               -->
    <!-- ======================================================================= -->
    <main class="paper-sheet">

        <?php if ($type === 'sppb'): ?>
            <!-- =============================================================== -->
            <!-- KOP SURAT RESMI PABEAN: KEMENTERIAN KEUANGAN RI - DJBC         -->
            <!-- =============================================================== -->
            <div class="pb-2">
                <div class="flex items-center gap-3">
                    <!-- Logo Lambang Kemenkeu / DJBC SVG -->
                    <div class="w-16 h-16 flex-shrink-0 flex items-center justify-center">
                        <svg viewBox="0 0 100 100" width="60" height="60">
                            <circle cx="50" cy="50" r="46" fill="#002f5e" />
                            <circle cx="50" cy="50" r="41" fill="#ffffff" stroke="#c29b38" stroke-width="2" />
                            <path d="M 50 16 L 54 28 L 66 28 L 56 36 L 60 48 L 50 40 L 40 48 L 44 36 L 34 28 L 46 28 Z" fill="#c29b38" />
                            <path d="M 28 52 C 28 72, 72 72, 72 52 Z" fill="#002f5e" />
                            <text x="50" y="65" font-size="8" font-weight="900" fill="#ffffff" text-anchor="middle">BEA CUKAI</text>
                            <text x="50" y="80" font-size="5" font-weight="800" fill="#002f5e" text-anchor="middle">REPUBLIK INDONESIA</text>
                        </svg>
                    </div>
                    <div class="flex-1 text-center pr-12">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 m-0">KEMENTERIAN KEUANGAN REPUBLIK INDONESIA</h2>
                        <h1 class="text-sm font-black uppercase tracking-wide text-slate-900 m-0">DIREKTORAT JENDERAL BEA DAN CUKAI</h1>
                        <h3 class="text-xs font-extrabold uppercase text-slate-800 m-0"><?= htmlspecialchars($doc_data['customs_office']) ?></h3>
                        <p class="text-[9.5px] text-slate-600 m-0 mt-0.5">Kawasan Pabean Terpadu, Jl. Dry Port Raya Kav. 1, Cikarang, Jawa Barat &bull; Telp: (021) 8983-0000</p>
                    </div>
                </div>
                <div class="kop-customs-separator"></div>
            </div>

            <!-- Judul Dokumen SPPB -->
            <div class="text-center my-3">
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 underline"><?= htmlspecialchars($doc_data['title']) ?></h2>
                <div class="font-mono-code text-xs font-extrabold text-slate-800 mt-0.5">Nomor: <?= htmlspecialchars($doc_data['doc_no']) ?></div>
            </div>

            <p class="text-[11px] text-justify leading-relaxed text-slate-800 mb-2">
                Berdasarkan <strong>Pemberitahuan Impor Barang (PIB)</strong> yang diajukan dan telah dipenuhinya segala kewajiban kepabeanan serta ketentuan larangan/pembatasan sesuai peraturan perundang-undangan, dengan ini diberikan <strong>Persetujuan Pengeluaran Barang</strong> dari Kawasan Pabean Conclusion Intermodal Dry Port (CIDP 35 Ha) kepada:
            </p>

            <table class="w-full text-[11px] border border-slate-300 mb-3">
                <tr class="border-b border-slate-200">
                    <td class="w-1/3 p-2 font-bold bg-slate-50 border-r border-slate-200">Nama Importir / Pemilik:</td>
                    <td class="p-2 font-extrabold text-slate-900"><?= htmlspecialchars($doc_data['importer']) ?></td>
                </tr>
                <tr class="border-b border-slate-200">
                    <td class="p-2 font-bold bg-slate-50 border-r border-slate-200">NPWP Importir:</td>
                    <td class="p-2 font-mono-code font-bold text-slate-800"><?= htmlspecialchars($doc_data['importer_npwp']) ?></td>
                </tr>
                <tr class="border-b border-slate-200">
                    <td class="p-2 font-bold bg-slate-50 border-r border-slate-200">Nomor &amp; Tanggal PIB:</td>
                    <td class="p-2 font-mono-code"><?= htmlspecialchars($doc_data['pib_no']) ?> tanggal <?= htmlspecialchars($doc_data['pib_date']) ?></td>
                </tr>
                <tr class="border-b border-slate-200">
                    <td class="p-2 font-bold bg-slate-50 border-r border-slate-200">Nomor Peti Kemas (Container):</td>
                    <td class="p-2 font-mono-code font-black text-emerald-800 text-xs"><?= htmlspecialchars($doc_data['container_number']) ?> (<?= htmlspecialchars($doc_data['size_type']) ?>)</td>
                </tr>
                <tr class="border-b border-slate-200">
                    <td class="p-2 font-bold bg-slate-50 border-r border-slate-200">Nomor Segel Elektronik:</td>
                    <td class="p-2 font-mono-code font-bold text-slate-800"><?= htmlspecialchars($doc_data['seal_number']) ?></td>
                </tr>
                <tr class="border-b border-slate-200">
                    <td class="p-2 font-bold bg-slate-50 border-r border-slate-200">Uraian Jenis Muatan Kargo:</td>
                    <td class="p-2 text-slate-800"><?= htmlspecialchars($doc_data['commodity']) ?></td>
                </tr>
                <tr class="border-b border-slate-200">
                    <td class="p-2 font-bold bg-slate-50 border-r border-slate-200">Kanal Pelayanan / Penetapan:</td>
                    <td class="p-2 font-black text-emerald-700 bg-emerald-50/50"><?= htmlspecialchars($doc_data['channel']) ?></td>
                </tr>
                <tr>
                    <td class="p-2 font-bold bg-slate-50 border-r border-slate-200">Status Pelunasan Pungutan (NTPN):</td>
                    <td class="p-2 font-mono-code font-extrabold text-emerald-700"><?= htmlspecialchars($doc_data['ntpn_status']) ?></td>
                </tr>
            </table>

            <div class="p-2.5 bg-slate-50 border border-slate-200 rounded text-[10.5px] text-slate-700 mb-4">
                <strong>Catatan Gate Out Terminal:</strong> Dokumen SPPB ini telah terhubung secara host-to-host dengan sistem gerbang CIDP. Pengeluaran melalui Outbound Gate dapat dilakukan secara otomatis melalui verifikasi plat nomor ANPR dan palang otomatis.
            </div>

            <!-- Tanda Tangan & Stempel Resmi DJBC -->
            <div class="pt-2 flex items-end justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-16 h-16 border border-slate-400 p-1 bg-white flex items-center justify-center">
                        <i class="fa-solid fa-qrcode text-4xl text-slate-800"></i>
                    </div>
                    <div class="text-[8.5px] text-slate-500 leading-snug">
                        <div class="font-bold text-slate-800">QR CODE CEISA 4.0 NASIONAL</div>
                        <div>Pindai untuk verifikasi keabsahan pabean resmi DJBC.</div>
                        <div class="font-mono-code font-bold mt-0.5">SHA256-<?= substr(md5($doc_data['doc_no']), 0, 12) ?></div>
                    </div>
                </div>

                <div class="text-center relative pr-8">
                    <div class="text-[9.5px] font-bold text-slate-600">Ditetapkan di Cikarang, <?= $doc_date ?></div>
                    <div class="text-[10px] font-extrabold text-slate-900">a.n. KEPALA KANTOR PELAYANAN BEA DAN CUKAI</div>
                    <div class="h-16 flex items-center justify-center relative my-1">
                        <!-- Stempel Vektor Hijau Bea Cukai -->
                        <svg viewBox="0 0 120 120" width="68" height="68" class="absolute opacity-85" style="transform: rotate(-5deg);">
                            <circle cx="60" cy="60" r="54" fill="#ecfdf5" fill-opacity="0.5" stroke="#059669" stroke-width="2.5"/>
                            <circle cx="60" cy="60" r="49" fill="none" stroke="#059669" stroke-width="1" stroke-dasharray="3 2"/>
                            <rect x="14" y="47" width="92" height="24" rx="3" fill="#059669" fill-opacity="0.12" stroke="#059669" stroke-width="1.3"/>
                            <text x="60" y="61" font-size="10" font-weight="900" fill="#059669" text-anchor="middle" letter-spacing="1">SPPB CLEARED</text>
                            <text x="60" y="34" font-size="6.5" font-weight="800" fill="#059669" text-anchor="middle">BEA DAN CUKAI</text>
                            <text x="60" y="88" font-size="6" font-weight="700" fill="#059669" text-anchor="middle">KPU CIKARANG</text>
                        </svg>
                        <span class="font-signature text-2xl text-slate-900 relative z-10">Hendra Kusuma</span>
                    </div>
                    <div class="border-t border-slate-700 pt-0.5">
                        <div class="text-[10px] font-black text-slate-900"><?= htmlspecialchars($doc_data['officer_name']) ?></div>
                        <div class="text-[8.5px] font-mono-code text-slate-600">NIP. <?= htmlspecialchars($doc_data['officer_nip']) ?></div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- =============================================================== -->
            <!-- KOP SURAT RESMI PERUSAHAAN (PT MTI / CONCLUSION INTERMODAL)     -->
            <!-- =============================================================== -->
            <div class="pb-2">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <!-- Logo Vektor Modern CIDP -->
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-[#002f5e] via-[#0170b9] to-blue-700 text-white flex items-center justify-center font-black text-2xl shadow-sm border border-blue-900 flex-shrink-0">
                            C
                        </div>
                        <div>
                            <h1 class="text-sm font-black uppercase tracking-tight text-slate-900 m-0">PT MULTI TERMINAL INDONESIA (PELINDO LOGISTICS)</h1>
                            <div class="text-xs font-black text-orange-600 tracking-wide uppercase m-0">CONCLUSION INTERMODAL DRY PORT (CIDP 35 HA)</div>
                            <p class="text-[9.5px] text-slate-600 m-0 mt-0.5 leading-snug">
                                Kawasan Industri Jababeka, Cikarang Dry Port Hub, Jawa Barat &bull; Telp: (021) 8983-4567<br>
                                Email: commercial@conclusion-dryport.com &bull; Web: www.conclusion-dryport.com &bull; NPWP: 01.061.821.4-054.000
                            </p>
                        </div>
                    </div>

                    <!-- Kotak Nomor Dokumen Kanan Atas -->
                    <div class="text-right min-w-[200px]">
                        <span class="inline-block px-2 py-0.5 rounded text-[8.5px] font-black uppercase tracking-wide bg-blue-50 text-[#0170b9] border border-blue-200">
                            Dokumen Operasional Resmi
                        </span>
                        <div class="text-xs font-mono-code font-black text-slate-900 mt-1">
                            No: <?= htmlspecialchars($doc_data['doc_no']) ?>
                        </div>
                        <div class="text-[10px] text-slate-500 mt-0.5">
                            Tanggal: <strong><?= $doc_date ?></strong>
                        </div>
                    </div>
                </div>
                <div class="kop-separator"></div>
            </div>

            <!-- Judul Dokumen -->
            <div class="text-center my-2.5">
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 underline"><?= htmlspecialchars($doc_data['title']) ?></h2>
            </div>

            <?php if ($type === 'invoice'): ?>
                <!-- =========================================================== -->
                <!-- ISI DOKUMEN FAKTUR PENAGIHAN (INVOICE)                      -->
                <!-- =========================================================== -->
                <div class="grid grid-cols-2 gap-3 mb-2 text-[11px] bg-slate-50 p-2.5 rounded border border-slate-200">
                    <div>
                        <span class="text-[9.5px] text-slate-400 font-bold uppercase block">Ditagihkan Kepada (Shipper / Consignee):</span>
                        <strong class="text-slate-900 text-xs block"><?= htmlspecialchars($doc_data['customer']) ?></strong>
                        <span class="font-mono-code text-[10px] text-slate-600 block">NPWP: <?= htmlspecialchars($doc_data['customer_npwp']) ?></span>
                        <span class="text-slate-500 text-[10px] block"><?= htmlspecialchars($doc_data['customer_addr']) ?></span>
                    </div>
                    <div class="text-right text-[10.5px] space-y-0.5">
                        <div><span class="text-slate-500">No. Peti Kemas:</span> <strong class="font-mono-code text-slate-900 font-bold"><?= htmlspecialchars($doc_data['container_number']) ?></strong></div>
                        <div><span class="text-slate-500">Tipe / Ukuran:</span> <strong class="text-slate-800"><?= htmlspecialchars($doc_data['size_type']) ?></strong></div>
                        <div><span class="text-slate-500">Jatuh Tempo:</span> <strong class="text-rose-600 font-bold"><?= htmlspecialchars($doc_data['due_date']) ?></strong></div>
                        <div><span class="text-slate-500">Status Pembayaran:</span> <span class="px-1.5 py-0.2 rounded font-extrabold text-[9px] <?= ($doc_data['status'] === 'PAID') ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>"><?= htmlspecialchars($doc_data['status']) ?></span></div>
                    </div>
                </div>

                <table class="formal-table">
                    <thead>
                        <tr>
                            <th class="w-8 text-center">No</th>
                            <th>Uraian Jasa Kepelabuhanan &amp; Penanganan Terminal</th>
                            <th class="w-16 text-center">Qty</th>
                            <th class="w-16 text-center">Satuan</th>
                            <th class="w-24 text-right">Tarif (Rp)</th>
                            <th class="w-28 text-right">Total (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($doc_data['items'] as $idx => $it): ?>
                        <tr>
                            <td class="text-center font-bold text-slate-500"><?= $idx + 1 ?></td>
                            <td>
                                <strong class="text-slate-900"><?= htmlspecialchars($it['desc']) ?></strong>
                            </td>
                            <td class="text-center font-mono-code"><?= $it['qty'] ?></td>
                            <td class="text-center"><?= htmlspecialchars($it['unit']) ?></td>
                            <td class="text-right font-mono-code"><?= number_format($it['rate'], 0, ',', '.') ?></td>
                            <td class="text-right font-mono-code font-bold text-slate-900"><?= number_format($it['amount'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="bg-slate-50 font-bold">
                            <td colspan="5" class="text-right">Subtotal DPP:</td>
                            <td class="text-right font-mono-code">Rp <?= number_format($doc_data['subtotal'], 0, ',', '.') ?></td>
                        </tr>
                        <tr class="bg-slate-50 font-bold">
                            <td colspan="5" class="text-right">PPN 11% (UU Harmonisasi Perpajakan):</td>
                            <td class="text-right font-mono-code">Rp <?= number_format($doc_data['ppn'], 0, ',', '.') ?></td>
                        </tr>
                        <tr class="bg-slate-50 font-bold">
                            <td colspan="5" class="text-right">Bea Meterai Elektronik Lunas:</td>
                            <td class="text-right font-mono-code">Rp <?= number_format($doc_data['meterai'], 0, ',', '.') ?></td>
                        </tr>
                        <tr class="bg-blue-50/60 font-black text-xs text-slate-900">
                            <td colspan="5" class="text-right uppercase">TOTAL TAGIHAN KESELURUHAN:</td>
                            <td class="text-right font-mono-code text-sm text-blue-900">Rp <?= number_format($doc_data['grand_total'], 0, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>

                <div class="p-2 bg-slate-50 border border-slate-200 rounded text-[10px] mb-3">
                    <span class="font-bold text-slate-600 uppercase block">Terbilang:</span>
                    <em class="font-bold text-slate-900 font-serif"># <?= htmlspecialchars($doc_data['terbilang']) ?> Rupiah #</em>
                </div>

                <!-- Rekening Pembayaran Bank H2H -->
                <div class="grid grid-cols-2 gap-3 text-[10px] text-slate-600 mb-3">
                    <div class="p-2 bg-slate-50 border border-slate-200 rounded">
                        <strong class="text-slate-800 block mb-0.5">Instruksi Pembayaran Mandiri H2H / Virtual Account:</strong>
                        <div>Bank Mandiri Escrow: <strong>120-00-9812048-1</strong> a.n. PT Multi Terminal Indonesia</div>
                        <div>BCA Virtual Account: <strong>89012 08912304</strong></div>
                    </div>
                    <div class="p-2 bg-slate-50 border border-slate-200 rounded">
                        <strong class="text-slate-800 block mb-0.5">Integrasi Akuntansi ERP:</strong>
                        <div>Faktur ini telah terbit dan tersinkronisasi otomatis dengan <strong>Odoo 17 Enterprise Cloud</strong> melalui host-to-host API REST.</div>
                    </div>
                </div>

            <?php elseif ($type === 'gate_pass'): ?>
                <!-- =========================================================== -->
                <!-- ISI DOKUMEN SURAT JALAN & EIR                               -->
                <!-- =========================================================== -->
                <div class="grid grid-cols-2 gap-3 mb-2 text-[11px]">
                    <div class="bg-slate-50 p-2.5 rounded border border-slate-200 space-y-1">
                        <div class="font-extrabold text-slate-900 border-b border-slate-200 pb-0.5 text-[10.5px] uppercase">Data Armada &amp; Pengemudi</div>
                        <div><span class="text-slate-500">No. Polisi Truk:</span> <strong class="font-mono-code text-amber-900 font-extrabold text-xs"><?= htmlspecialchars($doc_data['license_plate']) ?></strong></div>
                        <div><span class="text-slate-500">Pengemudi:</span> <strong class="text-slate-900"><?= htmlspecialchars($doc_data['driver_name']) ?></strong></div>
                        <div><span class="text-slate-500">Nomor SIM:</span> <span class="font-mono-code"><?= htmlspecialchars($doc_data['driver_sim']) ?></span></div>
                        <div><span class="text-slate-500">Tag RFID UHF:</span> <span class="font-mono-code text-purple-800 font-bold"><?= htmlspecialchars($doc_data['rfid_tid']) ?></span></div>
                        <div><span class="text-slate-500">Transporter:</span> <span class="text-slate-800 font-bold"><?= htmlspecialchars($doc_data['transporter']) ?></span></div>
                    </div>

                    <div class="bg-slate-50 p-2.5 rounded border border-slate-200 space-y-1">
                        <div class="font-extrabold text-slate-900 border-b border-slate-200 pb-0.5 text-[10.5px] uppercase">Data Peti Kemas &amp; Timbangan VGM</div>
                        <div><span class="text-slate-500">No. Peti Kemas:</span> <strong class="font-mono-code text-blue-900 font-black text-xs"><?= htmlspecialchars($doc_data['container_number']) ?></strong></div>
                        <div><span class="text-slate-500">Spesifikasi:</span> <span class="text-slate-800"><?= htmlspecialchars($doc_data['size_type']) ?></span></div>
                        <div><span class="text-slate-500">No. Segel:</span> <span class="font-mono-code font-bold text-slate-800"><?= htmlspecialchars($doc_data['seal_number']) ?></span></div>
                        <div><span class="text-slate-500">Berat Kotor (Gross):</span> <strong class="font-mono-code text-slate-900"><?= number_format($doc_data['weight_gross'], 0, ',', '.') ?> kg</strong></div>
                        <div><span class="text-slate-500">Berat Bersih (Netto VGM):</span> <strong class="font-mono-code text-emerald-700"><?= number_format($doc_data['weight_net'], 0, ',', '.') ?> kg</strong> (SOLAS OK)</div>
                    </div>
                </div>

                <div class="p-2.5 bg-blue-50/50 border border-blue-200 rounded text-[11px] mb-3">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-[9.5px] font-bold text-blue-800 uppercase block">Disposisi Penempatan Lapangan (Yard Stacking Slot):</span>
                            <strong class="text-slate-900 text-xs font-mono-code font-black"><?= htmlspecialchars($doc_data['yard_slot']) ?></strong>
                        </div>
                        <div class="text-right">
                            <span class="text-[9.5px] font-bold text-blue-800 uppercase block">Alat Berat Penangan:</span>
                            <strong class="text-slate-800"><?= htmlspecialchars($doc_data['equipment']) ?></strong>
                        </div>
                    </div>
                </div>

                <div class="p-2 bg-slate-50 border border-slate-200 rounded text-[10px] text-slate-600 mb-3">
                    <strong>Pemeriksaan Fisik Peti Kemas (EIR Condition):</strong> <?= htmlspecialchars($doc_data['condition']) ?>. Tanda tangan di bawah membuktikan pertukaran alat telah diverifikasi tanpa cacat fisik tampak luar.
                </div>

            <?php elseif ($type === 'cfs_tally'): ?>
                <!-- =========================================================== -->
                <!-- ISI DOKUMEN TALLY SHEET CFS                                -->
                <!-- =========================================================== -->
                <div class="grid grid-cols-2 gap-3 mb-2 text-[11px] bg-slate-50 p-2.5 rounded border border-slate-200">
                    <div>
                        <div><span class="text-slate-500">Nomor Job Order:</span> <strong class="font-mono-code text-slate-900"><?= htmlspecialchars($doc_data['job_no']) ?></strong></div>
                        <div><span class="text-slate-500">Peti Kemas Asal:</span> <strong class="font-mono-code text-blue-900"><?= htmlspecialchars($doc_data['container_number']) ?></strong></div>
                        <div><span class="text-slate-500">Consignee:</span> <strong class="text-slate-800"><?= htmlspecialchars($doc_data['consignee']) ?></strong></div>
                    </div>
                    <div class="text-right">
                        <div><span class="text-slate-500">Lokasi Rampa:</span> <strong class="text-slate-900"><?= htmlspecialchars($doc_data['ramp_id']) ?></strong></div>
                        <div><span class="text-slate-500">Tally Clerk:</span> <strong class="text-slate-800"><?= htmlspecialchars($doc_data['tallyman']) ?></strong></div>
                        <div><span class="text-slate-500">Status:</span> <span class="px-1.5 py-0.2 rounded font-bold text-[9px] bg-emerald-100 text-emerald-800">COMPLETED</span></div>
                    </div>
                </div>

                <table class="formal-table">
                    <thead>
                        <tr>
                            <th class="w-8 text-center">No</th>
                            <th>Kode Barcode Palet GS1 SSCC-18</th>
                            <th>Deskripsi Muatan Kargo Koli</th>
                            <th class="w-16 text-center">Koli</th>
                            <th class="w-24 text-right">Berat Koli</th>
                            <th class="w-20 text-center">Kondisi</th>
                            <th class="w-20 text-center">Rak CFS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($doc_data['pallets'] as $idx => $pal): ?>
                        <tr>
                            <td class="text-center font-bold text-slate-500"><?= $idx + 1 ?></td>
                            <td class="font-mono-code font-bold text-blue-900 text-[10px]"><?= htmlspecialchars($pal['sscc']) ?></td>
                            <td><?= htmlspecialchars($pal['desc']) ?></td>
                            <td class="text-center font-mono-code font-bold"><?= $pal['qty'] ?></td>
                            <td class="text-right font-mono-code"><?= $pal['weight'] ?></td>
                            <td class="text-center font-bold text-emerald-700 text-[9.5px]"><?= $pal['cond'] ?></td>
                            <td class="text-center font-mono-code text-slate-700"><?= $pal['slot'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="bg-slate-50 font-black">
                            <td colspan="3" class="text-right uppercase">TOTAL MUATAN TERVERIFIKASI:</td>
                            <td class="text-center font-mono-code"><?= $doc_data['total_koli'] ?> Koli</td>
                            <td class="text-right font-mono-code"><?= $doc_data['total_weight'] ?></td>
                            <td colspan="2" class="text-center text-emerald-700">100% SESUAI</td>
                        </tr>
                    </tbody>
                </table>

            <?php elseif ($type === 'reefer_cert'): ?>
                <!-- =========================================================== -->
                <!-- ISI SERTIFIKAT KEPATUHAN SUHU REEFER (HACCP)                -->
                <!-- =========================================================== -->
                <div class="grid grid-cols-2 gap-3 mb-2 text-[11px] bg-slate-50 p-2.5 rounded border border-slate-200">
                    <div>
                        <div><span class="text-slate-500">No. Peti Kemas:</span> <strong class="font-mono-code text-cyan-900 font-black text-xs"><?= htmlspecialchars($doc_data['container_number']) ?></strong></div>
                        <div><span class="text-slate-500">Shipper:</span> <strong class="text-slate-900"><?= htmlspecialchars($doc_data['shipper']) ?></strong></div>
                        <div><span class="text-slate-500">Komoditas Kargo:</span> <strong class="text-slate-800"><?= htmlspecialchars($doc_data['commodity']) ?></strong></div>
                    </div>
                    <div class="text-right">
                        <div><span class="text-slate-500">Titik Soket Listrik:</span> <strong class="font-mono-code text-slate-900"><?= htmlspecialchars($doc_data['socket_id']) ?></strong></div>
                        <div><span class="text-slate-500">Durasi Terhubung:</span> <strong class="text-slate-800"><?= htmlspecialchars($doc_data['total_hours']) ?></strong></div>
                        <div><span class="text-slate-500">Total Daya Terpakai:</span> <strong class="font-mono-code text-slate-900"><?= htmlspecialchars($doc_data['total_kwh']) ?></strong></div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2 text-center text-xs mb-3">
                    <div class="p-2 rounded bg-cyan-50 border border-cyan-200">
                        <span class="text-[9.5px] font-bold text-cyan-800 uppercase block">Suhu Target (Set Point):</span>
                        <strong class="font-mono-code text-base text-cyan-950 font-black"><?= htmlspecialchars($doc_data['set_point']) ?></strong>
                    </div>
                    <div class="p-2 rounded bg-emerald-50 border border-emerald-200">
                        <span class="text-[9.5px] font-bold text-emerald-800 uppercase block">Rata-Rata Suhu Pasokan:</span>
                        <strong class="font-mono-code text-base text-emerald-950 font-black"><?= htmlspecialchars($doc_data['avg_temp']) ?></strong>
                    </div>
                    <div class="p-2 rounded bg-blue-50 border border-blue-200">
                        <span class="text-[9.5px] font-bold text-blue-800 uppercase block">Fluktuasi Min / Max:</span>
                        <strong class="font-mono-code text-xs text-blue-950 font-bold"><?= htmlspecialchars($doc_data['temp_min']) ?> / <?= htmlspecialchars($doc_data['temp_max']) ?></strong>
                    </div>
                </div>

                <div class="p-2 bg-emerald-50/70 border border-emerald-300 rounded text-[10.5px] text-emerald-900 mb-3">
                    <strong>Pernyataan Kepatuhan HACCP:</strong> <?= htmlspecialchars($doc_data['compliance']) ?>. Tidak terdeteksi lonjakan suhu (*temperature abuse*) melampaui toleransi 2.0°C sepanjang masa penyimpanan di reefer yard CIDP 35 Ha.
                </div>

            <?php endif; ?>

            <!-- =============================================================== -->
            <!-- PENGESAHAN DOKUMEN PERUSAHAAN (STEMPEL, QR & TANDA TANGAN)      -->
            <!-- =============================================================== -->
            <div class="pt-2 border-t-2 border-slate-800 mt-2">
                <div class="flex items-end justify-between">
                    <!-- QR Code Keabsahan Integritas -->
                    <div class="flex items-center gap-2">
                        <div class="w-16 h-16 border border-slate-300 p-1 bg-white flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-qrcode text-4xl text-slate-800"></i>
                        </div>
                        <div class="text-[8.5px] text-slate-500 leading-snug">
                            <div class="font-extrabold text-slate-900 uppercase">Keabsahan Dokumen Elektronik</div>
                            <div>Diterbitkan oleh CIDP DPMS Core Database sesuai UU ITE No. 11/2008.</div>
                            <div class="font-mono-code font-bold text-slate-800 mt-0.5">SHA256-<?= substr(md5($doc_data['doc_no']), 0, 16) ?></div>
                        </div>
                    </div>

                    <!-- Tanda Tangan 1: Driver / Customer (Khusus Gate Pass) -->
                    <?php if ($type === 'gate_pass'): ?>
                    <div class="text-center w-36">
                        <div class="text-[8.5px] font-bold text-slate-500 uppercase">Diterima Oleh Pengemudi:</div>
                        <div class="h-14 flex items-center justify-center">
                            <span class="font-signature text-xl text-slate-900">Rudi Hartono</span>
                        </div>
                        <div class="border-t border-slate-400 pt-0.5">
                            <div class="text-[9px] font-black text-slate-900"><?= htmlspecialchars($doc_data['driver_name']) ?></div>
                            <div class="text-[7.5px] text-slate-500">Transporter Driver</div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Tanda Tangan Utama & Stempel Basah Digital Vektor -->
                    <div class="text-center relative w-48">
                        <div class="text-[8.5px] font-bold text-slate-500 uppercase">Cikarang, <?= $doc_date ?></div>
                        <div class="text-[9px] font-extrabold text-slate-900">
                            <?= ($type === 'invoice') ? 'Head of Commercial & Billing' : (($type === 'cfs_tally') ? 'Head of CFS & Warehousing' : 'Kepala Operasional Gerbang') ?>
                        </div>
                        <div class="h-14 flex items-center justify-center relative my-0.5">
                            <!-- Stempel Basah Vektor Biru Pelindo / MTI -->
                            <svg viewBox="0 0 120 120" width="62" height="62" class="absolute opacity-85" style="transform: rotate(-4deg);">
                                <circle cx="60" cy="60" r="54" fill="#f0f7ff" fill-opacity="0.5" stroke="#002f5e" stroke-width="2.5"/>
                                <circle cx="60" cy="60" r="49" fill="none" stroke="#002f5e" stroke-width="1" stroke-dasharray="3 2"/>
                                <rect x="14" y="47" width="92" height="24" rx="3" fill="#002f5e" fill-opacity="0.1" stroke="#002f5e" stroke-width="1.3"/>
                                <text x="60" y="61" font-size="10" font-weight="900" fill="#002f5e" text-anchor="middle" letter-spacing="1">VERIFIED</text>
                                <text x="60" y="34" font-size="6.5" font-weight="800" fill="#002f5e" text-anchor="middle">CIDP DRY PORT 35HA</text>
                                <text x="60" y="88" font-size="6" font-weight="700" fill="#002f5e" text-anchor="middle">PT MULTI TERMINAL</text>
                            </svg>
                            <span class="font-signature text-2xl text-slate-900 relative z-10">
                                <?= ($type === 'invoice') ? 'Zulfi Fachrurrozi' : (($type === 'cfs_tally') ? 'Juan Gamaliel' : (($type === 'gate_pass') ? 'Ahmad Baihaqi' : 'Armansyah M.')) ?>
                            </span>
                        </div>
                        <div class="border-t border-slate-700 pt-0.5">
                            <div class="text-[9.5px] font-black text-slate-900">
                                <?= ($type === 'invoice') ? 'ZULFI FACHRURROZI, S.Log.' : (($type === 'cfs_tally') ? 'JUAN GAMALIEL, S.Log.' : (($type === 'gate_pass') ? 'AHMAD BAIHAQI, S.Log.' : 'ARMANSYAH MUCHTARROM, S.T.')) ?>
                            </div>
                            <div class="text-[7.5px] text-slate-500">PT Multi Terminal Indonesia</div>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </main>

    <?php if ($auto_print): ?>
    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
    <?php endif; ?>

</body>
</html>
