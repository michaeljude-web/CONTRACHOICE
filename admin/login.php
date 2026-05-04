<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/admin/auth.php';

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
    <title>Admin Login — ContraChoice</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=DM+Sans:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-5/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome-7/css/all.min.css">
    <style>
        :root {
            --rose: #C1666B;
            --rose-deep: #9E4A4F;
            --rose-light: #F2D7D8;
            --cream: #FAF7F2;
            --ink: #1C1A18;
            --muted: #7A7068;
            --border: #E8E0D6;
            --white: #ffffff;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            display: flex;
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            overflow: hidden;
        }

        .panel-left {
            width: 45%;
            background: var(--ink);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px 52px;
            overflow: hidden;
        }

        .panel-left::before,
        .panel-left::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(193,102,107,0.18);
        }
        .panel-left::before {
            width: 520px; height: 520px;
            bottom: -180px; right: -180px;
            animation: arcFloat1 14s ease-in-out infinite;
        }
        .panel-left::after {
            width: 320px; height: 320px;
            bottom: -80px; right: -80px;
            border-color: rgba(193,102,107,0.28);
            animation: arcFloat2 11s ease-in-out infinite;
        }

        .ring-extra {
            position: absolute;
            width: 160px; height: 160px;
            border-radius: 50%;
            border: 1px solid rgba(193,102,107,0.35);
            bottom: 40px; right: 20px;
            pointer-events: none;
            animation: ringPulse 4s ease-in-out infinite;
        }

        @keyframes arcFloat1 {
            0%,100% { transform: rotate(0deg) scale(1); }
            50% { transform: rotate(5deg) scale(1.04); }
        }
        @keyframes arcFloat2 {
            0%,100% { transform: rotate(0deg) scale(1); }
            50% { transform: rotate(-4deg) scale(0.96); }
        }
        @keyframes ringPulse {
            0%,100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.06); opacity: 0.85; }
        }

        .dot-grid {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: radial-gradient(rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 28px 28px;
        }

        .brand {
            position: relative;
            z-index: 2;
        }

        .brand-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(193,102,107,0.15);
            border: 1px solid rgba(193,102,107,0.3);
            border-radius: 40px;
            padding: 6px 14px 6px 10px;
            margin-bottom: 28px;
        }

        .brand-pill .dot {
            width: 8px; height: 8px;
            background: var(--rose);
            border-radius: 50%;
            animation: dotPulse 2.2s ease-in-out infinite;
        }
        @keyframes dotPulse {
            0%,100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.75); }
        }

        .brand-pill span {
            color: var(--rose-light);
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .mylogo-wrapper {
            display: inline-block;
            text-align: center;
            line-height: 1;
            font-family: 'Playfair Display', 'Georgia', 'Times New Roman', serif;
        }
        .mylogo-brand {
            font-size: 52px;
            font-weight: 900;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            display: inline-block;
        }
        .mylogo-contra {
            font-style: italic;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #ffffff;
        }
        .mylogo-choice {
            font-weight: 900;
            color: #ba485b;
        }
        .mylogo-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 0.3rem;
        }
        .mylogo-line {
            width: 42px;
            height: 1.5px;
            background: #f4c1cc;
            opacity: 0.9;
        }
        .mylogo-diamond {
            width: 6px;
            height: 6px;
            background: #d36e7e;
            transform: rotate(45deg);
            border-radius: 1px;
        }

        .left-footer {
            position: relative;
            z-index: 2;
        }

        .study-institution {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .inst-bar {
            width: 24px; height: 2px;
            background: var(--rose);
            flex-shrink: 0;
        }

        .inst-name {
            font-size: 12px;
            font-weight: 500;
            color: rgba(255,255,255,0.4);
            letter-spacing: 0.05em;
        }

        .panel-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .login-box {
            width: 100%;
            max-width: 400px;
            animation: fadeUp 0.6s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .login-eyebrow {
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--rose);
            margin-bottom: 10px;
        }

        .login-heading {
            font-family: 'Cormorant Garamond', serif;
            font-size: 38px;
            font-weight: 600;
            color: var(--ink);
            line-height: 1.1;
            margin-bottom: 6px;
        }

        .login-sub {
            font-size: 13.5px;
            color: var(--muted);
            margin-bottom: 36px;
            line-height: 1.6;
        }

        .error-box {
            background: #FDF0F0;
            border: 1px solid #F0CECE;
            border-left: 3px solid var(--rose);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            color: var(--rose-deep);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: fadeIn 0.4s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .field-group {
            margin-bottom: 16px;
        }

        .field-label {
            font-size: 12px;
            font-weight: 500;
            color: var(--ink);
            letter-spacing: 0.04em;
            margin-bottom: 7px;
            display: block;
        }

        .field-wrap {
            position: relative;
        }

        .field-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 14px;
            pointer-events: none;
        }

        .field-input {
            width: 100%;
            padding: 13px 16px 13px 42px;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            color: var(--ink);
            background: var(--white);
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        .field-input::placeholder {
            color: #B8B0A8;
        }

        .field-input:focus {
            border-color: var(--rose);
            box-shadow: 0 0 0 3px rgba(193,102,107,0.12);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--ink);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            letter-spacing: 0.02em;
        }

        .btn-login:hover {
            background: var(--rose-deep);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(158,74,79,0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        @media (max-width: 1024px) {
            .panel-left { width: 42%; padding: 40px 36px; }
            .mylogo-brand { font-size: 42px; }
            .mylogo-line { width: 34px; }
            .panel-left::before { width: 400px; height: 400px; bottom: -140px; right: -140px; }
            .panel-left::after { width: 250px; height: 250px; bottom: -60px; right: -60px; }
            .ring-extra { width: 120px; height: 120px; bottom: 30px; right: 15px; }
        }
        @media (max-width: 768px) {
            body { flex-direction: column; overflow: auto; }
            .panel-left {
                width: 100%;
                padding: 36px 28px 32px;
                min-height: auto;
            }
            .mylogo-brand { font-size: 38px; }
            .mylogo-line { width: 32px; }
            .left-footer { margin-top: 24px; }
            .panel-right { padding: 32px 24px; }
        }
    </style>
</head>
<body>

<div class="panel-left">
    <div class="dot-grid"></div>
    <div class="ring-extra"></div>

    <div class="brand">
        <div class="brand-pill">
            <div class="dot"></div>
            <span>Admin Portal</span>
        </div>
        <div class="mylogo-wrapper">
            <div class="mylogo-brand">
                <span class="mylogo-contra">Contra</span><span class="mylogo-choice">Choice</span>
            </div>
            <div class="mylogo-divider">
                <span class="mylogo-line"></span>
                <span class="mylogo-diamond"></span>
                <span class="mylogo-line"></span>
            </div>
        </div>
    </div>

</div>

<div class="panel-right">
    <div class="login-box">

        <div class="login-heading">Welcome back,<br>Administrator</div>
        <div class="login-sub">Sign in to manage contraceptive methods and system content.</div>

        <?php if ($error): ?>
        <div class="error-box">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="field-group">
                <label class="field-label">Username</label>
                <div class="field-wrap">
                    <i class="fas fa-user field-icon"></i>
                    <input type="text" name="username" class="field-input" placeholder="Enter your username" required autofocus>
                </div>
            </div>

            <div class="field-group">
                <label class="field-label">Password</label>
                <div class="field-wrap">
                    <i class="fas fa-lock field-icon"></i>
                    <input type="password" name="password" class="field-input" placeholder="Enter your password" required>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-arrow-right-to-bracket"></i> Sign In
            </button>
        </form>

    </div>
</div>

</body>
</html>