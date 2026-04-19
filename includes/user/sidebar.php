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
  ['id' => 'dashboard',       'label' => 'Dashboard',          'icon' => 'grid',        'color' => '#5B8FB9'],
  ['id' => 'questionnaire',   'label' => 'Questionnaire',      'icon' => 'list-search', 'color' => '#6DA56D'],
  ['id' => 'comparison',      'label' => 'Comparison guide',   'icon' => 'compare',     'color' => '#E8A35E'],
  ['id' => 'recommendations', 'label' => 'My recommendations', 'icon' => 'star',        'color' => '#E598B3'],
  ['section' => 'Community'],
  ['id' => 'forum',           'label' => 'Anonymous forum',    'icon' => 'chat',        'color' => '#A97BC9'],
  ['id' => 'chatbot',         'label' => 'AI chatbot',         'icon' => 'bot',         'color' => '#5F9D8F'],
  ['section' => 'Account'],
  ['id' => 'settings',        'label' => 'Settings',           'icon' => 'settings',    'color' => '#B8A9C9'],
];

$admin_nav = [
  ['section' => 'Overview'],
  ['id' => 'dashboard',     'label' => 'Dashboard',           'icon' => 'grid',        'color' => '#5B8FB9'],
  ['id' => 'analytics',     'label' => 'Analytics',           'icon' => 'analytics',   'color' => '#6DA56D'],
  ['section' => 'Content'],
  ['id' => 'methods',       'label' => 'Manage methods',      'icon' => 'compare',     'color' => '#E8A35E'],
  ['id' => 'qstats',        'label' => 'Questionnaire stats', 'icon' => 'list-search', 'color' => '#6DA56D'],
  ['id' => 'chatresponses', 'label' => 'Chatbot responses',   'icon' => 'bot',         'color' => '#5F9D8F'],
  ['section' => 'Moderation'],
  ['id' => 'forum-mod',     'label' => 'Forum moderation',    'icon' => 'chat',        'color' => '#A97BC9', 'badge' => '5', 'badge_type' => 'red'],
  ['id' => 'reports',       'label' => 'Reported posts',      'icon' => 'flag',        'color' => '#D48A6B', 'badge' => '2', 'badge_type' => 'red'],
  ['section' => 'System'],
  ['id' => 'settings',      'label' => 'Settings',            'icon' => 'settings',    'color' => '#B8A9C9'],
  ['id' => 'accounts',      'label' => 'User accounts',       'icon' => 'users',       'color' => '#C97E8A'],
];

$nav_items = $is_admin ? $admin_nav : $user_nav;

// BOLD & MAKAPAL ICONS - stroke-width 2 to 2.5, some with fill
$icons = [
  'grid' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><rect x="2.5" y="2.5" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="11.5" y="2.5" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="2.5" y="11.5" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="11.5" y="11.5" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="2"/></svg>',
  
  'list-search' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M2.5 5h15M2.5 10h9M2.5 15h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="15.5" cy="14.5" r="2.5" stroke="currentColor" stroke-width="2"/><path d="M17.5 16.5l2 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
  
  'compare' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><rect x="2" y="4" width="7" height="12" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="11" y="4" width="7" height="12" rx="1.5" stroke="currentColor" stroke-width="2"/><path d="M9 10h2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
  
  'star' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 2.5l2.2 4.5L17 7.7l-3.5 3.4.8 4.9L10 13.7l-4.3 2.3.8-4.9L3 7.7l4.8-.7L10 2.5z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M10 2.5l2.2 4.5L17 7.7l-3.5 3.4.8 4.9L10 13.7l-4.3 2.3.8-4.9L3 7.7l4.8-.7L10 2.5z" fill="currentColor" fill-opacity="0.15"/></svg>',
  
  'chat' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M3 4h14v10h-7l-4 4v-4H3V4z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>',
  
  'bot' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><rect x="3" y="5" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2"/><path d="M6 15l2-2h4l2 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="8" cy="10" r="1.5" fill="currentColor"/><circle cx="12" cy="10" r="1.5" fill="currentColor"/></svg>',
  
  'user' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="6.5" r="3" stroke="currentColor" stroke-width="2"/><path d="M3.5 16c0-3.5 3-5.5 6.5-5.5s6.5 2 6.5 5.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
  
  'clock' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="7" stroke="currentColor" stroke-width="2"/><path d="M10 6v4.5l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
  
  'analytics' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M3 15l5-5 3 3 6-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="17" cy="6" r="2" stroke="currentColor" stroke-width="2"/></svg>',
  
  'flag' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M5 3v14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M5 5h10l-3 4 3 4H5" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>',
  
  'settings' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="2.5" stroke="currentColor" stroke-width="2"/><path d="M10 3v1.5M10 15.5V17M3 10h1.5M15.5 10H17M5.5 5.5l1 1M13.5 13.5l1 1M5.5 14.5l1-1M13.5 6.5l1-1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
  
  'users' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><circle cx="7.5" cy="6.5" r="2.5" stroke="currentColor" stroke-width="2"/><path d="M2.5 16c0-2.8 2.5-4.5 5-4.5s5 1.7 5 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="15" cy="6" r="2" stroke="currentColor" stroke-width="2"/><path d="M17.5 15.5c0-1.8-1.5-3-3.5-3.2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
  
  'logout' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M7.5 4H5.5A1.5 1.5 0 004 5.5v9A1.5 1.5 0 005.5 16h2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12.5 12.5L16 9l-3.5-3.5M16 9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  
  'dots' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="4" r="1.5" fill="currentColor"/><circle cx="10" cy="10" r="1.5" fill="currentColor"/><circle cx="10" cy="16" r="1.5" fill="currentColor"/></svg>',
];
?>

