# Changelog

Formato de cada entrada:
```
## [data] — [Fase X] — [resumo curto]
- O que foi adicionado/alterado
- Arquivos principais afetados
```

---

## [2026-08-27] — Fase 27 Parte 2: Copiloto Inteligente do Gestor — UX, segurança e testes

### Delegação Inteligente (`/tarefas/nova`)
- Structured output JSON com: título, tipo (demanda|compra|serviço|desenvolvimento|responsabilidade|outro), prioridade, prazo, responsável sugerido com justificativa, descrição, critérios de aceitação, evidências esperadas, checkpoints, informações faltantes, confiança.
- UX mobile-first: entrada livre, preview do rascunho, botão "Aplicar ao formulário" (nada criado automaticamente).
- Validação de assignee inválido (ID não enviado como candidato → descartado).
- Fallback preserva dados do parser sem descrição heurística genérica.

### Copiloto do Gestor (`/assistente`)
- Radar determinístico (Top 5) sem depender de LLM: pontuação por atraso, bloqueio, aprovação, prioridade.
- Chat com tool calling: `list_overdue_tasks`, `list_tasks_due_today`, `list_blocked_tasks`, `list_tasks_awaiting_approval`, `get_team_member_profile`, `search_team_knowledge`, `search_tasks`, `search_company_knowledge`.
- "Dividir em passos": botão em cada card de tarefa no chat, abre painel lateral com passos contextuais.
- Cobrança sugerida: rascunho objetivo (tons: Objetiva, Firme, Colaborativa), **nunca enviada automaticamente**.
- Contexto seletivo: envia apenas perfil + Top-K chunks + dados operacionais específicos, não o banco inteiro.

### Perfil Inteligente do Liderado (`/equipe/{user}`)
- Filtro de sugestões por categoria (Todas, Demandas, Compras, Serviços, Desenvolvimento, Responsabilidades).
- Fontes do resumo IA persistidas (`ai_summary_sources`) e invalidadas ao editar perfil/documentos.
- Desenvolvimento profissional: **apenas** objetivos explicitamente registrados.
- Documentos: allowlist (pdf,doc,docx,txt,md,csv), status de processamento, remoção com limpeza de chunks.
- XSS mitigado: substituição de `innerHTML` por `document.createElement`/`textContent`.

### Segurança e qualidade
- ZDR bloqueante: providers externos (Groq/OpenAI) **não recebem contexto** enquanto não confirmado.
- Fallback pago para OpenAI bloqueado; logs `AIUsageLog` apenas metadados (provider, modelo, tokens, status, duração, erro, user_id opcional).
- Escopo por gestor (`users.manager_id`) em tarefas, API, dashboard, relatórios, radar, tools do Copiloto.
- Prompt injection em documento não quebra autorização (teste dedicado).
- Métricas corrigidas: ciclo = `created_at → completed_at`, atraso de entrega compara `completed_at` a `due_at`, reprovação de desempenho só `nao_atende`.

### Testes (270 testes / 616 assertions)
- GroqProvider: HTTP 200/401/403/429/500, timeout, JSON inválido, chave ausente.
- ZDR não confirmado bloqueia provider externo antes do prompt.
- Escopo entre gestores: API, dashboard, relatórios, perfil, tools não vazam dados de outra equipe.
- Métricas, normalização de critérios/evidências, fontes de perfil.
- Prompt injection, assignee inválido, fallback IA, parser preservado.

### Arquivos principais
- `app/Services/AI/SmartDelegationService.php`, `app/Services/AI/CopilotService.php`, `app/Services/AI/ProfileIntelligenceService.php`, `app/Services/AI/TaskSuggestionService.php`
- `app/Services/AI/ManagementRadarService.php`, `app/Services/AI/AIService.php`, `app/Services/AI/Providers/`
- `app/Http/Controllers/TaskController.php`, `app/Http/Controllers/AssistantController.php`, `app/Http/Controllers/TeamProfileController.php`
- `resources/views/tasks/create.blade.php`, `resources/views/assistant/index.blade.php`, `resources/views/team/profile.blade.php`
- `tests/Feature/Part1SecurityTest.php`, `tests/Unit/Services/AI/GroqProviderTest.php`
- `database/migrations/2026_08_27_*.php`

---

## [2026-08-26] — Fase 27 Parte 2: Copiloto Inteligente do Gestor — finalização UX e deploy

