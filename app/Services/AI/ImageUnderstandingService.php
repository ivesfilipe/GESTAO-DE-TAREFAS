<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageUnderstandingService
{
    /**
     * @return array{text: string, status: string}
     */
    public function understand(string $path): array
    {
        $ai = app(AIService::class);

        if ($ai->isMock()) {
            return ['text' => '', 'status' => 'needs_ocr'];
        }

        if (config('ai.zdr.required', true) && ! config('ai.zdr.confirmed', false)) {
            return ['text' => '', 'status' => 'needs_ocr'];
        }

        if (! Storage::exists($path)) {
            return ['text' => '', 'status' => 'erro'];
        }

        $model = config('ai.providers.groq.vision_model', 'meta-llama/llama-4-scout-17b-16e-instruct');
        $apiKey = config('ai.providers.groq.api_key');
        $baseUrl = rtrim((string) config('ai.providers.groq.base_url', 'https://api.groq.com/openai/v1'), '/');

        if (empty($apiKey)) {
            return ['text' => '', 'status' => 'needs_ocr'];
        }

        $binary = Storage::get($path);
        $mime = Storage::mimeType($path) ?: 'image/jpeg';
        $dataUrl = 'data:'.$mime.';base64,'.base64_encode($binary);

        try {
            $response = Http::timeout(30)
                ->withToken($apiKey)
                ->acceptJson()
                ->post("{$baseUrl}/chat/completions", [
                    'model' => $model,
                    'max_tokens' => 600,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => 'Extraia todo o texto visível e descreva a imagem em 3 a 5 frases em português. Se não houver texto, descreva só o que aparece.',
                                ],
                                [
                                    'type' => 'image_url',
                                    'image_url' => ['url' => $dataUrl],
                                ],
                            ],
                        ],
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('Vision provider failed', ['status' => $response->status(), 'body' => $response->body()]);

                return ['text' => '', 'status' => 'needs_ocr'];
            }

            $text = trim((string) data_get($response->json(), 'choices.0.message.content', ''));

            return [
                'text' => $text,
                'status' => $text !== '' ? 'pronto' : 'needs_ocr',
            ];
        } catch (\Throwable $e) {
            Log::warning('Vision request failed', ['error' => $e->getMessage()]);

            return ['text' => '', 'status' => 'needs_ocr'];
        }
    }
}
