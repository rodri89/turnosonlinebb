<?php

/*

Route::get('/login', function () {
  return view('turnos/login');
});

Route::post('login_check','AuthController@login')->name('logincheck');

*/
Route::get('/OneSignalSDKWorker.js', function() {
    return response()->file(public_path('OneSignalSDKWorker.js'));
});

Route::get('/', function () {	
	return View('turnos.home');   
});

Route::get('/pagina_mantenimiento', function () {	
	return View('turnos.pagina_mantenimiento'); 
  //return view('turnos/datos_paciente');
});

Route::get('/homes', function () {	
	return View('turnos.home'); 
  //return view('turnos/datos_paciente');
});

Route::get('/portada', function () {	
	return View('turnos.home'); 
  //return view('turnos/datos_paciente');
})->name('portada');

Route::get('/selec_especialidad', function () {
	$especialidades= DB::table('especialidads')->where('especialidads.activo', 1)->orderBy('especialidads.nombre')->get();
	if($especialidades->count()>1){
		return View('turnos.seleccionar_especialidad')->with('especialidades',$especialidades);    	        
	} else {
		$medicos = DB::select('select * from medicos where especialidad = ? and activo=1', [$especialidades[0]->id]);              
        return view('turnos.seleccionar_medico')->with('medicos',$medicos);
	}
  //return view('turnos/datos_paciente');
})->name("selecespecialidad");

Route::get('/seleccionar_consultorio', function () {
	$consultorios= DB::table('consultorios')->where('activo',1)->get();
	
	return View('turnos.seleccionar_consultorio')->with('consultorios',$consultorios);    	        
	
  //return view('turnos/datos_paciente');
})->name("selecconsultorio");

Route::get('/seleccionar_medico', function () {
	$medicos= DB::table('medicos')
					->select('medicos.id', 'medicos.foto', 'medicos.castigo_automatico', 'medicos.especialidad', 'medicos.nombre as m_nombre', 'medicos.apellido as m_apellido', 'especialidads.nombre as e_nombre','especialidads.color','medicos.activo')
					->join('especialidads','especialidads.id','=','medicos.especialidad')
					->where('medicos.activo',1)
					->get();
	
	return View('turnos.seleccionar_medico')->with('medicos',$medicos);    	        
	
  //return view('turnos/datos_paciente');
})->name("seleccionarmedicoget");

Route::get('test','PacienteController@test')->name('test');
Route::get('send_notifications','TurnoController@sendNotifications')->name('send_notifications');
Route::get('test_fcm_notification/{paciente_id}','TurnoController@testFcmNotification')->name('test_fcm_notification');

Route::post('save_one_signal_id','PacienteController@saveOneSignalId')->name('saveonesignalid');
Route::post('save_fcm_token','PacienteController@saveFcmToken')->name('savefcmtoken');

Route::group(['middleware' => ['auth', 'usuarioAdmin']], function () {

	Route::get('admin_obra_social','EspecialidadController@adminObraSocial');		

	Route::post('alta_obra_social','EspecialidadController@altaObraSocial')->name('altaobrasocial');		

	Route::post('guardar_mp_config','VideollamadaController@guardarKeySecret')->name('guardarmpconfig');	

	Route::post('admin_modulo_medico','MedicoController@adminModuloMedico');
	
	Route::post('guardar_ventana_dias','MedicoController@guardarVentanaDias');
	
	Route::get('admin_feriados','TurnoController@adminFeriados')->name('adminferiados');

	Route::post('alta_feriados','TurnoController@altaFeriados')->name('altaferiados');

	Route::get('admin_extras','TurnoController@adminExtras')->name('adminextras');

	Route::post('ejecutar_extras','TurnoController@ejecutarExtras')->name('ejecutarextras');

	Route::post('ejecutar_extras_2','TurnoController@ejecutarExtras2')->name('ejecutarextras2');

	Route::post('eliminar_fecha_feriado','TurnoController@eliminarFechaFeriado')->name('eliminarfechaferiado');

	Route::post('actualizar_foto','MedicoController@actualizarFoto')->name('actualizarFoto');

	Route::post('actualizar_foto_consultorio','ConsultorioController@actualizarFotoConsultorio')->name('actualizarFotoConsultorio');
	
	Route::get('/admin_especialidad', function () {
	  return view('turnos_admin/admin_especialidad');
	});

	Route::post('/admin_medico','MedicoController@adminMedico')->name('adminmedico');
	
	Route::post('alta_medico','MedicoController@store')->name('altamedico');
	
	Route::post('actualizar_medico','MedicoController@actualizarMedico')->name('actualizarmedico');

	Route::get('/admin_consultorios', function () {
	  $consultorios = DB::table('consultorios')->where('activo',1)->get();
	  return view('turnos_admin/admin_consultorios')->with('consultorios', $consultorios);
	});

	Route::post('alta_consultorio','ConsultorioController@store')->name('altaconsultorio');
	
	
	Route::get('alta_secretarias', 'SecretariaController@AltaSecretariaForm');
	Route::post('vincular_secretaria_consultorio', 'SecretariaController@vincularSecretariaConsultorio')->name('vincularsecretariaconsultorio');
	

	Route::get('/admin_turnos','TurnoController@adminTurnosIndex')->name('adminTurnosIndex');
	Route::post('crear_turnos_dia','TurnoController@storeTurnosDia')->name('crearturnosdia');
	Route::post('crear_turnos_dia_dh','TurnoController@storeTurnosDiaDh')->name('crearturnosdiadh');


	Route::get('/admin_especialidad', function () {
	  return view('turnos_admin/admin_especialidad');
	});

	Route::post('alta_especialidad','EspecialidadController@store')->name('altaespecialidad');

	Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
	//Route::post('register', 'Auth\RegisterController@register');
	Route::post('/registrar', 'Auth\RegisterController@registrar')->name('registrar');	
	
	Route::get('/admin_show_medicos', 'MedicoController@adminShowMedicos')->name('adminshowmedicos');
	
	Route::get('/home', 'HomeController@index')->name('home');
});

