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

echo "== Ativacao do ZDR (one-time) =="
ZDR_FLAG="storage/app/.zdr_activated"
if [ ! -f "$ZDR_FLAG" ]; then
  if grep -q "^GROQ_ZDR_CONFIRMED=" .env; then
    sed -i 's/^GROQ_ZDR_CONFIRMED=.*/GROQ_ZDR_CONFIRMED=true/' .env
  else
    echo "GROQ_ZDR_CONFIRMED=true" >> .env
  fi
  touch "$ZDR_FLAG"
  echo "GROQ_ZDR_CONFIRMED=true aplicado no .env de producao."
else
  echo "ZDR ja ativado anteriormente."
fi

echo "== Testando SMTP (one-time) =="
SMTP_FLAG="storage/app/.smtp_test_done"
if [ ! -f "$SMTP_FLAG" ]; then
  $PHP artisan tinker --execute="try { Illuminate\Support\Facades\Mail::raw('Teste SMTP - Gestao de Tarefas', function (\$message) { \$message->to('gestor@medicalthermo.com')->subject('Teste SMTP'); }); echo 'E-mail de teste enviado para gestor@medicalthermo.com\n'; } catch (Throwable \$e) { echo 'ERRO SMTP: '.\$e->getMessage().'\n'; }" > storage/logs/smtp-test.log 2>&1 || true
  touch "$SMTP_FLAG"
  echo "Teste SMTP realizado. Verifique a caixa de entrada de gestor@medicalthermo.com e o arquivo storage/logs/smtp-test.log."
else
  echo "Teste SMTP ja foi realizado."
fi

echo "== Atualizando caches =="
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache

echo "== Link publico do storage (anexos) =="
$PHP artisan storage:link || true

echo "== Reiniciando fila =="
$PHP artisan queue:restart

echo "== Deploy concluído =="
