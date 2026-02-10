<?php
require_once '../../init.php';
requireLogin();

$conn = getConnection();
$success = '';
$error = '';

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

function generateKodeMember($conn) {
    $result = query($conn, "SELECT KodeMember FROM pelanggan WHERE KodeMember IS NOT NULL ORDER BY PelangganID DESC LIMIT 1");
    if (numRows($result) > 0) {
        $last = fetchArray($result);
        $lastNumber = intval(substr($last['KodeMember'], 3));
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }
    return 'MBR' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
}

if (isset($_POST['tambah'])) {
    $kodeMember = generateKodeMember($conn);
    $nama = escapeString($conn, $_POST['nama']);
    $alamat = escapeString($conn, $_POST['alamat']);
    $notelp = escapeString($conn, $_POST['notelp']);
    
    $sql = "INSERT INTO pelanggan (NamaPelanggan, KodeMember, Alamat, NomorTelepon) VALUES ('$nama', '$kodeMember', '$alamat', '$notelp')";
    if (query($conn, $sql)) {
        $_SESSION['success'] = "Member berhasil ditambahkan dengan kode: $kodeMember";
        header("Location: member.php");
        exit();
    } else {
        $error = 'Gagal menambahkan member!';
    }
}

if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $nama = escapeString($conn, $_POST['nama']);
    $alamat = escapeString($conn, $_POST['alamat']);
    $notelp = escapeString($conn, $_POST['notelp']);
    
    $sql = "UPDATE pelanggan SET NamaPelanggan='$nama', Alamat='$alamat', NomorTelepon='$notelp' WHERE PelangganID=$id";
    if (query($conn, $sql)) {
        $_SESSION['success'] = 'Member berhasil diupdate!';
        header("Location: member.php");
        exit();
    } else {
        $error = 'Gagal mengupdate member!';
    }
}

if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $sql = "DELETE FROM pelanggan WHERE PelangganID=$id AND KodeMember IS NOT NULL";
    if (query($conn, $sql)) {
        $_SESSION['success'] = 'Member berhasil dihapus!';
        header("Location: member.php");
        exit();
    } else {
        $error = 'Gagal menghapus member!';
    }
}

$pageTitle = 'Data Member';
require_once INCLUDES_PATH . 'header.php';

$search = isset($_GET['search']) ? escapeString($conn, $_GET['search']) : '';

if ($search) {
    $memberList = query($conn, "SELECT * FROM pelanggan WHERE KodeMember IS NOT NULL AND (KodeMember LIKE '%$search%' OR NamaPelanggan LIKE '%$search%') ORDER BY PelangganID ASC");
} else {
    $memberList = query($conn, "SELECT * FROM pelanggan WHERE KodeMember IS NOT NULL ORDER BY PelangganID ASC");
}

$editData = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $result = query($conn, "SELECT * FROM pelanggan WHERE PelangganID=$editId AND KodeMember IS NOT NULL");
    $editData = fetchArray($result);
}

// Hitung total member
$totalMember = numRows($memberList);
mysqli_data_seek($memberList, 0);
?>

<div class="page-header">
    <h2>Data Member</h2>
    <p>Kelola data member untuk diskon khusus</p>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Info Card -->
<div class="card" style="margin-bottom: 20px; background: #3498db; color: white; border: none;">
    <div class="card-body">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-size: 0.9em; opacity: 0.9;">Total Member Terdaftar</div>
                <div style="font-size: 2.5em; font-weight: 700; font-family: 'Crimson Pro', serif;"><?php echo $totalMember; ?> Member</div>
            </div>
            <div style="font-size: 0.9em; opacity: 0.9;">
                <strong>Benefit Member:</strong> Diskon 10% setiap transaksi
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
    <!-- Form Tambah/Edit Member -->
    <div class="card">
        <div class="card-header">
            <h3><?php echo $editData ? 'Edit Member' : 'Tambah Member'; ?></h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?php echo $editData['PelangganID']; ?>">
                    <div class="form-group">
                        <label>Kode Member</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($editData['KodeMember']); ?>" readonly>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info" style="margin-bottom: 20px;">
                        Kode member akan digenerate otomatis
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" 
                           value="<?php echo $editData ? htmlspecialchars($editData['NamaPelanggan']) : ''; ?>" 
                           required placeholder="Masukkan nama lengkap">
                </div>

                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat" class="form-control" rows="3" 
                              required placeholder="Masukkan alamat lengkap"><?php echo $editData ? htmlspecialchars($editData['Alamat']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" name="notelp" class="form-control" 
                           value="<?php echo $editData ? htmlspecialchars($editData['NomorTelepon']) : ''; ?>" 
                           required placeholder="08xxxxxxxxxx">
                </div>

                <?php if ($editData): ?>
                    <button type="submit" name="edit" class="btn btn-warning" style="width: 100%;">
                        Update Member
                    </button>
                    <a href="member.php" class="btn btn-secondary" style="width: 100%; margin-top: 10px;">
                        Batal
                    </a>
                <?php else: ?>
                    <button type="submit" name="tambah" class="btn btn-success" style="width: 100%;">
                        Tambah Member
                    </button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Daftar Member -->
    <div class="card">
        <div class="card-header">
            <h3>Daftar Member</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="" style="margin-bottom: 20px;">
                <div style="display: flex; gap: 10px;">
                    <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan Kode Member atau Nama..." 
                           value="<?php echo htmlspecialchars($search); ?>" style="flex: 1;">
                    <button type="submit" class="btn btn-primary">Cari</button>
                    <?php if ($search): ?>
                        <a href="member.php" class="btn btn-secondary">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Kode Member</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>No. Telepon</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (numRows($memberList) > 0): ?>
                            <?php while ($row = fetchArray($memberList)): ?>
                                <tr>
                                    <td>
                                        <strong style="background: #3498db; color: white; padding: 4px 12px; border-radius: 4px; font-size: 0.9em;">
                                            <?php echo htmlspecialchars($row['KodeMember']); ?>
                                        </strong>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($row['NamaPelanggan']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['Alamat']); ?></td>
                                    <td><?php echo htmlspecialchars($row['NomorTelepon']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <a href="?edit=<?php echo $row['PelangganID']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="?hapus=<?php echo $row['PelangganID']; ?>" class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Yakin ingin menghapus member ini?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-light);">
                                    <?php echo $search ? "Tidak ada member yang cocok dengan pencarian \"$search\"" : "Belum ada member terdaftar"; ?>
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