Route::group(['middleware' => ['auth', 'usuarioMedico']], function () {

	Route::get('admin_horariosm','MedicoController@adminHorarios')->name('adminhorariosm');

	Route::post('/medico_desbloquear_paciente', 'MedicoController@medicoDesbloquearPaciente')->name('medicodesbloquearpaciente');	

	Route::post('/medico_bloquear_paciente', 'MedicoController@medicoBloquearPaciente')->name('medicobloquearpaciente');

	Route::post('/medico_eliminar_paciente', 'MedicoController@medicoEliminarPaciente')->name('medicoeliminarpaciente');

	Route::post('/agregar_sobreturno_videollamada', 'VideollamadaController@agregarSobreturnoVideollamada')->name('agregarsobreturnovideollamada');

	Route::post('/medico_actualizar_cargado', 'VideollamadaController@medicoActualizarCargado')->name('medicoactualizarcargado');
	
	Route::post('/historial_videollamadas', 'VideollamadaController@historialVideollamadas')->name('historialvideollamadas');

	Route::get('/historial-videollamada-list', 'VideollamadaController@historialVideollamadaList')->name('historialvideollamadalist');
	
	Route::post('/cargar_foto_receta', 'RecetaController@cargarFotoReceta')->name('cargarfotoreceta');

	Route::get('/medico_home', 'MedicoController@selectConsultorio')->name('medicohome');
	
	Route::post('medico_actualizar_listado_videollamadas','VideollamadaController@medicoActualizarListadoVideollamadas');					

	Route::post('medico_actualizar_listado_pacientes','MedicoController@medicoActualizarListadoPacientes');				
	
	Route::post('medico_cancelar_turno_videollamadas','VideollamadaController@medicoCancelarTurnoVideollamadas');				
	
	Route::post('medico_cancelar_turno_videollamadas_id','VideollamadaController@medicoCancelarTurnoVideollamadasId');				

	Route::post('medico_descancelar_turno_videollamadas','VideollamadaController@medicoDescancelarTurnoVideollamadas');
					
	Route::post('medico_enviar_link_videollamadas','VideollamadaController@medicoEnviarLinkVideollamadas');

	Route::get('/medico_home1', function () {
	  return view('turnos_admin_medico/home');
	});
	Route::post('medico_buscar_pacientes','MedicoController@listadoBuscarPacientes')->name('medicobuscarpacientes');	
	Route::get('medico-users-list','MedicoController@usersList');
	Route::get('modal_buscar_pacientes_list','MedicoController@modalBuscarPacientesList');
							

	Route::post('medico_asignar_turnos','MedicoController@medicoAsignarTurnos')->name('medicoasignarturnos');

	Route::post('medico_asignar_turnos_peg','MedicoController@medicoAsignarTurnosPeg')->name('medicoasignarturnospeg');

	Route::post('medico_consultar_paciente','MedicoController@consultarPaciente')->name('medicoconsultarpaciente');		

	Route::post('medico_actualizar_asignar_turnos','MedicoController@actualizarAsignarTurnos');		
				
	Route::post('medico_asignar_sobreturnos_peg','MedicoController@medicoAsignarsobreTurnosPeg')->name('medicoasignarsobreturnospeg');	

	// Route::post('medico_registrar_paciente','MedicoController@registrarPaciente')->name('medicoregistrarpaciente');	

	Route::post('medico_registrar_asignar_turno','MedicoController@registrarAsignarTurno')->name('medicoregistrarasignarturno');
	
	Route::post('medico_registrar_asignar_turno_doble','MedicoController@registrarAsignarTurnoDoble')->name('medicoregistrarasignarturnodoble');
	Route::post('medico_ver_semana','MedicoController@mostrarSemana')->name('medicomostrarsemana');			

	Route::post('medico_actualizar_paciente','MedicoController@medicoActualizarPaciente')->name('medicoactualizarpaciente');				
	Route::post('/medico_recetas','RecetaController@medicoRecetas')->name('medicorecetas');		

	Route::get('/medico_recetas', function () {
		$user = Auth::user()->id;
	  	$medico = DB::table('medicos')->where('medicos.user_id', $user)->first();
	  	$consultorio = $medico->consultorio;
	  	$recetas = DB::table('recetas')						
					->join('pacientes','pacientes.id','=','recetas.paciente')					
					->join('receta_estados','recetas.estado','=','receta_estados.id')					
					->select('pacientes.id as pac_id','pacientes.nombre','pacientes.apellido','pacientes.telefono','pacientes.dni','pacientes.mail','recetas.id as rec_id','recetas.motivo','receta_estados.estado', 'recetas.created_at as fecha' )
					->where('recetas.consultorio',$consultorio)
					->where('recetas.estado','!=',5)					
					->where('recetas.activo',1)
					->where('pacientes.activo',1)
					->where('receta_estados.activo',1)
					->orderby('recetas.created_at')
					->get();
		return  View('turnos_admin_medico.admin_recetas')
		    	->with('consultorio',$consultorio)
		    	->with('recetas',$recetas);   
	})->name('medicorecetas2');	

	Route::post('medico_actualizar_listado_ver_semana','MedicoController@actualizarListadoVerSemana')->name('medicoactualizarlistadoversemana');		

	Route::post('medico_registrar_turno_agenda_semanal','MedicoController@registrarTurnoAgendaSemanal')->name('medicoregistrarturnoagendasemanal');		
				
	Route::post('medico_registrar_turno_paciente_agenda_semanal','MedicoController@medicoRegistrarTurnoPacienteAgendaSemanal')->name('medicoregistrarturnopacienteagendasemanal');		

	Route::post('medico_bloquear_turnos','MedicoController@mostrarSemana')->name('medicobloquearturnos');

	Route::post('medico_borrar_turno_agenda_semanal','MedicoController@borrarTurnoAgendaSemanal')->name('medicoborrarturnoagendasemanal');

	Route::post('medico_bloquear_dia_agenda_semanal','MedicoController@bloquarDiaAgendaSemanal')->name('medicobloqueardiaagendasemanal');

	Route::post('medico_admin_sobreturnos','MedicoController@adminSobreturnos')->name('medicoadminsobreturnos');		

	Route::post('medico_registrar_sobreturno','MedicoController@registrarSobreturno')->name('medicoregistrarsobreturno');

	Route::post('medico_nuevo_paciente','MedicoController@nuevoPaciente')->name('mediconuevopaciente');					

	Route::post('medico_admin_pacientes','MedicoController@adminPacientes')->name('medicoadminpacientes');	

	Route::post('medico_activar_paciente','MedicoController@activarPaciente')->name('medicoactivarpaciente');		

	Route::post('medico_registrar_asistencia','MedicoController@registrarAsistencia')->name('medicoregistrarasistencia');	

	Route::post('update_caja_listado_pacientes','MedicoController@updateCaja')->name('updatecajalistadopacientes');			

	Route::post('update_comentario_listado_pacientes','MedicoController@updateComentario')->name('updatecomentariolistadopacientes');	

	Route::post('update_comentario_listado_pacientes_peg','MedicoController@updateComentarioPeg')->name('updatecomentariolistadopacientespeg');	

	Route::get('medico_configuracion','MedicoController@configuracion')->name('medicoconfiguracion');	
	
	Route::post('actualizar_valor_consulta','MedicoController@actualizarValorConsulta')->name('actualizarvalorconsulta');	
	
	Route::post('medico_config_primer_control_actualizar','MedicoController@configPrimerControlActualizar')->name('configPrimerControlActualizar');	

	Route::post('medico_cancelar_sobreturno','MedicoController@medicoCancelarSobreturno')->name('cancelarsobreturno');	
	
	Route::post('medico_actualizar_listado_ver_semana_peg','MedicoController@medicoActualizarListadoVerSemanaPeg')->name('medicoactualizarlistadoversemanapeg');	
		
	Route::post('medico_cancelar_asignar_turnos','MedicoController@medicoCancelarAsignarTurnos')->name('medicocancelarasignarturnos');	

	Route::get('receta-listado-medico','RecetaController@recetaListado');						

	Route::post('/medico_ver_receta','RecetaController@verReceta')->name('medicoverreceta');	

	Route::post('/medico_actualizar_estado_receta','RecetaController@actualizarEstadoReceta')->name('medicoactualizarestadoreceta');	

	Route::post('/medico_videollamadas', 'VideollamadaController@medicoVideollamadas')->name('medicovideollamadas');
	
	Route::post('guardar_link_videollamada', 'VideollamadaController@guardarLinkVideollamada')->name('guardarlinkvideollamada');
	
	Route::post('actualizar_config_videollamada', 'VideollamadaController@actualizarConfigVideollamada')->name('actualizarconfigvideollamada');
	
	Route::post('medico_validar_pagos','MedicoController@medicoValidarPagos')->name('medicovalidarpagos');	
	Route::get('medico-validar-pagos-list/{opcion}','MedicoController@validarPagosList');	
	Route::post('medico_accion_validar_pago','MedicoController@medicoAccionValidarPago')->name('medicoaccionvalidarpago');	
	
	Route::get('medico-obras-sociales-list','MedicoController@medicoObrasSocialesList')->name('medicoobrassocialeslist');	
	
	Route::post('medico_obra_social_estado','MedicoController@medicoObraSocialEstado')->name('medicoobrasocialestado');	
	
	Route::post('medico_admin_obra_social','MedicoController@medicoAdminObraSocial')->name('medicoadminobrasocial');	
	
	Route::post('activar_obras_sociales','MedicoController@activarObrasSociales')->name('activarobrassociales');	
	
	Route::post('desactivar_obras_sociales','MedicoController@desactivarObrasSociales')->name('desactivarobrassociales');	

	Route::post('registrar_asistencia_pegm','MedicoController@registrarAsistenciaPeg')->name('registrarasistenciapegm');
				
	Route::post('check_recetas_pendientes','MedicoController@checkRecetasPendientes')->name('checkrecetaspendientes');

	Route::post('pacientes_historial_listado','MedicoController@pacientesHistorialListado')->name('pacienteshistoriallistado');
	Route::get('historial_pacientes_list_m','MedicoController@historialPacientesList');
	Route::post('medico_obra_social_actualizar_importe','MedicoController@medicoObraSocialActualizarImporte')->name('medicoobrasocialactualizarimporte');	
});


