# 18 — Checklist de Segurança (OWASP Básico)

Aplicar em toda fase, revisar por completo na Fase 11 (polimento).

- [ ] **Injeção SQL**: usar exclusivamente Eloquent/Query Builder com
  binding de parâmetros — nunca concatenar SQL cru com input do usuário
- [ ] **XSS (Cross-Site Scripting)**: usar `{{ }}` do Blade (escapa por
  padrão); nunca usar `{!! !!}` com conteúdo vindo de usuário (ex:
  comentários, título de tarefa)
- [ ] **CSRF**: todos os formulários e requisições Livewire usam token
  CSRF nativo do Laravel — não desabilitar
- [ ] **Upload malicioso de anexos**: validar mime type real do
  arquivo (não confiar só na extensão), limitar tipos a
  imagem/PDF, renomear arquivo no storage (não usar nome original como
  caminho), servir anexos por rota autenticada/autorizada, nunca
  diretório público sem controle de acesso quando o conteúdo for
  sensível
- [ ] **Controle de acesso quebrado**: toda rota valida autorização via
  Policy (ver `16-SEGURANCA-PERMISSOES.md`), nunca apenas por ocultar
  UI
- [ ] **Exposição de dados sensíveis**: senha nunca retorna em nenhuma
  resposta (usar `$hidden` no Model User); logs de erro não podem
  expor credenciais do `.env`
- [ ] **Configuração incorreta de segurança**: `APP_DEBUG=false` em
  produção sempre; `.env` fora do diretório público, nunca acessível
  via URL
- [ ] **Dependências desatualizadas**: rodar `composer audit`
  periodicamente; manter Laravel e pacotes em versões com suporte
  ativo
- [ ] **Rate limiting**: aplicar em login e em rotas de criação de
  recursos sensíveis contra abuso
- [ ] **HTTPS obrigatório**: forçar redirecionamento HTTP → HTTPS em
  produção (SSL via Let's Encrypt já disponível no cPanel)
- [ ] **Logs sem dado sensível**: nunca logar senha, token de convite,
  ou conteúdo de anexo

## Regra de commit
Nenhum Pull Request/merge para `main` deve ser aceito sem confirmar
mentalmente esta checklist para as mudanças daquela entrega.

