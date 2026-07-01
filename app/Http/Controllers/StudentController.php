<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function welcome()
    {
        return view('welcome');
    }

    public function create()
    {
        return view('student.create');
    }
    private function generateAccessCode(): string
    {
        do {
            $code = 'ISJ-' . strtoupper(Str::random(5));
        } while (Student::where('access_code', $code)->exists());

        return $code;
    }
    public function resumeForm()
    {
        return view('student.resume');
    }

    public function resume(Request $request)
    {
        $validated = $request->validate([
            'access_code' => ['required', 'string', 'max:20'],
        ], [
            'access_code.required' => 'Ingresa tu código de acceso.',
        ]);

        $accessCode = strtoupper(trim($validated['access_code']));

        $student = Student::where('access_code', $accessCode)->first();

        if (!$student) {
            return back()
                ->withErrors(['access_code' => 'No encontramos un estudiante con ese código.'])
                ->withInput();
        }

        $conversation = $student->conversations()
            ->latest()
            ->first();

        if (!$conversation) {
            return back()
                ->withErrors(['access_code' => 'No tienes una conversación activa para retomar.'])
                ->withInput();
        }

        return redirect()->route('chat.show', $conversation);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'course' => ['required', 'string', 'max:50'],
            'school' => ['required', 'string', 'max:150'],
            'selected_route' => ['required', 'string', 'max:100'],
            'consent_accepted' => ['accepted'],
        ], [
            'name.required' => 'Debes ingresar tu nombre.',
            'course.required' => 'Debes seleccionar tu curso.',
            'school.required' => 'Debes ingresar el colegio.',
            'selected_route.required' => 'Debes seleccionar una ruta vocacional.',
            'consent_accepted.accepted' => 'Debes aceptar el consentimiento para continuar.',
        ]);

        $student = Student::create([
            'name' => $validated['name'],
            'course' => $validated['course'],
            'school' => $validated['school'],
            'consent_accepted' => true,
            'access_code' => $this->generateAccessCode(),
        ]);

        $conversation = Conversation::create([
            'student_id' => $student->id,
            'selected_route' => $validated['selected_route'],
            'status' => 'active',
            'started_at' => now(),
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender' => 'ai',
            'content' => $this->initialMessage($student->name, $validated['selected_route']),
        ]);

        return redirect()->route('chat.show', $conversation);
    }

    private function initialMessage(string $studentName, string $route): string
    {
        $routes = [
            'universidad' => 'ruta universitaria',
            'tecnico-profesional' => 'ruta técnico-profesional',
            'beneficios-fuas' => 'ruta de beneficios, gratuidad, becas y FUAS',
            'pedagogia' => 'ruta de pedagogías',
            'ffaa-orden' => 'ruta de Fuerzas Armadas, de Orden y Seguridad Pública',
            'no-se-aun' => 'exploración vocacional general',
        ];

        $routeName = $routes[$route] ?? 'exploración vocacional general';

        return "Hola {$studentName}, soy tu asistente vocacional del Instituto San José. Estoy aquí para ayudarte a explorar opciones de estudio, carreras, beneficios y caminos posibles después de 4° medio. Veo que seleccionaste {$routeName}. Para orientarte mejor, cuéntame: ¿qué asignaturas te gustan más y qué actividades disfrutas hacer?";
    }
}
