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

            <div class="flex flex-col sm:flex-row gap-2">
                @if($student->access_code)
                <span class="inline-flex items-center justify-center rounded-xl bg-indigo-100 px-4 py-2 text-sm font-semibold text-indigo-700">
                    Código: {{ $student->access_code }}
                </span>
                @endif

                <a href="{{ route('orientador.dashboard') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    Volver al dashboard
                </a>
            </div>
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

    $clarityLabels = [
    'bajo' => 'Bajo',
    'medio' => 'Medio',
    'alto' => 'Alto',
    ];

    $lastConversation = $student->conversations->sortByDesc('created_at')->first();
    $lastReport = $student->reports->sortByDesc('created_at')->first();

    $activeConversations = $student->conversations->where('status', 'active')->count();
    $finishedConversations = $student->conversations->where('status', 'finished')->count();
    @endphp

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
            <div class="rounded-xl bg-green-50 border border-green-200 p-4 text-green-700">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="rounded-xl bg-red-50 border border-red-200 p-4 text-red-700">
                {{ session('error') }}
            </div>
            @endif

            {{-- Resumen superior --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm rounded-2xl p-6 border border-gray-100">
                    <p class="text-sm text-gray-500">Conversaciones</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">
                        {{ $student->conversations->count() }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        {{ $activeConversations }} activas · {{ $finishedConversations }} finalizadas
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-2xl p-6 border border-gray-100">
                    <p class="text-sm text-gray-500">Reportes</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">
                        {{ $student->reports->count() }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        {{ $lastReport ? 'Último reporte disponible' : 'Sin reporte generado' }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-2xl p-6 border border-gray-100">
                    <p class="text-sm text-gray-500">Última ruta</p>

                    <p class="text-base font-semibold text-gray-900 mt-2">
                        @if($lastConversation)
                        {{ $routeLabels[$lastConversation->selected_route] ?? $lastConversation->selected_route }}
                        @else
                        Sin conversación
                        @endif
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-2xl p-6 border border-gray-100">
                    <p class="text-sm text-gray-500">Último nivel</p>

                    <div class="mt-3">
                        @if($lastReport)
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $clarityClasses[$lastReport->clarity_level] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ $clarityLabels[$lastReport->clarity_level] ?? ucfirst($lastReport->clarity_level) }}
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
            <div class="bg-white shadow-sm rounded-2xl p-6 border border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">
                            Datos del estudiante
                        </h3>

                        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">Nombre</p>
                                <p class="font-semibold text-gray-900">
                                    {{ $student->name }}
                                </p>
                            </div>

                            <div>
                                <p class="text-gray-500">Curso</p>
                                <p class="font-semibold text-gray-900">
                                    {{ $student->course }}
                                </p>
                            </div>

                            <div>
                                <p class="text-gray-500">Colegio</p>
                                <p class="font-semibold text-gray-900">
                                    {{ $student->school }}
                                </p>
                            </div>

                            <div>
                                <p class="text-gray-500">Fecha de registro</p>
                                <p class="font-semibold text-gray-900">
                                    {{ $student->created_at->format('d-m-Y H:i') }}
                                </p>
                            </div>

                            <div>
                                <p class="text-gray-500">Código de acceso</p>

                                @if($student->access_code)
                                <span class="inline-flex rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                                    {{ $student->access_code }}
                                </span>
                                @else
                                <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                                    Sin código
                                </span>
                                @endif
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

                    <div class="flex flex-col sm:flex-row lg:flex-col gap-2">
                        @if($lastConversation)
                        <a href="{{ route('chat.show', $lastConversation) }}"
                            class="inline-flex justify-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                            Ver último chat
                        </a>
                        @endif

                        @if($lastReport)
                        <a href="{{ route('orientador.reports.show', $lastReport) }}"
                            class="inline-flex justify-center rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800">
                            Ver último reporte
                        </a>

                        <a href="{{ route('orientador.reports.pdf', $lastReport) }}"
                            class="inline-flex justify-center rounded-xl bg-green-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800">
                            Descargar PDF
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Conversaciones --}}
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">
                        Conversaciones del estudiante
                    </h3>

                    <p class="text-sm text-gray-500 mt-1">
                        Historial completo de conversaciones, mensajes y reportes asociados.
                    </p>
                </div>

                @forelse($student->conversations->sortByDesc('created_at') as $conversation)
                @php
                $routeLabel = $routeLabels[$conversation->selected_route] ?? $conversation->selected_route;
                $report = $conversation->report;
                $isFinished = $conversation->status === 'finished';
                @endphp

                <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden">
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

                                    @if($isFinished)
                                    <span class="inline-flex rounded-full bg-red-100 px-3 py-1 font-semibold text-red-700">
                                        Finalizada
                                    </span>
                                    @else
                                    <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 font-semibold text-blue-700">
                                        Activa
                                    </span>
                                    @endif

                                    <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 font-semibold text-blue-700">
                                        Inicio: {{ optional($conversation->started_at)->format('d-m-Y H:i') ?? 'No registrado' }}
                                    </span>

                                    @if($conversation->finished_at)
                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-700">
                                        Término: {{ $conversation->finished_at->format('d-m-Y H:i') }}
                                    </span>
                                    @endif

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
                                <!--
                                <a href="{{ route('chat.show', $conversation) }}"
                                    class="inline-flex justify-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                                    Ver chat
                                </a>
                                -->
                                @if($report)
                                <a href="{{ route('orientador.reports.show', $report) }}"
                                    class="inline-flex justify-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                                    Ver reporte
                                </a>

                                <a href="{{ route('orientador.reports.pdf', $report) }}"
                                    class="inline-flex justify-center rounded-xl bg-green-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800">
                                    Descargar PDF
                                </a>
                                @else
                                <form action="{{ route('orientador.reports.generate', $conversation) }}" method="POST">
                                    @csrf

                                    <button type="submit"
                                        class="inline-flex justify-center rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800">
                                        Generar reporte
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-gray-50">
                        @if($conversation->messages->isNotEmpty())
                        <div class="space-y-4">
                            @foreach($conversation->messages as $message)
                            @if($message->sender === 'student')
                            <div class="flex justify-end">
                                <div class="max-w-[85%] rounded-2xl rounded-br-sm bg-green-700 px-4 py-3 text-white text-sm shadow-sm">
                                    <p class="text-xs text-green-100 mb-1">
                                        Estudiante · {{ $message->created_at->format('H:i') }}
                                    </p>

                                    <div class="leading-relaxed">
                                        {!! nl2br(e($message->content)) !!}
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="flex justify-start">
                                <div class="max-w-[85%] rounded-2xl rounded-bl-sm bg-white border border-gray-200 px-4 py-3 text-gray-700 text-sm shadow-sm">
                                    <p class="text-xs text-gray-400 mb-1">
                                        Asistente IA · {{ $message->created_at->format('H:i') }}
                                    </p>

                                    <div class="leading-relaxed">
                                        {!! nl2br(e($message->content)) !!}
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                        @else
                        <div class="rounded-xl border border-gray-200 bg-white p-4 text-sm text-gray-500">
                            Esta conversación aún no tiene mensajes registrados.
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="bg-white shadow-sm rounded-2xl p-6 text-gray-500 border border-gray-100">
                    Este estudiante no tiene conversaciones registradas.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>