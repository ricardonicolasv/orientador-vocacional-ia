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
        $armedForcesResponse = $this->generateArmedForcesResponseIfApplies(
            $conversation,
            $normalizedMessage
        );

        if ($armedForcesResponse) {
            return $armedForcesResponse;
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
            str_contains($message, 'pasos') ||
            str_contains($message, 'paso a paso') ||
            str_contains($message, 'como postular') ||
            str_contains($message, 'completar el fuas') ||
            str_contains($message, 'llenar el fuas')
        ) {
            return "{$studentName}, estos son los pasos generales para postular a gratuidad y beneficios estudiantiles:

1. Revisar las fechas oficiales del proceso FUAS.
2. Completar el FUAS con tus datos personales, académicos y grupo familiar.
3. Esperar el resultado de nivel socioeconómico.
4. Revisar si quedas preseleccionado para gratuidad, becas o créditos.
5. Matricularte en una institución y carrera que cumplan las condiciones del beneficio.
6. Revisar el resultado final de asignación.
7. Si el sistema solicita antecedentes adicionales, realizar la evaluación socioeconómica en la institución.

Documentos o datos que podrías necesitar:
- Datos de tu grupo familiar.
- Ingresos del grupo familiar.
- Información académica.
- Datos de contacto actualizados.

La gratuidad no se activa solo por llenar el FUAS: también depende de la institución, carrera, modalidad y requisitos definidos por Mineduc.

¿Ya tienes pensada una institución y carrera específica para revisar el caso?";
        }

        if (
            str_contains($message, '60%') ||
            str_contains($message, 'menores ingresos') ||
            str_contains($message, 'puedo postular') ||
            str_contains($message, 'postular a la gratuidad') ||
            str_contains($message, 'postular gratuidad') ||
            str_contains($message, 'requisitos gratuidad') ||
            str_contains($message, 'requisitos de gratuidad')
        ) {
            return "{$studentName}, sí: si perteneces al 60% de hogares de menores ingresos, puedes postular a la gratuidad mediante el FUAS.

Pero es importante distinguir dos cosas:
- Puedes postular.
- La asignación final no es automática.

Además del requisito socioeconómico, normalmente debes revisar:
1. Completar el FUAS dentro de las fechas oficiales.
2. Matricularte en una institución adscrita a gratuidad.
3. Matricularte en una carrera de pregrado que cumpla las condiciones del beneficio.
4. No contar con un título profesional previo o licenciatura terminal, salvo excepciones.
5. Revisar que la institución, carrera y modalidad permitan acceder al beneficio.
6. Esperar los resultados oficiales del proceso.

En tu caso, estar dentro del 60% es un buen antecedente para postular, pero falta confirmar la institución y la carrera donde te matricularías.

Para revisar esto con seguridad, busca:
- Beneficios Estudiantiles Mineduc.
- FUAS.
- ChileAtiende.
- Sitio oficial de la institución.

¿Quieres que hagamos una checklist concreta usando tu caso, por ejemplo: Derecho en la UDD?";
        }

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
            return "{$studentName}, una institución puede tener gratuidad solo si está adscrita al beneficio para el proceso correspondiente.

Para verificar una institución específica, revisa:
1. Si está adscrita a gratuidad.
2. Si la carrera que quieres estudiar permite acceder al beneficio.
3. Si la modalidad de estudio cumple las condiciones.
4. Si cumples el requisito socioeconómico.
5. Si completaste el FUAS dentro del plazo.
6. Si la información aparece confirmada en Beneficios Estudiantiles Mineduc o en el sitio oficial de la institución.

No basta con que sea universidad, instituto profesional o CFT. Lo importante es que esté adscrita y que la carrera cumpla las condiciones.

