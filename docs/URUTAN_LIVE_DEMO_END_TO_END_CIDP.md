# Panduan & Urutan Live Demo End-to-End — CIDP DPMS (35 Ha)

Dokumen ini adalah panduan resmi skenario presentasi dan demonstrasi langsung (**Live Demo Runbook**) untuk **Conclusion Intermodal Dry Port Management System (CIDP DPMS)**. Seluruh alur, halaman modul, tombol aksi, integrasi sensor/API, hingga pencetakan dokumen kertas resmi A4 telah diaudit dan terverifikasi bekerja *end-to-end* terhadap server Apache & database MySQL `cidp_yms`.

---

## 1. Persiapan Pra-Demo & Arsitektur Multi-Device (5 Laptop / PC + Layar Utama)

Agar demonstrasi terasa seperti operasional terminal nyata (*Terminal Control & Distributed Operations*), tim kelompok menjalankan aplikasi secara bersamaan di jaringan/server lokal (`http://localhost/Conclusion_Intermodal_Dry_Port` atau IP LAN server XAMPP):

1. **Login Portal Utama (`landing.php`):**
   - Kredensial demo terintegrasi (*Single Portal Simulation Mode*, akses penuh) sudah terisi otomatis di modal login:
     - Email: `admin@cidp.ac.id` / Kata Sandi: `admin123` → klik tombol **`Masuk`**.
     - Akun role lain yang terdaftar di tabel `login`: `operator@cidp.ac.id` (`staf`), `client@cidp.ac.id` (`user`), `driver@cidp.ac.id` (`driver`).
2. **Reset Baseline Bersih Sebelum Presentasi Dimulai:**
   - Buka modul **Integrasi Data & OAuth** (`dashboard.php?page=integration`) atau panggil `api/demo_control.php?action=reset`.
   - Kondisi awal terjamin bersih:
     - **30 manifest Pre-Advice** (`PG-...-001` s.d. `PG-...-030`) beserta 30 kontainer berstatus `expected` dan 30 truk berstatus `scheduled` (masih berada **di luar** dry port).
     - Contoh sampel utama yang siap dipakai demo:
       - Kontainer Dry #1: `MSCU9180379` (Truk `B 1137 KFA`, Blok target `A`).
       - Kontainer Dry #2: `MAEU9180744` (Truk `B 1274 TRB`, Blok target `B`).
       - Kontainer Reefer: `ONEU9181483` (`cargo_type = reefer`, Blok target `REEFER`).
     - **2 Rangkaian Kereta Api Pasoso**: `KA 2518` (Inbound `arrived`, 12/30 gerbong terisi) dan `KA 2519` (Outbound `loading`, 0/30 gerbong terisi).

### Pembagian Peran & Layar Multi-Device Anggota Kelompok

