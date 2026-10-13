# Pemetaan Fitur Perangkat Lunak Logistik

**Business Software Feature Data Rule Output**

Mengubah Spesifikasi Fitur Software (WMS, TMS, WCS, YMS, ERP) Menjadi Business Requirements & Functional Specs.

**Modul Perkuliahan Perangkat Lunak Logistik**

---

## 1. Mengubah Fitur Menjadi Aturan Bisnis (Business Rules)

### Pendekatan Developer
- Fokus pada struktur koding.
- Implementasi logika di sistem.
- Pembuatan tombol/antarmuka fitur.
- Integrasi teknis antar modul.

### Pendekatan Business Analyst (BA)
- Fokus pada logika bisnis.
- Parameter masukan (input).
- Validasi data dan aturan bisnis.
- Dampak ke operasional.

### Contoh Analisis BA pada Fitur Putaway
**Fitur:** Button “Confirm Putaway”.

**Business Rule:** Sistem wajib memverifikasi apakah berat barang ≤ kapasitas maksimum rak/bin sebelum status lokasi diperbarui di database.

Alur:
1. Button “Confirm Putaway”.
2. Sistem memverifikasi berat barang terhadap kapasitas maksimum rak/bin sebelum pembaruan status.
3. Status lokasi diperbarui di database.

---

## 2. Fitur WMS Inbound

### 2.1 ASN (Advance Shipping Notice) Matching
**Spesifikasi / Business Rule:** Validasi otomatis nomor PO dari ERP. Jika kuantitas fisik > PO, picu status **Over-receipt Alert**.

**Dampak / Status Sistem:** Over-receipt Alert.

### 2.2 Directed Putaway Engine
**Spesifikasi / Business Rule:** Algoritma menentukan lokasi simpan berdasarkan matriks kriteria: dimensi SKU, kategori barang (HazMat), dan suhu ruang.

**Dampak / Status Sistem:** Lokasi simpan optimal sesuai kriteria yang ditetapkan.

### 2.3 Quality Control (QC) & Quarantine Status
**Spesifikasi / Business Rule:** Mengubah status stok secara otomatis menjadi **Blocked** agar tidak teralokasi untuk penjualan sebelum lolos inspeksi.

**Dampak / Status Sistem:** Status stok menjadi Blocked sampai lolos inspeksi.

---

## 3. Fitur WMS Inventory Management

### 3.1 Real-Time Bin Tracking (Aisle-Rack-Shelf-Bin)
**Spesifikasi / Business Rule BA:** Pemetaan struktur hierarki lokasi untuk memastikan tingkat keterlacakan (traceability) hingga 100%.

**Dampak / Status Sistem:** Keterlacakan lokasi hingga 100%.

### 3.2 Automated Stock Rotation (FIFO / FEFO / LIFO)
**Spesifikasi / Business Rule BA:** Mengunci alokasi barang berdasarkan Expiry Date paling dekat (FEFO) saat Pick Task dibuat oleh sistem.

**Dampak / Status Sistem:** Alokasi mengikuti Expiry Date paling dekat saat Pick Task dibuat.

### 3.3 Cycle Counting Workflow
**Spesifikasi / Business Rule BA:** Perhitungan stok berkala tanpa menghentikan operasional gudang (blind counting untuk mencegah kecurangan operator).

**Dampak / Status Sistem:** Perhitungan stok berjalan tanpa menghentikan operasional gudang.

---

## 4. Fitur WMS Outbound

### 4.1 Slotting Optimization (Analisis ABC)
**Spesifikasi / Business Rule BA:** Penataan ulang lokasi simpan secara periodik berdasarkan frekuensi transaksi produk (fast/slow moving).

**Dampak / Status Sistem:** Lokasi simpan lebih optimal dan pencarian lebih efisien.

### 4.2 Picking Strategy Engine (Wave, Batch, Zone)
**Spesifikasi / Business Rule BA:** Penggabungan beberapa Sales Order menjadi satu jalur unduh (Wave) untuk memangkas jarak tempuh operator.

**Dampak / Status Sistem:** Jarak tempuh operator lebih pendek dan throughput meningkat.

### 4.3 Packing & Weight Verification
**Spesifikasi / Business Rule BA:** Pengecekan toleransi selisih berat (± 2%) pada timbangan otomatis sebelum pencetakan label resi.

**Dampak / Status Sistem:** Akurasi pengiriman meningkat dan retur akibat selisih berat berkurang.

---

## 5. Fitur WCS / WES (Hardware Control)

### 5.1 Conveyor Diverter Control
**Spesifikasi / Business Rule BA:** Penerjemahan sinyal pindaian barcode menjadi perintah fisik pusher/diverter untuk mengarahkan paket ke jalur yang benar.

