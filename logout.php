<?php
// =============================================================================
// FILE: logout.php
// FUNGSI: Mengakhiri sesi login pengguna dan kembali ke halaman login
// =============================================================================

session_start();

// 1. Kosongkan semua variabel sesi
$_SESSION = [];

// 2. Hancurkan sesi di server
session_destroy();

// 3. Arahkan pengguna kembali ke halaman login
header("Location: login.php");
exit;
?>