| No | Nama Anggota & Peran Resmi | Halaman Utama yang Dipegang saat Demo | Fokus Demonstrasi |
| :-: | :--- | :--- | :--- |
| **1** | **Zulfikar Jafarudin Fatah** <br>*Lead System Architect & Full-Stack Programmer* | • **Cockpit Eksekutif** (`page=beranda`)<br>• **Digital Twin (Data Nyata)** (`page=twin`)<br>• **Denah Master 35 Ha** (`page=denah`)<br>• **Simulator 3D** (`page=simulator`)<br>• **Pengaturan Global** (`page=settings`) | Layar Utama/Proyektor: Pusat kendali **TOC Live Asset Locator**, pemantauan **3 Aliran Sinkron (Fisik–Informasi–Finansial)** pada Digital Twin SSE, perbedaan Simulator 3D vs Digital Twin, serta cetak Laporan Eksekutif A4. |
| **2** | **Armansyah Muchtarrom** <br>*Hardware & Infrastructure Specialist* | • **Gate & Timbangan VGM** (`page=gate`)<br>• **Reefer Cold Chain** (`page=reefer`)<br>• **Alat Berat & RTG GPS** (`page=alat`)<br>• **Integrasi Data & OAuth** (`page=integration`) | Memicu sensor perangkat keras (**ANPR/OCR/RFID**, Timbangan 80T SOLAS VGM, RTK-GNSS Reach Stacker, Soket 380V LoRaWAN Reefer) dan demonstrasi keamanan **OAuth 2.0 JWT, HMAC-SHA256, & DLQ**. |
| **3** | **Juan Gamaliel** <br>*Yard Planning, CFS & Multimodal Specialist* | • **Yard & Stacking Block** (`page=yard`)<br>• **Container Freight Station** (`page=cfs`)<br>• **Rail Siding KA Pasoso** (`page=intermodal`)<br>• **Trucking & Shunting** (`page=trucking`)<br>• **Inventori Kontainer** (`page=kontainer`) | Pengaturan penumpukan slot Blok-Bay-Row-Tier, *stripping/stuffing* LCL ke palet SSCC-18 di gudang CFS, muat/bongkar gerbong `KA 2518`/`KA 2519`, dan cetak *CFS Tally Sheet*. |
| **4** | **Naufal Andika Heditya** <br>*Business Analyst & QA Specialist* | • **Bea Cukai CEISA 4.0** (`page=customs`)<br>• **Scanner Optik AIDC** (`page=scanner`)<br>• Audit Mutu Gate-Out & *Cold Chain Compliance* | Pengawasan pabean (*Hold* Jalur Merah vs rilis **SPPB CEISA 4.0**), uji **Dual-Clearance Gate-Out Interlock**, pemindaian optik AIDC, serta cetak dokumen resmi **SPPB A4** dan **Sertifikat Reefer HACCP A4**. |
| **5** | **Afriansayah Ayubi** <br>*Software & ERP Process Specialist* | • **Billing & ERP Odoo** (`page=billing`)<br>• Sync Data Integrasi ERP H2H<br>• Laporan Keuangan Eksekutif (`report_executive.php`) | Menunjukkan bagaimana setiap aksi fisik (Gate-In VGM, LoLo Alat Berat, Relokasi, CFS, Listrik Reefer) otomatis mengakruasi faktur, melakukan pelunasan VA/Host-to-Host, serta cetak **Faktur Resmi A4**. |

---

## 2. Perbedaan Kunci untuk Dijelaskan ke Penguji: Simulator 3D vs Digital Twin

Sebelum atau selama demonstrasi visualisasi 3D, jelaskan dengan tegas pemisahan arsitektur dua modul berikut:

1. **Simulator 3D (Eksplorasi Fasilitas)** — `dashboard.php?page=simulator`
   - **Sifat:** **100% Read-Only Non-Destructive Sandbox**.
   - **Fungsi:** Hanya membaca *snapshot* posisi awal lewat `api/simulator_action.php?action=get_state` untuk memperkenalkan tata letak fasilitas dry port 35 Ha (Gate Inbound/Outbound, Jembatan Timbang 80T, Blok Yard A–E, Zona Reefer, Gudang CFS, Rail Siding KA Pasoso) dan menyimulasikan animasi gerak truk/Reach Stacker/RTG di browser (**Tanpa mengubah/merusak data MySQL produksi**).
2. **Digital Twin (Data Nyata)** — `dashboard.php?page=twin`
   - **Sifat:** **Real-Time Live Telemetry Console berbasis Database MySQL + SSE (`api/stream_twin.php`)**.
   - **Fungsi:** Alat berat 3D/2D, status barrier gate, jumlah kontainer di yard, jumlah SPPB cleared, serta argo pendapatan **berubah murni mengikuti data nyata** saat event masuk melalui sensor (`api/ingest_event.php`) atau modul operasional manual.

---

## 3. Urutan Live Demo Operasional End-to-End (Tahap demi Tahap)

