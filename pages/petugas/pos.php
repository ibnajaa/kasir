<?php
require_once '../../init.php';
requireLogin();

$conn = getConnection();
$userInfo = getUserInfo();
$success = '';
$error = '';

// Proses tambah member dari POS
if (isset($_POST['tambah_member_pos'])) {
    if (isset($_POST['keranjang_temp']) && !empty($_POST['keranjang_temp'])) {
        $_SESSION['keranjang_temp'] = $_POST['keranjang_temp'];
    }
    
    // Generate kode member
    $result = query($conn, "SELECT KodeMember FROM pelanggan WHERE KodeMember IS NOT NULL ORDER BY PelangganID DESC LIMIT 1");
    if (numRows($result) > 0) {
        $last = fetchArray($result);
        $lastNumber = intval(substr($last['KodeMember'], 3));
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }
    $kodeMember = 'MBR' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    
    $nama = escapeString($conn, $_POST['nama_member']);
    $alamat = escapeString($conn, $_POST['alamat_member']);
    $notelp = escapeString($conn, $_POST['notelp_member']);
    
    $sql = "INSERT INTO pelanggan (NamaPelanggan, KodeMember, Alamat, NomorTelepon) VALUES ('$nama', '$kodeMember', '$alamat', '$notelp')";
    if (query($conn, $sql)) {
        // Set session untuk auto-fill kode member
        $_SESSION['new_member_code'] = $kodeMember;
        $_SESSION['new_member_name'] = $nama;
        header("Location: pos.php");
        exit();
    } else {
        $error = 'Gagal menambahkan member!';
    }
}

// Proses transaksi
if (isset($_POST['proses_transaksi'])) {
    $namaPelanggan = escapeString($conn, $_POST['nama_pelanggan']);
    $kodeMember = escapeString($conn, $_POST['kode_member']);
    $keranjang = json_decode($_POST['keranjang_data'], true);
    $totalHarga = floatval($_POST['total_harga']);
    
    // Cek member untuk diskon
    $pelangganId = null;
    $diskon = 0;
    $memberValid = true;
    
    if (!empty($kodeMember)) {
        // Cari member berdasarkan kode
        $memberResult = query($conn, "SELECT * FROM pelanggan WHERE KodeMember = '$kodeMember'");
        if (numRows($memberResult) > 0) {
            $memberData = fetchArray($memberResult);
            $pelangganId = $memberData['PelangganID'];
            $namaPelanggan = $memberData['NamaPelanggan']; 
            $diskon = $totalHarga * 0.10; // Diskon 10%
        } else {
            // Kode member tidak ditemukan
            $error = "Kode member '$kodeMember' tidak ditemukan! Silakan periksa kembali atau kosongkan jika bukan member.";
            $memberValid = false;
        }
    }
    
    $totalBayar = $totalHarga - $diskon;
    
    if ($memberValid && !empty($keranjang) && $totalHarga > 0) {
        // Jika bukan member atau member tidak ditemukan, cari atau buat pelanggan biasa
        if (!$pelangganId) {
            $checkPelanggan = query($conn, "SELECT PelangganID FROM pelanggan WHERE NamaPelanggan = '$namaPelanggan' AND KodeMember IS NULL");
            
            if (numRows($checkPelanggan) > 0) {
                $pelangganId = fetchArray($checkPelanggan)['PelangganID'];
            } else {
                query($conn, "INSERT INTO pelanggan (NamaPelanggan, KodeMember, Alamat, NomorTelepon) VALUES ('$namaPelanggan', NULL, '-', '-')");
                $pelangganId = lastInsertId($conn);
            }
        }
        
        // Insert penjualan dengan diskon
        $userId = $userInfo['id'];
        $sql = "INSERT INTO penjualan (TanggalPenjualan, TotalHarga, Diskon, TotalBayar, PelangganID, UserID) 
                VALUES (NOW(), $totalHarga, $diskon, $totalBayar, $pelangganId, $userId)";
        
        if (query($conn, $sql)) {
            $penjualanId = lastInsertId($conn);
            
            // Insert detail dan update stok
            $berhasil = true;
            foreach ($keranjang as $item) {
                $produkId = intval($item['id']);
                $jumlah = intval($item['qty']);
                $subtotal = floatval($item['subtotal']);
                
                $sqlDetail = "INSERT INTO detailpenjualan (PenjualanID, ProdukID, JumlahProduk, Subtotal) 
                              VALUES ($penjualanId, $produkId, $jumlah, $subtotal)";
                
                if (query($conn, $sqlDetail)) {
                    query($conn, "UPDATE produk SET Stok = Stok - $jumlah WHERE ProdukID = $produkId");
                } else {
                    $berhasil = false;
                    break;
                }
            }
            
            if ($berhasil) {
                // Redirect ke preview nota untuk menghindari form resubmission
                header("Location: ../../print/preview_nota.php?id=$penjualanId");
                exit();
            } else {
                $error = 'Gagal menyimpan detail transaksi!';
            }
        } else {
            $error = 'Gagal memproses transaksi!';
        }
    } else {
        $error = 'Keranjang kosong atau total tidak valid!';
    }
}

