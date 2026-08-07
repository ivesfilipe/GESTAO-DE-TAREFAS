# 02 — Requisitos Não-Funcionais

## Performance
- **RNF-01**: Tela inicial (login e "Minhas tarefas") deve carregar em
  menos de 2 segundos em conexão 4G comum.
- **RNF-02**: Criação de tarefa deve responder em menos de 1 segundo
  após envio do formulário.
- **RNF-03**: Sistema deve operar de forma estável em hospedagem
  compartilhada (recursos limitados de CPU/memória) — evitar consultas
  N+1, usar eager loading, e cache de configuração/rota em produção.

## Disponibilidade
- **RNF-04**: Sistema deve estar disponível durante o horário comercial
  (8h–19h, dias úteis) com tolerância mínima a indisponibilidade — é a
  janela em que gestor e liderados de fato o utilizam.
- **RNF-05**: Deploys devem ocorrer fora do horário de pico de uso
  sempre que possível.

## Escalabilidade
- **RNF-06**: Arquitetura deve suportar crescimento de 5 para até ~50
  usuários sem mudança estrutural (apenas ajuste de recursos de
  hospedagem).
- **RNF-07**: Modelo de dados deve prever hierarquia de mais de 2
  níveis (ex: coordenador entre gestor e liderados) sem necessidade de
  redesenho de tabelas.

## Usabilidade
- **RNF-08**: Toda ação principal (criar tarefa, aprovar, reprovar)
  deve ser alcançável em no máximo 2 toques a partir da tela inicial no
  celular.
- **RNF-09**: Sistema deve ser 100% responsivo — ver
  `15-RESPONSIVIDADE.md`.

## Confiabilidade dos dados
- **RNF-10**: Nenhum dado de histórico pode ser perdido ou sobrescrito
  sem deixar rastro (event log imutável).
- **RNF-11**: Nenhuma exclusão física de tarefa, comentário ou usuário —
  sempre soft delete.

## Segurança
- **RNF-12**: Ver `16, 17, 18, 19` — permissões, autenticação, checklist
  OWASP e gestão de segredos.

## Manutenibilidade
- **RNF-13**: Código deve seguir PSR-12 e convenções Laravel padrão
  (ver `04-PADROES-DE-CODIGO.md` do pacote de infraestrutura).
- **RNF-14**: Toda regra de negócio complexa deve viver em Service
  Classes/Actions, nunca direto no Controller.

## Compatibilidade
- **RNF-15**: Suportar as duas últimas versões estáveis de Chrome,
  Safari e Edge, em desktop e mobile.

