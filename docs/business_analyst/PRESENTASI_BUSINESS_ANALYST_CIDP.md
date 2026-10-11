# 📊 BAHAN TAYANG PRESENTASI: CONCLUSION SUPPLY CHAIN CONSULTANT
## Kajian Strategis: Business Analysis, Multi-Dashboard Intelligence, End-to-End Decision Flows & Studi Kelayakan Finansial CIDP 35 Ha

* **Mata Kuliah:** Teknologi dan Perangkat Lunak Logistik
* **Dosen Pengampu / Klien Akademik:** Dr. Tigor Franky, S.T., M.T.
* **Institusi:** Program Studi S1 Manajemen Logistik — Institut Transportasi dan Logistik (ITL) Trisakti — Semester 5
* **Entitas Konsultan:** **Conclusion Supply Chain Consultant**
* **Studi Kasus:** Prototype Sistem Manajemen Lapangan Penumpukan (*Yard Management System / YMS*) pada **Conclusion Intermodal Dry Port (CIDP) 35 Ha**
* **Presenter Utama (Lead BA & QA):** Naufal Andika Heditya (24D507001012)
* **Co-Presenter (Lead Architect):** Zulfikar Jafarudin Fatah (24D507001001)
* **Dewan Konsultan Pendukung:**
  - Armansyah Muchtarrom (24D507001010) — Hardware Specialist (Sensor Lapangan & Otomasi Gate)
  - Afriansayah Ayubi (24D507001026) — Software & ERP Specialist (Penanganan Peti Kemas & Tarif Odoo)
  - Juan Gamaliel (24D507001016) — Data Integration Specialist (REST API Gateway & Stacking Rules Matrix)
* **Tanggal Penyusunan:** Oktober 2026

---

# 📑 DAFTAR ISI 12 SLIDE STRATEGIS KONSULTAN (SESUAI SILABUS DR. TIGOR FRANKY)

1. **Slide 1:** Cover Laporan Advisory Konsultan: Conclusion Supply Chain Consultant — CIDP 35 Ha
2. **Slide 2:** Paradigma Business Analyst: Developer vs BA & Metodologi 3-Langkah Pemetaan Fitur
3. **Slide 3:** Master End-to-End Decision Flow: Peta Alur Keputusan Terminal (Gate-In s/d Gate-Out) `[5 DECISION GATEPOINTS]`
4. **Slide 4:** Flow 1: Gerbang Masuk & Timbangan Dinamis (WCS + YMS) `[SOLAS ≤34t, JIKA YA vs JIKA TIDAK U-Turn]`
5. **Slide 5:** Flow 2: Pengangkatan Peti Kemas & Stacking 3D Bay-Row-Tier `[20ft vs 40ft Telescopic Spreader, Heavy-at-Bottom]`
6. **Slide 6:** Flow 3 & 4: Rantai Dingin Reefer 300 Plugs & Gudang CFS Inbound Stripping `[Decision Suhu & Barcode GS1]`
7. **Slide 7:** Flow 5 & 6: Kepatuhan Bea Cukai CEISA 4.0 & Kereta Api Rel Siding 600m `[Jalur Hijau vs Merah, E-Seal KA]`
8. **Slide 8:** Flow 7: Model Pendapatan (Gross Rev Rp 76 M) & Penguncian Gate-Out Zero Revenue Leakage `[Interlock Odoo ERP]`
9. **Slide 9:** Evaluasi Kelayakan Investasi Finansial: CAPEX Rp 250 M, OPEX Rp 24.8 M, PBP 5.84 Thn, NPV +Rp 22.9 M, IRR 11.9%
10. **Slide 10:** Dashboard Intelligence: Executive Command Center (YOR 68.4%, Dwell 2.3 Hari, Gross Daily Rev) `[C-LEVEL DSS]`
11. **Slide 11:** Matriks Penanganan Anomali & QA Testing Matrix (PASS 100%) & Kepatuhan SLA Non-Fungsional
12. **Slide 12:** Kesimpulan Eksekutif Business Analyst & Rekomendasi Roadmap Implementasi Menuju Go-Live

---

```
========================================================================================
[SLIDE 1] COVER: CONCLUSION SUPPLY CHAIN CONSULTANT — STRATEGIC ADVISORY REPORT
========================================================================================
```
### Visual Slide
* **Header & Banner:** CONCLUSION SUPPLY CHAIN CONSULTANT — STRATEGIC ADVISORY REPORT
* **Judul Utama:** Business Analysis & Alur Proses Bisnis End-to-End: Pemetaan Fitur Software Logistik CIDP 35 Ha
* **Sub-Judul Silabus:** STANDAR SILABUS: ALUR KEPUTUSAN (JIKA YA / JIKA TIDAK), ALAT & HARDWARE, VALIDASI BISNIS, SERTA FEASIBILITY
* **Tim Advisory Business Analysis & QA:**
  - Naufal Andika Heditya (24D507001012) — Lead Business Analyst & QA
  - Zulfikar Jafarudin Fatah (24D507001001) — Lead System Architect
* **Dewan Konsultan Pendukung:** Armansyah Muchtarrom, Afriansayah Ayubi, Juan Gamaliel
* **Klien / Evaluator Akademik:** Dr. Tigor Franky, S.T., M.T. (Teknologi dan Perangkat Lunak Logistik)
* **Institusi:** Program Studi S1 Manajemen Logistik, ITL Trisakti, Semester 5, Oktober 2026
* **Live Web Portal:** `http://localhost/Conclusion_Intermodal_Dry_Port/dashboard.php`

### Skrip Pembicara (Speaker Notes) — Naufal Andika Heditya
> *"Selamat pagi Bapak Dr. Tigor Franky dan rekan-rekan sekalian.  
> Kami dari **Conclusion Supply Chain Consultant** hadir menyajikan Laporan Kajian Strategis dari sudut pandang **Business Analyst (BA) dan Quality Assurance (QA)** pada prototype **Conclusion Intermodal Dry Port (CIDP) 35 Hektar**.  
>
> Mengacu pada arahan silabus perkuliahan Bapak Dr. Tigor mengenai **Pemetaan Fitur Perangkat Lunak Logistik**, tugas seorang Business Analyst adalah merancang logika bisnis secara menyeluruh: bukan hanya alur normal saat semuanya lancar, melainkan **alur keputusan percabangan (Decision Logic): apa yang terjadi JIKA YA, apa yang terjadi JIKA TIDAK, serta secara spesifik MENGGUNAKAN ALAT APA—baik sensor hardware, alat berat derek, personil pelaksana, maupun modul perangkat lunaknya**.  
>
> Hari ini, saya Naufal Andika Heditya bersama Lead Architect Zulfikar Jafarudin Fatah akan membedah tuntas seluruh alur end-to-end, matriks kelayakan finansial Rp 250 Miliar, hingga pembuktian sistem live demo di portal CIDP."*

