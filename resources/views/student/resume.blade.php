<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retomar orientación</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">
    <main class="max-w-xl mx-auto px-4 py-10">
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6">
            <p class="text-sm font-semibold text-green-700">
                Orientador Vocacional IA
            </p>

            <h1 class="mt-2 text-2xl font-bold text-slate-900">
                Retomar orientación
            </h1>

            <p class="mt-2 text-sm text-slate-600">
                Ingresa el código de acceso que se mostró en tu chat para continuar tu conversación activa.
            </p>

            <form method="POST" action="{{ route('student.resume') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Código de acceso
                    </label>

                    <input type="text" name="access_code" value="{{ old('access_code') }}"
                        placeholder="Ej: ISJ-8F3K2"
                        class="w-full rounded-xl border-slate-300 uppercase focus:border-green-600 focus:ring-green-600">

                    @error('access_code')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col sm:flex-row gap-2">
                    <button type="submit"
                        class="inline-flex justify-center rounded-xl bg-green-700 px-5 py-3 text-sm font-semibold text-white hover:bg-green-800">
                        Retomar chat
                    </button>

                    <a href="{{ route('welcome') }}"
                        class="inline-flex justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Volver al inicio
                    </a>
                </div>
            </form>
        </div>
    </main>
</body>

</html>