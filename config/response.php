<?php
// =============================================================================
// FILE: config/response.php
// FUNGSI: Centralized JSON API Response & Request Input Helper
// =============================================================================

if (!function_exists('api_init_headers')) {
    /**
     * Set standard API headers for JSON & CORS
     */
    function api_init_headers() {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}

if (!function_exists('get_json_input')) {
    /**
     * Retrieve parsed JSON input, merged with $_POST and $_GET
     * @return array
     */
    function get_json_input() {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true) ?? [];
        return array_merge($_GET, $_POST, $json);
    }
}

if (!function_exists('json_response')) {
    /**
     * Send structured JSON response and terminate script
     * @param bool $success
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @param array $extra
     */
    function json_response($success, $data = null, $message = '', $statusCode = 200, $extra = []) {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
        }

        $response = [
            'success' => (bool)$success,
            'message' => (string)$message,
            'data'    => $data
        ];

        if (!empty($extra)) {
            foreach ($extra as $key => $val) {
                $response[$key] = $val;
            }
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('json_success')) {
    /**
     * Success JSON response shortcut
     */
    function json_success($data = null, $message = 'Success', $extra = [], $statusCode = 200) {
        json_response(true, $data, $message, $statusCode, $extra);
    }
}

if (!function_exists('json_error')) {
    /**
     * Error JSON response shortcut
     */
    function json_error($message = 'Error', $statusCode = 400, $data = null, $extra = []) {
        json_response(false, $data, $message, $statusCode, $extra);
    }
}
