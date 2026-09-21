
@extends('turnos/modelo_plantilla')

@section('titulo_header','Seleccionar Especialidad')

@section('headerContainer')
  <div class="col-md-2 mb-5">
@endsection

@section('descripcion_header','En esta sección podrá elegir la especialidad que desees')

@section('body_titulo','Click en la especialidad para continuar.')

@section('contenedor')

<div class="especialidad-grid">
  @foreach($especialidades as $especialidad)
    @if($especialidad->activo == 1)
      @php
        $colorValido = preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', trim($especialidad->color ?? ''))
            ? trim($especialidad->color)
            : '#303F9F';
      @endphp
      <form method="POST" action="{{ route('seleccionarmedicoespecialidad') }}">
          @csrf
          <input type="hidden" name="especialidad_id" value="{{$especialidad->id}}"  />
          <button type="submit" class="especialidad-card" style="--especialidad-color: {{ $colorValido }}">
            <span class="especialidad-card__nombre">{{ $especialidad->nombre }}</span>
          </button>
      </form>
    @endif
  @endforeach
</div>

<!-- Botón flotante "Mis Turnos" solo para móviles -->
<div class="boton-flotante-mis-turnos">
  <a href="/mis_turnos" class="flotante-mis-turnos rodri_button">Mis Turnos</a>
</div>

<style>
  .especialidad-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 16px;
    padding: 4px 0 24px;
  }

  .especialidad-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 100px;
    background: #fff;
    border: 2px solid var(--especialidad-color, #303F9F);
    border-radius: 18px;
    padding: 16px 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    cursor: pointer;
  }

  .especialidad-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 22px rgba(0, 0, 0, 0.12);
  }

  .especialidad-card:active {
    transform: scale(0.96);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.10);
  }

  .especialidad-card:focus-visible {
    outline: 2px solid #00968833;
    outline-offset: 3px;
  }

  .especialidad-card__nombre {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    text-align: center;
    line-height: 1.3;
  }

  @media (min-width: 768px) {
    .especialidad-grid {
      grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
      gap: 24px;
    }

    .especialidad-card {
      min-height: 120px;
    }

    .especialidad-card__nombre {
      font-size: 18px;
    }
  }

  @media (prefers-reduced-motion: reduce) {
    .especialidad-card {
      transition: none;
    }
  }

  /* Botón flotante solo visible en móviles */
  .boton-flotante-mis-turnos {
    display: none;
  }

  @media (max-width: 768px) {
    .boton-flotante-mis-turnos {
      display: block;
    }

    .flotante-mis-turnos {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 35px;
      font-size: 18px;
      width: 120px;
      position: fixed;
      bottom: 25px;
      right: 25px;
      z-index: 1000;
      text-decoration: none;
      text-align: center;
      line-height: 30px;
    }

    .flotante-mis-turnos:hover {
      text-decoration: none;
      color: #FFF;
    }
  }
</style>

@endsection
