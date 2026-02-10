<?php
// Auth.php - loaded by init.php
// Don't require init.php here to avoid circular dependency

// Fungsi untuk login
function login($username, $password) {
    $conn = getConnection();
    $username = escapeString($conn, $username);
    $password = md5($password);
    
    $sql = "SELECT * FROM user WHERE Username = '$username' AND Password = '$password'";
    $result = query($conn, $sql);
    
    if (numRows($result) > 0) {
        $user = fetchArray($result);
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['username'] = $user['Username'];
        $_SESSION['role'] = $user['Role'];
        $_SESSION['logged_in'] = true;
        closeConnection($conn);
        return true;
    }
    
    closeConnection($conn);
    return false;
}

// Fungsi untuk logout
function logout() {
    session_destroy();
    header('Location: index.php');
    exit();
}

// Fungsi untuk cek login
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Fungsi untuk cek role admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Fungsi untuk cek role petugas
function isPetugas() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'petugas';
}

// Fungsi untuk redirect jika belum login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

// Fungsi untuk redirect jika bukan admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit();
    }
}

// Fungsi untuk get user info
function getUserInfo() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role']
        ];
    }
    return null;
}
?>
