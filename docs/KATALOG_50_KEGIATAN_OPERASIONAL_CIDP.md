# KATALOG MASTER 50 KEGIATAN OPERASIONAL & SKENARIO DEMONSTRASI
## CONCLUSION INTERMODAL DRY PORT MANAGEMENT SYSTEM (CIDP DPMS 35 HA)
### Inland Extension of International Seaport — PT Multi Terminal Indonesia / ITL Trisakti

> **Status Dokumen:** Living Document (Versi 2.0 — Dapat Diperluas / Expandable)  
> **Klasifikasi:** Dokumen Acuan Demonstrasi Operasional & Matriks Fungsional Sistem  
> **Mata Kuliah:** Teknologi dan Perangkat Lunak Logistik (Dosen Pengampu: Dr. Tigor Franky)  
> **Repository:** `d:\xampp\htdocs\Conclusion_Intermodal_Dry_Port`  
> **Host Server Jaringan:** `http://192.168.100.82/Conclusion_Intermodal_Dry_Port/`

---

## 1. PENDAHULUAN & PRINSIP FUNDAMENTAL SISTEM

### 1.1 Doktrin Operasional Terminal (TOC Command Doctrine)
Sebagai kepanjangan tangan pelabuhan laut internasional di darat (*inland container terminal*), seluruh keputusan operasional (alokasi jalur gerbang, penugasan slot penumpukan lapangan, izin rilis kepabeanan, susunan rangkaian gerbong kereta api, hingga persetujuan *gate-out*) **diputuskan secara terpusat oleh Terminal Operations Center (TOC)**. Personel operasional di lapangan (supir truk, operator Reach Stacker/RTG, tally clerk gudang, teknisi reefer) menerima instruksi kerja digital (melalui VMT, HHT, display LED), dan sensor/hardware melaporkan realisasi fisik ke pusat kendali.

### 1.2 Struktur Tim Resmi 5 Orang & Tanggung Jawab (RACI Matrix)
Sesuai ketetapan tim proyek, seluruh pengujian dan demonstrasi sistem didistribusikan kepada 5 personel resmi:
1. **Zulfikar Jafarudin Fatah** — *Lead System Architect & Full-Stack Programmer* (Accountable)
2. **Naufal Andika Heditya** — *Business Analyst & QA Specialist* (Responsible)
3. **Armansyah Muchtarrom** — *Hardware & Infrastructure Specialist* (Responsible)
4. **Afriansayah Ayubi** — *Software & ERP Process Specialist* (Responsible)
5. **Juan Gamaliel** — *Yard Planning, CFS & Multimodal Specialist* (Responsible)

### 1.3 Penegasan Konseptual: Simulator 3D vs. Digital Twin Data Nyata
Untuk mencegah kerancuan saat presentasi di hadapan dosen penguji, arsitektur visual sistem dirancang dengan dua modul yang memiliki tujuan fundamental berbeda:

| Parameter | Simulator 3D Sandbox (`page=simulator`) | Digital Twin 2D/3D Telemetri (`page=twin`) |
| :--- | :--- | :--- |
| **Tujuan Utama** | **Eksplorasi Fasilitas & Simulasi Visual Bebas** | **Kembar Digital Berbasis Data Nyata (Real-Time)** |
| **Sifat Interaksi** | *Read-Only Showcase / Non-Destructive* (Aman dicoba) | *Event-Driven Mirror* (Terikat data sensor & database) |
| **Pengaruh ke Database**| **TIDAK mengubah data operasional**. Dibuat agar dosen/pengunjung dapat melihat fasilitas apa saja yang ada di kawasan dry port 35 Ha tanpa merusak data demo. | **MENGUBAH & MEMBACA database secara langsung**. Alat berat, truk, dan kontainer **hanya bergerak** jika ada transaksi data masuk. |
| **Mesin Render** | Three.js WebGL dengan orbit controls, preset kamera sinematik, dan raycasting inspektor 3D. | Three.js WebGL + 2D SVG Blueprint dengan koneksi Server-Sent Events (SSE) berlatensi <35ms. |
| **Analog Kasus** | Seperti *Flight Simulator* (wadah uji coba visual mandiri). | Seperti *Air Traffic Control Radar* (cerminan riil penerbangan aktif di langit). |

