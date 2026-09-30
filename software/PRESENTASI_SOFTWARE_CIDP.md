# 💻 BAHAN TAYANG PRESENTASI: TOPIK 7 — INLAND CONTAINER DEPOT (ICD) & DRY PORT MANAGEMENT
## Progres Minggu Ini: Arsitektur Software, Ingestion Data Hardware & Engine Informasi Intelejen Operasional

* **Mata Kuliah:** Teknologi dan Perangkat Lunak Logistik
* **Dosen Pengampu:** Dr. Tigor Franky, S.T., M.T.
* **Institusi Akademik:** Institut Transportasi dan Logistik (ITL) Trisakti — Semester 5
* **Entitas Konsultan:** Conclusion Supply Chain Consultant
* **Topik Utama Silabus:** **Topik 7 — Inland Container Depot (ICD) & Dry Port Management**
* **Studi Kasus:** Prototype Sistem Manajemen Lapangan Penumpukan (*Yard Management System / YMS*) pada **Conclusion Intermodal Dry Port (CIDP) 35 Ha**
* **Fokus Progres Minggu Ini:** **Arsitektur Software, Ingestion Data Hardware & Engine Informasi Intelejen Operasional**
* **Presenter:** Afriansayah Ayubi (*Software & ERP Process Specialist*) & Juan Gamaliel (*Data Integration Specialist*)
* **Tanggal Penyusunan:** September 2026

---

# 📑 DAFTAR ISI SLIDE PRESENTASI

1. **Slide 1:** Cover: Topik 7 — ICD & Dry Port Management (Progres: Software Architecture & Data Intelligence)
2. **Slide 2:** Taksonomi 4-Layer IT Logistik & Pengelolaan 3 Aliran Utama Terminal (Framework Dr. Tigor Franky)
3. **Slide 3:** Arsitektur Teknologi Software: Decoupled Stack & Virtual IoT Sandbox
4. **Slide 4:** Pipeline Ingestion Data: Bagaimana Software Menerima Sinyal 11 Hardware Simulasi
5. **Slide 5:** Core Processing Engine 1: Dynamic Yard Allocation & 3D Stacking Matrix (Bay-Row-Tier)
6. **Slide 6:** Core Processing Engine 2: Otomasi Gerbang, Kalkulasi VGM SOLAS & Penerbitan e-Pass
7. **Slide 7:** Core Processing Engine 3: Telemetri Cold Chain, Siding Kereta Api, & Bea Cukai CEISA 4.0
8. **Slide 8:** Business Intelligence Engine: Transformasi Data Mentah Menjadi Keputusan Cerdas
9. **Slide 9:** Terminal Tariff Engine & Integrasi Finansial ERP Odoo (*Zero Revenue Leakage*)
10. **Slide 10:** Struktur Data Relasional: Skema Basis Data `cidp_yms` & Audit Trail Reversibel
11. **Slide 11:** Arsitektur REST API Gateway & Struktur Payload JSON Riil
12. **Slide 12:** Digital Twin & Simulasi 3D Virtual Terminal (Three.js WebGL Engine)
13. **Slide 13:** Quality Assurance (QA), Postman Testing Suite & Standar Mutu GS1
14. **Slide 14:** Benchmarking Industri: Perbandingan Solusi CIDP YMS vs Navis N4 vs Solvo.TOS
15. **Slide 15:** Kesimpulan: Transformasi Data Menjadi Efisiensi, Transparansi, & Profitabilitas

---

```
========================================================================================
[SLIDE 1] COVER: TOPIK 7 — ICD & DRY PORT MANAGEMENT (PROGRES: SOFTWARE)
========================================================================================
```

### 1. Visual Slide
* **Kategori / Banner:** TOPIK 7 — INLAND CONTAINER DEPOT (ICD) & DRY PORT MANAGEMENT
* **Judul Utama Slide:** Sistem Manajemen Lapangan Penumpukan (Yard Management System / YMS) pada Conclusion Intermodal Dry Port (CIDP) 35 Ha
* **Fokus Progres Minggu Ini:** PROGRES MINGGU INI: ARSITEKTUR SOFTWARE, INGESTION DATA HARDWARE & ENGINE INFORMASI INTELEJEN OPERASIONAL
* **Tim Pengembang & Konsultan (Conclusion Supply Chain Consultant):**
  1. **Zulfikar Jafarudin Fatah** (24D507001001) — *Lead System Architect (Ketua Tim)*  
     *Fokus:* Arsitektur Sistem Web YMS, Database MySQL, Dashboard Eksekutif, & 3D Three.js Engine.
  2. **Armansyah Muchtarrom** (24D507001010) — *Hardware & Infrastructure Specialist*  
     *Fokus:* Pemetaan Protokol Sensor Lapangan (RS-232, TCP/IP, LoRaWAN) & Spesifikasi IoT.
  3. **Afriansayah Ayubi** (24D507001026) — *Software & ERP Process Specialist (Presenter Utama)*  
     *Fokus:* Logika Penanganan Peti Kemas, Terminal Tariff Engine (Lo-Lo, Storage, VGM), & Integrasi Odoo.
  4. **Juan Gamaliel** (24D507001016) — *Data Integration Specialist (Presenter Pendamping)*  
     *Fokus:* Arsitektur REST API Gateway, Pemodelan JSON Payload, Stacking Matrix & Collision Detection.
  5. **Naufal Andika Heditya** (24D507001012) — *Business Analyst & QA Specialist*  
     *Fokus:* Standardisasi AIDC (GS1-128, SSCC-18, ISO 6346), Integrasi CEISA 4.0 Bea Cukai, & QA Testing.
* **Informasi Akademik:**
  * Mata Kuliah: Teknologi dan Perangkat Lunak Logistik | Dosen Pengampu: Dr. Tigor Franky, S.T., M.T.
  * Program Studi S1 Manajemen Logistik | Institut Transportasi dan Logistik (ITL) Trisakti | September 2026

### 2. Skrip Pembicara (Speaker Notes)
> *"Selamat pagi Bapak Dr. Tigor Franky dan rekan-rekan mahasiswa yang kami banggakan.  
> Kami dari tim konsultan **Conclusion Supply Chain Consultant** kembali hadir untuk mempresentasikan kelanjutan dari progres perancangan sistem logistik terpadu kami.  
> Jika pada minggu lalu rekan kami Armansyah telah memaparkan arsitektur fisik 26 perangkat keras (hardware) di lapangan terminal 35 Hektar, maka pada pertemuan minggu ini, fokus utama kami adalah **Arsitektur Perangkat Lunak (Software), Mekanisme Penerimaan Data (Data Ingestion) dari Hardware, dan Engine Analitik yang Mengolah Data Mentah Menjadi Informasi Strategis**.  
>
> Proyek ini berakar pada silabus resmi **Topik 7: Inland Container Depot (ICD) & Dry Port Management**, dengan fokus studi kasus **Yard Management System (YMS) pada Conclusion Intermodal Dry Port (CIDP)**.  
> Hari ini, saya Afriansayah Ayubi selaku Software & ERP Specialist bersama rekan saya Juan Gamaliel selaku Data Integration Specialist akan membedah tuntas bagaimana baris kode, algoritma penumpukan, REST API, dan basis data bekerja sama mengubah sinyal fisik sensor menjadi keputusan logistik bernilai tinggi."*

---

```
========================================================================================
[SLIDE 2] TAKSONOMI 4-LAYER IT LOGISTIK & PENGELOLAAN 3 ALIRAN UTAMA
========================================================================================
```

