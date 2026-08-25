<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Relatório de Funcionalidades - Gestão de Tarefas</title>
<style>
    @page { margin: 2cm 1.8cm; }
    body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; color: #1e293b; font-size: 11px; line-height: 1.55; }
    h1 { font-size: 24px; color: #0f3d5c; margin: 0 0 4px; }
    h2 { font-size: 15px; color: #0f3d5c; border-bottom: 2px solid #e2e8f0; padding-bottom: 4px; margin: 22px 0 10px; page-break-after: avoid; }
    h3 { font-size: 12px; color: #334155; margin: 12px 0 6px; }
    .cover { text-align: center; padding-top: 180px; page-break-after: always; }
    .cover .brand { font-size: 12px; letter-spacing: 3px; text-transform: uppercase; color: #94a3b8; margin-bottom: 16px; }
    .cover .subtitle { font-size: 13px; color: #64748b; margin-top: 14px; }
    .badge { display: inline-block; background: #ede9fe; color: #6d28d9; font-weight: bold; font-size: 9px; padding: 2px 8px; border-radius: 999px; }
    table { width: 100%; border-collapse: collapse; margin: 8px 0 14px; }
    th { background: #0f3d5c; color: #fff; text-align: left; padding: 7px 9px; font-size: 10px; }
    td { border-bottom: 1px solid #e2e8f0; padding: 6px 9px; vertical-align: top; }
    tr:nth-child(even) td { background: #f8fafc; }
    code { background: #f1f5f9; border: 1px solid #e2e8f0; padding: 1px 5px; border-radius: 4px; font-family: Menlo, Consolas, monospace; font-size: 9.5px; color: #0f172a; }
    pre { background: #0f172a; color: #e2e8f0; padding: 12px 14px; border-radius: 8px; font-family: Menlo, Consolas, monospace; font-size: 9.5px; overflow-x: hidden; white-space: pre-wrap; }
    .step { background: #f8fafc; border-left: 3px solid #0f3d5c; padding: 8px 12px; margin: 6px 0; }
    .tip { background: #ecfdf5; border-left: 3px solid #10b981; padding: 8px 12px; margin: 6px 0; }
    .warn { background: #fffbeb; border-left: 3px solid #f59e0b; padding: 8px 12px; margin: 6px 0; }
    ul { margin: 4px 0 10px 18px; padding: 0; }
    li { margin: 2px 0; }
    .footer-note { margin-top: 26px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; }
</style>
</head>
<body>

<div class="cover">
    <div class="brand">MedicalThermo Engenharia</div>
    <h1>Gestão de Tarefas<br>Relatório de Novas Funcionalidades</h1>
    <div class="subtitle">Guia de utilização das 10 melhorias implementadas<br>
        Tempo real · Busca global · Kanban interativo · Monitoramento · Linguagem natural ·<br>
        Calendário · PWA · API &amp; Webhooks · Assistente IA · Agenda Inteligente
    </div>
    <div class="subtitle" style="margin-top:40px;">{{ now()->format('d/m/Y') }} · Laravel 12 · {{ $tests }} testes automatizados passando</div>
</div>

<h2>Visão geral do que foi entregue</h2>
<table>
    <tr><th style="width:30px;">#</th><th style="width:190px;">Funcionalidade</th><th>Onde encontrar</th></tr>
    <tr><td>1</td><td>Tempo real (Laravel Reverb)</td><td>Automático em todo o sistema — toasts no canto inferior direito e badge de notificações atualizam sem recarregar a página.</td></tr>
    <tr><td>2</td><td>Busca global + Command Palette</td><td>Botão “Buscar…” no topo ou atalho <code>Cmd/Ctrl + K</code>.</td></tr>
    <tr><td>3</td><td>Kanban com arrastar-e-soltar</td><td>Menu <strong>Tarefas → Quadro</strong> (<code>/tarefas/quadro</code>).</td></tr>
    <tr><td>4</td><td>Pulse (monitoramento)</td><td>Menu <strong>Monitoramento</strong> (<code>/pulse</code>) — acesso exclusivo do gestor.</td></tr>
    <tr><td>5</td><td>Criação por linguagem natural</td><td><strong>Nova Tarefa</strong> → card “Criação inteligente”.</td></tr>
    <tr><td>6</td><td>Calendário + feed iCal</td><td>Menu <strong>Calendário</strong> (<code>/calendario</code>).</td></tr>
    <tr><td>7</td><td>PWA instalável / offline</td><td>Instalar pelo navegador (Chrome/Safari/Edge).</td></tr>
    <tr><td>8</td><td>API REST + Webhooks</td><td><code>/api/v1/...</code> com token Sanctum.</td></tr>
    <tr><td>9</td><td>Assistente IA</td><td>Menu <strong>Assistente</strong> (<code>/assistente</code>).</td></tr>
    <tr><td>10</td><td>Agenda Inteligente</td><td>Menu <strong>Agenda Inteligente</strong> (<code>/agenda-inteligente</code>).</td></tr>
</table>

<h2>1 · Tempo real com Laravel Reverb</h2>
<p>Todos os eventos do domínio (criação, atribuição, mudança de status/prazo/prioridade, comentários, anexos, bloqueios, aprovações, solicitações de alteração e cancelamentos) são transmitidos via WebSocket em canais privados:</p>
<ul>
    <li><code>private-user.{id}</code> — cada usuário recebe atualizações das tarefas em que está envolvido;</li>
    <li><code>private-task.{id}</code> — canal da tarefa para integrações futuras.</li>
</ul>
<div class="step"><strong>Como funciona na prática:</strong> enquanto você tem o sistema aberto, qualquer ação de outro usuário aparece instantaneamente como um toast (“Ana comentou em Inspeção elétrica”) e o contador de notificações aumenta sem recarregar.</div>
<div class="step"><strong>Como iniciar o servidor WebSocket:</strong></div>
<pre>php artisan reverb:start          # padrão: porta 8080</pre>
<div class="tip">Em produção, execute o Reverb como serviço permanente (Supervisor) atrás do load balancer.</div>

<h2>2 · Busca global e Command Palette (Cmd+K)</h2>
<ul>
    <li>Pressione <code>Cmd+K</code> (Mac) ou <code>Ctrl+K</code> (Windows/Linux), clique no botão “Buscar…”, digite ao menos 2 letras;</li>
    <li>A busca indexa título e descrição das tarefas (motor <em>database</em> do Laravel Scout);</li>
    <li>Setas ↑↓ navegam, <code>Enter</code> abre a tarefa, <code>Esc</code> fecha;</li>
    <li>Liderados encontram apenas tarefas próprias; gestores encontram todas.</li>
</ul>

<h2>3 · Kanban interativo (Livewire + drag-and-drop)</h2>
<div class="step"><strong>Mover tarefa:</strong> arraste o card até a coluna desejada e solte. O sistema valida a máquina de estados (ex.: Nova → Recebida → Em andamento). Movimentos inválidos exibem mensagem amigável e nada é alterado.</div>
<ul>
    <li>Filtro por responsável no topo (gestor);</li>
    <li>O quadro se atualiza sozinho quando outros usuários movem cards (integração com Reverb);</li>
    <li>Cards atrasadas recebem destaque vermelho “Atrasada”.</li>
</ul>

<h2>4 · Monitoramento (Pulse) e feature flags (Pennant)</h2>
<ul>
    <li><strong>/pulse</strong>: requisições lentas, queries pesadas, exceções, uso por usuário — tudo gravado automaticamente;</li>
    <li>Flags disponíveis: <code>ai-assistant</code> e <code>auto-scheduling</code>, ativas para gestores. Para liberar a um liderado específico:</li>
</ul>
<pre>Laravel\Pennant\Feature::for($user)->activate('ai-assistant');</pre>

<h2>5 · Criação de tarefas por linguagem natural</h2>
<p>No formulário <strong>Nova Tarefa</strong>, use o card roxo “Criação inteligente”: escreva a tarefa em português, clique em <strong>Interpretar</strong> e os campos título, prazo, prioridade e recorrência são preenchidos automaticamente.</p>
<table>
    <tr><th>Você escreve</th><th>O sistema entende</th></tr>
    <tr><td>“Reunião com o time amanhã às 15h urgente”</td><td>Prazo amanhã 15:00 · Prioridade urgente</td></tr>
    <tr><td>“Inspecionar compressores sexta 10h importante”</td><td>Sexta-feira 10:00 · Prioridade importante</td></tr>
    <tr><td>“Backup toda segunda 08h”</td><td>Próxima segunda 08:00</td></tr>
    <tr><td>“Auditoria interna 15/09 crítica”</td><td>15/09 (ano automático) · Prioridade crítica</td></tr>
    <tr><td>“Checklist todo dia 07h”</td><td>Recorrência diária 07:00</td></tr>
    <tr><td>“Inventário em 3 dias”</td><td>Daqui a 3 dias 17:00</td></tr>
</table>
<div class="tip">Sem prazo definido, o padrão é amanhã às 17h. Palavras reconhecidas: hoje, amanhã, depois de amanhã, dias da semana (com ou sem “próxima”), dd/mm, em N dias/semanas, HH:MM, HHh.</div>

<h2>6 · Calendário mensal e sincronização iCal</h2>
<ul>
    <li>Visualização mensal com todas as suas tarefas por prazo (setas ← → navegam entre meses);</li>
    <li><strong>Feed iCal:</strong> botão “Copiar feed iCal” copia uma URL privada com token seguro. Cole no Google Agenda (“Outros calendários → De URL”) ou Outlook/Apple Calendar para ver as tarefas junto com sua agenda;</li>
    <li>Cada usuário tem um feed próprio — gestores veem todas as tarefas; liderados só as suas.</li>
</ul>

<h2>7 · PWA — instalar no celular/desktop e uso offline</h2>
<div class="step"><strong>Instalar:</strong> abra o sistema no Chrome/Edge → ícone ⊕ na barra de endereço → “Instalar”. No iPhone: Safari → Compartilhar → “Adicionar à Tela de Início”. O app abre em janela própria, como aplicativo nativo.</div>
<div class="step"><strong>Offline:</strong> sem internet, páginas já visitadas e recursos continuam carregando; navegações novas mostram a tela “Você está sem conexão” com botão “Tentar novamente”.</div>

<h2>8 · API REST v1 e Webhooks</h2>
<h3>Autenticação por token (Sanctum)</h3>
<pre>// Gerar token para um usuário (tinker ou endpoint interno)
$user = App\Models\User::find(1);
$token = $user->createToken('integracao-erp')->plainTextToken;</pre>
<pre># Exemplo de uso
curl -H "Authorization: Bearer SEU_TOKEN" \
     -H "Accept: application/json" \
     https://seu-dominio.com/api/v1/tasks?status=nova</pre>
<h3>Endpoints disponíveis</h3>
<table>
    <tr><th>Método</th><th>Rota</th><th>Descrição</th></tr>
    <tr><td>GET</td><td>/api/v1/tasks</td><td>Lista com filtros status, priority, assigned_to, per_page (máx. 100)</td></tr>
    <tr><td>POST</td><td>/api/v1/tasks</td><td>Cria tarefa (title, priority, due_at, description?, assigned_to?, recurrence_frequency?)</td></tr>
    <tr><td>GET</td><td>/api/v1/tasks/{id}</td><td>Detalhe com comentários e responsável</td></tr>
    <tr><td>PATCH</td><td>/api/v1/tasks/{id}/status</td><td>Move status validando máquina de estados</td></tr>
    <tr><td>POST</td><td>/api/v1/tasks/{id}/comments</td><td>Adiciona comentário</td></tr>
    <tr><td>GET/POST/DELETE</td><td>/api/v1/webhooks</td><td>Gerencia endpoints de webhook do usuário</td></tr>
</table>
<h3>Webhooks assinados (HMAC-SHA256)</h3>
<div class="step">Cadastre a URL de destino em <code>POST /api/v1/webhooks</code>. A cada evento de tarefa o sistema envia um POST JSON com headers <code>X-GT-Event</code> (nome do evento) e <code>X-GT-Signature</code> (HMAC do corpo com seu secret). Valide a assinatura no receptor antes de confiar nos dados. Tentativas automáticas: 3x com backoff.</div>

<h2>9 · Assistente IA</h2>
<ul>
    <li><strong>Resumo do dia:</strong> contadores de atrasadas, entregas de hoje, bloqueadas, aguardando aprovação e concluídas na semana + narrativa em português;</li>
    <li><strong>Foco sugerido agora:</strong> ranking das 5 tarefas mais críticas com pontuação transparente (prioridade + atraso + proximidade do prazo + idade) e justificativas legíveis;</li>
    <li><strong>Dividir em passos:</strong> cada sugestão oferece decomposição em subtarefas objetivas.</li>
</ul>
<div class="tip"><strong>Modo LLM opcional:</strong> defina <code>OPENAI_API_KEY=...</code> no arquivo <code>.env</code> e o assistente passa a gerar narrativa e divisão de tarefas via GPT. Sem chave, tudo funciona no modo heurístico determinístico — nenhum recurso fica indisponível.</div>

<h2>10 · Agenda Inteligente (auto-agendamento)</h2>
<div class="step"><strong>Como usar:</strong> abra <code>/agenda-inteligente</code> → “Gerar nova sugestão” distribui as tarefas abertas em blocos de trabalho (seg–sex, 09h–12h e 13h–18h), priorizando críticas e atrasadas e prazos próximos → revise → “Aplicar agenda” grava o horário planejado em cada tarefa.</div>
<ul>
    <li>Duração por tarefa: campo <code>estimated_minutes</code> (padrão 60 min, mínimo 30);</li>
    <li>Tarefas já agendadas não entram em novas propostas;</li>
    <li>Blocos nunca atravessam almoço nem fim de semana — a tarefa maior que a janela vai para a próxima janela que a comporte inteira.</li>
</ul>

<h2>Como rodar tudo localmente</h2>
<pre>composer install && npm install && npm run build
cp .env.example .env        # configure BROADCAST_CONNECTION=reverb
php artisan key:generate
php artisan migrate

# Terminal 1                # Terminal 2              # Terminal 3
php artisan serve           php artisan reverb:start  npm run dev</pre>
<div class="warn"><strong>Produção:</strong> rode também <code>php artisan queue:work</code> (webhooks e notificações) e mantenha <code>reverb:start</code> sob Supervisor. Defina <code>OPENAI_API_KEY</code> apenas se quiser o modo LLM.</div>

<h2>Qualidade</h2>
<table>
    <tr><th>Métrica</th><th>Resultado</th></tr>
    <tr><td>Suíte de testes automatizados (Pest)</td><td>{{ $tests }} testes · {{ $assertions }} asserções — 100% aprovados</td></tr>
    <tr><td>Novos testes desta entrega</td><td>10 suítes (Fase 14–23): tempo real, busca, kanban, Pulse/Pennant, parser NL, calendário/iCal, PWA, API/webhooks, assistente IA, agenda</td></tr>
    <tr><td>Análise estática de estilo</td><td>Laravel Pint (PSR-12) aplicado</td></tr>
    <tr><td>Front-end compilado</td><td>Vite build OK (Tailwind 4 + Livewire + Echo + SortableJS)</td></tr>
</table>

<div class="footer-note">Gestão de Tarefas — MedicalThermo Engenharia · Documento gerado automaticamente em {{ now()->format('d/m/Y H:i') }} · Rotas do relatório: GET /relatorio-funcionalidades (HTML→PDF, autenticado) · Arquivo salvo em docs/RELATORIO-FUNCIONALIDADES.pdf</div>

</body>
</html>