---

## 2. REKAPITULASI 11 KLUSTER AKTIVITAS SISTEM

Seluruh proses bisnis terminal dipetakan ke dalam **50 Kegiatan Operasional (ACT-01 s/d ACT-50)** yang mencakup 3 Aliran Terpadu (*Fisik, Informasi, Finansial*):

```
┌────────────────────────────────────────────────────────────────────────┐
│                     11 KLUSTER OPERASIONAL CIDP                        │
├────────────────────────────────────────────────────────────────────────┤
│ Kluster 01: Pra-Kedatangan & Antrean Luar        (ACT-01 s/d ACT-03)   │
│ Kluster 02: Gerbang Masuk & Timbangan VGM        (ACT-04 s/d ACT-10)   │
│ Kluster 03: Lapangan Penumpukan Kontainer (CY)   (ACT-11 s/d ACT-16)   │
│ Kluster 04: Gudang CFS & Kargo LCL               (ACT-17 s/d ACT-21)   │
│ Kluster 05: Rantai Dingin Kontainer Reefer       (ACT-22 s/d ACT-25)   │
│ Kluster 06: Kepabeanan Bea Cukai CEISA 4.0       (ACT-26 s/d ACT-30)   │
│ Kluster 07: Intermodal Rail Siding Kereta Api    (ACT-31 s/d ACT-34)   │
│ Kluster 08: Billing, Finansial & Odoo 17 ERP     (ACT-35 s/d ACT-38)   │
│ Kluster 09: Gerbang Keluar Dual-Clearance        (ACT-39 s/d ACT-41)   │
│ Kluster 10: Pengendalian Sentral TOC & Spasial   (ACT-42 s/d ACT-45)   │
│ Kluster 11: Keamanan Cyber API & Dead Letter Q   (ACT-46 s/d ACT-50)   │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 3. MATRIKS DETAIL 50 KEGIATAN OPERASIONAL

### KLUSTER 01: PRA-KEDATANGAN & ANTREAN LUAR (PRE-GATE BUFFER AREA)

| ID | Nama Kegiatan | PIC / Aktor | Hardware / Media | Halaman | Dampak 3-Aliran | Cara Demonstrasi |
| :---: | :--- | :--- | :--- | :--- | :--- | :--- |
| **ACT-01** | Pendaftaran Digital Pre-Advice Truk | Afriansayah / Shipper | Form Web / API | [`pages/gate.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/gate.php) | **Info:** Truk tercatat `SCHEDULED` di tabel `preadvice`. | Klik form pendaftaran pre-advice atau impor JSON tiket. |
| **ACT-02** | Penerbitan e-Ticket & Barcode Kiosk | Zulfikar / Kiosk | Barcode Engine (HW-02) | [`pages/gate.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/gate.php) | **Info:** QR / Barcode Code-128 tiket unik terbit. | Klik tombol *"Tiket e-Pass"* di antrean pre-gate. |
| **ACT-03** | Monitoring Buffer Parkir Luar 4.2 Ha | Naufal / TOC | Peta Denah Z1 | [`pages/beranda.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/beranda.php) | **Fisik:** 30 armada siaga tanpa memacetkan jalan arteri. | Cek indikator Z1 Buffer di Cockpit Beranda Eksekutif. |

---

### KLUSTER 02: GERBANG MASUK & TIMBANGAN VGM (INBOUND GATE & SCALE)

