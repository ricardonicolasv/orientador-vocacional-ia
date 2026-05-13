<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Conversation;
use Illuminate\Http\Request;

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
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('course', 'like', "%{$search}%")
                    ->orWhere('school', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalStudents = Student::count();
        $totalConversations = Conversation::count();
        $activeConversations = Conversation::where('status', 'active')->count();

        return view('orientador.dashboard', compact(
            'students',
            'search',
            'totalStudents',
            'totalConversations',
            'activeConversations'
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