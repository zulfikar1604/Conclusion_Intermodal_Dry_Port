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
        <button onclick="switchGateTab('tab-simulasi')" id="btn-tab-simulasi" class="tab-btn flex-1 min-w-[150px] py-2.5 px-3.5 rounded-lg text-xs sm:text-sm font-bold transition-all text-[#0170b9] bg-blue-50/80 shadow-xs flex items-center justify-center space-x-2">
            <i class="fa-solid fa-microchip"></i>
            <span>Simulasi Alur Gate & Sensor</span>
        </button>
        <button onclick="switchGateTab('tab-ocr-iso')" id="btn-tab-ocr-iso" class="tab-btn flex-1 min-w-[150px] py-2.5 px-3.5 rounded-lg text-xs sm:text-sm font-semibold transition-all text-gray-600 hover:text-gray-900 hover:bg-gray-50 flex items-center justify-center space-x-2">
            <i class="fa-solid fa-camera-retro"></i>
            <span>Interactive OCR & ISO 6346</span>
        </button>
        <button onclick="switchGateTab('tab-katalog')" id="btn-tab-katalog" class="tab-btn flex-1 min-w-[150px] py-2.5 px-3.5 rounded-lg text-xs sm:text-sm font-semibold transition-all text-gray-600 hover:text-gray-900 hover:bg-gray-50 flex items-center justify-center space-x-2">
            <i class="fa-solid fa-list-check"></i>
            <span>Katalog Hardware & BOM (26 Item)</span>
        </button>
        <button onclick="switchGateTab('tab-telemetri')" id="btn-tab-telemetri" class="tab-btn flex-1 min-w-[150px] py-2.5 px-3.5 rounded-lg text-xs sm:text-sm font-semibold transition-all text-gray-600 hover:text-gray-900 hover:bg-gray-50 flex items-center justify-center space-x-2">
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
                        <button onclick="printGatePass()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-bold transition-colors shadow-xs">
                            <i class="fa-solid fa-print mr-1"></i> Cetak Pass
                        </button>
                        <button onclick="openEdifactModal()" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-bold transition-colors flex items-center space-x-1.5 shadow-xs">
                            <i class="fa-solid fa-file-code"></i>
                            <span>Dokumen EDIFACT CODECO</span>
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
    <!-- TAB: INTERACTIVE OCR & ISO 6346 CHECK DIGIT ENGINE -->
    <!-- ======================================================================= -->
    <div id="tab-ocr-iso" class="tab-content hidden space-y-6">
        <!-- Banner Intro -->
        <div class="bg-gradient-to-r from-[#002f5e] via-[#014d80] to-[#0170b9] rounded-2xl p-6 text-white shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="inline-flex items-center space-x-2 px-2.5 py-0.5 rounded-full bg-blue-400/20 border border-blue-300/30 text-xs font-mono text-blue-200">
                    <i class="fa-solid fa-microchip"></i>
                    <span>ISO 6346:1995 / BIC Standard Modulo 11</span>
                </div>
                <h2 class="text-xl font-bold tracking-tight">Portal Optical Character Recognition (OCR) & Check Digit Engine</h2>
                <p class="text-xs text-blue-100 max-w-2xl">
                    Simulasi 4-Camera OCR Gantry (Hikvision iDS-TCV300) yang membaca nomor kontainer, kode ukuran/tipe ISO, serta memvalidasi check digit secara matematis sebelum verifikasi Gate-In dry port.
                </p>
            </div>
            <div class="flex items-center space-x-2 flex-shrink-0">
                <button type="button" onclick="triggerOcrDoorScan()" class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold rounded-xl text-xs transition-all shadow-md flex items-center space-x-2">
                    <i class="fa-solid fa-barcode"></i>
                    <span>Scan OCR Otomatis</span>
                </button>
            </div>
        </div>

        <!-- Presets Selection Bar -->
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-3">
                <i class="fa-solid fa-images mr-1.5 text-blue-600"></i>Pilih Sampel Kontainer Realistis (Dry Port Test Cases):
            </span>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <button type="button" onclick="selectOcrPreset('MSKU')" id="preset-btn-MSKU" class="ocr-preset-btn p-3 rounded-xl border-2 border-blue-500 bg-blue-50/50 text-left transition-all hover:shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-blue-600 block uppercase">Maersk Line • 40HC</span>
                        <span class="font-mono font-bold text-sm text-gray-900">MSKU 982145-8</span>
                        <span class="text-[11px] text-gray-500 block">45G1 • 32.500 kg</span>
                    </div>
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded">Valid</span>
                </button>
                <button type="button" onclick="selectOcrPreset('ONEU')" id="preset-btn-ONEU" class="ocr-preset-btn p-3 rounded-xl border border-gray-200 bg-white text-left transition-all hover:shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-pink-600 block uppercase">Ocean Network Express • 40GP</span>
                        <span class="font-mono font-bold text-sm text-gray-900">ONEU 661298-4</span>
                        <span class="text-[11px] text-gray-500 block">42G1 • 30.480 kg</span>
                    </div>
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded">Valid</span>
                </button>
                <button type="button" onclick="selectOcrPreset('EMCU')" id="preset-btn-EMCU" class="ocr-preset-btn p-3 rounded-xl border border-gray-200 bg-white text-left transition-all hover:shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-emerald-600 block uppercase">Evergreen Marine • 20GP</span>
                        <span class="font-mono font-bold text-sm text-gray-900">EMCU 551820-2</span>
                        <span class="text-[11px] text-gray-500 block">22G1 • 24.000 kg</span>
                    </div>
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded">Valid</span>
                </button>
                <button type="button" onclick="selectOcrPreset('INVALID')" id="preset-btn-INVALID" class="ocr-preset-btn p-3 rounded-xl border border-gray-200 bg-white text-left transition-all hover:shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-rose-600 block uppercase">Test Checksum Gagal</span>
                        <span class="font-mono font-bold text-sm text-rose-700">MSKU 982145-3</span>
                        <span class="text-[11px] text-gray-500 block">Digit Salah (Harusnya 8)</span>
                    </div>
                    <span class="px-2 py-0.5 bg-rose-100 text-rose-700 text-[10px] font-bold rounded">Invalid</span>
                </button>
            </div>
        </div>

        <!-- Main OCR Visualizer & Calculation Engine Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: Simulated Container Door & Camera Detection (5 cols) -->
            <div class="lg:col-span-5 space-y-4">
                <div class="bg-slate-900 rounded-2xl p-5 border border-slate-800 text-white shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3 border-b border-slate-800 pb-3">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-xs font-mono font-bold text-slate-300">CAM-02: REAR DOOR OCR GANTRY</span>
                        </div>
                        <span id="ocr-fps-tag" class="text-[10px] font-mono text-slate-400">1080P @ 30 FPS • HDR ON</span>
                    </div>

                    <!-- Visual Container Door Box Graphic -->
                    <div class="relative w-full aspect-[4/3] bg-gradient-to-b from-slate-800 to-slate-850 rounded-xl border-2 border-slate-700 p-4 flex flex-col justify-between overflow-hidden shadow-inner">
                        <!-- Scanning Line Animation -->
                        <div id="ocr-scanline" class="absolute left-0 right-0 h-1 bg-gradient-to-r from-transparent via-cyan-400 to-transparent shadow-[0_0_12px_#38bdf8] opacity-0 transition-opacity duration-300 pointer-events-none z-20 top-0"></div>

                        <!-- Top Door Hinges & Corrugation Effect -->
                        <div class="flex justify-between items-center text-slate-500 text-[11px] px-2 border-b border-slate-700/60 pb-1">
                            <span><i class="fa-solid fa-lock"></i> LOCK BAR L</span>
                            <span class="text-[10px] font-mono text-slate-400" id="door-seal-tag">SEAL: ML-ID992810</span>
                            <span>LOCK BAR R <i class="fa-solid fa-lock"></i></span>
                        </div>

                        <!-- Container Identification Decal Graphic -->
                        <div class="my-auto text-right space-y-1 pr-3">
                            <!-- Bounding Box Container Number -->
                            <div id="bbox-container" class="inline-block p-1.5 border-2 border-dashed border-emerald-400 bg-emerald-950/40 rounded transition-all">
                                <div class="text-[9px] font-mono text-emerald-300 text-left">OCR CONF: 99.6% [BOX-ID]</div>
                                <div id="door-container-no" class="font-mono text-2xl sm:text-3xl font-black tracking-widest text-white drop-shadow">
                                    MSKU 982145<span class="ml-1 px-1 border border-white/40 bg-white/10 rounded">8</span>
                                </div>
                            </div>

                            <!-- Bounding Box Size Type -->
                            <div class="block">
                                <div id="bbox-sizetype" class="inline-block p-1 border border-dashed border-cyan-400 bg-cyan-950/40 rounded mt-1">
                                    <span class="text-[9px] font-mono text-cyan-300 mr-2">SIZE/TYPE:</span>
                                    <span id="door-sizetype" class="font-mono text-sm font-bold text-cyan-200">45G1</span>
                                </div>
                            </div>

                            <!-- Weight Specifications Stencil -->
                            <div class="pt-2 text-[10px] sm:text-[11px] font-mono text-slate-300 space-y-0.5">
                                <div>MAX. GROSS: <span id="door-max-gross" class="font-bold text-white">32.500 KG / 71.650 LBS</span></div>
                                <div>TARE WT: <span id="door-tare" class="font-bold text-white">3.900 KG / 8.598 LBS</span></div>
                                <div>NET / PAYLOAD: <span id="door-payload" class="font-bold text-white">28.600 KG / 63.052 LBS</span></div>
                                <div>MAX CUBE: <span id="door-cube" class="font-bold text-white">76.4 CU.M / 2.700 CU.FT</span></div>
                            </div>
                        </div>

                        <!-- CSC Safety Approval Plate & Barcode at bottom -->
                        <div class="flex items-center justify-between border-t border-slate-700/60 pt-2 text-slate-400 text-[10px]">
                            <div class="flex items-center space-x-2">
                                <span class="px-1.5 py-0.5 bg-slate-800 border border-slate-600 rounded font-mono text-slate-300">CSC APPROVED</span>
                                <span class="font-mono text-slate-400">BV-IND-2024</span>
                            </div>
                            <div class="font-mono text-slate-400 flex items-center space-x-1">
                                <i class="fa-solid fa-barcode text-sm"></i>
                                <span id="door-bottom-code">MSKU9821458</span>
                            </div>
                        </div>
                    </div>

                    <!-- OCR Engine Status Footer -->
                    <div class="mt-4 grid grid-cols-2 gap-3 text-xs font-mono">
                        <div class="bg-slate-800/80 p-2.5 rounded-lg border border-slate-700/60">
                            <span class="text-slate-400 block text-[10px]">RECOGNITION LATENCY</span>
                            <span id="ocr-latency-val" class="font-bold text-emerald-400 text-sm">184 ms</span>
                            <span class="text-[10px] text-slate-500 block">TensorRT Inference</span>
                        </div>
                        <div class="bg-slate-800/80 p-2.5 rounded-lg border border-slate-700/60">
                            <span class="text-slate-400 block text-[10px]">OVERALL CONFIDENCE</span>
                            <span id="ocr-overall-conf" class="font-bold text-emerald-400 text-sm">99.4%</span>
                            <span class="text-[10px] text-slate-500 block">4/4 Cameras Voted</span>
                        </div>
                    </div>
                </div>

                <!-- Fast Actions -->
                <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm space-y-2">
                    <button type="button" onclick="transferOcrToGateLane()" class="w-full py-2.5 px-4 bg-[#002f5e] hover:bg-[#0170b9] text-white font-bold rounded-xl text-xs transition-colors flex items-center justify-center space-x-2 shadow-xs">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                        <span>Kirim Kontainer ke Inbound Gate Lane 1</span>
                    </button>
                    <button type="button" onclick="openEdifactModalCurrent()" class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl text-xs transition-colors flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-file-invoice"></i>
                        <span>Generate UN/EDIFACT CODECO</span>
                    </button>
                </div>
            </div>

            <!-- Right: ISO 6346 Mathematical Check Digit Engine (7 cols) -->
            <div class="lg:col-span-7 space-y-4">
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-4 mb-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 flex items-center">
                                <i class="fa-solid fa-calculator text-[#0170b9] mr-2"></i>
                                Kalkulator Standar ISO 6346 & Check Digit
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">Validasi matematis algoritma modulus 11 dengan pembobotan eksponensial 2^i</p>
                        </div>
                        <span id="iso-badge-status" class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full font-bold text-xs self-start sm:self-auto flex items-center space-x-1">
                            <i class="fa-solid fa-circle-check"></i>
                            <span id="iso-badge-text">VALID ISO 6346</span>
                        </span>
                    </div>

                    <!-- Interactive Input Field -->
                    <div class="space-y-2 mb-5">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Nomor Kontainer (10 atau 11 Karakter):
                        </label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input type="text" id="iso-input-container" value="MSKU9821458" maxlength="11" oninput="handleContainerInput(this.value)" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 font-mono text-base font-bold uppercase tracking-wider text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none uppercase" placeholder="Contoh: MSKU9821458">
                                <span class="absolute right-3 top-2.5 text-xs font-mono text-gray-400" id="iso-char-count">11/11</span>
                            </div>
                            <button type="button" onclick="selectOcrPreset('MSKU')" class="px-3 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-xl transition-colors">
                                Reset
                            </button>
                        </div>
                    </div>

                    <!-- Breakdown Badges -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 mb-5 text-center">
                        <div class="bg-blue-50/70 border border-blue-100 rounded-xl p-2.5">
                            <span class="text-[10px] font-bold text-blue-600 uppercase block">Owner Prefix</span>
                            <span id="iso-owner-code" class="font-mono font-bold text-sm text-gray-900">MSK</span>
                            <span id="iso-owner-name" class="text-[10px] text-gray-500 block truncate">Maersk Line</span>
                        </div>
                        <div class="bg-indigo-50/70 border border-indigo-100 rounded-xl p-2.5">
                            <span class="text-[10px] font-bold text-indigo-600 uppercase block">Category Identifier</span>
                            <span id="iso-cat-code" class="font-mono font-bold text-sm text-gray-900">U</span>
                            <span class="text-[10px] text-gray-500 block">Freight Container</span>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5">
                            <span class="text-[10px] font-bold text-slate-600 uppercase block">Serial Number</span>
                            <span id="iso-serial-code" class="font-mono font-bold text-sm text-gray-900">982145</span>
                            <span class="text-[10px] text-gray-500 block">6 Digit Unik</span>
                        </div>
                        <div id="iso-check-box" class="bg-emerald-50 border border-emerald-200 rounded-xl p-2.5">
                            <span class="text-[10px] font-bold text-emerald-700 uppercase block">Check Digit</span>
                            <span id="iso-check-digit" class="font-mono font-extrabold text-sm text-emerald-700">8</span>
                            <span id="iso-check-sub" class="text-[10px] text-emerald-600 font-semibold block">Cocok (Rem 8)</span>
                        </div>
                    </div>

                    <!-- Mathematical Step-by-Step Table -->
                    <div class="border border-gray-100 rounded-xl overflow-hidden mb-4">
                        <div class="bg-slate-50 px-3.5 py-2 border-b border-gray-200 flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-700">Tabel Kalkulasi Modulo 11 Langkah Demi Langkah</span>
                            <span class="text-[11px] text-gray-500 font-mono">Rumus: &sum; (Nilai &times; 2<sup>i</sup>) mod 11</span>
                        </div>
                        <div class="overflow-x-auto max-h-64 overflow-y-auto">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-100 text-gray-600 font-semibold uppercase tracking-wider text-[10px] sticky top-0">
                                    <tr>
                                        <th class="py-2 px-3 text-center">Pos (i)</th>
                                        <th class="py-2 px-3 text-center">Karakter</th>
                                        <th class="py-2 px-3 text-center">Nilai Karakter</th>
                                        <th class="py-2 px-3 text-center">Bobot (2<sup>i</sup>)</th>
                                        <th class="py-2 px-3 text-right">Hasil Kali</th>
                                    </tr>
                                </thead>
                                <tbody id="iso-calc-tbody" class="divide-y divide-gray-100 font-mono">
                                    <!-- Populated via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Modulo 11 Summary Result Panel -->
                    <div id="iso-calc-summary" class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="space-y-1 text-center sm:text-left">
                            <div class="text-xs text-gray-600">
                                Total Penjumlahan Bobot (&sum; Produk): <span id="iso-sum-total" class="font-mono font-bold text-gray-900 text-sm">4958</span>
                            </div>
                            <div class="text-xs text-gray-600">
                                Sisa Bagi Modulo 11: <span id="iso-mod-formula" class="font-mono font-semibold text-blue-700">4958 mod 11 = 8</span>
                            </div>
                            <div class="text-[11px] text-gray-500 italic">
                                *Catatan ISO 6346: Jika sisa bagi adalah 10, digit pemeriksa ditetapkan menjadi 0.
                            </div>
                        </div>
                        <div id="iso-verdict-card" class="bg-emerald-500 text-white px-4 py-2.5 rounded-xl text-center shadow-xs flex-shrink-0 min-w-[140px]">
                            <span class="text-[10px] uppercase font-bold block opacity-90">Hasil Verifikasi</span>
                            <span id="iso-verdict-text" class="text-base font-black tracking-wide">VALID PASS</span>
                        </div>
                    </div>
                </div>
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

