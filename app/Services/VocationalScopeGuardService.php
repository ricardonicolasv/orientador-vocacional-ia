<?php

namespace App\Services;

class VocationalScopeGuardService
{
    public function generateIfOutOfScope(string $message): ?string
    {
        $text = $this->normalize($message);

        if ($this->isClearlyVocational($text)) {
            return null;
        }

        if ($this->isOutOfScope($text)) {
            return $this->outOfScopeResponse();
        }

        return null;
    }

    private function isClearlyVocational(string $text): bool
    {
        $keywords = [
            'carrera',
            'carreras',
            'universidad',
            'universidades',
            'instituto',
            'instituto profesional',
            'ip',
            'cft',
            'tecnico',
            'tecnica',
            'vocacion',
            'vocacional',
            'orientacion',
            'estudiar',
            'estudios',
            'malla',
            'admision',
            'paes',
            'nem',
            'ranking',
            'ponderacion',
            'fuas',
            'gratuidad',
            'beca',
            'becas',
            'credito',
            'arancel',
            'matricula',
            'campo laboral',
            'empleabilidad',
            'pedagogia',
            'salud',
            'ingenieria',
            'informatica',
            'derecho',
            'turismo',
            'ecoturismo',
            'turismo aventura',
            'duoc',
            'inacap',
            'aiep',
            'santo tomas',
            'carabineros',
            'pdi',
            'armada',
            'ejercito',
            'fuerza aerea',
            'gendarmeria',
        ];

        return $this->containsAny($text, $keywords);
    }

    private function isOutOfScope(string $text): bool
    {
        $blockedKeywords = [
            // videojuegos / gaming
            'ragnarok',
            'build rk',
            'rune knight',
            'runeknight',
            'diablo',
            'minecraft',
            'fortnite',
            'lol',
            'league of legends',
            'valorant',
            'genshin',
            'pokemon',

            // soporte técnico/código fuera de orientación
            'codigo php',
            'codigo laravel',
            'programame',
            'hazme una app',
            'error 500',
            'base de datos',
            'sql',
            'javascript',
            'python',

            // temas generales no vocacionales
            'receta',
            'cocina',
            'auto',
            'mecanico',
            'motor',
            'jeep',
            'kia',
            'tapiceria',
            'precio de',
            'clima',
            'horoscopo',
            'pelicula',
            'serie',
            'cancion',
            'futbol',
            'mundial',
        ];

        return $this->containsAny($text, $blockedKeywords);
    }

    private function outOfScopeResponse(): string
    {
        return 'Esta plataforma está enfocada en orientación vocacional del Instituto San José. Puedo ayudarte con carreras, rutas de estudio, universidad, IP, CFT, beneficios estudiantiles, FUAS, admisión, pedagogías o instituciones de formación.

Si quieres, podemos volver a tu orientación con una pregunta como:
- ¿Qué carrera podría relacionarse con mis intereses?
- ¿Me conviene universidad, IP o CFT?
- ¿Qué debo comparar antes de elegir una institución?';
    }

    private function containsAny(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $text): string
    {
        $text = mb_strtolower($text);

        return str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ'],
            ['a', 'e', 'i', 'o', 'u', 'u', 'n'],
            $text
        );
    }
}