| ID | Nama Kegiatan | PIC / Aktor | Hardware / Media | Halaman | Dampak 3-Aliran | Cara Demonstrasi |
| :---: | :--- | :--- | :--- | :--- | :--- | :--- |
| **ACT-04** | Deteksi Otomatis RFID Truk | Armansyah | RFID Reader (HW-01) | [`pages/integration.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/integration.php) | **Info:** Validasi TID tag RFID 860–960 MHz pengenal armada. | Klik Panel A Langkah 1 di Pusat Integrasi. |
| **ACT-05** | Scan Barcode e-Ticket Kamera Live | Armansyah / Driver | Kamera Webcam (HW-02) | [`pages/scanner.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/scanner.php) | **Info:** Pencocokan tiket barcode ke database preadvice. | Hadapkan webcam/kamera HP ke tiket pre-advice. |
| **ACT-06** | Identifikasi Plat Nomor ANPR | Armansyah | CCTV OCR (HW-05) | [`pages/gate.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/gate.php) | **Info:** Foto tangkapan plat truk terverifikasi AI. | Muncul otomatis saat Gate-In dieksekusi. |
| **ACT-07** | Penimbangan SOLAS VGM 80 Ton | Armansyah | Timbangan 80T (HW-03) | [`pages/gate.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/gate.php) | **Fisik & Info:** Berat kotor terbit & bersertifikat VGM. | Klik Panel A Langkah 2 atau form timbangan di Gate. |
| **ACT-08** | Pencetakan Surat Jalan Gate-In (EIR) | Zulfikar | Thermal Printer A4 | [`pages/gate.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/gate.php) | **Info:** Lembar tanda terima pertukaran alat terbit. | Klik tombol *"Cetak EIR"* di log gerbang masuk. |
| **ACT-09** | Pembukaan Palang Barrier & LED Guidance | Armansyah | Controller (HW-06) | [`pages/integration.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/integration.php) | **Fisik:** Barrier terangkat, LED menampilkan jalur. | Pemicu HW-06 atau eksekusi Gate-In. |
| **ACT-10** | Akrual Biaya Penimbangan Otomatis | Afriansayah | Billing Engine | [`pages/billing.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/billing.php) | **Finansial:** Tagihan bertambah Rp 75.000 ke invoice. | Cek faktur kontainer di modul Billing. |

---

### KLUSTER 03: LAPANGAN PENUMPUKAN KONTAINER (CONTAINER YARD - YMS)

| ID | Nama Kegiatan | PIC / Aktor | Hardware / Media | Halaman | Dampak 3-Aliran | Cara Demonstrasi |
| :---: | :--- | :--- | :--- | :--- | :--- | :--- |
| **ACT-11** | Disposisi Instruksi Kerja VMT Kabin | Juan Gamaliel | VMT Terminal | [`pages/alat.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/alat.php) | **Info:** Operator RS menerima tugas penempatan slot. | Klik form *"Kirim Job Order VMT"* di modul Alat. |
| **ACT-12** | Deteksi Pengangkatan (Lift-Off) Spreader | Armansyah | Sensor Twistlock (HW-08) | [`pages/integration.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/integration.php) | **Fisik:** Spreader mengunci dan mengangkat boks. | Klik Panel A Langkah 3 di Pusat Integrasi. |
| **ACT-13** | Penentuan Posisi Slot RTK-GNSS | Juan Gamaliel | Antena RTK (HW-07) | [`pages/yard.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/yard.php) | **Fisik & Info:** Kontainer duduk di Blok-Bay-Row-Tier. | Cek peta grid slot 2D di modul Yard. |
| **ACT-14** | Relokasi / Reshuffling Antar Blok | Juan Gamaliel | VMT Crane | [`pages/yard.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/yard.php) | **Fisik:** Boks berpindah (contoh: Blok A ➔ Blok B). | Klik *"Relokasi"* pada salah satu kontainer di Yard. |
| **ACT-15** | Pemantauan Mesin & CAN-Bus Alat Berat | Armansyah | Telemetri RS | [`pages/alat.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/alat.php) | **Info:** Jam mesin, level solar, dan GPS terdeteksi. | Periksa kartu status RS-01 s/d RS-03 di modul Alat. |
| **ACT-16** | Akrual Biaya Lift-Off / Relokasi | Afriansayah | Billing Engine | [`pages/billing.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/billing.php) | **Finansial:** Penambahan biaya LoLo Rp 150.000–200.000. | Periksa buku besar faktur kontainer terkait. |

---

### KLUSTER 04: GUDANG CFS & KARGO LCL (CFS WAREHOUSE - WMS)

| ID | Nama Kegiatan | PIC / Aktor | Hardware / Media | Halaman | Dampak 3-Aliran | Cara Demonstrasi |
| :---: | :--- | :--- | :--- | :--- | :--- | :--- |
| **ACT-17** | Pembuatan Job Order Bongkar CFS | Juan Gamaliel | WMS Interface | [`pages/cfs.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/cfs.php) | **Info:** Tiket pekerjaan kargo LCL terdaftar di sistem. | Klik *"Registrasi Job Order CFS"* di modul CFS. |
| **ACT-18** | Alokasi Rampa Gudang D1 s/d D5 | Juan Gamaliel | Denah Rampa | [`pages/cfs.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/cfs.php) | **Fisik:** Truk kargo diarahkan ke rampa rampa gudang. | Pilih nomor rampa saat pembongkaran kargo. |
| **ACT-19** | Pemindaian Barcode Palet GS1 SSCC-18 | Armansyah / Tally | HHT Barcode (HW-04) | [`pages/integration.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/integration.php) | **Info:** Serial palet koli terverifikasi standar GS1. | Klik Panel B Skenario 1 di Pusat Integrasi. |
| **ACT-20** | Tally Sheet Digital & Quality Inspection | Juan Gamaliel | Form Tally Clerk | [`pages/cfs.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/cfs.php) | **Info:** Jumlah koli & status fisik tercatat resmi. | Periksa tabel manifest palet di modul CFS. |
| **ACT-21** | Akrual Biaya Stripping & Storage CFS | Afriansayah | Billing Engine | [`pages/billing.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/billing.php) | **Finansial:** Akrual tarif gudang Rp 450.000 masuk faktur. | Periksa rincian faktur CFS di modul Billing. |

