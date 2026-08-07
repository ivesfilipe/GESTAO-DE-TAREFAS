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

