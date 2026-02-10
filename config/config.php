<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kasir_ibn');

// Koneksi Database
function getConnection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if (!$conn) {
        die("Koneksi gagal: " . mysqli_connect_error());
    }
    
    mysqli_set_charset($conn, "utf8mb4");
    return $conn;
}

// Fungsi untuk menutup koneksi
function closeConnection($conn) {
    mysqli_close($conn);
}

// Fungsi untuk escape string
function escapeString($conn, $string) {
    return mysqli_real_escape_string($conn, $string);
}

// Fungsi untuk query
function query($conn, $sql) {
    return mysqli_query($conn, $sql);
}

// Fungsi untuk fetch data
function fetchArray($result) {
    return mysqli_fetch_assoc($result);
}

// Fungsi untuk fetch all data
function fetchAll($result) {
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

// Fungsi untuk menghitung jumlah rows
function numRows($result) {
    return mysqli_num_rows($result);
}

// Fungsi untuk mendapatkan ID terakhir
function lastInsertId($conn) {
    return mysqli_insert_id($conn);
}
?>
