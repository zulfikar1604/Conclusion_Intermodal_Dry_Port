# 📘 DOKUMEN ARSITEKTUR PERANGKAT LUNAK (SOFTWARE ARCHITECTURE SPECIFICATION)
## Proyek Conclusion Intermodal Dry Port (CIDP) 35 Ha — Yard Management System (YMS)
**Penanggung Jawab:** Afriansayah Ayubi (*Software & ERP Process Specialist*) & Zulfikar Jafarudin Fatah (*Lead System Architect*)  
**Konsultan:** Conclusion Supply Chain Consultant  
**Institusi Akademik:** Institut Transportasi dan Logistik (ITL) Trisakti — Semester 5  
**Mata Kuliah:** Teknologi dan Perangkat Lunak Logistik | **Dosen Pengampu:** Dr. Tigor Franky, S.T., M.T.  
**Topik Utama Silabus:** **Topik 7 — Inland Container Depot (ICD) & Dry Port Management**  
**Status Dokumen:** Release v2.0 (Spesifikasi Software, Ingestion Hardware, & Data Intelligence)  
**Tanggal Rilis:** September 2026  

---

## 1. 🎯 Ringkasan Eksekutif & Landasan Filosofis Software

Pembangunan kawasan pelabuhan kering **Conclusion Intermodal Dry Port (CIDP)** seluas 35 Hektar di koridor industri Cikarang mengadopsi standar operasional internasional. Keberhasilan fasilitas ini bergantung pada **keandalan perangkat lunak (software)** sebagai "otak dan sistem saraf pusat" yang mengorkestrasi pergerakan fisik alat berat, armada truk trailer, rangkaian kereta api barang, serta proses perizinan pabean.

Dokumen ini merupakan cetak biru (*blueprint*) arsitektur perangkat lunak resmi yang menjabarkan:
1. **Bagaimana software menerima data (*data ingestion*)** dari 11 perangkat keras aktif di lapangan melalui protokol standar industri (RS-232, Modbus, TCP/IP, LoRaWAN, Wiegand, CANopen, MQTT).
2. **Bagaimana software memproses data mentah (*core processing logic*)** menggunakan algoritma penumpukan spasial 3D (*Bay-Row-Tier*), deteksi tabrakan slot (*collision detection*), validasi maritim SOLAS VGM, dan pengawasan suhu rantai dingin.
3. **Bagaimana software menghasilkan informasi intelejen bernilai tinggi (*business intelligence & insights*)** untuk manajemen eksekutif (audit dwell time, okupansi yard, produktivitas alat berat, dan pencegahan kemacetan gate).
4. **Bagaimana software mengintegrasikan peristiwa fisik ke akuntansi ERP Odoo (*financial flow*)** guna menjamin prinsip *Zero Revenue Leakage* (setiap pergerakan kargo otomatis tercatat menjadi tagihan sah).

---

## 2. 🏛️ Taksonomi 4-Layer IT Logistik & Pengelolaan 3 Aliran Rantai Pasok

Mengacu pada materi dasar perkuliahan Pertemuan 1 oleh **Dr. Tigor Franky, S.T., M.T.**, arsitektur sistem CIDP YMS mengelola **tiga aliran utama logistik** secara simultan melalui **empat lapisan (layers) teknologi**:

```
[ LAYER 4: INTEGRATION & ENTERPRISE LAYER ]
  ├── ERP Odoo (Finance, General Ledger, Invoicing, Fleet, Maintenance)
  ├── CEISA 4.0 DJBC (Kepabeanan & SPPB Clearance)
  └── SAKA KAI Logistik (Surat Angkutan Kereta Api & Track Access Charge)
                            ▲ (REST API / JSON Webhook)
[ LAYER 3: CORE APPLICATION & SOFTWARE SYSTEMS ]
  ├── CIDP YMS Core Engine (PHP 8.2 + PDO MySQL InnoDB)
  ├── 3D Digital Twin Engine (Three.js WebGL r128 + Tween.js)
  ├── 3D Stacking Matrix & Collision Detection Engine (Bay-Row-Tier)
  ├── SOLAS VGM & Automated Gate Engine (<28 Detik)
  ├── Cold Chain Reefer Watchdog & Threshold Alert Engine
  └── Terminal Tariff & Financial Event Engine
                            ▲ (HTTP POST / RESTful Gateway)
[ LAYER 2: NETWORK & CONNECTIVITY PROTOCOLS ]
  ├── Industrial Edge AI PC Gateway (Advantech ARK-3532 Fanless)
  ├── Wi-Fi 7 Outdoor (Ubiquiti U7 Pro) & Jaringan Seluler 4G LTE
  └── Protokol Lapangan: RS-232, Modbus RTU/TCP, Wiegand-34, CANopen, LoRaWAN, MQTT
                            ▲ (Sinyal Sensor / Data Telemetri Mentah)
[ LAYER 1: SENSING & DATA CAPTURE LAYER (HARDWARE) ]
  └── 26 Perangkat Lapangan (11 Simulasi Live Software + 15 Fasilitas Blueprint Fisik)
```

