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
    <title>Login | ContraChoice</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-5/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome-7/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #faf0f5;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: stretch;
        }

        .login-card {
            display: flex;
            width: 100vw;
            min-height: 100vh;
            overflow: hidden;
            box-shadow: none;
            border-radius: 0;
        }

        .illus-side {
            flex: 1;
            background: linear-gradient(145deg, #fbe9f2 0%, #ffe0ec 50%, #f9d9e8 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 2rem;
            position: relative;
            overflow: hidden;
        }

        .illus-side::before {
            content: '';
            position: absolute;
            width: 220px;
            height: 220px;
            background: rgba(255, 200, 220, 0.3);
            border-radius: 50%;
            top: -60px;
            right: -60px;
        }

        .illus-side::after {
            content: '';
            position: absolute;
            width: 150px;
            height: 150px;
            background: rgba(255, 180, 210, 0.25);
            border-radius: 50%;
            bottom: -40px;
            left: -40px;
        }

        .illus-badge {
            background: rgba(255, 240, 245, 0.7);
            border: 1.5px solid rgba(255, 140, 170, 0.3);
            color: #c96a8d;
            font-size: 10.5px;
            font-weight: 600;
            padding: 5px 14px;
            border-radius: 20px;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            margin-bottom: 1.2rem;
            z-index: 1;
        }

        .illus-svg {
            z-index: 1;
            margin-bottom: 1.2rem;
            filter: drop-shadow(0 8px 16px rgba(200, 100, 130, 0.15));
        }

        .illus-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #b85c7e;
            text-align: center;
            margin-bottom: 0.4rem;
            z-index: 1;
        }

        .illus-sub {
            font-size: 0.75rem;
            color: #c97a99;
            text-align: center;
            line-height: 1.6;
            max-width: 200px;
            z-index: 1;
        }

        .pill-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: center;
            margin-top: 1.2rem;
            z-index: 1;
        }

        .pill-tag {
            background: rgba(255, 240, 245, 0.7);
            border: 1px solid rgba(255, 140, 170, 0.35);
            color: #b85c7e;
            font-size: 10px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
        }

        .form-side {
            flex: 1;
            background: #fffdf8;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem 2.5rem;
        }

        .form-greeting {
            font-size: 0.78rem;
            font-weight: 600;
            color: #e0a3bb;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 0.3rem;
        }

        .form-title {
            font-size: 2rem;
            font-weight: 700;
            color: #c46b8d;
            margin-bottom: 0.2rem;
            line-height: 1.2;
        }

        .form-sub {
            font-size: 0.78rem;
            color: #da9bb5;
            margin-bottom: 1.8rem;
        }

        .alert-cute {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 12px;
            margin-bottom: 1rem;
            font-size: 0.78rem;
            font-weight: 500;
        }

        .alert-cute-danger {
            background: #ffe8f0;
            border: 1.5px solid #fcc4d6;
            color: #bf6b89;
        }

        .alert-cute-success {
            background: #e6f5ed;
            border: 1.5px solid #b8dfcb;
            color: #4a7a62;
        }

        .alert-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 12px;
        }

        .alert-cute-danger .alert-icon {
            background: #fdd8e4;
            color: #d47a9a;
        }

        .alert-cute-success .alert-icon {
            background: #c6ecdb;
            color: #3a7a5e;
        }

        .input-wrap {
            position: relative;
            margin-bottom: 1rem;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #dba3bc;
            font-size: 14px;
            z-index: 2;
        }

        .cute-input {
            width: 100%;
            background: #fff7fa;
            border: 1.5px solid #f5cedf;
            border-radius: 12px;
            padding: 12px 42px 12px 42px;
            font-size: 0.875rem;
            color: #8b5570;
            font-family: 'Poppins', sans-serif;
            transition: border 0.2s, background 0.2s;
            outline: none;
        }

        .cute-input:focus {
            border-color: #e2a1be;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(220, 130, 160, 0.12);
        }

        .cute-input::placeholder {
            color: #e0b9cd;
        }

        .toggle-pass-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #dba3bc;
            cursor: pointer;
            font-size: 13px;
            padding: 0;
            z-index: 2;
        }

        .toggle-pass-btn:hover { color: #bc7f9b; }

        .btn-cute-login {
            width: 100%;
            background: linear-gradient(135deg, #e7b0c8 0%, #d492b0 100%);
            border: none;
            border-radius: 12px;
            padding: 13px;
            color: #fff;
            font-size: 0.9rem;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.25s;
            margin-top: 0.3rem;
            letter-spacing: 0.3px;
        }

        .btn-cute-login:hover {
            background: linear-gradient(135deg, #da9bb8 0%, #c97f9f 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(200, 100, 130, 0.35);
        }

        .btn-cute-login:active {
            transform: translateY(0);
        }

        .signup-txt {
            text-align: center;
            font-size: 0.78rem;
            color: #da9bb5;
            margin-top: 1.2rem;
            margin-bottom: 0;
        }

        .signup-txt a {
            color: #c46b8d;
            font-weight: 600;
            text-decoration: none;
        }

        .signup-txt a:hover { text-decoration: underline; }

        .modal-content {
            border: none;
            border-radius: 22px;
            background: #fffdf8;
        }

        .modal-header {
            border-bottom: 1px solid #f5d8e6;
            padding: 1.25rem 1.5rem 1rem;
        }

        .modal-title-cute {
            font-size: 1.2rem;
            font-weight: 700;
            color: #c46b8d;
        }

        .modal-body { padding: 1.5rem; }

        .btn-close:focus { box-shadow: none; }

        .btn-cute-register {
            width: 100%;
            background: linear-gradient(135deg, #e7b0c8 0%, #d492b0 100%);
            border: none;
            border-radius: 12px;
            padding: 13px;
            color: #fff;
            font-size: 0.9rem;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.25s;
            letter-spacing: 0.3px;
        }

        .btn-cute-register:hover {
            background: linear-gradient(135deg, #da9bb8 0%, #c97f9f 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(200, 100, 130, 0.35);
        }

        @media (max-width: 768px) {
            .illus-side { display: none; }
            .form-side { padding: 2.5rem 1.5rem; }
            .login-card { width: 100vw; }
            .form-title { font-size: 1.7rem; }
        }
    </style>
</head>
<body>

<div class="login-card">

    <div class="illus-side d-none d-md-flex flex-column align-items-center justify-content-center">

        <span class="illus-badge">Reproductive Health</span>

        <svg class="illus-svg" width="210" height="195" viewBox="0 0 210 195" xmlns="http://www.w3.org/2000/svg">
            <circle cx="105" cy="97" r="82" fill="rgba(255, 180, 200, 0.15)"/>

            <path d="M78,122 C64,116 58,99 63,85 C68,72 79,67 90,70 L90,78 C82,76 73,80 70,90 C67,101 72,114 83,118 Z" fill="#e89fc0" opacity="0.85"/>
            <path d="M132,122 C146,116 152,99 147,85 C142,72 131,67 120,70 L120,78 C128,76 137,80 140,90 C143,101 138,114 127,118 Z" fill="#e89fc0" opacity="0.85"/>
            <path d="M90,70 C90,61 96,56 105,56 C114,56 120,61 120,70 L120,122 C120,128 114,132 105,132 C96,132 90,128 90,122 Z" fill="#e89fc0" opacity="0.9"/>

            <path d="M96,70 C96,64 100,60 105,60 C108,60 111,62 112,65 L112,80 C111,78 108,77 105,77 C102,77 99,78 98,80 Z" fill="rgba(255,255,255,0.35)"/>

            <ellipse cx="66" cy="80" rx="14" ry="10" fill="#ffe4f0" stroke="#e89fc0" stroke-width="2"/>
            <ellipse cx="144" cy="80" rx="14" ry="10" fill="#ffe4f0" stroke="#e89fc0" stroke-width="2"/>

            <ellipse cx="63" cy="77" rx="5" ry="3.5" fill="rgba(255,255,255,0.5)" transform="rotate(-20,63,77)"/>
            <ellipse cx="141" cy="77" rx="5" ry="3.5" fill="rgba(255,255,255,0.5)" transform="rotate(20,141,77)"/>

            <path d="M90,70 Q78,67 66,80" fill="none" stroke="#e89fc0" stroke-width="2.5" stroke-linecap="round"/>
            <path d="M120,70 Q132,67 144,80" fill="none" stroke="#e89fc0" stroke-width="2.5" stroke-linecap="round"/>

            <rect x="34" y="40" width="28" height="15" rx="7.5" fill="#c47a9e"/>
            <line x1="48" y1="40" x2="48" y2="55" stroke="rgba(255,255,255,0.6)" stroke-width="1.5"/>
            <rect x="34" y="40" width="14" height="15" rx="7.5" fill="#e89fc0"/>

            <rect x="148" y="47" width="28" height="15" rx="7.5" fill="#b3d9c0"/>
            <line x1="162" y1="47" x2="162" y2="62" stroke="rgba(255,255,255,0.6)" stroke-width="1.5"/>
            <rect x="148" y="47" width="14" height="15" rx="7.5" fill="#d0f0e0"/>

            <line x1="35" y1="140" x2="58" y2="140" stroke="#c47a9e" stroke-width="2.5" stroke-linecap="round"/>
            <line x1="46.5" y1="140" x2="46.5" y2="155" stroke="#c47a9e" stroke-width="2.5" stroke-linecap="round"/>
            <circle cx="35" cy="140" r="3" fill="#ffe4f0" stroke="#c47a9e" stroke-width="1.5"/>
            <circle cx="58" cy="140" r="3" fill="#ffe4f0" stroke="#c47a9e" stroke-width="1.5"/>

            <path d="M163,115 Q162,104 168,100 Q174,97 175,107 L175,122 Q175,128 168.5,128 Q162,128 162,122 Z" fill="rgba(240, 190, 210, 0.6)" stroke="#dba3bc" stroke-width="1.8"/>
            <line x1="162" y1="122" x2="175" y2="122" stroke="#dba3bc" stroke-width="1.8"/>
            <ellipse cx="168.5" cy="122" rx="6.5" ry="2" fill="#ffe4f0" opacity="0.6"/>
        </svg>

        <p class="illus-title">Contra Choice</p>
        <p class="illus-sub">Track your contraceptive methods safely and privately</p>

        <div class="pill-tags">
            <span class="pill-tag">Pills</span>
            <span class="pill-tag">IUD</span>
            <span class="pill-tag">Condom</span>
            <span class="pill-tag">Implant</span>
            <span class="pill-tag">Injection</span>
        </div>

    </div>

    <div class="form-side">

        <p class="form-greeting">Hello there!</p>
        <h1 class="form-title">Welcome back</h1>
        <p class="form-sub">Log in to your ContraChoice account</p>

        <?php if ($login_error): ?>
        <div class="alert-cute alert-cute-danger">
            <span class="alert-icon"><i class="fa-solid fa-heart-broken"></i></span>
            <span><?= htmlspecialchars($login_error) ?></span>
        </div>
        <?php endif; ?>

        <?php if ($register_success): ?>
        <div class="alert-cute alert-cute-success">
            <span class="alert-icon"><i class="fa-solid fa-check"></i></span>
            <span><?= htmlspecialchars($register_success) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-wrap">
                <i class="fa-solid fa-user input-icon"></i>
                <input type="text" name="username" class="cute-input" placeholder="Username" required>
            </div>
            <div class="input-wrap">
                <i class="fa-solid fa-lock input-icon"></i>
                <input type="password" name="password" id="login-pass" class="cute-input" placeholder="Password" required>
                <button type="button" class="toggle-pass-btn" onclick="togglePass('login-pass', this)">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
            <button type="submit" name="login" class="btn-cute-login">Login</button>
        </form>

        <p class="signup-txt">
            Don't have an account?
            <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal">Sign up</a>
        </p>

    </div>
</div>

<div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title-cute">Create an Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <?php if ($register_error): ?>
                <div class="alert-cute alert-cute-danger">
                    <span class="alert-icon"><i class="fa-solid fa-exclamation"></i></span>
                    <span><?= htmlspecialchars($register_error) ?></span>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="input-wrap">
                        <i class="fa-solid fa-user input-icon"></i>
                        <input type="text" name="username" class="cute-input" placeholder="Choose a username" required>
                    </div>
                    <div class="input-wrap" style="margin-bottom: 1.4rem;">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" name="password" id="reg-pass" class="cute-input" placeholder="Password (min 4 chars)" required>
                        <button type="button" class="toggle-pass-btn" onclick="togglePass('reg-pass', this)">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <button type="submit" name="register" class="btn-cute-register">Create Account</button>
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