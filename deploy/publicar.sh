#!/bin/bash
# Script de deploy — executar após git pull em produção
# Ver docs/24-PIPELINE-DE-DEPLOY.md

set -e  # interrompe o script se qualquer comando falhar

echo "== Instalando dependências (produção, sem dev) =="
composer install --no-dev --optimize-autoloader

echo "== Rodando migrations =="
php artisan migrate --force

echo "== Atualizando caches ==" 
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "== Reiniciando fila =="
php artisan queue:restart

echo "== Deploy concluído =="

