# 🖥️ PANDUAN PRAKTIK LIVE DEMO WEB & SKRIP PRESENTASI
## Skenario Demonstrasi Aplikasi Langsung di Depan Dosen Pengampu (Dr. Tigor Franky, S.T., M.T.)
**Proyek:** Conclusion Intermodal Dry Port (CIDP) 35 Ha — Yard Management System (YMS)  
**Entitas:** Conclusion Supply Chain Consultant — ITL Trisakti (2026)  
**Presenter:** Afriansayah Ayubi (*Software & ERP Specialist*) & Juan Gamaliel (*Data Integration Specialist*)  

---

## 🎯 Strategi Presentasi: Dual-Screen / Alt+Tab Harmonis
Bahan presentasi Anda telah dirancang dalam **8 Slide Ringkas** (`PRESENTASI_SOFTWARE_RINGKAS_CIDP.pptx`). Di bagian bawah setiap slide, terdapat banner kuning penanda: **`[LIVE DEMO WEB]`**.

Gunakan strategi **Alt+Tab** yang mulus antara file PowerPoint dan jendela browser Google Chrome / Edge yang sudah membuka:  
👉 `http://localhost/Conclusion_Intermodal_Dry_Port/dashboard.php`

---

## 🧭 Peta Alur Demo Berdasarkan Slide PPTX

```
[ SLIDE 1 & 2: PEMBUKAAN & 4 LAYER IT ] 
  └── Teori 4-Layer & 3 Aliran Logistik (Goods, Info, Financial)
  └── [DEMO 1]: Tunjukkan Struktur Sidebar 4 Kluster di Beranda Web
                      │
[ SLIDE 3 & 4: INGESTION 11 HARDWARE SIMULASI ]
  └── Arsitektur Decoupled REST API & Gateway
  └── [DEMO 2]: Buka Menu Gate (page=gate) ➔ Jalankan Simulasi 5-Tahap & Telemetri IoT
                      │
[ SLIDE 5: 3D YARD STACKING & COLLISION (INTI SILABUS) ]
  └── Aturan Penumpukan Bay-Row-Tier & Deteksi Tabrakan
  └── [DEMO 3]: Buka Menu Simulator 3D (page=simulator) ➔ Klik "Pindahkan Box (RS)" & 3D Raycasting
                      │
[ SLIDE 6: SOLAS VGM & DOKUMEN DIGITAL ]
  └── Formula Net VGM = Gross - Tare & Validasi <34t
  └── [DEMO 4]: Buka Menu Pelacakan Kontainer & Trucking (page=kontainer & page=trucking)
                      │
[ SLIDE 7: BUSINESS INTELLIGENCE & FINANSIAL ERP ODOO ]
  └── Dwell Time Heatmap, Okupansi Blok Yard, & Zero Leakage
  └── [DEMO 5]: Buka Beranda Eksekutif (page=beranda) ➔ Analitik Chart.js & Gauge Dwell
                      │
[ SLIDE 8: KESIMPULAN & PENUTUP ]
  └── Cheat Sheet & Tanya Jawab Dosen
```

---

## 🎬 Panduan Langkah Demi Langkah (Step-by-Step Walkthrough)

### 📍 TITIK DEMO 1 (Pada Slide 2 & 3)
* **Waktu Buka Web:** Setelah menjelaskan 3 aliran logistik dan 4-layer IT.
* **Menu yang Dibuka:** **Struktur Navigasi Sidebar** (`dashboard.php?page=beranda`)
* **Aksi yang Ditunjukkan:**
  1. Perlihatkan sidebar kiri berwarna navy gelap.
  2. Jelaskan kepada dosen bahwa sistem YMS ini dikelompokkan ke dalam **4 Kluster Divisi Profesional**:
     - *Kluster 1: Eksekutif & Visualisasi* (Beranda & Denah 35 Ha)
     - *Kluster 2: Operasional Utama (Core YMS)* (Kontainer, Trucking, Alat Berat GPS, Gate, Yard, Intermodal)
     - *Kluster 3: Fasilitas Khusus & Pabean* (Reefer, Bea Cukai, Scanner GS1)
     - *Kluster 4: Komersial & Sistem* (Billing ERP, Simulator 3D, Pengaturan Global)
