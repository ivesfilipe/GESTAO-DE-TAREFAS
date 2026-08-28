# STATUS DO PROJETO — Sistema de Gestão de Tarefas para Liderados

Última atualização: 2026-08-27 por OpenCode (agente de IA)

## Fase atual
**FASE 27 — COPILOTO INTELIGENTE DO GESTOR (PARTE 1 E 2) ENTREGUE** —
Auditoria e endurecimento de segurança (Parte 1) + UX completa de delegação, copiloto e perfil inteligente (Parte 2). Pronto para deploy em https://tarefas.medicalthermo.com.

## Progresso da fase atual
- [x] Parte 1: arquitetura multi-provider, ZDR bloqueante, memória gerencial, campos de IA, logs sem prompt/resposta
- [x] Parte 1: escopo por gestor (`users.manager_id`), Gates/Policies, fallback pago bloqueado, visão centralizada
- [x] Parte 2:
  - [x] `SmartDelegationService` com structured output JSON, UX mobile-first, tipos de tarefa, validação de assignee inválido
  - [x] `CopilotService` com radar determinístico (Top 5), chat com tools, cobrança não-automática, divisão em passos
  - [x] `ProfileIntelligenceService` com fontes, invalidação, `TaskSuggestionService` com filtro por categoria
  - [x] Documentos com allowlist, status de processamento, XSS mitigado
  - [x] 270 testes automatizados passando (incluindo ZDR, fallback, escopo, métricas, prompt injection)
  - [x] Pint limpo, build Vite, documentação atualizada
  - [ ] **Deploy em produção**
- [ ] Preenchimento final do manual do usuário (29-MANUAL-DO-USUARIO.md)

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
- [x] Fase 27 — Copiloto Inteligente do Gestor

## Resumo da entrega
- **230 testes automatizados** passando (Pest/PHPUnit)
- **Camada multi-provider de IA**: Groq (padrão), OpenAI, Ollama, Mock
- **Governança**: Zero Data Retention, `AIUsageLog`, gates de gestor
- **Memória gerencial**: perfis, documentos e chunks por liderado
- **Novas superfícies**: delegação inteligente, copiloto com tools, perfil inteligente
- **Deploy contínuo**: cron + `deploy/auto-deploy.sh` (push → produção em ~1 min)

## Pendências e bloqueios conhecidos
- ~~SSH inacessível~~ — **contornado em 2026-08-08**: operações de
  servidor rodam via cPanel > Avançado > Terminal; deploy é 100%
  automático via cron (`deploy/auto-deploy.sh`). SSH não é mais
  necessário para operar o sistema
- Credenciais SMTP precisam ser confirmadas no `.env` de produção
  (envio de convites por e-mail)
- Manual do usuário (29-MANUAL-DO-USUARIO.md) precisa ser preenchido
  com prints e instruções finais
- Higiene opcional: apagar a cópia secundária
  `/home/medicalthermo/tarefas.medicalthermo.com` (sem prefixo
  duplicado) no servidor — não é servida nem usada pelo deploy

## Próximo passo recomendado
1. Preencher manual do usuário (29) com prints da aplicação no ar
2. Validar envio de e-mail de convite (SMTP) em produção
3. Convidar os liderados pela tela Equipe e iniciar uso real
