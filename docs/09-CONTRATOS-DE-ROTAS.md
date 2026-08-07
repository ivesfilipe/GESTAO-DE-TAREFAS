# 09 — Contratos de Rotas

> Nomes de rotas devem seguir exatamente este documento. Se uma fase
> exigir rota nova, adicionar aqui antes de implementar.

## Autenticação
| Método | Rota | Acesso | Descrição |
|---|---|---|---|
| GET | /login | público | tela de login |
| POST | /login | público | autentica usuário |
| POST | /logout | autenticado | encerra sessão |
| GET | /convite/{token} | público | tela de definição de senha via convite |
| POST | /convite/{token} | público | define senha e ativa conta |

## Usuários (somente Gestor)
| Método | Rota | Acesso | Descrição |
|---|---|---|---|
| GET | /equipe | gestor | lista de liderados |
| POST | /equipe | gestor | convida novo liderado |
| PATCH | /equipe/{user} | gestor | desativa/reativa usuário |

## Tarefas
| Método | Rota | Acesso | Descrição |
|---|---|---|---|
| GET | /tarefas | gestor + liderado (filtrado) | lista de tarefas |
| GET | /tarefas/nova | gestor | formulário de criação rápida |
| POST | /tarefas | gestor | cria tarefa |
| GET | /tarefas/{task} | gestor + liderado (se responsável) | detalhe da tarefa |
| PATCH | /tarefas/{task}/atribuir | gestor | define/altera responsável |
| PATCH | /tarefas/{task}/status | gestor + liderado (regras por status) | muda status |
| POST | /tarefas/{task}/comentarios | gestor + liderado (se responsável) | adiciona comentário |
| POST | /tarefas/{task}/anexos | gestor + liderado (se responsável) | envia anexo |
| POST | /tarefas/{task}/bloquear | liderado (se responsável) | marca como bloqueada |
| POST | /tarefas/{task}/desbloquear | gestor ou quem foi apontado | desbloqueia |
| POST | /tarefas/{task}/solicitar-conclusao | liderado (se responsável) | → aguardando aprovação |
| POST | /tarefas/{task}/aprovar | gestor | → concluída |
| POST | /tarefas/{task}/reprovar | gestor | → reprovada, com motivo categorizado |
| POST | /tarefas/{task}/solicitar-alteracao | liderado (se responsável) | cria change_request |
| PATCH | /tarefas/{task}/alteracoes/{change_request} | gestor | aprova/recusa solicitação |

## Dashboard e portal
| Método | Rota | Acesso | Descrição |
|---|---|---|---|
| GET | /painel | gestor | dashboard do gestor |
| GET | /minhas-tarefas | liderado | portal "o que eu preciso fazer agora" |

## Notificações
| Método | Rota | Acesso | Descrição |
|---|---|---|---|
| GET | /notificacoes | autenticado | lista de notificações do usuário |
| PATCH | /notificacoes/{id}/lida | autenticado | marca como lida |

## Regras gerais de contrato
- Toda rota autenticada retorna 403 (não 404) quando o usuário não tem
  permissão sobre o recurso — nunca vazar existência de um recurso a
  quem não deveria vê-lo.
- Toda mutação de status de tarefa passa obrigatoriamente por uma
  Action dedicada (nunca update direto de coluna via controller).
- Erros de validação retornam mensagens em português, claras, associadas
  ao campo específico.