<!-- ======================================================================= -->
<!-- MODAL: UN/EDIFACT CODECO (Container Gate-In/Out Report) -->
<!-- ======================================================================= -->
<div id="edifactModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 animate-fadeIn">
    <div class="bg-white rounded-2xl max-w-4xl w-full border border-gray-200 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="bg-[#002f5e] text-white px-6 py-4 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-blue-500/20 border border-blue-400/30 flex items-center justify-center text-blue-300 text-lg">
                    <i class="fa-solid fa-file-code"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold tracking-tight">UN/EDIFACT CODECO (D.95B Standard SMDG)</h3>
                    <p class="text-xs text-blue-200">Container Gate-In / Gate-Out Electronic Data Interchange Transmission</p>
                </div>
            </div>
            <button type="button" onclick="closeEdifactModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 overflow-y-auto space-y-4">
            <!-- Metadata Strip -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                    <span class="text-[10px] text-gray-500 uppercase font-bold block">Interchange Sender</span>
                    <span class="text-xs font-mono font-bold text-gray-900">CIDP_YMS</span>
                </div>
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                    <span class="text-[10px] text-gray-500 uppercase font-bold block">Interchange Receiver</span>
                    <span id="edi-receiver-tag" class="text-xs font-mono font-bold text-gray-900">MAERSK_LINE</span>
                </div>
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                    <span class="text-[10px] text-gray-500 uppercase font-bold block">Document Type</span>
                    <span class="text-xs font-mono font-bold text-blue-700">CODECO (Gate-In: BGM+34)</span>
                </div>
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                    <span class="text-[10px] text-gray-500 uppercase font-bold block">Format Standard</span>
                    <span class="text-xs font-mono font-bold text-emerald-700">UN/EDIFACT D.95B</span>
                </div>
            </div>

            <!-- Monospace EDIFACT Message Preview -->
            <div class="relative bg-slate-950 rounded-xl p-4 border border-slate-800 shadow-inner">
                <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-800 text-[11px] font-mono text-slate-400">
                    <span>TRANSMISSION BUFFER: CODECO_PAYLOAD.EDI</span>
                    <span class="text-emerald-400">SYNTAX 2 • SEGMENT TERM (')</span>
                </div>
                <pre id="edifact-raw-text" class="font-mono text-xs text-emerald-300 leading-relaxed overflow-x-auto whitespace-pre selection:bg-blue-600 selection:text-white max-h-64"></pre>
            </div>

            <!-- Segment Legend & Technical Explanation -->
            <div class="bg-blue-50/60 border border-blue-100 rounded-xl p-4 text-xs space-y-2">
                <h4 class="font-bold text-gray-900 flex items-center">
                    <i class="fa-solid fa-circle-info text-blue-600 mr-1.5"></i>
                    Struktur Segmen Standar SMDG CODECO D.95B:
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-[11px] text-gray-600 font-mono">
                    <div><b class="text-blue-800 font-bold">UNA:+.? '</b> — Service String Advice (delimiter format)</div>
                    <div><b class="text-blue-800 font-bold">UNB+UNOA:2</b> — Interchange Header (Sender, Receiver, Jam)</div>
                    <div><b class="text-blue-800 font-bold">BGM+34</b> — 34 = Gate In Confirmation Report</div>
                    <div><b class="text-blue-800 font-bold">TDT+20</b> — Inland Truck Details & Nomor Polisi</div>
                    <div><b class="text-blue-800 font-bold">EQD+CN</b> — Equipment Details (No Kontainer & Tipe ISO)</div>
                    <div><b class="text-blue-800 font-bold">MEA+WT</b> — Measurement (Verified Gross Mass / VGM SOLAS)</div>
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3 flex-shrink-0">
            <span class="text-xs text-gray-500">Kompatibel dengan Navis N4, Pelindo TOS, dan Ocean Carrier Shipping EDI Gateway.</span>
            <div class="flex items-center space-x-2">
                <button type="button" onclick="copyEdifactText()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold rounded-xl text-xs transition-colors flex items-center space-x-1.5">
                    <i class="fa-regular fa-copy"></i>
                    <span id="copy-edi-btn-text">Salin Pesan EDI</span>
                </button>
                <button type="button" onclick="downloadEdifactFile()" class="px-4 py-2 bg-[#0170b9] hover:bg-[#002f5e] text-white font-bold rounded-xl text-xs transition-colors flex items-center space-x-1.5 shadow-sm">
                    <i class="fa-solid fa-download"></i>
                    <span>Unduh File (.edi)</span>
                </button>
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

// ===========================================================================
// OCR ENGINE & ISO 6346 CHECK DIGIT JAVASCRIPT
// ===========================================================================
const ISO_LETTER_VALUES = {
    'A': 10, 'B': 12, 'C': 13, 'D': 14, 'E': 15, 'F': 16, 'G': 17, 'H': 18, 'I': 19, 'J': 20,
    'K': 21, 'L': 23, 'M': 24, 'N': 25, 'O': 26, 'P': 27, 'Q': 28, 'R': 29, 'S': 30, 'T': 31,
    'U': 32, 'V': 34, 'W': 35, 'X': 36, 'Y': 37, 'Z': 38
};

const ocrPresetsData = {
    'MSKU': {
        code: 'MSKU9821450',
        displayMain: 'MSKU 982145',
        checkDigit: '0',
        sizeType: '45G1',
        owner: 'MSK',
        ownerName: 'Maersk Line A/S',
        category: 'U',
        serial: '982145',
        maxGross: '32.500 KG / 71.650 LBS',
        tare: '3.900 KG / 8.598 LBS',
        payload: '28.600 KG / 63.052 LBS',
        cube: '76.4 CU.M / 2.700 CU.FT',
        seal: 'SEAL: ML-ID992810',
        plate: 'B 9812 UIK',
        weight: '32.450 kg',
        receiver: 'MAERSK_LINE',
        yardLoc: 'Block B - Bay 04 - Row 02'
    },
    'ONEU': {
        code: 'ONEU6612988',
        displayMain: 'ONEU 661298',
        checkDigit: '8',
        sizeType: '42G1',
        owner: 'ONE',
        ownerName: 'Ocean Network Express',
        category: 'U',
        serial: '661298',
        maxGross: '30.480 KG / 67.200 LBS',
        tare: '3.820 KG / 8.420 LBS',
        payload: '26.660 KG / 58.780 LBS',
        cube: '67.7 CU.M / 2.390 CU.FT',
        seal: 'SEAL: ONE-JKT88120',
        plate: 'B 9144 PXT',
        weight: '24.120 kg',
        receiver: 'OCEAN_NETWORK_EXPRESS',
        yardLoc: 'Reefer Yard - Block R02 - Plug 14'
    },
    'EMCU': {
        code: 'EMCU5518200',
        displayMain: 'EMCU 551820',
        checkDigit: '0',
        sizeType: '22G1',
        owner: 'EMC',
        ownerName: 'Evergreen Marine Corp',
        category: 'U',
        serial: '551820',
        maxGross: '24.000 KG / 52.910 LBS',
        tare: '2.280 KG / 5.026 LBS',
        payload: '21.720 KG / 47.884 LBS',
        cube: '33.2 CU.M / 1.172 CU.FT',
        seal: 'SEAL: EMC-TW77192',
        plate: 'B 9033 BAA',
        weight: '21.800 kg',
        receiver: 'EVERGREEN_LINE',
        yardLoc: 'Block A - Bay 02 - Row 01'
    },
    'INVALID': {
        code: 'MSKU9821453',
        displayMain: 'MSKU 982145',
        checkDigit: '3',
        sizeType: '45G1',
        owner: 'MSK',
        ownerName: 'Maersk Line A/S',
        category: 'U',
        serial: '982145',
        maxGross: '32.500 KG / 71.650 LBS',
        tare: '3.900 KG / 8.598 LBS',
        payload: '28.600 KG / 63.052 LBS',
        cube: '76.4 CU.M / 2.700 CU.FT',
        seal: 'SEAL: ML-ID992810',
        plate: 'B 9812 UIK',
        weight: '32.450 kg',
        receiver: 'MAERSK_LINE',
        yardLoc: 'Area Behandle / Bea Cukai'
    }
};

let activePresetKey = 'MSKU';

function getIsoCharValue(ch) {
    ch = ch.toUpperCase();
    if (ch >= '0' && ch <= '9') return parseInt(ch, 10);
    return ISO_LETTER_VALUES[ch] !== undefined ? ISO_LETTER_VALUES[ch] : 0;
}

function handleContainerInput(val) {
    const clean = val.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
    document.getElementById('iso-input-container').value = clean;
    document.getElementById('iso-char-count').textContent = clean.length + '/11';
    recalculateISO(clean);
}

function recalculateISO(code) {
    const tbody = document.getElementById('iso-calc-tbody');
    tbody.innerHTML = '';

    const ownerCodeEl = document.getElementById('iso-owner-code');
    const ownerNameEl = document.getElementById('iso-owner-name');
    const catCodeEl = document.getElementById('iso-cat-code');
    const serialCodeEl = document.getElementById('iso-serial-code');
    const checkDigitEl = document.getElementById('iso-check-digit');
    const checkSubEl = document.getElementById('iso-check-sub');
    const checkBoxEl = document.getElementById('iso-check-box');
    const badgeStatus = document.getElementById('iso-badge-status');
    const badgeText = document.getElementById('iso-badge-text');
    const sumTotalEl = document.getElementById('iso-sum-total');
    const modFormulaEl = document.getElementById('iso-mod-formula');
    const verdictCard = document.getElementById('iso-verdict-card');
    const verdictText = document.getElementById('iso-verdict-text');

    if (!code || code.length < 4) {
        ownerCodeEl.textContent = '---';
        ownerNameEl.textContent = 'Menunggu Input';
        catCodeEl.textContent = '-';
        serialCodeEl.textContent = '------';
        checkDigitEl.textContent = '-';
        checkSubEl.textContent = 'Belum Lengkap';
        sumTotalEl.textContent = '0';
        modFormulaEl.textContent = '-';
        return;
    }

    const owner = code.substring(0, 3);
    ownerCodeEl.textContent = owner;
    
    // Lookup owner name
    const ownerDirectory = {
        'MSK': 'Maersk Line A/S',
        'ONE': 'Ocean Network Express',
        'EMC': 'Evergreen Marine',
        'TCK': 'Trans Capital Korea',
        'CMA': 'CMA CGM Group',
        'HLC': 'Hapag-Lloyd AG',
        'COS': 'COSCO Shipping',
        'MSC': 'Mediterranean Shipping Co'
    };
    ownerNameEl.textContent = ownerDirectory[owner] || 'International BIC Owner';

    const cat = code.length >= 4 ? code[3] : '-';
    catCodeEl.textContent = cat;

    const serial = code.length >= 10 ? code.substring(4, 10) : (code.length > 4 ? code.substring(4) : '------');
    serialCodeEl.textContent = serial;

    // Check calculation
    let totalSum = 0;
    const calcLen = Math.min(code.length, 10);

    for (let i = 0; i < calcLen; i++) {
        const char = code[i];
        const val = getIsoCharValue(char);
        const weight = Math.pow(2, i);
        const product = val * weight;
        totalSum += product;

        const tr = document.createElement('tr');
        tr.className = "hover:bg-slate-50 transition-colors";
        tr.innerHTML = `
            <td class="py-1.5 px-3 text-center text-gray-400 font-bold">${i}</td>
            <td class="py-1.5 px-3 text-center font-bold text-gray-900 bg-slate-50/50">${char}</td>
            <td class="py-1.5 px-3 text-center text-blue-600">${val}</td>
            <td class="py-1.5 px-3 text-center text-gray-500">2<sup>${i}</sup> = ${weight}</td>
            <td class="py-1.5 px-3 text-right font-bold text-gray-800">${product.toLocaleString()}</td>
        `;
        tbody.appendChild(tr);
    }

    sumTotalEl.textContent = totalSum.toLocaleString();

    if (code.length >= 10) {
        const rem = totalSum % 11;
        const expectedCheck = (rem === 10) ? 0 : rem;
        modFormulaEl.textContent = `${totalSum.toLocaleString()} mod 11 = ${rem} (Harusnya: ${expectedCheck})`;

        if (code.length >= 11) {
            const actualCheck = parseInt(code[10], 10);
            checkDigitEl.textContent = actualCheck;

            if (actualCheck === expectedCheck) {
                // Match
                badgeStatus.className = "px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full font-bold text-xs self-start sm:self-auto flex items-center space-x-1";
                badgeStatus.innerHTML = '<i class="fa-solid fa-circle-check"></i><span id="iso-badge-text">VALID ISO 6346</span>';
                checkBoxEl.className = "bg-emerald-50 border border-emerald-200 rounded-xl p-2.5";
                checkDigitEl.className = "font-mono font-extrabold text-sm text-emerald-700";
                checkSubEl.className = "text-[10px] text-emerald-600 font-semibold block";
                checkSubEl.textContent = `Cocok (Rem: ${expectedCheck})`;

                verdictCard.className = "bg-emerald-500 text-white px-4 py-2.5 rounded-xl text-center shadow-xs flex-shrink-0 min-w-[140px]";
                verdictText.textContent = "VALID PASS";
            } else {
                // Mismatch
                badgeStatus.className = "px-3 py-1 bg-rose-100 text-rose-800 rounded-full font-bold text-xs self-start sm:self-auto flex items-center space-x-1";
                badgeStatus.innerHTML = '<i class="fa-solid fa-circle-xmark"></i><span id="iso-badge-text">CHECKSUM GAGAL</span>';
                checkBoxEl.className = "bg-rose-50 border border-rose-200 rounded-xl p-2.5";
                checkDigitEl.className = "font-mono font-extrabold text-sm text-rose-700";
                checkSubEl.className = "text-[10px] text-rose-600 font-semibold block";
                checkSubEl.textContent = `Salah! Sisa bagi: ${expectedCheck}`;

                verdictCard.className = "bg-rose-600 text-white px-4 py-2.5 rounded-xl text-center shadow-xs flex-shrink-0 min-w-[140px]";
                verdictText.textContent = "MISMATCH (GAGAL)";
            }
        } else {
            checkDigitEl.textContent = expectedCheck + ' (Estimasi)';
            checkSubEl.textContent = 'Digit ke-11 Belum Diisi';
        }
    } else {
        modFormulaEl.textContent = 'Minimal 10 karakter untuk hitung modulo 11';
    }
}

function selectOcrPreset(key) {
    activePresetKey = key;
    const preset = ocrPresetsData[key];
    if (!preset) return;

    // Update preset buttons styling
    document.querySelectorAll('.ocr-preset-btn').forEach(btn => {
        btn.classList.remove('border-2', 'border-blue-500', 'bg-blue-50/50');
        btn.classList.add('border', 'border-gray-200', 'bg-white');
    });
    const currentBtn = document.getElementById('preset-btn-' + key);
    if (currentBtn) {
        currentBtn.classList.remove('border', 'border-gray-200', 'bg-white');
        currentBtn.classList.add('border-2', 'border-blue-500', 'bg-blue-50/50');
    }

    // Update Door Graphics
    document.getElementById('door-container-no').innerHTML = `${preset.displayMain}<span class="ml-1 px-1 border border-white/40 bg-white/10 rounded">${preset.checkDigit}</span>`;
    document.getElementById('door-sizetype').textContent = preset.sizeType;
    document.getElementById('door-max-gross').textContent = preset.maxGross;
    document.getElementById('door-tare').textContent = preset.tare;
    document.getElementById('door-payload').textContent = preset.payload;
    document.getElementById('door-cube').textContent = preset.cube;
    document.getElementById('door-seal-tag').textContent = preset.seal;
    document.getElementById('door-bottom-code').textContent = preset.code;

    // Trigger OCR Input
    document.getElementById('iso-input-container').value = preset.code;
    document.getElementById('iso-char-count').textContent = preset.code.length + '/11';
    recalculateISO(preset.code);

    triggerOcrDoorScan();
}

function triggerOcrDoorScan() {
    const scanline = document.getElementById('ocr-scanline');
    const confTag = document.getElementById('ocr-overall-conf');
    const latencyTag = document.getElementById('ocr-latency-val');
    const bbox = document.getElementById('bbox-container');

    // Animasi scanline
    scanline.style.opacity = '1';
    scanline.style.top = '0%';
    scanline.style.transition = 'top 0.6s ease-in-out, opacity 0.3s ease';

    setTimeout(() => {
        scanline.style.top = '100%';
    }, 50);

    setTimeout(() => {
        scanline.style.opacity = '0';
        scanline.style.top = '0%';
    }, 700);

    // Randomize telemetry
    const latency = Math.floor(Math.random() * 40) + 165;
    const conf = (99.1 + Math.random() * 0.7).toFixed(1);
    if (latencyTag) latencyTag.textContent = latency + ' ms';
    if (confTag) confTag.textContent = conf + '%';

    // Bbox highlight
    if (bbox) {
        bbox.classList.add('ring-4', 'ring-emerald-400');
        setTimeout(() => bbox.classList.remove('ring-4', 'ring-emerald-400'), 500);
    }
}

function transferOcrToGateLane() {
    const preset = ocrPresetsData[activePresetKey] || ocrPresetsData['MSKU'];
    const currentCode = document.getElementById('iso-input-container').value;

    // Update Skenario 1 dengan data OCR terkini
    scenarios[1].container = currentCode.length === 11 
        ? currentCode.substring(0, 4) + ' ' + currentCode.substring(4, 10) + '-' + currentCode[10] 
        : currentCode;
    scenarios[1].plate = preset.plate;
    scenarios[1].weight = preset.weight;
    scenarios[1].yardLoc = preset.yardLoc;

    // Pindah ke tab simulasi dan jalankan
    switchGateTab('tab-simulasi');
    
    // Pilih skenario 1
    const radio1 = document.querySelector('input[name="sim-scenario"][value="1"]');
    if (radio1) radio1.checked = true;
    selectScenario(1);

    // Otomatis picu simulasi gate inbound
    setTimeout(() => {
        runGateSimulation();
    }, 300);
}

// ===========================================================================
// UN/EDIFACT CODECO ENGINE JAVASCRIPT
// ===========================================================================
let currentEdifactPayload = "";

function generateEdifactCodecoString(preset) {
    const now = new Date();
    const ymd = now.getFullYear().toString() + 
                String(now.getMonth() + 1).padStart(2, '0') + 
                String(now.getDate()).padStart(2, '0');
    const hm = String(now.getHours()).padStart(2, '0') + String(now.getMinutes()).padStart(2, '0');
    const refNum = 'CIDP' + ymd + hm;
    const cleanPlate = (preset.plate || 'B9812UIK').replace(/\s+/g, '');
    const cleanWeight = (preset.weight || '32.450 kg').replace(/[^0-9]/g, '');

    return `UNA:+.? '
UNB+UNOA:2+CIDP_DRY_PORT+${preset.receiver || 'MAERSK_LINE'}+${ymd}:${hm}+${refNum}'
BGM+34+GP-${ymd}-0042+9'
TDT+20++1++TRUCK:CIDP_GATE+${cleanPlate}'
RFF+SI:CIDP-SI-${ymd}'
NAD+CF+${preset.owner || 'MSK'}:172:20'
EQD+CN+${preset.code}+${preset.sizeType}:102:5++2+5'
MEA+WT++KGM:${cleanWeight}'
SEL+${preset.seal.replace('SEAL: ', '')}+CA'
UNT+9+${refNum}'
UNZ+1+${refNum}'`;
}

function openEdifactModal(customPreset) {
    const preset = customPreset || ocrPresetsData[activePresetKey] || ocrPresetsData['MSKU'];
    currentEdifactPayload = generateEdifactCodecoString(preset);

    const rawPre = document.getElementById('edifact-raw-text');
    const recvTag = document.getElementById('edi-receiver-tag');
    if (rawPre) rawPre.textContent = currentEdifactPayload;
    if (recvTag) recvTag.textContent = preset.receiver || 'MAERSK_LINE';

    const modal = document.getElementById('edifactModal');
    if (modal) modal.classList.remove('hidden');
}

function openEdifactModalCurrent() {
    openEdifactModal(ocrPresetsData[activePresetKey]);
}

function closeEdifactModal() {
    const modal = document.getElementById('edifactModal');
    if (modal) modal.classList.add('hidden');
}

function copyEdifactText() {
    if (!currentEdifactPayload) return;
    navigator.clipboard.writeText(currentEdifactPayload).then(() => {
        const btnText = document.getElementById('copy-edi-btn-text');
        if (btnText) {
            btnText.textContent = "Tersalin ke Clipboard!";
            setTimeout(() => {
                btnText.textContent = "Salin Pesan EDI";
            }, 2000);
        }
    }).catch(err => {
        alert("Pesan EDIFACT:\n\n" + currentEdifactPayload);
    });
}

function downloadEdifactFile() {
    if (!currentEdifactPayload) return;
    const preset = ocrPresetsData[activePresetKey] || ocrPresetsData['MSKU'];
    const filename = `CODECO_${preset.code}.edi`;
    const blob = new Blob([currentEdifactPayload], { type: 'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

// Inisialisasi awal saat load
document.addEventListener('DOMContentLoaded', () => {
    recalculateISO('MSKU9821450');
});
</script>
