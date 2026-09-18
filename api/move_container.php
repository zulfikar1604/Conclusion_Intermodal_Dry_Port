<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$container_id = $data['container_id'] ?? null;
$equipment_id = $data['equipment_id'] ?? null;
$to_block = $data['to_block'] ?? null;
$to_bay = $data['to_bay'] ?? null;
$to_row = $data['to_row'] ?? null;
$to_tier = $data['to_tier'] ?? null;

if (!$container_id || !$equipment_id || !$to_block || !$to_bay || !$to_row || !$to_tier) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Get current container position
    $stmt = $pdo->prepare("SELECT container_number, block, bay, row, tier FROM containers WHERE id = ?");
    $stmt->execute([$container_id]);
    $container = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$container) {
        throw new Exception("Container not found");
    }

    // 2. Check if destination slot is empty
    $stmt = $pdo->prepare("SELECT id FROM containers WHERE block = ? AND bay = ? AND row = ? AND tier = ?");
    $stmt->execute([$to_block, $to_bay, $to_row, $to_tier]);
    if ($stmt->fetch()) {
        throw new Exception("Destination slot is not empty");
    }

    // 3. Update container position
    $stmt = $pdo->prepare("UPDATE containers SET block = ?, bay = ?, row = ?, tier = ? WHERE id = ?");
    $stmt->execute([$to_block, $to_bay, $to_row, $to_tier, $container_id]);

    // 4. Insert yard_event log
    $stmt = $pdo->prepare("INSERT INTO yard_events (event_type, container_number, equipment_id, from_block, from_bay, from_row, from_tier, to_block, to_bay, to_row, to_tier, operator_name, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Get operator name for event log
    $opStmt = $pdo->prepare("SELECT operator_name FROM equipment WHERE equipment_id = ?");
    $opStmt->execute([$equipment_id]);
    $equipment = $opStmt->fetch(PDO::FETCH_ASSOC);
    $operator_name = $equipment ? $equipment['operator_name'] : 'Unknown';

    $stmt->execute([
        'RELOCATION',
        $container['container_number'],
        $equipment_id,
        $container['block'],
        $container['bay'],
        $container['row'],
        $container['tier'],
        $to_block,
        $to_bay,
        $to_row,
        $to_tier,
        $operator_name,
        'API Relocation'
    ]);
    $event_id = $pdo->lastInsertId();

    // 5. Update equipment status and position
    $stmt = $pdo->prepare("UPDATE equipment SET status = 'operating', last_block = ?, current_container = NULL WHERE equipment_id = ?");
    $stmt->execute([$to_block, $equipment_id]);

    $pdo->commit();

    $from = $container['block'] . '-' . $container['bay'] . '-' . $container['row'] . '-' . $container['tier'];
    $to = $to_block . '-' . $to_bay . '-' . $to_row . '-' . $to_tier;

    echo json_encode([
        'success' => true,
        'message' => "Kontainer {$container['container_number']} berhasil dipindahkan ke {$to}",
        'event_id' => $event_id,
        'from' => $from,
        'to' => $to
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
