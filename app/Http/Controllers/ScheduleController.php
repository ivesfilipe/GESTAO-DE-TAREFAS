<?php

namespace App\Http\Controllers;

use App\Services\AutoSchedulingService;
use Illuminate\Http\Request;
use Laravel\Pennant\Feature;

class ScheduleController extends Controller
{
    public function index(Request $request, AutoSchedulingService $service)
    {
        if (! Feature::for($request->user())->active('auto-scheduling')) {
            abort(403);
        }

        $blocks = session('blocks') ?? $service->propose($request->user());

        return view('schedule.index', ['blocks' => collect($blocks)]);
    }

    public function regenerate(Request $request, AutoSchedulingService $service)
    {
        if (! Feature::for($request->user())->active('auto-scheduling')) {
            abort(403);
        }

        return redirect()->route('schedule.index')
            ->with('blocks', $service->propose($request->user()));
    }

    public function apply(Request $request, AutoSchedulingService $service)
    {
        if (! Feature::for($request->user())->active('auto-scheduling')) {
            abort(403);
        }

        $data = $request->validate([
            'blocks' => ['required', 'array'],
            'blocks.*.task_id' => ['required', 'integer'],
            'blocks.*.start' => ['required', 'date'],
        ]);

        $applied = $service->apply($request->user(), $data['blocks']);

        return redirect()->route('schedule.index')
            ->with('success', "{$applied} tarefa(s) agendada(s) com sucesso.");
    }
}
