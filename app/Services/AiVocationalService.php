<?php

namespace App\Services;

use App\Models\Conversation;

class AiVocationalService
{
    public function generateResponse(Conversation $conversation, string $studentMessage): string
    {
        $message = $this->normalize($studentMessage);

        if ($this->isCorrectionAboutDifficulty($message)) {
            return $this->difficultyCorrectionResponse($conversation);
        }

        $detectedAreas = $this->detectInterestAreas($message);

        if (empty($detectedAreas)) {
            return $this->genericGuidanceResponse($conversation);
        }

        return $this->buildVocationalResponse($conversation, $detectedAreas);
    }

    private function normalize(string $text): string
    {
        $text = mb_strtolower($text);

        $search = ['á', 'é', 'í', 'ó', 'ú', 'ñ'];
        $replace = ['a', 'e', 'i', 'o', 'u', 'n'];

        return str_replace($search, $replace, $text);
    }

    private function detectInterestAreas(string $message): array
    {
        $areas = [];

        $negativeContext = $this->detectNegativeContext($message);

        $keywords = [
            'tecnologia' => [
                'computador',
                'computadores',
                'tecnologia',
                'programacion',
                'informatica',
                'software',
                'hardware',
                'pc',
                'videojuegos',
                'datos',
                'robotica',
                'inteligencia artificial',
            ],
            'matematica_fisica' => [
                'matematica',
                'matematicas',
                'fisica',
                'calculo',
                'numeros',
                'ingenieria',
                'mecanica',
                'electronica',
                'electricidad',
            ],
            'biologia_salud' => [
                'biologia',
                'salud',
                'medicina',
                'enfermeria',
                'kinesiologia',
                'laboratorio',
                'quimica',
                'bioquimica',
                'biotecnologia',
                'animales',
                'naturaleza',
            ],
            'arte_musica_cultura' => [
                'arte',
                'artes',
                'musica',
                'musical',
                'conciertos',
                'museos',
                'dibujo',
                'pintura',
                'diseno',
                'diseño',
                'cultura',
                'teatro',
                'cine',
                'fotografia',
                'fotografía',
                'danza',
                'manualidades',
                'creatividad',
            ],
            'social_personas' => [
                'personas',
                'ayudar',
                'social',
                'psicologia',
                'comportamiento humano',
                'trastornos mentales',
                'salud mental',
                'filosofia',
                'lenguaje',
                'trabajo social',
                'comunicacion',
                'atender',
                'orientar',
            ],
            'educacion' => [
                'educacion',
                'profesor',
                'profesora',
                'pedagogia',
                'ensenar',
                'enseñar',
                'niños',
                'jovenes',
                'colegio',
            ],
            'seguridad_ffaa' => [
                'carabineros',
                'pdi',
                'militar',
                'armada',
                'fach',
                'gendarmeria',
                'seguridad',
                'escuela militar',
                'escuela naval',
            ],
            'beneficios' => [
                'beca',
                'becas',
                'gratuidad',
                'fuas',
                'credito',
                'cae',
                'financiamiento',
                'arancel',
                'dinero',
            ],
        ];

        foreach ($keywords as $area => $words) {
            foreach ($words as $word) {
                if (str_contains($message, $word)) {
                    if ($this->shouldIgnoreAreaBecauseOfDifficulty($area, $negativeContext)) {
                        continue;
                    }

                    $areas[] = $area;
                    break;
                }
            }
        }

        return array_unique($areas);
    }

