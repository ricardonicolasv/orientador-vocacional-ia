<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Student;
use App\Models\VocationalReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $students = $studentsQuery
            ->latest()
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Estadísticas según filtros
        |--------------------------------------------------------------------------
        */

        $filteredStudentIds = (clone $studentsQuery)
            ->select('students.id')
            ->pluck('id');

        $totalStudents = $filteredStudentIds->count();

        $totalConversations = Conversation::whereIn('student_id', $filteredStudentIds)->count();

        $activeConversations = Conversation::whereIn('student_id', $filteredStudentIds)
            ->where('status', 'active')
            ->count();

        $totalReports = VocationalReport::whereIn('student_id', $filteredStudentIds)->count();

        $routesStats = Conversation::select('selected_route', DB::raw('COUNT(*) as total'))
            ->whereIn('student_id', $filteredStudentIds)
            ->whereNotNull('selected_route')
            ->groupBy('selected_route')
            ->orderByDesc('total')
            ->get();

        $clarityStats = VocationalReport::select('clarity_level', DB::raw('COUNT(*) as total'))
            ->whereIn('student_id', $filteredStudentIds)
            ->groupBy('clarity_level')
            ->orderBy('clarity_level')
            ->get();

        $courseStats = Student::select('course', DB::raw('COUNT(*) as total'))
            ->whereIn('id', $filteredStudentIds)
            ->groupBy('course')
            ->orderByDesc('total')
            ->get();

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
            'availableCourses'
        ));
    }

    public function showStudent(Student $student)
    {
        $student->load([
            'conversations.messages',
            'conversations.report',
            'reports',
        ]);

        return view('orientador.student-show', compact('student'));
    }
}