### Novos serviços de IA
- `SmartDelegationService`: delegação com structured output JSON e fallback controlado para o parser.
- `CopilotService`: chat do gestor com tool calling (`list_overdue_tasks`, `list_tasks_due_today`, `list_blocked_tasks`, `list_tasks_awaiting_approval`, `get_team_member_profile`, `search_team_knowledge`) e rascunho de cobrança.
- `ProfileIntelligenceService`: geração/atualização do resumo inteligente do perfil profissional do liderado.
- `TaskSuggestionService`: sugestões de tarefas a partir do perfil, responsabilidades, objetivos e métricas.

### UX redesenhada
- `/tarefas/nova` com aba “Delegar com IA” mobile-first: entrada em linguagem natural, pré-visualização do rascunho, checklist de campos e criação com confirmação humana.
- `/assistente` com radar de risco, chat com tools e rascunho de cobrança; nunca envia mensagem automaticamente.
- `/equipe/{user}` com perfil profissional editável, resumo IA, sugestões de tarefas, documentos e status de processamento.

### Qualidade e segurança
- 230 testes automatizados passando.
- `MockProvider` ajustado para simular respostas de cobrança e sugestões.
- Respostas JSON de perfil/sugestões incluem `provider` e `mock`.
- Pint limpo e build Vite atualizado.

### Deploy
- Deploy em produção executado e validado em https://tarefas.medicalthermo.com.

---

## [2026-08-26] — Fase 27 Parte 1: Copiloto Inteligente do Gestor

### Arquitetura multi-provider de IA
- `config/ai.php` com providers Groq (padrão), OpenAI, Ollama e mock.
- `AIProviderInterface`, `AIRequest`/`AIResponse`, `AIProviderManager`, `AIService`.
- Providers Groq/OpenAI/Ollama/Mock em `app/Services/AI/Providers/`.
- Sem fallback pago automático (`AI_FALLBACK_ENABLED=false` por padrão).
- Modo mock quando `GROQ_API_KEY` ausente.

### Segurança e governança
- `ZeroDataRetention` bloqueia/anonimiza dados reais enquanto `GROQ_ZDR_CONFIRMED=false`.
- `AIUsageLog` registra todas as chamadas para auditoria.
- `TeamMemberPolicy` e Gates garantem acesso apenas ao gestor.

### Memória gerencial persistente
- Models: `TeamMemberProfile`, `TeamMemberDocument`, `TeamMemberKnowledgeChunk`.
- `TeamKnowledgeService` com chunking local e retrieval lexical por `LIKE`.
- `DocumentTextExtractor` para `.txt`, `.md`, `.pdf` e `.docx`.

### Serviços de negócio
- `ManagementRadarService`: resumo de risco do time.
- `DelegationRecommendationService`: sugere assignee, tipo, critérios, evidências e prazo.
- `TeamPerformanceService`: métricas de carga e performance por liderado.

### Campos adicionais em tarefas
- `task_type` (`demanda|compra|servico|desenvolvimento|responsabilidade|outro`).
- `acceptance_criteria` e `expected_evidence`.
- `CreateTask` e `UpdateTask` atualizados; formulário `/tarefas/nova` com sugestão IA.

### Superfícies
- **Delegação inteligente** em `/tarefas/nova` (rascunho com confirmação humana).
- **Copiloto/Radar** em `/assistente` com chat, radar e foco sugerido.
- **Perfil inteligente** em `/equipe/{user}` com métricas, documentos e análise IA.

### Documentação e testes
- ADRs `ADR-014` (multi-provider) e `ADR-015` (memória gerencial).
- Docs em `docs/ai/`.
- Testes unitários em `tests/Unit/Services/AI/` e feature `AiAssistantTest`, `AiDelegationTest`, `AiTeamProfileTest`.

### Preservação
- `NaturalLanguageTaskParser` intacto.
- `AiAssistantService` mantido e delega para a nova arquitetura (compatível com `services.openai.key` legado).

---

## [2026-08-25] — Kanban, Relatórios de Desempenho e Tarefas Recorrentes

### Quadro Kanban
- `GET /tarefas/quadro`: 8 colunas (Sem responsável → Concluída, incluindo
  Bloqueada e Reprovada), contadores por coluna, filtro por responsável
  (gestor) e visão restrita às próprias tarefas (liderado)
- Drag-and-drop com SortableJS 1.15.6 versionado em `public/js` (sem CDN)
- Transições validadas no cliente (mapa de estados) e no servidor; card
  inválido volta com toast explicativo
