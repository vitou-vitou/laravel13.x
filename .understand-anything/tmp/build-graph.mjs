import { readFileSync, writeFileSync } from 'node:fs';
import { execSync } from 'node:child_process';

const projectRoot = process.argv[2];
const scan = JSON.parse(
  readFileSync(`${projectRoot}/.understand-anything/intermediate/scan-result.json`, 'utf8')
);
const priority = JSON.parse(
  readFileSync(`${projectRoot}/.understand-anything/intermediate/priority-extract.json`, 'utf8')
);

const nodes = [];
const edges = [];
const nodeIds = new Set();

function addNode(node) {
  if (nodeIds.has(node.id)) return;
  nodeIds.add(node.id);
  nodes.push(node);
}

function prefixForCategory(cat, path) {
  if (cat === 'config') return `config:${path}`;
  if (cat === 'docs') return `document:${path}`;
  if (cat === 'infra') return `service:${path}`;
  if (cat === 'script') return `file:${path}`;
  return `file:${path}`;
}

for (const file of scan.files) {
  const id = prefixForCategory(file.fileCategory, file.path);
  const type =
    file.fileCategory === 'config'
      ? 'config'
      : file.fileCategory === 'docs'
        ? 'document'
        : file.fileCategory === 'infra'
          ? 'service'
          : 'file';
  addNode({
    id,
    type,
    name: file.path.split('/').pop(),
    filePath: file.path,
    summary: `${file.language} ${file.fileCategory} file (${file.sizeLines} lines)`,
    tags: [file.language, file.fileCategory],
    complexity: file.sizeLines > 200 ? 'moderate' : 'simple',
  });
}

for (const { extracted } of priority.results) {
  for (const result of extracted.results ?? []) {
    const fileId = `file:${result.path}`;
    addNode({
      id: fileId,
      type: 'file',
      name: result.path.split('/').pop(),
      filePath: result.path,
      summary: `PHP/JS module with ${result.functions?.length ?? 0} functions, ${result.classes?.length ?? 0} classes`,
      tags: [result.language ?? 'code', 'analyzed'],
      complexity: (result.nonEmptyLines ?? 0) > 150 ? 'moderate' : 'simple',
    });

    for (const fn of result.functions ?? []) {
      const fnId = `function:${result.path}:${fn.name}`;
      addNode({
        id: fnId,
        type: 'function',
        name: fn.name,
        filePath: result.path,
        summary: `Function ${fn.name} in ${result.path}`,
        tags: ['function'],
        complexity: 'simple',
      });
      edges.push({
        source: fileId,
        target: fnId,
        type: 'contains',
        weight: 1,
      });
    }

    for (const cls of result.classes ?? []) {
      const clsId = `class:${result.path}:${cls.name}`;
      addNode({
        id: clsId,
        type: 'class',
        name: cls.name,
        filePath: result.path,
        summary: `Class ${cls.name} with ${cls.methods?.length ?? 0} methods`,
        tags: ['class'],
        complexity: 'moderate',
      });
      edges.push({
        source: fileId,
        target: clsId,
        type: 'contains',
        weight: 1,
      });
    }
  }
}

for (const [from, imports] of Object.entries(scan.importMap ?? {})) {
  const source = prefixForCategory(
    scan.files.find((f) => f.path === from)?.fileCategory ?? 'code',
    from
  );
  for (const target of imports) {
    const targetCat = scan.files.find((f) => f.path === target)?.fileCategory ?? 'code';
    const targetId = prefixForCategory(targetCat, target);
    if (nodeIds.has(source) && nodeIds.has(targetId)) {
      edges.push({ source, target: targetId, type: 'imports', weight: 0.7 });
    }
  }
}

const layers = [
  {
    id: 'layer:tooling',
    name: 'Tooling',
    description: 'Herd-aware bin scripts and verification gates',
    nodeIds: nodes
      .filter((n) => n.filePath?.startsWith('bin/'))
      .map((n) => n.id),
  },
  {
    id: 'layer:docs-handoff',
    name: 'Documentation & Handoff',
    description: 'Session state, lessons, scaffold guides, study packets',
    nodeIds: nodes
      .filter((n) => n.filePath?.startsWith('docs/'))
      .map((n) => n.id),
  },
  {
    id: 'layer:root-api',
    name: 'Root Laravel API',
    description: 'Passport auth + Todo REST at repo root',
    nodeIds: nodes
      .filter(
        (n) =>
          n.filePath?.startsWith('app/') ||
          n.filePath?.startsWith('routes/') ||
          n.filePath?.startsWith('tests/')
      )
      .map((n) => n.id),
  },
  {
    id: 'layer:examples-mvp',
    name: 'MVP Examples',
    description: 'Runnable Spec-Kit example apps (login, commerce, booking, dashboards)',
    nodeIds: nodes
      .filter((n) =>
        /examples\/(kindly-login-1122|kindly-e-commerce-1122|booking-v1|clone-the-fb-nav|dashboard-v1|dashboard-v2|invoice-app)\//.test(
          n.filePath ?? ''
        )
      )
      .map((n) => n.id),
  },
];

const tour = [
  {
    order: 1,
    title: 'Session handoff',
    description: 'Start every agent session from locked decisions and MVP status.',
    nodeIds: ['document:docs/SESSION_STATE.md'].filter((id) => nodeIds.has(id)),
  },
  {
    order: 2,
    title: 'Example factory',
    description: 'Greenfield flow: new-example → Herd link → verify-example.',
    nodeIds: ['file:bin/new-example', 'file:bin/verify-example'].filter((id) =>
      nodeIds.has(id)
    ),
  },
  {
    order: 3,
    title: 'Root API playground',
    description: 'Passport-authenticated Todo API at monorepo root.',
    nodeIds: nodes
      .filter((n) => n.filePath === 'routes/api.php' || n.filePath === 'app/Http/Controllers/TodoController.php')
      .map((n) => n.id),
  },
  {
    order: 4,
    title: 'MVP examples',
    description: 'Independent Laravel apps with Breeze, Sanctum, Filament, or static UI.',
    nodeIds: nodes
      .filter((n) => n.filePath?.includes('examples/kindly-login-1122/routes/web.php'))
      .map((n) => n.id),
  },
];

let gitCommitHash = 'unknown';
try {
  gitCommitHash = execSync('git rev-parse HEAD', { cwd: projectRoot, encoding: 'utf8' }).trim();
} catch {}

const graph = {
  version: '1.0.0',
  project: {
    name: scan.name,
    languages: scan.languages,
    frameworks: scan.frameworks,
    description: scan.description,
    analyzedAt: new Date().toISOString(),
    gitCommitHash,
    scopeNote:
      'Scoped scan: MVP examples + root + docs + bin; 30k+ files excluded via .understandignore',
  },
  nodes,
  edges,
  layers,
  tour,
};

writeFileSync(
  `${projectRoot}/.understand-anything/knowledge-graph.json`,
  JSON.stringify(graph, null, 2)
);
writeFileSync(
  `${projectRoot}/.understand-anything/meta.json`,
  JSON.stringify(
    {
      lastAnalyzedAt: graph.project.analyzedAt,
      gitCommitHash,
      version: '1.0.0',
      analyzedFiles: scan.totalFiles,
      scope: 'mvp-examples-root-docs-bin',
    },
    null,
    2
  )
);

console.log(
  JSON.stringify(
    {
      nodes: nodes.length,
      edges: edges.length,
      layers: layers.length,
      tourSteps: tour.length,
    },
    null,
    2
  )
);