---

### KLUSTER 05: RANTAI DINGIN KONTAINER REEFER (REEFER COLD CHAIN)

| ID | Nama Kegiatan | PIC / Aktor | Hardware / Media | Halaman | Dampak 3-Aliran | Cara Demonstrasi |
| :---: | :--- | :--- | :--- | :--- | :--- | :--- |
| **ACT-22** | Penyambungan Steker Listrik 380V | Armansyah / Teknisi | Smart Socket (HW-09) | [`pages/integration.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/integration.php) | **Fisik & Info:** Plug-In aktif, pasokan daya terhubung. | Klik Panel B Skenario 2 di Pusat Integrasi. |
| **ACT-23** | Monitoring Suhu HACCP Real-Time | Naufal / Teknisi | Modbus-RTU Sensor | [`pages/reefer.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/reefer.php) | **Info:** Grafik suhu stabil pada kisaran -18.5°C. | Buka grafik telemetri suhu di modul Reefer. |
| **ACT-24** | Deteksi Alarm Lonjakan Suhu Kritis | Naufal / TOC | Telemetri Spike | [`pages/integration.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/integration.php) | **Info:** Peringatan visual darurat di dashboard TOC. | Klik Panel B Skenario 3 di Pusat Integrasi. |
| **ACT-25** | Pencabutan Steker (Unplug) & Billing Listrik | Afriansayah | Smart Meter Socket | [`pages/reefer.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/reefer.php) | **Finansial:** Perhitungan kWh dan jam monitoring terbit. | Klik tombol *"Cabut Steker (Unplug)"* di modul Reefer. |

---

### KLUSTER 06: KEPABEANAN BEA CUKAI & KARANTINA (CUSTOMS CEISA 4.0)

