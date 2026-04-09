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
$initials = strtoupper(substr($user_name, 0, 2));

$user_nav = [
  ['section' => 'Main'],
  ['id' => 'dashboard',       'label' => 'Dashboard',          'icon' => 'grid'],
  ['id' => 'questionnaire',   'label' => 'Questionnaire',      'icon' => 'list-search'],
  ['id' => 'comparison',      'label' => 'Comparison guide',   'icon' => 'compare'],
  ['id' => 'recommendations', 'label' => 'My recommendations', 'icon' => 'star'],
  ['section' => 'Community'],
  ['id' => 'forum',           'label' => 'Anonymous forum',    'icon' => 'chat'],
  ['id' => 'chatbot',         'label' => 'AI chatbot',         'icon' => 'bot'],
  ['section' => 'Account'],
  ['id' => 'profile',         'label' => 'My profile',         'icon' => 'user'],
  ['id' => 'history',         'label' => 'History',            'icon' => 'clock'],
];

$admin_nav = [
  ['section' => 'Overview'],
  ['id' => 'dashboard',     'label' => 'Dashboard',           'icon' => 'grid'],
  ['id' => 'analytics',     'label' => 'Analytics',           'icon' => 'analytics'],
  ['section' => 'Content'],
  ['id' => 'methods',       'label' => 'Manage methods',      'icon' => 'compare'],
  ['id' => 'qstats',        'label' => 'Questionnaire stats', 'icon' => 'list-search'],
  ['id' => 'chatresponses', 'label' => 'Chatbot responses',   'icon' => 'bot'],
  ['section' => 'Moderation'],
  ['id' => 'forum-mod',     'label' => 'Forum moderation',    'icon' => 'chat',  'badge' => '5', 'badge_type' => 'red'],
  ['id' => 'reports',       'label' => 'Reported posts',      'icon' => 'flag',  'badge' => '2', 'badge_type' => 'red'],
  ['section' => 'System'],
  ['id' => 'settings',      'label' => 'Settings',            'icon' => 'settings'],
  ['id' => 'accounts',      'label' => 'User accounts',       'icon' => 'users'],
];

$nav_items = $is_admin ? $admin_nav : $user_nav;

$icons = [
  'grid'        => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.2"/><rect x="9" y="2" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.2"/><rect x="2" y="9" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.2"/><rect x="9" y="9" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.2"/></svg>',
  'list-search' => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M2 4h12M2 8h8M2 12h5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/><circle cx="13" cy="12" r="2" stroke="currentColor" stroke-width="1.1"/></svg>',
  'compare'     => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1.5" y="3" width="5.5" height="10" rx="1" stroke="currentColor" stroke-width="1.2"/><rect x="9" y="3" width="5.5" height="10" rx="1" stroke="currentColor" stroke-width="1.2"/><path d="M7 8h2" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>',
  'star'        => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 2l1.6 3.2L13 6l-2.5 2.4.6 3.6L8 10.2 4.9 12l.6-3.6L3 6l3.4-.8L8 2z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/></svg>',
  'chat'        => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M2 3h12v8H8l-3 3v-3H2V3z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/></svg>',
  'bot'         => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="2" y="4" width="12" height="8" rx="1.5" stroke="currentColor" stroke-width="1.2"/><path d="M5 13l1.5-1.5h3L11 13" stroke="currentColor" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round"/><circle cx="6" cy="8" r="1" fill="currentColor"/><circle cx="10" cy="8" r="1" fill="currentColor"/></svg>',
  'user'        => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="5.5" r="2.5" stroke="currentColor" stroke-width="1.2"/><path d="M2.5 13.5c0-2.5 2.5-4 5.5-4s5.5 1.5 5.5 4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>',
  'clock'       => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.2"/><path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>',
  'analytics'   => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M2 13l3.5-4 3 2.5L12 5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="5" r="1.5" stroke="currentColor" stroke-width="1.1"/></svg>',
  'flag'        => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M4 2v12" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/><path d="M4 3h8l-2 3.5 2 3.5H4" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/></svg>',
  'settings'    => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="2" stroke="currentColor" stroke-width="1.2"/><path d="M8 2v1M8 13v1M2 8h1M13 8h1M3.5 3.5l.7.7M11.8 11.8l.7.7M3.5 12.5l.7-.7M11.8 4.2l.7-.7" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>',
  'users'       => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="6" cy="5.5" r="2" stroke="currentColor" stroke-width="1.2"/><path d="M1.5 13c0-2 2-3.5 4.5-3.5S10.5 11 10.5 13" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/><circle cx="11.5" cy="5" r="1.8" stroke="currentColor" stroke-width="1.1"/><path d="M13.5 12.5c0-1.8-1-3-2.5-3.2" stroke="currentColor" stroke-width="1.1" stroke-linecap="round"/></svg>',
  'logout'      => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 3H3.5A1.5 1.5 0 002 4.5v7A1.5 1.5 0 003.5 13H6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/><path d="M10 11l3-3-3-3M13 8H6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  'dots'        => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="3.5" r="1.2" fill="currentColor"/><circle cx="8" cy="8" r="1.2" fill="currentColor"/><circle cx="8" cy="12.5" r="1.2" fill="currentColor"/></svg>',
];
?>

