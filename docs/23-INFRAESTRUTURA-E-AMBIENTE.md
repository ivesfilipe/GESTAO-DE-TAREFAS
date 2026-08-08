# 23 — Infraestrutura e Ambiente

## Hospedagem
- Tipo: hospedagem compartilhada, painel cPanel
- Servidor: `br65-cp`
- Sistema operacional: Linux
- Pacote de hospedagem: Plano 30GB

## Stack de servidor
- LiteSpeed (LSAPI) — **não é Apache** (o doc original dizia Apache +
  PHP-FPM; corrigido em ago/2026 após diagnóstico em produção)
- PHP: versão 8.3 forçada via `AddHandler application/x-httpd-ea-php83 .php`
  no topo de `public/.htaccess` — **linha obrigatória**: a conta usa
  PHP 8.2 por padrão, e o Laravel 12 quebra nele
  (`ReflectionFunction::isAnonymous()` só existe no PHP 8.3+)
- PHP web tem `exec()`, `shell_exec()` etc. desabilitados
  (`disable_functions`) — nenhum deploy via endpoint PHP funciona;
  deploys rodam apenas em shell (cron / Terminal do cPanel)
- API do cPanel (porta 2083) bloqueada para automação pelo Imunify360
- Banco de dados: MariaDB 10.11.18 (compatível MySQL)
- SSL: Let's Encrypt / AutoSSL via cPanel

## Aplicação
- Caminho no servidor (repositório git **e** docroot real):
  `/home/medicalthermo/home/medicalthermo/tarefas.medicalthermo.com`
  — sim, com o prefixo duplicado: o cPanel Git Version Control
  prepend `$HOME` ao caminho informado na criação do repositório.
  O docroot é o `public/` dessa pasta.
- ⚠️ `/home/medicalthermo/tarefas.medicalthermo.com` (sem o prefixo
  duplicado) é apenas uma cópia secundária criada por engano na
  implantação inicial — **o site não é servido dela**
- Subdomínio: `tarefas.medicalthermo.com`
- Banco de dados: `medicalthermo_gestao_de_tarefas`
- Usuário do banco: `medicalthermo_gestor` (todos os privilégios)
- Senha do banco: definida apenas no `.env` de produção (ver
  `19-GESTAO-DE-SEGREDOS.md`)

## Acesso remoto
- SSH: **indisponível** na prática (sem credenciais de senha/chave
  entregues; o doc original dizia "liberado", não confirmado)
- Acesso de shell real: **cPanel > Avançado > Terminal** (roda como o
  usuário da conta, sem as restrições do PHP web)
- Controle de versão: "Git Version Control" nativo do cPanel disponível

## Ferramentas de build
- Composer: disponível via SSH (sem tela gráfica dedicada neste
  cPanel)
- Node.js/NPM: necessário apenas para build de assets do Tailwind
  (Vite) — verificar disponibilidade via SSH antes da Fase 0; se
  ausente, compilar assets localmente e versionar o build, ou usar
  Tailwind via CDN como alternativa temporária de menor performance

## Cron
- Scheduler do Laravel:
  `* * * * * cd /home/medicalthermo/home/medicalthermo/tarefas.medicalthermo.com && /opt/cpanel/ea-php83/root/usr/bin/php artisan schedule:run >> /dev/null 2>&1`
- Auto-deploy (ver `24-PIPELINE-DE-DEPLOY.md`):
  `* * * * * /bin/bash /home/medicalthermo/home/medicalthermo/tarefas.medicalthermo.com/deploy/auto-deploy.sh >> /home/medicalthermo/auto-deploy.log 2>&1`

## Limites conhecidos do ambiente (a respeitar no código)
- Sem Redis disponível — filas usam driver `database`
- Recursos de CPU/memória compartilhados — evitar processos longos
  síncronos; qualquer processamento pesado deve ir para fila
- Disco: 30GB no total do pacote — anexos devem respeitar o limite de
  10MB por arquivo definido em `01-REQUISITOS-FUNCIONAIS.md`

