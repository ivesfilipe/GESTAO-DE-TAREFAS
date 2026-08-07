# 01 — Requisitos Funcionais

Cada requisito tem um identificador (RF-XX) usado em testes, commits e
no dicionário de dados para rastreabilidade.

## Identidade e Acesso
- **RF-01**: Gestor cadastra liderado via e-mail; sistema envia convite
  com link de definição de senha. Usuário nunca se autocadastra.
  - Critério de aceite: liderado recebe e-mail, define senha, consegue
    logar; link expira após uso ou após 48h.
- **RF-02**: Sistema possui dois perfis: Gestor e Liderado, com
  permissões distintas (ver `16-SEGURANCA-PERMISSOES.md`).
- **RF-03**: Usuário nunca é deletado fisicamente, apenas desativado
  (soft delete). Tarefas abertas de um usuário desativado devem ser
  sinalizadas ao gestor para reatribuição.

## Criação de Tarefas
- **RF-04**: Gestor cria tarefa em poucos segundos com campos mínimos:
  título/descrição, responsável (opcional no momento da criação),
  prioridade, prazo (data E hora).
  - Critério de aceite: criação completa em no máximo 2 toques a partir
    da tela inicial no celular.
- **RF-05**: Prioridades fixas e limitadas a 4 níveis: Normal,
  Importante, Urgente, Crítica.
- **RF-06**: Tarefa sem responsável definido entra no status
  "Não atribuída" e não conta como carga de trabalho de ninguém.

## Ciclo de Vida da Tarefa
- **RF-07**: Status principal segue a sequência: Não atribuída → Nova →
  Recebida → Em andamento → Aguardando aprovação → Concluída.
- **RF-08**: Estados de exceção: Bloqueada, Reprovada, Cancelada.
- **RF-09**: Liderado nunca encerra definitivamente uma tarefa sozinho —
  ele solicita conclusão, o gestor aprova ou reprova.
- **RF-10**: Cancelamento de tarefa é sempre soft delete; a tarefa
  permanece no histórico, some apenas das listas ativas.
- **RF-11**: Apenas o gestor altera prazo/prioridade diretamente. O
  liderado pode solicitar mudança, com justificativa obrigatória,
  sujeita à aprovação do gestor.

## Comunicação
- **RF-12**: Cada tarefa possui uma thread de comentários própria.
- **RF-13**: Comentários não podem ser editados nem apagados após
  enviados. Correções exigem novo comentário.
- **RF-14**: É possível anexar arquivos (imagem ou PDF, até 10MB por
  arquivo) a uma tarefa ou comentário.

## Aprovação e Reprovação
- **RF-15**: Liderado solicita conclusão → status muda para
  "Aguardando aprovação".
- **RF-16**: Gestor aprova (→ Concluída) ou reprova com motivo
  categorizado obrigatório: "não atende ao solicitado" | "escopo
  mudou" | "informação incompleta" | "outro".
- **RF-17**: Se uma tarefa fica em "Aguardando aprovação" por mais de 3
  dias sem resposta do gestor, o sistema envia cobrança automática ao
  gestor (não muda o status sozinho).

## Bloqueio
- **RF-18**: Liderado pode marcar tarefa como "Bloqueada", com motivo
  obrigatório e indicação de quem/o que ela depende.
- **RF-19**: Tempo bloqueado não conta como atraso do liderado
  responsável, mas permanece visível ao gestor no painel.

## Histórico e Auditoria
- **RF-20**: Todo evento relevante da tarefa gera um registro imutável
  de histórico: criação, atribuição, alteração de prazo/prioridade,
  comentários, anexos, mudanças de status, conclusão, aprovador, motivo
  de reprovação.

## Painel do Gestor
- **RF-21**: Dashboard exibe: total de atrasadas, urgentes, que vencem
  hoje, aguardando aprovação do gestor.
- **RF-22**: Dashboard exibe visão por pessoa: tarefas abertas e
  atrasadas por liderado.
- **RF-23**: Uma tarefa é considerada "atrasada" automaticamente quando
  passa da data/hora do prazo, calculada no fuso horário do liderado
  responsável.
- **RF-24**: Sistema notifica o liderado 2h antes do vencimento do
  prazo.

## Portal do Liderado
- **RF-25**: Tela inicial do liderado responde "o que eu preciso fazer
  agora?", agrupando tarefas em: Urgentes, Hoje, Próximas.
- **RF-26**: Liderado só visualiza as próprias tarefas — nunca as de
  outro liderado.

## Notificações
- **RF-27**: Sistema notifica (in-app nesta V1) para: nova tarefa
  atribuída, tarefa marcada urgente/crítica, prazo próximo, tarefa
  atrasada, novo comentário, tarefa reprovada, tarefa aprovada.
- **RF-28**: Tarefa Crítica sem mudança de status por mais de 4h gera
  alerta automático ao gestor (não ao liderado).

## Métricas
- **RF-29**: Apenas reprovações categorizadas como "não atende ao
  solicitado" contam para métricas de desempenho do liderado.

