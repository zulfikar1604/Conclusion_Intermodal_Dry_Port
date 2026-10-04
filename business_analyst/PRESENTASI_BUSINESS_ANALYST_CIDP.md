# 📊 BAHAN TAYANG PRESENTASI: CONCLUSION SUPPLY CHAIN CONSULTANT
## Kajian Strategis: Business Analysis, Multi-Dashboard Intelligence & Studi Kelayakan Finansial CIDP 35 Ha

* **Mata Kuliah:** Teknologi dan Perangkat Lunak Logistik
* **Dosen Pengampu / Klien Akademik:** Dr. Tigor Franky, S.T., M.T.
* **Institusi:** Program Studi S1 Manajemen Logistik — Institut Transportasi dan Logistik (ITL) Trisakti — Semester 5
* **Entitas Konsultan:** **Conclusion Supply Chain Consultant**
* **Studi Kasus:** Prototype Sistem Manajemen Lapangan Penumpukan (*Yard Management System / YMS*) pada **Conclusion Intermodal Dry Port (CIDP) 35 Ha**
* **Presenter Utama (Lead BA & QA):** Naufal Andika Heditya (24D507001012)
* **Co-Presenter (Lead Architect):** Zulfikar Jafarudin Fatah (24D507001001)
* **Tanggal Penyusunan:** Oktober 2026

---

# 📑 DAFTAR ISI 12 SLIDE STRATEGIS KONSULTAN

1. **Slide 1:** Cover Laporan Advisory Konsultan: Conclusion Supply Chain Consultant — CIDP 35 Ha
2. **Slide 2:** Kerangka Kerja Konsultan: Pengendalian Aliran Finansial (*Financial Flow*) & Taksonomi 4-Layer IT
3. **Slide 3:** Rekayasa Ulang Proses Bisnis (*Business Process Reengineering* — Diagnosis As-Is vs To-Be CIDP)
4. **Slide 4:** Standardisasi Mutu Identifikasi Global: GS1 SSCC-18 vs Kontainer ISO 6346 `[LIVE DEMO SCANNER]`
5. **Slide 5:** Studi Kelayakan Finansial: Struktur Alokasi Investasi CAPEX Rp 250 M & Beban Rutin OPEX Rp 24.8 M/Thn
6. **Slide 6:** Model Pendapatan (*Revenue Streams Waterfall*) & Strategi *Zero Revenue Leakage* `[LIVE DEMO BILLING]`
7. **Slide 7:** Evaluasi Kelayakan Investasi Finansial: Throughput 150.000 TEU, PBP 5.8 Tahun, NPV +Rp 13 M, IRR 11.4%
8. **Slide 8:** Dashboard Intelligence 1 — *Executive Command Center*: YOR 68.4%, Throughput, Dwell Heatmap & Cash Flow `[LIVE DEMO BERANDA]`
9. **Slide 9:** Dashboard Intelligence 2 — *Efisiensi Lapangan & Mitigasi Biaya*: Gate TAT 90s, SOLAS VGM, & Rasio Gerak Derek `[LIVE DEMO GATE & YARD]`
10. **Slide 10:** Dashboard Intelligence 3 — *Mitigasi Risiko Fasilitas Khusus*: Cold Chain Reefer, Bea Cukai CEISA 4.0 & CFS `[LIVE DEMO REEFER & CUSTOMS]`
11. **Slide 11:** Penjaminan Mutu Sistem (*QA Testing Matrix*) & Kepatuhan SLA Non-Fungsional
12. **Slide 12:** Kesimpulan Eksekutif Business Analyst & Rekomendasi Roadmap Implementasi Menuju Go-Live

---

