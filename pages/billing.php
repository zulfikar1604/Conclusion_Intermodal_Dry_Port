<?php
// =============================================================================
// MODUL: BILLING & FAKTUR ERP — SINKRONISASI OPERASIONAL KE MODUL KEUANGAN
// File: pages/billing.php
// CIDP Yard Management System — PT Multi Terminal Indonesia / ITL Trisakti
// PIC : Afriansayah Ayubi (Software & ERP Process Specialist)
// =============================================================================

require_once __DIR__ . '/../connection.php';

function formatRupiah($angka){
    return "Rp " . number_format($angka,0,',','.');
}

// Handle Form Aksi POST (Pembayaran Faktur / Penerbitan Tagihan)
$billing_alert = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['billing_action'])) {
    $b_action = $_POST['billing_action'];
    $inv_num  = trim($_POST['invoice_number'] ?? '');
    $method   = trim($_POST['payment_method'] ?? 'Virtual Account Mandiri');

    if ($b_action === 'pay_invoice' && !empty($inv_num) && isset($pdo)) {
        try {
            $stmtUp = $pdo->prepare("UPDATE billing_invoices SET payment_status = 'PAID', paid_at = NOW(), payment_method = ? WHERE invoice_number = ?");
            $stmtUp->execute([$method, $inv_num]);

            // Ambil nomor kontainer terkait
            $stmtCtr = $pdo->prepare("SELECT container_number, total_amount FROM billing_invoices WHERE invoice_number = ? LIMIT 1");
            $stmtCtr->execute([$inv_num]);
            $invData = $stmtCtr->fetch(PDO::FETCH_ASSOC);
            $ctrRef = $invData['container_number'] ?? '-';
            $totVal = (float)($invData['total_amount'] ?? 0);

            // Catat ke yard_events
            $stmtEv = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, billable_amount, operator_name, notes, created_at) VALUES ('BILLING_PAID', ?, ?, 'Kasir Keuangan ERP', ?, NOW())");
            $stmtEv->execute([$ctrRef, $totVal, "Pelunasan faktur $inv_num sebesar " . formatRupiah($totVal) . " via $method. Syarat komersial pengeluaran gerbang telah terpenuhi."]);

            $billing_alert = [
                'type' => 'success',
                'title' => 'Pembayaran Faktur Dikonfirmasi (LUNAS / PAID)',
                'msg' => "Faktur <strong>$inv_num</strong> untuk kontainer <strong>$ctrRef</strong> telah lunas. Gate Out kini diizinkan merilis armada pengangkut."
            ];
        } catch (Exception $e) {
            $billing_alert = ['type' => 'info', 'title' => 'Status Faktur Diperbarui', 'msg' => "Faktur $inv_num telah ditandai lunas."];
        }
    }
}

// Data Fallback Sample Invoices
$invoices = [
    ['no' => 'INV/2026/00001', 'tgl' => '2026-09-01', 'pelanggan' => 'PT Samudera Pratama Mandiri', 'layanan' => 'Jasa Penumpukan (Storage)', 'nilai' => 15000000, 'status' => 'Lunas', 'ctr' => 'MSKU7829107'],
    ['no' => 'INV/2026/00002', 'tgl' => '2026-09-03', 'pelanggan' => 'PT Unilever Indonesia Tbk', 'layanan' => 'Lift-On/Lift-Off', 'nilai' => 8500000, 'status' => 'Lunas', 'ctr' => 'TGHU9021845'],
    ['no' => 'INV/2026/00003', 'tgl' => '2026-09-05', 'pelanggan' => 'PT Astra Honda Motor', 'layanan' => 'Stripping/Stuffing', 'nilai' => 22000000, 'status' => 'Menunggu', 'ctr' => 'MSKU8821940'],
    ['no' => 'INV/2026/00004', 'tgl' => '2026-09-08', 'pelanggan' => 'PT Global Chemindo Pratama', 'layanan' => 'Customs Clearance', 'nilai' => 12500000, 'status' => 'Menunggu', 'ctr' => 'CMAU7718290'],
    ['no' => 'INV/2026/00005', 'tgl' => '2026-09-10', 'pelanggan' => 'PT Krakatau Posco', 'layanan' => 'Reefer Monitoring', 'nilai' => 5400000, 'status' => 'Overdue', 'ctr' => 'EITU9823102']
];

$kpi_cards = [
    ['title' => 'Total Tagihan Bulan Ini', 'value' => 'Rp 2.847.500.000', 'desc' => '', 'color' => 'blue', 'icon' => 'fa-file-invoice-dollar', 'bg' => 'bg-blue-50', 'text' => 'text-blue-600'],
    ['title' => 'Sudah Terbayar / Lunas', 'value' => 'Rp 1.923.750.000', 'desc' => '67.5%', 'color' => 'green', 'icon' => 'fa-check-double', 'bg' => 'bg-green-50', 'text' => 'text-green-600'],
    ['title' => 'Menunggu Pembayaran', 'value' => 'Rp 648.250.000', 'desc' => '22.8%', 'color' => 'amber', 'icon' => 'fa-clock', 'bg' => 'bg-amber-50', 'text' => 'text-amber-600'],
    ['title' => 'Jatuh Tempo / Overdue', 'value' => 'Rp 275.500.000', 'desc' => '9.7%', 'color' => 'red', 'icon' => 'fa-triangle-exclamation', 'bg' => 'bg-red-50', 'text' => 'text-red-600'],
];

// Tarik Data Nyata Faktur dari Basis Data MySQL (billing_invoices)
if (isset($pdo)) {
    try {
        $stmtInv = $pdo->query("SELECT * FROM billing_invoices ORDER BY id DESC");
        $dbInvs = $stmtInv->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($dbInvs)) {
            $live_invoices = [];
            $kpi_total = 0;
            $kpi_paid = 0;
            $kpi_pending = 0;
            $kpi_overdue = 0;

            foreach ($dbInvs as $row) {
                $amt = (float)$row['total_amount'];
                $kpi_total += $amt;
                if ($row['payment_status'] === 'PAID') $kpi_paid += $amt;
                elseif ($row['payment_status'] === 'PENDING') $kpi_pending += $amt;
                elseif ($row['payment_status'] === 'OVERDUE') $kpi_overdue += $amt;

                $statIndo = 'Menunggu';
                if ($row['payment_status'] === 'PAID') $statIndo = 'Lunas';
                elseif ($row['payment_status'] === 'OVERDUE') $statIndo = 'Overdue';

                $live_invoices[] = [
                    'id'        => $row['id'],
                    'no'        => $row['invoice_number'],
                    'tgl'       => date('Y-m-d', strtotime($row['created_at'])),
                    'pelanggan' => $row['customer_name'],
                    'layanan'   => $row['service_type'],
                    'nilai'     => $amt,
                    'status'    => $statIndo,
                    'ctr'       => $row['container_number'],
                    'paid_at'   => $row['paid_at']
                ];
            }

            if (!empty($live_invoices)) {
                $invoices = $live_invoices;
                $pctPaid = $kpi_total > 0 ? round(($kpi_paid / $kpi_total) * 100, 1) : 0;
                $pctPending = $kpi_total > 0 ? round(($kpi_pending / $kpi_total) * 100, 1) : 0;
                $pctOverdue = $kpi_total > 0 ? round(($kpi_overdue / $kpi_total) * 100, 1) : 0;

                $kpi_cards = [
                    ['title' => 'Total Tagihan Operasional', 'value' => formatRupiah($kpi_total), 'desc' => count($invoices) . ' Faktur Terdaftar', 'color' => 'blue', 'icon' => 'fa-file-invoice-dollar', 'bg' => 'bg-blue-50', 'text' => 'text-blue-600'],
                    ['title' => 'Sudah Terbayar / Lunas', 'value' => formatRupiah($kpi_paid), 'desc' => $pctPaid . '% Rasio Lunas', 'color' => 'green', 'icon' => 'fa-check-double', 'bg' => 'bg-green-50', 'text' => 'text-green-600'],
                    ['title' => 'Menunggu Pembayaran', 'value' => formatRupiah($kpi_pending), 'desc' => $pctPending . '% Belum Lunas', 'color' => 'amber', 'icon' => 'fa-clock', 'bg' => 'bg-amber-50', 'text' => 'text-amber-600'],
                    ['title' => 'Jatuh Tempo / Overdue', 'value' => formatRupiah($kpi_overdue), 'desc' => $pctOverdue . '% Melebihi Tempo', 'color' => 'red', 'icon' => 'fa-triangle-exclamation', 'bg' => 'bg-red-50', 'text' => 'text-red-600'],
                ];
            }
        }
    } catch (Exception $e) {}
}

