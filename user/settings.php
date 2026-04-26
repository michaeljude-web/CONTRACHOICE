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
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/vendor/bootstrap-5/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendor/fontawesome-7/css/all.min.css">
  <style>
    :root {
      --bg:         #f5f0e8;
      --surface:    #fdfaf5;
      --surface-2:  #faf6ef;
      --border:     #e8dfd0;
      --text:       #4a3728;
      --muted:      #9b8776;
      --accent-blue:   #b8cfe8;
      --accent-blue-d: #6b9ab8;
      --accent-pink:   #f0d5d5;
      --accent-pink-d: #c47a7a;
      --accent-mint:   #cce8dc;
      --accent-mint-d: #5a9a7a;
      --accent-peach:  #f5ddd0;
      --accent-peach-d:#c47a55;
      --accent-lav:    #ddd5f0;
      --accent-lav-d:  #7a6ab8;
      --brown:      #7d5a4a;
      --brown-d:    #5a3a2a;
      --radius-sm:  12px;
      --radius-md:  18px;
      --radius-lg:  24px;
      --shadow-sm:  0 2px 8px rgba(120,80,50,.08);
      --shadow-md:  0 4px 16px rgba(120,80,50,.12);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: var(--bg);
      font-family: 'Nunito', sans-serif;
      color: var(--text);
      background-image:
        radial-gradient(circle at 15% 20%, rgba(184,207,232,.18) 0%, transparent 50%),
        radial-gradient(circle at 85% 75%, rgba(204,232,220,.15) 0%, transparent 50%),
        radial-gradient(circle at 50% 50%, rgba(240,213,213,.10) 0%, transparent 60%);
    }

    .layout { display: flex; height: 100vh; overflow: hidden; }
    .main   { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }

    .topbar {
      height: 56px;
      background: var(--surface);
      border-bottom: 1.5px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 28px;
      flex-shrink: 0;
      font-size: 13px;
      color: var(--muted);
      font-family: 'Quicksand', sans-serif;
    }
    .topbar b {
      color: var(--brown);
      font-weight: 700;
    }
    .topbar-left {
      display: flex;
      align-items: center;
      gap: 8px;
      font-weight: 600;
    }
    .topbar-sep {
      color: var(--border);
      font-size: 16px;
    }
    .topbar-page {
      color: var(--muted);
      font-weight: 500;
    }

    .page-body {
      flex: 1;
      overflow-y: auto;
      padding: 28px 32px;
    }
    .page-body::-webkit-scrollbar {
      width: 5px;
    }
    .page-body::-webkit-scrollbar-thumb {
      background: var(--border);
      border-radius: 10px;
    }

    .page-header {
      margin-bottom: 24px;
    }
    .page-header h1 {
      font-family: 'Quicksand', sans-serif;
      font-size: 22px;
      font-weight: 700;
      color: var(--brown-d);
      margin-bottom: 4px;
    }
    .page-header p {
      font-size: 13px;
      color: var(--muted);
    }

    .alert {
      border-radius: 16px;
      padding: 12px 18px;
      font-size: 13px;
      margin-bottom: 22px;
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 600;
    }
    .alert-success {
      background: var(--accent-mint);
      color: var(--accent-mint-d);
      border: 1.5px solid #b0d8c4;
    }
    .alert-danger {
      background: var(--accent-pink);
      color: var(--accent-pink-d);
      border: 1.5px solid #dfc0c0;
    }

    .accordion {
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-bottom: 28px;
    }

    .acc-item {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 20px;
      overflow: hidden;
    }

    .acc-trigger {
      width: 100%;
      background: none;
      border: none;
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 18px 22px;
      cursor: pointer;
      text-align: left;
      transition: background 0.15s;
    }
    .acc-trigger:hover {
      background: var(--surface-2);
    }

    .acc-icon {
      width: 44px;
      height: 44px;
      border-radius: 14px;
      background: var(--accent-blue);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .acc-icon i {
      font-size: 18px;
      color: var(--accent-blue-d);
    }

    .acc-label {
      flex: 1;
    }
    .acc-label h3 {
      font-size: 15px;
      font-weight: 700;
      color: var(--brown-d);
      margin-bottom: 3px;
    }
    .acc-label p {
      font-size: 12px;
      color: var(--muted);
      font-weight: 500;
    }

    .acc-arrow {
      font-size: 14px;
      color: var(--muted);
      transition: transform 0.25s;
      flex-shrink: 0;
    }
    .acc-item.open .acc-arrow {
      transform: rotate(90deg);
    }

    .acc-body {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.35s ease;
    }
    .acc-item.open .acc-body {
      max-height: 800px;
    }

    .acc-inner {
      padding: 0 22px 22px;
      border-top: 1.5px solid var(--border);
      padding-top: 20px;
    }

    .avatar-scroll {
      max-height: 220px;
      overflow-y: auto;
      padding-right: 4px;
    }
    .avatar-scroll::-webkit-scrollbar {
      width: 4px;
    }
    .avatar-scroll::-webkit-scrollbar-track {
      background: transparent;
    }
    .avatar-scroll::-webkit-scrollbar-thumb {
      background: var(--border);
      border-radius: 4px;
    }

    .field {
      margin-bottom: 16px;
    }
    .field:last-of-type {
      margin-bottom: 0;
    }
    .field label {
      display: block;
      font-size: 12px;
      font-weight: 700;
      color: var(--brown);
      margin-bottom: 6px;
    }
    .field input {
      width: 100%;
      border: 1.5px solid var(--border);
      border-radius: 14px;
      padding: 10px 16px;
      font-family: 'Nunito', sans-serif;
      font-size: 13px;
      color: var(--text);
      background: var(--surface);
      transition: border-color 0.18s;
    }
    .field input:focus {
      outline: none;
      border-color: var(--brown);
    }
    .field input:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    .field-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    .btn-save {
      background: var(--brown);
      color: #fff;
      border: none;
      border-radius: 50px;
      padding: 10px 24px;
      font-family: 'Nunito', sans-serif;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-top: 20px;
      transition: background 0.18s, transform 0.1s;
      box-shadow: 0 3px 10px rgba(125,90,74,.3);
    }
    .btn-save:hover {
      background: var(--brown-d);
      transform: translateY(-1px);
    }
    .btn-save:active {
      transform: scale(0.97);
    }

    .strength-bar {
      height: 4px;
      border-radius: 4px;
      background: var(--border);
      margin-top: 8px;
      overflow: hidden;
    }
    .strength-fill {
      height: 100%;
      border-radius: 4px;
      width: 0;
      transition: width 0.3s, background 0.3s;
    }
    .strength-label {
      font-size: 11px;
      color: var(--muted);
      margin-top: 5px;
      font-weight: 600;
    }

    .avatar-group-label {
      font-size: 11px;
      font-weight: 700;
      color: var(--brown);
      margin: 14px 0 8px;
    }
    .avatar-group-label:first-child {
      margin-top: 0;
    }

    .avatar-grid {
      display: grid;
      grid-template-columns: repeat(8, 1fr);
      gap: 10px;
    }

    .avatar-opt {
      aspect-ratio: 1;
      border-radius: 14px;
      border: 2px solid var(--border);
      background: var(--surface-2);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      cursor: pointer;
      transition: border-color 0.15s, background 0.15s, transform 0.1s;
    }
    .avatar-opt:hover {
      border-color: var(--brown);
      background: var(--surface);
      transform: scale(1.08);
    }
    .avatar-opt.selected {
      border-color: var(--brown);
      background: var(--accent-peach);
      box-shadow: 0 0 0 3px rgba(125,90,74,0.2);
    }

    .profile-card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 24px;
      padding: 28px 28px;
      display: flex;
      align-items: center;
      gap: 24px;
    }

    .profile-avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: var(--accent-blue);
      border: 3px solid var(--accent-blue-d);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 38px;
      flex-shrink: 0;
      box-shadow: 0 3px 10px rgba(107,154,184,0.3);
    }

    .profile-info h2 {
      font-family: 'Quicksand', sans-serif;
      font-size: 20px;
      font-weight: 700;
      color: var(--brown-d);
      margin-bottom: 6px;
    }
    .profile-info p {
      font-size: 13px;
      color: var(--muted);
      font-weight: 500;
    }

    .profile-badge {
      margin-left: auto;
      background: var(--surface-2);
      color: var(--brown);
      font-size: 12px;
      font-weight: 700;
      padding: 6px 18px;
      border-radius: 30px;
      border: 1.5px solid var(--border);
      flex-shrink: 0;
    }
  </style>
</head>
<body>
<div class="layout">
  <?php include '../includes/user/sidebar.php'; ?>

  <div class="main">
    <div class="topbar">
      <div class="topbar-left">
        <span><b>ContraChoice</b></span>
        <span class="topbar-sep">/</span>
        <span class="topbar-page"><?= htmlspecialchars($page_title) ?></span>
      </div>
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

      <div class="accordion">

        <div class="profile-card">
          <div class="profile-avatar" id="previewAvatar"><?= htmlspecialchars($current_avatar) ?></div>
          <div class="profile-info">
            <h2 id="previewName"><?= htmlspecialchars($user['username']) ?></h2>
            <p>ContraChoice Member</p>
          </div>
          <div class="profile-badge"><i class="fas fa-user" style="font-size:10px;margin-right:5px;"></i> User</div>
        </div>

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