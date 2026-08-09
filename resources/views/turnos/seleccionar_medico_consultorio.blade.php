@extends('turnos/modelo_plantilla')

@section('titulo_header','Seleccionar Médico')

@section('descripcion_header')
  Médicos que atienden en {{ $consultorio->nombre }} ({{ $consultorio->direccion }}).
@endsection

@section('body_titulo','Seleccione el médico en este consultorio.')

@section('contenedor')

<div class="row">
  @foreach($medicos as $medico)
    @if($medico->castigo_automatico == 1)
      <div class="text-center col-md-3 mb-4">
        @if($medico->id == 44)
        <form method="POST" action="https://patient.consultoriomovil.net/patient/web/carolinaheinrich/">
        @else
          <form method="POST" action="{{ route('ingresarpaciente') }}">
        @endif        
          @csrf
          <input type="hidden" name="medico_id" value="{{ $medico->id }}" />
          {{-- Usamos la especialidad del médico para mantener el flujo actual --}}
          <input type="hidden" name="especialidad_id" value="{{ $medico->especialidad }}" />
          <input type="hidden" name="especialidad_nombre_flujo" value="{{ $medico->e_nombre ?? '' }}" />

          <button type="submit" class="btn btn-primary-outline img-responsive img_home">
            <img class="card-img-top" src="{{ asset('images/medicos/'.$medico->foto) }}" alt="">
          </button>
          <div class="card-body">
            <h6 class="fontImage" align="center">{{ $medico->m_apellido }}, <br> {{ $medico->m_nombre }}</h6>
            <h6 class="fontImage fontColorHeader" align="center"><b>{{ $medico->e_nombre }}</b></h6>
          </div>
        </form>
      </div>
    @endif
  @endforeach
</div>

@endsection