**Dampak / Status Sistem:** Paket diarahkan ke jalur yang benar berdasarkan hasil pindaian barcode.

### 5.2 Tunnel / Mass Scanner Integration (DWS System)
**Spesifikasi / Business Rule BA:** Pencatatan data dimensi (volumetrik), berat (scale), dan pindaian GS1 secara bersamaan dalam kurun waktu < 500 ms.

**Dampak / Status Sistem:** Data dimensi, berat, dan GS1 tercatat secara bersamaan dalam waktu < 500 ms.

### 5.3 AMR Fleet Routing & Navigation
**Spesifikasi / Business Rule BA:** Manajemen rute armada robotik (Goods-to-Person) untuk mencegah kondisi deadlock atau bertabrakan di lorong gudang.

**Dampak / Status Sistem:** Rute armada robotik dikelola untuk mencegah deadlock atau tabrakan.

---

## 6. Fitur TMS (Transportation Management System) dari Perspektif BA

### 6.1 Dynamic Route Optimization Engine
**Spesifikasi / Business Rule BA:** Algoritma pencarian rute terpendek dengan memperhitungkan batasan kemacetan, jam operasional jalan, dan kapasitas muat.

**Dampak / Status Sistem:** Rute terpendek dipilih dengan mempertimbangkan kemacetan, jam operasional jalan, dan kapasitas muat.

### 6.2 3D Load Planning & Consolidation
**Spesifikasi / Business Rule BA:** Pengaturan tata letak kubikasi barang dengan bak truk untuk memaksimalkan faktor keterisian (cubic utilization).

**Dampak / Status Sistem:** Tata letak kubikasi barang diatur untuk memaksimalkan cubic utilization.

### 6.3 Carrier Rate Audit & Settlement
**Spesifikasi / Business Rule BA:** Perhitungan otomatis biaya pengiriman berdasarkan matriks tarif vendor, jarak, dan zona wilayah.

**Dampak / Status Sistem:** Biaya pengiriman dihitung otomatis berdasarkan tarif vendor, jarak, dan zona wilayah.

---

## 7. Fitur YMS (Yard Management System)

### 7.1 Dock Door Scheduling
**Spesifikasi / Business Rule BA:** Penjadwalan time-slot kedatangan truk untuk menghindari penumpukan armada di area pelataran.

**Dampak / Status Sistem:** Kedatangan truk terjadwal untuk menghindari penumpukan armada di area pelataran.

### 7.2 Trailer Position Tracking
**Spesifikasi / Business Rule BA:** Pemetaan visual (Yard Map) status kontainer: Kosong / Terisi / Dalam Proses Unloading.

**Dampak / Status Sistem:** Status dan posisi kontainer terlihat pada Yard Map.

### 7.3 Gate Management & Demurrage Alert
**Spesifikasi / Business Rule BA:** Pencatatan otomatis waktu masuk/keluar di pintu gerbang dan notifikasi peringatan jika truk melebihi batas dwelling time.

**Dampak / Status Sistem:** Peringatan muncul jika truk melebihi batas dwelling time.

---

## 8. Fitur TMS Tracking & Execution

### 8.1 Real-Time GPS Track & Trace
**Spesifikasi / Business Rule BA:** Pemantauan posisi armada secara berkala (pinging rate) serta fitur Geofencing Alert saat armada keluar dari rute resmi.

**Dampak / Status Sistem:** Posisi armada dipantau secara berkala dan peringatan muncul saat armada keluar dari rute resmi.

### 8.2 Driver Mobile App & Digital POD (e-POD)
**Spesifikasi / Business Rule BA:** Modul pengambilan foto bukti serah terima, tanda tangan digital pelanggan, dan koordinat GPS penyerahan.

**Dampak / Status Sistem:** Bukti serah terima, tanda tangan digital, dan koordinat GPS tercatat secara digital.

### 8.3 Exception Alert (Delay / Damage Notice)
**Spesifikasi / Business Rule BA:** Pemicu notifikasi otomatis ke tim Customer Service jika terjadi penundaan perjalanan akibat mogok atau kecelakaan.

**Dampak / Status Sistem:** Tim Customer Service menerima notifikasi otomatis ketika terjadi penundaan akibat mogok atau kecelakaan.

---

## 9. Matriks Integrasi Data Fitur (Data Mapping Matrix)

