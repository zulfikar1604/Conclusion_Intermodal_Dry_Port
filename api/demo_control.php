<?php
/**
 * Conclusion Intermodal Dry Port (CIDP)
 * Demo & Pre-Advice Control API
 * File: api/demo_control.php
 *
 * action=status          -> ringkasan kondisi (di luar / di dalam / DLQ)
 * action=list            -> daftar pre-advice (?status=SCHEDULED|ARRIVED|ALL)
 * action=register (POST) -> daftarkan pre-gate baru (booking truk + kontainer)
 * action=reset           -> kosongkan transaksi, kembalikan semua ke "di luar" + seed pre-advice
 * action=dlq             -> daftar Dead Letter Queue
 * action=dlq_resolve|dlq_discard (id)
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/twin_state_helper.php';
require_once __DIR__ . '/preadvice_helper.php';
require_once __DIR__ . '/oauth_helper.php';
global $pdo;
ensure_integration_schema($pdo);

$action = $_GET['action'] ?? ($_POST['action'] ?? 'status');

function out($d, int $code = 200) {
    http_response_code($code);
    echo json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function make_container_no(string $prefix, int $serial): string {
    $first10 = $prefix . str_pad((string)$serial, 6, '0', STR_PAD_LEFT);
    return $first10 . iso6346_check_digit($first10);
}

/** Data master pre-advice (EDI 940 dari Principal): deterministik, 30 baris */
function build_preadvice_seed(): array {
    $lines = [
        ['MSCU', 'MSC Indonesia', '42G1', '40FT HIGH CUBE'],
        ['MAEU', 'Maersk Line', '22G1', '20FT STANDARD'],
        ['CMAU', 'CMA CGM', '42G1', '40FT HIGH CUBE'],
        ['ONEU', 'Ocean Network Express', '22G1', '20FT STANDARD'],
        ['HLCU', 'Hapag-Lloyd', '45G1', '40FT HIGH CUBE'],
        ['TGHU', 'PT Samudera Logistik Prima', '22G1', '20FT STANDARD'],
    ];
    $drivers = ['Rahmat Hidayat','Slamet Riyadi','Joko Susilo','Anton Wijaya','Bambang Hermawan','Dedi Kurniawan','Wahyu Pratama','Fajar Nugroho','Gunawan Saputra','Hendra Setiawan','Irwan Maulana','Yusuf Hakim','Taufik Hidayat','Bambang Supriyadi','Agus Salim'];
    $trucking = ['PT Samudera Logistik Prima','PT Cikarang Trans Logistik','PT Bekasi Karya Angkut','PT Mitra Kontainer Nusantara','PT Jababeka Lintas Raya'];
    $blocks = ['A','B','C','D','E'];
    $anomCatalog = cidp_preadvice_anomalies_catalog();
    $rows = [];
    for ($i = 1; $i <= 30; $i++) {
        $ln = $lines[($i - 1) % count($lines)];
        $cargo = 'dry';
        $iso = $ln[2]; $size = $ln[3];
        if (in_array($i, [4, 9, 14, 19, 24], true)) { $cargo = 'reefer'; $iso = '45R1'; $size = '40FT HIGH CUBE REEFER'; }
        elseif (in_array($i, [7, 21], true)) { $cargo = 'dg'; }
        elseif (in_array($i, [12, 26, 30], true)) { $cargo = 'empty'; }
        $anom = $anomCatalog[$i] ?? null;
        $defSeal = 'SL-CIDP-' . str_pad((string)(80000 + $i), 5, '0', STR_PAD_LEFT);
        $rows[] = [
            'pre_gate_code'        => sprintf('PG-%s-%03d', date('ymd'), $i),
            'truck_plate'          => sprintf('B %d %s', 1000 + $i * 137 % 8999, ['UIX','KFA','TRB','DPL','CKR'][$i % 5] ),
            'rfid_tag'             => sprintf('E2801170000002048911%04d', $i),
            'driver_name'          => $drivers[($i - 1) % count($drivers)],
            'company'              => $trucking[($i - 1) % count($trucking)],
            'job_type'             => 'drop_off',
            'do_number'            => sprintf('DO-%s-%04d', date('Y'), 7000 + $i),
            'container_number'     => make_container_no($ln[0], 918000 + $i * 37),
            'iso_code'             => $iso,
            'size_type'            => $size,
            'cargo_type'           => $cargo,
            'owner_company'        => $ln[1],
            'target_block'         => $cargo === 'reefer' ? 'REEFER' : $blocks[($i - 1) % count($blocks)],
            'eta'                  => date('Y-m-d H:i:s', strtotime('+' . (10 + $i * 7) . ' minutes')),
            'anomaly_code'         => $anom['anomaly_code'] ?? null,
            'anomaly_title'        => $anom['anomaly_title'] ?? null,
            'anomaly_stage'        => $anom['anomaly_stage'] ?? null,
            'anomaly_notes'        => $anom['anomaly_notes'] ?? null,
            'declared_weight_kg'   => $anom['declared_weight_kg'] ?? 26500.00,
            'actual_weight_kg'     => $anom['actual_weight_kg'] ?? 26750.00,
            'seal_number'          => $anom['seal_number'] ?? $defSeal,
            'actual_seal_ocr'      => $anom['actual_seal_ocr'] ?? $defSeal,
            'actual_container_ocr' => $anom['actual_container_ocr'] ?? null,
        ];
    }
    return $rows;
}