// Ambil semua produk
$produkList = query($conn, "SELECT * FROM produk WHERE Stok > 0 ORDER BY NamaProduk ASC");

// Cek jika ada member baru ditambahkan
$newMemberCode = '';
$keranjangTemp = '';
$clearForm = isset($_GET['clear']) ? true : false;

if (isset($_SESSION['new_member_code'])) {
    $newMemberCode = $_SESSION['new_member_code'];
    $success = "Member berhasil ditambahkan dengan kode: $newMemberCode";
    unset($_SESSION['new_member_code']);
}

if (isset($_SESSION['keranjang_temp'])) {
    $keranjangTemp = $_SESSION['keranjang_temp'];
    unset($_SESSION['keranjang_temp']);
}

// Sekarang baru load header
$pageTitle = 'Penjualan (POS)';
require_once '../../init.php';
require_once INCLUDES_PATH . 'header.php';
?>

<style>
    .pos-container {
        display: grid;
        grid-template-columns: 2fr 1.3fr;
        gap: 20px;
        height: calc(100vh - 150px);
    }

    .produk-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 15px;
        max-height: calc(100vh - 250px);
        overflow-y: auto;
        padding: 20px;
    }

    .produk-card {
        background: white;
        border: 2px solid var(--border);
        border-radius: 12px;
        padding: 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .produk-card:hover {
        transform: translateY(-5px);
        border-color: #3498db;
        box-shadow: 0 5px 20px rgba(52, 152, 219, 0.2);
    }

    .produk-card.stok-rendah {
        border-color: var(--warning);
        background: #fff9f0;
    }

    .produk-nama {
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--text-dark);
        font-size: 0.95em;
    }

    .produk-harga {
        color: var(--success);
        font-weight: 700;
        font-size: 1.1em;
        margin-bottom: 8px;
    }

    .produk-stok {
        font-size: 0.85em;
        color: var(--text-light);
    }

    .keranjang-section {
        background: white;
        border-radius: 8px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        height: calc(100vh - 150px);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        overflow-y: auto;
    }

    .keranjang-items {
        overflow-y: auto;
        margin: 15px 0;
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
        padding: 10px 0;
        min-height: 150px;
        max-height: 300px;
        flex-shrink: 0;
    }

    .keranjang-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        background: var(--light-bg);
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .keranjang-item-info {
        flex: 1;
    }

    .keranjang-item-nama {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 4px;
    }

    .keranjang-item-detail {
        font-size: 0.85em;
        color: var(--text-light);
    }

    .qty-control {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .qty-btn {
        width: 30px;
        height: 30px;
        border: none;
        background: #3498db;
        color: white;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .qty-btn:hover {
        background: #2980b9;
        transform: scale(1.1);
    }

    .qty-value {
        min-width: 40px;
        text-align: center;
        font-weight: 600;
    }

    .remove-btn {
        background: var(--danger);
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.85em;
        margin-left: 10px;
    }

    .total-section {
        background: #3498db;
        color: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 15px;
    }

    .total-label {
        font-size: 0.9em;
        opacity: 0.9;
        margin-bottom: 5px;
    }

    .total-value {
        font-size: 2em;
        font-weight: 700;
        font-family: 'Crimson Pro', serif;
    }

    .payment-info {
        display: grid;
        gap: 10px;
        margin-bottom: 15px;
    }

    .payment-row {
        display: grid;
        grid-template-columns: 120px 1fr;
        align-items: center;
        gap: 10px;
    }

    .payment-row label {
        font-weight: 600;
        color: var(--text-dark);
    }

    .kembalian-display {
        background: #d4edda;
        color: #155724;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
        font-size: 1.5em;
        font-weight: 700;
        font-family: 'Crimson Pro', serif;
        margin-top: 10px;
    }
</style>

<div class="page-header">
    <h2>Point of Sale</h2>
    <p>Proses transaksi penjualan</p>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="pos-container">
    <!-- Katalog Produk -->
    <div class="card">
        <div class="card-header">
            <h3>Katalog Produk</h3>
        </div>
        <div class="produk-grid">
            <?php while ($produk = fetchArray($produkList)): ?>
                <div class="produk-card <?php echo $produk['Stok'] < 20 ? 'stok-rendah' : ''; ?>" 
                     onclick="tambahKeKeranjang(<?php echo $produk['ProdukID']; ?>, 
                              '<?php echo htmlspecialchars($produk['NamaProduk']); ?>', 
                              <?php echo $produk['Harga']; ?>, 
                              <?php echo $produk['Stok']; ?>)">
                    <div class="produk-nama"><?php echo htmlspecialchars($produk['NamaProduk']); ?></div>
                    <div class="produk-harga">Rp <?php echo number_format($produk['Harga'], 0, ',', '.'); ?></div>
                    <div class="produk-stok">Stok: <?php echo $produk['Stok']; ?> unit</div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Keranjang & Checkout -->
    <div class="keranjang-section">
        <h3 style="font-family: 'Crimson Pro', serif; color: var(--primary); margin-bottom: 15px;">
            Keranjang Belanja
        </h3>

        <div class="form-group">
            <label>Nama Pelanggan</label>
            <input type="text" id="namaPelanggan" class="form-control" placeholder="Masukkan nama pelanggan" 
                   value="Umum" required>
        </div>

        <div class="form-group">
            <label>Kode Member <small>(Opsional - Diskon 10%)</small></label>
            <input type="text" id="kodeMember" class="form-control" placeholder="MBRxxx" 
                   style="text-transform: uppercase;" oninput="cekMember(this.value)" value="<?php echo $newMemberCode; ?>">
            <small style="color: var(--text-light); display: block; margin-top: 5px;">
                Kosongkan jika bukan member
            </small>
            <div id="memberInfo" style="margin-top: 8px; padding: 8px; background: #d4edda; border-radius: 4px; display: none;">
                <strong style="color: #155724;">Member: <span id="memberNama"></span></strong>
            </div>
            <div id="memberError" style="margin-top: 8px; padding: 8px; background: #f8d7da; border-radius: 4px; display: none; color: #721c24;">
                Kode member tidak ditemukan!
            </div>
        </div>

        <button onclick="showFormMember()" class="btn btn-info" style="width: 100%; margin-bottom: 15px;">
            Daftar Member Baru
        </button>

        <div class="keranjang-items" id="keranjangItems">
            <p style="text-align: center; color: var(--text-light); padding: 40px;">
                Keranjang masih kosong<br>Pilih produk untuk memulai transaksi
            </p>
        </div>

        <div class="total-section">
            <div class="total-label">Subtotal</div>
            <div style="font-size: 1.5em; font-weight: 600; margin-bottom: 10px;" id="subtotalDisplay">Rp 0</div>
            <div class="total-label" id="diskonLabel" style="display: none;">Diskon Member (10%)</div>
            <div style="font-size: 1.2em; font-weight: 600; color: #e67e22; margin-bottom: 10px;" id="diskonDisplay">- Rp 0</div>
            <div class="total-label">Grand Total</div>
            <div class="total-value" id="grandTotal">Rp 0</div>
        </div>

        <div class="payment-info">
            <div class="payment-row">
                <label>Uang Bayar:</label>
                <input type="number" id="uangBayar" class="form-control" placeholder="0" 
                       oninput="hitungKembalian()" step="1000">
            </div>
        </div>

        <div class="kembalian-display" id="kembalianDisplay" style="display: none;">
            Kembalian: Rp <span id="kembalianValue">0</span>
        </div>

        <button onclick="prosesTransaksi()" class="btn btn-success" style="width: 100%; padding: 15px; font-size: 1.1em; margin-top: 15px;">
            Proses Pembayaran
        </button>

        <button onclick="resetKeranjang()" class="btn btn-danger" style="width: 100%; padding: 12px; margin-top: 10px;">
            Kosongkan Keranjang
        </button>
    </div>
</div>

<form id="transaksiForm" method="POST" style="display: none;">
    <input type="hidden" name="nama_pelanggan" id="formNamaPelanggan">
    <input type="hidden" name="kode_member" id="formKodeMember">
    <input type="hidden" name="keranjang_data" id="formKeranjang">
    <input type="hidden" name="total_harga" id="formTotal">
    <input type="hidden" name="proses_transaksi" value="1">
</form>

<!-- Modal Form Member -->
<div id="modalMember" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 8px; padding: 30px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <h3 style="margin-bottom: 20px; font-family: 'Crimson Pro', serif;">Daftar Member Baru</h3>
        <form method="POST" action="" id="formMember">
            <input type="hidden" name="keranjang_temp" id="keranjangTempInput">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_member" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Alamat</label>
                <textarea name="alamat_member" class="form-control" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label>Nomor Telepon</label>
                <input type="text" name="notelp_member" class="form-control" required>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" name="tambah_member_pos" class="btn btn-success" style="flex: 1;">Daftar</button>
                <button type="button" onclick="hideFormMember()" class="btn btn-secondary" style="flex: 1;">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
let keranjang = [];

// Clear form jika ada parameter clear
<?php if ($clearForm): ?>
document.addEventListener('DOMContentLoaded', function() {
    // Clear semua input
    document.getElementById('namaPelanggan').value = 'Umum';
    document.getElementById('kodeMember').value = '';
    document.getElementById('uangBayar').value = '';
    document.getElementById('memberInfo').style.display = 'none';
    document.getElementById('memberError').style.display = 'none';
    keranjang = [];
    updateKeranjang();
});
<?php endif; ?>

// Restore keranjang dari session jika ada  
<?php if (!empty($keranjangTemp)): ?>
try {
    keranjang = JSON.parse(<?php echo json_encode($keranjangTemp); ?>);
    updateKeranjang();
} catch(e) {
    console.error('Error parsing keranjang:', e);
}
<?php endif; ?>

// Auto-fill kode member jika baru ditambahkan
<?php if (!empty($newMemberCode)): ?>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        document.getElementById('kodeMember').value = '<?php echo $newMemberCode; ?>';
        cekMember('<?php echo $newMemberCode; ?>');
    }, 300);
});
<?php endif; ?>

