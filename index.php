<?php
// =============================================================================
// FILE: index.php
// FUNGSI: Mengarahkan pengguna ke login.php atau dashboard.php
// =============================================================================

session_start();

if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header("Location: dashboard.php");
    exit;
}

header("Location: login.php");
exit;
?>
