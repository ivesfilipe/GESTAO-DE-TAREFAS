# 23 — Infraestrutura e Ambiente

## Hospedagem
- Tipo: hospedagem compartilhada, painel cPanel
- Servidor: `br65-cp`
- Sistema operacional: Linux
- Pacote de hospedagem: Plano 30GB

## Stack de servidor
- Apache 2.4.68 + PHP-FPM
- PHP: versão 8.3 (via MultiPHP Manager — atualizado de 8.1 na Fase 0,
  ver ADR-007 em `06-DECISOES-DE-ARQUITETURA-ADR.md`)
- Banco de dados: MariaDB 10.11.18 (compatível MySQL)
- SSL: Let's Encrypt / AutoSSL via cPanel

## Aplicação
- Caminho no servidor: `/home/medicalthermo/tarefas.medicalthermo.com`
- Subdomínio: `tarefas.medicalthermo.com`
- Banco de dados: `medicalthermo_gestao_de_tarefas`
- Usuário do banco: `medicalthermo_gestor` (todos os privilégios)
- Senha do banco: definida apenas no `.env` de produção (ver
  `19-GESTAO-DE-SEGREDOS.md`)

## Acesso remoto
- SSH liberado, chave configurada
- Controle de versão: "Git Version Control" nativo do cPanel disponível

## Ferramentas de build
- Composer: disponível via SSH (sem tela gráfica dedicada neste
  cPanel)
- Node.js/NPM: necessário apenas para build de assets do Tailwind
  (Vite) — verificar disponibilidade via SSH antes da Fase 0; se
  ausente, compilar assets localmente e versionar o build, ou usar
  Tailwind via CDN como alternativa temporária de menor performance

## Cron
- Um único Cron Job apontando para:
  `* * * * * cd /home/medicalthermo/tarefas.medicalthermo.com && php artisan schedule:run >> /dev/null 2>&1`

## Limites conhecidos do ambiente (a respeitar no código)
- Sem Redis disponível — filas usam driver `database`
- Recursos de CPU/memória compartilhados — evitar processos longos
  síncronos; qualquer processamento pesado deve ir para fila
- Disco: 30GB no total do pacote — anexos devem respeitar o limite de
  10MB por arquivo definido em `01-REQUISITOS-FUNCIONAIS.md`

