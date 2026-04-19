<?php
$active_page = $active_page ?? 'dashboard';
$is_admin    = false;

$user_name = 'User';
if (isset($_SESSION['user_id'])) {
    $sid    = intval($_SESSION['user_id']);
    $result = mysqli_query($conn, "SELECT username FROM users WHERE user_id = $sid LIMIT 1");
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $user_name = $row['username'];
    }
}
$initials    = strtoupper(substr($user_name, 0, 2));
$user_avatar = $_SESSION['avatar'] ?? null;

$user_nav = [
  ['section' => 'Main'],
  ['id' => 'dashboard',       'label' => 'Dashboard',          'icon' => 'grid',        'color' => '#C1666B'],
  ['id' => 'questionnaire',   'label' => 'Questionnaire',      'icon' => 'list-search', 'color' => '#7A9E8E'],
  ['id' => 'comparison',      'label' => 'Comparison guide',   'icon' => 'compare',     'color' => '#C8956C'],
  ['id' => 'recommendations', 'label' => 'My recommendations', 'icon' => 'star',        'color' => '#B07CC6'],
  ['section' => 'Community'],
  ['id' => 'forum',           'label' => 'Anonymous forum',    'icon' => 'chat',        'color' => '#7A9E8E'],
  ['id' => 'chatbot',         'label' => 'AI chatbot',         'icon' => 'bot',         'color' => '#6A90C4'],
  ['section' => 'Account'],
  ['id' => 'settings',        'label' => 'Settings',           'icon' => 'settings',    'color' => '#9E9E9E'],
];

$admin_nav = [
  ['section' => 'Overview'],
  ['id' => 'dashboard',     'label' => 'Dashboard',           'icon' => 'grid',        'color' => '#C1666B'],
  ['id' => 'analytics',     'label' => 'Analytics',           'icon' => 'analytics',   'color' => '#7A9E8E'],
  ['section' => 'Content'],
  ['id' => 'methods',       'label' => 'Manage methods',      'icon' => 'compare',     'color' => '#C8956C'],
  ['id' => 'qstats',        'label' => 'Questionnaire stats', 'icon' => 'list-search', 'color' => '#7A9E8E'],
  ['id' => 'chatresponses', 'label' => 'Chatbot responses',   'icon' => 'bot',         'color' => '#6A90C4'],
  ['section' => 'Moderation'],
  ['id' => 'forum-mod',     'label' => 'Forum moderation',    'icon' => 'chat',        'color' => '#B07CC6', 'badge' => '5', 'badge_type' => 'red'],
  ['id' => 'reports',       'label' => 'Reported posts',      'icon' => 'flag',        'color' => '#C1666B', 'badge' => '2', 'badge_type' => 'red'],
  ['section' => 'System'],
  ['id' => 'settings',      'label' => 'Settings',            'icon' => 'settings',    'color' => '#9E9E9E'],
  ['id' => 'accounts',      'label' => 'User accounts',       'icon' => 'users',       'color' => '#C1666B'],
];

$nav_items = $is_admin ? $admin_nav : $user_nav;

