<?php
// =============================================================================
// MODUL: MANAJEMEN GATE & INFRASTRUKTUR HARDWARE DRY PORT
// File: pages/gate.php
// Proyek: Conclusion Intermodal Dry Port (CIDP) 35 Ha — Yard Management System
// PIC: Armansyah Muchtarrom (Hardware & Infrastructure Specialist)
// =============================================================================

$gate_info = [
    'pic'    => 'Armansyah Muchtarrom',
    'role'   => 'Hardware & Infrastructure Specialist',
    'desc'   => 'Bertanggung jawab atas analisis infrastruktur fisik lapangan, integrasi sensor telemetri alat angkat (spreader/twistlock), jembatan timbang bersertifikasi VGM SOLAS, otomasi gerbang masuk/keluar (ANPR, OCR, Barrier Gate), serta penyusunan blueprint spesifikasi teknis hardware pelabuhan kering 35 Ha.',
    'icon'   => 'fa-door-open',
    'status' => 'Modul Aktif & Terintegrasi Sensor'
];

// Data Hardware 26 Item
$hardware_list = [
    [
        'no' => 1,
        'kategori' => 'Otomasi Gate',
        'tipe' => 'live',
        'nama' => 'Kamera ANPR Gate',
        'model' => 'Hikvision DS-TCG406-E / Seri ANPR',
        'spesifikasi' => 'Pembacaan plat nomor otomatis, capture kendaraan, outdoor IP67, integrasi barrier gate & YMS',
        'harga' => 16000000,
        'harga_label' => 'Rp 16.000.000',
        'unit' => 4,
        'unit_label' => '4 unit (2 In / 2 Out)',
        'subtotal' => 64000000,
        'subtotal_label' => 'Rp 64.000.000',
        'link' => 'https://www.hikvision.com/id/products/parking-management/vehicle-access-control-management/anpr-cameras/ds-tcg406-e/'
    ],
    [
        'no' => 2,
        'kategori' => 'Otomasi Gate',
        'tipe' => 'live',
        'nama' => 'Kamera OCR Kontainer',
        'model' => 'Hikvision iDS-TCV300-A6I / OCR Portal',
        'spesifikasi' => 'Membaca nomor kontainer ISO 6346 (4 huruf + 7 angka), multi-angle recognition, integrasi gate system',
        'harga' => 55000000,
        'harga_label' => 'Rp 55.000.000',
        'unit' => 4,
        'unit_label' => '4 unit (2 Portal In)',
        'subtotal' => 220000000,
        'subtotal_label' => 'Rp 220.000.000',
        'link' => 'https://www.hikvision.com/id/products/ITS-Products/Checkpoint-Systems/Checkpoint-Capture-Units/ids-tcv300-a6i/'
    ],
    [
        'no' => 3,
        'kategori' => 'Keamanan & Bea Cukai',
        'tipe' => 'blueprint',
        'nama' => 'CCTV Yard 4 MP',
        'model' => 'Hikvision DS-2CD2046G2H-IU',
        'spesifikasi' => '4 MP, IP67 weatherproof, IR night vision, PoE, monitoring area penumpukan umum',
        'harga' => 3500000,
        'harga_label' => 'Rp 3.500.000',
        'unit' => 32,
        'unit_label' => '32 unit',
        'subtotal' => 112000000,
        'subtotal_label' => 'Rp 112.000.000',
        'link' => 'https://www.hikvision.com/en/products/IP-Products/Network-Cameras/Pro-Series-EasyIP-/ds-2cd2046g2h-i-u-/'
    ],
    [
        'no' => 4,
        'kategori' => 'Keamanan & Bea Cukai',
        'tipe' => 'blueprint',
        'nama' => 'CCTV Yard 8 MP',
        'model' => 'Hikvision DS-2CD2T87G2H-LI',
        'spesifikasi' => '8 MP / 4K UHD, ColorVu night vision, outdoor IP67, perimeter boundary monitoring',
        'harga' => 6500000,
        'harga_label' => 'Rp 6.500.000',
        'unit' => 16,
        'unit_label' => '16 unit',
        'subtotal' => 104000000,
        'subtotal_label' => 'Rp 104.000.000',
        'link' => 'https://www.hikvision.com/id/products/IP-Products/Network-Cameras/Pro-Series-EasyIP-/ds-2cd2t87g2h-li/?subName=DS-2CD2T87G2H-LI'
    ],
    [
        'no' => 5,
        'kategori' => 'Keamanan & Bea Cukai',
        'tipe' => 'blueprint',
        'nama' => 'CCTV PTZ 360°',
        'model' => 'Hikvision DS-2SE4C425MWG-E/14 TandemVu',
        'spesifikasi' => 'Pan-tilt-zoom 25x optical zoom, dual-channel panoramic + PTZ, pemantauan area luas 360°',
        'harga' => 10300000,
        'harga_label' => 'Rp 10.300.000',
        'unit' => 8,
        'unit_label' => '8 unit',
        'subtotal' => 82400000,
        'subtotal_label' => 'Rp 82.400.000',
        'link' => 'https://www.hikvision.com/id/products/IP-Products/PTZ-Cameras/Pro-Series/ds-2se4c425mwg-e-14-f0-/'
    ],
    [
        'no' => 6,
        'kategori' => 'Otomasi Gate',
        'tipe' => 'live',
        'nama' => 'Weighbridge / VGM Scale',
        'model' => 'Fangda Electronic Truck Scale 80 Ton',
        'spesifikasi' => 'Jembatan timbang kendaraan kapasitas 80 ton, digital load cell indicator, sertifikasi SOLAS VGM, integrasi YMS',
        'harga' => 267000000,
        'harga_label' => 'Rp 267.000.000',
        'unit' => 2,
        'unit_label' => '2 unit (Inbound Lanes)',
        'subtotal' => 534000000,
        'subtotal_label' => 'Rp 534.000.000',
        'link' => 'https://cnfjkeda.en.made-in-china.com/product/aQRUXTBbvLYe/China-60-Ton-80-Ton-Weighbridge-Price-Electronic-Truck-Scale.html'
    ],
    [
        'no' => 7,
        'kategori' => 'Otomasi Gate',
        'tipe' => 'live',
        'nama' => 'RFID Reader Long-Range',
        'model' => 'Long-Range UHF RFID Reader (20m)',
        'spesifikasi' => 'Frekuensi 860-960 MHz, jarak baca hingga 20 meter, identifikasi e-Pass driver & armada truk otomatis',
        'harga' => 3000000,
        'harga_label' => 'Rp 3.000.000',
        'unit' => 4,
        'unit_label' => '4 unit (Gate Lanes)',
        'subtotal' => 12000000,
        'subtotal_label' => 'Rp 12.000.000',
        'link' => 'https://www.orbitadigital.com/en/access-controls/accessories/19689-uhf-reader-20m-access-reader-uhf-tag-access-range-up-to-20-m.html'
    ],
    [
        'no' => 8,
        'kategori' => 'Otomasi Gate',
        'tipe' => 'blueprint',
        'nama' => 'RFID Tag UHF ISO',
        'model' => 'RFID UHF ID Tag ISO (TRANSIT & ATEX)',
        'spesifikasi' => 'Tag pasif UHF anti-metal standar ISO 18000-6C, identitas truk mitra / kartu akses pengemudi',
        'harga' => 690000,
        'harga_label' => 'Rp 690.000 / pack',
        'unit' => 50,
        'unit_label' => '50 pack (500 tags)',
        'subtotal' => 34500000,
        'subtotal_label' => 'Rp 34.500.000',
        'link' => 'https://www.ebay.com/itm/316860017458'
    ],
    [
        'no' => 9,
        'kategori' => 'Otomasi Gate',
        'tipe' => 'blueprint',
        'nama' => 'Automatic Barrier Gate',
        'model' => 'High-Speed Automatic Barrier Gate',
        'spesifikasi' => 'Palang otomatis 3 meter, motor DC brushless (kecepatan buka 1-1.5 detik), sensor loop detector, integrasi ANPR/RFID',
        'harga' => 60000000,
        'harga_label' => 'Rp 60.000.000',
        'unit' => 4,
        'unit_label' => '4 unit (2 In / 2 Out)',
        'subtotal' => 240000000,
        'subtotal_label' => 'Rp 240.000.000',
        'link' => 'https://jutaigateopener.en.made-in-china.com/product/MObfpFLKLorl/China-Automatic-Barrier-Gate-with-Single-Bar-Gate-Arm-Barrier-Automatic-Parking-Gate-Barrier.html'
    ],
    [
        'no' => 10,
        'kategori' => 'Otomasi Gate',
        'tipe' => 'live',
        'nama' => 'LED Information Display',
        'model' => 'Outdoor P6/P8 Industrial LED Display',
        'spesifikasi' => 'Menampilkan nomor gate, instruksi sopir ("SILAHKAN MASUK"), nomor kontainer, dan blok tujuan yard',
        'harga' => 47000000,
        'harga_label' => 'Rp 47.000.000',
        'unit' => 4,
        'unit_label' => '4 unit (Lanes)',
        'subtotal' => 188000000,
        'subtotal_label' => 'Rp 188.000.000',
        'link' => 'https://chipshow.en.made-in-china.com/product/KYarZvkHyFhd/China-Sc-Smart-P6-67-Outdoor-LED-Display-for-Wall-Mounted-Advertising.html'
    ],
    [
        'no' => 11,
        'kategori' => 'Core Datacenter & Jaringan',
        'tipe' => 'blueprint',
        'nama' => 'Industrial Network Switch',
        'model' => 'Managed Industrial PoE+ Gigabit Switch',
        'spesifikasi' => 'Switch industri 16/24-Port PoE+ Gigabit, casing logam IP40, tahan suhu ekstrem lapangan, redundant power input',
        'harga' => 10000000,
        'harga_label' => 'Rp 10.000.000',
        'unit' => 12,
        'unit_label' => '12 unit',
        'subtotal' => 120000000,
        'subtotal_label' => 'Rp 120.000.000',
        'link' => 'https://global.microless.com/product/tp-link-jetstream-48-port-gigabit-l2-managed-switch-with-4-gigabit-sfp-slots-t2600g-52ts-tl-sg3452/'
    ],
    [
        'no' => 12,
        'kategori' => 'Intermodal Rail Siding',
        'tipe' => 'blueprint',
        'nama' => 'Access Point Outdoor',
        'model' => 'Ubiquiti U7 Pro Outdoor / UniFi WiFi 7 AP',
        'spesifikasi' => 'Tri-Band WiFi 7 outdoor, casing IP67 weatherproof, jangkauan luas untuk konektivitas VMT & PDA di yard',
        'harga' => 7200000,
        'harga_label' => 'Rp 7.200.000',
        'unit' => 18,
        'unit_label' => '18 unit (Yard & Rail)',
        'subtotal' => 129600000,
        'subtotal_label' => 'Rp 129.600.000',
        'link' => 'https://www.aloinfousa.com/products/ubiquiti-networks-unifi-u7-pro-outdoor-tri-band-wi-fi-7-access-point'
    ],
    [
        'no' => 13,
        'kategori' => 'Core Datacenter & Jaringan',
        'tipe' => 'blueprint',
        'nama' => 'Server NVR & Database',
        'model' => 'Hikvision DS-9664NI-M16/R 64-Ch NVR & Server',
        'spesifikasi' => 'NVR 64-Channel 4K, 16 SATA bay, redundant PSU, rekaman CCTV yard & gate, integrasi database YMS',
        'harga' => 102000000,
        'harga_label' => 'Rp 102.000.000',
        'unit' => 2,
        'unit_label' => '2 unit (Primary + Backup)',
        'subtotal' => 204000000,
        'subtotal_label' => 'Rp 204.000.000',
        'link' => 'https://www.orbitadigital.com/en/cctv-ip/ip-nvr-recorders/56602-hikvision-solutions-ds-9664ni-m16-r-hikvision-nvr-recorder-64-ch-ip-solutions-maximum.html'
    ],
    [
        'no' => 14,
        'kategori' => 'Core Datacenter & Jaringan',
        'tipe' => 'blueprint',
        'nama' => 'UPS Online Rackmount',
        'model' => 'APC Smart-UPS SRT 5000VA RM 230V (SRT5KRMXLI)',
        'spesifikasi' => 'UPS On-Line double conversion 5kVA/4.5kW, rackmount 3U, backup daya darurat server & gate automation',
        'harga' => 81000000,
        'harga_label' => 'Rp 81.000.000',
        'unit' => 4,
        'unit_label' => '4 unit (Server & Gate)',
        'subtotal' => 324000000,
        'subtotal_label' => 'Rp 324.000.000',
        'link' => 'https://www.monotaro.id/items/s013485894.html'
    ],
    [
        'no' => 15,
        'kategori' => 'Core Datacenter & Jaringan',
        'tipe' => 'blueprint',
        'nama' => 'Barcode / QR Scanner',
        'model' => 'Zebra Symbol LS2208 Handheld Scanner',
        'spesifikasi' => 'Laser barcode/QR scanner 1D/2D, USB, scan dokumen surat jalan, gate pass, e-Ticket booking pos sekuriti',
        'harga' => 1600000,
        'harga_label' => 'Rp 1.600.000',
        'unit' => 8,
        'unit_label' => '8 unit (Loket & Pos)',
        'subtotal' => 12800000,
        'subtotal_label' => 'Rp 12.800.000',
        'link' => 'https://www.monotaro.id/items/s003425886.html'
    ],
    [
        'no' => 16,
        'kategori' => 'Cold Chain Reefer',
        'tipe' => 'blueprint',
        'nama' => 'Thermal Camera Reefer',
        'model' => 'Hikvision DS-2TD2636B-13/P Thermal & Optical',
        'spesifikasi' => 'Pemantauan suhu permukaan reefer container, deteksi dini overheat kompresor & alarm kebakaran cold chain',
        'harga' => 30000000,
        'harga_label' => 'Rp 30.000.000',
        'unit' => 4,
        'unit_label' => '4 unit (Reefer Yard)',
        'subtotal' => 120000000,
        'subtotal_label' => 'Rp 120.000.000',
        'link' => 'https://www.hikvision.com/en/products/Thermal-Products/Thermography-thermal-cameras/temperature-screening-series/ds-2td2636b-13-p/'
    ],
    [
        'no' => 17,
        'kategori' => 'Intermodal Rail Siding',
        'tipe' => 'blueprint',
        'nama' => 'GPS Tracker Truck & Asset',
        'model' => 'Teltonika TAT240 4G LTE Asset Tracker',
        'spesifikasi' => 'Pelacak posisi armada truk real-time, 4G Cat M1/NB-IoT, baterai internal otonom, tracking perjalanan Priok - Cikarang',
        'harga' => 2500000,
        'harga_label' => 'Rp 2.500.000 / unit',
        'unit' => 40,
        'unit_label' => '40 unit',
        'subtotal' => 100000000,
        'subtotal_label' => 'Rp 100.000.000',
        'link' => 'https://www.slapukas.lt/ee/teltonika-tat240'
    ],
    [
        'no' => 18,
        'kategori' => 'Otomasi Gate',
        'tipe' => 'live',
        'nama' => 'Industrial Edge AI PC Gate',
        'model' => 'Advantech ARK-3532 / Neousys Nuvo-9000 Fanless',
        'spesifikasi' => 'Intel Core i7, Fanless Rugged, suhu -20°C s/d 60°C, catu daya 9-48V DC, IP40, Dual GbE PoE, 4x COM RS-232/422/485 untuk Timbangan & PLC Gate',
        'harga' => 42000000,
        'harga_label' => 'Rp 42.000.000',
        'unit' => 4,
        'unit_label' => '4 unit (Gate Lanes)',
        'subtotal' => 168000000,
        'subtotal_label' => 'Rp 168.000.000',
        'link' => 'https://www.advantech.com/en/products/fanless-embedded-box-pcs/ark-3532/'
    ],
    [
        'no' => 19,
        'kategori' => 'Keamanan & Bea Cukai',
        'tipe' => 'blueprint',
        'nama' => 'Gantry Container X-Ray',
        'model' => 'Nuctech MB1215DE / Smiths Detection HCVG',
        'spesifikasi' => 'High-Energy X-Ray (6 MeV), penetrasi baja >300 mm, throughput 20-25 truk/jam, pemindaian peti kemas jalur merah & integrasi CEISA 4.0 Bea Cukai',
        'harga' => 12500000000,
        'harga_label' => 'Rp 12.500.000.000',
        'unit' => 1,
        'unit_label' => '1 sistem gantry',
        'subtotal' => 12500000000,
        'subtotal_label' => 'Rp 12.500.000.000',
        'link' => 'https://www.nuctech.com/en/product/high-energy-container-and-vehicle-inspection-system/'
    ],
    [
        'no' => 20,
        'kategori' => 'Yard & Alat Angkat',
        'tipe' => 'blueprint',
        'nama' => 'Vehicle Mounted Terminal (VMT)',
        'model' => 'Zebra VC8300 Rugged Vehicle Mounted Terminal',
        'spesifikasi' => 'Android Enterprise, layar sentuh 8 inci ultra-bright, keyboard fisik, tahan getaran MIL-STD-810G & IP66 untuk kabin Reach Stacker / RTG',
        'harga' => 74000000,
        'harga_label' => 'Rp 74.000.000',
        'unit' => 6,
        'unit_label' => '6 unit (Alat Berat)',
        'subtotal' => 444000000,
        'subtotal_label' => 'Rp 444.000.000',
        'link' => 'https://www.zebra.com/ap/en/products/mobile-computers/vehicle-mounted/vc8300/vc8300.html'
    ],
    [
        'no' => 21,
        'kategori' => 'Yard & Alat Angkat',
        'tipe' => 'live',
        'nama' => 'DGPS / RTK GNSS Receiver',
        'model' => 'CHCNAV CGI-610 / Hemisphere GNSS',
        'spesifikasi' => 'Dual-antenna RTK GNSS + IMU sensor, akurasi centimeter (<2 cm), update rate 50Hz, RS-232/CAN Bus, IP67 rugged untuk Reach Stacker & RTG auto Bay-Row-Tier',
        'harga' => 45000000,
        'harga_label' => 'Rp 45.000.000',
        'unit' => 6,
        'unit_label' => '6 unit (Alat Berat)',
        'subtotal' => 270000000,
        'subtotal_label' => 'Rp 270.000.000',
        'link' => 'https://chcnav.com/product-detail/cgi-610-gnss-ins-sensor'
    ],
    [
        'no' => 22,
        'kategori' => 'Yard & Alat Angkat',
        'tipe' => 'live',
        'nama' => 'Spreader Twistlock & Load Cell',
        'model' => 'Bromma / Kalmar SmartSpreader Sensor Kit',
        'spesifikasi' => 'Inductive proximity sensors (twistlock engaged/unlocked), 4x load cell telemetry (gross weight), RS-485/CANopen, tahan getaran maritim untuk Reach Stacker',
        'harga' => 35000000,
        'harga_label' => 'Rp 35.000.000',
        'unit' => 6,
        'unit_label' => '6 unit (Spreader)',
        'subtotal' => 210000000,
        'subtotal_label' => 'Rp 210.000.000',
        'link' => 'https://www.bromma.com/products/sensors-telematics/'
    ],
    [
        'no' => 23,
        'kategori' => 'Cold Chain Reefer',
        'tipe' => 'live',
        'nama' => 'Smart Reefer Power Socket',
        'model' => 'Marechal / Cavotec 380V/32A Smart Receptacle',
        'spesifikasi' => '3P+N+E 32A 380V IP67, built-in Modbus RTU / LoRaWAN energy meter & continuous temperature logging, trip detection untuk 300 titik reefer yard',
        'harga' => 8500000,
        'harga_label' => 'Rp 8.500.000 / titik',
        'unit' => 300,
        'unit_label' => '300 titik colokan',
        'subtotal' => 2550000000,
        'subtotal_label' => 'Rp 2.550.000.000',
        'link' => 'https://marechal.com/en/products/decontractor/smart-reefer-sockets'
    ],
    [
        'no' => 24,
        'kategori' => 'Yard & Alat Angkat',
        'tipe' => 'blueprint',
        'nama' => 'Rugged Mobile PDA',
        'model' => 'Zebra TC57x / Honeywell CT60',
        'spesifikasi' => 'Android Enterprise, 2D Long-Range Barcode Imager, IP68 tahan banting/air, 4G LTE & WiFi 6, hot-swappable battery untuk tallyman & inspeksi kontainer (EIR)',
        'harga' => 16000000,
        'harga_label' => 'Rp 16.000.000',
        'unit' => 10,
        'unit_label' => '10 unit (Tallyman)',
        'subtotal' => 160000000,
        'subtotal_label' => 'Rp 160.000.000',
        'link' => 'https://www.zebra.com/id/id/products/mobile-computers/handheld/tc57.html'
    ],
    [
        'no' => 25,
        'kategori' => 'Intermodal Rail Siding',
        'tipe' => 'live',
        'nama' => 'Rail Trackside Axle Counter',
        'model' => 'Frauscher RSR123 Wheel Sensor',
        'spesifikasi' => 'Inductive rail head sensor, deteksi arah gerak KA, hitung as roda (axle count), estimasi kecepatan, IP68, SIL 4 untuk Intermodal Rail Siding KA Logistik',
        'harga' => 85000000,
        'harga_label' => 'Rp 85.000.000',
        'unit' => 4,
        'unit_label' => '4 sensor (2 Jalur KA)',
        'subtotal' => 340000000,
        'subtotal_label' => 'Rp 340.000.000',
        'link' => 'https://www.frauscher.com/en/products/wheel-sensors/frauscher-wheel-sensor-rsr123'
    ],
    [
        'no' => 26,
        'kategori' => 'Keamanan & Bea Cukai',
        'tipe' => 'live',
        'nama' => 'Electronic Cargo Smart Seal',
        'model' => 'Jointech JT701 GPS Smart E-Seal',
        'spesifikasi' => 'GPS/GSM/RFID Smart Electronic Lock, sensor kawat anti-tamper, masa pakai baterai 30 hari, IP67 untuk kontainer transit Bea Cukai Priok - Cikarang (CEISA 4.0)',
        'harga' => 3200000,
        'harga_label' => 'Rp 3.200.000 / unit',
        'unit' => 100,
        'unit_label' => '100 unit e-Seal',
        'subtotal' => 320000000,
        'subtotal_label' => 'Rp 320.000.000',
        'link' => 'https://www.jointech.com/product/jt701-smart-gps-electronic-seal-tracker.html'
    ]
];

