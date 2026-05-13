<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Panel del Orientador
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Seguimiento general de estudiantes, conversaciones y reportes vocacionales.
                </p>
                <p class="text-xs text-purple-700 mt-2 font-semibold">
                    Modo IA activo:
                    @switch(config('ai.mode'))
                    @case('openai')
                    OpenAI
                    @break

                    @case('groq')
                    Groq
                    @break

                    @default
                    Local
                    @endswitch
                </p>
            </div>

            <a href="{{ route('welcome') }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Ir al inicio
            </a>
        </div>
    </x-slot>

    @php
    $today = now()->format('Y-m-d');
    $routeLabels = [
    'universidad' => 'Universitaria',
    'tecnico-profesional' => 'Técnico-profesional',
    'beneficios-fuas' => 'Beneficios / FUAS',
    'pedagogia' => 'Pedagogía',
    'ffaa-orden' => 'FF.AA. y Orden',
    'no-se-aun' => 'Exploración general',
    ];

    $clarityLabels = [
    'bajo' => 'Bajo',
    'medio' => 'Medio',
    'alto' => 'Alto',
    ];

    $clarityClasses = [
    'bajo' => 'bg-red-100 text-red-700',
    'medio' => 'bg-yellow-100 text-yellow-700',
    'alto' => 'bg-green-100 text-green-700',
    ];

    $maxRouteTotal = max($routesStats->max('total') ?? 1, 1);
    $maxCourseTotal = max($courseStats->max('total') ?? 1, 1);
    $maxClarityTotal = max($clarityStats->max('total') ?? 1, 1);
    @endphp

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">
                <p class="font-semibold">Revisa los filtros ingresados:</p>

                <ul class="mt-2 list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            {{-- Tarjetas principales --}}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-6 border border-gray-100">
                    <p class="text-sm text-gray-500">Estudiantes registrados</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalStudents }}</p>
                    <p class="text-xs text-gray-400 mt-2">Total acumulado en el sistema</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-6 border border-gray-100">
                    <p class="text-sm text-gray-500">Conversaciones totales</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalConversations }}</p>
                    <p class="text-xs text-gray-400 mt-2">Chats iniciados por estudiantes</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-6 border border-gray-100">
                    <p class="text-sm text-gray-500">Conversaciones activas</p>
                    <p class="text-3xl font-bold text-green-700 mt-2">{{ $activeConversations }}</p>
                    <p class="text-xs text-gray-400 mt-2">Conversaciones aún no finalizadas</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-6 border border-gray-100">
                    <p class="text-sm text-gray-500">Reportes generados</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalReports }}</p>
                    <p class="text-xs text-gray-400 mt-2">Reportes disponibles para revisión</p>
                </div>
            </div>

            {{-- Estadísticas --}}
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                {{-- Rutas más consultadas --}}
                <div class="bg-white shadow-sm sm:rounded-2xl p-6 border border-gray-100">
                    <div class="mb-5">
                        <h3 class="text-lg font-bold text-gray-900">
                            Rutas más consultadas
                        </h3>
                        <p class="text-sm text-gray-500">
                            Distribución de conversaciones por ruta vocacional.
                        </p>
                    </div>

                    <div class="space-y-4">
                        @forelse($routesStats as $routeStat)
                        @php
                        $percentage = round(($routeStat->total / $maxRouteTotal) * 100);
                        $label = $routeLabels[$routeStat->selected_route] ?? $routeStat->selected_route;
                        @endphp

                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700">{{ $label }}</span>
                                <span class="text-gray-500">{{ $routeStat->total }}</span>
                            </div>

                            <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-2 rounded-full bg-green-700" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500">
                            Aún no hay conversaciones registradas.
                        </p>
                        @endforelse
                    </div>
                </div>

                {{-- Claridad vocacional --}}
                <div class="bg-white shadow-sm sm:rounded-2xl p-6 border border-gray-100">
                    <div class="mb-5">
                        <h3 class="text-lg font-bold text-gray-900">
                            Claridad vocacional
                        </h3>
                        <p class="text-sm text-gray-500">
                            Nivel estimado en reportes generados.
                        </p>
                    </div>

                    <div class="space-y-4">
                        @if($studentsWithoutReports > 0)
                        <div>
                            <div class="flex justify-between items-center text-sm mb-1">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-gray-100 text-gray-700">
                                    Sin reporte
                                </span>
                                <span class="text-gray-500">{{ $studentsWithoutReports }}</span>
                            </div>

                            <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-2 rounded-full bg-gray-400"
                                    style="width: {{ round(($studentsWithoutReports / max($totalStudents, 1)) * 100) }}%">
                                </div>
                            </div>
                        </div>
                        @endif
                        @forelse($clarityStats as $clarityStat)
                        @php
                        $percentage = round(($clarityStat->total / max($totalStudents, 1)) * 100);
                        $label = $clarityLabels[$clarityStat->clarity_level] ?? ucfirst($clarityStat->clarity_level);
                        $class = $clarityClasses[$clarityStat->clarity_level] ?? 'bg-gray-100 text-gray-700';
                        @endphp

                        <div>
                            <div class="flex justify-between items-center text-sm mb-1">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $class }}">
                                    {{ $label }}
                                </span>
                                <span class="text-gray-500">{{ $clarityStat->total }}</span>
                            </div>

                            <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-2 rounded-full bg-gray-700" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500">
                            Aún no hay reportes generados.
                        </p>
                        @endforelse
                    </div>
                </div>

                {{-- Cursos con mayor uso --}}
                <div class="bg-white shadow-sm sm:rounded-2xl p-6 border border-gray-100">
                    <div class="mb-5">
                        <h3 class="text-lg font-bold text-gray-900">
                            Cursos con mayor uso
                        </h3>
                        <p class="text-sm text-gray-500">
                            Cantidad de estudiantes registrados por curso.
                        </p>
                    </div>

                    <div class="space-y-4">
                        @forelse($courseStats as $courseStat)
                        @php
                        $percentage = round(($courseStat->total / $maxCourseTotal) * 100);
                        @endphp

                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700">{{ $courseStat->course }}</span>
                                <span class="text-gray-500">{{ $courseStat->total }}</span>
                            </div>

                            <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-2 rounded-full bg-blue-700" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500">
                            Aún no hay estudiantes registrados.
                        </p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Tabla de estudiantes --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">
                                Estudiantes registrados
                            </h3>
                            <p class="text-sm text-gray-500">
                                Revisa el detalle de cada estudiante y filtra por curso, ruta, claridad o fecha.
                            </p>
                        </div>

                        <form method="GET" action="{{ route('orientador.dashboard') }}" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                                        Buscar
                                    </label>
                                    <input type="text" name="search" value="{{ $search }}"
                                        placeholder="Nombre, curso o colegio..."
                                        class="w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                                        Curso
                                    </label>
                                    <select name="course"
                                        class="w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                                        <option value="">Todos los cursos</option>
                                        @foreach($availableCourses as $availableCourse)
                                        <option value="{{ $availableCourse }}" @selected($course===$availableCourse)>
                                            {{ $availableCourse }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                                        Ruta vocacional
                                    </label>
                                    <select name="route"
                                        class="w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                                        <option value="">Todas las rutas</option>
                                        @foreach($routeLabels as $value => $label)
                                        <option value="{{ $value }}" @selected($route===$value)>
                                            {{ $label }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                                        Claridad vocacional
                                    </label>
                                    <select name="clarity"
                                        class="w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                                        <option value="">Todos los niveles</option>
                                        @foreach($clarityLabels as $value => $label)
                                        <option value="{{ $value }}" @selected($clarity===$value)>
                                            {{ $label }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                                        Desde
                                    </label>
                                    <input type="date" name="date_from" value="{{ $dateFrom }}" max="{{ $today }}"
                                        class="w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                                        Hasta
                                    </label>
                                    <input type="date" name="date_to" value="{{ $dateTo }}" max="{{ $today }}"
                                        class="w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-end">
                                <button type="submit"
                                    class="rounded-xl bg-green-700 px-5 py-2.5 text-white font-semibold hover:bg-green-800">
                                    Aplicar filtros
                                </button>

                                @if($search || $course || $route || $clarity || $dateFrom || $dateTo)
                                <a href="{{ route('orientador.dashboard') }}"
                                    class="rounded-xl border border-gray-300 px-5 py-2.5 text-gray-700 font-semibold hover:bg-gray-50 text-center">
                                    Limpiar filtros
                                </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Estudiante
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Curso
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Colegio
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Conversaciones
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Última ruta
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Acción
                                </th>
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($students as $student)
                            @php
                            $lastConversation = $student->conversations->first();
                            $lastRouteLabel = $lastConversation
                            ? ($routeLabels[$lastConversation->selected_route] ?? $lastConversation->selected_route)
                            : null;
                            @endphp

                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-gray-900">
                                        {{ $student->name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Registrado: {{ $student->created_at->format('d-m-Y H:i') }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                    {{ $student->course }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                    {{ $student->school }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                    {{ $student->conversations_count }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($lastConversation)
                                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                        {{ $lastRouteLabel }}
                                    </span>
                                    @else
                                    <span class="text-gray-400">Sin conversación</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <a href="{{ route('orientador.students.show', $student) }}"
                                        class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                                        Ver detalle
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                    No se encontraron estudiantes con los filtros aplicados.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-6">
                    {{ $students->links() }}
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateFrom = document.querySelector('input[name="date_from"]');
            const dateTo = document.querySelector('input[name="date_to"]');

            if (!dateFrom || !dateTo) {
                return;
            }

            const today = new Date().toISOString().split('T')[0];

            dateFrom.setAttribute('max', today);
            dateTo.setAttribute('max', today);

            function syncDateLimits() {
                if (dateFrom.value) {
                    dateTo.setAttribute('min', dateFrom.value);
                } else {
                    dateTo.removeAttribute('min');
                }

                if (dateTo.value) {
                    dateFrom.setAttribute('max', dateTo.value < today ? dateTo.value : today);
                } else {
                    dateFrom.setAttribute('max', today);
                }

                if (dateFrom.value && dateTo.value && dateFrom.value > dateTo.value) {
                    dateTo.value = dateFrom.value;
                }
            }

            dateFrom.addEventListener('change', syncDateLimits);
            dateTo.addEventListener('change', syncDateLimits);

            syncDateLimits();
        });
    </script>
</x-app-layout>