### Sinkronisasi 3 Aliran Utama Logistik:
1. **Goods Flow (Aliran Fisik):** Pergerakan peti kemas di lahan 35 Ha dari gerbang masuk, jembatan timbang 80t, lapangan penumpukan Blok A-E, hingga gerbong datar kereta api logistik.
2. **Information Flow (Aliran Informasi):** Telemetri instan yang merekam identitas peti kemas (ISO 6346), tag RFID UHF supir, koordinat satelit DGPS RTK (<2 cm), status twistlock crane, dan suhu dingin reefer (-18°C).
3. **Financial Flow (Aliran Finansial):** Otomatisasi penerbitan piutang (*Accounts Receivable*) dan e-Invoice atas jasa handling (Lo-Lo, penumpukan progresif, timbang VGM, steker listrik reefer) ke sistem ERP Odoo tanpa intervensi manual.

---

## 3. 🧩 Arsitektur Komponen Software (Decoupled & Modern Web Stack)

### A. Filosofi Decoupled Architecture (Pemisahan Lapisan)
Software YMS CIDP menganut prinsip **Decoupled Architecture**: logika aplikasi web tidak pernah terikat secara langsung ke port serial atau driver perangkat keras tertentu. Seluruh sensor berkomunikasi melalui **IoT Edge Gateway** yang membungkus data menjadi payload standar **RESTful JSON**.

> **Jawaban Resmi Mengenai Ketiadaan Perangkat Keras Asli di Kelas:**  
> *"Dalam standar industri maritim enterprise global (seperti Navis N4 atau Kalmar SmartPort), software tidak pernah diuji langsung dengan menancapkan kabel ke derek crane, melainkan menggunakan Virtual IoT Mocking Payload. Struktur JSON yang ditembakkan oleh simulator dan Postman Collection kami 100% identik dengan payload yang akan dikirimkan gateway fisik Advantech nantinya. Ketika perangkat fisik dipasang di Cikarang, perangkat tinggal menembak URL endpoint yang sama tanpa memerlukan perubahan satu baris kode pun pada software YMS kita."*

### B. Tabel Lengkap Spesifikasi Software & Bill of Software (Software BOM)

