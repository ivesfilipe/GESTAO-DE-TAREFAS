# 32 — Modelo Oficial de Deploy (padrão para todos os projetos)

Modelo validado em produção em ago/2026 neste repositório. Serve como
**referência obrigatória** para qualquer projeto Laravel hospedado em
cPanel compartilhado com LiteSpeed.

## Visão geral

```
git push origin main
        │
        ▼ (cron a cada 1 min, no servidor)
deploy/auto-deploy.sh ── origin/main mudou? ── não ──▶ sai em silêncio
        │ sim
        ▼
git checkout -- . && git pull origin main
        │
        ▼
deploy/publicar.sh
  ├─ composer install --no-dev --optimize-autoloader
  ├─ php artisan migrate --force
  ├─ php artisan config:cache
  ├─ php artisan route:cache
  ├─ php artisan view:cache
  └─ php artisan queue:restart
        │
        ▼
  produção atualizada (log em ~/auto-deploy.log)
```

**GitHub `main` é a fonte da verdade. Nenhum deploy é feito por FTP,
upload manual ou edição de arquivo no servidor.**

## Por que assim — restrições do ambiente (medidas, não suposições)

| Restrição do servidor | Consequência no modelo |
|---|---|
| LiteSpeed (não Apache) | `.htaccess` vale, mas sem diretivas exóticas |
| PHP padrão da conta é 8.2; Laravel 12 exige 8.3 | `AddHandler application/x-httpd-ea-php83 .php` **obrigatório** no topo de `public/.htaccess` e **versionado no git** |
| `exec()`, `shell_exec()` etc. desabilitados no PHP web | Nenhum deploy via endpoint PHP (webhook) funciona. Deploy só em shell: cron ou Terminal |
| API do cPanel (2083) bloqueada por Imunify360 para automação | Nada de deploy via API token; cron dispensa API |
| Sem SSH utilizável | Acesso de emergência: cPanel > Avançado > Terminal |
| Sem Redis | Filas usam driver `database` |

## O kit padrão (4 arquivos, copiar para todo projeto novo)

1. **`deploy/publicar.sh`** — os passos do deploy em si (composer,
   migrations, caches, fila). Detecta o PHP 8.3 em
   `/opt/cpanel/ea-php83/root/usr/bin/php` e exporta
   `COMPOSER_MEMORY_LIMIT=-1`.
2. **`deploy/auto-deploy.sh`** — o loop do cron: `git fetch`, compara
   `HEAD` com `origin/main`, e só publica se mudou. Tem trava anti-
   sobreposição (lock de 15 min) e `GIT_TERMINAL_PROMPT=0` (nunca
   trava pedindo senha).
3. **`public/.htaccess`** — o do Laravel **+ a linha do AddHandler**
   como primeira linha. Sem ela o site inteiro cai em 500
   (`ReflectionFunction::isAnonymous()`).
4. **Cron job no cPanel** — aponta para o `auto-deploy.sh`.

> Opcional: `.cpanel.yml` só é necessário se o repositório for
> gerenciado pelo "Git Version Control" do cPanel. No modelo atual
> (clone manual via Terminal) ele é dispensável.

## Fase 0 — setup de um projeto novo (uma vez, ~20 min)

1. **Subdomínio**: cPanel > Domains > criar `projeto.dominio.com`.
   **Defina o docroot já na criação** para
   `/home/USUARIO/projeto.dominio.com/public` — assim repo e docroot
   ficam na mesma árvore, sem cópias nem caminhos duplicados.
2. **Clone** (cPanel > Avançado > Terminal):
   ```bash
   git clone https://github.com/ORG/REPO.git /home/USUARIO/projeto.dominio.com
   ```
3. **`.env` de produção**: criar na raiz do projeto (base:
   `.env.example`), com `APP_ENV=production`, `APP_DEBUG=false`,
   `APP_URL` correta e o banco escolhido (SQLite em
   `database/database.sqlite` ou MySQL do cPanel).
