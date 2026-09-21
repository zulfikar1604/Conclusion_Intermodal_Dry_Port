# 📘 PANDUAN UTAMA PROYEK: CIDP YARD MANAGEMENT SYSTEM (YMS)
### Single Source of Truth (SSOT) untuk Anggota Tim & AI Assistant
> **PENTING UNTUK DEVELOPER, ANGGOTA TIM, MAUPUN AI:**  
> **BACA DOKUMEN INI TERLEBIH DAHULU** sebelum melakukan penambahan kode, modifikasi modul, atau integrasi hardware/API. Dokumen ini merangkum seluruh kesepakatan arsitektur, ruang lingkup resmi (Scope of Work), pembagian tugas tim, skema basis data, dan mekanisme integrasi hardware-software berbasis simulasi.

---

## 1. 🎯 Identitas & Visi Proyek

* **Identitas Konsultan:** Conclusion Supply Chain Consultant
* **Institusi:** Institut Transportasi dan Logistik (ITL) Trisakti — Semester 5
* **Mata Kuliah:** Teknologi dan Perangkat Lunak Logistik
* **Topik Utama:** **Topik 7 — Inland Container Depot (ICD) & Dry Port Management**
* **Studi Kasus:** Prototype Sistem Manajemen Lapangan Penumpukan (*Yard Management System / YMS*) pada **Conclusion Intermodal Dry Port (CIDP)**.

---

## 2. 📜 Scope of Work Resmi (Materi Kuliah & Panduan Dosen)

Berdasarkan silabus dan lembar penugasan resmi proyek:
> **Scope Wajib:**
> 1. **Yard Management System (YMS):** Manajemen slot penumpukan lapangan, dwell time, dan visualisasi aktivitas kargo.
> 2. **Integrasi GPS Reach Stacker:** Pelacakan posisi armada alat berat di area penumpukan kontainer secara real-time.
> 3. **RFID Tagging Kontainer:** Identifikasi otomatis kontainer (standar RFID UHF EPC Gen2 / ISO 18000-6C) saat masuk gerbang dan verifikasi di lapangan.
> 4. **Integrasi Data Moda Truk – Kereta Api Logistik:** Konektivitas intermodal antarmoda jalan raya (Gate-In/Out armada truk) dengan jalur rel sepur simpan (*Rail Siding*) KA Logistik.
> 
> **Fokus Utama Simulasi (Kunci Penilaian Dosen):**
> **"Pembaruan posisi stacking kontainer di area yard saat dipindahkan oleh operator Reach Stacker."**

---

## 3. 👥 Struktur Tim & Pembagian Tanggung Jawab

Setiap anggota memiliki batas wilayah tanggung jawab yang jelas agar tidak tumpang tindih (*conflict of work*):

