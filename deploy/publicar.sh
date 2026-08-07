#!/bin/bash
# Script de deploy — executar após git pull em produção
# (manualmente, via cron, ou automaticamente pelo .cpanel.yml)
# Ver docs/24-PIPELINE-DE-DEPLOY.md

set -e  # interrompe o script se qualquer comando falhar
cd "$(dirname "$0")/.."

# PHP 8.3 (EasyApache 4); cai no php do PATH se nao existir
if [ -x /opt/cpanel/ea-php83/root/usr/bin/php ]; then
  PHP=/opt/cpanel/ea-php83/root/usr/bin/php
else
  PHP=$(command -v php)
fi

# Composer global do cPanel; senao tenta o phar padrao
if command -v composer >/dev/null 2>&1; then
  COMPOSER_BIN=$(command -v composer)
else
  COMPOSER_BIN=/usr/local/bin/composer
fi

echo "== Instalando dependências (produção, sem dev) =="
$PHP $COMPOSER_BIN install --no-dev --optimize-autoloader

echo "== Rodando migrations =="
$PHP artisan migrate --force

echo "== Atualizando caches =="
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache

echo "== Reiniciando fila =="
$PHP artisan queue:restart

echo "== Deploy concluído =="
