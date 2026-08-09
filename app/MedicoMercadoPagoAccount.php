<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedicoMercadoPagoAccount extends Model
{
    protected $table = 'medico_mercadopago_accounts';

    protected $fillable = [
        'medico_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'collector_id',
        'mp_user_id',
        'cobro_activo',
        'cobro_desde',
        'mensaje_aviso_cobro_titulo',
        'mensaje_aviso_cobro',
        'reembolso_cancelacion_activo',
        'reembolso_cancelacion_dias_previos',
        'secretaria_puede_reembolso',
        'secretaria_puede_ver_panel_mp',
        'importe_reserva',
        'linked_at',
        'mode',
    ];

    protected $dates = [
        'expires_at',
        'linked_at',
        'cobro_desde',
    ];

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }

    public function isLinked()
    {
        return !empty($this->access_token) && !empty($this->collector_id);
    }

    public function requiresPayment()
    {
        return (int) $this->cobro_activo === 1 && $this->isLinked();
    }

    public function hasCobroReservaConfigurado()
    {
        return (int) $this->cobro_activo === 1 && $this->isLinked();
    }

    public function tieneReembolsoCancelacionActivo()
    {
        return (int) $this->reembolso_cancelacion_activo === 1
            && $this->reembolso_cancelacion_dias_previos !== null
            && (int) $this->reembolso_cancelacion_dias_previos >= 1;
    }

    public function secretariaPuedeReembolso()
    {
        return (int) $this->secretaria_puede_reembolso === 1;
    }

    public function secretariaPuedeVerPanelMp()
    {
        return (int) $this->secretaria_puede_ver_panel_mp === 1;
    }

    /**
     * @param string|\DateTimeInterface $fechaTurno Fecha del turno (Y-m-d)
     */
    public function permiteReembolsoCancelacionPaciente($fechaTurno)
    {
        if (!$this->tieneReembolsoCancelacionActivo()) {
            return false;
        }

        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $hoy = new \DateTime(date('Y-m-d'));
        $fechaTurnoDt = $fechaTurno instanceof \DateTimeInterface
            ? new \DateTime($fechaTurno->format('Y-m-d'))
            : new \DateTime((string) $fechaTurno);

        $diasAntes = (int) $hoy->diff($fechaTurnoDt)->days;
        if ($fechaTurnoDt < $hoy) {
            return false;
        }

        return $diasAntes >= (int) $this->reembolso_cancelacion_dias_previos;
    }
}
