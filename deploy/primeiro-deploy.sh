#!/bin/bash
# Bootstrap do servidor — executar UMA VEZ no primeiro deploy (Fase 0)
# Uso (Terminal do cPanel ou cron único):
#   bash /home/medicalthermo/tarefas.medicalthermo.com/deploy/primeiro-deploy.sh
# Ver docs/24-PIPELINE-DE-DEPLOY.md e docs/23-INFRAESTRUTURA-E-AMBIENTE.md

set -e
cd "$(dirname "$0")/.."

# PHP 8.3 (EasyApache 4); cai no php do PATH se nao existir
if [ -x /opt/cpanel/ea-php83/root/usr/bin/php ]; then
  PHP=/opt/cpanel/ea-php83/root/usr/bin/php
else
  PHP=$(command -v php)
fi
echo "== PHP em uso =="
$PHP -v | head -1

# Composer global do cPanel; senao tenta o phar padrao
if command -v composer >/dev/null 2>&1; then
  COMPOSER_BIN=$(command -v composer)
else
  COMPOSER_BIN=/usr/local/bin/composer
fi

echo "== Instalando dependencias (producao, sem dev) =="
$PHP $COMPOSER_BIN install --no-dev --optimize-autoloader

echo "== Gerando APP_KEY (somente se vazia no .env) =="
$PHP artisan key:generate --force

echo "== Rodando migrations =="
$PHP artisan migrate --force

echo "== Atualizando caches =="
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache

echo "== Primeiro deploy concluido =="
