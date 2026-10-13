<?php
// =============================================================================
// FILE: logout.php
// FUNGSI: Mengakhiri sesi login pengguna dan kembali ke halaman utama dengan notifikasi aman
// PROYEK: Conclusion Intermodal Dry Port (CIDP 35 Ha)
// =============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Kosongkan semua variabel sesi
$_SESSION = [];

// 2. Hancurkan cookie sesi jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hancurkan sesi di server
session_destroy();

// 4. Arahkan ke landing page dengan parameter status logout_success
header("Location: index.php?status=logout_success");
exit;
?>
