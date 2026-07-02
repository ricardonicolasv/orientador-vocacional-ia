<?php

namespace App\Services;

use App\Models\Conversation;

class SafeVocationalResponseService
{
    public function generateIfApplies(Conversation $conversation, string $studentMessage): ?string
    {
        $normalizedMessage = $this->normalize($studentMessage);

        if ($this->isAdmissionRequirementsQuestion($normalizedMessage)) {
            return $this->safeAdmissionRequirementsResponse($conversation);
        }

        if ($this->isStudentBenefitsQuestion($normalizedMessage)) {
            return $this->safeStudentBenefitsResponse($conversation, $studentMessage);
        }

        if ($this->isSpecificInstitutionQuestion($normalizedMessage)) {
            return $this->safeInstitutionResponse($conversation, $normalizedMessage);
        }

        if ($this->isCareerComparisonQuestion($normalizedMessage)) {
            return $this->safeCareerComparisonResponse($conversation);
        }

        return null;
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

    private function safeAdmissionRequirementsResponse(Conversation $conversation): string
    {
        $studentName = $conversation->student->name ?? 'estudiante';

        return "Buena pregunta, {$studentName}. Para saber qué necesitas para entrar a esa carrera, lo correcto es revisar información oficial y actualizada.

En Chile, la admisión universitaria suele considerar:
- PAES.
- NEM.
- Ranking.
- Ponderaciones definidas por cada carrera e institución.
- Vacantes.
- Puntaje de corte referencial, cuando esté disponible.
- Requisitos propios publicados oficialmente por la institución, si existen.

No conviene asumir promedios mínimos, puntajes o requisitos especiales sin revisar la ficha oficial, porque pueden cambiar según el año, la carrera y la institución.

Te recomiendo revisar:
- Sitio oficial de admisión de la institución.
- DEMRE.
- Acceso Educación Superior Mineduc.
- Mi Futuro Mineduc.

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

¿Quieres que te ayude a armar una checklist para revisar esa carrera?";
    }

    private function isStudentBenefitsQuestion(string $message): bool
    {
        return str_contains($message, 'fuas') ||
            str_contains($message, 'beca') ||
            str_contains($message, 'becas') ||
            str_contains($message, 'gratuidad') ||
            str_contains($message, 'credito') ||
            str_contains($message, 'creditos') ||
            str_contains($message, 'cae') ||
            str_contains($message, 'beneficio') ||
            str_contains($message, 'beneficios') ||
            str_contains($message, 'financiamiento') ||
            str_contains($message, 'arancel') ||
            str_contains($message, 'matricula') ||
            str_contains($message, 'como pagar') ||
            str_contains($message, 'ayuda economica') ||
            str_contains($message, 'ayuden') ||
            str_contains($message, 'ayuda para estudiar');
    }

    private function safeStudentBenefitsResponse(Conversation $conversation, string $studentMessage): string
    {
        $studentName = $conversation->student->name ?? 'estudiante';
        $message = $this->normalize($studentMessage);

        if (
            str_contains($message, 'aiep') ||
            str_contains($message, 'duoc') ||
            str_contains($message, 'inacap') ||
            str_contains($message, 'santo tomas') ||
            str_contains($message, 'instituto') ||
            str_contains($message, 'universidad') ||
            str_contains($message, 'cft') ||
            str_contains($message, 'udd') ||
            str_contains($message, 'unab') ||
            str_contains($message, 'uss')
        ) {
            return "{$studentName}, para saber si una institución tiene gratuidad o permite acceder a beneficios, hay que revisar información oficial y actualizada.

No conviene asumir que una institución tiene gratuidad solo por ser universidad, instituto profesional o CFT.

Para verificarlo, revisa:
- Si la institución está adscrita a gratuidad.
- Si la carrera permite acceder a beneficios.
- Si cumples los requisitos socioeconómicos definidos por Mineduc.
- Si completaste el FUAS dentro de las fechas oficiales.
- El sitio oficial de la institución.
- Beneficios Estudiantiles Mineduc.
- FUAS.
- ChileAtiende.

¿Quieres que armemos una checklist para revisar si una institución permite gratuidad y beneficios?";
        }

        if (
            str_contains($message, 'no tengo dinero') ||
            str_contains($message, 'como puedo pagar') ||
            str_contains($message, 'pagar la carrera') ||
            str_contains($message, 'financiar') ||
            str_contains($message, 'financiamiento')
        ) {
            return "{$studentName}, si te preocupa cómo pagar una carrera, lo principal es revisar opciones de financiamiento antes de decidir.

Pasos recomendados:
1. Completar el FUAS dentro de las fechas oficiales.
2. Revisar si podrías acceder a gratuidad.
3. Revisar becas de arancel.
4. Revisar créditos estudiantiles, si corresponde.
5. Comparar arancel y matrícula de la carrera.
6. Confirmar si la institución está adscrita a beneficios.
7. Revisar información oficial en Beneficios Estudiantiles Mineduc, FUAS y ChileAtiende.

La gratuidad no es automática para todos. Depende de requisitos socioeconómicos, institución, carrera y condiciones definidas por Mineduc.

¿Quieres que revisemos una ruta paso a paso para financiar estudios superiores?";
        }

        if (
            str_contains($message, 'derecho') ||
            str_contains($message, 'psicologia') ||
            str_contains($message, 'trabajo social') ||
            str_contains($message, 'pedagogia') ||
            str_contains($message, 'ingenieria') ||
            str_contains($message, 'carrera')
        ) {
            return "{$studentName}, sí existen beneficios que pueden ayudar a financiar carreras de educación superior, pero no conviene asumir que aplican automáticamente a una carrera específica.

Para una carrera como Derecho u otra carrera profesional, deberías revisar:
- Si la institución está adscrita a gratuidad.
- Si la carrera permite acceder a beneficios.
- Requisitos socioeconómicos.
- Fechas del FUAS.
- Becas de arancel disponibles.
- Créditos estudiantiles.
- Arancel y matrícula.
- Duración de la carrera.

El FUAS es el Formulario Único de Acreditación Socioeconómica y sirve para postular a beneficios como gratuidad, becas y créditos.

Te recomiendo revisar fuentes oficiales: Beneficios Estudiantiles Mineduc, FUAS, ChileAtiende y el sitio de la institución donde quieras estudiar.

¿Quieres comparar gratuidad, becas y créditos?";
        }

        return "{$studentName}, para financiar estudios superiores en Chile existen beneficios como gratuidad, becas y créditos, pero no conviene asumir que aplican automáticamente.

El FUAS es el Formulario Único de Acreditación Socioeconómica. Sirve para postular a beneficios estudiantiles como:
- Gratuidad.
- Becas de arancel.
- Créditos estudiantiles.
- Otros apoyos definidos por Mineduc según el proceso vigente.

Puntos importantes:
- La gratuidad no es automática para todos.
- Depende de requisitos socioeconómicos.
- También depende de la institución, la carrera, el nivel de estudios y las condiciones definidas por Mineduc.
- Las fechas, requisitos y resultados pueden cambiar cada año.

Para revisar información oficial, usa:
- Beneficios Estudiantiles Mineduc.
- FUAS.
- ChileAtiende.
- Sitio oficial de la institución donde quieras estudiar.

¿Quieres que revisemos los pasos generales para completar el FUAS o prefieres comparar gratuidad, becas y créditos?";
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
            'udd',
            'unab',
            'uss',
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
            str_contains($message, 'universidades') ||
            str_contains($message, 'universidad privada') ||
            str_contains($message, 'universidades privadas') ||
            str_contains($message, 'alguna carrera') ||
            str_contains($message, 'carrera similar') ||
            str_contains($message, 'carreras similares') ||
            str_contains($message, 'cuanto dura');

        foreach ($institutions as $institution) {
            if (str_contains($message, $institution) && $questionIntent) {
                return true;
            }
        }

        return false;
    }

    private function safeInstitutionResponse(Conversation $conversation, string $message): string
    {
        $studentName = $conversation->student->name ?? 'estudiante';
        $institutionName = $this->detectInstitutionName($message);

        return "Buena pregunta, {$studentName}.

Sobre {$institutionName}, lo correcto es revisar información oficial directamente en el sitio de la institución, porque carreras, sedes, modalidades, duración, mallas, aranceles y beneficios pueden cambiar.

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
10. Beneficios disponibles, como gratuidad, becas o créditos si corresponde.

No conviene asumir que una institución imparte una carrera específica, tiene gratuidad o mantiene la misma duración de carrera sin verificarlo en su sitio oficial.

¿Quieres que armemos una checklist para comparar instituciones?";
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
            str_contains($message, 'udd') => 'la Universidad del Desarrollo',
            str_contains($message, 'unab') => 'la Universidad Andrés Bello',
            str_contains($message, 'uss') => 'la Universidad San Sebastián',
            str_contains($message, 'instituto profesional') => 'el instituto profesional',
            str_contains($message, 'cft') => 'el centro de formación técnica',
            default => 'esa institución',
        };
    }

