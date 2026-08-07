# 10 — Eventos Internos (Event-Driven)

> Módulos futuros (CRM, financeiro, etc.) devem se conectar apenas
> escutando estes eventos — nunca escrevendo direto nas tabelas do
> módulo de Tarefas.

## Lista de eventos

| Evento | Disparado quando | Listeners nesta V1 |
|---|---|---|
| `TarefaCriada` | tarefa é salva pela primeira vez | GravarHistoricoListener, NotificarResponsavelListener (se atribuída) |
| `TarefaAtribuida` | responsável definido/alterado | GravarHistoricoListener, NotificarResponsavelListener |
| `PrioridadeAlterada` | prioridade muda | GravarHistoricoListener |
| `PrazoAlterado` | due_at muda | GravarHistoricoListener, NotificarResponsavelListener |
| `StatusAlterado` | qualquer mudança de status | GravarHistoricoListener |
| `ComentarioAdicionado` | novo comentário | GravarHistoricoListener, NotificarInteressadosListener |
| `AnexoAdicionado` | novo anexo | GravarHistoricoListener |
| `TarefaBloqueada` | status → bloqueada | GravarHistoricoListener, NotificarGestorListener |
| `TarefaDesbloqueada` | status sai de bloqueada | GravarHistoricoListener |
| `ConclusaoSolicitada` | liderado solicita conclusão | GravarHistoricoListener, NotificarGestorListener |
| `TarefaAprovada` | gestor aprova | GravarHistoricoListener, NotificarResponsavelListener, AtualizarMetricasListener |
| `TarefaReprovada` | gestor reprova | GravarHistoricoListener, NotificarResponsavelListener, AtualizarMetricasListener (se categoria = nao_atende) |
| `TarefaCancelada` | soft delete da tarefa | GravarHistoricoListener |
| `AlteracaoSolicitada` | liderado pede mudança de prazo/prioridade | GravarHistoricoListener, NotificarGestorListener |
| `PrazoProximo` | 2h antes do due_at (via scheduler) | NotificarResponsavelListener |
| `TarefaAtrasada` | due_at ultrapassado (via scheduler) | NotificarResponsavelListener |
| `AprovacaoParada` | 3+ dias em aguardando_aprovacao (via scheduler) | NotificarGestorListener |
| `CriticaSemMovimento` | 4h+ sem mudança de status em tarefa crítica (via scheduler) | NotificarGestorListener |

## Regra de listener universal
Todo evento relacionado a `Task` deve, no mínimo, acionar o
`GravarHistoricoListener`, que grava um `task_history_events`
correspondente. Nenhum evento de negócio pode existir sem gerar rastro
de histórico.

## Extensibilidade futura
`NotificarResponsavelListener` e `NotificarGestorListener` hoje
disparam apenas notificação in-app. Ao adicionar canal e-mail/WhatsApp
no futuro, a extensão deve ocorrer dentro da classe de Notification do
Laravel (método `via()`), sem alterar o disparo do evento em si.

