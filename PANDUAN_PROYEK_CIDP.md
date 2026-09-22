# 📘 PANDUAN MASTER ARSITEKTUR & PENGEMBANGAN: CIDP YMS
### Single Source of Truth (SSOT) — Cetak Biru Proyek Konsultan Logistik
> **WAJIB DIBACA OLEH ANGGOTA TIM & AI ASSISTANT SEBELUM MENULIS KODE:**  
> Dokumen ini adalah panduan resmi yang mengikat seluruh pengembangan prototipe **Conclusion Intermodal Dry Port (CIDP) - Yard Management System (YMS)**. Dokumen ini merangkum visi proyek, alur pengerjaan teknis, integrasi sistem ERP, analisis finansial (CAPEX & OPEX), skema hardware-software berbasis simulasi, dan arsitektur divisi profesional.

---

## 1. 🎯 Identitas & Visi Proyek

* **Nama Entitas:** Conclusion Supply Chain Consultant
* **Institusi Akademik:** Institut Transportasi dan Logistik (ITL) Trisakti — Semester 5
* **Mata Kuliah:** Teknologi dan Perangkat Lunak Logistik
* **Dosen Pengampu:** Dr. Tigor Franky, S.T., M.T.
* **Topik Utama:** **Topik 7 — Inland Container Depot (ICD) & Dry Port Management**
* **Studi Kasus:** Prototype Sistem Manajemen Lapangan Penumpukan (*Yard Management System / YMS*) pada **Conclusion Intermodal Dry Port (CIDP)**.

---

## 2. 📜 Scope of Work Resmi (Materi Kuliah & Panduan Dosen)

Berdasarkan silabus dan lembar penugasan resmi proyek:
> **Scope Wajib:**
> 1. **Yard Management System (YMS):** Manajemen penumpukan kontainer, audit dwell time, dan visualisasi lapangan.
> 2. **Integrasi GPS Reach Stacker:** Pelacakan posisi armada alat berat di area yard secara real-time.
> 3. **RFID Tagging Kontainer:** Identifikasi otomatis kontainer (standar RFID UHF EPC Gen2 / ISO 18000-6C) saat masuk gerbang dan verifikasi di lapangan.
> 4. **Integrasi Data Moda Truk – Kereta Api Logistik:** Konektivitas intermodal antarmoda jalan raya (Gate-In/Out armada truk) dengan jalur rel sepur simpan (*Rail Siding*) KA Logistik.
> 
> **Fokus Utama Simulasi (Kunci Evaluasi Dosen):**
> **"Pembaruan posisi stacking kontainer di area yard saat dipindahkan oleh operator Reach Stacker."**

---

## 3. 👥 Struktur Tim & Matriks Tanggung Jawab

| No | Nama & NIM | Peran Utama | Lingkup Pekerjaan & Tanggung Jawab | Status di Web |
|---|---|---|---|---|
| 1 | **Zulfikar Jafarudin Fatah** | **Lead System Architect (Ketua Tim)** | • Arsitektur sistem web, basis data MySQL, dan auth satu pintu.<br>• Dashboard Eksekutif (Beranda, KPI, Chart Analytics).<br>• Modul mandiri **Pelacakan Kontainer** (`pages/kontainer.php`).<br>• Modul mandiri **Manajemen Trucking** (`pages/trucking.php`).<br>• Modul mandiri **Lokasi Alat Berat GPS** (`pages/alat.php`).<br>• Modul **Simulasi 3D Virtual Terminal** (`pages/simulator.php`). | ✅ **Selesai & Aktif** |
| 2 | **Armansyah Muchtarrom** | **Hardware & Infrastructure Specialist** | • Analisis infrastruktur fisik lapangan terminal 35 Ha.<br>• Spesifikasi teknis sensor telemetri alat angkat (spreader/twistlock).<br>• Jembatan timbang bersertifikasi VGM SOLAS dan otomasi gerbang masuk/keluar. | ⏳ Menunggu Integrasi Hardware |
| 3 | **Afriansayah Ayubi** | **Software & ERP Process Specialist** | • Rekayasa logika proses perangkat lunak dan alur penanganan peti kemas.<br>• Perancangan struktur tarif jasa terminal (Lo-Lo, Lift-On, Storage Dwell, Timbang VGM).<br>• Sinkronisasi peristiwa operasional lapangan ke modul keuangan sistem ERP terpadu. | ⏳ Menunggu Alur ERP |
| 4 | **Juan Gamaliel** | **Data Integration Specialist** | • Integrasi aliran data antarsistem dan pemodelan pertukaran data JSON/API.<br>• Rekonsiliasi basis data waktu-nyata dan sinkronisasi informasi kepabeanan serta pelayaran.<br>• Penyelarasan denah tata letak fisik terminal 35 Ha dan stacking rules. | ⏳ Menunggu Integrasi Data & Denah |
| 5 | **Naufal Andika Heditya** | **Business Analyst & QA Specialist** | • Analisis kebutuhan proses bisnis operasional pelabuhan kering.<br>• Standardisasi mutu identifikasi logistik global (GS1-128, SSCC-18, e-Labeling).<br>• Pengujian penjaminan kualitas (Quality Assurance) dan kepatuhan regulasi pabean. | ⏳ Menunggu Standar Mutu & QA |