### Tahap 0 — Pembukaan di Pusat Kontrol Utama (TOC Command Center) & Metrik Kuantitatif
- **Layar Utama (Zulfikar):** Buka `dashboard.php?page=beranda` dan buka tab kedua pada `dashboard.php?page=twin`.
- **Tunjukkan:**
  - Strip **SLA Kuantitatif & Efisiensi** di Control Tower: **Target OTIF ($\ge 95\%$)**, **Akurasi Peramalan VBS (MAE & MAPE $<10\%$)** pada grafik `Gate vs VBS Demand`, **Efisiensi Cycle Time** ($18{,}2$ menit vs SLA 35 menit), dan **Efisiensi Biaya & ROI (0% Revenue Leakage)**.
  - KPI awal terminal setelah reset bersih (`0` kontainer di dalam yard, 30 manifest Pre-Advice antri di luar) dan fitur filter tabel **`No` + `Tampil: 10/25/50/Semua`** di setiap modul.
  - Fitur **Pusat Kendali TOC: Pelacak Posisi Kargo & Armada** (*TOC Universal Asset Locator* yang memanggil `api/track_asset.php`): ketik atau klik pill cepat `MSCU9180379` untuk memperlihatkan bahwa sistem langsung melacak posisi aset secara *live* tanpa re-entry data (*Zero Re-Entry SSoT*).

---

### Tahap 1 — Otomasi Siklus Masuk Utama 6 Langkah (*Hardware Edge Pipeline*)
- **Device Hardware (Armansyah):** Buka `dashboard.php?page=integration` → Tab **1. Pre-Advice & Trigger Sensor Lapangan**.
- **Layar Utama (Zulfikar):** Pantau pergerakan live di **Digital Twin** (`page=twin`) sambil menyorot **Live Handshake & Tri-Flow Inspector**.
- **Eksekusi Berurutan Step 1 s.d. 6** (untuk kontainer pertama `MSCU9180379` / truk `B 1137 KFA`):
  1. Klik **STEP 1 — `1. Gate-In ANPR/OCR/RFID`**:
     - Event `TRUCK_GATE_ARRIVAL` mencocokkan plat `B 1137 KFA` + ISO 6346 `MSCU9180379` dengan tabel `preadvice` (*Zero Re-Entry*). Truk berubah ke status `at_gate`, barrier inbound terbuka.
  2. Klik **STEP 2 — `2. Jembatan Timbang 80T VGM`**:
     - Event `TRUCK_WEIGHBRIDGE` mencatat berat kotor ke kontainer & truk, lalu **otomatis menerbitkan invoice akrual Biaya Timbang Rp 75.000**.
  3. Klik **STEP 3 — `3. Lift-Off Spreader Twistlock`**:
     - Event `EQUIPMENT_MOVE` + `CONTAINER_LIFT` menggerakkan **Reach Stacker `RS-02`** mengangkat box dan **mengakruasi invoice Tarif Lift-Off Rp 325.000**.
  4. Klik **STEP 4 — `4. Yard Stacking DGPS/RTK`**:
     - Event `CONTAINER_STACK` mengunci posisi presisi `Blok A / Bay 01 - Row 01 - Tier 01` (`status = in_yard`) dan mulai menyalakan argo Dwell Time.
  5. Klik **STEP 5 — `5. Webhook E-Seal CEISA 4.0`**:
     - Event `CUSTOMS_CEISA` mengubah status pabean `MSCU9180379` menjadi `SPPB_CLEARED` secara H2H (ditandai aksen hijau pada Digital Twin).
  6. Klik **STEP 6 — `6. Pelunasan & Edge Interlock`**:
     - Event `PAYMENT_SETTLED` melunasi faktur `PENDING` menjadi `PAID` dan men-trigger audit `BILLING_PAID`.
- **Verifikasi Pusat Kontrol (Zulfikar):** Di **TOC Search** (`page=beranda`), klik **Lacak** pada `MSCU9180379` — tunjukkan bahwa lokasi, truk, alat, status Bea Cukai (`SPPB_CLEARED`), dan status billing kini Sinkron Real-Time!

---

### Tahap 2 — Operasional Manual Multi-Modul & Koordinasi Multi-Device
*(Menggunakan kontainer kedua, misal `MAEU9180744` / `B 1274 TRB`, dikerjakan bertahap dari masing-masing laptop anggota)*