---

```
========================================================================================
[SLIDE 2] PARADIGMA BUSINESS ANALYST: DEVELOPER VS BA & METODOLOGI 3-LANGKAH
========================================================================================
```
### Visual Slide
* **Halaman Web & Tautan:** `dashboard.php?page=beranda` (`http://localhost/Conclusion_Intermodal_Dry_Port/dashboard.php`)
* **Kartu 1: Developer vs Business Analyst (Perbedaan Sudut Pandang):**
  - *Pendekatan Developer (Koding):* Fokus pada struktur koding, implementasi logika di sistem, sintaks query, dan tombol UI (Contoh: Membuat Button *"Confirm Putaway"*).
  - *Pendekatan Business Analyst (BA):* Fokus pada logika bisnis, parameter masukan (input), validasi aturan bisnis, dan dampak operasional (Contoh: *"Sistem wajib memverifikasi apakah berat barang ≤ kapasitas maksimum rak bin sebelum status lokasi diperbarui di database"*).
* **Kartu 2: Metodologi Perkuliahan Dr. Tigor (Rumus 3-Langkah Analisis Fitur):**
  - 1. **FITUR / EVENT TRIGGER:** Pemicu aksi sistem (misal: sensor menginjak loop atau klik tombol konfirmasi).
  - 2. **VALIDASI BUSINESS RULE:** Pengecekan aturan bisnis (misal: berat ≤ 34.000 kg SOLAS VGM & nomor ISO 6346 Modulo 11).
  - 3. **DAMPAK / STATUS DATABASE:** Pembaruan status di database dan aksi perangkat keras (misal: status `GATE_IN_APPROVED`, tiket QR tercetak, palang barrier terbuka).
* **Kartu 3: Orkestrasi 5 Modul Logistik Terpadu CIDP:**
  - **WCS (Warehouse Control System):** Kontrol perangkat keras (kamera OCR/ANPR, timbangan pitless 80t, RFID reader).
  - **YMS (Yard Management System):** Penjadwalan gate, visualisasi lapangan penumpukan, dan algoritma slot 3D Bay-Row-Tier.
  - **WMS (Warehouse Management System):** Gudang CFS LCL berstandar GS1 SSCC-18 dan monitoring 300 steker Reefer.
  - **TMS (Transportation Management System):** Penjadwalan kereta api rel siding 600m dan Smart GPS E-Seal transit.
  - **ERP (Enterprise Resource Planning):** Billing Odoo Finance untuk kalkulasi tarif sewa progresif dan integrasi webhook CEISA 4.0 DJBC.
* **Banner Navigasi Sistem:** `👉 PUSAT NAVIGASI SISTEM: Seluruh Alur Terintegrasi Diakses Melalui Portal 'dashboard.php?page=beranda'`

### Skrip Pembicara (Speaker Notes)
> *"Bapak Dr. Tigor, fondasi kajian konsultan kami berakar langsung pada modul perkuliahan:  
> Perbedaan mendasar seorang developer dan business analyst terletak pada cara pandang:  
> Developer berfokus pada membuat tombol dan sintaks koding. Sedangkan Business Analyst berfokus pada **mengapa fitur itu ada, validasi apa yang harus dilewati, dan dampak operasional apa yang ditimbulkannya**.  
>
> Kami menerapkan rumus 3-Langkah Dr. Tigor:  
> **1. Fitur/Trigger ➔ 2. Validasi Business Rule ➔ 3. Dampak & Pembaruan Status Database**.  
> Kelima modul logistik—WCS, YMS, WMS, TMS, dan ERP Odoo—bekerja sebagai satu kesatuan sistem saraf yang mengatur aliran fisik, informasi, dan finansial pelabuhan kering."*

---

```
========================================================================================
[SLIDE 3] MASTER END-TO-END DECISION FLOW: PETA KEPUTUSAN TERMINAL (GATE-IN S/D GATE-OUT)
========================================================================================
```
### Visual Slide
* **Halaman Web & Tautan:** `pages/simulator.php` (`http://localhost/Conclusion_Intermodal_Dry_Port/pages/simulator.php`)
* **Arsitektur 5 Tahapan Keputusan Berurutan (Sequential Decision Gatepoints):**

| Tahapan | Decision Point (Pertanyaan Logika) | JIKA YA (Approved / Pass) | JIKA TIDAK (Rejected / Fail) | Menggunakan Apa (Alat, Sensor, Modul) |
| :--- | :--- | :--- | :--- | :--- |
| **Stage 1: Gate-In** | Berat Total ≤ 34t & Check Digit ISO Sah? | Cetak tiket e-Pass QR, palang buka, truk masuk ke zona tujuan. | Palang terkunci, sirine 105 dB bunyi, truk ditolak & belok Jalur U-Turn. | Timbangan Pitless 80t Avery, Kamera ANPR 4K, Barrier Gate Magnetik, WCS Gate. |
| **Stage 2: Yard Stacking** | Boks 20ft vs 40ft? Lolos Aturan Anti-Floating? | 20ft ke Bay Ganjil; 40ft ke Bay Genap. Heavy di Tier 1-2. | YMS tolak putaway, alarm tablet operator bunyi, dialihkan ke bin cadangan. | 3 RS 45t Kalmar (Spreader Telescopic), 1 RTG 40t Konecranes, Tablet Kabin VMT. |
| **Stage 3: Fasilitas Khusus** | Suhu -18°C s/d -25°C? Barcode GS1 SSCC Valid? | Colok steker 380V; Forklift susun palet ke rak bin gudang CFS. | Alarm reefer + failover Genset 1.250 kVA; Barcode rusak cetak ulang (status BLOCKED). | 300 Steker CEE Mennekes 380V, Genset Cummins 1.250 kVA, Forklift 3t, PDA Zebra TC26. |
| **Stage 4: Kepabeanan & KA** | Profil Hijau vs Merah? Smart E-Seal Utuh? | SPPB rilis <5 mnt; Muat 30 GD Kereta Api (60 TEU) jalan 90 mnt ke Priok. | Jalur Merah ke Behandle X-Ray; Kawat putus picu PABEAN_TAMPER_ALERT & isolasi. | Webhook CEISA 4.0, Nuctech Gantry X-Ray 6 MeV, Lok CC206, E-Seal Satelit LoRa. |
| **Stage 5: Gate-Out** | Invoice Odoo PAID & Dokumen SPPB Cleared? | Palang terbuka otomatis, foto ANPR e-Exit Pass, kargo rilis keluar. | Palang KUNCI PERMANEN, VMS suruh ke kasir, truk belok ke Holding Lane. | ERP Odoo Finance Webhook, Barrier Gate Outbound, Display LED VMS 3-Warna. |

