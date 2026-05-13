<?php

use App\Http\Controllers\OrientadorDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StudentController::class, 'welcome'])->name('welcome');

Route::get('/estudiante/inicio', [StudentController::class, 'create'])->name('student.create');
Route::post('/estudiante', [StudentController::class, 'store'])->name('student.store');

Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
Route::post('/chat/{conversation}/mensaje', [ChatController::class, 'sendMessage'])->name('chat.message');
Route::post('/chat/{conversation}/finalizar', [ChatController::class, 'finish'])
    ->name('chat.finish');

Route::middleware(['auth'])->prefix('orientador')->group(function () {
    Route::get('/dashboard', [OrientadorDashboardController::class, 'index'])
        ->name('orientador.dashboard');

    Route::get('/estudiantes/{student}', [OrientadorDashboardController::class, 'showStudent'])
        ->name('orientador.students.show');

    Route::post('/conversaciones/{conversation}/generar-reporte', [ReportController::class, 'generate'])
        ->name('orientador.reports.generate');

    Route::get('/reportes/{report}', [ReportController::class, 'showForOrientador'])
        ->name('orientador.reports.show');

    Route::get('/reportes/{report}/pdf', [ReportController::class, 'downloadPdf'])
        ->name('orientador.reports.pdf');
});

/*
|--------------------------------------------------------------------------
| Dashboard Breeze
|--------------------------------------------------------------------------
| Redirigimos el dashboard por defecto de Breeze al panel del orientador.
*/
Route::get('/dashboard', function () {
    return redirect()->route('orientador.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
