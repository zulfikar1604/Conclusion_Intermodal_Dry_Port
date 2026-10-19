<?php
// =============================================================================
// FILE: config/connection.php
// FUNGSI: Menghubungkan aplikasi PHP ke database MySQL di XAMPP
// =============================================================================

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'cidp_yms'; // Menggunakan database cidp_yms sesuai di phpMyAdmin

try {
    // Coba koneksi ke database cidp_yms
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    // Fallback jika database bernama cidp
    try {
        $pdo = new PDO("mysql:host=$host;dbname=cidp;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e2) {
        if (defined('CIDP_SILENT_DB_FALLBACK') && CIDP_SILENT_DB_FALLBACK === true) {
            $pdo = null;
        } else {
            die("<div style='font-family:sans-serif; padding:20px; color:#b91c1c; background:#fee2e2; border-radius:8px; margin:20px;'>
                <strong>Koneksi Database Gagal!</strong><br>
                Pastikan MySQL di XAMPP sudah aktif dan database <code>cidp_yms</code> tersedia di phpMyAdmin.<br>
                <small style='color:#666;'>Detail Error: " . htmlspecialchars($e2->getMessage()) . "</small>
            </div>");
        }
    }
}

// Inisialisasi koneksi MySQLi untuk kompatibilitas skrip mysqli_query($conn, ...)
if (!isset($conn) || !$conn) {
    try {
        $conn = @mysqli_connect($host, $user, $pass, $db);
        if (!$conn) {
            $conn = @mysqli_connect($host, $user, $pass, 'cidp');
        }
        if ($conn) {
            mysqli_set_charset($conn, 'utf8mb4');
        }
    } catch (Throwable $th) {
        $conn = false;
    }
}