- Gestor arrasta "Aguardando aprovação → Concluída" para aprovar direto
- `changeStatus` e `approve` respondem JSON (`422` amigável) para fetch

### Relatórios de Desempenho (gestor)
- `GET /relatorios` com filtros 30/90/365 dias
- KPIs: criadas, concluídas, tempo médio de conclusão (ciclo), % entregas
  fora do prazo e taxa de retrabalho (reprovadas ÷ revisadas)
- Desempenho por pessoa: abertas, atrasadas (fuso do responsável),
  concluídas com barra comparativa, ciclo médio, % fora do prazo,
  reprovadas
- Reprovações por motivo (4 categorias) com barras
- SQL portável (ciclo calculado em PHP — sem TIMESTAMPDIFF, compatível
  SQLite/MySQL)

### Tarefas Recorrentes
- Migration: `recurrence_frequency`, `recurrence_next_at`,
  `recurrence_series_id` em tasks (índices)
- Frequências: diária, semanal, quinzenal, mensal (cadência fixa —
  adequado a manutenção preventiva/PMOC)
- Comando `tarefas:gerar-recorrentes` agendado a cada 10 minutos: cria a
  próxima instância quando a cadência vence (reusa CreateTask → histórico
  e notificação automáticos), avança a cadência sem criar instâncias no
  passado e encerra o ciclo na origem
- UI: select "Repetir" no formulário, badges ↻ no quadro/lista/detalhe
- Links "Quadro" e "Relatórios" no menu lateral (relatórios só gestor)

### Observação de ambiente
- Produção utiliza `DB_CONNECTION=sqlite` (não MariaDB como indicado em
  docs/23) — funcional, volume baixo; doc será corrigido em oportunidade
  futura

### Validação
- 101 testes (13 novos), Pint limpo, screenshots Playwright local +
  produção (kanban e relatórios com dados reais da equipe)
- Deploy via auto-deploy com migration aplicada

---

## [2026-08-24] — Identidade visual MedicalThermo + seed de demonstração

### Branding
- Paleta extraída do site oficial medicalthermo.com: navy `#083048`
  (logo "Medical"/linha) e azul institucional `#1880C0` ("Thermo"/HERZOG)
- Tokens `brand-50..950` no Tailwind 4 (`resources/css/app.css` via @theme)
- Todas as views migram `blue-*` → `brand-*` (botões, badges, links,
  estados de foco, FAB, bottom-nav)
- Logos oficiais versionadas em `public/images/` (cor, branca, favicons
  32/192) + `theme-color` navy
- Sidebar com logo + label "Gestão de Tarefas"; topbar mobile com logo
- Login e convite redesenhados: gradiente navy, logo branca, rodapé
  institucional
- `welcome.blade.php` (scaffold morto) removido

### Dados de demonstração (DatabaseSeeder)
- Gestor `gestor@medicalthermo.com` / senha `senha123` + 4 liderados
  (`ana|bruno|carla|diego@medicalthermo.com` / `senha123`)
- 23 tarefas cobrindo os 9 status, 4 prioridades, atrasadas, vence-hoje,
  bloqueadas com motivo/dependência, reprovadas categorizadas, concluídas
  aprovadas e 1 cancelada (soft delete)
- 2 change requests pendentes (prazo e prioridade), 4 comentários,
  histórico inicial

### Validação
- Screenshots Playwright: login, painel, tarefas, detalhe, minhas-tarefas
  (desktop 1440px e mobile 390px)
- Suíte: 88 testes passando

### Arquivos principais afetados
- `resources/css/app.css`, `resources/views/**` (todas), `public/images/*`
  (novos), `database/seeders/DatabaseSeeder.php`,
  `public/build/*` (rebuild)

---

## [2026-08-24] — Correções críticas + notificações em produção

### Segurança
- IDOR corrigido em `PATCH /tarefas/{task}/status`: exigia gate
  `view-task` e não validava vínculo com a tarefa — qualquer usuário
  logado podia mover ou cancelar tarefa alheia. Agora: cancelar é
  exclusivo do gestor; demais transições, só do responsável
- `TaskController::changeStatus()` (app/Http/Controllers)

### Bugs de fluxo corrigidos
- Botão "Desbloquear" chamava `tasks.approve` (rota errada) e gerava 500;
  corrigido para `tasks.unblock`
