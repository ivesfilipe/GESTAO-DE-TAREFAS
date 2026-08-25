<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\AiAssistantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AssistantController extends Controller
{
    public function index(Request $request, AiAssistantService $assistant)
    {
        Gate::authorize('create-task');

        $summary = $assistant->dailySummary($request->user());
        $suggestions = $assistant->prioritySuggestions($request->user());

        $breakdown = null;
        if ($request->filled('breakdown')) {
            $task = Task::findOrFail($request->input('breakdown'));
            Gate::authorize('view-task', $task);
            $breakdown = ['task' => $task, 'steps' => $assistant->breakdownSuggestions($task)];
        }

        return view('assistant.index', compact('assistant', 'summary', 'suggestions', 'breakdown'));
    }
}
