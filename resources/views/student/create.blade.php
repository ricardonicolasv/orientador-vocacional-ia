<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro del estudiante</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
    <main class="max-w-4xl mx-auto px-6 py-10">
        <div class="mb-8">
            <a href="{{ route('welcome') }}" class="text-sm text-green-700 hover:text-green-800">
                ← Volver al inicio
            </a>

            <h1 class="mt-4 text-3xl font-bold text-slate-900">
                Antes de comenzar
            </h1>

            <p class="mt-2 text-slate-600">
                Ingresa tus datos básicos y selecciona la ruta que quieres explorar primero.
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">
                <p class="font-semibold">Revisa los siguientes campos:</p>
                <ul class="mt-2 list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('student.store') }}" method="POST" class="bg-white rounded-3xl shadow border border-slate-100 p-6 space-y-6">
            @csrf

            <div class="grid md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Nombre</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="mt-2 w-full rounded-xl border-slate-300 focus:border-green-600 focus:ring-green-600"
                           placeholder="Ej: Juan Pérez">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700">Curso</label>
                    <select name="course"
                            class="mt-2 w-full rounded-xl border-slate-300 focus:border-green-600 focus:ring-green-600">
                        <option value="">Selecciona tu curso</option>
                        <option value="1° Medio" @selected(old('course') === '1° Medio')>1° Medio</option>
                        <option value="2° Medio" @selected(old('course') === '2° Medio')>2° Medio</option>
                        <option value="3° Medio" @selected(old('course') === '3° Medio')>3° Medio</option>
                        <option value="4° Medio" @selected(old('course') === '4° Medio')>4° Medio</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700">Colegio</label>
                <input type="text" name="school" value="{{ old('school', 'Instituto San José') }}"
                       class="mt-2 w-full rounded-xl border-slate-300 focus:border-green-600 focus:ring-green-600">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-3">
                    ¿Qué ruta quieres explorar primero?
                </label>

                <div class="grid md:grid-cols-2 gap-3">
                    @php
                        $routes = [
                            'universidad' => 'Ruta universitaria',
                            'tecnico-profesional' => 'Ruta técnico-profesional',
                            'beneficios-fuas' => 'Beneficios, gratuidad y FUAS',
                            'pedagogia' => 'Pedagogía',
                            'ffaa-orden' => 'FF.AA., Orden y Seguridad',
                            'no-se-aun' => 'No sé aún / Ayúdame a explorar',
                        ];
                    @endphp

                    @foreach ($routes as $value => $label)
                        <label class="cursor-pointer rounded-2xl border border-slate-200 p-4 hover:border-green-600 hover:bg-green-50 transition">
                            <input type="radio" name="selected_route" value="{{ $value }}" class="text-green-700 focus:ring-green-600"
                                @checked(old('selected_route') === $value)>
                            <span class="ml-2 font-medium text-slate-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                <label class="flex gap-3 text-sm text-slate-600">
                    <input type="checkbox" name="consent_accepted" value="1"
                           class="mt-1 rounded border-slate-300 text-green-700 focus:ring-green-600"
                           @checked(old('consent_accepted'))>
                    <span>
                        Acepto que la información entregada sea usada solo con fines de orientación vocacional escolar.
                        Entiendo que esta herramienta entrega apoyo informativo y no reemplaza la conversación con el orientador.
                    </span>
                </label>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="rounded-xl bg-green-700 px-6 py-3 text-white font-semibold hover:bg-green-800 transition">
                    Iniciar chat
                </button>
            </div>
        </form>
    </main>
</body>
</html>