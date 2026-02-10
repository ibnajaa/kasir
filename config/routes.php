<?php
// Base path configuration
define('BASE_PATH', '/kasir_ibn');
define('ROOT_DIR', dirname(__DIR__));

// Path constants
define('CONFIG_PATH', ROOT_DIR . '/config');
define('INCLUDES_PATH', ROOT_DIR . '/includes');
define('PAGES_PATH', ROOT_DIR . '/pages');
define('COMPONENTS_PATH', ROOT_DIR . '/components');
define('PRINT_PATH', ROOT_DIR . '/print');
define('ASSETS_PATH', ROOT_DIR . '/assets');

// URL helpers
function url($path = '') {
    return BASE_PATH . '/' . ltrim($path, '/');
}

// Route definitions untuk menu
$routes = [
    // Admin routes
    'admin' => [
        'dashboard' => url('pages/shared/dashboard.php'),
        'penjualan' => url('pages/admin/penjualan.php'),
        'produk' => url('pages/admin/produk.php'),
        'member' => url('pages/admin/member.php'),
        'user' => url('pages/admin/user.php'),
        'laporan' => url('pages/admin/laporan.php'),
    ],
    
    // Petugas routes
    'petugas' => [
        'dashboard' => url('pages/shared/dashboard.php'),
        'pos' => url('pages/petugas/pos.php'),
        'member' => url('pages/admin/member.php'),
        'stok' => url('pages/petugas/stok.php'),
        'laporan' => url('pages/petugas/laporan_petugas.php'),
    ],
    
    // Shared routes
    'auth' => [
        'login' => url('index.php'),
        'logout' => url('logout.php'),
    ],
    
    // Components
    'components' => [
        'detail_transaksi' => url('components/detail_transaksi.php'),
        'cek_member' => url('components/cek_member.php'),
    ],
    
    // Print
    'print' => [
        'nota' => url('print/preview_nota.php'),
        'laporan' => url('print/preview_laporan.php'),
        'laporan_petugas' => url('print/preview_laporan_petugas.php'),
    ],
];
?>
