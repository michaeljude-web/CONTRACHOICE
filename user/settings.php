<?php
session_start();
include '../includes/db_connection.php';
include '../includes/user/auth.php';

$page_title  = 'Account Settings';
$active_page = 'settings';
$user_id     = $_SESSION['user_id'] ?? 0;

$success = '';
$error   = '';

$stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (isset($_POST['action']) && $_POST['action'] === 'save_avatar') {
    $avatar = trim($_POST['avatar'] ?? '');
    $_SESSION['avatar'] = $avatar;
    $success = "Avatar updated successfully.";
}

if (isset($_POST['action']) && $_POST['action'] === 'change_username') {
    $new_username = trim($_POST['new_username'] ?? '');
    $confirm_pass = trim($_POST['confirm_pass'] ?? '');
    if (empty($new_username) || empty($confirm_pass)) {
        $error = "Please fill in all fields.";
    } elseif (strlen($new_username) < 3) {
        $error = "Username must be at least 3 characters.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $new_username)) {
        $error = "Username can only contain letters, numbers, and underscores.";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!password_verify($confirm_pass, $row['password'])) {
            $error = "Incorrect password.";
        } else {
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
            $stmt->bind_param("si", $new_username, $user_id);
            $stmt->execute();
            $taken = $stmt->get_result()->num_rows > 0;
            $stmt->close();
            if ($taken) {
                $error = "That username is already taken.";
            } else {
                $stmt = $conn->prepare("UPDATE users SET username = ? WHERE user_id = ?");
                $stmt->bind_param("si", $new_username, $user_id);
                $stmt->execute();
                $stmt->close();
                $_SESSION['username'] = $new_username;
                $user['username'] = $new_username;
                $success = "Username updated successfully.";
            }
        }
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_pass = trim($_POST['current_pass'] ?? '');
    $new_pass     = trim($_POST['new_pass'] ?? '');
    $confirm_new  = trim($_POST['confirm_new'] ?? '');
    if (empty($current_pass) || empty($new_pass) || empty($confirm_new)) {
        $error = "Please fill in all fields.";
    } elseif ($new_pass !== $confirm_new) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_pass) < 6) {
        $error = "New password must be at least 6 characters.";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!password_verify($current_pass, $row['password'])) {
            $error = "Current password is incorrect.";
        } else {
            $hashed = password_hash($new_pass, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->bind_param("si", $hashed, $user_id);
            $stmt->execute();
            $stmt->close();
            $success = "Password updated successfully.";
        }
    }
}

$current_avatar = $_SESSION['avatar'] ?? '🌸';

