# STATUS DO PROJETO — Sistema de Gestão de Tarefas para Liderados

Última atualização: 2026-08-07 por OpenCode (agente de IA)

## Fase atual
Fase 0 — Infraestrutura e esqueleto do projeto (em andamento:
esqueleto pronto localmente e no GitHub; falta subir no servidor)

## Progresso da fase atual
- [x] Levantamento de infraestrutura do servidor (cPanel, PHP 8.1,
      MySQL, SSH, caminho do subdomínio)
- [x] Banco de dados criado e usuário vinculado
- [x] Decisão de stack tecnológica (Laravel + Livewire + MySQL)
- [x] Documentação completa de produto, arquitetura, dados, UX,
      segurança, qualidade, DevOps, governança e operação
- [x] Projeto Laravel 12 criado e rodando LOCALMENTE (SQLite, testes
      passando, assets Vite buildados) — falta rodar no servidor
- [x] Repositório Git inicializado, primeiro commit realizado, push
      para github.com/ivesfilipe/GESTAO-DE-TAREFAS (branch `main`)
- [ ] Projeto Laravel rodando no servidor (aguardando acesso — ver
      bloqueio de SSH abaixo)
- [ ] Conexão com banco de dados de PRODUÇÃO validada (depende do
      `.env` de produção no servidor)
- [ ] Pipeline de deploy testada (primeiro clone + `deploy/publicar.sh`
      no servidor)

## Fases concluídas
*(nenhuma ainda — Fase 0 em andamento)*

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
- Ver `06-DECISOES-DE-ARQUITETURA-ADR.md` (ADR-001 a ADR-009; destaques
  da Fase 0: ADR-007 PHP 8.3 + Laravel 12, ADR-008 assets versionados,
  ADR-009 docroot em `public/`)

## Pendências e bloqueios conhecidos
- **BLOQUEIO — SSH inacessível**: conexões recusadas em todas as portas
  comuns (22, 2222, 22222, 2022, 8022, 9022, 9922, 65022, 21098, 7822)
  em `br65-cp.valueserver.com.br` (187.110.165.194). Apenas cPanel
  (2083) responde. Operador tem senha SSH fornecida pelo provedor —
  precisa localizar no painel da ValueHost (ou no e-mail de boas-vindas)
  o HOST e a PORTA corretos, ou solicitar a liberação do SSH ao suporte.
- Node.js/NPM no servidor: pendência neutralizada pelo ADR-008 (assets
  compilados localmente e versionados em `public/build`).
- Confirmar periodicidade do backup automático (JetBackup) no cPanel
- Senha do banco de dados e credenciais SMTP ainda precisam ser
  inseridas manualmente no `.env` de produção pelo operador humano
- Senha SSH foi compartilhada nesta conversa — recomendado rotacioná-la
  (docs/19, regra de exposição de segredos)

## Próximo passo recomendado
Concluir a Fase 0 no servidor assim que o acesso (SSH ou cPanel Git
Version Control + Terminal) estiver disponível: clonar o repositório em
`/home/medicalthermo/tarefas.medicalthermo.com`, apontar o docroot para
`public/` (ADR-009), criar o `.env` de produção, rodar
`deploy/publicar.sh` e validar https://tarefas.medicalthermo.com.
Ao concluir, atualizar este arquivo antes de iniciar a Fase 1.

