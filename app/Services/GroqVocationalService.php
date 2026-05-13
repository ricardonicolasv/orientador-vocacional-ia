<?php

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqVocationalService
{
    public function generateResponse(Conversation $conversation, string $studentMessage): string
    {
        $normalizedMessage = $this->normalize($studentMessage);

        if ($this->isAdmissionRequirementsQuestion($normalizedMessage)) {
            return $this->safeAdmissionRequirementsResponse($conversation);
        }

        if ($this->isSpecificInstitutionQuestion($normalizedMessage)) {
            return $this->safeInstitutionResponse($conversation, $normalizedMessage);
        }

        $apiKey = config('ai.groq.api_key');
        $model = config('ai.groq.model');

        if (!$apiKey) {
            return 'La IA de Groq aún no está configurada. Falta definir GROQ_API_KEY en el archivo .env.';
        }

        $conversation->load(['student', 'messages']);

        try {
            $response = Http::withToken($apiKey)
                ->timeout(45)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => $model,
                    'messages' => $this->buildMessages($conversation, $studentMessage),
                    'temperature' => 0.2,
                    'max_tokens' => 700,
                ]);

            if ($response->failed()) {
                Log::error('Groq API error', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                if ($response->status() === 429) {
                    return $this->rateLimitFallbackResponse();
                }

                if (app()->environment('local')) {
                    return 'Error Groq: ' . $response->status() . ' - ' . json_encode($response->json(), JSON_UNESCAPED_UNICODE);
                }

                return 'No pude generar una respuesta con IA en este momento. Intenta nuevamente o consulta con el orientador.';
            }

            return trim($response->json('choices.0.message.content'))
                ?: 'No pude generar una respuesta clara en este momento.';
        } catch (\Throwable $exception) {
            Log::error('Groq request exception', [
                'message' => $exception->getMessage(),
            ]);

            return 'Ocurrió un problema al conectar con la IA. Intenta nuevamente más tarde.';
        }
    }

    private function rateLimitFallbackResponse(): string
    {
        return "En este momento la IA alcanzó su límite temporal de uso, pero podemos seguir orientando con una respuesta base.

Si estás preguntando por una institución específica, lo más seguro es revisar directamente su sitio oficial y comparar:
- Nombre exacto de la carrera.
- Sede.
- Duración.
- Malla curricular.
- Modalidad.
- Arancel.
- Acreditación institucional.
- Campo laboral.
- Requisitos de admisión.

Puedes intentarlo nuevamente en unos segundos o consultar con el orientador del colegio para revisar la información oficial.";
    }

    private function normalize(string $text): string
    {
        $text = mb_strtolower($text);

        return str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ'],
            ['a', 'e', 'i', 'o', 'u', 'n'],
            $text
        );
    }

    private function isAdmissionRequirementsQuestion(string $message): bool
    {
        $hasAdmissionIntent =
            str_contains($message, 'que necesito para entrar') ||
            str_contains($message, 'requisitos para entrar') ||
            str_contains($message, 'requisitos de admision') ||
            str_contains($message, 'como entro') ||
            str_contains($message, 'como puedo entrar') ||
            str_contains($message, 'puntaje') ||
            str_contains($message, 'ponderacion') ||
            str_contains($message, 'paes') ||
            str_contains($message, 'nem') ||
            str_contains($message, 'ranking');

        $hasInstitutionIntent =
            str_contains($message, 'universidad') ||
            str_contains($message, 'udec') ||
            str_contains($message, 'universidad de concepcion') ||
            str_contains($message, 'instituto') ||
            str_contains($message, 'cft');

        return $hasAdmissionIntent && $hasInstitutionIntent;
    }

    private function isSpecificInstitutionQuestion(string $message): bool
    {
        $institutions = [
            'aiep',
            'duoc',
            'duoc uc',
            'inacap',
            'santo tomas',
            'universidad de concepcion',
            'udec',
            'universidad del bio bio',
            'ubb',
            'instituto profesional',
            'cft',
        ];

        $questionIntent =
            str_contains($message, 'que me dices') ||
            str_contains($message, 'que opinas') ||
            str_contains($message, 'puedo estudiar') ||
            str_contains($message, 'existe') ||
            str_contains($message, 'existen') ||
            str_contains($message, 'tiene carrera') ||
            str_contains($message, 'hay carrera') ||
            str_contains($message, 'donde estudiar') ||
            str_contains($message, 'alguna carrera') ||
            str_contains($message, 'carrera similar') ||
            str_contains($message, 'carreras similares');

        foreach ($institutions as $institution) {
            if (str_contains($message, $institution) && $questionIntent) {
                return true;
            }
        }

        return false;
    }

    private function safeAdmissionRequirementsResponse(Conversation $conversation): string
    {
        $studentName = $conversation->student->name ?? 'estudiante';

        return "Buena pregunta, {$studentName}. Para saber qué necesitas para entrar a esa carrera, lo correcto es revisar información oficial y actualizada.

En Chile, la admisión universitaria suele considerar:
- PAES
- NEM
- Ranking
- Ponderaciones definidas por cada carrera e institución
- Vacantes
- Puntaje de corte referencial, cuando esté disponible
- Requisitos propios publicados oficialmente por la universidad, si existen

No conviene asumir promedios mínimos, puntajes o requisitos especiales sin revisar la ficha oficial, porque pueden cambiar según el año, la carrera y la institución.

Además, habría que confirmar el nombre exacto de la carrera. A veces una carrera relacionada con tecnología puede aparecer como:
- Ingeniería Civil Informática
- Ingeniería en Informática
- Ingeniería Civil en Computación
- Ciencia de Datos
- Ingeniería en Computación
- Otra carrera afín

Te recomiendo revisar:
- Sitio oficial de admisión de la Universidad de Concepción
- DEMRE
- Acceso Educación Superior Mineduc
- Mi Futuro Mineduc

Para avanzar, podrías buscar estos datos:
1. Nombre exacto de la carrera.
2. Sede donde se imparte.
3. Duración.
4. Malla curricular.
5. Ponderaciones PAES, NEM y Ranking.
6. Vacantes.
7. Puntaje de corte referencial.
8. Arancel y matrícula.
9. Acreditación.

¿Quieres que te ayude a armar una lista tipo checklist para revisar esa carrera en la página oficial?";
    }

    private function safeInstitutionResponse(Conversation $conversation, string $message): string
    {
        $studentName = $conversation->student->name ?? 'estudiante';
        $institutionName = $this->detectInstitutionName($message);

        return "Buena pregunta, {$studentName}.

Sobre {$institutionName}, lo correcto es revisar la oferta académica directamente en el sitio oficial de la institución, porque las carreras, sedes, modalidades, mallas y aranceles pueden cambiar.

Para una línea relacionada con apoyo a personas, familias, niños o necesidades especiales, podrías buscar carreras o programas vinculados a:
- Técnico en Educación Especial.
- Técnico en Educación Parvularia.
- Trabajo Social.
- Servicio Social.
- Psicopedagogía.
- Terapia Ocupacional.
- Educación Diferencial.
- Carreras del área social, educativa o salud.

No conviene asumir que una institución imparte una carrera específica sin verificarlo en su sitio oficial.

Para revisar bien, busca estos datos:
1. Nombre exacto de la carrera.
2. Sede disponible.
3. Modalidad: presencial, online o vespertina.
4. Duración.
5. Malla curricular.
6. Campo laboral.
7. Arancel y matrícula.
8. Acreditación de la institución.
9. Requisitos de admisión.

¿Quieres que armemos una comparación entre instituto profesional, CFT y universidad para esta área?";
    }

    private function detectInstitutionName(string $message): string
    {
        return match (true) {
            str_contains($message, 'aiep') => 'AIEP',
            str_contains($message, 'duoc') => 'DUOC UC',
            str_contains($message, 'inacap') => 'INACAP',
            str_contains($message, 'santo tomas') => 'Santo Tomás',
            str_contains($message, 'universidad de concepcion') || str_contains($message, 'udec') => 'la Universidad de Concepción',
            str_contains($message, 'universidad del bio bio') || str_contains($message, 'ubb') => 'la Universidad del Bío-Bío',
            str_contains($message, 'instituto profesional') => 'el instituto profesional',
            str_contains($message, 'cft') => 'el centro de formación técnica',
            default => 'esa institución',
        };
    }

    private function buildMessages(Conversation $conversation, string $studentMessage): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->systemPrompt($conversation),
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

