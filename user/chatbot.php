<?php
session_start();
include '../includes/db_connection.php';
include '../includes/user/auth.php';

$page_title  = 'AI Chatbot';
$active_page = 'chatbot';
$is_admin    = false;

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
      --bg-dirty: #f8f6f0;
      --surface: #ffffff;
      --border-soft: #e8e4dc;
      --text-primary: #2c2b28;
      --text-secondary: #6b6b67;
      --blue-soft: #dceaf5;
      --blue-600: #185FA5;
      --blue-800: #0C447C;
      --chat-bot-msg: #f1f0ec;
      --chat-user-msg: #dceaf5;
    }
    body { background: var(--bg-dirty); font-family: 'Outfit', sans-serif; }
    .cc-layout { display: flex; height: 100vh; overflow: hidden; }
    .cc-main { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: var(--bg-dirty); }
    .topbar {
      height: 52px; background: var(--surface); border-bottom: 0.5px solid var(--border-soft);
      display: flex; align-items: center; justify-content: space-between; padding: 0 24px;
    }
    .topbar-title { font-family: 'Playfair Display', serif; font-size: 14px; }
    .topbar-title em { font-style: italic; color: var(--blue-600); }
    .topbar-user { font-size: 12px; background: #eef2f0; padding: 4px 12px; border-radius: 30px; }
    .content-area { flex: 1; overflow-y: auto; padding: 28px; display: flex; justify-content: center; align-items: flex-start; }
    .chat-container {
      max-width: 800px;
      width: 100%;
      background: var(--surface);
      border-radius: 28px;
      border: 1px solid var(--border-soft);
      overflow: hidden;
      box-shadow: 0 8px 24px rgba(0,0,0,0.04);
    }
    .chat-header {
      background: var(--surface);
      padding: 18px 24px;
      border-bottom: 1px solid var(--border-soft);
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .chat-header i {
      font-size: 28px;
      color: var(--blue-600);
    }
    .chat-header h2 {
      font-family: 'Playfair Display', serif;
      font-size: 20px;
      margin: 0;
      font-weight: 500;
    }
    .chat-header p {
      margin: 0;
      font-size: 12px;
      color: var(--text-secondary);
    }
    .chat-messages {
      height: 460px;
      overflow-y: auto;
      padding: 20px 24px;
      background: var(--bg-dirty);
      display: flex;
      flex-direction: column;
      gap: 16px;
    }
    .message {
      display: flex;
      gap: 12px;
      max-width: 85%;
      animation: fadeIn 0.2s ease;
    }
    .message.user {
      align-self: flex-end;
      flex-direction: row-reverse;
    }
    .message.bot {
      align-self: flex-start;
    }
    .message-avatar {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: var(--surface);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      border: 1px solid var(--border-soft);
    }
    .message.user .message-avatar {
      background: var(--blue-600);
      color: white;
    }
    .message.bot .message-avatar {
      background: var(--surface);
      color: var(--blue-600);
    }
    .message-bubble {
      background: var(--surface);
      padding: 10px 16px;
      border-radius: 18px;
      font-size: 14px;
      line-height: 1.5;
      color: var(--text-primary);
      box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .message.user .message-bubble {
      background: var(--chat-user-msg);
      border-bottom-right-radius: 4px;
    }
    .message.bot .message-bubble {
      background: var(--chat-bot-msg);
      border-bottom-left-radius: 4px;
    }
    .chat-input-area {
      padding: 16px 24px 24px;
      background: var(--surface);
      border-top: 1px solid var(--border-soft);
      display: flex;
      gap: 12px;
      align-items: flex-end;
    }
    .chat-input-area textarea {
      flex: 1;
      border: 1px solid var(--border-soft);
      border-radius: 24px;
      padding: 10px 18px;
      font-family: 'Outfit', sans-serif;
      font-size: 14px;
      resize: none;
      height: 48px;
      transition: border-color 0.2s;
    }
    .chat-input-area textarea:focus {
      outline: none;
      border-color: var(--blue-600);
    }
    .chat-input-area button {
      background: var(--blue-600);
      border: none;
      border-radius: 30px;
      width: 48px;
      height: 48px;
      color: white;
      font-size: 18px;
      transition: background 0.2s;
    }
    .chat-input-area button:hover { background: var(--blue-800); }
    .typing-indicator {
      display: flex;
      gap: 4px;
      align-items: center;
      padding: 8px 16px;
      background: var(--chat-bot-msg);
      border-radius: 20px;
      width: fit-content;
    }
    .typing-indicator span {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: var(--text-secondary);
      animation: blink 1.4s infinite;
    }
    .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
    .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes blink {
      0%, 60%, 100% { opacity: 0.3; transform: translateY(0); }
      30% { opacity: 1; transform: translateY(-4px); }
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(8px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .disclaimer {
      font-size: 11px;
      text-align: center;
      padding: 12px;
      color: var(--text-secondary);
      border-top: 1px solid var(--border-soft);
      background: var(--surface);
    }
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
      <div class="chat-container">
        <div class="chat-header">
          <i class="fas fa-robot"></i>
          <div>
            <h2>ContraChoice AI</h2>
            <p>Tanong tungkol sa contraceptive? / Ask me about birth control — 24/7, anonymous.</p>
          </div>
        </div>
        <div class="chat-messages" id="chatMessages">
          <div class="message bot">
            <div class="message-avatar"><i class="fas fa-robot"></i></div>
            <div class="message-bubble">
              👋 Hi! I can answer in English or Filipino. Ask me about pills, IUDs, implants, injections, condoms, effectiveness, side effects, and more.<br><br>
              👋 Puwede kang magtanong sa Tagalog o English tungkol sa mga contraceptive: pills, IUD, implant, injection, condom, effectiveness, side effects, atbp.
            </div>
          </div>
        </div>
        <div class="chat-input-area">
          <textarea id="userInput" placeholder="Magtanong o type your question..." rows="1"></textarea>
          <button id="sendBtn"><i class="fas fa-paper-plane"></i></button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  
  const knowledge = [
    {
      en_keywords: ['pill', 'birth control pill', 'combined pill', 'mini pill', 'oral contraceptive', 'contraceptive pill'],
      fil_keywords: ['pill', 'birth control pill', 'tabletas', 'contraseptibo na tableta', 'pills', 'oral na tableta'],
      en_response: "Birth control pills contain hormones (estrogen and/or progestin). You take one pill daily at the same time. Typical effectiveness is about 93% (99% with perfect use). Common side effects: nausea, breast tenderness, mood changes. Not recommended for smokers over 35.",
      fil_response: "Ang birth control pills ay naglalaman ng hormones (estrogen at/o progestin). Iniinom ito araw-araw sa parehong oras. Mga 93% ang epektibo nito sa karaniwang paggamit (99% kung perpekto). Karaniwang side effects: pagduduwal, pananakit ng dibdib, pagbabago ng mood. Hindi ito rekomendado sa mga naninigarilyo na edad 35 pataas."
    },
    {
      en_keywords: ['gago', 'tangina', 'putangina', 'putang ina', 'bobo', 'tanga', 'ulol', 'lintik', 'hayop', 'fuck', 'shit', 'damn', 'stupid', 'idiot'],
      fil_keywords: ['gago', 'tangina', 'putangina', 'putang ina', 'bobo', 'tanga', 'ulol', 'lintik', 'hayop'],
      en_response: "Please be respectful. I'm here to help answer your questions about contraceptives and family planning. How can I assist you today?",
      fil_response: "Ikaw ang gago putang ina mo!"
    },
    {
      en_keywords: ['iud', 'intrauterine device', 'copper iud', 'hormonal iud', 'mirena', 'kyleena', 'paragard'],
      fil_keywords: ['iud', 'intrauterine device', 'copper iud', 'hormonal iud', 'tansong iud', 'mirena'],
      en_response: "An IUD is a small T-shaped device placed inside the uterus by a doctor. Copper IUDs are hormone‑free and last up to 10 years; hormonal IUDs last 3–8 years. Effectiveness >99%. Side effects: Copper IUD may cause heavier periods; hormonal IUD may cause irregular spotting then lighter periods.",
      fil_response: "Ang IUD ay maliit na aparatong hugis-T na inilalagay sa loob ng bahay-bata ng doktor. Ang Copper IUD ay hormone‑free at tumatagal ng hanggang 10 taon; ang hormonal IUD ay 3–8 taon. Epektibo ito ng higit 99%. Side effects: Ang Copper IUD ay maaaring magpalakas ng regla; ang hormonal IUD ay maaaring magdulot ng hindi regular na spotting tapos magiging mahina ang regla."
    },
    {
      en_keywords: ['implant', 'rod', 'nexplanon', 'arm implant'],
      fil_keywords: ['implant', 'rod', 'nexplanon', 'implant sa braso'],
      en_response: "The implant is a small rod placed under the skin of your upper arm. It releases progestin and works for up to 3 years. Effectiveness over 99%. Side effects: irregular bleeding, possible absence of periods, headaches.",
      fil_response: "Ang implant ay maliit na rod na inilalagay sa ilalim ng balat ng iyong braso. Naglalabas ito ng progestin at tumatagal ng hanggang 3 taon. Higit 99% ang bisa. Side effects: hindi regular na pagdurugo, posibleng mawala ang regla, sakit ng ulo."
    },
    {
      en_keywords: ['injection', 'depo', 'depo-provera', 'shot', 'depo shot'],
      fil_keywords: ['injection', 'depo', 'depo-provera', 'turok', 'depo shot'],
      en_response: "Depo-Provera is an injection of progestin given every 3 months. Effectiveness is about 96% with typical use. Side effects: irregular bleeding, weight gain, possible delay in return of fertility after stopping.",
      fil_response: "Ang Depo-Provera ay turok ng progestin na ibinibigay kada 3 buwan. Mga 96% ang epektibo sa karaniwang paggamit. Side effects: hindi regular na pagdurugo, pagtaba, posibleng maantala ang pagbalik ng fertility pagkatapos itigil."
    },
    {
      en_keywords: ['condom', 'condoms', 'barrier method', 'male condom', 'female condom'],
      fil_keywords: ['condom', 'condoms', 'barrier method', 'condom panlalaki', 'condom pambabae'],
      en_response: "Condoms are barrier methods that prevent pregnancy and protect against STIs. Male condoms have 87% typical effectiveness (98% perfect use). They are hormone‑free and available without prescription. Use a new condom for each sex act.",
      fil_response: "Ang condom ay barrier method na pumipigil sa pagbubuntis at nakakaprotekta laban sa STIs. Ang condom panlalaki ay 87% ang bisa sa karaniwang paggamit (98% kung perpekto). Hormone‑free ito at nabibili nang walang reseta. Gumamit ng bagong condom sa bawat pagtatalik."
    },
    {
      en_keywords: ['emergency contraception', 'morning after pill', 'plan b', 'ella', 'copper iud emergency'],
      fil_keywords: ['emergency contraception', 'morning after pill', 'plan b', 'ella', 'emergency pill', 'pang-emergency na pills'],
      en_response: "Emergency contraception can prevent pregnancy after unprotected sex. Options: levonorgestrel pill (Plan B) up to 72 hours; ella (ulipristal acetate) up to 120 hours; Copper IUD up to 5 days — also serves as ongoing contraception. They are not abortion pills.",
      fil_response: "Ang emergency contraception ay pumipigil sa pagbubuntis pagkatapos ng hindi protektadong pagtatalik. Mga opsyon: levonorgestrel pill (Plan B) hanggang 72 oras; ella (ulipristal acetate) hanggang 120 oras; Copper IUD hanggang 5 araw — puwede ring pangmatagalang contraceptive. Hindi ito abortion pills."
    },
    {
      en_keywords: ['natural method', 'fertility awareness', 'calendar method', 'temperature method', 'cervical mucus'],
      fil_keywords: ['natural method', 'fertility awareness', 'calendar method', 'temperature method', 'cervical mucus', 'natural na paraan', 'calendar method'],
      en_response: "Natural family planning involves tracking your fertility signs (temperature, cervical mucus, cycle length). Effectiveness varies (76–88% typical use). Requires daily tracking and abstinence during fertile window. No side effects but higher failure rate.",
      fil_response: "Ang natural family planning ay pagsubaybay sa iyong fertility signs (temperatura, cervical mucus, haba ng cycle). Nag-iiba ang bisa (76–88% sa karaniwang paggamit). Kailangan ng araw-araw na pagsubaybay at pag-iwas sa pagtatalik sa fertile window. Walang side effects pero mas mataas ang tsansang mabuntis."
    },
    {
      en_keywords: ['withdrawal', 'pull out', 'coitus interruptus'],
      fil_keywords: ['withdrawal', 'pull out', 'coitus interruptus', 'hugot', 'bunot'],
      en_response: "Withdrawal (pulling out before ejaculation) has about 78% typical effectiveness. It is free and has no side effects, but requires strong self‑control and does not protect against STIs.",
      fil_response: "Ang withdrawal (pagbunot bago labasan) ay may 78% na bisa sa karaniwang paggamit. Libre ito at walang side effects, pero kailangan ng matinding disiplina at hindi ito proteksyon laban sa STIs."
    },
    {
      en_keywords: ['effectiveness', 'how effective', 'success rate'],
      fil_keywords: ['effectiveness', 'epektibo', 'gaano kaepektibo', 'bisa'],
      en_response: "Effectiveness varies by method. Long‑acting reversible methods (IUD, implant) >99%. Pill/injection ~93-96%. Condoms ~87%. Withdrawal ~78%. Natural methods ~76-88%. Perfect use rates are higher for most methods.",
      fil_response: "Nag-iiba ang bisa depende sa paraan. Long‑acting reversible methods (IUD, implant) >99%. Pill/injection ~93-96%. Condom ~87%. Withdrawal ~78%. Natural methods ~76-88%. Mas mataas ang perfect use rates para sa karamihan."
    },
    {
      en_keywords: ['side effect', 'side effects', 'bleeding', 'weight gain', 'acne', 'mood', 'nausea'],
      fil_keywords: ['side effect', 'side effects', 'bleeding', 'weight gain', 'acne', 'mood', 'nausea', 'epekto'],
      en_response: "Side effects depend on the method. Hormonal methods may cause nausea, breast tenderness, mood changes, irregular bleeding, or weight changes. Copper IUD can cause heavier periods. Always discuss side effects with your doctor.",
      fil_response: "Ang mga side effects ay depende sa paraan. Ang hormonal methods ay maaaring magdulot ng pagduduwal, pananakit ng dibdib, pagbabago ng mood, hindi regular na pagdurugo, o pagtaba. Ang Copper IUD ay puwedeng magpalakas ng regla. Palaging kumonsulta sa doktor tungkol sa side effects."
    },
    {
      en_keywords: ['hormone free', 'non hormonal', 'without hormones'],
      fil_keywords: ['hormone free', 'non hormonal', 'without hormones', 'walang hormones'],
      en_response: "Hormone‑free methods include: Copper IUD, condoms, diaphragm, cervical cap, sponge, fertility awareness, withdrawal. These do not contain estrogen or progestin and have no hormonal side effects.",
      fil_response: "Ang mga hormone‑free methods ay: Copper IUD, condom, diaphragm, cervical cap, sponge, fertility awareness, withdrawal. Wala itong estrogen o progestin at walang hormonal side effects."
    },
    {
      en_keywords: ['cost', 'price', 'expensive', 'cheap', 'affordable'],
      fil_keywords: ['cost', 'price', 'expensive', 'cheap', 'affordable', 'gastos', 'halaga', 'magkano'],
      en_response: "Costs vary: Pills ₱300–600/month; IUD/implant ₱5,000–12,000 one‑time (lasts years); condoms ₱20–50 each; injection ₱500–800 every 3 months. Many public health centers offer free or low‑cost contraceptives.",
      fil_response: "Iba-iba ang gastos: Pills ₱300–600/buwan; IUD/implant ₱5,000–12,000 isang beses (taon ang tagal); condom ₱20–50 bawat isa; injection ₱500–800 kada 3 buwan. Maraming public health center ang nagbibigay ng libre o murang contraceptive."
    },
    {
      en_keywords: ['how to choose', 'which method', 'best for me', 'recommend'],
      fil_keywords: ['how to choose', 'which method', 'best for me', 'recommend', 'paano pumili', 'alin ang maganda', 'rekomendasyon'],
      en_response: "Choosing a method depends on your health, lifestyle, and preferences. Consider: Do you want hormones? How long? Can you take a daily pill? Do you need STI protection? I recommend taking our questionnaire for personalized suggestions, but always consult a doctor.",
      fil_response: "Ang pagpili ng paraan ay depende sa iyong kalusugan, pamumuhay, at kagustuhan. Isipin: Gusto mo ba ng hormones? Gaano katagal? Kaya mo bang uminom araw-araw? Kailangan mo ba ng proteksyon sa STI? Inirerekomenda kong sagutan ang aming questionnaire para sa personal na rekomendasyon, pero kumonsulta palagi sa doktor."
    },
    {
      en_keywords: ['doctor', 'prescription', 'where to get', 'clinic', 'pharmacy'],
      fil_keywords: ['doctor', 'prescription', 'where to get', 'clinic', 'pharmacy', 'doktor', 'reseta', 'saan kukuha', 'klinika', 'botika'],
      en_response: "Pills, IUD, implant, injection require a prescription and a doctor's visit. Condoms, spermicides, and emergency pills (Plan B) are available over‑the‑counter at pharmacies. Many public health centers (e.g., Likhaan, POPCOM) offer free services.",
      fil_response: "Ang pills, IUD, implant, injection ay nangangailangan ng reseta at konsultasyon sa doktor. Ang condom, spermicides, at emergency pills (Plan B) ay mabibili sa botika nang walang reseta. Maraming public health center (tulad ng Likhaan, POPCOM) ang nagbibigay ng libreng serbisyo."
    },
    {
      en_keywords: ['breastfeeding', 'postpartum', 'after birth'],
      fil_keywords: ['breastfeeding', 'postpartum', 'after birth', 'nagpapasuso', 'pagpapasuso', 'pagkatapos manganak'],
      en_response: "Breastfeeding women can use progestin‑only methods (mini‑pill, implant, injection, hormonal IUD) safely. Estrogen‑containing methods (combined pill) may reduce milk supply. Copper IUD is also safe. Always discuss with your doctor.",
      fil_response: "Ang mga nagpapasusong ina ay puwedeng gumamit ng progestin‑only methods (mini‑pill, implant, injection, hormonal IUD) nang ligtas. Ang estrogen‑containing methods (combined pill) ay maaaring makabawas ng gatas. Ligtas din ang Copper IUD. Kumonsulta sa doktor."
    },
    {
      en_keywords: ['smoker', 'smoking', 'smoke'],
      fil_keywords: ['smoker', 'smoking', 'smoke', 'naninigarilyo', 'yosi'],
      en_response: "Smoking (especially over age 35) increases the risk of blood clots with combined hormonal contraceptives (pill, patch, ring). Smokers are advised to use progestin‑only methods or non‑hormonal options. Consult your doctor.",
      fil_response: "Ang paninigarilyo (lalo na kung 35 pataas) ay nagpapataas ng panganib ng blood clots sa combined hormonal contraceptives (pill, patch, ring). Ang mga naninigarilyo ay pinapayuhan na gumamit ng progestin‑only methods o non‑hormonal options. Kumonsulta sa doktor."
    }
  ];

  const FALLBACK_EN = "I only answer questions related to contraceptive methods. Please ask about pills, IUDs, implants, injections, condoms, emergency contraception, effectiveness, side effects, or how to choose a method.";
  const FALLBACK_FIL = "Sumasagot lang ako sa mga tanong tungkol sa contraceptive. Puwede kang magtanong tungkol sa pills, IUD, implant, injection, condom, emergency contraception, effectiveness, side effects, o kung paano pumili ng paraan.";

  function detectLanguage(text) {
    const lower = text.toLowerCase();
    const filIndicators = ['po', 'ba', 'ako', 'mo', 'siya', 'tayo', 'kayo', 'ang', 'ng', 'sa', 'ay', 'mga', 'ito', 'iyan', 'yun', 'dito', 'doon', 'paano', 'bakit', 'ano', 'saan', 'kailan', 'magkano', 'pwede', 'puede', 'gusto', 'kailangan', 'meron', 'wala', 'tanong', 'sagot', 'doktor', 'reseta', 'gamot', 'tableta', 'injection', 'turok', 'condom', 'iud', 'implant', 'pill', 'birth control', 'contraceptive'];
    for (let word of filIndicators) {
      if (lower.includes(word)) return 'fil';
    }
  
    for (let item of knowledge) {
      for (let kw of item.fil_keywords) {
        if (lower.includes(kw.toLowerCase())) return 'fil';
      }
    }
    return 'en';
  }

  function getBotResponse(userMessage) {
    const lowerMsg = userMessage.toLowerCase();
    const lang = detectLanguage(userMessage);
    
    let related = false;
    for (let item of knowledge) {
      for (let kw of item.en_keywords) {
        if (lowerMsg.includes(kw.toLowerCase())) { related = true; break; }
      }
      if (related) break;
      for (let kw of item.fil_keywords) {
        if (lowerMsg.includes(kw.toLowerCase())) { related = true; break; }
      }
      if (related) break;
    }
    if (!related) {
      return lang === 'fil' ? FALLBACK_FIL : FALLBACK_EN;
    }

    for (let item of knowledge) {
      // Check English keywords
      for (let kw of item.en_keywords) {
        if (lowerMsg.includes(kw.toLowerCase())) {
          return lang === 'fil' ? item.fil_response : item.en_response;
        }
      }
      // Check Filipino keywords
      for (let kw of item.fil_keywords) {
        if (lowerMsg.includes(kw.toLowerCase())) {
          return lang === 'fil' ? item.fil_response : item.en_response;
        }
      }
    }
    
    if (lang === 'fil') {
      return "Magandang tanong iyan tungkol sa contraceptive. Puwede mo bang palawakin? Halimbawa, magtanong tungkol sa isang partikular na paraan (pills, IUD, implant, injection, condom), side effects, effectiveness, o kung paano pumili. Nandito lang ako para tumulong!";
    } else {
      return "That's a good question about contraception. Could you be more specific? For example, ask about a particular method (pills, IUD, implant, injection, condom), side effects, effectiveness, or how to choose. I'm here to help!";
    }
  }

  const chatMessagesDiv = document.getElementById('chatMessages');
  const userInput = document.getElementById('userInput');
  const sendBtn = document.getElementById('sendBtn');

  function addMessage(text, isUser = false) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isUser ? 'user' : 'bot'}`;
    const avatar = document.createElement('div');
    avatar.className = 'message-avatar';
    avatar.innerHTML = isUser ? '<i class="fas fa-user"></i>' : '<i class="fas fa-robot"></i>';
    const bubble = document.createElement('div');
    bubble.className = 'message-bubble';
    bubble.innerText = text;
    messageDiv.appendChild(avatar);
    messageDiv.appendChild(bubble);
    chatMessagesDiv.appendChild(messageDiv);
    chatMessagesDiv.scrollTop = chatMessagesDiv.scrollHeight;
  }

  function showTypingIndicator() {
    const typingDiv = document.createElement('div');
    typingDiv.id = 'typingIndicator';
    typingDiv.className = 'message bot';
    typingDiv.innerHTML = `
      <div class="message-avatar"><i class="fas fa-robot"></i></div>
      <div class="typing-indicator"><span></span><span></span><span></span></div>
    `;
    chatMessagesDiv.appendChild(typingDiv);
    chatMessagesDiv.scrollTop = chatMessagesDiv.scrollHeight;
  }

  function removeTypingIndicator() {
    const indicator = document.getElementById('typingIndicator');
    if (indicator) indicator.remove();
  }

  async function sendMessage() {
    const message = userInput.value.trim();
    if (message === '') return;
    userInput.disabled = true;
    sendBtn.disabled = true;
    addMessage(message, true);
    userInput.value = '';
    userInput.style.height = '48px';
    showTypingIndicator();
    setTimeout(() => {
      const reply = getBotResponse(message);
      removeTypingIndicator();
      addMessage(reply, false);
      userInput.disabled = false;
      sendBtn.disabled = false;
      userInput.focus();
    }, 600);
  }

  sendBtn.addEventListener('click', sendMessage);
  userInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });
  userInput.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
  });
</script>
</body>
</html>