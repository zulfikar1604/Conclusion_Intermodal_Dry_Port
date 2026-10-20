# 🔗 BAHAN TAYANG PRESENTASI: ARSITEKTUR INTEGRASI DATA (DATA INTEGRATION)
## Merancang Arsitektur Integrasi Data: Mencapai Efisiensi Operasional Real-Time Melalui Konsep *Zero Re-Entry Data* & *OAuth 2.0 Secured Payload* pada CIDP 35 Ha

* **Mata Kuliah:** Teknologi dan Perangkat Lunak Logistik
* **Dosen Pengampu:** Dr. Tigor Franky, S.T., M.T. (Referensi Materi: *Merancang Arsitektur Integrasi Data*, 13 Oktober 2026)
* **Institusi Akademik:** Program Studi S1 Manajemen Logistik — Institut Transportasi dan Logistik (ITL) Trisakti — Semester 5
* **Entitas Konsultan:** **Conclusion Supply Chain Consultant**
* **Topik Utama Silabus:** **Topik 7 — Inland Container Depot (ICD) & Dry Port Management**
* **Studi Kasus:** **Conclusion Intermodal Dry Port Management System (CIDP DPMS) 35 Ha — Cikarang**
* **Fokus Progres Minggu Ini:** **Arsitektur Integrasi Data End-to-End, Standar Protokol Multi-Konteks, Keamanan Payload OAuth 2.0, & Ketahanan Transaksi (Idempotency, Backoff, DLQ)**
* **Presenter Utama:** **Juan Gamaliel** (24D507001016) — *Data Integration Specialist*
* **Co-Presenter:** **Afriansayah Ayubi** (24D507001026) — *Software & ERP Process Specialist* & **Zulfikar Jafarudin Fatah** (24D507001001) — *Lead System Architect*
* **Anggota Tim Konsultan Pendukung:**
  - **Armansyah Muchtarrom** (24D507001010) — *Hardware & Infrastructure Specialist*
  - **Naufal Andika Heditya** (24D507001012) — *Business Analyst & QA Specialist*
* **Tanggal Penyusunan:** Oktober 2026

---

# 📑 DAFTAR ISI 12 SLIDE PRESENTASI (BERBASIS MATERI DR. TIGOR FRANKY + PROYEK CIDP)

1. **Slide 1:** Cover: Merancang Arsitektur Integrasi Data pada CIDP DPMS 35 Ha (*Zero Re-Entry & OAuth 2.0*)
2. **Slide 2:** Transisi Menuju Ekosistem Digital Terintegrasi: Menghapus Silo Data di Dry Port 35 Ha
3. **Slide 3:** Empat Prinsip Utama dalam Satu Aliran Data (*End-to-End Handshake, Zero Re-Entry, Real-Time Control, Secure by Design*)
4. **Slide 4:** Arsitektur *E2E Data Handshake*: Satu Status Operasional Memicu Tindakan Sistem Berikutnya (*Tri-Flow Correlation*)
5. **Slide 5:** Lapisan *Ingestion & Source*: Mengubah Fisik Lapangan Menjadi Data Digital Terstruktur Otomatis
6. **Slide 6:** Protokol Tepat untuk Setiap Konteks: REST API/JSON-RPC, EDI 940/945 (GS1/ISO), & TCP/IP + SSE Real-Time
7. **Slide 7:** Keamanan Payload Berbasis **OAuth 2.0** (*Arahan Notulensi*): Arsitektur *Bearer Token JWT* & *Cryptographic Trust*
8. **Slide 8:** Bedah Kontrak Payload Riil CIDP: *Contract-First Design* & Enkapsulasi OAuth 2.0 pada Endpoint Produksi
9. **Slide 9:** Transport yang Tahan Gangguan: *Idempotency*, *Local Edge Queue*, & *Exponential Backoff*
10. **Slide 10:** Pisahkan Error, Pertahankan Alur Utama: *ACID Rollback*, *Dead Letter Queue (DLQ)*, & Audit Trail `yard_events`
11. **Slide 11:** Dampak Efisiensi Operasional Nyata & Strategi Mitigasi Implementasi Bertahap di CIDP
12. **Slide 12:** Kesimpulan (*Satu Data. Satu Aliran. Tanpa Re-Entry.*), Skrip Live Demo, & Bank Q&A Pertahanan Dosen

---

```
========================================================================================
[SLIDE 1] COVER: MERANCANG ARSITEKTUR INTEGRASI DATA PADA CIDP DPMS 35 HA
========================================================================================
```

### 1. Visual Slide
* **Kategori / Banner:** TOPIK 7 — INLAND CONTAINER DEPOT (ICD) & DRY PORT MANAGEMENT | DATA INTEGRATION ARCHITECTURE
* **Judul Utama Slide:** Merancang Arsitektur Integrasi Data pada Conclusion Intermodal Dry Port (CIDP) 35 Ha
* **Sub-Judul:** Mencapai Efisiensi Operasional Real-Time Melalui Konsep *Zero Re-Entry Data* & Keamanan Payload Berbasis **OAuth 2.0**
* **Referensi Utama:** Materi Perkuliahan Dr. Tigor Franky, S.T., M.T. (*13 Oktober 2026*) + Implementasi Riil Codebase CIDP DPMS
* **Tim Pengembang & Konsultan (Conclusion Supply Chain Consultant):**
  1. **Juan Gamaliel** (24D507001016) — *Data Integration Specialist (Presenter Utama)*  
     *Fokus:* Arsitektur E2E Data Handshake, OAuth 2.0 Payload Security, REST API Gateway, & Idempotency Engine.
  2. **Afriansayah Ayubi** (24D507001026) — *Software & ERP Process Specialist*  
     *Fokus:* Integrasi JSON-RPC Live ERP Odoo (`api/odoo_client.php`), Terminal Tariff Engine, & Akrual Billing Otomatis.
  3. **Zulfikar Jafarudin Fatah** (24D507001001) — *Lead System Architect (Ketua Tim)*  
     *Fokus:* Orkestrasi Triad DPMS (TMS-YMS-WMS), Stream SSE Digital Twin (`api/stream_twin.php`), & Database MySQL.
  4. **Armansyah Muchtarrom** (24D507001010) — *Hardware & Infrastructure Specialist*  
     *Fokus:* Lapisan Ingestion Sensor AIDC/IoT (ANPR, OCR, Timbangan 80T, RFID, DGPS RTK, CAN-Bus).
  5. **Naufal Andika Heditya** (24D507001012) — *Business Analyst & QA Specialist*  
     *Fokus:* Validasi Kontrak Standar Global (ISO 6346, GS1 SSCC-18), CEISA 4.0 Bea Cukai, & Pengujian Anomali/DLQ.

### 2. Skrip Pembicara (Speaker Notes)
> *"Selamat pagi/siang Bapak Dr. Tigor Franky dan rekan-rekan sekalian.  
> Pada pertemuan minggu ini, kami dari **Conclusion Supply Chain Consultant** akan mempresentasikan kepingan penyambung paling vital dari keseluruhan proyek **Conclusion Intermodal Dry Port (CIDP) 35 Hektar**, yaitu **Arsitektur Integrasi Data (Data Integration)**.  
>
> Mengacu langsung pada materi perkuliahan Bapak Dr. Tigor mengenai **'Merancang Arsitektur Integrasi Data: Mencapai Efisiensi Operasional Real-Time Melalui Konsep Zero Re-Entry Data'**, serta menindaklanjuti arahan notulensi untuk menerapkan standar autentikasi **OAuth 2.0 pada pengiriman Payload**, hari ini kami akan membedah bagaimana seluruh modul di aplikasi CIDP—mulai dari sensor fisik di gerbang, derek Reach Stacker di lapangan, gudang CFS, rel kereta api Pasoso, CEISA 4.0 Bea Cukai, hingga ERP Odoo Cloud—berbicara dalam satu aliran data yang utuh, aman dengan OAuth 2.0, tahan gangguan, dan 100% bebas input ulang manual."*

---

```
========================================================================================
[SLIDE 2] TRANSISI MENUJU EKOSISTEM DIGITAL TERINTEGRASI (MENGHAPUS SILO DATA)
========================================================================================
```

### 1. Visual Slide
* **Judul:** Menuju Ekosistem Digital Terintegrasi: Menghapus Silo Data di Dry Port 35 Ha
* **Sub-Judul:** *"Silo data menciptakan friksi operasional. Kebutuhan: arsitektur integrasi otomatis, menyeluruh, dan dapat dipercaya."*
* **Perbandingan: Terminal Konvensional (Silo) vs Ekosistem Terintegrasi CIDP DPMS:**

