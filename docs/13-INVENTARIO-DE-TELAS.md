# 13 — Inventário de Telas (por Fase)

## Fase 1 — Identidade e acesso
- **Login** — campos: e-mail, senha. Ação: entrar.
- **Definição de senha via convite** — campos: nova senha, confirmação.
- **Gestão de equipe** (gestor) — lista de liderados, botão "convidar",
  ação de desativar/reativar.

## Fase 2 — Criação de tarefas
- **Nova tarefa** (gestor) — campos: título/descrição, responsável
  (opcional), prioridade, prazo (data+hora). Otimizada para 2 toques.

## Fase 3 — Ciclo de vida
- **Lista de tarefas** (gestor, com filtro por status/pessoa/prioridade)
- **Detalhe da tarefa** — mostra todos os campos, histórico resumido,
  ações disponíveis conforme papel e status atual

## Fase 4 — Comunicação
- **Thread de comentários** (dentro do detalhe da tarefa)
- **Upload de anexo** (dentro do detalhe da tarefa e da thread)

## Fase 5 — Aprovação
- **Modal/tela de aprovação** — confirmação simples
- **Modal/tela de reprovação** — seleção de categoria + campo de motivo

## Fase 6 — Histórico
- **Linha do tempo completa da tarefa** (dentro do detalhe, expandível)

## Fase 7 — Dashboard do gestor
- **Painel do gestor** — contadores (atrasadas, urgentes, vencem hoje,
  aguardando aprovação) + tabela por pessoa (aberta/atrasada)
- **Modo exibição ampliada** (variante para TV/ultrawide)

## Fase 8 — Portal do liderado
- **Minhas tarefas** — agrupamento Urgentes / Hoje / Próximas

## Fase 9 — Notificações
- **Central de notificações** — lista com marcação de lida/não lida
- **Indicador de notificação não lida** (ícone no cabeçalho, todas as
  telas)

## Fase 10 — Bloqueio
- **Modal de bloqueio** — motivo + de quem depende
- **Indicador visual de bloqueio** (badge na lista e no detalhe)

## Telas transversais (existem desde a Fase 1, evoluem com o sistema)
- **Cabeçalho/navegação** — logo, nome do usuário, ícone de
  notificações, botão sair
- **Estado vazio** — usado em qualquer lista sem itens
- **Estado de erro genérico** (ex: 403, 404, 500)