Route::group(['middleware' => ['auth', 'usuarioSecretaria']], function () {

	Route::post('check_recetas_pendientes_secretaria','SecretariaController@checkRecetasPendientesSecretaria')->name('checkrecetaspendientessecretaria');
	
	Route::post('admin_seleccionar_consultorio','SecretariaController@seleccionarConsultorio')->name('seleccionarconsultorio');	

	Route::get('/secretaria_home', function () {	  
	  return view('turnos_admin_secretaria/home');
	})->name('secretaria_home');

	Route::post('mostrar_medico_consultorio','SecretariaController@showMedicosConsultorio')->name('mostrarmedicoconsultorio');	
	
	Route::post('secretaria_asignar_turnos_peg','SecretariaController@secretariaAsignarTurnosPeg')->name('secretariaasignarturnospeg');	
	Route::post('secretaria_asignar_sobreturnos_peg','SecretariaController@secretariaAsignarSobreturnosPeg')->name('secretariaasignarsobreturnospeg');		
	
	Route::post('update_comentario_listado_pacientes_peg','SecretariaController@updateComentarioPeg')->name('updatecomentariolistadopacientespeg');	

	Route::post('borrar_turno_agenda_semanal','SecretariaController@borrarTurnoAgendaSemanal')->name('borrarturnoagendasemanal');
	Route::post('bloquear_dia_agenda_semanal','SecretariaController@bloquarDiaAgendaSemanal')->name('bloqueardiaagendasemanal');
	Route::post('secretaria_bloquear_turnos','SecretariaController@mostrarSemana')->name('secretariabloquearturnos');
	Route::post('secretaria_asignar_turnos','SecretariaController@secretariaAsignarTurnos')->name('secretariaasignarturnos');		
	Route::post('secretaria_admin_sobreturnos','SecretariaController@adminSobreturnos')->name('secretariaadminsobreturnos');		
	Route::post('turnos_asignados_dia','SecretariaController@turnosAsignadosDia')->name('turnosasignadosdia');

	Route::post('secretaria_registrar_turno_paciente_agenda_semanal','SecretariaController@secretariaRegistrarTurnoPacienteAgendaSemanal')->name('secretariaregistrarturnopacienteagendasemanal');		

	Route::post('consultar_paciente','SecretariaController@consultarPaciente')->name('consultarpaciente');	
	
	Route::post('actualizar_listado_pacientes','SecretariaController@actualizarListadoPacientes');				

	Route::post('registrar_asistencia','SecretariaController@registrarAsistencia')->name('registrarasistencia');

	Route::post('registrar_asistencia_peg','SecretariaController@registrarAsistenciaPeg')->name('registrarasistenciapeg');			

	Route::post('registrar_sobreturno','SecretariaController@registrarSobreturno')->name('registrarsobreturno');

	Route::post('registrar_asignar_turno','SecretariaController@registrarAsignarTurno')->name('registrarasignarturno');

	Route::post('registrar_asignar_turno_doble','SecretariaController@registrarAsignarTurnoDoble')->name('registrarasignarturnodoble');

	Route::post('registrar_paciente','SecretariaController@registrarPaciente')->name('registrarpaciente');	

	Route::post('actualizar_asignar_turnos','SecretariaController@actualizarAsignarTurnos');					

	Route::post('admin_pacientes','SecretariaController@adminPacientes')->name('adminpacientes');

	Route::post('activar_paciente','SecretariaController@activarPaciente')->name('activarpaciente');		

	Route::post('nuevo_paciente','SecretariaController@nuevoPaciente')->name('nuevopaciente');					

//	Route::post('alta_paciente_secretaria','SecretariaController@altaPacienteSecretaria')->name('altapacientesecretaria');					
	Route::post('actualizar_paciente','SecretariaController@actualizarPaciente')->name('actualizarpaciente');
	
	Route::post('listado_pacientes','SecretariaController@listadoPacientes')->name('listadopacientes');	
	Route::get('users-list','SecretariaController@usersList');
	Route::get('modal_buscar_pacientes_secretaria_list','SecretariaController@modalBuscarPacientesSecretariaList');						

	Route::post('ver_semana','SecretariaController@mostrarSemana')->name('mostrarsemana');			

	Route::post('actualizar_listado_ver_semana','SecretariaController@actualizarListadoVerSemana')->name('actualizarlistadoversemana');		

	Route::post('registrar_turno_agenda_semanal','SecretariaController@registrarTurnoAgendaSemanal')->name('registrarturnoagendasemanal');		
	Route::post('actualizar_listado_ver_semana_peg','SecretariaController@actualizarListadoVerSemanaPeg')->name('actualizarlistadoversemanapeg');

	Route::post('update_caja_listado_pacientes_secretaria','SecretariaController@updateCaja')->name('updatecajalistadopacientessecretaria');			

	Route::post('update_comentario_listado_pacientes_secretaria','SecretariaController@updateComentario')->name('updatecomentariolistadopacientessecretaria');				

	Route::post('cancelar_sobreturno','SecretariaController@cancelarSobreturno')->name('cancelarsobreturno');	

	Route::post('/asignar_turno_cancelar','SecretariaController@asignarTurnoCancelar')->name('asignarturnocancelar');	

	Route::post('/asignar_turno_cancelar_peg','SecretariaController@asignarTurnoCancelarPeg')->name('asignarturnocancelarpeg');	
	
	Route::post('/secretaria_recetas','RecetaController@secretariaRecetas')->name('secretariarecetas');	
	
	Route::get('receta-listado','RecetaController@recetaListado');		

	Route::post('/secretaria_ver_receta','RecetaController@verReceta')->name('secretariaverreceta');	

	Route::post('/actualizar_estado_receta','RecetaController@actualizarEstadoReceta')->name('actualizarestadoreceta');		

	Route::post('listado_pacientes_historial','SecretariaController@listadoPacientesHistorial')->name('listadopacienteshistorial');
	Route::get('historial_pacientes_list','SecretariaController@historialPacientesList');

	Route::get('/secretaria_recetas', function () {
		$user = Auth::user()->id;
	  	//$medico = DB::table('medicos')->where('medicos.user_id', $user)->first();
	  	$usuario_actual=\Auth::user();    
    	$secretaria = DB::table('secretarias')                                                                
		                ->where('secretarias.user_id',$usuario_actual->id)
		                ->first();
        $secretaria_consultorio = DB::table('secretaria_consultorios')
		                ->where('secretaria_consultorios.secretaria_id',$secretaria->id)
		                ->first();
		$consultorio = $secretaria_consultorio->consultorio_id;
     	  	
	  	$recetas = DB::table('recetas')						
					->join('pacientes','pacientes.id','=','recetas.paciente')					
					->join('receta_estados','recetas.estado','=','receta_estados.id')					
					->select('pacientes.id as pac_id','pacientes.nombre','pacientes.apellido','pacientes.telefono','pacientes.dni','pacientes.mail','recetas.id as rec_id','recetas.motivo','receta_estados.estado', 'recetas.created_at as fecha' )
					->where('recetas.consultorio',$consultorio)
					->where('recetas.estado','!=',5)					
					->where('recetas.activo',1)
					->where('pacientes.activo',1)
					->where('receta_estados.activo',1)
					->orderby('recetas.created_at')
					->get();
		return  View('turnos_admin_secretaria.sadmin_recetas')
		    	->with('consultorio',$consultorio)
		    	->with('recetas',$recetas);   
	})->name('secretariarecetas2');
});

