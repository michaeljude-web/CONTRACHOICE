<?php
session_start();
include '../includes/db_connection.php';
include '../includes/user/auth.php';

$page_title  = 'My Recommendations';
$active_page = 'recommendations';
$is_admin    = false;

$user_id = intval($_SESSION['user_id']);

$res = mysqli_query($conn,
    "SELECT * FROM questionnaire_responses
     WHERE user_id = $user_id
     ORDER BY submitted_at DESC LIMIT 1");
$q = mysqli_fetch_assoc($res);

$uRes = mysqli_query($conn, "SELECT username FROM users WHERE user_id = $user_id LIMIT 1");
$uRow = mysqli_fetch_assoc($uRes);
$user_name = $uRow['username'] ?? $_SESSION['username'] ?? 'User';

$recommendations = [];

if ($q) {
    $response_id       = intval($q['response_id']);
    $health_conditions = $q['health_conditions'] ?? 'none';
    $conditions_arr    = array_map('trim', explode(',', $health_conditions));

    $mRes = mysqli_query($conn, "SELECT * FROM contraceptive_methods");

    while ($method = mysqli_fetch_assoc($mRes)) {
        $score = 0;
        $reasons = [];
        $warnings = [];

        $contra = array_map('trim', explode(',', $method['contraindications'] ?? ''));
        $blocked = false;
        foreach ($conditions_arr as $cond) {
            if ($cond !== 'none' && in_array($cond, $contra)) {
                $blocked = true;
                $warnings[] = "Not recommended with your health condition ($cond)";
            }
        }
        if ($blocked) continue; 

        if ($q['is_smoker'] === 'yes' && !$method['suitable_smoker']) {
            $warnings[] = "Not recommended for smokers";
            continue;
        }

        if ($q['is_breastfeeding'] === 'yes' && !$method['suitable_breastfeeding']) {
            $warnings[] = "Not recommended while breastfeeding";
            continue;
        }

        if ($method['delivery'] === $q['delivery_pref']) {
            $score += 30;
            $reasons[] = "Matches your preferred delivery method";
        } elseif (
            ($q['delivery_pref'] === 'long_term' && in_array($method['delivery'], ['long_term'])) ||
            ($q['delivery_pref'] === 'barrier'   && $method['delivery'] === 'barrier')
        ) {
            $score += 20;
        }

        if ($q['hormone_free_pref'] === 'very_important' && $method['is_hormone_free']) {
            $score += 25;
            $reasons[] = "Hormone-free as you preferred";
        } elseif ($q['hormone_free_pref'] === 'not_important' && !$method['is_hormone_free']) {
            $score += 20;
            $reasons[] = "Hormonal option you're open to";
        } elseif ($q['hormone_free_pref'] === 'somewhat') {
            $score += 15;
        }

        $budgetMap = ['low' => 'low', 'medium' => 'medium', 'high' => 'high'];
        if (isset($budgetMap[$q['budget_pref']]) && $method['cost_level'] === $budgetMap[$q['budget_pref']]) {
            $score += 20;
            $reasons[] = "Fits your budget";
        } elseif ($q['budget_pref'] === 'high') {
            $score += 15;
        } elseif ($q['budget_pref'] === 'medium' && $method['cost_level'] === 'low') {
            $score += 12;
        }

        if ($q['wants_children'] === 'no' && in_array($method['category'], ['long_term'])) {
            $score += 15;
            $reasons[] = "Good long-term option since you don't plan on children";
        } elseif ($q['wants_children'] === 'yes') {
            if ($q['children_when'] === 'within_1yr' && in_array($method['delivery'], ['barrier','natural'])) {
                $score += 15;
                $reasons[] = "Easily reversible — good if you want children soon";
            } elseif ($q['children_when'] === '1_to_3yrs' && in_array($method['category'], ['hormonal'])) {
                $score += 12;
                $reasons[] = "Reversible when you're ready";
            } elseif ($q['children_when'] === '3yrs_plus' && in_array($method['category'], ['long_term'])) {
                $score += 15;
                $reasons[] = "Long-term protection with planned reversibility";
            }
        } elseif ($q['wants_children'] === 'unsure') {
            if ($method['category'] !== 'long_term') {
                $score += 10;
                $reasons[] = "Flexible option while you decide";
            }
        }

        if ($q['used_before'] === 'no' && in_array($method['category'], ['barrier','hormonal'])) {
            $score += 5;
            $reasons[] = "Beginner-friendly";
        }

        if ($method['effectiveness'] >= 99) {
            $score += 5;
            $reasons[] = ">99% effective";
        } elseif ($method['effectiveness'] >= 95) {
            $score += 3;
        }

        $score = min($score, 100);

        $recommendations[] = [
            'method'   => $method,
            'score'    => $score,
            'reasons'  => $reasons,
            'warnings' => $warnings,
        ];
    }

    usort($recommendations, fn($a, $b) => $b['score'] - $a['score']);

    $recommendations = array_slice($recommendations, 0, 5);

    mysqli_query($conn, "DELETE FROM recommendations WHERE response_id = $response_id AND user_id = $user_id");
    foreach ($recommendations as $rank => $rec) {
        $method_id = intval($rec['method']['method_id']);
        $sc        = intval($rec['score']);
        $rk        = $rank + 1;
        mysqli_query($conn,
            "INSERT INTO recommendations (user_id, response_id, method_id, score, `rank`)
             VALUES ($user_id, $response_id, $method_id, $sc, $rk)");
    }
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
  <link rel="stylesheet" href="../assets/css/user/style.css">
  <style>
    :root {
      --bg: #f7f6f3; --surface: #fff; --border: rgba(0,0,0,0.08);
      --border-md: rgba(0,0,0,0.14); --text-primary: #1a1a18;
      --text-secondary: #6b6b67; --text-muted: #a0a09b;
      --blue-50: #e6f1fb; --blue-100: #b5d4f4; --blue-600: #185FA5; --blue-800: #0C447C;
      --green-50: #eaf3de; --green-600: #3B6D11; --green-800: #27500A;
      --amber-50: #faeeda; --amber-600: #854F0B; --amber-800: #633806;
    }
    html, body { height:100%; font-family:'Outfit',sans-serif; background:var(--bg); color:var(--text-primary); font-size:14px; }
    .cc-layout { display:flex; height:100vh; overflow:hidden; }
    .cc-main   { flex:1; display:flex; flex-direction:column; overflow:hidden; }

    .topbar { height:52px; background:var(--surface); border-bottom:.5px solid var(--border-md); display:flex; align-items:center; justify-content:space-between; padding:0 24px; flex-shrink:0; }
    .topbar-title { font-family:'Playfair Display',serif; font-size:14px; }
    .topbar-title em { font-style:italic; color:var(--blue-600); }
    .topbar-sep  { color:var(--text-muted); }
    .topbar-page { font-size:13px; color:var(--text-secondary); }
    .topbar-user { font-size:12px; background:var(--blue-50); color:var(--blue-800); padding:3px 10px; border-radius:20px; font-weight:500; }
    .content-area { flex:1; overflow-y:auto; padding:28px; }

    .rec-header { max-width:900px; margin-bottom:28px; }
    .rec-header h1 { font-family:'Playfair Display',serif; font-size:26px; font-weight:500; margin-bottom:4px; }
    .rec-header p  { font-size:13px; color:var(--text-secondary); margin:0; }
    .rec-meta { display:flex; gap:8px; flex-wrap:wrap; margin-top:12px; }
    .meta-pill { font-size:11px; font-weight:500; padding:3px 10px; border-radius:20px; border:1px solid var(--border-md); color:var(--text-secondary); background:var(--surface); display:flex; align-items:center; gap:5px; }

    .top-pick-banner { background: linear-gradient(135deg, #0f4a8a 0%, #185FA5 60%, #2070b8 100%); border-radius:16px; padding:24px 28px; max-width:900px; margin-bottom:20px; color:#fff; display:flex; align-items:center; gap:20px; position:relative; overflow:hidden; }
    .top-pick-banner::before { content:''; position:absolute; top:-40px; right:-40px; width:200px; height:200px; border-radius:50%; background:rgba(255,255,255,0.06); }
    .top-pick-banner::after  { content:''; position:absolute; bottom:-60px; right:80px; width:140px; height:140px; border-radius:50%; background:rgba(255,255,255,0.04); }
    .top-pick-icon { width:56px; height:56px; background:rgba(255,255,255,0.15); border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0; z-index:1; }
    .top-pick-info { flex:1; z-index:1; }
    .top-pick-badge { font-size:10px; font-weight:600; letter-spacing:.1em; text-transform:uppercase; opacity:.7; margin-bottom:4px; }
    .top-pick-name  { font-family:'Playfair Display',serif; font-size:22px; font-weight:500; margin-bottom:6px; }
    .top-pick-desc  { font-size:13px; opacity:.85; line-height:1.5; max-width:520px; }
    .top-pick-score { text-align:center; z-index:1; flex-shrink:0; }
    .score-ring { width:72px; height:72px; position:relative; }
    .score-ring svg { width:72px; height:72px; transform:rotate(-90deg); }
    .score-ring .bg  { fill:none; stroke:rgba(255,255,255,0.2); stroke-width:5; }
    .score-ring .fg  { fill:none; stroke:#fff; stroke-width:5; stroke-linecap:round; transition:stroke-dashoffset 1s ease; }
    .score-num { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; }
    .score-num span { font-size:18px; font-weight:600; color:#fff; line-height:1; }
    .score-num small { font-size:10px; opacity:.7; }
    .top-pick-reasons { display:flex; flex-wrap:wrap; gap:6px; margin-top:10px; }
    .reason-tag { font-size:11px; background:rgba(255,255,255,0.15); border-radius:20px; padding:2px 10px; }

    .rec-list { max-width:900px; display:flex; flex-direction:column; gap:12px; }
    .rec-card { background:var(--surface); border:.5px solid var(--border-md); border-radius:14px; padding:20px 22px; display:flex; align-items:flex-start; gap:18px; transition:box-shadow .15s, border-color .15s; cursor:pointer; }
    .rec-card:hover { border-color:rgba(24,95,165,.35); box-shadow:0 4px 20px rgba(24,95,165,.08); }
    .rank-badge { width:34px; height:34px; border-radius:10px; background:var(--bg); border:1px solid var(--border-md); display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:600; color:var(--text-secondary); flex-shrink:0; margin-top:2px; }
    .rank-badge.r1 { background:var(--blue-50); border-color:var(--blue-100); color:var(--blue-800); }
    .rank-badge.r2 { background:#f0f0ee; border-color:#d0d0cc; color:#444; }
    .rank-badge.r3 { background:var(--amber-50); border-color:#fac775; color:var(--amber-800); }
    .card-body  { flex:1; min-width:0; }
    .card-top   { display:flex; align-items:center; gap:10px; margin-bottom:6px; flex-wrap:wrap; }
    .card-name  { font-weight:500; font-size:15px; }
    .cat-badge  { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.06em; padding:2px 8px; border-radius:20px; }
    .cat-hormonal   { background:#ede9fb; color:#3c2f8a; }
    .cat-barrier    { background:var(--green-50); color:var(--green-800); }
    .cat-long_term  { background:var(--blue-50); color:var(--blue-800); }
    .cat-natural    { background:#fef3e2; color:#7a4a00; }
    .cat-emergency  { background:#fce8e8; color:#7a2020; }
    .card-desc  { font-size:13px; color:var(--text-secondary); line-height:1.5; margin-bottom:10px; }
    .card-tags  { display:flex; flex-wrap:wrap; gap:6px; }
    .tag { font-size:11px; padding:2px 9px; border-radius:20px; background:var(--bg); border:1px solid var(--border-md); color:var(--text-secondary); display:flex; align-items:center; gap:4px; }
    .tag.match { background:var(--green-50); border-color:rgba(59,109,17,.2); color:var(--green-800); }
    .tag.eff   { background:var(--blue-50); border-color:rgba(24,95,165,.2); color:var(--blue-800); }
    .card-score-wrap { text-align:center; flex-shrink:0; }
    .mini-score { font-size:20px; font-weight:600; color:var(--blue-600); }
    .mini-score-label { font-size:10px; color:var(--text-muted); margin-top:1px; }
    .mini-bar { width:52px; height:4px; border-radius:2px; background:var(--bg); border:1px solid var(--border-md); overflow:hidden; margin:6px auto 0; }
    .mini-bar-fill { height:100%; border-radius:2px; background:var(--blue-600); }

    .rec-detail { display:none; border-top:.5px solid var(--border); margin-top:14px; padding-top:14px; }
    .rec-detail.open { display:block; }
    .detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
    .detail-item label { font-size:11px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:var(--text-muted); display:block; margin-bottom:3px; }
    .detail-item p { font-size:13px; color:var(--text-secondary); margin:0; }
    .side-effects-tag { font-size:12px; color:var(--text-secondary); }

    .empty-state { text-align:center; padding:60px 24px; max-width:480px; }
    .empty-icon { font-size:40px; margin-bottom:16px; opacity:.3; }
    .empty-state h3 { font-family:'Playfair Display',serif; font-size:20px; font-weight:500; margin-bottom:8px; }
    .empty-state p  { font-size:13px; color:var(--text-secondary); margin-bottom:20px; }
    .btn-primary-custom { background:var(--blue-600); color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; font-family:inherit; font-weight:500; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
    .btn-primary-custom:hover { background:var(--blue-800); color:#fff; }

    .section-label { font-size:11px; font-weight:500; letter-spacing:.1em; text-transform:uppercase; color:var(--text-muted); margin-bottom:12px; max-width:900px; }
  </style>
</head>
<body>
<div class="cc-layout">
  <?php include '../includes/user/sidebar.php'; ?>

  <div class="cc-main">
    <div class="topbar">
      <div class="topbar-left">
        <span class="topbar-title">ContraChoice</span>
        <span class="topbar-sep">›</span>
        <span class="topbar-page"><?= htmlspecialchars($page_title) ?></span>
      </div>
      <div class="topbar-right">
        <span class="topbar-user">@<?= htmlspecialchars($user_name) ?></span>
      </div>
    </div>

    <div class="content-area">

      <?php if (empty($recommendations)): ?>
      <div class="d-flex justify-content-center align-items-center" style="height:70%;">
        <div class="empty-state">
          <div class="empty-icon"><i class="fa-solid fa-clipboard-question"></i></div>
          <h3>No recommendations yet</h3>
          <p>Complete the questionnaire first so we can match you with the best contraceptive options for your needs.</p>
          <a href="/hci/user/questionnaire.php" class="btn-primary-custom">
            <i class="fa-solid fa-arrow-right"></i> Take the Questionnaire
          </a>
        </div>
      </div>

      <?php else:
        $top = $recommendations[0];
        $topMethod = $top['method'];
        $topScore  = $top['score'];
        $circumference = 2 * M_PI * 30;
        $dash = ($topScore / 100) * $circumference;

        $iconMap = [
          'hormonal'  => 'fa-capsules',
          'barrier'   => 'fa-shield-halved',
          'long_term' => 'fa-clock',
          'natural'   => 'fa-leaf',
          'emergency' => 'fa-bolt',
        ];
        $topIcon = $iconMap[$topMethod['category']] ?? 'fa-circle-check';
      ?>

      <div class="rec-header">
        <h1>Your Recommendations</h1>
        <p>Based on your questionnaire, here are the contraceptive methods best suited for you.</p>
        <div class="rec-meta">
          <span class="meta-pill"><i class="fa-solid fa-user" style="font-size:10px;"></i> <?= htmlspecialchars($user_name) ?></span>
          <span class="meta-pill"><i class="fa-solid fa-list-check" style="font-size:10px;"></i> <?= count($recommendations) ?> matches found</span>
          <a href="/hci/user/questionnaire.php" class="meta-pill text-decoration-none" style="color:var(--blue-600);border-color:rgba(24,95,165,.2);background:var(--blue-50);">
            <i class="fa-solid fa-rotate" style="font-size:10px;"></i> Retake questionnaire
          </a>
        </div>
      </div>

      <div class="top-pick-banner mb-4">
        <div class="top-pick-icon">
          <i class="fa-solid <?= $topIcon ?>"></i>
        </div>
        <div class="top-pick-info">
          <div class="top-pick-badge">Best Match</div>
          <div class="top-pick-name"><?= htmlspecialchars($topMethod['name']) ?></div>
          <div class="top-pick-desc"><?= htmlspecialchars(substr($topMethod['description'], 0, 160)) ?>...</div>
          <?php if (!empty($top['reasons'])): ?>
          <div class="top-pick-reasons">
            <?php foreach ($top['reasons'] as $r): ?>
              <span class="reason-tag"><i class="fa-solid fa-check" style="font-size:9px;margin-right:3px;"></i><?= htmlspecialchars($r) ?></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
        <div class="top-pick-score">
          <div class="score-ring">
            <svg viewBox="0 0 72 72">
              <circle class="bg" cx="36" cy="36" r="30"/>
              <circle class="fg" cx="36" cy="36" r="30"
                stroke-dasharray="<?= $circumference ?>"
                stroke-dashoffset="<?= $circumference - $dash ?>"
                id="top-ring"/>
            </svg>
            <div class="score-num">
              <span><?= $topScore ?></span>
              <small>/100</small>
            </div>
          </div>
          <div style="font-size:11px;opacity:.7;margin-top:6px;">Match score</div>
        </div>
      </div>

      <?php if (count($recommendations) > 1): ?>
      <div class="section-label">Other matches</div>
      <?php endif; ?>

      <div class="rec-list">
        <?php foreach ($recommendations as $i => $rec):
          $m      = $rec['method'];
          $score  = $rec['score'];
          $rank   = $i + 1;
          if ($rank === 1) continue;

          $catClass = 'cat-' . $m['category'];
          $catLabel = ucfirst(str_replace('_', ' ', $m['category']));
          $icon     = $iconMap[$m['category']] ?? 'fa-circle';
          $rankClass = $rank === 2 ? 'r2' : ($rank === 3 ? 'r3' : '');
        ?>
        <div class="rec-card" onclick="toggleDetail(this)">
          <div class="rank-badge <?= $rankClass ?>">#<?= $rank ?></div>
          <div class="card-body">
            <div class="card-top">
              <span class="card-name"><?= htmlspecialchars($m['name']) ?></span>
              <span class="cat-badge <?= $catClass ?>"><?= $catLabel ?></span>
              <?php if ($m['is_hormone_free']): ?>
                <span class="cat-badge" style="background:#e8f5e0;color:#2d6b0a;">Hormone-free</span>
              <?php endif; ?>
            </div>
            <div class="card-desc"><?= htmlspecialchars(substr($m['description'], 0, 120)) ?>...</div>
            <div class="card-tags">
              <span class="tag eff"><i class="fa-solid fa-shield-check" style="font-size:10px;"></i> <?= $m['effectiveness'] ?>% effective</span>
              <span class="tag"><i class="fa-solid fa-tag" style="font-size:10px;"></i> <?= ucfirst($m['cost_level']) ?> cost</span>
              <?php foreach (array_slice($rec['reasons'], 0, 2) as $r): ?>
                <span class="tag match"><i class="fa-solid fa-check" style="font-size:9px;"></i> <?= htmlspecialchars($r) ?></span>
              <?php endforeach; ?>
            </div>

            <div class="rec-detail">
              <div class="detail-grid">
                <div class="detail-item">
                  <label>How it works</label>
                  <p><?= htmlspecialchars($m['description']) ?></p>
                </div>
                <div class="detail-item">
                  <label>Possible side effects</label>
                  <p class="side-effects-tag"><?= htmlspecialchars($m['side_effects'] ?? 'None listed') ?></p>
                </div>
                <div class="detail-item">
                  <label>Delivery method</label>
                  <p><?= ucfirst(str_replace('_', ' ', $m['delivery'])) ?></p>
                </div>
                <div class="detail-item">
                  <label>Effectiveness</label>
                  <p><?= $m['effectiveness'] ?>% (typical use)</p>
                </div>
              </div>
              <?php if (!empty($rec['reasons'])): ?>
              <div class="mt-3">
                <label style="font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);">Why this matches you</label>
                <div class="d-flex flex-wrap gap-2 mt-2">
                  <?php foreach ($rec['reasons'] as $r): ?>
                    <span class="tag match"><i class="fa-solid fa-check" style="font-size:9px;"></i> <?= htmlspecialchars($r) ?></span>
                  <?php endforeach; ?>
                </div>
              </div>
              <?php endif; ?>
              <p class="mt-3" style="font-size:12px;color:var(--text-muted);">
                <i class="fa-solid fa-circle-info me-1"></i>
                These recommendations are for informational purposes only. Please consult a healthcare provider before starting any contraceptive method.
              </p>
            </div>
          </div>

          <div class="card-score-wrap">
            <div class="mini-score"><?= $score ?></div>
            <div class="mini-score-label">/ 100</div>
            <div class="mini-bar">
              <div class="mini-bar-fill" style="width:<?= $score ?>%;"></div>
            </div>
            <div style="font-size:10px;color:var(--text-muted);margin-top:6px;">
              <i class="fa-solid fa-chevron-down" style="font-size:9px;"></i> Details
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div style="max-width:900px;margin-top:24px;padding:14px 18px;background:var(--surface);border:.5px solid var(--border-md);border-radius:10px;font-size:12px;color:var(--text-muted);display:flex;gap:10px;align-items:flex-start;">
        <i class="fa-solid fa-triangle-exclamation" style="margin-top:1px;flex-shrink:0;"></i>
        <span>These recommendations are generated based on your questionnaire responses and are for <strong>informational purposes only</strong>. They do not substitute professional medical advice. Always consult a licensed healthcare provider or OB-GYN before choosing a contraceptive method.</span>
      </div>

      <?php endif; ?>
    </div>
  </div>
</div>

<script src="../assets/vendor/bootstrap-5/js/bootstrap.bundle.min.js"></script>
<script>
function toggleDetail(card) {
  const detail = card.querySelector('.rec-detail');
  const icon   = card.querySelector('.fa-chevron-down');
  if (!detail) return;
  detail.classList.toggle('open');
  if (icon) {
    icon.style.transform = detail.classList.contains('open') ? 'rotate(180deg)' : '';
    icon.style.transition = 'transform .2s';
  }
}
</script>
</body>
</html>