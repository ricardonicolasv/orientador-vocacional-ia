<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vocational_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');

            $table->text('interests')->nullable();
            $table->text('detected_areas')->nullable();
            $table->text('explored_routes')->nullable();
            $table->text('main_questions')->nullable();

            $table->enum('clarity_level', ['bajo', 'medio', 'alto'])->default('bajo');

            $table->text('recommendations')->nullable();
            $table->text('student_summary')->nullable();
            $table->text('orientador_notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vocational_reports');
    }
};