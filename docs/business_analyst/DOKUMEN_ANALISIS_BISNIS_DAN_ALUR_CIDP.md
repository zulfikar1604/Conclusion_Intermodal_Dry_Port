# 📘 DOKUMEN CETAK BIRU BUSINESS ANALYSIS & ALUR PROSES BISNIS END-TO-END
## Prototype Conclusion Intermodal Dry Port (CIDP) 35 Ha
### Mengubah Spesifikasi Fitur Software (WMS, TMS, WCS, YMS, ERP) Menjadi Aturan Bisnis & FSD

* **Mata Kuliah:** Teknologi dan Perangkat Lunak Logistik
* **Dosen Evaluator / Pengampu:** Dr. Tigor Franky, S.T., M.T. (ITL Trisakti)
* **Entitas Konsultan:** Conclusion Supply Chain Consultant
* **Tim Business Analyst & QA:**
  * **Naufal Andika Heditya** (24D507001012) — *Lead Business Analyst & QA*
  * **Zulfikar Jafarudin Fatah** (24D507001001) — *Lead System Architect*
  * **Armansyah Muchtarrom** (24D507001010) — *Hardware & Weighbridge Specialist*
  * **Afriansyah Ayubi** (24D507001026) — *Software & ERP Billing Specialist*
  * **Juan Gamaliel** (24D507001016) — *Data Integration & Stacking Rules Specialist*
* **Dasar Silabus Baru:** Modul Perkuliahan Perangkat Lunak Logistik — *Pemetaan Fitur Menjadi Aturan Bisnis (Business Rules), Matriks Integrasi Data, Exception Handling, dan FSD*
* **File Output Presentasi:** 
  * [`PRESENTASI_BUSINESS_ANALYST_CIDP.pptx`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/docs/business_analyst/PRESENTASI_BUSINESS_ANALYST_CIDP.pptx)
  * [`Conclusion Intermodal Dry Port_Business Analysis.pptx`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/docs/business_analyst/Conclusion%20Intermodal%20Dry%20Port_Business%20Analysis.pptx)
* **File Output Gambar Diagram Terpisah (High-Resolution):**
  * [`ALUR_END_TO_END_PROSES_BISNIS_CIDP.jpg`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/docs/business_analyst/ALUR_END_TO_END_PROSES_BISNIS_CIDP.jpg)
  * [`ALUR_GATE_DAN_TIMBANGAN_DECISION_RULES.jpg`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/docs/business_analyst/ALUR_GATE_DAN_TIMBANGAN_DECISION_RULES.jpg)
  * [`ALUR_PENGANGKATAN_DAN_PENEMPATAN_KONTAINER.jpg`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/docs/business_analyst/ALUR_PENGANGKATAN_DAN_PENEMPATAN_KONTAINER.jpg)
  * [`PETA_ARSITEKTUR_DAN_KONSEP_APLIKASI_CIDP.jpg`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/docs/business_analyst/PETA_ARSITEKTUR_DAN_KONSEP_APLIKASI_CIDP.jpg)

---

## 🎯 EXECUTIVE SUMMARY & JAWABAN LANGSUNG NOTULENSI

Berdasarkan notulensi evaluasi perkuliahan bersama Bapak Dr. Tigor Franky, dokumen ini menjawab secara tegas, lugas, dan terstruktur poin-poin krusial yang harus diperjelas:

### 1. Business Role & Equipment: "Siapa yang Nimbang? Barangnya Apa? Kontainer 40 Feet Siapa yang Angkat?"
1. **Siapa yang Nimbang:**
   * **Sensor Otomasi (WCS):** Jembatan Timbang Dinamis Pitless 80 Ton (*Avery Weigh-Tronix Load Cell*) dengan kecepatan respons pembacaan $< 3$ detik.
   * **Aktor Lapangan:** Supir Truk Trailer (*External Driver*) yang menempelkan e-Ticket RFID di Kiosk, diawasi oleh *Gate Clerk / Tallyman Inbound*.
2. **Barangnya Apa:**
   * **Kontainer FCL Kering (Dry Cargo 20ft & 40ft):** Peti kemas standar muatan ekspor/impor umum.
   * **Kontainer Dingin (Reefer Cargo):** Kargo bersuhu terkendali (Chilled $2^\circ\text{C s/d } 8^\circ\text{C}$, Frozen $-18^\circ\text{C}$).
   * **Kargo Curah / Palet LCL CFS:** Barang lepasan yang harus dibongkar muat di gudang CFS (*Container Freight Station*).
   * **Dangerous Goods (HazMat / B3):** Kargo berbahaya bahan kimia mudah terbakar/beracun.
   * **Jalur Merah Bea Cukai:** Peti kemas yang memerlukan pemeriksaan fisik pabean.
