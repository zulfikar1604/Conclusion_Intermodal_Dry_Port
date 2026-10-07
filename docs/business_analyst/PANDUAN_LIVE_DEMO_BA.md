# 🖥️ PANDUAN PRAKTIK LIVE DEMO WEB & SKRIP PRESENTASI
## Skenario Demonstrasi Aplikasi Langsung untuk Tim Business Analyst & QA
**Klien / Evaluator:** Dr. Tigor Franky, S.T., M.T. — Institut Transportasi dan Logistik (ITL) Trisakti  
**Entitas:** **Conclusion Supply Chain Consultant (2026)**  
**Presenter:** Naufal Andika Heditya (*Lead Business Analyst & QA*) & Zulfikar Jafarudin Fatah (*Lead System Architect*)  

---

## 🎯 Strategi Presentasi: Harmoni Alt+Tab & Dual-Screen
Bahan presentasi PowerPoint Anda telah disinkronkan secara presisi dengan sistem web prototype CIDP YMS:
👉 `http://localhost/Conclusion_Intermodal_Dry_Port/dashboard.php`

Pada slide PowerPoint yang memiliki banner oranye-kuning **`[LIVE DEMO]`**, presenter cukup menekan **Alt+Tab** ke browser Chrome/Edge, mendemonstrasikan modul dashboard terkait sesuai skrip berikut, lalu kembali ke slide PowerPoint.

---

## 🧭 Peta Alur Demonstrasi Berdasarkan 12 Slide

```
[ SLIDE 1 – 3: COVER KONSULTAN, FINANCIAL FLOW & BPR AS-IS TO-BE ]
  └── Diagnosis Masalah, Aliran Finansial & Pemangkasan Dwell Time ke 2.3 Hari
                      │
[ SLIDE 4: STANDAR IDENTIFIKASI GS1 SSCC-18 VS ISO 6346 ]
  └── [DEMO 1]: Buka Menu Scanner Optik (pages/scanner.php)
  └── Pindai barcode palet AI (00) SSCC-18 & uji validasi Check Digit Modulo 11 ISO 6346
                      │
[ SLIDE 5 – 7: KAJIAN KELAYAKAN FINANSIAL CAPEX, OPEX, REVENUE & EVALUASI ]
  └── [DEMO 2]: Buka Menu Billing & Faktur ERP (pages/billing.php)
  └── Tunjukkan simulasi Auto-Invoice, tarif Lo-Lo, denda inap Masa II-III, & Odoo Sync
                      │
[ SLIDE 8: DASHBOARD INTELLIGENCE 1 — EXECUTIVE COMMAND CENTER ]
  └── [DEMO 3]: Buka Menu Dashboard Beranda (dashboard.php?page=beranda)
  └── Tunjukkan Gauge YOR 68.4%, Throughput 482 TEU, Dwell Heatmap & Gross Daily Revenue
                      │
[ SLIDE 9: DASHBOARD INTELLIGENCE 2 — OPERASIONAL & YARD OPTIMIZATION ]
  └── [DEMO 4]: Buka Menu Gate (pages/gate.php) & Yard (pages/yard.php)
  └── Jalankan simulasi gate: Truk lolos (<34t) vs Truk ditolak alarm SOLAS overload (>34t)
                      │
[ SLIDE 10: DASHBOARD INTELLIGENCE 3 — MITIGASI RISIKO REEFER, PABEAN & CFS ]
  └── [DEMO 5]: Buka Menu Reefer (page=reefer) & Kepabeanan (page=customs)
  └── Tunjukkan telemetri suhu 300 steker reefer, rilis SPPB Jalur Hijau, & Smart E-Seal transit
                      │
[ SLIDE 11 – 12: QA MATRIX & REKOMENDASI FINAL KONSULTAN ]
  └── Closing statement konsultan & kesiapan menjawab pertanyaan teknis/finansial Dr. Tigor
```

---

## 🎬 Panduan Demonstrasi Dashboard Langkah Demi Langkah

