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
    'desc'   => 'Master plan tata letak fisik 35 Ha: 7 zona operasional, 25 fasilitas terminal, dan 26 titik hardware dengan 4 layer kontrol.',
    'icon'   => 'fa-map-location-dot',
    'status' => 'Master Plan 4 Layer Interaktif'
];

// Data 7 Zona Operasional & 25 Fasilitas Lapangan
$zones_data = [
    'gate' => [
        'nama' => 'Zona Gate (Entrance Area)',
        'icon' => 'fa-door-open',
        'color' => '#0284c7',
        'facilities' => [
            ['id' => 'f_gate_in', 'nama' => 'Gatehouse Inbound', 'desc' => 'Pos kontrol masuk 2 lane dilengkapi Kamera ANPR, OCR Portal, RFID Reader, dan Industrial Edge AI PC.', 'icon' => 'fa-arrow-right-to-bracket', 'hw_id' => 2, 'hw_nama' => 'Kamera OCR Kontainer Portal & ANPR', 'img' => 'assets/img/hardware/converted/item_2.png'],
            ['id' => 'f_gate_out', 'nama' => 'Gatehouse Outbound', 'desc' => 'Pos kontrol keluar 2 lane dengan pembacaan e-Pass dan palang barrier gate otomatis.', 'icon' => 'fa-arrow-right-from-bracket', 'hw_id' => 9, 'hw_nama' => 'Automatic Barrier Gate & ANPR', 'img' => 'assets/img/hardware/converted/item_9.png'],
            ['id' => 'f_weighbridge', 'nama' => 'Weighbridge Station', 'desc' => 'Bangunan jembatan timbang kendaraan kapasitas 80 ton bersertifikasi SOLAS VGM.', 'icon' => 'fa-scale-balanced', 'hw_id' => 6, 'hw_nama' => 'Weighbridge / VGM Scale 80t', 'img' => 'assets/img/hardware/converted/item_6.png'],
            ['id' => 'f_security_gate', 'nama' => 'Security Post 24 Jam', 'desc' => 'Pos keamanan gerbang utama, verifikasi dokumen surat jalan, dan penerbitan izin masuk.', 'icon' => 'fa-shield-halved', 'hw_id' => 7, 'hw_nama' => 'Long-Range UHF RFID Reader', 'img' => 'assets/img/hardware/converted/item_7.png'],
            ['id' => 'f_truck_queue', 'nama' => 'Parkir Truk Antrian', 'desc' => 'Area parkir tunggu (queuing yard) berkapasitas 40 truk trailer untuk mencegah antrean di jalan arteri.', 'icon' => 'fa-truck-moving', 'hw_id' => 17, 'hw_nama' => 'GPS Tracker Truck & Fleet', 'img' => 'assets/img/hardware/converted/item_17.png']
        ]
    ],
    'cfs_yard' => [
        'nama' => 'Zona Container Yard & CFS',
        'icon' => 'fa-boxes-stacked',
        'color' => '#4f46e5',
        'facilities' => [
            ['id' => 'f_cfs', 'nama' => 'Container Freight Station (CFS)', 'desc' => 'Gudang modern 4.000 m² untuk aktivitas bongkar muat kargo LCL (stripping-stuffing) dan konsolidasi barang.', 'icon' => 'fa-warehouse', 'hw_id' => 15, 'hw_nama' => 'Barcode / QR Scanner Gudang CFS', 'img' => 'assets/img/hardware/converted/item_15.png'],
            ['id' => 'f_empty_depot', 'nama' => 'Empty Container Depot', 'desc' => 'Area penyimpanan peti kemas kosong (empty boxes) 20ft/40ft dengan kapasitas 2.500 TEUs.', 'icon' => 'fa-box-open', 'hw_id' => 20, 'hw_nama' => 'Terminal VMT Alat Berat Empty Depot', 'img' => 'assets/img/hardware/converted/item_20.png'],
            ['id' => 'f_warehouse', 'nama' => 'Warehouse / Gudang Transit', 'desc' => 'Gudang tertutup untuk muatan bernilai tinggi, transit distribusi barang, dan cross-docking.', 'icon' => 'fa-dolly', 'hw_id' => 17, 'hw_nama' => 'GPS Asset Tracker Transit Kargo', 'img' => 'assets/img/hardware/converted/item_17.png'],
            ['id' => 'f_stacking_yard', 'nama' => 'Container Stacking Yard (Blok A-E)', 'desc' => 'Lapangan penumpukan utama 15 Ha: Blok A/B (Laden Ekspor-Impor), Blok C (Domestik), Blok D (Buffer), Blok E (Dangerous Goods).', 'icon' => 'fa-cubes', 'hw_id' => 21, 'hw_nama' => 'DGPS / RTK GNSS Receiver Reach Stacker', 'img' => 'assets/img/hardware/converted/item_21.png']
        ]
    ],
    'reefer' => [
        'nama' => 'Zona Reefer Yard (Cold Chain)',
        'icon' => 'fa-snowflake',
        'color' => '#06b6d4',
        'facilities' => [
            ['id' => 'f_reefer_control', 'nama' => 'Reefer Control Room', 'desc' => 'Ruang pusat kendali monitoring suhu boks pendingin 24/7 dan pencatatan fluktuasi rantai dingin.', 'icon' => 'fa-temperature-arrow-down', 'hw_id' => 16, 'hw_nama' => 'Thermal Camera Reefer Monitoring', 'img' => 'assets/img/hardware/converted/item_16.png'],
            ['id' => 'f_genset_shelter', 'nama' => 'Genset Shelter Cadangan', 'desc' => 'Gedung pembangkit daya darurat 1.500 kVA Caterpillar untuk menjamin kelangsungan listrik reefer jika PLN padam.', 'icon' => 'fa-bolt', 'hw_id' => 14, 'hw_nama' => 'UPS Online & Sistem Kelistrikan Darurat', 'img' => 'assets/img/hardware/converted/item_14.png'],
            ['id' => 'f_reefer_racks', 'nama' => 'Reefer Stacking Racks (300 Plugs)', 'desc' => 'Platform bertingkat dilengkapi 300 titik colokan Smart Reefer Socket 380V/32A dengan meteran digital.', 'icon' => 'fa-plug-circle-check', 'hw_id' => 23, 'hw_nama' => 'Smart Reefer Power Socket 380V/32A', 'img' => 'assets/img/hardware/converted/item_23.png']
        ]
    ],
    'rail' => [
        'nama' => 'Zona Intermodal Rail Siding',
        'icon' => 'fa-train-subway',
        'color' => '#d97706',
        'facilities' => [
            ['id' => 'f_rail_office', 'nama' => 'Rail Office / Stasiun Operator', 'desc' => 'Kantor pengendali operasi langsiran dan persinyalan kereta api kontainer bekerja sama dengan KAI Logistik.', 'icon' => 'fa-train', 'hw_id' => 25, 'hw_nama' => 'Rail Trackside Axle Counter Frauscher', 'img' => 'assets/img/hardware/converted/item_25.png'],
            ['id' => 'f_loading_ramp', 'nama' => 'Loading Ramp & Sepur Ganda (Dual Track 450m)', 'desc' => '2 sepur rel berkapasitas 2x30 gerbong (Sepur Utara: Bongkar Inbound Impor ex-Priok; Sepur Selatan: Muat Outbound Ekspor siap kapal) untuk alih muat langsung gerbong datar (flatcar PPCW) dan cross-docking.', 'icon' => 'fa-arrows-split-up-and-left', 'hw_id' => 12, 'hw_nama' => 'Access Point Outdoor WiFi 7 Siding KA', 'img' => 'assets/img/hardware/converted/item_12.png']
        ]
    ],
    'customs' => [
        'nama' => 'Zona Behandle & Bea Cukai',
        'icon' => 'fa-stamp',
        'color' => '#dc2626',
        'facilities' => [
            ['id' => 'f_kppbc', 'nama' => 'Kantor Bea Cukai (KPPBC)', 'desc' => 'Kantor pelayanan kepabeanan dan pabean impor/ekspor terhubung langsung dengan sistem nasional CEISA 4.0.', 'icon' => 'fa-building-columns', 'hw_id' => 26, 'hw_nama' => 'Electronic Cargo Smart Seal (E-Seal)', 'img' => 'assets/img/hardware/converted/item_26.png'],
            ['id' => 'f_behandle_xray', 'nama' => 'Behandle Area & Gantry X-Ray', 'desc' => 'Gedung pemindaian kontainer X-Ray energi tinggi (6 MeV) Nuctech dan kanopi pemeriksaan fisik Jalur Merah.', 'icon' => 'fa-radiation', 'hw_id' => 19, 'hw_nama' => 'Gantry Container X-Ray Scanner 6 MeV', 'img' => 'assets/img/hardware/converted/item_19.png'],
            ['id' => 'f_quarantine', 'nama' => 'Quarantine Inspection Room', 'desc' => 'Laboratorium dan ruang pemeriksaan Balai Karantina Hewan, Ikan, dan Tumbuhan Kementerian Pertanian.', 'icon' => 'fa-biohazard', 'hw_id' => 26, 'hw_nama' => 'Smart E-Seal & Inspeksi Pabean', 'img' => 'assets/img/hardware/converted/item_26.png']
        ]
    ],
    'office' => [
        'nama' => 'Zona Kantor & Operasional',
        'icon' => 'fa-building',
        'color' => '#7c3aed',
        'facilities' => [
            ['id' => 'f_admin_office', 'nama' => 'Kantor Utama / Admin Building', 'desc' => 'Gedung kantor pusat pengelola terminal PT Multi Terminal Indonesia (MTI) 2 lantai.', 'icon' => 'fa-briefcase', 'hw_id' => 13, 'hw_nama' => 'Server NVR & Database YMS Core', 'img' => 'assets/img/hardware/converted/item_13.png'],
            ['id' => 'f_datacenter', 'nama' => 'Datacenter / Server Room (NOC)', 'desc' => 'Ruang server sentral berpendingin presisi, NVR 64-Channel, Online UPS 5000VA, dan rak switch jaringan inti.', 'icon' => 'fa-server', 'hw_id' => 11, 'hw_nama' => 'Industrial Network Switch PoE+ & Datacenter', 'img' => 'assets/img/hardware/converted/item_11.png'],
            ['id' => 'f_meeting_room', 'nama' => 'Ruang Meeting & Training Room', 'desc' => 'Ruang rapat operasional dan ruang simulasi/pelatihan bersertifikasi untuk operator terminal.', 'icon' => 'fa-users-gear', 'hw_id' => 10, 'hw_nama' => 'LED Information Display Ruang Operasi', 'img' => 'assets/img/hardware/converted/item_10.png'],
            ['id' => 'f_workshop_mr', 'nama' => 'Workshop / Bengkel M&R', 'desc' => 'Bengkel perawatan alat berat lapangan (Reach Stacker, Empty Handler, Forklift, dan perbaikan kontainer).', 'icon' => 'fa-wrench', 'hw_id' => 24, 'hw_nama' => 'Rugged Mobile PDA Tallyman & M&R', 'img' => 'assets/img/hardware/converted/item_24.png']
        ]
    ],
    'public' => [
        'nama' => 'Zona Fasilitas Umum & Kesejahteraan',
        'icon' => 'fa-hands-holding-child',
        'color' => '#059669',
        'facilities' => [
            ['id' => 'f_musholla', 'nama' => 'Musholla / Masjid Al-Hidayah', 'desc' => 'Fasilitas ibadah representatif berkapasitas 150 jamaah untuk pekerja terminal, petugas pabean, dan sopir truk.', 'icon' => 'fa-mosque', 'hw_id' => 12, 'hw_nama' => 'Access Point WiFi 7 Fasum', 'img' => 'assets/img/hardware/converted/item_12.png'],
            ['id' => 'f_kantin', 'nama' => 'Kantin / Warung Makan 🍛', 'desc' => 'Area pujasera bersih dan terjangkau untuk kebutuhan makan dan minum seluruh pekerja lapangan dan sopir.', 'icon' => 'fa-utensils', 'hw_id' => 12, 'hw_nama' => 'Access Point WiFi 7 Fasum', 'img' => 'assets/img/hardware/converted/item_12.png'],
            ['id' => 'f_koperasi', 'nama' => 'Koperasi Karyawan 🏪', 'desc' => 'Toko ritel penyedia kebutuhan sehari-hari, minuman, makanan ringan, perlengkapan APD, dan ATK.', 'icon' => 'fa-shop', 'hw_id' => 8, 'hw_nama' => 'RFID Tag UHF ISO Karyawan', 'img' => 'assets/img/hardware/converted/item_8.png'],
            ['id' => 'f_klinik', 'nama' => 'Klinik P3K / Pos Kesehatan', 'desc' => 'Pos pertolongan pertama pada kecelakaan kerja (K3) dilengkapi tenaga medis jaga dan ambulans darurat.', 'icon' => 'fa-kit-medical', 'hw_id' => 15, 'hw_nama' => 'Barcode / QR Scanner Medis K3', 'img' => 'assets/img/hardware/converted/item_15.png'],
            ['id' => 'f_toilet', 'nama' => 'Toilet Umum Tersebar', 'desc' => 'Fasilitas sanitasi dan MCK bersih yang tersebar di 3 lokasi strategis (Gerbang, Kantor, dan Siding KA).', 'icon' => 'fa-restroom', 'hw_id' => 12, 'hw_nama' => 'Access Point WiFi 7 Area Layanan', 'img' => 'assets/img/hardware/converted/item_12.png'],
            ['id' => 'f_parking_staff', 'nama' => 'Parkir Karyawan & Tamu', 'desc' => 'Area parkir kendaraan roda 2 dan roda 4 khusus pegawai terminal, tamu dinas, dan mitra logistik.', 'icon' => 'fa-square-parking', 'hw_id' => 1, 'hw_nama' => 'Kamera ANPR Gerbang Karyawan', 'img' => 'assets/img/hardware/converted/item_1.png'],
            ['id' => 'f_damkar', 'nama' => 'Pos Damkar & Fire Station', 'desc' => 'Pos tanggap darurat kebakaran dilengkapi armada pemadam kimia, reservoir air, dan jaringan hydrant yard.', 'icon' => 'fa-fire-extinguisher', 'hw_id' => 16, 'hw_nama' => 'Thermal Camera & Sensor Api', 'img' => 'assets/img/hardware/converted/item_16.png'],
            ['id' => 'f_driver_rest', 'nama' => 'Ruang Istirahat Driver (Rest Area)', 'desc' => 'Ruang istirahat ber-AC dilengkapi colokan listrik, dispenser air minum, dan kasur santai bagi sopir truk jarak jauh.', 'icon' => 'fa-couch', 'hw_id' => 8, 'hw_nama' => 'Kartu Akses RFID Tag Pengemudi', 'img' => 'assets/img/hardware/converted/item_8.png']
        ]
    ]
];