```
========================================================================================
[SLIDE 1] COVER: CONCLUSION SUPPLY CHAIN CONSULTANT — STRATEGIC ADVISORY REPORT
========================================================================================
```
### Visual Slide
* Banner Atas: CONCLUSION SUPPLY CHAIN CONSULTANT — STRATEGIC ADVISORY REPORT
* Judul Utama: Business Analysis, Multi-Dashboard Intelligence & Studi Kelayakan Finansial CIDP 35 Ha
* Sub-judul: KAJIAN STRATEGIS: FINANCIAL FEASIBILITY (CAPEX/OPEX/ROI), BPR AS-IS TO-BE, MULTI-DASHBOARD BI & QA MATRIX
* Tim Konsultan:
  - Naufal Andika Heditya (Lead Business Analyst & QA Specialist)
  - Zulfikar Jafarudin Fatah (Lead System Architect)
  - Dewan Konsultan Pendukung: Armansyah (Hardware), Afriansayah (Software & ERP), Juan Gamaliel (Data Integration)
* Klien / Evaluator: Dr. Tigor Franky, S.T., M.T. — ITL Trisakti

### Skrip Pembicara (Speaker Notes)
> *"Selamat pagi Bapak Dr. Tigor Franky dan rekan-rekan sekalian.  
> Kami dari **Conclusion Supply Chain Consultant** hadir untuk menyajikan laporan kajian strategis dari sudut pandang **Business Analysis & Quality Assurance** pada proyek **Conclusion Intermodal Dry Port (CIDP) 35 Hektar**.  
>
> Sebagai konsultan bisnis rantai pasok, tugas kami bukan sekadar membuktikan bahwa software dan hardware berfungsi, melainkan membuktikan **apakah sistem ini menghasilkan keuntungan komersial yang terukur (Feasibility), bagaimana ragam Dashboard Business Intelligence kami menjadi alat bantu keputusan direksi (Decision Support System), serta bagaimana seluruh proses patuh terhadap standar global GS1 dan regulasi Bea Cukai CEISA 4.0.**  
> Hari ini, saya Naufal Andika Heditya bersama Lead Architect Zulfikar Jafarudin Fatah akan membedah tuntas temuan dan rekomendasi konsultan kami."*

---

```
========================================================================================
[SLIDE 2] KERANGKA KERJA KONSULTAN: FINANCIAL FLOW & 4-LAYER IT
========================================================================================
```
### Visual Slide
* **Kartu 1 (Perspektif Konsultan Bisnis):**
  - *Goods Flow (Fisik)* + *Information Flow (Data Telemetri)* ➔ **Financial Flow (Billable Event)**.
  - Aliran fisik dan data telemetri tidak memiliki nilai bisnis jika tidak terkonversi menjadi pendapatan tanpa kebocoran (*Zero Revenue Leakage*).
* **Kartu 2 (Framework Dr. Tigor Franky):**
  - Layer 1 (Sensing): Audit utilisasi aset fisik bernilai ratusan miliar.
  - Layer 2 (Network): Menjamin integritas transmisi data penagihan.
  - Layer 3 (Application): Logika tarif progresif dan stacking rules.
  - Layer 4 (Enterprise Integration): Sinkronisasi ERP Odoo Finance & CEISA 4.0 DJBC.
* **Kartu 3 (Mandat Silabus Slide 13):**
  - 1. OPEX / ROI Analysis, 2. Mockup Dashboard KPI Eksekutif, 3. SRS & Penjaminan Mutu (QA).

### Skrip Pembicara (Speaker Notes)
> *"Bapak Dr. Tigor, kerangka kerja konsultan kami berakar langsung pada teori perkuliahan Pertemuan 1: IT logistik bertindak sebagai saraf yang menghubungkan **3 Aliran Utama**.  
> Di industri logistik, kebocoran pendapatan sering terjadi di persimpangan antara aliran informasi dan finansial: kargo menginap berhari-hari tetapi denda inapnya lolos dari penagihan karena pencatatan manual.  
> Sebagai Business Analyst, kami memastikan Taksonomi 4-Layer bekerja sinkron: begitu sensor di Layer 1 dan 2 mendeteksi kargo keluar atau melewati batas masa bebas, Layer 3 YMS seketika mengkalkulasi tarif progresif, dan Layer 4 menerbitkan tagihan resmi di ERP Odoo. Inilah pengendalian Financial Flow modern."*

