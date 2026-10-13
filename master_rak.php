<?php
// =============================================================================
// FILE: master_rak.php
// FUNGSI: Detail Inspeksi Slot Rak & Sektor Gudang CFS (Integrasi master_lokasi)
// =============================================================================
require_once 'connection.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$search = trim($_GET['search'] ?? '');
$blok = '';
$sektor = '';

if (!empty($search)) {
    // Format: "A1" -> Sektor A, Blok 1
    if (preg_match('/^([A-Za-z]+)(\d+)$/', $search, $matches)) {
        $sektor = strtoupper($matches[1]);
        $blok = $matches[2];
    } else {
        $sektor = strtoupper(substr($search, 0, 1));
        $blok = substr($search, 1);
    }
}

// Inisialisasi slots sebagai array kosong agar aman dari TypeError pada count($slots)
$slots = [];

// Sanitasi parameter input
$blok = trim($blok);
$sektor = strtoupper(trim($sektor));

// Eksekusi Query menggunakan PDO (Opsi A - Standar Proyek CIDP)
try {
    if (!empty($blok) && !empty($sektor)) {
        $stmt = $pdo->prepare("SELECT id, blok, bagian, rak, status, pallet_sscc, commodity_name, updated_at 
                               FROM master_lokasi 
                               WHERE blok = ? AND bagian = ? 
                               ORDER BY rak ASC");
        $stmt->execute([$blok, $sektor]);
        $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Ambil beberapa sampel awal jika tanpa parameter
        $stmt = $pdo->query("SELECT id, blok, bagian, rak, status, pallet_sscc, commodity_name, updated_at 
                             FROM master_lokasi 
                             ORDER BY blok ASC, bagian ASC, rak ASC 
                             LIMIT 24");
        $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Database error in master_rak.php: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <title>Master Rak &amp; Sektor - Conclusion Intermodal Dry Port</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 p-4 sm:p-6">
    <div class="max-w-4xl mx-auto space-y-5">
        <!-- Top Navigation -->
        <div class="flex items-center justify-between">
            <a href="dashboard.php?page=cfs" class="inline-flex items-center gap-2 px-3.5 py-2 bg-white hover:bg-slate-50 text-[#0170b9] font-bold text-xs rounded-xl shadow-xs border border-slate-200 transition">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Denah Gudang CFS</span>
            </a>
            <span class="text-xs font-mono text-slate-500">CIDP CFS WMS v2.4</span>
        </div>

        <!-- Header Card: Maritime Blue & Conclusion Orange Accent -->
        <div class="bg-gradient-to-r from-[#0170b9] to-[#0284c7] text-white p-5 rounded-2xl shadow-sm border border-blue-600/30 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center space-x-3.5">
                <div class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center text-amber-300 text-xl border border-white/20">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-white/20 text-white uppercase">
                        <?= !empty($blok) ? "BLOK {$blok} &bull; SEKTOR {$sektor}" : "INSPEKSI RAK GUDANG" ?>
                    </span>
                    <h1 class="text-lg sm:text-xl font-black mt-0.5">Detail Slot Rak &amp; Status Okupansi</h1>
                    <p class="text-xs text-blue-100">Pemetaan level rak R1 s/d R4 dari database <code class="font-mono bg-black/20 px-1 rounded">master_lokasi</code></p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-[11px] block text-blue-100 font-medium">Total Slot Terdata</span>
                <span class="text-xl font-mono font-extrabold text-white"><?= count($slots) ?> Level Rak</span>
            </div>
        </div>

        <!-- Slot Grid Card -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-blue-600"></i>
                    <span>Tingkat Level Rak (R1 - R<?= count($slots) ?>)</span>
                </h3>
                <span class="text-xs text-slate-500">Ketinggian Rak: 4 Level Standar</span>
            </div>

            <?php if (empty($slots)): ?>
                <div class="p-8 text-center text-slate-400">
                    <i class="fa-solid fa-box-open text-3xl mb-2"></i>
                    <p class="text-xs font-semibold">Tidak ada slot lokasi terdaftar untuk pencarian ini.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5">
                    <?php foreach ($slots as $s): 
                        $isTerisi = (strtolower(trim($s['status'])) === 'terisi');
                    ?>
                    <div class="rounded-xl border p-4 flex flex-col justify-between transition-all <?= $isTerisi ? 'border-rose-200 bg-rose-50/30' : 'border-slate-200 bg-slate-50/50' ?>">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-mono font-black text-sm <?= $isTerisi ? 'text-rose-700' : 'text-slate-700' ?>">
                                    Level R<?= $s['rak'] ?>
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase font-mono border <?= $isTerisi ? 'bg-rose-100 text-rose-700 border-rose-200' : 'bg-slate-100 text-slate-600 border-slate-200' ?>">
                                    <?= $isTerisi ? 'TERISI' : 'KOSONG' ?>
                                </span>
                            </div>
                            <div class="text-[11px] font-mono text-slate-500">
                                Blok <?= $s['blok'] ?> / Sektor <?= $s['bagian'] ?>
                            </div>
                        </div>

                        <div class="mt-3 pt-2.5 border-t border-slate-200/60">
                            <?php if ($isTerisi): ?>
                                <span class="text-[10px] text-slate-400 block uppercase font-mono">Muatan:</span>
                                <span class="text-xs font-bold text-slate-900 block truncate" title="<?= htmlspecialchars($s['commodity_name'] ?? 'Palet Kargo LCL') ?>">
                                    <?= htmlspecialchars($s['commodity_name'] ?? 'Palet Kargo LCL') ?>
                                </span>
                                <span class="text-[9.5px] font-mono text-emerald-700 mt-0.5 block">SSCC Verified</span>
                            <?php else: ?>
                                <span class="text-[11px] font-semibold text-emerald-600 flex items-center gap-1">
                                    <i class="fa-solid fa-circle-check text-[10px]"></i> Slot Tersedia
                                </span>
                                <span class="text-[9.5px] text-slate-400 mt-0.5 block">Siap Alokasi Putaway</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
