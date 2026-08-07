# 12 — Sistema de Design (Design System)

## Tom visual
Corporativo, sóbrio e direto — o sistema é uma ferramenta de trabalho
usada rapidamente sob pressão (gestor em movimento, liderado consultando
entre tarefas). Prioridade: clareza sobre decoração.

## Paleta de cores

| Uso | Cor | Hex (base) |
|---|---|---|
| Primária (ações principais, links) | Azul corporativo | `#1E40AF` |
| Secundária (destaques neutros) | Cinza-azulado | `#475569` |
| Fundo geral | Cinza muito claro | `#F8FAFC` |
| Superfície (cards) | Branco | `#FFFFFF` |
| Prioridade Normal | Cinza | `#94A3B8` |
| Prioridade Importante | Azul | `#3B82F6` |
| Prioridade Urgente | Laranja | `#F97316` |
| Prioridade Crítica | Vermelho | `#DC2626` |
| Status Concluída | Verde | `#16A34A` |
| Status Atrasada/Reprovada | Vermelho | `#DC2626` |
| Status Bloqueada | Amarelo | `#CA8A04` |
| Texto principal | Quase preto | `#0F172A` |
| Texto secundário | Cinza médio | `#64748B` |

> Implementar como variáveis no `tailwind.config.js`, nunca usar cores
> soltas hardcoded nos componentes.

## Tipografia
- Fonte: system-ui (nativa, sem carregar fonte externa — reduz peso de
  carregamento em conexão móvel)
- Escala: text-sm (detalhes), text-base (corpo), text-lg (títulos de
  card), text-xl/2xl (títulos de página e números de dashboard)
- Em modo "exibição ampliada" (telas ultrawide/TV), escala sobe um
  degrau inteiro (ver `15-RESPONSIVIDADE.md`)

## Componentes reutilizáveis (Livewire/Blade)
- `<x-badge-status>` — badge colorido conforme tabela de status acima
- `<x-badge-prioridade>` — badge colorido conforme tabela de prioridade
- `<x-card-tarefa>` — card usado tanto em lista quanto em modo TV
- `<x-botao-primario>` / `<x-botao-secundario>` / `<x-botao-perigo>`
  (ex: reprovar, cancelar)
- `<x-timeline-historico>` — linha do tempo de eventos da tarefa
- `<x-avatar-usuario>` — iniciais do nome em círculo colorido
- `<x-empty-state>` — estado vazio ("nenhuma tarefa por aqui")

## Iconografia
Usar um único pacote de ícones (ex: Heroicons, nativo do ecossistema
Tailwind) — nunca misturar bibliotecas de ícones diferentes entre telas.

## Princípios de microcopy (textos da interface)
- Sempre em português claro, direto, sem jargão técnico
- Mensagens de erro dizem o que fazer, não só o que deu errado
- Nenhuma mensagem de reprovação deve soar punitiva — foco no que
  precisa ser corrigido