---

```
========================================================================================
[SLIDE 3] REKAYASA ULANG PROSES BISNIS (BPR): AS-IS VS TO-BE CIDP
========================================================================================
```
### Visual Slide
* **Tabel Diagnosis Komparasi BPR:**
  1. *Waktu Gerbang (Gate TAT):* 12–18 Menit (Manual) ➔ **60–90 Detik** (ANPR + OCR + Timbang 80t) | *Efisiensi 89%*.
  2. *Alokasi Slot Yard:* Manual/Terselip ➔ **100% Terarah 3D Bay-Row-Tier** | *0% Box Terselip*.
  3. *Dwell Time:* 6.8 Hari ➔ **2.3 Hari** (Peringatan masa bebas + jadwal rel KA) | *Turun 66%*.
  4. *Timbang VGM:* Manual terpisah ➔ **Otomatis di Gate-In (<34t)** | *SOLAS 100% Patuh*.
  5. *Penagihan:* Rekap manual 3-5 hari ➔ **Real-Time Event-Driven ke Odoo ERP** | *Zero Revenue Leakage*.
  6. *Rilis Pabean:* Berkas fisik 24-48 jam ➔ **API CEISA 4.0 Rilis SPPB <5 Menit** | *Rilis 95% Lebih Cepat*.
* **Diagnosis Konsultan:** Rekayasa To-Be memangkas siklus kargo 66% dan mengeliminasi 100% biaya shifting sia-sia!

### Skrip Pembicara (Speaker Notes)
> *"Sebelum berinvestasi ratusan miliar, langkah pertama konsultan adalah mendiagnosis inefisiensi pada depo konvensional (As-Is):  
> Truk antre 15 menit di gerbang karena pencatatan kertas, dan peti kemas tertahan rata-rata 6,8 hari.  
> Melalui cetak biru rekayasa To-Be CIDP YMS:  
> Waktu siklus gerbang kami pangkas sebesar 89% menjadi hanya 90 detik. Dan rata-rata waktu inap (dwell time) ditekan hingga 66% menjadi 2,3 hari melalui integrasi peringatan masa bebas dan jadwal keberangkatan kereta api harian. Ini melipatgandakan kecepatan perputaran modal kerja terminal."*

---

```
========================================================================================
[SLIDE 4] STANDAR IDENTIFIKASI: GS1 SSCC-18 VS ISO 6346 [LIVE DEMO]
========================================================================================
```
### Visual Slide
* **Kolom 1 (ISO 6346:2022 - Wadah Baja Luar):** Format `MSKU9182374`. Kode pemilik (3 char) + Kategori `U` + Seri (6 digit) + Check Digit Modulo 11. Digunakan melacak fisik peti kemas maritim.
* **Kolom 2 (GS1 SSCC-18 - Isi Palet Kargo di CFS):** Format AI `(00)` 18 digit. Digunakan melacak isi muatan palet individual di dalam boks LCL gudang CFS.
* **Penjaminan Mutu (QA Data):** Modulo 11 menolak nomor boks salah ketik, dan Modulo 10 menolak palet dengan barcode cacat.
* **Banner Demo:** `👉 [LIVE DEMO 1]: Buka Menu 'Scanner SSCC / GS1' (pages/scanner.php) ➔ Pindai Barcode SSCC-18 & Validasi Cek Digit ISO 6346`

### Skrip Pembicara (Speaker Notes)
> *"Dalam audit standardisasi data global, konsultan sering ditanya: 'Mengapa harus menerapkan GS1 SSCC-18 jika kontainer sudah memiliki nomor ISO 6346?'  
> Konsultan membedakan secara tegas: ISO 6346 hanya mengidentifikasi wadah baja luarnya saja. Sementara di dalam satu kontainer LCL di gudang CFS, terdapat puluhan palet kargo milik berbagai importir yang berbeda. Di sinilah SSCC-18 dari GS1 berperan sebagai paspor unik unit palet.  
> Pada modul `pages/scanner.php` yang akan kami demokan, operator cukup memindai satu kali barcode palet di rampa dock hidrolik untuk langsung memetakan barang ke dokumen ASN, nomor batch, dan tanggal expired (FEFO) tanpa membuka kardus."*

