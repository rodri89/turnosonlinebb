<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TurnoPagoIntent extends Model
{
    protected $table = 'turno_pago_intents';

    protected $fillable = [
        'paciente_id',
        'medico_id',
        'consultorio_id',
        'dia',
        'horario',
        'fecha_turno',
        'primer_control',
        'tipo_turno',
        'especialidad',
        'amount',
        'platform_fee',
        'preference_id',
        'payment_id',
        'status',
        'turno_registrado_id',
        'expires_at',
    ];

    protected $dates = [
        'fecha_turno',
        'expires_at',
    ];

    public function turnoRegistrado()
    {
        return $this->belongsTo(TurnoRegistrado::class, 'turno_registrado_id');
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isExpired()
    {
        return $this->status === 'expired'
            || ($this->expires_at && $this->expires_at->isPast() && $this->isPending());
    }
}
