<?php
session_start();
include '../includes/db_connection.php';
include '../includes/user/auth.php';

$page_title  = 'Dashboard';
$active_page = 'dashboard';
$is_admin    = false;
$user_id     = $_SESSION['user_id'] ?? 0;
$user_name   = $_SESSION['username'] ?? 'User';
$user_avatar = $_SESSION['avatar'] ?? null;
$hour        = (int) date('G');
$greeting    = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

// ── 1. Questionnaire status ──────────────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT response_id, submitted_at
    FROM questionnaire_responses
    WHERE user_id = ?
    ORDER BY submitted_at DESC
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$q_row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$has_questionnaire = !empty($q_row);
$q_date = $has_questionnaire ? date('M d, Y', strtotime($q_row['submitted_at'])) : null;
$latest_response_id = $q_row['response_id'] ?? null;

// ── 2. Total questionnaire submissions ──────────────────────────────────────
$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM questionnaire_responses WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_q = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
$stmt->close();

// ── 3. Top recommendation ────────────────────────────────────────────────────
$top_method = null;
if ($latest_response_id) {
    $stmt = $conn->prepare("
        SELECT cm.name, cm.category, cm.effectiveness, cm.description, r.score
        FROM recommendations r
        JOIN contraceptive_methods cm ON cm.method_id = r.method_id
        WHERE r.response_id = ? AND r.user_id = ?
        ORDER BY r.rank ASC
        LIMIT 1
    ");
    $stmt->bind_param("ii", $latest_response_id, $user_id);
    $stmt->execute();
    $top_method = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// ── 4. Total recommendations received ───────────────────────────────────────
$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM recommendations WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_rec = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
$stmt->close();

// ── 5. Forum stats ───────────────────────────────────────────────────────────
$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM forum_posts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$my_posts = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM forum_replies WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$my_replies = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
$stmt->close();

// ── 6. Latest forum posts (community) ───────────────────────────────────────
$recent_posts = [];
$res = $conn->query("
    SELECT post_id, content, created_at, reply_count
    FROM forum_posts
    ORDER BY created_at DESC
    LIMIT 4
");
while ($row = $res->fetch_assoc()) $recent_posts[] = $row;

// ── 7. Category label helper ─────────────────────────────────────────────────
function cat_label($cat) {
    return match($cat) {
        'hormonal'  => 'Hormonal',
        'barrier'   => 'Barrier',
        'long_term' => 'Long-term',
        'natural'   => 'Natural',
        'emergency' => 'Emergency',
        default     => ucfirst($cat),
    };
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
      --green-50: #eaf3de;
      --green-800: #27500a;
      --amber-50: #faeeda;
      --amber-800: #633806;
      --teal-50: #e1f5ee;
      --teal-800: #085041;
      --coral-50: #faece7;
      --coral-800: #712b13;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { background: var(--bg); font-family: 'Outfit', sans-serif; color: var(--text); }

    .layout { display: flex; height: 100vh; overflow: hidden; }
    .main   { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }

    /* topbar */
    .topbar {
      height: 52px; background: var(--surface);
      border-bottom: 0.5px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 28px; flex-shrink: 0;
      font-size: 13px; color: var(--muted);
    }
    .topbar b { color: var(--text); font-weight: 500; }
    .topbar-date { font-size: 12px; color: var(--muted); }

    /* scroll area */
    .page-body { flex: 1; overflow-y: auto; padding: 28px 28px 40px; }
    .page-body::-webkit-scrollbar { width: 4px; }
    .page-body::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

    /* ── WELCOME BANNER ─────────────────────────── */
    .welcome-banner {
      background: var(--surface);
      border: 0.5px solid var(--border);
      border-radius: 20px;
      padding: 24px 28px;
      display: flex; align-items: center; gap: 18px;
      margin-bottom: 24px;
      animation: fadeUp .3s ease both;
    }

    .welcome-avatar {
      width: 56px; height: 56px; border-radius: 50%;
      background: var(--blue-50);
      border: 2px solid var(--blue-100);
      display: flex; align-items: center; justify-content: center;
      font-size: 28px; flex-shrink: 0;
    }
    .welcome-avatar.initials {
      font-size: 18px; font-weight: 500; color: var(--blue-800);
    }

    .welcome-text h1 {
      font-family: 'Playfair Display', serif;
      font-size: 20px; font-weight: 500; margin-bottom: 3px;
    }
    .welcome-text p { font-size: 13px; color: var(--muted); }

    .welcome-action {
      margin-left: auto; flex-shrink: 0;
    }

    .btn-primary {
      background: var(--blue-600); color: #fff;
      border: none; border-radius: 12px;
      padding: 10px 20px; font-family: 'Outfit', sans-serif;
      font-size: 13px; font-weight: 500; cursor: pointer;
      display: inline-flex; align-items: center; gap: 8px;
      text-decoration: none;
      transition: background .18s, transform .1s;
    }
    .btn-primary:hover { background: var(--blue-800); color: #fff; text-decoration: none; }
    .btn-primary:active { transform: scale(.97); }

    .btn-outline {
      background: transparent; color: var(--blue-600);
      border: 0.5px solid var(--blue-100); border-radius: 12px;
      padding: 9px 18px; font-family: 'Outfit', sans-serif;
      font-size: 13px; font-weight: 500; cursor: pointer;
      display: inline-flex; align-items: center; gap: 7px;
      text-decoration: none;
      transition: background .15s, border-color .15s;
    }
    .btn-outline:hover { background: var(--blue-50); border-color: var(--blue-600); color: var(--blue-800); text-decoration: none; }

    /* ── STAT CARDS ─────────────────────────────── */
    .stat-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 14px;
      margin-bottom: 24px;
    }

    .stat-card {
      background: var(--surface);
      border: 0.5px solid var(--border);
      border-radius: 16px;
      padding: 18px 20px;
      display: flex; flex-direction: column; gap: 10px;
      animation: fadeUp .3s ease both;
      transition: box-shadow .18s;
    }
    .stat-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.06); }

    .stat-icon {
      width: 36px; height: 36px; border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 16px; flex-shrink: 0;
    }
    .icon-blue   { background: var(--blue-50);  color: var(--blue-600); }
    .icon-green  { background: var(--green-50);  color: var(--green-800); }
    .icon-amber  { background: var(--amber-50);  color: var(--amber-800); }
    .icon-teal   { background: var(--teal-50);   color: var(--teal-800); }
    .icon-coral  { background: var(--coral-50);  color: var(--coral-800); }

    .stat-value { font-size: 26px; font-weight: 500; color: var(--text); line-height: 1; }
    .stat-label { font-size: 12px; color: var(--muted); }

    /* ── MAIN GRID ──────────────────────────────── */
    .main-grid {
      display: grid;
      grid-template-columns: 1fr 340px;
      gap: 20px;
    }

    /* ── QUICK ACTIONS ──────────────────────────── */
    .section-title {
      font-family: 'Playfair Display', serif;
      font-size: 16px; font-weight: 500;
      margin-bottom: 14px; color: var(--text);
    }

    .actions-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-bottom: 24px;
    }

    .action-card {
      background: var(--surface);
      border: 0.5px solid var(--border);
      border-radius: 16px;
      padding: 18px 20px;
      display: flex; align-items: center; gap: 14px;
      text-decoration: none; color: var(--text);
      transition: box-shadow .18s, border-color .18s, transform .12s;
      animation: fadeUp .3s ease both;
    }
    .action-card:hover {
      box-shadow: 0 4px 14px rgba(0,0,0,.07);
      border-color: var(--blue-100);
      transform: translateY(-1px);
      text-decoration: none; color: var(--text);
    }

    .action-icon {
      width: 40px; height: 40px; border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px; flex-shrink: 0;
    }

    .action-text h4 { font-size: 13px; font-weight: 500; margin-bottom: 2px; }
    .action-text p  { font-size: 12px; color: var(--muted); }

    /* ── TOP RECOMMENDATION ─────────────────────── */
    .rec-card {
      background: var(--surface);
      border: 0.5px solid var(--border);
      border-radius: 16px;
      padding: 20px;
      margin-bottom: 20px;
      animation: fadeUp .35s ease both;
    }

    .rec-header {
      display: flex; align-items: flex-start;
      justify-content: space-between; gap: 10px;
      margin-bottom: 10px;
    }

    .rec-badge {
      font-size: 11px; padding: 3px 10px;
      border-radius: 30px; font-weight: 500; flex-shrink: 0;
    }
    .badge-hormonal  { background: var(--blue-50);  color: var(--blue-800); }
    .badge-barrier   { background: var(--teal-50);  color: var(--teal-800); }
    .badge-long_term { background: var(--green-50); color: var(--green-800); }
    .badge-natural   { background: var(--amber-50); color: var(--amber-800); }
    .badge-emergency { background: var(--coral-50); color: var(--coral-800); }

    .rec-name { font-family: 'Playfair Display', serif; font-size: 16px; font-weight: 500; margin-bottom: 6px; }
    .rec-desc { font-size: 13px; color: var(--muted); line-height: 1.6; margin-bottom: 14px;
      display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

    .rec-meta { display: flex; gap: 16px; }
    .rec-stat { display: flex; flex-direction: column; gap: 2px; }
    .rec-stat-val { font-size: 15px; font-weight: 500; color: var(--text); }
    .rec-stat-lbl { font-size: 11px; color: var(--muted); }

    .score-bar { height: 4px; border-radius: 4px; background: var(--border); margin-top: 10px; overflow: hidden; }
    .score-fill { height: 100%; border-radius: 4px; background: var(--blue-600); transition: width .6s ease; }

    /* no rec state */
    .no-rec {
      background: var(--surface); border: 0.5px dashed var(--border);
      border-radius: 16px; padding: 32px 20px;
      text-align: center; color: var(--muted); font-size: 13px;
      margin-bottom: 20px;
    }
    .no-rec i { font-size: 28px; display: block; margin-bottom: 10px; color: var(--border); }

    /* ── FORUM FEED ──────────────────────────────── */
    .forum-card {
      background: var(--surface);
      border: 0.5px solid var(--border);
      border-radius: 16px;
      overflow: hidden;
      animation: fadeUp .4s ease both;
    }

    .forum-card-head {
      padding: 16px 18px;
      border-bottom: 0.5px solid var(--border);
      display: flex; justify-content: space-between; align-items: center;
    }

    .forum-item {
      padding: 14px 18px;
      border-bottom: 0.5px solid var(--border);
      transition: background .12s;
      text-decoration: none; display: block; color: var(--text);
    }
    .forum-item:last-child { border-bottom: none; }
    .forum-item:hover { background: var(--bg); text-decoration: none; color: var(--text); }

    .forum-item-text {
      font-size: 13px; line-height: 1.5; margin-bottom: 6px;
      display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }

    .forum-item-meta {
      display: flex; gap: 12px;
      font-size: 11px; color: var(--muted); align-items: center;
    }

    .anon-badge {
      background: #eef2f0; padding: 1px 8px;
      border-radius: 30px; font-size: 10px;
      display: inline-flex; align-items: center; gap: 3px;
    }

    /* ── RIGHT COLUMN ───────────────────────────── */
    .right-col { display: flex; flex-direction: column; gap: 18px; }

    .side-card {
      background: var(--surface);
      border: 0.5px solid var(--border);
      border-radius: 16px;
      overflow: hidden;
      animation: fadeUp .35s ease both;
    }

    .side-card-head {
      padding: 14px 18px;
      border-bottom: 0.5px solid var(--border);
      font-size: 13px; font-weight: 500;
      display: flex; justify-content: space-between; align-items: center;
    }

    .side-card-body { padding: 16px 18px; }

    .activity-item {
      display: flex; gap: 10px; align-items: flex-start;
      padding: 8px 0;
      border-bottom: 0.5px solid var(--border);
      font-size: 12px;
    }
    .activity-item:last-child { border-bottom: none; padding-bottom: 0; }
    .activity-dot {
      width: 8px; height: 8px; border-radius: 50%;
      flex-shrink: 0; margin-top: 4px;
    }
    .dot-blue  { background: var(--blue-600); }
    .dot-green { background: #3b6d11; }
    .dot-amber { background: #854f0b; }
    .activity-text { color: var(--text); line-height: 1.5; }
    .activity-time { color: var(--muted); font-size: 11px; margin-top: 1px; }

    /* link style */
    .link-sm {
      font-size: 12px; color: var(--blue-600);
      text-decoration: none;
    }
    .link-sm:hover { text-decoration: underline; color: var(--blue-800); }

    /* ── ANIMATIONS ─────────────────────────────── */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(10px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .stat-card:nth-child(1) { animation-delay: .05s; }
    .stat-card:nth-child(2) { animation-delay: .10s; }
    .stat-card:nth-child(3) { animation-delay: .15s; }
    .stat-card:nth-child(4) { animation-delay: .20s; }

    @media (max-width: 1100px) {
      .main-grid { grid-template-columns: 1fr; }
      .stat-grid { grid-template-columns: repeat(2, 1fr); }
    }
  </style>
</head>
<body>
<div class="layout">
  <?php include '../includes/user/sidebar.php'; ?>

  <div class="main">
    <div class="topbar">
      <div class="topbar-left">
        <span class="topbar-title">ContraChoice</span>
        <span class="topbar-sep">›</span>
        <span class="topbar-page"><?= htmlspecialchars($page_title) ?></span>
      </div>
    </div>

    <div class="page-body">

      <!-- ── WELCOME BANNER -->
      <div class="welcome-banner">
        <div class="welcome-avatar <?= $user_avatar ? '' : 'initials' ?>">
          <?= $user_avatar ? htmlspecialchars($user_avatar) : strtoupper(substr($user_name, 0, 2)) ?>
        </div>
        <div class="welcome-text">
          <h1><?= $greeting ?>, <?= htmlspecialchars($user_name) ?>!</h1>
          <p>
            <?php if ($has_questionnaire): ?>
              Your last assessment was on <?= $q_date ?>. You have <?= $total_rec ?> recommendation<?= $total_rec != 1 ? 's' : '' ?> waiting.
            <?php else: ?>
              Welcome to ContraChoice. Take the questionnaire to get your personalized recommendations.
            <?php endif; ?>
          </p>
        </div>
        <div class="welcome-action">
          <?php if ($has_questionnaire): ?>
            <a href="/hci/user/recommendations.php" class="btn-primary">
              <i class="fas fa-star"></i> View recommendations
            </a>
          <?php else: ?>
            <a href="/hci/user/questionnaire.php" class="btn-primary">
              <i class="fas fa-clipboard-list"></i> Take questionnaire
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- ── STAT CARDS -->
      <div class="stat-grid">
        <div class="stat-card">
          <div class="stat-icon icon-blue"><i class="fas fa-clipboard-list"></i></div>
          <div>
            <div class="stat-value"><?= $total_q ?></div>
            <div class="stat-label">Questionnaire<?= $total_q != 1 ? 's' : '' ?> submitted</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon icon-green"><i class="fas fa-star"></i></div>
          <div>
            <div class="stat-value"><?= $total_rec ?></div>
            <div class="stat-label">Recommendation<?= $total_rec != 1 ? 's' : '' ?> received</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon icon-amber"><i class="fas fa-comments"></i></div>
          <div>
            <div class="stat-value"><?= $my_posts ?></div>
            <div class="stat-label">Forum post<?= $my_posts != 1 ? 's' : '' ?> made</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon icon-teal"><i class="fas fa-reply"></i></div>
          <div>
            <div class="stat-value"><?= $my_replies ?></div>
            <div class="stat-label">Forum repl<?= $my_replies != 1 ? 'ies' : 'y' ?> made</div>
          </div>
        </div>
      </div>

      <!-- ── MAIN GRID -->
      <div class="main-grid">

        <!-- LEFT -->
        <div>

          <!-- Quick actions -->
          <div class="section-title">Quick actions</div>
          <div class="actions-grid">
            <a href="/hci/user/questionnaire.php" class="action-card">
              <div class="action-icon icon-blue"><i class="fas fa-clipboard-list"></i></div>
              <div class="action-text">
                <h4><?= $has_questionnaire ? 'Retake questionnaire' : 'Take questionnaire' ?></h4>
                <p>Get personalized method recommendations</p>
              </div>
            </a>
            <a href="/hci/user/recommendations.php" class="action-card">
              <div class="action-icon icon-green"><i class="fas fa-star"></i></div>
              <div class="action-text">
                <h4>My recommendations</h4>
                <p><?= $total_rec > 0 ? $total_rec . ' method' . ($total_rec != 1 ? 's' : '') . ' suggested for you' : 'No recommendations yet' ?></p>
              </div>
            </a>
            <a href="/hci/user/forum.php" class="action-card">
              <div class="action-icon icon-amber"><i class="fas fa-comments"></i></div>
              <div class="action-text">
                <h4>Anonymous forum</h4>
                <p>Ask questions, share experiences</p>
              </div>
            </a>
            <a href="/hci/user/chatbot.php" class="action-card">
              <div class="action-icon icon-teal"><i class="fas fa-robot"></i></div>
              <div class="action-text">
                <h4>Ask the AI</h4>
                <p>Get answers about contraceptives 24/7</p>
              </div>
            </a>
          </div>

          <!-- Top recommendation -->
          <div class="section-title">Your top recommendation</div>
          <?php if ($top_method): ?>
            <div class="rec-card">
              <div class="rec-header">
                <div>
                  <div class="rec-name"><?= htmlspecialchars($top_method['name']) ?></div>
                  <span class="rec-badge badge-<?= $top_method['category'] ?>">
                    <?= cat_label($top_method['category']) ?>
                  </span>
                </div>
                <a href="/hci/user/recommendations.php" class="link-sm">See all &rsaquo;</a>
              </div>
              <div class="rec-desc"><?= htmlspecialchars($top_method['description']) ?></div>
              <div class="rec-meta">
                <div class="rec-stat">
                  <div class="rec-stat-val"><?= $top_method['effectiveness'] ?>%</div>
                  <div class="rec-stat-lbl">Effectiveness</div>
                </div>
                <div class="rec-stat">
                  <div class="rec-stat-val"><?= $top_method['score'] ?>/100</div>
                  <div class="rec-stat-lbl">Match score</div>
                </div>
              </div>
              <div class="score-bar">
                <div class="score-fill" style="width:<?= $top_method['score'] ?>%"></div>
              </div>
            </div>
          <?php else: ?>
            <div class="no-rec">
              <i class="fas fa-star"></i>
              <div style="font-weight:500;margin-bottom:6px;">No recommendations yet</div>
              <div style="margin-bottom:14px;">Complete the questionnaire to receive personalized contraceptive recommendations.</div>
              <a href="/hci/user/questionnaire.php" class="btn-primary" style="display:inline-flex;">
                <i class="fas fa-clipboard-list"></i> Take questionnaire
              </a>
            </div>
          <?php endif; ?>

          <!-- Recent forum posts -->
          <div class="section-title">Latest community posts</div>
          <?php if (empty($recent_posts)): ?>
            <div class="no-rec"><i class="fas fa-comments"></i> No forum posts yet.</div>
          <?php else: ?>
            <div class="forum-card">
              <div class="forum-card-head">
                <span style="font-size:13px;font-weight:500;">Anonymous Forum</span>
                <a href="/hci/user/forum.php" class="link-sm">View all &rsaquo;</a>
              </div>
              <?php foreach ($recent_posts as $p): ?>
                <a href="/hci/user/forum.php#post-<?= $p['post_id'] ?>" class="forum-item">
                  <div class="forum-item-text"><?= htmlspecialchars($p['content']) ?></div>
                  <div class="forum-item-meta">
                    <span class="anon-badge"><i class="fas fa-user-secret" style="font-size:9px;"></i> Anonymous</span>
                    <span><?= date('M d', strtotime($p['created_at'])) ?></span>
                    <span><i class="fas fa-comment-dots" style="font-size:10px;"></i> <?= $p['reply_count'] ?> repl<?= $p['reply_count'] != 1 ? 'ies' : 'y' ?></span>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

        </div>

        <!-- RIGHT -->
        <div class="right-col">

          <!-- Questionnaire status -->
          <div class="side-card">
            <div class="side-card-head">
              <span>Questionnaire status</span>
              <a href="/hci/user/questionnaire.php" class="link-sm"><?= $has_questionnaire ? 'Retake' : 'Start' ?> &rsaquo;</a>
            </div>
            <div class="side-card-body">
              <?php if ($has_questionnaire): ?>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                  <div style="width:36px;height:36px;border-radius:50%;background:var(--green-50);display:flex;align-items:center;justify-content:center;color:#3b6d11;font-size:16px;">
                    <i class="fas fa-check"></i>
                  </div>
                  <div>
                    <div style="font-size:13px;font-weight:500;">Completed</div>
                    <div style="font-size:11px;color:var(--muted);">Last submitted <?= $q_date ?></div>
                  </div>
                </div>
                <div style="font-size:12px;color:var(--muted);line-height:1.6;">
                  You've submitted <?= $total_q ?> questionnaire<?= $total_q != 1 ? 's' : '' ?> total.
                  Retake anytime to get updated recommendations.
                </div>
              <?php else: ?>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                  <div style="width:36px;height:36px;border-radius:50%;background:var(--amber-50);display:flex;align-items:center;justify-content:center;color:var(--amber-800);font-size:16px;">
                    <i class="fas fa-clock"></i>
                  </div>
                  <div>
                    <div style="font-size:13px;font-weight:500;">Not yet completed</div>
                    <div style="font-size:11px;color:var(--muted);">Takes about 2–3 minutes</div>
                  </div>
                </div>
                <div style="font-size:12px;color:var(--muted);line-height:1.6;margin-bottom:14px;">
                  Answer a few questions about your health and preferences to get personalized contraceptive recommendations.
                </div>
                <a href="/hci/user/questionnaire.php" class="btn-primary" style="width:100%;justify-content:center;">
                  <i class="fas fa-clipboard-list"></i> Start now
                </a>
              <?php endif; ?>
            </div>
          </div>

          <!-- Activity summary -->
          <div class="side-card">
            <div class="side-card-head">
              <span>Your activity</span>
            </div>
            <div class="side-card-body">
              <div class="activity-item">
                <div class="activity-dot dot-blue"></div>
                <div>
                  <div class="activity-text"><?= $total_q ?> questionnaire<?= $total_q != 1 ? 's' : '' ?> submitted</div>
                </div>
              </div>
              <div class="activity-item">
                <div class="activity-dot dot-green"></div>
                <div>
                  <div class="activity-text"><?= $total_rec ?> recommendation<?= $total_rec != 1 ? 's' : '' ?> received</div>
                </div>
              </div>
              <div class="activity-item">
                <div class="activity-dot dot-amber"></div>
                <div>
                  <div class="activity-text"><?= $my_posts ?> forum post<?= $my_posts != 1 ? 's' : '' ?> &amp; <?= $my_replies ?> repl<?= $my_replies != 1 ? 'ies' : 'y' ?></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Links -->
          <div class="side-card">
            <div class="side-card-head"><span>Explore</span></div>
            <div class="side-card-body" style="display:flex;flex-direction:column;gap:8px;">
              <a href="/hci/user/comparison.php" class="action-card" style="padding:12px 14px;border-radius:12px;">
                <div class="action-icon icon-teal" style="width:32px;height:32px;border-radius:9px;font-size:14px;"><i class="fas fa-scale-balanced"></i></div>
                <div class="action-text">
                  <h4 style="font-size:12px;">Comparison guide</h4>
                  <p style="font-size:11px;">Compare methods side by side</p>
                </div>
              </a>
              <a href="/hci/user/chatbot.php" class="action-card" style="padding:12px 14px;border-radius:12px;">
                <div class="action-icon icon-coral" style="width:32px;height:32px;border-radius:9px;font-size:14px;background:var(--coral-50);color:var(--coral-800);"><i class="fas fa-robot"></i></div>
                <div class="action-text">
                  <h4 style="font-size:12px;">AI chatbot</h4>
                  <p style="font-size:11px;">Ask anything, anytime</p>
                </div>
              </a>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>
</body>
</html>