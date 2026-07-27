/*
 * build_html_for_pdf.js - generates print-optimised HTML from content.json
 * Usage: node build_html_for_pdf.js content.json output.html
 */
const fs = require("fs");
const path = require("path");

const contentPath = process.argv[2];
const outPath = process.argv[3] || "study_packet.html";
if (!contentPath) {
  console.error("Usage: node build_html_for_pdf.js <content.json> <output.html>");
  process.exit(1);
}
const C = JSON.parse(fs.readFileSync(contentPath, "utf8"));

function esc(s) {
  return String(s)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function table(t) {
  const headers = t.headers.map(h => `<th>${esc(h)}</th>`).join("");
  const rows = t.rows.map(r => `<tr>${r.map(c => `<td>${esc(c)}</td>`).join("")}</tr>`).join("\n");
  return `<table><thead><tr>${headers}</tr></thead><tbody>${rows}</tbody></table>`;
}

function callout(lines) {
  return `<div class="callout">${lines.map(l => `<p>${esc(l)}</p>`).join("")}</div>`;
}

const sections = [];

// Title page
sections.push(`
<div class="title-page">
  <h1 class="main-title">${esc(C.topic)}</h1>
  ${C.subtitle ? `<p class="subtitle">${esc(C.subtitle)}</p>` : ""}
  <p class="method-line">Built with an 8-principle learning method</p>
  ${C.preparedDate ? `<p class="date">${esc(C.preparedDate)}</p>` : ""}
</div>
<div class="page-break"></div>
`);

// How to use
if (C.howToUse) {
  sections.push(`<h2>How to use this packet</h2>`);
  C.howToUse.forEach(p => sections.push(`<p>${esc(p)}</p>`));
}

// 8 principles list
sections.push(`
<div class="callout principles-list">
  <strong>The 8 Principles</strong>
  <ol>
    <li>Map of the system</li>
    <li>Clear, simple explanations</li>
    <li>Use different media</li>
    <li>Short lessons</li>
    <li>Test yourself</li>
    <li>Wait to review (spaced repetition)</li>
    <li>Mix it up (interleaving)</li>
    <li>Don't stop (overlearning)</li>
  </ol>
</div>
<div class="page-break"></div>
`);

// Step 1
sections.push(`<h1>Step 1 - Understanding</h1>`);
if (C.step1Intro) sections.push(`<p>${esc(C.step1Intro)}</p>`);

// Principle 1
if (C.principle1) {
  const p1 = C.principle1;
  sections.push(`<h2>Principle 1 - A Map of the System</h2>`);
  if (p1.intro) sections.push(`<p>${esc(p1.intro)}</p>`);
  if (p1.tables) p1.tables.forEach(t => {
    sections.push(`<h3>${esc(t.title)}</h3>`);
    sections.push(table(t));
  });
  if (p1.takeaway) sections.push(callout(p1.takeaway));
}

// Principle 2
if (C.principle2) {
  const p2 = C.principle2;
  sections.push(`<h2>Principle 2 - Clear, Simple Explanations</h2>`);
  if (p2.intro) sections.push(`<p>${esc(p2.intro)}</p>`);
  if (p2.explanations) p2.explanations.forEach(e => {
    sections.push(`<h3>${esc(e.q)}</h3>`);
    sections.push(`<p>${esc(e.a)}</p>`);
  });
  if (p2.takeaway) sections.push(callout(p2.takeaway));
}

// Principle 3
if (C.principle3) {
  const p3 = C.principle3;
  sections.push(`<h2>Principle 3 - Use Different Media</h2>`);
  if (p3.intro) sections.push(`<p>${esc(p3.intro)}</p>`);
  if (p3.oneLiner) sections.push(`<div class="callout"><strong>One-liner:</strong> ${esc(p3.oneLiner)}</div>`);
  if (p3.diagram) sections.push(`<h3>Diagram</h3><pre class="diagram">${p3.diagram.map(esc).join("\n")}</pre>`);
  if (p3.analogy) sections.push(`<h3>Analogy</h3><p>${esc(p3.analogy)}</p>`);
  if (p3.comparisonTable) {
    sections.push(`<h3>Comparison Table</h3>`);
    sections.push(table(p3.comparisonTable));
  }
  if (p3.takeaway) sections.push(callout(p3.takeaway));
}

// Principle 4
if (C.principle4) {
  const p4 = C.principle4;
  sections.push(`<h2>Principle 4 - Short Lessons</h2>`);
  if (p4.intro) sections.push(`<p>${esc(p4.intro)}</p>`);
  if (p4.lessons) p4.lessons.forEach(l => {
    sections.push(`<h3>${esc(l.title)}</h3>`);
    sections.push(`<p>${esc(l.body)}</p>`);
  });
  if (p4.takeaway) sections.push(callout(p4.takeaway));
}

sections.push(`<div class="page-break"></div>`);

// Step 2
sections.push(`<h1>Step 2 - Automaticity</h1>`);
if (C.step2Intro) sections.push(`<p>${esc(C.step2Intro)}</p>`);

// Principle 5
if (C.principle5) {
  const p5 = C.principle5;
  sections.push(`<h2>Principle 5 - Test Yourself</h2>`);
  if (p5.intro) sections.push(`<p>${esc(p5.intro)}</p>`);
  if (p5.quizTitle) sections.push(`<h3>${esc(p5.quizTitle)}</h3>`);
  if (p5.questions) {
    sections.push(`<ol class="quiz">${p5.questions.map(q => `<li>${esc(q.replace(/^\d+\.\s*/,""))}</li>`).join("")}</ol>`);
  }
  if (p5.answers) {
    sections.push(`<div class="callout answer-key"><strong>Answer Key</strong><ol>${p5.answers.map(a => `<li>${esc(a.replace(/^\d+\.\s*/,""))}</li>`).join("")}</ol></div>`);
  }
  if (p5.flashcards) {
    sections.push(`<h3>Flashcards</h3>`);
    sections.push(`<table class="flashcards"><thead><tr><th>Front (Prompt)</th><th>Back (Answer)</th></tr></thead><tbody>`);
    sections.push(p5.flashcards.map(f => `<tr><td><strong>${esc(f.front)}</strong></td><td>${esc(f.back)}</td></tr>`).join(""));
    sections.push(`</tbody></table>`);
  }
}

// Principle 6
if (C.principle6) {
  const p6 = C.principle6;
  sections.push(`<h2>Principle 6 - Wait to Review (Spaced Repetition)</h2>`);
  if (p6.intro) sections.push(`<p>${esc(p6.intro)}</p>`);
  if (p6.schedule) {
    sections.push(`<table><thead><tr><th>When</th><th>What to do</th></tr></thead><tbody>`);
    sections.push(p6.schedule.map(s => `<tr><td>${esc(s.when)}</td><td>${esc(s.task)}</td></tr>`).join(""));
    sections.push(`</tbody></table>`);
  }
  if (p6.note) sections.push(callout(p6.note));
}

// Principle 7
if (C.principle7) {
  const p7 = C.principle7;
  sections.push(`<h2>Principle 7 - Mix It Up (Interleaving)</h2>`);
  if (p7.intro) sections.push(`<p>${esc(p7.intro)}</p>`);
  if (p7.quizTitle) sections.push(`<h3>${esc(p7.quizTitle)}</h3>`);
  if (p7.questions) {
    sections.push(`<ol class="quiz">${p7.questions.map(q => `<li>${esc(q.replace(/^\d+\.\s*/,""))}</li>`).join("")}</ol>`);
  }
  if (p7.answers) {
    sections.push(`<div class="callout answer-key"><strong>Answer Key</strong><ol>${p7.answers.map(a => `<li>${esc(a.replace(/^\d+\.\s*/,""))}</li>`).join("")}</ol></div>`);
  }
}

// Principle 8
if (C.principle8) {
  const p8 = C.principle8;
  sections.push(`<h2>Principle 8 - Don't Stop (Overlearning)</h2>`);
  if (p8.intro) sections.push(`<p>${esc(p8.intro)}</p>`);
  if (p8.stages) {
    sections.push(`<table><thead><tr><th>Stage</th><th>Feels like</th><th>What to do</th></tr></thead><tbody>`);
    sections.push(p8.stages.map(s => `<tr><td>${esc(s.stage)}</td><td>${esc(s.feels)}</td><td>${esc(s.action)}</td></tr>`).join(""));
    sections.push(`</tbody></table>`);
  }
  if (p8.planTitle) sections.push(`<h3>${esc(p8.planTitle)}</h3>`);
  if (p8.plan) sections.push(`<ul>${p8.plan.map(item => `<li>${esc(item)}</li>`).join("")}</ul>`);
  if (p8.takeaway) sections.push(callout(p8.takeaway));
}

sections.push(`<div class="page-break"></div>`);

// Glossary
if (C.glossary) {
  sections.push(`<h2>Appendix - Glossary</h2>`);
  sections.push(`<table><thead><tr><th>Term</th><th>Meaning</th></tr></thead><tbody>`);
  sections.push(C.glossary.map(g => `<tr><td><strong>${esc(g.term)}</strong></td><td>${esc(g.meaning)}</td></tr>`).join(""));
  sections.push(`</tbody></table>`);
}

const html = `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>${esc(C.topic)} - Study Packet</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Georgia, serif; font-size: 11pt; line-height: 1.6; color: #1a1a1a; max-width: 750px; margin: 0 auto; padding: 24px; }
  h1 { font-size: 18pt; color: #1F4E79; margin: 24px 0 12px; border-bottom: 2px solid #2E75B6; padding-bottom: 4px; }
  h2 { font-size: 14pt; color: #2E75B6; margin: 20px 0 8px; }
  h3 { font-size: 12pt; color: #1F4E79; margin: 16px 0 6px; }
  p { margin: 8px 0; }
  ul, ol { margin: 8px 0 8px 24px; }
  li { margin: 4px 0; }
  table { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 10pt; }
  th { background: #2E75B6; color: white; padding: 6px 8px; text-align: left; }
  td { padding: 5px 8px; border: 1px solid #ccc; vertical-align: top; }
  tr:nth-child(even) td { background: #f5f8fd; }
  .callout { background: #D9E2F3; border-left: 4px solid #2E75B6; padding: 10px 14px; margin: 12px 0; border-radius: 2px; }
  .callout p { margin: 4px 0; }
  .answer-key { background: #e8f5e9; border-left: 4px solid #388e3c; }
  .answer-key ol { margin: 6px 0 0 20px; }
  .answer-key li { margin: 6px 0; font-size: 10pt; }
  .quiz { margin: 8px 0 8px 20px; }
  .quiz li { margin: 8px 0; }
  pre.diagram { background: #f4f4f4; border: 1px solid #ddd; padding: 12px; font-family: Consolas, monospace; font-size: 10pt; margin: 10px 0; white-space: pre; }
  .flashcards td:first-child { width: 40%; font-size: 10pt; }
  .flashcards td:last-child { font-size: 10pt; }
  .title-page { text-align: center; padding: 80px 0 60px; }
  .main-title { font-size: 26pt; color: #1F4E79; border: none; margin-bottom: 16px; }
  .subtitle { font-size: 14pt; color: #555; margin: 8px 0; }
  .method-line { font-size: 11pt; color: #2E75B6; font-style: italic; margin: 12px 0; }
  .date { font-size: 10pt; color: #888; margin-top: 24px; }
  .page-break { page-break-after: always; }
  .principles-list { font-size: 11pt; }
  .principles-list ol { margin: 8px 0 0 20px; }
  @media print {
    body { padding: 0; max-width: 100%; }
    .page-break { page-break-after: always; }
    h1 { page-break-before: auto; }
  }
</style>
</head>
<body>
${sections.join("\n")}
</body>
</html>`;

fs.writeFileSync(outPath, html, "utf8");
console.log("HTML written to " + outPath);