### 📍 TITIK DEMO 1 (Pada Slide 4: Standar GS1 SSCC-18 & ISO 6346)
* **Kapan Dibuka:** Saat menjelaskan mengapa kontainer butuh nomor boks ISO dan palet butuh SSCC-18.
* **Halaman yang Dituju:** `dashboard.php?page=scanner` ([`pages/scanner.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/scanner.php))
* **Aksi yang Ditunjukkan ke Dosen:**
  1. Tunjukkan badge atas: **PIC: Naufal Andika Heditya (Business Analyst & QA Specialist)**.
  2. **Pemindaian Barcode GS1 SSCC-18:**
     * Pada panel *Simulasi Telemetri Input Scanner*, pilih palet: `(00)389912345000000128`.
     * Klik **`[Verifikasi Kode Barcode]`** ➔ Engine computer vision membaca AI `(00)`, validasi Modulo 10 sukses, dan menampilkan rincian barang: 100 Karton Susu Impor di Rak Aisle B-02.
  3. **Validasi Check Digit ISO 6346:**
     * Masukkan nomor valid: `MSKU9182374` ➔ Hijau: *VALID ISO 6346 (Check Digit 4 Cocok)*.
     * Ubah digit terakhir menjadi salah: `MSKU9182370` ➔ Merah: *INVALID CHECK DIGIT (Ditolak Sistem)*.
* **Skrip Ucapan Konsultan:**
  > *"Bapak Dr. Tigor, inilah penjaminan mutu data sejak titik awal. Standar ISO 6346 mengamankan identitas wadah baja kontainer, sementara SSCC-18 mengamankan unit palet di dalamnya. Rumus modulo otomatis memastikan tidak ada data salah ketik yang masuk ke database."*

---

### 📍 TITIK DEMO 2 (Pada Slide 6: Mesin Billing & Zero Revenue Leakage)
* **Kapan Dibuka:** Saat memaparkan strategi *Zero Revenue Leakage* dan integrasi tagihan ke ERP Odoo.
* **Halaman yang Dituju:** `dashboard.php?page=billing` ([`pages/billing.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/billing.php))
* **Aksi yang Ditunjukkan ke Dosen:**
  1. Perlihatkan kartu ringkasan: **Gross Daily Revenue Rp 184.500.000** dan tingkat penagihan piutang (*Collection Rate*) 98.2%.
  2. **Detail Faktur Multi-Tarif:**
     * Klik **Detail Faktur** pada boks yang menginap: perlihatkan bahwa biaya Lo-Lo (Rp 250rb), jembatan timbang (Rp 50rb), dan denda penumpukan progresif Masa II & III terhitung otomatis dari selisih tanggal fisik masuk.
  3. **Simulasi Pembayaran:**
     * Klik **`[Bayar Tagihan / Lunas]`** ➔ Status invoice berubah `PAID`, tercatat cap waktu pembayaran, dan menerbitkan rilis keluar gerbang.
* **Skrip Ucapan Konsultan:**
  > *"Inilah bukti Zero Revenue Leakage dari konsultan kami. Tidak ada lagi peti kemas yang keluar gerbang tanpa melunasi denda penumpukan, karena sistem secara cerdas mengunci otorisasi Gate-Out hingga status faktur di ERP berstatus PAID."*

---

### 📍 TITIK DEMO 3 (Pada Slide 8: Executive Command Center Dashboard)
* **Kapan Dibuka:** Saat membahas Slide 8 mengenai Business Intelligence untuk pengambilan keputusan pimpinan.
* **Halaman yang Dituju:** `dashboard.php?page=beranda` ([`dashboard.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/dashboard.php))
* **Aksi yang Ditunjukkan ke Dosen:**
  1. **4 Metrik Utama Direksi:**
     * *Yard Occupancy Rate (YOR) 68,4%:* Tunjukkan bahwa posisi jarum okupansi berada di zona hijau aman di bawah batas kritis 80%.
     * *Throughput Harian 482 TEU:* Dari target harian 500 TEU.
     * *Average Dwell Time 2,3 Hari:* Sangat cepat dibanding rata-rata nasional pelabuhan laut 4–5 hari.
     * *Gross Daily Revenue Rp 184,5 Juta:* Menunjukkan denyut kas harian terminal.
  2. **Analitik Visual Chart.js:**
     * Tunjukkan grafik fluktuasi arus truk per jam (peak hour 10:00 - 15:00) untuk alokasi staf gerbang.
     * Tunjukkan *Dwell Time Heatmap* per blok penumpukan (Blok A, B, C, D, E) untuk mendeteksi penumpukan kargo macet.
     * Tunjukkan *Live Event Stream* yang mencatat setiap kejadian fisik secara real-time.
* **Skrip Ucapan Konsultan:**
  > *"Dashboard Beranda ini kami bangun sebagai Decision Support System (DSS) untuk General Manager Terminal. Bukan sekadar visualisasi indah, melainkan alat kontrol: jika okupansi yard mendekati 75%, sistem langsung membunyikan peringatan dini agar penumpukan dialihkan ke blok cadangan sebelum terjadi kongesti."*

---

### 📍 TITIK DEMO 4 (Pada Slide 9: Gate TAT & Yard Optimization)
* **Kapan Dibuka:** Saat memaparkan efisiensi biaya operasional lapangan dan penghematan solar alat berat.
* **Halaman yang Dituju:** `dashboard.php?page=gate` ([`pages/gate.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/gate.php))
* **Aksi yang Ditunjukkan ke Dosen:**
  1. **Skenario Truk Normal (<34t):**
     * Pilih skenario truk lolos bobot 32.450 kg. Klik **`[Jalankan Simulasi Sensor Gate In]`**.
     * Perhatikan bersama dosen progress bar 5-tahap berjalan otomatis: ANPR, OCR, Timbangan Pass, alokasi slot Blok B-04, dan e-Pass QR terbit dalam waktu <30 detik.
  2. **Skenario Truk Overweight (>34t) - Kepatuhan SOLAS:**
     * Pilih truk over-tonase 37.200 kg. Klik simulasi.
     * Tunjukkan sistem menolak: status menjadi **OVERLOAD ALERT**, palang tetap terkunci (*LOCKED*), dan LED menampilkan peringatan penolakan.
