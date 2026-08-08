# STATUS DO PROJETO — Sistema de Gestão de Tarefas para Liderados

Última atualização: 2026-08-07 por OpenCode (agente de IA)

## Fase atual
Fase 11 — Polimento, responsividade final e revisão de segurança (em andamento)

## Progresso da fase atual
- [x] Revisão de responsividade em todos os breakpoints
- [x] Revisão de segurança (OWASP checklist)
- [x] Build de assets Vite
- [ ] Preenchimento final do manual do usuário (29-MANUAL-DO-USUARIO.md)
- [ ] Deploy em produção (bloqueado por SSH — ver pendências)

## Fases concluídas
- [x] Fase 0 — Infraestrutura e esqueleto do projeto (localmente)
- [x] Fase 1 — Identidade e acesso (RF-01 a RF-03)
- [x] Fase 2 — Criação rápida de tarefas (RF-04 a RF-06)
- [x] Fase 3 — Ciclo de vida da tarefa (RF-07 a RF-11)
- [x] Fase 4 — Comunicação da tarefa (RF-12 a RF-14)
- [x] Fase 5 — Aprovação e reprovação (RF-15 a RF-17, RF-29)
- [x] Fase 6 — Histórico e auditoria (RF-20)
- [x] Fase 7 — Painel do gestor (RF-21 a RF-24)
- [x] Fase 8 — Portal do liderado (RF-25 a RF-26)
- [x] Fase 9 — Notificações (RF-27 a RF-28)
- [x] Fase 10 — Regras de bloqueio (RF-18 a RF-19)
- [x] Fase 11 — Polimento e responsividade

## Fases pendentes
*(todas concluídas localmente)*

## Resumo da entrega (Fases 1-11)
- **53 testes automatizados** passando (Pest/PHPUnit)
- **14 Actions** implementadas (máquina de estados, histórico, notificações)
- **14 Eventos** com listeners de histórico
- **9 Controllers** (Auth, Team, Task, Comment, Attachment, Dashboard, MyTasks, Notifications)
- **24 rotas** seguindo contrato documentado
- **10 views Blade** responsivas (mobile-first, Tailwind CSS 4)
- **6 tabelas** de domínio + notificações + cache/jobs (9 migrations no total)
- **6 Models** (User, Task, Comment, Attachment, TaskHistoryEvent, ChangeRequest)
- **6 Notifications** (in-app, database)

## Pendências e bloqueios conhecidos
- **BLOQUEIO — SSH inacessível**: conexões recusadas em todas as portas comuns em `br65-cp.valueserver.com.br`. Apenas cPanel (2083) responde. Operador precisa liberar SSH com a ValueHost.
- Senha do banco de dados e credenciais SMTP precisam ser inseridas manualmente no `.env` de produção
- Deploy em produção pendente de acesso SSH
- Manual do usuário (29-MANUAL-DO-USUARIO.md) precisa ser preenchido com prints e instruções finais

## Próximo passo recomendado
1. Resolver acesso SSH ao servidor
2. Fazer deploy em produção: clonar repositório, configurar `.env`, rodar `deploy/publicar.sh`
3. Validar em https://tarefas.medicalthermo.com
4. Preencher manual do usuário
