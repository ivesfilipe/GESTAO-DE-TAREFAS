# 05 — Arquitetura, Visão Geral

## Diagrama de componentes (texto)

```
[ Navegador / Celular / Tablet / TV ]
              │
              ▼
    [ Laravel App (Blade + Livewire) ]
       │        │           │
       ▼        ▼           ▼
  [ Controllers ] [ Livewire ] [ Form Requests ]
       │
       ▼
  [ Service Classes / Actions ]  ← toda regra de negócio complexa
       │
       ▼
  [ Eloquent Models ]
       │
       ▼
  [ MySQL — medicalthermo_gestao_de_tarefas ]

  [ Laravel Queue (driver database) ] → processa notificações
  [ Laravel Scheduler ] → cron único → verifica atrasos, escalonamento,
                                        cobranças automáticas
   [ Laravel Notifications ] → canal in-app nesta V1 (extensível a
                                 e-mail/WhatsApp depois)

   [ AIService ] → [ AIProviderManager ] → Groq | OpenAI | Ollama | Mock
          │                 │
          └── ZDR + AIUsageLog (metadados) + memória/chunks locais
```

## Camadas do sistema
1. **Apresentação** — Blade + Livewire + Tailwind CSS, renderização
   server-side com interatividade via Livewire (sem exigir API separada)
2. **Aplicação** — Controllers finos, Form Requests para validação,
   Service Classes/Actions para regra de negócio
3. **Domínio** — Eloquent Models representando as entidades (Tarefa,
   Usuário, Comentário, Anexo, HistoricoEvento)
4. **Persistência** — MySQL via Migrations versionadas
5. **Infraestrutura** — hospedagem compartilhada cPanel, deploy via Git

## Por que essa arquitetura (resumo — detalhe em ADRs)
- Servidor é hospedagem compartilhada: evitar dependências pesadas
  (sem Redis, sem containers, sem processos long-running fora do padrão
  PHP-FPM)
- Time não-programador: Laravel + Livewire reduz a necessidade de
  manter frontend e backend como projetos separados
- Crescimento futuro: arquitetura em camadas (Service/Action) permite
  extrair regras de negócio para uma API própria no futuro sem
  reescrever tudo

## Fluxo de uma solicitação típica (criação de tarefa)
1. Gestor preenche formulário (Livewire component)
2. Componente valida via Form Request
3. Action `CriarTarefaAction` executa a regra de negócio e grava via
   Eloquent
4. Evento `TarefaCriada` é disparado
5. Listener dispara notificação in-app ao responsável (se definido)
6. Registro de histórico é criado automaticamente (event log)

## Fronteiras entre módulos (para modularidade futura)
- `Identidade` — usuários, papéis, autenticação
- `Tarefas` — ciclo de vida, prioridade, prazo
- `Comunicação` — comentários, anexos
- `Notificações` — desacoplado, consome eventos dos outros módulos
- `Auditoria` — consome eventos, nunca é consumida por eles
- `Dashboard` — somente leitura dos outros módulos
- `IA` — `AIService` centraliza provider, ZDR e auditoria; `CopilotService` e serviços de perfil/delegação nunca contornam essa fronteira
- `Memória gerencial` — perfis, documentos privados e chunks locais, isolados pelo vínculo gestor-liderado

Módulos futuros (CRM, financeiro, etc.) devem se conectar apenas por
eventos, nunca escrevendo diretamente nas tabelas de outro módulo.