1. **Gate-In ANPR & Timbangan Manual (Armansyah — `page=gate`):**
   - Di `dashboard.php?page=gate`, picu aksi **`Trigger ANPR Masuk & Buka Palang`** (`gate_action = trigger_anpr_gate_in`) dan **`Trigger Timbang & Terbitkan VGM`** (`gate_action = trigger_weighbridge`) untuk `MAEU9180744` (`B 1274 TRB`).
   - Sistem menempatkan kontainer ke Yard dan mengakruasi tagihan Lift-Off (`Rp 525.000`), Storage (`Rp 275.000`), dan Timbangan (`Rp 75.000`).
   - Klik tombol cetak **`Pass`** pada tabel gate (`document.php?type=gate_pass&plate=B%201274%20TRB&container=MAEU9180744`) untuk menunjukkan **Surat Jalan & EIR Resmi A4** dengan nama driver dinamis sesuai database (`$doc_data['driver_name']`).
2. **Alat Berat VMT & Relokasi Yard (Armansyah & Juan — `page=alat` & `page=yard`):**
   - Armansyah membuka `dashboard.php?page=alat`: menugaskan **Reach Stacker `RS-01`** (`alat_action = dispatch_job`) memindahkan `MAEU9180744` lalu melepas spreader (`release_spreader` → status alat kembali `idle`).
   - Juan membuka `dashboard.php?page=yard`: melakukan relokasi antar-blok (`yard_action = relocate_container`) beserta log operator resmi & pembaruan Live Twin.
3. **Gudang CFS LCL Stripping & Paletisasi GS1 SSCC-18 (Juan — `page=cfs`):**
   - Buka `dashboard.php?page=cfs`: buat Work Order baru (`cfs_action = create_job`, `STRIPPING` pada `MAEU9180744`) lalu jalankan sensor timbangan lantai / scan palet HHT (`trigger_floor_scale` / `trigger_hht_scan`).
   - Setiap palet mendapat kode **SSCC-18 valid Modulo 10** dan biaya CFS (`cfs_amount`) langsung tercatat di billing.
   - Buka dokumen **CFS Tally Sheet & Stripping Manifest A4** (`document.php?type=cfs_tally&container=MAEU9180744`).
4. **Reefer Cold Chain LoRaWAN 380V (Armansyah & Naufal — `page=reefer` & `page=integration`):**
   - Gunakan kontainer reefer (`ONEU9181483`) via tombol `Colok Soket Reefer (-18°C)` → `Telemetri Reefer Normal` → `Alarm Deviasi Suhu (-13.2°C)` di `page=integration`, ATAU lewat form `plug_in` & `trigger_lorawan_ping` di `dashboard.php?page=reefer`.
   - Tunjukkan akrual biaya listrik (`reefer_power_amount`) dan pencatatan alarm HACCP saat deviasi suhu melebihi toleransi.
   - Buka sertifikat resmi **Sertifikat Rantai Dingin / Reefer Cold Chain Compliance A4** (`document.php?type=reefer_cert&container=ONEU9181483`) dengan pemeriksa QA **Naufal Andika Heditya**.
5. **Multimodal Rail Siding KA Pasoso (Juan — `page=intermodal`):**
   - Buka `dashboard.php?page=intermodal`: pilih kontainer di Yard dan muat ke gerbong rangkaian `KA 2519` (`rail_action = manual_load_wagon`), atau jalankan tombol **`Muat Kereta Siding`** di `page=integration`.
   - Tunjukkan di **TOC / Digital Twin** bahwa status kontainer berpindah ke jalur rel (`on_rail`, blok `RAIL`) dan jumlah gerbong terisi pada `KA 2519` bertambah otomatis.

---

### Tahap 3 — Pengawasan Bea Cukai CEISA 4.0 & Bukti *Dual-Clearance Interlock*
*(Highlight utama keandalan aturan bisnis sistem)*

