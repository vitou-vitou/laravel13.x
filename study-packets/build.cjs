#!/usr/bin/env node
/*
 * build.js — 8-Principle study-packet HTML generator.
 *
 * Usage:  node build.js <content.json> <output.html>
 *
 * Emits a single self-contained .html (inline CSS+JS, no CDN, no storage APIs)
 * styled to match the pilot (multi-tenant-saas-laravel.html). Dark theme,
 * sticky jump nav, flip flashcards, click-reveal quizzes.
 *
 * CONTENT JSON SHAPE
 * {
 *   "title": "Building X in Laravel",
 *   "subtitle": "one-line description",
 *   "badges": ["Level: Professional", "Laravel 11/12 · PHP 8.2+", ...],
 *   "estimate": "~90 min first pass",
 *   "sections": [
 *     { "id":"p1", "pill":"Principle 1 — Map of the System", "step":false,
 *       "h2":"...", "blocks":[ <block>, ... ] },
 *     ...8 sections... ,
 *     // quizzes live as blocks of type "quiz" inside P5 / P7 sections
 *   ],
 *   "flashcards": [ ["front","back"], ... ],
 *   "glossary":   [ ["term","definition"], ... ]
 * }
 *
 * BLOCK TYPES
 *   {"p":"text"}                          paragraph (inline HTML allowed)
 *   {"h3":"text"} / {"h4":"text"}         sub-headings
 *   {"ul":["item", ...]} / {"ol":[...]}   lists
 *   {"code":"...", "lang":"php"}          code block (lang optional, cosmetic)
 *   {"callout":"text", "title":"...", "kind":"info|warn|danger"}
 *   {"diagram":"ascii art"}               monospace diagram box
 *   {"table":{"head":[...],"rows":[[...],...]}}
 *   {"quiz":{"sec":"p5","items":[{"q":"...","a":"..."}, ...]}}  reveal quiz w/ show-all toolbar
 *
 * Inline HTML (e.g. <code>, <strong>, <em>, <a>) is passed through as-is.
 */

const fs = require('fs');

function esc(s){ return String(s); } // content is trusted (authored by us); allow inline HTML

function renderBlock(b){
  if (b.p)        return `<p>${b.p}</p>`;
  if (b.h3)       return `<h3>${b.h3}</h3>`;
  if (b.h4)       return `<h4>${b.h4}</h4>`;
  if (b.ul)       return `<ul>${b.ul.map(i=>`<li>${i}</li>`).join('')}</ul>`;
  if (b.ol)       return `<ol>${b.ol.map(i=>`<li>${i}</li>`).join('')}</ol>`;
  if (b.code)     return `<pre><code>${b.code.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')}</code></pre>`;
  if (b.diagram)  return `<div class="diagram"><pre>${b.diagram.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')}</pre></div>`;
  if (b.callout){
    const kind = b.kind && b.kind!=='info' ? ' '+b.kind : '';
    const t = b.title ? `<div class="t">${b.title}</div>` : '';
    return `<div class="callout${kind}">${t}${b.callout}</div>`;
  }
  if (b.table){
    const head = `<thead><tr>${b.table.head.map(h=>`<th>${h}</th>`).join('')}</tr></thead>`;
    const rows = `<tbody>${b.table.rows.map(r=>`<tr>${r.map(c=>`<td>${c}</td>`).join('')}</tr>`).join('')}</tbody>`;
    const cls = b.class ? ` class="${b.class}"` : '';
    return `<table${cls}>${head}${rows}</table>`;
  }
  if (b.quiz){
    const sec = b.quiz.sec;
    const bar = `<div class="toolbar"><button onclick="revealAll('${sec}',true)">Show all answers</button><button class="ghost" onclick="revealAll('${sec}',false)">Hide all</button></div>`;
    const qs = b.quiz.items.map(it =>
      `<div class="q" data-sec="${sec}"><div class="qn">${it.q}</div>`+
      `<button class="reveal-btn" onclick="tog(this)">Reveal answer</button>`+
      `<div class="answer">${it.a}</div></div>`
    ).join('');
    return bar + qs;
  }
  return '';
}

function renderSection(s){
  const pillClass = s.step ? 'pill step' : 'pill';
  const blocks = (s.blocks||[]).map(renderBlock).join('\n  ');
  return `<section id="${s.id}">
  <span class="${pillClass}">${s.pill}</span>
  <h2>${s.h2}</h2>
  ${blocks}
</section>`;
}

