# 🖥️ BAHAN TAYANG PRESENTASI: TOPIK 7 — INLAND CONTAINER DEPOT (ICD) & DRY PORT MANAGEMENT
## Progres Minggu Ini: Perancangan Arsitektur Hardware, Otomasi Telemetri, & Integrasi Dua Arah YMS–ERP Odoo

* **Mata Kuliah:** Teknologi dan Perangkat Lunak Logistik
* **Dosen Pengampu:** Dr. Tigor Franky, S.T., M.T.
* **Institusi Akademik:** Institut Transportasi dan Logistik (ITL) Trisakti — Semester 5
* **Entitas Konsultan:** Conclusion Supply Chain Consultant
* **Topik Utama Silabus:** **Topik 7 — Inland Container Depot (ICD) & Dry Port Management**
* **Studi Kasus:** Prototype Sistem Manajemen Lapangan Penumpukan (*Yard Management System / YMS*) pada **Conclusion Intermodal Dry Port (CIDP) 35 Ha**
* **Fokus Progres Minggu Ini:** **Arsitektur Hardware, Telemetri IoT & Integrasi Sistem (YMS & ERP Odoo)**
* **Presenter:** Armansyah Muchtarrom (*Hardware & Infrastructure Specialist*)
* **Tanggal Penyusunan:** September 2026

---

# 📑 DAFTAR ISI SLIDE PRESENTASI