---

## 4. 🧭 Roadmap & Tahapan Pengerjaan Alur Sistem (4 Fase Pengerjaan)

Untuk memastikan proyek selesai dengan *proper* dan sistematis, seluruh alur pengerjaan dibagi menjadi 4 fase:

```
[ FASE 1: FONDASI & PELACAKAN MANDIRI ]  ✅ SELESAI
  ├── Single-Door Authentication & Executive Dashboard
  ├── Pelacakan Kontainer End-to-End (Origin ➔ 3D Yard ➔ Destination)
  ├── Manajemen Trucking & Jembatan Timbang VGM
  └── Pemantauan Posisi GPS Alat Berat (RS-01/02/03 & RTG-01)
                    │
                    ▼
[ FASE 2: TATA LETAK TERMINAL & OTOMASI GERBANG ]  ⏳ PROSES (Juan & Armansyah)
  ├── Digitalisasi Sketsa Denah Fisik 35 Ha ke Web
  ├── Stacking Rules & Koordinat 3D Bay-Row-Tier
  └── Spesifikasi Hardware Sensor Gate (Kamera ANPR, OCR, RFID UHF)
                    │
                    ▼
[ FASE 3: INTEGRASI BISNIS ERP, STANDAR DATA & PABEAN ]  ⏳ PROSES (Afriansayah & Naufal)
  ├── Alur Data Komersial YMS ➔ ERP Billing (Lo-Lo, Storage, VGM)
  ├── Standar Labeling Logistik Global GS1-128 / SSCC-18
  └── Integrasi Dokumen Kepabeanan CEISA 4.0 (SPPB Cleared)
                    │
                    ▼
[ FASE 4: VIRTUAL IOT SANDBOX & UJI KELAYAKAN KONSULTAN ]  ⏳ FINALISASI
  ├── Panel Pemicu Simulasi Operasional (Sandbox Pemicu Sensor)
  └── Laporan Feasibility Study Finansial (CAPEX, OPEX, Payback Period)
```

---

## 5. 🔗 Arsitektur Koneksi Sistem YMS ke Pihak ERP (Enterprise Resource Planning)

Salah satu pertanyaan paling penting dari penguji dan klien adalah:  
**"Di mana batas antara YMS dan ERP, serta bagaimana keduanya terhubung?"**

### A. Pembagian Peran (Separation of Concerns)
* **YMS (Yard Management System / TOS Level):** Berfokus pada **eksekusi fisik di lapangan (*shop floor*)**. Mengatur pergerakan fisik truk, pengangkatan kontainer oleh Reach Stacker, alokasi koordinat 3D slot, dan penimbangan kargo.
* **ERP (Enterprise Resource Planning / Corporate Level — misal SAP, Oracle, atau Odoo):** Berfokus pada **keuangan (*Finance & General Ledger*), piutang (*Accounts Receivable*), penagihan (*Billing*), pajak (*e-Faktur*), dan pengadaan (*Procurement*)**.

