<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova tarefa atribuída</title>
    <style>
        body { font-family: Arial, sans-serif; color: #334155; line-height: 1.5; }
        .container { max-width: 600px; margin: 0 auto; padding: 24px; }
        h1 { font-size: 20px; color: #083048; }
        .task { border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin: 16px 0; }
        .title { font-weight: bold; color: #0f172a; margin-bottom: 8px; font-size: 16px; }
        .meta { font-size: 14px; color: #64748b; }
        .priority { font-weight: bold; }
        .priority-critica { color: #dc2626; }
        .priority-urgente { color: #ea580c; }
        .priority-importante { color: #ca8a04; }
        .priority-normal { color: #2563eb; }
        .button { display: inline-block; margin-top: 16px; padding: 12px 20px; background: #1880C0; color: #ffffff; border-radius: 6px; text-decoration: none; font-weight: bold; }
        .footer { margin-top: 24px; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Olá, {{ $user->name }}</h1>
        <p>Uma nova tarefa foi atribuída a você no sistema de gestão de tarefas:</p>

        <div class="task">
            <div class="title">{{ $task->title }}</div>
            <div class="meta">
                <strong>Prioridade:</strong>
                <span class="priority priority-{{ $task->priority }}">{{ ucfirst($task->priority) }}</span><br>

                @if($task->due_at)
                    <strong>Prazo:</strong> {{ $task->due_at->timezone($user->timezone ?? 'America/Sao_Paulo')->format('d/m/Y H:i') }}<br>
                @endif

                @if($task->description)
                    <strong>Descrição:</strong><br>
                    {{ $task->description }}
                @endif
            </div>

            <a href="{{ url('/tarefas/'.$task->id) }}" class="button">Ver tarefa</a>
        </div>

        <p class="footer">
            E-mail automático do Gestão de Tarefas — MedicalThermo.<br>
            Para não receber mais este tipo de notificação, entre em contato com o gestor.
        </p>
    </div>
</body>
</html>
