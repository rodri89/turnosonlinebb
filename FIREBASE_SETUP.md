# Configuración de Firebase Cloud Messaging (FCM)

Este documento explica cómo configurar y usar Firebase Cloud Messaging en tu proyecto para enviar notificaciones push a los usuarios.

## Pasos de Configuración

### 1. Obtener la VAPID Key

La VAPID Key es necesaria para que el navegador pueda recibir notificaciones push.

1. Ve a [Firebase Console](https://console.firebase.google.com/)
2. Selecciona tu proyecto: `turnosonlinebb-8406b`
3. Ve a **Project Settings** (Configuración del proyecto)
4. Haz clic en la pestaña **Cloud Messaging**
5. En la sección **Web Push certificates**, si no tienes una clave, haz clic en **Generate key pair**
6. Copia la clave generada (será algo como: `BEl...`)

### 2. Configurar la VAPID Key en el código

Abre el archivo `resources/views/turnos/seleccionar_dia.blade.php` y busca la línea:

```javascript
const vapidKey = 'TU_VAPID_KEY_AQUI'; // Reemplaza con tu VAPID Key
```

Reemplaza `'TU_VAPID_KEY_AQUI'` con la clave VAPID que obtuviste en el paso anterior.

### 3. Obtener el Server Key de Firebase

El Server Key es necesario para enviar notificaciones desde el backend.

#### Opción A: Usar Server Key (Legacy) - Más simple

1. En Firebase Console, ve a **Project Settings** > **Cloud Messaging**
2. Busca la sección **Cloud Messaging API (Legacy)** o **Server key**
3. Si ves el Server Key, cópialo (será algo como: `AAAA...`)

#### Opción B: Crear cuenta de servicio (Recomendado si no hay Server Key)

Si no ves el Server Key, necesitas crear una cuenta de servicio:

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Selecciona tu proyecto `turnosonlinebb-8406b`
3. Ve a **APIs & Services** > **Credentials**
4. Haz clic en **+ CREATE CREDENTIALS** > **Service account**
5. Completa el formulario:
   - **Service account name**: `fcm-service` (o el nombre que prefieras)
   - **Service account ID**: se generará automáticamente
   - Haz clic en **CREATE AND CONTINUE**
6. En **Grant this service account access to project**:
   - **Role**: Selecciona **Firebase Cloud Messaging API Admin** o **Firebase Cloud Messaging API Service Agent**
   - Haz clic en **CONTINUE** y luego **DONE**
7. Una vez creada la cuenta de servicio:
   - Haz clic en la cuenta de servicio que acabas de crear
   - Ve a la pestaña **KEYS**
   - Haz clic en **ADD KEY** > **Create new key**
   - Selecciona **JSON** y haz clic en **CREATE**
   - Se descargará un archivo JSON con las credenciales
   - **IMPORTANTE:** Guarda este archivo en un lugar seguro (por ejemplo: `storage/app/firebase-service-account.json`)

**Nota importante:** Si usas una cuenta de servicio, necesitarás actualizar el código para usar el archivo JSON. Ver sección "Configuración con cuenta de servicio" más abajo.

#### Habilitar la API de Cloud Messaging

Si la API no está habilitada:
- Ve a [Google Cloud Console](https://console.cloud.google.com/)
- Selecciona tu proyecto
- Ve a **APIs & Services** > **Library**
- Busca "Firebase Cloud Messaging API" y haz clic en **ENABLE**

### 4. Configurar las credenciales en el archivo .env

#### Si usaste Server Key (Opción A):

Abre tu archivo `.env` y agrega:

```
FIREBASE_SERVER_KEY=TU_SERVER_KEY_AQUI
```

Reemplaza `TU_SERVER_KEY_AQUI` con el Server Key que obtuviste.

#### Si usaste cuenta de servicio (Opción B):

1. Coloca el archivo JSON descargado en `storage/app/firebase-service-account.json`
2. Abre tu archivo `.env` y agrega:

```
FIREBASE_SERVICE_ACCOUNT_PATH=storage/app/firebase-service-account.json
```

**Nota:** Asegúrate de que el archivo JSON esté en un lugar seguro y no sea accesible públicamente. El directorio `storage/app` es ideal porque Laravel lo protege automáticamente.

### 5. Ejecutar la migración

Ejecuta la migración para agregar el campo `fcm_token` a la tabla `pacientes`:

```bash
php artisan migrate
```

## Uso del Sistema

### Obtener el token FCM del usuario

El token FCM se obtiene automáticamente cuando el usuario visita la página de selección de turnos. El token se guarda en la base de datos asociado al `paciente_id`.

### Enviar notificaciones desde el backend

Puedes usar el servicio `FirebaseMessagingService` para enviar notificaciones. Ejemplo:

```php
use App\Paciente;
use App\Services\FirebaseMessagingService;

// Obtener el paciente
$paciente = Paciente::find($paciente_id);

// Verificar que tenga un token FCM
if ($paciente && $paciente->fcm_token) {
    // Obtener el servicio
    $firebase = app('firebase');
    
    // Enviar notificación de recordatorio de turno
    $turnoData = [
        'turno_id' => $turno->id,
        'fecha' => '15/02/2026',
        'horario' => '10:00',
        'medico' => 'Dr. García'
    ];
    
    $result = $firebase->sendTurnoReminder($paciente->fcm_token, $turnoData);
    
    // O enviar una notificación personalizada
    $firebase->sendToToken(
        $paciente->fcm_token,
        'Título de la notificación',
        'Cuerpo del mensaje',
        ['custom_data' => 'valor']
    );
}
```

### Enviar notificaciones a múltiples usuarios

```php
$pacientes = Paciente::whereNotNull('fcm_token')->get();
$tokens = $pacientes->pluck('fcm_token')->toArray();

$firebase = app('firebase');
$result = $firebase->sendToMultipleTokens(
    $tokens,
    'Título',
    'Mensaje para todos'
);
```

## Ejemplo: Recordatorio de Turnos

Puedes crear un comando de Laravel para enviar recordatorios automáticos:

```php
// app/Console/Commands/SendTurnoReminders.php
use Illuminate\Console\Command;
use App\TurnoRegistrado;
use App\Paciente;
use Carbon\Carbon;

class SendTurnoReminders extends Command
{
    protected $signature = 'turnos:send-reminders';
    
    public function handle()
    {
        // Obtener turnos del día siguiente
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $turnos = TurnoRegistrado::where('fechaTurno', $tomorrow)
            ->where('activo', 1)
            ->get();
        
        $firebase = app('firebase');
        
        foreach ($turnos as $turno) {
            $paciente = Paciente::find($turno->paciente);
            
            if ($paciente && $paciente->fcm_token) {
                $turnoData = [
                    'turno_id' => $turno->id,
                    'fecha' => Carbon::parse($turno->fechaTurno)->format('d/m/Y'),
                    'horario' => $turno->horario,
                    'medico' => $turno->medico->nombre . ' ' . $turno->medico->apellido
                ];
                
                $firebase->sendTurnoReminder($paciente->fcm_token, $turnoData);
            }
        }
    }
}
```

Luego programa este comando en `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('turnos:send-reminders')
        ->dailyAt('18:00'); // Enviar recordatorios todos los días a las 18:00
}
```

## Configuración con Cuenta de Servicio (Opción B)

Si elegiste usar una cuenta de servicio en lugar del Server Key, necesitarás usar la API v1 de FCM con OAuth2. Esto requiere instalar el paquete de Google Auth:

```bash
composer require google/auth
```

Luego, actualiza el servicio `FirebaseMessagingService` para usar OAuth2. El servicio actual está configurado para usar Server Key (método legacy). Si necesitas usar cuenta de servicio, puedes:

1. **Opción más simple**: Intenta obtener el Server Key desde Firebase Console > Project Settings > Cloud Messaging. Si está disponible, úsalo.

2. **Si no hay Server Key disponible**: Necesitarás actualizar el servicio para usar OAuth2 con la cuenta de servicio. Esto es más complejo pero más seguro.

**Recomendación**: Si es posible, usa el Server Key (Opción A) ya que es más simple y el código actual ya está configurado para eso.

## Solución de Problemas

### El token no se guarda

- Verifica que el `paciente_id` esté disponible en la página
- Revisa la consola del navegador para ver errores
- Verifica que la VAPID Key esté correctamente configurada

### Las notificaciones no se envían

- Verifica que el Server Key esté correctamente configurado en `.env`
- Asegúrate de que el token FCM esté guardado en la base de datos
- Revisa los logs de Laravel para ver errores

### El service worker no se registra

- Verifica que el archivo `public/firebase-messaging-sw.js` exista
- Asegúrate de que el sitio esté servido por HTTPS (requerido para service workers)
- Revisa la consola del navegador para ver errores de registro

## Notas Importantes

1. **HTTPS requerido**: Los service workers y las notificaciones push solo funcionan en sitios HTTPS (o localhost para desarrollo)

2. **Permisos del navegador**: El usuario debe otorgar permisos para recibir notificaciones

3. **Tokens únicos**: Cada dispositivo/navegador genera un token único. Si un usuario usa múltiples dispositivos, tendrás múltiples tokens

4. **Tokens expiran**: Los tokens FCM pueden expirar o cambiar. Es recomendable actualizar los tokens periódicamente

5. **Límites de FCM**: Firebase tiene límites en la cantidad de notificaciones que puedes enviar. Revisa la documentación oficial para más detalles

## Recursos Adicionales

- [Documentación oficial de FCM](https://firebase.google.com/docs/cloud-messaging)
- [Guía de configuración web](https://firebase.google.com/docs/cloud-messaging/js/client)
- [API REST de FCM](https://firebase.google.com/docs/cloud-messaging/send-message)