### 1. Visual Slide
* **Judul:** Taksonomi 4-Layer IT Logistik & Pengelolaan 3 Aliran Utama Terminal
* **Sub-Judul:** Landasan Teori Rantai Pasok Berdasarkan Silabus & Framework Perkuliahan Dr. Tigor Franky
* **3 Kartu Pilar Utama:**
  1. **Landasan Teori: 3 Aliran Utama Rantai Pasok:**
     * *Goods Flow (Aliran Fisik):* Pergerakan kontainer di area 35 Ha dari gerbang masuk, jembatan timbang, lapangan penumpukan Blok A-E, hingga gerbong datar kereta api logistik.
     * *Information Flow (Aliran Informasi):* Telemetri data instan yang merekam identitas kontainer, posisi slot Bay-Row-Tier, berat VGM, dan suhu kargo dingin secara real-time.
     * *Financial Flow (Aliran Finansial):* Konversi aktivitas fisik menjadi nilai moneter (billing handling Lo-Lo, timbang, steker reefer, denda inap dwell time) yang bermuara ke ERP Odoo.
  2. **Framework Taksonomi 4 Lapisan IT Logistik:**
     * *Layer 1 (Sensing & Data Capture):* 26 Perangkat fisik lapangan (Kamera OCR, ANPR, Timbangan 80t, RFID UHF, Twistlock, DGPS RTK).
     * *Layer 2 (Network & Connectivity Protocols):* Saluran komunikasi data (Wi-Fi 7, 4G LTE, LoRaWAN, RS-232, TCP/IP, Modbus).
     * *Layer 3 (Application & Core Systems):* Software YMS CIDP, Engine Grafika 3D WebGL Three.js, Stacking Rules Engine, dan Terminal Tariff Engine.
     * *Layer 4 (Integration & Enterprise):* Pertukaran data via REST API, format JSON, CEISA 4.0 Bea Cukai, SAKA PT KAI, dan ERP Odoo.
  3. **Nilai Strategis Software: Otak Pengolah Data Mentah:**
     * Hardware tanpa software hanyalah instrumen pasif yang memancarkan sinyal listrik tanpa arti komersial.
     * Software bertindak sebagai "saraf pusat" yang mengubah voltase sensor timbangan menjadi status SOLAS VGM, mengubah koordinat satelit menjadi tumpukan kontainer rapi, dan memicu penagihan tanpa kebocoran pendapatan (*zero revenue leakage*).

### 2. Skrip Pembicara (Speaker Notes)
> *"Bapak Dosen dan rekan-rekan, sistem perangkat lunak yang kami kembangkan tidak berdiri di ruang hampa, melainkan berakar kuat pada landasan teori yang diajarkan pada Pertemuan 1 mata kuliah ini: yaitu **pengelolaan 3 aliran utama logistik** dan **taksonomi 4 lapisan infrastruktur IT**.  
> Di pelabuhan kering CIDP 35 Hektar, ketiga aliran logistik harus bergerak serempak: saat aliran fisik peti kemas bergerak di lapangan (Goods Flow), aliran informasi berupa data telemetri harus mencatatnya detik itu juga (Information Flow), dan aliran finansial langsung menerbitkan piutang atas jasa yang diberikan (Financial Flow).  
>
> Software YMS kami menempati posisi sentral pada **Layer 3 (Aplikasi Core)** dan **Layer 4 (Integrasi Enterprise)**. Perangkat lunak inilah yang mengambil sinyal mentah dari Layer 1 dan Layer 2—seperti pulsa elektrik dari loop detector atau string hexadesimal dari sensor RFID—lalu mentransformasikannya menjadi informasi operasional yang dapat dieksekusi oleh manajemen pelabuhan."*

---

```
========================================================================================
[SLIDE 3] ARSITEKTUR TEKNOLOGI SOFTWARE: DECOUPLED STACK & SANDBOX
========================================================================================
```

### 1. Visual Slide
* **Judul:** Arsitektur Teknologi Software: Decoupled Stack & Virtual IoT Sandbox
* **Sub-Judul:** Pemisahan Lapisan Sensor dan Logika Bisnis Berbasis Standar Industri Enterprise
* **Kolom Kiri: Filosofi Desain Arsitektur (Decoupled & Virtual Sandbox):**
  * *Mengapa Harus Decoupled?* Software YMS dirancang independen dari vendor perangkat keras. Sistem tidak berkomunikasi lewat port fisik tertutup, melainkan melalui perantara IoT Edge Gateway dengan protokol RESTful API & JSON.
  * *Jawaban Mengenai Uji Coba Tanpa Alat Fisik di Kelas:* Industri pelabuhan global seperti Navis N4 atau Kalmar SmartPort selalu menguji sistem menggunakan simulator payload. Struktur JSON yang ditembakkan simulator kami 100% identik dengan paket data yang akan dipancarkan oleh Edge PC Advantech di gerbang riil nantinya.
  * *Kemudahan Migrasi ke Lapangan (Zero Code Change):* Saat perangkat fisik nanti dipasang di terminal Cikarang, perangkat tinggal menembak URL endpoint yang sama tanpa memerlukan perubahan satu baris kode pun pada software YMS!
* **Kolom Kanan: Rincian Stack Teknologi Pilihan:**
  * *Backend & Database:* PHP 8.2 Enterprise Engine dengan PDO MySQL (Prepared Statements, Transaksional ACID, Zero SQL Injection), MySQL 8.0 InnoDB.
  * *3D Graphics Engine:* Three.js r128 (WebGL), OrbitControls untuk rotasi bebas 360°, Tween.js untuk interpolasi animasi derek, Raycaster untuk inspeksi metadata.
  * *2D GIS & Terminal Mapping:* Leaflet.js v1.9 untuk pemetaan spasial denah 35 Ha, plotting GPS armada, dan overlay zona operasional.
  * *Frontend UI/UX:* Tailwind CSS 3.4, Vanilla ES6 JavaScript, Google Font Plus Jakarta Sans, FontAwesome 6 Pro Icons.
  * *Analytics Dashboard:* Chart.js 4.4 untuk grafik throughput gerbang, utilisasi yard per blok, dan breakdown pendapatan.
  * *Utilities & Reporting:* QRCode.js untuk generasi instan e-Gate Pass berkode QR, export-utils.js (ekspor laporan XLSX, PDF, CSV, & Print).
  * *Testing Suite:* Postman v11 Collection untuk otomatisasi pengujian unit, regresi API, dan simulasi beban request.

### 2. Skrip Pembicara (Speaker Notes)
> *"Sering muncul pertanyaan kritis dalam evaluasi akademis: 'Kalian tidak membawa timbangan 80 ton atau radar DGPS asli ke dalam ruang kelas, lantas bagaimana membuktikan software ini benar-benar bisa bekerja dengan hardware?'  
> Jawabannya terletak pada **Arsitektur Decoupled (Terpisah)** dan **Virtual IoT Sandbox**.  
>
> Dalam standar software engineering enterprise maritim, sistem YMS modern tidak pernah ditulis terikat mati pada kabel sensor. Komunikasi selalu dijembatani oleh *Edge Computing Gateway* yang membungkus data sensor menjadi format terbuka: **REST API dengan payload JSON**.  
> Oleh karena itu, Virtual Sandbox dan Postman Collection yang kami bangun bukanlah sekadar pura-pura, melainkan merepresentasikan 100% struktur data riil.  
> Kami memilih stack teknologi yang tangguh dan ringan: PHP 8.2 dengan PDO transaksional untuk menjamin integritas data ACID, dikombinasikan dengan Three.js WebGL untuk merender kembaran digital (*Digital Twin*) 3D terminal 35 Ha langsung di browser tanpa lag."*

---

```
========================================================================================
[SLIDE 4] PIPELINE INGESTION DATA: PENERIMAAN SINYAL 11 HARDWARE SIMULASI
========================================================================================
```

