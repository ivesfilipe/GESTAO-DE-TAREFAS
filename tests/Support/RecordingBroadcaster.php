<?php

namespace Tests\Support;

use Illuminate\Broadcasting\Broadcasters\Broadcaster;

class RecordingBroadcaster extends Broadcaster
{
    public array $broadcasts = [];

    public function auth($request)
    {
        return true;
    }

    public function validAuthenticationResponse($request, $result)
    {
        return $result;
    }

    public function broadcast(array $channels, $event, array $payload = [])
    {
        foreach ($this->formatChannels($channels) as $channel) {
            $this->broadcasts[] = ['channel' => $channel, 'event' => $event];
        }
    }

    public function pushedTo(string $suffix): bool
    {
        return collect($this->broadcasts)->contains(fn ($b) => str_contains($b['channel'], $suffix));
    }
}
