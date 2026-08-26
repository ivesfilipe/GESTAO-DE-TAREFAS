# Segurança da IA — Zero Data Retention

## Princípio

Enquanto `GROQ_ZDR_CONFIRMED=false`, **nenhum dado real** de colaboradores, tarefas ou documentos pode ser enviado para APIs externas.

## O que a camada faz

1. **Anonimização**: substitui nomes, e-mails e títulos por tokens (`[USER_NAME_1]`, `[TASK_TITLE_123]`).
2. **Bloqueio**: rejeita texto contendo e-mails, CPFs ou telefones.
3. **Logging**: registra provider, modelo, tokens e status em `ai_usage_logs`.

## Como confirmar ZDR

Só altere `GROQ_ZDR_CONFIRMED=true` após:
- Revisar contrato/DPA do provider.
- Obter autorização explícita do responsável pela proteção de dados.
- Documentar a decisão no ADR correspondente.

## Nunca faça

- Armazenar chaves de API no Git ou no frontend.
- Enviar dados pessoais sem anonimização com ZDR não confirmado.
- Permitir que a IA execute ações de escrita sem confirmação humana.