1. **Skenario Blokade Jalur Merah (Naufal — `page=customs`):**
   - Buka `dashboard.php?page=customs` dan klik aksi **`Hold / Tahan ke Jalur Merah`** (`customs_action = hold_red_lane`) pada `MAEU9180744` (atau gunakan tombol **`X-Ray: Temuan Jalur Merah`** di `page=integration`).
   - **Dampak terintegrasi otomatis:** Status kontainer berubah `RED_LANE_CHECK` dan seluruh Job / Palet CFS kontainer terkait ikut terkunci `HOLD` (tidak bisa didispatch keluar gudang).
2. **Uji Blokade Dual-Clearance di Gate-Out (Armansyah — `page=gate`):**
   - Tunjukkan pada tabel/form Gate-Out di `dashboard.php?page=gate` (`Trigger Rilis Keluar (Dual Clearance)` / `release_gate_out`) bahwa sistem **MENOLAK keras** karena syarat ganda (**SPPB_CLEARED** + **0 faktur belum lunas**) belum terpenuhi.
3. **Pelunasan Tagihan ERP Bertahap & Rilis SPPB (Naufal & Afriansayah — `page=customs` & `page=billing`):**
   - **Naufal (`page=customs`):** Jalankan **`Rilis SPPB (Jalur Hijau)`** (`release_sppb`), lalu cetak dokumen resmi **SPPB A4 (Kop Bea Cukai)** melalui tombol cetak (`document.php?type=sppb&container=MAEU9180744`).
   - **Afriansayah (`page=billing`):** Buka `dashboard.php?page=billing`. Tunjukkan seluruh faktur terakrual (`LoLo`, `Storage`, `CFS`, `Reefer`) yang sinkron dari modul operasi. Lunasi **hanya 1 faktur terlebih dahulu** — jika masih ada faktur `PENDING` tersisa pada kontainer yang sama, Gate-Out tetap menolak! Setelah **seluruh faktur kontainer dibayar `PAID`**, tampilkan cetakan resmi **Faktur / Official Invoice A4** (`document.php?type=invoice&no=...&container=MAEU9180744`).
4. **Gate-Out Terotorisasi:**
   - Kembali ke `dashboard.php?page=gate`: jalankan **`Trigger Rilis Keluar (Dual Clearance)`** (`release_gate_out`). Palang outbound terbuka, status kontainer & truk resmi menjadi `gate_out`, dan siklus komersial ditutup.

---

### Tahap 4 — Audit Keamanan API Gateway, Penanganan Lonjakan (Surge) & BCP Failover
*(Sangat kuat untuk tanya-jawab / sesi uji penguji IT & Keandalan Bisnis)*

Buka `dashboard.php?page=integration` pada laptop **Armansyah** atau **Zulfikar**:
1. **Tab `Antrean Error (DLQ)` — Isolasi Eror, Keamanan & Surge/BCP:**
   - **Skenario 1 • HTTP 401 (`Payload Palsu / MITM`):** Buktikan perubahan berat `34.850 kg` → `20.000 kg` gagal verifikasi HMAC-SHA256 dan dikarantina (`QUARANTINED`) ke DLQ.
   - **Skenario 2 • HTTP 403 (`Izin Ditolak / Least Privilege`):** Buktikan token gate (`cidp-edge-gate-01`) ditolak saat mencoba menembak event keuangan `PAYMENT_SETTLED`.
   - **Skenario 3 • HTTP 422 (`Truk Tak Terdaftar`):** Buktikan plat ilegal `B 0000 XXX` ditolak tanpa membuat data hantu.
   - **Skenario 4 • `Cegah Kirim Ganda` (Idempotency):** Kirim ulang `event_id` yang sama 2×; tembakan kedua dikenali sebagai duplikat tanpa menagih ulang.
   - **Skenario 5 • `Uji Lonjakan (12 Req)` (Surge Backpressure):** Simulasikan lonjakan trafik 12 transaksi serentak; 6 diproses live dan 6 sisanya diamankan ke status **`Buffer BCP/Surge` (`BUFFERED`, 0 data hilang)**.
   - **Skenario 6 • `Toggle BCP Failover` & `Pulihkan & Flush Buffer`:** Tunjukkan alih mode kontingensi (*BCP Edge Buffer*, `RPO=0`, `RTO<10 detik`), lalu klik **`Pulihkan & Flush Buffer`** untuk memproses ulang seluruh antrean tertunda secara penuh.
