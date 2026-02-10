<?php
require_once '../init.php';
requireLogin();

$conn = getConnection();
$userInfo = getUserInfo();
$userId = $userInfo['id'];

$filterMulai = $_GET['mulai'] ?? date('Y-m-01');
$filterSampai = $_GET['sampai'] ?? date('Y-m-d');

// Query transaksi petugas
$sql = "SELECT p.*, pl.NamaPelanggan 
        FROM penjualan p 
        JOIN pelanggan pl ON p.PelangganID = pl.PelangganID 
        WHERE p.UserID = $userId AND DATE(p.TanggalPenjualan) BETWEEN '$filterMulai' AND '$filterSampai'
        ORDER BY p.TanggalPenjualan ASC";
$transaksiList = query($conn, $sql);

$totalOmset = 0;
$totalTransaksi = numRows($transaksiList);
mysqli_data_seek($transaksiList, 0);
while ($row = fetchArray($transaksiList)) {
    $totalOmset += $row['TotalHarga'];
}
mysqli_data_seek($transaksiList, 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Petugas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            padding: 30px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 14px;
            color: #666;
        }

        .info {
            background: #f0f0f0;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: #f8f8f8;
            padding: 15px;
            border-left: 4px solid #3498db;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th {
            background: #333;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 13px;
        }

        table td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
            font-size: 13px;
        }

        table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #333;
            text-align: right;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="no-print" style="padding: 10px 20px; margin-bottom: 20px; cursor: pointer; background: #3498db; color: white; border: none; border-radius: 5px;">
        🖨️ Cetak Laporan
    </button>

    <div class="header">
        <h1>LAPORAN PETUGAS</h1>
        <p>Ibn Project/p>
    </div>

    <div class="info">
        <strong>Kasir:</strong> <?php echo htmlspecialchars($userInfo['username']); ?><br>
        <strong>Periode:</strong> <?php echo date('d F Y', strtotime($filterMulai)); ?> - <?php echo date('d F Y', strtotime($filterSampai)); ?>
    </div>

    <div class="stats">
        <div class="stat-box">
            <div class="stat-label">Total Transaksi</div>
            <div class="stat-value"><?php echo $totalTransaksi; ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Total Omset</div>
            <div class="stat-value">Rp <?php echo number_format($totalOmset, 0, ',', '.'); ?></div>
        </div>
    </div>

    <h3>Rincian Transaksi</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nota</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while ($row = fetchArray($transaksiList)): 
            ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td>#<?php echo str_pad($row['PenjualanID'], 6, '0', STR_PAD_LEFT); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($row['TanggalPenjualan'])); ?></td>
                    <td><?php echo htmlspecialchars($row['NamaPelanggan']); ?></td>
                    <td>Rp <?php echo number_format($row['TotalHarga'], 0, ',', '.'); ?></td>
                </tr>
            <?php endwhile; ?>
            <tr style="font-weight: bold; background: #e0e0e0;">
                <td colspan="4" style="text-align: right; padding: 15px;">GRAND TOTAL:</td>
                <td style="font-size: 16px;">Rp <?php echo number_format($totalOmset, 0, ',', '.'); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: <?php echo date('d F Y, H:i'); ?> WIB</p>
        <p>Kasir Pro © <?php echo date('Y'); ?></p>
    </div>
</body>
</html>
<?php closeConnection($conn); ?>
