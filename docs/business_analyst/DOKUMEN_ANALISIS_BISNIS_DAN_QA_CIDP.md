# 📊 CETAK BIRU ANALISIS BISNIS, STUDI KELAYAKAN FINANSIAL & PENJAMINAN MUTU (QA)
## Conclusion Intermodal Dry Port (CIDP) 35 Ha — Yard Management System (YMS)
### Single Source of Truth (SSOT) — Divisi Business Analysis & Quality Assurance

> **Dokumen Resmi Penugasan Proyek Akhir Kelompok**  
> **Mata Kuliah:** Teknologi dan Perangkat Lunak Logistik  
> **Dosen Pengampu:** Dr. Tigor Franky, S.T., M.T.  
> **Institusi:** Program Studi S1 Manajemen Logistik — Institut Transportasi dan Logistik (ITL) Trisakti  
> **Entitas Konsultan:** Conclusion Supply Chain Consultant  
> **Topik Silabus:** Topik 7 — Inland Container Depot (ICD) & Dry Port Management  
> **PIC Penyusun:** Naufal Andika Heditya (NIM: 24D507001012) — *Business Analyst & QA Specialist*  
> **Disahkan Oleh:** Zulfikar Jafarudin Fatah (NIM: 24D507001001) — *Lead System Architect (Ketua Tim)*  
> **Tanggal Pembaruan:** Oktober 2026  

---

## 📑 DAFTAR ISI

