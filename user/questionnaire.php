<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include '../includes/db_connection.php';
include '../includes/user/auth.php';

$page_title  = 'Questionnaire';
$active_page = 'questionnaire';
$is_admin    = false;

$user_id  = intval($_SESSION['user_id']);
$success  = '';
$error    = '';

$existing     = mysqli_query($conn, "SELECT response_id FROM questionnaire_responses WHERE user_id = $user_id ORDER BY submitted_at DESC LIMIT 1");
$has_existing = mysqli_num_rows($existing) > 0;

if (isset($_POST['submit_questionnaire'])) {

    $allowed = [
        'age_range'         => ['under_18','18_to_24','25_to_34','35_to_44','45_plus'],
        'sexually_active'   => ['yes','no','prefer_not_to_say'],
        'wants_children'    => ['yes','no','unsure'],
        'children_when'     => ['within_1yr','1_to_3yrs','3yrs_plus','not_applicable'],
        'is_smoker'         => ['yes','no'],
        'is_breastfeeding'  => ['yes','no'],
        'hormone_free_pref' => ['very_important','somewhat','not_important'],
        'delivery_pref'     => ['daily_pill','weekly_patch','monthly_injection','long_term','barrier','natural'],
        'budget_pref'       => ['low','medium','high'],
        'used_before'       => ['yes','no'],
        'cycle_regularity'  => ['regular','irregular','not_sure'],
        'relationship_status' => ['single','committed','prefer_not_to_say'],
    ];

    $age_range           = trim($_POST['age_range']           ?? '');
    $sexually_active     = trim($_POST['sexually_active']     ?? '');
    $wants_children      = trim($_POST['wants_children']      ?? '');
    $children_when       = trim($_POST['children_when']       ?? 'not_applicable');
    $is_smoker           = trim($_POST['is_smoker']           ?? '');
    $is_breastfeeding    = trim($_POST['is_breastfeeding']    ?? '');
    $hormone_free_pref   = trim($_POST['hormone_free_pref']   ?? '');
    $delivery_pref       = trim($_POST['delivery_pref']       ?? '');
    $budget_pref         = trim($_POST['budget_pref']         ?? '');
    $used_before         = trim($_POST['used_before']         ?? '');
    $cycle_regularity    = trim($_POST['cycle_regularity']    ?? '');
    $relationship_status = trim($_POST['relationship_status'] ?? '');
    $previous_method     = substr(trim($_POST['previous_method'] ?? ''), 0, 100);

    if ($wants_children !== 'yes') $children_when = 'not_applicable';
    if (empty($children_when))     $children_when = 'not_applicable';

    $valid_conditions = ['none','hypertension','migraines','diabetes','blood_clots','liver_disease','depression'];
    $raw_conditions   = isset($_POST['health_conditions']) && is_array($_POST['health_conditions'])
                        ? $_POST['health_conditions'] : ['none'];
    $clean_conditions = array_filter($raw_conditions, fn($v) => in_array($v, $valid_conditions));
    $health_conditions = !empty($clean_conditions) ? implode(',', $clean_conditions) : 'none';

    $fields_to_check = compact(
        'age_range','sexually_active','wants_children','children_when',
        'is_smoker','is_breastfeeding','hormone_free_pref',
        'delivery_pref','budget_pref','used_before',
        'cycle_regularity','relationship_status'
    );

    $missing = [];
    foreach ($fields_to_check as $field => $value) {
        if (!in_array($value, $allowed[$field], true)) {
            $missing[] = str_replace('_', ' ', $field);
        }
    }

    if (!empty($missing)) {
        $error = "Please answer all questions before submitting. Missing: " . implode(', ', $missing);
    } else {
        $age_range           = mysqli_real_escape_string($conn, $age_range);
        $sexually_active     = mysqli_real_escape_string($conn, $sexually_active);
        $wants_children      = mysqli_real_escape_string($conn, $wants_children);
        $children_when       = mysqli_real_escape_string($conn, $children_when);
        $health_conditions   = mysqli_real_escape_string($conn, $health_conditions);
        $is_smoker           = mysqli_real_escape_string($conn, $is_smoker);
        $is_breastfeeding    = mysqli_real_escape_string($conn, $is_breastfeeding);
        $hormone_free_pref   = mysqli_real_escape_string($conn, $hormone_free_pref);
        $delivery_pref       = mysqli_real_escape_string($conn, $delivery_pref);
        $budget_pref         = mysqli_real_escape_string($conn, $budget_pref);
        $used_before         = mysqli_real_escape_string($conn, $used_before);
        $cycle_regularity    = mysqli_real_escape_string($conn, $cycle_regularity);
        $relationship_status = mysqli_real_escape_string($conn, $relationship_status);
        $previous_method     = mysqli_real_escape_string($conn, $previous_method);

        $q = "INSERT INTO questionnaire_responses
                (user_id, age_range, sexually_active, wants_children, children_when, health_conditions,
                 is_smoker, is_breastfeeding, hormone_free_pref, delivery_pref, budget_pref,
                 used_before, previous_method, cycle_regularity, relationship_status)
              VALUES
                ($user_id, '$age_range', '$sexually_active', '$wants_children', '$children_when', '$health_conditions',
                 '$is_smoker', '$is_breastfeeding', '$hormone_free_pref', '$delivery_pref', '$budget_pref',
                 '$used_before', '$previous_method', '$cycle_regularity', '$relationship_status')";

        if (mysqli_query($conn, $q)) {
            $success      = "Your responses have been saved! Check your recommendations.";
            $has_existing = true;
        } else {
            $error = "Something went wrong: " . mysqli_error($conn);
        }
    }
}

