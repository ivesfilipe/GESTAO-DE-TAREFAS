#!/bin/zsh
# Script gerado automaticamente — cria toda a estrutura docs/ e deploy/
# do Sistema de Gestao de Tarefas para Liderados, com os 37 arquivos
# ja preenchidos. Rodar dentro da pasta raiz do projeto no terminal do Antigravity.
set -e

mkdir -p docs deploy

echo 'Criando docs/00-CONTEXTO-DO-PROJETO.md ...'
cat > "docs/00-CONTEXTO-DO-PROJETO.md" <<'PROJDOC_EOF'
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

PROJDOC_EOF

echo 'Criando docs/01-REQUISITOS-FUNCIONAIS.md ...'
cat > "docs/01-REQUISITOS-FUNCIONAIS.md" <<'PROJDOC_EOF'
# 01 — Requisitos Funcionais

Cada requisito tem um identificador (RF-XX) usado em testes, commits e
no dicionário de dados para rastreabilidade.

## Identidade e Acesso
- **RF-01**: Gestor cadastra liderado via e-mail; sistema envia convite
  com link de definição de senha. Usuário nunca se autocadastra.
  - Critério de aceite: liderado recebe e-mail, define senha, consegue
    logar; link expira após uso ou após 48h.
- **RF-02**: Sistema possui dois perfis: Gestor e Liderado, com
  permissões distintas (ver `16-SEGURANCA-PERMISSOES.md`).
- **RF-03**: Usuário nunca é deletado fisicamente, apenas desativado
  (soft delete). Tarefas abertas de um usuário desativado devem ser
  sinalizadas ao gestor para reatribuição.

## Criação de Tarefas
- **RF-04**: Gestor cria tarefa em poucos segundos com campos mínimos:
  título/descrição, responsável (opcional no momento da criação),
  prioridade, prazo (data E hora).
  - Critério de aceite: criação completa em no máximo 2 toques a partir
    da tela inicial no celular.
- **RF-05**: Prioridades fixas e limitadas a 4 níveis: Normal,
  Importante, Urgente, Crítica.
- **RF-06**: Tarefa sem responsável definido entra no status
  "Não atribuída" e não conta como carga de trabalho de ninguém.

## Ciclo de Vida da Tarefa
- **RF-07**: Status principal segue a sequência: Não atribuída → Nova →
  Recebida → Em andamento → Aguardando aprovação → Concluída.
- **RF-08**: Estados de exceção: Bloqueada, Reprovada, Cancelada.
- **RF-09**: Liderado nunca encerra definitivamente uma tarefa sozinho —
  ele solicita conclusão, o gestor aprova ou reprova.
- **RF-10**: Cancelamento de tarefa é sempre soft delete; a tarefa
  permanece no histórico, some apenas das listas ativas.
- **RF-11**: Apenas o gestor altera prazo/prioridade diretamente. O
  liderado pode solicitar mudança, com justificativa obrigatória,
  sujeita à aprovação do gestor.

## Comunicação
- **RF-12**: Cada tarefa possui uma thread de comentários própria.
- **RF-13**: Comentários não podem ser editados nem apagados após
  enviados. Correções exigem novo comentário.
- **RF-14**: É possível anexar arquivos (imagem ou PDF, até 10MB por
  arquivo) a uma tarefa ou comentário.

## Aprovação e Reprovação
- **RF-15**: Liderado solicita conclusão → status muda para
  "Aguardando aprovação".
- **RF-16**: Gestor aprova (→ Concluída) ou reprova com motivo
  categorizado obrigatório: "não atende ao solicitado" | "escopo
  mudou" | "informação incompleta" | "outro".
- **RF-17**: Se uma tarefa fica em "Aguardando aprovação" por mais de 3
  dias sem resposta do gestor, o sistema envia cobrança automática ao
  gestor (não muda o status sozinho).

## Bloqueio
- **RF-18**: Liderado pode marcar tarefa como "Bloqueada", com motivo
  obrigatório e indicação de quem/o que ela depende.
- **RF-19**: Tempo bloqueado não conta como atraso do liderado
  responsável, mas permanece visível ao gestor no painel.

## Histórico e Auditoria
- **RF-20**: Todo evento relevante da tarefa gera um registro imutável
  de histórico: criação, atribuição, alteração de prazo/prioridade,
  comentários, anexos, mudanças de status, conclusão, aprovador, motivo
  de reprovação.

## Painel do Gestor
- **RF-21**: Dashboard exibe: total de atrasadas, urgentes, que vencem
  hoje, aguardando aprovação do gestor.
- **RF-22**: Dashboard exibe visão por pessoa: tarefas abertas e
  atrasadas por liderado.
- **RF-23**: Uma tarefa é considerada "atrasada" automaticamente quando
  passa da data/hora do prazo, calculada no fuso horário do liderado
  responsável.
- **RF-24**: Sistema notifica o liderado 2h antes do vencimento do
  prazo.

## Portal do Liderado
- **RF-25**: Tela inicial do liderado responde "o que eu preciso fazer
  agora?", agrupando tarefas em: Urgentes, Hoje, Próximas.
- **RF-26**: Liderado só visualiza as próprias tarefas — nunca as de
  outro liderado.

## Notificações
- **RF-27**: Sistema notifica (in-app nesta V1) para: nova tarefa
  atribuída, tarefa marcada urgente/crítica, prazo próximo, tarefa
  atrasada, novo comentário, tarefa reprovada, tarefa aprovada.
- **RF-28**: Tarefa Crítica sem mudança de status por mais de 4h gera
  alerta automático ao gestor (não ao liderado).

## Métricas
- **RF-29**: Apenas reprovações categorizadas como "não atende ao
  solicitado" contam para métricas de desempenho do liderado.

PROJDOC_EOF

echo 'Criando docs/02-REQUISITOS-NAO-FUNCIONAIS.md ...'
cat > "docs/02-REQUISITOS-NAO-FUNCIONAIS.md" <<'PROJDOC_EOF'
# 02 — Requisitos Não-Funcionais

## Performance
- **RNF-01**: Tela inicial (login e "Minhas tarefas") deve carregar em
  menos de 2 segundos em conexão 4G comum.
- **RNF-02**: Criação de tarefa deve responder em menos de 1 segundo
  após envio do formulário.
- **RNF-03**: Sistema deve operar de forma estável em hospedagem
  compartilhada (recursos limitados de CPU/memória) — evitar consultas
  N+1, usar eager loading, e cache de configuração/rota em produção.

## Disponibilidade
- **RNF-04**: Sistema deve estar disponível durante o horário comercial
  (8h–19h, dias úteis) com tolerância mínima a indisponibilidade — é a
  janela em que gestor e liderados de fato o utilizam.
- **RNF-05**: Deploys devem ocorrer fora do horário de pico de uso
  sempre que possível.

## Escalabilidade
- **RNF-06**: Arquitetura deve suportar crescimento de 5 para até ~50
  usuários sem mudança estrutural (apenas ajuste de recursos de
  hospedagem).
- **RNF-07**: Modelo de dados deve prever hierarquia de mais de 2
  níveis (ex: coordenador entre gestor e liderados) sem necessidade de
  redesenho de tabelas.

## Usabilidade
- **RNF-08**: Toda ação principal (criar tarefa, aprovar, reprovar)
  deve ser alcançável em no máximo 2 toques a partir da tela inicial no
  celular.
- **RNF-09**: Sistema deve ser 100% responsivo — ver
  `15-RESPONSIVIDADE.md`.

## Confiabilidade dos dados
- **RNF-10**: Nenhum dado de histórico pode ser perdido ou sobrescrito
  sem deixar rastro (event log imutável).
- **RNF-11**: Nenhuma exclusão física de tarefa, comentário ou usuário —
  sempre soft delete.

## Segurança
- **RNF-12**: Ver `16, 17, 18, 19` — permissões, autenticação, checklist
  OWASP e gestão de segredos.

## Manutenibilidade
- **RNF-13**: Código deve seguir PSR-12 e convenções Laravel padrão
  (ver `04-PADROES-DE-CODIGO.md` do pacote de infraestrutura).
- **RNF-14**: Toda regra de negócio complexa deve viver em Service
  Classes/Actions, nunca direto no Controller.

## Compatibilidade
- **RNF-15**: Suportar as duas últimas versões estáveis de Chrome,
  Safari e Edge, em desktop e mobile.

PROJDOC_EOF

echo 'Criando docs/03-REGRAS-DE-NEGOCIO.md ...'
cat > "docs/03-REGRAS-DE-NEGOCIO.md" <<'PROJDOC_EOF'
# 03 — Regras de Negócio

Estas regras foram desenhadas para "blindar" tanto a empresa (gestor)
quanto o colaborador (liderado) — nenhuma cobrança automática é justa
sem registrar os fatores fora do controle do liderado, e nenhuma
alteração de prazo/prioridade pode ser feita unilateralmente pelo
liderado.

## RN-01 — Cálculo de atraso
Uma tarefa é "atrasada" quando a data/hora atual ultrapassa o campo
`prazo_em` da tarefa, calculado no fuso horário cadastrado do liderado
responsável (não do gestor). O sistema envia aviso automático 2h antes
do vencimento. Ao ultrapassar o prazo, o status derivado muda
automaticamente — nenhum humano decide isso manualmente.

## RN-02 — Alteração de prazo e prioridade
Somente o gestor pode alterar diretamente prazo ou prioridade de uma
tarefa. O liderado pode registrar uma "solicitação de alteração" com
justificativa obrigatória; essa solicitação gera um evento de histórico
e uma notificação ao gestor, que aprova ou recusa. Nenhuma alteração
ocorre sem essa aprovação.