| ID | Nama Kegiatan | PIC / Aktor | Hardware / Media | Halaman | Dampak 3-Aliran | Cara Demonstrasi |
| :---: | :--- | :--- | :--- | :--- | :--- | :--- |
| **ACT-26** | Sinkronisasi Host-to-Host CEISA 4.0 | Afriansayah | Hub Bea Cukai (HW-11)| [`pages/integration.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/integration.php) | **Info:** Data PIB/PEB & nomor segel terkirim ke KPU BC. | Klik Panel A Langkah 5 di Pusat Integrasi. |
| **ACT-27** | Penerbitan SPPB Otomatis (Jalur Hijau) | Afriansayah / Pabean | CEISA Auto-Clearance| [`pages/customs.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/customs.php) | **Info:** Surat Persetujuan Pengeluaran terbit (92% rilis). | Klik *"Rilis SPPB"* pada kontainer di modul Bea Cukai. |
| **ACT-28** | Penetapan Tahanan Pabean (Jalur Merah) | Afriansayah / Pabean | Red Lane Flag | [`pages/integration.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/integration.php) | **Info:** Kontainer dikunci statusnya (*Customs Hold*). | Klik Panel B Skenario 5 di Pusat Integrasi. |
| **ACT-29** | Pemeriksaan Fisik & Gantry X-Ray 6 MeV | Armansyah / Petugas | X-Ray Scanner Nuctech| [`pages/customs.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/customs.php) | **Fisik:** Boks dipindai di kanopi behandle. | Periksa log behandle di modul Bea Cukai. |
| **ACT-30** | Akrual Biaya Pemeriksaan Behandle | Afriansayah | Billing Engine | [`pages/billing.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/billing.php) | **Finansial:** Biaya behandle Rp 350.000 masuk tagihan. | Cek faktur kontainer jalur merah di Billing. |

---

### KLUSTER 07: INTERMODAL RAIL SIDING KERETA API (MULTIMODAL RAIL)

| ID | Nama Kegiatan | PIC / Aktor | Hardware / Media | Halaman | Dampak 3-Aliran | Cara Demonstrasi |
| :---: | :--- | :--- | :--- | :--- | :--- | :--- |
| **ACT-31** | Penghitungan Gandar Rangkaian KA | Armansyah | Axle Counter (HW-10) | [`pages/integration.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/integration.php) | **Info:** 126 gandar terhitung saat KA masuk rel siding. | Klik Panel B Skenario 4 di Pusat Integrasi. |
| **ACT-32** | Penyusunan Manifest Rangkaian KA 2518 | Juan Gamaliel | KAI EDI Interface | [`pages/intermodal.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/intermodal.php) | **Info:** Manifest 20–30 gerbong datar (PPCW) terdaftar. | Buka daftar gerbong siding di modul Intermodal. |
| **ACT-33** | Alih Muat (Cross-Docking) KA ↔ Truk | Juan Gamaliel | Reach Stacker Siding | [`pages/intermodal.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/intermodal.php) | **Fisik:** Pemindahan langsung boks dari truk ke gerbong. | Klik *"Alih Muat ke Kereta"* di modul Intermodal. |
| **ACT-34** | Pembaruan Status On-Rail ke Pelabuhan | Juan Gamaliel | YMS Status Engine | [`pages/kontainer.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/kontainer.php) | **Info:** Status kontainer berubah menjadi `on_rail`. | Periksa filter siding KA di modul Kontainer. |

---

### KLUSTER 08: BILLING, FINANSIAL & SINKRONISASI ERP ODOO 17 (FINANCE)

| ID | Nama Kegiatan | PIC / Aktor | Hardware / Media | Halaman | Dampak 3-Aliran | Cara Demonstrasi |
| :---: | :--- | :--- | :--- | :--- | :--- | :--- |
| **ACT-35** | Agregasi Seluruh Komponen Tarif Terminal | Afriansayah | Billing Engine | [`pages/billing.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/billing.php) | **Finansial:** Akrual total penimbangan, LoLo, CFS, & inap. | Buka rekap tagihan di modul Billing. |
| **ACT-36** | Penerbitan Faktur Billing Format Resmi A4 | Afriansayah | PDF/Print Engine | [`pages/billing.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/billing.php) | **Finansial:** Faktur resmi standar Pelindo/MTI terbit. | Klik tombol *"Cetak Faktur (A4)"* di modul Billing. |
| **ACT-37** | Validasi Pembayaran Kasir / Virtual Account | Afriansayah | Payment Gateway | [`pages/billing.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/billing.php) | **Finansial:** Status tagihan berubah menjadi `PAID`. | Klik tombol *"Bayar Lunas"* pada daftar faktur. |
| **ACT-38** | Sinkronisasi Host-to-Host Odoo 17 Cloud | Afriansayah | Odoo API Client | [`pages/integration.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/integration.php) | **Finansial:** Jurnal `account.move` terkirim ke Odoo Cloud. | Klik Panel A Langkah 6 atau tombol sync Odoo. |

---

### KLUSTER 09: GERBANG KELUAR & KLIRING TERPADU (GATE-OUT DUAL-CLEARANCE)

| ID | Nama Kegiatan | PIC / Aktor | Hardware / Media | Halaman | Dampak 3-Aliran | Cara Demonstrasi |
| :---: | :--- | :--- | :--- | :--- | :--- | :--- |
| **ACT-39** | Validasi Syarat Ganda (Dual-Clearance) | Zulfikar / Gate | Interlock Controller | [`pages/gate.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/gate.php) | **Info:** Validasi ketat: Wajib Faktur Lunas & SPPB Rilis. | Cek status rilis gate-out pada truk aktif. |
| **ACT-40** | Deteksi ANPR Keluar & Buka Palang Keluar | Armansyah | Barrier Gate (HW-06) | [`pages/gate.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/gate.php) | **Fisik:** Barrier outbound terbuka, truk keluar. | Klik *"Gate-Out Confirmation"* di modul Gate. |
| **ACT-41** | Penerbitan Surat Izin Jalan Keluar (Gate Pass) | Zulfikar | Print Engine A4 | [`pages/gate.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/gate.php) | **Info:** Tiket Delivery Order Gate-Out terbit resmi. | Klik tombol *"Cetak Gate Pass"* saat truk keluar. |

