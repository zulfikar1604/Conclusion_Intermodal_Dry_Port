<?php
// =============================================================================
// MODUL: DENAH TERMINAL 35 HA & MASTER PLAN SEBARAN HARDWARE CIDP
// File: pages/denah.php
// Proyek: Conclusion Intermodal Dry Port (CIDP) 35 Ha — Yard Management System
// PIC: Juan Gamaliel (Terminal Operations) & Armansyah Muchtarrom (Hardware & Infra)
// =============================================================================

$denah_info = [
    'pic'    => 'Juan Gamaliel & Armansyah Muchtarrom',
    'role'   => 'Terminal Operations & Infrastructure Specialists',
    'desc'   => 'Master plan tata letak fisik 35 Ha berbasis model Cikarang Dry Port. Memuat 7 zona operasional, 25 fasilitas lapangan, dan pemetaan sebaran 26 titik hardware (11 Live Telemetri & 15 Blueprint Fisik) dengan sistem kontrol 4 Layer.',
    'icon'   => 'fa-map-location-dot',
    'status' => 'Master Plan Aktif — 4 Layer Interaktif'
];

// Data 7 Zona Operasional & 25 Fasilitas Lapangan
$zones_data = [
    'gate' => [
        'nama' => 'Zona Gate (Entrance Area)',
        'icon' => 'fa-door-open',
        'color' => '#0284c7',
        'facilities' => [
            ['id' => 'f_gate_in', 'nama' => 'Gatehouse Inbound', 'desc' => 'Pos kontrol masuk 2 lane dilengkapi Kamera ANPR, OCR Portal, RFID Reader, dan Industrial Edge AI PC.', 'icon' => 'fa-arrow-right-to-bracket'],
            ['id' => 'f_gate_out', 'nama' => 'Gatehouse Outbound', 'desc' => 'Pos kontrol keluar 2 lane dengan pembacaan e-Pass dan palang barrier gate otomatis.', 'icon' => 'fa-arrow-right-from-bracket'],
            ['id' => 'f_weighbridge', 'nama' => 'Weighbridge Station', 'desc' => 'Bangunan jembatan timbang kendaraan kapasitas 80 ton bersertifikasi SOLAS VGM.', 'icon' => 'fa-scale-balanced'],
            ['id' => 'f_security_gate', 'nama' => 'Security Post 24 Jam', 'desc' => 'Pos keamanan gerbang utama, verifikasi dokumen surat jalan, dan penerbitan izin masuk.', 'icon' => 'fa-shield-halved'],
            ['id' => 'f_truck_queue', 'nama' => 'Parkir Truk Antrian', 'desc' => 'Area parkir tunggu (queuing yard) berkapasitas 40 truk trailer untuk mencegah antrean di jalan arteri.', 'icon' => 'fa-truck-moving']
        ]
    ],
    'cfs_yard' => [
        'nama' => 'Zona Container Yard & CFS',
        'icon' => 'fa-boxes-stacked',
        'color' => '#4f46e5',
        'facilities' => [
            ['id' => 'f_cfs', 'nama' => 'Container Freight Station (CFS)', 'desc' => 'Gudang modern 4.000 m² untuk aktivitas bongkar muat kargo LCL (stripping-stuffing) dan konsolidasi barang.', 'icon' => 'fa-warehouse'],
            ['id' => 'f_empty_depot', 'nama' => 'Empty Container Depot', 'desc' => 'Area penyimpanan peti kemas kosong (empty boxes) 20ft/40ft dengan kapasitas 2.500 TEUs.', 'icon' => 'fa-box-open'],
            ['id' => 'f_warehouse', 'nama' => 'Warehouse / Gudang Transit', 'desc' => 'Gudang tertutup untuk muatan bernilai tinggi, transit distribusi barang, dan cross-docking.', 'icon' => 'fa-dolly'],
            ['id' => 'f_stacking_yard', 'nama' => 'Container Stacking Yard (Blok A-E)', 'desc' => 'Lapangan penumpukan utama 15 Ha: Blok A/B (Laden Ekspor-Impor), Blok C (Domestik), Blok D (Buffer), Blok E (Dangerous Goods).', 'icon' => 'fa-cubes']
        ]
    ],
    'reefer' => [
        'nama' => 'Zona Reefer Yard (Cold Chain)',
        'icon' => 'fa-snowflake',
        'color' => '#06b6d4',
        'facilities' => [
            ['id' => 'f_reefer_control', 'nama' => 'Reefer Control Room', 'desc' => 'Ruang pusat kendali monitoring suhu boks pendingin 24/7 dan pencatatan fluktuasi rantai dingin.', 'icon' => 'fa-temperature-arrow-down'],
            ['id' => 'f_genset_shelter', 'nama' => 'Genset Shelter Cadangan', 'desc' => 'Gedung pembangkit daya darurat 1.500 kVA Caterpillar untuk menjamin kelangsungan listrik reefer jika PLN padam.', 'icon' => 'fa-bolt'],
            ['id' => 'f_reefer_racks', 'nama' => 'Reefer Stacking Racks (300 Plugs)', 'desc' => 'Platform bertingkat dilengkapi 300 titik colokan Smart Reefer Socket 380V/32A dengan meteran digital.', 'icon' => 'fa-plug-circle-check']
        ]
    ],
    'rail' => [
        'nama' => 'Zona Intermodal Rail Siding',
        'icon' => 'fa-train-subway',
        'color' => '#d97706',
        'facilities' => [
            ['id' => 'f_rail_office', 'nama' => 'Rail Office / Stasiun Operator', 'desc' => 'Kantor pengendali operasi langsiran dan persinyalan kereta api kontainer bekerja sama dengan KAI Logistik.', 'icon' => 'fa-train'],
            ['id' => 'f_loading_ramp', 'nama' => 'Loading Ramp & Jalur Ganda (400m)', 'desc' => '2 jalur rel KA kontainer sepanjang 400 meter untuk alih muat langsung gerbong datar (flatcar) ke lapangan.', 'icon' => 'fa-arrows-split-up-and-left']
        ]
    ],
    'customs' => [
        'nama' => 'Zona Behandle & Bea Cukai',
        'icon' => 'fa-stamp',
        'color' => '#dc2626',
        'facilities' => [
            ['id' => 'f_kppbc', 'nama' => 'Kantor Bea Cukai (KPPBC)', 'desc' => 'Kantor pelayanan kepabeanan dan pabean impor/ekspor terhubung langsung dengan sistem nasional CEISA 4.0.', 'icon' => 'fa-building-columns'],
            ['id' => 'f_behandle_xray', 'nama' => 'Behandle Area & Gantry X-Ray', 'desc' => 'Gedung pemindaian kontainer X-Ray energi tinggi (6 MeV) Nuctech dan kanopi pemeriksaan fisik Jalur Merah.', 'icon' => 'fa-radiation'],
            ['id' => 'f_quarantine', 'nama' => 'Quarantine Inspection Room', 'desc' => 'Laboratorium dan ruang pemeriksaan Balai Karantina Hewan, Ikan, dan Tumbuhan Kementerian Pertanian.', 'icon' => 'fa-biohazard']
        ]
    ],
    'office' => [
        'nama' => 'Zona Kantor & Operasional',
        'icon' => 'fa-building',
        'color' => '#7c3aed',
        'facilities' => [
            ['id' => 'f_admin_office', 'nama' => 'Kantor Utama / Admin Building', 'desc' => 'Gedung kantor pusat pengelola terminal PT Multi Terminal Indonesia (MTI) 2 lantai.', 'icon' => 'fa-briefcase'],
            ['id' => 'f_datacenter', 'nama' => 'Datacenter / Server Room (NOC)', 'desc' => 'Ruang server sentral berpendingin presisi, NVR 64-Channel, Online UPS 5000VA, dan rak switch jaringan inti.', 'icon' => 'fa-server'],
            ['id' => 'f_meeting_room', 'nama' => 'Ruang Meeting & Training Room', 'desc' => 'Ruang rapat operasional dan ruang simulasi/pelatihan bersertifikasi untuk operator terminal.', 'icon' => 'fa-users-gear'],
            ['id' => 'f_workshop_mr', 'nama' => 'Workshop / Bengkel M&R', 'desc' => 'Bengkel perawatan alat berat lapangan (Reach Stacker, Empty Handler, Forklift, dan perbaikan kontainer).', 'icon' => 'fa-wrench']
        ]
    ],
    'public' => [
        'nama' => 'Zona Fasilitas Umum & Kesejahteraan',
        'icon' => 'fa-hands-holding-child',
        'color' => '#059669',
        'facilities' => [
            ['id' => 'f_musholla', 'nama' => 'Musholla / Masjid Al-Hidayah', 'desc' => 'Fasilitas ibadah representatif berkapasitas 150 jamaah untuk pekerja terminal, petugas pabean, dan sopir truk.', 'icon' => 'fa-mosque'],
            ['id' => 'f_kantin', 'nama' => 'Kantin / Warung Makan 🍛', 'desc' => 'Area pujasera bersih dan terjangkau untuk kebutuhan makan dan minum seluruh pekerja lapangan dan sopir.', 'icon' => 'fa-utensils'],
            ['id' => 'f_koperasi', 'nama' => 'Koperasi Karyawan 🏪', 'desc' => 'Toko ritel penyedia kebutuhan sehari-hari, minuman, makanan ringan, perlengkapan APD, dan ATK.', 'icon' => 'fa-shop'],
            ['id' => 'f_klinik', 'nama' => 'Klinik P3K / Pos Kesehatan', 'desc' => 'Pos pertolongan pertama pada kecelakaan kerja (K3) dilengkapi tenaga medis jaga dan ambulans darurat.', 'icon' => 'fa-kit-medical'],
            ['id' => 'f_toilet', 'nama' => 'Toilet Umum Tersebar', 'desc' => 'Fasilitas sanitasi dan MCK bersih yang tersebar di 3 lokasi strategis (Gerbang, Kantor, dan Siding KA).', 'icon' => 'fa-restroom'],
            ['id' => 'f_parking_staff', 'nama' => 'Parkir Karyawan & Tamu', 'desc' => 'Area parkir kendaraan roda 2 dan roda 4 khusus pegawai terminal, tamu dinas, dan mitra logistik.', 'icon' => 'fa-square-parking'],
            ['id' => 'f_damkar', 'nama' => 'Pos Damkar & Fire Station', 'desc' => 'Pos tanggap darurat kebakaran dilengkapi armada pemadam kimia, reservoir air, dan jaringan hydrant yard.', 'icon' => 'fa-fire-extinguisher'],
            ['id' => 'f_driver_rest', 'nama' => 'Ruang Istirahat Driver (Rest Area)', 'desc' => 'Ruang istirahat ber-AC dilengkapi colokan listrik, dispenser air minum, dan kasur santai bagi sopir truk jarak jauh.', 'icon' => 'fa-couch']
        ]
    ]
];

