<?php
// =============================================================================
// MODUL: KEPABEANAN & BEA CUKAI — INTEGRASI SISTEM CEISA 4.0 DJBC
// File: pages/customs.php
// Proyek: Conclusion Intermodal Dry Port (CIDP) 35 Ha — Yard Management System
// PIC : Naufal Andika Heditya & Tim Kepabeanan CIDP
// Role: Business Analyst & QA Specialist / Customs Compliance Officer
// =============================================================================

$customs_info = [
    'pic'    => 'Naufal Andika Heditya & Tim Kepabeanan CIDP',
    'role'   => 'Business Analyst & QA Specialist / Customs Compliance Officer',
    'desc'   => 'Otomatisasi rilis SPPB CEISA 4.0 DJBC, penetapan jalur pabean, inspeksi behandle & Gantry X-Ray 6 MeV, serta monitoring E-Seal transit.',
    'icon'   => 'fa-shield-halved',
    'status' => 'CEISA 4.0 Connected — REST API v1.4 & EDI Engine Aktif'
];

require_once __DIR__ . '/../connection.php';

// Handle Form Aksi POST (Rilis SPPB Jalur Hijau / Tahan Jalur Merah Behandle)
$customs_alert = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['customs_action'])) {
    $c_action = $_POST['customs_action'];
    $ctr_num  = strtoupper(trim($_POST['container_number'] ?? ''));
    $officer  = trim($_POST['officer_name'] ?? 'Hanggar DJBC CIDP');
    $notes    = trim($_POST['customs_notes'] ?? '');

    if (!empty($ctr_num) && isset($pdo)) {
        try {
            if ($c_action === 'release_sppb') {
                $sppb_code = 'SPPB-' . rand(100000, 999999) . '/KPU.01/' . date('Y');
                $stmtUp = $pdo->prepare("UPDATE containers SET customs_status = 'SPPB_CLEARED' WHERE container_number = ?");
                $stmtUp->execute([$ctr_num]);

                $stmtEv = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, operator_name, notes, created_at) VALUES ('CUSTOMS_SPPB_RELEASE', ?, ?, ?, NOW())");
                $stmtEv->execute([$ctr_num, $officer, "Penerbitan $sppb_code CEISA 4.0 Jalur Hijau. Kargo dinyatakan cleared dan siap untuk pengeluaran di Gate Out."]);

                $customs_alert = [
                    'type' => 'success',
                    'title' => 'SPPB Berhasil Diterbitkan (Jalur Hijau CEISA 4.0)',
                    'msg' => "Peti kemas <strong>$ctr_num</strong> telah memperoleh status <strong>SPPB_CLEARED</strong> ($sppb_code). Pengeluaran gerbang di Gate Out kini telah diizinkan."
                ];
            } elseif ($c_action === 'hold_red_lane') {
                $stmtUp = $pdo->prepare("UPDATE containers SET customs_status = 'RED_LANE', block = 'E' WHERE container_number = ?");
                $stmtUp->execute([$ctr_num]);

                $stmtEv = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, to_block, operator_name, notes, created_at) VALUES ('CUSTOMS_RED_LANE_HOLD', ?, 'E', ?, ?, NOW())");
                $stmtEv->execute([$ctr_num, $officer, "Penetapan Jalur Merah & Instruksi Pemeriksaan Fisik Terminal Behandle Blok E / X-Ray 6 MeV. Catatan: $notes"]);

                $customs_alert = [
                    'type' => 'warning',
                    'title' => 'Kontainer Ditetapkan Jalur Merah (Pemeriksaan Fisik Behandle)',
                    'msg' => "Peti kemas <strong>$ctr_num</strong> dialokasikan ke <strong>Blok E (Terminal Behandle & Pemindai X-Ray)</strong>. Palang gerbang Gate Out otomatis terkunci hingga pemeriksaan selesai."
                ];
            }
        } catch (Exception $e) {
            $customs_alert = ['type' => 'info', 'title' => 'Pembaruan Pabean Tersimpan', 'msg' => "Status pabean untuk $ctr_num telah diperbarui."];
        }
    }
}

// Data Awal 12 Dokumen Kepabeanan CIDP (PIB, PEB, BC 2.3 TPB)
$customs_docs = [
    [
        'id'            => 1,
        'no_aju'        => '000020-001234-20260929-000101',
        'no_daftar'     => '042819/KPU.01/2026',
        'tgl_daftar'    => '2026-09-29 08:15',
        'jenis_dok'     => 'PIB (BC 2.0)',
        'importir'      => 'PT Unilever Indonesia Tbk',
        'npwp'          => '01.000.015.8-054.000',
        'kategori_mitra'=> 'AEO (Authorized Economic Operator)',
        'no_kontainer'  => 'MSKU7829104',
        'ukuran'        => "40' HC",
        'tipe_box'      => 'Dry High Cube',
        'hs_code'       => '3401.11.90',
        'uraian_barang' => 'Bahan Baku Sabun & Personal Care Products',
        'negara_asal'   => 'Singapura (SGSIN)',
        'cif_usd'       => 84500.00,
        'cif_idr'       => 1310250000,
        'bea_masuk'     => 65512500,
        'ppn'           => 144127500,
        'pph'           => 32756250,
        'total_pungutan'=> 242396250,
        'ntpn'          => '9A8B7C6D5E4F3A2B',
        'jalur'         => 'HIJAU',
        'status'        => 'SPPB_TERBIT',
        'alasan_jalur'  => 'Mitra AEO Terakreditasi, Komoditas Non-Lartas, Nilai Transaksi Wajar',
        'no_sppb'       => 'SPPB-042819/KPU.01/2026',
        'tgl_sppb'      => '2026-09-29 08:16 WIB',
        'petugas_pfpb'  => 'Sistem Otomatis CEISA 4.0',
        'e_seal_id'     => 'JT701-ID-8821',
        'e_seal_status' => 'UNLOCKED',
        'lokasi_yard'   => 'Blok A-03-02-1',
        'scan_xray'     => 'TIDAK_PERLU',
        'behandle_bay'  => null
    ],
    [
        'id'            => 2,
        'no_aju'        => '000020-001234-20260929-000102',
        'no_daftar'     => '042820/KPU.01/2026',
        'tgl_daftar'    => '2026-09-29 08:30',
        'jenis_dok'     => 'PIB (BC 2.0)',
        'importir'      => 'PT Astra Honda Motor',
        'npwp'          => '01.000.123.4-092.000',
        'kategori_mitra'=> 'MITA Pabean (Mitra Utama)',
        'no_kontainer'  => 'TGHU9021845',
        'ukuran'        => "40' HC",
        'tipe_box'      => 'Dry High Cube',
        'hs_code'       => '8714.10.90',
        'uraian_barang' => 'Komponen Suku Cadang Mesin Sepeda Motor (CKD Kits)',
        'negara_asal'   => 'Jepang (JPYOK)',
        'cif_usd'       => 142000.00,
        'cif_idr'       => 2201000000,
        'bea_masuk'     => 110050000,
        'ppn'           => 242110000,
        'pph'           => 55025000,
        'total_pungutan'=> 407185000,
        'ntpn'          => '7D8E9F1A2B3C4D5E',
        'jalur'         => 'HIJAU',
        'status'        => 'SPPB_TERBIT',
        'alasan_jalur'  => 'Status MITA Pabean Aktif, Nilai CIF Terverifikasi Otomatis',
        'no_sppb'       => 'SPPB-042820/KPU.01/2026',
        'tgl_sppb'      => '2026-09-29 08:31 WIB',
        'petugas_pfpb'  => 'Sistem Otomatis CEISA 4.0',
        'e_seal_id'     => 'JT701-ID-8822',
        'e_seal_status' => 'UNLOCKED',
        'lokasi_yard'   => 'Blok A-04-01-2',
        'scan_xray'     => 'TIDAK_PERLU',
        'behandle_bay'  => null
    ],
    [
        'id'            => 3,
        'no_aju'        => '000020-001234-20260929-000103',
        'no_daftar'     => '042821/KPU.01/2026',
        'tgl_daftar'    => '2026-09-29 09:10',
        'jenis_dok'     => 'BC 2.3 (Kawasan Berikat)',
        'importir'      => 'PT LG Electronics Indonesia',
        'npwp'          => '01.000.456.7-043.000',
        'kategori_mitra'=> 'AEO (Authorized Economic Operator)',
        'no_kontainer'  => 'CMAU3491028',
        'ukuran'        => "40' HC",
        'tipe_box'      => 'Dry High Cube',
        'hs_code'       => '8529.90.94',
        'uraian_barang' => 'Panel Display OLED & IC Microcontroller TV',
        'negara_asal'   => 'Korea Selatan (KRPUS)',
        'cif_usd'       => 210500.00,
        'cif_idr'       => 3262750000,
        'bea_masuk'     => 0, // Fasilitas TPB Ditangguhkan
        'ppn'           => 0, // Ditangguhkan
        'pph'           => 0, // Ditangguhkan
        'total_pungutan'=> 0,
        'ntpn'          => 'TPB-FASILITAS-BERIKAT',
        'jalur'         => 'HIJAU',
        'status'        => 'SPPB_TERBIT',
        'alasan_jalur'  => 'Fasilitas Kawasan Berikat AEO, Pengeluaran ke Gudang TPB',
        'no_sppb'       => 'SPPB-042821/KPU.01/2026',
        'tgl_sppb'      => '2026-09-29 09:12 WIB',
        'petugas_pfpb'  => 'Sistem Otomatis CEISA 4.0',
        'e_seal_id'     => 'JT701-ID-8823',
        'e_seal_status' => 'UNLOCKED',
        'lokasi_yard'   => 'Blok B-02-03-1',
        'scan_xray'     => 'TIDAK_PERLU',
        'behandle_bay'  => null
    ],
    [
        'id'            => 4,
        'no_aju'        => '000020-001234-20260929-000104',
        'no_daftar'     => '042822/KPU.01/2026',
        'tgl_daftar'    => '2026-09-29 09:45',
        'jenis_dok'     => 'PIB (BC 2.0)',
        'importir'      => 'PT Schneider Electric Manufacturing',
        'npwp'          => '01.000.789.0-021.000',
        'kategori_mitra'=> 'Importir Produsen (IP)',
        'no_kontainer'  => 'HLBU1238901',
        'ukuran'        => "20' GP",
        'tipe_box'      => 'Dry Standard',
        'hs_code'       => '8536.20.90',
        'uraian_barang' => 'Circuit Breaker Industri & Relay Tegangan Rendah',
        'negara_asal'   => 'Prancis (FRLEH)',
        'cif_usd'       => 58200.00,
        'cif_idr'       => 902100000,
        'bea_masuk'     => 45105000,
        'ppn'           => 99231000,
        'pph'           => 22552500,
        'total_pungutan'=> 166888500,
        'ntpn'          => '5B6C7D8E9F1A2B3C',
        'jalur'         => 'HIJAU',
        'status'        => 'SPPB_TERBIT',
        'alasan_jalur'  => 'Profil Importir Produsen (IP) Patuh - Rilis Otomatis SPPB (PMK 190/PMK.04/2022)',
        'no_sppb'       => 'SPPB-042822/KPU.01/2026',
        'tgl_sppb'      => '2026-09-29 09:42',
        'petugas_pfpb'  => 'Sistem Otomatis CEISA 4.0 DJBC',
        'e_seal_id'     => 'JT701-ID-8824',
        'e_seal_status' => 'UNLOCKED',
        'lokasi_yard'   => 'Blok C-01-02-3',
        'scan_xray'     => 'TIDAK_PERLU',
        'behandle_bay'  => null
    ],
    [
        'id'            => 5,
        'no_aju'        => '000020-001234-20260929-000105',
        'no_daftar'     => '042823/KPU.01/2026',
        'tgl_daftar'    => '2026-09-29 10:00',
        'jenis_dok'     => 'PIB (BC 2.0)',
        'importir'      => 'PT Global Chemindo Pratama',
        'npwp'          => '01.234.567.8-012.000',
        'kategori_mitra'=> 'Importir Umum (IU) - High Risk',
        'no_kontainer'  => 'EITU9823102',
        'ukuran'        => "20' ISO Tank",
        'tipe_box'      => 'Dangerous Goods (DG)',
        'hs_code'       => '2905.11.00',
        'uraian_barang' => 'Methanol Cair Kemurnian Industri 99.85% (B3 / Hazmat)',
        'negara_asal'   => 'Tiongkok (CNQIN)',
        'cif_usd'       => 46000.00,
        'cif_idr'       => 713000000,
        'bea_masuk'     => 35650000,
        'ppn'           => 78430000,
        'pph'           => 17825000,
        'total_pungutan'=> 131905000,
        'ntpn'          => '3C4D5E6F7A8B9C1D',
        'jalur'         => 'MERAH',
        'status'        => 'SPJM_TERBIT',
        'alasan_jalur'  => 'Komoditas Bahan Berbahaya & Beracun (B3), Rekomendasi KLHK, Wajib X-Ray 6 MeV & Uji Lab Pabean',
        'no_sppb'       => '-',
        'tgl_sppb'      => '-',
        'petugas_pfpb'  => 'Rizky Firmansyah, S.T. (PFPB Pemeriksa Madya)',
        'e_seal_id'     => 'JT701-ID-8825',
        'e_seal_status' => 'LOCKED',
        'lokasi_yard'   => 'Blok E (Hazmat DG)',
        'scan_xray'     => 'MENUNGGU_SCAN',
        'behandle_bay'  => 'Bay 01 (Kanopi Hazmat)'
    ],
    [
        'id'            => 6,
        'no_aju'        => '000020-001234-20260929-000106',
        'no_daftar'     => '042824/KPU.01/2026',
        'tgl_daftar'    => '2026-09-29 10:20',
        'jenis_dok'     => 'PIB (BC 2.0)',
        'importir'      => 'PT Tekstil Nusantara Abadi',
        'npwp'          => '01.345.678.9-034.000',
        'kategori_mitra'=> 'Importir Umum (IU) - Medium Risk',
        'no_kontainer'  => 'TEMU4561230',
        'ukuran'        => "40' HC",
        'tipe_box'      => 'Dry High Cube',
        'hs_code'       => '5407.52.00',
        'uraian_barang' => 'Kain Tenun Serat Poliester Sintetis Bermotif Cetak',
        'negara_asal'   => 'Tiongkok (CNSHA)',
        'cif_usd'       => 68900.00,
        'cif_idr'       => 1067950000,
        'bea_masuk'     => 106795000, // 10%
        'ppn'           => 117474500,
        'pph'           => 26698750,
        'total_pungutan'=> 250968250,
        'ntpn'          => '2E3F4A5B6C7D8E9F',
        'jalur'         => 'MERAH',
        'status'        => 'DALAM_BEHANDLE',
        'alasan_jalur'  => 'Pemberlakuan Regulasi Lartas Permendag Tekstil, Wajib Stripping Fisik 30% & Verifikasi Surveyor',
        'no_sppb'       => '-',
        'tgl_sppb'      => '-',
        'petugas_pfpb'  => 'Arief Hidayat, S.Sos. (Pemeriksa Bea Cukai)',
        'e_seal_id'     => 'JT701-ID-8826',
        'e_seal_status' => 'IN_TRANSIT_UNLOCKED',
        'lokasi_yard'   => 'Zona 5 (Behandle)',
        'scan_xray'     => 'XRAY_COMPLETED',
        'behandle_bay'  => 'Bay 02 (Dry Inspection)'
    ],
    [
        'id'            => 7,
        'no_aju'        => '000020-001234-20260929-000107',
        'no_daftar'     => '042825/KPU.01/2026',
        'tgl_daftar'    => '2026-09-29 10:45',
        'jenis_dok'     => 'PIB (BC 2.0)',
        'importir'      => 'PT Sumber Pangan Berkah',
        'npwp'          => '01.456.789.0-056.000',
        'kategori_mitra'=> 'Importir Khusus Cold Chain',
        'no_kontainer'  => 'SUDU5678901',
        'ukuran'        => "40' Reefer",
        'tipe_box'      => 'Reefer Container (-20°C)',
        'hs_code'       => '0202.30.00',
        'uraian_barang' => 'Daging Sapi Beku Tanpa Tulang (Frozen Boneless Beef)',
        'negara_asal'   => 'Australia (AUBNE)',
        'cif_usd'       => 115000.00,
        'cif_idr'       => 1782500000,
        'bea_masuk'     => 89125000,
        'ppn'           => 196075000,
        'pph'           => 44562500,
        'total_pungutan'=> 329762500,
        'ntpn'          => '8A9B1C2D3E4F5A6B',
        'jalur'         => 'MERAH',
        'status'        => 'DALAM_BEHANDLE',
        'alasan_jalur'  => 'Komoditas Pangan Segar, Wajib Karantina Pertanian (KHIT) & Pemeriksaan Rantai Dingin di Zona Behandle',
        'no_sppb'       => '-',
        'tgl_sppb'      => '-',
        'petugas_pfpb'  => 'drh. Siti Rahmawati (Karantina) & Wahyu P. (BC)',
        'e_seal_id'     => 'JT701-ID-8827',
        'e_seal_status' => 'IN_TRANSIT_UNLOCKED',
        'lokasi_yard'   => 'Zona 5 (Reefer Behandle)',
        'scan_xray'     => 'XRAY_COMPLETED',
        'behandle_bay'  => 'Bay 03 (Reefer Plug Inspection)'
    ],
    [
        'id'            => 8,
        'no_aju'        => '000020-001234-20260929-000108',
        'no_daftar'     => '042826/KPU.01/2026',
        'tgl_daftar'    => '2026-09-29 11:15',
        'jenis_dok'     => 'PEB (BC 3.0)',
        'importir'      => 'PT Mayora Indah Tbk',
        'npwp'          => '01.001.234.5-018.000',
        'kategori_mitra'=> 'Eksportir AEO',
        'no_kontainer'  => 'ONEU7890123',
        'ukuran'        => "40' HC",
        'tipe_box'      => 'Dry High Cube',
        'hs_code'       => '1905.31.20',
        'uraian_barang' => 'Biskuit Kopi & Wafer Cokelat (Ekspor ke ASEAN)',
        'negara_asal'   => 'Indonesia (IDCID)',
        'cif_usd'       => 42000.00,
        'cif_idr'       => 651000000,
        'bea_masuk'     => 0, // Ekspor Bebas Bea Keluar
        'ppn'           => 0, // PPN 0% Ekspor
        'pph'           => 0,
        'total_pungutan'=> 0,
        'ntpn'          => 'NPE-EKSPOR-042826',
        'jalur'         => 'HIJAU',
        'status'        => 'SPPB_TERBIT', // NPE Terbit
        'alasan_jalur'  => 'Nota Pelayanan Ekspor (NPE) Otomatis Terbit untuk Mitra AEO',
        'no_sppb'       => 'NPE-042826/KPU.01/2026',
        'tgl_sppb'      => '2026-09-29 11:16 WIB',
        'petugas_pfpb'  => 'Sistem Otomatis CEISA 4.0',
        'e_seal_id'     => 'JT701-ID-8828',
        'e_seal_status' => 'UNLOCKED',
        'lokasi_yard'   => 'Blok D (Export Staging)',
        'scan_xray'     => 'TIDAK_PERLU',
        'behandle_bay'  => null
    ],
    [
        'id'            => 9,
        'no_aju'        => '000020-001234-20260929-000109',
        'no_daftar'     => '042827/KPU.01/2026',
        'tgl_daftar'    => '2026-09-29 11:40',
        'jenis_dok'     => 'PIB (BC 2.0)',
        'importir'      => 'PT Toyota Motor Manufacturing Indonesia',
        'npwp'          => '01.000.999.8-061.000',
        'kategori_mitra'=> 'AEO (Authorized Economic Operator)',
        'no_kontainer'  => 'EISU6789012',
        'ukuran'        => "40' HC",
        'tipe_box'      => 'Dry High Cube',
        'hs_code'       => '8708.29.90',
        'uraian_barang' => 'Body Parts & Stamping Components Mobil Hybrid',
        'negara_asal'   => 'Thailand (THBKK)',
        'cif_usd'       => 168000.00,
        'cif_idr'       => 2604000000,
        'bea_masuk'     => 130200000,
        'ppn'           => 286440000,
        'pph'           => 65100000,
        'total_pungutan'=> 481740000,
        'ntpn'          => '1A2B3C4D5E6F7A8B',
        'jalur'         => 'HIJAU',
        'status'        => 'SPPB_TERBIT',
        'alasan_jalur'  => 'AEO Prioritas Tinggi, Integrasi Supply Chain Just-In-Time (JIT)',
        'no_sppb'       => 'SPPB-042827/KPU.01/2026',
        'tgl_sppb'      => '2026-09-29 11:41 WIB',
        'petugas_pfpb'  => 'Sistem Otomatis CEISA 4.0',
        'e_seal_id'     => 'JT701-ID-8829',
        'e_seal_status' => 'UNLOCKED',
        'lokasi_yard'   => 'Blok A-05-02-2',
        'scan_xray'     => 'TIDAK_PERLU',
        'behandle_bay'  => null
    ],
    [
        'id'            => 10,
        'no_aju'        => '000020-001234-20260929-000110',
        'no_daftar'     => '042828/KPU.01/2026',
        'tgl_daftar'    => '2026-09-29 12:05',
        'jenis_dok'     => 'PIB (BC 2.0)',
        'importir'      => 'PT Indo Baja Presisi',
        'npwp'          => '01.567.890.1-078.000',
        'kategori_mitra'=> 'Importir Produsen (IP)',
        'no_kontainer'  => 'KMDUS981234',
        'ukuran'        => "20' GP",
        'tipe_box'      => 'Dry Heavy Duty',
        'hs_code'       => '7210.49.12',
        'uraian_barang' => 'Lembaran Baja Canai Dingin Galvanis (Coil Steel)',
        'negara_asal'   => 'Taiwan (TWKHH)',
        'cif_usd'       => 72000.00,
        'cif_idr'       => 1116000000,
        'bea_masuk'     => 55800000,
        'ppn'           => 122760000,
        'pph'           => 27900000,
        'total_pungutan'=> 206460000,
        'ntpn'          => '4D5E6F7A8B9C1D2E',
        'jalur'         => 'HIJAU',
        'status'        => 'SPPB_TERBIT',
        'alasan_jalur'  => 'Kategori Importir Mitra Utama (MITA) Prioritas - Rilis Otomatis SPPB (PMK 190/PMK.04/2022)',
        'no_sppb'       => 'SPPB-042828/KPU.01/2026',
        'tgl_sppb'      => '2026-09-29 12:15',
        'petugas_pfpb'  => 'Sistem Otomatis CEISA 4.0 DJBC',
        'e_seal_id'     => 'JT701-ID-8830',
        'e_seal_status' => 'UNLOCKED',
        'lokasi_yard'   => 'Blok C-03-01-1',
        'scan_xray'     => 'TIDAK_PERLU',
        'behandle_bay'  => null
    ],
    [
        'id'            => 11,
        'no_aju'        => '000020-001234-20260929-000111',
        'no_daftar'     => '042829/KPU.01/2026',
        'tgl_daftar'    => '2026-09-29 12:30',
        'jenis_dok'     => 'PIB (BC 2.0)',
        'importir'      => 'PT Elektronik Jaya Makmur',
        'npwp'          => '01.678.901.2-089.000',
        'kategori_mitra'=> 'Importir Umum (IU) - High Risk',
        'no_kontainer'  => 'YMLU8901234',
        'ukuran'        => "40' HC",
        'tipe_box'      => 'Dry High Cube',
        'hs_code'       => '8517.13.00',
        'uraian_barang' => 'Smartphone & Wireless Communication Devices',
        'negara_asal'   => 'Hong Kong (HKHKG)',
        'cif_usd'       => 195000.00,
        'cif_idr'       => 3022500000,
        'bea_masuk'     => 0, // 0% MFN
        'ppn'           => 332475000,
        'pph'           => 75562500,
        'total_pungutan'=> 408037500,
        'ntpn'          => '6F7A8B9C1D2E3F4A',
        'jalur'         => 'MERAH',
        'status'        => 'LHP_SELESAI',
        'alasan_jalur'  => 'Komoditas Rawan Penyelundupan & Uji Validasi Nomor IMEI Kemenperin',
        'no_sppb'       => 'SPPB-042829/KPU.01/2026',
        'tgl_sppb'      => '2026-09-29 13:15 WIB',
        'petugas_pfpb'  => 'Hendra Kusuma, S.T. (PFPB Ahli Madya)',
        'e_seal_id'     => 'JT701-ID-8831',
        'e_seal_status' => 'UNLOCKED',
        'lokasi_yard'   => 'Zona 5 -> Menuju Gate Out',
        'scan_xray'     => 'XRAY_COMPLETED',
        'behandle_bay'  => 'Bay 04 (Electronics Cleared)'
    ],
    [
        'id'            => 12,
        'no_aju'        => '000020-001234-20260929-000112',
        'no_daftar'     => '042830/KPU.01/2026',
        'tgl_daftar'    => '2026-09-29 13:00',
        'jenis_dok'     => 'PIB (BC 2.0)',
        'importir'      => 'PT Kimia Tirta Mandiri',
        'npwp'          => '01.789.012.3-090.000',
        'kategori_mitra'=> 'Importir Produsen Farmasi',
        'no_kontainer'  => 'MEDU4321098',
        'ukuran'        => "20' GP",
        'tipe_box'      => 'Dry Standard',
        'hs_code'       => '2936.27.00',
        'uraian_barang' => 'Asam Askorbat (Vitamin C) Farmasi murni USP Grade',
        'negara_asal'   => 'Jerman (DEHAM)',
        'cif_usd'       => 62000.00,
        'cif_idr'       => 961000000,
        'bea_masuk'     => 48050000,
        'ppn'           => 105710000,
        'pph'           => 24025000,
        'total_pungutan'=> 177785000,
        'ntpn'          => '8B9C1D2E3F4A5B6C',
        'jalur'         => 'MERAH',
        'status'        => 'SPJM_TERBIT',
        'alasan_jalur'  => 'Pengawasan Bahan Baku Obat / Lartas BPOM (Surat Keterangan Impor - SKI)',
        'no_sppb'       => '-',
        'tgl_sppb'      => '-',
        'petugas_pfpb'  => 'Apt. Dewi Sartika, S.Farm. (PFPB Khusus Farmasi)',
        'e_seal_id'     => 'JT701-ID-8832',
        'e_seal_status' => 'LOCKED',
        'lokasi_yard'   => 'Blok B-01-01-1',
        'scan_xray'     => 'MENUNGGU_SCAN',
        'behandle_bay'  => 'Bay 01 (Staging Behandle)'
    ]
];

