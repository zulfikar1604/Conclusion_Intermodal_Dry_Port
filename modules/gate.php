<?php
// Pastikan file ini di-include dari dashboard.php
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}
require_once __DIR__ . '/../connection.php';
?>
<!-- Modul Manajemen Gate (Auto-Gate System & RFID Scanner) -->
<div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-[#002f5e] flex items-center">
            <i class="fa-solid fa-door-open text-[#0170b9] mr-3"></i>Portal Otomatisasi Gerbang (Gate Automation System)
        </h2>
        <p class="text-gray-500 text-sm mt-1">Instrumentasi Layer 1: Kamera ANPR, OCR Kontainer ISO 6346, Jembatan Timbang VGM & RFID Reader</p>
    </div>
    <div class="flex gap-2">
        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-blue-100 text-[#0170b9] border border-blue-200">
            <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>Portal Gate 01 & 02 Online
        </span>
    </div>
</div>

<!-- 4 Status Gate Card -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Truk Masuk (Gate-In)</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">9 Truk</p>
            <p class="text-[11px] text-blue-600 font-medium mt-0.5"><i class="fa-solid fa-camera mr-1"></i>OCR Akurasi 99.4%</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#0170b9] flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-right-to-bracket"></i>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Truk Keluar (Gate-Out)</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">5 Truk</p>
            <p class="text-[11px] text-indigo-600 font-medium mt-0.5"><i class="fa-solid fa-receipt mr-1"></i>Settlement Lunas</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-right-from-bracket"></i>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Antrian Gerbang</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">4 Unit</p>
            <p class="text-[11px] text-amber-600 font-medium mt-0.5"><i class="fa-solid fa-hourglass-half mr-1"></i>Avg Waktu 2.1 mnt</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-truck-ramp-box"></i>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Sensor Timbangan VGM</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">80 Ton</p>
            <p class="text-[11px] text-emerald-600 font-medium mt-0.5"><i class="fa-solid fa-certificate mr-1"></i>OIML R60 Certified</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-weight-scale"></i>
        </div>
    </div>
</div>

<!-- Grid 2 Kolom: Kiri (Simulasi Gate-In Form), Kanan (Live Feed & Audit Trail) -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    
    <!-- KOLOM KIRI: Simulator Akuisisi Data Gerbang Otomatis (Layer 1 Sensing) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-center pb-3 border-b border-gray-100 mb-4">
                <h3 class="font-bold text-gray-800 flex items-center">
                    <i class="fa-solid fa-microchip text-[#0170b9] mr-2"></i>Simulasi Akuisisi Data Sensor Gate-In
                </h3>
                <span class="text-[11px] bg-blue-50 text-[#0170b9] border border-blue-200 px-2 py-0.5 rounded font-bold">Live Emulation</span>
            </div>
            
            <p class="text-xs text-gray-500 mb-4">
                Uji coba deteksi otomatis kamera ANPR, pembaca RFID UHF (860-960 MHz), OCR kontainer 11 digit, dan jembatan timbang Verified Gross Mass (VGM):
            </p>

            <form id="form-gate-in" onsubmit="handleGateInSubmit(event)" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            <i class="fa-solid fa-camera mr-1 text-blue-500"></i>Kamera ANPR (Plat Nomor)
                        </label>
                        <input type="text" id="gate-plate" value="B 9481 UEK" required
                            class="w-full text-xs font-mono font-bold uppercase bg-gray-50 border border-gray-200 rounded-lg p-2.5 focus:bg-white focus:ring-2 focus:ring-[#0170b9] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            <i class="fa-solid fa-barcode mr-1 text-purple-500"></i>Kamera OCR Kontainer (ISO 6346)
                        </label>
                        <input type="text" id="gate-container" value="MSKU9182374" required
                            class="w-full text-xs font-mono font-bold uppercase bg-gray-50 border border-gray-200 rounded-lg p-2.5 focus:bg-white focus:ring-2 focus:ring-[#0170b9] outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            <i class="fa-solid fa-ruler mr-1 text-gray-500"></i>Ukuran / Tipe
                        </label>
                        <select id="gate-size" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-lg p-2.5 outline-none font-medium">
                            <option value="40FT HIGH CUBE">40FT High Cube (45G1)</option>
                            <option value="20FT STANDARD">20FT Standard (22G1)</option>
                            <option value="40FT REEFER">40FT Reefer (45R1)</option>
                            <option value="20FT DG">20FT DG Class 3</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            <i class="fa-solid fa-weight-scale mr-1 text-emerald-500"></i>Berat Kotor VGM (Kg)
                        </label>
                        <input type="number" id="gate-weight" value="26850" required
                            class="w-full text-xs font-mono font-bold bg-gray-50 border border-gray-200 rounded-lg p-2.5 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            <i class="fa-solid fa-shield-halved mr-1 text-indigo-500"></i>Status CEISA / SPPB
                        </label>
                        <select id="gate-customs" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-lg p-2.5 outline-none font-bold text-green-700">
                            <option value="SPPB_CLEARED">SPPB Cleared (Hijau)</option>
                            <option value="JALUR_MERAH">Pemeriksaan Fisik (Merah)</option>
                            <option value="EMPTY">Depo Kosong (Empty)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            <i class="fa-solid fa-tower-broadcast mr-1 text-purple-600"></i>Tag RFID Armada Truk
                        </label>
                        <input type="text" id="gate-rfid-truck" value="E28011700000020875A49B12"
                            class="w-full text-xs font-mono text-purple-700 bg-purple-50/50 border border-purple-200 rounded-lg p-2.5 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            <i class="fa-solid fa-tag mr-1 text-blue-600"></i>Kode SSCC-18 (GS1 e-Label)
                        </label>
                        <input type="text" id="gate-sscc" value="(00)389912345000000128"
                            class="w-full text-xs font-mono text-blue-700 bg-blue-50/50 border border-blue-200 rounded-lg p-2.5 outline-none">
                    </div>
                </div>

                <!-- Tombol Submit Gate-In -->
                <button type="submit" class="w-full bg-[#0170b9] hover:bg-[#004b87] text-white font-bold py-2.5 px-4 rounded-lg transition-colors text-xs flex items-center justify-center shadow-md">
                    <i class="fa-solid fa-network-wired mr-2"></i>Trigger Gate-In Event (Kirim JSON Payload Layer 4)
                </button>
            </form>
        </div>

        <!-- JSON Payload Preview Box -->
        <div class="mt-4 pt-3 border-t border-gray-100">
            <div class="flex justify-between items-center mb-1">
                <span class="text-[10px] uppercase font-bold text-gray-400">Payload JSON Outgoing (Node-RED / ERP API):</span>
                <span class="text-[10px] text-emerald-600 font-bold"><i class="fa-solid fa-check mr-1"></i>RESTful Schema Valid</span>
            </div>
            <pre id="payload-preview" class="bg-gray-900 text-emerald-400 p-3 rounded-lg text-[10px] font-mono overflow-x-auto max-h-32 leading-relaxed">
{
  "event_type": "TRUCK_GATE_IN",
  "gate_lane": "LANE-IN-01",
  "license_plate": "B 9481 UEK",
  "container_number": "MSKU9182374",
  "gross_weight_kg": 26850,
  "vgm_status": "VERIFIED",
  "customs_status": "SPPB_CLEARED",
  "assigned_slot": "BLOCK-B/Bay-08/Row-03/Tier-02"
}</pre>
        </div>
    </div>

    <!-- KOLOM KANAN: Live CCTV Simulasi & Log Transaksi Gerbang -->
    <div class="space-y-6">
        <!-- Panel Gerbang Visual Status -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-bold text-gray-800 text-sm mb-3 flex items-center">
                <i class="fa-solid fa-video text-red-500 mr-2"></i>Status Portal Gerbang & Barrier Gate (Real-Time)
            </h3>
            
            <div class="grid grid-cols-2 gap-3">
                <div class="border border-gray-200 rounded-lg p-3 bg-gray-50 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase text-gray-400 block">Lane 01 (Inbound)</span>
                        <span class="font-bold text-gray-800 text-xs">Palang Terbuka (0.9s)</span>
                        <span class="text-[10px] text-emerald-600 block"><i class="fa-solid fa-circle text-[7px] mr-1"></i>Truk Lewat</span>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-shield-check"></i>
                    </div>
                </div>
                <div class="border border-gray-200 rounded-lg p-3 bg-gray-50 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase text-gray-400 block">Lane 02 (Outbound)</span>
                        <span class="font-bold text-gray-800 text-xs">Palang Tertutup</span>
                        <span class="text-[10px] text-blue-600 block"><i class="fa-solid fa-circle text-[7px] mr-1"></i>Siap Pindai</span>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-barcode"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Log Aktivitas Gate Terbaru -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-bold text-gray-800 text-sm mb-3 flex items-center justify-between">
                <span><i class="fa-solid fa-list-check text-[#0170b9] mr-2"></i>Audit Trail Transaksi Gerbang</span>
                <span class="text-[11px] text-gray-400 font-normal">Auto-logged</span>
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 border-b border-gray-200">
                            <th class="py-2 px-2.5">Waktu</th>
                            <th class="py-2 px-2.5">Aksi</th>
                            <th class="py-2 px-2.5">Plat Truk</th>
                            <th class="py-2 px-2.5">Kontainer</th>
                            <th class="py-2 px-2.5 text-right">VGM</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="gate-log-body">
                        <tr>
                            <td class="py-2 px-2.5 text-gray-400">14:32</td>
                            <td class="py-2 px-2.5"><span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">Gate-In</span></td>
                            <td class="py-2 px-2.5 font-mono font-bold">B 9481 UEK</td>
                            <td class="py-2 px-2.5 font-mono text-[#0170b9]">MSKU9182374</td>
                            <td class="py-2 px-2.5 text-right font-medium text-emerald-600">26.850 kg ✓</td>
                        </tr>
                        <tr>
                            <td class="py-2 px-2.5 text-gray-400">13:55</td>
                            <td class="py-2 px-2.5"><span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700">Gate-Out</span></td>
                            <td class="py-2 px-2.5 font-mono font-bold">B 7712 SCK</td>
                            <td class="py-2 px-2.5 font-mono text-[#0170b9]">TCLU8827415</td>
                            <td class="py-2 px-2.5 text-right font-medium text-gray-500">Tare: 3.800 kg</td>
                        </tr>
                        <tr>
                            <td class="py-2 px-2.5 text-gray-400">13:15</td>
                            <td class="py-2 px-2.5"><span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">Gate-In</span></td>
                            <td class="py-2 px-2.5 font-mono font-bold">B 1234 XYZ</td>
                            <td class="py-2 px-2.5 font-mono text-[#0170b9]">CSQU3054821</td>
                            <td class="py-2 px-2.5 text-right font-medium text-emerald-600">21.400 kg ✓</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function handleGateInSubmit(e) {
    e.preventDefault();
    const plate = document.getElementById('gate-plate').value;
    const cont = document.getElementById('gate-container').value;
    const weight = document.getElementById('gate-weight').value;
    const size = document.getElementById('gate-size').value;
    const customs = document.getElementById('gate-customs').value;
    const rfid = document.getElementById('gate-rfid-truck').value;
    const sscc = document.getElementById('gate-sscc').value;

    const payload = {
        "event_id": "EVT-IN-" + Date.now(),
        "event_type": "TRUCK_GATE_IN",
        "timestamp": new Date().toISOString(),
        "gate_lane": "LANE-IN-01",
        "truck_data": {
            "license_plate": plate,
            "rfid_tag": rfid,
            "gross_weight_kg": parseFloat(weight)
        },
        "container_data": {
            "container_number": cont,
            "size_type": size,
            "sscc_code": sscc,
            "vgm_status": "VERIFIED",
            "customs_status": customs
        },
        "assigned_slot": {
            "block": "BLOCK-B",
            "bay": "08",
            "row": "03",
            "tier": "02"
        }
    };

    document.getElementById('payload-preview').innerText = JSON.stringify(payload, null, 2);

    // Tambahkan row baru ke log
    const tbody = document.getElementById('gate-log-body');
    const tr = document.createElement('tr');
    tr.className = 'bg-blue-50/50';
    tr.innerHTML = `
        <td class="py-2 px-2.5 text-blue-600 font-bold">Baru saja</td>
        <td class="py-2 px-2.5"><span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">Gate-In</span></td>
        <td class="py-2 px-2.5 font-mono font-bold">${plate}</td>
        <td class="py-2 px-2.5 font-mono text-[#0170b9] font-bold">${cont}</td>
        <td class="py-2 px-2.5 text-right font-medium text-emerald-600">${parseFloat(weight).toLocaleString('id-ID')} kg ✓</td>
    `;
    tbody.insertBefore(tr, tbody.firstChild);

    alert("Pindai Gerbang Berhasil!\nPlat: " + plate + "\nKontainer: " + cont + "\nVGM: " + weight + " kg\nSlot Yard Dialokasikan: BLOCK-B / Bay-08 / Row-03 / Tier-02\nPalang Barrier Otomatis Terbuka!");
}
</script>
