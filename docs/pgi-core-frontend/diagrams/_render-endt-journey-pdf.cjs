const path = require('path');
const fs = require('fs');
const { execSync } = require('child_process');

(async () => {
  let puppeteer;
  try {
    puppeteer = require('puppeteer');
  } catch {
    console.log('Installing puppeteer locally for PDF render...');
    execSync('npm install --no-save puppeteer@24', {
      stdio: 'inherit',
      cwd: path.resolve(__dirname, '../..'),
    });
    puppeteer = require(path.resolve(__dirname, '../../node_modules/puppeteer'));
  }

  const htmlPath = path.resolve(__dirname, 'auto-endorsement-journey.html');
  const pdfPath = path.resolve(__dirname, 'auto-endorsement-journey.pdf');
  const fileUrl = 'file:///' + htmlPath.replace(/\\/g, '/');

  const browser = await puppeteer.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--allow-file-access-from-files'],
  });
  const page = await browser.newPage();
  await page.goto(fileUrl, { waitUntil: 'networkidle0', timeout: 120000 });
  await page.waitForFunction(
    () => document.documentElement.getAttribute('data-mermaid-done') === '1',
    { timeout: 60000 }
  );
  await new Promise((r) => setTimeout(r, 800));
  await page.pdf({
    path: pdfPath,
    format: 'A4',
    printBackground: true,
    margin: { top: '12mm', right: '10mm', bottom: '12mm', left: '10mm' },
  });
  await browser.close();
  console.log('PDF written:', pdfPath, 'bytes:', fs.statSync(pdfPath).size);
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
