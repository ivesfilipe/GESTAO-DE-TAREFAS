# Changelog

Formato de cada entrada:
```
## [data] — [Fase X] — [resumo curto]
- O que foi adicionado/alterado
- Arquivos principais afetados
```

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
