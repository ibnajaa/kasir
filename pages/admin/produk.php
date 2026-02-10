<?php
require_once '../../init.php';
requireAdmin();

$conn = getConnection();
$success = '';
$error = '';

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_POST['tambah'])) {
    $nama = escapeString($conn, $_POST['nama']);
    $harga = floatval($_POST['harga']);
    $stok = intval($_POST['stok']);
    
    $sql = "INSERT INTO produk (NamaProduk, Harga, Stok) VALUES ('$nama', $harga, $stok)";
    if (query($conn, $sql)) {
        $_SESSION['success'] = 'Produk berhasil ditambahkan!';
        header("Location: produk.php");
        exit();
    } else {
        $error = 'Gagal menambahkan produk!';
    }
}

if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $nama = escapeString($conn, $_POST['nama']);
    $harga = floatval($_POST['harga']);
    $stok = intval($_POST['stok']);
    
    $sql = "UPDATE produk SET NamaProduk='$nama', Harga=$harga, Stok=$stok WHERE ProdukID=$id";
    if (query($conn, $sql)) {
        $_SESSION['success'] = 'Produk berhasil diupdate!';
        header("Location: produk.php");
        exit();
    } else {
        $error = 'Gagal mengupdate produk!';
    }
}

if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $sql = "DELETE FROM produk WHERE ProdukID=$id";
    if (query($conn, $sql)) {
        $_SESSION['success'] = 'Produk berhasil dihapus!';
        header("Location: produk.php");
        exit();
    } else {
        $error = 'Gagal menghapus produk!';
    }
}

$pageTitle = 'Data Produk';
require_once INCLUDES_PATH . 'header.php';

$search = isset($_GET['search']) ? escapeString($conn, $_GET['search']) : '';

if ($search) {
    $produkList = query($conn, "SELECT * FROM produk WHERE ProdukID LIKE '%$search%' OR NamaProduk LIKE '%$search%' ORDER BY ProdukID ASC");
} else {
    $produkList = query($conn, "SELECT * FROM produk ORDER BY ProdukID ASC");
}

$totalAset = mysqli_fetch_assoc(query($conn, "SELECT SUM(Harga * Stok) as total FROM produk"))['total'] ?? 0;

$editData = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $result = query($conn, "SELECT * FROM produk WHERE ProdukID=$editId");
    $editData = fetchArray($result);
}
?>

<div class="page-header">
    <h2>Data Produk</h2>
    <p>Kelola inventaris dan stok barang</p>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
    <!-- Form Tambah/Edit Produk -->
    <div class="card">
        <div class="card-header">
            <h3><?php echo $editData ? 'Edit Produk' : 'Tambah Produk'; ?></h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?php echo $editData['ProdukID']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="nama" class="form-control" 
                           value="<?php echo $editData ? htmlspecialchars($editData['NamaProduk']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input type="number" name="harga" class="form-control" step="0.01" 
                           value="<?php echo $editData ? $editData['Harga'] : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>Stok</label>
                    <input type="number" name="stok" class="form-control" 
                           value="<?php echo $editData ? $editData['Stok'] : ''; ?>" required>
                </div>

                <?php if ($editData): ?>
                    <button type="submit" name="edit" class="btn btn-warning" style="width: 100%;">
                        Update Produk
                    </button>
                    <a href="produk.php" class="btn btn-secondary" style="width: 100%; margin-top: 10px;">
                        Batal
                    </a>
                <?php else: ?>
                    <button type="submit" name="tambah" class="btn btn-success" style="width: 100%;">
                        Tambah Produk
                    </button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Daftar Produk -->
    <div class="card">
        <div class="card-header">
            <div>
                <h3>Daftar Produk</h3>
                <p style="margin-top: 5px; color: var(--text-light); font-size: 0.9em;">
                    Total Nilai Aset: <strong style="color: var(--success);">Rp <?php echo number_format($totalAset, 0, ',', '.'); ?></strong>
                </p>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="" style="margin-bottom: 20px;">
                <div style="display: flex; gap: 10px;">
                    <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan ID atau Nama Produk..." 
                           value="<?php echo htmlspecialchars($search); ?>" style="flex: 1;">
                    <button type="submit" class="btn btn-primary">Cari</button>
                    <?php if ($search): ?>
                        <a href="produk.php" class="btn btn-secondary">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Produk</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Nilai</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (numRows($produkList) > 0): ?>
                            <?php while ($row = fetchArray($produkList)): ?>
                                <tr>
                                    <td><?php echo $row['ProdukID']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['NamaProduk']); ?></strong></td>
                                    <td>Rp <?php echo number_format($row['Harga'], 0, ',', '.'); ?></td>
                                    <td>
                                        <span style="background: <?php echo $row['Stok'] < 20 ? '#ff6b6b' : '#00b894'; ?>; 
                                                     color: white; padding: 4px 12px; border-radius: 20px; 
                                                     font-size: 0.85em; font-weight: 600;">
                                            <?php echo $row['Stok']; ?> unit
                                        </span>
                                    </td>
                                    <td>Rp <?php echo number_format($row['Harga'] * $row['Stok'], 0, ',', '.'); ?></td>
                                    <td>
                                        <?php if ($row['Stok'] < 20): ?>
                                            <span style="background: #fff3cd; color: #856404; padding: 4px 12px; 
                                                         border-radius: 20px; font-size: 0.85em; font-weight: 600;">
                                                Rendah
                                            </span>
                                        <?php else: ?>
                                            <span style="background: #d4edda; color: #155724; padding: 4px 12px; 
                                                         border-radius: 20px; font-size: 0.85em; font-weight: 600;">
                                                Aman
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?edit=<?php echo $row['ProdukID']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="?hapus=<?php echo $row['ProdukID']; ?>" class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-light);">
                                    <?php echo $search ? "Tidak ada produk yang cocok dengan pencarian \"$search\"" : "Belum ada produk"; ?>
                                </td>
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