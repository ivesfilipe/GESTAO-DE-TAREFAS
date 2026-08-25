#!/bin/bash
# Drena a fila de jobs (webhooks, notificacoes) em hospedagem compartilhada.
# Roda a cada minuto via cron; processa por ate 50s e encerra.
#
# Cron sugerido:
#   * * * * * /bin/bash $APP_DIR/deploy/queue-drain.sh > /dev/null 2>&1

set -u
cd "$(dirname "$0")/.." || exit 1

PHP=/opt/cpanel/ea-php83/root/usr/bin/php
[ -x "$PHP" ] || PHP=$(command -v php)

$PHP artisan queue:work --stop-when-empty --max-time=50 --tries=3 >> ~/queue-tarefas.log 2>&1