$icons = [
  'grid' => '<svg width="18" height="18" viewBox="0 0 20 20" fill="none"><rect x="2.5" y="2.5" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="11.5" y="2.5" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="2.5" y="11.5" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="11.5" y="11.5" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="2"/></svg>',
  'list-search' => '<svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M2.5 5h15M2.5 10h9M2.5 15h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="15.5" cy="14.5" r="2.5" stroke="currentColor" stroke-width="2"/><path d="M17.5 16.5l2 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
  'compare' => '<svg width="18" height="18" viewBox="0 0 20 20" fill="none"><rect x="2" y="4" width="7" height="12" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="11" y="4" width="7" height="12" rx="1.5" stroke="currentColor" stroke-width="2"/><path d="M9 10h2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
  'star' => '<svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M10 2.5l2.2 4.5L17 7.7l-3.5 3.4.8 4.9L10 13.7l-4.3 2.3.8-4.9L3 7.7l4.8-.7L10 2.5z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M10 2.5l2.2 4.5L17 7.7l-3.5 3.4.8 4.9L10 13.7l-4.3 2.3.8-4.9L3 7.7l4.8-.7L10 2.5z" fill="currentColor" fill-opacity="0.15"/></svg>',
  'chat' => '<svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M3 4h14v10h-7l-4 4v-4H3V4z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>',
  'bot' => '<svg width="18" height="18" viewBox="0 0 20 20" fill="none"><rect x="3" y="5" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2"/><path d="M6 15l2-2h4l2 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="8" cy="10" r="1.5" fill="currentColor"/><circle cx="12" cy="10" r="1.5" fill="currentColor"/></svg>',
  'analytics' => '<svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M3 15l5-5 3 3 6-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="17" cy="6" r="2" stroke="currentColor" stroke-width="2"/></svg>',
  'flag' => '<svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M5 3v14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M5 5h10l-3 4 3 4H5" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>',
  'settings' => '<svg width="18" height="18" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="2.5" stroke="currentColor" stroke-width="2"/><path d="M10 3v1.5M10 15.5V17M3 10h1.5M15.5 10H17M5.5 5.5l1 1M13.5 13.5l1 1M5.5 14.5l1-1M13.5 6.5l1-1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
  'users' => '<svg width="18" height="18" viewBox="0 0 20 20" fill="none"><circle cx="7.5" cy="6.5" r="2.5" stroke="currentColor" stroke-width="2"/><path d="M2.5 16c0-2.8 2.5-4.5 5-4.5s5 1.7 5 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="15" cy="6" r="2" stroke="currentColor" stroke-width="2"/><path d="M17.5 15.5c0-1.8-1.5-3-3.5-3.2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
  'logout' => '<svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M7.5 4H5.5A1.5 1.5 0 004 5.5v9A1.5 1.5 0 005.5 16h2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12.5 12.5L16 9l-3.5-3.5M16 9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  'dots' => '<svg width="18" height="18" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="4" r="1.5" fill="currentColor"/><circle cx="10" cy="10" r="1.5" fill="currentColor"/><circle cx="10" cy="16" r="1.5" fill="currentColor"/></svg>',
];
?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,400;1,500&family=DM+Sans:wght@300;400;500;600&display=swap');

  :root {
    --rose:         #C1666B;
    --rose-deep:    #9E4A4F;
    --rose-pale:    #F5DDE0;
    --rose-blush:   #FDF0F1;
    --cream:        #FAF7F2;
    --ink:          #1C1A18;
    --ink-soft:     #2E2B28;
    --muted:        #9A9289;
    --border:       rgba(0,0,0,0.055);
    --border-md:    rgba(0,0,0,0.09);
    --surface:      #ffffff;
    --surface2:     #F5F2ED;
    --red-bg:       #FDF0F1;
    --red-text:     #9E4A4F;
    --sidebar-w:    252px;
  }

  .cc-layout { display: flex; height: 100vh; overflow: hidden; }
  .cc-main { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: var(--cream); }

  .sidebar {
    width: var(--sidebar-w);
    height: 100vh;
    background: var(--surface);
    border-right: 1px solid var(--border-md);
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    font-family: 'DM Sans', sans-serif;
  }

  .sidebar-logo {
    padding: 26px 20px 18px;
    border-bottom: 1px solid var(--border);
    flex-shrink: 0;
  }

  .logo-wordmark {
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px;
    font-weight: 600;
    color: var(--ink);
    letter-spacing: -0.5px;
    line-height: 1;
  }

  .logo-wordmark em {
    font-style: italic;
    color: var(--rose);
  }

  .logo-tagline {
    font-size: 10.5px;
    color: var(--muted);
    margin-top: 5px;
    letter-spacing: 0.04em;
    font-weight: 400;
  }

  .sidebar-nav {
    flex: 1;
    padding: 10px 10px;
    overflow-y: auto;
    scrollbar-width: none;
  }
  .sidebar-nav::-webkit-scrollbar { display: none; }

  .nav-section-label {
    font-size: 9.5px;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--muted);
    padding: 14px 10px 5px;
    display: block;
    opacity: 0.7;
  }

  .nav-item {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 9px 11px;
    border-radius: 11px;
    cursor: pointer;
    margin-bottom: 2px;
    transition: background 0.18s ease, color 0.18s ease;
    text-decoration: none;
    color: #6B6560;
    position: relative;
  }

  .nav-item:hover {
    background: var(--surface2);
    color: var(--ink);
    text-decoration: none;
  }

  .nav-item.active {
    background: var(--rose-blush);
    color: var(--rose-deep);
  }

  .nav-item.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 20%;
    bottom: 20%;
    width: 3px;
    background: var(--rose);
    border-radius: 0 3px 3px 0;
  }

  .nav-item.active .nav-label { font-weight: 600; }

  .nav-item.active-admin {
    background: #F0EDF8;
    color: #5A3F9E;
  }

  .nav-item.active-admin::before {
    content: '';
    position: absolute;
    left: 0;
    top: 20%;
    bottom: 20%;
    width: 3px;
    background: #8B6FD4;
    border-radius: 0 3px 3px 0;
  }

  .nav-item.active-admin .nav-label { font-weight: 600; }

  .nav-icon {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0.9;
  }

  .nav-label {
    font-size: 13px;
    flex: 1;
    font-weight: 500;
    letter-spacing: -0.1px;
  }

  .nav-badge {
    font-size: 10px;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 30px;
    flex-shrink: 0;
  }

  .badge-red { background: var(--red-bg); color: var(--red-text); }

  .sidebar-footer {
    border-top: 1px solid var(--border);
    padding: 12px 10px;
    position: relative;
    background: var(--surface);
    flex-shrink: 0;
  }

  .user-chip {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border-radius: 12px;
    transition: background 0.18s;
    cursor: default;
  }

  .user-chip:hover { background: var(--surface2); }

  .avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .avatar-emoji { font-size: 18px; background: var(--rose-blush); border: 1px solid rgba(193,102,107,0.2); }

  .avatar-initials {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.02em;
    font-family: 'DM Sans', sans-serif;
  }

  .avatar-user  { background: var(--rose-blush); color: var(--rose-deep); border: 1px solid rgba(193,102,107,0.2); }
  .avatar-admin { background: #F0EDF8; color: #5A3F9E; border: 1px solid rgba(139,111,212,0.2); }

  .user-info { flex: 1; min-width: 0; }

  .user-name {
    font-size: 12.5px;
    font-weight: 600;
    color: var(--ink);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-family: 'DM Sans', sans-serif;
  }

  .user-role {
    font-size: 10.5px;
    color: var(--muted);
    font-family: 'DM Sans', sans-serif;
  }

  .dots-btn {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    border: 1px solid transparent;
    background: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--muted);
    transition: all 0.18s;
    padding: 0;
    flex-shrink: 0;
  }

  .dots-btn:hover, .dots-btn.open {
    background: var(--surface2);
    border-color: var(--border-md);
    color: var(--ink);
  }

  .user-menu {
    position: absolute;
    bottom: calc(100% + 6px);
    left: 10px;
    right: 10px;
    background: var(--surface);
    border: 1px solid var(--border-md);
    border-radius: 14px;
    padding: 5px;
    box-shadow: 0 8px 28px rgba(0,0,0,0.1);
    display: none;
    z-index: 100;
  }

  .user-menu.open { display: block; }

  .menu-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 11px;
    border-radius: 10px;
    font-size: 13px;
    color: #6B6560;
    text-decoration: none;
    transition: all 0.15s;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
    font-family: 'DM Sans', sans-serif;
    font-weight: 500;
  }

  .menu-item:hover { background: var(--surface2); color: var(--ink); }

  .menu-item.danger { color: var(--rose-deep); }
  .menu-item.danger:hover { background: var(--rose-blush); color: var(--rose-deep); }

  .menu-divider { height: 1px; background: var(--border); margin: 4px 0; }

  .modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(28,26,24,0.4);
    backdrop-filter: blur(3px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 999;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s;
  }

  .modal-overlay.open { opacity: 1; pointer-events: all; }

  .modal-box {
    background: var(--surface);
    border-radius: 22px;
    padding: 28px 24px 22px;
    width: 310px;
    max-width: calc(100vw - 32px);
    box-shadow: 0 24px 40px rgba(0,0,0,0.15);
    transform: translateY(10px) scale(0.98);
    transition: transform 0.22s cubic-bezier(.22,.68,0,1.2);
    font-family: 'DM Sans', sans-serif;
  }

  .modal-overlay.open .modal-box { transform: translateY(0) scale(1); }

  .modal-icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--rose-blush);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    color: var(--rose-deep);
  }

  .modal-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 6px;
  }

  .modal-desc {
    font-size: 13px;
    color: var(--muted);
    line-height: 1.6;
    margin-bottom: 22px;
  }

  .modal-actions { display: flex; gap: 8px; justify-content: flex-end; }

  .btn {
    padding: 8px 18px;
    border-radius: 40px;
    font-size: 13px;
    font-family: 'DM Sans', sans-serif;
    font-weight: 500;
    cursor: pointer;
    border: 1px solid var(--border-md);
    transition: all 0.18s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 7px;
  }

  .btn-cancel { background: none; color: #6B6560; }
  .btn-cancel:hover { background: var(--surface2); color: var(--ink); }

  .btn-logout { background: var(--rose); border-color: var(--rose); color: white; }
  .btn-logout:hover { background: var(--rose-deep); border-color: var(--rose-deep); }
</style>

<aside class="sidebar" id="cc-sidebar">

  <div class="sidebar-logo">
    <div class="logo-wordmark">Contra<em>Choice</em></div>
    <div class="logo-tagline"><?= $is_admin ? 'Admin Panel' : "Women's Health Companion" ?></div>
  </div>

  <nav class="sidebar-nav">
    <?php foreach ($nav_items as $item): ?>
      <?php if (isset($item['section'])): ?>
        <span class="nav-section-label"><?= htmlspecialchars($item['section']) ?></span>
      <?php else:
        $active_cls = ($active_page === $item['id']) ? ($is_admin ? 'active-admin' : 'active') : '';
        $url = $is_admin ? '/hci/admin/' . $item['id'] . '.php' : '/hci/user/' . $item['id'] . '.php';
        $icon_color = ($active_page === $item['id']) ? ($is_admin ? '#5A3F9E' : 'var(--rose)') : ($item['color'] ?? '#9A9289');
      ?>
        <a href="<?= htmlspecialchars($url) ?>" class="nav-item <?= $active_cls ?>">
          <span class="nav-icon" style="color: <?= $icon_color ?>;"><?= $icons[$item['icon']] ?? '' ?></span>
          <span class="nav-label"><?= htmlspecialchars($item['label']) ?></span>
          <?php if (!empty($item['badge'])): ?>
            <span class="nav-badge badge-<?= htmlspecialchars($item['badge_type'] ?? 'red') ?>">
              <?= htmlspecialchars($item['badge']) ?>
            </span>
          <?php endif; ?>
        </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </nav>

  <div class="sidebar-footer">
    <div class="user-chip">
      <?php if ($user_avatar): ?>
        <div class="avatar avatar-emoji"><?= htmlspecialchars($user_avatar) ?></div>
      <?php else: ?>
        <div class="avatar avatar-initials avatar-<?= $is_admin ? 'admin' : 'user' ?>"><?= htmlspecialchars($initials) ?></div>
      <?php endif; ?>
      <div class="user-info">
        <div class="user-name"><?= htmlspecialchars($user_name) ?></div>
        <div class="user-role">@<?= htmlspecialchars($user_name) ?></div>
      </div>
      <button class="dots-btn" id="cc-dots-btn" type="button" onclick="ccToggleMenu(event)" aria-label="User options">
        <?= $icons['dots'] ?>
      </button>
    </div>
    <div class="user-menu" id="cc-user-menu">
      <div class="menu-divider"></div>
      <button class="menu-item danger" type="button" onclick="ccOpenLogout()">
        <span class="nav-icon" style="color: var(--rose-deep);"><?= $icons['logout'] ?></span> Logout
      </button>
    </div>
  </div>
</aside>

<div class="modal-overlay" id="cc-logout-modal">
  <div class="modal-box">
    <div class="modal-icon-wrap"><?= $icons['logout'] ?></div>
    <div class="modal-title">Sign out?</div>
    <p class="modal-desc">You will be logged out and redirected to the login page.</p>
    <div class="modal-actions">
      <button class="btn btn-cancel" type="button" onclick="ccCloseLogout()">Cancel</button>
      <a class="btn btn-logout" href="/hci/user/login.php">Log out</a>
    </div>
  </div>
</div>

<script>
function ccToggleMenu(e) {
  e.stopPropagation();
  const menu = document.getElementById('cc-user-menu');
  const btn  = document.getElementById('cc-dots-btn');
  const open = menu.classList.toggle('open');
  btn.classList.toggle('open', open);
}
document.addEventListener('click', function() {
  document.getElementById('cc-user-menu').classList.remove('open');
  document.getElementById('cc-dots-btn').classList.remove('open');
});
function ccOpenLogout() {
  document.getElementById('cc-user-menu').classList.remove('open');
  document.getElementById('cc-dots-btn').classList.remove('open');
  document.getElementById('cc-logout-modal').classList.add('open');
}
function ccCloseLogout() {
  document.getElementById('cc-logout-modal').classList.remove('open');
}
document.getElementById('cc-logout-modal').addEventListener('click', function(e) {
  if (e.target === this) ccCloseLogout();
});
</script>