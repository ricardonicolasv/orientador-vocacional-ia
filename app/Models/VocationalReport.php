<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VocationalReport extends Model
{
    protected $fillable = [
        'student_id',
        'conversation_id',
        'interests',
        'detected_areas',
        'explored_routes',
        'main_questions',
        'clarity_level',
        'recommendations',
        'student_summary',
        'orientador_notes',
        'version',
        'is_current',
        'generated_until_message_id',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
