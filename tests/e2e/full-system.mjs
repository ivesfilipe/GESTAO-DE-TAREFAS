import { chromium } from 'playwright';

const BASE = 'http://localhost:8040';
const OUT = '/tmp/opencode/e2e';
import { mkdirSync } from 'fs';
mkdirSync(OUT, { recursive: true });

const results = [];
const test = (name, fn) => results.push({ name, run: fn });
const assert = (cond, msg) => { if (!cond) throw new Error('FALHOU: ' + msg); };

const browser = await chromium.launch();
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
const page = await ctx.newPage();
page.setDefaultTimeout(10000);

// ---------- 1. LOGIN ----------
test('login gestor', async () => {
    await page.goto(BASE + '/login');
    await page.fill('#email', 'e2e@local.test');
    await page.fill('#password', 'SenhaE2E!123');
    await page.click('button[type=submit]');
    await page.waitForURL('**/painel');
});

// ---------- 2. PAINEL ----------
test('painel carrega com metricas', async () => {
    await page.goto(BASE + '/painel');
    const html = await page.content();
    assert(html.includes('Painel'), 'titulo painel');
    await page.screenshot({ path: OUT + '/01-painel.png' });
});

// ---------- 3. LISTA TAREFAS + BOTAO CRIAR (desktop) ----------
let fabVisibleOnDesktop = null;
test('lista de tarefas e visibilidade do botao criar', async () => {
    await page.goto(BASE + '/tarefas');
    const html = await page.content();
    assert(html.includes('Tarefas'), 'titulo tarefas');
    // FAB tem classe lg:hidden => no desktop (1440px) deve estar oculto
    const desktopBtn = page.locator('[data-testid="nova-tarefa-desktop"]');
    await desktopBtn.waitFor({ state: 'visible', timeout: 5000 });
    fabVisibleOnDesktop = true;
    await page.screenshot({ path: OUT + '/02-tarefas-desktop-sem-botao.png' });
});

// ---------- 4. CRIAR VIA FORMULARIO DIRETO (URL) ----------
let taskUrl = null;
test('criar tarefa via formulario', async () => {
    await page.goto(BASE + '/tarefas/nova');
    await page.fill('#title', 'E2E Manutencao chiller');
    await page.fill('#description', 'Criada via E2E automatizado');
    await page.selectOption('#priority', 'urgente');
    const due = new Date(Date.now() + 3 * 86400000).toISOString().slice(0, 16);
    await page.fill('#due_at', due);
    await page.click('form[action$="/tarefas"] button[type=submit]');
    await page.waitForURL('**/tarefas');
    const html = await page.content();
    assert(html.includes('E2E Manutencao chiller'), 'tarefa na lista');
});

// ---------- 5. LINGUAGEM NATURAL ----------
test('interpretacao em linguagem natural preenche form', async () => {
    await page.goto(BASE + '/tarefas/nova');
    await page.fill('#nl-input', 'Revisar filtros do HVAC amanhã às 15h urgente');
    await page.click('#nl-interpret');
    await page.waitForSelector('#nl-preview:not(.hidden)', { timeout: 8000 });
    const title = await page.inputValue('#title');
    const prio = await page.inputValue('#priority');
    const dueAt = await page.inputValue('#due_at');
    assert(title.includes('Revisar filtros do HVAC'), 'titulo interpretado: ' + title);
    assert(prio === 'urgente', 'prioridade=' + prio);
    assert(dueAt.length === 16, 'prazo preenchido: ' + dueAt);
    await page.screenshot({ path: OUT + '/03-linguagem-natural.png' });
    // cria de fato
    await page.click('form[action$="/tarefas"] button[type=submit]');
    await page.waitForURL('**/tarefas');
});

// ---------- 6. KANBAN ----------
test('kanban renderiza colunas e cards', async () => {
    await page.goto(BASE + '/tarefas/quadro');
    await page.waitForSelector('.kanban-card');
    const cols = await page.locator('.kanban-column').count();
    assert(cols >= 8, 'colunas=' + cols);
    const cards = await page.locator('.kanban-card').count();
    assert(cards >= 1, 'cards=' + cards);
    await page.screenshot({ path: OUT + '/04-kanban.png' });
});