---

### KLUSTER 10: PENGENDALIAN SENTRAL TOC & VISUAL SPASIAL (CENTRAL CONTROL)

| ID | Nama Kegiatan | PIC / Aktor | Hardware / Media | Halaman | Dampak 3-Aliran | Cara Demonstrasi |
| :---: | :--- | :--- | :--- | :--- | :--- | :--- |
| **ACT-42** | TOC Universal Asset & Cargo Live Locator | Naufal / Supervisor | API `track_asset.php` | [`pages/beranda.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/beranda.php) | **Info:** Deteksi instan kontainer/truk berada di mana. | Ketik nomor kontainer di bilah atas Beranda. |
| **ACT-43** | Digital Twin 2D/3D Berbasis Data Nyata | Zulfikar | WebGL Three.js & SSE | [`pages/digital_twin.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/digital_twin.php) | **Fisik & Info:** Animasi 3D bergerak HANYA saat data masuk. | Buka modul Digital Twin saat event dipicu. |
| **ACT-44** | Tur Fasilitas Simulasi 3D Bebas (Sandbox) | Juan Gamaliel | Three.js Sandbox | [`pages/simulator.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/simulator.php) | **Visual:** Eksplorasi interaktif fasilitas 35 Ha tanpa ubah DB. | Buka modul Simulator 3D dan gerakkan kamera. |
| **ACT-45** | SCADA Telemetry Uptime & Jitter Monitoring | Armansyah | Telemetry Engine | [`pages/beranda.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/beranda.php) | **Info:** Uptime 99.9% dan latensi EDI termonitor per detik. | Periksa baris konektivitas host-to-host di Beranda. |

---

### KLUSTER 11: KEAMANAN CYBER API & DEAD LETTER QUEUE (SECURITY & RESILIENCE)

