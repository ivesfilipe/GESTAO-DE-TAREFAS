# Changelog

Formato de cada entrada:
```
## [data] — [Fase X] — [resumo curto]
- O que foi adicionado/alterado
- Arquivos principais afetados
```

---

## [2026-08-07] — Fase 0 — Esqueleto Laravel 12 + repositório Git (parcial)
- Projeto Laravel 12.65 criado na raiz do repositório (ADR-007: PHP 8.3
  no servidor em vez de 8.1, pois Laravel 10 está EOL)
- Ambiente local validado: SQLite, migrations rodando, suíte de testes
  passando, app respondendo HTTP 200
- `.env.example` alinhado a `19-GESTAO-DE-SEGREDOS.md` (bloco de
  referência de produção)
- Assets Vite/Tailwind compilados localmente e versionados em
  `public/build` (ADR-008)
- Repositório Git inicializado; push da branch `main` para
  github.com/ivesfilipe/GESTAO-DE-TAREFAS (privado)
- ADR-009: docroot do subdomínio será apontado para `public/`
- Pendente: subir no servidor (SSH bloqueado — ver STATUS-DO-PROJETO.md)
- Arquivos principais: esqueleto Laravel completo na raiz, `docs/06`,
  `docs/23`, `.env.example`, `.gitignore`

## [a preencher] — Fase 0 — Documentação inicial completa
- Criada a estrutura completa de documentação em `/docs` (31 documentos
  + status vivo + changelog + fases de implantação), cobrindo produto,
  arquitetura, dados, UX/UI, segurança, qualidade, infraestrutura,
  governança e operação
- Arquivos principais: todos os arquivos em `/docs`