* **Skrip Ucapan Anda ke Dosen:**
  > *"Bapak Dr. Tigor, seperti yang terlihat pada layar web kami, seluruh arsitektur navigasi YMS ini mengadopsi standar terminal maritim kelas dunia yang dipetakan ke dalam 4 kluster operasional, sehingga seluruh aliran informasi lapangan dapat dikendalikan secara rapi."*

---

### 📍 TITIK DEMO 2 (Pada Slide 4: Ingestion 11 Hardware)
* **Waktu Buka Web:** Saat menjelaskan bagaimana software menerima sinyal dari hardware gerbang.
* **Menu yang Dibuka:** **Manajemen Gate** (`dashboard.php?page=gate`)
* **Aksi yang Ditunjukkan:**
  1. **Tab 1 (Simulasi Alur Gate & Sensor):**
     - Arahkan kursor ke kartu **Pilih Skenario Truk Gate-In**.
     - Pilih **Truk 1: Lolos Normal (40ft High Cube)** dengan plat `B 9812 UIK`, kontainer `TCKU 829104-2`, dan berat 32.450 kg.
     - Klik tombol biru: **`[Jalankan Simulasi Sensor Gate In]`**.
     - Perhatikan bersama dosen progress bar 5-tahap berjalan otomatis:
       - *Tahap 1:* ANPR membaca plat `B 9812 UIK` (lampu hijau).
       - *Tahap 2:* OCR mendeteksi boks `TCKU 829104-2`.
       - *Tahap 3:* Timbangan 80t membaca bobot 32.450 kg dengan status `VERIFIED PASS (<34t)`.
       - *Tahap 4:* Edge PC memvalidasi manifest.
       - *Tahap 5:* Layar LED menampilkan arahan: `"MASUK BLOK B-04"`, palang otomatis berstatus `OPEN`, dan muncul **Digital e-Gate Pass berkode QR**.
     - Tunjukkan bahwa baris transaksi baru langsung otomatis ditambahkan di puncak **Tabel Log Transaksi Gerbang Terkini**!
  2. **Uji Skenario Kegagalan (Edge Case Overweight):**
     - Pilih **Truk 3: Overweight Alert (>34.000 kg)** seberat 37.200 kg.
     - Klik lagi tombol simulasi. Tunjukkan bahwa sistem secara cerdas menolak: status VGM menjadi **OVERLOAD ALERT**, palang tetap tertutup (*LOCKED*), dan LED menampilkan peringatan penolakan!
  3. **Tab 3 (Telemetri IoT Lapangan):**
     - Klik tombol tab **Telemetri IoT Lapangan**.
     - Tunjukkan pembacaan telemetri riil dari 4 kluster hardware:
       - *Reach Stacker 01:* Koordinat DGPS RTK `-6.284210, 107.142890`, status twistlock `LOCKED`, beban 28.520 kg.
       - *Reefer Yard:* 142 dari 300 colokan aktif, daya 852 kW, suhu rata-rata stabil -18.4°C.
       - *Rail Siding:* Kereta KA 2501 terdeteksi, hitungan gandar 120 Axles (30 gerbong datar / 60 TEU).
       - *Bea Cukai:* 18 unit Smart E-Seal aktif berstatus kawat utuh 0 tamper alert.
* **Skrip Ucapan Anda ke Dosen:**
  > *"Di sinilah bukti software kami bekerja sebagai Ingestion Gateway, Pak. Saat kami memilih skenario truk dan mengeklik jalankan simulasi, sistem mengeksekusi 5 tahap otomasi gerbang dalam waktu 28 detik. Dan bila truk melebihi batas SOLAS 34 ton, sistem secara otomatis menolak dan mengunci palang."*

---