* **Banner Demo:** `👉 ARSITEKTUR KEPUTUSAN TERPADU: 5 Gatepoints Menjamin Kepatuhan Regulasi & Zero Revenue Leakage!`

### Skrip Pembicara (Speaker Notes)
> *"Pada Slide 3 ini, kami menyajikan **Master End-to-End Decision Flow** yang merangkum keseluruhan siklus hidup kontainer sejak roda truk pertama kali menginjak gerbang masuk hingga keluar terminal:  
> Terdapat **5 Gerbang Keputusan Kritis (Decision Gatepoints)**:  
> 1. Di Gerbang Masuk: Jika berat ≤ 34 ton, truk lolos masuk; jika lebih dari 34 ton, palang terkunci dan truk dibelokkan ke Jalur U-Turn menggunakan timbangan pitless 80 ton Avery.  
> 2. Di Lapangan Yard: Jika boks 20ft masuk Bay Ganjil, jika 40ft Reach Stacker memanjangkan spreader telescopic hidroliknya ke Bay Genap; jika operator mencoba menaruh boks melayang, sistem YMS seketika menolak perintah putaway.  
> 3. Di Fasilitas Khusus: Jika reefer stabil, listrik 380V aktif; jika listrik padam, failover genset cadangan 1.250 kVA otomatis menyala. Di CFS, barcode GS1 SSCC-18 divalidasi dengan PDA scanner.  
> 4. Di Kepabeanan & Kereta: Jalur Hijau rilis otomatis <5 menit via API CEISA, sedangkan Jalur Merah diisolasi ke Hanggar X-Ray. Kereta api mengangkut 60 TEU dengan pengamanan Smart E-Seal satelit.  
> 5. Di Gerbang Keluar: Jika faktur sudah lunas di Odoo ERP, palang terbuka; jika belum, palang terkunci permanen. Inilah arsitektur zero revenue leakage."*

---

```
========================================================================================
[SLIDE 4] FLOW 1: GERBANG MASUK & TIMBANGAN DINAMIS (WCS + YMS)
========================================================================================
```
### Visual Slide
* **Halaman Web & Tautan:** `pages/gate.php` (`http://localhost/Conclusion_Intermodal_Dry_Port/pages/gate.php`)
* **Kotak Kiri: [DECISION POINT & JIKA YA (PASS / APPROVED)]:**
  - *Decision Point:* `Apakah Berat Kotor Total ≤ 34.000 kg (SOLAS VGM) DAN Check Digit ISO 6346 Modulo 11 Sah?`
  - *Aksi Sistem JIKA YA:*
    - Tiket e-Pass (Kode QR) tercetak secara instan dalam waktu <90 detik.
    - Palang barrier terbuka otomatis.
    - Status Database: `GATE_IN_APPROVED`.
  - *Ke Mana Truk Bergerak Sesuai Manifes?*
    - Kontainer Kering FCL ➔ Menuju Blok Penumpukan Yard A/B/C/D.
    - Kontainer Reefer Dingin ➔ Menuju Blok E (Area 300 Steker Listrik 380V).
    - Kontainer Campuran LCL ➔ Menuju Rampa Dock Gudang CFS (D1–D5).
    - Kargo Bea Cukai Jalur Merah ➔ Menuju Hanggar Pemeriksaan Fisik Behandle DJBC.
* **Kotak Kanan: [DECISION POINT & JIKA TIDAK (REJECTED / EXCEPTION)]:**
  - *Decision Point:* `Apakah Berat > 34.000 kg ATAU Check Digit ISO 6346 Salah Ketik?`
  - *Aksi Sistem JIKA TIDAK:*
    - Palang Barrier **TETAP TERKUNCI PERMANEN**.
    - Sirine alarm berbunyi keras (105 dB) dan lampu strobo merah menyala.
    - Status Database: `OVERLOAD_REJECTED` (atau `INVALID_CHECK_DIGIT`).
  - *Ke Mana Truk Bergerak?*
    - Truk **DILARANG KERAS MASUK** ke area terminal!
    - Supir diarahkan belok kiri ke **Jalur U-Turn (Putar Balik)** untuk keluar pelabuhan atau ke Redress Area untuk pengurangan muatan.
    - Menyelamatkan jalan beton K-450 terminal dari kerusakan dini.
* **Kotak Bawah: MENGGUNAKAN APA (ASET FISIK, SENSOR HARDWARE & MODUL PERANGKAT LUNAK):**
  - *Sensor & Timbangan:* Jembatan Timbang Pitless 80 Ton (Load Cell Avery Weigh-Tronix) + Inductive Ground Loop Detektor WCS.
  - *Kamera & Vision AI:* Kamera ANPR Hikvision 4K (Plat Nomor Truk) + Kamera OCR Boks Kontainer (Standar ISO 6346:2022).
  - *Otomasi Gerbang:* Automatic Systems Barrier Gate Magnetic Control + Kiosk Cetak Tiket Thermal Zebra Kiosk KRD.
  - *Perangkat Lunak:* Modul WCS Gate Controller (Event Listener IoT) & YMS Gate Inbound Engine.
* **Banner Demo:** `👉 [LIVE DEMO]: Buka 'pages/gate.php' ➔ Jalankan Simulasi Kamera OCR, ANPR, & Uji Truk Overload SOLAS (>34t)`

### Skrip Pembicara (Speaker Notes)
> *"Menjawab pertanyaan operasional Salwa: 'Truk masuk timbangan, jika lolos ke mana, jika tidak lolos ke mana, dan menggunakan alat apa?'.  
> Pada Flow 1: Begitu truk melindas loop detektor jembatan timbang pitless 80 ton Avery, WCS membaca berat kotor dan kamera ANPR/OCR merekam nomor boks.  
>
> **Kondisi JIKA YA (Lolos ≤ 34 ton):** Tiket e-Pass QR tercetak kurang dari 90 detik, palang terbuka, status database menjadi `GATE_IN_APPROVED`, dan supir diarahkan ke zonanya masing-masing—apakah ke Blok Yard FCL, Blok E Reefer, Rampa Gudang CFS, atau Hanggar Bea Cukai.  
>
> **Kondisi JIKA TIDAK (Overload > 34 ton):** Palang gerbang terkunci rapat, sirine 105 dB berbunyi, status database menjadi `OVERLOAD_REJECTED`. Truk dilarang melintasi yard dan diarahkan langsung belok kiri ke Jalur U-Turn (Putar Balik) untuk keluar terminal. Ini menyelamatkan investasi lantai beton terminal yang bernilai Rp 130 Miliar dari kehancuran."*

---

