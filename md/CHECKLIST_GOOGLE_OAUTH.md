# ✅ Checklist Rápido: Configurar Google Calendar OAuth

Usa este checklist mientras configuras. Marca cada paso cuando lo completes.

## 🔧 Configuración en Google Cloud Console

### Paso 1: Proyecto
- [ ] Accedí a https://console.cloud.google.com/
- [ ] Seleccioné el proyecto `turnosonlinebb-8406b` (o creé uno nuevo)

### Paso 2: API
- [ ] Fui a "APIs y servicios" > "Biblioteca"
- [ ] Busqué "Google Calendar API"
- [ ] Hice clic en "HABILITAR"
- [ ] Vi el mensaje de confirmación

### Paso 3: Pantalla de Consentimiento
- [ ] Fui a "APIs y servicios" > "Pantalla de consentimiento de OAuth"
- [ ] Seleccioné "Externo"
- [ ] Completé:
  - [ ] Nombre: `TurnosOnlineBB`
  - [ ] Email de soporte
  - [ ] Email del desarrollador
- [ ] Agregué ámbitos:
  - [ ] `https://www.googleapis.com/auth/calendar`
  - [ ] `https://www.googleapis.com/auth/calendar.events`
- [ ] Agregué usuarios de prueba (mi email)

### Paso 4: Credenciales OAuth
- [ ] Fui a "APIs y servicios" > "Credenciales"
- [ ] Hice clic en "CREAR CREDENCIALES" > "ID de cliente de OAuth"
- [ ] Configuré:
  - [ ] Tipo: "Aplicación web"
  - [ ] Nombre: `TurnosOnlineBB Web Client`
  - [ ] Orígenes autorizados:
    - [ ] `http://localhost:8000`
    - [ ] `https://turnosonlinebb.com`
  - [ ] URIs de redirección:
    - [ ] `http://localhost:8000/google-calendar/callback`
    - [ ] `https://turnosonlinebb.com/google-calendar/callback`
- [ ] **Copié el Client ID** (lo guardé en un lugar seguro)
- [ ] **Copié el Client Secret** (lo guardé en un lugar seguro)

## 💻 Configuración en el Servidor

### Paso 5: Variables de Entorno
- [ ] Abrí el archivo `.env` en el servidor
- [ ] Agregué:
  ```env
  GOOGLE_CALENDAR_CLIENT_ID=mi_client_id
  GOOGLE_CALENDAR_CLIENT_SECRET=mi_client_secret
  GOOGLE_CALENDAR_REDIRECT_URI=https://turnosonlinebb.com/google-calendar/callback
  ```
- [ ] Verifiqué que los valores sean correctos (sin espacios extra)

### Paso 6: Migración
- [ ] Me conecté al servidor por SSH
- [ ] Ejecuté: `php artisan migrate`
- [ ] Vi el mensaje de confirmación

### Paso 7: Limpiar Caché
- [ ] Ejecuté: `php artisan config:clear`
- [ ] Ejecuté: `php artisan cache:clear`

## 🧪 Pruebas

### Paso 8: Probar Conexión
- [ ] Fui a mi sitio web
- [ ] Intenté conectar con Google Calendar
- [ ] Fui redirigido a Google
- [ ] Autorizé la aplicación
- [ ] Volví a mi sitio y vi el mensaje de éxito

### Paso 9: Probar Agregar Evento
- [ ] Registré un turno para un paciente con Google Calendar conectado
- [ ] Fui a Google Calendar
- [ ] Verifiqué que el evento apareció automáticamente
- [ ] Verifiqué que el recordatorio (día previo) también apareció

## 📝 Valores que Necesitas

Guarda estos valores mientras configuras:

**Client ID:**
```
_________________________________________________
```

**Client Secret:**
```
_________________________________________________
```

**Redirect URI (producción):**
```
https://turnosonlinebb.com/google-calendar/callback
```

**Redirect URI (desarrollo):**
```
http://localhost:8000/google-calendar/callback
```

## ⚠️ Errores Comunes a Evitar

- ❌ No agregues `/` al final de las URIs
- ❌ No dejes espacios al inicio o final de los valores
- ❌ No uses `http://` en producción (debe ser `https://`)
- ❌ No olvides agregar los ámbitos (scopes) en la pantalla de consentimiento
- ❌ No olvides agregar usuarios de prueba si estás en modo de prueba

## 🎯 ¿Listo?

Cuando hayas completado todos los pasos, los eventos se agregarán automáticamente al calendario sin que el usuario tenga que hacer nada más después de la primera autorización.

