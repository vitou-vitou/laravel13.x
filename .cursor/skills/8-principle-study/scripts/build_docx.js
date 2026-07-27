/*
 * build_docx.js — builds a polished 8-principle study packet as a .docx file.
 *
 * USAGE
 *   npm install docx        (once, if not already installed)
 *   node build_docx.js content.json output.docx
 *
 * The script is data-driven: all packet content lives in a JSON file so the
 * skill only has to assemble the content, not write document code. Below is the
 * exact shape of that JSON. Every field is optional except `topic`; omit a
 * section and it is simply skipped. Arrays may be any length.
 *
 * {
 *   "topic": "Photosynthesis",                       // required — title
 *   "subtitle": "A Structured Study Packet",         // optional
 *   "preparedDate": "23 May 2026",                   // optional
 *   "howToUse": ["paragraph 1", "paragraph 2"],      // intro paragraphs
 *   "sourceNote": "One paragraph if the source...",  // optional caveat
 *
 *   "step1Intro": "One paragraph opening Step 1.",
 *   "principle1": {                                  // Map of the system
 *     "intro": "paragraph",
 *     "tables": [                                    // one or more tables
 *       { "title": "...", "headers": ["A","B"],
 *         "rows": [["a1","b1"],["a2","b2"]] }
 *     ],
 *     "takeaway": ["line 1", "line 2"]               // callout box lines
 *   },
 *   "principle2": {                                  // Clear explanations
 *     "intro": "paragraph",
 *     "explanations": [ { "q": "What is X?", "a": "answer paragraph" } ],
 *     "takeaway": ["line 1"]
 *   },
 *   "principle3": {                                  // Different media
 *     "intro": "paragraph",
 *     "oneLiner": "single sentence summary",
 *     "diagram": ["diagram line 1", "diagram line 2"],
 *     "analogy": "analogy paragraph",
 *     "comparisonTable": { "headers": [...], "rows": [[...]] },
 *     "takeaway": ["line 1"]
 *   },
 *   "principle4": {                                  // Short lessons
 *     "intro": "paragraph",
 *     "lessons": [ { "title": "Lesson 1 — ...", "body": "paragraph" } ],
 *     "takeaway": ["line 1"]
 *   },
 *
 *   "step2Intro": "One paragraph opening Step 2.",
 *   "principle5": {                                  // Test yourself
 *     "intro": "paragraph",
 *     "quizTitle": "Quiz A — Core concepts",
 *     "questions": ["q1", "q2"],
 *     "answers": ["1. ...", "2. ..."],
 *     "flashcards": [ { "front": "...", "back": "..." } ]
 *   },
 *   "principle6": {                                  // Spaced repetition
 *     "intro": "paragraph",
 *     "schedule": [ { "when": "Today", "task": "..." } ],
 *     "note": ["why-it-works line 1"]
 *   },
 *   "principle7": {                                  // Interleaving
 *     "intro": "paragraph",
 *     "quizTitle": "Mixed Quiz B — Interleaved",
 *     "questions": ["q1"],
 *     "answers": ["1. ..."]
 *   },
 *   "principle8": {                                  // Overlearning
 *     "intro": "paragraph",
 *     "stages": [ { "stage": "...", "feels": "...", "action": "..." } ],
 *     "planTitle": "Your overlearning plan",
 *     "plan": ["action 1", "action 2"],
 *     "takeaway": ["final line 1"]
 *   },
 *
 *   "glossary": [ { "term": "...", "meaning": "..." } ]
 * }
 *
 * NOTE on punctuation: write plain hyphens and straight quotes in the JSON.
 * Word will display them fine. The script does not transform text.
 */

const fs = require("fs");
const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
  Header, Footer, AlignmentType, LevelFormat, BorderStyle, WidthType,
  ShadingType, HeadingLevel, TableOfContents, PageNumber, PageBreak,
  TabStopType, TabStopPosition,
} = require("docx");

// ---- args ----
const contentPath = process.argv[2];
const outPath = process.argv[3] || "study_packet.docx";
if (!contentPath) {
  console.error("Usage: node build_docx.js <content.json> <output.docx>");
  process.exit(1);
}
const C = JSON.parse(fs.readFileSync(contentPath, "utf8"));
if (!C.topic) { console.error("content.json must include a 'topic' field"); process.exit(1); }

// ---- palette ----
const BLUE = "2E75B6";
const DARKBLUE = "1F4E79";
const LIGHT = "D9E2F3";
const GREY = "F2F2F2";
const CONTENT_W = 9360; // US Letter, 1in margins