| No | Kategori Software | Nama Software / Library | Versi | Peran & Fungsi Spesifik di Proyek CIDP | Integrasi & Output yang Dihasilkan |
|:---:|:---|:---|:---:|:---|:---|
| **1** | **Backend Engine** | **PHP Enterprise Core** | v8.2 | Menjalankan logika bisnis terminal, kalkulasi tarif, sesi pengguna, dan routing modul. | Memproses seluruh HTTP request dan transaksi bisnis YMS. |
| **2** | **Database Access** | **PDO (PHP Data Objects)** | Native | Abstraksi akses basis data dengan Prepared Statements dan transaksi ACID (Begin/Commit/Rollback). | Mencegah SQL Injection & menjamin konsistensi data multi-user. |
| **3** | **Basis Data Relasional** | **MySQL Database Server** | v8.0 (InnoDB) | Menyimpan data master kontainer, armada truk, alat berat, jadwal KA, dan buku besar audit log. | Penyimpan tunggal (*Single Source of Truth / SSOT*) terminal 35 Ha. |
| **4** | **Web Server Runtime** | **Apache HTTP Server (XAMPP)** | v2.4 | Menangani koneksi web, melayani berkas statis & dinamis, serta manajemen virtual host. | Lingkungan eksekusi lokal mandiri (*zero external dependency*). |
| **5** | **3D Graphics Engine** | **Three.js (WebGL)** | r128 | Merender lanskap 3D terminal 35 Ha, lapangan penumpukan, rel KA, dan boks kontainer secara real-time. | Menghasilkan Digital Twin visual interaktif 60 FPS di browser. |
| **6** | **3D Physics & Motion** | **Tween.js** | v18.6 | Menghitung interpolasi kinematik pergerakan lengan derek Reach Stacker, twistlock, dan laju truk. | Animasi pergerakan alat berat fisik yang halus dan realistis. |
| **7** | **3D Viewport Control** | **OrbitControls.js** | r128 | Menyediakan kendali navigasi kamera rotasi bebas 360°, pan, dan zoom pada lanskap 35 Ha. | Menghadirkan 5 preset kamera sinematik (Drone, Gate, Yard, Rail, POV). |
| **8** | **2D GIS & Layout Map** | **Leaflet.js** | v1.9.4 | Menampilkan denah interaktif 2D terminal 35 Ha dengan koordinat geospasial dan plotting GPS. | Peta denah 2D penempatan fasilitas terminal dan sebaran 26 hardware. |
| **9** | **Digital Pass Generator** | **QRCode.js** | v1.0 | Meng-generate kode QR dinamis di sisi klien untuk dokumen tiket e-Gate Pass supir truk. | Menerbitkan e-Gate Pass digital instan (format GP-YYYYMMDD-XXXX). |
| **10** | **Frontend UI System** | **Tailwind CSS** | v3.4 | Framework utilitas CSS modern untuk antarmuka pengguna yang bersih, responsif, dan rapi. | Tampilan dasbor profesional berstandar korporat maritim. |
| **11** | **Visual Data Analytics** | **Chart.js** | v4.4 | Merender grafik tren gerbang masuk/keluar 7 hari, okupansi blok yard, dan proyeksi finansial. | Dashboard analitik operasional dan Business Intelligence eksekutif. |
| **12** | **Reporting Engine** | **export-utils.js** | v1.0 | Utilitas klien untuk mengekspor data tabel operasional ke format Excel (XLSX), PDF, CSV, dan cetak. | Laporan rekapitulasi data kontainer dan invoice untuk stakeholder. |
| **13** | **Testing & Simulation** | **Postman API Suite** | v11 | Menguji endpoint API, memvalidasi skema payload JSON, dan mensimulasikan tembakan data IoT. | Penjaminan mutu (QA) dan bukti uji integrasi hardware-to-software. |
| **14** | **Enterprise ERP** | **Odoo ERP System** | v17 Community | Sistem ERP korporasi untuk modul Invoicing, Accounting General Ledger, Fleet, dan Maintenance. | Menghitung piutang otomatis dan e-Faktur (Zero Revenue Leakage). |
| **15** | **Customs Integration** | **DJBC CEISA 4.0** | v4.0 API | Sistem kepabeanan Bea Cukai untuk sinkronisasi dokumen SPPB dan pengawasan kargo transit. | Pemilahan Jalur Hijau/Merah dan pelacakan segel Smart E-Seal. |
| **16** | **Rail Logistics SAKA** | **PT KAI Logistik SAKA API** | Intermodal API | Sinkronisasi manifes Surat Angkutan Kereta Api (SAKA) dan rekonsiliasi Track Access Charge. | Manajemen alih muat antarmoda rel KA logistik Tanjung Priok - CIDP. |

---

## 4. 📡 Pipeline Ingestion Data: Cara Software Menerima Sinyal 11 Hardware Simulasi

Software YMS CIDP menyediakan gerbang masuk (*Ingestion Layer*) terstandarisasi untuk 11 perangkat keras aktif:

```
[ HARDWARE LAPANGAN ]             [ PROTOKOL ]           [ PAYLOAD MASUK ]          [ AKSI DI SOFTWARE YMS ]
Kamera ANPR (#1)         ───►  TCP/IP HTTP POST  ───►  "plate": "B 9481 UEK"   ───► Validasi armada di DB & Odoo
Kamera OCR Portal (#2)   ───►  Ethernet Socket   ───►  "iso": "MSKU9182374"    ───► Verifikasi DO & Booking manifes
Weighbridge 80t (#6)     ───►  Serial RS-232     ───►  "gross_kg": 30500       ───► Hitung Net VGM = Gross - Tare
RFID Reader 20m (#7)     ───►  Wiegand-34 / TCP  ───►  "rfid": "E28011700..."  ───► Identifikasi instan supir truk
Edge AI PC (#18)         ───►  REST JSON Batch   ───►  Combined Gate Object    ───► Eksekusi keputusan gerbang masuk
LED Display (#10)        ◄───  RS-485 / Serial   ◄───  "MASUK BLOK B-04"       ◄─── Tampilkan instruksi jalur supir
DGPS RTK (<2cm) (#21)    ───►  NMEA-0183 Serial  ───►  Lat/Long Centimeter     ───► Konversi ke koordinat Bay-Row-Tier
Twistlock Spreader (#22) ───►  CANopen Bus       ───►  State: 0=Open, 1=Lock   ───► Kunci slot saat status UNLOCKED
Smart Reefer Socket (#23)───►  LoRaWAN / Modbus  ───►  Current (A), Temp (°C)  ───► Pantau suhu & catat jam steker
Axle Counter KA (#25)    ───►  Digital Pulses    ───►  Axle Count = 120        ───► Hitung Gerbong = Axle / 4
Smart E-Seal GPS (#26)   ───►  4G LTE / MQTT     ───►  GPS Route + Wire Loop   ───► Pantau koridor pabean & tamper
```

