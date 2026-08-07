# 00 — Contexto do Projeto

## Nome
Sistema de Gestão de Tarefas para Liderados

## Objetivo central
Garantir que nenhuma solicitação feita por um gestor a um liderado seja
esquecida ou perdida. O fluxo essencial que o sistema resolve é:

> Solicitação → responsável → execução → resposta → aprovação → histórico

## Princípio norteador do produto
Toda decisão de design, dado ou código deve ser avaliada contra este
princípio:

> "Uma solicitação feita a um liderado não pode depender da memória de
> quem pediu nem da memória de quem recebeu. O sistema existe para
> transformar uma orientação verbal ou uma lembrança do gestor em uma
> responsabilidade registrada, atribuída, acompanhada e validada até sua
> conclusão."

## Problema que o sistema resolve
Hoje, solicitações do gestor aos liderados se perdem entre WhatsApp,
conversa presencial, e-mail e memória. Não existe um único lugar que
registre quem pediu o quê, para quem, com que prazo, e se foi
efetivamente concluído e aprovado.

## Escopo desta primeira versão (V1)
- 1 gestor + 4 liderados (arquitetura deve permitir crescer esse número
  e, no futuro, suportar hierarquias de mais de 2 níveis sem reescrever
  o núcleo)
- Web, mobile-first, funcionando bem em celular, tablet, notebook,
  ultrawide e TV de escritório

## Fora de escopo desta V1 (não construir, mesmo que pareça simples)
CRM, financeiro, projetos complexos, Kanban sofisticado, Gantt, controle
de horas, IA complexa, dezenas de relatórios, workflows configuráveis,
integrações extensas.

Ver detalhamento em `28-BACKLOG-E-FORA-DE-ESCOPO.md`.

## Stakeholders
| Papel | Responsabilidade |
|---|---|
| Gestor (operador do projeto) | Dono do produto, aprova entregas, prioriza fases |
| Liderados (4 usuários iniciais) | Usuários finais operacionais do sistema |
| Agente de desenvolvimento (IA) | Constrói o sistema seguindo esta documentação |

## Documento vivo relacionado
O estado atual da construção (o que já foi feito, o que falta) está
sempre em `STATUS-DO-PROJETO.md` — esse é o primeiro arquivo que
qualquer pessoa ou agente deve ler antes de tocar em código.

