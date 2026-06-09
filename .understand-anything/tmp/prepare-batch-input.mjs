import { readFileSync, writeFileSync } from 'node:fs';

const projectRoot = process.argv[2];
const batchIndex = Number(process.argv[3]);
const batches = JSON.parse(
  readFileSync(`${projectRoot}/.understand-anything/intermediate/batches.json`, 'utf8')
);
const batch = batches.batches.find((b) => b.batchIndex === batchIndex);
if (!batch) {
  console.error(`Batch ${batchIndex} not found`);
  process.exit(1);
}
writeFileSync(
  `${projectRoot}/.understand-anything/tmp/ua-file-analyzer-input-${batchIndex}.json`,
  JSON.stringify({
    projectRoot,
    batchFiles: batch.files,
    batchImportData: batch.batchImportData ?? {},
  })
);
console.log(`batch ${batchIndex}: ${batch.files.length} files`);
