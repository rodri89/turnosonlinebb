@extends('turnos_admin/modelo_plantilla_admin')

@section('title', 'Mercado Pago — Configuración global')

@section('titulo_header', 'Mercado Pago Marketplace')

@section('descripcion_header', 'Configuración global de Split Payments para cobro de turnos.')

@section('body_titulo', 'Configuración global')

@section('contenedor')
<p class="text-muted mb-4">Credenciales de la aplicación integradora. Los médicos vinculan su cuenta desde su panel (Pagos / Mercado Pago).</p>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 pl-3">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="alert alert-info">
    <strong>Redirect URI</strong> (registrar en Mercado Pago Developers):<br>
    <code>{{ $redirectUri }}</code>
</div>

<form method="POST" action="{{ route('admin.mercadopago.settings.update') }}">
    @csrf
    <div class="form-row">
        <div class="form-group col-md-4">
            <label>Comisión plataforma (%)</label>
            <input type="number" step="0.01" min="0" max="100" class="form-control" name="platform_commission_percent"
                value="{{ old('platform_commission_percent', $settings->platform_commission_percent ?? 5) }}">
        </div>
        <div class="form-group col-md-4">
            <label>Modo</label>
            <select class="form-control" name="mode">
                <option value="sandbox" {{ ($settings->mode ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                <option value="production" {{ ($settings->mode ?? '') === 'production' ? 'selected' : '' }}>Producción</option>
            </select>
        </div>
        <div class="form-group col-md-4">
            <label>Fallback comisión MP (% del bruto)</label>
            <input type="number" step="0.01" min="0" max="100" class="form-control" name="mp_commission_fallback_percent"
                value="{{ old('mp_commission_fallback_percent', $settings->mp_commission_fallback_percent ?? 0.8) }}">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label>APP ID (Client ID OAuth)</label>
            <input type="text" class="form-control" name="mp_app_id"
                value="{{ old('mp_app_id', $settings->mp_app_id ?? '') }}">
        </div>
        <div class="form-group col-md-6">
            <label>Client Secret OAuth</label>
            <input type="password" class="form-control" name="mp_client_secret" placeholder="Dejar vacío para no cambiar">
        </div>
    </div>

    <div class="form-group">
        <label>Access token integrador</label>
        <input type="password" class="form-control" name="integrator_access_token" placeholder="Dejar vacío para no cambiar">
    </div>

    <div class="form-group">
        <label>Public key integrador</label>
        <input type="text" class="form-control" name="integrator_public_key"
            value="{{ old('integrator_public_key', $settings->integrator_public_key ?? '') }}">
    </div>

    <div class="form-group">
        <label>Descripción en checkout</label>
        <input type="text" class="form-control" name="checkout_description"
            value="{{ old('checkout_description', $settings->checkout_description ?? 'Reserva de turno online') }}">
    </div>

    <button type="submit" class="btn btn-primary">Guardar configuración</button>
</form>
@endsection