---

## 5. 🧠 Core Processing & Intelligence Engines

### Engine 1: Dynamic Yard Allocation & 3D Stacking Matrix (Bay-Row-Tier)
*Fokus Utama Silabus Topik 7: Pembaruan posisi stacking kontainer di area yard saat dipindahkan oleh operator Reach Stacker.*
1. **Transformasi Koordinat Spasial:**
   Algoritma mengubah pembacaan satelit DGPS RTK $(X, Y, Z)$ menjadi format terminal peti kemas:
   $$\text{Slot ID} = \text{Block} \text{ [A-E]} \;-\; \text{Bay} \text{ [01-12]} \;-\; \text{Row} \text{ [01-04]} \;-\; \text{Tier} \text{ [01-04]}$$
2. **Aturan Penumpukan Cerdas (Stacking Rules):**
   - *Heavy-on-Bottom:* Kontainer bermuatan berat (Laden 25-32 ton) wajib dialokasikan di Tier 1 atau Tier 2 guna menjamin stabilitas gravitasi tumpukan dari tiupan angin kencang.
   - *Anti-Overhang:* Sistem secara ketat menolak penumpukan boks 40 kaki di atas boks 20 kaki.
   - *Segregasi Kargo Berbahaya:* Kontainer DG (Dangerous Goods / IMO Class) otomatis diarahkan ke Blok DG terisolasi.
3. **Deteksi Tabrakan Slot (Collision Prevention):**
   Sebelum mutasi disimpan, software menjalankan validasi ketersediaan:
   ```php
   $stmtCheck = $pdo->prepare("SELECT id FROM containers 
                               WHERE block = ? AND bay = ? AND row = ? AND tier = ? 
                                 AND status = 'in_yard' AND id != ?");
   $stmtCheck->execute([$to_block, $to_bay, $to_row, $to_tier, $container_id]);
   if ($stmtCheck->fetch()) {
       throw new Exception("Slot {$to_block}-{$to_bay}-{$to_row}-{$to_tier} sudah terisi! Pilih slot lain.");
   }
   ```
4. **Sinkronisasi Atomik ACID:**
   Update koordinat kontainer, pelepasan status alat berat, dan pencatatan audit trail diikat dalam satu transaksi database (`$pdo->beginTransaction()` dan `$pdo->commit()`).

---

### Engine 2: Otomasi Gerbang, Kalkulasi SOLAS VGM & e-Gate Pass (<28 Detik)
1. **Data Matching Otomatis:** Sinyal loop detector memicu kamera ANPR dan OCR. Software mencocokkan plat nomor dan nomor boks dengan data Delivery Order di database.
2. **Kalkulasi Berat Bersih SOLAS VGM:**
   $$\text{Net VGM} = \text{Gross Measured Weight} - \text{Truck Tare Weight}$$
   - Jika $\text{Net VGM} \le 34.000\text{ kg} \rightarrow$ Status `VERIFIED_PASS`.
   - Jika $\text{Net VGM} > 34.000\text{ kg} \rightarrow$ Status `OVERLOAD_REJECT` (Palang tetap terkunci, alarm merah berbunyi).
3. **Smart Routing & Panduan LED:** Sistem menentukan blok kosong terdekat untuk menghemat BBM Reach Stacker, lalu mengirim teks panduan ke layar LED: `"SILAHKAN MASUK - BLOK B-04"`.
4. **Penerbitan Dokumen Digital e-Gate Pass:** Software menerbitkan kode QR e-Gate Pass (`GP-YYYYMMDD-XXXX`) ke smartphone pengemudi dan mengangkat palang gerbang otomatis dalam 1.2 detik.

---