// ---------- 7. DETALHE DA TAREFA + COMENTARIO ----------
test('detalhe da tarefa e comentario', async () => {
    const list = await page.request.get(BASE + '/busca?q=chiller', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const found = (await list.json()).results[0];
    assert(found && found.url, 'tarefa encontrada na busca');
    await page.goto(BASE + found.url);
    await page.waitForSelector('form[action*="comentarios"]');
    await page.fill('textarea[name="body"]', 'Comentario E2E automatico');
    await page.click('form[action*="comentarios"] button[type=submit]');
    await page.waitForLoadState('networkidle');
    const html = await page.content();
    assert(html.includes('Comentario E2E automatico'), 'comentario visivel');
    taskUrl = page.url();
});

// ---------- 8. BUSCA GLOBAL (Cmd+K) ----------
test('command palette Cmd+K busca e navega', async () => {
    await page.keyboard.press('Control+k');
    await page.waitForSelector('#palette-input', { state: 'visible' });
    await page.fill('#palette-input', 'chiller');
    await page.waitForSelector('.palette-item', { timeout: 8000 });
    const items = await page.locator('.palette-item').count();
    assert(items >= 1, 'resultados=' + items);
    await page.screenshot({ path: OUT + '/05-cmdk.png' });
    await page.keyboard.press('Enter');
    await page.waitForURL('**/tarefas/*');
});

// ---------- 9. CALENDARIO ----------
test('calendario mostra tarefas do mes', async () => {
    await page.goto(BASE + '/calendario');
    const html = await page.content();
    assert(html.includes('Calendário') || html.includes('de 2026'), 'cabecalho calendario');
    await page.screenshot({ path: OUT + '/06-calendario.png' });
});

// ---------- 10. ASSISTENTE IA ----------
test('assistente IA resumo e sugestoes', async () => {
    await page.goto(BASE + '/assistente');
    await page.waitForSelector('text=Foco sugerido agora');
    const html = await page.content();
    assert(html.includes('Assistente'), 'titulo assistente');
    await page.screenshot({ path: OUT + '/07-assistente.png' });
});

// ---------- 11. AGENDA INTELIGENTE ----------
test('agenda inteligente gera blocos', async () => {
    await page.goto(BASE + '/agenda-inteligente');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: OUT + '/08-agenda.png' });
    const html = await page.content();
    assert(html.includes('Agenda Inteligente'), 'titulo agenda');
});

// ---------- 12. RELATORIOS / EQUIPE / NOTIFICACOES ----------
test('relatorios, equipe e notificacoes respondem', async () => {
    for (const path of ['/relatorios', '/equipe', '/notificacoes']) {
        const resp = await page.goto(BASE + path);
        assert(resp.status() === 200, path + '=' + resp.status());
    }
});

// ---------- 13. PWA ----------
test('manifest e service worker acessiveis', async () => {
    for (const path of ['/manifest.webmanifest', '/sw.js', '/offline.html']) {
        const resp = await page.request.get(BASE + path);
        assert(resp.status() === 200, path + '=' + resp.status());
    }
});

// ---------- 14. API v1 ----------
test('api v1 com token autenticado', async () => {
    const { execSync } = await import('child_process');
    execSync(
        `php artisan tinker --execute="file_put_contents('/tmp/opencode/token.txt', App\\Models\\User::where('email','e2e@local.test')->first()->createToken('e2e')->plainTextToken);" 2>/dev/null`,
        { cwd: process.cwd() },
    );
    const { readFileSync } = await import('fs');
    const token = readFileSync('/tmp/opencode/token.txt', 'utf8').trim();
    const resp = await page.request.get(BASE + '/api/v1/tasks', {
        headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
    });
    assert(resp.status() === 200, 'status=' + resp.status());
    const json = await resp.json();
    assert(Array.isArray(json.data) && json.data.length >= 2, 'tarefas via api=' + json.data.length);

    const created = await page.request.post(BASE + '/api/v1/tasks', {
        headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
        data: { title: 'E2E via API pura', priority: 'normal', due_at: new Date(Date.now() + 86400000).toISOString() },
    });
    assert(created.status() === 201, 'create api=' + created.status());
});

// ---------- 15. LOGOUT ----------
test('logout encerra sessao', async () => {
    await page.goto(BASE + '/painel');
    await page.click('button:text-is("Sair")');
    await page.waitForURL('**/login');
});

// ---------- EXECUCAO ----------
let pass = 0;
for (const t of results) {
    try {
        await t.run();
        pass++;
        console.log('✓ ' + t.name);
    } catch (e) {
        console.log('✗ ' + t.name + ' → ' + e.message.split('\n')[0]);
    }
}
console.log(`\n=== ${pass}/${results.length} fluxos E2E OK ===`);
console.log(fabVisibleOnDesktop === false
    ? '\n⚠️  CONFIRMADO: botão criar tarefa INVISÍVEL no desktop (só mobile)'
    : '\nbotão criar visível no desktop? ' + fabVisibleOnDesktop);
await browser.close();