### 📍 TITIK DEMO 3 (Pada Slide 5: Core 3D Yard Stacking Engine)
* **Waktu Buka Web:** Saat membahas fokus kunci evaluasi silabus: *pembaruan posisi stacking kontainer di area yard saat dipindahkan oleh Reach Stacker*.
* **Menu yang Dibuka:** **Panel Simulasi IoT 3D Virtual Terminal** (`dashboard.php?page=simulator`)
* **Aksi yang Ditunjukkan:**
  1. Tunjukkan kembaran digital (*Digital Twin*) 3D terminal 35 Ha yang merender tumpukan kontainer berkode warna ISO maritim (Biru=Dry, Putih=Reefer, Merah=DG, Abu-abu=Empty).
  2. **Rotasi Kamera & Preset Sudut Pandang:**
     - Klik tombol preset kamera di HUD atas: klik **🛰️ Drone 35 Ha**, lalu **🚧 Gate & VGM**, lalu **🚂 Rail Siding**, dan yang paling keren: klik **🕹️ Kabin RS (POV)** untuk melihat pandangan dari dalam kabin kemudi derek!
  3. **3D Raycasting Inspector (Klik Objek):**
     - Gunakan mouse untuk mengeklik sembarang boks kontainer di layar 3D.
     - Tunjukkan jendela pop-up inspeksi yang muncul melayang: menampilkan nomor ISO, berat timbangan VGM, tag RFID, dan slot tumpukan saat ini.
  4. **Eksekusi Pemindahan Kontainer (Aksi Utama Silabus):**
     - Klik tombol kuning di kanan atas: **`[Pindahkan Box (RS)]`**.
     - Di jendela modal, pilih kontainer target (misal `MSKU9182374`) dan tentukan slot tujuan baru: **Blok B - Bay 04 - Row 02 - Tier 03**.
     - Klik **Konfirmasi Relokasi**.
     - Tunjukkan kepada dosen:
       - Animasi derek Reach Stacker bergerak mengangkat boks dan memindahkannya ke slot baru via Three.js.
       - Muncul notifikasi sukses: *"Kontainer berhasil dipindahkan oleh RS-02 ke B-04-02-03"*.
       - Tagihan jasa Lo-Lo sebesar **Rp 250.000** langsung dicatat ke sistem!
* **Skrip Ucapan Anda ke Dosen:**
  > *"Ini adalah jawaban langsung atas evaluasi utama pada silabus Topik 7, Pak Tigor. Kami membangun Digital Twin berbasis Three.js WebGL. Saat tombol 'Pindahkan Box' diklik, software memvalidasi ketersediaan slot agar tidak terjadi tabrakan, menggerakkan animasi derek, memperbarui koordinat Bay-Row-Tier di basis data MySQL, dan seketika mencatat tagihan Lo-Lo Rp 250.000."*

---

### 📍 TITIK DEMO 4 (Pada Slide 6: Pelacakan Kontainer & Trucking)
* **Waktu Buka Web:** Saat menjelaskan data master kontainer, kepabeanan, dan e-Gate Pass.
* **Menu yang Dibuka:** **Pelacakan Kontainer** (`dashboard.php?page=kontainer`) & **Manajemen Trucking** (`page=trucking`)
* **Aksi yang Ditunjukkan:**
  1. Di halaman **Pelacakan Kontainer**:
     - Tunjukkan tabel bersih satu baris per kontainer (mematuhi kaidah UI/UX ringkas).
     - Perlihatkan kolom **Nomor Kontainer ISO 6346**, **Kode RFID UHF**, **Kode Barcode GS1 SSCC**, dan **Koordinat 3D Bay-Row-Tier**.
     - Tunjukkan status kepabeanan: ada yang berstatus `SPPB_CLEARED` dan ada yang transit.
  2. Buka halaman **Manajemen Trucking**:
     - Perlihatkan daftar truk mitra logistik lengkap dengan nomor polisi, supir, perusahaan, dan berat kotor jembatan timbang VGM.
     - Perlihatkan tombol cetak e-Gate Pass digital berkode QR hasil generasi engine `QRCode.js`.