| Dimensi Operasional | Kondisi Lama: Sistem Terisolasi (Data Silo) | Kondisi Aplikasi CIDP DPMS Saat Ini (Terintegrasi Penuh) |
| :--- | :--- | :--- |
| **1. Gerbang & Timbangan (TMS)** | Petugas gate mengetik ulang nomor plat & kontainer dari kertas surat jalan ke Excel lokal. | Kamera ANPR & Portal OCR membaca otomatis, timbangan 80T mengirim VGM langsung ke `api/ingest_event.php`. |
| **2. Lapangan Penumpukan (YMS)** | Operator Reach Stacker melapor posisi tumpukan lewat HT/radio; sering salah catat slot. | Sensor Twistlock Bromma & DGPS RTK `<2 cm` mengunci slot 3D (`Block-Bay-Row-Tier`) otomatis ke DB MySQL. |
| **3. Gudang Konsolidasi CFS (WMS)** | Tally gudang mencatat jumlah palet LCL di kertas clipboard lalu di-input ulang sore hari. | Scanner Honeywell membaca GS1 SSCC-18 & timbangan palet 3T langsung memperbarui stok `cfs_cargo` seketika. |
| **4. Kepabeanan & Kereta Api** | Pengecekan berkas SPPB Bea Cukai dan manifest gerbong KA dilakukan manual di loket terpisah. | Webhook H2H **CEISA 4.0 DJBC** & **Frauscher Axle Counter** langsung menyinkronkan status `SPPB_CLEARED` & muatan KA. |
| **5. Keuangan & Billing (ERP)** | Bagian keuangan mengumpulkan nota kertas dari lapangan; rawan kebocoran tagihan (*revenue leakage*). | Setiap event fisik otomatis membuat *invoice accrual* dan tersinkronisasi live ke **ERP Odoo Cloud** (`api/odoo_client.php`). |

* **Diagram Orkestrasi Triad DPMS dalam 3 Aliran Utama:**
```mermaid
flowchart LR
    subgraph SILO["❌ Masa Lalu: Silo Terpisah"]
        direction TB
        S1["Gate Excel"] -. "Input Ulang Manual" .-> S2["Yard Radio HT"]
        S2 -. "Nota Kertas" .-> S3["Kasir Billing"]
    end

    subgraph CIDP["✅ Proyek CIDP Saat Ini: Satu Ekosistem Digital Terintegrasi (Zero Re-Entry)"]
        direction LR
        IOT["26 Sensor & IoT Lapangan\n(ANPR, OCR, 80T, RTK, RFID)"] ==>|"OAuth 2.0 + JSON"| GW["API Integration Gateway\n(api/ingest_event.php)"]
        GW ==>|"Transaksi ACID"| DB[("MySQL cidp_yms\n+ Atomic Twin Cache")]
        DB ==>|"Triad DPMS"| MOD["TMS • YMS • WMS\n+ 2D/3D Digital Twin"]
        DB ==>|"JSON-RPC / H2H"| EXT["ERP Odoo SaaS • CEISA 4.0\n• Bank Mandiri H2H"]
    end
```

### 2. Skrip Pembicara (Speaker Notes)
> *"Mengapa kita membutuhkan arsitektur integrasi data di CIDP 35 Hektar?  
> Pada terminal darat tradisional, sistem berjalan secara terisolasi atau **data silo**: bagian gerbang punya catatan sendiri, operator alat berat di lapangan melapor pakai radio HT, gudang CFS mencatat di kertas, dan bagian kasir baru mengetik ulang tagihan di akhir hari. Friksi operasional ini menyebabkan antrean truk hingga 15 menit, kontainer terselip, dan kebocoran pendapatan.  
>
> Dalam proyek CIDP yang kami bangun saat ini, seluruh sekat silo tersebut kami runtuhkan melalui arsitektur **Triad DPMS (TMS, YMS, dan WMS)** yang disatukan oleh API Gateway terpusat. Begitu satu data lahir di gerbang, data tersebut mengalir lurus menembus seluruh modul internal hingga ke sistem eksternal seperti Bea Cukai CEISA 4.0 dan ERP Odoo tanpa ada satu pun proses ketik ulang."*

---

```
========================================================================================
[SLIDE 3] EMPAT PRINSIP UTAMA, SATU ALIRAN DATA DI PROYEK CIDP
========================================================================================
```

### 1. Visual Slide
* **Judul:** Empat Prinsip, Satu Aliran Data pada Arsitektur CIDP
* **Sub-Judul:** Penerapan 4 Pilar Integrasi Dr. Tigor Franky ke dalam Modul Aktual Aplikasi CIDP
* **4 Kuadran Prinsip Integrasi:**
  1. **01 — END-TO-END HANDSHAKE (Pertukaran Otomatis & Terverifikasi Antar Sistem):**
     * **Implementasi di Proyek CIDP:** Setiap perpindahan status operasional dikawal oleh mekanisme *handshake* (jabat tangan digital) berantai antara perangkat Edge (`api/edge_simulator.php`), endpoint ingesti (`api/ingest_event.php`), basis data MySQL (`cidp_yms`), dan klien ERP (`api/odoo_client.php`).
     * Sistem pengirim selalu menerima *acknowledgment response* terstruktur (`event_id`, `timestamp`, `tri_flow`, dan `state_summary`).
  2. **02 — ZERO RE-ENTRY (Sinkronisasi Mengeliminasi Penginputan Ulang):**
     * **Implementasi di Proyek CIDP:** Prinsip *"No Manual Data Entry for Physical Movements"*.
     * Nomor kontainer `MSKU9182374` dan plat truk `B 9812 UIX` hanya ditangkap **satu kali** oleh Kamera OCR & ANPR di Gate-In. Data yang sama otomatis dipakai untuk sertifikat timbangan SOLAS VGM, instruksi angkat Reach Stacker `RS-02`, lokasi stacking `B-08-03-02`, rilis SPPB pabean, hingga penerbitan faktur di Odoo.
  3. **03 — REAL-TIME CONTROL (Status Operasional Langsung Terlihat & Dapat Ditindak):**
     * **Implementasi di Proyek CIDP:** Perubahan data di tabel MySQL langsung memperbarui cache atomik `cache/twin_state.json` (`api/twin_state_helper.php`) dan dipancarkan ke layar pengguna melalui **Server-Sent Events (`api/stream_twin.php`)** setiap 500 milidetik.
     * Manajer langsung melihat pergerakan alat berat, okupansi yard (YOR), dan akrual pendapatan detik itu juga di `pages/digital_twin.php` & `pages/simulator.php`.
  4. **04 — SECURE BY DESIGN (Kontrol Akses Menjaga Integritas & Kerahasiaan):**
     * **Implementasi di Proyek CIDP (Sesuai Arahan Notulensi):** Setiap pertukaran payload diamankan dengan autentikasi **OAuth 2.0 (`Bearer Access Token / JWT`)**, tanda tangan kriptografi **HMAC-SHA256**, validasi algoritma matematis (Modulo 11 ISO 6346 & Modulo 10 GS1 SSCC-18), serta PDO *Prepared Statements* anti-SQL Injection.

### 2. Skrip Pembicara (Speaker Notes)
> *"Arsitektur integrasi CIDP berdiri di atas empat prinsip utama yang digariskan dalam materi perkuliahan:  
> Pertama, **End-to-End Handshake**: tidak ada pengiriman data yang dilepas begitu saja tanpa kepastian terima; setiap sensor dan modul melakukan jabat tangan digital yang terverifikasi.  
> Kedua, **Zero Re-Entry**: data kontainer dan truk yang sudah dipindai oleh kamera OCR dan ANPR di gerbang masuk tidak pernah diketik ulang lagi oleh staf di lapangan maupun staf keuangan.  
> Ketiga, **Real-Time Control**: melalui mesin Digital Twin berbasis Server-Sent Events di file `api/stream_twin.php`, begitu Reach Stacker menurunkan kontainer di lapangan, detik itu juga tampilan peta 2D dan simulator 3D di Command Center berubah secara otomatis.  
> Keempat, **Secure by Design**: sesuai arahan notulensi terbaru, seluruh lalu lintas payload kami proteksi dengan kerangka kerja **OAuth 2.0** sehingga hanya perangkat Edge dan sistem mitra yang memiliki token otorisasi sah yang dapat menyuntikkan data ke dalam server CIDP."*

---

```
========================================================================================
[SLIDE 4] ARSITEKTUR E2E DATA HANDSHAKE (SATU STATUS MEMICU TINDAKAN BERIKUTNYA)
========================================================================================
```

