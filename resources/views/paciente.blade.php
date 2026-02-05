<div class="col-md-16 col-md-offset-0">
	<div class="col-md-5 col-md-offset-0">																													
		<div class="well fondoTransparente well-lg" >																  									
			<h1>Nuevo Paciente</h1>
			<div class="caption">
				<form action="{{ route('pacientesAdd') }}" method="POST">
						
					
					<label class="linputText">Nombre</label>									
					<input id="nombre" name="nombre" type="text" class="form-control" placeholder="Nombre" required value=""/><br>
					
					<label class="linputText">Categoria</label>									
					<input id="apellido" name="apellido" type="text" class="form-control" placeholder="Categoria" required value=""/><br>
					
					<input type="submit" value="Cargar" />									
				</form>															
		</div>							
		</div>			
	</div>
</div>