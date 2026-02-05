# Configuración de Google Calendar OAuth

Esta guía te ayudará a configurar OAuth con Google Calendar para agregar eventos automáticamente al calendario de los usuarios.

## 📋 Requisitos Previos

1. Cuenta de Google Cloud Platform
2. Proyecto en Google Cloud Console
3. API de Google Calendar habilitada

## 🔧 Pasos de Configuración

### 1. Crear Proyecto en Google Cloud Console

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Crea un nuevo proyecto o selecciona uno existente
3. Anota el **Project ID**

### 2. Habilitar Google Calendar API

1. En el menú lateral, ve a **APIs & Services** > **Library**
2. Busca "Google Calendar API"
3. Haz clic en **Enable**

### 3. Crear Credenciales OAuth 2.0

1. Ve a **APIs & Services** > **Credentials**
2. Haz clic en **Create Credentials** > **OAuth client ID**
3. Si es la primera vez, configura la **OAuth consent screen**:
   - **User Type**: External (para usuarios externos)
   - **App name**: TurnosOnlineBB
   - **User support email**: Tu email
   - **Developer contact information**: Tu email
   - **Scopes**: Agrega `https://www.googleapis.com/auth/calendar` y `https://www.googleapis.com/auth/calendar.events`
   - **Test users**: Agrega emails de prueba (opcional para desarrollo)

4. Crea el **OAuth client ID**:
   - **Application type**: Web application
   - **Name**: TurnosOnlineBB Web Client
   - **Authorized JavaScript origins**: 
     - `http://localhost:8000` (para desarrollo)
     - `https://turnosonlinebb.com` (tu dominio de producción)
   - **Authorized redirect URIs**:
     - `http://localhost:8000/google-calendar/callback` (para desarrollo)
     - `https://turnosonlinebb.com/google-calendar/callback` (tu dominio de producción)

5. Copia el **Client ID** y **Client Secret**

### 4. Configurar Variables de Entorno

Agrega estas variables a tu archivo `.env`:

```env
GOOGLE_CALENDAR_CLIENT_ID=tu_client_id_aqui
GOOGLE_CALENDAR_CLIENT_SECRET=tu_client_secret_aqui
GOOGLE_CALENDAR_REDIRECT_URI=https://turnosonlinebb.com/google-calendar/callback
```

**Nota**: En desarrollo local, usa:
```env
GOOGLE_CALENDAR_REDIRECT_URI=http://localhost:8000/google-calendar/callback
```

### 5. Ejecutar Migración

Ejecuta la migración para agregar los campos de OAuth a la tabla `pacientes`:

```bash
php artisan migrate
```

## 🚀 Cómo Funciona

### Flujo de Autenticación

1. **Primera vez**: El usuario hace clic en "Conectar con Google Calendar"
2. **Autorización**: Se redirige a Google para autorizar la aplicación
3. **Callback**: Google redirige de vuelta con un código de autorización
4. **Tokens**: El servidor intercambia el código por tokens de acceso
5. **Almacenamiento**: Los tokens se guardan en la base de datos

### Agregar Eventos Automáticamente

Cuando un usuario registra un turno:
1. El sistema verifica si tiene tokens de OAuth guardados
2. Si tiene tokens válidos, crea el evento automáticamente en Google Calendar
3. Si el token expiró, intenta refrescarlo automáticamente
4. Si no tiene OAuth, descarga el archivo .ics como fallback

## 📱 Integración en el Frontend

Para agregar un botón de "Conectar con Google Calendar", agrega esto en tu vista:

```html
<a href="/google-calendar/authorize?paciente_id={{$paciente_id}}" class="btn btn-primary">
    📅 Conectar con Google Calendar
</a>
```

## 🔒 Seguridad

- Los tokens se almacenan encriptados en la base de datos
- Los tokens de acceso expiran después de 1 hora
- Los refresh tokens permiten obtener nuevos tokens sin re-autenticación
- Los tokens se refrescan automáticamente cuando expiran

## 🐛 Troubleshooting

### Error: "redirect_uri_mismatch"
- Verifica que la URI de redirección en `.env` coincida exactamente con la configurada en Google Cloud Console
- Asegúrate de incluir `http://` o `https://` según corresponda

### Error: "invalid_grant"
- El código de autorización puede haber expirado (válido por 10 minutos)
- Intenta autenticarte nuevamente

### Los eventos no se crean
- Verifica que los tokens estén guardados en la base de datos
- Revisa los logs de Laravel para ver errores específicos
- Verifica que la API de Google Calendar esté habilitada

## 📝 Notas Importantes

- En modo de prueba, solo los usuarios agregados como "Test users" pueden autenticarse
- Para producción, necesitas verificar la aplicación en Google Cloud Console
- El proceso de verificación puede tomar varios días

## 🔄 Actualizar Tokens

Los tokens se refrescan automáticamente cuando:
- Se intenta crear un evento y el token expiró
- El refresh token es válido

Si el refresh token también expiró, el usuario necesitará autenticarse nuevamente.

