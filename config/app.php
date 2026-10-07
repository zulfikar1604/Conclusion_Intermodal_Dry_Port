<?php
// =============================================================================
// FILE: config/app.php
// FUNGSI: Konfigurasi Sentral Aplikasi CIDP (Cikarang Intermodal Dry Port)
// =============================================================================

if (!defined('APP_NAME')) {
    define('APP_NAME', 'Conclusion Intermodal Dry Port');
    define('APP_VERSION', '2.5.0');
    define('APP_LOCATION', 'Cikarang Dry Port 35 Ha');

    // Base Directory Paths
    define('ROOT_PATH', dirname(__DIR__));
    define('CONFIG_PATH', ROOT_PATH . '/config');
    define('DATABASE_PATH', ROOT_PATH . '/database');
    define('PAGES_PATH', ROOT_PATH . '/pages');
    define('COMPONENTS_PATH', ROOT_PATH . '/components');
    define('ASSETS_PATH', ROOT_PATH . '/assets');
    define('DOCS_PATH', ROOT_PATH . '/docs');
    define('API_PATH', ROOT_PATH . '/api');

    // Asset URLs
    define('ASSETS_URL', 'assets/');
    define('VENDOR_URL', 'assets/vendor/');

    // Odoo ERP Live Integration Config (Enterprise saas~19.4+e)
    define('ODOO_DEFAULT_HOST', 'https://conclusion-intermodal-dry-port.odoo.com');
    define('ODOO_DEFAULT_DB',   'conclusion-intermodal-dry-port');
    define('ODOO_DEFAULT_USER', 'zulfikarjafarudinfatah@gmail.com');
    define('ODOO_DEFAULT_PASS', '@Zulfikar123');
}

// Auto-load API helpers
require_once __DIR__ . '/response.php';

// Auto-load Composer Vendor Packages (Dompdf, GuzzleHTTP, Endroid QR, Dotenv)
if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
}
