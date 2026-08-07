# 08 — Modelo de Dados (Dicionário de Dados + ER)

> Este é o documento de maior autoridade sobre nomes de tabelas e
> colunas. Nenhuma fase deve criar campo com nome diferente do definido
> aqui. Se uma fase precisar de um campo novo não previsto, o agente
> deve primeiro ATUALIZAR este documento e só então criar a migration.

## Diagrama ER (texto)

```
users ──┬───< tasks (created_by)
        ├───< tasks (assigned_to)
        ├───< comments (author_id)
        ├───< attachments (uploaded_by)
        ├───< task_history_events (actor_id)
        ├───< change_requests (requested_by)
        └───< notifications (user_id)

tasks ──┬───< comments
        ├───< attachments
        ├───< task_history_events
        └───< change_requests

comments ───< attachments (comment_id, opcional)
```

## Tabela: `users`
| Coluna | Tipo | Regras |
|---|---|---|
| id | bigint unsigned, PK | auto increment |
| name | varchar(255) | obrigatório |
| email | varchar(255) | obrigatório, único |
| password | varchar(255) | obrigatório, hash |
| role | enum('gestor','liderado') | obrigatório |
| timezone | varchar(64) | obrigatório, default 'America/Sao_Paulo' |
| invited_at | timestamp | nulo até convite enviado |
| activated_at | timestamp | nulo até definir senha |
| is_active | boolean | default true |
| created_at / updated_at | timestamp | padrão Laravel |
| deleted_at | timestamp | soft delete — nunca excluir fisicamente |

## Tabela: `tasks`
| Coluna | Tipo | Regras |
|---|---|---|
| id | bigint unsigned, PK | auto increment |
| title | varchar(255) | obrigatório |
| description | text | opcional |
| created_by | bigint unsigned, FK → users.id | obrigatório |
| assigned_to | bigint unsigned, FK → users.id | nulo = "Não atribuída" |
| priority | enum('normal','importante','urgente','critica') | obrigatório, default 'normal' |
| status | enum('nao_atribuida','nova','recebida','em_andamento','aguardando_aprovacao','concluida','bloqueada','reprovada','cancelada') | obrigatório, default 'nao_atribuida' ou 'nova' |
| due_at | datetime | obrigatório (data + hora) |
| original_due_at | datetime | preenchido na criação, nunca alterado (referência histórica) |
| completed_at | datetime | nulo até aprovação final |
| approved_by | bigint unsigned, FK → users.id | nulo até aprovação |
| block_reason | text | nulo, obrigatório se status = bloqueada |
| blocked_on | varchar(255) | nulo, "de quem/o que depende" |
| rejection_category | enum('nao_atende','escopo_mudou','info_incompleta','outro') | nulo, obrigatório se reprovada |
| rejection_note | text | nulo, obrigatório se reprovada |
| created_at / updated_at | timestamp | padrão Laravel |
| deleted_at | timestamp | soft delete (usado para "Cancelada") |

## Tabela: `comments`
| Coluna | Tipo | Regras |
|---|---|---|
| id | bigint unsigned, PK | auto increment |
| task_id | bigint unsigned, FK → tasks.id | obrigatório |
| author_id | bigint unsigned, FK → users.id | obrigatório |
| body | text | obrigatório |
| created_at | timestamp | imutável — sem updated_at editável (RN-08) |

> Comentários não possuem `updated_at` funcional de edição — o campo
> existe por padrão do Laravel mas a aplicação NUNCA deve permitir
> update de `body` após criação.

## Tabela: `attachments`
| Coluna | Tipo | Regras |
|---|---|---|
| id | bigint unsigned, PK | auto increment |
| task_id | bigint unsigned, FK → tasks.id | obrigatório |
| comment_id | bigint unsigned, FK → comments.id | nulo (anexo pode ser direto na tarefa) |
| uploaded_by | bigint unsigned, FK → users.id | obrigatório |
| file_path | varchar(255) | obrigatório, caminho no storage |
| file_name | varchar(255) | obrigatório, nome original |
| file_type | varchar(50) | obrigatório (mime type) |
| file_size | integer | obrigatório, em bytes, máx. 10485760 (10MB) |
| created_at | timestamp | padrão Laravel |
| deleted_at | timestamp | soft delete |

## Tabela: `task_history_events` (event log imutável)
| Coluna | Tipo | Regras |
|---|---|---|
| id | bigint unsigned, PK | auto increment |
| task_id | bigint unsigned, FK → tasks.id | obrigatório |
| actor_id | bigint unsigned, FK → users.id | obrigatório (quem gerou o evento) |
| event_type | varchar(50) | obrigatório (ex: created, assigned, priority_changed, due_date_changed, status_changed, comment_added, attachment_added, blocked, unblocked, rejected, approved, cancelled) |
| payload | json | dados do evento (valor anterior, valor novo, motivo, etc.) |
| created_at | timestamp | imutável, sem updated_at |

> Esta tabela NUNCA recebe UPDATE ou DELETE. É append-only por design.

## Tabela: `change_requests`
| Coluna | Tipo | Regras |
|---|---|---|
| id | bigint unsigned, PK | auto increment |
| task_id | bigint unsigned, FK → tasks.id | obrigatório |
| requested_by | bigint unsigned, FK → users.id | obrigatório (sempre o liderado) |
| field | enum('due_at','priority') | obrigatório |
| current_value | varchar(255) | obrigatório |
| requested_value | varchar(255) | obrigatório |
| justification | text | obrigatório |
| status | enum('pendente','aprovada','recusada') | default 'pendente' |
| resolved_by | bigint unsigned, FK → users.id | nulo até resolução |
| resolved_at | timestamp | nulo até resolução |
| created_at / updated_at | timestamp | padrão Laravel |

## Tabela: `notifications`
Usar a tabela padrão de notifications do Laravel
(`php artisan notifications:table`), com campos padrão: id (uuid),
type, notifiable_type, notifiable_id, data (json), read_at, created_at,
updated_at.

## Índices obrigatórios
- `tasks.assigned_to` + `tasks.status` (consulta de dashboard por pessoa)
- `tasks.due_at` (consulta de atrasadas/vence hoje)
- `task_history_events.task_id` (montagem do histórico da tarefa)
- `comments.task_id`
- `attachments.task_id`

## Regras de integridade
- Toda FK usa `onDelete('restrict')` — nunca cascade automático, para
  não apagar histórico acidentalmente por engano em cascata.
- Todo soft delete usa o trait `SoftDeletes` do Eloquent.

