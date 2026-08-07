# 15 — Guia de Responsividade

O sistema deve funcionar perfeitamente em TODOS os tamanhos abaixo,
sem exceção, para toda funcionalidade construída em qualquer fase.

## Breakpoints de referência (Tailwind, mobile-first)
| Nome | Largura | Dispositivo típico |
|---|---|---|
| base (sem prefixo) | a partir de 360px | Celular |
| sm | 640px+ | Celular grande / phablet |
| md | 768px+ | Tablet |
| lg | 1024px+ | Notebook pequeno |
| xl | 1280px+ | Desktop padrão |
| 2xl | 1536px+ | Desktop grande |
| 2xl+ customizado | 2560px+ | Ultrawide / TV de escritório |

## Regras por breakpoint

**Celular (base a sm)**
- Navegação principal em menu inferior fixo ou hambúrguer
- Listas de tarefas em formato de card empilhado (nunca tabela com
  scroll horizontal)
- Botão de ação principal ("Nova tarefa") fixo e sempre visível
- Formulários em coluna única, campos grandes o suficiente para toque

**Tablet (md)**
- Listas podem adotar grid de 2 colunas de cards
- Modais ocupam ~80% da largura, não tela cheia

**Notebook/Desktop (lg a xl)**
- Listas de tarefas podem virar tabela real com colunas (status,
  prioridade, responsável, prazo)
- Dashboard do gestor em grid de múltiplos cartões lado a lado
- Barra lateral de navegação fixa (em vez de menu inferior)

**Ultrawide (2xl+)**
- Conteúdo principal usa `max-width` (ex: `max-w-7xl`) centralizado —
  nunca esticar tabelas/formulários por toda a largura da tela
- Dashboard pode aproveitar o espaço extra com mais cartões visíveis
  simultaneamente, não com cartões maiores e vazios

**TV de escritório / modo exibição**
- Dashboard do gestor deve ter uma variante de "modo exibição": fontes
  maiores, foco apenas nos números-chave (atrasadas, urgentes,
  aguardando aprovação, visão por pessoa), sem elementos de interação
  (sem hover, sem botões pequenos)
- Contraste alto, legível a distância de alguns metros
- Ativação desse modo: parâmetro de URL ou toggle simples, não requer
  tela dedicada nova

## Regras técnicas obrigatórias
- Toda tabela de dados tem sua versão em cards para telas pequenas —
  implementar como duas variantes do mesmo componente Blade, alternadas
  por breakpoint CSS, nunca JavaScript de detecção de dispositivo
- Testar manualmente (ou emulação) cada entrega de fase nos larguras:
  360px, 768px, 1366px, 1920px, 2560px
- Nenhum texto pode quebrar layout ou sair da tela em nenhum breakpoint
- Imagens/anexos em preview devem ser responsivos (max-width: 100%)

