<?php

namespace App\Jobs;

use App\Models\WebhookEndpoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = [10, 60];

    public function __construct(
        public WebhookEndpoint $endpoint,
        public string $eventName,
        public array $payload,
    ) {}

    public function handle(): void
    {
        if (! $this->endpoint->is_active || ! $this->endpoint->listensTo($this->eventName)) {
            return;
        }

        $body = [
            'event' => $this->eventName,
            'sent_at' => now()->toIso8601String(),
            'data' => $this->payload,
        ];

        $signature = hash_hmac('sha256', json_encode($body), $this->endpoint->secret);

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-GT-Event' => $this->eventName,
                    'X-GT-Signature' => $signature,
                ])
                ->post($this->endpoint->url, $body);
        } catch (ConnectionException) {
            $this->endpoint->increment('failure_count');

            return;
        }

        if ($response->successful()) {
            $this->endpoint->update(['last_triggered_at' => now(), 'failure_count' => 0]);
        } else {
            $this->endpoint->increment('failure_count');
            Log::warning('Webhook falhou', ['endpoint_id' => $this->endpoint->id, 'status' => $response->status()]);
        }
    }
}
