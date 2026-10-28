<?php
/**
 * Conclusion Intermodal Dry Port (CIDP)
 * OAuth 2.0 (RFC 6749), JWT Bearer Token (RFC 7519) & HMAC-SHA256 Cryptographic Trust Helper
 * File: api/oauth_helper.php
 *
 * Mengimplementasikan Prinsip 04 (Secure by Design) & Slide 7-8 Materi Data Integration:
 * - Client Credentials Grant untuk Machine-to-Machine (IoT Edge, WMS, DJBC CEISA 4.0, Odoo/Mandiri H2H)
 * - Short-Lived JWT Bearer Token (3600 detik / 1 jam)
 * - Granular Scope per jenis event (Least Privilege Principle)
 * - Verifikasi Integritas Payload HMAC-SHA256 (Anti-Tampering / Man-in-the-Middle Protection)
 */

class OAuthSecurityException extends Exception {
    public int $httpCode;
    public string $errorCode;
    public function __construct(string $message, int $httpCode = 401, string $errorCode = 'invalid_token') {
        parent::__construct($message);
        $this->httpCode = $httpCode;
        $this->errorCode = $errorCode;
    }
}

if (!defined('CIDP_JWT_MASTER_SECRET')) {
    define('CIDP_JWT_MASTER_SECRET', 'cidp_dpms_35ha_oauth2_jwt_master_signing_key_2026_secure');
}

/**
 * Daftar Client OAuth 2.0 Resmi Ekosistem CIDP 35 Ha (Sesuai Matriks Slide 7 Presentasi)
 */
function oauth_registered_clients(): array {
    return [
        'cidp-edge-gate-01' => [
            'client_id'     => 'cidp-edge-gate-01',
            'client_secret' => 'cidp_sec_gate_hw06_2026',
            'name'          => 'Edge AI PC Gate & Timbangan (HW-06)',
            'grant_type'    => 'client_credentials',
            'zone'          => 'TMS • Gate & Weighbridge',
            'scopes'        => ['ingest:gate', 'ingest:weighbridge', 'gate:out'],
            'endpoint'      => 'POST /api/ingest_event.php'
        ],
        'cidp-edge-yard-fleet' => [
            'client_id'     => 'cidp-edge-yard-fleet',
            'client_secret' => 'cidp_sec_kalmar_rtk_2026',
            'name'          => 'Kalmar Telemetry, DGPS RTK & Siding KA Gateway',
            'grant_type'    => 'client_credentials',
            'zone'          => 'YMS • Yard, Reefer & Rail',
            'scopes'        => ['ingest:equipment', 'yard:stack', 'yard:relocate', 'reefer:telemetry', 'rail:load'],
            'endpoint'      => 'POST /api/ingest_event.php'
        ],
        'cidp-edge-cfs-wms' => [
            'client_id'     => 'cidp-edge-cfs-wms',
            'client_secret' => 'cidp_sec_cfs_honeywell_2026',
            'name'          => 'Scanner GS1 Honeywell & Timbangan Palet 3T CFS',
            'grant_type'    => 'client_credentials',
            'zone'          => 'WMS • Gudang Konsolidasi CFS',
            'scopes'        => ['cfs:strip', 'cfs:pallet'],
            'endpoint'      => 'POST /api/ingest_event.php'
        ],
        'djbc-ceisa40-h2h' => [
            'client_id'     => 'djbc-ceisa40-h2h',
            'client_secret' => 'djbc_sec_ceisa40_h2h_2026',
            'name'          => 'Webhook CEISA 4.0 DJBC (Bea Cukai) & Gantry X-Ray',
            'grant_type'    => 'client_credentials',
            'zone'          => 'CUSTOMS • Kepabeanan DJBC',
            'scopes'        => ['customs:sppb:write', 'customs:inspect'],
            'endpoint'      => 'POST /api/ingest_event.php (CUSTOMS_CEISA)'
        ],
        'erp-odoo-finance-sync' => [
            'client_id'     => 'erp-odoo-finance-sync',
            'client_secret' => 'odoo_sec_mandiri_h2h_2026',
            'name'          => 'Payment Gateway Mandiri H2H & Odoo Cloud ERP',
            'grant_type'    => 'client_credentials',
            'zone'          => 'ERP • Keuangan & Billing',
            'scopes'        => ['billing:settle', 'erp:invoice:sync'],
            'endpoint'      => 'POST /api/ingest_event.php, /api/odoo_client.php'
        ],
        'cidp-web-portal-ui' => [
            'client_id'     => 'cidp-web-portal-ui',
            'client_secret' => 'cidp_sec_portal_master_2026',
            'name'          => 'Dashboard Operator & Virtual IoT Sandbox UI',
            'grant_type'    => 'authorization_code',
            'zone'          => 'EXEC • Control Tower & Simulator',
            'scopes'        => [
                'ingest:gate', 'ingest:weighbridge', 'gate:out',
                'ingest:equipment', 'yard:stack', 'yard:relocate',
                'cfs:strip', 'cfs:pallet',
                'customs:sppb:write', 'customs:inspect',
                'reefer:telemetry', 'rail:load',
                'billing:settle', 'erp:invoice:sync',
                'dashboard:read', 'simulator:control'
            ],
            'endpoint'      => 'GET /api/stream_twin.php, POST /api/ingest_event.php'
        ]
    ];
}

