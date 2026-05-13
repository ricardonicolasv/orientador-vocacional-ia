<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Reporte Vocacional Individual
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Resumen generado a partir de la conversación vocacional.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-2">
                <a href="{{ route('orientador.reports.pdf', $report) }}"
                    class="inline-flex items-center justify-center rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">
                    Descargar PDF
                </a>

                <a href="{{ route('orientador.students.show', $report->student) }}"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Volver al estudiante
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
            <div class="rounded-xl bg-green-50 border border-green-200 p-4 text-green-700">
                {{ session('success') }}
            </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-2xl p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    Datos generales
                </h3>

                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Estudiante</p>
                        <p class="font-semibold text-gray-900">{{ $report->student->name }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500">Curso</p>
                        <p class="font-semibold text-gray-900">{{ $report->student->course }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500">Colegio</p>
                        <p class="font-semibold text-gray-900">{{ $report->student->school }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500">Fecha del reporte</p>
                        <p class="font-semibold text-gray-900">{{ $report->created_at->format('d-m-Y H:i') }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500">Ruta explorada</p>
                        <p class="font-semibold text-gray-900">{{ $report->explored_routes }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500">Nivel de claridad vocacional</p>

                        @php
                        $clarityClasses = [
                        'bajo' => 'bg-red-100 text-red-700',
                        'medio' => 'bg-yellow-100 text-yellow-700',
                        'alto' => 'bg-green-100 text-green-700',
                        ];
                        @endphp

                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $clarityClasses[$report->clarity_level] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst($report->clarity_level) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-2xl p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-3">Intereses mencionados</h3>
                <div class="prose max-w-none text-gray-700 whitespace-pre-line">
                    {{ $report->interests }}
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-2xl p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-3">Áreas detectadas</h3>
                <p class="text-gray-700">{{ $report->detected_areas }}</p>
            </div>

            <div class="bg-white shadow-sm sm:rounded-2xl p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-3">Dudas principales</h3>
                <div class="prose max-w-none text-gray-700 whitespace-pre-line">
                    {{ $report->main_questions }}
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-2xl p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-3">Recomendaciones sugeridas</h3>
                <div class="prose max-w-none text-gray-700 whitespace-pre-line">
                    {{ $report->recommendations }}
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-2xl p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-3">Resumen para el estudiante</h3>
                <div class="prose max-w-none text-gray-700 whitespace-pre-line">
                    {{ $report->student_summary }}
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-2xl p-6 border-l-4 border-green-700">
                <h3 class="text-lg font-bold text-gray-900 mb-3">Sección técnica para el orientador</h3>
                <div class="prose max-w-none text-gray-700 whitespace-pre-line">
                    {{ $report->orientador_notes }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>