// Sinkronisasi Status Dokumen Pabean dengan Basis Data Nyata (containers)
if (isset($pdo)) {
    try {
        $stmtSync = $pdo->query("SELECT container_number, customs_status, block FROM containers");
        $dbStatusMap = [];
        while ($r = $stmtSync->fetch(PDO::FETCH_ASSOC)) {
            $dbStatusMap[$r['container_number']] = $r;
        }

        foreach ($customs_docs as &$cd) {
            if (isset($dbStatusMap[$cd['no_kontainer']])) {
                $stat = $dbStatusMap[$cd['no_kontainer']]['customs_status'];
                if ($stat === 'SPPB_CLEARED') {
                    $cd['status'] = 'SPPB_TERBIT';
                    $cd['jalur'] = 'HIJAU';
                    $cd['e_seal_status'] = 'UNLOCKED';
                    $cd['no_sppb'] = ($cd['no_sppb'] === '-' ? 'SPPB-0428' . rand(10,99) . '/KPU.01/2026' : $cd['no_sppb']);
                } elseif ($stat === 'RED_LANE') {
                    $cd['status'] = 'SPJM_TERBIT';
                    $cd['jalur'] = 'MERAH';
                    $cd['e_seal_status'] = 'LOCKED';
                    $cd['lokasi_yard'] = 'Blok E (Terminal Behandle)';
                }
            }
        }
        unset($cd);
    } catch (Exception $e) {}
}

// Perhitungan Statistik Kepabeanan (Sesuai PMK 190/PMK.04/2022: Jalur Hijau & Jalur Merah)
$total_docs       = count($customs_docs);
$count_hijau      = 0;
$count_merah      = 0;
$count_sppb       = 0;
$count_behandle   = 0;
$total_pungutan   = 0;
$total_cif_idr    = 0;

foreach ($customs_docs as $d) {
    if ($d['jalur'] === 'HIJAU') $count_hijau++;
    elseif ($d['jalur'] === 'MERAH') $count_merah++;

    if ($d['status'] === 'SPPB_TERBIT') $count_sppb++;
    if ($d['status'] === 'DALAM_BEHANDLE' || $d['status'] === 'SPJM_TERBIT') $count_behandle++;

    $total_pungutan += $d['total_pungutan'];
    $total_cif_idr  += $d['cif_idr'];
}

$persen_hijau = round(($count_hijau / $total_docs) * 100, 1);
$persen_merah = round(($count_merah / $total_docs) * 100, 1);
?>

<!-- Alert Feedback Pasca Aksi Pabean CEISA -->
<?php if ($customs_alert): ?>
<div class="mb-4 p-4 rounded-xl border flex items-start space-x-3 animate-fadeIn <?= $customs_alert['type'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-amber-50 border-amber-200 text-amber-900' ?>">
    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 <?= $customs_alert['type'] === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
        <i class="fa-solid <?= $customs_alert['type'] === 'success' ? 'fa-check' : 'fa-triangle-exclamation' ?>"></i>
    </div>
    <div class="flex-1 min-w-0">
        <h4 class="text-sm font-bold"><?= $customs_alert['title'] ?></h4>
        <p class="text-xs mt-0.5"><?= $customs_alert['msg'] ?></p>
    </div>
    <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>
<?php endif; ?>