2. **Tab `Koneksi Odoo & API` — OAuth 2.0 Bearer JWT & Odoo Cloud:**
   - Pilih salah satu `client_id` (misal `cidp-edge-gate-01`) dan klik **`Minta Token`** untuk memperlihatkan token JWT HS256 & cakupan *scopes*-nya serta test koneksi **Cloud ERP Odoo**.

---

### Tahap 5 — Dokumen Kontekstual A4 & Tour 3D Sandbox
- **Context-Aware `Dokumen A4` di Navbar:** Klik tombol **`Dokumen A4`** dari modul mana saja (mis. dari `Gate` langsung membuka `Surat Jalan / Gate Pass A4`, dari `Bea Cukai` membuka `SPPB A4`, dari `CFS` membuka `CFS Tally Sheet`, dari `Reefer` membuka `Sertifikat HACCP`, dari `Billing` membuka `Invoice A4`) lengkap dengan pemilih record (`Sebelumnya` / `Berikutnya`).
- **Afriansayah / Zulfikar (`page=beranda`):** Klik **`Ekspor`** → **`Executive Briefing (A4)`** (`report_executive.php`) untuk menyajikan rangkuman A4 gabungan **3 Aliran (Fisik, Informasi, Finansial)**.
- **Zulfikar (`page=simulator`):** Tutup sesi visualisasi dengan tur interaktif **Simulator 3D (`480m × 230m`, Blok Bay×Row 8×8)** yang berjalan 100% di *sandbox* browser dengan **RTG Crane + Reach Stacker** otomatis serta arus **truk keluar-masuk kontinu 2 arah** tanpa memotong yard dan tanpa mengotori data transaksi nyata di database.

---

## 4. Ringkasan Cepat Endpoint Dokumen Resmi A4 (`document.php` & `report_executive.php`)

Semua dokumen dirancang dalam format kertas siap cetak A4 ber-kop resmi (bukan *screenshot* layar):

| Jenis Dokumen | Parameter URL / Akses | Modul Asal Tombol Cetak | Penanda Tangan / Verifikator Resmi |
| :--- | :--- | :--- | :--- |
| **Official Invoice & Nota Billing** | `document.php?type=invoice&no=...&container=...` | Cockpit Beranda (`beranda.php`) & Billing ERP (`billing.php`) | **Afriansayah Ayubi, S.Log.** |
| **Gate Pass / EIR Surat Jalan** | `document.php?type=gate_pass&plate=...&container=...` | Smart Gate (`gate.php`) & Trucking (`trucking.php`) | **Armansyah Muchtarrom, S.T.** + Tanda Tangan Driver DB dinamis |
| **SPPB CEISA 4.0 DJBC** | `document.php?type=sppb&container=...` | Bea Cukai (`customs.php`) | **Hendra Kusuma, S.E.** (Pejabat Pabean) |
| **CFS Tally Sheet & Stripping** | `document.php?type=cfs_tally&job=...&container=...` | Container Freight Station (`cfs.php`) | **Juan Gamaliel, S.Log.** (Supervisor: Armansyah Muchtarrom) |
| **Sertifikat Reefer HACCP Cold Chain** | `document.php?type=reefer_cert&container=...` | Reefer Cold Chain (`reefer.php`) | **Naufal Andika Heditya, S.Log.** (QA & Cold Chain Compliance) |
| **Laporan Eksekutif 3-Aliran CIDP** | `report_executive.php` | Cockpit Beranda (`beranda.php`) | Hendra Kusuma, S.E., Afriansayah Ayubi, S.Log., Zulfikar J.F. |

---

## 5. Matriks Tindakan Operasional Lengkap Seluruh 17 Modul & Catatan Multi-Device LAN

