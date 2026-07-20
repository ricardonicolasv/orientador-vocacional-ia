<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Student;
use App\Models\Message;
use App\Models\VocationalReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrientadorDashboardController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'course' => ['nullable', 'string', 'max:50'],
            'route' => ['nullable', 'string', 'max:100'],
            'clarity' => ['nullable', 'in:bajo,medio,alto'],
            'date_from' => ['nullable', 'date', 'before_or_equal:today'],
            'date_to' => ['nullable', 'date', 'before_or_equal:today', 'after_or_equal:date_from'],
        ], [
            'date_from.before_or_equal' => 'La fecha desde no puede ser posterior al día actual.',
            'date_to.before_or_equal' => 'La fecha hasta no puede ser posterior al día actual.',
            'date_to.after_or_equal' => 'La fecha hasta debe ser igual o posterior a la fecha desde.',
        ]);

        $search = $validated['search'] ?? null;
        $course = $validated['course'] ?? null;
        $route = $validated['route'] ?? null;
        $clarity = $validated['clarity'] ?? null;
        $dateFrom = $validated['date_from'] ?? null;
        $dateTo = $validated['date_to'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Consulta base filtrada
        |--------------------------------------------------------------------------
        */

        $studentsQuery = Student::query()
            ->withCount('conversations')
            ->with([
                'conversations' => function ($query) {
                    $query->latest();
                },
                'reports',
            ]);

        if ($search) {
            $studentsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('course', 'like', "%{$search}%")
                    ->orWhere('school', 'like', "%{$search}%");
            });
        }

        if ($course) {
            $studentsQuery->where('course', $course);
        }

        if ($dateFrom) {
            $studentsQuery->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $studentsQuery->whereDate('created_at', '<=', $dateTo);
        }

        if ($route) {
            $studentsQuery->whereHas('conversations', function ($query) use ($route) {
                $query->where('selected_route', $route);
            });
        }

        if ($clarity) {
            $studentsQuery->whereHas('reports', function ($query) use ($clarity) {
                $query->where('clarity_level', $clarity);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Estadísticas según filtros
        |--------------------------------------------------------------------------
        */

        $filteredStudentIds = (clone $studentsQuery)
            ->select('students.id')
            ->pluck('id');

        $totalStudents = $filteredStudentIds->count();

        $totalConversations = Conversation::whereIn('student_id', $filteredStudentIds)
            ->count();

        $activeConversations = Conversation::whereIn('student_id', $filteredStudentIds)
            ->where('status', 'active')
            ->count();

        $totalReports = VocationalReport::whereIn('student_id', $filteredStudentIds)
            ->count();

        $routesStats = Conversation::select('selected_route', DB::raw('COUNT(*) as total'))
            ->whereIn('student_id', $filteredStudentIds)
            ->whereNotNull('selected_route')
            ->groupBy('selected_route')
            ->orderByDesc('total')
            ->get();

        $clarityStats = VocationalReport::select('clarity_level', DB::raw('COUNT(DISTINCT student_id) as total'))
            ->whereIn('student_id', $filteredStudentIds)
            ->groupBy('clarity_level')
            ->orderBy('clarity_level')
            ->get();

        $studentsWithReports = VocationalReport::whereIn('student_id', $filteredStudentIds)
            ->distinct('student_id')
            ->count('student_id');

        $studentsWithoutReports = max($totalStudents - $studentsWithReports, 0);

        $courseStats = Student::select('course', DB::raw('COUNT(*) as total'))
            ->whereIn('id', $filteredStudentIds)
            ->groupBy('course')
            ->orderByDesc('total')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Lista paginada
        |--------------------------------------------------------------------------
        */

        $students = $studentsQuery
            ->latest()
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Opciones para selects
        |--------------------------------------------------------------------------
        */

        $availableCourses = Student::select('course')
            ->whereNotNull('course')
            ->distinct()
            ->orderBy('course')
            ->pluck('course');

        return view('orientador.dashboard', compact(
            'students',
            'search',
            'course',
            'route',
            'clarity',
            'dateFrom',
            'dateTo',
            'totalStudents',
            'totalConversations',
            'activeConversations',
            'totalReports',
            'routesStats',
            'clarityStats',
            'courseStats',
            'availableCourses',
            'studentsWithoutReports'
        ));
    }

    public function showStudent(Student $student)
    {
        $student->load([
            'conversations' => function ($query) {
                $query->latest();
            },
            'conversations.messages',
            'conversations.reports' => function ($query) {
                $query->latest();
            },
            'reports' => function ($query) {
                $query->latest();
            },
        ]);

        return view('orientador.student-show', compact('student'));
    }
    public function destroyStudent(Student $student)
    {
        try {
            $studentName = $student->name;

            DB::transaction(function () use ($student) {
                $conversationIds = Conversation::query()
                    ->where('student_id', $student->id)
                    ->pluck('id');

                /*
             * Primero eliminamos los informes porque pueden tener
             * referencias tanto al estudiante como a mensajes.
             */
                VocationalReport::query()
                    ->where(function ($query) use ($student, $conversationIds) {
                        $query->where('student_id', $student->id);

                        if ($conversationIds->isNotEmpty()) {
                            $query->orWhereIn(
                                'conversation_id',
                                $conversationIds
                            );
                        }
                    })
                    ->delete();

                /*
             * Luego eliminamos todos los mensajes de las conversaciones.
             */
                if ($conversationIds->isNotEmpty()) {
                    Message::query()
                        ->whereIn('conversation_id', $conversationIds)
                        ->delete();

                    Conversation::query()
                        ->whereIn('id', $conversationIds)
                        ->delete();
                }

                /*
             * Finalmente eliminamos el registro del estudiante.
             */
                $student->delete();
            });

            return redirect()
                ->route('orientador.dashboard')
                ->with(
                    'success',
                    "El registro de {$studentName} y toda su información asociada fueron eliminados correctamente."
                );
        } catch (\Throwable $exception) {
            Log::error('Error al eliminar estudiante', [
                'student_id' => $student->id,
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('orientador.dashboard')
                ->with(
                    'error',
                    'No fue posible eliminar el registro. Intenta nuevamente.'
                );
        }
    }
}