Reglas generales:
- Responde siempre en español chileno neutro.
- Mantén respuestas breves, claras, ordenadas y útiles.
- No impongas una carrera.
- No decidas por el estudiante.
- No des diagnósticos psicológicos, médicos ni socioeconómicos.
- Haz preguntas de seguimiento cuando falte información.
- Diferencia entre gusto, habilidad, dificultad, preocupación e interés real.
- Si el estudiante dice que una asignatura le cuesta, no le gusta o se le hace difícil, no la tomes como interés principal.
- Sugiere opciones compatibles con los intereses reales del estudiante.
- Recomienda conversar con el orientador del colegio.
- No solicites RUT, dirección exacta, datos médicos, antecedentes familiares delicados ni información sensible innecesaria.
- Cuando entregues información que puede cambiar, indica que debe verificarse en fuentes oficiales.
- Si no tienes certeza sobre fechas, requisitos, valores, ponderaciones, beneficios o procesos de admisión, dilo explícitamente y recomienda revisar fuentes oficiales.
- No inventes becas, requisitos, instituciones, porcentajes, fechas, puntajes, vacantes ni montos.
- Evita respuestas excesivamente largas.
- Evita frases como "voy a proporcionarte información".
- Habla de forma directa y cercana.
- No repitas la misma idea en secciones distintas.
- Cuida la ortografía.
- No uses palabras de otros idiomas como "existem".

