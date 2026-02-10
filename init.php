<?php
if (!defined('INIT_LOADED')) {
    define('INIT_LOADED', true);
}

// Definisi Path Folder
define('ROOT_PATH', __DIR__ . '/');
define('CONFIG_PATH', ROOT_PATH . 'config/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('PAGES_PATH', ROOT_PATH . 'pages/');
define('ADMIN_PATH', PAGES_PATH . 'admin/');
define('PETUGAS_PATH', PAGES_PATH . 'petugas/');
define('SHARED_PATH', PAGES_PATH . 'shared/');
define('COMPONENTS_PATH', ROOT_PATH . 'components/');
define('PRINT_PATH', ROOT_PATH . 'print/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');
define('CSS_PATH', ASSETS_PATH . 'css/');
define('JS_PATH', ASSETS_PATH . 'js/');
define('IMG_PATH', ASSETS_PATH . 'images/');

// Definisi URL (Otomatis)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$baseUrl = $protocol . '://' . $host . dirname(dirname($scriptName)) . '/';

define('BASE_URL', $baseUrl);
define('ASSETS_URL', BASE_URL . 'assets/');
define('CSS_URL', ASSETS_URL . 'css/');
define('JS_URL', ASSETS_URL . 'js/');
define('IMG_URL', ASSETS_URL . 'images/');

// Muat konfigurasi database
if (file_exists(CONFIG_PATH . 'config.php')) {
    require_once CONFIG_PATH . 'config.php';
} else {
    die('ERROR: File config.php tidak ditemukan.');
}

// Muat fungsi autentikasi
if (file_exists(INCLUDES_PATH . 'auth.php')) {
    require_once INCLUDES_PATH . 'auth.php';
}

// Fungsi pembantu (Helpers)
function redirect($url) {
    header("Location: " . $url);
    exit();
}

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function formatTanggal($tanggal) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $pecah = explode('-', $tanggal);
    return $pecah[2] . ' ' . $bulan[(int)$pecah[1]] . ' ' . $pecah[0];
}

// Inisialisasi Sesi & Waktu
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Jakarta');