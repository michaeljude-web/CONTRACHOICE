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

function scoreColor(int $score): string {
    if ($score >= 90) return '#27ae60';
    if ($score >= 75) return '#8fbe3a';
    if ($score >= 65) return '#f5a623';
    if ($score >= 50) return '#f47c3c';
    return '#e74c3c';
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
  <link rel="stylesheet" href="../assets/css/user/style.css">
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

      --orange:        #f47c3c;
      --orange-deep:   #d95f1a;
      --orange-light:  #f9a06a;
      --orange-pale:   #fff0e8;
      --orange-soft:   #fff7f2;
      --orange-mid:    #fde4d0;
      --amber:         #f5a623;
      --amber-pale:    #fef6e0;
      --coral:         #f26b5e;
      --coral-pale:    #fdecea;
      --brown-soft:    #a0522d;
      --ink:           #2a1a0e;
      --ink-soft:      #4a3020;
      --muted-lt:      #d4b89a;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; font-family: 'Nunito', sans-serif; background: var(--bg); color: var(--ink); font-size: 14px; -webkit-font-smoothing: antialiased; }
    .cc-layout { display: flex; height: 100vh; overflow: hidden; }
    .cc-main   { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: var(--bg); }

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

    .content-area { flex: 1; overflow-y: auto; padding: 32px 28px; }
    .content-area::-webkit-scrollbar { width: 5px; }
    .content-area::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }

    .page-header { max-width: 980px; margin-bottom: 28px; display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 16px; }
    .page-header-left h1 { font-family: 'Quicksand', sans-serif; font-size: 28px; font-weight: 700; color: var(--ink); margin-bottom: 4px; }
    .page-header-left h1 span { color: var(--orange); }
    .page-header-left p { font-size: 13px; color: var(--muted); margin: 0; font-weight: 500; }
    .retake-btn {
      display: inline-flex; align-items: center; gap: 7px;
      font-size: 12.5px; font-weight: 700; padding: 9px 20px; border-radius: 30px;
      background: var(--orange-pale); color: var(--orange-deep);
      text-decoration: none; border: 2px solid rgba(217,95,26,0.2);
      transition: background 0.18s, transform 0.15s; white-space: nowrap;
    }
    .retake-btn:hover { background: var(--orange); color: #fff; transform: translateY(-1px); text-decoration: none; border-color: var(--orange); }

    .recommendation-top-pick-wrap { max-width: 980px; margin-bottom: 28px; }
    .recommendation-top-pick-card {
      border-radius: 24px; overflow: hidden;
      display: grid; grid-template-columns: 320px 1fr; min-height: 290px;
      box-shadow: 0 8px 48px rgba(244,124,60,0.20);
      animation: slideUp 0.5s ease both;
      border: 2px solid rgba(244,124,60,0.15);
    }
    @keyframes slideUp { from { opacity:0; transform: translateY(20px); } to { opacity:1; transform:none; } }

    .recommendation-top-pick-img-side { position: relative; overflow: hidden; background: var(--orange-pale); }
    .recommendation-top-pick-img-side img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .recommendation-top-pick-img-placeholder {
      width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
      background: linear-gradient(135deg, var(--orange-pale) 0%, var(--amber-pale) 100%);
    }
    .recommendation-top-pick-img-placeholder .method-icon {
      width: 80px; height: 80px; background: var(--orange); border-radius: 50%;
      display: flex; align-items: center; justify-content: center; color: #fff; font-size: 32px;
    }
    .recommendation-best-match-ribbon {
      position: absolute; top: 18px; left: -2px;
      background: var(--orange); color: #fff;
      font-size: 10px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase;
      padding: 6px 16px 6px 14px; border-radius: 0 30px 30px 0;
      box-shadow: 0 3px 12px rgba(217,95,26,0.40);
    }
    .recommendation-top-pick-info-side {
      background: linear-gradient(135deg, #fffaf6 0%, #fff7ee 100%);
      padding: 36px 40px; display: flex; flex-direction: column; justify-content: center; gap: 16px;
    }
    .recommendation-top-method-name { font-family: 'Quicksand', sans-serif; font-size: 28px; font-weight: 700; color: var(--ink); line-height: 1.15; }
    .recommendation-top-method-desc { font-size: 13.5px; color: var(--ink-soft); line-height: 1.75; font-weight: 400; }
    .recommendation-top-score-row { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .recommendation-top-score-pill {
      display: inline-flex; align-items: center; gap: 8px;
      background: var(--orange); color: #fff; font-size: 13px; font-weight: 800;
      padding: 8px 20px; border-radius: 30px; box-shadow: 0 3px 12px rgba(244,124,60,0.35);
    }
    .recommendation-top-eff-pill {
      display: inline-flex; align-items: center; gap: 6px;
      background: var(--orange-pale); color: var(--orange-deep);
      font-size: 12.5px; font-weight: 700; padding: 7px 16px; border-radius: 30px;
      border: 1.5px solid rgba(217,95,26,0.2);
    }
    .recommendation-top-reasons-row { display: flex; flex-wrap: wrap; gap: 7px; }
    .recommendation-reason-chip {
      display: inline-flex; align-items: center; gap: 5px;
      font-size: 11.5px; font-weight: 700; padding: 5px 13px; border-radius: 30px;
      background: var(--orange-mid); color: var(--orange-deep);
    }
    .recommendation-reason-chip i { font-size: 9px; }
    .recommendation-top-detail-btn {
      display: inline-flex; align-items: center; gap: 8px;
      background: var(--orange); color: #fff; font-size: 13px; font-weight: 700;
      padding: 10px 24px; border-radius: 30px; border: none; cursor: pointer;
      align-self: flex-start; transition: background 0.18s, transform 0.15s;
      box-shadow: 0 3px 14px rgba(244,124,60,0.3);
    }
    .recommendation-top-detail-btn:hover { background: var(--orange-deep); transform: translateY(-1px); }

    .recommendation-others-label {
      max-width: 980px; font-size: 11px; font-weight: 800; letter-spacing: 0.15em;
      text-transform: uppercase; color: var(--muted); margin-bottom: 10px;
      display: flex; align-items: center; gap: 12px;
    }
    .recommendation-others-label::after { content:''; flex:1; height:1.5px; background: var(--orange-mid); }

    .score-legend {
      max-width: 980px; display: flex; align-items: center; gap: 8px;
      flex-wrap: wrap; margin-bottom: 16px;
      font-size: 11px; font-weight: 700; color: var(--muted);
    }
    .score-legend strong { color: var(--ink-soft); font-size: 11px; margin-right: 2px; }
    .legend-item {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 3px 10px 3px 7px; border-radius: 20px;
      background: rgba(0,0,0,.04);
    }
    .legend-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; flex-shrink: 0; }

    .recommendation-list { max-width: 980px; display: flex; flex-direction: column; gap: 12px; }

    .recommendation-card {
      background: var(--surface); border: 2px solid var(--border);
      border-radius: 18px; overflow: hidden;
      display: grid; grid-template-columns: 130px 1fr auto;
      transition: box-shadow 0.18s, border-color 0.18s, transform 0.15s;
      cursor: pointer; animation: slideUp 0.4s ease both;
    }
    .recommendation-card:hover { border-color: var(--orange); box-shadow: 0 6px 30px rgba(244,124,60,0.18); transform: translateY(-2px); }
    .recommendation-card:nth-child(1) { animation-delay: 0.06s; }
    .recommendation-card:nth-child(2) { animation-delay: 0.12s; }
    .recommendation-card:nth-child(3) { animation-delay: 0.18s; }
    .recommendation-card:nth-child(4) { animation-delay: 0.24s; }

    .recommendation-card-img-col { position: relative; overflow: hidden; min-height: 130px; }
    .recommendation-card-img-col img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .recommendation-card-img-placeholder {
      width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
    }
    .recommendation-card-img-placeholder .recommendation-method-icon-sm {
      width: 52px; height: 52px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center; color: #fff; font-size: 20px;
    }

    .recommendation-rank-badge {
      position: absolute; top: 10px; left: 10px;
      width: 34px; height: 34px; border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 13px; font-weight: 800; color: #fff;
      box-shadow: 0 2px 10px rgba(0,0,0,0.18);
    }

    .recommendation-card-body-col { padding: 18px 20px; display: flex; flex-direction: column; justify-content: center; gap: 8px; }
    .recommendation-card-name-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .recommendation-card-name { font-family: 'Quicksand', sans-serif; font-size: 17px; font-weight: 700; color: var(--ink); }

    .recommendation-cat-pill { font-size: 10px; font-weight: 800; letter-spacing: 0.07em; text-transform: uppercase; padding: 3px 10px; border-radius: 30px; }
    .recommendation-cat-hormonal  { background: var(--amber-pale);  color: #a06d00; }
    .recommendation-cat-barrier   { background: var(--orange-pale); color: var(--orange-deep); }
    .recommendation-cat-long_term { background: var(--coral-pale);  color: var(--coral); }
    .recommendation-cat-natural   { background: #f0f8ee;            color: #3a7a2a; }
    .recommendation-cat-emergency { background: var(--orange-mid);  color: var(--brown); }

    .recommendation-card-desc { font-size: 13px; color: var(--ink-soft); line-height: 1.6; font-weight: 400; }
    .recommendation-card-chips { display: flex; flex-wrap: wrap; gap: 6px; }
    .recommendation-chip { display: inline-flex; align-items: center; gap: 5px; font-size: 11.5px; font-weight: 700; padding: 4px 11px; border-radius: 30px; }
    .recommendation-chip-eff   { background: #e8f8ee;           color: #2a7a4a; }
    .recommendation-chip-cost  { background: var(--amber-pale);  color: #8a6000; }
    .recommendation-chip-match { background: var(--orange-pale); color: var(--orange-deep); }

    .recommendation-card-score-col {
      padding: 18px; display: flex; flex-direction: column;
      align-items: center; justify-content: center; gap: 8px;
      border-left: 1.5px solid var(--orange-mid);
      min-width: 88px; background: var(--orange-soft);
    }
    .recommendation-score-ring { width: 64px; height: 64px; position: relative; }
    .recommendation-score-ring svg { width: 64px; height: 64px; transform: rotate(-90deg); }
    .recommendation-score-ring .recommendation-ring-bg { fill: none; stroke: var(--orange-mid); stroke-width: 5; }
    .recommendation-score-label { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    .recommendation-score-num-val { font-size: 16px; font-weight: 800; line-height: 1; }
    .recommendation-score-num-sub { font-size: 9px; color: var(--muted); font-weight: 600; }
    .recommendation-score-caption { font-size: 10px; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }

    .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 70%; text-align: center; padding: 24px; }
    .empty-icon-wrap { width: 80px; height: 80px; border-radius: 50%; background: var(--orange-pale); display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 20px; color: var(--orange); }
    .empty-state h3 { font-family: 'Quicksand', sans-serif; font-size: 22px; font-weight: 700; color: var(--ink); margin-bottom: 8px; }
    .empty-state p  { font-size: 13.5px; color: var(--muted); max-width: 360px; line-height: 1.7; margin-bottom: 24px; font-weight: 500; }
    .btn-start { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; background: var(--orange); color: #fff; border-radius: 30px; font-size: 13.5px; font-weight: 800; text-decoration: none; transition: background 0.2s, transform 0.15s; }
    .btn-start:hover { background: var(--orange-deep); transform: translateY(-2px); color: #fff; text-decoration: none; }

    .recommendation-modal-overlay {
      display: none; position: fixed; top: 0; left: 0;
      width: 100vw; height: 100vh; z-index: 99999;
      animation: fadeIn 0.22s ease;
    }
    .recommendation-modal-overlay.open { display: block; }
    @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }

    .recommendation-modal-box {
      display: grid; grid-template-columns: 400px 1fr;
      width: 100vw; height: 100vh; background: var(--surface);
      animation: modalSlide 0.3s cubic-bezier(0.34,1.1,0.64,1) both;
    }
    @keyframes modalSlide { from { opacity:0; transform: translateX(30px); } to { opacity:1; transform:none; } }

    .recommendation-modal-left-panel { position: relative; overflow: hidden; background: var(--orange-pale); height: 100vh; }
    .recommendation-modal-left-panel img {
      max-width: 100%; max-height: 100%; object-fit: contain; display: block;
      position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
    }
    .recommendation-modal-left-placeholder {
      width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
      background: linear-gradient(160deg, #fff0e8 0%, #fef6e0 50%, #fce8ef 100%);
      position: relative; z-index: 1;
    }
    .recommendation-modal-left-placeholder .recommendation-hero-icon {
      width: 140px; height: 140px; background: var(--orange); border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: 56px; box-shadow: 0 16px 48px rgba(244,124,60,0.35);
    }
    .recommendation-modal-left-overlay {
      position: absolute; inset: 0;
      background: linear-gradient(to top, rgba(42,26,14,0.65) 0%, rgba(42,26,14,0.1) 55%, transparent 100%);
      z-index: 2;
    }
    .recommendation-modal-left-bottom { position: absolute; bottom: 0; left: 0; right: 0; padding: 36px 40px; z-index: 3; }
    .recommendation-modal-left-rank {
      display: inline-flex; align-items: center; gap: 7px;
      background: var(--orange); color: #fff;
      font-size: 11px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase;
      padding: 7px 20px; border-radius: 30px; margin-bottom: 14px;
      box-shadow: 0 3px 14px rgba(244,124,60,0.45);
    }
    .recommendation-modal-left-name { font-family: 'Quicksand', sans-serif; font-size: 36px; font-weight: 700; color: #fff; line-height: 1.15; text-shadow: 0 2px 16px rgba(0,0,0,0.4); }

    .recommendation-modal-right-panel { display: flex; flex-direction: column; height: 100vh; overflow: hidden; background: #fdfaf7; }
    .recommendation-modal-right-topbar {
      display: flex; align-items: center; justify-content: space-between;
      padding: 22px 40px; border-bottom: 2px solid var(--orange-mid);
      flex-shrink: 0; background: var(--surface);
    }
    .recommendation-modal-right-topbar-title { font-family: 'Quicksand', sans-serif; font-size: 17px; font-weight: 700; color: var(--ink); }
    .recommendation-modal-close-btn {
      width: 40px; height: 40px; border-radius: 12px;
      background: var(--orange-pale); border: 2px solid var(--orange-mid);
      cursor: pointer; display: flex; align-items: center; justify-content: center;
      color: var(--orange-deep); font-size: 16px;
      transition: background 0.15s, transform 0.15s; flex-shrink: 0;
    }
    .recommendation-modal-close-btn:hover { background: var(--orange); color: #fff; transform: scale(1.05); }

    .recommendation-modal-right-scroll {
      flex: 1; overflow-y: auto; padding: 36px 40px 48px;
      scrollbar-width: thin; scrollbar-color: var(--orange-mid) transparent;
    }
    .recommendation-modal-right-scroll::-webkit-scrollbar { width: 6px; }
    .recommendation-modal-right-scroll::-webkit-scrollbar-thumb { background: var(--orange-mid); border-radius: 10px; }
    .recommendation-modal-right-scroll::-webkit-scrollbar-thumb:hover { background: var(--orange-light); }

    .recommendation-modal-pills-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 32px; }
    .recommendation-modal-score-pill { display: inline-flex; align-items: center; gap: 7px; color: #fff; font-size: 12.5px; font-weight: 800; padding: 9px 20px; border-radius: 30px; }
    .recommendation-modal-eff-pill   { display: inline-flex; align-items: center; gap: 6px; background: #e8f8ee;   color: #2a7a4a; font-size: 12.5px; font-weight: 700; padding: 9px 18px; border-radius: 30px; }
    .recommendation-modal-cost-pill  { display: inline-flex; align-items: center; gap: 6px; background: #fef6e0;   color: #8a6000; font-size: 12.5px; font-weight: 700; padding: 9px 18px; border-radius: 30px; }
    .recommendation-modal-hf-pill    { display: inline-flex; align-items: center; gap: 6px; background: #eef5ff;   color: #2a5ba0; font-size: 12.5px; font-weight: 700; padding: 9px 18px; border-radius: 30px; }

    .recommendation-modal-section { margin-bottom: 28px; }
    .recommendation-modal-section-label {
      font-size: 10.5px; font-weight: 800; text-transform: uppercase;
      letter-spacing: 0.13em; color: var(--orange); margin-bottom: 10px;
      display: flex; align-items: center; gap: 8px;
    }
    .recommendation-modal-section-label::after { content:''; flex:1; height:1.5px; background: var(--orange-mid); }
    .recommendation-modal-section p { font-size: 14px; color: var(--ink-soft); line-height: 1.85; font-weight: 400; }

    .recommendation-modal-reasons { display: flex; flex-wrap: wrap; gap: 8px; }
    .recommendation-modal-reason-chip {
      display: inline-flex; align-items: center; gap: 6px;
      font-size: 12.5px; font-weight: 700; padding: 7px 16px; border-radius: 30px;
      background: var(--orange-mid); color: var(--orange-deep);
    }
    .recommendation-modal-disclaimer {
      font-size: 13px; color: var(--muted);
      display: flex; align-items: flex-start; gap: 10px;
      padding: 16px 20px; background: var(--orange-soft);
      border-radius: 14px; border: 2px solid var(--orange-mid);
      margin-top: 8px; line-height: 1.6;
    }

    @media (max-width: 900px) {
      .recommendation-modal-box { grid-template-columns: 1fr; grid-template-rows: 260px 1fr; height: 100vh; }
      .recommendation-modal-left-panel { height: 260px; }
      .recommendation-modal-right-panel { height: calc(100vh - 260px); }
    }
    @media (max-width: 768px) {
      .recommendation-top-pick-card { grid-template-columns: 1fr; }
      .recommendation-top-pick-img-side { height: 180px; }
      .recommendation-card { grid-template-columns: 100px 1fr; }
      .recommendation-card-score-col { display: none; }
      .recommendation-top-pick-info-side { padding: 24px; }
      .recommendation-modal-right-scroll { padding: 24px 24px 36px; }
      .recommendation-modal-right-topbar { padding: 16px 24px; }
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

      <?php if (empty($recommendations)): ?>
      <div class="empty-state">
        <div class="empty-icon-wrap">
          <i class="fa-solid fa-notes-medical"></i>
        </div>
        <h3>No recommendations yet</h3>
        <p>Complete the questionnaire first so we can match you with the best contraceptive options for your needs.</p>
        <a href="/hci/user/questionnaire.php" class="btn-start">
          <i class="fa-solid fa-arrow-right"></i> Take the Questionnaire
        </a>
      </div>

      <?php else:
        $catIcons = [
          'hormonal'  => 'fa-capsules',
          'barrier'   => 'fa-shield',
          'long_term' => 'fa-clock',
          'natural'   => 'fa-leaf',
          'emergency' => 'fa-bolt',
        ];
        $top       = $recommendations[0];
        $topMethod = $top['method'];
        $topScore  = $top['score'];
        $topIcon   = $catIcons[$topMethod['category']] ?? 'fa-circle-dot';
        $circumference = 2 * M_PI * 28;
      ?>

      <div class="page-header">
        <div class="page-header-left">
          <h1>Your Recommendations</h1>
          <p>Based on your answers, here are your best-matched contraceptive options.</p>
        </div>
        <a href="/hci/user/questionnaire.php" class="retake-btn">
          <i class="fa-solid fa-rotate"></i> Retake Questionnaire
        </a>
      </div>

      <div class="recommendation-top-pick-wrap">
        <div class="recommendation-top-pick-card" style="cursor:pointer;" onclick="openModal(0)">
          <div class="recommendation-top-pick-img-side">
            <?php if (!empty($topMethod['image_path'])): ?>
              <img src="../uploads/contraceptive_methods/<?= htmlspecialchars($topMethod['image_path']) ?>" alt="<?= htmlspecialchars($topMethod['name']) ?>">
            <?php else: ?>
              <div class="recommendation-top-pick-img-placeholder">
                <div class="method-icon"><i class="fa-solid <?= $topIcon ?>"></i></div>
              </div>
            <?php endif; ?>
            <div class="recommendation-best-match-ribbon">Best Match</div>
          </div>
          <div class="recommendation-top-pick-info-side">
            <div class="recommendation-top-method-name"><?= htmlspecialchars($topMethod['name']) ?></div>
            <div class="recommendation-top-method-desc"><?= htmlspecialchars(substr($topMethod['description'], 0, 180)) ?>...</div>
            <div class="recommendation-top-score-row">
              <div class="recommendation-top-score-pill">
                <i class="fa-solid fa-star" style="font-size:11px;"></i>
                <?= $topScore ?>/100 Match
              </div>
              <div class="recommendation-top-eff-pill">
                <i class="fa-solid fa-shield-check" style="font-size:11px;"></i>
                <?= $topMethod['effectiveness'] ?>% effective
              </div>
            </div>
            <?php if (!empty($top['reasons'])): ?>
            <div class="recommendation-top-reasons-row">
              <?php foreach ($top['reasons'] as $r): ?>
                <span class="recommendation-reason-chip"><i class="fa-solid fa-check"></i><?= htmlspecialchars($r) ?></span>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <button class="recommendation-top-detail-btn" onclick="event.stopPropagation(); openModal(0)">
              <i class="fa-solid fa-circle-info"></i> View Full Details
            </button>
          </div>
        </div>
      </div>

      <?php if (count($recommendations) > 1): ?>
      <div class="recommendation-others-label">Other Matches</div>

      <div class="score-legend">
        <strong>Match Score:</strong>
        <span class="legend-item"><span class="legend-dot" style="background:#e74c3c;"></span>0–49 Low</span>
        <span class="legend-item"><span class="legend-dot" style="background:#f47c3c;"></span>50–64 Fair</span>
        <span class="legend-item"><span class="legend-dot" style="background:#f5a623;"></span>65–74 Good</span>
        <span class="legend-item"><span class="legend-dot" style="background:#8fbe3a;"></span>75–89 Great</span>
        <span class="legend-item"><span class="legend-dot" style="background:#27ae60;"></span>90–100 Excellent</span>
      </div>

      <div class="recommendation-list">
        <?php foreach ($recommendations as $i => $rec):
          if ($i === 0) continue;
          $m        = $rec['method'];
          $score    = $rec['score'];
          $rank     = $i + 1;
          $catClass = 'recommendation-cat-' . $m['category'];
          $catLabel = ucfirst(str_replace('_', ' ', $m['category']));
          $icon     = $catIcons[$m['category']] ?? 'fa-circle-dot';
          $ringDash = ($score / 100) * $circumference;
          $color    = scoreColor($score);
          $bgColors = [
            'hormonal'  => 'linear-gradient(135deg,#fce8ef,#fdecea)',
            'barrier'   => 'linear-gradient(135deg,#e4f7f2,#eef5ff)',
            'long_term' => 'linear-gradient(135deg,#eef5ff,#f0eafb)',
            'natural'   => 'linear-gradient(135deg,#eefae8,#e4f7f2)',
            'emergency' => 'linear-gradient(135deg,#fff0e8,#fef6e0)',
          ];
          $cardBg = $bgColors[$m['category']] ?? 'linear-gradient(135deg,#fff0e8,#fce8ef)';
        ?>
        <div class="recommendation-card" onclick="openModal(<?= $i ?>)">
          <div class="recommendation-card-img-col" style="background: <?= $cardBg ?>;">
            <?php if (!empty($m['image_path'])): ?>
              <img src="../uploads/contraceptive_methods/<?= htmlspecialchars($m['image_path']) ?>" alt="<?= htmlspecialchars($m['name']) ?>">
            <?php else: ?>
              <div class="recommendation-card-img-placeholder">
                <div class="recommendation-method-icon-sm" style="background:<?= $color ?>;"><i class="fa-solid <?= $icon ?>"></i></div>
              </div>
            <?php endif; ?>
            <div class="recommendation-rank-badge" style="background:<?= $color ?>;"><?= $rank ?></div>
          </div>

          <div class="recommendation-card-body-col">
            <div class="recommendation-card-name-row">
              <span class="recommendation-card-name"><?= htmlspecialchars($m['name']) ?></span>
              <span class="recommendation-cat-pill <?= $catClass ?>"><?= $catLabel ?></span>
              <?php if ($m['is_hormone_free']): ?>
                <span class="recommendation-cat-pill recommendation-cat-barrier">Hormone-free</span>
              <?php endif; ?>
            </div>
            <div class="recommendation-card-desc"><?= htmlspecialchars(substr($m['description'], 0, 110)) ?>...</div>
            <div class="recommendation-card-chips">
              <span class="recommendation-chip recommendation-chip-eff"><i class="fa-solid fa-shield-check" style="font-size:10px;"></i> <?= $m['effectiveness'] ?>%</span>
              <span class="recommendation-chip recommendation-chip-cost"><i class="fa-solid fa-tag" style="font-size:10px;"></i> <?= ucfirst($m['cost_level']) ?></span>
              <?php foreach (array_slice($rec['reasons'], 0, 2) as $r): ?>
                <span class="recommendation-chip recommendation-chip-match"><i class="fa-solid fa-check" style="font-size:9px;"></i> <?= htmlspecialchars($r) ?></span>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="recommendation-card-score-col">
            <div class="recommendation-score-ring">
              <svg viewBox="0 0 64 64">
                <circle class="recommendation-ring-bg" cx="32" cy="32" r="28"/>
                <circle cx="32" cy="32" r="28"
                  fill="none"
                  stroke="<?= $color ?>"
                  stroke-width="5"
                  stroke-linecap="round"
                  stroke-dasharray="<?= $circumference ?>"
                  stroke-dashoffset="<?= $circumference - $ringDash ?>"/>
              </svg>
              <div class="recommendation-score-label">
                <span class="recommendation-score-num-val" style="color:<?= $color ?>;"><?= $score ?></span>
                <span class="recommendation-score-num-sub">/100</span>
              </div>
            </div>
            <span class="recommendation-score-caption">Match</span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php endif; ?>
    </div>
  </div>
</div>

<?php if (!empty($recommendations)): ?>
<div class="recommendation-modal-overlay" id="recModal">
  <div class="recommendation-modal-box" id="modalBox">
    <div class="recommendation-modal-left-panel" id="modalLeftPanel">
      <div class="recommendation-modal-left-placeholder" id="modalImgPlaceholder">
        <div class="recommendation-hero-icon"><i class="fa-solid fa-circle-dot" id="modalIconEl"></i></div>
      </div>
      <img id="modalImg" src="" alt=""
           style="display:none; max-width:100%; max-height:100%; object-fit:contain;
                  position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);">
      <div class="recommendation-modal-left-overlay" id="modalLeftOverlay"></div>
      <div class="recommendation-modal-left-bottom">
        <div class="recommendation-modal-left-rank" id="modalRankBadge">Best Match</div>
        <div class="recommendation-modal-left-name" id="modalName"></div>
      </div>
    </div>

    <div class="recommendation-modal-right-panel">
      <div class="recommendation-modal-right-topbar">
        <span class="recommendation-modal-right-topbar-title">Full Details</span>
        <button class="recommendation-modal-close-btn" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="recommendation-modal-right-scroll">
        <div class="recommendation-modal-pills-row" id="modalPills"></div>

        <div class="recommendation-modal-section">
          <div class="recommendation-modal-section-label">How It Works</div>
          <p id="modalDesc"></p>
        </div>
        <div class="recommendation-modal-section" id="modalSideSection" style="display:none;">
          <div class="recommendation-modal-section-label">Side Effects</div>
          <p id="modalSide"></p>
        </div>
        <div class="recommendation-modal-section" id="modalHowSection" style="display:none;">
          <div class="recommendation-modal-section-label">How It's Used</div>
          <p id="modalHow"></p>
        </div>
        <div class="recommendation-modal-section" id="modalReasonsSection" style="display:none;">
          <div class="recommendation-modal-section-label">Why This Matches You</div>
          <div class="recommendation-modal-reasons" id="modalReasons"></div>
        </div>
        <div class="recommendation-modal-disclaimer">
          <i class="fa-solid fa-circle-info" style="margin-top:1px; color:var(--orange); flex-shrink:0;"></i>
          <span>For informational purposes only. Please consult a healthcare provider before choosing a contraceptive method.</span>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function scoreColor(s) {
  if (s >= 90) return '#27ae60';
  if (s >= 75) return '#8fbe3a';
  if (s >= 65) return '#f5a623';
  if (s >= 50) return '#f47c3c';
  return '#e74c3c';
}

const recData = <?= json_encode(array_map(function($rec, $i) use ($catIcons) {
  $m = $rec['method'];
  return [
    'rank'            => $i + 1,
    'name'            => $m['name'],
    'description'     => $m['description'],
    'side_effects'    => $m['side_effects'] ?? '',
    'how_used'        => $m['how_used']     ?? '',
    'effectiveness'   => $m['effectiveness'],
    'cost_level'      => ucfirst($m['cost_level']),
    'is_hormone_free' => (bool)$m['is_hormone_free'],
    'category'        => $m['category'],
    'image_path'      => $m['image_path']   ?? '',
    'score'           => $rec['score'],
    'reasons'         => $rec['reasons'],
    'icon'            => $catIcons[$m['category']] ?? 'fa-circle-dot',
  ];
}, $recommendations, array_keys($recommendations)), JSON_HEX_TAG) ?>;

function openModal(index) {
  const d   = recData[index];
  const col = scoreColor(d.score);

  document.getElementById('modalName').textContent = d.name;

  const imgEl       = document.getElementById('modalImg');
  const placeholder = document.getElementById('modalImgPlaceholder');
  const overlay     = document.getElementById('modalLeftOverlay');
  const iconEl      = document.getElementById('modalIconEl');

  if (d.image_path) {
    imgEl.src = '../uploads/contraceptive_methods/' + d.image_path;
    imgEl.alt = d.name;
    imgEl.style.display = 'block';
    placeholder.style.display = 'none';
    overlay.style.display = 'block';
  } else {
    imgEl.style.display = 'none';
    placeholder.style.display = 'flex';
    overlay.style.display = 'none';
    iconEl.className = 'fa-solid ' + d.icon;
  }

  document.getElementById('modalRankBadge').textContent = d.rank === 1 ? 'Best Match' : 'Match #' + d.rank;

  document.getElementById('modalPills').innerHTML = `
    <span class="recommendation-modal-score-pill" style="background:${col};box-shadow:0 3px 12px ${col}55;">
      <i class="fa-solid fa-star" style="font-size:10px;"></i> ${d.score}/100 Match
    </span>
    <span class="recommendation-modal-eff-pill"><i class="fa-solid fa-shield-check" style="font-size:10px;"></i> ${d.effectiveness}% Effective</span>
    <span class="recommendation-modal-cost-pill"><i class="fa-solid fa-tag" style="font-size:10px;"></i> ${d.cost_level} Cost</span>
    ${d.is_hormone_free ? '<span class="recommendation-modal-hf-pill"><i class="fa-solid fa-leaf" style="font-size:10px;"></i> Hormone-Free</span>' : ''}
  `;

  document.getElementById('modalDesc').textContent = d.description;

  const sideSection = document.getElementById('modalSideSection');
  if (d.side_effects) { document.getElementById('modalSide').textContent = d.side_effects; sideSection.style.display = 'block'; }
  else { sideSection.style.display = 'none'; }

  const howSection = document.getElementById('modalHowSection');
  if (d.how_used) { document.getElementById('modalHow').textContent = d.how_used; howSection.style.display = 'block'; }
  else { howSection.style.display = 'none'; }

  const reasonsSection = document.getElementById('modalReasonsSection');
  const reasonsEl      = document.getElementById('modalReasons');
  if (d.reasons && d.reasons.length > 0) {
    reasonsEl.innerHTML = d.reasons.map(r =>
      `<span class="recommendation-modal-reason-chip"><i class="fa-solid fa-check" style="font-size:9px;"></i> ${r}</span>`
    ).join('');
    reasonsSection.style.display = 'block';
  } else { reasonsSection.style.display = 'none'; }

  document.getElementById('recModal').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  document.getElementById('recModal').classList.remove('open');
  document.body.style.overflow = '';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>
<?php endif; ?>

<script src="../assets/vendor/bootstrap-5/js/bootstrap.bundle.min.js"></script>
<?php include '../includes/user/chatbot_widget.php'; ?>
</body>
</html>