/**
 * Pemetaan setiap event_type ke scope OAuth 2.0 wajib dan client pengirim default-nya
 */
function oauth_event_scope_map(): array {
    return [
        'TRUCK_GATE_ARRIVAL'     => ['scope' => 'ingest:gate',        'default_client' => 'cidp-edge-gate-01'],
        'TRUCK_WEIGHBRIDGE'      => ['scope' => 'ingest:weighbridge', 'default_client' => 'cidp-edge-gate-01'],
        'TRUCK_GATE_OUT'         => ['scope' => 'gate:out',           'default_client' => 'cidp-edge-gate-01'],
        'EQUIPMENT_MOVE'         => ['scope' => 'ingest:equipment',   'default_client' => 'cidp-edge-yard-fleet'],
        'CONTAINER_LIFT'         => ['scope' => 'yard:stack',         'default_client' => 'cidp-edge-yard-fleet'],
        'CONTAINER_STACK'        => ['scope' => 'yard:stack',         'default_client' => 'cidp-edge-yard-fleet'],
        'YARD_RELOCATION'        => ['scope' => 'yard:relocate',      'default_client' => 'cidp-edge-yard-fleet'],
        'CFS_STRIPPING_START'    => ['scope' => 'cfs:strip',          'default_client' => 'cidp-edge-cfs-wms'],
        'CFS_PALLET_SCAN'        => ['scope' => 'cfs:pallet',         'default_client' => 'cidp-edge-cfs-wms'],
        'CFS_STRIPPING_COMPLETE' => ['scope' => 'cfs:strip',          'default_client' => 'cidp-edge-cfs-wms'],
        'CFS_PALLET_DISPATCH'    => ['scope' => 'cfs:pallet',         'default_client' => 'cidp-edge-cfs-wms'],
        'CUSTOMS_INSPECTION'     => ['scope' => 'customs:inspect',    'default_client' => 'djbc-ceisa40-h2h'],
        'CUSTOMS_CEISA'          => ['scope' => 'customs:sppb:write', 'default_client' => 'djbc-ceisa40-h2h'],
        'REEFER_PLUGIN'          => ['scope' => 'reefer:telemetry',   'default_client' => 'cidp-edge-yard-fleet'],
        'REEFER_TELEMETRY'       => ['scope' => 'reefer:telemetry',   'default_client' => 'cidp-edge-yard-fleet'],
        'RAIL_LOADING'           => ['scope' => 'rail:load',          'default_client' => 'cidp-edge-yard-fleet'],
        'PAYMENT_SETTLED'        => ['scope' => 'billing:settle',     'default_client' => 'erp-odoo-finance-sync'],
    ];
}

function b64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function b64url_decode(string $data): string {
    $pad = strlen($data) % 4;
    if ($pad > 0) $data .= str_repeat('=', 4 - $pad);
    return base64_decode(strtr($data, '-_', '+/')) ?: '';
}

/**
 * Menerbitkan JWT Bearer Token (RFC 7519) untuk client_id tertentu
 */
function oauth_issue_jwt(string $client_id, array $scopes = [], int $ttl_seconds = 3600): array {
    $clients = oauth_registered_clients();
    if (!isset($clients[$client_id])) {
        throw new OAuthSecurityException("Client ID '{$client_id}' tidak terdaftar di CIDP OAuth Server.", 401, 'invalid_client');
    }
    $client = $clients[$client_id];
    $granted = empty($scopes) ? $client['scopes'] : array_values(array_intersect($scopes, $client['scopes']));
    if (empty($granted)) {
        throw new OAuthSecurityException("Scope yang diminta tidak diizinkan untuk client '{$client_id}'.", 403, 'invalid_scope');
    }

    $now = time();
    $exp = $now + $ttl_seconds;
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $payload = [
        'iss'       => 'cidp-auth-server',
        'sub'       => $client_id,
        'client_id' => $client_id,
        'scope'     => implode(' ', $granted),
        'iat'       => $now,
        'exp'       => $exp,
        'jti'       => 'JWT-' . date('YmdHis', $now) . '-' . substr(bin2hex(random_bytes(3)), 0, 6)
    ];

    $h64 = b64url_encode(json_encode($header, JSON_UNESCAPED_SLASHES));
    $p64 = b64url_encode(json_encode($payload, JSON_UNESCAPED_SLASHES));
    $sig = b64url_encode(hash_hmac('sha256', "{$h64}.{$p64}", CIDP_JWT_MASTER_SECRET, true));
    $jwt = "{$h64}.{$p64}.{$sig}";

    return [
        'access_token'   => $jwt,
        'token_type'     => 'Bearer',
        'expires_in'     => $ttl_seconds,
        'scope'          => implode(' ', $granted),
        'granted_scopes' => $granted,
        'client_id'      => $client_id,
        'client_name'    => $client['name'],
        'issued_at'      => date('c', $now),
        'expires_at'     => date('c', $exp),
        'claims'         => $payload
    ];
}

