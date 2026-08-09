@if($medico->id == 1)
  <ul>              
    <li class="fontColorHeader">Lunes: 08:00 a 12:00</li>      
    <li class="fontColorHeader">Jueves: 15:00 a 19:00</li>     
  </ul>
  <div class="col-md-8 mb-3">
    <h6 class="fontColorHeader">Consultorio: {{ $consultorio->direccion}}</h6>
    <h6 class="fontColorHeader">Telefono {{$consultorio->telefono}}</h6>
  </div>
@else
@if($medico->id == 36)  
  <ul>              
    <li class="fontColorHeader">Lunes: 12:00 a 16:00</li>          
  </ul>
  <div class="col-md-8 mb-3">
    <h6 class="fontColorHeader">Consultorio: Fournier 927 - Punta Alta</h6>
    <h6 class="fontColorHeader">Telefono {{$consultorio->telefono}}</h6>
  </div>

  <ul>              
    <li class="fontColorHeader">Viernes: 15:00 a 20:00</li>          
  </ul>
  <div class="col-md-8 mb-3">
    <h6 class="fontColorHeader">Consultorio: {{ $consultorio->direccion}}</h6>
    <h6 class="fontColorHeader">Telefono {{$consultorio->telefono}}</h6>
  </div>  
@else
@if($medico->id == 2)
  @if($tipoTurno == 24)
    <ul>              
      <li class="fontColorHeader">Jueves: 09:30 a 11:30</li>  
      <li class="fontColorHeader">Viernes: 11:00 a 15:00</li>          
    </ul>
    <div class="col-md-8 mb-3">
      <h6 class="fontColorHeader">Consultorio: Florida 700</h6>
      <h6 class="fontColorHeader">Telefono 2914165011</h6>
    </div>
  @else
    <ul>              
    <li class="fontColorHeader">Lunes: 15:30 a 19:00</li>      
    <li class="fontColorHeader">Miercoles: 09:30 a 16:00</li> 
  </ul>
  <div class="col-md-8 mb-3">
    <h6 class="fontColorHeader">Consultorio: {{ $consultorio->direccion}}</h6>
    <h6 class="fontColorHeader">Telefono {{$consultorio->telefono}}</h6>
  </div>
  @endif
@else
@if($medico->id == 29)
  <ul>              
    <li class="fontColorHeader">Martes: 08:00 a 12:00</li>      
    <li class="fontColorHeader">Jueves: 15:00 a 19:00</li> 
  </ul>
  <div class="col-md-8 mb-3">
    <h6 class="fontColorHeader">Consultorio: {{ $consultorio->direccion}}</h6>
    <h6 class="fontColorHeader">Telefono {{$consultorio->telefono}}</h6>
  </div>
@else

@if($medico->id == 30)
  <ul>              
    <li class="fontColorHeader">Lunes: 14:00 a 18:00</li>      
    <li class="fontColorHeader">Miercoles: 09:00 a 13:00</li> 
  </ul>
  <div class="col-md-8 mb-3">
    <h6 class="fontColorHeader">Consultorio: {{ $consultorio->direccion}}</h6>
    <h6 class="fontColorHeader">Telefono {{$consultorio->telefono}}</h6>
  </div>
@else

@if($medico->id == 9)
  <ul>              
    <li class="fontColorHeader">Lunes: 17:00 a 19:00</li>      
    <li class="fontColorHeader">Miercoles: 14:00 a 19:00</li> 
  </ul>
  <div class="col-md-8 mb-3">
    <h6 class="fontColorHeader">Consultorio: {{ $consultorio->direccion}}</h6>
    <h6 class="fontColorHeader">Telefono {{$consultorio->telefono}}</h6>
  </div>
@else

@if($medico->id == 24)
  <ul>              
    <li class="fontColorHeader">Lunes: 17:20 a 19:40</li>
    <li class="fontColorHeader">Viernes: 14:00 a 16:00</li>      
  </ul>
  <div class="col-md-8 mb-3">
    <h6 class="fontColorHeader">Consultorio: Luiggi 463 </h6>    
    <h6 class="fontColorHeader">Telefono 2914717327 </h6>
  </div>

  <ul>              
    <li class="fontColorHeader letrasrojo">Miercoles: 18:00 a 19:45</li>      
  </ul>
  <div class="col-md-8 mb-3">
    <h6 class="fontColorHeader letrasrojo">Consultorio: Blandengues 505 </h6>
    <h6 class="fontColorHeader letrasrojo">Telefono 2914717327 </h6>
  </div>
@else

@if($medico->id == 25)
  <ul>              
    <li class="fontColorHeader">Miercoles: 08:00 a 10:00</li>      
  </ul>
  <div class="col-md-8 mb-3">
    <h6 class="fontColorHeader">Consultorio: Blandengues 505</h6>
      <h6 class="fontColorHeader">Telefono 2914717327</h6>
  </div>
@else

