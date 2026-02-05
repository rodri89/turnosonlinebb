<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Paciente;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Facades\Log;

class GoogleCalendarController extends Controller
{
    protected $calendarService;

    public function __construct(GoogleCalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    /**
     * Iniciar flujo de autenticación OAuth
     */
    public function redirect(Request $request)
    {
        $paciente_id = $request->input('paciente_id');
        $return_url = $request->input('return_url', $request->header('referer', '/'));
        
        if (!$paciente_id) {
            return redirect()->back()->with('error', 'ID de paciente no proporcionado');
        }

        // Guardar paciente_id y URL de retorno en la sesión
        session([
            'google_calendar_paciente_id' => $paciente_id,
            'google_calendar_return_url' => $return_url
        ]);

        // Generar URL de autorización
        $authUrl = $this->calendarService->getAuthUrl($paciente_id);

        return redirect($authUrl);
    }

    /**
     * Callback de OAuth - recibir código de autorización
     */
    public function callback(Request $request)
    {
        $code = $request->input('code');
        $paciente_id = session('google_calendar_paciente_id');

        if (!$code || !$paciente_id) {
            return redirect('/')->with('error', 'Error en la autenticación de Google Calendar');
        }

        // Intercambiar código por tokens
        $token = $this->calendarService->getAccessTokenFromCode($code);

        if (!$token || isset($token['error'])) {
            Log::error('Error obteniendo token de Google Calendar', ['token' => $token]);
            return redirect('/')->with('error', 'Error al obtener permisos de Google Calendar');
        }

        // Guardar tokens en la base de datos
        $paciente = Paciente::find($paciente_id);
        if ($paciente) {
            $paciente->google_calendar_access_token = $token['access_token'];
            $paciente->google_calendar_refresh_token = isset($token['refresh_token']) ? $token['refresh_token'] : $paciente->google_calendar_refresh_token;
            
            // Calcular fecha de expiración
            $expiresIn = isset($token['expires_in']) ? $token['expires_in'] : 3600;
            $paciente->google_calendar_token_expires_at = date('Y-m-d H:i:s', time() + $expiresIn);
            
            $paciente->save();

            // Obtener URL de retorno y limpiar sesión
            $returnUrl = session('google_calendar_return_url', '/');
            session()->forget('google_calendar_paciente_id');
            session()->forget('google_calendar_return_url');

            // Validar y redirigir a la URL de retorno
            if ($returnUrl && $returnUrl !== '/') {
                // Si es una URL absoluta, verificar que sea del mismo dominio
                if (filter_var($returnUrl, FILTER_VALIDATE_URL)) {
                    $parsedUrl = parse_url($returnUrl);
                    $currentDomain = parse_url(config('app.url'), PHP_URL_HOST);
                    
                    if (isset($parsedUrl['host']) && $parsedUrl['host'] === $currentDomain) {
                        return redirect($returnUrl)->with('success', '✅ Calendario de Google conectado exitosamente. Tus turnos se agregarán automáticamente.');
                    }
                } 
                // Si es una URL relativa (empieza con /), redirigir directamente
                elseif (strpos($returnUrl, '/') === 0) {
                    return redirect($returnUrl)->with('success', '✅ Calendario de Google conectado exitosamente. Tus turnos se agregarán automáticamente.');
                }
            }

            // Redirigir a home con mensaje de éxito
            return redirect('/')->with('success', '✅ Calendario de Google conectado exitosamente. Tus turnos se agregarán automáticamente.');
        }

        return redirect('/')->with('error', 'Paciente no encontrado');
    }

    /**
     * Desconectar Google Calendar
     */
    public function disconnect(Request $request)
    {
        $paciente_id = $request->input('paciente_id');

        if (!$paciente_id) {
            return response()->json(['success' => false, 'message' => 'ID de paciente no proporcionado'], 400);
        }

        $paciente = Paciente::find($paciente_id);
        if ($paciente) {
            $paciente->google_calendar_access_token = null;
            $paciente->google_calendar_refresh_token = null;
            $paciente->google_calendar_token_expires_at = null;
            $paciente->save();

            return response()->json(['success' => true, 'message' => 'Google Calendar desconectado']);
        }

        return response()->json(['success' => false, 'message' => 'Paciente no encontrado'], 404);
    }
}