| No | Nama & NIM | Peran Utama | Lingkup Pekerjaan & Tanggung Jawab | Status di Web |
|---|---|---|---|---|
| 1 | **Zulfikar Jafarudin Fatah** (2344190003) | **Lead Project & System Architect** | • Arsitektur sistem web, basis data MySQL, dan auth satu pintu.<br>• Dashboard Eksekutif (Beranda, KPI, Chart Analytics).<br>• Modul mandiri **Pelacakan Kontainer** (`pages/kontainer.php`).<br>• Modul mandiri **Manajemen Trucking** (`pages/trucking.php`).<br>• Modul mandiri **Lokasi Alat Berat GPS** (`pages/alat.php`). | ✅ **Selesai & Aktif** |
| 2 | **Juan Manuel** (2344190013) | **Yard Layout Specialist** | • Menggambar sketsa fisik denah terminal CIDP (35 Ha): Blok A-E, Jalur Rel, Dermaga Reefer, DG Yard, Depo M&R.<br>• Merumuskan aturan penataan (*Stacking Rules* Bay-Row-Tier). | ⏳ Menunggu Gambar Denah |
| 3 | **Muhammad Arman** (2344190012) | **Hardware & Automation Integrator** | • Memasukkan gambar denah dari Juan ke prototipe web.<br>• Spesifikasi teknis sensor: Kamera ANPR, OCR ISO 6346, Jembatan Timbang VGM 80 Ton, RFID UHF Reader.<br>• Integrasi telemetri nirkabel IoT pemantauan steker reefer. | ⏳ Menunggu Integrasi Hardware |
| 4 | **Muhammad Daffa Al Hafizh** (2344190001) | **Business Process & ERP Analyst** | • Menyusun Standard Operating Procedure (SOP) alur alih muat KA-Truk.<br>• Perancangan struktur tarif jasa terminal (Lo-Lo, Lift-On, Storage Dwell, Timbang VGM).<br>• Perancangan integrasi data komersial/keuangan ke sistem ERP. | ⏳ Menunggu Alur ERP |
| 5 | **Muhammad Bagoes Syahputra** (2344190022) | **Logistics Data & Standards Officer** | • Penerapan standar identifikasi logistik global (GS1-128, SSCC-18, e-Labeling).<br>• Perumusan skema data JSON API untuk integrasi sistem eksternal. | ⏳ Menunggu Standar Data |

> [!NOTE]
> Menu yang masih dalam tanggung jawab rekan tim (Denah, Gate, Yard, Intermodal, Reefer, Billing, Scanner, Settings) saat ini menampilkan **Kartu Status Kolaboratif** (*"Menunggu Input Rekan Tim"*) agar sistem tetap rapi, profesional, dan tidak menampilkan kode pura-pura/mentah sebelum ada input resmi dari anggota bersangkutan.

---

## 4. 🧠 Konsep Inti: Bagaimana Hardware & Software Terhubung dalam Simulasi?

### A. Alur Dunia Nyata (Real-Life Industrial IoT)
Di pelabuhan kering nyata, data bergerak melalui rantai peristiwa:
```
[ SENSOR FISIK ] ➔ [ IOT GATEWAY / EDGE CONTROLLER ] ➔ [ REST API (JSON) ] ➔ [ YMS DATABASE ]
```
1. **Gate Masuk:** Sensor loop mendeteksi truk ➔ Kamera ANPR membaca plat nomor ➔ Kamera OCR membaca ISO kontainer ➔ Antena RFID UHF membaca tag ➔ Indikator timbangan load cell mengirim berat kotor ➔ Gateway gerbang mengirim `POST /api/gate/inbound` ➔ Barrier terbuka otomatis & tiket e-Gate tercetak.
2. **Penumpukan di Yard:** Operator Reach Stacker mengunci spreader ke kontainer (sensor *Twistlock Locked*) ➔ RFID di spreader memverifikasi nomor box ➔ GPS DGPS membaca koordinat fisik boom ➔ Operator menaruh box di slot baru (sensor *Twistlock Unlocked*) ➔ Tablet kabin (VMT) mengirim `POST /api/yard/relocate` ➔ Posisi 3D di YMS ter-update seketika.

### B. Implementasi pada Prototipe Kita (Virtual Hardware Mocking)
Karena dalam lingkungan akademik perangkat keras fisik (antena UHF 860 MHz, timbangan 80 Ton, crane hidrolik) belum terpasang fisik di kelas:
* Kita menerapkan prinsip **Decoupling (Pemisahan Lapisan)** standar industri.
* Software YMS dibangun menggunakan **REST API & JSON Payload standar**.
* Disediakan **Virtual Simulation Trigger (Aksi Berbasis Peran)** di web UI:
  - Tombol aksi simulasi gerbang mengirimkan payload yang formatnya **100% sama** dengan hardware aslinya.
  - Begitu perangkat keras fisik siap, hardware gateway tinggal menembak endpoint API yang sama **tanpa perlu mengubah kode backend YMS sedikit pun**.

---

## 5. 🗄️ Arsitektur Basis Data (`cidp_yms`)