```
========================================================================================
[SLIDE 5] FLOW 2: PENGANGKATAN KONTAINER & STACKING 3D BAY-ROW-TIER (YMS + WCS)
========================================================================================
```
### Visual Slide
* **Halaman Web & Tautan:** `pages/yard.php` (`http://localhost/Conclusion_Intermodal_Dry_Port/pages/yard.php`) & `pages/alat.php`
* **Kotak Kiri: Siapa yang Mengangkat & Decision Ukuran Boks:**
  - *Decision 1: Ukuran Boks Kontainer 20 Feet vs 40 Feet?*
    - **[JIKA 20 FT]:** Reach Stacker 45 Ton mengatur Spreader hidrolik pada posisi 20 kaki ➔ Menaruh di **Bay Ganjil** (Bay 01, 03, 05, 07).
    - **[JIKA 40 FT]:** Spreader Telescopic hidrolik memanjang otomatis ke 40 kaki via tombol kabin operator ➔ Menaruh di **Bay Genap** (Bay 02, 06, 10, 14).
  - *Penumpukan Blok Intensif (Blok A/B):* Diangkat oleh **RTG Crane 40 Ton** Konecranes 16-Roda.
  - *Palet di Gudang CFS:* Diangkat oleh **Forklift Heavy Duty 3–5 Ton** Linde (bukan crane).
  - *Rangkaian Kereta Api:* Diangkat oleh Reach Stacker di Sepur Simpan Siding Rel 600 meter.
* **Kotak Kanan: Decision Aturan Penempatan 3D Bay-Row-Tier (JIKA YA vs JIKA TIDAK):**
  - *Decision 2: Apakah Penempatan Memenuhi Aturan Stacking (Anti-Floating & Heavy-at-Bottom)?*
    - **[JIKA YA]:** Boks diletakkan di koordinat 3D Bay-Row-Tier yang ditugaskan sistem ➔ Status Database: `STACK_CONFIRMED`. Rasio gerak derek optimal berada pada 1.1 moves/box (menghemat konsumsi BBM solar HSD senilai Rp 1,06 Miliar per tahun).
    - **[JIKA TIDAK]:** Pelanggaran *Corner Casting Rule* (dilarang menaruh boks 20ft di atas 40ft karena sudut baja boks 20ft akan menjebol atap boks 40ft) atau *Anti-Floating Rule* (menaruh boks di Tier $n$ saat Tier $n-1$ kosong) ➔ **Sistem YMS MENOLAK PERINTAH PUTAWAY!** Alarm di komputer kabin operator berbunyi, dan sistem mengalihkan operator ke slot bin alternatif yang aman.
* **Kotak Bawah: MENGGUNAKAN APA (ARMADA ALAT BERAT, HARDWARE & SOFTWARE YMS):**
  - *Armada Derek:* 3 Unit Reach Stacker 45 Ton (Kalmar Gloria DRG450-65S5M) dengan Telescopic Spreader Hidrolik Bromma + 1 Unit RTG Crane 40 Ton Konecranes Electric.
  - *Armada CFS:* Forklift Elektrik 3–5 Ton Linde H30D + Rampa Loading Dock Leveler Hidrolik Rite-Hite.
  - *Komunikasi Kabin:* Komputer Kabin VMT (Vehicle Mounted Terminal) Advantech DLT-V72 terhubung Wireless Mesh Industrial.
  - *Perangkat Lunak:* Modul YMS 3D Yard Allocation Engine, WCS Equipment Dispatcher & Driver Mobile Job Order.
* **Banner Demo:** `👉 [LIVE DEMO]: Buka 'pages/yard.php' & 'pages/simulator.php' ➔ Uji Coba Telescopic Spreader & Alokasi Slot 3D`

### Skrip Pembicara (Speaker Notes)
> *"Menjawab pertanyaan Salwa berikutnya: 'Siapa yang mengangkat kontainer 40 feet, siapa beratnya, dan bagaimana penempatannya?'.  
> Untuk boks 40 feet dan 20 feet, alat utamanya adalah **Reach Stacker 45 Ton Kalmar DRG450**. Spreader-nya bertipe **Telescopic**: jika mengangkat boks 20 kaki, spreader berada pada mode 20ft; jika boks 40 kaki, hidrolik memanjangkan spreader menjadi 40ft secara otomatis dalam hitungan detik.  
>
> Mengenai aturan penempatan 3D di yard:  
> **JIKA YA (Sesuai Aturan):** Boks 20ft ditempatkan di Bay Ganjil, boks 40ft di Bay Genap. Kargo berat (Laden 25 ton) ditaruh di lantai bawah (Tier 1-2), dan boks kosong di Tier 3-4. Status menjadi `STACK_CONFIRMED`.  
> **JIKA TIDAK (Melanggar Aturan):** Jika operator salah ingin menaruh boks 20ft di atas boks 40ft atau menaruh boks melayang, algoritma YMS seketika menolak perintah putaway dan menyalakan alarm di kabin operator. Penataan ini menekan rasio gerakan derek menjadi 1,1 kali angkat per boks, menghemat biaya solar Rp 1,06 Miliar per tahun!"*

---

```
========================================================================================
[SLIDE 6] FLOW 3 & 4: RANTAI DINGIN REEFER & CFS INBOUND (WMS + WCS)
========================================================================================
```
### Visual Slide
* **Halaman Web & Tautan:** `pages/reefer.php & pages/cfs.php` (`http://localhost/Conclusion_Intermodal_Dry_Port/pages/reefer.php`)
* **Kotak Kiri: Flow 3: Cold Chain Reefer (Blok E - 300 Plugs):**
  - *Decision Point:* `Apakah Kargo Dingin Terhubung Listrik < 30 Menit DAN Suhu Berada di -18°C s/d -25°C?`
  - **[JIKA YA]:** Teknisi colok kabel steker industri CEE 380V/32A, status database `REEFER_PLUGGED_ACTIVE`, sensor IoT LoRaWAN merekam suhu dan arus listrik setiap 60 detik, tarif plugging Rp 250.000 per shift tercatat otomatis ke modul billing.
  - **[JIKA TIDAK (Suhu > -15°C atau Listrik Padam >10 Menit)]:** Sistem memicu sirene bahaya `REEFER_BREACH_ALERT`, Automatic Transfer Switch (ATS) otomatis menyalakan Genset cadangan Cummins 1.250 kVA (*failover*), teknisi listrik melakukan pengecekan unit. Menghindarkan klaim kargo beku rusak bernilai $100.000+ per kontainer.
* **Kotak Kanan: Flow 4: Gudang CFS Inbound Stripping Kargo LCL:**
  - *Decision Point:* `Apakah Barcode GS1 SSCC-18 Terbaca Sah (Modulo 10) DAN Kuantitas Fisik Cocok dengan Dokumen ASN?`
  - **[JIKA YA]:** Blind count tally master cocok, Forklift membawa palet dari rampa dock ke rak bin terarah (*Directed Putaway*), status database `PALLET_QC_PASSED`.
  - **[JIKA TIDAK (Barcode Cacat / Kuantitas Selisih)]:** Jika barcode rusak ➔ Operator menekan *Emergency Relabeling* pada PDA untuk mencetak label GS1 baru via printer barcode; Jika kargo selisih/rusak ➔ Sistem mengubah status stok menjadi `BLOCKED` (Karantina QC) agar tidak keluar gudang sebelum diinvestigasi.
