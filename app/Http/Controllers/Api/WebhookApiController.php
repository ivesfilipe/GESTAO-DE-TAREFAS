<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebhookEndpoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user()->webhookEndpoints()->get(['id', 'url', 'events', 'is_active', 'last_triggered_at', 'failure_count']),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'url' => ['required', 'url', 'max:500'],
            'events' => ['nullable', 'array'],
            'events.*' => ['string'],
        ]);

        $endpoint = $request->user()->webhookEndpoints()->create([
            'url' => $data['url'],
            'secret' => Str::random(40),
            'events' => $data['events'] ?? null,
        ]);

        return response()->json([
            'data' => $endpoint,
            'secret_note' => 'Guarde este secret com segurança: '.$endpoint->secret,
        ], 201);
    }

    public function destroy(Request $request, WebhookEndpoint $endpoint): JsonResponse
    {
        if ((int) $endpoint->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $endpoint->delete();

        return response()->json(['deleted' => true]);
    }
}
