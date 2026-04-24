<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ContraChoice — Know Your Options</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink:        #141210;
            --ink-soft:   #2a2724;
            --muted:      #7a756e;
            --muted-lt:   #b0aba3;
            --cream:      #f9f7f3;
            --cream-dark: #f0ece4;
            --white:      #ffffff;
            --rose:       #b85c5c;
            --rose-deep:  #8f3f3f;
            --rose-pale:  #f3e0e0;
            --border:     rgba(0,0,0,0.08);
            --border-md:  rgba(0,0,0,0.13);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Jost', sans-serif;
            background: var(--cream);
            color: var(--ink);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── NAV ─── */
        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            padding: 0 56px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(249,247,243,0.92);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
        }

        .nav-brand {
            font-family: 'Libre Baskerville', serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--ink);
            text-decoration: none;
            letter-spacing: -0.3px;
        }
        .nav-brand em { font-style: italic; color: var(--rose); }

        .nav-center {
            display: flex;
            align-items: center;
            gap: 40px;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }
        .nav-center a {
            font-size: 13px;
            font-weight: 400;
            letter-spacing: 0.04em;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.2s;
        }
        .nav-center a:hover { color: var(--ink); }

        .btn-signin {
            font-size: 12.5px;
            font-weight: 500;
            letter-spacing: 0.06em;
            padding: 8px 20px;
            border: 1px solid var(--ink);
            border-radius: 2px;
            color: var(--ink);
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
        }
        .btn-signin:hover { background: var(--ink); color: var(--white); }

        /* ─── HERO ─── */
        .hero {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            padding-top: 64px;
        }

        .hero-left {
            padding: 80px 64px 80px 56px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-right: 1px solid var(--border-md);
            animation: fadeUp 0.8s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .hero-kicker {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 36px;
        }
        .hero-kicker-line {
            width: 32px;
            height: 1px;
            background: var(--rose);
        }
        .hero-kicker span {
            font-size: 10.5px;
            font-weight: 500;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--rose);
        }

        .hero-title {
            font-family: 'Libre Baskerville', serif;
            font-size: 62px;
            font-weight: 700;
            line-height: 1.05;
            letter-spacing: -1.5px;
            color: var(--ink);
            margin-bottom: 28px;
        }
        .hero-title em {
            font-style: italic;
            color: var(--rose);
            font-weight: 400;
        }

        .hero-desc {
            font-size: 15px;
            font-weight: 300;
            color: var(--muted);
            line-height: 1.8;
            max-width: 420px;
            margin-bottom: 48px;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .btn-primary {
            padding: 14px 32px;
            background: var(--ink);
            color: var(--white);
            font-family: 'Jost', sans-serif;
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 0.06em;
            text-decoration: none;
            border-radius: 2px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: background 0.2s, transform 0.15s;
        }
        .btn-primary:hover { background: var(--rose-deep); transform: translateY(-1px); color: var(--white); text-decoration: none; }

        .btn-text {
            font-size: 13px;
            font-weight: 400;
            color: var(--muted);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
        }
        .btn-text:hover { color: var(--ink); text-decoration: none; }

        .hero-right {
            background: var(--cream-dark);
            display: flex;
            flex-direction: column;
            animation: fadeUp 0.8s 0.12s ease both;
        }

        .hero-stat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            height: 100%;
        }

        .hero-stat {
            padding: 40px 36px;
            border-bottom: 1px solid var(--border-md);
            border-right: 1px solid var(--border-md);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            transition: background 0.2s;
        }
        .hero-stat:hover { background: var(--cream); }
        .hero-stat:nth-child(even) { border-right: none; }
        .hero-stat:nth-child(3),
        .hero-stat:nth-child(4) { border-bottom: none; }

        .stat-num {
            font-family: 'Libre Baskerville', serif;
            font-size: 48px;
            font-weight: 700;
            color: var(--ink);
            line-height: 1;
            margin-bottom: 8px;
            letter-spacing: -1px;
        }
        .stat-num.rose { color: var(--rose); }
        .stat-label {
            font-size: 12px;
            font-weight: 400;
            color: var(--muted);
            line-height: 1.5;
            letter-spacing: 0.01em;
        }

        /* ─── DIVIDER ─── */
        .divider {
            height: 1px;
            background: var(--border-md);
        }

        /* ─── FEATURES ─── */
        .features {
            padding: 100px 56px;
            background: var(--white);
        }

        .section-header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
            align-items: end;
            margin-bottom: 64px;
            padding-bottom: 48px;
            border-bottom: 1px solid var(--border);
        }

        .eyebrow {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .eyebrow-line { width: 24px; height: 1px; background: var(--rose); }
        .eyebrow span {
            font-size: 10.5px;
            font-weight: 500;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--rose);
        }

        .section-title {
            font-family: 'Libre Baskerville', serif;
            font-size: 42px;
            font-weight: 700;
            line-height: 1.1;
            letter-spacing: -1px;
            color: var(--ink);
        }
        .section-title em { font-style: italic; color: var(--rose); font-weight: 400; }

        .section-desc {
            font-size: 15px;
            font-weight: 300;
            color: var(--muted);
            line-height: 1.8;
            align-self: end;
        }

        .features-list {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0;
            border: 1px solid var(--border-md);
        }

        .feature-item {
            padding: 40px 36px;
            border-right: 1px solid var(--border-md);
            border-bottom: 1px solid var(--border-md);
            transition: background 0.2s;
        }
        .feature-item:hover { background: var(--cream); }
        .feature-item:nth-child(3n) { border-right: none; }
        .feature-item:nth-child(4),
        .feature-item:nth-child(5),
        .feature-item:nth-child(6) { border-bottom: none; }

        .feature-num {
            font-family: 'Libre Baskerville', serif;
            font-size: 11px;
            font-weight: 400;
            color: var(--muted-lt);
            margin-bottom: 24px;
            letter-spacing: 0.08em;
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--rose);
            margin-bottom: 20px;
        }

        .feature-title {
            font-family: 'Libre Baskerville', serif;
            font-size: 17px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 12px;
            letter-spacing: -0.3px;
        }

        .feature-desc {
            font-size: 13.5px;
            font-weight: 300;
            color: var(--muted);
            line-height: 1.75;
        }

        /* ─── METHODS ─── */
        .methods-section {
            padding: 100px 56px;
            background: var(--cream);
        }

        .methods-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 48px;
            padding-bottom: 32px;
            border-bottom: 1px solid var(--border-md);
            gap: 32px;
        }

        .methods-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0;
            border: 1px solid var(--border-md);
            background: var(--white);
        }

        .method-item {
            padding: 28px 24px;
            border-right: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
            transition: background 0.18s;
        }
        .method-item:hover { background: var(--rose-pale); }
        .method-item:nth-child(4n) { border-right: none; }
        .method-item:nth-child(9),
        .method-item:nth-child(10),
        .method-item:nth-child(11) { border-bottom: none; }

        .method-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .method-name {
            font-size: 13px;
            font-weight: 400;
            color: var(--ink-soft);
            line-height: 1.4;
        }

        /* ─── CTA ─── */
        .cta-section {
            background: var(--ink);
            padding: 100px 56px;
            position: relative;
            overflow: hidden;
        }

        .cta-texture {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.015) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.015) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        .cta-inner {
            position: relative;
            z-index: 1;
            max-width: 1100px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            gap: 80px;
        }

        .cta-kicker {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
        }
        .cta-kicker-line { width: 24px; height: 1px; background: var(--rose); }
        .cta-kicker span {
            font-size: 10.5px;
            font-weight: 500;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--rose);
        }

        .cta-title {
            font-family: 'Libre Baskerville', serif;
            font-size: 50px;
            font-weight: 700;
            color: var(--white);
            letter-spacing: -1.5px;
            line-height: 1.05;
            margin-bottom: 20px;
        }
        .cta-title em { font-style: italic; color: var(--rose); font-weight: 400; }

        .cta-desc {
            font-size: 15px;
            font-weight: 300;
            color: rgba(255,255,255,0.4);
            line-height: 1.8;
            max-width: 460px;
        }

        .cta-right {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
            flex-shrink: 0;
        }

        .btn-cta {
            padding: 16px 40px;
            background: var(--rose);
            color: var(--white);
            font-family: 'Jost', sans-serif;
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 0.06em;
            text-decoration: none;
            border-radius: 2px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: background 0.2s, transform 0.15s;
            white-space: nowrap;
        }
        .btn-cta:hover { background: var(--rose-deep); transform: translateY(-1px); color: var(--white); text-decoration: none; }

        .cta-note {
            font-size: 11px;
            color: rgba(255,255,255,0.2);
            letter-spacing: 0.04em;
        }

        /* ─── FOOTER ─── */
        footer {
            background: var(--ink);
            border-top: 1px solid rgba(255,255,255,0.06);
            padding: 28px 56px;
        }

        .footer-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
        }

        .footer-brand {
            font-family: 'Libre Baskerville', serif;
            font-size: 16px;
            font-weight: 700;
            color: rgba(255,255,255,0.5);
            text-decoration: none;
        }
        .footer-brand em { font-style: italic; color: var(--rose); }

        .footer-mid {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .footer-mid .dash { width: 16px; height: 1px; background: var(--rose); opacity: 0.5; }
        .footer-mid span { font-size: 11px; color: rgba(255,255,255,0.2); letter-spacing: 0.06em; }

        .footer-copy { font-size: 11px; color: rgba(255,255,255,0.15); }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 960px) {
            nav { padding: 0 24px; }
            .nav-center { display: none; }
            .hero { grid-template-columns: 1fr; min-height: auto; }
            .hero-left { padding: 60px 24px; border-right: none; border-bottom: 1px solid var(--border-md); }
            .hero-title { font-size: 44px; }
            .hero-right { min-height: 400px; }
            .features { padding: 72px 24px; }
            .section-header { grid-template-columns: 1fr; }
            .features-list { grid-template-columns: 1fr; }
            .feature-item { border-right: none; }
            .feature-item:nth-child(n) { border-bottom: 1px solid var(--border-md); }
            .feature-item:last-child { border-bottom: none; }
            .methods-section { padding: 72px 24px; }
            .methods-header { flex-direction: column; align-items: flex-start; }
            .methods-grid { grid-template-columns: 1fr 1fr; }
            .method-item:nth-child(2n) { border-right: none; }
            .method-item:nth-child(4n) { border-right: none; }
            .cta-section { padding: 72px 24px; }
            .cta-inner { grid-template-columns: 1fr; gap: 40px; }
            .cta-title { font-size: 36px; }
            footer { padding: 24px; }
        }
    </style>