* **Kotak Bawah: MENGGUNAKAN APA (ASET LISTRIK, SENSOR IOT, PDA SCANNER & WMS MODUL):**
  - *Fasilitas Reefer:* 300 Soket CEE Mennekes 380V/32A IP67, Trafo PLN 1.250 kVA, Genset Cummins 1.250 kVA + Sensor IoT LoRaWAN Dragino LHT65N.
  - *Fasilitas CFS:* 5 Pintu Rampa Dock Leveler Hidrolik Rite-Hite (D1–D5), Handheld PDA Zebra TC26 Android (2D Imager), Printer Barcode Zebra ZT411.
  - *Armada Angkut CFS:* Forklift Elektrik 3 Ton Linde E30.
  - *Perangkat Lunak:* Modul WMS Cold Chain Monitoring, WMS Inbound Stripping & Directed Putaway Engine.
* **Banner Demo:** `👉 [LIVE DEMO]: Buka 'pages/reefer.php' (Monitoring Suhu 300 Plugs) & 'pages/cfs.php' (Scan Barcode GS1 SSCC-18)`

### Skrip Pembicara (Speaker Notes)
> *"Pada fasilitas penanganan kargo khusus, modul WMS dan WCS menjalankan kontrol yang sangat presisi:  
> Pada Flow 3 Rantai Dingin Reefer di Blok E yang berkapasitas 300 steker pendingin:  
> **JIKA YA (Suhu Aman):** Sensor IoT merekam suhu -20°C secara otomatis tiap 60 detik.  
> **JIKA TIDAK (Suhu Drop atau Listrik Mati):** Sistem seketika membunyikan sirene `REEFER_BREACH_ALERT` dan sakelar otomatis menyalakan genset cadangan 1.250 kVA. Ini melindungi terminal dari risiko klaim kargo rusak bernilai miliaran rupiah.  
>
> Pada Flow 4 Gudang CFS LCL:  
> **JIKA YA (Barcode GS1 Valid):** Tally master memindai barcode palet AI `(00)` SSCC-18 menggunakan PDA Zebra TC26, dan forklift langsung diarahkan ke rak bin tertentu.  
> **JIKA TIDAK (Barcode Rusak atau Kemasan Cacat):** Operator mencetak ulang barcode berstandar GS1, dan status kargo dikunci menjadi `BLOCKED` (Karantina) agar tidak keluar gudang sebelum diinspeksi."*

---

```
========================================================================================
[SLIDE 7] FLOW 5 & 6: KEPATUHAN PABEAN & KERETA API REL SIDING (ERP + TMS)
========================================================================================
```
### Visual Slide
* **Halaman Web & Tautan:** `pages/customs.php & pages/yard.php` (`http://localhost/Conclusion_Intermodal_Dry_Port/pages/customs.php`)
* **Kotak Kiri: Flow 5: Integrasi Kepabeanan CEISA 4.0 DJBC (PMK 190/2022):**
  - *Decision Point:* `Apa Status Profil Risiko Manifes DJBC: Jalur Hijau vs Jalur Merah?`
  - **[JIKA JALUR HIJAU (94,2% Muatan)]:** Webhook CEISA 4.0 menerbitkan Surat Persetujuan Pengeluaran Barang (SPPB) digital dalam waktu <5 menit ➔ Status Database: `SPPB_CLEARED`, kontainer langsung siap diambil tanpa hambatan fisik.
  - **[JIKA JALUR MERAH (5,8% Muatan)]:** Status di database dikunci menjadi `CUSTOMS_HOLD` ➔ Reach Stacker 45t otomatis diarahkan membawa kontainer ke Hanggar Behandle Bea Cukai untuk dipindai Portal Gantry X-Ray 6 MeV. Dilarang keras keluar terminal sebelum inspektur pabean menyetujui SPPB manual.
* **Kotak Kanan: Flow 6: Intermodal Kereta Api Rel Siding 600 Meter:**
  - *Decision Point:* `Apakah Beban Gandar Lokomotif ≤ 18 Ton DAN Smart GPS E-Seal Terverifikasi Utuh?`
  - **[JIKA YA]:** Reach Stacker memuat kontainer ke 30 Gerbong Datar (PPCW) dengan distribusi berat seimbang di tengah, kereta api berangkat tepat waktu menuju Tanjung Priok (waktu tempuh 90 menit), status database `TRAIN_DISPATCHED_ON_TIME`.
  - **[JIKA TIDAK (Kawat Segel E-Seal Putus / Tamper di Rel)]:** Satelit GPS seketika mengirimkan sinyal bahaya `PABEAN_TAMPER_ALERT` ke server CIDP, gerbong diisolasi saat tiba di stasiun, dan Polsuska serta Bea Cukai melakukan penyegelan ulang.
* **Kotak Bawah: MENGGUNAKAN APA (PORTAL X-RAY, INFRASTRUKTUR REL, E-SEAL & TMS MODUL):**
  - *Mesin Pemindai Pabean:* Nuctech FG9056 Portal Gantry X-Ray 6 MeV (Daya tembus 300 mm baja) + Hanggar Behandle Tertutup 1.500 m².
  - *Kereta Api:* 2 Sepur Simpan Rel R54 600m, Lokomotif Diesel CC206 KAI, 30 Gerbong Datar PPCW (Kapasitas 60 TEU).
  - *Perangkat Keamanan:* Smart GPS E-Seal Satelit LoRa/GSM dengan sensor pemutus kawat kargo.
  - *Perangkat Lunak:* Modul TMS Rail Freight Dispatcher, 3D Train Load Planner & Webhook REST API Gateway CEISA 4.0 DJBC.
* **Banner Demo:** `👉 [LIVE DEMO]: Buka 'pages/customs.php' (Webhook SPPB) & 'dashboard.php' (Simulasi Rangkaian Rel Kereta Api)`

### Skrip Pembicara (Speaker Notes)
> *"Kekuatan utama sebuah Intermodal Dry Port terletak pada integrasi kepabeanan dan konektivitas rel kereta api:  
> Pada Flow 5 Kepabeanan:  
> **JIKA HIJAU:** Melalui webhook API CEISA 4.0 DJBC, SPPB digital terbit dalam tempo kurang dari 5 menit, dan kontainer langsung berstatus `SPPB_CLEARED`.  
> **JIKA MERAH:** Status seketika dikunci `CUSTOMS_HOLD` dan Reach Stacker otomatis membawa kontainer ke Hanggar Behandle untuk dipindai Portal Gantry X-Ray 6 MeV berdaya tembus baja 300 mm.  
>
> Pada Flow 6 Moda Kereta Api:  
> **JIKA YA (Segel Utuh):** 30 gerbong datar (60 TEU) diberangkatkan ke Pelabuhan Tanjung Priok dalam waktu pasti 90 menit tanpa terjebak macet tol.  
> **JIKA TIDAK (Segel Rusak):** Sensor satelit Smart GPS E-Seal seketika memicu alarm `PABEAN_TAMPER_ALERT` ke command center terminal untuk diisolasi."*

