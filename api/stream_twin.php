<?php
/**
 * Conclusion Intermodal Dry Port (CIDP)
 * Server-Sent Events (SSE) Stream Endpoint for 2D Digital Twin
 * File: api/stream_twin.php
 */

require_once __DIR__ . '/twin_state_helper.php';

// Support simple one-shot JSON polling fallback
if (isset($_GET['poll']) && $_GET['poll'] == '1') {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    $force = isset($_GET['force']) && $_GET['force'] == '1';
    echo json_encode(get_live_twin_state($pdo, $force));
    exit;
}

// Disable all output buffering
if (function_exists('apache_setenv')) {
    @apache_setenv('no-gzip', '1');
}
@ini_set('zlib.output_compression', '0');
@ini_set('implicit_flush', '1');
while (ob_get_level()) {
    ob_end_flush();
}
ob_implicit_flush(1);

// Set SSE Headers
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');
header('Access-Control-Allow-Origin: *');

set_time_limit(60);
ignore_user_abort(false);

$cache_file = TWIN_CACHE_FILE;
$last_mtime = file_exists($cache_file) ? filemtime($cache_file) : 0;

// 1. Send initial state immediately
$initial_state = get_live_twin_state($pdo, false);
echo "event: initial_state\n";
echo "id: " . uniqid('msg_') . "\n";
echo "data: " . json_encode($initial_state) . "\n\n";
flush();

$start_time = time();
$last_heartbeat = time();

// 2. Event loop
while (!connection_aborted()) {
    clearstatcache(false, $cache_file);
    $current_mtime = file_exists($cache_file) ? filemtime($cache_file) : 0;

    // Detected state change
    if ($current_mtime > $last_mtime) {
        $last_mtime = $current_mtime;
        $content = @file_get_contents($cache_file);
        if ($content) {
            echo "event: state_update\n";
            echo "id: " . uniqid('msg_') . "\n";
            echo "data: " . $content . "\n\n";
            flush();
        }
    }

    // Periodic heartbeat every 4 seconds
    if (time() - $last_heartbeat >= 4) {
        $last_heartbeat = time();
        echo "event: heartbeat\n";
        echo "data: {\"time\":\"" . date('c') . "\",\"status\":\"ONLINE\",\"clients\":1}\n\n";
        flush();
    }

    // Graceful cycle renewal after 50 seconds (avoids Apache timeout killing process)
    if (time() - $start_time >= 50) {
        echo "event: reconnect_advisory\n";
        echo "data: {\"message\":\"cycle_refresh\"}\n\n";
        flush();
        break;
    }

    usleep(500000); // 500 ms check interval
}