---

```
========================================================================================
[SLIDE 5] STRUKTUR INVESTASI AWAL CAPEX & OPEX
========================================================================================
```
### Visual Slide
* **Kolom 1 (CAPEX: Rp 249,5 Miliar / ± Rp 250 M):**
  - Lahan & Perkerasan Rigid 80t (35 Ha): Rp 130,0 M (52,1%)
  - Armada 3 RS Kalmar + 1 RTG Crane: Rp 55,0 M (22,0%)
  - Sepur Simpan Rel KA (Double Track): Rp 35,0 M (14,0%)
  - Gudang CFS 4.000m² & Hanggar DJBC: Rp 15,0 M (6,0%)
  - Infrastruktur Reefer (300 Plugs): Rp 6,5 M (2,6%)
  - Otomasi Gerbang & Sensor IoT: Rp 4,5 M (1,8%)
  - Software YMS, Cloud & ERP Odoo: Rp 3,5 M (1,4%)
  - *Insight Konsultan:* IT & IoT hanya 3,2% dari CAPEX, namun mengontrol dan mendigitalkan 96,8% aset terminal lainnya!
* **Kolom 2 (OPEX: Rp 24,78 Miliar / Tahun ~ Rp 2,06 M / Bulan):**
  - Solar Industri & Listrik PLN 380V: Rp 9,44 M / Thn (38,1%)
  - Gaji & Payroll 82 Personil: Rp 6,89 M / Thn (27,8%)
  - Sewa Jalur Rel KA (TAC KAI Logistik): Rp 5,40 M / Thn (21,8%)
  - Perawatan Mesin M&R & Asuransi/IT: Rp 3,06 M / Thn (12,3%)

### Skrip Pembicara (Speaker Notes)
> *"Kajian kelayakan finansial konsultan memproyeksikan kebutuhan belanja modal (CAPEX) sebesar **Rp 249,5 Miliar**, dengan porsi terbesar pada perkerasan beton kapasitas gandar 80 Ton dan armada derek berat.  
> Yang sangat menarik secara analisis bisnis: alokasi perangkat lunak YMS dan otomasi gerbang IoT hanya memakan **Rp 8 Miliar atau 3,2% dari total CAPEX**, namun menjadi otak pengendali seluruh operasi aset fisik bernilai ratusan miliar tersebut.  
> Sementara untuk beban operasional rutin tahunan (OPEX), terminal membutuhkan **Rp 24,78 Miliar per tahun**, atau hanya 32,6% dari total proyeksi pendapatan kotor."*

---

```
========================================================================================
[SLIDE 6] MODEL PENDAPATAN & STRATEGI ZERO LEAKAGE [LIVE DEMO]
========================================================================================
```
### Visual Slide
* **Target Throughput:** 150.000 TEU / Tahun
* **Total Gross Revenue:** **Rp 76.025.000.000 / Tahun (± Rp 76 Miliar)**
* **7 Aliran Pendapatan Komersial:**
  1. Bongkar Muat Lo-Lo: Rp 33,75 M (44,4%)
  2. Sewa Inap Progresif (Masa I Bebas, Masa II Rp 45rb, Masa III Rp 90rb): Rp 10,80 M (14,2%)
  3. Bagi Hasil Kereta Api Logistik: Rp 9,00 M (11,8%)
  4. Jasa Gudang CFS Stuffing/Stripping: Rp 8,10 M (10,7%)
  5. Steker Listrik Reefer Container: Rp 6,25 M (8,2%)
  6. Jembatan Timbang VGM SOLAS: Rp 5,50 M (7,2%)
  7. Pemeriksaan Behandle Karantina Pabean: Rp 2,62 M (3,5%)
