<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orientador Vocacional IA</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-orientador-vocacional-ia.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">
    <main class="min-h-screen flex items-center justify-center px-6 py-10">
        <section class="max-w-6xl w-full grid md:grid-cols-2 gap-10 items-center">
            <div>
                <!--
                <span class="inline-flex items-center rounded-full bg-green-100 px-4 py-1 text-sm font-medium text-green-700">
                    Instituto San José
                </span>
                -->
                <div class="mt-6">
                    <img src="{{ asset('images/logo-orientador-vocacional-ia.png') }}"
                        alt="Logo Orientador Vocacional IA"
                        class="h-36 md:h-44 w-auto object-contain">
                </div>

                <p class="mt-6 text-lg text-slate-600 leading-relaxed max-w-xl">
                    Una plataforma de apoyo para estudiantes de enseñanza media que desean explorar carreras,
                    instituciones, beneficios estudiantiles y posibles caminos después de 4° medio.
                </p>

                <div class="mt-8 flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('student.create') }}"
                        class="inline-flex items-center justify-center rounded-2xl bg-green-700 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-green-800 hover:shadow-md">
                        Comenzar orientación
                    </a>

                    <a href="{{ route('student.resume.form') }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-green-700 bg-white px-6 py-3 text-sm font-bold text-green-700 shadow-sm transition hover:bg-green-50 hover:shadow-md">
                        Retomar orientación
                    </a>

                    <a href="{{ route('login') }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 hover:shadow-md">
                        Acceso orientador
                    </a>
                </div>

                <p class="mt-6 text-sm text-slate-500 max-w-xl">
                    La información entregada es orientativa y no reemplaza la entrevista con el orientador del colegio.
                </p>
            </div>

            <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6">
                <div class="flex items-center gap-3 mb-6">
                    <img src="{{ asset('images/logo.png') }}"
                        alt="Logo Orientador Vocacional IA"
                        class="h-12 w-12 rounded-2xl object-contain bg-white">

                    <div>
                        <p class="text-sm font-bold text-slate-900">
                            Chat vocacional
                        </p>
                        <p class="text-xs text-slate-500">
                            Orientación inicial asistida por IA
                        </p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-start">
                        <div class="max-w-xs rounded-2xl rounded-bl-sm bg-slate-100 px-4 py-3 text-sm text-slate-700">
                            Hola, soy tu asistente vocacional. ¿Qué áreas te llaman la atención?
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <div class="max-w-xs rounded-2xl rounded-br-sm bg-green-700 px-4 py-3 text-sm text-white">
                            Me gusta la tecnología, pero también ayudar a personas.
                        </div>
                    </div>

                    <div class="flex justify-start">
                        <div class="max-w-xs rounded-2xl rounded-bl-sm bg-slate-100 px-4 py-3 text-sm text-slate-700">
                            Bien. Podemos explorar carreras de informática, salud, educación o áreas sociales.
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-xl bg-green-50 p-4 text-green-800 font-medium">Universidad</div>
                    <div class="rounded-xl bg-green-50 p-4 text-green-800 font-medium">Técnico-profesional</div>
                    <div class="rounded-xl bg-green-50 p-4 text-green-800 font-medium">Beneficios/FUAS</div>
                    <div class="rounded-xl bg-green-50 p-4 text-green-800 font-medium">FF.AA. y Orden</div>
                </div>
            </div>
        </section>
    </main>
</body>

</html>