- Modal "Atribuir" sempre vazio: `show()` não repassava `$liderados`
- Filtro por responsável na listagem sempre vazio: `index()` não
  repassava `$teamMembers`

### Notificações ativadas (eram código morto desde a Fase 9)
- As 6 classes de notification nunca eram disparadas; a central de
  notificações ficava sempre vazia em produção
- Novo `NotificarPartesInteressadasListener`: dispara nova tarefa
  (criação/atribuição), comentário (criador + responsável, nunca o autor),
  aprovada e reprovada; ignora usuário inativo e o próprio ator da ação
- Novos comandos agendados: `tarefas:notificar-prazos-proximos` (08:00)
  e `tarefas:notificar-atrasadas` (08:10) em `routes/console.php`;
  respeitam status bloqueada/concluída/cancelada e fuso do responsável

### Bug latente resolvido: eventos executados em duplicidade
- O Laravel 12 faz event discovery automático dos métodos `handle*` em
  `app/Listeners`, somando-se aos registros manuais no
  `AppServiceProvider`: cada evento rodava 2x desde o início — inclusive
  em produção, gravando histórico duplicado em `task_history_events`
  (testes antigos usavam `assertDatabaseHas` e não detectavam)
- Corrigido com `->withEvents(discover: false)` em `bootstrap/app.php`,
  mantendo os registros explícitos como fonte única

### Testes
- Novo arquivo `tests/Feature/Fase11CorrecoesCriticasTest.php` com
  17 testes de regressão (IDOR, rotas, notificações, comandos agendados)
- Suíte completa: 82 testes passando (antes: 65)

### Documentação
- `docs/23` → Cron do scheduler marcado como obrigatório para as
  notificações de prazo
- `docs/30` → novos diagnósticos: notificações de prazo sem chegar;
  histórico/eventos duplicados

### Arquivos principais afetados
- `app/Http/Controllers/TaskController.php`,
  `app/Listeners/NotificarPartesInteressadasListener.php` (novo),
  `app/Console/Commands/NotificarPrazosProximos.php` (novo),
  `app/Console/Commands/NotificarTarefasAtrasadas.php` (novo),
  `app/Providers/AppServiceProvider.php`, `bootstrap/app.php`,
  `routes/console.php`, `resources/views/tasks/show.blade.php`,
  `tests/Feature/Fase11CorrecoesCriticasTest.php` (novo), `docs/23`,
  `docs/30`

---

## [2026-08-07] — Fases 1-11 — Sistema completo de gestão de tarefas

### Infraestrutura
- Migrations: users (atualizada), tasks, comments, attachments, task_history_events, change_requests, notifications
- Models: User (atualizado), Task, Comment, Attachment, TaskHistoryEvent, ChangeRequest
- APP_LOCALE configurado para pt_BR
- Pest PHP instalado para testes
- TaskFactory criada para testes

### Fase 1 — Identidade e acesso
- Login/logout com verificação de is_active
- Convite de liderado com token em password_reset_tokens (48h expiração)
- Definição de senha via convite
- Gestão de equipe: listar, convidar, ativar/desativar liderados
- Gates: view-task, manage-team, create-task, approve-task, reject-task

### Fase 2 — Criação rápida de tarefas
- Formulário otimizado para criação rápida (mobile-first)
- Campos: título, descrição, responsável (opcional), prioridade, prazo (data+hora)
- Tarefa sem responsável → status "nao_atribuida"
- original_due_at preservado como referência histórica

### Fase 3 — Ciclo de vida da tarefa
- Máquina de estados com 9 transições + cancelamento
- Estados: nao_atribuida → nova → recebida → em_andamento → aguardando_aprovacao → concluida
- Exceções: bloqueada, reprovada, cancelada
- Liderado solicita conclusão, gestor aprova/reprova
- Solicitação de alteração de prazo/prioridade com aprovação do gestor
- Soft delete para cancelamento

### Fase 4 — Comunicação da tarefa
- Thread de comentários por tarefa (imutáveis)
- Upload de anexos (imagem/PDF, até 10MB)
- Liderado só vê as próprias tarefas (retorna 403 para outras)

### Fase 5 — Aprovação e reprovação
- Aprovação: gestor aprova → status concluída, registra approved_by e completed_at
- Reprovação categorizada: nao_atende, escopo_mudou, info_incompleta, outro
- Apenas "nao_atende" conta para métricas de desempenho (RF-29)

