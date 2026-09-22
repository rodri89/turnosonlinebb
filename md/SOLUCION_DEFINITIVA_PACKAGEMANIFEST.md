# Solución Definitiva - Error PackageManifest

## El problema persiste: "Undefined index: name"

Esto significa que algún paquete en `vendor/composer/installed.json` no tiene la clave 'name'.

## Solución 1: Encontrar el paquete problemático

Ejecuta este comando para encontrar qué paquete está causando el problema:

```bash
cd /home/u895805914/domains/turnosonlinebb.com/TurnosImage

php -r "
\$packages = json_decode(file_get_contents('vendor/composer/installed.json'), true);
\$packages = isset(\$packages['packages']) ? \$packages['packages'] : \$packages;

foreach(\$packages as \$index => \$pkg) {
    if(!isset(\$pkg['name'])) {
        echo \"Paquete sin 'name' en índice: \$index\n\";
        print_r(\$pkg);
        echo \"\n---\n\";
    }
}
"
```

## Solución 2: Parche Temporal (Más Rápida)

Mientras solucionas el problema, crea un archivo packages.php vacío para que Laravel no falle:

```bash
cd /home/u895805914/domains/turnosonlinebb.com/TurnosImage

# Crear archivo vacío
echo "<?php return [];" > bootstrap/cache/packages.php
chmod 644 bootstrap/cache/packages.php

# Limpiar cachés
php artisan config:clear
php artisan cache:clear
```

**Esto permitirá que Laravel funcione, aunque no descubra paquetes automáticamente.**

## Solución 3: Deshabilitar package discovery temporalmente

Edita `composer.json` y agrega esto en la sección `extra`:

```json
"extra": {
    "laravel": {
        "dont-discover": ["*"]
    }
}
```

Luego ejecuta:
```bash
composer dump-autoload --optimize --no-dev
```

Esto deshabilitará el package discovery y evitará el error.

## Solución 4: Reinstalar dependencias limpiamente

Si las anteriores no funcionan:

```bash
cd /home/u895805914/domains/turnosonlinebb.com/TurnosImage

# Backup
cp composer.json composer.json.backup
cp composer.lock composer.lock.backup

# Eliminar vendor
rm -rf vendor

# Reinstalar
composer install --no-dev --optimize-autoloader --no-scripts

# Crear packages.php vacío
echo "<?php return [];" > bootstrap/cache/packages.php
chmod 644 bootstrap/cache/packages.php

# Limpiar cachés
php artisan config:clear
php artisan cache:clear
```

## Solución 5: Modificar PackageManifest.php (Último recurso)

Si nada funciona, puedes modificar temporalmente el archivo de Laravel:

```bash
cd /home/u895805914/domains/turnosonlinebb.com/TurnosImage

# Hacer backup
cp vendor/laravel/framework/src/Illuminate/Foundation/PackageManifest.php vendor/laravel/framework/src/Illuminate/Foundation/PackageManifest.php.backup

# Editar el archivo (buscar la línea 122 y agregar verificación)
# Busca: return [$this->format($package['name']) => $package['extra']['laravel'] ?? []];
# Cámbialo por:
# return isset($package['name']) ? [$this->format($package['name']) => $package['extra']['laravel'] ?? []] : [];
```

**NOTA:** Esta solución modifica código de Laravel y se perderá al actualizar. Solo úsala como último recurso.

## Recomendación

**Usa la Solución 2 (archivo vacío) + Solución 3 (deshabilitar discovery)** para que funcione inmediatamente, y luego investiga el paquete problemático con la Solución 1.

