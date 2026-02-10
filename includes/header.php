<?php
requireLogin();
$userInfo = getUserInfo();

$scriptPath = $_SERVER['SCRIPT_NAME'];
if (strpos($scriptPath, '/pages/admin/') !== false) {
    $basePath = '../../';
} elseif (strpos($scriptPath, '/pages/petugas/') !== false) {
    $basePath = '../../';
} elseif (strpos($scriptPath, '/pages/shared/') !== false) {
    $basePath = '../../';
} elseif (strpos($scriptPath, '/components/') !== false) {
    $basePath = '../';
} elseif (strpos($scriptPath, '/print/') !== false) {
    $basePath = '../';
} else {
    $basePath = '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Sistem Kasir'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --danger: #e74c3c;
            --warning: #f39c12;
            --info: #3498db;
            --light-bg: #ecf0f1;
            --border: #bdc3c7;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --success: #27ae60;
            --sidebar-bg: #34495e;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            color: var(--text-dark);
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 30px 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h1 {
            font-family: 'Crimson Pro', serif;
            font-size: 1.8em;
            margin-bottom: 5px;
            color: white;
        }

        .sidebar-header .user-info {
            font-size: 0.9em;
            opacity: 0.8;
            margin-top: 10px;
        }

        .sidebar-header .role-badge {
            display: inline-block;
            padding: 4px 12px;
            background: var(--warning);
            color: white;
            border-radius: 4px;
            font-size: 0.75em;
            font-weight: 600;
            margin-top: 8px;
            text-transform: uppercase;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-section {
            margin-bottom: 25px;
        }

        .nav-section-title {
            padding: 0 25px;
            font-size: 0.75em;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.5;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 14px 25px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: var(--warning);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-left-color: var(--warning);
            font-weight: 600;
        }

        .nav-link .icon {
            margin-right: 12px;
            font-size: 1.2em;
            width: 24px;
            text-align: center;
            display: none;
        }

        .logout-link {
            margin: 20px 25px;
            padding: 12px 20px;
            background: var(--danger);
            border: none;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .logout-link:hover {
            background: #c0392b;
        }

        .logout-link .icon {
            margin-right: 8px;
            display: none;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 30px;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h2 {
            font-family: 'Crimson Pro', serif;
            font-size: 2.2em;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .page-header p {
            color: var(--text-light);
            font-size: 1em;
        }

        .breadcrumb {
            display: flex;
            gap: 10px;
            align-items: center;
            font-size: 0.9em;
            color: var(--text-light);
            margin-bottom: 15px;
        }

        .breadcrumb a {
            color: var(--info);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        /* Card Styles */
        .card {
            background: white;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
        }

        .card-header h3 {
            font-family: 'Crimson Pro', serif;
            font-size: 1.4em;
            color: var(--primary);
        }

        .card-body {
            padding: 25px;
        }

        /* Button Styles */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 0.95em;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'Inter', sans-serif;
        }

        .btn-primary {
            background: var(--secondary);
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: #229954;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .btn-warning {
            background: var(--warning);
            color: white;
        }

        .btn-warning:hover {
            background: #e67e22;
        }

        .btn-info {
            background: var(--info);
            color: white;
        }

        .btn-info:hover {
            background: #2980b9;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85em;
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: var(--light-bg);
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9em;
            color: var(--text-dark);
            border-bottom: 2px solid var(--border);
        }

        table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border);
        }

        table tr:hover {
            background: rgba(102, 126, 234, 0.03);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
            font-size: 0.95em;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 1em;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        select.form-control {
            cursor: pointer;
        }

        /* Alert Styles */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .alert-success {
            background: #d4edda;
            border-color: var(--success);
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            border-color: var(--danger);
            color: #721c24;
        }

        .alert-warning {
            background: #fff3cd;
            border-color: var(--warning);
            color: #856404;
        }

        .alert-info {
            background: #d1ecf1;
            border-color: var(--info);
            color: #0c5460;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }

            .sidebar-header h1,
            .sidebar-header .user-info,
            .sidebar-header .role-badge,
            .nav-section-title,
            .nav-link span {
                display: none;
            }

            .nav-link {
                justify-content: center;
                padding: 14px;
            }

            .nav-link .icon {
                margin: 0;
            }

            .logout-link {
                margin: 20px 10px;
                padding: 12px;
                justify-content: center;
            }

            .logout-link span {
                display: none;
            }

            .main-content {
                margin-left: 70px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>Sistem Kasir</h1>
                <div class="user-info">
                    <?php echo htmlspecialchars($userInfo['username']); ?>
                </div>
                <span class="role-badge"><?php echo htmlspecialchars($userInfo['role']); ?></span>
            </div>

            <nav class="sidebar-nav">
                <?php if (isAdmin()): ?>
                    <div class="nav-section">
                        <div class="nav-section-title">Menu Admin</div>
                        <a href="<?= $basePath ?>pages/shared/dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                            <span>Dashboard</span>
                        </a>
                        <a href="<?= $basePath ?>pages/admin/penjualan.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'penjualan.php' ? 'active' : ''; ?>">
                            <span>Riwayat Transaksi</span>
                        </a>
                        <a href="<?= $basePath ?>pages/admin/produk.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'produk.php' ? 'active' : ''; ?>">
                            <span>Data Produk</span>
                        </a>
                        <a href="<?= $basePath ?>pages/admin/member.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'member.php' ? 'active' : ''; ?>">
                            <span>Member</span>
                        </a>
                        <a href="<?= $basePath ?>pages/admin/user.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'user.php' ? 'active' : ''; ?>">
                            <span>Registrasi User</span>
                        </a>
                        <a href="<?= $basePath ?>pages/admin/laporan.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : ''; ?>">
                            <span>Laporan</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="nav-section">
                        <div class="nav-section-title">Menu Petugas</div>
                        <a href="<?= $basePath ?>pages/shared/dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                            <span>Dashboard</span>
                        </a>
                        <a href="<?= $basePath ?>pages/petugas/pos.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pos.php' ? 'active' : ''; ?>">
                            <span>Penjualan (POS)</span>
                        </a>
                        <a href="<?= $basePath ?>pages/admin/member.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'member.php' ? 'active' : ''; ?>">
                            <span>Member</span>
                        </a>
                        <a href="<?= $basePath ?>pages/petugas/stok.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'stok.php' ? 'active' : ''; ?>">
                            <span>Cek Stok Barang</span>
                        </a>
                        <a href="<?= $basePath ?>pages/petugas/laporan_petugas.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'laporan_petugas.php' ? 'active' : ''; ?>">
                            <span>Laporan Saya</span>
                        </a>
                    </div>
                <?php endif; ?>
            </nav>

            <a href="<?= $basePath ?>logout.php" class="logout-link" onclick="return confirm('Yakin ingin logout?')">
                <span>Logout</span>
            </a>
        </aside>

        <main class="main-content">