---

```
========================================================================================
[SLIDE 8] FLOW 7: MODEL PENDAPATAN & ZERO REVENUE LEAKAGE (ERP + YMS)
========================================================================================
```
### Visual Slide
* **Halaman Web & Tautan:** `pages/billing.php` (`http://localhost/Conclusion_Intermodal_Dry_Port/pages/billing.php`)
* **Kotak Kiri: 7 Aliran Pendapatan Komersial (Gross Revenue Rp 76 Miliar / Tahun):**
  - *1. Bongkar Muat Lo-Lo (Lift-On / Lift-Off):* Rp 33,75 Miliar (44,4% — 150.000 gerakan boks @ Rp 225.000)
  - *2. Penumpukan Lapangan Progresif:* Rp 10,80 Miliar (14,2% — Denda Masa Inap)
    - Masa I (Hari 1–3) = Masa Bebas (Rp 0)
    - Masa II (Hari 4–10) = Rp 45.000 / box / hari
    - Masa III (Hari 11+) = Rp 90.000 / box / hari (Tarif Denda Progresif 200%)
  - *3. Bagi Hasil Kereta Api Logistik:* Rp 9,00 Miliar (11,8% — 6.000 Gerbong Datar @ Rp 1.500.000)
  - *4. Gudang CFS Stuffing / Stripping:* Rp 8,10 Miliar (10,7% — Penanganan Kargo LCL @ Rp 90.000/ton)
  - *5. Steker Pendingin Reefer Yard:* Rp 6,25 Miliar (8,2% — Pemakaian Listrik @ Rp 250.000/shift)
  - *6. Jembatan Timbang VGM SOLAS:* Rp 5,50 Miliar (7,2% — Sertifikasi Berat Kotor @ Rp 55.000/truk)
  - *7. Behandle Pemeriksaan Karantina:* Rp 2,62 Miliar (3,5% — Penanganan Jalur Merah @ Rp 350.000/box)
* **Kotak Kanan: Decision Interlock Gate-Out: Zero Revenue Leakage:**
  - *Decision Point:* `Apakah Faktur ERP Odoo LUNAS (PAID) DAN Dokumen Kepabeanan = SPPB_CLEARED?`
  - **[JIKA YA]:** Palang barrier otomatis terbuka, kamera ANPR mengambil foto bukti keberangkatan truk (Digital e-Exit Pass), status database `GATE_OUT_COMPLETED`, garansi Zero Revenue Leakage terwujud!
  - **[JIKA TIDAK]:** Palang Barrier **TETAP TERKUNCI PERMANEN**, Layar Display Digital VMS (Variable Message Sign) menampilkan *"TAGIHAN BELUM LUNAS — HUBUNGI KASIR ODOO"* atau *"STATUS TAHAN BEA CUKAI"*, truk diarahkan ke Holding Lane agar tidak menghambat antrean gerbang keluar.
* **Kotak Bawah: MENGGUNAKAN APA (PALANG GATE-OUT, SISTEM BILLING & KASIR ODOO):**
  - *Perangkat Keras Gerbang Keluar:* Automatic Magnetic Barrier Gate Outbound + Layar Digital VMS (Variable Message Sign) 3-Warna.
  - *Vision AI:* Kamera ANPR Gate-Out Hikvision + RFID Card Reader.
  - *Sistem Akuntansi:* ERP Odoo Finance v17 Community Edition (Modul Invoicing, Journal Entries, Bank Reconciliation & Payment Gateway).
  - *Integrasi Middleware:* Event-Driven Webhook Tariff Engine yang mengunci palang gerbang secara otomatis.
* **Banner Demo:** `👉 [LIVE DEMO]: Buka 'pages/billing.php' ➔ Simulasi Auto-Invoice Odoo & Penguncian Palang Gate-Out`

### Skrip Pembicara (Speaker Notes)
> *"Bapak Dr. Tigor, aliran operasional fisik harus bermuara pada aliran finansial yang sehat dan terkontrol tanpa kebocoran:  
> Dari target throughput 150.000 TEU per tahun, CIDP menghasilkan pendapatan kotor sebesar **Rp 76,02 Miliar per tahun** dari 7 aliran komersial.  
>
> Keunggulan arsitektur kami adalah strategi **Zero Revenue Leakage di Gate-Out**:  
> **JIKA YA (Faktur Lunas & SPPB Rilis):** Palang terbuka secara otomatis, kamera ANPR merekam e-Exit Pass, dan kargo diizinkan keluar terminal.  
> **JIKA TIDAK (Tagihan Belum Lunas atau Masih Tertahan Pabean):** Palang gerbang keluar terkunci permanen! Layar VMS menampilkan peringatan untuk menuju loket kasir Odoo, dan truk dialihkan ke Holding Lane.  
> Ini mengeliminasi 100% risiko piutang tak tertagih yang sering terjadi di terminal konvensional."*

---

```
========================================================================================
[SLIDE 9] EVALUASI KELAYAKAN INVESTASI FINANSIAL: PBP, NPV, IRR & ROI
========================================================================================
```
### Visual Slide
* **Model Finansial Excel:** `business_analyst/MODEL_FINANSIAL_DAN_BLUEPRINT_CIDP.xlsx` (Sheet *"Cash Flow 10 Thn"*)
* **Kartu 1: Arus Kas & EBITDA:**
  - *Gross Revenue:* Rp 76,02 Miliar
  - *Beban OPEX:* Rp 24,79 Miliar (Solar HSD, Payroll 82 personil, sewa rel TAC KAI, M&R)
  - *EBITDA:* **Rp 51,23 Miliar (Margin 67,4%)**
  - *Depresiasi (20 Tahun):* Rp 12,50 Miliar
  - *PPh Badan (22%):* Rp 8,52 Miliar
  - *Laba Bersih Setelah Pajak (EAT):* **Rp 30,21 Miliar / Tahun**
  - *Arus Kas Bersih (Net Cash Flow):* **Rp 42,71 Miliar / Tahun**
