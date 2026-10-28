<?php
/**
 * Conclusion Intermodal Dry Port (CIDP)
 * OAuth 2.0 Authorization Server (RFC 6749 Client Credentials Grant & Token Introspection)
 * File: api/oauth_server.php
 *
 * Endpoints:
 * - POST /api/oauth_server.php (action=token): Menerbitkan Short-Lived Bearer JWT Token (3600s)
 * - GET  /api/oauth_server.php?action=clients: Daftar client_id, scope, dan pemetaan event
 * - POST /api/oauth_server.php?action=introspect: Verifikasi & dekode Bearer JWT Token
 * - POST /api/oauth_server.php?action=sign: Menghitung HMAC-SHA256 signature & envelope OAuth 2.0
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/twin_state_helper.php';
require_once __DIR__ . '/preadvice_helper.php';
require_once __DIR__ . '/oauth_helper.php';
global $pdo;
ensure_integration_schema($pdo);

$raw = file_get_contents('php://input');
$in = (!empty($raw) ? @json_decode($raw, true) : null) ?: array_merge($_GET, $_POST);
$action = strtolower($in['action'] ?? ($_GET['action'] ?? 'token'));

try {
    switch ($action) {
        case 'clients':
            echo json_encode([
                'success'          => true,
                'issuer'           => 'cidp-auth-server',
                'token_endpoint'   => '/api/oauth_server.php?action=token',
                'default_ttl_sec'  => 3600,
                'clients'          => array_values(oauth_registered_clients()),
                'event_scope_map'  => oauth_event_scope_map()
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            break;

        case 'token':
            $grant_type = $in['grant_type'] ?? 'client_credentials';
            if ($grant_type !== 'client_credentials' && $grant_type !== 'authorization_code') {
                throw new OAuthSecurityException("Grant type '{$grant_type}' tidak didukung. Gunakan 'client_credentials'.", 400, 'unsupported_grant_type');
            }
            $client_id = trim($in['client_id'] ?? '');
            $client_secret = trim($in['client_secret'] ?? '');
            $clients = oauth_registered_clients();
            if ($client_id === '' || !isset($clients[$client_id])) {
                throw new OAuthSecurityException("Client ID '{$client_id}' tidak terdaftar.", 401, 'invalid_client');
            }
            // Validasi client_secret (jika dikirim; pada mode demo UI mengizinkan autofill dari matriks resmi)
            if ($client_secret !== '' && !hash_equals($clients[$client_id]['client_secret'], $client_secret)) {
                throw new OAuthSecurityException("Client Secret untuk '{$client_id}' tidak cocok.", 401, 'invalid_client');
            }
            $req_scopes = [];
            if (!empty($in['scope'])) {
                $req_scopes = is_array($in['scope']) ? $in['scope'] : preg_split('/[\s,]+/', trim((string)$in['scope']), -1, PREG_SPLIT_NO_EMPTY);
            }
            $ttl = isset($in['ttl']) ? max(60, min(86400, (int)$in['ttl'])) : 3600;
            $tok = oauth_issue_jwt($client_id, $req_scopes, $ttl);

            echo json_encode([
                'success'        => true,
                'access_token'   => $tok['access_token'],
                'token_type'     => 'Bearer',
                'expires_in'     => $tok['expires_in'],
                'scope'          => $tok['scope'],
                'granted_scopes' => $tok['granted_scopes'],
                'client_id'      => $tok['client_id'],
                'client_name'    => $tok['client_name'],
                'issued_at'      => $tok['issued_at'],
                'expires_at'     => $tok['expires_at'],
                'claims'         => $tok['claims']
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            break;

        case 'introspect':
            $token = trim($in['token'] ?? ($in['access_token'] ?? ''));
            $verified = oauth_verify_jwt($token);
            echo json_encode([
                'success' => true,
                'active'  => true,
                'token'   => $verified
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            break;

        case 'sign':
            $event_type = strtoupper(trim($in['event_type'] ?? 'TRUCK_WEIGHBRIDGE'));
            $payload = is_array($in['payload'] ?? null) ? $in['payload'] : [];
            $client_id = trim($in['client_id'] ?? '') ?: null;
            $env = oauth_build_signed_envelope($event_type, $payload, $client_id);
            echo json_encode([
                'success'        => true,
                'event_type'     => $event_type,
                'bearer_token'   => $env['bearer_token'],
                'signature_hmac' => $env['signature_hmac'],
                'oauth_context'  => $env['oauth_context']
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Action tidak dikenal. Gunakan token, clients, introspect, atau sign.']);
            break;
    }
} catch (OAuthSecurityException $e) {
    http_response_code($e->httpCode);
    echo json_encode([
        'success'           => false,
        'error'             => $e->errorCode,
        'error_description' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'server_error',
        'error_description' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
