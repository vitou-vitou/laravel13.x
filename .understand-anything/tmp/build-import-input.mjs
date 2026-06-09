import { readFileSync, writeFileSync } from 'node:fs';

const projectRoot = process.argv[2];
const scan = JSON.parse(readFileSync(`${projectRoot}/.understand-anything/tmp/ua-scan-files.json`, 'utf8'));
writeFileSync(
  `${projectRoot}/.understand-anything/tmp/ua-import-map-input.json`,
  JSON.stringify({ projectRoot, files: scan.files })
);
