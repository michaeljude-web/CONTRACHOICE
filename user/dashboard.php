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

$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM questionnaire_responses WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_q = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
$stmt->close();

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

$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM recommendations WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_rec = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
$stmt->close();

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

$recent_posts = [];
$res = $conn->query("
    SELECT post_id, content, created_at, reply_count
    FROM forum_posts
    ORDER BY created_at DESC
    LIMIT 4
");
while ($row = $res->fetch_assoc()) $recent_posts[] = $row;

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
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 28px; flex-shrink: 0;
      font-size: 13px; color: var(--muted);
      font-family: 'Quicksand', sans-serif;
    }
    .topbar b { color: var(--brown); font-weight: 700; }
    .topbar-left { display: flex; align-items: center; gap: 8px; font-weight: 600; }
    .topbar-sep { color: var(--border); font-size: 16px; }
    .topbar-page { color: var(--muted); font-weight: 500; }

    .page-body { flex: 1; overflow-y: auto; padding: 28px 28px 48px; }
    .page-body::-webkit-scrollbar { width: 5px; }
    .page-body::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }

    /* ── WELCOME BANNER ── */
    .welcome-banner {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 24px 28px;
      display: flex; align-items: center; gap: 18px;
      margin-bottom: 22px;
      box-shadow: var(--shadow-sm);
      animation: popIn .4s cubic-bezier(.34,1.56,.64,1) both;
      position: relative; overflow: hidden;
    }
    .welcome-banner::before {
      content: '✿';
      position: absolute; right: 100px; top: 12px;
      font-size: 32px; opacity: .08; color: var(--brown);
    }
    .welcome-banner::after {
      content: '❀';
      position: absolute; right: 60px; bottom: 10px;
      font-size: 20px; opacity: .07; color: var(--accent-pink-d);
    }

    .welcome-avatar {
      width: 60px; height: 60px; border-radius: 50%;
      background: var(--accent-blue);
      border: 2.5px solid var(--accent-blue-d);
      display: flex; align-items: center; justify-content: center;
      font-size: 22px; font-weight: 800; color: #fff;
      flex-shrink: 0; font-family: 'Quicksand', sans-serif;
      box-shadow: 0 3px 10px rgba(107,154,184,.3);
    }

    .welcome-text h1 {
      font-family: 'Quicksand', sans-serif;
      font-size: 20px; font-weight: 700;
      color: var(--brown-d); margin-bottom: 4px;
    }
    .welcome-text p { font-size: 13px; color: var(--muted); font-weight: 500; }

    .welcome-action { margin-left: auto; flex-shrink: 0; }

    /* ── BUTTONS ── */
    .btn-primary {
      background: var(--brown);
      color: #fff;
      border: none; border-radius: 50px;
      padding: 10px 22px;
      font-family: 'Nunito', sans-serif;
      font-size: 13px; font-weight: 700; cursor: pointer;
      display: inline-flex; align-items: center; gap: 8px;
      text-decoration: none;
      transition: background .18s, transform .12s, box-shadow .18s;
      box-shadow: 0 3px 10px rgba(125,90,74,.3);
    }
    .btn-primary:hover {
      background: var(--brown-d); color: #fff; text-decoration: none;
      transform: translateY(-1px); box-shadow: 0 5px 14px rgba(125,90,74,.35);
    }
    .btn-primary:active { transform: scale(.97); }

    .btn-outline {
      background: transparent; color: var(--brown);
      border: 2px solid var(--border); border-radius: 50px;
      padding: 9px 18px; font-family: 'Nunito', sans-serif;
      font-size: 13px; font-weight: 600; cursor: pointer;
      display: inline-flex; align-items: center; gap: 7px;
      text-decoration: none;
      transition: background .15s, border-color .15s;
    }
    .btn-outline:hover {
      background: var(--surface-2); border-color: var(--brown);
      color: var(--brown-d); text-decoration: none;
    }

    /* ── STAT CARDS ── */
    .stat-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 14px;
      margin-bottom: 22px;
    }

    .stat-card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: var(--radius-md);
      padding: 18px 20px;
      display: flex; align-items: center; gap: 14px;
      animation: popIn .4s cubic-bezier(.34,1.56,.64,1) both;
      transition: transform .18s, box-shadow .18s;
      box-shadow: var(--shadow-sm);
    }
    .stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
    .stat-card:nth-child(1) { animation-delay: .05s; }
    .stat-card:nth-child(2) { animation-delay: .10s; }
    .stat-card:nth-child(3) { animation-delay: .15s; }
    .stat-card:nth-child(4) { animation-delay: .20s; }

    .stat-icon {
      width: 48px; height: 48px; border-radius: var(--radius-sm);
      display: flex; align-items: center; justify-content: center;
      font-size: 20px; flex-shrink: 0;
      border: 1.5px solid transparent;
    }
    .icon-blue   { background: var(--accent-blue);  color: var(--accent-blue-d);  border-color: #a8c2dc; }
    .icon-mint   { background: var(--accent-mint);  color: var(--accent-mint-d);  border-color: #b0d8c4; }
    .icon-pink   { background: var(--accent-pink);  color: var(--accent-pink-d);  border-color: #dfc0c0; }
    .icon-peach  { background: var(--accent-peach); color: var(--accent-peach-d); border-color: #dfc8b8; }
    .icon-lav    { background: var(--accent-lav);   color: var(--accent-lav-d);   border-color: #ccc0e0; }

    .stat-value {
      font-family: 'Quicksand', sans-serif;
      font-size: 28px; font-weight: 700;
      color: var(--brown-d); line-height: 1;
    }
    .stat-label { font-size: 11.5px; color: var(--muted); font-weight: 600; margin-top: 3px; }

    /* ── MAIN GRID ── */
    .main-grid {
      display: grid;
      grid-template-columns: 1fr 320px;
      gap: 20px;
    }

    .section-title {
      font-family: 'Quicksand', sans-serif;
      font-size: 15px; font-weight: 700;
      margin-bottom: 12px; color: var(--brown);
      display: flex; align-items: center; gap: 8px;
    }
    .section-title::before {
      content: '';
      display: inline-block; width: 4px; height: 16px;
      background: var(--brown); border-radius: 4px; opacity: .5;
    }

    /* ── ACTION CARDS ── */
    .actions-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-bottom: 22px;
    }

    .action-card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: var(--radius-md);
      padding: 16px 18px;
      display: flex; align-items: center; gap: 14px;
      text-decoration: none; color: var(--text);
      transition: transform .18s, box-shadow .18s, border-color .18s;
      box-shadow: var(--shadow-sm);
      animation: popIn .4s cubic-bezier(.34,1.56,.64,1) both;
    }
    .action-card:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-md);
      border-color: var(--brown);
      text-decoration: none; color: var(--text);
    }

    .action-icon {
      width: 44px; height: 44px; border-radius: var(--radius-sm);
      display: flex; align-items: center; justify-content: center;
      font-size: 18px; flex-shrink: 0;
      border: 1.5px solid transparent;
    }

    .action-text h4 { font-size: 13px; font-weight: 700; margin-bottom: 3px; color: var(--brown-d); }
    .action-text p  { font-size: 11.5px; color: var(--muted); font-weight: 500; }

    /* ── REC CARD ── */
    .rec-card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: var(--radius-md);
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: var(--shadow-sm);
      animation: popIn .4s cubic-bezier(.34,1.56,.64,1) .25s both;
    }

    .rec-header {
      display: flex; align-items: flex-start;
      justify-content: space-between; gap: 10px;
      margin-bottom: 10px;
    }

    .rec-badge {
      font-size: 11px; padding: 4px 12px;
      border-radius: 50px; font-weight: 700;
      border: 1.5px solid transparent;
    }
    .badge-hormonal  { background: var(--accent-blue);  color: var(--accent-blue-d);  border-color: #a8c2dc; }
    .badge-barrier   { background: var(--accent-mint);  color: var(--accent-mint-d);  border-color: #b0d8c4; }
    .badge-long_term { background: var(--accent-lav);   color: var(--accent-lav-d);   border-color: #ccc0e0; }
    .badge-natural   { background: var(--accent-peach); color: var(--accent-peach-d); border-color: #dfc8b8; }
    .badge-emergency { background: var(--accent-pink);  color: var(--accent-pink-d);  border-color: #dfc0c0; }

    .rec-name {
      font-family: 'Quicksand', sans-serif;
      font-size: 17px; font-weight: 700;
      color: var(--brown-d); margin-bottom: 6px;
    }
    .rec-desc {
      font-size: 13px; color: var(--muted); line-height: 1.65; margin-bottom: 14px;
      display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
      font-weight: 500;
    }

    .rec-meta { display: flex; gap: 18px; }
    .rec-stat { display: flex; flex-direction: column; gap: 2px; }
    .rec-stat-val {
      font-family: 'Quicksand', sans-serif;
      font-size: 16px; font-weight: 700; color: var(--brown-d);
    }
    .rec-stat-lbl { font-size: 11px; color: var(--muted); font-weight: 600; }

    .score-bar { height: 6px; border-radius: 6px; background: var(--border); margin-top: 12px; overflow: hidden; }
    .score-fill { height: 100%; border-radius: 6px; background: linear-gradient(90deg, var(--accent-blue-d), var(--accent-mint-d)); transition: width .8s cubic-bezier(.34,1.56,.64,1); }

    .no-rec {
      background: var(--surface); border: 1.5px dashed var(--border);
      border-radius: var(--radius-md); padding: 36px 20px;
      text-align: center; color: var(--muted); font-size: 13px;
      margin-bottom: 20px; font-weight: 500;
    }
    .no-rec i { font-size: 30px; display: block; margin-bottom: 12px; color: var(--border); }

    /* ── FORUM FEED ── */
    .forum-card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: var(--radius-md);
      overflow: hidden;
      box-shadow: var(--shadow-sm);
      animation: popIn .4s cubic-bezier(.34,1.56,.64,1) .3s both;
    }

    .forum-card-head {
      padding: 14px 18px;
      border-bottom: 1.5px solid var(--border);
      display: flex; justify-content: space-between; align-items: center;
      background: var(--surface-2);
    }
    .forum-card-head span { font-family: 'Quicksand', sans-serif; font-weight: 700; font-size: 13px; color: var(--brown); }

    .forum-item {
      padding: 14px 18px;
      border-bottom: 1px solid var(--border);
      transition: background .12s;
      text-decoration: none; display: block; color: var(--text);
    }
    .forum-item:last-child { border-bottom: none; }
    .forum-item:hover { background: var(--bg); text-decoration: none; color: var(--text); }

    .forum-item-text {
      font-size: 13px; line-height: 1.55; margin-bottom: 7px; font-weight: 500;
      display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }

    .forum-item-meta {
      display: flex; gap: 10px;
      font-size: 11px; color: var(--muted); align-items: center; font-weight: 600;
    }

    .anon-badge {
      background: var(--accent-peach); color: var(--accent-peach-d);
      padding: 2px 9px; border-radius: 50px; font-size: 10px;
      display: inline-flex; align-items: center; gap: 3px;
      border: 1px solid #dfc8b8; font-weight: 700;
    }

    /* ── RIGHT COLUMN ── */
    .right-col { display: flex; flex-direction: column; gap: 16px; }

    .side-card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: var(--radius-md);
      overflow: hidden;
      box-shadow: var(--shadow-sm);
      animation: popIn .4s cubic-bezier(.34,1.56,.64,1) both;
    }

    .side-card-head {
      padding: 13px 18px;
      border-bottom: 1.5px solid var(--border);
      font-family: 'Quicksand', sans-serif;
      font-size: 13px; font-weight: 700; color: var(--brown);
      display: flex; justify-content: space-between; align-items: center;
      background: var(--surface-2);
    }

    .side-card-body { padding: 16px 18px; }

    .activity-item {
      display: flex; gap: 10px; align-items: flex-start;
      padding: 9px 0;
      border-bottom: 1px solid var(--border);
      font-size: 12px; font-weight: 600;
    }
    .activity-item:last-child { border-bottom: none; padding-bottom: 0; }
    .activity-dot {
      width: 10px; height: 10px; border-radius: 50%;
      flex-shrink: 0; margin-top: 3px;
      border: 2px solid transparent;
    }
    .dot-blue  { background: var(--accent-blue);  border-color: var(--accent-blue-d); }
    .dot-mint  { background: var(--accent-mint);  border-color: var(--accent-mint-d); }
    .dot-pink  { background: var(--accent-pink);  border-color: var(--accent-pink-d); }
    .activity-text { color: var(--text); line-height: 1.5; }

    .link-sm {
      font-size: 12px; color: var(--brown);
      text-decoration: none; font-weight: 700;
    }
    .link-sm:hover { text-decoration: underline; color: var(--brown-d); }

    /* ── ANIMATIONS ── */
    @keyframes popIn {
      from { opacity: 0; transform: translateY(14px) scale(.97); }
      to   { opacity: 1; transform: translateY(0) scale(1); }
    }

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
        <span><b>ContraChoice</b></span>
        <span class="topbar-sep">›</span>
        <span class="topbar-page"><?= htmlspecialchars($page_title) ?></span>
      </div>
    </div>

    <div class="page-body">

      <div class="welcome-banner">
        <div class="welcome-avatar">
          <?= strtoupper(substr($user_name, 0, 2)) ?>
        </div>
        <div class="welcome-text">
          <h1><?= $greeting ?>, <?= htmlspecialchars($user_name) ?>! ✿</h1>
          <p>
            <?php if ($has_questionnaire): ?>
              Last assessment: <?= $q_date ?>. You have <?= $total_rec ?> recommendation<?= $total_rec != 1 ? 's' : '' ?> waiting for you~
            <?php else: ?>
              Welcome to ContraChoice! Take the questionnaire to get your personalized recommendations.
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

      <div class="stat-grid">
        <div class="stat-card">
          <div class="stat-icon icon-blue"><i class="fas fa-clipboard-list"></i></div>
          <div>
            <div class="stat-value"><?= $total_q ?></div>
            <div class="stat-label">Questionnaire<?= $total_q != 1 ? 's' : '' ?> submitted</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon icon-mint"><i class="fas fa-star"></i></div>
          <div>
            <div class="stat-value"><?= $total_rec ?></div>
            <div class="stat-label">Recommendation<?= $total_rec != 1 ? 's' : '' ?> received</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon icon-pink"><i class="fas fa-comments"></i></div>
          <div>
            <div class="stat-value"><?= $my_posts ?></div>
            <div class="stat-label">Forum post<?= $my_posts != 1 ? 's' : '' ?> made</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon icon-peach"><i class="fas fa-reply"></i></div>
          <div>
            <div class="stat-value"><?= $my_replies ?></div>
            <div class="stat-label">Forum repl<?= $my_replies != 1 ? 'ies' : 'y' ?> made</div>
          </div>
        </div>
      </div>

      <div class="main-grid">

        <div>

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
              <div class="action-icon icon-mint"><i class="fas fa-star"></i></div>
              <div class="action-text">
                <h4>My recommendations</h4>
                <p><?= $total_rec > 0 ? $total_rec . ' method' . ($total_rec != 1 ? 's' : '') . ' suggested for you' : 'No recommendations yet' ?></p>
              </div>
            </a>
            <a href="/hci/user/forum.php" class="action-card">
              <div class="action-icon icon-pink"><i class="fas fa-comments"></i></div>
              <div class="action-text">
                <h4>Anonymous forum</h4>
                <p>Ask questions, share experiences</p>
              </div>
            </a>
            <a href="/hci/user/chatbot.php" class="action-card">
              <div class="action-icon icon-lav"><i class="fas fa-robot"></i></div>
              <div class="action-text">
                <h4>Ask the AI</h4>
                <p>Get answers about contraceptives 24/7</p>
              </div>
            </a>
          </div>

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
              <div style="font-weight:700;margin-bottom:6px;color:var(--brown);">No recommendations yet</div>
              <div style="margin-bottom:16px;">Complete the questionnaire to receive personalized contraceptive recommendations.</div>
              <a href="/hci/user/questionnaire.php" class="btn-primary" style="display:inline-flex;">
                <i class="fas fa-clipboard-list"></i> Take questionnaire
              </a>
            </div>
          <?php endif; ?>

          <div class="section-title">Latest community posts</div>
          <?php if (empty($recent_posts)): ?>
            <div class="no-rec"><i class="fas fa-comments"></i> No forum posts yet.</div>
          <?php else: ?>
            <div class="forum-card">
              <div class="forum-card-head">
                <span>Anonymous Forum</span>
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

        <div class="right-col">

          <div class="side-card">
            <div class="side-card-head">
              <span>Questionnaire status</span>
              <a href="/hci/user/questionnaire.php" class="link-sm"><?= $has_questionnaire ? 'Retake' : 'Start' ?> &rsaquo;</a>
            </div>
            <div class="side-card-body">
              <?php if ($has_questionnaire): ?>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                  <div style="width:40px;height:40px;border-radius:50%;background:var(--accent-mint);border:2px solid var(--accent-mint-d);display:flex;align-items:center;justify-content:center;color:var(--accent-mint-d);font-size:16px;">
                    <i class="fas fa-check"></i>
                  </div>
                  <div>
                    <div style="font-size:13px;font-weight:700;color:var(--brown-d);">Completed ✓</div>
                    <div style="font-size:11px;color:var(--muted);font-weight:500;">Last submitted <?= $q_date ?></div>
                  </div>
                </div>
                <div style="font-size:12px;color:var(--muted);line-height:1.65;font-weight:500;">
                  You've submitted <?= $total_q ?> questionnaire<?= $total_q != 1 ? 's' : '' ?> total. Retake anytime for updated recommendations!
                </div>
              <?php else: ?>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                  <div style="width:40px;height:40px;border-radius:50%;background:var(--accent-peach);border:2px solid var(--accent-peach-d);display:flex;align-items:center;justify-content:center;color:var(--accent-peach-d);font-size:16px;">
                    <i class="fas fa-clock"></i>
                  </div>
                  <div>
                    <div style="font-size:13px;font-weight:700;color:var(--brown-d);">Not yet completed</div>
                    <div style="font-size:11px;color:var(--muted);font-weight:500;">Takes about 2–3 minutes</div>
                  </div>
                </div>
                <div style="font-size:12px;color:var(--muted);line-height:1.65;margin-bottom:14px;font-weight:500;">
                  Answer a few questions about your health and preferences to get personalized contraceptive recommendations.
                </div>
                <a href="/hci/user/questionnaire.php" class="btn-primary" style="width:100%;justify-content:center;">
                  <i class="fas fa-clipboard-list"></i> Start now
                </a>
              <?php endif; ?>
            </div>
          </div>

          <div class="side-card">
            <div class="side-card-head">
              <span>Your activity</span>
            </div>
            <div class="side-card-body">
              <div class="activity-item">
                <div class="activity-dot dot-blue"></div>
                <div class="activity-text"><?= $total_q ?> questionnaire<?= $total_q != 1 ? 's' : '' ?> submitted</div>
              </div>
              <div class="activity-item">
                <div class="activity-dot dot-mint"></div>
                <div class="activity-text"><?= $total_rec ?> recommendation<?= $total_rec != 1 ? 's' : '' ?> received</div>
              </div>
              <div class="activity-item">
                <div class="activity-dot dot-pink"></div>
                <div class="activity-text"><?= $my_posts ?> post<?= $my_posts != 1 ? 's' : '' ?> &amp; <?= $my_replies ?> repl<?= $my_replies != 1 ? 'ies' : 'y' ?> in forum</div>
              </div>
            </div>
          </div>

          <div class="side-card">
            <div class="side-card-head"><span>Explore</span></div>
            <div class="side-card-body" style="display:flex;flex-direction:column;gap:10px;">
              <a href="/hci/user/comparison.php" class="action-card" style="padding:12px 14px;border-radius:var(--radius-sm);">
                <div class="action-icon icon-mint" style="width:36px;height:36px;border-radius:10px;font-size:15px;"><i class="fas fa-scale-balanced"></i></div>
                <div class="action-text">
                  <h4 style="font-size:12px;">Comparison guide</h4>
                  <p style="font-size:11px;">Compare methods side by side</p>
                </div>
              </a>
              <a href="/hci/user/chatbot.php" class="action-card" style="padding:12px 14px;border-radius:var(--radius-sm);">
                <div class="action-icon icon-lav" style="width:36px;height:36px;border-radius:10px;font-size:15px;"><i class="fas fa-robot"></i></div>
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
<?php include '../includes/user/chatbot_widget.php'; ?>
</body>
</html>