* **Skrip Ucapan Konsultan:**
  > *"Di gerbang inilah efisiensi dimulai: waktu siklus ditekan menjadi 90 detik. Dan sistem secara otomatis menolak truk overload di atas 34 ton, menjamin kepatuhan 100% terhadap regulasi keselamatan maritim internasional SOLAS VGM."*

---

### 📍 TITIK DEMO 5 (Pada Slide 10: Cold Chain Reefer & Bea Cukai CEISA 4.0)
* **Kapan Dibuka:** Saat membahas mitigasi risiko fasilitas khusus bernilai kargo tinggi.
* **Halaman yang Dituju:** `dashboard.php?page=reefer` ([`pages/reefer.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/reefer.php)) dan `dashboard.php?page=customs` ([`pages/customs.php`](file:///d:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/customs.php))
* **Aksi yang Ditunjukkan ke Dosen:**
  1. **Reefer Cold Chain Monitoring (`page=reefer`):**
     * Tunjukkan status 300 steker pendingin dengan telemetri suhu real-time (rata-rata -18.4°C).
     * Jelaskan bahwa sistem alarm otomatis ini melindungi muatan bernilai $100.000+ per box dari risiko klaim pembusukan.
  2. **Kepabeanan CEISA 4.0 (`page=customs`):**
     * Tunjukkan perbandingan Jalur Hijau (94,2%) vs Jalur Merah (5,8%).
     * Klik **`[Rilis SPPB Otomatis]`** pada kontainer Jalur Hijau ➔ Status langsung berubah menjadi `SPPB CLEARED` dengan QR signature resmi DJBC.
     * Tunjukkan status pengawasan transit kargo via **Smart GPS E-Seal** pada jalur rel sepur simpan KA.
* **Skrip Ucapan Konsultan:**
  > *"Bapak Dosen, inilah wujud integrasi mitigasi risiko: pada kargo dingin, telemetri IoT menjaga suhu kargo agar tidak rusak. Dan pada kargo kepabeanan, integrasi CEISA 4.0 DJBC memastikan dokumen SPPB dirilis seketika sesuai regulasi PMK 190/2022."*

---

## ❓ Kisi-Kisi Pertanyaan Kritis Klien / Dosen & Jawaban Kunci

### ❓ "Apakah perhitungan CAPEX Rp 250 Miliar dan Payback Period 5,84 tahun ini realistis?"
> **Jawaban Naufal (Lead BA):**  
> *"Sangat realistis dan berbasis benchmark industri, Bapak Dr. Tigor.  
> Dari CAPEX Rp 249,5 Miliar, 52% dialokasikan untuk perkerasan beton kapasitas 80 Ton di atas lahan 35 Ha, dan 22% untuk alat berat 3 Reach Stacker Kalmar serta 1 RTG Crane Konecranes.  
> Pada target throughput 150.000 TEU per tahun (hanya 410 TEU/hari dari kapasitas terpasang 250.000 TEU), pendapatan kotor mencapai Rp 76 Miliar. Setelah dipotong OPEX Rp 24,8 Miliar dan pajak PPh 22%, arus kas bersih tahunan adalah Rp 42,7 Miliar.  
> CAPEX dibagi arus kas menghasilkan Payback Period **5,84 tahun**. Untuk infrastruktur pelabuhan 35 Ha yang berumur ekonomis 25 tahun, modal kembali dalam waktu kurang dari 6 tahun adalah pencapaian investasi yang sangat sehat dan bankable."*

### ❓ "Mengapa YMS ini punya begitu banyak dashboard terpisah?"
> **Jawaban Naufal (Lead BA):**  
> *"Setiap dashboard dirancang berdasarkan prinsip **Separation of Concerns & Role-Based Access Control (RBAC)**, Pak:  
> 1. Dashboard Eksekutif (`page=beranda`): Untuk General Manager melihat metrik makro (YOR, Dwell Time, Arus Kas).  
> 2. Dashboard Gate & Yard (`page=gate` & `page=yard`): Untuk manajer operasional memantau antrean truk, VGM, dan rasio gerak derek.  
> 3. Dashboard Fasilitas Khusus (`page=reefer`, `page=customs`, `page=cfs`): Untuk officer spesialis memitigasi risiko pembusukan kargo dingin dan kepatuhan pabean Bea Cukai.  
> Semua dashboard ini teragregasi ke satu basis data relasional MySQL tunggal tanpa siloing informasi."*
