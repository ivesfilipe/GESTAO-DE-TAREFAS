<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suas tarefas abertas</title>
    <style>
        body { font-family: Arial, sans-serif; color: #334155; line-height: 1.5; }
        .container { max-width: 600px; margin: 0 auto; padding: 24px; }
        h1 { font-size: 20px; color: #083048; }
        .task { border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 12px; }
        .title { font-weight: bold; color: #0f172a; margin-bottom: 4px; }
        .meta { font-size: 13px; color: #64748b; }
        .priority { font-weight: bold; }
        .priority-critica { color: #dc2626; }
        .priority-urgente { color: #ea580c; }
        .priority-importante { color: #ca8a04; }
        .priority-normal { color: #2563eb; }
        .footer { margin-top: 24px; font-size: 12px; color: #94a3b8; }
        a { color: #1880C0; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Olá, {{ $user->name }}</h1>
        <p>Você tem {{ $tasks->count() }} tarefa(s) aberta(s) no sistema de gestão de tarefas:</p>

        @foreach($tasks as $task)
            <div class="task">
                <div class="title">{{ $task->title }}</div>
                <div class="meta">
                    Status: {{ str_replace('_', ' ', $task->status) }}<br>
                    Prioridade: <span class="priority priority-{{ $task->priority }}">{{ ucfirst($task->priority) }}</span><br>
                    @if($task->due_at)
                        Prazo: {{ $task->due_at->timezone($user->timezone ?? 'America/Sao_Paulo')->format('d/m/Y H:i') }}<br>
                    @endif
                    <a href="{{ url('/tarefas/'.$task->id) }}">Ver tarefa</a>
                </div>
            </div>
        @endforeach

        <p class="footer">
            Este é um resumo automático do Gestão de Tarefas — MedicalThermo.<br>
            Para não receber mais este e-mail, entre em contato com o gestor.
        </p>
    </div>
</body>
</html>