// Perhitungan Ringkasan CAPEX
$total_capex = 0;
$total_unit = 0;
$live_count = 0;
$blueprint_count = 0;

foreach ($hardware_list as $item) {
    $total_capex += $item['subtotal'];
    $total_unit += $item['unit'];
    if ($item['tipe'] === 'live') {
        $live_count++;
    } else {
        $blueprint_count++;
    }
}
?>

<div class="space-y-6 animate-fadeIn pb-12">
    <!-- Header Modul & Info PIC -->
    <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-start space-x-4">
            <div class="w-14 h-14 bg-gradient-to-br from-[#002f5e] to-[#0170b9] text-white rounded-2xl flex items-center justify-center text-2xl shadow-md flex-shrink-0">
                <i class="fa-solid fa-door-open"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Manajemen Gate & Otomasi Infrastruktur</h1>
                    <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-semibold flex items-center">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>Sensor Live
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 max-w-3xl leading-relaxed">
                    <?= $gate_info['desc'] ?>
                </p>
            </div>
        </div>

        <!-- Kartu PIC -->
        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5 flex items-center space-x-3 flex-shrink-0">
            <div class="w-10 h-10 rounded-full bg-[#002f5e] text-white flex items-center justify-center font-bold text-sm shadow-xs">
                <i class="fa-solid fa-hard-hat"></i>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider">Penanggung Jawab:</span>
                <p class="font-bold text-gray-900 text-xs sm:text-sm"><?= $gate_info['pic'] ?></p>
                <span class="text-[11px] text-[#0170b9] font-semibold block"><?= $gate_info['role'] ?></span>
            </div>
        </div>
    </div>

    <!-- KPI Ringkasan Gate & Hardware -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 sm:p-5 border border-gray-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500">Inbound Gate Lanes</span>
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-[#0170b9] flex items-center justify-center text-xs">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </span>
            </div>
            <div class="mt-2.5 flex items-baseline space-x-2">
                <span class="text-xl sm:text-2xl font-bold text-gray-900">2 / 2 Lane</span>
                <span class="text-xs text-emerald-600 font-semibold flex items-center">
                    <i class="fa-solid fa-circle-check mr-1"></i> Aktif
                </span>
            </div>
            <p class="text-[11px] text-gray-400 mt-1">Lane 1 (OCR+VGM) & Lane 2 (e-Seal Fast)</p>
        </div>

        <div class="bg-white rounded-xl p-4 sm:p-5 border border-gray-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500">Avg Transaction Time</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-stopwatch"></i>
                </span>
            </div>
            <div class="mt-2.5 flex items-baseline space-x-2">
                <span class="text-xl sm:text-2xl font-bold text-gray-900">28 Detik</span>
                <span class="text-xs text-emerald-600 font-semibold">-37% vs Manual</span>
            </div>
            <p class="text-[11px] text-gray-400 mt-1">Target SOLAS VGM: &lt; 45 Detik / Truk</p>
        </div>

        <div class="bg-white rounded-xl p-4 sm:p-5 border border-gray-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500">Katalog Hardware</span>
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-server"></i>
                </span>
            </div>
            <div class="mt-2.5 flex items-baseline space-x-2">
                <span class="text-xl sm:text-2xl font-bold text-gray-900">26 Perangkat</span>
                <span class="text-xs text-indigo-600 font-semibold"><?= $live_count ?> Live / <?= $blueprint_count ?> Fisik</span>
            </div>
            <p class="text-[11px] text-gray-400 mt-1">Total 1.099 Unit Terpasang di 35 Ha</p>
        </div>

        <div class="bg-white rounded-xl p-4 sm:p-5 border border-gray-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500">Total CAPEX Hardware</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-coins"></i>
                </span>
            </div>
            <div class="mt-2.5 flex items-baseline space-x-2">
                <span class="text-xl sm:text-2xl font-bold text-gray-900">Rp 18,94 M</span>
                <span class="text-xs text-slate-500 font-semibold">6 Klaster</span>
            </div>
            <p class="text-[11px] text-gray-400 mt-1">Investasi Fasilitas Pelabuhan Kering</p>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white rounded-xl p-1.5 border border-gray-100 shadow-xs flex flex-wrap gap-1">
        <button onclick="switchGateTab('tab-simulasi')" id="btn-tab-simulasi" class="tab-btn flex-1 min-w-[160px] py-2.5 px-4 rounded-lg text-xs sm:text-sm font-bold transition-all text-[#0170b9] bg-blue-50/80 shadow-xs flex items-center justify-center space-x-2">
            <i class="fa-solid fa-microchip"></i>
            <span>Simulasi Alur Gate & Sensor</span>
        </button>
        <button onclick="switchGateTab('tab-katalog')" id="btn-tab-katalog" class="tab-btn flex-1 min-w-[160px] py-2.5 px-4 rounded-lg text-xs sm:text-sm font-semibold transition-all text-gray-600 hover:text-gray-900 hover:bg-gray-50 flex items-center justify-center space-x-2">
            <i class="fa-solid fa-list-check"></i>
            <span>Katalog Hardware & BOM (26 Item)</span>
        </button>
        <button onclick="switchGateTab('tab-telemetri')" id="btn-tab-telemetri" class="tab-btn flex-1 min-w-[160px] py-2.5 px-4 rounded-lg text-xs sm:text-sm font-semibold transition-all text-gray-600 hover:text-gray-900 hover:bg-gray-50 flex items-center justify-center space-x-2">
            <i class="fa-solid fa-tower-broadcast"></i>
            <span>Telemetri IoT Lapangan</span>
        </button>
    </div>

    <!-- ======================================================================= -->
    <!-- TAB 1: SIMULASI ALUR GATE & SENSOR INTERAKTIF -->
    <!-- ======================================================================= -->
    <div id="tab-simulasi" class="tab-content space-y-6">
        <!-- Visual Alur 5 Tahap Gate-In -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
            <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center">
                <i class="fa-solid fa-diagram-project text-[#0170b9] mr-2"></i>
                Alur Otomasi Gerbang Masuk (Inbound Lane 1)
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5 relative overflow-hidden">
                    <span class="text-[10px] font-extrabold text-blue-600 bg-blue-100/70 px-2 py-0.5 rounded-md inline-block mb-2">TAHAP 1</span>
                    <h3 class="text-xs font-bold text-gray-900">Kamera ANPR</h3>
                    <p class="text-[11px] text-gray-500 mt-1">Capture plat nomor truk saat melintasi induction loop</p>
                    <div class="mt-3 text-[11px] font-mono text-slate-700 bg-white p-1.5 rounded border border-slate-200">
                        <i class="fa-solid fa-camera text-blue-500 mr-1"></i> DS-TCG406-E
                    </div>
                </div>

                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5 relative overflow-hidden">
                    <span class="text-[10px] font-extrabold text-indigo-600 bg-indigo-100/70 px-2 py-0.5 rounded-md inline-block mb-2">TAHAP 2</span>
                    <h3 class="text-xs font-bold text-gray-900">OCR Kontainer</h3>
                    <p class="text-[11px] text-gray-500 mt-1">Ekstraksi nomor ISO 6346 (4 huruf + 7 angka) & tipe box</p>
                    <div class="mt-3 text-[11px] font-mono text-slate-700 bg-white p-1.5 rounded border border-slate-200">
                        <i class="fa-solid fa-expand text-indigo-500 mr-1"></i> iDS-TCV300-A6I
                    </div>
                </div>

                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5 relative overflow-hidden">
                    <span class="text-[10px] font-extrabold text-emerald-600 bg-emerald-100/70 px-2 py-0.5 rounded-md inline-block mb-2">TAHAP 3</span>
                    <h3 class="text-xs font-bold text-gray-900">Weighbridge 80t</h3>
                    <p class="text-[11px] text-gray-500 mt-1">Penimbangan bobot riil & verifikasi batas VGM SOLAS</p>
                    <div class="mt-3 text-[11px] font-mono text-slate-700 bg-white p-1.5 rounded border border-slate-200">
                        <i class="fa-solid fa-scale-balanced text-emerald-500 mr-1"></i> Fangda 80 Ton
                    </div>
                </div>

                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5 relative overflow-hidden">
                    <span class="text-[10px] font-extrabold text-purple-600 bg-purple-100/70 px-2 py-0.5 rounded-md inline-block mb-2">TAHAP 4</span>
                    <h3 class="text-xs font-bold text-gray-900">Edge AI PC</h3>
                    <p class="text-[11px] text-gray-500 mt-1">Validasi surat jalan, manifest EDI & booking tiket</p>
                    <div class="mt-3 text-[11px] font-mono text-slate-700 bg-white p-1.5 rounded border border-slate-200">
                        <i class="fa-solid fa-microchip text-purple-500 mr-1"></i> ARK-3532 Fanless
                    </div>
                </div>

                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5 relative overflow-hidden">
                    <span class="text-[10px] font-extrabold text-amber-600 bg-amber-100/70 px-2 py-0.5 rounded-md inline-block mb-2">TAHAP 5</span>
                    <h3 class="text-xs font-bold text-gray-900">LED & Palang</h3>
                    <p class="text-[11px] text-gray-500 mt-1">Display arahan blok yard & barrier gate terbuka otomatis</p>
                    <div class="mt-3 text-[11px] font-mono text-slate-700 bg-white p-1.5 rounded border border-slate-200">
                        <i class="fa-solid fa-traffic-light text-amber-500 mr-1"></i> Palang Terbuka
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Interaktif Simulator Gate In -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Kontrol Simulasi -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center">
                            <i class="fa-solid fa-sliders text-[#0170b9] mr-2"></i>
                            Pilih Skenario Truk Gate-In
                        </h3>
                        <span class="text-[11px] text-gray-400">Simulation Mode</span>
                    </div>

                    <p class="text-xs text-gray-500 mb-4">
                        Pilih armada truk kontainer untuk menguji respons pembacaan kamera ANPR, OCR kontainer, timbangan VGM, dan pemicu barrier gate:
                    </p>

                    <div class="space-y-2.5 mb-6">
                        <label class="block p-3 border border-gray-200 rounded-xl hover:border-blue-300 transition-colors cursor-pointer bg-blue-50/40" onclick="selectScenario(1)">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-gray-900">Truk 1: Lolos Normal (40ft High Cube)</span>
                                <input type="radio" name="scenario" value="1" checked class="text-[#0170b9] focus:ring-blue-500">
                            </div>
                            <div class="text-[11px] text-gray-500 mt-1 flex flex-wrap gap-x-3">
                                <span><i class="fa-solid fa-truck text-gray-400"></i> B 9812 UIK</span>
                                <span><i class="fa-solid fa-box text-gray-400"></i> TCKU 829104-2</span>
                                <span><i class="fa-solid fa-weight-scale text-gray-400"></i> 32.450 kg</span>
                            </div>
                        </label>

                        <label class="block p-3 border border-gray-200 rounded-xl hover:border-blue-300 transition-colors cursor-pointer" onclick="selectScenario(2)">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-gray-900">Truk 2: Reefer Cold Chain (20ft)</span>
                                <input type="radio" name="scenario" value="2" class="text-[#0170b9] focus:ring-blue-500">
                            </div>
                            <div class="text-[11px] text-gray-500 mt-1 flex flex-wrap gap-x-3">
                                <span><i class="fa-solid fa-truck text-gray-400"></i> B 9144 PXT</span>
                                <span><i class="fa-solid fa-snowflake text-blue-400"></i> MSKU 441029-7</span>
                                <span><i class="fa-solid fa-weight-scale text-gray-400"></i> 24.120 kg</span>
                            </div>
                        </label>

                        <label class="block p-3 border border-gray-200 rounded-xl hover:border-amber-300 transition-colors cursor-pointer" onclick="selectScenario(3)">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-gray-900">Truk 3: Overweight Alert (>34.000 kg)</span>
                                <input type="radio" name="scenario" value="3" class="text-[#0170b9] focus:ring-blue-500">
                            </div>
                            <div class="text-[11px] text-gray-500 mt-1 flex flex-wrap gap-x-3">
                                <span><i class="fa-solid fa-truck text-gray-400"></i> B 9033 BAA</span>
                                <span><i class="fa-solid fa-box text-gray-400"></i> CMAU 992183-5</span>
                                <span class="text-rose-600 font-bold"><i class="fa-solid fa-triangle-exclamation text-rose-500"></i> 37.200 kg</span>
                            </div>
                        </label>
                    </div>
                </div>

                <button id="btn-run-sim" onclick="runGateSimulation()" class="w-full py-3 bg-[#0170b9] hover:bg-[#004b87] text-white font-bold text-xs sm:text-sm rounded-xl transition-all shadow-md flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-play"></i>
                    <span>Jalankan Simulasi Sensor Gate In</span>
                </button>
            </div>

            <!-- Tampilan Hasil Sensor Live -->
            <div class="lg:col-span-2 bg-slate-900 rounded-2xl p-6 text-white shadow-md flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 opacity-5 pointer-events-none text-9xl">
                    <i class="fa-solid fa-microchip"></i>
                </div>

                <div>
                    <div class="flex items-center justify-between border-b border-slate-800 pb-4 mb-5">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                            <span class="text-xs font-mono font-bold tracking-wider text-emerald-400 uppercase">Live Lane 1 Sensor Feed</span>
                        </div>
                        <span id="sim-status-badge" class="px-2.5 py-0.5 bg-slate-800 text-slate-300 rounded-full text-[11px] font-mono">
                            STANDBY
                        </span>
                    </div>

                    <!-- Sensor Live Feed Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                        <!-- ANPR Box -->
                        <div class="bg-slate-800/80 rounded-xl p-4 border border-slate-700/60">
                            <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                                <span><i class="fa-solid fa-camera text-blue-400 mr-1.5"></i> ANPR Camera Feed</span>
                                <span id="anpr-status" class="text-[10px] text-slate-400">READY</span>
                            </div>
                            <div class="mt-2 text-center py-2 bg-slate-950 rounded-lg border border-slate-800">
                                <span class="text-[10px] text-slate-500 uppercase block">License Plate</span>
                                <span id="anpr-plate" class="text-lg font-mono font-bold tracking-widest text-amber-400">-- ---- ---</span>
                            </div>
                        </div>

                        <!-- OCR Kontainer Box -->
                        <div class="bg-slate-800/80 rounded-xl p-4 border border-slate-700/60">
                            <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                                <span><i class="fa-solid fa-expand text-indigo-400 mr-1.5"></i> Container OCR Portal</span>
                                <span id="ocr-status" class="text-[10px] text-slate-400">READY</span>
                            </div>
                            <div class="mt-2 text-center py-2 bg-slate-950 rounded-lg border border-slate-800">
                                <span class="text-[10px] text-slate-500 uppercase block">ISO 6346 Container ID</span>
                                <span id="ocr-container" class="text-lg font-mono font-bold tracking-wider text-cyan-400">---- ------- -</span>
                            </div>
                        </div>

                        <!-- Weighbridge Box -->
                        <div class="bg-slate-800/80 rounded-xl p-4 border border-slate-700/60">
                            <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                                <span><i class="fa-solid fa-scale-balanced text-emerald-400 mr-1.5"></i> Weighbridge 80t</span>
                                <span id="vgm-status" class="text-[10px] text-slate-400">READY</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between px-3 py-2 bg-slate-950 rounded-lg border border-slate-800">
                                <div>
                                    <span class="text-[10px] text-slate-500 uppercase block">Gross Weight</span>
                                    <span id="vgm-weight" class="text-base font-mono font-bold text-white">0 kg</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-slate-500 uppercase block">VGM SOLAS</span>
                                    <span id="vgm-solas" class="text-xs font-mono font-bold text-slate-400">--</span>
                                </div>
                            </div>
                        </div>

                        <!-- Barrier Gate & LED Box -->
                        <div class="bg-slate-800/80 rounded-xl p-4 border border-slate-700/60">
                            <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                                <span><i class="fa-solid fa-traffic-light text-amber-400 mr-1.5"></i> Barrier & LED Gate</span>
                                <span id="barrier-status" class="text-[10px] text-rose-400 font-bold">CLOSED</span>
                            </div>
                            <div class="mt-2 py-2 px-3 bg-slate-950 rounded-lg border border-slate-800 text-center">
                                <span class="text-[10px] text-slate-500 uppercase block">LED Instruction Display</span>
                                <span id="led-text" class="text-xs font-mono font-bold text-emerald-400 animate-pulse">MENUNGGU KENDARAAN</span>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar Simulasi -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between text-[11px] text-slate-400 mb-1">
                            <span id="progress-step-text">Status: Siap menjalankan simulasi sensor...</span>
                            <span id="progress-percent" class="font-mono">0%</span>
                        </div>
                        <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                            <div id="sim-progress-bar" class="h-full bg-gradient-to-r from-blue-500 to-emerald-400 w-0 transition-all duration-300"></div>
                        </div>
                    </div>
                </div>

                <!-- Digital Gate Pass Preview (Muncul saat selesai) -->
                <div id="gate-pass-preview" class="hidden bg-emerald-950/60 border border-emerald-500/40 rounded-xl p-3.5 mt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-3 animate-fadeIn">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-lg bg-emerald-500 text-slate-950 flex items-center justify-center font-bold text-base">
                            <i class="fa-solid fa-qrcode"></i>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-emerald-300 block">E-Gate Pass Diterbitkan:</span>
                            <span id="gate-pass-no" class="font-mono font-bold text-xs text-white">GP-20260922-0041</span>
                            <span id="gate-pass-loc" class="text-[11px] text-emerald-200 block">Tujuan: Yard Block B - Bay 04</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="printGatePass()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-bold transition-colors">
                            <i class="fa-solid fa-print mr-1"></i> Cetak Pass
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Log Aktivitas Gate Terkini -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-900 flex items-center">
                    <i class="fa-solid fa-clock-rotate-left text-[#0170b9] mr-2"></i>
                    Log Transaksi Gerbang Terkini (Real-time Gate Activity)
                </h3>
                <span class="text-xs text-gray-400">Diperbarui otomatis via Edge AI</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-gray-500 uppercase tracking-wider font-semibold border-b border-gray-200">
                            <th class="py-3 px-3.5">Waktu</th>
                            <th class="py-3 px-3.5">Lane</th>
                            <th class="py-3 px-3.5">Plat Nomor (ANPR)</th>
                            <th class="py-3 px-3.5">No Kontainer (OCR)</th>
                            <th class="py-3 px-3.5">Berat VGM</th>
                            <th class="py-3 px-3.5">VGM SOLAS</th>
                            <th class="py-3 px-3.5">Tujuan Yard</th>
                            <th class="py-3 px-3.5">Status Gate</th>
                        </tr>
                    </thead>
                    <tbody id="gate-log-tbody" class="divide-y divide-gray-100 text-gray-700">
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-3.5 font-mono text-gray-500">23:38:12</td>
                            <td class="py-3 px-3.5"><span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[11px] font-bold">Lane 1 (In)</span></td>
                            <td class="py-3 px-3.5 font-mono font-bold text-gray-900">B 9381 UIX</td>
                            <td class="py-3 px-3.5 font-mono text-gray-900">TEMU 671209-1</td>
                            <td class="py-3 px-3.5 font-mono">29.100 kg</td>
                            <td class="py-3 px-3.5"><span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded font-bold text-[10px]">VERIFIED</span></td>
                            <td class="py-3 px-3.5 font-semibold text-gray-700">Block A - Bay 02</td>
                            <td class="py-3 px-3.5"><span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full text-[10px] font-bold">GATE-IN COMPLETE</span></td>
                        </tr>
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-3.5 font-mono text-gray-500">23:32:45</td>
                            <td class="py-3 px-3.5"><span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded text-[11px] font-bold">Lane 2 (e-Seal)</span></td>
                            <td class="py-3 px-3.5 font-mono font-bold text-gray-900">B 9044 BZX</td>
                            <td class="py-3 px-3.5 font-mono text-gray-900">SUDU 519280-4</td>
                            <td class="py-3 px-3.5 font-mono">22.400 kg</td>
                            <td class="py-3 px-3.5"><span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded font-bold text-[10px]">VERIFIED</span></td>
                            <td class="py-3 px-3.5 font-semibold text-gray-700">Block C - Bay 06</td>
                            <td class="py-3 px-3.5"><span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full text-[10px] font-bold">GATE-IN COMPLETE</span></td>
                        </tr>
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-3.5 font-mono text-gray-500">23:25:19</td>
                            <td class="py-3 px-3.5"><span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-[11px] font-bold">Lane 3 (Out)</span></td>
                            <td class="py-3 px-3.5 font-mono font-bold text-gray-900">B 9912 KLA</td>
                            <td class="py-3 px-3.5 font-mono text-gray-900">MSKU 918237-4</td>
                            <td class="py-3 px-3.5 font-mono">25.400 kg</td>
                            <td class="py-3 px-3.5"><span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded font-bold text-[10px]">EXIT PASS</span></td>
                            <td class="py-3 px-3.5 font-semibold text-gray-700">Exit ke Tol Japek</td>
                            <td class="py-3 px-3.5"><span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-[10px] font-bold">GATE-OUT COMPLETE</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- TAB 2: KATALOG HARDWARE & BILL OF MATERIALS (BOM 26 ITEM) -->
    <!-- ======================================================================= -->
    <div id="tab-katalog" class="tab-content hidden space-y-6">
        <!-- Ringkasan CAPEX & Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-[#002f5e] to-[#0170b9] text-white rounded-2xl p-5 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-200">Total Nilai Investasi (CAPEX)</span>
                <p class="text-2xl sm:text-3xl font-bold mt-1">Rp <?= number_format($total_capex, 0, ',', '.') ?></p>
                <p class="text-xs text-blue-100 mt-2">Mencakup 26 jenis hardware untuk lahan operasional 35 Ha</p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Klasifikasi Perangkat</span>
                <div class="mt-2 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-emerald-700 font-bold flex items-center">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2"></span> Live Software / Telemetri
                        </span>
                        <span class="font-bold text-gray-900"><?= $live_count ?> Item (Disimulasikan)</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-indigo-700 font-bold flex items-center">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 mr-2"></span> Blueprint & Fasilitas Fisik
                        </span>
                        <span class="font-bold text-gray-900"><?= $blueprint_count ?> Item (Infrastruktur)</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Dokumen Acuan Resmi</span>
                    <p class="text-xs text-gray-700 font-semibold mt-1">File Word & Markdown Tersedia di Direktori:</p>
                    <p class="text-[11px] font-mono text-gray-500 bg-gray-50 p-1.5 rounded mt-1 border border-gray-200">hardware/KATALOG_HARDWARE_CIDP.md</p>
                </div>
                <div class="mt-3 flex items-center space-x-2">
                    <a href="hardware/KATALOG_HARDWARE_CIDP.md" target="_blank" class="text-xs text-[#0170b9] hover:underline font-bold flex items-center">
                        <i class="fa-solid fa-file-lines mr-1"></i> Buka Katalog MD
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex flex-wrap gap-1.5" id="category-filter-buttons">
                    <button onclick="filterCategory('all')" class="filter-cat-btn px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-[#002f5e] text-white" data-cat="all">Semua (26)</button>
                    <button onclick="filterCategory('Otomasi Gate')" class="filter-cat-btn px-3 py-1.5 rounded-lg text-xs font-semibold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200" data-cat="Otomasi Gate">Gate (8)</button>
                    <button onclick="filterCategory('Yard & Alat Angkat')" class="filter-cat-btn px-3 py-1.5 rounded-lg text-xs font-semibold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200" data-cat="Yard & Alat Angkat">Yard & Crane (4)</button>
                    <button onclick="filterCategory('Cold Chain Reefer')" class="filter-cat-btn px-3 py-1.5 rounded-lg text-xs font-semibold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200" data-cat="Cold Chain Reefer">Cold Chain (2)</button>
                    <button onclick="filterCategory('Intermodal Rail Siding')" class="filter-cat-btn px-3 py-1.5 rounded-lg text-xs font-semibold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200" data-cat="Intermodal Rail Siding">Rail Siding (3)</button>
                    <button onclick="filterCategory('Keamanan & Bea Cukai')" class="filter-cat-btn px-3 py-1.5 rounded-lg text-xs font-semibold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200" data-cat="Keamanan & Bea Cukai">Keamanan & Bea Cukai (5)</button>
                    <button onclick="filterCategory('Core Datacenter & Jaringan')" class="filter-cat-btn px-3 py-1.5 rounded-lg text-xs font-semibold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200" data-cat="Core Datacenter & Jaringan">Core Datacenter (4)</button>
                </div>

                <div class="relative min-w-[220px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" id="hardware-search" onkeyup="searchHardwareTable()" placeholder="Cari hardware, model, spesifikasi..." class="w-full pl-8 pr-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-blue-500 focus:bg-white transition-all">
                </div>
            </div>
        </div>

        <!-- Tabel 26 Hardware -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs" id="hardware-table">
                    <thead>
                        <tr class="bg-slate-50 text-gray-500 uppercase tracking-wider font-semibold border-b border-gray-200">
                            <th class="py-3.5 px-3 text-center w-12">No.</th>
                            <th class="py-3.5 px-3.5">Hardware</th>
                            <th class="py-3.5 px-3.5">Klaster / Kategori</th>
                            <th class="py-3.5 px-3.5">Tipe Integrasi</th>
                            <th class="py-3.5 px-3.5">Contoh / Model</th>
                            <th class="py-3.5 px-3.5">Spesifikasi Utama</th>
                            <th class="py-3.5 px-3.5 text-right">Harga / Unit</th>
                            <th class="py-3.5 px-3.5 text-center">Unit</th>
                            <th class="py-3.5 px-3.5 text-right">Subtotal CAPEX</th>
                            <th class="py-3.5 px-3.5 text-center">Tautan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <?php foreach ($hardware_list as $hw): ?>
                        <tr class="hardware-row hover:bg-slate-50/70 transition-colors" data-category="<?= htmlspecialchars($hw['kategori']) ?>">
                            <td class="py-3 px-3 text-center font-bold text-gray-400"><?= $hw['no'] ?></td>
                            <td class="py-3 px-3.5 font-bold text-gray-900"><?= htmlspecialchars($hw['nama']) ?></td>
                            <td class="py-3 px-3.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700">
                                    <?= htmlspecialchars($hw['kategori']) ?>
                                </span>
                            </td>
                            <td class="py-3 px-3.5">
                                <?php if ($hw['tipe'] === 'live'): ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center w-fit">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Live Software
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center w-fit">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mr-1.5"></span> Blueprint Fisik
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-3.5 font-medium text-slate-800"><?= htmlspecialchars($hw['model']) ?></td>
                            <td class="py-3 px-3.5 text-gray-500 max-w-xs text-[11px] leading-relaxed"><?= htmlspecialchars($hw['spesifikasi']) ?></td>
                            <td class="py-3 px-3.5 text-right font-mono font-semibold text-gray-800"><?= $hw['harga_label'] ?></td>
                            <td class="py-3 px-3.5 text-center font-mono text-gray-600"><?= $hw['unit_label'] ?></td>
                            <td class="py-3 px-3.5 text-right font-mono font-bold text-[#002f5e]"><?= $hw['subtotal_label'] ?></td>
                            <td class="py-3 px-3.5 text-center">
                                <a href="<?= htmlspecialchars($hw['link']) ?>" target="_blank" rel="noopener noreferrer" class="p-1.5 text-[#0170b9] hover:text-[#004b87] rounded hover:bg-blue-50 transition-colors inline-block" title="Buka tautan spesifikasi resmi">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- TAB 3: TELEMETRI SENSOR IOT LAPANGAN -->
    <!-- ======================================================================= -->
    <div id="tab-telemetri" class="tab-content hidden space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Telemetri Alat Angkat (Reach Stacker) -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center">
                        <i class="fa-solid fa-truck-ramp-box text-blue-600 mr-2"></i>
                        Reach Stacker 01 (Kalmar DRG450)
                    </h3>
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-bold border border-emerald-200">
                        ONLINE - OPERATIONAL
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">DGPS / RTK Position</span>
                        <p class="text-xs font-mono font-bold text-gray-900 mt-0.5">-6.284210, 107.142890</p>
                        <span class="text-[10px] text-emerald-600 font-semibold block mt-1"><i class="fa-solid fa-satellite mr-1"></i> RTK FIXED (&lt; 1.4 cm)</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Current Yard Slot</span>
                        <p class="text-xs font-mono font-bold text-[#0170b9] mt-0.5">Block B - Bay 04 - Row 02</p>
                        <span class="text-[10px] text-gray-500 block mt-1">Tier 3 (Stacking Height)</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Spreader Twistlock</span>
                        <p class="text-xs font-bold text-emerald-700 mt-0.5 flex items-center">
                            <i class="fa-solid fa-lock text-emerald-600 mr-1.5"></i> LOCKED (Loaded)
                        </p>
                        <span class="text-[10px] text-gray-500 block mt-1">Bromma SmartSpreader Kit</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Load Cell Telemetry</span>
                        <p class="text-xs font-mono font-bold text-gray-900 mt-0.5">28.520 kg (28.5 t)</p>
                        <span class="text-[10px] text-emerald-600 font-semibold block mt-1"><i class="fa-solid fa-check mr-1"></i> Beban Normal Aman</span>
                    </div>
                </div>
            </div>

            <!-- Telemetri Cold Chain (Smart Reefer Monitoring) -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center">
                        <i class="fa-solid fa-snowflake text-cyan-600 mr-2"></i>
                        Reefer Yard Monitoring (300 Titik Colokan)
                    </h3>
                    <span class="px-2 py-0.5 bg-blue-50 text-[#0170b9] rounded-full text-[10px] font-bold border border-blue-200">
                        142 / 300 PLUGGED
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Total Konsumsi Daya</span>
                        <p class="text-xs font-mono font-bold text-gray-900 mt-0.5">852 kW</p>
                        <span class="text-[10px] text-gray-500 block mt-1">Rata-rata 6.0 kW / Reefer</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Suhu Rata-rata Cold Chain</span>
                        <p class="text-xs font-mono font-bold text-cyan-700 mt-0.5">-18.4 °C</p>
                        <span class="text-[10px] text-emerald-600 font-semibold block mt-1"><i class="fa-solid fa-check mr-1"></i> Rentang Suhu Beku Stabil</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Status Trip Alarm Listrik</span>
                        <p class="text-xs font-bold text-emerald-700 mt-0.5 flex items-center">
                            <i class="fa-solid fa-shield-check mr-1.5"></i> 0 Peringatan
                        </p>
                        <span class="text-[10px] text-gray-500 block mt-1">Semua socket beroperasi normal</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Kamera Thermal Inframerah</span>
                        <p class="text-xs font-bold text-gray-900 mt-0.5">4 Unit Aktif</p>
                        <span class="text-[10px] text-emerald-600 font-semibold block mt-1"><i class="fa-solid fa-temperature-arrow-down mr-1"></i> Tidak Ada Overheat</span>
                    </div>
                </div>
            </div>

            <!-- Telemetri Intermodal Rail Siding (Axle Counter) -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center">
                        <i class="fa-solid fa-train-subway text-indigo-600 mr-2"></i>
                        Intermodal Rail Siding (Frauscher RSR123)
                    </h3>
                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded-full text-[10px] font-bold border border-indigo-200">
                        TRAIN DETECTED
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Identitas Kereta Api</span>
                        <p class="text-xs font-bold text-gray-900 mt-0.5">KA 2501 (KAI Logistik)</p>
                        <span class="text-[10px] text-gray-500 block mt-1">Rute: Tj. Priok &rarr; CIDP Siding</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Hitungan Gandar (Axle Count)</span>
                        <p class="text-xs font-mono font-bold text-indigo-700 mt-0.5">120 Axles</p>
                        <span class="text-[10px] text-gray-500 block mt-1">30 Gerbong Datar (60 TEUs)</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Kecepatan Pendekatan</span>
                        <p class="text-xs font-mono font-bold text-emerald-700 mt-0.5">12 km/h (Safe)</p>
                        <span class="text-[10px] text-emerald-600 font-semibold block mt-1">Dalam batas aman siding</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Status Jalur Siding</span>
                        <p class="text-xs font-bold text-emerald-700 mt-0.5 flex items-center">
                            <i class="fa-solid fa-circle-check mr-1.5"></i> Jalur 1 Siap Bongkar
                        </p>
                        <span class="text-[10px] text-gray-500 block mt-1">Reach Stacker siap melayani</span>
                    </div>
                </div>
            </div>

            <!-- Telemetri E-Seal Bea Cukai (Jointech JT701) -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center">
                        <i class="fa-solid fa-lock text-amber-600 mr-2"></i>
                        Bea Cukai CEISA 4.0 (Smart E-Seal Tracker)
                    </h3>
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-bold border border-emerald-200">
                        ALL SEALS SECURED
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Kontainer Transit Aktif</span>
                        <p class="text-xs font-mono font-bold text-gray-900 mt-0.5">18 Unit</p>
                        <span class="text-[10px] text-gray-500 block mt-1">Koridor KA Priok - Cikarang</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Integritas Kawat Segel</span>
                        <p class="text-xs font-bold text-emerald-700 mt-0.5 flex items-center">
                            <i class="fa-solid fa-shield-check mr-1.5"></i> 0 Tamper Alert
                        </p>
                        <span class="text-[10px] text-emerald-600 font-semibold block mt-1">Semua segel utuh terkunci</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Kondisi Baterai Rata-rata</span>
                        <p class="text-xs font-mono font-bold text-gray-900 mt-0.5">94%</p>
                        <span class="text-[10px] text-gray-500 block mt-1">Masa pakai hingga 30 hari</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">Sinkronisasi CEISA 4.0</span>
                        <p class="text-xs font-bold text-blue-700 mt-0.5 flex items-center">
                            <i class="fa-solid fa-cloud-arrow-up mr-1.5"></i> Terhubung Real-Time
                        </p>
                        <span class="text-[10px] text-gray-500 block mt-1">PIC: Naufal Andika Heditya</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript Interaktif Modul Gate -->
<script>
// Tab Switching Logic
function switchGateTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('text-[#0170b9]', 'bg-blue-50/80', 'shadow-xs', 'font-bold');
        btn.classList.add('text-gray-600', 'font-semibold');
    });

    const activeTab = document.getElementById(tabId);
    if (activeTab) activeTab.classList.remove('hidden');

    const activeBtn = document.getElementById('btn-' + tabId);
    if (activeBtn) {
        activeBtn.classList.add('text-[#0170b9]', 'bg-blue-50/80', 'shadow-xs', 'font-bold');
        activeBtn.classList.remove('text-gray-600', 'font-semibold');
    }
}