### 1. Visual Slide
* **Judul:** Arsitektur E2E Data Handshake: Korelasi 3 Aliran (*Tri-Flow Correlation*)
* **Sub-Judul:** *"Satu status operasional memicu tindakan sistem berikutnya — tanpa jeda administrasi."* (Implementasi pada `api/ingest_event.php`)
* **Diagram Sekuens E2E Handshake 6 Tahap Operasional CIDP:**
```mermaid
sequenceDiagram
    autonumber
    participant EDGE as Edge IoT / Gate / Crane<br/>(OAuth Client)
    participant API as CIDP Ingestion Gateway<br/>(api/ingest_event.php)
    participant DB as MySQL DB & Twin Cache<br/>(cidp_yms + twin_state.json)
    participant EXT as Sistem Eksternal<br/>(CEISA 4.0 & Odoo ERP)

    EDGE->>API: 1. POST TRUCK_GATE_ARRIVAL (Bearer Token + OCR/RFID)
    API->>DB: UPSERT trucks ('at_gate') & containers ('in_transit') + Log GATE_IN
    DB-->>API: Handshake OK (Palang Terbuka Otomatis)

    EDGE->>API: 2. POST TRUCK_WEIGHBRIDGE (Gross: 34.850 kg)
    API->>DB: UPDATE weight_gross + INSERT billing_invoices (Rp 75.000 PENDING)
    DB-->>API: Handshake OK (Sertifikat SOLAS VGM Terbit)

    EDGE->>API: 3. POST CONTAINER_LIFT (RS-02 Twistlock LOCKED)
    API->>DB: UPDATE equipment ('carrying') + INSERT billing_invoices (Lo-Lo Rp 325.000)
    DB-->>API: Handshake OK (Status Kontainer IN_TRANSIT)

    EDGE->>API: 4. POST CONTAINER_STACK (RTK <2cm: Slot B-08-03-02)
    API->>DB: UPDATE containers ('in_yard', B-08-03-02) + Start Dwell Time (t0)
    DB-->>API: Handshake OK (Visual 3D & 2D Twin Terupdate)

    EXT->>API: 5. POST CUSTOMS_CEISA (Webhook DJBC: SPPB_CLEARED)
    API->>DB: UPDATE customs_status = 'SPPB_CLEARED' (Aksen Hijau Pabean)
    DB-->>EXT: Handshake 200 OK (Izin Pabean Terverifikasi)

    EXT->>API: 6. POST PAYMENT_SETTLED (Mandiri H2H / Odoo Invoice Sync)
    API->>DB: UPDATE billing_invoices = 'PAID' + Buka Interlock Gate-Out
    DB-->>EXT: Handshake 200 OK (e-Exit Pass & SP2B Rilis)
```
* **Bukti Koding Korelasi 3 Aliran (`$tri_flow_log` di `api/ingest_event.php`):**
  Setiap *single event* yang masuk secara otomatis mengeksekusi **3 dampak serempak** tanpa jeda administrasi:
  1. `tri_flow['physical']`: Mengubah posisi fisik armada/kontainer & memicu aktuator (buka palang / kunci twistlock).
  2. `tri_flow['information']`: Mencatat sertifikasi VGM, koordinat spasial `B-08-03-02`, atau nomor SPPB Bea Cukai.
  3. `tri_flow['financial']`: Mengakrual tagihan otomatis ke tabel `billing_invoices` dan menyinkronkannya ke ERP Odoo.

### 2. Skrip Pembicara (Speaker Notes)
> *"Slide 4 ini memperlihatkan bagaimana konsep **Arsitektur E2E Data Handshake** dari materi Bapak Dr. Tigor telah kami wujudkan menjadi baris kode nyata di berkas `api/ingest_event.php` dan `api/edge_simulator.php`.  
> Perhatikan bagaimana satu status operasional secara instan memicu tindakan sistem berikutnya tanpa jeda administrasi:  
> 1. Saat truk tiba di gerbang (`TRUCK_GATE_ARRIVAL`), pembacaan kamera OCR dan antena RFID langsung mendaftarkan truk dan membuka palang.  
> 2. Detik truk menginjak jembatan timbang (`TRUCK_WEIGHBRIDGE`), berat 34.850 kg langsung menerbitkan sertifikat SOLAS VGM sekaligus mencetak tagihan timbang Rp 75.000.  
> 3. Saat Reach Stacker `RS-02` mengunci kontainer (`CONTAINER_LIFT`) dan menaruhnya di slot `B-08-03-02` (`CONTAINER_STACK`), sistem langsung mengunci koordinat 3D, menyalakan penghitung waktu inap (*dwell time $t_0$*), dan menagihkan biaya Lift-Off Rp 325.000.  
> 4. Begitu webhook `CUSTOMS_CEISA` dari Bea Cukai dan `PAYMENT_SETTLED` dari pelunasan bank/Odoo masuk, kunci gerbang keluar (*gate-out interlock*) otomatis terbuka. Semuanya mengalir otomatis dari hulu ke hilir."*

---

```
========================================================================================
[SLIDE 5] LAPISAN INGESTION & SOURCE (DARI FISIK KE DATA DIGITAL TERSTRUKTUR)
========================================================================================
```

### 1. Visual Slide
* **Judul:** Lapisan Ingestion & Source: Mengubah Aktivitas Fisik Menjadi Data Digital Terstruktur
* **Sub-Judul:** *"Menjadi data digital terstruktur secara otomatis."* — Pemetaan 26 Instrumen Hardware Lapangan CIDP ke Endpoint Ingestion
* **Arsitektur Lapisan Source & Ingestion pada 4 Zona Operasional CIDP:**

| Zona Operasional (Pilar DPMS) | Instrumen Source / Sensor Fisik (Layer 1) | Sinyal Mentah (Raw Signal) | Transformasi Data Terstruktur di Lapisan Ingestion | Tabel Tujuan di DB `cidp_yms` |
| :--- | :--- | :--- | :--- | :--- |
| **1. Gate & Weighbridge (Pilar TMS)** | • Kamera ANPR Hikvision (`HW-01`)<br>• Portal OCR ISO 6346 (`HW-02`)<br>• Timbangan Pitless 80T Avery (`HW-03`)<br>• UHF RFID Reader 20m (`HW-04`)<br>• Edge AI PC Advantech (`HW-06`) | • Frame citra optik 4K<br>• Gelombang RF 920 MHz<br>• Sinyal elektrik Load Cell (RS-232 Modbus) | Agregasi oleh Edge AI PC menjadi JSON: `truck_plate`, `container_number` (tervalidasi Check Digit), `rfid_tag`, `weight_gross`, `vgm_net`. | `trucks`, `containers`, `billing_invoices`, `yard_events` |
| **2. Lapangan Penumpukan 35 Ha (Pilar YMS)** | • DGPS RTK GNSS `<2 cm` (`HW-07`)<br>• Spreader Twistlock & Load Cell Bromma (`HW-08`)<br>• CAN-Bus Kalmar DRG450 | • String satelit NMEA `$GNGGA`<br>• Sinyal pin induktif CANopen (`0`/`1`)<br>• Telemetri solar & RPM mesin | Perkawinan sensor: Saat Twistlock transisi `LOCKED` $\rightarrow$ `UNLOCKED`, koordinat GPS $(X,Y,Z)$ dikonversi menjadi matriks `block`, `bay`, `row`, `tier`. | `containers`, `equipment`, `yard_events`, `billing_invoices` |
| **3. Cold Chain & Gudang CFS (Pilar YMS & WMS)** | • Smart Reefer Socket 380V (`HW-23`)<br>• Scanner Barcode GS1 Honeywell (`HW-18`)<br>• Timbangan Pallet 3T (`HW-17`)<br>• Detektor Multigas (`HW-19`) | • Paket radio LoRaWAN suhu/arus<br>• Sinyal optik 2D GS1-128<br>• Sinyal berat palet & ppm gas | Dekode Application Identifier `(00)` SSCC-18, `(10)` Batch, `(17)` Exp Date; konversi suhu `-18.4°C` & durasi shift listrik reefer. | `cfs_cargo`, `containers`, `yard_events`, `billing_invoices` |
| **4. Siding Rel KA & Koridor Pabean (Intermodal)** | • Frauscher Rail Axle Counter (`HW-10/25`)<br>• Smart GPS E-Seal LoRa/GSM (`HW-26`)<br>• Gantry X-Ray 6 MeV (`HW-24`) | • Pulsa magnetik roda besi KA<br>• Koordinat GPS & status kawat segel | Konversi jumlah gandar ($\text{Gerbong} = \text{Axle}/4$), pelacakan geofencing koridor Pasoso–Cikarang, & status `PABEAN_TAMPER_ALERT`. | `trains`, `containers`, `yard_events` |