* **Kartu 2: Payback Period (PBP) — 5.84 Tahun (~5 Thn 10 Bln):**
  $$\text{PBP} = \frac{\text{CAPEX Rp 249,50 M}}{\text{Net Cash Flow Rp 42,71 M}} = \mathbf{5,84\text{ Tahun}}$$
  - Modal investasi awal Rp 250 Miliar lunas kembali dalam waktu kurang dari 6 tahun. Standar aman industri pelabuhan adalah 8–10 tahun; CIDP balik modal 2 tahun lebih cepat!
* **Kartu 3: Net Present Value (NPV) — +Rp 22.913.000.000:**
  - Horizon Waktu: 10 Tahun | WACC (Discount Rate): 10%
  - Total Arus Kas Kumulatif Terdiskonto: Rp 262,45 Miliar vs Investasi Awal Rp 249,50 Miliar.
  - $\text{NPV} = +\text{Rp 22,91 Miliar } (> 0)$. Bernilai positif membuktikan secara absolut bahwa proyek **SANGAT LAYAK (FEASIBLE)**.
* **Kartu 4: Internal Rate of Return (IRR: 11.90%) & ROI (140.6%):**
  - **IRR: 11,90%**, melampaui *Hurdle Rate* suku bunga kredit komersial bank (8,50%). Proyek ini sangat *bankable* bagi sindikasi perbankan.
  - **ROI 10 Tahun: 140,6%**, di mana total akumulasi laba bersih 10 tahun (Rp 350,9 M) jauh melampaui seluruh modal awal.
* **Banner Model:** `👉 [MODEL FINANSIAL EXCEL]: Buka Sheet 'Cash Flow 10 Thn' di MODEL_FINANSIAL_DAN_BLUEPRINT_CIDP.xlsx ➔ Formula NPV & IRR Asli`

### Skrip Pembicara (Speaker Notes)
> *"Inilah pembuktian matematis kelayakan bisnis yang kami sajikan kepada Dewan Direksi dan Investor:  
> Dengan alokasi CAPEX awal sebesar Rp 249,5 Miliar dan beban operasional rutin OPEX sebesar Rp 24,78 Miliar per tahun, terminal menghasilkan EBITDA sebesar Rp 51,23 Miliar dengan margin keuntungan 67,4%, serta laba bersih setelah pajak sebesar Rp 30,21 Miliar per tahun.  
>
> Berdasarkan perhitungan arus kas 10 tahun pada model Excel kami:  
> **1. Payback Period tercapai dalam 5,84 tahun**—jauh lebih cepat dibanding rata-rata terminal logistik yang membutuhkan 8 hingga 10 tahun.  
> **2. Net Present Value (NPV) bernilai positif sebesar +Rp 22,91 Miliar** pada discount rate 10%.  
> **3. Internal Rate of Return (IRR) mencapai 11,90%**, melampaui suku bunga pinjaman komersial bank sebesar 8,5%.  
> Angka-angka ini membuktikan secara ilmiah dan finansial bahwa proyek pembangunan Dry Port CIDP 35 Hektar sangat layak untuk didanai."*

---

```
========================================================================================
[SLIDE 10] DASHBOARD INTELLIGENCE: EXECUTIVE COMMAND CENTER [C-LEVEL DSS]
========================================================================================
```
### Visual Slide
* **Halaman Web & Tautan:** `dashboard.php?page=beranda` (`http://localhost/Conclusion_Intermodal_Dry_Port/dashboard.php`)
* **Kolom Kiri: 4 Metrik Keputusan Direksi (C-Level DSS):**
  1. *Yard Occupancy Rate (YOR) — 68,4%:* Berada di ambang batas aman operasional (<80%). Memberi sinyal dini sebelum terminal mengalami kemacetan tumpukan dan mencegah penalti *port congestion surcharge* ($50/TEU).
  2. *Throughput Harian — 482 TEU:* Mencapai 96,4% dari target harian (500 TEU). Mengukur kecepatan aliran kargo intermodal gerbang truk dan stasiun kereta api.
  3. *Average Dwell Time — 2,3 Hari:* Sangat produktif dibanding rata-rata nasional pelabuhan laut (4–5 hari), melipatgandakan kecepatan perputaran modal kerja.
  4. *Gross Daily Revenue — Rp 184,5 Juta:* Menampilkan arus kas harian tertagih seketika dengan Collection Rate 98,2%.
* **Kolom Kanan: Business Intelligence & Analitik Visual Data:**
  - *Hourly Traffic Flow Chart:* Grafik fluktuasi arus kedatangan truk per jam untuk perencanaan penugasan operator gerbang dan alat berat Reach Stacker (jam sibuk 10:00–15:00).
  - *Dwell Time Heatmap Blok Yard:* Peta visual warna mendeteksi kontainer yang menginap kritis (>7 hari) di Blok A–E untuk memicu penagihan tarif denda Masa III secara otomatis.
  - *Live Event Stream & Consignment Tracking:* Memonitor 5 transaksi operasional terakhir (gate-in, timbang, lift-off, rilis SPPB) secara detik-per-detik untuk transparansi audit.
* **Banner Demo:** `👉 [LIVE DEMO]: Buka Halaman 'Dashboard' (dashboard.php?page=beranda) ➔ Demonstrasikan Gauge YOR 68.4%, Chart.js, & Heatmap`

### Skrip Pembicara (Speaker Notes)
> *"Sesuai mandat silabus perkuliahan Bapak Dr. Tigor mengenai **Mockup Dashboard KPI**, konsultan kami membangun sebuah **Executive Command Center** berbasis Business Intelligence yang dapat diakses langsung pada `dashboard.php`.  
> Dashboard ini bukan sekadar pajangan grafik, melainkan alat bantu keputusan direksi (*Decision Support System*):  
> Indikator YOR di level 68,4% memastikan lapangan beroperasi lancar tanpa macet.  
> Melalui grafik lalu lintas per jam, Manajer Operasional dapat mengatur giliran kerja operator alat berat pada jam sibuk.  
> Dan heatmap dwell time memberikan peringatan visual jika ada kargo yang menginap lebih dari 7 hari agar segera dipindahkan atau ditagih denda progresifnya. Seluruh data ini terhubung real-time dengan basis data operasional."*

---

```
========================================================================================
[SLIDE 11] MATRIKS PENANGANAN ANOMALI & QA TESTING MATRIX (PASS 100%)
========================================================================================
```
### Visual Slide
* **Halaman Web & Tautan:** `pages/simulator.php` (`http://localhost/Conclusion_Intermodal_Dry_Port/pages/simulator.php`)
* **Matriks Pengujian Terpadu (Exception Handling & QA Testing Matrix):**

