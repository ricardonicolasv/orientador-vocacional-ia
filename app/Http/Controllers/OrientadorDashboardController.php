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
        $search = $request->input('search');

        $students = Student::withCount('conversations')
            ->with(['conversations' => function ($query) {
                $query->latest();
            }])
            ->when($search, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('course', 'like', "%{$search}%")
                        ->orWhere('school', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalStudents = Student::count();
        $totalConversations = Conversation::count();
        $activeConversations = Conversation::where('status', 'active')->count();
        $totalReports = VocationalReport::count();

        $routesStats = Conversation::select('selected_route', DB::raw('COUNT(*) as total'))
            ->whereNotNull('selected_route')
            ->groupBy('selected_route')
            ->orderByDesc('total')
            ->get();

        $clarityStats = VocationalReport::select('clarity_level', DB::raw('COUNT(*) as total'))
            ->groupBy('clarity_level')
            ->orderBy('clarity_level')
            ->get();

        $courseStats = Student::select('course', DB::raw('COUNT(*) as total'))
            ->groupBy('course')
            ->orderByDesc('total')
            ->get();

        return view('orientador.dashboard', compact(
            'students',
            'search',
            'totalStudents',
            'totalConversations',
            'activeConversations',
            'totalReports',
            'routesStats',
            'clarityStats',
            'courseStats'
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
