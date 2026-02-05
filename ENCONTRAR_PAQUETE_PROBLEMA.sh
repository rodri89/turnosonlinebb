#!/bin/bash
# Script para encontrar el paquete sin 'name' en installed.json

cd /home/u895805914/domains/turnosonlinebb.com/TurnosImage

echo "Buscando paquetes sin 'name' en installed.json..."
echo ""

php -r "
\$packages = json_decode(file_get_contents('vendor/composer/installed.json'), true);
\$packages = isset(\$packages['packages']) ? \$packages['packages'] : \$packages;

foreach(\$packages as \$index => \$pkg) {
    if(!isset(\$pkg['name'])) {
        echo \"Paquete sin 'name' encontrado en índice: \$index\n\";
        echo \"Contenido:\n\";
        print_r(\$pkg);
        echo \"\n---\n\";
    }
}
"

echo ""
echo "Si no encontró nada, el problema puede estar en la estructura del JSON."

