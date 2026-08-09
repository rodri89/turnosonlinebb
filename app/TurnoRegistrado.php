<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TurnoRegistrado extends Model
{
	protected $fillable = ['paciente','medico', 'consultorio', 'dia', 'horario','fechaTurno','asistio','sobreturno','primerControl','caja','comentario', 'tipo_turno', 'especialidad', 'otorgado_por', 'msj_enviado','activo', 'google_calendar_event_id', 'pago', 'pago_estado', 'mercadopago_payment_id', 'mercadopago_preference_id', 'mercadopago_refund_id', 'importe_reserva', 'turno_pago_intent_id'];
}
