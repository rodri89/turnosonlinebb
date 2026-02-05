# Configurar Firebase Cloud Messaging API v1

Tu proyecto usa la API v1 de FCM, que es más moderna pero requiere configuración adicional.

## Opción 1: Obtener Server Key desde Google Cloud Console (Más Simple)

Aunque Firebase Console no muestre el Server Key, puedes intentar obtenerlo desde Google Cloud Console:

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Selecciona tu proyecto: `turnosonlinebb-8406b`
3. Ve a **APIs & Services** > **Credentials**
4. Busca si hay alguna **API Key** existente
5. Si no hay, crea una nueva:
   - Haz clic en **+ CREATE CREDENTIALS** > **API key**
   - Copia la clave generada
   - (Opcional) Restringe la clave a **Firebase Cloud Messaging API**

**NOTA:** Esta API Key puede no funcionar para FCM. Si no funciona, usa la Opción 2.

## Opción 2: Usar Cuenta de Servicio con API v1 (Recomendado)

La API v1 requiere autenticación OAuth2 con una cuenta de servicio.

### Paso 1: Crear cuenta de servicio

1. En [Google Cloud Console](https://console.cloud.google.com/), ve a **APIs & Services** > **Credentials**
2. Haz clic en **+ CREATE CREDENTIALS** > **Service account**
3. Completa:
   - **Service account name**: `fcm-service`
   - **Service account ID**: se genera automáticamente
   - Haz clic en **CREATE AND CONTINUE**
4. En **Grant this service account access to project**:
   - **Role**: Selecciona **Firebase Cloud Messaging API Admin**
   - Haz clic en **CONTINUE** y luego **DONE**

### Paso 2: Descargar clave JSON

1. Haz clic en la cuenta de servicio que acabas de crear
2. Ve a la pestaña **KEYS**
3. Haz clic en **ADD KEY** > **Create new key**
4. Selecciona **JSON** y haz clic en **CREATE**
5. Se descargará un archivo JSON (algo como `turnosonlinebb-8406b-xxxxx.json`)

### Paso 3: Guardar el archivo JSON

1. Coloca el archivo JSON en: `storage/app/firebase-service-account.json`
2. Asegúrate de que el archivo tenga permisos de lectura

### Paso 4: Instalar dependencias

```bash
composer require google/auth
```

### Paso 5: Actualizar el servicio

El servicio `FirebaseMessagingService` necesita actualizarse para usar la API v1. Esto requiere cambios en el código.

## Opción 3: Habilitar API Legacy (Si es posible)

Algunos proyectos permiten habilitar la API legacy:

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Selecciona tu proyecto
3. Ve a **APIs & Services** > **Library**
4. Busca **Firebase Cloud Messaging API (Legacy)**
5. Si aparece, haz clic en **ENABLE**
6. Luego intenta obtener el Server Key desde Firebase Console nuevamente

## Recomendación

Para proyectos nuevos, la **Opción 2 (Cuenta de Servicio)** es la forma recomendada y más segura. Sin embargo, requiere actualizar el código del servicio.

Si prefieres mantener el código actual (que usa Server Key), intenta primero la **Opción 1** o **Opción 3**.

