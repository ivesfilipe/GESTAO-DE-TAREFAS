#!/bin/bash
# Keepalive do Reverb (WebSocket) para hospedagem compartilhada.
# Roda a cada minuto via cron; inicia o servidor se nao estiver ativo.
#
# Cron sugerido:
#   * * * * * /bin/bash $APP_DIR/deploy/keepalive-reverb.sh >> $HOME/reverb-tarefas.log 2>&1

set -u
cd "$(dirname "$0")/.." || exit 1

PHP=/opt/cpanel/ea-php83/root/usr/bin/php
[ -x "$PHP" ] || PHP=$(command -v php)

if ! pgrep -f "artisan reverb:start" > /dev/null 2>&1; then
  echo "[$(date '+%Y-%m-%d %H:%M:%S')] reverb inativo — iniciando..."
  nohup $PHP artisan reverb:start --host=0.0.0.0 --port=${REVERB_PORT:-8080} >> ~/reverb-tarefas.log 2>&1 &
  sleep 3
  pgrep -f "artisan reverb:start" > /dev/null && \
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] reverb iniciado ✓" || \
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] FALHA ao iniciar reverb"
fi
