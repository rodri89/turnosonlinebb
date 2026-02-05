# Solución Error PackageManifest en Producción

## Error: "Undefined index: name" en PackageManifest.php

Este error ocurre cuando algún paquete en `vendor/composer/installed.json` no tiene la clave 'name' correctamente definida.

## Soluciones (en orden de preferencia)

### Solución 1: Regenerar el manifest manualmente (Más Rápida)

En el servidor, ejecuta:

```bash
cd /home/u895805914/domains/turnosonlinebb.com/TurnosImage

# Limpiar el manifest existente
rm -f bootstrap/cache/packages.php

# Regenerar el manifest
php artisan package:discover

# Limpiar cachés
php artisan config:clear
php artisan cache:clear
```

### Solución 2: Regenerar composer autoload

```bash
cd /home/u895805914/domains/turnosonlinebb.com/TurnosImage

# Regenerar autoload
composer dump-autoload

# Regenerar manifest
php artisan package:discover

# Limpiar cachés
php artisan config:clear
php artisan cache:clear
```

### Solución 3: Reinstalar dependencias (Si las anteriores no funcionan)

```bash
cd /home/u895805914/domains/turnosonlinebb.com/TurnosImage

# Eliminar vendor y lock (cuidado, esto reinstalará todo)
rm -rf vendor
rm composer.lock

# Reinstalar dependencias
composer install --no-dev --optimize-autoloader

# Regenerar manifest
php artisan package:discover

# Limpiar cachés
php artisan config:clear
php artisan cache:clear
```

### Solución 4: Parche temporal (Si nada más funciona)

Si el error persiste, puedes crear un parche temporal en `app/Providers/AppServiceProvider.php`:

```php
public function boot()
{
    Schema::defaultStringLength(191);
    
    // Parche temporal para PackageManifest
    try {
        \Artisan::call('package:discover');
    } catch (\Exception $e) {
        // Ignorar errores de package discovery
    }
}
```

## Verificar que funciona

Después de aplicar cualquier solución, verifica:

```bash
# Verificar que el manifest se generó
ls -la bootstrap/cache/packages.php

# Probar que Laravel funciona
php artisan --version

# Probar una ruta
curl https://turnosonlinebb.com/test_fcm_notification/1
```

## Prevención

Para evitar este error en el futuro:

1. **Siempre usa `composer install` en lugar de `composer update`** en producción
2. **Mantén `composer.lock` actualizado** en tu repositorio
3. **Ejecuta `composer install --no-dev --optimize-autoloader`** en producción
4. **Limpia cachés después de instalar paquetes**: `php artisan config:clear`

## Comando Completo Recomendado para Producción

```bash
cd /home/u895805914/domains/turnosonlinebb.com/TurnosImage

# 1. Instalar/actualizar dependencias
composer install --no-dev --optimize-autoloader

# 2. Regenerar manifest
php artisan package:discover

# 3. Limpiar todos los cachés
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# 4. Optimizar (opcional, para mejor rendimiento)
php artisan config:cache
php artisan route:cache
```

## Si el error persiste

1. **Revisa los logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Verifica el archivo installed.json:**
   ```bash
   cat vendor/composer/installed.json | grep -A 5 "name"
   ```
   Busca paquetes que no tengan la clave "name"

3. **Verifica permisos:**
   ```bash
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