### Engine 3: Telemetri Cold Chain, Siding Kereta Api, & Bea Cukai CEISA 4.0
1. **Cold Chain Watchdog:**
   Memantau 300 steker reefer 380V secara kontinu. Target suhu komoditas beku adalah -18°C s/d -20°C. Jika suhu melonjak $> -15^\circ\text{C}$ atau arus listrik mendadak 0 Ampere (indikasi trip listrik/steker lepas), alarm visual merah langsung menyala di Command Center dalam waktu 3 detik guna mencegah pembusukan muatan.
2. **Intermodal Rail Siding Engine:**
   Mengolah pulsa induktif sensor gandar rel Frauscher:
   $$\text{Total Gerbong Datar} = \frac{\text{Axle Count}}{4}, \quad \text{Total Muatan TEU} = \text{Gerbong Datar} \times 2\text{ TEU}$$
   Memicu penugasan RTG crane untuk alih muat kontainer dari kereta api ke lapangan penumpukan Blok A, serta menghitung bagi hasil sewa rel (*Track Access Charge / TAC*) bersama PT KAI Logistik.
3. **Customs Clearance Pipeline (CEISA 4.0 DJBC):**
   Melacak rute truk ber-E-Seal GPS dari Pelabuhan Tanjung Priok menuju CIDP Cikarang. Jika kawat segel terputus ilegal di jalan raya, alarm sabotase berbunyi seketika. Mengintegrasikan dokumen SPPB (Surat Persetujuan Pengeluaran Barang) untuk pemilahan Jalur Hijau (rilis langsung) atau Jalur Merah (wajib pemindai Gantry X-Ray 6 MeV).

---

### Engine 4: Business Intelligence & Analitik Eksekutif
*Menjawab arahan: "Software yang digunakan untuk menggenerate mencari suatu informasi yang bagus"*
1. **Audit Dwell Time & Peringatan Inap Progresif:**
   Menghitung durasi inap real-time per peti kemas:
   $$\text{Dwell Time (Hari)} = \text{Waktu Sekarang} - \text{Gate\_In\_Time}$$
   Menampilkan *Heatmap Lapangan*: kontainer yang menginap lebih dari 3 hari (Masa II & Masa III) diberi sorotan warna agar diprioritaskan keluar terminal, menjaga rata-rata dwell time terminal tetap di angka **2.1 hari**.
2. **Okupansi Blok Yard & Optimasi Kapasitas 35 Ha:**
   Menghitung persentase keterisian 200 slot yard secara real-time. Jika Blok A melebihi 80%, software secara otomatis mengalihkan alokasi penumpukan kontainer masuk ke Blok B atau C (*Dynamic Load Balancing*).
3. **Produktivitas Alat Berat (OEE) & Konsumsi Solar:**
   Melacak rasio *Moves per Hour* (target >22 gerakan boks/jam) dan konsumsi bahan bakar solar per kontainer. Setiap akumulasi 250 jam kerja, software otomatis menerbitkan tiket servis berkala di modul Odoo Maintenance.
4. **Truck Turnaround Time (TTT):**
   Mengukur performa keluar-masuk armada truk (target TTT < 35 menit) guna mendeteksi titik macet (*bottleneck*) operasional di gerbang maupun lapangan penumpukan.

---

## 6. 💰 Terminal Tariff Engine & Sinkronisasi Finansial ERP Odoo (Zero Leakage)

Setiap peristiwa fisik di lapangan yang diproses oleh software YMS secara otomatis memicu entri jurnal keuangan di sistem ERP Odoo melalui panggilan REST Webhook:

$$\text{Aksi Fisik di Lapangan} \;\xrightarrow{\text{Sensor}}\; \text{Logika Bisnis YMS} \;\xrightarrow{\text{Payload JSON}}\; \text{Modul Odoo ERP} \;\xrightarrow{\text{Jurnal}}\; \text{Faktur & Piutang}$$