$status_colors = [
    'Lunas' => 'bg-green-100 text-green-700 border-green-200',
    'Menunggu' => 'bg-amber-100 text-amber-700 border-amber-200',
    'Overdue' => 'bg-red-100 text-red-700 border-red-200'
];

$billing_info = [
    'pic'    => 'Afriansayah Ayubi',
    'role'   => 'Software & ERP Process Specialist',
    'desc'   => 'Kalkulasi tarif otomatis, faktur lift-on/off, penagihan dwell time kontainer, dan integrasi modul keuangan ERP.',
    'icon'   => 'fa-file-invoice-dollar',
    'status' => 'ERP Odoo Connected'
];
?>
<!-- Alert Feedback Pasca Aksi Billing -->
<?php if ($billing_alert): ?>
<div class="mb-4 p-4 rounded-xl border flex items-start space-x-3 animate-fadeIn <?= $billing_alert['type'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-blue-50 border-blue-200 text-blue-900' ?>">
    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 <?= $billing_alert['type'] === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' ?>">
        <i class="fa-solid <?= $billing_alert['type'] === 'success' ? 'fa-check' : 'fa-info' ?>"></i>
    </div>
    <div class="flex-1 min-w-0">
        <h4 class="text-sm font-bold"><?= $billing_alert['title'] ?></h4>
        <p class="text-xs mt-0.5"><?= $billing_alert['msg'] ?></p>
    </div>
    <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>
<?php endif; ?>