<style>
  :root {
    --bg:           #f7f6f3;
    --surface:      #ffffff;
    --surface2:     #f1f0ec;
    --border:       rgba(0,0,0,0.08);
    --border-md:    rgba(0,0,0,0.14);
    --text-primary: #1a1a18;
    --text-secondary:#6b6b67;
    --text-muted:   #a0a09b;
    --blue-50:      #e6f1fb;
    --blue-600:     #185FA5;
    --blue-800:     #0C447C;
    --purple-50:    #eeedfe;
    --purple-600:   #534AB7;
    --purple-800:   #3C3489;
    --red-50:       #fcebeb;
    --red-700:      #791F1F;
    --green-50:     #eaf3de;
    --green-800:    #27500A;
    --sidebar-w:    240px;
  }

  .cc-layout { display: flex; height: 100vh; overflow: hidden; }
  .cc-main { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: var(--bg); }

  .sidebar {
    width: var(--sidebar-w);
    height: 100vh;
    background: var(--surface);
    border-right: 0.5px solid var(--border-md);
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    animation: sbFadeIn 0.25s ease both;
  }

  .sidebar-logo { padding: 20px 18px 16px; border-bottom: 0.5px solid var(--border); }
  .logo-wordmark { font-family: 'Playfair Display', Georgia, serif; font-size: 18px; color: var(--text-primary); letter-spacing: -0.3px; line-height: 1; }
  .logo-wordmark em { font-style: italic; color: var(--blue-600); }
  .logo-tagline { font-size: 11px; color: var(--text-muted); margin-top: 4px; letter-spacing: 0.02em; }

  .sidebar-nav { flex: 1; padding: 10px; overflow-y: auto; }

  .nav-section-label { font-size: 10px; font-weight: 500; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-muted); padding: 6px 8px 4px; display: block; margin-top: 6px; }

  .nav-item { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 8px; cursor: pointer; margin-bottom: 1px; transition: background 0.12s; text-decoration: none; color: var(--text-secondary); }
  .nav-item:hover { background: var(--surface2); color: var(--text-primary); text-decoration: none; }
  .nav-item.active { background: var(--blue-50); color: var(--blue-800); }
  .nav-item.active .nav-label { font-weight: 500; }
  .nav-item.active .nav-icon svg path,
  .nav-item.active .nav-icon svg rect,
  .nav-item.active .nav-icon svg circle { stroke: var(--blue-600); }
  .nav-item.active-admin { background: var(--purple-50); color: var(--purple-800); }
  .nav-item.active-admin .nav-label { font-weight: 500; }
  .nav-item.active-admin .nav-icon svg path,
  .nav-item.active-admin .nav-icon svg rect,
  .nav-item.active-admin .nav-icon svg circle { stroke: var(--purple-600); }

  .nav-icon { width: 16px; height: 16px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
  .nav-label { font-size: 13px; flex: 1; line-height: 1; }
  .nav-badge { font-size: 10px; font-weight: 500; padding: 2px 6px; border-radius: 20px; flex-shrink: 0; }
  .badge-red   { background: var(--red-50);   color: var(--red-700); }
  .badge-green { background: var(--green-50); color: var(--green-800); }

  .sidebar-footer { border-top: 0.5px solid var(--border); padding: 12px 10px; position: relative; }

  .user-chip { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 8px; transition: background 0.12s; }
  .user-chip:hover { background: var(--surface2); }

  .avatar { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 500; flex-shrink: 0; }
  .avatar-user  { background: var(--blue-50);   color: var(--blue-800); }
  .avatar-admin { background: var(--purple-50); color: var(--purple-800); }

  .user-info { flex: 1; min-width: 0; }
  .user-name { font-size: 12px; font-weight: 500; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .user-role { font-size: 11px; color: var(--text-muted); }

  .dots-btn { width: 26px; height: 26px; border-radius: 6px; border: 1px solid transparent; background: none; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--text-muted); flex-shrink: 0; transition: background 0.12s, border-color 0.12s, color 0.12s; padding: 0; }
  .dots-btn:hover, .dots-btn.open { background: var(--surface2); border-color: var(--border-md); color: var(--text-primary); }

  .user-menu { position: absolute; bottom: calc(100% + 4px); left: 8px; right: 8px; background: var(--surface); border: 0.5px solid var(--border-md); border-radius: 10px; padding: 5px; box-shadow: 0 4px 18px rgba(0,0,0,0.10); display: none; z-index: 100; }
  .user-menu.open { display: block; }

  .menu-item { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 7px; font-size: 13px; color: var(--text-secondary); text-decoration: none; transition: background 0.1s, color 0.1s; border: none; background: none; width: 100%; text-align: left; cursor: pointer; font-family: inherit; }
  .menu-item:hover { background: var(--surface2); color: var(--text-primary); text-decoration: none; }
  .menu-item .nav-icon { opacity: 0.7; }
  .menu-item:hover .nav-icon { opacity: 1; }
  .menu-item.danger { color: #A32D2D; }
  .menu-item.danger:hover { background: var(--red-50); color: var(--red-700); }
  .menu-item.danger .nav-icon { opacity: 1; }
  .menu-divider { height: 0.5px; background: var(--border); margin: 4px 0; }

  .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.28); display: flex; align-items: center; justify-content: center; z-index: 999; opacity: 0; pointer-events: none; transition: opacity 0.18s; }
  .modal-overlay.open { opacity: 1; pointer-events: all; }
  .modal-box { background: var(--surface); border-radius: 14px; padding: 28px 24px 20px; width: 310px; max-width: calc(100vw - 32px); box-shadow: 0 8px 32px rgba(0,0,0,0.13); transform: translateY(8px); transition: transform 0.18s; }
  .modal-overlay.open .modal-box { transform: translateY(0); }
  .modal-icon-wrap { width: 44px; height: 44px; border-radius: 50%; background: var(--red-50); display: flex; align-items: center; justify-content: center; margin-bottom: 14px; color: #A32D2D; }
  .modal-title { font-size: 15px; font-weight: 600; color: var(--text-primary); margin-bottom: 6px; }
  .modal-desc { font-size: 13px; color: var(--text-secondary); line-height: 1.6; margin-bottom: 20px; }
  .modal-actions { display: flex; gap: 8px; justify-content: flex-end; }
  .btn { padding: 8px 18px; border-radius: 8px; font-size: 13px; font-family: inherit; font-weight: 500; cursor: pointer; border: 0.5px solid var(--border-md); transition: background 0.12s; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
  .btn-cancel { background: none; color: var(--text-secondary); }
  .btn-cancel:hover { background: var(--surface2); color: var(--text-primary); }
  .btn-logout { background: #A32D2D; border-color: #A32D2D; color: #fff; }
  .btn-logout:hover { background: var(--red-700); border-color: var(--red-700); color: #fff; }

  @keyframes sbFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
</style>

<aside class="sidebar" id="cc-sidebar">

  <div class="sidebar-logo">
    <div class="logo-wordmark">ContraChoice</div>
    <div class="logo-tagline"><?= $is_admin ? 'Admin Panel' : "Women's Health Information" ?></div>
  </div>

  <nav class="sidebar-nav">
    <?php foreach ($nav_items as $item): ?>
      <?php if (isset($item['section'])): ?>
        <span class="nav-section-label"><?= htmlspecialchars($item['section']) ?></span>
      <?php else:
        $active_cls = ($active_page === $item['id']) ? ($is_admin ? 'active-admin' : 'active') : '';
        $url = $is_admin ? '/hci/admin/' . $item['id'] . '.php' : '/hci/user/' . $item['id'] . '.php';
      ?>
        <a href="<?= htmlspecialchars($url) ?>" class="nav-item <?= $active_cls ?>">
          <span class="nav-icon"><?= $icons[$item['icon']] ?? '' ?></span>
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
      <div class="avatar avatar-<?= $is_admin ? 'admin' : 'user' ?>">
        <?= htmlspecialchars($initials) ?>
      </div>
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
        <span class="nav-icon" style="color:#A32D2D;"><?= $icons['logout'] ?></span> Logout
      </button>
    </div>
  </div>
</aside>

<div class="modal-overlay" id="cc-logout-modal">
  <div class="modal-box">
    <div class="modal-icon-wrap"><?= $icons['logout'] ?></div>
    <div class="modal-title">Log out?</div>
    <p class="modal-desc">Your current session will end. You'll need to log in again to access your account.</p>
    <div class="modal-actions">
      <button class="btn btn-cancel" type="button" onclick="ccCloseLogout()">Cancel</button>
      <a class="btn btn-logout" href="/hci/user/login.php"><?= $icons['logout'] ?> Log out</a>
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