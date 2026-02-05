# Cómo Enviar Notificaciones Push con Firebase

## 🚀 Métodos Disponibles

### 1. Enviar Notificación a un Usuario Específico

```php
use App\Paciente;
use App\Services\FirebaseMessagingService;

// Obtener el paciente
$paciente = Paciente::find($paciente_id);

// Verificar que tenga token FCM
if ($paciente && $paciente->fcm_token) {
    $firebase = app('firebase');
    
    // Enviar notificación simple
    $result = $firebase->sendToToken(
        $paciente->fcm_token,
        'Título de la notificación',
        'Cuerpo del mensaje',
        ['custom_data' => 'valor'] // Datos adicionales (opcional)
    );
    
    // Verificar resultado
    if (isset($result['success']) && $result['success']) {
        echo "Notificación enviada correctamente";
    } else {
        echo "Error: " . ($result['message'] ?? 'Error desconocido');
    }
}
```

### 2. Enviar Recordatorio de Turno (Método Especializado)

```php
use App\Paciente;
use App\TurnoRegistrado;
use App\Medico;

$paciente = Paciente::find($paciente_id);
$turno = TurnoRegistrado::find($turno_id);
$medico = Medico::find($turno->medico);

if ($paciente && $paciente->fcm_token) {
    $firebase = app('firebase');
    
    $turnoData = [
        'turno_id' => $turno->id,
        'fecha' => date('d/m/Y', strtotime($turno->fechaTurno)),
        'horario' => $turno->horario,
        'medico' => $medico->apellido . ', ' . $medico->nombre
    ];
    
    $result = $firebase->sendTurnoReminder($paciente->fcm_token, $turnoData);
}
```

### 3. Enviar Notificación a Múltiples Usuarios

```php
use App\Paciente;

// Obtener todos los pacientes con token FCM
$pacientes = Paciente::whereNotNull('fcm_token')->get();
$tokens = $pacientes->pluck('fcm_token')->toArray();

$firebase = app('firebase');

// Enviar a todos
$result = $firebase->sendToMultipleTokens(
    $tokens,
    'Título de la notificación',
    'Mensaje para todos los usuarios'
);
```

## 🧪 Probar el Sistema

### Método 1: Usar la ruta de prueba

1. Obtén el ID de un paciente que tenga token FCM:
```sql
SELECT id, nombre, apellido, fcm_token FROM pacientes WHERE fcm_token IS NOT NULL LIMIT 1;
```

2. Accede a la URL de prueba:
```
http://localhost:8000/test_fcm_notification/{paciente_id}
```

Por ejemplo:
```
http://localhost:8000/test_fcm_notification/1
```

### Método 2: Desde el código (Tinker)

```bash
php artisan tinker
```

```php
$paciente = App\Paciente::whereNotNull('fcm_token')->first();
$firebase = app('firebase');
$result = $firebase->sendToToken(
    $paciente->fcm_token,
    'Prueba',
    'Esta es una notificación de prueba'
);
print_r($result);
```

## 📋 Ejemplo Completo: Recordatorio Automático de Turnos

El método `enviarRecordatorioMail()` ya está actualizado para enviar notificaciones push además de emails.

### Cómo funciona:

1. Obtiene todos los turnos del día siguiente
2. Para cada turno:
   - Si el paciente tiene email → Envía email
   - Si el paciente tiene token FCM → Envía notificación push

### Ejecutar manualmente:

```php
// Desde un controlador o comando
$turnoController = new App\Http\Controllers\TurnoController();
$turnoController->enviarRecordatorioMail();
```

### Programar automáticamente:

En `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Enviar recordatorios todos los días a las 18:00
    $schedule->call(function () {
        $controller = new \App\Http\Controllers\TurnoController();
        $controller->enviarRecordatorioMail();
    })->dailyAt('18:00');
}
```

## 📱 Ejemplo: Notificación cuando se registra un turno

Puedes enviar una notificación inmediatamente cuando un paciente registra un turno:

```php
// En el método que registra el turno (ej: registrarTurno)
public function registrarTurno(Request $request) {
    // ... código para registrar el turno ...
    
    // Después de registrar el turno exitosamente
    $paciente = Paciente::find($request->paciente_id);
    $medico = Medico::find($request->medico);
    
    if ($paciente && $paciente->fcm_token) {
        $firebase = app('firebase');
        
        $turnoData = [
            'turno_id' => $turno->id,
            'fecha' => date('d/m/Y', strtotime($turno->fechaTurno)),
            'horario' => $turno->horario,
            'medico' => $medico->apellido . ', ' . $medico->nombre
        ];
        
        // Enviar notificación de confirmación
        $firebase->sendToToken(
            $paciente->fcm_token,
            'Turno Confirmado',
            "Tu turno ha sido registrado para el {$turnoData['fecha']} a las {$turnoData['horario']}",
            array_merge($turnoData, ['type' => 'turno_confirmed'])
        );
    }
    
    // ... resto del código ...
}
```

## 🔔 Ejemplo: Notificación de cancelación de turno

```php
public function cancelarTurno($turno_id) {
    $turno = TurnoRegistrado::find($turno_id);
    $paciente = Paciente::find($turno->paciente);
    
    if ($paciente && $paciente->fcm_token) {
        $firebase = app('firebase');
        
        $firebase->sendToToken(
            $paciente->fcm_token,
            'Turno Cancelado',
            "Tu turno del {$turno->fechaTurno} a las {$turno->horario} ha sido cancelado",
            [
                'type' => 'turno_cancelled',
                'turno_id' => $turno->id
            ]
        );
    }
}
```

## ⚠️ Importante: Configurar el Server Key

Antes de enviar notificaciones, asegúrate de tener configurado el Server Key en tu archivo `.env`:

```env
FIREBASE_SERVER_KEY=tu_server_key_aqui
```

Si no lo tienes configurado, las notificaciones fallarán silenciosamente.

## 📊 Verificar Resultados

El método `sendNotification()` retorna un array con el resultado:

```php
$result = $firebase->sendToToken($token, 'Título', 'Mensaje');

// Resultado exitoso:
[
    'multicast_id' => 123456789,
    'success' => 1,
    'failure' => 0,
    'canonical_ids' => 0,
    'results' => [
        ['message_id' => '0:1234567890']
    ]
]

// Resultado con error:
[
    'success' => false,
    'error' => true,
    'message' => 'Mensaje de error'
]
```

## 🐛 Solución de Problemas

### La notificación no se envía

1. **Verifica el Server Key**: Asegúrate de que esté en `.env`
2. **Verifica el token**: El token FCM debe ser válido
3. **Revisa los logs**: Busca errores en `storage/logs/laravel.log`
4. **Verifica la respuesta**: Revisa el array de resultado para ver errores específicos

### Error: "Invalid registration token"

- El token FCM puede haber expirado
- El usuario puede haber desinstalado la app o limpiado datos
- Solución: Actualizar el token cuando el usuario vuelva a visitar el sitio

### Error: "MismatchSenderId"

- El Server Key no corresponde al proyecto de Firebase
- Verifica que el Server Key sea del proyecto correcto

## 💡 Mejores Prácticas

1. **Maneja errores**: Siempre verifica el resultado de `sendNotification()`
2. **Actualiza tokens**: Los tokens pueden expirar, actualízalos periódicamente
3. **No spamees**: No envíes demasiadas notificaciones al mismo usuario
4. **Personaliza mensajes**: Usa el nombre del paciente y datos relevantes
5. **Incluye datos**: Usa el campo `data` para pasar información adicional que la app pueda usar

