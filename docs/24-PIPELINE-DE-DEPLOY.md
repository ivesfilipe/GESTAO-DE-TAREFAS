# 24 — Pipeline de Deploy

## Fonte da verdade
Repositório GitHub, branch `main` reflete sempre o que deve estar em
produção.

## Método 1 — Script de deploy local (recomendado)

```bash
./deploy/remote-deploy.sh
```

O script faz em ordem:
1. Tenta disparar git pull via API do cPanel (se `.env.deploy` estiver
   configurado)
2. Se a API estiver bloqueada (WAF/anti-bot no servidor), abre o cPanel
   no navegador automaticamente

### Setup único (uma vez)

**Passo 1 — Token de API:**
1. Acesse `https://tarefas.medicalthermo.com:2083`
2. Vá em **Segurança > API Token**
3. Clique em **Criar**, dê o nome `deploy-script`
4. Copie o token gerado

**Passo 2 — Arquivo `.env.deploy` (já está no `.gitignore`):**
```bash
CPANEL_USER=medicalthermo
CPANEL_TOKEN=TOKEN_COPIADO_ACIMA
CPANEL_DOMAIN=tarefas.medicalthermo.com
REPO_PATH=/home/medicalthermo/tarefas.medicalthermo.com
```

> ⚠️ **Limitação conhecida:** O servidor tem WAF/Imunify360 com
> proteção anti-bot que bloqueia chamadas de API via curl. O token
> está configurado, mas o uso efetivo depende de o provedor liberar
> a API para chamadas programáticas. Enquanto isso, o script abre o
> cPanel no navegador como fallback.

### Fluxo completo
1. `git push origin main` (ou merge no GitHub)
2. `./deploy/remote-deploy.sh`
3. O servidor faz git pull + `deploy/publicar.sh` (composer, migrations,
   cache, queue restart)
4. Validar em `https://tarefas.medicalthermo.com`

## Método 2 — Webhook do GitHub → cPanel (ideal, sem bloqueios)

Configuração única que faz deploy automático a cada push no GitHub —
não depende de API token e não é bloqueada por WAF.

### Configurar

1. Acesse `https://tarefas.medicalthermo.com:2083` > **Git Version
   Control**
2. No repositório listado, procure a opção **Webhook** ou **Deploy URL**
3. Copie a URL gerada (formato:
   `https://.../cpsess.../git/versionControl/...`)
4. No GitHub, vá em **Settings > Webhooks > Add webhook**
   - Payload URL: cole a URL copiada
   - Content type: `application/json`
   - Event: `Just the push event`
   - Clique **Add webhook**

A partir daí, todo `git push origin main` dispara automaticamente o
deploy no servidor.

## Método 3 — Git Version Control pelo painel (manual)

1. Acesse `https://tarefas.medicalthermo.com:2083`
2. Abra **Git Version Control**
3. No repositório listado, clique em **Update from Remote** ou
   **Deploy HEAD Commit**
4. O `.cpanel.yml` fará o resto automaticamente

## Passo a passo do deploy
1. Código é revisado e mesclado (merge) em `main` no GitHub
2. No servidor, via Git Version Control do cPanel (ou script SSH),
   executa `git pull origin main` dentro de
   `/home/medicalthermo/tarefas.medicalthermo.com`
3. Script `/deploy/publicar.sh` é executado automaticamente após o
   pull, rodando nesta ordem:
   ```
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan queue:restart
   ```
4. Validação manual pós-deploy: abrir `tarefas.medicalthermo.com` e
   confirmar que a aplicação responde sem erro 500

## Regras invioláveis
- Nunca rodar `migrate:fresh` em produção, sob nenhuma circunstância —
  apaga dados reais
- Nunca fazer deploy direto na branch `main` sem passar por `develop`
  primeiro, exceto correções emergenciais de bug crítico
  (`hotfix/`)
- Testes automatizados devem passar antes de qualquer merge em `main`
  (ver `20-ESTRATEGIA-DE-TESTES.md`)
- Deploy só ocorre depois que a fase atende à
  `22-DEFINITION-OF-DONE.md`

## Rollback
Em caso de falha após deploy:
1. Identificar o último commit estável em `main` (via `git log`)
2. `git reset --hard <hash-do-commit-estavel>` no servidor
   (⚠️ apenas no diretório da aplicação, nunca no banco)
3. Repetir os passos de cache/queue do script de deploy
4. Se a falha envolveu migration de banco, avaliar necessidade de
   `php artisan migrate:rollback` — fazer isso apenas com backup do
   banco confirmado (ver `26-MONITORAMENTO-E-LOGS.md`)
5. Registrar o incidente em `STATUS-DO-PROJETO.md`, seção "Pendências
   e bloqueios conhecidos"

## Branches
- `main`: sempre estável, reflete produção
- `develop`: trabalho em andamento, integração das fases
- `fase-N-nome-da-fase`: branch de trabalho de cada fase grande,
  mesclada em `develop` e depois em `main` quando testada
- `hotfix/descricao`: correção emergencial direto sobre `main`

