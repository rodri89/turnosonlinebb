# TurnosOnlineBB

Sistema de gestión de turnos médicos online desarrollado en Laravel con integración de Firebase Cloud Messaging (FCM) y Google Calendar.

## 📋 Descripción

TurnosOnlineBB es una plataforma web completa para la gestión de turnos médicos que permite a los pacientes:
- Solicitar y gestionar turnos médicos online
- Recibir notificaciones push sobre sus turnos
- Agregar automáticamente turnos a su calendario de Google
- Realizar videollamadas con médicos
- Gestionar recetas médicas
- Realizar pagos online

## 🚀 Características Principales

### Para Pacientes
- ✅ Solicitud de turnos online
- ✅ Notificaciones push (Firebase FCM)
- ✅ Integración con Google Calendar (OAuth)
- ✅ Videollamadas con médicos
- ✅ Gestión de recetas médicas
- ✅ Pagos online (MercadoPago)
- ✅ Consulta de historial médico

### Para Médicos
- ✅ Gestión de horarios y disponibilidad
- ✅ Asignación de turnos
- ✅ Videollamadas con pacientes
- ✅ Emisión de recetas digitales
- ✅ Gestión de pacientes
- ✅ Configuración de obras sociales

### Para Secretarias
- ✅ Asignación de turnos
- ✅ Gestión de pacientes
- ✅ Bloqueo de horarios
- ✅ Administración de sobreturnos

## 🛠️ Tecnologías Utilizadas

- **Backend**: Laravel 5.8
- **Frontend**: Blade Templates, jQuery, Bootstrap
- **Base de Datos**: MySQL
- **Notificaciones Push**: Firebase Cloud Messaging (FCM)
- **Calendario**: Google Calendar API (OAuth 2.0)
- **Pagos**: MercadoPago API
- **Autenticación**: Laravel Passport (OAuth2)

## 📦 Requisitos

- PHP >= 7.1.3
- Composer
- MySQL
- Node.js y npm (opcional, para assets)
- Servidor web (Apache/Nginx)

## 🔧 Instalación

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/rodri89/turnosonlinebb.git
   cd turnosonlinebb
   ```

2. **Instalar dependencias**
   ```bash
   composer install
   ```

3. **Configurar el archivo .env**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configurar la base de datos**
   - Crear una base de datos MySQL
   - Actualizar las credenciales en `.env`
   - Ejecutar las migraciones:
     ```bash
     php artisan migrate
     ```

5. **Configurar Firebase**
   - Crear un proyecto en Firebase Console
   - Descargar el archivo `firebase-service-account.json`
   - Colocarlo en `storage/app/firebase-service-account.json`
   - Configurar las variables en `.env`:
     ```
     FIREBASE_SERVICE_ACCOUNT_PATH=storage/app/firebase-service-account.json
     FIREBASE_PROJECT_ID=tu-project-id
     ```

6. **Configurar Google Calendar OAuth**
   - Crear un proyecto en Google Cloud Console
   - Habilitar Google Calendar API
   - Crear credenciales OAuth 2.0
   - Configurar en `.env`:
     ```
     GOOGLE_CALENDAR_CLIENT_ID=tu_client_id
     GOOGLE_CALENDAR_CLIENT_SECRET=tu_client_secret
     GOOGLE_CALENDAR_REDIRECT_URI=https://tu-dominio.com/google-calendar/callback
     ```

7. **Configurar permisos de almacenamiento**
   ```bash
   php artisan storage:link
   chmod -R 775 storage bootstrap/cache
   ```

8. **Ejecutar el servidor de desarrollo**
   ```bash
   php artisan serve
   ```

## 📱 Notificaciones Push (FCM)

El sistema utiliza Firebase Cloud Messaging para enviar notificaciones push a los pacientes sobre sus turnos.

### Configuración
1. Obtener el VAPID key desde Firebase Console
2. Configurar el service worker: `public/firebase-messaging-sw.js`
3. Los tokens FCM se almacenan automáticamente cuando el usuario acepta las notificaciones

### Envío de Notificaciones
```php
use App\Services\FirebaseMessagingService;

$firebase = app('firebase');
$result = $firebase->sendTurnoReminder($paciente, $turno, $medico, $consultorio);
```

## 📅 Integración con Google Calendar

El sistema permite agregar automáticamente turnos al calendario de Google mediante OAuth 2.0.

### Flujo de OAuth
1. El paciente autoriza la conexión con Google Calendar
2. Se almacenan los tokens de acceso y refresh
3. Los turnos se agregan automáticamente al calendario
4. Se crea un recordatorio el día previo al turno

### Sin OAuth
Si el paciente no tiene OAuth configurado, se abre Google Calendar directamente con el evento pre-cargado para que el usuario pueda guardarlo manualmente.

## 🔐 Seguridad

- Archivos sensibles excluidos del repositorio (`.env`, `firebase-service-account.json`)
- Autenticación mediante Laravel Passport
- Middleware de autorización por roles
- Validación de datos en formularios
- Protección CSRF

## 📚 Documentación Adicional

- [Guía de configuración de Firebase](FIREBASE_SETUP.md)
- [Guía de Google Cloud Console](GUIA_GOOGLE_CLOUD_CONSOLE.md)
- [Configuración de OAuth para Google Calendar](GOOGLE_CALENDAR_OAUTH_SETUP.md)

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:
1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📝 Licencia

Este proyecto es privado y de uso exclusivo para TurnosOnlineBB.

## 👨‍💻 Autor

**Rodrigo Banegas**
- GitHub: [@rodri89](https://github.com/rodri89)

## 📞 Soporte

Para soporte, contactar a través de los canales oficiales de TurnosOnlineBB.

---

**Versión**: 1.0.0  
**Última actualización**: 2026
