<?php
// =============================================================================
// FILE       : dashboard.php
// KONSULTAN  : Conclusion Supply Chain Consultant
// FUNGSI     : Konsol Utama Demonstrasi Yard Management System (YMS)
// KETERANGAN : Disiapkan kosongan untuk tahapan belajar dan verifikasi modul login
// =============================================================================

session_start();

// 1. Cek validitas sesi login. Apabila belum masuk, alihkan kembali ke login.php
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// 2. Ambil data sesi pengguna yang sedang aktif
$nama  = $_SESSION['nama'] ?? 'Pengguna Konsultan';
$email = $_SESSION['email'] ?? '-';
$role  = $_SESSION['role'] ?? 'admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konsol Demonstrasi YMS &bull; Conclusion Supply Chain Consultant</title>
    
    <!-- Favicon / Ikon Tab Peramban (Browser Tab Icon) -->
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link rel="shortcut icon" type="image/png" href="assets/img/logo.png">
    <link rel="apple-touch-icon" href="assets/img/logo.png">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cdp: {
                            navy: '#004b87',
                            dark: '#002f5e',
                            blue: '#0170b9',
                            light: '#f4f8fc'
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace']
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen antialiased">

  
    
            <div class="pt-2">
                <a href="logout.php" class="inline-flex items-center text-xs font-bold text-red-600 hover:text-red-700 transition">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Keluar dari Konsol dan Uji Coba Halaman Masuk Kembali
                </a>
            </div>


</body>
</html>