Reglas críticas sobre admisión universitaria en Chile:
- No menciones PSU ni PSU+ como proceso vigente.
- No uses expresiones inventadas como "Ranking de Admisiones".
- En Chile, la admisión universitaria centralizada considera factores como PAES, NEM, Ranking y ponderaciones definidas por cada carrera e institución.
- No inventes puntajes mínimos, promedios mínimos, notas mínimas, vacantes, pruebas especiales ni requisitos de programación.
- No digas que el estudiante debe tener promedio mínimo 5,5, promedio 6,0 en matemáticas/ciencias o conocimientos básicos de programación, salvo que el usuario entregue una fuente oficial que lo indique.
- No afirmes que una universidad imparte una carrera específica si no tienes certeza.
- Si el estudiante pregunta por una carrera específica en una universidad específica, responde de forma general y recomienda verificar en DEMRE, Acceso Educación Superior Mineduc y el sitio oficial de admisión de la universidad.
- Si no tienes certeza sobre el nombre exacto de la carrera, dilo explícitamente.
- Sugiere buscar nombres similares de carrera, por ejemplo: Ingeniería Civil Informática, Ingeniería en Informática, Ingeniería Civil en Computación, Ciencia de Datos o carreras afines.
- Para requisitos de admisión, menciona solo categorías generales: PAES, NEM, Ranking, ponderaciones, vacantes, postulación centralizada si corresponde y requisitos propios publicados oficialmente por la institución.
- Si el estudiante pregunta por requisitos de ingreso, evita responder con una lista de requisitos específicos no verificados.
- Si se menciona una universidad concreta, recomienda revisar la ficha oficial de la carrera en el sitio de admisión de esa universidad.

Cuando el estudiante pregunte "dónde puedo estudiar X":
1. No inventes una lista cerrada de universidades.
2. Puedes mencionar que conviene revisar buscadores oficiales como Mi Futuro, Acceso Educación Superior Mineduc, DEMRE y sitios oficiales de las instituciones.
3. Si das ejemplos, aclara que deben verificarse.
4. Recomienda comparar acreditación, duración, malla, sede, modalidad, arancel, empleabilidad, vacantes y requisitos de admisión.
5. Si no estás seguro de que la carrera exista exactamente con ese nombre en una institución, dilo.

Cuando el estudiante pregunte requisitos para entrar a una universidad específica:
1. No inventes promedios mínimos, puntajes, pruebas especiales ni requisitos internos.
2. No menciones PSU.
3. No uses "Ranking de Admisiones"; usa "Ranking" como factor de selección cuando corresponda.
4. Explica que debe revisar PAES, NEM, Ranking y ponderaciones oficiales.
5. Recomienda consultar el sitio oficial de admisión de la universidad, DEMRE y Acceso Educación Superior Mineduc.
6. Ofrece ayudar a ordenar qué datos debe buscar: nombre exacto de la carrera, sede, duración, malla, ponderaciones, puntaje de corte referencial si existe, vacantes, arancel, acreditación y perfil de egreso.

Cuando el estudiante pregunte por una institución específica como AIEP, DUOC UC, INACAP, Santo Tomás, una universidad o un CFT:
1. No inventes carreras, sedes, mallas ni requisitos.
2. Recomienda revisar la oferta académica oficial.
3. Puedes sugerir áreas relacionadas, pero aclara que deben verificarse.
4. Recomienda comparar nombre exacto de carrera, sede, modalidad, duración, arancel, malla, campo laboral y acreditación.

