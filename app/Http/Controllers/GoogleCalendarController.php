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
        $return_url = $request->input('return_url', '/');
        
        Log::info('Google Calendar OAuth - Iniciando redirección', [
            'paciente_id' => $paciente_id,
            'return_url' => $return_url,
            'has_client_id' => !empty(env('GOOGLE_CALENDAR_CLIENT_ID')),
            'has_client_secret' => !empty(env('GOOGLE_CALENDAR_CLIENT_SECRET'))
        ]);
        
        if (!$paciente_id) {
            Log::warning('Google Calendar OAuth - Paciente ID no proporcionado');
            return redirect('/')->with('error', 'ID de paciente no proporcionado');
        }

        // Validar que return_url sea una URL GET válida (no POST)
        if ($return_url && $return_url !== '/') {
            // Si es una URL absoluta, verificar que sea del mismo dominio
            if (filter_var($return_url, FILTER_VALIDATE_URL)) {
                $parsedUrl = parse_url($return_url);
                $currentDomain = parse_url(config('app.url'), PHP_URL_HOST);
                
                if (isset($parsedUrl['host']) && $parsedUrl['host'] !== $currentDomain) {
                    $return_url = '/';
                }
            } 
            // Si es una URL relativa, verificar que no sea una ruta POST conocida
            elseif (strpos($return_url, '/') === 0) {
                // Lista de rutas POST que no debemos usar como return_url
                $postRoutes = ['/tipo_turno', '/registrar_turno', '/seleccionar_turno_horario'];
                foreach ($postRoutes as $postRoute) {
                    if (strpos($return_url, $postRoute) !== false) {
                        $return_url = '/';
                        break;
                    }
                }
            }
        }

        // Guardar paciente_id y URL de retorno en la sesión
        session([
            'google_calendar_paciente_id' => $paciente_id,
            'google_calendar_return_url' => $return_url
        ]);

        // Generar URL de autorización
        try {
            $authUrl = $this->calendarService->getAuthUrl($paciente_id);
            
            // Log detallado de la URL generada
            Log::info('Google Calendar OAuth - URL generada', [
                'auth_url_length' => strlen($authUrl),
                'contains_google' => strpos($authUrl, 'accounts.google.com') !== false,
                'client_id_from_env' => env('GOOGLE_CALENDAR_CLIENT_ID'),
                'auth_url_preview' => substr($authUrl, 0, 200)
            ]);
            
            // Verificar que la URL se haya generado correctamente
            if (empty($authUrl)) {
                Log::error('Google Calendar OAuth - URL de autorización vacía');
                return redirect($return_url)->with('error', 'Error: No se pudo generar la URL de autorización. Verifica que las credenciales de Google Calendar estén configuradas en el archivo .env');
            }
            
            if (strpos($authUrl, 'accounts.google.com') === false) {
                Log::error('Google Calendar OAuth - URL de autorización inválida', ['url' => $authUrl]);
                return redirect($return_url)->with('error', 'Error: La URL de autorización generada no es válida. Verifica la configuración de Google Calendar.');
            }
            
            Log::info('Google Calendar OAuth - Redirigiendo a Google', ['auth_url' => substr($authUrl, 0, 100) . '...']);
            return redirect($authUrl);
        } catch (\Exception $e) {
            Log::error('Google Calendar OAuth - Error generando URL', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Redirigir de vuelta a la página de origen con el error
            $errorMessage = 'Error de configuración: Google Calendar no está configurado correctamente. ';
            $errorMessage .= 'Verifica que GOOGLE_CALENDAR_CLIENT_ID y GOOGLE_CALENDAR_CLIENT_SECRET estén en el archivo .env';
            
            if ($return_url && $return_url !== '/') {
                return redirect($return_url)->with('error', $errorMessage);
            }
            
            return redirect('/')->with('error', $errorMessage);
        }
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

    /**
     * Página de prueba para conectar Google Calendar
     */
    public function testPage(Request $request)
    {
        $paciente_id = $request->input('paciente_id');
        $paciente = null;

        if ($paciente_id) {
            $paciente = Paciente::find($paciente_id);
        }

        return view('turnos.test_google_calendar', compact('paciente'));
    }

    /**
     * Conectar Google Calendar desde página de prueba
     */
    public function testConnect(Request $request)
    {
        $paciente_id = $request->input('paciente_id');
        $token = $request->route('token');

        if (!$paciente_id) {
            $testUrl = route('test.google.calendar', ['token' => $token]);
            return redirect($testUrl)->with('error', 'ID de paciente no proporcionado');
        }

        $paciente = Paciente::find($paciente_id);
        if (!$paciente) {
            $testUrl = route('test.google.calendar', ['token' => $token]);
            return redirect($testUrl)->with('error', 'Paciente no encontrado');
        }

        // Redirigir a la autorización con return_url a la página de prueba
        $returnUrl = route('test.google.calendar', ['token' => $token, 'paciente_id' => $paciente_id]);
        
        session([
            'google_calendar_paciente_id' => $paciente_id,
            'google_calendar_return_url' => $returnUrl
        ]);

        try {
            $authUrl = $this->calendarService->getAuthUrl($paciente_id);
            return redirect($authUrl);
        } catch (\Exception $e) {
            Log::error('Error en testConnect: ' . $e->getMessage());
            $testUrl = route('test.google.calendar', ['token' => $token]);
            return redirect($testUrl)->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Desconectar Google Calendar desde página de prueba
     */
    public function testDisconnect(Request $request)
    {
        $paciente_id = $request->input('paciente_id');
        $token = $request->route('token');

        if (!$paciente_id) {
            $testUrl = route('test.google.calendar', ['token' => $token]);
            return redirect($testUrl)->with('error', 'ID de paciente no proporcionado');
        }

        $paciente = Paciente::find($paciente_id);
        if ($paciente) {
            $paciente->google_calendar_access_token = null;
            $paciente->google_calendar_refresh_token = null;
            $paciente->google_calendar_token_expires_at = null;
            $paciente->save();

            $testUrl = route('test.google.calendar', ['token' => $token, 'paciente_id' => $paciente_id]);
            return redirect($testUrl)->with('success', 'Google Calendar desconectado exitosamente');
        }

        $testUrl = route('test.google.calendar', ['token' => $token]);
        return redirect($testUrl)->with('error', 'Paciente no encontrado');
    }
}

