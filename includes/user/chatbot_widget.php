
<!-- ══════════════════════════════════════
     ContraChoice — Floating Chatbot Widget
-->
<link rel="stylesheet" href="/hci/assets/vendor/fontawesome-7/css/all.min.css">
<!-- ══════════════════════════════════════
     ContraChoice — Floating Chatbot Widget
     Usage: 
     ══════════════════════════════════════ -->

<!-- Floating Button -->
<button class="cc-fab" id="ccFab" onclick="ccToggle()" title="Chat with AI">
  <i class="fas fa-comment-dots cc-fab-icon-open"></i>
  <i class="fas fa-xmark cc-fab-icon-close" style="display:none;"></i>
  <span class="cc-fab-badge" id="ccBadge">1</span>
</button>

<!-- Chat Modal -->
<div class="cc-widget" id="ccWidget">
  <div class="cc-widget-header">
    <div class="cc-widget-header-left">
      <div class="cc-widget-avatar"><i class="fas fa-robot"></i></div>
      <div>
        <div class="cc-widget-title">ContraChoice AI</div>
        <div class="cc-widget-sub"><span class="cc-online-dot"></span>Online &mdash; Anonymous</div>
      </div>
    </div>
    <button class="cc-widget-close" onclick="ccToggle()"><i class="fas fa-xmark"></i></button>
  </div>

  <div class="cc-widget-msgs" id="ccMsgs">
    <div class="cc-msg cc-bot">
      <div class="cc-av"><i class="fas fa-robot"></i></div>
      <div class="cc-bubble">
        Hi! Ask me anything about <strong>contraceptives & family planning</strong>.<br>
        <span style="font-size:12px;opacity:.75;">English or Filipino — walang judgment!</span>
      </div>
    </div>
  </div>

  <div class="cc-widget-chips" id="ccChips">
    <button class="cc-chip" onclick="ccChip('Most effective method?')">Most effective?</button>
    <button class="cc-chip" onclick="ccChip('Ano ang side effects ng pills?')">Side effects ng pills</button>
    <button class="cc-chip" onclick="ccChip('How does IUD work?')">How does IUD work?</button>
    <button class="cc-chip" onclick="ccChip('Hormone-free options')">Hormone-free</button>
    <button class="cc-chip" onclick="ccChip('Emergency contraception after unprotected sex')">Emergency</button>
    <button class="cc-chip" onclick="ccChip('Magkano ang contraceptive?')">Magkano?</button>
  </div>

  <div class="cc-widget-input-row">
    <textarea id="ccInp" placeholder="Magtanong..." rows="1"></textarea>
    <button class="cc-send" id="ccSnd"><i class="fas fa-paper-plane"></i></button>
  </div>
</div>

