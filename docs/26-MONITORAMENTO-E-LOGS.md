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

