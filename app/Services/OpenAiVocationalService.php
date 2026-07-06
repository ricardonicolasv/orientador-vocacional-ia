<?php

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiVocationalService
{
    public function __construct(
        private VocationalSystemPromptService $promptService
    ) {}

    public function generateResponse(Conversation $conversation, string $studentMessage): string
    {
        $apiKey = config('ai.openai.api_key');
        $model = config('ai.openai.model', 'gpt-5-mini');

        if (!$apiKey) {
            return 'La IA de OpenAI aún no está configurada. Falta definir OPENAI_API_KEY en el archivo .env.';
        }

        if (!$model) {
            return 'La IA de OpenAI aún no está configurada. Falta definir OPENAI_MODEL en el archivo .env.';
        }

        $conversation->load(['student', 'messages']);

        try {
            $response = Http::timeout(45)
                ->retry(2, 300)
                ->withToken($apiKey)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.openai.com/v1/responses', [
                    'model' => $model,
                    'instructions' => $this->promptService->build($conversation),
                    'input' => $this->buildInput($conversation, $studentMessage),
                    'temperature' => 0.25,
                    'max_output_tokens' => 1200,
                ]);

            if ($response->failed()) {
                Log::error('OpenAI API error', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                if ($response->status() === 429) {
                    return $this->rateLimitFallbackResponse();
                }

                if (app()->environment('local')) {
                    return 'Error OpenAI: ' . $response->status() . ' - ' . json_encode($response->json(), JSON_UNESCAPED_UNICODE);
                }

                return 'No pude generar una respuesta con IA en este momento. Intenta nuevamente o consulta con el orientador.';
            }

            $content = trim($response->json('output_text') ?? '');

            if (!$content) {
                Log::warning('OpenAI empty response', [
                    'body' => $response->json(),
                ]);

                return 'No pude generar una respuesta clara en este momento. Intenta reformular tu pregunta o consulta con el orientador.';
            }

            return $this->limitResponseLength($content);
        } catch (\Throwable $exception) {
            Log::error('OpenAI request exception', [
                'message' => $exception->getMessage(),
            ]);

            return 'Ocurrió un problema al conectar con OpenAI. Intenta nuevamente más tarde.';
        }
    }

    private function buildInput(Conversation $conversation, string $studentMessage): array
    {
        $input = [];

        $messages = $conversation->messages
            ->sortBy('created_at')
            ->take(-10)
            ->values();

        foreach ($messages as $message) {
            if (!$message->content) {
                continue;
            }

            $input[] = [
                'role' => $message->sender === 'student' ? 'user' : 'assistant',
                'content' => [
                    [
                        'type' => 'input_text',
                        'text' => trim($message->content),
                    ],
                ],
            ];
        }

        $lastUserMessage = collect($input)
            ->where('role', 'user')
            ->last();

        $lastUserText = trim(data_get($lastUserMessage, 'content.0.text', ''));

        if ($lastUserText !== trim($studentMessage)) {
            $input[] = [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'input_text',
                        'text' => trim($studentMessage),
                    ],
                ],
            ];
        }

        return $input;
    }

    private function rateLimitFallbackResponse(): string
    {
        return "En este momento la IA alcanzó su límite temporal de uso o saldo disponible.

Para avanzar, dime qué área te interesa más:
- Universidad.
- Instituto profesional o CFT.
- Beneficios, becas, gratuidad o FUAS.
- Carreras relacionadas con tus intereses.
- Fuerzas Armadas, de Orden o Seguridad Pública.

También puedes intentarlo nuevamente más tarde.";
    }

    private function limitResponseLength(string $content): string
    {
        if (mb_strlen($content) <= 1800) {
            return $content;
        }

        return mb_substr($content, 0, 1800) . "\n\nRespuesta resumida por extensión. Podemos seguir profundizando paso a paso.";
    }
}
