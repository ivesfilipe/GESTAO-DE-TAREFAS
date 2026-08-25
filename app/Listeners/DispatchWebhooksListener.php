<?php

namespace App\Listeners;

use App\Jobs\SendWebhook;
use App\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

class DispatchWebhooksListener
{
    public function handle(object $event): void
    {
        $eventName = $this->resolveEventName($event);

        if ($eventName === null) {
            return;
        }

        WebhookEndpoint::query()
            ->where('is_active', true)
            ->chunkById(100, function ($endpoints) use ($eventName, $event) {
                foreach ($endpoints as $endpoint) {
                    if (! $endpoint->listensTo($eventName)) {
                        continue;
                    }

                    Queue::push(new SendWebhook($endpoint, $eventName, [
                        'task_id' => $event->task->id,
                        'title' => $event->task->title,
                        'status' => $event->task->status,
                        'priority' => $event->task->priority,
                        'due_at' => optional($event->task->due_at)->toIso8601String(),
                        'actor_name' => $event->actor->name,
                    ]));
                }
            });
    }

    private function resolveEventName(object $event): ?string
    {
        if (! property_exists($event, 'task')) {
            return null;
        }

        return 'task.'.Str::snake((new \ReflectionClass($event))->getShortName());
    }
}