    private function detectNegativeContext(string $message): array
    {
        $negativeContext = [];

        $difficultyPatterns = [
            'matematica_fisica' => [
                'me cuesta la matematica',
                'me cuestan las matematicas',
                'me cuesta matematica',
                'me cuesta matematicas',
                'me cuesta mucho la matematica',
                'me cuesta mucho matematica',
                'me cuesta mucho las matematicas',
                'me cuesta mucho matematicas',
                'se me hace dificil matematica',
                'se me hacen dificiles las matematicas',
                'no me gusta matematica',
                'no me gustan las matematicas',
                'odio matematica',
                'odio matematicas',
                'me cuesta fisica',
                'me cuesta la fisica',
                'me cuesta mucho fisica',
                'me cuesta mucho la fisica',
                'se me hace dificil fisica',
                'no me gusta fisica',
                'odio fisica',
            ],
            'tecnologia' => [
                'no me gusta la tecnologia',
                'no me gusta tecnologia',
                'me cuesta programar',
                'no me gusta programar',
                'me cuesta informatica',
                'no me gusta informatica',
            ],
            'biologia_salud' => [
                'no me gusta biologia',
                'me cuesta biologia',
                'no me gusta salud',
                'no me interesa salud',
            ],
            'arte_musica_cultura' => [
                'no me gusta el arte',
                'no me gusta arte',
                'no me gusta musica',
                'no me interesa musica',
                'no me interesa el arte',
            ],
            'educacion' => [
                'no me gusta ensenar',
                'no me gusta enseñar',
                'no me interesa ensenar',
                'no me interesa enseñar',
                'no quiero ser profesor',
                'no quiero ser profesora',
                'no me gusta pedagogia',
            ],
        ];

        foreach ($difficultyPatterns as $area => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($message, $pattern)) {
                    $negativeContext[] = $area;
                    break;
                }
            }
        }

        return array_unique($negativeContext);
    }

    private function shouldIgnoreAreaBecauseOfDifficulty(string $area, array $negativeContext): bool
    {
        return in_array($area, $negativeContext, true);
    }

    private function isCorrectionAboutDifficulty(string $message): bool
    {
        $patterns = [
            'dije que me cuesta',
            'dije que me cuestan',
            'te dije que me cuesta',
            'te dije que me cuestan',
            'me cuesta',
            'me cuestan',
            'se me hace dificil',
            'se me hacen dificiles',
            'no me gusta',
            'no me gustan',
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function difficultyCorrectionResponse(Conversation $conversation): string
    {
        $studentName = $conversation->student->name ?? 'estudiante';

        return "Tienes razón, {$studentName}. Gracias por corregirme.

Si matemáticas o física se te hacen difíciles, no debería tomarlas como áreas principales de interés. En ese caso conviene priorizar lo que sí mencionaste como gusto, interés o actividad que disfrutas.

Por lo que has contado, podríamos explorar mejor áreas como:
- Arte, música, diseño y creatividad
- Humanidades, lenguaje o comunicación
- Pedagogía en artes, música, lenguaje o educación básica
- Psicología, orientación o áreas sociales si te interesa comprender a las personas
- Gestión cultural, producción de eventos o comunicación audiovisual

Para orientarte mejor:
1. ¿Te gustaría dedicarte a crear, enseñar, comunicar o trabajar en actividades culturales?
2. ¿Te interesa más la música, el arte visual, el diseño, el teatro, la escritura o la producción de eventos?
3. ¿Te ves trabajando con niños, jóvenes, público general o de forma más independiente?";
    }

    private function buildVocationalResponse(Conversation $conversation, array $areas): string
    {
        $studentName = $conversation->student->name ?? 'estudiante';
        $route = $conversation->selected_route;

        $response = "Gracias por contarme, {$studentName}. Con lo que mencionas, puedo detectar estas áreas de interés:\n\n";

        foreach ($areas as $area) {
            $response .= "- " . $this->getAreaLabel($area) . "\n";
        }

        $response .= "\n";
        $response .= $this->getRouteSpecificIntro($route);
        $response .= "\n\n";
        $response .= $this->getCareerSuggestions($areas);
        $response .= "\n\n";
        $response .= $this->getFollowUpQuestions($areas, $route);
        $response .= "\n\nRecuerda: esto es una orientación inicial. Antes de decidir, conviene revisar mallas, requisitos de admisión, acreditación, campo laboral y conversar con el orientador del colegio.";

        return $response;
    }

    private function getAreaLabel(string $area): string
    {
        return match ($area) {
            'tecnologia' => 'Tecnología, computación e informática',
            'matematica_fisica' => 'Matemáticas, física e ingeniería',
            'biologia_salud' => 'Biología, salud y ciencias',
            'arte_musica_cultura' => 'Arte, música, cultura y creatividad',
            'social_personas' => 'Trabajo con personas y área social',
            'educacion' => 'Educación y pedagogía',
            'seguridad_ffaa' => 'Fuerzas Armadas, Orden y Seguridad Pública',
            'beneficios' => 'Beneficios estudiantiles y financiamiento',
            default => 'Exploración vocacional general',
        };
    }

    private function getRouteSpecificIntro(?string $route): string
    {
        return match ($route) {
            'universidad' => 'Como seleccionaste la ruta universitaria, podemos revisar carreras profesionales, requisitos de admisión, PAES, NEM, ranking, ponderaciones y alternativas de universidades.',
            'tecnico-profesional' => 'Como seleccionaste la ruta técnico-profesional, podemos revisar carreras técnicas, institutos profesionales, CFT, duración, empleabilidad y continuidad de estudios.',
            'beneficios-fuas' => 'Como seleccionaste beneficios y FUAS, podemos ordenar información sobre gratuidad, becas, créditos, requisitos generales y fechas importantes.',
            'pedagogia' => 'Como seleccionaste pedagogía, podemos revisar vocación docente, requisitos para estudiar pedagogía, acreditación y alternativas en educación.',
            'ffaa-orden' => 'Como seleccionaste FF.AA., Orden y Seguridad, podemos revisar requisitos generales, procesos de admisión, edad, condición física, salud, antecedentes y etapas de postulación.',
            'no-se-aun' => 'Como todavía no tienes una ruta clara, podemos explorar distintas áreas paso a paso hasta encontrar opciones que hagan sentido para ti.',
            default => 'Podemos explorar distintas rutas vocacionales según tus intereses, habilidades y dudas.',
        };
    }

    private function getCareerSuggestions(array $areas): string
    {
        $suggestions = [];

        if (in_array('tecnologia', $areas) || in_array('matematica_fisica', $areas)) {
            $suggestions[] = "Por el lado tecnológico/científico podrías explorar:\n- Ingeniería Civil Informática\n- Ingeniería en Computación\n- Ingeniería Civil Industrial\n- Ingeniería Civil Eléctrica\n- Ingeniería en Automatización o Robótica\n- Analista Programador\n- Técnico en Informática\n- Ciencia de Datos";
        }

        if (in_array('biologia_salud', $areas)) {
            $suggestions[] = "Por el lado de biología y salud podrías explorar:\n- Medicina\n- Enfermería\n- Tecnología Médica\n- Kinesiología\n- Bioquímica\n- Biotecnología\n- Técnico en Enfermería\n- Laboratorio Clínico";
        }

        if (in_array('arte_musica_cultura', $areas)) {
            $suggestions[] = "Por el lado artístico, cultural y creativo podrías explorar:\n- Diseño Gráfico\n- Diseño Digital\n- Diseño de Vestuario\n- Artes Visuales\n- Pedagogía en Artes Visuales\n- Pedagogía en Música\n- Producción Musical\n- Comunicación Audiovisual\n- Gestión Cultural\n- Teatro o Artes Escénicas";
        }

        if (in_array('social_personas', $areas)) {
            $suggestions[] = "Por el lado social podrías explorar:\n- Psicología\n- Trabajo Social\n- Terapia Ocupacional\n- Administración Pública\n- Sociología\n- Orientación o áreas de apoyo comunitario";
        }

        if (in_array('educacion', $areas)) {
            $suggestions[] = "Por el lado educativo podrías explorar:\n- Pedagogía en Educación Básica\n- Educación Diferencial\n- Pedagogía en Lenguaje\n- Pedagogía en Artes Visuales\n- Pedagogía en Música\n- Pedagogía en Inglés\n- Educación Parvularia";
        }

        if (in_array('seguridad_ffaa', $areas)) {
            $suggestions[] = "Por el lado de FF.AA., Orden y Seguridad podrías explorar:\n- Escuela Militar\n- Escuela Naval\n- Escuela de Aviación\n- Escuela de Especialidades FACh\n- Carabineros\n- PDI\n- Gendarmería";
        }

        if (in_array('beneficios', $areas)) {
            $suggestions[] = "Sobre financiamiento, conviene revisar:\n- FUAS\n- Gratuidad\n- Becas del Mineduc\n- Créditos estudiantiles\n- Requisitos socioeconómicos\n- Fechas oficiales de postulación";
        }

        if (empty($suggestions)) {
            return "Aún no tengo suficientes datos para sugerir carreras concretas. Primero conviene aclarar tus intereses, habilidades y tipo de institución que prefieres.";
        }

        return implode("\n\n", $suggestions);
    }

    private function getFollowUpQuestions(array $areas, ?string $route): string
    {
        $questions = [];

        if (in_array('tecnologia', $areas)) {
            $questions[] = "¿Te gustaría más programar software, trabajar con hardware, analizar datos o crear soluciones con inteligencia artificial?";
        }

        if (in_array('matematica_fisica', $areas)) {
            $questions[] = "¿Disfrutas resolver problemas matemáticos complejos o prefieres aplicar la ciencia a cosas prácticas?";
        }

        if (in_array('biologia_salud', $areas)) {
            $questions[] = "¿Te interesa más atender personas, trabajar en laboratorio o investigar temas científicos?";
        }

        if (in_array('arte_musica_cultura', $areas)) {
            $questions[] = "¿Te interesa más crear, interpretar, diseñar, producir eventos, enseñar arte/música o trabajar en gestión cultural?";
        }

        if (in_array('social_personas', $areas)) {
            $questions[] = "¿Te ves trabajando directamente con personas, escuchando, guiando o resolviendo problemas sociales?";
        }

        if (in_array('educacion', $areas)) {
            $questions[] = "¿Te gustaría enseñar a niños, adolescentes o adultos?";
        }

        if (in_array('seguridad_ffaa', $areas)) {
            $questions[] = "¿Te interesa una carrera con disciplina, condición física, jerarquía y servicio público?";
        }

        if ($route === 'universidad') {
            $questions[] = "¿Conoces tu NEM, ranking o puntajes PAES aproximados?";
        }

        if ($route === 'tecnico-profesional') {
            $questions[] = "¿Te gustaría una carrera más corta y práctica, con rápida salida laboral?";
        }

        if ($route === 'beneficios-fuas') {
            $questions[] = "¿Quieres revisar gratuidad, becas, créditos o el proceso FUAS en general?";
        }

        if (empty($questions)) {
            $questions = [
                "¿Qué asignaturas te gustan más?",
                "¿Qué asignaturas se te hacen más difíciles?",
                "¿Prefieres universidad, instituto profesional, CFT, FF.AA. o aún no lo sabes?",
            ];
        }

        $response = "Para seguir orientándote, responde estas preguntas:\n";

        foreach ($questions as $index => $question) {
            $response .= ($index + 1) . ". {$question}\n";
        }

        return trim($response);
    }

    private function genericGuidanceResponse(Conversation $conversation): string
    {
        $studentName = $conversation->student->name ?? 'estudiante';

        return "Gracias por contarme, {$studentName}. Para poder orientarte mejor necesito conocer un poco más sobre tus intereses.

Respóndeme algunas de estas preguntas:
1. ¿Qué asignaturas te gustan más?
2. ¿Qué asignaturas se te hacen más difíciles?
3. ¿Qué actividades disfrutas hacer fuera del colegio?
4. ¿Prefieres trabajar con personas, tecnología, números, salud, educación, arte, seguridad o naturaleza?
5. ¿Ya tienes alguna carrera en mente o todavía estás explorando?";
    }
}