3. **Kontainer 40 Feet Siapa yang Angkat:**
   * **Alat Utama:** **RTG Crane (Rubber Tyred Gantry Crane) Kapasitas 45 Ton** yang mencakup bentang 6+1 baris dan 5+1 tinggi tier.
   * **Alat Sekunder:** **Reach Stacker Kapasitas 45 Ton** (*Kalmar / Konecranes*) dengan *telescopic spreader* otomatis.
   * **Operator Manusia:** Operator RTG / Reach Stacker berlisensi SIO Kemnaker yang menerima instruksi kerja (*Work Instruction*) secara real-time melalui layar VMT (*Vehicle Mounted Terminal*) di dalam kabin.
   * **Sebagai Pembanding:**
     * Kontainer 20ft Kosong (*Empty*) diangkat oleh **Empty Container Handler (ECH) 10 Ton** (hingga 8 tier).
     * Kargo palet di CFS diangkat oleh **Forklift 3-5 Ton**.
     * Kontainer di jalur kereta api diangkat oleh **RMG Crane (Rail Mounted Gantry) 45 Ton** atau Reach Stacker Intermodal.

---

### 2. Business Rules Timbangan: "Truk Masuk Timbangan, Jika Lulus ke Mana, Jika Tidak ke Mana?"
* **Parameter Input Timbangan:**
  1. `Gross_Weight`: Berat aktual dari sensor load cell WCS.
  2. `Tare_Weight`: Berat kosong truk dari database ERP/TMS.
  3. `Net_Weight`: `Gross_Weight - Tare_Weight`.
  4. `VGM_SOLAS`: Berat muatan terverifikasi dari shipping manifest.
* **Aturan Validasi (Business Rules):**
  1. **Batas Legal Muatan:** Berat Kotor Total $\le 34.000\text{ kg}$ (UU RI No. 22/2009 Kelas Jalan 1).
  2. **Toleransi Berat:** Selisih bobot netto terhadap dokumen $\le \pm 2.0\%$ (Kepatuhan SOLAS VGM Bab VI).
  3. **Integritas Penomoran:** Check Digit ISO 6346 Modulo 11 valid.
* **JIKA LULUS (Semua Aturan Valid):**
  * Palang *Barrier Gate* otomatis terbuka.
  * Status database berubah: `GATE_IN_APPROVED`.
  * Kiosk mencetak **Container Movement Slip (CMS) QR Code**.
  * **Truk Diarahkan ke:**
    * *FCL Dry* $\rightarrow$ Menuju **Container Yard Blok A/B/C/D** sesuai koordinat Bay-Row-Tier.
    * *Reefer* $\rightarrow$ Menuju **Blok E (Reefer Yard)** untuk segera disambung ke colokan listrik 380V (Max exposure 30 menit).
    * *Kargo Curah LCL* $\rightarrow$ Menuju **Gudang CFS Dock D1-D5** untuk proses stripping.
    * *HazMat B3* $\rightarrow$ Menuju **Blok F (Tanggul Isolasi Kimia)**.
    * *Jalur Merah Bea Cukai* $\rightarrow$ Menuju **Hanggar Behandle Bea Cukai**.
* **JIKA TIDAK LULUS (Overweight > 34T / Selisih > 2% / ISO Invalid):**
  * Palang *Barrier Gate* **TETAP TERKUNCI (LOCK)**.
  * Lampu indikator menyala merah, sirine alert berbunyi di pos gate.
  * Status database berubah: `OVERLOAD_REJECTED` atau `DISCREPANCY_HOLD`.
  * **Truk Diarahkan ke Mana:**
    * Truk **DILARANG KERAS** masuk ke lapangan penumpukan (mencegah jalan beton K-450 ambles dan menghindari sanksi IMO).
    * Truk diarahkan belok kiri masuk ke **REJECT LANE / HOLDING BAY (Area Karantina Timbangan)** di samping pos gate.
    * Supervisor Gate melakukan klarifikasi manifest bersama supir. Jika kesalahan administratif, dilakukan verifikasi revisi. Jika terbukti kelebihan muatan murni, truk diputarbalikkan keluar terminal melalui **U-Turn Bypass Exit** atau dialihkan untuk bongkar muatan berlebih (*Partial Stripping*).

