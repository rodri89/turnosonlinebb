# 🔧 Instalar Solo Dependencias de Google (Sin Actualizar Otras)

## ⚠️ PROBLEMA
No quieres ejecutar `composer install` porque actualiza otras dependencias y rompe cosas.

## ✅ SOLUCIÓN: Instalar Solo Google Dependencies

### Opción 1: Instalar solo las dependencias de Google (Recomendado)

```bash
cd /ruta/a/tu/proyecto

# Instalar solo google/apiclient y google/auth sin actualizar otras dependencias
composer require google/apiclient:^2.0 google/auth:^1.26 --no-update

# Luego actualizar solo estas dependencias
composer update google/apiclient google/auth --with-dependencies

# Regenerar autoloader
composer dump-autoload --optimize --no-dev
```

### Opción 2: Verificar si ya están instaladas y solo regenerar autoloader

```bash
cd /ruta/a/tu/proyecto

# Verificar si las dependencias ya están instaladas
if [ ! -d "vendor/google/apiclient" ]; then
    echo "Instalando google/apiclient..."
    composer require google/apiclient:^2.0 --no-update
    composer update google/apiclient --with-dependencies
fi

if [ ! -d "vendor/google/auth" ]; then
    echo "Instalando google/auth..."
    composer require google/auth:^1.26 --no-update
    composer update google/auth --with-dependencies
fi

# Regenerar autoloader
composer dump-autoload --optimize --no-dev

# Limpiar cache
php artisan config:clear
php artisan cache:clear
```

### Opción 3: Usar composer.json lock (Más seguro)

Si tienes un `composer.lock` actualizado en local:

1. **En local**, ejecuta:
   ```bash
   composer update google/apiclient google/auth --with-dependencies
   ```

2. **Sube a producción**:
   - `composer.json`
   - `composer.lock` (actualizado)

3. **En producción**, ejecuta:
   ```bash
   composer install --no-dev --optimize-autoloader --no-scripts
   ```

   Esto instalará exactamente las versiones del `composer.lock` sin actualizar nada.

## 🎯 RECOMENDACIÓN

**Usa la Opción 1** - Es la más segura y solo actualiza las dependencias de Google:

```bash
composer require google/apiclient:^2.0 google/auth:^1.26 --no-update
composer update google/apiclient google/auth --with-dependencies
composer dump-autoload --optimize --no-dev
php artisan config:clear
```

Esto solo instalará/actualizará `google/apiclient` y `google/auth`, sin tocar otras dependencias.

