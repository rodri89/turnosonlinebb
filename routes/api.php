<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('set_mensaje_enviado', 'TurnoController@setMensajeEnviado');
Route::post('set_mensaje_enviado_receta', 'PacienteController@setMensajeEnviadoReceta');

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');
  
    Route::group(['middleware' => 'auth:api'], function() {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
    });    

});

Route::post('/mercadopago/preference', 'MercadopagoController@createPreference')->name('createPreference');
Route::post('/mercadopago/preference2', 'MercadopagoController@createPreference')->name('createPreference2');
Route::post('/webhooks/mercadopago', 'TurnoPagoController@webhook');

/*
|--------------------------------------------------------------------------
| API Salud 360 (app de médicos, secretarias y administrador)
|--------------------------------------------------------------------------
| Autenticación con Passport (guard api). Ver API_SALUD360.md.
*/
Route::group(['prefix' => 'salud360', 'namespace' => 'Api\Salud360'], function () {
    Route::post('auth/login', 'AuthController@login');

    Route::group(['middleware' => ['auth:api', 'salud360']], function () {
        Route::get('auth/perfil', 'AuthController@perfil');
        Route::post('auth/logout', 'AuthController@logout');
        Route::post('auth/password', 'AuthController@cambiarPassword');

        Route::get('catalogos', 'CatalogoController@index');
        Route::get('obras-sociales', 'CatalogoController@obrasSocialesMedico');
        Route::post('obras-sociales', 'CatalogoController@guardarObraSocialMedico');
        Route::get('mensajes', 'CatalogoController@mensajes');
        Route::post('feriados', 'CatalogoController@guardarFeriado');
        Route::delete('feriados/{id}', 'CatalogoController@borrarFeriado');

        Route::get('agenda/dia', 'AgendaController@dia');
        Route::get('agenda/disponibilidad', 'AgendaController@disponibilidad');
        Route::get('agenda/semana', 'AgendaController@semana');
        Route::get('agenda/proximas-fechas', 'AgendaController@proximasFechas');
        Route::get('agenda/dias-atencion', 'AgendaController@diasAtencion');

        Route::get('turnos', 'TurnoController@index');
        Route::post('turnos', 'TurnoController@store');
        Route::post('turnos/sobreturno', 'TurnoController@sobreturno');
        Route::post('turnos/bloquear-dia', 'TurnoController@bloquearDia');
        Route::get('turnos/paciente/{pacienteId}', 'TurnoController@porPaciente');
        Route::get('turnos/{id}', 'TurnoController@show');
        Route::post('turnos/{id}/cancelar', 'TurnoController@cancelar');
        Route::post('turnos/{id}/asistencia', 'TurnoController@asistencia');
        Route::post('turnos/{id}/caja', 'TurnoController@caja');
        Route::post('turnos/{id}/comentario', 'TurnoController@comentario');

        Route::get('pacientes', 'PacienteController@index');
        Route::get('pacientes/buscar', 'PacienteController@buscar');
        Route::get('pacientes/pendientes', 'PacienteController@pendientes');
        Route::post('pacientes', 'PacienteController@store');
        Route::get('pacientes/{id}', 'PacienteController@show');
        Route::put('pacientes/{id}', 'PacienteController@update');
        Route::post('pacientes/{id}/activar', 'PacienteController@activar');
        Route::post('pacientes/{id}/bloqueo', 'PacienteController@bloqueo');

        Route::get('horarios', 'HorarioController@index');
        Route::post('horarios', 'HorarioController@store');
        Route::post('horarios/fecha-especial', 'HorarioController@guardarFechaEspecial');
        Route::delete('horarios/fecha-especial/horario/{id}', 'HorarioController@borrarHorarioEspecial');
        Route::delete('horarios/fecha-especial/{id}', 'HorarioController@borrarFechaEspecial');
        Route::put('horarios/{id}', 'HorarioController@update');
        Route::delete('horarios/{id}', 'HorarioController@destroy');
        Route::put('config/primer-control', 'HorarioController@primerControl');
        Route::put('config/ventana-dias', 'HorarioController@ventanaDias');

        Route::get('recetas', 'RecetaController@index');
        Route::get('recetas/{id}', 'RecetaController@show');
        Route::post('recetas/{id}/estado', 'RecetaController@estado');
    });
});
