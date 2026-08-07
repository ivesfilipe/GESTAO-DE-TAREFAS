# 24 — Pipeline de Deploy

## Fonte da verdade
Repositório GitHub, branch `main` reflete sempre o que deve estar em
produção.

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

