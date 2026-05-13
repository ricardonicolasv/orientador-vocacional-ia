<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Panel del Orientador
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Seguimiento de estudiantes y conversaciones vocacionales.
                </p>
            </div>

            <a href="{{ route('welcome') }}"
               class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Ir al inicio
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-6">
                    <p class="text-sm text-gray-500">Estudiantes registrados</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalStudents }}</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-6">
                    <p class="text-sm text-gray-500">Conversaciones totales</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalConversations }}</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-6">
                    <p class="text-sm text-gray-500">Conversaciones activas</p>
                    <p class="text-3xl font-bold text-green-700 mt-2">{{ $activeConversations }}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl">
                <div class="p-6 border-b border-gray-100">
                    <form method="GET" action="{{ route('orientador.dashboard') }}" class="flex flex-col md:flex-row gap-3">
                        <input type="text"
                               name="search"
                               value="{{ $search }}"
                               placeholder="Buscar por nombre, curso o colegio..."
                               class="w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">

                        <button type="submit"
                                class="rounded-xl bg-green-700 px-5 py-2.5 text-white font-semibold hover:bg-green-800">
                            Buscar
                        </button>

                        @if($search)
                            <a href="{{ route('orientador.dashboard') }}"
                               class="rounded-xl border border-gray-300 px-5 py-2.5 text-gray-700 font-semibold hover:bg-gray-50 text-center">
                                Limpiar
                            </a>
                        @endif
                    </form>
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
                                @endphp

                                <tr>
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
                                                {{ $lastConversation->selected_route }}
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
                                        No hay estudiantes registrados.
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
</x-app-layout>