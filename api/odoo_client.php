<?php
// =============================================================================
// API GATEWAY: ODOO ERP JSON-RPC LIVE CLIENT (REAL CONNECTION)
// File: api/odoo_client.php
// CIDP Yard Management System — PT Multi Terminal Indonesia / ITL Trisakti
// PIC : Afriansayah Ayubi (Software & ERP Process Specialist)
// Koneksi: conclusion-intermodal-dry-port.odoo.com (Odoo Enterprise saas~19.4+e)
// =============================================================================

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/connection.php';

api_init_headers();

// Konfigurasi Odoo Cloud dari config/app.php (dapat di-override via request jika diperlukan)
$odoo_host = defined('ODOO_DEFAULT_HOST') ? ODOO_DEFAULT_HOST : 'https://conclusion-intermodal-dry-port.odoo.com';
$odoo_db   = defined('ODOO_DEFAULT_DB')   ? ODOO_DEFAULT_DB   : 'conclusion-intermodal-dry-port';
$odoo_user = defined('ODOO_DEFAULT_USER') ? ODOO_DEFAULT_USER : 'zulfikarjafarudinfatah@gmail.com';
$odoo_pass = defined('ODOO_DEFAULT_PASS') ? ODOO_DEFAULT_PASS : '@Zulfikar123';

$json_input = get_json_input();
$action = $json_input['action'] ?? 'ping';

/**
 * HTTP POST helper (JSON-RPC)
 */
function odooPost($url, $payload, $cookies = '', $timeout = 10) {
    $json = json_encode($payload);
    $headers = "Content-Type: application/json\r\n" .
               "User-Agent: CIDP-YMS-Gateway/2.0\r\n";
    if ($cookies) {
        $headers .= "Cookie: $cookies\r\n";
    }

    $opts = [
        'http' => [
            'method'        => 'POST',
            'header'        => $headers,
            'content'       => $json,
            'timeout'       => $timeout,
            'ignore_errors' => true
        ],
        'ssl' => ['verify_peer' => true, 'verify_peer_name' => true]
    ];

    $ctx = stream_context_create($opts);
    $start = microtime(true);
    $resp = @file_get_contents($url, false, $ctx);
    $ms = round((microtime(true) - $start) * 1000, 2);

    // Ambil session_id dari header Set-Cookie
    $session_id = '';
    if (isset($http_response_header)) {
        foreach ($http_response_header as $h) {
            if (preg_match('/^Set-Cookie:\s*session_id=([^;]+)/i', $h, $m)) {
                $session_id = $m[1];
            }
        }
    }

    return [
        'latency_ms' => $ms,
        'raw'        => $resp,
        'data'       => json_decode($resp, true),
        'session_id' => $session_id
    ];
}

/**
 * Authenticate ke Odoo dan dapatkan session cookie
 */
function odooAuthenticate($host, $db, $user, $pass) {
    $payload = [
        'jsonrpc' => '2.0',
        'method'  => 'call',
        'params'  => [
            'db'       => $db,
            'login'    => $user,
            'password' => $pass
        ],
        'id' => 1
    ];
    return odooPost($host . '/web/session/authenticate', $payload);
}

/**
 * Panggil model Odoo via /web/dataset/call_kw
 */
function odooCallKw($host, $model, $method, $args, $kwargs, $sessionCookie) {
    $payload = [
        'jsonrpc' => '2.0',
        'method'  => 'call',
        'params'  => [
            'model'  => $model,
            'method' => $method,
            'args'   => $args,
            'kwargs' => $kwargs
        ],
        'id' => rand(100, 9999)
    ];
    return odooPost(
        $host . '/web/dataset/call_kw/' . $model . '/' . $method,
        $payload,
        'session_id=' . $sessionCookie
    );
}

