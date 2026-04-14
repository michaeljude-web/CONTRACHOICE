<?php
session_start();
include '../includes/db_connection.php';
include '../includes/user/auth.php';

$page_title  = 'AI Chatbot';
$active_page = 'chatbot';
$user_name = $_SESSION['username'] ?? 'User';
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
  <style>
    :root {
      --bg: #f8f6f0;
      --surface: #ffffff;
      --border: #e8e4dc;
      --text: #2c2b28;
      --muted: #6b6b67;
      --blue-50: #e8f1fb;
      --blue-100: #b5d4f4;
      --blue-600: #185FA5;
      --blue-800: #0C447C;
      --bot-bg: #f4f3ef;
      --user-bg: #dceaf5;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { background: var(--bg); font-family: 'Outfit', sans-serif; color: var(--text); }
    .layout { display: flex; height: 100vh; overflow: hidden; }
    .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }

    /* topbar */
    .topbar {
      height: 52px; background: var(--surface);
      border-bottom: 0.5px solid var(--border);
      display: flex; align-items: center;
      padding: 0 28px; flex-shrink: 0;
      font-size: 13px; color: var(--muted);
    }
    .topbar b { color: var(--text); font-weight: 500; }

    /* page body */
    .page-body {
      flex: 1; overflow: hidden;
      display: flex; flex-direction: column;
      padding: 20px 24px 22px;
      min-height: 0;
    }

    /* chat card — full height, full width of content area */
    .chat-card {
      flex: 1; display: flex; flex-direction: column;
      background: var(--surface);
      border-radius: 20px;
      border: 0.5px solid var(--border);
      overflow: hidden;
      min-height: 0;
    }

    /* ── HEADER */
    .chat-header {
      display: flex; align-items: center; gap: 14px;
      padding: 16px 24px;
      border-bottom: 0.5px solid var(--border);
      flex-shrink: 0;
    }
    .bot-icon {
      width: 44px; height: 44px; border-radius: 50%;
      background: var(--blue-50); border: 0.5px solid var(--blue-100);
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }
    .bot-icon i { font-size: 19px; color: var(--blue-600); }
    .hd-text h2 {
      font-family: 'Playfair Display', serif;
      font-size: 17px; font-weight: 500; color: var(--text);
    }
    .hd-text p { font-size: 11.5px; color: var(--muted); margin-top: 1px; }
    .online-dot {
      width: 7px; height: 7px; border-radius: 50%;
      background: #3b6d11; display: inline-block; margin-right: 5px;
    }

    /* ── MESSAGES */
    .messages {
      flex: 1; overflow-y: auto;
      padding: 22px 24px;
      background: var(--bg);
      display: flex; flex-direction: column;
      gap: 16px; min-height: 0;
    }
    .messages::-webkit-scrollbar { width: 3px; }
    .messages::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

    .msg { display: flex; gap: 9px; max-width: 80%; animation: pop .18s ease; }
    .msg.user { align-self: flex-end; flex-direction: row-reverse; }
    .msg.bot  { align-self: flex-start; }
    @keyframes pop {
      from { opacity: 0; transform: translateY(5px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .av {
      width: 28px; height: 28px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0; font-size: 11px; margin-top: 3px;
      border: 0.5px solid var(--border);
    }
    .msg.user .av { background: var(--blue-600); color: #fff; border-color: var(--blue-600); }
    .msg.bot  .av { background: var(--surface); color: var(--blue-600); }

    .bubble {
      padding: 10px 15px;
      border-radius: 16px;
      font-size: 13.5px;
      line-height: 1.62;
      color: var(--text);
    }
    .msg.user .bubble {
      background: var(--user-bg);
      border-bottom-right-radius: 4px;
    }
    .msg.bot .bubble {
      background: var(--surface);
      border: 0.5px solid var(--border);
      border-bottom-left-radius: 4px;
    }
    .bubble strong { color: var(--blue-800); }
    .r-line   { margin: 1px 0; }
    .r-bullet { margin: 2px 0 2px 10px; }
    .r-arrow  { margin: 2px 0 2px 14px; color: var(--blue-600); font-size: 13px; }
    .r-space  { height: 5px; }
    .r-trow   {
      display: flex; gap: 10px; font-size: 13px;
      border-bottom: 0.5px solid var(--border); padding: 3px 0;
    }

    /* typing */
    .typing-wrap { display: flex; gap: 9px; align-self: flex-start; animation: pop .18s ease; }
    .typing-dots {
      display: flex; gap: 4px; align-items: center;
      padding: 10px 14px;
      background: var(--surface); border: 0.5px solid var(--border);
      border-radius: 16px; border-bottom-left-radius: 4px;
    }
    .typing-dots span {
      width: 6px; height: 6px; border-radius: 50%;
      background: var(--muted); animation: blink 1.4s infinite;
    }
    .typing-dots span:nth-child(2) { animation-delay: .2s; }
    .typing-dots span:nth-child(3) { animation-delay: .4s; }
    @keyframes blink {
      0%,60%,100% { opacity: .25; transform: translateY(0); }
      30% { opacity: 1; transform: translateY(-3px); }
    }

    /* ── CHIPS */
    .chips {
      display: flex; flex-wrap: wrap; gap: 7px;
      padding: 11px 24px;
      border-top: 0.5px solid var(--border);
      background: var(--surface); flex-shrink: 0;
    }
    .chip {
      background: var(--bg);
      border: 0.5px solid var(--border);
      border-radius: 30px; padding: 5px 13px;
      font-size: 12px; cursor: pointer;
      color: var(--muted); font-family: 'Outfit', sans-serif;
      transition: background .14s, color .14s, border-color .14s;
      white-space: nowrap;
    }
    .chip:hover { background: var(--blue-50); border-color: var(--blue-100); color: var(--blue-800); }

    /* ── INPUT */
    .input-row {
      display: flex; gap: 10px; align-items: flex-end;
      padding: 13px 24px 18px;
      border-top: 0.5px solid var(--border);
      background: var(--surface); flex-shrink: 0;
    }
    .input-row textarea {
      flex: 1;
      border: 0.5px solid var(--border);
      border-radius: 20px;
      padding: 10px 17px;
      font-family: 'Outfit', sans-serif;
      font-size: 13.5px;
      resize: none; height: 44px; max-height: 120px;
      line-height: 1.5; color: var(--text);
      background: var(--bg);
      transition: border-color .18s, background .18s;
    }
    .input-row textarea:focus {
      outline: none; border-color: var(--blue-600); background: var(--surface);
    }
    .input-row textarea::placeholder { color: var(--muted); opacity: .75; }
    .send {
      width: 44px; height: 44px; border-radius: 50%;
      border: none; background: var(--blue-600);
      color: #fff; font-size: 14px; cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0; transition: background .18s, transform .1s;
    }
    .send:hover { background: var(--blue-800); }
    .send:active { transform: scale(.95); }
    .send:disabled { background: var(--border); cursor: default; }

    /* disclaimer */
    .disclaimer {
      text-align: center; font-size: 11px;
      padding: 7px 24px;
      color: var(--muted); border-top: 0.5px solid var(--border);
      background: var(--surface); flex-shrink: 0;
    }
  </style>
</head>
<body>
<div class="layout">
  <?php include '../includes/user/sidebar.php'; ?>

  <div class="main">
    <div class="topbar">
      ContraChoice &rsaquo; <b>&nbsp;AI Chatbot</b>
    </div>

    <div class="page-body">
      <div class="chat-card">

        <!-- header -->
        <div class="chat-header">
          <div class="bot-icon"><i class="fas fa-robot"></i></div>
          <div class="hd-text">
            <h2>ContraChoice AI</h2>
            <p><span class="online-dot"></span>Online &mdash; Ask in English or Filipino, 24/7 &amp; anonymous</p>
          </div>
        </div>

        <!-- messages -->
        <div class="messages" id="msgs">
          <div class="msg bot">
            <div class="av"><i class="fas fa-robot"></i></div>
            <div class="bubble">
              <div class="r-line">Hi! I can answer questions about contraceptives and family planning in <strong>English or Filipino</strong>.</div>
              <div class="r-space"></div>
              <div class="r-line">Ask me about: <strong>pills, IUD, implant, injection, condom, emergency contraception, side effects, effectiveness, costs,</strong> and more.</div>
              <div class="r-space"></div>
              <div class="r-line">Puwede kang magtanong sa <strong>Tagalog o English</strong> — hindi kita hahatulan.</div>
            </div>
          </div>
        </div>

        <!-- quick chips -->
        <div class="chips" id="chips">
          <button class="chip" onclick="chip('What is the most effective contraceptive?')">Most effective method?</button>
          <button class="chip" onclick="chip('Ano ang side effects ng pills?')">Side effects ng pills</button>
          <button class="chip" onclick="chip('How does IUD work?')">How does IUD work?</button>
          <button class="chip" onclick="chip('Magkano ang contraceptive?')">Magkano?</button>
          <button class="chip" onclick="chip('Safe ba ang condom?')">Safe ba ang condom?</button>
          <button class="chip" onclick="chip('Emergency contraception after unprotected sex')">Emergency contraception</button>
          <button class="chip" onclick="chip('Hormone-free options')">Hormone-free</button>
          <button class="chip" onclick="chip('Contraceptive myths and misconceptions')">Myths &amp; facts</button>
        </div>

        <!-- input -->
        <div class="input-row">
          <textarea id="inp" placeholder="Magtanong o type your question..." rows="1"></textarea>
          <button class="send" id="snd" title="Send"><i class="fas fa-paper-plane"></i></button>
        </div>


      </div>
    </div>
  </div>
</div>

<script>
const knowledge = [
  { keywords:['hello','hi','hey','good morning','good afternoon','good evening','kumusta','musta','kamusta','magandang umaga','magandang hapon','magandang gabi'],
    response:`Hello!\n\nI'm ContraChoice AI. I can answer questions about contraceptives and family planning in **English or Filipino**.\n\nAsk me about: **pills, IUD, implant, injection, condom, emergency contraception, effectiveness, side effects, costs,** and more!` },
  { keywords:['gago'],
    response:`Ikaw ang gago putang ina mo!` },
  { keywords:['tangina','putangina','putang ina','bobo','tanga','ulol','lintik','hayop ka','fuck you','asshole','bitch','puta','pakyu'],
    response:`Pakiusap, maging magalang. Nandito ako para tumulong sa iyong mga tanong tungkol sa contraceptives — walang judgment. Subukan ulit!` },
  { keywords:['pill','pills','birth control pill','combined pill','mini pill','oral contraceptive','tableta','contraseptibo na tableta','inom ng pill','trust pill','diane','althea','daphne','nordette','micropills','micropil'],
    response:`**Birth Control Pills**\n\nMay dalawang uri:\n• **Combined pill** — may estrogen at progestin\n• **Mini-pill (progestin-only)** — para sa nagpapasuso o hindi makainom ng estrogen\n\n**Paano gamitin:** Uminom ng isang tableta araw-araw sa parehong oras.\n\n**Bisa:** ~93% (typical use), ~99% (perfect use)\n\n**Side effects:** pagduduwal, pananakit ng dibdib, pagbabago ng mood, irregular na regla — karaniwang nawawala pagkalipas ng 1–3 buwan.\n\n**Hindi angkop para sa:** Mga naninigarilyo na 35 pataas, o may kasaysayan ng blood clots.\n\n**Gastos:** ₱300–₱600/buwan sa botika; libre sa RHU/Botika ng Bayan.` },
  { keywords:['iud','intrauterine device','copper iud','hormonal iud','mirena','kyleena','paragard','tansong iud','lagay ng iud','iud sakit'],
    response:`**IUD (Intrauterine Device)**\n\nMaliit na T-shaped na device na inilalagay ng doktor sa loob ng bahay-bata.\n\n**Dalawang uri:**\n• **Copper IUD** — hormone-free, tumatagal hanggang 10 taon, pwede ring emergency contraception\n• **Hormonal IUD (Mirena/Kyleena)** — tumatagal 3–8 taon, nagpapagaan ng regla\n\n**Bisa:** >99% — isa sa pinaka-epektibong paraan\n\n**Side effects:**\n• Copper IUD: mas malakas o masakit na regla sa unang ilang buwan\n• Hormonal IUD: irregular spotting sa simula, pagkatapos ay magaan o mawawala ang regla\n\n**Reversible:** Oo — kapag inalis, maaaring mabuntis agad\n\n**Gastos:** ₱5,000–₱12,000 (one-time, taon ang tagal)` },
  { keywords:['implant','rod','nexplanon','arm implant','implanon','subdermal implant'],
    response:`**Contraceptive Implant**\n\nMaliit na plastic rod (laki ng posporo) na inilalagay sa ilalim ng balat ng itaas ng braso.\n\n**Paano gumagana:** Naglalabas ng progestin na pumipigil sa ovulation.\n\n**Tagal:** Hanggang 3 taon\n\n**Bisa:** >99%\n\n**Side effects:** Irregular na pagdurugo sa unang ilang buwan, posibleng mawala ang regla, sakit ng ulo.\n\n**Reversible:** Oo — kapag inalis, bumabalik agad ang fertility.\n\n**Gastos:** ₱2,000–₱8,000 (one-time, 3 taon)` },
  { keywords:['injection','depo','depo-provera','shot','depo shot','turok','injectable','dmpa','3 months injection','kada 3 buwan'],
    response:`**Contraceptive Injection (Depo-Provera)**\n\nTurok ng progestin ibinibigay ng nurse o doktor sa braso o puwit.\n\n**Dalas:** Kada 3 buwan (DMPA) o kada buwan\n\n**Bisa:** ~96% (typical), ~99% (perfect use)\n\n**Side effects:**\n• Irregular na pagdurugo o pagkawala ng regla\n• Posibleng pagtaba (~1–2 kg/taon)\n• Maantala ang pagbabalik ng fertility (3–18 buwan) pagkatapos itigil\n\n**Gastos:** ₱500–₱800 bawat 3 buwan; libre sa RHU` },
  { keywords:['condom','condoms','male condom','female condom','internal condom','external condom','rubber','barrier method','safe sex','gamit ng condom'],
    response:`**Condom**\n\n**Male condom:** Suotin sa titi bago ang intercourse.\n**Female condom:** Inilalagay sa loob ng vagina bago ang intercourse.\n\n**Bisa (male condom):** ~87% (typical), ~98% (perfect use)\n\n**Unique advantage:** Tanging paraan na nagbibigay ng proteksyon laban sa **STIs/STDs** (HIV, chlamydia, gonorrhea, atbp.).\n\n**Tips:**\n• Suriin ang expiry date bago buksan\n• Gumamit ng bagong condom sa bawat intercourse\n• Huwag gumamit ng oil-based lubricant — sisiraing ang condom\n• Water-based lubricant ang pwede\n\n**Gastos:** ₱20–₱100 bawat isa; walang reseta` },
  { keywords:['emergency contraception','morning after pill','plan b','ella','ecp','after sex','pagkatapos ng sex','hindi nag-condom','emergency pill','postech','postinor','levonorgestrel','72 hours','5 days after'],
    response:`**Emergency Contraception (EC)**\n\nPwedeng gamitin **pagkatapos** ng unprotected sex para maiwasan ang pagbubuntis.\n\n**Hindi ito abortion pill** — pumipigil sa pagbubuntis bago magsimula.\n\n**Mga opsyon:**\n\n• **Levonorgestrel (Plan B / Postinor / Postech)**\n  Inumin sa loob ng **72 oras** — mas maaga, mas epektibo (~89%)\n  Available sa botika nang walang reseta\n\n• **Ulipristal acetate (ella)**\n  Pwede hanggang **120 oras** (5 araw); mas epektibo sa 72–120 oras\n\n• **Copper IUD**\n  Pinaka-epektibo: >99% kung inilagay sa loob ng **5 araw**\n  Pwedeng regular contraceptive rin pagkatapos\n\n**Side effects ng pill:** Pagduduwal, sakit ng ulo, irregular na regla sa susunod na cycle.` },
  { keywords:['natural method','fertility awareness','calendar method','temperature method','cervical mucus','rhythm method','nfp','basal body temperature','bbt','billing method','natural na paraan','safe period','safe days'],
    response:`**Natural Family Planning (NFP)**\n\nNagsusubaybay sa natural na fertility signs para malaman kung kailan ka fertile.\n\n**Mga pamamaraan:**\n• **Calendar/Rhythm method** — binibilang ang cycle length\n• **Basal Body Temperature (BBT)** — sinusukat ang temperatura sa umaga\n• **Cervical Mucus (Billing) method** — pinupuna ang pagbabago ng mucus\n• **Symptothermal** — pinagsama ang BBT at cervical mucus\n\n**Bisa:** 76–88% (typical); hanggang 99% kung perfect use\n\n**Limitation:**\n• Hindi angkop kung irregular ang regla\n• Walang proteksyon laban sa STIs\n• Mas mataas ang failure rate kumpara sa hormonal methods` },
  { keywords:['withdrawal','pull out','coitus interruptus','bunot','hugot method','pullout'],
    response:`**Withdrawal Method (Pull-out)**\n\nPagbunot ng titi bago mag-ejaculate sa loob ng vagina.\n\n**Bisa:** ~78% (typical), ~96% (perfect use)\n\n**Pros:** Libre, walang side effects, walang reseta\n\n**Cons:**\n• May sperm sa pre-ejaculatory fluid (pre-cum)\n• Hindi proteksyon laban sa STIs\n• Mas mataas ang failure rate\n\nTip: Mas epektibo kung pinagsama sa condom o pills.` },
  { keywords:['most effective','pinaka epektibo','pinaka-epektibo','effectiveness','epektibo','gaano kaepektibo','compare','comparison','which is better','alin ang mas maganda','success rate','failure rate'],
    response:`**Effectiveness ng mga Contraceptive Methods**\n\n**Pinaka-epektibo (>99%):**\n• Implant\n• Hormonal IUD\n• Copper IUD\n• Vasectomy / Tubal ligation\n\n**Napaka-epektibo (91–99%):**\n• Injection (Depo) ~96%\n• Combined pill ~93%\n• Mini-pill ~93%\n\n**Epektibo (80–90%):**\n• Male condom ~87%\n• Diaphragm ~88%\n\n**Mas mababa:**\n• Withdrawal ~78%\n• Natural methods ~76–88%\n• Spermicide ~72%\n\nPara sa pinakamataas na proteksyon, gumamit ng **dalawang paraan** — halimbawa: pills + condom.` },
  { keywords:['side effect','side effects','masamang epekto','bleeding','irregular bleeding','spotting','weight gain','pagtaba','acne','pimples','mood','mood swings','nausea','pagduduwal','sakit ng ulo','headache','libido','depression','blood clot','epekto'],
    response:`**Common Side Effects ng Contraceptives**\n\n**Hormonal methods (pills, injection, implant, hormonal IUD):**\n• Irregular na pagdurugo o spotting (common sa unang 3–6 buwan)\n• Pagduduwal (pills — usually nawawala after ilang linggo)\n• Pananakit ng dibdib\n• Sakit ng ulo o migraine\n• Pagbabago ng mood o depresyon\n• Pagtaba (mas karaniwan sa injection)\n\n**Copper IUD:**\n• Mas malakas at mas masakit na regla sa unang 3–6 buwan\n\n**Non-hormonal (condom, natural):**\n• Halos walang systemic side effects\n\n**Kailan kakausapin ang doktor:**\n• Sobrang sakit ng tiyan, matinding sakit ng ulo, pananakit ng binti, chest pain` },
  { keywords:['hormone free','non hormonal','without hormones','walang hormones','hormone-free','ayaw ng hormones','no hormones'],
    response:`**Hormone-Free Contraceptive Options**\n\n**Long-acting:**\n• **Copper IUD** — >99% epektibo, hanggang 10 taon\n\n**Barrier methods:**\n• **Male condom** — ~87%; nagpoprotekta rin sa STIs\n• **Female condom** — ~79%\n• **Diaphragm / cervical cap** — ~88%\n\n**Behavior-based:**\n• **Fertility Awareness Methods** — 76–88%\n• **Withdrawal** — ~78% (hindi ipinapayo bilang tanging paraan)\n\n**Permanent:**\n• Vasectomy o Tubal ligation\n\nAng **Copper IUD** ang pinaka-epektibong hormone-free option para sa long-term na proteksyon.` },
  { keywords:['cost','price','expensive','cheap','affordable','gastos','halaga','magkano','libre','free contraceptive','rhu','botika ng bayan','presyo','mahal','mura'],
    response:`**Gastos ng mga Contraceptive**\n\n| Paraan | Gastos |\n| Pills | ₱300–₱600/buwan |\n| Injection (Depo) | ₱500–₱800 bawat 3 buwan |\n| Condom | ₱20–₱100 bawat isa |\n| IUD | ₱5,000–₱12,000 (one-time, 3–10 taon) |\n| Implant | ₱2,000–₱8,000 (one-time, 3 taon) |\n| Emergency pill | ₱150–₱400 |\n\n**Saan makakakuha ng LIBRE o mura:**\n• RHU (Rural Health Unit) — pills, injection, condom\n• City/Barangay Health Center\n• Botika ng Bayan — subsidized\n• POPCOM centers\n• Likhaan Center for Women` },
  { keywords:['breastfeeding','postpartum','after birth','nagpapasuso','pagpapasuso','pagkatapos manganak','after delivery','bagong ina','LAM','lactation'],
    response:`**Contraceptive para sa Nagpapasusong Ina**\n\n**Ligtas gamitin habang nagpapasuso:**\n• Progestin-only pill (mini-pill)\n• Contraceptive implant\n• Contraceptive injection (Depo)\n• Hormonal IUD\n• Copper IUD\n• Condom\n\n**Iwasan:**\n• Combined pill (may estrogen) — maaaring makabawas ng gatas sa unang 6 na linggo\n\n**Lactational Amenorrhea Method (LAM):**\n• Exclusively breastfeeding + wala pang regla + wala pang 6 na buwan ang baby = ~98% epektibo\n• Pagkatapos ng 6 na buwan: kailangan na ng ibang paraan` },
  { keywords:['smoker','smoking','smoke','naninigarilyo','yosi','cigarette','nagsisigarilyo'],
    response:`**Contraceptive para sa mga Naninigarilyo**\n\nAng paninigarilyo (lalo na kung 35 pataas) ay nagpapataas ng panganib ng blood clots, stroke, at heart attack kapag gumagamit ng estrogen-containing na contraceptive.\n\n**Ligtas para sa mga naninigarilyo:**\n• Progestin-only pill (mini-pill)\n• Implant\n• Injection\n• IUD (copper o hormonal)\n• Condom\n\n**Iwasan kung 35+ at naninigarilyo:**\n• Combined pill\n• Contraceptive patch\n• Vaginal ring` },
  { keywords:['how to choose','which method','best for me','recommend','paano pumili','alin ang maganda','rekomendasyon','ano ang tamang','para sa akin','angkop','advice','suggest','help me choose'],
    response:`**Paano Pumili ng Tamang Contraceptive?**\n\n**Gusto mo ng long-term at halos 100% epektibo:**\n→ IUD o Implant\n\n**Ayaw mo ng hormones:**\n→ Copper IUD o Condom\n\n**Nagpapasuso ka:**\n→ Mini-pill, Implant, Injection, o IUD\n\n**Naninigarilyo ka (35 pataas):**\n→ Progestin-only methods, IUD, o Condom\n\n**Kailangan din ng STI protection:**\n→ Condom (solo o kasabay ng ibang paraan)\n\n**Ayaw ng araw-araw na pills:**\n→ Injection (bawat 3 buwan) o Implant/IUD (taon)\n\n**May limitadong budget:**\n→ Pills o Condom mula sa RHU (libre)\n\nPalaging kumonsulta sa doktor o midwife para sa personalized na rekomendasyon.` },
  { keywords:['where to get','saan makakakuha','saan mabibili','prescription','clinic','pharmacy','botika','doktor','reseta','klinika','ospital','health center','rhu','available'],
    response:`**Saan Makakakuha ng Contraceptive?**\n\n**Sa botika (walang reseta):**\n• Condom, Spermicide\n• Emergency pills (Postinor, Postech)\n\n**Sa botika (may reseta):**\n• Birth control pills\n• Emergency pill (ella)\n\n**Sa doktor / health provider:**\n• IUD insertion\n• Implant insertion\n• Injection\n\n**Libre o subsidized:**\n• RHU / Barangay Health Center — pills, injection, condom\n• Botika ng Bayan\n• POPCOM — family planning services\n• Likhaan Center for Women\n• Government hospitals (OB clinic)` },
  { keywords:['sti','std','sexually transmitted','hiv','aids','chlamydia','gonorrhea','syphilis','herpes','hpv','proteksyon sa sti'],
    response:`**STI/STD Protection**\n\nKaramihan sa contraceptives ay **hindi nagpoprotekta** sa STIs.\n\n**Tanging paraan na nagbibigay ng STI protection:**\n• Male condom — pinaka-epektibo\n• Female condom\n\n**Hindi nagpoprotekta sa STI:**\n• Pills, IUD, Implant, Injection, Natural methods, Withdrawal\n\n**Para sa pinakamataas na proteksyon:**\n→ Gumamit ng **condom + isa pang contraceptive** (double protection)\n\nAng regular STI testing ay inirerekomenda para sa lahat ng sexually active, kahit walang symptoms.` },
  { keywords:['myth','totoo ba','misconception','fake news','sabi nila','narinig ko','is it true','katotohanan','pamahiin','hindi totoo'],
    response:`**Common Myths tungkol sa Contraceptives**\n\n**"Ang pills ay nagpapabuntis kapag itinigil."**\n→ HINDI TOTOO. Maaaring mabuntis agad pagkatapos itigil.\n\n**"Ang IUD ay nagpapalaglag (abortion)."**\n→ HINDI. Pumipigil ang IUD sa fertilization — hindi ito abortion.\n\n**"Hindi mabubuntis sa first time."**\n→ MALI. Mabubuntis kahit first time, kahit anong oras ng cycle.\n\n**"Ang pills ay nagpapataba."**\n→ May minimal effect lang. Ang injection ang mas may kaugnayan sa weight gain.\n\n**"Hindi mabubuntis kung nagpapasuso."**\n→ HINDI GANAP NA TOTOO. Pagkatapos ng 6 na buwan, kailangan na ng ibang paraan.\n\n**"Ang withdrawal ay epektibo."**\n→ 78% lang ang bisa — hindi ipinapayo bilang tanging paraan.` },
  { keywords:['permanent','tubal ligation','vasectomy','sterilization','ayaw nang magkaanak','BTL'],
    response:`**Permanent Contraceptive Methods**\n\nPara sa mga **sigurado na ayaw nang magkaanak**:\n\n**Tubal Ligation (para sa babae):**\n• Pinutol/sealed ang fallopian tubes\n• Bisa: >99%; libre sa government hospitals\n\n**Vasectomy (para sa lalaki):**\n• Pinutol/sealed ang vas deferens\n• Bisa: >99%\n• Mas simple, mas mabilis, at mas mura kaysa tubal ligation\n• Libre rin sa ilang government hospitals\n\nKailangan ng counseling bago ang procedure.` },
  { keywords:['paano gumagana','how does it work','mechanism','paano pumipigil','how do pills work','how does iud work'],
    response:`**Paano Gumagana ang mga Contraceptive?**\n\n**Hormonal methods (pills, injection, implant, hormonal IUD):**\n• Pumipigil sa ovulation (walang itlog na nilalabas)\n• Pinapa-espeso ang cervical mucus\n• Pinipayat ang uterine lining\n\n**Copper IUD:**\n• Copper ions = nakakalason sa sperm\n• Pumipigil sa fertilization\n\n**Condom:**\n• Physical barrier — pumipigil sa pagpasok ng sperm\n• Pumipigil din sa STI transmission\n\n**Emergency contraception:**\n• Pumipigil o nagpapaliban ng ovulation\n• Hindi ito abortion pill` },
  { keywords:['diabetes','hypertension','high blood','heart disease','migraine','PCOS','endometriosis','may migraine','may diabetes','may sakit'],
    response:`**Contraceptive at Health Conditions**\n\n**Hypertension:**\n• Iwasan ang combined pill\n• Ligtas: Progestin-only methods, IUD, condom\n\n**Diabetes:**\n• Karamihan sa hormonal methods ay ligtas sa controlled diabetes\n• Kumonsulta sa doktor\n\n**Migraine with aura:**\n• Iwasan ang combined pill — nagpapataas ng risk ng stroke\n• Ligtas: Progestin-only methods, IUD, condom\n\n**PCOS:**\n• Combined pill — maaaring makatulong sa pag-regulate ng regla\n\n**Endometriosis:**\n• Hormonal IUD o combined pill — maaaring makatulong sa pain\n\nMahalagang kumonsulta sa iyong doktor bago magsimula ng anumang contraceptive.` }
];

const FB_EN = `I'm designed to answer questions about contraceptives and family planning.\n\nYou can ask me about:\n• Pills, IUD, implant, injection, condom\n• Emergency contraception\n• Effectiveness and side effects\n• Costs and where to get them\n• How to choose the right method\n\nWhat would you like to know?`;
const FB_FIL = `Sumasagot lang ako sa mga tanong tungkol sa contraceptives at family planning.\n\nPwede kang magtanong tungkol sa:\n• Pills, IUD, implant, injection, condom\n• Emergency contraception\n• Effectiveness at side effects\n• Gastos at saan makakakuha\n• Paano pumili ng tamang paraan\n\nAno ang gusto mong malaman?`;

function detectLang(t) {
  const l = t.toLowerCase();
  const fil = ['po','ba','ko','mo','ako','siya','tayo','kayo','kami','ang','ng','sa','ay','mga','ito','iyan','yun','dito','paano','bakit','ano','saan','magkano','pwede','gusto','kailangan','meron','wala','hindi','huwag','salamat','talaga','naman','lang','din','rin','kasi','kaya','pero','dahil','kapag','kung'];
  return fil.filter(w => l.split(/[\s,!?.]+/).includes(w)).length >= 1 ? 'fil' : 'en';
}

function getReply(msg) {
  const l = msg.toLowerCase();
  let best = null, score = 0;
  for (const item of knowledge) {
    let s = 0;
    for (const kw of item.keywords) if (l.includes(kw)) s += kw.split(' ').length + 1;
    if (s > score) { score = s; best = item; }
  }
  if (score > 0) return best.response;
  return detectLang(msg) === 'fil' ? FB_FIL : FB_EN;
}

function fmt(text) {
  const d = document.createElement('div');
  d.className = 'bubble';
  let h = '';
  for (const line of text.split('\n')) {
    let l = line.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    if (!l.trim()) h += '<div class="r-space"></div>';
    else if (l.startsWith('• ')) h += `<div class="r-bullet">${l}</div>`;
    else if (l.startsWith('→ ')) h += `<div class="r-arrow">${l}</div>`;
    else if (l.startsWith('| ') && !l.startsWith('|---')) {
      const cells = l.split('|').filter(c => c.trim());
      h += `<div class="r-trow">`;
      cells.forEach((c,i) => { h += `<span style="flex:${i===0?'1.5':'1'};${i===0?'font-weight:500':''};">${c.trim()}</span>`; });
      h += '</div>';
    } else h += `<div class="r-line">${l}</div>`;
  }
  d.innerHTML = h;
  return d;
}

const msgBox = document.getElementById('msgs');
const inp    = document.getElementById('inp');
const snd    = document.getElementById('snd');

function addMsg(text, isUser) {
  const w = document.createElement('div');
  w.className = `msg ${isUser ? 'user' : 'bot'}`;
  const av = document.createElement('div');
  av.className = 'av';
  av.innerHTML = isUser ? '<i class="fas fa-user"></i>' : '<i class="fas fa-robot"></i>';
  let b;
  if (isUser) { b = document.createElement('div'); b.className = 'bubble'; b.innerText = text; }
  else b = fmt(text);
  w.appendChild(av); w.appendChild(b);
  msgBox.appendChild(w);
  msgBox.scrollTop = msgBox.scrollHeight;
}

function showTyping() {
  const w = document.createElement('div'); w.className = 'typing-wrap'; w.id = 'typ';
  const av = document.createElement('div'); av.className = 'av'; av.innerHTML = '<i class="fas fa-robot"></i>';
  const d = document.createElement('div'); d.className = 'typing-dots';
  d.innerHTML = '<span></span><span></span><span></span>';
  w.appendChild(av); w.appendChild(d);
  msgBox.appendChild(w); msgBox.scrollTop = msgBox.scrollHeight;
}
function removeTyping() { const e = document.getElementById('typ'); if (e) e.remove(); }

function send(msg) {
  const m = msg || inp.value.trim();
  if (!m) return;
  inp.disabled = snd.disabled = true;
  addMsg(m, true);
  if (!msg) { inp.value = ''; inp.style.height = '44px'; }
  showTyping();
  setTimeout(() => { removeTyping(); addMsg(getReply(m), false); inp.disabled = snd.disabled = false; inp.focus(); }, 650);
}

function chip(t) { document.getElementById('chips').style.display = 'none'; send(t); }

snd.addEventListener('click', () => send());
inp.addEventListener('keypress', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); } });
inp.addEventListener('input', function() { this.style.height = 'auto'; this.style.height = Math.min(this.scrollHeight, 120) + 'px'; });
</script>
</body>
</html>