## RN-03 — Tarefa sem responsável
Tarefas podem ser criadas sem responsável definido (status "Não
atribuída"). Enquanto nesse estado, não contam como carga de trabalho
de ninguém nem geram cobrança de prazo.

## RN-04 — Reprovação categorizada
Toda reprovação exige um motivo categorizado: "não atende ao
solicitado", "escopo mudou", "informação incompleta" ou "outro".
Apenas reprovações por "não atende ao solicitado" entram na métrica de
desempenho do liderado — isso evita penalizar o colaborador por
mudanças de ideia do próprio gestor.

## RN-05 — Bloqueio de tarefa
Ao marcar uma tarefa como bloqueada, o liderado é obrigado a informar o
motivo e de quem/o que ela depende (outro liderado, o próprio gestor,
ou um terceiro externo). O tempo em estado bloqueado não conta como
atraso do responsável, mas permanece visível e rastreável pelo gestor —
evitando tanto a penalização injusta quanto o uso do bloqueio como
desculpa oculta.

## RN-06 — Escalonamento por inação
Uma tarefa com prioridade Crítica que não mude de status por mais de 4h
gera alerta automático ao **gestor**, não ao liderado — a lógica é que
o acompanhamento de itens críticos é responsabilidade compartilhada, e
o painel do gestor já expõe isso.

## RN-07 — Cobrança de aprovação parada
Uma tarefa em "Aguardando aprovação" por mais de 3 dias sem resposta do
gestor gera notificação automática de cobrança ao gestor. O status não
muda sozinho — apenas o gestor decide.

## RN-08 — Integridade de comentários
Comentários são imutáveis após o envio (sem edição ou exclusão). Um
erro se corrige com um novo comentário, preservando a integridade do
histórico como prova de comunicação para ambos os lados.

## RN-09 — Visibilidade entre liderados
Um liderado nunca visualiza tarefas atribuídas a outro liderado. Apenas
o gestor tem visão consolidada de toda a equipe.

## RN-10 — Ciclo de conclusão
O liderado nunca marca uma tarefa como definitivamente concluída. Ele
"solicita conclusão" (→ Aguardando aprovação); apenas o gestor,
aprovando, move a tarefa para "Concluída". Se reprovada, a tarefa volta
ao liderado, mantendo o vínculo histórico da tentativa anterior.

## RN-11 — Retenção de usuários desligados
Usuários nunca são apagados fisicamente. Ao desativar um usuário, o
sistema deve alertar o gestor sobre tarefas abertas vinculadas a ele
para reatribuição manual — nada fica "órfão" silenciosamente.

## RN-12 — Nada é apagado de verdade
Tarefas canceladas, usuários desativados, nenhum registro sai fisicamente
do banco — tudo é soft delete, preservando o histórico completo para
consulta futura ("meses depois, será possível entender exatamente o que
aconteceu").

PROJDOC_EOF

echo 'Criando docs/04-PERSONAS-E-JORNADAS.md ...'
cat > "docs/04-PERSONAS-E-JORNADAS.md" <<'PROJDOC_EOF'
# 04 — Personas e Jornadas de Usuário

## Persona 1 — Gestor
Responsável por delegar, acompanhar e aprovar o trabalho da equipe.
Costuma lembrar de pendências em movimento (reunião, fábrica, viagem,
celular na mão) e precisa registrar isso antes de esquecer.

### Jornada principal — "Lembrei de algo, preciso delegar agora"
1. Abre o sistema no celular (app web responsivo).
2. Toca em "Nova tarefa".
3. Digita título/descrição curta.
4. Seleciona responsável (ou deixa em aberto).
5. Define prioridade e prazo (data + hora).
6. Salva — tarefa passa a existir e notificar o responsável.
   - Meta: concluir esse fluxo em até 2 toques além da digitação.

### Jornada secundária — "O que está pendente da minha aprovação?"
1. Abre o dashboard.
2. Vê contador "Aguardando minha aprovação".
3. Abre cada tarefa, lê a resposta do liderado e os anexos.
4. Aprova ou reprova (com motivo categorizado, se reprovar).

### Jornada terciária — "Quem está sobrecarregado ou atrasado?"
1. Abre o dashboard.
2. Consulta visão por pessoa (tarefas abertas/atrasadas por liderado).
3. Usa essa informação para redistribuir ou cobrar pessoalmente.

## Persona 2 — Liderado
Recebe tarefas do gestor, executa, e precisa deixar claro o andamento
sem depender de lembrar tudo de cabeça.

### Jornada principal — "O que eu preciso fazer agora?"
1. Abre o sistema (celular ou desktop).
2. Tela inicial já mostra: Urgentes, Hoje, Próximas.
3. Toca em uma tarefa para ver detalhes.
4. Registra andamento via comentário, anexando evidência se necessário.
5. Quando pronto, toca em "Solicitar conclusão".

### Jornada secundária — "Não consigo avançar nesta tarefa"
1. Abre a tarefa.
2. Marca como "Bloqueada".
3. Informa motivo e de quem depende.
4. Tarefa para de contar como atraso dele, mas gestor é avisado.

### Jornada terciária — "Fui reprovado, preciso corrigir"
1. Recebe notificação de reprovação com motivo.
2. Abre a tarefa, lê o comentário do gestor.
3. Corrige e solicita conclusão novamente.

## Mapa de emoções (para orientar tom de UI/mensagens)
- Gestor em movimento: precisa de **velocidade** e **confirmação
  visual imediata** de que a tarefa foi criada.
- Liderado recebendo tarefa nova: precisa de **clareza** — o que,
  quando, e qual o nível de urgência real.
- Liderado reprovado: momento sensível — mensagens devem ser objetivas
  e não punitivas, focadas no que precisa ser corrigido.

PROJDOC_EOF

echo 'Criando docs/05-ARQUITETURA-VISAO-GERAL.md ...'
cat > "docs/05-ARQUITETURA-VISAO-GERAL.md" <<'PROJDOC_EOF'
# 05 — Arquitetura, Visão Geral

## Diagrama de componentes (texto)

```
[ Navegador / Celular / Tablet / TV ]
              │
              ▼
    [ Laravel App (Blade + Livewire) ]
       │        │           │
       ▼        ▼           ▼
  [ Controllers ] [ Livewire ] [ Form Requests ]
       │
       ▼
  [ Service Classes / Actions ]  ← toda regra de negócio complexa
       │
       ▼
  [ Eloquent Models ]
       │
       ▼
  [ MySQL — medicalthermo_gestao_de_tarefas ]

  [ Laravel Queue (driver database) ] → processa notificações
  [ Laravel Scheduler ] → cron único → verifica atrasos, escalonamento,
                                        cobranças automáticas
  [ Laravel Notifications ] → canal in-app nesta V1 (extensível a
                                e-mail/WhatsApp depois)
```

## Camadas do sistema
1. **Apresentação** — Blade + Livewire + Tailwind CSS, renderização
   server-side com interatividade via Livewire (sem exigir API separada)
2. **Aplicação** — Controllers finos, Form Requests para validação,
   Service Classes/Actions para regra de negócio
3. **Domínio** — Eloquent Models representando as entidades (Tarefa,
   Usuário, Comentário, Anexo, HistoricoEvento)
4. **Persistência** — MySQL via Migrations versionadas
5. **Infraestrutura** — hospedagem compartilhada cPanel, deploy via Git

## Por que essa arquitetura (resumo — detalhe em ADRs)
- Servidor é hospedagem compartilhada: evitar dependências pesadas
  (sem Redis, sem containers, sem processos long-running fora do padrão
  PHP-FPM)
- Time não-programador: Laravel + Livewire reduz a necessidade de
  manter frontend e backend como projetos separados
- Crescimento futuro: arquitetura em camadas (Service/Action) permite
  extrair regras de negócio para uma API própria no futuro sem
  reescrever tudo

## Fluxo de uma solicitação típica (criação de tarefa)
1. Gestor preenche formulário (Livewire component)
2. Componente valida via Form Request
3. Action `CriarTarefaAction` executa a regra de negócio e grava via
   Eloquent
4. Evento `TarefaCriada` é disparado
5. Listener dispara notificação in-app ao responsável (se definido)
6. Registro de histórico é criado automaticamente (event log)

## Fronteiras entre módulos (para modularidade futura)
- `Identidade` — usuários, papéis, autenticação
- `Tarefas` — ciclo de vida, prioridade, prazo
- `Comunicação` — comentários, anexos
- `Notificações` — desacoplado, consome eventos dos outros módulos
- `Auditoria` — consome eventos, nunca é consumida por eles
- `Dashboard` — somente leitura dos outros módulos

Módulos futuros (CRM, financeiro, etc.) devem se conectar apenas por
eventos, nunca escrevendo diretamente nas tabelas de outro módulo.

PROJDOC_EOF

echo 'Criando docs/06-DECISOES-DE-ARQUITETURA-ADR.md ...'
cat > "docs/06-DECISOES-DE-ARQUITETURA-ADR.md" <<'PROJDOC_EOF'
# 06 — Decisões de Arquitetura (ADRs)

Cada decisão relevante deve ser registrada aqui no formato abaixo,
incluindo as tomadas durante a construção (o agente deve adicionar
novos ADRs conforme decide algo não previsto nesta documentação inicial).

---

## ADR-001 — Uso de Laravel como framework backend
**Status**: Aceito
**Contexto**: Hospedagem compartilhada cPanel, PHP 8.1 disponível,
equipe não-programadora dependente de agentes de IA para construção.
**Decisão**: Usar Laravel (versão estável mais recente compatível com
PHP 8.1).
**Motivo**: Framework mais maduro do ecossistema PHP, amplamente
treinado em modelos de IA (menor risco de código inconsistente),
resolve de fábrica autenticação, filas, agendamento e migrations.
**Alternativas descartadas**: PHP puro (mais controle, mas exige muito
mais código manual e maior risco de falha de segurança sem framework).

## ADR-002 — Uso de Livewire + Blade em vez de SPA com API separada
**Status**: Aceito
**Contexto**: Necessidade de interatividade (aprovação, comentários em
tempo real) sem exigir dois projetos separados (backend API + frontend
JS).
**Decisão**: Usar Livewire para interatividade dentro do próprio
Laravel.
**Motivo**: Reduz complexidade de deploy em hospedagem compartilhada,
mantém um único repositório e um único pipeline de deploy.
**Alternativas descartadas**: Next.js/React consumindo API Laravel
(mais flexível para app mobile nativo futuro, mas dobra a complexidade
de infraestrutura hoje).

## ADR-003 — Driver de fila "database" em vez de Redis
**Status**: Aceito
**Contexto**: Hospedagem compartilhada não garante Redis disponível.
**Decisão**: Usar driver de fila `database` do Laravel.
**Motivo**: Não exige serviço adicional, suficiente para o volume de 5
usuários iniciais.
**Revisão futura**: Se o volume de notificações crescer muito, avaliar
migração para Redis (exigiria upgrade de plano de hospedagem).

## ADR-004 — Agendamento via único Cron Job do cPanel
**Status**: Aceito
**Decisão**: Um único Cron Job aponta para `php artisan schedule:run`
a cada minuto; todo agendamento fino (verificação de atraso,
escalonamento, cobrança de aprovação) é definido dentro do Laravel
Scheduler, não como múltiplos cron jobs separados.
**Motivo**: cPanel compartilhado geralmente limita a quantidade de cron
jobs simultâneos; centralizar em um evita esse limite.

## ADR-005 — Soft delete universal
**Status**: Aceito
**Decisão**: Todas as tabelas de negócio usam soft delete
(`deleted_at`), nenhuma exclusão física.
**Motivo**: Requisito de negócio (RN-12) — histórico nunca pode
desaparecer.

## ADR-006 — Notificações in-app nesta V1, arquitetura extensível
**Status**: Aceito
**Decisão**: Implementar apenas canal in-app via Laravel Notifications
nesta fase, mas estruturar o código de forma que adicionar canal
e-mail/WhatsApp no futuro não exija alterar a lógica de disparo.
**Motivo**: Reduz escopo da V1 sem fechar porta de crescimento.

---

## Como adicionar um novo ADR
Ao tomar qualquer decisão técnica não coberta nesta documentação
durante a construção, o agente deve adicionar uma nova entrada aqui,
seguindo o mesmo formato (Status / Contexto / Decisão / Motivo /
Alternativas descartadas), e referenciar o número do ADR no commit
correspondente.

PROJDOC_EOF

echo 'Criando docs/07-MODELO-DE-DOMINIO.md ...'
cat > "docs/07-MODELO-DE-DOMINIO.md" <<'PROJDOC_EOF'
# 07 — Modelo de Domínio (Conceitual)

Antes do banco de dados: as entidades de negócio e como se relacionam
conceitualmente.

```
Organização (futuro — hoje implícita, única)
   │
   └── Usuário (Gestor | Liderado)
          │
          └── Tarefa
                ├── criada por: Usuário (Gestor)
                ├── atribuída a: Usuário (Liderado) [opcional no início]
                ├── possui: Prioridade
                ├── possui: Status (ciclo de vida)
                ├── possui muitos: Comentário
                │        └── possui muitos: Anexo
                ├── possui muitos: Anexo (direto na tarefa)
                ├── possui muitos: HistoricoEvento
                └── possui: SolicitacaoDeAlteracao [0..N]

Notificação
   ├── pertence a: Usuário (destinatário)
   └── refere-se a: Tarefa (opcional)
```

## Entidades e seus papéis
- **Usuário**: pessoa com acesso ao sistema; tem um papel (Gestor ou
  Liderado); possui fuso horário próprio (usado para cálculo de
  atraso).
- **Tarefa**: unidade central do sistema; carrega todo o ciclo de vida
  descrito em `03-REGRAS-DE-NEGOCIO.md`.
- **Comentário**: mensagem imutável vinculada a uma tarefa, com autor e
  timestamp.
- **Anexo**: arquivo vinculado a uma tarefa ou a um comentário
  específico.
- **HistoricoEvento**: registro imutável de qualquer mudança relevante
  na tarefa (event log) — nunca um campo sobrescrito sem rastro.
- **SolicitacaoDeAlteracao**: pedido do liderado para mudar prazo ou
  prioridade, sujeito à aprovação do gestor (RN-02).
- **Notificação**: mensagem in-app gerada por eventos do sistema.

## Multiplicidades-chave
- Um Usuário Gestor cria muitas Tarefas.
- Uma Tarefa é atribuída a no máximo um Usuário Liderado por vez.
- Uma Tarefa tem muitos Comentários, muitos Anexos, muitos
  HistoricoEventos.
- Um HistoricoEvento pertence a exatamente uma Tarefa e é imutável.

PROJDOC_EOF

echo 'Criando docs/08-MODELO-DE-DADOS.md ...'
cat > "docs/08-MODELO-DE-DADOS.md" <<'PROJDOC_EOF'
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

PROJDOC_EOF

echo 'Criando docs/09-CONTRATOS-DE-ROTAS.md ...'
cat > "docs/09-CONTRATOS-DE-ROTAS.md" <<'PROJDOC_EOF'
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

PROJDOC_EOF

echo 'Criando docs/10-EVENTOS-INTERNOS.md ...'
cat > "docs/10-EVENTOS-INTERNOS.md" <<'PROJDOC_EOF'
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

PROJDOC_EOF

echo 'Criando docs/11-INTEGRACOES-EXTERNAS.md ...'
cat > "docs/11-INTEGRACOES-EXTERNAS.md" <<'PROJDOC_EOF'
# 11 — Integrações Externas

## E-mail (convite de usuário e, futuramente, notificações)
- **Driver**: SMTP
- **Provedor**: usar o SMTP da própria hospedagem cPanel (conta de
  e-mail do domínio, ex: sistema@medicalthermo.com) nesta V1, para
  evitar dependência de serviço pago externo
- **Configuração**: via `.env` (`MAIL_MAILER=smtp`, `MAIL_HOST`,
  `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`) —
  credenciais obtidas em cPanel → "Email Accounts"
- **Uso nesta V1**: exclusivamente para envio do link de convite/
  definição de senha (RF-01). Notificações de tarefa permanecem in-app.
- **Revisão futura**: se o volume justificar, avaliar serviço dedicado
  (ex: um provedor transacional) para melhor taxa de entrega.

## Armazenamento de anexos
- **Driver nesta V1**: `local`, dentro de
  `storage/app/public/anexos`, com link simbólico público
  (`php artisan storage:link`)
- **Motivo**: hospedagem compartilhada não garante serviço de storage
  externo (S3, etc.) sem custo adicional; volume esperado (5 usuários)
  não justifica isso ainda
- **Limite**: 10MB por arquivo, tipos aceitos: imagem (jpg, png, webp)
  e PDF
- **Revisão futura**: se o disco da hospedagem (30GB conforme plano
  contratado) se aproximar do limite, migrar para storage externo —
  arquitetura do Laravel (`Storage::disk()`) já permite essa troca sem
  reescrever a lógica de upload.

## WhatsApp (fora de escopo da V1)
Mencionado como possível canal futuro de notificação. Não implementar
nesta V1. Quando avaliado, exigirá: conta comercial no WhatsApp
Business API ou serviço intermediário (ex: Twilio), e um novo canal de
Notification do Laravel — a arquitetura de eventos (ver documento 10)
já comporta essa adição sem alterar o núcleo.

## Nenhuma outra integração externa está prevista nesta V1
(sem CRM, sem financeiro, sem calendário externo — ver
`28-BACKLOG-E-FORA-DE-ESCOPO.md`).

PROJDOC_EOF

echo 'Criando docs/12-DESIGN-SYSTEM.md ...'
cat > "docs/12-DESIGN-SYSTEM.md" <<'PROJDOC_EOF'
# 12 — Sistema de Design (Design System)

## Tom visual
Corporativo, sóbrio e direto — o sistema é uma ferramenta de trabalho
usada rapidamente sob pressão (gestor em movimento, liderado consultando
entre tarefas). Prioridade: clareza sobre decoração.

## Paleta de cores

| Uso | Cor | Hex (base) |
|---|---|---|
| Primária (ações principais, links) | Azul corporativo | `#1E40AF` |
| Secundária (destaques neutros) | Cinza-azulado | `#475569` |
| Fundo geral | Cinza muito claro | `#F8FAFC` |
| Superfície (cards) | Branco | `#FFFFFF` |
| Prioridade Normal | Cinza | `#94A3B8` |
| Prioridade Importante | Azul | `#3B82F6` |
| Prioridade Urgente | Laranja | `#F97316` |
| Prioridade Crítica | Vermelho | `#DC2626` |
| Status Concluída | Verde | `#16A34A` |
| Status Atrasada/Reprovada | Vermelho | `#DC2626` |
| Status Bloqueada | Amarelo | `#CA8A04` |
| Texto principal | Quase preto | `#0F172A` |
| Texto secundário | Cinza médio | `#64748B` |

> Implementar como variáveis no `tailwind.config.js`, nunca usar cores
> soltas hardcoded nos componentes.

## Tipografia
- Fonte: system-ui (nativa, sem carregar fonte externa — reduz peso de
  carregamento em conexão móvel)
- Escala: text-sm (detalhes), text-base (corpo), text-lg (títulos de
  card), text-xl/2xl (títulos de página e números de dashboard)
- Em modo "exibição ampliada" (telas ultrawide/TV), escala sobe um
  degrau inteiro (ver `15-RESPONSIVIDADE.md`)

## Componentes reutilizáveis (Livewire/Blade)
- `<x-badge-status>` — badge colorido conforme tabela de status acima
- `<x-badge-prioridade>` — badge colorido conforme tabela de prioridade
- `<x-card-tarefa>` — card usado tanto em lista quanto em modo TV
- `<x-botao-primario>` / `<x-botao-secundario>` / `<x-botao-perigo>`
  (ex: reprovar, cancelar)
- `<x-timeline-historico>` — linha do tempo de eventos da tarefa
- `<x-avatar-usuario>` — iniciais do nome em círculo colorido
- `<x-empty-state>` — estado vazio ("nenhuma tarefa por aqui")

## Iconografia
Usar um único pacote de ícones (ex: Heroicons, nativo do ecossistema
Tailwind) — nunca misturar bibliotecas de ícones diferentes entre telas.

## Princípios de microcopy (textos da interface)
- Sempre em português claro, direto, sem jargão técnico
- Mensagens de erro dizem o que fazer, não só o que deu errado
- Nenhuma mensagem de reprovação deve soar punitiva — foco no que
  precisa ser corrigido

PROJDOC_EOF

echo 'Criando docs/13-INVENTARIO-DE-TELAS.md ...'
cat > "docs/13-INVENTARIO-DE-TELAS.md" <<'PROJDOC_EOF'
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

PROJDOC_EOF

echo 'Criando docs/14-FLUXOS-DE-NAVEGACAO.md ...'
cat > "docs/14-FLUXOS-DE-NAVEGACAO.md" <<'PROJDOC_EOF'
# 14 — Fluxos de Navegação

## Fluxo: Gestor cria tarefa rapidamente
```
Login → Painel do Gestor → [botão flutuante "+"] → Nova Tarefa
  → preenche campos → Salvar → volta para Painel (com confirmação
    visual breve, tipo toast) 
```

## Fluxo: Gestor aprova/reprova
```
Painel do Gestor → card "Aguardando minha aprovação" → Lista filtrada
  → Detalhe da Tarefa → botão Aprovar ou Reprovar
    → (se reprovar) Modal de categoria + motivo → Confirmar
  → volta para Lista filtrada (item sai da lista)
```

## Fluxo: Liderado executa e conclui tarefa
```
Login → Minhas Tarefas → toca no card → Detalhe da Tarefa
  → adiciona comentário/anexo (quantas vezes precisar)
  → botão "Solicitar conclusão" → Confirmar
  → volta para Minhas Tarefas (item some da lista ativa, some para
    Aguardando Aprovação)
```

## Fluxo: Liderado bloqueia tarefa
```
Detalhe da Tarefa → botão "Marcar como bloqueada"
  → Modal (motivo + de quem depende) → Confirmar
  → Detalhe da Tarefa atualizado com badge "Bloqueada"
```

## Fluxo: Liderado solicita mudança de prazo
```
Detalhe da Tarefa → botão "Solicitar novo prazo"
  → Modal (novo valor + justificativa) → Enviar
  → Notificação ao gestor → Gestor aprova/recusa na Detalhe da Tarefa
```

## Fluxo: Convite de novo liderado
```
Gestão de Equipe → botão "Convidar" → informa e-mail → Enviar
  → liderado recebe e-mail → abre link → Definição de Senha
  → login automático → Minhas Tarefas
```

## Regra geral de navegação
- Toda ação de mutação (aprovar, reprovar, bloquear, solicitar) deve
  retornar visualmente ao ponto de origem com confirmação clara —
  nunca deixar o usuário sem saber se a ação teve efeito.
- Nenhum fluxo crítico (aprovação, reprovação, bloqueio) deve ser uma
  ação de um único toque acidental — sempre um passo de confirmação
  intermediário (modal), exceto ações de baixo risco como "solicitar
  conclusão".

PROJDOC_EOF

echo 'Criando docs/15-RESPONSIVIDADE.md ...'
cat > "docs/15-RESPONSIVIDADE.md" <<'PROJDOC_EOF'
# 15 — Guia de Responsividade

O sistema deve funcionar perfeitamente em TODOS os tamanhos abaixo,
sem exceção, para toda funcionalidade construída em qualquer fase.

## Breakpoints de referência (Tailwind, mobile-first)
| Nome | Largura | Dispositivo típico |
|---|---|---|
| base (sem prefixo) | a partir de 360px | Celular |
| sm | 640px+ | Celular grande / phablet |
| md | 768px+ | Tablet |
| lg | 1024px+ | Notebook pequeno |
| xl | 1280px+ | Desktop padrão |
| 2xl | 1536px+ | Desktop grande |
| 2xl+ customizado | 2560px+ | Ultrawide / TV de escritório |

## Regras por breakpoint

**Celular (base a sm)**
- Navegação principal em menu inferior fixo ou hambúrguer
- Listas de tarefas em formato de card empilhado (nunca tabela com
  scroll horizontal)
- Botão de ação principal ("Nova tarefa") fixo e sempre visível
- Formulários em coluna única, campos grandes o suficiente para toque

**Tablet (md)**
- Listas podem adotar grid de 2 colunas de cards
- Modais ocupam ~80% da largura, não tela cheia

**Notebook/Desktop (lg a xl)**
- Listas de tarefas podem virar tabela real com colunas (status,
  prioridade, responsável, prazo)
- Dashboard do gestor em grid de múltiplos cartões lado a lado
- Barra lateral de navegação fixa (em vez de menu inferior)

**Ultrawide (2xl+)**
- Conteúdo principal usa `max-width` (ex: `max-w-7xl`) centralizado —
  nunca esticar tabelas/formulários por toda a largura da tela
- Dashboard pode aproveitar o espaço extra com mais cartões visíveis
  simultaneamente, não com cartões maiores e vazios

**TV de escritório / modo exibição**
- Dashboard do gestor deve ter uma variante de "modo exibição": fontes
  maiores, foco apenas nos números-chave (atrasadas, urgentes,
  aguardando aprovação, visão por pessoa), sem elementos de interação
  (sem hover, sem botões pequenos)
- Contraste alto, legível a distância de alguns metros
- Ativação desse modo: parâmetro de URL ou toggle simples, não requer
  tela dedicada nova

## Regras técnicas obrigatórias
- Toda tabela de dados tem sua versão em cards para telas pequenas —
  implementar como duas variantes do mesmo componente Blade, alternadas
  por breakpoint CSS, nunca JavaScript de detecção de dispositivo
- Testar manualmente (ou emulação) cada entrega de fase nos larguras:
  360px, 768px, 1366px, 1920px, 2560px
- Nenhum texto pode quebrar layout ou sair da tela em nenhum breakpoint
- Imagens/anexos em preview devem ser responsivos (max-width: 100%)

PROJDOC_EOF

echo 'Criando docs/16-SEGURANCA-PERMISSOES.md ...'
cat > "docs/16-SEGURANCA-PERMISSOES.md" <<'PROJDOC_EOF'
# 16 — Modelo de Permissões e Papéis

## Papéis
- **Gestor**: acesso total às tarefas da própria equipe
- **Liderado**: acesso restrito às próprias tarefas

## Matriz de permissões

| Ação | Gestor | Liderado (tarefa própria) | Liderado (tarefa de outro) |
|---|---|---|---|
| Ver lista de tarefas | Todas | Somente as suas | Nenhuma |
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
| Ver histórico completo | Sim (todas) | Sim (só das próprias) | Não |
| Convidar/desativar usuário | Sim | Não | Não |
| Ver dashboard consolidado | Sim | Não | Não |

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

PROJDOC_EOF

echo 'Criando docs/17-SEGURANCA-AUTENTICACAO.md ...'
cat > "docs/17-SEGURANCA-AUTENTICACAO.md" <<'PROJDOC_EOF'
# 17 — Política de Autenticação

## Mecanismo
- Autenticação via sessão padrão do Laravel (Laravel Breeze como base)
- Sem autocadastro — usuário só existe se convidado pelo gestor (RF-01)

## Senhas
- Hash via bcrypt (padrão Laravel), nunca texto plano em lugar algum
- Mínimo de 8 caracteres, exigir letra e número
- Sem expiração forçada nesta V1 (equipe pequena, risco controlado);
  reavaliar se a base de usuários crescer

## Convite e definição de senha
- Link de convite é um token único, assinado, com expiração de 48h
- Após uso ou expiração, o token não pode ser reutilizado
- Ao expirar, gestor pode reenviar novo convite

## Sessão
- Timeout de sessão por inatividade: 8h (cobre um dia de trabalho
  completo sem exigir novo login)
- Logout invalida a sessão no servidor, não apenas no cliente

## Recuperação de senha
- Fluxo padrão "esqueci minha senha" via e-mail (reutiliza o mesmo
  driver SMTP definido em `11-INTEGRACOES-EXTERNAS.md`)

## Proteções obrigatórias
- Rate limiting no login (Laravel Throttle) — máximo de 5 tentativas
  por minuto por IP/e-mail
- CSRF token em todos os formulários (padrão Laravel/Blade, não
  desabilitar)
- Nenhuma rota de negócio acessível sem autenticação — middleware
  `auth` aplicado globalmente ao grupo de rotas do sistema

PROJDOC_EOF

echo 'Criando docs/18-SEGURANCA-CHECKLIST-OWASP.md ...'
cat > "docs/18-SEGURANCA-CHECKLIST-OWASP.md" <<'PROJDOC_EOF'
# 18 — Checklist de Segurança (OWASP Básico)

Aplicar em toda fase, revisar por completo na Fase 11 (polimento).

- [ ] **Injeção SQL**: usar exclusivamente Eloquent/Query Builder com
  binding de parâmetros — nunca concatenar SQL cru com input do usuário
- [ ] **XSS (Cross-Site Scripting)**: usar `{{ }}` do Blade (escapa por
  padrão); nunca usar `{!! !!}` com conteúdo vindo de usuário (ex:
  comentários, título de tarefa)
- [ ] **CSRF**: todos os formulários e requisições Livewire usam token
  CSRF nativo do Laravel — não desabilitar
- [ ] **Upload malicioso de anexos**: validar mime type real do
  arquivo (não confiar só na extensão), limitar tipos a
  imagem/PDF, renomear arquivo no storage (não usar nome original como
  caminho), servir anexos por rota autenticada/autorizada, nunca
  diretório público sem controle de acesso quando o conteúdo for
  sensível
- [ ] **Controle de acesso quebrado**: toda rota valida autorização via
  Policy (ver `16-SEGURANCA-PERMISSOES.md`), nunca apenas por ocultar
  UI
- [ ] **Exposição de dados sensíveis**: senha nunca retorna em nenhuma
  resposta (usar `$hidden` no Model User); logs de erro não podem
  expor credenciais do `.env`
- [ ] **Configuração incorreta de segurança**: `APP_DEBUG=false` em
  produção sempre; `.env` fora do diretório público, nunca acessível
  via URL
- [ ] **Dependências desatualizadas**: rodar `composer audit`
  periodicamente; manter Laravel e pacotes em versões com suporte
  ativo
- [ ] **Rate limiting**: aplicar em login e em rotas de criação de
  recursos sensíveis contra abuso
- [ ] **HTTPS obrigatório**: forçar redirecionamento HTTP → HTTPS em
  produção (SSL via Let's Encrypt já disponível no cPanel)
- [ ] **Logs sem dado sensível**: nunca logar senha, token de convite,
  ou conteúdo de anexo

## Regra de commit
Nenhum Pull Request/merge para `main` deve ser aceito sem confirmar
mentalmente esta checklist para as mudanças daquela entrega.

PROJDOC_EOF

echo 'Criando docs/19-GESTAO-DE-SEGREDOS.md ...'
cat > "docs/19-GESTAO-DE-SEGREDOS.md" <<'PROJDOC_EOF'
# 19 — Gestão de Segredos

## O que é segredo neste projeto
- Senha do usuário MySQL (`medicalthermo_gestor`)
- `APP_KEY` do Laravel (chave de criptografia da aplicação)
- Credenciais SMTP (e-mail de convite)
- Chave SSH usada para deploy

## Onde os segredos vivem
- Exclusivamente no arquivo `.env` do servidor de produção
- `.env` está listado em `.gitignore` — NUNCA deve ser commitado
- Um `.env.example` (sem valores reais, só os nomes das variáveis)
  deve existir versionado, como referência de quais variáveis a
  aplicação espera

## Regras
- Nenhum segredo é solicitado pelo agente de IA fora do momento exato
  de configuração do `.env`
- Nenhum segredo aparece em: commits, documentação, `STATUS-DO-PROJETO.md`,
  `CHANGELOG.md`, ou mensagens de log
- Se um segredo for exposto acidentalmente em qualquer lugar, ele deve
  ser rotacionado (trocado) imediatamente, não apenas removido do lugar
  onde apareceu

## Rotação
- Sem política de rotação automática nesta V1 (equipe pequena)
- Rotação manual recomendada se: alguém com acesso à infraestrutura sair
  da empresa, ou se houver qualquer suspeita de vazamento

## Variáveis esperadas no `.env` (referência)
```
APP_NAME=
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://tarefas.medicalthermo.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medicalthermo_gestao_de_tarefas
DB_USERNAME=medicalthermo_gestor
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="${APP_NAME}"

QUEUE_CONNECTION=database
```

PROJDOC_EOF

echo 'Criando docs/20-ESTRATEGIA-DE-TESTES.md ...'
cat > "docs/20-ESTRATEGIA-DE-TESTES.md" <<'PROJDOC_EOF'
# 20 — Estratégia de Testes

## Ferramenta
Pest (sobre PHPUnit), padrão moderno do ecossistema Laravel — sintaxe
mais legível para revisão futura por não-programadores.

## Camadas de teste

**Testes unitários**
- Cobrem Service Classes/Actions isoladamente (ex: cálculo de atraso
  considerando fuso horário, categorização de reprovação)
- Rodam rápido, sem tocar banco de dados quando possível

**Testes de feature (integração)**
- Cobrem o fluxo completo de uma rota (requisição HTTP → resposta),
  incluindo autorização e efeitos no banco
- Exemplo: "gestor consegue criar tarefa", "liderado não consegue
  aprovar a própria tarefa"

**Testes de regressão**
- Antes de iniciar uma fase nova, rodar a suíte completa das fases
  anteriores — nenhuma fase nova pode quebrar um teste já existente
- Suíte completa deve rodar como parte do pipeline de deploy (ver
  `24-PIPELINE-DE-DEPLOY.md`) — deploy é bloqueado se algum teste falhar

## O que é obrigatório testar (mínimo, por fase)
- Fase 1: convite funciona, login funciona, liderado não acessa rota de
  gestor
- Fase 2: criação de tarefa com campos mínimos, prioridade default
  correta
- Fase 3: transições de status seguem exatamente a máquina de estados
  definida (nenhuma transição "pulada")
- Fase 4: comentário não pode ser editado/apagado via requisição direta
- Fase 5: reprovação exige categoria; só "não atende" conta na métrica
- Fase 6: todo evento relevante gera exatamente um registro de
  histórico
- Fase 7/8: liderado não vê tarefa de outro liderado, mesmo tentando
  acessar por URL direta
- Fase 9: notificação é criada para os eventos corretos
- Fase 10: tempo bloqueado não conta como atraso

## Definição de cobertura mínima
Toda regra de negócio listada em `03-REGRAS-DE-NEGOCIO.md` (RN-01 a
RN-12) deve ter ao menos um teste automatizado correspondente antes da
fase respectiva ser considerada concluída.

PROJDOC_EOF

echo 'Criando docs/21-CASOS-DE-TESTE-CRITICOS.md ...'
cat > "docs/21-CASOS-DE-TESTE-CRITICOS.md" <<'PROJDOC_EOF'
# 21 — Casos de Teste Críticos

Estes cenários NUNCA podem falhar — são a linha vermelha do sistema.
Cada um deve virar um teste automatizado nomeado de forma equivalente.

1. **Liderado não vê tarefa de outro liderado**, mesmo acessando a URL
   do detalhe diretamente (deve retornar 403).
2. **Liderado não consegue aprovar a própria tarefa** (rota de
   aprovação deve ser inacessível ao papel liderado).
3. **Liderado não altera prazo/prioridade diretamente** — apenas cria
   uma `change_request`.
4. **Comentário não pode ser editado ou apagado** após criado, mesmo
   via requisição direta à API/rota.
5. **Reprovação sem categoria é rejeitada pela validação** — campo
   obrigatório.
6. **Apenas reprovação "não atende ao solicitado" conta para métrica**
   de desempenho — as outras três categorias não afetam o número.
7. **Tarefa marcada como bloqueada não é contabilizada como atrasada**
   mesmo que o prazo já tenha passado.
8. **Cálculo de atraso respeita o fuso horário do liderado
   responsável**, não o do gestor nem o do servidor.
9. **Usuário desativado não consegue logar**, mas seus dados/comentários
   antigos continuam visíveis no histórico das tarefas.
10. **Nenhuma tabela permite exclusão física** — testar que
    `forceDelete()` não é chamado em nenhum fluxo da aplicação (apenas
    `delete()` com soft delete ativo).
11. **Toda mudança de status gera evento de histórico correspondente**
    — nenhuma transição "silenciosa".
12. **Token de convite expirado não permite definição de senha.**
13. **Upload de arquivo fora dos tipos permitidos (ex: .exe) é
    rejeitado**, independentemente da extensão informada pelo usuário
    (validação por mime type real).
14. **Escalonamento de tarefa crítica (RN-06) só dispara para o
    gestor**, nunca notifica outro liderado.
15. **Cobrança de aprovação parada (RN-07) não altera o status da
    tarefa** — apenas notifica.

PROJDOC_EOF

echo 'Criando docs/22-DEFINITION-OF-DONE.md ...'
cat > "docs/22-DEFINITION-OF-DONE.md" <<'PROJDOC_EOF'
# 22 — Definição de Pronto (Definition of Done)

Uma fase só pode ser marcada como concluída em `STATUS-DO-PROJETO.md`
quando TODOS os itens abaixo forem verdadeiros:

- [ ] Todos os Requisitos Funcionais da fase (ver `01-REQUISITOS-FUNCIONAIS.md`)
      estão implementados
- [ ] Todas as tabelas/colunas usadas existem exatamente como descrito
      em `08-MODELO-DE-DADOS.md` (nenhum nome de campo inventado fora
      do documento)
- [ ] Todas as rotas usadas existem exatamente como descrito em
      `09-CONTRATOS-DE-ROTAS.md`
- [ ] Toda mudança de estado relevante gera evento de histórico (ver
      `10-EVENTOS-INTERNOS.md`)
- [ ] Testes automatizados da fase escritos e passando (ver
      `20-ESTRATEGIA-DE-TESTES.md` e `21-CASOS-DE-TESTE-CRITICOS.md`
      aplicáveis a essa fase)
- [ ] Suíte de testes completa (fases anteriores incluídas) continua
      passando — nenhuma regressão
- [ ] Telas da fase testadas visualmente nos breakpoints: 360px, 768px,
      1366px, 1920px, 2560px (ver `15-RESPONSIVIDADE.md`)
- [ ] Checklist de segurança (`18-SEGURANCA-CHECKLIST-OWASP.md`)
      revisado para as mudanças desta fase
- [ ] Nenhum segredo exposto em código ou commit (ver
      `19-GESTAO-DE-SEGREDOS.md`)
- [ ] Commit(s) seguem a convenção definida (tipo(escopo): descrição)
- [ ] `CHANGELOG.md` atualizado com a entrega
- [ ] `STATUS-DO-PROJETO.md` atualizado: fase movida de "pendente" para
      "concluída", com data e próximo passo recomendado
- [ ] Deploy realizado em produção e validado manualmente no subdomínio
      real (`tarefas.medicalthermo.com`)

## Regra de bloqueio
Se qualquer item acima não puder ser marcado, a fase NÃO está concluída
— o agente deve registrar o item pendente na seção "Pendências e
bloqueios conhecidos" do `STATUS-DO-PROJETO.md` e não avançar para a
próxima fase sem sinalizar isso claramente ao operador humano.

PROJDOC_EOF

echo 'Criando docs/23-INFRAESTRUTURA-E-AMBIENTE.md ...'
cat > "docs/23-INFRAESTRUTURA-E-AMBIENTE.md" <<'PROJDOC_EOF'
# 23 — Infraestrutura e Ambiente

## Hospedagem
- Tipo: hospedagem compartilhada, painel cPanel
- Servidor: `br65-cp`
- Sistema operacional: Linux
- Pacote de hospedagem: Plano 30GB

## Stack de servidor
- Apache 2.4.68 + PHP-FPM
- PHP: versão 8.1 (via MultiPHP Manager)
- Banco de dados: MariaDB 10.11.18 (compatível MySQL)
- SSL: Let's Encrypt / AutoSSL via cPanel

## Aplicação
- Caminho no servidor: `/home/medicalthermo/tarefas.medicalthermo.com`
- Subdomínio: `tarefas.medicalthermo.com`
- Banco de dados: `medicalthermo_gestao_de_tarefas`
- Usuário do banco: `medicalthermo_gestor` (todos os privilégios)
- Senha do banco: definida apenas no `.env` de produção (ver
  `19-GESTAO-DE-SEGREDOS.md`)

## Acesso remoto
- SSH liberado, chave configurada
- Controle de versão: "Git Version Control" nativo do cPanel disponível

## Ferramentas de build
- Composer: disponível via SSH (sem tela gráfica dedicada neste
  cPanel)
- Node.js/NPM: necessário apenas para build de assets do Tailwind
  (Vite) — verificar disponibilidade via SSH antes da Fase 0; se
  ausente, compilar assets localmente e versionar o build, ou usar
  Tailwind via CDN como alternativa temporária de menor performance

## Cron
- Um único Cron Job apontando para:
  `* * * * * cd /home/medicalthermo/tarefas.medicalthermo.com && php artisan schedule:run >> /dev/null 2>&1`

## Limites conhecidos do ambiente (a respeitar no código)
- Sem Redis disponível — filas usam driver `database`
- Recursos de CPU/memória compartilhados — evitar processos longos
  síncronos; qualquer processamento pesado deve ir para fila
- Disco: 30GB no total do pacote — anexos devem respeitar o limite de
  10MB por arquivo definido em `01-REQUISITOS-FUNCIONAIS.md`

PROJDOC_EOF

echo 'Criando docs/24-PIPELINE-DE-DEPLOY.md ...'
cat > "docs/24-PIPELINE-DE-DEPLOY.md" <<'PROJDOC_EOF'
# 24 — Pipeline de Deploy

## Fonte da verdade
Repositório GitHub, branch `main` reflete sempre o que deve estar em
produção.

## Passo a passo do deploy
1. Código é revisado e mesclado (merge) em `main` no GitHub
2. No servidor, via Git Version Control do cPanel (ou script SSH),
   executa `git pull origin main` dentro de
   `/home/medicalthermo/tarefas.medicalthermo.com`
3. Script `/deploy/publicar.sh` é executado automaticamente após o
   pull, rodando nesta ordem:
   ```
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan queue:restart
   ```
4. Validação manual pós-deploy: abrir `tarefas.medicalthermo.com` e
   confirmar que a aplicação responde sem erro 500

## Regras invioláveis
- Nunca rodar `migrate:fresh` em produção, sob nenhuma circunstância —
  apaga dados reais
- Nunca fazer deploy direto na branch `main` sem passar por `develop`
  primeiro, exceto correções emergenciais de bug crítico
  (`hotfix/`)
- Testes automatizados devem passar antes de qualquer merge em `main`
  (ver `20-ESTRATEGIA-DE-TESTES.md`)
- Deploy só ocorre depois que a fase atende à
  `22-DEFINITION-OF-DONE.md`

## Rollback
Em caso de falha após deploy:
1. Identificar o último commit estável em `main` (via `git log`)
2. `git reset --hard <hash-do-commit-estavel>` no servidor
   (⚠️ apenas no diretório da aplicação, nunca no banco)
3. Repetir os passos de cache/queue do script de deploy
4. Se a falha envolveu migration de banco, avaliar necessidade de
   `php artisan migrate:rollback` — fazer isso apenas com backup do
   banco confirmado (ver `26-MONITORAMENTO-E-LOGS.md`)
5. Registrar o incidente em `STATUS-DO-PROJETO.md`, seção "Pendências
   e bloqueios conhecidos"

## Branches
- `main`: sempre estável, reflete produção
- `develop`: trabalho em andamento, integração das fases
- `fase-N-nome-da-fase`: branch de trabalho de cada fase grande,
  mesclada em `develop` e depois em `main` quando testada
- `hotfix/descricao`: correção emergencial direto sobre `main`

PROJDOC_EOF

echo 'Criando docs/25-AMBIENTES.md ...'
cat > "docs/25-AMBIENTES.md" <<'PROJDOC_EOF'
# 25 — Estratégia de Ambientes

## Ambientes existentes nesta V1

**Local/Desenvolvimento** (na máquina onde o Antigravity/OpenCode roda,
ou em um ambiente isolado antes do servidor real)
- Banco de dados próprio, separado do de produção (ex: SQLite local ou
  um segundo banco MySQL de teste)
- `APP_ENV=local`, `APP_DEBUG=true`
- Usado para construir e testar cada fase antes de enviar ao servidor

**Produção**
- Servidor cPanel descrito em `23-INFRAESTRUTURA-E-AMBIENTE.md`
- `APP_ENV=production`, `APP_DEBUG=false`
- Banco: `medicalthermo_gestao_de_tarefas` — dado real da operação

## Por que não existe ambiente de "homologação" separado nesta V1
O volume de uso (5 pessoas) e o tipo de hospedagem (compartilhada, um
único plano contratado) não justificam um terceiro ambiente agora — o
risco é mitigado pela suíte de testes automatizados e pela revisão
manual pós-deploy. Reavaliar se a equipe crescer significativamente.

## Regra de isolamento
Nenhum dado de teste pode, em hipótese alguma, ser inserido diretamente
no banco de produção para "só testar rápido". Todo teste ocorre no
ambiente local ou via testes automatizados (que usam banco de teste
próprio, nunca o de produção).

PROJDOC_EOF

echo 'Criando docs/26-MONITORAMENTO-E-LOGS.md ...'
cat > "docs/26-MONITORAMENTO-E-LOGS.md" <<'PROJDOC_EOF'
# 26 — Monitoramento e Logs

## Logs de aplicação
- Usar o log padrão do Laravel (`storage/logs/laravel.log`)
- Nível de log em produção: `error` e `warning` (evitar poluição com
  `debug` em produção)
- Nunca logar dado sensível (senha, token, conteúdo de anexo) — ver
  `19-GESTAO-DE-SEGREDOS.md`

## Verificação de saúde do sistema
- Rota simples de "healthcheck" (`/up`, recurso nativo do Laravel a
  partir das versões recentes) para confirmar que a aplicação está
  respondendo
- Verificação manual periódica do painel cPanel: uso de disco, memória
  e carga do servidor (telas já usadas durante o levantamento de
  infraestrutura)

## Backup
- Banco de dados: usar o backup automático do cPanel (JetBackup, já
  identificado como serviço ativo no servidor) — confirmar periodicidade
  configurada (diária recomendada)
- Antes de qualquer migration estrutural relevante em produção, gerar
  um backup manual adicional do banco via cPanel → "Backup"
- Anexos (`storage/app/public/anexos`): incluídos no backup geral de
  arquivos do cPanel

## O que observar após cada deploy
- Log de erro do Laravel nos primeiros minutos após deploy
- Fila de jobs (`php artisan queue:failed`) — jobs falhados indicam
  problema em notificação/processamento assíncrono
- Resposta visual manual das telas principais (login, painel, minhas
  tarefas)

## Alertas (nesta V1, manuais)
Não há ferramenta de monitoramento externo contratada nesta V1. A
verificação é manual, feita pelo operador após cada deploy e
periodicamente. Revisão futura: considerar um serviço gratuito de
uptime monitoring (ex: ping externo ao `/up`) caso a dependência do
sistema no dia a dia justifique.

PROJDOC_EOF

echo 'Criando docs/27-REGRAS-DE-EXECUCAO-DO-AGENTE.md ...'
cat > "docs/27-REGRAS-DE-EXECUCAO-DO-AGENTE.md" <<'PROJDOC_EOF'
# 27 — Regras de Execução do Agente

> Este é o documento de comportamento. Qualquer agente de IA
> (Antigravity, OpenCode, Claude Code, ou outro) que trabalhar neste
> projeto deve seguir estas regras acima de qualquer instinto próprio
> de "resolver sozinho".

## Regra 1 — Nunca inventar o que não está definido
Se, durante a construção de qualquer fase, o agente encontrar uma
decisão de negócio, nome de campo, rota ou regra que NÃO está descrita
em nenhum documento de `/docs`, ele deve:
1. Parar a implementação daquele ponto específico
2. Registrar a dúvida claramente em `STATUS-DO-PROJETO.md`, seção
   "Pendências e bloqueios conhecidos"
3. Perguntar objetivamente ao operador humano antes de prosseguir
4. Nunca presumir a resposta mais "óbvia" e seguir sem confirmação

Isso vale mesmo que a resposta pareça trivial — é exatamente esse tipo
de suposição pequena e não registrada que causa inconsistência entre
fases construídas em sessões diferentes.

## Regra 2 — Ler antes de escrever
Antes de qualquer linha de código em qualquer sessão, o agente deve ler,
nesta ordem:
1. `STATUS-DO-PROJETO.md` (onde parou, o que falta)
2. O(s) documento(s) de `/docs` relevantes à fase atual
3. `08-MODELO-DE-DADOS.md` e `09-CONTRATOS-DE-ROTAS.md` (nomes exatos)

## Regra 3 — Nunca pular fase
A ordem das fases definida em `FASES-DE-IMPLANTACAO.md` deve ser
seguida estritamente, a menos que o operador humano explicitamente
instrua o contrário por escrito.

## Regra 4 — Atualizar a documentação viva imediatamente
Ao concluir qualquer item (não apenas ao final de uma fase inteira), o
agente atualiza `STATUS-DO-PROJETO.md` e, se aplicável,
`CHANGELOG.md`. Nunca deixar para atualizar "depois" — se a sessão for
interrompida, o próximo agente precisa do estado real.

## Regra 5 — Documentar decisões técnicas novas
Qualquer decisão técnica tomada durante a construção que não estava
prevista nos documentos originais deve virar um novo ADR em
`06-DECISOES-DE-ARQUITETURA-ADR.md`, não apenas viver implícita no
código.

## Regra 6 — Nunca alterar nomes já definidos
Nomes de tabelas, colunas e rotas já definidos em
`08-MODELO-DE-DADOS.md` e `09-CONTRATOS-DE-ROTAS.md` são autoritativos.
Se uma alteração for genuinamente necessária, o agente deve primeiro
atualizar o documento (explicando o motivo) e só então alterar o
código — nunca o inverso.

## Regra 7 — Consistência de responsividade e segurança em toda entrega
Nenhuma tela é considerada entregue sem passar pelos critérios de
`15-RESPONSIVIDADE.md` e `18-SEGURANCA-CHECKLIST-OWASP.md`, mesmo que
o pedido específico da sessão não tenha mencionado isso explicitamente
— esses dois documentos se aplicam a toda e qualquer tela, sempre.

## Regra 8 — Definition of Done é inegociável
Uma fase não é "concluída" até satisfazer integralmente
`22-DEFINITION-OF-DONE.md`. Otimismo não substitui verificação.

## Regra 9 — Perguntas objetivas, não abertas
Quando o agente precisar perguntar algo ao operador humano (Regra 1),
a pergunta deve ser objetiva e, sempre que possível, com opções
concretas — nunca "o que você acha que deveria acontecer aqui?" solto,
e sim "encontrei X não definido; a opção A seria [...], a opção B seria
[...]; qual devo seguir?"

PROJDOC_EOF

echo 'Criando docs/28-BACKLOG-E-FORA-DE-ESCOPO.md ...'
cat > "docs/28-BACKLOG-E-FORA-DE-ESCOPO.md" <<'PROJDOC_EOF'
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

PROJDOC_EOF

echo 'Criando docs/29-MANUAL-DO-USUARIO.md ...'
cat > "docs/29-MANUAL-DO-USUARIO.md" <<'PROJDOC_EOF'
# 29 — Manual do Usuário

> Este documento deve ser preenchido/expandido ao final de cada fase
> com instruções reais de uso, incluindo capturas de tela quando
> possível. A estrutura abaixo é o esqueleto a ser seguido.

## Para o Gestor

### Como criar uma tarefa rapidamente
1. Abra o sistema e toque no botão "+" (Nova Tarefa)
2. Escreva um título curto e claro
3. Escolha o responsável (ou deixe em aberto para decidir depois)
4. Defina a prioridade e o prazo (data e hora)
5. Toque em Salvar

### Como aprovar ou reprovar uma entrega
1. No painel, toque em "Aguardando minha aprovação"
2. Abra a tarefa, leia o comentário e os anexos enviados
3. Toque em Aprovar (se estiver correto) ou Reprovar (escolhendo o
   motivo)

### Como acompanhar a equipe
1. No painel, veja os contadores de atrasadas, urgentes e vencendo hoje
2. Veja a lista por pessoa para identificar sobrecarga

## Para o Liderado

### Como ver minhas tarefas
1. Abra o sistema — a tela inicial já mostra Urgentes, Hoje e Próximas

### Como registrar andamento
1. Abra a tarefa
2. Escreva um comentário contando o que foi feito
3. Anexe uma foto/comprovante/PDF se necessário

### Como solicitar conclusão
1. Quando terminar, toque em "Solicitar conclusão"
2. Aguarde a aprovação do gestor

### Como marcar uma tarefa como bloqueada
1. Abra a tarefa
2. Toque em "Marcar como bloqueada"
3. Explique o motivo e de quem depende

## Perguntas frequentes
*(a preencher conforme dúvidas reais surgirem durante o uso)*

---
*Última atualização deste manual: [preencher a cada fase relevante]*

PROJDOC_EOF

echo 'Criando docs/30-RUNBOOK-DE-OPERACAO.md ...'
cat > "docs/30-RUNBOOK-DE-OPERACAO.md" <<'PROJDOC_EOF'
# 30 — Runbook de Operação

Guia de "o que fazer quando algo dá errado" em produção.

## Sistema fora do ar (erro 500 ou tela em branco)
1. Verificar `storage/logs/laravel.log` via SSH para identificar o erro
2. Verificar se o último deploy foi recente — se sim, considerar
   rollback (ver `24-PIPELINE-DE-DEPLOY.md`)
3. Verificar status dos serviços no cPanel (Apache, MySQL) na tela de
   status do servidor
4. Verificar se `APP_KEY` está definido no `.env` (causa comum de erro
   500 após novo deploy)

## Banco de dados não conecta
1. Confirmar credenciais no `.env` batem com as do cPanel → MySQL
   Databases
2. Confirmar que o serviço MySQL está "up" na tela de status do
   servidor
3. Verificar se o limite de conexões do plano de hospedagem foi
   atingido

## Disco cheio ou próximo do limite
1. Verificar uso em cPanel → estatísticas de disco
2. Revisar volume de anexos em `storage/app/public/anexos`
3. Considerar migração de storage (ver `28-BACKLOG-E-FORA-DE-ESCOPO.md`)

## Fila de notificações não está processando
1. Verificar `php artisan queue:failed` via SSH
2. Confirmar que o Cron Job está ativo e rodando a cada minuto
3. Reiniciar a fila: `php artisan queue:restart`

## Tarefa "presa" em um status incorretamente
1. Nunca alterar diretamente via banco de dados sem registrar o motivo
2. Se necessário, criar uma correção manual documentada, sempre
   gerando o evento de histórico correspondente (nunca alterar `status`
   direto no banco sem o rastro em `task_history_events`)

## Usuário não recebe e-mail de convite
1. Verificar configuração SMTP no `.env`
2. Verificar pasta de spam do destinatário
3. Reenviar convite pela tela de Gestão de Equipe

## Contato de suporte
*(preencher com quem o gestor deve acionar em caso de problema técnico
que ultrapasse este runbook — ex: suporte da hospedagem, ou quem
mantém o projeto)*

PROJDOC_EOF

echo 'Criando docs/31-PLANO-DE-SUPORTE.md ...'
cat > "docs/31-PLANO-DE-SUPORTE.md" <<'PROJDOC_EOF'
# 31 — Plano de Suporte

## Canal de suporte para os usuários finais (gestor e liderados)
*(a definir pelo operador — sugestão inicial: canal direto com o
gestor/responsável pelo sistema, dado o porte pequeno da equipe)*

## Tipos de solicitação esperados
- Dúvida de uso → resolver com `29-MANUAL-DO-USUARIO.md`
- Bug/comportamento inesperado → registrar com: o que fazia, o que
  esperava, o que aconteceu, em qual dispositivo/tela
- Solicitação de nova funcionalidade → avaliar contra
  `28-BACKLOG-E-FORA-DE-ESCOPO.md` antes de qualquer implementação

## Fluxo de reporte de bug
1. Usuário relata ao gestor/responsável pelo sistema
2. Responsável reproduz o problema (ou registra o cenário) e cria uma
   entrada em `STATUS-DO-PROJETO.md`, seção "Pendências e bloqueios
   conhecidos"
3. Agente de IA prioriza a correção seguindo as mesmas regras de
   qualidade das fases normais (não é aceitável corrigir um bug
   introduzindo um novo, sem teste correspondente)

## SLA (nível de serviço) — nesta V1
Sem SLA formal contratado — equipe pequena, resolução conforme
disponibilidade do responsável pelo sistema. Reavaliar se o sistema se
tornar crítico para operação diária de forma mais ampla.

PROJDOC_EOF

echo 'Criando docs/CHANGELOG.md ...'
cat > "docs/CHANGELOG.md" <<'PROJDOC_EOF'
# Changelog

Formato de cada entrada:
```
## [data] — [Fase X] — [resumo curto]
- O que foi adicionado/alterado
- Arquivos principais afetados
```

---

## [a preencher] — Fase 0 — Documentação inicial completa
- Criada a estrutura completa de documentação em `/docs` (31 documentos
  + status vivo + changelog + fases de implantação), cobrindo produto,
  arquitetura, dados, UX/UI, segurança, qualidade, infraestrutura,
  governança e operação
- Arquivos principais: todos os arquivos em `/docs`

PROJDOC_EOF

echo 'Criando docs/FASES-DE-IMPLANTACAO.md ...'
cat > "docs/FASES-DE-IMPLANTACAO.md" <<'PROJDOC_EOF'
# Fases de Implantação

> Ordem obrigatória de construção. Ver `27-REGRAS-DE-EXECUCAO-DO-AGENTE.md`,
> Regra 3: nunca pular fase sem autorização explícita do operador.

## Fase 0 — Infraestrutura e esqueleto do projeto
- Projeto Laravel criado e rodando no servidor
- Conexão com banco de dados validada
- Estrutura `/docs` criada e preenchida (este pacote)
- Repositório Git inicializado, primeiro commit realizado
- Pipeline de deploy testada (deploy "vazio" funcionando)

## Fase 1 — Identidade e acesso
Ver RF-01 a RF-03, telas em `13-INVENTARIO-DE-TELAS.md`.

## Fase 2 — Criação rápida de tarefas
Ver RF-04 a RF-06.

## Fase 3 — Ciclo de vida da tarefa
Ver RF-07 a RF-11.

## Fase 4 — Comunicação da tarefa
Ver RF-12 a RF-14.

## Fase 5 — Aprovação e reprovação
Ver RF-15 a RF-17, RF-29.

## Fase 6 — Histórico e auditoria
Ver RF-20.

## Fase 7 — Painel do gestor (dashboard)
Ver RF-21 a RF-24.

## Fase 8 — Portal do liderado
Ver RF-25 a RF-26.

## Fase 9 — Notificações
Ver RF-27 a RF-28.

## Fase 10 — Regras de bloqueio
Ver RF-18 a RF-19.

## Fase 11 — Polimento, responsividade final e revisão de segurança
- Testes em todos os breakpoints (`15-RESPONSIVIDADE.md`)
- Revisão completa de `18-SEGURANCA-CHECKLIST-OWASP.md`
- Revisão de performance em hospedagem compartilhada
- Preenchimento final de `29-MANUAL-DO-USUARIO.md`

## O que NÃO entra em nenhuma fase desta V1
Ver `28-BACKLOG-E-FORA-DE-ESCOPO.md`.

PROJDOC_EOF

echo 'Criando docs/README.md ...'
cat > "docs/README.md" <<'PROJDOC_EOF'
# Documentação — Sistema de Gestão de Tarefas para Liderados

## Comece por aqui
1. **`STATUS-DO-PROJETO.md`** — sempre o primeiro arquivo a ler, em
   qualquer sessão, com qualquer ferramenta
2. **`27-REGRAS-DE-EXECUCAO-DO-AGENTE.md`** — regras de comportamento
   obrigatórias para qualquer agente de IA trabalhando neste projeto
3. **`FASES-DE-IMPLANTACAO.md`** — ordem de construção

## Índice completo

### Produto e Negócio
- `00-CONTEXTO-DO-PROJETO.md`
- `01-REQUISITOS-FUNCIONAIS.md`
- `02-REQUISITOS-NAO-FUNCIONAIS.md`
- `03-REGRAS-DE-NEGOCIO.md`
- `04-PERSONAS-E-JORNADAS.md`

### Arquitetura
- `05-ARQUITETURA-VISAO-GERAL.md`
- `06-DECISOES-DE-ARQUITETURA-ADR.md`
- `07-MODELO-DE-DOMINIO.md`

### Dados
- `08-MODELO-DE-DADOS.md`

### Contratos e Integração
- `09-CONTRATOS-DE-ROTAS.md`
- `10-EVENTOS-INTERNOS.md`
- `11-INTEGRACOES-EXTERNAS.md`

### UX/UI
- `12-DESIGN-SYSTEM.md`
- `13-INVENTARIO-DE-TELAS.md`
- `14-FLUXOS-DE-NAVEGACAO.md`
- `15-RESPONSIVIDADE.md`

### Segurança
- `16-SEGURANCA-PERMISSOES.md`
- `17-SEGURANCA-AUTENTICACAO.md`
- `18-SEGURANCA-CHECKLIST-OWASP.md`
- `19-GESTAO-DE-SEGREDOS.md`

### Qualidade e Testes
- `20-ESTRATEGIA-DE-TESTES.md`
- `21-CASOS-DE-TESTE-CRITICOS.md`
- `22-DEFINITION-OF-DONE.md`

### Infraestrutura e DevOps
- `23-INFRAESTRUTURA-E-AMBIENTE.md`
- `24-PIPELINE-DE-DEPLOY.md`
- `25-AMBIENTES.md`
- `26-MONITORAMENTO-E-LOGS.md`

### Processo e Governança
- `27-REGRAS-DE-EXECUCAO-DO-AGENTE.md`
- `28-BACKLOG-E-FORA-DE-ESCOPO.md`

### Operacional
- `29-MANUAL-DO-USUARIO.md`
- `30-RUNBOOK-DE-OPERACAO.md`
- `31-PLANO-DE-SUPORTE.md`

### Documentos vivos (atualizados continuamente)
- `STATUS-DO-PROJETO.md`
- `CHANGELOG.md`
- `FASES-DE-IMPLANTACAO.md`

PROJDOC_EOF

echo 'Criando docs/STATUS-DO-PROJETO.md ...'
cat > "docs/STATUS-DO-PROJETO.md" <<'PROJDOC_EOF'
# STATUS DO PROJETO — Sistema de Gestão de Tarefas para Liderados

Última atualização: [preencher na primeira execução] por [ferramenta/agente]

## Fase atual
Fase 0 — Infraestrutura e esqueleto do projeto (ainda não iniciada
tecnicamente; documentação completa)

## Progresso da fase atual
- [x] Levantamento de infraestrutura do servidor (cPanel, PHP 8.1,
      MySQL, SSH, caminho do subdomínio)
- [x] Banco de dados criado e usuário vinculado
- [x] Decisão de stack tecnológica (Laravel + Livewire + MySQL)
- [x] Documentação completa de produto, arquitetura, dados, UX,
      segurança, qualidade, DevOps, governança e operação
- [ ] Projeto Laravel criado e rodando no servidor
- [ ] Conexão com banco de dados validada
- [ ] Repositório Git inicializado, primeiro commit realizado
- [ ] Pipeline de deploy testada

## Fases concluídas
*(nenhuma ainda — apenas a documentação inicial está pronta)*

## Fases pendentes
- [ ] Fase 0 — Infraestrutura e esqueleto do projeto
- [ ] Fase 1 — Identidade e acesso
- [ ] Fase 2 — Criação rápida de tarefas
- [ ] Fase 3 — Ciclo de vida da tarefa
- [ ] Fase 4 — Comunicação da tarefa
- [ ] Fase 5 — Aprovação e reprovação
- [ ] Fase 6 — Histórico e auditoria
- [ ] Fase 7 — Painel do gestor
- [ ] Fase 8 — Portal do liderado
- [ ] Fase 9 — Notificações
- [ ] Fase 10 — Regras de bloqueio
- [ ] Fase 11 — Polimento, responsividade final e revisão de segurança

## Decisões técnicas tomadas nesta fase
- Ver `06-DECISOES-DE-ARQUITETURA-ADR.md` (ADR-001 a ADR-006)

## Pendências e bloqueios conhecidos
- Confirmar se Node.js/NPM está disponível no servidor via SSH (necessário
  para build de assets do Tailwind/Vite) — ver `23-INFRAESTRUTURA-E-AMBIENTE.md`
- Confirmar periodicidade do backup automático (JetBackup) no cPanel
- Senha do banco de dados e credenciais SMTP ainda precisam ser
  inseridas manualmente no `.env` de produção pelo operador humano

## Próximo passo recomendado
Executar a Fase 0 (infraestrutura e esqueleto do projeto) seguindo
`FASES-DE-IMPLANTACAO.md` e todas as regras de `27-REGRAS-DE-EXECUCAO-DO-AGENTE.md`.
Ao concluir, atualizar este arquivo antes de iniciar a Fase 1.

PROJDOC_EOF

echo 'Criando deploy/publicar.sh ...'
cat > "deploy/publicar.sh" <<'PROJDOC_EOF'
#!/bin/bash
# Script de deploy — executar após git pull em produção
# Ver docs/24-PIPELINE-DE-DEPLOY.md

set -e  # interrompe o script se qualquer comando falhar

echo "== Instalando dependências (produção, sem dev) =="
composer install --no-dev --optimize-autoloader

echo "== Rodando migrations =="
php artisan migrate --force

echo "== Atualizando caches ==" 
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "== Reiniciando fila =="
php artisan queue:restart

echo "== Deploy concluído =="

PROJDOC_EOF

chmod +x deploy/publicar.sh
echo ""
echo "Estrutura criada com sucesso:"
find docs deploy -type f | sort
