# Archivos para Subir al Servidor - Firebase FCM

## 📋 Lista Completa de Archivos

### 1. Archivos de Código (OBLIGATORIOS)

#### Migraciones
- ✅ `database/migrations/2026_02_03_161943_add_fcm_token_to_pacientes_table.php`

#### Modelos
- ✅ `app/Paciente.php` (modificado - agregado fcm_token al fillable)

#### Controladores
- ✅ `app/Http/Controllers/PacienteController.php` (modificado - agregado método saveFcmToken)
- ✅ `app/Http/Controllers/TurnoController.php` (modificado - agregado testFcmNotification y actualizado enviarRecordatorioMail)

#### Servicios
- ✅ `app/Services/FirebaseMessagingService.php` (nuevo archivo)

#### Providers
- ✅ `app/Providers/AppServiceProvider.php` (modificado - registrado servicio firebase)

#### Rutas
- ✅ `routes/web.php` (modificado - agregadas rutas save_fcm_token y test_fcm_notification)

#### Vistas
- ✅ `resources/views/turnos/seleccionar_dia.blade.php` (modificado - agregado código Firebase FCM)

#### Service Worker (Frontend)
- ✅ `public/firebase-messaging-sw.js` (nuevo archivo - IMPORTANTE)

### 2. Archivos de Configuración (OBLIGATORIOS)

#### Credenciales de Firebase
- ✅ `storage/app/firebase-service-account.json` (nuevo archivo - CONTIENE DATOS SENSIBLES)

**⚠️ IMPORTANTE:** Este archivo contiene credenciales privadas. NO lo subas a Git, pero SÍ debes subirlo al servidor de forma segura (FTP/SFTP).

### 3. Dependencias de Composer

#### Archivos de Composer
- ✅ `composer.json` (modificado - agregado google/auth)
- ✅ `composer.lock` (modificado - agregado google/auth)

**Después de subir, ejecuta en el servidor:**
```bash
composer install
```

### 4. Configuración del Servidor (.env)

**NO subas el archivo `.env`**, pero asegúrate de agregar estas líneas en el `.env` del servidor:

```env
FIREBASE_SERVICE_ACCOUNT_PATH=storage/app/firebase-service-account.json
FIREBASE_PROJECT_ID=turnosonlinebb-8406b
```

### 5. Archivos de Documentación (OPCIONALES - No necesarios para funcionar)

Estos archivos son solo documentación y NO son necesarios para que funcione:
- ❌ `FIREBASE_SETUP.md`
- ❌ `FIREBASE_ENVIAR_NOTIFICACIONES.md`
- ❌ `FIREBASE_SERVER_KEY_SETUP.md`
- ❌ `FIREBASE_TROUBLESHOOTING.md`
- ❌ `FIREBASE_LOCALHOST.md`
- ❌ `FIREBASE_API_V1_SETUP.md`
- ❌ `OBTENER_SERVER_KEY.md`
- ❌ `CONFIGURAR_CREDENCIALES_FCM.md`
- ❌ `ARCHIVOS_PARA_SUBIR_SERVIDOR.md` (este archivo)

## 📝 Checklist de Subida

### Paso 1: Subir Archivos de Código
- [ ] `database/migrations/2026_02_03_161943_add_fcm_token_to_pacientes_table.php`
- [ ] `app/Paciente.php`
- [ ] `app/Http/Controllers/PacienteController.php`
- [ ] `app/Http/Controllers/TurnoController.php`
- [ ] `app/Services/FirebaseMessagingService.php`
- [ ] `app/Providers/AppServiceProvider.php`
- [ ] `routes/web.php`
- [ ] `resources/views/turnos/seleccionar_dia.blade.php`
- [ ] `public/firebase-messaging-sw.js`

### Paso 2: Subir Credenciales (IMPORTANTE)
- [ ] `storage/app/firebase-service-account.json` (subir de forma segura, NO por Git)

### Paso 3: Subir Dependencias
- [ ] `composer.json`
- [ ] `composer.lock`

### Paso 4: Configurar en el Servidor

1. **Ejecutar migración:**
   ```bash
   php artisan migrate
   ```

2. **Instalar dependencias:**
   ```bash
   composer install
   ```

3. **Configurar .env:**
   Agregar al `.env` del servidor:
   ```env
   FIREBASE_SERVICE_ACCOUNT_PATH=storage/app/firebase-service-account.json
   FIREBASE_PROJECT_ID=turnosonlinebb-8406b
   ```

4. **Verificar permisos:**
   ```bash
   chmod 644 storage/app/firebase-service-account.json
   chmod 755 storage/app
   ```

5. **Limpiar caché (si es necesario):**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

## 🔒 Seguridad

### Archivos que NO debes subir a Git (pero SÍ al servidor):
- `storage/app/firebase-service-account.json` - Contiene credenciales privadas
- `.env` - Contiene configuraciones sensibles

### Cómo subir el archivo JSON de forma segura:
1. Usa SFTP/SCP en lugar de FTP
2. O usa un gestor de archivos seguro
3. Verifica que el archivo esté en `storage/app/firebase-service-account.json` en el servidor

## ✅ Verificación Post-Subida

Después de subir todo, verifica:

1. **El archivo JSON existe:**
   ```bash
   ls -la storage/app/firebase-service-account.json
   ```

2. **Las dependencias están instaladas:**
   ```bash
   composer show google/auth
   ```

3. **La migración se ejecutó:**
   ```bash
   php artisan migrate:status
   ```
   Debe mostrar la migración `2026_02_03_161943_add_fcm_token_to_pacientes_table` como ejecutada.

4. **Probar notificación:**
   Accede a: `https://tudominio.com/test_fcm_notification/{paciente_id}`

## 🐛 Si algo no funciona

1. Verifica que el archivo JSON esté en la ruta correcta
2. Verifica los permisos del archivo JSON
3. Verifica que las variables de entorno estén en `.env`
4. Revisa los logs: `storage/logs/laravel.log`
5. Limpia la caché: `php artisan config:clear`