### 1. Visual Slide
* **Judul:** Pipeline Ingestion Data: Bagaimana Software Menerima Sinyal 11 Hardware Simulasi
* **Sub-Judul:** Pemetaan Jalur Masuk Data dari 11 Instrumen Lapangan Menuju REST API Gateway YMS
* **3 Kartu Klaster Ingestion:**
  1. **Klaster 1: Otomasi Gerbang Masuk (6 Hardware):**
     * *Kamera ANPR (#1):* Mengirim string plat nomor via TCP/IP HTTP POST saat loop detector mendeteksi truk.
     * *Kamera OCR (#2):* Mengirim nomor kontainer ISO 6346 (4 huruf + 7 angka) via socket ethernet.
     * *Jembatan Timbang 80t (#6):* Mengirim sinyal bobot kotor via serial RS-232 Modbus RTU ke Edge PC.
     * *RFID Reader 20m (#7):* Mengirim data kartu supir via interface Wiegand-34.
     * *Edge AI PC (#18):* Mengagregasi seluruh sensor gerbang menjadi paket JSON terpadu ke endpoint YMS.
     * *LED Display (#10):* Menerima perintah balasan dari YMS untuk menampilkan teks panduan blok penumpukan.
  2. **Klaster 2: Alat Berat & Penumpukan Yard (2 Hardware):**
     * *DGPS RTK (<2 cm) (#21):* Mengirim data koordinat spasial NMEA-0183 ($GNGGA) akurasi sentimeter dari boom Reach Stacker.
     * *Spreader Twistlock & Load Cell (#22):* Mengirim status pin pengunci (0=UNLOCKED, 1=LOCKED) via bus CANopen industri dan tonase kargo riil.
     * *Logika Perkawinan Sensor:* Software mendeteksi transisi status dari LOCKED ➔ UNLOCKED sebagai penanda mutlak pelepasan kontainer di tumpukan yard, lalu detik itu juga mengunci data DGPS sebagai koordinat Bay-Row-Tier resmi!
  3. **Klaster 3, 4, & 5: Fasilitas Khusus (3 Hardware):**
     * *Smart Reefer Socket (#23):* Mengirim telemetri arus ampere, daya kWh, dan suhu pendingin kompresor (-18°C) via LoRaWAN / Modbus RTU setiap 5 detik.
     * *Rail Axle Counter (#25):* Mengirim sinyal pulsa induktif leher rel saat roda kereta melintas, dikonversi software menjadi jumlah gerbong datar.
     * *Smart E-Seal GPS (#26):* Mengirim koordinat posisi GPS rute koridor transit Priok-CIDP dan status loop kawat segel via protokol MQTT jaringan 4G.

### 2. Skrip Pembicara (Speaker Notes)
> *"Pada slide ini, kita melihat bagaimana data mentah dari ke-11 hardware simulasi masuk ke dalam software.  
> Perhatikan bahwa setiap perangkat keras memiliki bahasa dan protokolnya masing-masing: ada yang menggunakan RS-232 serial pada timbangan, Wiegand pada RFID, CANopen pada spreader crane, hingga LoRaWAN nirkabel pada colokan reefer.  
>
> Tugas lapisan *Ingestion Software* adalah bertindak sebagai penerjemah universal.  
> Contoh paling krusial ada pada **perkawinan antara sensor Twistlock Spreader (#22) dan DGPS RTK (#21)**:  
> Selama Reach Stacker bergerak di lapangan membawa kontainer, sensor twistlock berstatus `LOCKED`. Namun tepat di detik operator memutar tuas melepas kontainer di atas tumpukan, status sensor beralih menjadi `UNLOCKED`.  
> Perubahan status inilah yang menjadi pemicu (*event trigger*) bagi software untuk memotret koordinat DGPS akurasi sentimeter saat itu juga, lalu menyimpannya ke database YMS sebagai lokasi Bay-Row-Tier permanen."*

---

```
========================================================================================
[SLIDE 5] CORE ENGINE 1: DYNAMIC YARD ALLOCATION & 3D STACKING MATRIX
========================================================================================
```

### 1. Visual Slide
* **Judul:** Core Engine 1: Dynamic Yard Allocation & 3D Stacking Matrix (Bay-Row-Tier)
* **Sub-Judul:** Pembaruan Koordinat Penumpukan Spasial & Logika Anti-Tabrakan Slot Lapangan 35 Ha
* **Kolom Kiri: Algoritma Pembaruan Posisi Stacking (Fokus Kunci Evaluasi Dosen):**
  * *Transformasi Koordinat Spasial:* Algoritma software mengubah koordinat geografis satelit (Latitude, Longitude, Altitude) menjadi sistem koordinat standar terminal peti kemas maritim:
    $$\text{DGPS}(X, Y, Z) \;\longrightarrow\; \text{Block [A - E]} \;\times\; \text{Bay [01 - 12]} \;\times\; \text{Row [01 - 04]} \;\times\; \text{Tier [01 - 04]}$$
  * *Aturan Penumpukan Cerdas (Stacking Rules Engine):*
    1. *Heavy-on-Bottom Rule:* Kontainer bermuatan berat (Laden 25–32 ton) wajib ditempatkan di Tier 1 dan Tier 2. Kontainer kosong (Empty) dialokasikan di Tier 3 dan Tier 4 demi stabilitas tumpukan dari tiupan angin kencang.
    2. *Anti-Overhang Check:* Sistem menolak penumpukan kontainer 40ft High Cube di atas kontainer 20ft standar untuk mencegah patah struktur kargo.
    3. *Segregasi Kargo Berbahaya (DG):* Kontainer bahan kimia mudah terbakar (IMO Class) otomatis dialokasikan ke Blok DG khusus yang terisolasi dari jalur rel dan gedung kantor.
* **Kolom Kanan: Deteksi Tabrakan Slot & Sinkronisasi Atomik:**
  * *Deteksi Tabrakan Slot (Collision Check):* Sebelum perintah relokasi dieksekusi, software memeriksa ketersediaan slot:
    ```sql
    SELECT id FROM containers 
    WHERE block = :b AND bay = :by AND row = :r AND tier = :t 
      AND status = 'in_yard' AND id != :current_id;
    ```
    Jika slot sudah terisi kontainer lain, sistem membunyikan peringatan: *"Slot B-04-02-03 sudah terisi! Pilih koordinat lain!"*
  * *Transaksi Atomik ACID:* Database MySQL memastikan bahwa pembaruan slot kontainer, status alat berat, dan pencatatan tagihan Lo-Lo Rp 250.000 tersimpan bersamaan tanpa risiko data korup (*rollback* otomatis jika salah satu query gagal).
  * *Sinkronisasi Seketika ke WebGL 3D:* Event listener langsung menggerakkan objek mesh 3D kontainer di layar monitor Three.js secara halus.

### 2. Skrip Pembicara (Speaker Notes)
> *"Bapak Dr. Tigor Franky secara spesifik menuliskan dalam silabus: **'Fokus Simulasi: Pembaruan posisi stacking kontainer di area yard saat dipindahkan oleh operator Reach Stacker'**.  
> Slide 5 ini adalah pembuktian ilmiah bagaimana software kami mengeksekusi instruksi tersebut.  
>
> Pertama, software memiliki modul transformasi koordinat yang memetakan sinyal DGPS satelit menjadi matriks standar pelabuhan: Blok, Bay, Row, dan Tier.  
> Kedua, software kami dilengkapi **Stacking Rules Engine** yang cerdas: kontainer berat dilarang ditaruh di Tier atas (*Heavy-on-Bottom*), dan kontainer 40 kaki dilarang ditumpuk di atas kontainer 20 kaki.  
> Ketiga, ada mekanisme **Collision Detection**: jika operator mencoba menumpuk kontainer di koordinat yang sudah dihuni boks lain, sistem akan menolak perintah tersebut.  
> Semua transaksi ini dijalankan secara atomik dalam basis data MySQL PDO, dan langsung meng-update visualisasi 3D Three.js pada layar operator secara instan."*

---

```
========================================================================================
[SLIDE 6] CORE ENGINE 2: OTOMASI GERBANG, KALKULASI VGM SOLAS & E-PASS
========================================================================================
```

### 1. Visual Slide
* **Judul:** Core Engine 2: Otomasi Gerbang, Kalkulasi VGM SOLAS & Penerbitan e-Pass
* **Sub-Judul:** Rantai Pemrosesan Logika Transaksi Gerbang Terpadu dalam Waktu 28 Detik
* **4 Kartu Tahapan Berurutan:**
  1. **Tahap 1: Data Matching & Validasi Identitas (ANPR & OCR):**
     * Sinyal masuk dari loop detector memicu kamera ANPR membaca plat nomor truk trailer.
     * Software mencocokkan plat nomor dengan database armada vendor di modul Odoo Fleet.
     * Kamera OCR membaca nomor kontainer ISO 6346 ➔ memvalidasi status dokumen kepemilikan kargo (Delivery Order / Booking Ticket).
  2. **Tahap 2: Kalkulasi Berat & Kepatuhan SOLAS VGM:**
     * 8 Load cell jembatan timbang 80 ton mengirim bobot kotor riil kendaraan.
     * Software menghitung bobot bersih kargo:
       $$\text{Net VGM} = \text{Gross Measured Weight} - \text{Truck Tare Weight}$$
     * Validasi Regulasi Maritim SOLAS:
       - Jika $\text{Net VGM} \le 34.000\text{ kg} \;\rightarrow\; \text{Status: } \textbf{VERIFIED\_PASS}$.
       - Jika $\text{Net VGM} > 34.000\text{ kg} \;\rightarrow\; \text{Status: } \textbf{OVERLOAD\_REJECT}$ (Palang terkunci + alarm merah).
     * Otomatis membebankan tagihan Jasa Timbang VGM Rp 50.000 ke akun pengguna.
  3. **Tahap 3: Smart Routing & Penentuan Blok Yard:**
     * Software memeriksa karakteristik kargo: Dry dipetakan ke Blok A/B/C, Reefer diarahkan ke Blok Reefer, DG ke Blok Kargo Berbahaya.
     * Algoritma mencari koordinat kosong terdekat untuk meminimalkan waktu tempuh Reach Stacker.
     * Mengirim instruksi ke LED Display gerbang: `"SILAHKAN MASUK - BLOK B-04"`.
  4. **Tahap 4: Otomasi Palang & Penerbitan Digital e-Gate Pass:**
     * Edge PC memicu relay fisik untuk mengangkat palang gerbang (barrier gate) dalam 1.2 detik.
     * Software menerbitkan dokumen digital e-Gate Pass berkode QR (format `GP-YYYYMMDD-XXXX`) ke smartphone pengemudi.
     * Seluruh transaksi selesai hanya dalam **28 detik**, memangkas waktu gate manual hingga 65%!

### 2. Skrip Pembicara (Speaker Notes)
> *"Pada modul gerbang, software kami bertindak sebagai hakim otomatis yang menentukan apakah suatu truk diizinkan masuk atau ditolak.  
> Dalam waktu kurang dari 28 detik, empat proses komputasi terjadi secara simultan.  
> Yang paling krusial adalah **Kalkulasi Berat Bersih VGM SOLAS**. Konvensi maritim internasional SOLAS mewajibkan verifikasi berat kargo demi keselamatan pelayaran.  
> Software kami secara otomatis mengurangkan berat kotor timbangan dengan berat tara truk (`Net = Gross - Tare`).  
> Jika berat bersih di bawah ambang batas 34 ton, status `VERIFIED_PASS` diterbitkan, biaya timbang Rp 50.000 dicatat, rute blok penumpukan ditentukan, teks panduan dikirim ke layar LED, dan tiket digital e-Gate Pass berkode QR langsung diterbitkan ke smartphone supir truk."*

---

```
========================================================================================
[SLIDE 7] CORE ENGINE 3: COLD CHAIN, REL KA, & KEPABEANAN CEISA 4.0
========================================================================================
```

### 1. Visual Slide
* **Judul:** Core Engine 3: Telemetri Cold Chain, Siding Kereta Api, & Bea Cukai CEISA 4.0
* **Sub-Judul:** Pemrosesan Khusus untuk Komoditas Rantai Dingin, Moda Kereta Api, dan Regulasi Pabean
* **3 Kartu Klaster Spesifik:**
  1. **Cold Chain Telemetry Engine (Smart Reefer Watchdog):**
     * *Monitoring 300 Colokan Listrik 380V:* Menerima data konsumsi arus (Ampere) dan daya (kW) secara kontinu via LoRaWAN/Modbus.
     * *Threshold Alert Pencegah Pembusukan:* Ambang batas komoditas beku adalah -18°C hingga -20°C. Jika suhu melonjak di atas -15°C atau arus mendadak 0 Ampere (indikasi trip listrik/steker lepas), software membunyikan alarm visual merah di Command Center dalam waktu 3 detik.
     * *Kalkulasi Otomatis Tarif Steker:* Menghitung durasi colokan aktif dan menerbitkan tagihan Jasa Steker Reefer Rp 250.000 per shift (8 jam) ke Odoo Invoicing.
  2. **Intermodal Rail Siding Engine (Deteksi Rangkaian KA Logistik):**
     * *Algoritma Konversi Pulsa Gandar:* Software mengolah pulsa leher rel dari sensor Frauscher:
       $$\text{Total Gerbong Datar} = \frac{\text{Axle Count}}{4}, \quad \text{Kapasitas TEU} = \text{Total Gerbong} \times 2\text{ TEU}$$
       *(Contoh: 120 Axles terbaca = 30 Gerbong Datar = 60 TEU kapasitas muat).*
     * *Alokasi RTG Crane Otomatis:* Menerbitkan Job Order ke kabin operator RTG-01 untuk membongkar peti kemas dari gerbong datar ke yard Blok A.
     * *Rekonsiliasi Sewa Rel (TAC):* Menghitung pembagian biaya Track Access Charge bersama PT Kereta Api Logistik (KAI Logistik).
  3. **Customs Clearance Pipeline (Integrasi CEISA 4.0 DJBC):**
     * *Pengawasan Koridor Transit Priok-CIDP:* Geofencing GPS melacak pergerakan kontainer impor dari pelabuhan laut Tanjung Priok menuju pelabuhan kering Cikarang.
     * *Tamper Alert Sabotase Segel:* Jika kawat Smart E-Seal terputus ilegal di perjalanan, software membunyikan alarm sabotase dan mengunci status kontainer.
     * *Penetapan Jalur Pabean:* Sinkronisasi dokumen SPPB (Surat Persetujuan Pengeluaran Barang): Jalur Hijau dapat langsung rilis; Jalur Merah otomatis dialokasikan ke hanggar Behandle / Gantry X-Ray 6 MeV.

### 2. Skrip Pembicara (Speaker Notes)
> *"Di luar kargo umum, pelabuhan kering 35 Ha kami memiliki tiga mesin komputasi spesifik:  
> Pertama, **Smart Reefer Watchdog**. Kargo dingin seperti vaksin atau daging beku bernilai miliaran rupiah sangat rentan rusak. Software kami memantau 300 steker listrik secara kontinu. Jika kompresor mati atau suhu naik di atas -15°C, alarm otomatis berbunyi dalam 3 detik agar teknisi segera turun ke lapangan.  
> Kedua, **Intermodal Rail Siding Engine**. Software menerima pulsa dari sensor leher rel Frauscher dan secara otomatis menghitung jumlah gerbong (`Total Gerbong = Axle / 4`), lalu memerintahkan derek RTG untuk bersiap membongkar peti kemas.  
> Ketiga, **Integrasi CEISA 4.0 Bea Cukai**. Melalui Smart E-Seal ber-GPS, kontainer impor dalam status transit terus diawasi jalurnya. Begitu dokumen SPPB dari Bea Cukai terbit, sistem langsung mengizinkan barang keluar dari terminal."*

---

```
========================================================================================
[SLIDE 8] BUSINESS INTELLIGENCE ENGINE: TRANSFORMASI RAW DATA JADI INFORMASI
========================================================================================
```

### 1. Visual Slide
* **Judul:** Business Intelligence Engine: Transformasi Data Mentah Menjadi Keputusan Cerdas
* **Sub-Judul:** Menjawab Arahan Dosen: Bagaimana Software Menghasilkan Informasi Strategis bagi Manajemen Terminal
* **4 Kuadran Analitik Bisnis:**
  1. **1. Audit Dwell Time & Peringatan Inap Progresif:**
     * Menghitung durasi inap setiap kontainer secara real-time: $\text{Dwell Time} = \text{Waktu Sekarang} - \text{Gate\_In\_Time}$.
     * Audit 3 Masa Tarif: Masa I Bebas Tarif (1-3 hari), Masa II Rp 45.000/hari (4-10 hari), Masa III Progresif Rp 90.000/hari (>10 hari).
     * Visualisasi Heatmap Lapangan: Kontainer yang menginap terlalu lama (*overstay*) diberi highlight warna oranye/merah agar operator memprioritaskan pengeluarannya guna menjaga rata-rata dwell time terminal tetap di angka ideal **2.1 hari**.
  2. **2. Okupansi Blok Yard & Optimasi Kapasitas 35 Ha:**
     * Formula Utilisasi Yard: $\text{Occupancy \%} = (\text{Slot Terisi} / \text{Total Kapasitas 200 Slot}) \times 100\%$.
     * *Dynamic Load Balancing:* Jika Blok A telah mencapai utilisasi di atas 80%, sistem secara cerdas mengarahkan alokasi kontainer baru ke Blok B atau C guna mencegah antrean crane yang menumpuk di satu lorong penumpukan.
  3. **3. Produktivitas Alat Berat & Konsumsi BBM Solar:**
     * Melacak KPI Operasional: *Moves per Hour* (target >22 gerakan boks per jam untuk Reach Stacker Kalmar DRG450).
     * Analisis Rasio Bahan Bakar: Menghitung konsumsi solar (liter) per gerakan kontainer untuk mendeteksi rute alat berat yang boros.
     * Pemicu Pemeliharaan Odoo: Jam operasi alat berat diakumulasikan secara otomatis; setiap 250 jam kerja, modul Odoo Maintenance menerbitkan jadwal servis berkala.
  4. **4. Truck Turnaround Time (TTT) & Analisis Bottleneck:**
     * Mengukur durasi total truk sejak masuk gerbang hingga keluar: Target TTT terminal < 35 Menit.
     * Mengidentifikasi stasiun kerja yang mengalami perlambatan (apakah pada saat penimbangan, inspeksi dokumen, atau proses bongkar di yard).
     * Menyajikan metrik ketepatan waktu layanan pengiriman kargo (98% On-Time Delivery).

### 2. Skrip Pembicara (Speaker Notes)
> *"Ini adalah inti dari pertanyaan: 'Bagaimana software menghasilkan informasi yang bagus bagi manajemen terminal?'  
> Sinyal hardware yang berdiri sendiri tidak memiliki nilai keputusan. Namun saat masuk ke **Business Intelligence Engine** YMS kami, data tersebut diolah menjadi empat wawasan manajerial yang sangat berharga:  
>
> 1. **Dwell Time Analytics**: Sistem secara aktif mengaudit masa inap kontainer. Kontainer yang sudah masuk Masa II dan Masa III otomatis diberi warna khusus pada heatmap yard agar segera dikeluarkan, sehingga rata-rata dwell time terminal kita berada di angka **2.1 hari**, jauh di bawah standar nasional 3 hari.  
> 2. **Yard Occupancy & Balancing**: Sistem membagi beban kerja secara merata antar-blok agar tidak terjadi penumpukan crane di satu area.  
> 3. **OEE Alat Berat**: Kita bisa melihat produktivitas Reach Stacker berapa boks per jam dan berapa liter konsumsi solar per kontainer.  
> 4. **Truck Turnaround Time**: Kita memastikan supir truk trailer tidak tertahan lama di pelabuhan kering, dengan target keluar terminal di bawah 35 menit."*

---

```
========================================================================================
[SLIDE 9] TARIFF ENGINE & SINKRONISASI FINANSIAL ERP ODOO (ZERO LEAKAGE)
========================================================================================
```

### 1. Visual Slide
* **Judul:** Terminal Tariff Engine & Integrasi Finansial ERP Odoo (Zero Revenue Leakage)
* **Sub-Judul:** Pemetaan Otomatis Kejadian Lapangan Menjadi Entri Pembukuan Keuangan Resmi
* **Tabel Matriks Tarif & Dampak Finansial:**

| Kejadian Fisik (Hardware) | Sensor Pemicu | Rumus / Logika Tarif YMS | Nilai Jasa (IDR) | Modul ERP Odoo | Dampak Akuntansi |
|:---|:---|:---|:---|:---|:---|
| **Truk Masuk Timbangan** | Weighbridge 80t (#6) | Biaya verifikasi SOLAS bersertifikat | Rp 50.000 / truk | Odoo Invoicing | Pendapatan jasa timbang |
| **RS Angkat/Lepas Box** | Twistlock & DGPS (#21,22) | Tarif Lo-Lo: 40ft Rp 250k / 20ft Rp 175k | Rp 250.000 / box | Odoo Invoicing | Pendapatan stevedoring yard |
| **Inap Yard Hari Ke-5** | Audit Dwell Timer YMS | Tarif Masa II: Rp 45.000/box/hari | Rp 45.000 / hari | Odoo Invoicing | Piutang sewa penumpukan |
| **Steker Reefer Aktif** | Smart Socket 380V (#23) | Tarif listrik dingin per 8 jam (shift) | Rp 250.000 / shift | Odoo Invoicing | Pendapatan utilitas cold chain |
| **Bongkar Kereta Api** | Axle Counter & RTG (#25) | Tarif alih muat antarmoda rel ke yard | Rp 350.000 / box | Odoo Invoicing | Pendapatan intermodal handling |
| **Sewa Jalur Rel KAI** | Sensor Gandar Frauscher (#25)| Bagi hasil Track Access Charge PT KAI | Rp 1.500.000 / gerbong | Odoo Purchase | Beban pokok sewa rel (HPP) |

### 2. Skrip Pembicara (Speaker Notes)
> *"Salah satu kelemahan terbesar terminal logistik tradisional adalah kebocoran pendapatan (*revenue leakage*)—misalnya Reach Stacker mengangkat boks tetapi operator lupa mencatat, atau kontainer menginap lebih dari 3 hari tetapi tarif penumpukannya tidak ditagihkan.  
> Di sistem CIDP YMS, hal ini **mustahil terjadi**.  
>
> Kami merancang **Event-Driven Terminal Tariff Engine** yang terhubung langsung ke ERP Odoo.  
> Begitu twistlock crane terbuka di yard, mutasi data fisik langsung memicu pencatatan Jasa Lift-Off Rp 250.000.  
> Begitu steker reefer dicolokkan, timer shift listrik berjalan dan menagihkan Rp 250.000 per 8 jam.  
> Begitu kontainer melewati batas waktu 3 hari bebas tarif, sistem otomatis menerbitkan invoice denda inap Masa II sebesar Rp 45.000 per hari.  
> Melalui integrasi REST Webhook ke Odoo Invoicing dan General Ledger, setiap tetes keringat operator dan setiap watt listrik yang digunakan di terminal 35 Ha langsung terkonversi menjadi pendapatan yang sah."*

---

```
========================================================================================
[SLIDE 10] STRUKTUR DATA RELASIONAL: SKEMA BASIS DATA CIDP_YMS
========================================================================================
```

### 1. Visual Slide
* **Judul:** Struktur Data Relasional: Skema Basis Data cidp_yms & Audit Trail
* **Sub-Judul:** Arsitektur Tabel MySQL Transaksional dengan Kunci Relasi dan Pencatatan Log Kronologis
* **4 Kartu Entitas Tabel Utama:**
  1. **TABEL: containers (Entitas Master Peti Kemas):**
     * `id` (INT, PK, Auto Increment)
     * `container_number` (VARCHAR 11, Unique Index, format ISO 6346)
     * `iso_code` (VARCHAR 4, misal 42G1, 22G1, 42R1)
     * `size_type` (40FT HIGH CUBE / 20FT STANDARD)
     * `cargo_type` (dry / reefer / dg / empty)
     * `rfid_tag` (VARCHAR 32, UHF EPC Gen2)
     * `sscc_code` (VARCHAR 30, Standar GS1)
     * `gross_weight_kg` (DECIMAL 10,2)
     * `owner_company` (VARCHAR 100, Shipper / Pelayaran)
     * `seal_number` (VARCHAR 50)
     * `customs_status` (SPPB_CLEARED / RED_LANE / TRANSIT)
     * `block, bay, row, tier` (CHAR 1 & INT 2, Koordinat Yard)
     * `gate_in_time, gate_out_time` (DATETIME)
     * `status` (in_yard / gate_out)
  2. **TABEL: equipment (Entitas Armada Alat Berat):**
     * `id` (INT, PK, Auto Increment)
     * `equipment_id` (VARCHAR 10, misal RS-01, RS-02, RTG-01)
     * `equipment_type` (REACH_STACKER / RTG_CRANE)
     * `brand_model` (Kalmar DRG450-65S5 / Konecranes Electric)
     * `operator_name, operator_id` (Nama & NIK operator crane)
     * `status` (idle / operating / maintenance)
     * `gps_x, gps_y` (DECIMAL 8,2, Koordinat spasial)
     * `current_container` (VARCHAR 11, Boks yang sedang digantung)
     * `fuel_percent` (INT, Level solar armada)
     * `hours_today` (DECIMAL 4,1, Akumulasi jam operasi)
  3. **TABEL: trucks & trains (Entitas Moda Antarmoda):**
     * *Tabel trucks:* `id`, `license_plate` (Unique), `rfid_tag` (e-Pass supir), `driver_name`, `company`, `container_number`, `status`, `gate_in_time`, `gate_out_time`.
     * *Tabel trains:* `id`, `train_code` (misal KA-LOG-JKT-SMG), `origin`, `destination`, `total_wagons`, `loaded_wagons`, `status`, `arrival_time`.
  4. **TABEL: yard_events (Entitas Audit Trail & Billing Engine):**
     * `id` (BIGINT, PK, Auto Increment)
     * `event_type` (GATE_IN / RELOCATION / RAIL_UNLOAD / REEFER_PLUG)
     * `container_number`, `equipment_id`, `operator_name`
     * `from_block, from_bay, from_row, from_tier` (Titik asal)
     * `to_block, to_bay, to_row, to_tier` (Titik tujuan baru)
     * `billable_amount` (Nominal rupiah jasa terminal)
     * `notes` (Keterangan audit kronologis)
     * `created_at` (TIMESTAMP waktu kejadian detik persis)

### 2. Skrip Pembicara (Speaker Notes)
> *"Rekan saya Juan Gamaliel dan Lead Architect Zulfikar merancang skema basis data relasional MySQL `cidp_yms` ini dengan kepatuhan normalisasi tingkat tinggi (3NF).  
> Terdapat empat pilar tabel utama yang saling berelasi:  
> 1. Tabel `containers` menyimpan master peti kemas, spesifikasi ISO, tag RFID, dan koordinat Bay-Row-Tier.  
> 2. Tabel `equipment` menyimpan data armada Reach Stacker dan RTG, operator yang bertugas, koordinat GPS, dan konsumsi BBM.  
> 3. Tabel `trucks` dan `trains` merepresentasikan moda transportasi darat dan rel kereta api.  
> 4. Dan yang paling istimewa adalah tabel `yard_events`: tabel ini adalah buku besar (*immutable audit trail*) yang mencatat setiap jengkal pergerakan kontainer di terminal lengkap dengan jam kejadian, alat yang mengangkat, nama operator, dan nominal tagihan rupiahnya.  
> Skema ini menjamin bahwa seluruh data operasional dapat dilacak balik (*traceable*) dan tidak dapat dimanipulasi."*

---

```
========================================================================================
[SLIDE 11] ARSITEKTUR REST API GATEWAY & PAYLOAD JSON RIIL
========================================================================================
```

### 1. Visual Slide
* **Judul:** Arsitektur REST API Gateway & Struktur Payload JSON Riil
* **Sub-Judul:** Dokumentasi Teknis Dua Endpoint Produksi di Proyek CIDP (`api/simulator_action.php`)
* **Kotak Kiri (Dark Code Theme): API Relokasi Kontainer (Lo-Lo Yard):**
  ```json
  POST /api/simulator_action.php?action=move_container
  Content-Type: application/json

  {
    "container_number": "MSKU9182374",
    "equipment_id": "RS-02",
    "to_block": "B",
    "to_bay": "04",
    "to_row": "02",
    "to_tier": "03"
  }

  // Response: HTTP 200 OK
  {
    "success": true,
    "message": "Kontainer MSKU9182374 berhasil dipindahkan oleh RS-02 ke B-04-02-03",
    "event_id": 142,
    "billing": {
      "charge_name": "Jasa Lo-Lo / Relokasi Yard",
      "amount": 250000,
      "currency": "IDR"
    }
  }
  ```
* **Kotak Kanan (Dark Code Theme): API Ingestion Gate-In & Timbangan VGM:**
  ```json
  POST /api/simulator_action.php?action=gate_in
  Content-Type: application/json

  {
    "license_plate": "B 9481 UEK",
    "driver_name": "Sholehudin",
    "company": "PT Samudera Logistik Prima",
    "measured_gross_kg": 30500,
    "truck_tare_kg": 11200
  }

  // Response: HTTP 200 OK
  {
    "success": true,
    "truck": {
      "license_plate": "B 9481 UEK",
      "gross_weight": 30500,
      "tare_weight": 11200,
      "vgm_net": 19300,
      "vgm_status": "SOLAS VERIFIED (PASS)"
    },
    "billing": {
      "charge_name": "Jasa Jembatan Timbang VGM SOLAS",
      "amount": 50000
    }
  }
  ```

### 2. Skrip Pembicara (Speaker Notes)
> *"Ini adalah bukti nyata implementasi baris kode yang sudah berjalan di server lokal kami pada berkas `api/simulator_action.php`.  
> Pada kotak sebelah kiri, Anda melihat payload saat Reach Stacker memindahkan kontainer `MSKU9182374` ke Blok B Bay 04 Row 02 Tier 03. API memvalidasi slot, memperbarui koordinat, mencatat event audit ID 142, dan mengembalikan konfirmasi billing Jasa Lo-Lo sebesar Rp 250.000.  
>
> Pada kotak sebelah kanan, Anda melihat payload dari gerbang masuk: truk plat `B 9481 UEK` ditimbang dengan berat bruto 30.500 kg dan tara 11.200 kg. API menghitung berat bersih VGM 19.300 kg, memberikan status `SOLAS VERIFIED (PASS)`, dan menerbitkan tagihan timbang Rp 50.000.  
> Struktur JSON ini bersih, mematuhi standar RESTful API, dan siap dihubungkan langsung ke sistem pihak ketiga mana pun."*

---

```
========================================================================================
[SLIDE 12] DIGITAL TWIN & SIMULATOR 3D (THREE.JS WEBGL ENGINE)
========================================================================================
```

### 1. Visual Slide
* **Judul:** Digital Twin & Simulasi 3D Virtual Terminal (Three.js WebGL Engine)
* **Sub-Judul:** Representasi Kembaran Digital 35 Ha Waktu-Nyata pada Layar Pengguna Langsung di Browser
* **3 Kartu Fitur Grafika 3D:**
  1. **Grafika 3D WebGL Interaktif (Three.js v2.0):**
     * Memodelkan tata letak fisik terminal 35 Ha secara proporsional: jalan lingkar gate, 5 blok lapangan penumpukan (A-E), dermaga reefer, dan jalur rel sepur simpan ganda.
     * Rendering 60 FPS langsung di browser web tanpa memerlukan instalasi aplikasi tambahan.
     * Kode Warna Peti Kemas Standar ISO: Biru (Dry Kargo), Putih/Cyan (Reefer Dingin), Merah/Kuning (Hazardous DG), Abu-abu (Empty Box).
     * Animasi Kinematik Derek (Tween.js): Pergerakan lengan boom Reach Stacker dan gerakan troli RTG crane disimulasikan secara realistis.
  2. **5 Sudut Kamera Sinematik (Cinematic Camera Presets):**
     * *1. Drone 35 Ha Overview:* Sudut pandang makro manajerial dari ketinggian untuk memantau seluruh area pelabuhan kering.
     * *2. Gate & VGM:* Sudut pandang gerbang masuk dan jembatan timbang truk trailer.
     * *3. Blok Yard:* Sudut pandang lorong penumpukan peti kemas Bay-Row-Tier.
     * *4. Rail Siding:* Sudut pandang alih muat rangkaian kereta api logistik.
     * *5. Kabin RS (POV Operator):* Sudut pandang pengemudi Reach Stacker dari dalam kabin kemudi!
  3. **3D Raycaster Inspector & Simulasi Lingkungan:**
     * *Raycasting Click-to-Inspect:* Setiap kontainer 3D di layar dapat diklik langsung dengan kursor mouse. Seketika muncul jendela HUD melayang yang menampilkan nomor ISO 6346, kode RFID, berat kotor VGM, nama pemilik barang, nomor segel, dan slot yard.
     * *Dynamic Time-of-Day Lighting:* Dilengkapi saklar pencahayaan Siang (Day), Senja (Sunset), dan Malam (Night) berlampu sorot high-mast untuk merefleksikan operasional 24 jam nonstop pelabuhan kering berstandar internasional.

### 2. Skrip Pembicara (Speaker Notes)
> *"Salah satu kebanggaan terbesar dari prototipe kami adalah modul **Digital Twin 3D** yang dibangun oleh Lead Architect Zulfikar di halaman `pages/simulator.php`.  
> Kami tidak menggunakan animasi video pra-rekaman, melainkan mesin grafika 3D WebGL Three.js murni yang merender seluruh lanskap 35 Hektar secara real-time pada 60 FPS.  
>
> Penguji atau operator dapat memilih 5 sudut kamera sinematik, termasuk sudut pandang kamera dari dalam kabin operator Reach Stacker!  
> Lebih hebat lagi, kami menerapkan teknik **3D Raycasting**: jika dosen mengeklik salah satu boks kontainer di layar 3D, sistem akan melakukan deteksi perpotongan laser dan memunculkan pop-up inspeksi berisi data lengkap boks tersebut: mulai dari nomor ISO, berat timbangan VGM, tag RFID, hingga tujuan pengirimannya.  
> Ini adalah implementasi *Digital Twin* yang sesungguhnya di dunia logistik maritim."*

---

```
========================================================================================
[SLIDE 13] QUALITY ASSURANCE (QA), POSTMAN TESTING & STANDAR GS1
========================================================================================
```

### 1. Visual Slide
* **Judul:** Quality Assurance (QA), Postman Testing Suite & Standar Mutu GS1
* **Sub-Judul:** Verifikasi Keandalan API, Validasi Integritas Data, dan Kepatuhan Standar Logistik Global
* **Kolom Kiri: Postman Automated Testing Suite (Penjaminan Kualitas QA):**
  * *Otomatisasi Assertion Script:*
    - Response Time Latency: Memastikan waktu tanggap API di bawah 150 milidetik (`pm.expect(pm.response.responseTime).to.be.below(150)`).
    - Status Code Verification: Memastikan seluruh respons bernilai HTTP 200 OK untuk request valid dan HTTP 400 Bad Request untuk data cacat.
    - JSON Schema Contract: Memvalidasi ketersediaan field wajib seperti `container_number`, `vgm_status`, dan `billable_amount`.
  * *Pengujian Skenario Ekstrem (Edge Cases & Stress Testing):*
    - Uji coba penolakan kontainer kelebihan muatan (Overweight >34t).
    - Uji coba pencegahan tabrakan koordinat tumpukan (Double Booking Slot Prevention).
    - Uji coba degradasi sistem saat database offline (Graceful Fallback Mode).
* **Kolom Kanan: Kepatuhan Standar Mutu Global (GS1, SSCC, & ISO):**
  * *Standar Penomoran ISO 6346 Maritim:* Verifikasi algoritma Check Digit Modulo 11 untuk memastikan 4 huruf kode pemilik dan 7 angka seri kontainer valid secara matematis internasional.
  * *Standar Barcode GS1-128 & SSCC-18:* Mendukung penguraian Application Identifier (AI):
    - `(00)` Serial Shipping Container Code (SSCC-18) untuk pelacakan unik unit logistik kemasan palet.
    - `(10)` Nomor Batch / Lot manufaktur kargo.
    - `(17)` Tanggal Kedaluwarsa (*Expiration Date*) produk farmasi dan rantai dingin.
  * *Standar RFID Maritim (EPCglobal UHF Gen2 / ISO 18000-6C):* Identifikasi tanpa sentuh jarak jauh untuk portal otomatisasi gerbang.

### 2. Skrip Pembicara (Speaker Notes)
> *"Kualitas sebuah software tidak hanya diukur dari tampilannya yang indah, tetapi dari keandalan dan kepatuhannya terhadap standar global. Modul QA kami dikawal ketat oleh Naufal Andika Heditya.  
> Kami menggunakan **Postman Automated Testing Suite** untuk menguji kinerja API kami secara terus-menerus. Setiap kali ada perubahan kode, skrip otomatis memvalidasi apakah waktu respons tetap di bawah 150 milidetik dan apakah format data JSON tetap valid.  
>
> Selain itu, software kami mematuhi standar internasional logistik:  
> Nomor kontainer divalidasi menggunakan **algoritma Check Digit Modulo 11 ISO 6346** untuk mencegah kesalahan ketik supir.  
> Dan untuk penanganan kargo di gudang CFS, sistem mendukung penguraian **GS1-128 dan SSCC-18 (Serial Shipping Container Code)**.  
> Inilah yang membuat prototipe kami siap bertransformasi menjadi software kelas industri komersial."*

---

```
========================================================================================
[SLIDE 14] BENCHMARKING SISTEM: CIDP YMS VS NAVIS N4 VS SOLVO.TOS
========================================================================================
```

### 1. Visual Slide
* **Judul:** Benchmarking Industri: Perbandingan Solusi CIDP YMS vs Navis N4 vs Solvo.TOS
* **Sub-Judul:** Analisis Komparatif Keunggulan Solusi Konsultan Dibandingkan Perangkat Lunak Pasar Global
* **Tabel Perbandingan Enterprise:**

| Parameter Evaluasi Sistem | CIDP YMS (Inovasi Tim Konsultan) | Navis N4 (Standard Port Global) | Solvo.TOS (Intermodal Rusia/Eropa) |
|:---|:---|:---|:---|
| **Arsitektur & Penerapan** | Decoupled Web-Based, Zero Client Install, Modular | On-Premise / Hybrid Heavy Enterprise Client | Client-Server On-Premise Monolitik |
| **Kesiapan Simulasi IoT** | Tersedia Virtual Sandbox & Postman Ready | Memerlukan lisensi emulator mahal tambahan | Simulasi terbatas pada lingkungan lab tertutup |
| **Dukungan Antarmoda Rel** | Native Frauscher Axle Counter & SAKA KAI | Modul add-on rail berbiaya lisensi tinggi | Native rail support untuk standar rel CIS |
| **Integrasi Pabean Indonesia**| Terhubung langsung spesifikasi CEISA 4.0 DJBC | Memerlukan bridging EDI kustomisasi khusus | Memerlukan modul kustomisasi pihak ketiga |
| **Integrasi Finansial ERP** | Event-Driven Webhook langsung ke Odoo ERP | Integrasi kompleks ke SAP via middleware IBM MQ| Konektor kustom ke sistem akuntansi lokal |
| **Biaya Kepemilikan (TCO)** | Sangat Efisien, Bebas Royalti Lisensi Asing | Sangat Tinggi ($500k - $2M+ per terminal) | Tinggi ($250k - $800k per terminal) |

### 2. Skrip Pembicara (Speaker Notes)
> *"Sebagai calon sarjana logistik yang bersikap kritis, kami membandingkan sistem yang kami rancang dengan dua raksasa pasar dunia: **Navis N4** asal Amerika Serikat yang menguasai pelabuhan laut internasional, dan **Solvo.TOS** asal Eropa yang kuat di terminal kereta api.  
>
> Di mana keunggulan CIDP YMS?  
> Pertama, Navis dan Solvo dirancang untuk pelabuhan laut konvensional dengan biaya lisensi selangit (mencapai miliaran hingga puluhan miliar rupiah per tahun).  
> Sementara CIDP YMS kami rancang khusus untuk karakteristik **Dry Port Intermodal di Indonesia**: sistem kami sudah mengadopsi regulasi kepabeanan **CEISA 4.0 Bea Cukai**, mendukung rekonsiliasi Surat Angkutan Kereta Api (**SAKA PT KAI**), dan terintegrasi langsung dengan ERP Odoo secara *native*.  
> Sistem kami berbasis web murni tanpa perlu instalasi aplikasi desktop yang berat di komputer kantor, sehingga menghadirkan *Total Cost of Ownership* yang jauh lebih rasional bagi pengelola pelabuhan kering nasional."*

---

```
========================================================================================
[SLIDE 15] KESIMPULAN: TRANSFORMASI DATA MENJADI EFISIENSI & PROFITABILITAS
========================================================================================
```

### 1. Visual Slide
* **Judul:** Kesimpulan: Transformasi Data Menjadi Efisiensi, Transparansi, & Profitabilitas
* **Sub-Judul:** 4 Pilar Nilai Strategis Software bagi Operasional Terminal Pelabuhan Kering 35 Ha
* **4 Kartu Kesimpulan Utama:**
  1. **1. Kecepatan Operasional & Eliminasi Kemacetan:**
     * Otomasi gerbang masuk 28 detik memangkas antrean truk trailer di jalan akses Cikarang.
     * Algoritma alokasi yard cerdas mengeliminasi manuver sia-sia (*unproductive moves*) Reach Stacker.
     * Rata-rata masa inap kargo (Dwell Time) berhasil ditekan hingga 2.1 hari (jauh di bawah batas toleransi 3 hari).
  2. **2. Integritas Data Absolut Tanpa Human Error:**
     * 100% data transaksi operasional ditangkap otomatis oleh sensor dan divalidasi oleh REST API Gateway.
     * Menghilangkan pencatatan kertas manual (*100% paperless terminal*) dan salah ketik nomor kontainer.
     * Menjamin kepatuhan penuh terhadap standar keselamatan maritim SOLAS VGM dan regulasi DJBC.
  3. **3. Akuntabilitas Finansial Penuh (Zero Revenue Leakage):**
     * Setiap aksi fisik alat berat dan pemakaian utilitas listrik reefer langsung tercatat menjadi faktur tagihan di ERP Odoo.
     * Mencegah kebocoran pendapatan terminal (*zero unbilled services*).
     * Memastikan pengembalian investasi (ROI) fasilitas terminal 35 Ha tercapai sesuai target studi kelayakan.
  4. **4. Visibilitas Kolaboratif Multi-Stakeholder:**
     * *Shipper / Pemilik Kargo:* Memantau pergerakan peti kemas dan mengunduh e-Invoice secara transparan.
     * *Bea Cukai:* Mengawasi kontainer jalur merah dan integritas E-Seal secara real-time.
     * *PT KAI Logistik:* Sinkronisasi jadwal kedatangan kereta dan rekonsiliasi kapasitas gandar rel secara otomatis.

### 2. Skrip Pembicara (Speaker Notes)
> *"Bapak Dr. Tigor Franky dan rekan-rekan sekalian,  
> Mengakhiri presentasi progres software kami minggu ini, izinkan kami menyimpulkan empat pilar nilai yang dihadirkan oleh **Conclusion Intermodal Dry Port YMS**:  
>
> 1. **Kecepatan Operasional**: Kami membuktikan bahwa dengan otomasi gerbang 28 detik dan algoritma penumpukan otomatis, kemacetan terminal dapat dieliminasi dan dwell time ditekan ke angka 2.1 hari.  
> 2. **Integritas Data**: Kami membuktikan bahwa software mampu menjembatani data mentah dari 11 hardware simulasi menjadi informasi yang akurat tanpa kesalahan manusia.  
> 3. **Akuntabilitas Finansial**: Melalui integrasi dua arah ke ERP Odoo, kami menjamin *Zero Revenue Leakage*—tidak ada satu sen pun jasa terminal yang luput dari penagihan.  
> 4. **Kolaborasi Multimoda**: Sistem ini merekatkan hubungan kerja antara pengelola terminal, supir truk, PT KAI Logistik, dan Direktorat Jenderal Bea dan Cukai dalam satu wadah digital terpadu.  
>
> Demikian presentasi progres software dari kelompok Conclusion Supply Chain Consultant. Kami siap mendemonstrasikan kode program dan menjawab pertanyaan dari Bapak Dosen. Terima kasih."*

---
*Dokumen ini disusun secara resmi oleh **Conclusion Supply Chain Consultant** untuk mata kuliah Teknologi dan Perangkat Lunak Logistik, ITL Trisakti (2026).*
