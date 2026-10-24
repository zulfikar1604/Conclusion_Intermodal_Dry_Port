<?php
// =============================================================================
// FILE: components/exec_status.php
// FUNGSI: Strip Status Eksekutif global — menjawab "Apakah operasi sehat?"
//         dalam 5 detik di bagian paling atas SETIAP halaman DPMS.
//         Seluruh angka dihitung langsung dari database (bukan dummy):
//         - Kontainer tertahan Bea Cukai (DOC_HOLD / RED_LANE_CHECK)
//         - Okupansi yard terhadap kapasitas
//         - Risiko demurrage (kontainer di yard > 5 hari)
//         - Invoice belum lunas (PENDING)
//         - Antrean truk di gate (at_gate)
// Dibutuhkan: $pdo (connection.php), $current_lang (config/i18n.php)
// =============================================================================

$__lang = $current_lang ?? 'id';
$__en   = ($__lang === 'en');

$__alerts = [];
$__yard_cap = 12500; // Kapasitas desain stacking yard (TEU) — sama dengan api/beranda_telemetry.php

if (isset($pdo) && $pdo instanceof PDO) {
    try {
        // 1) Hold Bea Cukai
        $n = (int)$pdo->query("SELECT COUNT(*) FROM containers WHERE status != 'gate_out' AND customs_status IN ('DOC_HOLD','RED_LANE_CHECK')")->fetchColumn();
        if ($n > 0) {
            $__alerts[] = [
                'sev'   => $n >= 5 ? 'critical' : 'high',
                'count' => $n,
                'label' => $__en ? 'containers on customs hold' : 'kontainer tertahan Bea Cukai',
                'icon'  => 'fa-shield-halved',
                'href'  => 'dashboard.php?page=customs',
            ];
        }

        // 2) Risiko demurrage: di yard > 5 hari
        $n = (int)$pdo->query("SELECT COUNT(*) FROM containers WHERE status = 'in_yard' AND gate_in_time IS NOT NULL AND gate_in_time < (NOW() - INTERVAL 5 DAY)")->fetchColumn();
        if ($n > 0) {
            $__alerts[] = [
                'sev'   => $n >= 5 ? 'high' : 'medium',
                'count' => $n,
                'label' => $__en ? 'boxes at demurrage risk (>5 days)' : 'box berisiko demurrage (>5 hari)',
                'icon'  => 'fa-hourglass-half',
                'href'  => 'dashboard.php?page=yard',
            ];
        }

        // 3) Invoice belum lunas
        $row = $pdo->query("SELECT COUNT(*) n, COALESCE(SUM(total_amount),0) s FROM billing_invoices WHERE payment_status = 'PENDING'")->fetch(PDO::FETCH_ASSOC);
        if ($row && (int)$row['n'] > 0) {
            $amt = (float)$row['s'];
            $amtTxt = $amt >= 1000000 ? 'Rp ' . number_format($amt / 1000000, 1, ',', '.') . ($__en ? 'M' : ' Jt') : 'Rp ' . number_format($amt, 0, ',', '.');
            $__alerts[] = [
                'sev'   => 'medium',
                'count' => (int)$row['n'],
                'label' => ($__en ? 'unpaid invoices' : 'invoice belum lunas') . ' (' . $amtTxt . ')',
                'icon'  => 'fa-file-invoice-dollar',
                'href'  => 'dashboard.php?page=billing',
            ];
        }

        // 4) Antrean truk di gate
        $n = (int)$pdo->query("SELECT COUNT(*) FROM trucks WHERE status = 'at_gate'")->fetchColumn();
        if ($n > 0) {
            $__alerts[] = [
                'sev'   => $n >= 6 ? 'high' : 'medium',
                'count' => $n,
                'label' => $__en ? 'trucks queuing at gate' : 'truk antre di gate',
                'icon'  => 'fa-truck-fast',
                'href'  => 'dashboard.php?page=gate',
            ];
        }

        // 5) Okupansi yard
        $inYard = (int)$pdo->query("SELECT COUNT(*) FROM containers WHERE status = 'in_yard'")->fetchColumn();
        $occ = $__yard_cap > 0 ? round($inYard / $__yard_cap * 100, 1) : 0;
        if ($occ >= 85) {
            $__alerts[] = [
                'sev'   => $occ >= 92 ? 'critical' : 'high',
                'count' => $occ . '%',
                'label' => $__en ? 'yard occupancy' : 'okupansi yard',
                'icon'  => 'fa-boxes-stacked',
                'href'  => 'dashboard.php?page=yard',
            ];
        }
    } catch (Throwable $e) {
        // Strip tetap tampil (status netral) bila salah satu tabel tidak tersedia
    }
}

// Urutkan dari yang paling kritis
$__rank = ['critical' => 0, 'high' => 1, 'medium' => 2];
usort($__alerts, function ($a, $b) use ($__rank) { return $__rank[$a['sev']] <=> $__rank[$b['sev']]; });

