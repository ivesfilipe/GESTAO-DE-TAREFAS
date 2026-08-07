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

