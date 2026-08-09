#!/bin/bash
# Script para instalar solo dependencias de Google sin actualizar otras

echo "🔍 Verificando dependencias de Google..."

# Verificar si ya están instaladas
if [ -d "vendor/google/apiclient" ] && [ -d "vendor/google/auth" ]; then
    echo "✅ Las dependencias de Google ya están instaladas"
    echo "🔄 Regenerando autoloader..."
    composer dump-autoload --optimize --no-dev
else
    echo "📦 Instalando dependencias de Google..."
    
    # Instalar solo google/apiclient y google/auth
    composer require google/apiclient:^2.0 google/auth:^1.26 --no-update
    
    # Actualizar solo estas dependencias
    composer update google/apiclient google/auth --with-dependencies
    
    # Regenerar autoloader
    composer dump-autoload --optimize --no-dev
fi

echo "🧹 Limpiando cache de Laravel..."
php artisan config:clear
php artisan cache:clear

echo "✅ Completado!"
