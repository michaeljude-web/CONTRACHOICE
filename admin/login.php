<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/admin/auth.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                adminLogin($admin);
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid password.';
            }
        } else {
            $error = 'Admin username not found.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ContraChoice</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400&family=Outfit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-5/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome-7/css/all.min.css">
    <style>
        :root {
            --bg-dirty: #f8f6f0;
            --surface: #ffffff;
            --border-soft: #e8e4dc;
            --blue-600: #185FA5;
            --blue-800: #0C447C;
        }
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            padding: 20px;
        }
        .admin-login-card {
            background: var(--surface);
            border-radius: 28px;
            padding: 40px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            border: 1px solid var(--border-soft);
        }
        .admin-icon {
            background: var(--blue-600);
            width: 64px;
            height: 64px;
            border-radius: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 28px;
        }
        h2 {
            font-family: 'Playfair Display', serif;
            text-align: center;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .subtitle {
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 28px;
        }
        .form-control {
            border-radius: 30px;
            padding: 12px 20px;
            border: 1px solid var(--border-soft);
            font-family: 'Outfit', sans-serif;
        }
        .form-control:focus {
            border-color: var(--blue-600);
            box-shadow: 0 0 0 0.2rem rgba(24,95,165,0.25);
        }
        .btn-admin {
            background: var(--blue-600);
            border: none;
            border-radius: 30px;
            padding: 12px;
            width: 100%;
            color: white;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn-admin:hover {
            background: var(--blue-800);
        }
        .alert-custom {
            background: #fcebeb;
            border: 1px solid #f0c0c0;
            border-radius: 20px;
            padding: 12px 18px;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: var(--blue-600);
            text-decoration: none;
            font-size: 13px;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="admin-login-card">
    <div class="admin-icon">
        <i class="fas fa-user-shield"></i>
    </div>
    <h2>Admin Portal</h2>
    <div class="subtitle">Sign in to manage contraceptive methods and content</div>

    <?php if ($error): ?>
        <div class="alert-custom">
            <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <input type="text" name="username" class="form-control" placeholder="Username" required autofocus>
        </div>
        <div class="mb-4">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <button type="submit" class="btn-admin">
            <i class="fas fa-sign-in-alt me-2"></i> Login
        </button>
    </form>

</div>
</body>
</html>