function insert_preadvice(PDO $pdo, array $r): void {
    $pdo->prepare("INSERT INTO preadvice (pre_gate_code, truck_plate, rfid_tag, driver_name, company, job_type, do_number, container_number, iso_code, size_type, cargo_type, owner_company, target_block, eta, status, source, anomaly_code, anomaly_title, anomaly_stage, anomaly_notes, declared_weight_kg, actual_weight_kg, seal_number, actual_seal_ocr, actual_container_ocr)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'SCHEDULED', ?, ?,?,?,?,?,?,?,?,?)")
        ->execute([$r['pre_gate_code'], $r['truck_plate'], $r['rfid_tag'], $r['driver_name'], $r['company'], $r['job_type'], $r['do_number'],
            $r['container_number'], $r['iso_code'], $r['size_type'], $r['cargo_type'], $r['owner_company'], $r['target_block'], $r['eta'], $r['source'] ?? 'EDI-940',
            $r['anomaly_code'] ?? null, $r['anomaly_title'] ?? null, $r['anomaly_stage'] ?? null, $r['anomaly_notes'] ?? null,
            $r['declared_weight_kg'] ?? 26500.00, $r['actual_weight_kg'] ?? 26750.00,
            $r['seal_number'] ?? null, $r['actual_seal_ocr'] ?? null, $r['actual_container_ocr'] ?? null]);
}

function register_outside(PDO $pdo, array $r): void {
    // Truk & kontainer dibuat sebagai master data berstatus DI LUAR dry port
    $pdo->prepare("INSERT INTO trucks (license_plate, rfid_tag, driver_name, company, job_type, do_number, container_number, target_block, status) VALUES (?,?,?,?,?,?,?,?, 'scheduled')")
        ->execute([$r['truck_plate'], $r['rfid_tag'], $r['driver_name'], $r['company'], $r['job_type'], $r['do_number'], $r['container_number'], $r['target_block']]);
    $pdo->prepare("INSERT INTO containers (container_number, iso_code, size_type, cargo_type, rfid_tag, owner_company, customs_status, block, status) VALUES (?,?,?,?,?,?, 'AWAITING_CLEARANCE', 'OUTSIDE', 'expected')")
        ->execute([$r['container_number'], $r['iso_code'], $r['size_type'], $r['cargo_type'], $r['rfid_tag'], $r['owner_company']]);
}