* **Strategi Zero Revenue Leakage:** Palang Gate-Out terkunci otomatis sampai faktur di database berstatus `PAID` via webhook ERP Odoo.
* **Banner Demo:** `👉 [LIVE DEMO 2]: Buka Menu 'Billing & Faktur ERP' (pages/billing.php) ➔ Simulasi Auto-Invoice & Integrasi Odoo`

### Skrip Pembicara (Speaker Notes)
> *"Melalui diversifikasi 7 aliran pendapatan jasa terminal, dengan throughput moderat 150.000 TEU per tahun, CIDP diproyeksikan meraup pendapatan kotor sebesar **Rp 76,02 Miliar per tahun**.  
> Dan bagaimana kami mengamankan pendapatan ini? Kami menerapkan solusi **Zero Revenue Leakage**: setiap kejadian fisik (seperti Reach Stacker mengangkat boks atau kontainer menginap melewati 3 hari) secara otomatis menerbitkan e-Invoice di `pages/billing.php` dan tersinkronisasi ke modul akuntansi Odoo.  
> Palang Gate-Out terkunci permanen hingga status tagihan lunas atau memiliki jaminan kredit resmi."*

---

```
========================================================================================
[SLIDE 7] EVALUASI KELAYAKAN INVESTASI FINANSIAL: PBP, NPV, IRR & ROI
========================================================================================
```
### Visual Slide
* **Kartu 1 (Arus Kas & EBITDA):**
  Gross Revenue Rp 76,02 M – OPEX Rp 24,79 M = **EBITDA Rp 51,23 Miliar (Margin 67,4%)**. Laba Bersih Setelah Pajak (EAT): **Rp 30,21 Miliar / Tahun**. Arus Kas Bersih Tahunan: **Rp 42,71 Miliar / Tahun**.
* **Kartu 2 (Payback Period / PBP):**
  $\text{PBP} = \frac{\text{CAPEX Rp 249,5 M}}{\text{Cash Flow Rp 42,71 M}} = \mathbf{5,84\text{ Tahun}}\ (\approx 5\text{ Thn } 10\text{ Bln})$. Modal balik lunas di bawah 6 tahun (standar aman pelabuhan 8–10 tahun).
* **Kartu 3 (Net Present Value / NPV @10%):**
  $\text{NPV} = \mathbf{+\text{Rp 12.955.000.000}}\ (> 0)$. Bernilai Positif membuktikan secara absolut proyek **SANGAT LAYAK (FEASIBLE)**.
* **Kartu 4 (IRR & ROI 10 Tahun):**
  **IRR: 11,42%** (Melampaui *Hurdle Rate* kredit bank 8,50%). **ROI 10 Tahun: 121,1%** (Total laba bersih 10 tahun melampaui seluruh modal awal).

### Skrip Pembicara (Speaker Notes)
> *"Inilah bukti kuantitatif utama kelayakan investasi yang kami sajikan kepada pemodal:  
> Terminal menghasilkan laba bersih setelah pajak sebesar **Rp 30,21 Miliar per tahun** dan arus kas bersih tahunan Rp 42,71 Miliar.  
> Hasil evaluasi membuktikan:  
> Pertama, Payback Period tercapai dalam **5,84 tahun** (kurang dari 6 tahun).  
> Kedua, Net Present Value (NPV) bernilai positif sebesar **+Rp 12,95 Miliar** pada tingkat diskonto 10%.  
> Dan ketiga, Internal Rate of Return (IRR) mencapai **11,42%**, melampaui suku bunga pinjaman komersial 8,5%. Angka-angka ini memastikan proyek sangat bankable."*

---