### 2. Skrip Pembicara (Speaker Notes)
> *"Pada Slide 5, kita membedah **Lapisan Ingestion & Source** sesuai kerangka materi Bapak Dr. Tigor: bagaimana aktivitas fisik di lapangan berubah menjadi data digital terstruktur secara otomatis.  
> Di kawasan CIDP 35 Hektar, sumber data kita bukanlah jari manusia di atas keyboard, melainkan 26 instrumen sensor industri yang terbagi dalam empat klaster:  
> - Di Gerbang Masuk, Edge AI PC Advantech (`HW-06`) menggabungkan tangkapan plat nomor dari kamera ANPR, nomor kontainer dari portal OCR, tag RFID supir, dan tonase dari jembatan timbang 80 ton menjadi satu payload JSON yang utuh.  
> - Di Lapangan Yard, lapisan ingestion kita mengawinkan sensor **Twistlock Spreader** dengan **DGPS RTK berakurasi di bawah 2 sentimeter**. Tepat di detik operator Reach Stacker melepas kunci twistlock, koordinat satelit langsung diterjemahkan otomatis menjadi alamat `Blok-Bay-Row-Tier`.  
> - Begitu pula di Gudang CFS dengan scanner barcode GS1 SSCC-18 dan di Siding Kereta Api dengan sensor penghitung gandar roda Frauscher. Semuanya terkonversi otomatis menjadi baris data relasional yang siap diolah."*

---

```
========================================================================================
[SLIDE 6] PROTOKOL TEPAT UNTUK SETIAP KONTEKS DI EKOSISTEM CIDP
========================================================================================
```

### 1. Visual Slide
* **Judul:** Protokol Tepat untuk Setiap Konteks Operasional CIDP
* **Sub-Judul:** Pemetaan 3 Standar Protokol Materi Perkuliahan ke dalam Implementasi Nyata Proyek CIDP
* **3 Pilar Protokol Integrasi CIDP:**

#### 1. 🌐 REST API & JSON-RPC (`JSON · WEB / CLOUD` — Komunikasi Lintas Aplikasi)
* **Konteks Penggunaan di Proyek CIDP:**
  - **API Gateway Internal:** `api/ingest_event.php`, `api/simulator_action.php`, `api/crud.php`, dan `api/beranda_telemetry.php` menggunakan HTTP/HTTPS RESTful JSON untuk komunikasi antar-modul web dan Edge Gateway.
  - **Integrasi Cloud ERP Live (`api/odoo_client.php`):** Menggunakan protokol **JSON-RPC 2.0 over HTTPS** langsung ke server produksi **`https://conclusion-intermodal-dry-port.odoo.com`** (Odoo Enterprise SaaS) untuk sinkronisasi model `account.move` (Customer Invoices), `product.product` (Katalog Tarif Terminal), dan `res.partner` (Pelanggan/Shipper).
  - **Host-to-Host Eksternal:** Webhook REST JSON dengan **CEISA 4.0 DJBC** (status SPPB/Jalur Merah) dan **Bank Mandiri H2H** (`PAYMENT_SETTLED`).

#### 2. 📄 EDI 940/945 & Standar AIDC Global (`PARTNER EKSTERNAL` — Principal & Mitra Logistik)
* **Konteks Penggunaan di Proyek CIDP:**
  - **EDI 940 (*Warehouse Shipping Order* / *Pre-Advise Manifest*):** Diterima oleh CIDP dari *Shipping Line* / Principal (Maersk, CMA CGM, ONE, PT Samudera Logistik Prima) sebagai instruksi pra-kedatangan kontainer (*Booking / Delivery Order*) dan perintah pengeluaran barang di Gudang CFS (`pages/cfs.php`).
  - **EDI 945 (*Warehouse Shipping Advice* / *Gate & Stuffing Confirmation*):** Dikirim balik secara otomatis oleh CIDP ke Principal setelah kontainer selesai *stripping/stuffing* di Rampa CFS D1–D5, dimuat ke Gerbong Datar KA Pasoso (`pages/intermodal.php`), atau resmi keluar gerbang (`GATE_OUT`).
  - **Standar Muatan Data EDI:** Mematuhi kode peti kemas **ISO 6346** (Check Digit Modulo 11) dan **GS1-128 / SSCC-18** (`pages/scanner.php`).

#### 3. ⚡ TCP/IP, SSE & Protokol Industri (`WAREHOUSE & YARD REAL-TIME` — WMS, WCS, & Alat Berat)
* **Konteks Penggunaan di Proyek CIDP:**
  - **Server-Sent Events / Persistent HTTP Stream (`api/stream_twin.php`):** Aliran data *real-time* satu arah berlatensi rendah (`text/event-stream`, interval cek **500 ms** + *heartbeat* **4 detik**) yang mendorong pembaruan posisi Reach Stacker, truk, dan slot kontainer langsung ke kanvas **2D Digital Twin (`pages/digital_twin.php`)** dan **3D WebGL Simulator (`pages/simulator.php`)** tanpa perlu *refresh* halaman.
  - **Protokol Lantai Lapangan (WCS):** Socket **TCP/IP** pada kamera ANPR/OCR, **RS-232/RS-485 Modbus RTU** pada jembatan timbang 80T, **CANopen** pada spreader crane Kalmar, dan **LoRaWAN/MQTT** pada 300 soket Reefer.

### 2. Skrip Pembicara (Speaker Notes)
> *"Sebagaimana ditekankan Bapak Dr. Tigor, tidak ada satu protokol tunggal yang cocok untuk semua kebutuhan; kita harus memilih **protokol yang tepat untuk setiap konteks**:  
> 1. Untuk **komunikasi lintas aplikasi dan cloud**, CIDP menggunakan **REST API berbasis JSON** pada `api/ingest_event.php` dan `api/simulator_action.php`, serta **JSON-RPC 2.0** pada `api/odoo_client.php` yang terhubung secara live ke server cloud **Odoo Enterprise (`conclusion-intermodal-dry-port.odoo.com`)**.  
> 2. Untuk **pertukaran dokumen dengan partner eksternal dan principal pelayaran**, kita memetakan standar **EDI 940** (instruksi pengiriman/pra-notifikasi DO masuk ke CFS dan Yard) dan **EDI 945** (konfirmasi realisasi bongkar muat dan pengiriman) yang dikombinasikan dengan standar identifikasi **GS1 SSCC-18** dan **ISO 6346**.  
> 3. Sedangkan untuk **kontrol gudang dan lapangan real-time**, selain TCP/IP dan Modbus di level sensor, aplikasi kita telah mengimplementasikan streaming real-time di `api/stream_twin.php` dengan siklus 500 milidetik untuk menggerakkan objek di layar Digital Twin secara instan."*

---

```
========================================================================================
[SLIDE 7] KEAMANAN PAYLOAD BERBASIS OAUTH 2.0 (ARAHAN NOTULENSI)
========================================================================================
```

### 1. Visual Slide
* **Judul:** Mengamankan Pertukaran Payload dengan Standar **OAuth 2.0** (*Secure by Design*)
* **Sub-Judul:** Menindaklanjuti Arahan Notulensi: Implementasi *OAuth 2.0 Authorization Framework (RFC 6749)* & *JWT Bearer Token (RFC 7519)* pada API CIDP
* **Mengapa Payload CIDP Wajib Menggunakan OAuth 2.0?**
  - Tanpa autentikasi berstandar industri, endpoint terbuka seperti `/api/ingest_event.php` atau `/api/odoo_client.php` rentan terhadap *spoofing* (pemalsuan status timbangan VGM, manipulasi rilis SPPB Bea Cukai, atau pemalsuan status lunas `PAYMENT_SETTLED`).
  - **OAuth 2.0** memisahkan peran *Authentication Server* dan *Resource Server* menggunakan **Access Token berbatas waktu (*Short-Lived Bearer Token*)** dan **Scope Hak Akses Spesifik (*Granular Scopes*)**, tanpa pernah mempertukarkan *username/password* mentah di dalam setiap transaksi payload.
