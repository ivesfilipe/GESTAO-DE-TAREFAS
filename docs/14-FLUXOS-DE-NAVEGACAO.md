# 14 — Fluxos de Navegação

## Fluxo: Gestor cria tarefa rapidamente
```
Login → Painel do Gestor → [botão flutuante "+"] → Nova Tarefa
  → preenche campos → Salvar → volta para Painel (com confirmação
    visual breve, tipo toast) 
```

## Fluxo: Gestor aprova/reprova
```
Painel do Gestor → card "Aguardando minha aprovação" → Lista filtrada
  → Detalhe da Tarefa → botão Aprovar ou Reprovar
    → (se reprovar) Modal de categoria + motivo → Confirmar
  → volta para Lista filtrada (item sai da lista)
```

## Fluxo: Liderado executa e conclui tarefa
```
Login → Minhas Tarefas → toca no card → Detalhe da Tarefa
  → adiciona comentário/anexo (quantas vezes precisar)
  → botão "Solicitar conclusão" → Confirmar
  → volta para Minhas Tarefas (item some da lista ativa, some para
    Aguardando Aprovação)
```

## Fluxo: Liderado bloqueia tarefa
```
Detalhe da Tarefa → botão "Marcar como bloqueada"
  → Modal (motivo + de quem depende) → Confirmar
  → Detalhe da Tarefa atualizado com badge "Bloqueada"
```

## Fluxo: Liderado solicita mudança de prazo
```
Detalhe da Tarefa → botão "Solicitar novo prazo"
  → Modal (novo valor + justificativa) → Enviar
  → Notificação ao gestor → Gestor aprova/recusa na Detalhe da Tarefa
```

## Fluxo: Convite de novo liderado
```
Gestão de Equipe → botão "Convidar" → informa e-mail → Enviar
  → liderado recebe e-mail → abre link → Definição de Senha
  → login automático → Minhas Tarefas
```

## Regra geral de navegação
- Toda ação de mutação (aprovar, reprovar, bloquear, solicitar) deve
  retornar visualmente ao ponto de origem com confirmação clara —
  nunca deixar o usuário sem saber se a ação teve efeito.
- Nenhum fluxo crítico (aprovação, reprovação, bloqueio) deve ser uma
  ação de um único toque acidental — sempre um passo de confirmação
  intermediário (modal), exceto ações de baixo risco como "solicitar
  conclusão".

