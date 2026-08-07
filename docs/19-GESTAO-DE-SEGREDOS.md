# 19 — Gestão de Segredos

## O que é segredo neste projeto
- Senha do usuário MySQL (`medicalthermo_gestor`)
- `APP_KEY` do Laravel (chave de criptografia da aplicação)
- Credenciais SMTP (e-mail de convite)
- Chave SSH usada para deploy

## Onde os segredos vivem
- Exclusivamente no arquivo `.env` do servidor de produção
- `.env` está listado em `.gitignore` — NUNCA deve ser commitado
- Um `.env.example` (sem valores reais, só os nomes das variáveis)
  deve existir versionado, como referência de quais variáveis a
  aplicação espera

## Regras
- Nenhum segredo é solicitado pelo agente de IA fora do momento exato
  de configuração do `.env`
- Nenhum segredo aparece em: commits, documentação, `STATUS-DO-PROJETO.md`,
  `CHANGELOG.md`, ou mensagens de log
- Se um segredo for exposto acidentalmente em qualquer lugar, ele deve
  ser rotacionado (trocado) imediatamente, não apenas removido do lugar
  onde apareceu

## Rotação
- Sem política de rotação automática nesta V1 (equipe pequena)
- Rotação manual recomendada se: alguém com acesso à infraestrutura sair
  da empresa, ou se houver qualquer suspeita de vazamento

## Variáveis esperadas no `.env` (referência)
```
APP_NAME=
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://tarefas.medicalthermo.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medicalthermo_gestao_de_tarefas
DB_USERNAME=medicalthermo_gestor
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="${APP_NAME}"

QUEUE_CONNECTION=database
```