* **Alur Handshake OAuth 2.0 (`Client Credentials Grant` untuk Machine-to-Machine IoT/ERP/H2H):**
```mermaid
sequenceDiagram
    autonumber
    participant CLIENT as Client Pengirim Payload<br/>(Edge AI PC HW-06 / CEISA / Mandiri H2H)
    participant AUTH as CIDP OAuth 2.0 Auth Server<br/>(POST /oauth/token)
    participant GATEWAY as CIDP API Resource Gateway<br/>(api/ingest_event.php)

    Note over CLIENT,AUTH: Tahap 1: Permintaan Access Token (Machine-to-Machine)
    CLIENT->>AUTH: POST /oauth/token<br/>grant_type=client_credentials<br/>client_id=cidp_edge_gate01 & client_secret=***<br/>scope=ingest:telemetry yard:write billing:accrue
    AUTH-->>CLIENT: HTTP 200 OK<br/>{"access_token": "eyJhbGciOiJSUzI1Ni...", "token_type": "Bearer", "expires_in": 3600}

    Note over CLIENT,GATEWAY: Tahap 2: Pengiriman Payload Operasional dengan Bearer Token
    CLIENT->>GATEWAY: POST /api/ingest_event.php<br/>Authorization: Bearer eyJhbGciOiJSUzI1Ni...<br/>X-Payload-Signature: sha256=9f86d081884c...<br/>Body: { "oauth_context": {...}, "event_type": "TRUCK_WEIGHBRIDGE", "payload": {...} }
    GATEWAY->>GATEWAY: 1. Verifikasi Signature JWT & Expiry (exp)<br/>2. Cek OAuth Scope ('ingest:telemetry')<br/>3. Cek HMAC Integrity & Schema Contract
    GATEWAY-->>CLIENT: HTTP 200 OK (Payload Dipercaya & Diproses ke MySQL)
```
* **Matriks Pembagian `client_id` & `scope` OAuth 2.0 pada Ekosistem CIDP:**

| Entitas Sistem Pengirim | OAuth 2.0 Grant Type | `client_id` Terdaftar | `scope` Otorisasi yang Diizinkan | Endpoint yang Diakses |
| :--- | :--- | :--- | :--- | :--- |
| **Edge AI PC Gate & Timbangan (`HW-06`)** | `client_credentials` | `cidp-edge-gate-01` | `ingest:gate` `ingest:weighbridge` | `POST /api/ingest_event.php` |
| **Kalmar Telemetry & RTK Gateway (`RS/RTG`)** | `client_credentials` | `cidp-edge-yard-fleet` | `ingest:equipment` `yard:stack` | `POST /api/ingest_event.php`, `/api/simulator_action.php` |
| **Webhook CEISA 4.0 DJBC (Bea Cukai)** | `client_credentials` | `djbc-ceisa40-h2h` | `customs:sppb:write` | `POST /api/ingest_event.php` (`CUSTOMS_CEISA`) |
| **Payment Gateway Mandiri H2H & Odoo ERP** | `client_credentials` | `erp-odoo-finance-sync` | `billing:settle` `erp:invoice:sync` | `POST /api/ingest_event.php`, `/api/odoo_client.php` |
| **Dashboard Operator & Eksekutif Web** | `authorization_code` | `cidp-web-portal-ui` | `dashboard:read` `simulator:control` | `GET /api/stream_twin.php`, `POST /api/crud.php` |

### 2. Skrip Pembicara (Speaker Notes)
> *"Menjawab secara khusus arahan notulensi minggu ini bahwa **'Payload harus menggunakan OAuth'**, pada Slide 7 ini kami merancang arsitektur keamanan **OAuth 2.0** yang terintegrasi langsung dengan ekosistem CIDP.  
> Mengapa OAuth 2.0 sangat krusial di dry port? Karena kita menghubungkan perangkat keras lapangan (seperti jembatan timbang dan Reach Stacker) serta instansi eksternal (seperti Bea Cukai CEISA 4.0 dan Bank Mandiri Host-to-Host) langsung ke database transaksi kita. Kita tidak boleh menaruh password database atau API key statis yang mudah bocor di dalam payload.  
>
> Dengan **OAuth 2.0 Client Credentials Grant**:  
> 1. Sebelum Edge AI PC di gerbang mengirim data timbangan, ia melakukan autentikasi ke endpoint `/oauth/token` menggunakan `client_id` dan `client_secret` miliknya untuk meminta **Bearer Access Token berformat JWT** yang hanya berlaku selama 1 jam (`3600 detik`).  
> 2. Setiap token dikunci dengan **Scope spesifik** (*Least Privilege Principle*): misalnya token milik komputer gerbang hanya punya scope `ingest:gate` dan `ingest:weighbridge`—ia tidak akan pernah bisa memalsukan status lunas tagihan (`billing:settle`) atau status SPPB Bea Cukai (`customs:sppb:write`).  
> 3. Saat payload ditembakkan ke `api/ingest_event.php`, API Gateway memverifikasi keabsahan tanda tangan kriptografi JWT dan scope-nya terlebih dahulu sebelum menyentuh database."*

---

```
========================================================================================
[SLIDE 8] BEDAH KONTRAK PAYLOAD RIIL CIDP (CONTRACT-FIRST & OAUTH 2.0)
========================================================================================
```

### 1. Visual Slide
* **Judul:** Payload Dipercaya Sebelum Diproses: *Contract-First Design* & *Cryptographic Trust*
* **Sub-Judul:** Struktur Riil HTTP Header & JSON Payload Berbasis **OAuth 2.0** pada `api/ingest_event.php`
* **2 Pilar Kepercayaan Payload (Materi Dr. Tigor Franky):**
  1. **CONTRACT-FIRST DESIGN (Format Valid dan Konsisten):** Struktur JSON wajib mematuhi kontrak skema baku (`event_id`, `event_type`, `source_device`, `oauth_context`, `payload`), tipe data ketat (`float` untuk tonase VGM, pola regex `^[A-Z]{4}[0-9]{7}$` + Modulo 11 untuk nomor kontainer ISO 6346).
  2. **CRYPTOGRAPHIC TRUST (Manipulasi Terdeteksi & Akses Terkendali):** Kombinasi header **`Authorization: Bearer <OAuth2_JWT_Token>`** + metadata **`oauth_context`** + **`signature_hmac` (HMAC-SHA256)** memastikan jika angka berat timbangan diubah 1 kg saja di tengah jalan (*Man-in-the-Middle*), nilai hash tidak cocok dan payload langsung ditolak (`HTTP 401/403`).

* **Kotak Kiri: Contoh Request Payload OAuth 2.0 ke `POST /api/ingest_event.php`:**
```http
POST /Conclusion_Intermodal_Dry_Port/api/ingest_event.php HTTP/1.1
Host: localhost
Content-Type: application/json; charset=utf-8
Authorization: Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJjaWRwLWF1dGgtc2VydmVyIiwic3ViIjoiY2lkcC1lZGdlLWdhdGUtMDEiLCJzY29wZSI6ImluZ2VzdDpnYXRlIGluZ2VzdDp3ZWlnaGJyaWRnZSIsImV4cCI6MTc5MjUwMzYwMH0.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c
X-OAuth-Client-Id: cidp-edge-gate-01
X-Idempotency-Key: EVT-20261020101530-8f9a
X-Payload-Signature: sha256=4b227777d4dd1fc61c6f884f48641d02b4d121d3fd328cb08b5531fcacdabf8a

{
  "event_id": "EVT-20261020101530-8f9a",
  "event_type": "TRUCK_WEIGHBRIDGE",
  "source_device": "WEIGHBRIDGE-80T-LANE01",
  "oauth_context": {
    "token_type": "Bearer",
    "client_id": "cidp-edge-gate-01",
    "granted_scopes": ["ingest:gate", "ingest:weighbridge"],
    "issued_at": "2026-10-20T10:00:00+07:00",
    "expires_at": "2026-10-20T11:00:00+07:00"
  },
  "payload": {
    "truck_plate": "B 9812 UIX",
    "container_number": "MSKU9182374",
    "iso_code": "42G1",
    "weight_gross": 34850.0,
    "truck_tare_kg": 11200.0,
    "vgm_net_kg": 23650.0,
    "solas_verified": true
  }
}
```

* **Kotak Kanan: Contoh Response Terverifikasi dari `api/ingest_event.php` (`HTTP 200 OK`):**
```json
{
  "success": true,
  "event_id": "EVT-20261020101530-8f9a",
  "event_type": "TRUCK_WEIGHBRIDGE",
  "source_device": "WEIGHBRIDGE-80T-LANE01",
  "oauth_verified": {
    "authenticated": true,
    "client_id": "cidp-edge-gate-01",
    "scope_matched": "ingest:weighbridge",
    "integrity_check": "HMAC_SHA256_VALID"
  },
  "timestamp": "2026-10-20T10:15:30+07:00",
  "tri_flow": {
    "physical": "Truk B 9812 UIX ditimbang di Jembatan Timbang 80T (Berat: 34.850 kg)",
    "information": "Sertifikat SOLAS VGM diterbitkan secara digital untuk MSKU9182374",
    "financial": "Biaya Timbang Rp 75.000 terakrual pada invoice INV-W-20261020-a4f1"
  },
  "state_summary": {
    "containers_in_yard": 19,
    "total_revenue_accrued_idr": 48825000,
    "sppb_cleared": 15
  }
}
```

