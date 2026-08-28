# 16 — Modelo de Permissões e Papéis

## Papéis
- **Gestor**: acesso total às tarefas da própria equipe
- **Liderado**: acesso restrito às próprias tarefas

## Matriz de permissões

| Ação | Gestor | Liderado (tarefa própria) | Liderado (tarefa de outro) |
|---|---|---|---|
| Ver lista de tarefas | Somente a própria equipe | Somente as suas | Nenhuma |
| Criar tarefa | Sim | Não | Não |
| Definir/alterar responsável | Sim | Não | Não |
| Alterar prazo/prioridade diretamente | Sim | Não | Não |
| Solicitar alteração de prazo/prioridade | — | Sim | Não |
| Comentar | Sim | Sim | Não |
| Anexar arquivo | Sim | Sim | Não |
| Marcar como bloqueada | Não (só o liderado bloqueia) | Sim | Não |
| Desbloquear | Sim | Somente se apontado como dependência | Não |
| Solicitar conclusão | Não | Sim | Não |
| Aprovar/Reprovar | Sim | Não | Não |
| Cancelar tarefa | Sim | Não | Não |
| Ver histórico completo | Sim (própria equipe) | Sim (só das próprias) | Não |
| Convidar/desativar usuário | Sim | Não | Não |
| Ver dashboard consolidado | Sim | Não | Não |
| Ver perfil/documentos/chunks de liderado | Somente da própria equipe | Não | Não |
| Usar Copiloto/Radar/Delegação IA | Sim, em modo read-only | Não | Não |

## Implementação técnica
- Usar Laravel Policies para cada Model (`TaskPolicy`,
  `UserPolicy`) — nunca checar permissão diretamente no Controller
  com `if` solto
- Toda rota autenticada usa `authorize()` no início da Action/Controller
- Testes automatizados devem cobrir explicitamente: "liderado não
  consegue acessar tarefa de outro liderado" (ver
  `21-CASOS-DE-TESTE-CRITICOS.md`)

## Princípio geral
Autorização é sempre verificada no backend, nunca apenas escondendo
botões no frontend — esconder um botão não impede uma requisição
 manual à rota.

## Isolamento de equipe e IA
- `users.manager_id` é a fonte de escopo para liderados. Em uma base legada com exatamente um gestor ativo, a migration associa automaticamente os liderados existentes; com mais gestores, a associação deve ser explícita.
- Consultas de dashboard, relatórios, radar, perfil, API e tools do Copiloto usam o escopo do gestor autenticado.
- O Copiloto só possui tools de leitura. Criar, atribuir, aprovar, reprovar ou enviar mensagens sempre exige ação humana em rota protegida.
- Documentos e chunks de liderados não são expostos publicamente e só podem ser recuperados pelo gestor responsável.
