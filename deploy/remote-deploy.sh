#!/bin/bash
# Deploy remoto — envia código para o servidor cPanel e executa o script
# de publicação (composer, migrations, cache, queue).
#
# Pré-requisito: git push já feito para origin/main.
#
# Modos de operação (tentados em ordem):
#   1. API do cPanel (requer token em .env.deploy)
#   2. Abre o painel cPanel no navegador (fallback)
#
# Configuração única (uma vez):
#   Crie .env.deploy com:
#     CPANEL_USER=medicalthermo
#     CPANEL_TOKEN=...     # cPanel > Segurança > API Token
#     CPANEL_DOMAIN=tarefas.medicalthermo.com
#     REPO_PATH=/home/medicalthermo/tarefas.medicalthermo.com
#
# Uso:
#   ./deploy/remote-deploy.sh

set -e
cd "$(dirname "$0")/.."

CPANEL_USER="${CPANEL_USER:-medicalthermo}"
CPANEL_DOMAIN="${CPANEL_DOMAIN:-tarefas.medicalthermo.com}"
REPO_PATH="${REPO_PATH:-/home/medicalthermo/tarefas.medicalthermo.com}"
CPANEL_PORT="${CPANEL_PORT:-2083}"

if [ -f .env.deploy ]; then
  set -a
  source .env.deploy
  set +a
fi

CPANEL_URL="https://${CPANEL_DOMAIN}:${CPANEL_PORT}"
GIT_VC_URL="${CPANEL_URL}/cpsess0000000000/frontend/paper_lantern/version_control/index.html"

echo ""
echo "== Gestão de Tarefas — Deploy Remoto =="
echo ""

# ---- Verifica se há alterações pendentes no git local ----
if ! git diff --quiet origin/main..HEAD 2>/dev/null; then
  echo "AVISO: há commits locais ainda não enviados ao GitHub."
  echo "Execute 'git push origin main' primeiro."
  exit 1
fi

# ---- Tenta via API ----
if [ -n "$CPANEL_TOKEN" ]; then
  echo "== Tentando via API do cPanel =="
  API_BASE="${CPANEL_URL}/execute"

  RESPONSE=$(curl -s -k -w "\n%{http_code}" \
    --connect-timeout 10 --max-time 30 \
    -H "Authorization: cpanel ${CPANEL_USER}:${CPANEL_TOKEN}" \
    -H "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36" \
    -H "Accept: application/json" \
    "${API_BASE}/VersionControl/update" \
    -d "repository_root=${REPO_PATH}" \
    -d "branch=main" 2>/dev/null || true)

  HTTP_CODE=$(echo "$RESPONSE" | tail -1)
  BODY=$(echo "$RESPONSE" | sed '$d')

  # Verifica se a resposta é JSON válido (não é página HTML de WAF)
  if echo "$BODY" | python3 -c "import sys,json; json.load(sys.stdin)" 2>/dev/null; then
    ERRORS=$(echo "$BODY" | python3 -c "import sys,json; d=json.load(sys.stdin); print(len(d.get('errors',[])))" 2>/dev/null || echo "0")
    if [ "$ERRORS" = "0" ]; then
      echo "OK — deploy disparado via API com sucesso!"
      echo "O servidor fará git pull + composer install + migrations + cache."
      exit 0
    else
      echo "API retornou erros:"
      echo "$BODY" | python3 -c "import sys,json; d=json.load(sys.stdin); [print(f'  - {e}') for e in d.get('errors',[])]" 2>/dev/null
      echo ""
    fi
  else
    echo "API bloqueada por WAF (proteção anti-bot do servidor)."
    echo ""
  fi
fi

# ---- Fallback: abrir o cPanel no navegador ----
echo "== Abrindo cPanel no navegador =="
echo ""
echo "  URL: ${CPANEL_URL}"
echo ""
echo "No painel:"
echo "  1. Faça login"
echo "  2. Abra 'Git Version Control'"
echo "  3. No repositório 'tarefas.medicalthermo.com', clique em"
echo "     'Update from Remote' ou 'Deploy HEAD Commit'"
echo "  4. O .cpanel.yml executará o script de deploy automaticamente"
echo ""

# Tenta abrir o navegador
if command -v open &>/dev/null; then
  open "${CPANEL_URL}" 2>/dev/null || true
  echo "Navegador aberto. Faça login e siga os passos acima."
else
  echo "Copie a URL acima e cole no navegador."
fi