$user_result = mysqli_query($conn, "SELECT username FROM users WHERE user_id = $user_id LIMIT 1");
$user_row    = mysqli_fetch_assoc($user_result);
$user_name   = $user_row['username'] ?? $_SESSION['username'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title) ?> — ContraChoice</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/vendor/bootstrap-5/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendor/fontawesome-7/css/all.min.css">
  <style>
    :root {
      --bg:           #f5f0e8;
      --surface:      #fdfaf5;
      --surface-2:    #faf6ef;
      --border:       #e8dfd0;
      --border-md:    #d8cfc0;
      --text:         #4a3728;
      --muted:        #9b8776;
      --brown:        #7d5a4a;
      --brown-d:      #5a3a2a;

      --c-blue:       #b8cfe8;
      --c-blue-d:     #4a7fa8;
      --c-blue-bg:    #eaf2fb;

      --c-mint:       #cce8dc;
      --c-mint-d:     #3a8a6a;
      --c-mint-bg:    #eaf7f2;

      --c-peach:      #f5ddd0;
      --c-peach-d:    #b86040;
      --c-peach-bg:   #fdf2ec;

      --c-pink:       #f0d5d5;
      --c-pink-d:     #c05858;
      --c-pink-bg:    #fceaea;

      --c-lav:        #ddd5f0;
      --c-lav-d:      #7a6ab8;
      --c-lav-bg:     #f2eefb;

      --c-sage:       #cce0c0;
      --c-sage-d:     #4a7a3a;
      --c-sage-bg:    #eef6ea;

      --radius-sm:    10px;
      --radius-md:    16px;
      --radius-lg:    22px;
      --shadow-sm:    0 2px 10px rgba(90,58,42,.07);
      --shadow-md:    0 4px 20px rgba(90,58,42,.11);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
      height: 100%;
      font-family: 'Nunito', sans-serif;
      background: var(--bg);
      color: var(--text);
      font-size: 14px;
    }

    body {
      background-image:
        radial-gradient(circle at 10% 15%, rgba(184,207,232,.20) 0%, transparent 45%),
        radial-gradient(circle at 88% 80%, rgba(204,232,220,.18) 0%, transparent 45%);
    }

    .cc-layout { display: flex; height: 100vh; overflow: hidden; }
    .cc-main   { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

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

    .content-area {
      flex: 1; overflow-y: auto;
      padding: 32px 32px 56px;
    }
    .content-area::-webkit-scrollbar { width: 5px; }
    .content-area::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }

    .notice-bar {
      max-width: 660px;
      border-radius: var(--radius-md);
      padding: 13px 18px;
      display: flex; align-items: center; gap: 10px;
      margin-bottom: 22px;
      font-size: 13px; font-weight: 600;
      border: 1.5px solid transparent;
    }
    .notice-success { background: var(--c-mint-bg); border-color: var(--c-mint); color: var(--c-mint-d); }
    .notice-error   { background: var(--c-pink-bg); border-color: var(--c-pink); color: var(--c-pink-d); }
    .notice-info    { background: var(--c-blue-bg); border-color: var(--c-blue); color: var(--c-blue-d); }

    .notice-bar a { color: inherit; font-weight: 700; text-decoration: underline; }
    .notice-bar a:hover { opacity: .8; }

    .q-steps {
      display: flex; align-items: center;
      margin-bottom: 28px; max-width: 660px;
    }
    .q-step { display: flex; align-items: center; flex: 1; }
    .q-step:last-child { flex: 0; }

    .step-pill {
      height: 30px; min-width: 30px; padding: 0 12px;
      border-radius: 30px;
      border: 2px solid var(--border-md);
      background: var(--surface);
      color: var(--muted);
      font-size: 11.5px; font-weight: 700;
      display: flex; align-items: center; justify-content: center; gap: 5px;
      flex-shrink: 0;
      transition: all 0.2s;
      white-space: nowrap;
      font-family: 'Quicksand', sans-serif;
    }

    .step-line { flex: 1; height: 2px; background: var(--border); margin: 0 6px; border-radius: 2px; }

    .q-step.done .step-pill {
      background: var(--c-mint-bg); border-color: var(--c-mint);
      color: var(--c-mint-d);
    }
    .q-step.active .step-pill {
      background: var(--brown); border-color: var(--brown);
      color: #fff;
      box-shadow: 0 3px 10px rgba(125,90,74,.3);
    }
    .q-step.done .step-line { background: var(--c-mint); }

    .q-panel { display: none; }
    .q-panel.active { display: block; animation: popIn .3s cubic-bezier(.34,1.56,.64,1) both; }

    @keyframes popIn {
      from { opacity: 0; transform: translateY(10px) scale(.98); }
      to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .q-card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 30px 30px 24px;
      max-width: 660px;
      box-shadow: var(--shadow-sm);
    }

    .q-section-label {
      font-size: 10.5px; font-weight: 800;
      letter-spacing: 0.12em; text-transform: uppercase;
      color: var(--muted); margin-bottom: 6px;
    }

    .q-title {
      font-family: 'Quicksand', sans-serif;
      font-size: 22px; font-weight: 700;
      color: var(--brown-d); margin-bottom: 4px;
    }

    .q-subtitle {
      font-size: 13px; color: var(--muted); margin-bottom: 26px;
      font-weight: 500; line-height: 1.6;
    }

    .q-block { margin-bottom: 26px; }
    .q-block:last-of-type { margin-bottom: 0; }

    .q-label {
      font-size: 13px; font-weight: 700;
      color: var(--brown); margin-bottom: 10px; display: block;
    }
    .q-label span { font-weight: 500; color: var(--muted); }

    .opt-group { display: flex; flex-direction: column; gap: 7px; }

    .opt-btn {
      display: flex; align-items: center; gap: 14px;
      padding: 12px 16px;
      border: 1.5px solid var(--border-md);
      border-radius: var(--radius-sm);
      background: var(--surface-2);
      cursor: pointer; text-align: left;
      transition: border-color .15s, background .15s, transform .1s;
      width: 100%; font-family: 'Nunito', sans-serif;
      font-size: 13.5px; color: var(--text);
    }
    .opt-btn:hover {
      border-color: var(--brown);
      background: var(--bg);
      transform: translateX(2px);
    }
    .opt-btn.selected {
      border-color: var(--brown);
      background: #fdf4ee;
      color: var(--brown-d);
    }

    .opt-dot {
      width: 18px; height: 18px; border-radius: 50%;
      border: 2px solid var(--border-md);
      flex-shrink: 0; display: flex; align-items: center; justify-content: center;
      transition: all .15s;
    }
    .opt-btn.selected .opt-dot {
      background: var(--brown); border-color: var(--brown);
    }
    .opt-dot-inner {
      width: 6px; height: 6px; border-radius: 50%;
      background: #fff; opacity: 0; transition: opacity .15s;
    }
    .opt-btn.selected .opt-dot-inner { opacity: 1; }

    .opt-text-main { font-weight: 600; line-height: 1.2; }
    .opt-text-sub  { font-size: 12px; color: var(--muted); margin-top: 2px; font-weight: 500; }

    .opt-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 7px; }
    .opt-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 7px; }

    .opt-check {
      display: flex; align-items: center; gap: 10px;
      padding: 11px 14px;
      border: 1.5px solid var(--border-md);
      border-radius: var(--radius-sm);
      background: var(--surface-2);
      cursor: pointer;
      transition: border-color .15s, background .15s;
      user-select: none; font-size: 13px; font-weight: 600;
    }
    .opt-check:hover { border-color: var(--brown); background: var(--bg); }
    .opt-check.selected { border-color: var(--brown); background: #fdf4ee; color: var(--brown-d); }
    .opt-check input[type=checkbox] { display: none; }

    .check-box {
      width: 18px; height: 18px; border-radius: 5px;
      border: 2px solid var(--border-md);
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0; transition: all .15s;
    }
    .opt-check.selected .check-box { background: var(--brown); border-color: var(--brown); }
    .check-icon { display: none; color: #fff; font-size: 9px; }
    .opt-check.selected .check-icon { display: block; }

    .q-divider {
      height: 1.5px; background: var(--border);
      border-radius: 2px; margin: 24px 0;
    }

    .q-nav {
      display: flex; justify-content: space-between; align-items: center;
      margin-top: 26px; padding-top: 20px;
      border-top: 1.5px solid var(--border);
    }

    .btn-prev {
      background: none; border: 1.5px solid var(--border-md);
      border-radius: 50px; padding: 9px 22px;
      font-size: 13px; font-family: 'Nunito', sans-serif; font-weight: 700;
      cursor: pointer; color: var(--muted);
      transition: background .12s, border-color .12s, color .12s;
    }
    .btn-prev:hover { background: var(--bg); border-color: var(--brown); color: var(--brown); }

    .btn-next {
      background: var(--brown); border: none; border-radius: 50px;
      padding: 10px 26px; font-size: 13px;
      font-family: 'Nunito', sans-serif; font-weight: 700;
      cursor: pointer; color: #fff;
      transition: background .12s, box-shadow .12s, transform .1s;
      box-shadow: 0 3px 10px rgba(125,90,74,.28);
    }
    .btn-next:hover { background: var(--brown-d); transform: translateY(-1px); box-shadow: 0 5px 14px rgba(125,90,74,.35); }

    .btn-submit {
      background: var(--c-mint-d); border: none; border-radius: 50px;
      padding: 10px 26px; font-size: 13px;
      font-family: 'Nunito', sans-serif; font-weight: 700;
      cursor: pointer; color: #fff;
      transition: background .12s, box-shadow .12s, transform .1s;
      box-shadow: 0 3px 10px rgba(58,138,106,.28);
      display: inline-flex; align-items: center; gap: 8px;
    }
    .btn-submit:hover { background: #2d7a58; transform: translateY(-1px); box-shadow: 0 5px 14px rgba(58,138,106,.35); }

    .step-counter { font-size: 12px; color: var(--muted); font-weight: 600; }

    .text-input {
      width: 100%;
      padding: 11px 16px;
      border: 1.5px solid var(--border-md);
      border-radius: var(--radius-sm);
      background: var(--surface-2);
      font-family: 'Nunito', sans-serif;
      font-size: 13.5px; color: var(--text);
      outline: none;
      transition: border-color .15s;
    }
    .text-input:focus { border-color: var(--brown); background: var(--surface); }
    .text-input::placeholder { color: var(--muted); }

    .tag-pill {
      display: inline-block; padding: 3px 10px;
      border-radius: 20px; font-size: 11px; font-weight: 700;
      background: var(--c-peach-bg); color: var(--c-peach-d);
      border: 1px solid var(--c-peach); margin-left: 6px;
    }
  </style>
</head>
<body>
<div class="cc-layout">

  <?php include '../includes/user/sidebar.php'; ?>

  <div class="cc-main">
    <div class="topbar">
      <div class="topbar-left">
        <span><b>ContraChoice</b></span>
        <span class="topbar-sep">/</span>
        <span class="topbar-page"><?= htmlspecialchars($page_title) ?></span>
      </div>
    </div>

    <div class="content-area">

      <?php if ($success): ?>
      <div class="notice-bar notice-success">
        <i class="fa-solid fa-circle-check"></i>
        <?= $success ?>
        <a href="/hci/user/recommendations.php" class="ms-2">View Recommendations</a>
      </div>
      <?php endif; ?>

      <?php if ($error): ?>
      <div class="notice-bar notice-error">
        <i class="fa-solid fa-circle-exclamation"></i>
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <?php if ($has_existing && !$success): ?>
      <div class="notice-bar notice-info">
        <i class="fa-solid fa-rotate"></i>
        You have already completed the questionnaire.
        <a href="#" onclick="document.getElementById('q-form-wrap').style.display='block';this.closest('.notice-bar').style.display='none';return false;" class="ms-2">Retake</a>
        <a href="/hci/user/recommendations.php" class="ms-2">View results</a>
      </div>
      <div id="q-form-wrap" style="display:none;">
      <?php else: ?>
      <div id="q-form-wrap">
      <?php endif; ?>

        <div class="q-steps" id="q-steps">
          <?php
          $step_labels = ['Personal','Health','Family','Preferences'];
          for ($i = 1; $i <= 5; $i++):
            $label = $step_labels[$i - 1] ?? '';
          ?>
            <div class="q-step <?= $i === 1 ? 'active' : '' ?>" id="step-ind-<?= $i ?>">
              <div class="step-pill">
                <?php if ($i < 5): ?>
                  <?= $i ?>. <?= $step_labels[$i-1] ?>
                <?php else: ?>
                  5. Preferences
                <?php endif; ?>
              </div>
              <?php if ($i < 5): ?><div class="step-line"></div><?php endif; ?>
            </div>
          <?php endfor; ?>
        </div>

        <form method="POST" id="q-form">

          <div class="q-panel active" id="panel-1">
            <div class="q-card">
              <div class="q-section-label">Step 1 of 5</div>
              <div class="q-title">Personal Information</div>
              <div class="q-subtitle">Basic details to help us provide age-appropriate recommendations.</div>

              <div class="q-block">
                <span class="q-label">What is your age range?</span>
                <div class="opt-group opt-grid-3" data-name="age_range" id="age_range-group" style="display:grid;">
                  <button type="button" class="opt-btn" data-val="under_18">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">Under 18</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="18_to_24">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">18 – 24</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="25_to_34">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">25 – 34</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="35_to_44">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">35 – 44</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="45_plus">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">45 or older</div></div>
                  </button>
                </div>
                <input type="hidden" name="age_range" id="val-age_range">
              </div>

              <div class="q-divider"></div>

              <div class="q-block">
                <span class="q-label">What is your relationship status?</span>
                <div class="opt-group" data-name="relationship_status">
                  <button type="button" class="opt-btn" data-val="single">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">Single / Not in a relationship</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="committed">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">In a committed relationship</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="prefer_not_to_say">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">Prefer not to say</div></div>
                  </button>
                </div>
                <input type="hidden" name="relationship_status" id="val-relationship_status">
              </div>

              <div class="q-divider"></div>

              <div class="q-block">
                <span class="q-label">Are you currently sexually active?</span>
                <div class="opt-group" data-name="sexually_active">
                  <button type="button" class="opt-btn" data-val="yes">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">Yes</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="no">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">No</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="prefer_not_to_say">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">Prefer not to say</div></div>
                  </button>
                </div>
                <input type="hidden" name="sexually_active" id="val-sexually_active">
              </div>

              <div class="q-nav">
                <span class="step-counter">1 of 5</span>
                <button type="button" class="btn-next" onclick="nextPanel(1)">Next</button>
              </div>
            </div>
          </div>

          <div class="q-panel" id="panel-2">
            <div class="q-card">
              <div class="q-section-label">Step 2 of 5</div>
              <div class="q-title">Your Health</div>
              <div class="q-subtitle">This helps us avoid recommending methods that may not be safe for you.</div>

              <div class="q-block">
                <span class="q-label">Do you have any of these health conditions?</span>
                <div class="opt-grid-2" id="health-grid" style="display:grid;">
                  <?php
                  $conditions = [
                    'none'          => ['None / Not sure',            ''],
                    'hypertension'  => ['High blood pressure',         ''],
                    'migraines'     => ['Migraines',                   ''],
                    'diabetes'      => ['Diabetes',                    ''],
                    'blood_clots'   => ['History of blood clots',      ''],
                    'liver_disease' => ['Liver disease',               ''],
                    'depression'    => ['Depression / Anxiety',        ''],
                  ];
                  foreach ($conditions as $val => [$label, $sub]): ?>
                  <label class="opt-check" data-val="<?= $val ?>">
                    <input type="checkbox" name="health_conditions[]" value="<?= $val ?>">
                    <div class="check-box"><i class="fa-solid fa-check check-icon"></i></div>
                    <div>
                      <div style="font-weight:600;"><?= $label ?></div>
                      <?php if ($sub): ?><div style="font-size:11.5px;color:var(--muted);margin-top:1px;"><?= $sub ?></div><?php endif; ?>
                    </div>
                  </label>
                  <?php endforeach; ?>
                </div>
              </div>

              <div class="q-divider"></div>

              <div class="q-block">
                <span class="q-label">How would you describe your menstrual cycle?</span>
                <div class="opt-group" data-name="cycle_regularity">
                  <button type="button" class="opt-btn" data-val="regular">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div>
                      <div class="opt-text-main">Regular</div>
                      <div class="opt-text-sub">Consistent, predictable cycle</div>
                    </div>
                  </button>
                  <button type="button" class="opt-btn" data-val="irregular">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div>
                      <div class="opt-text-main">Irregular</div>
                      <div class="opt-text-sub">Unpredictable or varying cycle</div>
                    </div>
                  </button>
                  <button type="button" class="opt-btn" data-val="not_sure">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div>
                      <div class="opt-text-main">Not sure</div>
                      <div class="opt-text-sub">I am not certain</div>
                    </div>
                  </button>
                </div>
                <input type="hidden" name="cycle_regularity" id="val-cycle_regularity">
              </div>

              <div class="q-divider"></div>

              <div class="q-block">
                <span class="q-label">Do you smoke?</span>
                <div class="opt-group" data-name="is_smoker">
                  <button type="button" class="opt-btn" data-val="yes">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">Yes</div><div class="opt-text-sub">Current smoker</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="no">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">No</div><div class="opt-text-sub">Non-smoker or ex-smoker</div></div>
                  </button>
                </div>
                <input type="hidden" name="is_smoker" id="val-is_smoker">
              </div>

              <div class="q-divider"></div>

              <div class="q-block" style="margin-bottom:0;">
                <span class="q-label">Are you currently breastfeeding?</span>
                <div class="opt-group" data-name="is_breastfeeding">
                  <button type="button" class="opt-btn" data-val="yes">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">Yes</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="no">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">No</div></div>
                  </button>
                </div>
                <input type="hidden" name="is_breastfeeding" id="val-is_breastfeeding">
              </div>

              <div class="q-nav">
                <button type="button" class="btn-prev" onclick="prevPanel(2)">Back</button>
                <span class="step-counter">2 of 5</span>
                <button type="button" class="btn-next" onclick="nextPanel(2)">Next</button>
              </div>
            </div>
          </div>

          <div class="q-panel" id="panel-3">
            <div class="q-card">
              <div class="q-section-label">Step 3 of 5</div>
              <div class="q-title">Family Planning</div>
              <div class="q-subtitle">This helps us understand whether you need short-term or long-term protection.</div>

              <div class="q-block">
                <span class="q-label">Do you want to have children in the future?</span>
                <div class="opt-group" data-name="wants_children">
                  <button type="button" class="opt-btn" data-val="yes">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">Yes</div><div class="opt-text-sub">I plan to have children someday</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="no">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">No</div><div class="opt-text-sub">I do not want children</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="unsure">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">Unsure</div><div class="opt-text-sub">I have not decided yet</div></div>
                  </button>
                </div>
                <input type="hidden" name="wants_children" id="val-wants_children">
              </div>

              <div id="children-when-wrap" style="display:none;" class="q-block">
                <div class="q-divider"></div>
                <span class="q-label">How soon do you plan to have children?</span>
                <div class="opt-group" data-name="children_when">
                  <button type="button" class="opt-btn" data-val="within_1yr">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">Within the next year</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="1_to_3yrs">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">In 1 to 3 years</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="3yrs_plus">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">More than 3 years from now</div></div>
                  </button>
                </div>
                <input type="hidden" name="children_when" id="val-children_when" value="not_applicable">
              </div>

              <div class="q-nav">
                <button type="button" class="btn-prev" onclick="prevPanel(3)">Back</button>
                <span class="step-counter">3 of 5</span>
                <button type="button" class="btn-next" onclick="nextPanel(3)">Next</button>
              </div>
            </div>
          </div>

          <div class="q-panel" id="panel-4">
            <div class="q-card">
              <div class="q-section-label">Step 4 of 5</div>
              <div class="q-title">Your Preferences</div>
              <div class="q-subtitle">Tell us what matters most so we can find the best match for you.</div>

              <div class="q-block">
                <span class="q-label">How do you prefer to use contraception?</span>
                <div class="opt-group" data-name="delivery_pref">
                  <button type="button" class="opt-btn" data-val="daily_pill">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">Daily pill</div><div class="opt-text-sub">Take a pill every day</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="monthly_injection">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">Injection</div><div class="opt-text-sub">Monthly or quarterly shot</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="long_term">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">Long-term / Set and forget</div><div class="opt-text-sub">IUD or implant — years of protection</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="barrier">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">Barrier method</div><div class="opt-text-sub">Condoms, diaphragm</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="natural">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">Natural method</div><div class="opt-text-sub">Fertility awareness, calendar method</div></div>
                  </button>
                </div>
                <input type="hidden" name="delivery_pref" id="val-delivery_pref">
              </div>

              <div class="q-divider"></div>

              <div class="q-block" style="margin-bottom:0;">
                <span class="q-label">How important is a hormone-free method to you?</span>
                <div class="opt-group" data-name="hormone_free_pref">
                  <button type="button" class="opt-btn" data-val="very_important">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">Very important</div><div class="opt-text-sub">I prefer to avoid hormones</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="somewhat">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">Somewhat important</div><div class="opt-text-sub">I am open to both</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="not_important">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">Not important</div><div class="opt-text-sub">Hormones are fine with me</div></div>
                  </button>
                </div>
                <input type="hidden" name="hormone_free_pref" id="val-hormone_free_pref">
              </div>

              <div class="q-nav">
                <button type="button" class="btn-prev" onclick="prevPanel(4)">Back</button>
                <span class="step-counter">4 of 5</span>
                <button type="button" class="btn-next" onclick="nextPanel(4)">Next</button>
              </div>
            </div>
          </div>

          <div class="q-panel" id="panel-5">
            <div class="q-card">
              <div class="q-section-label">Step 5 of 5</div>
              <div class="q-title">Budget & History</div>
              <div class="q-subtitle">Last step — tell us about your budget and prior experience.</div>

              <div class="q-block">
                <span class="q-label">What is your budget preference?</span>
                <div class="opt-group" data-name="budget_pref">
                  <button type="button" class="opt-btn" data-val="low">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">Low cost</div><div class="opt-text-sub">Free or very affordable options</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="medium">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">Moderate</div><div class="opt-text-sub">Willing to spend a reasonable amount</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="high">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">Not a concern</div><div class="opt-text-sub">Cost is not a deciding factor</div></div>
                  </button>
                </div>
                <input type="hidden" name="budget_pref" id="val-budget_pref">
              </div>

              <div class="q-divider"></div>

              <div class="q-block">
                <span class="q-label">Have you used contraception before?</span>
                <div class="opt-group" data-name="used_before">
                  <button type="button" class="opt-btn" data-val="yes">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">Yes</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="no">
                    <div class="opt-dot"><div class="opt-dot-inner"></div></div>
                    <div><div class="opt-text-main">No, this is my first time</div></div>
                  </button>
                </div>
                <input type="hidden" name="used_before" id="val-used_before">
              </div>

              <div id="prev-method-wrap" style="display:none;" class="q-block">
                <div class="q-divider"></div>
                <label class="q-label" for="previous_method">Which method did you use before? <span>(optional)</span></label>
                <input type="text" name="previous_method" id="previous_method" class="text-input" placeholder="e.g. Pills, condoms, IUD...">
              </div>

              <div class="q-nav">
                <button type="button" class="btn-prev" onclick="prevPanel(5)">Back</button>
                <span class="step-counter">5 of 5</span>
                <button type="submit" name="submit_questionnaire" class="btn-submit">
                  <i class="fa-solid fa-paper-plane"></i> Submit
                </button>
              </div>
            </div>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>

<script src="../assets/vendor/bootstrap-5/js/bootstrap.bundle.min.js"></script>
<script>
const TOTAL = 5;

function showPanel(n) {
  document.querySelectorAll('.q-panel').forEach(p => p.classList.remove('active'));
  document.getElementById('panel-' + n).classList.add('active');
  for (let i = 1; i <= TOTAL; i++) {
    const ind = document.getElementById('step-ind-' + i);
    ind.classList.remove('active', 'done');
    if (i < n)  ind.classList.add('done');
    if (i === n) ind.classList.add('active');
  }
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function nextPanel(n) {
  if (!validatePanel(n)) return;
  if (n < TOTAL) showPanel(n + 1);
}

function prevPanel(n) {
  if (n > 1) showPanel(n - 1);
}

function shakeError(el) {
  el.style.outline      = '2px solid var(--c-pink-d)';
  el.style.borderRadius = '10px';
  el.style.padding      = '4px';
  setTimeout(() => { el.style.outline = ''; el.style.padding = ''; }, 1800);
}

function validatePanel(n) {
  const panel = document.getElementById('panel-' + n);
  let valid   = true;

  panel.querySelectorAll('.opt-group[data-name]').forEach(g => {
    const name   = g.dataset.name;
    const hidden = document.getElementById('val-' + name);
    if (!hidden) return;

    if (name === 'children_when') {
      const wrap = document.getElementById('children-when-wrap');
      if (wrap && wrap.style.display === 'none') return;
    }

    if (!hidden.value) { shakeError(g); valid = false; }
  });

  if (n === 2) {
    const anyChecked = document.querySelector('#health-grid input[type=checkbox]:checked');
    if (!anyChecked) { shakeError(document.getElementById('health-grid')); valid = false; }
  }

  return valid;
}

document.addEventListener('click', function(e) {
  const btn = e.target.closest('.opt-btn');
  if (!btn) return;

  const group = btn.closest('[data-name]');
  if (!group) return;

  group.querySelectorAll('.opt-btn').forEach(b => b.classList.remove('selected'));
  btn.classList.add('selected');

  const name   = group.dataset.name;
  const hidden = document.getElementById('val-' + name);
  if (hidden) hidden.value = btn.dataset.val;

  if (name === 'wants_children') {
    const show = btn.dataset.val === 'yes';
    document.getElementById('children-when-wrap').style.display = show ? 'block' : 'none';
    if (!show) {
      document.getElementById('val-children_when').value = 'not_applicable';
      document.querySelectorAll('#children-when-wrap .opt-btn').forEach(b => b.classList.remove('selected'));
    }
  }

  if (name === 'used_before') {
    document.getElementById('prev-method-wrap').style.display = btn.dataset.val === 'yes' ? 'block' : 'none';
    if (btn.dataset.val === 'no') document.getElementById('previous_method').value = '';
  }
});

document.querySelectorAll('#health-grid .opt-check').forEach(el => {
  el.addEventListener('click', function(e) {
    e.preventDefault();
    const val = this.dataset.val;
    const cb  = this.querySelector('input[type=checkbox]');

    if (val === 'none') {
      const wasSelected = this.classList.contains('selected');
      document.querySelectorAll('#health-grid .opt-check').forEach(c => {
        c.classList.remove('selected');
        c.querySelector('input[type=checkbox]').checked = false;
      });
      if (!wasSelected) { this.classList.add('selected'); cb.checked = true; }
    } else {
      const noneEl = document.querySelector('#health-grid .opt-check[data-val="none"]');
      noneEl.classList.remove('selected');
      noneEl.querySelector('input[type=checkbox]').checked = false;
      const isSelected = !this.classList.contains('selected');
      this.classList.toggle('selected', isSelected);
      cb.checked = isSelected;
    }
  });
});
</script>
<?php include '../includes/user/chatbot_widget.php'; ?>
</body>
</html>