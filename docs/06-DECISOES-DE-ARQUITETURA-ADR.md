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

## ADR-007 — PHP 8.3 + Laravel 12 (substitui a premissa de PHP 8.1 do ADR-001)
**Status**: Aceito
**Contexto**: O servidor foi levantado com PHP 8.1 (`23-INFRAESTRUTURA-E-AMBIENTE.md`),
o que limitaria o projeto ao Laravel 10. Porém o Laravel 10 está EOL
(fim de suporte em fev/2025) e o Composer bloqueia sua instalação por
advisories de segurança conhecidos — incompatível com
`18-SEGURANCA-CHECKLIST-OWASP.md`.
**Decisão**: Operador autorizou trocar o PHP do subdomínio para 8.3 via
MultiPHP Manager do cPanel; projeto criado com Laravel 12 (suportado).
**Motivo**: Framework com correções de segurança ativas; mantém o
espírito do ADR-001 ("versão estável mais recente compatível com o
servidor").
**Alternativas descartadas**: Manter PHP 8.1 + Laravel 10 ignorando os
advisories (risco de segurança em produção).

## ADR-008 — Assets de front-end compilados localmente e versionados
**Status**: Aceito
**Contexto**: O build do Vite/Tailwind exige Node.js, cuja
disponibilidade no servidor ainda não foi confirmada (pendência
registrada em `STATUS-DO-PROJETO.md`).
**Decisão**: Rodar `npm run build` localmente e versionar
`public/build` no Git (removido do `.gitignore`), conforme alternativa
já prevista em `23-INFRAESTRUTURA-E-AMBIENTE.md`.
**Motivo**: Deploy em hospedagem compartilhada não pode depender de
ferramenta que talvez não exista no servidor; o script
`deploy/publicar.sh` fica 100% PHP/Composer.
**Revisão futura**: Se Node.js for confirmado no servidor, o build pode
passar a rodar no deploy e `public/build` volta ao `.gitignore`.

---

## Como adicionar um novo ADR
Ao tomar qualquer decisão técnica não coberta nesta documentação
durante a construção, o agente deve adicionar uma nova entrada aqui,
seguindo o mesmo formato (Status / Contexto / Decisão / Motivo /
Alternativas descartadas), e referenciar o número do ADR no commit
correspondente.

