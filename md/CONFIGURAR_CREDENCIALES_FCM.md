# Configurar Credenciales para FCM

Veo que ya tienes una cuenta de servicio `fcm-service` creada. Tienes dos opciones:

## Opción 1: Crear API Key (Más Simple - Intentar primero)

1. En la página que estás viendo, haz clic en el botón **"+ Crear credenciales"** (arriba a la derecha)
2. Selecciona **"Clave de API"** (API key)
3. Se generará una clave (algo como `AIza...`)
4. Copia esa clave completa
5. Agrega esta línea a tu archivo `.env`:
   ```env
   FIREBASE_SERVER_KEY=AIza...tu_clave_completa_aqui
   ```
6. Guarda el archivo y reinicia el servidor
7. Prueba enviar una notificación

**NOTA:** Esta API Key puede funcionar con la API legacy. Si no funciona (da error 401/404), usa la Opción 2.

## Opción 2: Usar la Cuenta de Servicio (Recomendado para API v1)

Ya tienes la cuenta de servicio `fcm-service` creada. Ahora necesitas obtener su clave JSON:

### Paso 1: Obtener la clave JSON

1. Haz clic en el enlace **"Administrar cuentas de servicio"** (abajo en la sección de cuentas de servicio)
2. O ve directamente a: **APIs & Services** > **Credentials** > pestaña **Service accounts**
3. Busca la cuenta `fcm-service@turnosonlinebb-8406b.iam.gserviceaccount.com`
4. Haz clic en el correo electrónico de esa cuenta
5. Ve a la pestaña **KEYS**
6. Haz clic en **ADD KEY** > **Create new key**
7. Selecciona **JSON** y haz clic en **CREATE**
8. Se descargará un archivo JSON (algo como `turnosonlinebb-8406b-xxxxx.json`)

### Paso 2: Guardar el archivo JSON

1. Coloca el archivo JSON descargado en: `storage/app/firebase-service-account.json`
2. Asegúrate de que el archivo tenga permisos de lectura

### Paso 3: Instalar dependencias

```bash
composer require google/auth
```

### Paso 4: Configurar en .env

Agrega esta línea a tu `.env`:

```env
FIREBASE_SERVICE_ACCOUNT_PATH=storage/app/firebase-service-account.json
```

### Paso 5: Actualizar el código

El servicio `FirebaseMessagingService` necesita actualizarse para usar la API v1 con OAuth2. Esto requiere cambios en el código.

## Recomendación

1. **Primero intenta la Opción 1** (crear API Key) - es más simple y puede funcionar
2. Si la API Key no funciona, usa la **Opción 2** (cuenta de servicio) - es más robusta pero requiere actualizar código

## Verificar que funciona

Después de configurar, prueba:
```
http://localhost:8000/test_fcm_notification/{paciente_id}
```

