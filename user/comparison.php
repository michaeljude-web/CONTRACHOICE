<?php
session_start();
include '../includes/db_connection.php';
include '../includes/user/auth.php';

$page_title  = 'Comparison Guide';
$active_page = 'comparison';
$is_admin    = false;

$result = $conn->query("SELECT * FROM contraceptive_methods ORDER BY method_id");
$methods = [];
while ($row = $result->fetch_assoc()) {
    $methods[] = $row;
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
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg:           #f7f6f3;
      --surface:      #ffffff;
      --surface2:     #f1f0ec;
      --border:       rgba(0,0,0,0.08);
      --border-md:    rgba(0,0,0,0.13);
      --text-primary: #1a1a18;
      --text-sec:     #6b6b67;
      --text-muted:   #a0a09b;
      --blue-50:      #e6f1fb;
      --blue-100:     #b5d4f4;
      --blue-600:     #185FA5;
      --blue-800:     #0C447C;
      --green-50:     #eaf3de;
      --green-700:    #3B6D11;
      --green-800:    #27500A;
      --amber-50:     #faeeda;
      --amber-700:    #854F0B;
      --amber-800:    #633806;
      --red-50:       #fcebeb;
      --red-700:      #791F1F;
      --purple-50:    #eeedfe;
      --purple-700:   #534AB7;
      --purple-800:   #3C3489;
      --teal-50:      #e1f5ee;
      --teal-700:     #0F6E56;
    }
    html, body {
      height: 100%;
      font-family: 'Outfit', sans-serif;
      background: var(--bg);
      color: var(--text-primary);
      font-size: 14px;
    }
    .cc-layout { display: flex; height: 100vh; overflow: hidden; }
    .cc-main { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: var(--bg); }
    .topbar {
      height: 52px;
      background: var(--surface);
      border-bottom: 0.5px solid var(--border-md);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 24px;
      flex-shrink: 0;
    }
    .topbar-left { display: flex; align-items: center; gap: 8px; }
    .topbar-title { font-family: 'Playfair Display', Georgia, serif; font-size: 14px; font-weight: 400; color: var(--text-primary); }
    .topbar-sep  { color: var(--text-muted); font-size: 13px; }
    .topbar-page { font-size: 13px; color: var(--text-sec); }
    .content-area { flex: 1; overflow-y: auto; padding: 28px; }
    .page-header { margin-bottom: 24px; }
    .page-heading { font-family: 'Playfair Display', Georgia, serif; font-size: 24px; font-weight: 400; color: var(--text-primary); margin-bottom: 5px; letter-spacing: -0.3px; }
    .filter-bar { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; align-items: center; }
    .filter-label { font-size: 11px; font-weight: 500; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.07em; margin-right: 2px; }
    .filter-btn { font-size: 12px; padding: 5px 14px; border-radius: 20px; border: 0.5px solid var(--border-md); background: var(--surface); color: var(--text-sec); cursor: pointer; font-family: 'Outfit', sans-serif; transition: background 0.12s, color 0.12s; }
    .filter-btn:hover { background: var(--surface2); }
    .filter-btn.active { background: var(--blue-50); border-color: var(--blue-600); color: var(--blue-800); font-weight: 500; }
    .compare-bar {
      display: none; align-items: center; gap: 12px;
      background: var(--blue-800); color: #fff;
      padding: 10px 18px; border-radius: 10px; margin-bottom: 18px; font-size: 13px;
    }
    .compare-bar.visible { display: flex; }
    .compare-bar-label { flex: 1; font-weight: 500; }
    .compare-bar-chips { display: flex; gap: 6px; flex-wrap: wrap; }
    .compare-chip { background: rgba(255,255,255,0.15); border-radius: 20px; padding: 3px 10px; font-size: 12px; }
    .compare-bar-btn { padding: 6px 16px; border-radius: 7px; border: none; background: #fff; color: var(--blue-800); font-size: 12px; font-weight: 600; cursor: pointer; font-family: 'Outfit', sans-serif; }
    .compare-bar-clear { background: none; border: none; color: rgba(255,255,255,0.6); cursor: pointer; font-size: 13px; padding: 4px; }
    .compare-bar-clear:hover { color: #fff; }
    .methods-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 14px; }
    .method-card {
      background: var(--surface);
      border: 0.5px solid var(--border-md);
      border-radius: 12px;
      padding: 18px;
      display: flex; flex-direction: column; gap: 14px;
      transition: border-color 0.15s, box-shadow 0.15s;
      position: relative;
      animation: cardIn 0.25s ease both;
    }
    .method-card:hover { border-color: var(--blue-100); box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
    .method-card.selected { border-color: var(--blue-600); box-shadow: 0 0 0 2px var(--blue-50); }
    .method-card.hidden { display: none; }
    @keyframes cardIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }

    .method-img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 8px;
      border: 0.5px solid var(--border);
    }
    .method-img-placeholder {
      width: 100%;
      height: 200px;
      border-radius: 8px;
      background: var(--surface2);
      border: 0.5px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--text-muted);
      font-size: 32px;
    }

    .card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
    .card-name { font-size: 15px; font-weight: 500; color: var(--text-primary); line-height: 1.2; }
    .card-select-btn {
      width: 20px; height: 20px; border-radius: 50%;
      border: 1.5px solid var(--border-md); background: none; cursor: pointer;
      flex-shrink: 0; display: flex; align-items: center; justify-content: center;
      transition: border-color 0.12s, background 0.12s; margin-top: 1px; padding: 0;
    }
    .card-select-btn:hover { border-color: var(--blue-600); }
    .method-card.selected .card-select-btn { background: var(--blue-600); border-color: var(--blue-600); }
    .card-select-btn svg { display: none; }
    .method-card.selected .card-select-btn svg { display: block; }
    .card-tags { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 4px; }
    .tag { font-size: 10px; font-weight: 500; padding: 2px 8px; border-radius: 20px; }
    .tag-blue   { background: var(--blue-50);   color: var(--blue-800); }
    .tag-green  { background: var(--green-50);  color: var(--green-800); }
    .tag-amber  { background: var(--amber-50);  color: var(--amber-800); }
    .tag-teal   { background: var(--teal-50);   color: var(--teal-700); }
    .tag-purple { background: var(--purple-50); color: var(--purple-800); }
    .eff-row { display: flex; align-items: center; gap: 10px; }
    .eff-label { font-size: 11px; color: var(--text-muted); width: 78px; flex-shrink: 0; }
    .eff-track { flex: 1; height: 5px; background: var(--surface2); border-radius: 3px; overflow: hidden; }
    .eff-fill { height: 100%; border-radius: 3px; background: var(--blue-600); }
    .eff-pct { font-size: 12px; font-weight: 500; color: var(--text-primary); width: 36px; text-align: right; flex-shrink: 0; }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .info-item-label { font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.07em; color: var(--text-muted); margin-bottom: 2px; }
    .info-item-val { font-size: 12px; color: var(--text-primary); }
    .card-detail { border-top: 0.5px solid var(--border); padding-top: 12px; display: none; flex-direction: column; gap: 8px; }
    .method-card.expanded .card-detail { display: flex; }
    .detail-block-label { font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.07em; color: var(--text-muted); margin-bottom: 3px; }
    .detail-block-val { font-size: 12px; color: var(--text-sec); line-height: 1.55; }
    .card-toggle { font-size: 12px; color: var(--blue-600); background: none; border: none; cursor: pointer; padding: 0; font-family: 'Outfit', sans-serif; display: flex; align-items: center; gap: 4px; align-self: flex-start; }
    .card-toggle svg { transition: transform 0.2s; }
    .card-toggle.expanded svg { transform: rotate(180deg); }

    .compare-modal-overlay {
      position: fixed; inset: 0; background: rgba(0,0,0,0.3);
      display: flex; align-items: flex-start; justify-content: center;
      z-index: 999; padding: 28px 20px; overflow-y: auto;
      opacity: 0; pointer-events: none; transition: opacity 0.18s;
    }
    .compare-modal-overlay.open { opacity: 1; pointer-events: all; }
    .compare-modal { background: var(--surface); border-radius: 14px; width: 100%; max-width: 900px; overflow: hidden; box-shadow: 0 12px 48px rgba(0,0,0,0.14); transform: translateY(8px); transition: transform 0.18s; }
    .compare-modal-overlay.open .compare-modal { transform: translateY(0); }
    .modal-header { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; border-bottom: 0.5px solid var(--border); }
    .modal-title { font-family: 'Playfair Display', Georgia, serif; font-size: 17px; font-weight: 400; }
    .modal-close { width: 28px; height: 28px; border-radius: 8px; border: 0.5px solid var(--border-md); background: none; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--text-sec); font-size: 16px; }
    .modal-close:hover { background: var(--surface2); }
    .compare-table-wrap { overflow-x: auto; }
    .compare-table { width: 100%; border-collapse: collapse; }
    .compare-table th { padding: 14px 18px; text-align: left; font-size: 13px; font-weight: 500; color: var(--text-primary); background: var(--bg); border-bottom: 0.5px solid var(--border-md); white-space: nowrap; }
    .compare-table th:first-child { font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.07em; color: var(--text-muted); width: 130px; }
    .compare-table td { padding: 12px 18px; font-size: 13px; color: var(--text-sec); border-bottom: 0.5px solid var(--border); vertical-align: top; line-height: 1.5; }
    .compare-table td:first-child { font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); white-space: nowrap; }
    .compare-table tr:last-child td { border-bottom: none; }
    .compare-img { width: 100%; height: 180px; object-fit: cover; border-radius: 8px; border: 0.5px solid var(--border); }
    .compare-img-placeholder { width: 100%; height: 180px; border-radius: 8px; background: var(--surface2); display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 24px; }
    .pill-yes { display: inline-block; background: var(--green-50); color: var(--green-800); font-size: 11px; font-weight: 500; padding: 2px 8px; border-radius: 20px; }
    .pill-no  { display: inline-block; background: var(--surface2); color: var(--text-muted); font-size: 11px; font-weight: 500; padding: 2px 8px; border-radius: 20px; }
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
    </div>
    <div class="content-area">
      <div class="page-header">
        <h1 class="page-heading">Contraceptive Comparison Guide</h1>
      </div>
      <div class="filter-bar">
        <span class="filter-label">Filter:</span>
        <button class="filter-btn active" onclick="filterMethods('all', this)">All methods</button>
        <button class="filter-btn" onclick="filterMethods('long_term', this)">Long-acting</button>
        <button class="filter-btn" onclick="filterMethods('hormonal', this)">Hormonal</button>
        <button class="filter-btn" onclick="filterMethods('barrier', this)">Barrier</button>
        <button class="filter-btn" onclick="filterMethods('natural', this)">Natural</button>
        <button class="filter-btn" onclick="filterMethods('emergency', this)">Emergency</button>
        <button class="filter-btn" onclick="filterMethods('non-hormonal', this)">Non-hormonal</button>
      </div>
      <div class="compare-bar" id="compare-bar">
        <span class="compare-bar-label">Comparing:</span>
        <div class="compare-bar-chips" id="compare-chips"></div>
        <button class="compare-bar-btn" onclick="openCompareModal()">Compare side by side →</button>
        <button class="compare-bar-clear" onclick="clearSelection()">✕</button>
      </div>
      <div class="methods-grid" id="methods-grid">
        <?php
        $category_labels  = [
          'hormonal'  => 'Hormonal',
          'barrier'   => 'Barrier',
          'long_term' => 'Long-acting',
          'natural'   => 'Natural',
          'emergency' => 'Emergency',
        ];
        $delivery_labels  = [
          'daily_pill'        => 'Daily Pill',
          'weekly_patch'      => 'Weekly Patch',
          'monthly_injection' => 'Monthly Injection',
          'long_term'         => 'Long-acting Device',
          'barrier'           => 'Barrier Method',
          'natural'           => 'Natural Method',
        ];
        $cost_labels = [
          'low'    => 'Low',
          'medium' => 'Medium',
          'high'   => 'High',
        ];
        $type_colors = [
          'hormonal'  => 'green',
          'barrier'   => 'amber',
          'long_term' => 'blue',
          'natural'   => 'teal',
          'emergency' => 'purple',
        ];

        foreach ($methods as $i => $m):
          $eff        = floatval($m['effectiveness']);
          $eff_color  = $eff >= 99 ? '#0F6E56' : ($eff >= 95 ? '#185FA5' : '#854F0B');
          $category   = strtolower($m['category']);
          $type_label = $category_labels[$category] ?? ucfirst($category);
          $type_color = $type_colors[$category] ?? 'blue';
          $del_label  = $delivery_labels[$m['delivery']] ?? ucfirst(str_replace('_', ' ', $m['delivery']));
          $cost_label = $cost_labels[$m['cost_level']] ?? ucfirst($m['cost_level']);
        ?>
        <div class="method-card" id="card-<?= $i ?>"
             data-index="<?= $i ?>"
             data-category="<?= htmlspecialchars($category) ?>"
             data-hormonal="<?= $m['is_hormone_free'] ? 'false' : 'true' ?>">

          <?php if (!empty($m['image_path'])): ?>
            <img src="../uploads/contraceptive_methods/<?= htmlspecialchars($m['image_path']) ?>" class="method-img" alt="<?= htmlspecialchars($m['name']) ?>">
          <?php else: ?>
            <div class="method-img-placeholder">💊</div>
          <?php endif; ?>

          <div>
            <div class="card-top">
              <div class="card-name"><?= htmlspecialchars($m['name']) ?></div>
              <button class="card-select-btn" onclick="toggleSelect(<?= $i ?>)" title="Select to compare">
                <svg width="10" height="8" viewBox="0 0 10 8" fill="none">
                  <path d="M1 4l2.5 2.5L9 1" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
            </div>
            <div class="card-tags">
              <span class="tag tag-<?= $type_color ?>"><?= $type_label ?></span>
              <?php if ($m['is_hormone_free']): ?>
                <span class="tag tag-teal">Non-hormonal</span>
              <?php endif; ?>
              <?php if (strtolower($m['cost_level']) === 'low'): ?>
                <span class="tag tag-amber">Low cost</span>
              <?php endif; ?>
            </div>
          </div>

          <div class="eff-row">
            <span class="eff-label">Effectiveness</span>
            <div class="eff-track">
              <div class="eff-fill" style="width:<?= $eff ?>%;background:<?= $eff_color ?>;"></div>
            </div>
            <span class="eff-pct"><?= $eff ?>%</span>
          </div>

          <div class="info-grid">
            <div class="info-item">
              <div class="info-item-label">Delivery</div>
              <div class="info-item-val"><?= htmlspecialchars($del_label) ?></div>
            </div>
            <div class="info-item">
              <div class="info-item-label">Cost</div>
              <div class="info-item-val"><?= htmlspecialchars($cost_label) ?></div>
            </div>
            <div class="info-item">
              <div class="info-item-label">Hormonal</div>
              <div class="info-item-val"><?= $m['is_hormone_free'] ? 'No' : 'Yes' ?></div>
            </div>
            <div class="info-item">
              <div class="info-item-label">Smoker-safe</div>
              <div class="info-item-val"><?= $m['suitable_smoker'] ? 'Yes' : 'No' ?></div>
            </div>
          </div>

          <button class="card-toggle" id="toggle-<?= $i ?>" onclick="toggleExpand(<?= $i ?>)">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
              <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            See details
          </button>

          <div class="card-detail">
            <?php if (!empty($m['how_used'])): ?>
            <div class="detail-block">
              <div class="detail-block-label">How it's used</div>
              <div class="detail-block-val"><?= htmlspecialchars($m['how_used']) ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($m['side_effects'])): ?>
            <div class="detail-block">
              <div class="detail-block-label">Common side effects</div>
              <div class="detail-block-val"><?= htmlspecialchars($m['side_effects']) ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($m['best_for'])): ?>
            <div class="detail-block">
              <div class="detail-block-label">Best for</div>
              <div class="detail-block-val"><?= htmlspecialchars($m['best_for']) ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($m['contraindications'])): ?>
            <div class="detail-block">
              <div class="detail-block-label">Contraindications</div>
              <div class="detail-block-val"><?= htmlspecialchars($m['contraindications']) ?></div>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<div class="compare-modal-overlay" id="compare-modal-overlay">
  <div class="compare-modal">
    <div class="modal-header">
      <span class="modal-title">Side-by-side comparison</span>
      <button class="modal-close" onclick="closeCompareModal()">✕</button>
    </div>
    <div class="compare-table-wrap">
      <table class="compare-table" id="compare-table"></table>
    </div>
  </div>
