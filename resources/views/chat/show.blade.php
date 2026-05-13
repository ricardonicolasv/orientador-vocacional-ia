<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat vocacional</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <main class="max-w-5xl mx-auto min-h-screen flex flex-col px-4 py-6">
        <header class="bg-white border border-slate-200 rounded-3xl shadow-sm p-5 mb-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <p class="text-sm text-green-700 font-semibold">Orientador Vocacional IA</p>
                    <h1 class="text-2xl font-bold text-slate-900">
                        Chat de {{ $conversation->student->name }}
                    </h1>
                    <p class="text-sm text-slate-500">
                        Curso: {{ $conversation->student->course }} · Ruta: {{ $conversation->selected_route }}
                    </p>
                </div>

                <a href="{{ route('welcome') }}"
                   class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Salir
                </a>
            </div>
        </header>

        <section class="flex-1 bg-white border border-slate-200 rounded-3xl shadow-sm p-5 overflow-y-auto">
            <div class="space-y-4">
                @foreach ($conversation->messages as $message)
                    @if ($message->sender === 'student')
                        <div class="flex justify-end">
                            <div class="max-w-[80%] rounded-2xl rounded-br-sm bg-green-700 px-4 py-3 text-white">
                                {!! nl2br(e($message->content)) !!}
                            </div>
                        </div>
                    @else
                        <div class="flex justify-start">
                            <div class="max-w-[80%] rounded-2xl rounded-bl-sm bg-slate-100 px-4 py-3 text-slate-700">
                                {!! nl2br(e($message->content)) !!}
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </section>

        <form action="{{ route('chat.message', $conversation) }}" method="POST"
              class="mt-4 bg-white border border-slate-200 rounded-3xl shadow-sm p-4">
            @csrf

            @error('content')
                <p class="mb-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="flex gap-3">
                <input type="text" name="content"
                       class="flex-1 rounded-xl border-slate-300 focus:border-green-600 focus:ring-green-600"
                       placeholder="Escribe tu mensaje...">

                <button type="submit"
                        class="rounded-xl bg-green-700 px-5 py-3 text-white font-semibold hover:bg-green-800 transition">
                    Enviar
                </button>
            </div>
        </form>
    </main>
</body>
</html>