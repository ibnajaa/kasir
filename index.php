<?php
require_once 'init.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    redirect('pages/shared/dashboard.php');
    exit();
}

$error = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($username, $password)) {
        redirect('pages/shared/dashboard.php');
        exit();
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Kasir</title>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@400;600;700&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2d3436;
            --secondary: #00b894;
            --accent: #fdcb6e;
            --dark: #1a1a1a;
            --light: #f8f9fa;
            --danger: #ff6b6b;
        }

        body {
            font-family: 'Work Sans', sans-serif;
            background: #3498db;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-side {
            background: #34495e;
            padding: 60px 40px;
            color: white;
            position: relative;
        }

        .login-side-content {
            position: relative;
            z-index: 1;
        }

        .login-side h1 {
            font-family: 'Crimson Pro', serif;
            font-size: 3em;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.1;
        }

        .login-side p {
            font-size: 1.1em;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .features {
            display: grid;
            gap: 15px;
            margin-top: 40px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }

        .feature-item:hover {
            transform: translateX(5px);
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2em;
            flex-shrink: 0;
        }

        .login-form-side {
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            margin-bottom: 40px;
        }

        .form-header h2 {
            font-family: 'Crimson Pro', serif;
            font-size: 2.2em;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .form-header p {
            color: #666;
            font-size: 1em;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark);
            font-weight: 500;
            font-size: 0.95em;
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1em;
            font-family: 'Work Sans', sans-serif;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            font-family: 'Work Sans', sans-serif;
        }

        .btn-login:hover {
            background: #229954;
        }

        .error-message {
            background: #ffe0e0;
            color: var(--danger);
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid var(--danger);
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .default-credentials {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid var(--accent);
        }

        .default-credentials h4 {
            color: var(--dark);
            margin-bottom: 10px;
            font-size: 0.95em;
        }

        .default-credentials code {
            background: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
            color: #3498db;
            border: 1px solid #e0e0e0;
        }

        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
            }

            .login-side {
                padding: 40px 30px;
            }

            .login-side h1 {
                font-size: 2em;
            }

            .features {
                display: none;
            }

            .login-form-side {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-side">
            <div class="login-side-content">
                <h1>Sistem Kasir Modern</h1>
                <p>Kelola transaksi, stok, dan laporan penjualan dengan mudah dan efisien</p>
                
                <div class="features">
                    <div class="feature-item">
                        <div>
                            <strong>Dashboard Interaktif</strong>
                            <p style="font-size: 0.9em; opacity: 0.8; margin-top: 5px;">Pantau bisnis secara real-time</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div>
                            <strong>Transaksi Cepat</strong>
                            <p style="font-size: 0.9em; opacity: 0.8; margin-top: 5px;">Proses penjualan dalam hitungan detik</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div>
                            <strong>Manajemen Stok</strong>
                            <p style="font-size: 0.9em; opacity: 0.8; margin-top: 5px;">Kontrol inventaris dengan mudah</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="login-form-side">
            <div class="form-header">
                <h2>Selamat Datang</h2>
                <p>Silakan login untuk melanjutkan</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn-login">Login</button>
            </form>

            <div class="default-credentials">
                <h4>Akun Default:</h4>
                <p style="margin-top: 10px; color: #666; font-size: 0.9em;">
                    <strong>Admin:</strong> <code>admin</code> / <code>admin123</code>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
