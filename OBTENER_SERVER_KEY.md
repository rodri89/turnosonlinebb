# Cómo Obtener el Server Key para Firebase FCM

## Método 1: Desde Google Cloud Console (Intentar primero)

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Selecciona tu proyecto: `turnosonlinebb-8406b`
3. Ve a **APIs & Services** > **Credentials**
4. Busca si hay alguna **API Key** existente que puedas usar
5. Si no hay, crea una nueva:
   - Haz clic en **+ CREATE CREDENTIALS** > **API key**
   - Se generará una clave (algo como `AIza...`)
   - **IMPORTANTE:** Esta clave puede no funcionar para FCM legacy

6. Prueba esta clave en tu `.env`:
   ```env
   FIREBASE_SERVER_KEY=AIza...tu_clave_aqui
   ```

**NOTA:** Si esta clave no funciona (da error 401 o 404), necesitarás usar el Método 2.

## Método 2: Habilitar API Legacy (Si es posible)

Algunos proyectos permiten habilitar la API legacy que usa Server Key:

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Selecciona tu proyecto: `turnosonlinebb-8406b`
3. Ve a **APIs & Services** > **Library**
4. Busca **"Firebase Cloud Messaging API (Legacy)"** o **"Firebase Cloud Messaging API"**
5. Si aparece, haz clic en **ENABLE**
6. Espera unos minutos
7. Vuelve a Firebase Console > Project Settings > Cloud Messaging
8. Ahora debería aparecer el **Server key**

## Método 3: Usar API v1 con Cuenta de Servicio (Más Complejo)

Si los métodos anteriores no funcionan, necesitarás usar la API v1 con una cuenta de servicio. Esto requiere:

1. Crear una cuenta de servicio
2. Descargar el archivo JSON
3. Instalar `composer require google/auth`
4. Actualizar el código del servicio

Ver `FIREBASE_API_V1_SETUP.md` para instrucciones detalladas.

## Recomendación

1. **Primero intenta el Método 1** (crear API Key desde Google Cloud Console)
2. Si no funciona, intenta el **Método 2** (habilitar API Legacy)
3. Si ninguno funciona, usa el **Método 3** (API v1 con cuenta de servicio)