```
========================================================================================
[SLIDE 8] DASHBOARD INTELLIGENCE 1: EXECUTIVE COMMAND CENTER [LIVE DEMO]
========================================================================================
```
### Visual Slide
* **Kolom 1 (4 Metrik Keputusan Direksi - C-Level DSS):**
  1. *Yard Occupancy Rate (YOR) — 68,4%:* Ambang batas aman (<80%). Mencegah kemacetan penumpukan dan denda port congestion surcharge ($50/TEU).
  2. *Throughput Harian — 482 TEU:* Mencapai 96,4% dari target harian (500 TEU). Mengukur kecepatan intermodal gate truk dan rail siding KA.
  3. *Average Dwell Time — 2,3 Hari:* Sangat produktif dibanding rata-rata nasional pelabuhan laut (4 - 5 hari).
  4. *Gross Daily Revenue — Rp 184,5 Juta:* Arus kas harian tertagih real-time dengan Collection Rate 98,2%.
* **Kolom 2 (Business Intelligence & Analitik Data):**
  - *Hourly Traffic Flow Chart:* Grafik fluktuasi arus truk per jam untuk penugasan operator gate & RS (peak hour 10:00 - 15:00).
  - *Dwell Time Heatmap Blok Yard:* Peta warna mendeteksi kontainer menginap kritis (>7 hari) di Blok A-E untuk pemicu denda Masa III.
  - *Live Event Stream:* Memonitor 5 transaksi lapangan terakhir detik-per-detik untuk transparansi audit.
* **Banner Demo:** `👉 [LIVE DEMO 3]: Buka Halaman 'Dashboard' (dashboard.php) ➔ Demonstrasikan Gauge YOR 68.4%, Chart.js, & Heatmap Dwell Time`

### Skrip Pembicara (Speaker Notes)
> *"Sesuai mandat slide 13 silabus: **Mockup Dashboard KPI**. Konsultan kami tidak hanya membuat dashboard yang estetis, melainkan sebuah **Decision Support System (DSS)** berbasis Business Intelligence di `dashboard.php`.  
> General Manager terminal dapat memantau denyut nadi pelabuhan dalam satu layar:  
> Jika jarum okupansi yard (YOR) mendekati 75%, sistem memberi peringatan dini sebelum lapangan macet.  
> Melalui heatmap dwell time, pimpinan langsung mengetahui tumpukan blok mana yang mengalami penumpukan kargo kritis. Dan kartu Gross Revenue menyajikan arus kas harian tertagih seketika."*

---

```
========================================================================================
[SLIDE 9] DASHBOARD INTELLIGENCE 2: EFISIENSI LAPANGAN & MITIGASI BIAYA [LIVE DEMO]
========================================================================================
```
### Visual Slide
* **Kolom 1 (Analisis Gerbang & Trucking - page=gate & page=trucking):**
  - *Gate TAT Analisis:* Depo konvensional 15 menit vs CIDP 90 detik. Menghemat biaya tunggu armada truk pengangkut.
  - *Validasi Otomatis SOLAS VGM (<34.000 kg):* Sistem menolak otomatis truk overload >34t di gerbang, menghilangkan risiko tuntutan hukum maritim internasional dan denda kelebihan beban jalan raya.
* **Kolom 2 (Analisis Yard & Alat Berat - page=yard & page=alat):**
  - *Optimasi Rasio Shifting Derek (Moves per Box):* Algoritma 3D Bay-Row-Tier menata penumpukan berdasarkan pelabuhan tujuan dan jadwal rel KA. Rasio gerak turun dari 1.8 ➔ 1.1 moves/box.
  - *Nilai Bisnis:* Menghemat konsumsi Solar HSD Reach Stacker sebesar 22% (efisiensi OPEX Rp 1,06 Miliar per tahun).
  - *Telemetri GPS Alat Berat:* Mencegah waktu henti sia-sia (*idling time*) armada RS dan RTG Crane.
* **Banner Demo:** `👉 [LIVE DEMO 4]: Buka Menu 'Gate' (pages/gate.php) ➔ Jalankan Simulasi Truk Lolos vs Truk Overload Ditolak SOLAS`

