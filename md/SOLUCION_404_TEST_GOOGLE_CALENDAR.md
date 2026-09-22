# 🔧 Solución: Error 404 en /test/google-calendar/

## ⚠️ PROBLEMA
La ruta `https://turnosonlinebb.com/test/google-calendar/turnosonlinebb-test-oauth-2026-secreto` devuelve 404.

## ✅ SOLUCIONES

### 1. Verificar que los archivos estén subidos

Asegúrate de que estos archivos estén en producción:

- ✅ `routes/web.php` (actualizado con las rutas de test)
- ✅ `app/Http/Controllers/GoogleCalendarController.php`
- ✅ `resources/views/turnos/test_google_calendar.blade.php`

### 2. Verificar variable de entorno en producción

En el servidor, verifica que el `.env` tenga:

```env
GOOGLE_CALENDAR_TEST_TOKEN=turnosonlinebb-test-oauth-2026-secreto
```

### 3. Limpiar cache en producción

Ejecuta estos comandos en el servidor:

```bash
cd /ruta/a/tu/proyecto
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### 4. Verificar que las rutas estén registradas

Ejecuta en el servidor:

```bash
php artisan route:list | grep "test/google-calendar"
```

Deberías ver:
- `GET|HEAD  test/google-calendar/{token?}`
- `GET|HEAD  test/google-calendar-connect/{token?}`
- `POST      test/google-calendar-disconnect/{token?}`

### 5. Verificar orden de rutas

En `routes/web.php`, las rutas de test DEBEN estar ANTES de:

```php
Route::get('test','PacienteController@test')->name('test');
```

Si están después, Laravel puede estar interceptando `/test/` antes de llegar a `/test/google-calendar/`.

### 6. Verificar permisos

Asegúrate de que los archivos tengan los permisos correctos:

```bash
chmod -R 755 app/Http/Controllers
chmod -R 755 resources/views
chmod 644 routes/web.php
```

## 🔍 DEBUGGING

### Verificar logs

Revisa los logs de Laravel en producción:

```bash
tail -f storage/logs/laravel.log
```

Luego intenta acceder a la URL y verifica si hay errores.

### Probar directamente el controlador

Crea una ruta temporal para verificar:

```php
Route::get('/test-debug', function() {
    return 'Test funciona. Token: ' . env('GOOGLE_CALENDAR_TEST_TOKEN', 'NO CONFIGURADO');
});
```

Si esta ruta funciona, el problema es específico de las rutas de test.

## 📝 CHECKLIST

- [ ] Archivos subidos a producción
- [ ] Variable `GOOGLE_CALENDAR_TEST_TOKEN` en `.env` de producción
- [ ] Cache limpiado (`php artisan route:clear`, etc.)
- [ ] Rutas verificadas (`php artisan route:list`)
- [ ] Orden de rutas correcto (test/google-calendar antes de test)
- [ ] Permisos de archivos correctos
- [ ] Logs revisados para errores

## 🎯 URL CORRECTA

La URL debe ser exactamente:

```
https://turnosonlinebb.com/test/google-calendar/turnosonlinebb-test-oauth-2026-secreto
```

**Importante:** El token debe coincidir EXACTAMENTE con el valor en `GOOGLE_CALENDAR_TEST_TOKEN` del `.env`.