### B. Mekanisme Komunikasi (Event-Driven Webhook / REST API)
YMS tidak mengurus pembukuan akuntansi, melainkan mengirimkan **Pemicu Tagihan (*Billable Charge Event*)** ke sistem ERP secara otomatis setiap kali ada aktivitas fisik yang selesai:

```
[ KEJADIAN OPERASIONAL DI YMS ]
  1. Truk ditimbang di Gate-In       ➔ Jasa Timbang VGM (Rp 50.000)
  2. RS-02 mengangkat kontainer      ➔ Jasa Lift-Off / Lo-Lo (Rp 250.000)
  3. Kontainer menginap di Yard      ➔ Jasa Storage Progresif (Rp 45.000/hari)
  4. Kontainer Reefer dicolok listrik ➔ Jasa Plugging Reefer (Rp 250.000/shift)
                     │
                     ▼ (HTTP POST / REST API Webhook)
[ TERMINAL TARIFF ENGINE (YMS) ]
  • Menghitung tarif resmi berdasarkan tipe muatan (Dry/Reefer/DG) dan lama inap
                     │
                     ▼ (Payload JSON)
[ ERP CONNECTOR / API GATEWAY ]
  URL: POST https://erp.cidp.ac.id/api/v1/billing/charge-event
  Payload:
  {
    "event_id": "EVT-20260921-9182",
    "customer_id": "CUST-SAMUDERA-01",
    "customer_name": "PT Samudera Logistik Prima",
    "container_number": "MSKU9182374",
    "service_type": "LIFT_OFF_AND_STORAGE",
    "breakdown": [
      { "item": "Jasa Lift-Off 40ft", "amount": 250000 },
      { "item": "Jembatan Timbang VGM SOLAS", "amount": 50000 },
      { "item": "Penumpukan Yard (Dwell 2 Hari - Bebas Masa I)", "amount": 0 }
    ],
    "total_billable": 300000,
    "currency": "IDR",
    "timestamp": "2026-09-21T14:32:00Z"
  }
                     │
                     ▼
[ MODUL FINANCE & ACCOUNTING ERP ]
  • Mencatat Piutang (Accounts Receivable)
  • Menerbitkan e-Invoice / Faktur Pajak Resmi untuk Shipper
```

---

## 6. 💰 Analisis Finansial Dry Port: Titik Masuk Perhitungan CAPEX, OPEX, dan Model Pendapatan

Sebagai konsultan rantai pasok profesional, sistem yang kita tawarkan tidak hanya canggih secara teknis, tetapi juga **layak secara finansial (*financially viable*)**.

### A. Struktur CAPEX (Capital Expenditure / Investasi Awal)
CAPEX adalah seluruh biaya investasi aset tetap untuk membangun infrastruktur CIDP:

| Kategori Aset | Deskripsi Komponen Investasi | Estimasi Anggaran (Konsultan) |
|---|---|---|
| **Lahan & Perkerasan Pavement** | Pengadaan & pemadatan lahan 35 Ha, perkerasan beton *Heavy Duty Rigid Pavement* kapasitas beban gandar 80 Ton untuk manuver Reach Stacker. | Rp 130.000.000.000 |
| **Jalur Rel Siding Intermodal** | Pembangunan 2 sepur simpan rel KA barang (*Double Track* 600 meter), bantalan beton, wesel percabangan ke jalur utama PT KAI. | Rp 35.000.000.000 |
| **Armada Alat Berat** | • 3 Unit Reach Stacker (Kalmar DRG450 @ Rp 9 Miliar = Rp 27 M)<br>• 1 Unit RTG Crane (Konecranes Electric = Rp 28 Miliar). | Rp 55.000.000.000 |
| **Otomasi Gerbang & IoT** | 2 Unit Jembatan Timbang 80 Ton, 4 Kamera ANPR Plat Nomor, 4 Kamera OCR ISO 6346, Portal Antena RFID UHF, Barrier Gate Otomatis. | Rp 4.500.000.000 |
| **Infrastruktur Cold Chain** | Dermaga Reefer Rack 300 colokan listrik (*plugs*), transformator gardu industri 380V, sensor telemetri suhu IoT LoRaWAN. | Rp 6.500.000.000 |
| **Teknologi Informasi & Software** | Lisensi Server Cloud/On-Premise, Pembangunan Software YMS CIDP, Lisensi ERP Finansial, dan Keamanan Jaringan. | Rp 3.000.000.000 |
| **TOTAL ESTIMASI CAPEX** | **Investasi Awal Pembangunan Terminal CIDP 35 Ha** | **± Rp 234.000.000.000** |

