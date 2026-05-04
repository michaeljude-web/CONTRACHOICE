<?php
session_start();
include 'includes/db_connection.php';

$methods = [];
$result = $conn->query("SELECT * FROM contraceptive_methods ORDER BY method_id");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $methods[] = $row;
    }
}

$category_labels = [
    'hormonal' => 'Hormonal',
    'barrier' => 'Barrier',
    'long_term' => 'Long-Term',
    'natural' => 'Natural',
    'emergency' => 'Emergency'
];
$cat_icons = [
    'hormonal' => 'fa-pills',
    'barrier' => 'fa-shield-halved',
    'long_term' => 'fa-clock',
    'natural' => 'fa-leaf',
    'emergency' => 'fa-bolt'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ContraChoice</title>
<link rel="stylesheet" href="/hci/assets/vendor/fontawesome-7/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400;1,500&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}

:root{
    --rose:#c0506e;
    --rose-mid:#d4748e;
    --rose-soft:#eedde3;
    --rose-pale:#f8f0f3;
    --teal:#4a9b8e;
    --teal-soft:#ddf0ec;
    --bg:#fdfbfc;
    --bg2:#f7f3f5;
    --bg3:#f0eaec;
    --ink:#111111;
    --ink2:#333333;
    --ink3:#666666;
    --line:#e8dfe3;
    --r:8px;
}

body{
    font-family:'Jost',system-ui,sans-serif;
    background:var(--bg);
    color:var(--ink);
    line-height:1.6;
    overflow-x:hidden;
    -webkit-font-smoothing:antialiased;
}
a{text-decoration:none;color:inherit;}
img{display:block;width:100%;}

nav{
    position:fixed;top:0;left:0;right:0;z-index:200;
    height:64px;
    background:rgba(253,251,252,0.93);
    border-bottom:1px solid var(--line);
    display:flex;align-items:center;justify-content:space-between;
    padding:0 56px;
    backdrop-filter:blur(16px);
    -webkit-backdrop-filter:blur(16px);
}

.mylogo-wrapper{
    display:inline-flex;
    flex-direction:column;
    align-items:center;
    line-height:1;
    gap:3px;
    font-family:'Playfair Display','Georgia','Times New Roman',serif;
}
.mylogo-brand{
    font-size:20px;
    font-weight:900;
    letter-spacing:0.03em;
    text-transform:uppercase;
    display:inline-block;
}
.mylogo-contra{
    font-style:italic;
    font-weight:700;
    letter-spacing:0.05em;
    color:#343232;
}
.mylogo-choice{
    font-weight:900;
    color:#ba485b;
}
.mylogo-divider{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:6px;
}
.mylogo-line{
    width:28px;
    height:1px;
    background:#f4c1cc;
    opacity:0.9;
}
.mylogo-diamond{
    width:5px;
    height:5px;
    background:#d36e7e;
    transform:rotate(45deg);
    border-radius:1px;
}

.nav-links{display:flex;align-items:center;gap:2px;list-style:none;}
.nav-links a{
    font-size:13px;font-weight:400;letter-spacing:0.3px;
    color:var(--ink2);padding:7px 14px;border-radius:var(--r);
    transition:all 0.18s;
}
.nav-links a:hover{color:var(--rose);background:var(--rose-pale);}
.nav-links .cta{
    background:var(--rose);
    color:#fff!important;
    font-weight:500!important;
    font-size:12px!important;
    letter-spacing:0.8px;
    text-transform:uppercase;
    padding:8px 22px!important;
    border-radius:var(--r);
    transition:background 0.2s,transform 0.15s;
}
.nav-links .cta:hover{background:#a83d5a;transform:translateY(-1px);}

.hero{
    position:relative;z-index:10;
    min-height:100vh;
    display:flex;flex-direction:column;
    align-items:center;justify-content:center;
    text-align:center;
    padding:120px 48px 80px;
    overflow:hidden;
    background:linear-gradient(165deg,#fff 0%,var(--rose-pale) 50%,#fdf6f8 100%);
}
.hero-deco{
    position:absolute;border-radius:50%;
    pointer-events:none;
}
.deco1{
    width:520px;height:520px;
    top:-140px;right:-160px;
    background:radial-gradient(circle,var(--rose-soft) 0%,transparent 70%);
    opacity:0.5;
}
.deco2{
    width:380px;height:380px;
    bottom:-100px;left:-120px;
    background:radial-gradient(circle,var(--teal-soft) 0%,transparent 70%);
    opacity:0.55;
}
.deco3{
    width:200px;height:200px;
    top:40%;left:10%;
    background:radial-gradient(circle,var(--rose-soft) 0%,transparent 70%);
    opacity:0.3;
}

.hero-eyebrow{
    display:inline-flex;align-items:center;gap:10px;
    font-size:10px;font-weight:500;letter-spacing:2.8px;
    text-transform:uppercase;color:var(--rose);
    margin-bottom:28px;
}
.hero-eyebrow::before,.hero-eyebrow::after{
    content:'';display:block;
    width:32px;height:1px;background:var(--rose-mid);opacity:0.6;
}
.hero-title{
    font-family:'Playfair Display',serif;
    font-size:clamp(48px,7.5vw,88px);
    font-weight:400;line-height:1.04;letter-spacing:-0.5px;
    color:var(--ink);margin-bottom:8px;
}
.hero-title em{font-style:normal;color:var(--rose);}
.hero-sub{
    font-family:'Playfair Display',serif;
    font-size:clamp(22px,3.5vw,42px);
    font-weight:400;font-style:normal;
    color:var(--ink2);margin-bottom:24px;line-height:1.25;
}
.hero-desc{
    font-size:15px;font-weight:300;color:var(--ink3);
    max-width:420px;line-height:1.85;margin-bottom:40px;
    letter-spacing:0.1px;
}
.hero-btns{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;}

.btn-primary{
    display:inline-flex;align-items:center;gap:8px;
    background:var(--rose);color:#fff;
    padding:13px 32px;border-radius:var(--r);
    font-size:12px;font-weight:500;letter-spacing:1px;
    text-transform:uppercase;transition:all 0.2s;
    box-shadow:0 4px 20px rgba(192,80,110,0.22);
}
.btn-primary:hover{
    background:#a83d5a;
    transform:translateY(-2px);
    box-shadow:0 8px 28px rgba(192,80,110,0.3);
}
.btn-secondary{
    display:inline-flex;align-items:center;gap:8px;
    background:transparent;color:var(--ink2);
    padding:12px 32px;border-radius:var(--r);
    font-size:12px;font-weight:400;letter-spacing:1px;
    text-transform:uppercase;border:1.5px solid var(--line);
    transition:all 0.2s;
}
.btn-secondary:hover{border-color:var(--rose-mid);color:var(--rose);background:var(--rose-pale);}

.hero-stats{
    display:flex;gap:56px;margin-top:60px;
    padding-top:36px;border-top:1px solid var(--line);
}
.sv{
    font-family:'Playfair Display',serif;
    font-size:38px;font-weight:400;color:var(--rose);
    display:block;line-height:1;
}
.sl{font-size:10px;letter-spacing:1.5px;text-transform:uppercase;color:var(--ink3);margin-top:6px;}

.scroll-hint{
    position:absolute;bottom:30px;left:50%;transform:translateX(-50%);
    display:flex;flex-direction:column;align-items:center;gap:8px;
    font-size:9px;letter-spacing:2px;text-transform:uppercase;color:var(--ink3);
    animation:bob 2.2s ease-in-out infinite;
}
.scroll-line{width:1px;height:36px;background:linear-gradient(to bottom,var(--rose-mid),transparent);}
@keyframes bob{0%,100%{transform:translateX(-50%) translateY(0);}50%{transform:translateX(-50%) translateY(6px);}}

.sec{position:relative;z-index:10;padding:96px 0;}
.wrap{max-width:1200px;margin:0 auto;padding:0 56px;}
.sec-tag{
    font-size:9.5px;letter-spacing:2.5px;text-transform:uppercase;
    color:var(--rose);margin-bottom:10px;
    display:flex;align-items:center;gap:10px;
}
.sec-tag::after{content:'';flex:0 0 36px;height:1px;background:var(--rose-mid);opacity:0.5;}
.sec-title{
    font-family:'Playfair Display',serif;
    font-size:clamp(28px,3.8vw,46px);font-weight:400;
    color:var(--ink);line-height:1.18;letter-spacing:-0.2px;margin-bottom:12px;
}
.sec-title em{font-style:normal;color:var(--rose);}
.sec-sub{font-size:14px;font-weight:300;color:var(--ink3);line-height:1.85;max-width:480px;}

.divider{width:100%;height:1px;background:var(--line);}

.sec-how{background:var(--bg);}
.steps{display:grid;grid-template-columns:repeat(3,1fr);margin-top:52px;}
.step{
    padding:40px 36px;
    border-right:1px solid var(--line);
    position:relative;transition:background 0.25s;
}
.step:last-child{border-right:none;}
.step:hover{background:var(--rose-pale);}
.step-n{
    font-family:'Playfair Display',serif;
    font-size:56px;font-weight:400;
    color:var(--rose-soft);line-height:1;margin-bottom:18px;
}
.step-t{font-size:15px;font-weight:500;color:var(--ink);margin-bottom:8px;}
.step-d{font-size:13px;font-weight:300;color:var(--ink3);line-height:1.75;}

.sec-methods{background:var(--bg2);}
.filter-bar{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:32px;}
.fb{
    font-size:10.5px;font-weight:400;letter-spacing:0.8px;text-transform:uppercase;
    padding:7px 18px;border-radius:100px;
    border:1.5px solid var(--line);
    background:var(--bg);color:var(--ink3);
    cursor:pointer;transition:all 0.18s;font-family:'Jost',sans-serif;
}
.fb:hover{border-color:var(--rose-mid);color:var(--rose);}
.fb.on{background:var(--rose);border-color:var(--rose);color:#fff;}

.mgrid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;}
.mc{
    background:var(--bg);
    border:1px solid var(--line);
    border-radius:var(--r);
    display:flex;flex-direction:column;
    transition:all 0.25s;overflow:hidden;
}
.mc:hover{
    border-color:var(--rose-mid);
    transform:translateY(-3px);
    box-shadow:0 8px 32px rgba(192,80,110,0.1);
}
.mc.hidden{display:none;}

.mc-img{
    width:100%;aspect-ratio:1/1;
    overflow:hidden;position:relative;
    background:var(--rose-pale);flex-shrink:0;
}
.mc-img img{
    width:100%;height:100%;object-fit:cover;
    transition:transform 0.5s ease;
}
.mc:hover .mc-img img{transform:scale(1.05);}
.mc-placeholder{
    width:100%;height:100%;
    display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;
    color:var(--rose-mid);
}
.mc-placeholder i{font-size:26px;opacity:0.35;}
.mc-placeholder span{font-size:10px;letter-spacing:1px;text-transform:uppercase;opacity:0.4;color:var(--ink3);}

.mc-badge{
    position:absolute;top:10px;left:10px;
    font-size:9px;font-weight:500;letter-spacing:1px;text-transform:uppercase;
    padding:4px 10px;border-radius:100px;
    background:rgba(253,251,252,0.92);color:var(--rose);
    border:1px solid var(--rose-soft);
}

.mc-body{padding:16px 18px;flex:1;display:flex;flex-direction:column;gap:8px;}
.mc-name{font-size:13px;font-weight:500;color:var(--ink);line-height:1.4;}
.mc-desc{
    font-size:12px;font-weight:300;color:var(--ink3);line-height:1.7;
    display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;
    flex:1;
}
.mc-foot{
    display:flex;align-items:center;justify-content:space-between;
    padding-top:10px;border-top:1px solid var(--line);margin-top:auto;
}
.mc-eff{
    font-family:'Playfair Display',serif;
    font-size:18px;font-weight:400;color:var(--rose);line-height:1;
}
.mc-eff span{font-size:10px;font-weight:300;color:var(--ink3);font-family:'Jost',sans-serif;}
.mc-tags{display:flex;gap:4px;flex-wrap:wrap;justify-content:flex-end;}
.tag{
    font-size:9px;font-weight:500;letter-spacing:0.6px;text-transform:uppercase;
    padding:3px 8px;border-radius:100px;
}
.t-hf{background:var(--teal-soft);color:var(--teal);border:1px solid rgba(74,155,142,0.25);}
.t-low{background:var(--bg2);color:var(--ink3);border:1px solid var(--line);}
.t-med{background:var(--rose-pale);color:var(--rose);border:1px solid var(--rose-soft);}
.t-high{background:var(--bg3);color:var(--ink3);border:1px solid var(--line);}

.sec-about{background:var(--bg);}
.about-grid{display:grid;grid-template-columns:1fr 1.3fr;gap:72px;align-items:start;}
.about-list{display:flex;flex-direction:column;gap:28px;}
.ai{display:flex;gap:16px;}
.ai-dot{width:7px;height:7px;border-radius:50%;background:var(--rose);flex-shrink:0;margin-top:6px;}
.ai-t{font-size:13.5px;font-weight:500;color:var(--ink);margin-bottom:4px;}
.ai-d{font-size:12.5px;font-weight:300;color:var(--ink3);line-height:1.75;}
.about-panel{
    background:linear-gradient(150deg,var(--rose-pale) 0%,#fff 100%);
    border:1px solid var(--line);
    border-radius:12px;padding:40px;
    display:flex;flex-direction:column;gap:28px;
}
.panel-stat{border-bottom:1px solid var(--line);padding-bottom:22px;}
.panel-stat:last-child{border-bottom:none;padding-bottom:0;}
.pv{
    font-family:'Playfair Display',serif;
    font-size:42px;font-weight:400;color:var(--rose);line-height:1;
}
.pl{font-size:10.5px;letter-spacing:1px;text-transform:uppercase;color:var(--ink3);margin-top:5px;}

.disc{
    text-align:center;padding:26px 48px;
    border-top:1px solid var(--line);
    font-size:11.5px;letter-spacing:0.2px;color:var(--ink3);
    background:var(--bg2);
}
.disc span{color:var(--rose);font-weight:500;}

.reveal{opacity:0;transform:translateY(20px);transition:opacity 0.65s ease,transform 0.65s ease;}
.reveal.shown{opacity:1;transform:translateY(0);}

@media(max-width:1024px){
    .mgrid{grid-template-columns:repeat(3,1fr);}
    .about-grid{grid-template-columns:1fr;}
    .about-panel{display:none;}
}
@media(max-width:768px){
    nav{padding:0 20px;}
    .hero{padding:100px 24px 70px;}
    .hero-stats{gap:32px;flex-wrap:wrap;justify-content:center;}
    .wrap{padding:0 24px;}
    .steps{grid-template-columns:1fr;}
    .step{border-right:none;border-bottom:1px solid var(--line);}
    .mgrid{grid-template-columns:repeat(2,1fr);gap:12px;}
    .disc{padding:22px 24px;}
}
@media(max-width:480px){.mgrid{grid-template-columns:1fr;}}
</style>
</head>
<body>

<nav>
    <div class="mylogo-wrapper">
        <div class="mylogo-brand">
            <span class="mylogo-contra">Contra</span><span class="mylogo-choice">Choice</span>
        </div>
        <div class="mylogo-divider">
            <span class="mylogo-line"></span>
            <span class="mylogo-diamond"></span>
            <span class="mylogo-line"></span>
        </div>
    </div>
    <ul class="nav-links">
        <li><a href="#how">How It Works</a></li>
        <li><a href="#methods">Methods</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="/hci/user/login.php" class="cta">Login</a></li>
    </ul>
</nav>

<div class="hero">
    <div class="hero-deco deco1"></div>
    <div class="hero-deco deco2"></div>
    <div class="hero-deco deco3"></div>

    <div style="position:relative;z-index:2;">
        <div class="hero-eyebrow">Contraceptive Guidance Platform</div>
        <h1 class="hero-title">Find the <em>right</em></h1>
        <div class="hero-sub">contraceptive for you</div>
        <p class="hero-desc">A personalized questionnaire matched to your health, lifestyle, and goals — to help you walk into any clinic informed.</p>
        <div class="hero-stats">
            <div><span class="sv"><?= count($methods) ?>+</span><div class="sl">Methods</div></div>
            <div><span class="sv">99.9%</span><div class="sl">Max Effectiveness</div></div>
            <div><span class="sv"><?= count($category_labels) ?></span><div class="sl">Categories</div></div>
        </div>
    </div>

    <div class="scroll-hint">
        <div class="scroll-line"></div>
        Scroll
    </div>
</div>

<div class="divider"></div>

<div class="sec sec-how" id="how">
    <div class="wrap">
        <div class="reveal">
            <div class="sec-tag">How It Works</div>
            <h2 class="sec-title">Three steps to your <em>best match</em></h2>
            <p class="sec-sub">Our guided questionnaire analyzes your health profile and surfaces the most suitable options for you.</p>
        </div>
        <div class="steps reveal">
            <div class="step">
                <div class="step-n">01</div>
                <div class="step-t">Answer the Questionnaire</div>
                <p class="step-d">Share details about your health, lifestyle, smoking status, breastfeeding, and contraceptive preferences in a short, private form.</p>
            </div>
            <div class="step">
                <div class="step-n">02</div>
                <div class="step-t">Get Personalized Results</div>
                <p class="step-d">Our system scores and ranks methods based on your specific answers and health profile — surfacing the best fits for you.</p>
            </div>
            <div class="step">
                <div class="step-n">03</div>
                <div class="step-t">Consult Your Provider</div>
                <p class="step-d">Use your results as a starting point with a healthcare professional. ContraChoice informs — your doctor decides.</p>
            </div>
        </div>
    </div>
</div>

<div class="divider"></div>

<div class="sec sec-methods" id="methods">
    <div class="wrap">
        <div class="reveal">
            <div class="sec-tag">Contraceptive Methods</div>
            <h2 class="sec-title">All <?= count($methods) ?> methods, <em>explained</em></h2>
            <p class="sec-sub" style="margin-bottom:32px;">From daily pills to long-term devices and natural tracking — every option, clearly laid out.</p>
            <div class="filter-bar">
                <button class="fb on" onclick="filt(this,'all')">All</button>
                <?php foreach ($category_labels as $key => $lbl): ?>
                <button class="fb" onclick="filt(this,'<?= $key ?>')"><?= $lbl ?></button>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="mgrid reveal" id="mgrid">
            <?php
            $cost_cls = ['low'=>'t-low','medium'=>'t-med','high'=>'t-high'];
            foreach ($methods as $m):
                $cat = $m['category'];
            ?>
            <div class="mc" data-cat="<?= $cat ?>">
                <div class="mc-img">
                    <?php if (!empty($m['image_path'])): ?>
                        <img src="/hci/uploads/contraceptive_methods/<?= htmlspecialchars($m['image_path']) ?>"
                             alt="<?= htmlspecialchars($m['name']) ?>">
                    <?php else: ?>
                        <div class="mc-placeholder">
                            <i class="fa-solid <?= $cat_icons[$cat] ?>"></i>
                            <span>No image</span>
                        </div>
                    <?php endif; ?>
                    <span class="mc-badge"><?= $category_labels[$cat] ?></span>
                </div>
                <div class="mc-body">
                    <div class="mc-name"><?= htmlspecialchars($m['name']) ?></div>
                    <p class="mc-desc"><?= htmlspecialchars($m['description']) ?></p>
                    <div class="mc-foot">
                        <div class="mc-eff"><?= number_format($m['effectiveness'],1) ?><span>% effective</span></div>
                        <div class="mc-tags">
                            <?php if ($m['is_hormone_free']): ?><span class="tag t-hf">Hormone-free</span><?php endif; ?>
                            <span class="tag <?= $cost_cls[$m['cost_level']] ?>"><?= ucfirst($m['cost_level']) ?> cost</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="divider"></div>

<div class="sec sec-about" id="about">
    <div class="wrap">
        <div class="reveal" style="margin-bottom:48px;">
            <div class="sec-tag">About ContraChoice</div>
            <h2 class="sec-title">Information that <em>empowers</em></h2>
            <p class="sec-sub">An educational platform built on established medical knowledge. We inform — you and your doctor decide.</p>
        </div>
        <div class="about-grid reveal">
            <div class="about-list">
                <div class="ai">
                    <div class="ai-dot"></div>
                    <div><div class="ai-t">Comprehensive Method Database</div>
                    <p class="ai-d">Covers <?= count($methods) ?> methods across <?= count($category_labels) ?> categories including effectiveness rates, delivery methods, and contraindications.</p></div>
                </div>
                <div class="ai">
                    <div class="ai-dot"></div>
                    <div><div class="ai-t">Personalized Scoring System</div>
                    <p class="ai-d">The questionnaire accounts for smoking status, breastfeeding, health conditions, hormone preferences, budget, and delivery preference.</p></div>
                </div>
                <div class="ai">
                    <div class="ai-dot"></div>
                    <div><div class="ai-t">Community Forum</div>
                    <p class="ai-d">Read real experiences and ask questions anonymously. Learn from others who have used these methods.</p></div>
                </div>
                <div class="ai">
                    <div class="ai-dot"></div>
                    <div><div class="ai-t">Educational Use Only</div>
                    <p class="ai-d">ContraChoice does not provide medical advice. Always consult a licensed healthcare provider before starting or changing any contraceptive method.</p></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="disc">
    &copy; <?= date('Y') ?> <span>ContraChoice</span> 
</div>

<script>
function filt(btn,cat){
    document.querySelectorAll('.fb').forEach(b=>b.classList.remove('on'));
    btn.classList.add('on');
    document.querySelectorAll('.mc').forEach(c=>{
        c.classList.toggle('hidden',cat!=='all'&&c.dataset.cat!==cat);
    });
}

const obs=new IntersectionObserver(entries=>{
    entries.forEach(e=>{
        if(e.isIntersecting){e.target.classList.add('shown');obs.unobserve(e.target);}
    });
},{threshold:0.1});
document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));

document.querySelectorAll('a[href^="#"]').forEach(a=>{
    a.addEventListener('click',e=>{
        const t=document.querySelector(a.getAttribute('href'));
        if(t){e.preventDefault();t.scrollIntoView({behavior:'smooth'});}
    });
});
</script>
</body>
</html>