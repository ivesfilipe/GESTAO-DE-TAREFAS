# 04 — Personas e Jornadas de Usuário

## Persona 1 — Gestor
Responsável por delegar, acompanhar e aprovar o trabalho da equipe.
Costuma lembrar de pendências em movimento (reunião, fábrica, viagem,
celular na mão) e precisa registrar isso antes de esquecer.

### Jornada principal — "Lembrei de algo, preciso delegar agora"
1. Abre o sistema no celular (app web responsivo).
2. Toca em "Nova tarefa".
3. Digita título/descrição curta.
4. Seleciona responsável (ou deixa em aberto).
5. Define prioridade e prazo (data + hora).
6. Salva — tarefa passa a existir e notificar o responsável.
   - Meta: concluir esse fluxo em até 2 toques além da digitação.

### Jornada secundária — "O que está pendente da minha aprovação?"
1. Abre o dashboard.
2. Vê contador "Aguardando minha aprovação".
3. Abre cada tarefa, lê a resposta do liderado e os anexos.
4. Aprova ou reprova (com motivo categorizado, se reprovar).

### Jornada terciária — "Quem está sobrecarregado ou atrasado?"
1. Abre o dashboard.
2. Consulta visão por pessoa (tarefas abertas/atrasadas por liderado).
3. Usa essa informação para redistribuir ou cobrar pessoalmente.

## Persona 2 — Liderado
Recebe tarefas do gestor, executa, e precisa deixar claro o andamento
sem depender de lembrar tudo de cabeça.

### Jornada principal — "O que eu preciso fazer agora?"
1. Abre o sistema (celular ou desktop).
2. Tela inicial já mostra: Urgentes, Hoje, Próximas.
3. Toca em uma tarefa para ver detalhes.
4. Registra andamento via comentário, anexando evidência se necessário.
5. Quando pronto, toca em "Solicitar conclusão".

### Jornada secundária — "Não consigo avançar nesta tarefa"
1. Abre a tarefa.
2. Marca como "Bloqueada".
3. Informa motivo e de quem depende.
4. Tarefa para de contar como atraso dele, mas gestor é avisado.

### Jornada terciária — "Fui reprovado, preciso corrigir"
1. Recebe notificação de reprovação com motivo.
2. Abre a tarefa, lê o comentário do gestor.
3. Corrige e solicita conclusão novamente.

## Mapa de emoções (para orientar tom de UI/mensagens)
- Gestor em movimento: precisa de **velocidade** e **confirmação
  visual imediata** de que a tarefa foi criada.
- Liderado recebendo tarefa nova: precisa de **clareza** — o que,
  quando, e qual o nível de urgência real.
- Liderado reprovado: momento sensível — mensagens devem ser objetivas
  e não punitivas, focadas no que precisa ser corrigido.

