<?php
// =============================================================================
// FILE       : login.php
// FUNGSI     : Pengarah Tunggal ke Landing Page Utama CIDP DPMS (Modal Masuk)
// KONSULTAN  : Conclusion Supply Chain Consultant
// TUJUAN     : Menjamin SATU landing page terpadu (tanpa duplikasi / dobel landing page)
// =============================================================================

session_start();

// Jika pengguna telah masuk, alihkan langsung ke konsol dashboard
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header("Location: dashboard.php");
    exit;
}

// Alihkan langsung ke halaman depan utama dengan modal login aktif
header("Location: index.php?login=1");
exit;
?>