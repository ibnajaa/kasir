<?php
$pageTitle = 'Detail Transaksi';
require_once '../init.php';
require_once INCLUDES_PATH . 'header.php';
requireAdmin();

$conn = getConnection();

$id = intval($_GET['id'] ?? 0);

// Ambil data transaksi
$transaksi = fetchArray(query($conn, "SELECT p.*, pl.NamaPelanggan, pl.KodeMember, pl.Alamat, pl.NomorTelepon, u.Username
                                        FROM penjualan p 
                                        JOIN pelanggan pl ON p.PelangganID = pl.PelangganID 
                                        JOIN user u ON p.UserID = u.UserID 
                                        WHERE p.PenjualanID = $id"));

if (!$transaksi) {
    echo "<script>alert('Transaksi tidak ditemukan!'); window.location.href='penjualan.php';</script>";
    exit();
}

// Ambil detail produk
$detailList = query($conn, "SELECT dp.*, pr.NamaProduk 
                             FROM detailpenjualan dp 
                             JOIN produk pr ON dp.ProdukID = pr.ProdukID 
                             WHERE dp.PenjualanID = $id");
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <div>
            <h2>Detail Transaksi</h2>
            <p>Nota #<?php echo str_pad($id, 6, '0', STR_PAD_LEFT); ?></p>
        </div>
        <div>
            <a href="../pages/admin/penjualan.php" class="btn btn-secondary">Kembali</a>
            <a href="../print/preview_nota.php?id=<?php echo $id; ?>&from=detail" class="btn btn-success">Cetak Nota</a>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <!-- Info Transaksi -->
    <div class="card">
        <div class="card-header">
            <h3>Informasi Transaksi</h3>
        </div>
        <div class="card-body">
            <table style="width: 100%;">
                <tr>
                    <td style="padding: 10px 0; font-weight: 600; width: 40%;">Nomor Nota:</td>
                    <td style="padding: 10px 0;">#<?php echo str_pad($id, 6, '0', STR_PAD_LEFT); ?></td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; font-weight: 600;">Tanggal & Waktu:</td>
                    <td style="padding: 10px 0;"><?php echo date('d/m/Y H:i:s', strtotime($transaksi['TanggalPenjualan'])); ?></td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; font-weight: 600;">Kasir:</td>
                    <td style="padding: 10px 0;"><?php echo htmlspecialchars($transaksi['Username']); ?></td>
                </tr>
                <?php if ($transaksi['KodeMember']): ?>
                <tr>
                    <td style="padding: 10px 0; font-weight: 600;">Member:</td>
                    <td style="padding: 10px 0;">
                        <span style="background: #3498db; color: white; padding: 4px 12px; border-radius: 4px; font-size: 0.9em;">
                            <?php echo htmlspecialchars($transaksi['KodeMember']); ?>
                        </span>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td style="padding: 10px 0; font-weight: 600; border-top: 2px solid var(--border); padding-top: 20px;">Subtotal:</td>
                    <td style="padding: 10px 0; border-top: 2px solid var(--border); padding-top: 20px;">
                        <strong>Rp <?php echo number_format($transaksi['TotalHarga'], 0, ',', '.'); ?></strong>
                    </td>
                </tr>
                <?php if ($transaksi['Diskon'] > 0): ?>
                <tr>
                    <td style="padding: 10px 0; font-weight: 600;">Diskon Member (10%):</td>
                    <td style="padding: 10px 0;">
                        <strong style="color: #e67e22;">- Rp <?php echo number_format($transaksi['Diskon'], 0, ',', '.'); ?></strong>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td style="padding: 10px 0; font-weight: 600; border-top: 2px solid var(--border); padding-top: 20px;">Total Pembayaran:</td>
                    <td style="padding: 10px 0; border-top: 2px solid var(--border); padding-top: 20px;">
                        <strong style="font-size: 1.3em; color: var(--success);">
                            Rp <?php echo number_format($transaksi['TotalBayar'], 0, ',', '.'); ?>
                        </strong>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Info Pelanggan -->
    <div class="card">
        <div class="card-header">
            <h3>Informasi Pelanggan</h3>
        </div>
        <div class="card-body">
            <table style="width: 100%;">
                <tr>
                    <td style="padding: 10px 0; font-weight: 600; width: 40%;">Nama:</td>
                    <td style="padding: 10px 0;"><?php echo htmlspecialchars($transaksi['NamaPelanggan']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; font-weight: 600;">Alamat:</td>
                    <td style="padding: 10px 0;"><?php echo htmlspecialchars($transaksi['Alamat']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; font-weight: 600;">No. Telepon:</td>
                    <td style="padding: 10px 0;"><?php echo htmlspecialchars($transaksi['NomorTelepon']); ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<!-- Detail Produk -->
<div class="card">
    <div class="card-header">
        <h3>Detail Produk</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>Harga Satuan</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($detail = fetchArray($detailList)): 
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><strong><?php echo htmlspecialchars($detail['NamaProduk']); ?></strong></td>
                            <td>Rp <?php echo number_format($detail['Subtotal'] / $detail['JumlahProduk'], 0, ',', '.'); ?></td>
                            <td><?php echo $detail['JumlahProduk']; ?> unit</td>
                            <td><strong>Rp <?php echo number_format($detail['Subtotal'], 0, ',', '.'); ?></strong></td>
                        </tr>
                    <?php endwhile; ?>
                    <tr style="background: var(--light-bg); font-weight: 700;">
                        <td colspan="4" style="text-align: right; padding: 15px;">SUBTOTAL:</td>
                        <td style="font-size: 1.1em;">
                            Rp <?php echo number_format($transaksi['TotalHarga'], 0, ',', '.'); ?>
                        </td>
                    </tr>
                    <?php if ($transaksi['Diskon'] > 0): ?>
                    <tr style="background: var(--light-bg); font-weight: 700; color: #e67e22;">
                        <td colspan="4" style="text-align: right; padding: 15px;">DISKON MEMBER (10%):</td>
                        <td style="font-size: 1.1em;">
                            - Rp <?php echo number_format($transaksi['Diskon'], 0, ',', '.'); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <tr style="background: var(--light-bg); font-weight: 700;">
                        <td colspan="4" style="text-align: right; padding: 15px;">GRAND TOTAL:</td>
                        <td style="font-size: 1.2em; color: var(--success);">
                            Rp <?php echo number_format($transaksi['TotalBayar'], 0, ',', '.'); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
closeConnection($conn);
require_once INCLUDES_PATH . 'footer.php';
?>