<style>
  :root {
    --bg:           #f8f6f2;
    --surface:      #ffffff;
    --surface2:     #f1efea;
    --border:       rgba(0,0,0,0.06);
    --border-md:    rgba(0,0,0,0.1);
    --text-primary: #1e1e2a;
    --text-secondary:#6c6c7a;
    --text-muted:   #a3a3b0;
    --blue-50:      #eef3fc;
    --blue-600:     #1c6fb0;
    --blue-800:     #0e4a7a;
    --purple-50:    #f1effe;
    --purple-600:   #6b5fd9;
    --purple-800:   #4a3faa;
    --red-50:       #fef0f0;
    --red-700:      #b13e3e;
    --green-50:     #edf7e6;
    --green-800:    #3a6b1f;
    --sidebar-w:    260px;
  }

  .cc-layout { display: flex; height: 100vh; overflow: hidden; }
  .cc-main { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: var(--bg); }

  .sidebar {
    width: var(--sidebar-w);
    height: 100vh;
    background: var(--surface);
    border-right: 1px solid var(--border-md);
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    box-shadow: 2px 0 12px rgba(0,0,0,0.02);
  }

  .sidebar-logo { padding: 24px 20px 20px; border-bottom: 1px solid var(--border); }
  .logo-wordmark { font-family: 'Playfair Display', Georgia, serif; font-size: 20px; font-weight: 600; color: var(--text-primary); letter-spacing: -0.3px; }
  .logo-wordmark em { font-style: italic; color: var(--blue-600); }
  .logo-tagline { font-size: 11px; color: var(--text-muted); margin-top: 6px; letter-spacing: 0.3px; }

  .sidebar-nav { flex: 1; padding: 12px 12px; overflow-y: auto; }

  .nav-section-label { font-size: 10px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-muted); padding: 12px 12px 6px; display: block; }

  .nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 12px;
    cursor: pointer;
    margin-bottom: 4px;
    transition: all 0.2s ease;
    text-decoration: none;
    color: var(--text-secondary);
    position: relative;
  }
  .nav-item:hover { background: var(--surface2); color: var(--text-primary); text-decoration: none; }
  .nav-item.active {
    background: linear-gradient(135deg, var(--blue-50) 0%, #ffffff 100%);
    color: var(--blue-800);
    border-left: 3px solid var(--blue-600);
    box-shadow: 0 2px 6px rgba(28,111,176,0.08);
  }
  .nav-item.active .nav-label { font-weight: 600; }
  .nav-item.active-admin {
    background: linear-gradient(135deg, var(--purple-50) 0%, #ffffff 100%);
    color: var(--purple-800);
    border-left: 3px solid var(--purple-600);
    box-shadow: 0 2px 6px rgba(107,95,217,0.08);
  }
  .nav-item.active-admin .nav-label { font-weight: 600; }

  .nav-icon { width: 22px; height: 22px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
  .nav-icon svg { stroke: currentColor; }
  
  .nav-label { font-size: 13.5px; flex: 1; font-weight: 500; letter-spacing: -0.2px; }
  .nav-badge { font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 30px; flex-shrink: 0; }
  .badge-red   { background: var(--red-50);   color: var(--red-700); }
  .badge-green { background: var(--green-50); color: var(--green-800); }

  .sidebar-footer { border-top: 1px solid var(--border); padding: 16px 12px; position: relative; background: var(--surface); }

  .user-chip {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 10px;
    border-radius: 14px;
    transition: background 0.2s;
  }
  .user-chip:hover { background: var(--surface2); }

  .avatar {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-weight: 500;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  }
  .avatar-emoji {
    font-size: 20px;
    background: var(--blue-50);
    border: 1px solid rgba(28,111,176,0.2);
  }
  .avatar-initials { font-size: 13px; font-weight: 600; }
  .avatar-user  { background: var(--blue-50);   color: var(--blue-800); }
  .avatar-admin { background: var(--purple-50); color: var(--purple-800); }

  .user-info { flex: 1; min-width: 0; }
  .user-name { font-size: 13px; font-weight: 600; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .user-role { font-size: 11px; color: var(--text-muted); }

  .dots-btn {
    width: 30px; height: 30px; border-radius: 10px;
    border: 1px solid transparent;
    background: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    transition: all 0.2s;
    padding: 0;
  }
  .dots-btn:hover, .dots-btn.open { background: var(--surface2); border-color: var(--border-md); color: var(--text-primary); }

  .user-menu {
    position: absolute;
    bottom: calc(100% + 8px);
    left: 12px;
    right: 12px;
    background: var(--surface);
    border: 1px solid var(--border-md);
    border-radius: 16px;
    padding: 6px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    display: none;
    z-index: 100;
  }
  .user-menu.open { display: block; }

  .menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 12px;
    font-size: 13px;
    color: var(--text-secondary);
    text-decoration: none;
    transition: all 0.15s;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
    font-family: inherit;
  }
  .menu-item:hover { background: var(--surface2); color: var(--text-primary); }
  .menu-item.danger { color: var(--red-700); }
  .menu-item.danger:hover { background: var(--red-50); color: var(--red-700); }
  .menu-item.danger .nav-icon svg { stroke: var(--red-700); }
  .menu-divider { height: 1px; background: var(--border); margin: 6px 0; }

  .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; z-index: 999; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
  .modal-overlay.open { opacity: 1; pointer-events: all; }
  .modal-box { background: var(--surface); border-radius: 24px; padding: 28px 24px 20px; width: 320px; max-width: calc(100vw - 32px); box-shadow: 0 20px 35px rgba(0,0,0,0.2); transform: translateY(8px); transition: transform 0.2s; }
  .modal-overlay.open .modal-box { transform: translateY(0); }
  .modal-icon-wrap { width: 48px; height: 48px; border-radius: 50%; background: var(--red-50); display: flex; align-items: center; justify-content: center; margin-bottom: 18px; color: var(--red-700); }
  .modal-title { font-size: 18px; font-weight: 600; color: var(--text-primary); margin-bottom: 8px; }
  .modal-desc { font-size: 13px; color: var(--text-secondary); line-height: 1.5; margin-bottom: 24px; }
  .modal-actions { display: flex; gap: 10px; justify-content: flex-end; }
  .btn { padding: 8px 20px; border-radius: 40px; font-size: 13px; font-family: inherit; font-weight: 500; cursor: pointer; border: 1px solid var(--border-md); transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
  .btn-cancel { background: none; color: var(--text-secondary); }
  .btn-cancel:hover { background: var(--surface2); color: var(--text-primary); border-color: var(--border-md); }
  .btn-logout { background: var(--red-700); border-color: var(--red-700); color: white; }
  .btn-logout:hover { background: #8f2e2e; border-color: #8f2e2e; }
</style>

<aside class="sidebar" id="cc-sidebar">

  <div class="sidebar-logo">
    <div class="logo-wordmark">Contra<span style="color:#1c6fb0;">Choice</span></div>
    <div class="logo-tagline"><?= $is_admin ? 'Admin Panel' : "Women's Health Companion" ?></div>
  </div>

  <nav class="sidebar-nav">
    <?php foreach ($nav_items as $item): ?>
      <?php if (isset($item['section'])): ?>
        <span class="nav-section-label"><?= htmlspecialchars($item['section']) ?></span>
      <?php else:
        $active_cls = ($active_page === $item['id']) ? ($is_admin ? 'active-admin' : 'active') : '';
        $url = $is_admin ? '/hci/admin/' . $item['id'] . '.php' : '/hci/user/' . $item['id'] . '.php';
        $icon_color = $item['color'] ?? '#6c6c7a';
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
        <span class="nav-icon" style="color: var(--red-700);"><?= $icons['logout'] ?></span> Logout
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