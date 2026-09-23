<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiService
{
    private const MODEL = 'gemini-3.6-flash';

    public function generate(string $prompt): string
    {
        $apiKey = config('services.gemini.key');

        if (! $apiKey) {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        $response = Http::withHeaders([
            'x-goog-api-key' => $apiKey,
        ])
            ->acceptJson()
            ->timeout(30)
            ->post(
                'https://generativelanguage.googleapis.com/v1beta/models/'
                . self::MODEL
                . ':generateContent',
                [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt,
                                ],
                            ],
                        ],
                    ],

                    'generationConfig' => [
                        'maxOutputTokens' => 800,

                        'thinkingConfig' => [
                            'thinkingLevel' => 'minimal',
                        ],
                    ],
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Gemini API error: '.$response->status().' '.$response->body()
            );
        }

        $text = collect(
            data_get(
                $response->json(),
                'candidates.0.content.parts',
                []
            )
        )
            ->pluck('text')
            ->filter()
            ->implode("\n");

        if (trim($text) === '') {
            throw new RuntimeException(
                'Gemini returned an empty response.'
            );
        }

        return trim($text);
    }
}