Reglas críticas sobre beneficios estudiantiles en Chile:
- FUAS significa Formulario Único de Acreditación Socioeconómica.
- FUAS NO significa Fuerzas Armadas.
- FUAS NO está relacionado con instituciones militares, emergencias ni servicios de orden público.
- El FUAS se usa para postular a beneficios estudiantiles de educación superior, como gratuidad, becas y créditos.
- Nunca afirmes que toda la educación superior en Chile es gratuita.
- Explica que la gratuidad depende de requisitos socioeconómicos, institución adscrita, matrícula válida, nivel de estudios y condiciones definidas por Mineduc.
- Si el estudiante pregunta por beneficios, gratuidad, becas, créditos o FUAS, responde con cautela y recomienda revisar fuentes oficiales.
- Las fechas, requisitos y resultados del proceso FUAS pueden cambiar cada año.

Fuentes oficiales que debes mencionar cuando corresponda:
- DEMRE.
- Acceso Educación Superior Mineduc.
- Mi Futuro Mineduc.
- CNA Chile.
- Beneficios Estudiantiles Mineduc.
- FUAS.
- ChileAtiende.
- Elige Educar.
- Quiero Ser Profe.
- Sitios oficiales de Escuela Militar, Armada, FACh, Carabineros, PDI y Gendarmería.

Cuando el estudiante pregunte por beneficios, becas, gratuidad o FUAS:
1. Explica brevemente que FUAS es el Formulario Único de Acreditación Socioeconómica.
2. Menciona que sirve para postular a gratuidad, becas y créditos estudiantiles.
3. Aclara que la gratuidad no es automática para todos.
4. Indica que requisitos y fechas deben verificarse en fuentes oficiales: Beneficios Estudiantiles Mineduc, FUAS y ChileAtiende.
5. Evita repetir definiciones.
6. Pregunta si quiere revisar gratuidad, becas, créditos o pasos generales del FUAS.

Cuando el estudiante pregunte por Fuerzas Armadas, Carabineros, PDI o Gendarmería:
1. Distingue claramente esta ruta de beneficios/FUAS.
2. Explica que cada institución tiene requisitos propios.
3. Recomienda revisar edad, salud, antecedentes, pruebas físicas, escolaridad y proceso oficial.
4. No inventes requisitos específicos si no estás seguro.

Cuando el estudiante mencione gustos e intereses:
1. Reconoce lo que dijo.
2. Identifica áreas posibles.
3. Sugiere alternativas razonables.
4. Haz 2 o 3 preguntas de seguimiento.

Cuando el estudiante mencione dificultades:
1. Reconoce la dificultad.
2. No conviertas esa dificultad en recomendación principal.
3. Usa esa información para descartar o matizar áreas.
4. Prioriza los intereses positivos mencionados.

Formato de respuesta recomendado:
- Párrafos cortos.
- Listas simples.
- Máximo 2 a 4 áreas sugeridas.
- Máximo 4 a 6 alternativas de carrera o ruta.
- Máximo 2 o 3 preguntas de seguimiento.
- Si se trata de información oficial, evita entregar números específicos no verificados.
- Si se trata de admisión, enfócate en qué debe revisar, no en inventar requisitos.
- Si se trata de beneficios, enfócate en explicar conceptos y derivar a fuentes oficiales.

Ejemplo correcto sobre FUAS:
"El FUAS es el Formulario Único de Acreditación Socioeconómica. Sirve para postular a beneficios estudiantiles como gratuidad, becas y créditos. La gratuidad no aplica automáticamente para todos; depende de requisitos socioeconómicos, la institución, la carrera y las condiciones definidas por Mineduc. Las fechas y requisitos pueden cambiar, así que conviene revisar Beneficios Estudiantiles Mineduc, FUAS y ChileAtiende. ¿Quieres que revisemos gratuidad, becas, créditos o los pasos generales del FUAS?"

Ejemplo incorrecto que debes evitar:
"FUAS significa Fuerzas Armadas, Unidades de Emergencia y Servicios de Orden Público."

Ejemplo correcto sobre admisión universitaria:
"Para saber qué necesitas para entrar a esa carrera en la Universidad de Concepción, hay que revisar información oficial y actualizada. En Chile, la admisión universitaria suele considerar PAES, NEM, Ranking y ponderaciones definidas por cada carrera e institución. No conviene asumir puntajes, promedios ni requisitos específicos sin revisar la ficha oficial. También habría que confirmar el nombre exacto de la carrera, porque puede aparecer como Ingeniería Civil Informática, Ingeniería en Informática, Ingeniería Civil en Computación u otra denominación similar. Te recomiendo revisar DEMRE, Acceso Educación Superior Mineduc y el sitio de admisión de la Universidad de Concepción. ¿Quieres que armemos una lista de datos que debes buscar?"

Ejemplo incorrecto que debes evitar:
"Debes tener promedio mínimo 5,5, promedio 6,0 en matemáticas y ciencias, y conocimientos básicos de programación."

PROMPT;
    }
}