function showFormMember() {
    // Simpan keranjang sebelum buka form
    document.getElementById('keranjangTempInput').value = JSON.stringify(keranjang);
    document.getElementById('modalMember').style.display = 'flex';
}

function hideFormMember() {
    document.getElementById('modalMember').style.display = 'none';
}

// Fungsi untuk cek member 
function cekMember(kodeMember) {
    const memberInfo = document.getElementById('memberInfo');
    const memberError = document.getElementById('memberError');
    const memberNama = document.getElementById('memberNama');
    
    if (!kodeMember || kodeMember.trim() === '') {
        memberInfo.style.display = 'none';
        memberError.style.display = 'none';
        updateKeranjang();
        return;
    }
    
    // Cek member
    fetch('../../components/cek_member.php?kode=' + encodeURIComponent(kodeMember))
        .then(response => response.json())
        .then(data => {
            if (data.found) {
                memberNama.textContent = data.nama;
                memberInfo.style.display = 'block';
                memberError.style.display = 'none';
                document.getElementById('namaPelanggan').value = data.nama;
            } else {
                memberInfo.style.display = 'none';
                memberError.style.display = 'block';
            }
            updateKeranjang();
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function tambahKeKeranjang(id, nama, harga, stokMax) {
    const existing = keranjang.find(item => item.id === id);
    
    if (existing) {
        if (existing.qty < stokMax) {
            existing.qty++;
            existing.subtotal = existing.qty * existing.harga;
        } else {
            alert('Stok tidak mencukupi!');
            return;
        }
    } else {
        keranjang.push({
            id: id,
            nama: nama,
            harga: harga,
            qty: 1,
            stokMax: stokMax,
            subtotal: harga
        });
    }
    
    updateKeranjang();
}

function updateQty(id, change) {
    const item = keranjang.find(i => i.id === id);
    if (item) {
        item.qty += change;
        
        if (item.qty > item.stokMax) {
            alert('Stok tidak mencukupi!');
            item.qty = item.stokMax;
        }
        
        if (item.qty <= 0) {
            hapusDariKeranjang(id);
        } else {
            item.subtotal = item.qty * item.harga;
            updateKeranjang();
        }
    }
}

function hapusDariKeranjang(id) {
    keranjang = keranjang.filter(item => item.id !== id);
    updateKeranjang();
}

function updateKeranjang() {
    const container = document.getElementById('keranjangItems');
    
    if (keranjang.length === 0) {
        container.innerHTML = `
            <p style="text-align: center; color: var(--text-light); padding: 40px;">
                Keranjang masih kosong<br>Pilih produk untuk memulai transaksi
            </p>
        `;
    } else {
        container.innerHTML = keranjang.map(item => `
            <div class="keranjang-item">
                <div class="keranjang-item-info">
                    <div class="keranjang-item-nama">${item.nama}</div>
                    <div class="keranjang-item-detail">
                        Rp ${item.harga.toLocaleString('id-ID')} × ${item.qty} = 
                        Rp ${item.subtotal.toLocaleString('id-ID')}
                    </div>
                </div>
                <div class="qty-control">
                    <button class="qty-btn" onclick="updateQty(${item.id}, -1)">-</button>
                    <div class="qty-value">${item.qty}</div>
                    <button class="qty-btn" onclick="updateQty(${item.id}, 1)">+</button>
                    <button class="remove-btn" onclick="hapusDariKeranjang(${item.id})">×</button>
                </div>
            </div>
        `).join('');
    }
    
    const subtotal = keranjang.reduce((sum, item) => sum + item.subtotal, 0);
    const kodeMember = document.getElementById('kodeMember').value.trim();
    const diskon = kodeMember ? subtotal * 0.10 : 0;
    const grandTotal = subtotal - diskon;
    
    // Update tampilan subtotal
    document.getElementById('subtotalDisplay').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
    
    // Cek apakah ada kode member
    if (kodeMember) {
        document.getElementById('diskonLabel').style.display = 'block';
        document.getElementById('diskonDisplay').style.display = 'block';
        document.getElementById('diskonDisplay').textContent = '- Rp ' + diskon.toLocaleString('id-ID');
        document.getElementById('grandTotal').textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
    } else {
        document.getElementById('diskonLabel').style.display = 'none';
        document.getElementById('diskonDisplay').style.display = 'none';
        document.getElementById('grandTotal').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
    }
    
    hitungKembalian();
}

function hitungKembalian() {
    const subtotal = keranjang.reduce((sum, item) => sum + item.subtotal, 0);
    const kodeMember = document.getElementById('kodeMember').value.trim();
    const diskon = kodeMember ? subtotal * 0.10 : 0;
    const total = subtotal - diskon;
    const bayar = parseFloat(document.getElementById('uangBayar').value) || 0;
    const kembalian = bayar - total;
    
    const kembalianDisplay = document.getElementById('kembalianDisplay');
    const kembalianValue = document.getElementById('kembalianValue');
    
    if (bayar > 0 && kembalian >= 0) {
        kembalianDisplay.style.display = 'block';
        kembalianValue.textContent = kembalian.toLocaleString('id-ID');
    } else {
        kembalianDisplay.style.display = 'none';
    }
}

function prosesTransaksi() {
    const namaPelanggan = document.getElementById('namaPelanggan').value.trim();
    const kodeMember = document.getElementById('kodeMember').value.trim();
    const subtotal = keranjang.reduce((sum, item) => sum + item.subtotal, 0);
    const diskon = kodeMember ? subtotal * 0.10 : 0;
    const total = subtotal - diskon;
    const bayar = parseFloat(document.getElementById('uangBayar').value) || 0;
    
    if (!namaPelanggan && !kodeMember) {
        alert('Nama pelanggan atau kode member harus diisi!');
        return;
    }
    
    if (keranjang.length === 0) {
        alert('Keranjang masih kosong!');
        return;
    }
    
    if (bayar < total) {
        alert('Uang bayar kurang!');
        return;
    }
    
    if (confirm('Proses transaksi ini?')) {
        document.getElementById('formNamaPelanggan').value = namaPelanggan;
        document.getElementById('formKodeMember').value = kodeMember;
        document.getElementById('formKeranjang').value = JSON.stringify(keranjang);
        document.getElementById('formTotal').value = subtotal;
        document.getElementById('transaksiForm').submit();
    }
}

function resetKeranjang() {
    if (confirm('Kosongkan keranjang?')) {
        keranjang = [];
        updateKeranjang();
        document.getElementById('uangBayar').value = '';
        document.getElementById('namaPelanggan').value = 'Umum';
        document.getElementById('kodeMember').value = '';
        document.getElementById('memberInfo').style.display = 'none';
        document.getElementById('memberError').style.display = 'none';
    }
}
</script>

</script>

<?php
closeConnection($conn);
require_once INCLUDES_PATH . 'footer.php';
?>