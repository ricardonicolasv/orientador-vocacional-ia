<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'student_id',
        'selected_route',
        'status',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /*
     * Relación antigua.
     * La dejamos para no romper vistas/controladores existentes.
     * Más adelante puede reemplazarse por currentReport().
     */
    public function report()
    {
        return $this->hasOne(VocationalReport::class);
    }

    /*
     * Todos los informes generados para esta conversación.
     */
    public function reports()
    {
        return $this->hasMany(VocationalReport::class);
    }

    /*
     * Informe vigente/actual de la conversación.
     */
    public function currentReport()
    {
        return $this->hasOne(VocationalReport::class)
            ->where('is_current', true)
            ->latestOfMany();
    }
}
