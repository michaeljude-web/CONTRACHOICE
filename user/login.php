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
    <title>Login</title>
    <link rel="stylesheet" href="../assets/vendor/bootstrap-5/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome-7/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/user/style.css">
</head>
<body class="bg-light min-vh-100 d-flex align-items-center justify-content-center">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-9 col-md-6 col-lg-4">

            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4 p-md-5">

                    <div class="text-center mb-4">
                        <i class="fa-solid fa-shield-halved fa-2x text-primary mb-2"></i>
                        <h4 class="fw-bold mb-0">Welcome Back</h4>
                        <p class="text-muted small">Sign in to your account</p>
                    </div>

                    <?php if ($login_error): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 py-2 small">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <?= htmlspecialchars($login_error) ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($register_success): ?>
                    <div class="alert alert-success d-flex align-items-center gap-2 py-2 small">
                        <i class="fa-solid fa-circle-check"></i>
                        <?= htmlspecialchars($register_success) ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-user text-muted"></i></span>
                                <input type="text" name="username" class="form-control"
                                       placeholder="Enter your username" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-medium">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock text-muted"></i></span>
                                <input type="password" name="password" id="login-pass" class="form-control"
                                       placeholder="Enter your password" required>
                                <button type="button" class="btn btn-outline-secondary"
                                        onclick="togglePass('login-pass', this)">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="login" class="btn btn-primary">
                                <i class="fa-solid fa-right-to-bracket me-1"></i> Login
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <p class="text-center text-muted small mb-0">
                        Don't have an account?
                        <a href="#" class="text-primary fw-medium text-decoration-none"
                           data-bs-toggle="modal" data-bs-target="#registerModal">
                            Sign up
                        </a>
                    </p>

                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">

            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold" id="registerModalLabel">
                        <i class="fa-solid fa-user-plus text-primary me-2"></i>Create an Account
                    </h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-3">

                <?php if ($register_error): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2 py-2 small">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($register_error) ?>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Username <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-user text-muted"></i></span>
                            <input type="text" name="username" class="form-control" placeholder="Username" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock text-muted"></i></span>
                            <input type="password" name="password" id="reg-pass" class="form-control" placeholder="Password" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePass('reg-pass', this)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" name="register" class="btn btn-primary">
                            <i class="fa-solid fa-user-plus me-1"></i> Create Account
                        </button>
                    </div>
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