</head>
<body>

<!-- NAV -->
<nav>
    <a href="#" class="nav-brand">Contra<em>Choice</em></a>
    <div class="nav-center">
        <a href="#features">Features</a>
        <a href="#methods">Methods</a>
        <a href="#about">About</a>
    </div>
    <a href="user/login.php" class="btn-signin">Sign In</a>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-left">
        <div class="hero-kicker">
            <div class="hero-kicker-line"></div>
            <span>Women's Health · SEAIT</span>
        </div>
        <h1 class="hero-title">
            Know your<br>
            <em>contraceptive</em><br>
            options.
        </h1>
        <p class="hero-desc">
            ContraChoice helps women make informed, confident decisions about birth control — through guided questionnaires, side-by-side comparisons, and personalized recommendations.
        </p>
        <div class="hero-actions">
            <a href="user/login.php" class="btn-primary">
                Get Started
                <svg width="14" height="14" viewBox="0 0 20 20" fill="none"><path d="M4 10h12M12 6l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <a href="#features" class="btn-text">
                Learn more
                <svg width="12" height="12" viewBox="0 0 20 20" fill="none"><path d="M10 4v12M4 10l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </div>
    </div>
    <div class="hero-right">
        <div class="hero-stat-grid">
            <div class="hero-stat">
                <div class="stat-num">11</div>
                <div class="stat-label">Contraceptive methods covered</div>
            </div>
            <div class="hero-stat">
                <div class="stat-num rose">99.9<span style="font-size:20px;letter-spacing:0;">%</span></div>
                <div class="stat-label">Highest effectiveness available</div>
            </div>
            <div class="hero-stat">
                <div class="stat-num" style="font-size:36px; letter-spacing:-0.5px;">Free</div>
                <div class="stat-label">No cost, always accessible</div>
            </div>
            <div class="hero-stat">
                <div class="stat-num rose" style="font-size:36px; letter-spacing:0;">AI</div>
                <div class="stat-label">Powered chatbot for your questions</div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="features" id="features">
    <div class="section-header">
        <div>
            <div class="eyebrow">
                <div class="eyebrow-line"></div>
                <span>What we offer</span>
            </div>
            <h2 class="section-title">Tools built for<br><em>informed choices</em></h2>
        </div>
        <p class="section-desc">Everything you need to understand, compare, and choose the right contraceptive method for your lifestyle and health.</p>
    </div>
    <div class="features-list">
        <div class="feature-item">
            <div class="feature-num">01</div>
            <div class="feature-icon">
                <svg width="24" height="24" viewBox="0 0 20 20" fill="none"><path d="M2.5 5h15M2.5 10h9M2.5 15h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="15.5" cy="14.5" r="2.5" stroke="currentColor" stroke-width="1.6"/><path d="M17.5 16.5l1.5 1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </div>
            <div class="feature-title">Smart Questionnaire</div>
            <p class="feature-desc">Answer a few guided questions about your health, lifestyle, and preferences to receive a tailored contraceptive recommendation.</p>
        </div>
        <div class="feature-item">
            <div class="feature-num">02</div>
            <div class="feature-icon">
                <svg width="24" height="24" viewBox="0 0 20 20" fill="none"><rect x="2" y="4" width="7" height="12" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="11" y="4" width="7" height="12" rx="1.5" stroke="currentColor" stroke-width="1.6"/><path d="M9 10h2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </div>
            <div class="feature-title">Comparison Guide</div>
            <p class="feature-desc">Compare contraceptive methods side by side — effectiveness, cost, side effects, and suitability — all in one clear view.</p>
        </div>
        <div class="feature-item">
            <div class="feature-num">03</div>
            <div class="feature-icon">
                <svg width="24" height="24" viewBox="0 0 20 20" fill="none"><rect x="3" y="5" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.6"/><circle cx="8" cy="10" r="1.5" fill="currentColor"/><circle cx="12" cy="10" r="1.5" fill="currentColor"/></svg>
            </div>
            <div class="feature-title">AI Chatbot</div>
            <p class="feature-desc">Have your questions answered anytime by our AI assistant — private, non-judgmental, and always available when you need guidance.</p>
        </div>
        <div class="feature-item">
            <div class="feature-num">04</div>
            <div class="feature-icon">
                <svg width="24" height="24" viewBox="0 0 20 20" fill="none"><path d="M10 2.5l2.2 4.5L17 7.7l-3.5 3.4.8 4.9L10 13.7l-4.3 2.3.8-4.9L3 7.7l4.8-.7L10 2.5z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
            </div>
            <div class="feature-title">Personalized Picks</div>
            <p class="feature-desc">Get recommendations matched specifically to your profile — taking into account your age, health conditions, and contraceptive goals.</p>
        </div>
        <div class="feature-item">
            <div class="feature-num">05</div>
            <div class="feature-icon">
                <svg width="24" height="24" viewBox="0 0 20 20" fill="none"><path d="M3 4h14v10h-7l-4 4v-4H3V4z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
            </div>
            <div class="feature-title">Anonymous Forum</div>
            <p class="feature-desc">Share experiences and ask questions in a safe, anonymous community of women navigating the same decisions as you.</p>
        </div>
        <div class="feature-item">
            <div class="feature-num">06</div>
            <div class="feature-icon">
                <svg width="24" height="24" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="7" stroke="currentColor" stroke-width="1.6"/><path d="M10 6v4.5l3 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </div>
            <div class="feature-title">Always Available</div>
            <p class="feature-desc">Access ContraChoice anytime, from any device. Your health information and recommendations are saved securely to your account.</p>
        </div>
    </div>
