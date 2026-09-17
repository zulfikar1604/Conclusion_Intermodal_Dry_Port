# Conclusion Intermodal Dry Port (CIDP) - Yard Management System

A prototype and academic showcase for **Topic 7: Inland Container Depot (ICD) & Dry Port Management**, developed for the **Teknologi dan Perangkat Lunak Logistik** course at **Institut Transportasi dan Logistik (ITL) Trisakti**.

---

## 📌 Informasi Akademik
- **Program Studi:** Manajemen Logistik / Transportasi Logistik
- **Institusi:** Institut Transportasi dan Logistik (ITL) Trisakti
- **Mata Kuliah:** Teknologi dan Perangkat Lunak Logistik
- **Dosen Pengampu:** Dr. Tigor Franky, S.T., M.T.
- **Topik Pembahasan:** Topik 7 — *Inland Container Depot (ICD) & Dry Port Management*

---
1. Zulfikar Jafarudin Fatah — Lead System Architect (Ketua Tim)
2. Armansyah Muchtarrom — Hardware & Infrastructure Specialist
3. Afriansayah Ayubi — Software & ERP Process Specialist
4. Juan Gamaliel — Data Integration Specialist
5. Naufal Andika Heditya — Business Analyst & QA Specialist


---

## 🚀 Fitur Utama Prototipe
1. **Interactive Showcase & Landing Page:**
   - Profil 6 fasilitas inti terminal (CY, Rail Siding, CFS, M&R, Reefer, OCR Gate).
   - Penjelasan 3 Alur Utama Logistik (Inbound, Yard Processing, Outbound).
2. **Multi-Role Authentication:**
   - Superadmin / Head of Operations
   - Yard Operator (Reach Stacker RS-03)
   - Cargo Owner / Freight Forwarder (Shipper)
   - Fleet Driver (Truk Kontainer)
3. **Standar Identifikasi Logistik Global:**
   - Penerapan GS1 Standards (SSCC-18, GS1-128, QR Code container tracking).
4. **Alur Integrasi Intermodal:**
   - Konektivitas moda rel (Kereta Api Barang) dan moda jalan raya (Truk).

---

## 🛠️ Instalasi & Penggunaan Lokal
1. Clone repositori ini ke folder `htdocs` pada instalasi XAMPP:
   ```bash
   git clone https://github.com/zulfikar1604/Conclusion_Intermodal_Dry_Port.git
   ```
2. Pastikan service **Apache** dan **MySQL** aktif pada XAMPP Control Panel.
3. Buat database `cidp_yms` dan import file `cidp.sql`.
4. Buka browser:
   ```
   http://localhost/Conclusion_Intermodal_Dry_Port
   ```
