# 11 — Integrações Externas

## E-mail (convite de usuário e, futuramente, notificações)
- **Driver**: SMTP
- **Provedor**: usar o SMTP da própria hospedagem cPanel (conta de
  e-mail do domínio, ex: sistema@medicalthermo.com) nesta V1, para
  evitar dependência de serviço pago externo
- **Configuração**: via `.env` (`MAIL_MAILER=smtp`, `MAIL_HOST`,
  `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`) —
  credenciais obtidas em cPanel → "Email Accounts"
- **Uso nesta V1**: exclusivamente para envio do link de convite/
  definição de senha (RF-01). Notificações de tarefa permanecem in-app.
- **Revisão futura**: se o volume justificar, avaliar serviço dedicado
  (ex: um provedor transacional) para melhor taxa de entrega.

## Armazenamento de anexos
- **Driver nesta V1**: `local`, dentro de
  `storage/app/public/anexos`, com link simbólico público
  (`php artisan storage:link`)
- **Motivo**: hospedagem compartilhada não garante serviço de storage
  externo (S3, etc.) sem custo adicional; volume esperado (5 usuários)
  não justifica isso ainda
- **Limite**: 10MB por arquivo, tipos aceitos: imagem (jpg, png, webp)
  e PDF
- **Revisão futura**: se o disco da hospedagem (30GB conforme plano
  contratado) se aproximar do limite, migrar para storage externo —
  arquitetura do Laravel (`Storage::disk()`) já permite essa troca sem
  reescrever a lógica de upload.

## WhatsApp (fora de escopo da V1)
Mencionado como possível canal futuro de notificação. Não implementar
nesta V1. Quando avaliado, exigirá: conta comercial no WhatsApp
Business API ou serviço intermediário (ex: Twilio), e um novo canal de
Notification do Laravel — a arquitetura de eventos (ver documento 10)
já comporta essa adição sem alterar o núcleo.

## Nenhuma outra integração externa está prevista nesta V1
(sem CRM, sem financeiro, sem calendário externo — ver
`28-BACKLOG-E-FORA-DE-ESCOPO.md`).