// Simulasi Gate Skenario Data
const scenarios = {
    1: {
        plate: "B 9812 UIK",
        container: "TCKU 829104-2",
        weight: "32.450 kg",
        vgm: "VERIFIED PASS",
        solasClass: "text-emerald-400",
        led: "SILAHKAN MASUK - BLOK B04",
        gatePass: "GP-20260922-0042",
        yardLoc: "Block B - Bay 04 - Row 02 (Dry 40HC)",
        isPass: true
    },
    2: {
        plate: "B 9144 PXT",
        container: "MSKU 441029-7",
        weight: "24.120 kg",
        vgm: "VERIFIED PASS",
        solasClass: "text-emerald-400",
        led: "SILAHKAN KE REEFER YARD R02",
        gatePass: "GP-20260922-0043",
        yardLoc: "Reefer Yard - Block R02 - Plug 14 (Reefer 20ft)",
        isPass: true
    },
    3: {
        plate: "B 9033 BAA",
        container: "CMAU 992183-5",
        weight: "37.200 kg",
        vgm: "OVERWEIGHT ALERT (>34t)",
        solasClass: "text-rose-400 font-bold",
        led: "OVERLOAD! LAPOR KE OPERATOR GATE",
        gatePass: "REJECT-OVERWEIGHT",
        yardLoc: "Area Pemeriksaan / Behandle Bea Cukai",
        isPass: false
    }
};