// Data 26 Hardware Terpetakan pada Koordinat Denah 35 Ha
$hardware_pins = [
    // Zona Gate (Entrance Area)
    [
        'id' => 1, 'nama' => 'Kamera ANPR Gate', 'model' => 'Hikvision DS-TCG406-E', 'tipe' => 'live', 'layer' => 4,
        'zona' => 'Zona Gate', 'zona_id' => 'gate', 'posisi' => 'Gatehouse Inbound & Outbound',
        'x' => 205, 'y' => 184, 'harga' => 'Rp 16.000.000', 'unit' => '4 unit', 'subtotal' => 'Rp 64.000.000',
        'img' => 'hardware/images/converted/item_1.png', 'modul' => 'gate',
        'desc' => 'Kamera pembaca nomor plat otomatis berkecepatan tinggi, mendeteksi kedatangan truk trailer di loop masuk.'
    ],
    [
        'id' => 2, 'nama' => 'Kamera OCR Kontainer', 'model' => 'Hikvision iDS-TCV300-A6I', 'tipe' => 'live', 'layer' => 4,
        'zona' => 'Zona Gate', 'zona_id' => 'gate', 'posisi' => 'Portal Inbound Lane 1 & 2',
        'x' => 235, 'y' => 184, 'harga' => 'Rp 55.000.000', 'unit' => '4 unit', 'subtotal' => 'Rp 220.000.000',
        'img' => 'hardware/images/converted/item_2.png', 'modul' => 'gate',
        'desc' => 'Portal pemindai multi-sudut untuk mengekstraksi kode kontainer ISO 6346 (4 huruf + 7 angka) dan tipe kontainer.'
    ],
    [
        'id' => 6, 'nama' => 'Weighbridge / VGM Scale 80t', 'model' => 'Fangda Electronic Truck Scale 80 Ton', 'tipe' => 'live', 'layer' => 4,
        'zona' => 'Zona Gate', 'zona_id' => 'gate', 'posisi' => 'Weighbridge Station Inbound',
        'x' => 265, 'y' => 184, 'harga' => 'Rp 267.000.000', 'unit' => '2 unit', 'subtotal' => 'Rp 534.000.000',
        'img' => 'hardware/images/converted/item_6.png', 'modul' => 'gate',
        'desc' => 'Jembatan timbang terintegrasi load cell digital berkapasitas 80 ton untuk verifikasi berat kotor bersertifikat SOLAS VGM.'
    ],
    [
        'id' => 7, 'nama' => 'RFID Reader Long-Range', 'model' => 'UHF RFID Reader (20m)', 'tipe' => 'live', 'layer' => 4,
        'zona' => 'Zona Gate', 'zona_id' => 'gate', 'posisi' => 'Tiang e-Pass Gatehouse',
        'x' => 172, 'y' => 184, 'harga' => 'Rp 3.000.000', 'unit' => '4 unit', 'subtotal' => 'Rp 12.000.000',
        'img' => 'hardware/images/converted/item_7.png', 'modul' => 'gate',
        'desc' => 'Antena pembaca tag RFID jarak jauh hingga 20 meter untuk identifikasi kartu pengemudi dan verifikasi izin akses gerbang.'
    ],
    [
        'id' => 8, 'nama' => 'RFID Tag UHF ISO', 'model' => 'RFID UHF ID Tag ISO', 'tipe' => 'blueprint', 'layer' => 1,
        'zona' => 'Zona Gate', 'zona_id' => 'gate', 'posisi' => 'Security Post & Loket Tag',
        'x' => 147, 'y' => 125, 'harga' => 'Rp 690.000 / pack', 'unit' => '50 pack', 'subtotal' => 'Rp 34.500.000',
        'img' => 'hardware/images/converted/item_8.png', 'modul' => 'gate',
        'desc' => 'Kartu tag pintar anti-metal standar ISO 18000-6C yang dibagikan kepada mitra truk dan kartu akses pengemudi.'
    ],
    [
        'id' => 9, 'nama' => 'Automatic Barrier Gate', 'model' => 'High-Speed Barrier Gate', 'tipe' => 'blueprint', 'layer' => 4,
        'zona' => 'Zona Gate', 'zona_id' => 'gate', 'posisi' => 'Lane 1, 2, 3, 4 Gatehouses',
        'x' => 295, 'y' => 184, 'harga' => 'Rp 60.000.000', 'unit' => '4 unit', 'subtotal' => 'Rp 240.000.000',
        'img' => 'hardware/images/converted/item_9.png', 'modul' => 'gate',
        'desc' => 'Palang otomatis kecepatan tinggi (buka 1.2 detik) yang digerakkan sinyal relay Edge PC setelah dokumen tervalidasi.'
    ],
    [
        'id' => 10, 'nama' => 'LED Information Display', 'model' => 'Outdoor P6/P8 Industrial LED', 'tipe' => 'live', 'layer' => 4,
        'zona' => 'Zona Gate', 'zona_id' => 'gate', 'posisi' => 'Kanopi Gerbang Inbound/Outbound',
        'x' => 220, 'y' => 132, 'harga' => 'Rp 47.000.000', 'unit' => '4 unit', 'subtotal' => 'Rp 188.000.000',
        'img' => 'hardware/images/converted/item_10.png', 'modul' => 'gate',
        'desc' => 'Display LED outdoor cerah yang menampilkan instruksi sopir ("SILAHKAN MASUK", "KE YARD BLOK B"), dan nomor gate pass.'
    ],
    [
        'id' => 18, 'nama' => 'Industrial Edge AI PC Gate', 'model' => 'Advantech ARK-3532 Fanless', 'tipe' => 'live', 'layer' => 2,
        'zona' => 'Zona Gate', 'zona_id' => 'gate', 'posisi' => 'Kabinet Kontrol Gate Inbound/Outbound',
        'x' => 280, 'y' => 132, 'harga' => 'Rp 42.000.000', 'unit' => '4 unit', 'subtotal' => 'Rp 168.000.000',
        'img' => 'hardware/images/converted/item_18.png', 'modul' => 'gate',
        'desc' => 'Komputer tepi tangguh tanpa kipas (-20°C s/d 60°C) yang memproses validasi transaksi gerbang lokal dan kontrol relay palang.'
    ],
    [
        'id' => 15, 'nama' => 'Barcode / QR Scanner', 'model' => 'Zebra Symbol LS2208 Handheld', 'tipe' => 'blueprint', 'layer' => 2,
        'zona' => 'Zona Gate', 'zona_id' => 'gate', 'posisi' => 'Security Post & Loket Pelayanan',
        'x' => 171, 'y' => 125, 'harga' => 'Rp 1.600.000', 'unit' => '8 unit', 'subtotal' => 'Rp 12.800.000',
        'img' => 'hardware/images/converted/item_15.png', 'modul' => 'scanner',
        'desc' => 'Pemindai laser 1D/2D untuk membaca barcode surat jalan cetak, gate pass digital di smartphone sopir, dan dokumen booking.'
    ],

    // Zona Container Yard & CFS
    [
        'id' => 3, 'nama' => 'CCTV Yard 4 MP', 'model' => 'Hikvision DS-2CD2046G2H-IU', 'tipe' => 'blueprint', 'layer' => 3,
        'zona' => 'Zona Container Yard', 'zona_id' => 'cfs_yard', 'posisi' => 'Tiang Lampu Blok A, B, C, D (32 Titik)',
        'x' => 557, 'y' => 350, 'harga' => 'Rp 3.500.000', 'unit' => '32 unit', 'subtotal' => 'Rp 112.000.000',
        'img' => 'hardware/images/converted/item_3.png', 'modul' => 'yard',
        'desc' => 'Kamera pengawas berdefinisi tinggi IP67 weatherproof dengan night vision IR untuk memantau keselamatan penumpukan kontainer.'
    ],
    [
        'id' => 5, 'nama' => 'CCTV PTZ 360°', 'model' => 'Hikvision DS-2SE4C425MWG TandemVu', 'tipe' => 'blueprint', 'layer' => 3,
        'zona' => 'Zona Container Yard', 'zona_id' => 'cfs_yard', 'posisi' => 'Menara Pengawas Pusat (Tower 1 & 2)',
        'x' => 640, 'y' => 275, 'harga' => 'Rp 10.300.000', 'unit' => '8 unit', 'subtotal' => 'Rp 82.400.000',
        'img' => 'hardware/images/converted/item_5.png', 'modul' => 'yard',
        'desc' => 'Kamera bergerak 360 derajat dengan 25x optical zoom untuk pemantauan insiden darurat dan pergerakan alat berat di yard.'
    ],
    [
        'id' => 20, 'nama' => 'Vehicle Mounted Terminal (VMT)', 'model' => 'Zebra VC8300 Rugged Terminal', 'tipe' => 'blueprint', 'layer' => 4,
        'zona' => 'Zona Container Yard', 'zona_id' => 'cfs_yard', 'posisi' => 'Kabin Reach Stacker 01 & 02',
        'x' => 605, 'y' => 425, 'harga' => 'Rp 74.000.000', 'unit' => '6 unit', 'subtotal' => 'Rp 444.000.000',
        'img' => 'hardware/images/converted/item_20.png', 'modul' => 'alat',
        'desc' => 'Terminal komputer kabin layar sentuh tahan guncangan ekstrem (MIL-STD-810G) untuk menerima perintah kerja (Job Order) dari YMS.'
    ],
    [
        'id' => 21, 'nama' => 'DGPS / RTK GNSS Receiver', 'model' => 'CHCNAV CGI-610 Dual Antenna', 'tipe' => 'live', 'layer' => 4,
        'zona' => 'Zona Container Yard', 'zona_id' => 'cfs_yard', 'posisi' => 'Atap Boom Reach Stacker 01 & 02',
        'x' => 645, 'y' => 425, 'harga' => 'Rp 45.000.000', 'unit' => '6 unit', 'subtotal' => 'Rp 270.000.000',
        'img' => 'hardware/images/converted/item_21.png', 'modul' => 'yard',
        'desc' => 'Antena navigasi satelit berakurasi centimeter (<1.4 cm) untuk mendeteksi secara otomatis posisi penumpukan Bay-Row-Tier.'
    ],
    [
        'id' => 22, 'nama' => 'Spreader Twistlock & Load Cell', 'model' => 'Bromma SmartSpreader Kit', 'tipe' => 'live', 'layer' => 4,
        'zona' => 'Zona Container Yard', 'zona_id' => 'cfs_yard', 'posisi' => 'Head Spreader Alat Angkat RS',
        'x' => 685, 'y' => 425, 'harga' => 'Rp 35.000.000', 'unit' => '6 unit', 'subtotal' => 'Rp 210.000.000',
        'img' => 'hardware/images/converted/item_22.png', 'modul' => 'yard',
        'desc' => 'Sensor induktif penguncian twistlock dan load cell telemetri untuk auto-update status kontainer (lift-on / lift-off) ke basis data.'
    ],
    [
        'id' => 24, 'nama' => 'Rugged Mobile PDA', 'model' => 'Zebra TC57x Handheld PDA', 'tipe' => 'blueprint', 'layer' => 4,
        'zona' => 'Zona Container Yard', 'zona_id' => 'cfs_yard', 'posisi' => 'Petugas Tallyman & Checker Lapangan',
        'x' => 375, 'y' => 490, 'harga' => 'Rp 16.000.000', 'unit' => '10 unit', 'subtotal' => 'Rp 160.000.000',
        'img' => 'hardware/images/converted/item_24.png', 'modul' => 'kontainer',
        'desc' => 'Perangkat genggam enterprise tahan banting (IP68) untuk inspeksi fisik kontainer rusak (EIR) dan checklist kondisi segel.'
    ],

    // Zona Reefer Yard (Cold Chain)
    [
        'id' => 16, 'nama' => 'Thermal Camera Reefer', 'model' => 'Hikvision DS-2TD2636B-13/P Thermal', 'tipe' => 'blueprint', 'layer' => 3,
        'zona' => 'Zona Reefer Yard', 'zona_id' => 'reefer', 'posisi' => 'Menara Pengawas Reefer Yard',
        'x' => 1018, 'y' => 78, 'harga' => 'Rp 30.000.000', 'unit' => '4 unit', 'subtotal' => 'Rp 120.000.000',
        'img' => 'hardware/images/converted/item_16.png', 'modul' => 'reefer',
        'desc' => 'Kamera pemindai suhu inframerah untuk mendeteksi dini panas abnormal pada kompresor reefer dan mencegah bahaya kebakaran.'
    ],
    [
        'id' => 23, 'nama' => 'Smart Reefer Power Socket', 'model' => 'Marechal 380V/32A DECONTACTOR', 'tipe' => 'live', 'layer' => 2,
        'zona' => 'Zona Reefer Yard', 'zona_id' => 'reefer', 'posisi' => 'Rak Penumpukan Reefer (300 Titik)',
        'x' => 985, 'y' => 155, 'harga' => 'Rp 8.500.000 / titik', 'unit' => '300 titik', 'subtotal' => 'Rp 2.550.000.000',
        'img' => 'hardware/images/converted/item_23.png', 'modul' => 'reefer',
        'desc' => 'Colokan industri 380V dengan meteran listrik Modbus dan sensor suhu kontinu untuk memantau rantai dingin dan alarm listrik trip.'
    ],

    // Zona Intermodal Rail Siding
    [
        'id' => 25, 'nama' => 'Rail Trackside Axle Counter', 'model' => 'Frauscher RSR180 Wheel Sensor', 'tipe' => 'live', 'layer' => 4,
        'zona' => 'Zona Intermodal Rail Siding', 'zona_id' => 'rail', 'posisi' => 'Kepala Rel Ujung Barat & Timur Siding',
        'x' => 225, 'y' => 675, 'harga' => 'Rp 85.000.000', 'unit' => '4 sensor', 'subtotal' => 'Rp 340.000.000',
        'img' => 'hardware/images/converted/item_25.png', 'modul' => 'intermodal',
        'desc' => 'Sensor induktif pada kepala rel untuk menghitung gandar roda KA, estimasi kecepatan, dan konfirmasi kedatangan gerbong otomatis.'
    ],
    [
        'id' => 12, 'nama' => 'Access Point Outdoor', 'model' => 'Ubiquiti U7 Pro Outdoor WiFi 7', 'tipe' => 'blueprint', 'layer' => 2,
        'zona' => 'Zona Intermodal Rail Siding', 'zona_id' => 'rail', 'posisi' => 'Tiang Lampu Yard & Rail Siding (18 Titik)',
        'x' => 750, 'y' => 640, 'harga' => 'Rp 7.200.000', 'unit' => '18 unit', 'subtotal' => 'Rp 129.600.000',
        'img' => 'hardware/images/converted/item_12.png', 'modul' => 'intermodal',
        'desc' => 'Pemancar nirkabel generasi WiFi 7 bersertifikasi IP67 untuk transmisi data real-time ke terminal VMT alat berat dan PDA tallyman.'
    ],
    [
        'id' => 17, 'nama' => 'GPS Tracker Truck & Asset', 'model' => 'Teltonika TAT240 4G LTE', 'tipe' => 'blueprint', 'layer' => 4,
        'zona' => 'Zona Intermodal Rail Siding', 'zona_id' => 'rail', 'posisi' => 'Gerbong Kereta Api & Armada Mitra',
        'x' => 880, 'y' => 675, 'harga' => 'Rp 2.500.000 / unit', 'unit' => '40 unit', 'subtotal' => 'Rp 100.000.000',
        'img' => 'hardware/images/converted/item_17.png', 'modul' => 'trucking',
        'desc' => 'Modul pelacak otonom dengan baterai mandiri untuk memantau posisi rangkaian gerbong kontainer di rute Priok - Cikarang.'
    ],

    // Zona Behandle & Bea Cukai (Customs)
    [
        'id' => 19, 'nama' => 'Gantry Container X-Ray Scanner', 'model' => 'Nuctech MB1215DE (6 MeV)', 'tipe' => 'blueprint', 'layer' => 3,
        'zona' => 'Zona Behandle & Bea Cukai', 'zona_id' => 'customs', 'posisi' => 'Gedung Pemindai X-Ray Jalur Merah',
        'x' => 1135, 'y' => 410, 'harga' => 'Rp 12.500.000.000', 'unit' => '1 sistem', 'subtotal' => 'Rp 12.500.000.000',
        'img' => 'hardware/images/converted/item_19.png', 'modul' => 'customs',
        'desc' => 'Pemindai radiasi sinar-X energi tinggi berdaya tembus baja >300 mm untuk pemeriksaan non-intrusif peti kemas impor Jalur Merah.'
    ],
    [
        'id' => 26, 'nama' => 'Electronic Cargo Smart Seal (E-Seal)', 'model' => 'Jointech JT701 GPS Smart E-Seal', 'tipe' => 'live', 'layer' => 3,
        'zona' => 'Zona Behandle & Bea Cukai', 'zona_id' => 'customs', 'posisi' => 'Pos Stasiun Pemeriksaan Segel Bea Cukai',
        'x' => 1135, 'y' => 538, 'harga' => 'Rp 3.200.000 / unit', 'unit' => '100 unit', 'subtotal' => 'Rp 320.000.000',
        'img' => 'hardware/images/converted/item_26.png', 'modul' => 'customs',
        'desc' => 'Gembok elektronik pintar ber-GPS dan sensor anti-tamper untuk mengamankan kontainer transit pabean terintegrasi CEISA 4.0.'
    ],
    [
        'id' => 4, 'nama' => 'CCTV Yard 8 MP Perimeter', 'model' => 'Hikvision DS-2CD2T87G2H-LI 4K', 'tipe' => 'blueprint', 'layer' => 3,
        'zona' => 'Zona Behandle & Bea Cukai', 'zona_id' => 'customs', 'posisi' => 'Pagar Batas Perimeter Pabean (16 Titik)',
        'x' => 1170, 'y' => 430, 'harga' => 'Rp 6.500.000', 'unit' => '16 unit', 'subtotal' => 'Rp 104.000.000',
        'img' => 'hardware/images/converted/item_4.png', 'modul' => 'customs',
        'desc' => 'Kamera perimeter 4K UHD dengan teknologi ColorVu malam hari untuk menjaga sterilitas kawasan pabean internasional.'
    ],

    // Zona Kantor & Datacenter
    [
        'id' => 13, 'nama' => 'Server NVR & Database YMS', 'model' => 'Hikvision DS-9664NI-M16/R & Rack Server', 'tipe' => 'blueprint', 'layer' => 2,
        'zona' => 'Zona Kantor & Datacenter', 'zona_id' => 'office', 'posisi' => 'Datacenter / Server Room (NOC)',
        'x' => 548, 'y' => 135, 'harga' => 'Rp 102.000.000', 'unit' => '2 unit', 'subtotal' => 'Rp 204.000.000',
        'img' => 'hardware/images/converted/item_13.png', 'modul' => 'settings',
        'desc' => 'Pusat server rekaman CCTV 64 saluran dan server komputasi basis data MySQL sistem operasi pelabuhan kering CIDP YMS.'
    ],
    [
        'id' => 14, 'nama' => 'UPS Online 5000VA Rackmount', 'model' => 'APC Smart-UPS SRT 5000VA (SRT5KRMXLI)', 'tipe' => 'blueprint', 'layer' => 2,
        'zona' => 'Zona Kantor & Datacenter', 'zona_id' => 'office', 'posisi' => 'Datacenter & Ruang Kontrol Gate',
        'x' => 580, 'y' => 135, 'harga' => 'Rp 81.000.000', 'unit' => '4 unit', 'subtotal' => 'Rp 324.000.000',
        'img' => 'hardware/images/converted/item_14.png', 'modul' => 'settings',
        'desc' => 'Pencatu daya cadangan darurat (On-Line Double Conversion 5kVA) untuk menjaga server dan gerbang tetap beroperasi saat listrik PLN padam.'
    ],
    [
        'id' => 11, 'nama' => 'Industrial Network Switch PoE+', 'model' => 'Managed Industrial PoE+ Gigabit Switch', 'tipe' => 'blueprint', 'layer' => 2,
        'zona' => 'Zona Kantor & Datacenter', 'zona_id' => 'office', 'posisi' => 'Core Switch Datacenter & Distribusi Lapangan',
        'x' => 515, 'y' => 135, 'harga' => 'Rp 10.000.000', 'unit' => '12 unit', 'subtotal' => 'Rp 120.000.000',
        'img' => 'hardware/images/converted/item_11.png', 'modul' => 'settings',
        'desc' => 'Switch jaringan gigabit berpelindung logam tahan suhu ekstrem untuk menghubungkan seluruh kamera, access point, dan kontroler sensor.'
    ]
];

