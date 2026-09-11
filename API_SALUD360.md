# API Salud 360

API REST que consume la app **Salud 360** (Kotlin Multiplatform: Android, iOS y Web) para que médicos,
secretarias y el administrador trabajen sobre **la misma base de datos** que este sitio.
La web y la app de pacientes siguen funcionando igual: Laravel sigue siendo el único dueño de la base.

- Prefijo: `/api/salud360/`
- Formato: JSON. Fechas `AAAA-MM-DD`, horarios `HH:MM`, días de la semana `1` (lunes) … `7` (domingo).
- Autenticación: tokens propios de la API (tabla `salud360_tokens`). Enviar `Authorization: Bearer <token>`; si el hosting descarta ese encabezado (PHP como CGI sin la regla de `.htaccess`), enviar `X-Salud360-Token: <token>`. No se usa Passport: el modelo `User` no tiene el trait `HasApiTokens`.
- Solo usuarios con `usuario_tipo` 1 (admin), 2 (médico) o 3 (secretaria). Los pacientes reciben `403`.
- Todas las respuestas traen `ok: true|false`. Los errores traen `mensaje` y, cuando aplica, `codigo`.

## Código

| Archivo | Qué hace |
|---|---|
| `app/Services/Salud360/AgendaService.php` | Lógica de disponibilidad y validaciones (antes `createJson` / `createJsonTurnosDobles` en `TurnoController`). La web ahora delega en este servicio. |
| `app/Http/Controllers/Api/Salud360/*` | Controladores de la API (`Auth`, `Agenda`, `Turno`, `Paciente`, `Horario`, `Catalogo`, `Receta`). |
| `app/Services/Salud360/TokenService.php` | Emite y valida los tokens (hash SHA-256 en `salud360_tokens`; la tabla se crea sola en el primer ingreso o con la migración). |
| `app/Http/Middleware/Salud360Api.php` | Autentica por token y restringe la API a admin / médico / secretaria (alias `salud360` en `Kernel.php`). |
| `routes/api.php` | Grupo `salud360`. |
| `tests/Unit/Salud360AgendaServiceTest.php` | Pruebas de los métodos puros del servicio. |

Única migración: `salud360_tokens` (idempotente; la tabla también se crea sola). Sin cambios de versión de PHP/Laravel (PHP 7.1 / Laravel 5.8).

## Permisos

Casi todos los endpoints reciben `medico_id`. Quién puede usar cada médico:

- **Médico**: solo sobre sí mismo (si no manda `medico_id` se usa el propio).
- **Secretaria**: sobre los médicos activos de sus consultorios (`secretaria_consultorios`), igual que en la web.
- **Administrador**: sobre cualquier médico activo. Solo el admin puede crear/borrar feriados.

`consultorio_id` es opcional: por defecto se usa el consultorio del médico.

## Endpoints

### Sesión

| Método | Ruta | Cuerpo / Query | Respuesta |
|---|---|---|---|
| POST | `auth/login` | `email`, `password` | `access_token`, `expires_at`, `perfil` |
| GET | `auth/perfil` | | `perfil` |
| POST | `auth/logout` | | |
| POST | `auth/password` | `password_actual`, `password_nueva` | |

`perfil` contiene `usuario`, `rol` (`admin` / `medico` / `secretaria`) y:

- `medico`: datos del médico, `especialidad`, `consultorio`, `modulos` (ids activos de `modulo_medicos`),
  `ventana_dias`, `cupo_primer_control`.
- `secretaria`: `consultorios` y `medicos` que puede gestionar (cada uno con el mismo detalle que arriba).

### Agenda (lectura)

| Método | Ruta | Query | Devuelve |
|---|---|---|---|
| GET | `agenda/dia` | `medico_id`, `fecha`, `[tipo_turno=1]` | `turnos` del día con datos del paciente, `slots` `[{horario, libre}]`, `es_feriado`, `cantidad_sobreturnos`, `modulo_caja_comentario` |
| GET | `agenda/disponibilidad` | `medico_id`, `fecha`, `[tipo_turno]`, `[primer_control=0/1]` | `slots`. Con `primer_control=1` y módulo 3 activo devuelve pares `{horario, horario2, libre}` y `cupo_primer_control_disponible` |
| GET | `agenda/semana` | `medico_id`, `[desde]`, `[cantidad=5]` | Próximos días con horarios cargados: `dias[] {fecha, dia, es_feriado, slots, turnos}` |
| GET | `agenda/proximas-fechas` | `medico_id`, `[primer_control]`, `[cantidad=3]`, `[desde]` | `fechas[]` con al menos un turno libre |
| GET | `agenda/dias-atencion` | `medico_id` | `dias_semana[]`, `fechas_especiales[]` |