@if($medico->id == 11)
  @if($tipoTurno == 1)
    <ul>              
      <li class="fontColorHeader">Horarios para el mes de Enero</li>      
      <li class="fontColorHeader">Lunes: 11:00 a 13:45 - 16:00 a 19:00</li>      
      <li class="fontColorHeader">Martes: 08:15 a 14:15 - 16:00 a 19:00</li>      
      <li class="fontColorHeader">Miercoles: 08:15 a 13:30 - 16:00 a 19:45</li>      
      <li class="fontColorHeader">Jueves: 08:15 a 13:30</li>      
    </ul>
    <div class="col-md-8 mb-3">
      <h6 class="fontColorHeader">Consultorio: {{$consultorio->direccion}}</h6>        
      <h6 class="fontColorHeader">Telefono {{$consultorio->telefono}}</h6>
    </div>  
    @else
      <ul>              
        <li class="fontColorHeader">Viernes: 09:00 a 11:00</li>      
      </ul>
      <div class="col-md-8 mb-3">        
        <h6 class="fontColorHeader">Telefono {{$consultorio->telefono}}</h6>
      </div>
  @endif
@else

@if($medico->id == 43)
  <ul>              
    <li class="fontColorHeader">Martes: 15:00 a 19:00</li>
    <li class="fontColorHeader">Jueves: 15:00 a 19:00</li>      
  </ul>
  <div class="col-md-8 mb-3">
    <h6 class="fontColorHeader">Consultorio: Gimnasio EFI - Jorge Walsh 31 </h6>    
    <h6 class="fontColorHeader">Telefono 2914236530 </h6>
  </div>
  <p>---------------------------------------</p>
  <ul>              
    <li class="fontColorHeader letrasrojo">Lunes: 15:00 a 19:00</li>          
    <li class="fontColorHeader letrasrojo">Miercoles: 08:30 a 12:30</li>  
    <li class="fontColorHeader letrasrojo">Jueves: 08:00 a 12:30</li>          
    <li class="fontColorHeader letrasrojo">Viernes: 08:00 a 12:30</li>      
  </ul>
  <div class="col-md-8 mb-3">
    <h6 class="fontColorHeader letrasrojo">Consultorio: Ing. Luiggi 463 </h6>
    <h6 class="fontColorHeader letrasrojo">Telefono 2914236530 </h6>
  </div>
@else

<!-- Erica Pacheco -->
@if($medico->id == 12)
    <?php $dir_consul = $consultorio->direccion; $tel_consul =  $consultorio->telefono;?>
    
    <div class="col-md-8 mb-3">
      <h6 class="fontColorHeader">Consultorio: Blandengues 505</h6>
      <h6 class="fontColorHeader">Telefono 2914717327</h6>
    </div>
    <ul>              
      <li class="fontColorHeader">Miercoles: 14:00 a 17:00</li>      
    </ul>
    <?php $dir_consul = 'Garibaldi 44'; $tel_consul =  '4814538';?>
    
    <div class="col-md-8 mb-3">
      <h6 class="fontColorHeader letrasrojo">Consultorio: {{ $dir_consul}}</h6>
      <h6 class="fontColorHeader letrasrojo">Telefono {{$tel_consul}}</h6>
    </div>
    <ul>                    
      <li class="fontColorHeader letrasrojo">Viernes: 14:00 a 16:30</li>      
    </ul>
  <!--<ul>
    <?php $dir_consul = $consultorio->direccion; $tel_consul =  $consultorio->telefono;?>
    @foreach($diasAtencion as $valor)            
    <li class="fontColorHeader">{{$valor}}</li>
    <div class="col-md-8 mb-3">
      <h6 class="fontColorHeader">Consultorio en {{$dir_consul}}</h6>
      <h6 class="fontColorHeader">Telefono {{$tel_consul}}</h6>
    </div>
    <?php $dir_consul = 'Garibaldi 44'; $tel_consul =  '4814538';?>
    @endforeach
  </ul> -->                                                  
@else
  
    @if($medico->id == 13) <!-- Lucas Sosa -->    
      @if($tipoTurno == 1)
      <p class="fontColorHeader"><b>Turnos Presenciales:</b></p>
      <ul>        
        <li class="fontColorHeader">Lunes: 15:40 a 18:40</li>
        <li class="fontColorHeader">Martes: 13:40 a 16:40</li>        
        <li class="fontColorHeader">Jueves: 13:40 a 16:40</li>
      </ul>
      <div class="col-md-8 mb-3">
        <h6 class="fontColorHeader">Consultorio: {{$consultorio->direccion}}</h6>
        <h6 class="fontColorHeader">Telefono {{$consultorio->telefono}}</h6>
      </div>
      @else
        <p class="fontColorHeader"><b>Turnos Presenciales:</b></p>
        <ul>        
          <li class="fontColorHeader">Martes: 12:00 a 13:30</li>          
        </ul>
        <div class="col-md-8 mb-3">
          <h6 class="fontColorHeader">Consultorio: {{$consultorio->direccion}}</h6>
          <h6 class="fontColorHeader">Telefono {{$consultorio->telefono}}</h6>
        </div>
      @endif
      
    @else
      <ul>
        @foreach($diasAtencion as $valor)            
        <li class="fontColorHeader">{{$valor}}</li>
        @endforeach
      </ul>          
      @if($esVideollamada == 0)
      <div class="col-md-8 mb-3">
        @if($tipoTurno == 1)
          <h6 class="fontColorHeader">Consultorio: {{$consultorio->direccion}}</h6>
        @endif
        <h6 class="fontColorHeader">Telefono {{$consultorio->telefono}}</h6>
      </div>
      @endif
    @endif
  @endif
@endif
@endif
@endif
@endif
@endif
@endif
@endif
@endif
@endif
@endif
