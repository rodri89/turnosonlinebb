# Solución de Problemas - Firebase Cloud Messaging

## Error: "Registration failed - push service error"

Este error generalmente ocurre por una de las siguientes razones:

### 1. Verificar que la VAPID Key esté correctamente configurada

**Pasos para verificar:**

1. Ve a [Firebase Console](https://console.firebase.google.com/)
2. Selecciona tu proyecto: `turnosonlinebb-8406b`
3. Ve a **Project Settings** (⚙️) > **Cloud Messaging**
4. En la sección **Web Push certificates**, verifica que:
   - Exista una clave VAPID
   - La clave que tienes en el código coincida EXACTAMENTE con la de Firebase
   - Si no hay clave, haz clic en **Generate key pair**

**IMPORTANTE:** La VAPID key debe ser EXACTAMENTE la misma que está en Firebase Console. Copia y pega directamente desde Firebase.

### 2. Verificar que el dominio esté autorizado

Firebase puede tener restricciones de dominio. Verifica:

1. En Firebase Console, ve a **Authentication** > **Settings** > **Authorized domains**
2. Asegúrate de que `localhost` esté en la lista (debería estar por defecto)
3. Si estás en producción, asegúrate de que tu dominio esté autorizado

### 3. Verificar permisos del navegador

1. Abre las **Configuraciones del navegador**
2. Ve a **Privacidad y seguridad** > **Configuración del sitio** > **Notificaciones**
3. Verifica que el sitio tenga permisos para enviar notificaciones
4. Si está bloqueado, cambia a "Permitir"

### 4. Verificar el Service Worker

1. Abre las **Herramientas de desarrollador** (F12)
2. Ve a la pestaña **Application** (o **Aplicación**)
3. En el menú lateral, ve a **Service Workers**
4. Verifica que `firebase-messaging-sw.js` esté registrado y activo
5. Si hay errores, haz clic en **Unregister** y recarga la página

### 5. Verificar la consola del navegador

Revisa la consola del navegador (F12 > Console) para ver mensajes de error más específicos:

- `messaging/invalid-vapid-key`: La VAPID key es incorrecta
- `messaging/permission-blocked`: Los permisos están bloqueados
- `messaging/permission-default`: Los permisos no han sido otorgados
- `messaging/unsupported-browser`: El navegador no es compatible

### 6. Probar en un navegador diferente

Algunos navegadores tienen mejor soporte para FCM:
- **Chrome/Edge**: Mejor soporte
- **Firefox**: Soporte completo
- **Safari**: Soporte limitado (requiere configuración adicional)

### 7. Verificar que estés usando HTTPS (o localhost)

- FCM funciona en HTTPS
- También funciona en `localhost` para desarrollo
- NO funciona en `http://` en dominios reales (solo localhost)

### 8. Limpiar caché y recargar

1. Abre las **Herramientas de desarrollador** (F12)
2. Haz clic derecho en el botón de recargar
3. Selecciona **Vaciar caché y volver a cargar de forma forzada**

### 9. Verificar la versión de Firebase SDK

Asegúrate de estar usando una versión compatible. El código actual usa:
- `firebase-app.js` versión 12.8.0
- `firebase-messaging.js` versión 12.8.0
- `firebase-messaging-compat.js` versión 12.8.0 (en el service worker)

### 10. Verificar la configuración de Firebase

Asegúrate de que la configuración en `seleccionar_dia.blade.php` coincida EXACTAMENTE con la de Firebase Console:

```javascript
const firebaseConfig = {
  apiKey: "AIzaSyBLLw7_0CJZ4mepUZjWbdHN9SAwFbZTtz0",
  authDomain: "turnosonlinebb-8406b.firebaseapp.com",
  projectId: "turnosonlinebb-8406b",
  storageBucket: "turnosonlinebb-8406b.firebasestorage.app",
  messagingSenderId: "991206372015",
  appId: "1:991206372015:web:0ef83da53a43d5a3f11a5a",
  measurementId: "G-KBD5NC6E1V"
};
```

## Solución paso a paso recomendada

1. **Verifica la VAPID Key en Firebase Console**
   - Copia la VAPID key directamente desde Firebase
   - Pégala en el código reemplazando la actual

2. **Limpia el Service Worker**
   - Abre DevTools > Application > Service Workers
   - Haz clic en "Unregister" en `firebase-messaging-sw.js`
   - Recarga la página

3. **Verifica permisos**
   - Acepta los permisos de notificación cuando el navegador los solicite

4. **Revisa la consola**
   - Busca mensajes de error más específicos
   - Comparte los errores si persisten

## Si el problema persiste

1. Verifica que la API de Cloud Messaging esté habilitada en Google Cloud Console
2. Verifica que no haya restricciones de IP o dominio en Firebase
3. Prueba en modo incógnito para descartar extensiones del navegador
4. Verifica que no haya conflictos con otros service workers (como OneSignal)

