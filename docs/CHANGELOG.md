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
