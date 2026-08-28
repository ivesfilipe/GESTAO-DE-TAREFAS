# 07 — Modelo de Domínio (Conceitual)

Antes do banco de dados: as entidades de negócio e como se relacionam
conceitualmente.

```
Organização (futuro — hoje implícita, única)
   │
   └── Usuário (Gestor | Liderado)
           ├── Gestor gerencia muitos Liderados
           ├── Liderado pertence a zero ou um Gestor
          │
          └── Tarefa
                ├── criada por: Usuário (Gestor)
                ├── atribuída a: Usuário (Liderado) [opcional no início]
                ├── possui: Prioridade
                ├── possui: Status (ciclo de vida)
                ├── possui muitos: Comentário
                │        └── possui muitos: Anexo
                ├── possui muitos: Anexo (direto na tarefa)
                ├── possui muitos: HistoricoEvento
                 ├── possui: SolicitacaoDeAlteracao [0..N]
                 └── define: tipo, critérios de aceitação e evidências

Liderado ── possui um PerfilProfissional
PerfilProfissional ── possui muitos DocumentoDeEquipe ── possui muitos ChunkDeConhecimento
UsoDeIA ── referencia opcionalmente Usuário e registra apenas metadados

Notificação
   ├── pertence a: Usuário (destinatário)
   └── refere-se a: Tarefa (opcional)
```

## Entidades e seus papéis
- **Usuário**: pessoa com acesso ao sistema; tem um papel (Gestor ou
  Liderado); possui fuso horário próprio (usado para cálculo de
  atraso).
- **Tarefa**: unidade central do sistema; carrega todo o ciclo de vida
  descrito em `03-REGRAS-DE-NEGOCIO.md`.
- **Comentário**: mensagem imutável vinculada a uma tarefa, com autor e
  timestamp.
- **Anexo**: arquivo vinculado a uma tarefa ou a um comentário
  específico.
- **HistoricoEvento**: registro imutável de qualquer mudança relevante
  na tarefa (event log) — nunca um campo sobrescrito sem rastro.
- **SolicitacaoDeAlteracao**: pedido do liderado para mudar prazo ou
  prioridade, sujeito à aprovação do gestor (RN-02).
- **Notificação**: mensagem in-app gerada por eventos do sistema.
- **PerfilProfissional**: dados persistentes de função, responsabilidades, objetivos e análise gerada da pessoa liderada.
- **DocumentoDeEquipe/ChunkDeConhecimento**: arquivo privado do liderado e trechos locais usados para recuperação lexical.
- **UsoDeIA**: auditoria técnica de provider, modelo, tokens, duração e status, sem conteúdo de prompt ou resposta.

## Multiplicidades-chave
- Um Usuário Gestor cria muitas Tarefas.
- Um Gestor gerencia muitos Liderados; um Liderado só é visível ao seu gestor.
- Uma Tarefa é atribuída a no máximo um Usuário Liderado por vez.
- Uma Tarefa tem muitos Comentários, muitos Anexos, muitos
  HistoricoEventos.
- Um HistoricoEvento pertence a exatamente uma Tarefa e é imutável.
- Um PerfilProfissional pertence a um Liderado; documentos e chunks desse perfil são privados ao gestor responsável.
