<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../connection.php';

try {
    $data = [];

    // 1. Get containers in yard
    $stmt = $pdo->query("SELECT * FROM containers WHERE status = 'in_yard'");
    $data['containers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Get equipment
    $stmt = $pdo->query("SELECT * FROM equipment");
    $data['equipment'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Get recent events
    $stmt = $pdo->query("SELECT * FROM yard_events ORDER BY created_at DESC LIMIT 10");
    $data['recent_events'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Get stats
    $stats = [];
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM containers WHERE status = 'in_yard'");
    $stats['total_containers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt = $pdo->query("SELECT cargo_type, COUNT(*) as count FROM containers WHERE status = 'in_yard' GROUP BY cargo_type");
    $cargo_counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $stats['total_dry'] = $cargo_counts['dry'] ?? 0;
    $stats['total_reefer'] = $cargo_counts['reefer'] ?? 0;
    $stats['total_dg'] = $cargo_counts['dg'] ?? 0;
    $stats['total_empty'] = $cargo_counts['empty'] ?? 0;
    
    // Assume max capacity is 500 for demo utilization
    $stats['yard_utilization'] = round(($stats['total_containers'] / 500) * 100);

    $data['stats'] = $stats;

    echo json_encode($data);

} catch (PDOException $e) {
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
