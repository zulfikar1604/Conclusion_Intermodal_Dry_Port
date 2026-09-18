<?php
// Pastikan file ini di-include dari dashboard.php
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}
require_once __DIR__ . '/../connection.php';
?>
<!-- Modul Billing & Faktur Komersial (Financial Flow ERP) -->
<div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-[#002f5e] flex items-center">
            <i class="fa-solid fa-file-invoice-dollar text-[#0170b9] mr-3"></i>Modul Finansial & Penerbitan Faktur (e-Invoice)
        </h2>
        <p class="text-gray-500 text-sm mt-1">Konversi Otomatis Aktivitas Fisik Terminal Menjadi Pos Pendapatan Resmi (Aliran Finansial ERP)</p>
    </div>
    <div class="flex gap-2">
        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
            <i class="fa-solid fa-check-double mr-1.5"></i>Sinkronisasi Modul Piutang Akuntansi Aktif
        </span>
    </div>
</div>

<!-- 4 KPI Finansial -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Pendapatan Hari Ini</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">Rp 47.25M</p>
            <p class="text-[11px] text-emerald-600 font-medium mt-0.5"><i class="fa-solid fa-arrow-trend-up mr-1"></i>+8.4% vs Kemarin</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-money-bill-wave"></i>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Faktur Diterbitkan</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">289 Faktur</p>
            <p class="text-[11px] text-blue-600 font-medium mt-0.5"><i class="fa-solid fa-file-circle-check mr-1"></i>Bulan September 2026</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#0170b9] flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-file-invoice"></i>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Jasa Lift-on / Lift-off</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">Rp 275.000</p>
            <p class="text-[11px] text-gray-500 mt-0.5">Tarif Standar / Box</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-dolly"></i>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Sewa Penumpukan Yard</p>
            <p class="text-2xl font-bold text-purple-600 mt-1">Rp 150.000</p>
            <p class="text-[11px] text-gray-500 mt-0.5">Tarif Progresif / Hari</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-warehouse"></i>
        </div>
    </div>
</div>

<!-- Template Faktur Interaktif (Sesuai Materi Bab V.4) -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Kolom Kiri: Simulator Pembuatan Faktur Otomatis -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-start pb-4 border-b border-gray-200 mb-6">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-[#0170b9]">FAKTUR TAGIHAN TERMINAL PETI KEMAS</span>
                <h3 class="text-xl font-black text-gray-900 mt-0.5">INV-20260918-0089</h3>
                <p class="text-xs text-gray-400">Trigger Origin: Gate-Out Settlement &bull; Tanggal: 18 September 2026</p>
            </div>
            <div class="text-right">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">LUNAS (PAID)</span>
                <p class="text-[11px] text-gray-400 mt-1 font-mono">ID Transaksi: TRX-882194</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 text-xs mb-6 bg-gray-50 p-4 rounded-lg border border-gray-100">
            <div>
                <span class="text-gray-400 block uppercase font-bold text-[10px]">Ditagihkan Kepada (Partner):</span>
                <p class="font-bold text-gray-900 text-sm mt-0.5">PT Samudera Logistik Prima</p>
                <p class="text-gray-600">NPWP: 01.892.441.2-054.000</p>
                <p class="text-gray-600">Alamat: Jl. Raya Pelabuhan No. 88, Jakarta Utara</p>
            </div>
            <div>
                <span class="text-gray-400 block uppercase font-bold text-[10px]">Objek & Detail Muatan:</span>
                <p class="font-bold text-gray-900 text-sm mt-0.5 font-mono">MSKU9182374 (40FT High Cube)</p>
                <p class="text-gray-600">Armada Pengangkut: Truk Plat <span class="font-mono font-bold">B 9481 UEK</span></p>
                <p class="text-gray-600">Dwell Time di Lapangan: <span class="font-bold text-orange-600">3 Hari Penumpukan</span></p>
            </div>
        </div>

        <!-- Rincian Item Tagihan (Sesuai Materi) -->
        <table class="w-full text-left text-xs mb-6">
            <thead>
                <tr class="bg-gray-100 text-gray-700 font-bold border-b border-gray-200">
                    <th class="py-2.5 px-3">Uraian Jasa Layanan Terminal</th>
                    <th class="py-2.5 px-3 text-center">Vol</th>
                    <th class="py-2.5 px-3 text-right">Tarif Satuan (IDR)</th>
                    <th class="py-2.5 px-3 text-right">Subtotal (IDR)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr>
                    <td class="py-2.5 px-3 font-medium text-gray-800">Jasa Lift-Off (Lo-Lo) Kontainer 40ft (Saat Masuk)</td>
                    <td class="py-2.5 px-3 text-center">1 Box</td>
                    <td class="py-2.5 px-3 text-right font-mono">275.000</td>
                    <td class="py-2.5 px-3 text-right font-mono font-bold">275.000</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-3 font-medium text-gray-800">Jasa Lift-On (Lo-Lo) Kontainer 40ft (Saat Keluar)</td>
                    <td class="py-2.5 px-3 text-center">1 Box</td>
                    <td class="py-2.5 px-3 text-right font-mono">275.000</td>
                    <td class="py-2.5 px-3 text-right font-mono font-bold">275.000</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-3 font-medium text-gray-800">Sewa Penumpukan Lapangan (Storage Fee - 3 Hari)</td>
                    <td class="py-2.5 px-3 text-center">3 Hari</td>
                    <td class="py-2.5 px-3 text-right font-mono">150.000</td>
                    <td class="py-2.5 px-3 text-right font-mono font-bold">450.000</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-3 font-medium text-gray-800">Jasa Pengukuran Timbangan Dinamis (VGM 80 Ton)</td>
                    <td class="py-2.5 px-3 text-center">1 Unit</td>
                    <td class="py-2.5 px-3 text-right font-mono">50.000</td>
                    <td class="py-2.5 px-3 text-right font-mono font-bold">50.000</td>
                </tr>
            </tbody>
            <tfoot class="border-t-2 border-gray-300 font-bold">
                <tr>
                    <td colspan="3" class="py-2 px-3 text-right text-gray-600">Subtotal Jasa Operasional:</td>
                    <td class="py-2 px-3 text-right font-mono text-gray-900">Rp 1.050.000</td>
                </tr>
                <tr>
                    <td colspan="3" class="py-1 px-3 text-right text-gray-600">Pajak Pertambahan Nilai (PPN 11%):</td>
                    <td class="py-1 px-3 text-right font-mono text-gray-900">Rp 115.500</td>
                </tr>
                <tr class="text-sm bg-blue-50/60 text-[#002f5e]">
                    <td colspan="3" class="py-3 px-3 text-right font-black">TOTAL TAGIHAN (GRAND TOTAL):</td>
                    <td class="py-3 px-3 text-right font-mono font-black text-base text-[#0170b9]">Rp 1.165.500</td>
                </tr>
            </tfoot>
        </table>

        <!-- Tombol Aksi Cetak Faktur -->
        <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
            <button onclick="window.print()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-xs font-bold transition flex items-center">
                <i class="fa-solid fa-print mr-2"></i>Cetak Dokumen Faktur
            </button>
            <button onclick="alert('Faktur INV-20260918-0089 berhasil disinkronkan ke jurnal piutang ERP Odoo!')" class="bg-[#0170b9] hover:bg-[#004b87] text-white px-4 py-2 rounded-lg text-xs font-bold transition flex items-center shadow-md">
                <i class="fa-solid fa-cloud-arrow-up mr-2"></i>Sinkronisasi ke Modul ERP Odoo
            </button>
        </div>
    </div>

    <!-- Kolom Kanan: Daftar Faktur Terbaru -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col justify-between">
        <div>
            <h3 class="font-bold text-gray-800 text-sm mb-3 pb-2 border-b border-gray-100 flex items-center">
                <i class="fa-solid fa-clock-rotate-left text-[#0170b9] mr-2"></i>Riwayat Faktur Terbit Terkini
            </h3>
            
            <div class="space-y-3">
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 text-xs">
                    <div class="flex justify-between items-start mb-1">
                        <span class="font-mono font-bold text-[#0170b9]">INV-20260918-0089</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700">Lunas</span>
                    </div>
                    <p class="font-semibold text-gray-800">PT Samudera Logistik Prima</p>
                    <p class="text-[11px] text-gray-500 mt-0.5">MSKU9182374 &bull; Rp 1.165.500</p>
                </div>

                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 text-xs">
                    <div class="flex justify-between items-start mb-1">
                        <span class="font-mono font-bold text-[#0170b9]">INV-20260918-0088</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700">Lunas</span>
                    </div>
                    <p class="font-semibold text-gray-800">PT Evergreen Shipping Indonesia</p>
                    <p class="text-[11px] text-gray-500 mt-0.5">TCLU8827415 &bull; Rp 825.000</p>
                </div>

                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 text-xs">
                    <div class="flex justify-between items-start mb-1">
                        <span class="font-mono font-bold text-[#0170b9]">INV-20260918-0087</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Menunggu</span>
                    </div>
                    <p class="font-semibold text-gray-800">Maersk Indonesia</p>
                    <p class="text-[11px] text-gray-500 mt-0.5">TEMU4819203 (Reefer) &bull; Rp 2.450.000</p>
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100">
            <span class="text-[11px] text-gray-400 block mb-1">Audit Trail & Integrasi:</span>
            <p class="text-[11px] text-gray-600">
                Setiap kali palang barrier Gate-Out terangkat, sistem YMS secara otomatis memicu pemanggilan API RESTful ke modul akuntansi guna memastikan tidak terjadi kebocoran pendapatan.
            </p>
        </div>
    </div>
</div>
