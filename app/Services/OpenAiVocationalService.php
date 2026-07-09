<?php

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiVocationalService
{
    private const MAX_HISTORY_MESSAGES = 10;
    private const REQUEST_TIMEOUT_SECONDS = 45;
    private const MAX_OUTPUT_TOKENS = 1200;
    private const MAX_RESPONSE_LENGTH = 3000;

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
            $payload = [
                'model' => $model,
                'instructions' => $this->promptService->build($conversation),
                'input' => $this->buildInput($conversation, $studentMessage),
                'max_output_tokens' => self::MAX_OUTPUT_TOKENS,
                'reasoning' => [
                    'effort' => 'low',
                ],
            ];

            $response = Http::timeout(self::REQUEST_TIMEOUT_SECONDS)
                ->retry(2, 300)
                ->withToken($apiKey)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.openai.com/v1/responses', $payload);

            if ($response->failed()) {
                return $this->handleFailedResponse($response);
            }

            $body = $response->json();

            $status = data_get($body, 'status');
            $incompleteReason = data_get($body, 'incomplete_details.reason');

            if ($status === 'incomplete') {
                Log::warning('OpenAI incomplete response', [
                    'reason' => $incompleteReason,
                    'body' => $this->sanitizeLogPayload($body),
                ]);

                $partialContent = $this->extractContent($body);

                if ($partialContent) {
                    return $this->limitResponseLength($partialContent)
                        . "\n\nLa respuesta fue ajustada porque el modelo alcanzó un límite de generación. Puedes pedir que continúe desde este punto.";
                }

                return 'La IA no alcanzó a completar la respuesta. Para avanzar, reformula la pregunta en una parte más específica o pide una comparación paso a paso.';
            }

            $content = $this->extractContent($body);

            if (!$content) {
                Log::warning('OpenAI empty response', [
                    'body' => $this->sanitizeLogPayload($body),
                ]);

                return 'No pude generar una respuesta completa en este momento. Podemos seguir con una orientación base: dime si quieres comparar universidad, IP, CFT, beneficios/FUAS, pedagogía o Fuerzas Armadas y de Orden.';
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
            ->take(-self::MAX_HISTORY_MESSAGES)
            ->values();

        foreach ($messages as $message) {
            if (!$message->content) {
                continue;
            }

            $isStudent = $message->sender === 'student';

            $input[] = [
                'role' => $isStudent ? 'user' : 'assistant',
                'content' => [
                    [
                        'type' => $isStudent ? 'input_text' : 'output_text',
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

    private function handleFailedResponse($response): string
    {
        $body = $response->json();

        Log::error('OpenAI API error', [
            'status' => $response->status(),
            'body' => $this->sanitizeLogPayload($body),
        ]);

        if (in_array($response->status(), [401, 403], true)) {
            return 'La conexión con OpenAI no está autorizada. Revisa que OPENAI_API_KEY sea válida y esté bien configurada.';
        }

        if (in_array($response->status(), [402, 429], true)) {
            return $this->rateLimitFallbackResponse();
        }

        if (app()->environment('local')) {
            return 'Error OpenAI: ' . $response->status() . ' - ' . json_encode($body, JSON_UNESCAPED_UNICODE);
        }

        return 'No pude generar una respuesta con IA en este momento. Intenta nuevamente o consulta con el orientador.';
    }

    private function extractContent(?array $payload): string
    {
        if (!$payload) {
            return '';
        }

        $outputText = trim((string) data_get($payload, 'output_text', ''));

        if ($outputText !== '') {
            return $outputText;
        }

        $output = data_get($payload, 'output', []);

        if (!is_array($output)) {
            return '';
        }

        $texts = collect($output)
            ->flatMap(function ($item) {
                $content = data_get($item, 'content', []);

                if (!is_array($content)) {
                    return [];
                }

                return $content;
            })
            ->map(function ($content) {
                return data_get($content, 'text')
                    ?? data_get($content, 'content')
                    ?? data_get($content, 'value');
            })
            ->filter()
            ->map(fn($text) => trim((string) $text))
            ->filter()
            ->values();

        return trim($texts->implode("\n"));
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
        $content = trim($content);

        if (mb_strlen($content) <= self::MAX_RESPONSE_LENGTH) {
            return $content;
        }

        $cut = mb_substr($content, 0, self::MAX_RESPONSE_LENGTH);

        $lastPeriod = max(
            mb_strrpos($cut, '.'),
            mb_strrpos($cut, '?'),
            mb_strrpos($cut, '!')
        );

        if ($lastPeriod !== false && $lastPeriod > 600) {
            $cut = mb_substr($cut, 0, $lastPeriod + 1);
        }

        return trim($cut) . "\n\nPuedo seguir con más detalle si quieres profundizar en este punto.";
    }

    private function sanitizeLogPayload(?array $payload): array
    {
        if (!$payload) {
            return [];
        }

        return [
            'error' => data_get($payload, 'error'),
            'status' => data_get($payload, 'status'),
            'id' => data_get($payload, 'id'),
        ];
    }
}