¿Quieres revisar una institución concreta, por ejemplo UDD, AIEP, INACAP, DUOC UC, UNAB o USS?";
        }

        if (
            str_contains($message, 'no tengo dinero') ||
            str_contains($message, 'como puedo pagar') ||
            str_contains($message, 'pagar la carrera') ||
            str_contains($message, 'financiar') ||
            str_contains($message, 'financiamiento')
        ) {
            return "{$studentName}, si te preocupa cómo pagar una carrera, lo primero es ordenar las alternativas de financiamiento.

Opciones que deberías revisar:
1. Gratuidad.
2. Becas de arancel.
3. Créditos estudiantiles.
4. Becas internas de la institución.
5. Apoyos complementarios, si existen.
6. Arancel, matrícula y duración real de la carrera.

Ruta recomendada:
- Completa el FUAS.
- Revisa si cumples requisitos socioeconómicos.
- Confirma si la institución está adscrita a beneficios.
- Compara el costo total de la carrera.
- Consulta directamente en admisión o bienestar estudiantil de la institución.

Si me dices la carrera y la institución que estás mirando, puedo ayudarte a ordenar qué revisar.";
        }

        if (
            str_contains($message, 'derecho') ||
            str_contains($message, 'psicologia') ||
            str_contains($message, 'trabajo social') ||
            str_contains($message, 'pedagogia') ||
            str_contains($message, 'ingenieria') ||
            str_contains($message, 'carrera')
        ) {
            return "{$studentName}, sí pueden existir beneficios para estudiar una carrera como Derecho, pero normalmente no dependen solo de la carrera, sino de tu situación socioeconómica, la institución y los requisitos del proceso.

Para Derecho deberías revisar:
1. Si la universidad está adscrita a gratuidad.
2. Si Derecho en esa universidad cumple las condiciones del beneficio.
3. Si cumples el requisito socioeconómico.
4. Si debes cumplir requisitos académicos para becas específicas.
5. Arancel y matrícula.
6. Duración de la carrera.
7. Fechas del FUAS.

El FUAS permite postular a gratuidad, becas y créditos. Si estás dentro del 60% de menores ingresos, podrías postular a gratuidad, pero debes confirmar los demás requisitos.

¿Quieres que revisemos el caso como ejemplo: Derecho en una universidad específica?";
        }

        return "{$studentName}, para financiar estudios superiores en Chile existen beneficios como gratuidad, becas y créditos.

Lo principal:
- El FUAS es el Formulario Único de Acreditación Socioeconómica.
- Sirve para postular a gratuidad, becas y créditos.
- La gratuidad no es automática.
- Debes cumplir requisitos socioeconómicos.
- También importa la institución, carrera, modalidad y condiciones definidas por Mineduc.

Si quieres una respuesta más concreta, dime:
1. Qué carrera quieres estudiar.
2. En qué institución.
3. Si ya sabes si perteneces al 60% de menores ingresos.
4. Si quieres universidad, instituto profesional o CFT.