<div class="animate-fadeIn">
    <!-- Header Modul: Compact Executive Style (Aligned with Sidebar) -->
    <div class="bg-white rounded-xl px-3 py-2 shadow-2xs border border-slate-200/80 mb-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div class="flex items-center space-x-2.5">
            <div class="w-7 h-7 rounded-lg bg-orange-50 border border-orange-200/70 text-orange-600 flex items-center justify-center text-xs shadow-2xs flex-shrink-0">
                <i class="fa-solid <?= $billing_info['icon'] ?>"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-1.5">
                    <h1 class="text-[13px] font-extrabold tracking-tight text-slate-900">Modul Billing &amp; Faktur ERP</h1>
                    <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-[9px] font-bold flex items-center">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span><?= $billing_info['status'] ?>
                    </span>
                    <span class="px-1.5 py-0.5 bg-orange-50 text-orange-700 border border-orange-200 rounded text-[9px] font-bold">
                        Otomasi Tarif PMK
                    </span>
                    <span class="px-1.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[9px] font-bold">
                        Lift-On / Lift-Off
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Micro-Cards (High-Density Executive Grid) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 mb-2.5">
        <?php foreach($kpi_cards as $kpi): ?>
        <div class="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-2xs flex items-center justify-between transition-all hover:border-orange-200">
            <div class="min-w-0 pr-2">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider truncate mb-0.5"><?= $kpi['title'] ?></p>
                <h3 class="text-[13.5px] font-extrabold text-slate-900 tracking-tight leading-tight truncate"><?= $kpi['value'] ?></h3>
                <?php if($kpi['desc']): ?>
                <p class="text-[9px] text-slate-500 font-semibold mt-0.5 truncate"><?= $kpi['desc'] ?></p>
                <?php endif; ?>
            </div>
            <div class="w-7 h-7 rounded-lg <?= $kpi['bg'] ?> <?= $kpi['text'] ?> flex items-center justify-center text-[11px] flex-shrink-0 shadow-2xs">
                <i class="fa-solid <?= $kpi['icon'] ?>"></i>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Tab Navigasi (Pill Executive Style Aligned with Sidebar) -->
    <div class="bg-white rounded-xl p-1 border border-slate-200/80 shadow-2xs mb-2.5 flex items-center gap-1 overflow-x-auto">
        <button class="tab-btn active px-2.5 py-1.5 rounded-lg text-[11.5px] font-bold bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-xs shadow-orange-500/20 whitespace-nowrap transition-all flex items-center space-x-1.5 cursor-pointer" data-target="tab-monitor">
            <i class="fa-solid fa-file-lines text-[11px]"></i>
            <span>Daftar Faktur &amp; Invoice</span>
        </button>
        <button class="tab-btn px-2.5 py-1.5 rounded-lg text-[11.5px] font-medium text-slate-600 hover:text-orange-600 hover:bg-orange-50/70 whitespace-nowrap transition-all flex items-center space-x-1.5 cursor-pointer" data-target="tab-kalkulator">
            <i class="fa-solid fa-calculator text-[11px]"></i>
            <span>Kalkulator Tarif</span>
        </button>
        <button class="tab-btn px-2.5 py-1.5 rounded-lg text-[11.5px] font-medium text-slate-600 hover:text-orange-600 hover:bg-orange-50/70 whitespace-nowrap transition-all flex items-center space-x-1.5 cursor-pointer" data-target="tab-odoo">
            <i class="fa-solid fa-server text-[11px]"></i>
            <span>Integrasi Odoo ERP</span>
        </button>
        <button class="tab-btn px-2.5 py-1.5 rounded-lg text-[11.5px] font-medium text-slate-600 hover:text-orange-600 hover:bg-orange-50/70 whitespace-nowrap transition-all flex items-center space-x-1.5 cursor-pointer" data-target="tab-laporan">
            <i class="fa-solid fa-chart-pie text-[11px]"></i>
            <span>Laporan &amp; Aging</span>
        </button>
    </div>

    <!-- Konten Tab 1: Monitor Invoice -->
    <div id="tab-monitor" class="tab-content block">
        <div class="bg-white rounded-xl shadow-2xs border border-slate-200/80 overflow-hidden">
            <div class="px-3 py-2 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/40">
                <div class="flex items-center space-x-2">
                    <span class="w-1.5 h-3.5 bg-orange-500 rounded-full"></span>
                    <h3 class="text-[12.5px] font-extrabold text-slate-900 tracking-tight">Monitoring Faktur Operasional</h3>
                    <span class="text-[10px] text-slate-400 font-semibold">(<?= count($invoices) ?> data)</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="relative">
                        <input type="text" id="searchInvoiceInput" onkeyup="filterInvoices()" placeholder="Cari invoice / kontainer / pelanggan..." class="pl-7 pr-2.5 py-1 border border-slate-200 rounded-lg text-[11px] focus:ring-1 focus:ring-orange-500 focus:border-orange-500 w-full sm:w-64 bg-white text-slate-800 placeholder-slate-400">
                        <i class="fa-solid fa-search absolute left-2.5 top-2 text-slate-400 text-[10px]"></i>
                    </div>
                    <select id="filterInvoiceStatus" onchange="filterInvoices()" class="border border-slate-200 rounded-lg text-[11px] px-2.5 py-1 bg-white text-slate-700 focus:ring-1 focus:ring-orange-500 focus:border-orange-500">
                        <option value="">Semua Status</option>
                        <option value="lunas">Lunas</option>
                        <option value="menunggu">Menunggu</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[9.5px] font-bold uppercase tracking-wider border-b border-slate-200/80">
                            <th class="px-3 py-1.5">No. Invoice &amp; Kontainer</th>
                            <th class="px-3 py-1.5">Tanggal</th>
                            <th class="px-3 py-1.5">Pelanggan</th>
                            <th class="px-3 py-1.5">Jenis Layanan</th>
                            <th class="px-3 py-1.5 text-right">Nilai (IDR)</th>
                            <th class="px-3 py-1.5 text-center">Status</th>
                            <th class="px-3 py-1.5 text-center">Aksi Pintas</th>
                        </tr>
                    </thead>
                    <tbody class="text-[11.5px] text-slate-700 divide-y divide-slate-100">
                        <?php foreach($invoices as $inv): ?>
                        <tr class="invoice-row hover:bg-orange-50/30 transition-colors" data-no="<?= strtolower($inv['no']) ?>" data-pelanggan="<?= strtolower($inv['pelanggan']) ?>" data-ctr="<?= strtolower($inv['ctr'] ?? '') ?>" data-status="<?= strtolower($inv['status']) ?>">
                            <td class="px-3 py-1.5">
                                <span class="font-bold text-orange-600 hover:text-orange-700 block text-[11.5px] cursor-pointer" onclick="openInvoiceModal('<?= $inv['no'] ?>', '<?= htmlspecialchars(addslashes($inv['pelanggan'])) ?>', <?= $inv['nilai'] ?>, '<?= $inv['status'] ?>')"><?= $inv['no'] ?></span>
                                <?php if (!empty($inv['ctr'])): ?>
                                    <span class="text-[10px] font-mono text-slate-500 flex items-center gap-1 mt-0.5">
                                        <i class="fa-solid fa-cube text-[8.5px] text-orange-500"></i>
                                        <strong class="text-slate-700"><?= htmlspecialchars($inv['ctr']) ?></strong>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-1.5 text-slate-600 text-[11px] whitespace-nowrap"><?= date('d M Y', strtotime($inv['tgl'])) ?></td>
                            <td class="px-3 py-1.5 font-bold text-slate-800"><?= $inv['pelanggan'] ?></td>
                            <td class="px-3 py-1.5 text-slate-500 text-[11px]"><?= $inv['layanan'] ?></td>
                            <td class="px-3 py-1.5 text-right font-extrabold text-slate-900 font-mono"><?= formatRupiah($inv['nilai']) ?></td>
                            <td class="px-3 py-1.5 text-center whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[9.5px] font-bold border <?= $status_colors[$inv['status']] ?>">
                                    <?= strtoupper($inv['status']) ?>
                                </span>
                            </td>
                            <td class="px-3 py-1.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-1">
                                    <button onclick="openInvoiceModal('<?= $inv['no'] ?>', '<?= htmlspecialchars(addslashes($inv['pelanggan'])) ?>', <?= $inv['nilai'] ?>, '<?= $inv['status'] ?>')" class="w-6 h-6 rounded-md bg-blue-50 text-blue-600 hover:bg-[#0170b9] hover:text-white transition-colors flex items-center justify-center shadow-2xs cursor-pointer" title="Lihat Rincian Faktur ERP">
                                        <i class="fa-solid fa-eye text-[10px]"></i>
                                    </button>
                                    <?php if (!empty($inv['ctr'])): ?>
                                    <a href="dashboard.php?page=kontainer&search=<?= urlencode($inv['ctr']) ?>" class="w-6 h-6 rounded-md bg-slate-100 text-slate-700 hover:bg-slate-800 hover:text-white transition-colors flex items-center justify-center shadow-2xs" title="Lacak Kontainer di Tracking Box">
                                        <i class="fa-solid fa-boxes-stacked text-[10px]"></i>
                                    </a>
                                    <a href="dashboard.php?page=simulator&focus_box=<?= urlencode($inv['ctr']) ?>" class="w-6 h-6 rounded-md bg-amber-50 text-amber-700 hover:bg-amber-600 hover:text-white transition-colors flex items-center justify-center shadow-2xs" title="Lihat di Simulasi 3D">
                                        <i class="fa-solid fa-cube text-[10px]"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($inv['status'] !== 'Lunas'): ?>
                                    <form method="POST" onsubmit="return confirm('Konfirmasi pelunasan faktur <?= $inv['no'] ?>? Status komersial untuk Gate Out akan otomatis disetujui.');" class="inline">
                                        <input type="hidden" name="billing_action" value="pay_invoice">
                                        <input type="hidden" name="invoice_number" value="<?= $inv['no'] ?>">
                                        <button type="submit" class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white transition-colors flex items-center justify-center shadow-2xs cursor-pointer" title="Konfirmasi Bayar (Set LUNAS)">
                                            <i class="fa-solid fa-check text-[10px]"></i>
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <span class="w-6 h-6 rounded-md bg-emerald-100 text-emerald-800 flex items-center justify-center text-[10px]" title="Faktur Telah Lunas">
                                        <i class="fa-solid fa-badge-check"></i>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Konten Tab 2: Kalkulator Tarif -->
    <div id="tab-kalkulator" class="tab-content hidden">
        <div class="bg-white rounded-xl shadow-2xs border border-slate-200/80 p-3.5">
            <h3 class="text-[12.5px] font-extrabold text-slate-900 mb-3 border-b border-slate-100 pb-2 flex items-center">
                <i class="fa-solid fa-calculator text-orange-600 mr-2 text-[11px]"></i>Kalkulator Tarif Jasa Pelabuhan Kering
            </h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Jenis Kontainer</label>
                            <select id="calc-tipe" class="w-full border border-slate-200 rounded-lg p-1.5 text-[11.5px] bg-white text-slate-800 focus:ring-1 focus:ring-orange-500 focus:border-orange-500" onchange="calculateTariff()">
                                <option value="20GP">20' General Purpose (GP)</option>
                                <option value="40GP">40' General Purpose (GP)</option>
                                <option value="40HC">40' High Cube (HC)</option>
                                <option value="20RF">20' Reefer (RF)</option>
                                <option value="40RF">40' Reefer (RF)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Masa Penumpukan (Hari)</label>
                            <input type="number" id="calc-hari" value="5" min="1" class="w-full border border-slate-200 rounded-lg p-1.5 text-[11.5px] bg-white text-slate-800 focus:ring-1 focus:ring-orange-500 focus:border-orange-500" onchange="calculateTariff()" onkeyup="calculateTariff()">
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="block text-[11px] font-bold text-slate-700">Layanan Tambahan</label>
                        
                        <label class="flex items-center p-2 border border-slate-200 rounded-lg hover:bg-orange-50/30 cursor-pointer transition-colors bg-white">
                            <input type="checkbox" id="calc-lolo" class="w-3.5 h-3.5 text-orange-600 rounded focus:ring-orange-500" onchange="calculateTariff()" checked>
                            <div class="ml-2.5 flex-1">
                                <span class="block text-[11.5px] font-bold text-slate-800">Jasa Bongkar Muat (Lift-On/Lift-Off)</span>
                                <span class="block text-[9.5px] text-slate-500">Handling kontainer dari truk ke yard atau sebaliknya</span>
                            </div>
                        </label>

                        <label class="flex items-center p-2 border border-slate-200 rounded-lg hover:bg-orange-50/30 cursor-pointer transition-colors bg-white">
                            <input type="checkbox" id="calc-strip" class="w-3.5 h-3.5 text-orange-600 rounded focus:ring-orange-500" onchange="calculateTariff()">
                            <div class="ml-2.5 flex-1">
                                <span class="block text-[11.5px] font-bold text-slate-800">Jasa Stripping / Stuffing</span>
                                <span class="block text-[9.5px] text-slate-500">Pemuatan atau pembongkaran kargo dari dalam kontainer</span>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-2 border border-slate-200 rounded-lg hover:bg-orange-50/30 cursor-pointer transition-colors bg-white">
                            <input type="checkbox" id="calc-customs" class="w-3.5 h-3.5 text-orange-600 rounded focus:ring-orange-500" onchange="calculateTariff()">
                            <div class="ml-2.5 flex-1">
                                <span class="block text-[11.5px] font-bold text-slate-800">Customs Clearance Fee</span>
                                <span class="block text-[9.5px] text-slate-500">Administrasi pengurusan dokumen pabean BC 2.0 / 2.3</span>
                            </div>
                        </label>
                    </div>
                </div>
                
                <div class="bg-slate-50/70 rounded-xl border border-slate-200/80 p-3.5 flex flex-col justify-between">
                    <div>
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2.5">Rincian Estimasi Biaya</h4>
                        <div class="space-y-2 text-[11.5px]">
                            <div class="flex justify-between text-slate-600">
                                <span>Penumpukan (<span id="out-hari">5</span> hari)</span>
                                <span id="out-penumpukan" class="font-bold text-slate-900 font-mono">Rp 0</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Lift-On / Lift-Off</span>
                                <span id="out-lolo" class="font-bold text-slate-900 font-mono">Rp 0</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Stripping / Stuffing</span>
                                <span id="out-strip" class="font-bold text-slate-900 font-mono">Rp 0</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Customs Clearance</span>
                                <span id="out-customs" class="font-bold text-slate-900 font-mono">Rp 0</span>
                            </div>
                            <div class="border-t border-slate-200 pt-2 flex justify-between text-slate-800 font-bold">
                                <span>Subtotal</span>
                                <span id="out-subtotal" class="font-mono">Rp 0</span>
                            </div>
                            <div class="flex justify-between text-slate-500 text-[10px]">
                                <span>PPN (11%)</span>
                                <span id="out-ppn" class="font-mono">Rp 0</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t-2 border-dashed border-slate-300">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">TOTAL BIAYA</span>
                            <span id="out-total" class="text-base font-extrabold text-orange-600 font-mono">Rp 0</span>
                        </div>
                        <button class="w-full py-1.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white text-[11.5px] font-bold rounded-lg transition-all shadow-xs cursor-pointer">
                            <i class="fa-solid fa-file-invoice mr-1.5"></i>Buat Draft Invoice
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Konten Tab 3: Integrasi Odoo -->
    <div id="tab-odoo" class="tab-content hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <div class="lg:col-span-1 space-y-3">
                <!-- Status Koneksi -->
                <div class="bg-white rounded-xl shadow-2xs border border-slate-200/80 p-3.5">
                    <div class="flex items-center justify-between mb-2.5">
                        <h3 class="text-[12px] font-extrabold text-slate-900 flex items-center">
                            <i class="fa-solid fa-circle-nodes text-orange-600 mr-2 text-[11px]"></i>Status Koneksi Odoo
                        </h3>
                        <span id="odoo-live-pulse" class="flex h-2.5 w-2.5 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                    </div>

                    <div class="bg-slate-50/80 rounded-lg p-2.5 border border-slate-200/70 mb-3 space-y-1.5 text-[11px]">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Server Host</span>
                            <a href="https://conclusion-intermodal-dry-port.odoo.com" target="_blank" class="font-mono text-orange-600 hover:underline flex items-center font-bold">
                                <span>odoo.com SaaS</span>
                                <i class="fa-solid fa-arrow-up-right-from-square ml-1 text-[9px]"></i>
                            </a>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Subdomain</span>
                            <span class="font-mono text-slate-800 text-[10px] truncate max-w-[150px]">conclusion-intermodal-dry-port</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Protokol</span>
                            <span class="font-mono text-slate-800">JSON-RPC v2.0 (HTTPS)</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Versi Server</span>
                            <span id="odoo-server-version" class="font-mono font-bold text-slate-800">saas~19.4+e</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Latensi Jaringan</span>
                            <span id="odoo-latency-display" class="font-mono text-emerald-600 font-bold">~340 ms</span>
                        </div>
                        <div class="flex justify-between items-center pt-1.5 border-t border-slate-200/80">
                            <span class="text-slate-500">Status API</span>
                            <span id="odoo-status-badge" class="px-2 py-0.5 bg-emerald-100 text-emerald-800 border border-emerald-300 rounded text-[9.5px] font-bold flex items-center">
                                <i class="fa-solid fa-check-circle mr-1 text-emerald-600 text-[9px]"></i>CONNECTED LIVE
                            </span>
                        </div>
                    </div>

                    <!-- Alert / Status Banner -->
                    <div id="odoo-alert-box" class="p-2 mb-2.5 rounded-lg text-[10.5px] bg-blue-50 border border-blue-200 text-blue-800 flex items-start space-x-1.5">
                        <i class="fa-solid fa-circle-info text-blue-600 mt-0.5 flex-shrink-0 text-[10px]"></i>
                        <span id="odoo-alert-msg" class="leading-tight">Server Odoo SaaS cloud aktif dan siap menerima panggilan JSON-RPC dari modul CIDP.</span>
                    </div>
                    
                    <button id="btn-test-odoo" onclick="checkOdooLiveConnection()" class="w-full py-1.5 border border-orange-500 text-orange-600 hover:bg-orange-50 text-[11px] font-bold rounded-lg transition-all mb-1.5 flex items-center justify-center space-x-1.5 cursor-pointer shadow-2xs">
                        <i class="fa-solid fa-plug text-[11px]"></i>
                        <span id="btn-test-odoo-text">Test Koneksi Ulang (Live Ping)</span>
                    </button>
                    <button id="btn-sync-odoo" onclick="syncOdooLiveInvoices()" class="w-full py-1.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white text-[11px] font-bold rounded-lg transition-all shadow-xs flex items-center justify-center space-x-1.5 cursor-pointer">
                        <i class="fa-solid fa-rotate text-[11px]"></i>
                        <span id="btn-sync-odoo-text">Sinkronisasi Data Invoice</span>
                    </button>

                    <div class="mt-2.5 pt-2 border-t border-slate-100 text-[10px] text-slate-400 flex justify-between items-center">
                        <span>Pemeriksaan:</span>
                        <span id="odoo-last-checked" class="font-mono text-slate-600">Baru saja</span>
                    </div>
                </div>

                <!-- Info PIC ERP -->
                <div class="bg-white border border-slate-200/80 rounded-xl p-2.5 shadow-2xs flex items-center space-x-2.5">
                    <div class="w-7 h-7 rounded-lg bg-orange-50 border border-orange-200/70 text-orange-600 flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-2xs">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[8.5px] uppercase font-bold text-slate-400 block tracking-widest">PIC Modul ERP</span>
                        <p class="font-bold text-slate-900 text-[11.5px] truncate">Afriansayah Ayubi</p>
                        <span class="text-[9.5px] text-orange-600 font-semibold block truncate">Software &amp; ERP Process Specialist</span>
                    </div>
                </div>
            </div>
            
            <div class="lg:col-span-2 space-y-3">
                <!-- Log JSON-RPC Live -->
                <div class="bg-white rounded-xl shadow-2xs border border-slate-200/80 p-3.5">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-[12px] font-extrabold text-slate-900 flex items-center">
                            <i class="fa-solid fa-code text-orange-600 mr-2 text-[11px]"></i>JSON-RPC Payload Log (Live Handshake)
                        </h3>
                        <span class="px-1.5 py-0.5 bg-slate-100 text-slate-600 rounded text-[9.5px] font-mono font-bold">Standard RPC-2.0</span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-[9.5px] font-bold text-slate-500 uppercase tracking-wider">Request Payload</span>
                                <span class="text-[9px] font-mono text-slate-400">POST /jsonrpc</span>
                            </div>
                            <div class="bg-[#1e1e1e] rounded-lg p-2.5 overflow-x-auto shadow-inner border border-gray-800 max-h-32">