// ---- helpers ----
const h1 = (t) => new Paragraph({ heading: HeadingLevel.HEADING_1, children: [new TextRun(t)] });
const h2 = (t) => new Paragraph({ heading: HeadingLevel.HEADING_2, children: [new TextRun(t)] });
const h3 = (t) => new Paragraph({ heading: HeadingLevel.HEADING_3, children: [new TextRun(t)] });
const p = (t) => new Paragraph({ spacing: { after: 120 }, children: [new TextRun(String(t))] });
const spacer = () => new Paragraph({ spacing: { after: 120 }, children: [] });
const pageBreak = () => new Paragraph({ children: [new PageBreak()] });

function bullet(t) {
  return new Paragraph({
    numbering: { reference: "bullets", level: 0 },
    spacing: { after: 60 }, children: [new TextRun(String(t))],
  });
}
function numbered(t) {
  return new Paragraph({
    numbering: { reference: "numbers", level: 0 },
    spacing: { after: 60 }, children: [new TextRun(String(t))],
  });
}
function divider() {
  return new Paragraph({
    border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: BLUE, space: 1 } },
    spacing: { after: 200 }, children: [],
  });
}
function callout(title, lines, fill = LIGHT) {
  const kids = [new Paragraph({ spacing: { after: 80 }, children: [new TextRun({ text: title, bold: true, color: BLUE })] })];
  (lines || []).forEach((l) => kids.push(new Paragraph({ spacing: { after: 60 }, children: [new TextRun(String(l))] })));
  const b = { style: BorderStyle.SINGLE, size: 1, color: BLUE };
  return new Table({
    width: { size: CONTENT_W, type: WidthType.DXA },
    columnWidths: [CONTENT_W],
    rows: [new TableRow({ children: [new TableCell({
      width: { size: CONTENT_W, type: WidthType.DXA },
      shading: { fill, type: ShadingType.CLEAR },
      margins: { top: 140, bottom: 140, left: 180, right: 180 },
      borders: { top: b, bottom: b, left: b, right: b },
      children: kids,
    })] })],
  });
}
const cb = { style: BorderStyle.SINGLE, size: 1, color: "BFBFBF" };
const cellBorders = { top: cb, bottom: cb, left: cb, right: cb };
function cell(text, width, opts = {}) {
  return new TableCell({
    width: { size: width, type: WidthType.DXA },
    borders: cellBorders,
    shading: opts.fill ? { fill: opts.fill, type: ShadingType.CLEAR } : undefined,
    margins: { top: 80, bottom: 80, left: 120, right: 120 },
    verticalAlign: "center",
    children: (Array.isArray(text) ? text : [text]).map(
      (t) => new Paragraph({ children: [new TextRun({ text: String(t), bold: !!opts.bold, color: opts.color })] })),
  });
}
function gridTable(headers, rows) {
  const n = headers.length;
  const widths = Array(n).fill(Math.floor(CONTENT_W / n));
  widths[n - 1] += CONTENT_W - widths.reduce((a, b) => a + b, 0); // fix rounding
  const headerRow = new TableRow({
    tableHeader: true,
    children: headers.map((hd, i) => cell(hd, widths[i], { bold: true, fill: BLUE, color: "FFFFFF" })),
  });
  const body = rows.map((r, ri) => new TableRow({
    children: r.map((c, i) => cell(c, widths[i], { fill: ri % 2 ? GREY : undefined })),
  }));
  return new Table({ width: { size: CONTENT_W, type: WidthType.DXA }, columnWidths: widths, rows: [headerRow, ...body] });
}
function flashcard(num, front, back) {
  const w1 = 1600, w2 = CONTENT_W - w1;
  return new Table({
    width: { size: CONTENT_W, type: WidthType.DXA },
    columnWidths: [w1, w2],
    rows: [
      new TableRow({ children: [
        cell(`Card ${num}`, w1, { bold: true, fill: BLUE, color: "FFFFFF" }),
        cell(front, w2, { bold: true, fill: LIGHT }),
      ] }),
      new TableRow({ children: [
        cell("Answer", w1, { bold: true, fill: GREY }),
        cell(back, w2),
      ] }),
    ],
  });
}

// ---- assemble ----
const kids = [];

// title page
kids.push(
  new Paragraph({ spacing: { before: 2400, after: 200 }, alignment: AlignmentType.CENTER,
    children: [new TextRun({ text: C.topic, bold: true, size: 60, color: BLUE })] }),
  new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 600 },
    children: [new TextRun({ text: C.subtitle || "A Structured Study Packet", size: 32, color: "595959" })] }),
  new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 120 },
    children: [new TextRun({ text: "Built with an 8-principle learning method", italics: true, size: 26 })] }),
  new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 120 },
    children: [new TextRun({ text: "Step 1 - Understanding   |   Step 2 - Automaticity", size: 24, color: "595959" })] }),
);
if (C.preparedDate) {
  kids.push(new Paragraph({ alignment: AlignmentType.CENTER, spacing: { before: 1200 },
    children: [new TextRun({ text: "Prepared " + C.preparedDate, size: 22, color: "808080" })] }));
}
kids.push(pageBreak());