Berikut daftar lengkap seluruh tindakan operasional (*POST form*, *AJAX CRUD*, dan *hardware trigger*) di tiap halaman menu aplikasi agar tidak ada modul yang terlewat saat sesi tanya-jawab:

| Kluster | Modul (`?page=...`) | Tindakan Operasional yang Tersedia & Terhubung DB |
| :--- | :--- | :--- |
| **Ringkasan & Telemetri** | `beranda` (`pages/beranda.php`) | • **TOC Live Asset Locator** (`api/track_asset.php?q=...`)<br>• Cetak faktur langsung (`document.php?type=invoice`) & **`Cetak Laporan PDF`** (`report_executive.php`)<br>• Ekspor data tabel ke Excel (`exportBerandaToExcel`) |
| | `twin` (`pages/digital_twin.php`) | • Monitor Live 3D/2D via SSE (`api/stream_twin.php`)<br>• Step trigger (`api/edge_simulator.php`) sinkron Real DB |
| | `integration` (`pages/integration.php`) | • **Tab 1 (`Antrean & Alur Gate`):** Daftar Pre-Advice (`register`), generator ISO 6346 (`generate_iso`), Ekspor/Impor JSON EDI-940, Jalankan 1 Truk / Step 1–6 / 10 tombol aktivitas lapangan<br>• **Tab 2 (`Antrean Error (DLQ)`):** Isolasi DLQ (`QUARANTINED`), Buffer Lonjakan & BCP (`BUFFERED`), Uji 401/403/422/Idempotency, **`Uji Lonjakan (12 Req)`**, **`Toggle BCP Failover`**, & **`Pulihkan & Flush Buffer`**<br>• **Tab 3 (`Koneksi Odoo & API`):** Token OAuth 2.0 Bearer JWT & Sinkronisasi Odoo Cloud (`account.move`) |
| **Peta & Simulasi Spasial** | `denah` (`pages/denah.php`) | • Inspeksi peta zonasi master 35 Ha berbasis koordinat tunggal `layout_master.php` |
| | `simulator` (`pages/simulator.php`) | • *Read-Only 3D Sandbox*: kamera sinematik & animasi simulasi tanpa memodifikasi MySQL |
| **TMS • Transportasi** | `gate` (`pages/gate.php`) | • `trigger_anpr_gate_in` (`Trigger ANPR Masuk & Buka Palang`)<br>• `trigger_weighbridge` (`Trigger Timbang & Terbitkan VGM`)<br>• `quick_gate_in`, `batch_gate_in`, `reset_demo_queue`<br>• `release_gate_out` (*Dual-Clearance Interlock*: wajib `SPPB_CLEARED` + `0` invoice belum lunas)<br>• Tambah truk manual (`api/crud.php?action=add_truck`) & cetak **Gate Pass A4** |
| | `trucking` (`pages/trucking.php`) | • Manajemen antrean armada & shunting internal, tambah armada (`api/crud.php?action=add_truck`), & cetak **Gate Pass A4** |
| | `intermodal` (`pages/intermodal.php`) | • `manual_load_wagon` (muat kontainer ke gerbong `KA 2518`/`KA 2519`)<br>• `manual_unload_wagon` (bongkar kontainer dari gerbong ke Yard)<br>• Manajemen jadwal kereta (`api/crud.php?action=add_train`) |
| **YMS • Lapangan Yard** | `yard` (`pages/yard.php`) | • `relocate_container` (relokasi slot Blok–Bay–Row–Tier + akrual fee + update Live Twin) |
| | `alat` (`pages/alat.php`) | • **Pemetaan 15 Unit MHE & Armada Terminal** (`4 Reach Stacker`, `5 E-RTG`, `4 Forklift CFS/Behandle`, `2 Shuttle Tractor`) sinkron DB & Simulator 3D<br>• `dispatch_job` (penugasan VMT + update koordinat GPS/RTK blok sasaran)<br>• `release_spreader` (lepas twistlock, kembalikan status alat ke `idle`) |
| | `kontainer` (`pages/kontainer.php`) | • CRUD Inventori Peti Kemas (`api/crud.php`: `add_container`, `update_container`, `gate_out_container`, `delete_container`) & filter status |
| | `reefer` (`pages/reefer.php`) | • `plug_in` (colok soket daya 380V + akrual)<br>• `un_plug` (cabut soket listrik)<br>• `trigger_lorawan_ping` (update suhu & deteksi deviasi > 2°C), `trigger_thermal_scan`, `trigger_ats_test`<br>• Cetak **Sertifikat Reefer HACCP A4** |
| **WMS • Gudang CFS** | `cfs` (`pages/cfs.php`) | • `create_job` (buat Work Order `STRIPPING`/`STUFFING`), `update_tally`, `complete_job` (`COMPLETED`)<br>• `add_pallet` (input palet SSCC-18), `trigger_dock_sensor`, `trigger_floor_scale`, `trigger_hht_scan`, `trigger_cfs_env`<br>• Cetak **CFS Tally Sheet A4** |
| | `scanner` (`pages/scanner.php`) | • Tab **`Kamera Langsung`**, **`Unggah Gambar / Dokumen`** (`handleFileScan`), & **`Uji Sampel Instan`** (`simulateSampleScan`: `GS1 SSCC-18`, `ISO 6346 Container`, `QR Smart E-Seal`, `e-Pass Truk Driver`) + ekspor CSV log scan |
| **Regulasi & Finansial** | `customs` (`pages/customs.php`) | • `hold_red_lane` (`Hold / Tahan ke Jalur Merah` → mengunci container `RED_LANE_CHECK` + CFS `HOLD` + akrual behandle Rp 350.000)<br>• `release_sppb` (`Rilis SPPB (Jalur Hijau)` → membuka hold & mengizinkan kelengkapan Gate-Out)<br>• Cetak **SPPB Resmi A4** |
| | `billing` (`pages/billing.php`) | • `pay_invoice` (pelunasan faktur ke `PAID` + pencatatan `BILLING_PAID`)<br>• Cetak **Official Invoice A4** (`document.php?type=invoice`) |
| **Sistem & Konfigurasi** | `settings` (`pages/settings.php`) | • `save_terminal_params`, `test_api_gateway`, `save_hardware_calibration`, `optimize_database`, `save_language` (ID/EN), `add_user` |