// Determine which accordion to auto-open after submit
$open_panel = 'none';
if ($success || $error) {
    $action = $_POST['action'] ?? '';
    if ($action === 'save_avatar')      $open_panel = 'avatar';
    elseif ($action === 'change_username') $open_panel = 'username';
    elseif ($action === 'change_password') $open_panel = 'password';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title) ?> — ContraChoice</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400&family=Outfit:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/vendor/bootstrap-5/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendor/fontawesome-7/css/all.min.css">
  <style>
    :root {
      --bg: #f8f6f0;
      --surface: #ffffff;
      --border: #e8e4dc;
      --text: #2c2b28;
      --muted: #6b6b67;
      --blue-50: #e8f1fb;
      --blue-100: #b5d4f4;
      --blue-600: #185FA5;
      --blue-800: #0C447C;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { background: var(--bg); font-family: 'Outfit', sans-serif; color: var(--text); }

    .layout { display: flex; height: 100vh; overflow: hidden; }
    .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }

    .topbar {
      height: 52px; background: var(--surface);
      border-bottom: 0.5px solid var(--border);
      display: flex; align-items: center;
      padding: 0 28px; flex-shrink: 0;
      font-size: 13px; color: var(--muted);
    }
    .topbar b { color: var(--text); font-weight: 500; }

    .page-body { flex: 1; overflow-y: auto; padding: 28px 32px; }
    .page-body::-webkit-scrollbar { width: 4px; }
    .page-body::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

    .page-header { margin-bottom: 24px; }
    .page-header h1 {
      font-family: 'Playfair Display', serif;
      font-size: 22px; font-weight: 500; margin-bottom: 4px;
    }
    .page-header p { font-size: 13px; color: var(--muted); }

    .alert {
      border-radius: 14px; padding: 11px 16px;
      font-size: 13px; margin-bottom: 22px;
      display: flex; align-items: center; gap: 9px;
    }
    .alert-success { background: #eaf3de; color: #27500a; }
    .alert-danger  { background: #fcebeb; color: #791f1f; }

    /* ── ACCORDION ──────────────────────────── */
    .accordion { display: flex; flex-direction: column; gap: 10px; margin-bottom: 24px; }

    .acc-item {
      background: var(--surface);
      border: 0.5px solid var(--border);
      border-radius: 16px;
      overflow: hidden;
    }

    .acc-trigger {
      width: 100%; background: none; border: none;
      display: flex; align-items: center; gap: 14px;
      padding: 16px 20px;
      cursor: pointer;
      text-align: left;
      transition: background .15s;
    }
    .acc-trigger:hover { background: var(--bg); }

    .acc-icon {
      width: 36px; height: 36px; border-radius: 10px;
      background: var(--blue-50);
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }
    .acc-icon i { font-size: 15px; color: var(--blue-600); }

    .acc-label { flex: 1; }
    .acc-label h3 { font-size: 14px; font-weight: 500; color: var(--text); margin-bottom: 2px; }
    .acc-label p  { font-size: 12px; color: var(--muted); }

    .acc-arrow {
      font-size: 12px; color: var(--muted);
      transition: transform .25s;
      flex-shrink: 0;
    }
    .acc-item.open .acc-arrow { transform: rotate(90deg); }

    .acc-body {
      max-height: 0;
      overflow: hidden;
      transition: max-height .3s ease;
    }
    .acc-item.open .acc-body { max-height: 800px; }

    .acc-inner {
      padding: 0 20px 20px;
      border-top: 0.5px solid var(--border);
      padding-top: 18px;
    }

    .avatar-scroll {
      max-height: 220px;
      overflow-y: auto;
      padding-right: 4px;
    }
    .avatar-scroll::-webkit-scrollbar { width: 4px; }
    .avatar-scroll::-webkit-scrollbar-track { background: transparent; }
    .avatar-scroll::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

    /* ── FORM FIELDS ────────────────────────── */
    .field { margin-bottom: 14px; }
    .field:last-of-type { margin-bottom: 0; }
    .field label {
      display: block; font-size: 12px; font-weight: 500;
      color: var(--muted); margin-bottom: 6px;
    }
    .field input {
      width: 100%;
      border: 0.5px solid var(--border);
      border-radius: 12px;
      padding: 10px 14px;
      font-family: 'Outfit', sans-serif;
      font-size: 13.5px; color: var(--text);
      background: var(--bg);
      transition: border-color .18s, background .18s;
    }
    .field input:focus { outline: none; border-color: var(--blue-600); background: var(--surface); }
    .field input:disabled { opacity: .55; cursor: not-allowed; }
    .field input::placeholder { color: var(--muted); opacity: .6; }

    .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

    .btn-save {
      background: var(--blue-600); color: #fff;
      border: none; border-radius: 12px;
      padding: 10px 20px;
      font-family: 'Outfit', sans-serif;
      font-size: 13px; font-weight: 500;
      cursor: pointer;
      display: inline-flex; align-items: center; gap: 7px;
      margin-top: 16px;
      transition: background .18s, transform .1s;
    }
    .btn-save:hover { background: var(--blue-800); }
    .btn-save:active { transform: scale(.97); }

    /* password strength */
    .strength-bar { height: 3px; border-radius: 3px; background: var(--border); margin-top: 7px; overflow: hidden; }
    .strength-fill { height: 100%; border-radius: 3px; width: 0; transition: width .3s, background .3s; }
    .strength-label { font-size: 11px; color: var(--muted); margin-top: 4px; min-height: 16px; }

    /* ── AVATAR PICKER ──────────────────────── */
    .avatar-group-label {
      font-size: 11px; color: var(--muted); font-weight: 500;
      margin: 12px 0 7px;
    }
    .avatar-group-label:first-child { margin-top: 0; }

    .avatar-grid {
      display: grid;
      grid-template-columns: repeat(8, 1fr);
      gap: 8px;
    }

    .avatar-opt {
      aspect-ratio: 1; border-radius: 12px;
      border: 2px solid var(--border);
      background: var(--bg);
      display: flex; align-items: center; justify-content: center;
      font-size: 22px; cursor: pointer;
      transition: border-color .15s, background .15s, transform .12s;
      line-height: 1;
    }
    .avatar-opt:hover { border-color: var(--blue-100); background: var(--blue-50); transform: scale(1.1); }
    .avatar-opt.selected {
      border-color: var(--blue-600); background: var(--blue-50);
      box-shadow: 0 0 0 3px rgba(24,95,165,.15);
    }

    /* ── PROFILE CARD (bottom) ──────────────── */
    .profile-card {
      background: var(--surface);
      border: 0.5px solid var(--border);
      border-radius: 20px;
      padding: 28px 24px;
      display: flex; align-items: center; gap: 20px;
    }

    .profile-avatar {
      width: 72px; height: 72px; border-radius: 50%;
      background: var(--blue-50);
      border: 2px solid var(--blue-100);
      display: flex; align-items: center; justify-content: center;
      font-size: 34px; line-height: 1; flex-shrink: 0;
    }

    .profile-info h2 {
      font-family: 'Playfair Display', serif;
      font-size: 18px; font-weight: 500; margin-bottom: 4px;
    }
    .profile-info p { font-size: 12px; color: var(--muted); }

    .profile-badge {
      margin-left: auto;
      background: var(--blue-50); color: var(--blue-800);
      font-size: 11px; padding: 4px 14px;
      border-radius: 30px; border: 0.5px solid var(--blue-100);
      flex-shrink: 0;
    }
  </style>
</head>
<body>
<div class="layout">
  <?php include '../includes/user/sidebar.php'; ?>

  <div class="main">
    <div class="topbar">
      ContraChoice &rsaquo; <b>&nbsp;Account Settings</b>
    </div>

    <div class="page-body">

      <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
      <?php elseif ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div class="page-header">
        <h1>Account Settings</h1>
        <p>Manage your avatar, username, and password.</p>
      </div>

      <!-- ACCORDION -->
      <div class="accordion">

      <!-- PROFILE CARD at the bottom -->
      <div class="profile-card">
        <div class="profile-avatar" id="previewAvatar"><?= htmlspecialchars($current_avatar) ?></div>
        <div class="profile-info">
          <h2 id="previewName"><?= htmlspecialchars($user['username']) ?></h2>
          <p>ContraChoice Member</p>
        </div>
        <div class="profile-badge"><i class="fas fa-user" style="font-size:10px;margin-right:4px;"></i> User</div>
      </div>

        <!-- 1. Choose Avatar -->
        <div class="acc-item <?= $open_panel === 'avatar' ? 'open' : '' ?>" id="acc-avatar">
          <button class="acc-trigger" onclick="toggle('acc-avatar')" type="button">
            <div class="acc-icon"><i class="fas fa-face-smile"></i></div>
            <div class="acc-label">
              <h3>Choose Avatar</h3>
              <p>Pick an emoji as your profile picture</p>
            </div>
            <i class="fas fa-chevron-right acc-arrow"></i>
          </button>
          <div class="acc-body">
            <div class="acc-inner">
              <form method="POST">
                <input type="hidden" name="action" value="save_avatar">
                <input type="hidden" name="avatar" id="avatarInput" value="<?= htmlspecialchars($current_avatar) ?>">

                <div class="avatar-scroll">
                <?php
                $avatar_groups = [
                  'Plants & Nature' => ['🌸','🌺','🌻','🌹','🌷','🌼','🍀','🌿','🍃','🌱','🌾','🌵'],
                  'Animals'         => ['🦋','🐝','🐞','🐢','🦜','🦩','🐬','🐱','🐶','🐼','🦊','🐸'],
                  'Fun & Misc'      => ['⭐','🌙','☀️','🌈','💫','✨','🎀','💎','🍓','🍉','🎵','🎨'],
                ];
                foreach ($avatar_groups as $label => $emojis): ?>
                  <div class="avatar-group-label"><?= $label ?></div>
                  <div class="avatar-grid">
                    <?php foreach ($emojis as $emoji): ?>
                      <button type="button"
                        class="avatar-opt <?= $emoji === $current_avatar ? 'selected' : '' ?>"
                        data-emoji="<?= htmlspecialchars($emoji) ?>"
                        onclick="selectAvatar(this)">
                        <?= $emoji ?>
                      </button>
                    <?php endforeach; ?>
                  </div>
                <?php endforeach; ?>
                </div>

                <button type="submit" class="btn-save">
                  <i class="fas fa-check"></i> Save Avatar
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- 2. Change Username -->
        <div class="acc-item <?= $open_panel === 'username' ? 'open' : '' ?>" id="acc-username">
          <button class="acc-trigger" onclick="toggle('acc-username')" type="button">
            <div class="acc-icon"><i class="fas fa-user-pen"></i></div>
            <div class="acc-label">
              <h3>Change Username</h3>
              <p>Update your display name</p>
            </div>
            <i class="fas fa-chevron-right acc-arrow"></i>
          </button>
          <div class="acc-body">
            <div class="acc-inner">
              <form method="POST">
                <input type="hidden" name="action" value="change_username">
                <div class="field">
                  <label>Current Username</label>
                  <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                </div>
                <div class="field">
                  <label>New Username</label>
                  <input type="text" name="new_username" placeholder="Enter new username" autocomplete="off" required minlength="3">
                </div>
                <div class="field">
                  <label>Confirm with Password</label>
                  <input type="password" name="confirm_pass" placeholder="Enter your current password" required>
                </div>
                <button type="submit" class="btn-save">
                  <i class="fas fa-user-check"></i> Update Username
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- 3. Change Password -->
        <div class="acc-item <?= $open_panel === 'password' ? 'open' : '' ?>" id="acc-password">
          <button class="acc-trigger" onclick="toggle('acc-password')" type="button">
            <div class="acc-icon"><i class="fas fa-lock"></i></div>
            <div class="acc-label">
              <h3>Change Password</h3>
              <p>Use a strong password you don't use elsewhere</p>
            </div>
            <i class="fas fa-chevron-right acc-arrow"></i>
          </button>
          <div class="acc-body">
            <div class="acc-inner">
              <form method="POST">
                <input type="hidden" name="action" value="change_password">
                <div class="field">
                  <label>Current Password</label>
                  <input type="password" name="current_pass" placeholder="Enter current password" required>
                </div>
                <div class="field-row">
                  <div class="field">
                    <label>New Password</label>
                    <input type="password" name="new_pass" placeholder="Min. 6 characters" required minlength="6" oninput="checkStrength(this.value)">
                    <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                    <div class="strength-label" id="strengthLabel"></div>
                  </div>
                  <div class="field">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_new" placeholder="Repeat new password" required minlength="6">
                  </div>
                </div>
                <button type="submit" class="btn-save">
                  <i class="fas fa-key"></i> Update Password
                </button>
              </form>
            </div>
          </div>
        </div>

      </div>
      <!-- end accordion -->

      

    </div>
  </div>
</div>

<script>
function toggle(id) {
  const item = document.getElementById(id);
  const isOpen = item.classList.contains('open');
  document.querySelectorAll('.acc-item').forEach(i => i.classList.remove('open'));
  if (!isOpen) item.classList.add('open');
}

function selectAvatar(btn) {
  document.querySelectorAll('.avatar-opt').forEach(b => b.classList.remove('selected'));
  btn.classList.add('selected');
  const emoji = btn.dataset.emoji;
  document.getElementById('avatarInput').value = emoji;
  document.getElementById('previewAvatar').textContent = emoji;
}

function checkStrength(val) {
  const fill  = document.getElementById('strengthFill');
  const label = document.getElementById('strengthLabel');
  let score = 0;
  if (val.length >= 6)  score++;
  if (val.length >= 10) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^a-zA-Z0-9]/.test(val)) score++;
  const levels = [
    { w:'0%',   bg:'transparent', txt:'' },
    { w:'25%',  bg:'#e24b4a',     txt:'Weak' },
    { w:'50%',  bg:'#ef9f27',     txt:'Fair' },
    { w:'75%',  bg:'#97c459',     txt:'Good' },
    { w:'100%', bg:'#3b6d11',     txt:'Strong' },
  ];
  const lvl = val.length === 0 ? levels[0] : levels[Math.min(score, 4)];
  fill.style.width = lvl.w;
  fill.style.background = lvl.bg;
  label.textContent = lvl.txt;
  label.style.color = lvl.bg;
}
</script>
</body>
</html>