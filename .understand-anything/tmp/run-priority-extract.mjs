import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import { spawnSync } from 'node:child_process';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const projectRoot = process.argv[2];
const pluginRoot = process.argv[3];
const skillDir = join(pluginRoot, 'skills/understand');
const batches = JSON.parse(
  readFileSync(`${projectRoot}/.understand-anything/intermediate/batches.json`, 'utf8')
);

const priority = batches.batches
  .filter((b) => b.files.some((f) => f.fileCategory === 'code'))
  .filter((b) => {
    const paths = b.files.map((f) => f.path).join(' ');
    return (
      paths.includes('/app/') ||
      paths.includes('/routes/') ||
      paths.includes('/tests/') ||
      paths.startsWith('bin/') ||
      paths.includes('bin/')
    );
  })
  .map((b) => b.batchIndex);

const unique = [...new Set(priority)].sort((a, b) => a - b);
const results = [];

for (const batchIndex of unique) {
  const batch = batches.batches.find((b) => b.batchIndex === batchIndex);
  const inputPath = `${projectRoot}/.understand-anything/tmp/ua-file-analyzer-input-${batchIndex}.json`;
  const outputPath = `${projectRoot}/.understand-anything/tmp/ua-file-extract-results-${batchIndex}.json`;

  writeFileSync(
    inputPath,
    JSON.stringify({
      projectRoot,
      batchFiles: batch.files,
      batchImportData: batch.batchImportData ?? {},
    })
  );

  const proc = spawnSync(
    'node',
    [join(skillDir, 'extract-structure.mjs'), inputPath, outputPath],
    { encoding: 'utf8' }
  );

  if (proc.status !== 0) {
    console.error(`batch ${batchIndex} failed:`, proc.stderr?.slice(0, 200));
    continue;
  }

  if (!existsSync(outputPath)) continue;
  const extracted = JSON.parse(readFileSync(outputPath, 'utf8'));
  results.push({ batchIndex, extracted });
  console.log(`batch ${batchIndex}: ${extracted.filesAnalyzed} files`);
}

writeFileSync(
  `${projectRoot}/.understand-anything/intermediate/priority-extract.json`,
  JSON.stringify({ batches: unique.length, results }, null, 2)
);
