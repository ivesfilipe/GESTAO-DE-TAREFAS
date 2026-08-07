# 30 — Runbook de Operação

Guia de "o que fazer quando algo dá errado" em produção.

## Sistema fora do ar (erro 500 ou tela em branco)
1. Verificar `storage/logs/laravel.log` via SSH para identificar o erro
2. Verificar se o último deploy foi recente — se sim, considerar
   rollback (ver `24-PIPELINE-DE-DEPLOY.md`)
3. Verificar status dos serviços no cPanel (Apache, MySQL) na tela de
   status do servidor
4. Verificar se `APP_KEY` está definido no `.env` (causa comum de erro
   500 após novo deploy)

## Banco de dados não conecta
1. Confirmar credenciais no `.env` batem com as do cPanel → MySQL
   Databases
2. Confirmar que o serviço MySQL está "up" na tela de status do
   servidor
3. Verificar se o limite de conexões do plano de hospedagem foi
   atingido

## Disco cheio ou próximo do limite
1. Verificar uso em cPanel → estatísticas de disco
2. Revisar volume de anexos em `storage/app/public/anexos`
3. Considerar migração de storage (ver `28-BACKLOG-E-FORA-DE-ESCOPO.md`)

## Fila de notificações não está processando
1. Verificar `php artisan queue:failed` via SSH
2. Confirmar que o Cron Job está ativo e rodando a cada minuto
3. Reiniciar a fila: `php artisan queue:restart`

## Tarefa "presa" em um status incorretamente
1. Nunca alterar diretamente via banco de dados sem registrar o motivo
2. Se necessário, criar uma correção manual documentada, sempre
   gerando o evento de histórico correspondente (nunca alterar `status`
   direto no banco sem o rastro em `task_history_events`)

## Usuário não recebe e-mail de convite
1. Verificar configuração SMTP no `.env`
2. Verificar pasta de spam do destinatário
3. Reenviar convite pela tela de Gestão de Equipe

## Contato de suporte
*(preencher com quem o gestor deve acionar em caso de problema técnico
que ultrapasse este runbook — ex: suporte da hospedagem, ou quem
mantém o projeto)*