### Catatan Teknis Koneksi Multi-Device (Lebih dari 1 Laptop/HP)
1. **Akses LAN Tanpa Kendala Redirect:** Seluruh navigasi portal, `fetch('api/...')`, SSE `api/stream_twin.php`, dan tombol cetak `document.php` memakai **path relatif** (bukan `localhost`). Pada laptop lain di Wi-Fi/hotspot yang sama, cukup buka `http://<IP-Laptop-Server>/Conclusion_Intermodal_Dry_Port` (misal cek IP server dengan `ipconfig` di CMD/PowerShell) dan pastikan Apache Port `80` diizinkan pada *Windows Defender Firewall* (Private Network).
2. **Catatan Kamera Browser pada `page=scanner`:** Browser modern mewajibkan `localhost` atau HTTPS untuk mengaktifkan `getUserMedia` (tab **`Kamera Langsung`**). Karena itu, untuk demo `page=scanner` dari laptop klien lewat HTTP LAN (`http://<IP>...`), gunakan tab **`Unggah Gambar / Dokumen`** atau **`Uji Sampel Instan`** (atau buka `page=scanner` di laptop utama `localhost` jika ingin demo webcam langsung).
3. **Catatan Tab Odoo/MQTT (`page=settings`):** Menu uji konektivitas `http://localhost:8069` / `mqtt://localhost:1883` di `page=settings` ditujukan untuk endpoint lokal opsional jika container Odoo/Mosquitto terpasang terpisah; sedangkan sinkronisasi Cloud Odoo pada **Tab 4 `page=integration`** langsung berjalan ke endpoint Odoo yang dikonfigurasi.