/**
 * Memverifikasi JWT Bearer Token (Signature HS256, Expiry, & Client)
 */
function oauth_verify_jwt(string $jwt): array {
    $jwt = trim(preg_replace('/^Bearer\s+/i', '', trim($jwt)));
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) {
        throw new OAuthSecurityException('Format Bearer JWT Token tidak valid (harus terdiri dari header.payload.signature).', 401, 'invalid_token');
    }
    [$h64, $p64, $s64] = $parts;
    $expected_sig = b64url_encode(hash_hmac('sha256', "{$h64}.{$p64}", CIDP_JWT_MASTER_SECRET, true));
    if (!hash_equals($expected_sig, $s64)) {
        throw new OAuthSecurityException('Tanda tangan kriptografi JWT tidak sah (JWT Signature Invalid / Tampered).', 401, 'invalid_token');
    }

    $claims = json_decode(b64url_decode($p64), true);
    if (!is_array($claims) || empty($claims['client_id'])) {
        throw new OAuthSecurityException('Isi klaim JWT rusak atau tidak memiliki client_id.', 401, 'invalid_token');
    }
    if (isset($claims['exp']) && time() > (int)$claims['exp']) {
        throw new OAuthSecurityException("Bearer Token OAuth 2.0 telah kedaluwarsa (expired pada " . date('c', (int)$claims['exp']) . ").", 401, 'expired_token');
    }
    $clients = oauth_registered_clients();
    if (!isset($clients[$claims['client_id']])) {
        throw new OAuthSecurityException("Client '{$claims['client_id']}' pada token tidak dikenali.", 401, 'invalid_client');
    }

    $scopes = array_values(array_filter(explode(' ', (string)($claims['scope'] ?? ''))));
    return [
        'client_id'      => $claims['client_id'],
        'client_name'    => $clients[$claims['client_id']]['name'],
        'granted_scopes' => $scopes,
        'issued_at'      => isset($claims['iat']) ? date('c', (int)$claims['iat']) : null,
        'expires_at'     => isset($claims['exp']) ? date('c', (int)$claims['exp']) : null,
        'jti'            => $claims['jti'] ?? null,
        'claims'         => $claims
    ];
}

/**
 * Menghitung canonical HMAC-SHA256 atas sebuah event & payload
 * Digunakan baik untuk body JSON maupun internal dispatch
 */
function oauth_compute_payload_hmac(string $client_id, string $event_type, array $payload, string $raw_body = ''): string {
    $clients = oauth_registered_clients();
    $secret = $clients[$client_id]['client_secret'] ?? CIDP_JWT_MASTER_SECRET;
    if ($raw_body !== '') {
        return 'sha256=' . hash_hmac('sha256', $raw_body, $secret);
    }
    ksort($payload);
    $canonical = strtoupper($event_type) . '|' . json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return 'sha256=' . hash_hmac('sha256', $canonical, $secret);
}

/**
 * Memverifikasi tanda tangan HMAC-SHA256 terhadap raw_body atau canonical payload
 */
function oauth_verify_payload_hmac(string $provided_sig, string $client_id, string $event_type, array $payload, string $raw_body = ''): bool {
    $provided = trim($provided_sig);
    if ($provided === '') return false;
    if (stripos($provided, 'sha256=') !== 0) {
        $provided = 'sha256=' . $provided;
    }
    // Cek kecocokan terhadap canonical payload terlebih dahulu, lalu raw_body
    $expected_canonical = oauth_compute_payload_hmac($client_id, $event_type, $payload, '');
    if (hash_equals($expected_canonical, $provided)) {
        return true;
    }
    if ($raw_body !== '') {
        $expected_raw = oauth_compute_payload_hmac($client_id, $event_type, $payload, $raw_body);
        if (hash_equals($expected_raw, $provided)) {
            return true;
        }
    }
    return false;
}

/**
 * Helper untuk simulator / internal caller: menerbitkan paket OAuth 2.0 + HMAC resmi sesuai event_type
 */
function oauth_build_signed_envelope(string $event_type, array $payload, ?string $override_client_id = null): array {
    $map = oauth_event_scope_map();
    $ev = strtoupper($event_type);
    $client_id = $override_client_id ?: ($map[$ev]['default_client'] ?? 'cidp-web-portal-ui');
    $tok = oauth_issue_jwt($client_id);
    $sig = oauth_compute_payload_hmac($client_id, $ev, $payload, '');
    return [
        'bearer_token'   => $tok['access_token'],
        'signature_hmac' => $sig,
        'oauth_context'  => [
            'token_type'     => 'Bearer',
            'client_id'      => $tok['client_id'],
            'granted_scopes' => $tok['granted_scopes'],
            'issued_at'      => $tok['issued_at'],
            'expires_at'     => $tok['expires_at']
        ]
    ];
}
