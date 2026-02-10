<?php
require_once '../init.php';
requireLogin();

$conn = getConnection();
$id = intval($_GET['id'] ?? 0);

// Ambil data transaksi
$transaksi = fetchArray(query($conn, "SELECT p.*, pl.NamaPelanggan, u.Username 
                                        FROM penjualan p 
                                        JOIN pelanggan pl ON p.PelangganID = pl.PelangganID 
                                        JOIN user u ON p.UserID = u.UserID 
                                        WHERE p.PenjualanID = $id"));

if (!$transaksi) {
    die("Transaksi tidak ditemukan!");
}

// Ambil detail produk
$detailList = query($conn, "SELECT dp.*, pr.NamaProduk 
                             FROM detailpenjualan dp 
                             JOIN produk pr ON dp.ProdukID = pr.ProdukID 
                             WHERE dp.PenjualanID = $id");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota #<?php echo str_pad($id, 6, '0', STR_PAD_LEFT); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            padding: 20px;
            max-width: 400px;
            margin: 0 auto;
        }

        .nota {
            border: 2px dashed #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px dashed #333;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 12px;
            margin: 3px 0;
        }

        .info {
            margin-bottom: 15px;
            font-size: 13px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }

        .items {
            border-top: 2px dashed #333;
            border-bottom: 2px dashed #333;
            padding: 15px 0;
            margin: 15px 0;
        }

        .item {
            margin: 10px 0;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 3px;
        }

        .item-detail {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }

        .total {
            margin-top: 15px;
            font-size: 16px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
        }

        .grand-total {
            font-size: 20px;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
        }

        @media print {
            body {
                padding: 0;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="no-print" style="padding: 10px 20px; margin-bottom: 20px; cursor: pointer; background: #3498db; color: white; border: none; border-radius: 5px; font-size: 14px;">
        🖨️ Cetak Nota
    </button>

    <div class="nota">
        <div class="header">
            <h1>Nota Kasir</h1>
            <p>Jl. adalah No. 123, Kota Pekanbaru</p>
            <p>Telp: 081122334455</p>
        </div>

        <div class="info">
            <div class="info-row">
                <span>Nota:</span>
                <strong>#<?php echo str_pad($id, 6, '0', STR_PAD_LEFT); ?></strong>
            </div>
            <div class="info-row">
                <span>Tanggal:</span>
                <span><?php echo date('d/m/Y H:i', strtotime($transaksi['TanggalPenjualan'])); ?></span>
            </div>
            <div class="info-row">
                <span>Kasir:</span>
                <span><?php echo htmlspecialchars($transaksi['Username']); ?></span>
            </div>
            <div class="info-row">
                <span>Pelanggan:</span>
                <span><?php echo htmlspecialchars($transaksi['NamaPelanggan']); ?></span>
            </div>
        </div>

        <div class="items">
            <?php while ($detail = fetchArray($detailList)): ?>
                <div class="item">
                    <div class="item-name"><?php echo htmlspecialchars($detail['NamaProduk']); ?></div>
                    <div class="item-detail">
                        <span><?php echo $detail['JumlahProduk']; ?> x Rp <?php echo number_format($detail['Subtotal'] / $detail['JumlahProduk'], 0, ',', '.'); ?></span>
                        <strong>Rp <?php echo number_format($detail['Subtotal'], 0, ',', '.'); ?></strong>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="total">
            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>Rp <?php echo number_format($transaksi['TotalHarga'], 0, ',', '.'); ?></span>
            </div>
        </div>

        <div class="footer">
            <p>== TERIMA KASIH ==</p>
            <p>Barang yang sudah dibeli</p>
            <p>tidak dapat dikembalikan</p>
        </div>
    </div>
</body>
</html>
<?php closeConnection($conn); ?>
