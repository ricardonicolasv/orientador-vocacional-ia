<?php

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiVocationalService
{
    public function __construct(
        private VocationalSystemPromptService $promptService
    ) {}

    public function generateResponse(Conversation $conversation, string $studentMessage): string
    {
        $apiKey = config('ai.gemini.api_key');
        $model = config('ai.gemini.model');

        if (!$apiKey) {
            return 'La IA de Gemini aún no está configurada. Falta definir GEMINI_API_KEY en el archivo .env.';
        }

        if (!$model) {
            return 'La IA de Gemini aún no está configurada. Falta definir GEMINI_MODEL en el archivo .env.';
        }

        $conversation->load(['student', 'messages']);

        try {
            $response = Http::timeout(45)
                ->retry(2, 300)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'x-goog-api-key' => $apiKey,
                ])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'systemInstruction' => [
                        'parts' => [
                            [
                                'text' => $this->promptService->build($conversation),
                            ],
                        ],
                    ],
                    'contents' => $this->buildContents($conversation, $studentMessage),
                    'generationConfig' => [
                        'temperature' => 0.25,
                        'topP' => 0.85,
                        'maxOutputTokens' => 1200,
                        'thinkingConfig' => [
                            'thinkingBudget' => 0,
                        ],
                    ],
                ]);

            if ($response->failed()) {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                if ($response->status() === 429) {
                    return $this->rateLimitFallbackResponse();
                }

                if (app()->environment('local')) {
                    return 'Error Gemini: ' . $response->status() . ' - ' . json_encode($response->json(), JSON_UNESCAPED_UNICODE);
                }

                return 'No pude generar una respuesta con IA en este momento. Intenta nuevamente o consulta con el orientador.';
            }

            $content = trim($response->json('candidates.0.content.parts.0.text') ?? '');
            $finishReason = $response->json('candidates.0.finishReason');

            if (!$content) {
                Log::warning('Gemini empty response', [
                    'finish_reason' => $finishReason,
                    'body' => $response->json(),
                ]);

                if ($finishReason === 'SAFETY') {
                    return 'Prefiero que este tema lo converses directamente con el orientador del colegio, para recibir una ayuda más adecuada y segura.';
                }

                if ($finishReason === 'RECITATION') {
                    return 'No pude responder esa consulta de forma segura. Reformula la pregunta o revísala con el orientador del colegio.';
                }

                if ($finishReason === 'MAX_TOKENS') {
                    return $this->completeTruncatedResponseFallback($conversation);
                }

                return 'No pude generar una respuesta clara en este momento. Intenta reformular tu pregunta o consulta con el orientador.';
            }

            if ($finishReason === 'MAX_TOKENS') {
                Log::warning('Gemini response truncated by max tokens', [
                    'finish_reason' => $finishReason,
                    'content' => $content,
                ]);

                return $this->completeTruncatedResponseFallback($conversation);
            }

            if ($finishReason === 'SAFETY') {
                Log::warning('Gemini response blocked by safety filters', [
                    'finish_reason' => $finishReason,
                ]);

                return 'Prefiero que este tema lo converses directamente con el orientador del colegio, para recibir una ayuda más adecuada y segura.';
            }

            if ($finishReason === 'RECITATION') {
                Log::warning('Gemini response blocked by recitation filters', [
                    'finish_reason' => $finishReason,
                ]);

                return 'No pude responder esa consulta de forma segura. Reformula la pregunta o revísala con el orientador del colegio.';
            }

            if ($finishReason === 'OTHER') {
                Log::warning('Gemini response finished with OTHER reason', [
                    'finish_reason' => $finishReason,
                ]);

                return 'No pude generar una respuesta completa en este momento. Intenta nuevamente con una pregunta más específica.';
            }

            return $this->limitResponseLength($content);
        } catch (\Throwable $exception) {
            Log::error('Gemini request exception', [
                'message' => $exception->getMessage(),
            ]);

            return 'Ocurrió un problema al conectar con Gemini. Intenta nuevamente más tarde.';
        }
    }

    private function completeTruncatedResponseFallback(Conversation $conversation): string
    {
        $studentName = $conversation->student->name ?? 'estudiante';

        return "{$studentName}, la respuesta se cortó antes de completarse, pero puedo dejarte una orientación inicial.

Con lo que mencionas, conviene seguir explorando tus intereses paso a paso y comparar opciones reales de estudio.

Para avanzar, podemos revisar:
- Qué áreas te interesan más.
- Si te conviene universidad, instituto profesional o CFT.
- Qué carreras o programas podrían relacionarse con tus intereses.
- Qué requisitos, duración, campo laboral y beneficios debes verificar en fuentes oficiales.

¿Quieres que comparemos las rutas universidad, IP y CFT según lo que has contado?";
    }

    private function buildContents(Conversation $conversation, string $studentMessage): array
    {
        $contents = [];

        $messages = $conversation->messages
            ->sortBy('created_at')
            ->take(-10)
            ->values();

        foreach ($messages as $message) {
            if (!$message->content) {
                continue;
            }

            $contents[] = [
                'role' => $message->sender === 'student' ? 'user' : 'model',
                'parts' => [
                    [
                        'text' => trim($message->content),
                    ],
                ],
            ];
        }

        $lastUserMessage = collect($contents)
            ->where('role', 'user')
            ->last();

        $lastUserText = trim(data_get($lastUserMessage, 'parts.0.text', ''));

        if ($lastUserText !== trim($studentMessage)) {
            $contents[] = [
                'role' => 'user',
                'parts' => [
                    [
                        'text' => trim($studentMessage),
                    ],
                ],
            ];
        }

        return $contents;
    }

    private function rateLimitFallbackResponse(): string
    {
        return "En este momento la IA alcanzó su límite temporal de uso, pero podemos seguir orientando con una respuesta base.

Para avanzar, dime qué área te interesa más:
- Universidad.
- Instituto profesional o CFT.
- Beneficios, becas, gratuidad o FUAS.
- Carreras relacionadas con tus intereses.
- Fuerzas Armadas, de Orden o Seguridad Pública.

También puedes intentarlo nuevamente en unos segundos.";
    }

    private function limitResponseLength(string $content): string
    {
        if (mb_strlen($content) <= 1800) {
            return $content;
        }

        return mb_substr($content, 0, 1800) . "\n\nRespuesta resumida por extensión. Podemos seguir profundizando paso a paso.";
    }
}