Route::post('/receta_lista_enviar','RecetaController@recetaListaEnviar')->name('recetalistaenviar');

Route::post('/actualizar_estado_receta_paciente','PacienteController@actualizarEstadoRecetaPaciente')->name('actualizarestadorecetapaciente');	

Route::post('paciente_consultar','PacienteController@consultarPaciente')->name('pacienteconsultar');		

Route::get('/mis_turnos', function () {	
  return view('turnos/cancelar_turno')->with('dni_paciente','')->with('mensaje','');
});

Route::get('/mis_recetas', function () {
  return view('turnos/mis_recetas')->with('dni_paciente','')->with('mensaje','');
});

Route::get('/contacto', function () {
  return view('turnos/contacto');
});

Route::get('/seleccionar_dia', function () {
  return view('turnos/seleccionar_dia');
});

Route::get('/seleccionar_turno', function () {
  return view('turnos/seleccionar_turno');
});

Route::get('/enviar_recordatorio_mail','TurnoController@enviarRecordatorioMail')->name('enviarrecordatoriomail');

//Route::get('/turno_registrado', function () {return view('turnos/turno_registrado');});
Route::get('/turno_registrado/{t}', 'TurnoController@turnoRegistrado')->name('turnoregistrado');

Route::post('/turno_registrado','TurnoController@turnoRegistrado2')->name('turnoregistrado');