let currentScenarioId = 1;

function selectScenario(id) {
    currentScenarioId = id;
}

function runGateSimulation() {
    const btn = document.getElementById('btn-run-sim');
    const badge = document.getElementById('sim-status-badge');
    const progressBar = document.getElementById('sim-progress-bar');
    const stepText = document.getElementById('progress-step-text');
    const percentText = document.getElementById('progress-percent');
    
    const anprPlate = document.getElementById('anpr-plate');
    const anprStatus = document.getElementById('anpr-status');
    const ocrContainer = document.getElementById('ocr-container');
    const ocrStatus = document.getElementById('ocr-status');
    const vgmWeight = document.getElementById('vgm-weight');
    const vgmSolas = document.getElementById('vgm-solas');
    const vgmStatus = document.getElementById('vgm-status');
    const barrierStatus = document.getElementById('barrier-status');
    const ledText = document.getElementById('led-text');
    const gatePassPreview = document.getElementById('gate-pass-preview');

    const sc = scenarios[currentScenarioId];

    // Reset State
    btn.disabled = true;
    btn.classList.add('opacity-50', 'cursor-not-allowed');
    gatePassPreview.classList.add('hidden');
    badge.textContent = "PROCESSING SENSORS...";
    badge.className = "px-2.5 py-0.5 bg-amber-900/60 text-amber-300 rounded-full text-[11px] font-mono animate-pulse";
    
    barrierStatus.textContent = "CLOSED";
    barrierStatus.className = "text-[10px] text-rose-400 font-bold";

    // Step 1: ANPR Trigger
    stepText.textContent = "Tahap 1/5: Truk melintasi loop detector, kamera ANPR mengambil gambar plat...";
    percentText.textContent = "20%";
    progressBar.style.width = "20%";
    anprStatus.textContent = "SCANNING...";
    anprStatus.className = "text-[10px] text-amber-400";

    setTimeout(() => {
        anprPlate.textContent = sc.plate;
        anprStatus.textContent = "MATCHED";
        anprStatus.className = "text-[10px] text-emerald-400 font-bold";

        // Step 2: OCR Trigger
        stepText.textContent = "Tahap 2/5: Kamera OCR mengekstraksi nomor kontainer ISO 6346...";
        percentText.textContent = "40%";
        progressBar.style.width = "40%";
        ocrStatus.textContent = "RECOGNIZING...";
        ocrStatus.className = "text-[10px] text-amber-400";

        setTimeout(() => {
            ocrContainer.textContent = sc.container;
            ocrStatus.textContent = "CONFIRMED";
            ocrStatus.className = "text-[10px] text-emerald-400 font-bold";

            // Step 3: Weighbridge Trigger
            stepText.textContent = "Tahap 3/5: Jembatan timbang 80t membaca bobot & validasi SOLAS VGM...";
            percentText.textContent = "60%";
            progressBar.style.width = "60%";
            vgmStatus.textContent = "WEIGHING...";
            vgmStatus.className = "text-[10px] text-amber-400";

            setTimeout(() => {
                vgmWeight.textContent = sc.weight;
                vgmSolas.textContent = sc.vgm;
                vgmSolas.className = "text-xs font-mono " + sc.solasClass;
                vgmStatus.textContent = sc.isPass ? "VERIFIED" : "ALERT";
                vgmStatus.className = sc.isPass ? "text-[10px] text-emerald-400 font-bold" : "text-[10px] text-rose-400 font-bold";

                // Step 4: Edge AI PC Decision
                stepText.textContent = "Tahap 4/5: Industrial Edge AI PC memvalidasi surat jalan dan manifest EDI...";
                percentText.textContent = "80%";
                progressBar.style.width = "80%";

                setTimeout(() => {
                    // Step 5: Barrier & LED
                    percentText.textContent = "100%";
                    progressBar.style.width = "100%";
                    ledText.textContent = sc.led;

                    if (sc.isPass) {
                        barrierStatus.textContent = "OPEN (PALANG TERBUKA)";
                        barrierStatus.className = "text-[10px] text-emerald-400 font-bold animate-bounce";
                        badge.textContent = "SUCCESS - ACCESS GRANTED";
                        badge.className = "px-2.5 py-0.5 bg-emerald-900/80 text-emerald-300 rounded-full text-[11px] font-mono";
                        stepText.textContent = "Selesai: Palang terbuka otomatis. Silakan masuk ke area yard.";

                        // Tampilkan Gate Pass
                        document.getElementById('gate-pass-no').textContent = sc.gatePass;
                        document.getElementById('gate-pass-loc').textContent = "Tujuan: " + sc.yardLoc;
                        gatePassPreview.classList.remove('hidden');

                        // Tambah log ke tabel
                        prependGateLog(sc);
                    } else {
                        barrierStatus.textContent = "LOCKED (PALANG TETAP TERTUTUP)";
                        barrierStatus.className = "text-[10px] text-rose-500 font-bold";
                        badge.textContent = "REJECTED - OVERWEIGHT";
                        badge.className = "px-2.5 py-0.5 bg-rose-900/80 text-rose-300 rounded-full text-[11px] font-mono";
                        stepText.textContent = "Peringatan: Berat kontainer melebihi ambang batas SOLAS. Palang ditahan tertutup!";
                    }

                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                }, 700);
            }, 700);
        }, 700);
    }, 700);
}

