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
    $totalOmset += $row['TotalBayar'];
}
mysqli_data_seek($transaksiList, 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Preview Laporan Petugas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .actions {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #ddd;
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

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .header p {
            font-size: 14px;
            color: #666;
        }

        .info {
            background: #ecf0f1;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            border-left: 4px solid #3498db;
        }

        .stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            border-left: 4px solid #3498db;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th {
            background: #2c3e50;
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

        table tr:last-child {
            background: #ecf0f1;
            font-weight: bold;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #2c3e50;
            text-align: right;
            font-size: 12px;
            color: #666;
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
            <a href="../pages/petugas/laporan_petugas.php?mulai=<?php echo $filterMulai; ?>&sampai=<?php echo $filterSampai; ?>" class="btn btn-back">Kembali</a>
        </div>

        <div class="header">
            <h1>LAPORAN PETUGAS</h1>
            <p>Ibn Project</p>
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

        <h3 style="margin-bottom: 15px; color: #2c3e50;">Rincian Transaksi</h3>
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
                        <td>Rp <?php echo number_format($row['TotalBayar'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endwhile; ?>
                <tr>
                    <td colspan="4" style="text-align: right; padding: 15px;">GRAND TOTAL:</td>
                    <td style="font-size: 16px;">Rp <?php echo number_format($totalOmset, 0, ',', '.'); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <p>Dicetak pada: <?php echo date('d F Y, H:i'); ?> WIB</p>
            <p>Kasir Pro © <?php echo date('Y'); ?></p>
        </div>
    </div>
</body>
</html>
<?php closeConnection($conn); ?>