function build(data){
  const badges = (data.badges||[]).map(b=>`<span class="badge">${b}</span>`).join('\n    ');
  // jump nav: 8 principle links + flashcards + glossary
  const navItems = (data.sections||[]).map((s,i)=>`<a href="#${s.id}">${i+1}</a>`);
  const nav = (data.sections||[]).map(s=>{
    const short = s.pill.replace(/Principle \d+ — /,'').split(/[ /]/)[0];
    return `<a href="#${s.id}">${short}</a>`;
  }).join('\n    ') + `\n    <a href="#cards">Flashcards</a>\n    <a href="#glossary">Glossary</a>`;

  // insert step dividers before p1 and p5
  let sectionsHtml = '';
  (data.sections||[]).forEach(s=>{
    if (s.id==='p1') sectionsHtml += `<div class="step-divider"><span class="lbl">Step 1 — Understanding</span><span class="bar"></span></div>\n`;
    if (s.id==='p5') sectionsHtml += `<div class="step-divider"><span class="lbl">Step 2 — Automaticity</span><span class="bar"></span></div>\n`;
    sectionsHtml += renderSection(s) + '\n\n';
  });

  const flashcards = JSON.stringify(data.flashcards||[]);
  const glossaryRows = (data.glossary||[]).map(g=>`<tr><td><strong>${g[0]}</strong></td><td>${g[1]}</td></tr>`).join('\n      ');

  return `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Study Packet — ${data.title}</title>
<style>
  :root{--bg:#0f1117;--panel:#171a23;--panel2:#1d212c;--ink:#e6e9ef;--muted:#9aa3b2;--accent:#7c9cff;--accent2:#5be0b3;--warn:#ffcc66;--danger:#ff7b72;--line:#2a2f3c;--code:#0b0d13;--radius:14px}
  *{box-sizing:border-box}html{scroll-behavior:smooth}
  body{margin:0;background:var(--bg);color:var(--ink);font:16px/1.65 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased}
  .wrap{max-width:920px;margin:0 auto;padding:0 20px 120px}
  header.hero{padding:64px 20px 40px;text-align:center;background:radial-gradient(1200px 400px at 50% -120px,rgba(124,156,255,.18),transparent 70%);border-bottom:1px solid var(--line)}
  header.hero h1{font-size:34px;line-height:1.2;margin:0 0 10px;letter-spacing:-.5px}
  header.hero .sub{color:var(--muted);max-width:680px;margin:0 auto;font-size:16px}
  .badges{margin-top:18px;display:flex;gap:8px;justify-content:center;flex-wrap:wrap}
  .badge{background:var(--panel2);border:1px solid var(--line);color:var(--muted);padding:5px 12px;border-radius:999px;font-size:13px}
  nav.jump{position:sticky;top:0;z-index:20;background:rgba(15,17,23,.85);backdrop-filter:blur(10px);border-bottom:1px solid var(--line);padding:10px 20px}
  nav.jump .inner{max-width:920px;margin:0 auto;display:flex;gap:6px;flex-wrap:wrap;justify-content:center}
  nav.jump a{font-size:12.5px;color:var(--muted);text-decoration:none;padding:5px 10px;border-radius:8px;border:1px solid transparent}
  nav.jump a:hover{color:var(--ink);border-color:var(--line);background:var(--panel)}
  section{margin-top:46px}
  .pill{display:inline-block;font-size:12px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;color:var(--accent);background:rgba(124,156,255,.12);padding:4px 10px;border-radius:999px;margin-bottom:10px}
  .pill.step{color:var(--accent2);background:rgba(91,224,179,.12)}
  h2{font-size:25px;margin:6px 0 14px;letter-spacing:-.3px}
  h3{font-size:19px;margin:26px 0 8px;color:#fff}
  h4{font-size:16px;margin:18px 0 6px;color:var(--accent2)}
  p{margin:10px 0}ul,ol{margin:10px 0;padding-left:22px}li{margin:5px 0}
  .step-divider{display:flex;align-items:center;gap:14px;margin:60px 0 0}
  .step-divider .bar{flex:1;height:1px;background:var(--line)}
  .step-divider .lbl{color:var(--muted);font-size:13px;letter-spacing:1px;text-transform:uppercase}
  .card{background:var(--panel);border:1px solid var(--line);border-radius:var(--radius);padding:20px 22px;margin:16px 0}
  .callout{border-left:3px solid var(--accent);background:var(--panel2);padding:14px 18px;border-radius:8px;margin:16px 0}
  .callout.warn{border-color:var(--warn)}.callout.danger{border-color:var(--danger)}
  .callout .t{font-weight:700;margin-bottom:4px}
  code{font-family:"SF Mono",Menlo,Consolas,monospace;font-size:13.5px;background:var(--panel2);padding:2px 6px;border-radius:5px;color:#cdd6f4}
  pre{background:var(--code);border:1px solid var(--line);border-radius:10px;padding:16px;overflow:auto;margin:14px 0}
  pre code{background:none;padding:0;font-size:13px;line-height:1.55;display:block;color:#cdd6f4}
  table{width:100%;border-collapse:collapse;margin:16px 0;font-size:14.5px}
  th,td{border:1px solid var(--line);padding:10px 12px;text-align:left;vertical-align:top}
  th{background:var(--panel2);color:#fff;font-weight:600}
  tr:nth-child(even) td{background:rgba(255,255,255,.015)}
  .diagram{background:var(--code);border:1px solid var(--line);border-radius:10px;padding:18px;overflow:auto}
  .diagram pre{background:none;border:none;margin:0;padding:0;color:#9fb4ff;font-size:12.5px;line-height:1.45}
  .cards-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px;margin:16px 0}
  .flip{perspective:1200px;height:170px;cursor:pointer}
  .flip-in{position:relative;width:100%;height:100%;transition:transform .5s;transform-style:preserve-3d}
  .flip.flipped .flip-in{transform:rotateY(180deg)}
  .flip-face{position:absolute;inset:0;backface-visibility:hidden;border-radius:12px;border:1px solid var(--line);padding:16px;display:flex;align-items:center;justify-content:center;text-align:center}
  .flip-front{background:var(--panel2);font-weight:600}
  .flip-back{background:linear-gradient(160deg,#1c2738,#15202e);transform:rotateY(180deg);color:#d7e4ff;font-size:14px;line-height:1.5}
  .flip .hint{position:absolute;bottom:8px;right:12px;font-size:10.5px;color:var(--muted);font-weight:400}
  .q{border:1px solid var(--line);border-radius:10px;padding:14px 16px;margin:12px 0;background:var(--panel)}
  .q .qn{font-weight:600;margin-bottom:6px}
  .reveal-btn{background:var(--panel2);color:var(--accent);border:1px solid var(--line);padding:6px 12px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600}
  .reveal-btn:hover{background:#222838}
  .answer{display:none;margin-top:10px;border-left:3px solid var(--accent2);background:rgba(91,224,179,.07);padding:10px 14px;border-radius:6px;font-size:14.5px}
  .answer.show{display:block}
  .toolbar{display:flex;gap:10px;flex-wrap:wrap;margin:8px 0 4px}
  .toolbar button{background:var(--accent);color:#0b0d13;border:none;padding:8px 14px;border-radius:8px;cursor:pointer;font-weight:700;font-size:13px}
  .toolbar button.ghost{background:var(--panel2);color:var(--ink);border:1px solid var(--line)}
  .sched td:first-child{white-space:nowrap;font-weight:600;color:var(--accent2)}
  footer{margin-top:80px;padding-top:24px;border-top:1px solid var(--line);color:var(--muted);font-size:13px;text-align:center}
  .kbd{font-size:12px;color:var(--muted)}
  a.inlink{color:var(--accent)}
</style>
</head>
<body>
<header class="hero">
  <h1>${data.title}</h1>
  <p class="sub">${data.subtitle||''}</p>
  <div class="badges">
    ${badges}
  </div>
</header>
<nav class="jump"><div class="inner">
    ${nav}
</div></nav>
<div class="wrap">
${sectionsHtml}
<section id="cards">
  <span class="pill step">Active Recall — Flashcards</span>
  <h2>Click a card to flip</h2>
  <p class="kbd">Prompt on the front, answer on the back. Say your answer before flipping.</p>
  <div class="cards-grid" id="flashcards"></div>
</section>
<section id="glossary">
  <span class="pill">Reference — Glossary</span>
  <h2>Key terms</h2>
  <table><tbody>
      ${glossaryRows}
  </tbody></table>
</section>
<footer>
  <p>Study packet · 8-Principle Method · ${data.title} · Professional level</p>
  <p class="kbd">Self-contained. No external resources, no tracking, no browser storage — works offline on any device.</p>
</footer>
</div>
<script>
  function tog(btn){var a=btn.parentElement.querySelector('.answer');var on=a.classList.toggle('show');btn.textContent=on?'Hide answer':'Reveal answer';}
  function revealAll(sec,show){document.querySelectorAll('.q[data-sec="'+sec+'"]').forEach(function(q){var a=q.querySelector('.answer'),b=q.querySelector('.reveal-btn');if(show){a.classList.add('show');b.textContent='Hide answer';}else{a.classList.remove('show');b.textContent='Reveal answer';}});}
  var CARDS=${flashcards};
  (function(){var grid=document.getElementById('flashcards');CARDS.forEach(function(c){var el=document.createElement('div');el.className='flip';el.innerHTML='<div class="flip-in"><div class="flip-face flip-front">'+c[0]+'<span class="hint">click ↻</span></div><div class="flip-face flip-back">'+c[1]+'<span class="hint">click ↻</span></div></div>';el.addEventListener('click',function(){el.classList.toggle('flipped');});grid.appendChild(el);});})();
</script>
</body>
</html>`;
}

// --- main ---
const [,, inFile, outFile] = process.argv;
if (!inFile || !outFile){ console.error('Usage: node build.js <content.json> <output.html>'); process.exit(1); }
const data = JSON.parse(fs.readFileSync(inFile,'utf8'));
fs.writeFileSync(outFile, build(data));
console.log('Built', outFile, '('+(fs.statSync(outFile).size/1024).toFixed(1)+' KB)');