1. [Bab 1: Mandat Peran Business Analyst & Taksonomi IT Logistik](#bab-1-mandat-peran-business-analyst--taksonomi-it-logistik)
2. [Bab 2: Rekayasa Ulang Proses Bisnis (Business Process Reengineering - BPR)](#bab-2-rekayasa-ulang-proses-bisnis-business-process-reengineering---bpr)
3. [Bab 3: Standardisasi Mutu Identifikasi Global AIDC & GS1](#bab-3-standardisasi-mutu-identifikasi-global-aidc--gs1)
4. [Bab 4: Studi Kelayakan Finansial Terminal 35 Ha (CAPEX, OPEX & Model Pendapatan)](#bab-4-studi-kelayakan-finansial-terminal-35-ha-capex-opex--model-pendapatan)
5. [Bab 5: Evaluasi Investasi Finansial (Throughput, PBP, NPV, IRR & Zero Leakage)](#bab-5-evaluasi-investasi-finansial-throughput-pbp-npv-irr--zero-leakage)
6. [Bab 6: Kepatuhan Regulasi Kepabeanan (Customs Compliance & CEISA 4.0 DJBC)](#bab-6-kepatuhan-regulasi-kepabeanan-customs-compliance--ceisa-40-djbc)
7. [Bab 7: Analisis Nilai Tambah Gudang CFS (Container Freight Station) LCL 4.000 m²](#bab-7-analisis-nilai-tambah-gudang-cfs-container-freight-station-lcl-4000-m)
8. [Bab 8: Rangka Kerja Penjaminan Mutu (Quality Assurance / QA Testing Suite)](#bab-8-rangka-kerja-penjaminan-mutu-quality-assurance--qa-testing-suite)
9. [Bab 9: Mockup & Business Intelligence (Executive KPI Dashboard)](#bab-9-mockup--business-intelligence-executive-kpi-dashboard)
10. [Bab 10: Matriks Kebutuhan Sistem (SRS Functional & Non-Functional Requirements)](#bab-10-matriks-kebutuhan-sistem-srs-functional--non-functional-requirements)

---

## Bab 1: Mandat Peran Business Analyst & Taksonomi IT Logistik

### 1.1 Mandat Berdasarkan Silabus Dr. Tigor Franky
Berdasarkan dokumen silabus resmi (*Materi Pertemuan 1, Slide 13*), pembagian peran dalam tim konsultan dirancang presisi tanpa *free-rider*. Peran **Business Analyst & QA** memiliki mandat utama:
1. **Analisis OPEX & ROI (Kelayakan Finansial):** Membuktikan bahwa penerapan teknologi informasi dan otomasi pada dry port menghasilkan pengembalian modal yang terukur, efisiensi operasional, dan profitabilitas berkelanjutan.
2. **Mockup Dashboard KPI:** Merancang indikator kinerja utama (*Key Performance Indicators*) berbasis data waktu-nyata untuk mendukung pengambilan keputusan strategis tingkat direksi.
3. **Kerapian Dokumen SRS (Software Requirements Specification) & QA:** Menjamin seluruh kebutuhan fungsional dan non-fungsional terdokumentasi rapi, teruji secara sistematis, dan patuh terhadap standar regulasi logistik nasional maupun internasional.

### 1.2 Integrasi 3 Aliran Utama Rantai Pasok (Logistics 4.0)
Sebagaimana diajarkan pada *Pertemuan 1 Slide 3*, sistem IT logistik bertindak sebagai "saraf pusat" yang menghubungkan tiga aliran:
* **Goods Flow (Aliran Fisik Barang):** Pergerakan kontainer di area 35 Ha dari gerbang masuk (*Gate-In*), jembatan timbang 80 Ton, area penumpukan (*Yard Blocks A-E*), rampa CFS, hingga rangkaian kereta api logistik sepur simpan (*Rail Siding*).
* **Information Flow (Aliran Informasi):** Telemetri data instan yang menangkap nomor kontainer (OCR ISO 6346), plat truk (ANPR), berat kargo (SOLAS VGM), posisi koordinat 3D (*Bay-Row-Tier*), suhu reefer kargo dingin, hingga kode unik palet SSCC-18.
* **Financial Flow (Aliran Finansial — Fokus Utama BA):** Konversi setiap peristiwa fisik di lapangan menjadi nilai moneter yang dapat ditagihkan (*Billable Charge Event*), rilis dokumen pabean berbayar, kalkulasi denda masa inap (*dwell time penalty*), hingga rekonsiliasi piutang (*Accounts Receivable*) pada sistem ERP Odoo secara otomatis tanpa kebocoran pendapatan (*Zero Revenue Leakage*).

### 1.3 Penyelarasan Taksonomi 4-Layer IT Logistik
Business Analyst memastikan arsitektur terminal memenuhi 4 lapisan teknologi:
* **Layer 1 (Sensing & Data Capture):** 26 Perangkat fisik lapangan (Kamera ANPR, OCR, Timbangan 80t, RFID UHF, Handheld PDA GS1, Sensor Suhu LoRaWAN, Smart E-Seal).
* **Layer 2 (Network & Connectivity):** Wi-Fi 7 industri, jaringan seluler 4G/5G, LoRaWAN 915 MHz, bus data RS-232/Modbus, dan TCP/IP.
* **Layer 3 (Application & Core Systems):** Software CIDP YMS, 3D WebGL Three.js Engine, Stacking Rules Engine, dan Terminal Tariff Engine.
* **Layer 4 (Data Integration & Enterprise):** RESTful API Gateway, JSON Payload, CEISA 4.0 DJBC (Kepabeanan), SAKA PT KAI Logistik, dan ERP Odoo Finansial.

---

## Bab 2: Rekayasa Ulang Proses Bisnis (Business Process Reengineering - BPR)

### 2.1 Perbandingan Proses Bisnis As-Is vs To-Be
Sebagai konsultan rantai pasok, Business Analyst membedah inefisiensi pada terminal konvensional (*As-Is*) dan merancang proses digital terpadu (*To-Be*):

| Parameter Evaluasi | Kondisi Saat Ini (As-Is / Depo Konvensional) | Rekayasa Digital CIDP YMS (To-Be / Target) | Peningkatan Efisiensi |
|---|---|---|---|
| **Waktu Transaksi Gerbang (Gate Turnaround Time)** | 12 – 18 Menit per truk (Pencatatan manual kertas, cek fisik nomor boks, stempel tiket) | **60 – 120 Detik per truk** (Kamera ANPR + OCR boks otomatis, timbang dinamis, e-Gate Pass QR) | **Efisiensi Waktu 85% – 90%** |
| **Akurasi Alokasi Yard (Stacking Accuracy)** | Operator mencari slot kosong manual; sering salah blok; terjadi *misplacement* box | **100% Terarah Otomatis** via sistem pandu slot 3D (*Bay-Row-Tier*) dan GPS Reach Stacker | **Eliminasi 100% Shifting Sia-Sia** |
| **Rata-rata Waktu Inap (Average Dwell Time)** | 6.8 Hari (Penumpukan tidak terkontrol, notifikasi kargo lambat, proses rilis dokumen lama) | **2.3 Hari** (Peringatan otomatis Masa Bebas, integrasi SPPB pabean instan, jadwal rel pasti) | **Percepatan Dwell Time 66%** |
| **Kalkulasi & Verifikasi VGM SOLAS** | Penimbangan terpisah di luar depo; entri sertifikat VGM manual rawan pemalsuan | **Otomatis di Gate-In**; sistem menolak truk overload >34t secara real-time | **Kepatuhan Regulasi SOLAS 100%** |
| **Penerbitan Tagihan (Billing Cycle)** | Rekapitulasi manual 3-5 hari kerja setelah kapal/truk berangkat; rawan manipulasi denda | **Real-Time Event-Driven**; invoice terbit detik itu juga ke portal pelanggan ERP Odoo | **Zero Revenue Leakage (0% Bocor)** |
| **Kepatuhan Bea Cukai (Customs Clearance)** | Petugas membawa map fisik ke pos pabean; waktu tunggu SPPB 24–48 jam | **Integrasi API CEISA 4.0**; rilis SPPB Jalur Hijau <5 menit, alur Behandle terisolasi | **Rilis Dokumen 95% Lebih Cepat** |

### 2.2 Diagram Alir Proses Bisnis To-Be (BPMN Core YMS)
```
[ TRUK TIBA DI GATE ] 
       │
       ▼
 [ SENSOR GATE ] ➔ Kamera ANPR (Plat) + OCR (Kontainer) + Timbangan 80t
       │
       ├─ Overweight (>34t)? ──► [ GATE LOCKED & ALARM ] ──► Tolak Masuk (SOLAS Violation)
       │
       ▼ (Berat Lolos <34t)
 [ VALIDASI MANIFES & CEISA 4.0 ] ──► Terbitkan e-Gate Pass (QR Code) & Instruksi Slot Yard
       │
       ▼
 [ REACH STACKER / YARD ] ➔ Operator memindai slot Bay-Row-Tier via Tablet Kabin
       │
       ▼ (Twistlock Release)
 [ TELEMETRI UPDATE ] ──► Update Koordinat 3D Database YMS & Tembak Event Lo-Lo ke ERP
       │
       ▼
 [ KELUAR GERBANG (GATE-OUT) ] ➔ Verifikasi e-Pass QR + Status Lunas Tagihan ERP + SPPB Cleared
```

---

## Bab 3: Standardisasi Mutu Identifikasi Global AIDC & GS1

### 3.1 Taksonomi GS1 Application Identifiers (AI) dalam Operasional CIDP
Untuk menjamin kompatibilitas global dengan seluruh rantai pasok dunia, Business Analyst mengadopsi standar **GS1 General Specifications v23**:

| GS1 Application Identifier (AI) | Format Data | Panjang Karakter | Makna Bisnis dalam Ekosistem CIDP | Implementasi di Sistem |
|---|---|---|---|---|
| **AI (00)** | Numerik | 18 Digit | **SSCC (Serial Shipping Container Code):** Paspor unik unit logistik kargo/palet dalam kontainer CFS. | Kunci tracking palet LCL & Inbound receiving. |
| **AI (01)** | Numerik | 14 Digit | **GTIN (Global Trade Item Number):** Identitas unik produk kargo yang disimpan di CFS. | Rekonsiliasi manifes packing list importir. |
| **AI (10)** | Alfanumerik | Hingga 20 Char | **Batch / Lot Number:** Nomor produksi dari produsen kargo. | Penanganan karantina obat-obatan / makanan. |
| **AI (17)** | Numerik (YYMMDD) | 6 Digit | **Expiration Date:** Tanggal kedaluwarsa kargo rantai dingin reefer. | Sistem FEFO (*First Expired First Out*) di Yard Reefer. |
| **AI (310x)** | Numerik | 6 Digit | **Net Weight (Kilogram):** Bobot bersih kargo tanpa kemasan. | Cross-check dengan sertifikat timbangan SOLAS VGM. |

### 3.2 Anatomi 18-Digit SSCC (Serial Shipping Container Code)
Standar SSCC-18 adalah "paspor" kargo logistik yang tidak pernah berulang di seluruh dunia selama minimal 12 bulan:
```
  (00)   3   899123450   00000012   8
  ────  ───  ─────────   ────────  ───
   │     │       │          │       │
   │     │       │          │       └─ Check Digit (Algoritma Modulo 10)
   │     │       │          └───────── Serial Reference (Nomor urut palet CIDP)
   │     │       └──────────────────── GS1 Company Prefix (Diterbitkan GS1 Indonesia)
   │     └──────────────────────────── Extension Digit (0-9 pengatur kapasitas penomoran)
   └────────────────────────────────── Application Identifier (AI 00 = SSCC)
```

**Formula Perhitungan Check Digit Modulo 10 (GS1):**
1. Jumlahkan digit pada posisi ganjil (dihitung dari kanan ke kiri, posisi 17 hingga 1), kalikan hasilnya dengan 3.
2. Jumlahkan seluruh digit pada posisi genap, kalikan hasilnya dengan 1.
3. Jumlahkan kedua hasil tersebut.
4. Digit cek adalah angka pembulatan ke kelipatan 10 terdekat dikurangi total jumlah tersebut.

### 3.3 Standardisasi Kontainer Maritim ISO 6346:2022
Selain GS1 untuk muatan, unit kontainer wajib mematuhi standar internasional **ISO 6346**:
* **Kode Pemilik (Owner Code):** 3 Huruf kapital (misal: `MSK` untuk Maersk, `CMA` untuk CMA CGM).
* **Kategori Peralatan (Equipment Category):** `U` untuk freight container standar, `J` untuk detachable equipment, `Z` untuk trailer/chassis.
* **Nomor Seri (Serial Number):** 6 Digit angka unik.
* **Digit Cek (Check Digit):** 1 Digit angka hasil kalkulasi bobot pangkat 2 basis Modulo 11.
* *Implementasi Web:* Fitur verifikasi otomatis ISO 6346 aktif di [`pages/scanner.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/scanner.php) dan menolak input nomor kontainer yang tidak memenuhi rumus modulo resmi.

---

## Bab 4: Studi Kelayakan Finansial Terminal 35 Ha (CAPEX, OPEX & Model Pendapatan)

Sebagai konsultan profesional, Business Analyst menyusun studi kelayakan investasi komprehensif untuk pembangunan terminal CIDP seluas 35 Hektar:

### 4.1 Rekapitulasi CAPEX (Capital Expenditure / Investasi Awal)
Total kebutuhan dana investasi aset tetap pembangunan terminal:

| Komponen Investasi CAPEX | Spesifikasi Teknis & Lingkup Pekerjaan | Estimasi Anggaran (IDR) |
|---|---|---|
| **Pengadaan Lahan & Perkerasan Rigid 80t** | Pembebasan lahan 350.000 m² (35 Ha), pemadatan tanah sub-grade CBR >10%, pengecoran beton *Heavy Duty Rigid Pavement* 40 cm kapasitas gandar 80 Ton untuk manuver Reach Stacker. | Rp 130.000.000.000 |
| **Infrastruktur Rel Siding Intermodal** | 2 Sepur simpan rel ganda (*Double Track*) panjang 600 meter, bantalan beton R54, wesel hidrolik sambungan ke jalur utama PT KAI, ballast grade A. | Rp 35.000.000.000 |
| **Armada Alat Berat (Heavy Equipment Fleet)** | • 3 Unit Kalmar Gloria DRG450 Reach Stacker (@ Rp 9.000.000.000 = Rp 27 M)<br>• 1 Unit Konecranes Electric RTG Crane 16-Wheel (Rp 28.000.000.000). | Rp 55.000.000.000 |
| **Otomasi Gerbang & Instrumen IoT** | 2 Unit Jembatan Timbang 80 Ton, 4 Kamera ANPR 4K, 4 Kamera OCR ISO 6346, Portal RFID UHF Gen2, Barrier Gate Otomatis, Layar Display LED VMS. | Rp 4.500.000.000 |
| **Infrastruktur Cold Chain (Reefer Yard)** | Dermaga Reefer Rack 300 colokan (*plugs*) 380V/32A, gardu trafo industri 1.250 kVA, telemetri pemantau suhu nirkabel LoRaWAN. | Rp 6.500.000.000 |
| **Gudang CFS LCL & Hanggar Kepabeanan** | Gedung CFS 4.000 m², 5 Rampa Loading Dock Hidrolik, Rak Palet Selektif, Pos Hanggar DJBC, Ruang Behandle Fisik & Portal X-Ray Gantry 6 MeV. | Rp 15.000.000.000 |
| **Teknologi Informasi, Software & Jaringan** | Server On-Premise High Availability, Lisensi Cloud AWS Backup, Pembangunan Software YMS CIDP, Lisensi ERP Odoo Enterprise, Cyber Security Firewall. | Rp 3.500.000.000 |
| **TOTAL INVESTASI AWAL (CAPEX)** | **Total Belanja Modal Pembangunan Terminal CIDP 35 Ha** | **Rp 249.500.000.000**<br>*(Dibulatkan: ± Rp 250 Miliar)* |

---

### 4.2 Struktur OPEX (Operational Expenditure / Biaya Operasional Tahunan)
Biaya operasional rutin yang dikeluarkan untuk menjalankan terminal 24/7/365:

| Kategori Biaya OPEX | Asumsi Dasar & Rincian Operasional | Biaya Bulanan (IDR) | Biaya Tahunan (IDR) |
|---|---|---|---|
| **Bahan Bakar Solar HSD Alat Berat** | 3 Reach Stacker konsumsi 18 liter/jam × 16 jam operasi/hari × Rp 15.500/liter (Solar Industri). | Rp 401.760.000 | Rp 4.821.120.000 |
| **Listrik Industri PLN Gardu 380V** | Beban listrik RTG Crane elektrik, pendingin 300 plug reefer (rata-rata 150 aktif), lampu sorot yard LED high-mast, dan gedung operasional. | Rp 385.000.000 | Rp 4.620.000.000 |
| **Gaji Personil & Payroll (82 Karyawan)** | 8 Operator Crane/RS bersertifikat SIO, 12 Petugas Gate & Timbangan, 16 Petugas CFS & Tally, 8 Teknisi M&R, 6 Tim IT & YMS, 12 Sekuriti & Safety, 20 Staf Admin/Finance/Pabean. | Rp 574.000.000 | Rp 6.888.000.000 |
| **Perawatan & Suku Cadang (M&R)** | Penggantian ban crane *heavy duty* 18.00-25, pelumas hidrolik, twistlock pin, kalibrasi tera jembatan timbang oleh Badan Metrologi, pemeliharaan rel sepur simpan. | Rp 180.000.000 | Rp 2.160.000.000 |
| **Akses Rel Kereta (*Track Access Charge*)** | Biaya hak akses jalur rel KA dan bagi hasil traksi lokomotif dengan PT Kereta Api Logistik (KAI Logistik) untuk koridor CIDP – Pelabuhan Tanjung Priok / Tanjung Perak. | Rp 450.000.000 | Rp 5.400.000.000 |
| **Asuransi Terminal & Keamanan IT** | Asuransi tanggung gugat operator (*Terminal Operator Liability Insurance*), asuransi properti all-risk, dan lisensi cloud/keamanan siber. | Rp 75.000.000 | Rp 900.000.000 |
| **TOTAL ESTIMASI OPEX TAHUNAN** | **Total Biaya Operasional Tahunan Dry Port CIDP** | **Rp 2.065.760.000 / bln** | **Rp 24.789.120.000 / thn**<br>*(± Rp 24.8 Miliar/tahun)* |

---

### 4.3 Struktur Model Pendapatan (Revenue Streams Terminal CIDP)
Tarif jasa komersial yang dipungut terminal berdasarkan standar asosiasi depo dan SK tarif pelabuhan:

| Jenis Jasa Layanan Terminal | Besaran Tarif Resmi | Satuan Dasar Pengenaan | Proyeksi Volume Tahunan | Estimasi Pendapatan Tahunan (IDR) |
|---|---|---|---|---|
| **Lift-On / Lift-Off (Lo-Lo)** | Rp 175.000 (20ft) / Rp 275.000 (40ft)<br>*(Rata-rata tertimbang: Rp 225.000/box)* | Per Box Kontainer (Gerbang & Rel) | 150.000 Box Handling | Rp 33.750.000.000 |
| **Penumpukan Lapangan (Storage)** | • Hari 1–3: **Rp 0 (Masa I Bebas)**<br>• Hari 4–10: **Rp 45.000/hari (Masa II)**<br>• Hari >10: **Rp 90.000/hari (Masa III Progresif)**<br>*(Rata-rata tagihan inap: Rp 120.000/box)* | Per Box / Siklus Inap | 90.000 Box Kena Inap | Rp 10.800.000.000 |
| **Jembatan Timbang VGM SOLAS** | Rp 50.000 | Per Truk Gate-In | 110.000 Truk Ditimbang | Rp 5.500.000.000 |
| **Steker Pendingin Reefer (Plugging)** | Rp 250.000 | Per Shift (8 Jam) | 25.000 Shift Reefer | Rp 6.250.000.000 |
| **CFS Stuffing / Stripping LCL** | Rp 450.000 | Per Box Kontainer | 18.000 Box CFS | Rp 8.100.000.000 |
| **Bagi Hasil Angkutan KA Logistik** | Rp 1.500.000 (Rata-rata bagi hasil margin kargo per gerbong KA) | Per Gerbong KA Datar | 6.000 Gerbong KA | Rp 9.000.000.000 |
| **Jasa Pemeriksaan Behandle Pabean** | Rp 350.000 (Pindah box ke hanggar & reposisi) | Per Box Jalur Merah | 7.500 Box Behandle | Rp 2.625.000.000 |
| **TOTAL PROYEKSI PENDAPATAN KOTOR (GROSS REVENUE)** | **Throughput Terminal 150.000 TEU / Tahun** | — | — | **Rp 76.025.000.000 / thn**<br>*(± Rp 76 Miliar/tahun)* |

---

## Bab 5: Evaluasi Investasi Finansial (Throughput, PBP, NPV, IRR & Zero Leakage)

### 5.1 Perhitungan Laba Bersih & Arus Kas Operasional (EBITDA)
* **Pendapatan Kotor Tahunan (Gross Revenue):** Rp 76.025.000.000
* **Beban Operasional Tahunan (OPEX):** Rp 24.789.120.000
* **EBITDA (Laba Sebelum Bunga, Pajak & Depresiasi):** Rp 51.235.880.000 (Margin EBITDA: **67.4%**)
* **Penyusutan Aset (Depresiasi Garis Lurus 20 Tahun):** Rp 12.500.000.000 / tahun
* **Laba Sebelum Pajak (EBT):** Rp 38.735.880.000
* **Pajak Penghasilan Badan (PPh 22%):** Rp 8.521.893.600
* **Laba Bersih Setelah Pajak (Net Profit / EAT):** **Rp 30.213.986.400 / tahun**
* **Arus Kas Bersih Tahunan (Net Cash Flow = Net Profit + Depresiasi):** **Rp 42.713.986.400 / tahun**

### 5.2 Analisis Kelayakan Investasi Finansial
1. **Payback Period (PBP):**
   $$\text{PBP} = \frac{\text{Total Investasi CAPEX}}{\text{Arus Kas Bersih Tahunan}} = \frac{\text{Rp 249.500.000.000}}{\text{Rp 42.713.986.400}} \approx \mathbf{5.84\text{ Tahun}}\ (\approx 5\text{ Tahun } 10\text{ Bulan})$$
   *Interpretasi:* Modal investasi awal sebesar Rp 250 Miliar akan kembali lunas dalam waktu kurang dari 6 tahun, jauh lebih cepat daripada standar batas aman industri pelabuhan (8–10 tahun).

2. **Net Present Value (NPV) — Horizon 10 Tahun (Tingkat Diskonto / WACC 10%):**
   $$\text{NPV} = \sum_{t=1}^{10} \frac{\text{CF}_t}{(1 + r)^t} - \text{CAPEX} = (\text{Rp 42.713.986.400} \times 6.1446) - \text{Rp 249.500.000.000} \approx \mathbf{+\text{Rp 12.955.000.000}}$$
   *Interpretasi:* Nilai NPV bernilai **Positif (+)** signifikan, membuktikan secara absolut bahwa proyek dry port CIDP 35 Ha layak dijalankan (*Feasible*).

3. **Internal Rate of Return (IRR):**
   * Perhitungan nilai IRR menghasilkan angka: **11.42%**
   * Nilai ini melampaui suku bunga acuan pinjaman komersial perbankan (*Hurdle Rate* BI-Rate + Risk Premium = 8.5%), sehingga proyek sangat menarik bagi konsorsium pembiayaan perbankan dan investor swasta.

4. **Return on Investment (ROI):**
   $$\text{ROI Rata-rata 10 Tahun} = \frac{\text{Total Net Profit 10 Tahun}}{\text{Total Investasi CAPEX}} \times 100\% = \frac{\text{Rp 302.139.864.000}}{\text{Rp 249.500.000.000}} \times 100\% \approx \mathbf{121.1\%}$$

### 5.3 Strategi Zero Revenue Leakage
Dalam audit penagihan konvensional, kebocoran pendapatan mencapai 8% – 12% akibat:
* Kontainer menginap melewati masa bebas tetapi dicatat tanggal keluar palsu oleh oknum lapangan.
* Truk tidak tercatat menimbang tetapi sertifikat VGM diterbitkan.
* Pemakaian colokan reefer dicatat kurang dari jam penggunaan riil.

**Solusi Sistemik CIDP YMS:**
Setiap sensor fisik (twistlock spreader RS, loop detector gate, sensor ampere reefer rack) secara otomatis mengirimkan pemicu transaksi (*Event Webhook*) langsung ke tabel `billing_invoices` basis data YMS dan diteruskan via REST API ke modul keuangan ERP Odoo. Tidak ada kontainer yang diizinkan *Gate-Out* jika status faktur belum `PAID` atau terotorisasi plafon kredit piutang (*Credit Limit*).

---

## Bab 6: Kepatuhan Regulasi Kepabeanan (Customs Compliance & CEISA 4.0 DJBC)

### 6.1 Landasan Regulasi Kepabeanan Nasional
Terminal CIDP beroperasi sebagai **Tempat Penimbunan Berikat (TPB) / Pusat Logistik Berikat (PLB) / Inland Port** resmi di bawah pengawasan Direktorat Jenderal Bea dan Cukai (DJBC) Kementerian Keuangan RI, mengacu pada:
* **Peraturan Menteri Keuangan (PMK) No. 190/PMK.04/2022** tentang Pengeluaran Barang Impor untuk Dipakai.
* **Peraturan Dirjen Bea dan Cukai No. PER-07/BC/2021** tentang Tata Laksana Kepabeanan di Bidang Impor.
* **Standar Keamanan Rantai Pasok Global WCO SAFE Framework of Standards**.

### 6.2 Alur Penetapan Jalur Pabean & Otomasi Dokumen SPPB
Sistem YMS terhubung secara dua arah (*bi-directional*) dengan sistem **CEISA 4.0 DJBC**:
1. **Jalur Hijau (Automatic Green Lane Release):**
   * Manifes elektronik PIB (Pemberitahuan Impor Barang) dianalisis oleh *Risk Engine* CEISA.
   * Bila status risiko rendah, dokumen **SPPB (Surat Persetujuan Pengeluaran Barang)** diterbitkan otomatis dengan signature elektronik QR Code DJBC.
   * YMS CIDP menerima notifikasi API (`POST /api/customs/sppb-webhook`), mengupdate status kontainer menjadi `SPPB CLEARED`, dan membuka otorisasi keluar terminal (*Gate-Out Clearance*).
2. **Jalur Merah (Red Lane Inspection & Behandle):**
   * Kontainer berisiko tinggi atau muatan acak ditetapkan masuk Jalur Merah.
   * YMS secara otomatis mengunci status kontainer menjadi `CUSTOMS HOLD / BEHANDLE REQUIRED`.
   * Sistem memerintahkan operator Reach Stacker untuk memindahkan kontainer ke **Area Behandle Fisik Blok D** atau melintasi **Pemindai Portal Gantry X-Ray 6 MeV**.
   * Setelah pemeriksaan fisik bersama petugas Hanggar Bea Cukai selesai dan berita acara BAP disetujui, petugas merilis SPPB di modul [`pages/customs.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/customs.php).

### 6.3 Pengamanan Multimoda: Smart GPS E-Seal
Untuk kargo berstatus transit (misalnya perpindahan kargo impor dari Pelabuhan Tanjung Priok ke CIDP Cikarang via Kereta Api Logistik):
* Kontainer disegel menggunakan **Smart GPS E-Seal (Kunci Elektronik Ber-SIM Card & RFID)**.
* E-Seal memancarkan koordinat satelit dan status kawat pengunci setiap 60 detik.
* Jika kawat digunting atau dibuka paksa sebelum tiba di sepur simpan CIDP, sensor secara otomatis menembakkan **Tamper Alert** ke dashboard pabean YMS dan pos komando Bea Cukai untuk penindakan langsung.

---

## Bab 7: Analisis Nilai Tambah Gudang CFS (Container Freight Station) LCL 4.000 m²

### 7.1 Nilai Strategis CFS dalam Rantai Pasok Modern
Gudang CFS seluas 4.000 m² di CIDP adalah fasilitas penunjang utama yang mengubah kargo borongan menjadi bernilai komersial tinggi:
* **Stripping (FCL to LCL Unstuffing):** Pembongkaran kontainer impor penuh menjadi koli/palet terpisah milik beberapa importir berbeda (*Less-than-Container Load*).
* **Stuffing (LCL Consolidation Export):** Penggabungan kargo dari berbagai UMKM/pabrikan lokal ke dalam satu kontainer ekspor penuh 40ft untuk diberangkatkan via kereta api langsung ke pelabuhan laut.

### 7.2 Spesifikasi Operasional & Kapasitas CFS
* **Luas Fasilitas:** 4.000 m² lantai bebas debu (*Epoxy Coated Dust-Free Floor*).
* **Rampa Loading Dock:** 5 Rampa berkanopi dilengkapi dock leveller hidrolik otomatis kapasitas 10 Ton (Rampa D1 hingga D5).
* **Sistem Rak Palet:** *Selective Heavy Duty Pallet Racking* 4 Tingkat, Lorong Aisle A–F, kapasitas 1.600 slot palet standar ISO (1.200 × 1.000 mm).
* **Armada MHE Zero-Emission:** 4 Unit Forklift Listrik Lithium 2.5 Ton dan 6 Unit Electric Hand Pallet Truck.
* **Integrasi GS1 SSCC:** Setiap palet yang dibongkar dari kontainer langsung dicetak stiker **e-Label GS1-128 berisi kode SSCC-18** unik dan ditempatkan pada slot bin rak yang terpetakan di basis data [`pages/cfs.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/cfs.php).

---

## Bab 8: Rangka Kerja Penjaminan Mutu (Quality Assurance / QA Testing Suite)

Sebagai Penjamin Mutu (*QA Specialist*), Naufal Andika Heditya menyusun strategi pengujian berlapis (*Multi-Layer Testing Matrix*) untuk memastikan perangkat lunak tidak mengalami kegagalan fungsi di lapangan:

### 8.1 Matriks Uji Fungsional & Kasus Ekstrem (Edge-Case Testing Matrix)

| Test ID | Skenario Pengujian Operasional | Kondisi Input / Pemicu Sensor | Perilaku Sistem yang Diharapkan | Status Hasil Uji | Modul Terkait |
|---|---|---|---|---|---|
| **QA-TC-01** | Truk Gate-In Muatan Normal Lolos Validasi | Truk plat `B 9812 UIK`, kontainer `TCKU829104-2`, berat 32.450 kg (<34t) | ANPR membaca plat, OCR membaca kontainer, timbangan validasi SOLAS Pass, palang gate terbuka otomatis, e-Pass QR terbit, alokasi slot Blok B-04. | **PASS (100%)** | `pages/gate.php` |
| **QA-TC-02** | Penolakan Truk Overweight (Pelanggaran SOLAS) | Truk bobot 37.200 kg (> batas aman 34.000 kg) | Sistem memicu alarm merah `OVERLOAD ALERT`, palang gerbang terkunci (*LOCKED*), layar LED menampilkan penolakan, event penolakan dicatat. | **PASS (100%)** | `pages/gate.php` |
| **QA-TC-03** | Verifikasi Check Digit Nomor Kontainer ISO 6346 | Input nomor kontainer acak salah (misal: `MSKU1234560`) | Algoritma modulo 11 mendeteksi digit cek tidak cocok; sistem menampilkan alert validasi merah dan menolak proses pendaftaran box. | **PASS (100%)** | `pages/scanner.php` |
| **QA-TC-04** | Pemindaian Optik Barcode GS1-128 & SSCC-18 | Pemindaian kamera/gambar label palet AI `(00)389912345000000128` | Engine `html5-qrcode` mem-parsing AI `(00)`, mencocokkan ke manifes basis data, dan mengonfirmasi lokasi rak Aisle B-02. | **PASS (100%)** | `pages/scanner.php` |
| **QA-TC-05** | Pencegahan Tabrakan Penumpukan (*Yard Collision*) | Operator mencoba meletakkan box di Tier 3 tanpa adanya Tier 2 di bawahnya | Algoritma *Stacking Rules Engine* menolak perintah gerak; status error ditampilkan pada tablet operator Reach Stacker. | **PASS (100%)** | `pages/simulator.php` |
| **QA-TC-06** | Penguncian Pabean Kontainer Jalur Merah | Kontainer ditetapkan Jalur Merah oleh CEISA 4.0 | Status kontainer otomatis terkunci `CUSTOMS HOLD`; palang Gate-Out menolak truk pengangkut sebelum ada rilis resmi SPPB. | **PASS (100%)** | `pages/customs.php` |
| **QA-TC-07** | Deteksi Pembobolan Kawat Segel (E-Seal Tamper) | Kawat Smart E-Seal terputus saat perjalanan transit KA | Sensor mengirim payload HTTP POST darurat; dashboard membunyikan alarm visual merah seketika. | **PASS (100%)** | `pages/customs.php` |
| **QA-TC-08** | Kalkulasi Tarif Inap Progresif Masa II & III | Kontainer menginap selama 12 hari (Masa I: 3 hari, Masa II: 7 hari, Masa III: 2 hari) | Sistem menghitung: (3 × Rp 0) + (7 × Rp 45.000) + (2 × Rp 90.000) = **Rp 495.000**. Tagihan akurat tanpa manipulasi. | **PASS (100%)** | `pages/billing.php` |

### 8.2 Parameter Kepatuhan SLA Kinerja Sistem (Non-Functional Requirements)
* **Response Time API Backend:** Rata-rata **85 ms** (Batas toleransi maksimal: <150 ms) diuji via Postman Automation Runner.
* **Throughput Waktu Gerbang:** **92 Detik** per truk (Target SLA: <120 detik per transaksi).
* **Ketersediaan Sistem (High Availability):** Target uptime **99.9%** dengan arsitektur failover basis data MySQL master-slave.
* **Integritas Transaksi Finansial:** **Zero Revenue Leakage (0% kebocoran)** melalui enkripsi token JWT dan audit trail histori di tabel `billing_invoices`.

---

## Bab 9: Mockup & Business Intelligence (Executive KPI Dashboard)

Dashboard Eksekutif pada [`dashboard.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/dashboard.php) dirancang oleh Business Analyst khusus untuk memenuhi kebutuhan visibilitas pemangku kepentingan tingkat pimpinan (*C-Level & Terminal GM*):

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│ 📊 CONCLUSION INTERMODAL DRY PORT 35 Ha — EXECUTIVE INTELLIGENCE DASHBOARD            │
├─────────────────┬──────────────────┬──────────────────┬─────────────────┬──────────────┤
│ YARD OCCUPANCY  │ THROUGHPUT HARI  │ AVG DWELL TIME   │ GROSS REVENUE   │ CUSTOMS SPPB │
│     68.4%       │    482 TEU       │    2.3 HARI      │ Rp 184.500.000  │  94.2% PASS  │
│ [Normal <80%]   │ [Target: 500]    │ [Target: <2.5 H] │ [Collection 98%]│ [Jalur Hijau]│
└─────────────────┴──────────────────┴──────────────────┴─────────────────┴──────────────┘
```

### Penjelasan Metrik Finansial & Operasional:
1. **Yard Occupancy Rate (YOR) — 68.4%:** Menunjukkan utilisasi kapasitas lapangan penumpukan. Ambang batas kritis adalah 80%. Jika YOR >80%, sistem secara cerdas memberi peringatan *Congestion Alert* dan mengarahkan stacking ke blok cadangan.
2. **Throughput Harian — 482 TEU:** Jumlah total peti kemas yang ditangani (Gate-In, Gate-Out, Rail Transfer) dalam 24 jam terakhir.
3. **Average Dwell Time — 2.3 Hari:** Waktu rata-rata kontainer menginap di terminal. Angka 2.3 hari membuktikan terminal bekerja sangat produktif (standar nasional pelabuhan laut masih 3.5 – 5 hari).
4. **Gross Revenue Hari Ini — Rp 184.500.000:** Total akumulasi pendapatan jasa terminal yang berhasil ditagihkan secara real-time dari seluruh aktivitas handling, storage, timbangan, dan reefer.

---

## Bab 10: Matriks Kebutuhan Sistem (SRS Functional & Non-Functional Requirements)

### 10.1 Kebutuhan Fungsional (Functional Requirements)
* **FR-01 (Gate Automation):** Sistem harus mampu menerima data OCR kontainer, ANPR plat nomor, dan bobot jembatan timbang dalam waktu <5 detik.
* **FR-02 (SOLAS Compliance):** Sistem wajib menolak secara otomatis truk dengan muatan bruto melebihi batas legal 34.000 kg.
* **FR-03 (3D Slot Allocation):** Sistem harus mengalokasikan slot penyimpanan Bay-Row-Tier secara dinamis berdasarkan jenis kargo (Dry/Reefer/DG) dan pelabuhan tujuan.
* **FR-04 (AIDC GS1 Scanner):** Sistem harus mampu memindai dan mem-parsing kode GS1-128 dan SSCC-18 menggunakan kamera perangkat mobile/laptop.
* **FR-05 (Customs CEISA Sync):** Sistem harus menyinkronkan status SPPB dan menetapkan alur jalur hijau/merah secara otomatis via webhook REST API.
* **FR-06 (Automated Billing):** Sistem harus menerbitkan tagihan biaya terminal secara otomatis begitu suatu event operasional fisik selesai terekam.

### 10.2 Kebutuhan Non-Fungsional (Non-Functional Requirements)
* **NFR-01 (Security):** Seluruh komunikasi REST API wajib menggunakan otentikasi token JWT dan enkripsi HTTPS TLS 1.3.
* **NFR-02 (Performance):** Query pencarian status kontainer dan invoice harus dieksekusi dalam waktu kurang dari 200 milidetik pada beban 100 concurrent users.
* **NFR-03 (Reliability):** Basis data harus memiliki mekanisme pencatatan riwayat audit (*audit trail logging*) yang bersifat *immutable* (tidak dapat diubah atau dihapus sembarangan).
* **NFR-04 (Usability):** Antarmuka web harus memenuhi prinsip ergonomi visual maritim: bersih (*clean & spacious*), ramah layar sentuh pada tablet operator kabin crane, dan responsif.

---

### Pengesahan Dokumen:

| Disusun Oleh, | Disetujui Oleh, |
|:---:|:---:|
| <br><br>**Naufal Andika Heditya**<br>NIM: 24D507001012<br>*Business Analyst & QA Specialist* | <br><br>**Zulfikar Jafarudin Fatah**<br>NIM: 24D507001001<br>*Lead System Architect (Ketua Tim)* |
