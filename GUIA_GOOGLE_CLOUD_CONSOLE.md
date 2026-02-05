# 🚀 Guía Completa: Configurar Google Cloud Console para OAuth

Esta guía te llevará paso a paso para configurar OAuth y que los eventos se agreguen automáticamente al calendario.

---

## 📋 PASO 1: Acceder a Google Cloud Console

1. Abre tu navegador (Chrome recomendado)
2. Ve a: **https://console.cloud.google.com/**
3. Inicia sesión con tu cuenta de Google
   - **Recomendación**: Usa la misma cuenta que usas para Firebase (`turnosonlinebb-8406b`)

---

## 📋 PASO 2: Seleccionar o Crear Proyecto

### Opción A: Usar el proyecto de Firebase existente (RECOMENDADO)

1. En la parte superior de la pantalla, verás un selector de proyectos (al lado del logo de Google Cloud)
2. Haz clic en el selector
3. Busca y selecciona: **`turnosonlinebb-8406b`** (tu proyecto de Firebase)
4. Si no lo ves, haz clic en **"Todos"** y búscalo ahí

### Opción B: Crear un nuevo proyecto

1. Haz clic en el selector de proyectos
2. Haz clic en **"NUEVO PROYECTO"**
3. **Nombre del proyecto**: `TurnosOnlineBB Calendar`
4. Haz clic en **"CREAR"**
5. Espera unos segundos hasta que se cree

---

## 📋 PASO 3: Habilitar Google Calendar API

1. En el menú lateral izquierdo (☰), busca y haz clic en **"APIs y servicios"**
2. En el submenú, haz clic en **"Biblioteca"**
3. En el buscador de la parte superior, escribe: **`Google Calendar API`**
4. Haz clic en **"Google Calendar API"** (debería ser el primer resultado)
5. Haz clic en el botón azul **"HABILITAR"**
6. Espera unos segundos hasta que aparezca un mensaje de confirmación

✅ **Verificación**: Deberías ver un mensaje verde que dice "API habilitada" o similar

---

## 📋 PASO 4: Configurar Pantalla de Consentimiento OAuth

1. En el menú lateral, ve a **"APIs y servicios"** > **"Pantalla de consentimiento de OAuth"**
2. Si es la primera vez, verás un formulario. Si no, haz clic en **"EDITAR APP"**

### 4.1. Información de la aplicación

Completa estos campos:

- **Tipo de usuario**: Selecciona **"Externo"** (para usuarios externos)
- Haz clic en **"CREAR"**

- **Nombre de la aplicación**: `TurnosOnlineBB`
- **Email de soporte al usuario**: (tu email, ej: `tu-email@gmail.com`)
- **Logo de la aplicación**: (opcional, puedes subir un logo)
- **Dominio de la aplicación**: `turnosonlinebb.com` (opcional)
- **Email del desarrollador**: (tu email, ej: `tu-email@gmail.com`)

Haz clic en **"GUARDAR Y CONTINUAR"**

### 4.2. Ámbitos (Scopes) - MUY IMPORTANTE

1. Haz clic en **"AGREGAR O QUITAR ÁMBITOS"**
2. En el panel que aparece, busca y marca estas dos opciones:
   - ✅ `https://www.googleapis.com/auth/calendar` (Google Calendar API)
   - ✅ `https://www.googleapis.com/auth/calendar.events` (Google Calendar API)
3. Haz clic en **"ACTUALIZAR"** (botón azul en la parte inferior)
4. Haz clic en **"GUARDAR Y CONTINUAR"**

### 4.3. Usuarios de prueba (solo para desarrollo)

1. Si estás en modo de prueba, verás una pantalla para agregar usuarios de prueba
2. Haz clic en **"AGREGAR USUARIOS"**
3. Agrega tu email (y los emails de usuarios que quieras que prueben)
4. Haz clic en **"AGREGAR"**
5. Haz clic en **"GUARDAR Y CONTINUAR"**

### 4.4. Resumen

1. Revisa la información
2. Haz clic en **"VOLVER AL PANEL"**

---

## 📋 PASO 5: Crear Credenciales OAuth 2.0

### 5.1. Ir a Credenciales

