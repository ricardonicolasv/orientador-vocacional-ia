<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detalle del estudiante
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Revisión de datos, conversaciones y reportes vocacionales.
                </p>
            </div>

            <a href="{{ route('orientador.dashboard') }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Volver al dashboard
            </a>
        </div>
    </x-slot>

    @php
    $routeLabels = [
    'universidad' => 'Ruta universitaria',
    'tecnico-profesional' => 'Ruta técnico-profesional',
    'beneficios-fuas' => 'Beneficios / FUAS',
    'pedagogia' => 'Pedagogía',
    'ffaa-orden' => 'FF.AA., Orden y Seguridad',
    'no-se-aun' => 'Exploración general',
    ];

    $clarityClasses = [
    'bajo' => 'bg-red-100 text-red-700',
    'medio' => 'bg-yellow-100 text-yellow-700',
    'alto' => 'bg-green-100 text-green-700',
    ];

    $lastConversation = $student->conversations->sortByDesc('created_at')->first();
    $lastReport = $student->reports->sortByDesc('created_at')->first();
    @endphp

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
            <div class="rounded-xl bg-green-50 border border-green-200 p-4 text-green-700">
                {{ session('success') }}
            </div>
            @endif

            {{-- Resumen superior --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm sm:rounded-2xl p-6 border border-gray-100">
                    <p class="text-sm text-gray-500">Conversaciones</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">
                        {{ $student->conversations->count() }}
                    </p>
                </div>

                <div class="bg-white shadow-sm sm:rounded-2xl p-6 border border-gray-100">
                    <p class="text-sm text-gray-500">Reportes</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">
                        {{ $student->reports->count() }}
                    </p>
                </div>

                <div class="bg-white shadow-sm sm:rounded-2xl p-6 border border-gray-100">
                    <p class="text-sm text-gray-500">Última ruta</p>
                    <p class="text-base font-semibold text-gray-900 mt-2">
                        @if($lastConversation)
                        {{ $routeLabels[$lastConversation->selected_route] ?? $lastConversation->selected_route }}
                        @else
                        Sin conversación
                        @endif
                    </p>
                </div>

                <div class="bg-white shadow-sm sm:rounded-2xl p-6 border border-gray-100">
                    <p class="text-sm text-gray-500">Último nivel</p>
                    <div class="mt-3">
                        @if($lastReport)
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $clarityClasses[$lastReport->clarity_level] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst($lastReport->clarity_level) }}
                        </span>
                        @else
                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                            Sin reporte
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Datos del estudiante --}}
            <div class="bg-white shadow-sm sm:rounded-2xl p-6 border border-gray-100">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">
                            Datos del estudiante
                        </h3>

                        <div class="grid md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">Nombre</p>
                                <p class="font-semibold text-gray-900">{{ $student->name }}</p>
                            </div>

                            <div>
                                <p class="text-gray-500">Curso</p>
                                <p class="font-semibold text-gray-900">{{ $student->course }}</p>
                            </div>

                            <div>
                                <p class="text-gray-500">Colegio</p>
                                <p class="font-semibold text-gray-900">{{ $student->school }}</p>
                            </div>

                            <div>
                                <p class="text-gray-500">Fecha de registro</p>
                                <p class="font-semibold text-gray-900">
                                    {{ $student->created_at->format('d-m-Y H:i') }}
                                </p>
                            </div>

                            <div>
                                <p class="text-gray-500">Consentimiento</p>
                                @if($student->consent_accepted)
                                <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                    Aceptado
                                </span>
                                @else
                                <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                    No aceptado
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($lastReport)
                    <div class="flex flex-col sm:flex-row md:flex-col gap-2">
                        <a href="{{ route('orientador.reports.show', $lastReport) }}"
                            class="inline-flex justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                            Ver último reporte
                        </a>

                        <a href="{{ route('orientador.reports.pdf', $lastReport) }}"
                            class="inline-flex justify-center rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">
                            Descargar PDF
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Conversaciones --}}
            <div class="space-y-6">
                @forelse($student->conversations->sortByDesc('created_at') as $conversation)
                @php
                $routeLabel = $routeLabels[$conversation->selected_route] ?? $conversation->selected_route;
                $report = $conversation->report;
                @endphp

                <div class="bg-white shadow-sm sm:rounded-2xl border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">
                                    Conversación #{{ $conversation->id }}
                                </h3>

                                <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 font-semibold text-green-700">
                                        {{ $routeLabel }}
                                    </span>

                                    <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-700">
                                        {{ $conversation->messages->count() }} mensajes
                                    </span>

                                    <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 font-semibold text-blue-700">
                                        Inicio: {{ optional($conversation->started_at)->format('d-m-Y H:i') ?? 'No registrado' }}
                                    </span>

                                    @if($report)
                                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 font-semibold text-emerald-700">
                                        Reporte generado
                                    </span>
                                    @else
                                    <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 font-semibold text-yellow-700">
                                        Reporte pendiente
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-2">
                                @if($report)
                                <a href="{{ route('orientador.reports.show', $report) }}"
                                    class="inline-flex justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                    Ver reporte
                                </a>

                                <a href="{{ route('orientador.reports.pdf', $report) }}"
                                    class="inline-flex justify-center rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">
                                    Descargar PDF
                                </a>
                                @else
                                <form action="{{ route('orientador.reports.generate', $conversation) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                                        Generar reporte
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-gray-50">
                        <div class="space-y-4">
                            @foreach($conversation->messages as $message)
                            @if($message->sender === 'student')
                            <div class="flex justify-end">
                                <div class="max-w-[85%] rounded-2xl rounded-br-sm bg-green-700 px-4 py-3 text-white text-sm shadow-sm">
                                    <p class="text-xs text-green-100 mb-1">
                                        Estudiante · {{ $message->created_at->format('H:i') }}
                                    </p>
                                    {!! nl2br(e($message->content)) !!}
                                </div>
                            </div>
                            @else
                            <div class="flex justify-start">
                                <div class="max-w-[85%] rounded-2xl rounded-bl-sm bg-white border border-gray-200 px-4 py-3 text-gray-700 text-sm shadow-sm">
                                    <p class="text-xs text-gray-400 mb-1">
                                        Asistente IA · {{ $message->created_at->format('H:i') }}
                                    </p>
                                    {!! nl2br(e($message->content)) !!}
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white shadow-sm sm:rounded-2xl p-6 text-gray-500 border border-gray-100">
                    Este estudiante no tiene conversaciones registradas.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>