<?php
$pageTitle = 'Laporan Saya';
require_once '../../init.php';
require_once INCLUDES_PATH . 'header.php';

$conn = getConnection();
$userInfo = getUserInfo();
$userId = $userInfo['id'];

// Filter tanggal
$filterMulai = $_GET['mulai'] ?? date('Y-m-01');
$filterSampai = $_GET['sampai'] ?? date('Y-m-d');

// Query transaksi petugas
$sql = "SELECT p.*, pl.NamaPelanggan 
        FROM penjualan p 
        JOIN pelanggan pl ON p.PelangganID = pl.PelangganID 
        WHERE p.UserID = $userId AND DATE(p.TanggalPenjualan) BETWEEN '$filterMulai' AND '$filterSampai'
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
?>

<div class="page-header">
    <h2>Laporan Transaksi Saya</h2>
    <p>Rekap penjualan Anda</p>
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
            <a href="../../print/preview_laporan_petugas.php?mulai=<?php echo $filterMulai; ?>&sampai=<?php echo $filterSampai; ?>" 
               class="btn btn-success">Cetak</a>
        </form>
    </div>
</div>

<!-- Statistik -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px;">
    <div class="card" style="background: #3498db; color: white; border: none;">
        <div class="card-body">
            <div style="font-size: 0.9em; opacity: 0.9; margin-bottom: 5px;">Total Transaksi</div>
            <div style="font-size: 2.8em; font-weight: 700; font-family: 'Crimson Pro', serif;"><?php echo $totalTransaksi; ?></div>
        </div>
    </div>

    <div class="card" style="background: #27ae60; color: white; border: none;">
        <div class="card-body">
            <div style="font-size: 0.9em; opacity: 0.9; margin-bottom: 5px;">Total Omset</div>
            <div style="font-size: 1.8em; font-weight: 700; font-family: 'Crimson Pro', serif;">
                Rp <?php echo number_format($totalOmset, 0, ',', '.'); ?>
            </div>
        </div>
    </div>
</div>

<!-- Daftar Transaksi -->
<div class="card">
    <div class="card-header">
        <h3>Rincian Transaksi</h3>
    </div>
    <div class="card-body">
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
                    <?php if (numRows($transaksiList) > 0): ?>
                        <?php while ($row = fetchArray($transaksiList)): ?>
                            <tr>
                                <td><strong>#<?php echo str_pad($row['PenjualanID'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['TanggalPenjualan'])); ?></td>
                                <td><?php echo htmlspecialchars($row['NamaPelanggan']); ?></td>
                                <td><strong>Rp <?php echo number_format($row['TotalBayar'], 0, ',', '.'); ?></strong></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 40px; color: var(--text-light);">
                                Tidak ada transaksi pada periode ini
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
closeConnection($conn);
require_once INCLUDES_PATH . 'footer.php';
?>
