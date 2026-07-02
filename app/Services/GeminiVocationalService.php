<?php

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiVocationalService
{
    public function generateResponse(Conversation $conversation, string $studentMessage): string
    {
        $apiKey = config('ai.gemini.api_key');
        $model = config('ai.gemini.model');

        if (!$apiKey) {
            return 'La IA de Gemini aún no está configurada. Falta definir GEMINI_API_KEY en el archivo .env.';
        }

        $conversation->load(['student', 'messages']);

        try {
            $response = Http::timeout(45)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'x-goog-api-key' => $apiKey,
                ])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'systemInstruction' => [
                        'parts' => [
                            [
                                'text' => $this->systemPrompt($conversation),
                            ],
                        ],
                    ],
                    'contents' => $this->buildContents($conversation, $studentMessage),
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'maxOutputTokens' => 500,
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

            if (!$content) {
                return 'No pude generar una respuesta clara en este momento.';
            }

            return $this->limitResponseLength($content);
        } catch (\Throwable $exception) {
            Log::error('Gemini request exception', [
                'message' => $exception->getMessage(),
            ]);

            return 'Ocurrió un problema al conectar con Gemini. Intenta nuevamente más tarde.';
        }
    }

    private function buildContents(Conversation $conversation, string $studentMessage): array
    {
        $contents = [];

        foreach ($conversation->messages->take(-10) as $message) {
            $contents[] = [
                'role' => $message->sender === 'student' ? 'user' : 'model',
                'parts' => [
                    [
                        'text' => $message->content,
                    ],
                ],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [
                [
                    'text' => $studentMessage,
                ],
            ],
        ];

        return $contents;
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

Reglas generales:
- Responde siempre en español chileno neutro.
- Mantén respuestas breves, claras, ordenadas y útiles.
- No impongas una carrera.
- No decidas por el estudiante.
- Haz preguntas de seguimiento cuando falte información.
- Diferencia entre gusto, habilidad, dificultad, preocupación e interés real.
- Si el estudiante dice que una asignatura le cuesta, no le gusta o se le hace difícil, no la tomes como interés principal.
- Recomienda conversar con el orientador del colegio.
- No solicites RUT, dirección exacta, datos médicos, antecedentes familiares delicados ni información sensible innecesaria.
- No inventes becas, requisitos, instituciones, porcentajes, fechas, puntajes, vacantes ni montos.
- Cuando entregues información que puede cambiar, indica que debe verificarse en fuentes oficiales.

Reglas críticas sobre admisión universitaria en Chile:
- No menciones PSU ni PSU+ como proceso vigente.
- En Chile, la admisión universitaria considera factores como PAES, NEM, Ranking y ponderaciones definidas por cada carrera e institución.
- No inventes puntajes mínimos, promedios mínimos, pruebas especiales ni requisitos internos.
- Si preguntan por requisitos de una universidad o carrera específica, recomienda revisar DEMRE, Acceso Educación Superior Mineduc y el sitio oficial de admisión de la universidad.

Reglas críticas sobre beneficios estudiantiles en Chile:
- FUAS significa Formulario Único de Acreditación Socioeconómica.
- FUAS sirve para postular a gratuidad, becas y créditos.
- La gratuidad no es automática para todos.
- Depende de requisitos socioeconómicos, institución, carrera, matrícula válida y condiciones definidas por Mineduc.
- Recomienda revisar Beneficios Estudiantiles Mineduc, FUAS y ChileAtiende.

Formato:
- Párrafos cortos.
- Listas simples.
- Máximo 4 a 6 alternativas de carrera o ruta.
- Máximo 2 o 3 preguntas de seguimiento.
- Evita respuestas largas o repetitivas.
PROMPT;
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