// Ringkasan Statistik
$total_facilities_count = 0;
foreach ($zones_data as $z) {
    $total_facilities_count += count($z['facilities']);
}
?>

<div class="space-y-6 animate-fadeIn pb-12">
    <!-- Header Modul & Info PIC -->
    <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-start space-x-4">
            <div class="w-14 h-14 bg-gradient-to-br from-[#002f5e] via-[#004b87] to-[#0170b9] text-white rounded-2xl flex items-center justify-center text-2xl shadow-md flex-shrink-0">
                <i class="fa-solid <?= $denah_info['icon'] ?>"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Denah Terminal 35 Ha & Master Plan CIDP</h1>
                    <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-semibold flex items-center">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span><?= $denah_info['status'] ?>
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 max-w-3xl leading-relaxed">
                    <?= $denah_info['desc'] ?>
                </p>
            </div>
        </div>

        <!-- Kartu PIC Bersama -->
        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5 flex items-center space-x-3 flex-shrink-0">
            <div class="w-10 h-10 rounded-full bg-[#002f5e] text-white flex items-center justify-center font-bold text-sm shadow-xs">
                <i class="fa-solid fa-compass-drafting"></i>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider">Penanggung Jawab Bersama:</span>
                <p class="font-bold text-gray-900 text-xs sm:text-sm"><?= $denah_info['pic'] ?></p>
                <span class="text-[11px] text-[#0170b9] font-semibold block"><?= $denah_info['role'] ?></span>
            </div>
        </div>
    </div>

    <!-- KPI Ringkasan Denah -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3.5 sm:gap-4">
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-xs">
            <span class="text-[11px] font-semibold text-gray-500 block">Luas Total Lahan</span>
            <div class="mt-1 flex items-baseline space-x-1.5">
                <span class="text-xl sm:text-2xl font-bold text-gray-900">35</span>
                <span class="text-xs text-gray-400 font-semibold">Hektar</span>
            </div>
            <p class="text-[10px] text-gray-400 mt-1">Standar Cikarang Dry Port</p>
        </div>

        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-xs">
            <span class="text-[11px] font-semibold text-gray-500 block">Zona Operasional</span>
            <div class="mt-1 flex items-baseline space-x-1.5">
                <span class="text-xl sm:text-2xl font-bold text-[#002f5e]">7</span>
                <span class="text-xs text-gray-400 font-semibold">Zona Fungsional</span>
            </div>
            <p class="text-[10px] text-gray-400 mt-1">Sirkulasi Satu Arah Terpadu</p>
        </div>

        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-xs">
            <span class="text-[11px] font-semibold text-gray-500 block">Fasilitas Lapangan</span>
            <div class="mt-1 flex items-baseline space-x-1.5">
                <span class="text-xl sm:text-2xl font-bold text-[#0170b9]"><?= $total_facilities_count ?></span>
                <span class="text-xs text-gray-400 font-semibold">Fasilitas</span>
            </div>
            <p class="text-[10px] text-gray-400 mt-1">Gate, Yard, Siding, KPPBC & Fasum</p>
        </div>

        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-xs">
            <span class="text-[11px] font-semibold text-gray-500 block">Titik Hardware Terpetakan</span>
            <div class="mt-1 flex items-baseline space-x-1.5">
                <span class="text-xl sm:text-2xl font-bold text-emerald-600">26</span>
                <span class="text-xs text-emerald-600 font-semibold">Hardware</span>
            </div>
            <p class="text-[10px] text-emerald-600 font-medium mt-1">11 Live Telemetri / 15 Blueprint</p>
        </div>

        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-xs col-span-2 lg:col-span-1">
            <span class="text-[11px] font-semibold text-gray-500 block">Sistem Arsitektur</span>
            <div class="mt-1 flex items-baseline space-x-1.5">
                <span class="text-xl sm:text-2xl font-bold text-purple-700">4</span>
                <span class="text-xs text-purple-600 font-semibold">Layer GIS</span>
            </div>
            <p class="text-[10px] text-gray-400 mt-1">Sipil, Utilitas, Keamanan, Sensor</p>
        </div>
    </div>

    <!-- Toolbar Pengendali Layer & Filter Denah -->
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm space-y-4">
        
        <!-- 4 Layer Switcher -->
        <div class="border-b border-gray-100 pb-4">
            <div class="flex items-center justify-between mb-2.5">
                <span class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-layer-group text-[#0170b9] mr-2"></i>
                    Kontrol 4 Layer Arsitektur Denah
                </span>
                <span class="text-[11px] text-gray-400">Klik untuk menyembunyikan/menampilkan layer pada peta</span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5">
                <!-- Layer 1 -->
                <label class="layer-card flex items-center space-x-2.5 p-2.5 rounded-xl border border-gray-200 cursor-pointer bg-slate-50/70 hover:bg-white transition-all select-none">
                    <input type="checkbox" id="layerToggle1" checked onchange="toggleLayer(1)" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                    <div class="min-w-0">
                        <span class="text-xs font-bold text-gray-800 block truncate">Layer 1: Sipil & Bangunan</span>
                        <span class="text-[10px] text-gray-500 block truncate">Jalan, Tapak 25 Fasilitas, Batas</span>
                    </div>
                </label>

                <!-- Layer 2 -->
                <label class="layer-card flex items-center space-x-2.5 p-2.5 rounded-xl border border-gray-200 cursor-pointer bg-slate-50/70 hover:bg-white transition-all select-none">
                    <input type="checkbox" id="layerToggle2" checked onchange="toggleLayer(2)" class="w-4 h-4 text-cyan-600 rounded focus:ring-cyan-500">
                    <div class="min-w-0">
                        <span class="text-xs font-bold text-gray-800 block truncate">Layer 2: Daya & Utilitas</span>
                        <span class="text-[10px] text-gray-500 block truncate">Switch PoE, AP WiFi 7, Smart Socket</span>
                    </div>
                </label>

                <!-- Layer 3 -->
                <label class="layer-card flex items-center space-x-2.5 p-2.5 rounded-xl border border-gray-200 cursor-pointer bg-slate-50/70 hover:bg-white transition-all select-none">
                    <input type="checkbox" id="layerToggle3" checked onchange="toggleLayer(3)" class="w-4 h-4 text-rose-600 rounded focus:ring-rose-500">
                    <div class="min-w-0">
                        <span class="text-xs font-bold text-gray-800 block truncate">Layer 3: Keamanan & Pabean</span>
                        <span class="text-[10px] text-gray-500 block truncate">CCTV 4MP/8MP/PTZ, Gantry X-Ray</span>
                    </div>
                </label>

                <!-- Layer 4 -->
                <label class="layer-card flex items-center space-x-2.5 p-2.5 rounded-xl border border-gray-200 cursor-pointer bg-slate-50/70 hover:bg-white transition-all select-none">
                    <input type="checkbox" id="layerToggle4" checked onchange="toggleLayer(4)" class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500">
                    <div class="min-w-0">
                        <span class="text-xs font-bold text-gray-800 block truncate">Layer 4: Sensor & Otomasi</span>
                        <span class="text-[10px] text-emerald-600 font-semibold block truncate">ANPR, OCR, VGM, RTK, Axle KA</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- Filter Zona & Hardware Type -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <!-- Filter Zona -->
            <div class="flex flex-wrap items-center gap-1.5" id="zoneFilterContainer">
                <span class="text-xs font-bold text-gray-500 mr-1"><i class="fa-solid fa-filter mr-1"></i>Zona:</span>
                <button onclick="filterZone('all')" class="zone-btn px-2.5 py-1 rounded-lg text-xs font-bold bg-[#002f5e] text-white transition-all" data-zone="all">Semua (7)</button>
                <button onclick="filterZone('gate')" class="zone-btn px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all" data-zone="gate">🏗️ Gate</button>
                <button onclick="filterZone('cfs_yard')" class="zone-btn px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all" data-zone="cfs_yard">📦 Yard & CFS</button>
                <button onclick="filterZone('reefer')" class="zone-btn px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all" data-zone="reefer">❄️ Reefer</button>
                <button onclick="filterZone('rail')" class="zone-btn px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all" data-zone="rail">🚂 Rail Siding</button>
                <button onclick="filterZone('customs')" class="zone-btn px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all" data-zone="customs">🔍 Bea Cukai</button>
                <button onclick="filterZone('office')" class="zone-btn px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all" data-zone="office">🏢 Kantor & M&R</button>
                <button onclick="filterZone('public')" class="zone-btn px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all" data-zone="public">🛐 Fasum</button>
            </div>

            <!-- Filter Tipe Hardware -->
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-gray-500"><i class="fa-solid fa-microchip mr-1"></i>Pin:</span>
                <button onclick="filterHardwareType('all')" id="btnHwAll" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-[#0170b9] text-white transition-all shadow-xs">Semua (26)</button>
                <button onclick="filterHardwareType('live')" id="btnHwLive" class="px-2.5 py-1 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-all">🟢 Live (11)</button>
                <button onclick="filterHardwareType('blueprint')" id="btnHwBlueprint" class="px-2.5 py-1 rounded-lg text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition-all">🔵 Blueprint (15)</button>
            </div>
        </div>
    </div>

    <!-- AREA UTAMA: PETA DENAH SVG INTERAKTIF & INSPECTOR DRAWER -->
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6 items-start">
        
        <!-- Kanvas Peta SVG 35 Ha (Span 3 Kolom) -->
        <div class="xl:col-span-3 bg-slate-900 rounded-2xl p-4 sm:p-5 shadow-lg border border-slate-800 relative overflow-hidden">
            
            <!-- Map Floating Controls & Legend Overlay -->
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3 pb-3 border-b border-slate-800">
                <div class="flex items-center space-x-3">
                    <span class="text-xs font-bold tracking-wider text-slate-300 uppercase flex items-center">
                        <i class="fa-solid fa-compass text-amber-400 mr-2 text-sm"></i>
                        CIDP 35 Ha Master Plan Map (Skala 1:1.000)
                    </span>
                    <span class="text-[10px] text-slate-400 bg-slate-800/80 px-2 py-0.5 rounded border border-slate-700">
                        <i class="fa-solid fa-arrow-up text-blue-400 mr-1"></i>UTARA (NORTH)
                    </span>
                </div>
                
                <div class="flex items-center space-x-3 text-[11px] text-slate-300">
                    <span class="flex items-center">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping mr-1.5"></span>
                        <strong class="text-emerald-300">Pin Hijau:</strong> Live Software (11)
                    </span>
                    <span class="flex items-center">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-400 mr-1.5"></span>
                        <strong class="text-indigo-300">Pin Biru:</strong> Blueprint Fisik (15)
                    </span>
                    <button onclick="resetMapView()" class="px-2 py-0.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded text-[10px] font-mono border border-slate-700 transition" title="Reset Tampilan Peta">
                        <i class="fa-solid fa-rotate-left mr-1"></i>Reset
                    </button>
                </div>
            </div>

            <!-- SVG Container -->
            <div class="w-full overflow-x-auto rounded-xl bg-slate-950 border border-slate-800/80" id="mapSvgWrapper">
                <svg id="denahSvg" viewBox="0 0 1200 760" class="w-full h-auto min-w-[900px] select-none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <!-- Grid Pattern Background -->
                        <pattern id="gridPattern" width="40" height="40" patternUnits="userSpaceOnUse">
                            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#1e293b" stroke-width="0.75" />
                        </pattern>
                        
                        <!-- Asphalt Road Texture -->
                        <pattern id="roadTexture" width="20" height="20" patternUnits="userSpaceOnUse">
                            <rect width="20" height="20" fill="#1e2430" />
                            <circle cx="5" cy="5" r="0.7" fill="#334155" />
                            <circle cx="15" cy="15" r="0.7" fill="#334155" />
                        </pattern>

                        <!-- Pulse Filter for Live Pins -->
                        <filter id="glowGreen" x="-20%" y="-20%" width="140%" height="140%">
                            <feGaussianBlur stdDeviation="3" result="blur" />
                            <feComposite in="SourceGraphic" in2="blur" operator="over" />
                        </filter>
                    </defs>

                    <!-- Background Grid -->
                    <rect width="1200" height="760" fill="#0f172a" />
                    <rect width="1200" height="760" fill="url(#gridPattern)" />

                    <!-- Perimeter Boundary Pagar 35 Ha -->
                    <rect x="20" y="20" width="1160" height="720" rx="16" fill="none" stroke="#334155" stroke-width="3" stroke-dasharray="8 4" />
                    <text x="35" y="42" fill="#64748b" font-size="10" font-family="monospace" font-weight="bold">BATAS PERIMETER KAWASAN DRY PORT (35 HA) — PAGAR STERIL KEPABEANAN</text>

                    <!-- ======================================================= -->
                    <!-- LAYER 1: TATA LETAK SIPIL, JALAN & BANGUNAN FASILITAS -->
                    <!-- ======================================================= -->
                    <g id="svgLayer1" class="transition-opacity duration-300">
                        
                        <!-- Jaringan Sirkulasi Jalan Aspal Truk Trailer (One-Way Circuit) -->
                        <!-- Jalan Utama Masuk (Inbound Arteri) -->
                        <path d="M 20 160 L 360 160 L 360 600 L 1140 600" fill="none" stroke="url(#roadTexture)" stroke-width="50" stroke-linecap="round" />
                        <path d="M 20 160 L 360 160 L 360 600 L 1140 600" fill="none" stroke="#475569" stroke-width="1.5" stroke-dasharray="10 10" />

                        <!-- Jalan Sirkulasi Yard & Keluar (Outbound Ring) -->
                        <path d="M 360 260 L 940 260 L 940 580 L 360 580 Z" fill="none" stroke="url(#roadTexture)" stroke-width="36" stroke-linejoin="round" />
                        <path d="M 360 260 L 940 260 L 940 580 L 360 580 Z" fill="none" stroke="#cbd5e1" stroke-width="1" stroke-dasharray="8 8" opacity="0.4" />

                        <!-- Jalan Keluar Utama (Outbound Road ke Gerbang) -->
                        <path d="M 360 130 L 20 130" fill="none" stroke="url(#roadTexture)" stroke-width="40" />

                        <!-- --------------------------------------------------- -->
                        <!-- ZONA 1: GATE ENTRANCE AREA (Barat Laut / NW) -->
                        <!-- --------------------------------------------------- -->
                        <g id="zone_gate" class="zone-element cursor-pointer" onclick="showZoneDetails('gate')">
                            <!-- Tapak Zona Gate -->
                            <rect x="35" y="60" width="295" height="175" rx="12" fill="#0369a1" fill-opacity="0.12" stroke="#0284c7" stroke-width="1.5" />
                            <text x="45" y="80" fill="#38bdf8" font-size="11" font-weight="bold"><tspan font-family="sans-serif">🏗️ ZONA 1: MAIN GATE ENTRANCE COMPLEX</tspan></text>
                            
                            <!-- Parkir Truk Antrian -->
                            <rect id="fac_f_truck_queue" x="45" y="90" width="80" height="65" rx="6" fill="#1e293b" stroke="#0ea5e9" stroke-width="1" onclick="showFacilityDetails('f_truck_queue', event)" />
                            <text x="50" y="110" fill="#94a3b8" font-size="8" font-weight="bold">PARKIR ANTRIAN</text>
                            <text x="50" y="122" fill="#cbd5e1" font-size="7">Kapasitas: 40 Truk</text>
                            <!-- Garis Slot Parkir Truk -->
                            <line x1="75" y1="92" x2="75" y2="153" stroke="#334155" stroke-dasharray="3 3" />
                            <line x1="100" y1="92" x2="100" y2="153" stroke="#334155" stroke-dasharray="3 3" />

                            <!-- Security Post 24 Jam -->
                            <rect id="fac_f_security_gate" x="135" y="86" width="48" height="52" rx="6" fill="#0f172a" stroke="#0284c7" stroke-width="1.2" onclick="showFacilityDetails('f_security_gate', event)" />
                            <text x="140" y="99" fill="#38bdf8" font-size="7.5" font-weight="bold">POS SATPAM</text>
                            <text x="140" y="109" fill="#94a3b8" font-size="6.5">Security 24 Jam</text>
                            <rect x="139" y="116" width="40" height="18" rx="3" fill="#1e293b" stroke="#334155" opacity="0.6" />

                            <!-- Ruang Istirahat Driver (Rest Area) -->
                            <rect id="fac_f_driver_rest" x="45" y="165" width="80" height="40" rx="6" fill="#0f172a" stroke="#10b981" stroke-width="1" onclick="showFacilityDetails('f_driver_rest', event)" />
                            <text x="50" y="180" fill="#6ee7b7" font-size="7.5" font-weight="bold">REST AREA DRIVER</text>
                            <text x="50" y="192" fill="#94a3b8" font-size="6.5">Ruang AC, Kasur, Air Minum</text>

                            <!-- Gatehouse Inbound & Weighbridge -->
                            <rect id="fac_f_gate_in" x="190" y="146" width="135" height="52" rx="6" fill="#1e3a8a" stroke="#60a5fa" stroke-width="1.5" onclick="showFacilityDetails('f_gate_in', event)" />
                            <text x="198" y="159" fill="#ffffff" font-size="8" font-weight="bold">GATE INBOUND (LANE 1 & 2)</text>
                            <text x="198" y="169" fill="#93c5fd" font-size="7">Portal Otomasi & Verifikasi VGM 80t</text>
                            <line x1="195" y1="184" x2="320" y2="184" stroke="#3b82f6" stroke-width="1" stroke-dasharray="3 3" opacity="0.6" />

                            <!-- Gatehouse Outbound -->
                            <rect id="fac_f_gate_out" x="190" y="88" width="135" height="32" rx="6" fill="#1e293b" stroke="#94a3b8" stroke-width="1.2" onclick="showFacilityDetails('f_gate_out', event)" />
                            <text x="198" y="101" fill="#e2e8f0" font-size="8" font-weight="bold">GATE OUTBOUND (LANE 3 & 4)</text>
                            <text x="198" y="112" fill="#94a3b8" font-size="7">ANPR + RFID Exit Pass</text>
                        </g>

                        <!-- --------------------------------------------------- -->
                        <!-- ZONA 6 & 7: KANTOR UTAMA & FASILITAS UMUM (Utara Tengah) -->
                        <!-- --------------------------------------------------- -->
                        <g id="zone_office" class="zone-element cursor-pointer" onclick="showZoneDetails('office')">
                            <rect x="345" y="60" width="435" height="175" rx="12" fill="#6d28d9" fill-opacity="0.10" stroke="#7c3aed" stroke-width="1.5" />
                            <text x="355" y="80" fill="#c4b5fd" font-size="11" font-weight="bold">🏢 ZONA 6 & 7: ADMINISTRATION, DATACENTER & FASUM</text>

                            <!-- Kantor Utama MTI (Admin Building) -->
                            <rect id="fac_f_admin_office" x="355" y="90" width="130" height="60" rx="8" fill="#1e1b4b" stroke="#8b5cf6" stroke-width="1.5" onclick="showFacilityDetails('f_admin_office', event)" />
                            <text x="365" y="108" fill="#ffffff" font-size="8.5" font-weight="bold">KANTOR UTAMA (MTI)</text>
                            <text x="365" y="120" fill="#ddd6fe" font-size="7.5">Administration & Billing Office</text>
                            <text x="365" y="132" fill="#a78bfa" font-size="7">Lantai 1-2 • Manajemen Terminal</text>

                            <!-- Datacenter / Server Room (NOC) -->
                            <rect id="fac_f_datacenter" x="495" y="90" width="105" height="60" rx="8" fill="#0f172a" stroke="#a855f7" stroke-width="1.5" onclick="showFacilityDetails('f_datacenter', event)" />
                            <text x="503" y="104" fill="#f3e8ff" font-size="8" font-weight="bold">DATACENTER / NOC</text>
                            <text x="503" y="115" fill="#d8b4fe" font-size="7">Core YMS Server & DB</text>
                            <rect x="501" y="123" width="93" height="23" rx="4" fill="#1e1b4b" stroke="#6b21a8" stroke-width="0.8" />

                            <!-- Ruang Meeting & Training -->
                            <rect id="fac_f_meeting_room" x="610" y="90" width="80" height="60" rx="6" fill="#1e293b" stroke="#7c3aed" stroke-width="1" onclick="showFacilityDetails('f_meeting_room', event)" />
                            <text x="615" y="108" fill="#e2e8f0" font-size="7.5" font-weight="bold">MEETING &</text>
                            <text x="615" y="119" fill="#e2e8f0" font-size="7.5" font-weight="bold">TRAINING ROOM</text>
                            <text x="615" y="132" fill="#94a3b8" font-size="6.5">Pelatihan Operator</text>

                            <!-- Parkir Karyawan & Tamu -->
                            <rect id="fac_f_parking_staff" x="700" y="90" width="70" height="60" rx="6" fill="#1e293b" stroke="#64748b" stroke-width="1" onclick="showFacilityDetails('f_parking_staff', event)" />
                            <text x="706" y="110" fill="#94a3b8" font-size="7.5" font-weight="bold">PARKIR STAF</text>
                            <text x="706" y="122" fill="#64748b" font-size="6.5">& TAMU DINAS</text>

                            <!-- Fasilitas Umum (Baris Bawah: Masjid, Kantin, Koperasi, Klinik, Damkar) -->
                            <!-- Musholla / Masjid Al-Hidayah -->
                            <rect id="fac_f_musholla" x="355" y="160" width="85" height="45" rx="6" fill="#064e3b" stroke="#10b981" stroke-width="1.2" onclick="showFacilityDetails('f_musholla', event)" />
                            <text x="362" y="177" fill="#a7f3d0" font-size="8" font-weight="bold">MASJID AL-HIDAYAH</text>
                            <text x="362" y="189" fill="#6ee7b7" font-size="7">Kapasitas 150 Jamaah</text>

                            <!-- Kantin / Warung Makan 🍛 -->
                            <rect id="fac_f_kantin" x="450" y="160" width="80" height="45" rx="6" fill="#1e293b" stroke="#f59e0b" stroke-width="1" onclick="showFacilityDetails('f_kantin', event)" />
                            <text x="456" y="177" fill="#fcd34d" font-size="7.5" font-weight="bold">KANTIN MAKAN 🍛</text>
                            <text x="456" y="189" fill="#94a3b8" font-size="6.5">Pujasera Driver & Staf</text>

                            <!-- Koperasi Karyawan 🏪 -->
                            <rect id="fac_f_koperasi" x="540" y="160" width="65" height="45" rx="6" fill="#1e293b" stroke="#10b981" stroke-width="1" onclick="showFacilityDetails('f_koperasi', event)" />
                            <text x="545" y="177" fill="#6ee7b7" font-size="7" font-weight="bold">KOPERASI 🏪</text>
                            <text x="545" y="189" fill="#94a3b8" font-size="6.5">Toko & APD</text>

                            <!-- Klinik P3K -->
                            <rect id="fac_f_klinik" x="615" y="160" width="65" height="45" rx="6" fill="#1e293b" stroke="#ef4444" stroke-width="1" onclick="showFacilityDetails('f_klinik', event)" />
                            <text x="620" y="177" fill="#fca5a5" font-size="7" font-weight="bold">KLINIK P3K</text>
                            <text x="620" y="189" fill="#94a3b8" font-size="6.5">Pos Medis K3</text>

                            <!-- Pos Damkar & Hydrant -->
                            <rect id="fac_f_damkar" x="690" y="160" width="80" height="45" rx="6" fill="#450a0a" stroke="#dc2626" stroke-width="1" onclick="showFacilityDetails('f_damkar', event)" />
                            <text x="696" y="177" fill="#f87171" font-size="7" font-weight="bold">POS DAMKAR</text>
                            <text x="696" y="189" fill="#fca5a5" font-size="6.5">Fire Station & Hydrant</text>
                        </g>

                        <!-- --------------------------------------------------- -->
                        <!-- ZONA 3: REEFER COLD CHAIN YARD (Timur Laut / NE) -->
                        <!-- --------------------------------------------------- -->
                        <g id="zone_reefer" class="zone-element cursor-pointer" onclick="showZoneDetails('reefer')">
                            <rect x="795" y="60" width="375" height="175" rx="12" fill="#0891b2" fill-opacity="0.12" stroke="#06b6d4" stroke-width="1.5" />
                            <text x="805" y="80" fill="#22d3ee" font-size="11" font-weight="bold">❄️ ZONA 3: COLD CHAIN & REEFER YARD (2 HA)</text>

                            <!-- Menara Pengawas Thermal Reefer -->
                            <polygon points="1013,85 1023,85 1018,72" fill="#0e7490" stroke="#06b6d4" stroke-width="1" />

                            <!-- Rak Penumpukan Reefer (300 Plugs) -->
                            <rect id="fac_f_reefer_racks" x="805" y="90" width="220" height="115" rx="8" fill="#164e63" stroke="#22d3ee" stroke-width="1.2" onclick="showFacilityDetails('f_reefer_racks', event)" />
                            <text x="815" y="108" fill="#ffffff" font-size="8.5" font-weight="bold">REEFER RACKS (300 PLUGS)</text>
                            <text x="815" y="120" fill="#a5f3fc" font-size="7.5">300 Titik Colokan Smart Socket 380V/32A</text>
                            
                            <!-- Grid Titik Colokan Visual Reefer -->
                            <g fill="#06b6d4" opacity="0.7">
                                <circle cx="820" cy="138" r="2.5" /><circle cx="835" cy="138" r="2.5" /><circle cx="850" cy="138" r="2.5" /><circle cx="865" cy="138" r="2.5" /><circle cx="880" cy="138" r="2.5" /><circle cx="895" cy="138" r="2.5" /><circle cx="910" cy="138" r="2.5" /><circle cx="925" cy="138" r="2.5" />
                                <circle cx="820" cy="155" r="2.5" /><circle cx="835" cy="155" r="2.5" /><circle cx="850" cy="155" r="2.5" /><circle cx="865" cy="155" r="2.5" /><circle cx="880" cy="155" r="2.5" /><circle cx="895" cy="155" r="2.5" /><circle cx="910" cy="155" r="2.5" /><circle cx="925" cy="155" r="2.5" />
                                <circle cx="820" cy="172" r="2.5" /><circle cx="835" cy="172" r="2.5" /><circle cx="850" cy="172" r="2.5" /><circle cx="865" cy="172" r="2.5" /><circle cx="880" cy="172" r="2.5" /><circle cx="895" cy="172" r="2.5" /><circle cx="910" cy="172" r="2.5" /><circle cx="925" cy="172" r="2.5" />
                            </g>
                            <text x="815" y="193" fill="#67e8f9" font-size="7">Suhu Operasional: -20°C s/d +15°C • Auto Telemetry</text>

                            <!-- Reefer Control Room -->
                            <rect id="fac_f_reefer_control" x="1035" y="90" width="125" height="50" rx="6" fill="#0f172a" stroke="#0891b2" stroke-width="1.2" onclick="showFacilityDetails('f_reefer_control', event)" />
                            <text x="1042" y="106" fill="#e0f2fe" font-size="7.5" font-weight="bold">REEFER CONTROL ROOM</text>
                            <text x="1042" y="118" fill="#7dd3fc" font-size="6.5">Monitoring Suhu 24/7</text>
                            <text x="1042" y="129" fill="#94a3b8" font-size="6.5">Alarm Overheat & Trip</text>

                            <!-- Genset Shelter (1500 kVA) -->
                            <rect id="fac_f_genset_shelter" x="1035" y="150" width="125" height="55" rx="6" fill="#0f172a" stroke="#f59e0b" stroke-width="1.2" onclick="showFacilityDetails('f_genset_shelter', event)" />
                            <text x="1042" y="167" fill="#fde68a" font-size="7.5" font-weight="bold">GENSET SHELTER</text>
                            <text x="1042" y="179" fill="#fcd34d" font-size="6.5">Caterpillar 1.500 kVA</text>
                            <text x="1042" y="190" fill="#94a3b8" font-size="6.5">Backup Darurat Otomatis</text>
                        </g>

                        <!-- --------------------------------------------------- -->
                        <!-- ZONA CFS, GUDANG TRANSIT & WORKSHOP M&R (Tengah Barat) -->
                        <!-- --------------------------------------------------- -->
                        <g id="zone_cfs" class="zone-element cursor-pointer" onclick="showZoneDetails('cfs_yard')">
                            <!-- CFS Warehouse -->
                            <rect id="fac_f_cfs" x="35" y="260" width="165" height="110" rx="8" fill="#1e293b" stroke="#6366f1" stroke-width="1.5" onclick="showFacilityDetails('f_cfs', event)" />
                            <text x="45" y="280" fill="#ffffff" font-size="8.5" font-weight="bold">CONTAINER FREIGHT STATION</text>
                            <text x="45" y="292" fill="#a5b4fc" font-size="7.5">Gudang LCL Stripping-Stuffing (4.000 m²)</text>
                            <rect x="45" y="302" width="145" height="20" fill="#0f172a" rx="4" />
                            <text x="50" y="315" fill="#cbd5e1" font-size="6.5">8 Pintu Loading Dock Truk Kontainer</text>
                            <text x="45" y="340" fill="#94a3b8" font-size="6.5">Fasilitas Forklift & Pallet Staging</text>

                            <!-- Warehouse / Gudang Transit -->
                            <rect id="fac_f_warehouse" x="35" y="380" width="165" height="85" rx="8" fill="#1e293b" stroke="#818cf8" stroke-width="1.2" onclick="showFacilityDetails('f_warehouse', event)" />
                            <text x="45" y="400" fill="#ffffff" font-size="8.5" font-weight="bold">GUDANG TRANSIT DISTRIBUSI</text>
                            <text x="45" y="412" fill="#c7d2fe" font-size="7">Temporary High-Value Storage</text>
                            <text x="45" y="430" fill="#94a3b8" font-size="6.5">Cross-docking Muatan Pabrik Cikarang</text>

                            <!-- Workshop / Bengkel M&R Alat Berat -->
                            <rect id="fac_f_workshop_mr" x="35" y="475" width="165" height="95" rx="8" fill="#1e293b" stroke="#f59e0b" stroke-width="1.2" onclick="showFacilityDetails('f_workshop_mr', event)" />
                            <text x="45" y="495" fill="#fef3c7" font-size="8.5" font-weight="bold">WORKSHOP & M&R (BENGKEL)</text>
                            <text x="45" y="507" fill="#fcd34d" font-size="7">Perawatan Alat Berat & Repair Box</text>
                            <text x="45" y="525" fill="#94a3b8" font-size="6.5">Service Bay Reach Stacker & Forklift</text>
                            <text x="45" y="540" fill="#94a3b8" font-size="6.5">Penyimpanan Suku Cadang & Pelumas</text>
                        </g>

                        <!-- --------------------------------------------------- -->
                        <!-- ZONA 2: CONTAINER YARD (Tengah & Tengah Timur - 15 Ha) -->
                        <!-- --------------------------------------------------- -->
                        <g id="zone_yard" class="zone-element cursor-pointer" onclick="showZoneDetails('cfs_yard')">
                            <!-- Tapak Utama Lapangan Penumpukan -->
                            <rect x="220" y="260" width="740" height="310" rx="12" fill="#1e1b4b" fill-opacity="0.15" stroke="#4f46e5" stroke-width="1.5" />
                            <text x="235" y="280" fill="#818cf8" font-size="11" font-weight="bold">📦 ZONA 2: CONTAINER STACKING YARD (15 HA)</text>

                            <!-- Empty Container Depot -->
                            <rect id="fac_f_empty_depot" x="235" y="295" width="130" height="260" rx="8" fill="#1e293b" stroke="#64748b" stroke-width="1.2" onclick="showFacilityDetails('f_empty_depot', event)" />
                            <text x="245" y="315" fill="#f1f5f9" font-size="8.5" font-weight="bold">EMPTY CONTAINER DEPOT</text>
                            <text x="245" y="327" fill="#94a3b8" font-size="7">Kapasitas: 2.500 TEUs</text>
                            <!-- Visualisasi Tumpukan Empty -->
                            <g fill="#475569" opacity="0.6">
                                <rect x="245" y="340" width="110" height="25" rx="3" />
                                <rect x="245" y="375" width="110" height="25" rx="3" />
                                <rect x="245" y="410" width="110" height="25" rx="3" />
                                <rect x="245" y="445" width="110" height="25" rx="3" />
                                <rect x="245" y="480" width="110" height="25" rx="3" />
                                <rect x="245" y="515" width="110" height="25" rx="3" />
                            </g>
                            <text x="250" y="357" fill="#e2e8f0" font-size="7">Empty 20ft & 40ft (Tier 1-5)</text>

                            <!-- BLOK A: Laden Ekspor Yard -->
                            <rect x="385" y="295" width="165" height="120" rx="8" fill="#0f172a" stroke="#2563eb" stroke-width="1.2" />
                            <text x="395" y="315" fill="#93c5fd" font-size="8.5" font-weight="bold">BLOK A (LADEN EKSPOR)</text>
                            <text x="395" y="327" fill="#60a5fa" font-size="7">40ft High Cube • Bay 01-16</text>
                            <rect x="395" y="337" width="145" height="65" fill="#1e3a8a" rx="4" opacity="0.5" />
                            <text x="405" y="360" fill="#ffffff" font-size="7.5" font-mono>STACKING TIER 1-4</text>
                            <text x="405" y="375" fill="#93c5fd" font-size="6.5">Reach Stacker RTK Area</text>

                            <!-- Tiang Lampu & CCTV Antar-Blok -->
                            <line x1="557" y1="295" x2="557" y2="415" stroke="#334155" stroke-width="1" stroke-dasharray="3 3" />

                            <!-- BLOK B: Laden Impor Yard -->
                            <rect x="565" y="295" width="165" height="120" rx="8" fill="#0f172a" stroke="#0284c7" stroke-width="1.2" />
                            <text x="575" y="315" fill="#7dd3fc" font-size="8.5" font-weight="bold">BLOK B (LADEN IMPOR)</text>
                            <text x="575" y="327" fill="#38bdf8" font-size="7">40ft High Cube • Bay 01-16</text>
                            <rect x="575" y="337" width="145" height="65" fill="#0369a1" rx="4" opacity="0.5" />
                            <text x="585" y="360" fill="#ffffff" font-size="7.5" font-mono>STACKING TIER 1-4</text>
                            <text x="585" y="375" fill="#7dd3fc" font-size="6.5">Jalur Pelabuhan Tg. Priok</text>

                            <!-- Jalur Transfer Alat Berat (Reach Stacker Maneuvering Aisle) -->
                            <line x1="385" y1="425" x2="730" y2="425" stroke="#334155" stroke-width="1.5" stroke-dasharray="4 4" />
                            <text x="390" y="423" fill="#64748b" font-size="6" font-family="monospace">MANEUVER RS (BAY TRANSFER)</text>

                            <!-- BLOK C: Domestik & General Cargo -->
                            <rect x="385" y="435" width="165" height="120" rx="8" fill="#0f172a" stroke="#10b981" stroke-width="1.2" />
                            <text x="395" y="455" fill="#6ee7b7" font-size="8.5" font-weight="bold">BLOK C (DOMESTIK / 20FT)</text>
                            <text x="395" y="467" fill="#34d399" font-size="7">20ft Standard • Bay 01-12</text>
                            <rect x="395" y="477" width="145" height="65" fill="#065f46" rx="4" opacity="0.5" />
                            <text x="405" y="500" fill="#ffffff" font-size="7.5" font-mono>DISTRIBUSI PULAU JAWA</text>
                            <text x="405" y="515" fill="#a7f3d0" font-size="6.5">Kereta Api & Trucking Lokal</text>

                            <!-- BLOK D: Buffer & Staging Yard -->
                            <rect x="565" y="435" width="165" height="120" rx="8" fill="#0f172a" stroke="#8b5cf6" stroke-width="1.2" />
                            <text x="575" y="455" fill="#c4b5fd" font-size="8.5" font-weight="bold">BLOK D (BUFFER YARD)</text>
                            <text x="575" y="467" fill="#a78bfa" font-size="7">Penyangga Alih Muat Siding KA</text>
                            <rect x="575" y="477" width="145" height="65" fill="#4c1d95" rx="4" opacity="0.5" />
                            <text x="585" y="500" fill="#ffffff" font-size="7.5" font-mono>TRANSIT REL KE LAPANGAN</text>
                            <text x="585" y="515" fill="#c4b5fd" font-size="6.5">Kesiapan Muatan Kereta Api</text>

                            <!-- BLOK E: Dangerous Goods (DG Yard) -->
                            <rect x="745" y="295" width="200" height="260" rx="8" fill="#450a0a" stroke="#ef4444" stroke-width="1.5" />
                            <text x="755" y="315" fill="#fca5a5" font-size="8.5" font-weight="bold">BLOK E: HAZARDOUS CARGO (DG)</text>
                            <text x="755" y="327" fill="#f87171" font-size="7">Dangerous Goods Yard (ISO 14001)</text>
                            <rect x="755" y="340" width="180" height="200" rx="6" fill="#7f1d1d" opacity="0.4" stroke="#dc2626" stroke-dasharray="4 2" />
                            <text x="765" y="370" fill="#ffffff" font-size="7.5">Klasifikasi IMO / UN Hazard</text>
                            <text x="765" y="385" fill="#fecaca" font-size="6.5">• Drainase Khusus Bahan Kimia</text>
                            <text x="765" y="400" fill="#fecaca" font-size="6.5">• Penahan Tumpahan (Spill Basin)</text>
                            <text x="765" y="415" fill="#fecaca" font-size="6.5">• Pemadam Busa Otomatis</text>
                            <text x="765" y="430" fill="#fecaca" font-size="6.5">• Jarak Aman Khusus 50 Meter</text>
                        </g>

                        <!-- --------------------------------------------------- -->
                        <!-- ZONA 5: BEHANDLE & BEA CUKAI (Timur / East - 3 Ha) -->
                        <!-- --------------------------------------------------- -->
                        <g id="zone_customs" class="zone-element cursor-pointer" onclick="showZoneDetails('customs')">
                            <rect x="975" y="260" width="195" height="310" rx="12" fill="#7f1d1d" fill-opacity="0.12" stroke="#dc2626" stroke-width="1.5" />
                            <text x="985" y="280" fill="#f87171" font-size="11" font-weight="bold">🔍 ZONA 5: BEA CUKAI & BEHANDLE</text>

                            <!-- Kantor Bea Cukai (KPPBC) -->
                            <rect id="fac_f_kppbc" x="985" y="295" width="175" height="55" rx="6" fill="#1e293b" stroke="#ef4444" stroke-width="1.2" onclick="showFacilityDetails('f_kppbc', event)" />
                            <text x="995" y="315" fill="#ffffff" font-size="8.5" font-weight="bold">KANTOR BEA CUKAI (KPPBC)</text>
                            <text x="995" y="327" fill="#fca5a5" font-size="7">Pelayanan Pabean & CEISA 4.0</text>
                            <text x="995" y="340" fill="#94a3b8" font-size="6.5">Ruang Pejabat Pemeriksa Fisik</text>

                            <!-- Behandle Area & Gantry X-Ray Building -->
                            <rect id="fac_f_behandle_xray" x="985" y="360" width="175" height="90" rx="6" fill="#450a0a" stroke="#dc2626" stroke-width="1.5" onclick="showFacilityDetails('f_behandle_xray', event)" />
                            <text x="995" y="376" fill="#ffffff" font-size="8.5" font-weight="bold">BEHANDLE & X-RAY BUILDING</text>
                            <text x="995" y="388" fill="#f87171" font-size="7">Gantry X-Ray 6 MeV (Nuctech)</text>
                            <rect x="995" y="398" width="125" height="24" fill="#7f1d1d" rx="3" />
                            <text x="1003" y="413" fill="#ffffff" font-size="7" font-mono>PEMINDAI JALUR MERAH</text>
                            <text x="995" y="438" fill="#fca5a5" font-size="6.5">Kapasitas: 25 Truk/Jam • Tanpa Buka Box</text>

                            <!-- Quarantine Inspection Room -->
                            <rect id="fac_f_quarantine" x="985" y="460" width="175" height="50" rx="6" fill="#1e293b" stroke="#ea580c" stroke-width="1" onclick="showFacilityDetails('f_quarantine', event)" />
                            <text x="995" y="478" fill="#fed7aa" font-size="8" font-weight="bold">RUANG KARANTINA</text>
                            <text x="995" y="490" fill="#fdba74" font-size="7">Balai Karantina Hewan & Tumbuhan</text>
                            <text x="995" y="501" fill="#94a3b8" font-size="6.5">Inspeksi Sanitari & Fitosanitari (SPS)</text>

                            <!-- Pos Inspeksi E-Seal -->
                            <rect x="985" y="520" width="175" height="35" rx="6" fill="#0f172a" stroke="#eab308" stroke-width="1" />
                            <text x="995" y="534" fill="#fef08a" font-size="7.5" font-weight="bold">STASIUN E-SEAL BEA CUKAI</text>
                            <text x="995" y="545" fill="#fde047" font-size="6.5">Verifikasi Smart GPS Lock Jointech</text>
                        </g>

                        <!-- --------------------------------------------------- -->
                        <!-- ZONA 4: INTERMODAL RAIL SIDING (Selatan / 400 Meter) -->
                        <!-- --------------------------------------------------- -->
                        <g id="zone_rail" class="zone-element cursor-pointer" onclick="showZoneDetails('rail')">
                            <!-- Tapak Zona Rel -->
                            <rect x="35" y="590" width="1135" height="135" rx="12" fill="#78350f" fill-opacity="0.12" stroke="#d97706" stroke-width="1.5" />
                            <text x="45" y="612" fill="#fbbf24" font-size="11" font-weight="bold">🚂 ZONA 4: INTERMODAL RAIL SIDING (JALUR GANDA 400 METER) — KORIDOR TJ. PRIOK</text>

                            <!-- Rail Office / Stasiun Operator KA -->
                            <rect id="fac_f_rail_office" x="45" y="625" width="150" height="85" rx="8" fill="#1e293b" stroke="#f59e0b" stroke-width="1.2" onclick="showFacilityDetails('f_rail_office', event)" />
                            <text x="55" y="645" fill="#ffffff" font-size="8.5" font-weight="bold">RAIL OFFICE / OPERATOR</text>
                            <text x="55" y="657" fill="#fde68a" font-size="7.5">Kantor Operasi KAI Logistik</text>
                            <text x="55" y="675" fill="#94a3b8" font-size="6.5">Pusat Sinyal & Jadwal KA Kontainer</text>
                            <text x="55" y="690" fill="#94a3b8" font-size="6.5">Monitoring Rangkaian 30 Flatcars</text>

                            <!-- Loading Ramp (Area Transfer Rel ke Lapangan) -->
                            <rect id="fac_f_loading_ramp" x="210" y="625" width="945" height="30" rx="4" fill="#334155" stroke="#64748b" stroke-width="1" onclick="showFacilityDetails('f_loading_ramp', event)" />
                            <text x="220" y="644" fill="#f8fafc" font-size="8" font-weight="bold">LOADING RAMP (AREA BONGKAR MUAT GERBONG DATAR RTG / REACH STACKER)</text>

                            <!-- Jalur Rel KA Ganda (Track 1 & Track 2) -->
                            <!-- Track 1 -->
                            <line x1="210" y1="675" x2="1155" y2="675" stroke="#71717a" stroke-width="5" />
                            <line x1="210" y1="671" x2="1155" y2="671" stroke="#f4f4f5" stroke-width="1.5" />
                            <line x1="210" y1="679" x2="1155" y2="679" stroke="#f4f4f5" stroke-width="1.5" />
                            <!-- Bantalan Rel Kayu/Beton (Ties) -->
                            <g stroke="#52525b" stroke-width="2">
                                <line x1="250" y1="668" x2="250" y2="682" /><line x1="300" y1="668" x2="300" y2="682" />
                                <line x1="350" y1="668" x2="350" y2="682" /><line x1="400" y1="668" x2="400" y2="682" />
                                <line x1="450" y1="668" x2="450" y2="682" /><line x1="500" y1="668" x2="500" y2="682" />
                                <line x1="550" y1="668" x2="550" y2="682" /><line x1="600" y1="668" x2="600" y2="682" />
                                <line x1="650" y1="668" x2="650" y2="682" /><line x1="700" y1="668" x2="700" y2="682" />
                                <line x1="750" y1="668" x2="750" y2="682" /><line x1="800" y1="668" x2="800" y2="682" />
                                <line x1="850" y1="668" x2="850" y2="682" /><line x1="900" y1="668" x2="900" y2="682" />
                                <line x1="950" y1="668" x2="950" y2="682" /><line x1="1000" y1="668" x2="1000" y2="682" />
                                <line x1="1050" y1="668" x2="1050" y2="682" /><line x1="1100" y1="668" x2="1100" y2="682" />
                            </g>
                            <text x="1080" y="663" fill="#fbbf24" font-size="7" font-mono>TRACK 1 (MAIN SIDING)</text>

                            <!-- Track 2 -->
                            <line x1="210" y1="705" x2="1155" y2="705" stroke="#71717a" stroke-width="5" />
                            <line x1="210" y1="701" x2="1155" y2="701" stroke="#f4f4f5" stroke-width="1.5" />
                            <line x1="210" y1="709" x2="1155" y2="709" stroke="#f4f4f5" stroke-width="1.5" />
                            <g stroke="#52525b" stroke-width="2">
                                <line x1="250" y1="698" x2="250" y2="712" /><line x1="300" y1="698" x2="300" y2="712" />
                                <line x1="350" y1="698" x2="350" y2="712" /><line x1="400" y1="698" x2="400" y2="712" />
                                <line x1="450" y1="698" x2="450" y2="712" /><line x1="500" y1="698" x2="500" y2="712" />
                                <line x1="550" y1="698" x2="550" y2="712" /><line x1="600" y1="698" x2="600" y2="712" />
                                <line x1="650" y1="698" x2="650" y2="712" /><line x1="700" y1="698" x2="700" y2="712" />
                                <line x1="750" y1="698" x2="750" y2="712" /><line x1="800" y1="698" x2="800" y2="712" />
                                <line x1="850" y1="698" x2="850" y2="712" /><line x1="900" y1="698" x2="900" y2="712" />
                                <line x1="950" y1="698" x2="950" y2="712" /><line x1="1000" y1="698" x2="1000" y2="712" />
                                <line x1="1050" y1="698" x2="1050" y2="712" /><line x1="1100" y1="698" x2="1100" y2="712" />
                            </g>
                            <text x="1080" y="723" fill="#fbbf24" font-size="7" font-mono>TRACK 2 (LANGSIR SIDING)</text>
                        </g>

                    </g> <!-- End of Layer 1 -->

                    <!-- ======================================================= -->
                    <!-- LAYER 2: JARINGAN, DAYA & UTILITAS (Power & IT) -->
                    <!-- ======================================================= -->
                    <g id="svgLayer2" class="transition-opacity duration-300">
                        <!-- Jalur Kabel FO Backbone Lapangan -->
                        <path d="M 548 145 L 548 260 L 640 260 L 640 600" fill="none" stroke="#38bdf8" stroke-width="1.5" stroke-dasharray="4 4" opacity="0.6" />
                        <path d="M 548 260 L 235 260 L 235 184" fill="none" stroke="#38bdf8" stroke-width="1.5" stroke-dasharray="4 4" opacity="0.6" />
                        <path d="M 548 260 L 985 260 L 985 170" fill="none" stroke="#38bdf8" stroke-width="1.5" stroke-dasharray="4 4" opacity="0.6" />
                    </g>

                    <!-- ======================================================= -->
                    <!-- LAYER 3: KEAMANAN & PENGAWASAN PABEAN -->
                    <!-- ======================================================= -->
                    <g id="svgLayer3" class="transition-opacity duration-300">
                        <!-- Sudut Pandang Kamera PTZ Menara Yard (FOV Cone) -->
                        <path d="M 640 275 L 560 380 L 720 380 Z" fill="#f59e0b" fill-opacity="0.08" stroke="#f59e0b" stroke-width="0.5" stroke-dasharray="2 2" />
                        <!-- Sudut Pandang Kamera Thermal Reefer (FOV Cone) -->
                        <path d="M 1018 78 L 815 110 L 815 190 Z" fill="#06b6d4" fill-opacity="0.08" stroke="#06b6d4" stroke-width="0.5" stroke-dasharray="2 2" />
                    </g>

                    <!-- ======================================================= -->
                    <!-- LAYER 4 & HARDWARE PINS (26 TITIK HARDWARE LENGKAP) -->
                    <!-- ======================================================= -->
                    <g id="svgLayer4" class="transition-opacity duration-300">
                        <?php foreach ($hardware_pins as $hw): ?>
                            <?php 
                                $is_live = ($hw['tipe'] === 'live');
                                $pin_class = "hw-pin cursor-pointer group";
                                $data_layer = "layer-" . $hw['layer'];
                                $data_type = $hw['tipe'];
                                $data_zone = $hw['zona_id'];
                            ?>
                            <g class="<?= $pin_class ?>" 
                               id="pin_<?= $hw['id'] ?>"
                               data-layer="<?= $hw['layer'] ?>"
                               data-type="<?= $data_type ?>" 
                               data-zone="<?= $data_zone ?>"
                               onclick="showHardwareDetails(<?= $hw['id'] ?>, event)">
                                
                                <?php if ($is_live): ?>
                                    <!-- Efek Denyut Berpendar untuk Hardware Live -->
                                    <circle cx="<?= $hw['x'] ?>" cy="<?= $hw['y'] ?>" r="13" fill="#10b981" fill-opacity="0.3" class="animate-ping" style="transform-origin: <?= $hw['x'] ?>px <?= $hw['y'] ?>px;" />
                                    <circle cx="<?= $hw['x'] ?>" cy="<?= $hw['y'] ?>" r="10" fill="#065f46" stroke="#10b981" stroke-width="2" filter="url(#glowGreen)" />
                                    <!-- Badge Nomor -->
                                    <text x="<?= $hw['x'] ?>" y="<?= $hw['y'] + 3.5 ?>" text-anchor="middle" fill="#ffffff" font-size="8.5" font-family="monospace" font-weight="bold"><?= $hw['id'] ?></text>
                                <?php else: ?>
                                    <!-- Pin Solid Biru/Indigo untuk Blueprint Fisik -->
                                    <circle cx="<?= $hw['x'] ?>" cy="<?= $hw['y'] ?>" r="9" fill="#1e1b4b" stroke="#818cf8" stroke-width="1.8" />
                                    <!-- Badge Nomor -->
                                    <text x="<?= $hw['x'] ?>" y="<?= $hw['y'] + 3 ?>" text-anchor="middle" fill="#ffffff" font-size="8" font-family="monospace" font-weight="bold"><?= $hw['id'] ?></text>
                                <?php endif; ?>

                                <!-- Tooltip Hover SVG -->
                                <title><?= $hw['id'] ?>. <?= htmlspecialchars($hw['nama']) ?> (<?= $is_live ? 'LIVE SIMULASI' : 'BLUEPRINT FISIK' ?>)</title>
                            </g>
                        <?php endforeach; ?>
                    </g>

                </svg>
            </div>

            <!-- Petunjuk Navigasi Cepat Peta -->
            <div class="mt-3 flex flex-wrap items-center justify-between text-[11px] text-slate-400">
                <span class="flex items-center">
                    <i class="fa-solid fa-hand-pointer mr-1.5 text-blue-400"></i>
                    <strong>Petunjuk:</strong> Klik salah satu pin lingkaran nomor atau kotak fasilitas untuk memeriksa spesifikasi lengkap, foto produk, dan status live di panel samping.
                </span>
                <span class="font-mono text-slate-500">26 Hardware • 25 Fasilitas • 35 Hektar</span>
            </div>

        </div>

        <!-- =================================================================== -->
        <!-- INSPECTOR DRAWER SAMPING (DETAIL SPESIFIKASI & FOTO PRODUK) -->
        <!-- =================================================================== -->
        <div class="xl:col-span-1 bg-white rounded-2xl p-5 border border-gray-100 shadow-sm space-y-4" id="inspectorSidebar">
            
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-700">Inspector Lapangan</span>
                </div>
                <span id="inspTypeBadge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    LIVE SOFTWARE
                </span>
            </div>

            <!-- Kontainer Foto Produk / Fasilitas -->
            <div class="bg-slate-50 rounded-xl p-3 border border-slate-200/80 flex items-center justify-center min-h-[160px] relative overflow-hidden" id="inspImageWrapper">
                <img id="inspImg" src="hardware/images/converted/item_1.png" alt="Hardware Image" class="max-h-36 max-w-full object-contain transition-all duration-300 drop-shadow-sm">
                <div id="inspIconFallback" class="hidden text-5xl text-slate-400">
                    <i class="fa-solid fa-building" id="inspFallbackIcon"></i>
                </div>
            </div>

            <!-- Informasi Pokok -->
            <div>
                <span id="inspNum" class="text-[10px] font-mono font-bold text-blue-600 uppercase tracking-widest block">HARDWARE #1</span>
                <h3 id="inspTitle" class="text-sm font-bold text-gray-900 leading-tight mt-0.5">Kamera ANPR Gate</h3>
                <p id="inspModel" class="text-xs font-medium text-slate-600 mt-0.5">Hikvision DS-TCG406-E</p>
            </div>

            <!-- Detail Grid -->
            <div class="space-y-2.5 text-xs border-t border-gray-100 pt-3">
                <div class="bg-gray-50/80 p-2.5 rounded-lg border border-gray-100">
                    <span class="text-[10px] uppercase font-bold text-gray-400 block">Lokasi di Denah 35 Ha:</span>
                    <p id="inspLocation" class="font-semibold text-gray-800 text-xs mt-0.5">Gatehouse Inbound & Outbound</p>
                </div>

                <div class="bg-gray-50/80 p-2.5 rounded-lg border border-gray-100">
                    <span class="text-[10px] uppercase font-bold text-gray-400 block">Deskripsi & Peran Operasional:</span>
                    <p id="inspDesc" class="text-gray-600 text-[11px] leading-relaxed mt-0.5">
                        Kamera pembaca nomor plat otomatis berkecepatan tinggi, mendeteksi kedatangan truk trailer di loop masuk.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-2" id="inspPriceBox">
                    <div class="bg-blue-50/50 p-2 rounded-lg border border-blue-100">
                        <span class="text-[10px] uppercase font-bold text-gray-400 block">Harga Satuan:</span>
                        <p id="inspPrice" class="font-mono font-bold text-blue-700 text-xs mt-0.5">Rp 16.000.000</p>
                    </div>
                    <div class="bg-blue-50/50 p-2 rounded-lg border border-blue-100">
                        <span class="text-[10px] uppercase font-bold text-gray-400 block">Subtotal (35 Ha):</span>
                        <p id="inspSubtotal" class="font-mono font-bold text-gray-900 text-xs mt-0.5">Rp 64.000.000</p>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi Langsung ke Modul Terkait (Hanya untuk Live Software) -->
            <div class="pt-2" id="inspActionWrapper">
                <a id="inspActionBtn" href="dashboard.php?page=gate" class="w-full py-2.5 bg-[#0170b9] hover:bg-[#004b87] text-white text-xs font-bold rounded-xl transition-all shadow-sm flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    <span id="inspActionText">Buka Simulasi di Modul Gate</span>
                </a>
            </div>

            <!-- Notifikasi Khusus Blueprint Fisik (Tidak memerlukan simulasi software) -->
            <div id="inspBlueprintNotice" class="hidden pt-2">
                <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl text-center space-y-1">
                    <span class="text-xs font-bold text-slate-700 flex items-center justify-center space-x-1.5">
                        <i class="fa-solid fa-compass-drafting text-indigo-500"></i>
                        <span>Spesifikasi Blueprint Fisik</span>
                    </span>
                    <p class="text-[11px] text-slate-500 leading-snug">
                        Infrastruktur fisik terpasang di lapangan (Perangkat Pasif / Hardware Pendukung). Tidak memerlukan modul simulasi software.
                    </p>
                </div>
            </div>

        </div>

    </div>

    <!-- ======================================================================= -->
    <!-- TABEL DIREKTORI FASILITAS & SEBARAN 26 HARDWARE -->
    <!-- ======================================================================= -->
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
        
        <!-- Tab Sub-Navigasi Bawah -->
        <div class="flex items-center justify-between border-b border-gray-100 pb-3 flex-wrap gap-2">
            <div class="flex items-center space-x-2">
                <button onclick="switchDirectoryTab('tab-facilities')" id="btnTabFacilities" class="px-3.5 py-1.5 rounded-lg text-xs font-bold bg-[#002f5e] text-white transition-all shadow-xs flex items-center space-x-1.5">
                    <i class="fa-solid fa-building"></i>
                    <span>Direktori 25 Fasilitas Lapangan (7 Zona)</span>
                </button>
                <button onclick="switchDirectoryTab('tab-hardware')" id="btnTabHardware" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-all flex items-center space-x-1.5">
                    <i class="fa-solid fa-microchip"></i>
                    <span>Matriks Sebaran 26 Hardware & Layer</span>
                </button>
            </div>
            <span class="text-xs text-gray-400">Master Data Kawasan Terintegrasi</span>
        </div>

        <!-- Konten Tab 1: 25 Fasilitas Lapangan -->
        <div id="tab-facilities" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                <?php foreach ($zones_data as $z_key => $z_val): ?>
                    <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-3.5 space-y-2">
                        <div class="flex items-center space-x-2 border-b border-slate-200/60 pb-2">
                            <span class="w-6 h-6 rounded-md flex items-center justify-center text-xs text-white" style="background-color: <?= $z_val['color'] ?>;">
                                <i class="fa-solid <?= $z_val['icon'] ?>"></i>
                            </span>
                            <h4 class="font-bold text-xs text-gray-900 truncate"><?= htmlspecialchars($z_val['nama']) ?></h4>
                        </div>
                        <ul class="space-y-1.5 text-[11px] text-gray-600">
                            <?php foreach ($z_val['facilities'] as $fac): ?>
                                <li class="flex items-start space-x-2 hover:text-[#0170b9] cursor-pointer transition-colors" onclick="showFacilityDetails('<?= $fac['id'] ?>', event)">
                                    <i class="fa-solid <?= $fac['icon'] ?> text-slate-400 text-[10px] mt-0.5 shrink-0"></i>
                                    <div>
                                        <strong class="text-gray-800 text-[11.5px] block leading-tight"><?= htmlspecialchars($fac['nama']) ?></strong>
                                        <span class="text-[10px] text-gray-500 line-clamp-1"><?= htmlspecialchars($fac['desc']) ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Konten Tab 2: Matriks Sebaran 26 Hardware -->
        <div id="tab-hardware" class="hidden overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-gray-500 uppercase tracking-wider font-semibold border-b border-gray-200">
                        <th class="py-3 px-3 text-center w-12">No</th>
                        <th class="py-3 px-3.5">Nama Hardware</th>
                        <th class="py-3 px-3.5">Model / Seri</th>
                        <th class="py-3 px-3.5">Tipe Integrasi</th>
                        <th class="py-3 px-3.5">Layer GIS</th>
                        <th class="py-3 px-3.5">Zona Denah 35 Ha</th>
                        <th class="py-3 px-3.5">Lokasi Spesifik</th>
                        <th class="py-3 px-3.5 text-right">Subtotal Investasi</th>
                        <th class="py-3 px-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    <?php foreach ($hardware_pins as $hw): ?>
                    <tr class="hover:bg-slate-50/70 transition-colors cursor-pointer" onclick="showHardwareDetails(<?= $hw['id'] ?>, event)">
                        <td class="py-2.5 px-3 text-center font-bold text-gray-400 font-mono"><?= $hw['id'] ?></td>
                        <td class="py-2.5 px-3.5 font-bold text-gray-900"><?= htmlspecialchars($hw['nama']) ?></td>
                        <td class="py-2.5 px-3.5 font-medium text-slate-600"><?= htmlspecialchars($hw['model']) ?></td>
                        <td class="py-2.5 px-3.5">
                            <?php if ($hw['tipe'] === 'live'): ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center w-fit">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span> Live Software
                                </span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center w-fit">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 mr-1.5"></span> Blueprint Fisik
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="py-2.5 px-3.5 font-semibold text-slate-700">Layer <?= $hw['layer'] ?></td>
                        <td class="py-2.5 px-3.5"><span class="px-2 py-0.5 bg-slate-100 rounded text-[11px] font-medium text-slate-700"><?= $hw['zona'] ?></span></td>
                        <td class="py-2.5 px-3.5 text-gray-500 text-[11px]"><?= $hw['posisi'] ?></td>
                        <td class="py-2.5 px-3.5 text-right font-mono font-bold text-[#002f5e]"><?= $hw['subtotal'] ?></td>
                        <td class="py-2.5 px-3 text-center">
                            <button class="p-1 text-[#0170b9] hover:bg-blue-50 rounded transition" title="Lihat di Inspector">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- DATA JAVASCRIPT & LOGIKA INTERAKTIF -->
<script>
// Dataset Hardware Javascript untuk Inspector
const hardwareDataset = <?= json_encode($hardware_pins, JSON_PRETTY_PRINT) ?>;

// Dataset Fasilitas Javascript
const facilitiesDataset = {
    <?php foreach ($zones_data as $z_key => $z_val): ?>
        <?php foreach ($z_val['facilities'] as $fac): ?>
            "<?= $fac['id'] ?>": {
                nama: "<?= addslashes($fac['nama']) ?>",
                zona: "<?= addslashes($z_val['nama']) ?>",
                icon: "<?= $fac['icon'] ?>",
                desc: "<?= addslashes($fac['desc']) ?>"
            },
        <?php endforeach; ?>
    <?php endforeach; ?>
};

// Fungsi Tampilkan Detail Hardware pada Inspector Samping
function showHardwareDetails(id, event) {
    if (event) event.stopPropagation();
    
    const hw = hardwareDataset.find(item => item.id === id);
    if (!hw) return;

    document.getElementById('inspNum').textContent = "HARDWARE #" + hw.id;
    document.getElementById('inspTitle').textContent = hw.nama;
    document.getElementById('inspModel').textContent = hw.model;
    document.getElementById('inspLocation').textContent = hw.posisi + " (" + hw.zona + ")";
    document.getElementById('inspDesc').textContent = hw.desc;

    // Foto Produk
    const imgEl = document.getElementById('inspImg');
    const fallbackEl = document.getElementById('inspIconFallback');
    imgEl.src = hw.img;
    imgEl.classList.remove('hidden');
    fallbackEl.classList.add('hidden');

    // Tipe Badge
    const badge = document.getElementById('inspTypeBadge');
    if (hw.tipe === 'live') {
        badge.textContent = "LIVE SOFTWARE";
        badge.className = "px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200";
    } else {
        badge.textContent = "BLUEPRINT FISIK";
        badge.className = "px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200";
    }

    // Kotak Harga
    document.getElementById('inspPriceBox').classList.remove('hidden');
    document.getElementById('inspPrice').textContent = hw.harga;
    document.getElementById('inspSubtotal').textContent = hw.subtotal + " (" + hw.unit + ")";

    // Tombol Aksi Modul Terkait (Hanya untuk Live Software)
    const actionWrapper = document.getElementById('inspActionWrapper');
    const blueprintNotice = document.getElementById('inspBlueprintNotice');
    const actionBtn = document.getElementById('inspActionBtn');
    const actionText = document.getElementById('inspActionText');

    if (hw.tipe === 'live') {
        actionWrapper.classList.remove('hidden');
        if (blueprintNotice) blueprintNotice.classList.add('hidden');

        const moduleMapping = {
            'gate': { url: 'dashboard.php?page=gate', label: 'Buka Simulasi di Modul Gate' },
            'yard': { url: 'dashboard.php?page=yard', label: 'Buka Modul Yard & Stacking' },
            'reefer': { url: 'dashboard.php?page=reefer', label: 'Buka Modul Reefer Cold Chain' },
            'intermodal': { url: 'dashboard.php?page=intermodal', label: 'Buka Modul Intermodal Rail' },
            'customs': { url: 'dashboard.php?page=customs', label: 'Buka Modul Kepabeanan & Bea Cukai' },
            'alat': { url: 'dashboard.php?page=alat', label: 'Buka Lokasi Alat Berat GPS' },
            'scanner': { url: 'dashboard.php?page=scanner', label: 'Buka Scanner SSCC/GS1' },
            'settings': { url: 'dashboard.php?page=settings', label: 'Buka Pengaturan Datacenter' }
        };

        const targetMod = moduleMapping[hw.modul] || { url: 'dashboard.php?page=gate', label: 'Buka Modul Terkait' };
        actionBtn.href = targetMod.url;
        actionText.textContent = targetMod.label;
    } else {
        // BLUEPRINT FISIK: TIDAK ADA TOMBOL SIMULASI!
        actionWrapper.classList.add('hidden');
        if (blueprintNotice) blueprintNotice.classList.remove('hidden');
    }
}

// Fungsi Tampilkan Detail Fasilitas Bangunan pada Inspector Samping
function showFacilityDetails(facId, event) {
    if (event) event.stopPropagation();

    const fac = facilitiesDataset[facId];
    if (!fac) return;

    document.getElementById('inspNum').textContent = "FASILITAS LAPANGAN";
    document.getElementById('inspTitle').textContent = fac.nama;
    document.getElementById('inspModel').textContent = fac.zona;
    document.getElementById('inspLocation').textContent = "Kawasan Dry Port 35 Ha";
    document.getElementById('inspDesc').textContent = fac.desc;

    // Sembunyikan foto hardware, tampilkan icon fasilitas
    const imgEl = document.getElementById('inspImg');
    const fallbackEl = document.getElementById('inspIconFallback');
    const fallbackIcon = document.getElementById('inspFallbackIcon');
    imgEl.classList.add('hidden');
    fallbackEl.classList.remove('hidden');
    fallbackIcon.className = "fa-solid " + fac.icon + " text-[#0170b9]";

    // Badge Fasilitas
    const badge = document.getElementById('inspTypeBadge');
    badge.textContent = "FASILITAS OPERASIONAL";
    badge.className = "px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200";

    // Sembunyikan harga dan tombol aksi / blueprint notice
    document.getElementById('inspPriceBox').classList.add('hidden');
    document.getElementById('inspActionWrapper').classList.add('hidden');
    const blueprintNotice = document.getElementById('inspBlueprintNotice');
    if (blueprintNotice) blueprintNotice.classList.add('hidden');
}

// Fungsi Tampilkan Info Zona
function showZoneDetails(zoneKey) {
    // Optional click on zone background
}

// Kontrol 4 Layer Toggle
function toggleLayer(layerNum) {
    const isChecked = document.getElementById('layerToggle' + layerNum).checked;
    
    // Sembunyikan/tampilkan elemen layer terkait
    if (layerNum === 1) {
        document.getElementById('svgLayer1').style.opacity = isChecked ? '1' : '0.15';
    } else if (layerNum === 2) {
        document.getElementById('svgLayer2').style.opacity = isChecked ? '1' : '0';
    } else if (layerNum === 3) {
        document.getElementById('svgLayer3').style.opacity = isChecked ? '1' : '0';
    }
    
    // Perbarui visibilitas pin yang berada di layer tersebut
    document.querySelectorAll('.hw-pin').forEach(pin => {
        const pinLayer = parseInt(pin.getAttribute('data-layer'));
        if (pinLayer === layerNum) {
            pin.style.display = isChecked ? '' : 'none';
        }
    });
}

// Filter Berdasarkan Zona
function filterZone(zoneId) {
    document.querySelectorAll('.zone-btn').forEach(btn => {
        if (btn.getAttribute('data-zone') === zoneId) {
            btn.classList.add('bg-[#002f5e]', 'text-white');
            btn.classList.remove('bg-gray-100', 'text-gray-700');
        } else {
            btn.classList.remove('bg-[#002f5e]', 'text-white');
            btn.classList.add('bg-gray-100', 'text-gray-700');
        }
    });

    document.querySelectorAll('.hw-pin').forEach(pin => {
        const pZone = pin.getAttribute('data-zone');
        if (zoneId === 'all' || pZone === zoneId) {
            pin.style.display = '';
        } else {
            pin.style.display = 'none';
        }
    });
}

// Filter Berdasarkan Tipe Hardware (All / Live / Blueprint)
function filterHardwareType(type) {
    const btnAll = document.getElementById('btnHwAll');
    const btnLive = document.getElementById('btnHwLive');
    const btnBlueprint = document.getElementById('btnHwBlueprint');

    btnAll.className = "px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 transition-all";
    btnLive.className = "px-2.5 py-1 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 transition-all";
    btnBlueprint.className = "px-2.5 py-1 rounded-lg text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200 transition-all";

    if (type === 'all') {
        btnAll.className = "px-2.5 py-1 rounded-lg text-xs font-bold bg-[#0170b9] text-white shadow-xs transition-all";
    } else if (type === 'live') {
        btnLive.className = "px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-600 text-white shadow-xs transition-all";
    } else if (type === 'blueprint') {
        btnBlueprint.className = "px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-600 text-white shadow-xs transition-all";
    }

    document.querySelectorAll('.hw-pin').forEach(pin => {
        const pType = pin.getAttribute('data-type');
        if (type === 'all' || pType === type) {
            pin.style.display = '';
        } else {
            pin.style.display = 'none';
        }
    });
}

// Switch Tab Direktori Bawah
function switchDirectoryTab(tabId) {
    document.getElementById('tab-facilities').classList.add('hidden');
    document.getElementById('tab-hardware').classList.add('hidden');
    document.getElementById('btnTabFacilities').classList.replace('bg-[#002f5e]', 'text-gray-600');
    document.getElementById('btnTabFacilities').classList.remove('text-white', 'shadow-xs');
    document.getElementById('btnTabHardware').classList.replace('bg-[#002f5e]', 'text-gray-600');
    document.getElementById('btnTabHardware').classList.remove('text-white', 'shadow-xs');

    document.getElementById(tabId).classList.remove('hidden');
    if (tabId === 'tab-facilities') {
        document.getElementById('btnTabFacilities').classList.add('bg-[#002f5e]', 'text-white', 'shadow-xs');
        document.getElementById('btnTabFacilities').classList.remove('text-gray-600');
    } else {
        document.getElementById('btnTabHardware').classList.add('bg-[#002f5e]', 'text-white', 'shadow-xs');
        document.getElementById('btnTabHardware').classList.remove('text-gray-600');
    }
}

// Reset Map View
function resetMapView() {
    document.getElementById('layerToggle1').checked = true;
    document.getElementById('layerToggle2').checked = true;
    document.getElementById('layerToggle3').checked = true;
    document.getElementById('layerToggle4').checked = true;
    toggleLayer(1);
    toggleLayer(2);
    toggleLayer(3);
    toggleLayer(4);
    filterZone('all');
    filterHardwareType('all');
    showHardwareDetails(1, null);
}
</script>
