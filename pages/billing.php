<?php
// =============================================================================
// MODUL: BILLING & FAKTUR ERP — SINKRONISASI OPERASIONAL KE MODUL KEUANGAN
// File: pages/billing.php
// CIDP Yard Management System — PT Multi Terminal Indonesia / ITL Trisakti
// PIC : Afriansayah Ayubi (Software & ERP Process Specialist)
// =============================================================================

$billing_info = [
    'pic'    => 'Afriansayah Ayubi',
    'role'   => 'Software & ERP Process Specialist',
    'desc'   => 'Bertanggung jawab atas rekayasa logika proses perangkat lunak, perancangan alur penanganan peti kemas, serta sinkronisasi peristiwa operasional lapangan ke dalam modul keuangan sistem ERP terpadu.',
    'icon'   => 'fa-file-invoice-dollar',
    'status' => 'Menunggu Perancangan Alur ERP dari Afriansayah'
];
?>

<!-- Kartu Status Modul: Menunggu Input Rekan Tim -->
<div class="bg-white rounded-2xl p-8 sm:p-12 shadow-sm border border-gray-100 text-center flex flex-col items-center justify-center max-w-2xl mx-auto my-8 animate-fadeIn">
    <div class="w-20 h-20 bg-blue-50 text-[#0170b9] rounded-2xl flex items-center justify-center mb-5 shadow-inner text-3xl">
        <i class="fa-solid <?= $billing_info['icon'] ?>"></i>
    </div>
    
    <span class="px-3.5 py-1.5 bg-amber-50 text-amber-800 border border-amber-200 rounded-full text-xs font-bold tracking-wide mb-3 flex items-center shadow-xs">
        <span class="w-2 h-2 rounded-full bg-amber-500 mr-2 animate-pulse"></span><?= $billing_info['status'] ?>
    </span>

    <h2 class="text-2xl font-bold text-gray-800 mb-2">Modul Billing & Faktur ERP</h2>
    
    <p class="text-gray-600 text-xs sm:text-sm leading-relaxed mb-6 max-w-lg">
        <?= $billing_info['desc'] ?>
    </p>

    <!-- Kartu PIC Anggota Tim Penanggung Jawab -->
    <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 text-left w-full max-w-md mb-8 flex items-center space-x-3.5">
        <div class="w-11 h-11 rounded-full bg-[#002f5e] text-white flex items-center justify-center font-bold text-base flex-shrink-0 shadow-xs">
            <i class="fa-solid fa-user-pen"></i>
        </div>
        <div>
            <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider">Penanggung Jawab Divisi:</span>
            <p class="font-bold text-gray-900 text-xs sm:text-sm mt-0.5"><?= $billing_info['pic'] ?></p>
            <span class="text-[11px] text-[#0170b9] font-semibold block"><?= $billing_info['role'] ?></span>
        </div>
    </div>

    <a href="dashboard.php?page=beranda" class="px-6 py-2.5 bg-[#0170b9] hover:bg-[#004b87] text-white text-xs font-bold rounded-lg transition-colors shadow-sm inline-flex items-center space-x-2">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Kembali ke Beranda Eksekutif</span>
    </a>
</div>
