<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ContraChoice — Know Your Options</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500;1,600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --rose:       #C1666B;
            --rose-deep:  #9E4A4F;
            --rose-pale:  #F5DDE0;
            --rose-blush: #FDF0F1;
            --cream:      #FAF7F2;
            --cream-dark: #F2EDE4;
            --ink:        #1C1A18;
            --ink-soft:   #2E2B28;
            --muted:      #8A8278;
            --border:     rgba(0,0,0,0.07);
            --white:      #ffffff;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--ink);
            overflow-x: hidden;
        }

        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            padding: 20px 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(250,247,242,0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
        }

        .nav-brand {
            font-family: 'Cormorant Garamond', serif;
            font-size: 22px;
            font-weight: 600;
            color: var(--ink);
            letter-spacing: -0.5px;
            text-decoration: none;
        }
        .nav-brand em { font-style: italic; color: var(--rose); }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 36px;
        }

        .nav-links a {
            font-size: 13.5px;
            font-weight: 500;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.2s;
        }
        .nav-links a:hover { color: var(--ink); }

        .btn-nav {
            padding: 9px 22px;
            background: var(--ink);
            color: var(--white);
            border-radius: 40px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.2s, transform 0.15s;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }
        .btn-nav:hover {
            background: var(--rose-deep);
            transform: translateY(-1px);
            color: var(--white);
            text-decoration: none;
        }

        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 120px 48px 80px;
            position: relative;
            overflow: hidden;
        }

        .hero-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
        }

        .hero-bg .blob-1 {
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(193,102,107,0.1) 0%, transparent 70%);
            top: -100px;
            right: -100px;
        }

        .hero-bg .blob-2 {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(193,102,107,0.06) 0%, transparent 70%);
            bottom: 0;
            left: 200px;
        }

        .hero-bg .dot-grid {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(0,0,0,0.06) 1px, transparent 1px);
            background-size: 32px 32px;
            opacity: 0.5;
        }

        .hero-inner {
            position: relative;
            z-index: 1;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 80px;
        }

        .hero-left { animation: fadeUp 0.7s ease both; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: var(--rose-blush);
            border: 1px solid rgba(193,102,107,0.25);
            border-radius: 40px;
            padding: 5px 13px 5px 9px;
            margin-bottom: 28px;
        }
        .hero-badge .pip {
            width: 7px; height: 7px;
            background: var(--rose);
            border-radius: 50%;
            animation: pulse 2.4s ease-in-out infinite;
        }
        @keyframes pulse {
            0%,100% { opacity: 1; transform: scale(1); }
            50%      { opacity: .5; transform: scale(0.8); }
        }
        .hero-badge span {
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--rose-deep);
        }

        .hero-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 68px;
            font-weight: 600;
            line-height: 1.0;
            letter-spacing: -2px;
            color: var(--ink);
            margin-bottom: 22px;
        }
        .hero-title em {
            font-style: italic;
            color: var(--rose);
        }

        .hero-desc {
            font-size: 15.5px;
            color: var(--muted);
            line-height: 1.75;
            max-width: 440px;
            margin-bottom: 40px;
        }

        .hero-cta {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .btn-primary {
            padding: 14px 32px;
            background: var(--ink);
            color: var(--white);
            border-radius: 40px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary:hover {
            background: var(--rose-deep);
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(158,74,79,0.3);
            color: var(--white);
            text-decoration: none;
        }

        .btn-ghost {
            padding: 14px 28px;
            background: transparent;
            color: var(--muted);
            border-radius: 40px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            border: 1.5px solid var(--border);
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-ghost:hover {
            border-color: rgba(193,102,107,0.3);
            color: var(--rose-deep);
            text-decoration: none;
        }

        .hero-right {
            animation: fadeUp 0.7s 0.15s ease both;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .stat-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 24px 22px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.06);
        }
        .stat-card.accent {
            background: var(--ink);
            border-color: var(--ink);
        }
        .stat-card.rose {
            background: var(--rose-blush);
            border-color: rgba(193,102,107,0.2);
        }

        .stat-num {
            font-family: 'Cormorant Garamond', serif;
            font-size: 44px;
            font-weight: 600;
            color: var(--ink);
            line-height: 1;
            margin-bottom: 6px;
            letter-spacing: -1px;
        }
        .stat-card.accent .stat-num { color: var(--white); }
        .stat-card.rose .stat-num { color: var(--rose-deep); }

        .stat-label {
            font-size: 12px;
            font-weight: 500;
            color: var(--muted);
            letter-spacing: 0.03em;
        }
        .stat-card.accent .stat-label { color: rgba(255,255,255,0.5); }
        .stat-card.rose .stat-label { color: var(--rose); }

        .features {
            padding: 100px 48px;
            background: var(--white);
            position: relative;
        }

        .section-inner {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-eyebrow {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--rose);
            margin-bottom: 12px;
        }

        .section-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 48px;
            font-weight: 600;
            color: var(--ink);
            letter-spacing: -1.5px;
            line-height: 1.1;
            margin-bottom: 16px;
        }
        .section-title em { font-style: italic; color: var(--rose); }

        .section-sub {
            font-size: 15px;
            color: var(--muted);
            line-height: 1.7;
            max-width: 520px;
            margin-bottom: 56px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .feature-card {
            background: var(--cream);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 28px 26px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 32px rgba(0,0,0,0.06);
        }

        .feature-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: var(--rose-blush);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
            color: var(--rose);
        }

        .feature-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 20px;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 10px;
        }

        .feature-desc {
            font-size: 13.5px;
            color: var(--muted);
            line-height: 1.7;
        }

        .methods-section {
            padding: 100px 48px;
            background: var(--cream);
        }

        .methods-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 40px;
        }

        .method-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 40px;
            font-size: 13.5px;
            font-weight: 500;
            color: var(--ink-soft);
            transition: all 0.2s;
            cursor: default;
        }
        .method-pill:hover {
            border-color: rgba(193,102,107,0.3);
            background: var(--rose-blush);
            color: var(--rose-deep);
            transform: translateY(-2px);
        }

        .method-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .cta-section {
            padding: 100px 48px;
            background: var(--ink);
            position: relative;
            overflow: hidden;
        }

        .cta-section .blob {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(193,102,107,0.15) 0%, transparent 70%);
            top: -150px;
            right: -100px;
        }

        .cta-section .dot-grid {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 28px 28px;
        }

        .cta-inner {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 48px;
            flex-wrap: wrap;
        }

        .cta-text .section-eyebrow { color: var(--rose-pale); opacity: 0.7; }

        .cta-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 52px;
            font-weight: 600;
            color: var(--white);
            letter-spacing: -1.5px;
            line-height: 1.05;
            margin-bottom: 16px;
        }
        .cta-title em { font-style: italic; color: var(--rose); }

        .cta-desc {
            font-size: 15px;
            color: rgba(255,255,255,0.5);
            line-height: 1.7;
            max-width: 440px;
        }

        .cta-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 14px;
            flex-shrink: 0;
        }

        .btn-cta {
            padding: 15px 36px;
            background: var(--rose);
            color: var(--white);
            border-radius: 40px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }
        .btn-cta:hover {
            background: var(--rose-deep);
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(193,102,107,0.4);
            color: var(--white);
            text-decoration: none;
        }

        .cta-note {
            font-size: 12px;
            color: rgba(255,255,255,0.3);
        }

        footer {
            padding: 32px 48px;
            background: var(--ink);
            border-top: 1px solid rgba(255,255,255,0.06);
        }

        .footer-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .footer-brand {
            font-family: 'Cormorant Garamond', serif;
            font-size: 18px;
            font-weight: 600;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
        }
        .footer-brand em { font-style: italic; color: var(--rose); }

        .footer-copy {
            font-size: 12px;
            color: rgba(255,255,255,0.25);
        }

        .footer-inst {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .footer-inst .dash {
            width: 16px; height: 2px;
            background: var(--rose);
        }
        .footer-inst span {
            font-size: 11px;
            color: rgba(255,255,255,0.3);
            letter-spacing: 0.05em;
        }

        @media (max-width: 900px) {
            nav { padding: 16px 24px; }
            .nav-links { gap: 20px; }
            .hero { padding: 100px 24px 60px; }
            .hero-inner { grid-template-columns: 1fr; gap: 48px; }
            .hero-title { font-size: 48px; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .features { padding: 72px 24px; }
            .features-grid { grid-template-columns: 1fr; }
            .methods-section { padding: 72px 24px; }
            .cta-section { padding: 72px 24px; }
            .cta-inner { flex-direction: column; }
            .cta-title { font-size: 38px; }
            footer { padding: 24px; }
        }
    </style>
</head>
<body>

<nav>
    <a href="#" class="nav-brand">Contra<em>Choice</em></a>
    <div class="nav-links">
        <a href="#features">Features</a>
        <a href="#methods">Methods</a>
        <a href="user/login.php" class="btn-nav">
            <svg width="14" height="14" viewBox="0 0 20 20" fill="none"><path d="M7.5 4H5.5A1.5 1.5 0 004 5.5v9A1.5 1.5 0 005.5 16h2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12.5 12.5L16 9l-3.5-3.5M16 9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Sign In
        </a>
    </div>
</nav>

<section class="hero">
    <div class="hero-bg">
        <div class="blob-1"></div>
        <div class="blob-2"></div>
        <div class="dot-grid"></div>
    </div>
    <div class="hero-inner">
        <div class="hero-left">
            <div class="hero-badge">
                <div class="pip"></div>
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
            <div class="hero-cta">
                <a href="user/login.php" class="btn-primary">
                    Get Started
                    <svg width="14" height="14" viewBox="0 0 20 20" fill="none"><path d="M4 10h12M12 6l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
                <a href="#features" class="btn-ghost">Learn more</a>
            </div>
        </div>
        <div class="hero-right">
            <div class="stats-grid">
                <div class="stat-card accent">
                    <div class="stat-num">11</div>
                    <div class="stat-label">Contraceptive methods covered</div>
                </div>
                <div class="stat-card rose">
                    <div class="stat-num">99.9<span style="font-size:22px;">%</span></div>
                    <div class="stat-label">Highest effectiveness available</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num" style="color:var(--rose);">Free</div>
                    <div class="stat-label">No cost, no prescription needed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num">AI</div>
                    <div class="stat-label">Powered chatbot for your questions</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="features" id="features">
    <div class="section-inner">
        <div class="section-eyebrow">What we offer</div>
        <h2 class="section-title">Tools built for<br><em>informed choices</em></h2>
        <p class="section-sub">Everything you need to understand, compare, and choose the right contraceptive method for your lifestyle and health.</p>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="22" height="22" viewBox="0 0 20 20" fill="none"><path d="M2.5 5h15M2.5 10h9M2.5 15h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="15.5" cy="14.5" r="2.5" stroke="currentColor" stroke-width="2"/><path d="M17.5 16.5l1.5 1.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <div class="feature-title">Smart Questionnaire</div>
                <p class="feature-desc">Answer a few guided questions about your health, lifestyle, and preferences to receive a tailored contraceptive recommendation.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="22" height="22" viewBox="0 0 20 20" fill="none"><rect x="2" y="4" width="7" height="12" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="11" y="4" width="7" height="12" rx="1.5" stroke="currentColor" stroke-width="2"/><path d="M9 10h2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <div class="feature-title">Comparison Guide</div>
                <p class="feature-desc">Compare contraceptive methods side by side — effectiveness, cost, side effects, and suitability — all in one clear view.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="22" height="22" viewBox="0 0 20 20" fill="none"><rect x="3" y="5" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2"/><circle cx="8" cy="10" r="1.5" fill="currentColor"/><circle cx="12" cy="10" r="1.5" fill="currentColor"/></svg>
                </div>
                <div class="feature-title">AI Chatbot</div>
                <p class="feature-desc">Have your questions answered anytime by our AI assistant — private, non-judgmental, and always available when you need guidance.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="22" height="22" viewBox="0 0 20 20" fill="none"><path d="M10 2.5l2.2 4.5L17 7.7l-3.5 3.4.8 4.9L10 13.7l-4.3 2.3.8-4.9L3 7.7l4.8-.7L10 2.5z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
                </div>
                <div class="feature-title">Personalized Picks</div>
                <p class="feature-desc">Get recommendations matched specifically to your profile — taking into account your age, health conditions, and contraceptive goals.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="22" height="22" viewBox="0 0 20 20" fill="none"><path d="M3 4h14v10h-7l-4 4v-4H3V4z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
                </div>
                <div class="feature-title">Anonymous Forum</div>
                <p class="feature-desc">Share experiences and ask questions in a safe, anonymous community of women navigating the same decisions as you.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="22" height="22" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="7" stroke="currentColor" stroke-width="2"/><path d="M10 6v4.5l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <div class="feature-title">Always Available</div>
                <p class="feature-desc">Access ContraChoice anytime, from any device. Your health information and recommendations are saved securely to your account.</p>
            </div>
        </div>
    </div>
</section>

<section class="methods-section" id="methods">
    <div class="section-inner">
        <div class="section-eyebrow">Covered methods</div>
        <h2 class="section-title">11 methods,<br><em>one platform</em></h2>
        <p class="section-sub">From daily pills to long-term devices, we cover the full range of contraceptive options available to women today.</p>
        <div class="methods-row">
            <div class="method-pill"><div class="method-dot" style="background:#C1666B;"></div> Combined Oral Contraceptive Pill</div>
            <div class="method-pill"><div class="method-dot" style="background:#C1666B;"></div> Progestin-Only Pill</div>
            <div class="method-pill"><div class="method-dot" style="background:#7A9E8E;"></div> Hormonal IUD</div>
            <div class="method-pill"><div class="method-dot" style="background:#7A9E8E;"></div> Copper IUD</div>
            <div class="method-pill"><div class="method-dot" style="background:#C8956C;"></div> Injectable Contraceptive</div>
            <div class="method-pill"><div class="method-dot" style="background:#6A90C4;"></div> Condom</div>
            <div class="method-pill"><div class="method-dot" style="background:#7A9E8E;"></div> Contraceptive Implant</div>
            <div class="method-pill"><div class="method-dot" style="background:#B07CC6;"></div> Fertility Awareness Method</div>
            <div class="method-pill"><div class="method-dot" style="background:#6A90C4;"></div> Diaphragm with Spermicide</div>
            <div class="method-pill"><div class="method-dot" style="background:#C1666B;"></div> Emergency Contraceptive Pill</div>
            <div class="method-pill"><div class="method-dot" style="background:#9A9289;"></div> Bilateral Tubal Ligation</div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="blob"></div>
    <div class="dot-grid"></div>
    <div class="cta-inner">
        <div class="cta-text">
            <div class="section-eyebrow">Get started today</div>
            <h2 class="cta-title">Ready to make<br>an <em>informed</em> choice?</h2>
            <p class="cta-desc">Create your free account and take the guided questionnaire to find the contraceptive method that fits your life.</p>
        </div>
        <div class="cta-actions">
            <a href="user/login.php" class="btn-cta">
                Sign In or Register
                <svg width="14" height="14" viewBox="0 0 20 20" fill="none"><path d="M4 10h12M12 6l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <div class="cta-note">Free to use · No prescription needed</div>
        </div>
    </div>
</section>

<footer>
    <div class="footer-inner">
        <a href="#" class="footer-brand">Contra<em>Choice</em></a>
        <div class="footer-inst">
            <div class="dash"></div>
            <span>SEAIT · Women's Health Research</span>
        </div>
        <div class="footer-copy">© 2026 ContraChoice. All rights reserved.</div>
    </div>
</footer>

</body>
</html>