---

### 3. Stacking Rules: "Pengangkatan Kontainer, Penempatannya Seperti Apa?"
Sistem penumpukan YMS CIDP menerapkan sistem koordinat 4 Dimensi: **Blok - Bay - Row - Tier**.
1. **Aturan Ukuran 20 Feet vs 40 Feet (Bay Rule):**
   * **Bay Ganjil (01, 03, 05, 07):** Khusus dialokasikan untuk 2 unit kontainer 20 Feet.
   * **Bay Genap (02, 06, 10, 14):** Khusus dialokasikan untuk 1 unit kontainer 40 Feet (menempati bentang 2 bay 20ft).
   * **Aturan Keamanan:** Dilarang keras menaruh kontainer 40ft di atas kontainer 20ft kecuali dipasang twistlock penyangga standar maritim.
2. **Aturan Distribusi Berat (Weight Stacking Hierarchy):**
   * **Tier 1 & Tier 2 (Paling Bawah):** Kontainer *Heavy Laden* ($> 24\text{ Ton}$) wajib di dasar untuk mengunci *Center of Gravity* tumpukan.
   * **Tier 3 (Tengah):** Kontainer *Medium Weight* ($15 - 24\text{ Ton}$).
   * **Tier 4 & Tier 5 (Paling Atas):** Kontainer *Light* ($< 15\text{ Ton}$) atau Kontainer Kosong (*Empty*). Mencegah roboh ditiup angin kencang di dry port terbuka.
3. **Aturan Zonasi Kargo:**
   * **Reefer Yard (Blok E):** Hanya dialokasikan pada baris yang memiliki tiang listrik 380V, maksimal Tier 3 agar mudah dimonitor teknisi genset.
   * **HazMat Yard (Blok F):** Dilengkapi tanggul penahan tumpahan kimia (*bunded area*) berjarak minimal 50 meter dari gudang CFS dan kantor.
   * **Ekspor vs Impor:** Kontainer siap muat KA dialokasikan berdekatan dengan Rel Siding 600m; kontainer impor ditaruh mendekati Gate Out.

---

### 4. Solusi Masalah: "Di Mana Nanti Ditaruhnya dan Konsepnya Seperti Apa di Aplikasi Saya?"
Aplikasi terasa sangat kompleks karena menggabungkan **5 Sistem Logistik Terpadu** ke dalam 1 Web Portal. Kuncinya adalah **Pemisahan Peran (Separation of Concerns)**:

| Modul Logistik | Konsep Bisnis & Peran Sistem | Di Mana Ditaruhnya di Aplikasi CIDP? | File Halaman Terkait |
|---|---|---|---|
| **1. WCS / WES** | Mengontrol hardware fisik lapangan dalam waktu $< 500\text{ ms}$: timbangan 80T, ANPR, OCR, palang barrier, diverter conveyor. | Modul Otomasi Gerbang & Scanner Lapangan | [`pages/gate.php`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/gate.php)<br>[`pages/scanner.php`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/scanner.php) |
| **2. YMS** | Mengatur tata letak penumpukan 35 Ha, denah 3D Bay-Row-Tier, dispatching alat RTG/RS/ECH, dan dwelling time alert. | Modul Yard Management & Manajemen Alat | [`pages/yard.php`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/yard.php)<br>[`pages/alat.php`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/alat.php)<br>[`pages/denah.php`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/denah.php) |
| **3. WMS** | Mengatur pergerakan kargo curah/palet di gudang CFS (stripping/stuffing), rak Aisle-Rack-Shelf-Bin, FEFO/FIFO, dan cold storage. | Modul CFS Inbound/Outbound & Monitoring Reefer | [`pages/cfs.php`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/cfs.php)<br>[`pages/reefer.php`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/reefer.php)<br>[`master_rak.php`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/master_rak.php) |
| **4. TMS** | Mengatur rute armada truk ekspedisi di luar gate, GPS Geofencing, bukti e-POD, dan transshipment gerbong KA di rel siding 600m. | Modul Ekspedisi Truk & Intermodal Rel Siding | [`pages/trucking.php`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/trucking.php)<br>[`pages/intermodal.php`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/intermodal.php) |
| **5. ERP** | Mengelola master data pelanggan, penagihan THC, tarif penumpukan, denda progresif demurrage, dan validasi SPPB pabean. | Modul Terminal Billing & Customs CEISA | [`pages/billing.php`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/billing.php)<br>[`pages/customs.php`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/customs.php)<br>[`pages/settings.php`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/pages/settings.php) |

