<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat vocacional</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">
    @php
    $routeLabels = [
    'universidad' => 'Ruta universitaria',
    'tecnico-profesional' => 'Ruta técnico-profesional',
    'beneficios-fuas' => 'Beneficios / FUAS',
    'pedagogia' => 'Pedagogía',
    'ffaa-orden' => 'FF.AA., Orden y Seguridad',
    'no-se-aun' => 'Exploración general',
    ];

    $routeLabel = $routeLabels[$conversation->selected_route] ?? $conversation->selected_route;
    $isFinished = $conversation->status === 'finished';
    @endphp

    <main class="max-w-5xl mx-auto min-h-screen flex flex-col px-4 py-6">
        <header class="bg-white border border-slate-200 rounded-3xl shadow-sm p-5 mb-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <p class="text-sm text-green-700 font-semibold">
                        Orientador Vocacional IA
                    </p>

                    <h1 class="text-2xl font-bold text-slate-900">
                        Chat de {{ $conversation->student->name }}
                    </h1>

                    <div class="mt-2 flex flex-wrap gap-2 text-xs">
                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 font-semibold text-green-700">
                            {{ $conversation->student->course }}
                        </span>

                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-700">
                            {{ $routeLabel }}
                        </span>

                        @if($isFinished)
                        <span class="inline-flex rounded-full bg-red-100 px-3 py-1 font-semibold text-red-700">
                            Conversación finalizada
                        </span>
                        @else
                        <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 font-semibold text-blue-700">
                            Conversación activa
                        </span>
                        @endif
                        <span class="inline-flex rounded-full bg-purple-100 px-3 py-1 font-semibold text-purple-700">
                            IA:
                            @switch(config('ai.mode'))
                            @case('openai')
                            OpenAI
                            @break

                            @case('groq')
                            Groq
                            @break

                            @case('gemini')
                            Gemini
                            @break

                            @default
                            Local
                            @endswitch
                        </span>
                        @if($conversation->student->access_code && !$isFinished)
                        <p class="mt-3 text-xs text-slate-500">
                            Guarda este código para retomar tu conversación si sales de la página:
                            <span class="font-bold text-slate-800">{{ $conversation->student->access_code }}</span>
                        </p>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-2">
                    @if(!$isFinished)
                    <form action="{{ route('chat.finish', $conversation) }}" method="POST"
                        onsubmit="return confirm('¿Deseas finalizar esta conversación? Después no podrás enviar más mensajes.');">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 hover:shadow-md">
                            Finalizar conversación
                        </button>
                    </form>
                    @endif

                    <a href="{{ route('welcome') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 hover:text-slate-900">
                        Salir
                    </a>
                </div>
            </div>
        </header>

        @if(session('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-4 text-green-700">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">
            {{ session('error') }}
        </div>
        @endif

        <section id="chatContainer"
            class="flex-1 bg-white border border-slate-200 rounded-3xl shadow-sm p-5 overflow-y-auto min-h-[520px] max-h-[65vh]">
            <div class="space-y-4">
                @foreach ($conversation->messages as $message)
                @if ($message->sender === 'student')
                <div class="flex justify-end">
                    <div class="max-w-[85%] rounded-2xl rounded-br-sm bg-green-700 px-4 py-3 text-white shadow-sm">
                        <p class="mb-1 text-xs text-green-100">
                            Tú · {{ $message->created_at->format('H:i') }}
                        </p>
                        <div class="text-sm leading-relaxed">
                            {!! nl2br(e($message->content)) !!}
                        </div>
                    </div>
                </div>
                @else
                <div class="flex justify-start">
                    <div class="max-w-[85%] rounded-2xl rounded-bl-sm bg-slate-100 px-4 py-3 text-slate-700 shadow-sm">
                        <p class="mb-1 text-xs text-slate-400">
                            Asistente vocacional · {{ $message->created_at->format('H:i') }}
                        </p>
                        <div class="text-sm leading-relaxed">
                            {!! nl2br(e($message->content)) !!}
                        </div>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </section>

        @if(!$isFinished)
        <section class="mt-4 bg-white border border-slate-200 rounded-3xl shadow-sm p-4">
            <p class="mb-3 text-sm font-semibold text-slate-700">
                Preguntas rápidas
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                <button type="button"
                    data-message="Quiero comparar carreras relacionadas con mis intereses."
                    class="quick-message rounded-xl border border-slate-200 px-4 py-2 text-left text-sm text-slate-700 hover:border-green-600 hover:bg-green-50">
                    Comparar carreras relacionadas con mis intereses
                </button>

                <button type="button"
                    data-message="Quiero saber qué ruta me conviene más: universidad, IP o CFT."
                    class="quick-message rounded-xl border border-slate-200 px-4 py-2 text-left text-sm text-slate-700 hover:border-green-600 hover:bg-green-50">
                    Saber si me conviene universidad, IP o CFT
                </button>

                <button type="button"
                    data-message="Quiero información sobre beneficios, becas, gratuidad y FUAS."
                    class="quick-message rounded-xl border border-slate-200 px-4 py-2 text-left text-sm text-slate-700 hover:border-green-600 hover:bg-green-50">
                    Consultar beneficios, becas, gratuidad y FUAS
                </button>

                <button type="button"
                    data-message="Todavía no tengo claro qué estudiar y necesito orientación paso a paso."
                    class="quick-message rounded-xl border border-slate-200 px-4 py-2 text-left text-sm text-slate-700 hover:border-green-600 hover:bg-green-50">
                    No tengo claro qué estudiar
                </button>
            </div>
        </section>

        <form id="chatForm" action="{{ route('chat.message', $conversation) }}" method="POST"
            class="mt-4 bg-white border border-slate-200 rounded-3xl shadow-sm p-4">
            @csrf

            @error('content')
            <p class="mb-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="flex flex-col md:flex-row gap-3">
                <textarea id="messageInput" name="content" rows="2" required maxlength="2000"
                    class="flex-1 resize-none rounded-xl border-slate-300 focus:border-green-600 focus:ring-green-600"
                    placeholder="Escribe tu mensaje..."></textarea>

                <button id="sendButton" type="submit"
                    class="rounded-xl bg-green-700 px-6 py-3 text-white font-semibold hover:bg-green-800 transition disabled:opacity-60 disabled:cursor-not-allowed">
                    Enviar
                </button>
            </div>
            <p id="loadingMessage" class="mt-2 hidden text-sm font-medium text-slate-500">
                Generando respuesta, espera un momento...
            </p>
            <p class="mt-2 text-xs text-slate-400">
                Esta herramienta entrega orientación inicial y no reemplaza la conversación con el orientador del colegio.
            </p>
        </form>
        @else
        <div class="mt-4 rounded-3xl border border-slate-200 bg-white p-6 text-center shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">
                Conversación finalizada
            </h2>

            <p class="mt-2 text-sm text-slate-600">
                Ya no se pueden enviar más mensajes en esta conversación. El orientador podrá revisar el historial y generar un reporte vocacional.
            </p>

            <a href="{{ route('welcome') }}"
                class="mt-4 inline-flex rounded-xl bg-green-700 px-5 py-2.5 text-white font-semibold hover:bg-green-800">
                Volver al inicio
            </a>
        </div>
        @endif
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatContainer = document.getElementById('chatContainer');
            const messageInput = document.getElementById('messageInput');
            const quickButtons = document.querySelectorAll('.quick-message');
            const chatForm = document.getElementById('chatForm');
            const sendButton = document.getElementById('sendButton');
            const loadingMessage = document.getElementById('loadingMessage');

            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }

            quickButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    if (!messageInput || messageInput.readOnly) {
                        return;
                    }

                    messageInput.value = button.dataset.message;
                    messageInput.focus();
                });
            });

            if (chatForm && messageInput && sendButton) {
                let isSubmitting = false;

                chatForm.addEventListener('submit', function(event) {
                    const content = messageInput.value.trim();

                    if (!content) {
                        event.preventDefault();
                        messageInput.focus();
                        return;
                    }

                    if (isSubmitting) {
                        event.preventDefault();
                        return;
                    }

                    isSubmitting = true;

                    sendButton.disabled = true;
                    sendButton.textContent = 'Enviando...';
                    messageInput.readOnly = true;

                    quickButtons.forEach(function(button) {
                        button.disabled = true;
                        button.classList.add('opacity-60', 'cursor-not-allowed');
                    });

                    if (loadingMessage) {
                        loadingMessage.classList.remove('hidden');
                    }
                });
                window.addEventListener('pageshow', function() {
                    if (sendButton) {
                        sendButton.disabled = false;
                        sendButton.textContent = 'Enviar';
                    }

                    if (messageInput) {
                        messageInput.readOnly = false;
                    }

                    if (loadingMessage) {
                        loadingMessage.classList.add('hidden');
                    }

                    quickButtons.forEach(function(button) {
                        button.disabled = false;
                        button.classList.remove('opacity-60', 'cursor-not-allowed');
                    });
                });
            }
        });
    </script>
</body>

</html>