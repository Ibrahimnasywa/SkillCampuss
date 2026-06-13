<?php
$host     = "127.0.0.1"; 
$username = "root";
$password = "";          
$database = "skillcampuss"; // Pastikan double 's' sesuai phpMyAdmin

$koneksi = mysqli_connect($host, $username, $password, $database);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>