1. En el menú lateral, ve a **"APIs y servicios"** > **"Credenciales"**
2. Verás una lista de credenciales (puede estar vacía)

### 5.2. Crear ID de Cliente OAuth

1. Haz clic en el botón **"CREAR CREDENCIALES"** (arriba a la izquierda)
2. En el menú desplegable, selecciona **"ID de cliente de OAuth"**

### 5.3. Configurar el ID de Cliente

Si es la primera vez, te pedirá configurar la pantalla de consentimiento (ya lo hiciste en el Paso 4).

Completa el formulario:

**Tipo de aplicación**: 
- Selecciona **"Aplicación web"**

**Nombre**:
- Escribe: `TurnosOnlineBB Web Client`

**Orígenes de JavaScript autorizados**:
- Haz clic en **"+ AGREGAR URI"**
- Agrega estas dos URIs (una por una):
  1. `http://localhost:8000` (para desarrollo local)
  2. `https://turnosonlinebb.com` (tu dominio de producción)
  
  **Importante**: 
  - No agregues `/` al final
  - Usa `http://` para localhost y `https://` para producción

**URI de redirección autorizadas**:
- Haz clic en **"+ AGREGAR URI"**
- Agrega estas dos URIs (una por una):
  1. `http://localhost:8000/google-calendar/callback` (para desarrollo)
  2. `https://turnosonlinebb.com/google-calendar/callback` (para producción)
  
  **Importante**: 
  - Debe coincidir EXACTAMENTE con la ruta que definiste en Laravel
  - No debe tener espacios ni caracteres extra

### 5.4. Crear y Copiar Credenciales

1. Haz clic en **"CREAR"** (botón azul)
2. Se abrirá un popup con tus credenciales

⚠️ **¡MUY IMPORTANTE!** Copia estos valores AHORA (no podrás ver el secreto después):

**ID de cliente**:
```
123456789-abcdefghijklmnopqrstuvwxyz.apps.googleusercontent.com
```
(Copia este valor completo)

**Secreto de cliente**:
```
GOCSPX-abcdefghijklmnopqrstuvwxyz123456
```
(Copia este valor completo)

3. **Guarda estos valores en un lugar seguro** (un archivo de texto, notas, etc.)
4. Haz clic en **"LISTO"**

---

## 📋 PASO 6: Configurar Variables de Entorno

1. Abre tu archivo `.env` en el servidor (o en local si estás probando)
2. Agrega estas líneas al final del archivo:

```env
# Google Calendar OAuth
GOOGLE_CALENDAR_CLIENT_ID=tu_client_id_aqui
GOOGLE_CALENDAR_CLIENT_SECRET=tu_client_secret_aqui
GOOGLE_CALENDAR_REDIRECT_URI=https://turnosonlinebb.com/google-calendar/callback
```

**Ejemplo real** (reemplaza con tus valores):
```env
GOOGLE_CALENDAR_CLIENT_ID=123456789-abcdefghijklmnopqrstuvwxyz.apps.googleusercontent.com
GOOGLE_CALENDAR_CLIENT_SECRET=GOCSPX-abcdefghijklmnopqrstuvwxyz123456
GOOGLE_CALENDAR_REDIRECT_URI=https://turnosonlinebb.com/google-calendar/callback
```

**Para desarrollo local**, usa:
```env
GOOGLE_CALENDAR_REDIRECT_URI=http://localhost:8000/google-calendar/callback
```

3. **Guarda el archivo `.env`**

---

## 📋 PASO 7: Ejecutar Migración en el Servidor

Conéctate a tu servidor por SSH y ejecuta:

```bash
cd /home/u895805914/domains/turnosonlinebb.com/TurnosImage
php artisan migrate
```

Esto agregará las columnas necesarias a la tabla `pacientes`:
- `google_calendar_access_token`
- `google_calendar_refresh_token`
- `google_calendar_token_expires_at`

---

## 📋 PASO 8: Limpiar Caché

En el servidor, ejecuta:

```bash
php artisan config:clear
php artisan cache:clear
```

Esto asegura que Laravel lea las nuevas variables de entorno.

---

## ✅ PASO 9: Verificar que Funciona

### 9.1. Probar la Conexión

