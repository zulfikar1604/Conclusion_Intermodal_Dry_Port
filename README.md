# Conclusion Intermodal Dry Port (CIDP) - Yard Management System

> ### ⚠️ PENTING UNTUK ANGGOTA TIM & AI ASSISTANT:
> Sebelum melakukan penambahan kode, konfigurasi modul, atau integrasi hardware/API, **WAJIB MEMBACA** dokumen panduan utama arsitektur proyek:
> 👉 **[PANDUAN_PROYEK_CIDP.md](PANDUAN_PROYEK_CIDP.md)**
> *(Dokumen ini merangkum seluruh Scope of Work resmi, pembagian tugas 5 anggota tim, struktur tabel database, dan konsep integrasi hardware-software berbasis simulasi).*

---

## 📌 Informasi Akademik
* **Institusi:** Institut Transportasi dan Logistik (ITL) Trisakti
* **Mata Kuliah:** Teknologi dan Perangkat Lunak Logistik (Semester 5)
* **Topik Pembahasan:** **Topik 7 — Inland Container Depot (ICD) & Dry Port Management**
* **Kelompok Konsultan:** Conclusion Supply Chain Consultant

### 👥 Anggota Tim & Pembagian Tugas:
Conclusion Grup
---

## 🎯 Scope of Work Resmi
1. **Yard Management System (YMS):** Manajemen slot penumpukan lapangan, dwell time, dan visualisasi aktivitas kargo.
2. **Integrasi GPS Reach Stacker:** Pelacakan armada alat berat di area yard secara real-time.
3. **RFID Tagging Kontainer:** Identifikasi otomatis kontainer (UHF EPC Gen2 / ISO 18000-6C) saat masuk gerbang dan verifikasi di lapangan.
4. **Integrasi Data Moda Truk – Kereta Api Logistik:** Alih moda antarmoda jalan raya dan jalur rel sepur simpan (*Rail Siding*).
5. **Fokus Simulasi (Kunci Penilaian Dosen):** Pembaruan posisi stacking kontainer di area yard saat dipindahkan oleh operator Reach Stacker.

---

## 🚀 Modul Operasional yang Sudah Aktif
1. **Beranda Eksekutif (`dashboard.php?page=beranda`):** 6 KPI summary cards, 7 grafik Chart.js, 2 speedometer gauge (yard occupancy & dwell time), dan log konsinyasi live.
2. **Pelacakan Kontainer & Audit Perjalanan (`dashboard.php?page=kontainer`):** Data kontainer ringkas 1 baris, kode ISO, status SPPB Bea Cukai, posisi 3D yard, dan modal interaktif jejak audit perjalanan 5 tahap.
3. **Manajemen Trucking & Arus Gerbang (`dashboard.php?page=trucking`):** Arus armada truk, gate lane monitoring, data jembatan timbang (Gross, Tare, Net VGM SOLAS), dan modal digital tiket e-Gate Pass.
4. **Lokasi & Status Alat Berat GPS (`dashboard.php?page=alat`):** Peta skematik radar terminal, telemetri live unit RS-01, RS-02, RS-03, RTG-01, dan audit log pemindahan kargo.
5. **Modul Kolaborasi Tim:** Modul `denah`, `gate`, `yard`, `intermodal`, `reefer`, `billing`, `scanner`, `settings` menampilkan kartu status kolaboratif PIC penanggung jawab sambil menunggu input resmi rekan tim.

---

## 🔐 Akses Masuk Sistem (Mode Simulasi Konsultan)
Aplikasi menggunakan sistem **Akses Satu Pintu (*Single-Door Access*)** untuk kemudahan evaluasi dan demonstrasi di depan dosen penguji:
* **URL Login:** `http://localhost/Conclusion_Intermodal_Dry_Port/login.php`
* **Email:** `admin@cidp.ac.id`
* **Password:** `admin123`
* **Fitur Instan:** Tersedia tombol **"1-Klik Masuk sebagai Konsultan"** pada halaman login.

---

## 🛠️ Instalasi & Menjalankan di Lokal
1. Clone repositori ini ke folder `htdocs` instalasi XAMPP:
   ```bash
   git clone https://github.com/zulfikar1604/Conclusion_Intermodal_Dry_Port.git
   ```
2. Pastikan service **Apache** dan **MySQL** sudah berjalan di XAMPP Control Panel.
3. Pastikan database `cidp_yms` sudah aktif di phpMyAdmin.
4. Buka di browser:
   ```
   http://localhost/Conclusion_Intermodal_Dry_Port/
   ```

---
*Dikembangkan oleh Conclusion Supply Chain Consultant — ITL Trisakti.*
