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
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
      *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --comparison-bg:            #f5f0e8;
    --comparison-surface:       #fdfaf5;
    --comparison-surface-2:     #faf6ef;
    --comparison-border:        #e8dfd0;
    --comparison-text:          #4a3728;
    --comparison-muted:         #9b8776;
    --comparison-accent-blue:   #b8cfe8;
    --comparison-accent-blue-d: #6b9ab8;
    --comparison-accent-pink:   #f0d5d5;
    --comparison-accent-pink-d: #c47a7a;
    --comparison-accent-mint:   #cce8dc;
    --comparison-accent-mint-d: #5a9a7a;
    --comparison-accent-peach:  #f5ddd0;
    --comparison-accent-peach-d:#c47a55;
    --comparison-accent-lav:    #ddd5f0;
    --comparison-accent-lav-d:  #7a6ab8;
    --comparison-brown:         #7d5a4a;
    --comparison-brown-d:       #5a3a2a;
    --comparison-radius-sm:     12px;
    --comparison-radius-md:     18px;
    --comparison-radius-lg:     24px;
    --comparison-shadow-sm:     0 2px 8px rgba(120,80,50,.08);
    --comparison-shadow-md:     0 4px 16px rgba(120,80,50,.12);
  }
  html, body {
    height: 100%;
    font-family: 'Nunito', sans-serif;
    background: var(--comparison-bg);
    color: var(--comparison-text);
    font-size: 14px;
    background-image:
      radial-gradient(circle at 15% 20%, rgba(184,207,232,.18) 0%, transparent 50%),
      radial-gradient(circle at 85% 75%, rgba(204,232,220,.15) 0%, transparent 50%),
      radial-gradient(circle at 50% 50%, rgba(240,213,213,.10) 0%, transparent 60%);
  }
  .comparison-layout { display: flex; height: 100vh; overflow: hidden; }
  .comparison-main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
  .comparison-topbar {
    height: 56px;
    background: var(--comparison-surface);
    border-bottom: 1.5px solid var(--comparison-border);
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 28px; flex-shrink: 0;
    font-size: 13px; color: var(--comparison-muted);
    font-family: 'Quicksand', sans-serif;
  }
  .comparison-topbar-left { display: flex; align-items: center; gap: 8px; font-weight: 600; }
  .comparison-topbar-title { color: var(--comparison-brown-d); font-weight: 700; font-family: 'Quicksand', sans-serif; }
  .comparison-topbar-sep { color: var(--comparison-border); font-size: 16px; }
  .comparison-topbar-page { color: var(--comparison-muted); font-weight: 500; }
  .comparison-content-area { flex: 1; overflow-y: auto; padding: 28px; }
  .comparison-content-area::-webkit-scrollbar { width: 5px; }
  .comparison-content-area::-webkit-scrollbar-thumb { background: var(--comparison-border); border-radius: 10px; }
  .comparison-page-header { margin-bottom: 24px; }
  .comparison-page-heading {
    font-family: 'Quicksand', sans-serif;
    font-size: 22px; font-weight: 700;
    color: var(--comparison-brown-d); margin-bottom: 5px;
  }
  .comparison-filter-bar { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; align-items: center; }
  .comparison-filter-label { font-size: 11px; font-weight: 700; color: var(--comparison-muted); text-transform: uppercase; letter-spacing: 0.07em; margin-right: 2px; }
  .comparison-filter-btn {
    font-size: 12px; padding: 6px 16px; border-radius: 50px;
    border: 1.5px solid var(--comparison-border); background: var(--comparison-surface);
    color: var(--comparison-muted); cursor: pointer; font-family: 'Nunito', sans-serif;
    font-weight: 600; transition: background .12s, color .12s, border-color .12s;
  }
  .comparison-filter-btn:hover { background: var(--comparison-surface-2); color: var(--comparison-brown); border-color: var(--comparison-brown); }
  .comparison-filter-btn.active { background: var(--comparison-brown); border-color: var(--comparison-brown); color: #fff; font-weight: 700; box-shadow: 0 3px 10px rgba(125,90,74,.3); }
  .comparison-compare-bar {
    display: none; align-items: center; gap: 12px;
    background: var(--comparison-surface); 
    border: 1.5px solid var(--comparison-border);
    border-radius: var(--comparison-radius-sm);
    margin-bottom: 18px;
    padding: 10px 18px;
    font-size: 13px;
    font-family: 'Nunito', sans-serif;
    color: var(--comparison-text);
    box-shadow: var(--comparison-shadow-sm);
  }
  .comparison-compare-bar.visible { display: flex; }
  .comparison-compare-bar-label { flex: 1; font-weight: 700; color: var(--comparison-brown-d); }
  .comparison-compare-bar-chips { display: flex; gap: 6px; flex-wrap: wrap; }
  .comparison-compare-chip {
    background: var(--comparison-surface-2);
    border: 1.5px solid var(--comparison-border);
    border-radius: 50px;
    padding: 3px 12px;
    font-size: 12px;
    font-weight: 600;
    color: var(--comparison-brown);
  }
  .comparison-compare-bar-btn {
    padding: 7px 18px;
    border-radius: 50px;
    border: none;
    background: var(--comparison-brown);
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    font-family: 'Nunito', sans-serif;
    box-shadow: 0 2px 8px rgba(125,90,74,.2);
    transition: background 0.15s;
  }
  .comparison-compare-bar-btn:hover { background: var(--comparison-brown-d); }
  .comparison-compare-bar-clear {
    background: none;
    border: none;
    color: var(--comparison-muted);
    cursor: pointer;
    font-size: 18px;
    padding: 4px;
    font-weight: 700;
  }
  .comparison-compare-bar-clear:hover { color: var(--comparison-brown); }
  .comparison-methods-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px; }
  .comparison-method-card {
    background: var(--comparison-surface);
    border: 1.5px solid var(--comparison-border);
    border-radius: var(--comparison-radius-md);
    padding: 0;
    display: flex; flex-direction: column;
    transition: border-color .15s, box-shadow .15s, transform .18s;
    position: relative; overflow: hidden;
    animation: comparisonPopIn .4s cubic-bezier(.34,1.56,.64,1) both;
    box-shadow: var(--comparison-shadow-sm);
  }
  .comparison-method-card:hover { border-color: var(--comparison-accent-blue-d); box-shadow: var(--comparison-shadow-md); transform: translateY(-2px); }
  .comparison-method-card.selected { border-color: var(--comparison-brown); box-shadow: 0 0 0 3px rgba(125,90,74,.15), var(--comparison-shadow-md); }
  .comparison-method-card.hidden { display: none; }
  @keyframes comparisonPopIn { from { opacity: 0; transform: translateY(10px) scale(.97); } to { opacity: 1; transform: none; } }

  .comparison-method-img-wrap {
    width: 100%; aspect-ratio: 1 / 1;
    overflow: hidden; position: relative; flex-shrink: 0;
    background: var(--comparison-surface);
    border-bottom: 1.5px solid var(--comparison-border);
  }
  .comparison-method-img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .comparison-method-img-placeholder {
    width: 100%; height: 100%;
    background: var(--comparison-surface);
    display: flex; align-items: center; justify-content: center;
    color: var(--comparison-muted);
    font-size: 14px;
    font-weight: 500;
    text-align: center;
    padding: 12px;
  }

  .comparison-card-select-btn {
    position: absolute; top: 10px; right: 10px;
    width: 26px; height: 26px; border-radius: 50%;
    border: 2px solid var(--comparison-brown);
    background: var(--comparison-surface);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: border-color .12s, background .12s;
    padding: 0; box-shadow: 0 2px 6px rgba(0,0,0,.18);
  }
  .comparison-card-select-btn:hover { background: var(--comparison-accent-peach); }
  .comparison-method-card.selected .comparison-card-select-btn { background: var(--comparison-brown); border-color: var(--comparison-brown); }
  .comparison-card-select-btn svg { display: none; }
  .comparison-method-card.selected .comparison-card-select-btn svg { display: block; }

  .comparison-card-body { padding: 14px 16px; display: flex; flex-direction: column; gap: 11px; }
  .comparison-card-name { font-family: 'Quicksand', sans-serif; font-size: 15px; font-weight: 700; color: var(--comparison-brown-d); line-height: 1.2; }
  .comparison-card-tags { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 4px; }
  .comparison-tag { font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 50px; border: 1.5px solid transparent; }
  .comparison-tag-blue   { background: var(--comparison-accent-blue);  color: var(--comparison-accent-blue-d);  border-color: #a8c2dc; }
  .comparison-tag-green  { background: var(--comparison-accent-mint);  color: var(--comparison-accent-mint-d);  border-color: #b0d8c4; }
  .comparison-tag-amber  { background: var(--comparison-accent-peach); color: var(--comparison-accent-peach-d); border-color: #dfc8b8; }
  .comparison-tag-teal   { background: var(--comparison-accent-mint);  color: var(--comparison-accent-mint-d);  border-color: #b0d8c4; }
  .comparison-tag-purple { background: var(--comparison-accent-lav);   color: var(--comparison-accent-lav-d);   border-color: #ccc0e0; }
  .comparison-eff-row { display: flex; align-items: center; gap: 10px; }
  .comparison-eff-label { font-size: 11px; color: var(--comparison-muted); width: 78px; flex-shrink: 0; font-weight: 600; }
  .comparison-eff-track { flex: 1; height: 6px; background: var(--comparison-border); border-radius: 6px; overflow: hidden; }
  .comparison-eff-fill { height: 100%; border-radius: 6px; }
  .comparison-eff-pct { font-size: 12px; font-weight: 700; color: var(--comparison-brown-d); width: 36px; text-align: right; flex-shrink: 0; }
  .comparison-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
  .comparison-info-item-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: var(--comparison-muted); margin-bottom: 2px; }
  .comparison-info-item-val { font-size: 12px; color: var(--comparison-text); font-weight: 600; }
  .comparison-card-detail { border-top: 1.5px solid var(--comparison-border); padding-top: 12px; display: none; flex-direction: column; gap: 8px; }
  .comparison-method-card.expanded .comparison-card-detail { display: flex; }
  .comparison-detail-block-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: var(--comparison-muted); margin-bottom: 3px; }
  .comparison-detail-block-val { font-size: 12px; color: var(--comparison-muted); line-height: 1.55; font-weight: 500; }
  .comparison-card-toggle {
    font-size: 12px; color: var(--comparison-brown); background: none; border: none;
    cursor: pointer; padding: 0; font-family: 'Nunito', sans-serif;
    display: flex; align-items: center; gap: 4px; align-self: flex-start; font-weight: 700;
  }
  .comparison-card-toggle svg { transition: transform 0.2s; }
  .comparison-card-toggle.expanded svg { transform: rotate(180deg); }

  .comparison-modal-overlay {
    position: fixed; inset: 0; background: rgba(74,55,40,0.35);
    display: flex; align-items: flex-start; justify-content: center;
    z-index: 999; padding: 28px 20px; overflow-y: auto;
    opacity: 0; pointer-events: none; transition: opacity 0.18s;
  }
  .comparison-modal-overlay.open { opacity: 1; pointer-events: all; }
  .comparison-modal {
    background: var(--comparison-surface); border-radius: var(--comparison-radius-lg);
    width: 100%; max-width: 920px; overflow: hidden;
    box-shadow: 0 20px 60px rgba(74,55,40,.2);
    transform: translateY(8px) scale(.98); transition: transform 0.18s;
    border: 1.5px solid var(--comparison-border);
  }
  .comparison-modal-overlay.open .comparison-modal { transform: translateY(0) scale(1); }
  .comparison-modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 24px; border-bottom: 1.5px solid var(--comparison-border);
    background: var(--comparison-surface-2);
  }
  .comparison-modal-title { font-family: 'Quicksand', sans-serif; font-size: 16px; font-weight: 700; color: var(--comparison-brown-d); }
  .comparison-modal-close {
    width: 30px; height: 30px; border-radius: 50%;
    border: 1.5px solid var(--comparison-border); background: var(--comparison-surface);
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: var(--comparison-muted); font-size: 15px; font-weight: 700;
  }
  .comparison-modal-close:hover { background: var(--comparison-accent-peach); border-color: var(--comparison-accent-peach-d); }
  .comparison-table-wrap { overflow-x: auto; }
  .comparison-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
  }
  .comparison-table th {
    padding: 14px 18px; text-align: left; font-size: 13px;
    font-weight: 700; color: var(--comparison-brown-d); background: var(--comparison-bg);
    border-bottom: 1.5px solid var(--comparison-border);
    font-family: 'Quicksand', sans-serif;
  }
  .comparison-table th:first-child { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: var(--comparison-muted); width: 130px; }
  .comparison-table td { padding: 12px 18px; font-size: 13px; color: var(--comparison-muted); border-bottom: 1.5px solid var(--comparison-border); vertical-align: top; line-height: 1.5; font-weight: 500; }
  .comparison-table td:first-child { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--comparison-muted); white-space: nowrap; background: var(--comparison-bg); }
  .comparison-table tr:last-child td { border-bottom: none; }
  .comparison-table tr:hover td { background: rgba(245,240,232,.5); }
  .comparison-table tr:hover td:first-child { background: var(--comparison-bg); }
  .comparison-compare-img-wrap {
    width: 100%;
    aspect-ratio: 1 / 1;
    overflow: hidden;
    border-radius: var(--comparison-radius-sm);
    border: 1.5px solid var(--comparison-border);
    background: var(--comparison-surface);
  }
  .comparison-compare-img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .comparison-compare-img-placeholder {
    width: 100%;
    height: 100%;
    background: var(--comparison-surface);
    display: flex; align-items: center; justify-content: center;
    color: var(--comparison-muted);
    font-size: 13px;
    font-weight: 500;
    text-align: center;
    padding: 8px;
  }
  .comparison-pill-yes { display: inline-block; background: var(--comparison-accent-mint); color: var(--comparison-accent-mint-d); font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 50px; border: 1.5px solid #b0d8c4; }
  .comparison-pill-no  { display: inline-block; background: var(--comparison-surface-2); color: var(--comparison-muted); font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 50px; border: 1.5px solid var(--comparison-border); }
  </style>
</head>
<body>
<div class="comparison-layout">
  <?php include '../includes/user/sidebar.php'; ?>
  <div class="comparison-main">
    <div class="comparison-topbar">
      <div class="comparison-topbar-left">
        <span class="comparison-topbar-title">ContraChoice</span>
        <span class="comparison-topbar-sep">/</span>
        <span class="comparison-topbar-page"><?= htmlspecialchars($page_title) ?></span>
      </div>
    </div>
    <div class="comparison-content-area">
      <div class="comparison-page-header">
        <h1 class="comparison-page-heading">Contraceptive Comparison Guide</h1>
      </div>
      <div class="comparison-filter-bar">
        <span class="comparison-filter-label">Filter:</span>
        <button class="comparison-filter-btn active" onclick="filterMethods('all', this)">All methods</button>
        <button class="comparison-filter-btn" onclick="filterMethods('long_term', this)">Long-acting</button>
        <button class="comparison-filter-btn" onclick="filterMethods('hormonal', this)">Hormonal</button>
        <button class="comparison-filter-btn" onclick="filterMethods('barrier', this)">Barrier</button>
        <button class="comparison-filter-btn" onclick="filterMethods('natural', this)">Natural</button>
        <button class="comparison-filter-btn" onclick="filterMethods('emergency', this)">Emergency</button>
        <button class="comparison-filter-btn" onclick="filterMethods('non-hormonal', this)">Non-hormonal</button>
      </div>
      <div class="comparison-compare-bar" id="compare-bar">
        <span class="comparison-compare-bar-label">Comparing:</span>
        <div class="comparison-compare-bar-chips" id="compare-chips"></div>
        <button class="comparison-compare-bar-btn" onclick="openCompareModal()">Compare side by side &rarr;</button>
        <button class="comparison-compare-bar-clear" onclick="clearSelection()">&times;</button>
      </div>
      <div class="comparison-methods-grid" id="methods-grid">
        <?php
        $category_labels = [
          'hormonal'  => 'Hormonal',
          'barrier'   => 'Barrier',
          'long_term' => 'Long-acting',
          'natural'   => 'Natural',
          'emergency' => 'Emergency',
        ];
        $delivery_labels = [
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
          $eff_color  = $eff >= 99 ? '#5a9a7a' : ($eff >= 95 ? '#6b9ab8' : '#c47a55');
          $category   = strtolower($m['category']);
          $type_label = $category_labels[$category] ?? ucfirst($category);
          $type_color = $type_colors[$category] ?? 'blue';
          $del_label  = $delivery_labels[$m['delivery']] ?? ucfirst(str_replace('_', ' ', $m['delivery']));
          $cost_label = $cost_labels[$m['cost_level']] ?? ucfirst($m['cost_level']);
        ?>
        <div class="comparison-method-card" id="card-<?= $i ?>"
             data-index="<?= $i ?>"
             data-category="<?= htmlspecialchars($category) ?>"
             data-hormonal="<?= $m['is_hormone_free'] ? 'false' : 'true' ?>">

          <div class="comparison-method-img-wrap">
            <?php if (!empty($m['image_path'])): ?>
              <img src="../uploads/contraceptive_methods/<?= htmlspecialchars($m['image_path']) ?>" class="comparison-method-img" alt="<?= htmlspecialchars($m['name']) ?>">
            <?php else: ?>
              <div class="comparison-method-img-placeholder">No image available</div>
            <?php endif; ?>
            <button class="comparison-card-select-btn" onclick="toggleSelect(<?= $i ?>)" title="Select to compare">
              <svg width="11" height="9" viewBox="0 0 11 9" fill="none">
                <path d="M1 4.5l2.8 2.8L10 1" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>

          <div class="comparison-card-body">
            <div>
              <div class="comparison-card-name"><?= htmlspecialchars($m['name']) ?></div>
              <div class="comparison-card-tags">
                <span class="comparison-tag comparison-tag-<?= $type_color ?>"><?= $type_label ?></span>
                <?php if ($m['is_hormone_free']): ?>
                  <span class="comparison-tag comparison-tag-teal">Non-hormonal</span>
                <?php endif; ?>
                <?php if (strtolower($m['cost_level']) === 'low'): ?>
                  <span class="comparison-tag comparison-tag-amber">Low cost</span>
                <?php endif; ?>
              </div>
            </div>

            <div class="comparison-eff-row">
              <span class="comparison-eff-label">Effectiveness</span>
              <div class="comparison-eff-track">
                <div class="comparison-eff-fill" style="width:<?= $eff ?>%;background:<?= $eff_color ?>;"></div>
              </div>
              <span class="comparison-eff-pct"><?= $eff ?>%</span>
            </div>

            <div class="comparison-info-grid">
              <div class="comparison-info-item">
                <div class="comparison-info-item-label">Delivery</div>
                <div class="comparison-info-item-val"><?= htmlspecialchars($del_label) ?></div>
              </div>
              <div class="comparison-info-item">
                <div class="comparison-info-item-label">Cost</div>
                <div class="comparison-info-item-val"><?= htmlspecialchars($cost_label) ?></div>
              </div>
              <div class="comparison-info-item">
                <div class="comparison-info-item-label">Hormonal</div>
                <div class="comparison-info-item-val"><?= $m['is_hormone_free'] ? 'No' : 'Yes' ?></div>
              </div>
              <div class="comparison-info-item">
                <div class="comparison-info-item-label">Smoker-safe</div>
                <div class="comparison-info-item-val"><?= $m['suitable_smoker'] ? 'Yes' : 'No' ?></div>
              </div>
            </div>

            <button class="comparison-card-toggle" id="toggle-<?= $i ?>" onclick="toggleExpand(<?= $i ?>)">
              <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              See details
            </button>

            <div class="comparison-card-detail">
              <?php if (!empty($m['how_used'])): ?>
              <div class="comparison-detail-block">
                <div class="comparison-detail-block-label">How it's used</div>
                <div class="comparison-detail-block-val"><?= htmlspecialchars($m['how_used']) ?></div>
              </div>
              <?php endif; ?>
              <?php if (!empty($m['side_effects'])): ?>
              <div class="comparison-detail-block">
                <div class="comparison-detail-block-label">Common side effects</div>
                <div class="comparison-detail-block-val"><?= htmlspecialchars($m['side_effects']) ?></div>
              </div>
              <?php endif; ?>
              <?php if (!empty($m['best_for'])): ?>
              <div class="comparison-detail-block">
                <div class="comparison-detail-block-label">Best for</div>
                <div class="comparison-detail-block-val"><?= htmlspecialchars($m['best_for']) ?></div>
              </div>
              <?php endif; ?>
              <?php if (!empty($m['contraindications'])): ?>
              <div class="comparison-detail-block">
                <div class="comparison-detail-block-label">Contraindications</div>
                <div class="comparison-detail-block-val"><?= htmlspecialchars($m['contraindications']) ?></div>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<div class="comparison-modal-overlay" id="compare-modal-overlay">
  <div class="comparison-modal">
    <div class="comparison-modal-header">
      <span class="comparison-modal-title">Side-by-side comparison</span>
      <button class="comparison-modal-close" onclick="closeCompareModal()">&times;</button>
    </div>
    <div class="comparison-table-wrap">
      <table class="comparison-table" id="compare-table"></table>
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
    chips.innerHTML = selected.map(i => `<span class="comparison-compare-chip">${METHODS[i].name}</span>`).join('');
  } else {
    bar.classList.remove('visible');
  }
}