| ID | Nama Kegiatan | PIC / Aktor | Hardware / Media | Halaman | Dampak 3-Aliran | Cara Demonstrasi |
| :---: | :--- | :--- | :--- | :--- | :--- | :--- |
| **ACT-46** | Penerbitan Token Akses OAuth 2.0 Bearer | Zulfikar | OAuth 2.0 Server | [`pages/integration.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/integration.php) | **Info:** Token JWT terenkripsi dengan masa berlaku 1 jam. | Cek panel token OAuth 2.0 di Pusat Integrasi. |
| **ACT-47** | Validasi Tanda Tangan Kriptografi HMAC | Zulfikar | HMAC-SHA256 Engine | [`pages/integration.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/integration.php) | **Info:** Verifikasi integritas payload sensor telemetri. | Terverifikasi otomatis pada setiap event hardware. |
| **ACT-48** | Penegakan Scope Akses (Least Privilege) | Zulfikar | API Security Gateway | [`pages/integration.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/integration.php) | **Keamanan:** Penolakan HTTP 403 jika scope tidak cocok. | Klik tombol uji *"Scope Violation"* di Pusat Integrasi. |
| **ACT-49** | Karantina Data Terkontaminasi ke DLQ | Zulfikar / QA | Dead Letter Queue | [`pages/integration.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/integration.php) | **Keamanan:** Paket data manipulasi dicegat di tabel DLQ. | Klik tombol uji *"MITM Tampering"* di Pusat Integrasi. |
| **ACT-50** | Audit Forensik & Replay Paket Data DLQ | Naufal / Zulfikar | DLQ Console | [`pages/integration.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/integration.php) | **Keamanan:** Operator menginspeksi muatan dan merilis data. | Buka modal inspeksi DLQ di Pusat Integrasi. |

---

## 4. PANDUAN PENAMBAHAN KEGIATAN BARU (EXPANDABILITY: ACT-51+)

Dokumen ini bersifat **fleksibel dan dapat bertambah seiring pengembangan aplikasi**. Setiap penambahan kegiatan operasional baru di masa mendatang wajib mengikuti konvensi berikut:

1. **Penomoran Standar:** Gunakan kode berurutan `ACT-51`, `ACT-52`, dst.
2. **Kategori Kluster:** Tetapkan ke salah satu dari 11 Kluster atau buat kluster baru jika merupakan zona fungsional baru (misalnya *Kluster 12: Bengkel Maintenance & Repair M&R* atau *Kluster 13: Manajemen K3 & Lingkungan*).
3. **Atribut Wajib:**
   * **PIC Penanggung Jawab:** Ditugaskan ke salah satu dari 5 anggota tim resmi.
   * **Perangkat Terkait:** Hardware fisik, sensor IoT, atau modul software.
   * **Dampak 3-Aliran:** Wajib mengidentifikasi dampaknya terhadap **Aliran Fisik**, **Aliran Informasi**, atau **Aliran Finansial**.
   * **Prosedur Uji Coba:** Menjelaskan cara dosen penguji memverifikasi kegiatan tersebut di layar.

---

## 5. SKENARIO DEMONSTRASI MULTI-DEVICE 5 PERANGKAT

Saat mempresentasikan 50 kegiatan ini di depan dosen penguji, alokasikan 5 laptop/perangkat sesuai matriks peran:

* **Laptop 1 (Proyektor / Dosen Penguji / Supervisor TOC):**  
  Membuka [`pages/beranda.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/beranda.php). Mengamati metrik KPI, grafik okupansi, notifikasi SCADA, dan mengetik nomor kontainer di **TOC Universal Asset & Cargo Live Locator** untuk membuktikan posisi kargo secara langsung.
* **Laptop 2 (Zulfikar / Armansyah - Gate & Scanner):**  
  Membuka [`pages/scanner.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/scanner.php) & [`pages/gate.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/gate.php). Menghadapkan kamera live ke barcode tiket, mengeksekusi Gate-In dan penimbangan VGM.
* **Laptop 3 (Juan Gamaliel - Yard Crane Operator VMT):**  
  Membuka [`pages/yard.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/yard.php) & [`pages/alat.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/alat.php). Menerima disposisi penempatan, menaruh kontainer di slot Blok A/B/C, dan melakukan relokasi.
* **Laptop 4 (Armansyah / Naufal - CFS Warehouse & Reefer Specialist):**  
  Membuka [`pages/cfs.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/cfs.php) & [`pages/reefer.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/reefer.php). Melakukan stripping palet kargo LCL dan mencolok steker daya reefer 380V.
* **Laptop 5 (Afriansayah - Customs Officer & Cashier ERP):**  
  Membuka [`pages/customs.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/customs.php) & [`pages/billing.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/billing.php). Menerbitkan SPPB Jalur Hijau/Merah, mencetak faktur resmi A4, dan melakukan sinkronisasi live ke Odoo 17 Cloud.

---
*Dokumen ini disahkan oleh Tim Pengembang CIDP DPMS 35 Ha (Zulfikar J. F., Naufal A. H., Armansyah M., Afriansayah A., Juan G.) sebagai acuan resmi demonstrasi dan pengujian sistem.*
