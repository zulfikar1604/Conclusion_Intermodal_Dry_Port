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
        'site_width'  => 480,   // meter, timur-barat  (pagar perimeter YANG DIMODELKAN di 3D)
        'site_depth'  => 230,   // meter, utara-selatan
        'denah_width' => 500,   // meter, lebar kawasan
        'denah_depth' => 320,   // meter, panjang kawasan
        'fence_half_w'=> 240,
        'fence_half_d'=> 115,
        'rail_length' => 340,   // panjang rel siding yang dimodelkan (m)
    ],

    // id => [label, E, N]
    'facilities' => [
        // ---- Zona Gate (timur laut, N=86) ---------------------------------
        'f_gate_in'        => ['Gatehouse Inbound (Lane 1)',   60,  86],
        'f_gate_out'       => ['Gatehouse Outbound (Lane 2)',  50,  86],
        'f_security_gate'  => ['Security Post 24 Jam',         34,  86],
        'f_driver_rest'    => ['Rest Area Driver',             34,  66],
        'f_truck_queue'    => ['Parkir Antrian Truk',         154,  86],

        // ---- Zona Kantor (utara-tengah, N=86), urut barat -> timur ---------
        'f_datacenter'     => ['Datacenter / NOC',            -48,  86],
        'f_admin_office'   => ['Kantor Utama PT MTI',         -26,  86],
        'f_meeting_room'   => ['Ruang Meeting & Training',     -4,  86],
        'f_parking_staff'  => ['Parkir Karyawan & Tamu',       16,  86],

        // ---- Zona Fasum (barat laut) -------------------------------------
        'f_genset_shelter' => ['Genset Shelter 1.500 kVA',   -205,  86],
        'f_musholla'       => ['Masjid Al-Hidayah',          -178,  86],
        'f_kantin'         => ['Kantin & Koperasi',          -150,  86],
        'f_klinik'         => ['Klinik P3K',                 -178,  66],
        'f_damkar'         => ['Pos Damkar',                 -150,  66],

        // ---- Kolom Barat: M&R (utara) - CFS - Transit (selatan) -----------
        'f_workshop_mr'    => ['M&R Workshop',               -212,  24],
        'f_cfs'            => ['CFS Warehouse 4.000 m2',     -212,   0],
        'f_warehouse'      => ['Transit Warehouse',          -212, -24],

        // ---- Yard Penumpukan Utama (Lebih Luas dari Jalan: 8 Bay x 8 Row) --
        'f_empty_depot'    => ['Empty Container Depot',      -154,   0],
        'blok_a'           => ['Blok A (Laden Export)',       -64,  24],
        'blok_b'           => ['Blok B (Laden Import)',        64,  24],
        'blok_c'           => ['Blok C (Domestik)',           -64, -24],
        'blok_d'           => ['Blok D (Buffer)',              64, -24],

        // ---- Kolom Timur: Reefer & DG (E=154), lalu Bea Cukai (E=210) -----
        'f_reefer_racks'   => ['Reefer Yard (300 plug)',      154,  24],
        'blok_e'           => ['Blok E (DG Bunded)',          154, -24],
        'f_reefer_control' => ['Reefer Control Room',         210,  54],
        'f_behandle_xray'  => ['Gantry X-Ray 6 MeV',          210,  24],
        'f_behandle_area'  => ['Behandle Jalur Merah',        210,   8],
        'f_quarantine'     => ['Quarantine Inspection',       210,  -8],
        'f_kppbc'          => ['Kantor KPPBC',                210, -24],

        // ---- Rail Siding (selatan, N=-82) --------------------------------
        'rail_siding'      => ['Rail Siding & Dispatcher',      0, -82],
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