function filterMethods(filter, btn) {
  document.querySelectorAll('.comparison-filter-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.comparison-method-card').forEach(card => {
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
  const effColor = e => e >= 99 ? '#5a9a7a' : e >= 95 ? '#6b9ab8' : '#c47a55';

  const rows = [
    { label: 'Photo', fn: m => {
        if (m.image_path) {
          return `<div class="comparison-compare-img-wrap"><img src="../uploads/contraceptive_methods/${m.image_path}" class="comparison-compare-img" alt="${m.name}"></div>`;
        } else {
          return `<div class="comparison-compare-img-wrap"><div class="comparison-compare-img-placeholder">No image</div></div>`;
        }
      }
    },
    { label: 'Category',      fn: m => CATEGORY_LABELS[m.category]  || m.category },
    { label: 'Effectiveness', fn: m => `<div class="comparison-eff-row" style="gap:8px;">
        <div class="comparison-eff-track"><div class="comparison-eff-fill" style="width:${m.effectiveness}%;background:${effColor(m.effectiveness)};"></div></div>
        <span style="font-size:12px;font-weight:700;flex-shrink:0;color:var(--comparison-brown-d);">${m.effectiveness}%</span></div>` },
    { label: 'Delivery',      fn: m => DELIVERY_LABELS[m.delivery]  || m.delivery },
    { label: 'Cost',          fn: m => COST_LABELS[m.cost_level]    || m.cost_level },
    { label: 'Hormonal',      fn: m => m.is_hormone_free == 1 ? '<span class="comparison-pill-no">No</span>'  : '<span class="comparison-pill-yes">Yes</span>' },
    { label: 'Smoker-safe',   fn: m => m.suitable_smoker == 1      ? '<span class="comparison-pill-yes">Yes</span>' : '<span class="comparison-pill-no">No</span>' },
    { label: 'Breastfeeding', fn: m => m.suitable_breastfeeding == 1 ? '<span class="comparison-pill-yes">Yes</span>' : '<span class="comparison-pill-no">No</span>' },
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
<?php include '../includes/user/chatbot_widget.php'; ?>
</html>