<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Reporte vocacional
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Informe generado a partir de la conversación vocacional del estudiante.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-2">
                <a href="{{ route('orientador.students.show', $report->student) }}"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Volver al estudiante
                </a>

                <a href="{{ route('orientador.dashboard') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Volver al dashboard
                </a>

                <a href="{{ route('orientador.reports.pdf', $report) }}"
                    class="inline-flex items-center justify-center rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">
                    Descargar PDF
                </a>
            </div>
        </div>
    </x-slot>

    @php
    $clarityClasses = [
    'bajo' => 'bg-red-100 text-red-700 border-red-200',
    'medio' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
    'alto' => 'bg-green-100 text-green-700 border-green-200',
    ];

    $clarityLabels = [
    'bajo' => 'Bajo',
    'medio' => 'Medio',
    'alto' => 'Alto',
    ];

    $clarityClass = $clarityClasses[$report->clarity_level] ?? 'bg-gray-100 text-gray-700 border-gray-200';
    $clarityLabel = $clarityLabels[$report->clarity_level] ?? ucfirst($report->clarity_level);

    $student = $report->student;
    $conversation = $report->conversation;
    @endphp

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Resumen superior --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white border border-gray-100 shadow-sm rounded-2xl p-5">
                    <p class="text-sm text-gray-500">Estudiante</p>
                    <p class="mt-2 text-lg font-bold text-gray-900">
                        {{ $student->name }}
                    </p>
                </div>

                <div class="bg-white border border-gray-100 shadow-sm rounded-2xl p-5">
                    <p class="text-sm text-gray-500">Curso</p>
                    <p class="mt-2 text-lg font-bold text-gray-900">
                        {{ $student->course }}
                    </p>
                </div>

                <div class="bg-white border border-gray-100 shadow-sm rounded-2xl p-5">
                    <p class="text-sm text-gray-500">Ruta explorada</p>
                    <p class="mt-2 text-base font-semibold text-gray-900">
                        {{ $report->explored_routes }}
                    </p>
                </div>

                <div class="bg-white border border-gray-100 shadow-sm rounded-2xl p-5">
                    <p class="text-sm text-gray-500">Claridad vocacional</p>
                    <div class="mt-3">
                        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-bold {{ $clarityClass }}">
                            {{ $clarityLabel }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Datos generales --}}
            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl p-6">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">
                            Datos generales
                        </h3>

                        <p class="text-sm text-gray-500 mt-1">
                            Información base del estudiante y del reporte generado.
                        </p>
                    </div>

                    <div class="text-sm text-gray-600 md:text-right">
                        <p>
                            <span class="font-semibold text-gray-800">Colegio:</span>
                            {{ $student->school }}
                        </p>

                        <p class="mt-1">
                            <span class="font-semibold text-gray-800">Fecha de reporte:</span>
                            {{ $report->created_at->format('d-m-Y H:i') }}
                        </p>

                        @if($conversation)
                        <p class="mt-1">
                            <span class="font-semibold text-gray-800">Conversación:</span>
                            #{{ $conversation->id }}
                        </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Contenido principal --}}
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                {{-- Columna principal --}}
                <div class="xl:col-span-2 space-y-6">

                    <section class="bg-white border border-gray-100 shadow-sm rounded-2xl p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-bold text-gray-900">
                                📌 Intereses mencionados
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Antecedentes entregados por el estudiante durante la conversación.
                            </p>
                        </div>

                        <div class="rounded-xl bg-gray-50 border border-gray-100 p-5 text-base leading-relaxed text-gray-700 whitespace-pre-line">
                            {{ $report->interests }}
                        </div>
                    </section>

                    <section class="bg-white border border-gray-100 shadow-sm rounded-2xl p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-bold text-gray-900">
                                ✅ Recomendaciones sugeridas
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Acciones recomendadas para continuar el proceso de orientación.
                            </p>
                        </div>

                        <div class="rounded-xl bg-green-50 border border-green-100 p-5 text-base leading-relaxed text-gray-700 whitespace-pre-line">
                            {{ $report->recommendations }}
                        </div>
                    </section>

                    <section class="bg-white border border-gray-100 shadow-sm rounded-2xl p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-bold text-gray-900">
                                👨‍🎓 Resumen para el estudiante
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Síntesis orientativa pensada para ser compartida con el estudiante.
                            </p>
                        </div>

                        <div class="rounded-xl bg-blue-50 border border-blue-100 p-5 text-base leading-relaxed text-gray-700 whitespace-pre-line">
                            {{ $report->student_summary }}
                        </div>
                    </section>
                </div>

                {{-- Columna lateral --}}
                <div class="space-y-6">

                    <section class="bg-white border border-gray-100 shadow-sm rounded-2xl p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-bold text-gray-900">
                                🎯 Áreas detectadas
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Áreas vocacionales identificadas.
                            </p>
                        </div>

                        <div class="rounded-xl bg-gray-50 border border-gray-100 p-4 text-sm leading-relaxed text-gray-700 whitespace-pre-line">
                            {{ $report->detected_areas }}
                        </div>
                    </section>

                    <section class="bg-white border border-gray-100 shadow-sm rounded-2xl p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-bold text-gray-900">
                                ❓ Dudas principales
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Temas que requieren seguimiento.
                            </p>
                        </div>

                        <div class="rounded-xl bg-yellow-50 border border-yellow-100 p-4 text-sm leading-relaxed text-gray-700 whitespace-pre-line">
                            {{ $report->main_questions }}
                        </div>
                    </section>

                    <section class="bg-white border border-green-200 shadow-sm rounded-2xl p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-bold text-green-800">
                                🧑‍🏫 Sección técnica para el orientador
                            </h3>
                            <p class="text-sm text-green-700 mt-1">
                                Observaciones para seguimiento individual.
                            </p>
                        </div>

                        <div class="rounded-xl bg-green-50 border border-green-100 p-4 text-sm leading-relaxed text-gray-700 whitespace-pre-line">
                            {{ $report->orientador_notes }}
                        </div>
                    </section>
                </div>
            </div>

            {{-- Acciones inferiores --}}
            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">
                            Acciones del reporte
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Puedes volver al estudiante, revisar el dashboard o descargar el informe en PDF.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2">
                        <a href="{{ route('orientador.students.show', $student) }}"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Volver al estudiante
                        </a>

                        <a href="{{ route('orientador.reports.pdf', $report) }}"
                            class="inline-flex items-center justify-center rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">
                            Descargar PDF
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>