try {
    switch ($action) {

        // =====================================================================
        // 1. PING — Test koneksi (public, tanpa login)
        // =====================================================================
        case 'ping':
        case 'check_connection':
            $rpc_payload = [
                'jsonrpc' => '2.0',
                'method'  => 'call',
                'params'  => ['service' => 'common', 'method' => 'version', 'args' => []],
                'id'      => rand(1000, 9999)
            ];
            $result = odooPost($odoo_host . '/jsonrpc', $rpc_payload);

            if (isset($result['data']['result'])) {
                $v = $result['data']['result'];
                echo json_encode([
                    'success'        => true,
                    'status'         => 'CONNECTED',
                    'server_host'    => $odoo_host,
                    'database'       => $odoo_db,
                    'latency_ms'     => $result['latency_ms'],
                    'server_version' => $v['server_version'] ?? '-',
                    'server_serie'   => $v['server_serie'] ?? '-',
                    'protocol_ver'   => $v['protocol_version'] ?? 1,
                    'timestamp'      => date('Y-m-d H:i:s') . ' WIB',
                    'request_log'    => $rpc_payload,
                    'response_log'   => $result['data'],
                    'message'        => 'Koneksi JSON-RPC ke Odoo SaaS berhasil terhubung secara live!'
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            } else {
                echo json_encode([
                    'success' => false,
                    'status'  => 'ERROR',
                    'message' => 'Server Odoo tidak merespon.',
                    'latency_ms' => $result['latency_ms']
                ], JSON_PRETTY_PRINT);
            }
            break;

        // =====================================================================
        // 2. SYNC INVOICES — Tarik invoice asli dari account.move (REAL DATA)
        // =====================================================================
        case 'sync_invoices':
            // Step 1: Authenticate
            $auth = odooAuthenticate($odoo_host, $odoo_db, $odoo_user, $odoo_pass);
            $sid = $auth['session_id'];

            if (!$sid) {
                throw new Exception('Gagal autentikasi ke Odoo. Periksa kredensial.');
            }

            // Step 2: search_read account.move (Customer Invoices)
            $inv = odooCallKw($odoo_host, 'account.move', 'search_read',
                [   // args: domain
                    [['move_type', '=', 'out_invoice']]
                ],
                [   // kwargs
                    'fields' => ['name', 'partner_id', 'amount_total', 'amount_residual',
                                 'state', 'payment_state', 'invoice_date', 'invoice_date_due'],
                    'limit'  => 20,
                    'order'  => 'id desc'
                ],
                $sid
            );

            $invoices = $inv['data']['result'] ?? [];

            // Step 3: search_read product.product (Katalog Jasa)
            $prod = odooCallKw($odoo_host, 'product.product', 'search_read',
                [[]],
                [
                    'fields' => ['name', 'list_price', 'type'],
                    'limit'  => 20
                ],
                $sid
            );
            $products = $prod['data']['result'] ?? [];

            // Step 4: search_read res.partner (Pelanggan)
            $part = odooCallKw($odoo_host, 'res.partner', 'search_read',
                [[['customer_rank', '>', 0]]],
                [
                    'fields' => ['name', 'email', 'phone', 'city', 'customer_rank'],
                    'limit'  => 20
                ],
                $sid
            );
            $partners = $part['data']['result'] ?? [];

            // Build request payload log for UI display
            $req_log = [
                'jsonrpc' => '2.0',
                'method'  => 'call',
                'params'  => [
                    'model'  => 'account.move',
                    'method' => 'search_read',
                    'args'   => [[['move_type', '=', 'out_invoice']]],
                    'kwargs' => [
                        'fields' => ['name','partner_id','amount_total','amount_residual','state','payment_state','invoice_date','invoice_date_due'],
                        'limit'  => 20,
                        'order'  => 'id desc'
                    ]
                ]
            ];

            echo json_encode([
                'success'          => true,
                'status'           => 'SYNCED_LIVE',
                'source'           => 'REAL ODOO DATABASE',
                'server_host'      => $odoo_host,
                'auth_user'        => $odoo_user,
                'auth_uid'         => 2,
                'latency_ms'       => $auth['latency_ms'] + $inv['latency_ms'],
                'synced_count'     => count($invoices),
                'invoices'         => $invoices,
                'products'         => $products,
                'partners'         => $partners,
                'timestamp'        => date('Y-m-d H:i:s') . ' WIB',
                'request_payload'  => $req_log,
                'response_payload' => [
                    'jsonrpc' => '2.0',
                    'id'      => $inv['data']['id'] ?? null,
                    'result'  => $invoices
                ],
                'message' => 'Berhasil menarik ' . count($invoices) . ' invoice ASLI dari database Odoo Enterprise (' . $odoo_host . ')!'
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            break;

        // =====================================================================
        // 3. GET PRODUCTS — Katalog Jasa Terminal (product.product)
        // =====================================================================
        case 'get_products':
            $auth = odooAuthenticate($odoo_host, $odoo_db, $odoo_user, $odoo_pass);
            $sid = $auth['session_id'];
            if (!$sid) throw new Exception('Auth gagal');

            $prod = odooCallKw($odoo_host, 'product.product', 'search_read',
                [[]],
                ['fields' => ['name','list_price','type','default_code'], 'limit' => 50],
                $sid
            );

            echo json_encode([
                'success'  => true,
                'products' => $prod['data']['result'] ?? [],
                'latency_ms' => $auth['latency_ms'] + $prod['latency_ms']
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;

        // =====================================================================
        // 4. GET PARTNERS — Daftar Pelanggan (res.partner)
        // =====================================================================
        case 'get_partners':
            $auth = odooAuthenticate($odoo_host, $odoo_db, $odoo_user, $odoo_pass);
            $sid = $auth['session_id'];
            if (!$sid) throw new Exception('Auth gagal');

            $part = odooCallKw($odoo_host, 'res.partner', 'search_read',
                [[['customer_rank', '>', 0]]],
                ['fields' => ['name','email','phone','city','customer_rank'], 'limit' => 50],
                $sid
            );

            echo json_encode([
                'success'  => true,
                'partners' => $part['data']['result'] ?? [],
                'latency_ms' => $auth['latency_ms'] + $part['latency_ms']
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Action tidak dikenali: ' . htmlspecialchars($action)]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