// how to use
kids.push(h1("How to Use This Packet"));
(C.howToUse || []).forEach((para) => kids.push(p(para)));
if (C.sourceNote) kids.push(p(C.sourceNote));
kids.push(callout("The 8 learning principles used in this packet", [
  "Step 1 - Understanding: (1) Use a map of the system, (2) Get clear explanations, (3) Use different media, (4) Have short lessons.",
  "Step 2 - Automaticity: (5) Test yourself, (6) Wait to review, (7) Mix it up, (8) Don't stop.",
]));
kids.push(spacer());
kids.push(h2("Table of Contents"));
kids.push(new TableOfContents("Table of Contents", { hyperlink: true, headingStyleRange: "1-2" }));
kids.push(pageBreak());

// ---- STEP 1 ----
kids.push(h1("Step 1: Understanding"));
if (C.step1Intro) kids.push(p(C.step1Intro));
kids.push(divider());

if (C.principle1) {
  const x = C.principle1;
  kids.push(h2("Principle 1 - A Map of the System"));
  if (x.intro) kids.push(p(x.intro));
  (x.tables || []).forEach((t) => {
    if (t.title) kids.push(h3(t.title));
    kids.push(gridTable(t.headers, t.rows));
    kids.push(spacer());
  });
  if (x.takeaway) kids.push(callout("Map takeaway", x.takeaway));
  kids.push(pageBreak());
}

if (C.principle2) {
  const x = C.principle2;
  kids.push(h2("Principle 2 - Clear, Simple Explanations"));
  if (x.intro) kids.push(p(x.intro));
  (x.explanations || []).forEach((e) => { kids.push(h3(e.q)); kids.push(p(e.a)); });
  if (x.takeaway) kids.push(callout("Explanation takeaway", x.takeaway));
  kids.push(pageBreak());
}

if (C.principle3) {
  const x = C.principle3;
  kids.push(h2("Principle 3 - Use Different Media"));
  if (x.intro) kids.push(p(x.intro));
  if (x.oneLiner) { kids.push(h3("As a one-line summary")); kids.push(callout("In one sentence", [x.oneLiner])); }
  if (x.diagram) { kids.push(h3("As a diagram")); kids.push(callout("Diagram", x.diagram, GREY)); }
  if (x.analogy) { kids.push(h3("As an analogy")); kids.push(p(x.analogy)); }
  if (x.comparisonTable) { kids.push(h3("As a comparison table")); kids.push(gridTable(x.comparisonTable.headers, x.comparisonTable.rows)); kids.push(spacer()); }
  if (x.takeaway) kids.push(callout("Media takeaway", x.takeaway));
  kids.push(pageBreak());
}

if (C.principle4) {
  const x = C.principle4;
  kids.push(h2("Principle 4 - Short Lessons"));
  if (x.intro) kids.push(p(x.intro));
  (x.lessons || []).forEach((l) => { kids.push(h3(l.title)); kids.push(p(l.body)); });
  if (x.takeaway) kids.push(callout("Short-lessons takeaway", x.takeaway));
  kids.push(pageBreak());
}

// ---- STEP 2 ----
kids.push(h1("Step 2: Automaticity"));
if (C.step2Intro) kids.push(p(C.step2Intro));
kids.push(divider());

if (C.principle5) {
  const x = C.principle5;
  kids.push(h2("Principle 5 - Test Yourself (Retrieval Practice)"));
  if (x.intro) kids.push(p(x.intro));
  if (x.questions) {
    kids.push(h3(x.quizTitle || "Quiz - Core concepts"));
    x.questions.forEach((q) => kids.push(numbered(q)));
    kids.push(spacer());
  }
  if (x.answers) {
    kids.push(h3((x.quizTitle || "Quiz") + " - Answer key"));
    kids.push(callout("Answers", x.answers, GREY));
  }
  if (x.flashcards && x.flashcards.length) {
    kids.push(pageBreak());
    kids.push(h2("Flashcards"));
    kids.push(p("Read the prompt, say your answer aloud, then reveal the answer row."));
    x.flashcards.forEach((fc, i) => { kids.push(flashcard(i + 1, fc.front, fc.back)); kids.push(spacer()); });
  }
  kids.push(pageBreak());
}