Route::post('/cancelar_turno','TurnoController@cancelarTurno')->name('cancelarturno');

Route::post('/cancelar_turno_paciente','TurnoController@cancelarTurnoPaciente')->name('cancelarturnoactionpaciente');

Route::post('/cancelar_turno_paciente_videollamada','TurnoController@cancelarTurnoPacienteVideollamada')->name('cancelarturnopacientevideollamada');

Route::post('/cancelar_turno_action','TurnoController@cancelarTurnoAction')->name('cancelarturnoaction');

Route::post('/cancelar_turno_dni','TurnoController@cancelarTurnoDni')->name('cancelarturnodni');

Route::post('/asistir_videollamada','TurnoController@asistirVideollamada')->name('asistirvideollamada');

Route::post('/actualizar_estado_paciente_videollamada','TurnoController@actualizarEstadoPacienteVideollamada')->name('actualizarestadopacientevideollamada');

Route::post('/check_estado_medico_videollamada','TurnoController@checkEstadoMedicoVideollamada')->name('checkestadomedicovideollamada');

Route::post('/consultar_receta','PacienteController@consultarReceta')->name('consultarreceta');

Route::post('/ver_receta_paciente','PacienteController@verReceta')->name('verrecetapaciente');

Route::post('/ver_receta_paciente_fotos','PacienteController@verRecetaPacienteFotos')->name('verrecetapacientefotos');