### Fase 6 — Histórico e auditoria
- Tabela task_history_events (append-only, nunca update/delete)
- 14 eventos de negócio mapeados
- GravarHistoricoListener registra todos os eventos
- Linha do tempo visível no detalhe da tarefa

### Fase 7 — Painel do gestor
- Dashboard com 4 cards de métricas: atrasadas, urgentes, vencem hoje, aguardando aprovação
- Visão por pessoa: tarefas abertas e atrasadas por liderado
- Acesso restrito ao gestor (Gate manage-team)

### Fase 8 — Portal do liderado
- "O que eu preciso fazer agora?" agrupado em: Urgentes, Hoje, Próximas
- Visualização apenas das próprias tarefas

### Fase 9 — Notificações
- Notificações in-app (database): nova tarefa, aprovada, reprovada, prazo próximo, atrasada, comentário
- Central de notificações com marcação de lida/não lida
- Indicador visual no cabeçalho

### Fase 10 — Regras de bloqueio
- Liderado bloqueia tarefa com motivo e indicação de dependência
- Gestor ou dependente desbloqueia
- Tarefa bloqueada não conta como atrasada (isOverdue = false)
- Badge visual de bloqueio

### Fase 11 — Polimento e responsividade
- Layout mobile-first com menu inferior (mobile) e sidebar (desktop)
- Breakpoints: 360px, 768px, 1024px, 1280px, 1536px, 2560px
- Cards em mobile, tabela em desktop
- Design system aplicado (cores, tipografia, badges coloridos)
- 53 testes automatizados passando

### Arquivos principais
- `app/Actions/` (14 classes)
- `app/Events/` (14 eventos)
- `app/Listeners/GravarHistoricoListener.php`
- `app/Http/Controllers/` (9 controllers)
- `app/Models/` (6 models)
- `app/Notifications/` (6 notificações)
- `app/Providers/AppServiceProvider.php`
- `database/migrations/` (9 migrations)
- `database/factories/` (UserFactory, TaskFactory)
- `resources/views/` (10 views + layout)
- `routes/web.php` (24 rotas)
- `tests/Feature/` (11 arquivos de teste, 53 testes)

---

## [2026-08-08] — Deploy em produção + modelo oficial de deploy

### Incidente resolvido (site retornava 404 em /login e depois 500 geral)
Causas raiz identificadas em diagnóstico (sem acesso SSH/API — via
Terminal do cPanel e GitHub API):
- Site servia rotas do skeleton: cache de rotas antigo nunca regenerado
- Docroot real é o caminho duplicado
  `/home/medicalthermo/home/medicalthermo/tarefas.medicalthermo.com`
  (a pasta sem prefixo era cópia secundária — deploys rodavam nela por engano)
- Webhook de deploy inviável: `exec()` desabilitado no PHP web
  (6 entregas do GitHub, 6 erros 500)
- `.htaccess` sem `AddHandler ...ea-php83`: conta cai no PHP 8.2 e o
  Laravel 12 morre em `ReflectionFunction::isAnonymous()`
- Drift de schema: migration `create_users_table` foi editada após rodar
  em produção — bancos sem 6 colunas (role, timezone, invited_at,
  activated_at, is_active, deleted_at)

### Correções
- `public/.htaccess`: AddHandler PHP 8.3 restaurado e **versionado**
- `public/deploy-webhook.php` **removido** (endpoint morto)
- Nova migration `2026_08_08_120000_add_missing_columns_to_users_table`
  (idempotente — adiciona cada coluna só se ausente)
- Banco de dados correto copiado para a pasta ativa; usuário gestor criado
- `.cpanel.yml` aponta para o caminho real

### Deploy contínuo (validado ao vivo: 2 pushes publicados sem tocar no servidor)
- `deploy/auto-deploy.sh` + cron de 1 min no cPanel: se `origin/main`
  mudou, faz pull + `publicar.sh` (shell puro, sem `exec()`)
- Webhook do GitHub removido

### Documentação
- `docs/32-MODELO-OFICIAL-DE-DEPLOY.md` — padrão para todos os projetos
- `docs/23` e `docs/24` corrigidos (LiteSpeed, caminho real, restrições)

### Arquivos principais afetados
- `public/.htaccess`, `deploy/auto-deploy.sh` (novo), `.cpanel.yml`
- `database/migrations/2026_08_08_120000_add_missing_columns_to_users_table.php`
- `docs/32-MODELO-OFICIAL-DE-DEPLOY.md` (novo), `docs/23`, `docs/24`
