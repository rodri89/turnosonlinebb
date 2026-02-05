# Solución Error DataTables en Servidor

## Error: Class 'Yajra\Datatables\DatatablesServiceProvider' not found

El problema es que hay archivos de caché con el namespace incorrecto.

## Solución Completa (Ejecutar en el Servidor)

```bash
cd /home/u895805914/domains/turnosonlinebb.com/TurnosImage

# 1. Eliminar TODOS los archivos de caché
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/routes.php
rm -f bootstrap/cache/packages.php

# 2. Asegurarse de que config/app.php tenga el namespace correcto
# Verifica que la línea 165 tenga:
# Yajra\DataTables\DataTablesServiceProvider::class,
# (con DataTables, no Datatables)

# 3. Limpiar todos los cachés de Laravel
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# 4. Regenerar cachés (opcional, para mejor rendimiento)
php artisan config:cache
php artisan route:cache
```

## Verificar que config/app.php está correcto

En el servidor, verifica que `config/app.php` tenga:

**Línea ~165 (providers):**
```php
Yajra\DataTables\DataTablesServiceProvider::class,
```

**Línea ~233 (aliases):**
```php
'DataTables' => Yajra\DataTables\Facades\DataTables::class,
```

**NOTA:** Debe ser `DataTables` (con T mayúscula), NO `Datatables`.

## Si el archivo config/app.php en el servidor está incorrecto

Edita el archivo en el servidor:

```bash
nano config/app.php
```

O súbelo desde tu máquina local (ya está corregido).

## Comando Rápido (Copia y Pega)

```bash
cd /home/u895805914/domains/turnosonlinebb.com/TurnosImage && \
rm -f bootstrap/cache/*.php && \
php artisan config:clear && \
php artisan cache:clear && \
php artisan view:clear && \
php artisan route:clear && \
echo "✅ Cachés limpiados"
```

## Verificar que funciona

```bash
php artisan --version
```

Si funciona, deberías ver la versión de Laravel sin errores.

