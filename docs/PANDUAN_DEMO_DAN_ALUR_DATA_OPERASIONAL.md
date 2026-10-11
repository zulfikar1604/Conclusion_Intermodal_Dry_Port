# PANDUAN LENGKAP DEMO APLIKASI & ALUR PERGERAKAN DATA OPERASIONAL
## Conclusion Intermodal Dry Port (CIDP) 35 Ha — Yard Management System (YMS)

**Institusi:** Institut Transportasi dan Logistik (ITL) Trisakti  
**Program Studi:** Manajemen Transportasi Logistik  
**Mata Kuliah:** Teknologi dan Perangkat Lunak Logistik (Topik: ICD & Dry Port Management)  
**Dosen Pengampu:** Dr. Tigor Franky, S.T., M.T.  
**Entitas Konsultan:** Conclusion Supply Chain Consultant  
**Penyusun:** Zulfikar Jafarudin Fatah & Tim Pengembang CIDP  
**Versi Dokumen:** 2.0 (Executive High-Density UI Edition)  
**Tanggal Terbit:** Oktober 2026  

---

## DAFTAR ISI
1. [Filosofi Desain & Karakteristik Data Dry Port](#1-filosofi-desain--karakteristik-data-dry-port)
2. [Triad Aliran Utama: Fisik, Informasi, dan Finansial](#2-triad-aliran-utama-fisik-informasi-dan-finansial)
3. [Titik Awal Demo & Genesis of Data (Dari Mana Data Bergerak?)](#3-titik-awal-demo--genesis-of-data-dari-mana-data-bergerak)
4. [Skenario Utama Demo: Perjalanan 1 Peti Kemas End-to-End](#4-skenario-utama-demo-perjalanan-1-peti-kemas-end-to-end)
5. [Panduan Langkah-demi-Langkah Demo Layar (Screen-by-Screen Script)](#5-panduan-langkah-demi-langkah-demo-layar-screen-by-screen-script)
   - [Langkah 1: Beranda Eksekutif (Opening & Gambaran Fasilitas 35 Ha)](#langkah-1-beranda-eksekutif-opening--gambaran-fasilitas-35-ha)
   - [Langkah 2: Gerbang Masuk & AIDC Telemetry (Titik Awal Data Masuk)](#langkah-2-gerbang-masuk--aidc-telemetry-titik-awal-data-masuk)
   - [Langkah 3: Visualisasi Spasial Lapangan (Denah Terminal & Simulator 3D)](#langkah-3-visualisasi-spasial-lapangan-denah-terminal--simulator-3d)
   - [Langkah 4: Penataan Lapangan & Dispatching Alat Berat (Yard & RTG/RS)](#langkah-4-penataan-lapangan--dispatching-alat-berat-yard--rtgrs)
   - [Langkah 5: Pembuktian Langsung Scanner Optik & Validasi Check Digit](#langkah-5-pembuktian-langsung-scanner-optik--validasi-check-digit)
   - [Langkah 6: Keunggulan Intermodal KA Siding Pasoso ↔ CIDP](#langkah-6-keunggulan-intermodal-ka-siding-pasoso--cidp)
   - [Langkah 7: Klirens Kepabeanan & CEISA 4.0 DJBC (Jalur Hijau vs Merah)](#langkah-7-klirens-kepabeanan--ceisa-40-djbc-jalur-hijau-vs-merah)
   - [Langkah 8: Penutupan Siklus Finansial & Sinkronisasi ERP Billing](#langkah-8-penutupan-siklus-finansial--sinkronisasi-erp-billing)
6. [Matriks Perubahan Tabel Basis Data (Database Lifecycle State)](#6-matriks-perubahan-tabel-basis-data-database-lifecycle-state)
7. [Bank Pertanyaan Kritis Dosen Penguji & Jawaban Emas (Q&A Defense)](#7-bank-pertanyaan-kritis-dosen-penguji--jawaban-emas-qa-defense)

---

## 1. Filosofi Desain & Karakteristik Data Dry Port

Aplikasi **Conclusion Intermodal Dry Port (CIDP) 35 Ha** dirancang bukan sekadar aplikasi pencatatan administrasi gudang pasif, melainkan sebuah **Yard Management System (YMS) berbasis peristiwa nyata (*Event-Driven Architecture*)**.

Dalam logistik pelabuhan dan terminal intermodal modern, sebuah sistem perangkat lunak harus mencerminkan prinsip:
> *"No Manual Data Entry for Physical Movements. All movements are triggered by sensors, validated by business rules, and accounted for in the financial ledger."*

Artinya:
1. **Tidak ada operator yang mengetik manual** bahwa kontainer telah masuk atau berpindah. Semua dipicu oleh perangkat keras optik, RFID, jembatan timbang, dan GPS alat berat.
2. **Data bergerak searah dengan fisik barang**, menjamin integritas data audit trail sesuai standar kepabeanan (CEISA 4.0 DJBC) dan regulasi maritim internasional (SOLAS VGM IMO).
3. **Kepadatan Informasi Tinggi (*High Density Dashboard*)**: Seluruh modul telah dirancang ringkas menyerupai kokpit terminal eksekutif pelabuhan internasional.

---

## 2. Triad Aliran Utama: Fisik, Informasi, dan Finansial

Sebelum memulai demonstrasi, pahami segitiga keterhubungan yang selalu berjalan di dalam aplikasi CIDP:

```
┌────────────────────────────────────────────────────────────────────────┐
│ 1. ALIRAN FISIK & SENSOR (Physical & Hardware Layer)                    │
│ Truk, Kereta Api, Peti Kemas, Reach Stacker, Sensor, Kamera, Timbangan  │
└──────────────────────────────────┬─────────────────────────────────────┘
                                   │ Memicu Sinyal Telemetri (Trigger)
                                   ▼
┌────────────────────────────────────────────────────────────────────────┐
│ 2. ALIRAN INFORMASI & SOFTWARE (CIDP YMS Engine Layer)                 │
│ Ingesti Sensor, Validasi Aturan Bisnis, Pemutakhiran Database MySQL    │
└──────────────────────────────────┬─────────────────────────────────────┘
                                   │ Memicu Transaksi Finansial (Billing)
                                   ▼
┌────────────────────────────────────────────────────────────────────────┐
│ 3. ALIRAN FINANSIAL & KOMERSIAL (Billing ERP & Ledger Layer)            │
│ Tarif Gate Pass, Jasa Lo-Lo, VGM SOLAS, Plugging Listrik, Haulage KA   │
└────────────────────────────────────────────────────────────────────────┘
```

Setiap detik pergerakan fisik yang ditangkap perangkat keras akan mengubah status di database YMS, yang seketika membentuk pos piutang (*billing ledger entry*) pada modul keuangan terminal.

---

## 3. Titik Awal Demo & Genesis of Data (Dari Mana Data Bergerak?)

Bila dosen atau penguji bertanya: **"Dari mana data berawal di aplikasi ini?"**, berikut adalah pemahaman fundamentalnya:

### A. Tahap 0: Pra-Kondisi Data (Pre-Advise / Master Data)
Sebelum truk atau kereta api tiba secara fisik di gerbang, data awal telah tersedia di sistem melalui pertukaran data elektronik (EDI / API) berupa:
- **Delivery Order (DO)** untuk impor atau **Shipping Order (SO)** untuk ekspor.
- **Manifest Pelayaran / Kereta Api** (daftar nomor kontainer yang dijadwalkan tiba).
- Data ini berada dalam status **`SCHEDULED`** atau **`QUEUING`** (antrian pra-gerbang).

### B. Tahap 1: Titik Kelahiran Data Nyata (*Genesis Event*)
Pergerakan data operasional aktif **pertama kali tercipta di GERBANG MASUK (Gate-In)** saat ban depan truk menyentuh sensor tanam aspal (*Inductive Loop Detector*).

Detik itu juga terjadi sinkronisasi 4 instrumen telemetri secara simultan:
1. **Loop Detector** mendeteksi kehadiran kendaraan fisik dan menyalakan kamera.
2. **Kamera ANPR (HW-01)** menangkap plat nomor truk (misal: `B 9182 TE`) dengan akurasi 99.4%.
3. **Kamera Portal OCR (HW-02)** membaca nomor peti kemas ISO 6346 (misal: `MSKU9182374`) dan kode tipe ukuran (`42G1`).
4. **Jembatan Timbang 80T (HW-03)** mengukur bobot kotor (Gross Weight) dan memverifikasi sertifikasi SOLAS VGM.
5. **Edge PC Advantech (HW-06)** memvalidasi data ke database MySQL:
   - Status truk berubah dari `queuing` $\rightarrow$ `at_gate` $\rightarrow$ `in_yard`.
   - Status kontainer berubah menjadi `in_yard`.
   - Tercatat log riwayat pada tabel `yard_events` dengan tipe `GATE_IN`.
   - Membuka palang otomatis dan menerbitkan tiket tugas (*Gate Pass*).
   - Menginisiasi tagihan *Gate Pass Fee* (Rp 50.000) dan *VGM Weighing Fee* (Rp 150.000) di modul Billing.

---

## 4. Skenario Utama Demo: Perjalanan 1 Peti Kemas End-to-End

Untuk mendemonstrasikan aplikasi secara terstruktur dalam waktu **8 hingga 10 menit**, gunakan alur **Skenario Tunggal Peti Kemas**:

```
[Gerbang Masuk] ──► [Penumpukan Yard] ──► [Bea Cukai] ──► [KA Siding] ──► [Faktur Billing]
  (Gate-In ANPR)      (Reach Stacker)     (CEISA 4.0)     (Intermodal)      (Odoo ERP)
```

- **Nomor Peti Kemas Rekomendasi:** `MSKU9182374` (Tipe: 40ft High Cube Dry).
- **Armada Truk Pembawa:** Plat `B 9182 TE` (Sopir: Soleh Marzuki, PT Trans Logistik Prima).
- **Misi:** Impor Pelabuhan Tanjung Priok $\rightarrow$ Alih muat ke Kereta Api Siding CIDP Hub.

---

## 5. Panduan Langkah-demi-Langkah Demo Layar (Screen-by-Screen Script)

Berikut adalah panduan praktis layar per layar lengkap dengan **tindakan klik**, **narasi bicara**, dan **poin kunci yang harus ditonjolkan**:

---

### Langkah 1: Beranda Eksekutif (Opening & Gambaran Fasilitas 35 Ha)
* **URL Layar:** `dashboard.php?page=beranda`
* **Alokasi Waktu:** 1 Menit
* **Aksi Pengguna:**
  1. Arahkan mouse ke kartu status SCADA dan jam operasional real-time di header.
  2. Tunjukkan **Pusat Kendali Sensor & IoT Workbench Lapangan** (4 shortcut cepat: Gate ANPR, CFS Rampa, Reefer Terminal, Denah 35 Ha).
  3. Sorot 6 kartu KPI ringkas di bawahnya (Okupansi Yard, Gate TAT, Siding KA, Reefer, CFS, Pabean).
* **Naskah Pembicara:**
  > *"Selamat pagi/siang Bapak/Ibu dosen penguji. Selamat datang di presentasi sistem operasional Conclusion Intermodal Dry Port (CIDP) seluas 35 Hektar di Cikarang.*  
  > *Sistem yang kami kembangkan adalah Yard Management System (YMS) generasi baru yang mengintegrasikan 26 perangkat keras telemetri IoT, koneksi rel kereta api pelabuhan, klirens Bea Cukai CEISA 4.0, dan penagihan otomatis.*  
  > *Di halaman Beranda Eksekutif ini, manajemen dapat memantau seluruh performa terminal secara real-time dengan latensi di bawah 150 milidetik. Sekarang, mari kita telusuri bagaimana data peti kemas bergerak dari titik paling awal di gerbang masuk."*

---

### Langkah 2: Gerbang Masuk & AIDC Telemetry (Titik Awal Data Masuk)
* **URL Layar:** `dashboard.php?page=gate` (atau `dashboard.php?page=trucking`)
* **Alokasi Waktu:** 2 Menit
* **Aksi Pengguna:**
  1. Klik menu **Gate & Timbangan** di sidebar kiri.
  2. Perlihatkan 4 kartu metrik gerbang (Arus Truk/Jam, Turnaround Time 18.2 menit, Sertifikasi VGM SOLAS 80T, Okupansi Buffer).
  3. Tunjukkan tab navigasi lajur (Lajur Inbound 1 & 2, Lajur Outbound 1 & 2).
  4. Buka baris tabel truk `B 9182 TE`, klik tombol **Milestone** atau baris tabel untuk memunculkan modal Gate Pass Interaktif.
* **Naskah Pembicara:**
  > *"Ini adalah modul Gerbang Masuk, tempat data pertama kali tercipta. Saat truk kontainer tiba di Lajur 1:*  
  > *Pertama, sensor loop detector mendeteksi kendaraan. Kamera ANPR membaca plat nomor truk B 9182 TE secara otomatis tanpa input manusia.*  
  > *Kedua, portal optik OCR membaca nomor kontainer MSKU9182374 dan mencocokkannya dengan Delivery Order pelayaran.*  
  > *Ketiga, jembatan timbang 80 Ton mengukur berat kotor armada (34.200 kg) dan langsung menerbitkan sertifikasi SOLAS VGM yang wajib secara hukum maritim internasional.*  
  > *Sistem kemudian membuka palang otomatis, mengubah status armada menjadi 'Di Lapangan', dan mencatat tiket elektronik yang berisi rute blok tujuan penumpukan."*

---

### Langkah 3: Visualisasi Spasial Lapangan (Denah Terminal & Simulator 3D)
* **URL Layar:** `dashboard.php?page=denah`
* **Alokasi Waktu:** 1.5 Menit
* **Aksi Pengguna:**
  1. Klik menu **Denah Terminal** di sidebar.
  2. Tunjukkan gambar site plan master plan teknik sipil arsitektural (latar kertas terang bergrid teknik).
  3. Gunakan kontrol zoom terapung di pojok kanan peta: klik **[+] Zoom In**, geser (pan) denah untuk memperlihatkan detail Blok A, B, C, Dermaga Reefer, Jalur Rel Siding, dan Gudang CFS.
  4. Klik salah satu pin perangkat keras (misal: Pin HW-01 ANPR atau HW-10 Axle Counter) untuk memunculkan panel Inspector rincian teknis alat di sebelah kanan.
* **Naskah Pembicara:**
  > *"Untuk memantau sebaran fisik di lahan 35 Ha, sistem menyediakan Denah Terminal Arsitektural berbasis SVG interaktif dengan kontrol zoom dan pan presisi.*  
  > *Di denah ini, kita dapat melihat posisi 26 perangkat keras yang tersebar di 6 zona operasional. Setiap titik memiliki status operasional langsung, mulai dari kamera ANPR di pintu depan, steker reefer di sisi timur, hingga sensor gandar kereta api di sisi selatan."*

---

### Langkah 4: Penataan Lapangan & Dispatching Alat Berat (Yard & RTG/RS)
* **URL Layar:** `dashboard.php?page=yard` dilanjutkan `dashboard.php?page=alat`
* **Alokasi Waktu:** 2 Menit
* **Aksi Pengguna:**
  1. Klik menu **Lapangan (Yard)**: perlihatkan okupansi TEU per blok (Blok A untuk Dry 40ft, Blok B untuk Dry 20ft, Blok C, Reefer, dan Blok E Behandle).
  2. Pindah ke menu **Alat Berat**: tunjukkan **Peta Skematik Radar Live Alat Berat Terminal**.
  3. Perlihatkan unit **RS-01** (Blok A), **RS-02** (Blok B - sedang mengangkut kontainer), **RS-03** (Zona Reefer), dan **RTG-01** (Siaga di Rel Siding).
  4. Klik tombol **+ Dispatch Job** untuk memperlihatkan bagaimana supervisor menugaskan alat berat memindahkan peti kemas dari chasis truk ke tumpukan slot tertentu (*Bay-Row-Tier*).
* **Naskah Pembicara:**
  > *"Setelah melewati gerbang, truk diarahkan ke Blok penumpukan. Modul Yard dan Alat Berat mengatur pergerakan internal lapangan.*  
  > *Di layar ini, kita dapat memantau lokasi real-time Reach Stacker dan derek RTG melalui telemetri DGPS/RTK berakurasi sentimeter.*  
  > *Saat kontainer diturunkan (Lift-Off) dari truk ke Blok B slot Bay 08 - Row 03 - Tier 02, sensor twistlock spreader pada Reach Stacker mengunci koordinat tersebut ke basis data YMS, sekaligus memicu pencatatan biaya Lift-Off di sistem akuntansi."*

---

### Langkah 5: Pembuktian Langsung Scanner Optik & Validasi Check Digit
* **URL Layar:** `dashboard.php?page=scanner`
* **Alokasi Waktu:** 1.5 Menit
* **Aksi Pengguna:**
  1. Klik menu **Scanner Optik** di sidebar.
  2. Tunjukkan antarmuka scanner optik yang mendukung 3 mode: Kamera Langsung (Webcam), Unggah Gambar/Dokumen, dan **Uji Sampel Instan**.
  3. Klik tab **"Uji Sampel Instan"**.
  4. Klik kartu sampel pertama: **Peti Kemas Standar ISO 6346 (`MSKU9182374`)**.
  5. Perlihatkan kartu inspeksi optik sebelah kanan seketika terisi otomatis:
     - Tipe barcode terdeteksi (Code 128 / ISO 6346).
     - Validasi check digit ISO: **VALID (Check Digit = 4)**.
     - Status kepemilikan pelayaran (Maersk Line).
     - Status verifikasi database YMS CIDP (Cocok dengan data lapangan).
* **Naskah Pembicara:**
  > *"Untuk membuktikan integrasi pemindaian optik di lapangan, kami menyediakan modul Scanner Optik cerdas.*  
  > *Sistem ini mengimplementasikan algoritma verifikasi Check Digit ISO 6346 secara matematis. Ketika barcode kontainer dipindai, algoritma menghitung bobot numerik tiap karakter untuk memastikan nomor boks tidak dipalsukan.*  
  > *Hanya dalam 42 milidetik, sistem memverifikasi bahwa peti kemas MSKU9182374 valid, terdaftar resmi di manifes pelayaran, dan posisinya di lapangan langsung teridentifikasi."*

---

### Langkah 6: Keunggulan Intermodal KA Siding Pasoso ↔ CIDP
* **URL Layar:** `dashboard.php?page=intermodal`
* **Alokasi Waktu:** 2 Menit
* **Aksi Pengguna:**
  1. Klik menu **Intermodal KA** di sidebar.
  2. Perlihatkan 4 kartu metrik utama:
     - Rangkaian KA 2-Arah Aktif: KA 2518 (Inbound Impor) & KA 2519 (Outbound Ekspor).
     - Muatan Siding TEU: 60/60 TEU (Load factor 100%).
     - **Sensor Gandeng Frauscher SIL 4**: 252 As Roda terdeteksi.
     - Target Waktu Alih Muat (TAT): 54 menit.
  3. Perlihatkan tabel jadwal reguler 24-jam shuttle bolak-balik antara Stasiun Pasoso (Pelabuhan Tanjung Priok) dan CIDP Cikarang.
  4. Perlihatkan pelacak spasial koridor rel Daop 1 Jakarta sepanjang 54.8 KM.
* **Naskah Pembicara:**
  > *"Inilah diferensiasi fundamental CIDP yang membedakannya dari depo kontainer konvensional: Fasilitas Intermodal Rail Siding ganda (2 x 450 meter).*  
  > *Kereta api kontainer menghubungkan langsung Pelabuhan Tanjung Priok dengan kawasan industri Cikarang tanpa terkena kemacetan Tol Jakarta-Cikampek.*  
  > *Di rel simpang ini, sensor Frauscher Wheel Sensor menghitung tepat 126 as roda per rangkaian 30 gerbong datar PPCW 42 Ton.*  
  > *Derek RTG bertenaga listrik melakukan transshipment alih muat dari yard ke atas gerbong kereta, memotong emisi karbon hingga 76% dibandingkan truk jalan raya."*

---

### Langkah 7: Klirens Kepabeanan & CEISA 4.0 DJBC (Jalur Hijau vs Merah)
* **URL Layar:** `dashboard.php?page=customs`
* **Alokasi Waktu:** 1.5 Menit
* **Aksi Pengguna:**
  1. Klik menu **Kepabeanan** di sidebar.
  2. Perlihatkan koneksi REST API v1.4 CEISA 4.0 DJBC (KPPBC TMP Cikarang).
  3. Tunjukkan tabel dokumen PIB/PEB:
     - Dokumen Jalur Hijau: SPPB otomatis terbit (*Auto Release < 3.2 detik*) untuk importir terakreditasi AEO / MITA.
     - Dokumen Jalur Merah: Memerlukan pemeriksaan fisik.
  4. Tunjukkan tombol aksi interaktif: Anda dapat mendemonstrasikan penerbitan SPPB untuk Jalur Hijau atau menahan kontainer ke Jalur Merah (Terminal Behandle Blok E).
* **Naskah Pembicara:**
  > *"Sebagai Tempat Penimbunan Pabean resmi, sistem CIDP terintegrasi langsung dengan platform CEISA 4.0 Bea Cukai.*  
  > *Kargo impor dari importir bersertifikasi AEO otomatis memperoleh status Jalur Hijau dengan penerbitan dokumen SPPB elektronik.*  
  > *Namun jika sistem profil risiko DJBC menetapkan Jalur Merah, sistem YMS secara otomatis mengunci palang keluar Gate Out dan mengalihkan peti kemas ke Blok E untuk pemeriksaan fisik di rampa Behandle dan pemindai Gantry X-Ray 6 MeV."*

---

### Langkah 8: Penutupan Siklus Finansial & Sinkronisasi ERP Billing
* **URL Layar:** `dashboard.php?page=billing`
* **Alokasi Waktu:** 1.5 Menit
* **Aksi Pengguna:**
  1. Klik menu **Billing & Keuangan** di sidebar.
  2. Perlihatkan 4 kartu ringkasan omzet, piutang operasional, dan aging invoice.
  3. Buka tab **Faktur & Penagihan**: cari nomor kontainer `MSKU9182374`.
  4. Tunjukkan rincian komponen biaya yang dikalkulasi secara transparan:
     - Biaya Pas Masuk Truk (Gate Pass): Rp 50.000
     - Sertifikasi Timbang SOLAS VGM: Rp 150.000
     - Jasa Bongkar Muat (Lift-Off): Rp 250.000
     - Penumpukan Masa 1 (Free Time 3 Hari): Rp 0
     - Total Tagihan Faktur: Rp 450.000 + PPN 11%
  5. Tunjukkan status sinkronisasi payload JSON ke sistem ERP Odoo / SAP via XML-RPC.
* **Naskah Pembicara:**
  > *"Seluruh rangkaian aktivitas operasional yang kita saksikan tadi bermuara pada modul penagihan (Billing).*  
  > *Tidak ada kalkulasi manual. Sistem mengagregasikan biaya gerbang, penimbangan VGM, jasa crane lift-off, dan masa penumpukan dwell time secara otomatis menjadi satu faktur proforma yang sah.*  
  > *Faktur ini langsung disinkronkan ke ERP Odoo keuangan terminal. Begitu pembayaran lunas dan status kepabeanan SPPB terverifikasi, sistem menerbitkan Surat Pengeluaran Peti Kemas (SP2B) dan mengizinkan truk melakukan Gate-Out.*  
  > *Dengan demikian, siklus data dan fisik barang tertutup secara sempurna dari hulu ke hilir."*

---

## 6. Matriks Perubahan Tabel Basis Data (Database Lifecycle State)

Untuk memperkuat penjelasan teknis jika dosen menanyakan arsitektur database, tunjukkan bagaimana record data berubah pada setiap tahapan:

| Tahapan Operasional | Tabel Terpengaruh | Kolom Utama | Nilai Sebelum Event | Nilai Sesudah Event |
| :--- | :--- | :--- | :--- | :--- |
| **0. Pra-Notifikasi** | `trucks`<br>`containers` | `status`<br>`status` | `(Belum ada)`<br>`in_transit` | `queuing`<br>`in_transit` |
| **1. Gerbang Masuk** | `trucks`<br>`containers`<br>`yard_events` | `status`, `gate_in_time`<br>`status`, `gross_weight_kg`<br>`event_type` | `queuing`<br>`in_transit`<br>*(New Insert)* | `in_yard`, *TIMESTAMP*<br>`in_yard`, `34200.00`<br>`GATE_IN` |
| **2. Yard Lift-Off** | `containers`<br>`equipment`<br>`yard_events` | `block`, `bay`, `row`, `tier`<br>`status`, `current_container`<br>`event_type` | `NULL`<br>`idle`<br>*(New Insert)* | `B`, `08`, `03`, `02`<br>`carrying` $\rightarrow$ `idle`<br>`LIFT_OFF` |
| **3. Reefer Plugging** | `containers`<br>`yard_events` | `plug_status`, `temp_c`<br>`event_type` | `unplugged`<br>*(New Insert)* | `plugged_in`, `-20.2`<br>`REEFER_MONITOR` |
| **4. Bea Cukai** | `containers`<br>`yard_events` | `customs_status`<br>`event_type` | `CUSTOMS_HOLD`<br>*(New Insert)* | `SPPB_CLEARED`<br>`CUSTOMS_SPPB_RELEASE` |
| **5. Kereta Api** | `containers`<br>`trains`<br>`yard_events` | `status`<br>`loaded_wagons`<br>`event_type` | `in_yard`<br>`29`<br>*(New Insert)* | `on_rail`<br>`30`<br>`RAIL_LOAD` |
| **6. Penagihan** | `billing_invoices` | `payment_status`<br>`total_amount` | `unpaid`<br>`Rp 0` | `paid`<br>`Rp 499.500` |
| **7. Gerbang Keluar** | `trucks`<br>`containers`<br>`yard_events` | `status`, `gate_out_time`<br>`status`<br>`event_type` | `in_yard`<br>`on_rail`/`in_yard`<br>*(New Insert)* | `gate_out`, *TIMESTAMP*<br>`gate_out`<br>`GATE_OUT` |

---

## 7. Bank Pertanyaan Kritis Dosen Penguji & Jawaban Emas (Q&A Defense)

Gunakan cheatsheet jawaban ini saat sesi tanya jawab dengan dosen penguji:

---

### Q1: "Dari mana data pertama kali tercipta jika terminal baru mulai beroperasi?"
> **Jawaban Emas:**  
> *"Data awal tercipta dari dua lapis, Bapak/Ibu:*  
> *1. Lapisan administratif berupa Master Manifes / Delivery Order yang dikirim oleh perusahaan pelayaran atau agen logistik sebelum armada bergerak.*  
> *2. Lapisan operasional fisik tercipta di Gerbang Masuk (Gate-In) saat ban truk menginjak Loop Detector. Sensor tersebut memicu kamera ANPR membaca plat nomor, portal OCR membaca nomor kontainer ISO 6346, dan jembatan timbang merekam bobot VGM secara otomatis ke tabel `trucks` dan `containers`."*

---

### Q2: "Mengapa jembatan timbang di gate harus langsung menerbitkan sertifikasi SOLAS VGM?"
> **Jawaban Emas:**  
> *"Berdasarkan amandemen Konvensi Internasional SOLAS Chapter VI Regulation 2 oleh International Maritime Organization (IMO), setiap peti kemas yang akan dinaikkan ke kapal laut wajib memiliki Verified Gross Mass (VGM) yang tersertifikasi resmi.*  
> *Dengan menimbang bobot kotor di gerbang masuk CIDP menggunakan jembatan timbang terkalibrasi 80 Ton Metode 1 (Weighing the packed container), CIDP memberikan nilai tambah kepada eksportir sehingga kontainer tidak perlu ditimbang ulang di pelabuhan laut Tanjung Priok, mempercepat proses loading ke kapal."*

---

### Q3: "Apa perbedaan paling mendasar antara Dry Port CIDP ini dengan Depo Kontainer biasa?"
> **Jawaban Emas:**  
> *"Terdapat 3 perbedaan fundamental:*  
> *1. **Konektivitas Multimoda Berjadwal**: CIDP memiliki jalur rel ganda langsung ke dermaga pelabuhan laut (Pasoso Priok) dengan jadwal kereta api reguler 6 trip per hari.*  
> *2. **Otoritas Kepabeanan Penuh (Port of Origin/Destination)**: CIDP memiliki kode pelabuhan internasional sendiri dan hanggar Bea Cukai CEISA 4.0 terpadu, sehingga proses pabean (SPPB/SPJM) diselesaikan di Cikarang, bukan di pelabuhan laut.*  
> *3. **Otomasi Terintegrasi**: Menggunakan sistem YMS berbasis sensor IoT AIDC terpadu yang terhubung langsung dengan sistem perbankan Billing dan ERP Odoo."*

---

### Q4: "Bagaimana sistem memastikan nomor kontainer yang dibaca kamera OCR itu benar dan bukan nomor palsu?"
> **Jawaban Emas:**  
> *"Sistem kami menerapkan algoritma validasi Check Digit standar ISO 6346 secara matematis di backend.*  
> *Setiap 4 huruf kode pemilik dan 6 angka nomor seri dikonversi ke nilai bobot geometri perpangkatan 2 ($2^0$ hingga $2^9$), lalu dibagi dengan modulus 11. Angka sisa pembagian harus sama persis dengan angka ke-11 (check digit) pada fisik peti kemas.*  
> *Jika terjadi kesalahan pembacaan atau nomor palsu, sistem langsung membunyikan alert dan menghentikan palang gate untuk verifikasi fisik oleh petugas."*

---

### Q5: "Bagaimana sistem Billing menghitung biaya jika kontainer menginap lebih lama dari batas waktu?"
> **Jawaban Emas:**  
> *"Sistem menerapkan formula kalkulasi Dwell Time bertingkat:*  
> *1. Masa 1 (Hari 1 hingga Hari 3) adalah Masa Bebas Penumpukan (Free Time) dengan tarif Rp 0.*  
> *2. Jika peti kemas menginap melewati hari ke-3, fungsi cron job dan timestamp `gate_in_time` otomatis mengaktifkan tarif Masa 2 (Dwell Surcharge) sebesar Rp 180.000 per hari untuk ukuran 40 kaki.*  
> *3. Biaya ini diakumulasikan ke proforma invoice pada saat permohonan Surat Pengeluaran Peti Kemas (SP2B) diajukan oleh pengguna jasa."*

---

### 8. Ringkasan Eksekutif untuk Penutup Demo

> *"Melalui integrasi fisik, informasi, dan finansial ini, Conclusion Intermodal Dry Port membuktikan bahwa digitalisasi logistik intermodal mampu memangkas waktu inap (Dwell Time) dari rata-rata nasional 3.8 hari menjadi 1.8 hari, mengeliminasi kongesti jalan raya, serta memberikan transparansi rantai pasok berstandar internasional bagi industri manufaktur Indonesia."*

---
*(Dokumen ini merupakan bagian dari arsip resmi Proyek Akhir Mata Kuliah Teknologi dan Perangkat Lunak Logistik — ITL Trisakti 2026)*