### Turnos

| Método | Ruta | Cuerpo | Notas |
|---|---|---|---|
| GET | `turnos` | query `medico_id`, `[desde]`, `[hasta]`, `[actualizado_desde]`, `[incluir_cancelados]`, `[limite]` | Para sincronizar. Con `actualizado_desde` (timestamp) trae también cancelados. |
| GET | `turnos/{id}` | | |
| GET | `turnos/paciente/{pacienteId}` | query `medico_id` | Historial del paciente con el médico |
| POST | `turnos` | `medico_id`, `paciente_id`, `fecha`, `horario`, `[horario2]`, `[tipo_turno=1]`, `[primer_control]`, `[comentario]` | Valida horario ocupado y "un turno por día". Con `primer_control=1` + `horario2` registra el primer control doble (dos turnos) y controla el cupo del día. |
| POST | `turnos/sobreturno` | `medico_id`, `paciente_id`, `fecha`, `horario`, `[tipo_turno]` | Turno con `sobreturno = 1` |
| POST | `turnos/bloquear-dia` | `medico_id`, `fecha`, `paciente_id`, `[tipo_turno]` | Ocupa todos los horarios del día con el paciente "bloqueo" (misma mecánica que la web). Falla si ya hay turnos. |
| POST | `turnos/{id}/cancelar` | `[motivo]` | `activo = 0`, comentario "Cancelado por :Apellido, Nombre" |
| POST | `turnos/{id}/asistencia` | `asistio` (0/1/2) | |
| POST | `turnos/{id}/caja` | `caja` | |
| POST | `turnos/{id}/comentario` | `comentario` | |

Códigos de rechazo (HTTP 409): `ocupado`, `mismo_dia`, `cupo_primer_control`, `dia_con_turnos`.

Formato de un turno:

```json
{
  "id": 123, "paciente_id": 45, "medico_id": 3, "consultorio_id": 1,
  "dia": 2, "horario": "09:20", "fecha": "2026-09-08",
  "asistio": 0, "sobreturno": 0, "primer_control": false, "caja": 0, "comentario": "",
  "tipo_turno": 1, "especialidad": null, "otorgado_por": "secretaria@mail.com",
  "cancelado_por": null, "activo": 1, "pago": 0, "pago_estado": null, "importe_reserva": null,
  "paciente": {"id": 45, "nombre": "…", "apellido": "…", "dni": "…", "telefono": "…", "mail": "…", "obra_social": "…", "numero_afiliado": "…"},
  "created_at": "…", "updated_at": "…"
}
```

### Pacientes

| Método | Ruta | Cuerpo / Query | Notas |
|---|---|---|---|
| GET | `pacientes` | `medico_id`, `[desde_id]`, `[limite=500]`, `[actualizado_desde]` | Pacientes vinculados al médico (`medico_pacientes`) o al consultorio (`paciente_secretarias`). Paginado por id: repetir con `desde_id = ultimo_id` mientras `hay_mas`. |
| GET | `pacientes/buscar` | `q` (DNI, apellido, nombre o mail) | Máximo 50 |
| GET | `pacientes/pendientes` | `medico_id` | Registrados desde la app que esperan activación (`activo = 2`) |
| GET | `pacientes/{id}` | | Incluye `medicos[] {medico_id, bloqueado}` |
| POST | `pacientes` | `medico_id`, `dni`, `nombre`, `apellido`, `[telefono]`, `[mail]`, `[fecha_nacimiento]`, `[domicilio]`, `[localidad]`, `[obra_social]`, `[numero_afiliado]`, `[obra_social_plan]`, `[afiliado_obligatorio]` | Si el DNI ya existe actualiza y vincula (misma regla que la web). `201` si es nuevo. |
| PUT | `pacientes/{id}` | mismos campos | |
| POST | `pacientes/{id}/activar` | `medico_id` | `activo = 1`, vincula y envía el mail de activación |
| POST | `pacientes/{id}/bloqueo` | `medico_id`, `bloqueado` (0/1) | |

