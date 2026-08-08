#!/bin/bash
# Auto-deploy via cron — substitui o webhook PHP (inválido neste servidor:
# exec() é desabilitado no PHP web, ver docs/24-PIPELINE-DE-DEPLOY.md).
#
# A cada minuto o cron roda este script; se origin/main tiver commit novo,
# faz git pull + deploy/publicar.sh (composer, migrations, caches).
#
# Setup (uma vez) — cPanel > Cron Jobs > "Add New Cron Job":
#   * * * * * /bin/bash $HOME/home/medicalthermo/tarefas.medicalthermo.com/deploy/auto-deploy.sh >> $HOME/auto-deploy.log 2>&1
# (no cPanel, $HOME deve ser expandido para /home/medicalthermo)

set -u
cd "$(dirname "$0")/.." || exit 1
APP_DIR="$(pwd)"
GIT=/usr/local/cpanel/3rdparty/lib/path-bin/git
LOCK=/tmp/auto-deploy-tarefas.lock
export GIT_TERMINAL_PROMPT=0  # nunca trava esperando senha

# Trava contra execuções sobrepostas; trava com +15min é ignorada
# (deploy anterior pode ter morrido no meio)
if [ -f "$LOCK" ] && [ $(( $(date +%s) - $(stat -c %Y "$LOCK") )) -lt 900 ]; then
  exit 0
fi

$GIT fetch origin main >/dev/null 2>&1 || exit 0  # falha de rede: tenta no próximo minuto

LOCAL=$($GIT rev-parse HEAD)
REMOTE=$($GIT rev-parse origin/main)
[ "$LOCAL" = "$REMOTE" ] && exit 0  # nada novo

touch "$LOCK"
echo "[$(date '+%Y-%m-%d %H:%M:%S')] nova versão: ${LOCAL:0:7} -> ${REMOTE:0:7} — publicando..."

$GIT checkout -- .
if ! $GIT pull origin main; then
  echo "[$(date '+%Y-%m-%d %H:%M:%S')] FALHA no git pull"
  rm -f "$LOCK"
  exit 1
fi

bash deploy/publicar.sh
STATUS=$?

rm -f "$LOCK"
echo "[$(date '+%Y-%m-%d %H:%M:%S')] deploy finalizado (status $STATUS)"
exit $STATUS
