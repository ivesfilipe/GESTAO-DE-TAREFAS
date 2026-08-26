import { chromium } from 'playwright';

const BASE = 'http://localhost:8040';
const browser = await chromium.launch();
const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
page.setDefaultTimeout(10000);

await page.goto(BASE + '/login');
await page.fill('#email', 'e2e@local.test');
await page.fill('#password', 'SenhaE2E!123');
await page.click('button[type=submit]');
await page.waitForURL('**/painel');

await page.goto(BASE + '/tarefas/nova');
await page.fill('#nl-input', 'Apresentar resultados do trimestre para a diretoria amanhã às 10h urgente');
await page.click('#nl-interpret');

await page.waitForFunction(() => {
    const d = document.getElementById('description');
    return d && d.value.trim().length > 50;
}, undefined, { timeout: 15000 });

const title = await page.inputValue('#title');
const desc = await page.inputValue('#description');
console.log('✓ titulo:', title);
console.log('✓ descricao gerada (' + desc.length + ' chars):');
console.log(desc.split('\n').slice(0, 4).join('\n'));

await page.fill('#title', title + ' - versao revisada');
await page.click('#nl-description');
await page.waitForFunction((old) => document.getElementById('description').value !== old && document.getElementById('description').value.length > 50, desc, { timeout: 15000 });
console.log('✓ botao "Sugerir descricao" regenerou com novo titulo');

await page.screenshot({ path: '/tmp/opencode/e2e/09-descricao-ia.png', fullPage: true });
await browser.close();
console.log('=== E2E descricao IA OK ===');