Koneksi database dikelola melalui file central [`connection.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/connection.php) menggunakan PDO MySQL.

### Tabel Utama:
1. **`containers`**: Data master kontainer di terminal
   - `id`, `container_number` (ISO 6346), `iso_code` (42G1, 22G1, 42R1), `size_type`, `cargo_type` (`dry`, `reefer`, `dg`, `empty`), `rfid_tag`, `sscc_code`, `gross_weight_kg`, `owner_company`, `seal_number`, `customs_status` (`SPPB_CLEARED`, `INSPECTION_REQUIRED`), `block`, `bay`, `row`, `tier`, `gate_in_time`, `status` (`in_yard`, `in_transit`, `gate_out`, `on_rail`).
2. **`trucks`**: Data arus armada pengangkut
   - `id`, `license_plate`, `rfid_tag`, `driver_name`, `company`, `container_number`, `status` (`queuing`, `at_gate`, `in_yard`, `loading`, `gate_out`), `gate_in_time`, `gate_out_time`.
3. **`equipment`**: Data armada alat berat
   - `id`, `equipment_id` (`RS-01`, `RS-02`, `RS-03`, `RTG-01`), `equipment_type`, `brand_model`, `operator_name`, `operator_id`, `status` (`idle`, `operating`, `carrying`, `maintenance`), `gps_x`, `gps_y`, `current_container`, `last_block`, `fuel_percent`, `hours_today`.
4. **`yard_events`**: Audit log pemindahan kargo
   - `id`, `event_type` (`GATE_IN`, `LIFT_OFF`, `LIFT_ON`, `RELOCATION`, `RAIL_LOAD`, `GATE_OUT`), `container_number`, `equipment_id`, `from_block`, `from_bay`, `from_row`, `from_tier`, `to_block`, `to_bay`, `to_row`, `to_tier`, `operator_name`, `billable_amount`, `notes`, `created_at`.
5. **`trains`**: Rangkaian kereta api logistik intermodal
   - `id`, `train_code`, `origin`, `destination`, `total_wagons`, `loaded_wagons`, `status` (`scheduled`, `arrived`, `loading`, `departed`), `arrival_time`, `departure_time`.
6. **`login`**: Kredensial autentikasi pengguna.

---

## 6. 🎨 Pedoman UI/UX & Standar Desain

Agar aplikasi tetap berkelas konsultan logistik modern (*enterprise level* seperti Flexport, Portbase, atau Navis N4):

1. **Prinsip Tampilan Tabel (Clean & Spacious):**
   > [!IMPORTANT]
   > **JANGAN MEMASUKKAN TERLALU BANYAK TEKS DALAM SATU SEL TABEL.**
   > - Setiap baris tabel harus ringkas dan rapi (1 baris per kolom, tidak boleh ada 3-4 baris teks menumpuk).
   > - Tampilkan data inti saja di baris tabel.
   > - Jika pengguna/penguji ingin melihat rincian lengkap, mereka cukup **mengeklik baris atau tombol "Detail & Milestone"** untuk membuka modal interaktif.

2. **Tipografi & Warna Brand:**
   - **Font:** Plus Jakarta Sans (`font-family: 'Plus Jakarta Sans', sans-serif`)
   - **Ikon:** FontAwesome 6 Free Solid & Regular
   - **Warna Identitas CIDP:**
     - `cdp-navy`: `#004b87` (Aksen navigasi & tombol utama)
     - `cdp-dark`: `#002f5e` (Background sidebar & header brand)
     - `cdp-blue`: `#0170b9` (Link aktif & kartu sorotan)
     - `cdp-light`: `#f4f8fc` (Background canvas aplikasi)

3. **Autentikasi Satu Pintu (*Single-Door Access*):**
   - Mode simulasi menggunakan akun konsultan superadmin: `admin@cidp.ac.id` / `admin123`.
   - Tersedia tombol cepat **1-Klik Masuk** di `login.php` untuk mempermudah demonstrasi di depan dosen tanpa kerumitan mengetik kredensial.

---

## 7. 📁 Struktur File & Direktori Proyek

```
Conclusion_Intermodal_Dry_Port/
├── PANDUAN_PROYEK_CIDP.md    <-- DOKUMEN INI (Single Source of Truth)
├── README.md                 <-- Ringkasan publik repositori GitHub
├── connection.php            <-- Koneksi basis data MySQL PDO terpusat
├── dashboard.php             <-- Shell utama dashboard, routing halaman, & menu navigasi
├── index.php                 <-- Landing page publik & etalase proyek CIDP
├── login.php                 <-- Halaman login satu pintu (Single-Door Access)
├── logout.php                <-- Handler penghancur sesi login
├── cidp.sql                  <-- File SQL seed database awal
├── pages/                    <-- MODUL HALAMAN OPERASIONAL MANDIRI
│   ├── kontainer.php         <-- Pelacakan Kontainer, 3D Yard Coordinate & 5-Step Journey Modal
│   ├── trucking.php          <-- Manajemen Armada Truk, Gate Traffic, & e-Gate Pass Modal
│   └── alat.php              <-- Monitoring Radar Lokasi Alat Berat & Audit Log Pemindahan
├── Materi/                   <-- Dokumen PDF silabus & materi kuliah ITL Trisakti
└── assets/                   <-- File CSS, JS pendukung, dan logo CIDP
```

---

## 8. 🚀 Alur Kerja Jika Anggota Tim Ingin Menambahkan Modul Baru

Jika **Juan, Arman, Daffa, atau Bagoes** (atau AI yang bertugas) hendak mengaktifkan modul yang sebelumnya berstatus "Menunggu Input Rekan Tim":

1. **Buat file modul baru** di dalam folder `pages/` (misal: `pages/denah.php`, `pages/gate.php`, dll.).
2. **Gunakan koneksi PDO central**:
   ```php
   require_once __DIR__ . '/../connection.php';
   ```
3. **Buka file [`dashboard.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/dashboard.php)** dan tambahkan rute `elseif` baru:
   ```php
   <?php elseif ($page === 'denah'): ?>
       <?php include_once __DIR__ . '/pages/denah.php'; ?>
   ```
4. **Patuhi aturan tabel bersih**: Tampilkan data ringkas di tabel, dan gunakan modal interaktif untuk rincian data mendalam.
5. **Verifikasi sintaksis**: Jalankan `php -l <nama_file>.php` sebelum melakukan `git commit`.

---

## 9. 💬 Cara Menjelaskan Sistem Ini Saat Sidang / Presentasi Dosen

Jika dosen bertanya:
> *"Bagaimana sistem YMS kalian bisa membuktikan pergerakan barang secara end-to-end tanpa ada hardware fisik di kelas?"*

**Jawaban Resmi Tim:**
> *"Sistem CIDP YMS mengadopsi arsitektur **Event-Driven Decoupled Architecture**. Setiap perpindahan kargo dari gerbang (Gate-In), penumpukan lapangan (Yard Stacking), hingga alih moda ke Kereta Api (Rail Siding) digerakkan oleh pemicu data standar (REST API JSON).
>
> Di lapangan sebenarnya, sinyal tersebut dikirimkan oleh IoT Gateway yang membaca sensor ANPR, RFID UHF, load cell jembatan timbang, dan twistlock spreader Reach Stacker. Dalam prototipe simulasi ini, kami membangun **Virtual Simulation Trigger Engine** dengan struktur data yang 100% identik. Sehingga kapan pun perangkat keras fisik diimplementasikan oleh tim infrastruktur, sistem YMS ini langsung siap beroperasi tanpa perubahan kode dasar."*

---
*Dokumen ini disusun dan disahkan oleh **Lead Project & System Architect (Zulfikar Jafarudin Fatah)** sebagai standar operasional pengembangan Conclusion Intermodal Dry Port (CIDP).*
