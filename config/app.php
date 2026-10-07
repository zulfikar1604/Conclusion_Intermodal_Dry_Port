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

    // Asset URLs
    define('ASSETS_URL', 'assets/');
    define('VENDOR_URL', 'assets/vendor/');
}
