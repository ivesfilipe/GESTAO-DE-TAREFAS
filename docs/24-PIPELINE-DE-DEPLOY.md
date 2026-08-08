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

## Método 2 — Auto-deploy via cron (recomendado, sem bloqueios)

> ⚠️ **Por que não webhook?** O PHP web do servidor tem `exec()`,
> `shell_exec()` etc. desabilitados (`disable_functions`) — qualquer
> endpoint PHP de deploy morre ao tentar rodar `git`. Confirmado em
> ago/2026: 6 entregas do webhook, todas 500. O cron roda em shell
> puro, sem essa restrição.

O script `deploy/auto-deploy.sh` roda a cada minuto via cron: se
`origin/main` tiver commit novo, faz `git pull` + `deploy/publicar.sh`
automaticamente. Não depende de API token, webhook nem WAF.

### Configurar (uma vez)

1. Acesse `https://tarefas.medicalthermo.com:2083` > **Cron Jobs**
2. Em "Add New Cron Job", selecione "Once Per Minute" (`* * * * *`)
   e use o comando:
   ```
   /bin/bash /home/medicalthermo/home/medicalthermo/tarefas.medicalthermo.com/deploy/auto-deploy.sh >> /home/medicalthermo/auto-deploy.log 2>&1
   ```
3. Salvar. A partir daí, todo `git push origin main` é publicado em
   até ~1 minuto, sem nenhuma ação manual.
4. Log das publicações: `/home/medicalthermo/auto-deploy.log`

### Fallback manual (se o cron falhar)

Terminal do cPanel (Avançado > Terminal):
```bash
cd /home/medicalthermo/home/medicalthermo/tarefas.medicalthermo.com && bash deploy/auto-deploy.sh
```

## Método 3 — Git Version Control pelo painel (manual)

1. Acesse `https://tarefas.medicalthermo.com:2083`
2. Abra **Git Version Control**
3. No repositório listado, clique em **Update from Remote** ou
   **Deploy HEAD Commit**
4. O `.cpanel.yml` fará o resto automaticamente

## Passo a passo do deploy
1. Código é revisado e mesclado (merge) em `main` no GitHub
2. No servidor, via cron (`auto-deploy.sh`) ou Git Version Control do
   cPanel, executa `git pull origin main` dentro de
   `/home/medicalthermo/home/medicalthermo/tarefas.medicalthermo.com`
   (caminho real do repositório/docroot — o cPanel o criou com o
   prefixo `$HOME` duplicado; **não** usar
   `/home/medicalthermo/tarefas.medicalthermo.com`, que é uma cópia
   secundária sem uso)
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