### 2. Skrip Pembicara (Speaker Notes)
> *"Pada Slide 8 ini, kami menampilkan bedah kode nyata bagaimana prinsip **'Payload Dipercaya Sebelum Diproses'** bekerja di endpoint produksi kita `api/ingest_event.php`.  
> Perhatikan bagian sebelah kiri: setiap request yang dikirim oleh jembatan timbang 80 ton membawa **Header `Authorization: Bearer`** berisi JSON Web Token (JWT), serta objek **`oauth_context`** di dalam body payload yang merinci `client_id`, `granted_scopes`, dan masa kedaluwarsa token, ditambah tanda tangan **`X-Payload-Signature` (HMAC-SHA256)**.  
>
> Sebelum baris kode PHP kita menjalankan query `UPDATE trucks` atau `INSERT INTO billing_invoices`, sistem memeriksa dua hal:  
> Pertama, **Cryptographic Trust**: apakah Bearer Token OAuth 2.0 ini asli, belum kedaluwarsa, memiliki scope `ingest:weighbridge`, dan tanda tangan HMAC-nya cocok dengan isi body? Jika ada pihak luar mencoba mengubah angka `weight_gross` dari 34.850 kg menjadi 20.000 kg di tengah jalan, nilai hash HMAC akan berubah dan sistem langsung menolak request dengan status `401 Unauthorized`.  
> Kedua, **Contract-First Design**: sistem memastikan format JSON sesuai kontrak skema. Setelah lolos keduanya, barulah transaksi dieksekusi dan menghasilkan respons korelasi 3 aliran (*physical, information, financial*) seperti terlihat di kotak kanan."*

---

```
========================================================================================
[SLIDE 9] TRANSPORT TAHAN GANGGUAN: IDEMPOTENCY & EXPONENTIAL BACKOFF
========================================================================================
```

### 1. Visual Slide
* **Judul:** Transport yang Tahan Gangguan: *Gagal Kirim Bukan Berarti Gagal Proses*
* **Sub-Judul:** *"Saat sistem hilir down, event tetap aman di antrean—lalu dilanjutkan tanpa kehilangan data."*
* **2 Mekanisme Ketahanan Transaksi pada Aplikasi CIDP:**

#### 1. 🔑 IDEMPOTENCY (Mencegah Duplikasi Data & Penagihan Ganda / *Zero Double-Billing*)
* **Masalah Lapangan:** Sinyal Wi-Fi 7 / 4G di area yard 35 Ha bisa berkedip saat Reach Stacker `RS-02` mengirim event `CONTAINER_LIFT`, sehingga perangkat Edge mengirim paket JSON yang sama 2 atau 3 kali.
* **Implementasi Nyata di Kode CIDP (`api/ingest_event.php`):**
  - Setiap paket memiliki kunci unik **`event_id` / `X-Idempotency-Key`** (contoh: `EVT-20261020101530-8f9a`).
  - Pada `TRUCK_GATE_ARRIVAL` (baris 70–88): Sistem melakukan pengecekan eksistensi (`SELECT id FROM trucks WHERE license_plate = ?` dan `SELECT id FROM containers WHERE container_number = ?`). Jika sudah ada, sistem melakukan `UPDATE` state (bukan `INSERT` duplikat).
  - Pada `PAYMENT_SETTLED` (baris 238): Query dikunci dengan klausa idempoten `UPDATE billing_invoices SET payment_status = 'PAID' WHERE container_number = ? AND payment_status = 'PENDING'`. Pengiriman ulang webhook pelunasan **tidak akan pernah menggandakan jurnal pembayaran**.

#### 2. ⏳ LOCAL EDGE QUEUE & EXPONENTIAL BACKOFF (Ketahanan Saat Sistem Hilir Down)
* **Masalah Lapangan:** Bagaimana jika server eksternal **Odoo Cloud (`conclusion-intermodal-dry-port.odoo.com`)** atau **Server CEISA 4.0 Bea Cukai** sedang mengalami gangguan jaringan? Apakah operasional crane dan gerbang di Cikarang harus berhenti?
* **Solusi Arsitektur CIDP:**
  - **Decoupled Local Buffer & Atomic Cache:** Transaksi utama disimpan terlebih dahulu di database lokal MySQL `cidp_yms` dan cache atomik `cache/twin_state.json` (`write_twin_state_cache()` menggunakan teknik *atomic file rename* `.tmp` di `api/twin_state_helper.php` baris 248–258), sehingga operasional lapangan 35 Ha **tetap berjalan 100% tanpa henti**.
  - **Algoritma Retry Exponential Backoff:** Sinkronisasi keluar ke Odoo Cloud (`api/odoo_client.php` dengan parameter `$timeout = 10` detik) dan webhook eksternal menggunakan antrean *retry* dengan waktu tunggu berlipat ganda:
    $$T_{\text{wait}} = \min\left(T_{\max},\; T_{\text{base}} \times 2^{\text{attempt}} + \text{Jitter}\right) \implies 2\text{ dtk} \;\rightarrow\; 4\text{ dtk} \;\rightarrow\; 8\text{ dtk} \;\rightarrow\; 16\text{ dtk} \;\rightarrow\; 32\text{ dtk}$$

```mermaid
flowchart LR
    A["Event Terjadi di Lapangan\n(Gate / RS-02 / Timbangan)"] --> B["Simpan Atomik di DB Lokal CIDP\n& cache/twin_state.json"]
    B --> C{"Kirim ke Sistem Hilir\n(Odoo Cloud / CEISA 4.0)"}
    C -->|"HTTP 200 OK"| D["Status: SYNCED_LIVE\nTransaksi Selesai"]
    C -->|"Timeout / HTTP 503 Down"| E["Masuk Antrean Retry\n(Exponential Backoff: 2s, 4s, 8s, 16s)"]
    E -->|"Percobaan <= 5x"| C
    E -->|"Gagal > 5x (Batas Retry)"| F["Pindahkan ke Dead Letter Queue (DLQ)\nTanpa Menghentikan Alur Utama"]
```

### 2. Skrip Pembicara (Speaker Notes)
> *"Dalam materi Bapak Dr. Tigor ditegaskan bahwa: **'Saat sistem hilir down, event tetap aman di antrean—lalu dilanjutkan tanpa kehilangan data. Gagal kirim bukan berarti gagal proses.'**  
> Di proyek CIDP, kami menerjemahkan prinsip ini melalui dua mekanisme: **Idempotency** dan **Exponential Backoff**.  
>
> Pertama, **Idempotency**: di lapangan pelabuhan 35 Hektar, koneksi nirkabel dari komputer kabin Reach Stacker bisa saja terputus sepersekian detik setelah mengirim sinyal angkat boks, lalu perangkat otomatis mengirim ulang paket yang sama. Dengan kunci `event_id` unik dan pengecekan state idempoten di `api/ingest_event.php`, sistem mengenali bahwa event tersebut sudah diproses, sehingga kontainer tidak akan pernah tercatat ganda dan pelanggan **tidak akan pernah ditagih dua kali**.  
>
> Kedua, **Transport Tahan Gangguan dengan Exponential Backoff**: karena aplikasi kita terhubung secara live ke server cloud **Odoo Enterprise (`conclusion-intermodal-dry-port.odoo.com`)** di `api/odoo_client.php`, bagaimana jika internet publik sedang gangguan? Di sistem kita, transaksi operasional tetap disimpan secara aman ke database lokal dan cache atomik `cache/twin_state.json` sehingga palang gerbang dan crane di lapangan tetap jalan terus. Sementara itu, pengiriman faktur ke cloud Odoo masuk ke antrean *retry* bertahap—menunggu 2 detik, 4 detik, 8 detik, hingga 16 detik—sampai koneksi pulih kembali tanpa satu byte pun data yang hilang."*

---

```
========================================================================================
[SLIDE 10] PISAHKAN ERROR, PERTAHANKAN ALUR UTAMA (DLQ, ACID ROLLBACK & AUDIT TRAIL)
========================================================================================
```

### 1. Visual Slide
* **Judul:** Pisahkan Error, Pertahankan Alur Utama: *DLQ, ACID Rollback, & Audit Trail*
* **Sub-Judul:** *"Batas retry melindungi throughput. DLQ dan audit trail mempercepat root-cause analysis tanpa menghentikan transaksi sehat."*
* **3 Lapisan Isolasi Error pada Aplikasi CIDP:**

#### 1. 🛡️ Isolasi Transaksi dengan Database ACID & Auto-Rollback (`api/ingest_event.php`)
* Seluruh mutasi multi-tabel dibungkus di dalam blok transaksi atomik `$pdo->beginTransaction()` (baris 55) hingga `$pdo->commit()` (baris 254).
* Apabila salah satu langkah gagal (misal format event tidak dikenali atau constraint gagal), blok `catch (Exception $e)` langsung mengeksekusi **`$pdo->rollBack()`** (baris 275), memastikan tidak ada *partial/corrupt data* yang mengotori database.

