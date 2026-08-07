# 17 — Política de Autenticação

## Mecanismo
- Autenticação via sessão padrão do Laravel (Laravel Breeze como base)
- Sem autocadastro — usuário só existe se convidado pelo gestor (RF-01)

## Senhas
- Hash via bcrypt (padrão Laravel), nunca texto plano em lugar algum
- Mínimo de 8 caracteres, exigir letra e número
- Sem expiração forçada nesta V1 (equipe pequena, risco controlado);
  reavaliar se a base de usuários crescer

## Convite e definição de senha
- Link de convite é um token único, assinado, com expiração de 48h
- Após uso ou expiração, o token não pode ser reutilizado
- Ao expirar, gestor pode reenviar novo convite

## Sessão
- Timeout de sessão por inatividade: 8h (cobre um dia de trabalho
  completo sem exigir novo login)
- Logout invalida a sessão no servidor, não apenas no cliente

## Recuperação de senha
- Fluxo padrão "esqueci minha senha" via e-mail (reutiliza o mesmo
  driver SMTP definido em `11-INTEGRACOES-EXTERNAS.md`)

## Proteções obrigatórias
- Rate limiting no login (Laravel Throttle) — máximo de 5 tentativas
  por minuto por IP/e-mail
- CSRF token em todos os formulários (padrão Laravel/Blade, não
  desabilitar)
- Nenhuma rota de negócio acessível sem autenticação — middleware
  `auth` aplicado globalmente ao grupo de rotas do sistema