if (C.principle6) {
  const x = C.principle6;
  kids.push(h2("Principle 6 - Wait to Review (Spaced Repetition)"));
  if (x.intro) kids.push(p(x.intro));
  if (x.schedule) {
    kids.push(gridTable(["When", "What to do", "Done"],
      x.schedule.map((s) => [s.when, s.task, "[  ]"])));
    kids.push(spacer());
  }
  if (x.note) kids.push(callout("Why spacing works", x.note));
  kids.push(pageBreak());
}

if (C.principle7) {
  const x = C.principle7;
  kids.push(h2("Principle 7 - Mix It Up (Interleaving)"));
  if (x.intro) kids.push(p(x.intro));
  if (x.questions) {
    kids.push(h3(x.quizTitle || "Mixed Quiz - Interleaved"));
    x.questions.forEach((q) => kids.push(numbered(q)));
    kids.push(spacer());
  }
  if (x.answers) {
    kids.push(h3((x.quizTitle || "Mixed Quiz") + " - Answer key"));
    kids.push(callout("Answers", x.answers, GREY));
  }
  kids.push(pageBreak());
}

if (C.principle8) {
  const x = C.principle8;
  kids.push(h2("Principle 8 - Don't Stop (Overlearning)"));
  if (x.intro) kids.push(p(x.intro));
  if (x.stages) {
    kids.push(gridTable(["Stage", "How it feels", "What to do"],
      x.stages.map((s) => [s.stage, s.feels, s.action])));
    kids.push(spacer());
  }
  if (x.plan) {
    kids.push(h3(x.planTitle || "Your overlearning plan"));
    x.plan.forEach((a) => kids.push(bullet(a)));
    kids.push(spacer());
  }
  if (x.takeaway) kids.push(callout("Final takeaway", x.takeaway));
}

if (C.glossary && C.glossary.length) {
  kids.push(pageBreak());
  kids.push(h1("Appendix - Quick-Reference Glossary"));
  kids.push(gridTable(["Term", "Plain-language meaning"], C.glossary.map((g) => [g.term, g.meaning])));
}

// ---- document ----
const doc = new Document({
  styles: {
    default: { document: { run: { font: "Arial", size: 22 } } },
    paragraphStyles: [
      { id: "Heading1", name: "Heading 1", basedOn: "Normal", next: "Normal", quickFormat: true,
        run: { size: 34, bold: true, font: "Arial", color: BLUE },
        paragraph: { spacing: { before: 240, after: 200 }, outlineLevel: 0 } },
      { id: "Heading2", name: "Heading 2", basedOn: "Normal", next: "Normal", quickFormat: true,
        run: { size: 28, bold: true, font: "Arial", color: DARKBLUE },
        paragraph: { spacing: { before: 220, after: 140 }, outlineLevel: 1 } },
      { id: "Heading3", name: "Heading 3", basedOn: "Normal", next: "Normal", quickFormat: true,
        run: { size: 24, bold: true, font: "Arial", color: "333333" },
        paragraph: { spacing: { before: 160, after: 100 }, outlineLevel: 2 } },
    ],
  },
  numbering: {
    config: [
      { reference: "bullets", levels: [{ level: 0, format: LevelFormat.BULLET, text: "•",
        alignment: AlignmentType.LEFT, style: { paragraph: { indent: { left: 720, hanging: 360 } } } }] },
      { reference: "numbers", levels: [{ level: 0, format: LevelFormat.DECIMAL, text: "%1.",
        alignment: AlignmentType.LEFT, style: { paragraph: { indent: { left: 720, hanging: 360 } } } }] },
    ],
  },
  features: { updateFields: true },
  sections: [{
    properties: { page: {
      size: { width: 12240, height: 15840 },
      margin: { top: 1440, right: 1440, bottom: 1440, left: 1440 },
    } },
    headers: { default: new Header({ children: [new Paragraph({
      alignment: AlignmentType.RIGHT,
      border: { bottom: { style: BorderStyle.SINGLE, size: 4, color: "BFBFBF", space: 2 } },
      children: [new TextRun({ text: C.topic + " - Study Packet", size: 16, color: "808080" })],
    })] }) },
    footers: { default: new Footer({ children: [new Paragraph({
      tabStops: [{ type: TabStopType.RIGHT, position: TabStopPosition.MAX }],
      children: [
        new TextRun({ text: "8-principle learning method", size: 16, color: "808080" }),
        new TextRun({ text: "\tPage ", size: 16, color: "808080" }),
        new TextRun({ children: [PageNumber.CURRENT], size: 16, color: "808080" }),
      ],
    })] }) },
    children: kids,
  }],
});

Packer.toBuffer(doc).then((buf) => {
  fs.writeFileSync(outPath, buf);
  console.log("Study packet written to " + outPath);
});
