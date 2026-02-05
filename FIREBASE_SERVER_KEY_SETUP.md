# Configurar Server Key de Firebase

## ❌ Error: 404 Not Found al enviar notificaciones

Si recibes un error 404 al intentar enviar notificaciones, significa que el **Server Key** no está configurado correctamente o no es válido.

## 🔑 Cómo obtener el Server Key

### Opción 1: Server Key Legacy (Recomendado - Más simple)

1. Ve a [Firebase Console](https://console.firebase.google.com/)
2. Selecciona tu proyecto: `turnosonlinebb-8406b`
3. Haz clic en el ícono de ⚙️ (Configuración) > **Project settings**
4. Ve a la pestaña **Cloud Messaging**
5. Busca la sección **Cloud Messaging API (Legacy)**
6. Si ves **Server key**, cópialo (será algo como: `AAAA...`)
7. Si NO ves el Server Key, ve a la **Opción 2** más abajo

### Opción 2: Si no aparece el Server Key Legacy

Firebase puede haber deshabilitado el Server Key legacy para proyectos nuevos. En ese caso:

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Selecciona tu proyecto: `turnosonlinebb-8406b`
3. Ve a **APIs & Services** > **Credentials**
4. Busca una clave de API existente o crea una nueva:
   - Haz clic en **+ CREATE CREDENTIALS** > **API key**
   - Restringe la clave a **Firebase Cloud Messaging API**
5. Copia la clave generada

**NOTA:** Si no puedes obtener un Server Key, necesitarás usar una cuenta de servicio (más complejo). Ver la documentación principal.

## ⚙️ Configurar en Laravel

Una vez que tengas el Server Key:

1. Abre tu archivo `.env` en la raíz del proyecto
2. Agrega o actualiza esta línea:

```env
FIREBASE_SERVER_KEY=tu_server_key_aqui
```

**IMPORTANTE:** 
- Reemplaza `tu_server_key_aqui` con el Server Key que copiaste
- NO pongas comillas alrededor del valor
- El Server Key debe empezar con `AAAA` o similar

3. Guarda el archivo `.env`
4. Si estás usando `php artisan serve`, reinicia el servidor

## ✅ Verificar que funciona

1. Accede a: `http://localhost:8000/test_fcm_notification/{paciente_id}`
2. Deberías ver una respuesta como:

```json
{
  "success": true,
  "message": "Notificación enviada",
  "result": {
    "multicast_id": 123456789,
    "success": 1,
    "failure": 0
  }
}
```

## 🐛 Solución de Problemas

### Error: "FIREBASE_SERVER_KEY no está configurado"

- Verifica que la línea `FIREBASE_SERVER_KEY=...` esté en tu `.env`
- Asegúrate de que no tenga comillas
- Reinicia el servidor de Laravel

### Error: 401 Unauthorized

- El Server Key es incorrecto
- Verifica que lo copiaste completo (son claves largas)
- Asegúrate de que no tenga espacios al inicio o final

### Error: 404 Not Found

- El Server Key puede no ser válido para este proyecto
- Intenta obtener un nuevo Server Key desde Firebase Console
- Verifica que el proyecto de Firebase sea el correcto

### El Server Key no aparece en Firebase Console

Algunos proyectos nuevos de Firebase no muestran el Server Key legacy. Opciones:

1. **Habilitar API Legacy** (si es posible):
   - Ve a Google Cloud Console
   - APIs & Services > Library
   - Busca "Firebase Cloud Messaging API (Legacy)"
   - Habilítala si está deshabilitada

2. **Usar cuenta de servicio** (más complejo):
   - Ver `FIREBASE_SETUP.md` sección "Opción B"

## 📝 Ejemplo de .env

```env
APP_NAME=TurnosOnline
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost

# ... otras configuraciones ...

# Firebase Cloud Messaging
FIREBASE_SERVER_KEY=AAAA1234567890:APA91bH...
```

## ⚠️ Importante

- **NUNCA** subas tu archivo `.env` a Git
- El Server Key es sensible, mantenlo seguro
- Si expones el Server Key, revócalo y genera uno nuevo

