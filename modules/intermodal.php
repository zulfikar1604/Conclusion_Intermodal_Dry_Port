<?php
// Pastikan file ini di-include dari dashboard.php
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}
require_once __DIR__ . '/../connection.php';

// Ambil data kereta api dan truk dari database
$trains = [];
$trucks = [];
try {
    $stmt = $pdo->query("SELECT * FROM trains ORDER BY created_at DESC");
    $trains = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt2 = $pdo->query("SELECT * FROM trucks ORDER BY id DESC LIMIT 10");
    $trucks = $stmt2->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // fallback data jika query gagal
}
?>
<!-- Modul Intermodal: Truk & Kereta Api Logistik -->
<div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-[#002f5e] flex items-center">
            <i class="fa-solid fa-train-subway text-[#0170b9] mr-3"></i>Integrasi Multimoda: Truk ↔ Kereta Api Logistik
        </h2>
        <p class="text-gray-500 text-sm mt-1">Sinkronisasi data alih muat kargo intermodal (Rail Siding Terminal CIDP & Armada Angkutan Jalan Raya)</p>
    </div>
    <div class="flex gap-2">
        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200">
            <span class="w-2 h-2 rounded-full bg-green-500 mr-2 animate-pulse"></span>Rail Siding Active (2 Rangkaian)
        </span>
    </div>
</div>

<!-- 4 Summary KPI Multimoda -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Rangkaian KA Hari Ini</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">2 KA</p>
            <p class="text-[11px] text-purple-600 font-medium mt-0.5"><i class="fa-solid fa-route mr-1"></i>JKT - SMG - SBY</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-train"></i>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Gerbong Flatcar Terisi</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">14 / 20</p>
            <p class="text-[11px] text-blue-600 font-medium mt-0.5"><i class="fa-solid fa-boxes-stacked mr-1"></i>70% Load Factor</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#0170b9] flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-dolly"></i>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Truk Armada Terdaftar</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">14 Unit</p>
            <p class="text-[11px] text-indigo-600 font-medium mt-0.5"><i class="fa-solid fa-id-card mr-1"></i>UHF RFID Terverifikasi</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-truck-front"></i>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Alih Muat (Throughput)</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">48 TEUs</p>
            <p class="text-[11px] text-emerald-600 font-medium mt-0.5"><i class="fa-solid fa-arrow-trend-up mr-1"></i>+12% vs Minggu Lalu</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-arrows-rotate"></i>
        </div>
    </div>
</div>

<!-- Grid 2 Kolom: Kiri (Kereta Api), Kanan (Truk) -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    
    <!-- KOLOM KIRI: Manajemen Kereta Api Logistik (Rail Siding) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
        <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-train text-purple-600 text-lg"></i>
                <h3 class="font-bold text-gray-800">Moda Kereta Api (Rail Siding)</h3>
            </div>
            <span class="text-xs bg-purple-100 text-purple-700 px-2.5 py-1 rounded-md font-bold">Jalur Rel Ganda Aktif</span>
        </div>
        
        <div class="p-5 space-y-5 flex-1">
            <!-- Rangkaian Aktif Card -->
            <div class="border border-purple-100 rounded-xl p-4 bg-purple-50/30">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <span class="font-mono text-xs font-bold text-purple-700 bg-purple-100 px-2 py-0.5 rounded">KA-LOG-JKT-SMG</span>
                        <h4 class="font-bold text-gray-900 mt-1">KA Logistik Jawa Pantura</h4>
                        <p class="text-xs text-gray-500">Relasi: Jakarta Gudang (JKTG) ➔ Semarang Poncol (SMC) ➔ Kalimas (KLM)</p>
                    </div>
                    <span class="px-2 py-1 rounded text-xs font-bold bg-amber-100 text-amber-700 animate-pulse">
                        <i class="fa-solid fa-circle-notch fa-spin mr-1"></i>Bongkar Muat
                    </span>
                </div>

                <!-- Visualisasi Gerbong Datar (Flatcar Wagon Grid) -->
                <div class="mt-4">
                    <p class="text-xs font-semibold text-gray-600 mb-2 flex justify-between">
                        <span>Rangkaian 10 Gerbong Datar (Flatcar):</span>
                        <span class="text-purple-600">RTG-01 Beroperasi</span>
                    </p>
                    <div class="grid grid-cols-5 gap-2" id="wagon-grid">
                        <div class="bg-gray-800 text-white rounded p-2 text-center text-[11px] font-bold border-2 border-emerald-400" title="Gerbong 1: Terisi MSKU9182374 (40ft)">
                            <div class="text-[9px] text-emerald-400">G-01</div>
                            <i class="fa-solid fa-box text-xs my-0.5"></i>
                            <div class="text-[8px] truncate">MSKU9182374</div>
                        </div>
                        <div class="bg-gray-800 text-white rounded p-2 text-center text-[11px] font-bold border-2 border-emerald-400" title="Gerbong 2: Terisi TCLU8827415 (20ft)">
                            <div class="text-[9px] text-emerald-400">G-02</div>
                            <i class="fa-solid fa-box text-xs my-0.5"></i>
                            <div class="text-[8px] truncate">TCLU8827415</div>
                        </div>
                        <div class="bg-gray-800 text-white rounded p-2 text-center text-[11px] font-bold border-2 border-emerald-400" title="Gerbong 3: Terisi TEMU4819203 (40ft Reefer)">
                            <div class="text-[9px] text-cyan-300">G-03 ❄️</div>
                            <i class="fa-solid fa-box text-xs my-0.5"></i>
                            <div class="text-[8px] truncate">TEMU4819203</div>
                        </div>
                        <div class="bg-gray-800 text-white rounded p-2 text-center text-[11px] font-bold border-2 border-amber-400 bg-amber-900/40" title="Gerbong 4: Proses Loading RTG-01">
                            <div class="text-[9px] text-amber-300">G-04 ⚡</div>
                            <i class="fa-solid fa-arrows-down-to-line text-xs my-0.5 animate-bounce"></i>
                            <div class="text-[8px] truncate">CSQU3054821</div>
                        </div>
                        <div class="bg-gray-100 text-gray-500 rounded p-2 text-center text-[11px] font-bold border border-dashed border-gray-300" title="Gerbong 5: Kosong">
                            <div class="text-[9px]">G-05</div>
                            <i class="fa-regular fa-square text-xs my-0.5"></i>
                            <div class="text-[8px] text-gray-400">KOSONG</div>
                        </div>
                        <div class="bg-gray-100 text-gray-500 rounded p-2 text-center text-[11px] font-bold border border-dashed border-gray-300" title="Gerbong 6: Kosong">
                            <div class="text-[9px]">G-06</div>
                            <i class="fa-regular fa-square text-xs my-0.5"></i>
                            <div class="text-[8px] text-gray-400">KOSONG</div>
                        </div>
                        <div class="bg-gray-800 text-white rounded p-2 text-center text-[11px] font-bold border-2 border-emerald-400" title="Gerbong 7: Terisi FCIU4444444">
                            <div class="text-[9px] text-emerald-400">G-07</div>
                            <i class="fa-solid fa-box text-xs my-0.5"></i>
                            <div class="text-[8px] truncate">FCIU4444444</div>
                        </div>
                        <div class="bg-gray-800 text-white rounded p-2 text-center text-[11px] font-bold border-2 border-emerald-400" title="Gerbong 8: Terisi CMAU5555555 (DG)">
                            <div class="text-[9px] text-red-400">G-08 ⚠️</div>
                            <i class="fa-solid fa-box text-xs my-0.5"></i>
                            <div class="text-[8px] truncate">CMAU5555555</div>
                        </div>
                        <div class="bg-gray-100 text-gray-500 rounded p-2 text-center text-[11px] font-bold border border-dashed border-gray-300" title="Gerbong 9: Kosong">
                            <div class="text-[9px]">G-09</div>
                            <i class="fa-regular fa-square text-xs my-0.5"></i>
                            <div class="text-[8px] text-gray-400">KOSONG</div>
                        </div>
                        <div class="bg-gray-100 text-gray-500 rounded p-2 text-center text-[11px] font-bold border border-dashed border-gray-300" title="Gerbong 10: Kosong">
                            <div class="text-[9px]">G-10</div>
                            <i class="fa-regular fa-square text-xs my-0.5"></i>
                            <div class="text-[8px] text-gray-400">KOSONG</div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi Simulasi Bongkar Muat KA -->
                <div class="mt-4 pt-3 border-t border-purple-200/60 flex flex-wrap gap-2">
                    <button onclick="simulateRailLoad()" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold py-2 px-3 rounded-lg transition-colors flex items-center justify-center">
                        <i class="fa-solid fa-arrow-down-to-bracket mr-1.5"></i>Simulasi Muat ke Gerbong (RTG-01)
                    </button>
                    <button onclick="simulateRailDepart()" class="bg-white border border-purple-300 text-purple-700 hover:bg-purple-50 text-xs font-bold py-2 px-3 rounded-lg transition-colors flex items-center justify-center">
                        <i class="fa-solid fa-paper-plane mr-1.5"></i>Lepas Rangkaian (Depart)
                    </button>
                </div>
            </div>

            <!-- Jadwal Kedatangan KA Logistik Berikutnya -->
            <div class="border border-gray-100 rounded-xl p-4 bg-gray-50">
                <h4 class="font-bold text-xs uppercase tracking-wider text-gray-600 mb-2">Jadwal KA Logistik Berikutnya</h4>
                <div class="space-y-2 text-xs text-gray-700">
                    <div class="flex justify-between items-center bg-white p-2 rounded border border-gray-200">
                        <div>
                            <span class="font-mono font-bold text-purple-700">KA-LOG-SBY-JKT</span>
                            <span class="text-gray-500 ml-2">Asal: Kalimas (Surabaya)</span>
                        </div>
                        <span class="font-bold text-gray-800">Tiba: 18:45 WIB (Tepat Waktu)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: Manajemen Truk Kontainer (Road Fleet) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
        <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-truck text-[#0170b9] text-lg"></i>
                <h3 class="font-bold text-gray-800">Moda Truk Kontainer (Armada Darat)</h3>
            </div>
            <span class="text-xs bg-blue-100 text-[#0170b9] px-2.5 py-1 rounded-md font-bold">RFID Auto-Gate Ready</span>
        </div>

        <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
            <!-- Tabel Armada Truk Aktif -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 border-b border-gray-200">
                            <th class="py-2.5 px-3 font-semibold">No. Plat Truk</th>
                            <th class="py-2.5 px-3 font-semibold">Tag RFID Armada</th>
                            <th class="py-2.5 px-3 font-semibold">Supir / Transporter</th>
                            <th class="py-2.5 px-3 font-semibold">Kontainer</th>
                            <th class="py-2.5 px-3 font-semibold text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="truck-table-body">
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="py-2.5 px-3 font-mono font-bold text-gray-900">B 9481 UEK</td>
                            <td class="py-2.5 px-3 font-mono text-purple-700 text-[10px]">E280117000000001</td>
                            <td class="py-2.5 px-3">
                                <span class="font-semibold text-gray-800">Ahmad Fauzi</span>
                                <span class="block text-[10px] text-gray-400">PT Samudera Logistik</span>
                            </td>
                            <td class="py-2.5 px-3 font-mono font-bold text-blue-700">MSKU9182374</td>
                            <td class="py-2.5 px-3 text-right">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700">In Yard (B-08)</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="py-2.5 px-3 font-mono font-bold text-gray-900">B 7712 SCK</td>
                            <td class="py-2.5 px-3 font-mono text-purple-700 text-[10px]">E280117000000002</td>
                            <td class="py-2.5 px-3">
                                <span class="font-semibold text-gray-800">Dedi Sutrisno</span>
                                <span class="block text-[10px] text-gray-400">PT Trans Mega</span>
                            </td>
                            <td class="py-2.5 px-3 font-mono font-bold text-blue-700">TCLU8827415</td>
                            <td class="py-2.5 px-3 text-right">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">Gate-Out OK</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="py-2.5 px-3 font-mono font-bold text-gray-900">B 9123 POI</td>
                            <td class="py-2.5 px-3 font-mono text-purple-700 text-[10px]">E280117000000003</td>
                            <td class="py-2.5 px-3">
                                <span class="font-semibold text-gray-800">Bambang H.</span>
                                <span class="block text-[10px] text-gray-400">PT Cipta Krida</span>
                            </td>
                            <td class="py-2.5 px-3 font-mono font-bold text-blue-700">CSQU3054821</td>
                            <td class="py-2.5 px-3 text-right">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 animate-pulse">Antrian Gate-In</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="py-2.5 px-3 font-mono font-bold text-gray-900">B 8831 KLM</td>
                            <td class="py-2.5 px-3 font-mono text-purple-700 text-[10px]">E280117000000004</td>
                            <td class="py-2.5 px-3">
                                <span class="font-semibold text-gray-800">Surya Pratama</span>
                                <span class="block text-[10px] text-gray-400">PT Armada Utama</span>
                            </td>
                            <td class="py-2.5 px-3 font-mono font-bold text-cyan-700">TEMU4819203 ❄️</td>
                            <td class="py-2.5 px-3 text-right">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-100 text-cyan-700">Reefer Plug</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Tombol Aksi Simulasi Gate Truk -->
            <div class="pt-3 border-t border-gray-100 flex gap-2">
                <button onclick="simulateTruckGateIn()" class="flex-1 bg-[#0170b9] hover:bg-[#004b87] text-white text-xs font-bold py-2 px-3 rounded-lg transition-colors flex items-center justify-center">
                    <i class="fa-solid fa-truck-ramp-box mr-1.5"></i>Simulasi Truk Baru Masuk (RFID Auto-Scan)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Log Perpindahan Antar Moda (Intermodal Transfer Log) -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-100">
        <h3 class="font-bold text-gray-800 text-sm sm:text-base flex items-center">
            <i class="fa-solid fa-clock-rotate-left text-[#0170b9] mr-2"></i>Log Alih Muat Multimoda Terkini (Rail ↔ Yard ↔ Road)
        </h3>
        <span class="text-xs text-gray-500 font-medium">Auto-synced via EDIFACT CODECO/COARRI</span>
    </div>

    <div class="space-y-3" id="transfer-log-list">
        <div class="p-3 bg-purple-50/60 rounded-lg border border-purple-100 flex items-start justify-between text-xs">
            <div class="flex items-start space-x-3">
                <div class="w-8 h-8 rounded-full bg-purple-600 text-white flex items-center justify-center text-xs mt-0.5">
                    <i class="fa-solid fa-train"></i>
                </div>
                <div>
                    <span class="font-bold text-gray-900">Transfer Rail ➔ Yard: CSQU3054821</span>
                    <p class="text-gray-600 mt-0.5">RTG-01 memindahkan kontainer dari Gerbong KA-LOG-JKT-SMG ke Yard Blok A Bay 04</p>
                    <span class="font-mono text-[10px] text-purple-700 font-bold">RFID Container: E280117000000003 &bull; Operator: Joko Susilo</span>
                </div>
            </div>
            <span class="text-gray-400 font-medium text-[11px]">13:45 WIB</span>
        </div>

        <div class="p-3 bg-blue-50/60 rounded-lg border border-blue-100 flex items-start justify-between text-xs">
            <div class="flex items-start space-x-3">
                <div class="w-8 h-8 rounded-full bg-[#0170b9] text-white flex items-center justify-center text-xs mt-0.5">
                    <i class="fa-solid fa-truck"></i>
                </div>
                <div>
                    <span class="font-bold text-gray-900">Transfer Truck ➔ Yard: MSKU9182374</span>
                    <p class="text-gray-600 mt-0.5">Reach Stacker RS-02 menurunkan kontainer dari Truk B 9481 UEK ke Blok B Bay 08</p>
                    <span class="font-mono text-[10px] text-blue-700 font-bold">VGM: 26.850 kg (Verified) &bull; Lo-Lo Service Billed</span>
                </div>
            </div>
            <span class="text-gray-400 font-medium text-[11px]">14:28 WIB</span>
        </div>

        <div class="p-3 bg-emerald-50/60 rounded-lg border border-emerald-100 flex items-start justify-between text-xs">
            <div class="flex items-start space-x-3">
                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs mt-0.5">
                    <i class="fa-solid fa-arrow-right-arrow-left"></i>
                </div>
                <div>
                    <span class="font-bold text-gray-900">Transfer Yard ➔ Rail: TCLU8827415</span>
                    <p class="text-gray-600 mt-0.5">Reach Stacker RS-01 memuat kontainer dari Blok C ke Gerbong G-02 KA Logistik</p>
                    <span class="font-mono text-[10px] text-emerald-700 font-bold">Tujuan: Pelabuhan Tanjung Perak &bull; Status: Export Ready</span>
                </div>
            </div>
            <span class="text-gray-400 font-medium text-[11px]">13:30 WIB</span>
        </div>
    </div>
</div>

<script>
function simulateRailLoad() {
    const list = document.getElementById('transfer-log-list');
    const newLog = document.createElement('div');
    newLog.className = 'p-3 bg-purple-50 rounded-lg border border-purple-200 flex items-start justify-between text-xs animate-pulse';
    newLog.innerHTML = `
        <div class="flex items-start space-x-3">
            <div class="w-8 h-8 rounded-full bg-purple-600 text-white flex items-center justify-center text-xs mt-0.5">
                <i class="fa-solid fa-dolly"></i>
            </div>
            <div>
                <span class="font-bold text-gray-900">Simulasi Alih Muat Sukses (RTG-01)</span>
                <p class="text-gray-600 mt-0.5">Kontainer dimuat ke Gerbong Datar G-05 KA Logistik. Posisi RFID tervalidasi.</p>
                <span class="font-mono text-[10px] text-purple-700 font-bold">Waktu: ` + new Date().toLocaleTimeString('id-ID') + ` &bull; EDIFACT COARRI Generated</span>
            </div>
        </div>
        <span class="text-purple-600 font-bold text-[11px]">Baru saja</span>
    `;
    list.insertBefore(newLog, list.firstChild);
    alert('Simulasi Alih Muat: RTG-01 berhasil memuat kontainer ke Gerbong Datar KA!');
}

function simulateRailDepart() {
    alert('Status Rangkaian KA-LOG-JKT-SMG diubah: Berangkat menuju Stasiun Tujuan (Surabaya Kalimas)!');
}

function simulateTruckGateIn() {
    const tbody = document.getElementById('truck-table-body');
    const plates = ['B 9912 WQ', 'B 3341 YU', 'B 8821 PP', 'B 1102 QA'];
    const drivers = ['Sandi Kurnia', 'Ilham Prasetyo', 'Agus Salim', 'Wahyu Hidayat'];
    const p = plates[Math.floor(Math.random() * plates.length)];
    const d = drivers[Math.floor(Math.random() * drivers.length)];
    const rfid = 'E280117000' + Math.floor(100000 + Math.random() * 899999);
    const cont = 'MSKU' + Math.floor(1000000 + Math.random() * 8999999);

    const tr = document.createElement('tr');
    tr.className = 'hover:bg-blue-50/30 transition bg-green-50/50';
    tr.innerHTML = `
        <td class="py-2.5 px-3 font-mono font-bold text-gray-900">${p}</td>
        <td class="py-2.5 px-3 font-mono text-purple-700 text-[10px]">${rfid}</td>
        <td class="py-2.5 px-3">
            <span class="font-semibold text-gray-800">${d}</span>
            <span class="block text-[10px] text-gray-400">PT Mitra Logistik</span>
        </td>
        <td class="py-2.5 px-3 font-mono font-bold text-blue-700">${cont}</td>
        <td class="py-2.5 px-3 text-right">
            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 animate-pulse">Auto-Scan RFID</span>
        </td>
    `;
    tbody.insertBefore(tr, tbody.firstChild);
    alert('Truk baru terdeteksi di portal OCR/RFID Gate: Plat ' + p + ' dengan Kontainer ' + cont + '!');
}
</script>
