# KATALOG PERALATAN & HARDWARE REKOMENDASI INTERMODAL DRY PORT
## Proyek Conclusion Intermodal Dry Port (CIDP) 35 Ha — Yard Management System (YMS)
**Penanggung Jawab:** Armansyah Muchtarrom (*Hardware & Infrastructure Specialist*)  
**Status Dokumen:** Versi 2.0 (Revisi, Penyelarasan, & Integrasi Sistem Web)  
**Tanggal Pembaruan:** September 2026  

---

## 1. Ringkasan Eksekutif & Arsitektur Lapangan

Pengembangan **Conclusion Intermodal Dry Port (CIDP)** dengan luas 35 Hektar (mengadopsi skala operasional Cikarang Dry Port) membutuhkan kesiapan infrastruktur fisik, otomasi gerbang, telemetri alat angkat, jaringan komunikasi industri, dan integrasi kepabeanan yang andal.

Dokumen ini merupakan acuan resmi spesifikasi teknis hardware, bill of materials (BOM), dan rencana anggaran belanja modal (*Capital Expenditure* / CAPEX). Perangkat dibagi menjadi dua klasifikasi operasional:
1. **Perangkat Aktif (Software-Integrated & Live Telemetry):** Perangkat dengan antarmuka data langsung (API / Webhook / RS-232 / Modbus / MQTT) yang terintegrasi ke aplikasi web YMS.
2. **Perangkat Blueprint & Utilitas Fisik (Supporting Infrastructure):** Perangkat pendukung kelistrikan, jaringan, fisik, dan pengawasan pasif lapangan.

---

## 2. Klasifikasi Klaster Operasional Hardware

Seluruh 26 perangkat dikelompokkan ke dalam 6 klaster fungsional:
- **Klaster 1: Otomasi Gate & Kontrol Akses (8 Item):** Kamera ANPR, Kamera OCR Kontainer, Jembatan Timbang VGM 80t, RFID Reader, RFID Tag, Barrier Gate, LED Display, Industrial Edge AI PC.
- **Klaster 2: Telemetri Lapangan Penumpukan & Alat Angkat (4 Item):** Vehicle Mounted Terminal (VMT), DGPS / RTK GNSS Receiver, Spreader Twistlock & Load Cell Sensor Kit, Rugged Mobile PDA.
- **Klaster 3: Cold Chain & Monitoring Reefer (2 Item):** Thermal Camera Reefer, Smart Reefer Power Monitoring Socket.
- **Klaster 4: Infrastruktur Intermodal Rel KA (3 Item):** Rail Trackside Axle Counter, GPS Tracker Truck/Wagon, Outdoor Wireless Access Point.
- **Klaster 5: Keamanan Terminal & Pemeriksaan Bea Cukai (5 Item):** CCTV Yard 4 MP, CCTV Yard 8 MP, CCTV PTZ 360°, Gantry Container X-Ray Scanner, Electronic Cargo Smart Seal (E-Seal).
- **Klaster 6: Core Datacenter, Edge Computing & Jaringan (4 Item):** Server NVR & Database, UPS Online 5000VA, Industrial Network Switch PoE+, Barcode/QR Scanner Meja.

---

## 3. Tabel Lengkap Spesifikasi Hardware & Bill of Materials (BOM)