</div>

<script>
const METHODS = <?= json_encode($methods, JSON_UNESCAPED_UNICODE) ?>;

const CATEGORY_LABELS = {
  hormonal:  'Hormonal',
  barrier:   'Barrier',
  long_term: 'Long-acting',
  natural:   'Natural',
  emergency: 'Emergency'
};

const DELIVERY_LABELS = {
  daily_pill:        'Daily Pill',
  weekly_patch:      'Weekly Patch',
  monthly_injection: 'Monthly Injection',
  long_term:         'Long-acting Device',
  barrier:           'Barrier Method',
  natural:           'Natural Method'
};

const COST_LABELS = {
  low:    'Low',
  medium: 'Medium',
  high:   'High'
};

let selected = [];
const MAX_SELECT = 3;

function toggleSelect(i) {
  const card = document.getElementById('card-' + i);
  if (selected.includes(i)) {
    selected = selected.filter(x => x !== i);
    card.classList.remove('selected');
  } else {
    if (selected.length >= MAX_SELECT) {
      alert('You can compare up to ' + MAX_SELECT + ' methods at a time.');
      return;
    }
    selected.push(i);
    card.classList.add('selected');
  }
  updateCompareBar();
}

function clearSelection() {
  selected.forEach(i => document.getElementById('card-' + i).classList.remove('selected'));
  selected = [];
  updateCompareBar();
}

