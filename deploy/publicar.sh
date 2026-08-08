#!/bin/bash
# Script de deploy — executar após git pull em produção
# (manualmente, via cron, ou automaticamente pelo .cpanel.yml)
# Ver docs/24-PIPELINE-DE-DEPLOY.md

set -e  # interrompe o script se qualquer comando falhar
cd "$(dirname "$0")/.."

# Sem .env nao ha como subir a aplicacao — aborta com orientacao clara
if [ ! -f .env ]; then
  echo "ERRO: arquivo .env nao encontrado."
  echo "Crie o .env de producao no Gerenciador de Arquivos do cPanel"
  echo "(base: .env.example, bloco 'Producao') e rode o deploy de novo."
  exit 1
fi

# Hospedagem compartilhada: evita falha de memoria no composer
export COMPOSER_MEMORY_LIMIT=-1

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
$PHP $COMPOSER_BIN install --no-dev --optimize-autoloader --ignore-platform-req=php

echo "== Rodando migrations =="
$PHP artisan migrate --force

echo "== Atualizando caches =="
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache

echo "== Reiniciando fila =="
$PHP artisan queue:restart

echo "== Deploy concluído =="