4. **Primeiro deploy**:
   ```bash
   cd /home/USUARIO/projeto.dominio.com && bash deploy/primeiro-deploy.sh
   ```
   (composer install, `key:generate`, `migrate --force`, caches)
5. **Cron** (cPanel > Cron Jobs > Once Per Minute):
   ```
   /bin/bash /home/USUARIO/projeto.dominio.com/deploy/auto-deploy.sh >> /home/USUARIO/auto-deploy-PROJETO.log 2>&1
   ```
6. **Primeiro usuário** (via Terminal, nunca seeder de teste):
   ```bash
   /opt/cpanel/ea-php83/root/usr/bin/php artisan tinker --execute="echo App\Models\User::create(['name'=>'...','email'=>'...','password'=>bcrypt('...'),'role'=>'gestor','invited_at'=>now(),'activated_at'=>now()])->id;"
   ```
7. **Validação** (checklist abaixo).

## Dia a dia

1. Desenvolver local, testes passando (`php artisan test`).
2. `git push origin main`.
3. Esperar ~1 minuto. Validar o site.
4. Se algo falhar: `tail -30 ~/auto-deploy-PROJETO.log` no Terminal.

## Validação pós-deploy (sempre)

```bash
curl -s -o /dev/null -w "%{http_code}\n" https://DOMINIO/up      # 200
curl -s -o /dev/null -w "%{http_code}\n" https://DOMINIO/login   # 200
curl -s -o /dev/null -w "%{http_code}\n" https://DOMINIO/robots.txt  # 200 (estático)
```
- `/up` 200 + estático 200 + rota dinâmica 200 = deploy saudável.
- Estático 200 + PHP 500 = `.htaccess` perdeu o AddHandler (PHP caiu
  para 8.2).
- Rota dinâmica 404 com `/up` 200 = cache de rotas velho; rodar
  `bash deploy/publicar.sh` no Terminal.

## Rollback

```bash
cd /home/USUARIO/projeto.dominio.com
/usr/local/cpanel/3rdparty/lib/path-bin/git fetch origin main
/usr/local/cpanel/3rdparty/lib/path-bin/git reset --hard <hash-estavel>
bash deploy/publicar.sh
```
Nunca `migrate:fresh` em produção. Rollback de migration problemática
é feito com migration nova de correção, não com `migrate:rollback`
cego.

## Regras invioláveis

1. **Nunca editar migration que já rodou em produção.** Schema novo =
   migration nova. (Violar isto causou o incidente `users.deleted_at`.)
2. **Nunca depender de `exec()`/webhook PHP** para deploy.
3. **O AddHandler do PHP 8.3 não se remove do `.htaccess`.** Se o
   LiteSpeed reclamar, ajusta-se outra coisa.
4. **Caches são regenerados em todo deploy** — é o que o
   `publicar.sh` faz; não pular etapas.
5. **`APP_DEBUG=false` em produção**, sempre. Debug só se liga
   temporariamente para diagnóstico e se desliga em seguida.
6. **Nada de credenciais em código versionado** — `.env` só existe no
   servidor (ver `19-GESTAO-DE-SEGREDOS.md`).

## Checklist de adoção em projeto novo

- [ ] `deploy/primeiro-deploy.sh`, `deploy/publicar.sh`, `deploy/auto-deploy.sh` copiados
- [ ] `public/.htaccess` com AddHandler na 1ª linha
- [ ] Subdomínio criado com docroot apontando para `.../public` do repo
- [ ] `.env` de produção criado no servidor
- [ ] `bash deploy/primeiro-deploy.sh` executado sem erros
- [ ] Cron do auto-deploy criado
- [ ] Primeiro usuário criado e login testado no navegador
- [ ] Push de teste → confirma deploy automático em ~1 min
- [ ] `~/auto-deploy-PROJETO.log` mostra o deploy