<div class="space-y-2.5 animate-fadeIn pb-12">
    <!-- Header Modul: Compact Executive Style (Aligned with Sidebar) -->
    <div class="bg-white rounded-xl px-3 py-2 shadow-2xs border border-slate-200/80 mb-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div class="flex items-center space-x-2.5">
            <div class="w-7 h-7 rounded-lg bg-orange-50 border border-orange-200/70 text-orange-600 flex items-center justify-center text-xs shadow-2xs flex-shrink-0">
                <i class="fa-solid <?= $customs_info['icon'] ?>"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-1.5">
                    <h1 class="text-[13px] font-extrabold tracking-tight text-slate-900">Kepabeanan &amp; Bea Cukai — Integrasi CEISA 4.0</h1>
                    <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-[9px] font-bold flex items-center">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span>CEISA 4.0 Live
                    </span>
                    <span class="px-1.5 py-0.5 bg-orange-50 text-orange-700 border border-orange-200 rounded text-[9px] font-bold flex items-center">
                        <i class="fa-solid fa-stamp mr-1 text-[9px]"></i>KPPBC Cikarang
                    </span>
                    <button type="button" onclick="openVerifyStampModal()" class="px-1.5 py-0.5 bg-blue-50 hover:bg-blue-100 text-blue-800 border border-blue-200 rounded text-[9px] font-bold flex items-center transition cursor-pointer">
                        <i class="fa-solid fa-certificate mr-1 text-[9px] text-blue-600"></i>Stempel BSrE
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Micro-Cards (High-Density Executive Grid) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 mb-2.5">
        <!-- Card 1: Total Dokumen PIB/PEB -->
        <div class="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-2xs flex items-center justify-between transition hover:border-orange-200">
            <div class="min-w-0 pr-2">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider truncate mb-0.5">PIB / PEB Hari Ini</p>
                <div class="flex items-baseline space-x-1.5">
                    <h3 class="text-[13.5px] font-extrabold text-slate-900 tracking-tight leading-tight" id="statTotalDocs"><?= $total_docs ?> Dokumen</h3>
                    <span class="text-[9px] text-blue-600 font-bold">CEISA</span>
                </div>
                <p class="text-[9px] text-slate-500 font-semibold mt-0.5 truncate">CIF: Rp <?= number_format($total_cif_idr / 1000000000, 2, ',', '.') ?> M</p>
            </div>
            <div class="w-7 h-7 rounded-lg bg-blue-50 text-[#0170b9] flex items-center justify-center text-[11px] flex-shrink-0 shadow-2xs">
                <i class="fa-solid fa-file-invoice"></i>
            </div>
        </div>

        <!-- Card 2: Jalur Hijau (Auto SPPB) -->
        <div class="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-2xs flex items-center justify-between transition hover:border-emerald-200">
            <div class="min-w-0 pr-2">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider truncate mb-0.5">Jalur Hijau (Auto SPPB)</p>
                <div class="flex items-baseline space-x-1.5">
                    <h3 class="text-[13.5px] font-extrabold text-emerald-700 tracking-tight leading-tight" id="statHijauDocs"><?= $count_hijau ?> Boks</h3>
                    <span class="text-[9px] text-emerald-600 font-bold"><?= $persen_hijau ?>%</span>
                </div>
                <p class="text-[9px] text-slate-500 font-semibold mt-0.5 truncate">Auto Release &lt; 3.2s</p>
            </div>
            <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-[11px] flex-shrink-0 shadow-2xs">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <!-- Card 3: Penerimaan Negara (Billing MPN G3) -->
        <div class="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-2xs flex items-center justify-between transition hover:border-indigo-200">
            <div class="min-w-0 pr-2">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider truncate mb-0.5">Penerimaan Negara (MPN)</p>
                <div class="flex items-baseline space-x-1.5">
                    <h3 class="text-[13.5px] font-extrabold text-indigo-700 tracking-tight leading-tight" id="statPungutanMpn">Rp <?= number_format($total_pungutan / 1000000, 1, ',', '.') ?> Jt</h3>
                    <span class="text-[9px] text-indigo-600 font-bold">100% Lunas</span>
                </div>
                <p class="text-[9px] text-slate-500 font-semibold mt-0.5 truncate">Bea Masuk, PPN &amp; PPh</p>
            </div>
            <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-[11px] flex-shrink-0 shadow-2xs">
                <i class="fa-solid fa-receipt"></i>
            </div>
        </div>

        <!-- Card 4: Jalur Merah (Behandle & X-Ray 6 MeV) -->
        <div class="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-2xs flex items-center justify-between transition hover:border-rose-200">
            <div class="min-w-0 pr-2">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider truncate mb-0.5">Jalur Merah (Behandle)</p>
                <div class="flex items-baseline space-x-1.5">
                    <h3 class="text-[13.5px] font-extrabold text-rose-700 tracking-tight leading-tight" id="statMerahDocs"><?= $count_merah ?> Boks</h3>
                    <span class="text-[9px] text-rose-600 font-bold"><?= $persen_merah ?>%</span>
                </div>
                <p class="text-[9px] text-slate-500 font-semibold mt-0.5 truncate">X-Ray Gantry &amp; Kanopi</p>
            </div>
            <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-[11px] flex-shrink-0 shadow-2xs">
                <i class="fa-solid fa-radiation"></i>
            </div>
        </div>
    </div>

    <!-- Pipeline Kanal Pabean CEISA 4.0 Bar (Compact) -->
    <div class="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-2xs mb-2.5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 mb-1.5">
            <div class="flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Distribusi Kanal Pabean (Risk Profiling CEISA 4.0)</h3>
            </div>
            <div class="flex items-center space-x-2 text-[10px]">
                <span class="text-slate-500">Penerimaan NTPN: <strong class="text-slate-900 font-mono">Rp <?= number_format($total_pungutan, 0, ',', '.') ?></strong></span>
                <span class="px-1.5 py-0.2 bg-slate-100 rounded text-slate-600 font-mono text-[9px]">CIDP-GATEWAY</span>
            </div>
        </div>

        <div class="w-full bg-slate-100 rounded-full h-2 flex overflow-hidden shadow-inner">
            <div class="bg-emerald-500 h-2 transition-all duration-500" style="width: <?= $persen_hijau ?>%" title="Jalur Hijau: <?= $count_hijau ?> Dokumen (<?= $persen_hijau ?>%)"></div>
            <div class="bg-rose-500 h-2 transition-all duration-500" style="width: <?= $persen_merah ?>%" title="Jalur Merah: <?= $count_merah ?> Dokumen (<?= $persen_merah ?>%)"></div>
        </div>

        <div class="flex flex-wrap justify-between items-center text-[10px] text-slate-600 pt-1.5 border-t border-slate-100 mt-1.5">
            <div class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-xs bg-emerald-500"></span>
                <span class="font-bold text-emerald-700">Jalur Hijau: <?= $count_hijau ?> (<?= $persen_hijau ?>%)</span>
                <span class="text-slate-400 hidden sm:inline">&mdash; Auto SPPB terbit tanpa periksa fisik</span>
            </div>
            <div class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-xs bg-rose-500"></span>
                <span class="font-bold text-rose-700">Jalur Merah: <?= $count_merah ?> (<?= $persen_merah ?>%)</span>
                <span class="text-slate-400 hidden sm:inline">&mdash; Wajib X-Ray &amp; Behandle Fisik</span>
            </div>
        </div>
    </div>

    <!-- TAB NAVIGASI MODUL KEPABEANAN -->
    <div class="border-b border-gray-200">
        <nav class="flex space-x-2 sm:space-x-4 overflow-x-auto pb-1" aria-label="Customs Tabs">
            <button onclick="switchCustomsTab('tab-monitoring')" id="btn-tab-monitoring" class="customs-tab-btn py-3 px-4 border-b-2 font-bold text-xs sm:text-sm flex items-center space-x-2 border-[#0170b9] text-[#0170b9] whitespace-nowrap transition">
                <i class="fa-solid fa-list-check"></i>
                <span>1. Monitoring Dokumen &amp; SPPB</span>
                <span class="ml-1.5 px-2 py-0.5 bg-blue-100 text-[#0170b9] rounded-full text-[10px]"><?= $total_docs ?></span>
            </button>
            <button onclick="switchCustomsTab('tab-simulator')" id="btn-tab-simulator" class="customs-tab-btn py-3 px-4 border-b-2 font-bold text-xs sm:text-sm flex items-center space-x-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap transition">
                <i class="fa-solid fa-network-wired"></i>
                <span>2. Gateway Simulasi PIB CEISA 4.0</span>
            </button>
            <button onclick="switchCustomsTab('tab-behandle')" id="btn-tab-behandle" class="customs-tab-btn py-3 px-4 border-b-2 font-bold text-xs sm:text-sm flex items-center space-x-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap transition">
                <i class="fa-solid fa-radiation"></i>
                <span>3. Behandle &amp; Gantry X-Ray 6 MeV</span>
                <span class="ml-1.5 px-2 py-0.5 bg-rose-100 text-rose-700 rounded-full text-[10px]">Zona 5</span>
            </button>
            <button onclick="switchCustomsTab('tab-eseal')" id="btn-tab-eseal" class="customs-tab-btn py-3 px-4 border-b-2 font-bold text-xs sm:text-sm flex items-center space-x-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap transition">
                <i class="fa-solid fa-lock"></i>
                <span>4. Smart GPS E-Seal (Transit Priok)</span>
                <span class="ml-1.5 px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-[10px]">JT701</span>
            </button>
            <button onclick="switchCustomsTab('tab-edifact')" id="btn-tab-edifact" class="customs-tab-btn py-3 px-4 border-b-2 font-bold text-xs sm:text-sm flex items-center space-x-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap transition">
                <i class="fa-solid fa-code"></i>
                <span>5. UN/EDIFACT CUSRES &amp; CUSDEC</span>
            </button>
        </nav>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 1: MONITORING DOKUMEN & SPPB PABEAN                           -->
    <!-- ================================================================= -->
    <div id="tab-monitoring" class="customs-tab-content space-y-4">
        <!-- Filter Bar & Search Box -->
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-xs flex flex-col md:flex-row items-center justify-between gap-3">
            <div class="relative w-full md:w-96">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" id="customsSearchInput" onkeyup="filterCustomsTable()" placeholder="Cari No. Aju, Kontainer, Importir, atau SPPB..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-[#0170b9] focus:outline-none focus:bg-white transition" />
            </div>

            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                <select id="channelFilterSelect" onchange="filterCustomsTable()" class="px-3 py-2 bg-slate-50 border border-gray-200 rounded-lg text-xs font-medium text-gray-700 focus:outline-none focus:ring-1 focus:ring-[#0170b9]">
                    <option value="">Semua Kanal Pabean</option>
                    <option value="HIJAU">Jalur Hijau (Auto SPPB)</option>
                    <option value="MERAH">Jalur Merah (Behandle/X-Ray)</option>
                </select>

                <select id="statusFilterSelect" onchange="filterCustomsTable()" class="px-3 py-2 bg-slate-50 border border-gray-200 rounded-lg text-xs font-medium text-gray-700 focus:outline-none focus:ring-1 focus:ring-[#0170b9]">
                    <option value="">Semua Status Dokumen</option>
                    <option value="SPPB_TERBIT">SPPB Rilis (Gate-Out OK)</option>
                    <option value="SPJM_TERBIT">SPJM (Menunggu Behandle)</option>
                    <option value="DALAM_BEHANDLE">Dalam Pemeriksaan Fisik</option>
                    <option value="LHP_SELESAI">LHP Approved (Siap SPPB)</option>
                </select>

                <button onclick="resetCustomsFilter()" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-xs font-medium transition" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left mr-1"></i>Reset
                </button>
                <button onclick="exportCustomsExcel()" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition flex items-center shadow-xs">
                    <i class="fa-solid fa-file-excel mr-1.5"></i> Export Excel
                </button>
                <button onclick="exportCustomsPDF()" class="px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition flex items-center shadow-xs">
                    <i class="fa-solid fa-file-pdf mr-1.5"></i> Export PDF
                </button>
            </div>
        </div>

        <!-- Tabel Dokumen Kepabeanan -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs" id="customsTable">
                    <thead class="bg-slate-50/80 border-b border-gray-200 text-gray-600 uppercase text-[10px] font-bold tracking-wider">
                        <tr>
                            <th class="py-3 px-3.5 text-center">No</th>
                            <th class="py-3 px-3">No. Aju &amp; No. Daftar</th>
                            <th class="py-3 px-3">Peti Kemas &amp; Tipe</th>
                            <th class="py-3 px-3">Importir &amp; Akreditasi</th>
                            <th class="py-3 px-3">HS Code &amp; Muatan</th>
                            <th class="py-3 px-3 text-right">Pungutan (IDR)</th>
                            <th class="py-3 px-3 text-center">Kanal Pabean</th>
                            <th class="py-3 px-3 text-center">Status Dokumen</th>
                            <th class="py-3 px-3 text-center">Aksi Pabean</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <?php foreach ($customs_docs as $idx => $doc): ?>
                            <?php 
                                $is_hijau = ($doc['jalur'] === 'HIJAU');
                                $is_merah = ($doc['jalur'] === 'MERAH');
                                
                                $badge_jalur = $is_hijau 
                                    ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' 
                                    : 'bg-rose-100 text-rose-800 border border-rose-300';
                                
                                $badge_status = '';
                                if ($doc['status'] === 'SPPB_TERBIT') {
                                    $badge_status = '<span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full font-bold text-[10px] inline-flex items-center"><i class="fa-solid fa-circle-check mr-1"></i>SPPB Rilis</span>';
                                } elseif ($doc['status'] === 'SPJM_TERBIT') {
                                    $badge_status = '<span class="px-2 py-0.5 bg-rose-50 text-rose-700 rounded-full font-bold text-[10px] inline-flex items-center"><i class="fa-solid fa-clock mr-1"></i>SPJM Terbit</span>';
                                } elseif ($doc['status'] === 'DALAM_BEHANDLE') {
                                    $badge_status = '<span class="px-2 py-0.5 bg-purple-50 text-purple-700 rounded-full font-bold text-[10px] inline-flex items-center animate-pulse"><i class="fa-solid fa-microscope mr-1"></i>Di Behandle</span>';
                                } elseif ($doc['status'] === 'LHP_SELESAI') {
                                    $badge_status = '<span class="px-2 py-0.5 bg-teal-50 text-teal-700 rounded-full font-bold text-[10px] inline-flex items-center"><i class="fa-solid fa-clipboard-check mr-1"></i>LHP Approved</span>';
                                }
                            ?>
                            <tr class="hover:bg-slate-50/70 transition doc-row" data-id="<?= $doc['id'] ?>" data-jalur="<?= $doc['jalur'] ?>" data-status="<?= $doc['status'] ?>" data-search="<?= strtolower($doc['no_aju'] . ' ' . $doc['no_kontainer'] . ' ' . $doc['importir'] . ' ' . $doc['no_sppb'] . ' ' . $doc['hs_code']) ?>">
                                <td class="py-3 px-3.5 text-center text-gray-400 font-mono"><?= $idx + 1 ?></td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-gray-900 font-mono text-[11px]"><?= $doc['no_daftar'] ?></div>
                                    <div class="text-[10px] text-gray-400 font-mono truncate max-w-[170px]" title="<?= $doc['no_aju'] ?>"><?= $doc['no_aju'] ?></div>
                                    <div class="text-[9.5px] text-blue-600 font-medium"><?= $doc['jenis_dok'] ?> &bull; <?= $doc['tgl_daftar'] ?></div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold font-mono text-gray-900"><?= $doc['no_kontainer'] ?></div>
                                    <div class="text-[10px] text-gray-500"><?= $doc['ukuran'] ?> &bull; <?= $doc['tipe_box'] ?></div>
                                    <div class="text-[9.5px] text-gray-400">Yard: <strong><?= $doc['lokasi_yard'] ?></strong></div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-gray-900 truncate max-w-[160px]" title="<?= $doc['importir'] ?>"><?= $doc['importir'] ?></div>
                                    <div class="text-[10px] text-gray-400 font-mono"><?= $doc['npwp'] ?></div>
                                    <span class="inline-block mt-0.5 text-[9.5px] font-semibold text-blue-700 bg-blue-50 px-1.5 py-0.2 rounded"><?= $doc['kategori_mitra'] ?></span>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-mono text-gray-800 font-bold"><?= $doc['hs_code'] ?></div>
                                    <div class="text-[10px] text-gray-600 truncate max-w-[180px]" title="<?= $doc['uraian_barang'] ?>"><?= $doc['uraian_barang'] ?></div>
                                    <div class="text-[9.5px] text-gray-400">Asal: <?= $doc['negara_asal'] ?></div>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <div class="font-mono font-bold text-gray-900"><?= number_format($doc['total_pungutan'], 0, ',', '.') ?></div>
                                    <div class="text-[10px] text-gray-400 font-mono">CIF: $<?= number_format($doc['cif_usd'], 0) ?></div>
                                    <span class="text-[9px] text-emerald-600 font-mono" title="NTPN: <?= $doc['ntpn'] ?>">&#10003; LUNAS</span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[10.5px] font-bold <?= $badge_jalur ?> shadow-2xs block">
                                        <?= $doc['jalur'] ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <?= $badge_status ?>
                                    <?php if ($doc['no_sppb'] !== '-'): ?>
                                        <div class="text-[9px] font-mono text-gray-400 mt-0.5 truncate max-w-[110px]" title="<?= $doc['no_sppb'] ?>"><?= $doc['no_sppb'] ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <div class="flex items-center justify-center space-x-1">
                                        <!-- Tombol Detail PIB -->
                                        <button onclick="viewDocDetail(<?= $doc['id'] ?>)" class="w-7 h-7 bg-blue-50 text-[#0170b9] hover:bg-blue-100 rounded flex items-center justify-center transition" title="Lihat Detail PIB & Audit Trail CEISA">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </button>

                                        <!-- Aksi Pintas Lintas Modul -->
                                        <a href="dashboard.php?page=kontainer&search=<?= urlencode($doc['no_kontainer']) ?>" class="w-7 h-7 bg-slate-100 text-slate-700 hover:bg-[#002f5e] hover:text-white rounded flex items-center justify-center transition" title="Lacak Kontainer di Tracking Box">
                                            <i class="fa-solid fa-boxes-stacked text-xs"></i>
                                        </a>
                                        <a href="dashboard.php?page=simulator&focus_box=<?= urlencode($doc['no_kontainer']) ?>" class="w-7 h-7 bg-amber-50 text-amber-700 hover:bg-amber-600 hover:text-white rounded flex items-center justify-center transition" title="Lihat Posisi Fisik 3D">
                                            <i class="fa-solid fa-cube text-xs"></i>
                                        </a>
                                        <a href="dashboard.php?page=billing&search=<?= urlencode($doc['no_kontainer']) ?>" class="w-7 h-7 bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white rounded flex items-center justify-center transition" title="Periksa Faktur & Billing">
                                            <i class="fa-solid fa-file-invoice-dollar text-xs"></i>
                                        </a>

                                        <?php if ($doc['status'] === 'SPPB_TERBIT'): ?>
                                            <!-- Tombol Cetak SPPB Resmi -->
                                            <button onclick="printOfficialSPPB(<?= $doc['id'] ?>)" class="w-7 h-7 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded flex items-center justify-center transition" title="Cetak Surat Persetujuan Pengeluaran Barang (SPPB)">
                                                <i class="fa-solid fa-stamp text-xs"></i>
                                            </button>
                                            <!-- Quick Action: Tahan Jalur Merah -->
                                            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENAHAN kontainer <?= $doc['no_kontainer'] ?> ke Jalur Merah (Behandle / Hold)?');" class="inline">
                                                <input type="hidden" name="customs_action" value="hold_red_lane">
                                                <input type="hidden" name="container_number" value="<?= $doc['no_kontainer'] ?>">
                                                <button type="submit" class="w-7 h-7 bg-rose-50 text-rose-700 hover:bg-rose-100 rounded flex items-center justify-center transition" title="Hold / Tahan ke Jalur Merah">
                                                    <i class="fa-solid fa-ban text-xs"></i>
                                                </button>
                                            </form>
                                        <?php elseif ($doc['status'] === 'SPJM_TERBIT' || $doc['status'] === 'DALAM_BEHANDLE'): ?>
                                            <!-- Quick Action: Rilis SPPB Hijau -->
                                            <form method="POST" onsubmit="return confirm('Konfirmasi rilis SPPB Jalur Hijau untuk kontainer <?= $doc['no_kontainer'] ?>?');" class="inline">
                                                <input type="hidden" name="customs_action" value="release_sppb">
                                                <input type="hidden" name="container_number" value="<?= $doc['no_kontainer'] ?>">
                                                <button type="submit" class="w-7 h-7 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded flex items-center justify-center transition" title="Rilis SPPB (Jalur Hijau)">
                                                    <i class="fa-solid fa-circle-check text-xs"></i>
                                                </button>
                                            </form>
                                            <!-- Tombol Alihkan ke Tab Behandle -->
                                            <button onclick="inspectBehandleDoc(<?= $doc['id'] ?>)" class="w-7 h-7 bg-rose-50 text-rose-700 hover:bg-rose-100 rounded flex items-center justify-center transition" title="Periksa Fisik Behandle & Scan X-Ray">
                                                <i class="fa-solid fa-radiation text-xs"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Footer Info Tabel -->
            <div class="p-3 bg-slate-50 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-500 gap-2">
                <div>
                    Menampilkan <span id="displayedCount" class="font-bold text-gray-800"><?= $total_docs ?></span> dari <span class="font-bold text-gray-800"><?= $total_docs ?></span> dokumen kepabeanan terdaftar.
                </div>
                <div class="flex items-center space-x-2 text-[11px]">
                    <span class="flex items-center"><i class="fa-solid fa-circle text-[7px] text-emerald-500 mr-1"></i> Jalur Hijau (Auto SPPB): <?= $persen_hijau ?>%</span>
                    <span class="flex items-center"><i class="fa-solid fa-circle text-[7px] text-rose-500 mr-1"></i> Jalur Merah (Behandle): <?= $persen_merah ?>%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 2: GATEWAY SIMULASI PIB & RISK PROFILING CEISA 4.0           -->
    <!-- ================================================================= -->
    <div id="tab-simulator" class="customs-tab-content space-y-6 hidden">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Form Input Pengajuan PIB Baru (Kolom Kiri 7 Col) -->
            <div class="lg:col-span-7 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 flex items-center">
                            <i class="fa-solid fa-paper-plane mr-2 text-[#0170b9]"></i>Simulasi Pengajuan PIB ke Gateway CEISA 4.0
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">Uji coba algoritma Risk Profiling, perhitungan bea masuk otomatis, dan penetapan kanal layanan.</p>
                    </div>
                    <span class="px-2.5 py-1 bg-blue-50 text-[#0170b9] rounded-lg font-mono text-xs font-bold">API v1.4 REST</span>
                </div>

                <!-- Form Fields -->
                <form id="pibSimulationForm" onsubmit="handlePIBSubmission(event)" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">Nomor Aju PIB (26 Digit Standar DJBC)</label>
                            <div class="flex gap-1.5">
                                <input type="text" id="simNoAju" required readonly class="w-full px-3 py-2 bg-slate-100 border border-gray-200 rounded-lg text-xs font-mono font-bold text-gray-800" value="000020-001234-<?= date('Ymd') ?>-000<?= rand(120, 999) ?>" />
                                <button type="button" onclick="generateNewAju()" class="px-2.5 py-2 bg-slate-200 hover:bg-slate-300 text-gray-700 rounded-lg text-xs" title="Generate No. Aju Baru">
                                    <i class="fa-solid fa-arrows-rotate"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">Jenis Dokumen Kepabeanan</label>
                            <select id="simJenisDok" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs font-semibold focus:ring-1 focus:ring-[#0170b9] focus:outline-none">
                                <option value="PIB (BC 2.0)">PIB (BC 2.0) — Impor untuk Dipakai Umum</option>
                                <option value="BC 2.3 (TPB)">BC 2.3 — Impor Fasilitas Kawasan Berikat</option>
                                <option value="PEB (BC 3.0)">PEB (BC 3.0) — Pemberitahuan Ekspor Barang</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">Nama Perusahaan Importir / Eksportir</label>
                            <input type="text" id="simImportir" required class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-[#0170b9] focus:outline-none" value="PT Mitra Sejahtera Logistik" />
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">Profil Kepatuhan Mitra (Risk Profile)</label>
                            <select id="simKategoriMitra" onchange="autoCalculateTaxes()" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs font-semibold focus:ring-1 focus:ring-[#0170b9] focus:outline-none">
                                <option value="AEO">AEO — Authorized Economic Operator (Prioritas Hijau)</option>
                                <option value="MITA">MITA Pabean — Mitra Utama (Prioritas Hijau)</option>
                                <option value="IU_MEDIUM">Importir Umum (IU) — Risiko Menengah</option>
                                <option value="IU_HIGH">Importir Umum (IU) — Risiko Tinggi (Wajib Merah)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">Nomor Kontainer ISO</label>
                            <input type="text" id="simNoKontainer" required class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs font-mono font-bold uppercase focus:ring-1 focus:ring-[#0170b9] focus:outline-none" value="CIDU<?= rand(1000000, 9999999) ?>" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">Ukuran &amp; Tipe Box</label>
                            <select id="simUkuranBox" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs font-medium focus:ring-1 focus:ring-[#0170b9] focus:outline-none">
                                <option value="40' HC">40' High Cube Dry</option>
                                <option value="20' GP">20' General Purpose Dry</option>
                                <option value="40' Reefer">40' Reefer (Cold Chain)</option>
                                <option value="20' ISO Tank">20' ISO Tank (Cair/Hazmat)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">Negara Pelabuhan Muat</label>
                            <input type="text" id="simNegaraAsal" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-[#0170b9] focus:outline-none" value="Singapura (SGSIN)" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">Harmonized System (HS Code)</label>
                            <input type="text" id="simHsCode" required class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs font-mono font-bold focus:ring-1 focus:ring-[#0170b9] focus:outline-none" value="8471.30.20" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">Uraian Spesifikasi Muatan Barang</label>
                            <input type="text" id="simUraian" required class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-[#0170b9] focus:outline-none" value="Laptop Komputer Portabel &amp; Aksesoris Penunjang" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 bg-slate-50 p-3.5 rounded-xl border border-gray-200/80">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">Nilai Pabean CIF (USD)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 font-bold">$</span>
                                <input type="number" id="simCifUsd" oninput="autoCalculateTaxes()" required class="w-full pl-8 pr-3 py-2 bg-white border border-gray-200 rounded-lg text-xs font-mono font-bold text-gray-900 focus:ring-1 focus:ring-[#0170b9] focus:outline-none" value="75000" />
                            </div>
                            <span class="text-[10px] text-gray-500 mt-1 block">Kurs Pajak DJBC: Rp 15.500 / USD</span>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">Total Pungutan Impor (Estimasi)</label>
                            <div class="text-base font-bold text-emerald-700 font-mono py-2" id="simTotalDisplay">
                                Rp 215.062.500
                            </div>
                            <span class="text-[10px] text-gray-500 block">BM: 5% | PPN: 11% | PPh: 2.5%</span>
                        </div>
                    </div>

                    <div class="pt-2 flex items-center justify-between">
                        <button type="button" onclick="fillSamplePIB('high_risk')" class="text-xs text-rose-600 hover:text-rose-800 font-semibold underline">
                            <i class="fa-solid fa-flask-vial mr-1"></i>Muat Contoh Kasus Risiko Tinggi (Jalur Merah)
                        </button>
                        <button type="submit" id="btnSubmitPIB" class="px-5 py-2.5 bg-[#0170b9] hover:bg-[#004b87] text-white text-xs font-bold rounded-lg shadow-sm flex items-center space-x-2 transition">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>Kirim ke CEISA 4.0 &amp; Analisis Risiko</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Panel Live Decision Engine CEISA 4.0 (Kolom Kanan 5 Col) -->
            <div class="lg:col-span-5 space-y-4">
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider flex items-center">
                            <i class="fa-solid fa-microchip mr-2 text-indigo-600"></i>Engine Penetapan Kanal Otomatis
                        </h3>
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    </div>

                    <!-- Status Box Hasil Analisis -->
                    <div id="ceisaDecisionBox" class="p-5 rounded-xl border border-dashed border-gray-200 bg-slate-50 text-center space-y-2">
                        <div class="w-12 h-12 mx-auto rounded-full bg-blue-50 text-[#0170b9] flex items-center justify-center text-xl">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <h4 class="text-sm font-bold text-gray-800">Menunggu Pengajuan Dokumen</h4>
                        <p class="text-xs text-gray-500 max-w-xs mx-auto">
                            Isi formulir di sebelah kiri dan klik tombol <strong>Kirim ke CEISA 4.0</strong> untuk menguji penetapan kanal pabean secara real-time.
                        </p>
                    </div>

                    <!-- Matriks Regulasi Pabean CEISA -->
                    <div class="space-y-2.5 pt-2">
                        <h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Aturan Keputusan Kanal Pabean DJBC:</h4>
                        
                        <div class="p-2.5 rounded-lg bg-emerald-50/60 border border-emerald-100 flex items-start space-x-2.5 text-xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 mt-1 flex-shrink-0"></span>
                            <div>
                                <strong class="text-emerald-800">Jalur Hijau:</strong>
                                <p class="text-[11px] text-gray-600">Mitra AEO / MITA, komoditas non-lartas, nilai CIF konsisten dengan database pabean. SPPB otomatis diterbitkan dalam hitungan detik.</p>
                            </div>
                        </div>

                        <div class="p-2.5 rounded-lg bg-blue-50/60 border border-blue-100 flex items-start space-x-2.5 text-xs">
                            <span class="w-2 h-2 rounded-full bg-blue-500 mt-1 flex-shrink-0"></span>
                            <div>
                                <strong class="text-blue-800">Deregulasi PMK 190/PMK.04/2022:</strong>
                                <p class="text-[11px] text-gray-600">Jalur Kuning (SPBL dokumen) telah resmi <strong>DIHAPUS</strong> sejak 2022. Penelitian dokumen impor disatukan dalam pre-clearance Jalur Hijau atau audit kepatuhan post-clearance (PCA), guna memangkas dwelling time pelabuhan kering.</p>
                            </div>
                        </div>

                        <div class="p-2.5 rounded-lg bg-rose-50/60 border border-rose-100 flex items-start space-x-2.5 text-xs">
                            <span class="w-2 h-2 rounded-full bg-rose-500 mt-1 flex-shrink-0"></span>
                            <div>
                                <strong class="text-rose-800">Jalur Merah:</strong>
                                <p class="text-[11px] text-gray-600">Importir risiko tinggi, komoditas Lartas (tekstil, B3, pangan segar), atau sampling acak pabean. Wajib Gantry X-Ray 6 MeV dan inspeksi fisik behandle di Zona 5.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live JSON API Payload Preview Card -->
                <div class="bg-slate-900 rounded-2xl p-5 text-white shadow-sm space-y-3 font-mono">
                    <div class="flex items-center justify-between text-xs pb-2 border-b border-slate-800">
                        <span class="text-slate-400 flex items-center">
                            <i class="fa-solid fa-brackets-curly mr-1.5 text-emerald-400"></i>CEISA REST API Payload
                        </span>
                        <span class="text-[10px] text-emerald-400">POST /api/v1/pib/validate</span>
                    </div>
                    <pre id="jsonPayloadPreview" class="text-[10.5px] text-slate-300 overflow-x-auto max-h-48 leading-relaxed scrollbar-thin">{
  "header": {
    "nomorAju": "000020-001234-20260929-000101",
    "kodeKantor": "042800",
    "jenisDokumen": "20",
    "npwpImportir": "01.000.015.8-054.000"
  },
  "kontainer": [
    { "nomor": "MSKU7829104", "ukuran": "40", "tipe": "HC" }
  ],
  "statusGateway": "READY"
}</pre>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 3: BEHANDLE TERMINAL & GANTRY X-RAY 6 MEV (ZONA 5)            -->
    <!-- ================================================================= -->
    <div id="tab-behandle" class="customs-tab-content space-y-6 hidden">
        <!-- Banner Zona 5 Lapangan & Hardware Overview -->
        <div class="bg-gradient-to-r from-slate-900 via-[#002f5e] to-slate-900 rounded-2xl p-6 text-white shadow-md flex flex-col lg:flex-row items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-0.5 bg-rose-500/20 text-rose-300 border border-rose-500/40 rounded-full text-xs font-bold uppercase tracking-wider flex items-center">
                        <i class="fa-solid fa-radiation mr-1.5 text-xs text-rose-400"></i>Zona 5 Lapangan (35 Ha)
                    </span>
                    <span class="px-2.5 py-0.5 bg-blue-500/20 text-blue-300 rounded-full text-xs font-mono">4 Bay Behandle + 1 Gantry 6 MeV</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight">Zona Pemeriksaan Fisik Terpadu &amp; Pemindai Gantry X-Ray 6 MeV</h2>
                <p class="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
                    Fasilitas pemeriksaan pabean terstandar internasional di Zona 5 CIDP dilengkapi <strong>Gantry X-Ray Nuctech MB1215DE berdaya tembus baja >300 mm</strong> (Hardware #19) dan 4 Bay kanopi behandle untuk verifikasi fisik kargo Jalur Merah &amp; Karantina.
                </p>
            </div>

            <!-- Thumbnail Produk Hardware #19 -->
            <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/20 flex items-center space-x-3.5 flex-shrink-0">
                <img src="hardware/images/converted/item_19.png" alt="Gantry Container X-Ray" class="w-16 h-16 object-contain bg-white rounded-lg p-1 shadow-sm" />
                <div class="text-left">
                    <span class="text-[10px] text-amber-300 font-bold block uppercase">Hardware #19 &bull; CAPEX Rp 12,5 M</span>
                    <strong class="text-xs font-bold text-white block">Nuctech MB1215DE 6 MeV</strong>
                    <span class="text-[11px] text-slate-300 block">Throughput: 20-25 Truk / Jam</span>
                </div>
            </div>
        </div>

        <!-- 2 Kolom: Kolom Kiri Simulator Gantry X-Ray, Kolom Kanan 4 Bay Behandle -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Kolom Kiri: Simulator Gantry X-Ray 6 MeV (7 Col) -->
            <div class="lg:col-span-7 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 flex items-center">
                            <i class="fa-solid fa-x-ray mr-2 text-rose-600"></i>Simulator Citra Radiografi Gantry X-Ray 6 MeV
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Pemindaian non-intrusif multi-energi untuk membedakan material organik, logam, dan anorganik.</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <select id="xraySampleSelect" onchange="changeXRaySample()" class="px-2.5 py-1 bg-slate-50 border border-gray-200 rounded text-xs font-semibold text-gray-700">
                            <option value="sample_clean">Sampel A: Kargo Bersih (Clean Cargo)</option>
                            <option value="sample_suspicious">Sampel B: Terdeteksi Anomali (Contraband)</option>
                            <option value="sample_chemicals">Sampel C: Bahan Kimia / Dangerous Goods</option>
                        </select>
                    </div>
                </div>

                <!-- Visual Kanvas Radiografi X-Ray -->
                <div class="relative bg-slate-950 rounded-xl p-4 overflow-hidden border border-slate-800 shadow-inner">
                    <!-- Scanner Head Laser Guide Line -->
                    <div id="xrayScanLaser" class="absolute top-0 bottom-0 w-1 bg-rose-500 shadow-[0_0_15px_#f43f5e] z-20 left-0 hidden transition-all duration-300"></div>

                    <!-- Tampilan Radiografi SVG Kontainer -->
                    <div class="h-56 sm:h-64 flex items-center justify-center relative select-none">
                        <svg viewBox="0 0 700 240" class="w-full h-full" id="xraySvgVisual">
                            <!-- Container Outline -->
                            <rect x="50" y="30" width="600" height="170" rx="4" fill="#030712" stroke="#374151" stroke-width="2" stroke-dasharray="4 2" />
                            <text x="60" y="50" fill="#9ca3af" font-family="monospace" font-size="11" font-weight="bold">PETI KEMAS: <tspan id="xraySvgContainerId">EITU9823102</tspan> &bull; HIGH-ENERGY SCAN 6 MeV</text>

                            <!-- Sinar Radiasi Grid & Density Layers -->
                            <!-- Material Pallet / Floor -->
                            <rect x="60" y="180" width="580" height="15" fill="#1e293b" opacity="0.8" />
                            
                            <!-- Cargo Items inside Container -->
                            <!-- Organics (Orange / Brown) -->
                            <rect x="80" y="80" width="130" height="95" rx="3" fill="#ea580c" opacity="0.65" id="xrayBlock1" />
                            <text x="95" y="130" fill="#fed7aa" font-size="9" font-family="monospace">ORGANIK (DENSITAS RENDAH)</text>

                            <!-- Metals / Heavy Alloys (Blue) -->
                            <rect x="230" y="70" width="160" height="105" rx="3" fill="#0284c7" opacity="0.75" id="xrayBlock2" />
                            <text x="250" y="125" fill="#bae6fd" font-size="9" font-family="monospace">LOGAM / BAJA (DENSITAS TINGGI)</text>

                            <!-- Inorganics (Green) -->
                            <rect x="410" y="85" width="110" height="90" rx="3" fill="#10b981" opacity="0.7" id="xrayBlock3" />
                            <text x="425" y="135" fill="#d1fae5" font-size="9" font-family="monospace">ANORGANIK</text>

                            <!-- Suspicious Object Box (Red Highlight - dynamically shown) -->
                            <g id="xraySuspiciousGroup" class="hidden">
                                <rect x="540" y="110" width="80" height="65" rx="2" fill="#dc2626" opacity="0.85" stroke="#fef08a" stroke-width="2" stroke-dasharray="3 2" />
                                <circle cx="580" cy="142" r="14" fill="none" stroke="#fef08a" stroke-width="1.5" class="animate-ping" />
                                <text x="548" y="188" fill="#fca5a5" font-size="9" font-family="monospace" font-weight="bold">! ANOMALI TERTUTUP</text>
                            </g>

                            <!-- Crosshair Cursor Center -->
                            <line x1="350" y1="20" x2="350" y2="210" stroke="#f43f5e" stroke-width="0.5" opacity="0.4" stroke-dasharray="2 2" />
                            <line x1="40" y1="115" x2="660" y2="115" stroke="#f43f5e" stroke-width="0.5" opacity="0.4" stroke-dasharray="2 2" />
                        </svg>

                        <!-- Live Status Overlay di Kanvas -->
                        <div class="absolute top-2 right-2 bg-slate-900/90 backdrop-blur px-2.5 py-1 rounded border border-slate-700 text-[10px] font-mono text-emerald-400" id="xrayStatusOverlay">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block mr-1"></span>DETEKTOR SIAP
                        </div>
                    </div>

                    <!-- Legend Indikator Densitas Radiografi -->
                    <div class="mt-3 pt-3 border-t border-slate-800 flex flex-wrap items-center justify-between text-[10.5px] font-mono text-slate-400">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center"><span class="w-2.5 h-2.5 rounded-xs bg-[#ea580c] mr-1"></span>Organik (Z &lt; 10)</span>
                            <span class="flex items-center"><span class="w-2.5 h-2.5 rounded-xs bg-[#10b981] mr-1"></span>Anorganik (10 &le; Z &le; 26)</span>
                            <span class="flex items-center"><span class="w-2.5 h-2.5 rounded-xs bg-[#0284c7] mr-1"></span>Logam Berat (Z &gt; 26)</span>
                        </div>
                        <span class="text-rose-400 font-bold" id="xrayAiVerdict">AI Verdict: Menunggu Scan</span>
                    </div>
                </div>

                <!-- Tombol Aksi Kontrol Pemindai X-Ray -->
                <div class="flex items-center justify-between pt-1">
                    <div class="text-xs text-gray-500">
                        Pemeriksa Radiografi: <strong class="text-gray-800">Operator KPPBC TMP Cikarang</strong>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="triggerXRayScan()" id="btnStartXRay" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold shadow-xs flex items-center space-x-2 transition">
                            <i class="fa-solid fa-bolt-lightning"></i>
                            <span>Jalankan Pemindaian Gantry X-Ray 6 MeV</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: 4 Bay Kanopi Behandle Fisik (5 Col) -->
            <div class="lg:col-span-5 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 flex items-center">
                            <i class="fa-solid fa-boxes-packing mr-2 text-indigo-600"></i>Status 4 Bay Kanopi Behandle
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Area pemeriksaan fisik langsung kargo Jalur Merah oleh Pejabat Bea Cukai.</p>
                    </div>
                    <span class="px-2 py-0.5 bg-purple-50 text-purple-700 text-xs font-bold rounded">2 / 4 Bay Terisi</span>
                </div>

                <!-- Daftar 4 Bay Behandle -->
                <div class="space-y-3">
                    <!-- Bay 1 -->
                    <div class="p-3.5 rounded-xl border border-gray-200 hover:border-blue-300 transition bg-slate-50 flex items-center justify-between">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="w-6 h-6 rounded bg-[#002f5e] text-white flex items-center justify-center font-bold text-[11px]">B1</span>
                                <strong class="text-xs text-gray-900">Bay 01: Kanopi Hazmat / DG</strong>
                                <span class="px-1.5 py-0.2 bg-amber-100 text-amber-800 text-[9.5px] font-bold rounded">Terisi</span>
                            </div>
                            <div class="text-[11px] text-gray-500 mt-1 pl-8">
                                Kontainer: <strong class="font-mono text-gray-800">EITU9823102</strong> &bull; Methanol B3
                            </div>
                        </div>
                        <button onclick="openBehandleModal('EITU9823102', 'PT Global Chemindo Pratama', 'Bay 01', 'Methanol B3')" class="px-2.5 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded text-xs font-semibold shadow-2xs">
                            Inspeksi Fisik
                        </button>
                    </div>

                    <!-- Bay 2 -->
                    <div class="p-3.5 rounded-xl border border-gray-200 hover:border-blue-300 transition bg-slate-50 flex items-center justify-between">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="w-6 h-6 rounded bg-[#002f5e] text-white flex items-center justify-center font-bold text-[11px]">B2</span>
                                <strong class="text-xs text-gray-900">Bay 02: Dry Box Inspection</strong>
                                <span class="px-1.5 py-0.2 bg-purple-100 text-purple-800 text-[9.5px] font-bold rounded">Stripping 30%</span>
                            </div>
                            <div class="text-[11px] text-gray-500 mt-1 pl-8">
                                Kontainer: <strong class="font-mono text-gray-800">TEMU4561230</strong> &bull; Tekstil Sintetis
                            </div>
                        </div>
                        <button onclick="openBehandleModal('TEMU4561230', 'PT Tekstil Nusantara Abadi', 'Bay 02', 'Tekstil Sintetis Lartas')" class="px-2.5 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded text-xs font-semibold shadow-2xs">
                            Inspeksi Fisik
                        </button>
                    </div>

                    <!-- Bay 3 -->
                    <div class="p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/40 flex items-center justify-between">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="w-6 h-6 rounded bg-emerald-700 text-white flex items-center justify-center font-bold text-[11px]">B3</span>
                                <strong class="text-xs text-gray-900">Bay 03: Reefer Cold Chain Plug</strong>
                                <span class="px-1.5 py-0.2 bg-emerald-100 text-emerald-800 text-[9.5px] font-bold rounded">Kosong / Siap</span>
                            </div>
                            <div class="text-[11px] text-gray-500 mt-1 pl-8">
                                Dilengkapi Smart Socket 380V &amp; Pemeriksaan Balai Karantina
                            </div>
                        </div>
                        <span class="text-xs text-emerald-700 font-bold">Tersedia</span>
                    </div>

                    <!-- Bay 4 -->
                    <div class="p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/40 flex items-center justify-between">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="w-6 h-6 rounded bg-emerald-700 text-white flex items-center justify-center font-bold text-[11px]">B4</span>
                                <strong class="text-xs text-gray-900">Bay 04: General Cargo Clearance</strong>
                                <span class="px-1.5 py-0.2 bg-emerald-100 text-emerald-800 text-[9.5px] font-bold rounded">Kosong / Siap</span>
                            </div>
                            <div class="text-[11px] text-gray-500 mt-1 pl-8">
                                Forklift elektrik &amp; timbangan portable terkalibrasi
                            </div>
                        </div>
                        <span class="text-xs text-emerald-700 font-bold">Tersedia</span>
                    </div>
                </div>

                <!-- SOP 5 Langkah Pemeriksaan Behandle -->
                <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 text-xs space-y-1.5">
                    <strong class="text-[#002f5e] font-bold block flex items-center">
                        <i class="fa-solid fa-list-ol mr-1.5"></i>Alur Standar Pemeriksaan Fisik Pabean (SOP DJBC):
                    </strong>
                    <ol class="list-decimal list-inside text-[11px] text-gray-600 space-y-0.5">
                        <li>Pemindahan Peti Kemas dari Stacking Yard ke Kanopi Behandle</li>
                        <li>Pemotongan Segel Timah / E-Seal oleh Petugas PFPB</li>
                        <li>Stripping Muatan 30% atau 100% didampingi Kuasa Importir</li>
                        <li>Pencocokan Jumlah, Merk, Tipe, &amp; HS Code dengan PIB</li>
                        <li>Penerbitan LHP Digital &amp; Pelepasan SPPB Jalur Merah</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 4: SMART GPS E-SEAL TRANSIT PRIOK <-> CIKARANG                -->
    <!-- ================================================================= -->
    <div id="tab-eseal" class="customs-tab-content space-y-6 hidden">
        <!-- Banner Hardware #26 -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-start space-x-4">
                <img src="hardware/images/converted/item_26.png" alt="Smart GPS E-Seal" class="w-16 h-16 object-contain bg-slate-50 rounded-xl p-1 border border-gray-200 flex-shrink-0" />
                <div>
                    <div class="flex items-center space-x-2 mb-1">
                        <h2 class="text-lg font-bold text-gray-900">Monitoring Segel Elektronik (Smart GPS E-Seal Jointech JT701)</h2>
                        <span class="px-2 py-0.5 bg-blue-100 text-[#0170b9] rounded-full text-xs font-bold font-mono">Hardware #26</span>
                    </div>
                    <p class="text-xs text-gray-500 max-w-2xl leading-relaxed">
                        Pengawasan pergerakan kontainer transit PLP (Pindahan Lokasi Penimbunan) dari Pelabuhan Tanjung Priok menuju Cikarang Dry Port (54.8 km) via jalan tol atau kereta api menggunakan segel elektronik pintar bersensor anti-tampering dan pelacakan GPS real-time.
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <span class="px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-bold flex items-center">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 animate-ping"></span>100 Unit e-Seal Terpasang
                </span>
                <span class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-xs font-bold font-mono">Baterai: 30 Hari</span>
            </div>
        </div>

        <!-- Koridor Transit Rute 4 Checkpoints & Live Telemetri -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Peta Visual Koridor Transit (Kolom Kiri 8 Col) -->
            <div class="lg:col-span-8 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider flex items-center">
                        <i class="fa-solid fa-route mr-2 text-[#0170b9]"></i>Koridor Pengawasan Pabean Priok &harr; CIDP Cikarang (54.8 km)
                    </h3>
                    <span class="text-xs text-gray-400 font-mono">PLP Jalur Darat &amp; KA Siding</span>
                </div>

                <!-- Visual Koridor Checkpoint SVG -->
                <div class="bg-slate-900 rounded-xl p-4 text-white">
                    <svg viewBox="0 0 760 160" class="w-full h-auto">
                        <!-- Jalur Penghubung Tol & Rel -->
                        <line x1="80" y1="80" x2="680" y2="80" stroke="#334155" stroke-width="6" stroke-linecap="round" />
                        <line x1="80" y1="80" x2="680" y2="80" stroke="#0284c7" stroke-width="2" stroke-dasharray="8 6" />

                        <!-- Checkpoint 1: Tanjung Priok -->
                        <g transform="translate(80, 80)">
                            <circle cx="0" cy="0" r="16" fill="#002f5e" stroke="#38bdf8" stroke-width="2" />
                            <circle cx="0" cy="0" r="6" fill="#38bdf8" />
                            <text x="0" y="-25" text-anchor="middle" fill="#ffffff" font-size="10" font-weight="bold">PEL. TJ. PRIOK</text>
                            <text x="0" y="32" text-anchor="middle" fill="#94a3b8" font-size="8.5">Origin / Pasang e-Seal</text>
                        </g>

                        <!-- Checkpoint 2: Tol Cibitung KM 24 -->
                        <g transform="translate(280, 80)">
                            <circle cx="0" cy="0" r="12" fill="#1e293b" stroke="#fcd34d" stroke-width="1.5" />
                            <text x="0" y="-20" text-anchor="middle" fill="#fcd34d" font-size="9" font-weight="bold">CP-1: TOL CIBITUNG</text>
                            <text x="0" y="28" text-anchor="middle" fill="#94a3b8" font-size="8">KM 24 &bull; GPS Ping OK</text>
                        </g>

                        <!-- Checkpoint 3: Geofence Boundary 35 Ha -->
                        <g transform="translate(480, 80)">
                            <circle cx="0" cy="0" r="12" fill="#1e293b" stroke="#a78bfa" stroke-width="1.5" />
                            <text x="0" y="-20" text-anchor="middle" fill="#a78bfa" font-size="9" font-weight="bold">GEOFENCE CIDP</text>
                            <text x="0" y="28" text-anchor="middle" fill="#94a3b8" font-size="8">Radius 500m Masuk</text>
                        </g>

                        <!-- Checkpoint 4: Inbound Gate CIDP Cikarang -->
                        <g transform="translate(680, 80)">
                            <circle cx="0" cy="0" r="16" fill="#065f46" stroke="#34d399" stroke-width="2" />
                            <circle cx="0" cy="0" r="6" fill="#34d399" />
                            <text x="0" y="-25" text-anchor="middle" fill="#ffffff" font-size="10" font-weight="bold">CIKARANG DRY PORT</text>
                            <text x="0" y="32" text-anchor="middle" fill="#94a3b8" font-size="8.5">Destinasi / Auto Unlock</text>
                        </g>

                        <!-- Truk Animasi Transit -->
                        <g transform="translate(390, 68)" class="animate-bounce-horizontal">
                            <rect x="0" y="0" width="28" height="15" rx="2" fill="#38bdf8" />
                            <rect x="20" y="4" width="8" height="11" rx="1" fill="#0284c7" />
                            <circle cx="6" cy="16" r="3" fill="#111827" />
                            <circle cx="22" cy="16" r="3" fill="#111827" />
                        </g>
                    </svg>
                </div>

                <!-- Tabel Status 4 Unit E-Seal Aktif -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[10px] text-gray-500 font-bold uppercase border-b border-gray-200">
                            <tr>
                                <th class="py-2.5 px-3">ID e-Seal</th>
                                <th class="py-2.5 px-3">Kontainer</th>
                                <th class="py-2.5 px-3">Status Kawat</th>
                                <th class="py-2.5 px-3">Posisi Koordinat</th>
                                <th class="py-2.5 px-3">Baterai</th>
                                <th class="py-2.5 px-3 text-center">Status Pabean</th>
                                <th class="py-2.5 px-3 text-center">Aksi Remote</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-3 font-mono font-bold text-gray-900">JT701-ID-8821</td>
                                <td class="py-2.5 px-3 font-mono">MSKU7829104</td>
                                <td class="py-2.5 px-3 text-emerald-600 font-semibold"><i class="fa-solid fa-link mr-1"></i>Tersambung (Normal)</td>
                                <td class="py-2.5 px-3 font-mono text-[11px] text-gray-500">-6.2842, 107.1682 (CIDP Yard)</td>
                                <td class="py-2.5 px-3"><span class="text-emerald-700 font-bold">98%</span></td>
                                <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full font-bold text-[10px]">UNLOCKED</span></td>
                                <td class="py-2.5 px-3 text-center">
                                    <button disabled class="px-2.5 py-1 bg-gray-100 text-gray-400 rounded text-[11px] cursor-not-allowed">Selesai</button>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-3 font-mono font-bold text-gray-900">JT701-ID-8824</td>
                                <td class="py-2.5 px-3 font-mono">HLBU1238901</td>
                                <td class="py-2.5 px-3 text-emerald-600 font-semibold"><i class="fa-solid fa-lock mr-1"></i>Terkunci Aman</td>
                                <td class="py-2.5 px-3 font-mono text-[11px] text-gray-500">-6.2839, 107.1678 (Blok C-01)</td>
                                <td class="py-2.5 px-3"><span class="text-emerald-700 font-bold">92%</span></td>
                                <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full font-bold text-[10px]">LOCKED</span></td>
                                <td class="py-2.5 px-3 text-center">
                                    <button onclick="remoteUnlockESeal('JT701-ID-8824', 'HLBU1238901')" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-[11px] font-semibold transition">Buka Kunci</button>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-3 font-mono font-bold text-gray-900">JT701-ID-8825</td>
                                <td class="py-2.5 px-3 font-mono">EITU9823102</td>
                                <td class="py-2.5 px-3 text-emerald-600 font-semibold"><i class="fa-solid fa-lock mr-1"></i>Terkunci (Hazmat)</td>
                                <td class="py-2.5 px-3 font-mono text-[11px] text-gray-500">-6.2845, 107.1690 (Zona 5)</td>
                                <td class="py-2.5 px-3"><span class="text-emerald-700 font-bold">87%</span></td>
                                <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 bg-rose-100 text-rose-800 rounded-full font-bold text-[10px]">LOCKED</span></td>
                                <td class="py-2.5 px-3 text-center">
                                    <button onclick="remoteUnlockESeal('JT701-ID-8825', 'EITU9823102')" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-[11px] font-semibold transition">Buka Kunci</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Panel Simulator Uji Integritas Kawat e-Seal (Kolom Kanan 4 Col) -->
            <div class="lg:col-span-4 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider flex items-center">
                        <i class="fa-solid fa-shield-virus mr-2 text-rose-600"></i>Simulasi Uji Tampering Kawat
                    </h3>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-gray-200 text-xs space-y-3">
                    <p class="text-gray-600 leading-relaxed">
                        Jika kawat segel dipotong atau dibuka paksa tanpa otorisasi pabean selama perjalanan Priok - Cikarang, e-Seal akan memicu <strong>Tamper Alarm</strong> ke NOC YMS dan Portal CEISA dalam 1.2 detik.
                    </p>

                    <div id="tamperResultBox" class="p-3 bg-white rounded-lg border border-gray-200 text-[11px] font-mono text-gray-600">
                        Status Kawat: <span class="text-emerald-600 font-bold">&#10003; Normal &bull; Tidak Ada Pelanggaran</span>
                    </div>

                    <div class="flex flex-col gap-2">
                        <button onclick="simulateWireTamper()" class="w-full py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg font-bold text-xs transition flex items-center justify-center space-x-1.5">
                            <i class="fa-solid fa-scissors"></i>
                            <span>Simulasi Putus Kawat (Tampering)</span>
                        </button>
                        <button onclick="resetWireStatus()" class="w-full py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-bold text-xs transition">
                            Reset Status Normal
                        </button>
                    </div>
                </div>

                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800">
                    <strong>Dasar Hukum Regulasi:</strong>
                    <p class="text-[11px] mt-0.5 text-amber-900 leading-normal">
                        Peraturan Dirjen Bea dan Cukai No. PER-09/BC/2020 tentang Tata Laksana Pindahan Lokasi Penimbunan (PLP) Menggunakan Pengaman Elektronik (e-Seal).
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 5: UN/EDIFACT CUSRES & CUSDEC GENERATOR                      -->
    <!-- ================================================================= -->
    <div id="tab-edifact" class="customs-tab-content space-y-6 hidden">
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                <div>
                    <h2 class="text-base font-bold text-gray-900 flex items-center">
                        <i class="fa-solid fa-file-code mr-2 text-[#0170b9]"></i>Generator Standar Maritim Internasional UN/EDIFACT CUSRES &amp; CUSDEC
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Format baku pertukaran pesan elektronik kepabeanan WCO (World Customs Organization) standar D.95B.</p>
                </div>

                <div class="flex items-center space-x-2">
                    <select id="edifactDocSelect" onchange="generateCustomsEDIFACT()" class="px-3 py-1.5 bg-slate-50 border border-gray-200 rounded-lg text-xs font-semibold text-gray-700">
                        <option value="1">MSKU7829104 — PT Unilever (Jalur Hijau SPPB)</option>
                        <option value="2">TGHU9021845 — PT Astra Honda (Jalur Hijau SPPB)</option>
                        <option value="5">EITU9823102 — PT Global Chemindo (Jalur Merah SPJM)</option>
                        <option value="4">HLBU1238901 — PT Schneider (Jalur Hijau SPPB)</option>
                    </select>

                    <button onclick="copyEDIFACTText()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-gray-700 rounded-lg text-xs font-bold transition flex items-center space-x-1">
                        <i class="fa-regular fa-copy"></i>
                        <span>Salin</span>
                    </button>
                    <button onclick="downloadEDIFACTFile()" class="px-3 py-1.5 bg-[#0170b9] hover:bg-[#004b87] text-white rounded-lg text-xs font-bold transition flex items-center space-x-1 shadow-xs">
                        <i class="fa-solid fa-download"></i>
                        <span>Unduh .edi</span>
                    </button>
                </div>
            </div>

            <!-- Teks Area EDIFACT terformat rapi -->
            <div class="bg-slate-900 rounded-xl p-4 text-emerald-400 font-mono text-xs overflow-x-auto border border-slate-800 shadow-inner">
                <pre id="edifactMessageText" class="leading-relaxed whitespace-pre font-mono">UNA:+.? '
UNB+UNOC:3+IDCKG:CUSTOMS+CIDP:TERMINAL+260929:0816+042819'
UNH+1+CUSRES:D:95B:UN:DJBC40'
BGM+961+SPPB-042819/KPU.01/2026+9'
DTM+137:202609290816:203'
RFF+ABT:000020-001234-20260929-000101'
NAD+CZ+010000158054000::92++PT UNILEVER INDONESIA TBK+BSD CITY+TANGERANG++15345+ID'
NAD+MR+042800::92++KPPBC TMP CIKARANG+KAWASAN INDUSTRI JABABEKA+BEKASI++17530+ID'
EQD+CN+MSKU7829104+45G1:102:5++2+1'
LOC+11+IDCID:139:6+CIKARANG DRY PORT'
LOC+9+SGSIN:139:6+SINGAPORE PORT'
DOC+730+042819/KPU.01/2026:20260929'
MOA+125:242396250:IDR'
GIS+1+GREEN:CHANNEL:RELEASED'
FTX+AAI+++CARGO RELEASED UNDER SPPB CUSTOMS CLEARANCE'
UNT+15+1'
UNZ+1+042819'</pre>
            </div>

            <!-- Keterangan Segmen Standar EDI -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-[11px] text-gray-500 pt-2 border-t border-gray-100">
                <div><strong class="font-mono text-gray-800">UNB / UNH:</strong> Envelope Header Pertukaran</div>
                <div><strong class="font-mono text-gray-800">BGM+961:</strong> Respon Penetapan Pabean SPPB</div>
                <div><strong class="font-mono text-gray-800">EQD+CN:</strong> Identitas Kontainer ISO 6346</div>
                <div><strong class="font-mono text-gray-800">GIS+1:</strong> Status Kanal (Green / Red - PMK 190/2022)</div>
            </div>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL SURAT PERSETUJUAN PENGELUARAN BARANG (SPPB RESMI DJBC)           -->
<!-- ===================================================================== -->
<div id="modalOfficialSPPB" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 hidden animate-fadeIn">
    <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl overflow-hidden border border-gray-200 flex flex-col max-h-[92vh]">
        <!-- Header Modal & Tombol Cetak -->
        <div class="bg-slate-900 text-white px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-stamp text-emerald-400 text-lg"></i>
                <h3 class="font-bold text-sm sm:text-base">Dokumen Resmi Pabean: SPPB CEISA 4.0</h3>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="window.print()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-bold transition flex items-center space-x-1">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak PDF</span>
                </button>
                <button onclick="closeModal('modalOfficialSPPB')" class="w-8 h-8 rounded-full hover:bg-white/10 flex items-center justify-center text-gray-300 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>
        </div>

        <!-- Badan Dokumen SPPB Formal Format DJBC -->
        <div class="p-6 overflow-y-auto space-y-4 font-serif text-gray-900 text-xs leading-relaxed" id="sppbPrintArea">
            <!-- Kop Kementerian Keuangan RI DJBC -->
            <div class="text-center border-b-2 border-black pb-3">
                <div class="font-sans font-bold text-[11px] tracking-wider uppercase text-gray-700">KEMENTERIAN KEUANGAN REPUBLIK INDONESIA</div>
                <div class="font-sans font-bold text-sm tracking-wider uppercase text-gray-900">DIREKTORAT JENDERAL BEA DAN CUKAI</div>
                <div class="font-sans font-bold text-xs uppercase text-gray-800">KANTOR PENGAWASAN DAN PELAYANAN BEA DAN CUKAI TIPE MADYA PABEAN CIKARANG</div>
                <div class="font-sans text-[10px] text-gray-500 italic mt-0.5">Kawasan Industri Jababeka, Cikarang Dry Port Hub, Jawa Barat &bull; Telp: (021) 8983-0000</div>
            </div>

            <!-- Judul Dokumen -->
            <div class="text-center py-2">
                <h2 class="font-sans font-bold text-base tracking-wide underline uppercase">SURAT PERSETUJUAN PENGELUARAN BARANG (SPPB)</h2>
                <div class="font-mono font-bold text-xs mt-1" id="sppbDocNumber">Nomor: SPPB-042819/KPU.01/2026</div>
            </div>

            <!-- Paragraf Persetujuan -->
            <p class="font-sans text-[11px] text-justify leading-relaxed">
                Berdasarkan Pemberitahuan Impor Barang (PIB) yang telah diajukan dan telah dipenuhi segala kewajiban kepabeanan sesuai dengan ketentuan peraturan perundang-undangan yang berlaku, dengan ini diberikan persetujuan pengeluaran barang dari Kawasan Pabean Cikarang Dry Port kepada:
            </p>

            <!-- Tabel Data PIB -->
            <table class="w-full font-sans text-xs border border-gray-300">
                <tr class="border-b border-gray-200">
                    <td class="w-1/3 p-2 font-bold bg-gray-50 border-r border-gray-200">Nama Importir</td>
                    <td class="p-2 font-bold text-gray-900" id="sppbDocImportir">PT Unilever Indonesia Tbk</td>
                </tr>
                <tr class="border-b border-gray-200">
                    <td class="p-2 font-bold bg-gray-50 border-r border-gray-200">NPWP Importir</td>
                    <td class="p-2 font-mono" id="sppbDocNpwp">01.000.015.8-054.000</td>
                </tr>
                <tr class="border-b border-gray-200">
                    <td class="p-2 font-bold bg-gray-50 border-r border-gray-200">Nomor &amp; Tanggal Pendaftaran</td>
                    <td class="p-2 font-mono" id="sppbDocNoDaftar">042819/KPU.01/2026 tanggal 29-09-2026</td>
                </tr>
                <tr class="border-b border-gray-200">
                    <td class="p-2 font-bold bg-gray-50 border-r border-gray-200">Nomor Pengajuan PIB</td>
                    <td class="p-2 font-mono text-[11px]" id="sppbDocNoAju">000020-001234-20260929-000101</td>
                </tr>
                <tr class="border-b border-gray-200">
                    <td class="p-2 font-bold bg-gray-50 border-r border-gray-200">Nomor Peti Kemas (Container)</td>
                    <td class="p-2 font-mono font-bold text-emerald-800 text-sm" id="sppbDocContainer">MSKU7829104 (40' HC)</td>
                </tr>
                <tr class="border-b border-gray-200">
                    <td class="p-2 font-bold bg-gray-50 border-r border-gray-200">Uraian Jenis Barang</td>
                    <td class="p-2" id="sppbDocUraian">Bahan Baku Sabun &amp; Personal Care Products</td>
                </tr>
                <tr class="border-b border-gray-200">
                    <td class="p-2 font-bold bg-gray-50 border-r border-gray-200">Kanal Pelayanan / Penetapan</td>
                    <td class="p-2 font-bold text-emerald-700">JALUR HIJAU (AUTOMATIC CLEARANCE CEISA 4.0)</td>
                </tr>
                <tr>
                    <td class="p-2 font-bold bg-gray-50 border-r border-gray-200">Status Pembayaran NTPN</td>
                    <td class="p-2 font-mono text-emerald-700 font-bold" id="sppbDocNtpn">&#10003; LUNAS / NTPN: 9A8B7C6D5E4F3A2B</td>
                </tr>
            </table>

            <!-- Catatan Pengeluaran Gate Out -->
            <div class="font-sans text-[10.5px] p-2.5 bg-slate-50 border border-gray-200 rounded">
                <strong>Catatan Gate Out Terminal:</strong> Peti kemas telah terhubung secara elektronik dengan Sistem Gate YMS CIDP. Pengeluaran melalui Outbound Gate dapat dilakukan secara otomatis melalui verifikasi QR Code atau e-Pass RFID pengemudi.
            </div>

            <!-- Tanda Tangan & QR Code DJBC -->
            <div class="pt-4 flex items-end justify-between font-sans relative">
                <!-- QR Code Validasi DJBC -->
                <div class="flex items-center space-x-2">
                    <div class="w-16 h-16 bg-white border border-gray-400 p-1 flex items-center justify-center">
                        <i class="fa-solid fa-qrcode text-4xl text-gray-800"></i>
                    </div>
                    <div class="text-[9.5px] text-gray-500 leading-tight">
                        Dokumen ditandatangani<br>secara elektronik oleh<br><strong>CEISA 4.0 DJBC RI</strong><br>Validitas Terjamin
                    </div>
                </div>

                <!-- Bagian Tanda Tangan & Stempel Digital Resmi Pabean -->
                <div class="text-center text-xs relative pr-2">
                    <div>Cikarang, <span id="sppbDocDate">29 September 2026</span></div>
                    <div class="font-bold mt-0.5">a.n. KEPALA KANTOR</div>
                    <div class="text-[11px] text-gray-600">Pejabat Fungsional Pemeriksa Bea dan Cukai</div>
                    
                    <!-- Wadah Tanda Tangan & Stempel Basah Digital -->
                    <div class="relative my-1 flex items-center justify-center min-h-[92px] w-64 mx-auto">
                        <!-- Tanda Tangan Digital Pejabat -->
                        <div class="font-serif italic text-base text-slate-700 tracking-wide select-none z-0">
                            Digital Signature Verified
                        </div>

                        <!-- STEMPEL DIGITAL RESMI PABEAN (DJBC / KPPBC TMP CIKARANG) -->
                        <div class="absolute -top-3 left-6 -rotate-6 z-10 opacity-90 hover:opacity-100 transition transform hover:scale-105 cursor-pointer" onclick="openVerifyStampModal()" title="Klik untuk verifikasi sertifikat digital pabean (BSrE BSSN)">
                            <svg viewBox="0 0 160 160" class="w-28 h-28 filter drop-shadow-xs">
                                <defs>
                                    <path id="stampPathTop" d="M 20,80 A 60,60 0 1,1 140,80" fill="none" />
                                    <path id="stampPathBottom" d="M 140,80 A 60,60 0 0,1 20,80" fill="none" />
                                </defs>
                                <!-- Outer Dual Ring -->
                                <circle cx="80" cy="80" r="76" fill="#f0f7ff" fill-opacity="0.35" stroke="#003876" stroke-width="2.5" />
                                <circle cx="80" cy="80" r="71" fill="none" stroke="#003876" stroke-width="0.8" stroke-dasharray="2.5 2" />
                                <circle cx="80" cy="80" r="49" fill="none" stroke="#003876" stroke-width="1.5" />
                                <circle cx="80" cy="80" r="46" fill="none" stroke="#003876" stroke-width="0.6" />

                                <!-- Circular Curved Texts -->
                                <text font-family="'Segoe UI', Roboto, Arial, sans-serif" font-size="7.5" font-weight="900" fill="#003876" letter-spacing="0.8">
                                    <textPath href="#stampPathTop" xlink:href="#stampPathTop" startOffset="50%" text-anchor="middle">
                                        ★ KEMENTERIAN KEUANGAN R.I. ★
                                    </textPath>
                                </text>
                                <text font-family="'Segoe UI', Roboto, Arial, sans-serif" font-size="7" font-weight="900" fill="#003876" letter-spacing="0.6">
                                    <textPath href="#stampPathBottom" xlink:href="#stampPathBottom" startOffset="50%" text-anchor="middle">
                                        DIREKTORAT JENDERAL BEA DAN CUKAI
                                    </textPath>
                                </text>

                                <!-- Middle Text: KPPBC TMP CIKARANG -->
                                <text x="80" y="60" text-anchor="middle" font-family="'Segoe UI', Arial, sans-serif" font-size="6.8" font-weight="bold" fill="#003876" letter-spacing="0.5">KPPBC TMP CIKARANG</text>

                                <!-- Star & Wings Symbol -->
                                <g transform="translate(80, 71) scale(0.85)">
                                    <polygon points="0,-11 3,-3.5 10,-3.5 4.5,1 6.5,8.5 0,4 -6.5,8.5 -4.5,1 -10,-3.5 -3,-3.5" fill="#003876" />
                                    <path d="M -22,2 C -12,-3 -6,5 0,3 C 6,5 12,-3 22,2 C 16,8 6,6 0,8 C -6,6 -16,8 -22,2 Z" fill="#003876" />
                                </g>

                                <!-- CEISA 4.0 Badge Bar -->
                                <rect x="34" y="81" width="92" height="13" rx="2" fill="#003876" />
                                <text x="80" y="90.5" text-anchor="middle" font-family="monospace" font-size="7.5" font-weight="bold" fill="#ffffff" letter-spacing="0.8">CEISA 4.0 DJBC</text>

                                <!-- Subtext -->
                                <text x="80" y="103" text-anchor="middle" font-family="'Segoe UI', Arial, sans-serif" font-size="5.8" font-weight="bold" fill="#003876" letter-spacing="0.3">DIGITALLY SIGNED &amp; SEALED</text>
                                <text x="80" y="112" text-anchor="middle" font-family="monospace" font-size="5.2" font-weight="bold" fill="#0284c7">#SPPB-DJBC-2026</text>
                            </svg>
                        </div>
                    </div>

                    <div class="font-bold underline font-sans" id="sppbDocSigner">SISTEM CEISA 4.0 DJBC</div>
                    <div class="text-[10px] text-gray-500 font-mono">NIP. 198504122008121001</div>
                    <div class="mt-1">
                        <button type="button" onclick="openVerifyStampModal()" class="cursor-pointer inline-flex items-center text-[9.5px] text-[#003876] bg-blue-50 hover:bg-blue-100 border border-blue-200 px-2 py-0.5 rounded font-sans font-semibold transition">
                            <i class="fa-solid fa-certificate text-amber-500 mr-1"></i>Stempel Digital Terverifikasi BSrE
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 bg-gray-50 border-t border-gray-200 flex justify-end">
            <button onclick="closeModal('modalOfficialSPPB')" class="px-5 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg text-xs font-bold transition">
                Tutup Dokumen
            </button>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL DETAIL PIB & AUDIT TRAIL CEISA 4.0                               -->
<!-- ===================================================================== -->
<div id="modalPIBDetail" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 hidden animate-fadeIn">
    <div class="bg-white rounded-2xl max-w-xl w-full shadow-2xl overflow-hidden border border-gray-200 flex flex-col max-h-[90vh]">
        <div class="bg-[#002f5e] text-white px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-file-invoice text-blue-300"></i>
                <h3 class="font-bold text-sm sm:text-base">Detail Dokumen Pabean &amp; Audit Trail</h3>
            </div>
            <button onclick="closeModal('modalPIBDetail')" class="w-8 h-8 rounded-full hover:bg-white/10 flex items-center justify-center text-gray-300 hover:text-white transition">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-4 text-xs" id="pibDetailContent">
            <!-- Dynamically populated via JS -->
        </div>

        <div class="p-4 bg-gray-50 border-t border-gray-200 flex justify-end">
            <button onclick="closeModal('modalPIBDetail')" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg text-xs font-bold transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL INSPEKSI BEHANDLE FISIK (ZONA 5)                                 -->
<!-- ===================================================================== -->
<div id="modalBehandleAction" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 hidden animate-fadeIn">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden border border-gray-200 flex flex-col">
        <div class="bg-rose-900 text-white px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-microscope text-rose-300 text-base"></i>
                <h3 class="font-bold text-sm sm:text-base">Pemeriksaan Fisik Behandle &amp; Penerbitan LHP</h3>
            </div>
            <button onclick="closeModal('modalBehandleAction')" class="w-8 h-8 rounded-full hover:bg-white/10 flex items-center justify-center text-gray-300 hover:text-white transition">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <div class="p-6 space-y-4 text-xs">
            <div class="p-3 bg-slate-50 rounded-xl border border-gray-200 space-y-1">
                <div class="font-bold text-gray-900 text-sm" id="behModalContainer">EITU9823102</div>
                <div class="text-gray-500" id="behModalImportir">PT Global Chemindo Pratama</div>
                <div class="text-[11px] text-gray-400" id="behModalLocation">Lokasi: Bay 01 (Kanopi Hazmat)</div>
            </div>

            <div class="space-y-2">
                <label class="block font-bold text-gray-700 uppercase text-[11px]">Checklist Pemeriksaan Fisik Pabean:</label>
                <div class="space-y-1.5">
                    <label class="flex items-center space-x-2 p-2 rounded bg-gray-50 border border-gray-200 cursor-pointer">
                        <input type="checkbox" checked class="rounded text-[#0170b9]" />
                        <span>Kesesuaian Nomor Segel Fisik / e-Seal dengan Dokumen PIB</span>
                    </label>
                    <label class="flex items-center space-x-2 p-2 rounded bg-gray-50 border border-gray-200 cursor-pointer">
                        <input type="checkbox" checked class="rounded text-[#0170b9]" />
                        <span>Stripping Kargo Sesuai Persentase Penugasan (30% / 100%)</span>
                    </label>
                    <label class="flex items-center space-x-2 p-2 rounded bg-gray-50 border border-gray-200 cursor-pointer">
                        <input type="checkbox" checked class="rounded text-[#0170b9]" />
                        <span>Hasil Pemindaian Gantry X-Ray 6 MeV: Sesuai (No Contraband)</span>
                    </label>
                    <label class="flex items-center space-x-2 p-2 rounded bg-gray-50 border border-gray-200 cursor-pointer">
                        <input type="checkbox" checked class="rounded text-[#0170b9]" />
                        <span>Pemeriksaan Laboratorium / Dokumen Perizinan Lartas Lengkap</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase text-[11px] mb-1">Catatan Laporan Hasil Pemeriksaan (LHP):</label>
                <textarea id="behModalNotes" rows="2" class="w-full p-2.5 bg-slate-50 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-[#0170b9] focus:outline-none">Fisik barang sesuai dengan pemberitahuan PIB, kemasan utuh, jumlah koli tepat, tidak ditemukan barang larangan atau pembatasan yang tidak diberitahukan. Rekomendasi: Terbitkan SPPB Jalur Merah.</textarea>
            </div>
        </div>

        <div class="p-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-2">
            <button onclick="closeModal('modalBehandleAction')" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg text-xs font-bold transition">
                Batal
            </button>
            <button onclick="approveBehandleRelease()" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-xs transition flex items-center space-x-1.5">
                <i class="fa-solid fa-stamp"></i>
                <span>Setujui LHP &amp; Rilis SPPB</span>
            </button>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL VERIFIKASI SERTIFIKAT & STEMPEL DIGITAL PABEAN (BSrE BSSN & DJBC)-->
<!-- ===================================================================== -->
<div id="modalVerifyStamp" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 hidden animate-fadeIn">
    <div class="bg-white rounded-2xl max-w-xl w-full shadow-2xl overflow-hidden border border-gray-200 flex flex-col max-h-[92vh]">
        <!-- Header Modal -->
        <div class="bg-gradient-to-r from-[#002f5e] to-[#0170b9] text-white px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center text-amber-300">
                    <i class="fa-solid fa-stamp text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm sm:text-base">Verifikasi Stempel Digital &amp; Sertifikat Elektronik</h3>
                    <p class="text-[10px] text-blue-200">Kementerian Keuangan RI &bull; DJBC &bull; BSrE BSSN</p>
                </div>
            </div>
            <button onclick="closeModal('modalVerifyStamp')" class="w-8 h-8 rounded-full hover:bg-white/10 flex items-center justify-center text-gray-300 hover:text-white transition">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-4 text-xs">
            <!-- Banner Status Keabsahan -->
            <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 flex items-start space-x-3.5">
                <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center text-lg flex-shrink-0 shadow-xs">
                    <i class="fa-solid fa-check-double"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <strong class="text-sm font-bold text-emerald-900">STEMPEL DIGITAL SAH &amp; TERVERIFIKASI</strong>
                        <span class="px-2 py-0.2 bg-emerald-200 text-emerald-800 rounded text-[9.5px] font-bold">VALID</span>
                    </div>
                    <p class="text-[11px] text-emerald-800 mt-0.5 leading-relaxed">
                        Dokumen kepabeanan ini telah dibubuhi Stempel Digital Resmi Direktorat Jenderal Bea dan Cukai serta ditandatangani secara elektronik menggunakan Sertifikat Elektronik yang diterbitkan oleh Balai Sertifikasi Elektronik (BSrE) - Badan Siber dan Sandi Negara (BSSN).
                    </p>
                </div>
            </div>

            <!-- Pratinjau Visual Stempel Digital Resolusi Tinggi -->
            <div class="bg-slate-50 rounded-xl p-4 border border-gray-200 flex flex-col sm:flex-row items-center gap-5">
                <div class="flex-shrink-0">
                    <svg viewBox="0 0 160 160" class="w-32 h-32 select-none filter drop-shadow-sm">
                        <defs>
                            <path id="stampPathTopModal" d="M 20,80 A 60,60 0 1,1 140,80" fill="none" />
                            <path id="stampPathBottomModal" d="M 140,80 A 60,60 0 0,1 20,80" fill="none" />
                        </defs>
                        <circle cx="80" cy="80" r="76" fill="#f0f7ff" stroke="#003366" stroke-width="2.5" />
                        <circle cx="80" cy="80" r="71" fill="none" stroke="#003366" stroke-width="0.8" stroke-dasharray="2.5 2" />
                        <circle cx="80" cy="80" r="49" fill="none" stroke="#003366" stroke-width="1.5" />
                        <circle cx="80" cy="80" r="46" fill="none" stroke="#003366" stroke-width="0.6" />

                        <text font-family="'Segoe UI', Roboto, Arial, sans-serif" font-size="7.5" font-weight="900" fill="#003366" letter-spacing="0.8">
                            <textPath href="#stampPathTopModal" xlink:href="#stampPathTopModal" startOffset="50%" text-anchor="middle">
                                ★ KEMENTERIAN KEUANGAN R.I. ★
                            </textPath>
                        </text>
                        <text font-family="'Segoe UI', Roboto, Arial, sans-serif" font-size="7" font-weight="900" fill="#003366" letter-spacing="0.6">
                            <textPath href="#stampPathBottomModal" xlink:href="#stampPathBottomModal" startOffset="50%" text-anchor="middle">
                                DIREKTORAT JENDERAL BEA DAN CUKAI
                            </textPath>
                        </text>

                        <text x="80" y="60" text-anchor="middle" font-family="'Segoe UI', Arial, sans-serif" font-size="6.8" font-weight="bold" fill="#003366" letter-spacing="0.5">KPPBC TMP CIKARANG</text>

                        <g transform="translate(80, 71) scale(0.85)">
                            <polygon points="0,-11 3,-3.5 10,-3.5 4.5,1 6.5,8.5 0,4 -6.5,8.5 -4.5,1 -10,-3.5 -3,-3.5" fill="#003366" />
                            <path d="M -22,2 C -12,-3 -6,5 0,3 C 6,5 12,-3 22,2 C 16,8 6,6 0,8 C -6,6 -16,8 -22,2 Z" fill="#003876" />
                        </g>

                        <rect x="34" y="81" width="92" height="13" rx="2" fill="#003366" />
                        <text x="80" y="90.5" text-anchor="middle" font-family="monospace" font-size="7.5" font-weight="bold" fill="#ffffff" letter-spacing="0.8">CEISA 4.0 DJBC</text>

                        <text x="80" y="103" text-anchor="middle" font-family="'Segoe UI', Arial, sans-serif" font-size="5.8" font-weight="bold" fill="#003366" letter-spacing="0.3">DIGITALLY SIGNED &amp; SEALED</text>
                        <text x="80" y="112" text-anchor="middle" font-family="monospace" font-size="5.2" font-weight="bold" fill="#0284c7">#SPPB-DJBC-2026</text>
                    </svg>
                </div>
                <div class="space-y-1 text-[11px] text-gray-600">
                    <div><strong>Instansi Penerbit:</strong> Kantor Pengawasan dan Pelayanan Bea dan Cukai Tipe Madya Pabean Cikarang</div>
                    <div><strong>Sistem Pengesah:</strong> Gateway Sentral CEISA 4.0 DJBC RI</div>
                    <div><strong>Waktu Pembubuhan Stempel:</strong> <span class="font-mono text-gray-800">29 September 2026, 08:16:04 WIB</span></div>
                    <div><strong>Integritas Dokumen:</strong> <span class="text-emerald-700 font-bold">&#10003; Dokumen Tidak Diubah (Untampered)</span></div>
                    <div><strong>Dasar Hukum:</strong> UU ITE No. 11/2008 &amp; PP No. 71/2019 Pasal 11</div>
                </div>
            </div>

            <!-- Detail Kriptografi & Metadata Sertifikat -->
            <div class="space-y-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Metadata Sertifikat Elektronik Pabean (X.509 v3):</span>
                <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-200 space-y-2 text-[11px] font-mono">
                    <div class="flex justify-between border-b border-gray-200 pb-1">
                        <span class="text-gray-500">Certificate Authority (CA):</span>
                        <span class="font-bold text-gray-800 text-right">Root CA BSrE &bull; Sub-CA DJBC Kemenkeu</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-200 pb-1">
                        <span class="text-gray-500">Serial Number:</span>
                        <span class="font-bold text-gray-800">0428-BSSN-BSRE-2026-0929-8819</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-200 pb-1">
                        <span class="text-gray-500">Algoritma Tanda Tangan:</span>
                        <span class="text-gray-800">SHA-256 with RSA 2048-bit</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-200 pb-1">
                        <span class="text-gray-500">Masa Berlaku:</span>
                        <span class="text-emerald-700 font-bold">01 Jan 2026 s/d 31 Des 2028 (Aktif)</span>
                    </div>
                    <div class="pt-1">
                        <span class="text-gray-500 block mb-0.5">Hash SHA-256 Dokumen SPPB:</span>
                        <span class="text-[10px] text-slate-700 break-all bg-white p-1.5 rounded border border-gray-200 block">e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
            <button onclick="downloadPublicCert()" class="px-3.5 py-2 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 rounded-lg text-xs font-bold transition flex items-center space-x-1.5 shadow-2xs">
                <i class="fa-solid fa-download text-blue-600"></i>
                <span>Unduh Sertifikat (.cer)</span>
            </button>
            <button onclick="closeModal('modalVerifyStamp')" class="px-5 py-2 bg-[#002f5e] hover:bg-[#0170b9] text-white rounded-lg text-xs font-bold transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- JAVASCRIPT LOGIC & INTERACTIVITY ENGINE                               -->
<!-- ===================================================================== -->
<script>
// Data Lokal Dokumen Pabean (di-clone dari PHP untuk manipulasi interaktif)
let customsData = <?= json_encode($customs_docs) ?>;
let activeBehandleId = 5;

// Tab Switcher
function switchCustomsTab(tabId) {
    document.querySelectorAll('.customs-tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.customs-tab-btn').forEach(btn => {
        btn.classList.remove('border-[#0170b9]', 'text-[#0170b9]');
        btn.classList.add('border-transparent', 'text-gray-500');
    });

    const targetTab = document.getElementById(tabId);
    if (targetTab) targetTab.classList.remove('hidden');

    const activeBtn = document.getElementById('btn-' + tabId);
    if (activeBtn) {
        activeBtn.classList.remove('border-transparent', 'text-gray-500');
        activeBtn.classList.add('border-[#0170b9]', 'text-[#0170b9]');
    }
}

// Filter Tabel Dokumen
function filterCustomsTable() {
    const searchVal = document.getElementById('customsSearchInput').value.toLowerCase();
    const channelVal = document.getElementById('channelFilterSelect').value;
    const statusVal = document.getElementById('statusFilterSelect').value;

    const rows = document.querySelectorAll('.doc-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const rowSearch = row.getAttribute('data-search') || '';
        const rowJalur = row.getAttribute('data-jalur') || '';
        const rowStatus = row.getAttribute('data-status') || '';

        const matchSearch = (searchVal === '' || rowSearch.includes(searchVal));
        const matchChannel = (channelVal === '' || rowJalur === channelVal);
        const matchStatus = (statusVal === '' || rowStatus === statusVal);

        if (matchSearch && matchChannel && matchStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const displayCounter = document.getElementById('displayedCount');
    if (displayCounter) displayCounter.textContent = visibleCount;
}

function resetCustomsFilter() {
    document.getElementById('customsSearchInput').value = '';
    document.getElementById('channelFilterSelect').value = '';
    document.getElementById('statusFilterSelect').value = '';
    filterCustomsTable();
}

// Perhitungan Pajak & Pungutan Otomatis di Tab 2
function autoCalculateTaxes() {
    const cifUsd = parseFloat(document.getElementById('simCifUsd').value) || 0;
    const rateIdr = 15500;
    const cifIdr = cifUsd * rateIdr;

    const profile = document.getElementById('simKategoriMitra').value;
    const bmRate = 0.05; // 5%
    const ppnRate = 0.11; // 11%
    const pphRate = (profile === 'AEO' || profile === 'MITA') ? 0.025 : 0.075;

    const bm = cifIdr * bmRate;
    const nilaiImpor = cifIdr + bm;
    const ppn = nilaiImpor * ppnRate;
    const pph = nilaiImpor * pphRate;
    const total = bm + ppn + pph;

    const display = document.getElementById('simTotalDisplay');
    if (display) {
        display.textContent = 'Rp ' + Math.round(total).toLocaleString('id-ID');
    }

    // Update JSON Preview
    updateJsonPreview(cifUsd, total);
}

function updateJsonPreview(cifUsd, totalPungutan) {
    const noAju = document.getElementById('simNoAju').value;
    const noBox = document.getElementById('simNoKontainer').value;
    const importir = document.getElementById('simImportir').value;
    const profile = document.getElementById('simKategoriMitra').value;

    const payload = {
        header: {
            nomorAju: noAju,
            kodeKantor: "042800",
            jenisDokumen: "20",
            namaImportir: importir,
            profilRisiko: profile,
            cifUsd: cifUsd,
            pungutanEstimasiIdr: Math.round(totalPungutan || 0)
        },
        kontainer: [
            { nomor: noBox, ukuran: "40", tipe: "HC" }
        ],
        timestamp: new Date().toISOString()
    };

    const pre = document.getElementById('jsonPayloadPreview');
    if (pre) pre.textContent = JSON.stringify(payload, null, 2);
}

function generateNewAju() {
    const today = new Date();
    const dateStr = today.getFullYear() + String(today.getMonth() + 1).padStart(2, '0') + String(today.getDate()).padStart(2, '0');
    const randSuffix = String(Math.floor(Math.random() * 900000) + 100000);
    document.getElementById('simNoAju').value = '000020-001234-' + dateStr + '-' + randSuffix;
    autoCalculateTaxes();
}

function fillSamplePIB(type) {
    if (type === 'high_risk') {
        document.getElementById('simImportir').value = 'PT Petrokimia Berkah Mandiri';
        document.getElementById('simKategoriMitra').value = 'IU_HIGH';
        document.getElementById('simUkuranBox').value = "20' ISO Tank";
        document.getElementById('simHsCode').value = '2905.11.00';
        document.getElementById('simUraian').value = 'Bahan Kimia Berbahaya B3 Industri (Kategori Lartas)';
        document.getElementById('simCifUsd').value = 52000;
        autoCalculateTaxes();
    }
}

// Simulasi Pengajuan PIB ke CEISA 4.0
function handlePIBSubmission(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitPIB');
    const originalText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>Memproses CEISA 4.0...</span>';

    setTimeout(() => {
        const noAju = document.getElementById('simNoAju').value;
        const noBox = document.getElementById('simNoKontainer').value;
        const importir = document.getElementById('simImportir').value;
        const profile = document.getElementById('simKategoriMitra').value;
        const jenisDok = document.getElementById('simJenisDok').value;
        const hsCode = document.getElementById('simHsCode').value;
        const uraian = document.getElementById('simUraian').value;
        const cifUsd = parseFloat(document.getElementById('simCifUsd').value) || 0;

        let jalur = 'HIJAU';
        let status = 'SPPB_TERBIT';
        let alasan = 'Mitra Terakreditasi, Dokumen Lengkap, Jalur Hijau Otomatis (PMK 190/2022)';
        let noSppb = 'SPPB-0428' + (customsData.length + 1) + '/KPU.01/2026';

        if (profile === 'IU_HIGH' || hsCode.startsWith('29')) {
            jalur = 'MERAH';
            status = 'SPJM_TERBIT';
            alasan = 'Risk Level Tinggi / Komoditas Lartas, Diarahkan ke Gantry X-Ray & Behandle Zona 5';
            noSppb = '-';
        } else if (profile === 'IU_MEDIUM') {
            jalur = 'HIJAU';
            status = 'SPPB_TERBIT';
            alasan = 'Importir Umum Bebas Lartas - Rilis Langsung SPPB Otomatis (PMK 190/2022)';
            noSppb = 'SPPB-0428' + (customsData.length + 1) + '/KPU.01/2026';
        }

        const newDoc = {
            id: customsData.length + 1,
            no_aju: noAju,
            no_daftar: '0428' + (customsData.length + 1) + '/KPU.01/2026',
            tgl_daftar: new Date().toISOString().slice(0, 16).replace('T', ' '),
            jenis_dok: jenisDok,
            importir: importir,
            npwp: '01.' + Math.floor(Math.random() * 899 + 100) + '.456.7-021.000',
            kategori_mitra: profile,
            no_kontainer: noBox,
            ukuran: document.getElementById('simUkuranBox').value,
            tipe_box: 'General Dry Box',
            hs_code: hsCode,
            uraian_barang: uraian,
            negara_asal: document.getElementById('simNegaraAsal').value,
            cif_usd: cifUsd,
            cif_idr: cifUsd * 15500,
            total_pungutan: cifUsd * 15500 * 0.185,
            ntpn: 'NTPN-' + Math.random().toString(36).substring(2, 10).toUpperCase(),
            jalur: jalur,
            status: status,
            alasan_jalur: alasan,
            no_sppb: noSppb,
            tgl_sppb: (jalur === 'HIJAU') ? 'Baru Saja' : '-',
            petugas_pfpb: (jalur === 'HIJAU') ? 'Sistem Otomatis CEISA 4.0' : 'Petugas PFPB Cikarang',
            e_seal_id: 'JT701-ID-' + (8800 + customsData.length + 1),
            e_seal_status: (jalur === 'HIJAU') ? 'UNLOCKED' : 'LOCKED',
            lokasi_yard: (jalur === 'MERAH') ? 'Zona 5 Behandle' : 'Blok A-01-01',
            scan_xray: (jalur === 'MERAH') ? 'MENUNGGU_SCAN' : 'TIDAK_PERLU',
            behandle_bay: (jalur === 'MERAH') ? 'Bay 04' : null
        };

        // Push ke data lokal
        customsData.unshift(newDoc);

        // Update Decision Panel Box
        const decisionBox = document.getElementById('ceisaDecisionBox');
        if (decisionBox) {
            let colorClass = jalur === 'HIJAU' ? 'bg-emerald-50 border-emerald-300 text-emerald-800' : 'bg-rose-50 border-rose-300 text-rose-800';
            let iconClass = jalur === 'HIJAU' ? 'fa-circle-check text-emerald-600' : 'fa-radiation text-rose-600';

            decisionBox.className = 'p-5 rounded-xl border ' + colorClass + ' text-center space-y-2 animate-fadeIn';
            decisionBox.innerHTML = `
                <div class="w-12 h-12 mx-auto rounded-full bg-white shadow-xs flex items-center justify-center text-2xl">
                    <i class="fa-solid ${iconClass}"></i>
                </div>
                <span class="px-3 py-1 bg-white rounded-full font-bold text-xs shadow-2xs">PENETAPAN: JALUR ${jalur}</span>
                <h4 class="text-sm font-bold text-gray-900 mt-1">${status === 'SPPB_TERBIT' ? 'SPPB Berhasil Diterbitkan Secara Otomatis!' : 'SPJM Diterbitkan: Wajib Behandle & Pemindaian X-Ray'}</h4>
                <p class="text-xs text-gray-600 max-w-sm mx-auto">${alasan}</p>
                <div class="pt-2 flex justify-center gap-2">
                    ${status === 'SPPB_TERBIT' ? `<button onclick="printOfficialSPPB(${newDoc.id})" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-bold shadow-xs">Lihat &amp; Cetak SPPB</button>` : ''}
                    <button onclick="switchCustomsTab('tab-monitoring')" class="px-3 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded text-xs font-bold">Lihat di Tabel Dokumen</button>
                </div>
            `;
        }

        btn.disabled = false;
        btn.innerHTML = originalText;

        // Reset No Aju
        generateNewAju();
    }, 1200);
}

// Simulator Gantry X-Ray
function changeXRaySample() {
    const val = document.getElementById('xraySampleSelect').value;
    const suspGroup = document.getElementById('xraySuspiciousGroup');
    const aiVerdict = document.getElementById('xrayAiVerdict');
    const containerId = document.getElementById('xraySvgContainerId');

    if (val === 'sample_suspicious') {
        if (suspGroup) suspGroup.classList.remove('hidden');
        if (aiVerdict) aiVerdict.textContent = 'AI Verdict: Suspicious High-Density Object Detected!';
        if (containerId) containerId.textContent = 'TEMU4561230';
    } else if (val === 'sample_chemicals') {
        if (suspGroup) suspGroup.classList.add('hidden');
        if (aiVerdict) aiVerdict.textContent = 'AI Verdict: Liquid Dangerous Goods (B3) Confirmed';
        if (containerId) containerId.textContent = 'EITU9823102';
    } else {
        if (suspGroup) suspGroup.classList.add('hidden');
        if (aiVerdict) aiVerdict.textContent = 'AI Verdict: Cargo Homogenous & Cleared';
        if (containerId) containerId.textContent = 'MSKU7829104';
    }
}

function triggerXRayScan() {
    const laser = document.getElementById('xrayScanLaser');
    const statusOverlay = document.getElementById('xrayStatusOverlay');
    const btn = document.getElementById('btnStartXRay');

    if (!laser) return;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Memindai Kontainer...';

    laser.classList.remove('hidden');
    laser.style.left = '0%';

    if (statusOverlay) {
        statusOverlay.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-rose-500 inline-block mr-1 animate-ping"></span>X-RAY AKTIF: 6 MeV SCANNING';
        statusOverlay.className = 'absolute top-2 right-2 bg-slate-900/90 backdrop-blur px-2.5 py-1 rounded border border-rose-500 text-[10px] font-mono text-rose-400';
    }

    let pos = 0;
    const interval = setInterval(() => {
        pos += 2;
        laser.style.left = pos + '%';
        if (pos >= 100) {
            clearInterval(interval);
            setTimeout(() => {
                laser.classList.add('hidden');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-check mr-1 text-emerald-400"></i>Scan Selesai &bull; Ulangi';
                
                if (statusOverlay) {
                    statusOverlay.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block mr-1"></span>PEMINDAIAN SELESAI (100%)';
                    statusOverlay.className = 'absolute top-2 right-2 bg-slate-900/90 backdrop-blur px-2.5 py-1 rounded border border-emerald-500 text-[10px] font-mono text-emerald-400';
                }
            }, 400);
        }
    }, 30);
}

// Modal Behandle
function openBehandleModal(containerNo, importir, bay, goods) {
    document.getElementById('behModalContainer').textContent = containerNo;
    document.getElementById('behModalImportir').textContent = importir;
    document.getElementById('behModalLocation').textContent = 'Lokasi: ' + bay + ' &bull; Komoditas: ' + goods;
    document.getElementById('modalBehandleAction').classList.remove('hidden');
}

function approveBehandleRelease() {
    closeModal('modalBehandleAction');
    alert('Laporan Hasil Pemeriksaan (LHP) fisik berhasil disetujui oleh Pejabat PFPB!\nStatus kontainer dialihkan menjadi: SPPB Rilis (Gate-Out Diizinkan).');
    switchCustomsTab('tab-monitoring');
}

// Modal SPPB Resmi
function printOfficialSPPB(docId) {
    const doc = customsData.find(d => d.id === docId) || customsData[0];

    document.getElementById('sppbDocNumber').textContent = 'Nomor: ' + (doc.no_sppb !== '-' ? doc.no_sppb : 'SPPB-042819/KPU.01/2026');
    document.getElementById('sppbDocImportir').textContent = doc.importir;
    document.getElementById('sppbDocNpwp').textContent = doc.npwp;
    document.getElementById('sppbDocNoDaftar').textContent = doc.no_daftar + ' tanggal ' + doc.tgl_daftar;
    document.getElementById('sppbDocNoAju').textContent = doc.no_aju;
    document.getElementById('sppbDocContainer').textContent = doc.no_kontainer + ' (' + doc.ukuran + ' ' + doc.tipe_box + ')';
    document.getElementById('sppbDocUraian').textContent = doc.uraian_barang + ' (HS Code: ' + doc.hs_code + ')';
    document.getElementById('sppbDocNtpn').innerHTML = '&#10003; LUNAS / NTPN: ' + doc.ntpn + ' (Rp ' + Math.round(doc.total_pungutan).toLocaleString('id-ID') + ')';
    document.getElementById('sppbDocDate').textContent = '29 September 2026';

    document.getElementById('modalOfficialSPPB').classList.remove('hidden');
}

// Detail PIB Modal
function viewDocDetail(docId) {
    const doc = customsData.find(d => d.id === docId);
    if (!doc) return;

    const content = document.getElementById('pibDetailContent');
    content.innerHTML = `
        <div class="p-3 bg-slate-50 rounded-xl border border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-[10px] text-gray-400 font-mono">NOMOR AJU PIB:</span>
                    <div class="font-bold font-mono text-gray-900">${doc.no_aju}</div>
                    <div class="text-[11px] text-blue-600 font-semibold mt-0.5">${doc.jenis_dok} &bull; Pendaftaran: ${doc.no_daftar}</div>
                </div>
                <span class="px-2.5 py-1 rounded text-xs font-bold ${doc.jalur === 'HIJAU' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'}">
                    JALUR ${doc.jalur}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="p-2.5 bg-gray-50 rounded-lg">
                <span class="text-gray-400 text-[10px] block">IMPORTIR / NPWP</span>
                <strong class="text-gray-800">${doc.importir}</strong>
                <div class="text-gray-500 font-mono text-[10px]">${doc.npwp}</div>
            </div>
            <div class="p-2.5 bg-gray-50 rounded-lg">
                <span class="text-gray-400 text-[10px] block">PETI KEMAS &amp; POSISI</span>
                <strong class="text-gray-800 font-mono">${doc.no_kontainer} (${doc.ukuran})</strong>
                <div class="text-gray-500 text-[10px]">Lokasi: ${doc.lokasi_yard}</div>
            </div>
        </div>

        <div class="p-3 bg-gray-50 rounded-lg space-y-1">
            <span class="text-gray-400 text-[10px] block">NILAI PABEAN &amp; PERPAJAKAN</span>
            <div class="flex justify-between text-xs">
                <span>Nilai CIF (USD):</span>
                <strong class="font-mono">$${doc.cif_usd.toLocaleString()}</strong>
            </div>
            <div class="flex justify-between text-xs">
                <span>Nilai Pabean (IDR):</span>
                <strong class="font-mono">Rp ${Math.round(doc.cif_idr).toLocaleString('id-ID')}</strong>
            </div>
            <div class="flex justify-between text-xs border-t border-gray-200 pt-1 text-emerald-700 font-bold">
                <span>Total Pungutan BM + PPN + PPh:</span>
                <span class="font-mono">Rp ${Math.round(doc.total_pungutan).toLocaleString('id-ID')}</span>
            </div>
        </div>

        ${doc.status === 'SPPB_TERBIT' ? `
        <div class="p-3 bg-blue-50/70 border border-blue-200 rounded-xl flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-lg bg-[#002f5e] text-amber-300 flex items-center justify-center text-sm shadow-xs">
                    <i class="fa-solid fa-stamp"></i>
                </div>
                <div>
                    <span class="text-[10.5px] text-[#002f5e] font-bold block">Stempel Digital Resmi Pabean BSrE</span>
                    <span class="text-[10px] text-gray-500">Telah dibubuhi secara elektronik &bull; CEISA 4.0 DJBC</span>
                </div>
            </div>
            <button type="button" onclick="openVerifyStampModal()" class="px-2.5 py-1 bg-[#002f5e] hover:bg-[#0170b9] text-white rounded text-[11px] font-bold transition shadow-2xs cursor-pointer">
                Verifikasi Stempel
            </button>
        </div>` : ''}

        <div class="p-3 bg-blue-50/70 border border-blue-200 rounded-xl space-y-1">
            <span class="text-[#002f5e] font-bold text-[11px] block flex items-center">
                <i class="fa-solid fa-clock-rotate-left mr-1"></i>Audit Trail CEISA 4.0:
            </span>
            <ul class="text-[10.5px] text-gray-600 space-y-1">
                <li>&bull; <strong class="text-gray-800">${doc.tgl_daftar}</strong>: Transmisi dokumen PIB diterima di Gateway DJBC.</li>
                <li>&bull; <strong class="text-gray-800">${doc.tgl_daftar}</strong>: Validasi manifest BC 1.1 Pos 0012 Subpos 0034 cocok.</li>
                <li>&bull; <strong class="text-gray-800">${doc.tgl_sppb !== '-' ? doc.tgl_sppb : 'Sedang Berjalan'}</strong>: Penetapan Kanal: <strong>JALUR ${doc.jalur}</strong> (${doc.alasan_jalur}).</li>
                ${doc.status === 'SPPB_TERBIT' ? `<li>&bull; <strong class="text-emerald-700">&#10003; SPPB Terbit: ${doc.no_sppb}</strong> &bull; Gate Out YMS diizinkan.</li>` : ''}
            </ul>
        </div>
    `;

    document.getElementById('modalPIBDetail').classList.remove('hidden');
}

function inspectBehandleDoc(docId) {
    const doc = customsData.find(d => d.id === docId);
    if (!doc) return;
    openBehandleModal(doc.no_kontainer, doc.importir, doc.behandle_bay || 'Bay 01', doc.uraian_barang);
}

// Remote Unlock E-Seal
function remoteUnlockESeal(sealId, containerNo) {
    if (confirm('Konfirmasi Pembukaan Segel Elektronik (Remote Unlock):\nID e-Seal: ' + sealId + '\nPeti Kemas: ' + containerNo + '\n\nApakah kontainer telah diverifikasi memasuki batas geofence pabean CIDP?')) {
        alert('Otorisasi pabean berhasil dikirimkan via sinyal RFID terenkripsi!\nStatus ' + sealId + ' kini: UNLOCKED (Segel Terbuka Sah).');
    }
}

function simulateWireTamper() {
    const box = document.getElementById('tamperResultBox');
    if (box) {
        box.className = 'p-3 bg-rose-50 rounded-lg border border-rose-300 text-[11px] font-mono text-rose-800 animate-pulse';
        box.innerHTML = '<strong>&#9888; TAMPER ALARM TRIGGERED!</strong><br>Kawat segel diputus di luar area geofence sah (KM 31.4 Tol Jakarta-Cikampek). Sinyal darurat dikirim ke Portal CEISA &amp; Patroli Bea Cukai.';
    }
}

function resetWireStatus() {
    const box = document.getElementById('tamperResultBox');
    if (box) {
        box.className = 'p-3 bg-white rounded-lg border border-gray-200 text-[11px] font-mono text-gray-600';
        box.innerHTML = 'Status Kawat: <span class="text-emerald-600 font-bold">&#10003; Normal &bull; Tidak Ada Pelanggaran</span>';
    }
}

// EDIFACT Generator
function generateCustomsEDIFACT() {
    const docId = parseInt(document.getElementById('edifactDocSelect').value) || 1;
    const doc = customsData.find(d => d.id === docId) || customsData[0];

    const edi = `UNA:+.? '
UNB+UNOC:3+IDCKG:CUSTOMS+CIDP:TERMINAL+260929:0816+${doc.no_daftar.replace(/[^0-9]/g, '').slice(0, 6)}'
UNH+1+CUSRES:D:95B:UN:DJBC40'
BGM+961+${doc.no_sppb !== '-' ? doc.no_sppb : 'SPJM-' + doc.no_daftar}+9'
DTM+137:202609290816:203'
RFF+ABT:${doc.no_aju}'
NAD+CZ+${doc.npwp.replace(/[^0-9]/g, '')}::92++${doc.importir.toUpperCase()}++CIKARANG++ID'
NAD+MR+042800::92++KPPBC TMP CIKARANG+KAWASAN INDUSTRI JABABEKA+BEKASI++17530+ID'
EQD+CN+${doc.no_kontainer}+45G1:102:5++2+1'
LOC+11+IDCID:139:6+CIKARANG DRY PORT'
LOC+9+${doc.negara_asal.replace(/[^A-Z]/g, '').slice(0, 5) || 'SGSIN'}:139:6+ORIGIN PORT'
DOC+730+${doc.no_daftar}:20260929'
MOA+125:${Math.round(doc.total_pungutan)}:IDR'
GIS+1+${doc.jalur}:CHANNEL:STATUS'
FTX+AAI+++${doc.alasan_jalur.toUpperCase()}'
UNT+15+1'
UNZ+1+${doc.no_daftar.replace(/[^0-9]/g, '').slice(0, 6)}'`;

    document.getElementById('edifactMessageText').textContent = edi;
}

function copyEDIFACTText() {
    const text = document.getElementById('edifactMessageText').textContent;
    navigator.clipboard.writeText(text).then(() => {
        alert('Pesan standar maritim UN/EDIFACT CUSRES D.95B berhasil disalin ke clipboard!');
    });
}

function downloadEDIFACTFile() {
    const text = document.getElementById('edifactMessageText').textContent;
    const blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'CUSRES_CIDP_' + new Date().toISOString().slice(0, 10) + '.edi';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

// Modal Controller Helper
function openVerifyStampModal() {
    const modal = document.getElementById('modalVerifyStamp');
    if (modal) modal.classList.remove('hidden');
}

function downloadPublicCert() {
    const certContent = `-----BEGIN CERTIFICATE-----
MIIDxzCCAq+gAwIBAgIUBCKPPBCIKARANG202609298819MA0GCSqGSIb3DQEBCwUA
MG8xCzAJBgNVBAYTAklEMRUwEwYDVQQKDAxLSU1FTktFVSBSSTEWMBQGA1UECwwN
REpCQyBDT05ORUNUMSIwIAYDVQQDDBlLUFBCQyBUTVAgQ0lLQVJBTkcgUm9vdCBD
QTAeFw0yNjAxMDEwMDAwMDBaFw0yODEyMzEyMzU5NTlaMGkxCzAJBgNVBAYTAklE
MRUwEwYDVQQKDAxLSU1FTktFVSBSSTEWMBQGA1UECwwNREpCQyBDT05ORUNUMSAw
HgYDVQQDDBdDRUlTQSA0LjAgREpCQyBPRkZJQ0lBTDCCASIwDQYJKoZIhvcNAQEB
BQADggEPADCCAQoCggEBAL3q8y9J6X1+u6a1FvLwM8A11x8k8d3q9v2w1A==
-----END CERTIFICATE-----`;
    const blob = new Blob([certContent], { type: 'application/x-x509-ca-cert' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'KPPBC_Cikarang_CEISA40_BSrE.cer';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.add('hidden');
}

// Inisialisasi awal saat halaman dimuat
document.addEventListener('DOMContentLoaded', () => {
    autoCalculateTaxes();
});

function getCustomsExportData() {
    const headers = ['No. Daftar', 'No. Aju', 'No. Kontainer', 'Importir', 'HS Code', 'Total Pungutan', 'Kanal Pabean', 'Status', 'No. SPPB'];
    const rows = [];
    const tableRows = document.querySelectorAll('#customsTable tbody tr.doc-row');
    
    tableRows.forEach(row => {
        if (row.style.display !== 'none') {
            const docId = parseInt(row.getAttribute('data-id'));
            const doc = customsData.find(d => d.id === docId);
            if (doc) {
                rows.push([
                    doc.no_daftar,
                    doc.no_aju,
                    doc.no_kontainer,
                    doc.importir,
                    doc.hs_code,
                    doc.total_pungutan.toString(),
                    doc.jalur,
                    doc.status,
                    doc.no_sppb
                ]);
            }
        }
    });
    return { headers, rows };
}

function exportCustomsExcel() {
    const { headers, rows } = getCustomsExportData();
    CIDPExport.toExcel(headers, rows, 'Dokumen Bea Cukai', 'Laporan_Pabean_CIDP');
}

function exportCustomsPDF() {
    const { headers, rows } = getCustomsExportData();
    CIDPExport.toPDF('Laporan Dokumen Kepabeanan CEISA', headers, rows, 'Laporan_Pabean_CIDP');
}

// URL Deep-Linking Initializer for Customs CEISA
setTimeout(() => {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const searchParam = urlParams.get('search') || urlParams.get('ctr') || urlParams.get('doc');
        const tabParam = urlParams.get('tab');

        if (tabParam && typeof switchCustomsTab === 'function') {
            switchCustomsTab(tabParam);
        }
        if (searchParam) {
            const input = document.getElementById('searchCustomsInput') || document.querySelector('input[placeholder*="Cari"]');
            if (input) {
                input.value = searchParam;
                if (typeof filterCustomsDocs === 'function') filterCustomsDocs();
            }
        }
    } catch(e) {}
}, 200);
</script>