#### 2. 📥 Dead Letter Queue (DLQ) & Isolasi Fisik Jalur Anomali
* **Di Lapisan Data (DLQ):** Jika sebuah payload tetap gagal setelah mencapai batas maksimum *retry* (misal karena kontrak data cacat, Check Digit ISO 6346 salah, atau percobaan menumpuk kontainer di slot yang sudah terisi pada `api/simulator_action.php` baris 149–161), pesan tersebut dipisahkan ke **Dead Letter Queue (DLQ) / Karantina Log Error** agar antrean ribuan event sehat di belakangnya tetap mengalir lancar (*Zero Head-of-Line Blocking*).
* **Di Lapisan Fisik Lapangan:** Sejalan dengan isolasi DLQ di software, truk yang gagal validasi (misal *Overload SOLAS > 34 Ton* di Gate-In atau tagihan Odoo belum lunas di Gate-Out) langsung dipisahkan ke **Jalur U-Turn / Holding Lane** agar tidak memacetkan jalur gerbang utama!

#### 3. 📜 Immutable Audit Trail pada Tabel `yard_events` (Root-Cause Analysis Instan)
* Setiap kejadian sehat maupun anomali terekam secara kronologis di tabel **`yard_events`** (`id`, `event_type`, `container_number`, `equipment_id`, `from_block/bay/row/tier`, `to_block/bay/row/tier`, `operator_name`, `billable_amount`, `notes`, `created_at`).
* Memudahkan tim QA dan auditor Bea Cukai melacak *root-cause analysis* dalam hitungan detik pada halaman Beranda, Billing, dan Simulator.

### 2. Skrip Pembicara (Speaker Notes)
> *"Prinsip berikutnya dari materi Bapak Dr. Tigor adalah: **'Pisahkan error. Pertahankan alur utama. Batas retry melindungi throughput, sementara DLQ dan audit trail mempercepat root-cause analysis tanpa menghentikan transaksi sehat.'**  
>
> Coba bayangkan jika ada 100 truk mengantre masuk ke CIDP, lalu truk nomor 3 mengirimkan data nomor kontainer yang rusak atau kelebihan muatan. Jika sistem tidak memisahkan error tersebut, maka 97 truk sehat di belakangnya akan ikut macet total!  
> Di proyek CIDP, pemisahan error ini terjadi di dua dimensi sekaligus:  
> 1. **Secara Software (ACID Rollback & Dead Letter Queue):** Di `api/ingest_event.php` dan `api/simulator_action.php`, transaksi yang cacat langsung di-*rollback* secara bersih oleh PDO MySQL dan dimasukkan ke log karantina/DLQ, sehingga worker API tetap bebas melayani transaksi berikutnya dengan latensi 85 milidetik.  
> 2. **Secara Fisik & Audit (`yard_events`):** Truk yang bermasalah otomatis diarahkan oleh layar LED VMS ke *Jalur U-Turn* atau *Holding Lane*, sementara seluruh jejak kronologisnya tercatat permanen di tabel `yard_events` untuk *root-cause analysis*."*

---

```
========================================================================================
[SLIDE 11] EFISIENSI YANG TERASA DI SELURUH OPERASI & MITIGASI IMPLEMENTASI BERTAHAP
========================================================================================
```

### 1. Visual Slide
* **Judul:** Efisiensi yang Terasa di Seluruh Operasi & Mitigasi Implementasi Bertahap
* **Sub-Judul:** Dampak Kuantitatif Integrasi Data End-to-End terhadap Kinerja Operasional & Finansial CIDP 35 Ha
* **4 Dampak Efisiensi Nyata di Proyek CIDP (Sesuai Matriks Materi Dr. Tigor Franky):**

| Indikator Materi Dr. Tigor | Metrik Sebelum Integrasi (Manual/Silo) | Metrik Sesudah Integrasi Data CIDP DPMS | Bukti Modul pada Aplikasi CIDP Saat Ini |
| :--- | :--- | :--- | :--- |
| **`−` ERROR & DUPLIKASI** | • Salah ketik nomor kontainer 4–7%<br>• Kontainer terselip di yard (*lost box*)<br>• Kebocoran tagihan (*revenue leakage*) | • **0% Re-Entry Error** (Validasi OCR Modulo 11)<br>• **0% Salah Letak Box** (DGPS RTK `<2 cm`)<br>• **0% Revenue Leakage** (Interlock Gate-Out) | `pages/scanner.php`<br>`pages/yard.php`<br>`pages/billing.php` |
| **`→` PENGIRIMAN LEBIH CEPAT** | • Waktu proses Gate-In: **12–15 Menit**<br>• Waktu tunggu SPPB manual: **1–2 Hari**<br>• Dwell Time rata-rata: **4,5–6,8 Hari** | • Siklus Gate-In Otomatis: **28–90 Detik (-85%)**<br>• Rilis SPPB Jalur Hijau H2H: **< 5 Menit**<br>• Siklus Langsir KTT Internal: **8,2 Menit**<br>• Average Dwell Time: **1,8 – 2,3 Hari** | `pages/gate.php`<br>`pages/trucking.php`<br>`pages/customs.php` |
| **`↑` KEPUTUSAN REAL-TIME** | • Laporan okupansi yard baru diketahui keesokan harinya (H+1).<br>• Sulit mendeteksi reefer mati listrik. | • Streaming Digital Twin **500 ms** (`YOR 68,4%`).<br>• Alarm suhu Reefer `< 3 detik` + Failover Genset.<br>• Pantauan akrual kas harian live di Dashboard. | `dashboard.php`<br>`pages/digital_twin.php`<br>`pages/reefer.php` |
| **`↑` KEPUASAN & DAYA SAING** | • Shipper komplain karena pungli, keterlambatan, dan tagihan tidak transparan. | • Kapasitas **150.000 TEU/thn** (60 TEU/trip KA).<br>• Gross Revenue **Rp 76,02 Miliar/thn** (EBITDA 67,4%).<br>• Kepatuhan penuh **SOLAS VGM, GS1, & CEISA 4.0**. | `pages/intermodal.php`<br>`report_executive.php` |

* **Strategi Implementasi Bertahap, Risiko Terkendali (MITIGASI: *Pilot Project • Evaluasi Berkala • Manajemen Risiko*):**
  1. **Tahap 1 — Pilot Project & Virtual IoT Sandbox (Status Saat Ini: 100% Selesai):**
     Pengujian kontrak JSON payload, OAuth 2.0 token validation, simulasi 6-langkah `api/edge_simulator.php`, dan visualisasi 2D/3D Digital Twin pada 1 jalur Gate dan Blok A–E tanpa mengganggu operasi eksisting.
  2. **Tahap 2 — Parallel Run & Stress Testing (Evaluasi Berkala):**
     Menjalankan sinkronisasi paralel antara pencatatan lapangan dan **Live Cloud ERP Odoo (`api/odoo_client.php`)** serta pengujian skenario anomali/DLQ (QA Pass 100%, latensi rata-rata 85 ms).
  3. **Tahap 3 — Full Cut-Over & Manajemen Risiko Operasional:**
     Aktivasi penuh 26 sensor hardware lapangan di lahan 35 Ha dengan dukungan *fallback* lokal (`cache/twin_state.json`), Genset ATS 1.250 kVA, dan protokol *Exponential Backoff*.

### 2. Skrip Pembicara (Speaker Notes)
> *"Ketika arsitektur integrasi data ini berjalan secara utuh, dampaknya langsung terasa di seluruh lini operasi CIDP 35 Hektar:  
> 1. **Penurunan drastis Error dan Duplikasi (`−`)**: kesalahan input nomor kontainer turun menjadi nol persen berkat validasi matematis Modulo 11 pada kamera OCR, salah taruh kontainer hilang berkat DGPS RTK berakurasi sentimeter, dan kebocoran tagihan menjadi nol persen berkat interlock otomatis dengan ERP Odoo.  
> 2. **Pengiriman Jauh Lebih Cepat (`→`)**: waktu transaksi truk di gerbang terpangkas dari 15 menit menjadi di bawah 90 detik, siklus langsir internal menggunakan Terminal Tractor `KTT-01` hanya 8,2 menit, dan rata-rata waktu inap (*dwell time*) berhasil ditekan ke angka 1,8 hingga 2,3 hari.  
> 3. **Keputusan Real-Time (`↑`)** dan **Daya Saing Tinggi (`↑`)**: direksi dapat memantau okupansi lapangan dan pendapatan harian secara live detik-per-detik.  
>
> Untuk memastikan risiko terkendali, kami menerapkan strategi **Mitigasi Implementasi Bertahap**: dimulai dari *Pilot Project* menggunakan *Virtual IoT Sandbox* (`api/edge_simulator.php`) yang sudah berjalan saat ini, dilanjutkan * evaluasi berkala* melalui sinkronisasi live ke server Odoo Cloud, hingga manajemen risiko *failover* saat *go-live* penuh."*

