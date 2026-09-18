<?php
// Pastikan file ini di-include dari dashboard.php
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}
require_once __DIR__ . '/../connection.php';
?>
<!-- Modul Scanner AIDC & Standar Identifikasi Global GS1 (SSCC-18 & e-Labeling) -->
<div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-[#002f5e] flex items-center">
            <i class="fa-solid fa-barcode text-[#0170b9] mr-3"></i>Simulasi Pemindai AIDC & Standar Global GS1
        </h2>
        <p class="text-gray-500 text-sm mt-1">Implementasi Barcode GS1-128, SSCC-18 (Paspor Unit Logistik), dan e-Labeling Rantai Pasok</p>
    </div>
    <div class="flex gap-2">
        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-blue-100 text-[#0170b9] border border-blue-200">
            <i class="fa-solid fa-qrcode mr-1.5"></i>Standar GS1 Indonesia &bull; Mod-10 Check Digit
        </span>
    </div>
</div>

<!-- 4 KPI AIDC GS1 -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Standar Barcode</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">GS1-128</p>
            <p class="text-[11px] text-blue-600 font-medium mt-0.5"><i class="fa-solid fa-bars mr-1"></i>Linear 1D Barcode</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#0170b9] flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-barcode"></i>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Panjang SSCC</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">18 Digit</p>
            <p class="text-[11px] text-purple-600 font-medium mt-0.5"><i class="fa-solid fa-passport mr-1"></i>Paspor Unit Logistik</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-fingerprint"></i>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Metode Integrasi</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">REST API</p>
            <p class="text-[11px] text-emerald-600 font-medium mt-0.5"><i class="fa-solid fa-bolt mr-1"></i>Payload JSON Terstruktur</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-code"></i>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Kecepatan Verifikasi</p>
            <p class="text-2xl font-bold text-cyan-600 mt-1">&lt; 0.8s</p>
            <p class="text-[11px] text-cyan-600 font-medium mt-0.5"><i class="fa-solid fa-gauge-high mr-1"></i>Single-Scan Receiving</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-bolt-lightning"></i>
        </div>
    </div>
</div>

<!-- Grid Simulator Scanner: Kiri (Virtual Scanner), Kanan (Hasil Parsing e-Label) -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    
    <!-- Kolom Kiri: Virtual Handheld PDA Scanner -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-center pb-3 border-b border-gray-100 mb-4">
                <h3 class="font-bold text-gray-800 flex items-center">
                    <i class="fa-solid fa-mobile-screen-button text-[#0170b9] mr-2"></i>Handheld Industrial PDA Scanner (Zebra / Honeywell)
                </h3>
                <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded font-bold">HID Wedge / Broadcast Intent</span>
            </div>

            <p class="text-xs text-gray-500 mb-4">
                Pilih sampel barcode logistik berstandar GS1 di bawah ini untuk mensimulasikan pemindaian otomatis satu kali (Single-Scan Inbound Receiving):
            </p>

            <!-- Presets Barcode Samples -->
            <div class="space-y-3 mb-4">
                <button type="button" onclick="loadSampleBarcode(1)" class="w-full text-left p-3 rounded-lg border border-gray-200 hover:border-[#0170b9] hover:bg-blue-50/40 transition flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-gray-900 block">Sampel 1: Palet Susu Impor (Cold Chain)</span>
                        <span class="text-[11px] font-mono text-gray-500">(01)08991234567890(10)LOT9988(17)261231(00)389912345000000128</span>
                    </div>
                    <span class="text-xs text-[#0170b9] font-bold"><i class="fa-solid fa-arrow-right"></i></span>
                </button>

                <button type="button" onclick="loadSampleBarcode(2)" class="w-full text-left p-3 rounded-lg border border-gray-200 hover:border-[#0170b9] hover:bg-blue-50/40 transition flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-gray-900 block">Sampel 2: Palet Kargo Kering FMCG (General Cargo)</span>
                        <span class="text-[11px] font-mono text-gray-500">(01)08998877665544(10)BATCH-A2(17)270630(00)389912345000000258</span>
                    </div>
                    <span class="text-xs text-[#0170b9] font-bold"><i class="fa-solid fa-arrow-right"></i></span>
                </button>
            </div>

            <!-- Input Barcode Raw String -->
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                    String Raw Pemindaian Barcode (Input Mesin Pemindai):
                </label>
                <div class="flex gap-2">
                    <input type="text" id="raw-barcode-input" value="(01)08991234567890(10)LOT9988(17)261231(00)389912345000000128"
                        class="w-full text-xs font-mono font-bold bg-gray-50 border border-gray-200 rounded-lg p-2.5 outline-none focus:bg-white focus:ring-2 focus:ring-[#0170b9]">
                    <button onclick="parseBarcode()" class="bg-[#0170b9] hover:bg-[#004b87] text-white px-4 rounded-lg text-xs font-bold transition flex items-center whitespace-nowrap">
                        <i class="fa-solid fa-bolt mr-1.5"></i>Pindai & Parse
                    </button>
                </div>
            </div>
        </div>

        <!-- Visual Barcode Display -->
        <div class="bg-gray-100 p-4 rounded-xl border border-gray-200 text-center">
            <div class="inline-block bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                <!-- Barcode SVG Simulation -->
                <svg class="h-14 w-64 mx-auto" viewBox="0 0 200 60">
                    <rect x="0" y="0" width="3" height="60" fill="black"/>
                    <rect x="6" y="0" width="1" height="60" fill="black"/>
                    <rect x="10" y="0" width="4" height="60" fill="black"/>
                    <rect x="18" y="0" width="2" height="60" fill="black"/>
                    <rect x="24" y="0" width="5" height="60" fill="black"/>
                    <rect x="34" y="0" width="2" height="60" fill="black"/>
                    <rect x="40" y="0" width="1" height="60" fill="black"/>
                    <rect x="44" y="0" width="6" height="60" fill="black"/>
                    <rect x="54" y="0" width="3" height="60" fill="black"/>
                    <rect x="62" y="0" width="2" height="60" fill="black"/>
                    <rect x="68" y="0" width="4" height="60" fill="black"/>
                    <rect x="76" y="0" width="1" height="60" fill="black"/>
                    <rect x="80" y="0" width="5" height="60" fill="black"/>
                    <rect x="90" y="0" width="2" height="60" fill="black"/>
                    <rect x="96" y="0" width="3" height="60" fill="black"/>
                    <rect x="104" y="0" width="5" height="60" fill="black"/>
                    <rect x="114" y="0" width="1" height="60" fill="black"/>
                    <rect x="118" y="0" width="4" height="60" fill="black"/>
                    <rect x="126" y="0" width="2" height="60" fill="black"/>
                    <rect x="132" y="0" width="6" height="60" fill="black"/>
                    <rect x="142" y="0" width="2" height="60" fill="black"/>
                    <rect x="148" y="0" width="4" height="60" fill="black"/>
                    <rect x="156" y="0" width="1" height="60" fill="black"/>
                    <rect x="162" y="0" width="5" height="60" fill="black"/>
                    <rect x="172" y="0" width="2" height="60" fill="black"/>
                    <rect x="178" y="0" width="3" height="60" fill="black"/>
                    <rect x="186" y="0" width="4" height="60" fill="black"/>
                    <rect x="194" y="0" width="2" height="60" fill="black"/>
                </svg>
                <span class="text-[10px] font-mono tracking-widest text-gray-700 block mt-1">(00) 3 8991234 500000012 8</span>
            </div>
            <p class="text-[11px] text-gray-500 mt-2">Format e-Label Logistik GS1: GS1-128 Linear Barcode with Application Identifiers</p>
        </div>
    </div>

    <!-- Kolom Kanan: Hasil Pemisahan Elemen Data (Parser Engine) & JSON Output -->
    <div class="space-y-6">
        <!-- Hasil Parsing Identifikasi GS1 -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-bold text-gray-800 text-sm mb-3 pb-2 border-b border-gray-100 flex items-center">
                <i class="fa-solid fa-list-check text-emerald-600 mr-2"></i>Elemen Data Hasil Dekode Application Identifiers (AI)
            </h3>

            <div class="space-y-3 text-xs">
                <div class="p-3 bg-purple-50 rounded-lg border border-purple-100 flex justify-between items-center">
                    <div>
                        <span class="font-bold text-purple-900 block">AI (00) &bull; SSCC (Serial Shipping Container Code)</span>
                        <span class="text-[11px] text-purple-700 font-mono font-bold" id="res-sscc">389912345000000128</span>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-200 text-purple-800">Paspor Palet (18 Digit)</span>
                </div>

                <div class="p-3 bg-blue-50 rounded-lg border border-blue-100 flex justify-between items-center">
                    <div>
                        <span class="font-bold text-blue-900 block">AI (01) &bull; GTIN (Global Trade Item Number)</span>
                        <span class="text-[11px] text-blue-700 font-mono font-bold" id="res-gtin">08991234567890</span>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-200 text-blue-800">Master Data Produk</span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="font-bold text-gray-800 block text-[11px]">AI (10) &bull; Nomor Batch / Lot</span>
                        <span class="text-xs font-mono font-bold text-gray-900" id="res-batch">LOT9988</span>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="font-bold text-gray-800 block text-[11px]">AI (17) &bull; Tanggal Kedaluwarsa</span>
                        <span class="text-xs font-mono font-bold text-gray-900" id="res-exp">31 Desember 2026</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Output JSON Payload Layer 4 (Sesuai Slide 12 Materi) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex justify-between items-center mb-2 pb-2 border-b border-gray-100">
                <span class="text-xs font-bold text-gray-800 flex items-center">
                    <i class="fa-solid fa-network-wired text-[#0170b9] mr-2"></i>JSON Payload Layer 4 (Simulasi PoC API Slide 12)
                </span>
                <span class="text-[10px] text-emerald-600 font-bold"><i class="fa-solid fa-circle text-[7px] mr-1"></i>200 OK Response</span>
            </div>

            <pre id="json-parsed-payload" class="bg-gray-900 text-emerald-400 p-4 rounded-lg text-xs font-mono overflow-x-auto max-h-48 leading-relaxed">
{
  "event": "INBOUND_SCAN",
  "sscc_code": "(00)389912345000000128",
  "scanned_at": "2026-09-18T14:30:00Z",
  "device_id": "ZEBRA-TC57-GATE01",
  "location_id": "CIDP-GATE-LANE01",
  "items": [
    {
      "gtin": "08991234567890",
      "batch_number": "LOT9988",
      "exp_date": "261231",
      "qty_pallets": 1
    }
  ]
}</pre>
        </div>
    </div>
</div>

<script>
function loadSampleBarcode(num) {
    if(num === 1) {
        document.getElementById('raw-barcode-input').value = '(01)08991234567890(10)LOT9988(17)261231(00)389912345000000128';
    } else {
        document.getElementById('raw-barcode-input').value = '(01)08998877665544(10)BATCH-A2(17)270630(00)389912345000000258';
    }
    parseBarcode();
}

function parseBarcode() {
    const raw = document.getElementById('raw-barcode-input').value;
    
    // Default parsing logic untuk sampel
    let gtin = "08991234567890";
    let batch = "LOT9988";
    let exp = "31 Desember 2026";
    let sscc = "389912345000000128";

    if(raw.includes("08998877665544")) {
        gtin = "08998877665544";
        batch = "BATCH-A2";
        exp = "30 Juni 2027";
        sscc = "389912345000000258";
    }

    document.getElementById('res-sscc').innerText = sscc;
    document.getElementById('res-gtin').innerText = gtin;
    document.getElementById('res-batch').innerText = batch;
    document.getElementById('res-exp').innerText = exp;

    const payload = {
        "event": "INBOUND_SCAN",
        "sscc_code": "(00)" + sscc,
        "scanned_at": new Date().toISOString(),
        "device_id": "ZEBRA-TC57-GATE01",
        "location_id": "CIDP-GATE-LANE01",
        "items": [
            {
                "gtin": gtin,
                "batch_number": batch,
                "exp_date": "YYMMDD",
                "qty_pallets": 1
            }
        ]
    };

    document.getElementById('json-parsed-payload').innerText = JSON.stringify(payload, null, 2);
}
</script>
