<?php
$pageTitle = 'Laporan';
require_once '../../init.php';
require_once INCLUDES_PATH . 'header.php';
requireAdmin();

$conn = getConnection();

// Filter tanggal
$filterMulai = $_GET['mulai'] ?? date('Y-m-01');
$filterSampai = $_GET['sampai'] ?? date('Y-m-d');

// Query data laporan
$sql = "SELECT p.*, pl.NamaPelanggan, u.Username 
        FROM penjualan p 
        JOIN pelanggan pl ON p.PelangganID = pl.PelangganID 
        JOIN user u ON p.UserID = u.UserID 
        WHERE DATE(p.TanggalPenjualan) BETWEEN '$filterMulai' AND '$filterSampai'
        ORDER BY p.TanggalPenjualan DESC";
$transaksiList = query($conn, $sql);

// Hitung statistik
$totalOmset = 0;
$totalTransaksi = numRows($transaksiList);
mysqli_data_seek($transaksiList, 0);
while ($row = fetchArray($transaksiList)) {
    $totalOmset += $row['TotalBayar'];
}
mysqli_data_seek($transaksiList, 0);

// Statistik per kasir
$kasirStats = query($conn, "SELECT u.Username, COUNT(*) as jumlah, SUM(p.TotalBayar) as omset
                             FROM penjualan p 
                             JOIN user u ON p.UserID = u.UserID
                             WHERE DATE(p.TanggalPenjualan) BETWEEN '$filterMulai' AND '$filterSampai'
                             GROUP BY u.UserID
                             ORDER BY omset DESC");

// Produk terlaris
$produkTerlaris = query($conn, "SELECT pr.NamaProduk, SUM(dp.JumlahProduk) as total_terjual, SUM(dp.Subtotal) as total_nilai
                                 FROM detailpenjualan dp
                                 JOIN produk pr ON dp.ProdukID = pr.ProdukID
                                 JOIN penjualan p ON dp.PenjualanID = p.PenjualanID
                                 WHERE DATE(p.TanggalPenjualan) BETWEEN '$filterMulai' AND '$filterSampai'
                                 GROUP BY dp.ProdukID
                                 ORDER BY total_terjual DESC
                                 LIMIT 10");
?>

<div class="page-header">
    <h2>Laporan Penjualan</h2>
    <p>Rekapitulasi dan analisis bisnis</p>
</div>

<!-- Filter -->
<div class="card">
    <div class="card-header">
        <h3>Periode Laporan</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="" style="display: grid; grid-template-columns: 1fr 1fr auto auto; gap: 15px; align-items: end;">
            <div class="form-group" style="margin: 0;">
                <label>Mulai Tanggal</label>
                <input type="date" name="mulai" class="form-control" value="<?php echo $filterMulai; ?>" required>
            </div>
            <div class="form-group" style="margin: 0;">
                <label>Sampai Tanggal</label>
                <input type="date" name="sampai" class="form-control" value="<?php echo $filterSampai; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="../../print/preview_laporan.php?mulai=<?php echo $filterMulai; ?>&sampai=<?php echo $filterSampai; ?>" 
               class="btn btn-success">Cetak Laporan</a>
        </form>
    </div>
</div>

<!-- Statistik Utama -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 25px;">
    <div class="card" style="background: #3498db; color: white; border: none;">
        <div class="card-body">
            <div style="font-size: 0.9em; opacity: 0.9; margin-bottom: 5px;">Total Transaksi</div>
            <div style="font-size: 2.8em; font-weight: 700; font-family: 'Crimson Pro', serif;"><?php echo $totalTransaksi; ?></div>
            <div style="font-size: 0.85em; opacity: 0.9; margin-top: 5px;">
                <?php echo date('d M', strtotime($filterMulai)); ?> - <?php echo date('d M Y', strtotime($filterSampai)); ?>
            </div>
        </div>
    </div>

    <div class="card" style="background: #27ae60; color: white; border: none;">
        <div class="card-body">
            <div style="font-size: 0.9em; opacity: 0.9; margin-bottom: 5px;">Total Omset</div>
            <div style="font-size: 2em; font-weight: 700; font-family: 'Crimson Pro', serif;">
                Rp <?php echo number_format($totalOmset, 0, ',', '.'); ?>
            </div>
            <div style="font-size: 0.85em; opacity: 0.9; margin-top: 5px;">
                Rata-rata: Rp <?php echo number_format($totalTransaksi > 0 ? $totalOmset / $totalTransaksi : 0, 0, ',', '.'); ?>/transaksi
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <!-- Statistik per Kasir -->
    <div class="card">
        <div class="card-header">
            <h3>Performa Kasir</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Kasir</th>
                            <th>Transaksi</th>
                            <th>Omset</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (numRows($kasirStats) > 0): ?>
                            <?php while ($row = fetchArray($kasirStats)): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['Username']); ?></strong></td>
                                    <td><?php echo $row['jumlah']; ?> transaksi</td>
                                    <td><strong>Rp <?php echo number_format($row['omset'], 0, ',', '.'); ?></strong></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--text-light);">Tidak ada data</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Produk Terlaris -->
    <div class="card">
        <div class="card-header">
            <h3>Produk Terlaris</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Terjual</th>
                            <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (numRows($produkTerlaris) > 0): ?>
                            <?php while ($row = fetchArray($produkTerlaris)): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['NamaProduk']); ?></strong></td>
                                    <td><?php echo $row['total_terjual']; ?> unit</td>
                                    <td>Rp <?php echo number_format($row['total_nilai'], 0, ',', '.'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--text-light);">Tidak ada data</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
closeConnection($conn);
require_once INCLUDES_PATH . 'footer.php';
?>
