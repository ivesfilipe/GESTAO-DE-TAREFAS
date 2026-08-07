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

