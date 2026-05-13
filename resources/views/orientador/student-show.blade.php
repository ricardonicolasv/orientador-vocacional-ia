<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detalle del estudiante
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Información y conversación vocacional registrada.
                </p>
            </div>

            <a href="{{ route('orientador.dashboard') }}"
               class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Volver al panel
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm sm:rounded-2xl p-6">
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
                        <p class="font-semibold text-gray-900">{{ $student->created_at->format('d-m-Y H:i') }}</p>
                    </div>
                </div>
            </div>

            @forelse($student->conversations as $conversation)
                <div class="bg-white shadow-sm sm:rounded-2xl p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">
                                Conversación #{{ $conversation->id }}
                            </h3>

                            <p class="text-sm text-gray-500">
                                Ruta: {{ $conversation->selected_route }} ·
                                Estado: {{ $conversation->status }} ·
                                Inicio: {{ optional($conversation->started_at)->format('d-m-Y H:i') }}
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
        {{ $conversation->messages->count() }} mensajes
    </span>

    @if($conversation->report)
        <a href="{{ route('orientador.reports.show', $conversation->report) }}"
           class="inline-flex justify-center rounded-lg border border-green-700 px-3 py-1.5 text-xs font-semibold text-green-700 hover:bg-green-50">
            Ver reporte
        </a>
    @else
        <form action="{{ route('orientador.reports.generate', $conversation) }}" method="POST">
            @csrf
            <button type="submit"
                    class="inline-flex justify-center rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-800">
                Generar reporte
            </button>
        </form>
    @endif
</div>
                    </div>

                    <div class="rounded-2xl bg-gray-50 border border-gray-200 p-4 space-y-4">
                        @foreach($conversation->messages as $message)
                            @if($message->sender === 'student')
                                <div class="flex justify-end">
                                    <div class="max-w-[80%] rounded-2xl rounded-br-sm bg-green-700 px-4 py-3 text-white text-sm">
                                        <p class="text-xs text-green-100 mb-1">Estudiante</p>
                                        {!! nl2br(e($message->content)) !!}
                                    </div>
                                </div>
                            @else
                                <div class="flex justify-start">
                                    <div class="max-w-[80%] rounded-2xl rounded-bl-sm bg-white border border-gray-200 px-4 py-3 text-gray-700 text-sm">
                                        <p class="text-xs text-gray-400 mb-1">IA</p>
                                        {!! nl2br(e($message->content)) !!}
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-sm sm:rounded-2xl p-6 text-gray-500">
                    Este estudiante no tiene conversaciones registradas.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>