---

```
========================================================================================
[SLIDE 12] PENUTUP, SKRIP LIVE DEMO INTEGRASI DATA, & BANK Q&A PERTAHANAN DOSEN
========================================================================================
```

### 1. Visual Slide
* **Judul:** Satu Data. Satu Aliran. Tanpa Re-Entry.
* **Sub-Judul:** *"Arsitektur integrasi end-to-end adalah fondasi efisiensi operasional real-time di ekosistem logistik digital."*
* **Urutan Pembuktian Live Demo Integrasi Data (3 Menit di Aplikasi Web CIDP):**
  1. **Demo 1 — E2E Data Handshake & Streaming SSE Real-Time (`pages/digital_twin.php` & `api/edge_simulator.php`):**
     * Buka halaman **Digital Twin 2D (`dashboard.php?page=digital_twin`)** atau **Simulator 3D (`pages/simulator.php`)**.
     * Klik tombol eksekusi siklus **`auto_cycle` / Step 1 s/d Step 6** (`TRUCK_GATE_ARRIVAL` $\rightarrow$ `TRUCK_WEIGHBRIDGE` $\rightarrow$ `CONTAINER_LIFT` $\rightarrow$ `CONTAINER_STACK` $\rightarrow$ `CUSTOMS_CEISA` $\rightarrow$ `PAYMENT_SETTLED`).
     * Tunjukkan bagaimana **1 tembakan API** di `api/ingest_event.php` langsung memperbarui posisi fisik di peta, mencatat log informasi di `yard_events`, dan menambah saldo akrual rupiah secara *real-time* via `api/stream_twin.php` tanpa me-*refresh* browser!
  2. **Demo 2 — Validasi Kontrak Data & Check Digit Otomatis (`pages/scanner.php` & `pages/gate.php`):**
     * Buka halaman **Scanner Optik (`dashboard.php?page=scanner`)**, uji kontainer `MSKU9182374` dan barcode palet GS1 SSCC-18. Tunjukkan bagaimana payload yang tidak sesuai kontrak Check Digit Modulo 11 langsung ditolak sebelum masuk database.
  3. **Demo 3 — Integrasi Cloud ERP Odoo Live via JSON-RPC (`pages/billing.php` & `api/odoo_client.php`):**
     * Buka halaman **Billing & Keuangan (`dashboard.php?page=billing`)**, klik tombol **Test Connection / Sync Live Odoo**.
     * Tunjukkan bukti nyata JSON Request & Response yang ditarik langsung dari server **`https://conclusion-intermodal-dry-port.odoo.com`** (`account.move`, `product.product`, `res.partner`).

---

### 🛡️ BANK PERTANYAAN KRITIS DOSEN PENGUJI & JAWABAN EMAS (KHUSUS TOPIK DATA INTEGRATION & OAUTH 2.0)

#### Q1: "Mengapa kalian memilih menggunakan OAuth 2.0 untuk mengamankan payload di proyek Dry Port ini, bukan sekadar API Key statis biasa?"
> **Jawaban Emas:**  
> *"Terima kasih Bapak Dr. Tigor. Penggunaan API Key statis sangat berbahaya untuk infrastruktur sekelas Dry Port 35 Hektar karena API Key tidak memiliki batas waktu kedaluwarsa otomatis dan tidak membawa informasi hak akses spesifik di dalamnya. Jika satu sensor di lapangan bocor kunci statisnya, penyerang bisa memanipulasi seluruh sistem.  
> Dengan **OAuth 2.0 (Client Credentials Grant)** yang menghasilkan **Bearer Token berformat JWT**:  
> 1. Token bersifat **Short-Lived** (hanya berlaku 3600 detik / 1 jam), sehingga risiko kebocoran sangat minimal.  
> 2. Token memiliki **Granular Scope** (pembatasan wewenang): misalnya perangkat timbangan `cidp-edge-gate-01` hanya diberi scope `ingest:weighbridge`—ia secara kriptografis ditolak jika mencoba mengirim event pelunasan tagihan `PAYMENT_SETTLED`.  
> 3. Dikombinasikan dengan signature **HMAC-SHA256**, kita mendapatkan *Cryptographic Trust* penuh bahwa payload tidak dimanipulasi di tengah jalan."*

#### Q2: "Tunjukkan di bagian mana dari aplikasi kalian konsep 'Zero Re-Entry Data' dan 'End-to-End Handshake' benar-benar berjalan!"
> **Jawaban Emas:**  
> *"Dapat dilihat langsung pada berkas **`api/ingest_event.php`** dan **`api/edge_simulator.php`**, Bapak.  
> Ketika truk plat `B 9812 UIX` yang membawa kontainer `MSKU9182374` tiba di gerbang masuk (Step 1: `TRUCK_GATE_ARRIVAL`), kamera ANPR dan OCR menangkap identitas tersebut satu kali saja.  
> Selanjutnya, pada Step 2 (`TRUCK_WEIGHBRIDGE`), Step 3 (`CONTAINER_LIFT` oleh Reach Stacker `RS-02`), Step 4 (`CONTAINER_STACK` ke slot `B-08-03-02`), Step 5 (`CUSTOMS_CEISA`), hingga Step 6 (`PAYMENT_SETTLED`), **tidak ada satu pun form input manual yang diketik oleh manusia**. Setiap tahapan langsung mengeksekusi *handshake* otomatis yang memperbarui 3 aliran sekaligus (`physical`, `information`, dan `financial`) serta langsung menciptakan invoice di tabel `billing_invoices` dan ERP Odoo."*

#### Q3: "Bagaimana arsitektur kalian menangani kondisi jika koneksi internet ke Odoo Cloud atau CEISA 4.0 Bea Cukai tiba-tiba terputus (down) saat jam sibuk?"
> **Jawaban Emas:**  
> *"Sesuai prinsip **'Gagal kirim bukan berarti gagal proses'** pada materi Bapak, kami merancang arsitektur **Decoupled Local-First dengan Atomic Cache dan Exponential Backoff**:  
> 1. Seluruh event dari perangkat lapangan selalu ditulis terlebih dahulu ke database lokal MySQL `cidp_yms` dan file cache atomik `cache/twin_state.json` (menggunakan mekanisme *atomic rename* di `api/twin_state_helper.php`). Dengan demikian, palang gerbang, jembatan timbang, dan derek Reach Stacker di Cikarang tetap beroperasi normal di bawah 100 milidetik meskipun internet luar mati total.  
> 2. Sementara itu, tugas pengiriman data keluar ke `conclusion-intermodal-dry-port.odoo.com` (`api/odoo_client.php`) atau CEISA 4.0 ditempatkan dalam antrean *retry* dengan algoritma **Exponential Backoff** ($2\text{s} \rightarrow 4\text{s} \rightarrow 8\text{s} \rightarrow 16\text{s}$). Begitu jaringan pulih, antrean langsung disinkronkan tanpa kehilangan satu transaksi pun, dan berkat kunci **Idempotency (`event_id`)**, tidak akan terjadi pencatatan ganda."*

---

### 2. Skrip Pembicara Penutup (Speaker Notes)
> *"Bapak Dr. Tigor Franky dan rekan-rekan sekalian,  
> Sebagai kesimpulan dari presentasi bagian **Data Integration** ini: kami telah membuktikan bahwa seluruh prinsip arsitektur integrasi modern—mulai dari penghapusan silo data melalui orkestrasi **Triad DPMS (TMS, YMS, WMS)**, penerapan **4 Prinsip Integrasi (End-to-End Handshake, Zero Re-Entry, Real-Time Control, dan Secure by Design)**, pemilihan **Protokol Multi-Konteks (REST/JSON-RPC, EDI 940/945, dan TCP/IP + SSE)**, pengamanan payload menggunakan **OAuth 2.0 Bearer Token & HMAC Signature**, hingga ketahanan sistem melalui **Idempotency, Exponential Backoff, dan Dead Letter Queue**—telah terimplementasi secara nyata dan konsisten di dalam proyek **Conclusion Intermodal Dry Port 35 Hektar**.  
>
> **Satu data. Satu aliran. Tanpa re-entry.**  
> Terima kasih atas perhatian Bapak Dosen dan rekan-rekan. Kami siap mendemonstrasikan alur integrasi ini secara langsung dan membuka sesi diskusi serta tanya jawab."*
