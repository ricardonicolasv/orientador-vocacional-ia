<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\VocationalReport;
use Illuminate\Support\Facades\DB;

class ReportGeneratorService
{
    public function generate(Conversation $conversation): VocationalReport
    {
        $conversation->load(['student', 'messages']);

        $studentMessages = $conversation->messages
            ->where('sender', 'student')
            ->pluck('content')
            ->implode(' ');

        $normalizedText = $this->normalize($studentMessages);

        $detectedAreas = $this->detectAreas($normalizedText);
        $difficulties = $this->detectDifficulties($normalizedText);
        $clarityLevel = $this->estimateClarityLevel($normalizedText, $conversation->selected_route);

        return DB::transaction(function () use (
            $conversation,
            $studentMessages,
            $detectedAreas,
            $difficulties,
            $clarityLevel,
            $normalizedText
        ) {
            $lastReport = VocationalReport::where('conversation_id', $conversation->id)
                ->lockForUpdate()
                ->orderByDesc('version')
                ->first();

            $nextVersion = ($lastReport?->version ?? 0) + 1;

            VocationalReport::where('conversation_id', $conversation->id)
                ->where('is_current', true)
                ->update([
                    'is_current' => false,
                ]);

            $lastMessageId = $conversation->messages()
                ->latest('id')
                ->value('id');

            return VocationalReport::create([
                'student_id' => $conversation->student_id,
                'conversation_id' => $conversation->id,
                'version' => $nextVersion,
                'is_current' => true,
                'generated_until_message_id' => $lastMessageId,

                'interests' => $this->extractInterests($studentMessages, $detectedAreas, $difficulties),
                'detected_areas' => implode(', ', $detectedAreas),
                'explored_routes' => $this->formatRoute($conversation->selected_route),
                'main_questions' => $this->extractMainQuestions($normalizedText),
                'clarity_level' => $clarityLevel,
                'recommendations' => $this->buildRecommendations($detectedAreas, $difficulties, $conversation->selected_route),
                'student_summary' => $this->buildStudentSummary($conversation, $detectedAreas, $difficulties, $clarityLevel),
                'orientador_notes' => $this->buildOrientadorNotes($conversation, $detectedAreas, $difficulties, $clarityLevel),
            ]);
        });
    }
    private function containsKeyword(string $text, string $keyword): bool
    {
        $keyword = $this->normalize($keyword);

        if (str_contains($keyword, ' ')) {
            return str_contains($text, $keyword);
        }

        return preg_match('/\b' . preg_quote($keyword, '/') . '\b/u', $text) === 1;
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

    private function detectAreas(string $text): array
    {
        $areas = [];
        $negativeContext = $this->detectNegativeContext($text);

        $keywords = [
            'Tecnología, computación e informática' => [
                'computador',
                'computadores',
                'tecnologia',
                'programacion',
                'informatica',
                'software',
                'hardware',
                'videojuegos',
                'datos',
                'robotica',
                'inteligencia artificial',
                'sistemas',
            ],
            'Deporte, turismo aventura y naturaleza' => [
                'deporte',
                'deportes',
                'educacion fisica',
                'actividad fisica',
                'aire libre',
                'trekking',
                'incursiones',
                'senderismo',
                'explorar',
                'paisaje',
                'paisajes',
                'naturaleza',
                'turismo aventura',
                'ecoturismo',
                'guia turistico',
                'guia de turismo',
                'guia de actividades',
                'actividades al aire libre',
                'guardaparques',
                'areas protegidas',
                'conservacion',
            ],
            'Matemáticas, física e ingeniería' => [
                'matematica',
                'matematicas',
                'calculo',
                'numeros',
                'ingenieria',
                'industrial',
                'procesos',
                'mecanica',
                'electronica',
                'electricidad',
                'estadistica',
            ],
            'Biología, salud y ciencias' => [
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
                'terapia ocupacional',
            ],
            'Arte, música, cultura y creatividad' => [
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
                'comunicacion audiovisual',
            ],
            'Área social, psicología y trabajo con personas' => [
                'personas',
                'ayudar',
                'social',
                'psicologia',
                'psicologia infantil',
                'comportamiento humano',
                'trastornos mentales',
                'salud mental',
                'familia',
                'terapia familiar',
                'apoyo familiar',
                'trabajo social',
                'servicio social',
                'comunicacion',
                'atender',
                'orientar',
                'escuchar',
                'comprender a las personas',
            ],
            'Educación, pedagogía y trabajo con niños' => [
                'educacion',
                'profesor',
                'profesora',
                'pedagogia',
                'ensenar',
                'enseñar',
                'niños',
                'ninos',
                'jovenes',
                'colegio',
                'educacion especial',
                'educacion diferencial',
                'psicopedagogia',
                'parvularia',
                'educacion parvularia',
            ],
            'Administración, negocios y gestión' => [
                'administracion',
                'negocios',
                'empresa',
                'gestion',
                'finanzas',
                'contabilidad',
                'logistica',
                'produccion',
                'recursos humanos',
                'emprendimiento',
                'liderazgo',
                'proyectos',
                'industrial',
            ],
            'Fuerzas Armadas, Orden y Seguridad Pública' => [
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
            'Beneficios estudiantiles y financiamiento' => [
                'beca',
                'becas',
                'gratuidad',
                'fuas',
                'credito',
                'cae',
                'financiamiento',
                'arancel',
                'dinero',
                'beneficios',
            ],
            'Derecho, ciencias políticas y gestión pública' => [
                'derecho',
                'ciencias politicas',
                'politica',
                'politico',
                'politicos',
                'politicas publicas',
                'cargo publico',
                'administracion publica',
                'gestion publica',
                'estado',
                'gobierno',
                'municipalidad',
                'servicio publico',
                'leyes',
                'legislacion',
                'relaciones internacionales',
            ],
        ];

        foreach ($keywords as $area => $words) {
            foreach ($words as $word) {
                if ($this->containsKeyword($text, $word)) {
                    if ($this->shouldIgnoreAreaBecauseOfDifficulty($area, $negativeContext)) {
                        continue;
                    }

                    $areas[] = $area;
                    break;
                }
            }
        }

        return array_values(array_unique($areas)) ?: ['Exploración vocacional general'];
    }

    private function detectNegativeContext(string $text): array
    {
        $negativeContext = [];

        $difficultyPatterns = [
            'Matemáticas, física e ingeniería' => [
                'me cuesta la matematica',
                'me cuestan las matematicas',
                'me cuesta matematica',
                'me cuesta matematicas',
                'me cuesta mucho la matematica',
                'me cuesta mucho matematica',
                'me cuesta fisica',
                'me cuesta la fisica',
                'me cuesta mucho fisica',
                'me cuesta mucho la fisica',
                'se me hace dificil matematica',
                'se me hacen dificiles las matematicas',
                'se me hace dificil fisica',
                'no me gusta matematica',
                'no me gustan las matematicas',
                'no me gusta fisica',
                'odio matematica',
                'odio matematicas',
                'odio fisica',
            ],
            'Tecnología, computación e informática' => [
                'no me gusta la tecnologia',
                'no me gusta tecnologia',
                'me cuesta programar',
                'no me gusta programar',
                'me cuesta informatica',
                'no me gusta informatica',
            ],
            'Biología, salud y ciencias' => [
                'no me gusta biologia',
                'me cuesta biologia',
                'no me gusta salud',
                'no me interesa salud',
            ],
            'Arte, música, cultura y creatividad' => [
                'no me gusta el arte',
                'no me gusta arte',
                'no me gusta musica',
                'no me interesa musica',
                'no me interesa el arte',
            ],
            'Educación, pedagogía y trabajo con niños' => [
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
                if (str_contains($text, $pattern)) {
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

    private function detectDifficulties(string $text): array
    {
        $difficulties = [];

        $patterns = [
            'Matemática' => [
                'me cuesta matematica',
                'me cuesta la matematica',
                'me cuestan las matematicas',
                'se me hace dificil matematica',
                'no me gusta matematica',
                'no me gustan las matematicas',
            ],
            'Física' => [
                'me cuesta fisica',
                'me cuesta la fisica',
                'se me hace dificil fisica',
                'no me gusta fisica',
            ],
            'Programación o informática' => [
                'me cuesta programar',
                'no me gusta programar',
                'me cuesta informatica',
                'no me gusta informatica',
            ],
            'Biología o ciencias de la salud' => [
                'me cuesta biologia',
                'no me gusta biologia',
                'no me interesa salud',
            ],
            'Pedagogía o enseñanza' => [
                'no quiero ser profesor',
                'no quiero ser profesora',
                'no me gusta pedagogia',
                'no me gusta ensenar',
                'no me gusta enseñar',
            ],
        ];

        foreach ($patterns as $difficulty => $difficultyPatterns) {
            foreach ($difficultyPatterns as $pattern) {
                if (str_contains($text, $pattern)) {
                    $difficulties[] = $difficulty;
                    break;
                }
            }
        }

        return array_values(array_unique($difficulties));
    }

    private function estimateClarityLevel(string $text, ?string $route): string
    {
        $specificCareerIndicators = [
            'ingenieria industrial',
            'ingenieria civil informatica',
            'ingenieria informatica',
            'informatica',
            'programacion',
            'medicina',
            'enfermeria',
            'kinesiologia',
            'psicologia',
            'trabajo social',
            'terapia ocupacional',
            'educacion diferencial',
            'educacion especial',
            'pedagogia',
            'diseno',
            'diseño',
            'artes visuales',
            'musica',
            'comunicacion audiovisual',
            'carabineros',
            'pdi',
            'militar',
            'derecho',
            'ciencias politicas',
            'administracion publica',
            'gestion publica',
            'cargo publico',
            'politica',
            'politicas publicas',
            'turismo aventura',
            'ecoturismo',
            'trekking',
            'aire libre',
            'naturaleza',
            'educacion fisica',
            'guia de turismo',
            'guardaparques',
        ];

        $hasSpecificCareer = false;

        foreach ($specificCareerIndicators as $indicator) {
            if ($this->containsKeyword($text, $indicator)) {
                $hasSpecificCareer = true;
                break;
            }
        }

        $hasInstitutionPreference =
            $this->containsKeyword($text, 'universidad') ||
            $this->containsKeyword($text, 'instituto') ||
            $this->containsKeyword($text, 'cft') ||
            $this->containsKeyword($text, 'duoc') ||
            $this->containsKeyword($text, 'inacap') ||
            $this->containsKeyword($text, 'aiep') ||
            str_contains($text, 'santo tomas') ||
            str_contains($text, 'concepcion');

        if ($hasSpecificCareer && $hasInstitutionPreference) {
            return 'alto';
        }

        if ($hasSpecificCareer) {
            return 'medio';
        }

        $uncertaintyIndicators = [
            'no se',
            'no tengo claro',
            'no tengo carrera',
            'no tengo una carrera',
            'todavia estoy explorando',
            'aun no se',
            'no se aun',
            'estoy indeciso',
            'estoy indecisa',
            'necesito orientacion',
            'necesito orientacion paso a paso',
        ];

        foreach ($uncertaintyIndicators as $indicator) {
            if (str_contains($text, $indicator)) {
                return 'bajo';
            }
        }

        if ($route && $route !== 'no-se-aun') {
            return 'medio';
        }

        return 'bajo';
    }

    private function extractInterests(string $originalText, array $detectedAreas, array $difficulties): string
    {
        if (trim($originalText) === '') {
            return 'No se registraron intereses suficientes durante la conversación.';
        }

        $normalizedText = $this->normalize($originalText);

        $interests = [];
        if (
            $this->containsKeyword($normalizedText, 'deporte') ||
            $this->containsKeyword($normalizedText, 'deportes') ||
            $this->containsKeyword($normalizedText, 'educacion fisica') ||
            $this->containsKeyword($normalizedText, 'aire libre') ||
            $this->containsKeyword($normalizedText, 'trekking') ||
            $this->containsKeyword($normalizedText, 'incursiones') ||
            $this->containsKeyword($normalizedText, 'paisaje') ||
            $this->containsKeyword($normalizedText, 'naturaleza') ||
            $this->containsKeyword($normalizedText, 'turismo aventura') ||
            $this->containsKeyword($normalizedText, 'ecoturismo')
        ) {
            $interests[] = 'Deporte, actividad física, trekking, naturaleza, turismo aventura o ecoturismo.';
        }

        if (
            $this->containsKeyword($normalizedText, 'derecho') ||
            $this->containsKeyword($normalizedText, 'ciencias politicas') ||
            $this->containsKeyword($normalizedText, 'administracion publica') ||
            $this->containsKeyword($normalizedText, 'gestion publica') ||
            $this->containsKeyword($normalizedText, 'cargo publico') ||
            $this->containsKeyword($normalizedText, 'politica')
        ) {
            $interests[] = 'Derecho, Ciencias Políticas, Administración Pública o áreas vinculadas al Estado.';
        }

        if (
            $this->containsKeyword($normalizedText, 'fuas') ||
            $this->containsKeyword($normalizedText, 'gratuidad') ||
            $this->containsKeyword($normalizedText, 'beca') ||
            $this->containsKeyword($normalizedText, 'becas') ||
            $this->containsKeyword($normalizedText, 'financiamiento') ||
            $this->containsKeyword($normalizedText, 'credito') ||
            $this->containsKeyword($normalizedText, 'creditos')
        ) {
            $interests[] = 'Beneficios estudiantiles, gratuidad, becas, créditos y financiamiento.';
        }
        if (
            $this->containsKeyword($normalizedText, 'duoc') ||
            $this->containsKeyword($normalizedText, 'ip') ||
            $this->containsKeyword($normalizedText, 'cft') ||
            $this->containsKeyword($normalizedText, 'universidad')
        ) {
            $antecedents[] = 'El estudiante quiere comparar rutas formativas como universidad, instituto profesional o CFT.';
        }

        if (
            $this->containsKeyword($normalizedText, 'concepcion') ||
            $this->containsKeyword($normalizedText, 'udd') ||
            $this->containsKeyword($normalizedText, 'udec') ||
            $this->containsKeyword($normalizedText, 'unab') ||
            $this->containsKeyword($normalizedText, 'uss')
        ) {
            $interests[] = 'Comparación de instituciones o alternativas de estudio en Concepción.';
        }

        if (
            $this->containsKeyword($normalizedText, 'psicologia') ||
            $this->containsKeyword($normalizedText, 'trabajo social') ||
            $this->containsKeyword($normalizedText, 'educacion especial') ||
            $this->containsKeyword($normalizedText, 'educacion diferencial') ||
            $this->containsKeyword($normalizedText, 'ayudar')
        ) {
            $interests[] = 'Área social, apoyo a personas, educación o acompañamiento.';
        }

        if (
            $this->containsKeyword($normalizedText, 'lenguaje') ||
            $this->containsKeyword($normalizedText, 'historia') ||
            $this->containsKeyword($normalizedText, 'ciencias sociales') ||
            $this->containsKeyword($normalizedText, 'filosofia')
        ) {
            $interests[] = 'Humanidades, lenguaje, historia, filosofía o ciencias sociales.';
        }

        $antecedents = [];

        if (str_contains($normalizedText, '60%') || str_contains($normalizedText, 'menores ingresos')) {
            $antecedents[] = 'El estudiante indica pertenecer al 60% de hogares de menores ingresos y consulta por gratuidad.';
        }

        if (
            str_contains($normalizedText, '5 anos') ||
            str_contains($normalizedText, '5 años') ||
            str_contains($normalizedText, '10 semestres')
        ) {
            $antecedents[] = 'El estudiante revisó información institucional e indicó que Derecho en UDD dura 5 años o 10 semestres.';
        }

        if (
            $this->containsKeyword($normalizedText, 'aiep') ||
            $this->containsKeyword($normalizedText, 'duoc') ||
            $this->containsKeyword($normalizedText, 'inacap') ||
            $this->containsKeyword($normalizedText, 'udd') ||
            $this->containsKeyword($normalizedText, 'unab') ||
            $this->containsKeyword($normalizedText, 'uss') ||
            $this->containsKeyword($normalizedText, 'universidad')
        ) {
            $antecedents[] = 'El estudiante muestra interés en comparar instituciones y verificar condiciones oficiales.';
        }

        $response = '';

        if (!empty($interests)) {
            $response .= "Intereses principales detectados:\n";
            foreach (array_unique($interests) as $interest) {
                $response .= "- {$interest}\n";
            }
        } else {
            $response .= "Intereses principales detectados:\n";
            foreach ($detectedAreas as $area) {
                $response .= "- {$area}\n";
            }
        }

        if (!empty($antecedents)) {
            $response .= "\nAntecedentes relevantes:\n";
            foreach (array_unique($antecedents) as $antecedent) {
                $response .= "- {$antecedent}\n";
            }
        }

        if (!empty($difficulties)) {
            $response .= "\nDificultades o áreas a reforzar mencionadas:\n";
            foreach ($difficulties as $difficulty) {
                $response .= "- {$difficulty}\n";
            }
        }

        return trim($response);
    }

    private function extractMainQuestions(string $text): string
    {
        $questions = [];

        if (
            str_contains($text, 'no se') ||
            str_contains($text, 'no tengo claro') ||
            str_contains($text, 'no tengo carrera') ||
            str_contains($text, 'necesito orientacion')
        ) {
            $questions[] = 'El estudiante manifiesta dudas vocacionales y requiere apoyo para comparar alternativas.';
        }

        if (
            str_contains($text, 'fuas') ||
            str_contains($text, 'beca') ||
            str_contains($text, 'gratuidad') ||
            str_contains($text, 'credito') ||
            str_contains($text, 'financiamiento')
        ) {
            $questions[] = 'El estudiante consulta por beneficios, becas, gratuidad, créditos o financiamiento.';
        }

        if (
            str_contains($text, 'universidad') ||
            str_contains($text, 'instituto') ||
            str_contains($text, 'cft') ||
            str_contains($text, 'aiep') ||
            str_contains($text, 'duoc') ||
            str_contains($text, 'inacap') ||
            str_contains($text, 'santo tomas')
        ) {
            $questions[] = 'El estudiante muestra interés en comparar instituciones o rutas formativas.';
        }

        if (
            str_contains($text, 'me cuesta') ||
            str_contains($text, 'se me hace dificil') ||
            str_contains($text, 'no me gusta')
        ) {
            $questions[] = 'El estudiante menciona dificultades académicas o áreas que no le resultan cómodas.';
        }

        if (empty($questions)) {
            return 'Durante esta conversación inicial no se identificaron dudas explícitas, pero se recomienda profundizar en intereses, requisitos y alternativas de estudio.';
        }

        return implode("\n", array_map(fn($item) => '- ' . $item, $questions));
    }

    private function formatRoute(?string $route): string
    {
        return match ($route) {
            'universidad' => 'Ruta universitaria',
            'tecnico-profesional' => 'Ruta técnico-profesional',
            'beneficios-fuas' => 'Beneficios estudiantiles, gratuidad y FUAS',
            'pedagogia' => 'Pedagogía',
            'ffaa-orden' => 'Fuerzas Armadas, de Orden y Seguridad Pública',
            'no-se-aun' => 'Exploración vocacional general',
            default => 'No especificada',
        };
    }

    private function buildRecommendations(array $areas, array $difficulties, ?string $route): string
    {
        $recommendations = [];

        $recommendations[] = 'Conversar con el orientador del colegio para profundizar el análisis vocacional.';
        $recommendations[] = 'Revisar mallas curriculares, duración, campo laboral, aranceles y acreditación de las carreras de interés.';
        $recommendations[] = 'Verificar información oficial antes de tomar decisiones sobre admisión, beneficios o instituciones.';

        if ($route === 'universidad') {
            $recommendations[] = 'Revisar PAES, NEM, Ranking, ponderaciones, vacantes y requisitos oficiales de cada carrera.';
        }

        if ($route === 'tecnico-profesional') {
            $recommendations[] = 'Comparar institutos profesionales y CFT según modalidad, sede, duración, continuidad de estudios y empleabilidad.';
        }

        if ($route === 'beneficios-fuas') {
            $recommendations[] = 'Revisar FUAS, gratuidad, becas y créditos en fuentes oficiales del Mineduc y ChileAtiende.';
        }

        if ($route === 'pedagogia') {
            $recommendations[] = 'Revisar vocación docente, requisitos específicos de pedagogía y acreditación de la carrera.';
        }

        if (in_array('Tecnología, computación e informática', $areas)) {
            $recommendations[] = 'Explorar carreras como Informática, Programación, Ciencia de Datos, Computación o áreas tecnológicas.';
        }

        if (in_array('Matemáticas, física e ingeniería', $areas)) {
            $recommendations[] = 'Explorar carreras de ingeniería, estadística, procesos, industrial o áreas cuantitativas, contrastando con rendimiento e interés real.';
        }

        if (in_array('Biología, salud y ciencias', $areas)) {
            $recommendations[] = 'Explorar carreras de salud, laboratorio, ciencias biológicas o terapia ocupacional.';
        }

        if (in_array('Arte, música, cultura y creatividad', $areas)) {
            $recommendations[] = 'Explorar carreras vinculadas a diseño, artes visuales, música, comunicación audiovisual o gestión cultural.';
        }

        if (in_array('Área social, psicología y trabajo con personas', $areas)) {
            $recommendations[] = 'Explorar Psicología, Trabajo Social, Terapia Ocupacional, Servicio Social u orientación familiar.';
        }

        if (in_array('Educación, pedagogía y trabajo con niños', $areas)) {
            $recommendations[] = 'Explorar Educación Diferencial, Educación Especial, Psicopedagogía, Educación Parvularia o pedagogías afines.';
        }

        if (in_array('Administración, negocios y gestión', $areas)) {
            $recommendations[] = 'Explorar Administración, Logística, Gestión de Personas, Ingeniería Industrial o carreras del área de negocios.';
        }
        if (in_array('Derecho, ciencias políticas y gestión pública', $areas)) {
            $recommendations[] = 'Explorar Derecho, Ciencias Políticas, Administración Pública, Gestión Pública o carreras vinculadas al Estado y políticas públicas.';
        }
        if (in_array('Deporte, turismo aventura y naturaleza', $areas)) {
            $recommendations[] = 'Explorar Turismo Aventura, Ecoturismo, Guía de Turismo, Gestión de Áreas Naturales, Preparación Física o carreras vinculadas al trabajo en terreno y naturaleza.';
        }

        if (!empty($difficulties)) {
            $recommendations[] = 'Considerar las asignaturas o áreas que el estudiante menciona como difíciles para definir apoyos o rutas alternativas.';
        }

        return implode("\n", array_map(fn($item) => '- ' . $item, $recommendations));
    }

    private function buildStudentSummary(
        Conversation $conversation,
        array $areas,
        array $difficulties,
        string $clarityLevel
    ): string {
        $studentName = $conversation->student->name;

        $summary = "Resumen para {$studentName}:\n\n";
        $summary .= "Durante la conversación se identificaron intereses o antecedentes relacionados con:\n";
        $summary .= implode("\n", array_map(fn($area) => '- ' . $area, $areas));

        if (!empty($difficulties)) {
            $summary .= "\n\nTambién se mencionaron áreas que podrían requerir refuerzo o revisión:\n";
            $summary .= implode("\n", array_map(fn($difficulty) => '- ' . $difficulty, $difficulties));
        }

        $summary .= "\n\nNivel de claridad vocacional estimado: {$clarityLevel}.\n\n";
        $summary .= "Este resultado no define una decisión final, pero sirve como punto de partida para comparar carreras, instituciones, requisitos de admisión y beneficios estudiantiles.";

        return $summary;
    }

    private function buildOrientadorNotes(
        Conversation $conversation,
        array $areas,
        array $difficulties,
        string $clarityLevel
    ): string {
        $notes = "Notas para el orientador:\n\n";
        $notes .= "- Revisar conversación completa del estudiante.\n";
        $notes .= "- Diferenciar intereses declarados de asignaturas que el estudiante menciona como difíciles.\n";
        $notes .= "- Profundizar en intereses, rendimiento académico, expectativas familiares y opciones reales de acceso.\n";
        $notes .= "- Nivel de claridad vocacional estimado: {$clarityLevel}.\n";
        $notes .= "- Áreas detectadas: " . implode(', ', $areas) . ".\n";

        if (!empty($difficulties)) {
            $notes .= "- Dificultades mencionadas: " . implode(', ', $difficulties) . ".\n";
        }

        $notes .= "- Se recomienda seguimiento individual si el estudiante mantiene dudas amplias o no identifica rutas concretas.";

        return $notes;
    }
}