### Skrip Pembicara (Speaker Notes)
> *"Pada Dashboard Operasional (`page=gate` dan `page=yard`), Business Analyst menganalisis titik-titik pemborosan biaya:  
> Di gerbang, validasi timbangan dinamis memastikan tidak ada truk overload di atas 34 ton yang masuk, mematuhi regulasi SOLAS VGM internasional.  
> Sementara di lapangan penumpukan, visualisasi 3D Bay-Row-Tier mengoptimalkan rasio gerakan derek dari 1,8 kali angkat per box menjadi 1,1 kali angkat. Penurunan rasio shifting ini secara langsung **memangkas konsumsi solar Reach Stacker sebesar 22%, yang setara dengan penghematan OPEX Rp 1,06 Miliar per tahun**."*

---

```
========================================================================================
[SLIDE 10] DASHBOARD INTELLIGENCE 3: MITIGASI RISIKO REEFER, PABEAN & CFS [LIVE DEMO]
========================================================================================
```
### Visual Slide
* **Kartu 1 (Cold Chain Reefer - page=reefer):**
  - 300 Plugs & IoT LoRaWAN memantau suhu kargo dingin (-18°C hingga -25°C) setiap 60 detik.
  - *Nilai Bisnis BA:* Mencegah pembusukan muatan beku (vaksin, daging, seafood) dan menghindari klaim ganti rugi asuransi kargo bernilai hingga $100.000+ per kontainer.
* **Kartu 2 (Bea Cukai CEISA 4.0 - page=customs):**
  - Kepatuhan PMK 190/2022: Jalur Hijau (94,2%) rilis SPPB otomatis <5 menit via webhook REST API; Jalur Merah (5,8%) dikunci status `CUSTOMS HOLD` & pemindai Gantry X-Ray 6 MeV penetrasi 300 mm.
  - Smart GPS E-Seal: Pengamanan kargo transit rel KA Tanjung Priok (tamper alert otomatis jika kawat diputus).
* **Kartu 3 (Gudang CFS LCL - page=cfs):**
  - Fasilitas 4.000 m² & 5 rampa loading dock hidrolik (D1-D5) melayani stripping impor dan konsolidasi ekspor UMKM lokal.
  - Digital e-Tally Sheet: Pencatatan kerusakan kargo dan overage/shortage instan via tablet tally master.
* **Banner Demo:** `👉 [LIVE DEMO 5]: Buka Menu 'Reefer' (page=reefer) & 'Kepabeanan' (page=customs) ➔ Tunjukkan Telemetri Suhu & Rilis SPPB`

### Skrip Pembicara (Speaker Notes)
> *"Di fasilitas khusus, Dashboard YMS berfungsi sebagai instrumen mitigasi risiko tinggi:  
> Pada Dashboard Reefer (`page=reefer`), sensor telemetri LoRa memantau suhu 300 steker pendingin setiap menit. Jika suhu naik di atas ambang batas, alarm aktif seketika. Ini menghindarkan terminal dari risiko klaim ganti rugi kargo rusak bernilai lebih dari 100 ribu dolar per kontainer.  
> Pada Dashboard Kepabeanan (`page=customs`), sistem terhubung dua arah ke CEISA 4.0 DJBC, memisahkan secara otomatis kargo Jalur Hijau dan Jalur Merah.  
> Dan di Gudang CFS (`page=cfs`), digital e-tally sheet mencatat kondisi fisik kargo impor dan ekspor secara real-time."*

---

```
========================================================================================
[SLIDE 11] PENJAMINAN MUTU SISTEM (QA TESTING MATRIX) & KEPATUHAN SLA
========================================================================================
```
### Visual Slide
* **Matriks Pengujian Kasus Ekstrem (Edge-Cases):**
  1. *QA-TC-01 (Truk Gate-In Normal <34t):* Lolos otomatis <30 detik ➔ **PASS (100%)**
  2. *QA-TC-02 (SOLAS Overweight >34t):* Alarm overload aktif, palang terkunci, LED tolak masuk ➔ **PASS (100%)**
  3. *QA-TC-03 (ISO 6346 Check Digit):* Salah ketik nomor kontainer ditolak rumus Modulo 11 ➔ **PASS (100%)**
  4. *QA-TC-04 (GS1 SSCC Optical Scan):* Barcode AI `(00)` terbaca akurat dan terarah ke rak CFS ➔ **PASS (100%)**
  5. *QA-TC-05 (Yard Collision Prevention):* Larangan menaruh box di Tier 3 tanpa Tier 2 di bawahnya ➔ **PASS (100%)**
  6. *QA-TC-06 (Customs Hold):* Boks tanpa SPPB dilarang keras Gate-Out ➔ **PASS (100%)**
  7. *QA-TC-07 (Tarif Inap Progresif):* Validasi formula Masa I (Rp 0), II (Rp 45rb), III (Rp 90rb) tanpa bocor ➔ **PASS (100%)**
