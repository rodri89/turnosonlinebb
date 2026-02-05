
@extends('turnos_admin/modelo_plantilla_admin')

@section('titulo_header','Lista Negra Mensajes')

@section('body_titulo','')

@section('contenedor')

<div class="row">    
    <div class="col-md-6">        
      <h2> Ingrese el numero que fallo al intentar enviar en whatsapp</h2>                  
        <label for="text" class="col-sm-0 control-label">Numero</label>      
        <input type="text" class="form-control" name="numero"  id="numero"  />
        <br>                    
        <div>
          <button type="button" onclick="registrar()">Registar</button>
          <!--<a href="turno_registrado" class="btn btn-primary">Registrar</a>-->
        </div>        
    </div>      
</div>

<script type="text/javascript">

    function registrar(){    
    var numero = document.getElementById("numero").value;
    
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/registrar_numero_lista_negra',
           data:{numero:numero, _token: '{{csrf_token()}}'},
           success:function(data){                    
              document.getElementById("numero").value = "";
              alert("El numero ha sido ingresado");   
           }
        }); 
    }
</script>
@endsection