### Horarios y configuración del médico

| Método | Ruta | Cuerpo | Notas |
|---|---|---|---|
| GET | `horarios` | query `medico_id` | `horarios_fijos[]` (con `valido_desde/hasta`, `tipo_turno`), `fechas_especiales[]` futuras con sus horarios, `cupo_primer_control`, `ventana_dias`, `modulos` |
| POST | `horarios` | `medico_id`, `dia`, `horario`, `[tipo_turno=1]`, `[valido_desde]`, `[valido_hasta]` | Horario fijo semanal |
| PUT | `horarios/{id}` | `[valido_desde]`, `[valido_hasta]` | Vigencia |
| DELETE | `horarios/{id}` | | Desactiva |
| POST | `horarios/fecha-especial` | `medico_id`, `fecha`, `horarios[]` | Fecha agregada que reemplaza la plantilla ese día |
| DELETE | `horarios/fecha-especial/{id}` | | Desactiva la fecha y sus horarios |
| DELETE | `horarios/fecha-especial/horario/{id}` | | Desactiva un horario de la fecha |
| PUT | `config/primer-control` | `medico_id`, `dias[] {dia, cantidad}` | Cupo de primeros controles por día |
| PUT | `config/ventana-dias` | `medico_id`, `dias` | Módulo 9 |

### Catálogos, obras sociales, mensajes, feriados

| Método | Ruta | Notas |
|---|---|---|
| GET | `catalogos` | `consultorios`, `especialidades`, `medicos`, `obras_sociales`, `feriados` (desde 60 días atrás), `modulos`, `receta_estados`, `tipos_turno`, `hoy` |
| GET | `obras-sociales?medico_id` | Obras sociales del médico con `activo`, `importe`, `importe_reserva` |
| POST | `obras-sociales` | `medico_id`, `obra_social_id`, `[activo]`, `[importe]` |
| GET | `mensajes?medico_id` | Mensajes especiales del médico |
| POST | `feriados` | `fecha`, `descripcion` (solo admin) |
| DELETE | `feriados/{id}` | (solo admin) |

### Recetas

| Método | Ruta | Notas |
|---|---|---|
| GET | `recetas?medico_id` | Recetas del consultorio. `[estado]`, `[solo_pendientes=1]` (excluye estado 5), `[actualizado_desde]` |
| GET | `recetas/{id}` | Incluye `fotos[]` |
| POST | `recetas/{id}/estado` | `estado_id`, `[comentario]`, `[motivo_rechazo]` (estado 4) |

## Ejemplo

```bash
curl -s -X POST https://turnosonlinebb.com/api/salud360/auth/login \
  -H 'Accept: application/json' -d 'email=medico@mail.com&password=secreto'

curl -s 'https://turnosonlinebb.com/api/salud360/agenda/dia?fecha=2026-09-08' \
  -H 'Accept: application/json' -H 'Authorization: Bearer <token>'

curl -s -X POST https://turnosonlinebb.com/api/salud360/turnos \
  -H 'Accept: application/json' -H 'Authorization: Bearer <token>' \
  -d 'paciente_id=45&fecha=2026-09-08&horario=09:20'
```

## Requisitos en el servidor

- No hace falta Passport. La tabla `salud360_tokens` se crea sola en el primer login (o con `php artisan migrate`).
- Mandar siempre `Accept: application/json` para que los errores de validación vuelvan en JSON.
- Tras desplegar: `php artisan route:clear && php artisan config:clear` (y `composer dump-autoload` si el hosting cachea el autoload).

## Alcance y pendientes

- Videollamadas (`tipo_turno = 4`, tablas `*_videollamadas`) no se gestionan desde Salud 360; el cálculo de
  disponibilidad las contempla solo para mantener el comportamiento de `createJson`.
- Cobro de reservas por MercadoPago: la API respeta los horarios con pago pendiente pero no inicia ni reembolsa pagos.
- Google Calendar del paciente: al cancelar desde la API no se borra el evento del calendario (igual que al cancelar
  desde la agenda semanal de la web).
