<?php

namespace App\Providers;

use App\Events\AlteracaoSolicitada;
use App\Events\AnexoAdicionado;
use App\Events\ComentarioAdicionado;
use App\Events\ConclusaoSolicitada;
use App\Events\PrazoAlterado;
use App\Events\PrioridadeAlterada;
use App\Events\StatusAlterado;
use App\Events\TarefaAprovada;
use App\Events\TarefaAtribuida;
use App\Events\TarefaBloqueada;
use App\Events\TarefaCancelada;
use App\Events\TarefaCriada;
use App\Events\TarefaDesbloqueada;
use App\Events\TarefaReprovada;
use App\Listeners\DispatchWebhooksListener;
use App\Listeners\EnviarEmailDeAtribuicaoListener;
use App\Listeners\GravarHistoricoListener;
use App\Listeners\NotificarPartesInteressadasListener;
use App\Models\Task;
use App\Models\TeamMemberDocument;
use App\Models\User;
use App\Policies\TeamMemberPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(TarefaCriada::class, [GravarHistoricoListener::class, 'handleTarefaCriada']);
        Event::listen(TarefaAtribuida::class, [GravarHistoricoListener::class, 'handleTarefaAtribuida']);
        Event::listen(PrioridadeAlterada::class, [GravarHistoricoListener::class, 'handlePrioridadeAlterada']);
        Event::listen(PrazoAlterado::class, [GravarHistoricoListener::class, 'handlePrazoAlterado']);
        Event::listen(StatusAlterado::class, [GravarHistoricoListener::class, 'handleStatusAlterado']);
        Event::listen(ComentarioAdicionado::class, [GravarHistoricoListener::class, 'handleComentarioAdicionado']);
        Event::listen(AnexoAdicionado::class, [GravarHistoricoListener::class, 'handleAnexoAdicionado']);
        Event::listen(TarefaBloqueada::class, [GravarHistoricoListener::class, 'handleTarefaBloqueada']);
        Event::listen(TarefaDesbloqueada::class, [GravarHistoricoListener::class, 'handleTarefaDesbloqueada']);
        Event::listen(ConclusaoSolicitada::class, [GravarHistoricoListener::class, 'handleConclusaoSolicitada']);
        Event::listen(TarefaAprovada::class, [GravarHistoricoListener::class, 'handleTarefaAprovada']);
        Event::listen(TarefaReprovada::class, [GravarHistoricoListener::class, 'handleTarefaReprovada']);
        Event::listen(TarefaCancelada::class, [GravarHistoricoListener::class, 'handleTarefaCancelada']);
        Event::listen(AlteracaoSolicitada::class, [GravarHistoricoListener::class, 'handleAlteracaoSolicitada']);

        Event::listen(TarefaCriada::class, [NotificarPartesInteressadasListener::class, 'handleTarefaCriada']);
        Event::listen(TarefaAtribuida::class, [NotificarPartesInteressadasListener::class, 'handleTarefaAtribuida']);

        Event::listen(TarefaCriada::class, [EnviarEmailDeAtribuicaoListener::class, 'handleTarefaCriada']);
        Event::listen(TarefaAtribuida::class, [EnviarEmailDeAtribuicaoListener::class, 'handleTarefaAtribuida']);
        Event::listen(TarefaAprovada::class, [NotificarPartesInteressadasListener::class, 'handleTarefaAprovada']);
        Event::listen(TarefaReprovada::class, [NotificarPartesInteressadasListener::class, 'handleTarefaReprovada']);
        Event::listen(ComentarioAdicionado::class, [NotificarPartesInteressadasListener::class, 'handleComentarioAdicionado']);

        foreach ([TarefaCriada::class, TarefaAtribuida::class, StatusAlterado::class, PrazoAlterado::class, PrioridadeAlterada::class,
            ComentarioAdicionado::class, AnexoAdicionado::class, TarefaBloqueada::class, TarefaDesbloqueada::class,
            ConclusaoSolicitada::class, TarefaAprovada::class, TarefaReprovada::class, TarefaCancelada::class,
            AlteracaoSolicitada::class, ] as $broadcastable) {
            Event::listen($broadcastable, DispatchWebhooksListener::class);
        }

        Gate::define('view-task', function (User $user, Task $task) {
            return ($user->isGestor() && Task::query()->forManager($user)->whereKey($task->id)->exists())
                || $task->assigned_to === $user->id;
        });

        Gate::define('manage-team', function (User $user) {
            return $user->isGestor();
        });

        Gate::define('create-task', function (User $user) {
            return $user->isGestor();
        });

        Gate::define('approve-task', function (User $user, Task $task) {
            return $user->isGestor() && Task::query()->forManager($user)->whereKey($task->id)->exists();
        });

        Gate::define('reject-task', function (User $user, Task $task) {
            return $user->isGestor() && Task::query()->forManager($user)->whereKey($task->id)->exists();
        });

        Gate::define('viewPulse', function (User $user) {
            return $user->isGestor();
        });

        Gate::policy(User::class, TeamMemberPolicy::class);
        Gate::define('view-team-profile', [TeamMemberPolicy::class, 'viewProfile']);
        Gate::define('manage-team-documents', [TeamMemberPolicy::class, 'manageDocuments']);
        Gate::define('delete-team-document', function (User $user, TeamMemberDocument $document) {
            return $user->isGestor() && (int) $document->user->manager_id === (int) $user->id;
        });

        Feature::define('ai-assistant', fn (User $user) => $user->isGestor());

        Feature::define('auto-scheduling', fn (User $user) => $user->isGestor());
    }
}