function updateCompareBar() {
  const bar   = document.getElementById('compare-bar');
  const chips = document.getElementById('compare-chips');
  if (selected.length >= 2) {
    bar.classList.add('visible');
    chips.innerHTML = selected.map(i => `<span class="compare-chip">${METHODS[i].name}</span>`).join('');
  } else {
    bar.classList.remove('visible');
  }
}

function filterMethods(filter, btn) {
  document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.method-card').forEach(card => {
    const cat      = card.dataset.category;
    const hormonal = card.dataset.hormonal;
    let show = false;
    if (filter === 'all')               show = true;
    else if (filter === 'non-hormonal') show = hormonal === 'false';
    else                                show = cat === filter;
    card.classList.toggle('hidden', !show);
  });
}

function toggleExpand(i) {
  const card   = document.getElementById('card-' + i);
  const toggle = document.getElementById('toggle-' + i);
  const expanded = card.classList.toggle('expanded');
  toggle.classList.toggle('expanded', expanded);
  toggle.innerHTML = expanded
    ? `<svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Hide details`
    : `<svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg> See details`;
}

function openCompareModal() {
  const cols = selected.map(i => METHODS[i]);
  const effColor = e => e >= 99 ? '#0F6E56' : e >= 95 ? '#185FA5' : '#854F0B';

  const rows = [
    { label: 'Photo', fn: m => m.image_path
        ? `<img src="../uploads/contraceptive_methods/${m.image_path}" class="compare-img" alt="${m.name}">`
        : `<div class="compare-img-placeholder">💊</div>` },
    { label: 'Category',      fn: m => CATEGORY_LABELS[m.category]  || m.category },
    { label: 'Effectiveness', fn: m => `<div class="eff-row" style="gap:8px;">
        <div class="eff-track"><div class="eff-fill" style="width:${m.effectiveness}%;background:${effColor(m.effectiveness)};"></div></div>
        <span style="font-size:12px;font-weight:500;flex-shrink:0;">${m.effectiveness}%</span></div>` },
    { label: 'Delivery',      fn: m => DELIVERY_LABELS[m.delivery]  || m.delivery },
    { label: 'Cost',          fn: m => COST_LABELS[m.cost_level]    || m.cost_level },
    { label: 'Hormonal',      fn: m => m.is_hormone_free == 1 ? '<span class="pill-no">No</span>'  : '<span class="pill-yes">Yes</span>' },
    { label: 'Smoker-safe',   fn: m => m.suitable_smoker == 1      ? '<span class="pill-yes">Yes</span>' : '<span class="pill-no">No</span>' },
    { label: 'Breastfeeding', fn: m => m.suitable_breastfeeding == 1 ? '<span class="pill-yes">Yes</span>' : '<span class="pill-no">No</span>' },
    { label: 'How it works',  fn: m => m.how_used     || '—' },
    { label: 'Side effects',  fn: m => m.side_effects || '—' },
    { label: 'Best for',      fn: m => m.best_for     || '—' },
  ];

  let html = '<thead><tr><th></th>';
  cols.forEach(m => { html += `<th>${m.name}</th>`; });
  html += '</tr></thead><tbody>';
  rows.forEach(row => {
    html += `<tr><td>${row.label}</td>`;
    cols.forEach(m => { html += `<td>${row.fn(m)}</td>`; });
    html += '</tr>';
  });
  html += '</tbody>';
  document.getElementById('compare-table').innerHTML = html;
  document.getElementById('compare-modal-overlay').classList.add('open');
}

function closeCompareModal() {
  document.getElementById('compare-modal-overlay').classList.remove('open');
}
document.getElementById('compare-modal-overlay').addEventListener('click', function(e) {
  if (e.target === this) closeCompareModal();
});
</script>
</body>
</html>