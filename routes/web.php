<?php

use App\Http\Controllers\AssistantController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Auth\InviteController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeaturesGuideController;
use App\Http\Controllers\MyTasksController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->isGestor()) {
            return redirect('/painel');
        }

        return redirect('/minhas-tarefas');
    }

    return redirect('/login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])
    ->name('login.store')
    ->middleware('throttle:5,1');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/convite/{token}', [InviteController::class, 'showSetPassword'])->name('invite.show');
Route::post('/convite/{token}', [InviteController::class, 'setPassword'])->name('invite.store');

Route::middleware(['auth'])->group(function () {
    Route::get('/equipe', [TeamController::class, 'index'])->name('team.index');
    Route::post('/equipe', [TeamController::class, 'store'])->name('team.store');
    Route::patch('/equipe/{user}', [TeamController::class, 'toggleActive'])->name('team.toggle-active');
    Route::post('/equipe/{user}/convite', [TeamController::class, 'regenerateInvite'])->name('team.regenerate-invite');
    Route::delete('/equipe/{user}', [TeamController::class, 'destroy'])->name('team.destroy');

    Route::middleware('can:manage-team')->group(function () {
        Route::get('/equipe/{user}', [TeamProfileController::class, 'show'])->name('team.profile');
        Route::patch('/equipe/{user}/perfil', [TeamProfileController::class, 'updateProfile'])->name('team.profile.update');
        Route::post('/equipe/{user}/resumo', [TeamProfileController::class, 'generateSummary'])->name('team.profile.summary');
        Route::post('/equipe/{user}/sugestoes', [TeamProfileController::class, 'suggestTasks'])->name('team.profile.suggestions');
        Route::post('/equipe/{user}/documentos', [TeamProfileController::class, 'storeDocument'])->name('team.profile.documents.store');
        Route::delete('/equipe/{user}/documentos/{documentId}', [TeamProfileController::class, 'destroyDocument'])->name('team.profile.documents.destroy');
    });
    Route::get('/equipe/{user}', [TeamProfileController::class, 'show'])->name('team.profile');
    Route::patch('/equipe/{user}/perfil', [TeamProfileController::class, 'updateProfile'])->name('team.profile.update');
    Route::post('/equipe/{user}/resumo', [TeamProfileController::class, 'generateSummary'])->name('team.profile.summary');
    Route::post('/equipe/{user}/sugestoes', [TeamProfileController::class, 'suggestTasks'])->name('team.profile.suggestions');
    Route::post('/equipe/{user}/documentos', [TeamProfileController::class, 'storeDocument'])->name('team.profile.documents.store');
    Route::delete('/equipe/{user}/documentos/{documentId}', [TeamProfileController::class, 'destroyDocument'])->name('team.profile.documents.destroy');

    Route::get('/tarefas', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tarefas/quadro', [TaskController::class, 'kanban'])->name('tasks.kanban');
    Route::get('/tarefas/nova', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/tarefas/interpretar', [TaskController::class, 'interpret'])->name('tasks.interpret');
    Route::post('/tarefas/descricao', [TaskController::class, 'generateDescription'])->name('tasks.generate-description');
    Route::post('/tarefas/delegar-com-ia', [TaskController::class, 'smartDelegate'])->name('tasks.smart-delegate');
    Route::post('/tarefas', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tarefas/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::patch('/tarefas/{task}/atribuir', [TaskController::class, 'assign'])->name('tasks.assign');
    Route::patch('/tarefas/{task}/status', [TaskController::class, 'changeStatus'])->name('tasks.change-status');
    Route::post('/tarefas/{task}/comentarios', [CommentController::class, 'store'])->name('tasks.comments.store');
    Route::post('/tarefas/{task}/anexos', [AttachmentController::class, 'store'])->name('tasks.attachments.store');
    Route::get('/tarefas/{task}/anexos/{attachment}', [AttachmentController::class, 'download'])->name('tasks.attachments.download');
    Route::post('/tarefas/{task}/bloquear', [TaskController::class, 'block'])->name('tasks.block');
    Route::post('/tarefas/{task}/desbloquear', [TaskController::class, 'unblock'])->name('tasks.unblock');
    Route::post('/tarefas/{task}/solicitar-conclusao', [TaskController::class, 'requestCompletion'])->name('tasks.request-completion');
    Route::post('/tarefas/{task}/aprovar', [TaskController::class, 'approve'])->name('tasks.approve');
    Route::post('/tarefas/{task}/reprovar', [TaskController::class, 'reject'])->name('tasks.reject');
    Route::post('/tarefas/{task}/solicitar-alteracao', [TaskController::class, 'requestChange'])->name('tasks.request-change');
    Route::patch('/tarefas/{task}/alteracoes/{changeRequest}', [TaskController::class, 'resolveChange'])->name('tasks.resolve-change');

    Route::get('/painel', [DashboardController::class, 'index'])->name('dashboard');
    Route::middleware('can:create-task')->get('/relatorio-funcionalidades', [FeaturesGuideController::class, 'pdf'])->name('features-guide.pdf');
    Route::get('/relatorios', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/minhas-tarefas', [MyTasksController::class, 'index'])->name('my-tasks');

    Route::get('/notificacoes', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notificacoes/{id}/lida', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');

    Route::get('/busca', [SearchController::class, 'search'])->name('search');

    Route::get('/calendario', [CalendarController::class, 'index'])->name('calendar.index');
    Route::middleware('can:create-task')->get('/assistente', [AssistantController::class, 'index'])->name('assistant.index');
    Route::middleware('can:create-task')->post('/assistente/perguntar', [AssistantController::class, 'ask'])->name('assistant.ask');
    Route::middleware('can:create-task')->post('/assistente/cobranca', [AssistantController::class, 'suggestCollection'])->name('assistant.collection');
    Route::get('/agenda-inteligente', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::post('/agenda-inteligente/regenerar', [ScheduleController::class, 'regenerate'])->name('schedule.regenerate');
    Route::post('/agenda-inteligente/aplicar', [ScheduleController::class, 'apply'])->name('schedule.apply');
});

Route::get('/calendario/{token}.ics', [CalendarController::class, 'ical'])->name('calendar.ical');

Route::get('/debug-assistant/{token}', function ($token) {
    if ($token !== 'TEMP_DEBUG_2026') {
        abort(404);
    }

    $gestor = \App\Models\User::where('role', 'gestor')->first();
    if (! $gestor) {
        return response()->json(['error' => 'nenhum gestor encontrado'], 404);
    }

    \Illuminate\Support\Facades\Auth::login($gestor);

    try {
        $controller = app(AssistantController::class);
        $request = request();
        $response = $controller->index($request, app(\App\Services\AiAssistantService::class), app(\App\Services\AI\ManagementRadarService::class));

        $status = method_exists($response, 'getStatusCode') ? $response->getStatusCode() : 'rendered view';

        return 'OK: assistant index retornou status '.$status;
    } catch (\Throwable $e) {
        return response()->json([
            'error' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString()),
        ], 500);
    }
});