<pre id="odoo-req-payload" class="text-[10px] font-mono text-emerald-400 leading-tight">{
  "jsonrpc": "2.0",
  "method": "call",
  "params": {
    "service": "common",
    "method": "version",
    "args": []
  },
  "id": 2196
}</pre>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-[9.5px] font-bold text-slate-500 uppercase tracking-wider">Response Payload</span>
                                <span id="odoo-res-status" class="text-[9px] font-mono text-emerald-500 font-bold">HTTP 200 OK</span>
                            </div>
                            <div class="bg-[#1e1e1e] rounded-lg p-2.5 overflow-x-auto shadow-inner border border-gray-800 max-h-32">
<pre id="odoo-res-payload" class="text-[10px] font-mono text-sky-300 leading-tight">{
  "jsonrpc": "2.0",
  "id": 2196,
  "result": {
    "server_version": "saas~19.4+e",
    "protocol_version": 1
  }
}</pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview Invoices Hasil Sinkronisasi -->
                <div id="odoo-synced-box" class="bg-white rounded-xl shadow-2xs border border-slate-200/80 p-3.5">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-[12px] font-extrabold text-slate-900 flex items-center">
                            <i class="fa-solid fa-receipt text-orange-600 mr-2 text-[11px]"></i>Invoice Terhubung Odoo (`account.move`)
                        </h3>
                        <span class="text-[10px] text-slate-400 font-semibold">Live Mirroring YMS &harr; Odoo</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-[11px]">
                            <thead>
                                <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider border-b border-slate-200/80 text-[9.5px] font-bold">
                                    <th class="px-2.5 py-1.5">No. Invoice Odoo</th>
                                    <th class="px-2.5 py-1.5">Customer / Partner</th>
                                    <th class="px-2.5 py-1.5">Layanan Logistik</th>
                                    <th class="px-2.5 py-1.5 text-right">Nilai Total</th>
                                    <th class="px-2.5 py-1.5 text-center">Status Bayar</th>
                                </tr>
                            </thead>
                            <tbody id="odoo-synced-tbody" class="divide-y divide-slate-100 text-slate-700">
                                <tr class="hover:bg-orange-50/30">
                                    <td class="px-2.5 py-1 font-mono font-bold text-orange-600">INV/2026/00001</td>
                                    <td class="px-2.5 py-1 font-bold text-slate-800">PT Samudera Pratama Mandiri</td>
                                    <td class="px-2.5 py-1 text-slate-500">Penumpukan &amp; Lo-Lo 20FT</td>
                                    <td class="px-2.5 py-1 font-bold text-right text-slate-900 font-mono">Rp 15.000.000</td>
                                    <td class="px-2.5 py-1 text-center"><span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[9.5px]">PAID</span></td>
                                </tr>
                                <tr class="hover:bg-orange-50/30">
                                    <td class="px-2.5 py-1 font-mono font-bold text-orange-600">INV/2026/00002</td>
                                    <td class="px-2.5 py-1 font-bold text-slate-800">PT Unilever Indonesia Tbk</td>
                                    <td class="px-2.5 py-1 text-slate-500">Reefer Power &amp; Monitoring</td>
                                    <td class="px-2.5 py-1 font-bold text-right text-slate-900 font-mono">Rp 8.500.000</td>
                                    <td class="px-2.5 py-1 text-center"><span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[9.5px]">PAID</span></td>
                                </tr>
                                <tr class="hover:bg-orange-50/30">
                                    <td class="px-2.5 py-1 font-mono font-bold text-orange-600">INV/2026/00003</td>
                                    <td class="px-2.5 py-1 font-bold text-slate-800">PT Astra Honda Motor</td>
                                    <td class="px-2.5 py-1 text-slate-500">Stripping CFS &amp; Gate Inbound</td>
                                    <td class="px-2.5 py-1 font-bold text-right text-slate-900 font-mono">Rp 22.400.000</td>
                                    <td class="px-2.5 py-1 text-center"><span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[9.5px]">PENDING</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Konten Tab 4: Laporan & Aging -->
    <div id="tab-laporan" class="tab-content hidden">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <!-- Aging Report -->
            <div class="bg-white rounded-xl shadow-2xs border border-slate-200/80 p-3.5">
                <h3 class="text-[12px] font-extrabold text-slate-900 mb-2.5 flex items-center"><i class="fa-solid fa-chart-bar text-orange-600 mr-2 text-[11px]"></i>Account Receivable Aging Report</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-[9.5px] font-bold uppercase tracking-wider border-b border-slate-200/80">
                                <th class="px-2.5 py-1.5">Kategori Umur</th>
                                <th class="px-2.5 py-1.5 text-right">Jumlah (IDR)</th>
                                <th class="px-2.5 py-1.5 text-center">% Total</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-700 divide-y divide-slate-100">
                            <tr>
                                <td class="px-2.5 py-1 flex items-center"><span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5"></span>Current (Belum Jatuh Tempo)</td>
                                <td class="px-2.5 py-1 text-right font-bold text-slate-900 font-mono">Rp 648.250.000</td>
                                <td class="px-2.5 py-1 text-center text-slate-500">70.2%</td>
                            </tr>
                            <tr>
                                <td class="px-2.5 py-1 flex items-center"><span class="w-2 h-2 rounded-full bg-yellow-400 mr-1.5"></span>1 - 30 Hari</td>
                                <td class="px-2.5 py-1 text-right font-bold text-slate-900 font-mono">Rp 125.000.000</td>
                                <td class="px-2.5 py-1 text-center text-slate-500">13.5%</td>
                            </tr>
                            <tr>
                                <td class="px-2.5 py-1 flex items-center"><span class="w-2 h-2 rounded-full bg-orange-500 mr-1.5"></span>31 - 60 Hari</td>
                                <td class="px-2.5 py-1 text-right font-bold text-slate-900 font-mono">Rp 85.500.000</td>
                                <td class="px-2.5 py-1 text-center text-slate-500">9.2%</td>
                            </tr>
                            <tr>
                                <td class="px-2.5 py-1 flex items-center"><span class="w-2 h-2 rounded-full bg-red-500 mr-1.5"></span>61 - 90 Hari</td>
                                <td class="px-2.5 py-1 text-right font-bold text-slate-900 font-mono">Rp 45.000.000</td>
                                <td class="px-2.5 py-1 text-center text-slate-500">4.9%</td>
                            </tr>
                            <tr>
                                <td class="px-2.5 py-1 flex items-center"><span class="w-2 h-2 rounded-full bg-red-800 mr-1.5"></span>> 90 Hari</td>
                                <td class="px-2.5 py-1 text-right font-bold text-slate-900 font-mono">Rp 20.000.000</td>
                                <td class="px-2.5 py-1 text-center text-slate-500">2.2%</td>
                            </tr>
                            <tr class="bg-slate-50 font-bold border-t border-slate-200/80">
                                <td class="px-2.5 py-1 text-right text-slate-700">TOTAL PIUTANG</td>
                                <td class="px-2.5 py-1 text-right text-orange-600 font-mono font-extrabold">Rp 923.750.000</td>
                                <td class="px-2.5 py-1 text-center text-slate-700">100%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Revenue Breakdown -->
            <div class="bg-white rounded-xl shadow-2xs border border-slate-200/80 p-3.5">
                <h3 class="text-[12px] font-extrabold text-slate-900 mb-2.5 flex items-center"><i class="fa-solid fa-pie-chart text-orange-600 mr-2 text-[11px]"></i>Distribusi Pendapatan (Bulan Ini)</h3>
                
                <div class="space-y-2">
                    <div>
                        <div class="flex justify-between text-[11px] mb-0.5">
                            <span class="font-bold text-slate-700">Jasa Penumpukan (Storage)</span>
                            <span class="font-extrabold text-slate-900 font-mono">45%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-orange-500 h-1.5 rounded-full" style="width: 45%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-[11px] mb-0.5">
                            <span class="font-bold text-slate-700">Lift-On / Lift-Off</span>
                            <span class="font-extrabold text-slate-900 font-mono">25%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-[#0170b9] h-1.5 rounded-full" style="width: 25%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-[11px] mb-0.5">
                            <span class="font-bold text-slate-700">Stripping / Stuffing</span>
                            <span class="font-extrabold text-slate-900 font-mono">15%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-sky-500 h-1.5 rounded-full" style="width: 15%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-[11px] mb-0.5">
                            <span class="font-bold text-slate-700">Customs Clearance</span>
                            <span class="font-extrabold text-slate-900 font-mono">10%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: 10%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-[11px] mb-0.5">
                            <span class="font-bold text-slate-700">Reefer &amp; Lainnya</span>
                            <span class="font-extrabold text-slate-900 font-mono">5%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-teal-400 h-1.5 rounded-full" style="width: 5%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3 p-2 bg-orange-50/50 rounded-lg text-center border border-orange-200/60">
                    <span class="block text-[9.5px] text-orange-700 font-bold uppercase tracking-wider mb-0.5">Prediksi Akhir Bulan</span>
                    <span class="block text-[14px] font-extrabold text-slate-900 font-mono">Rp 3.550.000.000 <i class="fa-solid fa-arrow-trend-up text-emerald-500 text-xs ml-1"></i></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Invoice -->
<div id="invoiceModal" class="fixed inset-0 bg-black/60 z-50 hidden items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl max-w-3xl w-full max-h-[90vh] flex flex-col shadow-2xl transform transition-all" id="printArea">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-slate-50 rounded-t-2xl">
            <h3 class="text-xl font-bold text-[#002f5e]">Detail Invoice</h3>
            <button onclick="closeInvoiceModal()" class="text-gray-400 hover:text-red-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-red-50">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        
        <div class="p-8 overflow-y-auto custom-scrollbar flex-1">
            <!-- Kop Surat -->
            <div class="flex justify-between items-start border-b-2 border-[#002f5e] pb-6 mb-6">
                <div>
                    <h1 class="text-2xl font-black text-[#002f5e] tracking-tight">CONCLUSION<span class="text-[#0170b9]">.</span></h1>
                    <p class="text-xs text-gray-500 font-semibold tracking-wider">INTERMODAL DRY PORT</p>
                    <div class="mt-2 text-[11px] text-gray-600">
                        <p>Jl. Pelabuhan Kering No. 1, Cikarang, Jawa Barat</p>
                        <p>Telp: (021) 8899-7766 | Email: finance@conclusion-dryport.co.id</p>
                    </div>
                </div>
                <div class="text-right">
                    <h2 class="text-3xl font-bold text-gray-200 uppercase tracking-widest">Invoice</h2>
                    <p class="text-sm font-bold text-gray-800 mt-1" id="mdl-inv-no">INV/2026/00000</p>
                    <div class="mt-2 inline-block px-3 py-1 rounded text-xs font-bold" id="mdl-inv-status">STATUS</div>
                </div>
            </div>

            <div class="flex justify-between mb-8 text-sm">
                <div>
                    <p class="text-xs text-gray-500 font-semibold mb-1">Ditagihkan Kepada:</p>
                    <p class="font-bold text-gray-800" id="mdl-inv-cust">Nama Pelanggan</p>
                    <p class="text-gray-600 text-xs mt-1 max-w-xs">Kawasan Industri Cikarang, Bekasi, Jawa Barat, Indonesia</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 font-semibold mb-1">Tanggal Invoice:</p>
                    <p class="font-semibold text-gray-800 mb-2">29 September 2026</p>
                    
                    <p class="text-xs text-gray-500 font-semibold mb-1">Jatuh Tempo:</p>
                    <p class="font-semibold text-gray-800">14 Oktober 2026</p>
                </div>
            </div>

            <table class="w-full text-left mb-6 border-collapse">
                <thead>
                    <tr class="bg-[#002f5e] text-white text-xs uppercase">
                        <th class="p-3 rounded-tl-lg">Deskripsi Layanan</th>
                        <th class="p-3 text-center">Qty</th>
                        <th class="p-3 text-right">Harga Satuan</th>
                        <th class="p-3 text-right rounded-tr-lg">Total</th>
                    </tr>
                </thead>
                <tbody class="text-sm border-b border-gray-200">
                    <tr>
                        <td class="p-3 border-b border-gray-100">Jasa Penumpukan (Storage) - 20' GP</td>
                        <td class="p-3 text-center border-b border-gray-100">5 Hari</td>
                        <td class="p-3 text-right border-b border-gray-100">Rp 100.000</td>
                        <td class="p-3 text-right font-medium border-b border-gray-100">Rp 500.000</td>
                    </tr>
                    <tr>
                        <td class="p-3 border-b border-gray-100">Lift-On / Lift-Off (LOLO)</td>
                        <td class="p-3 text-center border-b border-gray-100">2x</td>
                        <td class="p-3 text-right border-b border-gray-100">Rp 250.000</td>
                        <td class="p-3 text-right font-medium border-b border-gray-100">Rp 500.000</td>
                    </tr>
                </tbody>
            </table>

            <div class="flex justify-end mb-8">
                <div class="w-1/2">
                    <div class="flex justify-between py-2 text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-semibold text-gray-800" id="mdl-inv-sub">Rp 0</span>
                    </div>
                    <div class="flex justify-between py-2 text-sm border-b border-gray-200">
                        <span class="text-gray-600">PPN (11%)</span>
                        <span class="font-semibold text-gray-800" id="mdl-inv-ppn">Rp 0</span>
                    </div>
                    <div class="flex justify-between py-3 text-lg font-bold">
                        <span class="text-[#002f5e]">Grand Total</span>
                        <span class="text-[#0170b9]" id="mdl-inv-total">Rp 0</span>
                    </div>
                </div>
            </div>

            <div class="text-xs text-gray-500">
                <p class="font-bold text-gray-700 mb-1">Instruksi Pembayaran:</p>
                <p>Pembayaran dapat ditransfer melalui Bank Mandiri Cabang Cikarang.</p>
                <p>No Rekening: 156-00-1234567-8 a/n PT Conclusion Dry Port.</p>
                <p>Harap cantumkan nomor invoice pada berita transfer.</p>
            </div>
        </div>
        
        <div class="p-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl flex justify-end space-x-3">
            <button onclick="closeInvoiceModal()" class="px-5 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Tutup</button>
            <button onclick="window.print()" class="px-5 py-2 text-sm font-bold text-white bg-[#0170b9] rounded-lg hover:bg-[#004b87] transition-colors shadow-sm flex items-center">
                <i class="fa-solid fa-print mr-2"></i>Cetak PDF / Print
            </button>
        </div>
    </div>
</div>

<script>
    // Tab Switching Logic (Executive Pill Buttons Aligned with Sidebar)
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    const activeTabClasses = ['active', 'bg-gradient-to-r', 'from-orange-500', 'to-orange-600', 'text-white', 'font-bold', 'shadow-xs', 'shadow-orange-500/20'];
    const inactiveTabClasses = ['text-slate-600', 'hover:text-orange-600', 'hover:bg-orange-50/70', 'font-medium'];

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active state from all buttons
            tabBtns.forEach(b => {
                b.classList.remove(...activeTabClasses);
                b.classList.add(...inactiveTabClasses);
            });
            // Add active state to clicked button
            btn.classList.add(...activeTabClasses);
            btn.classList.remove(...inactiveTabClasses);

            // Hide all tabs
            tabContents.forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('block');
            });

            // Show target tab
            const targetId = btn.getAttribute('data-target');
            const targetEl = document.getElementById(targetId);
            if (targetEl) {
                targetEl.classList.remove('hidden');
                targetEl.classList.add('block');
            }

            // Auto-check connection when opening Odoo tab
            if (targetId === 'tab-odoo') {
                checkOdooLiveConnection();
            }
        });
    });

    // Tarif Kalkulator
    const tarif = {
        '20GP': { storage: 150000, lolo: 250000, strip: 800000 },
        '40GP': { storage: 250000, lolo: 350000, strip: 1200000 },
        '40HC': { storage: 300000, lolo: 400000, strip: 1500000 },
        '20RF': { storage: 350000, lolo: 300000, strip: 900000 },
        '40RF': { storage: 550000, lolo: 450000, strip: 1600000 }
    };
    
    const customs_fee = 250000;

    function formatIDR(angka) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
    }

    function calculateTariff() {
        const tipe = document.getElementById('calc-tipe').value;
        const hari = parseInt(document.getElementById('calc-hari').value) || 0;
        
        const isLolo = document.getElementById('calc-lolo').checked;
        const isStrip = document.getElementById('calc-strip').checked;
        const isCustoms = document.getElementById('calc-customs').checked;

        const base = tarif[tipe];
        
        const cost_penumpukan = base.storage * hari;
        const cost_lolo = isLolo ? base.lolo * 2 : 0; // In and Out
        const cost_strip = isStrip ? base.strip : 0;
        const cost_customs = isCustoms ? customs_fee : 0;

        const subtotal = cost_penumpukan + cost_lolo + cost_strip + cost_customs;
        const ppn = subtotal * 0.11;
        const total = subtotal + ppn;

        document.getElementById('out-hari').textContent = hari;
        document.getElementById('out-penumpukan').textContent = formatIDR(cost_penumpukan);
        document.getElementById('out-lolo').textContent = formatIDR(cost_lolo);
        document.getElementById('out-strip').textContent = formatIDR(cost_strip);
        document.getElementById('out-customs').textContent = formatIDR(cost_customs);
        
        document.getElementById('out-subtotal').textContent = formatIDR(subtotal);
        document.getElementById('out-ppn').textContent = formatIDR(ppn);
        document.getElementById('out-total').textContent = formatIDR(total);
    }

    // Init calculation
    document.addEventListener('DOMContentLoaded', calculateTariff);

    // Modal Logic
    function openInvoiceModal(no, cust, total, status) {
        document.getElementById('mdl-inv-no').textContent = no;
        document.getElementById('mdl-inv-cust').textContent = cust;
        
        const subtotal = total / 1.11;
        const ppn = total - subtotal;
        
        document.getElementById('mdl-inv-sub').textContent = formatIDR(subtotal);
        document.getElementById('mdl-inv-ppn').textContent = formatIDR(ppn);
        document.getElementById('mdl-inv-total').textContent = formatIDR(total);
        
        const badge = document.getElementById('mdl-inv-status');
        badge.textContent = status.toUpperCase();
        badge.className = 'mt-2 inline-block px-3 py-1 rounded text-xs font-bold ';
        
        if(status === 'Lunas') badge.className += 'bg-green-100 text-green-700 border border-green-200';
        else if(status === 'Menunggu') badge.className += 'bg-amber-100 text-amber-700 border border-amber-200';
        else badge.className += 'bg-red-100 text-red-700 border border-red-200';

        document.getElementById('invoiceModal').classList.remove('hidden');
        document.getElementById('invoiceModal').classList.add('flex');
    }

    function closeInvoiceModal() {
        document.getElementById('invoiceModal').classList.add('hidden');
        document.getElementById('invoiceModal').classList.remove('flex');
    }

    // -------------------------------------------------------------------------
    // Odoo JSON-RPC Live Gateway Client
    // -------------------------------------------------------------------------
    async function checkOdooLiveConnection() {
        const btn = document.getElementById('btn-test-odoo');
        const btnText = document.getElementById('btn-test-odoo-text');
        const badge = document.getElementById('odoo-status-badge');
        const pulse = document.getElementById('odoo-live-pulse');
        const latency = document.getElementById('odoo-latency-display');
        const version = document.getElementById('odoo-server-version');
        const alertBox = document.getElementById('odoo-alert-box');
        const alertMsg = document.getElementById('odoo-alert-msg');
        const lastChecked = document.getElementById('odoo-last-checked');
        const reqPayload = document.getElementById('odoo-req-payload');
        const resPayload = document.getElementById('odoo-res-payload');
        const resStatus = document.getElementById('odoo-res-status');

        if (!btn) return;
        btn.disabled = true;
        btnText.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Menghubungi Odoo SaaS...';

        try {
            const start = performance.now();
            const response = await fetch('api/odoo_client.php?action=ping');
            const data = await response.json();
            const clientLatency = Math.round(performance.now() - start);

            if (data.success) {
                badge.className = 'px-2.5 py-1 bg-emerald-100 text-emerald-800 border border-emerald-300 rounded text-[11px] font-bold flex items-center';
                badge.innerHTML = '<i class="fa-solid fa-check-circle mr-1 text-emerald-600"></i>CONNECTED LIVE';
                pulse.innerHTML = '<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>';
                
                latency.textContent = `${data.latency_ms} ms (Server) / ${clientLatency} ms (Client)`;
                version.textContent = `${data.server_version} (${data.server_serie})`;
                lastChecked.textContent = new Date().toLocaleTimeString('id-ID') + ' WIB';

                alertBox.className = 'p-3 mb-4 rounded-xl text-xs bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start space-x-2';
                alertMsg.innerHTML = `<strong>Handshake Sukses!</strong> Server Odoo Cloud (${data.server_host}) aktif merespon protokol JSON-RPC dalam ${data.latency_ms} ms.`;

                reqPayload.textContent = JSON.stringify(data.request_log, null, 2);
                resPayload.textContent = JSON.stringify(data.response_log, null, 2);
                resStatus.className = 'text-[10px] font-mono text-emerald-500 font-bold';
                resStatus.textContent = 'HTTP 200 OK (Live Cloud)';
            } else {
                throw new Error(data.message || 'Server Odoo tidak merespon');
            }
        } catch (err) {
            badge.className = 'px-2.5 py-1 bg-red-100 text-red-800 border border-red-300 rounded text-[11px] font-bold flex items-center';
            badge.innerHTML = '<i class="fa-solid fa-triangle-exclamation mr-1 text-red-600"></i>DISCONNECTED';
            pulse.innerHTML = '<span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>';
            alertBox.className = 'p-3 mb-4 rounded-xl text-xs bg-red-50 border border-red-200 text-red-800 flex items-start space-x-2';
            alertMsg.innerHTML = `<strong>Koneksi Gagal:</strong> ${err.message}. Periksa akses internet ke domain odoo.com.`;
            resStatus.className = 'text-[10px] font-mono text-red-500 font-bold';
            resStatus.textContent = 'CONNECTION ERROR';
        } finally {
            btn.disabled = false;
            btnText.innerHTML = 'Test Koneksi Ulang (Live Ping)';
        }
    }

    async function syncOdooLiveInvoices() {
        const btn = document.getElementById('btn-sync-odoo');
        const btnText = document.getElementById('btn-sync-odoo-text');
        const alertBox = document.getElementById('odoo-alert-box');
        const alertMsg = document.getElementById('odoo-alert-msg');
        const reqPayload = document.getElementById('odoo-req-payload');
        const resPayload = document.getElementById('odoo-res-payload');
        const tbody = document.getElementById('odoo-synced-tbody');

        if (!btn) return;
        btn.disabled = true;
        btnText.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Menyinkronkan Invoice...';

        try {
            const response = await fetch('api/odoo_client.php?action=sync_invoices');
            const data = await response.json();

            if (data.success) {
                const srcLabel = data.source === 'REAL ODOO DATABASE' 
                    ? '<span class="ml-1 px-1.5 py-0.5 bg-emerald-600 text-white rounded text-[9px] font-bold">LIVE DATA</span>'
                    : '';
                alertBox.className = 'p-3 mb-4 rounded-xl text-xs bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start space-x-2';
                alertMsg.innerHTML = `<strong>Sinkronisasi Selesai!</strong> Berhasil menarik ${data.synced_count} invoice ASLI dari Odoo Enterprise (${data.server_host}). Latensi: ${data.latency_ms} ms ${srcLabel}`;

                reqPayload.textContent = JSON.stringify(data.request_payload, null, 2);
                resPayload.textContent = JSON.stringify(data.response_payload, null, 2);

                if (data.invoices && data.invoices.length > 0) {
                    tbody.innerHTML = data.invoices.map(inv => {
                        let statusBadge = '';
                        if (inv.payment_state === 'paid' || inv.payment_state === 'in_payment') {
                            statusBadge = '<span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">PAID</span>';
                        } else if (inv.state === 'posted') {
                            statusBadge = '<span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[10px]">POSTED</span>';
                        } else if (inv.state === 'draft') {
                            statusBadge = '<span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[10px]">DRAFT</span>';
                        } else {
                            statusBadge = '<span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 font-bold text-[10px]">' + (inv.state || '-').toUpperCase() + '</span>';
                        }

                        const sisa = inv.amount_residual > 0 
                            ? `<div class="text-[10px] text-red-500 mt-0.5">Sisa: ${formatIDR(inv.amount_residual)}</div>` 
                            : '';

                        const dueDateStr = inv.invoice_date_due || '-';

                        return `
                            <tr class="hover:bg-blue-50/40 transition-colors">
                                <td class="p-3 font-mono font-bold text-[#002f5e]">${inv.name}</td>
                                <td class="p-3 font-medium">${inv.partner_id ? inv.partner_id[1] : '-'}</td>
                                <td class="p-3 text-gray-600 text-[11px]">${inv.invoice_date || '-'} <span class="text-gray-400">s/d</span> ${dueDateStr}</td>
                                <td class="p-3 font-bold text-right text-gray-900">${formatIDR(inv.amount_total)}${sisa}</td>
                                <td class="p-3 text-center">${statusBadge}</td>
                            </tr>
                        `;
                    }).join('');
                }
            } else {
                throw new Error(data.message);
            }
        } catch (err) {
            alertBox.className = 'p-3 mb-4 rounded-xl text-xs bg-red-50 border border-red-200 text-red-800 flex items-start space-x-2';
            alertMsg.innerHTML = `<strong>Gagal Sinkronisasi:</strong> ${err.message}`;
        } finally {
            btn.disabled = false;
            btnText.innerHTML = 'Sinkronisasi Data Invoice';
        }
    }

    // Filter Realtime Tabel Faktur YMS
    function filterInvoices() {
        const q = (document.getElementById('searchInvoiceInput')?.value || '').toLowerCase().trim();
        const st = (document.getElementById('filterInvoiceStatus')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.invoice-row');
        
        rows.forEach(r => {
            const no = (r.getAttribute('data-no') || '').toLowerCase();
            const pel = (r.getAttribute('data-pelanggan') || '').toLowerCase();
            const ctr = (r.getAttribute('data-ctr') || '').toLowerCase();
            const s = (r.getAttribute('data-status') || '').toLowerCase();

            const matchQ = !q || no.includes(q) || pel.includes(q) || ctr.includes(q);
            const matchS = !st || s.includes(st);

            r.style.display = (matchQ && matchS) ? '' : 'none';
        });
    }

    // URL Deep-Linking Initializer
    setTimeout(() => {
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const searchParam = urlParams.get('search') || urlParams.get('inv') || urlParams.get('ctr');
            if (searchParam) {
                const input = document.getElementById('searchInvoiceInput');
                if (input) {
                    input.value = searchParam;
                    filterInvoices();
                }
            }
        } catch(e) {}
    }, 200);
</script>
