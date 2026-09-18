<?php
// Pastikan file ini di-include dari dashboard.php
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}
require_once __DIR__ . '/../connection.php';

// Ambil data kontainer dari database
$containers = [];
$total_teus = 0;
try {
    $stmt = $pdo->query("SELECT * FROM containers ORDER BY block ASC, bay ASC, row ASC, tier ASC");
    $containers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($containers as $c) {
        $total_teus += (strpos($c['size_type'], '40') !== false) ? 2 : 1;
    }
} catch (Exception $e) {
    // fallback
}
?>
<!-- Modul Manajemen Yard & Inventaris Peti Kemas -->
<div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-[#002f5e] flex items-center">
            <i class="fa-solid fa-boxes-stacked text-[#0170b9] mr-3"></i>Inventaris & Manajemen Lapangan Penumpukan (Yard)
        </h2>
        <p class="text-gray-500 text-sm mt-1">Pemantauan inventaris fisik kontainer, koordinat 3D Bay-Row-Tier, verifikasi RFID & audit dwell time</p>
    </div>
    <div class="flex gap-2">
        <a href="dashboard.php?page=denah" class="bg-[#0170b9] hover:bg-[#004b87] text-white px-4 py-2 rounded-lg shadow-sm text-xs font-bold flex items-center transition">
            <i class="fa-solid fa-map-location-dot mr-2"></i>Buka Denah Interaktif
        </a>
    </div>
</div>

<!-- 4 Summary KPI Yard -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Total Kontainer Aktif</p>
            <p class="text-2xl font-bold text-gray-800 mt-1"><?= count($containers) ?> Box</p>
            <p class="text-[11px] text-blue-600 font-medium mt-0.5"><i class="fa-solid fa-cube mr-1"></i><?= $total_teus ?> TEUs Terisi</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#0170b9] flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-boxes-stacked"></i>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Reefer Cold Chain</p>
            <p class="text-2xl font-bold text-cyan-600 mt-1">4 Unit</p>
            <p class="text-[11px] text-cyan-600 font-medium mt-0.5"><i class="fa-solid fa-snowflake mr-1"></i>Suhu Stabil -20°C</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-plug"></i>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Dangerous Goods (DG)</p>
            <p class="text-2xl font-bold text-red-600 mt-1">2 Unit</p>
            <p class="text-[11px] text-red-600 font-medium mt-0.5"><i class="fa-solid fa-triangle-exclamation mr-1"></i>IMO Terisolasi</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-biohazard"></i>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Dwell Time Rata-rata</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">2.1 Hari</p>
            <p class="text-[11px] text-emerald-600 font-medium mt-0.5"><i class="fa-solid fa-clock mr-1"></i>Target &lt; 3.0 Hari ✓</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-stopwatch"></i>
        </div>
    </div>
</div>

<!-- Tabel Utama Inventaris Lapangan Penumpukan -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 mb-4 pb-3 border-b border-gray-100">
        <div class="flex items-center space-x-2">
            <h3 class="font-bold text-gray-800 text-base">Manifest Kontainer Terdaftar di Yard</h3>
            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded font-mono"><?= count($containers) ?> Records</span>
        </div>
        
        <!-- Filter & Search -->
        <div class="flex gap-2">
            <input type="text" id="filter-search" onkeyup="filterYardTable()" placeholder="Cari No. Kontainer / Pemilik..."
                class="text-xs bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5 outline-none focus:bg-white focus:ring-2 focus:ring-[#0170b9]">
            <select id="filter-block" onchange="filterYardTable()" class="text-xs bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 outline-none">
                <option value="">Semua Blok</option>
                <option value="A">Blok A</option>
                <option value="B">Blok B</option>
                <option value="C">Blok C</option>
                <option value="REEFER">Blok Reefer</option>
                <option value="DG">Blok DG</option>
                <option value="EMPTY">Blok Empty</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs" id="table-yard">
            <thead>
                <tr class="bg-gray-50 text-gray-600 border-b border-gray-200">
                    <th class="py-3 px-3 font-semibold">No. Kontainer</th>
                    <th class="py-3 px-3 font-semibold">Tipe & Kargo</th>
                    <th class="py-3 px-3 font-semibold">Tag RFID UHF</th>
                    <th class="py-3 px-3 font-semibold">Posisi (Bay-Row-Tier)</th>
                    <th class="py-3 px-3 font-semibold">Berat (VGM)</th>
                    <th class="py-3 px-3 font-semibold">Pemilik / Agen</th>
                    <th class="py-3 px-3 font-semibold">Bea Cukai</th>
                    <th class="py-3 px-3 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($containers as $c): ?>
                <?php
                    $badgeCargo = 'bg-blue-100 text-blue-700';
                    if ($c['cargo_type'] === 'reefer') $badgeCargo = 'bg-cyan-100 text-cyan-700';
                    elseif ($c['cargo_type'] === 'dg') $badgeCargo = 'bg-red-100 text-red-700';
                    elseif ($c['cargo_type'] === 'empty') $badgeCargo = 'bg-gray-100 text-gray-700';
                ?>
                <tr class="hover:bg-blue-50/20 transition">
                    <td class="py-3 px-3 font-mono font-bold text-gray-900">
                        <span class="text-[#0170b9]"><?= htmlspecialchars($c['container_number']) ?></span>
                        <span class="block text-[10px] text-gray-400 font-sans"><?= htmlspecialchars($c['sscc_code'] ?? '-') ?></span>
                    </td>
                    <td class="py-3 px-3">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $badgeCargo ?> uppercase"><?= htmlspecialchars($c['cargo_type']) ?></span>
                        <span class="block text-[11px] text-gray-500 mt-0.5"><?= htmlspecialchars($c['size_type']) ?></span>
                    </td>
                    <td class="py-3 px-3 font-mono text-purple-700 text-[11px]">
                        <span class="bg-purple-50 px-1.5 py-0.5 rounded border border-purple-100 font-bold"><?= htmlspecialchars($c['rfid_tag'] ?? '-') ?></span>
                    </td>
                    <td class="py-3 px-3 font-bold text-gray-800">
                        <span class="bg-gray-100 px-2 py-1 rounded text-xs">
                            <?= htmlspecialchars($c['block']) ?>-<?= htmlspecialchars($c['bay']) ?>-<?= htmlspecialchars($c['row']) ?>-<?= htmlspecialchars($c['tier']) ?>
                        </span>
                    </td>
                    <td class="py-3 px-3 font-mono font-medium text-emerald-600">
                        <?= number_format($c['gross_weight_kg'], 0, ',', '.') ?> kg ✓
                    </td>
                    <td class="py-3 px-3 text-gray-700">
                        <?= htmlspecialchars($c['owner_company'] ?? '-') ?>
                    </td>
                    <td class="py-3 px-3">
                        <?php if ($c['customs_status'] === 'SPPB_CLEARED'): ?>
                            <span class="text-emerald-700 font-bold text-[11px] flex items-center"><i class="fa-solid fa-check-circle mr-1"></i>SPPB Cleared</span>
                        <?php else: ?>
                            <span class="text-gray-500 text-[11px]"><?= htmlspecialchars($c['customs_status']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="py-3 px-3 text-right">
                        <a href="dashboard.php?page=denah" class="bg-gray-100 hover:bg-[#0170b9] hover:text-white text-gray-700 px-2.5 py-1 rounded text-[11px] font-medium transition inline-flex items-center" title="Buka di Denah">
                            <i class="fa-solid fa-crosshairs mr-1"></i>Lacak
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterYardTable() {
    const search = document.getElementById('filter-search').value.toUpperCase();
    const block = document.getElementById('filter-block').value.toUpperCase();
    const table = document.getElementById('table-yard');
    const tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        const rowText = tr[i].textContent.toUpperCase();
        const blockCell = tr[i].getElementsByTagName('td')[3];
        const blockText = blockCell ? blockCell.textContent.toUpperCase() : '';

        const matchSearch = rowText.indexOf(search) > -1;
        const matchBlock = !block || blockText.indexOf(block) > -1;

        if (matchSearch && matchBlock) {
            tr[i].style.display = '';
        } else {
            tr[i].style.display = 'none';
        }
    }
}
</script>
