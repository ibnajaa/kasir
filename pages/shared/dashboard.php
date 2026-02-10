<?php
$pageTitle = 'Dashboard';
require_once '../../init.php';
require_once INCLUDES_PATH . 'header.php';

$conn = getConnection();
$userInfo = getUserInfo();

// Statistik untuk Dashboard
if (isAdmin()) {
    // Statistik Admin
    $totalProduk = mysqli_fetch_assoc(query($conn, "SELECT COUNT(*) as total FROM produk"))['total'];
    $totalMember = mysqli_fetch_assoc(query($conn, "SELECT COUNT(*) as total FROM pelanggan WHERE KodeMember IS NOT NULL"))['total'];
    $totalPetugas = mysqli_fetch_assoc(query($conn, "SELECT COUNT(*) as total FROM user WHERE Role = 'petugas'"))['total'];
    
    $penjualanHariIni = mysqli_fetch_assoc(query($conn, "SELECT COUNT(*) as total, COALESCE(SUM(TotalBayar), 0) as omset FROM penjualan WHERE DATE(TanggalPenjualan) = CURDATE()"));
    
    // Produk stok rendah (< 20)
    $stokRendah = query($conn, "SELECT * FROM produk WHERE Stok < 20 ORDER BY Stok ASC LIMIT 5");
    
    // Transaksi terbaru
    $transaksiTerbaru = query($conn, "SELECT p.*, pl.NamaPelanggan, u.Username FROM penjualan p 
                                       JOIN pelanggan pl ON p.PelangganID = pl.PelangganID 
                                       JOIN user u ON p.UserID = u.UserID 
                                       ORDER BY p.TanggalPenjualan DESC LIMIT 5");
} else {
    // Statistik Petugas
    $userId = $userInfo['id'];
    $transaksiHariIni = mysqli_fetch_assoc(query($conn, "SELECT COUNT(*) as total, COALESCE(SUM(TotalBayar), 0) as omset FROM penjualan WHERE UserID = $userId AND DATE(TanggalPenjualan) = CURDATE()"));
    
    $transaksiTerbaru = query($conn, "SELECT p.*, pl.NamaPelanggan FROM penjualan p 
                                       JOIN pelanggan pl ON p.PelangganID = pl.PelangganID 
                                       WHERE p.UserID = $userId 
                                       ORDER BY p.TanggalPenjualan DESC LIMIT 5");
}
?>

<?php if (isAdmin()): ?>
    <div class="page-header">
        <h2>Dashboard Admin</h2>
        <p>Ringkasan dan monitoring bisnis Anda</p>
    </div>

    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div class="card" style="background: #3498db; color: white; border: none;">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <div style="font-size: 0.9em; opacity: 0.9; margin-bottom: 5px;">Total Produk</div>
                        <div style="font-size: 2.5em; font-weight: 700; font-family: 'Crimson Pro', serif;"><?php echo $totalProduk; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="background: #27ae60; color: white; border: none;">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <div style="font-size: 0.9em; opacity: 0.9; margin-bottom: 5px;">Penjualan Hari Ini</div>
                        <div style="font-size: 2.5em; font-weight: 700; font-family: 'Crimson Pro', serif;"><?php echo $penjualanHariIni['total']; ?></div>
                        <div style="font-size: 0.85em; opacity: 0.9; margin-top: 5px;">Rp <?php echo number_format($penjualanHariIni['omset'], 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="background: #e67e22; color: white; border: none;">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <div style="font-size: 0.9em; opacity: 0.9; margin-bottom: 5px;">Total Member</div>
                        <div style="font-size: 2.5em; font-weight: 700; font-family: 'Crimson Pro', serif;"><?php echo $totalMember; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="background: #16a085; color: white; border: none;">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <div style="font-size: 0.9em; opacity: 0.9; margin-bottom: 5px;">Total Petugas</div>
                        <div style="font-size: 2.5em; font-weight: 700; font-family: 'Crimson Pro', serif;"><?php echo $totalPetugas; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
        <!-- Alert Stok Rendah -->
        <div class="card">
            <div class="card-header">
                <h3>Alert Stok Rendah</h3>
            </div>
            <div class="card-body">
                <?php if (numRows($stokRendah) > 0): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Stok</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = fetchArray($stokRendah)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['NamaProduk']); ?></td>
                                        <td>
                                            <span style="background: #ff6b6b; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.85em; font-weight: 600;">
                                                <?php echo $row['Stok']; ?> unit
                                            </span>
                                        </td>
                                        <td>
                                            <a href="../admin/produk.php?edit=<?php echo $row['ProdukID']; ?>" class="btn btn-warning btn-sm">
                                                Update Stok
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-light); text-align: center; padding: 20px;">Semua stok produk aman</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Transaksi Terbaru -->
        <div class="card">
            <div class="card-header">
                <h3>Transaksi Terbaru</h3>
            </div>
            <div class="card-body">
                <?php if (numRows($transaksiTerbaru) > 0): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nota</th>
                                    <th>Pelanggan</th>
                                    <th>Total</th>
                                    <th>Kasir</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php mysqli_data_seek($transaksiTerbaru, 0); ?>
                                <?php while ($row = fetchArray($transaksiTerbaru)): ?>
                                    <tr>
                                        <td>#<?php echo str_pad($row['PenjualanID'], 6, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo htmlspecialchars($row['NamaPelanggan']); ?></td>
                                        <td>Rp <?php echo number_format($row['TotalBayar'], 0, ',', '.'); ?></td>
                                        <td><small><?php echo htmlspecialchars($row['Username']); ?></small></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-light); text-align: center; padding: 20px;">Belum ada transaksi</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Dashboard Petugas -->
    <div class="page-header">
        <h2>Selamat Bekerja, <?php echo strtoupper(htmlspecialchars($userInfo['username'])); ?>!</h2>
        <p>Semangat melayani pelanggan hari ini</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div class="card" style="background: #3498db; color: white; border: none;">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <div style="font-size: 0.9em; opacity: 0.9; margin-bottom: 5px;">Transaksi Hari Ini</div>
                        <div style="font-size: 2.8em; font-weight: 700; font-family: 'Crimson Pro', serif;"><?php echo $transaksiHariIni['total']; ?> Nota</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="background: #27ae60; color: white; border: none;">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <div style="font-size: 0.9em; opacity: 0.9; margin-bottom: 5px;">Total Penjualan</div>
                        <div style="font-size: 2em; font-weight: 700; font-family: 'Crimson Pro', serif;">Rp <?php echo number_format($transaksiHariIni['omset'], 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tombol Aksi Cepat -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
        <a href="../petugas/pos.php" class="btn btn-primary" style="padding: 20px; justify-content: center; font-size: 1.1em;">
            Transaksi Baru
        </a>
        <a href="../petugas/stok.php" class="btn btn-secondary" style="padding: 20px; justify-content: center; font-size: 1.1em;">
            Cek Stok
        </a>
        <a href="../petugas/laporan_petugas.php" class="btn btn-success" style="padding: 20px; justify-content: center; font-size: 1.1em;">
            Laporan
        </a>
    </div>

    <!-- Transaksi Terakhir -->
    <div class="card">
        <div class="card-header">
            <h3>Transaksi Terakhir Saya</h3>
        </div>
        <div class="card-body">
            <?php if (numRows($transaksiTerbaru) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Nomor Nota</th>
                                <th>Waktu</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = fetchArray($transaksiTerbaru)): ?>
                                <tr>
                                    <td><strong>#<?php echo str_pad($row['PenjualanID'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['TanggalPenjualan'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['NamaPelanggan']); ?></td>
                                    <td><strong>Rp <?php echo number_format($row['TotalBayar'], 0, ',', '.'); ?></strong></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color: var(--text-light); text-align: center; padding: 40px;">
                    Belum ada transaksi hari ini. <a href="../petugas/pos.php">Mulai transaksi pertama</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php
closeConnection($conn);
require_once INCLUDES_PATH . 'footer.php';
?>