$__has_critical = (bool)array_filter($__alerts, fn($a) => $a['sev'] === 'critical');
$__level = $__has_critical ? 'kritis' : (count($__alerts) > 0 ? 'perhatian' : 'sehat');
$__n     = count($__alerts);

$__verdict = [
    'sehat'     => [$__en ? 'Operations healthy' : 'Operasi sehat', 'fa-circle-check'],
    'perhatian' => [$__en ? 'Operations running — needs attention' : 'Operasi berjalan — perlu perhatian', 'fa-triangle-exclamation'],
    'kritis'    => [$__en ? 'Critical — immediate action required' : 'Kritis — perlu tindakan segera', 'fa-circle-exclamation'],
][$__level];

$__sub = $__n === 0
    ? ($__en ? 'No open issues. All KPIs within target.' : 'Tidak ada isu terbuka. Seluruh KPI dalam target.')
    : ($__en ? $__n . ' item' . ($__n > 1 ? 's' : '') . ' need attention today' : $__n . ' hal butuh perhatian hari ini');

$__shown = array_slice($__alerts, 0, 3);
$__rest  = array_slice($__alerts, 3);
?>
<section id="execStatusStrip" class="exec-status" data-level="<?= $__level ?>" role="status" aria-live="polite">
    <div class="exec-status__verdict">
        <div class="exec-status__icon"><i class="fa-solid <?= $__verdict[1] ?>"></i></div>
        <div class="min-w-0">
            <div class="exec-status__title"><?= htmlspecialchars($__verdict[0]) ?></div>
            <div class="exec-status__sub"><?= htmlspecialchars($__sub) ?> &middot; <?= date('H:i') ?> WIB</div>
        </div>
    </div>

    <div class="exec-status__items">
        <?php if ($__n === 0): ?>
            <span class="exec-status__item" data-sev="ok"><i class="fa-solid fa-check"></i><?= $__en ? 'All clear' : 'Semua aman' ?></span>
        <?php endif; ?>
        <?php foreach ($__shown as $a): ?>
            <a href="<?= htmlspecialchars($a['href']) ?>" class="exec-status__item" data-sev="<?= $a['sev'] ?>" title="<?= $__en ? 'Open module' : 'Buka modul terkait' ?>">
                <span class="count"><?= htmlspecialchars((string)$a['count']) ?></span>
                <span><?= htmlspecialchars($a['label']) ?></span>
                <i class="fa-solid fa-arrow-right text-[10px] opacity-60"></i>
            </a>
        <?php endforeach; ?>
        <?php if (!empty($__rest)): ?>
            <!-- Alert Tambahan yang Dapat Diklik & Diperluas -->
            <?php foreach ($__rest as $a): ?>
                <a href="<?= htmlspecialchars($a['href']) ?>" class="exec-status__item exec-status__rest hidden" data-sev="<?= $a['sev'] ?>" title="<?= $__en ? 'Open module' : 'Buka modul terkait' ?>">
                    <span class="count"><?= htmlspecialchars((string)$a['count']) ?></span>
                    <span><?= htmlspecialchars($a['label']) ?></span>
                    <i class="fa-solid fa-arrow-right text-[10px] opacity-60"></i>
                </a>
            <?php endforeach; ?>
            <button type="button" onclick="toggleExecStatusMore(this)" class="exec-status__item cursor-pointer hover:opacity-90 transition-all select-none font-bold" data-sev="medium" data-expanded="0" data-text-more="+<?= count($__rest) ?> <?= $__en ? 'more' : 'lainnya' ?>" data-text-less="<?= $__en ? '− Less' : '− Tutup' ?>" title="<?= $__en ? 'Click to show all alerts' : 'Klik untuk melihat alert lainnya' ?>">
                +<?= count($__rest) ?> <?= $__en ? 'more' : 'lainnya' ?>
            </button>
        <?php endif; ?>
    </div>

    <button type="button" id="execStatusToggleBtn" class="exec-status__toggle" onclick="toggleExecStatus()" title="<?= $__en ? 'Collapse / expand' : 'Ringkas / tampilkan' ?>">
        <i class="fa-solid fa-chevron-up"></i>
    </button>
</section>

<script>
window.toggleExecStatusMore = function(btn) {
    if (!btn) return;
    var strip = btn.closest('#execStatusStrip') || document.getElementById('execStatusStrip');
    if (!strip) return;
    var restItems = strip.querySelectorAll('.exec-status__rest');
    var isExpanded = btn.getAttribute('data-expanded') === '1';

    restItems.forEach(function(el) {
        if (isExpanded) {
            el.classList.add('hidden');
        } else {
            el.classList.remove('hidden');
        }
    });

    if (isExpanded) {
        btn.setAttribute('data-expanded', '0');
        btn.innerHTML = btn.getAttribute('data-text-more');
        btn.title = '<?= $__en ? "Click to show all alerts" : "Klik untuk melihat alert lainnya" ?>';
    } else {
        btn.setAttribute('data-expanded', '1');
        btn.innerHTML = btn.getAttribute('data-text-less');
        btn.title = '<?= $__en ? "Click to hide" : "Klik untuk meringkas" ?>';
    }
};
</script>
