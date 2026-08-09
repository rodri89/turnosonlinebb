# 🔄 Actualizar Credenciales OAuth al Proyecto Correcto

El error menciona "ConsultasMedicas" porque estás usando credenciales del proyecto anterior. Necesitas crear nuevas credenciales en el proyecto **"turnosonlinebb"**.

---

## ✅ PASO 1: Ir al Proyecto Correcto en Google Cloud Console

1. Ve a: **https://console.cloud.google.com/**
2. En la parte superior, haz clic en el selector de proyectos
3. Busca y selecciona: **`turnosonlinebb`** (ID: 294189365172)
4. Verifica que el proyecto esté seleccionado

---

## ✅ PASO 2: Verificar que Google Calendar API esté Habilitada

1. En el menú lateral (☰), ve a: **"APIs y servicios"** > **"Biblioteca"**
2. Busca: **"Google Calendar API"**
3. Si no está habilitada, haz clic en **"HABILITAR"**
4. Espera a que se habilite

---

## ✅ PASO 3: Configurar Pantalla de Consentimiento (si no está configurada)

1. Ve a: **"APIs y servicios"** > **"Pantalla de consentimiento de OAuth"**
2. Si es la primera vez, completa:
   - **Tipo de usuario**: "Externo"
   - **Nombre de la aplicación**: `TurnosOnlineBB`
   - **Email de soporte**: tu email
   - **Email del desarrollador**: tu email
   - **Política de privacidad**: `https://turnosonlinebb.com/politica-privacidad`
   - **Condiciones del servicio**: `https://turnosonlinebb.com/condiciones-servicio`
3. En "Ámbitos", agrega:
   - `https://www.googleapis.com/auth/calendar`
   - `https://www.googleapis.com/auth/calendar.events`
4. En "Usuarios de prueba", agrega: `banegasrodrigo89@gmail.com`
5. Guarda todo

---

## ✅ PASO 4: Crear Nuevas Credenciales OAuth

1. Ve a: **"APIs y servicios"** > **"Credenciales"**
2. Haz clic en **"CREAR CREDENCIALES"** > **"ID de cliente de OAuth"**
3. Si te pide configurar la pantalla de consentimiento, selecciona la que acabas de crear
4. Completa:
   - **Tipo de aplicación**: "Aplicación web"
   - **Nombre**: `TurnosOnlineBB Web Client`
   - **Orígenes de JavaScript autorizados**:
     - `http://localhost:8000`
     - `https://turnosonlinebb.com`
   - **URI de redirección autorizadas**:
     - `http://localhost:8000/google-calendar/callback`
     - `https://turnosonlinebb.com/google-calendar/callback`
5. Haz clic en **"CREAR"**
6. **¡IMPORTANTE!** Copia el **Client ID** y el **Client Secret** que aparecen

---

## ✅ PASO 5: Actualizar el archivo `.env`

Abre el archivo `.env` en tu proyecto y actualiza estas líneas:

```env
GOOGLE_CALENDAR_CLIENT_ID=TU_NUEVO_CLIENT_ID_AQUI
GOOGLE_CALENDAR_CLIENT_SECRET=TU_NUEVO_CLIENT_SECRET_AQUI
GOOGLE_CALENDAR_REDIRECT_URI=https://turnosonlinebb.com/google-calendar/callback
```

**Ejemplo:**
```env
GOOGLE_CALENDAR_CLIENT_ID=294189365172-xxxxxxxxxxxxx.apps.googleusercontent.com
GOOGLE_CALENDAR_CLIENT_SECRET=GOCSPX-xxxxxxxxxxxxx
GOOGLE_CALENDAR_REDIRECT_URI=https://turnosonlinebb.com/google-calendar/callback
```

---

## ✅ PASO 6: Limpiar Cache de Laravel

Ejecuta estos comandos en tu servidor (producción):

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

## ✅ PASO 7: Probar

1. Intenta conectar Google Calendar desde tu aplicación
2. Deberías ver la pantalla de autorización de Google con el nombre **"TurnosOnlineBB"** (no "ConsultasMedicas")
3. Autoriza y debería funcionar

---

## 🔍 VERIFICAR QUE TODO ESTÉ CORRECTO

1. **Proyecto en Google Cloud**: Debe ser `turnosonlinebb` (ID: 294189365172)
2. **Credenciales OAuth**: Deben ser del proyecto `turnosonlinebb`
3. **`.env`**: Debe tener las nuevas credenciales
4. **Cache**: Debe estar limpiado

---

## ⚠️ IMPORTANTE

- **NO mezcles credenciales** de diferentes proyectos
- **Cada proyecto** tiene sus propias credenciales OAuth
- Si cambias de proyecto, **siempre crea nuevas credenciales**

---

## 📝 NOTA

Si ya tenías credenciales del proyecto anterior "ConsultasMedicas", **NO las uses**. Debes crear nuevas credenciales en el proyecto "turnosonlinebb".

