<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vocational_reports', function (Blueprint $table) {
            $table->unsignedInteger('version')->default(1)->after('conversation_id');
            $table->boolean('is_current')->default(true)->after('version');
            $table->foreignId('generated_until_message_id')->nullable()->after('is_current')->constrained('messages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vocational_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('generated_until_message_id');
            $table->dropColumn(['version', 'is_current']);
        });
    }
};