Con esos datos podemos revisar una checklist más clara.";
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
    private function generateArmedForcesResponseIfApplies(Conversation $conversation, string $message): ?string
    {
        $studentName = $conversation->student->name ?? 'estudiante';

        if (!$this->isArmedForcesContext($conversation, $message)) {
            return null;
        }

        if ($this->containsAny($message, [
            'fuerza aerea',
            'fach',
            'escuela de aviacion',
            'escuela de especialidades',
            'rama de fuerza aerea',
        ])) {
            return $this->safeAirForceResponse($studentName);
        }

        if ($this->containsAny($message, [
            'ejercito',
            'escuela militar',
            'militar',
        ])) {
            return $this->safeArmyResponse($studentName);
        }

        if ($this->containsAny($message, [
            'armada',
            'escuela naval',
            'naval',
            'infanteria de marina',
        ])) {
            return $this->safeNavyResponse($studentName);
        }

        if ($this->containsAny($message, [
            'carabineros',
            'escuela de carabineros',
            'orden publico',
        ])) {
            return $this->safeCarabinerosResponse($studentName);
        }

        if ($this->containsAny($message, [
            'pdi',
            'policia de investigaciones',
            'investigacion',
            'detective',
        ])) {
            return $this->safePdiResponse($studentName);
        }

        if ($this->containsAny($message, [
            'gendarmeria',
            'gendarme',
            'penitenciario',
            'carcel',
        ])) {
            return $this->safeGendarmeriaResponse($studentName);
        }

        if ($this->containsAny($message, [
            'pruebas psicologicas',
            'prueba psicologica',
            'psicologico',
            'psicologica',
            'entrevista psicologica',
        ])) {
            return $this->safePsychologicalTestsResponse($studentName);
        }

        if ($this->containsAny($message, [
            'pruebas fisicas',
            'prueba fisica',
            'preparacion fisica',
            'plan fisico',
            'entrenamiento',
            'documentacion',
            'documentos',
            'papeles',
        ])) {
            return $this->safePhysicalAndDocumentsPlanResponse($studentName);
        }

        if ($this->containsAny($message, [
            'no se cual',
            'no se cuál',
            'cual me conviene',
            'cuál me conviene',
            'ayudame a orientarme',
            'orientarme',
            'no tengo claro',
            'no se que rama',
            'no sé qué rama',
        ])) {
            return $this->safeArmedForcesOrientationResponse($studentName);
        }

        if ($this->containsAny($message, [
            'que necesito',
            'qué necesito',
            'requisitos',
            'entrar',
            'postular',
            'admision',
            'admisión',
            'fechas',
            'convocatoria',
            'postulaciones',
        ])) {
            return $this->safeGeneralArmedForcesRequirementsResponse($studentName);
        }

        return null;
    }

    private function isArmedForcesContext(Conversation $conversation, string $message): bool
    {
        $route = $this->normalize((string) $conversation->selected_route);

        $routeIsArmedForces = $this->containsAny($route, [
            'ffaa',
            'ff.aa',
            'fuerzas armadas',
            'orden',
            'seguridad publica',
            'seguridad pública',
            'carabineros',
            'pdi',
            'gendarmeria',
        ]);

        $messageMentionsArmedForces = $this->containsAny($message, [
            'ffaa',
            'ff.aa',
            'fuerzas armadas',
            'ejercito',
            'armada',
            'fuerza aerea',
            'fach',
            'carabineros',
            'pdi',
            'gendarmeria',
            'escuela militar',
            'escuela naval',
            'escuela de aviacion',
            'escuela de especialidades',
            'orden y seguridad',
            'seguridad publica',
        ]);

        if ($routeIsArmedForces || $messageMentionsArmedForces) {
            return true;
        }

        $conversation->loadMissing('messages');

        $previousStudentText = $conversation->messages
            ->where('sender', 'student')
            ->pluck('content')
            ->map(fn($content) => $this->normalize($content))
            ->implode(' ');

        return $this->containsAny($previousStudentText, [
            'ffaa',
            'fuerzas armadas',
            'ejercito',
            'armada',
            'fuerza aerea',
            'fach',
            'carabineros',
            'pdi',
            'gendarmeria',
        ]);
    }

    private function safeGeneralArmedForcesRequirementsResponse(string $studentName): string
    {
        return "{$studentName}, para entrar a Fuerzas Armadas, de Orden o Seguridad Pública debes revisar los requisitos oficiales de cada institución, porque pueden cambiar cada año.

En general, se suele revisar:

- Edad, nacionalidad y escolaridad exigida.
- Salud compatible con la formación.
- Condición física.
- Evaluación psicológica y entrevista.
- Antecedentes personales.
- Documentación y fechas de postulación.

No conviene asumir requisitos exactos de edad, estatura, pruebas físicas o fechas sin revisar la convocatoria oficial.

Para orientarte mejor: ¿te interesa Ejército, Armada, Fuerza Aérea, Carabineros, PDI o Gendarmería?";
    }

    private function safeAirForceResponse(string $studentName): string
    {
        return "{$studentName}, si te interesa la Fuerza Aérea de Chile, puedes explorar principalmente dos líneas:

- Escuela de Aviación: orientada a la formación de oficiales.
- Escuela de Especialidades: orientada a áreas técnicas y de apoyo especializado.

Podría calzar contigo si te atraen la disciplina, la tecnología, la mecánica, la aviación, el trabajo técnico o la vida institucional.

Debes revisar en fuentes oficiales:
- Requisitos de edad y escolaridad.
- Etapas de postulación.
- Pruebas físicas, médicas y psicológicas.
- Fechas vigentes.
- Especialidades disponibles.

No inventaría requisitos exactos sin revisar la convocatoria oficial de la FACh.

Para seguir: ¿te llama más la atención pilotaje/oficialidad o un área técnica como mantenimiento, mecánica, electricidad, electrónica o apoyo operativo?";
    }

    private function safeArmyResponse(string $studentName): string
    {
        return "{$studentName}, el Ejército puede ser una opción si te interesa el trabajo en terreno, la disciplina, la actividad física, la logística, la mecánica o funciones operativas.

Puedes revisar líneas como:
- Formación de oficiales.
- Formación de suboficiales.
- Especialidades técnicas, logísticas o de mantenimiento.

Antes de decidir, revisa en el sitio oficial:
- Requisitos de ingreso.
- Etapas de postulación.
- Pruebas físicas, médicas y psicológicas.
- Fechas vigentes.
- Especialidades disponibles.

Para orientarte mejor: ¿te interesa más una función operativa en terreno o una especialidad técnica como mecánica, telecomunicaciones o logística?";
    }

    private function safeNavyResponse(string $studentName): string
    {
        return "{$studentName}, la Armada puede ser una opción si te interesa la vida marítima, navegación, operaciones navales, rescate, mecánica naval o trabajo técnico en buques y bases.

Pero si te inquieta el mar o no sabes nadar bien, eso es importante considerarlo y revisarlo con calma.

Antes de decidir, revisa oficialmente:
- Requisitos de ingreso.
- Exigencias físicas y de salud.
- Si la formación exige natación o vida a bordo.
- Fechas y etapas de postulación.
- Escuela Naval o escuelas de especialidades.

Para seguir: ¿te atrae la Armada por la acción y la mecánica, o te preocupa demasiado el tema del mar?";
    }

    private function safeCarabinerosResponse(string $studentName): string
    {
        return "{$studentName}, Carabineros puede interesarte si buscas trabajo en terreno, orden público, seguridad ciudadana, contacto con personas y servicio a la comunidad.

Debes revisar oficialmente:
- Requisitos de ingreso.
- Etapas de postulación.
- Pruebas físicas, médicas y psicológicas.
- Antecedentes y documentación.
- Fechas vigentes.

Es una ruta distinta a las Fuerzas Armadas, porque está más orientada a seguridad pública y trabajo directo con la comunidad.

Para seguir: ¿te interesa más la acción en terreno o el servicio comunitario y contacto con personas?";
    }

    private function safePdiResponse(string $studentName): string
    {
        return "{$studentName}, la PDI puede ser una opción si te interesa investigar, analizar información, resolver casos, trabajar con evidencia y participar en procesos de seguridad pública.

Suele requerir un perfil más investigativo, disciplinado y con buena capacidad de observación.

Debes revisar oficialmente:
- Requisitos de ingreso.
- Fechas de postulación.
- Evaluaciones psicológicas, médicas y físicas.
- Documentación solicitada.
- Etapas del proceso.

Para seguir: ¿te atrae más investigar y analizar casos, o prefieres una labor más física y operativa?";
    }

    private function safeGendarmeriaResponse(string $studentName): string
    {
        return "{$studentName}, Gendarmería puede interesarte si buscas una ruta de seguridad pública vinculada al sistema penitenciario, custodia, reinserción y trabajo institucional.

Debes revisar oficialmente:
- Requisitos de ingreso.
- Escuela de formación correspondiente.
- Evaluaciones físicas, médicas y psicológicas.
- Fechas de postulación.
- Documentación solicitada.

Es una ruta que exige disciplina, estabilidad emocional y disposición para trabajar en contextos complejos.

Para seguir: ¿te interesa esta área por seguridad institucional, servicio público o estabilidad laboral?";
    }

    private function safePsychologicalTestsResponse(string $studentName): string
    {
        return "{$studentName}, en procesos de Fuerzas Armadas, de Orden o Seguridad Pública, las evaluaciones psicológicas suelen buscar si el postulante tiene condiciones compatibles con la vida institucional.

En general pueden revisar:
- Motivación para postular.
- Manejo del estrés.
- Responsabilidad y disciplina.
- Trabajo en equipo.
- Control emocional.
- Toma de decisiones.
- Coherencia entre entrevista y cuestionarios.

No conviene intentar “preparar respuestas perfectas”. Lo mejor es ser honesto, dormir bien, llegar puntual y explicar con claridad por qué quieres postular.

¿Quieres que practiquemos una mini entrevista vocacional para ver si esta ruta calza contigo?";
    }

    private function safePhysicalAndDocumentsPlanResponse(string $studentName): string
    {
        return "{$studentName}, puedes prepararte en dos líneas: condición física y documentación. Te dejo una versión resumida.

Preparación física inicial:
- 3 días por semana: trote suave o caminata rápida de 20 a 30 minutos.
- 2 días: fuerza básica con sentadillas, flexiones, abdominales y plancha.
- 1 día: movilidad, elongación y recuperación.
- Registra tiempos, repeticiones y avances.

Documentación a ordenar:
- Cédula vigente.
- Certificados de estudio.
- Certificados solicitados en la convocatoria.
- Antecedentes personales, si la institución los pide.
- Fechas y bases oficiales del proceso.

Antes de entrenar fuerte, revisa tu estado de salud y los requisitos oficiales de la institución que te interesa.

¿Quieres que armemos un plan semanal de 4 semanas?";
    }

    private function safeArmedForcesOrientationResponse(string $studentName): string
    {
        return "{$studentName}, para orientarte entre Fuerzas Armadas, de Orden y Seguridad Pública, conviene cruzar tres cosas: lo que te gusta, lo que se te da bien y lo que te preocupa.

Resumen rápido:
- Ejército: acción, terreno, logística, mecánica y disciplina.
- Armada: mar, navegación, rescate, buques y mecánica naval.
- Fuerza Aérea: aviación, tecnología, mecánica, precisión y trabajo técnico.
- Carabineros: seguridad ciudadana, comunidad y orden público.
- PDI: investigación, análisis y trabajo policial especializado.
- Gendarmería: seguridad penitenciaria, control institucional y reinserción.

Para avanzar, dime:
- ¿Te gusta más la acción, la tecnología, investigar, ayudar a la comunidad o el trabajo técnico?
- ¿Hay algo que te preocupe, como altura, mar, disciplina, pruebas físicas o entrevistas?";
    }

    private function containsAny(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($text, $this->normalize($keyword))) {
                return true;
            }
        }

        return false;
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
