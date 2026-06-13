<?php
// 1. Memulai atau mengaktifkan session yang sedang berjalan
session_start();

// 2. Menghapus semua variabel session
$_SESSION = array();

// Jika ingin lebih bersih, hapus juga session cookie-nya (opsional tapi bagus untuk keamanan)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Menghancurkan session secara total
session_destroy();

// 4. Mengalihkan halaman ke register.php atau login.php
header("Location: dashboard.php");
exit;
?>