---

### B. Struktur OPEX (Operational Expenditure / Biaya Operasional Rutin)
OPEX adalah biaya berkala yang dibutuhkan untuk menjalankan operasi dry port:
1. **Biaya Energi & Utilitas:** Bahan bakar Solar HSD industri untuk armada Reach Stacker (estimasi 15-20 liter/jam operasi) dan tagihan listrik PLN industri gardu 380V untuk RTG & Reefer.
2. **Biaya Tenaga Kerja (Payroll):** Gaji operator Reach Stacker, operator RTG Crane, petugas gerbang & timbangan, teknisi mekanik M&R, staf IT YMS, dan admin billing.
3. **Biaya Perawatan & Suku Cadang (M&R):** Penggantian ban crane *heavy-duty*, pelumas oli hidrolik boom spreader, penggantian twistlock pin, dan kalibrasi tahunan jembatan timbang oleh Badan Metrologi.
4. **Biaya Akses Rel (*Track Access Charge / TAC*):** Biaya sewa jalur rel dan bagi hasil penggunaan lokomotif/gerbong datar dengan PT Kereta Api Logistik (KAI Logistik).

---

### C. Model Pendapatan (Revenue Streams / Tarif Jasa ICD)

| Jenis Jasa Layanan | Besaran Tarif Layanan | Satuan Pengenaan |
|---|---|---|
| **Lift-On / Lift-Off (Lo-Lo)** | Rp 175.000 (20ft) / Rp 275.000 (40ft) | Per Box Kontainer |
| **Penumpukan Lapangan (*Yard Storage*)** | • Hari 1 - 3: **Masa I (Bebas Tarif / Free Dwell)**<br>• Hari 4 - 10: **Masa II (Rp 45.000 / hari)**<br>• Hari >10: **Masa III Progresif (Rp 90.000 / hari)** | Per Box / Hari |
| **Jembatan Timbang VGM SOLAS** | Rp 50.000 | Per Truk |
| **Steker Pendingin Reefer (*Plugging*)** | Rp 250.000 | Per Shift (8 Jam) |
| **CFS Stuffing / Stripping Muatan** | Rp 450.000 | Per Box Kontainer |
| **Bagi Hasil Angkutan KA Logistik** | Rp 1.200.000 - Rp 1.800.000 | Per Gerbong (CIDP - Surabaya) |

---

### D. Di Mana Masuknya Perhitungan Ini dalam Sistem Prototipe Kita?
1. **Di Modul Billing & Faktur (`dashboard.php?page=billing` - Scope Afriansayah):**
   * Menjadi mesin kalkulasi otomatis yang menghitung tagihan per pelanggan berdasarkan catatan event dari tabel `yard_events`.
2. **Di Beranda Eksekutif (`dashboard.php?page=beranda` - Scope Zulfikar):**
   * Ditampilkan pada KPI Card: **Pendapatan Hari Ini (Gross Daily Revenue)** dan rasio penagihan piutang (*Collection Rate*).
3. **Di Dokumen Laporan Akhir Kelompok (Feasibility Study):**
   * Menghitung **Payback Period (PBP)**, **Net Present Value (NPV)**, dan **Internal Rate of Return (IRR)** dengan asumsi throughput 150.000 TEU/tahun untuk meyakinkan dosen penguji bahwa terminal ini menguntungkan secara bisnis.

---

## 7. 🗂️ Arsitektur Navigasi Sidebar: 4 Kluster Divisi Profesional

Untuk mencerminkan arsitektur sistem kelas dunia, menu sidebar dikelompokkan ke dalam **4 Kluster Divisi**:

