<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orientador Vocacional IA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
    <main class="min-h-screen flex items-center justify-center px-6 py-10">
        <section class="max-w-5xl w-full grid md:grid-cols-2 gap-8 items-center">
            <div>
                <span class="inline-flex items-center rounded-full bg-green-100 px-4 py-1 text-sm font-medium text-green-700">
                    Instituto San José
                </span>

                <h1 class="mt-6 text-4xl md:text-5xl font-bold tracking-tight text-slate-900">
                    Orientador Vocacional IA
                </h1>

                <p class="mt-5 text-lg text-slate-600 leading-relaxed">
                    Una plataforma de apoyo para estudiantes de enseñanza media que desean explorar carreras,
                    instituciones, beneficios estudiantiles y posibles caminos después de 4° medio.
                </p>

                <div class="mt-8 flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('student.create') }}"
                       class="inline-flex justify-center rounded-xl bg-green-700 px-6 py-3 text-white font-semibold shadow hover:bg-green-800 transition">
                        Comenzar orientación
                    </a>

                    <a href="{{ route('login') }}"
                       class="inline-flex justify-center rounded-xl border border-slate-300 px-6 py-3 text-slate-700 font-semibold hover:bg-white transition">
                        Acceso orientador
                    </a>
                </div>

                <p class="mt-6 text-sm text-slate-500">
                    La información entregada es orientativa y no reemplaza la entrevista con el orientador del colegio.
                </p>
            </div>

            <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6">
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