| No | Kategori | Tipe Integrasi | Hardware | Contoh / Model | Spesifikasi Utama | Harga / Unit | Estimasi Unit (35 Ha) | Subtotal CAPEX | Link Referensi |
|:---|:---|:---|:---|:---|:---|:---|:---:|:---|:---|
| 1 | Otomasi Gate | **Live Software** | Kamera ANPR Gate | Hikvision DS-TCG406-E / Seri ANPR | Pembacaan plat nomor otomatis, capture kendaraan, outdoor IP67, integrasi barrier gate & YMS | Rp 16.000.000 | 4 unit (2 In / 2 Out) | Rp 64.000.000 | [Hikvision ANPR](https://www.hikvision.com/id/products/parking-management/vehicle-access-control-management/anpr-cameras/ds-tcg406-e/) |
| 2 | Otomasi Gate | **Live Software** | Kamera OCR Kontainer | Hikvision iDS-TCV300-A6I / OCR Portal | Membaca nomor kontainer ISO 6346 (4 huruf + 7 angka), multi-angle recognition, integrasi gate system | Rp 55.000.000 | 4 unit (2 Portal In) | Rp 220.000.000 | [Hikvision OCR](https://www.hikvision.com/id/products/ITS-Products/Checkpoint-Systems/Checkpoint-Capture-Units/ids-tcv300-a6i/) |
| 3 | Keamanan | *Blueprint Fisik* | CCTV Yard 4 MP | Hikvision DS-2CD2046G2H-IU | 4 MP, IP67 weatherproof, IR night vision, PoE, monitoring area penumpukan umum | Rp 3.500.000 | 32 unit | Rp 112.000.000 | [Hikvision 4MP](https://www.hikvision.com/en/products/IP-Products/Network-Cameras/Pro-Series-EasyIP-/ds-2cd2046g2h-i-u-/) |
| 4 | Keamanan | *Blueprint Fisik* | CCTV Yard 8 MP | Hikvision DS-2CD2T87G2H-LI | 8 MP / 4K UHD, ColorVu night vision, outdoor IP67, perimeter boundary monitoring | Rp 6.500.000 | 16 unit | Rp 104.000.000 | [Hikvision 8MP](https://www.hikvision.com/id/products/IP-Products/Network-Cameras/Pro-Series-EasyIP-/ds-2cd2t87g2h-li/?subName=DS-2CD2T87G2H-LI) |
| 5 | Keamanan | *Blueprint Fisik* | CCTV PTZ 360° | Hikvision DS-2SE4C425MWG-E/14 TandemVu | Pan-tilt-zoom 25x optical zoom, dual-channel panoramic + PTZ, pemantauan area luas 360° | Rp 10.300.000 | 8 unit | Rp 82.400.000 | [Hikvision PTZ](https://www.hikvision.com/id/products/IP-Products/PTZ-Cameras/Pro-Series/ds-2se4c425mwg-e-14-f0-/) |
| 6 | Otomasi Gate | **Live Software** | Weighbridge / VGM Scale | Fangda Electronic Truck Scale 80 Ton | Jembatan timbang kendaraan kapasitas 80 ton, digital load cell indicator, sertifikasi SOLAS VGM, integrasi YMS | Rp 267.000.000 | 2 unit (Inbound Lanes) | Rp 534.000.000 | [Fangda Scale](https://cnfjkeda.en.made-in-china.com/product/aQRUXTBbvLYe/China-60-Ton-80-Ton-Weighbridge-Price-Electronic-Truck-Scale.html) |
| 7 | Otomasi Gate | **Live Software** | RFID Reader Long-Range | Long-Range UHF RFID Reader (20m) | Frekuensi 860-960 MHz, jarak baca hingga 20 meter, identifikasi e-Pass driver & armada truk otomatis | Rp 3.000.000 | 4 unit (Gate Lanes) | Rp 12.000.000 | [Orbita UHF Reader](https://www.orbitadigital.com/en/access-controls/accessories/19689-uhf-reader-20m-access-reader-uhf-tag-access-range-up-to-20-m.html) |
| 8 | Otomasi Gate | *Blueprint Fisik* | RFID Tag UHF ISO | RFID UHF ID Tag ISO (TRANSIT & ATEX) | Tag pasif UHF anti-metal standar ISO 18000-6C, identitas truk mitra / kartu akses pengemudi | Rp 690.000 | 50 pack (500 tags) | Rp 34.500.000 | [eBay RFID Tag](https://www.ebay.com/itm/316860017458) |
| 9 | Otomasi Gate | *Blueprint Fisik* | Automatic Barrier Gate | High-Speed Automatic Barrier Gate | Palang otomatis 3 meter, motor DC brushless (kecepatan buka 1-1.5 detik), sensor loop detector, integrasi ANPR/RFID | Rp 60.000.000 | 4 unit (2 In / 2 Out) | Rp 240.000.000 | [Juta Barrier Gate](https://jutaigateopener.en.made-in-china.com/product/MObfpFLKLorl/China-Automatic-Barrier-Gate-with-Single-Bar-Gate-Arm-Barrier-Automatic-Parking-Gate-Barrier.html) |
| 10 | Otomasi Gate | **Live Software** | LED Information Display | Outdoor P6/P8 Industrial LED Display | Menampilkan nomor gate, instruksi sopir ("SILAHKAN MASUK"), nomor kontainer, dan blok tujuan yard | Rp 47.000.000 | 4 unit (Lanes) | Rp 188.000.000 | [Chipshow LED](https://chipshow.en.made-in-china.com/product/KYarZvkHyFhd/China-Sc-Smart-P6-67-Outdoor-LED-Display-for-Wall-Mounted-Advertising.html) |
| 11 | Core IT | *Blueprint Fisik* | Industrial Network Switch | Managed Industrial PoE+ Gigabit Switch | Switch industri 16/24-Port PoE+ Gigabit, casing logam IP40, tahan suhu ekstrem lapangan, redundant power input | Rp 10.000.000 | 12 unit | Rp 120.000.000 | [TP-Link Jetstream](https://global.microless.com/product/tp-link-jetstream-48-port-gigabit-l2-managed-switch-with-4-gigabit-sfp-slots-t2600g-52ts-tl-sg3452/) |
| 12 | Rail Siding | *Blueprint Fisik* | Access Point Outdoor | Ubiquiti U7 Pro Outdoor / UniFi WiFi 7 AP | Tri-Band WiFi 7 outdoor, casing IP67 weatherproof, jangkauan luas untuk konektivitas VMT & PDA di yard | Rp 7.200.000 | 18 unit (Yard & Rail) | Rp 129.600.000 | [Ubiquiti U7 Pro](https://www.aloinfousa.com/products/ubiquiti-networks-unifi-u7-pro-outdoor-tri-band-wi-fi-7-access-point) |
| 13 | Core IT | *Blueprint Fisik* | Server NVR & Database | Hikvision DS-9664NI-M16/R 64-Ch NVR & Server | NVR 64-Channel 4K, 16 SATA bay, redundant PSU, rekaman CCTV yard & gate, integrasi database YMS | Rp 102.000.000 | 2 unit (Primary + Backup) | Rp 204.000.000 | [Hikvision NVR](https://www.orbitadigital.com/en/cctv-ip/ip-nvr-recorders/56602-hikvision-solutions-ds-9664ni-m16-r-hikvision-nvr-recorder-64-ch-ip-solutions-maximum.html) |
| 14 | Core IT | *Blueprint Fisik* | UPS Online Rackmount | APC Smart-UPS SRT 5000VA RM 230V (SRT5KRMXLI) | UPS On-Line double conversion 5kVA/4.5kW, rackmount 3U, backup daya darurat server & gate automation | Rp 81.000.000 | 4 unit (Server & Gate) | Rp 324.000.000 | [APC Smart-UPS](https://www.monotaro.id/items/s013485894.html) |
| 15 | Core IT | *Blueprint Fisik* | Barcode / QR Scanner | Zebra Symbol LS2208 Handheld Scanner | Laser barcode/QR scanner 1D/2D, USB, scan dokumen surat jalan, gate pass, e-Ticket booking pos sekuriti | Rp 1.600.000 | 8 unit (Loket & Pos) | Rp 12.800.000 | [Zebra LS2208](https://www.monotaro.id/items/s003425886.html) |
| 16 | Cold Chain | *Blueprint Fisik* | Thermal Camera Reefer | Hikvision DS-2TD2636B-13/P Thermal & Optical | Pemantauan suhu permukaan reefer container, deteksi dini overheat kompresor & alarm kebakaran cold chain | Rp 30.000.000 | 4 unit (Reefer Yard) | Rp 120.000.000 | [Hikvision Thermal](https://www.hikvision.com/en/products/Thermal-Products/Thermography-thermal-cameras/temperature-screening-series/ds-2td2636b-13-p/) |
| 17 | Rail Siding | *Blueprint Fisik* | GPS Tracker Truck & Asset | Teltonika TAT240 4G LTE Asset Tracker | Pelacak posisi armada truk real-time, 4G Cat M1/NB-IoT, baterai internal otonom, tracking perjalanan Priok - Cikarang | Rp 2.500.000 | 40 unit | Rp 100.000.000 | [Teltonika TAT240](https://www.slapukas.lt/ee/teltonika-tat240) |
| 18 | Otomasi Gate | **Live Software** | Industrial Edge AI PC Gate | Advantech ARK-3532 / Neousys Nuvo-9000 Fanless | Intel Core i7, Fanless Rugged, suhu operasional -20°C s/d 60°C, catu daya 9-48V DC, IP40, Dual GbE PoE, 4x COM RS-232/422/485 untuk Timbangan & PLC Gate | Rp 42.000.000 | 4 unit (Gate Lanes) | Rp 168.000.000 | [Advantech ARK-3532](https://www.advantech.com/en/products/fanless-embedded-box-pcs/ark-3532/) |
| 19 | Keamanan / Bea Cukai | *Blueprint Fisik* | Gantry Container X-Ray | Nuctech MB1215DE / Smiths Detection HCVG | High-Energy X-Ray (6 MeV), penetrasi baja >300 mm, throughput 20-25 truk/jam, pemindaian peti kemas jalur merah & integrasi CEISA 4.0 Bea Cukai | Rp 12.500.000.000 | 1 sistem gantry | Rp 12.500.000.000 | [Nuctech X-Ray](https://www.nuctech.com/en/product/high-energy-container-and-vehicle-inspection-system/) |
| 20 | Yard & Alat Angkat | *Blueprint Fisik* | Vehicle Mounted Terminal (VMT) | Zebra VC8300 Rugged Vehicle Mounted Terminal | Android Enterprise, layar sentuh 8 inci ultra-bright, keyboard fisik, tahan getaran MIL-STD-810G & IP66 untuk kabin Reach Stacker / RTG | Rp 74.000.000 | 6 unit (Alat Berat) | Rp 444.000.000 | [Zebra VC8300](https://www.zebra.com/ap/en/products/mobile-computers/vehicle-mounted/vc8300/vc8300.html) |
| 21 | Yard & Alat Angkat | **Live Software** | DGPS / RTK GNSS Receiver | CHCNAV CGI-610 / Hemisphere GNSS | Dual-antenna RTK GNSS + IMU sensor, akurasi centimeter (<2 cm), update rate 50Hz, RS-232/CAN Bus, IP67 rugged untuk Reach Stacker & RTG auto Bay-Row-Tier | Rp 45.000.000 | 6 unit (Alat Berat) | Rp 270.000.000 | [CHCNAV CGI-610](https://chcnav.com/product-detail/cgi-610-gnss-ins-sensor) |
| 22 | Yard & Alat Angkat | **Live Software** | Spreader Twistlock & Load Cell | Bromma / Kalmar SmartSpreader Sensor Kit | Inductive proximity sensors (twistlock engaged/unlocked), 4x load cell telemetry (gross weight), RS-485/CANopen, tahan getaran maritim untuk Reach Stacker | Rp 35.000.000 | 6 unit (Spreader) | Rp 210.000.000 | [Bromma Sensors](https://www.bromma.com/products/sensors-telematics/) |
| 23 | Cold Chain | **Live Software** | Smart Reefer Power Socket | Marechal / Cavotec 380V/32A Smart Receptacle | 3P+N+E 32A 380V IP67, built-in Modbus RTU / LoRaWAN energy meter & continuous temperature logging, trip detection untuk 300 titik reefer yard | Rp 8.500.000 | 300 titik colokan | Rp 2.550.000.000 | [Marechal Reefer](https://marechal.com/en/products/decontractor/smart-reefer-sockets) |
| 24 | Yard & Alat Angkat | *Blueprint Fisik* | Rugged Mobile PDA | Zebra TC57x / Honeywell CT60 | Android Enterprise, 2D Long-Range Barcode Imager, IP68 tahan banting/air, 4G LTE & WiFi 6, hot-swappable battery untuk tallyman & inspeksi kontainer (EIR) | Rp 16.000.000 | 10 unit (Tallyman) | Rp 160.000.000 | [Zebra TC57](https://www.zebra.com/id/id/products/mobile-computers/handheld/tc57.html) |
| 25 | Rail Siding | **Live Software** | Rail Trackside Axle Counter | Frauscher RSR123 Wheel Sensor | Inductive rail head sensor, deteksi arah gerak KA, hitung as roda (axle count), estimasi kecepatan, IP68, SIL 4 untuk Intermodal Rail Siding KA Logistik | Rp 85.000.000 | 4 sensor (2 Jalur KA) | Rp 340.000.000 | [Frauscher RSR123](https://www.frauscher.com/en/products/wheel-sensors/frauscher-wheel-sensor-rsr123) |
| 26 | Keamanan / Bea Cukai | **Live Software** | Electronic Cargo Smart Seal | Jointech JT701 GPS Smart E-Seal | GPS/GSM/RFID Smart Electronic Lock, sensor kawat anti-tamper, masa pakai baterai 30 hari, IP67 untuk kontainer transit Bea Cukai Priok - Cikarang (CEISA 4.0) | Rp 3.200.000 | 100 unit e-Seal | Rp 320.000.000 | [Jointech JT701](https://www.jointech.com/product/jt701-smart-gps-electronic-seal-tracker.html) |

---

## 4. Rekapitulasi Rencana Anggaran Biaya (CAPEX) per Klaster

| No | Klaster Operasional | Jumlah Jenis Hardware | Total Unit Terpasang | Subtotal Estimasi CAPEX | Persentase |
|:---:|:---|:---:|:---:|:---|:---:|
| 1 | Otomasi Gate & Kontrol Akses | 8 | 522 unit / pack | Rp 1.460.500.000 | 7.7% |
| 2 | Telemetri Lapangan Penumpukan & Alat Angkat | 4 | 28 unit | Rp 1.084.000.000 | 5.7% |
| 3 | Cold Chain & Monitoring Reefer | 2 | 304 unit / titik | Rp 2.670.000.000 | 14.1% |
| 4 | Infrastruktur Intermodal Rel KA | 3 | 62 unit | Rp 569.600.000 | 3.0% |
| 5 | Keamanan Terminal & Pemeriksaan Bea Cukai | 5 | 157 unit / sistem | Rp 13.118.400.000 | 69.3% |
| 6 | Core Datacenter, Edge Computing & Jaringan | 4 | 26 unit | Rp 660.800.000 | 3.5% |
| **TOTAL** | **Seluruh Fasilitas CIDP 35 Ha** | **26 Item** | **1.099 Unit** | **Rp 18.943.300.000** | **100.0%** |

> [!NOTE]
> Klaster Keamanan & Bea Cukai mendominasi 69.3% total CAPEX karena adanya pengadaan **Gantry Container X-Ray Scanner (Rp 12,5 Miliar)** yang merupakan persyaratan vital kawasan pabean pelabuhan kering internasional untuk jalur merah impor/ekspor.

---

## 5. Catatan Teknis Penyesuaian & Justifikasi Rekayasa

1. **Penggantian PC Gaming dengan Fanless Industrial Edge AI Computer (Item #18):**
   - *Problem:* Spesifikasi lama berupa PC gaming Core i9-14900KF berpendingin cairan (*liquid cooling*) sangat rentan mengalami kerusakan pompa pendingin, penumpukan debu pelabuhan, dan ketidakstabilan suhu luar ruangan.
   - *Solusi:* Menggunakan Advantech ARK-3532 / Neousys Nuvo-9000 yang beroperasi tanpa kipas (*fanless*), sertifikasi industri IP40, suhu -20°C hingga 60°C, catu daya DC 9-48V, serta memiliki port serial RS-232/422/485 untuk komunikasi langsung dengan timbangan jembatan dan PLC barrier gate.

2. **Pengisian Harga Kamera OCR Kontainer & Gantry X-Ray (Item #2 & #19):**
   - Kamera OCR portal kontainer ISO 6346 ditetapkan pada estimasi industri Rp 55.000.000 / unit.
   - Sistem X-Ray Gantry kontainer dilengkapi estimasi pasar Rp 12.500.000.000 (standar Nuctech / Smiths Detection).

3. **Penambahan 6 Perangkat Kritis Dry Port:**
   - **DGPS/RTK GNSS (Item #21):** Meniadakan kesalahan penempatan kontainer (*misplaced container*) dengan penentuan slot Bay-Row-Tier otomatis berakurasi <2 cm.
   - **Spreader Twistlock & Load Cell (Item #22):** Memberikan validasi otomatis saat kontainer terangkat/diletakkan tanpa perlu konfirmasi manual radio dari operator.
   - **Smart Reefer Power Socket (Item #23):** Memastikan kontinuitas rantai dingin (*cold chain*) 300 kontainer reefer dengan pemantauan arus listrik dan deteksi dini kegagalan kompresor.
   - **Rugged Mobile PDA (Item #24):** Mempercepat pemeriksaan kondisi fisik kontainer (*Equipment Interchange Receipt / EIR*) di lapangan.
   - **Rail Axle Counter (Item #25):** Mengotomasi deteksi kedatangan dan keberangkatan kereta api kontainer di jalur siding intermodal.
   - **E-Seal Bea Cukai (Item #26):** Menjamin integritas keamanan kontainer transit berstatus pabean antara Pelabuhan Tanjung Priok dan CIDP Cikarang.
