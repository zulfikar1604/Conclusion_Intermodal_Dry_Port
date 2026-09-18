<?php
// Pastikan file ini di-include dari dashboard.php
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}
require_once __DIR__ . '/../connection.php';
?>
<!-- Modul Monitor Reefer Cold Chain (IoT Telematika Lapangan) -->
<div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-[#002f5e] flex items-center">
            <i class="fa-solid fa-snowflake text-cyan-500 mr-3"></i>Monitoring Kontainer Berpendingin (Reefer Cold Chain)
        </h2>
        <p class="text-gray-500 text-sm mt-1">Sensor Telemetri IoT Nirkabel LoRaWAN: Pemantauan Suhu Real-Time, Kelembapan & Konsumsi Daya Listrik</p>
    </div>
    <div class="flex gap-2">
        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-cyan-100 text-cyan-800 border border-cyan-200">
            <span class="w-2 h-2 rounded-full bg-cyan-500 mr-2 animate-pulse"></span>60 Plugs Aktif (LoRaWAN Online)
        </span>
    </div>
</div>

<!-- 4 Status Card Reefer -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Reefer Terkoneksi Listrik</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">38 / 60</p>
            <p class="text-[11px] text-cyan-600 font-medium mt-0.5"><i class="fa-solid fa-plug-circle-check mr-1"></i>38 Plugs Beroperasi</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-plug"></i>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Rata-rata Suhu Yard</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">-19.6°C</p>
            <p class="text-[11px] text-emerald-600 font-medium mt-0.5"><i class="fa-solid fa-shield-check mr-1"></i>Dalam Batas Aman</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-temperature-arrow-down"></i>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Peringatan Suhu (Alert)</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">1 Unit</p>
            <p class="text-[11px] text-amber-600 font-medium mt-0.5"><i class="fa-solid fa-triangle-exclamation mr-1"></i>TEMU4819203 (-18.2°C)</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-bell"></i>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Konsumsi Listrik Harian</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">703 kWh</p>
            <p class="text-[11px] text-emerald-600 font-medium mt-0.5"><i class="fa-solid fa-bolt mr-1"></i>Rp 450.000 / Box / Hari</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-bolt"></i>
        </div>
    </div>
</div>

<!-- Kasus Materi Peringatan Dini IoT (Alert Box) -->
<div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-xl shadow-sm mb-6">
    <div class="flex items-start">
        <div class="flex-shrink-0 text-amber-600 mt-0.5">
            <i class="fa-solid fa-triangle-exclamation text-lg"></i>
        </div>
        <div class="ml-3">
            <h4 class="text-xs font-bold text-amber-900 uppercase tracking-wider">Mitigasi Risiko Kegagalan Cold Chain (Studi Kasus Slide 10 Kuliah)</h4>
            <p class="text-xs text-amber-800 mt-1">
                Sistem peringatan dini telemetri IoT CIDP mendeteksi lonjakan temperatur kontainer <span class="font-mono font-bold">TEMU4819203</span> pada rak R-02 (aktual: -18.2°C, setpoint: -20.0°C). Notifikasi Webhook otomatis dikirim ke modul pemeliharaan M&R guna mencegah kerusakan komoditas susu beku / perishable goods.
            </p>
        </div>
    </div>
</div>

<!-- Tabel Monitor Rak Reefer Live Telemetri -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100">
        <h3 class="font-bold text-gray-800 text-sm sm:text-base flex items-center">
            <i class="fa-solid fa-microchip text-cyan-600 mr-2"></i>Telemetri IoT Sensor Plugs (Carrier Transicold / Daikin IoT)
        </h3>
        <span class="text-xs font-mono text-cyan-700 bg-cyan-50 px-2 py-0.5 rounded">Protokol: MQTT / LoRaWAN</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="bg-gray-50 text-gray-600 border-b border-gray-200">
                    <th class="py-3 px-3 font-semibold">Sensor ID / Plug</th>
                    <th class="py-3 px-3 font-semibold">No. Kontainer</th>
                    <th class="py-3 px-3 font-semibold">Komoditas & Pemilik</th>
                    <th class="py-3 px-3 font-semibold">Set Temp</th>
                    <th class="py-3 px-3 font-semibold">Actual Temp</th>
                    <th class="py-3 px-3 font-semibold">Kelembapan</th>
                    <th class="py-3 px-3 font-semibold">Daya Listrik</th>
                    <th class="py-3 px-3 font-semibold text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr class="bg-amber-50/40 hover:bg-amber-50 transition">
                    <td class="py-3 px-3 font-mono font-bold text-gray-900">
                        REEFER-IOT-B14
                        <span class="block text-[10px] text-gray-400 font-sans">Plug: REEFER-RACK-02</span>
                    </td>
                    <td class="py-3 px-3 font-mono font-bold text-cyan-700">TEMU4819203</td>
                    <td class="py-3 px-3">
                        <span class="font-semibold text-gray-800">Susu Beku Impor (Frozen Milk)</span>
                        <span class="block text-[10px] text-gray-500">PT Samudera Logistik Prima</span>
                    </td>
                    <td class="py-3 px-3 font-mono font-bold text-blue-600">-20.0°C</td>
                    <td class="py-3 px-3 font-mono font-bold text-amber-600">-18.2°C ⚠️</td>
                    <td class="py-3 px-3 font-mono text-gray-600">88% RH</td>
                    <td class="py-3 px-3 font-mono text-emerald-700">18.5 kWh / hari</td>
                    <td class="py-3 px-3 text-right">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">Alert Naik</span>
                    </td>
                </tr>
                <tr class="hover:bg-cyan-50/20 transition">
                    <td class="py-3 px-3 font-mono font-bold text-gray-900">
                        REEFER-IOT-A01
                        <span class="block text-[10px] text-gray-400 font-sans">Plug: REEFER-RACK-01</span>
                    </td>
                    <td class="py-3 px-3 font-mono font-bold text-cyan-700">TEMU7777777</td>
                    <td class="py-3 px-3">
                        <span class="font-semibold text-gray-800">Daging Sapi Beku (Frozen Beef)</span>
                        <span class="block text-[10px] text-gray-500">Maersk Indonesia</span>
                    </td>
                    <td class="py-3 px-3 font-mono font-bold text-blue-600">-22.0°C</td>
                    <td class="py-3 px-3 font-mono font-bold text-blue-600">-22.1°C ✓</td>
                    <td class="py-3 px-3 font-mono text-gray-600">85% RH</td>
                    <td class="py-3 px-3 font-mono text-emerald-700">19.2 kWh / hari</td>
                    <td class="py-3 px-3 text-right">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700">Optimal</span>
                    </td>
                </tr>
                <tr class="hover:bg-cyan-50/20 transition">
                    <td class="py-3 px-3 font-mono font-bold text-gray-900">
                        REEFER-IOT-A02
                        <span class="block text-[10px] text-gray-400 font-sans">Plug: REEFER-RACK-01</span>
                    </td>
                    <td class="py-3 px-3 font-mono font-bold text-cyan-700">CSQU6666666</td>
                    <td class="py-3 px-3">
                        <span class="font-semibold text-gray-800">Ikan Tuna Segar (Fresh Tuna)</span>
                        <span class="block text-[10px] text-gray-500">CMA CGM Logistics</span>
                    </td>
                    <td class="py-3 px-3 font-mono font-bold text-blue-600">-18.0°C</td>
                    <td class="py-3 px-3 font-mono font-bold text-blue-600">-18.0°C ✓</td>
                    <td class="py-3 px-3 font-mono text-gray-600">90% RH</td>
                    <td class="py-3 px-3 font-mono text-emerald-700">17.8 kWh / hari</td>
                    <td class="py-3 px-3 text-right">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700">Optimal</span>
                    </td>
                </tr>
                <tr class="hover:bg-cyan-50/20 transition">
                    <td class="py-3 px-3 font-mono font-bold text-gray-900">
                        REEFER-IOT-C08
                        <span class="block text-[10px] text-gray-400 font-sans">Plug: REEFER-RACK-03</span>
                    </td>
                    <td class="py-3 px-3 font-mono font-bold text-cyan-700">FCIU4444444</td>
                    <td class="py-3 px-3">
                        <span class="font-semibold text-gray-800">Vaksin Farmasi (Pharma Cargo)</span>
                        <span class="block text-[10px] text-gray-500">PT Samudera Logistik Prima</span>
                    </td>
                    <td class="py-3 px-3 font-mono font-bold text-blue-600">+4.0°C</td>
                    <td class="py-3 px-3 font-mono font-bold text-blue-600">+3.9°C ✓</td>
                    <td class="py-3 px-3 font-mono text-gray-600">60% RH</td>
                    <td class="py-3 px-3 font-mono text-emerald-700">12.1 kWh / hari</td>
                    <td class="py-3 px-3 text-right">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700">Optimal</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
