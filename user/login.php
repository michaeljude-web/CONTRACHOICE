<?php
session_start();
include '../includes/db_connection.php';

$login_error = '';
$register_error = '';
$register_success = '';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            $login_error = "Incorrect password.";
        }
    } else {
        $login_error = "User not found.";
    }
    $stmt->close();
}

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $register_error = "All fields are required.";
    } elseif (strlen($password) < 3) {
        $register_error = "Password must be at least 4 characters.";
    } else {
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $register_error = "Username already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $insert_stmt->bind_param("ss", $username, $hashed_password);
            if ($insert_stmt->execute()) {
                $register_success = "Account created successfully! You can now log in.";
                $register_error = '';
            } else {
                $register_error = "Error: " . $conn->error;
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — ContraChoice</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=DM+Sans:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-5/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome-7/css/all.min.css">
    <style>
        :root {
            --rose:       #C1666B;
            --rose-deep:  #9E4A4F;
            --rose-soft:  #E8A4A8;
            --rose-pale:  #F5DDE0;
            --cream:      #FAF7F2;
            --ink:        #1C1A18;
            --muted:      #7A7068;
            --border:     #EDE5E0;
            --white:      #ffffff;
            --sage:       #7A9E8E;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            min-height: 100vh;
            display: flex;
        }

        .illus-panel {
            width: 46%;
            background: var(--ink);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 52px 48px;
            overflow: hidden;
        }

        .dot-bg {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255,255,255,0.035) 1px, transparent 1px);
            background-size: 26px 26px;
        }

        .arc {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(193,102,107,0.15);
        }
        .arc-1 {
            width: 480px; height: 480px;
            bottom: -160px; right: -160px;
            animation: arcFloat1 12s ease-in-out infinite;
        }
        .arc-2 {
            width: 280px; height: 280px;
            bottom: -60px; right: -60px;
            border-color: rgba(193,102,107,0.25);
            animation: arcFloat2 10s ease-in-out infinite;
        }
        .arc-3 {
            width: 120px; height: 120px;
            bottom: 30px; right: 10px;
            border-color: rgba(193,102,107,0.4);
            animation: arcPulse 3s ease-in-out infinite;
        }

        @keyframes arcFloat1 {
            0%,100% { transform: rotate(0deg) scale(1); }
            50% { transform: rotate(6deg) scale(1.03); }
        }
        @keyframes arcFloat2 {
            0%,100% { transform: rotate(0deg) scale(1); }
            50% { transform: rotate(-5deg) scale(0.98); }
        }
        @keyframes arcPulse {
            0%,100% { transform: scale(1); opacity: 0.6; }
            50% { transform: scale(1.08); opacity: 1; }
        }

        .brand-area { position: relative; z-index: 2; }

        .brand-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(193,102,107,0.12);
            border: 1px solid rgba(193,102,107,0.28);
            border-radius: 40px;
            padding: 5px 13px 5px 9px;
            margin-bottom: 24px;
        }
        .brand-pill .pip {
            width: 7px; height: 7px;
            background: var(--rose);
            border-radius: 50%;
            animation: pulse 2.4s ease-in-out infinite;
        }
        @keyframes pulse {
            0%,100% { opacity: 1; transform: scale(1); }
            50%      { opacity: .55; transform: scale(0.8); }
        }
        .brand-pill span {
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--rose-pale);
        }

        .mylogo-wrapper {
            display: inline-block;
            text-align: center;
            line-height: 1;
            font-family: 'Playfair Display', 'Georgia', 'Times New Roman', serif;
        }

        .mylogo-brand {
            font-size: 58px;
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
            gap: 12px;
            margin-top: 0.4rem;
        }

        .mylogo-line {
            width: 48px;
            height: 1.5px;
            background: #f4c1cc;
            opacity: 0.9;
        }

        .mylogo-diamond {
            width: 7px;
            height: 7px;
            background: #d36e7e;
            transform: rotate(45deg);
            border-radius: 1px;
        }

        .panel-bottom { position: relative; z-index: 2; }

        .inst-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .inst-dash {
            width: 22px; height: 2px;
            background: var(--rose);
            flex-shrink: 0;
        }
        .inst-name {
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.06em;
            color: rgba(255,255,255,0.35);
        }

        .form-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
        }

        .form-box {
            width: 100%;
            max-width: 400px;
            animation: slideUp 0.55s cubic-bezier(.22,.68,0,1.2) both;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .form-eyebrow {
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--rose);
            margin-bottom: 8px;
        }

        .form-heading {
            font-family: 'Cormorant Garamond', serif;
            font-size: 42px;
            font-weight: 600;
            color: var(--ink);
            line-height: 1.05;
            margin-bottom: 6px;
        }

        .form-sub {
            font-size: 13.5px;
            color: var(--muted);
            margin-bottom: 32px;
            line-height: 1.6;
        }

        .cc-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 15px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            animation: fadeIn 0.4s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .cc-alert-error {
            background: #FDF0F1;
            border: 1px solid #F5D0D2;
            border-left: 3px solid var(--rose);
            color: var(--rose-deep);
        }
        .cc-alert-success {
            background: #EEF6F3;
            border: 1px solid #BFD9CF;
            border-left: 3px solid var(--sage);
            color: #3E7A65;
        }
        .cc-alert i { margin-top: 1px; flex-shrink: 0; }

        .field-group { margin-bottom: 14px; }

        .field-label {
            display: block;
            font-size: 11.5px;
            font-weight: 500;
            letter-spacing: 0.04em;
            color: var(--ink);
            margin-bottom: 6px;
        }

        .field-wrap { position: relative; }

        .field-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 13px;
            color: var(--rose-soft);
            pointer-events: none;
        }

        .field-input {
            width: 100%;
            padding: 13px 44px 13px 42px;
            border: 1.5px solid var(--border);
            border-radius: 14px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            color: var(--ink);
            background: var(--white);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .field-input::placeholder { color: #C8C0BC; }
        .field-input:focus {
            border-color: var(--rose);
            box-shadow: 0 0 0 3px rgba(193,102,107,0.1);
        }

        .toggle-btn {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: var(--rose-soft);
            font-size: 13px;
            cursor: pointer;
            padding: 4px;
            transition: color 0.2s;
        }
        .toggle-btn:hover { color: var(--rose); }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--ink);
            color: #fff;
            border: none;
            border-radius: 14px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            letter-spacing: 0.02em;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
        }
        .btn-submit:hover {
            background: var(--rose-deep);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(158,74,79,0.3);
        }
        .btn-submit:active { transform: translateY(0); }

        .row-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 22px 0;
        }
        .row-divider hr {
            flex: 1; border: none;
            border-top: 1px solid var(--border);
        }
        .row-divider span { font-size: 11px; color: var(--muted); }

        .signup-row {
            text-align: center;
            font-size: 13px;
            color: var(--muted);
        }
        .signup-row a {
            color: var(--rose);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }
        .signup-row a:hover { color: var(--rose-deep); text-decoration: underline; }

        .modal-content {
            border: none;
            border-radius: 24px;
            background: var(--cream);
            overflow: hidden;
        }

        .modal-header {
            background: var(--ink);
            border: none;
            padding: 24px 28px 20px;
        }

        .modal-header-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 26px;
            font-weight: 600;
            color: #fff;
        }

        .modal-header-sub {
            font-size: 12px;
            color: rgba(255,255,255,0.45);
            margin-top: 2px;
        }

        .btn-close-custom {
            background: rgba(255,255,255,0.1);
            border: none;
            border-radius: 50%;
            width: 32px; height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,0.6);
            cursor: pointer;
            font-size: 13px;
            transition: background 0.2s;
        }
        .btn-close-custom:hover { background: rgba(255,255,255,0.2); color: #fff; }

        .modal-body { padding: 28px; }

        @media (max-width: 1024px) {
            .illus-panel {
                width: 42%;
                padding: 40px 32px;
            }
            .mylogo-brand {
                font-size: 44px;
            }
            .mylogo-line {
                width: 36px;
            }
            .arc-1 { width: 360px; height: 360px; bottom: -120px; right: -120px; }
            .arc-2 { width: 210px; height: 210px; bottom: -50px; right: -50px; }
            .arc-3 { width: 90px; height: 90px; bottom: 20px; right: 10px; }
        }
        @media (max-width: 820px) {
            .illus-panel { display: none; }
            .form-panel { padding: 36px 24px; }
            .form-heading { font-size: 36px; }
        }
        @media (max-width: 480px) {
            .form-box { max-width: 100%; }
            .form-heading { font-size: 32px; }
        }
    </style>
</head>
<body>

<div class="illus-panel d-none d-lg-flex flex-column">
    <div class="dot-bg"></div>
    <div class="arc arc-1"></div>
    <div class="arc arc-2"></div>
    <div class="arc arc-3"></div>

    <div class="brand-area">
        <div class="brand-pill">
            <div class="pip"></div>
            <span>Women's Health</span>
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

<div class="form-panel">
    <div class="form-box">

        <div class="form-eyebrow">User Portal</div>
        <div class="form-heading">Welcome<br>back.</div>
        <div class="form-sub">Sign in to your ContraChoice account to continue.</div>

        <?php if ($login_error): ?>
        <div class="cc-alert cc-alert-error">
            <i class="fas fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($login_error) ?></span>
        </div>
        <?php endif; ?>

        <?php if ($register_success): ?>
        <div class="cc-alert cc-alert-success">
            <i class="fas fa-circle-check"></i>
            <span><?= htmlspecialchars($register_success) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="field-group">
                <label class="field-label">Username</label>
                <div class="field-wrap">
                    <i class="fas fa-user field-icon"></i>
                    <input type="text" name="username" class="field-input" placeholder="Your username" required>
                </div>
            </div>
            <div class="field-group">
                <label class="field-label">Password</label>
                <div class="field-wrap">
                    <i class="fas fa-lock field-icon"></i>
                    <input type="password" name="password" id="login-pass" class="field-input" placeholder="Your password" required>
                    <button type="button" class="toggle-btn" onclick="togglePass('login-pass', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" name="login" class="btn-submit">
                <i class="fas fa-arrow-right-to-bracket"></i> Sign In
            </button>
        </form>

        <div class="row-divider">
            <hr><span>New here?</span><hr>
        </div>

        <div class="signup-row">
            Don't have an account?
            <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal">Create one</a>
        </div>

    </div>
</div>

<div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header d-flex align-items-start justify-content-between">
                <div>
                    <div class="modal-header-title">Create Account</div>
                    <div class="modal-header-sub">Join ContraChoice today</div>
                </div>
                <button type="button" class="btn-close-custom" data-bs-dismiss="modal">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body">

                <?php if ($register_error): ?>
                <div class="cc-alert cc-alert-error">
                    <i class="fas fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($register_error) ?></span>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="field-group">
                        <label class="field-label">Choose a Username</label>
                        <div class="field-wrap">
                            <i class="fas fa-user field-icon"></i>
                            <input type="text" name="username" class="field-input" placeholder="Pick a unique username" required>
                        </div>
                    </div>
                    <div class="field-group" style="margin-bottom: 20px;">
                        <label class="field-label">Password</label>
                        <div class="field-wrap">
                            <i class="fas fa-lock field-icon"></i>
                            <input type="password" name="password" id="reg-pass" class="field-input" placeholder="Minimum 4 characters" required>
                            <button type="button" class="toggle-btn" onclick="togglePass('reg-pass', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" name="register" class="btn-submit">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>

<script src="../assets/vendor/bootstrap-5/js/bootstrap.bundle.min.js"></script>
<script>
    function togglePass(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon  = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    <?php if ($register_error): ?>
    const registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
    registerModal.show();
    <?php endif; ?>
</script>

</body>
</html>