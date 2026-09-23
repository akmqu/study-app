<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class HomeworkAiController extends Controller
{
    public function generate(
        Request $request,
        GeminiService $gemini
    ): JsonResponse {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'prompt' => ['required', 'string', 'max:2000'],
        ]);

        $prompt = <<<PROMPT
You are an assistant for a private tutor.

Create homework instructions for a student.

Subject: {$validated['subject']}

Tutor request:
{$validated['prompt']}

Requirements:
- Return only the homework instructions.
- Follow the tutor request closely.
- Keep the instructions clear and practical.
- Use a simple numbered list when appropriate.
- Do not include answers or solutions.
- Do not mention AI.
- Use the same language as the tutor request when possible.
- Use plain text only.
- Do not use Markdown formatting.
- Do not use LaTeX syntax.
PROMPT;

        try {
            $instructions = $gemini->generate($prompt);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'AI generation failed. Please try again.',
            ], 500);
        }

        return response()->json([
            'instructions' => $instructions,
        ]);
    }
}