// Data 26 Hardware Terpetakan pada Koordinat Denah 35 Ha
$hardware_pins = [
    // Zona Gate (Entrance Area)
    [
        'id' => 1, 'nama' => 'Kamera ANPR Gate', 'model' => 'Hikvision DS-TCG406-E', 'tipe' => 'live', 'layer' => 4,
        'zona' => 'Zona Gate', 'zona_id' => 'gate', 'posisi' => 'Gatehouse Inbound & Outbound',
        'x' => 875, 'y' => 85, 'harga' => 'Rp 16.000.000', 'unit' => '4 unit', 'subtotal' => 'Rp 64.000.000',
        'img' => 'assets/img/hardware/converted/item_1.png', 'modul' => 'gate',
        'desc' => 'Kamera pembaca nomor plat otomatis berkecepatan tinggi, mendeteksi kedatangan truk trailer di loop masuk.'
    ],
    [
        'id' => 2, 'nama' => 'Kamera OCR Kontainer', 'model' => 'Hikvision iDS-TCV300-A6I', 'tipe' => 'live', 'layer' => 4,
        'zona' => 'Zona Gate', 'zona_id' => 'gate', 'posisi' => 'Portal Inbound Lane 1 & 2',
        'x' => 875, 'y' => 110, 'harga' => 'Rp 55.000.000', 'unit' => '4 unit', 'subtotal' => 'Rp 220.000.000',
        'img' => 'assets/img/hardware/converted/item_2.png', 'modul' => 'gate',
        'desc' => 'Portal pemindai multi-sudut untuk mengekstraksi kode kontainer ISO 6346 (4 huruf + 7 angka) dan tipe kontainer.'
    ],
    [
        'id' => 6, 'nama' => 'Weighbridge / VGM Scale 80t', 'model' => 'Fangda Electronic Truck Scale 80 Ton', 'tipe' => 'live', 'layer' => 4,
        'zona' => 'Zona Gate', 'zona_id' => 'gate', 'posisi' => 'Weighbridge Station Inbound',
        'x' => 875, 'y' => 145, 'harga' => 'Rp 267.000.000', 'unit' => '2 unit', 'subtotal' => 'Rp 534.000.000',
        'img' => 'assets/img/hardware/converted/item_6.png', 'modul' => 'gate',
        'desc' => 'Jembatan timbang terintegrasi load cell digital berkapasitas 80 ton untuk verifikasi berat kotor bersertifikat SOLAS VGM.'
    ],
    [
        'id' => 7, 'nama' => 'RFID Reader Long-Range', 'model' => 'UHF RFID Reader (20m)', 'tipe' => 'live', 'layer' => 4,
        'zona' => 'Zona Gate', 'zona_id' => 'gate', 'posisi' => 'Tiang e-Pass Gatehouse',
        'x' => 855, 'y' => 95, 'harga' => 'Rp 3.000.000', 'unit' => '4 unit', 'subtotal' => 'Rp 12.000.000',
        'img' => 'assets/img/hardware/converted/item_7.png', 'modul' => 'gate',
        'desc' => 'Antena pembaca tag RFID jarak jauh hingga 20 meter untuk identifikasi kartu pengemudi dan verifikasi izin akses gerbang.'
    ],
    [
        'id' => 8, 'nama' => 'RFID Tag UHF ISO', 'model' => 'RFID UHF ID Tag ISO', 'tipe' => 'blueprint', 'layer' => 1,
        'zona' => 'Zona Gate', 'zona_id' => 'gate', 'posisi' => 'Security Post & Loket Tag',
        'x' => 818, 'y' => 75, 'harga' => 'Rp 690.000 / pack', 'unit' => '50 pack', 'subtotal' => 'Rp 34.500.000',
        'img' => 'assets/img/hardware/converted/item_8.png', 'modul' => 'gate',
        'desc' => 'Kartu tag pintar anti-metal standar ISO 18000-6C yang dibagikan kepada mitra truk dan kartu akses pengemudi.'
    ],
    [
        'id' => 9, 'nama' => 'Automatic Barrier Gate', 'model' => 'High-Speed Barrier Gate', 'tipe' => 'blueprint', 'layer' => 4,
        'zona' => 'Zona Gate', 'zona_id' => 'gate', 'posisi' => 'Lane 1, 2, 3, 4 Gatehouses',
        'x' => 875, 'y' => 175, 'harga' => 'Rp 60.000.000', 'unit' => '4 unit', 'subtotal' => 'Rp 240.000.000',
        'img' => 'assets/img/hardware/converted/item_9.png', 'modul' => 'gate',
        'desc' => 'Palang otomatis kecepatan tinggi (buka 1.2 detik) yang digerakkan sinyal relay Edge PC setelah dokumen tervalidasi.'
    ],
    [
        'id' => 10, 'nama' => 'LED Information Display', 'model' => 'Outdoor P6/P8 Industrial LED', 'tipe' => 'live', 'layer' => 4,
        'zona' => 'Zona Gate', 'zona_id' => 'gate', 'posisi' => 'Kanopi Gerbang Inbound/Outbound',
        'x' => 897, 'y' => 65, 'harga' => 'Rp 47.000.000', 'unit' => '4 unit', 'subtotal' => 'Rp 188.000.000',
        'img' => 'assets/img/hardware/converted/item_10.png', 'modul' => 'gate',
        'desc' => 'Display LED outdoor cerah yang menampilkan instruksi sopir ("SILAHKAN MASUK", "KE YARD BLOK B"), dan nomor gate pass.'
    ],
    [
        'id' => 18, 'nama' => 'Industrial Edge AI PC Gate', 'model' => 'Advantech ARK-3532 Fanless', 'tipe' => 'live', 'layer' => 2,
        'zona' => 'Zona Gate', 'zona_id' => 'gate', 'posisi' => 'Kabinet Kontrol Gate Inbound/Outbound',
        'x' => 897, 'y' => 135, 'harga' => 'Rp 42.000.000', 'unit' => '4 unit', 'subtotal' => 'Rp 168.000.000',
        'img' => 'assets/img/hardware/converted/item_18.png', 'modul' => 'gate',
        'desc' => 'Komputer tepi tangguh tanpa kipas (-20°C s/d 60°C) yang memproses validasi transaksi gerbang lokal dan kontrol relay palang.'
    ],
    [
        'id' => 15, 'nama' => 'Barcode / QR Scanner', 'model' => 'Zebra Symbol LS2208 Handheld', 'tipe' => 'blueprint', 'layer' => 2,
        'zona' => 'Zona Gate', 'zona_id' => 'gate', 'posisi' => 'Security Post & Loket Pelayanan',
        'x' => 818, 'y' => 95, 'harga' => 'Rp 1.600.000', 'unit' => '8 unit', 'subtotal' => 'Rp 12.800.000',
        'img' => 'assets/img/hardware/converted/item_15.png', 'modul' => 'scanner',
        'desc' => 'Pemindai laser 1D/2D untuk membaca barcode surat jalan cetak, gate pass digital di smartphone sopir, dan dokumen booking.'
    ],

    // Zona Container Yard & CFS
    [
        'id' => 3, 'nama' => 'CCTV Yard 4 MP', 'model' => 'Hikvision DS-2CD2046G2H-IU', 'tipe' => 'blueprint', 'layer' => 3,
        'zona' => 'Zona Container Yard', 'zona_id' => 'cfs_yard', 'posisi' => 'Tiang Lampu Blok A, B, C, D (32 Titik)',
        'x' => 502, 'y' => 290, 'harga' => 'Rp 3.500.000', 'unit' => '32 unit', 'subtotal' => 'Rp 112.000.000',
        'img' => 'assets/img/hardware/converted/item_3.png', 'modul' => 'yard',
        'desc' => 'Kamera pengawas berdefinisi tinggi IP67 weatherproof dengan night vision IR untuk memantau keselamatan penumpukan kontainer.'
    ],
    [
        'id' => 5, 'nama' => 'CCTV PTZ 360°', 'model' => 'Hikvision DS-2SE4C425MWG TandemVu', 'tipe' => 'blueprint', 'layer' => 3,
        'zona' => 'Zona Container Yard', 'zona_id' => 'cfs_yard', 'posisi' => 'Menara Pengawas Pusat (Tower 1 & 2)',
        'x' => 630, 'y' => 391, 'harga' => 'Rp 10.300.000', 'unit' => '8 unit', 'subtotal' => 'Rp 82.400.000',
        'img' => 'assets/img/hardware/converted/item_5.png', 'modul' => 'yard',
        'desc' => 'Kamera bergerak 360 derajat dengan 25x optical zoom untuk pemantauan insiden darurat dan pergerakan alat berat di yard.'
    ],
    [
        'id' => 20, 'nama' => 'Vehicle Mounted Terminal (VMT)', 'model' => 'Zebra VC8300 Rugged Terminal', 'tipe' => 'blueprint', 'layer' => 4,
        'zona' => 'Zona Container Yard', 'zona_id' => 'cfs_yard', 'posisi' => 'Kabin Reach Stacker 01 & 02',
        'x' => 757, 'y' => 290, 'harga' => 'Rp 74.000.000', 'unit' => '6 unit', 'subtotal' => 'Rp 444.000.000',
        'img' => 'assets/img/hardware/converted/item_20.png', 'modul' => 'alat',
        'desc' => 'Terminal komputer kabin layar sentuh tahan guncangan ekstrem (MIL-STD-810G) untuk menerima perintah kerja (Job Order) dari YMS.'
    ],
    [
        'id' => 21, 'nama' => 'DGPS / RTK GNSS Receiver', 'model' => 'CHCNAV CGI-610 Dual Antenna', 'tipe' => 'live', 'layer' => 4,
        'zona' => 'Zona Container Yard', 'zona_id' => 'cfs_yard', 'posisi' => 'Atap Boom Reach Stacker 01 & 02',
        'x' => 450, 'y' => 290, 'harga' => 'Rp 45.000.000', 'unit' => '6 unit', 'subtotal' => 'Rp 270.000.000',
        'img' => 'assets/img/hardware/converted/item_21.png', 'modul' => 'yard',
        'desc' => 'Antena navigasi satelit berakurasi centimeter (<1.4 cm) untuk mendeteksi secara otomatis posisi penumpukan Bay-Row-Tier.'
    ],
    [
        'id' => 22, 'nama' => 'Spreader Twistlock & Load Cell', 'model' => 'Bromma SmartSpreader Kit', 'tipe' => 'live', 'layer' => 4,
        'zona' => 'Zona Container Yard', 'zona_id' => 'cfs_yard', 'posisi' => 'Head Spreader Alat Angkat RS',
        'x' => 550, 'y' => 290, 'harga' => 'Rp 35.000.000', 'unit' => '6 unit', 'subtotal' => 'Rp 210.000.000',
        'img' => 'assets/img/hardware/converted/item_22.png', 'modul' => 'yard',
        'desc' => 'Sensor induktif penguncian twistlock dan load cell telemetri untuk auto-update status kontainer (lift-on / lift-off) ke basis data.'
    ],
    [
        'id' => 24, 'nama' => 'Rugged Mobile PDA', 'model' => 'Zebra TC57x Handheld PDA', 'tipe' => 'blueprint', 'layer' => 4,
        'zona' => 'Zona Container Yard', 'zona_id' => 'cfs_yard', 'posisi' => 'Petugas Tallyman & Checker Lapangan',
        'x' => 470, 'y' => 480, 'harga' => 'Rp 16.000.000', 'unit' => '10 unit', 'subtotal' => 'Rp 160.000.000',
        'img' => 'assets/img/hardware/converted/item_24.png', 'modul' => 'kontainer',
        'desc' => 'Perangkat genggam enterprise tahan banting (IP68) untuk inspeksi fisik kontainer rusak (EIR) dan checklist kondisi segel.'
    ],

    // Zona Reefer Yard (Cold Chain)
    [
        'id' => 16, 'nama' => 'Thermal Camera Reefer', 'model' => 'Hikvision DS-2TD2636B-13/P Thermal', 'tipe' => 'blueprint', 'layer' => 3,
        'zona' => 'Zona Reefer Yard', 'zona_id' => 'reefer', 'posisi' => 'Menara Pengawas Reefer Yard',
        'x' => 1112, 'y' => 245, 'harga' => 'Rp 30.000.000', 'unit' => '4 unit', 'subtotal' => 'Rp 120.000.000',
        'img' => 'assets/img/hardware/converted/item_16.png', 'modul' => 'reefer',
        'desc' => 'Kamera pemindai suhu inframerah untuk mendeteksi dini panas abnormal pada kompresor reefer dan mencegah bahaya kebakaran.'
    ],
    [
        'id' => 23, 'nama' => 'Smart Reefer Power Socket', 'model' => 'Marechal 380V/32A DECONTACTOR', 'tipe' => 'live', 'layer' => 2,
        'zona' => 'Zona Reefer Yard', 'zona_id' => 'reefer', 'posisi' => 'Rak Penumpukan Reefer (300 Titik)',
        'x' => 977, 'y' => 295, 'harga' => 'Rp 8.500.000 / titik', 'unit' => '300 titik', 'subtotal' => 'Rp 2.550.000.000',
        'img' => 'assets/img/hardware/converted/item_23.png', 'modul' => 'reefer',
        'desc' => 'Colokan industri 380V dengan meteran listrik Modbus dan sensor suhu kontinu untuk memantau rantai dingin dan alarm listrik trip.'
    ],

    // Zona Intermodal Rail Siding
    [
        'id' => 25, 'nama' => 'Rail Trackside Axle Counter', 'model' => 'Frauscher RSR180 Wheel Sensor', 'tipe' => 'live', 'layer' => 4,
        'zona' => 'Zona Intermodal Rail Siding', 'zona_id' => 'rail', 'posisi' => 'Kepala Rel Ujung Barat & Timur Siding',
        'x' => 220, 'y' => 638, 'harga' => 'Rp 85.000.000', 'unit' => '4 sensor', 'subtotal' => 'Rp 340.000.000',
        'img' => 'assets/img/hardware/converted/item_25.png', 'modul' => 'intermodal',
        'desc' => 'Sensor induktif pada kepala rel untuk menghitung gandar roda KA, estimasi kecepatan, dan konfirmasi kedatangan gerbong otomatis.'
    ],
    [
        'id' => 12, 'nama' => 'Access Point Outdoor', 'model' => 'Ubiquiti U7 Pro Outdoor WiFi 7', 'tipe' => 'blueprint', 'layer' => 2,
        'zona' => 'Zona Intermodal Rail Siding', 'zona_id' => 'rail', 'posisi' => 'Tiang Lampu Yard & Rail Siding (18 Titik)',
        'x' => 522, 'y' => 600, 'harga' => 'Rp 7.200.000', 'unit' => '18 unit', 'subtotal' => 'Rp 129.600.000',
        'img' => 'assets/img/hardware/converted/item_12.png', 'modul' => 'intermodal',
        'desc' => 'Pemancar nirkabel generasi WiFi 7 bersertifikasi IP67 untuk transmisi data real-time ke terminal VMT alat berat dan PDA tallyman.'
    ],
    [
        'id' => 17, 'nama' => 'GPS Tracker Truck & Asset', 'model' => 'Teltonika TAT240 4G LTE', 'tipe' => 'blueprint', 'layer' => 4,
        'zona' => 'Zona Intermodal Rail Siding', 'zona_id' => 'rail', 'posisi' => 'Gerbong Kereta Api & Armada Mitra',
        'x' => 750, 'y' => 638, 'harga' => 'Rp 2.500.000 / unit', 'unit' => '40 unit', 'subtotal' => 'Rp 100.000.000',
        'img' => 'assets/img/hardware/converted/item_17.png', 'modul' => 'trucking',
        'desc' => 'Modul pelacak otonom dengan baterai mandiri untuk memantau posisi rangkaian gerbong kontainer di rute Priok - Cikarang.'
    ],

    // Zona Behandle & Bea Cukai (Customs)
    [
        'id' => 19, 'nama' => 'Gantry Container X-Ray Scanner', 'model' => 'Nuctech MB1215DE (6 MeV)', 'tipe' => 'blueprint', 'layer' => 3,
        'zona' => 'Zona Behandle & Bea Cukai', 'zona_id' => 'customs', 'posisi' => 'Gedung Pemindai X-Ray Jalur Merah',
        'x' => 1112, 'y' => 330, 'harga' => 'Rp 12.500.000.000', 'unit' => '1 sistem', 'subtotal' => 'Rp 12.500.000.000',
        'img' => 'assets/img/hardware/converted/item_19.png', 'modul' => 'customs',
        'desc' => 'Pemindai radiasi sinar-X energi tinggi berdaya tembus baja >300 mm untuk pemeriksaan non-intrusif peti kemas impor Jalur Merah.'
    ],
    [
        'id' => 26, 'nama' => 'Electronic Cargo Smart Seal (E-Seal)', 'model' => 'Jointech JT701 GPS Smart E-Seal', 'tipe' => 'live', 'layer' => 3,
        'zona' => 'Zona Behandle & Bea Cukai', 'zona_id' => 'customs', 'posisi' => 'Pos Stasiun Pemeriksaan Segel Bea Cukai',
        'x' => 1112, 'y' => 495, 'harga' => 'Rp 3.200.000 / unit', 'unit' => '100 unit', 'subtotal' => 'Rp 320.000.000',
        'img' => 'assets/img/hardware/converted/item_26.png', 'modul' => 'customs',
        'desc' => 'Gembok elektronik pintar ber-GPS dan sensor anti-tamper untuk mengamankan kontainer transit pabean terintegrasi CEISA 4.0.'
    ],
    [
        'id' => 4, 'nama' => 'CCTV Yard 8 MP Perimeter', 'model' => 'Hikvision DS-2CD2T87G2H-LI 4K', 'tipe' => 'blueprint', 'layer' => 3,
        'zona' => 'Zona Behandle & Bea Cukai', 'zona_id' => 'customs', 'posisi' => 'Pagar Batas Perimeter Pabean (16 Titik)',
        'x' => 1112, 'y' => 410, 'harga' => 'Rp 6.500.000', 'unit' => '16 unit', 'subtotal' => 'Rp 104.000.000',
        'img' => 'assets/img/hardware/converted/item_4.png', 'modul' => 'customs',
        'desc' => 'Kamera perimeter 4K UHD dengan teknologi ColorVu malam hari untuk menjaga sterilitas kawasan pabean internasional.'
    ],

    // Zona Kantor & Datacenter
    [
        'id' => 13, 'nama' => 'Server NVR & Database YMS', 'model' => 'Hikvision DS-9664NI-M16/R & Rack Server', 'tipe' => 'blueprint', 'layer' => 2,
        'zona' => 'Zona Kantor & Datacenter', 'zona_id' => 'office', 'posisi' => 'Datacenter / Server Room (NOC)',
        'x' => 437, 'y' => 95, 'harga' => 'Rp 102.000.000', 'unit' => '2 unit', 'subtotal' => 'Rp 204.000.000',
        'img' => 'assets/img/hardware/converted/item_13.png', 'modul' => 'settings',
        'desc' => 'Pusat server rekaman CCTV 64 saluran dan server komputasi basis data MySQL sistem operasi pelabuhan kering CIDP YMS.'
    ],
    [
        'id' => 14, 'nama' => 'UPS Online 5000VA Rackmount', 'model' => 'APC Smart-UPS SRT 5000VA (SRT5KRMXLI)', 'tipe' => 'blueprint', 'layer' => 2,
        'zona' => 'Zona Kantor & Datacenter', 'zona_id' => 'office', 'posisi' => 'Datacenter & Ruang Kontrol Gate',
        'x' => 457, 'y' => 125, 'harga' => 'Rp 81.000.000', 'unit' => '4 unit', 'subtotal' => 'Rp 324.000.000',
        'img' => 'assets/img/hardware/converted/item_14.png', 'modul' => 'settings',
        'desc' => 'Pencatu daya cadangan darurat (On-Line Double Conversion 5kVA) untuk menjaga server dan gerbang tetap beroperasi saat listrik PLN padam.'
    ],
    [
        'id' => 11, 'nama' => 'Industrial Network Switch PoE+', 'model' => 'Managed Industrial PoE+ Gigabit Switch', 'tipe' => 'blueprint', 'layer' => 2,
        'zona' => 'Zona Kantor & Datacenter', 'zona_id' => 'office', 'posisi' => 'Core Switch Datacenter & Distribusi Lapangan',
        'x' => 417, 'y' => 125, 'harga' => 'Rp 10.000.000', 'unit' => '12 unit', 'subtotal' => 'Rp 120.000.000',
        'img' => 'assets/img/hardware/converted/item_11.png', 'modul' => 'settings',
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
    <!-- Header Modul: Clean & Minimal -->
    <div class="bg-white rounded-xl px-4 py-3 shadow-2xs border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#002f5e] via-[#0170b9] to-orange-500 text-white flex items-center justify-center text-lg shadow-xs flex-shrink-0">
                <i class="fa-solid <?= $denah_info['icon'] ?>"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-base font-bold text-gray-900">Denah Terminal 35 Ha &amp; Master Plan CIDP</h1>
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-[10px] font-semibold flex items-center">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span><?= $denah_info['status'] ?>
                    </span>
                    <span class="px-2 py-0.5 bg-orange-50 text-orange-700 border border-orange-200 rounded-full text-[10px] font-semibold">
                        7 Zona &amp; 25 Fasilitas
                    </span>
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-full text-[10px] font-semibold">
                        26 Hardware Pins
                    </span>
                </div>
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
        <div class="xl:col-span-3 bg-white rounded-2xl p-4 sm:p-5 shadow-lg border border-gray-200 relative overflow-hidden">
            
            <!-- Map Floating Controls & Legend Overlay -->
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3 pb-3 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <span class="text-xs font-bold tracking-wider text-gray-800 uppercase flex items-center">
                        <i class="fa-solid fa-compass text-[#0170b9] mr-2 text-sm"></i>
                        CIDP 35 Ha Master Plan Map (Skala 1:1.000)
                    </span>
                    <span class="text-[10px] text-gray-500 bg-gray-100 px-2 py-0.5 rounded border border-gray-300">
                        <i class="fa-solid fa-arrow-up text-blue-400 mr-1"></i>UTARA (NORTH)
                    </span>
                </div>
                
                <div class="flex items-center space-x-3 text-[11px] text-gray-800">
                    <span class="flex items-center">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping mr-1.5"></span>
                        <strong class="text-emerald-700">Pin Hijau:</strong> Live Software (11)
                    </span>
                    <span class="flex items-center">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-400 mr-1.5"></span>
                        <strong class="text-indigo-700">Pin Biru:</strong> Blueprint Fisik (15)
                    </span>
                    <button onclick="resetMapView()" class="px-2 py-0.5 bg-white hover:bg-gray-50 text-gray-800 rounded text-[10px] font-mono border border-gray-300 transition" title="Reset Tampilan Peta">
                        <i class="fa-solid fa-rotate-left mr-1"></i>Reset
                    </button>
                </div>
            </div>

            <!-- SVG Container -->
            <div class="w-full overflow-hidden rounded-xl bg-white border border-gray-200" id="mapSvgWrapper" style="cursor: grab;">
<svg id="denahSvg" viewBox="0 0 1200 780" class="w-full h-auto min-w-[900px] select-none" xmlns="http://www.w3.org/2000/svg" style="transform-origin: center center; transition: transform 0.1s ease-out;">
    <defs>
        <!-- Engineering Grid Patterns -->
        <pattern id="gridFine" width="10" height="10" patternUnits="userSpaceOnUse">
            <path d="M 10 0 L 0 0 0 10" fill="none" stroke="#e2e8f0" stroke-width="0.35"/>
        </pattern>
        <pattern id="gridMajor" width="50" height="50" patternUnits="userSpaceOnUse">
            <path d="M 50 0 L 0 0 0 50" fill="none" stroke="#cbd5e1" stroke-width="0.6"/>
        </pattern>
        <!-- Material Hatching Patterns -->
        <pattern id="hatchConcrete" width="8" height="8" patternTransform="rotate(45)" patternUnits="userSpaceOnUse">
            <line x1="0" y1="0" x2="0" y2="8" stroke="#cbd5e1" stroke-width="0.6"/>
        </pattern>
        <pattern id="hatchReinforced" width="6" height="6" patternTransform="rotate(45)" patternUnits="userSpaceOnUse">
            <line x1="0" y1="0" x2="0" y2="6" stroke="#94a3b8" stroke-width="0.6"/>
            <line x1="0" y1="0" x2="6" y2="0" stroke="#94a3b8" stroke-width="0.6"/>
        </pattern>
        <pattern id="asphaltFill" width="4" height="4" patternUnits="userSpaceOnUse">
            <rect width="4" height="4" fill="#334155"/>
            <circle cx="1" cy="1" r="0.4" fill="#475569"/>
            <circle cx="3" cy="3" r="0.4" fill="#1e293b"/>
        </pattern>
        <pattern id="asphaltLight" width="4" height="4" patternUnits="userSpaceOnUse">
            <rect width="4" height="4" fill="#475569"/>
            <circle cx="1.5" cy="1.5" r="0.4" fill="#64748b"/>
        </pattern>
        <pattern id="gravelFill" width="8" height="8" patternUnits="userSpaceOnUse">
            <rect width="8" height="8" fill="#e2e8f0"/>
            <circle cx="2" cy="2" r="0.9" fill="#94a3b8" stroke="#64748b" stroke-width="0.3"/>
            <circle cx="6" cy="5" r="0.7" fill="#cbd5e1" stroke="#94a3b8" stroke-width="0.3"/>
            <circle cx="4" cy="7" r="0.6" fill="#94a3b8" stroke="#64748b" stroke-width="0.3"/>
        </pattern>
        <pattern id="pavingTactile" width="6" height="6" patternUnits="userSpaceOnUse">
            <rect width="6" height="6" fill="#fef3c7"/>
            <circle cx="3" cy="3" r="1.1" fill="#d97706"/>
        </pattern>
        <pattern id="hazardStripe" width="12" height="12" patternTransform="rotate(45)" patternUnits="userSpaceOnUse">
            <rect width="6" height="12" fill="#facc15"/>
            <rect x="6" width="6" height="12" fill="#0f172a"/>
        </pattern>
        <!-- Tree Symbol -->
        <symbol id="treeSym" viewBox="0 0 16 16">
            <circle cx="8" cy="8" r="7" fill="#dcfce7" stroke="#16a34a" stroke-width="0.8"/>
            <circle cx="8" cy="4.5" r="4.5" fill="#bbf7d0" stroke="#15803d" stroke-width="0.5"/>
            <line x1="4" y1="8" x2="12" y2="8" stroke="#15803d" stroke-width="0.5"/>
            <line x1="8" y1="4" x2="8" y2="12" stroke="#15803d" stroke-width="0.5"/>
        </symbol>
        <!-- Filters for Live Telemetri -->
        <filter id="glowGreen" x="-30%" y="-30%" width="160%" height="160%">
            <feGaussianBlur stdDeviation="2.5" result="blur" />
            <feComposite in="SourceGraphic" in2="blur" operator="over" />
        </filter>
        <filter id="glowBlue" x="-30%" y="-30%" width="160%" height="160%">
            <feGaussianBlur stdDeviation="2.5" result="blur" />
            <feComposite in="SourceGraphic" in2="blur" operator="over" />
        </filter>
    </defs>

    <!-- 1. Background Grid & Engineering Sheet -->
    <rect width="1200" height="780" fill="#f8fafc" />
    <rect width="1200" height="780" fill="url(#gridFine)" />
    <rect width="1200" height="780" fill="url(#gridMajor)" />

    <!-- 2. Grid Coordinate Reference (Columns A-H, Rows 1-7) -->
    <g fill="#64748b" font-family="'Consolas', 'Courier New', monospace" font-size="10" font-weight="bold" text-anchor="middle">
        <circle cx="130" cy="22" r="9" fill="#ffffff" stroke="#94a3b8" stroke-width="0.8"/><text x="130" y="26">A</text>
        <circle cx="280" cy="22" r="9" fill="#ffffff" stroke="#94a3b8" stroke-width="0.8"/><text x="280" y="26">B</text>
        <circle cx="420" cy="22" r="9" fill="#ffffff" stroke="#94a3b8" stroke-width="0.8"/><text x="420" y="26">C</text>
        <circle cx="560" cy="22" r="9" fill="#ffffff" stroke="#94a3b8" stroke-width="0.8"/><text x="560" y="26">D</text>
        <circle cx="700" cy="22" r="9" fill="#ffffff" stroke="#94a3b8" stroke-width="0.8"/><text x="700" y="26">E</text>
        <circle cx="840" cy="22" r="9" fill="#ffffff" stroke="#94a3b8" stroke-width="0.8"/><text x="840" y="26">F</text>
        <circle cx="980" cy="22" r="9" fill="#ffffff" stroke="#94a3b8" stroke-width="0.8"/><text x="980" y="26">G</text>
        <circle cx="1100" cy="22" r="9" fill="#ffffff" stroke="#94a3b8" stroke-width="0.8"/><text x="1100" y="26">H</text>
        <circle cx="20" cy="90" r="9" fill="#ffffff" stroke="#94a3b8" stroke-width="0.8"/><text x="20" y="94">1</text>
        <circle cx="20" cy="210" r="9" fill="#ffffff" stroke="#94a3b8" stroke-width="0.8"/><text x="20" y="214">2</text>
        <circle cx="20" cy="330" r="9" fill="#ffffff" stroke="#94a3b8" stroke-width="0.8"/><text x="20" y="334">3</text>
        <circle cx="20" cy="440" r="9" fill="#ffffff" stroke="#94a3b8" stroke-width="0.8"/><text x="20" y="444">4</text>
        <circle cx="20" cy="540" r="9" fill="#ffffff" stroke="#94a3b8" stroke-width="0.8"/><text x="20" y="544">5</text>
        <circle cx="20" cy="630" r="9" fill="#ffffff" stroke="#94a3b8" stroke-width="0.8"/><text x="20" y="634">6</text>
        <circle cx="20" cy="710" r="9" fill="#ffffff" stroke="#94a3b8" stroke-width="0.8"/><text x="20" y="714">7</text>
    </g>

    <!-- 3. Perimeter Boundary Fence (35 Ha Kawasan Pabean Berikat) -->
    <rect x="40" y="42" width="1120" height="675" rx="4" fill="none" stroke="#0f172a" stroke-width="2" stroke-dasharray="12 4 3 4" />
    <text x="50" y="38" fill="#475569" font-family="'Consolas', monospace" font-size="8.5" font-weight="bold">BATAS PERIMETER KAWASAN PABEAN CIDP 35 HA — KOORDINAT UTM 48S 107°08'42"E 06°18'25"S — PEIL EL. +0.00 M</text>
    
    <!-- Buffer Landscape Trees around perimeter -->
    <g>
        <use href="#treeSym" x="45" y="46" width="14" height="14"/><use href="#treeSym" x="65" y="46" width="14" height="14"/><use href="#treeSym" x="85" y="46" width="14" height="14"/>
        <use href="#treeSym" x="1142" y="150" width="14" height="14"/><use href="#treeSym" x="1142" y="250" width="14" height="14"/><use href="#treeSym" x="1142" y="350" width="14" height="14"/>
        <use href="#treeSym" x="1142" y="470" width="14" height="14"/><use href="#treeSym" x="1142" y="580" width="14" height="14"/>
        <use href="#treeSym" x="45" y="700" width="14" height="14"/><use href="#treeSym" x="65" y="700" width="14" height="14"/><use href="#treeSym" x="85" y="700" width="14" height="14"/>
    </g>

    <!-- ========================================================================= -->
    <!-- LAYER 1: TATA LETAK SIPIL, JALAN, MARKA, PEDESTRIAN & FASILITAS BANGUNAN -->
    <!-- ========================================================================= -->
    <g id="svgLayer1" class="transition-opacity duration-300">

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- HIGHWAY ENTRANCE ACCESS ROAD (NORTH / DI KOLOM F / KANAN)     -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <g id="highwayApproach">
            <rect x="845" y="8" width="105" height="42" fill="url(#asphaltFill)" stroke="none"/>
            <line x1="897" y1="8" x2="897" y2="50" stroke="#facc15" stroke-width="2" stroke-dasharray="6 4"/>
            <line x1="845" y1="8" x2="845" y2="50" stroke="#ffffff" stroke-width="2"/>
            <line x1="950" y1="8" x2="950" y2="50" stroke="#ffffff" stroke-width="2"/>
            <text x="860" y="24" fill="#38bdf8" font-size="7" font-family="'Consolas', monospace" font-weight="bold">INBOUND ▼</text>
            <text x="905" y="24" fill="#f87171" font-size="7" font-family="'Consolas', monospace" font-weight="bold">▲ OUTBOUND</text>
            <text x="897" y="38" fill="#facc15" font-size="6.5" font-family="'Consolas', monospace" font-weight="bold" text-anchor="middle">AKSES JL. RAYA ARTERI CIKARANG</text>
        </g>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- A. ZONA FASILITAS UMUM & K3 (NORTH-WEST, KOLOM A - C, y=50..155) -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <g id="zone_public" class="zone-element cursor-pointer" onclick="showZoneDetails('public')">
            <!-- Genset Shelter -->
            <g id="fac_f_genset_shelter" onclick="showFacilityDetails('f_genset_shelter', event)">
                <rect x="55" y="50" width="85" height="55" fill="#ffffff" stroke="#0891b2" stroke-width="1.6" rx="2"/>
                <text x="97" y="73" fill="#0e7490" font-family="'Consolas', monospace" font-size="7" font-weight="bold" text-anchor="middle">GENSET SHELTER</text>
                <text x="97" y="90" fill="#64748b" font-family="'Consolas', monospace" font-size="6" text-anchor="middle">1.500 KVA DIESEL</text>
            </g>

            <!-- Masjid Al-Hidayah -->
            <g id="fac_f_musholla" onclick="showFacilityDetails('f_musholla', event)">
                <rect x="150" y="50" width="85" height="65" fill="#ffffff" stroke="#16a34a" stroke-width="1.8" rx="2"/>
                <circle cx="192" cy="75" r="14" fill="#dcfce7" stroke="#16a34a" stroke-width="1"/>
                <text x="192" y="79" fill="#15803d" font-family="'Consolas', monospace" font-size="8" font-weight="bold" text-anchor="middle">MASJID</text>
                <text x="192" y="103" fill="#16a34a" font-family="'Consolas', monospace" font-size="6.5" text-anchor="middle">AL-HIDAYAH</text>
            </g>

            <!-- Kantin Pujasera -->
            <g id="fac_f_kantin" onclick="showFacilityDetails('f_kantin', event)">
                <rect x="245" y="50" width="65" height="65" fill="#ffffff" stroke="#0f172a" stroke-width="1.6" rx="2"/>
                <text x="277" y="78" fill="#0f172a" font-family="'Consolas', monospace" font-size="8" font-weight="bold" text-anchor="middle">KANTIN</text>
                <text x="277" y="96" fill="#64748b" font-family="'Consolas', monospace" font-size="6.5" text-anchor="middle">PUJASERA</text>
            </g>

            <!-- Koperasi Karyawan -->
            <g id="fac_f_koperasi" onclick="showFacilityDetails('f_koperasi', event)">
                <rect x="320" y="50" width="55" height="65" fill="#ffffff" stroke="#0f172a" stroke-width="1.6" rx="2"/>
                <text x="347" y="78" fill="#0f172a" font-family="'Consolas', monospace" font-size="7.5" font-weight="bold" text-anchor="middle">KOOP</text>
                <text x="347" y="96" fill="#64748b" font-family="'Consolas', monospace" font-size="6" text-anchor="middle">PORT MART</text>
            </g>

            <!-- Klinik P3K 24 Jam -->
            <g id="fac_f_klinik" onclick="showFacilityDetails('f_klinik', event)">
                <rect x="150" y="120" width="65" height="35" fill="#ffffff" stroke="#dc2626" stroke-width="1.8" rx="2"/>
                <rect x="175" y="124" width="10" height="18" fill="#ef4444"/>
                <rect x="171" y="128" width="18" height="10" fill="#ef4444"/>
                <text x="182" y="150" fill="#991b1b" font-family="'Consolas', monospace" font-size="6.5" font-weight="bold" text-anchor="middle">KLINIK P3K</text>
            </g>

            <!-- Pos Damkar & Pompa Hydrant -->
            <g id="fac_f_damkar" onclick="showFacilityDetails('f_damkar', event)">
                <rect x="225" y="120" width="75" height="35" fill="#ffffff" stroke="#dc2626" stroke-width="2" rx="2"/>
                <rect x="230" y="125" width="65" height="12" fill="#fee2e2" stroke="#ef4444" stroke-width="1"/>
                <text x="262" y="135" fill="#991b1b" font-family="'Consolas', monospace" font-size="7" font-weight="bold" text-anchor="middle">DAMKAR</text>
                <text x="262" y="149" fill="#dc2626" font-family="'Consolas', monospace" font-size="6" text-anchor="middle">HYDRANT PUMP</text>
            </g>

            <!-- Toilet Umum -->
            <g id="fac_f_toilet" onclick="showFacilityDetails('f_toilet', event)">
                <rect x="310" y="120" width="35" height="35" fill="#ffffff" stroke="#0f172a" stroke-width="1.4" rx="2"/>
                <text x="327" y="141" fill="#64748b" font-family="'Consolas', monospace" font-size="7" font-weight="bold" text-anchor="middle">WC</text>
            </g>
        </g>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- B. ZONA KANTOR & DATACENTER (NORTH-CENTER, KOLOM C - E, y=50..155) -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <g id="zone_office" class="zone-element cursor-pointer" onclick="showZoneDetails('office')">
            <!-- Datacenter & Server Room NOC -->
            <g id="fac_f_datacenter" onclick="showFacilityDetails('f_datacenter', event)">
                <rect x="390" y="50" width="95" height="95" fill="#ffffff" stroke="#002f5e" stroke-width="2.2" rx="3"/>
                <rect x="395" y="55" width="85" height="22" fill="#e0f2fe" stroke="#0284c7" stroke-width="1"/>
                <text x="437" y="69" fill="#002f5e" font-family="'Consolas', monospace" font-size="8.5" font-weight="bold" text-anchor="middle">DATACENTER NOC</text>
                <text x="437" y="95" fill="#0f172a" font-family="'Consolas', monospace" font-size="7.5" text-anchor="middle">SERVER TIER-3</text>
                <text x="437" y="112" fill="#64748b" font-family="'Consolas', monospace" font-size="6.5" text-anchor="middle">NVR REKAMAN 64CH</text>
                <text x="437" y="128" fill="#0284c7" font-family="'Consolas', monospace" font-size="6.5" text-anchor="middle">UPS 5KVA &amp; MYSQL DB</text>
            </g>

            <!-- Kantor Utama PT MTI -->
            <g id="fac_f_admin_office" onclick="showFacilityDetails('f_admin_office', event)">
                <rect x="495" y="50" width="125" height="95" fill="#ffffff" stroke="#002f5e" stroke-width="2.2" rx="3"/>
                <rect x="500" y="55" width="115" height="22" fill="#e0f2fe" stroke="#0284c7" stroke-width="1"/>
                <text x="557" y="70" fill="#002f5e" font-family="'Consolas', monospace" font-size="8.5" font-weight="bold" text-anchor="middle">KANTOR UTAMA PT MTI</text>
                <text x="557" y="95" fill="#0f172a" font-family="'Consolas', monospace" font-size="8" text-anchor="middle">HEADQUARTERS 2 LANTAI</text>
                <text x="557" y="112" fill="#64748b" font-family="'Consolas', monospace" font-size="7" text-anchor="middle">OPERATION &amp; CUSTOMER CARE</text>
                <text x="557" y="128" fill="#0284c7" font-family="'Consolas', monospace" font-size="6.5" text-anchor="middle">PORT BILLING &amp; FINANCE</text>
            </g>

            <!-- Ruang Meeting & Training Room -->
            <g id="fac_f_meeting_room" onclick="showFacilityDetails('f_meeting_room', event)">
                <rect x="630" y="50" width="75" height="95" fill="#ffffff" stroke="#0f172a" stroke-width="1.8" rx="2"/>
                <text x="667" y="80" fill="#0f172a" font-family="'Consolas', monospace" font-size="8" font-weight="bold" text-anchor="middle">RUANG MEETING</text>
                <text x="667" y="102" fill="#64748b" font-family="'Consolas', monospace" font-size="7" text-anchor="middle">AUDITORIUM</text>
                <text x="667" y="122" fill="#64748b" font-family="'Consolas', monospace" font-size="6.5" text-anchor="middle">TRAINING CENTER</text>
            </g>

            <!-- Parkir Karyawan & Tamu -->
            <g id="fac_f_parking_staff" onclick="showFacilityDetails('f_parking_staff', event)">
                <rect x="715" y="50" width="75" height="95" fill="url(#hatchConcrete)" stroke="#0f172a" stroke-width="1.8" rx="2"/>
                <text x="752" y="80" fill="#0f172a" font-family="'Consolas', monospace" font-size="8" font-weight="bold" text-anchor="middle">PARKIR STAF</text>
                <text x="752" y="100" fill="#475569" font-family="'Consolas', monospace" font-size="7" text-anchor="middle">60 MOBIL</text>
                <line x1="725" y1="115" x2="780" y2="115" stroke="#ffffff" stroke-width="1.5" stroke-dasharray="4 2"/>
            </g>
        </g>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- C. ZONA GATE & SECURITY COMPLEX (NORTH-EAST, KOLOM F / KANAN) -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <g id="zone_gate" class="zone-element cursor-pointer" onclick="showZoneDetails('gate')">
            <!-- Security Post -->
            <g id="fac_f_security_gate" onclick="showFacilityDetails('f_security_gate', event)">
                <rect x="795" y="50" width="45" height="55" fill="#f8fafc" stroke="#002f5e" stroke-width="2" rx="2"/>
                <rect x="798" y="55" width="39" height="15" fill="#002f5e" rx="1"/>
                <text x="818" y="66" fill="#ffffff" font-family="'Consolas', monospace" font-size="7" font-weight="bold" text-anchor="middle">SECURITY</text>
                <text x="818" y="85" fill="#0f172a" font-family="'Consolas', monospace" font-size="6" text-anchor="middle">LOKET e-PASS</text>
                <text x="818" y="98" fill="#0284c7" font-family="'Consolas', monospace" font-size="6" text-anchor="middle">SCANNER QR</text>
            </g>

            <!-- Driver Rest Area -->
            <g id="fac_f_driver_rest" onclick="showFacilityDetails('f_driver_rest', event)">
                <rect x="795" y="110" width="45" height="45" fill="#ffffff" stroke="#0f172a" stroke-width="1.8" rx="2"/>
                <text x="818" y="128" fill="#0f172a" font-family="'Consolas', monospace" font-size="7" font-weight="bold" text-anchor="middle">REST AREA</text>
                <text x="818" y="140" fill="#64748b" font-family="'Consolas', monospace" font-size="6" text-anchor="middle">DRIVER ROOM</text>
                <text x="818" y="150" fill="#16a34a" font-family="'Consolas', monospace" font-size="5.5" text-anchor="middle">AC &amp; WATER</text>
            </g>

            <!-- Gatehouse Portal Complex (Kolom F, x=845..950) -->
            <g id="gateLanes">
                <rect x="845" y="50" width="105" height="140" fill="none" stroke="#0284c7" stroke-width="2" stroke-dasharray="8 4" rx="3"/>
                <rect x="845" y="50" width="105" height="20" fill="#0284c7" rx="2"/>
                <text x="897" y="63" fill="#ffffff" font-family="'Consolas', monospace" font-size="8.5" font-weight="bold" text-anchor="middle">GATEHOUSE PORTAL COMPLEX</text>

                <!-- Lane 1 Inbound -->
                <g id="fac_f_gate_in" onclick="showFacilityDetails('f_gate_in', event)">
                    <rect x="855" y="73" width="40" height="113" fill="url(#asphaltFill)" stroke="#38bdf8" stroke-width="1"/>
                    <text x="862" y="150" fill="#38bdf8" font-size="7.5" font-family="'Consolas', monospace" font-weight="bold" transform="rotate(-90 862 150)">LANE 1 INBOUND (TRUK)</text>
                    <polygon points="875,115 871,125 879,125" fill="#38bdf8"/>
                </g>

                <!-- Weighbridge 80t -->
                <g id="fac_f_weighbridge" onclick="showFacilityDetails('f_weighbridge', event)">
                    <rect x="858" y="130" width="34" height="35" fill="url(#hatchReinforced)" stroke="#0f172a" stroke-width="1.8"/>
                    <rect x="856" y="130" width="2" height="35" fill="#facc15"/>
                    <rect x="892" y="130" width="2" height="35" fill="#facc15"/>
                    <text x="875" y="151" fill="#0f172a" font-family="'Consolas', monospace" font-size="6.5" font-weight="bold" text-anchor="middle">WB 80T</text>
                </g>

                <!-- Lane 2 Outbound -->
                <g id="fac_f_gate_out" onclick="showFacilityDetails('f_gate_out', event)">
                    <rect x="905" y="73" width="40" height="113" fill="url(#asphaltFill)" stroke="#f87171" stroke-width="1"/>
                    <text x="930" y="135" fill="#f87171" font-size="7.5" font-family="'Consolas', monospace" font-weight="bold" transform="rotate(-90 930 135)">LANE 2 OUTBOUND</text>
                    <polygon points="925,125 929,115 921,115" fill="#f87171"/>
                </g>

                <!-- Island Divider -->
                <rect x="898" y="78" width="4" height="105" fill="#e2e8f0" stroke="#94a3b8" stroke-width="0.8"/>
            </g>

            <!-- Truck Queue / Holding Yard (Kolom G, x=960..1150) -->
            <g id="fac_f_truck_queue" onclick="showFacilityDetails('f_truck_queue', event)">
                <rect x="960" y="50" width="190" height="105" fill="url(#asphaltFill)" stroke="#0f172a" stroke-width="2" rx="3"/>
                <text x="1055" y="70" fill="#ffffff" font-family="'Consolas', monospace" font-size="8.5" font-weight="bold" text-anchor="middle">PARKIR ANTRIAN TRUK</text>
                <text x="1055" y="85" fill="#facc15" font-family="'Consolas', monospace" font-size="7.5" font-weight="bold" text-anchor="middle">KAPASITAS: 40 TRAILER</text>
                <!-- Angled trailer lines -->
                <line x1="975" y1="95" x2="990" y2="142" stroke="#facc15" stroke-width="1.2"/>
                <line x1="1005" y1="95" x2="1020" y2="142" stroke="#facc15" stroke-width="1.2"/>
                <line x1="1035" y1="95" x2="1050" y2="142" stroke="#facc15" stroke-width="1.2"/>
                <line x1="1065" y1="95" x2="1080" y2="142" stroke="#facc15" stroke-width="1.2"/>
                <line x1="1095" y1="95" x2="1110" y2="142" stroke="#facc15" stroke-width="1.2"/>
                <line x1="1125" y1="95" x2="1140" y2="142" stroke="#facc15" stroke-width="1.2"/>
                <text x="1055" y="150" fill="#94a3b8" font-size="6" text-anchor="middle">HOLDING AREA PRE-GATE</text>
            </g>
        </g>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- D. PEDESTRIAN WALKWAY (NORTH STRIP, y=160..170)             -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <g id="pedestrianNetwork">
            <rect x="55" y="160" width="1095" height="9" fill="url(#pavingTactile)" stroke="#f59e0b" stroke-width="1" rx="1.5"/>
            <line x1="55" y1="164.5" x2="1150" y2="164.5" stroke="#d97706" stroke-width="0.8" stroke-dasharray="4 2"/>
            <text x="400" y="158" fill="#b45309" font-family="'Consolas', monospace" font-size="7" font-weight="bold">JALUR PEDESTRIAN K3 (TROTOAR TAKTIL LEBAR 3.00 M)</text>
        </g>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- E. TRANSFER HAUL ROAD (y=175..205)                          -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <g id="haulRoadTransfer">
            <rect x="230" y="175" width="870" height="30" fill="url(#asphaltFill)" stroke="none"/>
            <line x1="230" y1="190" x2="1100" y2="190" stroke="#facc15" stroke-width="1.8" stroke-dasharray="8 6"/>
            <polygon points="450,186 460,182 460,190" fill="#facc15"/>
            <polygon points="800,186 810,182 810,190" fill="#facc15"/>
            <text x="550" y="188" fill="#f8fafc" font-size="8" font-family="'Consolas', monospace" font-weight="bold" text-anchor="middle">← TRANSFER HAUL ROAD (CONTAINER KE YARD &amp; GATE) →</text>
        </g>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- F. ZONA LOGISTIK BARAT: CFS, GUDANG, WORKSHOP (y=210..530) -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <g id="zone_west_logistics">
            <!-- M&R Workshop -->
            <g id="fac_f_workshop_mr" class="cursor-pointer" onclick="showFacilityDetails('f_workshop_mr', event)">
                <rect x="55" y="210" width="165" height="95" fill="#f8fafc" stroke="#0f172a" stroke-width="1.8" rx="2"/>
                <rect x="60" y="215" width="155" height="30" fill="url(#hatchConcrete)" stroke="#94a3b8" stroke-width="0.8"/>
                <text x="137" y="233" fill="#0f172a" font-family="'Consolas', monospace" font-size="8" font-weight="bold" text-anchor="middle">M&amp;R WORKSHOP BENGKEL ALAT BERAT</text>
                <text x="137" y="265" fill="#475569" font-family="'Consolas', monospace" font-size="7" text-anchor="middle">MAINTENANCE REACH STACKER &amp; TRUK</text>
                <text x="137" y="285" fill="#64748b" font-family="'Consolas', monospace" font-size="6.5" text-anchor="middle">OVERHEAD CRANE 20T</text>
            </g>

            <!-- CFS Warehouse -->
            <g id="fac_f_cfs" class="cursor-pointer" onclick="showFacilityDetails('f_cfs', event)">
                <rect x="55" y="315" width="165" height="120" fill="#ffffff" stroke="#0f172a" stroke-width="2" rx="3"/>
                <rect x="58" y="320" width="159" height="30" fill="#f1f5f9" stroke="#cbd5e1" stroke-width="1"/>
                <text x="137" y="340" fill="#0f172a" font-family="'Consolas', monospace" font-size="9" font-weight="bold" text-anchor="middle">CFS WAREHOUSE 4.000 M²</text>
                <rect x="65" y="355" width="24" height="20" fill="url(#asphaltLight)" stroke="#94a3b8" stroke-width="0.8"/>
                <rect x="95" y="355" width="24" height="20" fill="url(#asphaltLight)" stroke="#94a3b8" stroke-width="0.8"/>
                <rect x="125" y="355" width="24" height="20" fill="url(#asphaltLight)" stroke="#94a3b8" stroke-width="0.8"/>
                <rect x="155" y="355" width="24" height="20" fill="url(#asphaltLight)" stroke="#94a3b8" stroke-width="0.8"/>
                <rect x="185" y="355" width="24" height="20" fill="url(#asphaltLight)" stroke="#94a3b8" stroke-width="0.8"/>
                <text x="77" y="368" fill="#ffffff" font-size="7" font-weight="bold" text-anchor="middle">D1</text>
                <text x="107" y="368" fill="#ffffff" font-size="7" font-weight="bold" text-anchor="middle">D2</text>
                <text x="137" y="368" fill="#ffffff" font-size="7" font-weight="bold" text-anchor="middle">D3</text>
                <text x="167" y="368" fill="#ffffff" font-size="7" font-weight="bold" text-anchor="middle">D4</text>
                <text x="197" y="368" fill="#ffffff" font-size="7" font-weight="bold" text-anchor="middle">D5</text>
                <text x="137" y="405" fill="#475569" font-family="'Consolas', monospace" font-size="7" text-anchor="middle">STUFFING &amp; STRIPPING LCL</text>
                <text x="137" y="425" fill="#64748b" font-family="'Consolas', monospace" font-size="6.5" text-anchor="middle">CROSS DOCKING PLATFORM</text>
            </g>

            <!-- Transit Warehouse -->
            <g id="fac_f_warehouse" class="cursor-pointer" onclick="showFacilityDetails('f_warehouse', event)">
                <rect x="55" y="445" width="165" height="85" fill="#ffffff" stroke="#0f172a" stroke-width="1.8" rx="2"/>
                <rect x="60" y="450" width="155" height="18" fill="#f8fafc" stroke="#cbd5e1" stroke-width="0.8"/>
                <text x="137" y="463" fill="#0f172a" font-family="'Consolas', monospace" font-size="8" font-weight="bold" text-anchor="middle">TRANSIT WAREHOUSE</text>
                <text x="137" y="492" fill="#64748b" font-family="'Consolas', monospace" font-size="7" text-anchor="middle">GUDANG TRANSIT KARGO UMUM</text>
            </g>

            <!-- West Haul Corridor -->
            <rect x="225" y="205" width="35" height="340" fill="url(#asphaltLight)" stroke="none"/>
            <line x1="242" y1="205" x2="242" y2="545" stroke="#facc15" stroke-width="1.5" stroke-dasharray="6 4"/>

            <!-- West Pedestrian Walkway -->
            <rect x="215" y="210" width="8" height="330" fill="url(#pavingTactile)" stroke="#f59e0b" stroke-width="1" rx="1.5"/>
        </g>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- G. ZONA CONTAINER YARD: BLOK A, B, C, D (15 HA, y=210..535)-->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <g id="zone_cfs_yard" class="zone-element cursor-pointer" onclick="showZoneDetails('cfs_yard')">
            <rect id="fac_f_stacking_yard" x="260" y="210" width="640" height="330" fill="none" pointer-events="none"/>

            <!-- Empty Container Depot -->
            <g id="fac_f_empty_depot" class="cursor-pointer" onclick="showFacilityDetails('f_empty_depot', event)">
                <rect x="265" y="210" width="105" height="155" fill="#f1f5f9" stroke="#64748b" stroke-width="2" rx="3"/>
                <text x="317" y="235" fill="#1e293b" font-family="'Consolas', monospace" font-size="8" font-weight="bold" text-anchor="middle">EMPTY DEPOT</text>
                <text x="317" y="250" fill="#475569" font-family="'Consolas', monospace" font-size="7" text-anchor="middle">2.500 TEU</text>
                <rect x="275" y="260" width="85" height="25" fill="#e2e8f0" stroke="#94a3b8" stroke-width="0.8"/>
                <rect x="275" y="290" width="85" height="25" fill="#e2e8f0" stroke="#94a3b8" stroke-width="0.8"/>
                <rect x="275" y="320" width="85" height="25" fill="#e2e8f0" stroke="#94a3b8" stroke-width="0.8"/>
                <text x="317" y="340" fill="#64748b" font-size="7" font-weight="bold" text-anchor="middle">5 TIERS</text>
            </g>

            <!-- BLOK A: LADEN EXPORT (y=210..365) -->
            <g id="blokA">
                <rect x="380" y="210" width="245" height="155" fill="#eff6ff" stroke="#3b82f6" stroke-width="2" rx="3"/>
                <line x1="430" y1="210" x2="430" y2="365" stroke="#bfdbfe" stroke-width="1" stroke-dasharray="3 3"/>
                <line x1="480" y1="210" x2="480" y2="365" stroke="#bfdbfe" stroke-width="1" stroke-dasharray="3 3"/>
                <line x1="530" y1="210" x2="530" y2="365" stroke="#bfdbfe" stroke-width="1" stroke-dasharray="3 3"/>
                <line x1="580" y1="210" x2="580" y2="365" stroke="#bfdbfe" stroke-width="1" stroke-dasharray="3 3"/>
                <text x="502" y="235" fill="#1d4ed8" font-family="'Consolas', monospace" font-size="11" font-weight="bold" text-anchor="middle">BLOK A — LADEN EXPORT</text>
                <text x="502" y="252" fill="#2563eb" font-family="'Consolas', monospace" font-size="8" text-anchor="middle">72.00 M × 24.00 M (5 BAYS × 7 ROWS × 4 TIERS)</text>
                <text x="502" y="300" fill="#3b82f6" font-family="'Consolas', monospace" font-size="28" font-weight="900" opacity="0.18" text-anchor="middle">A</text>
                <text x="405" y="360" fill="#2563eb" font-size="6.5" font-weight="bold">BAY 01</text>
                <text x="455" y="360" fill="#2563eb" font-size="6.5" font-weight="bold">BAY 03</text>
                <text x="505" y="360" fill="#2563eb" font-size="6.5" font-weight="bold">BAY 05</text>
                <text x="555" y="360" fill="#2563eb" font-size="6.5" font-weight="bold">BAY 07</text>
                <text x="600" y="360" fill="#2563eb" font-size="6.5" font-weight="bold">BAY 09</text>
            </g>

            <!-- BLOK B: LADEN IMPORT (y=210..365) -->
            <g id="blokB">
                <rect x="635" y="210" width="245" height="155" fill="#eff6ff" stroke="#3b82f6" stroke-width="2" rx="3"/>
                <line x1="685" y1="210" x2="685" y2="365" stroke="#bfdbfe" stroke-width="1" stroke-dasharray="3 3"/>
                <line x1="735" y1="210" x2="735" y2="365" stroke="#bfdbfe" stroke-width="1" stroke-dasharray="3 3"/>
                <line x1="785" y1="210" x2="785" y2="365" stroke="#bfdbfe" stroke-width="1" stroke-dasharray="3 3"/>
                <line x1="835" y1="210" x2="835" y2="365" stroke="#bfdbfe" stroke-width="1" stroke-dasharray="3 3"/>
                <text x="757" y="235" fill="#1d4ed8" font-family="'Consolas', monospace" font-size="11" font-weight="bold" text-anchor="middle">BLOK B — LADEN IMPORT</text>
                <text x="757" y="252" fill="#2563eb" font-family="'Consolas', monospace" font-size="8" text-anchor="middle">72.00 M × 24.00 M (5 BAYS × 7 ROWS × 4 TIERS)</text>
                <text x="757" y="300" fill="#3b82f6" font-family="'Consolas', monospace" font-size="28" font-weight="900" opacity="0.18" text-anchor="middle">B</text>
                <text x="660" y="360" fill="#2563eb" font-size="6.5" font-weight="bold">BAY 01</text>
                <text x="710" y="360" fill="#2563eb" font-size="6.5" font-weight="bold">BAY 03</text>
                <text x="760" y="360" fill="#2563eb" font-size="6.5" font-weight="bold">BAY 05</text>
                <text x="810" y="360" fill="#2563eb" font-size="6.5" font-weight="bold">BAY 07</text>
                <text x="855" y="360" fill="#2563eb" font-size="6.5" font-weight="bold">BAY 09</text>
            </g>

            <!-- CENTRAL HAUL ROAD (y=375..405) -->
            <rect x="260" y="375" width="625" height="32" fill="url(#asphaltFill)" stroke="none"/>
            <line x1="260" y1="391" x2="885" y2="391" stroke="#facc15" stroke-width="1.8" stroke-dasharray="8 6"/>
            <line x1="260" y1="377" x2="885" y2="377" stroke="#ffffff" stroke-width="1.2"/>
            <line x1="260" y1="405" x2="885" y2="405" stroke="#ffffff" stroke-width="1.2"/>
            <polygon points="400,388 390,384 390,392" fill="#facc15"/>
            <polygon points="700,388 690,384 690,392" fill="#facc15"/>
            <text x="550" y="388" fill="#f8fafc" font-size="8" font-family="'Consolas', monospace" font-weight="bold" text-anchor="middle">← CENTRAL HAUL ROAD (LEBAR 20.00 M) →</text>

            <!-- BLOK C: DOMESTIC (y=415..535) -->
            <g id="blokC">
                <rect x="380" y="415" width="245" height="120" fill="#ecfdf5" stroke="#10b981" stroke-width="2" rx="3"/>
                <line x1="430" y1="415" x2="430" y2="535" stroke="#a7f3d0" stroke-width="1" stroke-dasharray="3 3"/>
                <line x1="480" y1="415" x2="480" y2="535" stroke="#a7f3d0" stroke-width="1" stroke-dasharray="3 3"/>
                <line x1="530" y1="415" x2="530" y2="535" stroke="#a7f3d0" stroke-width="1" stroke-dasharray="3 3"/>
                <line x1="580" y1="415" x2="580" y2="535" stroke="#a7f3d0" stroke-width="1" stroke-dasharray="3 3"/>
                <text x="502" y="438" fill="#047857" font-family="'Consolas', monospace" font-size="11" font-weight="bold" text-anchor="middle">BLOK C — DOMESTIK ANTAR-PULAU</text>
                <text x="502" y="455" fill="#059669" font-family="'Consolas', monospace" font-size="8" text-anchor="middle">72.00 M × 24.00 M (5 BAYS × 7 ROWS × 4 TIERS)</text>
                <text x="502" y="492" fill="#10b981" font-family="'Consolas', monospace" font-size="28" font-weight="900" opacity="0.18" text-anchor="middle">C</text>
                <text x="405" y="528" fill="#059669" font-size="6.5" font-weight="bold">BAY 01</text>
                <text x="455" y="528" fill="#059669" font-size="6.5" font-weight="bold">BAY 03</text>
                <text x="505" y="528" fill="#059669" font-size="6.5" font-weight="bold">BAY 05</text>
                <text x="555" y="528" fill="#059669" font-size="6.5" font-weight="bold">BAY 07</text>
            </g>

            <!-- BLOK D: BUFFER (y=415..535) -->
            <g id="blokD">
                <rect x="635" y="415" width="245" height="120" fill="#f5f3ff" stroke="#8b5cf6" stroke-width="2" rx="3"/>
                <line x1="685" y1="415" x2="685" y2="535" stroke="#ddd6fe" stroke-width="1" stroke-dasharray="3 3"/>
                <line x1="735" y1="415" x2="735" y2="535" stroke="#ddd6fe" stroke-width="1" stroke-dasharray="3 3"/>
                <line x1="785" y1="415" x2="785" y2="535" stroke="#ddd6fe" stroke-width="1" stroke-dasharray="3 3"/>
                <line x1="835" y1="415" x2="835" y2="535" stroke="#ddd6fe" stroke-width="1" stroke-dasharray="3 3"/>
                <text x="757" y="438" fill="#6d28d9" font-family="'Consolas', monospace" font-size="11" font-weight="bold" text-anchor="middle">BLOK D — BUFFER &amp; TRANSHIPMENT</text>
                <text x="757" y="455" fill="#7c3aed" font-family="'Consolas', monospace" font-size="8" text-anchor="middle">72.00 M × 24.00 M (5 BAYS × 7 ROWS × 4 TIERS)</text>
                <text x="757" y="492" fill="#8b5cf6" font-family="'Consolas', monospace" font-size="28" font-weight="900" opacity="0.18" text-anchor="middle">D</text>
                <text x="660" y="528" fill="#7c3aed" font-size="6.5" font-weight="bold">BAY 01</text>
                <text x="710" y="528" fill="#7c3aed" font-size="6.5" font-weight="bold">BAY 03</text>
                <text x="760" y="528" fill="#7c3aed" font-size="6.5" font-weight="bold">BAY 05</text>
                <text x="810" y="528" fill="#7c3aed" font-size="6.5" font-weight="bold">BAY 07</text>
            </g>
        </g>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- H. REEFER (COLD CHAIN) & HAZMAT DG (EAST, y=210..535)       -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <g id="zone_reefer" class="zone-element cursor-pointer" onclick="showZoneDetails('reefer')">
            <!-- REEFER TERMINAL -->
            <g id="fac_f_reefer_racks" onclick="showFacilityDetails('f_reefer_racks', event)">
                <rect x="895" y="210" width="165" height="195" fill="#ecfeff" stroke="#06b6d4" stroke-width="2" rx="3"/>
                <text x="977" y="235" fill="#0e7490" font-family="'Consolas', monospace" font-size="9" font-weight="bold" text-anchor="middle">REEFER YARD (COLD CHAIN)</text>
                <text x="977" y="252" fill="#0891b2" font-family="'Consolas', monospace" font-size="8" text-anchor="middle">KAPASITAS: 300 POWER PLUGS</text>
                <circle cx="925" cy="275" r="3" fill="#06b6d4"/><circle cx="950" cy="275" r="3" fill="#06b6d4"/><circle cx="975" cy="275" r="3" fill="#06b6d4"/><circle cx="1000" cy="275" r="3" fill="#06b6d4"/><circle cx="1025" cy="275" r="3" fill="#06b6d4"/>
                <circle cx="925" cy="300" r="3" fill="#06b6d4"/><circle cx="950" cy="300" r="3" fill="#06b6d4"/><circle cx="975" cy="300" r="3" fill="#06b6d4"/><circle cx="1000" cy="300" r="3" fill="#06b6d4"/><circle cx="1025" cy="300" r="3" fill="#06b6d4"/>
                <circle cx="925" cy="325" r="3" fill="#06b6d4"/><circle cx="950" cy="325" r="3" fill="#06b6d4"/><circle cx="975" cy="325" r="3" fill="#06b6d4"/><circle cx="1000" cy="325" r="3" fill="#06b6d4"/><circle cx="1025" cy="325" r="3" fill="#06b6d4"/>
                <text x="977" y="380" fill="#0891b2" font-family="'Consolas', monospace" font-size="6.5" text-anchor="middle">CATWALK MONITORING 380V/32A</text>
            </g>

            <!-- BLOK E: DANGEROUS GOODS HAZMAT -->
            <g id="blokDG">
                <rect x="895" y="415" width="165" height="120" fill="#fef2f2" stroke="#ef4444" stroke-width="2" rx="3"/>
                <rect x="899" y="419" width="157" height="112" fill="none" stroke="#dc2626" stroke-width="2" stroke-dasharray="8 4"/>
                <rect x="895" y="415" width="165" height="16" fill="url(#hazardStripe)" opacity="0.3"/>
                <text x="977" y="445" fill="#b91c1c" font-family="'Consolas', monospace" font-size="8.5" font-weight="bold" text-anchor="middle">BLOK E — BUNDED DG YARD</text>
                <text x="977" y="460" fill="#dc2626" font-family="'Consolas', monospace" font-size="7" text-anchor="middle">HAZMAT DANGEROUS GOODS</text>
                <text x="977" y="478" fill="#ef4444" font-family="'Consolas', monospace" font-size="6.5" text-anchor="middle">TANGGUL BETON 1.4 METER</text>
                <rect x="947" y="490" width="60" height="25" fill="#fecaca" stroke="#dc2626" stroke-width="1"/>
                <text x="977" y="506" fill="#991b1b" font-size="7" font-weight="bold" text-anchor="middle">SUMP PIT</text>
            </g>

            <!-- Reefer Control Room -->
            <g id="fac_f_reefer_control" onclick="showFacilityDetails('f_reefer_control', event)">
                <rect x="1070" y="210" width="85" height="65" fill="#ffffff" stroke="#0891b2" stroke-width="1.6" rx="2"/>
                <text x="1112" y="235" fill="#0e7490" font-family="'Consolas', monospace" font-size="7.5" font-weight="bold" text-anchor="middle">REEFER CONTROL</text>
                <text x="1112" y="250" fill="#64748b" font-family="'Consolas', monospace" font-size="6.5" text-anchor="middle">RUANG MONITORING</text>
            </g>
        </g>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- I. ZONA BEA CUKAI & BEHANDLE (EAST, y=285..535)             -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <g id="zone_customs" class="zone-element cursor-pointer" onclick="showZoneDetails('customs')">
            <rect x="1070" y="285" width="85" height="250" rx="3" fill="#fef2f2" fill-opacity="0.4" stroke="#dc2626" stroke-width="1.5" stroke-dasharray="6 3"/>

            <!-- X-Ray Scanner -->
            <g id="fac_f_behandle_xray" onclick="showFacilityDetails('f_behandle_xray', event)">
                <rect x="1075" y="295" width="75" height="65" fill="#ffffff" stroke="#0f172a" stroke-width="2" rx="2"/>
                <rect x="1080" y="300" width="65" height="15" fill="#fee2e2" stroke="#ef4444" stroke-width="1"/>
                <text x="1112" y="311" fill="#991b1b" font-family="'Consolas', monospace" font-size="7" font-weight="bold" text-anchor="middle">X-RAY 6 MeV</text>
                <text x="1112" y="330" fill="#475569" font-family="'Consolas', monospace" font-size="6" text-anchor="middle">NUCTECH MB1215DE</text>
                <text x="1112" y="345" fill="#dc2626" font-family="'Consolas', monospace" font-size="6" text-anchor="middle">JALUR MERAH</text>
            </g>

            <!-- Quarantine Lab -->
            <g id="fac_f_quarantine" onclick="showFacilityDetails('f_quarantine', event)">
                <rect x="1075" y="370" width="75" height="55" fill="#ffffff" stroke="#0f172a" stroke-width="1.8" rx="2"/>
                <text x="1112" y="392" fill="#0f172a" font-family="'Consolas', monospace" font-size="7.5" font-weight="bold" text-anchor="middle">LAB KARANTINA</text>
                <text x="1112" y="408" fill="#64748b" font-family="'Consolas', monospace" font-size="6.5" text-anchor="middle">HEWAN &amp; TUMBUHAN</text>
            </g>

            <!-- Kantor KPPBC -->
            <g id="fac_f_kppbc" onclick="showFacilityDetails('f_kppbc', event)">
                <rect x="1075" y="435" width="75" height="90" fill="#ffffff" stroke="#dc2626" stroke-width="2" rx="2"/>
                <rect x="1078" y="440" width="69" height="18" fill="#fee2e2" stroke="#dc2626" stroke-width="1"/>
                <text x="1112" y="453" fill="#991b1b" font-family="'Consolas', monospace" font-size="7.5" font-weight="bold" text-anchor="middle">KANTOR KPPBC</text>
                <text x="1112" y="478" fill="#0f172a" font-family="'Consolas', monospace" font-size="7" text-anchor="middle">PELAYANAN PABEAN</text>
                <text x="1112" y="494" fill="#64748b" font-family="'Consolas', monospace" font-size="6.5" text-anchor="middle">SISTEM CEISA 4.0</text>
                <text x="1112" y="512" fill="#dc2626" font-family="'Consolas', monospace" font-size="6" text-anchor="middle">POS SMART E-SEAL</text>
            </g>
        </g>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- J. SOUTH HAUL ROAD (y=545..572)                             -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <g id="haulRoadSouth">
            <rect x="55" y="545" width="1045" height="26" fill="url(#asphaltLight)" stroke="none"/>
            <line x1="55" y1="558" x2="1100" y2="558" stroke="#facc15" stroke-width="1.5" stroke-dasharray="6 4"/>
            <text x="580" y="556" fill="#f8fafc" font-size="7.5" font-family="'Consolas', monospace" font-weight="bold" text-anchor="middle">← SOUTH HAUL ROAD (APRON CFS &amp; ACCESS KE SIDING KA) →</text>
        </g>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- K. ZONA INTERMODAL RAIL SIDING (SOUTH / BOTTOM, y=575..685) -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <g id="zone_rail" class="zone-element cursor-pointer" onclick="showZoneDetails('rail')">
            <!-- Outline / Boundary -->
            <rect x="55" y="575" width="820" height="110" rx="3" fill="#fffbeb" fill-opacity="0.5" stroke="#d97706" stroke-width="1.5" stroke-dasharray="6 3"/>
            <text x="465" y="590" text-anchor="middle" fill="#b45309" font-family="'Consolas', monospace" font-size="9" font-weight="bold" letter-spacing="1">ZONA 4: INTERMODAL RAIL SIDING (400 METER DUAL TRACK / DUA SEPUR SEJAJAR)</text>

            <!-- Loading Ramp Platform -->
            <g id="fac_f_loading_ramp" onclick="showFacilityDetails('f_loading_ramp', event)">
                <rect x="180" y="595" width="685" height="18" fill="url(#hatchConcrete)" stroke="#0f172a" stroke-width="1.4"/>
                <text x="522" y="608" fill="#0f172a" font-family="'Consolas', monospace" font-size="7.5" font-weight="bold" text-anchor="middle">PLATFORM LOADING RAMP 400 M (TRANSFER REACH STACKER KE GERBONG PPCW)</text>
            </g>

            <!-- Rail Ballast Bed -->
            <rect x="180" y="620" width="685" height="26" fill="url(#gravelFill)" stroke="#94a3b8" stroke-width="1"/>

            <!-- Concrete Sleepers -->
            <line x1="180" y1="626" x2="865" y2="626" stroke="#64748b" stroke-width="3" stroke-dasharray="2 3"/>
            <line x1="180" y1="639" x2="865" y2="639" stroke="#64748b" stroke-width="3" stroke-dasharray="2 3"/>

            <!-- Dual Steel Rails -->
            <line x1="180" y1="624" x2="865" y2="624" stroke="#0f172a" stroke-width="1.6"/>
            <line x1="180" y1="628" x2="865" y2="628" stroke="#0f172a" stroke-width="1.6"/>
            <line x1="180" y1="637" x2="865" y2="637" stroke="#0f172a" stroke-width="1.6"/>
            <line x1="180" y1="641" x2="865" y2="641" stroke="#0f172a" stroke-width="1.6"/>

            <!-- Rail Buffer Stop (Sepur Badug) -->
            <rect x="865" y="621" width="8" height="24" fill="#dc2626" stroke="#991b1b" stroke-width="1.5" rx="1"/>
            <line x1="865" y1="621" x2="873" y2="645" stroke="#fef08a" stroke-width="2"/>

            <!-- Rail Office / Stasiun Dispatcher -->
            <g id="fac_f_rail_office" onclick="showFacilityDetails('f_rail_office', event)">
                <rect x="65" y="595" width="105" height="75" fill="#ffffff" stroke="#0f172a" stroke-width="1.8" rx="2"/>
                <rect x="70" y="600" width="95" height="20" fill="#f1f5f9" stroke="#cbd5e1" stroke-width="0.8"/>
                <text x="117" y="613" fill="#0f172a" font-family="'Consolas', monospace" font-size="7.5" font-weight="bold" text-anchor="middle">STASIUN DISPATCHER</text>
                <text x="117" y="638" fill="#64748b" font-family="'Consolas', monospace" font-size="7" text-anchor="middle">KONTROL KA LOGISTIK</text>
                <text x="117" y="655" fill="#d97706" font-family="'Consolas', monospace" font-size="6.5" text-anchor="middle">KAI LOGISTIK CIDP</text>
            </g>
        </g>

        <!-- ZEBRA CROSSINGS -->
        <g id="zebraCrossings">
            <!-- Zebra 1: Admin to Yard Transfer -->
            <rect x="540" y="160" width="30" height="15" fill="#1e293b"/>
            <line x1="545" y1="160" x2="545" y2="175" stroke="#ffffff" stroke-width="4"/>
            <line x1="555" y1="160" x2="555" y2="175" stroke="#ffffff" stroke-width="4"/>
            <line x1="565" y1="160" x2="565" y2="175" stroke="#ffffff" stroke-width="4"/>
            <!-- Zebra 2: Gate to Transfer Road -->
            <rect x="882" y="175" width="30" height="30" fill="#1e293b"/>
            <line x1="882" y1="180" x2="912" y2="180" stroke="#ffffff" stroke-width="4"/>
            <line x1="882" y1="188" x2="912" y2="188" stroke="#ffffff" stroke-width="4"/>
            <line x1="882" y1="196" x2="912" y2="196" stroke="#ffffff" stroke-width="4"/>
            <!-- Zebra 3: Across Central Haul -->
            <rect x="485" y="375" width="35" height="32" fill="#1e293b"/>
            <line x1="490" y1="379" x2="515" y2="379" stroke="#ffffff" stroke-width="3"/>
            <line x1="490" y1="386" x2="515" y2="386" stroke="#ffffff" stroke-width="3"/>
            <line x1="490" y1="393" x2="515" y2="393" stroke="#ffffff" stroke-width="3"/>
            <line x1="490" y1="400" x2="515" y2="400" stroke="#ffffff" stroke-width="3"/>
        </g>

        <!-- DIMENSION LINES -->
        <g id="dimensionLines" font-family="'Consolas', monospace" font-size="7.5" font-weight="bold">
            <!-- Total Width 380m -->
            <line x1="40" y1="755" x2="880" y2="755" stroke="#002f5e" stroke-width="1"/>
            <line x1="40" y1="748" x2="40" y2="762" stroke="#002f5e" stroke-width="1.4"/>
            <line x1="880" y1="748" x2="880" y2="762" stroke="#002f5e" stroke-width="1.4"/>
            <polygon points="40,755 48,753 48,757" fill="#002f5e"/>
            <polygon points="880,755 872,753 872,757" fill="#002f5e"/>
            <text x="460" y="752" fill="#002f5e" text-anchor="middle">LEBAR TOTAL KAWASAN = 380.00 METER</text>

            <!-- Total Height 260m -->
            <line x1="1175" y1="42" x2="1175" y2="717" stroke="#002f5e" stroke-width="1"/>
            <line x1="1168" y1="42" x2="1182" y2="42" stroke="#002f5e" stroke-width="1.4"/>
            <line x1="1168" y1="717" x2="1182" y2="717" stroke="#002f5e" stroke-width="1.4"/>
            <polygon points="1175,42 1173,50 1177,50" fill="#002f5e"/>
            <polygon points="1175,717 1173,709 1177,709" fill="#002f5e"/>
            <text x="1188" y="380" fill="#002f5e" text-anchor="middle" transform="rotate(90 1188 380)">PANJANG TOTAL = 260.00 M</text>

            <!-- Blok A Dimension -->
            <line x1="380" y1="206" x2="625" y2="206" stroke="#0284c7" stroke-width="0.8"/>
            <line x1="380" y1="203" x2="380" y2="209" stroke="#0284c7" stroke-width="1"/>
            <line x1="625" y1="203" x2="625" y2="209" stroke="#0284c7" stroke-width="1"/>
            <text x="502" y="204" fill="#0284c7" text-anchor="middle">72.00 M</text>

            <!-- Elevation Tags -->
            <text x="45" y="740" fill="#475569" font-size="7">▽ EL. +0.00 M PEIL KAWASAN</text>
            <text x="270" y="540" fill="#475569" font-size="7">▽ EL. +0.30 M APRON YARD</text>
            <text x="180" y="660" fill="#475569" font-size="7">▽ EL. +0.65 M KEPALA REL KA</text>
            <text x="850" y="195" fill="#475569" font-size="7">▽ EL. +0.45 M TIMBANGAN GATE</text>

            <!-- Turning Radius Arcs -->
            <path d="M 260 375 Q 285 375 285 350" fill="none" stroke="#ef4444" stroke-width="1.2" stroke-dasharray="3 3"/>
            <text x="270" y="370" fill="#ef4444" font-size="6.5" font-family="'Consolas', monospace" font-weight="bold">R15M</text>
        </g>

        <!-- COMPASS ROSE (NORTH = UP) -->
        <g transform="translate(1120, 30)" class="select-none pointer-events-none">
            <circle cx="0" cy="0" r="18" fill="#ffffff" stroke="#002f5e" stroke-width="1.5" />
            <polygon points="0,0 0,-22 -5,-5" fill="#002f5e" />
            <polygon points="0,0 0,-22 5,-5" fill="#0284c7" />
            <polygon points="0,0 22,0 5,5" fill="#64748b" />
            <polygon points="0,0 -22,0 -5,-5" fill="#94a3b8" />
            <polygon points="0,0 0,22 5,5" fill="#94a3b8" />
            <circle cx="0" cy="0" r="3" fill="#f8fafc" stroke="#002f5e" stroke-width="1"/>
            <text x="0" y="-25" text-anchor="middle" fill="#002f5e" font-family="'Consolas', sans-serif" font-size="11" font-weight="900">U</text>
            <text x="0" y="-34" text-anchor="middle" fill="#64748b" font-family="'Consolas', sans-serif" font-size="6" font-weight="bold">NORTH</text>
        </g>

        <!-- SCALE BAR -->
        <g transform="translate(50, 765)" font-family="'Consolas', monospace" font-size="8" fill="#0f172a">
            <line x1="0" y1="0" x2="200" y2="0" stroke="#0f172a" stroke-width="2.5" />
            <line x1="0" y1="-6" x2="0" y2="6" stroke="#0f172a" stroke-width="1.5" />
            <line x1="50" y1="-4" x2="50" y2="0" stroke="#0f172a" stroke-width="1.2" />
            <line x1="100" y1="-6" x2="100" y2="6" stroke="#0f172a" stroke-width="1.5" />
            <line x1="200" y1="-6" x2="200" y2="6" stroke="#0f172a" stroke-width="1.5" />
            <text x="-4" y="-8">0</text>
            <text x="42" y="-8">50m</text>
            <text x="90" y="-8">100m</text>
            <text x="185" y="-8">200m</text>
        </g>

        <!-- TITLE BLOCK (ISO 7200 Etiket Gambar Teknik) - Placed neatly at bottom right -->
        <g id="cadTitleBlock" transform="translate(890, 580)" class="select-none pointer-events-none">
            <rect width="265" height="135" fill="#ffffff" stroke="#002f5e" stroke-width="2" rx="3"/>
            <rect width="265" height="24" fill="#002f5e" rx="2"/>
            <text x="132" y="16" fill="#ffffff" font-family="'Consolas', monospace" font-size="8.5" font-weight="bold" text-anchor="middle">CONCLUSION INTERMODAL DRY PORT (CIDP)</text>
            <line x1="0" y1="52" x2="265" y2="52" stroke="#cbd5e1" stroke-width="1"/>
            <line x1="0" y1="80" x2="265" y2="80" stroke="#cbd5e1" stroke-width="1"/>
            <line x1="0" y1="108" x2="265" y2="108" stroke="#cbd5e1" stroke-width="1"/>
            <line x1="145" y1="24" x2="145" y2="135" stroke="#cbd5e1" stroke-width="1"/>
            
            <text x="8" y="38" fill="#0f172a" font-family="'Consolas', monospace" font-size="7" font-weight="bold">PROYEK: CIDP 35 HA YMS</text>
            <text x="8" y="46" fill="#64748b" font-family="'Consolas', monospace" font-size="6">OPERATOR: PT MULTI TERMINAL INDONESIA</text>
            <text x="8" y="66" fill="#0f172a" font-family="'Consolas', monospace" font-size="7" font-weight="bold">JUDUL: MASTER SITE PLAN TATA LETAK</text>
            <text x="8" y="74" fill="#64748b" font-family="'Consolas', monospace" font-size="6">ORIENTASI: KANTOR UTARA, REL SELATAN</text>
            <text x="8" y="94" fill="#002f5e" font-family="'Consolas', monospace" font-size="6.5" font-weight="bold">ENGINEER: JUAN G. &amp; ARMANSYAH M.</text>
            <text x="8" y="102" fill="#16a34a" font-family="'Consolas', monospace" font-size="6" font-weight="bold">STATUS: AS-BUILT APPROVED</text>
            <text x="8" y="122" fill="#0284c7" font-family="'Consolas', monospace" font-size="6.5" font-weight="bold">SISTEM: 4 LAYER INTERAKTIF</text>

            <text x="153" y="38" fill="#0f172a" font-family="'Consolas', monospace" font-size="7" font-weight="bold">SKALA: 1:2.000</text>
            <text x="153" y="46" fill="#64748b" font-family="'Consolas', monospace" font-size="6">SATUAN: METER (WGS84)</text>
            <text x="153" y="66" fill="#0f172a" font-family="'Consolas', monospace" font-size="6.5" font-weight="bold">TGL: 30 SEP 2026</text>
            <text x="153" y="74" fill="#64748b" font-family="'Consolas', monospace" font-size="6">REVISI: REV.08 FINAL</text>
            <text x="153" y="94" fill="#002f5e" font-family="'Consolas', monospace" font-size="6.5" font-weight="bold">DWG-CIV-001</text>
            <text x="153" y="102" fill="#64748b" font-family="'Consolas', monospace" font-size="5.5">LEMBAR: 01 / 01</text>
            <text x="153" y="122" fill="#1e293b" font-family="'Consolas', monospace" font-size="6" font-weight="bold">26 SENSORS &amp; 25 FAC</text>
        </g>
    </g>

    <!-- ========================================================================= -->
    <!-- LAYER 2: DAYA & UTILITAS (ELECTRICAL & UTILITY DUCTING GRID)              -->
    <!-- ========================================================================= -->
    <g id="svgLayer2" class="transition-opacity duration-300">
        <!-- Main 20kV Underground Power Feeder Line from Genset (x=97, y=73) to Datacenter & Gate (x=897, y=135) -->
        <path d="M 97 73 L 240 190 L 437 95 L 897 135" fill="none" stroke="#f59e0b" stroke-width="2.2" stroke-dasharray="6 3"/>
        <!-- Power Line to Rail Siding High Mast -->
        <path d="M 240 190 L 240 558 L 522 600" fill="none" stroke="#f59e0b" stroke-width="1.8" stroke-dasharray="6 3"/>
        <text x="550" y="186" fill="#d97706" font-family="'Consolas', monospace" font-size="7" font-weight="bold">FEEDER KABEL DAYA TEGANGAN MENENGAH 20 KV DARI GENSET 1.500 KVA</text>
    </g>

    <!-- ========================================================================= -->
    <!-- LAYER 3: KEAMANAN & PABEAN (STERILE CUSTOMS & RADIATION BUFFER BOUNDARY)  -->
    <!-- ========================================================================= -->
    <g id="svgLayer3" class="transition-opacity duration-300">
        <!-- Radiation Exclusion Zone around X-Ray -->
        <circle cx="1112" cy="330" r="40" fill="#fee2e2" fill-opacity="0.35" stroke="#dc2626" stroke-width="1.8" stroke-dasharray="4 2"/>
        <text x="1112" y="278" fill="#dc2626" font-family="'Consolas', monospace" font-size="6.5" font-weight="bold" text-anchor="middle">RADIUS STERIL RADIASI 15M</text>
        <!-- Sterile Customs Fence Boundary -->
        <rect x="1068" y="280" width="89" height="260" fill="none" stroke="#dc2626" stroke-width="1.8" stroke-dasharray="8 4"/>
        <text x="1112" y="547" fill="#b91c1c" font-family="'Consolas', monospace" font-size="6" font-weight="bold" text-anchor="middle">BATAS STERIL PABEAN CEISA 4.0</text>
    </g>

    <!-- ========================================================================= -->
    <!-- LAYER 4: SENSOR & OTOMASI (26 TITIK HARDWARE INTERAKTIF)                 -->
    <!-- ========================================================================= -->
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
                
                <!-- Expanded Click Area (40px hit area) -->
                <circle cx="<?= $hw['x'] ?>" cy="<?= $hw['y'] ?>" r="20" fill="transparent" cursor="pointer" />
                
                <?php if ($is_live): ?>
                    <!-- Survey/engineering marker for Live Pins -->
                    <circle cx="<?= $hw['x'] ?>" cy="<?= $hw['y'] ?>" r="13" fill="none" stroke="#10b981" stroke-width="0.8" stroke-dasharray="3 2" class="animate-spin-slow" style="transform-origin: <?= $hw['x'] ?>px <?= $hw['y'] ?>px;" />
                    <circle cx="<?= $hw['x'] ?>" cy="<?= $hw['y'] ?>" r="10" fill="#ecfdf5" stroke="#10b981" stroke-width="2" filter="url(#glowGreen)" />
                    <!-- Crosshair -->
                    <path d="M <?= $hw['x'] - 11 ?> <?= $hw['y'] ?> L <?= $hw['x'] + 11 ?> <?= $hw['y'] ?> M <?= $hw['x'] ?> <?= $hw['y'] - 11 ?> L <?= $hw['x'] ?> <?= $hw['y'] + 11 ?>" stroke="#10b981" stroke-width="0.6" />
                    <!-- Badge Nomor -->
                    <text x="<?= $hw['x'] ?>" y="<?= $hw['y'] + 3 ?>" text-anchor="middle" fill="#065f46" font-size="8" font-family="'Consolas', monospace" font-weight="bold"><?= $hw['id'] ?></text>
                <?php else: ?>
                    <!-- Survey/engineering marker for Blueprint Pins -->
                    <circle cx="<?= $hw['x'] ?>" cy="<?= $hw['y'] ?>" r="9" fill="#eef2ff" stroke="#4f46e5" stroke-width="1.8" />
                    <!-- Crosshair -->
                    <path d="M <?= $hw['x'] - 10 ?> <?= $hw['y'] ?> L <?= $hw['x'] + 10 ?> <?= $hw['y'] ?> M <?= $hw['x'] ?> <?= $hw['y'] - 10 ?> L <?= $hw['x'] ?> <?= $hw['y'] + 10 ?>" stroke="#4f46e5" stroke-width="0.6" />
                    <!-- Badge Nomor -->
                    <text x="<?= $hw['x'] ?>" y="<?= $hw['y'] + 3 ?>" text-anchor="middle" fill="#312e81" font-size="7.5" font-family="'Consolas', monospace" font-weight="bold"><?= $hw['id'] ?></text>
                <?php endif; ?>

                <!-- Tooltip Hover SVG -->
                <title><?= $hw['id'] ?>. <?= htmlspecialchars($hw['nama']) ?> (<?= $is_live ? 'LIVE SIMULASI' : 'BLUEPRINT FISIK' ?>)</title>
            </g>
        <?php endforeach; ?>
    </g>

</svg>

<!-- FLOATING ZOOM CONTROLS UI -->
<div class="absolute top-16 right-4 flex flex-col gap-1 z-10">
    <button onclick="mapZoom(0.2)" class="w-8 h-8 bg-white border border-gray-200 shadow-sm rounded flex items-center justify-center text-[#0170b9] hover:bg-gray-50 transition" title="Zoom In">
        <i class="fa-solid fa-plus text-sm"></i>
    </button>
    <button onclick="mapZoom(-0.2)" class="w-8 h-8 bg-white border border-gray-200 shadow-sm rounded flex items-center justify-center text-[#0170b9] hover:bg-gray-50 transition" title="Zoom Out">
        <i class="fa-solid fa-minus text-sm"></i>
    </button>
    <button onclick="resetMapView()" class="w-8 h-8 bg-white border border-gray-200 shadow-sm rounded flex items-center justify-center text-gray-600 hover:bg-gray-50 transition mt-1" title="Reset View">
        <i class="fa-solid fa-rotate-left text-sm"></i>
    </button>
    <button onclick="fitToScreen()" class="w-8 h-8 bg-white border border-gray-200 shadow-sm rounded flex items-center justify-center text-gray-600 hover:bg-gray-50 transition" title="Fit to Screen">
        <i class="fa-solid fa-expand text-sm"></i>
    </button>
    <div id="zoomLevelDisplay" class="bg-white border border-gray-200 shadow-sm rounded px-2 py-1 text-[10px] text-gray-600 font-mono text-center mt-1 w-full box-border">100%</div>
</div>
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
                <img id="inspImg" src="assets/img/hardware/converted/item_1.png" alt="Hardware Image" class="max-h-36 max-w-full object-contain transition-all duration-300 drop-shadow-sm">
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
                desc: "<?= addslashes($fac['desc']) ?>",
                img: "<?= $fac['img'] ?? '' ?>",
                hw_id: <?= isset($fac['hw_id']) ? $fac['hw_id'] : 'null' ?>,
                hw_nama: "<?= addslashes($fac['hw_nama'] ?? '') ?>"
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

    actionBtn.onclick = null; // Reset click handler

    if (hw.tipe === 'live') {
        actionWrapper.classList.remove('hidden');
        if (blueprintNotice) blueprintNotice.classList.add('hidden');

        const moduleMapping = {
            'gate': { url: 'dashboard.php?page=gate', label: '⚡ Uji & Monitor Sensor di Modul Gate' },
            'yard': { url: 'dashboard.php?page=yard', label: '⚡ Monitor & Trigger di Modul Yard' },
            'reefer': { url: 'dashboard.php?page=reefer', label: '⚡ Uji Sensor Telemetri & Thermal Reefer' },
            'cfs': { url: 'dashboard.php?page=cfs', label: '⚡ Uji Sensor Dock & Timbangan CFS' },
            'intermodal': { url: 'dashboard.php?page=intermodal', label: '⚡ Monitor Axle Counter & RFID Rail' },
            'customs': { url: 'dashboard.php?page=customs', label: '⚡ Uji E-Seal & Scanner X-Ray CEISA' },
            'alat': { url: 'dashboard.php?page=alat', label: '⚡ Monitor Telemetri GPS & VMT Alat' },
            'scanner': { url: 'dashboard.php?page=cfs', label: '⚡ Uji Barcode/HHT Scanner di Gudang CFS' },
            'kontainer': { url: 'dashboard.php?page=kontainer', label: '⚡ Buka Siklus Kontainer di Modul Peti Kemas' },
            'trucking': { url: 'dashboard.php?page=trucking', label: '⚡ Monitor GPS Truk & Armada' },
            'settings': { url: 'dashboard.php?page=settings', label: '⚡ Konfigurasi Datacenter & Gateway' }
        };

        const targetMod = moduleMapping[hw.modul] || { url: 'dashboard.php?page=gate', label: '⚡ Uji Sensor di Modul Terkait' };
        actionBtn.href = targetMod.url;
        actionText.textContent = targetMod.label;
    } else {
        // BLUEPRINT FISIK: Sediakan tautan operasional ke modul terkait
        actionWrapper.classList.remove('hidden');
        if (blueprintNotice) blueprintNotice.classList.remove('hidden');
        
        const bpMapping = {
            'gate': { url: 'dashboard.php?page=gate', label: '⚡ Monitor Zona di Modul Gate' },
            'yard': { url: 'dashboard.php?page=yard', label: '⚡ Monitor Zona di Modul Yard' },
            'reefer': { url: 'dashboard.php?page=reefer', label: '⚡ Monitor Zona di Modul Reefer' },
            'cfs': { url: 'dashboard.php?page=cfs', label: '⚡ Buka Denah & Sensor CFS' },
            'intermodal': { url: 'dashboard.php?page=intermodal', label: '⚡ Buka Jalur Siding KA di Intermodal' },
            'customs': { url: 'dashboard.php?page=customs', label: '⚡ Monitor Jalur Merah di Bea Cukai' },
            'alat': { url: 'dashboard.php?page=alat', label: '⚡ Monitor Alat di Modul Fleet' },
            'scanner': { url: 'dashboard.php?page=cfs', label: '⚡ Uji Scanner di Modul CFS' },
            'kontainer': { url: 'dashboard.php?page=kontainer', label: '⚡ Buka Modul Kontainer' },
            'trucking': { url: 'dashboard.php?page=trucking', label: '⚡ Buka Modul Trucking' },
            'settings': { url: 'dashboard.php?page=settings', label: '⚡ Buka Konfigurasi Server' }
        };
        const bpTarget = bpMapping[hw.modul] || { url: 'dashboard.php?page=gate', label: '⚡ Buka Modul Terkait' };
        actionBtn.href = bpTarget.url;
        actionText.textContent = bpTarget.label;
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

    // Foto Produk / Perangkat Utama Terpasang
    const imgEl = document.getElementById('inspImg');
    const fallbackEl = document.getElementById('inspIconFallback');
    if (fac.img) {
        imgEl.src = fac.img;
        imgEl.classList.remove('hidden');
        fallbackEl.classList.add('hidden');
    } else {
        imgEl.classList.add('hidden');
        fallbackEl.classList.remove('hidden');
        const fallbackIcon = document.getElementById('inspFallbackIcon');
        fallbackIcon.className = "fa-solid " + fac.icon + " text-[#0170b9]";
    }

    // Badge Fasilitas
    const badge = document.getElementById('inspTypeBadge');
    badge.textContent = "FASILITAS OPERASIONAL";
    badge.className = "px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200";

    // Sembunyikan harga satuan murni fasilitas
    document.getElementById('inspPriceBox').classList.add('hidden');
    const blueprintNotice = document.getElementById('inspBlueprintNotice');
    if (blueprintNotice) blueprintNotice.classList.add('hidden');

    // Tombol Aksi Langsung ke Modul atau Hardware Terkait
    const actionWrapper = document.getElementById('inspActionWrapper');
    const actionBtn = document.getElementById('inspActionBtn');
    const actionText = document.getElementById('inspActionText');

    actionBtn.onclick = null; // Reset click handler

    // Pemetaan khusus fasilitas langsung ke modul operasional & trigger sensor
    const facilityDirectMap = {
        'f_cfs': { url: 'dashboard.php?page=cfs', label: '⚡ Buka Denah CFS & Konsol Pemicu Sensor' },
        'f_gate_in': { url: 'dashboard.php?page=gate', label: '⚡ Buka Modul Gate & Pemicu ANPR Inbound' },
        'f_gate_out': { url: 'dashboard.php?page=gate', label: '⚡ Buka Modul Gate & Palang Outbound' },
        'f_weighbridge': { url: 'dashboard.php?page=gate', label: '⚡ Buka Modul Gate & Pemicu Weighbridge VGM' },
        'f_reefer_control': { url: 'dashboard.php?page=reefer', label: '⚡ Buka Modul Reefer & Telemetri Suhu' },
        'f_reefer_racks': { url: 'dashboard.php?page=reefer', label: '⚡ Buka Matriks 300 Steker Reefer 380V' },
        'f_genset_shelter': { url: 'dashboard.php?page=reefer', label: '⚡ Uji ATS Genset 1.500 kVA di Modul Reefer' },
        'f_loading_ramp': { url: 'dashboard.php?page=intermodal', label: '⚡ Buka Modul Intermodal Rail Siding' },
        'f_rail_office': { url: 'dashboard.php?page=intermodal', label: '⚡ Monitor Jadwal Langsiran KA' },
        'f_behandle_xray': { url: 'dashboard.php?page=customs', label: '⚡ Buka Scanner X-Ray di Modul Bea Cukai' },
        'f_kppbc': { url: 'dashboard.php?page=customs', label: '⚡ Buka Integrasi Kepabeanan CEISA 4.0' },
        'f_quarantine': { url: 'dashboard.php?page=customs', label: '⚡ Buka Ruang Karantina Bea Cukai' },
        'f_stacking_yard': { url: 'dashboard.php?page=yard', label: '⚡ Buka Denah Stacking Blok A-E' },
        'f_empty_depot': { url: 'dashboard.php?page=yard', label: '⚡ Buka Empty Container Depot' },
        'f_workshop_mr': { url: 'dashboard.php?page=alat', label: '⚡ Buka Bengkel M&R & Telemetri Alat' },
        'f_datacenter': { url: 'dashboard.php?page=settings', label: '⚡ Buka Datacenter & Jaringan Core' }
    };

    if (facilityDirectMap[facId]) {
        actionWrapper.classList.remove('hidden');
        actionBtn.href = facilityDirectMap[facId].url;
        actionText.textContent = facilityDirectMap[facId].label;
    } else if (fac.hw_id) {
        actionWrapper.classList.remove('hidden');
        actionBtn.href = "javascript:void(0)";
        actionBtn.onclick = function(e) {
            if (e) e.preventDefault();
            showHardwareDetails(fac.hw_id, e);
        };
        actionText.textContent = "🔍 Periksa " + (fac.hw_nama || "Hardware #" + fac.hw_id);
    } else {
        actionWrapper.classList.add('hidden');
    }
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


// ==========================================
// ZOOM & PAN ENGINE
// ==========================================
const mapSvgWrapper = document.getElementById('mapSvgWrapper');
const denahSvg = document.getElementById('denahSvg');
const zoomLevelDisplay = document.getElementById('zoomLevelDisplay');

let scale = 1;
let minScale = 0.3;
let maxScale = 5;
let translateX = 0;
let translateY = 0;
let isDragging = false;
let startX, startY;

function updateTransform() {
    denahSvg.style.transform = 'translate(' + translateX + 'px, ' + translateY + 'px) scale(' + scale + ')';
    if(zoomLevelDisplay) {
        zoomLevelDisplay.innerText = Math.round(scale * 100) + '%';
    }
}

function mapZoom(delta) {
    scale += delta;
    scale = Math.min(Math.max(minScale, scale), maxScale);
    updateTransform();
}

function resetMapView() {
    scale = 1;
    translateX = 0;
    translateY = 0;
    updateTransform();
    // Also reset layers, filters, and inspector
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

function fitToScreen() {
    const wrapperRect = mapSvgWrapper.getBoundingClientRect();
    const svgRect = denahSvg.getBoundingClientRect();
    // Assuming viewBox is 1200x780
    const scaleX = wrapperRect.width / 1200;
    const scaleY = wrapperRect.height / 780;
    scale = Math.min(scaleX, scaleY) * 0.95; // 95% to leave a small margin
    scale = Math.min(Math.max(minScale, scale), maxScale);
    translateX = 0;
    translateY = 0;
    updateTransform();
}

// Mouse Wheel Zoom
mapSvgWrapper.addEventListener('wheel', (e) => {
    e.preventDefault();
    const delta = e.deltaY > 0 ? -0.1 : 0.1;
    mapZoom(delta);
}, { passive: false });

// Pan Dragging
mapSvgWrapper.addEventListener('mousedown', (e) => {
    isDragging = true;
    startX = e.clientX - translateX;
    startY = e.clientY - translateY;
    mapSvgWrapper.style.cursor = 'grabbing';
});

window.addEventListener('mousemove', (e) => {
    if (!isDragging) return;
    translateX = e.clientX - startX;
    translateY = e.clientY - startY;
    updateTransform();
});

window.addEventListener('mouseup', () => {
    isDragging = false;
    mapSvgWrapper.style.cursor = 'grab';
});

// Touch Support (Pinch Zoom & Pan)
let initialDistance = null;
mapSvgWrapper.addEventListener('touchstart', (e) => {
    if (e.touches.length === 1) {
        isDragging = true;
        startX = e.touches[0].clientX - translateX;
        startY = e.touches[0].clientY - translateY;
    } else if (e.touches.length === 2) {
        initialDistance = Math.hypot(
            e.touches[0].clientX - e.touches[1].clientX,
            e.touches[0].clientY - e.touches[1].clientY
        );
    }
}, { passive: false });

mapSvgWrapper.addEventListener('touchmove', (e) => {
    e.preventDefault();
    if (e.touches.length === 1 && isDragging) {
        translateX = e.touches[0].clientX - startX;
        translateY = e.touches[0].clientY - startY;
        updateTransform();
    } else if (e.touches.length === 2 && initialDistance) {
        const currentDistance = Math.hypot(
            e.touches[0].clientX - e.touches[1].clientX,
            e.touches[0].clientY - e.touches[1].clientY
        );
        const delta = (currentDistance - initialDistance) * 0.005;
        mapZoom(delta);
        initialDistance = currentDistance;
    }
}, { passive: false });

mapSvgWrapper.addEventListener('touchend', () => {
    isDragging = false;
    initialDistance = null;
});

// Initialize & URL Deep-Linking Handler
setTimeout(() => {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const hwId = urlParams.get('hw_id');
        const facId = urlParams.get('fac_id');
        const block = urlParams.get('highlight_block');
        const zone = urlParams.get('zone');

        if (hwId) {
            const idInt = parseInt(hwId);
            if (!isNaN(idInt)) showHardwareDetails(idInt, null);
        } else if (facId) {
            showFacilityDetails(facId, null);
        } else if (block) {
            const b = block.toUpperCase();
            if (b === 'R' || b === 'REEFER') {
                filterZone('reefer');
                showFacilityDetails('f_reefer_racks', null);
            } else if (b === 'RAIL') {
                filterZone('rail');
                showFacilityDetails('f_loading_ramp', null);
            } else if (b === 'GATE') {
                filterZone('gate');
                showFacilityDetails('f_gate_in', null);
            } else if (b === 'CFS') {
                filterZone('cfs_yard');
                showFacilityDetails('f_cfs', null);
            } else {
                filterZone('cfs_yard');
                showFacilityDetails('f_stacking_yard', null);
            }
        } else if (zone) {
            filterZone(zone);
        }
    } catch(err) {
        console.warn('URL deep-linking error:', err);
    }
}, 300);

</script>
