# PANDUAN LENGKAP DEMO APLIKASI & ALUR PERGERAKAN DATA OPERASIONAL
## Conclusion Intermodal Dry Port Management System (CIDP DPMS 35 Ha)
### Arsitektur Terpadu Tiga Pilar: YMS (Yard), TMS (Transport & Armada Langsir), dan WMS (Warehouse & CFS)

**Institusi:** Institut Transportasi dan Logistik (ITL) Trisakti  
**Program Studi:** Manajemen Transportasi Logistik  
**Mata Kuliah:** Teknologi dan Perangkat Lunak Logistik (Topik: ICD & Dry Port Management)  
**Dosen Pengampu:** Dr. Tigor Franky, S.T., M.T.  
**Entitas Konsultan:** Conclusion Supply Chain Consultant  
**Penyusun:** Zulfikar Jafarudin Fatah & Tim Pengembang CIDP  
**Versi Dokumen:** 2.5 (DPMS Triad Architecture Edition)  
**Tanggal Terbit:** Oktober 2026  

---

## DAFTAR ISI
1. [Filosofi Desain & Karakteristik Data Dry Port](#1-filosofi-desain--karakteristik-data-dry-port)
2. [Arsitektur Triad DPMS: Mengapa Dry Port Memerlukan YMS, TMS, dan WMS?](#2-arsitektur-triad-dpms-mengapa-dry-port-memerlukan-yms-tms-dan-wms)
3. [Triad Aliran Utama: Fisik, Informasi, dan Finansial](#3-triad-aliran-utama-fisik-informasi-dan-finansial)
4. [Titik Awal Demo & Genesis of Data (Dari Mana Data Bergerak?)](#4-titik-awal-demo--genesis-of-data-dari-mana-data-bergerak)
5. [Skenario Utama Demo: Perjalanan 1 Peti Kemas End-to-End](#5-skenario-utama-demo-perjalanan-1-peti-kemas-end-to-end)
6. [Panduan Langkah-demi-Langkah Demo Layar (Screen-by-Screen Script)](#6-panduan-langkah-demi-langkah-demo-layar-screen-by-screen-script)
   - [Langkah 1: Beranda Eksekutif (Opening & Gambaran DPMS 35 Ha)](#langkah-1-beranda-eksekutif-opening--gambaran-dpms-35-ha)
   - [Langkah 2: Gerbang Masuk, TMS Trucking & Head Truck Langsir Internal](#langkah-2-gerbang-masuk-tms-trucking--head-truck-langsir-internal)
   - [Langkah 3: Visualisasi Spasial Lapangan (Denah Terminal & Simulator 3D)](#langkah-3-visualisasi-spasial-lapangan-denah-terminal--simulator-3d)
   - [Langkah 4: Penataan Lapangan & Dispatching Alat Berat (Yard & RTG/RS)](#langkah-4-penataan-lapangan--dispatching-alat-berat-yard--rtgrs)
   - [Langkah 5: Pembuktian Langsung Scanner Optik & Validasi Check Digit](#langkah-5-pembuktian-langsung-scanner-optik--validasi-check-digit)
   - [Langkah 6: Keunggulan Intermodal KA Siding Pasoso ↔ CIDP](#langkah-6-keunggulan-intermodal-ka-siding-pasoso--cidp)
   - [Langkah 7: CFS Rampa D1-D5 & Konsolidasi Kargo (Pilar WMS)](#langkah-7-cfs-rampa-d1-d5--konsolidasi-kargo-pilar-wms)
   - [Langkah 8: Klirens Kepabeanan & CEISA 4.0 DJBC (Jalur Hijau vs Merah)](#langkah-8-klirens-kepabeanan--ceisa-40-djbc-jalur-hijau-vs-merah)
   - [Langkah 9: Penutupan Siklus Finansial & Sinkronisasi ERP Billing](#langkah-9-penutupan-siklus-finansial--sinkronisasi-erp-billing)
7. [Matriks Perubahan Tabel Basis Data (Database Lifecycle State)](#7-matriks-perubahan-tabel-basis-data-database-lifecycle-state)
8. [Bank Pertanyaan Kritis Dosen Penguji & Jawaban Emas (Q&A Defense)](#8-bank-pertanyaan-kritis-dosen-penguji--jawaban-emas-qa-defense)
9. [Ringkasan Eksekutif untuk Penutup Demo](#9-ringkasan-eksekutif-untuk-penutup-demo)

---

## 1. Filosofi Desain & Karakteristik Data Dry Port

Aplikasi **Conclusion Intermodal Dry Port Management System (CIDP DPMS) 35 Ha** dirancang bukan sekadar aplikasi pencatatan administrasi gudang pasif, melainkan sebuah **Sistem Manajemen Terpadu Pelabuhan Daratan Berbasis Peristiwa Nyata (*Event-Driven Architecture*)**.

Dalam logistik pelabuhan dan terminal intermodal kelas dunia, sebuah sistem perangkat lunak harus mencerminkan prinsip:
> *"No Manual Data Entry for Physical Movements. All movements are triggered by sensors, validated by business rules, and accounted for in the financial ledger."*

Artinya:
1. **Tidak ada operator yang mengetik manual** bahwa kontainer telah masuk atau berpindah. Semua dipicu oleh perangkat keras optik, RFID, jembatan timbang, dan GPS alat berat.
2. **Data bergerak searah dengan fisik barang**, menjamin integritas data audit trail sesuai standar kepabeanan (CEISA 4.0 DJBC) dan regulasi maritim internasional (SOLAS VGM IMO).
3. **Kepadatan Informasi Tinggi (*High Density Dashboard*)**: Seluruh modul telah dirancang ringkas menyerupai kokpit terminal eksekutif pelabuhan internasional.

---

## 2. Arsitektur Triad DPMS: Mengapa Dry Port Memerlukan YMS, TMS, dan WMS?

Dry Port adalah ekosistem logistik yang sangat kompleks. Konsep awal yang hanya berfokus pada lapangan penumpukan (*Yard*) kini telah ditingkatkan menjadi **CIDP DPMS (Dry Port Management System)** yang mengorkestrasikan tiga pilar utama dengan batasan wewenang fungsional yang jelas:

```
┌────────────────────────────────────────────────────────────────────────┐
│              CIDP DPMS (DRY PORT MANAGEMENT SYSTEM)                    │
│      Platform Orkestrasi Terpadu Logistik Pelabuhan Daratan 35 Ha      │
└──────────────────────────────────┬─────────────────────────────────────┘
                                   │
         ┌─────────────────────────┼─────────────────────────┐
         ▼                         ▼                         ▼
┌──────────────────┐      ┌──────────────────┐      ┌──────────────────┐
│   PILAR 1: TMS   │      │   PILAR 2: YMS   │      │   PILAR 3: WMS   │
│ (Transportation) │      │ (Yard & Stacking)│      │ (Warehouse/CFS)  │
├──────────────────┤      ├──────────────────┤      ├──────────────────┤
│ • Gate ANPR/OCR  │      │ • Blok 3D Yard   │      │ • CFS Rampa D1-D5│
│ • Timbangan VGM  │      │ • Reach Stacker  │      │ • Stripping LCL  │
│ • Truk Eksternal │      │ • RTG Crane GPS  │      │ • Stuffing FCL   │
│ • KTT Langsir    │      │ • Cold Chain     │      │ • Pallet 3T Scale│
│ • Siding KA Rail │      │ • Dwell Tracking │      │ • Gas HW-19 Safe │
└──────────────────┘      └──────────────────┘      └──────────────────┘
         │                         │                         │
         └─────────────────────────┼─────────────────────────┘
                                   ▼
         ┌───────────────────────────────────────────────────┐
         │            INTEGRASI PENDUKUNG ENTERPRISE         │
         │  • Bea Cukai CEISA 4.0 DJBC (Kepabeanan & Pabean) │
         │  • Billing Engine & ERP Odoo (Ledger Finansial)   │
         │  • Digital Twin GIS Denah 35 Ha & 3D Simulator    │
         └───────────────────────────────────────────────────┘
```

### A. Pilar TMS (Transportation Management System) — Mengapa Diperlukan?
1. **Manajemen Truk Eksternal (Highway Drayage):** Mengatur antrian pra-gerbang, verifikasi e-Ticket / Gate Pass, penimbangan SOLAS VGM 80 Ton, dan pemantauan Turnaround Time (TAT) truk pengangkut dari/ke jalan tol.
2. **Armada Head Truck Langsir Internal (Terminal Tractors / Shunting Trucks):**
   * Di dalam kawasan dry port seluas 35 Hektar, terdapat armada penarik internal (*KTT - Kendaraan Terminal Tractor*, seperti Kalmar Ottawa T2 dan Terberg YT220: unit `KTT-01`, `KTT-02`, `KTT-03`).
   * **Armada ini tidak pernah keluar ke jalan raya tol.** Fungsinya murni melayani transfer sirkulasi internal:
     - Rel Siding KA Pasoso $\leftrightarrow$ Gudang CFS Rampa Konsolidasi.
     - Rel Siding KA Pasoso $\leftrightarrow$ Blok Penumpukan Yard.
     - Blok Penumpukan Yard $\leftrightarrow$ Gudang CFS Konsolidasi.
   * TMS mengelola rute langsir internal, penugasan trailer chassis, utilisasi bahan bakar, dan siklus langsir (*shunting turnaround cycle* rata-rata 8.2 menit).
3. **Intermodal Rail Freight (KA Logistik Pasoso):** Penjadwalan rangkaian kereta api pelabuhan, pemantauan gerbong datar PPCW 42T, dan sensor gandar Frauscher Wheel Sensor.

### B. Pilar YMS (Yard Management System) — Inti Operasional 35 Ha
1. **Buffer Kapasitas Lapangan:** Yard seluas 35 Ha berfungsi sebagai titik temu (*cross-point*) alih muat antara moda kereta api dan angkutan truk darat.
2. **Optimasi Alokasi 3D (Bay-Row-Tier):** Menentukan posisi koordinat tumpukan peti kemas secara presisi untuk meminimalkan gerakan bongkar-pindah ganda (*shifting minimization*).
3. **Dispatching Alat Berat & Telemetri RTK/GPS:** Mengatur pergerakan derek RTG dan Reach Stacker berakurasi sentimeter.
4. **Reefer Cold Chain Management:** Memonitor 300 titik colokan pendingin reefer secara otomatis dengan sensor suhu SHT40 LoRaWAN.

### C. Pilar WMS (Warehouse Management System / CFS) — Mengapa Diperlukan?
1. **Container Freight Station (CFS Rampa D1-D5):** Dry port bukan hanya lapangan peti kemas kosong, melainkan pusat konsolidasi barang manufaktur ekspor-impor.
2. **Dekonsolidasi & Konsolidasi (LCL $\leftrightarrow$ FCL):** Membongkar isi kargo peti kemas impor (stripping) menjadi muatan pallet terpisah, atau menggabungkan kargo beberapa eksportir (stuffing) ke dalam satu kontainer utuh.
3. **Manajemen Gudang Tertutup:** Dilengkapi timbangan lantai pallet 3 Ton (HW-17), barcode scanner genggam Honeywell (HW-18), dan sensor keselamatan gas multigas HW-19 untuk mendeteksi uap kimia berbahaya.

---

## 3. Triad Aliran Utama: Fisik, Informasi, dan Finansial

Sebelum memulai demonstrasi, pahami segitiga keterhubungan yang selalu berjalan di dalam aplikasi CIDP:

```
┌────────────────────────────────────────────────────────────────────────┐
│ 1. ALIRAN FISIK & SENSOR (Physical & Hardware Layer)                    │
│ Truk, Kereta Api, Peti Kemas, Reach Stacker, Sensor, Kamera, Timbangan  │
└──────────────────────────────────┬─────────────────────────────────────┘
                                   │ Memicu Sinyal Telemetri (Trigger)
                                   ▼
┌────────────────────────────────────────────────────────────────────────┐
│ 2. ALIRAN INFORMASI & SOFTWARE (CIDP DPMS Engine Layer)                │
│ Ingesti Sensor, Validasi Aturan Bisnis, Pemutakhiran Database MySQL    │
└──────────────────────────────────┬─────────────────────────────────────┘
                                   │ Memicu Transaksi Finansial (Billing)
                                   ▼
┌────────────────────────────────────────────────────────────────────────┐
│ 3. ALIRAN FINANSIAL & KOMERSIAL (Billing ERP & Ledger Layer)            │
│ Tarif Gate Pass, Jasa Lo-Lo, VGM SOLAS, Plugging Listrik, Haulage KA   │
└────────────────────────────────────────────────────────────────────────┘
```

Setiap detik pergerakan fisik yang ditangkap perangkat keras akan mengubah status di database DPMS, yang seketika membentuk pos piutang (*billing ledger entry*) pada modul keuangan terminal.

---

## 4. Titik Awal Demo & Genesis of Data (Dari Mana Data Bergerak?)

Bila dosen atau penguji bertanya: **"Dari mana data berawal di aplikasi ini?"**, berikut adalah pemahaman fundamentalnya:

### A. Tahap 0: Pra-Kondisi Data (Pre-Advise / Master Data)
Sebelum armada bergerak, data manifes atau Delivery Order (DO) telah dipertukarkan via API / EDI ke dalam status `SCHEDULED` atau `QUEUING`.

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

## 5. Skenario Utama Demo: Perjalanan 1 Peti Kemas End-to-End

Untuk mendemonstrasikan aplikasi secara terstruktur dalam waktu **8 hingga 10 menit**, gunakan alur **Skenario Tunggal Peti Kemas**:

```
[Gerbang Masuk] ──► [Yard Stacking] ──► [Langsir KTT] ──► [CFS Gudang] ──► [KA Siding] ──► [Billing ERP]
  (Gate-In ANPR)      (Reach Stacker)     (Shunting KTT)     (WMS Rampa)     (Intermodal)      (Odoo ERP)
```

- **Nomor Peti Kemas Rekomendasi:** `MSKU9182374` (Tipe: 40ft High Cube Dry).
- **Armada Truk Luar:** Plat `B 9182 TE` (Sopir: Soleh Marzuki, PT Trans Logistik Prima).
- **Head Truck Langsir Internal:** Unit `KTT-01` (Kalmar Ottawa T2, Operator: Bambang Sugeng).
- **Misi Integrasi:** Impor Tanjung Priok $\rightarrow$ Alih muat Yard $\rightarrow$ Langsir ke CFS Rampa $\rightarrow$ Pengiriman via Kereta Api Pasoso.

---

## 6. Panduan Langkah-demi-Langkah Demo Layar (Screen-by-Screen Script)

Berikut adalah panduan praktis layar per layar lengkap dengan **tindakan klik**, **narasi bicara**, dan **poin kunci yang harus ditonjolkan**:

---

### Langkah 1: Beranda Eksekutif (Opening & Gambaran DPMS 35 Ha)
* **URL Layar:** `dashboard.php?page=beranda`
* **Alokasi Waktu:** 1 Menit
* **Aksi Pengguna:**
  1. Tunjukkan badge header: **"Konsol Eksekutif & Telemetri Terpadu Dry Port (DPMS)"** dengan pill **"Orkestrasi YMS • TMS • WMS"**.
  2. Tunjukkan **Pusat Kendali Sensor & IoT Workbench Lapangan** dengan 4 shortcut berlabel pilar:
     - Gate & Timbangan (Pilar TMS)
     - CFS Rampa D1-D5 (Pilar WMS)
     - Reefer Cold Chain (Pilar YMS)
     - Denah Terminal 35 Ha (Digital Twin Spasial)
  3. Sorot kartu status SCADA (99.8%) dan Siding KA (Ready).
* **Naskah Pembicara:**
  > *"Selamat pagi/siang Bapak Dr. Tigor Franky dan rekan-rekan penguji. Selamat datang di presentasi Conclusion Intermodal Dry Port Management System (CIDP DPMS) seluas 35 Hektar di Cikarang.*  
  > *Aplikasi ini kami rancang bukan sekadar sistem lapangan biasa, melainkan platform terpadu yang mengorkestrasikan tiga pilar utama logistik intermodal: YMS untuk penataan yard 35 Ha, TMS untuk sirkulasi angkutan jalan raya dan head truck langsir internal, serta WMS untuk pergudangan konsolidasi CFS.*  
  > *Di halaman Beranda Eksekutif ini, seluruh telemetri terintegrasi dengan latensi di bawah 150 milidetik. Sekarang, mari kita lihat bagaimana pilar TMS mengelola arus gerbang dan armada langsir internal."*

---

### Langkah 2: Gerbang Masuk, TMS Trucking & Head Truck Langsir Internal
* **URL Layar:** `dashboard.php?page=trucking` (atau `dashboard.php?page=gate`)
* **Alokasi Waktu:** 2.5 Menit
* **Aksi Pengguna:**
  1. Klik menu **Trucking & Langsir** di sidebar kiri.
  2. Tunjukkan filter dropdown: **"Tipe Armada: Semua / Truk Eksternal / Head Truck Langsir Internal"**.
  3. Perlihatkan 3 unit **Head Truck Langsir (Terminal Tractor CIDP: KTT-01, KTT-02, KTT-03)** di baris atas tabel:
     - Badge oranye tebal: `KTT CIDP • LANGSIR INTERNAL`.
     - Misi: `Langsir KA Siding ↔ CFS Rampa` atau `Langsir Yard ↔ CFS`.
     - Lintasan: `Sirkulasi 35 Ha • Non-Gate`.
  4. Klik baris `KTT-01` untuk membuka modal **Milestone Siklus Langsir**:
     - Perlihatkan siklus 5 tahap: Dispatching YMS/TMS $\rightarrow$ Kopling Chassis $\rightarrow$ Sirkulasi Perimeter 35 Ha (Max 20 km/jam) $\rightarrow$ Penanganan Kargo di CFS Rampa $\rightarrow$ Siklus Selesai Standby di Yard (tanpa keluar gerbang tol).
  5. Beralih ke filter **"Truk Eksternal"**, klik baris `B 9182 TE`:
     - Tunjukkan sertifikat jembatan timbang 80T SOLAS VGM (Gross, Tare, Net) dan siklus Gate-In ke Gate-Out.
* **Naskah Pembicara:**
  > *"Pada pilar TMS ini, terdapat diferensiasi operasional yang sangat krusial di dry port:*  
  > *Pertama adalah Truk Eksternal pembawa kargo dari jalan tol yang melalui gerbang masuk, jembatan timbang 80 Ton SOLAS VGM, dan mendapatkan e-Gate Pass.*  
  > *Kedua adalah Head Truck Langsir Internal (Terminal Tractor: KTT-01, KTT-02). Truk ini adalah aset internal CIDP yang tidak pernah keluar ke jalan raya tol. Mereka bertugas khusus melakukan transfer intermodal berkecepatan tinggi antara Siding Rel KA Pasoso, Blok Penumpukan Yard, dan Gudang Konsolidasi CFS dengan siklus langsir hanya 8.5 menit.*  
  > *Dengan modul TMS ini, terminal controller dapat memantau pergerakan kedua jenis armada secara transparan."*

---

### Langkah 3: Visualisasi Spasial Lapangan (Denah Terminal & Simulator 3D)
* **URL Layar:** `dashboard.php?page=denah`
* **Alokasi Waktu:** 1.5 Menit
* **Aksi Pengguna:**
  1. Klik menu **Denah Terminal 35 Ha** di sidebar.
  2. Tunjukkan site plan master plan teknik sipil arsitektural (kertas terang dengan grid teknis).
  3. Gunakan kontrol zoom terapung: klik **[+] Zoom In** dan geser (pan) denah untuk memperlihatkan layout Blok A-E, Jalur Sirkulasi Langsir, Siding KA, dan CFS Warehouse.
  4. Klik Pin HW-01 (ANPR Gate) atau HW-10 (Sensor Gandar Rel) untuk membuka panel Inspector.
* **Naskah Pembicara:**
  > *"Untuk memantau sebaran fisik di kawasan 35 Hektar, sistem menyediakan Denah Terminal Arsitektural berbasis site plan teknik sipil.*  
  > *Di denah ini terlihat jelas alur sirkulasi terpisah antara jalan truk eksternal dan jalur khusus langsir internal terminal tractor untuk mencegah titik kemacetan (bottleneck). Seluruh 26 sensor IoT BOM terpetakan secara presisi."*

---

### Langkah 4: Penataan Lapangan & Dispatching Alat Berat (Yard & RTG/RS)
* **URL Layar:** `dashboard.php?page=yard` dilanjutkan `dashboard.php?page=alat`
* **Alokasi Waktu:** 2 Menit
* **Aksi Pengguna:**
  1. Buka modul **Lapangan (Yard)**: perlihatkan okupansi TEU per blok (Blok A Dry 40ft, Blok B Dry 20ft, Reefer, dan Behandle Blok E).
  2. Buka modul **Alat Berat**: tunjukkan **Peta Skematik Radar Live Alat Berat**.
  3. Perlihatkan unit **RS-01** (Blok A), **RS-02** (Blok B), dan **RTG-01** (Siaga di Rel Siding KA Pasoso).
  4. Tunjukkan tombol **+ Dispatch Job** untuk penugasan Lift-Off/Lift-On peti kemas ke slot tumpukan (*Bay-Row-Tier*).
* **Naskah Pembicara:**
  > *"Pilar YMS mengelola jantung penumpukan terminal. Melalui telemetri DGPS/RTK berakurasi sentimeter pada spreader crane RTG dan Reach Stacker, sistem mengunci koordinat kontainer ke basis data.*  
  > *Saat kontainer MSKU9182374 diturunkan di Blok B Bay 08 - Row 03 - Tier 02, koordinat langsung terkunci dan sistem mencatat biaya Lift-Off secara otomatis ke modul billing."*

---

### Langkah 5: Pembuktian Langsung Scanner Optik & Validasi Check Digit
* **URL Layar:** `dashboard.php?page=scanner`
* **Alokasi Waktu:** 1 Menit
* **Aksi Pengguna:**
  1. Klik menu **Scanner Optik** di sidebar.
  2. Klik tab **"Uji Sampel Instan"**, lalu klik kartu **Peti Kemas ISO 6346 (`MSKU9182374`)**.
  3. Tunjukkan hasil verifikasi: Barcode Code 128 terdeteksi, Check Digit matematis: **VALID (4)**, dan data status di DB YMS langsung sinkron.
* **Naskah Pembicara:**
  > *"Sistem kami mengimplementasikan algoritma verifikasi Check Digit standar internasional ISO 6346 secara matematis di backend.*  
  > *Dalam 42 milidetik, sistem memverifikasi bahwa peti kemas ini valid secara nomor seri pabrikan, terdaftar di manifes pelayaran, dan bukan nomor palsu."*

---

### Langkah 6: Keunggulan Intermodal KA Siding Pasoso ↔ CIDP
* **URL Layar:** `dashboard.php?page=intermodal`
* **Alokasi Waktu:** 1.5 Menit
* **Aksi Pengguna:**
  1. Klik menu **Intermodal KA** di sidebar.
  2. Tunjukkan metrik rangkaian: KA 2518 (Inbound) & KA 2519 (Outbound), muatan 60 TEU (100% capacity), sensor gandar Frauscher Wheel Sensor SIL 4 (252 As Roda).
  3. Perlihatkan jalur shuttle 24-jam Pasoso Tanjung Priok $\leftrightarrow$ CIDP Cikarang (54.8 KM bebas macet).
* **Naskah Pembicara:**
  > *"Inilah keunggulan utama CIDP: Jalur rel siding ganda 2 x 450 meter yang tersambung langsung ke Pelabuhan Tanjung Priok.*  
  > *Satu rangkaian kereta mengangkut 60 TEU sekaligus, memangkas 60 perjalanan truk di Jalan Tol Jakarta-Cikampek dan mereduksi emisi karbon hingga 76%."*

---

### Langkah 7: CFS Rampa D1-D5 & Konsolidasi Kargo (Pilar WMS)
* **URL Layar:** `dashboard.php?page=cfs`
* **Alokasi Waktu:** 1.5 Menit
* **Aksi Pengguna:**
  1. Klik menu **CFS Rampa D1-D5** di sidebar.
  2. Tunjukkan 5 pintu rampa bongkar-muat (*Docking Ramps D1 s/d D5*).
  3. Tunjukkan integrasi sensor WMS:
     - Timbangan Lantai Pallet 3 Ton (HW-17) untuk muatan LCL.
     - Detektor Multigas Honeywell HW-19 yang memantau keamanan udara di dalam gudang tertutup.
  4. Jelaskan bagaimana kontainer LCL yang dibawa oleh head truck langsir `KTT-01` dibongkar (stripping) di rampa D2 untuk konsolidasi kargo industri.
* **Naskah Pembicara:**
  > *"Inilah pilar WMS pada CIDP DPMS: Fasilitas Container Freight Station seluas 12.000 meter persegi dengan 5 rampa docking.*  
  > *Ketika kontainer LCL tiba menggunakan head truck langsir internal KTT-01, kargo di-stripping dan ditimbang dengan timbangan pallet 3 Ton berstandar industri.*  
  > *Sistem WMS mencatat alokasi pallet, nomor SSCC-18 kargo, serta memastikan keselamatan kerja melalui sensor gas multigas HW-19."*

---

### Langkah 8: Klirens Kepabeanan & CEISA 4.0 DJBC (Jalur Hijau vs Merah)
* **URL Layar:** `dashboard.php?page=customs`
* **Alokasi Waktu:** 1 Menit
* **Aksi Pengguna:**
  1. Klik menu **Kepabeanan** di sidebar.
  2. Perlihatkan koneksi REST API CEISA 4.0 DJBC (KPPBC TMP Cikarang).
  3. Tunjukkan dokumen impor `MSKU9182374` bersertifikasi AEO memperoleh status Jalur Hijau dengan penerbitan dokumen SPPB otomatis (< 3.2 detik).
* **Naskah Pembicara:**
  > *"Sebagai Tempat Penimbunan Pabean resmi, CIDP memiliki kantor pabean mandiri. Kargo importir MITA/AEO otomatis terbit SPPB Jalur Hijau.*  
  > *Apabila terkena Jalur Merah, sistem DPMS otomatis mengunci gerbang keluar dan mengarahkan kontainer ke Blok E untuk pemeriksaan fisik Behandle dan Gantry X-Ray."*

---

### Langkah 9: Penutupan Siklus Finansial & Sinkronisasi ERP Billing
* **URL Layar:** `dashboard.php?page=billing`
* **Alokasi Waktu:** 1 Menit
* **Aksi Pengguna:**
  1. Klik menu **Billing & Keuangan** di sidebar.
  2. Cari nomor kontainer `MSKU9182374` pada tab **Faktur & Penagihan**.
  3. Tunjukkan rincian komponen biaya: Gate Pass (Rp 50.000), Timbang SOLAS VGM (Rp 150.000), Jasa Lift-Off (Rp 250.000), Masa Bebas Penumpukan 3 Hari (Rp 0) = Total Rp 450.000 + PPN 11%.
  4. Perlihatkan status sinkronisasi payload JSON ke ERP Odoo / SAP.
* **Naskah Pembicara:**
  > *"Seluruh siklus pergerakan fisik YMS, transportasi TMS, dan pergudangan WMS bermuara pada penagihan otomatis.*  
  > *Semua biaya dikalkulasikan secara transparan tanpa intervensi manual dan tersinkronisasi ke ERP Odoo. Begitu tagihan lunas dan SPPB terverifikasi, sistem menerbitkan SP2B untuk mengizinkan armada menyelesaikan siklus Gate-Out."*

---

## 7. Matriks Perubahan Tabel Basis Data (Database Lifecycle State)

Tabel berikut membuktikan arsitektur *event-driven* pada database relasional CIDP:

| Tahapan Operasional | Pilar Terkait | Tabel Terpengaruh | Nilai Sebelum Event | Nilai Sesudah Event | Trigger Sensor / Aksi |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **0. Pra-Notifikasi** | TMS | `trucks`<br>`containers` | `(Belum ada)`<br>`in_transit` | `queuing`<br>`in_transit` | Ingesti API EDI Pelayaran / DO |
| **1. Gerbang Masuk** | TMS | `trucks`<br>`containers`<br>`yard_events` | `queuing`<br>`in_transit`<br>*(New Insert)* | `in_yard`, *TIMESTAMP*<br>`in_yard`, `34200.00`<br>`GATE_IN` | ANPR HW-01 + OCR HW-02 + Loop Detector |
| **2. Penimbangan VGM** | TMS | `trucks` | `weight_gross: 0` | `weight_gross: 34200`<br>`weight_net: 21700` | Jembatan Timbang 80T HW-03 |
| **3. Yard Lift-Off** | YMS | `containers`<br>`equipment`<br>`yard_events` | `block: NULL`<br>`idle`<br>*(New Insert)* | `block: B`, `bay: 08`, `row: 03`, `tier: 02`<br>`carrying` $\rightarrow$ `idle`<br>`LIFT_OFF` | Twistlock Spreader RS-02 + DGPS RTK |
| **4. Langsir Internal** | TMS | `trucks` (KTT)<br>`yard_events` | `status: in_yard`<br>*(New Insert)* | `status: in_yard` (Langsir Aktif)<br>`INTERNAL_SHUNTING` | Dispatching YMS/TMS Controller |
| **5. Stripping CFS** | WMS | `cfs_cargo`<br>`yard_events` | `pallet_count: 0`<br>*(New Insert)* | `pallet_count: 24`<br>`CFS_STRIPPING` | Barcode Honeywell HW-18 + Timbangan Pallet HW-17 |
| **6. Klirens Pabean** | Compliance | `containers`<br>`yard_events` | `customs_status: HOLD`<br>*(New Insert)* | `customs_status: SPPB_CLEARED`<br>`CUSTOMS_SPPB_RELEASE` | REST API CEISA 4.0 DJBC |
| **7. Alih Muat KA** | TMS & YMS | `containers`<br>`trains`<br>`yard_events` | `in_yard`<br>`loaded: 29`<br>*(New Insert)* | `on_rail`<br>`loaded: 30`<br>`RAIL_LOAD` | Derek RTG-01 + Frauscher Wheel Sensor |
| **8. Penagihan** | Finance | `billing_invoices` | `payment_status: unpaid` | `payment_status: paid`<br>`total: Rp 499.500` | Payment Gateway / ERP Odoo XML-RPC |
| **9. Gerbang Keluar** | TMS | `trucks`<br>`yard_events` | `in_yard`<br>*(New Insert)* | `gate_out`, *TIMESTAMP*<br>`GATE_OUT` | ANPR Gate-Out + Barcode Gate Pass Scan |

---

## 8. Bank Pertanyaan Kritis Dosen Penguji & Jawaban Emas (Q&A Defense)

Gunakan cheatsheet jawaban ini saat sesi tanya jawab dengan dosen penguji Dr. Tigor Franky:

---

### Q1: "Mengapa sistem ini disebut Dry Port Management System (DPMS) yang menggabungkan YMS, TMS, dan WMS, bukan hanya YMS biasa?"
> **Jawaban Emas:**  
> *"Terima kasih atas pertanyaannya, Bapak Dr. Tigor. Dry port memiliki kompleksitas operasional yang jauh melampaui depo kontainer biasa. Terdapat tiga entitas operasional berbeda yang harus saling terhubung:*  
> *1. **Yard (YMS)** adalah intinya, yang mengelola kapasitas buffer penumpukan 35 Ha, pergerakan derek RTG/Reach Stacker, dan colokan reefer.*  
> *2. **Transport (TMS)** mutlak dibutuhkan karena ada dua kategori pergerakan armada: truk jalan raya eksternal yang melalui gerbang timbang jalan tol, serta **Head Truck Langsir Internal (Terminal Tractor KTT-01/02)** yang beroperasi khusus di dalam kawasan untuk memindahkan kargo dari rel kereta api ke gudang.*  
> *3. **Warehouse (WMS)** mutlak ada karena dry port mengoperasikan fasilitas CFS (Container Freight Station) untuk konsolidasi dan dekonsolidasi kargo LCL.*  
> *Jika ketiga sistem ini berdiri sendiri secara terpisah (terisolasi), akan terjadi jeda data (data silo), kesalahan koordinasi alat berat, dan kebocoran pendapatan terminal. Oleh karena itu, arsitektur DPMS kami menyatukan ketiganya dalam satu orkestrasi real-time."*

---

### Q2: "Mengapa Head Truck Langsir Internal (Terminal Tractor) tetap dimasukkan ke dalam TMS padahal tidak pernah keluar gerbang tol?"
> **Jawaban Emas:**  
> *"Dalam teori manajemen transportasi pelabuhan, terminal tractor adalah moda transportasi intermodal internal (Internal Horizontal Transport). Di lahan seluas 35 Hektar, jarak tempuh dari Siding Rel KA Pasoso ke Gudang CFS mencapai lebih dari 800 meter.*  
> *Tanpa modul TMS khusus langsir internal, terminal controller tidak dapat memantau utilisasi unit traktor, waktu siklus langsir (turnaround time per cycle), konsumsi bahan bakar, maupun antrian trailer di rampa docking. TMS internal memastikan siklus alih muat gerbong kereta api ke rampa gudang berjalan efisien dalam target waktu di bawah 10 menit."*

---

### Q3: "Dari mana data pertama kali tercipta jika terminal baru mulai beroperasi?"
> **Jawaban Emas:**  
> *"Data awal tercipta dari dua lapis, Bapak:*  
> *1. Lapisan administratif berupa Master Manifes / Delivery Order yang dikirim oleh perusahaan pelayaran atau agen logistik sebelum armada bergerak.*  
> *2. Lapisan operasional fisik tercipta di Gerbang Masuk (Gate-In) saat ban truk menginjak Loop Detector. Sensor tersebut memicu kamera ANPR membaca plat nomor, portal OCR membaca nomor kontainer ISO 6346, dan jembatan timbang merekam bobot VGM secara otomatis ke tabel `trucks` dan `containers`."*

---

### Q4: "Mengapa jembatan timbang di gate harus langsung menerbitkan sertifikasi SOLAS VGM?"
> **Jawaban Emas:**  
> *"Berdasarkan amandemen Konvensi Internasional SOLAS Chapter VI Regulation 2 oleh International Maritime Organization (IMO), setiap peti kemas yang akan dinaikkan ke kapal laut wajib memiliki Verified Gross Mass (VGM) yang tersertifikasi resmi.*  
> *Dengan menimbang bobot kotor di gerbang masuk CIDP menggunakan jembatan timbang terkalibrasi 80 Ton Metode 1 (Weighing the packed container), CIDP memberikan nilai tambah kepada eksportir sehingga kontainer tidak perlu ditimbang ulang di pelabuhan laut Tanjung Priok, mempercepat proses loading ke kapal."*

---

### Q5: "Apa perbedaan paling mendasar antara Dry Port CIDP ini dengan Depo Kontainer biasa?"
> **Jawaban Emas:**  
> *"Terdapat 3 perbedaan fundamental:*  
> *1. **Konektivitas Multimoda Berjadwal**: CIDP memiliki jalur rel ganda langsung ke dermaga pelabuhan laut (Pasoso Priok) dengan jadwal kereta api reguler 6 trip per hari.*  
> *2. **Otoritas Kepabeanan Penuh (Port of Origin/Destination)**: CIDP memiliki kode pelabuhan internasional sendiri dan hanggar Bea Cukai CEISA 4.0 terpadu, sehingga proses pabean (SPPB/SPJM) diselesaikan di Cikarang, bukan di pelabuhan laut.*  
> *3. **Otomasi Terintegrasi**: Menggunakan sistem DPMS berbasis sensor IoT AIDC terpadu yang terhubung langsung dengan sistem perbankan Billing dan ERP Odoo."*

---

### Q6: "Bagaimana sistem memastikan nomor kontainer yang dibaca kamera OCR itu benar dan bukan nomor palsu?"
> **Jawaban Emas:**  
> *"Sistem kami menerapkan algoritma validasi Check Digit standar ISO 6346 secara matematis di backend.*  
> *Setiap 4 huruf kode pemilik dan 6 angka nomor seri dikonversi ke nilai bobot geometri perpangkatan 2 ($2^0$ hingga $2^9$), lalu dibagi dengan modulus 11. Angka sisa pembagian harus sama persis dengan angka ke-11 (check digit) pada fisik peti kemas.*  
> *Jika terjadi kesalahan pembacaan atau nomor palsu, sistem langsung membunyikan alert dan menghentikan palang gate untuk verifikasi fisik oleh petugas."*

---

### Q7: "Bagaimana sistem Billing menghitung biaya jika kontainer menginap lebih lama dari batas waktu?"
> **Jawaban Emas:**  
> *"Sistem menerapkan formula kalkulasi Dwell Time bertingkat:*  
> *1. Masa 1 (Hari 1 hingga Hari 3) adalah Masa Bebas Penumpukan (Free Time) dengan tarif Rp 0.*  
> *2. Jika peti kemas menginap melewati hari ke-3, fungsi cron job dan timestamp `gate_in_time` otomatis mengaktifkan tarif Masa 2 (Dwell Surcharge) sebesar Rp 180.000 per hari untuk ukuran 40 kaki.*  
> *3. Biaya ini diakumulasikan ke proforma invoice pada saat permohonan Surat Pengeluaran Peti Kemas (SP2B) diajukan oleh pengguna jasa."*

---

## 9. Ringkasan Eksekutif untuk Penutup Demo

> *"Melalui integrasi fisik, informasi, dan finansial pada platform Conclusion Intermodal Dry Port Management System (CIDP DPMS) ini, kami membuktikan bahwa konvergensi tiga pilar logistik—YMS di lapangan 35 Ha, TMS pada sirkulasi jalan raya dan langsir internal, serta WMS di rampa konsolidasi CFS—mampu memangkas waktu inap (Dwell Time) dari rata-rata nasional 3.8 hari menjadi 1.8 hari, mengeliminasi kemacetan jalan tol, dan mewujudkan ekosistem logistik intermodal hijau berstandar internasional bagi industri manufaktur Indonesia."*

---
*(Dokumen ini merupakan bagian dari arsip resmi Proyek Akhir Mata Kuliah Teknologi dan Perangkat Lunak Logistik — ITL Trisakti 2026)*
