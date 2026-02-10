<?php
require_once '../init.php';
requireLogin();

$conn = getConnection();
$id = intval($_GET['id'] ?? 0);

// Tentukan halaman kembali berdasarkan parameter 'from'
$from = $_GET['from'] ?? 'pos'; // default: pos
$backUrl = '';

if ($from === 'detail') {
    // Kembali ke detail transaksi
    $backUrl = '../components/detail_transaksi.php?id=' . $id;
} elseif ($from === 'riwayat') {
    // Kembali ke riwayat transaksi (admin)
    $backUrl = '../pages/admin/penjualan.php';
} else {
    // Default: kembali ke POS
    $backUrl = '../pages/petugas/pos.php?clear=1';
}

// Ambil data transaksi
$transaksi = fetchArray(query($conn, "SELECT p.*, pl.NamaPelanggan, u.Username, pl.KodeMember
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
    <title>Preview Nota</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 450px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .actions {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px dashed #ccc;
        }

        .btn {
            padding: 10px 20px;
            margin: 0 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-print {
            background: #27ae60;
            color: white;
        }

        .btn-back {
            background: #95a5a6;
            color: white;
        }

        .btn:hover {
            opacity: 0.8;
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

        .grand-total {
            font-size: 20px;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .container {
                box-shadow: none;
                max-width: 100%;
            }

            .actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="actions">
            <button onclick="window.print()" class="btn btn-print">Cetak</button>
            <button onclick="window.location.href='<?php echo $backUrl; ?>'" class="btn btn-back">Kembali</button>
        </div>

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
                <div style="display: flex; justify-content: space-between; margin: 8px 0; font-size: 14px;">
                    <span>Subtotal:</span>
                    <span>Rp <?php echo number_format($transaksi['TotalHarga'], 0, ',', '.'); ?></span>
                </div>
                
                <?php if ($transaksi['Diskon'] > 0): ?>
                    <div style="display: flex; justify-content: space-between; margin: 8px 0; font-size: 14px; color: #e67e22;">
                        <span>Diskon Member (<?php echo $transaksi['KodeMember']; ?>):</span>
                        <span>- Rp <?php echo number_format($transaksi['Diskon'], 0, ',', '.'); ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="grand-total">
                    <span>TOTAL BAYAR:</span>
                    <span>Rp <?php echo number_format($transaksi['TotalBayar'], 0, ',', '.'); ?></span>
                </div>
            </div>

            <div class="footer">
                <p>== TERIMA KASIH ==</p>
                <p>Barang yang sudah dibeli</p>
                <p>tidak dapat dikembalikan</p>
            </div>
        </div>
    </div>
</body>
</html>
<?php closeConnection($conn); ?>