| Modul Fitur | Data Input (Source) | Proses Validasi Sistem | Data Output (Target) |
|---|---|---|---|
| ASN Receiving (WMS) | PO Data dari ERP | Cek nomor PO & status kuantitas | Stock Status: Received |
| Picking Engine (WMS/WCS) | Order List dari ERP | Cek aturan FEFO & ketersediaan bin | Task Command ke AMR / PDA |
| Mass Scanning (WCS) | Barcode & Weight Sensor | Cek kecocokan manifes order | Signal Diverter & Resi Label |
| Route Planner (TMS) | Alamat Kirim & Dimensi | Kalkulasi algoritma rute tercepat | Manifest Jalan & GPS Route |

---

## 10. Perancangan Fitur Penanganan Anomali (Exception Handling)

### 10.1 No-Read Barcode pada Conveyor (WCS)
**Skenario:** WCS memerintahkan diverter membelokkan paket ke Reject Lane, lalu memicu Alert ke PDA Operator untuk pindaian manual.

### 10.2 Shortage Stok saat Picking (WMS)
**Skenario:** Operator menekan tombol “Item Shortage” pada PDA. WMS otomatis memicu Cycle Count di bin tersebut dan mencari lokasi bin alternatif.

---

## 11. Menyusun Spesifikasi Fungsional (FSD) Fitur

- **User Role & Permission:** Siapa yang berhak mengakses fitur (misalnya Admin, Operator, Driver).
- **Trigger Condition:** Kejadian pemicu jalannya fitur (misalnya pindaian barcode pertama pada Conveyor).
- **Input Fields & Validation Rules:** Batasan tipe data, karakter wajib, dan logika validasi.
- **System Response & Error Handling:** Respons sistem saat data valid maupun saat terjadi kesalahan.

---

## 12. Alur Integrasi Sistem

Contoh alur modul yang ditampilkan:
- **ERP:** Sales Order → **WMS:** Cold-Zone Strategy (melalui REST API).
- **WMS:** Cold-Zone Strategy → **WCS:** AS/RS & Conveyor (melalui Socket).
- **WMS:** Shipment Ready → **TMS:** Cold-Fleet & Temp.
- **TMS:** Dock Assigned → **YMS**.
- **TMS / ERP:** Invoicing.

---

## 13. Alur Integrasi Sistem & Peran Masing-Masing Modul

### ERP — Enterprise Resource Planning
**Fokus:** Manajemen Pesanan & Master Data.

**Peran:** Mengirim data pesanan toko/pelanggan yang mencakup informasi threshold suhu barang, misalnya Chilled 2°C s.d. 8°C atau Frozen -18°C.

### WMS — Warehouse Management System
**Fokus:** Strategi Zona Cold Storage & FEFO.

**Peran:** Memecah Pick Task berdasarkan zona suhu gudang dan mengunci alokasi barang berdasarkan First Expired, First Out (FEFO). WMS juga membatasi waktu picking (Max Exposure Time), agar kualitas produk tidak turun akibat terlalu lama berada di luar area pendingin.

### WCS — Warehouse Control System
**Fokus:** Eksekusi Sistem Otomasi Suhu Dingin.

**Peran:** Mengontrol sistem rak penyimpanan otomatis (AS/RS) di ruang freezer (–18°C) untuk mengeluarkan palet tanpa perlu operator manusia masuk ke area bersuhu ekstrem, lalu mengarahkannya ke jalur Conveyor pendingin.

### YMS — Yard Management System
**Fokus:** Penjadwalan Dock Khusus Cold Chain.

**Peran:** Mengalokasikan pintu loading dock khusus yang memiliki inflatable dock seal (sekat kedap udara) agar suhu dingin dalam gudang tidak terbuang saat truk melakukan loading.

### TMS — Transportation Management System
**Fokus:** Pengiriman Berpendingin & Pelacakan Suhu.

**Peran:** Menjadwalkan truk pendingin (Reefer Truck), mengoptimalkan rute agar es di dalam boks tidak mencair, serta memantau data sensor suhu GPS trailer secara real-time selama perjalanan.

---

## 14. Pemetaan Interaksi Sistem

| Sistem | Fungsi Spesifik Cold Chain | Output / Aksi Nyata |
|---|---|---|
| ERP | Validasi kriteria suhu & exp date | Mengirim data Order & Aturan Kualitas |
| WMS | Optimasi FEFO & kontrol batas waktu luar ruang | Membagi Pick Task per zona (Chilled/Frozen) |
| WCS | Kontrol AS/RS di ruang suhu ekstrem | Mengeluarkan palet via robotik tanpa operator |
| YMS | Pengaturan pintu dock kedap udara | Menjadwalkan Cold Dock Door & Pre-cooling Truck |
| TMS | Rute efisien & Real-time Temp Monitoring | Mencetak manifes rute & mencatat log suhu GPS |