```markdown
📂 KLUSTER 1: EXECUTIVE & VISUALIZATION
├── 📊 Beranda Eksekutif (KPI, Metrik Kinerja & Finansial) [Aktif - Zulfikar]
└── 🗺️ Denah Terminal 35 Ha (Visual Top-Down Lapangan) [PIC: Juan & Armansyah]

📂 KLUSTER 2: OPERASIONAL UTAMA TERMINAL (CORE YMS)
├── 📦 Pelacakan Kontainer (Posisi 3D, Dwell Time & Rute) [Aktif - Zulfikar]
├── 🚛 Manajemen Trucking (Arus Gerbang, VGM & e-Gate Pass) [Aktif - Zulfikar]
├── 🚜 Lokasi Alat Berat GPS (Telemetri Reach Stacker & RTG) [Aktif - Zulfikar]
├── 🏗️ Manajemen Yard & Stacking (Aturan Bay-Row-Tier) [PIC: Juan]
└── 🚂 Intermodal Rail Siding (Jadwal & Rangkaian KA Logistik) [PIC: Juan & Afriansayah]

📂 KLUSTER 3: VALUE-ADDED SERVICES & KEPABEANAN
├── ❄️ Monitor Reefer (Steker Listrik & Telemetri Dingin IoT) [PIC: Armansyah]
├── 🏛️ Kepabeanan & Bea Cukai (Integrasi SPPB & Jalur Merah/Hijau) [PIC: Naufal & Tim Pabean]
└── 🏷️ Scanner SSCC / GS1 (Standar Barcode & Verifikasi Box) [PIC: Naufal]

📂 KLUSTER 4: KOMERSIAL, SIMULASI & SISTEM
├── 💵 Billing & Faktur (Tarif Lo-Lo, Storage & Integrasi ERP) [PIC: Afriansayah]
├── 🎮 Panel Simulasi 3D Virtual Terminal (Three.js WebGL & Live Aksi) [✅ Selesai & Aktif - Zulfikar & Armansyah]
└── ⚙️ Pengaturan Sistem (Hak Akses & Konfigurasi Basis Data) [PIC: Zulfikar]
```

---

## 8. 🧠 Konsep Hardware-Software: Mengapa Virtual IoT Mocking Sangat Kuat?

Jika dosen bertanya:
> *"Kalian tidak punya sensor RFID dan Reach Stacker asli di kelas, bagaimana membuktikan sistem ini bisa terhubung ke hardware?"*

**Jawaban Resmi Tim:**
> *"Dalam arsitektur industri enterprise, software YMS tidak pernah berkomunikasi langsung dengan sensor fisik kabel, melainkan melalui **IoT Edge Gateway** menggunakan standar **REST API & JSON Payload** (Decoupled Architecture).
>
> Karena hardware fisik masih dalam tahap spesifikasi pengadaan oleh tim infrastruktur, pada fase prototipe ini kami membangun **Virtual IoT Simulation Engine**. Struktur data JSON yang ditembakkan oleh simulator ini **100% identik** dengan data yang akan dikirimkan gateway fisik nantinya. Ketika hardware asli sudah dipasang di lapangan, hardware tinggal menembak endpoint API yang sama tanpa perlu mengubah satu baris pun kode pada sistem YMS kita."*

---

## 9. 🎨 Pedoman Tampilan UI/UX: Bersih & Ringkas (Clean & Spacious)

1. **Prinsip 1 Baris per Item:** Seluruh tabel operasional (Kontainer, Trucking, Alat Berat) wajib tampil ringkas dalam 1 baris bersih. Tidak boleh menumpuk 3-4 baris teks berjejal dalam satu sel tabel.
2. **Klik untuk Milestone Lengkap:** Pengguna yang ingin memeriksa rincian teknis, bobot timbangan, nomor dokumen, maupun kronologi perjalanan cukup **mengeklik baris tabel atau tombol Detail & Milestone** untuk membuka modal interaktif.

---
*Dokumen ini disusun dan disahkan oleh **Lead Project & System Architect (Zulfikar Jafarudin Fatah)** sebagai standar operasional pengembangan Conclusion Intermodal Dry Port (CIDP).*