Dengan pemetaan di atas:
- **Setiap halaman web memiliki fokus tunggal.**
- **Logika bisnis timbangan ditaruh di `pages/gate.php` (WCS + YMS).**
- **Logika pengangkatan RTG 45T & penempatan ditaruh di `pages/alat.php` & `pages/yard.php` (YMS).**
- **Logika barang curah & ASN ditaruh di `pages/cfs.php` (WMS).**
- **Logika kereta api ditaruh di `pages/intermodal.php` (TMS).**
- **Logika denda demurrage & palang keluar ditaruh di `pages/billing.php` (ERP).**

---

## 📑 PEMBAHASAN 14 BAB SILABUS MATERI BARU BUSINESS ANALYST

### 1. Mengubah Fitur Menjadi Aturan Bisnis (Developer vs BA Mindset)
* **Developer:** Berfokus pada implementasi coding (`if (weight <= 34000) save();`), pembuatan tombol UI (*Button Submit*), dan koneksi database.
* **Business Analyst:** Berfokus pada parameter masukan, validasi ambang batas, regulasi hukum (IMO SOLAS Bab VI & UU RI No. 22/2009), serta dampak terhadap operasional nyata.
* **Rumus 3-Langkah Analisis BA:**
  1. *Fitur / Trigger Event:* Kejadian fisik atau pemicu tombol.
  2. *Validasi Business Rule:* Logika penolakan/penerimaan data.
  3. *Dampak & Pembaruan Sistem:* Perubahan record database dan aksi mesin fisik di lapangan.

### 2. Fitur WMS Inbound
* **2.1 ASN Matching:** Validasi nomor PO dari ERP. Jika kuantitas fisik > PO, sistem otomatis memicu status **Over-receipt Alert** untuk mencegah klaim barang berlebih.
* **2.2 Directed Putaway Engine:** Algoritma penentuan bin rak otomatis berdasarkan dimensi SKU, sertifikasi HazMat, dan suhu ruang.
* **2.3 QC & Quarantine Status:** Stok otomatis dikunci ke status **Blocked** sebelum lolos inspeksi kualitas oleh surveyor.

### 3. Fitur WMS Inventory Management
* **3.1 Real-Time Bin Tracking:** Pemetaan hierarki lokasi Aisle-Rack-Shelf-Bin untuk memastikan akurasi pelacakan 100%.
* **3.2 Automated Stock Rotation (FEFO / FIFO):** Mengunci alokasi barang berdasarkan *Expiry Date* paling dekat saat Pick Task dibuat.
* **3.3 Cycle Counting Workflow:** Perhitungan stok berkala menggunakan metode *blind counting* tanpa menghentikan operasional gudang.

### 4. Fitur WMS Outbound
* **4.1 Slotting Optimization (Analisis ABC):** Penataan ulang rak berkala berdasarkan frekuensi perputaran transaksi (Fast/Medium/Slow moving).
* **4.2 Picking Strategy Engine (Wave, Batch, Zone):** Penggabungan beberapa order menjadi 1 gelombang petik guna memangkas jarak tempuh operator.
* **4.3 Packing & Weight Verification:** Pengecekan toleransi berat $\pm 2\%$ sebelum pencetakan resi dan surat jalan.

### 5. Fitur WCS / WES (Hardware Control)
* **5.1 Conveyor Diverter Control:** Sinyal pindaian barcode dikonversi menjadi perintah dorong mekanik (*pusher/diverter*) ke jalur pemilah yang tepat.
* **5.2 Tunnel / Mass Scanner Integration (DWS):** Perekaman data dimensi volumetrik, timbangan berat, dan kode GS1 secara simultan dalam $< 500\text{ ms}$.
* **5.3 AMR / Crane Fleet Routing & Anti-Deadlock:** Algoritma penentuan rute robotik dan crane untuk mencegah tabrakan dan kondisi deadlock di lorong gudang/lapangan.