1. **Slide 1:** Cover: Topik 7 — ICD & Dry Port Management (Progres Minggu Ini: Hardware)
2. **Slide 2:** Lanskap Ekosistem Hardware: 6 Klaster Operasional Terminal 35 Ha
3. **Slide 3:** Strategi Klasifikasi: 11 Simulasi (Data-Driven) vs 15 Blueprint (Infrastruktur Fisik)
4. **Slide 4:** Klaster 1 — Otomasi Gerbang & Rantai Trigger (Waktu Transaksi 28 Detik)
5. **Slide 5:** Klaster 2 — Telemetri Alat Berat & Penumpukan 3D (*Auto Bay-Row-Tier*)
6. **Slide 6:** Klaster 3, 4, & 5 — Cold Chain Reefer, Siding KA, & Integritas Bea Cukai
7. **Slide 7:** Matriks Integrasi Data: Hardware ➔ YMS ➔ ERP Odoo
8. **Slide 8:** Bukti Uji Postman: Struktur Data JSON Riil
9. **Slide 9:** Rekapitulasi Rencana Anggaran Belanja Modal (CAPEX Rp 18,94 Miliar)
10. **Slide 10:** Kesimpulan: Efisiensi Operasional & Akuntabilitas Finansial (*Zero Leakage*)
11. **Slide 11 (Lampiran Visual 1):** Foto Resmi Perangkat Simulasi (Edge PC #18, DGPS RTK #21, Twistlock #22)
12. **Slide 12 (Lampiran Visual 2):** Foto Resmi Perangkat Simulasi (Smart Reefer #23, Axle Counter #25, E-Seal #26)
13. **Slide 13 (Lampiran Visual 3):** Foto Resmi Perangkat Blueprint (Rugged PDA #24 & Ringkasan 14 Lainnya)
14. **Slide 14 (Lampiran Master BOM 1):** Tabel Lengkap 26 Hardware & Spesifikasi (Item #1 s/d #13)
15. **Slide 15 (Lampiran Master BOM 2):** Tabel Lengkap 26 Hardware & Spesifikasi (Item #14 s/d #26)
16. **LAMPIRAN LENGKAP:** Rincian Spesifikasi & Fungsi 26 Hardware (Item #1 s/d #26)

---

```
========================================================================================
[SLIDE 1] COVER: TOPIK 7 — ICD & DRY PORT MANAGEMENT (PROGRES: HARDWARE)
========================================================================================
```

### 1. Visual Slide
* **Kategori / Banner:** TOPIK 7: INLAND CONTAINER DEPOT (ICD) & DRY PORT MANAGEMENT
* **Judul Utama Slide:** Sistem Manajemen Lapangan Penumpukan (Yard Management System / YMS) pada Conclusion Intermodal Dry Port (CIDP) 35 Ha
* **Fokus Progres Minggu Ini:** PERANCANGAN ARSITEKTUR HARDWARE, TELEMETRI IOT & OTOMASI GERBANG
* **Tim Konsultan & Pembagian Tugas (Conclusion Supply Chain Consultant):**
  1. **Zulfikar Jafarudin Fatah** (24D507001001) — *Lead System Architect (Ketua Tim)*  
     *Tugas:* Arsitektur Sistem, Basis Data, Dashboard Eksekutif, & Modul Simulator 3D.
  2. **Armansyah Muchtarrom** (24D507001010) — *Hardware & Infrastructure Specialist (Presenter)*  
     *Tugas:* Analisis Infrastruktur Fisik 35 Ha, Spesifikasi 26 Hardware, Telemetri IoT & Otomasi Gate.
  3. **Afriansayah Ayubi** (24D507001026) — *Software & ERP Process Specialist*  
     *Tugas:* Alur Proses Bisnis Kontainer, Struktur Tarif Jasa Terminal, & Integrasi ERP Odoo.
  4. **Juan Gamaliel** (24D507001016) — *Data Integration Specialist*  
     *Tugas:* Integrasi Aliran Data Antarsistem, Stacking Rules, & Denah Tata Letak Fisik 35 Ha.
  5. **Naufal Andika Heditya** (24D507001012) — *Business Analyst & QA Specialist*  
     *Tugas:* Analisis Kebutuhan Proses Bisnis, Standardisasi GS1/SSCC, & Kepatuhan Bea Cukai.
* **Informasi Akademik:**
  * Mata Kuliah: Teknologi dan Perangkat Lunak Logistik | Dosen Pengampu: Dr. Tigor Franky, S.T., M.T.
  * Program Studi Manajemen Logistik | Institut Transportasi dan Logistik (ITL) Trisakti (2026)

### 2. Skrip Pembicara (Speaker Notes)
> *"Selamat pagi Bapak Dr. Tigor Franky dan rekan-rekan sekalian.  
> Kami dari kelompok **Conclusion Supply Chain Consultant**, beranggotakan:  
> 1. Zulfikar Jafarudin Fatah (24D507001001) selaku Lead System Architect,  
> 2. Saya sendiri, Armansyah Muchtarrom (24D507001010) selaku Hardware & Infrastructure Specialist,  
> 3. Afriansayah Ayubi (24D507001026) selaku Software & ERP Specialist,  
> 4. Juan Gamaliel (24D507001016) selaku Data Integration Specialist, dan  
> 5. Naufal Andika Heditya (24D507001012) selaku Business Analyst & QA Specialist.  
>
> Sesuai penugasan silabus kita pada **Topik 7: Inland Container Depot (ICD) & Dry Port Management**, judul proyek perancangan sistem kami adalah **Sistem Manajemen Lapangan Penumpukan (Yard Management System / YMS) pada Conclusion Intermodal Dry Port (CIDP) 35 Ha**.  
> Dan pada pertemuan minggu ini, fokus progres yang kami presentasikan adalah **perancangan arsitektur perangkat keras (*hardware architecture*), telemetri IoT lapangan, dan otomasi gerbang masuk/keluar**."*


---

```
========================================================================================
[SLIDE 2] LANSKAP EKOSISTEM HARDWARE: 6 KLASTER OPERASIONAL TERMINAL 35 HA
========================================================================================
```

### 1. Visual Slide
* **Judul Utama Slide:** Lanskap Ekosistem Hardware: 6 Klaster Operasional Terminal 35 Ha
* **Sub-Judul:** Pemetaan Kebutuhan Fisik Lapangan Berdasarkan Karakteristik Alur Kargo Intermodal
* **3 Kartu Pilar Utama:**
  1. **Tantangan Fisik Lapangan (Standar Maritim & Heavy-Duty):**
     * Luas area 35 Ha dengan manuver truk trailer 40ft & Reach Stacker 45 ton.
     * Operasi 24/7 non-stop di bawah terik matahari, debu semen/kontainer, & kelembapan tinggi.
     * Standar Proteksi: IP67/IP68 tahan cuaca, Fanless PC, getaran MIL-STD-810G, dan safety rel SIL 4.
  2. **Sebaran 26 Hardware (6 Klaster Kritis Lapangan):**
     * *Otomasi Gate (8 Item):* ANPR, OCR Portal, Jembatan Timbang 80t, RFID UHF, Barrier Gate, LED Display, Edge AI PC.
     * *Yard & Crane (4 Item):* DGPS RTK (<2 cm), Spreader Twistlock & Load Cell, VMT Kabin, Rugged PDA.
     * *Cold Chain (2 Item):* Smart Reefer Socket 380V & Thermal Camera.
     * *Rail Siding (3 Item):* Axle Counter KA, Access Point WiFi 7, GPS Tracker Truk.
     * *Bea Cukai (5 Item):* Gantry X-Ray 6 MeV, Smart E-Seal, CCTV 4MP/8MP/PTZ.
     * *Core IT (4 Item):* Server NVR & DB, Switch PoE+ IP40, Online UPS 5000VA, Scanner Meja.
  3. **Strategi Rekayasa Konsultan (Jembatan Menuju Slide 3):**
     * *Pemisahan Peran Sistemik:* Tidak semua hardware bekerja sama; ada instrumen cerdas pengirim data aktif, dan ada infrastruktur utilitas fisik pendukung.
     * *Efisiensi CAPEX Rp 18,94 M:* Pengadaan dirancang berbasis prioritas otomasi & kepatuhan regulasi.
     * *Kesiapan Integrasi:* Menjadi dasar klasifikasi mana yang disimulasikan alur datanya vs mana yang menjadi blueprint fasilitas fisik (Slide 3).

### 2. Skrip Pembicara (Speaker Notes)
> *"Bapak Dosen dan rekan-rekan sekalian, sebelum kita masuk ke rincian teknis, kita perlu memahami terlebih dahulu lanskap tantangan fisik di lapangan pelabuhan kering seluas 35 Hektar.  
> Mengoperasikan terminal peti kemas dengan skala seperti Cikarang Dry Port membutuhkan perangkat keras dengan standar ketahanan maritim dan *heavy-duty* (IP67/IP68 serta sertifikasi guncangan MIL-STD-810G), karena peralatan beroperasi 24 jam nonstop di tengah debu tebal kontainer dan cuaca ekstrem.  
> Untuk memenuhi kebutuhan tersebut, kami merancang **26 jenis hardware yang terdistribusi ke dalam 6 klaster operasional**: mulai dari otomasi gerbang, alat angkat penumpukan, rantai dingin reefer, sepur simpan rel kereta api, area pemeriksaan Bea Cukai, hingga pusat data.  
> Namun, secara rekayasa sistem, ke-26 hardware ini memiliki karakteristik yang berbeda: ada yang menjadi **instrumen aktif penghasil data transaksi telemetri**, dan ada yang menjadi **infrastruktur fisik pendukung**.  
> Inilah yang mendasari strategi pembagian kami pada slide berikutnya: membedakan antara perangkat yang kita simulasikan aliran datanya dengan perangkat blueprint fasilitas fisik."*

---

```
========================================================================================
[SLIDE 3] STRATEGI KLASIFIKASI: 11 SIMULASI VS 15 BLUEPRINT
========================================================================================
```

### 1. Visual Slide
* **Judul:** Klasifikasi 26 Hardware Berdasarkan Peran Fungsional
* **Kolom Kiri — 11 Perangkat SIMULASI (Live Software & Postman Tested):**
  * Perangkat aktif dengan aliran data (*data payload*) harian.
  * Berfungsi memicu mutasi status database YMS dan mencetak tagihan di ERP Odoo.
  * *Contoh:* Kamera ANPR, OCR Portal, Jembatan Timbang 80t, RFID Reader, Edge PC, DGPS RTK, Spreader Twistlock, Smart Reefer Socket, Axle Counter KA, dan E-Seal Bea Cukai.
* **Kolom Kanan — 15 Perangkat BLUEPRINT (Infrastruktur & Utilitas Fisik):**
  * Tulang punggung (*backbone*) kelistrikan, pengawasan pasif, jaringan, dan keselamatan.
  * Masuk dalam Bill of Materials (BOM) dan perhitungan CAPEX 35 Ha agar memenuhi standar ISPS Code dan regulasi pabean internasional.
  * *Contoh:* Tiang CCTV 4MP/8MP/PTZ, High-Speed Barrier Gate, Industrial Switch PoE+, Access Point WiFi 7, Server NVR, Online UPS 5000VA, Scanner Barcode Meja, Thermal Camera, GPS Tracker Truk, Gantry X-Ray 6 MeV, VMT Kabin, dan Rugged PDA.

### 2. Skrip Pembicara (Speaker Notes)
> *"Dari total 26 item hardware yang kami rancang, kami membaginya secara saintifik menjadi dua kelompok:  
> **11 Perangkat masuk kategori SIMULASI**, karena perangkat-perangkat inilah yang menghasilkan data dinamis yang kita uji menggunakan Postman dan terhubung ke web YMS serta Odoo.  
> Sedangkan **15 Perangkat lainnya masuk kategori BLUEPRINT FISIK**, seperti jaringan switch industri, UPS cadangan, kamera CCTV pasif, dan fasilitas pemindai Gantry X-Ray. Kelompok blueprint ini wajib ada dalam kajian konsultan untuk memastikan fasilitas 35 Ha ini layak secara teknis (*technically viable*) dan memenuhi regulasi pelabuhan internasional."*

---

```
========================================================================================
[SLIDE 4] KLASTER 1: OTOMASI GERBANG & MEKANISME TRIGGER
========================================================================================
```

### 1. Visual Slide
* **Judul:** Otomasi Gate Masuk: Rantai Trigger Fisik Menuju Transaksi 28 Detik
* **Diagram Rantai Trigger:**
  $$\text{Loop Detector} \;\xrightarrow{\text{Pulsa}}\; \text{ANPR & OCR} \;\xrightarrow{\text{Data}}\; \text{Timbangan 80t} \;\xrightarrow{\text{RS-232}}\; \text{Edge AI PC} \;\xrightarrow{\text{Relay}}\; \text{Palang & LED}$$
* **Rincian Trigger per Alat:**
  1. *Kamera ANPR (#1 - Simulasi):* Truk menekan *induction loop* $\rightarrow$ blitz menyala $\rightarrow$ AI mengekstrak plat `B 9812 UIK` via TCP/IP $\rightarrow$ validasi armada di **Odoo Fleet**.
  2. *Kamera OCR (#2 - Simulasi):* Kontainer memotong sensor photocell $\rightarrow$ portal memindai `TCKU 829104-2` (40HC) $\rightarrow$ penentuan tarif dasar di **Odoo Sales**.
  3. *Weighbridge 80t (#6 - Simulasi):* Beban menekan load cell $\rightarrow$ indikator digital menyaring getaran $\rightarrow$ kirim bobot kotor 32.450 kg via serial RS-232 $\rightarrow$ YMS memvalidasi batas aman SOLAS VGM (<34t) $\rightarrow$ **Odoo Invoicing** mencatat tagihan timbang **Rp 50.000**.
  4. *RFID Reader 20m (#7 - Simulasi):* Gelombang RF 860–960 MHz menyerap tag di kaca truk $\rightarrow$ identifikasi instan ID supir.
  5. *Edge AI PC (#18 - Simulasi):* Mengorelasikan input $\rightarrow$ mengirim sinyal relay buka palang barrier gate (#9) dan menampilkan teks di LED display (#10): `"SILAHKAN MASUK - BLOK B-04"`.

### 2. Skrip Pembicara (Speaker Notes)
> *"Mari kita bedah klaster gerbang masuk. Waktu transaksi kami pangkas menjadi rata-rata **28 detik** per truk.  
> Pemicunya dimulai saat roda truk menekan induction loop di aspal. Sensor loop mengirim pulsa elektrik ke kamera ANPR dan OCR portal. Bersamaan dengan itu, truk berhenti di atas jembatan timbang 80 ton, di mana 8 strain gauge load cell membaca bobot kotor dan mengirim data via kabel serial RS-232 ke Industrial Edge AI PC Advantech.  
> Edge PC memvalidasi kepatuhan SOLAS VGM secara lokal. Jika aman, Edge PC memicu relay untuk membuka palang dan mengirim data ke YMS serta ERP Odoo untuk menerbitkan tagihan jasa timbang Rp 50.000 secara otomatis."*

---

```
========================================================================================
[SLIDE 5] KLASTER 2: TELEMETRI ALAT BERAT & PENUMPUKAN 3D
========================================================================================
```

### 1. Visual Slide
* **Judul:** Telemetri Lapangan Penumpukan: Presisi Sentimeter Menuju *Auto-Stacking*
* **Rincian Trigger & Alur Data:**
  * **Spreader Twistlock Sensor (#22 - Simulasi):**
    * *Trigger Fisik:* Pin kerucut hidrolik berputar 90° di dalam corner casting kontainer.
    * *Sensor:* Sensor induktif membaca logam pin $\rightarrow$ status sirkuit beralih dari `0 (UNLOCKED)` ke `1 (LOCKED)`.
    * *Aksi Finansial:* Transisi ke `UNLOCKED` (kontainer dilepas di yard) memicu pencatatan **Jasa Lift-Off Rp 250.000** di ERP Odoo.
  * **Load Cell Spreader (#22 - Simulasi):**
    * *Trigger Fisik:* Boom mengangkat kontainer $\rightarrow$ mengukur tonase angkat riil untuk audit keselamatan crane.
  * **DGPS / RTK GNSS Receiver (#21 - Simulasi):**
    * *Trigger Spasial:* Koreksi diferensial RTK base station (< 2 cm) + sensor IMU kemiringan boom.
    * *Pemicu Drop:* Detik persis saat twistlock terbuka, koordinat $(X, Y, Z)$ dikunci.
    * *YMS Update:* YMS mengonversi koordinat satelit menjadi format terminal: **Block B, Bay 04, Row 02, Tier 03**.
    * *Odoo Maintenance:* Jam operasi alat berat dicatat untuk jadwal servis berkala.

### 2. Skrip Pembicara (Speaker Notes)
> *"Kunci evaluasi utama dosen pada proyek ini adalah: 'Bagaimana membuktikan sistem tahu posisi kontainer berpindah saat dipindahkan oleh Reach Stacker?'  
> Kuncinya ada pada duet sensor twistlock Bromma dan DGPS RTK CHCNAV. Saat operator mengunci kontainer, sensor twistlock berstatus `LOCKED`. Setelah dipindahkan ke Blok B dan diletakkan di tumpukan, pin diputar membuka menjadi `UNLOCKED`.  
> Perubahan status `UNLOCKED` inilah yang menjadi **pemicu (*trigger*)** bagi DGPS RTK berakurasi di bawah 2 sentimeter untuk merekam koordinat saat itu juga. YMS langsung meng-update visual 3D posisi Bay-Row-Tier, dan seketika menembakkan tagihan jasa Lift-Off Rp 250.000 ke modul Odoo Invoicing."*

---

```
========================================================================================
[SLIDE 6] KLASTER 3, 4, & 5: COLD CHAIN, REL KA, & KEPABEANAN
========================================================================================
```

### 1. Visual Slide
* **Judul:** Monitoring Rantai Dingin, Siding Kereta Api, & Integritas Bea Cukai
* **Poin Penjelasan:**
  * **Smart Reefer Socket (#23 - Simulasi):**
    * *Trigger:* Steker 380V dicolokkan $\rightarrow$ trafo arus (CT) mendeteksi arus start kompresor (> 5 Ampere).
    * *Logika Proteksi:* Jika arus mendadak 0 Ampere (kompresor mati), alarm trip menyala dalam 3 detik.
    * *Odoo:* Setiap pergantian shift (8 jam), otomatis menerbitkan tagihan **Jasa Steker Reefer Rp 250.000 / shift**.
  * **Rail Trackside Axle Counter (#25 - Simulasi):**
    * *Trigger:* Pelek baja roda KA memotong medan induktif sensor di leher rel sepur simpan.
    * *Logika:* Menghitung jumlah as roda (120 as roda = 30 gerbong datar = 60 TEUs).
    * *Odoo:* Rekonsiliasi Surat Angkutan KA (SAKA) untuk pembayaran sewa jalur rel (*Track Access Charge / TAC*) ke PT KAI.
  * **Smart E-Seal GPS (#26 - Simulasi):**
    * *Trigger:* Kawat baja segel dikunci di Priok $\rightarrow$ GPS memancarkan status `SEAL_ARMED`.
    * *Tamper Alert:* Jika kawat digunting sebelum tiba di CIDP, memicu alarm sabotase ke YMS & Bea Cukai.
    * *Odoo:* Menjadi prasyarat mutlak rilis *Delivery Order (DO)* di **Odoo Inventory** (wajib status SPPB Cleared).

### 2. Skrip Pembicara (Speaker Notes)
> *"Di area rantai dingin (Cold Chain), kami memonitor 300 titik reefer menggunakan Smart Power Socket Marechal. Pemicunya adalah deteksi arus induktif saat steker dicolokkan, yang secara otomatis menghitung tarif sewa listrik per shift di Odoo.  
> Di area rel sepur simpan, kami memasang Rail Axle Counter Frauscher pada leher rel. Pelek roda kereta yang melintas memicu pulsa induktif yang menghitung jumlah gerbong secara presisi untuk rekonsiliasi biaya sewa rel ke PT KAI.  
> Sedangkan untuk keamanan transit dari Pelabuhan Tanjung Priok, peti kemas disegel menggunakan Smart E-Seal ber-GPS. Jika segel dirusak di perjalanan, sistem YMS akan membunyikan alarm dan sistem ERP Odoo akan memblokir penerbitan dokumen pengeluaran barang."*

---

```
========================================================================================
[SLIDE 7] MATRIKS INTEGRASI DATA: HARDWARE ➔ YMS ➔ ERP ODOO
========================================================================================
```

### 1. Visual Slide
* **Judul:** Matriks Pemetaan Integrasi Sistem Operasional & Finansial

| Hardware | Kategori | Protokol Lapangan | Aksi di Software YMS | Modul di ERP Odoo | Dampak Finansial / Bisnis |
|:---|:---:|:---|:---|:---|:---|
| **ANPR (#1)** | Simulasi | TCP/IP HTTP Post | Validasi nomor polisi di database | Odoo Fleet | Rekonsiliasi armada vendor |
| **OCR ISO (#2)** | Simulasi | Ethernet Socket | Identifikasi nomor box & blok tujuan | Odoo Sales | Penentuan kelas tarif (20/40ft) |
| **Timbangan 80t (#6)** | Simulasi | Serial RS-232 / Modbus | Validasi ambang batas SOLAS VGM | Odoo Invoicing | Tagihan Timbang Rp 50.000 |
| **RFID Reader (#7)** | Simulasi | Wiegand-34 / TCP | Identifikasi otomatis e-Pass supir | Odoo Accounting | Potong deposit prabayar supir |
| **Edge AI PC (#18)** | Simulasi | REST API JSON | Otomasi keputusan gardu gerbang | Odoo API Gateway | Pengirim payload transaksi |
| **DGPS RTK (#21)** | Simulasi | NMEA-0183 RS-232 | Auto-update slot 3D Bay-Row-Tier | Odoo Maintenance | Catat jam operasi alat berat |
| **Twistlock (#22)** | Simulasi | CANopen / Proximity | Konfirmasi fisik peti kemas dilepas | Odoo Invoicing | Tagihan Lift-Off Rp 250.000 |
| **Smart Reefer (#23)**| Simulasi | LoRaWAN / Modbus RTU | Monitoring suhu & alarm trip daya | Odoo Invoicing | Tagihan Steker Rp 250.000/shift |
| **Axle Counter (#25)**| Simulasi | Pulsa Digital / Modbus | Hitung gerbong & okupansi jalur rel | Odoo Purchase | Validasi sewa rel (TAC KAI) |
| **Smart E-Seal (#26)**| Simulasi | GSM 4G / MQTT | Pengawasan rute pabean transit | Odoo Inventory | Syarat izin rilis kargo (SPPB) |
| **15 Item Lainnya** | Blueprint | FO, NVR, UPS, CCTV | Keamanan fisik & utilitas 35 Ha | Odoo Asset | Manajemen aset & depresiasi |

### 2. Skrip Pembicara (Speaker Notes)
> *"Tabel matriks ini memperlihatkan batas tanggung jawab yang sangat rapi.  
> Sistem YMS kita fokus pada eksekusi lapangan (*shop floor*): memandu pergerakan truk, mengatur susunan tumpukan kontainer, dan menjaga keselamatan kerja.  
> Sementara ERP Odoo fokus pada korporasi: membukukan pendapatan, menerbitkan faktur pajak, dan memonitor aset.  
> Hardware di lapangan menjadi jembatan penghubung fisik yang memastikan setiap aktivitas di lapangan langsung tercatat nilai rupiahnya di Odoo."*

---

```
========================================================================================
[SLIDE 8] BUKTI UJI POSTMAN: STRUKTUR DATA JSON RIIL
========================================================================================
```

### 1. Visual Slide
* **Judul:** Validasi Integrasi Software Menggunakan Postman JSON Payloads

#### Payload 1 — Gate-In (ANPR + OCR + Timbangan):
```json
POST /api/simulator_action.php?action=gate_in
{
  "device_source": "ADVANTECH_EDGE_PC_GATE_01",
  "license_plate": "B 9812 UIK",
  "container_number": "TCKU8291042",
  "container_size": "40HC",
  "measured_gross_kg": 32450,
  "vgm_status": "SOLAS_VERIFIED_PASS"
}
```

#### Payload 2 — Webhook YMS ke ERP Odoo Invoicing:
```json
POST https://erp.cidp.ac.id/api/v1/billing/charge-event
{
  "customer_id": "CUST-SAMUDERA-01",
  "container_number": "TCKU8291042",
  "charges": [
    { "service": "Jembatan Timbang VGM 80t", "amount": 50000 },
    { "service": "Jasa Lift-Off Yard 40ft", "amount": 250000 }
  ],
  "total_billable": 300000
}
```

### 2. Skrip Pembicara (Speaker Notes)
> *"Berikut adalah bukti konkret struktur data JSON yang kami gunakan saat pengujian di Postman.  
> Ketika truk masuk di gerbang, Edge PC menembakkan payload pertama ke endpoint `action=gate_in`. YMS memvalidasi data dan seketika menembakkan webhook kedua ke ERP Odoo.  
> Dalam hitungan milidetik, modul Odoo Invoicing langsung membuat draf faktur sebesar Rp 300.000 atas nama PT Samudera Logistik Prima. Ini membuktikan bahwa integrasi software kita bukan sekadar konsep, melainkan sudah berjalan secara fungsional melalui API."*

---

```
========================================================================================
[SLIDE 9] REKAPITULASI BIAYA INVESTASI (CAPEX 35 HA)
========================================================================================
```

### 1. Visual Slide
* **Judul:** Rencana Anggaran Belanja Modal (CAPEX) Hardware Terminal 35 Ha

| No | Klaster Operasional | Jumlah Jenis | Total Unit | Subtotal Estimasi CAPEX | Persentase |
|:---:|:---|:---:|:---:|:---|:---:|
| 1 | Otomasi Gate & Kontrol Akses | 8 Jenis | 522 Unit / Pack | Rp 1.460.500.000 | 7,7% |
| 2 | Telemetri Yard & Alat Angkat | 4 Jenis | 28 Unit | Rp 1.084.000.000 | 5,7% |
| 3 | Cold Chain & Monitoring Reefer | 2 Jenis | 304 Unit / Titik | Rp 2.670.000.000 | 14,1% |
| 4 | Infrastruktur Intermodal Rel KA | 3 Jenis | 62 Unit | Rp 569.600.000 | 3,0% |
| 5 | Keamanan & Bea Cukai (X-Ray) | 5 Jenis | 157 Unit / Sistem| Rp 13.118.400.000 | 69,3% |
| 6 | Core Datacenter & Jaringan | 4 Jenis | 26 Unit | Rp 660.800.000 | 3,5% |
| **TOTAL** | **Seluruh Fasilitas CIDP 35 Ha** | **26 Item** | **1.099 Unit** | **Rp 18.943.300.000** | **100,0%** |

*Catatan Khusus:* Klaster Keamanan mendominasi 69,3% karena pengadaan **Gantry Container X-Ray Scanner (Rp 12,5 Miliar)** untuk jalur merah pabean.

### 2. Skrip Pembicara (Speaker Notes)
> *"Dari sisi analisis finansial konsultan, total investasi belanja modal (CAPEX) untuk seluruh 26 perangkat keras di lahan 35 Hektar adalah sebesar **Rp 18,94 Miliar**.  
> Sebanyak 69,3% anggaran dialokasikan untuk sistem keamanan dan kepabeanan, terutama pengadaan Gantry Container X-Ray Scanner senilai Rp 12,5 Miliar yang merupakan prasyarat wajib pelabuhan kering internasional.  
> Rincian CAPEX ini telah kami sinkronkan ke dokumen studi kelayakan (*feasibility study*) untuk menghitung Payback Period dan kelayakan investasi proyek."*

---

```
========================================================================================
[SLIDE 10] KESIMPULAN
========================================================================================
```

### 1. Visual Slide
* **Judul:** Kesimpulan: Efisiensi Operasional & Akuntabilitas Finansial
* **Poin Kunci:**
  1. **Akurasi 100%:** DGPS RTK (< 2 cm) dan sensor twistlock meniadakan kesalahan penempatan kontainer (*misplaced box*).
  2. **Kecepatan Gerbang:** Waktu transaksi gerbang terpangkas menjadi 28 detik per truk.
  3. **Zero Revenue Leakage:** Seluruh aktivitas fisik di lapangan otomatis terbit fakturnya di ERP Odoo melalui pemicu JSON API.
  4. **Kesiapan Sistem:** 11 perangkat simulasi telah teruji via Postman dan siap dihubungkan ke alat fisik di lapangan.

### 2. Skrip Pembicara (Speaker Notes)
> *"Sebagai penutup, arsitektur hardware yang kami rancang membuktikan bahwa Conclusion Intermodal Dry Port siap menjadi pelabuhan kering cerdas kelas dunia.  
> Kami berhasil membuktikan bahwa integrasi antara sensor lapangan, software YMS, dan ERP Odoo mampu menghapuskan waktu tunggu antrean gerbang dan menjamin nol kebocoran pendapatan.  
> Sekian presentasi dari saya, terima kasih atas perhatian Bapak Dr. Tigor Franky dan rekan-rekan sekalian. Saya persilakan jika ada pertanyaan atau tanggapan."*

---
---

# 📎 LAMPIRAN LENGKAP: KATALOG VISUAL 26 PERANGKAT KERAS
*Bagian lampiran ini memuat seluruh 26 hardware secara lengkap dari nomor 1 sampai 26 beserta model, spesifikasi, dan berkas gambar resmi dari folder `hardware/images/`.*

```
====================================================================================================
                        DAFTAR LENGKAP 26 HARDWARE (DENGAN NAMA, SPESIFIKASI & GAMBAR)
====================================================================================================
```

### [ITEM #1] Kamera ANPR Gate
* **Klasifikasi:** **SIMULASI (Ditembak via JSON / Postman)**
* **Model:** Hikvision DS-TCG406-E Series
* **Spesifikasi:** 4 MP, Deep Learning OCR on-board, motorized lens, shutter 1/100.000s, IP67 weatherproof.
* **Fungsi:** Membaca plat nomor truk secara otomatis saat roda melindas induction loop di gerbang.

---

### [ITEM #2] Kamera OCR Portal Kontainer ISO 6346
* **Klasifikasi:** **SIMULASI (Ditembak via JSON / Postman)**
* **Model:** Hikvision iDS-TCV300-A6I Multi-Angle Portal
* **Spesifikasi:** Multi-angle capture, akurasi > 98% ISO 6346 (4 huruf + 7 angka), deteksi ukuran box 20/40ft.
* **Fungsi:** Mengidentifikasi nomor peti kemas dan ukuran box saat truk melintas perlahan di portal masuk.

---

### [ITEM #3] CCTV Yard 4 MP
* **Klasifikasi:** *BLUEPRINT FISIK (Fasilitas Lapangan)*
* **Model:** Hikvision DS-2CD2046G2H-IU
* **Spesifikasi:** 4 MP, IP67 Weatherproof, IR Night Vision 40m, PoE, Casing Logam.
* **Fungsi:** Pengawasan visual umum area tumpukan kontainer Blok A, B, C, D (32 Titik).

---

### [ITEM #4] CCTV Yard 8 MP (4K UHD)
* **Klasifikasi:** *BLUEPRINT FISIK (Fasilitas Lapangan)*
* **Model:** Hikvision DS-2CD2T87G2H-LI ColorVu
* **Spesifikasi:** 8 MP / 4K UHD, ColorVu 24/7 Full Color, Smart Hybrid Light, IP67.
* **Fungsi:** Pengawasan batas pagar terluar terminal (*perimeter boundary*) pada malam hari (16 Titik).

---

### [ITEM #5] CCTV PTZ 360° TandemVu
* **Klasifikasi:** *BLUEPRINT FISIK (Fasilitas Lapangan)*
* **Model:** Hikvision DS-2SE4C425MWG-E/14
* **Spesifikasi:** 25x Optical Zoom, Dual-Channel Panoramic + PTZ, IR 100m, Auto Tracking.
* **Fungsi:** Pemantauan fleksibel jarak jauh di menara pengawas pusat dan area rel KA (8 Titik).

---

### [ITEM #6] Weighbridge / Jembatan Timbang Truk 80 Ton
* **Klasifikasi:** **SIMULASI (Ditembak via JSON / Postman)**
* **Model:** Fangda Electronic Truck Scale 80 Ton + Digital Indicator Keda
* **Spesifikasi:** Platform baja 18 x 3 meter, kapasitas 80 Ton, 8 strain gauge load cell, output serial RS-232 / Modbus, sertifikasi SOLAS VGM.
* **Fungsi:** Menimbang bobot kotor truk kontainer untuk validasi keselamatan muatan SOLAS VGM di gerbang masuk.

---

### [ITEM #7] Long-Range UHF RFID Reader (20 Meter)
* **Klasifikasi:** **SIMULASI (Ditembak via JSON / Postman)**
* **Model:** Long-Range UHF RFID Reader (860–960 MHz)
* **Spesifikasi:** Frekuensi 860–960 MHz, jarak baca hingga 20 meter, polarisasi sirkular, protokol Wiegand / TCP/IP, IP67.
* **Fungsi:** Mengidentifikasi kartu e-Pass truk mitra tanpa mengharuskan pengemudi berhenti membuka kaca.

---

### [ITEM #8] RFID Tag UHF ISO (Pasif Anti-Metal)
* **Klasifikasi:** *BLUEPRINT FISIK (Consumable Tag)*
* **Model:** RFID UHF ID Tag ISO 18000-6C (TRANSIT & ATEX)
* **Spesifikasi:** Tag pasif anti-metal, memori EPC 128 bit, perekat 3M heavy duty tahan panas/hujan.
* **Fungsi:** Ditempel di kaca depan 500 truk armada mitra sebagai identitas kendaraan resmi.

---

### [ITEM #9] Automatic Barrier Gate
* **Klasifikasi:** *BLUEPRINT FISIK (Aktuator Gerbang)*
* **Model:** High-Speed DC Brushless Barrier Gate
* **Spesifikasi:** Palang 3 meter, motor DC Brushless, kecepatan buka 1–1.5 detik, siklus 5 juta kali operasi.
* **Fungsi:** Palang fisik penghalang truk di 4 lane gerbang, membuka hanya saat dipicu relay Edge PC.

---

### [ITEM #10] Outdoor Industrial LED Information Display
* **Klasifikasi:** **SIMULASI (Ditembak via JSON / Postman)**
* **Model:** Chipshow Outdoor P6/P8 Industrial LED Display
* **Spesifikasi:** Kecerahan > 6.500 nits, koneksi RS-485 / Modbus, tahan cuaca panas IP65.
* **Fungsi:** Menampilkan instruksi visual kepada pengemudi: nomor gate, status bobot, dan blok yard tujuan.

---

### [ITEM #11] Industrial Network Switch PoE+
* **Klasifikasi:** *BLUEPRINT FISIK (Infrastruktur Jaringan)*
* **Model:** Managed 24-Port Gigabit PoE+ Industrial Switch IP40
* **Spesifikasi:** 24-Port GbE PoE+, 4 SFP Fiber Slot, Casing logam IP40, suhu -40°C s/d 75°C, dual power input.
* **Fungsi:** Menghubungkan seluruh jaringan kabel data CCTV, Access Point, dan PC gardu di 12 junction box.

---

### [ITEM #12] Outdoor Access Point WiFi 7
* **Klasifikasi:** *BLUEPRINT FISIK (Infrastruktur Jaringan)*
* **Model:** Ubiquiti U7 Pro Outdoor / UniFi WiFi 7 AP
* **Spesifikasi:** Tri-Band WiFi 7, casing IP67 weatherproof, jangkauan luas antena directional.
* **Fungsi:** Memancarkan sinyal nirkabel industri untuk konektivitas VMT kabin crane dan PDA di lapangan 35 Ha.

---

### [ITEM #13] Server NVR & Database Core
* **Klasifikasi:** *BLUEPRINT FISIK (Datacenter)*
* **Model:** Hikvision DS-9664NI-M16/R 64-Channel 4K NVR
* **Spesifikasi:** 64-Channel IP Camera, 16 SATA HDD Bay (kapasitas hingga 256 TB), redundant PSU, RAID 5.
* **Fungsi:** Menyimpan rekaman video seluruh kamera terminal selama 90 hari sesuai standar keamanan ISPS Code.

---

### [ITEM #14] Online UPS Rackmount 5000VA
* **Klasifikasi:** *BLUEPRINT FISIK (Proteksi Daya)*
* **Model:** APC Smart-UPS SRT 5000VA RM 230V (SRT5KRMXLI)
* **Spesifikasi:** On-Line Double Conversion 5kVA/4.5kW, rackmount 3U, baterai hot-swappable.
* **Fungsi:** Menjaga pasokan daya listrik darurat agar server datacenter dan sistem gerbang tidak mati saat listrik PLN padam.

---

### [ITEM #15] Barcode / QR Scanner Meja
* **Klasifikasi:** *BLUEPRINT FISIK (Perangkat Loket)*
* **Model:** Zebra Symbol LS2208 Handheld Scanner
* **Spesifikasi:** Laser 1D/2D bi-directional, koneksi USB Plug-and-Play, tahan jatuh 1.5 meter ke beton.
* **Fungsi:** Memindai kode barcode pada dokumen surat jalan kertas dan gate pass cetak di loket pos sekuriti.

---

### [ITEM #16] Thermal Camera Reefer Yard
* **Klasifikasi:** *BLUEPRINT FISIK (Proteksi Kebakaran)*
* **Model:** Hikvision DS-2TD2636B-13/P Bi-Spectrum
* **Spesifikasi:** Sensor termal inframerah deteksi suhu -20°C s/d 150°C, akurasi ±2°C, alarm audio built-in.
* **Fungsi:** Memantau radiasi panas pada motor kompresor reefer untuk mencegah insiden kebakaran di reefer yard.

---

### [ITEM #17] GPS Tracker Truk & Aset 4G
* **Klasifikasi:** *BLUEPRINT FISIK (Pelacakan Armada)*
* **Model:** Teltonika TAT240 4G LTE Asset Tracker
* **Spesifikasi:** 4G Cat M1/NB-IoT, baterai internal otonom hingga 3 tahun, bodi IP68.
* **Fungsi:** Melacak posisi truk trailer dan gerbong datar di jalan raya lintas koridor Tanjung Priok – Cikarang.

---

### [ITEM #18] Industrial Edge AI PC Gate *(Otak Pengendali Gerbang)*
* **Gambar Lampiran:** `hardware/images/hw_18_edge_pc.jpg` *(atau `hardware/images/converted/item_18.png`)*
* **Klasifikasi:** **SIMULASI (Perangkat Pengirim JSON / Postman)**
* **Model:** Advantech ARK-3532 / Neousys Nuvo-9000 Fanless Series
* **Spesifikasi:** Intel Core i7, Desain Tanpa Kipas (*Fanless*), Suhu Operasional -20°C s/d 60°C, 4x COM RS-232/422/485, Dual GbE LAN PoE+, Input DC 9–48V.
* **Fungsi:** Menjadi gateway lokal di gardu gerbang yang mengonversi data timbangan, ANPR, OCR, dan RFID menjadi REST API JSON ke YMS & ERP Odoo.

---

### [ITEM #19] Gantry Container X-Ray Scanner (6 MeV)
* **Klasifikasi:** *BLUEPRINT FISIK (Fasilitas Bea Cukai)*
* **Model:** Nuctech MB1215DE / Smiths Detection HCVG
* **Spesifikasi:** High-Energy X-Ray (6 MeV), penetrasi baja > 300 mm, throughput 20–25 truk/jam, integrasi CEISA 4.0.
* **Fungsi:** Memindai isi muatan kargo peti kemas jalur merah tanpa pemeriksaan fisik bongkar muat (Investasi: Rp 12,5 Miliar).

---

### [ITEM #20] Vehicle Mounted Terminal (VMT)
* **Klasifikasi:** *BLUEPRINT FISIK (Komputer Kabin)*
* **Model:** Zebra VC8300 Rugged 8-Inch Android Terminal
* **Spesifikasi:** Layar sentuh 8 inci 1.000 nits ultra-bright, keyboard fisik QWERTY, sertifikasi MIL-STD-810G & IP66.
* **Fungsi:** Terpasang di kabin Reach Stacker / RTG untuk menampilkan tugas pemindahan kontainer dari YMS ke operator.

---

### [ITEM #21] DGPS / RTK GNSS Receiver Sensor
* **Gambar Lampiran:** `hardware/images/hw_21_dgps.png` *(atau `hardware/images/converted/item_21.png`)*
* **Klasifikasi:** **SIMULASI (Ditembak via JSON / Postman)**
* **Model:** CHCNAV CGI-610 Dual-Antenna GNSS/INS Sensor
* **Spesifikasi:** Akurasi RTK sentimeter (< 2 cm), update rate 50 Hz, sensor IMU terintegrasi, bodi maritim IP67, protokol NMEA-0183 / CAN Bus.
* **Fungsi:** Menentukan koordinat spatio-temporal presisi boom Reach Stacker untuk alokasi otomatis slot 3D (Bay-Row-Tier).

---

### [ITEM #22] Spreader Twistlock & Load Cell Sensor Kit
* **Gambar Lampiran:** `hardware/images/hw_22_twistlock.png` *(atau `hardware/images/converted/item_22.png`)*
* **Klasifikasi:** **SIMULASI (Ditembak via JSON / Postman)**
* **Model:** Bromma / Kalmar SmartSpreader Sensor Telematics Kit
* **Spesifikasi:** 4x *Inductive Proximity Sensor*, 4x *Strain Gauge Load Cell* pada headblock, bus komunikasi CANopen / RS-485.
* **Fungsi:** Mendeteksi status penguncian kontainer (`LOCKED`/`UNLOCKED`) dan bobot angkat riil kargo; memicu tagihan Lo-Lo Rp 250.000 di Odoo.

---

### [ITEM #23] Smart Reefer Power Monitoring Socket (380V/32A)
* **Gambar Lampiran:** `hardware/images/hw_23_reefer_socket.png` *(atau `hardware/images/converted/item_23.png`)*
* **Klasifikasi:** **SIMULASI (Ditembak via JSON / Postman)**
* **Model:** Marechal / Cavotec 3P+N+E 32A Decontactor Smart Receptacle
* **Spesifikasi:** Daya 380V/32A IP67, pembaca energi digital kWh, komunikasi Modbus RTU / LoRaWAN, deteksi trip listrik otomatis.
* **Fungsi:** Menyuplai daya 300 titik reefer, memantau kontinuitas arus dingin, dan menghitung tagihan plugging listrik per shift di Odoo Invoicing.

---

### [ITEM #24] Rugged Mobile PDA Tallyman
* **Gambar Lampiran:** `hardware/images/hw_24_pda.jpg` *(atau `hardware/images/converted/item_24.png`)*
* **Klasifikasi:** *BLUEPRINT FISIK (Perangkat Lapangan)*
* **Model:** Zebra TC57x / Honeywell CT60 Handheld Computer
* **Spesifikasi:** Android Enterprise, 2D Barcode Imager, IP68 tahan air/banting 1.8 meter, koneksi 4G LTE & WiFi 6, baterai hot-swap.
* **Fungsi:** Digunakan petugas tallyman lapangan untuk inspeksi fisik peti kemas dan pembuatan bukti serah terima (*EIR*).

---

### [ITEM #25] Rail Trackside Axle Counter Sensor
* **Gambar Lampiran:** `hardware/images/hw_25_axle_counter.webp` *(atau `hardware/images/converted/item_25.png`)*
* **Klasifikasi:** **SIMULASI (Ditembak via JSON / Postman)**
* **Model:** Frauscher RSR123 Wheel Sensor & Signal Converter
* **Spesifikasi:** Sensor induktif tanpa melubangi rel, standar keselamatan kereta api SIL 4, proteksi banjir IP68, output sinyal pulsa / Modbus.
* **Fungsi:** Mendeteksi pelek roda KA, menghitung as roda (*axle counting*), dan mengonfirmasi jumlah gerbong kontainer di jalur sepur simpan.

---

### [ITEM #26] Electronic Cargo Smart Seal (E-Seal GPS)
* **Gambar Lampiran:** `hardware/images/hw_26_eseal.png` *(atau `hardware/images/converted/item_26.png`)*
* **Klasifikasi:** **SIMULASI (Ditembak via JSON / Postman)**
* **Model:** Jointech JT701 Smart GPS Electronic Seal Lock
* **Spesifikasi:** Modul GPS/GSM/RFID, kabel baja sensor anti-potong (*tamper alert*), baterai 15.000 mAh (30 hari), sertifikasi pabean IP67.
* **Fungsi:** Mengunci pintu kontainer transit pabean Priok–Cikarang; memancarkan alarm darurat jika kawat dipotong paksa sebelum memperoleh izin SPPB di Odoo.
