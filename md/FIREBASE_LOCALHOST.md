# Firebase Cloud Messaging en Localhost

## ✅ Firebase FCM SÍ funciona en localhost

Firebase Cloud Messaging funciona perfectamente en `localhost` para desarrollo. No necesitas HTTPS en localhost.

## Consideraciones para Localhost

### 1. Verificar que el puerto sea correcto

Asegúrate de que estés accediendo a:
- `http://localhost:8000` (o el puerto que uses)
- `http://127.0.0.1:8000`

### 2. Service Worker debe estar en la raíz

El service worker `firebase-messaging-sw.js` debe estar en:
```
public/firebase-messaging-sw.js
```

Y debe ser accesible en:
```
http://localhost:8000/firebase-messaging-sw.js
```

### 3. Verificar que el Service Worker se cargue

1. Abre DevTools (F12)
2. Ve a **Application** > **Service Workers**
3. Deberías ver `firebase-messaging-sw.js` registrado
4. Si hay errores, haz clic en el link del error para ver detalles

### 4. Posibles conflictos con OneSignal

Si también usas OneSignal, puede haber conflictos entre service workers:

**Solución:**
- Ambos service workers pueden coexistir
- Asegúrate de que ambos estén correctamente registrados
- Si hay problemas, prueba deshabilitando temporalmente OneSignal

### 5. Verificar permisos del navegador

1. Ve a `chrome://settings/content/notifications` (Chrome)
2. O `about:preferences#privacy` > Notificaciones (Firefox)
3. Asegúrate de que `localhost` tenga permisos para notificaciones

### 6. Limpiar caché del Service Worker

Si hay problemas, limpia el service worker:

1. DevTools > **Application** > **Service Workers**
2. Haz clic en **Unregister** en `firebase-messaging-sw.js`
3. Recarga la página con **Ctrl+Shift+R** (o Cmd+Shift+R en Mac)

### 7. Verificar la VAPID Key

La VAPID key debe estar correctamente configurada:

1. Ve a Firebase Console > Project Settings > Cloud Messaging
2. Copia la VAPID key
3. Pégala en el código (línea ~1234 en `seleccionar_dia.blade.php`)

### 8. Verificar la consola del navegador

Revisa la consola (F12 > Console) para ver mensajes de depuración:

- ✅ Mensajes verdes = Todo bien
- ⚠️ Mensajes amarillos = Advertencias (pueden ignorarse)
- ❌ Mensajes rojos = Errores que necesitan atención

## Errores comunes en Localhost

### Error: "Registration failed - push service error"

**Causas posibles:**
1. VAPID key incorrecta o no configurada
2. Service worker no está completamente cargado
3. Permisos de notificación no otorgados
4. Conflicto con otro service worker

**Solución:**
1. Verifica la VAPID key en Firebase Console
2. Limpia y vuelve a registrar el service worker
3. Acepta los permisos cuando el navegador los solicite
4. Revisa la consola para más detalles

### Error: "Service worker registration failed"

**Causas:**
1. El archivo `firebase-messaging-sw.js` no existe
2. Ruta incorrecta del service worker
3. Error de sintaxis en el service worker

**Solución:**
1. Verifica que el archivo exista en `public/firebase-messaging-sw.js`
2. Accede directamente a `http://localhost:8000/firebase-messaging-sw.js` en el navegador
3. Deberías ver el código del service worker, no un error 404

### Error: "messaging/invalid-vapid-key"

**Causa:** La VAPID key no coincide con la de Firebase Console

**Solución:**
1. Ve a Firebase Console > Project Settings > Cloud Messaging
2. Copia la VAPID key EXACTAMENTE como aparece
3. Reemplázala en el código

## Pasos de depuración en Localhost

1. **Abre DevTools** (F12)
2. **Ve a Console** y busca mensajes de error
3. **Ve a Application** > **Service Workers** y verifica el estado
4. **Limpia el service worker** si es necesario
5. **Recarga la página** con caché limpio (Ctrl+Shift+R)
6. **Acepta los permisos** cuando se soliciten
7. **Revisa los logs** en la consola para ver el progreso

## Verificar que funciona

Cuando todo esté bien configurado, deberías ver en la consola:

```
🚀 Iniciando FCM en: http://localhost:8000/...
✅ Service Worker de Firebase registrado
✅ Service Worker ya está activo
✅ Messaging inicializado
🔑 Intentando obtener token con VAPID Key: BGCdVTZQ0UMPueqSKR...
✅ Service Worker activo, estado: activated
FCM Token obtenido: [un token largo]
Token FCM guardado: {success: true, message: "FCM Token guardado correctamente."}
```

## Si sigue sin funcionar

1. **Verifica la VAPID key** - Este es el problema más común
2. **Limpia todo el caché** del navegador
3. **Prueba en modo incógnito** para descartar extensiones
4. **Prueba en otro navegador** (Chrome suele tener mejor soporte)
5. **Verifica que Firebase esté correctamente configurado** en Firebase Console

## Nota sobre producción

Cuando despliegues a producción:
- Necesitarás HTTPS (no solo HTTP)
- El dominio debe estar autorizado en Firebase Console
- La VAPID key será la misma