### 6. Fitur TMS Planning
* **6.1 Dynamic Route Optimization:** Pencarian rute terpendek dengan mempertimbangkan kemacetan lalu lintas, regulasi jam operasional jalan, dan batas tonase.
* **6.2 3D Load Planning & Consolidation:** Tata letak kubikasi bak truk guna memaksimalkan faktor keterisian (*Cubic Utilization* hingga 92%).
* **6.3 Carrier Rate Audit & Settlement:** Perhitungan otomatis ongkos angkut vendor berdasarkan matriks tarif, jarak, dan zona wilayah.

### 7. Fitur YMS (Yard Management System)
* **7.1 Dock Door Scheduling:** Penjadwalan *time-slot* kedatangan truk guna mencegah penumpukan armada di gerbang utama.
* **7.2 Trailer Position Tracking:** Peta visual status kontainer di lapangan: *Empty, Laden, In-Stripping, Customs Hold*.
* **7.3 Gate Management & Demurrage Alert:** Pencatatan otomatis waktu masuk/keluar serta notifikasi denda jika melampaui masa bebas penumpukan (*Free Time* 3 hari).

### 8. Fitur TMS Tracking & Execution
* **8.1 Real-Time GPS Track & Trace:** Pemantauan koordinat GPS armada serta *Geofencing Alert* jika keluar jalur resmi.
* **8.2 Driver Mobile App & Digital POD (e-POD):** Pengambilan foto serah terima fisik, tanda tangan digital pelanggan, dan koordinat GPS penyerahan.
* **8.3 Exception Alert:** Pemicu notifikasi darurat ke CS saat truk mengalami mogok, pecah ban, atau kecelakaan di perjalanan.

### 9. Matriks Integrasi Data Fitur (Data Mapping Matrix)

| Modul Fitur | Data Input (Source) | Proses Validasi Sistem | Data Output (Target) |
|---|---|---|---|
| **ASN Receiving (WMS)** | PO Data dari ERP | Cek nomor PO & status kuantitas | Stock Status: Received / Over-receipt Alert |
| **Picking Engine (WMS/WCS)** | Order List dari ERP | Cek aturan FEFO & ketersediaan bin rak | Task Command ke AMR / Forklift PDA |
| **Mass Scanning (WCS)** | Barcode & Sensor Timbangan | Cek kesesuaian berat $\pm 2\%$ vs manifes | Signal Diverter & Resi Label Tercetak |
| **Route Planner (TMS)** | Alamat Kirim & Dimensi Muatan | Kalkulasi rute tercepat & batas tonase | Manifest Jalan & GPS Route Assignment |

---

### 10. Perancangan Fitur Penanganan Anomali (Exception Handling)
1. **Anomali Overweight Gate Timbangan (WCS + YMS):**
   * Pemicu: Berat kotor $> 34.000\text{ kg}$ atau selisih $> 2\%$.
   * Aksi Sistem: Palang terkunci, sirene bunyi, status `OVERLOAD_REJECTED`, dialihkan ke *Reject Lane / Holding Bay*.
2. **No-Read Barcode pada Conveyor (WCS):**
   * Pemicu: Barcode robek / kamera scanner gagal membaca kode.
   * Aksi Sistem: Diverter membelokkan barang ke *Reject Lane*, sistem memicu alert ke PDA operator untuk pindaian manual.
3. **Shortage Stok saat Picking (WMS):**
   * Pemicu: Fisik barang di rak kurang dari jumlah order sistem.
   * Aksi Sistem: Operator menekan tombol *“Item Shortage”*, WMS memicu *Blind Cycle Count* otomatis dan mengalihkan sisa pick task ke rak alternatif.
4. **Penyimpangan Suhu Reefer (WMS + YMS):**
   * Pemicu: Suhu kontainer beku naik melampaui $-15^\circ\text{C}$.
   * Aksi Sistem: Sirine bahaya aktif, sistem mengirim SMS dispatch ke Teknisi Genset untuk perbaikan konektor listrik 380V.

---

### 11. Penyusunan Spesifikasi Fungsional (FSD) Standar
Setiap modul fungsional di CIDP memiliki dokumen FSD dengan 4 pilar:
1. **User Role & Permission:** Aktor yang berwenang mengeksekusi fitur (Admin, Gate Clerk, Crane Operator, Billing Cashier).
2. **Trigger Condition:** Kejadian pemicu jalannya fungsi (tombol klik, interupsi sensor load cell, scan QR).
3. **Input Fields & Validation Rules:** Tipe data, batas nilai, dan logika validasi matematika.
4. **System Response & Error Handling:** Tampilan UI, perubahan status database, dan pesan peringatan ramah pengguna.