    private function isCareerComparisonQuestion(string $message): bool
    {
        return str_contains($message, 'comparar carreras') ||
            str_contains($message, 'carreras relacionadas') ||
            str_contains($message, 'comparar opciones') ||
            str_contains($message, 'comparar alternativas');
    }

    private function safeCareerComparisonResponse(Conversation $conversation): string
    {
        $studentName = $conversation->student->name ?? 'estudiante';

        $conversation->load('messages');

        $studentText = $conversation->messages
            ->where('sender', 'student')
            ->pluck('content')
            ->implode(' ');

        $normalizedText = $this->normalize($studentText);

        if (
            str_contains($normalizedText, 'psicologia') ||
            str_contains($normalizedText, 'trabajo social') ||
            str_contains($normalizedText, 'educacion especial') ||
            str_contains($normalizedText, 'educacion diferencial') ||
            str_contains($normalizedText, 'ninos') ||
            str_contains($normalizedText, 'ayudar a las personas')
        ) {
            return "Perfecto, {$studentName}. Según lo que ya mencionaste, conviene comparar carreras relacionadas con apoyo a personas, niños, familia, educación y área social.

Podríamos comparar estas opciones:
1. Psicología.
2. Trabajo Social o Servicio Social.
3. Educación Diferencial o Educación Especial.
4. Psicopedagogía.
5. Terapia Ocupacional.

Para compararlas bien, revisa:
- Duración.
- Malla curricular.
- Campo laboral.
- Tipo de institución.
- Requisitos de admisión.
- Acreditación.
- Arancel.
- Prácticas profesionales.
- Cuánto trabajo directo tienen con niños o familias.

Como mencionaste que matemática y física te cuestan, también conviene revisar cuánto peso tienen esas áreas en cada malla.

¿Quieres que comparemos Psicología, Trabajo Social y Educación Diferencial primero?";
        }

        if (
            str_contains($normalizedText, 'derecho') ||
            str_contains($normalizedText, 'ciencias politicas') ||
            str_contains($normalizedText, 'administracion publica') ||
            str_contains($normalizedText, 'cargo publico') ||
            str_contains($normalizedText, 'politica')
        ) {
            return "Perfecto, {$studentName}. Según lo que ya mencionaste, conviene comparar carreras relacionadas con Derecho, Ciencias Políticas, Administración Pública y gestión del Estado.

Podríamos comparar estas opciones:
1. Derecho.
2. Ciencias Políticas.
3. Administración Pública.
4. Gestión Pública.
5. Sociología.
6. Relaciones Internacionales, si te interesa el área internacional.

Para compararlas bien, revisa:
- Duración.
- Malla curricular.
- Campo laboral.
- Requisitos de admisión.
- Ponderaciones PAES, NEM y Ranking.
- Acreditación.
- Arancel.
- Posibles cargos públicos o áreas de desempeño.

Si te interesa un cargo público relacionado con política, Derecho y Ciencias Políticas pueden complementarse, pero conviene revisar mallas y hablar con el orientador del colegio.

¿Quieres que comparemos Derecho, Ciencias Políticas y Administración Pública?";
        }

        return "Perfecto, {$studentName}. Para comparar carreras de forma ordenada, primero necesitamos cruzar tus intereses con el tipo de formación que prefieres.

Podemos comparar usando estos criterios:
- Área de interés.
- Tipo de institución.
- Duración.
- Forma de trabajo.
- Malla curricular.
- Campo laboral.
- Acreditación.
- Arancel.
- Requisitos de admisión.
- Posibilidad de continuidad de estudios.

Para avanzar, dime 2 o 3 áreas que te interesen más.";
    }
}
