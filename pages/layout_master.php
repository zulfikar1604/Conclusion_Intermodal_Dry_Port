<?php
// =============================================================================
// FILE: pages/layout_master.php
// FUNGSI: SUMBER KOORDINAT TUNGGAL (Single Source of Truth) tata letak CIDP.
//         Dipakai oleh simulator.php (3D) dan denah.php (2D) supaya tidak
//         pernah berbeda lagi.
//
// KONVENSI KOORDINAT (WAJIB DIPATUHI SEMUA HALAMAN)
//   E = meter ke TIMUR dari titik tengah kawasan  (+E = KANAN pada denah)
//   N = meter ke UTARA dari titik tengah kawasan  (+N = ATAS  pada denah)
//
//   Konversi ke dunia 3D Three.js (kamera "Denah 2D" menatap dari selatan):
//       world.x = -E        (pada Three.js sumbu +X tampil di KIRI layar,
//                            sehingga E harus dinegasikan agar timur di kanan)
//       world.z =  N
//
//   Konversi ke piksel SVG denah.php (viewBox 1200x780, pagar 40..1160 x 42..717):
//       px = 40 + (E + LEBAR/2)  * (1120 / LEBAR)
//       py = 42 + (PANJANG/2 - N) * (675  / PANJANG)
//
// CATATAN: urutan relatif (barat-timur, utara-selatan) tiap fasilitas disusun
// mengikuti denah.php. Ukuran bangunan 3D tetap memakai ukuran riil model.
// =============================================================================

$CIDP_LAYOUT = [
    'meta' => [
        'convention'  => 'E=timur(+kanan), N=utara(+atas); world.x=-E, world.z=N',
        'site_width'  => 340,   // meter, timur-barat  (pagar perimeter YANG DIMODELKAN di 3D)
        'site_depth'  => 144,   // meter, utara-selatan
        'denah_width' => 380,   // meter, lebar kawasan seperti tertulis di denah.php (1120 px)
        'denah_depth' => 260,   // meter, panjang kawasan di denah.php (675 px, digambar skematik)
        'fence_half_w'=> 170,
        'fence_half_d'=> 72,
        'rail_length' => 210,   // panjang rel yang dimodelkan (m)
    ],

    // id => [label, E, N]
    'facilities' => [
        // ---- Zona Gate (timur laut) --------------------------------------
        'f_gate_in'        => ['Gatehouse Inbound (Lane 1)',   49,  52],
        'f_gate_out'       => ['Gatehouse Outbound (Lane 2)',  41,  52],
        'f_security_gate'  => ['Security Post 24 Jam',         28,  52],
        'f_driver_rest'    => ['Rest Area Driver',             28,  38],
        'f_truck_queue'    => ['Parkir Antrian Truk',           125,   54],

        // ---- Zona Kantor (utara-tengah), urut barat -> timur --------------
        'f_datacenter'     => ['Datacenter / NOC',            -44,  54],
        'f_admin_office'   => ['Kantor Utama PT MTI',         -24,  54],
        'f_meeting_room'   => ['Ruang Meeting & Training',     -4,  54],
        'f_parking_staff'  => ['Parkir Karyawan & Tamu',       12,  54],

        // ---- Zona Fasum (barat laut) -------------------------------------
        'f_genset_shelter' => ['Genset Shelter 1.500 kVA',   -150,  54],
        'f_musholla'       => ['Masjid Al-Hidayah',          -128,  54],
        'f_kantin'         => ['Kantin & Koperasi',          -108,  54],
        'f_klinik'         => ['Klinik P3K',                 -128,  38],
        'f_damkar'         => ['Pos Damkar',                 -108,  38],

        // ---- Kolom Barat: M&R (utara) - CFS - Transit (selatan) -----------
        'f_workshop_mr'    => ['M&R Workshop',               -152,   18],
        'f_cfs'            => ['CFS Warehouse 4.000 m2',     -152,  -3],
        'f_warehouse'      => ['Transit Warehouse',          -152, -20],

        // ---- Yard Penumpukan ---------------------------------------------
        'f_empty_depot'    => ['Empty Container Depot',      -100,    0],
        'blok_a'           => ['Blok A (Laden Export)',       -44,  16],
        'blok_b'           => ['Blok B (Laden Import)',        44,  16],
        'blok_c'           => ['Blok C (Domestik)',           -44, -26],
        'blok_d'           => ['Blok D (Buffer)',              44, -26],

        // ---- Kolom Timur: Reefer & DG, lalu Bea Cukai ---------------------
        'f_reefer_racks'   => ['Reefer Yard (300 plug)',      104,  16],
        'blok_e'           => ['Blok E (DG Bunded)',          104, -26],
        'f_reefer_control' => ['Reefer Control Room',         140,  36],
        'f_behandle_xray'  => ['Gantry X-Ray 6 MeV',           140,   16],
        'f_behandle_area'  => ['Behandle Jalur Merah',         140,     4],
        'f_quarantine'     => ['Quarantine Inspection',        140,   -9],
        'f_kppbc'          => ['Kantor KPPBC',                 140, -24],

        // ---- Rail Siding (selatan) ---------------------------------------
        'rail_siding'      => ['Rail Siding & Dispatcher',      0, -48],
    ],
];

/** Daftar fasilitas dalam format kompak untuk JavaScript: id => {e, n, label}. */
function cidp_layout_for_js(array $layout): array {
    $out = ['meta' => $layout['meta'], 'facilities' => []];
    foreach ($layout['facilities'] as $id => $f) {
        $out['facilities'][$id] = ['label' => $f[0], 'e' => $f[1], 'n' => $f[2]];
    }
    return $out;
}

/** E,N (meter) -> piksel SVG denah. */
function cidp_en_to_px(array $meta, float $e, float $n): array {
    $w = $meta['denah_width'];  $d = $meta['denah_depth'];   // denah digambar pada kanvas 380 x 260 m
    return [
        'x' => 40 + ($e + $w / 2) * (1120 / $w),
        'y' => 42 + ($d / 2 - $n) * (675 / $d),
    ];
}