1. Ve a tu sitio web: `https://turnosonlinebb.com`
2. Inicia sesión como paciente
3. Ve a la página de seleccionar turno
4. Deberías ver un botón o mensaje sobre conectar con Google Calendar
5. Haz clic en "Conectar con Google Calendar"
6. Serás redirigido a Google para autorizar
7. Haz clic en **"Permitir"** o **"Allow"**
8. Serás redirigido de vuelta a tu sitio

### 9.2. Probar que Agrega Eventos

1. Registra un turno para un paciente que tenga Google Calendar conectado
2. Ve a **Google Calendar** (calendar.google.com)
3. Verifica que el evento aparezca automáticamente
4. Verifica que también aparezca un recordatorio para el día previo

---

## 🔍 Verificación de Configuración

### Checklist de Verificación

Antes de probar, verifica que tengas:

- [ ] API de Google Calendar habilitada
- [ ] Pantalla de consentimiento configurada
- [ ] Ámbitos (scopes) agregados correctamente
- [ ] Credenciales OAuth creadas
- [ ] URIs de redirección configuradas correctamente
- [ ] Variables de entorno en `.env`
- [ ] Migración ejecutada
- [ ] Caché limpiado

---

## 🐛 Solución de Problemas Comunes

### Error: "redirect_uri_mismatch"

**Causa**: La URI de redirección no coincide exactamente.

**Solución**:
1. Ve a Google Cloud Console > Credenciales
2. Edita tu ID de cliente OAuth
3. Verifica que las URIs sean EXACTAMENTE iguales:
   - En Google Cloud: `https://turnosonlinebb.com/google-calendar/callback`
   - En `.env`: `https://turnosonlinebb.com/google-calendar/callback`
4. No debe haber espacios, `/` al final, ni diferencias de mayúsculas/minúsculas

### Error: "access_denied"

**Causa**: El usuario canceló la autorización o no está en la lista de usuarios de prueba.

**Solución**:
- Si estás en modo de prueba, agrega el email del usuario en "Usuarios de prueba"
- Si el usuario canceló, pídele que intente nuevamente

### Error: "invalid_client"

**Causa**: El Client ID o Client Secret son incorrectos.

**Solución**:
1. Verifica que copiaste correctamente los valores
2. Verifica que no haya espacios al inicio o final
3. Verifica que las variables estén en el `.env` correcto

### Los eventos no se crean automáticamente

**Verificaciones**:
1. ¿El paciente tiene tokens guardados? (revisa la base de datos)
2. ¿Los tokens no expiraron? (revisa `google_calendar_token_expires_at`)
3. Revisa los logs: `storage/logs/laravel.log`
4. Verifica que la API de Google Calendar esté habilitada

---

## 📝 Notas Importantes

### Modo de Prueba vs Producción

- **Modo de Prueba**: Solo usuarios agregados como "Test users" pueden autenticarse
- **Producción**: Cualquier usuario puede autenticarse, pero necesitas verificar la aplicación

### Verificación de la Aplicación

Para producción, Google puede pedirte verificar la aplicación:
- Esto puede tomar varios días
- Mientras tanto, puedes usar usuarios de prueba
- Una vez verificada, cualquier usuario podrá conectarse

### Límites de Cuota

Google Calendar API tiene límites muy altos:
- 1,000,000,000 (mil millones) de requests por día
- Para uso normal, nunca alcanzarás este límite

---

## 🎯 Resumen Rápido

1. ✅ Ve a Google Cloud Console
2. ✅ Selecciona proyecto `turnosonlinebb-8406b`
3. ✅ Habilita Google Calendar API
4. ✅ Configura pantalla de consentimiento
5. ✅ Agrega ámbitos: `calendar` y `calendar.events`
6. ✅ Crea credenciales OAuth 2.0
7. ✅ Agrega URIs de redirección
8. ✅ Copia Client ID y Client Secret
9. ✅ Agrega variables al `.env`
10. ✅ Ejecuta migración
11. ✅ Limpia caché
12. ✅ ¡Prueba!

---

## 📞 ¿Necesitas Ayuda en Algún Paso?

Si tienes problemas en algún paso específico, dime en cuál estás y te ayudo con más detalle.