Route::post('/cargar_domicilio_paciente','PacienteController@cargarDomicilioPaciente')->name('cargardomiciliopaciente');

Route::post('/cargar_numero_afiliado_paciente','PacienteController@cargarNumeroAfiliadoPaciente')->name('cargarnumeroafiliadopaciente');

Route::post('/registrar_turno','TurnoController@registrarTurno')->name('registrarturno');

Route::post('/registrar_turno_primer_control','TurnoController@registrarTurnoPrimerControl')->name('registrarturnoprimercontrol');

Route::get('/calendar/reminder/{turno_id}','TurnoController@downloadReminderCalendar')->name('calendar.reminder');
Route::get('/calendar/turno/{turno_id}','TurnoController@downloadTurnoCalendar')->name('calendar.turno');

// Rutas para Google Calendar OAuth
Route::get('/google-calendar/authorize','GoogleCalendarController@redirect')->name('google.calendar.authorize');
Route::get('/google-calendar/callback','GoogleCalendarController@callback')->name('google.calendar.callback');
Route::post('/google-calendar/disconnect','GoogleCalendarController@disconnect')->name('google.calendar.disconnect');

Route::post('/confirmar_turno','TurnoController@confirmarTurno')->name('confirmarturno');

Route::post('/seleccionar_turno_horario','TurnoController@seleccionarTurnoHorario')->name('seleccionarturnohorario');

