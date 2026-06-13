<?php
// File untuk testing koneksi database

// Include koneksi
if (file_exists('config/koneksi.php')) {
    include 'config/koneksi.php';
} elseif (file_exists('koneksi.php')) {
    include 'koneksi.php';
} else {
    die("❌ File koneksi tidak ditemukan!");
}

// Cek koneksi
if ($koneksi) {
    echo "✅ Koneksi berhasil!<br>";
    echo "Host: " . $koneksi->server_info . "<br>";
    echo "Database: skillcampuss<br><br>";
    
    // Cek tabel users
    $result = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "✅ Tabel 'users' ada<br>";
        echo "Total users: " . $row['total'] . "<br><br>";
    } else {
        echo "❌ Tabel 'users' tidak ditemukan atau error<br>";
    }
    
    // Cek kolom users
    $columns = mysqli_query($koneksi, "DESCRIBE users");
    if ($columns) {
        echo "✅ Struktur tabel users:<br>";
        echo "<table border='1' cellpadding='5'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($col = mysqli_fetch_assoc($columns)) {
            echo "<tr><td>" . htmlspecialchars($col['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Default']) . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "❌ Gagal membaca struktur tabel";
    }
} else {
    echo "❌ Koneksi gagal: " . mysqli_connect_error();
}
?>
