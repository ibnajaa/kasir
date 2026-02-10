<?php
require_once '../init.php';

header('Content-Type: application/json');

$conn = getConnection();
$kode = escapeString($conn, $_GET['kode'] ?? '');

if (empty($kode)) {
    echo json_encode(['found' => false]);
    exit();
}

$result = query($conn, "SELECT NamaPelanggan FROM pelanggan WHERE KodeMember = '$kode'");

if (numRows($result) > 0) {
    $member = fetchArray($result);
    echo json_encode([
        'found' => true,
        'nama' => $member['NamaPelanggan']
    ]);
} else {
    echo json_encode(['found' => false]);
}

closeConnection($conn);
?>