<style>
/* ── FAB button */
.cc-fab {
  position: fixed;
  bottom: 28px;
  right: 28px;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: #185FA5;
  color: #fff;
  border: none;
  font-size: 22px;
  cursor: pointer;
  box-shadow: 0 4px 20px rgba(24,95,165,0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9998;
  transition: background 0.18s, transform 0.15s, box-shadow 0.18s;
}
.cc-fab:hover { background: #0C447C; transform: scale(1.08); box-shadow: 0 6px 24px rgba(24,95,165,0.55); }

.cc-fab-badge {
  position: absolute;
  top: -2px; right: -2px;
  width: 18px; height: 18px;
  background: #e53935;
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid #fff;
  font-family: 'Outfit', sans-serif;
}
.cc-fab-badge.hidden { display: none; }

/* ── Widget panel */
.cc-widget {
  position: fixed;
  bottom: 94px;
  right: 28px;
  width: 370px;
  max-height: 540px;
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 8px 40px rgba(0,0,0,0.18);
  display: none;
  flex-direction: column;
  overflow: hidden;
  z-index: 9997;
  border: 0.5px solid rgba(0,0,0,0.09);
  animation: ccSlideUp 0.22s cubic-bezier(0.34,1.1,0.64,1) both;
  font-family: 'Outfit', sans-serif;
}
.cc-widget.open { display: flex; }
@keyframes ccSlideUp {
  from { opacity: 0; transform: translateY(18px) scale(0.97); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* header */
.cc-widget-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 18px;
  background: #185FA5;
  color: #fff;
  flex-shrink: 0;
}
.cc-widget-header-left { display: flex; align-items: center; gap: 10px; }
.cc-widget-avatar {
  width: 36px; height: 36px; border-radius: 50%;
  background: rgba(255,255,255,0.2);
  display: flex; align-items: center; justify-content: center;
  font-size: 16px;
}
.cc-widget-title { font-size: 14px; font-weight: 600; line-height: 1.2; }
.cc-widget-sub   { font-size: 11px; opacity: 0.8; margin-top: 1px; }
.cc-online-dot   { display: inline-block; width: 6px; height: 6px; background: #6cf; border-radius: 50%; margin-right: 4px; }
.cc-widget-close {
  width: 28px; height: 28px; border-radius: 8px;
  background: rgba(255,255,255,0.15); border: none;
  color: #fff; cursor: pointer; font-size: 14px;
  display: flex; align-items: center; justify-content: center;
  transition: background 0.15s;
}
.cc-widget-close:hover { background: rgba(255,255,255,0.3); }

/* messages */
.cc-widget-msgs {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  background: #f7f6f0;
  display: flex;
  flex-direction: column;
  gap: 10px;
  min-height: 0;
}
.cc-widget-msgs::-webkit-scrollbar { width: 3px; }
.cc-widget-msgs::-webkit-scrollbar-thumb { background: #ddd; border-radius: 3px; }

.cc-msg { display: flex; gap: 7px; max-width: 92%; animation: ccPop .18s ease; }
.cc-msg.cc-user { align-self: flex-end; flex-direction: row-reverse; }
.cc-msg.cc-bot  { align-self: flex-start; }
@keyframes ccPop { from { opacity:0; transform: translateY(4px); } to { opacity:1; transform:none; } }

.cc-av {
  width: 26px; height: 26px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; flex-shrink: 0; margin-top: 3px;
  border: 0.5px solid #e0e0e0;
}
.cc-msg.cc-user .cc-av { background: #185FA5; color: #fff; border-color: #185FA5; }
.cc-msg.cc-bot  .cc-av { background: #fff; color: #185FA5; }

.cc-bubble {
  padding: 9px 13px;
  border-radius: 14px;
  font-size: 13px;
  line-height: 1.6;
  color: #2c2b28;
}
.cc-msg.cc-user .cc-bubble { background: #dceaf5; border-bottom-right-radius: 4px; }
.cc-msg.cc-bot  .cc-bubble { background: #fff; border: 0.5px solid #e8e4dc; border-bottom-left-radius: 4px; }
.cc-bubble strong { color: #0C447C; }

/* formatted lines */
.cc-r-line   { margin: 1px 0; }
.cc-r-bullet { margin: 2px 0 2px 10px; }
.cc-r-arrow  { margin: 2px 0 2px 14px; color: #185FA5; font-size: 12px; }
.cc-r-space  { height: 4px; }
.cc-r-trow   { display: flex; gap: 8px; font-size: 12px; border-bottom: 0.5px solid #eee; padding: 3px 0; }

/* typing */
.cc-typing-wrap { display: flex; gap: 7px; align-self: flex-start; animation: ccPop .18s ease; }
.cc-typing-dots {
  display: flex; gap: 4px; align-items: center;
  padding: 9px 13px;
  background: #fff; border: 0.5px solid #e8e4dc;
  border-radius: 14px; border-bottom-left-radius: 4px;
}
.cc-typing-dots span {
  width: 5px; height: 5px; border-radius: 50%;
  background: #aaa; animation: ccBlink 1.4s infinite;
}
.cc-typing-dots span:nth-child(2) { animation-delay: .2s; }
.cc-typing-dots span:nth-child(3) { animation-delay: .4s; }
@keyframes ccBlink {
  0%,60%,100% { opacity:.25; transform: translateY(0); }
  30% { opacity:1; transform: translateY(-3px); }
}

/* chips */
.cc-widget-chips {
  display: flex; flex-wrap: wrap; gap: 6px;
  padding: 8px 14px;
  border-top: 0.5px solid #e8e4dc;
  background: #fff; flex-shrink: 0;
}
.cc-chip {
  background: #f4f3ef;
  border: 0.5px solid #e8e4dc;
  border-radius: 30px;
  padding: 4px 11px;
  font-size: 11.5px;
  cursor: pointer;
  color: #6b6b67;
  font-family: 'Outfit', sans-serif;
  transition: background .13s, color .13s;
  white-space: nowrap;
}
.cc-chip:hover { background: #e8f1fb; border-color: #b5d4f4; color: #0C447C; }

/* input row */
.cc-widget-input-row {
  display: flex; gap: 8px; align-items: flex-end;
  padding: 10px 14px 14px;
  border-top: 0.5px solid #e8e4dc;
  background: #fff; flex-shrink: 0;
}
.cc-widget-input-row textarea {
  flex: 1;
  border: 0.5px solid #e8e4dc;
  border-radius: 18px;
  padding: 8px 14px;
  font-family: 'Outfit', sans-serif;
  font-size: 13px;
  resize: none;
  height: 38px;
  max-height: 100px;
  line-height: 1.5;
  color: #2c2b28;
  background: #f7f6f0;
  transition: border-color .18s, background .18s;
}
.cc-widget-input-row textarea:focus { outline: none; border-color: #185FA5; background: #fff; }
.cc-widget-input-row textarea::placeholder { color: #aaa; }
.cc-send {
  width: 38px; height: 38px; border-radius: 50%;
  border: none; background: #185FA5;
  color: #fff; font-size: 13px; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; transition: background .18s;
}
.cc-send:hover { background: #0C447C; }
.cc-send:disabled { background: #ccc; cursor: default; }

/* mobile */
@media (max-width: 480px) {
  .cc-widget { width: calc(100vw - 24px); right: 12px; bottom: 80px; }
  .cc-fab { right: 16px; bottom: 16px; }
}
</style>

<script>
(function() {

/* ── Knowledge base ── */
const KB = [
  { kw:['hello','hi','hey','kumusta','musta','kamusta','magandang umaga','magandang hapon','magandang gabi'],
    r:`Hello!\n\nI'm ContraChoice AI. I can answer questions about contraceptives and family planning in **English or Filipino**.\n\nAsk me about: **pills, IUD, implant, injection, condom, emergency contraception, effectiveness, side effects, costs,** and more!` },
  { kw:['tangina','putangina','putang ina','bobo','tanga','ulol','lintik','hayop','fuck you','asshole','bitch','puta','pakyu','gago'],
    r:`Pakiusap, maging magalang. Nandito ako para tumulong sa iyong mga tanong tungkol sa contraceptives — walang judgment. Subukan ulit!` },
  { kw:['pill','pills','birth control pill','combined pill','mini pill','oral contraceptive','tableta','trust pill','diane','althea','daphne','nordette','micropil'],
    r:`**Birth Control Pills**\n\nMay dalawang uri:\n• **Combined pill** — may estrogen at progestin\n• **Mini-pill** — para sa nagpapasuso o hindi makainom ng estrogen\n\n**Paano gamitin:** Uminom ng isang tableta araw-araw sa parehong oras.\n\n**Bisa:** ~91% (typical), ~99% (perfect)\n\n**Side effects:** pagduduwal, pananakit ng dibdib, pagbabago ng mood, irregular na regla.\n\n**Gastos:** ₱300–₱600/buwan; libre sa RHU.` },
  { kw:['iud','intrauterine device','copper iud','hormonal iud','mirena','kyleena','paragard','tansong iud','lagay ng iud'],
    r:`**IUD (Intrauterine Device)**\n\nMaliit na T-shaped device na inilalagay ng doktor sa loob ng bahay-bata.\n\n**Dalawang uri:**\n• **Copper IUD** — hormone-free, hanggang 10 taon\n• **Hormonal IUD (Mirena)** — 3–8 taon, nagpapagaan ng regla\n\n**Bisa:** >99%\n\n**Reversible:** Oo — kapag inalis, maaaring mabuntis agad.\n\n**Gastos:** ₱5,000–₱12,000 (one-time)` },
  { kw:['implant','nexplanon','arm implant','implanon','subdermal'],
    r:`**Contraceptive Implant**\n\nMaliit na rod sa ilalim ng balat ng braso. Naglalabas ng progestin.\n\n**Tagal:** Hanggang 3 taon\n**Bisa:** >99%\n\n**Side effects:** Irregular na pagdurugo, sakit ng ulo.\n\n**Reversible:** Oo — bumabalik agad ang fertility kapag inalis.\n\n**Gastos:** ₱2,000–₱8,000 (one-time)` },
  { kw:['injection','depo','depo-provera','shot','turok','injectable','dmpa','3 months'],
    r:`**Contraceptive Injection (Depo-Provera)**\n\nTurok ng progestin bawat 3 buwan.\n\n**Bisa:** ~96% (typical), ~99% (perfect)\n\n**Side effects:**\n• Irregular na pagdurugo\n• Posibleng pagtaba\n• Maantala ang fertility pagkatapos itigil (3–18 buwan)\n\n**Gastos:** ₱500–₱800 bawat 3 buwan; libre sa RHU` },
  { kw:['condom','rubber','male condom','female condom','safe sex'],
    r:`**Condom**\n\n**Bisa:** ~87% (typical), ~98% (perfect)\n\n**Unique advantage:** Tanging paraan na nagbibigay ng proteksyon laban sa **STIs/STDs**.\n\n**Tips:**\n• Suriin ang expiry date\n• Bago condom bawat beses\n• Water-based lubricant lang\n\n**Gastos:** ₱20–₱100 bawat isa; walang reseta` },
  { kw:['emergency','morning after','plan b','ecp','after sex','hindi nag-condom','postech','postinor','levonorgestrel','72 hours'],
    r:`**Emergency Contraception**\n\n**Hindi ito abortion pill** — pumipigil sa fertilization.\n\n**Mga opsyon:**\n• **Levonorgestrel (Postinor/Postech)** — sa loob ng 72 oras (~89%)\n• **Ulipristal (ella)** — hanggang 120 oras (5 araw)\n• **Copper IUD** — >99% kung sa loob ng 5 araw\n\n**Side effects:** Pagduduwal, sakit ng ulo, irregular na regla.` },
  { kw:['natural','fertility awareness','calendar','rhythm','bbt','basal body temperature','billing','safe period','safe days'],
    r:`**Natural Family Planning (NFP)**\n\nNagsusubaybay sa natural na fertility signs.\n\n**Mga pamamaraan:**\n• Calendar/Rhythm method\n• Basal Body Temperature (BBT)\n• Cervical Mucus (Billing) method\n\n**Bisa:** 76–88% (typical), hanggang 99% (perfect)\n\n**Limitation:** Hindi angkop kung irregular ang regla. Walang STI protection.` },
  { kw:['most effective','pinaka epektibo','effectiveness','compare','which is better','alin ang mas maganda','success rate'],
    r:`**Effectiveness ng mga Methods**\n\n**>99%:**\n• Implant, Hormonal IUD, Copper IUD, Tubal Ligation\n\n**91–99%:**\n• Injection ~96%, Pills ~91–93%\n\n**80–90%:**\n• Condom ~87%, Diaphragm ~88%\n\n**Mas mababa:**\n• Withdrawal ~78%, Natural ~76–88%\n\nPara sa pinakamataas na proteksyon: **dalawang paraan** sabay (e.g. pills + condom).` },
  { kw:['side effect','masamang epekto','bleeding','spotting','pagtaba','weight gain','mood','nausea','pagduduwal','headache','sakit ng ulo'],
    r:`**Common Side Effects ng Contraceptives**\n\n**Hormonal (pills, injection, implant, hormonal IUD):**\n• Irregular na pagdurugo o spotting (common sa unang 3–6 buwan)\n• Pagduduwal (pills)\n• Pananakit ng dibdib\n• Sakit ng ulo\n• Pagbabago ng mood\n• Pagtaba (mas karaniwan sa injection)\n\n**Copper IUD:** Mas malakas/masakit na regla sa unang buwan\n\n**Condom/Natural:** Halos walang side effects` },
  { kw:['hormone free','non hormonal','walang hormones','no hormones','hormone-free'],
    r:`**Hormone-Free Options**\n\n• **Copper IUD** — >99%, hanggang 10 taon\n• **Male condom** — ~87%; nagpoprotekta rin sa STIs\n• **Female condom** — ~79%\n• **Diaphragm** — ~88%\n• **Fertility Awareness Methods** — 76–88%\n\nAng **Copper IUD** ang pinaka-epektibong hormone-free option.` },
  { kw:['cost','price','magkano','gastos','libre','free','rhu','botika ng bayan','presyo','mahal','mura','affordable'],
    r:`**Gastos ng mga Contraceptive**\n\n| Paraan | Gastos |\n| Pills | ₱300–₱600/buwan |\n| Injection | ₱500–₱800 / 3 buwan |\n| Condom | ₱20–₱100 bawat isa |\n| IUD | ₱5,000–₱12,000 (one-time) |\n| Implant | ₱2,000–₱8,000 (one-time) |\n| Emergency pill | ₱150–₱400 |\n\n**LIBRE sa:** RHU, Barangay Health Center, Botika ng Bayan, POPCOM` },
  { kw:['breastfeeding','nagpapasuso','postpartum','after birth','pagkatapos manganak','LAM'],
    r:`**Para sa Nagpapasusong Ina**\n\n**Ligtas:**\n• Mini-pill, Implant, Injection, Hormonal IUD, Copper IUD, Condom\n\n**Iwasan:** Combined pill — maaaring makabawas ng gatas sa unang 6 na linggo\n\n**LAM (Lactational Amenorrhea):** ~98% epektibo kung exclusively breastfeeding + wala pang regla + wala pang 6 na buwan ang baby.` },
  { kw:['smoker','smoking','naninigarilyo','yosi','cigarette'],
    r:`**Para sa mga Naninigarilyo**\n\n**Ligtas:** Mini-pill, Implant, Injection, IUD (copper o hormonal), Condom\n\n**Iwasan kung 35+ at naninigarilyo:** Combined pill — nagpapataas ng risk ng blood clots at stroke.` },
  { kw:['myth','totoo ba','misconception','fake news','sabi nila','is it true','pamahiin'],
    r:`**Common Myths tungkol sa Contraceptives**\n\n**"Ang IUD ay nagpapalaglag."**\n→ HINDI. Pumipigil sa fertilization — hindi abortion.\n\n**"Hindi mabubuntis sa first time."**\n→ MALI. Mabubuntis kahit first time.\n\n**"Ang pills ay nagpapataba."**\n→ Minimal effect lang. Injection ang mas may kaugnayan.\n\n**"Hindi mabubuntis kung nagpapasuso."**\n→ Hindi ganap na totoo. Pagkatapos ng 6 na buwan, kailangan na ng ibang paraan.\n\n**"Ang withdrawal ay epektibo."**\n→ 78% lang — hindi ipinapayo bilang tanging paraan.` },
  { kw:['permanent','tubal ligation','vasectomy','sterilization','BTL','ayaw nang magkaanak'],
    r:`**Permanent Methods**\n\n**Tubal Ligation (babae):** >99%; libre sa government hospitals\n\n**Vasectomy (lalaki):** >99%; mas simple at mas mura kaysa tubal ligation\n\nKailangan ng counseling bago ang procedure.` },
  { kw:['sti','std','hiv','aids','chlamydia','gonorrhea','syphilis','herpes','hpv','proteksyon sa sti'],
    r:`**STI/STD Protection**\n\nKaramihan sa contraceptives ay **hindi nagpoprotekta** sa STIs.\n\n**Nagpoprotekta sa STI:**\n• Male condom — pinaka-epektibo\n• Female condom\n\n**Hindi nagpoprotekta:** Pills, IUD, Implant, Injection, Natural methods\n\n→ Gumamit ng **condom + isa pang contraceptive** para sa pinakamataas na proteksyon.` },
  { kw:['how to choose','which method','recommend','paano pumili','alin ang maganda','advice','suggest','help me choose'],
    r:`**Paano Pumili ng Tamang Contraceptive?**\n\n→ **Long-term at halos 100% epektibo:** IUD o Implant\n→ **Ayaw ng hormones:** Copper IUD o Condom\n→ **Nagpapasuso:** Mini-pill, Implant, Injection, o IUD\n→ **Naninigarilyo (35+):** Progestin-only, IUD, o Condom\n→ **Kailangan ng STI protection:** Condom\n→ **Ayaw ng araw-araw na pills:** Injection o Implant/IUD\n→ **Limited budget:** Libre sa RHU — pills o condom\n\nKumonsulta sa doktor para sa personalized na rekomendasyon.` },
  { kw:['diabetes','hypertension','high blood','migraine','pcos','endometriosis','may sakit'],
    r:`**Contraceptive at Health Conditions**\n\n**Hypertension:** Iwasan ang combined pill → Ligtas: Progestin-only, IUD, condom\n**Migraine with aura:** Iwasan ang combined pill → Ligtas: Progestin-only, IUD\n**Diabetes:** Karamihan sa hormonal methods ay ligtas (controlled)\n**PCOS:** Combined pill — maaaring makatulong sa pag-regulate ng regla\n**Endometriosis:** Hormonal IUD o combined pill — maaaring makatulong sa pain\n\nKumonsulta sa iyong doktor.` }
];

const FB_EN  = `I'm designed to answer questions about contraceptives and family planning.\n\nAsk me about:\n• Pills, IUD, implant, injection, condom\n• Emergency contraception\n• Effectiveness, side effects, costs\n• How to choose the right method`;
const FB_FIL = `Sumasagot lang ako sa mga tanong tungkol sa contraceptives at family planning.\n\nPwede kang magtanong tungkol sa:\n• Pills, IUD, implant, injection, condom\n• Emergency contraception\n• Effectiveness, side effects, gastos\n• Paano pumili ng tamang paraan`;

function ccDetectLang(t) {
  const fil = ['po','ba','ko','mo','ako','siya','tayo','kayo','ang','ng','sa','mga','ito','paano','bakit','ano','saan','magkano','pwede','gusto','kailangan','hindi','salamat','talaga','naman','lang','din','kasi','kaya','pero','dahil','kapag','kung'];
  return fil.filter(w => t.toLowerCase().split(/[\s,!?.]+/).includes(w)).length >= 1 ? 'fil' : 'en';
}

function ccGetReply(msg) {
  const l = msg.toLowerCase();
  let best = null, score = 0;
  for (const item of KB) {
    let s = 0;
    for (const kw of item.kw) if (l.includes(kw)) s += kw.split(' ').length + 1;
    if (s > score) { score = s; best = item; }
  }
  if (score > 0) return best.r;
  return ccDetectLang(msg) === 'fil' ? FB_FIL : FB_EN;
}

function ccFmt(text) {
  const d = document.createElement('div');
  d.className = 'cc-bubble';
  let h = '';
  for (const line of text.split('\n')) {
    let l = line.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    if (!l.trim()) h += '<div class="cc-r-space"></div>';
    else if (l.startsWith('• ')) h += `<div class="cc-r-bullet">${l}</div>`;
    else if (l.startsWith('→ ')) h += `<div class="cc-r-arrow">${l}</div>`;
    else if (l.startsWith('| ') && !l.startsWith('|---')) {
      const cells = l.split('|').filter(c => c.trim());
      h += `<div class="cc-r-trow">`;
      cells.forEach((c, i) => { h += `<span style="flex:${i===0?'1.4':'1'};${i===0?'font-weight:500;':''}">${c.trim()}</span>`; });
      h += '</div>';
    } else h += `<div class="cc-r-line">${l}</div>`;
  }
  d.innerHTML = h;
  return d;
}

const ccMsgs = document.getElementById('ccMsgs');
const ccInp  = document.getElementById('ccInp');
const ccSnd  = document.getElementById('ccSnd');

function ccAddMsg(text, isUser) {
  const w = document.createElement('div');
  w.className = `cc-msg ${isUser ? 'cc-user' : 'cc-bot'}`;
  const av = document.createElement('div');
  av.className = 'cc-av';
  av.innerHTML = isUser ? '<i class="fas fa-user"></i>' : '<i class="fas fa-robot"></i>';
  let b;
  if (isUser) { b = document.createElement('div'); b.className = 'cc-bubble'; b.innerText = text; }
  else b = ccFmt(text);
  w.appendChild(av); w.appendChild(b);
  ccMsgs.appendChild(w);
  ccMsgs.scrollTop = ccMsgs.scrollHeight;
}

function ccShowTyping() {
  const w = document.createElement('div'); w.className = 'cc-typing-wrap'; w.id = 'ccTyp';
  const av = document.createElement('div'); av.className = 'cc-av'; av.innerHTML = '<i class="fas fa-robot"></i>';
  const d = document.createElement('div'); d.className = 'cc-typing-dots';
  d.innerHTML = '<span></span><span></span><span></span>';
  w.appendChild(av); w.appendChild(d);
  ccMsgs.appendChild(w); ccMsgs.scrollTop = ccMsgs.scrollHeight;
}
function ccRemoveTyping() { const e = document.getElementById('ccTyp'); if (e) e.remove(); }

function ccSend(msg) {
  const m = msg || ccInp.value.trim();
  if (!m) return;
  ccInp.disabled = ccSnd.disabled = true;
  ccAddMsg(m, true);
  if (!msg) { ccInp.value = ''; ccInp.style.height = '38px'; }
  ccShowTyping();
  setTimeout(() => {
    ccRemoveTyping();
    ccAddMsg(ccGetReply(m), false);
    ccInp.disabled = ccSnd.disabled = false;
    ccInp.focus();
  }, 650);
}

window.ccChip = function(t) {
  document.getElementById('ccChips').style.display = 'none';
  ccSend(t);
};

window.ccToggle = function() {
  const w = document.getElementById('ccWidget');
  const open = w.classList.toggle('open');
  const fab = document.getElementById('ccFab');
  fab.querySelector('.cc-fab-icon-open').style.display  = open ? 'none'  : '';
  fab.querySelector('.cc-fab-icon-close').style.display = open ? ''      : 'none';
  document.getElementById('ccBadge').classList.add('hidden');
  if (open) setTimeout(() => ccInp.focus(), 200);
};

ccSnd.addEventListener('click', () => ccSend());
ccInp.addEventListener('keypress', e => {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); ccSend(); }
});
ccInp.addEventListener('input', function() {
  this.style.height = 'auto';
  this.style.height = Math.min(this.scrollHeight, 100) + 'px';
});

})();
</script>