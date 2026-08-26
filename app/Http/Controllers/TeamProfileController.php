<?php

namespace App\Http\Controllers;

use App\Models\TeamMemberProfile;
use App\Models\User;
use App\Services\AI\AIService;
use App\Services\AI\ProfileIntelligenceService;
use App\Services\AI\TaskSuggestionService;
use App\Services\AI\TeamKnowledgeService;
use App\Services\AI\TeamPerformanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeamProfileController extends Controller
{
    public function show(Request $request, User $user, TeamPerformanceService $performance, TeamKnowledgeService $knowledge)
    {
        Gate::authorize('manage-team');

        if (! $user->isLiderado()) {
            abort(404);
        }

        $profile = $user->teamProfile ?? new TeamMemberProfile(['user_id' => $user->id]);
        $metrics = $performance->memberMetrics($user);
        $workload = $performance->workloadDistribution();
        $documents = $knowledge->documents($user, 10);

        return view('team.profile', compact('user', 'profile', 'metrics', 'workload', 'documents'));
    }

    public function updateProfile(Request $request, User $user)
    {
        Gate::authorize('manage-team');

        if (! $user->isLiderado()) {
            abort(404);
        }

        $data = $request->validate([
            'role' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'function_summary' => ['nullable', 'string'],
            'responsibilities' => ['nullable', 'array'],
            'responsibilities.*' => ['string', 'max:255'],
            'recurring_responsibilities' => ['nullable', 'array'],
            'recurring_responsibilities.*' => ['string', 'max:255'],
            'professional_objectives' => ['nullable', 'array'],
            'professional_objectives.*' => ['string', 'max:255'],
            'delegation_guidelines' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $profile = TeamMemberProfile::firstOrNew(['user_id' => $user->id]);
        $profile->fill($data);
        $profile->save();

        return redirect()->route('team.profile', $user)
            ->with('success', 'Perfil profissional atualizado.');
    }

    public function generateSummary(Request $request, User $user, ProfileIntelligenceService $service)
    {
        Gate::authorize('manage-team');

        if (! $user->isLiderado()) {
            abort(404);
        }

        $profile = $service->updateIntelligence($user);
        $ai = app(AIService::class);

        return response()->json([
            'ok' => true,
            'profile' => [
                'summary' => $profile->summary,
                'strengths' => $profile->strengths,
                'gaps' => $profile->gaps,
                'preferences' => $profile->preferences,
                'generated_at' => $profile->generated_at?->format('d/m/Y H:i'),
            ],
            'provider' => $ai->provider()->name(),
            'mock' => $ai->isMock(),
        ]);
    }

    public function suggestTasks(Request $request, User $user, TaskSuggestionService $service)
    {
        Gate::authorize('manage-team');

        if (! $user->isLiderado()) {
            abort(404);
        }

        $category = $request->input('category');
        if ($category && ! in_array($category, ['demanda', 'compra', 'servico', 'desenvolvimento', 'responsabilidade', 'outro'])) {
            $category = null;
        }

        $suggestions = $service->suggest($user, $category);
        $ai = app(AIService::class);

        return response()->json([
            'ok' => true,
            'suggestions' => $suggestions,
            'provider' => $ai->provider()->name(),
            'mock' => $ai->isMock(),
        ]);
    }

    public function storeDocument(Request $request, User $user, TeamKnowledgeService $knowledge)
    {
        Gate::authorize('manage-team');

        if (! $user->isLiderado()) {
            abort(404);
        }

        $request->validate([
            'document' => ['required', 'file', 'max:10240'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $document = $knowledge->storeDocument($user, $request->file('document'), $request->input('name'));

        return redirect()->route('team.profile', $user)
            ->with('success', 'Documento "'.$document->name.'" adicionado ao perfil.');
    }

    public function destroyDocument(Request $request, User $user, int $documentId, TeamKnowledgeService $knowledge)
    {
        Gate::authorize('manage-team');

        if (! $user->isLiderado()) {
            abort(404);
        }

        $document = $user->documents()->findOrFail($documentId);
        $knowledge->deleteDocument($document);

        return redirect()->route('team.profile', $user)
            ->with('success', 'Documento removido.');
    }
}