| Kejadian Fisik Lapangan | Sensor Pemicu | Aturan Tarif YMS | Nilai Jasa (IDR) | Modul Odoo Terintegrasi |
|:---|:---|:---|:---|:---|
| **Truk Masuk Timbangan** | Weighbridge 80t (#6) | Jasa penimbangan bersertifikat SOLAS | Rp 50.000 / truk | Odoo Invoicing |
| **Lift-Off Peti Kemas** | Twistlock & DGPS (#21,22) | Jasa penanganan derek Lo-Lo 40ft | Rp 250.000 / box | Odoo Invoicing |
| **Penumpukan Hari Ke-5** | Audit Dwell Timer YMS | Jasa inap yard Masa II (Hari 4-10) | Rp 45.000 / hari | Odoo Invoicing |
| **Colokan Listrik Reefer** | Smart Socket 380V (#23) | Jasa pemantauan dingin per shift (8 jam) | Rp 250.000 / shift | Odoo Invoicing |
| **Alih Muat Kereta Api** | Axle Counter & RTG (#25) | Jasa stevedoring gerbong datar KA | Rp 350.000 / box | Odoo Invoicing |
| **Sewa Rangkaian KA KAI** | Sensor Gandar Frauscher (#25)| Bagi hasil Track Access Charge PT KAI | Rp 1.500.000 / gerbong | Odoo Purchase (HPP) |

---

## 7. 🗄️ Kamus Data & Skema Basis Data Relasional (`cidp_yms`)

Struktur basis data dirancang menggunakan mesin MySQL InnoDB dengan integritas referensial dan pengindeksan performa tinggi:

### 1. Tabel: `containers` (Master Peti Kemas)
```sql
CREATE TABLE `containers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `container_number` VARCHAR(11) NOT NULL UNIQUE,  -- Format ISO 6346 (4 huruf + 7 angka)
  `iso_code` VARCHAR(4) NOT NULL,                  -- Contoh: 42G1, 22G1, 42R1
  `size_type` VARCHAR(30) NOT NULL,                -- 40FT HIGH CUBE / 20FT STANDARD
  `cargo_type` ENUM('dry','reefer','dg','empty') NOT NULL DEFAULT 'dry',
  `rfid_tag` VARCHAR(32) DEFAULT NULL,             -- Tag UHF EPC Gen2
  `sscc_code` VARCHAR(30) DEFAULT NULL,            -- Serial Shipping Container Code GS1
  `gross_weight_kg` DECIMAL(10,2) NOT NULL,        -- Berat timbangan riil
  `owner_company` VARCHAR(100) NOT NULL,           -- Pemilik barang / Shipping Line
  `seal_number` VARCHAR(50) DEFAULT NULL,          -- Nomor segel botol / e-Seal
  `customs_status` VARCHAR(30) DEFAULT 'TRANSIT',  -- SPPB_CLEARED / RED_LANE / TRANSIT
  `block` CHAR(5) NOT NULL,                        -- A, B, C, REEFER, DG, EMPTY
  `bay` CHAR(2) NOT NULL,                          -- 01 s/d 12
  `row` CHAR(2) NOT NULL,                          -- 01 s/d 04
  `tier` CHAR(2) NOT NULL,                         -- 01 s/d 04
  `status` ENUM('in_yard','gate_out') NOT NULL DEFAULT 'in_yard',
  `gate_in_time` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `gate_out_time` DATETIME DEFAULT NULL,
  INDEX idx_box (container_number),
  INDEX idx_rfid (rfid_tag),
  INDEX idx_slot (block, bay, row, tier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 2. Tabel: `equipment` (Armada Derek & Alat Berat)
```sql
CREATE TABLE `equipment` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `equipment_id` VARCHAR(10) NOT NULL UNIQUE,      -- RS-01, RS-02, RS-03, RTG-01
  `equipment_type` VARCHAR(30) NOT NULL,           -- REACH_STACKER / RTG_CRANE
  `brand_model` VARCHAR(50) NOT NULL,              -- Kalmar DRG450-65S5
  `operator_name` VARCHAR(50) NOT NULL,            -- Nama operator bertugas
  `operator_id` VARCHAR(20) NOT NULL,              -- NIK operator
  `status` ENUM('idle','operating','maintenance') DEFAULT 'idle',
  `gps_x` DECIMAL(8,2) NOT NULL,                   -- Koordinat posisi lapangan X
  `gps_y` DECIMAL(8,2) NOT NULL,                   -- Koordinat posisi lapangan Y
  `current_container` VARCHAR(11) DEFAULT NULL,    -- Peti kemas yang digantung
  `last_block` CHAR(5) DEFAULT 'A',
  `fuel_percent` INT DEFAULT 100,                  -- Level solar armada
  `hours_today` DECIMAL(4,1) DEFAULT 0.0,          -- Akumulasi jam operasi
  `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3. Tabel: `yard_events` (Buku Besar Audit Trail & Pemicu Tagihan)
```sql
CREATE TABLE `yard_events` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `event_type` VARCHAR(30) NOT NULL,               -- GATE_IN, RELOCATION, RAIL_UNLOAD, REEFER_PLUG
  `container_number` VARCHAR(11) NOT NULL,
  `equipment_id` VARCHAR(10) DEFAULT NULL,
  `from_block` CHAR(5) DEFAULT NULL,
  `from_bay` CHAR(2) DEFAULT NULL,
  `from_row` CHAR(2) DEFAULT NULL,
  `from_tier` CHAR(2) DEFAULT NULL,
  `to_block` CHAR(5) DEFAULT NULL,
  `to_bay` CHAR(2) DEFAULT NULL,
  `to_row` CHAR(2) DEFAULT NULL,
  `to_tier` CHAR(2) DEFAULT NULL,
  `operator_name` VARCHAR(50) DEFAULT NULL,
  `billable_amount` DECIMAL(12,2) DEFAULT 0.00,    -- Nominal rupiah tagihan ERP
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_evt_box (container_number),
  INDEX idx_evt_time (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 8. 🌐 Spesifikasi REST API Gateway (`api/simulator_action.php`)

Seluruh endpoint menerima request berformat JSON atau parameter POST/GET dan mengembalikan respons dengan header `Content-Type: application/json; charset=utf-8`.

### Endpoint 1: Relokasi Peti Kemas (Lo-Lo Yard)
* **Method:** `POST`
* **URL:** `/api/simulator_action.php?action=move_container`
* **Request Payload:**
```json
{
  "container_number": "MSKU9182374",
  "equipment_id": "RS-02",
  "to_block": "B",
  "to_bay": "04",
  "to_row": "02",
  "to_tier": "03"
}
```
* **Response (HTTP 200 OK):**
```json
{
  "success": true,
  "message": "Kontainer MSKU9182374 berhasil dipindahkan oleh RS-02 ke B-04-02-03",
  "event_id": 142,
  "container": {
    "id": 1,
    "container_number": "MSKU9182374",
    "from": "A-01-01-01",
    "to": "B-04-02-03"
  },
  "billing": {
    "charge_name": "Jasa Lo-Lo / Relokasi Yard",
    "amount": 250000,
    "currency": "IDR"
  }
}
```

---

### Endpoint 2: Ingestion Truk Gate-In & Timbangan VGM
* **Method:** `POST`
* **URL:** `/api/simulator_action.php?action=gate_in`
* **Request Payload:**
```json
{
  "license_plate": "B 9481 UEK",
  "driver_name": "Sholehudin",
  "company": "PT Samudera Logistik Prima",
  "measured_gross_kg": 30500,
  "truck_tare_kg": 11200
}
```
* **Response (HTTP 200 OK):**
```json
{
  "success": true,
  "message": "Truk B 9481 UEK berhasil Gate-In melalui Gate 1!",
  "truck": {
    "id": 14,
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

---

### Endpoint 3: Bongkar Muatan Kereta Api (Rail Stevedoring)
* **Method:** `POST`
* **URL:** `/api/simulator_action.php?action=rail_discharge`
* **Response (HTTP 200 OK):**
```json
{
  "success": true,
  "message": "Kontainer TEMU9921820 dari KA Logistik berhasil dibongkar oleh RTG-01 ke Blok A-02-01-01!",
  "container": {
    "container_number": "TEMU9921820",
    "block": "A",
    "bay": "02",
    "row": "01",
    "tier": "01"
  },
  "billing": {
    "charge_name": "Jasa Alih Muat KA Logistik (Rail Stevedoring)",
    "amount": 350000
  }
}
```

---

## 9. 🎮 Digital Twin 3D Virtual Terminal Engine (Three.js WebGL)

Modul `pages/simulator.php` membangun replika digital interaktif (*Digital Twin*) terminal 35 Ha:
1. **WebGL Canvas Pipeline:** Merender 35 Hektar lanskap, 5 blok lapangan penumpukan kontainer, 2 lajur rel sepur simpan, stasiun gerbang masuk, dan gedung manajemen.
2. **Standar Pewarnaan Mesh 3D ISO Maritim:**
   - `0x0170b9` (Biru): Peti kemas kargo umum (*Dry Container 40HC/20ft*).
   - `0xffffff` / `0x06b6d4` (Putih / Cyan): Peti kemas rantai dingin (*Reefer Container*).
   - `0xdc2626` / `0xf59e0b` (Merah / Kuning): Peti kemas bahan kimia berbahaya (*Dangerous Goods*).
   - `0x94a3b8` (Abu-abu): Peti kemas kosong (*Empty Container Depot*).
3. **5 Preset Sudut Kamera Sinematik:**
   - `overview`: Drone 35 Ha Makro (posisi ketinggian $Y=250$, pandangan seluruh terminal).
   - `gate`: Posisi gerbang timbangan VGM dan kamera OCR/ANPR.
   - `yard`: Sudut pandang tumpukan kontainer Bay-Row-Tier Blok A-E.
   - `rail`: Sudut pandang sepur simpan alih muat rangkaian kereta api.
   - `cockpit`: Sudut pandang orang pertama (*First-Person View*) dari kabin operator Reach Stacker!
4. **3D Raycasting Metadata Inspector:**  
   Pengguna dapat mengeklik objek kontainer 3D di layar monitor. Three.js Raycaster memancarkan sinar optik matematis dari kursor kamera, mendeteksi mesh kontainer yang terpotong, dan memunculkan pop-up inspeksi berisi rincian: Nomor Kontainer, Tag RFID, Berat VGM, Pemilik Barang, Nomor Segel, dan Slot Yard.

---

## 10. 🧪 Penjaminan Kualitas (QA), Postman Testing, & Standar Mutu GS1

1. **Postman Automated Testing Suite:**  
   Seluruh endpoint diuji menggunakan skrip asersi otomatis:
   ```javascript
   pm.test("Status code is 200 OK", function () {
       pm.response.to.have.status(200);
   });
   pm.test("Response time is under 150ms", function () {
       pm.expect(pm.response.responseTime).to.be.below(150);
   });
   pm.test("Billing charge event generated", function () {
       var jsonData = pm.response.json();
       pm.expect(jsonData.billing.amount).to.be.above(0);
   });
   ```
2. **Kepatuhan Standar Penomoran ISO 6346 (Modulo 11):**  
   Nomor kontainer 11 karakter (contoh: `MSKU9182374`) divalidasi menggunakan rumus matematis bobot eksponensial $2^n$ Modulo 11 untuk mencegah kesalahan pengetikan supir di gerbang.
3. **Standar Barcode GS1-128 & SSCC-18:**  
   Software mendukung pembacaan kode Application Identifier (AI):
   - `(00)`: Serial Shipping Container Code (SSCC-18) untuk identitas unit palet.
   - `(10)`: Nomor Lot / Batch produksi.
   - `(17)`: Tanggal Kedaluwarsa (*Expiration Date*) muatan rantai dingin.

---

## 11. 🏆 Evaluasi Komparatif Sistem (Benchmarking)

| Parameter Evaluasi | CIDP YMS (Inovasi Konsultan) | Navis N4 (Standard Port Global) | Solvo.TOS (Intermodal Rusia/Eropa) |
|---|---|---|---|
| **Arsitektur Sistem** | Web murni, Decoupled, Zero-Client Install | On-Premise Heavy Client / Java Hybrid | Client-Server On-Premise Monolitik |
| **Kesiapan Uji IoT** | Virtual Sandbox & Postman Ready | Memerlukan lisensi emulator mahal | Simulator terbatas pada lab tertutup |
| **Konektivitas Rel KA** | Native sensor Frauscher & SAKA KAI | Modul add-on rail berlisensi tinggi | Native support standar rel CIS |
| **Kepatuhan Bea Cukai**| Terhubung spesifikasi CEISA 4.0 DJBC | Memerlukan bridging EDI kustom | Memerlukan pihak ketiga |
| **Integrasi ERP Odoo** | Event-Driven Webhook langsung ke Odoo | Terhubung ke SAP via middleware IBM MQ | Konektor kustom akuntansi lokal |
| **Biaya Kepemilikan (TCO)**| Sangat Efisien, Bebas Royalti Asing | Sangat Mahal ($500k - $2M+ per terminal) | Tinggi ($250k - $800k per terminal) |

---

## 12. 🏁 Kesimpulan Teknis

Perancangan perangkat lunak **Conclusion Intermodal Dry Port (CIDP) YMS** membuktikan bahwa kemandirian teknologi logistik nasional dapat dicapai secara elegan dan berstandar internasional. Melalui penerapan arsitektur decoupled, ingestion multi-protokol, algoritma penumpukan cerdas, dan integrasi finansial ERP Odoo, sistem ini menjawab tuntas seluruh sasaran pembelajaran **Topik 7 Silabus Teknologi dan Perangkat Lunak Logistik ITL Trisakti**.

---
*Disahkan oleh **Lead System Architect (Zulfikar Jafarudin Fatah)** dan **Software Specialist (Afriansayah Ayubi)** — Conclusion Supply Chain Consultant (2026).*