function prependGateLog(sc) {
    const tbody = document.getElementById('gate-log-tbody');
    const now = new Date();
    const timeStr = String(now.getHours()).padStart(2, '0') + ':' + 
                    String(now.getMinutes()).padStart(2, '0') + ':' + 
                    String(now.getSeconds()).padStart(2, '0');

    const newRow = document.createElement('tr');
    newRow.className = "hover:bg-slate-50/60 transition-colors bg-blue-50/30 animate-fadeIn";
    newRow.innerHTML = `
        <td class="py-3 px-3.5 font-mono text-gray-500">${timeStr}</td>
        <td class="py-3 px-3.5"><span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[11px] font-bold">Lane 1 (In)</span></td>
        <td class="py-3 px-3.5 font-mono font-bold text-gray-900">${sc.plate}</td>
        <td class="py-3 px-3.5 font-mono text-gray-900">${sc.container}</td>
        <td class="py-3 px-3.5 font-mono">${sc.weight}</td>
        <td class="py-3 px-3.5"><span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded font-bold text-[10px]">VERIFIED</span></td>
        <td class="py-3 px-3.5 font-semibold text-gray-700">${sc.yardLoc.split('(')[0]}</td>
        <td class="py-3 px-3.5"><span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full text-[10px] font-bold">SIMULATION PASS</span></td>
    `;
    tbody.insertBefore(newRow, tbody.firstChild);
}

function printGatePass() {
    alert("Mencetak E-Gate Pass resmi CIDP...\nNomor Pass: " + document.getElementById('gate-pass-no').textContent + "\n" + document.getElementById('gate-pass-loc').textContent);
}

// Filter Kategori Hardware BOM
function filterCategory(category) {
    const buttons = document.querySelectorAll('.filter-cat-btn');
    buttons.forEach(btn => {
        if (btn.getAttribute('data-cat') === category) {
            btn.classList.add('bg-[#002f5e]', 'text-white');
            btn.classList.remove('bg-gray-100', 'text-gray-600');
        } else {
            btn.classList.remove('bg-[#002f5e]', 'text-white');
            btn.classList.add('bg-gray-100', 'text-gray-600');
        }
    });

    const rows = document.querySelectorAll('.hardware-row');
    rows.forEach(row => {
        const rowCat = row.getAttribute('data-category');
        if (category === 'all' || rowCat === category) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Quick Search Hardware Table
function searchHardwareTable() {
    const input = document.getElementById('hardware-search').value.toLowerCase();
    const rows = document.querySelectorAll('.hardware-row');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(input)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
