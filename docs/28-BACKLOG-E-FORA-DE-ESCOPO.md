# 28 — Backlog Futuro e Fora de Escopo

## Explicitamente fora de escopo da V1 (não construir, mesmo que pareça simples)
- CRM
- Módulo financeiro
- Gestão de projetos complexos
- Kanban visual sofisticado (drag-and-drop entre colunas)
- Gráfico de Gantt
- Controle de horas trabalhadas
- IA complexa (sugestões automáticas, priorização por IA, etc.)
- Dezenas de relatórios customizáveis
- Workflows configuráveis pelo usuário
- Integrações extensas com sistemas de terceiros

## Backlog para versões futuras (ordem não definida, avaliar por demanda real)
- Notificações por e-mail e/ou WhatsApp (arquitetura já preparada, ver
  `10-EVENTOS-INTERNOS.md` e `11-INTEGRACOES-EXTERNAS.md`)
- Hierarquia de mais de 2 níveis (ex: coordenador entre gestor e
  liderados) — modelo de dados já não bloqueia isso
  (ver `07-MODELO-DE-DOMINIO.md`)
- Suporte a múltiplas equipes/times dentro da mesma organização
- Ambiente de homologação separado, se a equipe crescer
- Migração de storage local de anexos para storage externo, se o disco
  se aproximar do limite
- App mobile nativo (hoje resolvido via responsividade web)
- Relatórios de desempenho mais elaborados (além da métrica simples de
  reprovação por "não atende")

## Regra de governança do escopo
Qualquer pedido de funcionalidade nova durante a construção da V1 deve
ser primeiro confrontado com esta lista. Se estiver aqui como "fora de
escopo", o agente deve sinalizar isso ao operador humano antes de
implementar, em vez de simplesmente atender ao pedido — para preservar
a decisão consciente de manter a V1 enxuta.

