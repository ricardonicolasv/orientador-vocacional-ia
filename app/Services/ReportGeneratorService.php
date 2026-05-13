<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\VocationalReport;

class ReportGeneratorService
{
    public function generate(Conversation $conversation): VocationalReport
    {
        $conversation->load(['student', 'messages']);

        $studentMessages = $conversation->messages
            ->where('sender', 'student')
            ->pluck('content')
            ->implode(' ');

        $detectedAreas = $this->detectAreas($studentMessages);
        $clarityLevel = $this->estimateClarityLevel($studentMessages, $conversation->selected_route);

        return VocationalReport::updateOrCreate(
            [
                'student_id' => $conversation->student_id,
                'conversation_id' => $conversation->id,
            ],
            [
                'interests' => $this->extractInterests($studentMessages),
                'detected_areas' => implode(', ', $detectedAreas),
                'explored_routes' => $this->formatRoute($conversation->selected_route),
                'main_questions' => $this->extractMainQuestions($studentMessages),
                'clarity_level' => $clarityLevel,
                'recommendations' => $this->buildRecommendations($detectedAreas, $conversation->selected_route),
                'student_summary' => $this->buildStudentSummary($conversation, $detectedAreas, $clarityLevel),
                'orientador_notes' => $this->buildOrientadorNotes($conversation, $detectedAreas, $clarityLevel),
            ]
        );
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
        $text = $this->normalize($text);

        $areas = [];

        $keywords = [
            'Tecnología, computación e informática' => [
                'computador',
                'computadores',
                'tecnologia',
                'programacion',
                'informatica',
                'software',
                'hardware',
                'pc',
                'datos',
                'robotica',
                'inteligencia artificial',
            ],
            'Matemáticas, física e ingeniería' => [
                'matematica',
                'matematicas',
                'fisica',
                'calculo',
                'numeros',
                'ingenieria',
                'mecanica',
                'electronica',
                'electricidad'
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
                'biotecnologia'
            ],
            'Área social, psicología y trabajo con personas' => [
                'personas',
                'ayudar',
                'social',
                'psicologia',
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
            'Educación y pedagogía' => [
                'educacion',
                'profesor',
                'profesora',
                'pedagogia',
                'ensenar',
                'niños',
                'jovenes',
                'colegio'
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
                'escuela naval'
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
                'dinero'
            ],
        ];

        foreach ($keywords as $area => $words) {
            foreach ($words as $word) {
                if (str_contains($text, $word)) {
                    $areas[] = $area;
                    break;
                }
            }
        }

        return array_unique($areas) ?: ['Exploración vocacional general'];
    }

    private function estimateClarityLevel(string $text, ?string $route): string
    {
        $text = $this->normalize($text);

        $careerIndicators = [
            'ingenieria',
            'medicina',
            'enfermeria',
            'psicologia',
            'pedagogia',
            'informatica',
            'computacion',
            'derecho',
            'arquitectura',
            'tecnico',
            'carabineros',
            'pdi',
            'militar'
        ];

        $hasSpecificCareer = false;

        foreach ($careerIndicators as $indicator) {
            if (str_contains($text, $indicator)) {
                $hasSpecificCareer = true;
                break;
            }
        }

        if ($hasSpecificCareer && $route && $route !== 'no-se-aun') {
            return 'alto';
        }

        if ($route && $route !== 'no-se-aun') {
            return 'medio';
        }

        return 'bajo';
    }

    private function extractInterests(string $text): string
    {
        if (trim($text) === '') {
            return 'No se registraron intereses suficientes durante la conversación.';
        }

        return 'Intereses mencionados por el estudiante: ' . trim($text);
    }

    private function extractMainQuestions(string $text): string
    {
        $normalized = $this->normalize($text);

        if (str_contains($normalized, 'no se') || str_contains($normalized, 'duda') || str_contains($normalized, 'no tengo claro')) {
            return 'El estudiante manifiesta dudas vocacionales y requiere apoyo para comparar alternativas.';
        }

        return 'Durante esta conversación inicial no se identificaron dudas explícitas, pero se recomienda profundizar en intereses, requisitos y alternativas de estudio.';
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

    private function buildRecommendations(array $areas, ?string $route): string
    {
        $recommendations = [];

        $recommendations[] = 'Conversar con el orientador del colegio para profundizar el análisis vocacional.';
        $recommendations[] = 'Revisar mallas curriculares de las carreras de interés.';
        $recommendations[] = 'Comparar duración, requisitos, campo laboral, empleabilidad e ingresos referenciales cuando existan datos oficiales.';
        $recommendations[] = 'Verificar acreditación institucional y de carrera cuando corresponda.';

        if ($route === 'universidad') {
            $recommendations[] = 'Revisar requisitos de admisión, PAES, NEM, Ranking y ponderaciones en fuentes oficiales.';
        }

        if ($route === 'tecnico-profesional') {
            $recommendations[] = 'Comparar alternativas en institutos profesionales y centros de formación técnica.';
            $recommendations[] = 'Evaluar continuidad de estudios y salida laboral.';
        }

        if ($route === 'beneficios-fuas') {
            $recommendations[] = 'Revisar fechas, requisitos y postulación FUAS en canales oficiales del Mineduc.';
        }

        if ($route === 'pedagogia') {
            $recommendations[] = 'Revisar requisitos específicos para estudiar pedagogía y acreditación de la carrera.';
        }

        if ($route === 'ffaa-orden') {
            $recommendations[] = 'Revisar requisitos de edad, salud, condición física, antecedentes y etapas de postulación en cada institución.';
        }

        if (in_array('Tecnología, computación e informática', $areas)) {
            $recommendations[] = 'Explorar carreras como Informática, Computación, Ciencia de Datos, Automatización o áreas tecnológicas.';
        }

        if (in_array('Biología, salud y ciencias', $areas)) {
            $recommendations[] = 'Explorar carreras de salud, laboratorio, biotecnología o ciencias biológicas.';
        }

        return implode("\n", array_map(fn($item) => '- ' . $item, $recommendations));
    }

    private function buildStudentSummary(Conversation $conversation, array $areas, string $clarityLevel): string
    {
        $studentName = $conversation->student->name;

        return "Resumen para {$studentName}:\n\n"
            . "Durante esta conversación se identificaron intereses relacionados con:\n"
            . implode("\n", array_map(fn($area) => '- ' . $area, $areas))
            . "\n\nTu nivel de claridad vocacional estimado es: {$clarityLevel}.\n\n"
            . "Este resultado no define tu decisión final, pero sirve como punto de partida para seguir explorando carreras, instituciones, requisitos de admisión y beneficios estudiantiles.";
    }

    private function buildOrientadorNotes(Conversation $conversation, array $areas, string $clarityLevel): string
    {
        return "Notas para el orientador:\n\n"
            . "- Revisar conversación completa del estudiante.\n"
            . "- Profundizar en intereses declarados y contrastarlos con rendimiento académico, expectativas familiares y opciones reales de acceso.\n"
            . "- Nivel de claridad vocacional estimado: {$clarityLevel}.\n"
            . "- Áreas detectadas: " . implode(', ', $areas) . ".\n"
            . "- Se recomienda seguimiento individual si el estudiante mantiene dudas amplias o no identifica rutas concretas.";
    }
}