* **Kepatuhan SLA Non-Fungsional:** Waktu respon API 85 ms (<150 ms), Siklus Gate 92 detik (<120s), dan Zero Revenue Leakage.

### Skrip Pembicara (Speaker Notes)
> *"Sebagai penjamin mutu (QA), konsultan menyusun matriks pengujian fungsional dan kasus ekstrem (Edge-Cases) untuk memastikan perangkat lunak tidak mengalami kegagalan fungsi di lapangan.  
> Seluruh skenario batas—mulai dari truk overload SOLAS, nomor kontainer salah ketik, kawat segel pabean putus di jalan rel, hingga kalkulasi denda penumpukan progresif—telah diuji dan berstatus **PASS 100%**.  
> Waktu respon API berada pada rata-rata 85 milidetik, menjamin performa transaksi yang sangat cepat dan andal."*

---

```
========================================================================================
[SLIDE 12] KESIMPULAN REKOMENDASI KONSULTAN & ROADMAP MENUJU GO-LIVE
========================================================================================
```
### Visual Slide
* **4 Kotak Kesimpulan Strategis:**
  1. *Kelayakan Finansial Mutlak:* CAPEX Rp 250 M balik modal dalam 5,84 tahun, Laba Bersih Rp 30,2 M/tahun, IRR 11,42%.
  2. *Efisiensi Operasional (BPR):* Waktu transaksi gerbang turun dari 15 menit ke 90 detik, Dwell Time turun dari 6,8 ke 2,3 hari.
  3. *Standar & Kepatuhan:* Mengadopsi GS1 AI `(00)` SSCC-18, ISO 6346, dan kepatuhan pabean CEISA 4.0 DJBC (PMK 190/2022).
  4. *Zero Revenue Leakage:* Event-driven billing mengeliminasi 100% kebocoran penagihan denda penumpukan.
* **Rekomendasi Final Konsultan:**
  Sistem CIDP YMS terbukti secara kuantitatif dan empiris **SANGAT LAYAK (FEASIBLE)** untuk dilanjutkan ke tahap implementasi komersial.

### Skrip Pembicara (Speaker Notes)
> *"Bapak Dr. Tigor Franky dan rekan-rekan sekalian,  
> Sebagai penutup dari laporan advisory Conclusion Supply Chain Consultant, kami menyimpulkan bahwa prototipe Conclusion Intermodal Dry Port YMS telah membuktikan nilai bisnisnya secara komprehensif:  
> Pertama, proyek ini menguntungkan secara finansial dengan pengembalian investasi dalam 5,8 tahun.  
> Kedua, rekayasa proses To-Be memangkas dwell time hingga 66%.  
> Ketiga, multi-dashboard yang kami bangun bertindak sebagai instrumen intelijen bisnis yang menekan biaya BBM derek dan memitigasi risiko klaim kargo.  
> Dan keempat, seluruh sistem 100% patuh terhadap standar global GS1 dan regulasi Bea Cukai CEISA 4.0.  
>
> Rekomendasi konsultan kami adalah melanjutkan cetak biru ini ke tahap implementasi komersial.  
> Terima kasih atas perhatian Bapak Dosen dan rekan-rekan mahasiswa. Kami menyambut sesi diskusi dan tanya jawab dengan antusias. Selamat pagi."*
