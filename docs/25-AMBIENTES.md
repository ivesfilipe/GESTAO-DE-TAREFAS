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