try {
    switch ($action) {

        case 'status':
            out([
                'success' => true,
                'outside_scheduled' => (int)$pdo->query("SELECT COUNT(*) FROM preadvice WHERE status IN ('SCHEDULED','HOLD_ANOMALY')")->fetchColumn(),
                'arrived' => (int)$pdo->query("SELECT COUNT(*) FROM preadvice WHERE status='ARRIVED'")->fetchColumn(),
                'containers_in_yard' => (int)$pdo->query("SELECT COUNT(*) FROM containers WHERE status='in_yard'")->fetchColumn(),
                'dlq_open' => (int)$pdo->query("SELECT COUNT(*) FROM integration_dlq WHERE status='QUARANTINED'")->fetchColumn(),
                'dlq_buffered' => (int)$pdo->query("SELECT COUNT(*) FROM integration_dlq WHERE status='BUFFERED'")->fetchColumn(),
                'anomaly_total' => (int)$pdo->query("SELECT COUNT(*) FROM preadvice WHERE anomaly_code IS NOT NULL AND anomaly_code <> ''")->fetchColumn(),
                'hold_anomaly_count' => (int)$pdo->query("SELECT COUNT(*) FROM preadvice WHERE status='HOLD_ANOMALY'")->fetchColumn(),
                'rejected_count' => (int)$pdo->query("SELECT COUNT(*) FROM preadvice WHERE status='REJECTED'")->fetchColumn(),
                'events_logged' => (int)$pdo->query("SELECT COUNT(*) FROM yard_events")->fetchColumn(),
                'bcp' => cidp_get_bcp_state($pdo),
            ]);

        case 'list':
            $st = strtoupper($_GET['status'] ?? 'SCHEDULED');
            if ($st === 'ALL') {
                $where = '';
            } elseif ($st === 'ANOMALY') {
                $where = " WHERE anomaly_code IS NOT NULL AND anomaly_code <> ''";
            } elseif ($st === 'SCHEDULED') {
                $where = " WHERE status IN ('SCHEDULED','HOLD_ANOMALY')";
            } else {
                $where = " WHERE status = " . $pdo->quote($st);
            }
            $sql = "SELECT * FROM preadvice" . $where . " ORDER BY (status = 'HOLD_ANOMALY') DESC, id ASC";
            $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$rw) {
                $meta = !empty($rw['anomaly_code']) ? cidp_get_anomaly_meta($rw['anomaly_code']) : null;
                $rw['sop_reject']  = $meta['sop_reject'] ?? null;
                $rw['sop_resolve'] = $meta['sop_resolve'] ?? null;
            }
            unset($rw);
            out(['success' => true, 'count' => count($rows), 'data' => $rows]);

        case 'register':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(['success' => false, 'error' => 'Gunakan POST.'], 405);
            $in = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $plate = strtoupper(trim($in['truck_plate'] ?? ''));
            $cn = strtoupper(trim($in['container_number'] ?? ''));
            if ($plate === '' || $cn === '') out(['success' => false, 'error' => 'Plat truk dan nomor kontainer wajib diisi.'], 422);
            if (!iso6346_valid($cn)) out(['success' => false, 'error' => "Nomor kontainer {$cn} tidak valid (check digit ISO 6346 salah)."], 422);
            $dup = $pdo->prepare("SELECT COUNT(*) FROM preadvice WHERE status IN ('SCHEDULED','ARRIVED') AND (container_number = ? OR truck_plate = ?)");
            $dup->execute([$cn, $plate]);
            if ($dup->fetchColumn() > 0) out(['success' => false, 'error' => 'Truk atau kontainer sudah terdaftar pada pre-advice aktif.'], 409);
            $dupc = $pdo->prepare("SELECT COUNT(*) FROM containers WHERE container_number = ?");
            $dupc->execute([$cn]);
            if ($dupc->fetchColumn() > 0) out(['success' => false, 'error' => 'Kontainer sudah ada di database.'], 409);

            $n = (int)$pdo->query("SELECT COALESCE(MAX(id),0)+1 FROM preadvice")->fetchColumn();
            $r = [
                'pre_gate_code' => sprintf('PG-%s-%03d', date('ymd'), $n),
                'truck_plate' => $plate,
                'rfid_tag' => trim($in['rfid_tag'] ?? '') ?: sprintf('E2801170000002049%05d', $n),
                'driver_name' => trim($in['driver_name'] ?? '') ?: null,
                'company' => trim($in['company'] ?? '') ?: null,
                'job_type' => in_array(($in['job_type'] ?? ''), ['drop_off', 'pick_up'], true) ? $in['job_type'] : 'drop_off',
                'do_number' => trim($in['do_number'] ?? '') ?: null,
                'container_number' => $cn,
                'iso_code' => trim($in['iso_code'] ?? '') ?: '42G1',
                'size_type' => trim($in['size_type'] ?? '') ?: '40FT HIGH CUBE',
                'cargo_type' => in_array(($in['cargo_type'] ?? ''), ['dry', 'reefer', 'dg', 'empty'], true) ? $in['cargo_type'] : 'dry',
                'owner_company' => trim($in['owner_company'] ?? '') ?: null,
                'target_block' => strtoupper(trim($in['target_block'] ?? '')) ?: null,
                'eta' => date('Y-m-d H:i:s', strtotime($in['eta'] ?? '+15 minutes')),
                'source' => 'PRE-GATE-WEB',
            ];
            $pdo->beginTransaction();
            insert_preadvice($pdo, $r);
            register_outside($pdo, $r);
            $pdo->commit();
            out(['success' => true, 'message' => 'Pre-gate terdaftar. Truk berstatus di luar dry port.', 'data' => $r]);

        case 'generate_iso':
            $pfx = strtoupper(preg_replace('/[^A-Z]/', '', $_GET['prefix'] ?? 'MSCU'));
            if (strlen($pfx) !== 4) $pfx = 'MSCU';
            $ser = random_int(100000, 999999);
            $cn = make_container_no($pfx, $ser);
            out(['success' => true, 'container_number' => $cn, 'check_digit' => (int)substr($cn, -1)]);

        case 'export_json':
            $rows = $pdo->query("SELECT pre_gate_code, truck_plate, rfid_tag, driver_name, company, job_type, do_number, container_number, iso_code, size_type, cargo_type, owner_company, target_block, eta, status, source FROM preadvice ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
            out(['success' => true, 'document_type' => 'EDI-940-PREADVICE-MANIFEST', 'exported_at' => date('c'), 'count' => count($rows), 'items' => $rows]);

        case 'import_json':
            $raw = file_get_contents('php://input');
            $parsed = @json_decode($raw, true);
            $items = $parsed['items'] ?? (is_array($parsed) ? $parsed : []);
            if (empty($items) || !is_array($items)) out(['success' => false, 'error' => 'Format JSON tidak valid atau kosong. Kirim array items pre-advice.'], 422);
            $added = 0; $skipped = [];
            $pdo->beginTransaction();
            foreach ($items as $idx => $in) {
                $plate = strtoupper(trim($in['truck_plate'] ?? ''));
                $cn = strtoupper(trim($in['container_number'] ?? ''));
                if ($plate === '' || $cn === '' || !iso6346_valid($cn)) {
                    $skipped[] = "Baris #" . ($idx + 1) . " ({$plate}/{$cn}): tidak lengkap atau ISO 6346 tidak valid.";
                    continue;
                }
                $dup = $pdo->prepare("SELECT COUNT(*) FROM preadvice WHERE container_number = ? OR truck_plate = ?");
                $dup->execute([$cn, $plate]);
                if ($dup->fetchColumn() > 0) {
                    $skipped[] = "Baris #" . ($idx + 1) . " ({$plate}/{$cn}): sudah ada.";
                    continue;
                }
                $n = (int)$pdo->query("SELECT COALESCE(MAX(id),0)+1 FROM preadvice")->fetchColumn();
                $r = [
                    'pre_gate_code' => sprintf('PG-%s-%03d', date('ymd'), $n),
                    'truck_plate' => $plate,
                    'rfid_tag' => trim($in['rfid_tag'] ?? '') ?: sprintf('E2801170000002049%05d', $n),
                    'driver_name' => trim($in['driver_name'] ?? '') ?: 'Driver Ekspedisi',
                    'company' => trim($in['company'] ?? '') ?: 'PT Mitra Logistik',
                    'job_type' => in_array(($in['job_type'] ?? ''), ['drop_off', 'pick_up'], true) ? $in['job_type'] : 'drop_off',
                    'do_number' => trim($in['do_number'] ?? '') ?: sprintf('DO-%s-%04d', date('Y'), 8000 + $n),
                    'container_number' => $cn,
                    'iso_code' => trim($in['iso_code'] ?? '') ?: '42G1',
                    'size_type' => trim($in['size_type'] ?? '') ?: '40FT HIGH CUBE',
                    'cargo_type' => in_array(($in['cargo_type'] ?? ''), ['dry', 'reefer', 'dg', 'empty'], true) ? $in['cargo_type'] : 'dry',
                    'owner_company' => trim($in['owner_company'] ?? '') ?: 'Principal Line',
                    'target_block' => strtoupper(trim($in['target_block'] ?? '')) ?: 'B',
                    'eta' => date('Y-m-d H:i:s', strtotime($in['eta'] ?? '+20 minutes')),
                    'source' => 'EDI-940-JSON',
                ];
                insert_preadvice($pdo, $r);
                register_outside($pdo, $r);
                $added++;
            }
            $pdo->commit();
            out(['success' => true, 'added' => $added, 'skipped' => $skipped, 'message' => "{$added} data pre-advice berhasil diimpor dari JSON."]);

        case 'reset':
            // 1) Kosongkan semua data TRANSAKSI & master yang akan dibangun ulang
            // Gunakan DELETE FROM (bukan TRUNCATE) agar aman dari crash tablespace .ibd InnoDB di Windows XAMPP saat reset beruntun
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            foreach (['yard_events', 'billing_invoices', 'cfs_pallets', 'cfs_jobs', 'reefer_telemetry', 'trains', 'integration_dlq', 'processed_events', 'preadvice', 'trucks', 'containers'] as $t) {
                $pdo->exec("DELETE FROM `$t`");
            }
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

            // 2) Seluruh 15 armada & MHE (aset terminal) kembali idle di posisi zona operasionalnya
            $pdo->exec("UPDATE equipment SET status='idle', current_container=NULL, last_updated=NOW()");
            $stResetEq = $pdo->prepare("UPDATE equipment SET gps_x=?, gps_y=?, last_block=?, status='idle', current_container=NULL WHERE equipment_id=?");
            foreach (cidp_master_equipment_fleet() as $fm) {
                $stResetEq->execute([$fm['gps_x'], $fm['gps_y'], $fm['last_block'], $fm['equipment_id']]);
            }

            // 3) Pre-advice + truk & kontainer berstatus DI LUAR
            $pdo->beginTransaction();
            $seed = build_preadvice_seed();
            foreach ($seed as $r) { insert_preadvice($pdo, $r); register_outside($pdo, $r); }

            // 4) Seed Rangkaian Kereta Api Logistik Inbound (KA 2518) & Outbound (KA 2519)
            $stmtTrSeed = $pdo->prepare("INSERT INTO trains (train_code, origin, destination, total_wagons, loaded_wagons, status, arrival_time, departure_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $nowStr = date('Y-m-d H:i:s');
            $depStr = date('Y-m-d H:i:s', strtotime('+2 hours'));
            $stmtTrSeed->execute(['KA 2518', 'Pelabuhan Tanjung Priok (Pasoso)', 'CIDP Cikarang Hub', 30, 12, 'arrived', $nowStr, null]);
            $stmtTrSeed->execute(['KA 2519', 'CIDP Cikarang Hub', 'Pelabuhan Tanjung Priok (Pasoso)', 30, 0, 'loading', null, $depStr]);

            $pdo->commit();

            cidp_set_bcp_state(false, 'Reset database ke mode Primary Online normal.');
            @unlink(cidp_rate_cache_file());
            get_live_twin_state($pdo, true);
            out(['success' => true, 'message' => 'Database di-reset. 30 truk + kontainer terdaftar (pre-advice) dan berstatus di luar dry port.', 'preadvice_seeded' => count($seed)]);

        case 'dlq':
            $rows = $pdo->query("SELECT id, event_id, event_type, source_device, reason, retry_count, status, anomaly_code, pre_gate_code, container_number, truck_plate, resolution_action, resolution_notes, ba_number, created_at, resolved_at FROM integration_dlq ORDER BY (status IN ('QUARANTINED','BUFFERED')) DESC, id DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$dr) {
                $meta = !empty($dr['anomaly_code']) ? cidp_get_anomaly_meta($dr['anomaly_code']) : null;
                $dr['anomaly_title'] = $meta['anomaly_title'] ?? null;
                $dr['sop_reject']    = $meta['sop_reject'] ?? 'Tolak Transaksi & Terbitkan Berita Acara Penolakan Gate (Turn-Back Slip).';
                $dr['sop_resolve']   = $meta['sop_resolve'] ?? 'Verifikasi Koreksi Dokumen / Otorisasi Supervisor & Proses Ulang Transaksi.';
            }
            unset($dr);
            out(['success' => true, 'count' => count($rows), 'data' => $rows, 'bcp' => cidp_get_bcp_state($pdo)]);

        case 'dlq_resolve':
        case 'dlq_discard':
        case 'handle_anomaly':
            $in = json_decode(file_get_contents('php://input'), true) ?: array_merge($_GET, $_POST);
            $dlqId = (int)($in['id'] ?? ($in['dlq_id'] ?? 0));
            $pgCode = trim((string)($in['pre_gate_code'] ?? ''));
            $cNo = strtoupper(trim((string)($in['container_number'] ?? '')));
            $anomCode = strtoupper(trim((string)($in['anomaly_code'] ?? '')));
            $customNotes = trim((string)($in['notes'] ?? ''));

            if ($action === 'dlq_resolve') {
                $decision = 'resolve';
            } elseif ($action === 'dlq_discard') {
                $decision = 'reject';
            } else {
                $decision = strtolower(trim((string)($in['decision'] ?? 'resolve')));
            }

            // Jika dipanggil dari tabel DLQ (punya dlqId), tarik informasi pre_gate_code / container_number / anomaly_code dari baris DLQ
            $dlqRow = null;
            if ($dlqId > 0) {
                $qDlq = $pdo->prepare("SELECT * FROM integration_dlq WHERE id = ?");
                $qDlq->execute([$dlqId]);
                $dlqRow = $qDlq->fetch(PDO::FETCH_ASSOC);
                if ($dlqRow) {
                    if ($pgCode === '' && !empty($dlqRow['pre_gate_code'])) $pgCode = $dlqRow['pre_gate_code'];
                    if ($cNo === '' && !empty($dlqRow['container_number'])) $cNo = $dlqRow['container_number'];
                    if ($anomCode === '' && !empty($dlqRow['anomaly_code'])) $anomCode = $dlqRow['anomaly_code'];
                }
            }

            // Cari baris preadvice terkait
            $pre = null;
            if ($pgCode !== '') {
                $qp = $pdo->prepare("SELECT * FROM preadvice WHERE pre_gate_code = ? LIMIT 1");
                $qp->execute([$pgCode]);
                $pre = $qp->fetch(PDO::FETCH_ASSOC);
            } elseif ($cNo !== '') {
                $qp = $pdo->prepare("SELECT * FROM preadvice WHERE container_number = ? ORDER BY id DESC LIMIT 1");
                $qp->execute([$cNo]);
                $pre = $qp->fetch(PDO::FETCH_ASSOC);
            } elseif ($anomCode !== '' && $anomCode !== 'GATE_OUT_INTERLOCK_BLOCK' && $anomCode !== 'UNREGISTERED_TRUCK_REJECT') {
                $qp = $pdo->prepare("SELECT * FROM preadvice WHERE anomaly_code = ? ORDER BY (status='HOLD_ANOMALY') DESC, id ASC LIMIT 1");
                $qp->execute([$anomCode]);
                $pre = $qp->fetch(PDO::FETCH_ASSOC);
            }

            if ($pre) {
                if ($pgCode === '') $pgCode = $pre['pre_gate_code'];
                if ($cNo === '') $cNo = $pre['container_number'];
                if ($anomCode === '' && !empty($pre['anomaly_code'])) $anomCode = $pre['anomaly_code'];
            }
            $plate = $pre['truck_plate'] ?? ($dlqRow['truck_plate'] ?? ($in['truck_plate'] ?? 'B 9001 KFA'));
            $meta = cidp_get_anomaly_meta($anomCode) ?: [
                'anomaly_code'  => $anomCode ?: 'OPERATIONAL_EXCEPTION',
                'anomaly_title' => $pre['anomaly_title'] ?? 'Anomali Validasi Operasional & Dokumen',
                'sop_reject'    => 'Tolak Akses & Putar Balik Truk via Jalur Darurat Gate.',
                'sop_resolve'   => 'Koreksi Data / Otorisasi Supervisor & Lanjutkan Siklus Operasional.'
            ];

            $runLocalIngest = function (string $evType, array $payload, string $src = 'SOP-RESOLUTION-DESK') {
                $env = oauth_build_signed_envelope($evType, $payload);
                $_POST = [
                    'event_id'         => 'SOP-' . date('YmdHis') . '-' . substr(uniqid('', true), -5),
                    'source_device'    => $src,
                    'event_type'       => $evType,
                    'bearer_token'     => $env['bearer_token'],
                    'signature_hmac'   => $env['signature_hmac'],
                    'oauth_context'    => $env['oauth_context'],
                    'enforce_security' => true,
                    'payload'          => $payload
                ];
                ob_start();
                include __DIR__ . '/ingest_event.php';
                $raw = ob_get_clean();
                http_response_code(200);
                return @json_decode($raw, true);
            };

            if ($decision === 'reject' || $decision === 'turnback') {
                $baNo = 'BA-TOLAK/CIDP/' . date('Ymd') . '/' . strtoupper(substr(uniqid(), -4));
                $resNotes = $customNotes !== '' ? $customNotes : $meta['sop_reject'];

                if ($pre) {
                    $pdo->prepare("UPDATE preadvice SET status = 'REJECTED', resolution_status = 'REJECTED_TURNBACK', resolution_notes = ?, ba_number = ? WHERE id = ?")
                        ->execute([$resNotes, $baNo, $pre['id']]);
                }
                if ($plate !== '') {
                    $pdo->prepare("UPDATE trucks SET status = 'gate_out', gate_out_time = NOW() WHERE license_plate = ?")->execute([$plate]);
                }
                if ($cNo !== '' && $anomCode !== 'GATE_OUT_INTERLOCK_BLOCK') {
                    $pdo->prepare("UPDATE containers SET status = 'expected', block = 'OUTSIDE', bay = NULL, row = NULL, tier = NULL WHERE container_number = ?")->execute([$cNo]);
                }

                if ($dlqId > 0) {
                    $pdo->prepare("UPDATE integration_dlq SET status = 'DISCARDED', resolution_action = 'REJECTED_TURNBACK', resolution_notes = ?, ba_number = ?, resolved_at = NOW() WHERE id = ?")
                        ->execute([$resNotes, $baNo, $dlqId]);
                }
                if ($pgCode !== '' || $cNo !== '') {
                    $pdo->prepare("UPDATE integration_dlq SET status = 'DISCARDED', resolution_action = 'REJECTED_TURNBACK', resolution_notes = ?, ba_number = ?, resolved_at = NOW() WHERE status = 'QUARANTINED' AND ((? <> '' AND pre_gate_code = ?) OR (? <> '' AND container_number = ?))")
                        ->execute([$resNotes, $baNo, $pgCode, $pgCode, $cNo, $cNo]);
                }

                $pdo->prepare("INSERT INTO yard_events (event_type, container_number, from_block, to_block, notes, billable_amount) VALUES ('GATE_REJECT_TURNBACK', ?, 'GATE', 'OUTSIDE', ?, 0)")
                    ->execute([$cNo ?: 'UNKNOWN', "[{$baNo}] DITOLAK & PUTAR BALIK ({$meta['anomaly_code']}): {$resNotes}"]);

                get_live_twin_state($pdo, true);
                out([
                    'success'         => true,
                    'id'              => $dlqId,
                    'status'          => 'DISCARDED',
                    'decision'        => 'REJECTED_TURNBACK',
                    'ba_number'       => $baNo,
                    'anomaly_code'    => $meta['anomaly_code'],
                    'anomaly_title'   => $meta['anomaly_title'],
                    'pre_gate_code'   => $pgCode,
                    'truck_plate'     => $plate,
                    'container_number'=> $cNo,
                    'resolution_notes'=> $resNotes,
                    'message'         => "SOP Penolakan dieksekusi ({$baNo}): Truk {$plate} ({$cNo}) ditolak & diarahkan putar balik melalui Jalur Darurat Gate.",
                    'tri_flow'        => [
                        'physical'    => "Palang Masuk Tetap Terkunci; Truk {$plate} diputar balik keluar lewat Gate Turn-Back Lane.",
                        'information' => "Berita Acara Penolakan {$baNo} diterbitkan; status Pre-Advice menjadi REJECTED.",
                        'financial'   => "Tidak ada tagihan ruang penumpukan (Akses ditolak sebelum serah terima EIR)."
                    ],
                    'slip' => [
                        'ba_number'        => $baNo,
                        'decision_label'   => 'DITOLAK & PUTAR BALIK (GATE TURN-BACK REJECTION)',
                        'timestamp'        => date('d M Y H:i:s') . ' WIB',
                        'pre_gate_code'    => $pgCode ?: '-',
                        'truck_plate'      => $plate ?: '-',
                        'container_number' => $cNo ?: '-',
                        'driver_name'      => $pre['driver_name'] ?? 'Pengemudi Ekspedisi',
                        'company'          => $pre['company'] ?? 'Perusahaan Angkutan',
                        'anomaly_code'     => $meta['anomaly_code'],
                        'anomaly_title'    => $meta['anomaly_title'],
                        'action_taken'     => $resNotes,
                        'officer'          => 'Supervisor Gate & Klinik Dokumen TOC CIDP'
                    ]
                ]);
            }

            // JALUR 2: SOP PENYELESAIAN / KOREKSI DOKUMEN / REVISI VGM / RESEAL / OVERRIDE
            $baNo = 'BA-PULIH/CIDP/' . date('Ymd') . '/' . strtoupper(substr(uniqid(), -4));
            $resNotes = $customNotes !== '' ? $customNotes : $meta['sop_resolve'];
            $resumeRes = null;

            if ($pre) {
                $newPreStatus = ($pre['status'] === 'HOLD_ANOMALY' || $pre['status'] === 'REJECTED') ? 'SCHEDULED' : $pre['status'];
                $pdo->prepare("UPDATE preadvice SET status = ?, resolution_status = 'OVERRIDDEN_APPROVED', resolution_notes = ?, ba_number = ? WHERE id = ?")
                    ->execute([$newPreStatus, $resNotes, $baNo, $pre['id']]);
            }

            if ($anomCode === 'DOC_DO_EXPIRED' && $pre) {
                $newDo = 'DO-EXT-' . date('Y') . '-' . random_int(9000, 9999);
                $pdo->prepare("UPDATE preadvice SET do_number = ?, status = 'SCHEDULED', resolution_status = 'OVERRIDDEN_APPROVED' WHERE id = ?")->execute([$newDo, $pre['id']]);
                $resumeRes = $runLocalIngest('TRUCK_GATE_ARRIVAL', [
                    'pre_gate_code'    => $pre['pre_gate_code'],
                    'truck_plate'      => $pre['truck_plate'],
                    'container_number' => $pre['container_number'],
                    'rfid_tag'         => $pre['rfid_tag']
                ], 'SOP-CLINIC-GATE-01');
            } elseif (($anomCode === 'CONTAINER_MISMATCH' || $anomCode === 'OCR_CHECKDIGIT_ERROR') && $pre) {
                $pdo->prepare("UPDATE preadvice SET actual_container_ocr = container_number, status = 'SCHEDULED', resolution_status = 'OVERRIDDEN_APPROVED' WHERE id = ?")->execute([$pre['id']]);
                $resumeRes = $runLocalIngest('TRUCK_GATE_ARRIVAL', [
                    'pre_gate_code'    => $pre['pre_gate_code'],
                    'truck_plate'      => $pre['truck_plate'],
                    'container_number' => $pre['container_number'],
                    'rfid_tag'         => $pre['rfid_tag']
                ], 'SOP-OCR-VERIFIER');
            } elseif ($anomCode === 'SEAL_TAMPERED' && $pre) {
                $newSeal = 'RESEAL-CIDP-' . random_int(10000, 99999);
                $pdo->prepare("UPDATE preadvice SET seal_number = ?, actual_seal_ocr = ?, target_block = 'E', status = 'SCHEDULED', resolution_status = 'OVERRIDDEN_APPROVED' WHERE id = ?")
                    ->execute([$newSeal, $newSeal, $pre['id']]);
                $resumeRes = $runLocalIngest('TRUCK_GATE_ARRIVAL', [
                    'pre_gate_code'    => $pre['pre_gate_code'],
                    'truck_plate'      => $pre['truck_plate'],
                    'container_number' => $pre['container_number'],
                    'rfid_tag'         => $pre['rfid_tag']
                ], 'SOP-EIR-RESEAL');
            } elseif ($anomCode === 'VGM_OVERWEIGHT' && $pre) {
                $actW = (float)($pre['actual_weight_kg'] ?: 39250.0);
                $pdo->prepare("UPDATE preadvice SET declared_weight_kg = ?, resolution_status = 'OVERRIDDEN_APPROVED' WHERE id = ?")
                    ->execute([$actW, $pre['id']]);
                $ckSt = $pdo->prepare("SELECT status FROM containers WHERE container_number = ?");
                $ckSt->execute([$pre['container_number']]);
                if ($ckSt->fetchColumn() === 'expected' || $pre['status'] !== 'ARRIVED') {
                    $pdo->prepare("UPDATE preadvice SET status = 'SCHEDULED' WHERE id = ?")->execute([$pre['id']]);
                    $runLocalIngest('TRUCK_GATE_ARRIVAL', [
                        'pre_gate_code'    => $pre['pre_gate_code'],
                        'truck_plate'      => $pre['truck_plate'],
                        'container_number' => $pre['container_number'],
                        'rfid_tag'         => $pre['rfid_tag']
                    ], 'SOP-VGM-GATE-IN');
                }
                accrue_billing($pdo, $pre['container_number'], 'Biaya Revisi Sertifikat SOLAS VGM & Timbang Ulang', 'lolo_amount', 150000.0);
                $resumeRes = $runLocalIngest('TRUCK_WEIGHBRIDGE', [
                    'truck_plate'      => $pre['truck_plate'],
                    'container_number' => $pre['container_number'],
                    'weight_gross'     => $actW
                ], 'SOP-VGM-AMENDMENT');
            } elseif ($anomCode === 'CUSTOMS_DOC_MISMATCH' && $cNo !== '') {
                $ckSt = $pdo->prepare("SELECT status FROM containers WHERE container_number = ?");
                $ckSt->execute([$cNo]);
                if ($ckSt->fetchColumn() !== 'in_yard' && $pre) {
                    $pdo->prepare("UPDATE preadvice SET status = 'SCHEDULED', resolution_status = 'OVERRIDDEN_APPROVED' WHERE id = ?")->execute([$pre['id']]);
                    $runLocalIngest('TRUCK_GATE_ARRIVAL', ['pre_gate_code' => $pre['pre_gate_code'], 'truck_plate' => $pre['truck_plate'], 'container_number' => $cNo, 'rfid_tag' => $pre['rfid_tag']], 'GATE-IN-LANE-01');
                    $runLocalIngest('TRUCK_WEIGHBRIDGE', ['truck_plate' => $pre['truck_plate'], 'container_number' => $cNo, 'weight_gross' => 29150.0], 'WEIGHBRIDGE-80T');
                    $runLocalIngest('CONTAINER_LIFT', ['equipment_id' => 'RS-02', 'truck_plate' => $pre['truck_plate'], 'container_number' => $cNo], 'KALMAR-DRG450-RS02');
                    $runLocalIngest('CONTAINER_STACK', ['equipment_id' => 'RS-02', 'container_number' => $cNo, 'block' => 'E', 'bay' => '02', 'row' => '01', 'tier' => '01'], 'KALMAR-DRG450-RS02');
                }
                $runLocalIngest('CUSTOMS_INSPECTION', [
                    'container_number' => $cNo,
                    'inspection_type'  => 'PHYSICAL_BEHANDLE',
                    'result'           => 'CLEAR',
                    'notes'            => "Nota Pembetulan BC 2.3 & Pemeriksaan Fisik Blok E Selesai ({$baNo})"
                ], 'DJBC-BEHANDLE-E');
                $resumeRes = $runLocalIngest('CUSTOMS_CEISA', [
                    'container_number'    => $cNo,
                    'customs_status'      => 'SPPB_CLEARED',
                    'force_sppb_override' => true,
                    'sppb_number'         => 'SPPB-REV-' . date('Ymd') . '/' . substr($cNo, 4, 4)
                ], 'DJBC-CEISA-H2H');
            } elseif ($anomCode === 'GATE_OUT_INTERLOCK_BLOCK' && $cNo !== '') {
                $runLocalIngest('PAYMENT_SETTLED', ['container_number' => $cNo], 'BANK-MANDIRI-H2H');
                $runLocalIngest('CUSTOMS_CEISA', ['container_number' => $cNo, 'customs_status' => 'SPPB_CLEARED', 'force_sppb_override' => true], 'DJBC-CEISA-H2H');
                $resumeRes = $runLocalIngest('TRUCK_GATE_OUT', ['container_number' => $cNo], 'GATE-OUT-LANE-01');
            } elseif ($pre && $pre['status'] !== 'ARRIVED') {
                $resumeRes = $runLocalIngest('TRUCK_GATE_ARRIVAL', [
                    'pre_gate_code'    => $pre['pre_gate_code'],
                    'truck_plate'      => $pre['truck_plate'],
                    'container_number' => $pre['container_number'],
                    'rfid_tag'         => $pre['rfid_tag']
                ], 'SOP-RESOLUTION-DESK');
            }

            if ($dlqId > 0) {
                $pdo->prepare("UPDATE integration_dlq SET status = 'RESOLVED', resolution_action = 'SOP_OVERRIDE_APPROVED', resolution_notes = ?, ba_number = ?, resolved_at = NOW() WHERE id = ?")
                    ->execute([$resNotes, $baNo, $dlqId]);
            }
            if ($pgCode !== '' || $cNo !== '') {
                $pdo->prepare("UPDATE integration_dlq SET status = 'RESOLVED', resolution_action = 'SOP_OVERRIDE_APPROVED', resolution_notes = ?, ba_number = ?, resolved_at = NOW() WHERE status = 'QUARANTINED' AND ((? <> '' AND pre_gate_code = ?) OR (? <> '' AND container_number = ?))")
                    ->execute([$resNotes, $baNo, $pgCode, $pgCode, $cNo, $cNo]);
            }

            $pdo->prepare("INSERT INTO yard_events (event_type, container_number, from_block, to_block, notes, billable_amount) VALUES ('ANOMALY_RESOLVED', ?, 'HOLD', 'GATE', ?, 0)")
                ->execute([$cNo ?: 'UNKNOWN', "[{$baNo}] DISELESAIKAN SESUAI SOP ({$meta['anomaly_code']}): {$resNotes}"]);

            get_live_twin_state($pdo, true);
            out([
                'success'          => true,
                'id'               => $dlqId,
                'status'           => 'RESOLVED',
                'decision'         => 'OVERRIDDEN_APPROVED',
                'ba_number'        => $baNo,
                'anomaly_code'     => $meta['anomaly_code'],
                'anomaly_title'    => $meta['anomaly_title'],
                'pre_gate_code'    => $pgCode,
                'truck_plate'      => $plate,
                'container_number' => $cNo,
                'resolution_notes' => $resNotes,
                'resume_result'    => $resumeRes,
                'message'          => "SOP Penyelesaian Berhasil ({$baNo}): Masalah {$meta['anomaly_code']} pada {$plate} ({$cNo}) telah diatasi & transaksi dilanjutkan ke terminal.",
                'tri_flow'         => $resumeRes['tri_flow'] ?? [
                    'physical'    => "Interlock dibuka pasca-otorisasi {$baNo}; truk/kontainer {$cNo} melanjutkan pergerakan fisik.",
                    'information' => "Data dokumen/timbangan disinkronkan ({$resNotes}); status DLQ menjadi RESOLVED.",
                    'financial'   => "Biaya administrasi / amandemen tercatat di buku besar tagihan."
                ],
                'slip' => [
                    'ba_number'        => $baNo,
                    'decision_label'   => 'DISELESAIKAN / OVERRIDE SESUAI SOP (CLEARED TO PROCEED)',
                    'timestamp'        => date('d M Y H:i:s') . ' WIB',
                    'pre_gate_code'    => $pgCode ?: '-',
                    'truck_plate'      => $plate ?: '-',
                    'container_number' => $cNo ?: '-',
                    'driver_name'      => $pre['driver_name'] ?? 'Pengemudi Ekspedisi',
                    'company'          => $pre['company'] ?? 'Perusahaan Angkutan',
                    'anomaly_code'     => $meta['anomaly_code'],
                    'anomaly_title'    => $meta['anomaly_title'],
                    'action_taken'     => $resNotes,
                    'officer'          => 'Supervisor Gate & Klinik Dokumen TOC CIDP'
                ]
            ]);

        case 'bcp_status':
            out(['success' => true, 'bcp' => cidp_get_bcp_state($pdo)]);

        case 'bcp_toggle':
            $cur = cidp_get_bcp_state($pdo);
            $modeParam = strtolower(trim((string)($_GET['mode'] ?? ($_POST['mode'] ?? ''))));
            if ($modeParam === 'failover' || $modeParam === 'buffer') {
                $nextActive = true;
            } elseif ($modeParam === 'normal' || $modeParam === 'primary') {
                $nextActive = false;
            } else {
                $nextActive = !$cur['failover_active'];
            }
            $reason = $nextActive
                ? 'Simulasi Kontingensi BCP Aktif: Link Cloud/ERP terputus — event dialihkan ke Edge Store-and-Forward Buffer (Zero Data Loss).'
                : 'Pemulihan Primary Online: Koneksi Cloud/ERP kembali aktif siap menerima sinkronisasi.';
            cidp_set_bcp_state($nextActive, $reason);
            try {
                $pdo->prepare("INSERT INTO yard_events (event_type, operator_name, notes, created_at) VALUES (?, 'BCP Failover Controller', ?, NOW())")
                    ->execute([$nextActive ? 'BCP_FAILOVER_ON' : 'BCP_PRIMARY_RESTORE', $reason]);
            } catch (Throwable $e) {}
            out(['success' => true, 'bcp' => cidp_get_bcp_state($pdo), 'message' => $reason]);

        case 'bcp_flush':
            $t0 = microtime(true);
            cidp_set_bcp_state(false, 'Primary pulih & antrean Edge Buffer sedang disinkronkan ulang (Zero Data Loss).');
            $bufRows = $pdo->query("SELECT * FROM integration_dlq WHERE status = 'BUFFERED' ORDER BY id ASC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
            $resolved = 0; $failed = 0; $details = [];
            foreach ($bufRows as $br) {
                $orig = @json_decode((string)$br['payload_json'], true) ?: [];
                $evType = strtoupper((string)($br['event_type'] ?: ($orig['event_type'] ?? 'EQUIPMENT_MOVE')));
                $evPayload = $orig['payload'] ?? $orig;
                if (!is_array($evPayload)) $evPayload = [];
                $env = oauth_build_signed_envelope($evType, $evPayload);
                $_POST = [
                    'event_id'         => $br['event_id'] ?: ('RPL-' . $br['id'] . '-' . date('His')),
                    'source_device'    => $br['source_device'] ?: 'EDGE-BCP-REPLAY',
                    'event_type'       => $evType,
                    'bearer_token'     => $env['bearer_token'],
                    'signature_hmac'   => $env['signature_hmac'],
                    'enforce_security' => true,
                    'bcp_replay'       => true,
                    'payload'          => $evPayload
                ];
                ob_start();
                include __DIR__ . '/ingest_event.php';
                $rawOut = ob_get_clean();
                http_response_code(200);
                $res = @json_decode($rawOut, true);
                if (!empty($res['success'])) {
                    $pdo->prepare("UPDATE integration_dlq SET status = 'RESOLVED', retry_count = retry_count + 1, resolved_at = NOW() WHERE id = ?")->execute([$br['id']]);
                    $resolved++;
                    $details[] = ['dlq_id' => (int)$br['id'], 'event_id' => $br['event_id'], 'event_type' => $evType, 'status' => 'RESOLVED'];
                } else {
                    $pdo->prepare("UPDATE integration_dlq SET retry_count = retry_count + 1 WHERE id = ?")->execute([$br['id']]);
                    $failed++;
                    $details[] = ['dlq_id' => (int)$br['id'], 'event_id' => $br['event_id'], 'event_type' => $evType, 'status' => 'RETRY_FAILED', 'error' => $res['error'] ?? 'Unknown'];
                }
            }
            $elapsedMs = round((microtime(true) - $t0) * 1000, 1);
            if ($resolved > 0) {
                try {
                    $pdo->prepare("INSERT INTO yard_events (event_type, operator_name, notes, created_at) VALUES ('BCP_BUFFER_FLUSH', 'BCP Recovery Engine', ?, NOW())")
                        ->execute(["Flush Edge Buffer selesai: {$resolved} transaksi dipulihkan penuh (RPO=0) dalam {$elapsedMs} ms."]);
                } catch (Throwable $e) {}
            }
            get_live_twin_state($pdo, true);
            out([
                'success' => true,
                'replayed' => count($bufRows),
                'resolved' => $resolved,
                'failed' => $failed,
                'elapsed_ms' => $elapsedMs,
                'rpo_seconds' => 0,
                'bcp' => cidp_get_bcp_state($pdo),
                'details' => $details,
                'message' => $resolved > 0
                    ? "{$resolved} event dari Edge Store-and-Forward Buffer berhasil dipulihkan (RPO = 0 dalam {$elapsedMs} ms)."
                    : 'Antrean Edge Buffer sudah bersih (0 event tertunda).'
            ]);

        default:
            out(['success' => false, 'error' => 'Aksi tidak dikenal.'], 400);
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    out(['success' => false, 'error' => $e->getMessage()], 500);
}
