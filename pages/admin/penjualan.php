<?php
require_once '../../init.php';

$pageTitle = 'Riwayat Transaksi';
require_once INCLUDES_PATH . 'header.php';
requireAdmin();

$conn = getConnection();
$success = '';
$error = '';

// Filter tanggal
$filterMulai = $_GET['mulai'] ?? date('Y-m-01');
$filterSampai = $_GET['sampai'] ?? date('Y-m-d');

// Hapus transaksi
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    
    // Kembalikan stok
    $details = query($conn, "SELECT * FROM detailpenjualan WHERE PenjualanID=$id");
    while ($detail = fetchArray($details)) {
        query($conn, "UPDATE produk SET Stok = Stok + " . $detail['JumlahProduk'] . " WHERE ProdukID = " . $detail['ProdukID']);
    }
    
    if (query($conn, "DELETE FROM penjualan WHERE PenjualanID=$id")) {
        $success = 'Transaksi berhasil dihapus dan stok dikembalikan!';
    } else {
        $error = 'Gagal menghapus transaksi!';
    }
}

// Query transaksi
$sql = "SELECT p.*, pl.NamaPelanggan, u.Username 
        FROM penjualan p 
        JOIN pelanggan pl ON p.PelangganID = pl.PelangganID 
        JOIN user u ON p.UserID = u.UserID 
        WHERE DATE(p.TanggalPenjualan) BETWEEN '$filterMulai' AND '$filterSampai'
        ORDER BY p.TanggalPenjualan DESC";
$transaksiList = query($conn, $sql);

// Hitung total
$totalOmset = 0;
$totalTransaksi = numRows($transaksiList);
mysqli_data_seek($transaksiList, 0);
while ($row = fetchArray($transaksiList)) {
    $totalOmset += $row['TotalBayar'];
}
mysqli_data_seek($transaksiList, 0);
?>

<div class="page-header">
    <h2>Riwayat Transaksi</h2>
    <p>Monitor semua transaksi penjualan</p>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Statistik -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px;">
    <div class="card" style="background: #27ae60; color: white; border: none;">
        <div class="card-body">
            <div style="font-size: 0.9em; opacity: 0.9; margin-bottom: 5px;">Total Transaksi</div>
            <div style="font-size: 2.5em; font-weight: 700; font-family: 'Crimson Pro', serif;"><?php echo $totalTransaksi; ?></div>
        </div>
    </div>

    <div class="card" style="background: #e67e22; color: white; border: none;">
        <div class="card-body">
            <div style="font-size: 0.9em; opacity: 0.9; margin-bottom: 5px;">Total Omset</div>
            <div style="font-size: 1.8em; font-weight: 700; font-family: 'Crimson Pro', serif;">
                Rp <?php echo number_format($totalOmset, 0, ',', '.'); ?>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card">
    <div class="card-header">
        <h3>Filter Transaksi</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 15px; align-items: end;">
            <div class="form-group" style="margin: 0;">
                <label>Mulai Tanggal</label>
                <input type="date" name="mulai" class="form-control" value="<?php echo $filterMulai; ?>" required>
            </div>
            <div class="form-group" style="margin: 0;">
                <label>Sampai Tanggal</label>
                <input type="date" name="sampai" class="form-control" value="<?php echo $filterSampai; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>
</div>

<!-- Tabel Transaksi -->
<div class="card">
    <div class="card-header">
        <h3>Daftar Transaksi</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nomor Nota</th>
                        <th>Waktu</th>
                        <th>Pelanggan</th>
                        <th>Kasir</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (numRows($transaksiList) > 0): ?>
                        <?php while ($row = fetchArray($transaksiList)): ?>
                            <tr>
                                <td><strong>#<?php echo str_pad($row['PenjualanID'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['TanggalPenjualan'])); ?></td>
                                <td><?php echo htmlspecialchars($row['NamaPelanggan']); ?></td>
                                <td><?php echo htmlspecialchars($row['Username']); ?></td>
                                <td><strong>Rp <?php echo number_format($row['TotalBayar'], 0, ',', '.'); ?></strong></td>
                                <td>
                                    <a href="../../components/detail_transaksi.php?id=<?php echo $row['PenjualanID']; ?>" 
                                       class="btn btn-info btn-sm">Detail</a>
                                    <a href="../../print/preview_nota.php?id=<?php echo $row['PenjualanID']; ?>&from=riwayat" 
                                       class="btn btn-secondary btn-sm">Cetak</a>
                                    <a href="?hapus=<?php echo $row['PenjualanID']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Yakin ingin menghapus transaksi ini? Stok akan dikembalikan.')">
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-light);">
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