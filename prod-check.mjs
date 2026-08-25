import { chromium } from 'playwright';

const base = 'https://tarefas.medicalthermo.com';
const out = '/tmp/opencode';

const browser = await chromium.launch();
const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });

await page.goto(base + '/login');
await page.fill('#email', 'e2e-func@temp.com');
await page.fill('#password', "Func12345!");
await page.click('button[type=submit]');
await page.waitForLoadState('networkidle');

const url = page.url();
console.log('apos login:', url);

if (!url.includes('/painel') && !url.includes('/minhas-tarefas')) {
  console.log('LOGIN FALHOU - sem credenciais validas');
  await browser.close();
  process.exit(0);
}

await page.goto(base + '/tarefas/quadro');
await page.waitForLoadState('networkidle');
await page.waitForTimeout(1000);
await page.screenshot({ path: `${out}/prod-kanban.png` });

await page.goto(base + '/relatorios');
await page.waitForLoadState('networkidle');
await page.screenshot({ path: `${out}/prod-relatorios.png` });

await browser.close();
console.log('ok');