Route::post('/get_horarios_medico','TurnoController@getHorariosMedico')->name('gethorariosmedico');

Route::post('/get_fechas_disponibles','TurnoController@getFechasDisponibles')->name('getfechasdisponibles');

Route::post('/seleccionar_turno_horario_videollamada','TurnoController@seleccionarTurnoHorarioVideollamada')->name('seleccionarturnohorariovideollamada');

Route::get('/seleccionar_especialidad','EspecialidadController@index')->name('seleccionarespecialidad');

Route::post('/alta_paciente','PacienteController@store')->name('altapaciente');

Route::post('/registrar_paciente_pendiente','PacienteController@registrarPacientePendiente')->name('registrarpacientependiente');

Route::post('/ingresar_paciente','PacienteController@index')->name('ingresarpaciente');

Route::get('/plantilla_medica','PacienteController@showMedicosIndex')->name('seleccionarmedicoindex');

Route::post('/seleccionar_medico','PacienteController@showMedicos')->name('seleccionarmedico');

Route::post('/seleccionar_medico_especialidad','PacienteController@seleccionarMedicoEspecialidad')->name('seleccionarmedicoespecialidad');

Route::post('/seleccionar_medico','PacienteController@consultorioSeleccionarMedico')->name('consultorioseleccionarmedico');

Route::post('/seleccionar_dia','TurnoController@selectDia')->name('seleccionardia');

Route::post('actualizar_datos_paciente','PacienteController@actualizarDatosPaciente')->name('actualizardatospaciente');

Route::post('tipo_turno','PacienteController@tipoTurno')->name('tipoturno');

Route::post('tipo_turno_cardiologo','PacienteController@tipoTurnoCardiologo')->name('tipoturnocardiologo');

Route::post('alta_paciente_medico_secretaria','PacienteController@altaPacienteMedicoSecretaria')->name('altapacientemedicosecretaria');	

Route::post('solicitar_nueva_receta','PacienteController@solicitarNuevaReceta')->name('solicitarnuevareceta');	

Route::get('/generar_listado_sms','TurnoController@generarListadoSms')->name('generarlistadosms');

Route::get('/get_users_information','TurnoController@getUsersInformation')->name('getusersinformation');

Route::get('/generar_listado_dia_tobb/{fecha}/{medico}','TurnoController@generarListadoDiaTobb')->name('generarlistadodiatobb');

Route::get('/get_paciente_tobb/{dni}','TurnoController@getPacienteTobb')->name('getpacientetobb');

Route::post('consultar_paciente_id','PacienteController@consultarPacienteId')->name('consultarpacienteid');

Route::post('verificar_paciente_bloqueado','PacienteController@verificarPacienteBloqueado')->name('verificarpacientebloqueado');

Route::post('verificar_paciente_medico_obra_social','PacienteController@verificarPacienteMedicoObraSocial')->name('verificarpacientemedicoobrasocial');