| Kondisi Uji / Anomali | Decision Logika BA | Perilaku Sistem yang Diharapkan & Penanganan Sistem | Status Hasil |
| :--- | :--- | :--- | :--- |
| **Truk Gate-In Normal (<34t)** | Berat ≤ 34t & ISO Sah | Palang buka <30s, e-Pass cetak, arahkan truk ke blok yard/reefer/CFS | **PASS (100%)** |
| **Truk Overload SOLAS (>34t)** | Berat > 34.000 kg | Palang kunci permanen, sirine bunyi, tolak masuk & arahkan ke Jalur U-Turn | **PASS (100%)** |
| **Check Digit ISO Salah** | Modulo 11 Gagal | Operator salah ketik digit kontainer; pendaftaran diblokir otomatis oleh sistem | **PASS (100%)** |
| **Barcode Palet Cacat di CFS** | GS1 SSCC Gagal Baca | Scanner gagal ➔ Trigger tombol Emergency Relabeling & cetak barcode baru | **PASS (100%)** |
| **Pencegahan Tabrakan Yard** | Anti-Floating Violation | Sistem tolak perintah taruh boks di Tier 3 tanpa adanya Tier 2 di bawahnya | **PASS (100%)** |
| **Kontainer Jalur Merah** | Customs Hold Active | Kontainer dilarang keras Gate-Out sebelum ada rilis resmi SPPB Bea Cukai | **PASS (100%)** |
| **Kalkulasi Denda Inap** | Event-Driven Billing | Masa I (Rp 0), Masa II (Rp 45rb), Masa III (Rp 90rb) tertagih tanpa kebocoran | **PASS (100%)** |

* **Kepatuhan SLA Non-Fungsional:**
  - Waktu Respon API Rata-Rata: **85 milidetik** (Ambang batas SLA < 150 ms)
  - Waktu Siklus Transaksi Gerbang: **90 detik** (Standar internasional < 120 detik)
  - Jaminan Kebocoran Penagihan: **0% (Zero Revenue Leakage)**
  - Hasil Pengujian: **QA PASS 100%**
* **Banner Test Bed:** `👉 [LIVE TEST BED]: Uji Kasus Batas (Edge-Cases) di 'pages/simulator.php' • Respon API 85 ms • PASS 100%`

### Skrip Pembicara (Speaker Notes)
> *"Sebagai spesialis Penjaminan Mutu (QA), kami menggabungkan analisis Exception Handling silabus dengan matriks pengujian fungsional kasus ekstrem (*edge-cases*) pada simulator `pages/simulator.php`.  
> Seluruh skenario batas—mulai dari deteksi truk normal, pencegahan truk overload SOLAS, rumus modulo check digit ISO dan GS1, larangan penumpukan melayang di yard, isolasi Jalur Merah, hingga perhitungan denda progresif otomatis—telah diuji dan memperoleh hasil **100% PASS**.  
> Waktu respon API kami berada pada rata-rata 85 milidetik, menjamin performa transaksi yang sangat cepat dan handal."*

---

```
========================================================================================
[SLIDE 12] KESIMPULAN REKOMENDASI KONSULTAN & ROADMAP MENUJU GO-LIVE
========================================================================================
```
### Visual Slide
* **Portal Aplikasi Terpadu:** `http://localhost/Conclusion_Intermodal_Dry_Port/` (`dashboard.php`)
* **4 Pilar Kesimpulan Strategis:**
  1. *Kelayakan Finansial Mutlak (Feasible):* Investasi CAPEX Rp 250 Miliar lunas kembali dalam **5,84 tahun** dengan Laba Bersih Rp 30,2 Miliar/tahun, NPV positif **+Rp 22,9 Miliar**, dan IRR **11,90%** di atas suku bunga pinjaman komersial bank (8,50%).
  2. *Efisiensi Operasional (BPR):* Waktu siklus gerbang dipangkas dari 15 menit menjadi **90 detik**, Dwell Time ditekan dari 6,8 hari menjadi **2,3 hari**, dan eliminasi 100% boks terselip melalui algoritma 3D Bay-Row-Tier.
  3. *Standar Global & Kepatuhan:* Menerapkan identifikasi global GS1-128 SSCC-18 AI `(00)` dan ISO 6346, serta kepatuhan pabean CEISA 4.0 DJBC (PMK 190/2022) dengan rilis SPPB otomatis 94,2% dan segel Smart GPS E-Seal kereta api.
  4. *Penjaminan Mutu & Zero Leakage:* 100% kasus ekstrem tertangani pada QA Testing Matrix, dan mekanisme interlock Gate-Out menggaransi nol kebocoran pendapatan terminal.
* **Rekomendasi Final Strategis:**
  Sistem CIDP YMS terbukti secara empiris dan kuantitatif **SANGAT LAYAK (FEASIBLE)** untuk dilanjutkan ke tahap implementasi komersial. Solusi ini menyelesaikan inefisiensi pelabuhan konvensional, menjamin keuntungan finansial bagi pemodal, dan memenuhi seluruh regulasi logistik nasional maupun internasional.
* **Banner Penutup:** `🌐 LIVE PORTAL: http://localhost/Conclusion_Intermodal_Dry_Port/  •  SESI TANYA JAWAB (Q&A)  •  DR. TIGOR FRANKY, S.T., M.T.`

### Skrip Pembicara (Speaker Notes) — Naufal Andika Heditya & Zulfikar J. Fatah
> *"Bapak Dr. Tigor Franky dan rekan-rekan sekalian,  
> Sebagai penutup dari kajian strategis Conclusion Supply Chain Consultant, kami menyimpulkan bahwa rancang bangun **Conclusion Intermodal Dry Port YMS** telah membuktikan nilai keunggulannya secara komprehensif:  
>
> 1. **Secara Finansial:** Proyek ini sangat menguntungkan dengan pengembalian modal dalam 5,8 tahun dan IRR 11,9%.  
> 2. **Secara Operasional:** Rekayasa proses memangkas dwell time hingga 66% dan menghemat biaya bahan bakar derek melalui penataan 3D Bay-Row-Tier yang presisi.  
> 3. **Secara Regulasi & Standar:** Seluruh modul patuh terhadap standar global GS1 SSCC-18, ISO 6346, dan Bea Cukai CEISA 4.0.  
> 4. **Secara Kualitas:** Matriks QA kami membuktikan seluruh penanganan keputusan JIKA YA dan JIKA TIDAK bekerja 100% sempurna dengan jaminan Zero Revenue Leakage.  
>
> Seluruh ekosistem ini telah terintegrasi penuh dan siap diuji langsung pada portal web kami.  
> Rekomendasi konsultan kami adalah melanjutkan cetak biru ini ke tahap peluncuran komersial.  
>
> Terima kasih atas perhatian Bapak Dr. Tigor dan rekan-rekan mahasiswa ITL Trisakti. Kami persilakan untuk sesi diskusi dan tanya jawab. Selamat pagi."*

---
*Laporan Bahan Tayang Presentasi ini disusun secara independen oleh Tim Konsultan Conclusion Supply Chain Consultant — Program Studi S1 Manajemen Logistik ITL Trisakti 2026.*