</section>

<!-- METHODS -->
<section class="methods-section" id="methods">
    <div class="methods-header">
        <div>
            <div class="eyebrow">
                <div class="eyebrow-line"></div>
                <span>Covered methods</span>
            </div>
            <h2 class="section-title">11 methods,<br><em>one platform</em></h2>
        </div>
        <p class="section-desc" style="max-width:340px;">From daily pills to long-term devices, we cover the full range of contraceptive options available to women today.</p>
    </div>
    <div class="methods-grid">
        <div class="method-item"><div class="method-dot" style="background:#b85c5c;"></div><span class="method-name">Combined Oral Contraceptive Pill</span></div>
        <div class="method-item"><div class="method-dot" style="background:#b85c5c;"></div><span class="method-name">Progestin-Only Pill</span></div>
        <div class="method-item"><div class="method-dot" style="background:#5c7e74;"></div><span class="method-name">Hormonal IUD</span></div>
        <div class="method-item"><div class="method-dot" style="background:#5c7e74;"></div><span class="method-name">Copper IUD</span></div>
        <div class="method-item"><div class="method-dot" style="background:#b07a5c;"></div><span class="method-name">Injectable Contraceptive</span></div>
        <div class="method-item"><div class="method-dot" style="background:#5c6e9e;"></div><span class="method-name">Condom</span></div>
        <div class="method-item"><div class="method-dot" style="background:#5c7e74;"></div><span class="method-name">Contraceptive Implant</span></div>
        <div class="method-item"><div class="method-dot" style="background:#8a6e9e;"></div><span class="method-name">Fertility Awareness Method</span></div>
        <div class="method-item"><div class="method-dot" style="background:#5c6e9e;"></div><span class="method-name">Diaphragm with Spermicide</span></div>
        <div class="method-item"><div class="method-dot" style="background:#b85c5c;"></div><span class="method-name">Emergency Contraceptive Pill</span></div>
        <div class="method-item"><div class="method-dot" style="background:#8a8278;"></div><span class="method-name">Bilateral Tubal Ligation</span></div>
        <div class="method-item" style="background:var(--cream); border:none; cursor:default;"></div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section" id="about">
    <div class="cta-texture"></div>
    <div class="cta-inner">
        <div>
            <div class="cta-kicker">
                <div class="cta-kicker-line"></div>
                <span>Get started today</span>
            </div>
            <h2 class="cta-title">Ready to make<br>an <em>informed</em> choice?</h2>
            <p class="cta-desc">Create your free account and take the guided questionnaire to find the contraceptive method that fits your life.</p>
        </div>
        <div class="cta-right">
            <a href="user/login.php" class="btn-cta">
                Sign In or Register
                <svg width="14" height="14" viewBox="0 0 20 20" fill="none"><path d="M4 10h12M12 6l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <div class="cta-note">Free to use · No prescription needed</div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="footer-inner">
        <a href="#" class="footer-brand">Contra<em>Choice</em></a>
        <div class="footer-mid">
            <div class="dash"></div>
            <span>SEAIT · Women's Health Research</span>
        </div>
        <div class="footer-copy">© 2026 ContraChoice. All rights reserved.</div>
    </div>
</footer>

</body>
</html>