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
        'sexually_active'   => ['yes','no','prefer_not_to_say'],
        'wants_children'    => ['yes','no','unsure'],
        'children_when'     => ['within_1yr','1_to_3yrs','3yrs_plus','not_applicable'],
        'is_smoker'         => ['yes','no'],
        'is_breastfeeding'  => ['yes','no'],
        'hormone_free_pref' => ['very_important','somewhat','not_important'],
        'delivery_pref'     => ['daily_pill','weekly_patch','monthly_injection','long_term','barrier','natural'],
        'budget_pref'       => ['low','medium','high'],
        'used_before'       => ['yes','no'],
    ];

    $sexually_active   = trim($_POST['sexually_active']   ?? '');
    $wants_children    = trim($_POST['wants_children']    ?? '');
    $children_when     = trim($_POST['children_when']     ?? 'not_applicable');
    $is_smoker         = trim($_POST['is_smoker']         ?? '');
    $is_breastfeeding  = trim($_POST['is_breastfeeding']  ?? '');
    $hormone_free_pref = trim($_POST['hormone_free_pref'] ?? '');
    $delivery_pref     = trim($_POST['delivery_pref']     ?? '');
    $budget_pref       = trim($_POST['budget_pref']       ?? '');
    $used_before       = trim($_POST['used_before']       ?? '');
    $previous_method   = substr(trim($_POST['previous_method'] ?? ''), 0, 100);

    if ($wants_children !== 'yes') {
        $children_when = 'not_applicable';
    }
    if (empty($children_when)) {
        $children_when = 'not_applicable';
    }

    $valid_conditions  = ['none','hypertension','migraines','diabetes','blood_clots','liver_disease','depression'];
    $raw_conditions    = isset($_POST['health_conditions']) && is_array($_POST['health_conditions'])
                         ? $_POST['health_conditions'] : ['none'];
    $clean_conditions  = array_filter($raw_conditions, fn($v) => in_array($v, $valid_conditions));
    $health_conditions = !empty($clean_conditions) ? implode(',', $clean_conditions) : 'none';

    $fields_to_check = compact(
        'sexually_active','wants_children','children_when',
        'is_smoker','is_breastfeeding','hormone_free_pref',
        'delivery_pref','budget_pref','used_before'
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
        $sexually_active   = mysqli_real_escape_string($conn, $sexually_active);
        $wants_children    = mysqli_real_escape_string($conn, $wants_children);
        $children_when     = mysqli_real_escape_string($conn, $children_when);
        $health_conditions = mysqli_real_escape_string($conn, $health_conditions);
        $is_smoker         = mysqli_real_escape_string($conn, $is_smoker);
        $is_breastfeeding  = mysqli_real_escape_string($conn, $is_breastfeeding);
        $hormone_free_pref = mysqli_real_escape_string($conn, $hormone_free_pref);
        $delivery_pref     = mysqli_real_escape_string($conn, $delivery_pref);
        $budget_pref       = mysqli_real_escape_string($conn, $budget_pref);
        $used_before       = mysqli_real_escape_string($conn, $used_before);
        $previous_method   = mysqli_real_escape_string($conn, $previous_method);

        $q = "INSERT INTO questionnaire_responses
                (user_id, sexually_active, wants_children, children_when, health_conditions,
                 is_smoker, is_breastfeeding, hormone_free_pref, delivery_pref, budget_pref,
                 used_before, previous_method)
              VALUES
                ($user_id, '$sexually_active', '$wants_children', '$children_when', '$health_conditions',
                 '$is_smoker', '$is_breastfeeding', '$hormone_free_pref', '$delivery_pref', '$budget_pref',
                 '$used_before', '$previous_method')";

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
$user_name   = $user_row['username']  ?? $_SESSION['username'] ?? 'User';
$user_age    = 'Not specified';
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
      --blue-50: #e6f1fb; --blue-600: #185FA5; --blue-800: #0C447C;
    }
    html, body { height: 100%; font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--text-primary); font-size: 14px; }
    .cc-layout { display: flex; height: 100vh; overflow: hidden; }
    .cc-main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
    .topbar { height: 52px; background: var(--surface); border-bottom: 0.5px solid var(--border-md); display: flex; align-items: center; justify-content: space-between; padding: 0 24px; flex-shrink: 0; }
    .topbar-left { display: flex; align-items: center; gap: 8px; }
    .topbar-title { font-family: 'Playfair Display', serif; font-size: 14px; color: var(--text-primary); }
    .topbar-title em { font-style: italic; color: var(--blue-600); }
    .topbar-sep { color: var(--text-muted); }
    .topbar-page { font-size: 13px; color: var(--text-secondary); }
    .topbar-user { font-size: 12px; background: var(--blue-50); color: var(--blue-800); padding: 3px 10px; border-radius: 20px; font-weight: 500; }
    .content-area { flex: 1; overflow-y: auto; padding: 28px; }

    .q-steps { display: flex; align-items: center; margin-bottom: 32px; max-width: 640px; }
    .q-step { display: flex; align-items: center; flex: 1; }
    .q-step:last-child { flex: 0; }
    .step-circle { width: 28px; height: 28px; border-radius: 50%; border: 2px solid var(--border-md); background: var(--surface); color: var(--text-muted); font-size: 12px; font-weight: 500; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s; }
    .step-line { flex: 1; height: 1px; background: var(--border-md); margin: 0 4px; }
    .q-step.done .step-circle { background: var(--blue-600); border-color: var(--blue-600); color: #fff; }
    .q-step.active .step-circle { border-color: var(--blue-600); color: var(--blue-600); font-weight: 700; }

    .q-panel { display: none; animation: fadeUp 0.25s ease both; }
    .q-panel.active { display: block; }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }

    .q-card { background: var(--surface); border: 0.5px solid var(--border-md); border-radius: 16px; padding: 28px 28px 24px; max-width: 640px; }
    .q-section-label { font-size: 11px; font-weight: 500; letter-spacing: 0.1em; text-transform: uppercase; color: var(--blue-600); margin-bottom: 6px; }
    .q-title { font-family: 'Playfair Display', serif; font-size: 20px; font-weight: 500; margin-bottom: 4px; }
    .q-subtitle { font-size: 13px; color: var(--text-secondary); margin-bottom: 24px; }

    .opt-group { display: flex; flex-direction: column; gap: 8px; }
    .opt-btn { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border: 1px solid var(--border-md); border-radius: 10px; background: var(--bg); cursor: pointer; text-align: left; transition: border-color 0.15s, background 0.15s; width: 100%; font-family: inherit; font-size: 14px; color: var(--text-primary); }
    .opt-btn:hover { border-color: var(--blue-600); background: var(--blue-50); }
    .opt-btn.selected { border-color: var(--blue-600); background: var(--blue-50); color: var(--blue-800); }
    .opt-label { font-weight: 500; line-height: 1.2; }
    .opt-desc { font-size: 12px; color: var(--text-secondary); margin-top: 1px; }

    .opt-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .opt-check { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 1px solid var(--border-md); border-radius: 10px; background: var(--bg); cursor: pointer; transition: border-color 0.15s, background 0.15s; user-select: none; }
    .opt-check:hover { border-color: var(--blue-600); background: var(--blue-50); }
    .opt-check.selected { border-color: var(--blue-600); background: var(--blue-50); color: var(--blue-800); }
    .opt-check input[type=checkbox] { display: none; }
    .check-box { width: 16px; height: 16px; border: 1.5px solid var(--border-md); border-radius: 4px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.15s; }
    .opt-check.selected .check-box { background: var(--blue-600); border-color: var(--blue-600); }
    .check-icon { display: none; color: #fff; font-size: 9px; }
    .opt-check.selected .check-icon { display: block; }

    .q-nav { display: flex; justify-content: space-between; align-items: center; margin-top: 24px; padding-top: 20px; border-top: 0.5px solid var(--border); }
    .btn-prev { background: none; border: 1px solid var(--border-md); border-radius: 8px; padding: 9px 20px; font-size: 13px; font-family: inherit; cursor: pointer; color: var(--text-secondary); transition: background 0.12s; }
    .btn-prev:hover { background: var(--bg); }
    .btn-next { background: var(--blue-600); border: none; border-radius: 8px; padding: 9px 24px; font-size: 13px; font-family: inherit; cursor: pointer; color: #fff; font-weight: 500; transition: background 0.12s; }
    .btn-next:hover { background: var(--blue-800); }
    .btn-submit { background: #27500A; border: none; border-radius: 8px; padding: 9px 24px; font-size: 13px; font-family: inherit; cursor: pointer; color: #fff; font-weight: 500; transition: background 0.12s; }
    .btn-submit:hover { background: #1d3a07; }
    .step-counter { font-size: 12px; color: var(--text-muted); }

    .notice-bar { background: var(--blue-50); border: 1px solid rgba(24,95,165,0.2); border-radius: 10px; padding: 12px 16px; display: flex; align-items: center; gap: 10px; margin-bottom: 20px; font-size: 13px; color: var(--blue-800); max-width: 640px; }
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
        <span class="topbar-page"><?= htmlspecialchars($page_title)?></span>
      </div>
    </div>

    <div class="content-area">

      <?php if ($success): ?>
      <div class="notice-bar" style="background:#eaf3de;border-color:rgba(39,80,10,0.2);color:#27500A;">
        <i class="fa-solid fa-circle-check"></i> <?= $success ?>
        <a href="/hci/user/recommendations.php" class="ms-2 fw-medium" style="color:#27500A;">View Recommendations →</a>
      </div>
      <?php endif; ?>

      <?php if ($error): ?>
      <div class="notice-bar" style="background:#fcebeb;border-color:rgba(163,45,45,0.2);color:#A32D2D;">
        <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <?php if ($has_existing && !$success): ?>
      <div class="notice-bar">
        <i class="fa-solid fa-rotate"></i>
        You've already completed the questionnaire.
        <a href="#" onclick="document.getElementById('q-form-wrap').style.display='block';this.closest('.notice-bar').style.display='none';return false;" class="ms-2 fw-medium" style="color:var(--blue-800);">Retake →</a>
        <a href="/hci/user/recommendations.php" class="ms-2 fw-medium" style="color:var(--blue-800);">View results →</a>
      </div>
      <div id="q-form-wrap" style="display:none;">
      <?php else: ?>
      <div id="q-form-wrap">
      <?php endif; ?>

        <div class="q-steps" id="q-steps">
          <?php for ($i = 1; $i <= 4; $i++): ?>
            <div class="q-step <?= $i === 1 ? 'active' : '' ?>" id="step-ind-<?= $i ?>">
              <div class="step-circle"><?= $i ?></div>
              <?php if ($i < 4): ?><div class="step-line"></div><?php endif; ?>
            </div>
          <?php endfor; ?>
        </div>

        <form method="POST" id="q-form">

          <div class="q-panel active" id="panel-1">
            <div class="q-card">
              <div class="q-section-label">Step 1 of 4</div>
              <div class="q-title">About You</div>
              <div class="q-subtitle">Please answer a few questions to help us understand your needs.</div>

              <div class="mb-4">
                <div class="fw-medium mb-2" style="font-size:13px;">Are you currently sexually active?</div>
                <div class="opt-group" data-name="sexually_active">
                  <button type="button" class="opt-btn" data-val="yes">
                    <div><div class="opt-label">Yes</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="no">
                    <div><div class="opt-label">No</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="prefer_not_to_say">
                    <div><div class="opt-label">Prefer not to say</div></div>
                  </button>
                </div>
                <input type="hidden" name="sexually_active" id="val-sexually_active">
              </div>

              <div class="mb-2">
                <div class="fw-medium mb-2" style="font-size:13px;">Are you currently breastfeeding?</div>
                <div class="opt-group" data-name="is_breastfeeding">
                  <button type="button" class="opt-btn" data-val="yes">
                    <div><div class="opt-label">Yes</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="no">
                    <div><div class="opt-label">No</div></div>
                  </button>
                </div>
                <input type="hidden" name="is_breastfeeding" id="val-is_breastfeeding">
              </div>

              <div class="q-nav">
                <span class="step-counter">1 of 4</span>
                <button type="button" class="btn-next" onclick="nextPanel(1)">Next →</button>
              </div>
            </div>
          </div>

          <div class="q-panel" id="panel-2">
            <div class="q-card">
              <div class="q-section-label">Step 2 of 4</div>
              <div class="q-title">Your Health</div>
              <div class="q-subtitle">This helps us avoid recommending methods that may not be safe for you.</div>

              <div class="mb-4">
                <div class="fw-medium mb-2" style="font-size:13px;">Do you have any of these health conditions? <span style="color:var(--text-muted);font-weight:400;">(Select all that apply)</span></div>
                <div class="opt-grid" id="health-grid">
                  <?php
                  $conditions = [
                    'none'         => 'None / Not sure',
                    'hypertension' => 'High blood pressure',
                    'migraines'    => 'Migraines',
                    'diabetes'     => 'Diabetes',
                    'blood_clots'  => 'History of blood clots',
                    'liver_disease'=> 'Liver disease',
                    'depression'   => 'Depression / Anxiety',
                  ];
                  foreach ($conditions as $val => $label): ?>
                  <label class="opt-check" data-val="<?= $val ?>">
                    <input type="checkbox" name="health_conditions[]" value="<?= $val ?>">
                    <div class="check-box">
                      <i class="fa-solid fa-check check-icon"></i>
                    </div>
                    <span style="font-size:13px;"><?= $label ?></span>
                  </label>
                  <?php endforeach; ?>
                </div>
              </div>

              <div class="mb-2">
                <div class="fw-medium mb-2" style="font-size:13px;">Do you smoke?</div>
                <div class="opt-group" data-name="is_smoker">
                  <button type="button" class="opt-btn" data-val="yes">
                    <div><div class="opt-label">Yes</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="no">
                    <div><div class="opt-label">No</div></div>
                  </button>
                </div>
                <input type="hidden" name="is_smoker" id="val-is_smoker">
              </div>

              <div class="q-nav">
                <button type="button" class="btn-prev" onclick="prevPanel(2)">← Back</button>
                <span class="step-counter">2 of 4</span>
                <button type="button" class="btn-next" onclick="nextPanel(2)">Next →</button>
              </div>
            </div>
          </div>

          <div class="q-panel" id="panel-3">
            <div class="q-card">
              <div class="q-section-label">Step 3 of 4</div>
              <div class="q-title">Family Planning</div>
              <div class="q-subtitle">This helps us understand whether you need short-term or long-term protection.</div>

              <div class="mb-4">
                <div class="fw-medium mb-2" style="font-size:13px;">Do you want to have children in the future?</div>
                <div class="opt-group" data-name="wants_children">
                  <button type="button" class="opt-btn" data-val="yes">
                    <div><div class="opt-label">Yes</div><div class="opt-desc">I plan to have children someday</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="no">
                    <div><div class="opt-label">No</div><div class="opt-desc">I do not want children</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="unsure">
                    <div><div class="opt-label">Unsure</div><div class="opt-desc">I haven't decided yet</div></div>
                  </button>
                </div>
                <input type="hidden" name="wants_children" id="val-wants_children">
              </div>

              <div id="children-when-wrap" style="display:none;" class="mb-2">
                <div class="fw-medium mb-2" style="font-size:13px;">How soon do you plan to have children?</div>
                <div class="opt-group" data-name="children_when">
                  <button type="button" class="opt-btn" data-val="within_1yr">
                    <div><div class="opt-label">Within the next year</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="1_to_3yrs">
                    <div><div class="opt-label">In 1–3 years</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="3yrs_plus">
                    <div><div class="opt-label">More than 3 years from now</div></div>
                  </button>
                </div>
                <input type="hidden" name="children_when" id="val-children_when" value="not_applicable">
              </div>

              <div class="q-nav">
                <button type="button" class="btn-prev" onclick="prevPanel(3)">← Back</button>
                <span class="step-counter">3 of 4</span>
                <button type="button" class="btn-next" onclick="nextPanel(3)">Next →</button>
              </div>
            </div>
          </div>

          <div class="q-panel" id="panel-4">
            <div class="q-card">
              <div class="q-section-label">Step 4 of 4</div>
              <div class="q-title">Your Preferences</div>
              <div class="q-subtitle">Tell us what matters most to you so we can find the best match.</div>

              <div class="mb-4">
                <div class="fw-medium mb-2" style="font-size:13px;">How do you prefer to use contraception?</div>
                <div class="opt-group" data-name="delivery_pref">
                  <button type="button" class="opt-btn" data-val="daily_pill">
                    <div><div class="opt-label">Daily pill</div><div class="opt-desc">Take a pill every day</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="monthly_injection">
                    <div><div class="opt-label">Injection</div><div class="opt-desc">Monthly or quarterly shot</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="long_term">
                    <div><div class="opt-label">Long-term / Set and forget</div><div class="opt-desc">IUD, implant — years of protection</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="barrier">
                    <div><div class="opt-label">Barrier method</div><div class="opt-desc">Condoms, diaphragm</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="natural">
                    <div><div class="opt-label">Natural method</div><div class="opt-desc">Fertility awareness, calendar</div></div>
                  </button>
                </div>
                <input type="hidden" name="delivery_pref" id="val-delivery_pref">
              </div>

              <div class="mb-4">
                <div class="fw-medium mb-2" style="font-size:13px;">How important is a hormone-free method to you?</div>
                <div class="opt-group" data-name="hormone_free_pref">
                  <button type="button" class="opt-btn" data-val="very_important">
                    <div><div class="opt-label">Very important</div><div class="opt-desc">I prefer to avoid hormones</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="somewhat">
                    <div><div class="opt-label">Somewhat important</div><div class="opt-desc">I'm open to both</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="not_important">
                    <div><div class="opt-label">Not important</div><div class="opt-desc">Hormones are fine</div></div>
                  </button>
                </div>
                <input type="hidden" name="hormone_free_pref" id="val-hormone_free_pref">
              </div>

              <div class="mb-4">
                <div class="fw-medium mb-2" style="font-size:13px;">What is your budget preference?</div>
                <div class="opt-group" data-name="budget_pref">
                  <button type="button" class="opt-btn" data-val="low">
                    <div><div class="opt-label">Low cost</div><div class="opt-desc">Free or very affordable options</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="medium">
                    <div><div class="opt-label">Moderate</div><div class="opt-desc">Willing to spend a reasonable amount</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="high">
                    <div><div class="opt-label">Not a concern</div><div class="opt-desc">Cost is not a deciding factor</div></div>
                  </button>
                </div>
                <input type="hidden" name="budget_pref" id="val-budget_pref">
              </div>

              <div class="mb-4">
                <div class="fw-medium mb-2" style="font-size:13px;">Have you used contraception before?</div>
                <div class="opt-group" data-name="used_before">
                  <button type="button" class="opt-btn" data-val="yes">
                    <div><div class="opt-label">Yes</div></div>
                  </button>
                  <button type="button" class="opt-btn" data-val="no">
                    <div><div class="opt-label">No, this is my first time</div></div>
                  </button>
                </div>
                <input type="hidden" name="used_before" id="val-used_before">
              </div>

              <div id="prev-method-wrap" style="display:none;" class="mb-2">
                <label class="fw-medium mb-1 d-block" style="font-size:13px;">Which method did you use? <span style="color:var(--text-muted);font-weight:400;">(optional)</span></label>
                <input type="text" name="previous_method" id="previous_method" class="form-control form-control-sm" placeholder="e.g. Pills, condoms, IUD...">
              </div>

              <div class="q-nav">
                <button type="button" class="btn-prev" onclick="prevPanel(4)">← Back</button>
                <span class="step-counter">4 of 4</span>
                <button type="submit" name="submit_questionnaire" class="btn-submit">
                  <i class="fa-solid fa-paper-plane me-1"></i> Submit
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
const TOTAL = 4;

function showPanel(n) {
  document.querySelectorAll('.q-panel').forEach(p => p.classList.remove('active'));
  document.getElementById('panel-' + n).classList.add('active');
  for (let i = 1; i <= TOTAL; i++) {
    const ind = document.getElementById('step-ind-' + i);
    ind.classList.remove('active', 'done');
    if (i < n)  ind.classList.add('done');
    if (i === n) ind.classList.add('active');
  }
}

function nextPanel(n) {
  if (!validatePanel(n)) return;
  if (n < TOTAL) showPanel(n + 1);
}

function prevPanel(n) {
  if (n > 1) showPanel(n - 1);
}

function validatePanel(n) {
  const panel  = document.getElementById('panel-' + n);
  let valid    = true;

  panel.querySelectorAll('.opt-group[data-name]').forEach(g => {
    const name   = g.dataset.name;
    const hidden = document.getElementById('val-' + name);
    if (!hidden) return;

    if (name === 'children_when') {
      const wrap = document.getElementById('children-when-wrap');
      if (wrap && wrap.style.display === 'none') return;
    }

    if (!hidden.value) {
      g.style.outline      = '2px solid #e05252';
      g.style.borderRadius = '10px';
      g.style.padding      = '4px';
      setTimeout(() => { g.style.outline = ''; g.style.padding = ''; }, 1800);
      valid = false;
    }
  });

  if (n === 2) {
    const anyChecked = document.querySelector('#health-grid input[type=checkbox]:checked');
    if (!anyChecked) {
      const grid = document.getElementById('health-grid');
      grid.style.outline      = '2px solid #e05252';
      grid.style.borderRadius = '10px';
      grid.style.padding      = '4px';
      setTimeout(() => { grid.style.outline = ''; grid.style.padding = ''; }, 1800);
      valid = false;
    }
  }

  return valid;
}

document.addEventListener('click', function(e) {
  const btn = e.target.closest('.opt-btn');
  if (!btn) return;

  const group = btn.closest('.opt-group[data-name]');
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
  el.addEventListener('click', function (e) {
    e.preventDefault();
    const val = this.dataset.val;
    const cb  = this.querySelector('input[type=checkbox]');

    if (val === 'none') {
      const wasSelected = this.classList.contains('selected');
      document.querySelectorAll('#health-grid .opt-check').forEach(c => {
        c.classList.remove('selected');
        c.querySelector('input[type=checkbox]').checked = false;
      });
      if (!wasSelected) {
        this.classList.add('selected');
        cb.checked = true;
      }
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
</body>
</html>