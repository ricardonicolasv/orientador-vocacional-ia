<?php

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiVocationalService
{
    public function generateResponse(Conversation $conversation, string $studentMessage): string
    {
        $apiKey = config('ai.openai.api_key');
        $model = config('ai.openai.model');

        if (!$apiKey) {
            return 'La IA real aún no está configurada. Falta definir OPENAI_API_KEY en el archivo .env.';
        }

        $conversation->load(['student', 'messages']);

        $input = $this->buildInput($conversation, $studentMessage);

        try {
            $response = Http::withToken($apiKey)
                ->timeout(45)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => $model,
                    'input' => $input,
                    'temperature' => 0.4,
                    'max_output_tokens' => 700,
                ]);

            if ($response->failed()) {
                Log::error('OpenAI API error', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return 'No pude generar una respuesta con IA en este momento. Intenta nuevamente o consulta con el orientador.';
            }

            return $this->extractText($response->json());
        } catch (\Throwable $exception) {
            Log::error('OpenAI request exception', [
                'message' => $exception->getMessage(),
            ]);

            return 'Ocurrió un problema al conectar con la IA. Intenta nuevamente más tarde.';
        }
    }

    private function buildInput(Conversation $conversation, string $studentMessage): array
    {
        $student = $conversation->student;

        $systemPrompt = $this->systemPrompt($conversation);

        $messages = [
            [
                'role' => 'developer',
                'content' => $systemPrompt,
            ],
        ];

        foreach ($conversation->messages->take(-10) as $message) {
            $messages[] = [
                'role' => $message->sender === 'student' ? 'user' : 'assistant',
                'content' => $message->content,
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $studentMessage,
        ];

        return $messages;
    }

    private function systemPrompt(Conversation $conversation): string
    {
        $student = $conversation->student;
        $route = $conversation->selected_route;

        return <<<PROMPT
Eres un asistente vocacional del Instituto San José.

Tu función es orientar estudiantes de enseñanza media, especialmente 3° y 4° medio, sobre opciones de estudio, carreras, rutas formativas, beneficios estudiantiles, pedagogías y opciones en Fuerzas Armadas, de Orden y Seguridad Pública.

Datos del estudiante:
- Nombre: {$student->name}
- Curso: {$student->course}
- Colegio: {$student->school}
- Ruta seleccionada: {$route}

Reglas de respuesta:
- Usa español chileno neutro, claro y respetuoso.
- Mantén respuestas breves, ordenadas y útiles.
- No impongas una carrera.
- No decidas por el estudiante.
- Haz preguntas de seguimiento cuando falte información.
- Si el estudiante dice que una asignatura le cuesta o no le gusta, no la tomes como interés principal.
- Diferencia entre gusto, habilidad, dificultad y preocupación.
- Sugiere opciones compatibles con intereses reales.
- Recomienda conversar con el orientador del colegio.
- No solicites RUT, dirección exacta, datos médicos ni información familiar delicada.
- Cuando entregues información que puede cambiar, indica que debe verificarse en fuentes oficiales.

Fuentes oficiales a considerar cuando corresponda:
- DEMRE
- Acceso Educación Superior Mineduc
- Mi Futuro Mineduc
- CNA Chile
- FUAS / Beneficios Estudiantiles Mineduc
- ChileAtiende
- Elige Educar
- Quiero Ser Profe
- Sitios oficiales de FF.AA., Carabineros, PDI y Gendarmería

Estructura recomendada:
1. Reconoce lo que el estudiante dijo.
2. Identifica áreas posibles.
3. Sugiere alternativas razonables.
4. Haz 2 o 3 preguntas de seguimiento.
PROMPT;
    }

    private function extractText(array $response): string
    {
        if (!empty($response['output_text'])) {
            return trim($response['output_text']);
        }

        $text = '';

        foreach ($response['output'] ?? [] as $outputItem) {
            foreach ($outputItem['content'] ?? [] as $contentItem) {
                if (($contentItem['type'] ?? null) === 'output_text') {
                    $text .= $contentItem['text'] ?? '';
                }
            }
        }

        return trim($text) ?: 'No pude generar una respuesta clara en este momento.';
    }
}