* **Skrip Ucapan Anda ke Dosen:**
  > *"Pada halaman pelacakan kontainer dan trucking ini, kita dapat melihat integrasi standar internasional: nomor peti kemas divalidasi dengan algoritma ISO 6346 Modulo 11, dilengkapi standar barcode GS1 SSCC-18, dan kartu RFID UHF untuk memastikan ketertelusuran (traceability) kargo secara penuh."*

---

### 📍 TITIK DEMO 5 (Pada Slide 7: Business Intelligence & Finansial ERP)
* **Waktu Buka Web:** Saat menjelaskan bagaimana software mengubah data mentah menjadi keputusan bisnis dan integrasi ERP Odoo.
* **Menu yang Dibuka:** **Beranda Eksekutif** (`dashboard.php?page=beranda`)
* **Aksi yang Ditunjukkan:**
  1. Tunjukkan **6 Kartu Ringkasan KPI di bagian atas**:
     - Yard Stack: 126 boks
     - Truk Hari Ini: 14 unit (In: 9, Out: 5)
     - Kereta Api: 2 rangkaian aktif
     - Reefer Plug: 38 unit suhu aman
     - DG/IMO: 3 boks terisolasi
     - Gross Daily Revenue: **Rp 47.2 Juta**
  2. Tunjukkan **Dua Gauge Meter di Tengah Dasbor**:
     - **Utilisasi Yard (63%)**: Menghitung kapasitas keterisian 126 dari 200 slot aktif di lahan 35 Ha.
     - **Rata-rata Dwell Time (2.1 Hari)**: Tunjukkan jarum hijau yang membuktikan bahwa performa terminal berada di angka 2.1 hari, jauh di bawah target maksimal 3 hari!
  3. Tunjukkan **Grafik Analitik Chart.js**:
     - Tren aktivitas gerbang (In vs Out 7 hari terakhir).
     - Komposisi pendapatan harian berdasarkan jenis layanan (Jasa Lift-Off, Storage Inap, Timbang VGM, Reefer Plugging).
* **Skrip Ucapan Anda ke Dosen:**
  > *"Di dasbor Beranda Eksekutif ini, Bapak Dosen dapat melihat bagaimana jutaan paket data mentah dari sensor diolah menjadi kecerdasan bisnis (Business Intelligence). Kami menampilkan audit Dwell Time riil di angka 2.1 hari, okupansi yard 63%, dan pendapatan kotor harian Rp 47.2 Juta yang tersinkronisasi langsung dengan sistem akuntansi ERP Odoo tanpa ada kebocoran pendapatan (Zero Revenue Leakage)."*

---

## 💡 Tips Praktis Agar Presentasi Berjalan Sempurna:

1. **Persiapan Sebelum Tampil:**
   - Nyalakan XAMPP (Apache & MySQL harus berwarna hijau).
   - Buka browser Chrome pada alamat `http://localhost/Conclusion_Intermodal_Dry_Port/dashboard.php?page=beranda`.
   - Buka file PowerPoint `software/PRESENTASI_SOFTWARE_RINGKAS_CIDP.pptx` dalam mode Slide Show (F5).
   - Lakukan latihan transisi Alt+Tab 1-2 kali agar perpindahan layar dari slide ke browser tampak cepat dan profesional.
2. **Kunci Jawaban Saat Dosen Bertanya:**
   - *Tanya:* "Database apa yang dipakai?" ➔ *Jawab:* "MySQL InnoDB dengan akses PDO Prepared Statements dan transaksi ACID atomik."
   - *Tanya:* "Grafika 3D pakai apa?" ➔ *Jawab:* "Three.js WebGL murni pada 60 FPS dengan pustaka Tween.js untuk animasi derek dan Raycaster untuk deteksi klik boks."
   - *Tanya:* "Bagaimana membuktikan sistem terhubung ke hardware?" ➔ *Jawab:* "Melalui REST API Gateway pada file `api/simulator_action.php`. Struktur data JSON yang kami tembakkan 100% identik dengan data yang akan dikirimkan gateway fisik Advantech nantinya."

---
*Panduan ini disusun untuk menjamin nilai A maksimal pada presentasi mingguan mata kuliah Teknologi dan Perangkat Lunak Logistik ITL Trisakti.*