Route::get('check_error_fecha/{fechaSelect}','TurnoController@checkErrorFecha')->name('checkdates');
Route::get('check_timeout','TurnoController@checkTimeout')->name('checktimeout');
//Auth::routes();
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');

Route::get('home/redirect', 'HomeController@irHome')->name('password.irHome');

Route::get('email/verify', 'Auth\VerificationController@show')->name('verification.notice');
Route::get('email/verify/{id}', 'Auth\VerificationController@verify')->name('verification.verify');
Route::get('email/resend', 'Auth\VerificationController@resend')->name('verification.resend');

Route::post('/payments/pay','MercadopagoController@pay')->name('pay');
Route::get('/payments/approval','MercadopagoController@approval')->name('approval');
Route::get('/payments/cancelled','MercadopagoController@cancelled')->name('cancelled');

Route::post('guardar_numero_operacion','MercadopagoController@guardarNumeroOperacion')->name('guardarnumerooperacion');
Route::get('mercado_pago_transaccion/{res}','MercadopagoController@mercadoPagoTransaccion')->name('mercadopagotransaccion');

Route::post('cargar_foto_obra_social','PacienteController@cargarFotoObraSocial')->name('cargarfotoobrasocial');

Route::post('cargar_foto_obra_socialv','PacienteController@cargarFotoObraSocialv')->name('cargarfotoobrasocialv');

Route::post('check_tiene_foto_os','PacienteController@checkTieneFotoOs')->name('checktienefotoos');

Route::get('get_turnos_dia_activo_desactivo/{fecha}','TurnoController@getTurnosDiaActivoDesactivo')->name('getturnosdiaactivodesactivo');

Route::post('get_turno_registrado_id','TurnoController@getTurnoRegistradoId')->name('getturnoregistradoid');

Route::post('validar_asistio_ultima_ves','PacienteController@validarAsistioUltimaVes')->name('validarasistioultimaves');

Route::post('/cargar_foto_receta_2', 'RecetaController@cargarFotoReceta2')->name('cargarfotoreceta2');

Route::post('/cargar_pdf_receta_2', 'RecetaController@cargarPdfReceta2')->name('cargarpdfreceta2');

Route::post('/obtener_fotos_receta', 'RecetaController@obtenerFotosReceta')->name('obtenerfotosreceta');

Route::get('/generar_listado_recetas_sms','PacienteController@generarListadoRecetasSms')->name('generarlistadorecetassms');

Route::post('/get_turno_id', 'TurnoController@getTurnoId')->name('getturnoid');


Route::get('admin_horarios','MedicoController@adminHorarios')->name('adminhorarios');

Route::post('/registrar_nuevo_horario', 'MedicoController@registrarNuevoHorario')->name('registrarnuevohorario');

Route::post('/actualizar_listado_nuevo_horario', 'MedicoController@actualizarListadoNuevoHorario')->name('actualizarlistadonuevohorario');

Route::post('/actualizar_listado_horarios_fecha', 'MedicoController@actualizarListadoHorariosFecha')->name('actualizarlistadohorariosfecha');

Route::post('/actualizar_listado_horarios_fecha_eliminar', 'MedicoController@actualizarListadoHorariosFechaEliminar')->name('actualizarlistadohorariosfechaeliminar');
Route::get('/cancelaturno/{id}', 'TurnoController@cancelaTurno')->name('cancelaturno');

Route::post('/mercadopago/preference', 'MercadopagoController@createPreference')->name('createPreference');

Route::post('/mercadopago/preference2', 'MercadopagoController@createPreference')->name('createPreference2');

Route::post('/get_obra_social_diferencial', 'TurnoController@getObraSocialDiferencial')->name('getobrasocialdiferencial');

Route::post('/registrar_numero_lista_negra', 'TurnoController@registrarNumeroListaNegra')->name('registrarnumerolistanegra');
Route::get('/lista_negra', 'TurnoController@listaNegra')->name('listanegra');

Route::post('/verificar_obra_social_medico', 'PacienteController@verificarObraSocialMedico')->name('verificarobrasocialmedico');

Route::post('/actualizar_tipo_turno', 'TurnoController@actualizarTipoTurno')->name('actualizartipoturno');

Route::post('/enviar_mail_confirmacion', 'TurnoController@enviarMailConfirmacion')->name('enviarmailconfirmacion');

Route::post('/validar_obra_social', 'PacienteController@validarObraSocial')->name('validarobrasocial');

//Route::post('/procesar_pago','MercadopagoController@procesarPago')->name('procesarpago');