---

### 12, 13, & 14. Alur Integrasi 5 Modul & Pemetaan Interaksi

```
┌──────────────┐     Sales Order / Invoicing     ┌──────────────┐
│     ERP      │ ◄─────────────────────────────► │     TMS      │
│ (Billing/PO) │                                 │ (Truck/Rail) │
└──────┬───────┘                                 └──────┬───────┘
       │ ASN Data & Threshold                           │ Slot & ETA
       ▼                                                ▼
┌──────────────┐      Pick Task / Conveyor       ┌──────────────┐
│     WMS      │ ◄─────────────────────────────► │     WCS      │
│ (CFS/Reefer) │                                 │ (Scale/Gate) │
└──────┬───────┘                                 └──────┬───────┘
       │                                                │ Weight & Scan
       │             Alokasi Stacking & Yard Map        │
       └──────────────────► ┌──────────────┐ ◄──────────┘
                            │     YMS      │
                            │ (Yard 35 Ha) │
                            └──────────────┘
```

| Sistem | Fokus Spesifik Dry Port & Cold Chain | Output / Aksi Nyata |
|---|---|---|
| **ERP** | Manajemen Kontrak, Penagihan THC, & Sinkronisasi Bea Cukai | Menerbitkan Faktur, Validasi SPPB, dan Kontrol Piutang |
| **WMS** | Optimasi FEFO, Stripping Gudang CFS, & Cold Storage | Membagi Pick Task per zona suhu dan rak selektif |
| **WCS** | Kontrol Hardware Lapangan (Timbangan 80T, ANPR, Diverter) | Membuka/mengunci palang dan mengarahkan paket conveyor |
| **YMS** | Penataan Yard 3D Bay-Row-Tier & Dispatch Alat RTG 45T | Menugaskan RTG/Reach Stacker dan memonitor dwelling time |
| **TMS** | Rute Truk Ekspedisi, e-POD, & Sinkronisasi Rel Siding KA | Mencetak surat jalan, pelacakan GPS, & transfer flatcar KA |

---

## 🖥️ DAFTAR FILE ARTIFAK HASIL GENERATE

1. **PowerPoint Presentation (16:9 Widescreen — 14 Slide Lengkap):**
   * [`docs/business_analyst/PRESENTASI_BUSINESS_ANALYST_CIDP.pptx`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/docs/business_analyst/PRESENTASI_BUSINESS_ANALYST_CIDP.pptx)
   * [`docs/business_analyst/Conclusion Intermodal Dry Port_Business Analysis.pptx`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/docs/business_analyst/Conclusion%20Intermodal%20Dry%20Port_Business%20Analysis.pptx)
   * [`business_analyst/PRESENTASI_BUSINESS_ANALYST_CIDP.pptx`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/business_analyst/PRESENTASI_BUSINESS_ANALYST_CIDP.pptx)

2. **Standalone High-Resolution Diagram Images (.JPG):**
   * [`docs/business_analyst/ALUR_END_TO_END_PROSES_BISNIS_CIDP.jpg`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/docs/business_analyst/ALUR_END_TO_END_PROSES_BISNIS_CIDP.jpg) *(Peta Keputusan Terminal End-to-End dari Gate In hingga Gate Out)*
   * [`docs/business_analyst/ALUR_GATE_DAN_TIMBANGAN_DECISION_RULES.jpg`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/docs/business_analyst/ALUR_GATE_DAN_TIMBANGAN_DECISION_RULES.jpg) *(Detail Decision Rules Timbangan: Lulus ke mana vs Gagal ke mana)*
   * [`docs/business_analyst/ALUR_PENGANGKATAN_DAN_PENEMPATAN_KONTAINER.jpg`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/docs/business_analyst/ALUR_PENGANGKATAN_DAN_PENEMPATAN_KONTAINER.jpg) *(Detail Siapa yang Ngangkat, Kontainer 40ft/20ft/Empty/CFS & Aturan Stacking)*
   * [`docs/business_analyst/PETA_ARSITEKTUR_DAN_KONSEP_APLIKASI_CIDP.jpg`](file:///C:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/docs/business_analyst/PETA_ARSITEKTUR_DAN_KONSEP_APLIKASI_CIDP.jpg) *(Pemetaan Konsep & Di Mana Letaknya di File PHP Aplikasi CIDP)*
