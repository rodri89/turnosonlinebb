# Verificar Firebase en Servidor de Producción

## Error: Archivo de cuenta de servicio no encontrado

El archivo `firebase-service-account.json` no está en el servidor o la ruta está mal configurada.

## Pasos para Solucionar

### Paso 1: Verificar si el archivo existe

```bash
cd /home/u895805914/domains/turnosonlinebb.com/TurnosImage

# Verificar si existe
ls -la storage/app/firebase-service-account.json

# Si no existe, verificar el directorio
ls -la storage/app/
```

### Paso 2: Si el archivo NO existe, subirlo

El archivo está en tu máquina local en:
```
storage/app/firebase-service-account.json
```

**Sube este archivo al servidor** usando SFTP/FTP a:
```
/home/u895805914/domains/turnosonlinebb.com/TurnosImage/storage/app/firebase-service-account.json
```

### Paso 3: Verificar permisos

```bash
cd /home/u895805914/domains/turnosonlinebb.com/TurnosImage

# Dar permisos correctos
chmod 644 storage/app/firebase-service-account.json
chmod 755 storage/app
chmod -R 755 storage
```

### Paso 4: Verificar configuración en .env

Edita el `.env` en el servidor:

```bash
nano .env
```

Verifica que tenga estas líneas:

```env
FIREBASE_SERVICE_ACCOUNT_PATH=storage/app/firebase-service-account.json
FIREBASE_PROJECT_ID=turnosonlinebb-8406b
```

**IMPORTANTE:** 
- La ruta puede ser relativa: `storage/app/firebase-service-account.json`
- O absoluta: `/home/u895805914/domains/turnosonlinebb.com/TurnosImage/storage/app/firebase-service-account.json`

### Paso 5: Limpiar caché

```bash
php artisan config:clear
php artisan cache:clear
```

### Paso 6: Verificar que funciona

```bash
# Probar que el archivo es accesible
php -r "echo file_exists('storage/app/firebase-service-account.json') ? 'EXISTE' : 'NO EXISTE';"

# Probar que Laravel puede leerlo
php artisan tinker
# Luego ejecuta:
# file_exists(storage_path('app/firebase-service-account.json'))
```

## Comandos Rápidos (Copia y Pega)

```bash
cd /home/u895805914/domains/turnosonlinebb.com/TurnosImage && \
echo "Verificando archivo..." && \
ls -la storage/app/firebase-service-account.json && \
echo "Verificando permisos..." && \
chmod 644 storage/app/firebase-service-account.json 2>/dev/null && \
chmod 755 storage/app && \
echo "Verificando .env..." && \
grep FIREBASE_SERVICE_ACCOUNT_PATH .env && \
php artisan config:clear && \
php artisan cache:clear && \
echo "✅ Verificación completada"
```

## Si el archivo no existe

1. **Desde tu máquina local**, sube el archivo:
   - Origen: `storage/app/firebase-service-account.json`
   - Destino en servidor: `/home/u895805914/domains/turnosonlinebb.com/TurnosImage/storage/app/firebase-service-account.json`

2. **O crea el archivo directamente en el servidor** copiando el contenido del JSON que tienes localmente.

## Verificar ruta absoluta vs relativa

Si la ruta relativa no funciona, prueba con absoluta en `.env`:

```env
FIREBASE_SERVICE_ACCOUNT_PATH=/home/u895805914/domains/turnosonlinebb.com/TurnosImage/storage/app/firebase-service-account.json
```

